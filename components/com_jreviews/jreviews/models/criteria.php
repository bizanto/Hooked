<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2008 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class CriteriaModel extends MyModel  {
	
	var $name = 'Criteria';
	
	var $useTable = '#__jreviews_criteria AS Criteria';
	
	var $primaryKey = 'Criteria.criteria_id';
	
	var $realKey = 'id';
	
	var $fields = array(
		'Criteria.id AS `Criteria.criteria_id`',
		'Criteria.title AS `Criteria.title`',
		'Criteria.criteria AS `Criteria.criteria`',
		'Criteria.required AS `Criteria.required`',
		'Criteria.weights AS `Criteria.weights`',
		'Criteria.tooltips AS `Criteria.tooltips`',
		'Criteria.qty AS `Criteria.quantity`',
		'Criteria.groupid AS `Criteria.group_id`',
		'Criteria.state AS `Criteria.state`',
        'Criteria.config AS `ListingType.config`'  # Configuration overrides
	);
					
	function getList() {
	
		$query = "SELECT * from #__jreviews_criteria order by title ASC";
		
		$this->_db->setQuery($query);
		
		$rows = $this->_db->loadObjectList();
		
		return $rows;
	
	}
	
	function getSelectList($criteria_id = null) {

		$query = "SELECT id AS value, title AS text"
		. "\n FROM #__jreviews_criteria"
		. ($criteria_id ? "\n WHERE id = " . $criteria_id : '')
		. "\n ORDER BY title ASC"
		;
		
		$this->_db->setQuery($query);
		
		$results = $this->_db->loadObjectList();
		
		return $results;
	
	}
	
	/**
	 * Returns criteria set
	 *
	 * @param array $data has extension, cat_id or criteria_id keys=>values
	 */
	function getCriteria($data) 
    {
		if(isset($data['criteria_id'])) {
			$conditions = array('Criteria.id = ' . Sanitize::getInt($data,'criteria_id'));
			$joins = array();
		} elseif(isset($data['cat_id'])) {
			$conditions = array('JreviewCategory.id = ' . Sanitize::getInt($data,'cat_id'));
			$joins = array("INNER JOIN #__jreviews_categories AS JreviewCategory ON Criteria.id = JreviewCategory.criteriaid AND JreviewCategory.`option` = '{$data['extension']}'");			
		}
		$queryData = array('conditions'=>$conditions,'joins'=>$joins);

		$results = $this->findRow($queryData);
		
		if(isset($results['Criteria']['criteria']) && $results['Criteria']['criteria'] != '') {
			$results['Criteria']['criteria'] = explode("\n",$results['Criteria']['criteria']);
		}

		if(isset($results['Criteria']['tooltips']) && $results['Criteria']['tooltips'] != '') {
			$results['Criteria']['tooltips'] = explode("\n",$results['Criteria']['tooltips']);
		}

		if(isset($results['Criteria']['weights']) && $results['Criteria']['weights'] != '') {
			$results['Criteria']['weights'] = explode("\n",$results['Criteria']['weights']);
		}
		return $results;
	}
    
    function afterFind($results)
    {
        foreach($results AS $key=>$result)
        {
            isset($result['ListingType']['config']) and $results[$key]['ListingType']['config'] = json_decode($result['ListingType']['config'],true);
        }
        return $results;
    }

}