<?php
/**
 * @package		ActivityComment
 * @copyright	Copyright (C) 2009 - 2010 SocialCode. All rights reserved.
 * @license		GNU/GPL
 * JSUsernames is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses.
 */
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.archive' );

function com_install()
{
	$db =& JFactory::getDBO();
	
	$query  = 'delete from #__plugins WHERE `element`=' . $db->Quote( 'activitycomment') . ' and `folder`=' . $db->Quote( 'community');
	$db->setQuery( $query );
	$db->Query();

	JArchive::extract( JPATH_ROOT . DS .'administrator'.DS.'components'.DS.'com_activitycomment'.DS.'activitycomment.zip' , JPATH_PLUGINS.DS.'community');
	
	$language	= JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_activitycomment'.DS.'en-GB.plg_activitycomment.ini';
	
	JFile::copy( $language , JPATH_ROOT.DS.'administrator'.DS.'language'.DS.'en-GB'.DS.'en-GB.plg_activitycomment.ini');
	
	$query = "insert into #__plugins set name='JS Activity comments',element='activitycomment',folder='community',access='0',ordering='0',published='1'";
	$db->setQuery($query);
	$db->query();

	$content = "<div>Installation completed</div>";
	$content .= '<div>Please remember to run the <a href="index.php?option=com_activitycomment">patch first</a>.</div>';
	$content .= '<div><b>To Uninstall</b> you must first return to this page, and restore your template. You may then Uninstall the Plugin, then the Component. The reason - if you don\'t restore your template, after install your Community page could display incorrect</div>';
	$content .= '<div><b>If you are upgrading, please make sure to uninstall the plugin and component and then reinstall again with the component</b>,/div>';
	echo $content;
}