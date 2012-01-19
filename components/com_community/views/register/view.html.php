<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');
jimport( 'joomla.utilities.arrayhelper');
jimport( 'joomla.html.html');

class CommunityViewRegister extends CommunityView
{
	function register($data = null)
	{
		require_once (JPATH_COMPONENT.DS.'libraries'.DS.'profile.php');
				
		$mainframe	=& JFactory::getApplication();
		$my 		= CFactory::getUser();
		
		$config		= CFactory::getConfig();
		$document 	=& JFactory::getDocument();
		$document->setTitle(JText::_('CC REGISTER NEW'));		
		
		// Hide this form for logged in user
		if($my->id) {
			$mainframe->enqueueMessage(JText::_('CC ALREADY USER'), 'warning');
			return;	
		}
		
		// If user registration is not allowed, show 403 not authorized.
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		if ($usersConfig->get('allowUserRegistration') == '0')		
		{
			//show warning message						
			$this->addWarning(JText::_( 'CC REGISTRATION DISABLED' ));
			return;
		}

		$fields 	= array();	
		$empty_html = array();
		$post 		= JRequest::get('post');

		CFactory::load('helpers', 'user');
		$isUseFirstLastName	= CUserHelper::isUseFirstLastName();

		$data								= array();
		$data['fields']						= $fields;
		$data['html_field']['jsname'] 		= (empty($post['jsname'])) ? '' : $post['jsname'];
		$data['html_field']['jsusername']	= (empty($post['jsusername'])) ? '' : $post['jsusername'];
		$data['html_field']['jsemail'] 		= (empty($post['jsemail'])) ? '' : $post['jsemail'];
		$data['html_field']['jsfirstname']	= (empty($post['jsfirstname'])) ? '' : $post['jsfirstname'];
		$data['html_field']['jslastname']	= (empty($post['jslastname'])) ? '' : $post['jslastname'];

		$js = 'assets/validate-1.5';
		$js	.= ( $config->getBool('usepackedjavascript') ) ? '.pack.js' : '.js';
		CAssets::attach($js, 'js');
		
		// @rule: Load recaptcha if required.
		CFactory::load( 'helpers' , 'recaptcha' );
		$recaptchaHTML	= getRecaptchaHTMLData();
		
		$tmpl			= new CTemplate();
		$tmpl->set( 'data' 			, $data );
		$tmpl->set( 'recaptchaHTML' , $recaptchaHTML );
		$tmpl->set( 'config'		, $config );
		$tmpl->set( 'isUseFirstLastName'	, $isUseFirstLastName );
		
		$content	= $tmpl->fetch( 'register.index' );
		
		$appsLib	=& CAppPlugins::getInstance();
		$appsLib->loadApplications();
				
		$args		= array(&$content);
		$appsLib->triggerEvent( 'onUserRegisterFormDisplay' , $args );				
		
		echo $content;
	}
	
	/**
	 * Displays the form where user selects their profile type.
	 **/	 	
	public function registerProfileType()
	{
		$mainframe	=& JFactory::getApplication();
		$document	=& JFactory::getDocument();
		$document->setTitle( JText::_('CC MULTIPROFILE SELECT TYPE') );

		$model	= CFactory::getModel( 'Profile' );
		$tmp	= $model->getProfileTypes();

		$profileTypes	= array();
		$showNotice		= false;
		foreach( $tmp as $profile )
		{
			$table	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
			$table->load( $profile->id );
			
			if( $table->approvals )
				$showNotice	= true;
				
			$profileTypes[]	= $table;
		}
		
		$tmpl		= new CTemplate();
		$tmpl->set( 'default'	, 0 );
		$tmpl->set( 'profileTypes'	, $profileTypes );
		$tmpl->set( 'showNotice'	, $showNotice );
		$tmpl->set( 'message'	, JText::_('CC MULTIPROFILE INFO') );
		
		echo $tmpl->fetch( 'register.profiletype' );
	}
	
