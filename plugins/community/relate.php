<?php

defined('_JEXEC') or die('Restricted access');
require_once( JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');

class plgCommunityRelate extends CApplications 
{
	var $name = "Relate";
	var $_name = "relate";
	
	function plgCommunityRelate(&$subject, $config) {
		parent::__construct($subject, $config);
	}
	
	function onProfileCreate($user) {
		// Upon registering, users should get 4 default photo albums:
		// Fiskeplasser, Fangstrapporter, Turer, and Klekker (Spots/Catches/Trips/Hatches)
		$config 	= CFactory::getConfig();
		
		CFactory::load( 'models' , 'photos' );
		
		$albumNames = array( 'Fiskeplasser', 'Fangstrapporter', 'Turer', 'Klekker' );
		
		foreach ($albumNames as $album_name) {
			$album =& JTable::getInstance( 'Album' , 'CTable' );

			$album->creator     = $user->id;
			$album->name        = $album_name;
			$album->description = "";
			$album->type        = "user";
			$album->created	    = gmdate('Y-m-d H:i:s');
			$params				= $user->getParams();
			$album->permissions	= $params->get('privacyPhotoView');
			$album->permanent   = 1; // don't let users delete default albums
			
			$storage     = JPATH_ROOT . DS . $config->getString('photofolder');
			$albumPath   = $storage . DS . 'photos' . DS . $user->id . DS ;
			$albumPath   = JString::str_ireplace( JPATH_ROOT . DS , '' , $albumPath );		
			$albumPath   = JString::str_ireplace( '\\' , '/' , $albumPath );
			$album->path = $albumPath;
			
			$album->store();
		}
	}
	
	function onPhotoComment($photoid, $message) {
		// when users comment on a photo that is related to a catch report,
		// add that comment to the catch report as well
		
		$db   =& JFactory::getDBO();
		$user =& JFactory::getUser();

		// get related listings
		$sql = "SELECT rp.listing_id FROM #__relate_photos rp ".
		       "LEFT JOIN #__content c ON rp.listing_id = c.id ".
		       "WHERE rp.photo_id = '".$photoid."' ".
		       "AND rp.listing_id != 0 AND c.state > 0 AND c.catid IN (13,14,15) ";
		$db->setQuery($sql);
		$listings = $db->loadResultArray();
		
		foreach ($listings as $listing_id) {
					
			$jr_comment = array(
				'pid'          => $listing_id,
				'mode'         => 'com_content',
				'created'      => gmdate("Y-m-d H:i:s"),
				'userid'       => $user->id,
				'name'         => $user->name,
				'username'     => $user->username,
				'email'        => $user->email,
				'comments'     => $message,
				'published'    => '1',
				'ipaddress'    => $_SERVER['REMOTE_ADDR'],
				'posts'        => '0',
				'vote_helpful' => '0', 
				'vote_total'   => '0'
			);
			
			$sql = "INSERT INTO #__jreviews_comments (".implode(",", array_keys($jr_comment)).") VALUES ('".implode("','", $jr_comment)."')";
			$db->setQuery($sql);
			$db->query();
			
			$jr_ratings = array(
				'reviewid'    => $db->insertid(),
				'ratings'     => 'na',
				'ratings_sum' => 0,
				'ratings_qty' => 0
			);
			
			$sql = "INSERT INTO #__jreviews_ratings (".implode(",", array_keys($jr_ratings)).") VALUES ('".implode("','", $jr_ratings)."')";
			$db->setQuery($sql);
			$db->query();
			
			require_once(JPATH_SITE.'/components/com_jreviews/jreviews/framework.php');
			require_once(JPATH_SITE.'/components/com_jreviews/jreviews/models/review.php');
		
			$model = new ReviewModel();
			$model->saveListingTotals($listing_id, 'com_content');
		}		
	}
	
	function onPhotoCreate($photos) {
		if (is_array($photos)) $photo = $photos[0];
		else $photo = $photos;

		$relate_id = JRequest::getInt('relate_id');
		if (!$relate_id) return;

		$this->_onJomsCreate($photo, "photo", $relate_id);
	}

	function onVideoCreate($video) {
		$session =& JFactory::getSession();
		
		if ($relate_id = $session->get('relate_id')) {
			$this->_onJomsCreate($video, "video", $relate_id);

			$session->clear('relate_id');
		}
	}

	function _onJomsCreate($item, $type, $relate_id) {
		include_once(JPATH_BASE.DS.'components'.DS.'com_relate'.DS.'models'.DS.'relate.php');
		$model = new RelateModelRelate();
		$result = $model->_add_jomsocial($type, $relate_id, array($item->id));
	}
	
	function onAfterPhotoDelete($photos) {
		if (is_array($photos)) $photo = $photos[0];
		else $photo = $photos;

		$this->_onJomsDelete($photo, "photo");
	}

	function onAfterVideoDelete($video) {
		$this->_onJomsDelete($video, "video");
	}

	function _onJomsDelete($item, $type) {
		$db =& JFactory::getDBO();
		$sql = "DELETE FROM #__relate_${type}s WHERE ${type}_id = $item->id"; 
		$db->setQuery($sql);
		$db->query();
	}
}

