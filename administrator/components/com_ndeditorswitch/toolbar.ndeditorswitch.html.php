<?php
/**
* @version		$Id: toolbar.ndeditorswitch.html.php 9 2008-09-26 10:30:11Z netdream $
* @package		NDEditorSwitch
* @subpackage	Component
* @copyright	Copyright (C) 2008 Netdream - Como,Italy. All rights reserved.
* @license		GNU/GPLv2
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Checkin
*/
class TOOLBAR_ndeditorswitch {
	/**
	* Draws the menu for a New category
	*/
	function _DEFAULT() {
		JToolBarHelper::title( JText::_( 'ND_EDITORSWITCH' ), 'ndlogo' );
		JToolBarHelper::help( 'index', 'true' );
	}
}