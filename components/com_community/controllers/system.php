<?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class CommunitySystemController extends CommunityBaseController
{
	public function ajaxShowInvitationForm( $friends , $callback , $cid , $displayFriends , $displayEmail )
	{
		$objResponse	= new JAXResponse();
		$displayFriends	= (bool) $displayFriends;
		
		$invitation		=& JTable::getInstance( 'Invitation' , 'CTable' );
		$invitation->load( $callback , $cid );
		
		$friends		= empty( $friends ) ? array() : explode( ',' , $friends );
		$tmpl			= new CTemplate();
		$tmpl->set( 'friends'	, $friends );
		$tmpl->set( 'selected'	, $invitation->getInvitedUsers() );		
		$tmpl->set( 'displayFriends' , $displayFriends );
		$tmpl->set( 'displayEmail'	, $displayEmail );
		$html			= $tmpl->fetch( 'ajax.showinvitation' );

		$buttons    = '<input type="button" class="button" onclick="joms.invitation.send(\'' . $callback . '\',\'' . $cid . '\');" value="' . JText::_('CC SEND INVITATIONS') . '"/>';
		
		$objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('CC INVITE FRIENDS'));
		$objResponse->addAssign('cWindowContent', 'innerHTML', $html );
		$objResponse->addScriptCall('cWindowActions', $buttons);
		return $objResponse->sendResponse();
	}
	
	public function ajaxSubmitInvitation( $callback , $cid , $values )
	{
		$objResponse	= new JAXResponse();
		$my				= CFactory::getUser();
		$methods		= explode( ',' , $callback );
		$emails			= array();
		$recipients		= array();
		$users			= '';
		$message		= $values[ 'message' ];
		$values['friends']	= isset( $values['friends'] ) ? $values['friends'] : array();
		
		if( !is_array( $values['friends'] ) )
		{
			$values['friends']	= array( $values['friends'] );
		}

		// This is where we process external email addresses
		if( !empty( $values[ 'emails' ] ) )
		{
			$emails	= explode( ',' , $values[ 'emails' ] );
			foreach( $emails as $email )
			{
				$recipients[]	= $email;
			}
		}
		
		// This is where we process site members that are being invited
		if( !empty( $values[ 'friends' ] ) )
		{
			$users		= implode( ',' , $values['friends'] );
			
			foreach( $values['friends'] as $id )
			{
				$recipients[]	= $id;
			}
		}

		if( !empty( $recipients) )
		{
			$arguments		=  array( $cid , $values['friends'] , $emails , $message );
			
			if( is_array( $methods ) && $methods[0] != 'plugins' )
			{
				$controller	= JString::strtolower( $methods[0] );
				$function	= $methods[1];
				require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'controllers' . DS . 'controller.php' );
				$file		= JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'controllers' . DS . $controller . '.php';
	 			
	 			
	 			if( JFile::exists( $file ) )
	 			{
	 				require_once( $file );

					$controller	= JString::ucfirst( $controller );
		 			$controller	= 'Community' . $controller . 'Controller';
		 			$controller	= new $controller();
		 			
		 			if( method_exists( $controller , $function ) )
					{ 
		 				$inviteMail	= call_user_func_array( array( $controller , $function ) , $arguments );
		 			}
		 			else
		 			{
		 				$objResponse->addAssign('invitation-error' , 'innerHTML' , JText::_('CC INVITE EXTERNAL METHOD ERROR' ) );
						return $objResponse->sendResponse();
					}
				}
				else
				{
	 				$objResponse->addAssign('invitation-error' , 'innerHTML' , JText::_('CC INVITE EXTERNAL METHOD ERROR' ) );
					return $objResponse->sendResponse();
				}
			}
			else if( is_array( $methods ) && $methods[0] == 'plugins' )
			{
				// Load 3rd party applications
				$element	= JString::strtolower( $methods[1] );
				$function	= $methods[2];
				$file		= JPATH_PLUGINS . DS . 'community' . DS . $element . '.php';

	 			if( JFile::exists( $file ) )
	 			{
	 				require_once( $file );
					$className	= 'plgCommunity' . JString::ucfirst( $element );
				
				
		 			if( method_exists( $controller , $function ) )
					{
						$inviteMail	= call_user_func_array( array( $className , $function ) , $arguments ); 
		 			}
		 			else
		 			{
		 				$objResponse->addAssign('invitation-error' , 'innerHTML' , JText::_('CC INVITE EXTERNAL METHOD ERROR' ) );
						return $objResponse->sendResponse();
					}
				}
				else
				{
	 				$objResponse->addAssign('invitation-error' , 'innerHTML' , JText::_('CC INVITE EXTERNAL METHOD ERROR' ) );
					return $objResponse->sendResponse();
				}
			}
			
			CFactory::load( 'libraries' , 'invitation' );
			
			// If the responsible method returns a false value, we should know that they want to stop the invitation process.
			
			if( $inviteMail instanceof CInvitationMail )
			{
				if( $inviteMail->hasError() )
				{
					$objResponse->addAssign('invitation-error' , 'innerHTML' , $inviteMail->getError() );
			
					return $objResponse->sendResponse();
				}
				else
				{
					// Once stored, we need to store selected user so they wont be invited again
					$invitation		=& JTable::getInstance( 'Invitation' , 'CTable' );
					$invitation->load( $callback , $cid );
	
					if( !empty( $values['friends'] ) )
					{
						if( !$invitation->id )
						{
							// If the record doesn't exists, we need add them into the
							$invitation->cid		= $cid;
							$invitation->callback	= $callback;
						}
						$invitation->users	= empty( $invitation->users ) ? implode( ',' , $values[ 'friends' ] ) : $invitation->users . ',' . implode( ',' , $values[ 'friends' ] );
						$invitation->store();
					}
	
					// Add notification
					CFactory::load( 'libraries' , 'notification' );	
		 			CNotificationLibrary::add( 'groups.invite' , $my->id , $recipients , $inviteMail->getTitle() , $inviteMail->getContent() , '' , $inviteMail->getParams() );
				}
			}
			else
			{
				$objResponse->addScriptCall( JText::_('CC INVITE INVALID RETURN TYPE') );
				return $objResponse->sendResponse();
			}
		}
		else
		{
			$objResponse->addAssign('invitation-error' , 'innerHTML' , JText::_('CC INVITE NO SELECTION') );
			
			return $objResponse->sendResponse();
		}

		$buttons    = '<input type="button" class="button" onclick="cWindowHide();" value="' . JText::_('CC BUTTON CLOSE') . '"/>';
		$html		= JText::_( 'CC INVITE SENT' );		
		
		$objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('CC INVITE FRIENDS'));
 		$objResponse->addAssign('cWindowContent', 'innerHTML', $html );
		$objResponse->addScriptCall('cWindowActions', $buttons);
		$objResponse->addScriptCall('cWindowResize' , 150 );
		
		return $objResponse->sendResponse();
	}
	
	public function ajaxReport( $reportFunc , $pageLink )
	{
		$objResponse    = new JAXResponse();
		$config			= CFactory::getConfig();
		
		$reports		= JString::trim( $config->get( 'predefinedreports' ) );
		
		$reports		= empty( $reports ) ? false : explode( "\n" , $reports );

		$html = '';

		$argsCount		= func_num_args();

		$argsData		= '';
		
		if( $argsCount > 1 )
		{
			
			for( $i = 2; $i < $argsCount; $i++ )
			{
				$argsData	.= "\'" . func_get_arg( $i ) . "\'";
				$argsData	.= ( $i != ( $argsCount - 1) ) ? ',' : '';
			}
		}

		ob_start();
?>
		<form id="report-form" name="report-form" action="" method="post">
			<table class="cWindowForm" cellspacing="1" cellpadding="0">
				<tr>
					<td class="cWindowFormKey"><?php echo JText::_('CC PREDEFINED REPORTS');?></td>
					<td class="cWindowFormVal">
						<select id="report-predefined" onchange="if(this.value!=0) joms.jQuery('#report-message').val( this.value ); else joms.jQuery('#report-message').val('');">
							<option selected="selected" value="0"><?php echo JText::_('CC SELECT PREDEFINED REPORTS'); ?></option>
							<?php
							if( $reports )
							{
								foreach( $reports as $report )
								{
							?>
								<option value="<?php echo $report;?>"><?php echo $report; ?></option>
							<?php
								}
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="cWindowFormKey"><?php echo JText::_('CC REPORT MESSAGE');?><span id="report-message-error"></span></td>
					<td class="cWindowFormVal"><textarea id="report-message" name="report-message" rows="3"></textarea></td>
				</tr>
				<tr class="hidden">
					<td class="cWindowFormKey"></td>
					<td class="cWindowFormVal"><input type="hidden" name="reportFunc" value="<?php echo $reportFunc; ?>" /></td>
				</tr>
			</div>
		</form>
<?php
		$html	.= ob_get_contents();
		ob_end_clean();
		
		ob_start();
?>
		<button class="button" onclick="joms.report.submit('<?php echo $reportFunc;?>','<?php echo $pageLink;?>','<?php echo $argsData;?>');" name="submit">
		<?php echo JText::_('CC BUTTON SEND');?>
		</button>
		<button class="button" onclick="javascript:cWindowHide();" name="cancel">
		<?php echo JText::_('CC BUTTON CANCEL');?>
		</button>
<?php
		$action	= ob_get_contents();
		ob_end_clean();

		// Change cWindow title
		$objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('CC REPORT THIS'));
		$objResponse->addAssign('cWindowContent', 'innerHTML', $html );
		$objResponse->addScriptCall('cWindowActions', $action);
		$objResponse->addScriptCall('cWindowResize', 200);
		
		return $objResponse->sendResponse();
	}
	
	public function ajaxSendReport()
	{
		$reportFunc		= func_get_arg( 0 );
		$pageLink		= func_get_arg( 1 );
		$message		= func_get_arg( 2 );

		$argsCount		= func_num_args();
		$method			= explode( ',' , $reportFunc );

		$args			= array();
		$args[]			= $pageLink;
		$args[]			= $message;
		
		for($i = 3; $i < $argsCount; $i++ )
		{
			$args[]		= func_get_arg( $i );
		}

		// Reporting should be session sensitive
		// Construct $output
		$uniqueString	= md5($reportFunc.$pageLink);
		$session = JFactory::getSession();

		
		if( $session->has('action-report-'. $uniqueString))
		{
			$output	= JText::_('CC REPORT ALREADY SENT');
		}
		else
		{
			if( is_array( $method ) && $method[0] != 'plugins' )
			{
				$controller	= JString::strtolower( $method[0] );
				
	 			require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'controllers' . DS . 'controller.php' );
	 			require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'controllers' . DS . $controller . '.php' );
	
				$controller	= JString::ucfirst( $controller );
	 			$controller	= 'Community' . $controller . 'Controller';
	 			$controller	= new $controller();
	 			
	 			
	 			$output		= call_user_func_array( array( &$controller , $method[1] ) , $args );
			}
			else if( is_array( $method ) && $method[0] == 'plugins' )
			{
				// Application method calls
				$element	= JString::strtolower( $method[1] );
				require_once( JPATH_PLUGINS . DS . 'community' . DS . $element . '.php' );
				$className	= 'plgCommunity' . JString::ucfirst( $element );
				$output		= call_user_func_array( array( $className , $method[2] ) , $args );
			}
		}
		$session->set('action-report-'. $uniqueString, true);
		
		// Construct the action buttons $action
		ob_start();
