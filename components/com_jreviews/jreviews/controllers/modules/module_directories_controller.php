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

class ModuleDirectoriesController extends MyController {
	
	var $uses = array('user','menu','directory');
	
	var $components = array('config','access');
	
	var $helpers = array('routes','libraries','html','assets','jreviews');
		
	var $autoRender = false;
	
	var $autoLayout = false;

	var $layout = 'module';
	
	function beforeFilter() {
		
		# Call beforeFilter of MyController parent class
		parent::beforeFilter();
		
		$this->Directory->Config = & $this->Config;

		# Change render controller/view
		$this->viewSuffix = Sanitize::getString($this->params['module'],'tmpl_suffix');
	}	
		
	function index($params) 
    {
		$this->action = 'directory'; // Set view file
		
		# Read module params
		$dir_id = cleanIntegerCommaList(Sanitize::getString($this->params['module'],'dir_ids'));
		
		$conditions = array();
		$order = array();
		$cat_id = '';
		$section_id = '';
		
		$directories = $this->Directory->getTree($dir_id,true /* called from module */);
        
		if($menu_id = Sanitize::getInt($this->params,'Itemid')) {
			$menuParams = $this->Menu->getMenuParams($menu_id);		
		}
		
       # Category auto detect
        $ids = CommonController::_discoverIDs($this);
        extract($ids);

        if($cat_id != '' && $section_id == '') 
        {
            $cat_id = cleanIntegerCommaList($cat_id);
            $sql = "SELECT section FROM #__categories WHERE id IN (" . $cat_id . ")";
            $this->_db->setQuery($sql);
            $section_id = $this->_db->loadResult();
        }

		$this->set(array(
			'directories'=>$directories,
			'cat_id'=>is_numeric($cat_id) && $cat_id >0 ? $cat_id : false,
			'section_id'=>$section_id
			)
		);

		return $this->render('modules','directories');
	}
}
