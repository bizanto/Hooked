<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @params	isMine		boolean is this group belong to me
 * @params	members		An array of member objects 
 */
defined('_JEXEC') or die();
?>
<?php if( $guests ) { ?>
<?php	foreach( $guests as $guest ){ ?>	
	<div class="mini-profile" id="member_<?php echo $guest->id;?>">
		<div class="mini-profile-avatar">
			<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $guest->id); ?>"><img class="avatar" src="<?php echo $guest->getThumbAvatar(); ?>" alt="<?php echo $guest->getDisplayName(); ?>" /></a>
		</div>
		<div class="mini-profile-details">
			<h3 class="name">
				<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $guest->id); ?>"><strong><?php echo $guest->getDisplayName(); ?></strong></a>
			</h3>
			<div class="jsAbs jsFriendRespond">				
				<?php if( $handler->manageable() ){ ?>
				<input type="submit" value="<?php echo JText::_('CC REMOVE');?>" onclick="joms.events.confirmRemoveGuest('<?php echo $guest->id; ?>','<?php echo $eventid;?>');" style="margin: 0pt;" class="button">
				<?php } ?>
			</div>
			<div class="mini-profile-details-status" style="padding-bottom:30px"><?php echo $guest->getStatus() ;?></div>
			<div class="mini-profile-details-action jsAbs jsFriendAction">
				<span class="icon-group"><a href="<?php echo CRoute::_('index.php?option=com_community&view=friends&userid=' . $guest->id );?>"><?php echo JText::sprintf( (CStringHelper::isPlural($guest->friendsCount)) ? 'CC FRIENDS COUNT MANY' : 'CC FRIENDS COUNT' , $guest->friendsCount);?></a></span>
				<?php if( $my->id != $guest->id && $config->get('enablepm') ){ ?>
		        <span class="icon-write">
		            <a onclick="joms.messaging.loadComposeWindow(<?php echo $guest->id; ?>)" href="javascript:void(0);">
		            <?php echo JText::_('CC WRITE MESSAGE'); ?>
		            </a>
		        </span>
		        <?php } ?>
		        
			    <?php if( ($guest->statusType== COMMUNITY_EVENT_STATUS_REQUESTINVITE) && $handler->manageable() ){ ?>
			    <span class="icon-approve" id="events-approve-<?php echo $guest->id;?>">
			    	<a href="javascript:void(0);" onclick="jax.call('community','events,ajaxApproveInvite', '<?php echo $guest->id;?>' , '<?php echo $eventid;?>');"><?php echo JText::_('CC APPROVE'); ?></a>
			    </span>
			    <?php } ?>
			    
			    <?php
			    	if( $handler->manageable() && !$guest->isMe && $guest->statusType == COMMUNITY_EVENT_STATUS_ATTEND && !$guest->isAdmin )
					{
				?>
				    <span class="icon-user">
				    	<a href="javascript:void(0);" onclick="jax.call('community','events,ajaxAddAdmin','<?php echo $guest->id;?>','<?php echo $eventid;?>');">
							<?php echo JText::_('CC SET AS EVENT ADMIN'); ?>
						</a>
				    </span>
			    <?php } else if( $handler->manageable() && !$guest->isMe && !$guest->isAdmin ) { ?>
				    <span class="icon-user">
				    	<a href="javascript:void(0);" onclick="jax.call('community','events,ajaxRemoveAdmin','<?php echo $guest->id;?>','<?php echo $eventid;?>');"><?php echo JText::_('CC REVERT EVENT ADMIN'); ?></a>
				    </span>
			    <?php
					}
				?>
				<?php if (!$guest->isMe && ($handler->manageable() || $event->isAdmin($my->id)) ){ ?>
					<?php if($guest->statusType == COMMUNITY_EVENT_STATUS_BLOCKED){ ?>
					<span class="icon-user">
						<a href="javascript:void(0);" onclick="joms.events.confirmUnblockGuest('<?php echo $guest->id;?>','<?php echo $eventid;?>');"><?php echo JText::_('CC EVENT UNBLOCK GUEST'); ?></a>
					</span>
					<?php } ?>
				<?php } ?>
			</div>
			<?php if($guest->isOnline()){ ?>
				<span class="icon-online-overlay"><?php echo JText::_('CC ONLINE'); ?></span>
		    <?php } ?>
		</div>
		<div class="clr"></div>
	</div>
	<?php } ?>
	<div class="pagination-container">
		<?php echo $pagination->getPagesLinks(); ?>
	</div>
<?php } else { ?>
<div class="community-empty-list"><?php echo JText::_('CC NO EVENT USERS'); ?></div>
<?php } ?>