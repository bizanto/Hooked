<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license http://www.azrul.com Copyrighted Commercial Software
 * 
 */
defined('_JEXEC') or die();
JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
?>
<link rel="stylesheet" href="<?php echo JURI::root();?>plugins/community/activitycomment/style.css" type="text/css" />
<script type="text/javascript" src="<?php echo JURI::root();?>plugins/community/activitycomment/activitycomment.js"></script>
<?php foreach($activities as $act): ?>
<?php if($act->type =='title'): ?>
<div class="ctitle" style="font-weight: bold;"><?php echo $act->title; ?></div>
<?php else: $actor =& CFactory::getUser($act->actor); ?>
<div id="<?php echo $idprefix; ?>profile-newsfeed-item<?php echo $act->id; ?>" class="newsfeed-item">
	<table style="width: 98%; overflow: hidden;"><tr>
		<?php
		if($config->get('showactivityavatar'))
		{
		?>
		<td style="width:20px;text-align: left; vertical-align: top;">
			<?php
			if(!empty($actor->id))
			{
			?>
				<a href="<?php echo cUserLink($actor->id); ?>"><img class="avatar" src="<?php echo $actor->getThumbAvatar(); ?>" width="36" border="0" alt=""/></a>
			<?php
			}
			else
			{
			?>
				<img class="avatar" src="<?php echo $actor->getThumbAvatar(); ?>" width="36" border="0" alt=""/>
			<?php
			}
			?>
		</td>
		<?php
		}
		?>
		<td style="width:16px;vertical-align: top;">
			<img src="<?php echo $act->favicon; ?>" class="icon" alt=""/>
		</td>
		<td style="width:70%; text-align: left; vertical-align: top;">
			<?php echo $act->title; ?>
			<?php
			$o = JRequest::getVar( 'option' );
			if($o == 'com_community' || $o == 'community')
			{
			?>
			<div>
			<?php
			if(!empty($act->content) && $showMore )
			{
			?>
			<span id="<?php echo $idprefix; ?>profile-newsfeed-item-content-<?php echo $act->id;?>" class="small profile-newsfeed-item-action"><a href="javascript:void(0);" id="newsfeed-content-<?php echo $act->id;?>" onclick="joms.activities.getContent('<?php echo $act->id;?>');"><?php echo JText::_('CC MORE');?></a></span>
			<?php
			}
		
			require_once( JPATH_PLUGINS.DS.'community'.DS.'activitycomment'.DS.'helper.php');
			$my =& CFactory::getUser();
			$rows = ActivityComments::getComments($act->id);
			$likes= ActivityComments::getLikes($act->id);
			?>
			<?php
			if($my->id != 0)
			{
			$params = ActivityComments::getParams();
			
			$showLikeIcon 		= ( $params->get('uselikeicon' , 1 ) == 1 )? '' : ' noicon';
			$showCommentIcon 	= ( $params->get('usecommenticon' , 1 ) == 1 )? '' : ' noicon';
				if($showMore && !empty($act->content) )
				{
					echo ' | ';
				}
			?>
			<span class="small"><a class="activity-comments<?php echo $showCommentIcon;?>" href="javascript:void(0);" onclick="activityShowComment('<?php echo $act->id;?>');"><?php echo JText::_('ADD COMMENT');?></a></span> | 
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
			}
			?>
			</div>
			<div id="likes-holder-<?php echo $act->id;?>" class="small" style="padding-left: 5px;">
			<?php
			if($likes)
			{
			?>
				<?php
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
				<div class="wallcmt small" id="activity-comment-item-<?php echo $row->id;?>">
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
					<button class="wall-coc-form-action add button" onclick="jax.call('community','plugins,activitycomment,savecomment', '<?php echo $act->id;?>' , jQuery('#commentval-<?php echo $act->id;?>').val() );return false"><?php echo JText::_('ADD COMMENT');?></button>
					<button class="wall-coc-form-action cancel button" onclick="activityHideComment('<?php echo $act->id;?>');return false"><?php echo JText::_('CANCEL COMMENT');?></button>
				</div>
				</form>
			</div>
			<?php
			}
			}
			?>
		</td>
		
		<td style="text-align: right;vertical-align:top;white-space:nowrap;">
			<?php echo $act->created; ?>
		</td>
		
		
		<?php if($isMine): ?>
		<td width="20">
			<a class="remove" onclick="jax.call('community', 'activities,ajaxHideActivity' , '<?php echo $my->id; ?>' , '<?php echo $act->id; ?>');" href="javascript:void(0);">
				<?php echo JText::_('CC HIDE');?>
			</a>
		</td>
		<?php endif; ?>
	</tr></table>
</div>
<?php endif; ?>
<?php endforeach; ?>
