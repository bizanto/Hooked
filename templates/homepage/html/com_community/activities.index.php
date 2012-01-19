<?php
/**
 * @packageJomSocial
 * @subpackage Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
?>
<?php foreach($activities as $act): ?>
	<?php if($act->type =='title'): ?>
		<?php if($config->get('activitydateformat') == COMMUNITY_DATE_FIXED ){ ?>
			<div class="ctitle"><?php echo $act->title; ?></div>
		<?php } ?>
	<?php else: $actor = CFactory::getUser($act->actor); ?>
		<div id="<?php echo $idprefix; ?>profile-newsfeed-item<?php echo $act->id; ?>" class="joms-newsfeed-item <?php if($isMine) { echo 'isMine'; } ?> <?php if($isSuperAdmin && !$isMine) { echo 'isSuperAdmin'; } ?> <?php if(!$config->get('showactivityavatar')) { echo 'no-avatar'; } ?>">
	    	<!--NEWS FEED AVATAR-->
			<div class="newsfeed-avatar">
			<?php if($config->get('showactivityavatar')) { ?>
				<?php if(!empty($actor->id)) { ?>
					<a href="<?php echo CUrlHelper::userLink($actor->id); ?>"><img class="avatar" src="<?php echo $actor->getThumbAvatar(); ?>" width="36" border="0" alt=""/></a>
				<?php } else { ?>
					<img class="avatar" src="<?php echo $actor->getThumbAvatar(); ?>" width="36" border="0" alt=""/>
				<?php } ?>
			<?php } ?>
			</div>
	    	<!--NEWS FEED AVATAR-->
	
			<!--NEWS FEED FAVICON-->
			<? /*
			<div class="newsfeed-favicon"><img src="<?php echo $act->favicon; ?>" class="icon" alt="<?php echo $act->app;?>" /></div>
			*/ ?>
			<!--NEWS FEED FAVICON-->
	
			<!--NEWS FEED CONTENT-->
	    	<div class="newsfeed-content">
				<div class="newsfeed-content-top"><?php echo $act->title; ?></div>
				<?php if(!empty($act->content) && $showMore ){ ?>
					<?php if( $config->getBool('showactivitycontent')) { ?>
						<div id="<?php echo $idprefix; ?>profile-newsfeed-item-content-<?php echo $act->id;?>" class="newsfeed-content-hidden" style="display:block"><?php echo $act->content; ?></div>
					<?php } else { ?>
						<div id="<?php echo $idprefix; ?>profile-newsfeed-item-content-<?php echo $act->id;?>" class="small profile-newsfeed-item-action" style="display:block">
							<a href="javascript:void(0);" id="newsfeed-content-<?php echo $act->id;?>" onclick="joms.activities.getContent('<?php echo $act->id;?>');"><?php echo JText::_('CC MORE');?></a>
						</div>
					<?php } ?>
				<?php } ?>
			</div>
			<!--NEWS FEED CONTENT-->
	
			<!--NEWS FEED DATE-->
			<div class="newsfeed-date small"><?php echo $act->created; ?></div>
			<!--NEWS FEED DATE-->
	
			<!--NEWS FEED REMOVE-->
			<?php if($isMine): ?>
				<div class="newsfeed-remove"><a class="remove" onclick="jax.call('community', 'activities,ajaxHideActivity' , '<?php echo $my->id; ?>' , '<?php echo $act->id; ?>');" href="javascript:void(0);"><?php echo JText::_('CC HIDE');?></a></div>
			<?php endif; ?>
			<!--NEWS FEED REMOVE-->
	    
		    <!--NEWS FEED DELETE-->
		    <?php if($isSuperAdmin && !$isMine): ?>
		    <div class="newsfeed-remove">
		    <a class="remove" onclick="jax.call('community', 'activities,ajaxDeleteActivity' , '<?php echo $act->app; ?>' , '<?php echo $act->id; ?>');" href="javascript:void(0);">
				<?php echo JText::_('CC HIDE');?>
		    </a>
		    </div>
		    <?php endif; ?>
		    <!--NEWS FEED DELETE-->
		</div>
	<?php endif; ?>
<?php endforeach; ?>
<?php if( $exclusions !== false && $showMoreActivity) { ?>
	<div class="joms-newsfeed-more" id="activity-more">
		<a class="more-activity-text" href="javascript:void(0);" onclick="joms.activities.more();"><?php echo JText::_('CC MORE');?></a>
		<div class="loading"></div>
	</div>
	<input type="hidden" id="activity-exclusions" value="<?php echo $exclusions;?>" />
<?php } ?>

