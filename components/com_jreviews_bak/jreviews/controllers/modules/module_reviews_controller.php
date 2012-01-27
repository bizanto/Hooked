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

class ModuleReviewsController extends MyController 
{
	var $uses = array('user','menu','category','review','field','criteria');
	
	var $helpers = array('paginator','routes','libraries','html','assets','text','time','jreviews','community','custom_fields','rating','thumbnail');
	
	var $components = array('config','access','everywhere');
	
	var $autoRender = false;
	
	var $autoLayout = false;
	
	var $layout = 'module';
			
	function beforeFilter() {
					
		# Call beforeFilter of MyController parent class
		parent::beforeFilter();
		
		# Stop AfterFind actions in Review model
		$this->Review->rankList = false;		
		
	}
	   
    // Need to return object by reference for PHP4
    function &getEverywhereModel() {
        return $this->Review;
    }           
	
	function index()
	{					
		$this->EverywhereAfterFind = true; // Triggers the afterFind in the Observer Model
		
        if(!isset($this->params['module'])) $this->params['module'] = array(); // For direct calls to the controller
        
		// Required for ajax pagination to remember module settings
		$Session = RegisterClass::getInstance('MvcSession');
		$module_id = Sanitize::getInt($this->params,'module_id',Sanitize::getInt($this->data,'module_id'));
		
		if($this->ajaxRequest) 
            {
			    $this->params = $Session->get('module_params'.$module_id,null,S2Paths::get('jreviews','S2_CMSCOMP'));
            }
         else 
            {
			    srand((float)microtime()*1000000);
			    $this->params['rand'] = rand();
			    $Session->set('module_rand'.$module_id,$this->params['rand'],S2Paths::get('jreviews','S2_CMSCOMP'));
			    $Session->set('module_params'.$module_id,$this->params,S2Paths::get('jreviews','S2_CMSCOMP'));
		    }

		$this->viewSuffix = Sanitize::getString($this->params['module'],'tmpl_suffix');
				
		$conditions = array();
		$joins = array();
		$order = array();

		# Read module parameters
		$extension = Sanitize::getString($this->params['module'],'extension');
        $reviews_type = Sanitize::getString($this->params['module'],'reviews_type');
        $custom_where = Sanitize::getString($this->params['module'],'custom_where');
		$cat_id = Sanitize::getString($this->params['module'],'category');
		$listing_id = Sanitize::getString($this->params['module'],'listing');
			
		if($extension == 'com_content') {
			$dir_id = Sanitize::getString($this->params['module'],'dir');
			$section_id = Sanitize::getString($this->params['module'],'section');
			$criteria_id = Sanitize::getString($this->params['module'],'criteria');
		} else {		
			$dir_id = null;
			$section_id = null;
			$criteria_id = null;
		}

		// This parameter determines the module mode
		$sort = Sanitize::getString($this->params['module'],'reviews_order');
		
		# Category auto detect
        if(Sanitize::getInt($this->params['module'],'cat_auto') && $extension == 'com_content') 
		{			
            $ids = CommonController::_discoverIDs($this);
            extract($ids);
        }

		$extension != '' and $conditions[] =  "Review.mode = '$extension'"; 
				
		# Set conditionals based on configuration parameters
		if($extension == 'com_content') 
		{ 
            $conditions = array_merge($conditions,array(
                'Listing.state = 1',
                '( Listing.publish_up = "'.NULL_DATE.'" OR DATE(Listing.publish_up) <= DATE("'._CURRENT_SERVER_TIME.'") )',
                '( Listing.publish_down = "'.NULL_DATE.'" OR DATE(Listing.publish_down) >= DATE("'._CURRENT_SERVER_TIME.'") )',
                'Listing.access <= ' . $this->_user->gid
            ));   

            $cat_id != '' and $conditions[] = 'Listing.catid IN (' . $cat_id. ')';

            $cat_id == '' and $section_id != '' and $conditions[] = 'Listing.sectionid IN (' . $section_id. ')';

            $cat_id == '' and $dir_id != '' and $conditions[] = 'JreviewsCategory.dirid IN (' . $dir_id . ')';
    
            $cat_id == '' and $criteria_id != '' and $conditions[] = 'JreviewsCategory.criteriaid IN (' . $criteria_id . ')';

		} 
        else 
        {
			if(Sanitize::getInt($this->params['module'],'cat_auto') && isset($this->Listing) && method_exists($this->Listing,'catUrlParam')) {
				if($cat_id = Sanitize::getInt($this->passedArgs,$this->Listing->catUrlParam())){
					$conditions[] = 'JreviewsCategory.id IN (' . $cat_id. ')';
				}
			} elseif($cat_id) {	
				$conditions[] = 'JreviewsCategory.id IN (' . $cat_id. ')';
			}		
		}
		
		$listing_id and $conditions[] = "Review.pid IN ($listing_id)";
		                                        
		$conditions[] = 'Review.published > 0';	
	
		switch($sort) {
			case 'latest':
				$order[] = $this->Review->processSorting('rdate');
				break;
			case 'helpful':
				$order[] = $this->Review->processSorting('helpful');
				break;				
			case 'random':
				$order[] = 'RAND('.$this->params['rand'].')';
				break;
			default:
				$order[] = $this->Review->processSorting('rdate');
				break;	
		}

        switch($reviews_type)
        {
            case 'all':
            break;
            case 'user':
                $conditions[] = 'Review.author = 0';    
            break;
            case 'editor':
                $conditions[] = 'Review.author = 1';    
            break;
        }
                
        # Custom WHERE
       $custom_where and $conditions[] = $custom_where;
                            
		$queryData = array(
			'joins'=>$joins,
			'conditions'=>$conditions,
			'order'=>$order,
			'limit'=>$this->module_limit,
			'offset'=>$this->module_offset
		);
		     
		# Don't run it here because it's run in the Everywhere Observer Component
		$this->Review->runProcessRatings = false;		
		
		// Excludes listing owner info in Everywhere component
		$this->Review->controller = 'module_reviews'; 
     
        $reviews = $this->Review->findAll($queryData);

		if(Sanitize::getInt($this->params['module'],'ajax_nav',1)) 
		{
			unset($queryData['order']);
			$count = $this->Review->findCount($queryData,'DISTINCT Review.id');				
		} 
        else 
        {
			$count = $this->module_limit;
		}

		# Send variables to view template		
		$this->set(
			array(
				'Access'=>$this->Access,
				'User'=>$this->_user,
				'reviews'=>$reviews,
				'total'=>$count				
				)
		);
		
		$page = $this->render('modules','reviews');

		if($this->ajaxRequest) 
        {
            return $this->ajaxResponse($page,false);
        } 
        else 
        {
			return $page;
		}				

	}
}