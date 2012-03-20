<?php
function getRelatedThumb($content_id,$limit) {
	
	$db =& JFactory::getDBO();
	$sql = "SELECT p.id, p.caption, p.creator, p.image, p.thumbnail, p.created, p.storage, u.username ".
		   "FROM #__community_photos p, #__users u ".
		   "LEFT JOIN #__relate_photos rp ON rp.listing_id = '".$content_id."'".
		   "WHERE p.id = rp.photo_id ".
		   "AND p.creator = u.id ORDER BY p.id asc";

	if ($limit) {
		$sql .= " LIMIT $limit";
	}
	
	$db->setQuery($sql);
	
	$relatedThumb = $db->loadObjectList();
	return $relatedThumb;
}

function getRelatedCount($listing_id, $cid) {
	static $counts = array();

	if (!isset($counts[$listing_id])) {
		$db =& JFactory::getDBO();
		$sql = "SELECT c.catid, COUNT(c.id) AS count FROM `#__content` c, `#__relate_listings` r ".
		       "WHERE r.id1 = $listing_id AND r.id2 = c.id AND c.state > 0 GROUP BY c.catid";
		$db->setQuery($sql);
		$counts[$listing_id] = $db->loadObjectList('catid');
	}
	
	if ($cid == "photos" || $cid == "videos") {
		if (!isset($counts[$listing_id][$cid])) {
			$db =& JFactory::getDBO();
			$sql = "SELECT COUNT(*) AS count FROM `#__relate_$cid` WHERE listing_id = '$listing_id'";
			$db->setQuery($sql);
			$counts[$listing_id][$cid] = $db->loadResult();
		}

		return $counts[$listing_id][$cid];
	}
	else if (is_numeric($cid)) {
		if (isset($counts[$listing_id][$cid])) {
			return $counts[$listing_id][$cid]->count;
		}
		else {
			return 0;
		}
	}
	else if (is_array($cid)) {
		$total = 0;
		foreach ($cid as $catid) {
			if (isset($counts[$listing_id][$catid])) 
				$total += $counts[$listing_id][$catid]->count;
		}
		return $total;
	}
	
	return 0;
}

/**
 * Get list of IDs of related items
 *
 * $listing_ids - IDs of items to find relations for. int, array, or comma separated list of IDs
 * $rel_categories = Categories of related items. int, array, or comma separated list of IDs
 */
function getRelatedIDs($listing_ids, $rel_categories) 
{
	$db =& JFactory::getDBO();
	
	if (is_array($rel_categories)) {
		$rel_categories = implode(",", $rel_categories);
	}
	
	if (!is_array($listing_ids)) {
		$listing_ids = explode(",", $listing_ids);
	}
	
	$listing_ids = array_filter($listing_ids, "is_numeric");
	$listing_ids = implode(",", $listing_ids);
	
	$sql = "SELECT c.id ".
	       "FROM `#__content` c ".
	       "INNER JOIN `#__relate_listings` r ON r.id1 IN ($listing_ids) AND r.id2 = c.id ".
	       "WHERE c.id NOT IN ($listing_ids) AND c.state > 0 AND c.catid IN ($rel_categories) ";
	
	$db->setQuery($sql);
	
	$ids = $db->loadResultArray();
	return $ids;
}

// like getRelatedIDs, but return id/title objects. 
function getRelatedItems($listing_ids, $rel_categories) 
{
	$db =& JFactory::getDBO();
	
	if (is_array($rel_categories)) {
		$rel_categories = implode(",", $rel_categories);
	}
	
	if (!is_array($listing_ids)) {
		$listing_ids = explode(",", $listing_ids);
	}
	
	$listing_ids = array_filter($listing_ids, "is_numeric");
	$listing_ids = implode(",", $listing_ids);
	
	$sql = "SELECT c.id, c.title ".
	       "FROM `#__content` c ".
	       "INNER JOIN `#__relate_listings` r ON r.id1 IN ($listing_ids) AND r.id2 = c.id ".
	       "WHERE c.id NOT IN ($listing_ids) AND c.state > 0 AND c.catid IN ($rel_categories) ";
	
	$db->setQuery($sql);
	
	$ids = $db->loadObjectList('id');
	return $ids;
}

/** 
 * Get full content for related items including related images
 */
function getRelatedList($listing_id, $rel_categories, $limit = 4)
{
	$db =& JFactory::getDBO();

	if (is_array($rel_categories)) {
		$rel_categories = implode(",", $rel_categories);
	}

	$sql = "SELECT c.id, c.title, c.catid, c.images, u.name  ".
	       "FROM `#__content` c, `#__relate_listings` r, `#__users` u ".
	       "WHERE (r.id1 = $listing_id AND r.id2 = c.id) AND u.id = c.created_by ".
	       "AND c.id != '$listing_id' AND c.state > 0 AND c.catid IN ($rel_categories) ".
		   "ORDER BY c.id DESC ";

	if ($limit) {
		$sql .= "LIMIT $limit";
	}
	
	$db->setQuery($sql);
	$listings = $db->loadObjectList('id');
	
	// todo get relatedThumbnail
	foreach ($listings as $listing) {
		$thisid=$listing->id;
		$photos = getRelatedThumb($thisid);
		$photocount="0";
		foreach ($photos as $photo) {
			if (!$photocount) {
				$default_thumbnail = $photo->thumbnail;
			}
			$photocount++;
		}
	}
	$listings->tn = $default_thumbnail;
	$listings->photocount = $photocount;
	
	return $listings;
	
	//	
}

function getParentLocation($spot_id)
{
	$db =& JFactory::getDBO();
	
	$sql = "SELECT c.id, c.title, c.catid, c.sectionid, cat.title AS catname, c.images AS thumbnail ".
	       "FROM `#__content` c, `#__categories` cat, `#__relate_listings` r ".
	       "WHERE (r.id1 = $spot_id AND r.id2 = c.id) AND c.catid = cat.id ".
	       "AND c.id != '$spot_id' AND c.state > 0 AND c.catid IN (1, 2, 100)";
	$db->setQuery($sql);
	$location = $db->loadObject();
	
	if ($location) {
		$thumb = explode("|||", $location->thumbnail);
		$thumb = $thumb[0];
		if ($thumb == '') {
			$thumb = 'jreviews/tn/tn_list_noimage.png';
		}
		$thumb = 'images/stories/'.$thumb;
		$location->thumbnail = $thumb;
	}
	
	return $location;
}

function getMediaLocation($content_id,$mediatype)
{
	$db =& JFactory::getDBO();
	
	$sql = "SELECT c.id, c.title, c.catid, c.sectionid, cat.title AS catname, c.images AS thumbnail ".
	       "FROM `#__content` c, `#__categories` cat";
	
	if ($mediatype == "videos") {
		$sql .= ", `#__relate_videos` rv WHERE (rv.video_id = $content_id AND rv.listing_id = c.id)";
	}
	else {
		$sql .= ", `#__relate_photos` rp WHERE (rp.photo_id = $content_id AND rp.listing_id = c.id)";
	}
	
	$sql .= " AND c.catid = cat.id AND c.state > 0 AND c.catid IN (1, 2, 3, 4, 100) GROUP BY c.id";
	
	$db->setQuery($sql);
	$location = $db->loadObjectList();
	
	return $location;
}




?>
