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
JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
require_once( JPATH_PLUGINS.DS.'community'.DS.'activitycomment'.DS.'helper.php');

$o = JRequest::getVar( 'option' );

if($o == 'com_community' || $o == 'community')
{
?>
	<div>
	<?php
		$model = CFactory::getModel( 'Friends' );
		CFactory::load( 'helpers' , 'owner' );
		$my =& CFactory::getUser();
		$rows = ActivityComments::getComments($act->id);
		$likes= ActivityComments::getLikes($act->id);
		
		$result	= $model->getFriends( $act->actor );
		$friends = array();
		foreach($result as $f )
		{
			$friends[]	= $f->id;
		}
		$friends[]	= $act->actor;
		$params	= ActivityComments::getParams();
		
		if($my->id != 0 && ( ( $params->get('respect_privacy' , 0 ) && in_array($my->id, $friends ) ) || $params->get('respect_privacy', 0) == 0 || COwnerHelper::isCommunityAdmin() ) )
		{
			$params = ActivityComments::getParams();
			$showLikeIcon 		= ( $params->get('uselikeicon' , 1 ) == 1 )? '' : ' noicon';
			$showCommentIcon 	= ( $params->get('usecommenticon' , 1 ) == 1 )? '' : ' noicon';
			$showSubscribeIcon	= ( $params->get('usesubscribeicon' , 1 ) == 1 )? '' : ' noicon';
			$showRepostIcon		= ( $params->get('usereposticon' , 1 ) == 1 )? '' : ' noicon';
	?>
	<span class="small">
	<?php
			if($showMore && !empty($act->content) && !$config->getBool('showactivitycontent') )
			{
				echo ' | ';
			}
	?>
	<a class="activity-comments<?php echo $showCommentIcon;?>" href="javascript:void(0);" onclick="activityShowComment('<?php echo $act->id;?>');"><?php echo JText::_('ADD COMMENT');?></a> | </span>
	<?php
			$showLike	= 'display:none;';
			$showUnlike	= 'display:none;';

			if( ActivityComments::hasLike($act->id) )
			{
				$showUnlike = 'display:inline;';
			}
			else
			{	
				$showLike = 'display:inline;';
			}
	?>
	<span class="small" id="activity-unlike-<?php echo $act->id;?>" style="<?php echo $showUnlike;?>"><a class="activity-unlike<?php echo $showLikeIcon;?>" href="javascript:void(0);" onclick="activityUnlikeItem('<?php echo $act->id;?>','<?php echo $my->id;?>');"><?php echo JText::_('UNLIKE ITEM');?></a></span>
	<span class="small" id="activity-like-<?php echo $act->id;?>" style="<?php echo $showLike;?>"><a class="activity-like<?php echo $showLikeIcon;?>" href="javascript:void(0);" onclick="activityLikeItem('<?php echo $act->id;?>','<?php echo $my->id;?>');"><?php echo JText::_('LIKE ITEM');?></a></span>
	<?php
	switch($act->app)
	{
			case 'groups':
			if( $params->get('joingroup' , 1) )
			{
				$activity	= ActivityComments::getAct($act->id);
				
				if( !empty($activity->content) )
				{		
			?>
			<span class="small">| <a class="activity-groups<?php echo $showCommentIcon;?>" href="javascript:void(0);" onclick="javascript:joms.groups.joinWindow('<?php echo $activity->cid;?>');"><?php echo JText::_('CC JOIN GROUP');?></a></span>
			<?php
				}
			}
			break;
			case 'friends':
			if( $params->get('addfriend' , 1) )
			{
				$activity	= ActivityComments::getAct($act->id);
				$actor = $activity->actor;
				$target = $activity->target;
				
				if(!ActivityComments::isFriends($actor) )
				{
					$usr = CFactory::getUser($actor);
				?>
				<span class="small">| <a class="activity-groups<?php echo $showCommentIcon;?>" href="javascript:void(0);" onclick="javascript:joms.friends.connect('<?php echo $activity->actor;?>');"><?php echo JText::sprintf('ADD AS FRIEND' , $usr->getDisplayName() );?></a></span>
				<?php
				}

				if(!ActivityComments::isFriends($target) )
				{
					$usr = CFactory::getUser($target);
				?>
				<span class="small">| <a class="activity-groups<?php echo $showCommentIcon;?>" href="javascript:void(0);" onclick="javascript:joms.friends.connect('<?php echo $activity->target;?>');"><?php echo JText::sprintf('ADD AS FRIEND' , $usr->getDisplayName() );?></a></span>
				<?php
				}
			}
			break;

		}
	
		if( $my->id != 0 && $params->get('enablesubscribe', 1 ))
		{
			$showSubscribe	= 'none;';
			$showUnsubscribe = 'none;';
			
			if( ActivityComments::subscribed($act->id) )
			{
				$showUnsubscribe = 'inline;';
			}
			else
			{
				$showSubscribe	= 'inline;';
			}
	?>
	<span class="small" id="activity-unsubscribe-<?php echo $act->id;?>" style="display:<?php echo $showUnsubscribe;?>">| <a class="activity-unsubscribe<?php echo $showSubscribeIcon;?>" href="javascript:void(0);" onclick="activityUnsubscribe('<?php echo $act->id;?>');"><?php echo JText::_('UNSUBSCRIBE');?></a></span>
	<span class="small" id="activity-subscribe-<?php echo $act->id;?>" style="display:<?php echo $showSubscribe;?>">| <a class="activity-subscribe<?php echo $showSubscribeIcon;?>" href="javascript:void(0);" onclick="activitySubscribe('<?php echo $act->id;?>');"><?php echo JText::_('SUBSCRIBE');?></a></span>
	<?php
		}

		if( $my->id != 0 && $params->get('enablerepost', 1 )  && $act->app == 'activitycomment.url' && $my->id != $act->actor )
		{
			$view	= JRequest::getVar( 'view' , 'frontpage');
	?>
	<span class="small" id="activity-repost-<?php echo $act->id;?>">| <a class="activity-repost<?php echo $showRepostIcon;?>" href="javascript:void(0);" onclick="activityRepost('<?php echo $act->id;?>','<?php echo $view;?>');"><?php echo JText::_('REPOST');?></a></span>
	<?php
		}
	}
	?>
	</div>
	
	<div id="likes-holder-<?php echo $act->id;?>" class="small<?php echo ($likes) ? ' likes-content' : '';?>" style="padding-left: 5px;">
	<?php
		if($likes)
		{
			$total_likes= count($likes);
			$i=0;
			$my=&CFactory::getUser();
			
			foreach($likes as $like)
			{
					$i++;
					$likeuser =& CFactory::getUser($like->userid);
					$name = $likeuser->getDisplayName();
					
					if($likeuser->id == $my->id)
						$name = JText::_('You');

					if( $i == 1 )
					{
						$comma	= '';
					}
					elseif( $i == $total_likes )
					{
						$comma	= ' ' . JText::_('AND') . ' ';
					}
					else
					{
						$comma	= ' , ';
					}
				?>
					<span id="like-<?php echo $act->id;?>-<?php echo $likeuser->id;?>"><?php echo $comma;?><a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $likeuser->id);?>"><?php echo $name;?></a></span>
				<?php
				}
				
				if($total_likes == 1 && $likes[0]->userid == $my->id || $i > 1 )
				{
				?>
				<span><?php echo JText::_('LIKE THIS');?></span>
				<?php
				}
				else
				{
				?>
				<span><?php echo JText::_('LIKES THIS');?></span>
				<?php
				}
				?>
			<?php
			}
			?>
			</div>
			<div id="comment-holder-<?php echo $act->id;?>">
			<?php
			if($rows)
			{
				$params	= ActivityComments::getParams();
				foreach($rows as $row)
				{
					$user =& CFactory::getUser($row->post_by);
					$date =& JFactory::getDate( $row->date );
				?>
				<div class="wallcmt small wall-content" id="activity-comment-item-<?php echo $row->id;?>">
					<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id);?>"><img class="wall-coc-avatar" src="<?php echo $user->getThumbAvatar();?>"/></a>
					<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $user->id);?>" class="wall-coc-author"><?php echo $user->getDisplayName();?></a> <?php echo JText::_('POST ON'); ?>
					<span class="wall-coc-date"><?php echo $date->toFormat(JText::_('DATE_FORMAT_LC2'));?></span>
					<?php
					if( ActivityComments::isSiteAdmin() || $my->id == $act->actor )
					{
					?>
					 | <span class="coc-remove"><a href="javascript:void(0);" onclick="jax.call('community','plugins,activitycomment,removecomment','<?php echo $row->id;?>');"><?php echo JText::_('REMOVE COMMENT');?></a></span>
					<?php
					}
					?>
					<p><?php echo $row->comment;?></p>
				</div>
				<?php
				}
				if( ActivityComments::hasMoreComments($act->id) )
				{
				?>
				<div class="wallcmt small" id="activity-comment-more-<?php echo $act->id;?>">
					<div><a href="javascript:void(0);" onclick="activityMoreComments('<?php echo $act->id;?>');"><?php echo JText::_('MORE COMMENTS');?></a></div>
				</div>
				<?php
				}
			}
			?>
			</div>
			<?php 
			if($my->id != 0)
			{
			?>
			<div id="activity-<?php echo $act->id;?>-comment" style="display:none;" class="small wallcmt">
				<div id="activity-<?php echo $act->id;?>-comment-errors"></div>
				<form><textarea name="commentval-<?php echo $act->id;?>" id="commentval-<?php echo $act->id;?>" style="height:40px;width: 95%;"></textarea>
				<div class="wall-coc-form-actions">
					<button class="wall-coc-form-action add button" onclick="jax.call('community','plugins,activitycomment,savecomment', '<?php echo $act->id;?>' , joms.jQuery('#commentval-<?php echo $act->id;?>').val() );return false"><?php echo JText::_('ADD COMMENT');?></button>
					<button class="wall-coc-form-action cancel button" onclick="activityHideComment('<?php echo $act->id;?>');return false"><?php echo JText::_('CANCEL COMMENT');?></button>
				</div>
				</form>
			</div>
<?php
			}
}
?>