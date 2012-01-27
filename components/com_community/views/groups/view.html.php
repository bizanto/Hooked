<?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

class CommunityViewGroups extends CommunityView
{
	function _addGroupInPathway( $groupId )
	{
		CFactory::load( 'models' , 'groups' );
		$group			=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $groupId );
        
		$this->addPathway( JText::_('CC GROUPS') , CRoute::_('index.php?option=com_community&view=groups') );
		$this->addPathway( $group->name , CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id) ); 
	}
	
	function sendmail()
	{
		$document =& JFactory::getDocument();
		$document->setTitle(JText::_('CC SEND EMAIL TO GROUP MEMBERS'));
        
		$id	=   JRequest::getInt( 'groupid' , 0 );

		$group	=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $id );
        
		if( $id == 0 )
		{
			echo JText::_('CC ACCESS FORBIDDEN');
			return;
		}
		
		$this->addPathway( JText::_('CC GROUPS') , CRoute::_('index.php?option=com_community&view=groups') );
		$this->addPathway( $group->name , CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id ) );
		$this->addPathway( JText::_('CC SEND EMAIL TO GROUP MEMBERS') );
		
		if(!$this->accessAllowed('registered'))
		{
			return;
		}
		
		// Display the submenu
		$this->showSubmenu();
		
		CFactory::load( 'helpers', 'owner' );		
		CFactory::load( 'models' , 'events' );
		
		$my			= CFactory::getUser();
		$config		= CFactory::getConfig();

		jimport( 'joomla.html.editor' );
		$editor		= new JEditor( $config->get( 'htmleditor' ) );
		
		if( !$group->isAdmin($my->id) && !COwnerHelper::isCommunityAdmin() )
		{
			$this->noAccess();
			return;
		}

		$message	= JRequest::getVar( 'message' , '' , 'post' , 'string' , JREQUEST_ALLOWRAW );
		$title		= JRequest::getVar( 'title'	, '' );
		
		$tmpl		= new CTemplate();
		$tmpl->set( 'editor'	, $editor );
		$tmpl->set( 'group' , $group );
		$tmpl->set( 'message' , $message );
		$tmpl->set( 'title' , $title );
		echo $tmpl->fetch( 'groups.sendmail' );
	
	}
	
	function _addSubmenu()
	{

		$task		=   JRequest::getVar( 'task' , '' , 'GET' );
		$config		=&  CFactory::getConfig();
		$groupid	=   JRequest::getInt( 'groupid' , '' , 'GET' );
		$categoryid	=   JRequest::getInt( 'categoryid' , '' , 'GET' );
		$my			=&  CFactory::getUser();

		
		$backLink	=   array( 'sendmail','invitefriends', 'viewmembers' , 'viewdiscussion' , 'viewdiscussions' , 'editdiscussion' ,'viewbulletins', 'adddiscussion' , 'addnews' , 'viewbulletin', 'uploadavatar' , 'edit', 'banlist');

		$groupsModel	=&  CFactory::getModel( 'groups' );
		$isAdmin	=   $groupsModel->isAdmin( $my->id , $groupid );
		$isSuperAdmin	=   COwnerHelper::isCommunityAdmin();
		
		// Load the group table.
		$group		=&  JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $groupid );
		$isBanned		=   $group->isBanned( $my->id );
		
		if( in_array( $task , $backLink) )
		{
			$this->addSubmenuItem('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $groupid, JText::_('CC BACK TO GROUP'));
			$this->addSubmenuItem('index.php?option=com_community&view=groups&task=viewmembers&groupid=' . $groupid, JText::_('CC GROUP ALL MEMBERS'));
			
			if( $isAdmin || $isSuperAdmin )
				$this->addSubmenuItem('index.php?option=com_community&view=groups&task=banlist&list=' . COMMUNITY_GROUP_BANNED . '&groupid=' . $groupid, JText::_('CC GROUP BANNED MEMBERS'));
				
			if( $task == 'viewdiscussions' && !$isBanned )
				$this->addSubmenuItem('index.php?option=com_community&view=groups&groupid=' . $groupid . '&task=adddiscussion', JText::_('CC ADD DISCUSSION') , '' , SUBMENU_RIGHT );
				
			if( $task == 'viewmembers' && !$isBanned )
				$this->addSubmenuItem('index.php?option=com_community&view=groups&task=invitefriends&groupid=' . $groupid, JText::_('CC TAB INVITE') , '' , SUBMENU_RIGHT );
		}
		else
		{
			$this->addSubmenuItem('index.php?option=com_community&view=groups', JText::_('CC GROUP LISTING'));
			
			if(COwnerHelper::isRegisteredUser())
			{
				$this->addSubmenuItem('index.php?option=com_community&view=groups&task=mygroups&userid=' . $my->id , JText::_('CC MY GROUPS'));
				$this->addSubmenuItem('index.php?option=com_community&view=groups&task=myinvites&userid=' . $my->id , JText::_('CC GROUP PENDING INVITATIONS'));
			}	

			if( $config->get('creategroups')  &&  ( $isSuperAdmin || (COwnerHelper::isRegisteredUser() && $my->canCreateGroups() ) ) )
			{
				$creationLink = $categoryid ? 'index.php?option=com_community&view=groups&task=create&categoryid=' . $categoryid : 'index.php?option=com_community&view=groups&task=create';
				$this->addSubmenuItem( $creationLink, JText::_('CC CREATE GROUP') , '' , SUBMENU_RIGHT );
			}

			if( (!$config->get('enableguestsearchgroups') && COwnerHelper::isRegisteredUser()  ) || $config->get('enableguestsearchgroups') )
			{
				$tmpl = new CTemplate();
				$tmpl->set( 'url', CRoute::_('index.php?option=com_community&view=groups&task=search') );
				$html = $tmpl->fetch( 'groups.search.submenu' );
				$this->addSubmenuItem('index.php?option=com_community&view=groups&task=search', JText::_('CC SEARCH GROUP'), 'joms.groups.toggleSearchSubmenu(this)', false, $html);
			}

		}
	}

	function showSubmenu()
	{
		$this->_addSubmenu();
		parent::showSubmenu();
	}

	/**
	 * Display invite form
	 **/
	function invitefriends()
	{
		$document	=& JFactory::getDocument();
		$document->setTitle( JText::_('CC INVITE FRIENDS TO GROUP TITLE') );

		if( !$this->accessAllowed( 'registered' ) )
		{
			return;
		}
		
		$this->showSubmenu();
		
		$my				= CFactory::getUser();
		$groupId		= JRequest::getInt( 'groupid' , '' , 'GET' );
		$this->_addGroupInPathway( $groupId );
		$this->addPathway( JText::_('CC INVITE FRIENDS TO GROUP TITLE') );

		$friendsModel	= CFactory::getModel( 'Friends' );
		$groupsModel	= CFactory::getModel( 'Groups' );
						
		$tmpFriends		= $friendsModel->getFriends( $my->id , 'name' , false);				
		
		$friends		= array();
		
		for( $i = 0; $i < count( $tmpFriends ); $i++ )
		{
			$friend			=& $tmpFriends[ $i ];
			$groupInvite	=& JTable::getInstance( 'GroupInvite' , 'CTable' );
			$groupInvite->load( $groupId , $friend->id );

			if( !$groupsModel->isMember( $friend->id , $groupId ) && !$groupInvite->exists() )
			{
				$friends[]	= $friend;
			}
		}
		unset( $tmpFriends );

		$group			=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $groupId );
		
		$tmpl			= new CTemplate();
		$tmpl->set( 'friends' , $friends );
		$tmpl->set( 'group' , $group );
		echo $tmpl->fetch( 'groups.invitefriends' );
	}

	function edit()
	{
		$config		= CFactory::getConfig();
		$document 	=& JFactory::getDocument();
        $document->setTitle( JText::_('CC EDIT GROUP TITLE') );
        

		$this->showSubmenu();
  		
		$js	= 'assets/validate-1.5'.(( $config->getBool('usepackedjavascript') ) ? '.pack.js' : '.js');
        CAssets::attach($js, 'js');
        
        $groupId		= JRequest::getInt( 'groupid' , '' , 'REQUEST' );
		$groupModel		= CFactory::getModel( 'Groups' );
		$categories		= $groupModel->getCategories();
		$group			=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $groupId );
		
		$this->_addGroupInPathway( $group->id );
		$this->addPathway( JText::_('CC EDIT GROUP TITLE') );

		CFactory::load( 'libraries' , 'apps' );
		$app 		=& CAppPlugins::getInstance();
		$appFields	= $app->triggerEvent('onFormDisplay' , array('jsform-groups-forms'));
		$beforeFormDisplay	= CFormElement::renderElements( $appFields , 'before' );
		$afterFormDisplay	= CFormElement::renderElements( $appFields , 'after' ); 

		// Load category tree
		CFactory::load('helpers','category');
		$cTree	= CCategoryHelper::getCategories($categories);
		$lists['categoryid']		= JHTML::_('select.genericlist',   $cTree, 'categoryid', 'size="1"', 'id', 'nodeText',$group->categoryid ); 
	
		$tmpl	= new CTemplate();
		$tmpl->set( 'beforeFormDisplay', $beforeFormDisplay );
		$tmpl->set( 'afterFormDisplay'	, $afterFormDisplay );
		$tmpl->set( 'config'	, $config ); 
		$tmpl->set( 'lists'	, $lists );
		$tmpl->set( 'categories', $categories );
		$tmpl->set( 'group' 	, $group );
		$tmpl->set( 'params'	, $group->getParams() );
		$tmpl->set( 'isNew'		, false );
		
		echo $tmpl->fetch( 'groups.forms' );
	}

	/**
	 * Method to display group creation form
	 **/
	function create( $data )
	{
		$config		= CFactory::getConfig();
		$document	=& JFactory::getDocument();
		$document->setTitle( JText::_('CC CREATE NEW GROUP TITLE') );
		
		$js	= 'assets/validate-1.5'.(( $config->getBool('usepackedjavascript') ) ? '.pack.js' : '.js');
        CAssets::attach($js, 'js');
        
		$this->showSubmenu();

		$my		= CFactory::getUser();
		$model		= CFactory::getModel( 'groups' );					
		$totalGroup	= $model->getGroupsCreationCount($my->id);
				
		//initialize default value
		$group			=& JTable::getInstance( 'Group' , 'CTable' );
		$group->name 			= JRequest::getVar('name', '', 'POST');
		$group->description		= JRequest::getVar('description', '', 'POST');
		$group->email			= JRequest::getVar('email', '', 'POST');
		$group->website 		= JRequest::getVar('website', '', 'POST');
		$group->categoryid		= JRequest::getVar('categoryid', '');

		CFactory::load( 'libraries' , 'apps' );
		$app 		=& CAppPlugins::getInstance();
		$appFields	= $app->triggerEvent('onFormDisplay' , array('jsform-groups-form'));
		$beforeFormDisplay	= CFormElement::renderElements( $appFields , 'before' );
		$afterFormDisplay	= CFormElement::renderElements( $appFields , 'after' );  

		// Load category tree
		CFactory::load('helpers','category');
		$cTree	= CCategoryHelper::getCategories($data->categories);
		$lists['categoryid']		= JHTML::_('select.genericlist',   $cTree, 'categoryid', 'size="1"', 'id', 'nodeText' ); 

		$tmpl	= new CTemplate();
		$tmpl->set( 'beforeFormDisplay', $beforeFormDisplay );
		$tmpl->set( 'afterFormDisplay'	, $afterFormDisplay );
		$tmpl->set('config'				, $config );  
		$tmpl->set( 'lists'	, $lists );
		$tmpl->set('categories' 		, $data->categories );
		$tmpl->set('group'				, $group );
		$tmpl->set('groupCreated'		, $totalGroup );
		$tmpl->set('groupCreationLimit'	, $config->get('groupcreatelimit') );		
		$tmpl->set('params'				, $group->getParams() );
		$tmpl->set('isNew'				, true );

		echo $tmpl->fetch( 'groups.forms' );
	}

	/**
	 * A group has just been created, should we just show the album ?
	 */
	function created()
	{

		$groupid 	=  JRequest::getCmd( 'groupid', 0 );

		CFactory::load( 'models' , 'groups');
		$group		=& JTable::getInstance( 'Group' , 'CTable' );

		$group->load($groupid);
		$document =& JFactory::getDocument();
        $document->setTitle( $group->name );

        $uri	= JURI::base();
		$this->showSubmenu();

		$tmpl	= new CTemplate();

		$tmpl->set( 'link'			, CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$groupid));
		$tmpl->set( 'linkBulletin'	, CRoute::_('index.php?option=com_community&view=groups&task=addnews&groupid=' . $groupid) );
		$tmpl->set('linkUpload', CRoute::_('index.php?option=com_community&view=groups&task=uploadavatar&groupid='.$groupid));
		$tmpl->set( 'linkEdit'		, CRoute::_('index.php?option=com_community&view=groups&task=edit&groupid=' . $groupid ) );

		echo $tmpl->fetch( 'groups.created' );
	}

	/**
	 * Method to display output after saving group
	 *
	 * @param	JTable	Group JTable object
	 **/
	function save( $group )
	{
		$mainframe =& JFactory::getApplication();

		$document =& JFactory::getDocument();
        $document->setTitle(JText::_('CC UPLOAD GROUP AVATAR TITLE'));

        // Load submenus
        $this->showSubmenu();

		if( !$group->id )
		{
			$this->addWarning('CC SAVE GROUP ERROR');
			return;
		}
		$mainframe->enqueueMessage(JText::sprintf('CC NEW GROUP CREATED', $group->name));

		$tmpl	= new CTemplate();

		$tmpl->set( 'group'		, $group );

		echo $tmpl->fetch( 'groups.save' );
	} 

	/**
	 * Method to display listing of groups from the site
	 **/
	function display( $data )
	{
		$mainframe  =&	JFactory::getApplication();
		$document   =&	JFactory::getDocument();

		// Load required filterbar library that will be used to display the filtering and sorting.
		CFactory::load( 'libraries' , 'filterbar' );
		
		require_once( JPATH_COMPONENT . DS . 'libraries' . DS . 'activities.php');
		
		$model		=&  CFactory::getModel( 'groups' );
 		$avatarModel	=&  CFactory::getModel( 'avatar' );
		$wallsModel	=&  CFactory::getModel( 'wall' );

		// Get category id from the query string if there are any.
		$categoryId	=   JRequest::getInt( 'categoryid' , 0 );
		$category	=&  JTable::getInstance( 'GroupCategory' , 'CTable' );
		$category->load( $categoryId );


		if ($categoryId!=0)
		{
			$this->addPathway( JText::_('CC GROUPS'), CRoute::_('index.php?option=com_community&view=groups') );			
			$document->setTitle(JText::_('CC BROWSE GROUPS CATEGORIES'). ' : ' . $category->name);
		} else {
			$this->addPathway( JText::_('CC GROUPS') );			
			$document->setTitle(JText::_('CC BROWSE ALL GROUPS'));			
		}

		// If we are browing by category, add additional breadcrumb and add
		// category name in the page title
		/* begin: UNLIMITED LEVEL BREADCRUMBS PROCESSING */
		if( $category->parent == COMMUNITY_NO_PARENT )
		{
			$this->addPathway( $category->name , CRoute::_('index.php?option=com_community&view=groups&categoryid=' . $category->id ) );
		}
		else{
			// Parent Category
			$parentsInArray	=   array();
			$n		=   0;
			$parentId	=   $category->id;

			$parent	=&  JTable::getInstance( 'GroupCategory' , 'CTable' );

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
				$this->addPathway( $parentsInArray[$i]['name'], CRoute::_('index.php?option=com_community&view=groups&categoryid=' . $parentsInArray[$i]['id'] ) );
			}
		}
		/* end: UNLIMITED LEVEL BREADCRUMBS PROCESSING */


		$config	=&  CFactory::getConfig();
		$my	=   CFactory::getUser();
		$uri	=   JURI::base();

		$this->showSubmenu();   
         
		$feedLink = CRoute::_('index.php?option=com_community&view=groups&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" title="'. JText::_('CC SUBSCRIBE TO LATEST GROUPS FEED') .'"  href="'.$feedLink.'"/>'; 
		$document->addCustomTag( $feed );
		
		$feedLink = CRoute::_('index.php?option=com_community&view=groups&task=viewlatestdiscussions&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" title="'. JText::_('CC SUBSCRIBE TO LATEST GROUP DISCUSSIONS FEED') .'" href="'.$feedLink.'"/>';
		$document->addCustomTag( $feed );  

 		$data	    =	new stdClass();
		$sorted	    =	JRequest::getVar( 'sort' , 'latest' , 'GET' );
		$limitstart =	JRequest::getVar( 'limitstart' , 0 );

		$cache 	    =	CFactory::getCache('Core');

		if (!($data->categories = $cache->load('_groups_category_' . $category->id))){
 			// Get the categories
 			$data->categories	= $model->getCategories( $category->id  );
 			
 			$cache->save($data->categories, NULL, array(COMMUNITY_CACHE_TAG_GROUPS_CAT));
 		}
		
		// cache groups list.
		if (!($groupsHTML   = $cache->load('_groups_showAllGroups_' . $categoryId . '_' . $sorted . '_' . $limitstart))){
	 		// It is safe to pass 0 as the category id as the model itself checks for this value.
	 		$data->groups = $model->getAllGroups( $category->id , $sorted );
	
			// Get pagination object
			$data->pagination = $model->getPagination();
	
			// Get the template for the group lists
			$groupsHTML	= $this->_getGroupsHTML( $data->groups, $data->pagination );
			
			$cache->save($groupsHTML, NULL, array(COMMUNITY_CACHE_TAG_GROUPS));
		}

		$act = new CActivityStream();
	
		CFactory::load( 'helpers' , 'owner' );
		
		if (!($featuredList   = $cache->load('_groups_featuredList'))){
			CFactory::load( 'libraries' , 'featured' );
			$featured		= new CFeatured( FEATURED_GROUPS );
			$featuredGroups	= $featured->getItemIds();
			$featuredList	= array();
			
			foreach($featuredGroups as $group )
			{
				$table			=& JTable::getInstance( 'Group' , 'CTable' );
				$table->load($group);
				$featuredList[]	= $table;
			}
			
			$cache->save($featuredList, NULL, array(COMMUNITY_CACHE_TAG_GROUPS));
		}
		
		// getting group's latest discussion activities.
		$templateParams	= CTemplate::getTemplateParams();
		$discussions	=	$model->getGroupLatestDiscussion($categoryId , '' , $templateParams->get('sidebarTotalDiscussions') );

		$tmpl		= new CTemplate();

		$sortItems =  array(
				'latest' 	=> JText::_('CC GROUP SORT LATEST') ,
				'alphabetical'	=> JText::_('CC SORT ALPHABETICAL'),
				'mostdiscussed'	=> JText::_('CC GROUP SORT MOST DISCUSSED'),
				'mostwall'	=> JText::_('CC GROUP SORT MOST WALL POST'),
 				'mostmembers'	=> JText::_('CC GROUP SORT MOST MEMBERS'),
 				'mostactive'	=> JText::_('CC GROUP SORT MOST ACTIVE') );

		$tmpl->set( 'featuredList'	, $featuredList );
		$tmpl->set( 'index'		, true );
		$tmpl->set( 'categories' 	, $data->categories );
		$tmpl->set( 'groupsHTML'	, $groupsHTML );
		$tmpl->set( 'config'		, $config );
		$tmpl->set( 'category' 		, $category );
		$tmpl->set( 'isCommunityAdmin'	, COwnerHelper::isCommunityAdmin() );
		$tmpl->set( 'sortings'		, CFilterBar::getHTML( CRoute::getURI(), $sortItems, 'latest') );
		$tmpl->set( 'my' 		, $my );
		$tmpl->set( 'discussionsHTML'	, $this->_getSidebarDiscussions( $discussions ) );
		echo $tmpl->fetch( 'groups.index' );
		
	}

	/**
	 * Application full view
	 **/
	function discussAppFullView()
	{
		$document		=& JFactory::getDocument();
		$document->setTitle( JText::_('CC REPLY TO DISCUSSION TITLE') );
			
		$applicationName	= JString::strtolower( JRequest::getVar( 'app' , '' , 'GET' ) );

		if(empty($applicationName))
		{
			JError::raiseError( 500, 'CC APP ID REQUIRED');
		}

		$output		= '';
		$topicId	= JRequest::getVar( 'topicid' , '' , 'GET' );

		$model		= CFactory::getModel( 'discussions' );
		$discussion	=& JTable::getInstance( 'Discussion' , 'CTable' );
		$discussion->load( $topicId );
		$group		=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $discussion->groupid );
		
		$this->addSubmenuItem('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $discussion->groupid . '&topicid=' . $topicId, JText::_('CC BACK TO TOPIC'));
		parent::showSubmenu();
		
		//@todo: Since group walls doesn't use application yet, we process it manually now.
		if( $applicationName == 'walls' )
		{
			CFactory::load( 'libraries' , 'wall' );
			$limit		= JRequest::getVar( 'limit' , 5 , 'REQUEST' );
			$limitstart = JRequest::getVar( 'limitstart', 0, 'REQUEST' );
			
			$my			= CFactory::getUser();
			$config		= CFactory::getConfig();
			
		
			CFactory::load( 'helpers' , 'owner' );
			
			if( !$config->get('lockgroupwalls') || ($config->get('lockgroupwalls') && $group->isMember( $my->id ) ) || COwnerHelper::isCommunityAdmin() )
			{   
				$outputLock		= '<div class="warning">' . JText::_('CC DISCUSSION LOCKED NOTICE') . '</div>';
				$outputUnLock	= CWallLibrary::getWallInputForm( $discussion->id , 'groups,ajaxSaveDiscussionWall', 'groups,ajaxRemoveWall' ); 
				$wallForm		= $discussion->lock ? $outputLock : $outputUnLock ; 
				
				$output	.= $wallForm;
			}

			// Get the walls content
			$output 		.='<div id="wallContent">';
			$output			.= CWallLibrary::getWallContents( 'discussions' , $discussion->id , ($my->id == $discussion->creator) , $limit , $limitstart , 'wall.content' , 'groups,discussion');
			$output 		.= '</div>';
			
			jimport('joomla.html.pagination');
			$wallModel 		= CFactory::getModel('wall');
			$pagination		= new JPagination( $wallModel->getCount( $discussion->id , 'discussions' ) , $limitstart , $limit );

			$output		.= '<div class="pagination-container">' . $pagination->getPagesLinks() . '</div>';
		}
		else
		{
			CFactory::load( 'libraries' , 'apps' );
			$model				= CFactory::getModel('apps');
			$applications		=& CAppPlugins::getInstance();
			$applicationId		= $model->getUserApplicationId( $applicationName );
			
			$application		= $applications->get( $applicationName , $applicationId );
	
			// Get the parameters
			$manifest			= JPATH_PLUGINS . DS . 'community' . DS . $applicationName . DS . $applicationName . '.xml';
			
			$params			= new JParameter( $model->getUserAppParams( $applicationId ) , $manifest );
	
			$application->params	=& $params;
			$application->id		= $applicationId;
			
			$output	= $application->onAppDisplay( $params );
		}
		
		echo $output;
	}
	
	/**
	 * Application full view
	 **/
	function appFullView()
	{
		$document		=& JFactory::getDocument();
		$document->setTitle( JText::_('CC GROUP WALL TITLE') );
		
		$applicationName	= JString::strtolower( JRequest::getVar( 'app' , '' , 'GET' ) );

		if(empty($applicationName))
		{
			JError::raiseError( 500, 'CC APP ID REQUIRED');
		}

		$output	= '';
		
		//@todo: Since group walls doesn't use application yet, we process it manually now.
		if( $applicationName == 'walls' )
		{
			CFactory::load( 'libraries' , 'wall' );
			$limit		= JRequest::getInt( 'limit' , 5 , 'REQUEST' );
			$limitstart = JRequest::getInt( 'limitstart', 0, 'REQUEST' );
			$groupId	= JRequest::getInt( 'groupid' , '' , 'GET' );
			$my			= CFactory::getUser();
			$config		= CFactory::getConfig();
			
			$groupModel	= CFactory::getModel( 'groups' );
			$group		=& JTable::getInstance( 'Group' , 'CTable' );
			$group->load( $groupId );

			// Test if the current browser is a member of the group
			$isMember			= $group->isMember( $my->id );
			$waitingApproval	= $groupModel->isWaitingAuthorization( $my->id , $group->id );
		
			CFactory::load( 'helpers' , 'owner' );
			
			if( !$isMember && !COwnerHelper::isCommunityAdmin() && $group->approvals == COMMUNITY_PRIVATE_GROUP )
			{
				$this->noAccess( JText::_('CC PRIVATE GROUP NOTICE') );
				return;
			}
			
			if( !$config->get('lockgroupwalls') || ($config->get('lockgroupwalls') && ($isMember) && !($waitingApproval) ) || COwnerHelper::isCommunityAdmin() )
			{
				$output	.= CWallLibrary::getWallInputForm( $group->id , 'groups,ajaxSaveWall', 'groups,ajaxRemoveWall' );
			}

			// Get the walls content
			$output 		.='<div id="wallContent">';
			$output			.= CWallLibrary::getWallContents( 'groups' , $group->id , ($my->id == $group->ownerid) , $limit , $limitstart , 'wall.content' ,'groups,group');
			$output 		.= '</div>';
			
			jimport('joomla.html.pagination');
			$wallModel 		= CFactory::getModel('wall');
			$pagination		= new JPagination( $wallModel->getCount( $group->id , 'groups' ) , $limitstart , $limit );

			$output		.= '<div class="pagination-container">' . $pagination->getPagesLinks() . '</div>';
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
		 	
	/**
	 * Displays specific groups
	 **/
	function viewGroup()
	{               
		$mainframe =& JFactory::getApplication();
		
		CFactory::load( 'libraries' , 'tooltip' );
		CFactory::load( 'libraries' , 'wall' );
		CFactory::load( 'libraries' , 'window' );
		CWindow::load();
		
		$config			= CFactory::getConfig();
		$document		=& JFactory::getDocument();

		// Load appropriate models
		$groupModel	= CFactory::getModel( 'groups' );
		$wallModel	= CFactory::getModel( 'wall' );
		$userModel	= CFactory::getModel( 'user' );
		$discussModel	= CFactory::getModel( 'discussions' );
		$bulletinModel	= CFactory::getModel( 'bulletins' );
		$photosModel	= CFactory::getModel( 'photos' );
		
		$groupid		= JRequest::getInt( 'groupid' , '' );
		CError::assert( $groupid , '' , '!empty' , __FILE__ , __LINE__ );

		$editGroup		= JRequest::getVar( 'edit' , false , 'GET' );
		$editGroup		= ( $editGroup == 1 ) ? true : false;
		
		// Load the group table.
		$group			=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $groupid );

		$params			= $group->getParams();
		CFactory::load( 'helpers' , 'string' );
		$mainframe->appendMetaTag('title', CStringHelper::escape( $group->name) );
		$mainframe->appendMetaTag('description', CStringHelper::escape( strip_tags( $group->description) ) );
		$mainframe->addCustomHeadTag('<link rel="image_src" href="'. JURI::root() . $group->thumb .'" />');
		
		// @rule: Test if the group is unpublished, don't display it at all.
		if( !$group->published )
		{
			echo JText::_('CC GROUP UNPUBLISHED');
			return;
		}

		// Show submenu
		$this->showSubmenu();

		// Set the group info to contain proper <br>
		$group->description	= nl2br( $group->description );

		$this->addPathway( JText::_('CC GROUPS') , CRoute::_('index.php?option=com_community&view=groups') ); 
		$this->addPathway( JText::sprintf( 'CC VIEW GROUP TITLE' , $group->name ), '' );
		
		// Load the current browsers data
		$my			= CFactory::getUser();

		// If user are invited
		$isInvited  =	$groupModel->isInvited( $my->id, $groupid );
		
		// Get members list for display
		$members		= $groupModel->getMembers( $groupid , 12 , true , CC_RANDOMIZE , true );
		$membersCount	= $groupModel->total;
		CError::assert( $members , 'array' , 'istype' , __FILE__ , __LINE__ );

		// Is there any my friend is the member of this group?
		$join		=   '';
		$friendsCount	=   0;
		if( $isInvited )
		{
		    // Get the invitors
		    $invitors	    =	$groupModel->getInvitors( $my->id, $groupid );

		    if( count($invitors) == 1 )
		    {
			$user	=   CFactory::getUser( $invitors[0]->creator );
			$join	=   '<a href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
		    }
		    else
		    {
			for( $i = 0; $i<count($invitors); $i++ )
			{
			    $user	= CFactory::getUser( $invitors[$i]->creator );

			    if( ($i + 1 )== count($invitors) )
			    {
				$join	.=  ' ' . JText::_('CC AND') . ' ' . '<a href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
			    }
			    else
			    {
				$join   .=  ', ' . '<a href="' . CUrlHelper::userLink($user->id) . '">' . $user->getDisplayName() . '</a>';
			    }
			}
		    }

		    // Get users friends in this group
		    $friendsCount   =	$groupModel->getFriendsCount( $my->id, $groupid );
		}

		$admins			= $groupModel->getAdmins( $groupid , 12 , CC_RANDOMIZE );
		$adminsCount	= $groupModel->total;
		
		// Get the news
		$bulletins		= $bulletinModel->getBulletins( $group->id );
		$totalBulletin	= $bulletinModel->total;
		CError::assert( $bulletins , 'array', 'istype', __FILE__ , __LINE__ );

		// Get discussions
		$discussions		= $discussModel->getDiscussionTopics( $group->id , '10' , $params->get('discussordering' , DISCUSSION_ORDER_BYLASTACTIVITY) );
		$totalDiscussion	= $discussModel->total;

		CError::assert( $discussions , 'array', 'istype', __FILE__ , __LINE__ );

		// Get list of unapproved members
		$unapproved		= $groupModel->getMembers( $group->id , null , false );

		// Attach avatar of the member to the discussions
		for( $i = 0; $i < count( $discussions ); $i++ )
		{
			$row	=& $discussions[$i];

			$row->user	= CFactory::getUser( $row->creator );
			
			// Get last replier for the discussion
			$row->lastreplier			= $discussModel->getLastReplier( $row->id );
			if( $row->lastreplier )
				$row->lastreplier->post_by	= CFactory::getUser( $row->lastreplier->post_by );
		}

		// Attach avatar of the admin
		for( $i = 0; ($i < count($admins)); $i++)
		{
			$row	=& $admins[$i];

			$admins[$i]	= CFactory::getUser( $row->id );
		}
		
		// Attach avatar of the member
		// Pre-load multiple users at once
		$userids = array();
		foreach($members as $uid){ $userids[] = $uid->id; }
		CFactory::loadUsers($userids);

		for( $i = 0; ($i < count($members)); $i++)
		{
			$row	=& $members[$i];

			$members[$i]	= CFactory::getUser( $row->id );
		}
		// Test if the current user is admin
		$isAdmin	    =	$groupModel->isAdmin( $my->id , $group->id );
		
		// Test if the current browser is a member of the group
		$isMember	    =	$group->isMember( $my->id );
		$waitingApproval    =	false;

		// Test if the current user is banned from this group
		$isBanned	    =	$group->isBanned( $my->id );
		if( $isBanned )
		{
			$mainframe->enqueueMessage( JText::_('CC GROUP MEMBER BANNED'), 'error');
		}

		// If I have tried to join this group, but not yet approved, display a notice
		if( $groupModel->isWaitingAuthorization( $my->id , $group->id ) )
		{
			$waitingApproval	= true;
		}

		// Get the walls
		$wallContent	= CWallLibrary::getWallContents( 'groups' , $group->id , $isAdmin , 10 ,0 , 'wall.content' , 'groups,group');
		$wallCount		= CWallLibrary::getWallCount('groups', $group->id);
		
		$viewAllLink = false;
		if(JRequest::getVar('task', '', 'REQUEST') != 'app')
		{
			$viewAllLink	= CRoute::_('index.php?option=com_community&view=groups&task=app&groupid=' . $group->id . '&app=walls');
		}
		$wallContent	.= CWallLibrary::getViewAllLinkHTML($viewAllLink, $wallCount);
		
		$wallForm		='';

		CFactory::load( 'helpers' , 'owner' );
		
		if( !$config->get('lockgroupwalls') || ($config->get('lockgroupwalls') && ($isMember && !$isBanned) && !($waitingApproval) ) || COwnerHelper::isCommunityAdmin() )
		{
			$wallForm	= CWallLibrary::getWallInputForm( $group->id , 'groups,ajaxSaveWall', 'groups,ajaxRemoveWall' );
		}

		// Get like
		CFactory::load( 'libraries' , 'like' );
		$likes	    = new CLike();
		$likesHTML  = ($isMember && !$isBanned) ? $likes->getHTML( 'groups', $group->id, $my->id ) : $likes->getHtmlPublic( 'groups', $group->id );

		// Process discussions HTML output
		$tmpl		= new CTemplate();
		$tmpl->set( 'discussions'	, $discussions );
		$tmpl->set( 'groupId'		, $group->id );
		$discussionsHTML	= $tmpl->fetch( 'groups.discussionlist' );
		unset( $tmpl );

		// Get the creator of the discussions
		for( $i = 0; $i < count( $bulletins ); $i++ )
		{
			$row			=& $bulletins[ $i ];

			$row->creator	= CFactory::getUser( $row->created_by );
		}

		// Only trigger the bulletins if there is really a need to.
		if( !empty( $bulletins )  )
		{
			$appsLib	=& CAppPlugins::getInstance();
			$appsLib->loadApplications();

			// Format the bulletins
			// the bulletins need to be an array or reference to work around
			// PHP 5.3 pass by value
			$args = array();
			foreach($bulletins as &$b)
			{
				$args[] = &$b;
			}
			$appsLib->triggerEvent( 'onBulletinDisplay',  $args );
		}
		
		// Process bulletins HTML output
		$tmpl		= new CTemplate();
		$tmpl->set( 'bulletins'	, $bulletins );
		$tmpl->set( 'groupId'		, $group->id );
		$bulletinsHTML	= $tmpl->fetch( 'groups.bulletinlist' );
		unset( $tmpl );
		$tmpl		= new CTemplate();

		// Get categories list
		// We should really load this in saperate file
		// @todo: editing group should really open a new page
		if($my->id == $group->ownerid || COwnerHelper::isCommunityAdmin() )
		{
			$categories		= $groupModel->getCategories();
			CError::assert( $categories , 'array', 'istype', __FILE__ , __LINE__ );
			
			$tmpl->set( 'categories' 		, $categories );
		}

		$isMine		= ($my->id == $group->ownerid);
		// Get reporting html
		CFactory::load('libraries', 'reporting');
		$report		= new CReportingLibrary();

		$reportHTML	= $report->getReportingHTML( JText::_('CC REPORT GROUP') , 'groups,reportGroup' , array( $group->id ) );
		
		$isSuperAdmin	= COwnerHelper::isCommunityAdmin();
		
		if( $group->approvals == '1' && !$isMine && !$isMember && !$isSuperAdmin )
		{
			$this->addWarning( JText::_( 'CC PRIVATE GROUP NOTICE' ) );
		}
		
		$albums			=& $photosModel->getGroupAlbums($group->id , true, false, $params->get('grouprecentphotos' , GROUP_PHOTO_RECENT_LIMIT));
		$totalAlbums	= $photosModel->total;
		
		CFactory::load( 'helpers', 'videos' );
		CFactory::load( 'libraries' , 'videos' );
		CFactory::load( 'helpers' , 'group' );
		$videoModel 	= CFactory::getModel('videos');
		$tmpVideos 		= $videoModel->getGroupVideos( $group->id, '', $params->get('grouprecentvideos' , GROUP_VIDEO_RECENT_LIMIT) );
		$videos			= array();
		if ($tmpVideos)
		{
			foreach($tmpVideos as $videoEntry)
			{
				$video	=& JTable::getInstance('Video','CTable');
				$video->bind( $videoEntry );
				$videos[]	= $video;
			}
		}
		
		$videoThumbWidth	= CVideoLibrary::thumbSize('width');
		$videoThumbHeight	= CVideoLibrary::thumbSize('height');
		$totalVideos	= $videoModel->total;
		
		$eventsModel	= CFactory::getModel( 'Events' );
		$tmpEvents		=& $eventsModel->getGroupEvents( $group->id , $params->get( 'grouprecentevents' , GROUP_EVENT_RECENT_LIMIT ) );
		$totalEvents	= $eventsModel->getTotalGroupEvents( $group->id );

		$events			= array();
		foreach( $tmpEvents as $event )
		{
			$table	=& JTable::getInstance( 'Event' , 'CTable' );
			$table->bind( $event );
			$events[]	= $table;
		}
		
		$allowManagePhotos	= CGroupHelper::allowManagePhoto( $group->id );		
		$allowManageVideos	= CGroupHelper::allowManageVideo( $group->id );
		$allowCreateEvent	= CGroupHelper::allowCreateEvent( $my->id , $group->id ); 

		CFactory::load( 'libraries' , 'bookmarks' );
		$bookmarks		= new CBookmarks(CRoute::getExternalURL( 'index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id ));
		$bookmarksHTML	= $bookmarks->getHTML();
		$isCommunityAdmin	= COwnerHelper::isCommunityAdmin();
		
		if( $group->approvals=='0' || $isMine || ($isMember && !$isBanned) || $isCommunityAdmin )
		{
			// Set feed url
			$feedLink = CRoute::_('index.php?option=com_community&view=groups&task=viewbulletins&groupid=' . $group->id . '&format=feed');
			$feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('CC SUBSCRIBE TO BULLETIN FEEDS') . '" href="'.$feedLink.'"/>';
			$document->addCustomTag( $feed );
			
			$feedLink = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussions&groupid=' . $group->id . '&format=feed');
			$feed = '<link rel="alternate" type="application/rss+xml" title="'. JText::_('CC SUBSCRIBE TO DISCUSSION FEEDS') .'" href="'.$feedLink.'"/>';
			$document->addCustomTag( $feed );
			
			$feedLink = CRoute::_('index.php?option=com_community&view=photos&groupid=' . $group->id . '&format=feed');
			$feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('CC SUBSCRIBE TO GROUP PHOTOS FEEDS') . '" href="'.$feedLink.'"/>';
			$mainframe->addCustomHeadTag( $feed );   
			
			$feedLink  = CRoute::_('index.php?option=com_community&view=videos&groupid=' . $group->id . '&format=feed');
			$feed      = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('CC SUBSCRIBE TO GROUP VIDEOS FEEDS') . '"  href="'.$feedLink.'"/>';
			$mainframe->addCustomHeadTag( $feed ); 
			
			$feedLink  = CRoute::_('index.php?option=com_community&view=events&groupid=' . $group->id . '&format=feed');
			$feed      = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('CC SUBSCRIBE TO GROUP EVENTS FEEDS') . '"  href="'.$feedLink.'"/>';
			$mainframe->addCustomHeadTag( $feed );
		}

		$friendsModel	= CFactory::getModel( 'Friends' );
		$groupsModel	= CFactory::getModel( 'Groups' );
						
		$tmpFriends		= $friendsModel->getFriends( $my->id , 'name' , false);				
		
		$friends		= array();
		
		for( $i = 0; $i < count( $tmpFriends ); $i++ )
		{
			$friend			=& $tmpFriends[ $i ];
			$groupInvite	=& JTable::getInstance( 'GroupInvite' , 'CTable' );
			$groupInvite->load( $group->id , $friend->id );

			if( !$groupsModel->isMember( $friend->id , $group->id ) )
			{
				$friends[]	= $friend;
			}
		}
		unset( $tmpFriends );

		CFactory::load( 'libraries' , 'invitation' );
		$inviteHTML = CInvitation::getHTML( $friends , 'groups,inviteUsers' , $group->id );

		$tmpl->setMetaTags( 'group'		, $group);
		$tmpl->set( 'likesHTML'			, $likesHTML );
		$tmpl->set( 'events'			, $events );
		$tmpl->set( 'totalEvents'		, $totalEvents );
		$tmpl->set( 'inviteHTML'		, $inviteHTML );
		$tmpl->set( 'showEvents'		, ($params->get('eventpermission') != -1) );
		$tmpl->set( 'showPhotos'		, ($params->get('photopermission') != -1) );
		$tmpl->set( 'showVideos'		, ($params->get('videopermission') != -1) );
		$tmpl->set( 'bookmarksHTML'		, $bookmarksHTML );
		$tmpl->set( 'allowManagePhotos'		, $allowManagePhotos );
		$tmpl->set( 'allowManageVideos'		, $allowManageVideos );
		$tmpl->set( 'allowCreateEvent'		, $allowCreateEvent );
		$tmpl->set( 'videos'			, $videos );
		$tmpl->set( 'videoThumbWidth'		, $videoThumbWidth );
		$tmpl->set( 'videoThumbHeight'		, $videoThumbHeight );
		$tmpl->set( 'totalVideos'		, $totalVideos );
		$tmpl->set( 'albums'			, $albums );
		$tmpl->set( 'totalAlbums'		, $totalAlbums );
		$tmpl->set( 'reportHTML'		, $reportHTML );
		$tmpl->set( 'editGroup'			, $editGroup );
		$tmpl->set( 'waitingApproval'		, $waitingApproval );
		$tmpl->set( 'config'			, $config );
		$tmpl->set( 'my'			, $my);
		$tmpl->set( 'isMine'			, $isMine );
		$tmpl->set( 'isAdmin'			, $isAdmin );
		$tmpl->set( 'isSuperAdmin'		, $isSuperAdmin );
		$tmpl->set( 'isMember' 			, $isMember );
		$tmpl->set( 'isInvited'			, $isInvited );
		$tmpl->set( 'friendsCount'		, $friendsCount );
		$tmpl->set( 'join'			, $join );
		$tmpl->set( 'unapproved'		, count( $unapproved ) );
		$tmpl->set( 'membersCount'		, $membersCount );
		$tmpl->set( 'group' 			, $group );
		$tmpl->set( 'totalBulletin'		, $totalBulletin );
		$tmpl->set( 'totalDiscussion'		, $totalDiscussion );
		$tmpl->set( 'members' 			, $members );
		$tmpl->set( 'admins'			, $admins );
		$tmpl->set( 'adminsCount'		, $adminsCount );
		$tmpl->set( 'bulletins'			, $bulletins );
		$tmpl->set( 'wallForm' 			, $wallForm );
		$tmpl->set( 'wallContent' 		, $wallContent );
		$tmpl->set( 'discussions' 		, $discussions );
		$tmpl->set( 'discussionsHTML'		, $discussionsHTML );
		$tmpl->set( 'bulletinsHTML'		, $bulletinsHTML );
		$tmpl->set( 'isCommunityAdmin'		, $isCommunityAdmin );
		$tmpl->set( 'isBanned'			, $isBanned );
		
		echo $tmpl->fetch( 'groups.viewgroup' );
	}

	function uploadAvatar( $data )
	{

		$document =& JFactory::getDocument();
        $document->setTitle(JText::_('CC UPLOAD GROUP AVATAR'));
		
		$this->_addGroupInPathway( $data->id );		
		$this->addPathway( JText::_('CC UPLOAD GROUP AVATAR') );

		$this->showSubmenu();

		$config			= CFactory::getConfig();
		$uploadLimit	= (double) $config->get('maxuploadsize');
		$uploadLimit	.= 'MB';
		
		CFactory::load( 'models' , 'groups' );
		$group			=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $data->id );
		
		CFactory::load( 'libraries' , 'apps' );
		$app 		=& CAppPlugins::getInstance();
		$appFields	= $app->triggerEvent('onFormDisplay' , array('jsform-groups-uploadavatar'));
		$beforeFormDisplay	= CFormElement::renderElements( $appFields , 'before' );
		$afterFormDisplay	= CFormElement::renderElements( $appFields , 'after' );

		$tmpl	= new CTemplate();
		$tmpl->set( 'beforeFormDisplay', $beforeFormDisplay );
		$tmpl->set( 'afterFormDisplay'	, $afterFormDisplay );
		$tmpl->set( 'groupId' 	, $data->id );
		$tmpl->set( 'avatar'	, $group->getAvatar('avatar') );
		$tmpl->set( 'thumbnail' , $group->getAvatar() );
		$tmpl->set( 'uploadLimit'	, $uploadLimit );
		 
		echo $tmpl->fetch( 'groups.uploadavatar' );
	}

	/**
	 * Method to display groups that belongs to a user.
	 *
	 * @access public
	 */
	function mygroups()
	{
		$mainframe 	=& JFactory::getApplication();
		$document 	=& JFactory::getDocument();
		$userid   	= JRequest::getVar('userid','');
		$user		= CFactory::getUser($userid);
		$my			= CFactory::getUser();
		
		$title	= ($my->id == $user->id) ? JText::_('CC MY GROUPS TITLE') : JText::sprintf('CC USERS GROUPS TITLE', $user->getDisplayName());
		$document->setTitle($title);
		
		// Add the miniheader if necessary
		if($my->id != $user->id) $this->attachMiniHeaderUser($user->id);
		
		// Load required filterbar library that will be used to display the filtering and sorting.
		CFactory::load( 'libraries' , 'filterbar' );

		
		$this->addPathway( JText::_('CC GROUPS') , CRoute::_('index.php?option=com_community&view=groups') );
		$this->addPathway( JText::_('CC MY GROUPS TITLE') , '' );

        $this->showSubmenu();  

		$uri	= JURI::base();

		//@todo: make mygroups page to contain several admin tools for owner?
        
        $groupsModel	= CFactory::getModel('groups');
        $avatarModel	= CFactory::getModel('avatar');
        $wallsModel		= CFactory::getModel( 'wall' );
        $activityModel	= CFactory::getModel( 'activities' );
		$discussionModel= CFactory::getModel( 'discussions' );
		$sorted			= JRequest::getVar( 'sort' , 'latest' , 'GET' );

		// @todo: proper check with CError::assertion
		// Make sure the sort value is not other than the array keys

		$groups			= $groupsModel->getGroups( $my->id , $sorted );
		$pagination		= $groupsModel->getPagination(count($groups));

		require_once( JPATH_COMPONENT . DS . 'libraries' . DS . 'activities.php');
		$act			= new CActivityStream();

 		// Attach additional properties that the group might have
 		$groupIds   = '';
 		if( $groups )
 		{
	 		foreach( $groups as $group )
	 		{
                $groupIds   = (empty($groupIds)) ? $group->id : $groupIds . ',' . $group->id;
			}
		}

		// Get the template for the group lists
		$groupsHTML	= $this->_getGroupsHTML( $groups, $pagination );
		
		// getting group's latest discussion activities.
		$templateParams	= CTemplate::getTemplateParams();
		$discussions	=	$groupsModel->getGroupLatestDiscussion('',$groupIds , $templateParams->get('sidebarTotalDiscussions') ); 

		$feedLink = CRoute::_('index.php?option=com_community&view=groups&task=mygroups&userid=' . $userid . '&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" title="'. JText::_('CC SUBSCRIBE TO LATEST MY GROUPS FEED') .'"  href="'.$feedLink.'"/>'; 
		$mainframe->addCustomHeadTag( $feed ); 
		
		$feedLink = CRoute::_('index.php?option=com_community&view=groups&task=viewmylatestdiscussions&groupids=' . $groupIds . '&userid=' . $userid . '&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" title="'. JText::_('CC SUBSCRIBE TO LATEST MY GROUP DISCUSSIONS FEED') .'"  href="'.$feedLink.'"/>'; 
		$mainframe->addCustomHeadTag( $feed );

		$sortItems =  array(
				'latest' 		=> JText::_('CC GROUP SORT LATEST') ,
				'alphabetical'	=> JText::_('CC SORT ALPHABETICAL'),
				'mostdiscussed'	=> JText::_('CC GROUP SORT MOST DISCUSSED'),
				'mostwall'		=> JText::_('CC GROUP SORT MOST WALL POST'),
 				'mostmembers'	=> JText::_('CC GROUP SORT MOST MEMBERS'),
 				'mostactive'	=> JText::_('CC GROUP SORT MOST ACTIVE') );
		$tmpl			= new CTemplate();
		$tmpl->set( 'groupsHTML'	, $groupsHTML );
        $tmpl->set( 'pagination'	, $pagination );
        $tmpl->set( 'my'			, $my );
        $tmpl->set( 'sortings'		, CFilterBar::getHTML( CRoute::getURI(), $sortItems, 'latest') );
        $tmpl->set( 'discussionsHTML'		, $this->_getSidebarDiscussions( $discussions ) );

        echo $tmpl->fetch('groups.mygroups');
	}
	
	function myinvites()
	{
		$mainframe =& JFactory::getApplication();
		$userId    = JRequest::getVar('userid','');

		// Load required filterbar library that will be used to display the filtering and sorting.
		CFactory::load( 'libraries' , 'filterbar' );
		
		$document =& JFactory::getDocument();
		
		$this->addPathway( JText::_('CC GROUPS') , CRoute::_('index.php?option=com_community&view=groups') );
		$this->addPathway( JText::_('CC GROUP PENDING INVITATIONS') , '' );

        $document->setTitle(JText::_('CC GROUP PENDING INVITATIONS'));
        $this->showSubmenu();  
        
		$feedLink = CRoute::_('index.php?option=com_community&view=groups&task=mygroups&userid=' . $userId . '&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" title="'. JText::_('CC SUBSCRIBE TO PENDING INVITATIONS FEED') .'"  href="'.$feedLink.'"/>'; 
		$mainframe->addCustomHeadTag( $feed );
		
		$my				= CFactory::getUser();
		$model			= CFactory::getModel('groups');
		$discussionModel= CFactory::getModel( 'discussions' );
		$sorted			= JRequest::getVar( 'sort' , 'latest' , 'GET' );

		$rows		= $model->getGroupInvites( $my->id );
		$pagination	= $model->getPagination(count($rows));
		$groups		= array();
		$ids		= '';

		if( $rows )
		{
			foreach( $rows as $row )
			{
				$table	=& JTable::getInstance( 'Group' , 'CTable' );
				$table->load( $row->groupid );
				
				$groups[]	= $table;
				$ids		= (empty($ids)) ? $table->id : $ids . ',' . $table->id;
			}
		}
		$sortItems =  array(
				'latest' 		=> JText::_('CC GROUP SORT LATEST') ,
				'alphabetical'	=> JText::_('CC SORT ALPHABETICAL'),
				'mostdiscussed'	=> JText::_('CC GROUP SORT MOST DISCUSSED'),
				'mostwall'		=> JText::_('CC GROUP SORT MOST WALL POST'),
 				'mostmembers'	=> JText::_('CC GROUP SORT MOST MEMBERS'),
 				'mostactive'	=> JText::_('CC GROUP SORT MOST ACTIVE') );
		$tmpl	= new CTemplate();
		
		$tmpl->set( 'groups'	, $groups );
        $tmpl->set( 'pagination', $pagination );
        $tmpl->set( 'count'		, $pagination->total );
        $tmpl->set( 'my'			, $my );
        $tmpl->set( 'sortings'		, CFilterBar::getHTML( CRoute::getURI(), $sortItems, 'latest') );

        //echo $tmpl->fetch('groups.mygroups');
        echo $tmpl->fetch('groups.myinvites');
	}
	
	function _getSidebarDiscussions( $discussions )
	{
		if(! empty($discussions))
		{
			for($i=0; $i < count($discussions); $i++)
			{
			    $row    	=& $discussions[$i];
			    $creator   	= CFactory::getUser($row->creator);

			    $commentorName  = '';
			    if(! empty($row->lastreplied_by))
			    {
			    	$commentor  	= CFactory::getUser($row->lastreplied_by);
			    	$commentorName  = $commentor->getDisplayName();
			    }

			    $row->creatorName   	= $creator->getDisplayName();
			    $row->commentorName   	= $commentorName;
			}
		}
		
		$tmpl	= new CTemplate();		
		$tmpl->set( 'discussions' , $discussions );

		return $tmpl->fetch( 'groups.sidebar.discussions' );
	}
	
	function viewbulletin( )
	{
		$document		=& JFactory::getDocument();
		
		// Load necessary libraries
		CFactory::load( 'models' , 	'bulletins' );
		CFactory::load( 'libraries' , 'apps' );
		$groupsModel	= CFactory::getModel( 'groups' );
		$bulletin		=& JTable::getInstance( 'Bulletin' , 'CTable' );
		$group			=& JTable::getInstance( 'Group' , 'CTable' );
		$my				= CFactory::getUser();
		$bulletinId		= JRequest::getVar( 'bulletinid' , '' , 'GET' );
		$bulletin->load( $bulletinId );
		$group->load( $bulletin->groupid );

		// @rule: Test if the group is unpublished, don't display it at all.
		if( !$group->published )
		{
			$this->noAccess( JText::_('CC GROUP UNPUBLISHED') );
			return;
		}
		
		CFactory::load( 'helpers' , 'owner' );
		
		if( $group->approvals == 1 && !($group->isMember($my->id) ) && !COwnerHelper::isCommunityAdmin() )
		{
			$this->noAccess( JText::_('CC PRIVATE GROUP NOTICE') );
			return;
		}
		
		$document->setTitle( $bulletin->title );
		
		// Santinise output
		CFactory::load( 'helpers' , 'string' );
		$bulletin->title	= strip_tags($bulletin->title);
		$bulletin->title	= CStringHelper::escape($bulletin->title);
		
		// Add pathways
		$this->_addGroupInPathway( $group->id );		
		$this->addPathway( JText::_('CC BULLETINS') , CRoute::_('index.php?option=com_community&view=groups&task=viewbulletins&groupid=' . $group->id) ); 
		$this->addPathway( JText::sprintf( 'CC BULLETIN VIEW TITLE' , $bulletin->title ) );

		CFactory::load( 'helpers' , 'owner' );
		
		if( $groupsModel->isAdmin( $my->id , $group->id )  || COwnerHelper::isCommunityAdmin() )
		{
			$this->addSubmenuItem( '' , JText::_('CC DELETE') , "joms.groups.removeBulletin('" . JText::_('CC DELETE') . "','" . $bulletin->groupid . "','" . $bulletin->id . "');" , true );
			$this->addSubmenuItem( '' , JText::_('CC EDIT') , "joms.groups.editBulletin();" , true );
		}
		$this->showSubMenu();

		$config		= CFactory::getConfig();

		//$editor		=& JFactory::getEditor();
		jimport( 'joomla.html.editor' );
		$editor		= new JEditor( $config->get('htmleditor' , 'none') );
		
		$appsLib	=& CAppPlugins::getInstance();
		$appsLib->loadApplications();

		$args[]		=& $bulletin;
		$editorMessage	= $bulletin->message;
		
		// Format the bulletins
		$appsLib->triggerEvent( 'onBulletinDisplay',  $args );
		CFactory::load( 'libraries' , 'bookmarks' );
		$bookmarks		= new CBookmarks(CRoute::getExternalURL( 'index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $group->id . '&bulletinid=' . $bulletin->id));
		$bookmarksHTML	= $bookmarks->getHTML();

        $creator    = CFactory::getUser( $bulletin->created_by );
        $tmpl			= new CTemplate();
        $tmpl->set( 'bookmarksHTML' , $bookmarksHTML );
        $tmpl->set( 'creator'       , $creator );
        $tmpl->set( 'bulletin' , $bulletin );
        $tmpl->set( 'editor'	, $editor );
        $tmpl->set( 'config'	, $config );
        $tmpl->set( 'editorMessage' , $editorMessage );
        echo $tmpl->fetch( 'groups.viewbulletin' );

	}

	/**
	 * Display a list of bulletins from the specific group
	 **/
	function viewbulletins()
	{
		$document	=& JFactory::getDocument();

		// Load necessary files
		CFactory::load( 'models' , 'groups' );
		CFactory::load( 'helpers' , 'owner' );

		$id			= JRequest::getInt( 'groupid' , '' , 'GET' );
		$my			= CFactory::getUser();
		
		// Load the group
		$group		=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $id );
		$this->_addGroupInPathway( $group->id );		
		$this->addPathway( JText::_('CC BULLETINS') ); 

		if( $group->id == 0 )
		{
			echo JText::_('CC INVALID GROUP ID');
			return;
		}
		
		//display notice if the user is not a member of the group
		if( $group->approvals == 1 && !($group->isMember($my->id) ) && !COwnerHelper::isCommunityAdmin() )
		{
			$this->noAccess( JText::_('CC PRIVATE GROUP NOTICE') );
			return;
		}
		
		// Set page title
		$document->setTitle( JText::sprintf('CC VIEW ALL BULLETINS TITLE' , $group->name) );

		// Load submenu
		$this->showSubMenu();

		$model			= CFactory::getModel( 'bulletins');
		$bulletins		= $model->getBulletins( $group->id );

		// Set feed url
		$feedLink = CRoute::_('index.php?option=com_community&view=groups&task=viewbulletins&groupid=' . $group->id . '&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" title="' . JText::_('CC SUBSCRIBE TO BULLETIN FEEDS') . '" href="'.$feedLink.'"/>';
		$document->addCustomTag( $feed );

		// Get the creator of the bulletins
		for( $i = 0; $i < count( $bulletins ); $i++ )
		{
			$row			=& $bulletins[ $i ];

			$row->creator	= CFactory::getUser( $row->created_by );
		}

		// Only trigger the bulletins if there is really a need to.
		if( !empty( $bulletins ) && isset( $bulletins ) )
		{
			$appsLib	=& CAppPlugins::getInstance();
			$appsLib->loadApplications();
			
			// Format the bulletins
			// the bulletins need to be an array or reference to work around
			// PHP 5.3 pass by value
			$args = array();
			foreach($bulletins as &$b)
			{
				$args[] = &$b;
			}
			$appsLib->triggerEvent( 'onBulletinDisplay',  $args );
		}
		
		// Process bulletins HTML output
		$tmpl		= new CTemplate();
		$tmpl->set( 'bulletins'	, $bulletins );
		$tmpl->set( 'groupId'		, $group->id );
		$bulletinsHTML	= $tmpl->fetch( 'groups.bulletinlist' );
		unset( $tmpl );

		$tmpl			= new CTemplate();
		$tmpl->set( 'group'			, $group );
		$tmpl->set( 'bulletinsHTML'	, $bulletinsHTML );
		$tmpl->set( 'pagination'	, $model->getPagination() );
		echo $tmpl->fetch( 'groups.viewbulletins' );
	}

	function banlist( $data )
	{
		$this->viewmembers( $data );
	}
	
	/**
	 * View method to display members of the groups
	 *
	 * @access	public
	 * @param	string 	Group Id
	 * @returns object  An object of the specific group
	 */
	function viewmembers( $data )
	{
		$mainframe =& JFactory::getApplication();

		$groupsModel	=&  CFactory::getModel( 'groups' );
		$friendsModel	=&  CFactory::getModel( 'friends' );
		$userModel	=&  CFactory::getModel('user');
		$my		=   CFactory::getUser();
		$config		=&  CFactory::getConfig();
		$type		=   JRequest::getVar( 'approve' , '' , 'GET' );
		$group		=&  JTable::getInstance( 'Group' , 'CTable' );
		$list		=   JRequest::getVar( 'list' , '' , 'GET' );

		if(!$group->load( $data->id ))
		{
			echo JText::_('CC GROUP NOT FOUND');
			return;
		}
		
		$document =& JFactory::getDocument();
		$document->setTitle(JText::sprintf("CC GROUP MEMBERS TITLE" , $group->name));

		$this->addPathway(JText::_('CC GROUPS'), CRoute::_('index.php?option=com_community&view=groups'));
		$this->addPathway( $group->name , CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id));
		$this->addPathway( JText::sprintf("CC GROUP MEMBERS TITLE" , $group->name ), '');
		
		CFactory::load('helpers' , 'owner' );
		$isSuperAdmin		= COwnerHelper::isCommunityAdmin();
		$isAdmin		= $groupsModel->isAdmin( $my->id , $group->id );		
		$isMember		= $group->isMember( $my->id );
		$isMine			= ($my->id == $group->ownerid);
		$isBanned		= $group->isBanned( $my->id );

		if( $group->approvals == '1' && !$isMine && !$isMember && !$isSuperAdmin )
		{
			$this->noAccess( JText::_('CC PRIVATE GROUP NOTICE') );
			return;
		}

		switch ( $list )
		{
			case COMMUNITY_GROUP_ADMIN :
				$members    =	$groupsModel->getAdmins( $data->id );
				break;
			case COMMUNITY_GROUP_BANNED :
				$members    =	$groupsModel->getBannedMembers( $data->id );
				break;
			default :
				if( !empty( $type ) && ( $type == '1' ) )
				{
					$members    =	$groupsModel->getMembers( $data->id , 0, false );
				}
				else
				{
					$members    =	$groupsModel->getMembers( $data->id , 0, true, false, SHOW_GROUP_ADMIN );
				}

		}

		$this->showSubmenu();

		// Attach avatar of the member
		// Pre-load multiple users at once
		$userids = array();
		foreach($members as $uid){ $userids[] = $uid->id; }
		CFactory::loadUsers($userids);

		$membersList = array();
		foreach($members as $member)
		{
			$user				= CFactory::getUser( $member->id );

			$user->friendsCount	= $user->getFriendCount();
			$user->approved		= $member->approved;
			$user->isMe			= ( $my->id == $member->id ) ? true : false;
			$user->isAdmin		= $groupsModel->isAdmin( $user->id , $group->id ); 
			$user->isOwner      = ( $member->id == $group->ownerid ) ? true : false;

			// Check user's permission
			$groupmember	=&  JTable::getInstance( 'GroupMembers' , 'CTable' );
			$groupmember->load( $member->id , $group->id );
			$user->isBanned	    =	( $groupmember->permissions == COMMUNITY_GROUP_BANNED ) ? true : false;
			
			$membersList[] 		= $user;
		}
		$pagination		= $groupsModel->getPagination();

		$tmpl	= new CTemplate();
		$tmpl->set( 'members'	    , $membersList );
		$tmpl->set( 'type'	    , $type );
		$tmpl->set( 'isMine'	    , $groupsModel->isCreator($my->id, $group->id));
		$tmpl->set( 'isAdmin'	    , $isAdmin );
		$tmpl->set( 'isMember'	    , $isMember );
		$tmpl->set( 'isSuperAdmin'  , $isSuperAdmin );
		$tmpl->set( 'pagination'    , $pagination );
		$tmpl->set( 'groupid'	    , $group->id );
		$tmpl->set( 'my'	    , $my );
		$tmpl->set( 'config'	    , $config );
		$tmpl->set( 'group'	    , $group );
		echo $tmpl->fetch( 'groups.viewmembers' );
	}

	/**
	 * View method to display discussions from a group
	 *
	 * @access	public
	 */
	function viewdiscussions()
	{
		$document	=& JFactory::getDocument();

		$id			= JRequest::getInt( 'groupid' , '' , 'GET' );
		$my			= CFactory::getUser();

		// Load necessary models, libraries & helpers
		CFactory::load( 'models' , 'groups' );
		CFactory::load( 'helpers' , 'owner' );
		$model		= CFactory::getModel( 'discussions' );

		// Load the group
		$group		=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $id );
		$this->_addGroupInPathway( $group->id );		
		$this->addPathway( JText::_('CC DISCUSSIONS') );
		$params		= $group->getParams();
		
		//check if group is valid
		if( $group->id == 0 )
		{
			echo JText::_('CC INVALID GROUP ID');
			return;
		}
		
		//display notice if the user is not a member of the group
		if( $group->approvals == 1 && !($group->isMember($my->id) ) && !COwnerHelper::isCommunityAdmin() )
		{
			$this->noAccess( JText::_('CC PRIVATE GROUP NOTICE') );
			return;
		}
		
		// Set page title
		$document->setTitle( JText::sprintf('CC VIEW ALL DISCUSSIONS TITLE' , $group->name ) );

		$feedLink = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussions&groupid=' . $group->id . '&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" title="'. JText::_('CC SUBSCRIBE TO DISCUSSION FEEDS') .'" href="'.$feedLink.'"/>';
		$document->addCustomTag( $feed );

		// Load submenu
		$this->showSubMenu();
		
		$discussions	= $model->getDiscussionTopics( $group->id , 0 ,  $params->get('discussordering' , DISCUSSION_ORDER_BYLASTACTIVITY) );
		
		for( $i = 0; $i < count( $discussions ); $i++ )
		{
			$row		=& $discussions[$i];

			$row->user	= CFactory::getUser( $row->creator );
		}

		// Process discussions HTML output
		$tmpl		= new CTemplate();
		$tmpl->set( 'discussions'	, $discussions );
		$tmpl->set( 'groupId'		, $group->id );
		$discussionsHTML	= $tmpl->fetch( 'groups.discussionlist' );
		unset( $tmpl );

		$tmpl			= new CTemplate();
		$tmpl->set( 'group'				, $group );
		$tmpl->set( 'discussions'		, $discussions );
		$tmpl->set( 'discussionsHTML'	, $discussionsHTML );
		$tmpl->set( 'pagination'		, $model->getPagination() );
		echo $tmpl->fetch( 'groups.viewdiscussions' );
	}

	/**
	 * View method to display specific discussion from a group
	 *
	 * @access	public
	 * @param	Object	Data object passed from controller
	 */
	function viewdiscussion( )
	{
		$mainframe	=& JFactory::getApplication();
		$document	=& JFactory::getDocument();
		$jconfig	= JFactory::getConfig();
		// Load window library
		CFactory::load( 'libraries' , 'window' );
		
		// Load necessary window css / javascript headers.
		CWindow::load();

		// Get necessary variables
		CFactory::load( 'models' , 'groups' );
		CFactory::load( 'models' , 'discussions' );
		$my				= CFactory::getUser();
		$groupId		= JRequest::getInt( 'groupid' , '' , 'GET' );
		$topicId		= JRequest::getInt( 'topicid' , '' , 'GET' );

		// Load necessary library and objects
		$groupModel		= CFactory::getModel( 'groups' );
		$group			=& JTable::getInstance( 'Group' , 'CTable' );
		$discussion		=& JTable::getInstance( 'Discussion' , 'CTable' );
		$group->load( $groupId );
		$discussion->load( $topicId );
		
		$mainframe->addCustomHeadTag('<link rel="image_src" href="'. $group->getThumbAvatar() .'" />');
		
		// @rule: Test if the group is unpublished, don't display it at all.
		if( !$group->published )
		{
			$this->noAccess( JText::_('CC GROUP UNPUBLISHED') );
			return;
		}
		
		$feedLink = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&topicid=' . $topicId . '&format=feed');
		$feed = '<link rel="alternate" type="application/rss+xml" title="'. JText::_('CC GROUP DISCUSSION REPLY FEED') .'"  href="'.$feedLink.'"/>'; 
		$document->addCustomTag( $feed );
		
		CFactory::load( 'helpers' , 'owner' );
		if( $group->approvals == 1 && !($group->isMember($my->id) ) && !COwnerHelper::isCommunityAdmin() )
		{
			$this->noAccess( JText::_('CC PRIVATE GROUP NOTICE') );
			return;
		}
		
		// Execute discussion onDisplay filter
		$appsLib	=& CAppPlugins::getInstance();
		$appsLib->loadApplications();
		$args = array();
		$args[]		=& $discussion;
		$appsLib->triggerEvent( 'onDiscussionDisplay',  $args );
		
		// Get the discussion creator info
		$creator		= CFactory::getUser( $discussion->creator );

		// Format the date accordingly.
		$discussion->created	= CTimeHelper::getDate( $discussion->created );

		// Set page title
		$document->setTitle( JText::sprintf( 'CC DISCUSSION VIEW TITLE' , $discussion->title ) );

		// Add pathways
		$this->_addGroupInPathway( $group->id );
		$this->addPathway( JText::_('CC DISCUSSIONS') , CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussions&groupid=' . $group->id) ); 		
		$this->addPathway( JText::sprintf( 'CC DISCUSSION VIEW TITLE' , $discussion->title ) );

		CFactory::load( 'helpers' , 'owner' );
		
		$isGroupAdmin	=   $groupModel->isAdmin( $my->id , $group->id );
		
		if( $my->id==$creator->id || $isGroupAdmin || COwnerHelper::isCommunityAdmin() )
		{
			$title	= JText::_('CC DELETE DISCUSSION');    
			                                              
			$titleLock	= $discussion->lock ? JText::_('CC UNLOCK DISCUSSION') : JText::_('CC LOCK DISCUSSION'); 
			$actionLock	= $discussion->lock ? JText::_('CC UNLOCK') : JText::_('CC LOCK'); 
			                                     
			$this->addSubmenuItem( '' , $actionLock , "joms.groups.lockTopic('" . $titleLock . "','" . $group->id . "','" . $discussion->id . "');" , SUBMENU_RIGHT );  
			$this->addSubmenuItem( '' , JText::_('CC DELETE') , "joms.groups.removeTopic('" . $title . "','" . $group->id . "','" . $discussion->id . "');" , SUBMENU_RIGHT );
			$this->addSubmenuItem( 'index.php?option=com_community&view=groups&task=editdiscussion&groupid=' . $group->id . '&topicid=' . $discussion->id , JText::_('CC EDIT') , '' , SUBMENU_RIGHT );
		}
	
		
		$this->showSubmenu();

		CFactory::load( 'libraries' , 'wall' );
		$wallContent	= CWallLibrary::getWallContents( 'discussions' , $discussion->id , $isGroupAdmin , $jconfig->get('list_limit') , 0, 'wall.content','groups,discussion');
		$wallCount		= CWallLibrary::getWallCount('discussions', $discussion->id);
		
		$viewAllLink	= CRoute::_('index.php?option=com_community&view=groups&task=discussapp&topicid=' . $discussion->id . '&app=walls');
		$wallContent	.= CWallLibrary::getViewAllLinkHTML($viewAllLink, $wallCount);

		// Test if the current browser is a member of the group
		$isMember			= $group->isMember( $my->id );
		$waitingApproval	= false;

		// If I have tried to join this group, but not yet approved, display a notice
		if( $groupModel->isWaitingAuthorization( $my->id , $group->id ) )
		{
			$waitingApproval	= true;
		}

		$wallForm	=	'';
		$config		= CFactory::getConfig();
		// Only get the wall form if user is really allowed to see it.
		if( !$config->get('lockgroupwalls') || ($config->get('lockgroupwalls') && ($isMember) && !($waitingApproval) ) || COwnerHelper::isCommunityAdmin() )
		{
			$outputLock		= '<div class="warning">' . JText::_('CC DISCUSSION LOCKED NOTICE') . '</div>';
			$outputUnLock	= CWallLibrary::getWallInputForm( $discussion->id , 'groups,ajaxSaveDiscussionWall', 'groups,ajaxRemoveReply' );
			
			$wallForm		= $discussion->lock ? $outputLock : $outputUnLock ; 
		}

		if( empty($wallForm ) )
		{
			$wallForm	= JText::_('CC YOU NEED TO BE MEMBER OF GROUP TO POST REPLY');
		}
		
		$config		= CFactory::getConfig();

		// Get creator link
		$creatorLink	= CRoute::_('index.php?option=com_community&view=profile&userid=' . $creator->id );
		
		// Get reporting html
		CFactory::load('libraries', 'reporting');
		$report		= new CReportingLibrary();
		$reportHTML	= $report->getReportingHTML( JText::_('CC REPORT DISCUSSION') , 'groups,reportDiscussion' , array( $discussion->id ) );
		CFactory::load( 'libraries' , 'bookmarks' );
		$bookmarks		= new CBookmarks(CRoute::getExternalURL( 'index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $group->id . '&topicid=' . $discussion->id));
		$bookmarksHTML	= $bookmarks->getHTML();

		$tmpl	= new CTemplate();
		$tmpl->set( 'bookmarksHTML'	, $bookmarksHTML );
		$tmpl->set( 'discussion' 	, $discussion );
		$tmpl->set( 'creator'		, $creator );
		$tmpl->set( 'wallContent'	, $wallContent );
		$tmpl->set( 'wallForm'		, $wallForm );
		$tmpl->set( 'creatorLink'	, $creatorLink );
		$tmpl->set( 'reportHTML'	, $reportHTML );
 		echo $tmpl->fetch( 'groups.viewdiscussion' );
	}

	/**
	 * View method to display new discussion form
	 *
	 * @access	public
	 * @param	Object	Data object passed from controller
	 */
	function adddiscussion( &$discussion )
	{
		$document =& JFactory::getDocument();
        $document->setTitle( JText::_('CC ADD DISCUSSION') );

		$groupId		= JRequest::getVar('groupid' , '' , 'GET');

		$this->_addGroupInPathway( $groupId );		
		$this->addPathway( JText::_('CC ADD DISCUSSION') );
		
		$this->showSubmenu();

		jimport( 'joomla.html.editor' );
		$config			= CFactory::getConfig();
		$editorType = ($config->get('allowhtml') )? $config->get('htmleditor' , 'none') : 'none' ;
        $editor		= new JEditor( $editorType );

		CFactory::load( 'models' , 'groups' );
		$group			=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $groupId );

		CFactory::load( 'libraries' , 'apps' );
		$app 		=& CAppPlugins::getInstance();
		$appFields	= $app->triggerEvent('onFormDisplay' , array('jsform-groups-discussionform'));
		$beforeFormDisplay	= CFormElement::renderElements( $appFields , 'before' );
		$afterFormDisplay	= CFormElement::renderElements( $appFields , 'after' );

		$tmpl	= new CTemplate();
		$tmpl->set( 'beforeFormDisplay', $beforeFormDisplay );
		$tmpl->set( 'afterFormDisplay'	, $afterFormDisplay );
		$tmpl->set( 'config'	, $config );
        $tmpl->set( 'editor'	, $editor );
		$tmpl->set( 'group'		, $group );
		$tmpl->set( 'discussion', $discussion );
		
		echo $tmpl->fetch( 'groups.adddiscussion' );
	}

	/**
	 * View method to display new discussion form
	 *
	 * @access	public
	 * @param	Object	Data object passed from controller
	 */
	function editdiscussion( $discussion )
	{
		$document =& JFactory::getDocument();
        $document->setTitle( JText::_('CC EDIT DISCUSSION') );

		$groupId		= JRequest::getVar('groupid' , '' , 'GET');
		$topicId		= JRequest::getVar('topicid' , '' , 'GET');
		
		$this->_addGroupInPathway( $groupId );		
		$this->addPathway( JText::_('CC EDIT DISCUSSION') );
		
		$this->showSubmenu();

		jimport( 'joomla.html.editor' );
		$config			= CFactory::getConfig();
		$editorType = ($config->get('allowhtml') )? $config->get('htmleditor' , 'none') : 'none' ;
        $editor		= new JEditor( $editorType );
        
		CFactory::load( 'models' , 'groups' );
		$group			=& JTable::getInstance( 'Group' , 'CTable' );
		$group->load( $groupId );
		
		// Santinise output
		CFactory::load( 'helpers' , 'string' );
		$discussion->title	= strip_tags($discussion->title);
		$discussion->title	= CStringHelper::escape($discussion->title);

		CFactory::load( 'libraries' , 'apps' );
		$app 		=& CAppPlugins::getInstance();
		$appFields	= $app->triggerEvent('onFormDisplay' , array('jsform-groups-discussionform'));
		$beforeFormDisplay	= CFormElement::renderElements( $appFields , 'before' );
		$afterFormDisplay	= CFormElement::renderElements( $appFields , 'after' );

		$tmpl	= new CTemplate();
		$tmpl->set( 'beforeFormDisplay', $beforeFormDisplay );
		$tmpl->set( 'afterFormDisplay'	, $afterFormDisplay );
		$tmpl->set( 'config'	, $config );
		$tmpl->set( 'editor'	, $editor );
		$tmpl->set( 'group'		, $group );
		$tmpl->set( 'discussion', $discussion );
		
		echo $tmpl->fetch( 'groups.editdiscussion' );
	}
	/**
	 * View method to search groups
	 *
	 * @access	public
	 *
	 * @returns object  An object of the specific group
	 */
	function search()
	{
		// Get the document object and set the necessary properties of the document
		$document   =&	JFactory::getDocument();
		$document->setTitle( JText::_('CC SEARCH TITLE') );

		$this->addPathway( JText::_('CC GROUPS'), CRoute::_('index.php?option=com_community&view=groups') );
		$this->addPathway( JText::_("CC SEARCH"), '');
		
		// Display the submenu
		$this->showSubmenu();

		$search		=   JRequest::getVar( 'search', '' );
		$catId		=   JRequest::getVar( 'catid', '' );
		$groups		=   '';
		$pagination	=   null;
		$posted		=   false;
		$count		=   0;

		$category	=&  JTable::getInstance( 'GroupCategory' , 'CTable' );
		$category->load( $catId );

		$model	    =	CFactory::getModel( 'groups' );

		$categories =	$model->getCategories();

		// Test if there are any post requests made
		if( ( !empty( $search ) || !empty( $catId ) ) )
		{
			JRequest::checkToken( 'get' ) or jexit( JText::_( 'CC INVALID TOKEN' ) );

			CFactory::load( 'libraries' , 'apps' );
			$appsLib	=&  CAppPlugins::getInstance();
			$saveSuccess	=   $appsLib->triggerEvent( 'onFormSave' , array('jsform-groups-search' ));

			if( empty($saveSuccess) || !in_array(false , $saveSuccess) )
			{
				$posted	    =	true;
				$groups	    =	$model->getAllGroups( $category->id , null , $search );
				$pagination =	$model->getPagination();
				$count	    =	$model->getGroupsSearchTotal();
			}
		}

		// Get the template for the group lists
		$groupsHTML	= $this->_getGroupsHTML( $groups, $pagination );

		CFactory::load( 'libraries' , 'apps' );
		$app			=&  CAppPlugins::getInstance();
		$appFields		=   $app->triggerEvent('onFormDisplay' , array('jsform-groups-search'));
		$beforeFormDisplay	=   CFormElement::renderElements( $appFields , 'before' );
		$afterFormDisplay	=   CFormElement::renderElements( $appFields , 'after' );

		$tmpl	= new CTemplate();
		$tmpl->set( 'beforeFormDisplay'	, $beforeFormDisplay );
		$tmpl->set( 'afterFormDisplay'	, $afterFormDisplay );
		$tmpl->set( 'posted'		, $posted );
		$tmpl->set( 'groupsCount'	, $count );
		$tmpl->set( 'groupsHTML'	, $groupsHTML );
		$tmpl->set( 'search'		, $search );
		$tmpl->set( 'categories'	, $categories );
		$tmpl->set( 'catId'		, $category->id );

		echo $tmpl->fetch( 'groups.search' );
	}

	/**
	 * Method to display add new bulletin form
	 *
	 * @param	$title	The title of the bulletin if the adding failed
	 * @param	$message	The message of the bulletin if adding failed
	 **/
	function addNews( $bulletin )
	{
		// Get the document object and set the necessary properties of the document
		$document	=& JFactory::getDocument();
        $document->setTitle( JText::_('CC ADD NEWS TITLE') );
        $this->showSubmenu();

		$config		= CFactory::getConfig();
		$groupId	= JRequest::getInt( 'groupid' , '' , 'GET' ); 

		// Add pathways
		$this->_addGroupInPathway( $groupId );		
		$this->addPathway( JText::_('CC ADD BULLETIN' ) );

		jimport( 'joomla.html.editor' );
		$editor		= new JEditor( $config->get('htmleditor' , 'none') );
		$title		= ( $bulletin ) ? $bulletin->title : '';
		$message	= ( $bulletin ) ? $bulletin->message : '';

		$tmpl		= new CTemplate();
		$tmpl->set( 'config'	, $config );
		$tmpl->set( 'title'		, $title );
		$tmpl->set( 'message'	, $message );
        $tmpl->set( 'groupid' 	, $groupId );
        $tmpl->set( 'editor'	, $editor );
        echo $tmpl->fetch( 'groups.addnews' );
	}
	
	function _getGroupsHTML( $tmpGroups, $tmpPagination = NULL)
	{	
		$tmpl	= new CTemplate();
		CFactory::load( 'helpers' , 'owner' );

		CFactory::load( 'libraries' , 'featured' );
		$featured	= new CFeatured( FEATURED_GROUPS );
		$featuredList	= $featured->getItemIds();

		$task			= JRequest::getVar( 'task' , '' , 'GET' );
		$showFeatured	= (empty($task) ) ? true : false;
		
		$groups	= array();
		CFactory::load( 'models' , 'groups' );
		
		if( $tmpGroups )
		{
			foreach( $tmpGroups as $row )
			{
				$group	=& JTable::getInstance( 'Group' , 'CTable' );
				$group->load( $row->id );
				$groups[]	= $group;
			}
			unset($tmpGroups);
		}
		
		$tmpl->set( 'showFeatured'		, $showFeatured );
		$tmpl->set( 'featuredList'		, $featuredList );
		$tmpl->set( 'isCommunityAdmin'	, COwnerHelper::isCommunityAdmin() );
		$tmpl->set( 'groups'			, $groups );
		$tmpl->set( 'pagination'		, $tmpPagination );
		$groupsHTML	= $tmpl->fetch( 'groups.list' );
		unset( $tmpl );
		
		return $groupsHTML;
	} 
}