?>
		<button class="button" onclick="javascript:cWindowHide();" name="cancel">
		<?php echo JText::_('CC BUTTON CLOSE');?>
		</button>
<?php
		$action	= ob_get_contents();
		ob_end_clean();
		
		// Construct the ajax response
		$objResponse	= new JAXResponse();
		$objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('CC REPORT SENT'));
		$objResponse->addAssign('cWindowContent', 'innerHTML', $output);
		$objResponse->addScriptCall('cWindowActions', $action);
		$objResponse->addScriptCall('cWindowResize', 100);
		
		return $objResponse->sendResponse();
	}
	
	public function ajaxEditWall( $wallId , $editableFunc )
	{
		$objResponse	= new JAXResponse();
		$wall			=& JTable::getInstance( 'Wall' , 'CTable' );
		$wall->load( $wallId );
		
		CFactory::load( 'libraries' , 'wall' );
		$isEditable		= CWall::isEditable( $editableFunc , $wall->id );
		
		if( !$isEditable )
		{
			$objResponse->addAlert(JText::_('CC NOT ALLOWED TO EDIT') );
			return $objResponse->sendResponse();
		}

		CFactory::load( 'libraries' , 'comment' );
		$tmpl			= new CTemplate();
		$message		= CComment::stripCommentData( $wall->comment );
		$tmpl->set( 'message' , $message );
		$tmpl->set( 'editableFunc' , $editableFunc );
		$tmpl->set( 'id'	, $wall->id );
		
		$content		= $tmpl->fetch( 'wall.edit' );
		
		$objResponse->addScriptCall( 'joms.jQuery("#wall_' . $wallId . ' div.loading").hide();');
		$objResponse->addAssign( 'wall-edit-container-' . $wallId , 'innerHTML' , $content );
		
		return $objResponse->sendResponse();
	}
	
	public function ajaxUpdateWall( $wallId , $message , $editableFunc )
	{
		$wall			=& JTable::getInstance( 'Wall' , 'CTable' );
		$wall->load( $wallId );
		$objResponse	= new JAXresponse();
		
		if( empty($message) )
		{
			$objResponse->addScriptCall( 'alert' , JText::_('CC EMPTY MESSAGE') );
			return $objResponse->sendResponse();
		}
		

		CFactory::load( 'libraries' , 'wall' );
		$isEditable		= CWall::isEditable( $editableFunc , $wall->id );
		
		if( !$isEditable )
		{
			$objResponse->addAssign( 'cWindowContent' , 'innerHTML' , JText::_('CC NOT ALLOWED TO EDIT') );
			return $objResponse->sendResponse();
		}
			
		CFactory::load( 'libraries' , 'comment' );
		
		// We don't want to touch the comments data.
		$comments		= CComment::getRawCommentsData( $wall->comment );
		$wall->comment	= $message;
		$wall->comment	.= $comments;
		$my				= CFactory::getUser();
		$data			= CWallLibrary::saveWall( $wall->contentid , $wall->comment , $wall->type , $my , false , $editableFunc , 'wall.content' , $wall->id );		
		
		$objResponse	= new JAXResponse();
		
		$objResponse->addScriptCall('joms.walls.update' , $wall->id , $data->content );

		return $objResponse->sendResponse();
	}
	
	public function ajaxGetOlderWalls($groupId, $discussionId, $limitStart)
	{
		$response	= new JAXResponse();

		$my			= CFactory::getUser();
		$jconfig	= JFactory::getConfig();
		
		$groupModel		= CFactory::getModel( 'groups' );
		$isGroupAdmin	=   $groupModel->isAdmin( $my->id , $groupId );
		
		CFactory::load( 'libraries' , 'wall' );
		$html	= CWall::getWallContents( 'discussions' , $discussionId , $isGroupAdmin , $jconfig->get('list_limit') , $limitStart, 'wall.content','groups,discussion', $groupId);
		
		// parse the user avatar
		CFactory::load( 'helpers' , 'string' );
		$html = CStringHelper::replaceThumbnails($html);
		$html = JString::str_ireplace(array('{error}', '{warning}', '{info}'), '', $html);
		
		
		$config	= CFactory::getConfig();
		$order	= $config->get('group_discuss_order');
		
		if ($order == 'ASC')
		{
			// Append new data at Top.
			$response->addScriptCall('joms.walls.prepend' , $html );
		} else {
			// Append new data at bottom.
			$response->addScriptCall('joms.walls.append' , $html );
		}
		
		return $response->sendResponse();
	}
	
	/**
	 * Like an item. Update ajax count
	 * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
	 * @param mixed $itemId	    Unique id to identify object item
	 *
	 */
	public function ajaxLike( $element, $itemId )
	{
		if (!COwnerHelper::isRegisteredUser())
		{
			return $this->ajaxBlockUnregister();
		}

		// @rule: Only display likes html codes when likes is allowed.
		$config		=& CFactory::getConfig();
		
		if( !$config->get( 'likes_' . $element ) )
		{
			return;
		}
		
		$my		=   CFactory::getUser();
		$objResponse	=   new JAXResponse();

		// Load libraries
		CFactory::load( 'libraries' , 'like' );
		$likes	=   new CLike();
		$result	=   $likes->addLike( $element, $itemId );

		if( !$result )
		{
			$msg	    =   JText::_('CC LIKE ERROR');

			$objResponse->addScriptCall('cWindowShow', '', JText::_('CC LIKE'), 430, 100);
			$objResponse->addAssign( 'cWindowContent' , 'innerHTML' , $msg );
		}
		else
		{
			$like = new CLike();
			$html = $like->getHTML( $element, $itemId, $my->id );

			$objResponse->addScriptCall('__callback', $html);
		}

		return $objResponse->sendResponse();
	}
	
	/**
	 * Dislike an item
	 * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
	 * @param mixed $itemId	    Unique id to identify object item
	 * 
	 */
	public function ajaxDislike( $element, $itemId )
	{
		if (!COwnerHelper::isRegisteredUser())
		{
			return $this->ajaxBlockUnregister();
		}

		// @rule: Only display likes html codes when likes is allowed.
		$config		=& CFactory::getConfig();
		
		if( !$config->get( 'likes_' . $element ) )
		{
			return;
		}
		
		$my		=   CFactory::getUser();
		$objResponse	=   new JAXResponse();

		// Load libraries
		CFactory::load( 'libraries' , 'like' );
		$dislikes   =   new CLike();
		$result	    =   $dislikes->addDislike( $element, $itemId );

		if( !$result )
		{
			$msg	=   JText::_('CC DISLIKE ERROR');

			$objResponse->addScriptCall('cWindowShow', '', JText::_('CC DISLIKE'), 430, 100);
			$objResponse->addAssign( 'cWindowContent' , 'innerHTML' , $msg );
		}
		else
		{
			$like = new CLike();
			$html = $like->getHTML( $element, $itemId, $my->id );

			$objResponse->addScriptCall('__callback', $html);
		}
		
		return $objResponse->sendResponse();
	}

	/**
	 * Unlike an item
	 * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
	 * @param mixed $itemId	    Unique id to identify object item
	 *
	 */
	public function ajaxUnlike( $element, $itemId )
	{
		if (!COwnerHelper::isRegisteredUser())
		{
			return $this->ajaxBlockUnregister();
		}

		// @rule: Only display likes html codes when likes is allowed.
		$config		=& CFactory::getConfig();
		
		if( !$config->get( 'likes_' . $element ) )
		{
			return;
		}
		
		$my		=   CFactory::getUser();
		$objResponse	=   new JAXResponse();

		// Load libraries
		CFactory::load( 'libraries' , 'like' );
		$unlike	    =   new CLike();
		$result	    =   $unlike->unlike( $element, $itemId );

		if( !$result )
		{
			$msg	=   JText::_('CC UNLIKE ERROR');
			
			$objResponse->addScriptCall('cWindowShow', '', JText::_('CC UNLIKE'), 430, 100);
			$objResponse->addAssign( 'cWindowContent' , 'innerHTML' , $msg );
		}
		else
		{
			$like = new CLike();
			$html = $like->getHTML( $element, $itemId, $my->id );

			$objResponse->addScriptCall('__callback', $html);
		}

		return $objResponse->sendResponse();
	}

	/**
	 * Undislike an item
	 * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
	 * @param mixed $itemId	    Unique id to identify object item
	 *
	 */
	public function ajaxUndislike( $element, $itemId )
	{
		if (!COwnerHelper::isRegisteredUser())
		{
			return $this->ajaxBlockUnregister();
		}

		// @rule: Only display likes html codes when likes is allowed.
		$config		=& CFactory::getConfig();
		
		if( !$config->get( 'likes_' . $element ) )
		{
			return;
		}
		
		$my		=   CFactory::getUser();
		$objResponse	=   new JAXResponse();

		// Load libraries
		CFactory::load( 'libraries' , 'like' );
		$unlike	    =   new CLike();
		$result	    =   $unlike->unlike( $element, $itemId );

		if( !$result )
		{
			$msg	=   JText::_('CC UNLIKE ERROR');
			
			$objResponse->addScriptCall('cWindowShow', '', JText::_('CC UNLIKE'), 430, 100);
			$objResponse->addAssign( 'cWindowContent' , 'innerHTML' , $msg );
		}
		else
		{
			$like = new CLike();
			$html = $like->getHTML( $element, $itemId, $my->id );

			$objResponse->addScriptCall('__callback', $html);
		}

		return $objResponse->sendResponse();
	}
}
