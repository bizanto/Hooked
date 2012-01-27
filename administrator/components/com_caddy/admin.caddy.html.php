<?php
/**
* @package SimpleCaddy 1.75 for Joomla 1.5
* @copyright Copyright (C) 2006-2011 Henk von Pickartz. All rights reserved.
*/
// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

class display {
	function showExport() {
		display::header();
		?>
		<form method="post" name="adminForm" action="index2.php">
			<input type="hidden" name="option" value="com_caddy" />
			<input type="hidden" name="action" value="orders" />
			<input type="hidden" name="task" value="show" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
		echo "Your export can be downloaded : <a href='components/com_caddy/exports/export.txt'>here</a>";
	}

	function MainMenu($message="") {
		display::header();
		JToolBarHelper::title( JText::_( 'SimpleCaddy Control Center' )); 
	?>
		<table border="1" class="adminform">
		<tr>
		<td width="55%" valign="top">

		<div id="cpanel">

			<div style="float:left;">
				<div class="icon">
				<a title="<?php echo JText::_('SC_PRODUCTS'); ?>" href="index2.php?option=com_caddy&amp;action=products&amp;task=show" >
				<img src="components/com_caddy/images/products.png" alt="<?php echo JText::_('SC_MANAGE_PRODUCTS');?>" align="middle" name="image" border="0" /><br />
				<?php echo JText::_('SC_PRODUCTS'); ?></a>
				</div>
			</div>

			<div style="float:left;">
				<div class="icon">
				<a title="<?php echo JText::_('SC_ORDERS'); ?>" href="index2.php?option=com_caddy&amp;action=orders&amp;task=show">
				<img src="components/com_caddy/images/orders.png" alt="<?php echo JText::_('SC_MANAGE_ORDERS');?>" align="middle" name="image" border="0" /><br />
				<?php echo JText::_('SC_ORDERS'); ?></a>
				</div>
			</div>

			<div style="float:left;">
				<div class="icon">
				<a title="<?php echo JText::_('SC_VOUCHERS'); ?>" href="index2.php?option=com_caddy&amp;action=vouchers&amp;task=show">
				<img src="components/com_caddy/images/vouchers.png" alt="<?php echo JText::_('SC_MANAGE_VOUCHERS');?>" align="middle" name="image" border="0" /><br />
				<?php echo JText::_('SC_VOUCHERS'); ?></a>
				</div>
			</div>

			<div style="float:left;">
				<div class="icon">
				<a title="<?php echo JText::_('SC_OPTION_FIELDS'); ?>" href="index2.php?option=com_caddy&amp;action=fields&amp;task=show">
				<img src="components/com_caddy/images/fields.png" alt="<?php echo JText::_('SC_MANAGE_FIELDS');?>" align="middle" name="image" border="0" /><br />
				<?php echo JText::_('SC_OPTION_FIELDS'); ?></a>
				</div>
			</div>

			<div style="float:left;">
				<div class="icon">
				<a title="<?php echo JText::_('SC_CONFIGURATION'); ?>" href="index2.php?option=com_caddy&amp;action=configuration&amp;task=show">
				<img src="components/com_caddy/images/config.png" alt="<?php echo JText::_('SC_CONFIGURATION');?>" align="middle" name="image" border="0" /><br />
				<?php echo JText::_('SC_CONFIGURATION'); ?></a>
				</div>
			</div>

			<div style="float:left;">
				<div class="icon">
				<a title="<?php echo JText::_('SC_SHIPPING'); ?>" href="index2.php?option=com_caddy&amp;action=shipping&amp;task=show">
				<img src="components/com_caddy/images/shipping.png" alt="<?php echo JText::_('SC_SHIPPING');?>" align="middle" name="image" border="0" /><br />
				<?php echo JText::_('SC_SHIPPING'); ?></a>
				</div>
			</div>

			<div style="float:left;">
				<div class="icon">
				<a title="<?php echo JText::_('SC_PG'); ?>" href="index2.php?option=com_caddy&amp;action=scphocag&amp;task=show">
				<img src="components/com_caddy/images/p-pg.png" alt="<?php echo JText::_('SC_PG');?>" align="middle" name="image" border="0" /><br />
				<?php echo JText::_('SC_PG'); ?></a>
				</div>
			</div>

			<div style="float:left;">
				<div class="icon">
				<a title="About" href="index2.php?option=com_caddy&amp;action=about&amp;task=show">
				<img src="components/com_caddy/images/about.png" alt="About" align="middle" name="image" border="0" /><br />
				About</a>
				</div>
			</div>
		</div>

		</td>
		<td valign="top">
		<?php
			echo "$message";
		?>
		</td>
		</tr>
		</table>
	<?php
	}

	function AFFooter() {
		?>
		<div style="margin-top: 10px;"><div align="center">
		<?php echo JText::_('SC_FOR_MORE_INFORMATION_CLICK_HERE');?>
		<a href="http://atlanticintelligence.net" target="_blank"><?php echo JText::_('SC_INFORMATION');?></a>
		<br /><a href="http://demo15.atlanticintelligence.net" target="_blank"><?php echo JText::_('SC_CLICK_HERE_FOR_DEMO');?></a>
		</div></div>
		<?php
	}

	function showAbout() {
		global $mainframe;
		jimport('joomla.filesystem.path');
		display::header();
		JToolBarHelper::title( JText::_( 'SimpleCaddy' )); 
		JToolBarHelper::custom( 'control', 'back.png', 'back.png', 'Main', false,  false );
	?>
		<form name="adminForm">
		<input type="hidden" name="task" />
		<input type="hidden" name="option" value="com_caddy" />
		</form>
		<div align="left">
		<h2><img src="components/com_caddy/images/sc_logo15.png"  />SimpleCaddy 1.75 for Joomla 1.5.x</h2>
		Adds basic shopping cart functionality to any page of Joomla content.
		<br/>Featuring
		  <ul>
			  <li>Add to cart</li>
			  <li>Simple shop mechanism</li>
			  <li>Simple order management</li>
			  <li>Simple store management</li>
			  <li>Checkout by email</li>
			  <li>Checkout and payment through PayPal</li>
			  <li>Individual item options</li>
			  <li>Formulas to define prices of individual options</li>
			  <li>Vouchers or Coupons</li>
		  </ul>
		  </div>
		  <p>SimpleCaddy (c)Henk von Pickartz, 2006-2011</p>
		  <p>Shipping module (c)Cole Diffin, 2010</p>
	<?php
	}
	
	function showSortArrows($fieldname) {
		echo "<a href=\"javascript:submitme('$fieldname,ASC')\"><img src=\"images/uparrow.png\" border=\"0\" /></a>";
		echo "<a href=\"javascript:submitme('$fieldname,DESC')\"><img src=\"images/downarrow.png\" border=\"0\" /></a>";
	}

