<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 */
defined('_JEXEC') or die();
?>
<script type="text/javascript">joms.filters.bind();</script>
<?php echo $header;?>

<!-- .frontpage -->
<div class="frontpage">


<!-- .frontpage-right -->
<div class="frontpage-right">

    <?php $this->renderModules( 'js_side_top' ); ?>
    
    <?php if( $config->get('showsearch') == '1' || ($config->get('showsearch') == '2' && $my->id != 0 ) ) { ?>
    <!-- Search -->
    <div class="app-box">
        <div class="app-box-header">
            <h2 class="app-box-title"><?php echo JText::_('CC SEARCH'); ?></h2>
        </div>
        <div class="app-box-content">
            <form name="search" id="cFormSearch" method="get" action="<?php echo CRoute::_('index.php?option=com_community&view=search');?>">
                <input type="text" class="inputbox" id="keyword" name="q" />
                <input type="submit" name="submit" value="<?php echo JText::_('CC SEARCH'); ?>" class="button" />
            	<input type="hidden" name="option" value="com_community" />
            	<input type="hidden" name="view" value="search" />
                <div class="small">
                    <?php echo JText::sprintf('CC TRY ADVANCED SEARCH', CRoute::_('index.php?option=com_community&view=search&task=advancesearch') ); ?>
                </div>
            </form>
        </div>
    </div>
    <!-- Search -->
    <?php } ?>
    
    <!-- Latest Groups -->
    <?php if($config->get('enablegroups')) { ?>
    <?php if( !empty($latestGroups) && ($config->get('showlatestgroups') == '1' || ($config->get('showlatestgroups') == '2' && $my->id != 0 ) ) ) { ?>
			<?php echo $latestGroups;?>
	<?php } ?>
    <?php } ?>
    <!-- Latest Groups -->
    
    <!-- Latest Events -->
	<?php if($this->params->get('frontpageShowLatestEvent')) { ?>
			<?php if($config->get('enableevents') ) { ?>
			<?php if( !empty($latestEvents) && ( $config->get('frontpage_latest_events') == '1' || ($config->get('frontpage_latest_events') == '2' && $my->id != 0 ) ) ) { ?>
			<!-- Latest Events -->
			<div class="app-box latest-events"><?php echo $latestEvents; ?></div>
			<!-- Latest Events -->
			<?php } ?>
			<?php } ?>
	<?php } ?>
    <!-- Latest Events -->
    
    <!-- Latest Photo -->
    <?php if($config->get('enablephotos')){ ?>
    <?php if( $config->get('showlatestphotos') == '1' || ($config->get('showlatestphotos') == '2' && $my->id != 0 ) ) { ?>
	    <div class="app-box">
			<?php echo $latestPhotosHTML; ?>
	    </div>
    <?php } ?>
    <?php } ?>        
    <!-- Latest Photo -->
	
	<?php if( $config->get('showonline') == '1' || ($config->get('showonline') == '2' && $my->id != 0 ) ) { ?>
    <!-- Who's online -->
    <div class="app-box">
        <div class="app-box-header">
            <h2 class="app-box-title"><?php echo JText::_('CC WHOSE ONLINE'); ?></h2>
            <div class="app-box-menus">
                <div class="app-box-menu"></div>
            </div>
        </div>
        
        <div class="app-box-content">
            <ul class="cThumbList clrfix">
                <?php
                    for( $i = 0 ; $i < count( $onlineMembers ); $i++ )
                    {
                        $row    =& $onlineMembers[$i];
                ?>
                <li>
                    <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$row->id ); ?>"><img class="avatar jomTips" src="<?php echo $row->user->getThumbAvatar(); ?>" title="<?php echo cAvatarTooltip($row->user); ?>" width="45" height="45" alt="<?php echo $row->user->getDisplayName();?>"/></a>
                </li>
                <?php
                    }
                ?>
            </ul>
        </div>
    </div>
    <!-- Who's online -->
	<?php } ?>
    <?php $this->renderModules( 'js_side_bottom' ); ?>

</div>
<!-- .frontpage-right -->



