<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');

class RelateModelEdit extends JModel
{			
	function getAllowedCategories($listing_id) 
	{
		$q = "SELECT catid FROM #__content WHERE id='$listing_id'";
		$this->_db->setQuery($q);
		$listing_cid = $this->_db->loadResult();
		
		$q = "SELECT relatable FROM #__relate_categories WHERE catID='$listing_cid'";
		$this->_db->setQuery($q);
		$relatable = $this->_db->loadResult();
		
		$q = "SELECT c.id, c.title, c.section, r.* ".
		     "FROM #__categories c, #__relate_categories r ".
		     "WHERE c.id = r.catID AND c.id IN ($relatable)";
		$this->_db->setQuery($q);
		$allowed_cats = $this->_db->loadObjectList('id');
		if (!$allowed_cats) $allowed_cats = array();

		return $allowed_cats;
	}

	function checkACL($listing_id, $cids) 
	{
		$user =& JFactory::getUser();

		if ($cids{0} == 's') { // $cids is a section id
			$cids = ltrim($cids, 's');
			// query list of category ids
			$sql = "SELECT id FROM #__categories WHERE section = $cids";
			$this->_db->setQuery($sql);
			$this->_db->query();
			$cids = $this->_db->loadResultArray();	
		}
		else {
			// turn cids into array, filter out non-ints
			$cids = explode(",", $cids);
			$cids = array_filter($cids, "is_numeric"); 
		}

		if ($listing_id) {
			// get allowed cats for this listing_id
			$sql = "SELECT r.relatable FROM #__relate_categories r, #__content c ".
			       "WHERE c.id = $listing_id AND r.catID = c.catid"; 
			$this->_db->setQuery($sql);
			$relatable = $this->_db->loadResult();
	
			$sql = "SELECT * FROM #__relate_categories WHERE catID IN ($relatable)";
			$this->_db->setQuery($sql);
			$allowed_cats = $this->_db->loadObjectList('catID');
		}
		else {
			// if a listing id isnt provided filter on all categories
			$sql = "SELECT * FROM #__relate_categories ";
			$this->_db->setQuery($sql);
			$allowed_cats = $this->_db->loadObjectList('catID');
		}
		
		// filter out any categories that aren't allowed_cats
		// add where clauses for allowed categories based on acl setting
		$wheres = array();
		foreach ($cids as $cid) {
			if (!array_key_exists($cid, $allowed_cats)) continue;

			$where = "(c.catid = $cid";

			$authortype = $allowed_cats[$cid]->author;
			if ($authortype == "1") {
				$where .= " AND c.created_by = ".$user->id;	
			}
			else if ($authortype == "2") {
				$where .= " AND c.created_by IN (SELECT id FROM #__users WHERE usertype = 'Super Administrator')";
			}

			$where .= ")";
			$wheres[] = $where;
		}

		$acl_where = '';	
		if (count($wheres)) {
		       	$acl_where = 'AND ('.implode(' OR ', $wheres).')';
		}

		return $acl_where;
	}

