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

<div class="albums">
<?php
if( $albums )
{   
		$i	= 0;
        foreach($albums as $album)
		{
?>
	<div class="album">
	<div class="album jomTips tipFullWidth" title="<?php echo $this->escape($album->name);?>::<?php echo $this->escape( $album->description );?>">
    	<div class="album-cover">
        	<a class="album-cover-link" href="<?php echo $album->link; ?>"><img src="<?php echo $album->thumbnail; ?>" alt="<?php echo $this->escape($album->name);?>" class="avatar"/></a>
        </div>

        <div class="album-summary">
        	<div class="album-name"><a href="<?php echo $album->link; ?>"><?php echo $this->escape($album->name); ?></a></div>
            <div class="album-count"><?php echo JText::sprintf('CC PHOTOS COUNT', $album->count ); ?>&nbsp;<?php echo JText::_('CC PHOTO ALBUM BY'); ?> <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$album->creator); ?>"><?php echo $album->user->getDisplayName(); ?></a></div>
			<div class="album-lastupdated small"><?php echo JText::sprintf('CC ALBUM LAST UPDATED', $album->lastupdated);?></div>
        </div>

        <div class="album-actions small">
            <?php if($album->isOwner) { ?>
                <a class="album-action edit" href="<?php echo $albums[$i]->editLink; ?>"><?php echo JText::_('CC ALBUM ACTION EDIT');?></a> | 
                <a class="album-action upload"href="<?php echo $albums[$i]->uploadLink; ?>"><?php echo JText::_('CC ALBUM ACTION UPLOAD');?></a> |
                <?php if (0 && $album->permanent != 1) { ?>
                <a class="album-action delete" href="javascript:void(0);" onclick="cWindowShow('jax.call(\'community\',\'photos,ajaxRemoveAlbum\',\'<?php echo $albums[$i]->id;?>\',\'<?php echo $currentTask; ?>\');' , '<?php echo JText::_('CC REMOVE');?>' , 450 , 150 );"><?php echo JText::_('CC ALBUM ACTION DELETE');?></a>
                <?php } ?>
            <?php } elseif($isSuperAdmin) { ?>
            	<a class="album-action edit" href="<?php echo $albums[$i]->editLink; ?>"><?php echo JText::_('CC ALBUM ACTION EDIT');?></a> |
                <?php /* <a class="album-action delete" href="javascript:void(0);" onclick="cWindowShow('jax.call(\'community\',\'photos,ajaxRemoveAlbum\',\'<?php echo $album->id;?>\',\'<?php echo $currentTask; ?>\');' , '<?php echo JText::_('CC REMOVE');?>' , 450 , 150 );"><?php echo JText::_('CC ALBUM ACTION DELETE');?></a> */ ?>
            <?php } // end: foreach($albums as $album) ?>
            
				<?php
				if( $isCommunityAdmin && $showFeatured && $type == PHOTOS_USER_TYPE )
				{
					if( !in_array($album->id, $featuredList) )
					{
				?>
		            | <a class="album-action" onclick="joms.featured.add('<?php echo $album->id;?>','photos');" href="javascript:void(0);">	            	            
		            <?php echo JText::_('CC MAKE FEATURED'); ?>
		            </a>
				<?php			
					}
				}
				?>
				
        </div>
	</div> 
  </div>  
	<?php
			$i++;
		}
}
else
{
?>
	<div class="community-empty-list">
		<?php echo JText::_('CC NO ALBUM'); ?>
		<?php if( $isOwner ){ ?><a href="<?php echo $createLink;?>"><?php echo JText::_('CC CREATE ALBUM NOW');?></a><?php } ?>
	</div>
<?php
}
?>
	<div class="clr"></div>
</div>
<div class="clr"></div>
<div class="pagination-container">
	<?php echo $pagination->getPagesLinks(); ?>
</div>