<?php
/**
 * @version $Id$
 * @package    FriendManager
 * @author     Socialable Studios {@link http://www.Socialables.com}
 * @created     Created on 16-Jan-2010
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
 * The main uninstaller function
 */
function com_uninstall()
{
	$errors = false;
	
	//-- common images
	$img_OK = '<img src="images/publish_g.png" />';
	$img_WARN = '<img src="images/publish_y.png" />';
	$img_ERROR = '<img src="images/publish_r.png" />';
	$BR = '<br />';

	//--uninstall...

	$db = & JFactory::getDBO();

	$query = "DROP TABLE IF EXISTS `#__friendmanager`;";
	$db->setQuery($query);
	if( ! $db->query() )
	{
		echo $img_ERROR.JText::_('Unable to delete table').$BR;
		echo $db->getErrorMsg();
		return false;
	}

	if( $errors )
	{
		return false;
	}
	
	return true;
}// function