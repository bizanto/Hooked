<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @param	posted	boolean	Determines whether the current state is a posted event.
 */
defined('_JEXEC') or die();
?>

<ul class="jsTogSearch">
	<li>
		<div>
		<form name="jsform-events-search" method="get" action="<?php echo $url; ?>">
			<input type="text" class="inputbox" name="search" value="" />
			<?php echo JHTML::_( 'form.token' ) ?>
			<input type="hidden" value="com_community" name="option" />
			<input type="hidden" value="events" name="view" />
			<input type="hidden" value="search" name="task" />
			<input type="hidden" value="<?php echo CRoute::getItemId();?>" name="Itemid" />
			<input type="submit" value="<?php echo JText::_('CC SEARCH'); ?>" class="button">
		</form>
		</div>		
		<a href="index.php?option=com_community&view=events&task=search"><?php echo JText::_('CC EVENT ADVANCE SEARCH'); ?></a>
	</li>
</ul>	