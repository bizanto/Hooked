<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.controller');

class RelateControllerRules extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);
	}
	
	function save()
	{
		$db =& JFactory::getDBO();
		
		$rules = JRequest::getVar('setRules', array());
				
		foreach ($rules as $catID => $rules) {
			$set = '';
			$first = 1;
			foreach ($rules as $key => $val) {
				if (!$first) $set .= ",";
				else $first = 0; 
				$set .= "`$key`='$val'";
			}
			
			$q = "UPDATE `#__relate_categories` SET $set WHERE `catID`='$catID'";
			$db->setQuery($q);
			$db->query();
		}
		
		$link = 'index.php?option=com_relate&view=rules';
		$msg = "Access Rules Updated";
		$this->setRedirect($link, $msg);
	}
	
	function cancel()
	{
		$link = 'index.php?option=com_relate&view=rules';
		$this->setRedirect($link);
	}
	
	function srcOptions()
	{
		$db =& JFactory::getDBO();
		
		$q = "SELECT catID, author, featured, can_add, menu_link FROM #__relate_categories";
		$db->setQuery($q);
		
		$categories = $db->loadObjectList('catID');
		
		echo json_encode($categories);
		exit();
	}
}
