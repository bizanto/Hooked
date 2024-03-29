<?php
/**
 * @category	Model
 * @package		JomSocial
 * @subpackage	Search
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.utilities.date');
jimport('joomla.html.pagination');

require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'models' . DS . 'models.php' );

class CommunityModelSearch extends JCCModel
{
	var $_data = null;
	var $_profile;
	var $_pagination;
	var $_total;
 
	
	function CommunityModelSearch(){
		parent::JCCModel();
 	 	$mainframe =& JFactory::getApplication();
 	 	
 	 	// Get pagination request variables
 	 	$limit		= ($mainframe->getCfg('list_limit') == 0) ? 5 : $mainframe->getCfg('list_limit');
	    $limitstart = JRequest::getVar('limitstart', 0, 'REQUEST');
 	 	
 	 	// In case limit has been changed, adjust it
	    $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		 	 	
		$this->setState('limit',$limit);
 	 	$this->setState('limitstart',$limitstart);
	}	
	
	function &getFiltered($wheres = array())
	{
		$db			= &$this->getDBO();
		
		$wheres[] = 'block = 0';
		
		$query = "SELECT *"
			. ' FROM #__users'
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' ORDER BY `id` DESC ';
	
		$db->setQuery( $query );
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		
		$result = $db->loadObjectList();
		return $result;
	}
	
	
	/**
	 * get pagination data
	 */	 	
	function getPagination()
	{
		return $this->_pagination;
	}
	
	/**
	 * get total data
	 */	 	
	function getTotal()
	{
		return $this->_total;
	}

	/**
	 * Search for people
	 * @param query	string	people's name to seach for	
	 */	 
	function searchPeople($query , $avatarOnly = '' )
	{
		$db	= &$this->getDBO();
		$filter = array();
		$strict = true;
		$regex = $strict? 
		      '/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' : 
		       '/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' ;
			
		$data = array();
		//select only non empty field
		foreach($query as $key => $value)
		{
			if(!empty($query[$key]))
			{
				$data[$key]=$value;
			}
		}
		
		// build where condition
		$filterField	= array();						
		if(isset($data['q']))
		{ 		
			$value			= $data['q'];
			if (preg_match($regex, JString::trim($value), $matches))
			{ 
				$query = array($matches[1], $matches[2]);
				$cond = $matches[1]."@".$matches[2];
				$filter[] = "`email`=" . $db->Quote($cond);
		    }
			else
			{
				$config		= CFactory::getConfig();
				$nameType	= $db->nameQuote( $config->get( 'displayname' ) );
				
				$filter[]	= 'UCASE(' . $nameType . ') LIKE UCASE(' . $db->Quote( '%' . $value . '%' ) . ')';
		    }
	    }
		
		$limit			= $this->getState('limit');
		$limitstart		= $this->getState('limitstart');	
	
		$finalResult	= array();
		$total			= 0;
		if(count($filter)> 0 || count($filterField > 0))
		{
			// Perform the simple search
			$basicResult = null;
			if(!empty($filter) && count($filter)>0)
			{
				$query = "SELECT distinct b.`id` FROM #__users b";
		
				if( $avatarOnly )
				{
					$query	.= ' INNER JOIN #__community_users AS c ON b.`id`=c.`userid`';
					$query	.= ' AND c.`thumb` != ' . $db->Quote( '' );
					$query  .= ' AND c.`thumb` != \'\'';
				}
				$query .= " WHERE b.block = 0 AND ".implode(' AND ',$filter);

				$queryCnt	= 'SELECT COUNT(1) FROM ('.$query.') AS z';
				$db->setQuery($queryCnt);		
				$total	= $db->loadResult();
				
				$query .=  " LIMIT " . $limitstart . "," . $limit;
												
				$db->setQuery( $query );
				$finalResult = $db->loadResultArray();
				if($db->getErrorNum()) {
					JError::raiseError( 500, $db->stderr());
				}
			}
			
			// Appy pagination
			if (empty($this->_pagination))
			{		 	    
		 	    $this->_pagination = new JPagination($total, $limitstart, $limit);
		 	}
		} 				

		if(empty($finalResult))
			$finalResult = array(0);
			
		$id = implode(",",$finalResult);
		$where = array("`id` IN (".$id.")");
	    $result = $this->getFiltered($where);
				
		return $result;
	}
	
	// @params $field, array with key[fieldcode] = value
	// just use 1 field for now
	function searchByFieldCode($field){
			
		CError::assert($field , '', '!empty', __FILE__ , __LINE__ );
		
		$db			=& $this->getDBO();

		$keys = array_keys($field);
		$vals = array_values($field);		
		
		$fieldId = $this->_getFieldIdFromFieldCode($keys[0]);
	
		$sql = "SELECT `user_id` FROM #__community_fields_values AS a"
		    ." INNER JOIN #__users AS b"
		    ." ON a.`user_id` = b.`id`"
			." WHERE a.`value`=". $db->Quote($vals[0]) 
			." AND a.`field_id`=". $db->Quote($fieldId);
			
		$limit		= $this->getState('limit');
		$limitstart	= $this->getState('limitstart');
		$total		= 0;

		//getting result count.
		$queryCnt	= 'SELECT COUNT(1) FROM ('.$sql.') AS z';
		$db->setQuery($queryCnt);
		$total		= $db->loadResult();
				
		$sql .=  " LIMIT " . $limitstart . "," . $limit;			
		
		$db->setQuery($sql);
		$result = $db->loadObjectList();
		
		if (empty($this->_pagination)) {
			$this->_pagination = new JPagination($total, $limitstart, $limit);
		}		
		
		// need to return user object
		// Pre-load multiple users at once
		$userids = array();
		foreach($result as $uid)
		{
			$userids[] = $uid->user_id;
		}

		CFactory::loadUsers($userids);

		$users = array();
		foreach($result as $row){
			$users [] = CFactory::getUser($row->user_id);
		}
		
		return $users;
	}
	
	
	function _getFieldIdFromFieldCode($code)
	{
		CError::assert($code , '', '!empty', __FILE__ , __LINE__ );
		
		$db	=& $this->getDBO();
		$query	= 'SELECT' . $db->nameQuote( 'id' ) . ' '
				. 'FROM ' . $db->nameQuote( '#__community_fields' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'fieldcode' ) . '=' . $db->Quote( $code );
		$db->setQuery( $query );
		$id		= $db->loadResult();
		
		CError::assert($id , '', '!empty', __FILE__ , __LINE__ );
		return $id;
	}
	
	/**
	 * Method to get users list on this site
	 * 	 
	 **/	 	
	function getPeople( $sorted = 'latest', $filter = 'all' )
	{
		$db			= &$this->getDBO();
		$limit		= $this->getState('limit');
		$limitstart = $this->getState('limitstart');
		$config		= CFactory::getConfig();
		
		$query		= 'SELECT distinct(a.id) FROM #__users AS a '
					. 'LEFT JOIN #__session AS b '
					. 'ON a.id=b.userid ' 
					. 'WHERE a.block=' . $db->Quote( 0 );
		$db->setQuery($query);
		$total		= $db->loadResult();
		
		$filterQuery	= '';
		
		switch( $filter )
		{
			case 'others':
				$filterQuery	.= ' AND a.name REGEXP "^[^a-zA-Z]."';
			break;
			case 'all':
			break;
			default:
				$filterCount	= JString::strlen( $filter );
				$allowedFilters	= array('abc','def','ghi','jkl','mno','pqr','stu','vwx' , 'yz' );

				if( in_array( $filter , $allowedFilters ) )
				{
					$filterQuery	.= ' AND(';
					for( $i = 0; $i < $filterCount; $i++ )
					{
						$char			= $filter{$i};
						$filterQuery	.= $i != 0 ? ' OR ' : ' ';
						$field			= $config->get( 'displayname' );
						$filterQuery	.= 'a.`' . $field . '` LIKE "' . JString::strtoupper($char) . '%" OR a.`' . $field . '` LIKE "' . JString::strtolower($char) . '%"'; 
					}
					$filterQuery	.= ')';
				}
			break;
		}

		$query	.= $filterQuery;

		switch( $sorted )
		{
			case 'online':
				$query	.= 'ORDER BY b.userid DESC';
				break;
			case 'alphabetical':
				$config	= CFactory::getConfig();

				$query	.= ' ORDER BY a.`' . $config->get('displayname') . '` ASC';
				break;
			default:
				$query	.= ' ORDER BY a.registerDate DESC';
				break;
		}

		if( !$this->_pagination )
		{
			$pagingQuery	= JString::str_ireplace( 'distinct(a.id)' , 'COUNT(DISTINCT(a.id))' , $query);
			$db->setQuery($pagingQuery);
			$total		= $db->loadResult();		
			$this->_pagination = new JPagination($total, $limitstart, $limit);
		}

		$query	.= ' LIMIT ' . $limitstart . ',' . $limit;
		$db->setQuery($query);
		$result = $db->loadObjectList();
		
		$cusers = array();

		// Pre-load multiple users at once
		$userids = array();
		foreach($result as $uid){ $userids[] = $uid->id; }
		CFactory::loadUsers($userids);

		for($i = 0; $i < count($result); $i++)
		{
			$usr = CFactory::getUser(	$result[$i]->id );
			$cusers[] = $usr;
		}
		return $cusers;
	}
	
	/**
	 * method to get the custom field options list.
	 * param - field id - int
	 * returm - array	 
	 */	 	 	
	
	function getFieldList($fieldId)
	{
		$db	=& $this->getDBO();
		
		$query	= 'SELECT `options` FROM `#__community_fields`';
		$query	.= ' WHERE `id` = ' . $db->Quote($fieldId);
		
		$db->setQuery($query);
		$result = $db->loadObject();
		$listOptions	= null;
		
		
		if(isset($result->options) && $result->options != '')
		{
			$listOptions	= $result->options;
			$listOptions	= explode("\n", $listOptions);
			array_walk($listOptions, array('JString' , 'trim') );
		}//end if
		
		return $listOptions;
	}
	
	function getAdvanceSearch($filter = array(), $join='and' , $avatarOnly = '' , $sorting = '' )
	{
		$limit 		= $this->getState('limit');
		$limitstart = $this->getState('limitstart');
	
		$db	=& $this->getDBO();
		
		$query	= $this->_buildCustomQuery($filter, $join , $avatarOnly );

		//ok now get the count 1st
		$queryCnt	= 'SELECT COUNT(1) FROM ('.$query.') AS z';
		$db->setQuery($queryCnt);		
		$total	= $db->loadResult();
		
		//setting pagination object.
		$this->_pagination = new JPagination($total, $limitstart, $limit);

		// @rule: Sorting if required.
		if( !empty( $sorting ) )
		{
			switch( $sorting )
			{
				case 'online':
					$query	.= 'ORDER BY online DESC';
					break;
				case 'alphabetical':
					$config	= CFactory::getConfig();
					$query	.= ' ORDER BY ' . $config->get('displayname') . ' ASC';
					break;
				default:
					$query	.= ' ORDER BY registerDate DESC';
					break;
			}
		}


		// execution of master query
		$query	.= ' LIMIT ' . $limitstart . ',' . $limit;

		$db->setQuery($query);
		$result = $db->loadResultArray();

		// Preload CUser objects
		if(! empty($result))
		{
			CFactory::loadUsers($result);
		}
		$cusers = array();
		for($i = 0; $i < count($result); $i++)
		{			
			//$usr = CFactory::getUser(	$result[$i]->user_id );
			$usr = CFactory::getUser( $result[$i] );
			$cusers[] = $usr;
		}		
		
		return 	$cusers;
	}
	
	function _buildCustomQuery($filter = array(), $join='and' , $avatarOnly = '')
	{
		$db	=& $this->getDBO();
		$query		= '';
		$itemCnt	= 0;
		
		CFactory::load('libraries', 'datetime');

		/**
		 * For the 'ALL' case, we use 'IN' whereas for 'ANY' case, we use UNION.
		 *
		 */
		if(! empty($filter))
		{
			$filterCnt	= count($filter);

			foreach($filter as $obj)
			{
				if($obj->field == 'username' || $obj->field == 'useremail')
				{
					$config		= CFactory::getConfig();
					
					$useArray	= array('username' => $config->get('displayname') , 'useremail' => 'email');
					
					if($itemCnt > 0 && $join == 'or')
					{
						$query	.= ' UNION ';
					}
					
					$query	.= ($join == 'or') ? ' (' : '';
					$query	.= ' SELECT DISTINCT( b.`userid` ) as `user_id`';

					if( $itemCnt == 0 || $join == 'or' )
					{
					    $query  .= ', a.`username` AS `username`';
					    $query  .= ', a.`name` AS `name`';
						$query  .= ', a.`registerDate` AS `registerDate`';
						$query	.= ', CASE WHEN s.userid IS NULL THEN 0 ELSE 1 END AS online';
					}

					$query  .= ' FROM `#__users` AS a';

					if( $itemCnt == 0 || $join == 'or' )
					{
						$query  .= ' LEFT JOIN `#__session` AS s';
						$query  .= ' ON a.`id`=s.`userid`';
					}

					$query	.= ' INNER JOIN `#__community_users` AS b';
					$query	.= ' ON a.`id` = b.`userid`';
					$query	.= ' AND a.`block` = 0';

					// @rule: Fetch records with proper avatar only.
					if( !empty($avatarOnly) )
					{
						$query	.= ' AND b.`thumb` != ' . $db->Quote( '' );
					}
					
					$query	.= ' WHERE ' . $this->_mapConditionKey($obj->condition, $obj->fieldType, $obj->value, $useArray[$obj->field]);

					$query	.= ($join == 'or') ? ' )' : '';
					
					if($itemCnt < ($filterCnt - 1) && $join == 'and')
					{
						$query	.= ' AND b.`userid` IN (';
					}
					
				}
				else
				{
					if($itemCnt > 0 && $join == 'or')
					{
						$query	.= ' UNION ';
					}
					
					$query	.= ($join == 'or') ? ' (' : '';
					$query	.= ' SELECT DISTINCT( a.`user_id` ) AS `user_id`';
					
					// We cannot select additional columns for the subquery otherwise it will result in operand errors,
					if( $itemCnt == 0 || $join == 'or' )
					{
					    $query  .= ', u.`username` AS `username`';
					    $query  .= ', u.`name` AS `name`';
						$query  .= ', u.`registerDate` AS `registerDate`';
						$query	.= ', CASE WHEN s.`userid` IS NULL THEN 0 ELSE 1 END AS online';
					}
					$query  .= ' FROM `#__community_fields_values` AS a';

					// We cannot select additional columns for the subquery otherwise it will result in operand errors,
					if( $itemCnt == 0 || $join == 'or' )
					{
						$query  .= ' LEFT JOIN `#__session` AS s';
					}

					$query  .= ' ON a.`id`=s.`userid`';
     				$query	.= ' INNER JOIN `#__community_fields` AS b';
					$query	.= ' ON a.`field_id` = b.`id`';
					$query	.= ' INNER JOIN `#__users` AS u ON a.`user_id` = u.`id`';
					$query	.= ' AND u.`block` =0';

					// @rule: Fetch records with proper avatar only.
					if( !empty($avatarOnly) )
					{
						$query	.= ' INNER JOIN `#__community_users` AS c ON a.`user_id`=c.`userid`';
						$query	.= ' AND c.`thumb` != ' . $db->Quote( '' );
					}

					if($obj->fieldType == 'birthdate')
					{
						$this->_birthdateFieldHelper($obj);
					}
					
					$query	.= ' WHERE b.`fieldcode` = ' . $db->Quote($obj->field);
					$query	.= ' AND ' . $this->_mapConditionKey($obj->condition, $obj->fieldType, $obj->value);

					$query	.= ($join == 'or') ? ' )' : '';
					
					if($itemCnt < ($filterCnt - 1) && $join == 'and')
					{
						$query	.= ' AND `user_id` IN (';
					}

				}
				$itemCnt++;
			}
			
			$closeTag	= '';
			if($itemCnt > 1)
			{
				for($i = 0; $i < ($itemCnt - 1); $i++)
				{
					$closeTag .= ' )';
				}
			}
			
			$query	= ($join == 'and') ? $query . $closeTag : $query;
		}
		
		return $query;
	}
	
	function _mapConditionKey($condition, $fieldType='text', $value, $fieldname = '')
	{
		$db	=& $this->getDBO();
		$condString	= (empty($fieldname)) ? ' a.`value`' : ' a.`' . $fieldname . '`';
		
		switch($condition)
		{
			case 'between':
				//for now assume the value is date.
				$startVal	= '';
				$endVal		= '';
				if(is_array($value))
				{
					$startVal	= $value[0];
					$endVal		= $value[1];
				}
				else
				{
					$startVal	= $value;
					$endVal		= $value;
				}				
				$condString	.= ' BETWEEN ' . $db->Quote($startVal) . ' AND ' . $db->Quote($endVal);
				break;
				
			case 'equal':
				if($fieldType != 'text' && $fieldType != 'select' && $fieldType != 'singleselect' && $fieldType != 'email' && $fieldType != 'radio') //this might be the list, select and etc. so we use like.
				{
					$chkOptionValue	= explode(',', $value);
					
					if($fieldType == 'checkbox' && count($chkOptionValue) > 1)
					{												
						$chkValue	= array_shift($chkOptionValue);						
						$condString = '(' . $condString;
						$condString	.= ' LIKE ' . $db->Quote('%'.$chkValue.'%');
						foreach($chkOptionValue as $chkValue)
						{
							$condString	.= (empty($fieldname)) ? ' OR a.`value`' : ' OR a.`' . $fieldname . '`';
							$condString	.= ' LIKE ' . $db->Quote('%'.$chkValue.'%'); 
						}
						$condString	.= ')';
					}
					else
					{
						$condString	.= (empty($value))? ' = ' . $db->Quote($value) : ' LIKE ' . $db->Quote('%'.$value.'%');
					}	
				}
				else
				{
					$condString	.= ' = ' . $db->Quote($value);				
				}								
				break;
				
			case 'notequal':
				if($fieldType != 'text' && $fieldType != 'select' && $fieldType != 'singleselect' && $fieldType != 'radio') //this might be the list, select and etc. so we use like.
				{
					$chkOptionValue	= explode(',', $value);

					if($fieldType == 'checkbox' && count($chkOptionValue) > 1)
					{
						$chkValue	= array_shift($chkOptionValue);						
						$condString = '(' . $condString;
						$condString	.= ' NOT LIKE ' . $db->Quote('%'.$chkValue.'%');
						foreach($chkOptionValue as $chkValue)
						{
							$condString	.= (empty($fieldname)) ? ' AND a.`value`' : ' AND a.`' . $fieldname . '`';
							$condString	.= ' NOT LIKE ' . $db->Quote('%'.$chkValue.'%'); 
						}
						$condString	.= ')';
					}
					else
					{
						$condString	.= ' NOT LIKE ' . $db->Quote('%'.$value.'%');
						//$condString	.= (empty($value))? ' != ' . $db->Quote($value) : ' NOT LIKE ' . $db->Quote('%'.$value.'%');
					}
				}
				else
				{			
					$condString	.= ' != ' . $db->Quote($value);
				}
				break;
				
			case 'lessthanorequal':
				$condString	.= ' <= ' . $db->Quote($value);
				break;
				
			case 'greaterthanorequal':
				$condString	.= ' >= ' . $db->Quote($value);
				break;
				
			case 'contain':
			default :
				$condString	.= ' LIKE ' . $db->Quote('%'.$value.'%');
				break;
		}
		$condString	.= (empty($join)) ? '' : ')';
		
		return $condString;
	}
	
	/**
	 * Simple video search to search the title and description
	 **/	 
	function searchVideo( $searchText )
	{
		$db		=& $this->getDBO();
		
		$limit			= $this->getState('limit');
		$limitstart		= $this->getState('limitstart');
		
		$query	= 'SELECT *, ' . $db->nameQuote('created') . ' AS lastupdated ' 
				. 'FROM ' . $db->nameQuote( '#__community_videos' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'status' ) . '=' . $db->Quote( 'ready' ) . ' ' 
				. 'AND ' . $db->nameQuote('published') . '=' . $db->Quote( 1 ) . ' '
				. 'AND (' . $db->nameQuote( 'title' ) . ' LIKE ' . $db->Quote( '%' . $searchText . '%' ) . ' '
				. 'OR ' . $db->nameQuote( 'description' ) . ' LIKE ' . $db->Quote( '%' . $searchText . '%' ) . ') ';
		
		$queryCnt	= 'SELECT COUNT(1) FROM ('.$query.') AS z';
		$db->setQuery($queryCnt);
		$this->_total= $db->loadResult();
		
		$query	.= 'LIMIT ' . $limitstart . ',' . $limit;
		 
		$db->setQuery( $query );
		$result	= $db->loadObjectList();
		
		// Appy pagination
		if (empty($this->_pagination))
		{		 	    
	 	    $this->_pagination = new JPagination($this->_total, $limitstart, $limit);
	 	}
		
		return $result;
	}
	
	/**
	 * auto user suggest search
	 * @param query	string	people's name to seach for
	 * param - fieldName	: string - name of the input box
	 *       - fieldId		: string - id of the input box	 
	 */	 
	function getAutoUserSuggest($searchName, $displayName)
	{
		$db	= &$this->getDBO();
		$filter = array();
		
		// build where condition
		$filterField = array();						
		if(isset($searchName))
		{	
	    	switch($displayName)
	    	{
	    		case 'name':
	    			$filter[] = "UCASE(`name`) like UCASE(" . $db->Quote('%'.$searchName.'%') . ")";
	    			break;
	    		case 'username':
	    		default :
					$filter[] = "UCASE(`username`) like UCASE(" . $db->Quote('%'.$searchName.'%') . ")";
	    			break;
			}
	    }
				
		$finalResult	= array();		
		if(count($filter)> 0 || count($filterField > 0))
		{
			// Perform the simple search
			$basicResult = null;
			if(!empty($filter) && count($filter)>0)
			{
				$query = "SELECT distinct b.`id` FROM #__users b";						  	    			  
				$query .= " WHERE b.block = 0 AND ".implode(' AND ',$filter);				
				//$query .=  " LIMIT " . $limitstart . "," . $limit;
												
				$db->setQuery( $query );
				$finalResult = $db->loadResultArray();
				if($db->getErrorNum()) {
					JError::raiseError( 500, $db->stderr());
				}
			}
		} 				

		if(empty($finalResult))
			$finalResult = array(0);
			
		$id = implode(",",$finalResult);
		$where = array("`id` IN (".$id.")");
	    $result = $this->getFiltered($where);
				
		return $result;
	}
	
	// since the user input value is age which is interger,
	// we need to convert it into datetime
	private function _birthdateFieldHelper(&$obj)
	{
		$datetime = new CDatetime();
		
		$obj->fieldType = 'birthdate';

		// correct the age order
		if (is_array($obj->value) && ($obj->value[1] > $obj->value[0]))
		{
			$obj->value = array_reverse($obj->value);
		}
		
		// TODO: something is wrong with comparing the datetime value
		// in text type instead of datetime type, 
		// e.g. BETWEEN '1955-09-07 00:00:00' AND '1992-09-07 23:59:59'   
		// we can't find '1992-02-26 23:59:59' in the result.
		
		if ($obj->condition == 'between')
		{
			$datetime->reset();
			$datetime->manipulate('year', '-'.$obj->value[0]);
			$obj->value[0]	= $datetime->toFormat('%Y-%m-%d 00:00:00');
			
			$datetime->reset();
			$datetime->manipulate('year', '-'.$obj->value[1]);
			//$datetime->manipulate('month', '12');
			$obj->value[1]	= $datetime->toFormat('%Y-%m-%d 23:59:59');
		}
		
		if ($obj->condition == 'equal')
		{
			// equal to an age means the birthyear range is 1 year
			// so we make it become a range
			$obj->condition = 'between';
			
			$datetime->reset();
			$age	= $obj->value;
			unset($obj->value);
			
			$datetime->manipulate('year', '-'.$age);
			$obj->value[0] = $datetime->toFormat('%Y-%m-%d 00:00:00');
			$datetime->manipulate('month', '+12');
			$datetime->manipulate('day', '-1');
			$obj->value[1] = $datetime->toFormat('%Y-%m-%d 23:59:59');
			
			
		}
		
		if ($obj->condition == 'lessthanorequal')
		{
			$obj->condition = 'between';
			$age	= $obj->value;
			unset($obj->value);
			
			$datetime->reset();
			$datetime->manipulate('year', '-'.$age);
			$datetime->manipulate('month', '-12');
			$obj->value[0] = $datetime->toFormat('%Y-%m-%d 00:00:00');
			
			$datetime->reset();
			$obj->value[1] = $datetime->toMySQL(true);
		}
		
		if ($obj->condition == 'greaterthanorequal')
		{
			$obj->condition = 'lessthanorequal'; //the datetime logic is inversed
			$age	= $obj->value;
			unset($obj->value);
			
			$datetime->reset();
			$datetime->manipulate('year', '-'.$age);
			$obj->value = $datetime->toFormat('%Y-%m-%d 12:59:59');
		}
		
		// correct the date order
		if (is_array($obj->value) && ($obj->value[1] < $obj->value[0]))
		{
			$obj->value = array_reverse($obj->value);
		}
		
	}
}
