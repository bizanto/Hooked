<?php 
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.controller');

class RelateControllerCreate extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);
	}

	function save()
	{
		$model = $this->getModel('create');

		$listing_type = JRequest::getVar('type');
		$location_id = JRequest::getInt('location_id');

		switch ($listing_type) {
			case 'catch':
				$catches = JRequest::getVar('catches');
				$media = JRequest::getVar('media');
				$response = $model->saveCatches($location_id, $catches, $media);
				echo json_encode($response);
			break;
			case 'spot':
				$title = JRequest::getString('title');
				$description = JRequest::getString('description');
				$fields = JRequest::getVar('fields');
				$related = JRequest::getVar('related_ids');
				$related['location'] = $location_id;
				$fb_share = JRequest::getInt('fb_share', 0);
				$response = $model->createSpot($title, $description, $fields, $related, $fb_share);
				echo json_encode($response);
			break;
			case 'trip':
				$title = JRequest::getString('title');
				$description = JRequest::getVar('description', '', 'post', 'string', JREQUEST_ALLOWRAW);
				$fields = JRequest::getVar('fields');
				$related = JRequest::getVar('related_ids', array());
				$locations = JRequest::getVar('locations', array());
				if (count($locations)) {
					$coords = $this->_getCoords($locations[0]);
					$fields['jr_lat'] = $coords[0];
					$fields['jr_long'] = $coords[1];
					$related = array_merge($related, $locations);
				}
				$media = JRequest::getVar('media');
				$fb_share = JRequest::getInt('fb_share', 0);
				$response = $model->createTrip($title, $description, $fields, $related, $media, $fb_share);
				echo json_encode($response);
			break;
			case 'hatch':
				$description = JRequest::getString('description');
				$fields = JRequest::getVar('fields');
				$related = JRequest::getVar('related');
				$media = JRequest::getVar('media');
				$fb_share = (JRequest::getVar('fb_post') == '*ja*') ? 1 : 0;
				$response = $model->createHatch($location_id, $description, $fields, $related, $media, $fb_share);
				echo json_encode($response);
			break;
		}

		exit();
	}
	
	function _getCoords($location_id) 
	{
		$db =& JFactory::getDBO();
		$q = "SELECT jr_lat, jr_long FROM #__jreviews_content WHERE contentid = $location_id";
		$db->setQuery($q);
		return $db->loadRow();
	}
	
	function coords()
	{
		$location_id = JRequest::getInt('id');
		
		$coords = $this->_getCoords($location_id);
		
		$db =& JFactory::getDBO();
		$q = "SELECT jr.jr_lat, jr.jr_long ".
		     "FROM jos_jreviews_content jr, jos_relate_listings r, jos_content c ".
		     "WHERE jr.contentid = r.id2 AND r.id1 = $location_id ".
		     "AND jr.contentid = c.id AND c.catid IN (3, 4)"; // cat 3, 4 - freshwater/saltwater spots
		$db->setQuery($q);
		$spots = $db->loadRowList();

		$response = array('coords' => $coords, 'spots' => $spots);
		echo json_encode($response);
		exit();
	}
	
	function locations()
	{
		$model = $this->getModel('create');
		
		$listing_type = JRequest::getVar('type', 'catch'); // => {'catch' | 'spot' | 'trip'}
 		$getSpots = ($listing_type != 'spot') ? true : false;
 		
 		$searchword = JRequest::getVar('searchword');
 		$state = JRequest::getVar('state');
 		
 		$start = JRequest::getInt('start', 0);
 		
 		$locations = $model->getLocations($getSpots, $searchword, $state, $start);
 		echo json_encode($locations);
 		exit();
	}
}

