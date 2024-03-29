<?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class CommunityRegisterController extends CommunityBaseController
{
	
	/**
	 * Display the new user registration form
	 */	 	
	public function register()
	{
		require_once (JPATH_COMPONENT.DS.'libraries'.DS.'profile.php');
		
		$db 		=& JFactory::getDBO();
		$document 	=& JFactory::getDocument();
		
		//run this silently to clean up the 'left-over' temp user.
		$rModel		= CFactory::getModel('register');
		$rModel->cleanTempUser();
								
		//we use session to store the token string so that if other component
		// altered the token string, we stil able to get back.
		$mySess 	= JFactory::getSession();
		
		// Always restart the session whenever a user visit this page
		if(JRequest::getVar('jsname', '', 'POST') == '') {
			//$mySess->restart();
		}

		$view =& $this->getView('register');
		echo $view->get('register');
	}
	
	public function register_save()
	{
		$mainframe	=& JFactory::getApplication();
		$modelRegister		= CFactory::getModel('register');
		
		// Check for request forgeries
		$mySess 	=& JFactory::getSession();
		
	    if(! $mySess->has('JS_REG_TOKEN'))
	    {
			echo '<div class="error-box">' . JText::_('CC INVALID SESSION') . '</div>';
			return;	    
	    }
		
		$token		= $mySess->get('JS_REG_TOKEN','');
		$ipAddress	= isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		$authKey	= $modelRegister->getAssignedAuthKey($token, $ipAddress);
		$formToken	= JRequest::getVar( 'authkey', '', 'REQUEST');
					
		if(empty($formToken) || empty($authKey) || ($formToken != $authKey))
		{
			//echo $formToken .'|'. $authKey;
			echo '<div class="error-box">' . JText::_('CC INVALID TOKEN') . '</div>';
			return;
		}
		
		//update the auth key life span to another 180 sec.
		$modelRegister->updateAuthKey ($token, $authKey, $ipAddress);
		
		// Get required system objects
		$config		= CFactory::getConfig();
		$post		= JRequest::get('post');
		
		// If user registration is not allowed, show 403 not authorized.
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		if ($usersConfig->get('allowUserRegistration') == '0')		
		{
			//show warning message		
			$view =& $this->getView('register');
			$view->addWarning(JText::_( 'CC REGISTRATION DISABLED' ));
			echo $view->get('register');									
			return;
		}
		
		//perform forms validation before continue further.
		/*
		 * Rules:
		 * First we let 3rd party plugin to intercept the validation.
		 * if there is not error return, we then proceed with our validation.
		 */
		$errMsg 	= array();
		$errTrigger = null;
		
		CFactory::load( 'libraries' , 'apps' );
		$appsLib	=& CAppPlugins::getInstance();
		$appsLib->loadApplications();

		$params		= array();
		$params[]	= $post;
		$errTrigger = $appsLib->triggerEvent( 'onRegisterValidate' , $params);

		if(is_null($errTrigger))
		{
		    //no trigger found.
		    $errMsg = $this->_validateRegister($post);
		}
		else
		{
		    if(! empty($errTrigger[0]))
		    {
		        $errMsg = $errTrigger[0];
		    }
			else
			{
			    // trigger found but no error return.
             	$errMsg = $this->_validateRegister($post);
			}
		}
				
		if(count($errMsg) > 0)
		{
		   //validation failed. show error message.
		   foreach ($errMsg as $err)
		   {		       
		       $mainframe->enqueueMessage($err, 'error');
		   }
		   $this->register();
		   return false;
		}

		// @rule: check with recaptcha
		$private	= $config->get('recaptchaprivate');
		$public		= $config->get('recaptchapublic');
		
		if( $config->get('recaptcha') == 1 && !empty( $public ) && !empty( $private ) )
		{
			CFactory::load( 'helpers' , 'recaptcha' );
			$ipAdddress	= isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
			$response	= recaptcha_check_answer( $private , $ipAddress , $_POST['recaptcha_challenge_field'] , $_POST['recaptcha_response_field'] );
			
			if(!$response->is_valid)
			{
				JError::raiseWarning('', JText::_( 'CC RECAPTCHA MISMATCH'));
				$this->register();
				return false;
			}			
		}
		
		//adding to temp reg table.
		if(! $modelRegister->addTempUser($post))
		{
            JError::raiseWarning('', JText::_( 'CC ERROR IN REGISTRATION'));		    
			$this->register();
			return false;
		}
		
		// Send the first email to inform user of their username and password
		$tmpUser		= $modelRegister->getTempUser($token);
		$password		= (string) $post['jspassword2'];
		
		//now we check whether there is any custom profile? if not, then we do the actual save here.
		$modelProfile 		= CFactory::getModel('profile');
		
		//get all published custom field for profile
		$filter = array('published'=>'1', 'registration' => '1');
		$fields =& $modelProfile->getAllFields($filter);
		
		// If there are no fields, we do not want to move to the edit profile area.
		if(count($fields) <= 0)
		{
			//do the actual user save.
			$user			= $this->_createUser($tmpUser);
			
			$this->_sendEMail('registration', $user, $password);
			
	        // now we need to set it for later avatar upload page
	        // do the clear up job for tmp user.
	        $mySess->set('tmpUser',$user);
	        
	        $modelRegister->removeTempUser($token);
	        $modelRegister->removeAuthKey($token);        
			
	        //redirect to avatar upload page. 
	        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=register&task=registerAvatar', false));						
		}
		else
		{
			$this->_sendEMail('registration_uncomplete', $tmpUser, $password);
			
			//redirect to profile update page.
			$model			= CFactory::getModel( 'Profile' );
			$profileTypes	= $model->getProfileTypes();

			// @rule: When there are no defined profile types, we will use the default.
			if( !$profileTypes || !$config->get('profile_multiprofile') )
			{
				$mainframe->redirect(CRoute::_('index.php?option=com_community&view=register&task=registerProfile&profileType=' . COMMUNITY_DEFAULT_PROFILE , false));
			}
			else
			{
				// Now that the username and name are properly entered, redirect them to select the profile type.
				$mainframe->redirect(CRoute::_('index.php?option=com_community&view=register&task=registerProfileType', false));
			}
		}
	}
	
	/**
	 * Display and process the multiple profile types.
	 * 	 
	 **/	 	
	public function registerProfileType()
	{
		$mainframe	=& JFactory::getApplication();
		$mySess 	=& JFactory::getSession();
		$config		= CFactory::getConfig();
		
		if( !$config->get('profile_multiprofile') )
		{
			echo JText::_('CC MULTIPROFILE IS CURRENTLY DISABLED');
			return;
		}
		
	    if(! $mySess->has('JS_REG_TOKEN'))
	    {						
			$mainframe->redirect(CRoute::_('index.php?option=com_community&view=register', false));
			return;
	    }
		$view =& $this->getView('register');
		
		
		if( JRequest::getMethod() == 'POST' )
		{
			$type	= JRequest::getVar( 'profileType' , 0 );
			
			// @rule: When multiple profile is enabled, and profile type is not selected, we should trigger an error.
			if( $config->get('profile_multiprofile') && $type == COMMUNITY_DEFAULT_PROFILE )
			{
				$mainframe->enqueueMessage( JText::_('CC NO PROFILE TYPE SELECTED') , 'error' );
			}
			else
			{
				$mainframe->redirect( CRoute::_('index.php?option=com_community&view=register&task=registerProfile&profileType=' . $type , false) );
			}
		}
		echo $view->get( __FUNCTION__ );
	}

	/**
	 * Display custom profiles for the user during registrations.
	 **/
	public function registerProfile()
	{
		$mainframe	=& JFactory::getApplication();
		$mySess 	=& JFactory::getSession();		

	    if(! $mySess->has('JS_REG_TOKEN'))
		{
			$mainframe->redirect(CRoute::_('index.php?option=com_community&view=register', false));
			return;
	    }

		// Get all published custom field for profile
		$filter		= array('published'=>'1', 'registration' => '1' );

	    $profileType	= JRequest::getVar( 'profileType' , 0 );
		$profileModel	= CFactory::getModel('profile');
		$fields			=& $profileModel->getAllFields( $filter , $profileType );
		
		if( empty($fields) )
		{
			$mySess 	=& JFactory::getSession();
			$token		= $mySess->get('JS_REG_TOKEN','');

			$profileType	= JRequest::getVar( 'profileType' , 0 , 'POST' );
			$multiprofile	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
			$multiprofile->load( $profileType );
			
			$model = CFactory::getModel( 'register' );
			$tmpUser		= $model->getTempUser($token);
			$user		= $this->_createUser($tmpUser , $multiprofile->approvals , $multiprofile->id );

	        $mySess->set('tmpUser',$user);
	
	        $model->removeTempUser($token);
	        $model->removeAuthKey($token);
	  		
			$this->_sendEMail('registration_complete', $user , null , $multiprofile->approvals );
						        
			// If no fields created yet, the system should be intelligent enough to automatically sense it and redirect users to the register avatar page.
			$mainframe->redirect( CRoute::_('index.php?option=com_community&view=register&task=registerAvatar' , false ) );
		}
		
		CFactory::load( 'libraries' , 'profile' );
		
		$document 	=& JFactory::getDocument();	
		$view		=& $this->getView('register');
		
		echo $view->get('registerProfile' , $fields );
	}
	
	/**
	 * Private method to create a user in the site.
	 **/	 	
	private function _createUser($tmpUser , $requireApproval = false , $profileType = 0 )
	{
		$user 			= clone(JFactory::getUser());
		$config			=& JFactory::getConfig();
		$authorize		=& JFactory::getACL();
		$usersConfig	=& JComponentHelper::getParams( 'com_users' );		
		
		if(empty($tmpUser))
		{
			JError::raiseError( 500, JText::_('CC REGISTRATION MISSING USER OBJ'));
			return;
		}
		$userObj 	= get_object_vars($tmpUser);
		
		// Get usertype from configuration. If tempty, user 'Registered' as default		
		$newUsertype	= $usersConfig->get( 'new_usertype' );
		if (!$newUsertype)
		{
			$newUsertype = 'Registered';
		}
		
		// Bind the post array to the user object
		if (!$user->bind( $userObj, 'usertype' )) {
			JError::raiseError( 500, $user->getError());
		}
		
		// Initialize user default values
		$date =& JFactory::getDate();
		
		if( $requireApproval )
		{
			$user->set( 'block' , 1 );
		}
		
		$user->set('id', 0);
		$user->set('usertype', $newUsertype );
		$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));			
		$user->set('registerDate', $date->toMySQL());
	
		// If user activation is turned on, we need to set the activation information
 		$useractivation = $usersConfig->get( 'useractivation' );
		if($useractivation == '1' && !$requireApproval )
		{
			jimport('joomla.user.helper');
			$user->set('activation', md5( JUserHelper::genRandomPassword()) );
			$user->set('block', '1');
		}
		
		// Don't re-encrypt the password 
		// JUser bind has encrypted the password
		$user->set('password', $userObj['password2']);
		
		// If there was an error with registration, set the message and display the form
		if ( !$user->save() )
		{
			JError::raiseWarning('', JText::_( $user->getError()));
			$this->register();
			return false;
		}

		if($user->id == 0)
		{
			$uModel		=& $this->getModel('user');
			$newUserId	= $uModel->getUserId($user->username);
			$user		=& JFactory::getUser($newUserId);
		}
		// Update the user's invite if any
		// @todo: move this into plugin. onUserCreated
		$inviteId	= JRequest::getVar('inviteId', 0, 'COOKIE');
		$cuser		= CFactory::getUser($user->id);
		
		if( $profileType != COMMUNITY_DEFAULT_PROFILE )
		{
			
			$multiprofile	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
			$multiprofile->load( $profileType );
			
			// @rule: set users profile type.
			$cuser->_profile_id			= $profileType;
			$cuser->_avatar				= $multiprofile->avatar;
			$cuser->_thumb				= $multiprofile->thumb; 
		}
		
		// @rule: increment user points for registrations.
		$cuser->_points += 2;
		$cuser->_invite = $inviteId;
		$cuser->save();
		
		return $user;
	}
	
	/**
	 * Update the users profile.
	 */
	public function registerUpdateProfile()
	{
		$mainframe	=& JFactory::getApplication();
		$model		=& $this->getModel('register');
		
		// Check for request forgeries
		$mySess 	=& JFactory::getSession();
		$ipAddress	= isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		$token		= $mySess->get('JS_REG_TOKEN','');
						
		$formToken	= JRequest::getVar( 'authkey', '', 'REQUEST');
		$authKey	= $model->getAssignedAuthKey($token, $ipAddress);
		
		if(empty($formToken) || empty($authKey) || ($formToken != $authKey))
		{
			echo '<div class="error-box">' . JText::_('CC INVALID SESSION') . '</div>';
			return;
		}
		
		//intercept validation process in custom profile
		$post		= JRequest::get('post');
		
		/*
		 * Rules:
		 * First we let 3rd party plugin to intercept the validation.
		 * if there is not error return, we then proceed with our validation.
		 */
		$errMsg 	= array();
		$errTrigger = null;
		
		CFactory::load( 'libraries' , 'apps' );
		$appsLib	=& CAppPlugins::getInstance();
		$appsLib->loadApplications();
		
		$params		= array();
		$params[]	= $post;
		$errTrigger = $appsLib->triggerEvent( 'onRegisterProfileValidate' , $params);
		
		if(! is_null($errTrigger))
		{
		    if(! empty($errTrigger[0]) && count($errTrigger[0]) > 0)
		    {
		        //error found.
		        foreach($errTrigger[0] as $err)
		        {
					$mainframe->enqueueMessage( $err , 'error' );
		        }
				$this->registerProfile();
				return;
		    }
		}
		
			
		// get required obj for registration
		$pModel =& $this->getModel('profile');
		$values	= array();
		
		CFactory::load( 'libraries' , 'profile' );
		
		$filter 		= array('published'=>'1', 'registration'=>'1');
		$profileType	= JRequest::getInt( 'profileType' , 0  , 'POST');
		$profiles =& $pModel->getAllFields($filter , $profileType );
			
		foreach( $profiles as $key => $groups )
		{
			foreach( $groups->fields as $data )
			{
				// Get value from posted data and map it to the field.
				// Here we need to prepend the 'field' before the id because in the form, the 'field' is prepended to the id.
				$postData				= JRequest::getVar( 'field' . $data->id , '' , 'POST' );
				$values[ $data->id ]	= CProfileLibrary::formatData( $data->type  , $postData );

				// @rule: Validate custom profile if necessary
				if( !CProfileLibrary::validateField( $data->type , $values[ $data->id ] , $data->required) )
				{
					// If there are errors on the form, display to the user.
					$message	= JText::sprintf('CC FIELD CONTAIN IMPROPER VALUES' ,  $data->name );
					$mainframe->enqueueMessage( $message , 'error' );
					$this->registerProfile();
					return;
				}
			}
		}
		
		$profileType	= JRequest::getVar( 'profileType' , 0 , 'POST' );
		$multiprofile	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
		$multiprofile->load( $profileType );
		
		$tmpUser		= $model->getTempUser($token);
		$user		= $this->_createUser($tmpUser , $multiprofile->approvals , $multiprofile->id );
		$pModel->saveProfile($user->id, $values);
        
        $this->_sendEMail('registration_complete', $user , null , $multiprofile->approvals );
        
        // now we need to set it for later avatar upload page
        // do the clear up job for tmp user.
        $mySess->set('tmpUser',$user);

        $model->removeTempUser($token);
        $model->removeAuthKey($token);        
						
        //redirect to avatar upload page. 
        $mainframe->redirect(CRoute::_('index.php?option=com_community&view=register&task=registerAvatar&profileType=' . $profileType, false));
	}
	
	
	/**
	 * Upload a new user avatar
	 */	 	
	public function registerAvatar()
	{
	    $mainframe =& JFactory::getApplication();		
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.utility');
		
        $mySess =& JFactory::getSession();
        $user   = $mySess->get('tmpUser','');
        
        if(empty($user)){
		    //throw error.
			JError::raiseError( 500, JText::_('CC REGISTRATION MISSING USER OBJ'));
			return;
		}
				
        //CFactory::setActiveProfile($user->id);
		
		$view 	= & $this->getView( 'register');
		
		CFactory::load( 'helpers' , 'image' );
		$profileType	= JRequest::getInt( 'profileType' , 0 );
		
		// If uplaod is detected, we process the uploaded avatar
		if(JRequest::getVar('action', '', 'POST'))
		{
			// Load avatar library
			CFactory::load( 'libraries' , 'avatar' );
			
			$my 		= CFactory::getUser($user->id);	
			$file		= JRequest::getVar( 'Filedata' , '' , 'FILES' , 'array' );		
		
			if($my->id == 0)
			{
				return $this->blockUnregister();
			}

			if( !isset( $file['tmp_name'] ) || empty( $file['tmp_name'] ) )
			{	
				$mainframe->enqueueMessage(JText::_('CC NO POST DATA'), 'error');
			}
			else
			{
				if( !CImageHelper::isValid($file['tmp_name'] ) )
				{
					$mainframe->enqueueMessage(JText::_('CC IMAGE FILE NOT SUPPORTED'), 'error');
				}
				else
				{
					$config			= CFactory::getConfig();
					$useWatermark	= $profileType != COMMUNITY_DEFAULT_PROFILE && $config->get('profile_multiprofile') ? true : false;
					
					// @todo: configurable width?
					$imageMaxWidth	= 160;

					// Get a hash for the file name.
					$fileName		= JUtility::getHash( $file['tmp_name'] . time() );
					$hashFileName	= JString::substr( $fileName , 0 , 24 );

					//@todo: configurable path for avatar storage?
					$config				= CFactory::getConfig();
					$storage			= JPATH_ROOT . DS . $config->getString('imagefolder') . DS . 'avatar';
					$storageImage		= $storage . DS . $hashFileName . CImageHelper::getExtension( $file['type'] );
					$storageThumbnail	= $storage . DS . 'thumb_' . $hashFileName . CImageHelper::getExtension( $file['type'] );
					$image				= $config->getString('imagefolder') . '/avatar/' . $hashFileName . CImageHelper::getExtension( $file['type'] );
					$thumbnail			= $config->getString('imagefolder') . '/avatar/' . 'thumb_' . $hashFileName . CImageHelper::getExtension( $file['type'] );
					
					$userModel			= CFactory::getModel( 'user' );
					
					// Generate full image
					if(!CImageHelper::resizeProportional( $file['tmp_name'] , $storageImage , $file['type'] , $imageMaxWidth ) )
					{
						$mainframe->enqueueMessage(JText::sprintf('CC ERROR MOVING UPLOADED FILE' , $storageImage), 'error');
					}
					
					// Generate thumbnail
					if(!CImageHelper::createThumb( $file['tmp_name'] , $storageThumbnail , $file['type'] ))
					{
						$mainframe->enqueueMessage(JText::sprintf('CC ERROR MOVING UPLOADED FILE' , $storageThumbnail), 'error');
					}

					if( $useWatermark )
					{
						// @rule: Before adding the watermark, we should copy the user's original image so that when the admin tries to reset the avatar,
						// it will be able to grab the original picture.
						JFile::copy( $storageImage , JPATH_ROOT . DS . 'images' . DS . 'watermarks' . DS . 'original' . DS . md5( $my->id . '_avatar' ) . CImageHelper::getExtension( $file['type'] ) );
						JFile::copy( $storageThumbnail , JPATH_ROOT . DS . 'images' . DS . 'watermarks' . DS . 'original' . DS . md5( $my->id . '_thumb' ) . CImageHelper::getExtension( $file['type'] ) );

						$multiprofile	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
						$multiprofile->load( $profileType );
						
						$watermarkPath	= JPATH_ROOT . DS . JString::str_ireplace('/' , DS , $multiprofile->watermark);
						
						list( $watermarkWidth , $watermarkHeight )	= getimagesize( $watermarkPath );
						list( $avatarWidth , $avatarHeight ) 		= getimagesize( $storageImage );
						list( $thumbWidth , $thumbHeight ) 		= getimagesize( $storageThumbnail );

						$watermarkImage		= $storageImage;
						$watermarkThumbnail	= $storageThumbnail;						
						
						// Avatar Properties
						$avatarPosition	= CImageHelper::getPositions( $multiprofile->watermark_location , $avatarWidth , $avatarHeight , $watermarkWidth , $watermarkHeight );

						// The original image file will be removed from the system once it generates a new watermark image.
						CImageHelper::addWatermark( $storageImage , $watermarkImage , 'image/jpg' , $watermarkPath , $avatarPosition->x , $avatarPosition->y );

						//Thumbnail Properties
						$thumbPosition	= CImageHelper::getPositions( $multiprofile->watermark_location , $thumbWidth , $thumbHeight , $watermarkWidth , $watermarkHeight );
						
						// The original thumbnail file will be removed from the system once it generates a new watermark image.
						CImageHelper::addWatermark( $storageThumbnail , $watermarkThumbnail , 'image/jpg' , $watermarkPath , $thumbPosition->x , $thumbPosition->y );

						$my->set( '_watermark_hash' , $multiprofile->watermark_hash );
						$my->save();
					}
					// Since this is a new registration, we definitely do not want to remove the old image.
					$removeOldImage = false;
					
					$userModel->setImage( $my->id , $image , 'avatar' , $removeOldImage );
					$userModel->setImage( $my->id , $thumbnail , 'thumb' , $removeOldImage );
					
					// Update the user object so that the profile picture gets updated.
					$my->set( '_avatar' , $image );
					$my->set( '_thumb'	, $thumbnail );
				}
			}
			
			//redirect to successful page
			$mainframe->redirect(CRoute::_('index.php?option=com_community&view=register&task=registerSucess&profileType=' . $profileType , false));
			
		}
		
		echo $view->get( __FUNCTION__ );
	}
	
	public function registerSucess()
	{
		// @rule: Clear any existing temporary session.
		$session	=& JFactory::getSession();
		$session->clear( 'tmpuser' );
		$session->clear( 'JS_REG_TOKEN' );

        $view 	= & $this->getView( 'register');
        echo $view->get( __FUNCTION__ );
	}
	
	/*
	 * $password is optional
	 */	 
	private function _sendEMail($type, $user, $password=null , $requireApproval = false )
	{
		$mainframe		=& JFactory::getApplication();
		$config			= CFactory::getConfig();
		$modelRegister 	=& $this->getModel('register');
		
		$usersConfig 			= &JComponentHelper::getParams( 'com_users' );
		$useractivation 		= $usersConfig->get( 'useractivation' );
		$newAccountActivation	= $usersConfig->get( 'useractivation' );
		
		$sitename 		= $mainframe->getCfg( 'sitename' );		
		$mailfrom 		= $mainframe->getCfg( 'mailfrom' );
		$fromname 		= $mainframe->getCfg( 'fromname' );
		$siteURL		= JURI::base();		

		$name 			= $user->get('name');
		$email 			= $user->get('email');
		$username 		= $user->get('username');
		
		if (is_null($password))
		{
			$password	= $user->get('password');
		}
		//Disallow control chars in the email
		$password = preg_replace('/[\x00-\x1F\x7F]/', '', $password);
		
		// Load Super Administrator email list
		$rows	= $modelRegister->getSuperAdministratorEmail();
		
		//getting superadmin email address.
		if ( ! $mailfrom  || ! $fromname )
		{
			foreach ( $rows as $row )
			{
				if($row->sendEmail)
				{
					$fromname = $row->name;
					$mailfrom = $row->email;
					break;
				}
			}
			
			//if still empty, then we just pick one of the admin email
			if ( ! $mailfrom  || ! $fromname )
			{
				$fromname = $rows[0]->name;
				$mailfrom = $rows[0]->email;
			}
		}

		$subject 	= JText::sprintf( 'CC ACCOUNT DETAILS FOR' , $name, $sitename);
		$subject 	= html_entity_decode($subject, ENT_QUOTES);
		
		$needActivation	= $usersConfig->get( 'useractivation' );
		
		if ($needActivation)
		{
			$activationURL	= $siteURL."index.php?option=com_user&task=activate&activation=".$user->get('activation');
		}

		switch ($type)
		{
			case 'registration' :
				
				// This section will only be called when there are no custom fields created and we just proceed like how Joomla
				// registers a user.
				if( $needActivation )
				{
					$message = sprintf( JText::_('CC EMAIL REGISTRATION REQUIRES ACTIVATION'), $name, $sitename, $activationURL, $siteURL , $username, $password );
				}
				else
				{
					$message = sprintf( JText::_('CC EMAIL REGISTRATION'), $name, $sitename, $username, $password );
				}

				break;
			case 'registration_uncomplete' :

				if ($needActivation)
				{
					$message = sprintf( JText::_('CC EMAIL REGISTRATION ACCOUNT DETAILS REQUIRES ACTIVATION'), $name, $sitename, $username, $password );
				}
				else
				{
					$message = sprintf( JText::_('CC EMAIL REGISTRATION ACCOUNT DETAILS'), $name, $sitename, $username, $password );
				}

				break;
			case 'registration_complete' :

				if( $requireApproval )
				{
					$message = sprintf( JText::_('CC EMAIL REGISTRATION COMPLETED REQUIRES APPROVAL'), $name, $sitename, $siteURL );
				}
				else
				{
					if ($needActivation)
					{
						$message = JText::sprintf('CC EMAIL REGISTRATION COMPLETED REQUIRES ACTIVATION', $name, $sitename, $activationURL, $siteURL );
					}
					else
					{
						$message = sprintf( JText::_('CC EMAIL REGISTRATION COMPLETED'), $name, $sitename, $siteURL );
					}
				}
				
				break;
			case 'resend_activation' :
				if ($config->get('activationresetpassword'))
				{
					$message = sprintf ( JText::_( 'CC ACTIVATION MSG WITH PWD' ), $name, $sitename, $siteURL."index.php?option=com_user&task=activate&activation=".$user->get('activation'), $siteURL, $username, $password);
				}
				else
				{
					$message = sprintf ( JText::_( 'CC ACTIVATION MSG' ), $name, $sitename, $siteURL."index.php?option=com_user&task=activate&activation=".$user->get('activation'), $siteURL);
				}
				break;
		}
		
		$message = html_entity_decode($message, ENT_QUOTES);
		
		// Send email to user
		JUtility::sendMail($mailfrom, $fromname, $email, $subject, $message);

		if( $type == 'registration_complete' )
		{
			//Send notification email to administrators
			foreach ( $rows as $row )
			{
				if ($row->sendEmail)
				{
					if( $requireApproval )
					{
						$message2	= JText::sprintf( JText::_( 'CC USER REGISTERED NEEDS APPROVAL' ), $row->name, $sitename, $name, $email, $username );
					}
					else
					{
						$message2	= JText::sprintf( JText::_( 'CC SEND_MSG_ADMIN' ), $row->name, $sitename, $name, $email, $username);
					}
					$message2 = html_entity_decode($message2, ENT_QUOTES);
					JUtility::sendMail($mailfrom, $fromname, $row->email, $subject, $message2);
				}
			}
		}
	}
	
	/**
	 * Validate registration form
	 */	 	
	private function _validateRegister($post = array())
	{
	    $mainframe =& JFactory::getApplication();
	    $config	= CFactory::getConfig();
		//$pModel =& $this->getModel('profile');
        $errMsg = array();
        $data   = array();
		
		if(! empty($post)){
		    //manual array_walk to trim
		    //for($post as $item){
		    foreach($post as $key => $value){
		        if(is_array($value)){
		           $data[$key] = $value; // dun do anything here.
		        } else {
		           $data[$key] = JString::trim($value);
		        }
		    }//end of		
		}
		
		$isUseFirstLastName	= $data['isUseFirstLastName'];
		
        //get all published custom field for profile						
		//$filter = array('published'=>'1');		
		//$groups =& $pModel->getAllFields($filter);
		
		//check the user infor
		CFactory::load( 'helpers' , 'validate' );
		if ($isUseFirstLastName)
		{
			if(empty($data['jsfirstname'])) {$errMsg[] = JText::_('CC FIELD ENTRY').' \''.JText::_( 'CC FIRST NAME' ).'\' '.JText::_('CC IS EMPTY').'.';}
			if(empty($data['jslastname'])) {$errMsg[] = JText::_('CC FIELD ENTRY').' \''.JText::_( 'CC LAST NAME' ).'\' '.JText::_('CC IS EMPTY').'.';}
		} else {
			if(empty($data['jsname'])) {$errMsg[] = JText::_('CC FIELD ENTRY').' \''.JText::_( 'Name' ).'\' '.JText::_('CC IS EMPTY').'.';}
		}
		if(empty($data['jsusername'])) {$errMsg[] = JText::_('CC FIELD ENTRY').' \''.JText::_( 'Username' ).'\' '.JText::_('CC IS EMPTY').'.';}
		if(empty($data['jsemail'])) {$errMsg[] = JText::_('CC FIELD ENTRY').' \''.JText::_( 'Email' ).'\' '.JText::_('CC IS EMPTY').'.';}
		if(empty($data['jspassword'])) {$errMsg[] = JText::_('CC FIELD ENTRY').' \''.JText::_( 'Password' ).'\' '.JText::_('CC IS EMPTY').'.';}
		if(empty($data['jspassword2'])) {$errMsg[] = JText::_('CC FIELD ENTRY').' \''.JText::_( 'Verify Password' ).'\' '.JText::_('CC IS EMPTY').'.';}
		
		if(! empty($data['jsusername']))
		{
			if(! CValidateHelper::username($data['jsusername']))
			{
				$errMsg[] = JText::_('CC IMPROPER USERNAME');
			}
		}
						
		if($config->get('enableterms')){		   
		   if(empty($data['tnc'])){ $errMsg[] = JText::_('CC_REG_ACCEPT_TNC');}		
		}
		
		return $errMsg;
	}
	
					
	
	/**
	 * Validate registration form
	 */	 	
	private function _validateProfile($post = array())
	{
	    $mainframe =& JFactory::getApplication();
		$pModel =& $this->getModel('profile');
        $errMsg = array();
        $data   = array();
						
		if(! empty($post)){
		    //manual array_walk to trim
		    //for($post as $item){
		    foreach($post as $key => $value){
		        if(is_array($value)){
		           $data[$key] = $value; // dun do anything here.
		        } else {
		           $data[$key] = JString::trim($value);
		        }
		    }//end of		
		}

        //get all published custom field for profile						
		$filter = array('published'=>'1', 'registration'=>'1');		
		$groups =& $pModel->getAllFields($filter);
		
		// Bind result from previous post into the field object		
		if(! empty($data)){			
			foreach($groups as $group){
			    $fields = $group->fields;
			    for($i = 0; $i <count($fields); $i++){
	 				$fieldid    = $fields[$i]->id;
	 				$fieldname  = $fields[$i]->name;
	 				$isRequired = $fields[$i]->required;
	 				$fieldType  = $fields[$i]->type;
	 				
	 				//$errMsg[] = 'Field entry \''.$fieldname.'\' : '.$isRequired.' : '.$fieldType;
	 					 		
	 				if($isRequired == 1){
                       if($fieldType == 'date'){
	 				      if(JString::trim($data['field'.$fieldid][0]) == '' || JString::trim($data['field'.$fieldid][2]) == ''){
	 				         $errMsg[] = JText::_('CC FIELD ENTRY').' \''.$fieldname.'\' '.JText::_('CC IS EMPTY').'.';
	 				      }							 				   
	 				   } else { 
					       if(empty($data['field'.$fieldid])){
	 				           $errMsg[] = JText::_('CC FIELD ENTRY').' \''.$fieldname.'\' '.JText::_('CC IS EMPTY').'.';
	 				       }
	 				   }//end if else					 	 				   
	 				}//ebd if
	 				
                }//end for i
			}//end foreach			
		}//end if
		
		//$errMsg[] = 'Testing error.';
		
		return $errMsg;
	}
	
	public function lostPassword(){
	}
	
	public function forgotUsername(){
	}
	
	public function display()
	{
		$this->register();
	}
	
	/*
	 * Leave this function here until it get stable. If someting is wrong, revert
	 * the function to use back this one.	 
	 */
	public function ajaxCheckUserName($param='')
	{
	    $objResponse   = new JAXResponse();
	    	    
	    $username	= $param;
	    $ipaddress	= isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
	    $model		=& $this->getModel('register');
	    
	    $isInvalid	= false;
	    $msg		= '';
	    
	    CFactory::load( 'helpers' , 'validate' );
	    if(! empty($username))
	    {
		    if(! CValidateHelper::username($username))
		    {
		    	$isInvalid	= true;
		    	$msg		= JText::_('CC IMPROPER USERNAME');				
		    }
		}	    
	    
	    if(! empty($username) && !$isInvalid){
	        $isInvalid = $model->isUserNameExists(array('username'=>$username, 'ip'=>$ipaddress));
			$msg = JText::sprintf('CC USERNAME EXIST', $username);    
	    }
	    	    	    	    
	    if($isInvalid){	    
			$objResponse->addScriptCall('joms.jQuery("#jsusername").addClass("invalid");');
			$objResponse->addScriptCall('joms.jQuery("#errjsusernamemsg").show();');
			$objResponse->addScriptCall('joms.jQuery("#errjsusernamemsg").html("<br/>'.$msg.'");');
			$objResponse->addScriptCall('joms.jQuery("#usernamepass").val("N");');
			$objResponse->addScriptCall('false;');
        } else {
			$objResponse->addScriptCall('joms.jQuery("#jsusername").removeClass("invalid");');
			$objResponse->addScriptCall('joms.jQuery("#errjsusernamemsg").html("&nbsp");');
			$objResponse->addScriptCall('joms.jQuery("#errjsusernamemsg").hide();');			
			$objResponse->addScriptCall('joms.jQuery("#usernamepass").val("'.$username.'");');
			$objResponse->addScriptCall('true;');
        }

        return $objResponse->sendResponse();
	}	
	
	public function ajaxCheckEmail($param='')
	{
	    $objResponse   = new JAXResponse();
	    
	    $email 		= $param;
	    $ipaddress	= isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
	    $model 		=& $this->getModel('register');
	    
	    $isExists = false;
	    if(! empty($email)){
	        $isExists = $model->isEmailExists(array('email'=>$email, 'ip'=>$ipaddress));    
	    }
	    
	    $msg = JText::sprintf('CC EMAIL EXIST', $email);
	    
	    if($isExists){	    
			$objResponse->addScriptCall('joms.jQuery("#jsemail").addClass("invalid");');
			$objResponse->addScriptCall('joms.jQuery("#errjsemailmsg").show();');
			$objResponse->addScriptCall('joms.jQuery("#errjsemailmsg").html("<br/>'.$msg.'");');
			$objResponse->addScriptCall('joms.jQuery("#emailpass").val("N");');
			$objResponse->addScriptCall('false;');
        } else {
			$objResponse->addScriptCall('joms.jQuery("#jsemail").removeClass("invalid");');
			$objResponse->addScriptCall('joms.jQuery("#errjsemailmsg").html("&nbsp");');
			$objResponse->addScriptCall('joms.jQuery("#errjsemailmsg").hide();');			
			$objResponse->addScriptCall('joms.jQuery("#emailpass").val("'.$email.'");');
			$objResponse->addScriptCall('true;');
        }

        return $objResponse->sendResponse();
	}
	
	public function ajaxSetMessage($fieldName, $txtLabel = '', $strMessage, $strParam='')
	{
       $objResponse   = new JAXResponse();
       
       $langMsg = '';
       if(! empty($strMessage)){       
           $langMsg = (empty($strParam)) ? JText::_($strMessage) : JText::sprintf($strMessage, $strParam);       
       }
	   
	   $myLabel = ($txtLabel == 'Field') ? JText::_('CC FIELD') : $txtLabel;
	          
       $langMsg = (empty($txtLabel)) ? $langMsg : $myLabel.' '.$langMsg;
       
       $objResponse->addScriptCall('joms.jQuery("#err'.$fieldName.'msg").html("<br />'.$langMsg.'");');
       $objResponse->addScriptCall('joms.jQuery("#err'.$fieldName.'msg").show();');

       return $objResponse->sendResponse();	
	}
	
	public function ajaxShowTnc()
	{
		$objResponse   = new JAXResponse();
		
		$config		= CFactory::getConfig();
		
		$html		= $config->get( 'registrationTerms' );
		
		$action = '<button class="button" onclick="javascript:cWindowHide();" name="close">Close</button>';
		$objResponse->addAssign('cWindowContent', 'innerHTML', $html);
		$objResponse->addScriptCall("joms.jQuery('#cWindowContent').css('overflow','auto');");
		//$objResponse->addScriptCall('cWindowActions', $action);
		return $objResponse->sendResponse();
	}
	
	public function ajaxGenerateAuthKey()
	{
	    $objResponse   = new JAXResponse();
	    
	    $authKey	= "";
	    $ipaddress	= isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		
		$mySess 	=& JFactory::getSession();	    
	    
	    $newToken	= $mySess->getToken(true);
		$mySess->set('JS_REG_TOKEN', $newToken);
		
	    if(! $mySess->has('JS_REG_TOKEN'))
	    {
	    	$message = JText::_('CC REG AUTH ERROR');
	    	$objResponse->addScriptCall("joms.registrations.showWarning('".$message."');");
	    	
	    	// Renable the submit button
	    	$objResponse->addScriptCall("joms.jQuery('#btnSubmit').show();");
	    	$objResponse->addScriptCall("joms.jQuery('#cwin-wait').hide();");
	    	$objResponse->addScriptCall("try{console.log('".$mySess->getId()."');}catch(e){}");
			return $objResponse->sendResponse();	    
	    }
	    
	    
	    			    	    
	    //$token		= JUtility::getToken();
		$token	= $mySess->get('JS_REG_TOKEN','');
			    
	    //generate a dynamic authentication key
	    $authKey	= md5(uniqid(rand(), true));
	    
	    $model 		=& $this->getModel('register');	    			
	    
	    if($model->addAuthKey($authKey))	    
	    {
		    $objResponse->addScriptCall("joms.registrations.assignAuthKey('jomsForm','authkey','".$authKey."');");
		    $objResponse->addScriptCall("joms.jQuery('#authenticate').val('1');");
		    $objResponse->addScriptCall("joms.jQuery('#btnSubmit').click();");
	    }
	    else
	    {
	    	$message = JText::_('CC REG AUTH ERROR');
	    	$objResponse->addScriptCall("joms.registrations.showWarning('".$message."');");
	    }			    	    	    
	    	    	    	    

        return $objResponse->sendResponse();
	}	
	
	public function ajaxAssignAuthKey()
	{
	    $objResponse   = new JAXResponse();
	    
	    $authKey	= "";
	    $ipaddress	= isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		
		$mySess 	=& JFactory::getSession();
		$token		= $mySess->get('JS_REG_TOKEN','');
	    
	    $model 		=& $this->getModel('register');
	    $authKey	= $model->getAuthKey ($token, $ipaddress);
	    	    	    	    			    				    	    	    
	    $objResponse->addScriptCall("joms.registrations.assignAuthKey('jomsForm','authkey','".$authKey."');");
	    $objResponse->addScriptCall("joms.jQuery('#authenticate').val('1');");
	    $objResponse->addScriptCall("joms.jQuery('#btnSubmit').click();");			    	    	    	    	    	    	    

        return $objResponse->sendResponse();	
	}
	
	public function activation()
	{
		$view =& $this->getView('register');
		echo $view->get('activation');	
	}
	
	public function activationResend()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		
		jimport('joomla.user.helper');
	
		$mainframe		=& JFactory::getApplication();
		$config			= CFactory::getConfig();
		$usersConfig	=& JComponentHelper::getParams( 'com_users' );
		$regModel 		=& $this->getModel('register');
		$jsEmail		= JRequest::getVar( 'jsemail', '', 'REQUEST');		
		
		$isExists = false;
	    if(! empty($jsEmail)){
	        $isExists = $regModel->isEmailExists(array('email'=>$jsEmail));    
	    }
	    
	    if(! $isExists)
	    {				    
		   $mainframe->enqueueMessage(JText::sprintf( 'CC ACTIVATION EMAIL INVALID' , $jsEmail ), 'error');
		   $this->activation();
		   return false;			    
	    }
			    
	    //if user is already 'unblock', then no need to process email activation resend. 
		$regUser		= $regModel->getUserByEmail($jsEmail);		
		$user			=& JFactory::getUser($regUser->id);
		if($user->block != '1')
		{
		   $mainframe->enqueueMessage(JText::sprintf('CC ACTIVATION ALREADY ACTIVATED', $jsEmail));
		   $this->activation();
		   return false;			
		}	    
				
		//if user activation disabled, show message to user.
 		$useractivation = $usersConfig->get( 'useractivation' ); 		 		
		if ($useractivation == '0')
		{
		   $mainframe->enqueueMessage(JText::_('CC ACTIVATION DISABLED'));
		   $this->activation();
		   return false;
		}
		
		$password		= null;
		if($config->get('activationresetpassword'))
		{
			$password	= JUserHelper::genRandomPassword();
			$salt		= JUserHelper::genRandomPassword(32);
			$crypt		= JUserHelper::getCryptedPassword($password, $salt);
			$password	= $crypt.':'.$salt;
			
			$user->set('password', $password);
		}
		
		$user->set('activation', md5( JUserHelper::genRandomPassword()) );		
		
		if(! $user->save())
		{
		   $mainframe->enqueueMessage(JText::sprintf('CC ACTIVATION FAILED', $jsEmail , $user->getError()));
		   $this->activation();
		   return false;
		}
		
		$this->_sendEMail('resend_activation', $user, $password);
				
		$mainframe->enqueueMessage(JText::sprintf('CC ACTIVATION SUCCESS', $jsEmail));
		$this->activation();
	}
}
