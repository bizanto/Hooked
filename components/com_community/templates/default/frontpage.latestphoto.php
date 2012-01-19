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
	<div class="app-box-content">
		<h3><span><?php echo JText::_('CC NEW PHOTOS'); ?></span></h3>
		<ul class="cThumbList clrfix">
		<?php
		if( !$latestPhotos )
		{
		?>
			<li><?php echo JText::_('CC NO PHOTOS UPLOADED YET');?></li>
		<?php
		}
		else
		{
			for( $i = 0 ; $i < count( $latestPhotos ); $i++ )
			{
				$row	=& $latestPhotos[$i];
		?>
		<li>
		<a href="<?php echo CRoute::_('index.php?option=com_community&view=photos&task=photo&albumid=' . $row->albumid .  '&userid=' . $row->user->id) . '#photoid=' . $row->id;?>"><img class="avatar jomTips" width="45" height="45" title="<?php echo $this->escape($row->caption);?>::<?php echo JText::sprintf('CC PHOTO UPLOADED BY' , $row->user->getDisplayName() );?>" src="<?php echo $row->getThumbURI(); ?>" alt="<?php echo $this->escape( $row->user->getDisplayName() );?>" /></a>
		</li>
		<?php
			}
		}
		?>
		</ul>
	</div>
	
    <div class="app-box-footer">
        <a href="<?php echo CRoute::_('index.php?option=com_community&view=photos'); ?>"><?php echo JText::_('CC VIEW ALL PHOTOS'); ?></a>
    </div>