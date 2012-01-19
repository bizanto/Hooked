<?php
/**
* @package SimpleCaddy 1.75 for Joomla 1.5
* @copyright Copyright (C) 2006-2011 Henk von Pickartz. All rights reserved.
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class display {

	function showCart($cart, $romode=null) { // $romode = readonly mode when checking out
	global $mainframe;

	$debug=0;
	$mainframe->addCustomHeadTag('<link rel="stylesheet" href="components/com_caddy/css/simplecaddy.css" type="text/css" />');
	$showhidden="hidden";
	if ($debug==1) $showhidden="text";
	$line=__LINE__;
		$cfg=new sc_configuration();
		$tsep=$cfg->get('thousand_sep');
		$dsep=$cfg->get('decimal_sep');
		$decs=$cfg->get('decimals');
		$currency=$cfg->get('currency');
		$curralign=$cfg->get('curralign');
		$showremove=$cfg->get('remove_button');
		$show_emptycart=$cfg->get('show_emptycart');
		$currency=$cfg->get('currency');
		$currleftalign=$cfg->get('curralign');
		$stdprodcode=$cfg->get("cart_fee_product");
		$shipping=$cfg->get("shippingenabled");
		$cfp=$cfg->get ("taxrate");
		$cfp=str_replace("%", "", $cfp);
		if ($cfp>1) {
			$cfp=$cfp/100;
		}
		$taxrate=$cfp;

		$gtotal=0;
		$html ="";
// some templates have problems with div tags only as content
// enable this line and the corresponding line at the end of this function to 
// accommodate these templates. It will then become invalid xhtml, though
//		$html  .= "<table><tr><td>";
		$html .= "\n<div class='sc_cart'>";
		$html .= "\n<div class='cartheading'>\n<div class='code_col'>".JText::_('SC_CODE')."</div>\n<div class='desc_col'>".JText::_('SC_DESCRIPTION')."</div>\n<div class='price_col'>".JText::_('SC_PRICE_PER_UNIT')."</div>\n<div class='qty_col'>".JText::_('SC_QUANTITY')."</div>\n<div class='total_col'>".JText::_('SC_TOTAL')."</div>\n<div class='actions_col'>&nbsp;</div></div>";
		$emptycart=true;
		if (!is_array($cart)) $cart=array();
		foreach ($cart as $key=>$cartproduct) {
			$formname=uniqid("Z");

			$html2 = "<form name='$formname' method='post'>";
			$html2 .= "\n<div class='code_col'>$cartproduct->prodcode</div>";
			$html2 .= "\n<div class='desc_col'>".urldecode($cartproduct->prodname)." - ".urldecode($cartproduct->option)."</div>";

			$pu = number_format($cartproduct->finalprice, $decs, $dsep, $tsep);
			if ($currleftalign==1) {
				$html2 .= "\n<div class='price_col'>$currency&nbsp;".$pu."</div>";
			}
			else
			{
				$html2 .= "\n<div class='price_col'>$pu&nbsp;$currency</div>";
			}
			$html2 .= "\n<div class='qty_col'>";
			if ((!$romode) and ($cartproduct->prodcode!=$stdprodcode) and ($cartproduct->prodcode!="voucher")) {
				$html2 .=  "\n<input type='text' name='edtqty' value='".$cartproduct->quantity."' class='sc_edtqty'>";
			}
			else
			{
				$html2 .=  $cartproduct->quantity;
			}
			$html2 .= "</div>";
			$html2 .= "\n<div class='total_col'>";

			$total=$cartproduct->quantity*$cartproduct->finalprice;
			$nombre_format = number_format($total, $decs, $dsep, $tsep);
			$gtotal= $gtotal + $total;
			if ($currleftalign==1) {
				$html2 .= "$currency&nbsp;".$nombre_format;
			}
			else
			{
				$html2 .= $nombre_format."&nbsp;$currency";
			}
			$html2 .="\n<input type='$showhidden' name='id' value='$cartproduct->id'>";
			$html2 .= "</div>\n<div class='actions_col'>";
			if ($cartproduct->prodcode!=$stdprodcode and ($cartproduct->prodcode!="voucher")) {
				$html2 .= "\n<input type='button' name='btnsubmit' value='".JText::_('SC_CHANGE')."' class='btnchange' onclick='document.$formname.submit()' />";
			}
			else
			{
				$html2 .= "&nbsp;";
			}
			if ($showremove==1 and ($cartproduct->prodcode!=$stdprodcode) and ($cartproduct->prodcode!="voucher")) {
				$html2 .= "\n<input type='button' name='btnremove' value='".JText::_('SC_REMOVE')."' class='btnremove' onclick='javascript:document.$formname.edtqty.value=0;javascript:document.$formname.submit()' />";
			}
			$html2 .= "\n<input type='hidden' name='option' value='com_caddy' />";
			$html2 .= "\n<input type='hidden' name='action' value='changeqty' />";
			$html2 .= "\n<input type='hidden' name='edtprodcode' value='".$cartproduct->prodcode."' />";
			$html2 .= "\n<input type='hidden' name='edtunitprice' value='".$cartproduct->unitprice."' />";
			$html2 .= "\n<input type='hidden' name='edtshorttext' value='".$cartproduct->prodname."' />";
			$html2 .= "\n<input type='hidden' name='edtoption' value='".$cartproduct->option."' />";
			$html2 .= "</form>";
			$html2 .= "</div>";
			if ($cartproduct->quantity) {
				$html .= $html2; // only add to display when qty != zero !
				$emptycart=false;
			}
		}
		if ($taxrate>0) {
			$html .= "\n<div class='fill_col'>"; 
			$html .= "<div class='text_left'>".JText::_('SC_SUBTOTAL')."</div>";
		}

		if ($taxrate>0) {
				if ($currleftalign==1) {
					$html .= "\n<div class='text_right'>$currency&nbsp;".number_format($gtotal, $decs, $dsep, $tsep)."</div>";
				}
				else
				{
					$html .= "\n<div class='text_right'>".number_format($gtotal, $decs, $dsep, $tsep)."&nbsp;$currency</div>";
				}
		$html .= "</div>";

				$html .= "\n<div class='fill_col'>";
				$html .= "<div class='text_left'>".JText::_('SC_TAX')."</div>";
				if ($currleftalign==1) {
					$html .= "\n<div class='text_right'>$currency&nbsp;".number_format($gtotal*$taxrate, $decs, $dsep, $tsep)."</div>";
				}
				else
				{
					$html .= "\n<div class='text_right'>".number_format($gtotal*$taxrate, $decs, $dsep, $tsep)."&nbsp;$currency</div>";
				}
		$html .= "</div>";
		}	

		$html .= "<div class='fill_col'>";
		$html .= "<div class='text_left'>".JText::_('SC_TOTAL')."</div>";
		if ($currleftalign==1) {
			$html .= "\n<div class='text_right'>$currency&nbsp;".number_format($gtotal+$gtotal*$taxrate, $decs, $dsep, $tsep)."</div>";
		}
		else
		{
			$html .= "\n<div class='text_right'>".number_format($gtotal+$gtotal*$taxrate, $decs, $dsep, $tsep)."&nbsp;$currency</div>";
		}
		$html .= "</div>";
		
		$html .= "<form name='checkout' method='post'>";
		$html .= "\n<div class='cartactions'>";
		$html .= "\n<input class='btncshopping' type='button' value='".JText::_('SC_CONTINUE_SHOPPING')."' onclick='window.history.go(-1)'>";

		$html .= "\n<input type='hidden' name='option' value='com_caddy'>";
		if ($show_emptycart==1) {
			$html .= "\n<input type='button' name='btnemptycart' value='".JText::_('SC_EMPTY_CART')."' class='btnemptycart' onclick='javascript:document.checkout.action.value=\"emptycart\";javascript:document.checkout.submit()'>";
		}

		$html .= "\n<input type='hidden' name='action' value='checkout'>";
		$html .= "\n<input class='btnorder' type='button' value='".JText::_('SC_ORDER')."' onclick='javascript:document.checkout.submit()'>";

		$html .="</div>";
		$html .= "</form>";
		if ($emptycart) {
			$html ="<div>";
			$html .=JText::_('SC_CART_EMPTY');
		}
		$html .= "</div><div id='debug'></div>";
//		$html .= "</td></tr></table>";
		echo $html;
		if ($debug==1) echo $line;
	}

	function ShowConfirmCart($cart, $pretext=null, $posttext=null, $ship=null) { 
		global $mainframe;
		$debug=0;
		//todo: add the full server path here instead of the relative local one
		$mainframe->addCustomHeadTag('<link rel="stylesheet" href="components/com_caddy/css/simplecaddy.css" type="text/css" />');
		$mainframe->addCustomHeadTag("<script type='text/javascript' src='components/com_caddy/js/ajax.js'></script>");
		$showhidden="hidden";
		if ($debug==1) $showhidden="text";
		$line=__LINE__;
		$cfg=new sc_configuration();
		$tsep=$cfg->get('thousand_sep');
		$dsep=$cfg->get('decimal_sep');
		$decs=$cfg->get('decimals');
		$currency=$cfg->get('currency');
		$curralign=$cfg->get('curralign');
		$showremove=$cfg->get('remove_button');
		$show_emptycart=$cfg->get('show_emptycart');
		$currency=$cfg->get('currency');
		$currleftalign=$cfg->get('curralign');
		$stdprodcode=$cfg->get("cart_fee_product");
		$shipping=$cfg->get ("shippingenabled");
		$cfp=$cfg->get ("taxrate");
		$cfp=str_replace("%", "", $cfp);
		if ($cfp>1) {
			$cfp=$cfp/100;
		}
		$taxrate=$cfp;
		$gtotal=0;
		$html = @$pretext->introtext; // the text preceding the confirm page
//		$html .= "<table><tr><td>";
		$html .= "\n<div class='sc_cart'>";
        //error message regarding no shipping selected
        $error = JRequest::getVar("error",null) ;
        if($error != null){
			$html.= "<div class='errormsg'>$error</div>";
		
        }
		$html .= "\n<div class='cartheading'>\n<div class='code_col'>".JText::_('SC_CODE')."</div>\n<div class='desc_col'>".JText::_('SC_DESCRIPTION')."</div>\n<div class='price_col'>".JText::_('SC_PRICE_PER_UNIT')."</div>\n<div class='qty_col'>".JText::_('SC_QUANTITY')."</div>\n<div class='total_col'>".JText::_('SC_TOTAL')."</div>\n<div class='actions_col'>&nbsp;</div></div>";
		$emptycart=true;
		if (!is_array($cart)) $cart=array();

		foreach ($cart as $key=>$cartproduct) {
			$formname=uniqid("Z");

			$html2 = "<form name='$formname' method='post'>";
			$html2 .= "\n<div class='code_col'>$cartproduct->prodcode</div>";
			$html2 .= "\n<div class='desc_col'>".urldecode($cartproduct->prodname)." - ".urldecode($cartproduct->option)."</div>";

			$pu = number_format($cartproduct->finalprice, $decs, $dsep, $tsep);
			if ($currleftalign==1) {
				$html2 .= "\n<div class='price_col'>$currency&nbsp;".$pu."</div>";
			}
			else
			{
				$html2 .= "\n<div class='price_col'>$pu&nbsp;$currency</div>";
			}
			$html2 .= "\n<div class='qty_col'>";

			$html2 .=  $cartproduct->quantity;

			$html2 .= "</div>";
			$html2 .= "\n<div class='total_col'>";

			$total=$cartproduct->quantity*$cartproduct->finalprice;
			$nombre_format = number_format($total, $decs, $dsep, $tsep);
			$gtotal= $gtotal + $total;
			if ($currleftalign==1) {
				$html2 .= "$currency&nbsp;".$nombre_format;
			}
			else
			{
				$html2 .= $nombre_format."&nbsp;$currency";
			}
			$html2 .="\n<input type='$showhidden' name='id' value='$cartproduct->id'>";
			$html2 .= "</div>\n<div class='actions_col'>";

			$html2 .= "\n<input type='hidden' name='option' value='com_caddy'>";
			$html2 .= "\n<input type='hidden' name='action' value='changeqty'>";
			$html2 .= "\n<input type='hidden' name='edtprodcode' value='".$cartproduct->prodcode."'>";
			$html2 .= "\n<input type='hidden' name='edtunitprice' value='".$cartproduct->unitprice."'>";
			$html2 .= "\n<input type='hidden' name='edtshorttext' value='".$cartproduct->prodname."'>";
			$html2 .= "\n<input type='hidden' name='edtoption' value='".$cartproduct->option."'>";
			$html2 .= "</form>";
			$html2 .= "</div>";
			if ($cartproduct->quantity) {
				$html .= $html2; // only add to display when qty != zero !
				$emptycart=false;
			}
		}
		//start checkout form
		$html .= "<form name='checkout' method='post'>";
		
		//shipping things go the enabler (Switch)
		//as shipping is purely for display purposes at the moment it interfers with nothing except increasing the totals
		
		if($ship['enabled']){
			//start shipping region select row
			$html .= "\n<div class='fill_col'>"; 
			$html .= "<div class='text_left'>".JText::_('SC_SHIPPING_SELECT')."</div>";
			$html .= "\n<div class='text_right'>".$ship['list']."</div>";
			$html .= "</div>";
			
			//start shipping cost row
			$html .= "\n<div class='fill_col'>"; 
			$html .= "<div class='text_left'>".JText::_('SC_SHIPPING_COST')."</div>";
			$html .= "\n<div class='text_right'>";
			$html .="<div id='shipCost'></div>";
			$html .="<div id='ajaxloader' style='display:none'><img src='components/com_caddy/images/loader.gif' /></div>";
			$html .= "</div>";
			$html .= "</div>";			
		
		} //end shipping additions
		$ccart=new cart2();
		if ($cfg->get("usevouchers")==1) {
			if (!$ccart->isInCart("voucher")) { // only show if no voucher in cart
				//start voucher row
				$html .= "\n<div class='fill_col'>"; 
				$html .= "<div class='text_left'>".JText::_('SC_VOUCHER')."</div>";
				$html .= "\n<div class='text_right'>";
				$html .= "<div id='voucherdiv'><input type='text' size='5' name='voucher' id='voucher' value=''>"; //  onblur='javascript:getVoucherInfo(this.value);'
				$html .= "<input type='button' name='btnrefresh' value='".JText::_('SC_CHECK_VOUCHER')."' class='btnemptycart' onclick='javascript:document.checkout.action.value=\"refresh\";document.checkout.submit(); '>"; //javascript:getVoucherInfo()
				$html .= "<div id='voucherinfo' class='voucherinfo'>&nbsp;</div></div>";
				$html .= "</div>";
				$html .= "</div>";	
			}		
		} 

		//start tax row
		if ($taxrate>0) {
			$html .= "\n<div class='fill_col'>"; 
			$html .= "<div class='text_left'>".JText::_('SC_SUBTOTAL')."</div>";
		}else
		{ //if no tax is being used, still need somewhere to dump the values that the shipping ajax call drags u, or else the ajax call fails half way through with unexpected resulats usually causing javascript to be disabled on the page.
			$html .="
			<div style='display:none'>
			<div id='scTax'></div><div id='scSub'></div>
			</div>";
		}

		if ($taxrate>0) {
			if ($currleftalign==1) {
				$html .= "\n<div class='text_right'><div id='scSub'>$currency&nbsp;".number_format($gtotal, $decs, $dsep, $tsep)."</div></div>";
			}
			else
			{
				$html .= "\n<div class='text_right'><div id='scSub'>".number_format($gtotal, $decs, $dsep, $tsep)."&nbsp;$currency</div></div>";
			}
			$html .= "</div>";
			$html .= "\n<div class='fill_col'>";
			$html .= "<div class='text_left'>".JText::_('SC_TAX')."</div>";
			if ($currleftalign==1) {
				$html .= "\n<div class='text_right'><div id='scTax'>$currency&nbsp;".number_format($gtotal*$taxrate, $decs, $dsep, $tsep)."</div></div>";
			}
			else
			{
				$html .= "\n<div class='text_right'><div id='scTax'>".number_format($gtotal*$taxrate, $decs, $dsep, $tsep)."&nbsp;$currency</div></div>";
			}
			$html .= "</div>";
		}	

		$html .= "<div class='fill_col_total'>";
		$html .= "<div class='text_left'>".JText::_('SC_TOTAL')."</div>";
		if ($currleftalign==1) {
			$html .= "\n<div class='text_right'><div id='scgTotal'>$currency&nbsp;".number_format($gtotal+$gtotal*$taxrate, $decs, $dsep, $tsep)."</div></div>";
		}
		else
		{
			$html .= "\n<div class='text_right'><div id='scgTotal'>".number_format($gtotal+$gtotal*$taxrate, $decs, $dsep, $tsep)."&nbsp;$currency</div></div>";
		}
		$html .= "</div>";

		$html .= "\n<div class='cartactions'>";

		if ($show_emptycart==1) {
			$html .= "\n<input type='button' name='btnemptycart' value='".JText::_('SC_EMPTY_CART')."' class='btnemptycart' onclick='javascript:document.checkout.action.value=\"emptycart\";javascript:document.checkout.submit()'>";
		}
		
		$html .= "\n<input type='hidden' name='option' value='com_caddy'>";
		$html .= "\n<input type='hidden' name='action' value='confirm'>";
		$html .= "\n<input name='scodrbtn' class='btnconfirm' type='submit' value='".JText::_('SC_CONFIRM')."' onclick='javascript:document.checkout.submit()'>";
		$html .="</div>";
		$html .= "</form>";
		if ($emptycart) {
			$html ="<div>";
			$html .=JText::_('SC_CART_EMPTY');
		}
		$html .= "</div><div id='debug'></div>";
//		$html .= "</td></tr></table>";
		$html .= @$posttext->introtext; // the text below the checkout page
		echo $html;
		if ($debug==1) echo $line;
	}

	// this is the custom fields form
	function showMyDetails($formfields, $errormessage=null, $fielddata=array()) {
	global $mainframe;
		$line=__LINE__;
		$script='<script language="javascript" type="text/javascript" src="components/com_caddy/js/datetimepicker.js"></script>';
		$mainframe->addCustomHeadTag($script);
		$mainframe->addCustomHeadTag("<script type='text/javascript' src='components/com_caddy/js/ajax.js'></script>");
		$document	=& JFactory::getDocument();
		$document->addStyleSheet( JURI::root(true).'/components/com_caddy/css/simplecaddy.css');
		?>
		
		<form name="frmdetails" method="post">
		<table width="100%" border="0">
		<?php
		if ($errormessage) {
			echo "<tr><td colspan='2'><div class='errormsg'>$errormessage</div></td></tr>";
		}
        $cfg = new sc_configuration();
		if($cfg->get("shippingenabled")){
            //shipping check for zone selected
            $shipregion = fshipping::shipregion();
        
            if($shipregion==null){
                $mainframe->redirect('index.php?option=com_caddy&action=checkout&error=Select%20A%20Ship%20Region');
           }
        }
    
  		$n=count($formfields);
		$first=true;
		foreach ($formfields as $field) {
			switch($field->type) {
				case "divider": // simple line with text, no fields
					echo "<tr class='$field->classname'><td colspan='2'>$field->caption";
					break;
				case "text": // textbox field, single line
					echo "<tr><td>$field->caption</td><td>";
					echo "<input type='text' name='$field->name' size='$field->length' class='$field->classname' value='". @$fielddata["$field->name"]."'>";
					break;
				case "textarea": // multiline textbox/textarea, no wysiwyg editor
					echo "<tr><td>$field->caption</td><td>";
					@list($cols, $rows)=explode(",", $field->length);
					echo "<textarea name='$field->name' class='$field->classname' cols='$cols' rows='$rows'>". @$fielddata["$field->name"]."</textarea>";
					break;
				case "radio": // yes/no radio buttons
					echo "<tr><td>$field->caption</td><td>";
					echo "<input type='radio' name='$field->name' class='$field->classname' value='yes' ". (@$fielddata["$field->name"]=="yes"?"checked":"").">". JText::_('Yes');
					echo "<input type='radio' name='$field->name' class='$field->classname' value='no' ". (@$fielddata["$field->name"]=="no"?"checked":"").">". JText::_('No');
					break;
				case "checkbox": // single checkbox
					echo "<tr><td>$field->caption</td><td>";
					echo "<input type='checkbox' name='$field->name' class='$field->classname' value='yes' ". (@$fielddata["$field->name"]=="yes"?"checked":"").">". JText::_('Yes');
					break;
				case "date": // textfield with calendar javascript
					echo "<tr><td>$field->caption</td><td>";
					echo "<input type='text' name='$field->name' id='$field->name' size='$field->length' class='$field->classname' value='". @$fielddata["$field->name"]."'>";
					echo "&nbsp;<a href=\"javascript:NewCal('$field->name','ddMMyyyy',true ,24)\"><img src=\"components/com_caddy/images/cal.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"".JText::_("SC_PICK_DATE")."\"/></a>";
					break;
				case "dropdown": // dropdown list, single selection
					echo "<tr><td>$field->caption</td><td>";
					echo "<select name='$field->name' id='$field->name' class='$field->classname'>";
					$aoptions=explode(";", $field->fieldcontents);
					foreach ($aoptions as $key=>$value) {
						echo "<option value='$value'".(@$fielddata["$field->name"]=="$value"?" selected":"").">$value</option>";
					}
					echo "</select>";
					break;
			}
			echo $field->required ? "<span class='reqfield'>".JText::_('SC_REQUIRED')."</span>" : "";
			echo "";
			echo "</td>";
			if ($first) {
				echo "<td rowspan='$n'><div class='checkoutright'>&nbsp;</div></td>";
				$first=false;
			}
			echo "</tr>";
		}
		?>

		<tr>
		<td>&nbsp;
		</td>
		<td>
		<input class="button" type="submit" name="submit" value="<?php echo JText::_('SC_CONFIRM');?>" />
		</td>
		<td>&nbsp;
		</td>
		</tr>

		</table>
		<input type="hidden" name="ipaddress" value="<?php echo $_SERVER['REMOTE_ADDR'] ?>" />
		<input type="hidden" name="option" value="com_caddy" />
		<input type="hidden" name="action" value="allconfirm" />
		<input type="hidden" name="shipRegion" value="<?php echo JRequest::getVar('shipRegion')?>" />
		
		</form>
	<?php
	
	if (defined("debug")) echo $line;
	}

	function ThanksForOrder() {
		$line=__LINE__;
		echo  JText::_('SC_THANKYOU');
		if (defined("debug")) echo $line;
	} 

	function ThanksForOrderNoMail($result=null) {
		$line=__LINE__;
		echo  JText::_('SC_THANKYOU_NO_MAIL');
		echo "'$result'";
		if (defined("debug")) echo $line;
		
	}
		
	function view_prod($alist) {
	global $mainframe;
	$stylesheet= JPATH_COMPONENT.DS.'css'.DS.'simplecaddy.css';
		?>
		<link rel="stylesheet" href="<?php echo $stylesheet ?>" type="text/css" />
		<script type="text/javascript" language="javascript">
			function insertCode(plugincode) {
				var sccode = '{simplecaddy code='+plugincode+'}';
				window.parent.jInsertEditorText(sccode, 'text');
				window.parent.document.getElementById('sbox-window').close();
			}
			function insertCat(plugincode) {
				var sccode = '{simplecaddy category='+plugincode+'}';
				window.parent.jInsertEditorText(sccode, 'text');
				window.parent.document.getElementById('sbox-window').close();
			}
		</script>
		<?php
		echo "<table class='codelist' width='100%'>";
		echo "<tr><th>".JText::_('SC_CLICK_CODE')."</th></tr>";
		$k=0;
		foreach ($alist as $product) {
			echo "<tr class='row$k'><td>$product->category&nbsp;<a class='codelist' href='#' onclick=\"insertCode('$product->prodcode');\">$product->shorttext (code: $product->prodcode)</a></td></tr>";
			$k=1-$k;
		}
		echo "</table>";
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
	
}
?>
