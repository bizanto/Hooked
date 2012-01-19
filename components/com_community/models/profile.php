<?php
/**
 * @category	Model
 * @package		JomSocial
 * @subpackage	Profile
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'models' . DS . 'models.php' );

class CommunityModelProfile extends JCCModel
{
	var $_data = null;
	var $_profile;

	var $_user	= '';
	var $_allField = null;
	
	function _getUngroup()
	{
		$obj = new stdClass();
		$obj->id = 0;
		$obj->type =  'group';
		$obj->ordering =  2;
		$obj->published =  1;
		$obj->min =  0;
		$obj->max =  0;
		$obj->name =  'ungrouped';
		$obj->tips =  '';
		$obj->visible =  1;
		$obj->required =  1;
		$obj->searchable =  1 ;
		$obj->fields = array();
		
		return $obj;
	}
	
	function aliasExists( $alias , $userId = '' )
	{
		// For backward compatibility, prior to 2.0, this method only has 1 parameter.
		if( empty($userId ) )
		{
			$my		= CFactory::getUser();
			$userId	= $my->id;
		}
		
		$db		=& JFactory::getDBO();
		$config	= CFactory::getConfig();
		
		$query	= 'SELECT COUNT(*) FROM '
				. $db->nameQuote( '#__users' ) . ' AS a '
				. 'INNER JOIN ' . $db->nameQuote( '#__community_users' ) . ' AS b '
				. 'ON b.userid=a.id '
				. 'WHERE ( CONCAT_WS("-", a.id , LOWER( REPLACE(a.' . $db->nameQuote( $config->get('displayname') ) . ', " " , "-") ) )=LOWER(' . $db->Quote( $alias ) . ') '
				. 'OR b.`alias`=' . $db->Quote( $alias ) . ') '
				. 'AND b.`userid`!=' . $db->Quote( $userId );
		$db->setQuery( $query );
		$exists	= $db->loadResult() > 0 ? true : false;
		
		return $exists;
	}
	
	function getGroup( $fieldId )
	{
		$db		=& $this->getDBO();
		$field	=& JTable::getInstance( 'ProfileField' , 'CTable' );
		$field->load( $fieldId );
		
		$query	= 'SELECT * '
				. 'FROM ' . $db->nameQuote( '#__community_fields' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'ordering' ) . '<' . $field->ordering . ' '
				. 'AND ' . $db->nameQuote( 'type' ) . '=' . $db->Quote( 'group' ) . ' '
				. 'ORDER BY ordering DESC';
		$db->setQuery( $query );
		
		$result	= $db->loadObject();
		
		return $db->loadObject();
	}
	
	function _loadAllFields($filter = array() , $type = COMMUNITY_DEFAULT_PROFILE )
	{
		if($this->_allField == null)
		{
			$this->_allField = array();
			$db		=& JFactory::getDBO();
			
			//setting up the search condition is there is any
			$wheres = array();
			if(! empty($filter))
			{
				foreach($filter as $column => $value)
				{
					$wheres[] = "`$column` = " . $db->Quote($value); 	
				}
			}
			
			if( $type != COMMUNITY_DEFAULT_PROFILE )
			{
				$query	= 'SELECT field_id FROM ' . $db->nameQuote( '#__community_profiles_fields' ) . ' '
						. 'WHERE ' . $db->nameQuote( 'parent' ) . '=' . $db->Quote( $type );
				$db->setQuery( $query );
				$filterIds	= $db->loadResultArray();
				
				$wheres[]	= "`id` IN (" . implode( ',' , $filterIds ) . ')';
			}

			$query	= 'SELECT * FROM ' . $db->nameQuote( '#__community_fields' );	

			if(! empty($wheres))
			{
			   $query .= " WHERE ".implode(' AND ', $wheres);
			}
			$query .= " ORDER BY `ordering`";

			$db->setQuery($query);

			
			$fields = $db->loadObjectList();
			$group	= 'ungrouped';
			
			for($i = 0; $i < count($fields); $i++)
			{
				if($fields[$i]->type == 'group')
				{
					$group	= $fields[$i]->name;
					$this->_allField[$group] = $fields[$i];
					$this->_allField[$group]->fields = array();
				}
				else
				{
					// Re-arrange options to be an array by splitting them into an array
					if(isset($fields[$i]->options) && $fields[$i]->options != '')
					{
						$options	= $fields[$i]->options;
						$options	= explode("\n", $options);

						array_walk($options, array('JString' , 'trim') );
						$fields[$i]->options	= $options;
					}
					
					if($group == 'ungrouped' && empty($this->_allField[$group]))
					{
						$this->_allField[$group] = $this->_getUngroup();
					} 

					$this->_allField[$group]->fields[] =	$fields[$i];
				}
			}
		}
	}
	
	/**
	 * Return the complete (but empty) profile structure
	 */	 	
	function &getAllFields($filter = array() , $profileType = COMMUNITY_DEFAULT_PROFILE )
	{
		$this->_loadAllFields($filter , $profileType );
		return $this->_allField;
	}	
	
	function _bind($data){
	}
	
	/**
	 * Returns an object of user's data
	 * 	 	
	 * @access	public
	 * @param	none
	 * @returns object  An object that is related to user's data	 
	 */	 
	function &getData()
	{
		$db	= &$this->getDBO();
		
		$wheres	  = array();
		$wheres[] = 'block = 0';
		$wheres[] = 'id = '. $this->getState('id');
		
		$query = "SELECT *"
			. ' FROM #__users'
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' ORDER BY `id` DESC ';

		$db->setQuery( $query );
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		$result = $db->loadObject();

		return $result;
	}

	function getProfileName( $fieldCode )
	{
		$db		=& $this->getDBO();
		
		$query	= 'SELECT ' . $db->nameQuote('name') . ' FROM '
				. $db->nameQuote( '#__community_fields') . ' WHERE '
				. $db->nameQuote( 'fieldcode') . '=' . $db->Quote( $fieldCode );
		
		$db->setQuery( $query );
		$name	= $db->loadResult();
		
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $name;
	}
	
	/**
	 * Wrapper method
	 */	 	
	function getProfile( $userId = null )
	{
		return $this->getViewableProfile( $userId );
	}
	
	/**
	 * Returns an array of custom profiles which are created from the back end.
	 * 	 	
	 * @access	public
	 * @param	string 	User's id.
	 * @returns array  An objects of custom fields.	 
	 */	 		
	function getViewableProfile( $userId	= null , $profileType = COMMUNITY_DEFAULT_PROFILE )
	{
		$db			=& $this->getDBO();
		$data		= array();
		
		// Return with empty data
		if($userId == null || $userId == '')
		{
			//return false;
		}

		$user		=& JFactory::getUser($userId);
		
		if($user->id == null){
			//return false;
		}
		
		$data['id']		= $user->id;
		$data['name']	= $user->name;
		$data['email']	= $user->email;

		// Attach custom fields into the user object
		$query	= 'SELECT field.*, value.value '
				. 'FROM ' . $db->nameQuote('#__community_fields') . ' AS field '
				. 'LEFT JOIN ' . $db->nameQuote('#__community_fields_values') . ' AS value '
 				. 'ON field.id=value.field_id AND value.user_id=' . $db->Quote($userId) . ' '
				. 'WHERE field.published=' . $db->Quote('1') . ' AND '
 				. 'field.visible=' . $db->Quote('1');
 		
 		// Build proper query for multiple profile types.
		if( $profileType != COMMUNITY_DEFAULT_PROFILE )
		{
			$query2	= 'SELECT field_id FROM ' . $db->nameQuote( '#__community_profiles_fields' ) . ' '
					. 'WHERE ' . $db->nameQuote( 'parent' ) . '=' . $db->Quote( $profileType );
			$db->setQuery( $query2 );
			$filterIds	= $db->loadResultArray();

			if( empty( $filterIds ) )
			{
				$data['fields']	= array();
				return $data;
			}
			
			$query	.= " AND field.`id` IN (" . implode( ',' , $filterIds ) . ')';
		}
		
		$query	.= ' ORDER BY field.ordering';
		
		$db->setQuery( $query );

		$result	= $db->loadAssocList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		$data['fields']	= array();
		for($i = 0; $i < count($result); $i++){

			// We know that the groups will definitely be correct in ordering.			
			if($result[$i]['type'] == 'group')
			{
				$group	= $result[$i]['name'];

				// Group them up			
				if(!isset($data['fields'][$group])){
					// Initialize the groups.
					$data['fields'][$group]	= array();
				}
			}
			
			// Re-arrange options to be an array by splitting them into an array
			if(isset($result[$i]['options']) && $result[$i]['options'] != '')
			{
				$options	= $result[$i]['options'];
				$options	= explode("\n", $options);

				array_walk($options, array( 'JString' , 'trim' ) );
				
				$result[$i]['options']	= $options;
				
			}

			// Only append non group type into the returning data as we don't
			// allow users to edit or change the group stuffs.
			if($result[$i]['type'] != 'group'){
				if(!isset($group))
					$data['fields']['ungrouped'][]	= $result[$i];
				else
					$data['fields'][$group][]	= $result[$i];
			}
		}
		//$this->_dump($data);
		return $data;
	}

	/**
	 * Returns an array of custom profiles which are created from the back end.
	 * 	 	
	 * @access	public
	 * @param	string 	User's id.
	 * @returns array  An objects of custom fields.	 
	 */	 		
	function getEditableProfile($userId	= null , $profileType = COMMUNITY_DEFAULT_PROFILE )
	{
		$db			=& $this->getDBO();
		$data		= array();
		
		$user		=& JFactory::getUser($userId);
		
		$data['id']		= $user->id;
		$data['name']	= $user->name;
		$data['email']	= $user->email;

		// Attach custom fields into the user object		
		$query	= 'SELECT field.*, value.value '
				. 'FROM ' . $db->nameQuote('#__community_fields') . ' AS field '
				. 'LEFT JOIN ' . $db->nameQuote('#__community_fields_values') . ' AS value '
 				. 'ON field.id=value.field_id AND value.user_id=' . $db->Quote($userId);
 
 		// Build proper query for multiple profile types.
		if( $profileType != COMMUNITY_DEFAULT_PROFILE )
		{
			$query2	= 'SELECT field_id FROM ' . $db->nameQuote( '#__community_profiles_fields' ) . ' '
					. 'WHERE ' . $db->nameQuote( 'parent' ) . '=' . $db->Quote( $profileType );
			$db->setQuery( $query2 );
			$filterIds	= $db->loadResultArray();

			if( empty( $filterIds ) )
			{
				$data['fields']	= array();
				return $data;
			}
						
			$query	.= " WHERE field.`id` IN (" . implode( ',' , $filterIds ) . ')';
			$query	.= ' AND field.published=' . $db->Quote( '1' );
		}
		else
		{
			$query	.= ' WHERE field.published=' . $db->Quote('1');
		}

		$query	.= ' ORDER BY field.ordering';

		$db->setQuery( $query );

		$result	= $db->loadAssocList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		$data['fields']	= array();
		for($i = 0; $i < count($result); $i++)
		{

			// We know that the groups will definitely be correct in ordering.			
			if($result[$i]['type'] == 'group')
			{
				$group	= $result[$i]['name'];

				// Group them up			
				if(!isset($data['fields'][$group]))
				{
					// Initialize the groups.
					$data['fields'][$group]	= array();
				}
			}
			
			// Re-arrange options to be an array by splitting them into an array
			if(isset($result[$i]['options']) && $result[$i]['options'] != '')
			{
				$options	= $result[$i]['options'];
				$options	= explode("\n", $options);

				array_walk($options, array( 'JString' , 'trim' ) );
				
				$result[$i]['options']	= $options;
				
			}

			// Only append non group type into the returning data as we don't
			// allow users to edit or change the group stuffs.
			if($result[$i]['type'] != 'group'){
				if(!isset($group))
					$data['fields']['ungrouped'][]	= $result[$i];
				else
					$data['fields'][$group][]	= $result[$i];
			}
		}
		//$this->_dump($data);
		return $data;
	}
	
	/**
	 * Returns an array of custom profiles which are created from the back end.
	 * 	 	
	 * @access	public
	 * @param	string 	User's id.
	 */	 
	function _dump(& $data){
		echo '<pre>';
		print_r($data);
		echo '</pre>';
		exit;
	}
		
	function saveProfile($userId, $fields)
	{
		jimport('joomla.utilities.date');
		$db		=& $this->getDBO();

		foreach($fields as $id => $value)
		{
			// Check if field value exists before inserting or updating
			$strSQL	= "SELECT COUNT(*) FROM #__community_fields_values"
					. " WHERE field_id='$id' AND user_id=" . $db->Quote($userId);
			$db->setQuery( $strSQL );

			$isNew	= ($db->loadResult() <= 0) ? true : false;

			$strSQL	= "INSERT INTO " . $db->nameQuote('#__community_fields_values')
					. ' SET ' . $db->nameQuote('user_id') . '=' . $db->Quote($userId) . ', '
					. $db->nameQuote('field_id') . '=' . $db->Quote($id) . ', ' . $db->nameQuote('value')
					. '=' . $db->Quote($value);

			if(!$isNew){
				$strSQL	= 'UPDATE ' . $db->nameQuote('#__community_fields_values') . ' SET '
						. $db->nameQuote('value') . '=' . $db->Quote($value)
						. ' WHERE ' . $db->nameQuote('user_id') . '=' . $db->Quote($userId)
						. ' AND ' . $db->nameQuote('field_id') . '=' . $db->Quote($id);
			}
			//echo $strSQL;
			$db->setQuery( $strSQL );
			$db->query();

		}
		
	}
	
	function setProfile($v)
	{
		$this->_profile = $v;
	}
	
	/**
	 * Method to test if a specific field for a user exists
	 * 
	 * @param	String	$fieldCode	Field Code
	 * @param	String	$userId		Userid
	 * 
	 *	return boolean	True if exists and false otherwise.
	 **/
	function _fieldValueExists( $fieldCode , $userId )
	{
		$db		=& JFactory::getDBO();
		
		$query	= 'SELECT COUNT(1) FROM ' . $db->nameQuote( '#__community_fields' ) . ' AS a '
				. 'INNER JOIN ' . $db->nameQuote( '#__community_fields_values' ) . ' AS b '
				. 'ON a.id=b.field_id '
				. 'WHERE a.fieldcode=' . $db->Quote( $fieldCode ) . ' '
				. 'AND b.user_id=' . $db->Quote( $userId );

		$db->setQuery( $query );

		$result	= ( $db->loadResult() >= 1 ) ? true : false;
		
		return $result;
	}
	
	/**
	 * Method to retrieve a field's id with a given field code
	 *
	 * @param	String	$fieldCode	Field code for the specific field.
	 **/	 
	function getFieldId( $fieldCode )
	{
		$db		=& JFactory::getDBO();
		$query	= 'SELECT ' . $db->nameQuote( 'id' ) . ' FROM '
				. $db->nameQuote( '#__community_fields' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'fieldcode' ) . '=' . $db->Quote( $fieldCode );
		
		$db->setQuery( $query );
		
		$result	= $db->loadResult();
		
		return $result; 
	}
	
	/**
	 * Method to retrieve a field's id with a given field code
	 *
	 * @param	String	$fieldCode	Field code for the specific field.
	 **/	 
	function getFieldCode( $fieldId )
	{
		$db		=& JFactory::getDBO();
		$query	= 'SELECT ' . $db->nameQuote( 'fieldcode' ) . ' FROM '
				. $db->nameQuote( '#__community_fields' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'id' ) . '=' . $db->Quote( $fieldId );
		
		$db->setQuery( $query );
		
		$result	= $db->loadResult();
		
		return $result; 
	}
	
	function updateUserData( $fieldCode , $userId , $value )
	{
		$db		=& JFactory::getDBO();
		
		$data	= new stdClass();
		
		$fieldId	= $this->getFieldId( $fieldCode );

		if( $this->_fieldValueExists( $fieldCode , $userId ) )
		{
			// For existing record we just update it.
			$query	= 'UPDATE ' . $db->nameQuote( '#__community_fields_values' ) . ' '
					. 'SET ' . $db->nameQuote( 'value' ) . '=' . $db->Quote( $value ) . ' '
					. 'WHERE ' . $db->nameQuote( 'field_id' ) . '=' . $db->Quote( $fieldId ) . ' '
					. 'AND ' . $db->nameQuote( 'user_id' ) . '=' . $db->Quote( $userId );

			$db->setQuery( $query );
			$db->query();
			return;
		}
		else
		{
			// For new records, we need to add it.
			$data			= new stdClass();
			$data->field_id	= $fieldId;
			$data->user_id	= $userId;
			$data->value	= $value;
			
			return $db->insertObject( '#__community_fields_values' ,  $data );
		}
	}
	
	function formatDate($value, $format='')
	{
		$db		=& $this->getDBO();
		$config	= CFactory::getConfig();
		$format	= $config->get( 'profileDateFormat' );
		
		$query	= 'SELECT DATE_FORMAT('.$db->Quote($value).', '.$db->Quote($format).') AS FORMATED_DATE';
		$db->setQuery($query);
		$result	= $db->loadResult();
		
		return $result; 
	}
	
	function getAdminEmails()
	{
		$emails		= '';
		$db			=& $this->getDBO();

		$query		= 'SELECT ' . $db->nameQuote('email')
					. ' FROM ' . $db->nameQuote('#__users')
					. ' WHERE ' . $db->nameQuote('gid') . '=' . $db->quote(24) 
					. ' OR ' . $db->nameQuote( 'gid' ) . '=' . $db->Quote( 25 );
					
		$db->setQuery($query);
		$emails		= $db->loadResultArray();
		
		return $emails; 	
	}
	
	/**
	 * Retrieves profile types available throughout the site.
	 * 
	 * @returns	Array	An array of objects.
	 **/	 	 	 	
	public function getProfileTypes()
	{
		$db		=& $this->getDBO();
		$query	= 'SELECT * FROM ' . $db->nameQuote( '#__community_profiles' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'published' ) . '=' . $db->Quote( 1 );
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
	
	public function reset()
	{
		$this->_allField	= null;
	}
}
