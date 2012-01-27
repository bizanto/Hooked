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
<ul class="cThumbList clrfix">
	<?php
	foreach($members as $member) 
	{
	?>
	<li><a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$member->id );?>"><img class="avatar jomTips" src="<?php echo $member->getThumbAvatar(); ?>" title="<?php echo cAvatarTooltip($member); ?>" width="45" height="45" alt="<?php echo $this->escape( $member->getDisplayName() ) ?>"/></a></li>
	<?php
	}
	?>
</ul>