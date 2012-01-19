<?php
/**
* @package SimpleCaddy 1.75 for Joomla 1.5
* @copyright Copyright (C) 2006-2011 Henk von Pickartz. All rights reserved.
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class shipping_HTML{
/* cos i cant be bothered formatting the moneys every time, this does all the right/left align of currency symbol as well. */
	function displayMoney($value){
		$config = shipping::getConfig();
	
		if($config['rightAlignCurency']=='1'){
			$formatted =  number_format($value,$config['decs'],$config['dsep'],$config['tsep']).' '.$config['currency'];
		}else{ 
			$formatted =  $config['currency']." ".number_format($value,$config['decs'],$config['dsep'],$config['tsep']);
		}
		return $formatted;
	}
	
	
	function editShipping($row){
		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
        $edit = ($cid!=array(0));
		$text = ( $edit ? JText::_( 'Edit' ) : JText::_( 'New' ) );
		JToolBarHelper::title( JText::_( "SimpleCaddy $text" ), 'generic.png'); 

		JToolBarHelper::save( 'save', 'Save' );
		JToolBarHelper::apply();
		if ( $edit ) {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
		} else {
			JToolBarHelper::cancel();
		}
		?>
		<fieldset class='adminForm'>
			<legend><?php echo JText::_( 'SC_SHIPPING_EDIT_TITLE')?></legend>
			<form method="get" name="adminForm" action="index2.php">
				<table class="adminform" width="100%">
					<tr>
						<td width="185"><?php echo JText::_( 'SC_SHIPPING_EDIT_NAME')?></td>
						<td><input type="text" name="name" value="<?php echo $row->name?>" maxlength="13"/></td>
					</tr>
					<tr>
						<td width="185"><?php echo JText::_( 'SC_SHIPPING_EDIT_LOWER')?></td>
						<td><input type="text" name="points_lower" value="<?php echo $row->points_lower?>" maxlength="13"/></td>
					</tr>
					<tr>
						<td width="185"><?php echo JText::_( 'SC_SHIPPING_EDIT_UPPER')?></td>
						<td><input type="text" name="points_upper" value="<?php echo $row->points_upper?>" maxlength="13"/></td>
					</tr>
					<tr>
						<td width="185"><?php echo JText::_( 'SC_SHIPPING_EDIT_PRICE')?></td>
						<td><input type="text" name="price" value="<?php echo $row->price?>" maxlength="13"/></td>
					</tr>
				</table>
				<input type='hidden' name='id' value='<?php echo $row->id; ?>' />
				<input type="hidden" name="option" value="com_caddy" />
				<input type="hidden" name="action" value="shipping" />
				<input type="hidden" name="task" value="" />
			</form>
		</fieldset>
		
		<?php
	}
	
	function viewShippingZones($rows, $msg = null){
		JToolBarHelper::title( JText::_( 'SimpleCaddy Shipping Zones' )); 
		JToolBarHelper::addnew('add');
		JToolbarHelper::save( 'edit', JText::_( 'edit' ) );
		JToolBarHelper::deleteList();
		JToolBarHelper::custom( 'control', 'back.png', 'back.png', 'Main', false,  false );
		if($msg){
		?>
			<fieldset>
				<legend><?php echo JText::_( 'SC_SHIPPING_NOTICE')?></legend>
				<?php if($msg)echo $msg;?>
			</fieldset>
		<?php } ?>
		
			<fieldset class='adminForm'>	
				<legend><?php echo JText::_( 'SC_SHIPPING_ZONE_VIEW')?></legend>
				<form name='adminForm' method='get' action='index2.php'>
					<table class='adminlist'>
					<thead >
						<tr >
							<th class="title" nowrap="nowrap"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" /></th>
							<th class="title" nowrap="nowrap"><?php echo JText::_( 'SC_SHIPPING_EDIT_ID')?></th>
							<th class="title" nowrap="nowrap"><?php echo JText::_( 'SC_SHIPPING_EDIT_NAME')?></th>
							<th class="title" nowrap="nowrap"><?php echo JText::_( 'SC_SHIPPING_EDIT_LOWER')?></th>
							<th class="title" nowrap="nowrap"><?php echo JText::_( 'SC_SHIPPING_EDIT_UPPER')?></th>
							<th class="title" nowrap="nowrap"><?php echo JText::_( 'SC_SHIPPING_EDIT_PRICE')?></th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<?php
					$k = 0;
					for ($i=0, $n=count( $rows ); $i < $n; $i++) {
						$row = &$rows[$i];
					?>
						<tr class="<?php echo "row$k"; ?>">
						<td width="20"><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);" /></td>
						<td width="40">
							<a href="index2.php?cid[]=<?php echo $row->id?>&option=com_caddy&action=shipping&task=edit&hidemainmenu=1" >
								<?php echo $row->id; ?>
							</a>
						</td>
						<td width="150">
							<a href="index2.php?cid[]=<?php echo $row->id?>&option=com_caddy&action=shipping&task=edit&hidemainmenu=1" >
								<?php echo $row->name; ?>
							</a>
						</td>
						<td width="150"><?php echo $row->points_lower; ?></td>
						<td width="150"><?php echo $row->points_upper; ?></td>
						<td width="200"><?php echo shipping_HTML::displayMoney($row->price); ?></td>
						<td>&nbsp;</td>
						</tr>
						<?php $k = 1 - $k; ?>
						<?php } ?>
					</table>
					<input type="hidden" name="option" value="com_caddy" />
					<input type="hidden" name="action" value="shipping" />
					<input type="hidden" name="task" value="" />
					<input type="hidden" name="boxchecked" value="0" />
					<input type="hidden" name="hidemainmenu" value="1" />
				</form>
			</fieldset>
		<?php
	}	
}

?>