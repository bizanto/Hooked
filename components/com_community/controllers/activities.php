<?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class CommunityActivitiesController extends CommunityBaseController
{
	/**
	 * Method to retrieve activities via AJAX
	 **/
	public function ajaxGetActivities( $exclusions )
	{
		$response	= new JAXResponse();
		$config		= CFactory::getConfig();
		$exclusions	= explode( ',' , $exclusions );
		$my			= CFactory::getUser();
				
		CFactory::load( 'libraries' , 'activities' );
		$act 	= new CActivityStream();

		if( $config->get('frontpageactivitydefault') == 'friends' && $my->id != 0 )
		{
			CFactory::load( 'helpers' , 'time' );
			$friendsModel	= CFactory::getModel( 'Friends' );

			$html	= $act->getHTML( $my->id, $friendsModel->getFriendIds( $my->id ) , CTimeHelper::getDate( $my->registerDate) , $config->get('maxactivities') , '' , '' , true , COMMUNITY_SHOW_ACTIVITY_MORE , $exclusions , COMMUNITY_SHOW_ACTIVITY_ARCHIVED );
		}
		else
		{
			$html	= $act->getHTML('', '', null, $config->get('maxactivities') , '' , '' , true , COMMUNITY_SHOW_ACTIVITY_MORE , $exclusions , COMMUNITY_SHOW_ACTIVITY_ARCHIVED );
		}
		
		// Append new data at bottom.
		$response->addScriptCall('joms.activities.append' , $html );
		
		return $response->sendResponse();
	}
	
	/**
	 * Get content for activity based on the activity id.
	 *
	 *	@params	$activityId	Int	Activity id	 
	 **/
	public function ajaxGetContent( $activityId )
	{
		$my				= CFactory::getUser();
		$showMore		= true;
		$objResponse	= new JAXResponse();
		$model			= CFactory::getModel( 'Activities' );
		
		CFactory::load('libraries', 'privacy');
		CFactory::load('libraries', 'activities');
		
		// These core apps has default privacy issues with it
		$coreapps 		= array('photos','walls','videos', 'groups' );
		
		// make sure current user has access to the content item
		// For known apps, we can filter this manually
		$activity 		= $model->getActivity( $activityId );
		if( in_array($activity->app, $coreapps ) )
		{
			CFactory::load( 'helpers' , 'privacy' );
			
			switch($activity->app)
			{
				case 'walls':
					// make sure current user has permission to the profile
					$showMore = CPrivacy::isAccessAllowed($my->id, $activity->target, 'user', 'privacyProfileView');
					break;
				case 'videos':
					// Each video has its own privacy setting within the video itself
					CFactory::load( 'models' , 'videos' );
					$video	= JTable::getInstance( 'Video' , 'CTable' );
					$video->load( $activity->cid );
					$showMore = CPrivacy::isAccessAllowed($my->id, $activity->actor, 'custom', $video->permissions);
					break;
					
				case 'photos':
					// for photos, we uses the actor since the target is 0 and he
					// is doing the action himself
					$showMore = CPrivacy::isAccessAllowed($my->id, $activity->actor, 'user', 'privacyPhotoView');
					break;
				case 'groups':
			}
		}
		else
		{
			// if it is not one of the core apps, we should allow plugins to decide
			// if they want to block the 'more' view
		}
		
		if( $showMore )
		{
			$act		= $model->getActivity( $activityId );
			$content	= CActivityStream::getActivityContent($act);			
			
			$objResponse->addScriptCall( 'joms.activities.setContent' , $activityId , $content );
		}
		else
		{
			$content 	= JText::_('CC ACCESS FORBIDDEN');
			$content	= nl2br( $content );
			$content	= JString::str_ireplace( "\n" , '' , $content );
			$objResponse->addScriptCall( 'joms.activities.setContent' , $activityId , $content );
		}
		$objResponse->addScriptCall( 'joms.tooltip.setup();' );
		
		return $objResponse->sendResponse();
	}
	
	/**
	 * Hide the activity from the profile
	 * @todo: we should also hide all aggregated activities	 
	 */	 	
	public function ajaxHideActivity( $userId , $activityId )
	{
		$objResponse	= new JAXResponse();
		$model			=& $this->getModel('activities');
		$my				= CFactory::getUser();
		
		// Guests should not be able to hide anything.
		if( $my->id == 0 )
			return false;
		
		CFactory::load( 'helpers' , 'ower' );
		$id		= $my->id;
		
		// Administrators are allowed to hide others activity.
		CFactory::load('helper', 'owner');
		if( COwnerHelper::isCommunityAdmin() )
		{
			$id	= $userId;
		}
		
		$model->hide( $id , $activityId );
		$objResponse->addScriptCall('joms.jQuery("#profile-newsfeed-item' . $activityId . '").fadeOut("5400");');
		$objResponse->addScriptCall('joms.jQuery("#mod_profile-newsfeed-item' . $activityId . '").fadeOut("5400");');
		
		return $objResponse->sendResponse();
	}
	
	
	public function ajaxDeleteActivity($app,$activityId)
	{   
		$objResponse	= new JAXResponse();   
		$model		=& $this->getModel( 'activities' );
		
		CFactory::load( 'helpers' , 'owner' );
		
		if( COwnerHelper::isCommunityAdmin() )
		{
			$model->deleteActivity( $app, $activityId );
			$objResponse->addScriptCall('joms.jQuery("#profile-newsfeed-item' . $activityId . '").fadeOut("5400");');
			$objResponse->addScriptCall('joms.jQuery("#mod_profile-newsfeed-item' . $activityId . '").fadeOut("5400");');
		}
		
		return $objResponse->sendResponse();
	}

	/**
	 * AJAX method to add predefined activity
	 **/
	function ajaxAddPredefined( $key , $message = '' )
	{
		$objResponse	= new JAXResponse();
		$my		= CFactory::getUser();

		CFactory::load( 'helpers' , 'owner' );
		
		if( !COwnerHelper::isCommunityAdmin() )
		{
			return;
		}
		// Predefined system custom activity.
		$system	= array( 'system.registered', 'system.populargroup' , 'system.totalphotos' , 'system.popularprofiles' , 'system.popularphotos' , 'system.popularvideos' );

		$act = new stdClass();
		$act->actor   	= $my->id;
		$act->target  	= 0;
		$act->app		= 'system';
		$act->access	= PRIVACY_FORCE_PUBLIC;
		$params         = '';

		if( in_array( $key , $system ) )
		{
			switch( $key )
			{
				case 'system.registered':
					CFactory::load( 'helpers' , 'time' );
					$usersModel	= CFactory::getModel( 'user' );
					$date		= CTimeHelper::getDate();
					$title		= JText::sprintf( 'CC TOTAL USERS REGISTERED THIS MONTH ACTIVITY TITLE' , $usersModel->getTotalRegisteredByMonth( $date->toFormat( '%Y-%m' ) ) , $date->_monthToString( $date->toFormat( '%m' ) ) );

					$act->cmd 		= 'system.registered';
					$act->title	  	= $title;
					$act->content   = '';
					
					break;
				case 'system.populargroup':
					$groupsModel	= CFactory::getModel( 'groups' );
					$activeGroup	= $groupsModel->getMostActiveGroup();
	
					$title			= JText::sprintf( 'CC MOST POPULAR GROUP ACTIVITY TITLE' , $activeGroup->name );
	
					$params			= new JParameter('');
					$params->set( 'action' , 'groups.join');
					$params->set( 'group_url', CRoute::_( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $activeGroup->id ) );

					$act->cmd       = 'groups.popular';
					$act->cid		= $activeGroup->id;
					$act->title     = $title;

					break;
				case 'system.totalphotos':
				    $photosModel    = CFactory::getModel( 'photos' );
				    $total			= $photosModel->getTotalSitePhotos();
	
					$params			= new JParameter('');
					$params->set( 'photos_url', CRoute::_( 'index.php?option=com_community&view=photos' ) );
					
					$act->cmd       = 'photos.total';
					$act->title     =  JText::sprintf( 'CC TOTAL PHOTOS ACTIVITY TITLE' , $total );

				    break;
				case 'system.popularprofiles':
				    CFactory::load( 'libraries' , 'tooltip' );
				    $model		= CFactory::getModel( 'user' );
					$members	= $model->getPopularMember( 5 );
					$total      = count( $members );
					$content    = '';
	
					$tmpl       = new CTemplate();
					$tmpl->set( 'members'	, $members );
					$content	= $tmpl->fetch( 'activity.members.popular' );

					$act->cmd       = 'members.popular';
					$act->content	= $content;
					$act->title     = JText::sprintf( 'CC ACTIVITIES TOP PROFILES', 5 );

				    break;
				case 'system.popularphotos':
					$model		= CFactory::getModel( 'photos');
					$photos		= $model->getPopularPhotos( 5 , 0 );
					
					$tmpl       = new CTemplate();
					$tmpl->set( 'photos'	, $photos );
					$content	= $tmpl->fetch( 'activity.photos.popular' );

					$act->cmd   = 'photos.popular';
					$act->title = JText::sprintf( 'CC ACTIVITIES TOP PHOTOS', 5 );
					$act->content   = $content;

					break;
				case 'system.popularvideos':
					$model		= CFactory::getModel( 'videos');
					$videos		= $model->getPopularVideos( 5 );
					
					$tmpl       = new CTemplate();
					$tmpl->set( 'videos'	, $videos );
					$content	= $tmpl->fetch( 'activity.videos.popular' );

					$act->cmd   = 'videos.popular';
					$act->title =  JText::sprintf( 'CC ACTIVITIES TOP VIDEOS', 5 );
					$act->content   = $content;

					break;
			}
		}
		else
		{
			// For additional custom activities, we only take the content passed by them.
			if( !empty( $message ) )
			{
				$app		= explode( '.' , $key );
				$app		= isset( $app[0] ) ? $app[0] : 'system';
				$act->title = JText::_( $message );
				$act->app   = $app;
			}
		}

		$this->_addActivity( $act , $params );

		$objResponse->addAssign('activity-stream-container' , 'innerHTML' , $this->_getActivityStream() );
		$objResponse->addScriptCall( "joms.jQuery('.jomTipsJax').addClass('jomTips');" );
		$objResponse->addScriptCall( 'joms.tooltip.setup();' );
		return $objResponse->sendResponse();
	}

	private function _getActivityStream()
	{
		CFactory::load( 'libraries' , 'activities' );
		$act 	= new CActivityStream();
		$html	= $act->getHTML( '' , '' , null , 0 , '' , '', true , COMMUNITY_SHOW_ACTIVITY_MORE );
		return $html;
	}
	
	private function _addActivity( $act , $params = ''  )
	{
		if( empty($params) )
		{
			$params	= new JParameter( '' );
		}

		// Add activity logging
		CFactory::load ( 'libraries', 'activities' );

		return CActivityStream::add( $act, $params->toString() );
	}
}
