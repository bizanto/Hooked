<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');
jimport('joomla.user.helper');
jimport('joomla.mail.helper');

class ResetPasswordController extends JController
{
	
	public function display()
	{
		parent::display() ;
	}
	
	public function submitemail() {
		
			
		
		$db = JFactory::getDBO() ;
		
		
		$requestedEmail =   JRequest::getVar('emailaddr' , '') ;
		
		
		$db->setQuery('select id , username, name, email from #__users where block = 0  and email = '. $db->Quote( $requestedEmail ) ) ;
		if( $user =  $db->loadObject() )  {
			
			// Make sure the user isn't a Super Admin.
			$juser = JFactory::getUser($user->id) ;
			
			//joomla 1.6 and 1.5 check	
			$option = JRequest::getWord('component'); 
			if ( $juser->authorize('core.admin' , $option ) || $juser->usertype == 'Super Administrator') {
				$this->setRedirect( 'index.php?option=com_resetpassword'  , JText::_('INVALID_EMAIL')) ;		
			}
			else { 
				
				$params = &JComponentHelper::getParams( 'com_resetpassword' );
				$random = $params->get( 'randompassword' , 'no' ) ;
				
		 		if( $random == 'yes'  ) {
		 			$notification = JText::_('RANDOM_EMAIL_INSTRUCTION' ) ;
		 			$this->sendRandomPassword( $user ) ;
		 		}
				else { 	
					$notification = JText::_('EMAIL_INSTRUCTION' )	;	
					$result = $this->sendPasswordResetEmail( $user ) ;
				}
				
				$url = JRequest::getVar('returnurl' , false ) ;
				if($url) {
					$url = urldecode($url) ;
				}
				else {
					$url =  'index.php?option=com_resetpassword&layout=success' ;
				}
				
				$this->setRedirect( $url   ,  $notification ) ;
			}
		}
		else {
			$this->setRedirect( 'index.php?option=com_resetpassword' , JText::_('INVALID_EMAIL') );
		}
	}
	
	
	public function checknewpassword() {
		
		$params = &JComponentHelper::getParams( 'com_resetpassword' );
 		$authtype = $params->get( 'authentication' , 'username' );
		
		$db = JFactory::getDBO() ;
		
		$username =  JRequest::getVar('username' , '')  ;
		$password1 = JRequest::getVar('password1' , '' )  ;
		$password2 = JRequest::getVar('password2' , '' )  ;
		$token =     JRequest::getVar('token' , '' )  ;
		
		
		$onepointsix = '' ;
      
	    if(file_exists(JPATH_SITE . '/components/com_users') ) {
	      $onepointsix = 's' ;
	    }
		
		$successlink = JRoute::_( 'index.php?option=com_user'.$onepointsix.'&view=login' ) ;
		
		
		$message = JText::_('PASSWORD_UPDATED') ;
		$errorlink = 'index.php?option=com_resetpassword&task=confirmtoken&token='. $token ;
		$link = $errorlink ;
		
		if(strlen($password1) > 3  ) { 
			if( $password1 == $password2 ) {
				
				
				$curdate = date("Y-m-d H:i:s" ) ;
				
				$db->setQuery('select user_id from #__resetpasswordtoken where token = "' . $token .'" and "'.$curdate.'" < expire  limit 1 ' ) ;
				$user_id = $db->loadResult() ;
				if($user_id) {
					
					$user = JFactory::getUser($user_id) ;
					if( 
					
						( ( $authtype == 'username' && strtolower( $user->username )  ==  strtolower( $username ) ) 
						
						|| 
						
						( $authtype == 'email' && strtolower( $user->email )  ==  strtolower( $username )  ) )
						
						&& 
						
						$user->block == 0 
						
						) 
						
						{
						
						$cryptpassword = JUserHelper::getCryptedPassword($password1) ;
						
						$db->setQuery('update #__users set password = "'.$cryptpassword.'" where id = ' . $user->id . ' LIMIT 1' ) ;
						$db->query() ;
						
						$db->setQuery('insert into #__resetpasswordlog (user_id , date) values ( '.$user->id.' , "'.$curdate.'" ) ') ;
						$db->query() ;
						
						$db->setQuery('delete from #__resetpasswordtoken where token = "' . $token . '" LIMIT 1' ) ;
						$db->query() ;
						
						$this->sendConfirmationEmail($user) ;
						
						$link = $successlink ;
						
					}
					else {
						$message = JText::_('WRONG_USERNAME')  ;
					}
				}
				else {
					$message = $user->block ? JText::_('ACCOUNT_BLOCKED') : JText::_('USER_NOT_FOUND')  ;
				}
			}
			else {
				$message = JText::_('PASSWORDS_DONT_MATCH') ;
			}
		}
		else {
			$message = JText::_('WRONG_PASSWORD_LENGTH')  ;
		}	
		
		$this->setRedirect($link , $message   );
	}
	
	
	public function confirmtoken() {
		$token = JRequest::getVar('token' , '' )   ;
		$db = JFactory::getDBO() ;
		
		$curdate = date( "Y-m-d H:i:s" , mktime() ) ;		
		
		$db->setQuery('select user_id from #__resetpasswordtoken where token = ' .  $db->Quote(  $token ) .' and "'.$curdate.'" < expire  limit 1 ' ) ;
		$user_id = $db->loadResult() ;
		
		if(!$user_id) {
			$this->setRedirect('index.php?option=com_resetpassword' , JText::_('INVALID_LINK') ); 
		}
		
		else { 
			JRequest::setVar('view' , 'newpassword') ;
			$this->display() ;
		}	
	}
	
	
	private function sendRandomPassword($user) {
		$db = JFactory::getDBO() ;
		
		
		$newpassword = $this->createRandomPassword() ;
		$cryptpassword = JUserHelper::getCryptedPassword($newpassword) ;
						
		$db->setQuery('update #__users set password = "'.$cryptpassword.'" where id = ' . $user->id . ' LIMIT 1' ) ;
		$db->query() ;
		
		$curdate = date("Y-m-d H:i:s" ) ;
		$db->setQuery('insert into #__resetpasswordlog (user_id , date) values ( '.$user->id.' , "'.$curdate.'" ) ') ;
		$db->query() ;
		
		
		$username = $user->name ;
		$sitename = $this->getSiteName() ;
		$body = JText::sprintf('RANDOM_PASSWORD_EMAIL' , $username, $sitename, $newpassword)  ; ;
		$this->sendEmail($user->email , JText::_('EMAIL_SUBJECT')  , $body ) ;
		
	}
	
	
	private function createRandomPassword() { 
	
	    $chars = "abcdefghijkmnopqrstuvwxyz023456789"; 
	    srand((double)microtime()*1000000); 
	    $i = 0; 
	    $pass = '' ; 
	
	    while ($i <= 7) { 
	        $num = rand() % 33; 
	        $tmp = substr($chars, $num, 1); 
	        $pass = $pass . $tmp; 
	        $i++; 
	    } 
	
	    return $pass; 
	
	}	
	
