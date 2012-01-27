<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

App::import('Controller','common','jreviews');

class ModuleListingsController extends MyController {
	
	var $uses = array('menu','field','criteria');
	
	var $helpers = array('paginator','routes','libraries','html','assets','text','jreviews','time','rating','thumbnail','custom_fields','community');
	
	var $components = array('config','access','everywhere');

	var $autoRender = false;
	
	var $autoLayout = false;
	
	var $layout = 'module';
		
	function beforeFilter() 
    {
        # Call beforeFilter of MyController parent class
		parent::beforeFilter();
	}
    
    // Need to return object by reference for PHP4
    function &getPluginModel() {
        return $this->Listing;
    }    
	
	function index()
	{	       
        // Required for ajax pagination to remember module settings
		$Session = RegisterClass::getInstance('MvcSession');
		$module_id = Sanitize::getInt($this->params,'module_id',Sanitize::getInt($this->data,'module_id'));
        if(!isset($this->params['module'])) $this->params['module'] = array(); // For direct calls to the controller        
         
        if($this->ajaxRequest) 
            {
			$this->params = $Session->get('module_params'.$module_id,null,S2Paths::get('jreviews','S2_CMSCOMP'));
		} else {
			srand((float)microtime()*1000000);
			$this->params['rand'] = rand();
			$Session->set('module_rand'.$module_id,$this->params['rand'],S2Paths::get('jreviews','S2_CMSCOMP'));
			$Session->set('module_params'.$module_id,$this->params,S2Paths::get('jreviews','S2_CMSCOMP'));
		}
		
		$this->viewSuffix = Sanitize::getString($this->params['module'],'tmpl_suffix');
				
		$conditions = array();
		$joins = array();
		$order = array();
		$having = array();

		# Read module parameters
		$dir_id = Sanitize::getString($this->params['module'],'dir');
		$section_id = Sanitize::getString($this->params['module'],'section');
		$cat_id = Sanitize::getString($this->params['module'],'category');
		$listing_id = Sanitize::getString($this->params['module'],'listing');
		$criteria_id = Sanitize::getString($this->params['module'],'criteria');
		$limit = Sanitize::getString($this->params['module'],'module_limit',5);
		$extension = Sanitize::getString($this->params['module'],'extension');
		$extension = $extension != '' ? $extension : 'com_content';

        if(isset($this->Listing))
        {                   
		    $this->Listing->_user = $this->_user;
						    
		    // This parameter determines the module mode
		    $sort = Sanitize::getString($this->params['module'],'listing_order');
		    $custom_order = Sanitize::getString($this->params['module'],'custom_order');
		    $custom_where = Sanitize::getString($this->params['module'],'custom_where');
		    
		    if($extension != 'com_content' && in_array($sort,array('topratededitor','featuredrandom','rhits'))) {
			    echo "You have selected the $sort mode which is not supported for components other than com_content. Please read the tooltips in the module parameters for more info on allowed settings.";			
			    return;
		    }
           
		    # Category auto detect
		    if(Sanitize::getInt($this->params['module'],'cat_auto') && $extension == 'com_content') 
		    { 		
                $ids = CommonController::_discoverIDs($this);
                extract($ids);
		    }
		    # Set conditionals based on configuration parameters
		    if($extension == 'com_content') 
		    { // Only works for core articles
			            
                $conditions = array_merge($conditions,array(
                    'Listing.state = 1',
                    '( Listing.publish_up = "'.NULL_DATE.'" OR DATE(Listing.publish_up) <= DATE("'._CURRENT_SERVER_TIME.'") )',
                    '( Listing.publish_down = "'.NULL_DATE.'" OR DATE(Listing.publish_down) >= DATE("'._CURRENT_SERVER_TIME.'") )',
                    'Listing.access <= ' . $this->_user->gid
                ));  
          
			    // Remove unnecessary fields from model query
			    $this->Listing->modelUnbind(array(
                    'Listing.fulltext AS `Listing.description`',
                    'Listing.metakey AS `Listing.metakey`',
                    'Listing.metadesc AS `Listing.metadesc`',
                    'User.email AS `User.email`'                    
                ));		
					    
                $cat_id != '' and $conditions[] = 'Listing.catid IN (' . $cat_id. ')';

                $cat_id == '' and $section_id != '' and $conditions[] = 'Listing.sectionid IN (' . $section_id. ')';

			    $cat_id == '' and $dir_id != '' and $conditions[] = 'JreviewsCategory.dirid IN (' . $dir_id . ')';
	    
                $cat_id == '' and $criteria_id != '' and $conditions[] = 'JreviewsCategory.criteriaid IN (' . $criteria_id . ')';
            } 
            else 
            {
			    if(Sanitize::getInt($this->params['module'],'cat_auto') && method_exists($this->Listing,'catUrlParam')) 
                {
				    if($cat_id = Sanitize::getInt($this->passedArgs,$this->Listing->catUrlParam())){
					    $conditions[] = 'JreviewsCategory.id IN (' . cleanIntegerCommaList($cat_id). ')';
				    }
			    } 
                elseif($cat_id) 
                {	
				    $conditions[] = 'JreviewsCategory.id IN (' . $cat_id. ')';
			    }			
		    }
		    
		    $listing_id and $conditions[] = "Listing.{$this->Listing->realKey} IN ($listing_id)";
            
		    switch($sort) 
            {
			    case 'random':
				    $order[] = 'RAND('.$this->params['rand'].')';				
				    break;
			    case 'featuredrandom':
				    $conditions[] = 'featured > 0';				
				    $order[] = 'RAND('.$this->params['rand'].')';
				    break;
                case 'topratededitor':
                    $conditions[] = 'Totals.editor_rating > 0';                
                    break;
			    // Editor rating sorting options dealt with in the Listing->processSorting method					
		    }

		    # Custom WHERE
		    $custom_where and $conditions[] = $custom_where;
            
            # Filtering options
            $having = array();
            // Listings submitted in the past x days
            $entry_period = Sanitize::getInt($this->params['module'],'filter_listing_period');
            
            if($entry_period > 0 && $this->Listing->dateKey)
            {
                $conditions[] = "Listing.{$this->Listing->dateKey} >= DATE_SUB('"._CURRENT_SERVER_TIME."', INTERVAL $entry_period DAY)";
            }
            
            // Listings with reviews submitted in past x days
            $review_period = Sanitize::getInt($this->params['module'],'filter_review_period');
            if($review_period > 0)
            {
                $conditions[] = "Review.created >= DATE_SUB(CURDATE(), INTERVAL $review_period DAY)";
                $joins[] = 'LEFT JOIN #__jreviews_comments AS Review ON Listing.'.$this->Listing->realKey . ' = Review.pid';                
            }
            
            // Listings with review count higher than
            $filter_review_count = Sanitize::getInt($this->params['module'],'filter_review_count');
            $filter_review_count > 0 and $conditions[] = "Totals.user_rating_count >= $filter_review_count";
            
            // Listings with avg rating higher than
            $filter_avg_rating = Sanitize::getFloat($this->params['module'],'filter_avg_rating');
            $filter_avg_rating > 0 and $conditions[] = 'Totals.user_rating  >= ' . (float)$filter_avg_rating; 

		    $this->Listing->group = array();

			// Exlude listings without ratings from the results
			$join_direction = in_array($sort,array('rating','rrating','topratededitor','reviews')) ? 'INNER' : 'LEFT';
		                		
            $this->Listing->joins['Total'] = "$join_direction JOIN #__jreviews_listing_totals AS Totals ON Totals.listing_id = Listing.{$this->Listing->realKey} AND Totals.extension = '{$extension}'";
		    
            # Modify query for correct ordering. Change FIELDS, ORDER BY and HAVING BY directly in Listing Model variables
		    if($custom_order) 
            {
			    $this->Listing->order[] = $custom_order;			
		    } 
            elseif(empty($order) && $extension == 'com_content') 
            {
			    $this->Listing->processSorting($sort,'');			
		    } 
            elseif(empty($order) && $order = $this->_processSorting($sort)) 
            {
			    $order = array($order);	
		    }		
		    
		    $queryData = array(
			    'fields'=>array(
				'Totals.user_rating AS `Review.user_rating`',
				'Totals.user_rating_count AS `Review.user_rating_count`',
				'Totals.user_comment_count AS `Review.review_count`',
				'Totals.editor_rating AS `Review.editor_rating`',
				'Totals.editor_rating_count AS `Review.editor_rating_count`',
				'Totals.editor_comment_count AS `Review.editor_review_count`'
			    ),
			    'joins'=>$joins,
			    'conditions'=>$conditions,
                'limit'=>$this->module_limit,
			    'offset'=>$this->module_offset,
                'having'=>$having
		    );	

            isset($order) and !empty($order) and $queryData['order'] = $order;

		    // Trigger addFields for $listing results. Checked in Everywhere model
		    $this->Listing->addFields = true;
            $listings = $this->Listing->findAll($queryData);

		    if(Sanitize::getInt($this->params['module'],'ajax_nav',1)) 
		    {
			    unset(
				    $queryData['joins']['Section'],
				    $queryData['joins']['Category'],
				    $queryData['joins']['Directory'],
				    $queryData['joins']['Criteria'],
				    $queryData['joins']['User'],
				    $queryData['order']
			    );

			    $count = $this->Listing->findCount($queryData,'DISTINCT Listing.'.$this->Listing->realKey);		
		    } 
            else 
            {
			    
			    $count = $this->module_limit;
		    }
        } // end Listing class check
        else {
            $listings = array();
            $count = 0;
        }		    
        
        unset($this->Listing);

		# Send variables to view template		
		$this->set(array(
				'Access'=>$this->Access,
				'User'=>$this->_user,
				'subclass'=>'listing',
				'listings'=>$listings,
				'total'=>$count
		));
		
		$page = $this->render('modules','listings');
				
        if($this->ajaxRequest) 
        {
            return $this->ajaxResponse($page,false);
		} 
        else 
        {
			return $page;
		}		
	}
    
   /**
    * Modifies the query ORDER BY statement based on ordering parameters
    */
 	function _processSorting($selected) 
    {
		$order = '';

		switch ( $selected ) 
        {
            case 'rating':
                $order = 'Totals.user_rating DESC, Totals.user_rating_count DESC';
                $this->Listing->conditions[] = 'Totals.user_rating > 0';
              break;
            case 'rrating':
                $order = 'Totals.user_rating ASC, Totals.user_rating_count DESC';
                $this->Listing->conditions[] = 'Totals.user_rating > 0';
              break;
            case 'reviews':
              $order = 'Totals.user_comment_count DESC'; 
              $this->Listing->conditions[] = 'Totals.user_comment_count > 0';
              break;
            case 'rdate':
                $order =  $this->Listing->dateKey ? "Listing.{$this->Listing->dateKey} DESC" : false;
            break;
		}
	
		return $order;
	}
}