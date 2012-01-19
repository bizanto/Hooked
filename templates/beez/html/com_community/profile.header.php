<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 **/
defined('_JEXEC') or die();
?>
<?php if( $isMine ): ?>
<script type="text/javascript" language="javascript">
joms.jQuery(document).ready(function(){
	
	var profileStatus = joms.jQuery('#profile-new-status');
	var statusText    = joms.jQuery('#statustext');
	var saveStatus    = joms.jQuery('#save-status');

	statusText.data('CC_PROFILE_STATUS_INSTRUCTION', '<?php echo addslashes(JText::_('CC PROFILE STATUS INSTRUCTION')); ?>')
	          .val(statusText.data('CC_PROFILE_STATUS_INSTRUCTION'));
	
	joms.utils.textAreaWidth(statusText);
	joms.utils.autogrow(statusText);

	statusText.focus(function()
	{
		if (profileStatus.hasClass('inactive'))
		{
			profileStatus.removeClass('inactive');
			statusText.val('');
		}
	}).blur(function()
	{
		if (statusText.val()=='')
		{
			setTimeout(function()
			{
				statusText.val(statusText.data('CC_PROFILE_STATUS_INSTRUCTION'));
				profileStatus.addClass('inactive');
			}, 200);
		}
	});

	saveStatus.click(function()
	{
		var newStatusText = statusText.val();
		jax.call('community', 'status,ajaxUpdate', statusText.val());

		statusText.val('').trigger('blur');
	});

	joms.profile.setStatusLimit( statusText );

});
</script>
<?php endif; ?>
	
	
	<!-- begin: .profile-box -->
	<div class="profile-box paper">
		<div class="profile-avatar">
		<?php if( $isMine ): ?><a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&task=edit'); ?>"><?php endif; ?><img class="prof-av" src="<?php echo $profile->largeAvatar; ?>" alt="<?php echo $this->escape( $user->getDisplayName() ); ?>" /><?php if( $isMine ): ?></a><?php endif; ?>
        
		<?php if($config->get('enablekarma')){ ?>
		<img src="<?php echo $karmaImgUrl; ?>" alt="" class="profile-karma" />
        <?php } ?>
        
		</div>
        
		
		<div class="profile-likes">
			<span id="like-container"><?php echo $likesHTML; ?></span>
		</div>

		<!-- Short Profile info -->
		<div class="profile-info">
			<div class="contentheading">
				<?php echo $user->getDisplayName(); ?>
			</div>

			<div id="profile-status">
				<span id="profile-status-message"><?php echo $profile->status; ?></span>
				<div class="small"><?php echo $profile->posted_on; ?></div>
			</div>

			<ul class="profile-details">
				<?php if( $config->get('enablevideos') && ($profile->profilevideo != 0 ) ){ ?>
                                <?php if( $config->get('enableprofilevideo') ){ ?>
				<li class="title"><?php echo JText::_('CC PROFILE VIDEO'); ?></li>
				<li class="video"><a class="icon-videos" onclick="joms.videos.playProfileVideo( <?php echo $profile->profilevideo; ?> , <?php echo $user->id; ?> )" href="javascript:void(0);"><?php echo JText::_('CC MY PROFILE VIDEO');?></a></li>
				<?php } ?>
				<?php } ?>
  				<li class="title"><?php echo JText::_('CC MEMBER SINCE'); ?></li>
				<li><?php echo JHTML::_('date', $registerDate , JText::_('DATE_FORMAT_LC2')); ?></li>
				
				<?php if( $multiprofile->name && $config->get('profile_multiprofile') ){ ?>
				<li class="title"><?php echo JText::_('CC PROFILE TYPE'); ?></li>
				<li><?php echo $multiprofile->name;?></li>
				<?php } ?>			    
			</ul>
		</div>
		
		<div style="clear: left;"></div>
	</div>
	<!-- end: .profile-box -->

	<div class="profile-toolbox-bl paper-light">
	    <div class="profile-toolbox-br">
	    	
			<!-- begin: .profile-toolbox-tl -->
	        <div class="profile-toolbox-tl">
	        
				<?php if( !$isMine ): ?>
				<ul class="small-button">
					<?php if(!$isFriend && !$isMine) { ?>
				    <li class="btn-add-friend">
						<a href="javascript:void(0)" onclick="joms.friends.connect('<?php echo $profile->id;?>')"><span><?php echo JText::_('CC ADD AS FRIEND'); ?></span></a>
					</li>
					<?php } ?>

					<?php if($config->get('enablephotos')): ?>
				    <li class="btn-gallery">
						<a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=myphotos&userid='.$profile->id); ?>">
							<span><?php echo JText::_('CC PHOTO GALLERY'); ?></span>
						</a>
					</li>
					<?php endif; ?>
	
					<?php if($showBlogLink): ?>
				    <li class="btn-blog">
						<a href="<?php echo JRoute::_('index.php?option=com_myblog&blogger=' . $user->getDisplayName() . '&Itemid=' . $blogItemId ); ?>">
							<span><?php echo JText::_('CC BLOG'); ?></span>
						</a>
					</li>
					<?php endif; ?>
									
					<?php if($config->get('enablevideos')): ?>
				    <li class="btn-videos">
						<a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&task=myvideos&userid='.$profile->id); ?>">
							<span><?php echo JText::_('CC VIDEOS GALLERY'); ?></span>
						</a>
					</li>
					<?php endif; ?>

					<?php if( !$isMine && $config->get('enablepm')): ?>
				    <li class="btn-write-message">
						<a onclick="<?php echo $sendMsg; ?>" href="javascript:void(0);">
							<span><?php echo JText::_('CC WRITE MESSAGE'); ?></span>
						</a>
					</li>
					<?php endif; ?>
                    <li class="mine">
                    	<a href="/index.php?option=com_jreviews&Itemid=222&url=my-listings/user:<?php echo $profile->id; ?>/tmpl_suffix:_topcatches/cat:14">
                        	<span>Fangstrapporter</span>
                        </a>
                    </li>
                    <li class="mine">
                    	<a href="/index.php?option=com_jreviews&Itemid=237&url=my-listings/user:<?php echo $profile->id; ?>/tmpl_suffix:_map_spots/section:1">
                        	<span>Fiskeplasser</span>
                        </a>
                    </li>
                    <li class="mine">
                    	<a href="/index.php?option=com_jreviews&Itemid=306&url=my-listings/user:<?php echo $profile->id; ?>/tmpl_suffix:_trip/cat:13/order:rjr_startdate">
                        	<span>Turrapporter</span>
                        </a>
                    </li>
                    <li class="mine">
                    	<a href="/index.php?option=com_jreviews&Itemid=310&url=my-listings/user:<?php echo $profile->id; ?>/tmpl_suffix:_map_hatch/cat:15/order:rjr_startdate">
                        	<span>Klekkerapporter</span>
                        </a>
                    </li>
				</ul>
				<div></div>
    			<?php else : ?>

			    <!-- begin: #profile-new-status -->
				<div id="profile-new-status" class="inactive">
					<label for="statustext"><?php echo JText::_('CC MY STATUS'); ?></label>
					<textarea name="statustext" id="statustext" class="status" rows="" cols="" maxlength="<?php echo $config->get('statusmaxchar');?>"></textarea>
					<button id="save-status" class="button"><?php echo JText::_('CC SAVE'); ?></button>
					<span id="profile-status-notice"><?php echo JText::sprintf('CC CHARACTERS LEFT' , $config->get('statusmaxchar') );?></span>
				</div>
				<!-- end: #profile-new-status -->
		        
		        <!-- begin: #profile-header -->
                <div id="profile-header" class="js-box-grey">
					<ul class="actions">
						<li class="profile">
	                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&task=edit'); ?>">
	                            <span><?php echo JText::_('CC EDIT PROFILE'); ?></span>
	                        </a>
						</li>
						<li class="avatar">
	                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&task=uploadAvatar'); ?>">
	                            <span><?php echo JText::_('CC EDIT AVATAR'); ?></span>
	                        </a>
						</li>
                        <?php if($config->get('enablevideos')){ ?>
							<li class="video">
		                        <a href="javascript:void(0);" onclick="joms.videos.addVideo();">
		                            <span><?php echo JText::_('CC ADD VIDEO'); ?></span>
		                        </a>
							</li>
						<?php } ?>
                        
						<?php if($config->get('enablephotos')){ ?>
							<li class="photo">
		                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=uploader&userid='.$profile->id); ?>">
		                            <span><?php echo JText::_('CC UPLOAD PHOTOS'); ?></span>
		                        </a>
							</li>
						<?php } ?>
						<li class="privacy">
	                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&task=privacy'); ?>">
	                            <span><?php echo JText::_('CC EDIT PRIVACY'); ?></span>
	                        </a>
						</li>
					</ul>

					<ul class="actions">
                        <?php if( $config->get('enablepm')){ ?>
						<li class="write">
	                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=inbox&task=write'); ?>">
	                            <span><?php echo JText::_('CC WRITE MESSAGE'); ?></span>
	                        </a>
						</li>
						<li class="inbox">
	                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=inbox'); ?>">
	                            <span><?php echo JText::_('CC VIEW YOUR INBOX'); ?></span>
	                        </a>
						</li>
						<?php } ?>
						<li class="invite">
	                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=friends&task=invite'); ?>">
	                            <span><?php echo JText::_('CC INVITE FRIENDS'); ?></span>
	                        </a>
						</li>
                        <?php if($config->get('creategroups')): ?>
						<li class="group">
	                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=create'); ?>">
	                            <span><?php echo JText::_('CC ADD GROUP'); ?></span>
	                        </a>
						</li>
						<?php endif;?>
						<?php if($config->get('createevents') && $config->get('enableevents')){ ?>
							<li class="events">
		                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=create'); ?>">
		                            <span><?php echo JText::_('CC ADD EVENT'); ?></span>
		                        </a>
							</li>
						<?php } ?>
					</ul>

	                <ul class="actions">
                        	<li class="profile catchreport">
                            	<a href="index.php?option=com_relate&view=create&type=catch">
                                	<span><?php echo JText::_('Legg til en fangstrapport'); ?></span>
                                </a>
                            </li>
                            <li class="profile fishingspot">
                            	<a href="index.php?option=com_relate&view=create&type=catch">
                                	<span><?php echo JText::_('Legg til en fiskeplass'); ?></span>
                                </a>
                            </li>
                            <li class="profile tripreport">
                            	<a href="index.php?option=com_relate&view=create&type=trip">
                                	<span><?php echo JText::_('Legg til en turrapport'); ?></span>
                                </a>
                            </li>
                            <li class="profile hatchreport">
                            	<a href="index.php?option=com_relate&view=create&type=hatch">
                                	<span><?php echo JText::_('Legg til en klekkerapport'); ?></span>
                                </a>
                            </li>
					</ul>

					<div style="clear: left; margin-bottom: 10px;"></div>
				</div>
				<!-- end: #profile-header -->
				<?php endif; ?>

				</div>
				<!-- end: .profile-toolbox-tl -->
	
			</div>
		</div>
      