<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');

class RelateViewCreate extends JView
{
	function display($tpl = null)
	{
		// type:- catch | spot | trip | hatch

		$model =& $this->getModel();
		
		$lists = array();

		$states = $model->getField('jr_state');
		
		// add empty default item
		$states = array_merge(array((object)array('text' => JText::_('CHOOSE STATE'), 'value' => '')), $states);
		$lists['states'] = JHTML::_('select.genericlist', $states, 'states');
		
		$spotTags = $model->getField('jr_fspottags');

		$favorites = $model->getFavorites();
		$favorites = array_merge(array((object)array('id' => '', 'title' => '')), $favorites);
		$lists['favorites'] = JHTML::_('select.genericlist', $favorites, 'favoritesList', '', 'id', 'title');

		$species = $model->getSpecies();
		$species = array_merge(array((object)array('id' => '', 'title' => '')), $species);
		$lists['species'] = JHTML::_('select.genericlist', $species, 'related[species]', 'class="validate"', 'id', 'title');

		$equip = $model->getEquipment();
		$equip = array_merge(array((object)array('id' => '', 'title' => '')), $equip);
		$lists['equip'] = JHTML::_('select.genericlist', $equip, 'related[equipment]', '', 'id', 'title');
		
		$bait = $model->getBait();
		$bait = array_merge(array((object)array('id' => '', 'title' => '')), $bait);
		$lists['bait'] = JHTML::_('select.genericlist', $bait, 'related[bait]', '', 'id', 'title');
		
		$insects = $model->getInsects();
		$lists['insects'] = $this->optgroupselect($insects, 'relatedinsects', 'related[insects]', 'validate');
		
		$hatchdegree = $model->getField('jr_hatchdegree');
		$hatchdegree = $this->addstars($hatchdegree);
		$hatchdegree = array_merge(array((object)array('text' => '', 'value' => '')), $hatchdegree);
		$lists['hatchdegree'] = JHTML::_('select.genericlist', $hatchdegree, 'fields[jr_hatchdegree]', '', 'value', 'text');
		
		$hatchweather = $model->getField('jr_weatherinfo');
		$hatchweather = $this->addstars($hatchweather);
		$hatchweather = array_merge(array((object)array('text' => '', 'value' => '')), $hatchweather);
		$lists['hatchweather'] = JHTML::_('select.genericlist', $hatchweather, 'fields[jr_weatherinfo]', '', 'value', 'text');
		
 		$listing_type = JRequest::getVar('type', 'catch'); // => {'catch' | 'spot' | 'trip' | 'hatch'}
 		$getSpots = ($listing_type != 'spot') ? true : false;
		$locations = $model->getLocations($getSpots);
		
		// pick album that is created by the current user
		// select from pre-created albums based on new content type (catches, trips, etc.)
		$album_name = '';
		switch ($listing_type) {
			case 'catch': 
				$album_name = 'Fangstrapporter'; 
			break;		
			case 'spot': 
				$album_name = 'Fiskeplasser'; 
			break;
			case 'trip':
				$album_name = 'Turer';
			break;
			case 'hatch':
				$album_name = 'Klekker';
			break;
		}
		$user =& JFactory::getUser();
		$db =& JFactory::getDBO();
		$q = "SELECT id FROM #__community_photos_albums ".
		     "WHERE name = '".$album_name."' AND creator = '".$user->id."' ";
		$db->setQuery($q);
		$albumId = $db->loadResult(); 
		
		$privacySettings = $model->getField('jr_privacy');
		
		// get google maps api key from jreviews config
		$q = "SELECT value FROM #__jreviews_config WHERE id = 'geomaps.google_key'";
		$db->setQuery($q);
		$gapi_key = $db->loadResult();
		
		if ($location_id = JRequest::getVar('id')) {
			$q = "SELECT id, title, catid FROM #__content WHERE id='$location_id'";
			$db->setQuery($q);
			$location = $db->loadObject();
			$this->assignRef('location', $location); 
		}
		
		// check if user is logged in with a facebook connect account
		$isFbConnect = check_facebook(); 
		$this->assignRef('isFbConnect', $isFbConnect);
		
		$this->assignRef('listing_type', $listing_type);
		$this->assignRef('lists', $lists);
		$this->assignRef('spotTags', $spotTags);
		$this->assignRef('locations', $locations);
		$this->assignRef('albumId', $albumId);
		$this->assignRef('privacySettings', $privacySettings);
		$this->assignRef('gapi_key', $gapi_key);

		parent::display($tpl);
	}
	
	// for jreviews fields we are pulling from the db and putting into select lists
	function addstars($jrfields) {
		$result = array();
		foreach ($jrfields as $field) {
			$field->value = '*'.$field->value.'*';
			$result[] = $field;
		}
		return $result;
	}
	
	function optgroupselect($items, $id, $name, $class) {
		$cats = array();

		foreach ($items as $item) {
			if (isset($cats[$item->category])) {
				$cats[$item->category][] = $item;
			}
			else {
				$cats[$item->category] = array($item);
			}
		}

		$html = '<select id="'.$id.'" name="'.$name.'" class="'.$class.'">';
		$html .= '<option selected="selected" value=""></option>';
		
		foreach ($cats as $title => $cat) {
			$html .= '<optgroup label="'.$title.'">';
			foreach ($cat as $citem) {
				$html .= '<option value="'.$citem->id.'">'.$citem->title.'</option>';
			}
			$html .= '</optgroup>';
		}
		
		$html .= '</select>';
		
		return $html;
	}
}

// Core file is required since we need to use CFactory
require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php' );

// Need to include Facebook's PHP API library so we can utilize them.
//require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'facebook' . DS . 'facebook.php' );
//require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'facebook' . DS . 'facebookrest.php' );

require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'facebook_new' . DS . 'facebook.php' );

function check_facebook()
{
	$config			= CFactory::getConfig();
	$key			= $config->get('fbconnectkey');
	$secret			= $config->get('fbconnectsecret');
	
	$facebook	= new Facebook(array('appId' => $key, 'secret' => $secret, 'cookie' => true));
	
	$user	= $facebook->getUser();

	if( !$user )
	{
		return false;
	}
	
	return true;
}
