<?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

class CommunityViewEvents extends CommunityView
{

	function _addSubmenu()
	{
		CFactory::load( 'helpers' , 'event' );
		$id		= JRequest::getVar( 'eventid' , '' , 'REQUEST' );
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $id );
		
		CEventHelper::getHandler( $event )->addSubmenus( $this );
	}
	
	function showSubmenu()
	{
		$this->_addSubmenu();
		parent::showSubmenu();
	}

	/**
	 * Application full view
	 **/
	function appFullView()
	{
		$document =& JFactory::getDocument();
		
		$this->showSubmenu();
		
		$applicationName = JString::strtolower( JRequest::getVar( 'app' , '' , 'GET' ) );

		if(empty($applicationName))
		{
			JError::raiseError( 500, 'CC APP ID REQUIRED');
		}
		
		if(!$this->accessAllowed('registered'))
		{
			return;
		}
		
		$output	= '';
		
		//@todo: Since group walls doesn't use application yet, we process it manually now.
		if( $applicationName == 'walls' )
		{
			CFactory::load( 'libraries' , 'wall' );
			$jConfig	= JFactory::getConfig();
			$limit		= $jConfig->get('list_limit');
			$limitstart = JRequest::getVar( 'limitstart', 0, 'REQUEST' );
			$eventId	= JRequest::getVar( 'eventid' , '' , 'GET' );
			$my			= CFactory::getUser();
			$config		= CFactory::getConfig();

			$eventsModel	= CFactory::getModel( 'Events' );
			$event			=& JTable::getInstance( 'Event' , 'CTable' );
			$event->load( $eventId );
			$config			= CFactory::getConfig();
			$document->setTitle( JText::sprintf('CC EVENTS WALL TITLE' , $event->title ) );		
			CFactory::load( 'helpers' , 'owner' );

			$guest				= $event->isMember( $my->id );
			$waitingApproval	= $event->isPendingApproval( $my->id );
			$status				= $event->getUserStatus($my->id);
			$responded			= (($status == COMMUNITY_EVENT_STATUS_ATTEND)
									|| ($status == COMMUNITY_EVENT_STATUS_WONTATTEND)
									|| ($status == COMMUNITY_EVENT_STATUS_MAYBE));

			if( !$config->get('lockeventwalls') || ($config->get('lockeventwalls') && ($guest) && !($waitingApproval) && $responded) || COwnerHelper::isCommunityAdmin() )
			{
				$output	.= CWallLibrary::getWallInputForm( $event->id , 'events,ajaxSaveWall', 'events,ajaxRemoveWall' );

				// Get the walls content
				$output 		.='<div id="wallContent">';
				$output			.= CWallLibrary::getWallContents( 'events' , $event->id , $event->isAdmin( $my->id ) , 0 , $limitstart , 'wall.content' ,'events,events');
				$output 		.= '</div>';
				
				jimport('joomla.html.pagination');
				$wallModel 		= CFactory::getModel('wall');
				$pagination		= new JPagination( $wallModel->getCount( $event->id , 'events' ) , $limitstart , $limit );
	
				$output		.= '<div class="pagination-container">' . $pagination->getPagesLinks() . '</div>';
			}
		}
		else
		{
			CFactory::load( 'libraries' , 'apps' );
			$model				= CFactory::getModel('apps');
			$applications		=& CAppPlugins::getInstance();
			$applicationId		= $model->getUserApplicationId( $applicationName );
			
			$application		= $applications->get( $applicationName , $applicationId );

			if( !$application )
			{
				JError::raiseError( 500 , 'CC APPLICATION NOT FOUND' );
			}
			
			// Get the parameters
			$manifest			= JPATH_PLUGINS . DS . 'community' . DS . $applicationName . DS . $applicationName . '.xml';
			
			$params			= new JParameter( $model->getUserAppParams( $applicationId ) , $manifest );
	
			$application->params	=& $params;
			$application->id		= $applicationId;
			
			$output	= $application->onAppDisplay( $params );
		}
		
		echo $output;
	}

	function display($tpl = null)
	{

		$mainframe	=& JFactory::getApplication();
		$document 	=& JFactory::getDocument();
		$config		= CFactory::getConfig();
		$my		= CFactory::getUser();

		$script = '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>';
		$mainframe->addCustomHeadTag( $script );

		$groupId    = JRequest::getVar('groupid','', 'GET');
		if (!empty($groupId))
		{
			$group =& JTable::getInstance( 'Group' , 'CTable' );
			$group->load( $groupId );
		
			// Set pathway for group videos
			// Community > Groups > Group Name > Events
			$this->addPathway( JText::_('CC GROUPS'), CRoute::_('index.php?option=com_community&view=groups') );
			$this->addPathway( $group->name, CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId));
		}

		//page title
		$this->addPathway( JText::_('CC EVENTS') , CRoute::_('index.php?option=com_community&view=events') );

		// Get category id from the query string if there are any.
		$categoryId	= JRequest::getInt( 'categoryid' , 0 );
		$limitstart	= JRequest::getVar( 'limitstart' , 0 );
		$category	=& JTable::getInstance( 'EventCategory' , 'CTable' );
		$category->load( $categoryId );
		
		if( isset( $category ) && $category->id != 0 )
		{
			$document->setTitle( JText::sprintf('CC VIEW BY CATEGORY NAME' , JText::_( $this->escape($category->name) ) ) );
		}
		else
		{
			$document->setTitle(JText::_('CC BROWSE EVENTS TITLE'));
		} 
		

		$this->showSubmenu();   
		
		$feedLink = CRoute::_('index.php?option=com_community&view=events&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('CC SUBSCRIBE ALL EVENTS FEED') . '" href="'.$feedLink.'"/>';
		$mainframe->addCustomHeadTag( $feed );
		
		// loading neccessary files here.
		CFactory::load( 'libraries' , 'filterbar' );
		CFactory::load( 'helpers' , 'owner' );
		CFactory::load( 'helpers' , 'event' );
		CFactory::load( 'models' , 'events');

 		$data		= new stdClass();
		$sorted		= JRequest::getVar( 'sort' , 'startdate' , 'GET' );

		/* begin: UNLIMITED LEVEL BREADCRUMBS PROCESSING */
		if( $category->parent == COMMUNITY_NO_PARENT )
		{
			$this->addPathway( $category->name , CRoute::_('index.php?option=com_community&view=events&categoryid=' . $category->id ) );
		}
		else{
			// Parent Category
			$parentsInArray	=   array();
			$n		=   0;
			$parentId	=   $category->id;

			$parent	=&  JTable::getInstance( 'EventCategory' , 'CTable' );

			do
			{
				$parent->load( $parentId );
				$parentId	=   $parent->parent;

				$parentsInArray[$n]['id']	=   $parent->id;
				$parentsInArray[$n]['parent']	=   $parent->parent;
				$parentsInArray[$n]['name']	=   $parent->name;
				
				$n++;				
			}
			while ( $parent->parent > COMMUNITY_NO_PARENT );

			for( $i=count($parentsInArray)-1; $i>=0; $i-- )
			{
				$this->addPathway( $parentsInArray[$i]['name'], CRoute::_('index.php?option=com_community&view=events&categoryid=' . $parentsInArray[$i]['id'] ) );
			}
		}
		/* end: UNLIMITED LEVEL BREADCRUMBS PROCESSING */

		$model		= CFactory::getModel( 'events' );		
		$cache		= CFactory::getCache('Core');
		
		if (!($data->categories = $cache->load('_events_category_' . $categoryId)))
		{
			// Get the categories
 			$data->categories	= $model->getCategories( CEventHelper::ALL_TYPES , $categoryId );
			$cache->save($data->categories, NULL, array(COMMUNITY_CACHE_TAG_EVENTS_CAT));
		}
		
		CFactory::load( 'helpers' , 'event' );
		$event		=&JTable::getInstance( 'Event' , 'CTable' );
		$handler	= CEventHelper::getHandler( $event );

		if (!($eventsHTML = $cache->load('_events_eventList_' . $categoryId . '_' . $limitstart)))
		{
			 // It is safe to pass 0 as the category id as the model itself checks for this value.
	 		$data->events      = $model->getEvents( $category->id , null, $sorted , null , true , false , null , null , $handler->getContentTypes() , $handler->getContentId() );
	 		                                          
			// Get pagination object
			$data->pagination	= $model->getPagination();
			
	
			// Get the template for the event category lists
			$eventsHTML	= $this->_getEventsHTML( $data->events, false, $data->pagination );
			
			$cache->save($eventsHTML, NULL, array(COMMUNITY_CACHE_TAG_PHOTOS, COMMUNITY_CACHE_TAG_EVENTS));
		}

		$tmpl		= new CTemplate();

		$sortItems =  array(
				'latest' 	=> JText::_('CC SORT EVENT CREATED') ,
				'startdate'	=> JText::_('CC SORT EVENT STARTDATE'));
		
		$tmpl->set( 'handler'		, $handler );
		$tmpl->set( 'index'		, true );
		$tmpl->set( 'categories' 	, $data->categories );
		$tmpl->set( 'eventsHTML'	, $eventsHTML );
		$tmpl->set( 'config'		, $config );
		$tmpl->set( 'category' 		, $category );
		$tmpl->set( 'isCommunityAdmin'	, COwnerHelper::isCommunityAdmin() );
		$tmpl->set( 'sortings'		, CFilterBar::getHTML( CRoute::getURI(), $sortItems, 'startdate') );
		$tmpl->set( 'my' 		, $my );
		
		echo $tmpl->fetch( 'events.index' );
		
	}

	/**
	 * Display invite form
	 **/
	function invitefriends()
	{
		$document	=& JFactory::getDocument();
		$document->setTitle( JText::_('CC INVITE FRIENDS TO EVENT TITLE') );

		if( !$this->accessAllowed( 'registered' ) )
		{
			return;
		}

		$this->showSubmenu();

		$my				= CFactory::getUser();
		$eventId		= JRequest::getVar( 'eventid' , '' , 'GET' );
		$this->_addEventInPathway( $eventId );
		$this->addPathway( JText::_('CC INVITE FRIENDS TO EVENT TITLE') );

		$friendsModel	= CFactory::getModel( 'Friends' );
		$model	        = CFactory::getModel( 'Events' );
        $event          =& JTable::getInstance('Event' , 'CTable');
        $event->load($eventId);

		$tmpFriends		= $friendsModel->getFriends( $my->id , 'name' , false);

		$friends		= array();

		for( $i = 0; $i < count( $tmpFriends ); $i++ )
		{
			$friend			=& $tmpFriends[ $i ];
			$eventMember	=& JTable::getInstance( 'EventMembers' , 'CTable' );
			$eventMember->load( $eventId , $friend->id );


			if( !$event->isMember( $friend->id ) && !$eventMember->exists())
			{
				$friends[]	= $friend;
			}
		}
		unset( $tmpFriends );

		$tmpl   = new CTemplate();
		$tmpl->set( 'friends' , $friends );
		$tmpl->set( 'event' , $event );
		echo $tmpl->fetch( 'events.invitefriends' );
	}
	
	function pastevents()
	{
		$mainframe	=&	JFactory::getApplication();
		$document 	=&	JFactory::getDocument();
		$config		=&	CFactory::getConfig();
		$my		=	CFactory::getUser();

		$this->addPathway( JText::_('CC EVENTS') , CRoute::_('index.php?option=com_community&view=events') );
		$this->addPathway( JText::_('CC PAST EVENTS TITLE') , '' );

		$document->setTitle(JText::_('CC PAST EVENTS TITLE'));

		$this->showSubmenu();

		$feedLink = CRoute::_('index.php?option=com_community&view=events&task=pastevents&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('CC SUBSCRIBE EXPIRED EVENTS FEED') . '"  href="'.$feedLink.'"/>';
		$mainframe->addCustomHeadTag( $feed );

		// loading neccessary files here.
		CFactory::load( 'libraries' , 'filterbar' );
		CFactory::load( 'helpers' , 'event' );
		CFactory::load( 'helpers' , 'owner' );
		CFactory::load( 'models' , 'events');
		//$event		= JTable::getInstance( 'Event' , 'CTable' );

 		$data		= new stdClass();
		$sorted		= JRequest::getVar( 'sort' , 'latest' , 'GET' );
		$model		= CFactory::getModel( 'events' );

 		// It is safe to pass 0 as the category id as the model itself checks for this value.
 		$data->events		= $model->getEvents( null, null , $sorted, null, false, true );

		// Get pagination object
		$data->pagination	= $model->getPagination();

		// Get the template for the group lists
		$eventsHTML	= $this->_getEventsHTML( $data->events, true, $data->pagination );

		$tmpl		= new CTemplate();

		$sortItems =  array(
				    'latest' 	=> JText::_('CC SORT EVENT CREATED') ,
				    'startdate'	=> JText::_('CC SORT EVENT START DATE'));

		$tmpl->set( 'eventsHTML'	, $eventsHTML );
		$tmpl->set( 'config'		, $config );
		$tmpl->set( 'isCommunityAdmin'	, COwnerHelper::isCommunityAdmin() );
		$tmpl->set( 'sortings'		, CFilterBar::getHTML( CRoute::getURI(), $sortItems, 'startdate') );
		$tmpl->set( 'my' 		, $my );

		echo $tmpl->fetch( 'events.pastevents' );
	}

	function myevents()
	{
		if(!$this->accessAllowed('registered'))
		{
			return;
		}

	    $mainframe	=&	JFactory::getApplication();
	    $document 	=&	JFactory::getDocument();
	    $config		=&	CFactory::getConfig();
		$my			=	CFactory::getUser();
        $userid     =	JRequest::getCmd('userid', null );

		$this->addPathway( JText::_('CC EVENTS') , CRoute::_('index.php?option=com_community&view=events') );
		$this->addPathway( JText::_('CC MY EVENTS TITLE') , '' );

        $document->setTitle(JText::_('CC MY EVENTS TITLE'));
		
		$this->showSubmenu(); 
		
		$feedLink = CRoute::_('index.php?option=com_community&view=events&userid=' . $userid . '&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('CC SUBSCRIBE MY EVENTS FEED') . '" href="'.$feedLink.'"/>';
		$mainframe->addCustomHeadTag( $feed );
		
		// loading neccessary files here.
		CFactory::load( 'libraries' , 'filterbar' );
		CFactory::load( 'helpers' , 'event' );
		CFactory::load( 'helpers' , 'owner' );
		CFactory::load( 'models' , 'events');
		//$event		= JTable::getInstance( 'Event' , 'CTable' );

 		$data		= new stdClass();
		$sorted		= JRequest::getVar( 'sort' , 'startdate' , 'GET' );
		$model		= CFactory::getModel( 'events' );

 		// It is safe to pass 0 as the category id as the model itself checks for this value.
 		$data->events		= $model->getEvents( null, $my->id , $sorted );

		// Get pagination object
		$data->pagination	= $model->getPagination();

		// Get the template for the group lists
		$eventsHTML	= $this->_getEventsHTML( $data->events, false, $data->pagination );

		$tmpl		= new CTemplate();

		$sortItems =  array(
				'latest' 		=> JText::_('CC SORT EVENT CREATED') ,
				'startdate'		=> JText::_('CC SORT EVENT STARTDATE'));

		$tmpl->set( 'eventsHTML'		, $eventsHTML );
		$tmpl->set( 'config'			, $config );
		$tmpl->set( 'isCommunityAdmin'	, COwnerHelper::isCommunityAdmin() );
		$tmpl->set( 'sortings'			, CFilterBar::getHTML( CRoute::getURI(), $sortItems, 'startdate') );
		$tmpl->set( 'my' 				, $my );

		echo $tmpl->fetch( 'events.myevents' );
	}

	function myinvites()
	{
		if(!$this->accessAllowed('registered'))
		{
			return;
		}

	    $mainframe	=&	JFactory::getApplication();
	    $document 	=&	JFactory::getDocument();
	    $config		=&	CFactory::getConfig();
		$my			=	CFactory::getUser();
        $userid     =	JRequest::getCmd('userid', null );

		$this->addPathway( JText::_('CC EVENTS') , CRoute::_('index.php?option=com_community&view=events') );
		$this->addPathway( JText::_('CC EVENT PENDING INVITATIONS') , '' );

        $document->setTitle(JText::_('CC EVENT PENDING INVITATIONS'));

		$this->showSubmenu();

		$feedLink = CRoute::_('index.php?option=com_community&view=events&userid=' . $userid . '&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('CC SUBSCRIBE TO PENDING INVITATIONS FEED') . '"  href="'.$feedLink.'"/>';
		$mainframe->addCustomHeadTag( $feed );


		CFactory::load( 'libraries' , 'filterbar' );
		CFactory::load( 'helpers' , 'event' );
		CFactory::load( 'helpers' , 'owner' );
		CFactory::load( 'models' , 'events');

		$sorted		= JRequest::getVar( 'sort' , 'startdate' , 'GET' );
		$model		= CFactory::getModel( 'events' );
		$pending	= COMMUNITY_EVENT_STATUS_INVITED;

		// It is safe to pass 0 as the category id as the model itself checks for this value.
 		$rows		= $model->getEvents( null, $my->id , $sorted, null, true, false, $pending );
		$pagination	= $model->getPagination();
		$count		= count( $rows );
		$tmpl		= new CTemplate();
		$sortItems	= array( 'latest' 		=> JText::_('CC SORT EVENT CREATED') ,
							 'startdate'		=> JText::_('CC SORT EVENT STARTDATE')
							);
		$events		= array();
		
		if( $rows )
		{
			foreach( $rows as $row )
			{
				$event	=& JTable::getInstance( 'Event' , 'CTable' );
				$event->bind( $row );
				$events[]	= $event;
			}
			unset($eventObjs);
		}
		
		$tmpl	= new CTemplate();
		$tmpl->set( 'events'			, $events );
		$tmpl->set( 'pagination' 		, $pagination );
		$tmpl->set( 'config'			, $config );
		$tmpl->set( 'isCommunityAdmin'	, COwnerHelper::isCommunityAdmin() );
		$tmpl->set( 'sortings'			, CFilterBar::getHTML( CRoute::getURI(), $sortItems, 'startdate') );
		$tmpl->set( 'my' 				, $my );
		$tmpl->set( 'count' 			, $count );

		echo $tmpl->fetch( 'events.myinvites' );
	}

	/**
	 * Method to display the create / edit event's form.
	 * Both views share the same template file.	 
	 **/	 		
	function _displayForm($event)
	{
		$mainframe	= JFactory::getApplication();
		$my			= CFactory::getUser();
		$config		= CFactory::getConfig();
		$model		= CFactory::getModel( 'events' );
		$categories	= $model->getCategories();		
		$now 		= JFactory::getDate();
		$now->setOffset( $mainframe->getCfg('offset'));
		
		jimport( 'joomla.html.editor' );
		$editorType = ($config->get('allowhtml') )? $config->get('htmleditor' , 'none') : 'none' ;
		$editor		= new JEditor( $editorType );
		
		$totalEventCount    = $model->getEventsCreationCount( $my->id );
		
		$event->startdatetime	= JRequest::getVar('startdatetime', '00:01', 'POST');
		$event->enddatetime	= JRequest::getVar('enddatetime', '23:59', 'POST');

        CFactory::load( 'helpers' , 'time' );
		$timezones	= CTimeHelper::getTimezoneList();
		
		CFactory::load( 'helpers' , 'event' );
		$helper	= CEventHelper::getHandler( $event );

		$startDate	= $event->getStartDate( false );
		$endDate	= $event->getEndDate( false );
		$hours		= array();
		
		$startAmPmSelect = "";
		$endAmPmSelect	 = "";

		if($config->get('eventshowampm')) 
		{
			for($i = 1; $i <= 12; $i++)
			{
				$hours[] = JHTML::_('select.option',  $i, "$i" );
			}

			// Cannot user ->toFormat('%p') since it is dependent on current locale
			// and would return a null if the system is configured for 24H
			$startAmPm 		= $startDate->toFormat('%H') >= 12 ? 'PM' : 'AM';
			$endAmPm		= $endDate->toFormat('%H') >= 12 ? 'PM' : 'AM';
			
			$amPmSelect		= array();
			$amPmSelect[]		= JHTML::_('select.option',  'AM', "am" );
			$amPmSelect[]		= JHTML::_('select.option',  'PM', "pm" );
			
			$startAmPmSelect	= JHTML::_('select.genericlist',  $amPmSelect , 'starttime-ampm', array('class'=>'required inputbox'), 'value', 'text', $startAmPm , false );
			$endAmPmSelect		= JHTML::_('select.genericlist',  $amPmSelect , 'endtime-ampm', array('class'=>'required inputbox'), 'value', 'text', $endAmPm , false );

			$selectedStartHour 	= intval($startDate->toFormat('%I'));
			$selectedEndHour 	= intval($endDate->toFormat('%I'));
		}
		else 
		{
			for($i = 0; $i <= 23; $i++)
			{
				$hours[] = JHTML::_('select.option',  $i, sprintf( "%02d" ,$i) );
			}
	
			$selectedStartHour 	= intval($startDate->toFormat('%H'));
			$selectedEndHour 	= intval($endDate->toFormat('%H'));		
		}
		$startHourSelect		= JHTML::_('select.genericlist',  $hours, 'starttime-hour', array('class'=>'required inputbox'), 'value', 'text', $selectedStartHour , false );
		$endHourSelect			= JHTML::_('select.genericlist',  $hours, 'endtime-hour', array('class'=>'required inputbox'), 'value', 'text', $selectedEndHour , false );
		
		$minutes	= array();
		$minutes[]	= JHTML::_('select.option',  0, "00" );
		$minutes[]	= JHTML::_('select.option',  15, "15" );
		$minutes[]	= JHTML::_('select.option',  30, "30" );
		$minutes[] 	= JHTML::_('select.option',  45, "45" );

		$startMinSelect		= JHTML::_('select.genericlist',  $minutes , 'starttime-min', array('class'=>'required inputbox'), 'value', 'text', $startDate->toFormat('%M') , false );
		$endMinSelect		= JHTML::_('select.genericlist',  $minutes , 'endtime-min', array('class'=>'required inputbox'), 'value', 'text', $endDate->toFormat('%M' ) , false );
        
		// Load category tree
		CFactory::load('helpers','category');
		$cTree	= CCategoryHelper::getCategories($categories);
		$lists['categoryid']		= JHTML::_('select.genericlist',   $cTree, 'catid', 'size="1"', 'id', 'nodeText',$event->catid ); 
		
		$tmpl	= new CTemplate();
		$tmpl->set( 'startDate'	, $startDate );
		$tmpl->set( 'endDate'	, $endDate );
		$tmpl->set( 'startHourSelect'	, $startHourSelect );
		$tmpl->set( 'endHourSelect'		, $endHourSelect );
		$tmpl->set( 'startMinSelect'	, $startMinSelect );
		$tmpl->set( 'endMinSelect'		, $endMinSelect );
		$tmpl->set( 'startAmPmSelect'	, $startAmPmSelect );
		$tmpl->set( 'endAmPmSelect'		, $endAmPmSelect );
		
		$tmpl->set( 'timezones'	, $timezones );
		$tmpl->set('config'		, $config );
		$tmpl->set( 'lists'	, $lists );
		$tmpl->set('categories' 	, $categories );
		$tmpl->set('event'		, $event );
		$tmpl->set('editor'		, $editor);
		$tmpl->set( 'helper'		, $helper );
		$tmpl->set('now'		, $now->toFormat('%Y-%m-%d') );
		$tmpl->set('eventCreated'	, $totalEventCount );
		$tmpl->set('eventcreatelimit'	, $config->get('eventcreatelimit') );

		echo $tmpl->fetch( 'events.forms' );
	}
	
	/**
	 * Display the form of the event import and the listing of events users can import
	 * from the calendar file.	 
	 **/	 	
	public function import( $events )
	{
		if(!$this->accessAllowed('registered'))
		{
			return;
		}
	
		$document 	= JFactory::getDocument();
		$config		= CFactory::getConfig();
		$document->setTitle( JText::_('CC EVENTS IMPORT FROM ICAL') );
		
		$this->showSubmenu();
		$model		= CFactory::getModel( 'events' );
		$categories	= $model->getCategories();	

		$tmpl	= new CTemplate();
		$tmpl->set( 'events' , $events );
		$tmpl->set( 'categories' , $categories );
		echo $tmpl->fetch( 'events.import' );
	}
	
	/**
	 * Displays the create event form
	 **/	 	
	function create($event)
	{
		if(!$this->accessAllowed('registered'))
		{
			return;
		}

		$document 	= JFactory::getDocument();
		$config		= CFactory::getConfig();
		$mainframe	= JFactory::getApplication();
		CFactory::load( 'helpers' , 'owner' );		
		CFactory::load( 'helpers' , 'event' );
		$handler	= CEventHelper::getHandler( $event );

		if( !$handler->creatable() )
		{
			$document->setTitle( '' );
			$mainframe->enqueueMessage( JText::_('CC EVENT CREATION DISABLED'), 'error');
			return;
		}		
		
		$this->addPathway( JText::_('CC EVENTS') , CRoute::_('index.php?option=com_community&view=events') );
		$this->addPathway( JText::_('CC CREATE EVENTS TITLE') , '' );
		$document->setTitle(JText::_('CC CREATE EVENTS TITLE'));
		
		$js	= 'assets/validate-1.5'.(( $config->getBool('usepackedjavascript') ) ? '.pack.js' : '.js');
		CAssets::attach($js, 'js');
		
		$this->showSubmenu();
		$this->_displayForm($event);
		return;		
	}
	
	function edit($event)
	{		
		if(!$this->accessAllowed('registered'))
			return;
	    $document 	= JFactory::getDocument();
	    $config		= CFactory::getConfig();
		$document->setTitle(JText::_('CC EDIT EVENTS TITLE'));

		$this->addPathway( JText::_('CC EVENTS') , CRoute::_('index.php?option=com_community&view=events') );
		$this->addPathway( JText::_('CC EDIT EVENTS TITLE') , '' );
        
        $file	= 'assets/validate-1.5';
        $file	.= $config->getBool('usepackedjavascript') ? '.pack.js' : '.js';
        
        CAssets::attach( $file , 'js' );

		
		if(!$this->accessAllowed('registered') )
		{
			echo JText::_( 'CC ACCESS FORBIDDEN' );
			return;
		}

		$this->showSubmenu();
		$this->_displayForm($event);
		return;
	}
	
	public function printpopup($event)
	{
		$config = CFactory::getConfig();
		$my 	= CFactory::getUser();
		// We need to attach the javascirpt manually
		
		$js = JURI::root().'components/com_community/assets/joms.jquery';
		$js	.= ( $config->getBool('usepackedjavascript') ) ? '.pack.js' : '.js';
		$script  = '<script type="text/javascript" src="'.$js.'"></script>';
		
		$js	= JURI::root().'components/com_community/assets/script-1.2';
		$js	.= ( $config->getBool('usepackedjavascript') ) ? '.pack.js' : '.js';
		
		$script .= '<script type="text/javascript" src="'.$js.'"></script>'; 
		
		$creator = CFactory::getUser($event->creator);
		$creatorUtcOffset = $creator->getUtcOffset();
		$creatorUtcOffsetStr = CTimeHelper::getTimezone( $creator->getTimezone() );

		// Output to template
        $tmpl		= new CTemplate();
        
        $tmpl->set( 'my'		, $my);
        $tmpl->set( 'event' 	, $event );
        $tmpl->set( 'script'	, $script);
        $tmpl->set( 'creatorUtcOffset' 		, $creatorUtcOffset );
        $tmpl->set( 'creatorUtcOffsetStr'	, $creatorUtcOffsetStr);

        echo $tmpl->fetch( 'events.print' );
	}
	
	/**
	 * Responsible for displaying the event page.
	 **/	 	
	function viewevent()
	{
		$mainframe	= JFactory::getApplication();
		$document 	= JFactory::getDocument();
		$config		= CFactory::getConfig();
		$my		= CFactory::getUser();
		
		CFactory::load( 'libraries' , 'tooltip' );
		CFactory::load( 'libraries' , 'wall' );
		CFactory::load( 'libraries' , 'window' );
		CWindow::load();

		$eventid	=	JRequest::getInt( 'eventid' , 0 );
		$eventModel	=&	CFactory::getModel( 'events' );
		$event		=&	JTable::getInstance( 'Event' , 'CTable' );
		CFactory::load( 'helpers' , 'event' );
		$handler	= CEventHelper::getHandler( $event );
		$event->load($eventid);
		
		if( !$handler->exists() )
		{
			$mainframe->enqueueMessage( JText::_('CC EVENTS NOT AVAILABLE'), 'error');
			return;
		}
		
		if( !$handler->browsable() )
		{
			echo JText::_('CC NOT ALLOWED TO ACCESS SECTION' );
			return;
		}
		
		// @rule: Test if the group is unpublished, don't display it at all.
		if( !$event->published )
		{
			echo JText::_('CC EVENTS UNDER MODERATION' );
			return;
		}
		$this->showSubmenu();
		$event->hit();

		// Basic page presentation
		if ($event->type=='group')
		{
			$groupId = $event->contentid;
			$group =& JTable::getInstance( 'Group' , 'CTable' );
			$group->load( $groupId );
		
			// Set pathway for group videos
			// Community > Groups > Group Name > Events
			$this->addPathway( JText::_('CC GROUPS'), CRoute::_('index.php?option=com_community&view=groups') );
			$this->addPathway( $group->name, CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupId));
		}

		$this->addPathway( JText::_('CC EVENTS'), CRoute::_('index.php?option=com_community&view=events') );
		$this->addPathway( JText::sprintf('CC VIEW EVENTS TITLE', $event->title) , '' );
		
		// Permissions and privacies
		CFactory::load('helpers' , 'owner');
		$isEventGuest		= $event->isMember( $my->id );
		$isMine			= ($my->id == $event->creator);
		$isAdmin		= $event->isAdmin( $my->id );
		$isCommunityAdmin	= COwnerHelper::isCommunityAdmin();

		// Get Event Admins
		$eventAdmins		= $event->getAdmins( 12 , CC_RANDOMIZE );
		$eventAdminsCount	= $event->getAdminsCount();

		// Attach avatar of the admin
		for( $i = 0; ($i < count($eventAdmins)); $i++)
		{
			$row				=&	$eventAdmins[$i];
			$eventAdmins[$i]	=	CFactory::getUser( $row->id );
		}
		
		// Get Attending Event Guests
		$eventMembers			= $event->getMembers( COMMUNITY_EVENT_STATUS_ATTEND, 12 , CC_RANDOMIZE );
		$eventMembersCount		= $event->getMembersCount( COMMUNITY_EVENT_STATUS_ATTEND );

		// Get pending event guests
 		$pendingMembers	        = $event->getMembers( COMMUNITY_EVENT_STATUS_INVITED, 12 , CC_RANDOMIZE );
 		$pendingMembersCount	= $event->getMembersCount( COMMUNITY_EVENT_STATUS_INVITED );

		// Get blocked Event Guests
 		$blockedMembers	        = $event->getMembers( COMMUNITY_EVENT_STATUS_BLOCKED, 12 , CC_RANDOMIZE );
 		$blockedMembersCount	= $event->getMembersCount( COMMUNITY_EVENT_STATUS_BLOCKED );

		// Attach avatar of the admin
		// Pre-load multiple users at once
		$userids = array();
		foreach($eventMembers as $uid)
		{
			$userids[] = $uid->id;
		}
		CFactory::loadUsers($userids);

		for( $i = 0; ($i < count($eventMembers)); $i++)
		{
			$row	=& $eventMembers[$i];
			$eventMembers[$i]	= CFactory::getUser( $row->id );
		}


		// Pre-load multiple users at once
		$userids = array();
		foreach($pendingMembers as $uid)
		{
			$userids[] = $uid->id;
		}
		CFactory::loadUsers($userids);

		for( $i = 0; ($i < count($pendingMembers)); $i++)
		{
			$row	=& $pendingMembers[$i];
			$pendingMembers[$i]	= CFactory::getUser( $row->id );
		}

		// Pre-load multiple users at once
		$userids = array();
		foreach($blockedMembers as $uid)
		{
			$userids[] = $uid->id;
		}
		CFactory::loadUsers($userids);

		for( $i = 0; ($i < count($blockedMembers)); $i++)
		{
			$row	=& $blockedMembers[$i];
			$blockedMembers[$i]	= CFactory::getUser( $row->id );
		}

		$waitingApproval	    = $event->isPendingApproval( $my->id );
		$waitingRespond	        = false;

		$myStatus = $event->getUserStatus($my->id);
		
		$hasResponded = (($myStatus == COMMUNITY_EVENT_STATUS_ATTEND)
						|| ($myStatus == COMMUNITY_EVENT_STATUS_WONTATTEND)
						|| ($myStatus == COMMUNITY_EVENT_STATUS_MAYBE));
			
		// Get Bookmark HTML
		CFactory::load('libraries' , 'bookmarks');
		$bookmarks		= new CBookmarks(CRoute::getExternalURL( 'index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id ));
		$bookmarksHTML	= $bookmarks->getHTML();
		
		// Get Reporting HTML
		CFactory::load('libraries', 'reporting');
		$report		= new CReportingLibrary();
		$reportHTML	= $report->getReportingHTML( JText::_('CC REPORT EVENT') , 'events,reportEvent' , array( $event->id ) );
		
		// Get the Wall
		CFactory::load( 'libraries' , 'wall' );
		$wallContent	= CWallLibrary::getWallContents( 'events' , $event->id , $isAdmin , 10 ,0 , 'wall.content' , 'events,events');
		$wallCount		= CWallLibrary::getWallCount('events', $event->id);
		$viewAllLink	= false;

		if(JRequest::getVar('task', '', 'REQUEST') != 'app')
		{
			$viewAllLink	= CRoute::_('index.php?option=com_community&view=events&task=app&eventid=' . $event->id . '&app=walls');
		}
		$wallContent	.= CWallLibrary::getViewAllLinkHTML($viewAllLink, $wallCount);
		
		$wallForm		= '';
		if( !$config->get('lockeventwalls') 
			|| ($config->get('lockeventwalls') && ($isEventGuest) && !($waitingApproval) && $hasResponded) 
			|| $isCommunityAdmin )
		{
			$wallForm	= CWallLibrary::getWallInputForm( $event->id , 'events,ajaxSaveWall', 'events,ajaxRemoveWall' );
		}
		
		// Construct the RVSP radio list
		$arr = array(
			JHTML::_('select.option',  COMMUNITY_EVENT_STATUS_ATTEND, JText::_( 'CC EVENT ACTION ATTEND' ) ),
			JHTML::_('select.option',  COMMUNITY_EVENT_STATUS_WONTATTEND, JText::_( 'CC EVENT ACTION DECLINE' ) ),
			JHTML::_('select.option',  COMMUNITY_EVENT_STATUS_MAYBE, JText::_( 'CC EVENT ACTION UNSURE' ) )
		);
		$status		= $event->getMemberStatus($my->id);
		$radioList	= JHTML::_('select.radiolist',  $arr, 'status', '', 'value', 'text', $status, false );
		
		$unapprovedCount = $event->inviteRequestCount();
		//...
		$editEvent		= JRequest::getVar( 'edit' , false , 'GET' );
		$editEvent		= ( $editEvent == 1 ) ? true : false;

		// Am I invited in this event?
		$isInvited  	= false;
		$join	    	= '';
		$friendsCount	= 0;
		$isInvited  	= $eventModel->isInvitedMe(0, $my->id, $event->id);

		// If I was invited, I want to know my invitation informations
		if( $isInvited )
		{
		     $invitor	=   CFactory::getUser( $isInvited[0]->invited_by );
		     $join	=   '<a href="' . CUrlHelper::userLink( $invitor->id ) . '">' . $invitor->getDisplayName() . '</a>';

		     // Get users friends in this group
		     $friendsCount  =	$eventModel->getFriendsCount( $my->id, $event->id );
		}

		$friendsModel	= CFactory::getModel( 'Friends' );
		$tmpFriends		= $friendsModel->getFriends( $my->id , 'name' , false);

		$friends		= array();

		for( $i = 0; $i < count( $tmpFriends ); $i++ )
		{
			$friend			=& $tmpFriends[ $i ];

			if( !$event->isMember( $friend->id ) )
			{
				$friends[]	= $friend;
			}
		}
		unset( $tmpFriends );

		// Get like
		CFactory::load( 'libraries' , 'like' );
		$likes	    =	new CLike();
		$likesHTML  =	$likes->getHTML( 'events', $event->id, $my->id );

		// Is this event is a past event?
		$now		=   new JDate();
		$isPastEvent	=   ( $event->getEndDate( false )->toMySQL() < $now->toMySQL(true) ) ? true : false;
				
		// Output to template
		$tmpl		= new CTemplate();

		CFactory::load( 'libraries' , 'invitation' );
		$inviteHTML			= CInvitation::getHTML( $friends , 'events,inviteUsers' , $event->id , CInvitation::SHOW_FRIENDS , CInvitation::HIDE_EMAIL );

		$tmpl->setMetaTags( 'event'		, $event );
		$tmpl->set( 'timezone'			, CTimeHelper::getTimezone( $event->offset ) );
		$tmpl->set( 'handler'			, $handler );
		$tmpl->set( 'likesHTML'			, $likesHTML );
		$tmpl->set( 'inviteHTML'		, $inviteHTML );
		$tmpl->set( 'guestStatus'		, $event->getUserStatus($my->id));

		$tmpl->set( 'event'			, $event );
		$tmpl->set( 'radioList'			, $radioList );
		$tmpl->set( 'bookmarksHTML'		, $bookmarksHTML );
		$tmpl->set( 'reportHTML'		, $reportHTML );
		$tmpl->set( 'isEventGuest'		, $isEventGuest );
		$tmpl->set( 'isMine'			, $isMine );
		$tmpl->set( 'isAdmin'			, $isAdmin );
		$tmpl->set( 'isCommunityAdmin'		, $isCommunityAdmin );
		$tmpl->set( 'unapproved'		, $unapprovedCount );
		$tmpl->set( 'waitingApproval'		, $waitingApproval );
		$tmpl->set( 'wallForm'			, $wallForm );
		$tmpl->set( 'wallContent'		, $wallContent );
		$tmpl->set( 'eventAdmins'		, $eventAdmins );
		$tmpl->set( 'eventAdminsCount'		, $eventAdminsCount );
		$tmpl->set( 'eventMembers'		, $eventMembers );
		$tmpl->set( 'eventMembersCount'		, $eventMembersCount );
		$tmpl->set( 'blockedMembers'		, $blockedMembers );
		$tmpl->set( 'blockedMembersCount'	, $blockedMembersCount);
		$tmpl->set( 'pendingMembers'		, $pendingMembers );
		$tmpl->set( 'pendingMembersCount'	, $pendingMembersCount );
		$tmpl->set( 'editEvent'			, $editEvent );
		$tmpl->set( 'my'			, $my );
		$tmpl->set( 'memberStatus'		, $myStatus );
		$tmpl->set( 'waitingRespond'		, $waitingRespond );
		$tmpl->set( 'isInvited'			, $isInvited );
		$tmpl->set( 'join'			, $join );
		$tmpl->set( 'friendsCount'		, $friendsCount );
		$tmpl->set( 'isPastEvent'		, $isPastEvent );

		echo $tmpl->fetch( 'events.viewevent' );
	}
	
	/**
	 * Responsible to output the html codes for the task viewguest.
	 * Outputs html codes for the viewguest page.
	 * 	 
	 * @return	none.	 
	 **/	 	 	
	function viewguest()
	{
		if(!$this->accessAllowed('registered'))
		{
			return;
		}
		
		$mainframe	= JFactory::getApplication();
	    $document 	= JFactory::getDocument();
	    $config		= CFactory::getConfig();
		$my			= CFactory::getUser();
		$id			= JRequest::getInt( 'eventid' , 0 );
		$type		= JRequest::getCmd('type');
		$approval	= JRequest::getCmd('approve');
		$event		= JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $id );
		
		CFactory::load( 'helpers' , 'event' );
		$handler	= CEventHelper::getHandler( $event );
		$types		= array( COMMUNITY_EVENT_ADMINISTRATOR , COMMUNITY_EVENT_STATUS_INVITED , COMMUNITY_EVENT_STATUS_ATTEND , COMMUNITY_EVENT_STATUS_BLOCKED , COMMUNITY_EVENT_STATUS_REQUESTINVITE );

		if( !in_array( $type , $types ) )
		{
			JError::raiseError( '500' , JText::_( 'Invalid status type' ) );
		}
		
		// Set the guest type for the title purpose
		switch ( $type )
		{
			case COMMUNITY_EVENT_ADMINISTRATOR:
				$guestType = JText::_('CC ADMINS');
			break;
			case COMMUNITY_EVENT_STATUS_INVITED:
				$guestType = JText::_('CC PENDING GUESTS');
			break;
			case COMMUNITY_EVENT_STATUS_ATTEND:
				$guestType = JText::_('CC CONFIRMED GUESTS');
			break;
			case COMMUNITY_EVENT_STATUS_BLOCKED:
				$guestType = JText::_('CC BLOCKED GUESTS');
			break;
			case COMMUNITY_EVENT_STATUS_REQUESTINVITE:
				$guestType = JText::_('CC REQUESTED INVITATION');
			break;
		}
		
		// Then we load basic page presentation
		$this->addPathway( JText::_('CC EVENTS') , CRoute::_('index.php?option=com_community&view=events') );
		$this->addPathway( JText::sprintf('CC VIEW EVENTS TITLE', $event->title) , '' );

		// Set the specific title
        $document->setTitle(JText::sprintf("CC EVENT VIEW GUESTLIST" , $event->title, $guestType ));
        

		CFactory::load( 'helpers' , 'owner' );
		$status			= $event->getUserStatus($my->id);
		$allowed		= array( COMMUNITY_EVENT_STATUS_INVITED , COMMUNITY_EVENT_STATUS_ATTEND , COMMUNITY_EVENT_STATUS_WONTATTEND , COMMUNITY_EVENT_STATUS_MAYBE );
		$accessAllowed	= ( ( in_array( $status , $allowed ) ) && $status != COMMUNITY_EVENT_STATUS_BLOCKED ) ? true : false;

		if( $handler->hasInvitation() && ( ( $accessAllowed && $event->allowinvite ) || $event->isAdmin( $my->id ) || COwnerHelper::isCommunityAdmin() ) )
		{
			$this->addSubmenuItem('index.php?option=com_community&view=events&task=invitefriends&eventid=' . $event->id, JText::_('CC TAB INVITE') , '' , SUBMENU_RIGHT );        
		}
		$this->showSubmenu();

		$isSuperAdmin	= COwnerHelper::isCommunityAdmin();    
        
        // status = unsure | noreply | accepted | declined | blocked
        // permission = admin | guest |  

        if( $type == COMMUNITY_EVENT_ADMINISTRATOR)
        {
        	$guestsIds		= $event->getAdmins( 0 );
		}
		else
		{
			$guestsIds		= $event->getMembers( $type , 0 , false, $approval);
		}
        
        $guests         = array();

		// Pre-load multiple users at once
		$userids = array();
		foreach($guestsIds as $uid){ $userids[] = $uid->id; }
		CFactory::loadUsers($userids);
		
        for ($i=0; $i < count($guestsIds); $i++)
        {
			$guests[$i]	= CFactory::getUser($guestsIds[$i]->id);
			$guests[$i]->friendsCount	= $guests[$i]->getFriendCount();
			$guests[$i]->isMe			= ( $my->id == $guests[$i]->id ) ? true : false;
			$guests[$i]->isAdmin		= $event->isAdmin($guests[$i]->id);
			$guests[$i]->statusType		= $guestsIds[$i]->statusCode;
		}
		
		$pagination		= $event->getPagination();

        // Output to template
        $tmpl		= new CTemplate();
        $tmpl->set( 'event'		, $event);
        $tmpl->set( 'handler'	, $handler );
        $tmpl->set( 'guests' 	, $guests );
        $tmpl->set( 'eventid'	, $event->id );
        $tmpl->set( 'isMine'	, $event->isCreator($my->id));
        $tmpl->set( 'isSuperAdmin', $isSuperAdmin );
        $tmpl->set( 'pagination', $pagination );
        $tmpl->set( 'my'		, $my );
        $tmpl->set( 'config'	, $config );
        echo $tmpl->fetch( 'events.viewguest' );
	}
	
	function search()
	{
		CFactory::load( 'helpers' , 'event' );
		
		// Get the document object and set the necessary properties of the document
		$document	=& JFactory::getDocument();
		$this->addPathway( JText::_('CC EVENTS') , CRoute::_('index.php?option=com_community&view=events') );
		$this->addPathway( JText::_('CC SEARCH EVENTS') , '' );
		$document->setTitle(JText::_('CC SEARCH EVENTS TITLE'));

		$mainframe	=& JFactory::getApplication();
		$script = '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>';
		$mainframe->addCustomHeadTag( $script );
		
		// Display the submenu
		$this->showSubmenu();

		//New search features
		$model		=   CFactory::getModel( 'events' );
		$categories	=   $model->getCategories();

		// input filtered to remove tags
		$search		=   JRequest::getVar( 'search' , '' );

		// Input for advance search
		$catId		=   JRequest::getInt( 'catid', '' );

		$category	=& JTable::getInstance( 'EventCategory' , 'CTable' );
		$category->load( $catId );
		
		$advance		    =   array();
		$advance['startdate']	    =   JRequest::getVar( 'startdate', '' );
		$advance['enddate']	    =   JRequest::getVar( 'enddate', '' );
		$advance['radius']	    =   JRequest::getVar( 'radius', '' );
		$advance['fromlocation']    =   JRequest::getVar( 'location', '' );
		
		$events		= '';
		$pagination	= null;
		$posted		= false;
		$count		= 0;
		$eventsHTML	= '';

		// Test if there are any post requests made
		if( !empty($search) || (!empty($advance['startdate']) || !empty($advance['enddate']) || !empty($advance['radius']) || !empty($advance['fromlocation'])) )
		{
			// Check for request forgeries
			JRequest::checkToken('get') or jexit( JText::_( 'CC INVALID TOKEN' ) );

			CFactory::load( 'libraries' , 'apps' );
			$appsLib	=&  CAppPlugins::getInstance();
			$saveSuccess	=   $appsLib->triggerEvent( 'onFormSave' , array('jsform-events-search' ));
			
			if( empty($saveSuccess) || !in_array( false , $saveSuccess ) )
			{
				$posted	    = true;
				$events	    = $model->getEvents( $category->id, null , null , $search, null, null, null, $advance );
				$pagination = $model->getPagination();
				$count	    = $model->getEventsSearchTotal();
			}
		}
		
		// Get the template for the events lists
		$eventsHTML	= $this->_getEventsHTML( $events, false, $pagination );

		CFactory::load( 'libraries' , 'apps' );
		$app			=&  CAppPlugins::getInstance();
		$appFields		=   $app->triggerEvent('onFormDisplay' , array( 'jsform-events-search') );
		$beforeFormDisplay	=   CFormElement::renderElements( $appFields , 'before' );
		$afterFormDisplay	=   CFormElement::renderElements( $appFields , 'after' );

		$tmpl	=   new CTemplate();
		$tmpl->set( 'beforeFormDisplay' , $beforeFormDisplay );
		$tmpl->set( 'afterFormDisplay'	, $afterFormDisplay );
		$tmpl->set( 'posted'		, $posted );
		$tmpl->set( 'eventsCount'	, $count );
		$tmpl->set( 'eventsHTML'	, $eventsHTML );
		$tmpl->set( 'search'		, $search );
		$tmpl->set( 'catId'		, $category->id );
		$tmpl->set( 'categories'	, $categories );
		$tmpl->set( 'advance'		, $advance );

		echo $tmpl->fetch( 'events.search' );
	}
	
	/**
	 * An event has just been created, should we just show the album ?
	 */
	function created()
	{

		$eventid 	=  JRequest::getInt( 'eventid', 0 );

		CFactory::load( 'models' , 'events');
		$event		= JTable::getInstance( 'Event' , 'CTable' );

		$event->load($eventid);
		$document = JFactory::getDocument();
        $document->setTitle( $event->title );

        $uri	= JURI::base();
		$this->showSubmenu();

		$tmpl	= new CTemplate();

		$tmpl->set( 'link'			, CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid='.$event->id));
		$tmpl->set( 'linkUpload'	, CRoute::_('index.php?option=com_community&view=events&task=uploadavatar&eventid='.$event->id));
		$tmpl->set( 'linkEdit'		, CRoute::_('index.php?option=com_community&view=events&task=edit&eventid=' . $event->id ) );
		$tmpl->set( 'linkInvite'	, CRoute::_('index.php?option=com_community&view=events&task=invitefriends&eventid=' . $event->id ) );

		echo $tmpl->fetch( 'events.created' );
	}
	
	function sendmail()
	{
		$document =& JFactory::getDocument();
        $document->setTitle(JText::_('CC SEND EMAIL TO PARTICIPANTS'));
        $this->addPathway( JText::_('CC EVENTS') , CRoute::_('index.php?option=com_community&view=events') );
		$this->addPathway( JText::_('CC SEND EMAIL TO PARTICIPANTS') );
		
        if(!$this->accessAllowed('registered'))
		{
			return;
		}
		
		// Display the submenu
		$this->showSubmenu();
        $eventId	= JRequest::getInt('eventid' , '' );
		
		CFactory::load( 'helpers', 'owner' );		
		CFactory::load( 'models' , 'events' );
		$event		=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventId );

		if( empty($eventId ) || empty( $event->title) )
		{
			echo JText::_('CC INVALID ID PROVIDED');
			return;
		}
		
		$my			= CFactory::getUser();
		$config		= CFactory::getConfig();

		jimport( 'joomla.html.editor' );
		$editor		= new JEditor( $config->get( 'htmleditor' ) );
		
		CFactory::load( 'helpers' , 'event' );
		$handler	= CEventHelper::getHandler( $event );
		if( !$handler->manageable() )
		{
			$this->noAccess();
			return;
		}

		$message	= JRequest::getVar( 'message' , '' , 'post' , 'string' , JREQUEST_ALLOWRAW );
		$title		= JRequest::getVar( 'title'	, '' );
		
        $tmpl		= new CTemplate();
        $tmpl->set( 'editor'	, $editor );
        $tmpl->set( 'event' , $event );
		$tmpl->set( 'message' , $message );
		$tmpl->set( 'title' , $title );
        echo $tmpl->fetch( 'events.sendmail' );
	}
	
	function uploadAvatar()
	{
		$document =& JFactory::getDocument();
        $document->setTitle(JText::_('CC UPLOAD EVENT AVATAR'));
        
		$eventid    = JRequest::getVar('eventid', '0');
		$this->_addEventInPathway( $eventid );
		$this->addPathway( JText::_('CC UPLOAD EVENT AVATAR') );

		$this->showSubmenu();
		$event		=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventid );
		
		CFactory::load( 'helpers' , 'event' );
		$handler	= CEventHelper::getHandler( $event );
		if( !$handler->manageable() )
		{
			$this->noAccess();
			return;
		}
		
		$config			= CFactory::getConfig();
		$uploadLimit	= (double) $config->get('maxuploadsize');
		$uploadLimit	.= 'MB';

		CFactory::load( 'models' , 'events' );
		$event	=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventid );

		CFactory::load( 'libraries' , 'apps' );
		$app 		=& CAppPlugins::getInstance();
		$appFields	= $app->triggerEvent('onFormDisplay' , array( 'jsform-events-uploadavatar') );
		$beforeFormDisplay	= CFormElement::renderElements( $appFields , 'before' );
		$afterFormDisplay	= CFormElement::renderElements( $appFields , 'after' );

		$tmpl	= new CTemplate();
		$tmpl->set( 'beforeFormDisplay', $beforeFormDisplay );
		$tmpl->set( 'afterFormDisplay'	, $afterFormDisplay );
		$tmpl->set( 'eventId' 	, $eventid );
		$tmpl->set( 'avatar'	, $event->getAvatar('avatar') );
		$tmpl->set( 'thumbnail' , $event->getAvatar() );
		$tmpl->set( 'uploadLimit'	, $uploadLimit );

		echo $tmpl->fetch( 'events.uploadavatar' );
	}
	
	function _addEventInPathway( $eventId )
	{
		CFactory::load( 'models' , 'events' );
		$event			=& JTable::getInstance( 'Event' , 'CTable' );
		$event->load( $eventId );

		$this->addPathway( $event->title , CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id) );
	}
	
	function _getEventsHTML( $eventObjs, $isExpired = false, $pagination = NULL)
	{
		CFactory::load( 'helpers' , 'owner' );

		$events	= array();
		CFactory::load( 'models' , 'events' );

		if( $eventObjs )
		{
			foreach( $eventObjs as $row )
			{
				$event	    =&	JTable::getInstance( 'Event' , 'CTable' );
				$event->bind( $row );
				$events[]   =	$event;
			}
			unset($eventObjs);
		}

		$tmpl	= new CTemplate();
		$tmpl->set( 'isCommunityAdmin'	, COwnerHelper::isCommunityAdmin() );
		$tmpl->set( 'events'		, $events );
		$tmpl->set( 'isExpired'		, $isExpired );
		$tmpl->set( 'pagination'	, $pagination );
		$eventsHTML	= $tmpl->fetch( 'events.list' );
		unset( $tmpl );

		return $eventsHTML;
	}
}
