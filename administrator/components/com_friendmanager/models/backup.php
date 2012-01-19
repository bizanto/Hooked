<?php
/**
 * @version $Id$
 * @package    FriendManager
 * @subpackage _ECR_SUBPACKAGE_
 * @author     Socialable Studios {@link http://www.Socialables.com}
 * @author     Created on 16-Jan-2010
 * @copyright	Copyright (C) 2005 - 2010 Socialables.com All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

//-- No direct access
defined('_JEXEC') or die('=;)');

jimport( 'joomla.application.component.model' );

/**
 * FriendManager Model
 *
 * @package    FriendManager
 * @subpackage Models
 */
class FriendManagersModelBackup extends JModel
{

	function __construct()
	{
		parent::__construct();
	 	
	}//function
	

	/**
	 * Returns the query
	 * @return string The query to be used to retrieve the rows from the database
	 */
	function backup($msg='')
	{
		$date = time();		
		
		$query = "CREATE TABLE IF NOT EXISTS #__community_connection_backup_".$date." LIKE #__community_connection";
		$this->_db->setQuery($query);
		$this->_db->query();
		
		$query = "INSERT INTO #__community_connection_backup_".$date." SELECT * FROM #__community_connection";
		$this->_db->setQuery($query);
		$this->_db->query();

		$this->backupDate($date, $msg);

		return true;
		
		
	}
	
	function clearDB()
	{		
		//$this->backup("Backup Prior to clear DB");
		$query = "TRUNCATE TABLE `#__community_connection`";
		$this->_db->setQuery($query);
		$this->_db->query();

		return true;
	}
	
	function _dropDB()
	{
		$query = "DROP TABLE `#__community_connection`";
		$this->_db->setQuery($query);
		$this->_db->query() ? $r=TRUE : $r=FALSE;

		return $r;
	}
	
	function backupDate($date, $msg='')
	{
		$date = gmdate("Y-m-d H:i:s", $date);
		
		$query = "INSERT INTO `#__friendmanager` (`backupdate`, `msg`) VALUES ('$date', '$msg')";
		$this->_db->setQuery($query);
		$this->_db->query();

		return true;
	}
	
	function restoreDB()
	{
		
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$row =& $this->getTable();
		
		if (count( $cids ) == 1)
		{
			foreach($cids as $cid) {
				if (!$row->load( $cid )) {
					$this->setError( $row->getErrorMsg() );
					return false;
				}								
			}//foreach
		}		
		
		$date = strtotime($row->backupdate);
		$this->backup("Backup Prior to restore DB $row->backupdate");
		
			$this->clearDB();
			$query = "INSERT INTO #__community_connection SELECT * FROM #__community_connection_backup_".$date;			
			$this->_db->setQuery($query);
			$this->_db->query();

		return true;
		
	}


}//class