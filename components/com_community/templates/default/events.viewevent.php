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
 * @params	event		Event	A group object that has the property of a group
 * @params	wallForm	string A html data that will output the walls form.
 * @params	wallContent string A html data that will output the walls data.
 **/
defined('_JEXEC') or die();
?>

<div class="event">
	<div class="page-actions">
		<?php echo $reportHTML;?>
		<?php echo $bookmarksHTML;?>
	</div>
	<!-- begin: .cLayout -->
	<div class="cLayout clrfix">
		<!-- begin: .cSidebar -->
	    <div class="cSidebar clrfix">
		<?php $this->renderModules( 'js_side_top' ); ?>
        <?php $this->renderModules( 'js_events_side_top' ); ?>
        <!-- Event Menu -->
        <?php if($memberStatus != COMMUNITY_EVENT_STATUS_BLOCKED) { ?>
        <div id="community-event-action" class="cModule">
			<h3><?php echo JText::_('CC EVENT OPTION'); ?></h3>
            <div class="app-box-content">
            <!-- Event Menu List -->
            <ul class="event-menus clrfix">
                <?php if( $isMine || $isCommunityAdmin || $isAdmin) {?>
                <!-- Edit Event Avatar -->
                <li class="event-menu">
                    <a class="event-edit-avatar" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=uploadavatar&eventid=' . $event->id );?>"><?php echo JText::_('CC EDIT EVENT AVATAR');?></a>
                </li>
                <!-- Send email to participants -->
                <li class="event-menu">
                    <a class="event-invite-email" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=sendmail&eventid=' . $event->id );?>"><?php echo JText::_('CC SEND EMAIL TO PARTICIPANTS');?></a>
                </li>
                <!-- Edit Event -->
                <li class="event-menu">
                    <a class="event-edit-info" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=edit&eventid=' . $event->id );?>"><?php echo JText::_('CC EDIT EVENT');?></a>
                </li>
                <?php } ?>
                <?php if( ( ($isEventGuest && ($event->allowinvite)) || $isAdmin) && $handler->hasInvitation() ) { ?>
				<li>
					<?php echo $inviteHTML; ?>
				</li>
				<?php } ?>
                <?php if( $handler->showPrint() ) { ?>
                <!-- Print Event -->
                <li class="event-menu">
                    <a class="event-print" href="javascript:void(0)" onclick="window.open('<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=printpopup&eventid='.$event->id); ?>','', 'menubar=no,width=600,height=700,toolbar=no');"><?php echo JText::_('CC EVENTS PRINT POPUP');?></a>
                </li>
                <?php } ?>

                <?php if( $handler->showExport() && $config->get('eventexportical') ) { ?>
                <!-- Export Event -->
                <li class="event-menu">
                    <a class="event-export-ical" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=export&format=raw&eventid=' . $event->id); ?>" ><?php echo JText::_('CC EVENTS EXPORT ICAL');?></a>
                </li>
                <?php } ?>

				<?php if( ($event->permission != COMMUNITY_PRIVATE_EVENT) && ($isMine || $isCommunityAdmin || $isAdmin) ){ ?>
				<!-- Copy Event -->
		                <li class="event-menu">
		                    <a class="event-copy" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=create&eventid=' . $event->id );?>"><?php echo JText::_('CC EVENTS DUPLICATE');?></a>
		                </li>
				<?php } ?>

                <?php if( (!$isEventGuest) && ($event->permission == COMMUNITY_PRIVATE_EVENT) && (!$waitingApproval)) { ?>
                <!-- Join Event -->
                <li class="event-menu">
                    <a class="event-join" href="javascript:void(0);" onclick="javascript:joms.events.join('<?php echo $event->id;?>');"><?php echo JText::_('CC TAB REQUEST INVITE'); ?></a>
                </li>
                <?php } ?>

                <?php if( (!$isMine) && !($waitingRespond) && (COwnerHelper::isRegisteredUser()) ) { ?>
                <!-- Leave Event -->
                <li class="event-menu important">
                    <a class="event-leave" href="javascript:void(0);" onclick="joms.events.leave('<?php echo $event->id;?>');"><?php echo JText::_('CC EVENTS IGNORE');?></a>
                </li>
                <?php } ?>

                <?php if( $handler->manageable() ) { ?>
                <!-- Delete Event -->
                <li class="event-menu important">
                    <a class="event-delete" href="javascript:void(0);" onclick="javascript:joms.events.deleteEvent('<?php echo $event->id;?>');"><?php echo JText::_('CC DELETE EVENT'); ?></a>
                </li>
                <?php } ?>
            </ul>
            <!-- Event Menu List -->

            </div>
        </div>
        <?php } ?>
        <!-- Event Menu -->
        <!-- Event RSVP Status -->
		<?php if( $handler->isAllowed() && !$isPastEvent ) { ?>
		<div id="community-event-rsvp-status" class="cModule">
			<h3><?php echo JText::_('CC YOUR RSVP'); ?></h3>
			<p><?php echo JText::_('CC YOUR RSVP TEXT'); ?></p>
			<form name="event-invite" id="event-status" action="<?php echo CRoute::_('index.php?option=com_community&view=events&task=updatestatus'); ?>" method="post">
			<div class="app-box-content">
				<div><?php echo $radioList; ?></div>
				<input type="hidden" name="eventid" value="<?php echo $event->id;?>" />
				<input type="hidden" name="memberid" value="<?php echo $my->id;?>" />
				<?php echo JHTML::_( 'form.token' ); ?>
			</div>
			<div class="app-box-footer">
		      <input type="submit" value="<?php echo JText::_('CC BUTTON RESPONSE'); ?>" class="button" />
			</div>
			</form>
		</div>
		<?php
		} else if($event->getMemberStatus($my->id) == COMMUNITY_EVENT_STATUS_BLOCKED){ ?>
		<div id="community-event-rsvp-status" class="cModule">
		 <h3><?php echo JText::_('CC YOUR RSVP'); ?></h3>
		<div class="app-box-content">
		      <?php echo JText::_('CC EVENTS ATTENDENCE BLOCKED'); ?>
		</div>
		</div>
		
		<?php } ?>
        <!-- Event RSVP Status -->

        <!-- Event Admins -->
        <div id="community-event-admins" class="cModule">
        	<h3><?php echo JText::sprintf('CC ADMINS'); ?></h3>

            <div class="app-box-content">
                <ul class="cThumbList clrfix">
                <?php
                if($eventAdmins) {
                    foreach($eventAdmins as $row) {
                ?>
                    <li class="event-admin-list">
                        <a class="event-admin-thumb" href="<?php echo CUrlHelper::userLink($row->id); ?>">
                            <img border="0" height="45" width="45" class="avatar jomTips" src="<?php echo $row->getThumbAvatar(); ?>" title="<?php echo cAvatarTooltip($row);?>" alt="<?php echo $row->getDisplayName(); ?>" />
                        </a>
                        <div class="event-admin-info">
                            <div id="event-admin-name"><?php echo $row->getDisplayName(); ?></div>
                            <div id="event-admin-is" class="small"><?php echo ($event->creator == $row->id) ? JText::_('CC EVENT CREATOR') : ''; ?></div>
                            <?php
                            if( $my->id != $row->id )
                            {
                            ?>
                                <div id="event-admin-write">
								    <a onclick="joms.messaging.loadComposeWindow(<?php echo $row->id; ?>)" href="javascript:void(0);"><?php echo JText::_('CC EVENT PM ME'); ?></a>
								</div>
                            <?php
                            }
                            ?>
                        </div>
                    </li>
                <?php
                  }
                }
                ?>
                </ul>
            </div>
            <div class="app-box-footer">
            <?php if( $handler->isAllowed() ){ ?>
                <a href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewguest&eventid=' . $event->id . '&type=' . COMMUNITY_EVENT_ADMINISTRATOR );?>">
                    <?php echo JText::_('CC SHOW ALL');?> (<?php echo $eventAdminsCount; ?>)
                </a>
            <?php } ?>
            </div>
        </div>
        <!-- Event Admins -->

		<?php if( $handler->isAllowed() || $isCommunityAdmin ) { ?>
        <!-- Event Attending -->
        <?php if($eventMembersCount>0): ?>
        <div id="community-event-members" class="cModule">
        	<h3><?php echo JText::sprintf('CC CONFIRMED GUESTS'); ?></h3>

            <div class="app-box-content">
                <ul class="cThumbList clrfix">
                <?php
                if($eventMembers) {
                    foreach($eventMembers as $member) {
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
                <a href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewguest&eventid=' . $event->id . '&type='.COMMUNITY_EVENT_STATUS_ATTEND );?>">
                    <?php echo JText::_('CC SHOW ALL');?> (<?php echo $eventMembersCount; ?>)
                </a>
            </div>
        </div>
        <?php endif; ?>

		<?php if($pendingMembersCount>0): ?>
        <div id="community-event-members-pending" class="cModule">
        	<h3><?php echo JText::sprintf('CC PENDING GUESTS'); ?></h3>

            <div class="app-box-content">
                <ul class="cThumbList clrfix">
                <?php
                if($pendingMembers) {
                    foreach($pendingMembers as $member) {
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
                <a href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewguest&eventid=' . $event->id . '&type=' . COMMUNITY_EVENT_STATUS_INVITED );?>">
                    <?php echo JText::_('CC SHOW ALL');?> (<?php echo $pendingMembersCount; ?>)
                </a>
            </div>
        </div>
        <?php endif; ?>

		<!-- Event Blocked Guests -->

		<?php if( $isMine || $isCommunityAdmin || $event->isAdmin($my->id) ) { ?>
		<?php if($blockedMembersCount>0): ?>
		<div id="community-event-members-blocked" class="cModule">
        	<h3><?php echo JText::sprintf('CC BLOCKED GUESTS'); ?></h3>

            <div class="app-box-content">
                <ul class="cThumbList clrfix">
                <?php
                if($blockedMembers) {
                    foreach($blockedMembers as $member) {
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
                <a href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewguest&eventid=' . $event->id . '&type=' . COMMUNITY_EVENT_STATUS_BLOCKED );?>">
                    <?php echo JText::_('CC SHOW ALL');?> (<?php echo $blockedMembersCount; ?>)
                </a>
            </div>
        </div>
        <?php endif; ?>
		<?php } ?>
        <!-- Event Members -->

        <!-- Event Summary -->
        <?php } ?>

	    <?php $this->renderModules( 'js_events_side_bottom' ); ?>
	    <?php $this->renderModules( 'js_side_bottom' ); ?>
    </div>
    <!-- end: .cSidebar -->

    <!-- begin: .cMain -->
    <div class="cMain clrfix">

	<?php if( $isInvited ){ ?>
	<div id="events-invite-<?php echo $event->id; ?>" class="com-invitation-msg">
		<div class="com-invite-info">
			<?php echo JText::sprintf( 'CC EVENTS YOU ARE INVITED', $join ); $test = 1; ?><br />
			<?php echo JText::sprintf( (CStringHelper::isPlural($friendsCount)) ? 'CC EVENTS FRIEND' : 'CC EVENTS FRIEND MANY', $friendsCount ); ?>
		</div>
		<div class="com-invite-action">
			<?php echo JText::_( 'CC EVENTS ACTION ORDER' ) . JText::_('CC OR'); ?>
			<a href="javascript:void(0);" onclick="jax.call('community','events,ajaxRejectInvitation','<?php echo $event->id; ?>');">
				<?php echo JText::_('CC REJECT INVITATION'); ?>
			</a>
		</div>
	</div>
	<?php } ?>

    <div class="event-top">
        <!-- Event Top: Event Left -->
        <div class="event-left">
            <!-- Event Avatar -->
            <div id="community-event-avatar" class="event-avatar">
                <img src="<?php echo $event->getAvatar( 'avatar' ); ?>" border="0" alt="<?php echo $this->escape($event->title);?>" />
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
            <!-- Event Avatar -->
        </div>
        <!-- Event Top: Event Left -->

        <!-- Event Top: Event Main -->
        <div class="event-main">
            <!-- Event Approval -->
            <div class="event-approval">
                <?php if( ( $isMine || $isAdmin || $isCommunityAdmin) && ( $unapproved > 0 ) ) { ?>
                <div class="info">
                    <a class="friend" href="<?php echo $handler->getFormattedLink('index.php?option=com_community&view=events&task=viewguest&type='.COMMUNITY_EVENT_STATUS_REQUESTINVITE.'&eventid=' . $event->id);?>">
                        <?php echo JText::sprintf((CStringHelper::isPlural($unapproved)) ? 'CC EVENT INVITE REQUEST PENDING MANY'  :'CC EVENT INVITE REQUEST PENDING' , $unapproved ); ?>
                    </a>
                </div>
                <?php } ?>

                <?php if( $waitingApproval ) { ?>
                <div class="info">
                    <span class="icon-waitingapproval"><?php echo JText::_('CC EVENT AWAITING APPROVAL USER'); ?></span>
                </div>
                <?php }?>
            </div>

            <!-- Event Information -->
            <div id="community-event-info" class="event-info">
                <div class="ctitle">
                    <?php echo JText::_('CC EVENT TITLE INFORMATION');?>
                    <?php
                    if( $isAdmin && !$isMine ) {
                        echo JText::_('CC EVENT USER ADMIN');
                    } else if( $isMine ) {
                        echo JText::_('CC EVENT USER CREATOR');
                    }
                    ?>
                </div>

                <div class="cparam event-category">
                    <div class="clabel"><?php echo JText::_('CC EVENTS CATEGORY'); ?>:</div>
                    <div class="cdata" id="community-event-data-category">
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=events&categoryid=' . $event->catid);?>"><?php echo JText::_( $event->getCategoryName() ); ?></a>
                    </div>
                </div>


    			<!-- Location info -->
                <div class="cparam event-location">
                    <div class="clabel"><?php echo JText::_('CC EVENTS LOCATION');?>:</div>
                    <div class="cdata" id="community-event-data-location"><?php echo $event->location; ?></div>
                </div>
                <div class="cparam event-created">
                    <div class="clabel"><?php echo JText::_('CC EVENTS TIME');?>:</div>
                    <div class="cdata small">
                    <?php echo JText::sprintf('CC EVENT TIME', JHTML::_('date', $event->startdate, JText::_('DATE_FORMAT_LC2') , ''), JHTML::_('date', $event->enddate, JText::_('DATE_FORMAT_LC2') , '') ); ?>
    				<?php if( $config->get('eventshowtimezone') ) { ?>
					<div class="small"><?php echo $timezone; ?></div>
					<?php } ?>
    				</div>
                </div>

    			<!-- Number of tickets -->
    			<div class="cparam event-tickets">
                    <div class="clabel">
                        <?php echo JText::_('CC SEATS AVAILABLE');?>:
                    </div>
                    <div class="cdata">
                        <?php
    						if($event->ticket)
    							echo JText::sprintf('CC EVENTS TICKET STATS', $event->ticket, $eventMembersCount, ($event->ticket - $eventMembersCount));
    						else
    							echo JText::sprintf('CC EVENTS TICKET UNLIMITED');
    					?>
                    </div>
                </div>

                <div class="cparam event-owner">
                    <div class="clabel">
                        <?php echo JText::_('CC EVENT CREATOR');?>:
                    </div>
                    <div class="cdata">
                        <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $event->creator );?>"><?php echo $event->getCreatorName(); ?></a>
                    </div>
                </div>
            </div>
            <!-- Event Information -->
            <div style="clear: left;"></div>
        </div>
        <!-- start: Event Main -->
        
	<!-- Event Top: App Like -->
	<div class="jsApLike">
	    <span id="like-container">
		    <?php echo $likesHTML; ?>
		</span>
		<div class="clr"></div>
	</div>
	<!-- end: App Like -->
		
        <!-- Event Top: Event Description -->
        <div class="event-desc">
            <div class="ctitle"><h2><?php echo JText::_('CC EVENT DETAILS');?></h2></div>
            <?php 
			if( !CStringHelper::isHTML($event->description) )
			{
				echo CStringHelper::nl2br($event->description);
			}
			else
			{
				echo $event->description;
			} 
			?>
        </div>
        <!-- Event Top: Event Description -->

    </div>


		<!-- begin: map -->
		<?php if( $config->get('eventshowmap') ) {  ?>
			<?php
	  		CFactory::load('libraries', 'mapping');
	  		if(CMapping::validateAddress($event->location)){
	  		?>
			<div id="community-event-map" class="app-box event-wall">
	            <div class="app-box-header">
	            <div class="app-box-header">
	                <h2 class="app-box-title"><?php echo JText::_('CC MAP LOCATION');?></h2>
	                <div class="app-box-menus">
	                    <div class="app-box-menu toggle">
	                        <a class="app-box-menu-icon" href="javascript: void(0)" onclick="joms.apps.toggle('#community-event-map');">
	                            <span class="app-box-menu-title"><?php echo JText::_('CC EXPAND');?></span>
	                        </a>
	                    </div>
	                </div>
	            </div>
	            </div>
	            <div class="app-box-content event-description">
	            	<!-- begin: dynamic map -->
	          		<?php echo CMapping::drawMap('event-map', $event->location); ?>
	          		<div id="event-map" style="height:300px;width:100%">
	          		<?php echo JText::_('CC MAPS LOADING'); ?>
	          		</div>
	          		<!-- end: dynamic map -->
	            </div>
	
				<div class="app-box-footer">
					<div class="app-box-actions">
						<a href="javascript:void(0)" onclick="joms.maps.initialize('event-map', '<?php echo urlencode($event->location); ?>', '', '')" class="app-box-action"><?php echo JText::_('CC EVENTS CENTER MAP') ?></a>
						<a href="http://maps.google.com/?q=<?php echo urlencode($event->location); ?>" target="_blank" class="app-box-action"><?php echo JText::_('CC EVENTS SHOW FULL MAP'); ?></a>
					</div>
				</div>
	        </div>
	        <?php } ?>
		<?php } ?>
		<!-- end: map -->
        <!-- Event Walls -->
        <?php if( $handler->isAllowed() ) { ?>
        <div id="community-event-wall" class="app-box event-wall">
            <div class="app-box-header">
            <div class="app-box-header">
                <h2 class="app-box-title"><?php echo JText::_('CC WALL');?></h2>
                <div class="app-box-menus">
                    <div class="app-box-menu toggle">
                        <a class="app-box-menu-icon" href="javascript: void(0)" onclick="joms.apps.toggle('#community-event-wall');">
                            <span class="app-box-menu-title"><?php echo JText::_('CC EXPAND');?></span>
                        </a>
                    </div>
                </div>
            </div>
            </div>
            <div class="app-box-content">
            	<!-- Cannot post something if you ignore the event -->
            	<?php if($wallForm) { ?>
            	<div id="wallForm"><?php echo $wallForm; ?></div>
            	<?php } ?>
                <div id="wallContent"><?php echo $wallContent; ?></div>
            </div>
        </div>
        <?php } ?>
        <!-- Event Walls -->

	</div>
    <!-- end: .cMain -->

</div>
<!-- end: .cLayout -->
</div>
<?php if($editEvent) {?>
<script type="text/javascript">
	joms.events.edit();
</script>
<?php } ?>