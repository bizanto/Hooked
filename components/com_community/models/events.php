<?php
/**
 * @category	Model
 * @package		JomSocial
 * @subpackage	Groups
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

require_once ( JPATH_ROOT .DS.'components'.DS.'com_community'.DS.'models'.DS.'models.php');

// Deprecated since 1.8.x to support older modules / plugins
CFactory::load( 'tables' , 'event' );
CFactory::load( 'tables' , 'eventcategory' );
CFactory::load( 'tables' , 'eventmembers' );
CFactory::load( 'helpers' , 'event' );

jimport( 'joomla.utilities.date' );
class CommunityModelEvents extends JCCModel
implements CGeolocationSearchInterface
{
	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $_pagination	= '';

	/**
	 * Configuration data
	 *
	 * @var object	JPagination object
	 **/
	var $total			= '';

	/**
	 * member count data
	 *
	 * @var int
	 **/
	var $membersCount	= array();

	/**
	 * Constructor
	 */
	function CommunityModelEvents()
	{
		parent::JCCModel();

		$mainframe =& JFactory::getApplication();

		// Get pagination request variables
 	 	$limit		= ($mainframe->getCfg('list_limit') == 0) ? 5 : $mainframe->getCfg('list_limit');
	    $limitstart = JRequest::getVar('limitstart', 0, 'REQUEST');
//		$limit		= 1;

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		return $this->_pagination;
	}

	/**
	 * Method to retrieve total events for a specific group
	 * 
	 * @param	int		$groupId	The unique group id.
	 * @return	array	$result		An array of result.
	 **/	 	 	 	 	
	public function getTotalGroupEvents( $groupId )
	{
		CFactory::load( 'helpers' , 'event' );
		$db		=& $this->getDBO();
		$query	= 'SELECT COUNT(*) FROM ' . $db->nameQuote( '#__community_events' ) . ' WHERE '
				. $db->nameQuote( 'type' ) . '=' . $db->Quote( CEventHelper::GROUP_TYPE ) . ' AND '
				. $db->nameQuote( 'contentid' ) . '=' . $db->Quote( $groupId );
		$db->setQuery( $query );
		$result	= $db->loadResult();

		return $result;
	}
	
	/**
	 * Method to retrieve events for a specific group
	 * 
	 * @param	int		$groupId	The unique group id.
	 * @return	array	$result		An array of result.
	 **/	 	 	 	 	
	public function getGroupEvents( $groupId , $limit = 0 )
	{
		CFactory::load( 'helpers' , 'event' );
		$db		=& $this->getDBO();

		$query	= 'SELECT * FROM ' . $db->nameQuote( '#__community_events' ) . ' WHERE '
				. $db->nameQuote( 'type' ) . '=' . $db->Quote( CEventHelper::GROUP_TYPE ) . ' AND '
				. $db->nameQuote( 'contentid' ) . '=' . $db->Quote( $groupId );

		if( $limit != 0 )
		{
			$query	.= 'LIMIT 0,' . $limit;
		}
		
		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		
		$query	= 'SELECT COUNT(*) FROM ' . $db->nameQuote( '#__community_events' ) . ' WHERE '
				. $db->nameQuote( 'type' ) . '=' . $db->Quote( CEventHelper::GROUP_TYPE ) . ' AND '
				. $db->nameQuote( 'contentid' ) . '=' . $db->Quote( $groupId );

		$db->setQuery( $query );		
		$total	= $db->loadObjectList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		if( empty($this->_pagination) )
		{
			$limit		= $this->getState('limit');
			$limitstart = $this->getState('limitstart');
			jimport('joomla.html.pagination');

			$this->_pagination	= new JPagination( $total, $limitstart, $limit );
		}
				
		return $result;
	}
	
	/**
	 * Returns an object of events which the user has registered.
	 *
	 * @access	public
	 * @param	string 	User's id.
	 * @param	string 	sorting criteria.
	 * @returns array  An objects of event fields.
	 */
	function getEvents( $categoryId = null, $userId = null , $sorting = null, $search = null, $hideOldEvent = true, $showOnlyOldEvent = false, $pending = null, $advance = null , $type = CEventHelper::ALL_TYPES , $contentid = 0 , $limit = null )
	{
		$db	    =&	$this->getDBO();
		$join	    =	'';
		$extraSQL   =	'';

		if( !empty($userId) )
		{
			$join	    =	'LEFT JOIN ' . $db->nameQuote('#__community_events_members') . ' AS b ON a.id=b.eventid ';
			$extraSQL   .= ' AND b.memberid=' . $db->Quote($userId);
		}
		
		if( !empty($search) )
		{
			$extraSQL   .= ' AND a.title LIKE ' . $db->Quote( '%' . $search . '%' );
		}
		
		if( !empty($categoryId) && $categoryId != 0 )
		{
			$extraSQL   .= ' AND a.catid=' . $db->Quote($categoryId);
		}

		if( !is_null( $pending ) && !empty($userId) )
		{
			$extraSQL   .= ' AND b.status=' . $db->Quote($pending);
		}

		/* Begin : ADVANCE SEARCH */
		if( !empty($advance) )
		{
			if( !empty($advance['startdate']) )
			{
				$startDate	=   CTimeHelper::getDate( strtotime($advance['startdate']) );

				$extraSQL	.=  ' AND a.startdate >= ' . $db->Quote( $startDate->toMySQL() );

			}
			else // If empty, don't select the past event
			{
				$now		=   CTimeHelper::getDate();

				$extraSQL	.=  ' AND a.startdate >= ' . $db->Quote( $now->toMySQL() );
			}

			if( !empty($advance['enddate']) )
			{
				$endDate	=   CTimeHelper::getDate( strtotime($advance['enddate']) );

				$extraSQL	.=  ' AND a.startdate <= ' . $db->Quote( $endDate->toMySQL() );
			}

			/* Begin : SEARCH WITHIN */
			if( !empty($advance['radius']) && !empty($advance['fromlocation']) ){

				$longitude  =	null;
				$latitude   =	null;

				CFactory::load('libraries', 'mapping');
				$data = CMapping::getAddressData( $advance['fromlocation'] );

				if($data){
					if($data->status == 'OK')
					{
						$latitude  = (float) $data->results[0]->geometry->location->lat;
						$longitude = (float) $data->results[0]->geometry->location->lng;
					}
				}

				$now = new JDate();

				$lng_min = $longitude - $advance['radius'] / abs(cos(deg2rad($latitude)) * 69);
				$lng_max = $longitude + $advance['radius'] / abs(cos(deg2rad($latitude)) * 69);
				$lat_min = $latitude - ($advance['radius'] / 69);
				$lat_max = $latitude + ($advance['radius'] / 69);

				$extraSQL   .=	' AND a.longitude > ' . $db->quote($lng_min)
						. ' AND a.longitude < ' . $db->quote($lng_max)
						. ' AND a.latitude > ' . $db->quote($lat_min)
						. ' AND a.latitude < ' . $db->quote($lat_max);

			}
			/* End : SEARCH WITHIN */
		}
		/* End : ADVANCE SEARCH */

		$limitstart =   $this->getState('limitstart');
		$limit	    =   $limit === null ? $this->getState('limit') : $limit;

		if( $type != CEventHelper::ALL_TYPES )
		{
			$extraSQL   .=  ' AND a.type=' . $db->Quote( $type );
			$extraSQL   .=  ' AND a.contentid=' . $contentid;
		}

		if( $type == CEventHelper::GROUP_TYPE || $type == CEventHelper::ALL_TYPES )
		{
			// @rule: Respect group privacy
			$join		.=  ' LEFT JOIN ' . $db->nameQuote('#__community_groups') . ' AS g';
			$join 		.= ' ON g.id = a.contentid ';
			
			if( $type != CEventHelper::GROUP_TYPE )
			{
				$extraSQL	.= ' AND (g.approvals = 0 OR g.approvals IS NULL';
				
				if( !empty($userId ) )
				{
					$extraSQL	.= ' OR b.memberid=' . $db->Quote( $userId );
				}
				$extraSQL	.= ')';
			}
		}

		$orderBy    =	'';
		$total	    =	0;

		switch($sorting)
		{			
			case 'latest':
				if( empty($orderBy) )
					$orderBy	= ' ORDER BY a.created DESC';
				break;
			case 'alphabetical':
				if( empty($orderBy) )
					$orderBy	= ' ORDER BY a.title ASC';
				break;
			case 'startdate':
			default:
				$orderBy	= ' ORDER BY a.startdate ASC';
				break;
		}
		
		$now = new JDate();
		
		if($hideOldEvent && $type != CEventHelper::GROUP_TYPE )
		{
			$extraSQL .= ' AND a.enddate > ' . $db->Quote( $now->toMySQL() );
		}

		if($showOnlyOldEvent)
		{
			$extraSQL .= ' AND a.enddate < ' . $db->Quote( $now->toMySQL() );
		}
		
		$limit	= empty($limit) ? 0 : $limit;
				
		$query	= 'SELECT DISTINCT a.* FROM '
				. $db->nameQuote('#__community_events') . ' AS a '
				. $join
				. 'WHERE a.published=' . $db->Quote( '1' )
				. $extraSQL
				. $orderBy
				. ' LIMIT ' . $limitstart . ', ' . $limit;

		$db->setQuery( $query );		
		$result	= $db->loadObjectList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		$query	= 'SELECT COUNT(DISTINCT(a.`id`)) FROM '
				. $db->nameQuote('#__community_events') . ' AS a '
				. $join
				. 'WHERE a.published=' . $db->Quote( '1' )
				. $extraSQL;

		$db->setQuery( $query );
		$this->total	= $db->loadResult();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		$query	= 'SELECT COUNT(DISTINCT(a.`id`)) FROM ' . $db->nameQuote('#__community_events') . ' AS a '
				. $join
				. 'WHERE a.published=' . $db->Quote( '1' ) . ' '
				. $extraSQL;

		$db->setQuery( $query );
		$total	= $db->loadResult();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		if( empty($this->_pagination) )
		{
			jimport('joomla.html.pagination');

			$this->_pagination	= new JPagination( $total, $limitstart, $limit );
		}

		return $result;
	}

	/**
	 * Return the number of groups count for specific user
	 **/
	function getEventsCount( $userId )
	{
		// guest obviously has no group
		if($userId == 0)
			return 0;

		$now	=& JFactory::getDate();
		$db		=& $this->getDBO();
		$query	= 'SELECT COUNT(*) FROM '
				. $db->nameQuote( '#__community_events_members' ) . ' AS a '
				. 'INNER JOIN ' . $db->nameQuote( '#__community_events' ) . ' AS b '
				. 'ON b.`id`=a.`eventid` '
				. 'AND b.`enddate` > ' . $db->Quote( $now->toMySQL() ) . ' '
				. 'WHERE a.`memberid`=' . $db->Quote( $userId ) . ' '
				. 'AND a.`status` IN ('.COMMUNITY_EVENT_STATUS_INVITED.','.COMMUNITY_EVENT_STATUS_ATTEND.','.COMMUNITY_EVENT_STATUS_MAYBE.')';
				
		$db->setQuery( $query );
		$count	= $db->loadResult();

		return $count;
	}
	
	/**
	 * Return the number of groups cretion count for specific user
	 **/
	function getEventsCreationCount( $userId )
	{
		// guest obviously has no events
		if($userId == 0)
			return 0;

		$db		=& $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM '
				. $db->nameQuote( '#__community_events' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'creator' ) . '=' . $db->Quote( $userId );
		$db->setQuery( $query );

		$count	= $db->loadResult();

		return $count;
	}

	/**
	 * Returns the count of the members of a specific group
	 *
	 * @access	public
	 * @param	string 	Group's id.
	 * @return	int	Count of members
	 */
	function getMembersCount( $id )
	{
		$db	=& $this->getDBO();

		if( !isset($this->membersCount[$id] ) )
		{
			$query	= 'SELECT COUNT(*) FROM ' . $db->nameQuote('#__community_events_members') . ' '
					. 'WHERE eventid=' . $db->Quote( $id ) . ' '
					. 'AND ' . $db->nameQuote( 'status' ) . ' IN ('.COMMUNITY_EVENT_STATUS_INVITED.','.COMMUNITY_EVENT_STATUS_ATTEND.','.COMMUNITY_EVENT_STATUS_MAYBE.')';

			$db->setQuery( $query );
			$this->membersCount[$id]	= $db->loadResult();

			if($db->getErrorNum())
			{
				JError::raiseError( 500, $db->stderr());
			}
		}
		return $this->membersCount[$id];
	}

	/**
	 * Loads the categories
	 *
	 * @access	public
	 * @returns Array  An array of categories object
	 */
	function getCategories( $type = CEventHelper::PROFILE_TYPE, $catId = COMMUNITY_ALL_CATEGORIES )
	{
		$db		=& $this->getDBO();
		$where	= '';
		$join	= '';
		
		if( $catId !== COMMUNITY_ALL_CATEGORIES )
		{
			if( $catId === COMMUNITY_NO_PARENT )
			{
				$where	=   'WHERE a.parent=' . $db->Quote( COMMUNITY_NO_PARENT ) . ' ';
			}
			else
			{
				$where	=   'WHERE a.parent=' . $db->Quote( $catId ) . ' ';
			}
		}
		
		if( $type != CEventHelper::ALL_TYPES )
		{
			$where	.= ' AND b.type=' . $db->Quote( $type ) . ' ';
		}
		else
		{
			// @rule: Respect group privacy
			$join	=  ' LEFT JOIN ' . $db->nameQuote('#__community_groups') . ' AS g';
			$join 	.= ' ON b.contentid = g.id ';
			$where  .= ' AND (g.approvals = 0 OR g.approvals IS NULL) ';
		}

		$now	=   new JDate();
		$query	=   'SELECT a.*, COUNT(b.id) AS count '
			    . 'FROM ' . $db->nameQuote('#__community_events_category') . ' AS a '
			    . 'LEFT OUTER JOIN ' . $db->nameQuote( '#__community_events' ) . ' AS b '
			    . 'ON a.id=b.catid '
			    . 'AND b.enddate > ' . $db->Quote($now->toMySQL())
			    . 'AND b.published=' . $db->Quote( '1' )
			    . $join
			    . $where 
			    . 'GROUP BY a.id ORDER BY a.name ASC';

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $result;
	}

	/**
	 * Returns the category name of the specific category
	 *
	 * @access public
	 * @param	string Category Id
	 * @returns string	Category name
	 **/
	function getCategoryName( $categoryId )
	{
		CError::assert($categoryId, '', '!empty', __FILE__ , __LINE__ );
		$db		=& $this->getDBO();

		$query	= 'SELECT ' . $db->nameQuote('name') . ' '
				. 'FROM ' . $db->nameQuote('#__community_events_category') . ' '
				. 'WHERE ' . $db->nameQuote('id') . '=' . $db->Quote( $categoryId );
		$db->setQuery( $query );

		$result	= $db->loadResult();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		CError::assert( $result , '', '!empty', __FILE__ , __LINE__ );
		return $result;
	}

	/**
	 * Check if the given group name exist.
	 * if id is specified, only search for those NOT within $id
	 */
	function isEventExist($title, $location, $id=0) {
		$db		=& $this->getDBO();

		$strSQL	= 'SELECT count(*) FROM `#__community_events`'
			. " WHERE `title` = " . $db->Quote( $title )
			. " AND `location` = " . $db->Quote( $location )
			. " AND `id` != " . $db->Quote( $id ) ;


		$db->setQuery( $strSQL );
		$result	= $db->loadResult();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		return $result;
	}

	/**
	 * Delete group's wall
	 *
	 * param	string	id The id of the group.
	 *
	 **/
	function deleteGroupWall($gid)
	{
		$db =& JFactory::getDBO();

		$sql = "DELETE

				FROM
						".$db->nameQuote("#__community_wall")."
				WHERE
						".$db->nameQuote("contentid")." = ".$db->quote($gid)." AND
						".$db->nameQuote("type")." = ".$db->quote('groups');
		$db->setQuery($sql);
		$db->Query();
		if($db->getErrorNum()){
			JError::raiseError( 500, $db->stderr());
		}

		return true;
	}
	
	/* Implement interfaces */
	
	/**
	 * caller should verify that the address is valid
	 */	 	
	public function searchWithin($address, $distance)
	{
		$db =& JFactory::getDBO();
		
		$longitude = null;
		$latitude = null;
		
		CFactory::load('libraries', 'mapping');
		$data = CMapping::getAddressData($address);
		
		if($data){
			if($data->status == 'OK')
			{
				$latitude  = (float) $data->results[0]->geometry->location->lat;
				$longitude = (float) $data->results[0]->geometry->location->lng; 
			}
		}
		
		if(is_null($latitude) || is_null($longitude)){
			return $null;
		}
		/*
		code from 
		http://blog.fedecarg.com/2009/02/08/geo-proximity-search-the-haversine-equation/
		*/	
		
		//$longitude = (float) 101.678;
		//$latitude = (float) 3.11966 ;
		
		// $radius = $radius_in_km * 0.621371192;
		$radius = 20; // in miles
		
		$lng_min = $longitude - $radius / abs(cos(deg2rad($latitude)) * 69);
		$lng_max = $longitude + $radius / abs(cos(deg2rad($latitude)) * 69);
		$lat_min = $latitude - ($radius / 69);
		$lat_max = $latitude + ($radius / 69);
		
		//echo 'lng (min/max): ' . $lng_min . '/' . $lng_max . PHP_EOL;
		//echo 'lat (min/max): ' . $lat_min . '/' . $lat_max;
		
		$now = new JDate();
		$sql = "SELECT *

				FROM
						".$db->nameQuote("#__community_events")."
				WHERE
						".$db->nameQuote("longitude")." > ".$db->quote($lng_min)." AND
						".$db->nameQuote("longitude")." < ".$db->quote($lng_max)." AND
						".$db->nameQuote("latitude")." > ".$db->quote($lat_min)." AND
						".$db->nameQuote("latitude")." < ".$db->quote($lat_max)." AND
						".$db->nameQuote("enddate")." > ".$db->quote($now->toMySQL());
	
		$db->setQuery($sql);
		echo $db->getQuery();
		$results = $db->loadObjectList();
		
		return $results;
	}

	/**
	 *	Get the pending invitations
	 *
	 */
	public function getPending($userId){
		if($userId == 0){
			return null;
		}

		$limit		= $this->getState('limit');
		$limitstart = $this->getState('limitstart');

		$db		=&	JFactory::getDBO();

		$query	=	"SELECT a.*, b.title, b.thumb"
					. " FROM " . $db->nameQuote("#__community_events_members") . " AS a, "
					. $db->nameQuote("#__community_events") . " AS b"
					. " WHERE a.`memberid`=" . $db->Quote($userId)
					. " AND a.`eventid`=b.`id`"
					. " AND b.`published`=" . $db->Quote( 1 )
					. " AND a.`status`=" . $db->Quote( COMMUNITY_EVENT_STATUS_INVITED )
					. " ORDER BY a.`id` DESC"
					. " LIMIT {$limitstart}, {$limit}";
					
		$db->setQuery($query);

		if( $db->getErrorNum() ){
			JError::raiseError(500, $db->stderr());
		}

		$result = $db->loadObjectList();
		
		return $result;
	}

	/**
	 * Check if I was invited and if yes return true
	 * If Event Id is provided, will return the invitation informations
	 *
	 */
	public function isInvitedMe($invitationId=0, $userId=0, $eventId=0){
		$db		=&	$this->getDBO();

		if( $eventId == 0 )
		{
		    $query	=   "SELECT COUNT(*) FROM "
				    . $db->nameQuote("#__community_events_members")
				    . " WHERE " . $db->nameQuote("id") . "=" . $db->Quote($invitationId)
				    . " AND " . $db->nameQuote("memberid") . "=" . $db->Quote($userId)
				    . " AND " . $db->nameQuote("status") . "=" . COMMUNITY_EVENT_STATUS_INVITED;

		    $db->setQuery($query);

		    $status = ($db->loadResult() > 0) ? true : false;

		    if ($db->getErrorNum()){
			    JError::raiseError(500, $db->stderr());
		    }

		    return $status;
		}
		else
		{
		    $query	=   "SELECT * FROM "
				    . $db->nameQuote("#__community_events_members")
				    . " WHERE " . $db->nameQuote("memberid") . "=" . $db->Quote($userId)
				    . " AND " . $db->nameQuote("eventid") . "=" . $db->Quote($eventId)
				    . " AND " . $db->nameQuote("status") . "=" . COMMUNITY_EVENT_STATUS_INVITED
				    . " AND " . $db->nameQuote("invited_by") . "!=" . $db->Quote($userId)
				    . " AND " . $db->nameQuote("invited_by") . "!=" . $db->Quote(0);

		    $db->setQuery($query);

		    $result = $db->loadObjectList();

		    return $result;
		}
	}

	/**
	 * Return the count of the user's friend of a specific event
	 */
	function getFriendsCount( $userid, $eventid )
	{
		$db	=& $this->getDBO();

		$query	=   'SELECT COUNT(DISTINCT(a.connect_to)) AS id  FROM ' . $db->nameQuote('#__community_connection') . ' AS a '
			    . 'INNER JOIN ' . $db->nameQuote( '#__users' ) . ' AS b '
			    . 'INNER JOIN ' . $db->nameQuote( '#__community_events_members' ) . ' AS c '
			    . 'ON a.connect_from=' . $db->Quote( $userid ) . ' '
			    . 'AND a.connect_to=b.id '
			    . 'AND c.eventid=' . $db->Quote( $eventid ) . ' '
			    . 'AND a.connect_to=c.memberid '
			    . 'AND a.status=' . $db->Quote( '1' ) . ' '
			    . 'AND c.status=' . $db->Quote( '1' );

		$db->setQuery( $query );

		$total = $db->loadResult();

		return $total;
	}

	/**
	 * Count total pending event invitations.
	 *
	 **/
	public function countPending($id){
		$db = & $this->getDBO();

		$query	= 'SELECT COUNT(*) FROM '
				. $db->nameQuote( '#__community_events_members' ) . ' AS a '
				. 'INNER JOIN ' . $db->nameQuote( '#__community_events' ) . ' AS b '
				. 'ON b.`id`=a.`eventid` '
				. 'AND b.`published`=' . $db->Quote( 1 ) . ' '
				. 'WHERE a.`memberid`=' . $db->Quote( $id ) . ' '
				. 'AND a.`status`=' . $db->Quote( COMMUNITY_EVENT_STATUS_INVITED ) . ' '
				. 'ORDER BY a.`id` DESC';

		$db->setQuery($query);
		
		if ($db->getErrorNum())
		{
			JError::raiseError(500, $db->stderr());
		}

		return $db->loadResult();
	}

    /**
     * @deprecated Since 2.0
     */
	function getThumbAvatar($id, $thumb)
	{
		CFactory::load('helpers', 'url');
		$thumb	= CUrlHelper::avatarURI($thumb, 'event_thumb.png');
		
		return $thumb;
	}

	/**
	 * Return events search total
	 *
	 */
	function getEventsSearchTotal()
	{
		return $this->total;
	}

}

