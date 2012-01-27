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

function getRelatedList($listing_id, $rel_categories, $limit = 4)
{
	$db =& JFactory::getDBO();

	if (is_array($rel_categories)) {
		$rel_categories = implode(",", $rel_categories);
	}

	$sql = "SELECT c.id, c.title, c.catid FROM `#__content` c, `#__relate_listings` r ".
	       "WHERE (r.id1 = $listing_id AND r.id2 = c.id) ".
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
	if ($mediatype=="videos") {
		$sql .= ", `#__relate_videos` rv WHERE (rv.video_id = $content_id AND rv.listing_id = c.id)";
	}
	else {
		$sql .= ", `#__relate_photos` rp WHERE (rp.photo_id = $content_id AND rp.listing_id = c.id)";
		echo $sql;
	}
		$sql .= " AND c.catid = cat.id AND c.state > 0 AND c.catid IN (1, 2, 3, 4, 100) GROUP BY c.id";
	
	$db->setQuery($sql);
	$location = $db->loadObjectList();
	
	return $location;
}




?>
