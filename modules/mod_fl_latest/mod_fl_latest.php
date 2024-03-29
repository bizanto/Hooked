<?php
/**
* @version		$Id: mod_fl_latest.php $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

    // Add style sheet
    $document	= & JFactory::getDocument();
    $document->addStyleSheet(JURI::base(true).'/modules/mod_fl_latest/assets/css/mod_fl_latest.css');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$list = modFenrisLatestHelper::getList($params);

require(JModuleHelper::getLayoutPath('mod_fl_latest'));
