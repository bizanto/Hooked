<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');

require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

class RelateModelCreate extends JModel 
{
	var $spots_exclude = array();
	
	function getField($fieldname)
	{
		$sql = "SELECT o.text, o.value FROM #__jreviews_fieldoptions o, #__jreviews_fields f ".
		       "WHERE f.name = '$fieldname' and o.fieldid = f.fieldid"; 
		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}

	function getParentLocation($spot) 
	{		
		$spot_id = $spot->id;
		
		$sql = "SELECT c.id, c.title, c.catid, c.sectionid, cat.title AS category ".
		       "FROM `#__content` c, `#__categories` cat, `#__relate_listings` r ".
		       "WHERE (r.id1 = $spot_id AND r.id2 = c.id) AND c.catid = cat.id ".
		       "AND c.id != '$spot_id' AND c.state > 0 AND c.catid IN (1, 2, 100)"; // category is lakes, rivers, or fjords
		$this->_db->setQuery($sql);
		$parentLoc = $this->_db->loadObject();

		return $parentLoc;
	}
	
	function getChildSpots($parentLoc) 
	{
		$sql = "SELECT c.id, c.title, c.catid, c.sectionid, cat.title AS category, jr.jr_state AS state ".
		       "FROM #__content c, #__categories cat, #__relate_listings r, #__jreviews_content jr ".
		       "WHERE (r.id1 = ".$parentLoc->id." AND r.id2 = c.id) AND c.catid = cat.id ".
		       "AND c.id = jr.contentid AND c.state > 0 AND c.catid IN (3, 4)"; // catid 3, 4 : freshwater/saltwater spots
		$sql .= " ORDER BY c.title ASC";
		$this->_db->setQuery($sql);
		$childSpots = $this->_db->loadObjectList('id');
		
		// make sure we don't get these spots again when searching for spots 
		// whose parent location name didnt match the search keyword
		$this->spots_exclude = array_merge($this->spots_exclude, array_keys($childSpots));
		
		foreach ($childSpots as $key => $spot) {
			$childSpots[$key]->category = "Spot @ ".$parentLoc->title;
			$childSpots[$key]->child = 1;
			$childSpots[$key]->url = ContentHelperRoute::getArticleRoute($spot->id, $spot->catid, $spot->sectionid);
		}
		
		return $childSpots;
	}
	
	function getSpots($searchword = '', $state = '', $start = 0, $limit = 100) 
	{
		$sql = "SELECT c.id, c.title, c.sectionid, c.catid, cat.title AS category, jr.jr_state AS state ".
		       "FROM #__content c ".
		       "LEFT JOIN #__categories cat ON c.catid = cat.id ".
		       "LEFT JOIN #__jreviews_content jr ON c.id = jr.contentid ".
		       "WHERE c.catid IN (3, 4) AND c.state > 0 "; // catid 3, 4 -> freshwater/saltwater spots

		if (count($this->spots_exclude)) {
			$sql .= "AND c.id NOT IN (".implode(",", $this->spots_exclude).") ";
		}
		
		if ($searchword != '') {
			$sql .= " AND c.title LIKE '%$searchword%'";
		}
		
		if ($state != '') {
			$sql .= " AND jr.jr_state = '*$state*'";
		}
		
		$sql .= " ORDER BY c.title ASC LIMIT $start,$limit";
		
		$this->_db->setQuery($sql);
		$spots = $this->_db->loadObjectList();
		
		foreach ($spots as $idx => $spot) {
			$parentLoc = $this->getParentLocation($spot);
			$spots[$idx]->category = "Spot @ ".$parentLoc->title;
			$spots[$idx]->url = ContentHelperRoute::getArticleRoute($spot->id, $spot->catid, $spot->sectionid);
		}
		
		return $spots;
	}
	
