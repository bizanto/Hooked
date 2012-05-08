<?php

defined('_JEXEC') or die( 'Restricted access' );

class JReviewsListingsHelper extends JObject {

	protected $db			= null;
	protected $wheres		= array();
	protected $filters		= array();
	protected $plugin		= array();

	public function __construct($plugin)
	{
		$this->set('db', JFactory::getDBO());
		$this->set('plugin', $plugin);
	
		$this->filters['category'] 	= JRequest::getVar('category', null);
		$this->filters['section']	= JRequest::getVar('section', null);
		$this->filters['id']		= JRequest::getVar('id', null);
		$this->filters['date']		= JRequest::getInt('date', 0);
		$this->filters['user']		= JRequest::getVar('user', 0);
		$this->filters['maxlng']	= JRequest::getVar('maxlng', 0);
		$this->filters['maxlat']	= JRequest::getVar('maxlat', 0);
		$this->filters['minlng']	= JRequest::getVar('minlng', 0);
		$this->filters['minlat']	= JRequest::getVar('minlat', 0);
		$this->filters['offset']	= JRequest::getInt('offset', 0);
		$this->filters['limit']		= JRequest::getInt('limit', 0);
		$this->filters['related']	= JRequest::getVar('related', null);
	}

	public function getListings()
	{
	
		if ($this->filters['limit'] > 500 || $this->filters['limit'] == 0)
			$this->filters['limit'] = 500;
	
		
		$query = $this->getQuery();
		$this->db->setQuery($query, $this->filters['offset'], $this->filters['limit']);
		$listings = $this->db->loadObjectList();
		$listings = $this->filterList($listings);
		return $listings;
	}

	private function getQuery()
	{
		$where = $this->buildWhere();
		$order = $this->buildOrder();
		$query = "SELECT c.id, c.title, c.alias, c.introtext, c.fulltext, c.catid, c.sectionid, c.created, c.created_by, c.modified, jr.* "
				."FROM #__content AS c "
				."INNER JOIN #__jreviews_content AS jr ON jr.contentid = c.id "
				.$where
				.$order
				;
				
		return $query;
	}

	private function buildOrder()
	{
		if ($this->filters['category'] == 14)
		{
			$field = 'jr.jr_startdate';
		}
		else
		{
			$field = 'c.created';
		}
		
		$order = " ORDER BY ".$field." DESC";
		
		return $order;
	}

	private function buildWhere()
	{
	
		if ($this->filters['id'])
		{
			$this->wheres[] = $this->splitForQuery($this->filters['id'], 'c.id');
		}
		
		if ($this->filters['category'])
		{
			$this->wheres[] = $this->splitForQuery($this->filters['category'], 'c.catid');
		}
	
		if ($this->filters['section'])
		{
			$this->wheres[] = $this->splitForQuery($this->filters['section'], 'c.sectionid');
		}
		
		if ($this->filters['date'])
		{
			$min = gmdate("Y-m-d H:i:s", $this->filters['date']);
			$this->wheres[] = '(c.modified >= '.$this->db->Quote($min).' OR c.created >= '.$this->db->Quote($min).')';
		}
	
		if ($this->filters['user'])
		{
			$this->wheres[] = $this->splitForQuery($this->filters['user'], 'c.created_by');
		}
		
		if ($this->filters['maxlng'])
		{
			$this->wheres[] = 'jr.jr_long <= '.$this->db->Quote($this->filters['maxlng']);
		}
		
		if ($this->filters['minlng'])
		{
			$this->wheres[] = 'jr.jr_long >= '.$this->db->Quote($this->filters['minlng']);
		}
		
		if ($this->filters['maxlat'])
		{
			$this->wheres[] = 'jr.jr_lat <= '.$this->db->Quote($this->filters['maxlat']);
		}
		
		if ($this->filters['minlat'])
		{
			$this->wheres[] = 'jr.jr_lat >= '.$this->db->Quote($this->filters['minlat']);
		}
	
		if ($this->filters['related'])
		{
			$subquery_where = $this->splitForQuery($this->filters['related'], 'id1');
			$this->wheres[] = "c.id IN (SELECT id2 FROM #__relate_listings WHERE ".$subquery_where.")";
		}		
	
		$this->wheres[] = "c.state = 1";
	
		// Add privacy stuff
		$sql = "SELECT connect_to FROM #__community_connection WHERE connect_from = '".$this->plugin->get('user')->id."'";
		$this->db->setQuery($sql);
		$friendsList = $this->db->loadResultArray();
		
		$privacy_where = "(jr.jr_privacy IN ('*offentlig*', '*site-members*', '')";
		if ($friendsList)
		{
			$friends = "('".implode("','",$friendsList)."')";
			$privacy_where .=  " OR (jr.jr_privacy IN ('*friends*', '*venner*') AND c.created_by IN $friends)";
		}
		
		$privacy_where .= " OR (jr.jr_privacy = '*privat*' AND c.created_by = '".$this->plugin->get('user')->id."')";
		
		$privacy_where .= ")";
		
		$this->wheres[] = $privacy_where;
		
		// add support for anonymous = *ja*
		
		$where = "WHERE ".implode(" AND ", $this->wheres);
		
		return $where;
	}
	
