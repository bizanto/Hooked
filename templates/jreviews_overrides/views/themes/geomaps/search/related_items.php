<?php 

defined('_JEXEC') or die('Restricted Access');

function getRelatedItemsList() {
	$cids = func_get_args();

	$groups = (count($cids) > 1) ? true : false;

	$list  = '<select class="relatedtech" name="data[related][]">'."\n";
	$list .= '<option value="">Velg...</option>'."\n";

	foreach ($cids as $catID) {
		if (!is_numeric($catID)) continue;

		$db =& JFactory::getDBO();
		$sql = "SELECT c.id, c.title, cat.title AS cat_title FROM #__content c ".
		       "LEFT JOIN #__categories cat ON c.catid = cat.id ".
		       "WHERE catid='$catID' AND c.state > 0 ORDER BY title";
		$db->setQuery($sql);
		
		$items = $db->loadObjectList();
		
		if ($groups && count($items) >= 1) {
			$list .= '<optgroup label="'.$items[0]->cat_title.'">'."\n";
		}

		foreach ($items as $item) {
			$list .= '<option value="'.$item->id.'">'.$item->title.'</option>'."\n";
		}

		if ($groups && count($items) >= 1) {
			$list .= '</optgroup>'."\n";
		}
	}

	$list .= '</select>'."\n";
	
	return $list;
}

function getRelatedItemsJumpList() {
	$cids = func_get_args();

	$groups = (count($cids) > 1) ? true : false;

	$list  = '<select class="relatedtech" name="data[related][]" onchange="window.location=this.value;return false;">'."\n";
	$list .= '<option value="" selection="selected">Velg...</option>'."\n";

	foreach ($cids as $catID) {
		if (!is_numeric($catID)) continue;

		$db =& JFactory::getDBO();
		$sql = "SELECT c.id, c.title, c.catid, c.sectionid, cat.title AS cat_title FROM #__content c ".
		       "LEFT JOIN #__categories cat ON c.catid = cat.id ".
		       "WHERE catid='$catID' AND c.state > 0 ORDER BY title";
		$db->setQuery($sql);
		
		$items = $db->loadObjectList();
		
		if ($groups && count($items) > 1) {
			$list .= '<optgroup label="'.$items[0]->cat_title.'">'."\n";
		}

		require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';
		
		foreach ($items as $item) {
			$listinglink = ContentHelperRoute::getArticleRoute($item->id,$item->catid,$item->sectionid);
			
			$list .= '<option value="'.$listinglink.'">'.$item->title.'</option>'."\n";
		}

		if ($groups && count($items) > 1) {
			$list .= '</optgroup>'."\n";
		}
	}

	$list .= '</select>'."\n";
	
	return $list;
}

function getRelatedItemsCheckList($cats,$limit) {
	$cids = func_get_args();

	$groups = (count($cids) > 1) ? true : false;

	$list  = '';
	$countopt = 1;
	
	if ($limit) $rel_limit = ' LIMIT 0,'.$limit.'';
	else $limit="";

	foreach ($cids as $catID) {
		if (!is_numeric($catID)) continue;

		$db =& JFactory::getDBO();
		$sql = "SELECT c.id, c.title, cat.title AS cat_title FROM #__content c ".
		       "LEFT JOIN #__categories cat ON c.catid = cat.id ".
		       "WHERE catid='$catID' AND c.state > 0 ORDER BY title".$rel_limit."";
		$db->setQuery($sql);
		
		$items = $db->loadObjectList();
		
		foreach ($items as $item) {
			$list .= '<div class="relchk_item"><input name="data[related][]" type="checkbox" value="'.$item->id.'" id="relchk'.$item->id.'_'.$countopt.'"> <label for="relchk'.$item->id.'_'.$countopt.'">'.$item->title.'</label></div>'."\n";
			$countopt++;
		}

	}
	
	return $list;
}

?>

