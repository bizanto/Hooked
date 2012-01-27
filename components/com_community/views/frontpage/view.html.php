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

class CommunityViewFrontpage extends CommunityView
{
	function display()
	{
		$mainframe 	= JFactory::getApplication();		
		$config 	= CFactory::getConfig();
		$document 	= JFactory::getDocument();
		
		$config	= CFactory::getConfig();
 		$document->setTitle( JText::sprintf('CC FRONTPAGE TITLE', $config->get('sitename')));

		$my 			 = CFactory::getUser();
		$model 			 = CFactory::getModel('user');
		$avatarModel 	 = CFactory::getModel('avatar');
		$status 		 = CFactory::getModel('status');	
		
		$frontpageUsers	 = intval( $config->get('frontpageusers') );
		$document->addScriptDeclaration("var frontpageUsers	= ".$frontpageUsers.";");
		
		$frontpageVideos = intval( $config->get('frontpagevideos') );
		$document->addScriptDeclaration("var frontpageVideos	= ".$frontpageVideos.";");
		
		$status			 = $status->get( $my->id );
		

		$feedLink = CRoute::_('index.php?option=com_community&view=frontpage&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('CC SUBSCRIBE RECENT ACTIVITIES FEED') . '" href="'.$feedLink.'"/>';
		$mainframe->addCustomHeadTag( $feed );

		CFactory::load( 'libraries' , 'tooltip' );
		CFactory::load( 'libraries' , 'activities' );

		// Process headers HTML output
		$headerHTML	= '';
		$tmpl		= new CTemplate();
		$alreadyLogin = 0;
		
		if( $my->id != 0 )
		{
			$headerHTML	  = $tmpl->fetch( 'frontpage.members');
			$alreadyLogin = 1;
		}
		else
		{
			$uri	= CRoute::_('index.php?option=com_community&view=' . $config->get('redirect_login') , false );
			$uri	= base64_encode($uri);
			
			$fbHtml	= '';

			if( $config->get('fbconnectkey') && $config->get('fbconnectsecret') )
			{
				CFactory::load( 'libraries' , 'facebook' );
				$facebook	= new CFacebook();
				$fbHtml		= $facebook->getLoginHTML();
			}

			$usersConfig =& JComponentHelper::getParams('com_users');

			$tmpl->set( 'fbHtml' , $fbHtml );
			$tmpl->set( 'return' , $uri );			
			$tmpl->set( 'config' , $config );
			$tmpl->set( 'usersConfig' , $usersConfig );
			$headerHTML	  = $tmpl->fetch( 'frontpage.guests' );
		}
		
		$my			   = CFactory::getUser();
		$totalMembers  = $model->getMembersCount();
		
		unset( $tmpl );
		
		// Caching on latest members, groups, videos, activities, photos...
		$cache 	    = CFactory::getCache('Core');
		$permission = $my->id==0 ? 0 : 20; // public or site members
		
		// Cache few groups to display random data because shuffle is not working after page cached.
		$intRandom = rand(COMMUNITY_CACHE_RANDOM_MIN, COMMUNITY_CACHE_RANDOM_MAX);
		
		if (!($latestMembersHTML   = $cache->load('frontpage_showLatestMembers_'. $intRandom))){
			$latestMembersHTML     = $this->showLatestMembers( $config->get('frontpageusers') );
			$cache->save($latestMembersHTML, NULL, array(COMMUNITY_CACHE_TAG_MEMBERS));
		}
		
		if (!($latestGroupsHTML    = $cache->load('frontpage_showLatestgroups'))) {
			$latestGroupsHTML      = $this->showLatestGroups( $config->get('frontpagegroups') );
			$cache->save($latestGroupsHTML, NULL, array(COMMUNITY_CACHE_TAG_GROUPS));
		}
		
		if (!($latestVideoHTML      = $cache->load('frontpage_showLatestVideos_' . $permission . '_' . $intRandom))) {
			$latestVideoHTML        = $this->showLatestVideos( $config->get('frontpagevideos'));
			$cache->save($latestVideoHTML, NULL, array(COMMUNITY_CACHE_TAG_VIDEOS));
		} 
		                        
		if (!($latestPhotosHTML     = $cache->load('frontpage_showLatestPhotos_'.$intRandom))) {   
			$latestPhotosHTML       = $this->showLatestPhotos();
			$cache->save($latestPhotosHTML, NULL, array(COMMUNITY_CACHE_TAG_PHOTOS));
		}
		
		$latestEventsHTML		= $this->showLatestEvents( $config->get('frontpage_events_limit') );
		$latestActivitiesHTML   = $this->showLatestActivities();
		
        $tmpl = new CTemplate();
		$tmpl->set( 'totalMembers'	 , $totalMembers);
		$tmpl->set( 'my'			 , $my );        
        $tmpl->set( 'alreadyLogin'	 , $alreadyLogin );
        $tmpl->set( 'header'		 , $headerHTML );
		$tmpl->set( 'onlineMembers'  , $this->getOnlineMembers() );
		$tmpl->set( 'userActivities' , $latestActivitiesHTML) ;	
		$tmpl->set( 'config'		 , $config);
		$tmpl->set( 'latestMembers'  , $latestMembersHTML);
		$tmpl->set( 'latestGroups'	 , $latestGroupsHTML);
		/** Compatibility fix **/
		$tmpl->set( 'latestPhotos'	, $this->showLatestPhotos( true ) );
        $tmpl->set( 'latestPhotosHTML'	 , $latestPhotosHTML );
		$tmpl->set( 'latestVideos'	 , $this->showLatestVideos( $config->get('frontpagevideos') , true ) );
		$tmpl->set( 'latestVideosHTML'	 , $latestVideoHTML );
		$tmpl->set( 'latestEvents'	, $latestEventsHTML );
		
		$tmpl->set( 'customActivityHTML' , $this->getCustomActivityHTML() );
		CFactory::load( 'helpers', 'string' );
		
		echo $tmpl->fetch('frontpage.index');
	}
	
	public function getCustomActivityHTML()
	{
		$tmpl	= new CTemplate();
		$tmpl->set( 'isCommunityAdmin'	, COwnerHelper::isCommunityAdmin() );		
		$tmpl->set( 'customActivities'	, CActivityStream::getCustomActivities() );
		
		return $tmpl->fetch( 'custom.activity' );
	}
	
	function showLatestActivities()
	{
		$act			= new CActivityStream();
		$config			= CFactory::getConfig();
		$my				= CFactory::getUser();
		$userActivities	= '';
		
		if( $config->get('frontpageactivitydefault') == 'friends' && $my->id != 0 )
		{
			CFactory::load( 'helpers' , 'time' );
			$friendsModel	= CFactory::getModel( 'Friends' );

			$userActivities	= $act->getHTML( $my->id, $friendsModel->getFriendIds( $my->id ) , CTimeHelper::getDate( $my->registerDate) , $config->get('maxactivities') , '' , '', true , COMMUNITY_SHOW_ACTIVITY_MORE );
		}
		else
		{
			$userActivities	= $act->getHTML('', '', null, $config->get('maxactivities') , '' , '', true , COMMUNITY_SHOW_ACTIVITY_MORE );
		}
		return $userActivities;
	}
	
	function showMostActive($data = null){
	}
	
	/**
	 * Show listing of group with the most recent activities
	 */	 	
	function showActiveGroup()
	{
		$groupModel 	= CFactory::getModel('groups');
		$activityModel	= CFactory::getModel('activities');
		$act	= new CActivityStream();
		
		$html = $act->getHTML( '', '', null, 10 , 'groups');
		
		return $html;
	}

	/**
	 * Retrieve the latest events
	 *
	 * @param	int	$total	The total number of events to retrieve
	 * @return	string	The html codes.	 
	 **/	 	 	 	
	function showLatestEvents( $total = 5 )
	{
		$model	= CFactory::getModel('Events');
		$result	= $model->getEvents( null , null , null , null , true , false , null , null , CEventHelper::ALL_TYPES , 0 , $total );
		$events	= array();

		foreach( $result as $row ) 
		{
			$event	=& JTable::getInstance( 'Event' , 'CTable' );
			$event->bind( $row );
			$events[]	= $event;
		}
		$tmpl = new CTemplate();
		$tmpl->set( 'events' , $events );
        return $tmpl->fetch('frontpage.latestevents');
	}
		
	function showLatestGroups( $total = 5 )
	{
		$groupModel	= CFactory::getModel('groups');
		$tmpGroups	= $groupModel->getAllGroups( null , null , null , $total );
		$groups		= array();
		
		foreach($tmpGroups as $row)
		{
			$group	=& JTable::getInstance('Group','CTable');
			$group->bind( $row );
			$groups[]	= $group;
		}
		$tmpl = new CTemplate();
        $tmpl->setRef( 'groups', $groups );
        return $tmpl->fetch('frontpage.latestgroup');
	}
	
	function showLatestVideos( $total = 5 , $raw = false )
	{
		$my		= CFactory::getUser();
		
		// Oversample the total so that we get a randomized value
		$oversampledTotal	= $total * COMMUNITY_OVERSAMPLING_FACTOR;
		
		CFactory::load( 'libraries', 'videos' );
		
		$videoModel 	= CFactory::getModel('videos');
		$videosfilter	= array(
			'published'	=> 1,
			'status'	=> 'ready',
			'permissions'	=> ($my->id==0) ? 0 : 20,
			'or_group_privacy'	=> 0,
			'limit'		=> $oversampledTotal
		);
		$videos			= $videoModel->getVideos($videosfilter, true);
		
		if ($videos)
		{
			shuffle( $videos );
			
			// Test the number of result so the loop will not fail with incorrect index.
			$total		= count( $videos ) < $total ? count($videos) : $total;
			$videos		= array_slice($videos, 0, $total);
		}
		
		if( $raw )
		{
			return $videos;
		}
		
		$tmpl = new CTemplate();
        $tmpl->setRef( 'data', $videos );

		$tmpl->set( 'thumbWidth' , CVideoLibrary::thumbSize('width') );
		$tmpl->set( 'thumbHeight' , CVideoLibrary::thumbSize('height') );
		
        return $tmpl->fetch('frontpage.latestvideos');
		
	}
	  
	function showLatestMembers($limit)
	{
		$model = CFactory::getModel('user');
		$latestMembers = $model->getLatestMember( $limit );
		$totalMembers  = $model->getMembersCount();
		
		$data = array();
		
		if( !empty( $latestMembers ) )
		{
			shuffle( $latestMembers );
			$data['members'] = $latestMembers;
			$data['limit'] = ( count( $latestMembers ) > $limit ) ? $limit : count( $latestMembers );	
		}

		$tmpl = new CTemplate();
        $tmpl->set('memberList', $this->get('getMembersHTML', $data));
        $tmpl->set('totalMembers', $totalMembers);
        return $tmpl->fetch('frontpage.latestmember');
	}
	
	/**
	 * Show listing of most recent photos.
	 * @param	$rawData	Retrieves the raw data of recent photos	 
	 */
	function showLatestPhotos( $rawData = false )
	{   		
		$config 		 = CFactory::getConfig();    
		$photoModel		 = CFactory::getModel('photos');
		$frontpagePhotos = intval( $config->get('frontpagephotos') );
		$latestPhotos	 = $photoModel->getAllPhotos( null , PHOTOS_USER_TYPE, $frontpagePhotos, 0 , COMMUNITY_ORDER_BY_DESC , COMMUNITY_ORDERING_BY_CREATED );

		if( $latestPhotos )
		{
			shuffle( $latestPhotos );
			// Make sure it is all photo object
			foreach( $latestPhotos as &$row )
			{
				
				$photo	=& JTable::getInstance( 'Photo' , 'CTable' );
				$photo->bind($row);
				$row = $photo; 
			}
		}
		
		if( !empty($latestPhotos) )
		{
			for( $i = 0; $i < count( $latestPhotos ); $i++ )
			{
				$row =& $latestPhotos[$i];
				
				$row->user	= CFactory::getUser( $row->creator );
			}
		}
		
		if( $rawData )
		{
			return $latestPhotos;
		}
			
		$tmpl = new CTemplate();
        $tmpl->setRef( 'latestPhotos', $latestPhotos );
        
        return $tmpl->fetch('frontpage.latestphoto');
	}

	function getMembersHTML($data)
	{
		if (empty($data)) return '';
		
		$members	= $data['members'];
		$limit		= $data['limit'];

		$tmpl = new CTemplate();
		$tmpl->set('members', $members);
		$html = $tmpl->fetch('frontpage.latestmember.list');

		echo $html;
	}
	
	function getOnlineMembers()
	{
		$model 		   = CFactory::getModel('user');
	 	$onlineMembers = $model->getOnlineUsers( 20 , false );
	    
		if( $onlineMembers )
		{
			shuffle( $onlineMembers );
		}
		
		if( !empty( $onlineMembers ) )
		{
			for( $i = 0; $i < count( $onlineMembers ); $i++ )
			{
				$row		=& $onlineMembers[$i];
				$row->user	=  CFactory::getUser( $row->id );
			}
		}
		
		return $onlineMembers;
	}
	
	public function getVideosHTML($rows){
		$tmpl = new CTemplate();
		$tmpl->set('videos', $rows);

		$tmpl->fetch('frontpage.videos.list');
	}
}

