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

class ModuleFieldsController extends MyController {
	
	var $uses = array('menu','field_option');
	
	var $helpers = array('routes','form','html','assets','text','custom_fields');
	
	var $components = array('config');

	var $autoRender = false;
	
	var $autoLayout = false;
		
	function beforeFilter() 
    {						
		$this->viewSuffix = Sanitize::getString($this->params['module'],'tmpl_suffix');
		
		# Set Theme	
		$this->viewTheme = $this->Config->template;
		$this->viewImages = S2Paths::get($this->app, 'S2_THEMES_URL') . $this->viewTheme . _DS . 'theme_images' . _DS;		

	}
	
	function index()
	{			
		global $Itemid;
		
		$cat_id = null;
		$conditions = array();
		$joins = array();
		$order = array();
        $menu_id = '';
        
		// Read module params
        $dir_id = Sanitize::getString($this->params['module'],'dir');
        $section_id = Sanitize::getString($this->params,'section');
        $cat_id = Sanitize::getString($this->params['module'],'cat');
        $criteria_id = Sanitize::getString($this->params['module'],'criteria');

		$itemid_options = Sanitize::getString($this->params['module'],'itemid_options' );
		$itemid_hc = Sanitize::getInt($this->params['module'],'hc_itemid' );		
		
        $field = Sanitize::paranoid(Sanitize::getString($this->params['module'],'field'),array('_'));
		$option_length = Sanitize::getInt($this->params['module'],'fieldoption_length');
		$custom_params = Sanitize::getString($this->params['module'],'custom_params');
		$sort = Sanitize::paranoid(Sanitize::getString($this->params['module'],'fieldoption_order'));
		
		# Set menu id
		switch($itemid_options) 
        {
			case 'none':
				$menu_id = '';
			break;
			case 'current':
			break;
			case 'hardcode':
				$menu_id = $itemid_hc;
			break;
		}
		
        # Category auto detect
        if(Sanitize::getInt($this->params['module'],'catauto')) 
        {            
            $ids = CommonController::_discoverIDs($this);
            extract($ids);
        }

		$this->FieldOption->modelUnbind(array(
		'FieldOption.value AS `FieldOption.value`',
		'FieldOption.fieldid AS `FieldOption.fieldid`',
		'FieldOption.image AS `FieldOption.image`',
		'FieldOption.ordering AS `FieldOption.ordering`',
		'FieldOption.optionid AS `FieldOption.optionid`',
		'FieldOption.text AS `FieldOption.text`',
		));

		$fields[] = 'FieldOption.optionid AS `FieldOption.optionid`';
		$fields[] = 'FieldOption.value AS `FieldOption.value`';
		
		if($option_length) {
			$fields[] = 'IF(CHAR_LENGTH(FieldOption.text)>'.$option_length.',CONCAT(SUBSTR(FieldOption.text,1,'.$option_length.'),"..."),FieldOption.text) AS `FieldOption.text`';
		} else {
			$fields[] = 'FieldOption.text AS `FieldOption.text`';			
		}
		$joins[] = 'INNER JOIN #__jreviews_fields AS Field ON Field.fieldid = FieldOption.fieldid';
		$order[] = 'FieldOption.'.$sort;
						
		$field_options = $this->FieldOption->findAll(array(
			'fields'=>$fields,	
			'conditions'=>'Field.name = ' . $this->quote($field),
			'joins'=>$joins,
			'order'=>$order
		));		
                                      
		# Send variables to view template		
		$this->set(array(
			'field'=>$field,
			'field_options'=>$field_options,
            'section_ids'=>$section_id,
			'category_ids'=>$cat_id,
			'criteria_id'=>$criteria_id,
			'menu_id'=>$menu_id,
			'custom_params'=>$custom_params	
		));

		return $this->render('modules','fields');

	}
}