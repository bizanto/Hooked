<?php
defined('_JEXEC') or die ('Restricted Access');

require_once(JApplicationHelper::getPath('toolbar_html'));

switch($task)
{
	case 'edit':
		TOOLBAR_saasy::_EDIT();
		break;
	default:
		TOOLBAR_saasy::_DEFAULT();
		break;
	
}
?>
