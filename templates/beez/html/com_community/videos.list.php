<?php
/**
 * @package        JomSocial
 * @subpackage     Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * 
 */
defined('_JEXEC') or die();
?>

<?php if ($videos) { ?>
<div class="video-items">
<?php foreach($videos as $video) { ?>

<div class="video-item jomTips tipFullWidth" id="<?php echo "video-" . $video->getId() ?>" title="<?php echo $this->escape( $video->getTitle() ). '::' . $this->escape( CStringHelper::truncate($video->description , VIDEO_TIPS_LENGTH )); ?>">
<div class="video-item">
		<!---VIDEO THUMB-->
    <div class="video-thumb">      
            <a class="video-thumb-url" href="<?php echo $video->getURL(); ?>" style="width: <?php echo $videoThumbWidth; ?>px; height:<?php echo $videoThumbHeight; ?>px;"><img src="<?php echo $video->getThumbnail(); ?>" width="<?php echo $videoThumbWidth; ?>" height="<?php echo $videoThumbHeight; ?>" alt="" /></a>
        <?php if (!$video->isPending()): ?>   
            <span class="video-durationHMS"><?php echo $video->getDurationInHMS(); ?></span>
        <?php endif; ?>                
    </div>
		<!---end: VIDEO THUMB-->
		
		<!---VIDEO SUMMARY-->
    <div class="video-summary">
        <div class="video-title">
            <?php
            if ($video->isPending()) {
                echo $video->getTitle();
            } else {
            ?>
                <a href="<?php echo $video->getURL(); ?>"><?php echo $video->getTitle(); ?></a>
            <?php } ?>
        </div>
        
        <div class="video-details small">
            <div class="video-lastupdated"><?php echo JText::sprintf('CC VIDEO LAST UPDATED', $video->getLastUpdated() );?></div>
            <?php if ( (!$video->isOwner() && !$groupVideo) || ($groupVideo && !$allowManageVideos) ) { ?>
            <div class="video-creatorName"><a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$video->creator); ?>"><?php echo $video->getCreatorName(); ?></a></div>
            <?php } ?>
        </div>
    
        <?php if ( $isCommunityAdmin || ($video->isOwner() && !$groupVideo) || ($groupVideo && $allowManageVideos) ) { ?>
        <div class="video-actions small">
            <a class="video-action edit" href="javascript:void(0);" onclick="joms.videos.showEditWindow('<?php echo $video->getId(); ?>', '<?php echo $redirectUrl;?>');"><span><?php echo JText::_('CC EDIT') ?></span></a> | 
            <a class="video-action delete" href="javascript:void(0);" onclick="joms.videos.deleteVideo('<?php echo $video->getId();?>','<?php echo $redirectUrl;?>');"><span><?php echo JText::_('CC DELETE') ?></span></a>


			<?php
			if( $isCommunityAdmin && !$groupVideo && !JRequest::getCmd('task') )
			{
				if( !in_array($video->id, $featuredList) )
				{
			?>
					| <span id="featured-<?php echo $video->getId(); ?>">	            
			            <a onclick="joms.featured.add('<?php echo $video->getId(); ?>', 'videos');" href="javascript:void(0);">	            	            
			            <?php echo JText::_('CC MAKE FEATURED'); ?>
			            </a>
			        </span>
			<?php			
				}
			}
			?>
			
        </div>
        <?php } ?>
    </div>
    <!---end: VIDEO SUMMARY-->
    <div class="clr"></div>
</div>
<!---end: VIDEO ITEM-->
</div>
<!---end: VIDEO ITEM-->

<?php } ?>
</div>
<!---end: VIDEO ITEM(S)-->
<div class="clr"></div>

<?php 
} else {
    $task	= JRequest::getVar('task');
	switch ($task)
	{
		case 'mypendingvideos':
			$msg	= JText::_('CC NO PENDING VIDEOS');
			break;
		case 'search':
			$msg	= JText::_('CC NO RESULT');
			break;
		case 'myvideos':
			$isMine	= ($user->id==$my->id);
			$msg	= $isMine ? JText::_('CC NO VIDEOS') : JText::sprintf('CC USER NO VIDEOS', $user->getDisplayName());
			break;
		default:
			$msg	= JText::_('CC NO VIDEOS');
			break;
	}
	?>
		<div class="video-not-found"><?php echo $msg; ?></div>
	<?php
}
?>
<div style="clear: both;"></div>
<?php if (!is_null($pagination)) {?>
<div class="pagination-container">
	<?php echo $pagination->getPagesLinks(); ?>
</div>
<?php }?>