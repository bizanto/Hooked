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
<form action="index.php?option=com_friendmanager&view=friends" method="post" name="adminForm">
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'ID' ); ?>
			</th>			
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>			
			<th>
				<?php echo JText::_( 'Connect From' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'Connect To' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'Status' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'Group' ); ?>
			</th>
			<th>
				<?php echo JText::_( 'Created' ); ?>
			</th>												
			<th>
				<?php echo JText::_( 'Message' ); ?>
			</th>	
			<th width="5">
				<?php echo JText::_( 'CID' ); ?>
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
		$fromo =& JFactory::getUser($row->connect_from);
		$fromo->id != 0 ? $from = $fromo->name : $from = 'ID#'.$row->connect_from.' has been Deleted';		
		
		$too =& JFactory::getUser($row->connect_to);
		$too->id != 0 ? $to = $too->name : $to = 'ID#'.$row->connect_to.' has been Deleted';
		$checked 	= JHTML::_('grid.id',   $i, $row->connection_id );
		$link 		= JRoute::_( 'index.php?option=com_friendmanager&controller=friendmanager&task=edit&cid[]='. $row->connection_id );

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $this->pagination->getRowOffset( $i ); ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $from; ?></a>
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $to; ?></a>
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $row->status; ?></a>
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $row->group; ?></a>
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $row->created; ?></a>
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo $row->msg; ?></a>
			</td>
			<td>
				<?php echo $row->connection_id; ?>
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
<input type="hidden" name="controller" value="friendmanager" />
</form>
