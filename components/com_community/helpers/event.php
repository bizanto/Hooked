<?php
/**
 * @category	Helper
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

abstract class CEventHelperHandler
{
	const PRIVACY_PUBLIC	= '0';
	const PRIVACY_MEMBERS	= '20';
	const PRIVACY_FRIENDS	= '30';
	const PRIVACY_PRIVATE	= '40';

	protected $model 	= '';
	protected $my		= '';
	protected $cid		= '';
	protected $event	= '';
	protected $url		= '';
	
	public function __construct( $event )
	{
		$this->my		= CFactory::getUser();
		$this->model	= CFactory::getModel( 'events' );
		$this->event	= $event;
		$this->url		= 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $this->event->id;
	}

	/**
	 * Returns the unique identifier for the event. E.g group's id.	 	 
	 **/	
	abstract public function getContentId();

	/**
	 * Returns the event url.	 	 
	 **/
	abstract public function getType();
	
	/**
	 * Sets the respective submenus in the view
	 **/
	abstract public function addSubmenus( $view );

	/**
	 * Determines whether the current event exists
	 **/	
	abstract public function exists();

	/**
	 * Determines whether the current user is allowed to browse the event or not.
	 **/
	abstract public function browsable();
	
	/**
	 * Determines whether the current user is allowed to create an event or not.
	 **/
	abstract public function creatable();
	
	/**
	 * Determines whether the current user is allowed to manage an event.
	 **/	 
	abstract public function manageable();
	
	/**
	 * Determines whether the current user is allowed to access an event or not.
	 **/
	abstract public function isAllowed();

	/**
	 * Returns a stdclass object for activity so that the event would be able to add it.
	 **/
	abstract public function getActivity( $command , $actor , $target , $content , $cid , $app );
	
	/**
	 * Retrieves the url for the specific event
	 **/
	abstract public function getFormattedLink( $raw , $xhtml = true , $external = false );
	
	/**
	 * Determines whether or not the current event is public or private
	 **/	 	
	abstract public function isPublic();

	/**
	 * Determines whether to show categories or not.
	 **/	
	abstract public function showCategories();

	/**
	 * Retrieves the redirect link after an event is ignored.
	 **/	
	abstract public function getIgnoreRedirectLink();
	
	/**
	 * Retrieves the events to be shown
	 **/
	abstract public function getContentTypes();

	/**
	 * Determines whether to show print event or not
	 **/	
	abstract public function showPrint();

	/**
	 * Determines whether to show event export or not
	 **/
	abstract public function showExport();
	
	/**
	 * Determines if the current event should display the privacy details
	 * 
	 * @return	bool 	Whether the current event requires privacy or not.	 	 
	 **/	 	
	public function hasPrivacy()
	{
		if( $this->getType() == CEventHelper::GROUP_TYPE )
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Determines if the current event should display the invitation details
	 * 	 
	 * @return	bool 	Whether the current event requires invitation
	 **/	
	public function hasInvitation()
	{
		if( $this->getType() == CEventHelper::GROUP_TYPE )
		{
			return false;
		}
		
		return true;
	}
	
	
}

class CEventGroupHelperHandler extends CEventHelperHandler
{
	public function __construct( $event )
	{
		$this->cid 		= JRequest::getInt( 'groupid' , '' , 'REQUEST' );
		$this->group	=& JTable::getInstance( 'Group' , 'CTable' );
		
		if( empty( $this->cid ) )
		{
			$this->cid	= $event->contentid;
		}
		$this->group->load( $this->cid );
		
		parent::__construct( $event );
	}
	
	public function showPrint()
	{
		return true;
	}
	
	public function showExport()
	{
		return true;
	}
	
	public function getContentTypes()
	{
		return CEventHelper::GROUP_TYPE;
	}
	
	public function getIgnoreRedirectLink()
	{
		return $this->getFormattedLink( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $this->group->id , false );
	}
	
	public function showCategories()
	{
		return false;
	}
	
