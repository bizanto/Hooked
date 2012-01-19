<?php
defined('_JEXEC') or die('Restricted Access');

// During ajax calls, the following constant might not be defined 
if(!defined('JPATH_COMPONENT'))
{
	define('JPATH_COMPONENT', dirname(__FILE__));
}

require_once(JPATH_COMPONENT.DS.'controller.php');

if (JRequest::getVar('task') != 'azrul_ajax') {
	if($controller = JRequest::getWord('controller')) {
	    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	    if (file_exists($path)) {
		require_once $path;
	    } else {
		$controller = '';
	    }
	}

	$classname = 'RelateController'.$controller;
	$controller = new $classname(); 

	$controller->execute(JRequest::getVar('task'));

	$controller->redirect();
}

function relateAjaxEntry($func, $args = null) {
	$lang =& JFactory::getLanguage();
	$lang->load('com_relate');

	$func = explode(",", $func);
	$func = $func[1];

	$controller = new RelateController();
	$output = call_user_func_array(array($controller, $func), $args);

	echo $output;
}

