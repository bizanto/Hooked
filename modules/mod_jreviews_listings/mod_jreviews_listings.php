<?php
/**
 * jReviews - Reviews Extension
 * Copyright (C) 2006 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

(defined( '_VALID_MOS') || defined( '_JEXEC')) or die( 'Direct Access to this location is not allowed.' );


# MVC initalization script
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if(defined('JPATH_SITE')){	
    $root = JPATH_SITE . DS;
} else {
    global $mainframe;
    $root = $mainframe->getCfg('absolute_path') . DS;
}
require($root . 'components' . DS . 'com_jreviews' . DS . 'jreviews' . DS . 'framework.php');

# Populate $params array with module settings
$moduleParams['module'] = stringToArray($params->_raw);
$moduleParams['module_id'] = $module->id;
$moduleParams['page'] = 1;
$moduleParams['data']['module'] = true;
$moduleParams['data']['controller'] = 'module_listings';
$moduleParams['data']['action'] = 'index';


$Dispatcher = new S2Dispatcher('jreviews',(bool)$moduleParams['module']['ajax_nav']);
echo $Dispatcher->dispatch($moduleParams);
unset($Dispatcher);
