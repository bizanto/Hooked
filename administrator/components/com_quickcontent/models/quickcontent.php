<?php

/*
* Quickcontent Component for Joomla 1.5.x
* @version 1.0.1
* @Date 2009.08.11
* @copyright (C) 2009 Thomas Lengler
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* www.einszuzwei.de
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );


class QuickcontentModelQuickcontent extends JModel
{
	
	function __construct()
	{
		parent::__construct();

	}

	function &getData(){
		
		global  $mainframe,  $my;
		
		$lists = array();
		$lists['access'] = JHTML::_('list.accesslevel',  $row );
		$lists['created_by'] = JHTML::_('list.users',  'created_by', $my->id );
		
		return $lists;
	}
	
	
}