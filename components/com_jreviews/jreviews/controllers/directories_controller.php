<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class DirectoriesController extends MyController {
	
	var $uses = array('user','menu','directory');
	
	var $components = array('config','access');
	
	var $helpers = array('assets','cache','routes','libraries','html','jreviews');
	
	var $layout = 'directory';
	
	function beforeFilter() {
		
		# Call beforeFilter of MyController parent class
		parent::beforeFilter();
		
		$this->Directory->Config = & $this->Config;
		
	}
		
	function index($params) {
		
		if($this->_user->id === 0) {
			$this->cacheAction = Configure::read('Cache.expires');
		}		

		$this->action = 'directory'; // Set view file

		$page = array('title'=>'','show_title'=>0);
		$conditions = array();
		$order = array();
		
		$directories = $this->Directory->getTree(Sanitize::getString($this->params,'dir'));
        
		if($menu_id = Sanitize::getInt($this->params,'Itemid')) {
			$menuParams = $this->Menu->getMenuParams($menu_id);		
			$page['title'] = Sanitize::getString($menuParams,'title');
			$page['show_title'] = Sanitize::getString($menuParams,'dirtitle',0);
		}
					
		$this->set(array(
			'page'=>$page,
			'directories'=>$directories
			)
		);
	}		
	
}