<?php
/**
 * @version $Id$
 * @package    FriendManager
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

jimport('joomla.application.component.controller');

/**
 * FriendManager Controller
 *
 * @package    FriendManager
 * @subpackage Controllers
 */
class FriendManagersControllerFriendBackup extends FriendManagersController
{
	/**
	 * constructor (registers additional tasks to methods)
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		//$this->registerTask( 'add' , 'edit' );
	}// function

	/**
	 * remove record(s)
	 * @return void
	 */
	function remove()
	{
		$model = $this->getModel('FriendManager');
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More Connections Could not be Deleted' );
		} else {
			$msg = JText::_( 'Connection(s) Deleted' );
		}

		$this->setRedirect( 'index.php?option=com_friendmanager', $msg );
	}// function

	/**
	 * cancel editing a record
	 * @return void
	 */
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$this->setRedirect( 'index.php?option=com_friendmanager', $msg );
	}// function
	
	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function backup()
	{
		$model = $this->getModel('Backup');

		if ($model->backup('Button Backup')) {
			$msg = JText::_( 'Backup Saved!' );
		} else {
			$msg = JText::_( 'Error Backing up Connections' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_friendmanager';
		$this->setRedirect($link, $msg);
	}// function

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function massfriend()
	{
		$model = $this->getModel('MassFriending');
		$backup =& $this->getModel('Backup');
		$friends =& $this->getModel('Friending');	

		if ($model->massUpdate($backup, $friends)) {
			$msg = JText::_( 'Mass Friending Completed' );
		} else {
			$msg = JText::_( 'Error while making everyone friends' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_friendmanager';
		$this->setRedirect($link, $msg);
	}// function

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function updatefriends()
	{
		$model = $this->getModel('MassFriending');
		$backup =& $this->getModel('Backup');
		$friends =& $this->getModel('Friending');

		if ($model->ffUpdate($backup, $friends)) {
			$msg = JText::_( 'All users updated with first friend' );
		} else {
			$msg = JText::_( 'Error Updating Users with First Friend' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_friendmanager';
		$this->setRedirect($link, $msg);
	}// function

	/**
	 * save a record (and redirect to main page)
	 * @return void
	 */
	function restore()
	{
		$model = $this->getModel('MassFriending');
		$backup =& $this->getModel('Backup');
		$friends =& $this->getModel('Friending');

		if ($model->ffUpdate($backup, $friends)) {
			$msg = JText::_( 'All users updated with first friend' );
		} else {
			$msg = JText::_( 'Error Updating Users with First Friend' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_friendmanager';
		$this->setRedirect($link, $msg);
	}// function	
	
}// class