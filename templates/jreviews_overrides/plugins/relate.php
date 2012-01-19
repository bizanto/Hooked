<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2009 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

/**
 * This plugin checks the session for a related content id after a listing is saved
 * and if found links the newly created listing id to the related content id 
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class RelateComponent extends S2Component {

	var $name = "relations";

	var $published = false;

	function startup(&$controller) 
	{
		$this->c = &$controller;
		$this->published = true;
	}

	function plgAfterFind(&$model, $rows)
	{
		if (JRequest::getVar('option') == 'com_content') {
			// don't filter if we're on a listings detail page (privacy already implemented in template)
			return $rows;
		}
		
		$rowIds = array_keys($rows);
		
		// get privacy settings for each listing from the database
		$db =& JFactory::getDBO();
		$sql = "SELECT contentid, jr_privacy FROM #__jreviews_content WHERE contentid IN (".implode(",", $rowIds).") ";
		$db->setQuery($sql);
		$privacySettings = $db->loadObjectList('contentid');
		
		// get friends list for current user
		$user =& JFactory::getUser();
		$sql = "SELECT connect_to FROM #__community_connection WHERE connect_from = '".$user->id."'";
		$db->setQuery($sql);
		$friendsList = $db->loadResultArray();
		
		// filter out returned listings based on privacy settings
		$filterRows = array();
		
		$isAdmin = ($user->usertype == "Super Administrator") ? 1 : 0;

		foreach ($rows as $row) {
			$jr_privacy = str_replace('*', '', $privacySettings[$row['Listing']['listing_id']]->jr_privacy);
			
			$isFriend = in_array($row['Listing']['user_id'], $friendsList);
			$isOwner  = ($user->id == $row['Listing']['user_id']) ? 1 : 0;
			
			$canview = 0; 
			
			switch ($jr_privacy) {
				case 'offentlig':
					$canview = 1;
					break;
				case 'site-members':
					$canview = ($user->id) ? 1 : 0;
					break;
				case 'friends':
					$canview = ($isFriend || $isOwner || $isAdmin) ? 1 : 0;
					break;
				case 'privat':
					$canview = ($isOwner || $isAdmin) ? 1 : 0;
					break;
				default:
					$canview = 1;
					break;		
			}
			
			if ($canview) {
				$filterRows[] = $row;
			}
		}

		return $filterRows;
	}
	
	function plgAfterSave(&$model)
	{
		switch($model->name)
		{
			case 'Discussion':
			break;   
			case 'Favorite':
			break;             
			case 'Listing':
				$this->_plgListingAfterSave($model);
			break;  
			case 'Review':
			break;  
			case 'Vote':
			break;  
		}

		$this->published = false;
	}      

	function plgBeforeDelete(&$model)
	{
		switch($model->name)
		{
			case 'Discussion':
			break;   
			case 'Listing':
				$this->_plgListingBeforeDelete($model);
			break;  
			case 'Review':
			break;  
		}
	}

	function _plgListingAfterSave(&$model)
	{
		// Limit running this for new/edited listings. Not deletion of images or other listing actions.
		if($this->c->name == 'listings' && in_array($this->c->action,array('_save'))) {
			$listing_id = $model->data['Listing']['id'];
			$listing_title = $model->data['Listing']['title'];

			$relate_id = JRequest::getString('relate_id');
			
			if (!$relate_id) return;

			// associate new listing with related listing_id
			include_once(JPATH_BASE.DS.'components'.DS.'com_relate'.DS.'models'.DS.'relate.php');
			$model = new RelateModelRelate();

			$rids = explode(',', $relate_id);
			$rel_titles = array();
			foreach ($rids as $rel_id) {
				if (!is_numeric($rel_id)) continue;

				$result = $model->add_listings($rel_id, array($listing_id));

				if ($result > 1) {
					// get title of related listing
					$db =& JFactory::getDBO();
					$sql = "SELECT title FROM #__content WHERE id = '$rel_id'";
					$db->setQuery($sql);
					$rel_titles[] = $db->loadResult();
				}
			}
			
			if (count($rel_titles)) {
				$relate_title = implode(',', $rel_titles);
				echo '<script type="text/javascript">alert("Related '.$relate_title.' to '.$listing_title.'");</script>';
			}
		}
	}

	function _plgListingBeforeDelete(&$model)
	{
		$listing_id = $model->data['listing_id'];
		$db =& JFactory::getDBO();
		$sql = "DELETE FROM #__relate_listings WHERE (id1 = '$listing_id') OR (id2 = '$listing_id')";
		$db->setQuery($sql);
		$db->query();
		
		$sql = "DELETE FROM #__relate_photos WHERE listing_id = '$listing_id'";
		$db->setQuery($sql);
		$db->query();
		
		$sql = "DELETE FROM #__relate_videos WHERE listing_id = '$listing_id'";
		$db->setQuery($sql);
		$db->query();
	}
}

