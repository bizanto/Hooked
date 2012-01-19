<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class LibrariesHelper extends MyHelper
{						
	function js()
	{
		$javascriptLibs = array();				
		$javascriptLibs['jreviews'] 			=	'jreviews';
        $javascriptLibs['jquery']               =   'jquery/jquery-1.4.2.min';
        $javascriptLibs['jq.ui.core']           =   'jquery/jquery-ui-1.8.5.custom.min';
        $javascriptLibs['jq.autocomplete']      =   'jquery/jquery.autocomplete';
		$javascriptLibs['jq.json']			    = 	'jquery/json.min'; 
		$javascriptLibs['jq.jsoncookie']		= 	'jquery/jquery.extjasoncookie-0.2';
		$javascriptLibs['jq.ui.autocomplete']   =   'jquery/ui.autocomplete.min';		
        $javascriptLibs['jq.ui.datepicker']     =   'jquery/ui.datepicker.min';
		$javascriptLibs['jq.ui.accordion']      =   'jquery/ui.accordion.min';
        $javascriptLibs['jq.ui.tabs']           =   'jquery/ui.tabs.min';
		$javascriptLibs['jq.ui.slider']         =   'jquery/ui.slider.min';
		$javascriptLibs['jq.ui.rating']			= 	'jquery/ui.stars';
        $javascriptLibs['jq.scrollable']        =   'jquery/jquery.scrollable';
		$javascriptLibs['jq.bgiframe']	 		= 	'jquery/jquery.bgiframe.min';
        $javascriptLibs['jq.thickbox']          =   'jquery/thickbox.min';        
        $javascriptLibs['jq.fancybox']          =   'jquery/jquery.fancybox-1.2.1';
		$javascriptLibs['jq.tooltip'] 			= 	'jquery/jquery.tooltip';
        $javascriptLibs['jq.popover']           =   'jquery/jquery.ezpz_tooltip';
		$javascriptLibs['jq.selectboxes'] 		= 	'jquery/jquery.selectboxes.min';
		$javascriptLibs['jq.treeview']			= 	'jquery/jquery.treeview.min';		
		$javascriptLibs['jq.jreviews.plugins'] 	= 	'jreviews.jquery.plugins';
		$javascriptLibs['jq.onload'] 			= 	'jquery.onload';
        
        if(!isset($this->Config) || empty($this->Config))
        {
            $this->Config = Configure::read('JreviewsSystem.Config');
        }
                
        if($this->Config->libraries_jquery && !defined('MVC_FRAMEWORK_ADMIN'))
        {
            unset($javascriptLibs['jquery']);
        }
         
        if($this->Config->libraries_jqueryui && !defined('MVC_FRAMEWORK_ADMIN'))
        {
            unset($javascriptLibs['jq.ui.core']);
        }   
        
		$exclude = Configure::read('Libraries.disableJS');

		if(is_array($exclude)){
			foreach($exclude AS $lib){
				if(isset($javascriptLibs[$lib])) unset($javascriptLibs[$lib]);
			}
		}
		
		return $javascriptLibs;
	}	
	
	function css()
	{
		$styleSheets = array();
        $styleSheets['modules']                 =   'modules';
        $styleSheets['plugins']                 =   'plugins';
		$styleSheets['theme']				 	= 	'theme';
		$styleSheets['theme.directory']	 		= 	'directory';
		$styleSheets['theme.list']		 		= 	'list';
		$styleSheets['theme.detail']		 	= 	'detail';	
        $styleSheets['theme.discussion']        =   'discussion';    
		$styleSheets['theme.form']		 		= 	'form';
		$styleSheets['paginator']				= 	'paginator';
        $styleSheets['jq.autocomplete']         =   'autocomplete/autocomplete';                
        $styleSheets['jq.ui.core']              =   'jquery_ui_theme/jquery-ui-1.8.5.custom';
		$styleSheets['jq.tooltip'] 				= 	'tooltip/jquery.tooltip';
		$styleSheets['jq.ui.rating'] 			= 	'rating/ui.stars';
        $styleSheets['jq.fancybox']             =   'fancybox/jquery.fancybox';        
		$styleSheets['jq.datepicker'] 			= 	'datepicker/ui.datepicker';
		$styleSheets['jq.treeview'] 			= 	'treeview/jquery.treeview';		

        if($this->Config->libraries_jqueryui && !defined('MVC_FRAMEWORK_ADMIN'))
        {
            unset($styleSheets['jq.ui.core']);
        }  

		return $styleSheets;
	}
}
