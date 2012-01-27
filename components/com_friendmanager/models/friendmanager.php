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
class FriendManagerModelFriendManager extends JModel
{
	/**
	 * Gets the greetings
	 * @return string The greeting to be displayed to the user
	 */
	function getGreetings()
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT msg FROM #__community_connection';
		$db->setQuery( $query );
		$greetings = $db->loadObjectList();

		return $greetings;
	}// function
}// class