	private function splitForQuery($string, $field)
	{
		$ids = preg_split('/\s*,\s*/', $string);
		JArrayHelper::toInteger($ids);
		if (count($categories) == 1)
		{
			$where = $field.' = '.$db->Quote($ids[0]);
		} 
		else
		{
			$where = $field.' IN ('.implode(',',$ids).')';
		}
	
		return $where;
	}

	private function filterList($listings)
	{
		$filtered = array();
		for($i=0; $i<count($listings); $i++)
		{
			
			$listing = $listings[$i];
			$this->addRelations($listing);
			unset($listing->email);
			$filtered[] = $listing;
			/*
			$main = array($listing->jr_lat, $listing->jr_long);
			
			$coords = array();
			$coords[] = $main;
			
			if ($listing->jr_extracoords)
			{	
				$extras = json_decode($listing->jr_extracoords, true);
				
				foreach($extras as $e)
				{
					$coords[] = array($e['y'], $e['x']);
				}
			}
			
			$coords_pass = array(
				'maxlng' => true,
				'maxlat' => true,
				'minlng' => true,
				'minlat' => true
			);
			
			if ($this->filters['maxlng'])
			{
				$coords_pass['maxlng'] = false;
				foreach($coords as $coord)
				{
					if ($coord[1] && $coord[1] <= $this->filters['maxlng']) $coords_pass['maxlng'] = true;
				}
			}
			
			if ($this->filters['maxlat'])
			{
				$coords_pass['maxlat'] = false;
				foreach($coords as $coord)
				{
					if ($coord[0] && $coord[0] <= $this->filters['maxlat']) $coords_pass['maxlat'] = true;
				}
			}
			
			if ($this->filters['minlng'])
			{
				$coords_pass['minlng'] = false;
				foreach($coords as $coord)
				{
					if ($coord[1] && $coord[1] >= $this->filters['minlng']) $coords_pass['minlng'] = true;
				}
			}
			
			if ($this->filters['minlat'])
			{
				$coords_pass['minlat'] = false;
				foreach($coords as $coord)
				{
					if ($coord[0] && $coord[0] >= $this->filters['minlat']) $coords_pass['minlat'] = true;
				}
			}
			//var_dump($coords_pass);
			if (!in_array(false, $coords_pass))
			{
				/* Add relations */
				
			//}
			
			
		}
		
		return $filtered;
	}