	public function getActivity( $command , $actor , $target , $content , $cid , $app )
	{
		// Need to prepend groups. into the activity command as we might want to
		// give different points for specific title
		$command		= 'groups.' . $command;
		$title			= '';
		
		switch( $command )
		{
			case 'groups.events.create':
				$title	= JText::sprintf( 'CC ACTIVITIES GROUP NEW EVENT' , $this->event->title );
			break;
			case 'groups.events.join':
			case 'groups.events.attendence.attend':
				$title	= JText::sprintf('CC ACTIVITIES GROUP EVENT ATTEND' , $this->event->title);
			break;
			case 'groups.events.avatar.upload':
				$title	= JText::sprintf( 'CC ACTIVITIES GROUP NEW EVENT AVATAR' , $this->event->title );
			break;
			case 'groups.events.wall.create':
				$title	= JText::sprintf('CC ACTIVITIES GROUP WALL POST EVENT' , $this->event->title );
			break;
		}
		
		$act = new stdClass();
		$act->cmd 		= $command;
		$act->actor   	= $actor;
		$act->target  	= $target;
		$act->title	  	= $title;
		$act->content	= $content;
		$act->app		= $app;
		$act->cid		= $cid;
		
		return $act;
	}
	
	public function isPublic()
	{
		return $this->group->approvals	== COMMUNITY_PUBLIC_GROUP;
	}
	
	public function exists()
	{
		return $this->event->contentid == $this->cid && $this->event->id != 0;
	}
	
	public function creatable()
	{
		CFactory::load( 'helpers' , 'group' );
		
		return CGroupHelper::allowCreateEvent( $this->my->id , $this->cid );
	}
	
	public function manageable()
	{
		CFactory::load( 'helpers' , 'group' );
		
		return CGroupHelper::allowManageEvent( $this->my->id , $this->cid , $this->event->id );
	}
	
	public function getFormattedLink( $raw , $xhtml = true , $external = false , $route = true )
	{
		$raw	.= '&groupid=' . $this->cid;
		$url	= '';
		
		if( $external )
		{
			$url	= $route ? CRoute::getExternalURL( $raw , $xhtml ) : $raw;
		}
		else
		{
			$url	= $route ? CRoute::_( $raw , $xhtml ) : $raw; 
		}
		
		return $url;
	}
	
	public function browsable()
	{
		CFactory::load( 'helpers' , 'owner' );

		if( COwnerHelper::isCommunityAdmin() )
		{
			return true;
		}

		$group	=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $this->cid );
		$params	= $group->getParams();
		
		if( ( $group->approvals == COMMUNITY_PRIVATE_GROUP && !$group->isMember( $this->my->id ) ) ||  $params->get('eventpermission') == GROUP_EVENT_PERMISSION_DISABLE )
		{
			return false;
		}
		return true;
	}
	
	public function addSubmenus( $view )
	{
		$config			= CFactory::getConfig();
		$task			= JRequest::getVar( 'task' , '' , 'GET' );
		$showBackLink	= array( 'invitefriends', 'viewguest', 'uploadavatar' , 'edit' , 'sendmail', 'app');
		
		if( in_array( $task , $showBackLink ) )
		{
			// @rule: Add a link back to the event's page
			$view->addSubmenuItem( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $this->event->id . '&groupid=' . $this->cid , JText::_('CC BACK TO EVENT') );
		}
		// @rule: Add a link back to the group's page.
		$view->addSubmenuItem( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $this->cid , JText::_('CC BACK TO GROUP') );
	}
	
	public function getContentId()
	{
		return $this->cid;		
	}
	
	public function getType()
	{
		return CEventHelper::GROUP_TYPE;
	}
	
	public function isAllowed()
	{
		CFactory::load( 'helpers' , 'owner' );
		
		$group	=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $this->cid );

		return $group->isMember( $this->my->id ) || COwnerHelper::isCommunityAdmin();
	}
}

class CEventUserHelperHandler extends CEventHelperHandler
{
	public function __construct( $event )
	{
		parent::__construct( $event );
	}

	public function showPrint()
	{
		return $this->isAllowed();
	}
	
	public function showExport()
	{
		return $this->isAllowed();
	}
	
	public function getContentTypes()
	{
		return CEventHelper::ALL_TYPES;
	}
	
	public function showCategories()
	{
		return true;
	}

	public function getIgnoreRedirectLink()
	{
		return $this->getFormattedLink( 'index.php?option=com_community&view=events' , false );
	}
	
