<?php
/**
* @package SimpleCaddy 1.75 for Joomla 1.5
* @copyright Copyright (C) 2006-2011 Henk von Pickartz. All rights reserved.
*/
// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

class voucher extends JTable {
	var $id=null;
	var $name;
	var $formula;
	var $avqty;
	var $qtylimited;
	var $validfrom;
	var $validto;
	var $datelimited;
	var $published;
	var $lot;

	function voucher() {
		$db	=& JFactory::getDBO();
	   	$this->__construct( '#__sc_vouchers', 'id', $db );
	}
	
	function datetimetoint($datetime) {
		list($date, $time)=explode(" ", $datetime);
		list($day, $month, $year)=explode("-", $date);
		list($hour, $minute, $second)=explode(":", $time);
		if ( ( (int)$day+(int)$month+(int)$year+(int)$hour+(int)$minute+(int)$second ) == 0 ) return "";
		return mktime((int)$hour, (int)$minute, (int)$second, (int)$month, (int)$day, (int)$year);
	}

	function savevoucher() {
		$this->bind($_REQUEST);
		$this->name=strtolower($this->name);
		$this->name=str_replace(" ","", $this->name);
		$this->validfrom=$this->datetimetoint(JRequest::getVar("validfrom"));
		$this->validto=$this->datetimetoint(JRequest::getVar("validto"));
		$this->store();
		return $this->id;
	}

	function publishvoucher( $cid=null, $publish=1) {
		$cids = implode( ',', $cid );
		$query = "UPDATE ".$this->_tbl
		. "\n SET published = " . intval( $publish )
		. "\n WHERE id IN ( $cids )"
		;
		$this->_db->setQuery( $query );
		$this->_db->query();
	}

	function getAllvouchers($field=null, $order=null) {
		global $mosConfig_list_limit, $mainframe;
		$query="select count(*) as total from ".$this->_tbl;
		$this->_db->setQuery($query);
		$total=$this->_db->loadResult();
		
		$limit = intval( $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit ) );
		$limitstart = intval( $mainframe->getUserStateFromRequest( "view{scp}limitstart", 'limitstart', 0 ) );
		jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit );
		$query="select * from ".$this->_tbl;
		if ($field) $query .= " order by `$field` $order ";
		$this->_db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
		$lst=$this->_db->loadObjectList();

		$res['lst']=$lst;
		$res['nav']=$pageNav;
		$res['qry']=$this->_db->getQuery();
		return $res;
	}

	function removevouchers($cid=null) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM ".$this->_tbl." WHERE id IN ( $cids )";
		$this->_db->setQuery( $query );
		$this->_db->query();
	}

	function getvoucherNames() { // internal use only
		$query="select `name` from ".$this->_tbl;
		$this->_db->setQuery($query);
		return $this->_db->loadResultArray();
	}

	function getPublishedvouchers() {
		$query="select * from ".$this->_tbl." where published=1 order by `id` asc ";
		$this->_db->setQuery($query);
		$lst=$this->_db->loadObjectList();
		return $lst;
	}

	function getPublishedvouchersArray() {
		$query="select name from ".$this->_tbl." where published=1 order by `id` asc ";
		$this->_db->setQuery($query);
		$lst=$this->_db->loadResultArray();
		return $lst;
	}
	
	function findvoucher($vouchercode) {
		$query="select * from ".$this->_tbl." where `name`= '$vouchercode' ";
		$this->_db->setQuery($query);
		$v=$this->_db->loadObject();
		if (@$v->id) $this->load($v->id);
		echo $this->_db->getErrorMsg();
		
	}

	function getvoucher($id) {
		$this->load($id);
	}
}

class vouchers extends JTable {
	var $redirect=null;
	var $message=null;
	var $search=null;
	var $field=null;
	var $order=null;
	var $cid=null;
	var $vouchercode="nocode";