<!-- .frontpage-main -->
<div class="frontpage-main">
	<?php if( $config->get('showlatestmembers') == '1' || ($config->get('showlatestmembers') == '2' && $my->id != 0 ) ) { ?>
    <?php echo $latestMembers; ?>
	<?php } ?>
	
	<?php if($config->get('enablevideos')) { ?>
	<?php if( $config->get('showlatestvideos') == '1' || ($config->get('showlatestvideos') == '2' && $my->id != 0 ) ) { ?>
	<!-- Latest Video -->
    <div class="app-box" id="latest-videos">
        <div class="app-box-header">
        	<h2 class="app-box-title"><?php echo JText::_('CC VIDEOS'); ?></h2>
            <div class="app-box-menus">
                <div class="app-box-menu toggle">
                    <a class="app-box-menu-icon"
                       href="javascript: void(0)"
                       onclick="joms.apps.toggle('#latest-videos');"><span class="app-box-menu-title"><?php echo JText::_('CC EXPAND');?></span></a>
                </div>
            </div>
        </div>
        
        <div class="app-box-content">
            <div id="latest-videos-nav" class="filterlink">
                <div style="float: right;">
                    <a class="newest-videos active-state" href="javascript:void(0);"><?php echo JText::_('CC NEWEST VIDEOS') ?></a>
                    <a class="featured-videos" href="javascript:void(0);"><?php echo JText::_('CC FEATURED VIDEOS') ?></a>
                    <a class="popular-videos" href="javascript:void(0);"><?php echo JText::_('CC POPULAR VIDEOS') ?></a>
                </div>
                <div class="loading"></div>
            </div>
        	
        	<div id="latest-videos-container" class="clrfix">
            <?php echo $latestVideosHTML;?>
        	</div>

        <div class="app-box-footer no-border">
            <a href="<?php echo CRoute::_('index.php?option=com_community&view=videos'); ?>"><?php echo JText::_('CC VIEW ALL VIDEOS'); ?></a>
        </div>
        </div>
    </div>
    <!-- Latest Video -->
    <?php } ?>
	<?php } ?>
	
	<?php if( $config->get('showactivitystream') == '1' || ($config->get('showactivitystream') == '2' && $my->id != 0 ) ) { ?>
	<!-- Recent Activities -->
    <div class="app-box" id="recent-activities">
    	<div class="app-box-header">
	        <h2 class="app-box-title"><?php echo JText::_('CC RECENT ACTIVITIES'); ?></h2>
            <div class="app-box-menus">
                <div class="app-box-menu toggle">
                    <a class="app-box-menu-icon"
                       href="javascript: void(0)"
                       onclick="joms.apps.toggle('#recent-activities');">
                        <span class="app-box-menu-title"><?php echo JText::_('CC EXPAND');?></span>
                    </a>
                </div>
            </div>
		</div>
		
		<!--start: Custom Activities -->
 		<?php echo $customActivityHTML; ?>
 		<!--end: Custom Activities -->
 		
        <div class="app-box-content">
			<?php if($alreadyLogin==1): ?>
            <div id="activity-stream-nav" class="filterlink">
                <div style="float: right;">
                    <a class="all-activity<?php echo $config->get('frontpageactivitydefault') == 'all' ? ' active-state': '';?>" href="javascript:void(0);"><?php echo JText::_('CC SHOW ALL') ?></a>
                    <a class="me-and-friends-activity<?php echo $config->get('frontpageactivitydefault') == 'friends' ? ' active-state': '';?>" href="javascript:void(0);"><?php echo JText::_('CC ME AND FRIENDS') ?></a>
                </div>
                <div class="loading"></div>
            </div>
            <?php endif; ?>
            
            <div style="position: relative;">
                <div id="activity-stream-container">
                <?php echo $userActivities; ?>
                </div>
            </div>
        </div>
    </div>
	<!-- Recent Activities -->
    <?php } ?>
</div>
<!-- .frontpage-main -->

<div style="clear: right;"></div>

</div>
<!-- .frontpage -->