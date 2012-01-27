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

class ApiResourceUser extends ApiResource {
	
	public function get() {
		$user = $this->plugin->get('user');
		
		
		if (JRequest::getInt('id', 0))
		{
			$id = JRequest::getInt('id');
		} else
		{
		
			$id = $user->id;
		}
		
		$map = array(
			'birthdate' => 3,
			'about' => 4
		);
		
		$db = JFactory::getDBO();
		$query = "SELECT u.id, u.name, u.username, cu.avatar, cf1.value AS birthdate, cf2.value AS about "
				."FROM #__users AS u "
				."INNER JOIN #__community_users AS cu ON cu.userid = u.id "
				."LEFT JOIN #__community_fields_values AS cf1 ON cf1.user_id = u.id AND cf1.field_id = ".$map['birthdate']." "
				."LEFT JOIN #__community_fields_values AS cf2 ON cf2.user_id = u.id AND cf2.field_id = ".$map['about']." "
				."WHERE u.id = ".$id
				;
		$db->setQuery($query);
		$profile = $db->loadObject();
		$profile->apikey = JRequest::getVar('key');
		
		JLoader::register('CommunityModelFriends', JPATH_SITE.'/components/com_community/models/friends.php');
		$model = new CommunityModelFriends();
		
		$profile->friends = $model->getFriendIds($profile->id);
		
		$this->plugin->setResponse($profile);
	}

}