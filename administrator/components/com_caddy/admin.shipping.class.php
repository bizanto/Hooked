<?php
/**
* @package SimpleCaddy 1.75 for Joomla 1.5
* @copyright Copyright (C) 2006-2011 Henk von Pickartz. All rights reserved.
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class shipping {
 
	/* Because I dont want too remember  Strange Names*/
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
		}else{
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
			
		//re-saving now we are sure that these are flaots
		if(!$msg){
			$ship->points_upper = $upper;
			$ship->points_lower = $lower;
			$ship->store();
			$msg=JText::_("SC_SHIPPING_ZONE_SAVED");
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
			return JText::_('SC_NO_ITEM_SELECTED');
		}
	}
 
}

/* Extends JTable so that you have a model to build DB objects around */

class shipZone extends JTable {

	var $id=null;
	var $name=null;
	var $points_lower =null;
	var $points_upper = null;
	var $price = null;



	function __construct(&$db){
		parent::__construct( '#__sc_ship_zones','id',$db);
	}
	
	function getAll(){
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



?>