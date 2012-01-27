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
foreach( $videos as $video )
{
?>
	<li class="avatarWrap">
		<a href="<?php echo $video->getURL();?>">
			<img alt="<?php echo $this->escape($video->title);?>" src="<?php echo $video->getThumbnail();?>" class="avatar jomTips" title="<?php echo $video->title; ?>" />
		</a>
	</li>
<?php
}
?>
</ul>