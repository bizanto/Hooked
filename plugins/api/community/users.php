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

class ApiResourceUsers extends ApiResource {
	
	public function get() {
		$user = $this->plugin->get('user');
		
		$id = JRequest::getVar('id');
		$where = null;
		$wheres = array();
		if ($id)
		{
			$wheres[] = $this->splitForQuery($id, 'u.id');
		}
		
		if (!empty($wheres))
		{
			$where = "WHERE ".implode(" AND ", $wheres);
		}
		
		$map = array(
			'birthdate' => 3,
			'about' => 4
		);
		
		$db = JFactory::getDBO();
		$query = $query = "SELECT u.id, u.name, u.username, cu.avatar, cf1.value AS birthdate, cf2.value AS about "
				."FROM #__users AS u "
				."INNER JOIN #__community_users AS cu ON cu.userid = u.id "
				."LEFT JOIN #__community_fields_values AS cf1 ON cf1.user_id = u.id AND cf1.field_id = ".$map['birthdate']." "
				."LEFT JOIN #__community_fields_values AS cf2 ON cf2.user_id = u.id AND cf2.field_id = ".$map['about']." "
				.$where
				;
		$db->setQuery($query);
		$users = $db->loadObjectList();
				
		
		$this->plugin->setResponse($users);
	}

	function splitForQuery($string, $field)
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

}