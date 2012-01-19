<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');



switch($task)
{
	default:
		showDefaultView($option);
	break;
}

function showDefaultView($option)
{

	$controller = new ResetPasswordController() ;
	
	// Perform the Request task
	$task = JRequest::getCmd('task', 'display' ) ; //default = display
	$controller->execute($task);
	
	// Redirect if set by the controller
	$controller->redirect();
	
}

?>