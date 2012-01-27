<?php
/**
 * @author     Kevin Strong
 * @email      kevin.strong@wearehathway.com
 * @website    http://www.wearehathway.com
 * @copyright  Copyright (C) 2011 Hathway
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
?>

<div class="topCatches">
<?php foreach ($catches as $catch): ?>
	<div class="topCatchItemWrap">
		<div class="topCatchItem">
			<h5><a href="<?php echo ContentHelperRoute::getArticleRoute($catch->id, $catch->catid, $catch->sectionid); ?>"><?php echo $catch->title; ?></a></h5>
			<a class="topCatchImage <?php if ($catch->image) echo 'hasImage'; ?>" href="<?php echo ContentHelperRoute::getArticleRoute($catch->id, $catch->catid, $catch->sectionid); ?>"><?php if ($catch->image): ?><img src="<?php echo $catch->image; ?>" alt="<?php echo $catch->caption; ?>" /><?php endif; ?></a>
			
			<div class="topCatchUser">
				<a class="topCatchAvatar" href="<?php echo JRoute::_('index.php?option=com_community&view=profile&userid='.$catch->userid.'&Itemid=48'); ?>"><img src="<?php echo $catch->avatar; ?>" alt="<?php echo $catch->username; ?>" /></a>
				<a class="topCatchUsername" href="<?php echo JRoute::_('index.php?option=com_community&view=profile&userid='.$catch->userid.'&Itemid=48'); ?>"><?php echo $catch->username; ?></a><br />
				<span class="topCatchDate"><?php echo date('d.m.Y', strtotime($catch->jr_startdate)); ?></span>
			</div>
		</div>
	</div>
<?php endforeach; ?>
</div>
