<?php
/**
 * @package		ActivityComment
 * @copyright	Copyright (C) 2009 - 2010 SocialCode. All rights reserved.
 * @license		GNU/GPL
 * ActivityComment is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses.
 */
defined('_JEXEC') or die('Restricted access');
require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');
require_once( JPATH_PLUGINS.DS.'community' . DS . 'activitycomment' .DS.'helper.php');

jimport('joomla.html.pagination');
JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
class plgCommunityActivityComment extends CApplications
{
	var $name		= 'Activitycomment';
	var $_name		= 'activitycomment';
	var $_user		= null;
	var $pagination	= null;
	
    function plgCommunityActivityComment(& $subject, $config)
    {
		$this->_user	=& CFactory::getActiveProfile();
		$this->_my		=& CFactory::getUser();

		parent::__construct($subject, $config);
    }
	
	function waiting( $response )
	{
		JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
		$html	= '<img src="' . JURI::root() . 'plugins/community/activitycomment/wait.gif" /><span class="fetching-content">' . JText::_('FETCHING CONTENT') . '</span>';
		$response->addAssign( 'share-url-meta' , 'innerHTML' , $html );
		$response->addScriptCall('activityshowmeta();');
		$response->addScriptCall('activitygetmeta();');
		$response->addScriptCall('activityhidepost();');
		return $response->sendResponse();
	}
	
	function addUrl( $response, $url , $from = 'frontpage' , $noImage = false , $image = '' )
	{
		JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
		$my	= CFactory::getUser();
		$params	= ActivityComments::getParams();
		
		if( $my->id == 0 )
		{
			$response->addScriptCall( 'alert' , 'NOT ALLOWED' );
			return $response->sendResponse();
		}
		
		$content	= $this->_getContent( $url , $noImage , true  , $image );
	
		if( $content !== false )
		{
			$url		= JString::str_ireplace( 'http://' , '' , $url );
			
			// Add activity logging
			CFactory::load ( 'libraries', 'activities' );
	
			$act = new stdClass();

			$target	= ' target="_blank"';
			
			if( $params->get('shareurltarget' , 1 ) == 0 )
			{
				$target = '';
			}

			$link	= '<a href="http://' . $url . '"'.$target.'>http://' . $url . '</a>';
			$author	= '<a href="' . CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id ) . '">' . $my->getDisplayName() . '</a>';
			$act->cmd 		= 'activitycomment.share.url';
			$act->actor 	= $my->id;
			$act->target 	= 0;
			$act->title		= JText::sprintf('ACTIVITY SHARED LINK TITLE' , $author , $link );
			$act->content	= $content;
			$act->app		= 'activitycomment.url';
			$act->cid		= $my->id;
			CActivityStream::add( $act );

			include_once(JPATH_COMPONENT . DS.'libraries'.DS.'activities.php');
			$act 	= new CActivityStream();
	
			$config	= CFactory::getConfig();
			
			if( $from == 'frontpage')
			{
				
				$html	= $act->getHTML('', '', null, $config->get('maxacitivities'));
			}
			else
			{
				$friendsModel	=& CFactory::getModel('friends');
				
				CFactory::load( 'helpers' , 'time');
				$memberSince	= cGetDate($my->registerDate);
				$friendIds		= $friendsModel->getFriendIds($my->id);

				$html	= $act->getHTML($my->id, $friendIds, $memberSince, 10);
			}
	
			$response->addAssign('activity-stream-container' , 'innerHTML' , $html );

		}		
		else
		{
			$response->addScriptCall( 'alert' , 'UNABLE TO CONNECT' );
		}

