<?php
/**
 * @version $Id: header.php 789 2009-03-23 15:56:03Z elkuku $
 * @package    Jomsocial
 * @subpackage FirstFriend
 * @author     Webmaster {@link http://www.Sociables.com}
 * @author     Created on 25-Mar-2009
 * @copyright	Copyright (C) 2005 - 2010 Socialables.com All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );

class plgUserFirstFriend extends JPlugin
{
	//var $_my		= null;
	var $_user		= null;
	function plgUserFirstFriend (& $subject, $config)
	{	
		//this should fix the error people are getting using forum bridges and stuff which dont load the jomsocial core
		include_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php' );				
		$this->_user	=& JFactory::getUser();
		parent::__construct($subject, $config);
		
		// Check whether the plugin table exists or not and create it if necessary						
	}
	
	
	function onAfterStoreUser($user, $isnew, $success, $msg)
		{
			if ($isnew)
				{
						require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'notification.php');

					//JPlugin::loadLanguage('plg_firstfriend', JPATH_ADMINISTRATOR);
				
					$sendreq = $this->params->get( 'sendreq' , 1 );		
					$sendletter = $this->params->get( 'sendletter' , 1 );
					$sendgift = $this->params->get( 'sendgift' , 1 );
					$sendpm = $this->params->get( 'sendpm' , 1 );
					//@todo add multiple first friends & conditions such as male/female
					$firstfriend = $this->params->get( 'firstfriend' , 62 );
					$friends = explode(',', $firstfriend);
					$method = $this->params->get( 'method' , 0 );
					//$massfriends = $this->params->get( 'massfriends' , 0 );					
					$user['id'] != $firstfriend ? $notself = 1 : $notself = 0;
								
					if (($sendreq) && ($notself))
					{
						$method = $this->params->get( 'method' , 0 );			
						
								if ($method == 3)
									{	
										$userids = $this->_getAllUsers();
										//parse through all id's
										foreach ($userids AS $row)
											{
												//for each user send a friend request to the user from the new member
												$this->_addFriendR(0, $row->userid, $user['id']);
				
											}		
									}
								else
									{
										if (@$friends[1])
											{	
												foreach ($friends as $friend)
												{
													$this->_addFriendR($method, $user['id'], $friend);		
												}
											}
										else
											{
												$this->_addFriendR($method, $user['id'], $firstfriend);			
											}
									}
									
						
						
					}
					
					if ($sendletter !=0 && $notself == 1)
						{
			
									$notify = new CNotificationLibrary();
						
									$notify->add( 'system.member.welcome' , $this->params->get( 'senderid' , 62 ) , $user['id'] , $this->params->get( 'subject' , '' ) ,
									 	JText::sprintf($this->params->get( 'body' , '' ) , JURI::base()), '' , array() );
						}
						
					if ($sendpm)
						{ 		
									$vars['id'] = $this->params->get( 'pmsenderid' , 62 ) ;
									$vars ['to'] = $user['id'];
									$vars['subject'] = $this->params->get( 'pmsubject' , '' );
									$vars['body'] = JText::sprintf($this->params->get( 'pmbody' , '' ) , JURI::base());
									$this->send($vars);
						}
						
					if ($sendgift)
					{	
					 jimport('joomla.application.component.helper');

						 if( JComponentHelper::isEnabled('com_giftexchange', true )) 
						 {						   						
						 	include_once( JPATH_ROOT .DS. 'components'.DS.'com_giftexchange'.DS.'helpers'.DS.'loader.php'); 
							$message = $this->params->get( 'giftmessage' , '' );
							$data['toid'] = $user['id'];
							$data['fromid'] = $this->params->get( 'giftfriend' , '' );;
							$data['giftids'] = $this->params->get( 'giftids' , '' );
							
							$userTo 		=& CFactory::getUser($user['id']);
							$recipientName	= $userTo->getDisplayName();						
							$search 	= array('{actor}', '{target}');
							$replace 	= array($userTo->getDisplayName(), $recipientName );	
														
							$data['message'] 	= JString::str_ireplace($search, $replace, $message);																		
							$data['cost'] = $this->params->get( 'giftvalue' , 0 );	
							$helper = new GXhelperSend();					
							$helper->sendGift($data);											
							//$sendgift = & GXhelperSend::sendGift($data);	
						 }					
					}
						
					
				}
		}
		
	function onLoginUser($user, $options)
	{
		$method = $this->params->get( 'method' , 0 );
		if ($method == 3 || $method == 0)
		{
			$model 	=& CFactory::getModel('friends');
			$count = $model->getFriendsCount($this->_user->id);
			$this->addFriendCount($this->_user->id, $count);
		}
	}		
	
	function _getAllUsers()
	{
		$db = &JFactory::getDBO();

			$query="SELECT ". $db->nameQuote( 'userid' ) . " FROM ". $db->nameQuote( '#__community_users' );
				$db->setQuery($query);
				$result = $db->loadObjectList();

		return $result;		
	}	
	
	function _addFriendR($method, $to, $from)
	{
		$model 	=& CFactory::getModel('friends');

		//1st lets make sure sender and receiver are not the same
		if (($to != $from)&&($to != $this->_user->id)&&($from != $this->_user->id))
			{		
				switch ($method)
				{
					case 0://Two Way auto approved
								//this is a check to see if the friend is already on their list if so dont add them again
								//needs to be approved
								if (!$model->getFriendConnection($to, $from))$model->addFriendRequest($to, $from);
									$count = $model->getFriendsCount($from);
									$this->addFriendCount($from, $count);
									$count = $model->getFriendsCount($to);
									$this->addFriendCount($to, $count);
						break;
					case 1://one way to - Must be approved by the recipient
								//send a request to a user from the current user								
								if (!$model->getFriendConnection($to, $from))$model->addFriend($to, $from);		
						break;
					case 2://one way from - Must be approved by the recipient							
								if (!$model->getFriendConnection($to, $from))$model->addFriend($from, $to);
						break;					
					default://default two way autoapprove
							if (!$model->getFriendConnection($to, $from))$model->addFriendRequest($to, $from);
									$count = $model->getFriendsCount($from);
									$this->addFriendCount($from, $count);
									$count = $model->getFriendsCount($to);
									$this->addFriendCount($to, $count);						
						break;
				}
			}	
	}
	
	
	function send($vars)
	{	    
		$db = &JFactory::getDBO();
		$my	=& JFactory::getUser($vars['id']);
		$userTo 		=& CFactory::getUser($vars['to']);
		$recipientName	= $userTo->getDisplayName();
		
		$search 	= array('{actor}', '{target}');
		$replace 	= array($userTo->getDisplayName(), $recipientName );
				
		$vars['subject'] 	= JString::str_ireplace($search, $replace, $vars['subject']);
		$vars['body'] 		= JString::str_ireplace($search, $replace, $vars['body']);
		
		// @todo: user db table later on				
		//$cDate =& JFactory::getDate(gmdate('Y-m-d H:i:s'), $mainframe->getCfg('offset'));//get the current date from system.
		//$date	= cGetDate();
		$date	=& JFactory::getDate(); //get the time without any offset!
		$cDate	=$date->toMySQL(); 
		
		$obj = new stdClass();
		$obj->id = null;
		$obj->from = $my->id;
		$obj->posted_on = $date->toMySQL();
		$obj->from_name	= $my->name;
		$obj->subject	= $vars['subject'];
		$obj->body		= $vars['body'];
		
		$db->insertObject('#__community_msg', $obj, 'id');
		
		// Update the parent
		$obj->parent = $obj->id;
		$db->updateObject('#__community_msg', $obj, 'id');
		
		if(is_array($vars['to'])){
		    //multiple recepint
		    foreach($vars['to'] as $sToId){
		        $this->addReceipient($obj, $sToId);
		    }		    
		} else {
		    //single recepient
		    $this->addReceipient($obj, $vars['to']);
		}    
		
		return $obj->id;
	}
	
	/**
	 * Add receipient
	 */	 	
	function addReceipient($msgObj, $recepientId){
		$db = &JFactory::getDBO();
	        
		$recepient = new stdClass();
		$recepient->msg_id = $msgObj->id;
		$recepient->msg_parent = $msgObj->parent;
		$recepient->msg_from = $msgObj->from;
		$recepient->to	= $recepientId;		
		$db->insertObject('#__community_msg_recepient', $recepient);
		
		if($db->getErrorNum()) {
		     JError::raiseError( 500, $db->stderr());
	    }
	}
	
        function addFriendCount($userId, $count)
        {
            $db = &JFactory::getDBO();
            
            $query = 'UPDATE '.$db->nameQuote('#__community_users')
            .'SET '.$db->nameQuote('friendcount').'='.$db->Quote($count)
            .'WHERE '.$db->nameQuote('userid').'='.$db->Quote($userId);
            $db->setQuery($query);
            $db->query();

            if ($db->getErrorNum())
            {
                JError::raiseError(500, $db->stderr());
            }
            return;
        }	
	

}