	public function getActivity( $command , $actor , $target , $content , $cid , $app )
	{
		$title			= '';

		switch( $command )
		{
			case 'events.create':
				$title	= JText::sprintf('CC ACTIVITIES NEW EVENT' , $this->event->title );
			break;
			case 'events.join':
			case 'events.attendence.attend':
				$title	= JText::sprintf( 'CC ACTIVITIES EVENT ATTEND' , $this->event->title );
			break;
			case 'events.avatar.upload':
				 $title	= JText::sprintf('CC ACTIVITIES NEW EVENT AVATAR' , $this->event->title );
			break;
			case 'events.wall.create':
				$title	= JText::sprintf('CC ACTIVITIES WALL POST EVENT' , $this->event->title );
			break;
		}

		$act = new stdClass();
		$act->cmd 		= $command;
		$act->actor   	= $actor;
		$act->target  	= $target;
		$act->title	  	= $title;
		$act->content	= $content;
		$act->app		= $app;
		$act->cid		= $cid;
		
		return $act;
	}

	public function isPublic()
	{
		return $this->event->permission == COMMUNITY_PUBLIC_EVENT;
	}
	
	public function browsable()
	{
		// Since we do not impose any restrictions on profile events, 
		// regardless of the event type, we don't really need to prevent this.
		return true;
	}
	
	public function creatable()
	{
		$config		= CFactory::getConfig();
		
		if( !$config->getBool('createevents') || $this->my->id == 0 )
		{
			return false;
		}
		return true;
	}
	
	public function exists()
	{
		return $this->event->id != 0;
	}
	
	public function manageable()
	{
		CFactory::load( 'helpers' , 'owner' );
		
		if( COwnerHelper::isCommunityAdmin() || $this->event->isCreator( $this->my->id ) )
		{
			return true;
		}
		return false;
	}
	
	public function getFormattedLink( $raw , $xhtml = true , $external = false , $route = true )
	{
		$url	= '';
		
		if( $external )
		{
			$url	= $route ? CRoute::getExternalURL( $raw , $xhtml ) : $raw;
		}
		else
		{
			$url	= $route ? CRoute::_( $raw , $xhtml ) : $raw;
		}
		return $url;
	}
	
	public function addSubmenus( $view )
	{
		$config		= CFactory::getConfig();
		$task	    = JRequest::getVar( 'task' , '' , 'GET' );
		$backLink	= array( 'invitefriends', 'viewguest', 'uploadavatar' , 'edit' , 'sendmail', 'app');
		
		if( in_array( $task , $backLink) )
		{

		    $eventid	= JRequest::getVar( 'eventid' , '' , 'GET' );

			$view->addSubmenuItem('index.php?option=com_community&view=events&task=viewevent&eventid=' . $eventid, JText::_('CC BACK TO EVENT'));
		}
		else
		{
    		$view->addSubmenuItem('index.php?option=com_community&view=events', JText::_('CC ALL EVENTS') );

			if( COwnerHelper::isRegisteredUser())
			{
				$view->addSubmenuItem('index.php?option=com_community&view=events&task=myevents&userid='. $this->my->id, JText::_('CC MY EVENTS'));
				$view->addSubmenuItem('index.php?option=com_community&view=events&task=myinvites&userid='. $this->my->id, JText::_('CC EVENT PENDING INVITATIONS'));
			}

			// Even guest should be able to view old events
			$view->addSubmenuItem('index.php?option=com_community&view=events&task=pastevents', JText::_('CC PAST EVENTS TITLE'));

			if( COwnerHelper::isRegisteredUser() && $config->get('createevents') || COwnerHelper::isCommunityAdmin() )
			{
				$view->addSubmenuItem('index.php?option=com_community&view=events&task=create', JText::_('CC CREATE EVENT') , '' , SUBMENU_RIGHT );
				
				if( $config->get('event_import_ical') )
				{
					$view->addSubmenuItem('index.php?option=com_community&view=events&task=import', JText::_('CC EVENTS IMPORT') , '' , SUBMENU_RIGHT );
				}
			}

			if( (!$config->get('enableguestsearchevents') && COwnerHelper::isRegisteredUser()  ) || $config->get('enableguestsearchevents') )
			{
				$tmpl = new CTemplate();
				$tmpl->set( 'url', CRoute::_('index.php?option=com_community&view=events&task=search') );
				$html = $tmpl->fetch( 'events.search.submenu' );

				$view->addSubmenuItem('index.php?option=com_community&view=events&task=search', JText::_('CC SEARCH EVENTS'), 'joms.events.toggleSearchSubmenu(this)', false, $html);
			}
		}
	}
	