	function vouchers() {
		$this->search=JRequest::getVar("search");
		$this->field=JRequest::getVar("field");
		$this->order=JRequest::getVar("order");
		$this->vouchercode=JRequest::getVar("voucher");
		$cid=JRequest::getVar( 'cid', array(0), '', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		$this->cid=$cid;
	}

	function redirect() {
	global $mainframe;
		if (isset($this->redirect)) {
			$mainframe->redirect($this->redirect, $this->message);
		}
	}
	
	function getvoucherinfo() {
		if (!$this->vouchercode) return;
		$res=0;
		$v=new voucher();
		$v->findvoucher($this->vouchercode);
		if (!$v->id) {
			echo JText::sprintf("SC_VOUCHER_INVALID" , $v->name );
			printf("-<pre>%s</pre>-", print_r($v, 1));
			return;
		}
		if ($v->qtylimited and $v->avqty>0) $res++;
		if ($v->datelimited and ( (mktime()<=$v->validto) and (mktime()>$v->validfrom)) ) $res++;
		if (!$v->qtylimited and !$v->datelimited) $res++;
		if ($res>0) {
			$korting=$this->redeemvoucher($v);
			echo "OK|".$korting['subtotal']."|".$korting['tax']."|".$korting['carttotal'];
		}
		else
		{
			echo JText::sprintf("SC_VOUCHER_INVALID" , $v->name );
			printf("-<pre>%s %s</pre>-", print_r($v, 1), $res);
		}
//		$this->redirect="index.php?option=com_caddy&action=showcart&Itemid=$itemid";
	}

	function redeemvoucher($voucher) {
		global $mainframe;
		$cfg=new sc_configuration();
		$cfp=$cfg->get ("taxrate");
		$cfp=str_replace("%", "", $cfp);
		if ($cfp>1) {
			$cfp=$cfp/100;
		}
		$taxrate=$cfp;
		
		$cartProd=new CartProduct();
		$cartProd->prodcode="voucher";
		$cartProd->option="";
		$cartProd->prodname=$voucher->name;
		$cartProd->unitprice=0;
		$cartProd->quantity=1;
		$cartProd->finalprice=0;// matheval("$cartProd->unitprice $cartProd->formula");

		$cart=new cart2();
		$cart->removeCartProduct($cartProd); // remove any vouchers from the cart
/**
		$acart=$cart->readCart(); // returns array of cart products
		$gtotal=0;
		foreach ($acart as $key=>$cartproduct) {
			$total=$cartproduct->quantity*$cartproduct->finalprice;
			$gtotal= $gtotal + $total;
		}
*/		
		$gtotal=$cart->getCartTotal();
		$korting=$gtotal- matheval("$gtotal $voucher->formula");
		$cartProd->finalprice= $korting * -1 ; // just to get the amount back in the cart

		$cartProd->unitprice=$cartProd->finalprice;
		$cartProd->id="voucher";
		$cart->addCartProduct($cartProd);
		
		// now return the values for immediate display
		$gtotal = $korting ;
		$tax=$gtotal * $taxrate;
		$res['korting']=$korting;
		$res['subtotal']=$gtotal;
		$res['tax']=$tax;
		$res['carttotal']=$gtotal+$tax;
		return $res;
	}

	function add() {
		$this->edit();
	}
	
	function edit() {
		$voucher= new voucher();
		$voucher->getVoucher($this->cid[0]);
		display::editVoucher($voucher);
	}

	function apply() {
		$v=new voucher();
		$v->savevoucher();
		$this->cid[0]=$v->id;
		$this->edit();
	}

	function cancel() {
		$this->show();
	}
	
	function show() {
		JRequest::setvar('task', "vouchers");
		$voucher=new voucher();
		$alist=$voucher->getAllVouchers($this->field, $this->order);
		display::showVouchers($alist);
	}
	
	function save() {
		$v=new voucher();
		$v->savevoucher();
		$this->redirect="index.php?option=com_caddy&action=vouchers&task=show&field=$this->field&this->order=$order&search=$this->search";
		$this->message=JText::_('SC_VOUCHERSAVED');
	}

	function publish() {
		$v=new voucher();
		$v->publishvoucher($this->cid, true);
		$this->redirect="index.php?option=com_caddy&action=vouchers&task=show&field=$this->field&this->order=$order&search=$this->search";
		$this->message=JText::_('SC_VOUCHERS_PUBLISHED');
	}

	function unpublish() {
		$v=new voucher();
		$v->publishvoucher($this->cid, false);
		$this->redirect="index.php?option=com_caddy&action=vouchers&task=show&field=$this->field&this->order=$order&search=$this->search";
		$this->message=JText::_('SC_VOUCHERS_UNPUBLISHED');
	}

	function remove() {
		$v=new voucher();
		$v->removevouchers($this->cid);
		$this->redirect="index.php?option=com_caddy&action=vouchers&task=show&field=$this->field&this->order=$order&search=$this->search";
		$this->message=JText::_('SC_VOUCHERSDELETED');
	}
}
?>