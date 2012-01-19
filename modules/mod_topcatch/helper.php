<?php
/**
 * @author     Kevin Strong
 * @email      kevin.strong@wearehathway.com
 * @website    http://www.wearehathway.com
 * @copyright  Copyright (C) 2011 Hathway
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modTopCatchHelper
{
	function getTopCatch($params) 
	{
		$db =& JFactory::getDBO();
		
		$sql = "SELECT c.id, c.title, c.sectionid, c.catid, jr.jr_startdate, jr.jr_catchweight, ".
		       "u.id AS userid, u.name AS username, cu.thumb AS avatar, p.image AS image, p.caption ".
		       "FROM #__content c ".
		       "LEFT JOIN #__jreviews_content jr ON c.id = jr.contentid ".
		       "LEFT JOIN #__users u ON c.created_by = u.id ".
		       "LEFT JOIN #__community_users cu ON c.created_by = cu.userid ".
		       "INNER JOIN #__relate_listings r ON c.id = r.id1 ".
		       "LEFT JOIN #__relate_photos rp ON c.id = rp.listing_id ".
		       "LEFT JOIN #__community_photos p ON p.id = rp.photo_id ".
		       "WHERE c.state > 0 AND c.catid = 14 "; // cat 14 = Fangstrapporter
		
		if ($params->get('filterby') == "species") {
			$sql .=	"  AND r.id2 = '".$params->get('species_id')."' ";
		}
		
		$sql .=	"  AND jr.jr_catchweight REGEXP '^[[:digit:]]+$' "; // filter out weights like ???? or 500 - 550 gr, etc.

		if ($params->get('minweight')) {
			$sql .=	"  AND jr.jr_catchweight >= '".$params->get('minweight')."' "; // require a minimum weight
		}
		
		$sql .= "GROUP BY c.id ";
		
		if ($params->get('orderby') == "weight") {
			// $sql .=	"ORDER BY jr.jr_catchweight+0 DESC, jr.jr_startdate DESC "; // uses catch date
			$sql .=	"ORDER BY jr.jr_catchweight+0 DESC, c.created DESC "; // uses joomla create date
		}
		elseif ($params->get('orderby') == "date") {
			// $sql .=	"ORDER BY jr.jr_startdate DESC, jr.jr_catchweight DESC "; // uses catch date
			$sql .=	"ORDER BY c.created DESC, jr.jr_catchweight DESC "; // uses joomla create date
		}
		
		if ($params->get('limit')) $sql .= "LIMIT ".$params->get('limit');
		else $sql .= "LIMIT 1";
		
		$db->setQuery($sql);

		$catches = $db->loadObjectList();
		
		foreach ($catches as $catch) {
			if (!$catch->avatar) {
				$catch->avatar = JURI::base().'components/com_community/assets/user_thumb.png';
			}
		}
		return $catches;
	}
	
	function getSpeciesName($params)
	{
		$db =& JFactory::getDBO();
		
		$sql = "SELECT title FROM #__content WHERE id = '".$params->get('species_id')."'";
		$db->setQuery($sql);
		
		return $db->loadResult();
	}
}
?>