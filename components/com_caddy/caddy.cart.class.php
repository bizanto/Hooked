<?php
/**
* @package SimpleCaddy 1.75 for Joomla 1.5
* @copyright Copyright (C) 2006-2011 Henk von Pickartz. All rights reserved.
*/
// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

class CartProduct {
    var $prodcode;  
    var $prodname;
    var $option; 
    var $quantity=1;
    var $unitprice;
    var $formula="";
    var $id="";
    var $caption="";
    var $finalprice;
    
    function CartProduct ($aa=null) // sets the CartProduct class variables
    {
    	if ($aa!=null) { // could be null in case directly created
 	       foreach ($aa as $k=>$v) {
				$this->$k = $aa[$k];
			}
		}
    }
}

class cart2 {
	function destroyCart() {
		if (isset($_SESSION)) {
			@$_SESSION['SimpleCart']=""; // empty the cart
		}
	}
	
	function readCart() { // reads cart from session cookie
		if (!isset($_SESSION)) {
			session_start(); // cart needs a session cookie	
		}
		$data = @$_SESSION['SimpleCart']; // suppress any legitimate warnings, after all the cart might be empty
		if (!$data) return; // no data in the cart
//		$data=urldecode($data);
	    $parser = xml_parser_create();
	    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	    xml_parse_into_struct($parser, $data, $values, $tags);
	    xml_parser_free($parser);
		$tdb=array();
	    foreach ($tags as $key=>$val) {
	        if ($key == "product") {
	            $ranges = $val;
	            for ($i=0; $i < count($ranges); $i+=2) {
	                $offset = $ranges[$i] + 1;
	                $len = $ranges[$i + 1] - $offset;
	                $tdb[] = @$this->parseProduct(array_slice($values, $offset, $len));
	            }
	        } else {
	            continue;
	        }
	    }
	    return $tdb;
	}
	
	function parseProduct($mvalues) {
	    for ($i=0; $i < count($mvalues); $i++) {
	        $prod[$mvalues[$i]["tag"]] = urldecode($mvalues[$i]["value"]);
	    }
	    return new CartProduct($prod);
	}
	
	function dumpCartArray() {
		$dump=$this->readCart();
		echo "Array content of Cart<pre>\n";
		print_r($dump);
		echo "</pre>";
	}
	
	function dumpCartXML() {
		$cartArray=$this->readCart();
		$cartXML=$this->makeCartXML($cartArray);
		printf("<pre>%s</pre>", htmlspecialchars($cartXML));
	}
	
	function setCartProductQty($p) {
		$cartArray=$this->readCart();
		$todelete=false;
		if (count($cartArray)>0) {
			foreach ($cartArray as $key=>$prod) {
				if ($prod->id == $p->id) {
					$prod->quantity = $p->quantity;
					$cartArray[$key]=$prod; // put back into the array for php4 compatibility
					if ($p->quantity==0) { // nothing left
						$todelete=true;
						$which=$key;
						break; // we have found the product, no need to do the rest
					}
				}
			}
			if ($todelete) {
				array_splice($cartArray, $which, 1);
			}
		}
		$cartXML=$this->makeCartXML($cartArray);
		$this->writeCart($cartXML);
		return $cartXML;
	}
	
	function getCartTotal() {
		$total=0;
		$cartArray=$this->readCart();
		if (count($cartArray)==0) return $total;
		foreach ($cartArray as $key=>$prod) {
			$total=$total + ($prod->quantity * $prod->finalprice);
		}
		return $total;
	}

	function getCartNumbers() {
		$total=0;
		$cartArray=$this->readCart();
		return count($cartArray);
	}
    
    function getCartQuantities() {
        $total=0;
		$cartArray=$this->readCart();
        $number=count($cartArray);
		if ($number==0) {
    		return $total;  
		}
		foreach ($cartArray as $key=>$prod) {
			$total=$total + $prod->quantity;
		}
		return $total;
    }

	function addCartProduct($p) {
		$cartArray=$this->readCart();
		$toadd=true; // consider we dont have it yet
		if (count($cartArray)>0) {
		foreach ($cartArray as $key=>$prod) {
			if (($prod->id == $p->id)) {
				$prod->quantity = $prod->quantity+$p->quantity;
				$toadd=false;
				break; // we have found the product, no need to do the rest
				}
			}
		}
		if ($toadd) {
			$cartArray[]=$p; // add the product to the array
		}

		$cartXML=$this->makeCartXML($cartArray);
		$this->writeCart($cartXML);
		return $cartXML;
	}
	
	function getCartProduct($p) {
		$cartArray=$this->readCart();
		$toadd=true; // consider we dont have it yet
		if (count($cartArray)>0) {
		foreach ($cartArray as $key=>$prod) {
			if (($prod->id == $p->id)) {
				$toadd=false;
				break; // we have found the product, no need to do the rest
				}
			}
		}
		if ($toadd) {
			return $prod;
		}
		return false;
	}
	
	function removeCartProduct($p) {
		$cartArray=$this->readCart();
		$todelete=false;
		if (count($cartArray)>0) {
			foreach ($cartArray as $key=>$prod) {
				if (($prod->prodcode == $p->prodcode) and ($prod->option==$p->option)) {
					$prod->quantity = $prod->quantity-1;
					if ($prod->quantity==0) { // nothing left
						$todelete=true;
						$which=$key;
						break; // we have found the producty, no need to do the rest
					}
				}
			}
			if ($todelete) {
				array_splice($cartArray, $key, 1);
			}
		}
		$cartXML=$this->makeCartXML($cartArray);
		$this->writeCart($cartXML);
		return $cartXML;
	}
	
	function makeCartXML($cartArray){ // make xml string from the array with cart contents
		$cartxml="<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
		$cartxml.="\n<cart>";
		$tmp=new CartProduct();
		$pv=get_object_vars($tmp);
		if (count($cartArray)>0){ // make sure we have something to add
			foreach ($cartArray as $key=>$product) {
				$cartxml .= "\n\t<product>";
				foreach ($pv as $key=>$v) {
					$xmlkey=urlencode($key);
					$cartxml .= "\n\t\t<$key>".urlencode($product->$key)."</$key>";
				}
				$cartxml .= "\n\t</product>";
			}
		}
		$cartxml .= "\n</cart>";
		return $cartxml;
	}
	
	function isInCart($prodcode) {
		$res=false;
		$cartArray=$this->readCart();
		$todelete=false;
		if (count($cartArray)>0) {
			foreach ($cartArray as $key=>$prod) {
				if (($prod->prodcode == $prodcode) ) {
					$res=true;
					break; // we have found the producty, no need to do the rest
				}
			}
		}
		return $res;
	}
	
	function writeCart($cartXML) { // write cart back to session cookie
	if (!isset($_SESSION)) session_start();
		$_SESSION['SimpleCart']=$cartXML;
	}
}
?>