	function getLocations($getSpots = true, $searchword = '', $state = '', $start = 0, $limit = 100)
	{
		$sql = "SELECT c.id, c.title, c.sectionid, c.catid, cat.title AS category, jr.jr_state AS state ".
		       "FROM #__content c ".
		       "LEFT JOIN #__categories cat ON c.catid = cat.id ".
		       "LEFT JOIN #__jreviews_content jr ON c.id = jr.contentid ".
		       "WHERE c.catid IN (1, 2, 100) AND c.state > 0"; // catid 1, 2, 100 -> lakes/rivers/fjords
		
		if ($searchword != '') {
			$sql .= " AND c.title LIKE '%$searchword%'";
		}
		
		if ($state != '') {
			$sql .= " AND jr.jr_state = '*$state*'";
		}
		
		$sql .= " ORDER BY c.title ASC LIMIT $start,$limit";
		
		$this->_db->setQuery($sql);
		$locations = $this->_db->loadObjectList();
		
		$locResults = array();
		foreach ($locations as $loc) {
			if ($loc->state) {
				$loc->category .= ", ".ucfirst(str_replace('*', '', $loc->state));
			}
			$loc->url = ContentHelperRoute::getArticleRoute($loc->id, $loc->catid, $loc->sectionid);
			$locResults[] = $loc;
			if ($getSpots) { 
				$spots = $this->getChildSpots($loc);
				if ($spots) {
					$locResults = array_merge($locResults, $spots);
				}
			}
		}
		
		// search for spots which match search terms (but parent location didn't)
		if ($getSpots) {
			$spots = $this->getSpots($searchword, $state, $start, $limit);
			if ($spots) {
				$locResults = array_merge($locResults, $spots);
			}
		}

		return $locResults;
	}

	function getFavorites()
	{
		$user =& JFactory::getUser();
		$sql = "SELECT c.id, c.title, cat.title AS category FROM #__content c ".
		       "LEFT JOIN #__categories cat ON c.catid = cat.id ".
		       "WHERE c.id IN (SELECT content_id FROM #__jreviews_favorites WHERE user_id = '".$user->id."') ".
		       "AND c.state > 0 AND c.sectionid = 1"; // section 1 = locations / fishing spots
		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}

	function getSpecies()
	{
		$sql = "SELECT c.id, c.title FROM #__content c, #__categories cat ".
		       "WHERE cat.id = c.catid AND cat.title = 'Fiskepedia' AND c.state > 0 ".
		       "ORDER BY c.title ASC";
		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}

	function getEquipment()
	{
		$sql = "SELECT c.id, c.title FROM #__content c, #__sections section ".
		       "WHERE section.id = c.sectionid AND section.title = 'Fiskeutstyr' AND c.state > 0";
		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}
	
	function getBait()
	{
		$sql = "SELECT c.id, c.title FROM #__content c ".
		       "WHERE c.catid IN (101, 102) AND c.state > 0 ".
		       "ORDER BY c.title ASC";
		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}
	
	function getInsects()
	{
		$sql = "SELECT c.id, c.title, c.catid, cat.title AS category ".
		       "FROM #__content c, #__categories cat, #__sections section ".
		       "WHERE section.id = c.sectionid AND c.catid = cat.id ".
		       "AND section.title = 'Insektpedia' AND c.state > 0";
		$this->_db->setQuery($sql);
		return $this->_db->loadObjectList();
	}

