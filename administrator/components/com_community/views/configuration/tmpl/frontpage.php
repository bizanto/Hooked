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
	<legend><?php echo JText::_( 'CC FRONTPAGE' ); ?></legend>
	<table class="admintable" cellspacing="1">
		<tbody>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC FRONTPAGE TITLE' ); ?>::<?php echo JText::_('CC FRONTPAGE TITLE TIPS'); ?>">
					<?php echo JText::_( 'CC FRONTPAGE TITLE' ); ?>
					</span>
				</td>
				<td valign="top">
					<input type="text" name="sitename" value="<?php echo $this->config->get('sitename');?>" size="40" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC REDIRECT LOGIN' ); ?>::<?php echo JText::_('CC REDIRECT LOGIN TIPS'); ?>">
					<?php echo JText::_( 'CC REDIRECT LOGIN' ); ?>
					</span>
				</td>
				<td valign="top">
					<select name="redirect_login">
						<option value="profile"<?php echo $this->config->get('redirect_login') == 'profile' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC PROFILE');?></option>
						<option value="frontpage"<?php echo $this->config->get('redirect_login') == 'frontpage' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC FRONTPAGE');?></option>
						<option value="videos"<?php echo $this->config->get('redirect_login') == 'videos' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC VIDEOS');?></option>
						<option value="photos"<?php echo $this->config->get('redirect_login') == 'photos' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC PHOTOS');?></option>
						<option value="friends"<?php echo $this->config->get('redirect_login') == 'friends' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC FRIENDS');?></option>
						<option value="apps"<?php echo $this->config->get('redirect_login') == 'apps' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC APPLICATIONS');?></option>
						<option value="inbox"<?php echo $this->config->get('redirect_login') == 'inbox' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC INBOX');?></option>
						<option value="groups"<?php echo $this->config->get('redirect_login') == 'groups' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC GROUPS');?></option>
						<option value="events"<?php echo $this->config->get('redirect_login') == 'events' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC EVENTS');?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC REDIRECT LOGOUT' ); ?>::<?php echo JText::_('CC REDIRECT LOGOUT TIPS'); ?>">
					<?php echo JText::_( 'CC REDIRECT LOGOUT' ); ?>
					</span>
				</td>
				<td valign="top">
					<select name="redirect_logout">
						<option value="profile"<?php echo $this->config->get('redirect_logout') == 'profile' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC PROFILE');?></option>
						<option value="frontpage"<?php echo $this->config->get('redirect_logout') == 'frontpage' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC FRONTPAGE');?></option>
						<option value="videos"<?php echo $this->config->get('redirect_logout') == 'videos' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC VIDEOS');?></option>
						<option value="photos"<?php echo $this->config->get('redirect_logout') == 'photos' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC PHOTOS');?></option>
						<option value="friends"<?php echo $this->config->get('redirect_logout') == 'friends' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC FRIENDS');?></option>
						<option value="apps"<?php echo $this->config->get('redirect_logout') == 'apps' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC APPLICATIONS');?></option>
						<option value="inbox"<?php echo $this->config->get('redirect_logout') == 'inbox' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC INBOX');?></option>
						<option value="groups"<?php echo $this->config->get('redirect_logout') == 'groups' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC GROUPS');?></option>
						<option value="events"<?php echo $this->config->get('redirect_logout') == 'events' ? ' selected="selected"' : ''; ?>><?php echo JText::_('CC EVENTS');?></option>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>