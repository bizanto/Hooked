<?php

class RelateAPIHelper {

	public function setup()
	{
	
	}

	function getRelatedId($listing, $cids=array())
	{
		$db = JFactory::getDBO();
		$query  = "SELECT r.id2 FROM #__relate_listings AS r ";
		
		if (count($cids)) {
			$query .= "INNER JOIN #__content AS c ON c.id = r.id2 "
			         ."WHERE c.catid IN (".implode(",", $cids).") AND ";
		}
		else 
			$query .= "WHERE ";
		
		$query .= "r.id1 = ".$listing->id." LIMIT 1";
		$db->setQuery($query); 
		$related_id = $db->loadResult();
		return $related_id;
	}

	

	public function getRelated($id, $cat = array()) {
		if ($cat) $where_cat = "AND c.catid IN(".implode(",",$cat).")";
		else $where_cat = "";
		
		$db =& JFactory::getDBO();
		$query = "SELECT c.id "
				."FROM #__content AS c "
				."INNER JOIN #__relate_listings AS r ON (r.id1 = $id AND r.id2 = c.id) "
				."WHERE c.state > 0 $where_cat";
		$db->setQuery($query);
		$related = $db->loadResultArray();
		return $related;
	}
	
	public function getPhotos($id) {
		$db =& JFactory::getDBO();
		$query = "SELECT r.photo_id, p.image, p.thumbnail, p.storage "
				."FROM #__relate_photos AS r "
				."INNER JOIN #__community_photos AS p ON p.id = r.photo_id "
				."WHERE listing_id = $id ";
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	public function getVideos($id) {
		$db =& JFactory::getDBO();
		$query = "SELECT r.video_id, v.thumb, v.path, v.storage "
				."FROM #__relate_videos AS r "
				."INNER JOIN #__community_videos AS v ON v.id = r.video_id "
				."WHERE listing_id = $id ";
		$db->setQuery($query);
		
		return $db->loadObjectList();
	}

	public function getRelatedListings($id, $cat = array()) {
		if ($cat) $where_cat = "AND c.catid IN(".implode(",",$cat).")";
		else $where_cat = "";
	
		$db = JFactory::getDBO();
		$query = "SELECT c.id, c.title, c.alias, c.catid, c.sectionid, c.introtext, c.fulltext, c.created_by, jr.* "
				."FROM #__content AS c "
				."INNER JOIN #__jreviews_content AS jr ON jr.contentid = c.id "
				."INNER JOIN #__relate_listings AS r ON (r.id1 = $id AND r.id2 = c.id) "
				."WHERE c.state = 1 ".$where_cat
				;
		$db->setQuery($query);
		$listing = $db->loadObject();
			
		return $listing;
	}

}