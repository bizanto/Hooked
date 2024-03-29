<?php
/**
 * @package     Advanced Module Manager
 * @version     1.16.1
 *
 * @author      Peter van Westen <peter@nonumber.nl>
 * @link        http://www.nonumber.nl
 * @copyright   Copyright © 2010 NoNumber! All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * BASE ON JOOMLA CORE FILE:
 * /administrator/components/com_modules/admin.modules.php
 */

/**
 * @version		$Id: admin.modules.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Make sure the user is authorized to view this page
$user =& JFactory::getUser();
if ( !$user->authorize( 'com_modules', 'manage' ) ) {
	$mainframe->redirect( 'index.php', JText::_( 'ALERTNOTAUTH' ) );
}

$lang =& JFactory::getLanguage();
$lang->load( 'com_modules', JPATH_ADMINISTRATOR );
// Loads English language file as fallback (for undefined stuff in other language file)
$lang->_load( JPATH_ADMINISTRATOR.DS.'language'.DS.'en-GB'.DS.'en-GB.com_advancedmodules.ini', 'com_advancedmodules', 0 );

jimport( 'joomla.filesystem.file' );

// return if NoNumber! Elements plugin is not installed
if ( !JFile::exists( JPATH_PLUGINS.DS.'system'.DS.'nonumberelements.php' ) ) {
	$mainframe->_messageQueue = array();
	$mainframe->enqueueMessage( JText::_( 'AMM_NONUMBER_ELEMENTS_PLUGIN_NOT_INSTALLED' ), 'error' );
	return;
}

// give notice if NoNumber! Elements plugin is not enabled
$nnep = JPluginHelper::getPlugin( 'system', 'nonumberelements' );
if ( !isset( $nnep->name ) ) {
	$mainframe->_messageQueue = array();
	$mainframe->enqueueMessage( JText::_( 'AMM_NONUMBER_ELEMENTS_PLUGIN_NOT_ENABLED' ), 'notice' );
}

// load the NoNumber! Elements language file
$lang->load( 'plg_system_nonumberelements', JPATH_ADMINISTRATOR );
// Loads English language file as fallback (for undefined stuff in other language file)
$lang->_load( JPATH_ADMINISTRATOR.DS.'language'.DS.'en-GB'.DS.'en-GB.plg_system_nonumberelements.ini', 'com_advancedmodules', 0 );

// Require the base controller
require_once JPATH_COMPONENT.DS.'controller.php';

// Create the controller
$controller	= new ModulesController( array( 'default_task' => 'view' ) );

// Perform the Request task
$controller->execute( JRequest::getCmd( 'task', 'view' ) );

// Redirect if set by the controller
$controller->redirect();

// Do the version check
require_once JPATH_PLUGINS.DS.'system'.DS.'nonumberelements'.DS.'helpers'.DS.'versions.php';
$versions = NNVersions::instance();
$version = '';
$xml = JApplicationHelper::parseXMLInstallFile( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_advancedmodules'.DS.'advancedmodules.xml' );
if ( $xml && isset( $xml['version'] ) ) {
	$version = $xml['version'];
}

echo '<p style="text-align:center;">'.JText::_( 'ADVANCED_MODULE_MANAGER' );
if ( $version ) {
	echo ' v'.$version;
}
echo ' - '.JText::_( 'COPYRIGHT' ).' (C) 2010 NoNumber! '.JText::_( 'ALL_RIGHTS_RESERVED' ).'</p>';

if ( JRequest::getCmd( 'task', 'view' ) == 'view' ) {
	$versions->setMessage( $version, 'advancedmodules', 'http://www.nonumber.nl/versions', 'http://www.nonumber.nl/advancedmodules/download' );
}