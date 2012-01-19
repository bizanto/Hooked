<?php
/**
 * @category	Core
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<fieldset class="adminform">
	<legend><?php echo JText::_( 'CC LIKES RATING' ); ?></legend>
	<table class="admintable" cellspacing="1">
		<tbody>
			<tr>
				<td width="300" class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC GROUPS' ); ?>::<?php echo JText::_('CC LIKES FOR GROUPS TIPS'); ?>">
						<?php echo JText::_( 'CC GROUPS' ); ?>
					</span>
				</td>
				<td valign="top">
					<?php echo JHTML::_('select.booleanlist' , 'likes_groups' , null , $this->config->get('likes_groups') , JText::_('CC YES') , JText::_('CC NO') ); ?>
				</td>
			</tr>
			<tr>
				<td width="300" class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC EVENTS' ); ?>::<?php echo JText::_('CC LIKES FOR EVENTS TIPS'); ?>">
						<?php echo JText::_( 'CC EVENTS' ); ?>
					</span>
				</td>
				<td valign="top">
					<?php echo JHTML::_('select.booleanlist' , 'likes_events' , null , $this->config->get('likes_events') , JText::_('CC YES') , JText::_('CC NO') ); ?>
				</td>
			</tr>
			<tr>
				<td width="300" class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC PHOTOS' ); ?>::<?php echo JText::_('CC LIKES FOR PHOTOS TIPS'); ?>">
						<?php echo JText::_( 'CC PHOTOS' ); ?>
					</span>
				</td>
				<td valign="top">
					<?php echo JHTML::_('select.booleanlist' , 'likes_photo' , null , $this->config->get('likes_photo') , JText::_('CC YES') , JText::_('CC NO') ); ?>
				</td>
			</tr>
			<tr>
				<td width="300" class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC VIDEOS' ); ?>::<?php echo JText::_('CC LIKES FOR VIDEOS TIPS'); ?>">
						<?php echo JText::_( 'CC VIDEOS' ); ?>
					</span>
				</td>
				<td valign="top">
					<?php echo JHTML::_('select.booleanlist' , 'likes_videos' , null , $this->config->get('likes_videos') , JText::_('CC YES') , JText::_('CC NO') ); ?>
				</td>
			</tr>
			<tr>
				<td width="300" class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC PROFILE' ); ?>::<?php echo JText::_('CC LIKES FOR PROFILE TIPS'); ?>">
						<?php echo JText::_( 'CC PROFILE' ); ?>
					</span>
				</td>
				<td valign="top">
					<?php echo JHTML::_('select.booleanlist' , 'likes_profile' , null , $this->config->get('likes_profile') , JText::_('CC YES') , JText::_('CC NO') ); ?>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>