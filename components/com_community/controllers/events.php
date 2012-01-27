<?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Events Controller
 */
class CommunityEventsController extends CommunityBaseController
{
	protected $_disabledMessage	= '';

	/**
	 * Responsible to return necessary contents to the Invitation library
	 * so that it can add the mails into the queue
	 **/	 	 	
	public function inviteUsers( $cid , $users , $emails , $message )
	{
		CFactory::load( 'libraries' , 'invitation' );

		$model  		= CFactory::getModel( 'Events' );
		$event			=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $cid );

		$my				= CFactory::getUser();
		$mainframe		=& JFactory::getApplication();
		$status			= $event->getUserStatus($my->id);

		$invitedCount   = 0;

		foreach( $users as $id )
		{
			$date                       =& JFactory::getDate();
			$eventMember		    =& JTable::getInstance( 'EventMembers' , 'CTable' );
			$eventMember->eventid	    = $event->id;
			$eventMember->memberid	    = $id;
			$eventMember->status	    = COMMUNITY_EVENT_STATUS_INVITED;
			$eventMember->invited_by    = $my->id;
			$eventMember->created       = $date->toMySQL();

			$eventMember->store();
			$invitedCount++;
		}

		//now update the invited count in event
		$event->invitedcount = $event->invitedcount + $invitedCount;
		$event->store();

		// Send notification to the invited user.
		CFactory::load( 'libraries' , 'notification' );

		$params	= new JParameter( '' );
		$params->set('url' 			, CRoute::getExternalURL('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id ) );

		$htmlTemplate	= new CTemplate();
		$htmlTemplate->set( 'eventTitle', $event->title );
		$htmlTemplate->set( 'message' 	, $message );
		
		$html	= $htmlTemplate->fetch( 'email.events.invite.html' );
		
		$textTemplate	= new CTemplate();
		$htmlTemplate->set( 'eventTitle', $event->title );
		$textTemplate->set( 'message' 	, $message );
		$text	= $textTemplate->fetch( 'email.events.invite.text' );
		
		$inviteMail	= new CInvitationMail( $html , $text , JText::sprintf('CC INVITED TO JOIN EVENT' , $event->title ) , $params );
		$allowed	= array( COMMUNITY_EVENT_STATUS_INVITED , COMMUNITY_EVENT_STATUS_ATTEND , COMMUNITY_EVENT_STATUS_WONTATTEND , COMMUNITY_EVENT_STATUS_MAYBE );
		$accessAllowed	= ( ( in_array( $status , $allowed ) ) && $status != COMMUNITY_EVENT_STATUS_BLOCKED ) ? true : false;
		$accessAllowed	= COwnerHelper::isCommunityAdmin() ? true : $accessAllowed;

		CFactory::load( 'helpers' , 'event' );
		
		if( !($accessAllowed && $event->allowinvite) && !$event->isAdmin( $my->id ) )
		{
			$inviteMail->setError( JText::_('CC ACCESS FORBIDDEN') );
		}
		
		return $inviteMail;
	}
	
	public function __construct()
	{
		$this->_disabledMessage	= JText::_('CC EVENTS DISABLED');
	}
	
	public function editEventsWall( $wallId )
	{
		CFactory::load( 'helpers' , 'owner' );
		CFactory::load( 'helpers' , 'time');
		
		$wall			=& JTable::getInstance( 'Wall' , 'CTable' );
		$wall->load( $wallId );

		$event			=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $wall->contentid );

		$my				= CFactory::getUser();

		// @rule: We only allow editing of wall in 15 minutes
		$now		= JFactory::getDate();
		$interval	= CTimeHelper::timeIntervalDifference( $wall->date , $now->toMySQL() );
		$interval	= abs( $interval );
		
		if( ( $event->isCreator( $my->id) || $event->isAdmin($my->id) || COwnerHelper::isCommunityAdmin() || $my->id == $wall->post_by ) && ( COMMUNITY_WALLS_EDIT_INTERVAL > $interval ) )
		{
			return true;
		}
		return false;
	}

	/**
	 * Full application view
	 */
	public function app()
	{
		$view	=& $this->getView('events');

		echo $view->get( 'appFullView' );
	}

	public function ajaxDisplayNearbyEvents( $location )
	{
		$response	= new JAXResponse();

		$config	= CFactory::getConfig();

		$advance		    =   array();
		$advance['radius']	    =   $config->get('event_nearby_radius');
		$advance['fromlocation']    =   $location;

		$model	=&  CFactory::getModel( 'events' );
		$objs	=   $model->getEvents( null, null , null , null, null, null, null, $advance );

		$events	= array();

		$tmpl		= new CTemplate();

		if( $objs )
		{
			foreach( $objs as $row )
			{
				$event	    =& JTable::getInstance( 'Event' , 'CTable' );
				$event->bind( $row );
				$events[]   = $event;
			}
			unset($objs);
		}

		// Get list of nearby events
		$tmpl->set( 'events',	$events);
		$tmpl->set( 'radius',	$config->get('event_nearby_radius') );
		$tmpl->set( 'location', $location );
		$html	= $tmpl->fetch( 'events.nearbylist' );
		$response->addScriptCall('__callback', $html);
		
		return $response->sendResponse();
	}
	
	/**
	 *  Ajax function to prompt warning during group deletion
	 *
	 * @param	$groupId	The specific group id to unpublish
	 **/
	public function ajaxWarnEventDeletion( $eventId )
	{
		$response	= new JAXResponse();

		$title      = JText::_('CC DELETE EVENT');
		$content 	= JText::_('CC EVENT DELETION WARNING');
		$buttons	= '<input type="button" class="button" onclick="jax.call(\'community\', \'events,ajaxDeleteEvent\', \''.$eventId.'\', 1 );" value="' . JText::_('CC DELETE') . '"/>';
		$buttons   .= '<input type="button" class="button" onclick="cWindowHide();" value="' . JText::_('CC BUTTON CANCEL') . '"/>';

		$response->addAssign('cWindowContent', 'innerHTML' , $content);
		$response->addScriptCall( 'cWindowActions' , $buttons );
		$response->addAssign('cwin_logo', 'innerHTML', $title);

		return $response->sendResponse();
	}
	
	/**
	 * Ajax function to add new admin to the event
	 *
	 * @param memberid	Members id
	 * @param groupid	Eventid
	 *
	 **/
	public function ajaxAddAdmin( $memberId , $eventId )
	{
		$response	= new JAXResponse();

		$my			= CFactory::getUser();
		$model		=& $this->getModel( 'events' );
		$event		=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventId );
		
		CFactory::load( 'helpers' , 'event' );
		$handler		= CEventHelper::getHandler( $event );
		
		if( !$handler->manageable() )
		{
			$response->addScriptCall('joms.jQuery("#notice").html("' . JText::_('CC PERMISSION DENIED') . '");');
			$response->addScriptCall('joms.jQuery("#notice").attr("class","error");');
		}
		else
		{
			$member		=& JTable::getInstance( 'EventMembers' , 'CTable' );

			$member->load( $memberId , $event->id );
			$member->permission	= 2;
			$member->store();

			$response->addScriptCall('joms.jQuery("#member_' . $memberId . '").css("border","3px solid green");');
			$response->addScriptCall('joms.jQuery("#notice").html("' . JText::_('CC EVENT MEMBER MADE ADMIN') . '");');
			$response->addScriptCall('joms.jQuery("#notice").attr("class","info");');
		}

		return $response->sendResponse();
	}
	
	/**
	 * Ajax function to add new admin to the event
	 *
	 * @param memberid	Members id
	 * @param groupid	Eventid
	 *
	 **/
	public function ajaxRemoveAdmin( $memberId , $eventId )
	{
		$response	= new JAXResponse();

		$my			= CFactory::getUser();

		$model		=& $this->getModel( 'events' );
		$event		=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventId );

		CFactory::load( 'helpers' , 'event' );
		$handler		= CEventHelper::getHandler( $event );
		
		if( !$handler->manageable() )
		{
			$response->addScriptCall('joms.jQuery("#notice").html("' . JText::_('CC PERMISSION DENIED') . '");');
			$response->addScriptCall('joms.jQuery("#notice").attr("class","error");');
		}
		else
		{
			$member		=& JTable::getInstance( 'EventMembers' , 'CTable' );

			$member->load( $memberId , $event->id );
			$member->permission	= 3;
			$member->store();

			$response->addScriptCall('joms.jQuery("#member_' . $memberId . '").css("border","3px solid green");');
			$response->addScriptCall('joms.jQuery("#notice").html("' . JText::_('CC EVENT MEMBER MADE USER') . '");');
			$response->addScriptCall('joms.jQuery("#notice").attr("class","info");');
		}

		return $response->sendResponse();
	}
	
	/**
	 * Ajax function to display the join event
	 *
	 * @params $eventid	A string that determines the evnt id
	 **/
	public function ajaxRequestInvite( $eventId , $redirectUrl)
	{
		if (!COwnerHelper::isRegisteredUser())
		{
			return $this->ajaxBlockUnregister();
		}

		$response	= new JAXResponse();
		$my			= CFactory::getUser();
		$event		=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventId );

        $eventMembers	=& JTable::getInstance( 'EventMembers' , 'CTable' );
        $eventMembers->load($my->id, $eventId);

		$isMember	= $eventMembers->exists();
		$tmpl		= new CTemplate();
		$tmpl->set( 'isMember'	, $isMember );
		$tmpl->set( 'event'		, $event );
		$contents	= $tmpl->fetch( 'ajax.events.requestinvite' );
		$buttons	= '<input onclick="cWindowHide();" type="submit" value="' . JText::_('CC BUTTON CLOSE') . '" class="button" name="Submit"/>';

		if( !$isMember )
		{
			$buttons	= '<form name="jsform-events-ajaxrequestinvite" method="post" action="' . CRoute::_('index.php?option=com_community&view=events&task=requestInvite') . '">';
			$buttons	.= '<input type="submit" value="' . JText::_('CC BUTTON YES') . '" class="button" name="Submit"/>';
			$buttons	.= '<input type="hidden" value="' . $eventId . '" name="eventid" />';
			$buttons	.= '<input onclick="cWindowHide();" type="button" value="' . JText::_('CC BUTTON NO') . '" class="button" name="Submit" />';
			$buttons	.= '</form>';
		}
			
		// Change cWindow title
		$response->addAssign('cwin_logo', 'innerHTML', JText::_('CC EVENT REQUEST INVITATION TITLE'));
		$response->addAssign('cWindowContent' , 'innerHTML' , $contents);
		$response->addScriptCall('cWindowActions', $buttons );
		return $response->sendResponse();
	}
	
	/**
	 * A user decided to ignore this event. Once he 'ignore' an event. He
	 * cannot be invited or contacted by event admin
	 **/
    public function ajaxIgnoreEvent( $eventId )
    {
		$response	= new JAXResponse();

		$model		=& $this->getModel( 'events' );
		$my			=& JFactory::getUser();

		$event		=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventId );

		ob_start();
