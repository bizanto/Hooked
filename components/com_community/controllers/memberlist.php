<?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'tooltip.php');

class CommunityMemberlistController extends CommunityBaseController
{
	public function display()
	{
		$document	=& JFactory::getDocument();
		$viewType	= $document->getType();
		$mainframe	=& JFactory::getApplication();
		$view = $this->getView('memberlist' , '' , $viewType);
		echo $view->get('display');
	}

	public function save()
	{
		if( !COwnerHelper::isCommunityAdmin() )
		{
			echo JText::_('CC RESTRICTED ACCESS');
			return;
		}
		$mainframe			=& JFactory::getApplication();
		$post				= JRequest::get('post');
		$table				=& JTable::getInstance( 'Memberlist' , 'CTable' );
		$table->bind( $post );
		$date				=& JFactory::getDate();
		$table->created		= $date->toMySQL();
		$table->store();
		
		if( empty( $table->title ) )
		{
			$mainframe->redirect( CRoute::_('index.php?option=com_community&view=memberlist', false ) , JText::_('CC MEMBERLIST TITLE EMPTY') , 'error');
		}

		if( empty( $table->description ) )
		{
			$mainframe->redirect( CRoute::_('index.php?option=com_community&view=memberlist', false ) , JText::_('CC MEMBERLIST DESCRIPTION EMPTY') , 'error');
		}
		
		$total	= JRequest::getVar( 'totalfilters' , '' , 'POST' );
		
		for( $i = 0; $i < $total; $i++ )
		{
			$filter	= JRequest::getVar( 'filter' . $i , '' , 'POST');
			
			if( !empty( $filter ) )
			{
				$filters	= explode( ',' , $filter , 4 );
				
				$field		= explode( '=' , $filters[0] , 2 );
				$condition	= explode( '=' , $filters[1] , 2 );
				$type		= explode( '=' , $filters[2] , 2 );
				$value		= explode( '=' , $filters[3] , 2 );
				
				$criteria	=& JTable::getInstance( 'MemberlistCriteria' , 'CTable' );
				$criteria->listid		= $table->id;
				$criteria->field		= $field[1];
				$criteria->value		= $value[1];
				$criteria->condition	= $condition[1];
				$criteria->type			= $type[1];
				
				$criteria->store();
			}
		}
		
		// Create the menu.
		CFactory::load( 'helpers' , 'menu' );
		$menu				=& JTable::getInstance( 'Menu' , 'JTable' );
		
		$menu->menutype		= JRequest::getWord( 'menutype' , '', 'POST' );
		$menu->name			= $table->title;
		$menu->alias		= JFilterOutput::stringURLSafe( $table->title );
		$menu->link			= 'index.php?option=com_community&view=memberlist&listid=' . $table->id;
		$menu->published	= 1;
		$menu->type			= 'component';
		$menu->ordering		= $menu->getNextOrder( 'menutype="' . $menu->menutype . '"');
		$menu->componentid	= CMenuHelper::getComponentId();
		$menu->access		= JRequest::getWord( 'access' , '', 'POST' );
		
		$menu->store();
		
		
		$mainframe->redirect( CRoute::_('index.php?option=com_community&view=memberlist&listid=' . $table->id , false ) , JText::_('CC MEMBERLIST CREATED') ); 
	}
	
	public function ajaxShowSaveForm()
	{
		CFactory::load( 'helpers' , 'owner' );
		require_once( JPATH_ROOT . DS .'administrator' . DS . 'components' . DS . 'com_menus' .DS.'helpers'.DS.'helper.php' );		

		if( !COwnerHelper::isCommunityAdmin() )
		{
			echo JText::_('CC RESTRICTED ACCESS');
			return;
		}
		
		$response	= new JAXResponse();
		$args		= func_get_args();
		
		if( !isset( $args[0] ) )
		{
			$response->addScriptCall( 'alert' , 'CC INVALID ID' );
			return $resopnse->sendResponse();
		}
		$condition	= $args[0];
		array_shift( $args );
		
		$avatarOnly	= $args[0];
		array_shift( $args );
		
		$filters	= $args; 

		$menuTypes	= MenusHelper::getMenuTypeList();
		$menuAccess	= new stdClass();
		$menuAccess->access	= 0;
		
		$tmpl	= new CTemplate();
		$tmpl->set( 'condition'	, $condition );
		$tmpl->set( 'menuTypes' , $menuTypes );
		$tmpl->set( 'menuAccess' , $menuAccess );
		$tmpl->set( 'avatarOnly' , $avatarOnly );
		$tmpl->set( 'filters' , $filters );
		
		$html	= $tmpl->fetch( 'ajax.memberlistform' );
				
		$response->addAssign('cwin_logo', 'innerHTML', JText::_('CC SEARCH FILTER') );
		$response->addAssign('cWindowContent', 'innerHTML', $html);

		$action		= '<button  class="button" onclick="cWindowHide();">' . JText::_('CC BUTTON CANCEL') . '</button>';
		$action		.= '<button  class="button" onclick="joms.memberlist.submit();">' . JText::_('CC BUTTON SAVE') . '</button>';
		$response->addScriptCall( 'cWindowActions' , $action );
		return $response->sendResponse();
	}	
}

