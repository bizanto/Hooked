<?php
/**
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

require_once ( JPATH_ROOT .DS.'components'.DS.'com_community'.DS.'models'.DS.'models.php');

// Deprecated since 1.8.x to support older modules / plugins
CFactory::load( 'tables' , 'group' );
CFactory::load( 'tables' , 'bulletin' );
CFactory::load( 'tables' , 'groupinvite' );
CFactory::load( 'tables' , 'groupmembers' );
CFactory::load( 'tables' , 'discussion' );
CFactory::load( 'tables' , 'category' );

class CommunityModelDiscussions extends JCCModel
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
	 * Constructor
	 */
	function CommunityModelDiscussions()
	{
		parent::JCCModel();
		
		$mainframe =& JFactory::getApplication();
		
		// Get pagination request variables
 	 	$limit		= ($mainframe->getCfg('list_limit') == 0) ? 5 : $mainframe->getCfg('list_limit');
	    $limitstart = JRequest::getVar('limitstart', 0, 'REQUEST');
	    
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
	 * Get list of discussion topics
	 *
	 * @param	$id	The group id
	 * @param	$limit Limit
	 **/
	function getDiscussionTopics( $groupId , $limit = 0 , $order = '' )
	{
		$db			=& $this->getDBO();
		$limit		= ($limit == 0) ? $this->getState( 'limit' ) : $limit;
		$limitstart	= $this->getState( 'limitstart' );

		$query	= 'SELECT COUNT(*) FROM ' . $db->nameQuote('#__community_groups_discuss') . ' '
				. 'WHERE ' . $db->nameQuote( 'groupid' ) . '=' . $db->Quote( $groupId )
				. 'AND parentid=' . $db->Quote( '0' );

		$db->setQuery( $query );
		$total	= $db->loadResult();
		$this->total	= $total;
		
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
				
		if( empty($this->_pagination) )
		{
			jimport('joomla.html.pagination');
			
			$this->_pagination	= new JPagination( $total , $limitstart , $limit);
		}
		
		$orderByQuery	= '';
		switch( $order )
		{
			default:
				$orderByQuery = 'ORDER BY a.lastreplied DESC ';
				break;
		}
		
		$query		= 'SELECT a.*, COUNT( b.id ) AS count, b.comment AS lastmessage '
					. 'FROM ' . $db->nameQuote( '#__community_groups_discuss' ) . ' AS a '
					. 'LEFT JOIN ' . $db->nameQuote( '#__community_wall' ) . ' AS b ON b.contentid=a.id '
					. 'AND b.`date`=( SELECT max( date ) FROM #__community_wall WHERE `contentid`=a.id) '
					. 'AND b.type=' . $db->Quote( 'discussions' ) . ' '
					. 'LEFT JOIN ' . $db->nameQuote( '#__community_wall' ) . ' AS c ON c.`contentid`=a.`id` '
					. 'AND c.`type`=' . $db->Quote( 'discussions') . ' '
					. 'WHERE a.groupid=' . $db->Quote( $groupId ) . ' '
					. 'AND a.parentid=' . $db->Quote( '0' ) . ' '
					. 'GROUP BY a.id '
					. $orderByQuery
					. 'LIMIT ' . $limitstart . ',' . $limit;

		$db->setQuery( $query );
		$result	= $db->loadObjectList();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}

		return $result;
	}

	/**
	 * Method to get the last replier information from specific discussion
	 * 
	 * @params $discussionId	The specific discussion row id
	 **/
	function getLastReplier( $discussionId )
	{
		$db		=& $this->getDBO();
		
		$query	= 'SELECT * FROM ' . $db->nameQuote( '#__community_wall' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'contentid' ) . '=' . $db->Quote( $discussionId ) . ' '
				. 'AND ' . $db->nameQuote( 'type' ) . '=' . $db->Quote( 'discussions' )
				. 'ORDER BY date DESC LIMIT 1';
		$db->setQuery( $query );
		$result	= $db->loadObject();
		
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		
		return $result;
	}
	
	function getRepliers( $discussionId , $groupId )
	{
		$db		=& JFactory::getDBO();
		$query	= 'SELECT DISTINCT(a.post_by) FROM ' . $db->nameQuote( '#__community_wall' ) . ' AS a '
				. 'INNER JOIN #__community_groups_members AS b '
				. 'ON b.groupid=' . $db->Quote( $groupId ) . ' '
				. 'WHERE ' . $db->nameQuote( 'a.contentid' ) . '=' . $db->Quote( $discussionId ) . ' '
				. 'AND ' . $db->nameQuote( 'a.type' ) . '=' . $db->Quote( 'discussions' ) . ' '
				. 'AND a.post_by=b.memberid';

		$db->setQuery( $query );
		return $db->loadResultArray();
	}
	
	/**
	 * Return a list of discussion replies.
	 * 
	 * @param	int		$topicId	The replies for specific topic id.
	 * @return	Array	An array of database objects.
	 **/	 	 	 	 	
	public function getReplies( $topicId )
	{
		$db		=& JFactory::getDBO();

		$query	= 'SELECT a.* , b.name FROM #__community_wall AS a '
				. 'INNER JOIN #__users AS b '
				. 'WHERE b.id=a.post_by '
				. 'AND a.type=' . $db->Quote( 'discussions' ) . ' '
				. 'AND a.contentid=' . $db->Quote( $topicId )
				. ' ORDER BY a.date DESC ';
 
		$db->setQuery( $query );
		
		if($db->getErrorNum())
		{
			JError::raiseError(500, $db->stderr());
		}
		
		$result	= $db->loadObjectList();

		return $result;
	}
}
