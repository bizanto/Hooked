<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.controller');

class RelateController extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);
	}
	
	function display()
	{	
		if (JRequest::getVar('view') == '') {
			JRequest::setVar('view', 'categories');
		}
		
		parent::display();
	}
}
