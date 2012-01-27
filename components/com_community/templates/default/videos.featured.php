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

<?php
if ($videos)
{
?>
	<div class="videos cRow">
		<div class="ctitle"><?php echo JText::_('CC FEATURED VIDEOS');?></div>
		<?php
		foreach($videos as $video)
		{
		?>
		<div class="jomTips" style="float: left;" id="<?php echo "video-" . $video->getId() ?>" title="<?php echo $this->escape($video->title) . '::' . $this->escape($video->description); ?>">
			<div class="video-thumb">
				<a class="video-thumb-url" href="<?php echo $video->getURL(); ?>" style="width: <?php echo $videoThumbWidth; ?>px; height:<?php echo $videoThumbHeight; ?>px;">
					<img src="<?php echo $video->getThumbnail(); ?>" alt="<?php echo $this->escape($video->title);?>" style="width: <?php echo $videoThumbWidth; ?>px; height:<?php echo $videoThumbHeight; ?>px;"/>
				</a>
				<span class="video-durationHMS"><?php echo $video->getDurationInHMS(); ?></span>
			</div>
			<div class="clr"></div>
            <?php
			if( $isCommunityAdmin )
			{
			?>
			<div class="icon-removefeatured">
	            <a onclick="joms.featured.remove('<?php echo $video->getId();?>','videos');" href="javascript:void(0);">	            	            
	            <?php echo JText::_('CC REMOVE FEATURED'); ?>
	            </a>
	        </div>
	        <?php
	        }
	        ?>
		</div>
		<?php
		}
		?>
		<div class="clr"></div>
	</div>
<?php
}