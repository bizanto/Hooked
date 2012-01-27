<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class FieldOptionModel extends MyModel  {
		
	var $name = 'FieldOption';
	
	var $useTable = '#__jreviews_fieldoptions AS `FieldOption`';
	
	var $primaryKey = 'FieldOption.optionid';
	
	var $realKey = 'optionid';
	
	var $fields = array(
		'FieldOption.optionid AS `FieldOption.optionid`',
		'FieldOption.fieldid AS `FieldOption.fieldid`',
		'FieldOption.text AS `FieldOption.text`',
		'FieldOption.value AS `FieldOption.value`',
		'FieldOption.image AS `FieldOption.image`',
		'FieldOption.ordering AS `FieldOption.ordering`'	
	);
	
	/**
	 * These are characters that will be removed from the field option value
	 *
	 * @var array
	 */
	var $blackList = array('!','$','%','"','^','°','_','&','(',')','*',';',':','@','#',"'",'+','.',',','/','\\');
    // 03-20-2009 - removed equal sign to use as separator for relationship fields	
	/**
	 * The values in the array will be replaced with a dash "-"
	 *
	 * @var array
	 */
	var $dashReplacements = array(' ','_',',');
		
	/**
	 * Retrieves option list for 
	 *
	 * @param unknown_type $fieldid
	 * @param unknown_type $limitstart
	 * @param unknown_type $limit
	 * @param unknown_type $total
	 * @return unknown
	 */
	function getList($fieldid, $limitstart, $limit, &$total) {
	
		// get the total number of records
		$query = "SELECT COUNT(*) FROM `#__jreviews_fieldoptions` WHERE fieldid='$fieldid'";
		$this->_db->setQuery( $query );
		$total = $this->_db->loadResult();
	
		$sql = "SELECT * FROM #__jreviews_fieldoptions"
		. "\n WHERE fieldid = '$fieldid' ORDER BY ordering ASC, optionid ASC"
		. "\n LIMIT $limitstart, $limit"
		;
		$this->_db->setQuery($sql);
		$rows = $this->_db->loadObjectList();

		return $rows;
	}
	
	function save(&$data) {
		
		$isNew = Sanitize::getInt($data['FieldOption'],'optionid') ? false : true;
				
		$field_id = Sanitize::getInt($data['FieldOption'],'fieldid');
		
		if($isNew) {
			// Remove non alphanumeric characters from option value
            $data['FieldOption']['value'] = Sanitize::translate($data['FieldOption']['value']);
            $data['FieldOption']['value'] = str_replace($this->blackList,'',$data['FieldOption']['value']);    
			$data['FieldOption']['value'] = str_replace($this->dashReplacements,'-',$data['FieldOption']['value']);						
            $data['FieldOption']['value'] = preg_replace(array('/[-]+/'), array('-'), $data['FieldOption']['value']);    		
            $data['FieldOption']['value'] = mb_strtolower($data['FieldOption']['value'],'UTF-8');
		
			// If is new checks for duplicate value
			$query = "SELECT count(fieldid) FROM #__jreviews_fieldoptions WHERE fieldid = '$field_id' AND value = " . $this->_db->Quote($data['FieldOption']['value']);
			$this->_db->setQuery($query);
			if ($this->_db->loadResult()) {
				return 'duplicate';
			}
			
			// Find last option
			$this->_db->setQuery("select max(ordering) FROM #__jreviews_fieldoptions WHERE fieldid = '".$field_id."'");
			$max = $this->_db->loadResult();
			
			if ($max > 0) {
				$data['FieldOption']['ordering'] = $max+1; 
			} else {		
				$data['FieldOption']['ordering'] = 1;
			}			
		}
				
		# store it in the db
		if(!$this->store($data)) { 
			return 'db_error';
		}		
		
		return 'success';
		
	}
}