	function saveCatches($location_id, $catches, $media)
	{
		if ($location_id) {
			$sql = "SELECT id, title, catid, sectionid FROM #__content WHERE id = '$location_id'";
			$this->_db->setQuery($sql);
			$spot = $this->_db->loadObject();
			$spotname = $spot->title;
			
			$parentLoc = $this->getParentLocation($spot);
		}
		
		$sql = "SELECT id, section FROM #__categories WHERE title = 'Fangstrapporter'";
		$this->_db->setQuery($sql);
		$cat = $this->_db->loadObject();
		
		$user     =& JFactory::getUser();
		$relate   =& JModel::getInstance('relate', 'RelateModel');

		$response = array();
		
		foreach ($catches as $catch) {
			$species_id = $catch['related']['species'];
			$sql = "SELECT title FROM #__content WHERE id = '$species_id'";
			$this->_db->setQuery($sql);
			$fishname = $this->_db->loadResult();
			
			$catchweight = $catch['fields']['jr_catchweight'];
			
			// com_content:
			// title, alias?, state, sectionid, catid, created, created_by, publish_up
			$article  =& JTable::getInstance('content');
			$filter = new JFilterInput(array(), array(), 1, 1);
			
			if ($catch['fields']['jr_catchanonymous'] == '*ja*') {
				$article->title = "$fishname ($catchweight gr)";
			}
			else {
				$article->title = "$fishname ($catchweight gr) @ $spotname";
			}
			$article->alias = JFilterOutput::stringURLSafe($article->title);
			$article->introtext = $filter->clean($catch['description']);
			$article->fulltext = '';
			$article->state = 1; // published
			$article->sectionid = $cat->section;
			$article->catid = $cat->id;
			$article->created = gmdate('Y-m-d H:i:s');
			$article->created_by = $user->id;
			$article->publish_up = $article->created;
			
			if (!$article->store()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			
			$article_id = $article->_db->insertId();
			$article->id = $article_id;
			
			// jreviews_content: 
			// contentid, email, jr_{...}
			$catch['fields']['contentid'] = $article_id;
			$catch['fields']['email'] = $user->email;
			
			$columns = implode(",", array_keys($catch['fields']));
			$values  = implode("','", array_values($catch['fields']));
			$sql = "INSERT INTO `#__jreviews_content` ($columns) VALUES ('$values')";
			$this->_db->setQuery($sql);
			
			if (!$this->_db->query()) {	
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
						
			// then add relations
			if ($location_id) {
				$catch['related']['location'] = $location_id;
			}
			$relate->add_listings($article_id, $catch['related'], false);
			
			// pass-through: relations added to catch should be added to location as well
			if ($location_id) {
				$relate->add_listings($location_id, $catch['related'], false);
			}
			
			$article->short_title = "$fishname ($catchweight gr)"; // don't want spot name in title when adding to stream
			$article->anonymous = ($catch['fields']['jr_catchanonymous'] == '*ja*');
			$this->_streamAddCatch($article, $spot, $parentLoc);
			
			if (isset($media['photos'])) {
				$thumbs = str_replace(JURI::base(), '', $media['photos']);
				$sql = "SELECT id FROM #__community_photos WHERE thumbnail IN ('".implode("','", $thumbs)."')";
				$this->_db->setQuery($sql);
				$photo_ids = $this->_db->loadResultArray();
				
				$relate->add_photos($article_id, $photo_ids, false);
				$relate->add_photos($location_id, $photo_ids, false); // pass-through: catch photos should also show on parent location
			
				if (isset($media['descriptions'])){
					foreach ($media['descriptions'] as $thumb => $desc) {
						$thumb = str_replace(JURI::base(), '', $thumb);
						
						$sql = "UPDATE #__community_photos SET caption = '$desc' WHERE thumbnail = '$thumb'";
						$this->_db->setQuery($sql);
						$this->_db->query();
					}
				}
			}
			
			$thumb = '';
			if (isset($media['photos']) && count($media['photos'])) {
				$thumb = $media['photos'][0];
				$article->thumbnail = $thumb;
			}
			$article_link = ContentHelperRoute::getArticleRoute($article_id, $article->catid, $article->sectionid);
			$article->link = $article_link;
			$response[] = array('id' => $article->id, 'title' => $article->title, 'link' => $article->link, 'thumbnail' => $thumb);
		
			if ($catch['fb_post'] == '*ja*') {
				facebook_share($article, JText::sprintf('FACEBOOK CATCH MESSAGE', $article->title));
			}
		}
		
		return $response;
	}
	
	function _streamAddCatch($catch, $spot, $parentLoc = null) 
	{
		include_once( JPATH_BASE . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');  
		
		$catch_uri = ContentHelperRoute::getArticleRoute($catch->id, $catch->catid, $catch->sectionid);
		$catch_link = '<a href="'.$catch_uri.'">'.$catch->short_title.'</a>';
		
		if (!$catch->anonymous && $spot) {
			$spot_uri = ContentHelperRoute::getArticleRoute($spot->id, $spot->catid, $spot->sectionid);
			$spot_link = '<a href="'.$spot_uri.'">'.$spot->title.'</a>';
			
			if ($parentLoc) {
				$parent_uri = ContentHelperRoute::getArticleRoute($parentLoc->id, $parentLoc->catid, $parentLoc->sectionid);
				$parent_link = 'in <a href="'.$parent_uri.'">'.$parentLoc->title.' ('.$parentLoc->category.')</a>';
			}
			else {
				$parent_link = '';
			}
		}
		else $catch->anonymous = true; // set to true in case no spot was specified but anonymous wasnt set
		
		// make sure the language file is loaded (if this was called from the api)
		$lang =& JFactory::getLanguage();
		$lang->load('com_relate');
		
		$act = new stdClass();
		$act->cmd     = 'wall.write';
		$act->actor   = $catch->created_by;
		$act->target  = 0;
		if ($catch->anonymous) {
			$act->title   = JText::sprintf('CAUGHT A ANON', $catch_link);
		}
		else {
			$act->title   = JText::sprintf('CAUGHT A LOC', $catch_link, $spot_link, $parent_link);
		}
		$act->content = '';
		$act->app     = 'wall';
		$act->cid     = 0;

		CFactory::load('libraries', 'activities');
		CActivityStream::add($act);
	}

	function createSpot($title, $description, $fields, $related, $fb_share = 0)
	{
		$user     =& JFactory::getUser();
		$relate   =& JModel::getInstance('relate', 'RelateModel');

		$q = "SELECT c.id, c.title, c.catid, c.sectionid, cat.title AS category ".
		     "FROM #__content c, #__categories cat ".
		     "WHERE c.catid = cat.id AND c.id = ".$related['location'];		     
		$this->_db->setQuery($q);
		$location = $this->_db->loadObject();

		// com_content:
		$article  =& JTable::getInstance('content');
		$filter = new JFilterInput(array(), array(), 1, 1);

		$article->title = trim($title);
		$article->alias = JFilterOutput::stringURLSafe($article->title);
		$article->introtext = $filter->clean($description);
		$article->fulltext = '';
		$article->state = 1; // published
		$article->sectionid = 1; // section 1 -> Fiskeplasser
		$article->catid = ($location->catid == 100) ? 3 : 4; // saltwater spot if parent location is fjord, otherwise freshwater
		$article->created = gmdate('Y-m-d H:i:s');
		$article->created_by = $user->id;
		$article->publish_up = $article->created;
		
		if (!$article->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$article_id = $article->_db->insertId();
		$article->id = $article_id;

		// jreviews_content: 
		// contentid, email, jr_{...}
		$fields['contentid'] = $article_id;
		$fields['email'] = $user->email;

		$columns = implode(",", array_keys($fields));
		$values  = implode("','", array_values($fields));
		$sql = "INSERT INTO `#__jreviews_content` ($columns) VALUES ('$values')";
		$this->_db->setQuery($sql);

		if (!$this->_db->query()) {	
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// then add relations
		$relate->add_listings($article_id, $related, false);
		
		$this->_streamAddSpot($article, $location, $related);

		$article_link = ContentHelperRoute::getArticleRoute($article_id, $article->catid, $article->sectionid);
		$article->link = $article_link;
		$thumb = '';
		$response = array();
		$response[] = array('id' => $article_id, 'title' => $article->title, 'link' => $article_link, 'thumbnail' => $thumb);

		if ($fb_share) {
			facebook_share($article, JText::sprintf('FACEBOOK SPOT MESSAGE', $location->title, $article->title));
		}
			
		return $response;
	}
	
	function _streamAddSpot($spot, $parent, $related = null) 
	{
		if (!$related) {
			$db =& JFactory::getDBO();
			$sql = "SELECT id2 FROM #__relate_listings WHERE id1 = ".$spot->id;
			$db->setQuery($sql);
			$related = $db->loadResultArray();
		}
		
		include_once( JPATH_BASE . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');  
		
		$parent_uri = ContentHelperRoute::getArticleRoute($parent->id, $parent->catid, $parent->sectionid);
		$parent_link = '<a href="'.$parent_uri.'">'.$parent->title.' ('.$parent->category.')</a>';

		$spot_uri = ContentHelperRoute::getArticleRoute($spot->id, $spot->catid, $spot->sectionid);
		$spot_link = '<a href="'.$spot_uri.'">'.$spot->title.'</a>';
		
		$related_ids = implode(',', $related);
		
		$q = "SELECT COUNT(*) FROM #__content WHERE catid = 17 AND id IN (".$related_ids.")";
		$this->_db->setQuery($q);
		$num_species = $this->_db->loadResult();
		
		$q = "SELECT COUNT(*) FROM #__content WHERE catid IN (24, 48, 79) AND id IN (".$related_ids.")";
		$this->_db->setQuery($q);
		$num_tech = $this->_db->loadResult();
		
		// make sure the language file is loaded (if this was called from the api)
		$lang =& JFactory::getLanguage();
		$lang->load('com_relate');
		
		if ($num_species == 0) $msg = 'NEW SPOT TEK';
		else $msg = 'NEW SPOT MSG';
		
		$act = new stdClass();
		$act->cmd     = 'wall.write';
		$act->actor   = $spot->created_by;
		$act->target  = 0;
		$act->title   = JText::sprintf($msg, $parent_link, $spot_link, $num_species, $num_tech);
		$act->content = '';
		$act->app     = 'wall';
		$act->cid     = 0;

		CFactory::load('libraries', 'activities');
		CActivityStream::add($act);
	}
	
	function createTrip($title, $description, $fields, $related, $media, $fb_share)
	{
		$sql = "SELECT id, section FROM #__categories WHERE title = 'Turrapporter'";
		$this->_db->setQuery($sql);
		$cat = $this->_db->loadObject();
		
		$user     =& JFactory::getUser();
		$relate   =& JModel::getInstance('relate', 'RelateModel');
		
		// com_content:
		$article  =& JTable::getInstance('content');
		$filter = new JFilterInput(array(), array(), 1, 1);

		$article->title = trim($title);
		$article->alias = JFilterOutput::stringURLSafe($article->title);
		$article->introtext = $filter->clean($description);
		$article->fulltext = '';
		$article->state = 1; // published
		$article->sectionid = $cat->section; 
		$article->catid = $cat->id;
		$article->created = gmdate('Y-m-d H:i:s');
		$article->created_by = $user->id;
		$article->publish_up = $article->created;
		
		if (!$article->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$article_id = $article->_db->insertId();
		$article->id = $article_id;

		// jreviews_content: 
		// contentid, email, jr_{...}
		$fields['contentid'] = $article_id;
		$fields['email'] = $user->email;

		$columns = implode(",", array_keys($fields));
		$values  = implode("','", array_values($fields));
		$sql = "INSERT INTO `#__jreviews_content` ($columns) VALUES ('$values')";
		$this->_db->setQuery($sql);

		if (!$this->_db->query()) {	
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// then add relations
		$relate->add_listings($article_id, $related, false);
		
		$this->_streamAddTrip($article);
		
		if (isset($media['photos'])) {
			$thumbs = str_replace(JURI::base(), '', $media['photos']);
			$sql = "SELECT id FROM #__community_photos WHERE thumbnail IN ('".implode("','", $thumbs)."')";
			$this->_db->setQuery($sql);
			$photo_ids = $this->_db->loadResultArray();
			
			$relate->add_photos($article_id, $photo_ids, false);
			$relate->add_photos($location_id, $photo_ids, false); // pass-through: catch photos should also show on parent location
		
			if (isset($media['descriptions'])){
				foreach ($media['descriptions'] as $thumb => $desc) {
					$thumb = str_replace(JURI::base(), '', $thumb);
					
					$sql = "UPDATE #__community_photos SET caption = '$desc' WHERE thumbnail = '$thumb'";
					$this->_db->setQuery($sql);
					$this->_db->query();
				}
			}
		}

		$article_link = ContentHelperRoute::getArticleRoute($article_id, $article->catid, $article->sectionid);
		$article->link = $article_link;
		$thumb = '';
		if (isset($media['photos']) && count($media['photos'])) {
			$thumb = $media['photos'][0];
			$article->thumbnail = $thumb;
		}
		$response = array();
		$response[] = array('id' => $article_id, 'title' => $article->title, 'link' => $article_link, 'thumbnail' => $thumb);

		if ($fb_share) {
			facebook_share($article, JText::_('FACEBOOK TRIP MESSAGE'));
		}
			
		return $response;
	}
	
	function _streamAddTrip($trip)
	{
		include_once( JPATH_BASE . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');  

		$trip_uri = ContentHelperRoute::getArticleRoute($trip->id, $trip->catid, $trip->sectionid);
		$trip_link = '<a href="'.$trip_uri.'">'.$trip->title.'</a>';
		
		// make sure the language file is loaded (if this was called from the api)
		$lang =& JFactory::getLanguage();
		$lang->load('com_relate');
		
		$act = new stdClass();
		$act->cmd     = 'wall.write';
		$act->actor   = $trip->created_by;
		$act->target  = 0;
		$act->title   = JText::sprintf('WROTE A TRIP', $trip_link);
		$act->content = '';
		$act->app     = 'wall';
		$act->cid     = 0;

		CFactory::load('libraries', 'activities');
		CActivityStream::add($act);
	}

	function createHatch($location_id, $description, $fields, $related, $media, $fb_share) 
	{
		if ($location_id) {
			$sql = "SELECT id, title, catid, sectionid FROM #__content WHERE id = '$location_id'";
			$this->_db->setQuery($sql);
			$spot = $this->_db->loadObject();
			$spotname = $spot->title;
			
			$parentLoc = $this->getParentLocation($spot);
		}
		
		$sql = "SELECT id, section FROM #__categories WHERE title = 'Klekkerapporter'";
		$this->_db->setQuery($sql);
		$cat = $this->_db->loadObject();
		
		$user     =& JFactory::getUser();
		$relate   =& JModel::getInstance('relate', 'RelateModel');
		
		$insect_id = $related['insects'];
		$sql = "SELECT title FROM #__content WHERE id = '$insect_id'";
		$this->_db->setQuery($sql);
		$insectname = $this->_db->loadResult();
		
		// com_content:
		$article  =& JTable::getInstance('content');
		$filter = new JFilterInput(array(), array(), 1, 1);

		//$article->title = trim($title);
		if ($fields['jr_catchanonymous'] == '*ja*') {
			$article->title = "$insectname";
		}
		else {
			$article->title = "$insectname @ $spotname";
		}
		$article->alias = JFilterOutput::stringURLSafe($article->title);
		$article->introtext = $filter->clean($description);
		$article->fulltext = '';
		$article->state = 1; // published
		$article->sectionid = $cat->section; 
		$article->catid = $cat->id;
		$article->created = gmdate('Y-m-d H:i:s');
		$article->created_by = $user->id;
		$article->publish_up = $article->created;
		
		if (!$article->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$article_id = $article->_db->insertId();
		$article->id = $article_id;

		// jreviews_content: 
		// contentid, email, jr_{...}
		$fields['contentid'] = $article_id;
		$fields['email'] = $user->email;

		$columns = implode(",", array_keys($fields));
		$values  = implode("','", array_values($fields));
		$sql = "INSERT INTO `#__jreviews_content` ($columns) VALUES ('$values')";
		$this->_db->setQuery($sql);

		if (!$this->_db->query()) {	
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// then add relations
		if ($location_id) {
			$related['location'] = $location_id;
		}
		$relate->add_listings($article_id, $related, false);
		
		$this->_streamAddHatch($article); //, $location, $related);
		
		if (isset($media['photos'])) {
			$thumbs = str_replace(JURI::base(), '', $media['photos']);
			$sql = "SELECT id FROM #__community_photos WHERE thumbnail IN ('".implode("','", $thumbs)."')";
			$this->_db->setQuery($sql);
			$photo_ids = $this->_db->loadResultArray();
			
			$relate->add_photos($article_id, $photo_ids, false);
			$relate->add_photos($location_id, $photo_ids, false); // pass-through: catch photos should also show on parent location
		
			if (isset($media['descriptions'])){
				foreach ($media['descriptions'] as $thumb => $desc) {
					$thumb = str_replace(JURI::base(), '', $thumb);
					
					$sql = "UPDATE #__community_photos SET caption = '$desc' WHERE thumbnail = '$thumb'";
					$this->_db->setQuery($sql);
					$this->_db->query();
				}
			}
		}

		$article_link = ContentHelperRoute::getArticleRoute($article_id, $article->catid, $article->sectionid);
		$article->link = $article_link;
		$thumb = '';
		if (isset($media['photos']) && count($media['photos'])) {
			$thumb = $media['photos'][0];
			$article->thumbnail = $thumb;
		}
		$response = array();
		$response[] = array('id' => $article_id, 'title' => $article->title, 'link' => $article_link, 'thumbnail' => $thumb);

		if ($fb_share) {
			facebook_share($article, JText::sprintf('FACEBOOK HATCH MESSAGE', $insectname, $spotname));
		}
			
		return $response;
	}
	
	function _streamAddHatch($hatch) {
		include_once( JPATH_BASE . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');  

		$hatch_uri = ContentHelperRoute::getArticleRoute($hatch->id, $hatch->catid, $hatch->sectionid);
		$hatch_link = '<a href="'.$hatch_uri.'">'.$hatch->title.'</a>';
		
		// make sure the language file is loaded (if this was called from the api)
		$lang =& JFactory::getLanguage();
		$lang->load('com_relate');
		
		$act = new stdClass();
		$act->cmd     = 'wall.write';
		$act->actor   = $hatch->created_by;
		$act->target  = 0;
		$act->title   = JText::sprintf('ADDED A HATCH', $hatch_link);
		$act->content = '';
		$act->app     = 'wall';
		$act->cid     = 0;

		CFactory::load('libraries', 'activities');
		CActivityStream::add($act);
	}
}

// Core file is required since we need to use CFactory
require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php' );

// Need to include Facebook's PHP API library so we can utilize them.
//require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'facebook' . DS . 'facebook.php' );
//require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'facebook' . DS . 'facebookrest.php' );

require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'facebook_new' . DS . 'facebook.php' );

function facebook_share($article, $message = '')
{
	$config			= CFactory::getConfig();
	$key			= $config->get('fbconnectkey');
	$secret			= $config->get('fbconnectsecret');
	
	$facebook	= new Facebook(array('appId' => $key, 'secret' => $secret, 'cookie' => true));
		
	$post = array(
		'message' => $message, 
		'name' => $article->title, 
		'link' => JURI::base().$article->link,
		'caption' => 'www.hooked.no',
		'description' => $article->introtext,
	);
	
	if ($article->thumbnail) {
		$post['picture'] = $article->thumbnail;
	}
	
	try 
	{
		$statusUpdate = $facebook->api('/me/feed', 'post', $post);
		
		if( !empty($statusUpdate) )
		{
			return true;
		}
		return false;
	}
	catch (FacebookApiException $e)
	{
		return false;
	}
}
