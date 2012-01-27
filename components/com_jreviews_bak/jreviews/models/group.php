<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class GroupModel extends MyModel  {
		
	var $name = 'Group';
	
	var $useTable = '#__jreviews_groups AS `Group`';
	
	var $primaryKey = 'Group.group_id';
	
	var $realKey = 'groupid';
	
	var $fields = array(
		'Group.groupid AS `Group.group_id`',
		'Group.name AS `Group.name`',
		'Group.title AS `Group.title`',
		'Group.type AS `Group.type`',
		'Group.ordering AS `Group.ordering`',
		'Group.showtitle AS `Group.showtitle`'		
	);
	
	function getList($type, $limitstart, $limit, &$total) {
		
		// get the total number of records
		$query = "SELECT COUNT(*) FROM `#__jreviews_groups` WHERE type='$type'";
		$this->_db->setQuery($query);
		$total = $this->_db->loadResult();
	
		$query = "SELECT `Group`.*, count(Field.fieldid) AS field_count"
		. "\n FROM #__jreviews_groups AS `Group`"
		. "\n LEFT JOIN #__jreviews_fields AS Field ON `Group`.groupid = Field.groupid"
		. "\n WHERE Group.type='$type'"
		. "\n GROUP BY `Group`.groupid"
		. "\n ORDER BY ordering LIMIT $limitstart, $limit"
		;
		$this->_db->setQuery($query);
		$rows = $this->_db -> loadObjectList();
		echo $this->_db->getErrorMsg();
		// Log message
		$message[] = '*********' . get_class($this) . ' | getList';
		$message[] = $this->_db->getQuery();
		$message[] = $this->_db->getErrorMsg();					
		appLogMessage($message, 'database');
		
		if(!$rows) {
			$rows = array();
		}
		return $rows;
	}
	
	function getSelectList($type) {
		
		$query = "SELECT groupid AS value, name AS text "
		. "\n FROM #__jreviews_groups"
		. "\n WHERE type= '$type' ORDER BY name"
		;
		
		$this->_db->setQuery($query);
		
		$results = $this->_db->loadObjectList();
		
		return $results;	
	}

}
