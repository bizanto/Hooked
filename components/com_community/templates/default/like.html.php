<?php
/**
 * @package	JomSocial
 * @subpackage 	Template 
 * @copyright	(C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license	GNU/GPL, see LICENSE.php
 * 
 */
defined('_JEXEC') or die();
?>
<?php if(COwnerHelper::isRegisteredUser()){ ?>
<span id="<?php echo $likeId ?>" class="like-snippet">
	
	<?php if( $likes > 0 ){ ?>
		<?php if( $userLiked==COMMUNITY_LIKE ) { ?>
			<a class="meLike" href="javascript:void(0);" onclick="joms.like.unlike(this);" title="<?php echo JText::_('CC LIKE ITEM'); ?>. <?php echo JText::_('CC UNLIKE'); ?>?"><?php echo $likes; ?></a>
		<?php } else { ?>
			<a class="like-button" href="javascript:void(0);" onclick="joms.like.like(this)" title="<?php echo JText::_('CC I LIKE'); ?>"><?php echo $likes; ?></a>
		<?php } ?>
	<?php } else { ?>
		<a class="like-button" href="javascript:void(0);" onclick="joms.like.like(this)"><?php echo JText::_('CC LIKE'); ?></a>
	<?php } ?>
	
	<?php if( $dislikes > 0 ){ ?>
		<?php if( $userLiked==COMMUNITY_DISLIKE ) { ?>
			<a class="meDislike" href="javascript:void(0);" onclick="joms.like.undislike(this);" title="<?php echo JText::_('CC DISLIKE ITEM'); ?>. <?php echo JText::_('CC UNDISLIKE'); ?>?"><?php echo $dislikes; ?></a>
		<?php } else { ?>
			<a class="dislike-button" href="javascript:void(0);" onclick="joms.like.dislike(this);" title="<?php echo JText::_('CC DISLIKE'); ?>"><?php echo $dislikes; ?></a>
		<?php } ?>	
	<?php } else { ?>
		<a class="dislike-button" href="javascript:void(0);" onclick="joms.like.dislike(this);"><?php echo $dislikes; ?></a>
	<?php } ?>

</span>
<?php } ?>