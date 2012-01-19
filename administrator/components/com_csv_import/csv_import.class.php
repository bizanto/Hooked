<?php
/**
 * CSV Import Component for Content and jReviews
 * Copyright (C) 2008 NakedJoomla and Alejandro Schmeichler
 * This is not free software. Do not distribute it.
 * For license information visit http://www.nakedjoomla.com/license/csv_import_license.html
 * or contact info@nakedjoomla.com
**/

// no direct access
(defined('_VALID_MOS') OR defined('_JEXEC')) or die('Direct Access to this location is not allowed.');

class mosCSVProfiler extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $name				= null;
	/** @var string */
	var $description				= null;
	/** @var int */
	var $number_run				= 0;
	/** @var datetime */
	var $last_run				= null;
	/** @var datetime */
	var $created		= null;	
	/** @var string */
	var $column_separator				= ",";	
	/** @var int */
	var $author_id				= 0;
	/** @var int */
	var $section_id				= 0;
	/** @var int */
	var $category_id				= 0;
	/** @var int */
	var $published				= 0;
	/** @var string */
	var $meta_keys				= null;
	/** @var string */
	var $meta_des				= null;
	/** @var datetime */
	var $created_date				= null;
	/** @var datetime */
	var $publish_up_date				= null;
	/** @var datetime */
	var $publish_down_date				= null;
	/** @var int */
	var $access_level				= null;	
	/**
	* @param database A database connector object
	*/
	function mosCSVProfiler( &$db ) {
		$this->mosDBTable( '#__im_profiler', 'id', $db );
	}
}
?>	