<?php

/*
 * Created on Jan 28, 2011

 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class ResetPasswordModelResetPassword extends JModel {

	/**
	 * Items total
	 * @var integer
	 */
	public $_total = null;

	/**
	 * Pagination object
	 * @var object
	 */
	public $_pagination = null;

	public function __construct() {
		parent :: __construct();

		
		
		$mainframe = JFactory::getApplication();

		// Get pagination request variables
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = JRequest :: getVar('limitstart', 0, '', 'int');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

	}

	private function _buildQuery() {
		$sql = 'select SQL_CALC_FOUND_ROWS log.date, u.* from #__resetpasswordlog as log inner join #__users as u on log.user_id = u.id order by date desc ';
		return $sql;
	}

	public function getTotal() {
		if(!$this->_total) { 
			$db = JFactory :: getDBO();
			$db->setQuery('SELECT FOUND_ROWS();');
		    $this->_total = $db->loadResult(); 
		}
		
		return $this->_total ; 
	}

	public function getData() {

		// Lets load the data if it doesn't already exist
		if (empty ($this->_data)) {
			$query = $this->_buildQuery();

			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data ? $this->_data : array ();
	}

	function getPagination() {
		// Load the content if it doesn't already exist
		if (empty ($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;
	}
}

?>