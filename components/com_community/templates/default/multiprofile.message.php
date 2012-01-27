<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @param	applications	An array of applications object
 * @param	pagination		JPagination object 
 */
defined('_JEXEC') or die();
?>
<?php if( $multiprofile->approvals && !$isCommunityAdmin ){ ?>
	<div><?php echo JText::sprintf('CC PROFILE CHANGE REQUIRE APPROVALS INFO' , $multiprofile->name );?></div>
	<div style="margin-top: 5px;"><a href="<?php echo CRoute::_('index.php?option=com_community&view=frontpage');?>"><?php echo JText::_('CC RETURN TO FRONTPAGE');?></a></div>
<?php } else { ?>
	<div><?php echo JText::sprintf('CC PROFILE CHANGE INFO' , $multiprofile->name );?></div>
	<div style="margin-top: 5px;"><a href="<?php echo CRoute::_('index.php?option=com_community&view=profile');?>"><?php echo JText::_('CC RETURN TO PROFILE');?></a></div>
<?php } ?>