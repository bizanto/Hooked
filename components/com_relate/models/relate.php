<?php 
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');

class RelateModelRelate extends JModel 
{
	function add_stream($listing_id, $cids, $ctype = '') 
	{
		include_once( JPATH_BASE . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');             
		
		// load language strings for this component so we get translated activity stream messages	
		$lang =& JFactory::getLanguage();
		$lang->load('com_relate');

		$sql = "SELECT id, title, catid FROM #__content WHERE id = '$listing_id'";
		$this->_db->setQuery($sql);
		$listing = $this->_db->loadObject();
		$listing_title = $listing->title;
		$listing_href = CRoute::_('index.php?option=com_content&view=article&catid='.$listing->catid.'&id='.$listing->id);
		$listing_link = '<a href="'.$listing_href.'">'.$listing_title.'</a>';

		if ($ctype != '') {
			$content_type = $ctype;
			$category_id = 1;
			$is_listing = false;
		}
		else {
			$sql = "SELECT l.id, l.catid, l.title, c.title AS cat_title FROM #__categories c, #__content l ".
			       "WHERE c.id = l.catid AND l.id IN (".implode(",", $cids).")";
			$this->_db->setQuery($sql);
			$content = $this->_db->loadObjectList('id');
			$is_listing = true;
		}

		foreach ($cids as $cid) {
			if ($is_listing) {
				$content_title = $content[$cid]->title;
				$content_type  = $content[$cid]->cat_title;
				$category_id   = $content[$cid]->catid;
				$content_id    = $content[$cid]->id;
				$href = CRoute::_('index.php?option=com_content&view=article&catid='.$category_id.'&id='.$content_id);
				$content_link  = '<a href="'.$href.'">'.$content_title.'</a>';
				$content_info = $content_link.' ('.$content_type.')';
			}
			else {
				$content_id = $cid;
				$tab = '';
				if ($content_type == "photo") $tab = '#bilder';
				else if ($content_type == "video") $tab = '#video';
				$content_link = '<a href="'.$listing_href.$tab.'">'.JText::_("a $content_type").'</a>'; 
				$content_info = $content_link;
			}

			$act = new stdClass();
			$act->cmd     = 'wall.write';
			$act->actor   = JFactory::getUser()->id;
			$act->target  = 0;
			$act->title   = JText::sprintf('RELATION ADDED', $content_info, $listing_link);
			$act->content = '';
			$act->app     = 'wall';
			$act->cid     = $content_id;

			CFactory::load('libraries', 'activities');
			CActivityStream::add($act);
		}
	}

	function add_listings($listing_id, $addIds, $act_stream = true, $creator = '')
	{
		if ($creator == '') {
			$user =& JFactory::getUser();
			$creator = $user->id;
		}
		$cAdd = 0; 
		
		if ($addIds) {
			// filter out any ids that are duplicates of existing relations
			$sql = "SELECT id2 FROM #__relate_listings WHERE id1 = $listing_id";
			$this->_db->setQuery($sql);
			$current_relations = $this->_db->loadResultArray();
			$new_relations = array_diff($addIds, $current_relations);
			$new_relations = array_filter($new_relations); // filter out any relations to 0 or ''
			
			$sql = "INSERT INTO #__relate_listings (id1, id2, creator) VALUES ";
			$first = 1; 
			foreach ($new_relations as $aid) {
				if ($listing_id == $aid) continue;
				if (!$first) $sql .= ",";
				else $first = 0;
				$sql .= "('$listing_id', '$aid', '$creator'),";
				$sql .= "('$aid', '$listing_id', '$creator')";
				$cAdd += 2;
			}
			$this->_db->setQuery($sql);
			$this->_db->query();
		}

		if ($act_stream) {
			$this->add_stream($listing_id, $addIds);
		}
		
		// pass-through :- if cat id = 3,4 : get parent Location, $this->add_listings($parent_id, $addIds, false) 
		$sql = "SELECT catid FROM #__content WHERE id = '$listing_id'";
		$this->_db->setQuery($sql);
		$catid = $this->_db->loadResult();
		
		if ($catid == 3 || $catid == 4) {
			$sql = "SELECT r.id2 FROM #__content c, #__relate_listings r ".
			       "WHERE r.id1 = '$listing_id' AND r.id2 = c.id AND c.catid IN (1, 2, 100) ";
			$this->_db->setQuery($sql);
			$parent_id = $this->_db->loadResult();
			if ($parent_id) {
				$sql = "SELECT id2 FROM #__relate_listings WHERE id1 = $parent_id";
				$this->_db->setQuery($sql);
				$parent_relations = $this->_db->loadResultArray();
				$parent_add = array_diff($addIds, $parent_relations);
				$this->add_listings($parent_id, $parent_add, false, $creator);
			}
		}
		
		return $cAdd;
	}

	function add_photos($listing_id, $addIds, $act_stream = true)
	{
		return $this->_add_jomsocial("photo", $listing_id, $addIds, $act_stream);
	}

	function add_videos($listing_id, $addIds, $act_stream = true)
	{
		return $this->_add_jomsocial("video", $listing_id, $addIds, $act_stream);
	}

	function _add_jomsocial($type, $listing_id, $addIds, $act_stream = true)
	{
		$user =& JFactory::getUser();

		if ($addIds) {
			// filter out any ids that are duplicates of existing relations
			$sql = "SELECT ".$type."_id FROM #__relate_".$type."s WHERE listing_id = $listing_id";
			$this->_db->setQuery($sql);
			$current_relations = $this->_db->loadResultArray();
			$new_relations = array_diff($addIds, $current_relations);
				
			$sql = "INSERT INTO #__relate_".$type."s (listing_id, ".$type."_id, creator) VALUES ";
			$first = 1;
			foreach ($new_relations as $aid) {
				if (!$first) $sql .= ",";
				else $first = 0;
				$sql .= "('$listing_id', '$aid', '$user->id')";
			}
			$this->_db->setQuery($sql);
			$this->_db->query();
		}
		
		if ($act_stream) {
			$this->add_stream($listing_id, $addIds, $type);
		}
		
		// pass-through :- if cat id = 3,4 : get parent Location, $this->add_listings($parent_id, $addIds, false) 
		/*
		$sql = "SELECT catid FROM #__content WHERE id = '$listing_id'";
		$this->_db->setQuery($sql);
		$catid = $this->_db->loadResult();
		
		if ($catid == 3 || $catid == 4) {
			$sql = "SELECT r.id2 FROM #__content c, #__relate_listings r ".
			       "WHERE r.id1 = '$listing_id' AND r.id2 = c.id AND c.catid IN (1, 2, 100) ";
			$this->_db->setQuery($sql);
			$parent_id = $this->_db->loadResult();
			if ($parent_id) {
				$sql = "SELECT ".$type."_id FROM #__relate_".$type."s WHERE listing_id = $parent_id";
				$this->_db->setQuery($sql);
				$parent_relations = $this->_db->loadResultArray();
				$parent_add = array_diff($addIds, $parent_relations);
				$this->_add_jomsocial($type, $parent_id, $parent_add, false);
			}
		}
		*/
		
		return $this->_db->getAffectedRows();
	}

	function remove_listings($listing_id, $remIds) 
	{
		$user =& JFactory::getUser();
		$cDel = 0;

		if ($remIds) {
			$sql = "DELETE FROM #__relate_listings WHERE ";
			if ($user->usertype != "Super Administrator") 
				$sql .= "creator='$user->id' AND ";
			$sql .= "(";
			$first = 1; 
			foreach ($remIds as $rid) {
				if (!$first) $sql .= " OR ";
				else $first = 0;
				$sql .= "((id1='$listing_id' AND id2='$rid') OR (id1='$rid' AND id2='$listing_id'))";
				$cDel += 2;
			}
			$sql .= ")";
			$this->_db->setQuery($sql);
			$this->_db->query();
		}

		return $cDel;
	}

	function remove_photos($listing_id, $remIds) 
	{
		return $this->_remove_jomsocial("photo", $listing_id, $remIds);
	}

	function remove_videos($listing_id, $remIds) 
	{
		return $this->_remove_jomsocial("video", $listing_id, $remIds);
	}

	function _remove_jomsocial($type, $listing_id, $remIds) 
	{
		$user =& JFactory::getUser();

		if ($remIds) {
			$sql = "DELETE FROM #__relate_".$type."s WHERE ";
			if ($user->usertype != "Super Administrator") 
				$sql .= "creator='$user->id' AND ";
			$sql .= "(";
			$first = 1; 
			foreach ($remIds as $rid) {
				if (!$first) $sql .= " OR ";
				else $first = 0;
				$sql .= "(listing_id = '$listing_id' AND ".$type."_id = '$rid')";
			}
			$sql .= ")";
			$this->_db->setQuery($sql);
			$this->_db->query();
		}

		return $this->_db->getAffectedRows();
	}
}

