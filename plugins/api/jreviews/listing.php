<?php
/**
 * @package	API
 * @version 1.5
 * @author 	Brian Edgerton
 * @link 	http://www.edgewebworks.com
 * @copyright Copyright (C) 2011 Edge Web Works, LLC. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class ApiResourceListing extends ApiResource {

	private function getRelatedItem($listing, $cids = array()) {
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
	
	function getRelatedThumb($content_id, $limit = 1) {
		$db =& JFactory::getDBO();
		$sql = "SELECT p.id, p.caption, p.creator, p.image, p.thumbnail, p.created, p.storage, u.username ".
			   "FROM #__community_photos p, #__users u WHERE ".
			   "p.id IN (SELECT photo_id FROM #__relate_photos WHERE listing_id = '".$content_id."') ".
			   "AND p.creator = u.id ORDER BY p.id asc";
		if ($limit) {
		$sql .= " LIMIT $limit";
		}
		$db->setQuery($sql);
		
		$relatedThumb = $db->loadObjectList();
		return $relatedThumb;
	}
	
	private function getListing($id) {
		$db = JFactory::getDBO();
		$query = "SELECT c.id, c.title, c.alias, c.catid, c.sectionid, c.introtext, c.fulltext, c.created_by, jr.* "
				."FROM #__content AS c "
				."INNER JOIN #__jreviews_content AS jr ON jr.contentid = c.id "
				."WHERE c.state = 1 "
				."AND c.id = ".$id
				;
		$db->setQuery($query);
		$listing = $db->loadObject();
		
		if ($listing) {
			$listing = $this->filterFields($listing);
			$listing->thumbnail = $this->getRelatedThumb($id);
		}
			
		return $listing;
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
	
	// filter out fields returned by jreviews field groups
	private function filterFields($listing) {
		$db =& JFactory::getDBO();
		$query = "SELECT crit.groupid FROM `#__jreviews_criteria` crit "
		        ."INNER JOIN `#__jreviews_categories` cat ON cat.criteriaid = crit.id "
		        ."WHERE cat.id = ".$listing->catid
		        ;
		$db->setQuery($query);
		$groupid = $db->loadResult();
		        
		$query = "SELECT field.name FROM `#__jreviews_fields` field "
		        ."WHERE field.groupid IN ($groupid)"
		        ;
		$db->setQuery($query);
		$fields = $db->loadResultArray();
		
		foreach ($listing as $key => $value) {
			if (substr($key, 0, 3) == "jr_" && !in_array($key, $fields)) {
				unset($listing->$key);
			}
		}
		
		return $listing;
	}
	
	public function get() {
		$id = JRequest::getInt('id', 0);
		if (!$id)
		{
			$this->plugin->setResponse(null);
			return;
		}	
		
		$listing = $this->getListing($id);
		
		if (!$listing)
		{
			$this->plugin->setResponse(null);
			return;
		}
		
		JLoader::register('RelateAPIHelper', JPATH_SITE.'/plugins/api/relate/helper.php');
		
		if ($listing->catid == 14) { // category 14 - catch reports
			//$listing->related_fish = $this->getRelatedItem($listing, array(17)); // related fish (category 17)
			$listing->related_fish = RelateAPIHelper::getRelated($listing->id, array(17));
			$listing->fish_count = count($listing->related_fish);
			
			$spots = RelateAPIHelper::getRelated($listing->id, array(3,4));
			$listing->spot_id = $spots[0];
			
			$parent = RelateAPIHelper::getRelated($listing->spot_id, array(1,2,100));
			$listing->parent_spot = $parent[0];
			
			//$listing->related_trips = RelateAPIHelper::getRelated($listing->id, array(14));
			
			$listing->bait         = $this->getRelatedItem($listing, array(101,102)); // bait categories
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
		
		}
		
		$listing->photos = RelateAPIHelper::getPhotos($listing->id);
		$listing->videos = RelateAPIHelper::getVideos($listing->id);
		
		$listing->average_rating = $this->getAverageRating($listing->id);
		$listing->comment_count = $this->getCommentCount($listing->id);
		
		$this->plugin->setResponse($listing);
	}
	
	
	public function post() {
		
		JLoader::register('JReviewsListingHelper', dirname(__FILE__).'/listing.helper.php');
		$post = JRequest::get('post');
	
		$data = json_decode($post['JSON_object']);

		$type = $post['type'];
		
		if ($type == 'comment')
		{
			//$resource = ApiResource::getInstance('Comments', $this->plugin);
			//$resource->post();
			$this->plugin->debug('comment wrong');
			return;
		}
		
		// Catch report
		if ($type == 'report')
		{
			$data->catid = 14;
			$data->sectionid = 6;
		}
		elseif ($type == 'spot')
		{
			$data->sectionid = 1;
			if ($data->jr_watertype == 'saltwater')
			{
				$data->catid = 3;
				$data->jr_watertype = '*saltvannsfiske*';
			} elseif ($data->jr_watertype == 'freshwater') {
				$data->catid = 4;
				$data->jr_watertype = '*ferskvannsfiske*';
			} else {
				$data->catid = 4; // Need to verify correct category ID
				$data->jr_watertype = '*ferskvannsfiske*saltvannsfiske*';
			}
			
		}

		$helper = new JReviewsListingHelper($this->plugin);
		$listing = $helper->save($data);
		
		
		JRequest::setVar('id', $listing->id);
		$this->get();


	}
	
}