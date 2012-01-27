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
<ul class ="cDetailList clrfix">
<?php
foreach( $photos as $photo )
{
?>
	<li class="avatarWrap">
		<a href="<?php echo $photo->getPhotoLink();?>">
			<img alt="<?php echo $this->escape($photo->caption);?>" src="<?php echo $photo->getThumbURI();?>" class="avatar jomTips" title="<?php echo $photo->caption; ?>" />
		</a>
	</li>
<?php
}
?>
</ul>