		return $response->sendResponse();
	}

	public function connect($url)
	{
		if (!$url)
			return false;
		
		if (function_exists('curl_init'))
		{
			$ch			= curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.1.3) Gecko/20090910 Ubuntu/9.04 (jaunty) Shiretoko/3.5.3");
			
			$response	= curl_exec($ch);
			curl_close($ch);
			return $response;
		}
	
		// CURL unavailable on this install
		return false;
	}
	
	function _getContent(  $url , $noImage = false , $isRaw = false , $image )
	{
		JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
		CFactory::load( 'helpers' , 'remote');

		require_once( JPATH_PLUGINS.DS.'community'.DS.'activitycomment'.DS.'helper.php');
		
		$params = ActivityComments::getParams();
		$content	= $this->connect( $url );
		
		if( $content === false )
			return false;
		
		$allimages	= array();

		// get encoding type
		$reg	= '/<meta http-equiv="[cC]ontent-[tT]ype" content="text\/html;[]charset=*[\"\']{0,1}([^\"\\>]*)"/i';
		preg_match( $reg , $content , $matches );
		
		$charset	= '';				
		if( isset($matches[1]) )
		{
			$ch = explode( '=' , $matches[1] );
			$charset	= $ch[1];
		}

		$desc		= '';
		$info		= '';
		$title		= '';
		$reg		= '/\<meta name="description" content=*[\"\']{0,1}([^\"\\>]*)/i';
		preg_match( $reg, $content , $matches );

		if( isset($matches[1]) )
		{
			$info	.= '<div>' . $matches[1] . '</div>';
		}
		else
		{
			$info		= JText::_('NO INFO AVAILABLE');
		}

		$reg		= '/\<title\>(.*)\<\/title\>/i';
		preg_match( $reg, $content , $matches );
		
		if( isset($matches[1]) )
		{
			$title	= '<div>' . $matches[1] . '</div>';
		}
		else
		{
			$title		= JText::_('NO TITLE ON SITE');
		}
		
		$desc	= '<table>';
		$desc	.= '<tr>';
		
		
		if( $params->get('photopreview' , 1) )
		{
			$desc	.= '<td valign="top" width="25%">';
			$maxW	= 250;
			$maxH	= 250;
			$width	= 150;
			$height	= 150;
			
			$sz		= @getimagesize( html_entity_decode( $image) );
			$totalimages	= count( $allimages );
			if( !empty( $sz) )
			{
				$width	= $sz[0] > $maxW ? $width : $sz[0];
				$height	= $sz[1] > $maxH ? $height : $sz[1];
			}

			if( $noImage )
			{
				$desc	.= '<span>' . JText::_('NO IMAGE') . '</span>';
			}
			else
			{
				$desc	.= '<img src="' . $image . '" width="' . $width . '" height="' . $height . '"/>';
			}
			$desc	.= '</td>';
		}
		
		if( $charset )
		{
			$title	= JString::transcode( $title, $charset , 'UTF-8' );
			$info	= JString::transcode( $info, $charset , 'UTF-8' );
		}
		$desc	.= '<td valign="top">';
		$desc	.= '<div style="font-weight:bold;">' . $title . '</div>';
		
		$target	= ' target="_blank"';
		
		if( $params->get('shareurltarget' , 1 ) == 0 )
		{
			$target = '';
		}
		$url	= JString::str_ireplace( 'http://' , '' , $url );
		$desc	.= '<div style="color: #666;"><a href="http://' . $url . '"'.$target.'>http://' . $url . '</a></div>';
		$desc	.= '<div class="share-url-meta-info">' . $info . '</div>';

		$desc	.= '</td>';
		$desc	.= '</tr>';
		$desc	.= '</table>';
		
		return $desc;
	}
	
	function _getUrlContent( $url , $noImage = false , $isRaw = false )
	{
		JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
		CFactory::load( 'helpers' , 'remote');

		require_once( JPATH_PLUGINS.DS.'community'.DS.'activitycomment'.DS.'helper.php');
		
		$params = ActivityComments::getParams();
		$content	= $this->connect( $url );
		$image		= '';
		
		if( $content === false )
			return false;
		
		$allimages	= array();
		
		if( !$noImage )
		{
			// Look for facebook alike link style
			$reg	= '/<link rel="image_src" href=*[\"\']{0,1}([^\"\'\ >]*)/i';
			preg_match( $reg , $content , $matches );
	
			if( empty($matches ) )
			{
				// BBC alike 
				$reg	= '/<meta name="THUMBNAIL_URL" content=*[\"\']{0,1}([^\"\'\ >]*)/i';
				preg_match( $reg , $content , $matches );
				
				if( isset( $matches[1] ) )
				{
					$allimages[]	= $matches[1];
				}
			}
			elseif( isset( $matches[1] ) )
			{
				$allimages[]	= $matches[1];
			}

			$reg		= '/<img.* src=*[\"\']{0,1}([^\"\'\ >]*)/i';
			preg_match_all( $reg, $content , $matches );
			
			if( isset( $matches[1] ) )
			{
				foreach( $matches[1] as $match )
				{
					$allimages[]	= $match;
				}
			}

			$url	= JString::str_ireplace( 'http://' , '' , $url );
			
			if( !empty($allimages) )
			{
				for( $i = 0; $i < count($allimages);$i++ )
				{
					$img =& $allimages[$i];
	
					if( JString::stristr( $img , 'http://' ) === false )
					{
						$img	= 'http://' . $url . '/' . ltrim( $img , '/' );
					}
				}
			}
		}

		// get encoding type
		$reg	= '/<meta http-equiv="[cC]ontent-[tT]ype" content="text\/html;[ c]harset=*[\"\']{0,1}([^\"\\>]*)"/i';
		preg_match( $reg , $content , $matches );
		
		$charset	= '';				
		if( isset($matches[1]) )
		{
			$charset	= $matches[1];
		}

		$desc		= '';
		$image		= $image;;
		$info		= '';
		$title		= '';
		$reg		= '/\<meta name="description" content=*[\"\']{0,1}([^\"\\>]*)/i';
		preg_match( $reg, $content , $matches );

		if( isset($matches[1]) )
		{
			$info	.= '<div>' . $matches[1] . '</div>';
		}
		else
		{
			$info		= JText::_('NO INFO AVAILABLE');
		}

		$reg		= '/\<title\>(.*)\<\/title\>/i';
		preg_match( $reg, $content , $matches );
		
		if( isset($matches[1]) )
		{
			$title	= '<div>' . $matches[1] . '</div>';
		}
		else
		{
			$title		= JText::_('NO TITLE ON SITE');
		}
		
		$desc	= '<table>';
		$desc	.= '<tr>';
		
		
		if( $params->get('photopreview' , 1) )
		{
			$desc	.= '<td valign="top" width="25%">';
			$maxW	= 250;
			$maxH	= 250;
			$width	= 150;
			$height	= 150;
			
			$sz		= @getimagesize( html_entity_decode( $image) );
			$totalimages	= count( $allimages );
			if( !empty( $sz) )
			{
				$width	= $sz[0] > $maxW ? $width : $sz[0];
				$height	= $sz[1] > $maxH ? $height : $sz[1];
			}

			if( empty($allimages) )
			{
				$desc	.= '<span>' . JText::_('NO IMAGE') . '</span>';
			}
			else
			{
				$desc	.= '<div id="images-wrapper">';
				for( $ix = 0,$e=1; $ix < count($allimages); $ix++,$e++)
				{
					$display	= ' style="display: none;"';
					
					if( $ix == 0 )
						$display	= ' style="display: block;"';
					
					$image	= $allimages[ $ix ];
					$desc	.= '<img src="' . $image . '" id="photo-share-' . $e . '" width="' . $width . '" height="' . $height . '"'. $display . ' />';
				}
				$desc	.= '<input type="hidden" id="photo-share-current" value="1" />';
				$desc	.= '<input type="hidden" id="photo-share-source" value="' . $allimages[0] . '" />';
				$desc	.= '</div>';
					
				$desc	.= '<div style="margin-top: 3px;">';
				$desc	.= '<a href="javascript:void(0);" onclick="prevPhoto(1);" id="prev-photo"><img src="' . JURI::root() . 'plugins/community/activitycomment/prev.png" border="0"/></a>';
				$desc	.= '<a href="javascript:void(0);" onclick="nextPhoto(\'' . $totalimages. '\');" id="next-photo"><img src="' . JURI::root() . 'plugins/community/activitycomment/next.png" border="0"/></a>';
				$desc	.= '</div>';
				$desc	.= '<div>';
				$desc	.= '<span id="image-count">1</span> ' . JText::_('OF') . ' <span id="total-count">' . $totalimages . '</span> ' . JText::_('IMAGES');
				$desc	.= '</div>';
			}
			$desc	.= '</td>';
		}
		
		if( $charset )
		{
			$title	= JString::transcode( $title, $charset , 'UTF-8' );
			$info	= JString::transcode( $info, $charset , 'UTF-8' );
		}
		$desc	.= '<td valign="top">';
		$desc	.= '<div style="font-weight:bold;">' . $title . '</div>';
		
		$target	= ' target="_blank"';
		
		if( $params->get('shareurltarget' , 1 ) == 0 )
		{
			$target = '';
		}
		$desc	.= '<div style="color: #666;"><a href="http://' . $url . '"'.$target.'>http://' . $url . '</a></div>';
		$desc	.= '<div class="share-url-meta-info">' . $info . '</div>';

		if( !$isRaw )
			$desc	.= '<input type="checkbox" name="no-image" id="no-image" /><label for="no-image" style="display: inline;">' . JText::_('NO THUMBNAIL') . '</label>';
			
		$desc	.= '</td>';
		$desc	.= '</tr>';
		$desc	.= '</table>';
		
		return $desc;
	}

	function getMeta( $response, $url )
	{
		JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
		$content	= $this->_getUrlContent( $url );
		
		if( $content !== false )
		{

			$response->addAssign( 'share-url-meta' , 'innerHTML' , $content );
			$response->addScriptCall( 'activityshowmeta();' );
			$response->addScriptCall( 'activityshowpostbutton();');
		}
		else
		{
			$content	= JText::_('UNABLE TO CONNECT');
			$response->addAssign( 'share-url-meta' , 'innerHTML' , $content );
			$response->addScriptCall( 'activityshowmeta();' );
		}
		return $response->sendResponse();
	}
	
	function rebuildItemString( $id )
	{
		require_once( JPATH_PLUGINS.DS.'community'.DS.'activitycomment'.DS.'helper.php');
		
		$items = ActivityComments::getLikes($id);
		$rows = count($items);
		$i = 0;
		$text = '';
		$comma = ' , ';
		$my =& CFactory::getUser();
		if($items )
		{
			foreach( $items as $item )
			{
				$i++;
				$user = & CFactory::getUser( $item->userid );
				$name	= $my->id == $item->userid ? JText::_('YOU') : $user->getDisplayName();
				
				if( $i == 1 )
				{
					$comma = '';
				}
				elseif($i == $rows)
				{
					$comma = ' ' . JText::_('AND') . ' ';
				}
				else
				{
					$comma	= ' , ';
				}
				$text .= '<span id="like-' . $id . '-'. $my->id . '">'.$comma.'<a href="' . CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id) . '">' . $name . '</a></span>';
			}
			$text .= '<span>' . JText::_('LIKE THIS') . '</span>';
		}
		
		return $text;

	}
	
	function addPhoto( $response , $url , $title , $from = 'frontpage')
	{
		JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
		$my	= CFactory::getUser();
		$url	= strip_tags( $url );

		if( $my->id == 0 )
		{
			$response->addScriptCall( 'alert' , 'NOT ALLOWED' );
			return $response->sendResponse();
		}
		
		if( empty( $url ) )
		{
			$response->addScriptCall( 'alert' , JText::_('CANNOT POST EMPTY URL') );
			return $response->sendResponse();
		}

		// Add activity logging
		CFactory::load ( 'libraries', 'activities' );

		$act = new stdClass();

		$act->cmd 		= 'activitycomment.share.photos';
		$act->actor 	= $my->id;
		$act->target 	= 0;
		$act->title		= '<a href="' . CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id ) . '">' . $my->getDisplayName() . '</a> ' . JText::_( $title );
		$act->content	= '<a href="' . $url . '" target="_blank"><img src="' . $url . '" width="120" border="0" /></a>';
		$act->app		= 'activitycomment.photos';
		$act->cid		= $my->id;
		CActivityStream::add( $act );

		$friendsModel	=& CFactory::getModel('friends');
		
		CFactory::load( 'helpers' , 'time');
		$memberSince	= cGetDate($my->registerDate);
		$friendIds		= $friendsModel->getFriendIds($my->id);

		include_once(JPATH_COMPONENT . DS.'libraries'.DS.'activities.php');
		$act 	= new CActivityStream();

		$config	= CFactory::getConfig();
		
		if( $from == 'frontpage')
		{
			
			$html	= $act->getHTML('', '', null, $config->get('maxacitivities'));
		}
		else
		{
			$html	= $act->getHTML($my->id, $friendIds, $memberSince, 10);
		}

		$response->addAssign('activity-stream-container' , 'innerHTML' , $html );
		$response->addAssign( 'post-message' , 'innerHTML' , JText::_('MSG POSTED') );
		$response->addScriptCall( 'jQuery("#post-message").fadeIn("fast");');
		$response->addScriptCall( 'setTimeout("jQuery(\'#post-message\').fadeOut();",6000);');
		$response->addScriptCall('activityshareshow("photos");');
		return $response->sendResponse();
	}
		
	function addNote( $response , $note , $from = 'frontpage' )
	{
		JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
		$my	= CFactory::getUser();
		$note	= strip_tags( $note );
		CFactory::load( 'helpers' , 'linkgenerator' );
		
		$params	= ActivityComments::getParams();
		$note	= ActivityComments::replaceURL( $note , true , $params->get('shareurltarget') );

		if( $my->id == 0 )
		{
			$response->addScriptCall( 'alert' , 'NOT ALLOWED' );
			return $response->sendResponse();
		}
		
		if( empty($note) )
		{
			$response->addScriptCall( 'alert' , JText::_('CANNOT POST EMPTY URL') );
			return $response->sendResponse();
		}
		
		if( $params->get('updatestatus') )
		{
			$status		=& CFactory::getModel('status');
			$status->update($my->id, $note );
			$response->addScriptCall( "joms.jQuery('#profile-status-message').html('" . $note . "');");
			$response->addScriptCall( "joms.jQuery('title').val('" . $note . "');");
		}

		// Add activity logging
		CFactory::load ( 'libraries', 'activities' );

		$act = new stdClass();

		$act->cmd 		= 'activitycomment.share.note';
		$act->actor 	= $my->id;
		$act->target 	= 0;
		$act->title		= '<a href="' . CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id ) . '">' . $my->getDisplayName() . '</a> ' . JText::_( $note );
		$act->content	= '';
		$act->app		= 'activitycomment.note';
		$act->cid		= $my->id;
		CActivityStream::add( $act );

		$friendsModel	=& CFactory::getModel('friends');
		
		CFactory::load( 'helpers' , 'time');
		$memberSince	= cGetDate($my->registerDate);
		$friendIds		= $friendsModel->getFriendIds($my->id);

		include_once(JPATH_COMPONENT . DS.'libraries'.DS.'activities.php');
		$act 	= new CActivityStream();
		$config	= CFactory::getConfig();
		
		if( $from == 'frontpage')
		{
			
			$html	= $act->getHTML('', '', null, $config->get('maxacitivities'));
		}
		else
		{
			$html	= $act->getHTML($my->id, $friendIds, $memberSince, 10);
		}
					

		$response->addAssign('activity-stream-container' , 'innerHTML' , $html );
		$response->addAssign( 'post-message' , 'innerHTML' , JText::_('MSG POSTED') );
		$response->addScriptCall( 'jQuery("#post-message").fadeIn("fast");');
		$response->addScriptCall( 'setTimeout("jQuery(\'#post-message\').fadeOut();",6000);');
		return $response->sendResponse();
	}
	
	function subscribe($response, $id )
	{
		JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
		$my = CFactory::getUser();
		
		$db = JFactory::getDBO();
		$query ='insert into #__activity_subscribe(`activity_id`,`user_id`,`type`) VALUES (' . $db->Quote($id) . ','. $db->Quote($my->id) . ',' . $db->Quote('profile') . ')';
		$db->setQuery( $query );
		$db->query();
		
		$response->sendResponse();
	}

	function unsubscribe($response, $id )
	{
		JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
		$my = CFactory::getUser();
		
		$db = JFactory::getDBO();
		$query = 'delete from #__activity_subscribe where `activity_id`=' . $db->Quote( $id ) . ' '
				. 'and `user_id`=' . $db->Quote( $my->id ) . ' '
				. 'and `type`=' . $db->Quote( 'profile' );
		$db->setQuery( $query );
		$db->query();
		
		$response->sendResponse();
	}
			
	function likeitem($response, $id , $userid )
	{
		JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
		$my=& CFactory::getUser();
		
		if($my->id == $userid )
		{
			$db =& JFactory::getDBO();
			$query ='insert into #__activitylikes(`activityid`,`userid`) VALUES (' . $db->Quote($id) . ','. $db->Quote($userid) . ')';
			$db->setQuery($query);
			$db->query();
			
			require_once( JPATH_PLUGINS.DS.'community'.DS.'activitycomment'.DS.'helper.php');
			
			$params = ActivityComments::getParams();
			$sendEmail = $params->get('notifylike' , 0 );
			
			if( $sendEmail == 1)
			{
				$db =& JFactory::getDBO();
				$query ='select actor from #__community_activities where id=' . $db->Quote($id);
				$db->setQuery($query);
				$actorId = $db->loadResult();
				if($actorId != $my->id)
				{
					$actor =& CFactory::getUser($actorId);
					CFactory::load( 'libraries' , 'notification' );
					$notification	= new CNotificationLibrary();
					$current = CRoute::emailLink( ActivityComments::getCurrent() , false ) . '#profile-newsfeed-item' . $id;
					$notification->add( 'profile.activity.like' , $my->id , $actorId , JText::sprintf('SOMEONE LIKED MAIL SUBJECT' , $my->getDisplayName() ) , JText::sprintf('SOMEONE LIKED MAIL CONTENT',$actor->getDisplayName(),$my->getDisplayName() ,$current) );
				}
			}
			$text = $this->rebuildItemString( $id );
			$response->addScriptCall( ActivityComments::getjs() . '("#likes-holder-' . $id . '").addClass("likes-content");');
			$response->addScriptCall('activityInsertLike', $id , $text);
			return $response->sendResponse();
		}
	}

	function unlikeitem($response, $id , $userid )
	{
		$my=& CFactory::getUser();
		JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
		if($my->id == $userid )
		{
			$db =& JFactory::getDBO();
			$query ='delete from #__activitylikes where userid=' .$db->Quote($my->id) . ' and activityid=' . $db->Quote($id);
			$db->setQuery($query);
			$db->query();
			
			// Check if there are other likes
			$query = 'select count(*) from #__activitylikes where activityid=' . $db->Quote($id);
			$db->setQuery($query);
			$rows = $db->loadResult();

			if( $rows == 0 )
			{
				$response->addScriptCall(ActivityComments::getjs() ."('#likes-holder-" . $id . "').removeClass('likes-content');");
			}
			$response->addScriptCall(ActivityComments::getjs() ."('#likes-holder-" . $id . "').children().remove();");

			$text = $this->rebuildItemString( $id );
			$response->addScriptCall('activityInsertLike', $id , $text);
			
			return $response->sendResponse();
		}
	}
	
	function removecomment($response,$id){
	require_once( JPATH_PLUGINS.DS.'community'.DS.'activitycomment'.DS.'helper.php');
	
	$db =& JFactory::getDBO();
			$query ='select actor from #__community_activities where id=' . $db->Quote($id);
			$db->setQuery($query);
			$actorId = $db->loadResult();
	$my =& CFactory::getUser();
	if(ActivityComments::isSiteAdmin() || $my->id != $actorId )
	{
		
		$db =& JFactory::getDBO();
		$query='delete from #__community_wall where id=' .$db->Quote($id);
		$db->setQuery($query);
		$db->Query();
		
		$response->addScriptCall('activityRemovecomment',$id);
		return $response->sendResponse();
	}
	}
	
	function morecomments($response, $id ){
		require_once( JPATH_PLUGINS.DS.'community'.DS.'activitycomment'.DS.'helper.php');
		JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
		
		$rows	= ActivityComments::getComments( $id , true );

		ob_start();
		foreach($rows as $wall )
		{
			$user =& CFactory::getUser($wall->post_by);
			$date	= JFactory::getDate( $wall->date );
	?>
		<div class="wallcmt small" id="activity-comment-item-<?php echo $wall->id;?>">
			<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $wall->post_by);?>"><img class="wall-coc-avatar" src="<?php echo $user->getThumbAvatar();?>"/></a>
			<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id);?>" class="wall-coc-author"><?php echo $user->getDisplayName();?></a> <?php echo JText::_('POST ON'); ?>
			<span class="wall-coc-date"><?php echo $date->toFormat(JText::_('DATE_FORMAT_LC2'));?></span>
					<?php
					if( ActivityComments::isSiteAdmin())
					{
					?>
					 | <span class="coc-remove"><a href="javascript:void(0);" onclick="jax.call('community','plugins,activitycomment,removecomment','<?php echo $wall->id;?>');"><?php echo JText::_('REMOVE COMMENT');?></a></span>
					<?php
					}
					?>
			<p><?php echo $wall->comment;?></p>
		</div>
	<?php
		}
		$contents	= ob_get_contents();
		ob_end_clean();
		
		$response->addScriptCall('activityMoreCommentsReplace' , $id , $contents );
		
		return $response->sendResponse();
	}
	
	function savecomment($response, $id , $value ){
	require_once( JPATH_PLUGINS.DS.'community'.DS.'activitycomment'.DS.'helper.php');
		JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
		
		$my =& CFactory::getUser();
		
		if( $my->id == 0 )
			return 'invalid';

		if(empty($value))
		{
			$response->addScriptCall(ActivityComments::getjs() .'("#activity-' . $id . '-comment-errors").html("' . JText::_('COMMENT CANNOT BE EMPTY') . '").css("color","red");' );
			return $response->sendResponse();
		}
		$my =& CFactory::getUser();
	require_once(JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'models'.DS.'wall.php');
		$wall		=& JTable::getInstance( 'Wall' , 'CTable' );

		CFactory::load( 'helpers' , 'linkgenerator' );
		
		$params	= ActivityComments::getParams();
		$value	= strip_tags($value);
		$value	= cGenerateURILinks( $value , true , $params->get('shareurltarget') );
		
		// Get current date
		$date		=& JFactory::getDate();
		$now		= $date->toMySQL();
		$wall->type			= 'activity';
		$wall->contentid	= $id;
		$wall->post_by		= $my->id;
		$wall->comment		= $value;
		$wall->date			= $now;
		$wall->published	= 1;
		$wall->ip			= $_SERVER['REMOTE_ADDR'];
		$wall->store();

			$params = ActivityComments::getParams();
			$sendEmail = $params->get('notifycomment' , 0 );
			$db =& JFactory::getDBO();
			if( $sendEmail == 1)
			{
				
				$query ='select actor from #__community_activities where id=' . $db->Quote($id);
				$db->setQuery($query);
				$actorId = $db->loadResult();
				if($actorId != $my->id)
				{
					$actor =& CFactory::getUser($actorId);
					CFactory::load( 'libraries' , 'notification' );
					$notification	= new CNotificationLibrary();
					
					$current = CRoute::emailLink( ActivityComments::getCurrent() , false ) . '#profile-newsfeed-item' . $id;
					$notification->add( 'profile.activity.comment' , $my->id , $actorId , JText::sprintf('SOMEONE COMMENTED MAIL SUBJECT' , $my->getDisplayName() ) , JText::sprintf('SOMEONE COMMENTED MAIL CONTENT',$actor->getDisplayName(),$my->getDisplayName() , $current ) );
				}
			}

				
			// send emails to subscribers
			$a = 'select * from #__activity_subscribe where '
				.'`activity_id`=' . $db->Quote( $id ) . ' '
				.'and `type`=' . $db->Quote( 'profile' );
			$db->setQuery($a);
			$subscribers = $db->loadObjectList();
			
			if( $subscribers )
			{
				$emails	= array();
				foreach($subscribers as $subscriber )
				{
					$emails[]	= $subscriber->user_id;
				}

				CFactory::load( 'libraries' , 'notification' );
				$notification	= new CNotificationLibrary();
				
				$current = CRoute::emailLink( ActivityComments::getCurrent() , false ) . '#profile-newsfeed-item' . $id;
				$notification->add( 'profile.activity.comment' , $my->id , $emails , JText::sprintf('SUBSCRIBE SOMEONE COMMENTED MAIL SUBJECT' , $my->getDisplayName() ) , JText::sprintf('SUBSCRIBE SOMEONE COMMENTED MAIL CONTENT', $my->getDisplayName() , $current ) );

			}
		$joomla	= JFactory::getConfig();

		$offset = $my->getParam( 'timezone' , $joomla->getValue('offset') );
		$config = CFactory::getConfig();
		
		$date->setOffset( $offset + $config->get('daylightsavingoffset') );
		ob_start();
	?>
		<div class="wallcmt small" id="activity-comment-item-<?php echo $wall->id;?>">
			<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id);?>"><img class="wall-coc-avatar" src="<?php echo $my->getThumbAvatar();?>"/></a>
			<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id);?>" class="wall-coc-author"><?php echo $my->getDisplayName();?></a> <?php echo JText::_('POST ON'); ?>
			<span class="wall-coc-date"><?php echo $date->toFormat(JText::_('DATE_FORMAT_LC2'));?></span>
					<?php
					if( ActivityComments::isSiteAdmin())
					{
					?>
					 | <span class="coc-remove"><a href="javascript:void(0);" onclick="jax.call('community','plugins,activitycomment,removecomment','<?php echo $wall->id;?>');"><?php echo JText::_('REMOVE COMMENT');?></a></span>
					<?php
					}
					?>
			<p><?php echo $wall->comment;?></p>
		</div>
	<?php
		$contents	= ob_get_contents();
		ob_end_clean();

		$closeComment	= $params->get('autoclose', 0 );
		
		if( $closeComment )
		{
			$response->addScriptCall('activityHideComment' , $id );
		}
		$response->addScriptCall( ActivityComments::getjs() .'("#activity-' . $id . '-comment-errors").html("' . JText::_('COMMENT ADDED') . '").css("color","green");' );
		$response->addScriptCall('activityInsertComment' , $id , $contents , $params->get('commentordering' , 'asc' ) );
		return $response->sendResponse();
	}
	
	function repost($response, $actId , $from = 'frontpage'){
		JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
		$my		= CFactory::getUser();

		if( $my->id == 0 )
		{
			$response->addScriptCall( 'alert' , 'NOT ALLOWED' );
			return $response->sendResponse();
		}
		
		$db		= JFactory::getDBO();
		$query	= 'SELECT * FROM #__community_activities WHERE `id`=' . $db->Quote( $actId );
		$db->setQuery( $query );
		$activity	= $db->loadObject();

		// Add activity logging
		CFactory::load ( 'libraries', 'activities' );

		$actor	= CFactory::getUser( $activity->actor );
		
		$act = new stdClass();
		
		$author	= '<a href="' . CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id ) . '">' . $my->getDisplayName() . '</a>';
		
		$act->cmd 		= 'activitycomment.repost';
		$act->actor 	= $my->id;
		$act->target 	= 0;
		$act->title		= JText::sprintf('%1$s,', $author ) . ' ' . $activity->title;
		$act->content	= $activity->content;
		$act->app		= $activity->app;
		$act->cid		= $my->id;
		CActivityStream::add( $act );

		$friendsModel	=& CFactory::getModel('friends');
		
		CFactory::load( 'helpers' , 'time');
		$memberSince	= cGetDate($my->registerDate);
		$friendIds		= $friendsModel->getFriendIds($my->id);

		include_once(JPATH_COMPONENT . DS.'libraries'.DS.'activities.php');
		$act 	= new CActivityStream();
		$config	= CFactory::getConfig();
		
		if( $from == 'frontpage')
		{
			
			$html	= $act->getHTML('', '', null, $config->get('maxacitivities'));
		}
		else
		{
			$html	= $act->getHTML($my->id, $friendIds, $memberSince, 10);
		}
					

		$response->addAssign('activity-stream-container' , 'innerHTML' , $html );
		return $response->sendResponse();
	}
}