<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

(defined( '_VALID_MOS') || defined( '_JEXEC')) or die( 'Direct Access to this location is not allowed.' );

# MVC initalization script
define('S2_CORE_INCLUDE_PATH','components' . DS . 'com_s2' . DS . 's2framework');	
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
require( dirname(__FILE__) . DS . 'jreviews' . DS. 'framework.php');

function JreviewsBuildRoute(&$query){
	
	global $Menu;

	$segments = array();
	if(isset($query['url'])) 
    {
		$query_string = explode('/',$query['url']);
		// Properly urlencodes the jReviews parameters contained within the url parameter
		foreach($query_string AS $key=>$param) {
			$query_string[$key] = urlencodeParam($param);
		}
		$query['url'] = implode('/',$query_string);
		$segments[0] = $query['url'];         
		unset($query['url']);

        // Forces Joomla to use the menu alias for JReviews menus of type url instead of /component/jreviews
        if(isset($query['Itemid']))
        {
            App::import('Model','menu','jreviews');
            $MenuModel = RegisterClass::getInstance('MenuModel');
            if(isset($MenuModel->menues[$query['Itemid']]) && $MenuModel->menues[$query['Itemid']]->componentid==0)
            {
                $query['option'] = '';
            }
        }    
    }
        
	return $segments;
}

function JreviewsParseRoute($segments)
{ 
	$vars = array();
	$vars['url'] = implode('/',$segments);

	return $vars;
}