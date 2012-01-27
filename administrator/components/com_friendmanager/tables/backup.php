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


/**
 * FriendManager Table class
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class TableBackup extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	var $id = null;

	/**
	 * @var string
	 */
	var $backupdate = null;
	
	/**
	 * @var string
	 */
	var $msg = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function TableBackup(& $db) {
		parent::__construct('#__friendmanager', 'id', $db);
	}
}
?>