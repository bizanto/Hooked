<?php
/**
 * @author     Kevin Strong
 * @email      kevin.strong@wearehathway.com
 * @website    http://www.wearehathway.com
 * @copyright  Copyright (C) 2011 Hathway
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$document =& JFactory::getDocument();

$document->addStyleSheet(JURI::base().'modules/mod_slideshow/tmpl/mod_slideshow.css');

$items = modSlideShowHelper::getItems($params);

require(JModuleHelper::getLayoutPath('mod_slideshow'));