	public function getContentId()
	{
		// Since profile based events will always use 0 as the content id
		return 0;
	}
	
	public function getType()
	{
		return CEventHelper::PROFILE_TYPE;
	}

	public function isAllowed()
	{
		CFactory::load( 'helpers' , 'owner' );
		
		$status	= $this->event->getUserStatus( $this->my->id );
		
		return ( ( ($status == COMMUNITY_EVENT_STATUS_INVITED)
				|| ($status == COMMUNITY_EVENT_STATUS_ATTEND)
				|| ($status == COMMUNITY_EVENT_STATUS_WONTATTEND)
				|| ($status == COMMUNITY_EVENT_STATUS_MAYBE)
				|| !$this->event->permission
				)
				&& ($status != COMMUNITY_EVENT_STATUS_BLOCKED)
				|| COwnerHelper::isCommunityAdmin() );
	}
}

class CEventHelper
{
	var $handler	= '';
	var $id			= '';
	const GROUP_TYPE	= 'group';
	const PROFILE_TYPE	= 'profile';
	const ALL_TYPES		= 'all';
	
	static public function getHandler( CTableEvent $event )
	{
		static $handler	= array();

		if( !isset( $handler[ $event->id ] ) )
		{
			// During AJAX calls, we might not be able to determine the groupid
			$defaultId	= ( $event ) ? $event->contentid : '';
			$groupId	= JRequest::getInt( 'groupid' , $defaultId , 'REQUEST' );
			
			if( !empty($groupId) )
			{
				$handler[ $event->id ]	= new CEventGroupHelperHandler( $event );
			}
			else
			{
				$handler[ $event->id ]	= new CEventUserHelperHandler( $event );
			}
		}
		
		return isset( $handler[ $event->id ] ) ? $handler[ $event->id ] : false;
	}
	
	/**
	 * Return true if the event is going on today
	 * An event is considered a 'today' event IF
	 * - starting date is today
	 * or
	 * - starting date if in the past but ending date is in the future	 	 	 	 
	 */	 	
	static public function isToday($event)
	{
		$startDate = CTimeHelper::getDate($event->startdate);
		$endDate = CTimeHelper::getDate($event->enddate);
		$now = CTimeHelper::getDate();
		
		// Same year, same day of the year
		$isToday = (
				($startDate->toFormat('%Y') == $now->toFormat('%Y')) 
			&& 	($startDate->toFormat('%j') == $now->toFormat('%j')));
		
		// If still not today, see if the event is ongoing now
		if(!$isToday)
		{
			$nowUnix = $now->toUnix();
			$isToday = (
					($startDate->toUnix() < $nowUnix)
				&&	($endDate->toUnix() > $nowUnix));
		}
		
		return $isToday;
	}

	/**
	 * Return true if the event is past
	 */
	static public function isPast( $event )
	{
		$endDate    =	CTimeHelper::getDate($event->enddate);
		$now	    =	CTimeHelper::getDate();

		$isPast	    =	(($endDate->toFormat('%Y') < $now->toFormat('%Y')) &&
				($endDate->toFormat('%j') < $now->toFormat('%j')));

		// If still not past
		if( !$isPast )
		{
			$nowUnix    =	$now->toUnix();
			$isPast	    =	( $endDate->toUnix() < $nowUnix );
		}

		return $isPast;
	}
	
	
	/**
	 * Return true if the event is going on this week
	 */	 	
	static public function isThisWeek($event)
	{
	}

	/**
	 * Returns formatted date for the event for the given format.
	 * 
	 * @param	CTableEvent	$event	The event table object.
	 * @param	String		$format	The date format.
	 * 
	 * @return	String	HTML value for the formatted date.
	 **/	 	 	 	 	 	 		
	static public function formatStartDate($event, $format)
	{
		$date		= JFactory::getDate( $event->startdate );
		$html		= $date->toFormat( $format );

		return $html;
	}
}