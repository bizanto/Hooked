<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class CommunityListingsController extends MyController {
	
	var $uses = array('user','menu','criteria','field','favorite');
	
	var $helpers = array('cache','routes','libraries','html','assets','text','jreviews','time','paginator','rating','thumbnail','custom_fields','community');
	
	var $components = array('config','access','everywhere');

	var $autoRender = false; //Output is returned
	
	var $autoLayout = false;
			
	function beforeFilter() {

		# Call beforeFilter of MyController parent class
		parent::beforeFilter();
						
	}
			
	function favorites()
	{		
		// Required for ajax pagination to remember module settings
		$Session = RegisterClass::getInstance('MvcSession');
		$module_id = Sanitize::getString($this->params,'module_id',Sanitize::getString($this->data,'module_id'));
		$extension = 'com_content';
		
		$cache_file = $module_id.'_'.md5(serialize($this->params));
		
		if($this->ajaxRequest) {
			$this->params = $Session->get($module_id,null,S2Paths::get('jreviews','S2_CMSCOMP'));
		} else {
			srand((float)microtime()*1000000);
			$this->params['rand'] = rand();
//			$Session->set('rand_'.$module_id,$this->params['rand'],S2Paths::get('jreviews','S2_CMSCOMP'));
			$Session->set($module_id,$this->params,S2Paths::get('jreviews','S2_CMSCOMP'));
		}

		if(!Sanitize::getVar($this->params['module'],'community')) {
			cmsFramework::noAccess();
			return;
		}				
		
		if($this->_user->id === 0) {
			$this->cacheAction = Configure::read('Cache.expires');
		}	
				
		// Automagically load and initialize Everywhere Model
		App::import('Model','everywhere_'.$extension,'jreviews');
		$class_name = inflector::camelize('everywhere_'.$extension).'Model';
		$this->Listing = new $class_name();
		$this->Listing->_user = $this->_user;		
		
		$action = Sanitize::paranoid($this->action);
        $dir_id = Sanitize::getString($this->params['module'],'dir');
        $section_id = Sanitize::getString($this->params['module'],'section');
        $cat_id = Sanitize::getString($this->params['module'],'category');
        $listing_id = Sanitize::getString($this->params['module'],'listing');        
		$user_id = Sanitize::getInt($this->params,'user',$this->_user->id);
		$index = Sanitize::getString($this->params,'index');
		$sort = Sanitize::getString($this->params['module'],'listings_order');
		$menu_id = Sanitize::getInt($this->params,'menu',Sanitize::getString($this->params,'Itemid'));
		$listings = array();
		$count = 0;
		
		if(!$user_id && !$this->_user->id) {
			cmsFramework::noAccess();
			return;					
		}	
							
		# Remove unnecessary fields from model query
		$this->Listing->modelUnbind('Listing.fulltext AS `Listing.description`');
				
		$conditions = array();
		$joins = array();
		
		# Get listings
		$joins[] = 	'INNER JOIN #__jreviews_favorites AS Favorite ON Listing.id = Favorite.content_id AND Favorite.user_id = ' . $user_id;
        
        # Set conditionals based on configuration parameters
        if($extension == 'com_content') 
        { // Only works for core articles 
            $conditions = array_merge($conditions,array(
                '( Listing.publish_up = "'.NULL_DATE.'" OR DATE(Listing.publish_up) <= DATE("'._CURRENT_SERVER_TIME.'") )',
                '( Listing.publish_down = "'.NULL_DATE.'" OR DATE(Listing.publish_down) >= DATE("'._CURRENT_SERVER_TIME.'") )',
                'Listing.access <= ' . $this->_user->gid,
                'Listing.catid > 0'
            )); 
                
            $conditions[] = $this->Access->canEditListing() ? 'Listing.state >= 0' :  'Listing.state = 1';
          
            if($dir_id) {
                $conditions[] = 'JreviewsCategory.dirid IN (' . $dir_id . ')';
            }
    
            if($section_id) {    
                $conditions[] = 'Listing.sectionid IN (' . $section_id. ')';
            }
    
            if($cat_id) {    
                $conditions[] = 'Listing.catid IN (' . $cat_id. ')';
            }
        }       
        
        if($listing_id) {    
            $conditions[] = "Listing.id IN ($listing_id)";
        }        
        
		switch($sort) {
			case 'random':
				$this->Listing->order = array();
				$order[] = "RAND({$this->params['rand']})";				
				break;
			default:
				$this->Listing->order = array();
				$order[] = "Listing.{$this->Listing->dateKey} DESC";
				break;	
		}

		$queryData = array(
//			'fields' they are set in the model
			'joins'=>$joins,
			'conditions'=>$conditions,
			'order'=>$order,			
			'limit'=>$this->module_limit,
			'offset'=>$this->module_offset
		);

		// This is used in Listings model to know whether this is a list page to remove the plugin tags
		$this->Listing->controller = 'categories';

        // Add custom fields to listings
        $this->Listing->addFields = true;
        
		$listings = $this->Listing->findAll($queryData);

		$count = 0;
		if(!empty($listings)){
			unset($queryData['order']);
			$count = $this->Listing->findCount($queryData,'DISTINCT Listing.id');
			
			if(Sanitize::getInt($this->data,'total_special') && Sanitize::getInt($this->data,'total_special') < $count) {
				$count = Sanitize::getInt($this->data,'total_special');
			}
		}
		
		# Send variables to view template		
		$this->set(
			array(
				'Access'=>$this->Access,
				'User'=>$this->_user,
				'listings'=>$listings,
				'total'=>$count,
				'module_id'=>$module_id
				)	
		);

		$page = $this->render('community_plugins','community_myfavorites');

        if($this->ajaxRequest) {
            return $this->ajaxResponse($page,false);
        } else {
            return $page;
        } 
	}
	
	function mylistings()
	{		
		// Required for ajax pagination to remember module settings
		$Session = RegisterClass::getInstance('MvcSession');
		$module_id = Sanitize::getString($this->params,'module_id',Sanitize::getString($this->data,'module_id'));

		$extension = 'com_content';

		$cache_file = $module_id.'_'.md5(serialize($this->params));
		
		if($this->ajaxRequest) {
			$this->params = $Session->get($module_id,null,S2Paths::get('jreviews','S2_CMSCOMP'));
		} else {
			srand((float)microtime()*1000000);
			$this->params['rand'] = rand();
			$Session->set($module_id,$this->params,S2Paths::get('jreviews','S2_CMSCOMP'));
		}

		if(!Sanitize::getVar($this->params['module'],'community')) {
			cmsFramework::noAccess();
			return;
		}				
		
		if($this->_user->id === 0) {
			$this->cacheAction = Configure::read('Cache.expires');
		}	

		// Automagically load and initialize Everywhere Model
		App::import('Model','everywhere_'.$extension,'jreviews');
		$class_name = inflector::camelize('everywhere_'.$extension).'Model';
		$this->Listing = new $class_name();
		$this->Listing->_user = $this->_user;		
		
		$action = Sanitize::paranoid($this->action);
		$dir_id = Sanitize::getString($this->params['module'],'dir');
		$section_id = Sanitize::getString($this->params['module'],'section');
		$cat_id = Sanitize::getString($this->params['module'],'category');
        $listing_id = Sanitize::getString($this->params['module'],'listing');        
		$user_id = Sanitize::getInt($this->params,'user',$this->_user->id);
		$index = Sanitize::getString($this->params,'index');
		$sort = Sanitize::getString($this->params['module'],'listings_order');
		$menu_id = Sanitize::getInt($this->params,'menu',Sanitize::getString($this->params,'Itemid'));
        
		$listings = array();
		$count = 0;
		
		if(!$user_id && !$this->_user->id) {
			cmsFramework::noAccess();
			return;					
		}	
					
		# Remove unnecessary fields from model query
		$this->Listing->modelUnbind('Listing.fulltext AS `Listing.description`');
				
		$conditions = array();
		$joins = array();
		
		# Get listings
		$conditions[] = 'Listing.created_by = ' . (int) $user_id;

        # Set conditionals based on configuration parameters
        if($extension == 'com_content') 
        { // Only works for core articles    
            if($dir_id) {
                $conditions[] = 'JreviewsCategory.dirid IN (' . $dir_id . ')';
            }
    
            if($section_id) {    
                $conditions[] = 'Listing.sectionid IN (' . $section_id. ')';
            }
    
            if($cat_id) {    
                $conditions[] = 'Listing.catid IN (' . $cat_id. ')';
            }
        }       
        
        if($listing_id) {    
            $conditions[] = "Listing.id IN ($listing_id)";
        }        
        
        if($extension == 'com_content') 
        { // Only works for core articles        
            if ( $this->Access->canEditListing() ) {
                $conditions[] = 'Listing.state >= 0';
            } else {
                $conditions[] = 'Listing.state = 1';
                $conditions[] = '( Listing.publish_up = "'.NULL_DATE.'" OR Listing.publish_up <= "'._CURRENT_SERVER_TIME.'" )';
                $conditions[] = '( Listing.publish_down = "'.NULL_DATE.'" OR Listing.publish_down >= "'._CURRENT_SERVER_TIME.'" )';
            }
            
            //Shows only links users can access
            $conditions[] = 'Listing.access <= ' . $this->_user->gid;
            $conditions[] = 'Listing.catid > 0';                
        }                          

		switch($sort) {
			case 'random':
				$this->Listing->order = array();
				$order[] = "RAND({$this->params['rand']})";				
				break;
			default:
				$this->Listing->order = array();
				$order[] = "Listing.{$this->Listing->dateKey} DESC";
				break;	
		}

		$queryData = array(
//			'fields' they are set in the model
			'joins'=>$joins,
			'conditions'=>$conditions,
			'order'=>$order,			
			'limit'=>$this->module_limit,
			'offset'=>$this->module_offset
		);

		// This is used in Listings model to know whether this is a list page to remove the plugin tags
		$this->Listing->controller = 'categories';

        // Add custom fields to listings
        $this->Listing->addFields = true;
                
		$listings = $this->Listing->findAll($queryData);

		$count = 0;
		
		if(!empty($listings)) {
			unset($queryData['order']);
			$count = $this->Listing->findCount($queryData,'DISTINCT Listing.id');
			
			if(Sanitize::getInt($this->data,'total_special') && Sanitize::getInt($this->data,'total_special') < $count) {
				$count = Sanitize::getInt($this->data,'total_special');
			}
		}

		# Send variables to view template		
		$this->set(
			array(
				'Access'=>$this->Access,
				'User'=>$this->_user,
				'listings'=>$listings,
				'total'=>$count,
				'module_id'=>$module_id
				)	
		);

		$page = $this->render('community_plugins','community_mylistings');

        if($this->ajaxRequest) {
            return $this->ajaxResponse($page,false);
        } else {
            return $page;
        } 
	}	
	
}