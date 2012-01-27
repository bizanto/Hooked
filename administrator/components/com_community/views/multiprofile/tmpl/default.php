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
<script type="text/javascript" language="javascript">
/**
 * This function needs to be here because, Joomla toolbar calls it
 **/ 
function submitbutton( action )
{
	submitform( action );
}
</script>
<form action="index.php?option=com_community" method="post" name="adminForm">
<table class="adminlist" cellspacing="1">
	<thead>
		<tr class="title">
			<th width="1%">#</th>
			<th width="1%">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->profiles ); ?>);" />
			</th>
			<th width="15%" style="text-align: left;">
				<?php echo JText::_('CC NAME');?>
			</th>
			<th style="text-align: left;">
				<?php echo JText::_('CC DESCRIPTION');?>
			</th>
			<th width="5%">
				<?php echo JText::_('CC TOTAL USERS');?>
			</th>
			<th width="1%">
				<?php echo JText::_('CC PUBLISHED');?>
			</th>
			<th width="10%">
				<?php echo JText::_( 'CC CREATED' );?>
			</th>
		</tr>
	</thead>
	<?php $i = 0; ?>
	<?php
		if( empty( $this->profiles ) )
		{
	?>
	<tr>
		<td colspan="7" align="center"><?php echo JText::_('CC NO PROFILE CREATED YET');?></td>
	</tr>
	<?php
		}
		else
		{
	?>
	<?php foreach( $this->profiles as $row ): ?>
	<tr>
		<td align="center">
			<?php echo ( $i + 1 ); ?>
		</td>
		<td>
			<?php echo JHTML::_('grid.id', $i++, $row->id); ?>
		</td>
		<td>
			<a href="<?php echo JRoute::_('index.php?option=com_community&view=multiprofile&layout=edit&id=' . $row->id ); ?>">
				<?php echo $row->name; ?>
			</a>
		</td>
		<td>
			<?php echo $row->description; ?>
		</td>
		<td align="center">
			<?php echo $this->getTotalUsers( $row->id );?>
		</td>
		<td id="published<?php echo $row->id;?>" align="center">
			<?php echo $this->getPublish( $row , 'published' , 'multiprofile,ajaxTogglePublish' );?>
		</td>
		<td align="center">
			<?php echo $row->created; ?>
		</td>
	</tr>
	<?php endforeach; ?>
	<?php } ?>
	<tfoot>
	<tr>
		<td colspan="7">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tr>
	</tfoot>
</table>
<input type="hidden" name="view" value="multiprofile" />
<input type="hidden" name="option" value="com_community" />
<input type="hidden" name="task" value="multiprofile" />
<input type="hidden" name="boxchecked" value="0" />
</form>