	/**
	 * Display custom profiles registration form.
	 **/
	function registerProfile( $fields )
	{
		CFactory::load( 'libraries' , 'profile' );
		jimport( 'joomla.utilities.arrayhelper' );
		jimport( 'joomla.utilities.date' );
	
		$mainframe	=& JFactory::getApplication();	
		$document 	=& JFactory::getDocument();		
		$document->setTitle( JText::_('CC REGISTER NEW') );
		
		$model 			= CFactory::getModel('profile');
		$profileType	= JRequest::getVar( 'profileType' , 0 );
		$config			= CFactory::getConfig();
		$profileTypes	= $model->getProfileTypes();
		
		// @rule: When multiple profile is enabled, and profile type is not selected, we should trigger an error.
		if( $config->get('profile_multiprofile') && $profileType == COMMUNITY_DEFAULT_PROFILE && !empty( $profileTypes ) )
		{
			$mainframe->redirect( CRoute::_('index.php?option=com_community&view=register&task=registerProfileType' , false ) , JText::_('CC NO PROFILE TYPE SELECTED') , 'error' );
		}
				
		$empty_html = array();
		$post = JRequest::get('post');
		
		CFactory::load('helpers', 'user');
		$isUseFirstLastName	= CUserHelper::isUseFirstLastName();
		
		$firstName		= '';
		$lastName		= '';
		if ($isUseFirstLastName)
		{
			$fullname	= $this->_getFirstLastName();
			$firstName	= $fullname['first'];
			$lastName	= $fullname['last'];
		}
		
		// Bind result from previous post into the field object
		if(! empty($post))
		{
			foreach($fields as $group)
			{
			    $field = $group->fields;
			    for($i = 0; $i <count($field); $i++)
				{
	 				$fieldid    = $field[$i]->id;
	 				$fieldType  = $field[$i]->type;
	 				
					if(!empty($post['field'.$fieldid]))
					{
						if(is_array($post['field'.$fieldid]))
						{
						   if($fieldType != 'date')
						   {
						        $values = $post['field'.$fieldid];
						        $value  = '';
								foreach($values as $listValue)
								{
									$value	.= $listValue . ',';
								}
						        $field[$i]->value = $value;
						   }
						   else 
						   {
						       $field[$i]->value = $post['field'.$fieldid];
						   }
						} 
						else 
						{
						    $field[$i]->value = $post['field'.$fieldid];						
						}
					}
                }
			}
		} 
		else 
		{
			if ($isUseFirstLastName)
			{
				foreach($fields as $group)
				{
				    $field = $group->fields;
				    for($i = 0; $i <count($field); $i++)
					{
		 				if ($field[$i]->fieldcode == 'FIELD_GIVENNAME')
		 					$field[$i]->value = $firstName;
		 				if ($field[$i]->fieldcode == 'FIELD_FAMILYNAME')
		 					$field[$i]->value = $lastName;
	                }
				}
			}
		}
		
		$config		= CFactory::getConfig();
		$js	= 'assets/validate-1.5'.(( $config->getBool('usepackedjavascript') ) ? '.pack.js' : '.js');
        CAssets::attach($js, 'js');

		$profileType	= JRequest::getVar( 'profileType' , 0 , 'GET' );
		
		$tmpl	= new CTemplate();
		$tmpl->set( 'fields' , $fields );
		$tmpl->set( 'profileType' , $profileType );
		echo $tmpl->fetch( 'register.profile' );
	}
	
	/**
	 * Display Upload avatar form for user
	 **/	 	
	function registerAvatar()
	{
		$mainframe =& JFactory::getApplication();

        //retrive the current session.		
        $mySess =& JFactory::getSession();
		$user		= CFactory::getUser($mySess->get('tmpUser','')->id);
		$firstLogin	= true;

		$uploadLimit = ini_get('upload_max_filesize');
		$uploadLimit = JString::str_ireplace('M', ' MB', $uploadLimit);		
		
		// Load the toolbar
		$this->showSubmenu();
		$document = & JFactory::getDocument ();
		$document->setTitle ( JText::_ ( 'CC EDIT AVATAR' ) );
		$profileType	= JRequest::getVar( 'profileType' , 0 , 'GET' );
		$tmpl	  = new CTemplate();
		$skipLink = CRoute::_('index.php?option=com_community&view=register&task=registerSucess&profileType=' . $profileType );
		
		$tmpl->set( 'profileType'	, $profileType );
		$tmpl->set( 'user' , $user );
		$tmpl->set( 'uploadLimit' , $uploadLimit );
		$tmpl->set( 'firstLogin' , $firstLogin );
		$tmpl->set( 'skipLink' , $skipLink );
		
		echo $tmpl->fetch( 'profile.uploadavatar' );
	}	
	
    function registerSucess()
	{
		$document =& JFactory::getDocument();				
		$document->setTitle(JText::_('CC USER REGISTERD'));
				
		$uri				= CRoute::_('index.php?option=com_community&view=frontpage');
        $usersConfig		= &JComponentHelper::getParams( 'com_users' );
        $useractivation		= $usersConfig->get( 'useractivation' );
		$profileType		= JRequest::getVar( 'profileType' , '' );
		$message			= JText::_( 'CC_REG_COMPLETE' );
		$multiprofile	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
		$multiprofile->load( $profileType );

		if( $multiprofile->approvals )
		{
			$message	= JText::_( 'CC REGISTRATION COMPLETED NEED APPROVAL' );
		}
		
		if( $useractivation == 1 && !$multiprofile->approvals )
		{
			$message	= JText::_( 'CC_REG_COMPLETE_ACTIVATE_REQUIRED' ); 
		}

        $tmpl	= new CTemplate();
        $tmpl->set( 'message'	, $message );
		$tmpl->set( 'uri'		, $uri );
		echo $tmpl->fetch( 'register.success' );    
	}
	
	function activation()
	{
		$config		= CFactory::getConfig();
		$document 	=& JFactory::getDocument ();
		$document->setTitle ( JText::_ ( 'CC RESEND ACTIVATION' ) );
						
		$js	= 'assets/validate-1.5'.(( $config->getBool('usepackedjavascript') ) ? '.pack.js' : '.js');
        CAssets::attach($js, 'js');
		
		$tmpl	  = new CTemplate();
		echo $tmpl->fetch( 'register.activation' );		
	}
	
	private function _getFirstLastName()
	{
		$tmpUserModel	= CFactory::getModel('register');
		$mySess 		=& JFactory::getSession();
		$tmpUser		= $tmpUserModel->getTempUser($mySess->get('JS_REG_TOKEN',''));
		
		$fullname		= array();
		$fullname['first']	= $tmpUser->firstname;
		$fullname['last']	= $tmpUser->lastname;
		
		return $fullname;
	}
}
