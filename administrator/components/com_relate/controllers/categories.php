<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.controller');

class RelateControllerCategories extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);
	}
	
	function save() 
	{		
		$categories = JRequest::getVar('categories');
		$cids = "('".implode("','", $categories)."')";

		$db =& JFactory::getDBO();
		$q = "DELETE FROM #__relate_categories WHERE catID NOT IN $cids";
		$db->setQuery($q);
		$db->query();
		
		// build string of rows with category ids and default values
		$values = ''; $first = 1;
		foreach ($categories as $catID) {
			if (!$first) $values .= ",";
			else $first = 0;
			$values .= "('$catID', '', 2, 0, 0, '')";
		}
		$q = "INSERT INTO #__relate_categories VALUES $values ".
		     "ON DUPLICATE KEY UPDATE catID=catID"; // ignore row if id already exists
		$db->setQuery($q);
		$db->query();
		
		$link = 'index.php?option=com_relate&view=categories';
                $msg = 'Categories Updated';
		$this->setRedirect($link, $msg);
	}
	
	function cancel()
	{
		$link = 'index.php?option=com_relate&view=categories';
		$this->setRedirect($link);
	}
}