	private function sendPasswordResetEmail($user ) {
		
	    $link = $this->buildLink($user) ;
		$username = $user->name ;
		$sitename = $this->getSiteName() ;
		$body = JText::sprintf('EMAIL_BODY' , $username, $sitename, $link)  ; 
				
		return $this->sendEmail($user->email , JText::_('EMAIL_SUBJECT')  , $body ) ;
	}
	
	private function sendConfirmationEmail($user) {
		$username = $user->name ;
		$sitename = $this->getSiteName() ;
		
		$body = JText::sprintf('EMAIL_SUCCESS_BODY' , $username, $sitename ) ;	
		return $this->sendEmail($user->email , JText::_('EMAIL_SUCCESS_SUBJECT' ) , $body ) ;
	}	
	
	private function sendEmail($emailaddress , $subject, $body ) {
		$mail  =& JFactory::getMailer();
		$app = JFactory::getApplication() ;
		$registry  =  JFactory::getConfig() ;
		
		$mail->setSender( array( $registry->getValue('mailfrom') , $registry->getValue('fromname')  ) );
		$mail->addRecipient(   $emailaddress  );
			
		$mail->setSubject( $subject );
	
		$mail->setBody( $body );
		
		return  $mail->Send() ;		
	}
	
	private function getSiteName() {
		$registry  =  JFactory::getConfig() ;
		return $registry->getValue('sitename') ;
	}
	
	private function buildLink( $user ) {
		$token = JUtility::getHash( JUserHelper::genRandomPassword());
		$this->saveToken($user->id, $token) ;
		return JRoute::_( JURI::root() . 'index.php?option=com_resetpassword&task=confirmtoken&token=' . $token ) ;
	}
	
	private function saveToken($userID, $token ) {
		$expiredate = date("Y-m-d H:i:s" , mktime() +  60 * 60 ) ;
		$db =  JFactory::getDBO() ;

		$db->setQuery('delete from #__resetpasswordtoken where user_id = ' . $userID ) ;
		$db->query() ;
		
		$db->setQuery('insert into #__resetpasswordtoken (user_id , token, expire) values ('.$userID.' , "'.$token.'" , "'.$expiredate.'")  ') ;
		$db->query() ;
	}
}

?>