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
//$ordering = ($this->lists['order'] == 'a.ordering');
?>
<form action="index.php?option=com_friendmanager&view=backups" method="post" name="adminForm">
<table>
<tr>
	<td align="left" width="100%">
		<?php echo JText::_( 'Filter' ); ?>:
		<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
		<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
		<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_catid').value='0';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
	</td>
</tr>
</table>
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'NUM' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>			
			<th width="40" class="title">
				<?php echo JHTML::_('grid.sort',  'Connection', 'id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>	
			<th width="100" >
				<?php echo JHTML::_('grid.sort',  'Backup Date', 'backupdate', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
			<th width="60" >
				<?php echo JText::_( 'Message' ); ?>
			</th>					
		</tr>			
	</thead>
	<tfoot>
		<tr>
			<td colspan="9">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>	
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];			
		$checked 	= JHTML::_('grid.id',   $i, $row->id );		
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $this->pagination->getRowOffset( $i ); ?>
			</td>		
			<td>
				<?php echo $checked; ?>
			</td>		
			<td>
				<?php echo $row->id; ?>
			</td>	
			<td>
				<?php echo $row->backupdate; ?>
			</td>
			<td>
				<?php echo $row->msg; ?>
			</td>							
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
</tbody>
	</table>
</div>

	<input type="hidden" name="option" value="com_friendmanager" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="backups" />
</form>