	function showProducts(&$arows, $field=null, $order=null) {
	global $mainframe, $mosConfig_list_limit, $mosConfig_absolute_path;
		display::header();
		JToolBarHelper::title( JText::_( 'SimpleCaddy Products' )); 
//		JToolBarHelper::custom( 'duplicate', 'copy.png', 'copy_f2.png', 'Duplicate', false,  false );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::custom( 'control', 'back.png', 'back.png', 'Main', false,  false );
		$cfg=new sc_configuration();
		$currency=$cfg->get("currency");
		$tsep=$cfg->get("thousand_sep");
		$decsep=$cfg->get("decimal_sep");
		$decs=$cfg->get("decimals");
		$pageNav=$arows['nav'];
		$rows=$arows['lst'];
		$lists=$arows['lists'];
		$search=JRequest::getVar("search");
		?>
		<script language="javascript">
			function submitme(option) {
			a=option.split(",");
			document.adminForm.field.value=a[0];
			document.adminForm.order.value=a[1];
			document.adminForm.submit();
			}
		</script>
		<form method="post" name="adminForm" action="index2.php">

			<table>
				<tr>
					<td width="100%">
						<?php echo JText::_( 'Filter' ); ?>:
						<?php echo $lists['category'];?>
					</td>
				</tr>
			</table>


				<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
					<tr>
						<th width="20">
							<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
						</th>

						<th class="title" nowrap="nowrap"><?php echo JText::_('SC_CODE') ?>&nbsp;<?php echo display::showSortArrows("prodcode");?></th>
						<th class="title" nowrap="nowrap"><?php echo JText::_('SC_DESCRIPTION') ?>&nbsp;<?php echo display::showSortArrows("shorttext");?></th>
						<th class="title" nowrap="nowrap"><?php echo JText::_('SC_PRODUCT_CATEGORY') ?>&nbsp;<?php echo display::showSortArrows("category");?></th>
						<th class="title tdright" nowrap="nowrap"><?php echo JText::_('SC_PRICE_PER_UNIT') ?>&nbsp;<?php echo display::showSortArrows("unitprice");?></th>
						<th class="title tdright" nowrap="nowrap"><?php echo JText::_('SC_NUM_IN_STORE') ?>&nbsp;<?php echo display::showSortArrows("av_qty");?></th>
						<th class="title tdcenter" nowrap="nowrap"><?php echo JText::_('SC_PUBLISHED') ?>&nbsp;<?php echo display::showSortArrows("published");?></th>
						<th class="title" nowrap="nowrap">&nbsp;</th>

					</tr>
				<?php
				$k = 0;
				for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];
				?>
					<tr class="<?php echo "row$k"; ?>">
						<td width="20">
							<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);" />
						</td>
						<td width="10%">
							<a href="#edit" onclick="return listItemTask('cb<?php echo $i;?>','edit')">
							<?php
							echo $row->prodcode; ?>
							</a>
						</td>
						<td width="40%">
							<?php echo $row->shorttext; ?>
						</td>
						<td width="40%">
							<?php echo $row->category; ?>
						</td>
						<td width="10%" class="tdright">
							<?php
							echo number_format($row->unitprice, $decs, $decsep, $tsep);
							?>
						</td>
						<td width="10%" class="tdright">
							<?php echo $row->av_qty; ?>
						</td>
						<td align="left" width="10%" class="tdcenter">
							<?php
								$published 	= JHTML::_('grid.published', $row, $i );
								echo $published;
							?>
						</td>
						<td>
							&nbsp;
						</td>
			<?php
				$k = 1 - $k; }

			?>
				</tr>
				<tr><td colspan="7">
					<?php
						echo $pageNav->getListFooter();
					?>
				</td></tr>
			</table>
			<input type="hidden" name="option" value="com_caddy" />
			<input type="hidden" name="action" value="products" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="field" value="<?php echo $field;?>" />
			<input type="hidden" name="order" value="<?php echo $order;?>" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}

	function showFields(& $arows ) {
	global $mainframe, $mosConfig_list_limit, $mosConfig_absolute_path;
		display::header();
		JToolBarHelper::title( JText::_( 'SimpleCaddy Checkout fields' )); 
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::custom( 'control', 'back.png', 'back.png', 'Main', false,  false );
	if (!$arows) {
		echo "Custom fields have not been installed in your SimpleCaddy, <a href='index2.php?option=com_caddy&action=update'>click here to Update</a>";
		return;
	}
	$pageNav=$arows['nav'];
	$rows=$arows['lst'];
	?>
		<form method="post" name="adminForm" action="index2.php">
				<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
					<tr>
						<th width="20">
							<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
						</th>
						<th class="title" nowrap="nowrap"><?php echo JText::_('SC_FIELDNAME') ?></th>
						<th class="title" nowrap="nowrap"><?php echo JText::_('SC_FIELDCAPTION') ?></th>
						<th class="title tdright" nowrap="nowrap"><?php echo JText::_('SC_FIELDTYPE') ?></th>
						<th class="title tdright" nowrap="nowrap"><?php echo JText::_('SC_FIELDLENGTH') ?></th>
						<th class="title tdcenter" nowrap="nowrap"><?php echo JText::_('SC_FIELDCLASS') ?></th>
						<th class="title tdcenter" nowrap="nowrap"><?php echo JText::_('SC_FIELDORDERING') ?></th>
						<th class="title tdcenter" nowrap="nowrap"><?php echo JText::_('SC_FIELDREQUIRED') ?></th>
						<th class="title tdcenter" nowrap="nowrap"><?php echo JText::_('SC_FIELDPUBLISHED') ?></th>
						<th class="title" nowrap="nowrap">&nbsp;</th>

					</tr>
				<?php
				$k = 0;
				for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];
				?>
					<tr class="<?php echo "row$k"; ?>">
						<td width="20">
							<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);" />
						</td>
						<td width="40%">
							<a href="#edit" onclick="return listItemTask('cb<?php echo $i;?>','edit')">
							<?php
							echo $row->name; ?>
							</a>
						</td>
						<td width="40%">
							<?php echo $row->caption; ?>
						</td>
						<td width="10%" class="tdright">
							<?php
							echo  JText::_($row->type);
							?>
						</td>
						<td width="10%" class="tdright">
							<?php echo $row->length; ?>
						</td>
						<td width="40%">
							<?php echo $row->classname; ?>
						</td>
						<td width="10%" class="tdright">
							<?php
							echo $row->ordering;
							?>
						</td>
						<td width="10%" class="tdright">
							<?php
							echo ($row->required?JText::_('Yes'):JText::_('No'));
							?>
						</td>
						<td align="left" width="10%" class="tdcenter">
							<?php
								$published 	= JHTML::_('grid.published', $row, $i );
								echo $published;
							?>
						</td>
						<td>
							&nbsp;
						</td>
			<?php
				$k = 1 - $k; }

			?>
				</tr>
				<tr><td colspan="9">
					<?php
						echo $pageNav->getListFooter();
					?>
				</td></tr>
			</table>
			<input type="hidden" name="option" value="com_caddy" />
			<input type="hidden" name="action" value="fields" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}

	function showVouchers(& $arows, $field=null, $order=null ) {
	global $mainframe, $mosConfig_list_limit, $mosConfig_absolute_path;
	display::header();
		JToolBarHelper::title( JText::_( 'SimpleCaddy Vouchers' )); 
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::custom( 'control', 'back.png', 'back.png', 'Main', false,  false );
	$pageNav=$arows['nav'];
	$rows=$arows['lst'];
	?>
		<script language="javascript">
			function submitme(option) {
			a=option.split(",");
			document.adminForm.field.value=a[0];
			document.adminForm.order.value=a[1];
			document.adminForm.submit();
			}
		</script>
		<form method="post" name="adminForm" action="index2.php">
				<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
					<tr>
						<th width="20">
							<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
						</th>
						<th class="title" nowrap="nowrap"><?php echo JText::_('SC_VOUCHERNAME') ?><?php echo display::showSortArrows("name");?></th>
						<th class="title" nowrap="nowrap"><?php echo JText::_('SC_VOUCHERFORMULA') ?></th>
						<th class="title tdcenter" nowrap="nowrap"><?php echo JText::_('SC_VOUCHERVALIDFROM') ?><?php echo display::showSortArrows("validfrom");?></th>
						<th class="title tdcenter" nowrap="nowrap"><?php echo JText::_('SC_VOUCHERVALIDTO') ?><?php echo display::showSortArrows("validto");?></th>
						<th class="title tdcenter" nowrap="nowrap"><?php echo JText::_('SC_VOUCHERGROUPNAME') ?><?php echo display::showSortArrows("lot");?></th>
						<th class="title tdcenter" nowrap="nowrap"><?php echo JText::_('SC_VOUCHERENABLED') ?><?php echo display::showSortArrows("published");?></th>
						<th class="title tdcenter" nowrap="nowrap"><?php echo JText::_('SC_VOUCHERAVAILABLE') ?><?php echo display::showSortArrows("avqty");?></th>
						<th class="title" nowrap="nowrap">&nbsp;</th>

					</tr>
				<?php
				$k = 0;
				for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];
				?>
					<tr class="<?php echo "row$k"; ?>">
						<td width="20">
							<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);" />
						</td>
						<td width="450">
							<a href="#edit" onclick="return listItemTask('cb<?php echo $i;?>','edit')">
							<?php
							echo $row->name; ?>
							</a>
						</td>
						<td width="100" class="tdright">
							<?php
							echo $row->formula;
							?>
						</td>
						<td width="150">
							<?php echo ($row->validfrom>0?date("d-m-Y h:i:s", $row->validfrom):JText::_("SC_NOT_SET")); ?>
						</td>
						<td width="150" class="tdright">
							<?php
							echo ($row->validto>0?date("d-m-Y h:i:s", $row->validto):JText::_("SC_NOT_SET"));
							?>
						</td>
						<td width="100" class="tdright">
							<?php
							echo $row->lot;
							?>
						</td>
						<td align="left" width="20" class="tdcenter">
							<?php
								$published 	= JHTML::_('grid.published', $row, $i );
								echo $published;
							?>
						</td>
						<td width="50" class="tdright">
							<?php
							echo $row->avqty;
							?>
						</td>
						<td width="10%">
							&nbsp;
						</td>
			<?php
				$k = 1 - $k; }

			?>
				</tr>
				<tr><td colspan="9">
					<?php
						echo $pageNav->getListFooter();
					?>
				</td></tr>
			</table>
			<input type="hidden" name="option" value="com_caddy" />
			<input type="hidden" name="action" value="vouchers" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="field" value="<?php echo $field;?>" />
			<input type="hidden" name="order" value="<?php echo $order;?>" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}

	function editProduct(&$a) {
	global $mainframe;
		$document	=& JFactory::getDocument();

		$document->addScript( JURI::root(true).'/administrator/components/com_caddy/js/caddy.js');
		display::header();

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

		$cfg=new sc_configuration();
		$currency=$cfg->get("currency");
		$tsep=$cfg->get("thousand_sep");
		$decsep=$cfg->get("decimal_sep");
		$decs=$cfg->get("decimals");
		$scats=$cfg->get("prodcats");
		$curalign=$cfg->get("curralign");
		$cats=explode("\r\n", $scats);
        $optiongroups=new optiongroups();
        $lstoptgroups=$optiongroups->getgroups($a->prodcode);
        
	?>

		<form method="post" name="adminForm" action="index2.php">
		<table class="adminform" width="100%"><tr><th><?php echo ($a->id ? JText::_('SC_EDIT') : JText::_('SC_NEW'))."&nbsp;".JText::_('SC_PRODUCT');?></th><th>&nbsp;</th></tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_PRODUCT_CODE');?></td>
			<td><input type="text" name="prodcode" value="<?php echo $a->prodcode; ?>" maxlength="13"/><?php echo JText::_('SC_MAX_10_CHAR');?></td>
		</tr>
		<tr>
			<td><?php echo JText::_('SC_PRODUCT_NAME');?></td>
			<td><input type="text" name="shorttext" value="<?php echo $a->shorttext; ?>"/></td>
		</tr>
		<tr>
			<td><?php echo JText::_('SC_PRODUCT_CATEGORY');?></td>
			<td>
			<select name="category">
			<?php
				foreach ($cats as $cat) {
					echo "<option value='$cat' ".($cat==$a->category ? ' selected' : '').">$cat</option>";
				}
			?>
			</select>
			</td>
		</tr>
		<tr>
			<td><?php echo JText::_('SC_AVAILABLE_QTY');?></td>
			<td>
			<input type="text" name="av_qty" value="<?php echo $a->av_qty; ?>" size="10"/>
			</td>
		</tr>
		<tr>
			<td><?php echo JText::_('SC_PRICE_PER_UNIT');?></td>
			<td>
			<?php
			if ($curalign==1) echo $currency;
			?>
			<input size="10" type="text" name="unitprice" value="<?php echo $a->unitprice; ?>"/> 
			<?php 
				if ($curalign==0) echo $currency;
				echo JText::_('Do not format your price');
			?> 
			</td>
		</tr>
		<tr>
			<td><?php echo JText::_('SC_SHIPPING_POINTS');?></td>
			<td>
			<input type="text" name="shippoints" value="<?php echo $a->shippoints; ?>" size="5"/>
			</td>
		</tr>
		<tr><td><?php echo JText::_('SC_PUBLISHED');?></td>
		<td>
			<?php
				$show_hide = array (JHTML::_('select.option', 0, JText::_('No')), JHTML::_('select.option', 1, JText::_('Yes')),);
				foreach ($show_hide as $value) {
					echo "<input type='radio' value='$value->value' name='published' ".($a->published==$value->value?' checked':'').">$value->text";
				}
			?>
		</td>
		</tr>
		</table>
        <?php // check if the product exists before adding options
            if ($a->id) { ?>
		<table class="adminform" border="1"><tr><th><?php echo JText::_('SC_OPTIONS');?>&nbsp;<input type="button" name="addbtn" onclick="submitbutton('addoptgroup')" value="<?php echo JText::_('Add Option');?>" /></th><th width="270"><?php echo JText::_('SC_SHOW_AS');?></th><th width="60"><?php echo JText::_('SC_ORDER');?></th><th width="120"><?php echo stripslashes( JText::_('SC_IND_OPTIONS'));?></th><th>&nbsp;</th></tr>
        <?php
        $showas=new optionsshowas();

            foreach ($lstoptgroups as $optgroup) {
                echo "\n<tr>";
                echo "<td><a class='modal' href='index2.php?option=com_caddy&action=optiongroups&task=show&optgrid=$optgroup->id&productid=$a->id&tmpl=component'>$optgroup->title</a></td>";
                echo "<td>";
                echo $showas->type[$optgroup->showas];
                echo "</td>";
                echo "<td>$optgroup->disporder</td>";
                if (($optgroup->showas !=5) and ($optgroup->showas !=6)) { // exclude the ones without options
                    echo "<td><a class='modal' href='index2.php?option=com_caddy&action=options&task=showindoptions&optgrid=$optgroup->id&tmpl=component&productid=$a->id'>".JText::_('SC_IND_OPTIONS')."</a></td>";
                }
                else
                { // no options? just display an empty cell for table alignment
                    echo "<td>&nbsp;</td>";
                }
                echo "<td><a class='button' href='index.php?option=com_caddy&action=optiongroups&task=remove&optgrid=$optgroup->id&productid=$a->id'>".JText::_('Remove option')."</td>";
                echo "</tr>";
            }
        ?>
        </table>
        <?php
        }
        else
        {
            echo JText::_("SC_SAVE_FIRST");
        }
        ?>

		<input type="hidden" name="id" value="<?php echo $a->id; ?>" />
		<input type="hidden" name="option" value="com_caddy" />
		<input type="hidden" name="action" value="products" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
	<?php
	}

	function editField(&$a) {
		display::header();
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
		<form method="post" name="adminForm" action="index2.php">
		<table><tr><td width="40%" valign="top">
		<table class="adminform" width="100%"><tr><th><?php echo ($a->id ? JText::_('SC_EDIT') : JText::_('SC_NEW'))."&nbsp;".JText::_('SC_OPTION_FIELDS');?></th><th>&nbsp;</th></tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_FIELDNAME');?></td>
			<td><input type="text" name="name" value="<?php echo $a->name; ?>" /></td>
		</tr>
		<tr>
			<td><?php echo JText::_('SC_FIELDCAPTION');?></td>
			<td><input type="text" name="caption" value="<?php echo $a->caption; ?>"/></td>
		</tr>
		<tr>
			<td><?php echo JText::_('SC_FIELDTYPE');?></td>
			<td>
			<select name="type">
				<option value="text" <?php echo $a->type == "text" ?"selected" : ""; ?>><?php echo JText::_('Text');?></option>
				<option value="textarea" <?php echo $a->type == "textarea" ? "selected" : ""; ?>><?php echo JText::_('Multiline Text');?></option>
				<option value="radio" <?php echo $a->type == "radio" ? "selected" : ""; ?>><?php echo JText::_('Yes/No');?></option>
				<option value="checkbox" <?php echo $a->type == "checkbox" ? "selected" : ""; ?>><?php echo JText::_('Checkbox');?></option>
				<option value="date" <?php echo $a->type == "date" ? "selected" : ""; ?>><?php echo JText::_('Date');?></option>
				<option value="dropdown" <?php echo $a->type == "dropdown" ? "selected" : ""; ?>><?php echo JText::_('Dropdown');?></option>
				<option value="divider" <?php echo $a->type == "divider" ? "selected" : ""; ?>><?php echo JText::_('Divider');?></option>
			</select>
			</td>
		</tr>
		<tr>
		<td><?php echo JText::_('SC_FIELDCONTENTS');?></td>
		<td><input type="text" name="fieldcontents" value="<?php echo $a->fieldcontents; ?>"/></td>
		</tr>
		<tr>
			<td><?php echo JText::_('SC_FIELDLENGTH');?></td>
			<td>
			<input type="text" name="length" value="<?php echo $a->length; ?>"/>
			</td>
		</tr>
		<tr><td><?php echo JText::_('SC_PUBLISHED');?></td>
		<td>
			<?php
				$show_hide = array (JHTML::_('select.option', 0, JText::_('No')), JHTML::_('select.option', 1, JText::_('Yes')),);
				foreach ($show_hide as $value) {
					echo "<input type='radio' value='$value->value' name='published' ".($a->published==$value->value?' checked':'').">$value->text";
				}
			?>
		</td>
		</tr>
		<tr>
		<td width="185"><?php echo stripslashes( JText::_('SC_FIELDCLASS'));?></td>
		<td>
		<input type="text" name="classname" value="<?php echo $a->classname; ?>" />
		</td>
		</tr>
		<tr>
		<td width="185"><?php echo stripslashes( JText::_('SC_FIELDORDERING'));?></td>
		<td>
		<input type="text" name="ordering" value="<?php echo $a->ordering; ?>" />
		</td>
		</tr>
		<tr><td><?php echo JText::_('SC_FIELDREQUIRED');?></td>
		<td>
			<?php
				$show_hide = array (JHTML::_('select.option', 0, JText::_('No')), JHTML::_('select.option', 1, JText::_('Yes')),);
				foreach ($show_hide as $value) {
					echo "<input type='radio' value='$value->value' name='required' ".($a->required==$value->value?' checked':'').">$value->text";
				}
			?>
		</td>
		</tr>
		</table>
		</td>
		<td width="60%" valign="top">
		<table class="adminform" width="100%">
		<tr><th colspan="2"><?php echo JText::_('SC_HELP');?></th></tr>
		<tr>
		<td valign="top" width="150"><?php echo JText::_("SC_FIELDNAME");?></td><td valign="top"><?php echo JText::_("SC_HELP_FIELDNAME");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('SC_FIELDCAPTION');?></td><td valign="top"><?php echo JText::_("SC_HELP_CAPTION");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('SC_FIELDTYPE');?></td><td valign="top"><?php echo JText::_("SC_HELP_TYPE");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('SC_FIELDCONTENTS');?></td><td valign="top"><?php echo JText::_("SC_HELP_FIELDCONTENTS");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('SC_FIELDLENGTH');?></td><td valign="top"><?php echo JText::_("SC_HELP_LENGTH");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('SC_PUBLISHED');?></td><td valign="top"><?php echo JText::_("SC_HELP_PUBLISHED");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('SC_FIELDCLASS');?></td><td valign="top"><?php echo JText::_("SC_HELP_CLASSNAME");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('SC_FIELDORDERING');?></td><td valign="top"><?php echo JText::_("SC_HELP_ORDER");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('SC_FIELDREQUIRED');?></td><td valign="top"><?php echo JText::_("SC_HELP_REQUIRED");?></td>
		</tr>
		</table>
		</td>
		</tr>
		</table>
		<input type="hidden" name="id" value="<?php echo $a->id; ?>" />
		<input type="hidden" name="option" value="com_caddy" />
		<input type="hidden" name="action" value="fields" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
	<?php
	}

	function editVoucher(&$a) {
		global $mainframe, $config;
		display::header();

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

		$script='<script language="javascript" type="text/javascript" src="'.JURI::base().'components/com_caddy/js/datetimepicker.js"></script>';
		$mainframe->addCustomHeadTag($script);
	?>
		<form method="post" name="adminForm" action="index2.php">
		<table width="100%">
		<tr><td valign="top">
		<table class="adminform" width="100%"><tr><th><?php echo ($a->id ? JText::_('SC_EDIT') : JText::_('SC_NEW'))."&nbsp;".JText::_('SC_VOUCHER');?></th><th>&nbsp;</th></tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_VOUCHERNAME');?></td>
			<td><input type="text" name="name" value="<?php echo $a->name; ?>" /></td>
		</tr>
		<tr>
			<td><?php echo JText::_('SC_VOUCHERFORMULA');?></td>
			<td><input type="text" name="formula" value="<?php echo $a->formula; ?>" /></td>
		</tr>
		<tr class="row1"><td><?php echo JText::_('SC_VOUCHERDATELIMITED');?></td>
		<td>
			<?php
				$show_hide = array (JHTML::_('select.option', 0, JText::_('No')), JHTML::_('select.option', 1, JText::_('Yes')),);
				foreach ($show_hide as $value) {
					echo "<input type='radio' value='$value->value' name='datelimited' ".($a->datelimited==$value->value?' checked':'').">$value->text";
				}
			?>
		</td>
		</tr>
		<tr class="row1">
			<td><?php echo JText::_('SC_VOUCHERVALIDFROM');?></td>
			<td>
			<input type="text" size="40" name="validfrom" id="validfrom" value="<?php echo ($a->validfrom?date("d-m-Y h:i:s", $a->validfrom):""); ?>"/>
			<?php echo "&nbsp;<a href=\"javascript:NewCal('validfrom','ddMMyyyy',true ,24)\"><img src=\"components/com_caddy/images/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"".JText::_("SC_PICK_DATE")."\"/></a>"; ?>
			</td>
		</tr>
		<tr class="row1">
			<td><?php echo JText::_('SC_VOUCHERVALIDTO');?></td>
			<td>
			<input type="text" size="40" name="validto" id="validto" value="<?php echo ($a->validto?date("d-m-Y h:i:s", $a->validto):""); ?>"/>
			<?php echo "&nbsp;<a href=\"javascript:NewCal('validto','ddMMyyyy',true ,24)\"><img src=\"components/com_caddy/images/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"".JText::_("SC_PICK_DATE")."\"/></a>"; ?>
			</td>
		</tr>

		<tr><td><?php echo JText::_('SC_VOUCHERQTYLIMITED');?></td>
		<td>
			<?php
				$show_hide = array (JHTML::_('select.option', 0, JText::_('No')), JHTML::_('select.option', 1, JText::_('Yes')),);
				foreach ($show_hide as $value) {
					echo "<input type='radio' value='$value->value' name='qtylimited' ".($a->qtylimited==$value->value?' checked':'').">$value->text";
				}
			?>
		</td>
		</tr>
		<tr>
			<td><?php echo JText::_('SC_VOUCHERAVQTY');?></td>
			<td>
			<input type="text" name="avqty" value="<?php echo $a->avqty; ?>"/>
			</td>
		</tr>


		<tr class="row1"><td><?php echo JText::_('SC_VOUCHERENABLED');?></td>
		<td>
			<?php
				$show_hide = array (JHTML::_('select.option', 0, JText::_('No')), JHTML::_('select.option', 1, JText::_('Yes')),);
				foreach ($show_hide as $value) {
					echo "<input type='radio' value='$value->value' name='published' ".($a->published==$value->value?' checked':'').">$value->text";
				}
			?>
		</td>
		</tr>
		<tr class="row1">
		<td width="185"><?php echo stripslashes( JText::_('SC_VOUCHERGROUPNAME'));?></td>
		<td>
		<input type="text" name="lot" value="<?php echo $a->lot; ?>" />
		</td>
		</tr>

		</table>
		</td>
		<td width="60%" valign="top">

		<table class="adminform" width="100%">
		<tr><th colspan="2"><?php echo JText::_('SC_HELP');?></th></tr>
		<tr>
		<td valign="top" width="150"><?php echo JText::_("SC_FIELDNAME");?></td><td valign="top"><?php echo JText::_("SC_HELP_FIELDNAME");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('Voucher formula');?></td><td valign="top"><?php echo JText::_("SC_HELP_VFORMULA");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('Date limited');?></td><td valign="top"><?php echo JText::_("SC_HELP_VDATELIMITED");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('Validity');?></td><td valign="top"><?php echo JText::_("SC_HELP_VVALIDITY");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('Quantity limited');?></td><td valign="top"><?php echo JText::_("SC_HELP_VQLIMITED");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('Available quantity');?></td><td valign="top"><?php echo JText::_("SC_HELP_VQANTITY");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('SC_ENABLED');?></td><td valign="top"><?php echo JText::_("SC_HELP_VENABLED");?></td>
		</tr>
		<tr>
		<td valign="top"><?php echo JText::_('Group');?></td><td valign="top"><?php echo JText::_("SC_HELP_VGROUP");?></td>
		</tr>
		</table>

		</td>
		</tr>
		</table>
		<input type="hidden" name="id" value="<?php echo $a->id; ?>" />
		<input type="hidden" name="option" value="com_caddy" />
		<input type="hidden" name="action" value="vouchers" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
	<?php
	}


	function showOrders( & $lists, $field=null, $order=null) {
	global $mainframe, $mosConfig_list_limit, $mosConfig_absolute_path;
		JToolBarHelper::title( JText::_( 'SimpleCaddy_Orders' )); 
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::custom( 'export', 'archive', 'archive' , 'Export', true,  false );
		JToolBarHelper::custom( 'control', 'back.png', 'back.png', 'Main', false,  false );

		$cfg=new sc_configuration();
		$currency=$cfg->get("currency");
		$tsep=$cfg->get("thousand_sep");
		$decsep=$cfg->get("decimal_sep");
		$decs=$cfg->get("decimals");
		$pageNav=$lists['nav'];
		$rows=$lists['lst'];

	?>
		<script language="javascript">
			function submitme(option) {
			a=option.split(",");
			document.adminForm.field.value=a[0];
			document.adminForm.order.value=a[1];
			document.adminForm.submit();
			}
		</script>
		<form method="post" name="adminForm" action="index2.php">
			<table>
				<tr>
					<td width="100%">
						<?php echo JText::_( 'Filter' ); ?>:
						<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
						<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
						<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
					</td>
				</tr>
			</table>
				<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
					<tr>
						<th width="20">
							<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
						</th>
						<th class="title" nowrap="nowrap">ID</th>
						<th class="title" nowrap="nowrap"><?php echo JText::_('SC_NAME') ?>&nbsp;<?php echo display::showSortArrows("name");?></th>
						<th class="title" nowrap="nowrap"><?php echo JText::_('SC_EMAIL') ?>&nbsp;<?php echo display::showSortArrows("email");?></th>
						<th class="title tdright" nowrap="nowrap"><?php echo JText::_('SC_DATE') ?>&nbsp;<?php echo display::showSortArrows("orderdt");?></th>
						<th class="title tdright" nowrap="nowrap"><?php echo JText::_('SC_TOTAL') ?>&nbsp;<?php echo display::showSortArrows("total");?></th>
						<th class="title" nowrap="nowrap"><?php echo JText::_('SC_ORDER_STATUS') ?>&nbsp;<?php echo display::showSortArrows("status");?></th>
						<th class="title" nowrap="nowrap">&nbsp;</th>
					</tr>
				<?php
				$k = 0;
				for ($i=0, $n=count( $rows ); $i < $n; $i++) {
				$row = &$rows[$i];
				?>
					<tr class="<?php echo "row$k"; ?>">
						<td width="20">
							<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);" />
						</td>
						<td width="20">
							<a href="#view" onclick="return listItemTask('cb<?php echo $i;?>','view')">
							<?php echo $row->id; ?>
							</a>
						</td>
						<td width="10%">
							<a href="#view" onclick="return listItemTask('cb<?php echo $i;?>','view')">
							<?php echo $row->name; ?>
							</a>
						</td>
						<td width="10%">
							<?php echo "<a href='mailto:$row->email'>$row->email</a>"; ?>
						</td>
						<td width="10%" class="tdright">
							<?php
							echo date("d-m-Y", $row->orderdt); ?>
						</td>
						<td width="10%" class="tdright">
						<?php
							echo number_format($row->total + $row->tax, $decs, $decsep, $tsep);
						?>
						</td>
						<td>
							<?php echo "<span class='".strtolower($row->status)."'>$row->status</span>"; ?>
						</td>
						<td>
							&nbsp;
						</td>
			<?php
				$k = 1 - $k; }

			?>
				</tr>
			</table>
			<?php
				echo $pageNav->getListFooter();
				$field=JRequest::getVar( 'field', '');
				$order=JRequest::getVar( 'order', '');
			?>
			<input type="hidden" name="option" value="com_caddy" />
			<input type="hidden" name="action" value="orders" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="field" value="<?php echo $field;?>" />
			<input type="hidden" name="order" value="<?php echo $order;?>" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="hidemainmenu" value="0" />
		</form>
		<?php
	}

	function editOrder($a, $items, $pageNav) {
	global $mainframe, $mosConfig_list_limit, $mosConfig_absolute_path;
		display::header();
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
    	$cfg=new sc_configuration();
    	$currency=$cfg->get("currency");
    	$tsep=$cfg->get("thousand_sep");
    	$decsep=$cfg->get("decimal_sep");
    	$decs=$cfg->get("decimals");
    	$align=$cfg->get("curralign"); // before amount==1
    	// hardcoded fields from old simplecaddy <1.7
    	$standardfields=array("name", "email", "address", "codepostal", "city", "telephone", "ipaddress" );
    	$statuses=explode("\n", $cfg->get("ostatus"));
	?>
		<form method="post" name="adminForm" action="index2.php">
		<table class="adminform" width="100%"><tr><th class="title"><?php echo JText::_('SC_ORDER');?></th><th><?php echo $a->id;?></th></tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_DATE');?></td>
			<td>
			<?php echo date("d-m-Y H:i:s", $a->orderdt); ?>
			</td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_USERID');?></td>
			<td><?php echo $a->j_user_id; ?></td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_NAME');?></td>
			<td><?php echo $a->name; ?></td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_EMAIL');?></td>
			<td><?php echo "<a href='mailto:$a->email'>$a->email</a>";?></td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_ADDRESS');?></td>
			<td>
			<?php echo $a->address; ?>
			</td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_ZIPCODE');?></td>
			<td>
			<?php echo $a->codepostal; ?>
			</td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_CITY');?></td>
			<td>
			<?php echo $a->city; ?>
			</td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_PHONE');?></td>
			<td>
			<?php echo $a->telephone; ?>
			</td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_IP_ADDRESS');?></td>
			<td>
			<?php 
	           $iplink = '&nbsp;<a href="http://whois.domaintools.com/'.$a->ipaddress.'" target="_blank" class="scbutton">'.JText::_("SC_CHECKIP")."</a>";
				echo $a->ipaddress;
				echo $iplink; 
 			?>
 			<input type="hidden" name="ipaddress" value="<?php echo $a->ipaddress;?>" />
			</td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_SHIP_REGION');?></td>
			<td>
			<?php
				echo $a->shipRegion; ?>
			</td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_SHIP_COST');?></td>
			<td>
			<?php
				if ($align==1) echo $currency. "&nbsp;";
				echo number_format($a->shipCost, $decs, $decsep, $tsep);
				if ($align==0) echo "&nbsp;". $currency; 
				?>
			</td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_SUBTOTAL');?></td>
			<td>
			<?php
				if ($align==1) echo $currency. "&nbsp;";
				echo number_format($a->total, $decs, $decsep, $tsep);
				if ($align==0) echo "&nbsp;". $currency;
				?> 
			</td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_TAX');?></td>
			<td>
			<?php
				if ($align==1) echo $currency. "&nbsp;";
				echo number_format($a->tax, $decs, $decsep, $tsep);
				if ($align==0) echo "&nbsp;". $currency;
				?> 
			</td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_TOTAL');?></td>
			<td>
			<?php
				if ($align==1) echo $currency. "&nbsp;";
				echo number_format($a->total + $a->tax, $decs, $decsep, $tsep);
				if ($align==0) echo "&nbsp;". $currency;
				?> 
			</td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_PAYMENT_ID');?></td>
			<td>
			<?php
				echo $a->ordercode;
				?> 
			</td>
		</tr>
		<?php
		if (@$a->customfields) {
			echo "<tr><th>".JText::_('Custom fields')."</th><th>&nbsp;</th></tr>";
			$fields=new fields();
			$fieldlist=$fields->getPublishedFieldsArray();
			$acfields=unserialize($a->customfields);

			foreach ($fieldlist as $key=>$cfield) {
				if (!in_array($cfield, $standardfields)) { // show only the fields that are not hardcoded
					if (isset($acfields[$cfield])) {
						echo "<tr>";
						echo "<td width=\"185\">$cfield</td>";
						echo "<td>".$acfields[$cfield]."</td>";
						echo "</tr>";
					}
				}
			}

		}
		?>
		<tr>
		<td><a href="index.php?option=com_caddy&action=orders&task=email&oid=<?php echo $a->id?>" class="scbutton"><?php echo JText::_('Resend order confirmation email');?></a></td>
		</tr>
		<tr>
			<td width="185"><?php echo JText::_('SC_ORDER_STATUS');?></td>
			<td>
			<?php
				echo "<select name='edtostatus'>";
				foreach ($statuses as $status) {
					$selected=(strtolower($a->status)==strtolower(trim($status))?" selected":"");
					echo "<option value='".trim($status)."' $selected>$status</option>\n";

				}
				echo "</select>";
			?>
			</td>
		</tr>
		</table>
		<table class="adminlist" width="100%" cellpadding="4" cellspacing="0" border="0" >
		<tr><th colspan="7"><?php echo JText::_('SC_DETAILS');?></th></tr>
		<tr>
			<th class="title"><?php echo JText::_('SC_CODE');?></th>
			<th class="title"><?php echo JText::_('SC_QUANTITY');?></th>
			<th class="title tdright"><?php echo JText::_('SC_PRICE_PER_UNIT');?></th>
			<th class="title tdright"><?php echo JText::_('SC_TOTAL');?></th>
			<th class="title"><?php echo JText::_('SC_PRODUCT_NAME');?></th>
			<th class="title"><?php echo JText::_('SC_PRODUCT_OPTION');?></th>
			<th class="title"><?php echo JText::_('SC_ACTION');?></th>
			<th class="title">&nbsp;</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $items ); $i < $n; $i++) {
		$row = &$items[$i];
		?>
			<tr class="<?php echo "row$k"; ?>">
				<td width="10%">
					<?php echo $row->prodcode; ?>
				</td>
				<td width="30">
					<?php
						echo $row->qty;
					?>
				</td>
				<td width="10%" class="tdright">
					<?php
						echo number_format($row->unitprice, $decs, $decsep, $tsep);
					?>
				</td>
				<td class="tdright">
					<?php
						echo number_format($row->total, $decs, $decsep, $tsep);
					?>
				</td>
				<td width="40%">
					<?php echo $row->shorttext; ?>
				</td>
				<td>
					<?php echo $row->option; ?>&nbsp;
				</td>
				<td>
					<?php
					echo "<a class=\"scbutton\" href=\"index2.php?option=com_caddy&action=products&task=decstore&pid=$row->prodcode&qty=$row->qty&order=$a->id\">".JText::_('SC_DECSTORE')."</a>";
					?>
				</td>
				<td>
					&nbsp;
				</td>
				<?php
					$k = 1 - $k; }
				?>
			</tr>
			<?php
				$field=JRequest::getVar( 'field', '');
				$order=JRequest::getVar( 'order', '');
			?>
		</table>
		<input type="hidden" name="id" value="<?php echo ($a->id?"$a->id":"-1"); ?>">
		<input type="hidden" name="option" value="com_caddy" />
		<input type="hidden" name="action" value="orders" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="order" value="<?php echo $order; ?>" />
		<input type="hidden" name="field" value="<?php echo $field; ?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
	<?php
	}

	function header() {
	global $mainframe;
        JHTML::_('behavior.modal');
		JHTML::stylesheet( 'simplecaddy.css', 'administrator/components/com_caddy/css/' );
	}

	function view_prod($alist) {
	global $mainframe;
    $document=JFactory::getDocument();
	$stylesheet= JPATH_COMPONENT.DS.'css'.DS.'simplecaddy.css';
	$document->addStyleSheet( $stylesheet );
    
	$e_name	=	JRequest::getVar( 'e_name' );  
		?>
		<link rel="stylesheet" href="<?php echo $stylesheet ?>" type="text/css" />
		<script type="text/javascript" language="javascript">
			function insertCode(plugincode) {
				var editor = '<?php echo $e_name; ?>';  
				var sccode = '{simplecaddy code='+plugincode;
                var compo=document.getElementById("component").value;
                if (compo=="com_phocagallery") {// special for PhocaGallery
                    var alias=window.parent.document.getElementById("alias").value;
                    sccode = sccode + " picname="+alias;    
                }
                var clssfx=document.getElementById("clssfx").value;
                if (clssfx!="") sccode = sccode + " classsfx="+clssfx;
                var defqty=document.getElementById("defqty").value;
                if (defqty!="") sccode = sccode + " defqty="+defqty;
                var minqty=document.getElementById("minqty").value;
                if (minqty!="") sccode = sccode + " minqty="+minqty;
                var qties=document.getElementById("qties").value;
                if (qties!="") sccode = sccode + " qties="+qties;
                var checkoos=document.getElementById("checkoos").value;
                if (checkoos!="") sccode = sccode + " checkoos=1";
                sccode = sccode + '}';
				window.parent.jInsertEditorText(sccode, editor);  
				window.parent.document.getElementById('sbox-window').close();
			}
			function insertCat(plugincode) {
				var editor = '<?php echo $e_name; ?>'; 
				var sccode = '{simplecaddy category='+plugincode+'}';
				window.parent.jInsertEditorText(sccode, editor); 
				window.parent.document.getElementById('sbox-window').close();
			}
		</script>
        <table class='codelist' width='100%'>
        <tr>
        <td>
            <?php 
                echo JText::_("SC_CLASS_SUFFIX");
            ?> 
        </td>
        <td><input type="text" name="clssfx" id="clssfx" /></td>
        </tr>
        <tr>
        <td>
            <?php 
                echo JText::_("SC_DEF_QTY");
            ?> 
        </td>
        <td><input type="text" name="defqty" id="defqty" /></td>
        </tr>
        <tr>
        <td>
            <?php 
                echo JText::_("SC_MIN_QTY");
            ?> 
        </td>
        <td><input type="text" name="minqty" id="minqty" /></td>
        </tr>
        <tr>
        <td>
            <?php 
                echo JText::_("SC_QTIES");
            ?> 
        </td>
        <td><input type="text" name="qties" id="qties" /></td>
        </tr>
        <tr>
        <td>
            <?php 
          		$cfg=new sc_configuration();
                if ($cfg->get("checkminqty") == "1") {
                    $disabled="";
                    echo JText::_("SC_CHECK_OOS");
                }
                else
                {
                    $disabled=" disabled";
                    echo JText::_("SC_NO_CHECK_OOS");
                }

            ?> 
        </td>
        <td>
            
            <input type="checkbox" name="checkoos" id="checkoos" <?php echo $disabled;?> /></td>
        </tr>
        </table>
        <table class='codelist' width='100%'>
        <input type="hidden" name="component" id="component" value="<?php echo JRequest::getVar("component");?>" />
		<?php
		echo "<tr><th>".JText::_('SC_CLICK_CODE')."</th></tr>";
		$k=0;
        
		foreach ($alist as $product) {
   			echo "<tr class='row$k'><td>$product->category&nbsp;<a class='codelist' href='#' onclick=\"insertCode('$product->prodcode');\">$product->shorttext (code: $product->prodcode)</a></td></tr>";
			$k=1-$k;
		}
        ?>
		</table>

        <?php
		$cfg=new sc_configuration();
		$aclist=$cfg->get("prodcats");
		$clist=explode("\r\n", $aclist);
		echo "<table class='codelist' width='100%'>";
		echo "<tr><th>".JText::_('SC_CLICK_CATEGORY')."</th></tr>";
		$k=0;
		foreach ($clist as $key=>$cat) {
			echo "<tr class='row$k'><td><a class='codelist' href='#' onclick=\"insertCat('$cat');\">$cat</a></td></tr>";
			$k=1-$k;
		}
		echo "</table>";
	}
    
    function showoptgroup($optiongroup, $prodid) { // shows option group edit screen
        $showas=new optionsshowas();
        ?>
        <p><h3><?php echo JText::_("SC_OPTIONGROUP");?></h3></p>
       <form name="frmoptgroup" action="index.php" method="post" target="_parent" > 
        <table border="0" width="100%">
        <tr><td>&nbsp;</td>
        <td align="right">
        <input type="button" onclick="document.frmoptgroup.submit();" value="<?php echo JText::_('SC_SAVE');?>" />
        </td>
        </tr>
        <tr>
        <td><?php echo JText::_('SC_GROUPTITLE');?></td>
        <td><input type="text" name="title" value="<?php echo $optiongroup->title; ?>" /></td>
        </tr>
        <tr>
        <td><?php echo JText::_('SC_DISPLAYORDER');?></td>
        <td><input name="disporder" size="1" value="<?php echo $optiongroup->disporder; ?>" type="text" /></td>
        </tr>
        <tr>
 		<td><?php echo JText::_('SC_SHOW_AS');?>
		</td>
		<td>
			<select name="showas">
            <?php
                foreach ($showas->type as $key=>$value) {
                    echo "\n<option value='$key' ".($optiongroup->showas==$key?" selected":"").">". $showas->type[$key] . " </option>";
                } 
            ?>
			</select>
		</td>
        </tr>
        
        </table>
        <input type="hidden" name="productid" value="<?php echo $prodid;?>" />
        <input type="hidden" name="id" value="<?php echo $optiongroup->id;?>" />
        <input type="hidden" name="prodcode" value="<?php echo $optiongroup->prodcode;?>" />
        <input type="hidden" name="option" value="com_caddy" />        
        <input type="hidden" name="action" value="optiongroups" />        
        <input type="hidden" name="task" value="saveoptiongroup" />        
        </form> 
        <?php
    }

    function showindoptions(&$rows, $optgrid, $productid) { // shows individual options
        $og=new optiongroups();
        $og->load($optgrid);
        ?>
        <form name="frmindoptions" method="post" action="index.php">
		<table border="1" class="adminform" width="100%"><tr><th colspan="2"><?php echo JText::_('SC_OPTIONS');?></th></tr>
		<tr>
		<td width="185"><?php echo stripslashes( JText::_('SC_OPTIONS_TITLE'));?></td>
		<td>
        <?php echo $og->title;?>
		</td>
		</tr>
        <tr>
        <td>&nbsp;</td>
        <td align="right"><input type="submit" name="submit" value="<?php echo JText::_('SC_SAVE');?>" /></td>
        </tr>
		<tr>
		<td><?php echo stripslashes( JText::_('SC_IND_OPTIONS'));?>
		</td>
		<td>
        
			<input type="button" name="addbtn" onclick="addRow()" value="<?php echo JText::_('Add Option');?>" />&nbsp;<input type="button" name="delbtn" onclick="deleteRow()" value="<?php echo JText::_('Remove option');?>" />
			<table id="mine" border="1" class="adminform">
			<tr><th width="20">#</th><th width="40"><?php echo JText::_('Description') ?></th><th width="40"><?php echo JText::_('Formula');?></th><th width="40"><?php echo JText::_('Caption');?></th><th width="20"><?php echo JText::_("Display order");?></th><th width="80"><?php echo JText::_('Default select');?></th><th>&nbsp;</th></tr>
			<?php
                //if (is_array($rows)) 
                {
				foreach ($rows as $key=>$line) {
                    
					echo "<tr>";
					echo "<td><input type='checkbox' name='tid$key' id='tid$key' value='$line->id'></td>";
					echo "<td><input type='hidden' name='optionid[]' value='$line->id'><input type='text' size='30' name='optionshorttext[]' value='$line->description' ></td>" ;
					echo "<td><input type='text' name='optionformula[]' value='$line->formula' ></td>" ;
					echo "<td><input type='text' name='optioncaption[]' value='$line->caption' ></td>" ;
					echo "<td><input type='text' name='optiondisporder[]' value='$line->disporder' size='1'></td>" ;
					echo "<td><input type='radio' name='optiondefselect' value='$key' ".($line->defselect=="1"?"checked":"")."></td>" ;
					echo "</tr>";
				}
                }
			?>

			</table>
			<input type="hidden" name="rows" value="" id="rows" />
			<input type="hidden" name="rows2" value="" id="rows2" />
		</td>
		</tr>
		</table>
		<input type="hidden" name="option" value="com_caddy" />
		<input type="hidden" name="optgrid" value="<?php echo $optgrid;?>" />
		<input type="hidden" name="productid" value="<?php echo $productid;?>" />
		<input type="hidden" name="action" value="options" />
		<input type="hidden" name="task" value="saveoptions" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
        <?php
    }

}