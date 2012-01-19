<?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');
jimport( 'joomla.utilities.arrayhelper');

class CommunityViewMemberList extends CommunityView
{
	public function display()
	{
		$id		= JRequest::getVar( 'listid' , '' );
		$list	=& JTable::getInstance( 'MemberList' , 'CTable' );
		$list->load( $id );
		
		if( empty( $list->id ) || is_null( $list->id ) )
		{
			echo JText::_('CC INVALID ID');
			return;
		}
		$document	=& JFactory::getDocument();
		
		$document->setTitle( $list->getTitle() );
		$tmpCriterias	= $list->getCriterias();
		$criterias		= array();
		
		foreach( $list->getCriterias() as $criteria )
		{
			$obj				= new stdClass();
			$obj->field			= $criteria->field;
			$obj->condition		= $criteria->condition;
			$obj->fieldType		= $criteria->type;
			
			switch( $criteria->type )
			{
				case 'date':
					if( $criteria->condition == 'between' )
					{
						$date		= explode( ',' , $criteria->value );
						$startDate	= explode( '/' , $date[0] );
						$endDate	= explode( '/' , $date[1] );
						$obj->value	= array( $startDate[2] . '-' . intval($startDate[1]) . '-' . $startDate[0] . ' 00:00:00',
											 $endDate[2] . '-' . intval($endDate[1]) . '-' . $endDate[0] . ' 23:59:59');
					}
					else
					{
						$startDate	= explode('/', $criteria->value );
						$obj->value	= $startDate[2] . '-' . intval($startDate[1]) . '-' . $startDate[0] . ' 00:00:00';
					}
				break;
				case 'checkbox':
				default:
					$obj->value			= $criteria->value;
				break;
			}
			
			
			$criterias[]		= $obj;
		}
		CFactory::load( 'helpers' , 'time');
		$created	=  CTimeHelper::getDate($list->created);
		
		CFactory::load( 'libraries' , 'advancesearch' );
		CFactory::load( 'libraries' , 'filterbar' );
		
		$sortItems	=  array(
							'latest' 	=> JText::_('CC SORT LATEST') , 
							'online'	=> JText::_('CC SORT ONLINE') ,
							'alphabetical'	=> JText::_('CC SORT ALPHABETICAL')
							);
		$sorting	= JRequest::getVar( 'sort' , 'latest' , 'GET' );
		$data		= CAdvanceSearch::getResult( $criterias , $list->condition , $list->avataronly , $sorting );

		$tmpl		= new CTemplate();
		$tmpl->set( 'list' 		, $list );
		$tmpl->set( 'created' 	, $created );
		$tmpl->set( 'sorting'	, CFilterBar::getHTML( CRoute::getURI(), $sortItems, 'latest') );
		
		$html		= $tmpl->fetch( 'memberlist.result' );
		unset( $tmpl );

		CFactory::load( 'libraries' , 'tooltip' );
		CFactory::load( 'helpers' , 'owner' );
		CFactory::load( 'libraries' , 'featured' );


		$featured		= new CFeatured( FEATURED_USERS );
		$featuredList	= $featured->getItemIds();
		$my				= CFactory::getUser();

		$resultRows = array();
		$friendsModel = CFactory::getModel('friends');
		
		CFactory::load( 'helpers' , 'friends' );
		foreach( $data->result as $user )
		{
			$obj				= new stdClass();
			$obj->user			= $user;
			$obj->friendsCount  = $user->getFriendCount();
			$obj->profileLink	= CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id );
			$isFriend =  CFriendsHelper::isConnected( $user->id, $my->id );
			
			$obj->addFriend 	= ((! $isFriend) && ($my->id != 0) && $my->id != $user->id) ? true : false;						
			
			$resultRows[] = $obj;
		}
				
		$tmpl		= new CTemplate();

		$tmpl->set( 'data' 		, $resultRows );
		$tmpl->set( 'sortings'	, '' );
		$tmpl->set( 'pagination', $data->pagination );
		$tmpl->set( 'filter' , '' );
		$tmpl->set( 'featuredList' , $featuredList);
		$tmpl->set( 'my' , $my );
		$tmpl->set( 'showFeaturedList' , false );
		$tmpl->set( 'isCommunityAdmin' , COwnerHelper::isCommunityAdmin() );

		$html		.= $tmpl->fetch('people.browse');
		echo $html;		
	}
}

