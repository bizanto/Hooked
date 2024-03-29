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

<?php echo @$header; ?>

<script type="text/javascript"> joms.filters.bind();</script>
<?php echo $adminControlHTML; ?>


<!-- begin: #cProfileWrapper -->
<div id="cProfileWrapper">

	<!-- begin: .cLayout -->
	<div class="cLayout clrfix">
		<?php $this->renderModules( 'js_profile_top' ); ?>	

		<!-- begin: .cSidebar -->
	    <div class="cSidebar clrfix">
			<?php $this->renderModules( 'js_side_top' ); ?>
			<?php $this->renderModules( 'js_profile_side_top' ); ?>
			<?php echo $sidebarTop; ?>
			
			<?php echo $about; ?>
			<?php echo $friends; ?>
			<?php if( $config->get('enablegroups')){ ?>
			<?php echo $groups; ?>
			<?php } ?>
			<?php echo $sidebarBottom; ?>
			<?php $this->renderModules( 'js_profile_side_bottom' ); ?>
			<?php $this->renderModules( 'js_side_bottom' ); ?>		
	    </div>
	    <!-- end: .cSidebar -->
	    
        <!-- begin: .cMain -->
	    <div class="cMain">
	    
			<div class="page-actions">
				<?php echo $reportsHTML;?>
				<?php echo $bookmarksHTML;?>
				<?php echo $blockUserHTML;?>
				<div style="clear: right;"></div>
			</div>
					

			
			<?php $this->renderModules( 'js_profile_feed_top' ); ?>
				<!-- Recent Activities -->
				<div class="app-box" id="recent-activities">
			        <div class="app-box-header">
			        <div class="app-box-header">
			            <h2 class="app-box-title"><?php echo JText::_('CC RECENT ACTIVITIES'); ?></h2>
			            <div class="app-box-menus">
			                <div class="app-box-menu toggle">
			                    <a class="app-box-menu-icon"
			                       href="javascript: void(0)"
			                       onclick="joms.apps.toggle('#recent-activities');"><span class="app-box-menu-title"><?php echo JText::_('CC EXPAND');?></span></a>
			                </div>
			            </div>
					</div>
			        </div>
			        
			        <div class="app-box-content">
				
						<div id="activity-stream-nav" class="filterlink">
						    <div style="float: right;">
								<a class="p-active-profile-and-friends-activity active-state" href="javascript:void(0);"><?php echo JText::sprintf('CC PROFILE OWNER AND FRIENDS' , $profileOwnerName );?></a>
								<a class="p-active-profile-activity" href="javascript:void(0);"><?php echo $profileOwnerName ?></a>
							</div>
							<div class="loading"></div>
						</div>
						
						<div style="position: relative;">
							<div id="activity-stream-container">
						  	<?php echo $newsfeed; ?>
						  	</div>
						</div>
					
					</div>
				</div>
				
				<?php $this->renderModules( 'js_profile_feed_bottom' ); ?>
				<?php echo $content; ?>
		</div>
	    <!-- end: .cMain -->
	    
		<?php $this->renderModules( 'js_profile_bottom' ); ?>	    
	</div>
	<!-- end: .cLayout -->

</div>
<!-- begin: #cProfileWrapper -->

<?php /* Insert plugin javascript at the bottom */ echo $jscript; ?>