<?php
/**
 * @category	Core
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model' );

class CommunityModelMemberlist extends JModel
{
	/**
	 * Configuration data
	 * 
	 * @var object
	 **/	 	 	 
	var $_params;

	/**
	 * Configuration data
	 * 
	 * @var object	JPagination object
	 **/	 	 	 
	var $_pagination;

	/**
	 * Configuration data
	 * 
	 * @var int	Total number of rows
	 **/
	var $_total;

	/**
	 * Configuration data
	 * 
	 * @var int	Total number of rows
	 **/
	var $_data;
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		$mainframe	=& JFactory::getApplication();

		// Call the parents constructor
		parent::__construct();

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( 'com_community.memberlist.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
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
		// Lets load the content if it doesn't already exist
		if ( empty( $this->_pagination ) )
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Method to return the total number of rows
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Load total number of rows
		if( empty($this->_total) )
		{
			$this->_total	= $this->_getListCount( $this->_buildQuery() );
		}

		return $this->_total;
	}

	/**
	 * Build the SQL query string
	 *
	 * @access	private
	 * @return	string	SQL Query string	 
	 */
	function _buildQuery()
	{		
		$db			=& JFactory::getDBO();

		$condition	= '';
		$mainframe	= JFactory::getApplication();
		$ordering		= $mainframe->getUserStateFromRequest( "com_community.memberlist.filter_order",		'filter_order',		'a.title',	'cmd' );
		$orderDirection	= $mainframe->getUserStateFromRequest( "com_community.memberlist.filter_order_Dir",	'filter_order_Dir',	'',			'word' );
		
		$orderBy		= ' ORDER BY '. $ordering .' '. $orderDirection;
		$search			= $mainframe->getUserStateFromRequest( "com_community.memberlist.search", 'search', '', 'string' );
		
		if( !empty( $search ) )
		{
			$condition	.= ' AND ( a.title LIKE ' . $db->Quote( '%' . $search . '%' ) . ' '
							. 'OR a.description LIKE ' . $db->Quote( '%' . $search . '%' ) . ' '
							. ')'; 
		}
		
		$query		= 'SELECT a.* FROM ' . $db->nameQuote( '#__community_memberlist' ) . ' AS a '
					. 'WHERE 1'
					. $condition
					. $orderBy;

		return $query;
	}

	/**
	 * Returns the memberlist
	 *
	 * @return Array	Array of groups object
	 **/
	function getMemberList()
	{
		if(empty($this->_data))
		{

			$query = $this->_buildQuery( );

			$this->_data	= $this->_getList( $this->_buildQuery() , $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_data;
	}
}