?>
		<div id="community-event-leave">
			<p><?php echo JText::sprintf( 'CC CONFIRM LEAVE EVENT' , $event->title ); ?></p>
		</div>
<?php
		$contents	= ob_get_contents();
		ob_end_clean();

		$buttons	= '<form name="jsform-events-ajaxignoreevent" method="post" action="' . CRoute::_('index.php?option=com_community&view=events&task=ignore') . '">';
		$buttons	.= '<input type="submit" value="' . JText::_('CC BUTTON YES') . '" class="button" name="Submit"/>';
		$buttons	.= '<input type="hidden" value="' . $eventId . '" name="eventid" />';
		$buttons	.= '<input onclick="cWindowHide();return false" type="button" value="' . JText::_('CC BUTTON NO') . '" class="button" name="Submit"/>';
		$buttons	.= '</form>';

		// Change cWindow title
		$response->addAssign('cwin_logo', 'innerHTML', JText::_('CC LEAVE EVENT TITLE'));
		$response->addAssign('cWindowContent' , 'innerHTML' , $contents);
		$response->addScriptCall('cWindowActions', $buttons);
		return $response->sendResponse();
    }

	/**
	 * Ajax function to approve a specific member when event admin or site admin tries to approve an invitation.
	 *
	 * @params	string	id	The member's id that needs to be approved.
	 * @params	string	groupid	The group id that the user is in.
	 **/
	public function ajaxApproveInvite( $memberId , $eventId )
	{
		$response	= new JAXResponse();

		$my			= CFactory::getUser();
		$model		= $this->getModel( 'events' );

		$event  	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventId );

		CFactory::load( 'helpers' , 'event' );
		$handler		= CEventHelper::getHandler( $event );
		
		if( !$handler->manageable() )
		{
			$response->addScriptCall( JText::_('CC NOT ALLOWED TO ACCESS SECTION') );
		}
		else
		{
			// Load required tables
			$member		=& JTable::getInstance( 'EventMembers' , 'CTable' );
			$member->load( $memberId , $eventId );

			$member->invite();
			$member->store();

			// Build the URL.
			$url	= CUrl::build( 'events' , 'viewevent' , array( 'eventid' => $event->id ) , true );
			$user	= CFactory::getUser( $memberId );

			$tmplData				= array();
			$tmplData['url']		= CRoute::getExternalURL('index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id, false);
			$tmplData['event']		= $event->title;
			$tmplData['user']		= $user->getDisplayName();
			$tmplData['approval']	= 1;

			// Send email to evnt member once their invitation is approved
			CFactory::load( 'libraries' , 'notification' );

			$params			= new JParameter( '' );
			$params->set('url' , CRoute::getExternalURL('index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id, false));
			$params->set('eventTitle' , $event->title );
			CNotificationLibrary::add( 'events.invitation.approved' , $event->creator , $user->id , JText::sprintf( 'CC EVENT INVITATION APPROVED EMAIL SUBJECT' , $event->title ) ,
							 	'' , 'events.invitation.approved' , $params );

			$response->addScriptCall('joms.jQuery("#member_' . $memberId . '").css("border","3px solid blue");');
			$response->addScriptCall('joms.jQuery("#notice").html("' . JText::_('CC INVITATION REQUEST APPROVED') . '");');
			$response->addScriptCall('joms.jQuery("#notice").attr("class","info");');
			$response->addScriptCall('joms.jQuery("#events-approve-' . $memberId . '").remove();');

		}

		return $response->sendResponse();
	}
	
	/**
	 *  Ajax function to delete a event
	 *
	 * @param	$eventId	The specific event id to unpublish
	 **/
	public function ajaxDeleteEvent( $eventId , $step=1 )
	{
		$response	= new JAXResponse();

		CFactory::load( 'libraries' , 'activities' );
		CFactory::load( 'helpers' , 'owner' );
		CFactory::load( 'models' , 'events' );

		$model	= CFactory::getModel( 'events' );
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventId );    

		$membersCount	= $event->getMembersCount('accepted');
		
		$my				= CFactory::getUser();
		$isMine			= ($my->id == $event->creator);

		CFactory::load( 'helpers' , 'event' );
		$handler		= CEventHelper::getHandler( $event );
		
		if( !$handler->manageable() )
		{
			$content = JText::_('CC NOT ALLOWED TO DELETE EVENT');
			$buttons  = '<input type="button" class="button" onclick="cWindowHide();" value="' . JText::_('CC CANCEL') . '"/>';
			$response->addScriptCall('cWindowResize', 100);
			$response->addAssign('cWindowContent', 'innerHTML' , $content);
			$response->addScriptCall('cWindowActions', $buttons);
			return $response->sendResponse();
		}
		
		// @rule: Do not allow event admin to delete event if there is less than 2 members
		if( $membersCount <= 1 && !COwnerHelper::isCommunityAdmin() )
		{
			$content = JText::_('CC NOT ALLOWED TO DELETE EVENT BECAUSE MEMBERS CRITERIA DOES NOT MATCH');
			$buttons  = '<input type="button" class="button" onclick="cWindowHide();" value="' . JText::_('CC CANCEL') . '"/>';
			$response->addScriptCall('cWindowResize', 100);
			$response->addAssign('cWindowContent', 'innerHTML' , $content);
			$response->addScriptCall('cWindowActions', $buttons);
			return $response->sendResponse();
		}

		$response->addScriptCall('cWindowResize', 160);

		$doneMessage	= ' - <span class=\'success\'>'.JText::_('CC DONE').'</span><br />';
		$failedMessage	= ' - <span class=\'failed\'>'.JText::_('CC FAILED').'</span><br />';

		switch($step)
		{
			case 1:
				// Nothing gets deleted yet. Just show a messge to the next step
				if( empty($eventId) )
				{
					$content = JText::_('CC INVALID EVENT ID');
				}
				else
				{
					$content	= '<strong>' . JText::sprintf( 'CC DELETING EVENT' , $event->title ) . '</strong><br/>';
					$content .= JText::_('CC DELETING EVENT MEMBERS');
					$response->addScriptCall('jax.call(\'community\', \'events,ajaxDeleteEvent\', \''.$eventId.'\', 2 );' );

					$this->triggerEvents( 'onBeforeEventDelete' , $event);
				}
				$response->addAssign('cWindowContent', 'innerHTML' , $content);
				break;
			case 2:
				// Delete all event members
				if($event->deleteAllMembers())
				{
					$content = $doneMessage;
				}
				else
				{
					$content = $failedMessage;
				}
				$content .= JText::_('CC DELETING EVENT WALLS');
				$response->addScriptCall('joms.jQuery("#cWindowContent").append("' . $content . '");' );
				$response->addScriptCall('jax.call(\'community\', \'events,ajaxDeleteEvent\', \''.$eventId.'\', 3 );' );
				break;
			case 3:
				// Delete all event wall
				if($event->deleteWalls())
				{
					$content = $doneMessage;
				}
				else
				{
					$content = $failedMessage;
				}
				$response->addScriptCall('joms.jQuery("#cWindowContent").append("' . $content . '");' );
				$response->addScriptCall('jax.call(\'community\', \'events,ajaxDeleteEvent\', \''.$eventId.'\', 4 );' );
				break;
			case 4:
				// Delete event master record
				$eventData = $event;

				if( $event->delete() )
				{

					jimport( 'joomla.filesystem.file' );

					if($eventData->avatar != "components/com_community/assets/eventAvatar.png" && !empty( $eventData->avatar ) )
					{
						$path = explode('/', $eventData->avatar);

						$file = JPATH_ROOT . DS . $path[0] . DS . $path[1] . DS . $path[2] .DS . $path[3];
						if(JFile::exists($file))
						{
							JFile::delete($file);
						}
					}
					
					if($eventData->thumb != "components/com_community/assets/event_thumb.png" && !empty( $eventData->avatar ) )
					{
						//$path = explode('/', $eventData->avatar);
						//$file = JPATH_ROOT . DS . $path[0] . DS . $path[1] . DS . $path[2] .DS . $path[3];
						$file	= JPATH_ROOT . DS . JString::str_ireplace('/', DS, $eventData->thumb);
						if(JFile::exists($file))
						{
							JFile::delete($file);
						}
					}

					$html	= '<div class=\"info\" style=\"display: none;\">' . JText::_('CC EVENT DELETED') . '</div>';
					$response->addScriptCall('joms.jQuery("' . $html . '").prependTo("#community-wrap").fadeIn();');
					$response->addScriptCall('joms.jQuery("#community-groups-wrap").fadeOut();');

					$content = JText::_('CC EVENT DELETED');

					//trigger for onGroupDelete
					$this->triggerEvents( 'onAfterEventDelete' , $eventData);

					// Remove from activity stream
					CActivityStream::remove('events', $eventId);
				}
				else
				{
					$content = JText::_('CC ERROR WHILE DELETING EVENT');
				}

				$redirectURL = CRoute::_('index.php?option=com_community&view=events&task=myevents&userid=' . $my->id);

				$buttons  = '<input type="button" class="button" id="eventDeleteDone" onclick="cWindowHide(); window.location=\''.$redirectURL.'\';" value="' . JText::_('CC BUTTON DONE') . '"/>';

				$response->addAssign('cWindowContent', 'innerHTML' , $content);
				$response->addScriptCall('cWindowActions', $buttons);
				$response->addScriptCall('cWindowResize', 100);
				break;
			default:
				break;
		}

		return $response->sendResponse();
	}

	/**
	 * Unblock this user for this event
	 */
	public function ajaxUnblockGuest($userid, $eventid)
	{
		$my = CFactory::getUser();

		CFactory::load('helpers', 'owner');

		$response	= new JAXResponse();

		// @todo: caller needs to be admin
		$model	= CFactory::getModel( 'events' );
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventid );

		// Make sure I am the group admin
		if($event->isAdmin($my->id))
		{
			// Make sure the user is not an admin
			if(COwnerHelper::isCommunityAdmin($userid))
			{
				// Should not require exact string since it should never
				// gets executed unless user try to inject ajax code
				$response->addAlert(JText::_('CC ACCESS FORBIDDEN'));
			}
			else
			{
				$guest	=& JTable::getInstance( 'EventMembers' , 'CTable' );
				$guest->load($userid, $eventid);

				$guest->status = COMMUNITY_EVENT_STATUS_MAYBE;
				$guest->store();

				// Update event stats count
				$event->updateGuestStats();
				$event->store();

				$header		=	JText::_('CC EVENTS UNBLOCK GUEST');
				$message    =   JText::_('CC EVENTS CONFIRM GUEST UNBLOCKED');

				$response->addAssign('cwin_logo', 'innerHTML', $header);
				$response->addAssign('cWindowContent', 'innerHTML', $message);

				$action		=	'<button  class="button" onclick="window.location.reload()">' . JText::_('CC BUTTON CLOSE') . '</button>';
				$response->addScriptCall( 'cWindowActions' , $action );
			}
		}
		else
        {
        	$response->addAlert(JText::_('CC ACCESS FORBIDDEN'));
		}

		return $response->sendResponse();
	}
	
	/**
	 * Block this user from this event
	 */	 	
	public function ajaxBlockGuest($userid, $eventid)
	{		
		$my = CFactory::getUser();
		
		CFactory::load('helpers', 'owner');
		
		$response	= new JAXResponse();
		
		// @todo: caller needs to be admin
		$model	= CFactory::getModel( 'events' );
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventid );
		
		// Make sure I am the group admin 
		if($event->isAdmin($userid) || COwnerHelper::isCommunityAdmin($my->id))
		{	
			$guest	=& JTable::getInstance( 'EventMembers' , 'CTable' );
	        $guest->load($userid, $eventid);

			// Set status to "BLOCKED"
	        $guest->status = COMMUNITY_EVENT_STATUS_BLOCKED;
	        $guest->store();
	
	        // Update event stats count
	        $event->updateGuestStats();
	        $event->store();
	        
	        $header		=	JText::_('CC EVENTS BLOCK GUEST');
			$message    =   JText::_('CC EVENTS CONFIRM GUEST BLOCKED');

			$response->addAssign('cwin_logo', 'innerHTML', $header);
			$response->addAssign('cWindowContent', 'innerHTML', $message);

			$action		=	'<button  class="button" onclick="window.location.reload()">' . JText::_('CC BUTTON CLOSE') . '</button>';
			$response->addScriptCall( 'cWindowActions' , $action );
        }
        else
        {
        	$response->addAssign('cWindowContent', 'innerHTML', JText::_('CC ACCESS FORBIDDEN'));
		}
		
        return $response->sendResponse();
	}

	/**
	 * AJAX remove user from event
	 *
	 */
	public function ajaxRemoveGuest($userid, $eventid){
		$my = CFactory::getUser();

		CFactory::load('helpers', 'owner');

		$response	= new JAXResponse();

		$model	= CFactory::getModel( 'events' );
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventid );

		// Site admin can remove guest
		// Event creator can remove guest
		// The guest himself can remove himself
		if($event->isAdmin($my->id) || COwnerHelper::isCommunityAdmin($my->id) || $my->id==$userid){
			// Delete guest from event
			$event->removeGuest($userid, $eventid);

			// Update event stats count
			$event->updateGuestStats();
			$event->store();

			$message    =   JText::_('CC EVENTS CONFIRM GUEST REMOVED');
		}else{
			$message    =	JText::_('CC ACCESS FORBIDDEN');
		}

		$header		=	JText::_('CC EVENTS REMOVE GUEST');

		$response->addAssign('cwin_logo', 'innerHTML', $header);
		$response->addAssign('cWindowContent', 'innerHTML', $message);

		$action		=	'<button  class="button" onclick="window.location.reload()">' . JText::_('CC BUTTON CLOSE') . '</button>';
		$response->addScriptCall( 'cWindowActions' , $action );

        return $response->sendResponse();
	}

	/**
	 *Ajax remove guest confirmation prompt
	 *
	 */
	public function ajaxConfirmRemoveGuest($userid, $eventid){

		$objResponse = new JAXResponse();

		// Get html
		$user = CFactory::getUser($userid);
		ob_start();
		?>
			<p><?php echo JText::sprintf('CC EVENTS CONFIRM REMOVE GUEST', $user->getDisplayName()); ?></p>
			<br/>
			<input type="checkbox" name="block"/><?php echo JText::_('CC ALSO BLOCK GUEST'); ?>
		<?php
		$html = ob_get_contents();
		ob_end_clean();

		// Get action
		ob_start();
		?>
			<button class="button" onclick="joms.events.removeGuest(<?php echo $userid; ?>, <?php echo $eventid; ?>)" name="yes">
				<?php echo JText::_('Yes'); ?>
			</button>

			<button class="button" onclick="cWindowHide();" name="no">
				<?php echo JText::_('No'); ?>
			</button>
		<?php
		$actions = ob_get_contents();
		ob_end_clean();

		$objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('CC EVENTS REMOVE GUEST'));
		$objResponse->addAssign('cWindowContent', 'innerHTML', $html);
		$objResponse->addScriptCall('cWindowActions', $actions);

        return $objResponse->sendResponse();
	}

	/**
	 *AJAX confirm block guest
	 *
	 */
	public function ajaxConfirmBlockGuest( $userid, $eventid ){
		$response	= new JAXResponse();

		$header		=	JText::_('CC EVENTS BLOCK GUEST');
		$message    =   JText::_('CC EVENTS CONFIRM BLOCK GUEST');

		$response->addAssign('cwin_logo', 'innerHTML', $header);
		$response->addAssign('cWindowContent', 'innerHTML', $message);

		$action		=	'<button  class="button" onclick="joms.events.blockGuest(' . $userid . ',' . $eventid . ');">' . JText::_('CC YES') . '</button>';
		$action		.=	'&nbsp;<button class="button" onclick="cWindowHide();">' . JText::_('CC NO') . '</button>';
		$response->addScriptCall( 'cWindowActions' , $action );

        return $response->sendResponse();
	}

	/**
	 *AJAX confirm unblock guest
	 *
	 */
	public function ajaxConfirmUnblockGuest($userid, $eventid){
		$response	= new JAXResponse();

		$header		=	JText::_('CC EVENTS UNBLOCK GUEST');
		$message    =   JText::_('CC EVENTS CONFIRM UNBLOCK GUEST');

		$response->addAssign('cwin_logo', 'innerHTML', $header);
		$response->addAssign('cWindowContent', 'innerHTML', $message);

		$action		=	'<button  class="button" onclick="joms.events.unblockGuest(' . $userid . ',' . $eventid . ');">' . JText::_('CC YES') . '</button>';
		$action		.=	'&nbsp;<button class="button" onclick="cWindowHide();">' . JText::_('CC NO') . '</button>';
		$response->addScriptCall( 'cWindowActions' , $action );

        return $response->sendResponse();
	}

	/**
	 * Ajax function to join an event invitation
	 *
	 **/
	public function ajaxJoinInvitation( $eventId )
	{
		if (!COwnerHelper::isRegisteredUser())
		{
			return $this->ajaxBlockUnregister();
		}

		$objResponse	=	new JAXResponse();
		$my				=	CFactory::getUser();

		// Get events model
		$model	=&	CFactory::getModel('events');
		$event	=&	JTable::getInstance( 'Event' , 'CTable' );

		// Check the event availability
		if($event->load( $eventId ))
		{
			$guest	=& JTable::getInstance( 'EventMembers' , 'CTable' );
			$guest->load($my->id, $event->id);

			// Set status to "CONFIRMED"
			$guest->status = COMMUNITY_EVENT_STATUS_ATTEND;
			$guest->store();

			// Update event stats count
			$event->updateGuestStats();
			$event->store();
			
			CFactory::load( 'helpers' , 'event' );
			$handler	= CEventHelper::getHandler( $event );
						
			// Activity stream purpose if the event is a public event
			if( $handler->isPublic() )
			{
				$actor			= $my->id;
				$target			= 0;
				$content		= '';
				$cid			= $event->id;
				$app			= 'events';
				$act			= $handler->getActivity( 'events.join' , $actor, $target , $content , $cid , $app );

			    $params 		= new JParameter('');
			    $action_str  	= 'events.join';
			    $params->set( 'eventid' , $event->id);
			    $params->set( 'action', $action_str );
			    $params->set( 'event_url', 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id);

			    // Add activity logging
			    CFactory::load ( 'libraries', 'activities' );
			    CActivityStream::add( $act, $params->toString() );
			}
			$url	= $handler->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id );
			$objResponse->addScriptCall( 'joms.jQuery("#events-invite-' . $event->id . '").html("<span class=\"community-invitation-message\">' . JText::sprintf('CC JOINED EVENT', $event->title, $url) . '</span>")');
		}
		else
		{
			$objResponse->addScriptCall( 'joms.jQuery("#events-invite-' . $event->id . '").html("<span class=\"community-invitation-message\">' . JText::_('CC EVENT NO LONGER EXIST') . '</span>")');
		}

		return $objResponse->sendResponse();
	}

	/**
	 * Ajax function to reject an event invitation
	 *
	 **/
	public function ajaxRejectInvitation( $eventId )
	{
		if (!COwnerHelper::isRegisteredUser()) {
			return $this->ajaxBlockUnregister();
		}

		$objResponse	=	new JAXResponse();
		$my				=	CFactory::getUser();

		// Get events model
		$model	=&	CFactory::getModel('events');
		$event	=&	JTable::getInstance( 'Event' , 'CTable' );

		// Check the event availability
		if($event->load( $eventId ))
		{
			$guest	=& JTable::getInstance( 'EventMembers' , 'CTable' );
			$guest->load($my->id, $event->id);

			// Set status to "REJECTED"
			$guest->status = COMMUNITY_EVENT_STATUS_WONTATTEND;
			$guest->store();

			// Update event stats count
			$event->updateGuestStats();
			$event->store();

			$url	=	CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id);

			$objResponse->addScriptCall( 'joms.jQuery("#events-invite-' . $event->id . '").html("<span class=\"community-invitation-message\">' . JText::sprintf('CC REJECT EVENT INVITATION', $event->title, $url) . '</span>")');
		}
		else
		{
			$objResponse->addScriptCall( 'joms.jQuery("#events-invite-' . $event->id . '").html("<span class=\"community-invitation-message\">' . JText::_('CC EVENT NO LONGER EXIST') . '</span>")');
		}

		return $objResponse->sendResponse();
	}
	
	/**
	 * Method is called from the reporting library. Function calls should be
	 * registered here.
	 *
	 * return	String	Message that will be displayed to user upon submission.
	 **/
	public function reportEvent( $link, $message , $eventId )
	{
		CFactory::load( 'libraries' , 'reporting' );
		
		$report = new CReportingLibrary();
		$report->createReport( JText::_('CC BAD EVENT') , $link , $message );

		$action					= new stdClass();
		$action->label			= 'Unpublish event';
		$action->method			= 'events,unpublishEvent';
		$action->parameters		= $eventId;
		$action->defaultAction	= true;

		$report->addActions( array( $action ) );

		return JText::_('CC REPORT SUBMITTED');
	}
	
	public function unpublishEvent( $eventId )
	{
		CFactory::load( 'models' , 'events' );

		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventId );
		$event->published	= '0';
		$event->store();

		return JText::_('CC EVENT IS UNPUBLISHED');
	}

	/**
	 * Displays the default events view
	 **/
	public function display()
	{
		$config	= CFactory::getConfig();

		if( !$config->get('enableevents') )
		{
			echo JText::_('CC EVENTS DISABLED');
			return;
		}
		
		$document 	=& JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
 		$view		=& $this->getView( $viewName , '' , $viewType);

 		echo $view->get( __FUNCTION__ );
	}
	
	public function export()
	{
		$config	= CFactory::getConfig();

		if( !$config->get('enableevents') )
		{
			echo JText::_('CC EVENTS DISABLED');
			return;
		}
		
		$document 	=& JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
 		$view		=& $this->getView( $viewName , '' , $viewType);
 		
 		$eventId    = JRequest::getInt('eventid', '0');
 		
 		$model		= $this->getModel( 'events' );
		$event		= JTable::getInstance( 'Event' , 'CTable' );
		$event->load($eventId);

 		echo $view->get( __FUNCTION__ , $event);
 		exit;
	}

	/**
	 * A user decided to ignore an event
	 * Banned used cannot be ignored	 
	 */	 	
    public function ignore()
    {
		$eventId	= JRequest::getInt('eventid' , '' , 'POST');
		CError::assert( $eventId , '' , '!empty' , __FILE__ , __LINE__ );

		$model		=& $this->getModel('events');
		$my			= CFactory::getUser();

		if( $my->id == 0 )
		{
			return $this->blockUnregister();
		}

        $eventMembers	=& JTable::getInstance( 'EventMembers' , 'CTable' );
        $eventMembers->load($my->id, $eventId);

        $message    = '';

        $event	=& JTable::getInstance( 'Event' , 'CTable' );
        $event->load($eventId);

        if($eventMembers->id != 0)
        {
            $eventMembers->status = COMMUNITY_EVENT_STATUS_IGNORE;
            $eventMembers->store();

            //now we need to update the events various count.
			$event->updateGuestStats();
            $event->store();
            $message    = JText::_('CC EVENTS IGNORE SUCCESS');
        }
        // Add user in ignore list


		CFactory::load( 'helpers' , 'event' );
		$handler		= CEventHelper::getHandler( $event );
		$mainframe =& JFactory::getApplication();
		$mainframe->redirect( $handler->getIgnoreRedirectLink() , $message );
    }
	
	/**
	 * Method is used to receive POST requests from specific user
	 * that wants to join a event
	 *
	 * @return	void
	 **/
	public function requestInvite()
	{
		$mainframe =& JFactory::getApplication();
		$eventId	= JRequest::getInt('eventid' , '' , 'POST');

		// Add assertion to the event id since it must be specified in the post request
		CError::assert( $eventId , '' , '!empty' , __FILE__ , __LINE__ );

		// Get the current user's object
		$my			= CFactory::getUser();

		if( $my->id == 0 )
		{
			return $this->blockUnregister();
		}

		// Load necessary tables
		$model	= CFactory::getModel('events');

        $event			=& JTable::getInstance( 'Event' , 'CTable' );
        $event->load($eventId);
        
        $eventMembers	=& JTable::getInstance( 'EventMembers' , 'CTable' );
        $eventMembers->load($my->id, $eventId);
		$isMember		= $eventMembers->exists();

		if( $isMember )
		{
			$url 	= CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid='.$eventId, false);
			$mainframe->redirect( $url , JText::_( 'CC ALREADY MEMBER OF EVENT' ) );
		}
		else
		{

			// Set the properties for the members table
			$eventMembers->eventid	= $event->id;
			$eventMembers->memberid	= $my->id;

			CFactory::load( 'helpers' , 'owner' );
			
			//required approval
			//$eventMembers->approval	= 1;

	 		//@todo: need to set the privileges
	 		$date   =& JFactory::getDate();
	 		$eventMembers->status			= COMMUNITY_EVENT_STATUS_REQUESTINVITE; // for now just set it to approve for the demo purpose
	 		$eventMembers->permission		= '3'; //always a member
	 		$eventMembers->created			= $date->toMySQL();

			// Get the owner data
			$owner	= CFactory::getUser( $event->creator );

			$store	= $eventMembers->store();

			// Add assertion if storing fails
			CError::assert( $store , true , 'eq' , __FILE__ , __LINE__ );

			// Build the URL.
			//$url	= CUrl::build( 'groups' , 'viewgroup' , array( 'groupid' => $group->id ) , true );
			$url 	= CRoute::getExternalURL('index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id, false);

			// Notify admin via email if user is unapproved or approved.
			// @todo: If user is not approve yet, display links to approve , reject
			$tmplData				= array();
			$tmplData['url']		= $url;
			$tmplData['event']		= $event->title;
			$tmplData['user']		= $my->getDisplayName();
			$tmplData['status']		= $eventMembers->status;

			//trigger for onGroupJoin
			$this->triggerEvents( 'onEventJoin' , $event , $my->id);


			$mainframe->redirect( $url , JText::_('CC EVENT INVITATION REQUEST SUCCESS') );
		}
	}
	
	public function myevents()
	{
		$document 	=& JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
 		$view		=& $this->getView( $viewName , '' , $viewType);

 		echo $view->get( __FUNCTION__ );	
	}

	public function myinvites()
	{
		$document 	=& JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
 		$view		=& $this->getView( $viewName , '' , $viewType);

 		echo $view->get( __FUNCTION__ );
	}
	
	public function pastevents()
	{
		$document 	= JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
 		$view		= $this->getView( $viewName , '' , $viewType);

 		echo $view->get( __FUNCTION__ );
	}
	
	public function saveImport()
	{
		$mainframe	= JFactory::getApplication();
		$events		= JRequest::getVar( 'events' , '' );
		$my			= CFactory::getUser();
		$config		= CFactory::getConfig();
		
		if( !empty( $events ) )
		{
			$errors	= array();
			
			foreach( $events as $index )
			{
				$event				=& JTable::getInstance( 'Event' , 'CTable' );
				$event->title		= JRequest::getString( 'event-' . $index . '-title' , '' );
				$event->startdate	= JRequest::getString( 'event-' . $index . '-startdate' , '' );
				$event->enddate		= JRequest::getString( 'event-' . $index . '-enddate' , '' );	
				$event->description	= JRequest::getString( 'event-' . $index . '-description' , '' );
				$event->catid		= JRequest::getString( 'event-' . $index . '-catid' , '' );
				$event->location	= JRequest::getString( 'event-' . $index . '-location' , '' );
				$event->allowinvite	= JRequest::getInt( 'event-' . $index . '-invite' );
				$event->creator		= $my->id;
				$error				= array();
				
				if( empty($event->title) )
				{
					$error[]		= JText::_('CC EVENTS TITLE ERROR');
				}
				
				if( empty($event->startdate) )
				{
					$error[]		= JText::_( 'CC EVENTS START DATE EMPTY' );
				}
				
				if( empty($event->enddate) )
				{
					$error[]		= JText::_('CC EVENTS END DATE EMPTY' );
				}
				
				if( empty($event->catid) )
				{
					$error[]		= JText::_( 'CC EVENTS CATEGORY NOT SELECTED' );
				}
				
				if( !empty($error) )
				{
					$errors[]	= $error;
				}

				//@rule: If event moderation is enabled, event should be unpublished by default
				$event->published	= $config->get('event_moderation') ? 0 : 1;
				$event->created		= JFactory::getDate()->toMySQL();
				
				// @rule: Only store event when no errors.
				if( empty( $error ) )
				{
					$event->store();

					// Since this is storing event, we also need to store the creator / admin
					// into the events members table
					$member				= JTable::getInstance( 'EventMembers' , 'CTable' );
					$member->eventid	= $event->id;
					$member->memberid	= $event->creator;
					$member->created	= JFactory::getDate()->toMySQL();
					
					// Creator should always be 1 as approved as they are the creator.
					$member->status		= COMMUNITY_EVENT_STATUS_ATTEND;
	
					// @todo: Setup required permissions in the future
					$member->permission	= '1';
	
					$member->store();
	
					// Increment the member count.
					$event->updateGuestStats();
					
					// Apparently the updateGuestStats does not store the item. Need to store it again.
					$event->store();
				}
			}

			if( !empty( $errors ) )
			{
				$errorMessage	= implode( ',' , $errors );
				
				$mainframe->redirect( CRoute::_('index.php?option=com_community&view=events&task=import' , false ) , $errorMessage , 'error' );
			}
			else
			{
				$message	= $config->get('event_moderation') ? JText::_('CC EVENTS IMPORT SUCCESS MODERATED') : JText::_('CC EVENTS IMPORT SUCCESS');
				
				$mainframe->redirect( CRoute::_('index.php?option=com_community&view=events&task=import' , false ) , $message );
			}
		}
		else
		{
			$mainframe->redirect( CRoute::_('index.php?option=com_community&view=events&task=import' , false ) , JText::_('CC EVENTS NOT SELECTED') , 'error' ); 
		}
	}
	
	/**
	 * Method to display import event form
	 * 	 
	 **/	 	
	public function import()
	{
		$document 	= JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
 		$view		= $this->getView( $viewName , '' , $viewType);
		$events		= array();
		$mainframe	=& JFactory::getApplication();
		$config		= CFactory::getConfig();
		
		if( !$config->get('event_import_ical') )
		{
			$mainframe->redirect( CRoute::_('index.php?option=com_community&view=events' , false ) , JText::_('CC EVENTS IMPORT DISABLED') , 'error' );
		}
		
		if( JRequest::getMethod() == 'POST' )
		{
			$type	= JRequest::getVar( 'type' , 'file' );
			$valid	= false;
			
			if( $type == 'file' )
			{
				$file		= JRequest::getVar( 'file' , '' , 'FILES' ); 
				$valid		= $file['type'] == 'text/calendar';
				$path		= $file['tmp_name'];
				
				if( $valid && JFile::exists( $path ) )
				{ 
					$contents	= JFile::read( $path );
				}
			}

			if( $type == 'url' )
			{
				CFactory::load( 'helpers' , 'remote' );
				$file		= JRequest::getVar( 'url' , '' );
				$contents	= CRemoteHelper::getContent( $file , true );
				preg_match( '/Content-Type: (.*)/im' , $contents , $matches );
				$valid		= isset( $matches[1] ) && JString::trim( $matches[1] ) == 'text/calendar';
				$contents	= preg_replace( '/(.*)Content-Type: text\/calendar\s/is' , '' , $contents );
			}
			
			CFactory::load( 'libraries' , 'ical' );
			$ical	= new CICal( $contents );

			if( $ical->init() && $valid )
			{
				$events	= $ical->getItems();	
			}
			else
			{
				$mainframe->redirect( CRoute::_('index.php?option=com_community&view=events&task=import' , false ) , JText::_('Unable to load .ics file') , 'error');
			}
		}
 		echo $view->get( __FUNCTION__ , $events );
	}
	
	public function selectEvents()
	{
		
	}
	
	public function create()
	{
		$document   =&	JFactory::getDocument();
		$mainframe  =&	JFactory::getApplication();
		$viewType   =	$document->getType();
 		$viewName   =	JRequest::getCmd( 'view', $this->getName() );
 		$view	    =&	$this->getView( $viewName , '' , $viewType);
		$eventId    =	JRequest::getInt('eventid', '0');
 		
 		$model	    =	$this->getModel( 'events' );
		$event	    =	JTable::getInstance( 'Event' , 'CTable' );
		
		CFactory::load( 'helpers' , 'event' );
		$handler    =	CEventHelper::getHandler( $event );

		$groupId    =	JRequest::getInt( 'groupid', '', 'REQUEST' );
		$my	    =   CFactory::getUser();
		$isBanned   =	false;

		if( $groupId )
		{
			$group	    =	JTable::getInstance( 'Group' , 'CTable' );
			$group->load( $groupId );

			// Check if the current user is banned from this group
			$isBanned   =   $group->isBanned( $my->id );
		}
		
		if( !$handler->creatable() || $isBanned )
		{
			echo JText::_('CC ACCESS FORBIDDEN');
			return;
		}
		
		if( JRequest::getVar('action', '', 'POST') == 'save')
		{
			$eid = $this->save($event);

			if($eid !== FALSE )
			{
				$mainframe = JFactory::getApplication();

				$event		= JTable::getInstance( 'Event' , 'CTable' );
				$event->load($eid);
				
				//trigger for onGroupCreate
				$this->triggerEvents( 'onEventCreate' , $event);

				CFactory::load( 'helpers' , 'event' );
				$url		= CEventHelper::getHandler( $event )->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id , false );
				$message	= JText::sprintf('CC EVENT CREATED NOTICE', $event->title );

				if( !$event->published )
				{
					$message	= JText::sprintf( 'CC EVENTS MODERATION NOTICE' , $event->title );					
				}
				$mainframe->redirect( $url , $message );
				return;
			}
		}

		/* Begin: COPY EVENT */
		if( !empty($eventId) )
		{
			$event->load($eventId);

			if( $my->id != $event->creator && $event->permission == COMMUNITY_PRIVATE_EVENT )
			{
				$url	    =	CEventHelper::getHandler( $event )->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id , false );
				$message    =	JText::_('CC EVENTS PRIVATE COPY ERROR');
				$mainframe->redirect( $url , $message );
				return;
			}

			// Consider system will create new id
			$event->id = null;
		}
		/* End: COPY EVENT */
		
 		echo $view->get( __FUNCTION__ , $event);	
	
	}
	
	/**
	 * Controller method responsible to display the edit task.
	 * 
	 * @return	none	 	  
	 **/	 	
	public function edit()
	{
		$document 	=& JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
 		$view		=& $this->getView( $viewName , '' , $viewType);
		$eventId	= JRequest::getInt('eventid', '0');
		$event		= JTable::getInstance( 'Event' , 'CTable' );
		$event->load($eventId);
		$my		= CFactory::getUser();

		CFactory::load( 'helpers' , 'event' );
		$handler	= CEventHelper::getHandler( $event );

		if( ! $handler->manageable() )
		{
			echo JText::_('CC RESTRICTED ACCESS');
			return;
		}

		if( JRequest::getMethod() == 'POST' )
		{
			$eid = $this->save($event);

			if($eid !== FALSE )
			{
				$mainframe = JFactory::getApplication();
				$event->load($eventId);

				//trigger for onGroupCreate
				$this->triggerEvents( 'onEventUpdate' , $event);

				$mainframe->redirect( $handler->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id , false ) , JText::sprintf('CC EVENT UPDATED NOTICE', $event->title ));
				return;
			}
		}

		echo $view->get( __FUNCTION__ , $event);
	}
	
	/**
	 * A new event has been created
	 */
	public function created()
	{
		$document 	= JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
		$view		= $this->getView( $viewName , '' , $viewType);

		echo $view->get( __FUNCTION__ );
	}
	
	/**
	 * Send an email announcement to members
	 */	 	
	public function announce(){
		$document 	= JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
		$view		= $this->getView( $viewName , '' , $viewType);
		$my			= CFactory::getUser();
		
		$eventId    = JRequest::getInt('eventid', '0');
		
		$model		= $this->getModel( 'events' );
		$event		= JTable::getInstance( 'Event' , 'CTable' );
		$event->load($eventId);
		if(!$event->isAdmin($my->id))
		{
			echo "no access";
			return;
		}
		
		echo $view->get( __FUNCTION__ , $event);
		
	}
	
	/**
	 * Method to save the group
	 * @return false if create fail, return the group id if create is successful
	 **/
	public function save(&$event)
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( JText::_( 'CC INVALID TOKEN' ) );

		$mainframe	= JFactory::getApplication();
		$document 	= JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
 		$view		= $this->getView( $viewName , '' , $viewType);

		if( JString::strtoupper(JRequest::getMethod()) != 'POST')
		{
			$view->addWarning( JText::_('CC PERMISSION DENIED'));
			return false;
		}

 		// Get my current data.
		$my			= CFactory::getUser();
		$validated	= true;
		$model		= $this->getModel( 'events' );

        $eventId    = JRequest::getInt('eventid', '0');
        $isNew		= ($eventId == '0') ? true : false;
		$postData	= JRequest::get( 'post' );
		
		//format startdate and eendate with time before we bind into event object
		if( isset( $postData['starttime-ampm'] ) && $postData['starttime-ampm'] == 'PM' && $postData['starttime-hour'] != 12 )
		{
			$postData['starttime-hour'] = $postData['starttime-hour']+12;
		}
		
		if( isset( $postData['endtime-ampm'] ) && $postData['endtime-ampm'] == 'PM' && $postData['endtime-hour'] != 12 )
		{
			$postData['endtime-hour'] = $postData['endtime-hour']+12;
		}
		
		$postData['startdate']  = $postData['startdate'] . ' ' . $postData['starttime-hour'].':'.$postData['starttime-min'].':00';
		$postData['enddate']  	= $postData['enddate'] . ' ' . $postData['endtime-hour'].':'.$postData['endtime-min'] . ':00';

		unset($postData['startdatetime']);
		unset($postData['enddatetime']);
		
		unset($postData['starttime-hour']);
		unset($postData['starttime-min']);
		unset($postData['starttime-ampm']);
		
		unset($postData['endtime-hour']);
		unset($postData['endtime-min']);
		unset($postData['endtime-ampm']);

		$event->load($eventId);
		$event->bind( $postData );
		
		$inputFilter = CFactory::getInputFilter(true);
		
		// Despite the bind, we would still need to capture RAW description
		$event->description = JRequest::getVar('description', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$event->description = $inputFilter->clean($event->description);
		
		// @rule: Test for emptyness
		if( empty( $event->title ) )
		{
			$validated = false;
			$mainframe->enqueueMessage( JText::_('CC EVENTS TITLE CANNOT BE EMPTY'), 'error');
		}
		
		if( empty( $event->location ) )
		{
			$validated	= false;
			$mainframe->enqueueMessage(JText::_('CC EVENTS LOCATION CANNOT BE EMPTY'), 'error');
		}		

		// @rule: Test if group exists
		if( $model->isEventExist( $event->title, $event->location , $eventId) )
		{
			$validated = false;
			$mainframe->enqueueMessage( JText::_('CC EVENTS TAKEN'), 'error');
		}

		// @rule: Description cannot be empty
		if( empty( $event->description ) )
		{
			$validated = false;
			$mainframe->enqueueMessage( JText::_('CC EVENTS DESCRIPTION CANNOT BE EMPTY'), 'error');
		}
		
		// @rule: Start date cannot be empty
		if( empty( $event->startdate ) )
		{
			$validated = false;
			$mainframe->enqueueMessage( JText::_('CC STARTDATE CANNOT BE EMPTY'), 'error');
		}
		
		// @rule: End date cannot be empty
		if( empty( $event->enddate ) )
		{
			$validated = false;
			$mainframe->enqueueMessage( JText::_('CC ENDATE CANNOT BE EMPTY'), 'error');
		}
		
		// @rule: Number of ticket must at least be 0
		if( Jstring::strlen( $event->ticket ) <= 0 )
		{
			$validated = false;
			$mainframe->enqueueMessage( JText::_('CC EVENTS TICKET CANNOT BE EMPTY'), 'error');
		}

		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'time.php');
		if(CTimeHelper::timeIntervalDifference($event->startdate, $event->enddate) > 0)
		{
			$validated = false;
			$mainframe->enqueueMessage( JText::_('CC EVENT STARTDATE GREATER THAN ENDDATE'), 'error');					
		}
		
		// @rule: Event must not end in the past
		$now = new JDate();
		$jConfig	= JFactory::getConfig();
		$now->setOffset( $jConfig->getValue('offset') + (- COMMUNITY_DAY_HOURS ) );

		if( CTimeHelper::timeIntervalDifference( $now->toMySQL( true ), $event->enddate) > 0)
		{
			$validated = false;
			$mainframe->enqueueMessage( JText::_('CC EVENT ENDDATE GREATER THAN NOW'), 'error');					
		}

		if($validated)
		{
			// If event offset is disabled, we need to set it to 0.
			if( $event->offset == null )
			{
				$event->offset	= 0;
			}
			
			// Set the default thumbnail and avatar for the event just in case
			// the user decides to skip this
			if($isNew)
			{
				$event->creator		= $my->id;
				$config				= CFactory::getConfig();
				
				//@rule: If event moderation is enabled, event should be unpublished by default
				$event->published	= $config->get('event_moderation') ? 0 : 1;
				$event->created		= JFactory::getDate()->toMySQL();
			}
			$event->store();

			if($isNew) //new event
			{
				CFactory::load( 'helpers' , 'event' );
				$handler			= CEventHelper::getHandler( $event );
				$event->contentid	= $handler->getContentId();
				$event->type		= $handler->getType();

				// Since this is storing event, we also need to store the creator / admin
				// into the events members table
				$member				= JTable::getInstance( 'EventMembers' , 'CTable' );
				$member->eventid	= $event->id;
				$member->memberid	= $event->creator;

				// Creator should always be 1 as approved as they are the creator.
				$member->status	= COMMUNITY_EVENT_STATUS_ATTEND;

				// @todo: Setup required permissions in the future
				$member->permission	= '1';

				$member->store();

				// Increment the member count
				$event->updateGuestStats();
				$event->store();

				CFactory::load( 'helpers' , 'event' );
				$handler	= CEventHelper::getHandler( $event );
							
				// Activity stream purpose if the event is a public event
				if( $handler->isPublic() )
				{
					$actor			= $my->id;
					$target			= 0;
					$content		= '';
					$cid			= $event->id;
					$app			= 'events';
					$act			= $handler->getActivity( 'events.create' , $actor, $target , $content , $cid , $app );
					$url			= $handler->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id , false , true , false );
					$params			= new JParameter('');
					$action_str  = 'events.create';
					$params->set( 'action', $action_str );
					$params->set( 'event_url', $url );
					
					// Add activity logging
					CFactory::load ( 'libraries', 'activities' );
					CActivityStream::add( $act, $params->toString() );
				}
				
				//add user points
				CFactory::load( 'libraries' , 'userpoints' );		
				CUserPoints::assignPoint($action_str);
				
			}
		
			$validated = $event->id;
		}

		return $validated;
	}
	
	public function printpopup()
	{
		$document 	= JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
 		$view		= $this->getView( $viewName , '' , $viewType);
		$id			= JRequest::getInt( 'eventid' , '' , 'REQUEST' );

		$event		=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $id );
		
		CFactory::load( 'helpers' , 'event' );
		$handler	= CEventHelper::getHandler( $event );
		
		if( !$handler->showPrint() )
		{
			echo JText::_('CC ACCESS FORBIDDEN');
			return;
		}
		
 		echo $view->get( __FUNCTION__ , $event);
 		exit;
	}

	/**
	 * Controller method responsible to display the sendmail task
	 * 
	 * @return	none
	 **/
	public function sendmail()
	{
		$document 	=   JFactory::getDocument();
		$viewType	=   $document->getType();
 		$viewName	=   JRequest::getCmd( 'view', $this->getName() );
 		$view		=   $this->getView( $viewName , '' , $viewType);
		$mainframe	=&  JFactory::getApplication();
		$id		=   JRequest::getInt( 'eventid' , '');
		$message	=   JRequest::getVar( 'message' , '' , 'post' , 'string' , JREQUEST_ALLOWRAW );
		$title		=   JRequest::getVar( 'title'	, '' );
		$my		=   CFactory::getUser();

		CFactory::load( 'helpers' , 'owner' );
		CFactory::load( 'models' , 'events' );
		$event		=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $id ); 
		
		CFactory::load( 'helpers' , 'event' );
		$handler	= CEventHelper::getHandler( $event );
		
		if( empty( $id ) || !$handler->manageable() )
		{
			echo JText::_('CC ACCESS FORBIDDEN');
			return;
		}

		if( JRequest::getMethod() == 'POST' )
		{
			// Check for request forgeries
			JRequest::checkToken() or jexit( JText::_( 'CC INVALID TOKEN' ) );

			$members	= $event->getMembers( COMMUNITY_EVENT_STATUS_ATTEND , null  );

			$errors	= false;
	
			if( empty( $message ) )
			{
				$errors	= true;
				$mainframe->enqueueMessage( JText::_( 'CC MESSAGE REQUIRED' ) , 'error' );
			}
			
			if( empty( $title ) )
			{
				$errors	= true;
				$mainframe->enqueueMessage( JText::_( 'CC TITLE REQUIRED' ) , 'error' );
			}
			
			if( !$errors )
			{
				// Add notification
				CFactory::load( 'libraries' , 'notification' );			
				$emails		= array();
				
				foreach( $members as $member )
				{
					$user		= CFactory::getUser( $member->id );

					// Do not sent email notification to self
					if( $my->id != $user->id )
					{
						$total	    +=	1;
						$emails[]   =	$user->id;
					}
				}
				
				$params		= new JParameter( '' );
				$params->set( 'url'		, $handler->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id , false , true) );
				$params->set( 'title'	, $title );
				$params->set( 'message' , $message );
				CNotificationLibrary::add( 'events.sendmail' , $my->id , $emails , JText::sprintf( 'CC EVENT SENDMAIL SUBJECT' , $event->title ) , '' , 'events.sendmail' , $params );
				
				$mainframe->redirect( $handler->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id , false ) , JText::sprintf('CC EMAIL SENT TO PARTICIPANTS' , $total ) );
			}
		}

 		echo $view->get( __FUNCTION__ );
	}
	
	public function viewevent()
	{
		$document 	= JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
 		$view		= $this->getView( $viewName , '' , $viewType);

 		echo $view->get( __FUNCTION__ );	

	}
	
	public function viewguest()
	{
		
		$mainframe 	= JFactory::getApplication();
		$document 	= JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
 		$view		= $this->getView( $viewName , '' , $viewType);
 		$my			= CFactory::getUser();
 		
 		$eventId	= JRequest::getInt( 'eventid' , '' , 'REQUEST' );
		$listype	= JRequest::getVar( 'type' , '' , 'REQUEST' );
		
		$model  =& $this->getModel( 'events' );
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventId );
		CFactory::load( 'helpers' , 'owner' );
		CFactory::load( 'helpers' , 'event' );
		$handler	= CEventHelper::getHandler( $event );
		
		// Restrict view of specific usertype to group admin only
		if( ($listype == COMMUNITY_EVENT_STATUS_BLOCKED
			|| $listype == COMMUNITY_EVENT_STATUS_REQUESTINVITE
			|| $listype == COMMUNITY_EVENT_STATUS_IGNORE)
			&& !$handler->manageable() )
		{
			echo JText::_('CC ACCESS FORBIDDEN');
			return;
		}
		
		// If an event is a secret event, non-invited user should not be able 
		// to view it (can only view admins)
		if( !$handler->isPublic() )
		{
			$myStatus = $event->getUserStatus($my->id); 
			if($listype != 'admins' && ( !$handler->manageable() 
				&& ( $myStatus != COMMUNITY_EVENT_STATUS_INVITED)
				&& ( $myStatus != COMMUNITY_EVENT_STATUS_ATTEND)
				&& ( $myStatus != COMMUNITY_EVENT_STATUS_WONTATTEND)
				&& ( $myStatus != COMMUNITY_EVENT_STATUS_MAYBE)
				)
			)	
			{
				$mainframe->redirect( $handler->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id , false ) , JText::_( 'CC PRIVATE EVENT NOTICE' ) , 'error' );
				return;
			}
		}

 		echo $view->get( __FUNCTION__ );
	}

	/**
	 * Show Invite
	 */
	public function invitefriends()
	{
		$document 	=& JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
		$view		=& $this->getView( $viewName , '' , $viewType);
		$my			= CFactory::getUser();

		$invited		= JRequest::getVar( 'invite-list' , '' , 'POST' );
		$inviteMessage	= JRequest::getVar( 'invite-message' , '' , 'POST' );
		$eventId		= JRequest::getInt( 'eventid' , '' , 'REQUEST' );

		$model  =& $this->getModel( 'events' );
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventId );

		if( $my->id == 0 )
		{
			return $this->blockUnregister();
		}

		$status		=   $event->getUserStatus($my->id);
		$allowed	=   array( COMMUNITY_EVENT_STATUS_INVITED , COMMUNITY_EVENT_STATUS_ATTEND , COMMUNITY_EVENT_STATUS_WONTATTEND , COMMUNITY_EVENT_STATUS_MAYBE );
		$accessAllowed	=   ( ( in_array( $status , $allowed ) ) && $status != COMMUNITY_EVENT_STATUS_BLOCKED ) ? true : false;
		$accessAllowed	=   COwnerHelper::isCommunityAdmin() ? true : $accessAllowed;

		if( !($accessAllowed && $event->allowinvite) && !$event->isAdmin( $my->id ) )
		{
			echo JText::_('CC ACCESS FORBIDDEN');
			return;
		}

		if( JRequest::getMethod() == 'POST' )
		{
			// Check for request forgeries
			JRequest::checkToken() or jexit( JText::_( 'CC INVALID TOKEN' ) );

			if( !empty($invited ) )
			{
				$mainframe	=&  JFactory::getApplication();
				$invitedCount   =   0;

				foreach( $invited as $invitedUserId )
				{
					$date			    =&  JFactory::getDate();
					$eventMember		    =&  JTable::getInstance( 'EventMembers' , 'CTable' );
					$eventMember->eventid	    =   $event->id;
					$eventMember->memberid	    =   $invitedUserId;
					$eventMember->status	    =   COMMUNITY_EVENT_STATUS_INVITED;
					$eventMember->invited_by    =	$my->id;
					$eventMember->created       =	$date->toMySQL();
					$eventMember->store();
					$invitedCount++;
				}

                //now update the invited count in event
                $event->invitedcount = $event->invitedcount + $invitedCount;
                $event->store();

				// Send notification to the invited user.
				CFactory::load( 'libraries' , 'notification' );
	
				$params	    =	new JParameter( '' );
				$params->set('url' , CRoute::getExternalURL('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id ) );
				$params->set('eventTitle' , $event->title );
				$params->set('message' , $inviteMessage );
				CNotificationLibrary::add( 'events.invite' , $my->id , $invited ,JText::sprintf('CC INVITED TO JOIN EVENT' , $event->title ) , '' , 'events.invite' , $params );
				
				$view->addInfo(JText::_( 'CC EVENT INVITATIONS SENT' ));
			}
			else
			{
				$view->addWarning( JText::_('CC INVITE NEED AT LEAST 1 FRIEND') );
			}
		}
		echo $view->get( __FUNCTION__ );
	}

	/**
	 * Responsible to update a specific user's rsvp
	 **/	 	
	public function updatestatus()
	{
		//return;
		

		CFactory::load('helpers' , 'friends');

		$mainframe	=& JFactory::getApplication();
		$my			= CFactory::getUser();

		$memberid	= JRequest::getInt( 'memberid' , '' , 'POST' );
		$eventId	= JRequest::getInt( 'eventid' , '' , 'REQUEST' );
		$status		= JRequest::getInt( 'status' , '' , 'REQUEST' );
		
		if( $my->id == 0 )
		{
			return $this->blockUnregister();
		}
		
		// Check for request forgeries
		// This need to be below id test to make sure login is properly processe
		JRequest::checkToken() or jexit( JText::_( 'CC INVALID TOKEN' ) . 'EVENTS' );
		
		$model  =& $this->getModel( 'events' );
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventId );

		CFactory::load( 'helpers' , 'event' );
		$handler	= CEventHelper::getHandler( $event );
		
		if( !$handler->isAllowed() )
		{
			echo JText::_('CC ACCESS FORBIDDEN');
			return;
		}

		// If a number of ticket is specified and adding one more person exceed
		// the ticket number, we have to decline them
		if( ($event->ticket) && (($status == COMMUNITY_EVENT_STATUS_ATTEND ) && ($event->confirmedcount + 1) > $event->ticket) )
		{
			$mainframe->redirect( $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id , false ), JText::_('CC EVENTS TICKET FULL') );
			return;
		}

		$eventMember    =& JTable::getInstance( 'EventMembers' , 'CTable' );
		$eventMember->load($memberid, $eventId);

		$date   =& JFactory::getDate();

		if($eventMember->permission != 1 && $eventMember->permission != 2)
		{
			//always a member
			$eventMember->permission		= '3';
		}
		
		$eventMember->created			= $date->toMySQL();
		$eventMember->status    = $status;
		$eventMember->store();

		$event->updateGuestStats();
		$event->store();

		//activities stream goes here.
		$url			= $handler->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id , false );
		$statustxt		= JText::_('CC EVENT ACTION DECLINE');

		if($status == COMMUNITY_EVENT_STATUS_ATTEND)
		{
			$statustxt = JText::_('CC EVENT ACTION ATTEND');
		}
		
		if($status == COMMUNITY_EVENT_STATUS_MAYBE)
		{
			$statustxt = JText::_('CC EVENT ACTION UNSURE');
		}

		CFactory::load( 'helpers' , 'event' );
		$handler	= CEventHelper::getHandler( $event );

		// We update the activity only if a user attend an event and the event was set to public event
		if( $status == COMMUNITY_EVENT_STATUS_ATTEND && $handler->isPublic() )
		{
			$command		= 'events.attendence.attend';
			$actor			= $my->id;
			$target			= 0;
			$content		= '';
			$cid			= $event->id;
			$app			= 'events';
			$act			= $handler->getActivity( $command , $actor, $target , $content , $cid , $app );
			
			$params 		= new JParameter('');
			$action_str  	= 'events.attendence.attend';
			$params->set( 'eventid' , $event->id);
			$params->set( 'action', $action_str );
			$params->set( 'event_url', $handler->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id , false , true , false ) );

			// Add activity logging
			CFactory::load ( 'libraries', 'activities' );
			CActivityStream::add( $act, $params->toString() );
		}

		//trigger goes here.
		CFactory::load('libraries', 'apps' );
		$appsLib	=& CAppPlugins::getInstance();
		$appsLib->loadApplications();

		$params		= array();
		$params[]	= &$event;
		$params[]	= $my->id;
		$params[]	= $status;

		if(!is_null($target))
			$params[]	= $target;

		$mainframe->redirect( $handler->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id , false ), JText::_('CC EVENTS RESPONSE UPDATED') );

	}

	public function search()
	{
		$config 	=&  CFactory::getConfig();
		$mainframe 	=&  JFactory::getApplication();
		$my		=&  JFactory::getUser();

		if( $my->id == 0 && !$config->get('enableguestsearchevents') )
		{
			$mainframe->enqueueMessage( JText::_('CC RESTRICTED ACCESS'), 'notice');
			return $this->blockUnregister();
		}

		$document 	=& JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
 		$view		=& $this->getView( $viewName , '' , $viewType);

 		echo $view->get( __FUNCTION__ );	
	
	}
	
	public function uploadAvatar()
	{
		$mainframe	=& JFactory::getApplication();
		$document 	=& JFactory::getDocument();
		$viewType	= $document->getType();
 		$viewName	= JRequest::getCmd( 'view', $this->getName() );
		$view		=& $this->getView( $viewName , '' , $viewType);

		$eventid	= JRequest::getInt('eventid' , '0' , 'REQUEST');

		$model	=& $this->getModel( 'events' );
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventid );
		
		CFactory::load( 'helpers' , 'event' );
		$handler	= CEventHelper::getHandler( $event );

		if( !$handler->manageable() )
		{
			echo JText::_('CC ACCESS FORBIDDEN');
			return;
		}

		if( JRequest::getMethod() == 'POST' )
		{
			JRequest::checkToken() or jexit( JText::_( 'CC INVALID TOKEN' ) );
			
			CFactory::load( 'libraries' , 'apps' );
			
			$my			= CFactory::getUser();
			$config		= CFactory::getConfig();
			$appsLib	=& CAppPlugins::getInstance();
			$saveSuccess	= $appsLib->triggerEvent( 'onFormSave' , array( 'jsform-events-uploadavatar' ) );
			
			if( empty($saveSuccess) || !in_array( false , $saveSuccess ) )
			{
				CFactory::load( 'helpers' , 'image' );
	
				$file		= JRequest::getVar('filedata' , '' , 'FILES' , 'array');

                if( !CImageHelper::isValidType( $file['type'] ) )
				{
					$mainframe->enqueueMessage( JText::_('CC IMAGE FILE NOT SUPPORTED') , 'error' );
					$mainframe->redirect( $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id . '&task=uploadAvatar', false) );
            	}
            	
				if( empty( $file ) )
				{
					$mainframe->enqueueMessage(JText::_('CC NO POST DATA'), 'error');
				}
				else
				{
	
					$uploadLimit	= (double) $config->get('maxuploadsize');
					$uploadLimit	= ( $uploadLimit * 1024 * 1024 );
	
					// @rule: Limit image size based on the maximum upload allowed.
					if( filesize( $file['tmp_name'] ) > $uploadLimit && $uploadLimit != 0 )
					{
						$mainframe->enqueueMessage( JText::_('CC IMAGE FILE SIZE EXCEEDED') , 'error' );
						$mainframe->redirect( $handler->getFormattedLink('index.php?option=com_community&view=events&task=uploadavatar&eventid=' . $event->id , false) );
					}
	
					if( !CImageHelper::isValid($file['tmp_name'] ) )
					{
						$mainframe->enqueueMessage( JText::_('CC IMAGE FILE NOT SUPPORTED') , 'error');
						$mainframe->redirect( $handler->getFormattedLink('index.php?option=com_community&view=events&task=uploadavatar&eventid=' . $event->id , false) );
					}
					else
					{
						// @todo: configurable width?
						$imageMaxWidth	= 160;
	
						// Get a hash for the file name.
						$fileName		= JUtility::getHash( $file['tmp_name'] . time() );
						$hashFileName	= JString::substr( $fileName , 0 , 24 );
	
						// @todo: configurable path for avatar storage?
						$storage			= JPATH_ROOT . DS . $config->getString('imagefolder') . DS . 'avatar' . DS . 'events';
						$storageImage		= $storage . DS . $hashFileName . CImageHelper::getExtension( $file['type'] );
						$image				= $config->getString('imagefolder'). '/avatar/events/' . $hashFileName . CImageHelper::getExtension( $file['type'] );
						
						$storageThumbnail	= $storage . DS . 'thumb_' . $hashFileName . CImageHelper::getExtension( $file['type'] );
						$thumbnail			= $config->getString('imagefolder'). '/avatar/events/' . 'thumb_' . $hashFileName . CImageHelper::getExtension( $file['type'] );
						
						// Generate full image
						if(!CImageHelper::resizeProportional( $file['tmp_name'] , $storageImage , $file['type'] , $imageMaxWidth ) )
						{
							$mainframe->enqueueMessage(JText::sprintf('CC ERROR MOVING UPLOADED FILE' , $storageImage), 'error');
							$mainframe->redirect( CRoute::_('index.php?option=com_community&view=events&task=uploadavatar&eventid=' . $event->id , false) );
						}
						
						// Generate thumbnail
						if(!CImageHelper::createThumb( $file['tmp_name'] , $storageThumbnail , $file['type']  ) )
						{
							$mainframe->enqueueMessage(JText::sprintf('CC ERROR MOVING UPLOADED FILE' , $storageImage), 'error');
							$mainframe->redirect( CRoute::_('index.php?option=com_community&view=events&task=uploadavatar&eventid=' . $event->id , false) );
						}
	
						// Update the event with the new image
						$event->setImage( $image , 'avatar' );
						$event->setImage( $thumbnail , 'thumb' );

						CFactory::load( 'helpers' , 'event' );
						$handler	= CEventHelper::getHandler( $event );
						
						if( $handler->isPublic() )
						{
							$actor			= $my->id;
							$target			= 0;
							$content		= '<img class="event-thumb" src="' . rtrim( JURI::root() , '/' ) . '/' . $image . '" style="border: 1px solid #eee;margin-right: 3px;" />';
							$cid			= $event->id;
							$app			= 'events';
							$act			= $handler->getActivity( 'events.avatar.upload' , $actor, $target , $content , $cid , $app );
										
							$params = new JParameter();
							$params->set('event_url', $handler->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id , false , true , false ) );
		
							CFactory::load ( 'libraries', 'activities' );
							CActivityStream::add( $act, $params->toString());
						}

						//add user points
						CFactory::load( 'libraries' , 'userpoints' );
						CUserPoints::assignPoint('event.avatar.upload');
	
						$mainframe =& JFactory::getApplication();
						$mainframe->redirect( $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewevent&eventid=' . $eventid , false ) , JText::_('CC EVENT AVATAR UPLOADED') );
						exit;
					}
				}
			}
		}

		echo $view->get( __FUNCTION__ );
	}
	
	/*
	 * group event name
	 * object array	 	
     */	
	
	public function triggerEvents( $eventName, &$args, $target = null)
	{
		CError::assert( $args , 'object', 'istype', __FILE__ , __LINE__ );
		
		require_once( COMMUNITY_COM_PATH.DS.'libraries' . DS . 'apps.php' );
		$appsLib	=& CAppPlugins::getInstance();
		$appsLib->loadApplications();		
		
		$params		= array();
		$params[]	= &$args;
		
		if(!is_null($target))
			$params[]	= $target;
				
		$appsLib->triggerEvent( $eventName , $params);
		return true;
	}		

	/**
	 * Ajax function to save a new wall entry
	 *
	 * @param message	A message that is submitted by the user
	 * @param uniqueId	The unique id for this group
	 *
	 **/
	public function ajaxSaveWall( $message , $uniqueId )
	{
		$response		= new JAXResponse();
		$my				= CFactory::getUser();

		// Load necessary libraries
		CFactory::load( 'libraries',	'wall' );
		CFactory::load( 'helpers',		'url' );
		CFactory::load( 'libraries',	'activities' );
		
		$model	=& $this->getModel( 'events' );
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $uniqueId );
		$message	= strip_tags( $message );

		// Only those who response YES/NO/MAYBE can write on wall
		$eventMembers	=& JTable::getInstance( 'EventMembers' , 'CTable' );
        $eventMembers->load($my->id, $uniqueId);

		$allowedStatus = array(COMMUNITY_EVENT_STATUS_ATTEND,
						COMMUNITY_EVENT_STATUS_WONTATTEND,
						COMMUNITY_EVENT_STATUS_MAYBE);
		CFactory::load( 'helpers' , 'owner' );
		$config			= CFactory::getConfig();
		
		if( (!in_array($eventMembers->status, $allowedStatus) && !COwnerHelper::isCommunityAdmin() && $config->get('lockeventwalls') ) || $my->id == 0 )
		{
			// Should not even be here unless use try to manipulate ajax call
			JError::raiseError( 500, 'PERMISSION DENIED');
		}

		// If the content is false, the message might be empty.
		if( empty( $message) )
		{
			$response->addAlert( JText::_('CC EMPTY MESSAGE') );
		}
		else
		{
			$isAdmin		= $event->isAdmin($my->id);
			// Save the wall content
			$wall			= CWallLibrary::saveWall( $uniqueId , $message , 'events' , $my , $isAdmin , 'events,events');
			$event->addWallCount();


			CFactory::load( 'helpers' , 'event' );
			$handler	= CEventHelper::getHandler( $event );
						
			if( $handler->isPublic() )
			{
				$actor			= $my->id;
				$target			= 0;
				$content		= $message;
				$cid			= $uniqueId;
				$app			= 'events';
				$act			= $handler->getActivity( 'events.wall.create' , $actor, $target , $content , $cid , $app );
							
				$params = new JParameter( '' );
				$params->set('event_url', $handler->getFormattedLink( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id , false , true , false ) );
				$params->set('action', 'events.wall.create');
				$params->set('wallid', $wall->id);

				CFactory::load ( 'libraries', 'activities' );
				CActivityStream::add( $act, $params->toString());
			}
						
			// @rule: Add user points
			CFactory::load( 'libraries' , 'userpoints' );
			CUserPoints::assignPoint('events.wall.create');

			$response->addScriptCall( 'joms.walls.insert' , $wall->content );
		}

		return $response->sendResponse();
	}
	
	public function ajaxRemoveWall( $wallId )
	{
		CError::assert($wallId , '', '!empty', __FILE__ , __LINE__ );
		
		$response	= new JAXResponse();
		
		CFactory::load('helpers', 'owner');
		if (!COwnerHelper::isRegisteredUser())
		{
			return $this->ajaxBlockUnregister();
		}

		//@rule: Check if user is really allowed to remove the current wall
		$my			= CFactory::getUser();
		$wallModel	=& $this->getModel( 'wall' );
		$wall		= $wallModel->get( $wallId );
		
		$eventModel	=& $this->getModel( 'events' );
		$event		=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $wall->contentid );

		if( !COwnerHelper::isCommunityAdmin() && !$event->isAdmin($my->id) )
		{
			$response->addScriptCall( 'alert' , JText::_('CC NOT ALLOWED TO REMOVE WALL') );
		}
		else
		{
			if( !$wallModel->deletePost( $wallId ) )
			{
				$response->addAlert( JText::_('CC CANNOT REMOVE WALL') );
			}
			else
			{
				if($wall->post_by != 0)
				{
					//add user points
					CFactory::load( 'libraries' , 'userpoints' );		
					CUserPoints::assignPoint('wall.remove', $wall->post_by);
				}
						
			}
	
			// Substract the count
			$event->substractWallCount();
		}

		return $response->sendResponse();
	}

	protected function _viewEnabled()
	{
		$config	= CFactory::getConfig();
		return $config->get( 'enableevents' );
	}
}