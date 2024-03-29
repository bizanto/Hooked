<?php
/**
 * @category	Core
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.controller' );

JTable::addIncludePath( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'tables' );

class CommunityControllerMemberlist extends CommunityController
{
	function __construct()
	{
		parent::__construct();		
	}
	
	function delete()
	{
		$data	= JRequest::getVar( 'cid' , '' , 'post' );
		$error	= array();
		$list	=& JTable::getInstance( 'Memberlist' , 'CTable' );
		
		if( !is_array( $data ) )
		{
			$data[]	= $data;
		}
		
		if( empty($data) )
		{
			JError::raiseError( '500' , JText::_('CC INVALID ID') );
		}
		
		foreach($data as $id)
		{
			$list->load( $id );
			
			if( !$list->delete() )
			{
				$error[]	= true;
			}
			
		}

		$mainframe	=& JFactory::getApplication();
		
		if( in_array( $error , true ) )
		{
			$mainframe->redirect( 'index.php?option=com_community&view=memberlist' , JText::_('CC ERROR REMOVING MEMBERLIST') , 'error' );
		}
		else
		{
			$mainframe->redirect( 'index.php?option=com_community&view=memberlist' , JText::_('CC MEMBERLIST DELETED') );
		}
	}
}