<?php
/**
 * @author     Kevin Strong
 * @email      kevin.strong@wearehathway.com
 * @website    http://www.wearehathway.com
 * @copyright  Copyright (C) 2011 Hathway
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modSlideShowHelper 
{
	function getItems($params)
	{
		$db =& JFactory::getDBO();
		
		$sql = "SELECT c.id, c.title, c.sectionid, c.catid, ".
		       "jr.".$params->get('tfield')." AS tagline, jr.featured, ".
			   "p.image, p.thumbnail ".
		       "FROM #__content c ".
		       "LEFT JOIN #__jreviews_content jr ON c.id = jr.contentid ".	
		       "LEFT JOIN `#__relate_photos` rp ON rp.listing_id = c.id ".
		       "LEFT JOIN `#__community_photos` p ON "."p.id = rp.photo_id ";
		
		$wheres = array();
		$wheres[] = "c.state > 0";
		$wheres[] = "p.image != ''";
		
		if ($params->get('categories')) {
			$cats = $params->get('categories');
			$cats = explode(',', $cats);
			
			if (count($cats) == 1) {
				$wheres[] = "c.catid = ".$cats[0];
			}
			else {
				$wheres[] = "c.catid IN (".implode(",", array_filter($cats, 'is_numeric')).")";
			}
		}
		else if ($params->get('section')) {
			$wheres[] = "c.sectionid = ".$params->get('section');
		}
		
		$sql .= "WHERE ".implode(" AND ", $wheres)." ";
			
		if ($params->get('filter') == "featured") {
			$sql .=	"AND jr.featured > 0 ";	
		}
		
		$sql .= "GROUP BY c.id ORDER BY c.created DESC ";
		
		if ($params->get('limit')) $sql .= "LIMIT ".$params->get('limit');
		
		$db->setQuery($sql);

		$items = $db->loadObjectList();
		
		if (!$items) $items = array();
		
		$slides = array();
		
		// Loop through each item and make sure it's related image meets the minimum width & height (eg. the slideshow width/height)
		// 680 x 350
		$minwidth = 680; $minheight = 350;
		foreach ($items as $item) {
			list($img_width, $img_height, $img_type, $img_attr) = $size = getimagesize($item->image);
			if ($img_width >= $minwidth && $img_height >= $minheight) {
				$slides[] = $item;
			}
		}
		
		return $slides;
	}	
}

?>