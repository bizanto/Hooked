<?php
/*
 * Created on Jan 25, 2011
 *
 */
 
 // no direct access
defined('_JEXEC') or die('Restricted access');

 
// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');

$controller = new ResetPasswordController() ;

// Perform the Request task
$task = JRequest::getCmd('task', 'display' ) ; //default = display
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();


?>