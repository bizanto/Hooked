<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class EverywhereComComprofilerModel extends MyModel  {
		
	var $UI_name = 'Community Builder';
	
	/**
	 * groups = Core Access Groups. The categories are the Access Groups (i.e. guest, registered, manager, etc.) and you can assign different criterias to each category
	 * fields = CB Custom Field. The categories are the value options of a CB custom fields and you can assign different criterias to each category
	 */
	var $integrationMode = 'groups'; // groups | fields
	
	/**
	 * Create a field with the name shown below, or change it's value to an existing field name.
	 * The options of this field will be used as categories
	 * Make sure this is a required field and shown at registration time
	 */
	var $cbCustomField = 'cb_membertype';

	var $name = 'Listing';
	
	var $useTable = '#__users AS Listing';
	
	var $primaryKey = 'Listing.listing_id';
	
	var $realKey = 'id';
	
	/**
	 * Used for listing module - latest listings ordering
	 */	
	var $dateKey = 'registerDate';	
	
	var $extension = 'com_comprofiler';
	
	var $listingUrl = 'index.php?option=com_comprofiler&amp;task=userProfile&amp;user=%s&amp;Itemid=%s';
	
	var $categoryPrimaryKey;
		
	var $fields = array(
		'Listing.id AS `Listing.listing_id`',
		'Listing.username AS `Listing.title`',
		'CommunityBuilder.avatar AS `Listing.images`',
		'CommunityBuilder.avatarapproved AS `Listing.images_approved`',
		"'com_comprofiler' AS `Listing.extension`",
		'JreviewsCategory.id AS `Listing.cat_id`',
		'Category.name AS `Category.title`',
		'JreviewsCategory.id AS `Category.cat_id`',
		'Criteria.id AS `Criteria.criteria_id`',
		'Criteria.criteria AS `Criteria.criteria`',
		'Criteria.tooltips AS `Criteria.tooltips`',
		'Criteria.weights AS `Criteria.weights`',
        'Criteria.required AS `Criteria.required`',       
		'Criteria.state AS `Criteria.state`',
        'User.id AS `User.user_id`',
        'User.name AS `User.name`',
        'User.username AS `User.username`',
        'User.email AS `User.email`',
        'user_rating'=>'Totals.user_rating AS `Review.user_rating`'
	);
	
	// Done in the __construct function below because it's CMS dependent for categoryPrimaryKey
	var $joins = array();
	
	var $joinsReviews = array();
	
	// Module controller includes the basic joins for review and rating information and others can be added here
	// depending on the fields used in the query
	var $joinsListingsModule = array();		
		
	function __construct() {
		
		parent::__construct();
			
		switch($this->integrationMode) {
			
			case 'groups':
				// The primary key for categories
				switch(getCmsVersion()) {
					case CMS_JOOMLA10: 
					case CMS_MAMBO46:
						$this->categoryPrimaryKey = 'group_id';
					break;
					case CMS_JOOMLA15:
						$this->categoryPrimaryKey = 'id';
					break;
				}
				
				$this->joins = array(
                'Total'=>"LEFT JOIN #__jreviews_listing_totals AS Totals ON Totals.listing_id = Listing.id AND Totals.extension = 'com_comprofiler'",
					"LEFT JOIN #__core_acl_aro_groups AS Category ON Listing.gid = Category.{$this->categoryPrimaryKey}",
					"INNER JOIN #__jreviews_categories AS JreviewsCategory ON Listing.gid = JreviewsCategory.id AND JreviewsCategory.`option` = 'com_comprofiler'",
					'LEFT JOIN #__jreviews_criteria AS Criteria ON JreviewsCategory.criteriaid = Criteria.id',
					'LEFT JOIN #__comprofiler AS CommunityBuilder ON Listing.id = CommunityBuilder.id',
                    "LEFT JOIN #__users AS User ON User.id = Listing.id"                       
				);
				
				$this->joinsReviews = array(
					"LEFT JOIN #__users AS Listing ON Review.pid = Listing.id",
					"LEFT JOIN #__core_acl_aro_groups AS Category ON Listing.gid = Category.{$this->categoryPrimaryKey}",
					"INNER JOIN #__jreviews_categories AS JreviewsCategory ON Listing.gid = JreviewsCategory.id AND JreviewsCategory.`option` = 'com_comprofiler'",
					'LEFT JOIN #__jreviews_criteria AS Criteria ON JreviewsCategory.criteriaid = Criteria.id'
				);
				break;
			case 'fields':				
				$this->fields = array(
					'Listing.id AS `Listing.listing_id`',
					'Listing.username AS `Listing.title`',
					'CommunityBuilder.avatar AS `Listing.images`',
					'CommunityBuilder.avatarapproved AS `Listing.images_approved`',
					"'com_comprofiler' AS `Listing.extension`",
					'JreviewsCategory.id AS `Listing.cat_id`',
					'Category.fieldtitle AS `Category.title`',
					'JreviewsCategory.id AS `Category.cat_id`',
					'Criteria.id AS `Criteria.criteria_id`',
					'Criteria.criteria AS `Criteria.criteria`',
					'Criteria.tooltips AS `Criteria.tooltips`',
					'Criteria.weights AS `Criteria.weights`',
                    'Criteria.required AS `Criteria.required`',       
					'Criteria.state AS `Criteria.state`'
				);
				
				$this->joins = array(
					'INNER JOIN #__comprofiler AS CommunityBuilder ON Listing.id = CommunityBuilder.id',
					"LEFT JOIN #__comprofiler_field_values AS Category ON CommunityBuilder.{$this->cbCustomField} = Category.fieldtitle",
					"INNER JOIN #__jreviews_categories AS JreviewsCategory ON Category.fieldvalueid = JreviewsCategory.id AND JreviewsCategory.`option` = 'com_comprofiler'",
					'LEFT JOIN #__jreviews_criteria AS Criteria ON JreviewsCategory.criteriaid = Criteria.id'
				);
				
				$this->joinsReviews = array(
//					"LEFT JOIN #__users AS Listing ON Review.pid = Listing.id",
					'INNER JOIN #__comprofiler AS CommunityBuilder ON Review.pid = CommunityBuilder.id',
					"LEFT JOIN #__comprofiler_field_values AS Category ON CommunityBuilder.{$this->cbCustomField} = Category.fieldtitle",
					"INNER JOIN #__jreviews_categories AS JreviewsCategory ON Category.fieldvalueid = JreviewsCategory.id AND JreviewsCategory.`option` = 'com_comprofiler'",
					'LEFT JOIN #__jreviews_criteria AS Criteria ON JreviewsCategory.criteriaid = Criteria.id'
				);				
				break;
		}
		
		$this->tag = __t("COMMUNITY_BUILDER_TAG",true);  // Used in MyReviews page to differentiate from other component reviews
		
		$this->fields[] = "'{$this->tag }' AS `Listing.tag`";
			
		
	}
	
	function exists() {
		return (bool) @ file_exists(PATH_ROOT . 'components' . _DS . 'com_comprofiler' . _DS . 'comprofiler.php');
	}
		
	function listingUrl($listing) {

		return sprintf($this->listingUrl,$listing['Listing']['listing_id'],$listing['Listing']['menu_id']); 
				
	}
    
    // Used to check whether reviews can be posted by listing owners, owner replies
    // Used to check whether reviews can be posted by listing owners, owner replies
    function getListingOwner($result_id) {
        $query = "SELECT User.id user_id, User.name, User.email 
            FROM #__users AS User ".
            "WHERE User.id = " . (int) $result_id;
        $this->_db->setQuery($query);
        return current($this->_db->loadAssocList());
    }      
		
	function afterFind($results) 
    {
        if (empty($results)) 
        {
            return $results;
        }
		
		# Find Itemid for component
		$Menu = RegisterClass::getInstance('MenuModel');
		
		$menu_id = $Menu->getComponentMenuId($this->extension);
	
		foreach($results AS $key=>$result) 
		{	
			// Process component menu id
			$results[$key][$this->name]['menu_id'] = $menu_id;
			
			// Process listing url
			$results[$key][$this->name]['url'] = $this->listingUrl($results[$key]);
			
			// Process criteria
			if(isset($result['Criteria']['criteria']) && $result['Criteria']['criteria'] != '') {
				$results[$key]['Criteria']['criteria'] = explode("\n",$result['Criteria']['criteria']);
			}

			if(isset($result['Criteria']['tooltips']) && $result['Criteria']['tooltips'] != '') {
				$results[$key]['Criteria']['tooltips'] = explode("\n",$result['Criteria']['tooltips']);
			}

			if(isset($result['Criteria']['weights']) && $result['Criteria']['weights'] != '') {
				$results[$key]['Criteria']['weights'] = explode("\n",$result['Criteria']['weights']);
			}
			
			// Process images	
			$images = $result['Listing']['images'];
			unset($results[$key]['Listing']['images']);
			$results[$key]['Listing']['images'] = array();

			if($images != '') {
				if ( @file_exists("images/comprofiler" . $images) ) {
				    $imagePath = "images/comprofiler" . $images;
				} else {
				    $imagePath = "images/comprofiler/" . $images;
				}
			} else {
				// Put a noimage path here?
				$imagePath = ''; //"images/comprofiler/" . $images;				
			}
			
			$results[$key]['Listing']['images'][] = array(
				'path'=>$imagePath,
				'caption'=>$results[$key]['Listing']['title'],
				'basepath'=>true
			);
						
		}

		return $results;
	}	
	
	/**
	 * This can be used to add post review save actions, like synching with another table
	 *
	 * @param array $data
	 */
	function afterSave(&$data) {
		//
	}	
		
	/**
	 * ADMIN FUNCTIONS BELOW
	 */	
	function getNewCategories()
	{	
		$call = 'getNewCategories'.$this->integrationMode; 
		return $this->$call();
	}
		
	function getUsedCategories() 
	{		
		$call = 'getUsedCategories'.$this->integrationMode; 
		return $this->$call();
	}	
	
	/**
	 * GROUPS MODE STARTS HERE
	 */	
	function getNewCategoriesgroups()
	{	
		$query = "SELECT id FROM #__jreviews_categories WHERE `option` = '{$this->extension}'";
		$this->_db->setQuery($query);
		$exclude = $this->_db->loadResultArray();
		appLogMessage("getNewCategories\n".$this->_db->getErrorMsg(),'everywhere');		
		
		$exclude = $exclude ? implode(',',$exclude) : '';
			
		$query = "SELECT Component.{$this->categoryPrimaryKey} AS value,Component.name as text"
		. "\n FROM #__core_acl_aro_groups AS Component"
		. "\n LEFT JOIN #__jreviews_categories AS JreviewCategory ON Component.{$this->categoryPrimaryKey} = JreviewCategory.id AND JreviewCategory.`option` = '{$this->extension}'"
		. ($exclude != '' ? "\n WHERE Component.{$this->categoryPrimaryKey} NOT IN ($exclude)" : '')
		. "\n ORDER BY Component.name ASC"
		;
		
		$this->_db->setQuery($query);
		appLogMessage("getNewCategories\n".$this->_db->getQuery(),'everywhere');

		$result = $this->_db->loadAssocList();
		appLogMessage($this->_db->getErrorMsg(),'everywhere');

		return $result;
	}
		
	function getUsedCategoriesgroups() 
	{		
		$query = "SELECT Component.{$this->categoryPrimaryKey} AS `Component.cat_id`,Component.name as `Component.cat_title`, Criteria.title AS `Component.criteria_title`"
		. "\n FROM #__core_acl_aro_groups AS Component"
		. "\n INNER JOIN #__jreviews_categories AS JreviewCategory ON Component.{$this->categoryPrimaryKey} = JreviewCategory.id AND JreviewCategory.`option` = '{$this->extension}'"
		. "\n LEFT JOIN #__jreviews_criteria AS Criteria ON JreviewCategory.criteriaid = Criteria.id"
		. "\n LIMIT $this->offset,$this->limit"
		;
		$this->_db->setQuery($query);
		appLogMessage("getUsedCategories\n".$this->_db->getQuery(),'everywhere');

		$results = $this->_db->loadObjectList();
		appLogMessage($this->_db->getErrorMsg(),'everywhere');
		
		$results = $this->__reformatArray($results);
		$results = $this->changeKeys($results,'Component','cat_id');
				
		$query = "SELECT count(JreviewCategory.id)"
		. "\n FROM #__jreviews_categories AS JreviewCategory"
		. "\n WHERE JreviewCategory.`option` = '{$this->extension}'"
		;
		$this->_db->setQuery($query);		
				
		$count = $this->_db->loadResult();  

		return array('rows'=>$results,'count'=>$count);
	}

	/**
	 * FIELDS MODE STARTS HERE
	 */
	function getNewCategoriesfields()
	{	
		$query = "SELECT id FROM #__jreviews_categories WHERE `option` = '{$this->extension}'";
		$this->_db->setQuery($query);
		$exclude = $this->_db->loadResultArray();
		appLogMessage("getNewCategoriesfields\n".$this->_db->getErrorMsg(),'everywhere');		
		
		$exclude = $exclude ? implode(',',$exclude) : '';
			
		$query = "SELECT Component.fieldvalueid AS value, Component.fieldtitle as text"
		. "\n FROM #__comprofiler_field_values AS Component"
		. "\n INNER JOIN #__comprofiler_fields AS Field ON Component.fieldid = Field.fieldid AND Field.name = '{$this->cbCustomField}'"
		. ($exclude != '' ? "\n WHERE Component.fieldvalueid NOT IN ($exclude)" : '')
		. "\n ORDER BY Component.fieldtitle ASC"
		;
		
		$this->_db->setQuery($query);
		appLogMessage("getNewCategoriesfields\n".$this->_db->getQuery(),'everywhere');

		$result = $this->_db->loadAssocList();
		appLogMessage($this->_db->getErrorMsg(),'everywhere');

		return $result;
	}
		
	function getUsedCategoriesfields() 
	{		
		$query = "SELECT Component.fieldvalueid AS `Component.cat_id`, Component.fieldtitle as `Component.cat_title`, Criteria.title AS `Component.criteria_title`"
		. "\n FROM #__comprofiler_field_values AS Component"
		. "\n INNER JOIN #__jreviews_categories AS JreviewCategory ON Component.fieldvalueid = JreviewCategory.id AND JreviewCategory.`option` = '{$this->extension}'"
		. "\n LEFT JOIN #__jreviews_criteria AS Criteria ON JreviewCategory.criteriaid = Criteria.id"
		. "\n LIMIT $this->offset,$this->limit"
		;
		$this->_db->setQuery($query);
		appLogMessage("getUsedCategories\n".$this->_db->getQuery(),'everywhere');

		$results = $this->_db->loadObjectList();
		appLogMessage($this->_db->getErrorMsg(),'everywhere');
		
		$results = $this->__reformatArray($results);
		$results = $this->changeKeys($results,'Component','cat_id');
				
		$query = "SELECT count(JreviewCategory.id)"
		. "\n FROM #__jreviews_categories AS JreviewCategory"
		. "\n WHERE JreviewCategory.`option` = '{$this->extension}'"
		;
		$this->_db->setQuery($query);		
				
		$count = $this->_db->loadResult();  

		return array('rows'=>$results,'count'=>$count);
	}		
}