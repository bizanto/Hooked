<?php

defined('_JEXEC') or die( 'Restricted access' );

class JReviewsListingHelper extends JObject {

	protected $plugin = null;

	public function __construct(APIPlugin $plugin)
	{
		$this->plugin = $plugin;
	}

	public function save($data)
	{
		$db =& JFactory::getDBO();

		$listing = JTable::getInstance('JRContent', 'JTable');
		$listing->bind($data);
		
		if ($listing->catid == 14) {
			// need to set the title of the catch report since the app doesn't do it...
			
			$species_id = $data->related_fish;
			$sql = "SELECT title FROM #__content WHERE id = '$species_id'";
			$db->setQuery($sql);
			$fishname = $db->loadResult();
			
			$catchweight = $data->jr_catchweight;
			
			if ($data->jr_privacy == '*privat*') {
				$listing->title = "$fishname ($catchweight gr)";
				$data->jr_catchanonymous = '*ja*';
			}
			else {
				$location_id = $data->spot_id;		
				$sql = "SELECT title FROM #__content WHERE id = '$location_id'";
				$db->setQuery($sql);
				$spotname = $db->loadResult();
			
				if ($spotname) {
					$listing->title = "$fishname ($catchweight gr) @ $spotname";
					$data->jr_catchanonymous = '*nei*';
				}
				else {
					$listing->title = "$fishname ($catchweight gr)";
				}
			}
		}
		
		$listing->created_by = $this->plugin->get('user')->id;
		$listing->created = gmdate("Y-m-d H:i:s");
		$listing->publish_up = $listing->created;
		$listing->state = 1;
		$listing->alias = JFilterOutput::stringURLSafe($listing->title);
		
		$listing->store();
		
		$jrcontent = JTable::getInstance('JRReview', 'JTable');
		$jrcontent->bind($data);
		$jrcontent->contentid = $listing->id;
		$jrcontent->jr_startdate = gmdate("Y-m-d H:i:s");
		//$jrcontent->jr_privacy = $data->share_location == 'yes' ? '*'.$data->share_scope.'*' : '*privat*';
		
		$jrcontent->store();
		
		// don't add standard activity stream messages for relations when creating catches or spots
		if ($data->catid == 3 || $data->catid == 4 || $data->catid == 14) {
			$add_stream = false;
		}
		else {
			$add_stream = true;
		}
		
		if ($data->spot_id)
		{
			$this->saveRelation($listing->id, $data->spot_id, $listing->created_by, $add_stream);
		}
		
		if ($data->parent_spot)
		{
			$this->saveRelation($listing->id, $data->parent_spot, $listing->created_by, $add_stream);
		}
		
		if ($data->related_fish)
		{
			$this->saveRelation($listing->id, $data->related_fish, $listing->created_by, $add_stream);
		}
		
		if ($data->bait)
		{
			$this->saveRelation($listing->id, $data->bait, $listing->created_by, $add_stream);
		}
		
		if ($data->related_techniques)
		{
			$this->saveRelation($listing->id, $data->related_techniques, $listing->created_by, $add_stream);
		}

		// if we're adding a catch or spot, add custom activity stream message
		include_once(JPATH_BASE.DS.'components'.DS.'com_relate'.DS.'models'.DS.'create.php');
		$model = new RelateModelCreate();

		if ($data->catid == 14) {
			// strip off location part of catch title so we can add it back in as an anchor link in the message
			$listing->short_title = preg_replace('/\s?@.*$/', '', $listing->title);
			$listing->anonymous   = $jrcontent->jr_privacy == '*privat*';
			
			$location_id = $data->spot_id;
			
			$sql = "SELECT id, title, catid, sectionid FROM #__content WHERE id = '$location_id'";
			$db->setQuery($sql);
			$spot = $db->loadObject();
			
			$parentLoc = $model->getParentLocation($spot);
			
			$model->_streamAddCatch($listing, $spot, $parentLoc);
		}
		else if (($data->catid == 3 || $data->catid == 4) && $jrcontent->jr_privacy != '*privat*') {
			$parentLoc = $model->getParentLocation($listing);

			$model->_streamAddSpot($listing, $parentLoc);
		}
		
		$listing->related = $jrcontent;
		return $listing;
	}
	
	public function saveRelation($id1, $id2, $creator, $add_stream)
	{
		include_once(JPATH_BASE.DS.'components'.DS.'com_relate'.DS.'models'.DS.'relate.php');
		$model = new RelateModelRelate();
		
		// add_listings takes an array of ids to relate to id1
		if (!is_array($id2)) {
			$id2 = array($id2);
		}
		
		$model->add_listings($id1, $id2, $add_stream, $creator);
	}

}

class JTableJRContent extends JTable
{
	var $id			= null;
	var $title		= null;
	var $catid		= null;
	var $sectionid 	= null;
	var $introtext	= null;
	var $created_by = null;
	

	function __construct(&$db)
	{	
		parent::__construct('#__content', 'id', $db);
	}

	
}

class JTableJRReview extends JTable
{

	var $contentid = null;

	function __construct(&$db)
	{
		$fields = $db->getTableFields('#__jreviews_content');
		
		foreach($fields['#__jreviews_content'] as $name => $type)
		{
			if (strpos($name, 'jr_') === 0)
				$this->$name = null;
		}
		
		parent::__construct('#__jreviews_content', 'contentid', $db);
	}
	
	function store()
	{
		$k = $this->_tbl_key;

		if( $this->$k)
		{
			$this->_db->setQuery("SELECT COUNT(*) FROM $this->_tbl WHERE $k = ".$this->$k);
			$exists = $this->_db->loadResult();
			if ($exists)
			{
				$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
			}
			else
			{
				$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
			}
		}
		else
		{
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		}
		if( !$ret )
		{
			$this->setError(get_class( $this ).'::store failed - '.$this->_db->getErrorMsg());
			return false;
		}
		else
		{
			return true;
		}
	}
	
}