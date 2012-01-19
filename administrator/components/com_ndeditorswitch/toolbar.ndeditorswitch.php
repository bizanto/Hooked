<?php
/**
* @version		$Id: toolbar.ndeditorswitch.php 9 2008-09-26 10:30:11Z netdream $
* @package		NDEditorSwitch
* @subpackage	Component
* @copyright	Copyright (C) 2008 Netdream - Como,Italy. All rights reserved.
* @license		GNU/GPLv2
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JApplicationHelper::getPath( 'toolbar_html' ) );

switch ($task){
	default:
		TOOLBAR_ndeditorswitch::_DEFAULT();
		break;
}