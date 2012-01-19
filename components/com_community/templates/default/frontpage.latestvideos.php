<?php
/**
 * @package		JomSocial
 * @subpackage 	Template
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
?>
<?php
foreach( $data as $video )
{
?>
<div class="video-items video-item jomTips tipFullWide" id="<?php echo "video-" . $video->getId() ?>" title="<?php echo $this->escape($video->title) . '::' . $this->escape( CStringHelper::truncate($video->description , VIDEO_TIPS_LENGTH ) ); ?>">
    <div class="video-item">
        <div class="video-thumb">
            <a class="video-thumb-url" href="<?php echo $video->getURL(); ?>" style="width: <?php echo $thumbWidth; ?>px; height:<?php echo $thumbHeight; ?>px;">
				<img src="<?php echo $video->getThumbNail(); ?>" style="width: <?php echo $thumbWidth; ?>px; height:<?php echo $thumbHeight; ?>px;" alt="<?php echo $video->getTitle(); ?>" />
			</a>
            <span class="video-durationHMS"><?php echo $video->getDurationInHMS(); ?></span>
        </div>
        <div class="video-summary">
            <div class="video-title">
            	<a href="<?php echo $video->getURL(); ?>"><?php echo $video->title; ?></a>
            </div>
            <div class="video-details small">
                <div class="video-hits"><?php echo JText::sprintf('CC VIDEO HITS COUNT', $video->getHits()) ?></div>
                <div class="video-lastupdated">
					<?php echo JText::sprintf('CC VIDEO LAST UPDATED', $video->getLastUpdated() ); ?>
				</div>
                <div class="video-creatorName">
					<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$video->creator); ?>">
						<?php echo $video->getCreatorName(); ?>
					</a>
				</div>
            </div>
        </div>
        <div class="clr"></div>
	</div>
</div>
<?php } ?>