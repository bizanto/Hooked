 <?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>
<div id="community-event-join">
	<?php if($isMember){ ?>
		<p><?php echo JText::_('CC ALREADY MEMBER OF EVENT'); ?></p>
	<?php }else{ ?>
		<p><?php echo JText::sprintf('CC CONFIRM INVITATION REQUEST', $event->title );?></p>
	<?php } ?>
</div>