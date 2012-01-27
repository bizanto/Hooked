<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

(defined( '_VALID_MOS') || defined( '_JEXEC')) or die( 'Direct Access to this location is not allowed.' );

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

class sef_jreviews {
	
	var $__Menu;
		
	function &getInstance() {

		static $instance = array();

		if (!isset($instance[0]) || !$instance[0]) {
			$instance[0] = new sef_jreviews();
			require( dirname(__FILE__) . DS . 'jreviews' . DS . 'framework.php');

			App::import('Model','Menu','jreviews');

			$instance[0]->__Menu = &RegisterClass::getInstance('MenuModel');
		}
		
		return $instance[0];
	}

	function create($string) {

		$_this =& sef_jreviews::getInstance();
		
		$url = '';
		$hasMenuId = preg_match('/Itemid=([0-9]{1,})/',$string,$menu_id);
		$isMenuLink = strpos($string,'url=menu');
		
		$sefstring = '';
				
		// Process internal "url" parameter
		$temp = explode('&amp;url=', $string);

		if(isset($temp[1])) {
			$url = urldecode($temp[1]);
		}
		
		if (preg_match('/&amp;url=/',$string) && !$isMenuLink) 
		{
			$query_string = explode('/',$url);
			// Properly urlencodes the jReviews parameters contained within the url parameter
			foreach($query_string AS $key=>$param) {
				$query_string[$key] = urlencodeParam($param);
			}
			$url = implode('/',$query_string);
					
			$sefstring .= $url;
		
		} elseif(isset($menu_id[1]) && ($isMenuLink || $hasMenuId)) {

			$sefstring .= $isMenuLink ? str_replace('menu',$_this->__Menu->getMenuName($menu_id[1]),$url) : $_this->__Menu->getMenuName($menu_id[1]);
		
		} else {
			
			$sefstring = $string;
		
		}
	
		return rtrim($sefstring,'/').'/';
	}

	function revert ($url_array, $pos) {

		$_PARAM_CHAR = ':';
		$url = array();
		$_this =& sef_jreviews::getInstance();

		global $QUERY_STRING;
		
		// First check if this is a menu link by looking for the menu name to get an Itemid
		if(isset($url_array[$pos+2]) && $menu_id = $_this->__Menu->getMenuId($url_array[$pos+2])) {
			
			$_GET['Itemid'] = $_REQUEST['Itemid'] = $menu_id;
			$QUERY_STRING = "option=com_jreviews&Itemid=$menu_id";

			for($i=$pos+2;$i<count($url_array);$i++) {
				if($url_array[$i] != '' && false!==strpos($url_array[$i],$_PARAM_CHAR)) {
					$parts = explode($_PARAM_CHAR,$url_array[$i]);
					if(isset($parts[1]) && $parts[1]!='') {
						$url[] = $url_array[$i];
						$_GET[$parts[0]] = $_REQUEST[$parts[0]] = $parts[1];
					}
				}
			}

			$QUERY_STRING .= '&url=menu/' . implode('/',$url);

		} else {

			// Not a menu link, so we use the url named param
			for($i=$pos+2;$i<count($url_array);$i++) {
				if($url_array[$i] != '') {
					$url[] = $url_array[$i];
				}
			}

			$url = implode('/',$url);
	
			if(preg_match('/_m([0-9]+)/',$url,$matches)) {
				$menu_id = $_GET['Itemid'] = $_REQUEST['Itemid'] = $matches[1];
			} else {				
				$menu_id = $_GET['Itemid'] = $_REQUEST['Itemid'] = '';
			}			
			
			$_GET['url'] = $_REQUEST['url'] = $url;
			$_GET['option'] = $_REQUEST['option'] = 'com_jreviews';

			$QUERY_STRING = "option=com_jreviews&Itemid=$menu_id&url=$url";
					
		}
//			return $QUERY_STRING;		
	}

}