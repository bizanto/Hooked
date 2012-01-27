<?php
/**
* @package SimpleCaddy 1.75 for Joomla 1.5
* @copyright Copyright (C) 2006-2011 Henk von Pickartz. All rights reserved.
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
JPlugin::loadLanguage( 'com_simplecaddy' );
jimport('joomla.application.component.router');
//define("debug", 1);
$debug=0;
$mainframe=JFactory::getApplication();

require_once( $mainframe->getPath( 'front_html' ) );
require_once( $mainframe->getPath( 'class' ) );

$user = JFactory::getUser();

$Itemid = intval( JRequest::getVar( 'Itemid', 0) );
$action=JRequest::getCmd( 'action', '');

switch ($action) {
	case "payfail":
		$cfg=new sc_configuration();
		$ppreturncancelurl=$cfg->get("ppreturncancel");
        $mainframe->redirect($ppreturncancelurl);
        break;
	case "paysuccess":
		//for verification of the returned values
		// you can then use these values in subsequent pages
//        printf("<pre>%s</pre>", print_r($_REQUEST, 1)); 

		$cfg=new sc_configuration();
    	$gateway=$cfg->get("pgclassname");
    	if ($gateway!="") { // check the gateway filename
    		if (!class_exists($gateway)) {
    			$pgpath=dirname($mainframe->getPath( 'class' ));
    			$pgfile=$pgpath.DS.$gateway.".class.php"; // filename should look like "scpaypal.class.php"
    			if (!file_exists($pgfile)) {
    				echo "The classname you provided in the configuration does not correspond to a known file ($pgfile)";
    				break;
    			}
    			require_once($pgfile);
    		}
    		$gw=new $gateway;
    		$gw->returnsuccess();
    	}

    
		$ppreturnurl=$gw->paymentsuccess;
        

        $mainframe->redirect($ppreturnurl);
		break;
	case "addtocart":
		$cfg=new sc_configuration();
		$cartProd=new CartProduct();
        $cartProd->option="";
        $cartProd->formula="";
        $cartProd->caption="";
    
		$stayonpage=JRequest::getVar("stayonpage");
		$acpoption=JRequest::getVar( 'edtoption', "");
		$acpoption2=JRequest::getVar( 'edtoption2' );
		$picname=JRequest::getVar("picname");
		$minqty=JRequest::getVar("minqty");

        if (is_array($acpoption)) {
        $prodoptions=new productoption();
        foreach ($acpoption as $key=>$value) {
    		$cpoption=explode(":", $value);
            $prodoptions->load($cpoption[1]);
            
    		$cartProd->option .= $prodoptions->description . "-";
    		$cartProd->formula .= $prodoptions->formula;
    		$cartProd->caption .= $prodoptions->caption;
            $cartProd->id .= md5($prodoptions->description.$picname);
        }
        }
        else
        {
            $cartProd->id=$acpoption;            
        }
    		
        @$cartProd->option .= " - ". $picname;
		if ($acpoption2=="hidden") $cartProd->option=$acpoption;

		$cartProd->prodcode=JRequest::getCmd( 'edtprodcode', 'cp error');
		$cartProd->prodname=JRequest::getVar( 'edtshorttext', 'txt error');
		// added security => retrieve original product from DB and not from session vars
		$product=new products();
		$p=$product->getproductByProdCode($cartProd->prodcode);
		$cartProd->unitprice=$p->unitprice; // retrieved from DB, not from session
		$cartProd->quantity=abs(JRequest::getInt( 'edtqty' ) ); // restrict to positive integer values, fractions not allowed
        if ($cartProd->quantity < $minqty ) $cartProd->quantity = $minqty;
		$cartProd->finalprice= matheval("$cartProd->unitprice $cartProd->formula");

		// check for minimum order quantities
		if ($cfg->get("checkoos")==1) { // check if we need to check
			$product=new products();
			$p=$product->getproductByProdCode($cartProd->prodcode);
			$cart=new cart2();
			$cp=$cart->getCartProduct($p);
			if ( ($p->av_qty + $cp->quantity) < $cartProd->quantity) { // if less than minimum quantity ordered
				$lasturl="index.php?option=com_caddy&action=showcart&Itemid=$Itemid";
				$mainframe->redirect($lasturl, JText::_("SC_MINQTY_WARNING")); // stay on the same page and issue a warning message
			}
		}

		$cart2=new cart2();
		$cart2->addCartProduct($cartProd); // add the product to the cart

		$usestdprod=$cfg->get("usestdproduct");
		if ($usestdprod==1) {
			AddStandard();
		}

		$lasturl=JRequest::getVar( 'lasturl', "index.php?option=com_caddy&action=showcart&Itemid=$Itemid");
		if ($stayonpage==1) {
			$mainframe->redirect( urldecode($lasturl) );
		}
		else // show the cart
		{
			$mainframe->redirect("index.php?option=com_caddy&action=showcart&Itemid=$Itemid");
		}
		break;
	case "changeqty":
		$product=new products();
		$p=$product->getproductByProdCode($cartProd->prodcode);

		$cartProd=new CartProduct();
		$cartProd->id=JRequest::getVar( 'id');
		$cartProd->prodcode=JRequest::getVar( 'edtprodcode');
		$cartProd->quantity=abs(JRequest::getInt( 'edtqty' )); // restrict to positive integer values
		$cartProd->unitprice=$p->unitprice; // retrieve price from DB not from session vars
		$cartProd->finalprice= matheval("$cartProd->unitprice $cartProd->formula");

		$cfg=new sc_configuration();
		if ($cfg->get("checkoos")==1) {
			// check for available quantity before making the change in the cart
			$product=new products();
			$p=$product->getproductByProdCode($cartProd->prodcode);
			if ($p->av_qty < $cartProd->quantity) {
				$lasturl="index.php?option=com_caddy&action=showcart&Itemid=$Itemid";
				$mainframe->redirect($lasturl,JText::_("SC_MINQTY_WARNING"));
			}
		}

		$cart2=new cart2();
		$cart2->setCartProductQty($cartProd);

		$c=$cart2->getCartNumbers();
		if ($c==1 and $cart2->isInCart("voucher")) $mainframe->redirect("index.php?option=com_caddy&action=emptycart&Itemid=$Itemid");
		$stdprod=$cfg->get("cart_fee_product");
		if ($c==2 and ($cart2->isInCart("voucher") and ($cart2->isInCart("$stdprod"))) ) {
			// if only standard prod and voucher remain, empty the cart
			$mainframe->redirect("index.php?option=com_caddy&action=emptycart&Itemid=$Itemid");
		}

		$cfg = new sc_configuration();
		$usestdprod=$cfg->get("usestdproduct");
		if ($usestdprod==1) {
			AddStandard();
		}
		$mainframe->redirect("index.php?option=com_caddy&action=showcart&Itemid=$Itemid");
		break;
	case "emptycart":
		$cart2=new cart2();
		$cart2->destroyCart();
		$mainframe->redirect("index.php?option=com_caddy&action=showcart&Itemid=$Itemid");
		break;
	case "showcart":
		$cart2=new cart2();
		$cart=$cart2->readCart();
		display::showCart($cart);
		break;
	case "showcartxml":
		$cart2=new cart2();
		$cart=$cart2->dumpCartXML();
		break;
	case "view_prod":
		$a=new product();
		$alist=$a->getPublishedProducts();
		display::view_prod($alist);
		break;
	case "checkout":
		$cart=new cart2();
		$cfg=new sc_configuration();
		$mintocheckout=$cfg->get("mincheckout");
		// check for minimum amount before checkout, default = 0 => any amount is enough
		if (!$mintocheckout) $mintocheckout=0;
		$carttotal= $cart->getCartTotal();
		if ( $carttotal < $mintocheckout ) {
			$txt=JText::_('SC_LESS_THAN_MIN_AMOUNT', $mintocheckout );
			$mainframe->redirect("index.php?option=com_caddy&action=showcart&Itemid=$Itemid", $txt);
		}

		$cart2=new cart2();
		$cart=$cart2->readCart();
		$cfg=new sc_configuration();
		$pretextid=$cfg->get("pretextid");
		$posttextid=$cfg->get("posttextid");
		$db	=& JFactory::getDBO();
		$query="select * from #__content where id='$pretextid'";
		$db->setQuery($query);
		$pretext=$db->loadObject();
		$query="select * from #__content where id='$posttextid'";
		$db->setQuery($query);
		$posttext=$db->loadObject();
		//is shipping enabled
		$cfg = new sc_configuration();
		$ship['enabled']=$cfg->get("shippingenabled");

		//populate region list
		if($ship['enabled']){
			$ship['list']=fshipping::buildRegionList();
			display::showConfirmCart($cart, $pretext, $posttext, $ship);
		}else{
			display::showConfirmCart($cart, $pretext, $posttext);
		}
		break;
	case "confirm":
		$fields=new fields();
		$fieldlist=$fields->getPublishedFields();
		$user=JFactory::getUser(); // get the Joomla logged in user
		// fielddata is an array containing field names as key and values.
		// fieldnames can be custom field names
		// here is also the moment to get infor from Community Builder
		$fielddata=array();
		$fielddata['username']=$user->username;
		$fielddata['name']=$user->name;
		$fielddata['email']=$user->email;
		display::showMyDetails($fieldlist, null, $fielddata); // show the User Info (Checkout) page
		break;
	case "refresh":
		$v=new vouchers();
		$v->getvoucherinfo();
		$mainframe->redirect("index.php?option=com_caddy&action=checkout");
		break;
	case "allconfirm":
		$errors=checkerrors();
		$cfg=new sc_configuration();
		if ($errors==0) {
			$cart=new cart2();
			$mycart=$cart->readCart();
			$ship['enabled']=$cfg->get('shippingenabled');
			if($ship['enabled']){
				$ship['region']=JRequest::getVar('shipRegion');
				$ship['cost']=fshipping::calculateShipCost(fshipping::getTotalPointsFromCart($mycart),$ship['region']);
			}
			// store the order first
			$order=new orders();
			$orderid = $order->store_new_order($mycart, $ship);
			$usePayPal=$cfg->get("usepaypal");
			if ($usePayPal==1) {
				$gateway=$cfg->get("pgclassname");
				if ($gateway!="") { // check the gateway filename
					if (!class_exists($gateway)) {
						$pgpath=dirname($mainframe->getPath( 'class' ));
						$pgfile=$pgpath.DS.$gateway.".class.php"; // filename should look like "scpaypal.class.php"
						if (!file_exists($pgfile)) {
							echo "The classname you provided in the configuration does not correspond to a known file ($pgfile)";
							break;
						}
						require_once($pgfile);
					}
					$checkout=new $gateway;
					$checkout->checkout($orderid, $ship);
				}
			}
			$result=true;
			if ($cfg->get("email_customer")== 1 ) {
				$mail=new email();
				$result=$mail->mailorder($orderid); // should be 1 for successful email
			}

			if ($result==1) {
				display::ThanksForOrder();
			}
			else
			{
				display::ThanksForOrderNoMail($result);
			}
			$cart=new cart2();
			$cart->destroyCart(); // emoty all session vars of the cart, no visual return
		}
		else // some required info is missing or incorrect
		{
			$fields=new fields();
			$fieldlist=$fields->getPublishedFields();
			$fielddata=$_REQUEST; // get everything back
			display::showMyDetails($fieldlist, JText::_('SC_REQUIRED_MISSING'), $fielddata);
		}

		break;
	case "vouchers":
		$c=new $action(); // should be "vouchers" here
		$c->$task(); // execute the task (taskname should be a function in the vouchers class)
		$c->redirect(); // redirect if set in the class
		break;
	case "shipping":
		$sh=new shippingRequest();
		break;
	default:
		echo "<p>To display products, use the plugins instead in your content, and read <a href='http://demo15.atlanticintelligence.net'>this</a> too.</p>";
		echo "<p>Do not link a menu to SimpleCaddy.</p>";
}

function AddStandard () {
	$cfg=new sc_configuration();
	$stdprod=$cfg->get("cart_fee_product");
	if ($stdprod != "") {
		$tmp=new products();
		$sp=$tmp->getproductByProdCode($stdprod);
		$cartProd=new CartProduct();
		$cartProd->option="";
		$cartProd->prodcode=$stdprod;
		$cartProd->prodname=$sp->shorttext;
		$cartProd->unitprice=$sp->unitprice;
		$cartProd->quantity=1;
		$cartProd->finalprice=$sp->unitprice;
		$cartProd->id=uniqid("S");
		$cart2=new cart2();
		$cart2->removecartProduct($cartProd);
		$c=$cart2->readcart();
		if (count($c)>0) {
			$cart2->addCartProduct($cartProd);
		}
	}
}

function checkerrors () {
	$errors=0;
	// this is a very simple check, you can add any kind of checking method to refine and enhance your security
	// first start by getting the published fields
	$fields=new fields();
	$fieldlist=$fields->getPublishedFields();
	// now check if they are required, and if so, check if they are filled
	// default function is "checkfilled" see below!
	foreach ($fieldlist as $field) {
		if ($field->required == 1 ){ // required field
			// now get the required function, this is set in the DB for each field
			if (function_exists($field->checkfunction)) { //check if you defined this function
				$errors = $errors + call_user_func($field->checkfunction, $field);
			}
		}
	}
	return $errors;
}

function matheval($equation){
    $equation = preg_replace("/[^0-9+\-.*\/()%]/","",$equation); 
    // fix percentage calcul when percentage value < 10 
    $equation = preg_replace("/([+-])([0-9]{1})(%)/","*(1\$1.0\$2)",$equation); 
    // calc percentage 
    $equation = preg_replace("/([+-])([0-9]+)(%)/","*(1\$1.\$2)",$equation); 
    // you could use str_replace on this next line 
    // if you really, really want to fine-tune this equation 
    $equation = preg_replace("/([0-9]+)(%)/",".\$1",$equation); 
    if ( $equation == "" ) 
    { 
      $return = 0; 
    } 
    else 
    { 
      eval("\$return=" . $equation . ";" ); 
    } 
    return $return; 
}


// the basic function for checking if a field has been filled
function checkfilled($field) {
	if (trim(JRequest::getVar($field->name))=="") { // trim the field and compare
		echo "<div class='errormsg'>".JText::_('SC_REQUIRED_FIELD')." <b>$field->caption</b> ".JText::_('SC_IS_EMPTY')."</div>";
		return 1; // add one to the errors total
	}
	else
	{
		return 0; // adds nothing to the errors total
	}
}

?>