	function getListings($listing_id, $cat_id = 0, $searchword = '', $start = 0)
	{
		$type = '';
		$user =& JFactory::getUser();

		if ($cat_id{0} == 's') {
			// $cat_id should actually be a section id 
			$cat_id = ltrim($cat_id, 's');
			if (is_numeric($cat_id)) {
				$where_cat = "c.sectionid = '$cat_id'";
				$type = "sections";
			}
			else {
				return array();
			}
		}
		else if ($cat_id == "photos") {
			return $this->getPhotos($listing_id, $searchword, $start);
		}
		else if ($cat_id == "videos") {
			return $this->getVideos($listing_id, $searchword, $start);
		}
		else if (is_numeric($cat_id)) {
			$where_cat = "c.catid = '$cat_id'";
			$type = "categories";
		}
		else { // comma separated categories
			$cids = explode(",", $cat_id);
			$cids = array_filter($cids, "is_numeric");
			$where_cat = "c.catid IN (".implode(",", $cids).")";
		}

		if ($user->usertype != "Super Administrator") $where_acl = $this->checkACL($listing_id, $cat_id);
		else $where_acl = '';	
		
		$sql = "SELECT c.id, c.title, p.thumbnail, COUNT(r.id1) AS related FROM `#__content` c ".
		       "LEFT JOIN `#__relate_listings` r ON ".
		       "(r.id1 = $listing_id AND r.id2 = c.id) ".
		       "LEFT JOIN `#__relate_photos` rp ON rp.listing_id = c.id ".
		       "LEFT JOIN `#__community_photos` p ON p.id = rp.photo_id ".
		       "WHERE c.id != '$listing_id' AND c.state > 0 AND $where_cat $where_acl ";
		
		if ($searchword != '') {
			$sql .= " AND c.title LIKE '%$searchword%' ";
		}
		
		$sql .= " GROUP BY c.id ORDER BY c.title ASC";
		
		$sql .= " LIMIT $start,100 ";

		$this->_db->setQuery($sql);
		$listings = $this->_db->loadObjectList('id');

		foreach ($listings as $idx => $listing) {
			if ($listing->thumbnail == '') {
				$thumb = 'images/stories/jreviews/tn/tn_list_noimage.png';
				$listings[$idx]->thumbnail = $thumb;
			}
		}

		$sql = "SELECT title FROM #__$type WHERE id = $cat_id";
		$this->_db->setQuery($sql);
		$type = $this->_db->loadResult();

		return array('type' => $type, 'listings' => (object)$listings);
	}

	function getPhotos($listing_id, $searchword = '', $start = 0)
	{
		$wheres = array();
		
		$user =& JFactory::getUser();
		if ($user->usertype != "Super Administrator") $wheres[] = " p.creator = $user->id ";
		
		if ($searchword != '') {
			$wheres[] = " p.caption LIKE '%$searchword%' ";
		}
		
		if (count($wheres)) {
			$where = 'WHERE '.implode(' AND ', $wheres);
		}
		else {
			$where = '';
		}
		
		$sql = "SELECT p.id, p.caption AS title, p.thumbnail, COUNT(r.listing_id) AS related ".
		       "FROM `#__community_photos` p ".
		       "LEFT JOIN `#__relate_photos` r ON ".
		       "(r.listing_id = '$listing_id' AND r.photo_id = p.id) ".
		       "$where ";
		
		$sql .= " GROUP BY p.id";
		
		$sql .= " LIMIT $start,100 ";
		
		$this->_db->setQuery($sql);
		$photos = $this->_db->loadObjectList('id');

		return array('type' => JText::_('photos'), 'listings' => (object)$photos);
	}

	function getVideos($listing_id, $searchword = '', $start = 0) 
	{
		$wheres = array();
		
		$user =& JFactory::getUser();
		if ($user->usertype != "Super Administrator") $wheres[] = " v.creator = $user->id ";

		if ($searchword != '') {
			$wheres[] = " v.title LIKE '%$searchword%' ";
		}
		
		if (count($wheres)) {
			$where = 'WHERE '.implode(' AND ', $wheres);
		}
		else {
			$where = '';
		}
		
		$sql = "SELECT v.id, v.title, v.thumb AS thumbnail, COUNT(r.listing_id) AS related ".
		       "FROM `#__community_videos` v ".
		       "LEFT JOIN `#__relate_videos` r ON ".
		       "(r.listing_id = '$listing_id' AND r.video_id = v.id) ".
		       "$where ";

		$sql .= " GROUP BY v.id";
		
		$sql .= " LIMIT $start,100 ";
		
		$this->_db->setQuery($sql);
		$videos = $this->_db->loadObjectList('id');
	
		return array('type' => JText::_('videos'), 'listings' => (object)$videos);
	}
}

