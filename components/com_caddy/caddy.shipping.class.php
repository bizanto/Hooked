<?php
/**
* @package SimpleCaddy 1.75 for Joomla 1.5
* @copyright Copyright (C) 2006-2011 Henk von Pickartz. All rights reserved.
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class shipping {
	function getConfig(){
		$cfg=new sc_configuration();
		$config['tsep']=$cfg->get('thousand_sep');
		$config['dsep']=$cfg->get('decimal_sep');
		$config['decs']=$cfg->get('decimals');
		$config['currency']=$cfg->get('currency');
		$config['showremove']=$cfg->get('remove_button');
		$config['show_emptycart']=$cfg->get('show_emptycart');
		$config['rightAlignCurency']=$cfg->get('curralign');
		$config['stdprodcode']=$cfg->get("cart_fee_product");
		return $config;
	}

	function doViewShippingZones($msg =null){
		$rows = shipZone::getAll();
		if(!$msg)
			$msg=JRequest::getVar('msg');
		
		if($msg){
			shipping_HTML::viewShippingZones($rows, $msg);
		}
        else
        {
			shipping_HTML::viewShippingZones($rows);
		}
	}

	function prepareEditShipping($cid){
		$ship = new shipZone(JFactory::getDBO());
		$cid=$cid[0];
		$ship->load($cid);
		return $ship;
	}
    
	function saveShipping(){
		$msg=null;
		$ship = new shipZone(JFactory::getDBO());
		$ship->bind($_REQUEST);
		
		//Checking if float was passed
		if(!is_numeric($upper= JRequest::getVar('points_upper'))||!is_numeric($lower= JRequest::getVar('points_lower')))
			$msg =  JText::_( 'SC_SHIPPING_NOTFLOAT');
			
		if(!$msg){
			$ship->points_upper = $upper;
			$ship->points_lower = $lower;
			$ship->store();
		}
		return $msg;
	}
    
	function deleteZone($cid){
		if($cid[0]){
			foreach($cid as $id){
				$ship = new shipZone(JFactory::getDBO());
				$ship->delete($id);
			}
			return JText::_('SC_ITEMS_DELETED');
		}else{
			return JText::_('SC_NOITEM_SELECTED');
		}
	}
 
}

class shipZone extends JTable {
	var $id=null;
	var $name=null;
	var $points_lower =null;
	var $points_upper = null;
	var $price = null;

	function __construct(&$db){
		parent::__construct( '#__sc_ship_zones','id',$db);
	}
	
	function getAll() {
		$query = "SELECT * 
				FROM  `#__sc_ship_zones` 
				ORDER BY  `name` ,  `points_lower` ";
				
		$db = &JFactory::getDBO();
		$db->setQuery($query);
		
		$rows= $db->loadObjectList();
		
		if ($db->getErrorNum()) { 
			echo $db->stderr(); 
			return false; 
		}  else {
			return $rows;
		}
	}
}
	
	/************************************************************
	**  Front end functions
	/************************************************************/
	
class fshipping {
	function calculateShipCost($shipPoints, $shipRegion){
		$db=JFactory::getDBO();
		$query2 = "	SELECT *
						FROM #__sc_ship_zones
						WHERE name = \"$shipRegion\"
						AND points_upper >=$shipPoints
						AND points_lower <=$shipPoints";
		$db->setQuery($query2);
		$relativeShipZone = $db->loadObject();
		@$zonePrice = (float)$relativeShipZone->price;
		return $zonePrice;
		//die("zp= ".$zonePrice."region $shipRegion.....$query2");
	}
	
	function shipregion(){
		return JRequest::getVar('shipRegion',null);
	}
	
	function getTotalPointsFromCart($cart){
	$db= JFactory::getDBO();
		$totalPoints =0;
		foreach($cart as $cartproduct){
			$query = "SELECT * FROM #__sc_products WHERE prodcode LIKE '$cartproduct->prodcode'";
			$db->setQuery($query);
			$prodRow = $db->loadObject();
			if (@$prodRow->id) { // check IF we have a genuine product and not a voucher
				// anyway we don't ship vouchers!
				$points = $prodRow->shippoints;
				$totalPoints += $prodRow->shippoints * $cartproduct->quantity;
			}
		}
		return $totalPoints;
	}
	
	function buildRegionList(){
		$db=JFactory::getDBO();
		$query = "SELECT DISTINCT `name` FROM #__sc_ship_zones";
		$db->setQuery($query);
		$shippingZones = $db->loadObjectList();
		$changeRegionJS = "Javascript:getShipCost(this.value)";
		$shipRegionSelect = "<select name='shipRegion' onchange='$changeRegionJS'><option class='selectItalic' value=''>".JText::_('SC_CHOOSE_SHIPPING_AREA')."</option>";

		foreach($shippingZones as $zone){
			if($zone->name == fshipping::shipregion()){
				$shipRegionSelect.="<option selected='$zone->name' value='$zone->name'>$zone->name</option>";
			}else{
				$shipRegionSelect.="<option value='$zone->name'>$zone->name</option>";
			}
		}
		$shipRegionSelect.="</select>";
		return $shipRegionSelect;
	}

}

class shippingRequest{

	function shippingRequest(){
		$task = JRequest::getVar('task');
		switch($task){
			case "loadshipcost":
				//doing Ship calculation
                $cfg=new sc_configuration();
                $taxonshipping=$cfg->get("taxonshipping");
                
				$shipPoints=shippingRequest::countPoints();
				$shipRegion = JRequest::getVar('shipRegion');
				$shipCost=fshipping::calculateShipCost($shipPoints,$shipRegion);
				$readyShipCost=shippingRequest::displayValue($shipCost);
				
				//update sub total
				$subTotal=shippingRequest::getSubTotalFromCartContents();
				$readySub = shippingRequest::displayValue($subTotal);

				if ($taxonshipping==1) {
                    $subTotal = $subTotal + $shipCost;
				}
				
				//update tax if applicable
                $ctax=new taxes();
                $taxrate=$ctax->getTax($shipRegion);
				$tax = $subTotal*$taxrate;
				$readyTax = shippingRequest::displayValue($tax);
                
                if ($taxonshipping==0) {
                    $subTotal = $subTotal + $shipCost;
                }
				
				//update Gtotal
				$gTotal=$tax+$subTotal;
				$readygTotal = shippingRequest::displayValue($gTotal);
				
				echo "$readyShipCost|$readySub|$readyTax|$readygTotal";
			break;
		}
	}
    
	function countPoints(){
		$cart2=new cart2();
		$cart=$cart2->readCart();
		return fshipping::getTotalPointsFromCart($cart);
	}
    
	function displayValue($value){
		$cfg=new sc_configuration();
		$tsep=$cfg->get('thousand_sep');
		$dsep=$cfg->get('decimal_sep');
		$decs=$cfg->get('decimals');
		$currency=$cfg->get('currency');
		$currleftalign=$cfg->get('curralign');
		$html='';
		if ($currleftalign==1) {
			$html .= "$currency&nbsp;".number_format($value, $decs, $dsep, $tsep);
		}
		else
		{
			$html .= number_format($value, $decs, $dsep, $tsep)."&nbsp;$currency";
		}
		return $html;
	}

	function getSubTotalFromCartContents(){
		$cart2=new cart2();
		$cart=$cart2->readCart();
		$sub=0;
		foreach($cart as $cartproduct){
			$sub+=$cartproduct->quantity*$cartproduct->finalprice;
		}
		return $sub;
	}
    
}


?>
