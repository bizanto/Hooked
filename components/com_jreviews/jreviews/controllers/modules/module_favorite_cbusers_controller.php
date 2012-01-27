<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class ModuleFavoriteCbusersController extends MyController {
	
	var $uses = array('user','menu','favorite');
	
	var $helpers = array('paginator','routes','libraries','html','assets','text','jreviews','community');
	
	var $components = array('config');

	var $autoRender = false;
	
	var $autoLayout = false;
		
	function beforeFilter() {
					
		# Call beforeFilter of MyController parent class
		parent::beforeFilter();		
	}
	
	function index()
	{
		$Session = RegisterClass::getInstance('MvcSession');
		$module_id = Sanitize::getInt($this->params,'module_id',Sanitize::getInt($this->data,'module_id'));
        if(!isset($this->params['module'])) $this->params['module'] = array(); // For direct calls to the controller

		if($this->ajaxRequest) {
			$this->params = $Session->get('module_params'.$module_id,null,S2Paths::get('jreviews','S2_CMSCOMP'));
		} else {
			srand((float)microtime()*1000000);
			$this->params['rand'] = rand();
			$Session->set('module_rand'.$module_id,$this->params['rand'],S2Paths::get('jreviews','S2_CMSCOMP'));
			$Session->set('module_params'.$module_id,$this->params,S2Paths::get('jreviews','S2_CMSCOMP'));
		}
		
		$this->viewSuffix = Sanitize::getString($this->params['module'],'tmpl_suffix');
				
		// Read the module parameters
		$img_width 		= Sanitize::getInt($this->params['module'],'img_width',50);
		$random_mode	= Sanitize::getString($this->params['module'],'random_mode','Random Users');
		$favorites_mode = Sanitize::getString($this->params['module'],'favorites_mode','Other users interested in {title}');

		// Pagination
		$this->Community->limit = $this->module_limit;
		$this->Community->offset = $this->module_offset;	

		# Get url params for current controller/action
		$url = Sanitize::getString($_REQUEST, 'url');
		$route['url']['url'] = $url;
		$route['data'] = array();
		$route = S2Router::parse($route,true,'jreviews');

		# Check if page is listing detail
		$detail = (Sanitize::getString($route['url'],'extension','com_content') == 'com_content') && isset($route['data']) && Sanitize::getString($route['data'],'controller') == 'listings' && Sanitize::getString($route['data'],'action') == 'detail' ? true : false;

		# Initialize variables
		$listing_id = $detail ? Sanitize::getInt($route,'id') : Sanitize::getInt($this->params,'id');
		$option = Sanitize::getString($this->params,'option');	
		$view = Sanitize::getString($this->params,'view');
		$task = Sanitize::getString($this->params,'task');
		$listing_title = '';

		# Article auto-detect - only for com_content
		if($detail || ('com_content' == $option && ('article' == $view || 'view' == $task))) {	
			$query = "SELECT Listing.id, Listing.title FROM #__content AS Listing WHERE Listing.id = " . $listing_id;
			$this->_db->setQuery($query);
			$listing = current($this->_db->loadObjectList());
			$listing_title = $listing->title;
		} else {
			$listing_id = null;
		}

		$profiles = $this->Community->getListingFavorites($listing_id, $this->_user->id, $this->params);

		$total = $this->Community->count;

		unset($this->Community->count);
		
		$this->set(array(
			'profiles'=>$profiles,
			'listing_title'=>$listing_title,
			'total'=>$total
		));

		$page = $this->render('modules','favorite_cbusers');

        if($this->ajaxRequest) {
            return $this->ajaxResponse($page,false);
        } else {
            return $page;
        }    
	}	
}