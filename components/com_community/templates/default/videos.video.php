<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * 
 */
defined('_JEXEC') or die();
?>

<div class="page-actions">
    <?php echo $reportHTML;?>
    <?php echo $bookmarksHTML;?>
    <div class="clr"></div>
</div>

<div class="video-full" id="<?php echo "video-" . $video->getId() ?>">
		<!--VIDEO PLAYER-->
    <div class="video-player">
			<?php echo $video->getPlayerHTML(); ?>
    </div>
    <!--end: VIDEO PLAYER-->
		
		
		
		
		<div class="cLayout clrfix">
			<div class="vidSubmenu clrfix">
				<!--VIDEO LINK-->
				<div class="video-permalink">
	                <div class="video-label">
	                    <label for="video-permalink"><?php echo JText::_('CC VIDEO PERMALINK') ?> :</label>
	                </div>
	                <div class="video-link">
	                    <input id="video-permalink" type="text" readonly="" onclick="joms.jQuery(this).focus().select()" value="<?php echo $video->getPermalink(); ?>" name="video_link" />
	                </div>
				</div>
				<!--end: VIDEO LINK-->
			
				<div class="">
					<ul class="submenu">
						<li><span><?php echo JText::_('CC VIDEO CREATE DATE') ?> <strong><?php echo JHTML::_('date', $video->created, JText::_('DATE_FORMAT_LC3')); ?></strong></span></li>
						<li><span><?php echo JText::_('CC VIDEO DURATION') ?> <strong><?php echo $video->getDurationInHMS(); ?></strong></span></li>
						<li><span><?php echo JText::_('CC VIDEO WALL POSTS') ?> <strong><?php echo $video->getWallCount(); ?></strong></span></li>
					</ul>
					
					<div id="like-container">
						<?php echo $likesHTML; ?>
					</div>
				</div>
			</div>
			
			<div class="cRow">
				<div class="ctitle"><h2><?php echo JText::_('CC PROFILE VIDEO DESCRIPTION'); ?></h2></div>
				<p class="video-description"><?php echo $video->getDescription(); ?></p>
			</div>
		</div>
		
		
		
    <!--<div class="video-summary" style="margin-left: <?php echo $video->getWidth(); ?>px">	-->
		
	
   
    <div class="clr"></div>

    
    
             
    
    <div class="ctitle"><?php echo JText::_('CC COMMENTS') ?></div>
    <div class="video-wall">
		<div id="wallForm"><?php echo $wallForm; ?></div>			
        <div id="wallContent"><?php echo $wallContent; ?></div>
    </div>
</div>
