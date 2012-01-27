<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

// no direct access
(defined( '_VALID_MOS') || defined( '_JEXEC')) or die( 'Direct Access to this location is not allowed.' );

define('_XAJAX_JREVIEWS', dirname(__FILE__));

// Define xajax functions
$xajaxFunctions[] = 'xajaxDispatch';

if(!function_exists('xajaxDispatch')) {
	function xajaxDispatch() {
	
		# MVC initalization script
		if (!defined('MVC_FRAMEWORK'))  require( dirname(dirname(__FILE__)) . DS . 'com_jreviews' . DS . 'jreviews' . DS . 'framework.php' );
	
		$objResponse = new xajaxResponse();
	
		# Debug
		if(S2_DEBUG == 0) {
			error_reporting(0);
		}
	
		# Function parameters
		$args = func_get_args();
	
		$controllerName = (string) array_shift($args);
		
		$action = (string) array_shift($args);
		
		$app = isset($args[0]) && is_string($args[0]) ? array_shift($args) : 'jreviews';
				
		App::import('Controller',$controllerName,$app);
		
		# remove admin path from controller name
		$controllerClass = inflector::camelize(str_replace(MVC_ADMIN._DS,'',$controllerName)) . 'Controller';
	
		$controller = new $controllerClass($app);
		
		$controller->app = $app;		
					
		$controller->passedArgs = array();
	
		
		if(isset($args[0]))
		{
			$post = S2Dispatcher::parseParamsAjax($args[0]);
			
			if(isset($post['data'])) { // pass form inputs to controller variable
				
				$rawData = $post['data'];
				$data = Sanitize::clean($post['data']);
				$data['__raw'] = $rawData;
				
				$controller->data = $data;
				
			}
					
			$controller->passedArgs = $post;
			$controller->params = $post;
				
		}	
		
		$controller->name = $controllerName;
	
		$controller->action = $action;
	
		$controller->autoLayout = false;
		
		$controller->autoRender = false;
			
		$controller->xajaxRequest = true;
	
		$controller->__initComponents();
	
		if(method_exists($controller,'beforeFilter')) {
			$controller->beforeFilter();
		}		
						
		$objResponse->loadCommands($controller->$action($args));
		
		return $objResponse;
	}
}