	public function addRelations(&$listing)
	{
		if ($listing->catid == 14) { // category 14 - catch reports
		
			$listing->related_fish = RelateAPIHelper::getRelated($listing->id, array(17));
			$listing->fish_count = count($listing->related_fish);
			
			
			
			$spots = RelateAPIHelper::getRelated($listing->id, array(3,4,1,2,100));
			
			$listing->spot_id = $spots[0];
			
			if ($listing->spot_id)
			{
				$parent = RelateAPIHelper::getRelated($listing->spot_id, array(1,2,100));
				$listing->parent_spot = $parent[0];
			}
			else
			{
				$listing->parent_spot = 0;
			}
			
			//$listing->related_trips = RelateAPIHelper::getRelated($listing->id, array(14));
			
			$listing->bait         = RelateAPIHelper::getRelated($listing->id, array(101,102)); // bait categories
		}
		
		if ($listing->sectionid == 1)
		{
			$listing->related_fish = RelateAPIHelper::getRelated($listing->id, array(17));
			$listing->fish_count = count($listing->related_fish);
			
			if ($listing->catid == 3 || $listing->catid == 4)
			{
				$parent = RelateAPIHelper::getRelated($listing->id, array(1,2,100));
				$listing->parent_spot = $parent[0];
			} else {
				$listing->related_spots = RelateAPIHelper::getRelated($listing->id, array(3,4));
			
			}
		
			$listing->related_catches = RelateAPIHelper::getRelated($listing->id, array(14));
			$listing->catch_count = count($listing->related_catches);
		
			$listing->related_techniques = RelateAPIHelper::getRelated($listing->id, array(24,48.79));
			$listing->technique_count = count($listing->related_catches);
		
			$db = JFactory::getDBO();
			$query = "SELECT DISTINCT(c.created_by) FROM #__content c WHERE c.catid = 14 AND c.id IN (
						SELECT r.id2 FROM #__relate_listings r WHERE r.id1 = '$listing->id'
					)";
			$db->setQuery($query);
			$listing->related_users = $db->loadResultArray();
		}
		
		$listing->photos = RelateAPIHelper::getPhotos($listing->id);
		$listing->videos = RelateAPIHelper::getVideos($listing->id);
		
		$listing->average_rating = $this->getAverageRating($listing->id);
		$listing->comment_count = $this->getCommentCount($listing->id);
		
		// check anonymous *ja*, cut out spot_id, zip, state, lat, long
		if ($listing->ja_catchanonymous == '*ja*' && $listing->created_by != $this->get('user')->id)
		{
			$listing->spot_id = null;
			$listing->parent_spot = null;
			$listing->jr_state = null;
			$listing->jr_city = null;
			$listing->jr_zip = null;
			$listing->jr_lat = null;
			$listing->jr_long = null;
		}
		
	}

	public function getRelatedItem($listing, $cids = array()) {
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
		
		return $this->getListing($related_id);
	}

	private function getRelatedUsers($id, $cat = array())
	{
		if ($cat) $where_cat = "AND c.catid IN(".implode(",",$cat).")";
		else $where_cat = "";
		
		$db =& JFactory::getDBO();
		$query = "SELECT DISTINCT(c.created_by) "
				."FROM #__content AS c "
				."INNER JOIN #__relate_listings AS r ON (r.id1 = $id AND r.id2 = c.id) "
				."WHERE c.state > 0 $where_cat"
				;
		$db->setQuery($query);
		return $db->loadResultArray();
	}

	private function getAverageRating($id)
	{
		$query = "SELECT AVG(r.ratings_sum) "
				."FROM #__jreviews_comments AS c "
				."INNER JOIN #__jreviews_ratings AS r ON r.reviewid = c.id "
				."WHERE c.pid = ".(int)$id;
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$rating = $db->loadResult();
		
		return $rating;
	}
	
	private function getCommentCount($id)
	{
		$query = "SELECT COUNT(*) FROM #__jreviews_comments WHERE pid = ".(int)$id;
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$count = $db->loadResult();
		return $count;
	}

}