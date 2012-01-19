<?php

/*
* Quickcontent Component for Joomla 1.5.x
* @version 1.0.1
* @Date 2009.08.04
* @copyright (C) 2009 Thomas Lengler
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* www.einszuzwei.de
*/

// no direct access
defined('_JEXEC') or die('Restricted access');


// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');

$controller	= new QuickcontentController( );

// Perform the Request task
$controller->execute( JRequest::getCmd('task'));
$controller->redirect();

?>
