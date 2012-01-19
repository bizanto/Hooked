<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.controller');

class RelateControllerRelations extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);
	}
	
	function save()
	{		
		$db =& JFactory::getDBO();
		
		$relations = JRequest::getVar('relations', array());
		
		foreach ($relations as $catID => $relatable) {
			if ($relatable == '-') $relatable = '';
			$q = "UPDATE #__relate_categories SET relatable='$relatable' ".
			     "WHERE catID = '$catID'";
			$db->setQuery($q);
			$db->query();
		}
		
		$link = "index.php?option=com_relate&view=relations";
		$msg = "Category Relations Updated";
		$this->setRedirect($link, $msg);
	}
	
	function cancel()
	{
		$link = "index.php?option=com_relate&view=relations";
		$this->setRedirect($link);
	}
	
	function srcOptions()
	{
		$db =& JFactory::getDBO();
		
		$q = "SELECT catID, relatable FROM #__relate_categories";
		$db->setQuery($q);
		
		$categories = $db->loadObjectList('catID');
		
		echo json_encode($categories);
		exit();
	}
}
