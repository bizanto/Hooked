<?php
/**
 * @package		JomSocial
 * @subpackage 	Template
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 *
 * @params	isMine		boolean is this group belong to me
 * @params	categories	Array	An array of categories object
 * @params	members		Array	An array of members object
 * @params	group		Group	A group object that has the property of a group
 * @params	wallForm	string A html data that will output the walls form.
 * @params	wallContent string A html data that will output the walls data.
 **/
 
defined('_JEXEC') or die();
?>
<div class="group">
<div class="page-actions">
    <?php echo $reportHTML;?>
    <?php echo $bookmarksHTML;?>
</div>

<!-- begin: .cLayout -->
<div class="cLayout clrfix">

	<!-- begin: .cSidebar -->
	<div class="cSidebar clrfix">

	<?php $this->renderModules( 'js_side_top' ); ?>
	<?php $this->renderModules( 'js_groups_side_top' ); ?>
	
	<!-- Group Menu -->
	<div id="community-group-action" class="app-box">
		<?php if( ($isMember && !$isBanned) || ((!$isMember && !$isBanned) && !$waitingApproval) || $isMine || $isAdmin || $isSuperAdmin ) { ?>
		<div class="app-box-header">
			<h2 class="app-box-title"><?php echo JText::_('CC GROUP OPTION'); ?></h2>
		</div>

		<div class="app-box-content">
		<ul class="group-menus cResetList clrfix">								
    	<?php if( $isMine || $isCommunityAdmin ) { ?>
    		
            <!-- Edit Group Avatar -->
            <li>
            <a class="group-edit-avatar" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=uploadavatar&groupid=' . $group->id );?>">
                <?php echo JText::_('CC EDIT GROUP AVATAR');?>
            </a>
            </li>
            
            <!-- Edit Group -->
            <li>
            <a class="group-edit-info" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=edit&groupid=' . $group->id );?>">
                <?php echo JText::_('CC TAB EDIT');?>
            </a>
            </li>
    	<?php } ?>

                    	
    	<?php if( $isAdmin || $isCommunityAdmin){ ?>
    		<li>
    			<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=sendmail&groupid=' . $group->id );?>" class="community-invite-email"><?php echo JText::_('CC GROUP SENDMAIL');?></a>
    		</li>
		<?php } ?>
						
    	<?php if( $isAdmin || $isSuperAdmin ): ?>
        	<!-- Add Bulletin -->
            <li>
            <a class="group-add-bulletin" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=addnews&groupid=' . $group->id );?>">
                <?php echo JText::_('CC ADD BULLETIN');?>
            </a>
            </li>
    	<?php endif; ?>
    	
    	<?php if( ($isMember && !$isBanned) && !($waitingApproval) || $isSuperAdmin): ?>
        	<!-- Add Discussion -->
        	<li>
        	<a class="group-add-discussion" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&groupid=' . $group->id . '&task=adddiscussion');?>">
        	   <?php echo JText::_('CC ADD DISCUSSION');?>
        	</a>
        	</li>
    	<?php endif; ?>
    	
    	<?php
    	if( $allowManagePhotos  && $config->get('groupphotos') && $config->get('enablephotos') )
    	{
    	if( $albums )
    	{
    	?>
        	<!-- Add Photo -->
            <li>
            <a class="group-add-photo" href="<?php echo CRoute::_('index.php?option=com_community&view=photos&groupid=' . $group->id . '&task=uploader');?>">
                <?php echo JText::_('CC UPLOAD PHOTOS');?>
            </a>
            </li>
    	<?php } ?>
    	<!-- Add Album -->
        <li>
        <a class="group-add-album" href="<?php echo CRoute::_('index.php?option=com_community&view=photos&groupid=' . $group->id . '&task=newalbum');?>">
            <?php echo JText::_('CC CREATE ALBUM BUTTON');?>
        </a>
        </li>
    	<?php } ?>
    	
    	<?php
    	if( $allowManageVideos && $config->get('groupvideos') && $config->get('enablevideos') )
    	{
    	?>
        	<!-- Add Video -->
        	<li>
        	<a class="group-add-video" href="javascript:void(0)" onclick="joms.videos.addVideo('<?php echo VIDEO_GROUP_TYPE; ?>', '<?php echo $group->id; ?>')">
        	   <?php echo JText::_('CC ADD VIDEO');?>
        	</a>
        	</li>
    	<?php 
    	}
    	?>
    	
    	<?php if( $isCommunityAdmin ) { ?>
        	<!-- Unpublish Group -->
        	<li>
        	<a class="group-unpublish" href="javascript:void(0);" onclick="javascript:joms.groups.unpublish('<?php echo $group->id;?>');">
                <?php echo JText::_('CC UNPUBLISH GROUP'); ?>
            </a>
        	</li>
    	<?php } ?>    	
    	    	
    	<?php if( (!$isMember && !$isBanned) && !($waitingApproval) ) { ?>
        	<!-- Join Group -->
        	<li>
        	<a class="group-join" href="javascript:void(0);" onclick="javascript:joms.groups.joinWindow('<?php echo $group->id;?>');">
                <?php echo JText::_('CC TAB JOIN GROUP'); ?>
            </a>
        	</li>
    	<?php } ?>
    	
    	<?php if( ($isAdmin) || ($isMine) || ($isMember && !$isBanned) ) { ?>
        	<!-- Invite Friend -->            
        	<li>
				<?php echo $inviteHTML;?>
        	</li>
    	<?php } ?>
    	
    	<?php if( ($isMember && !$isBanned) && (!$isMine) && !($waitingApproval) && (COwnerHelper::isRegisteredUser()) ) { ?>
        	<!-- Leave Group -->            
        	<li>
        	<a class="group-leave" href="javascript:void(0);" onclick="joms.groups.leave('<?php echo $group->id;?>');">
                <?php echo JText::_('CC TAB LEAVE GROUP');?>
            </a>
        	</li>
    	<?php } ?>

	<?php if( $isCommunityAdmin || ($isMine)) { ?>
        	<!-- Delete Group -->
        	<li class="important">
        	<a class="group-delete" href="javascript:void(0);" onclick="javascript:joms.groups.deleteGroup('<?php echo $group->id;?>');">
                <?php echo JText::_('CC DELETE GROUP'); ?>
            </a>
        	</li>
    	<?php } ?>
    	</ul>
	</div>
	<div style="clear: right;"></div>
	<?php } else {?>
	<?php echo JText::_('CC GROUP OPTION BANNED'); ?>
	<?php } ?>
	</div>
	<!-- Group Menu -->
			
	<?php if( $group->approvals=='0' || $isMine || ($isMember && !$isBanned) || $isCommunityAdmin ) { ?>
	<!-- Group Admins -->
	<div id="community-group-admins" class="app-box">
		<div class="app-box-header">
			<h2 class="app-box-title"><?php echo JText::sprintf('CC ADMINS'); ?></h2>
		</div>
		
		<div class="app-box-content">
			<ul class="cThumbList clrfix">
			<?php
			if($admins) {
					foreach($admins as $row) {
			?>
					<li>
							<a href="<?php echo CUrlHelper::userLink($row->id); ?>">
									<img border="0" height="45" width="45" class="avatar jomTips" src="<?php echo $row->getThumbAvatar(); ?>" title="<?php echo cAvatarTooltip($row);?>" alt="<?php echo $row->getDisplayName();?>" />
							</a>
					</li>
			<?php
					}
			}
			?>
			</ul>
		</div>
		<div class="app-box-footer">
		<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewmembers&list=' . COMMUNITY_GROUP_ADMIN . '&groupid=' . $group->id);?>">
				<?php echo JText::_('CC SHOW ALL');?> (<?php echo $adminsCount; ?>)
		</a>
		</div>
	</div>
	<!-- Group Admins -->
	<?php } ?>
		
	<?php if( $group->approvals=='0' || $isMine || ($isMember && !$isBanned) || $isCommunityAdmin ) { ?>
	<!-- Group Members -->
	<div id="community-group-members" class="app-box">
		<div class="app-box-header">
			<h2 class="app-box-title"><?php echo JText::sprintf('CC MEMBERS'); ?></h2>
		</div>
	
			<div class="app-box-content">
					<ul class="cThumbList clrfix">
					<?php
					if($members) {
							foreach($members as $member) {
					?>
							<li>
									<a href="<?php echo CUrlHelper::userLink($member->id); ?>">
											<img border="0" height="45" width="45" class="avatar jomTips" src="<?php echo $member->getThumbAvatar(); ?>" title="<?php echo cAvatarTooltip($member);?>" alt="" />
									</a>
							</li>
					<?php
							}
					}
					?>
					</ul>
			</div>
			<div class="app-box-footer">
					<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewmembers&groupid=' . $group->id);?>">
							<?php echo JText::_('CC SHOW ALL');?> (<?php echo $membersCount; ?>)
					</a>
			</div>
	</div>
	<!-- Group Members -->
	<?php } // if( $group->approvals == '0' || $isMine || $isMember || $isCommunityAdmin ) ?>
			
			
	<!-- Group Photo @ Sidebar -->
	<?php if($config->get('enablephotos') && $config->get('groupphotos') && $showPhotos){ ?>
	<?php if($this->params->get('groupsPhotosPosition') == 'js_groups_side_bottom'){ ?>
	<div id="community-group-side-photos" class="app-box">
			<div class="app-box-header">
				<h2 class="app-box-title"><?php echo JText::_('CC ALBUMS');?></h2>
			</div>
	
			<div class="app-box-content">
			<?php if( $albums ) { ?>			
			<div class="clrfix">
				<?php foreach($albums as $album ) { ?>
				<a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&groupid=' . $group->id);?>">
				<img class="avatar jomTips" title="<?php echo $this->escape($album->name);?>::<?php echo $this->escape( $album->description );?>" src="<?php echo $album->thumbnail;?>" alt="<?php echo $album->thumbnail;?>" />
				</a>
				<?php } ?>
				</div>
				
				<?php }	else	{ ?>
				<div class="empty"><?php echo JText::_('CC NO ALBUM');?></div>
				<?php } ?>
			</div>
			
			<div class="app-box-footer">
				<div class="app-box-foot-note">
					<?php echo JText::sprintf( 'CC DISPLAYING ALBUMS COUNT' , count($albums) , $totalAlbums ); ?>
				</div>
			</div>
			
			<div class="app-box-footer">
				<a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&groupid=' . $group->id );?>">
				<?php echo JText::_('CC SHOW ALL ALBUMS');?>
				</a>
			</div>				
	</div>
	<?php }; ?>
	<?php } ?> 
	<!-- Group Photo @ Sidebar -->

	<!-- Group Video @ Sidebar -->
	<?php if($config->get('enablevideos') && $config->get('groupvideos') && $showVideos){ ?>
	<?php if($this->params->get('groupsVideosPosition') == 'js_groups_side_bottom'){ ?>
	<div id="community-group-side-videos" class="app-box">
		<div class="app-box-header">
			<h2 class="app-box-title"><?php echo JText::_('CC VIDEOS');?></h2>
		</div>

			<div class="app-box-content">
				<div id="community-group-container">
					<?php if($videos) { ?>
					<?php foreach( $videos as $video ) { ?>
					<!--VIDEO ITEMS-->
					<div class="video-items video-item jomTips" id="<?php echo "video-" . $video->getId() ?>" title="<?php echo $video->title . '::' . $this->escape($video->description); ?>">
						<!--VIDEO ITEM-->
						<div class="video-item clrfix">
			
								<!--VIDEO THUMB-->
								<div class="video-thumb">
								<a class="video-thumb-url" href="<?php echo $video->getURL(); ?>" style="width: <?php echo $videoThumbWidth; ?>px; height:<?php echo $videoThumbHeight; ?>px;">
								<img src="<?php echo $video->getThumbnail(); ?>" style="width: <?php echo $videoThumbWidth; ?>px; height:<?php echo $videoThumbHeight; ?>px;" alt="<?php echo $video->title; ?>" />
								</a>
								<span class="video-durationHMS"><?php echo $video->getDurationInHMS(); ?></span>
								</div>
								<!--VIDEO THUMB-->
								
								<!--VIDEO SUMMARY-->
								<div class="video-summary">
														
									<div class="video-details small">
										<div class="video-hits"><?php echo JText::sprintf('CC VIDEO HITS COUNT', $video->getHits()) ?></div>
										<div class="video-lastupdated"><?php echo $video->created; ?></div>
										<div class="video-creatorName">
											<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$video->creator); ?>">
											<?php echo $video->getCreatorName(); ?>
											</a>
										</div>											
									</div>										
								</div>
								<!--VIDEO SUMMARY-->
								
							</div>
							<!--VIDEO ITEM-->
					</div>
					
					<?php } ?>
					<!--VIDEO ITEMS-->
							<div class="clr"></div>
					<?php } else { ?>
							<div class="empty"><?php echo JText::_('CC NO VIDEOS'); ?></div>
					<?php } ?>
				</div>
			</div>
			
			<div class="app-box-footer">
				<div class="app-box-foot-note">
					<?php echo JText::sprintf( 'CC DISPLAYING VIDEOS COUNT' , count($videos) , $totalVideos ); ?>
				</div>
			</div>
			
			<div class="app-box-footer">
				<a href="<?php echo CRoute::_('index.php?option=com_community&view=videos&groupid='.$group->id); ?>">
				<?php echo JText::_('CC VIEW ALL VIDEOS'); ?>
				</a>
			</div>				
	</div>
	<?php }; ?>
	<?php } ?>
			
	<?php if( $showEvents ){ ?>
	    <!-- Group Events -->
	<?php if($this->params->get('groupsEventPosition') == 'js_groups_side_bottom'){ ?>
	<div id="community-group-side-events" class="app-box">
		<div class="app-box-header">
			<h2 class="app-box-title"><?php echo JText::_('CC EVENTS');?></h2>
		</div>
			<div class="app-box-content">
				<div id="community-group-container">
					<?php if( $events ) { ?>
						<ul class="cResetList">
						<?php
						foreach( $events as $event ) {
						?>
							<li class="jsRel jomTips tipFullWidth" title="<?php echo $this->escape($event->title);?>::<?php echo $this->escape( $event->description );?>">
								<div class="event-date jsFlLf">
									<div><img class="avatar jsFlLf" src="<?php echo $event->getThumbAvatar();?>" alt="<?php echo $this->escape( $event->title );?>" /></div>
									<div><?php echo CEventHelper::formatStartDate($event, $config->get('eventdateformat') ); ?></div>
								</div>
								<div class="event-detail">
									<div class="event-title">
										<a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id.'&groupid=' . $group->id);?>">
											<?php echo $event->title;?>
										</a>
									</div>
									<div class="event-loc">
										<?php echo $event->getCategoryName();?> <span>|</span> <?php echo $event->location;?>
									</div>
									<div class="eventTime"><?php echo JText::sprintf('CC EVENT TIME', JHTML::_('date', $event->startdate, JText::_('DATE_FORMAT_LC2') ), JHTML::_('date', $event->enddate, JText::_('DATE_FORMAT_LC2') )); ?></div>
									<div class="event-attendee small">
										<a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewguest&groupid=' . $group->id . '&eventid=' . $event->id . '&type='.COMMUNITY_EVENT_STATUS_ATTEND);?>"><?php echo JText::sprintf((cIsPlural($event->confirmedcount)) ? 'CC GUESTS COUNT MANY':'CC GUESTS COUNT', $event->confirmedcount);?></a>
									</div>
								</div>
								<div class="clr"></div>					
							</li>
						<?php } ?>
						</ul>
					<?php } else { ?>
							<div class="empty"><?php echo JText::_('CC EVENTS NOT CREATED'); ?></div>
					<?php } ?>
				</div>
			</div>
			
			<div class="app-box-footer">
				<div class="app-box-foot-note">
					<?php echo JText::sprintf( 'CC EVENTS COUNT DISPLAY' , count($events) , $totalEvents ); ?>
				</div>
			</div>
			
			<div class="app-box-footer">
				<?php if( $allowCreateEvent && ($isMember && !$isBanned) ){ ?>
					<a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=create&groupid=' . $group->id );?>"><?php echo JText::_('CC GROUP CREATE EVENT');?></a>
				<?php }	?>
				<a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=events&groupid=' . $group->id );?>"><?php echo JText::_('CC SHOW ALL EVENTS');?></a>
			</div>				
	</div>
	<?php }; ?>
	<?php }; ?>
	<!-- Group Video @ Sidebar -->
	<?php $this->renderModules( 'js_groups_side_bottom' ); ?>
	<?php $this->renderModules( 'js_side_bottom' ); ?>
	</div>
	<!-- end: .cSidebar -->
		
    
    <!-- begin: .cMain -->
    <div class="cMain clrfix">
    
			<div class="group-top">
				<!-- Group Top: Group Left -->
				<div class="group-left">
						<!-- Group Avatar -->
						<div id="community-group-avatar" class="group-avatar">
								<img src="<?php echo $group->getAvatar( 'avatar' ); ?>" border="0" alt="<?php echo $this->escape($group->name);?>" />
								<!-- Group Buddy -->
								<?php if( $isAdmin && !$isMine ) { ?>
										<div class="cadmin tag-this" title="<?php echo JText::_('CC GROUP USER ADMIN'); ?>">
												<?php echo JText::_('CC GROUP USER ADMIN'); ?>
										</div>
								<?php } else if( $isMine ) { ?>
										<div class="cowner tag-this" title="<?php echo JText::_('CC GROUP USER CREATOR'); ?>">
												<?php echo JText::_('CC GROUP USER CREATOR'); ?>
										</div>
								<?php } ?>
								 <!-- Group Buddy -->
						</div>
						<!-- Group Avatar -->   
				</div>
				<!-- Group Top: Group Left -->
				
				<!-- Group Top: Group Main -->
				<div class="group-main">
						<!-- Group Approval -->
						<div class="group-approval">
								<?php if( ( $isMine || $isAdmin || $isSuperAdmin) && ( $unapproved > 0 ) ) { ?>
								<div class="info">
										<a class="friend" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewmembers&approve=1&groupid=' . $group->id);?>">
												<?php echo JText::sprintf((CStringHelper::isPlural($unapproved)) ? 'CC AWAITING APPROVAL MANY'  :'CC AWAITING APPROVAL' , $unapproved ); ?>
										</a>
								</div>
								<?php } ?>
						
								<?php if( $waitingApproval ) { ?>
								<div class="info">
										<span class="icon-waitingapproval"><?php echo JText::_('CC AWAITING APPROVAL USER'); ?></span>
								</div>
								<?php }?>
						</div>
						<!-- Group Approval -->
								
						<!-- Group Information -->
						<div id="community-group-info" class="group-info">
								<div class="ctitle">
										<?php echo JText::_('CC GROUP TITLE INFORMATION');?>
										
										<!-- Group Owner & Admin -->
										<?php if( $isAdmin && !$isMine ) { ?>
											<span class="cadmin"><?php echo JText::_('CC GROUP USER ADMIN'); ?><span>
										<?php } else if( $isMine ) { ?>
											</span class="cowner"><?php echo JText::_('CC GROUP USER CREATOR'); ?></span>
										<?php } ?>
										<!-- Group Owner & Admin -->
										
								</div>
								
								<div class="cparam group-category">
										<div class="clabel"><?php echo JText::_('CC GROUP INFO CATEGORY'); ?>:</div>
										<div class="cdata" id="community-group-data-category">
												<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&categoryid=' . $group->categoryid);?>"><?php echo JText::_( $group->getCategoryName() ); ?></a>
										</div>
								</div>
								<div class="cparam group-name">
										<div class="clabel"><?php echo JText::_('CC GROUP INFO NAME');?>:</div>
										<div class="cdata" id="community-group-data-name">
												<?php echo $this->escape($group->name); ?>
												<?php
														if($group->approvals == COMMUNITY_PRIVATE_GROUP)
														{
																if( $isMine || $isCommunityAdmin )
																{
																		echo '<a href="' . CRoute::_('index.php?option=com_community&view=groups&task=edit&groupid=' . $group->id) . '"> ' . '('. JText::_('CC PRIVATE GROUP') . ')' . '</a>';
																}
																else
																{
																		echo '('. JText::_('CC PRIVATE GROUP') . ')';
																}							
														}
												?>									
										</div>
								</div>
								
								<!--<div class="cparam group-description">
										<div class="clabel"><?php echo JText::_('CC GROUP TYPE');?>:</div>
										<div class="cdata" id="community-group-data-description">Public **</div>
								</div>-->
								
								<div class="cparam group-created">
										<div class="clabel"><?php echo JText::_('CC GROUP INFO CREATED');?>:</div>
										<div class="cdata"><?php echo JHTML::_('date', $group->created, JText::_('DATE_FORMAT_LC')); ?></div>
								</div>            
								<div class="cparam group-owner">
										<div class="clabel">
												<?php echo JText::_('CC GROUP INFO CREATOR');?>:
										</div>
										<div class="cdata">
												<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $group->ownerid );?>"><?php echo $group->getOwnerName(); ?></a>
										</div>
								</div>

								<div class="cparam group-owner">
									<div class="clabel">
										<?php echo JText::_('CC GROUP LIKE');?>:
									</div>
									<div class="cdata">
									<span id="like-container" class="profile">
										<?php echo $likesHTML; ?>
									</span>
									</div>
								</div>
						</div>
						<!-- Group Information -->
						<div style="clear: left;"></div>
				</div>
				
				<!-- Event Top: Event Description -->
				<div class="group-desc">
						<h2><?php echo JText::_('CC GROUP INFO DESCRIPTION');?></h2>
						<?php echo $group->description; ?>
				</div>
				<!-- Event Top: Event Description -->
				
				<!-- Group Top: Group Main -->
			</div>
        
        
        <?php if( $group->approvals=='0' || $isMine || ($isMember && !$isBanned) || $isCommunityAdmin ) { ?>
        
        <!-- Group News -->
        <div id="community-group-news" class="app-box">
            <div class="app-box-header">
            <div class="app-box-header">            
                <h2 class="app-box-title"><?php echo JText::_('CC GROUP NEWS');?></h2>
                <div class="app-box-menus">
                    <div class="app-box-menu toggle">
                        <a class="app-box-menu-icon" href="javascript: void(0)" onclick="joms.apps.toggle('#community-group-news');">
                            <span class="app-box-menu-title"><?php echo JText::_('CC EXPAND');?></span>
                        </a>
                    </div>
                </div>
            </div>                
            </div>  
            <div class="app-box-content">
                <?php echo $bulletinsHTML; ?>
            </div>
            <div class="app-box-footer">
            	<div class="app-box-info"><?php echo JText::sprintf( 'CC DISPLAYING BULLETIN COUNT' , count($bulletins) , $totalBulletin ); ?></div>
                <div class="app-box-actions">
                    <?php if( $isAdmin || $isSuperAdmin ): ?>
                    <a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=addnews&groupid=' . $group->id );?>">
                        <?php echo JText::_('CC ADD BULLETIN');?>
                    </a>
                    <?php endif; ?>
                    <a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewbulletins&groupid=' . $group->id);?>">
                        <?php echo JText::_('CC SHOW ALL BULLETINS');?>
                    </a>
                </div>                
            </div>
        </div>
        <!-- Group News -->    

        <!-- Group Discussion -->
        <?php if($config->get('creatediscussion')): ?>
        <div id="community-group-dicussion" class="app-box">
            <div class="app-box-header">
            <div class="app-box-header">            
                <h2 class="app-box-title"><?php echo JText::_('CC DISCUSSIONS');?></h2>
                <div class="app-box-menus">
                    <div class="app-box-menu toggle">
                        <a class="app-box-menu-icon" href="javascript: void(0)" onclick="joms.apps.toggle('#community-group-dicussion');">
                            <span class="app-box-menu-title"><?php echo JText::_('CC EXPAND');?></span>
                        </a>
                    </div>
                </div> 
            </div>
            </div>
            <div class="app-box-content">
                <?php echo $discussionsHTML; ?>
            </div>
            <div class="app-box-footer">
				<div class="app-box-info"><?php echo JText::sprintf( 'CC DISPLAYING DISCUSSION COUNT' , count($discussions) , $totalDiscussion ); ?></div>
                <div class="app-box-actions">
                    <?php if( ($isMember && !$isBanned) && !($waitingApproval) || $isSuperAdmin): ?>
					<a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&groupid=' . $group->id . '&task=adddiscussion');?>">
                        <?php echo JText::_('CC ADD DISCUSSION');?>
                    </a>
                    <?php endif; ?>
                    <a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussions&groupid=' . $group->id );?>">
                        <?php echo JText::_('CC SHOW ALL DISCUSSIONS');?>
                    </a>
                </div>                
            </div>        
        </div>
        <?php endif; ?>    
        <!-- Group Discussion -->

        <?php if( $showEvents ){ ?>
        <?php if($this->params->get('groupsEventPosition') == 'content'){ ?>
        <!-- Group Events -->
        <div id="community-group-events" class="app-box jsGroupEvent">
            <div class="app-box-header">
            <div class="app-box-header">            
                <h2 class="app-box-title"><?php echo JText::_('CC EVENTS');?></h2>
                <div class="app-box-menus">
                    <div class="app-box-menu toggle">
                        <a class="app-box-menu-icon" href="javascript: void(0)" onclick="joms.apps.toggle('#community-group-wall');">
                            <span class="app-box-menu-title"><?php echo JText::_('CC EXPAND');?></span>
                        </a>
                    </div>
                </div>            
            </div>
            </div>            
            <div class="app-box-content">
				<?php if( $events ){ ?>
				<ul class="cResetList">
				<?php
				foreach( $events as $event ) {
					$creator			= CFactory::getUser($event->creator);
				?>
					<li class="jsRel jomTips tipFullWidth" title="<?php echo $this->escape($event->title);?>::<?php echo $this->escape( $event->description );?>">
						<div class="event-date jsFlLf">
							<div><img class="avatar jsFlLf" src="<?php echo $event->getThumbAvatar();?>" alt="<?php echo $this->escape( $event->title );?>" /></div>
							<div><?php echo CEventHelper::formatStartDate($event, $config->get('eventdateformat') ); ?></div>
						</div>
						<div class="event-detail">
							<div class="event-title">
								<a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid=' . $event->id.'&groupid=' . $group->id);?>">
									<?php echo $event->title;?>
								</a>
							</div>
							<div class="event-loc">
								<?php echo $event->getCategoryName();?> <span>|</span> <?php echo $event->location;?>
							</div>
							<div class="eventTime"><?php echo JText::sprintf('CC EVENT TIME', JHTML::_('date', $event->startdate, JText::_('DATE_FORMAT_LC2') ), JHTML::_('date', $event->enddate, JText::_('DATE_FORMAT_LC2') )); ?></div>
							<div class="event-attendee small">
								<a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewguest&groupid=' . $group->id . '&eventid=' . $event->id . '&type='.COMMUNITY_EVENT_STATUS_ATTEND);?>"><?php echo JText::sprintf((cIsPlural($event->confirmedcount)) ? 'CC GUESTS COUNT MANY':'CC GUESTS COUNT', $event->confirmedcount);?></a>
							</div>
						</div>
						<div class="clr"></div>					
					</li>
				<?php } ?>
				</ul>
				<?php } else { ?>
				<div class="empty"><?php echo JText::_('CC EVENTS NOT CREATED');?></div>
				<?php } ?>
            </div>
            <div class="app-box-footer">
				<div class="app-box-info"><?php echo JText::sprintf( 'CC EVENTS COUNT DISPLAY' , count($events) , $totalEvents ); ?></div>
					<div class="app-box-actions">
						<?php if( $allowCreateEvent && ($isMember && !$isBanned) ){ ?>
							<a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=create&groupid=' . $group->id );?>"><?php echo JText::_('CC GROUP CREATE EVENT');?></a>
						<?php }	?>
						<a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=events&groupid=' . $group->id );?>"><?php echo JText::_('CC SHOW ALL EVENTS');?></a>
					</div>
            </div>
        </div>
        <!-- Group Events -->
        <?php } ?>
        <?php } ?>
        
        <!-- Group Photos -->
        <?php if($config->get('enablephotos') && $config->get('groupphotos') && $showPhotos): ?>
        <?php if($this->params->get('groupsPhotosPosition') == 'content'): ?>
        <div id="community-group-photos" class="app-box">
            <div class="app-box-header">
			<div class="app-box-header">    
                <h2 class="app-box-title"><?php echo JText::_('CC ALBUMS');?></h2>
                <div class="app-box-menus">
                    <div class="app-box-menu toggle">
                        <a class="app-box-menu-icon" href="javascript: void(0)" onclick="joms.apps.toggle('#community-group-photos');">
                            <span class="app-box-menu-title"><?php echo JText::_('CC EXPAND');?></span>
                        </a>
                    </div>
                </div> 
            </div>
            </div>
            <div class="app-box-content">
						<?php
						if( $albums )
						{
						?>
						<div class="clrfix">
						<?php foreach($albums as $album ) { ?>
						<a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=album&albumid=' . $album->id . '&groupid=' . $group->id);?>"><img class="avatar jomTips" title="<?php echo $this->escape($album->name);?>::<?php echo $this->escape( $album->description );?>" src="<?php echo $album->thumbnail;?>" alt="<?php echo $this->escape( $album->name );?>" /></a>
						<?php } ?>
						</div>
						
						<?php
						}
						else
						{
						?>
						<div class="empty"><?php echo JText::_('CC NO ALBUM');?></div>
						<?php
						}
						?>
            </div>
            <div class="app-box-footer">
							<div class="app-box-info">
								<?php echo JText::sprintf( 'CC DISPLAYING ALBUMS COUNT' , count($albums) , $totalAlbums ); ?>
							</div>
							
							<div class="app-box-actions">
							<?php
							if( $allowManagePhotos && ($isMember && !$isBanned) )
							{
							if( $albums )
							{
							?>
							<a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=photos&groupid=' . $group->id . '&task=uploader');?>">
								<?php echo JText::_('CC UPLOAD PHOTOS');?>
							</a>
							<?php
							}
							?>
							<a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=photos&groupid=' . $group->id . '&task=newalbum');?>">
								<?php echo JText::_('CC CREATE ALBUM BUTTON');?>
							</a>
							<?php 
							}
							?>
							<a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=photos&groupid=' . $group->id );?>">
								<?php echo JText::_('CC SHOW ALL ALBUMS');?>
							</a>
							</div>
            </div>
        </div>
        <?php endif; ?> 
        <?php endif; ?>    
        <!-- Group Photos -->
        
				<?php if($config->get('enablevideos') && $config->get('groupvideos') && $showVideos) { ?>
				<?php if($this->params->get('groupsPhotosPosition') == 'content'){ ?>
				<!-- Latest Group Video -->
				<div id="community-group-videos" class="app-box">
					<div class="app-box-header">
						<div class="app-box-header">
							<h2 class="app-box-title"><?php echo JText::_('CC VIDEOS'); ?></h2>
							<div class="app-box-menus">
									<div class="app-box-menu toggle">
											<a class="app-box-menu-icon"
												 href="javascript: void(0)"
												 onclick="joms.apps.toggle('#community-group-videos');"><span class="app-box-menu-title"><?php echo JText::_('CC EXPAND');?></span></a>
									</div>
							</div>
						</div>
					</div>
						
					<div class="app-box-content">
						<div id="community-group-container">
						<?php if($videos) { ?>
						<?php foreach( $videos as $video ) { ?>
						<!--VIDEO ITEMS-->
						<div class="video-items video-item jomTips" id="<?php echo "video-" . $video->getId() ?>" title="<?php echo $video->getTitle() . '::' . $video->getDescription(); ?>">
							<!--VIDEO ITEM-->
							<div class="video-item clrfix">
				
									<!--VIDEO THUMB-->
									<div class="video-thumb">
									<a class="video-thumb-url" href="<?php echo $video->getURL(); ?>" style="width: <?php echo $videoThumbWidth; ?>px; height:<?php echo $videoThumbHeight; ?>px;">
									<img src="<?php echo $video->getThumbnail(); ?>" style="width: <?php echo $videoThumbWidth; ?>px; height:<?php echo $videoThumbHeight; ?>px;" alt="<?php echo $video->getTitle(); ?>" />
									</a>
									<span class="video-durationHMS"><?php echo $video->getDurationInHMS(); ?></span>
									</div>
									<!--VIDEO THUMB-->
									
									<!--VIDEO SUMMARY-->
									<div class="video-summary">
										<div class="video-title"><a href="<?php echo $video->getURL(); ?>"><?php echo $video->getTitle(); ?></a></div>									
										<div class="video-details small">
											<div class="video-hits"><?php echo JText::sprintf('CC VIDEO HITS COUNT', $video->getHits()) ?></div>
											<div class="video-lastupdated"><?php echo JText::sprintf('CC VIDEO LAST UPDATED', $video->created ); ?></div>
											<div class="video-creatorName">
												<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$video->creator); ?>">
												<?php echo $video->getCreatorName(); ?>
												</a>
											</div>											
										</div>										
									</div>
									<!--VIDEO SUMMARY-->
									
								</div>
								<!--VIDEO ITEM-->
						</div>
						
						<?php } ?>
						<!--VIDEO ITEMS-->
						
								<div class="clr"></div>
						<?php } else { ?>
								<div class="empty"><?php echo JText::_('CC NO VIDEOS'); ?></div>
						<?php } ?>
							</div>
						</div>
						
						<div class="app-box-footer">
							<div class="app-box-info"><?php echo JText::sprintf( 'CC DISPLAYING VIDEOS COUNT' , count($videos) , $totalVideos ); ?></div>
								<div class="app-box-actions">
										<?php
										if( $allowManageVideos && ($isMember && !$isBanned) )
										{
										?>
										<a class="app-box-action" href="javascript:void(0)" onclick="joms.videos.addVideo('<?php echo VIDEO_GROUP_TYPE; ?>', '<?php echo $group->id; ?>')">
												<?php echo JText::_('CC ADD VIDEO');?>
										</a>
										<?php 
										}
										?>
										<a class="app-box-action" href="<?php echo CRoute::_('index.php?option=com_community&view=videos&groupid='.$group->id); ?>">
										<?php echo JText::_('CC VIEW ALL VIDEOS'); ?>
										</a>
								</div>                
						</div> 
				</div>
				<!-- Latest Group Video -->
				<?php } ?>
				<?php } ?>
        
        <!-- Group Walls -->
        <div id="community-group-wall" class="app-box group-wall">
            <div class="app-box-header">
            <div class="app-box-header">            
                <h2 class="app-box-title"><?php echo JText::_('CC WALL');?></h2>
                <div class="app-box-menus">
                    <div class="app-box-menu toggle">
                        <a class="app-box-menu-icon" href="javascript: void(0)" onclick="joms.apps.toggle('#community-group-wall');">
                            <span class="app-box-menu-title"><?php echo JText::_('CC EXPAND');?></span>
                        </a>
                    </div>
                </div>            
            </div>
            </div>            
            <div class="app-box-content">
            	<div id="wallForm"><?php echo $wallForm; ?></div>
                <div id="wallContent"><?php echo $wallContent; ?></div>
            </div>
        </div>
        <!-- Group Walls -->
        
        <?php } // if( $group->approvals == '0' || $isMine || $isMember || $isCommunityAdmin ) ?>
		</div>
		<!-- end: .cMain -->

</div>
<!-- end: .cLayout -->

</div>

<?php if($editGroup) {?>
<script type="text/javascript">
	joms.groups.edit();
</script>
<?php } ?>