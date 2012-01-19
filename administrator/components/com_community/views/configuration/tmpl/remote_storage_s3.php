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
	<legend><?php echo JText::_( 'CC AMAZONS3' ); ?></legend>
	<table class="admintable" cellspacing="1">
		<tbody>
			<tr>
				<td width="350" class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC BUCKET PATH' ); ?>::<?php echo JText::_('CC BUCKET PATH TIPS'); ?>">
						<?php echo JText::_( 'CC BUCKET PATH' ); ?>
					</span>
				</td>
				<td valign="top">
					<input type="text" name="storages3bucket" value="<?php echo $this->config->get('storages3bucket' , '' );?>" size="50" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC ACCESS KEY' ); ?>::<?php echo JText::_('CC ACCESS KEY TIPS'); ?>">
						<?php echo JText::_( 'CC ACCESS KEY' ); ?>
					</span>
				</td>
				<td valign="top">
					<input type="text" name="storages3accesskey" value="<?php echo $this->config->get('storages3accesskey' , '' );?>" size="50" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC SECRET KEY' ); ?>::<?php echo JText::_('CC SECRET KEY TIPS'); ?>">
						<?php echo JText::_( 'CC SECRET KEY' ); ?>
					</span>
				</td>
				<td valign="top">
					<input type="text" name="storages3secretkey" value="<?php echo $this->config->get('storages3secretkey' , '' );?>" size="50" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC AMAZON STORAGE CLASS' ); ?>::<?php echo JText::_('CC AMAZON STORAGE CLASS TIPS'); ?>">
						<?php echo JText::_( 'CC AMAZON STORAGE CLASS' ); ?>
					</span>
				</td>
				<td valign="top">
					<select name="amazon_storage_class">
						<option <?php echo ( $this->config->get('amazon_storage_class') == 'STANDARD' ) ? 'selected="true"' : ''; ?> value="STANDARD"><?php echo JText::_('CC STANDARD');?></option>
						<option <?php echo ( $this->config->get('amazon_storage_class') == 'REDUCED_REDUNDANCY' ) ? 'selected="true"' : ''; ?> value="REDUCED_REDUNDANCY"><?php echo JText::_('CC REDUCED REDUNDANCY');?></option>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
</fieldset>