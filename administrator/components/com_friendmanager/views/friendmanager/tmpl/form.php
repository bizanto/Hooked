<?php
/**
 * @version $Id$
 * @package    FriendManager
 * @subpackage _ECR_SUBPACKAGE_
 * @author     Socialable Studios {@link http://www.Socialables.com}
 * @author     Created on 16-Jan-2010
 * @copyright	Copyright (C) 2005 - 2010 Socialables.com All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

//-- No direct access
defined('_JEXEC') or die('=;)');

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

		<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="connect_from">
					<?php echo JText::_( 'Connect From' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="connect_from" id="connect_from" size="32" maxlength="250" value="<?php echo $this->FriendManager->connect_from;?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="connect_to">
					<?php echo JText::_( 'Connect To' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="connect_to" id="connect_to" size="32" maxlength="250" value="<?php echo $this->FriendManager->connect_to;?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="status">
					<?php echo JText::_( 'Status' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="status" id="status" size="32" maxlength="250" value="<?php echo $this->FriendManager->status;?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="group">
					<?php echo JText::_( 'Group' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="group" id="group" size="32" maxlength="250" value="<?php echo $this->FriendManager->group;?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="created">
					<?php echo JText::_( 'Created' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="created" id="created" size="32" maxlength="250" value="<?php echo $this->FriendManager->created;?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="msg">
					<?php echo JText::_( 'Message' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="msg" id="msg" size="32" maxlength="250" value="<?php echo $this->FriendManager->msg;?>" />
			</td>
		</tr>										
	</table>
	</fieldset>
</div>
<div class="clr"></div>

<input type="hidden" name="option" value="com_friendmanager" />
<input type="hidden" name="id" value="<?php echo $this->FriendManager->connection_id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="friendmanager" />
</form>