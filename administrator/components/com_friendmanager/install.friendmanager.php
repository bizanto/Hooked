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
 * Main installer
 */
function com_install()
{
	$errors = FALSE;
	
	//-- common images
	$img_OK = '<img src="images/publish_g.png" />';
	$img_WARN = '<img src="images/publish_y.png" />';
	$img_ERROR = '<img src="images/publish_r.png" />';
	$BR = '<br />';

	//--install...
	$db = & JFactory::getDBO();

			$query = "CREATE TABLE IF NOT EXISTS `#__friendmanager` (  ".
			  		 "`id` int(11) NOT NULL auto_increment, ".
					 "`backupdate` datetime NOT NULL, ".
					 "`msg` varchar(50) NOT NULL default '', ".			
					 "PRIMARY KEY  (`id`) ".
					 ")";	
	$db->setQuery($query);
	if( ! $db->query() )
	{
		echo $img_ERROR.JText::_('Unable to create table').$BR;
		echo $db->getErrorMsg();
		return FALSE;
	}
	
		// Install plg_geocode
	$plugin_installer = new JInstaller;
	if($plugin_installer->install(dirname(__FILE__).DS.'plg_firstfriend') )
	{
		echo "<br/><hr><h1>First Friend Plugin Installed and Published ".$img_OK."</h1><hr><br />";
	}	
	else
	{
		echo "<br/><hr><h1>First Friend Plugin Not Installed ".$img_ERROR."</h1><hr><br />";
	}

	// Enable plg_geocode
	$db->setQuery("UPDATE #__plugins SET published = 1 WHERE ".
		"name = 'FirstFriend' ");
	$db->query();
	
	return TRUE;
}// function