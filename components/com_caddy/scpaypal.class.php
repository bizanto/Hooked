<?php
/**
* @package SimpleCaddy 1.75 for Joomla 1.5
* @copyright Copyright (C) 2006-2011 Henk von Pickartz. All rights reserved.
*/
// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

class scpaypal {
    // mandatory functions for a checkout class are
    // returnsuccess()
    // checkout($orderis, $ship=null)
    
	var $debugshow="hidden"; // set to text or hidden
	var $paymentsuccess;
	var $paymentfail;
	var $gatewayreturntext;

    function returnsuccess(){ // mandatory function
        $orderid=JRequest::getVar("custom"); // orderid coming back from PP
        $txn_id=JRequest::getVar("txn_id"); // pp transaction ID
        
		$cfg=new sc_configuration();
        $statuses=explode("\r\n", $cfg->get("ostatus"));
        $status=$statuses[count($statuses)-1]; // set the status to the last one in the list
        
        $scorder=new order();
        $scorder->load($orderid);
        $scorder->ordercode=$txn_id;
        $scorder->status=$status;
        $scorder->store();
        return;        
    }

	function checkout($orderid, $ship=null ) {
		// it uses the simple way to upload a cart with contents to paypal as explained here:
		// https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_cart_upload#id08A3F700OYK
		// data available December 28, 2009.
		
		//get PayPal configuration
		$cfg=new sc_configuration();
		$reselleremail=$cfg->get("reselleremail");
		$ppcurrency=$cfg->get("paypalcurrency");
        $environment=$cfg->get("ppenvironment"); // live or sandbox

        $ctax=new taxes();
        $taxrate=$ctax->getTax(@$ship['region']);

		if (!$reselleremail) {
			echo "Reseller email is not entered, please add this to the configuration first. PayPal cannot be used!";
			return;
		}

		// add the details to the order
		$gtotal=0; //define the grand total
		$pptax=0; // define the tax for paypal
        
        if ($environment==0) { // live
    		$html = "<form action='https://www.paypal.com/cgi-bin/webscr' method='post' name ='ppform' target='paypal'>";
        }
        else
        { // sandbox
            $html = "<form action='https://www.sandbox.paypal.com/cgi-bin/webscr' method='post' name ='ppform' target='paypal'>";
        }
   		$html .="<input type=\"$this->debugshow\" name=\"cmd\" value=\"_cart\">";
   		$html .="<input type=\"$this->debugshow\" name=\"upload\" value=\"1\">";
   		$html .="<input type=\"$this->debugshow\" name=\"business\" value=\"$reselleremail\">";
		$html .="<input type=\"$this->debugshow\" name=\"currency_code\" value=\"$ppcurrency\">";
		$html .="<input type=\"$this->debugshow\" name=\"rm\" value=\"2\">";
		$fieldnumber=0; // PayPal field numbering variable
        $odetails=new orderdetail();
        $lst=$odetails->getDetailsByOrderId($orderid);
		foreach ($lst as $product) {
            // create a post field and field value for PayPal
            if($product->total>0) { // price positive, so standard product to be paid for
				$fieldnumber = $fieldnumber +1 ; //increment the field number (could also be done with $fieldnumber++)
				$html .= "<input type='$this->debugshow' name='item_name_".$fieldnumber. "' value='".$product->shorttext." (".$product->prodcode.") ".$product->option. "'>";
				$html .= "<input type='$this->debugshow' name='amount_".$fieldnumber. "' value='".$product->unitprice. "'>";
				$html .= "<input type='$this->debugshow' name='quantity_".$fieldnumber. "' value='".$product->qty. "'>";
            }
            else // price <0 so transfer it as a discount amount instead of a product
            {
                $html .= "<input type='$this->debugshow' name='discount_amount_cart' value='".abs($product->total). "'>";
            }
            $gtotal += $product->total;
		}
        
		if ($taxrate>0) {// taxes to be applied!
        // check if taxes should be paid on shipping
            $taxonshipping=$cfg->get("taxonshipping");
            if ($taxonshipping==1) { // tax is calculated on shipping cost
                $pptax = ($gtotal * $taxrate)  + ($ship['cost'] * $taxrate) ;
            }
            else
            {
    			$pptax = $gtotal * $taxrate;
            }
		}
		if ($pptax>0) { //either one or both of the taxes have to be applied, so we add the tax field for PP
			$html .= "<input type=\"$this->debugshow\" name=\"tax_cart\" value=\"".number_format($pptax, 2,".", "").'">';//
		}

		if($ship['enabled']){
			$html .="<input type=\"$this->debugshow\" name=\"shipping_1\" value=\"".$ship['cost']."\">";
		}

		$html .="<input type=\"$this->debugshow\" name=\"custom\" value=\"$orderid\">";

        // these are the return urls to go to when coming back from paypal
        $successurl=JURI::base(false)."index.php?option=com_caddy&action=paysuccess";
        $failurl=JURI::base(false)."index.php?option=com_caddy&action=payfail";
        
		$html .="<input type=\"$this->debugshow\" name=\"cancel_return\" value=\"$failurl\">";
		$html .="<input type=\"$this->debugshow\" name=\"return\" value=\"$successurl\">";

		$html .= JText::_('SC_WE_USE_PAYPAL');
 		// PayPal requires you use their logo to check out. Check the PayPal site for other button types
 		// look here for more buttons from PayPal https://www.paypal.com/newlogobuttons
        // look here for the rules of usage of the paypal logos and pay buttons:
        //https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/pdf/merchant_graphic_guidelines.pdf

/** customizers, do your stuff here!
You may add all kinds of fields now to the paypal "cart" to customize your heading in PayPal and so on.
None of these novelties have been added here, but if you want to customize the appearance of your presence in Paypal,
Here is the place.

*/

 		$html .= '<p>
        <input type="image" name="submit" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" align="left" style="margin-right:7px;"> <span style="font-size:11px; font-family: Arial, Verdana;">The safer, easier way to pay.</span>
        <p>';

        // additional PayPal info
        // be careful to 'escape' any " with \ !
/**
        $html .="<p>
<!-- PayPal Logo --><table border=\"0\" cellpadding=\"10\" cellspacing=\"0\" align=\"center\"><tr><td align=\"center\"></td></tr>
<tr><td align=\"center\"><a href=\"#\" onclick=\"javascript:window.open('https://www.paypal.com/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside','olcwhatispaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=350');\">
<img  src=\"https://www.paypal.com/en_US/i/bnr/vertical_solution_PPeCheck.gif\" border=\"0\" alt=\"Solution Graphics\"></a></td></tr></table><!-- PayPal Logo -->        
        </p>";
*/
		// otherwise we could use a simple submit button:
		//$html .='<input type="submit" value="PayPal">  ';
		$html .= "</form>";
		// if you want to add more text here
		// add it here like this:
		// $html .= "Your text here";
		// *before* the line below
		echo $html; // display the html.
	}
}
?>