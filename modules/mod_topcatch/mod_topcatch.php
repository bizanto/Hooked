<?php
/**
 * @author     Kevin Strong
 * @email      kevin.strong@wearehathway.com
 * @website    http://www.wearehathway.com
 * @copyright  Copyright (C) 2011 Hathway
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// require ContentHelperRoute
require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$catches = modTopCatchHelper::getTopCatch($params);
$species = modTopCatchHelper::getSpeciesName($params);

require(JModuleHelper::getLayoutPath('mod_topcatch'));
