<?php
defined('_JEXEC') or die ('Restricted Access');

require_once(JPATH_COMPONENT.DS.'controller.php');

$controller = new SaasyController();
$controller->_init();

$controller->execute(JRequest::getCmd('task','details'));
$controller->redirect();
?>
