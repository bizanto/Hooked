<?php
/**
* @package SimpleCaddy 1.75 for Joomla 1.5
* @copyright Copyright (C) 2006-2011 Henk von Pickartz. All rights reserved.
*/
// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

require_once("caddy.cart.class.php"); // just to make sure we have the cart functiona available
require_once("caddy.vouchers.class.php"); // just to make sure we have the cart functiona available
require_once("caddy.shipping.class.php"); // just to make sure we have the shipping functiona available
 
class email {
	function mailorder($orderid=null) {
        if (!$orderid) return;
		global $mainframe;
		$cfg=new sc_configuration();
		$tsep=$cfg->get('thousand_sep');
		$dsep=$cfg->get('decimal_sep');
		$decs=$cfg->get('decimals');
		$currency=$cfg->get('currency');
		$curralign=$cfg->get('curralign');
		$dateformat=$cfg->get('dateformat');
		$timeformat=$cfg->get('timeformat');
		$mode=$cfg->get("emailhtml");
		$usecontentasemail=$cfg->get("usecidasemail");
        
// create html orderheader
		$db	=& JFactory::getDBO();
		$query="select #__sc_orders.*, #__sc_orders.total as gtotal from #__sc_orders where #__sc_orders.id='$orderid' ";
		$db->setQuery($query);
		$header=$db->loadObject();
		echo $db->getErrorMsg();
		$hhtml = ""; // header html
		$hhtml .= "\n<br />".JText::_('SC_ORDER');
		$hhtml .= "\n<br />".date("$dateformat $timeformat", $header->orderdt);
		$hhtml .= "\n<br />$header->name";
		$hhtml .= "\n<br />$header->email";
		$hhtml .= "\n<br />".nl2br($header->address);
		$hhtml .= "\n<br />$header->codepostal";
		$hhtml .= "\n<br />$header->city";
		$hhtml .= "\n<br />$header->telephone";
		$hhtml .= "\n<br />$orderid";
// now get the tax rate based on the shipping region in the header        
        $taxes=new taxes();
        $taxrate=$taxes->getTax($header->shipRegion);
        
// create html order details block
        $odetails=new orderdetail();		
		$detailslist=$odetails->getDetailsByOrderId($orderid);

		$dhtml = "<p>"; // detail html
		$dhtml .= "<table width='100%' border='1'>\n";
		$dhtml .= "<tr><th>".JText::_('SC_CODE')."</th><th>".JText::_('SC_DESCRIPTION')."</th><th>".JText::_('SC_PRICE_PER_UNIT')."</th><th>".JText::_('SC_QUANTITY')."</th><th>".JText::_('SC_TOTAL')."</th></tr>";
		foreach ($detailslist as $detail) {
			$dhtml .= "<tr><td>$detail->prodcode</td>\n";
			$dhtml .= "<td>$detail->shorttext - $detail->option</td>\n";
			$dhtml .= "<td>".number_format($detail->unitprice, $decs, $dsep, $tsep)."</td>\n";
			$dhtml .= "<td>$detail->qty</td>\n";
			$dhtml .= "<td><strong>".number_format($detail->qty*$detail->unitprice, $decs, $dsep, $tsep)."</strong></td>\n";
		}
		if ($taxrate>0) { 
			$dhtml .= "<tr><td colspan='2'><td colspan='2'>".JText::_('SC_TAX')."</td><td>".number_format($header->tax, $decs, $dsep, $tsep)."</td>";
		}

		$ship['enabled']=$cfg->get('shippingenabled');
		if($ship['enabled']){
			$dhtml .= "<tr><td colspan='2'><td colspan='2'>".JText::_('SC_SHIPPING_REGION')."</td>";			
            $dhtml .= "<td colspan='1'>".$header->shipRegion."</td></tr>";			
            $dhtml .= "<tr><td colspan='2'><td colspan='2'>".JText::_('SC_SHIPPING_COST')."</td>";			
            $dhtml .= "<td colspan='3'>".$header->shipCost."</td></tr>";
		}		
		$dhtml .= "<tr><td colspan='2'><td colspan='2'>".JText::_('SC_TOTAL')."</td>";
		$dhtml .= "<td>".number_format($header->gtotal+$header->tax+$header->shipCost, $decs, $dsep, $tsep)."</td></tr>\n";
		$dhtml .= "</table>\n";
		$dhtml .= "</p>";

		$emailsubject=JText::_('SC_ORDER')." ".JText::_('SC_FOR')." ".number_format($header->gtotal, $decs, $dsep, $tsep)." ".JText::_('SC_FROM')." ".$header->name;

		if ($usecontentasemail==1) {
			$contentemail=$cfg->get("emailcid");
			$query="select introtext from #__content where id = '$contentemail'";
			$db->setQuery($query);
			$emailcontent=$db->loadResult();
			$fields=new fields(); 
			$fieldslist=$fields->getPublishedFields() ;// the custom fields defined for this system
			$thefields=unserialize($header->customfields); // the fields filled by customers
			foreach ($fieldslist as $key=>$customfield) {
				$emailcontent=str_replace("#".$customfield->name."#", $thefields[$customfield->name], $emailcontent); // replace custom tags with the field names
			}	
			$emailcontent=str_replace("#orderheading#",$hhtml, $emailcontent); // replace the headertag with header html
			$emailcontent=str_replace("#orderdetails#",$dhtml, $emailcontent); // replace detail tag with detail html
			$emailcontent=str_replace("#orderid#",$orderid, $emailcontent); // replace orderid tag with the order ID
			$emailbody=$emailcontent;
		}
		else
		{
			$emailbody=$hhtml.$dhtml; // simply add one after the other without processing anything else
		}

		$mailengine=$cfg->get("mailengine");
		if ($mailengine=="alternative") { 
			// some servers do NOT like to send to an array of addresses
			// so as an alternative way we send the emails one by one
			$from = $mainframe->getCfg('mailfrom');
			$fromname = $mainframe->getCfg('fromname');
			$recipient=trim($header->email); // customer email
			$subject = stripslashes( $emailsubject);
			$body = $emailbody;
			$mode = $mode;
			// send to customer
			$rs = JUtility::sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
			// now send the eventual copies
			$emailcopies=$cfg->get('email_copies'); // the complete address list is already trimmed
			$aemailcopies=explode("\r\n", $emailcopies);
			foreach ($aemailcopies as $key=>$emailaddress) {
				$copyrecipient=trim($emailaddress); // trim each address from any \n ...
				$rs = JUtility::sendMail($from, $fromname, $copyrecipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
			}
		}
		else
		{
			$mailer =& JFactory::getMailer();
			// Build e-mail message format
			$mailer->setSender(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('fromname')));
			$mailer->setSubject( stripslashes( $emailsubject));
			$mailer->setBody($emailbody );
			$mailer->IsHTML($mode);
	
			$emailcopies=$cfg->get('email_copies'); // the complete address list is already trimmed
			$aemailcopies=explode("\n", $emailcopies);
			// Add recipients
			$mailer->addRecipient(trim($header->email));
			// add the copies
			foreach ($aemailcopies as $key=>$emailaddress) {
				$mailer->addRecipient(trim($emailaddress)); // trim each address from any \n ...
			}
			// Send the Mail
			$rs	= $mailer->Send();
		}
		return $rs;
	}
}

class optionsshowas {
    var $type;
    
    function optionsshowas() {
        $this->type[1]=JText::_('SC_HORIZ_RADIO');
        $this->type[2]=JText::_('SC_DROPDOWN');
        $this->type[3]=JText::_('SC_STANDARDLIST');
        $this->type[4]=JText::_('SC_VERT_RADIO');
        $this->type[5]=JText::_('SC_SINGLELINE');
        $this->type[6]=JText::_('SC_CALENDAR');
    }
    
}

class productoption extends JTable { // individual options
	var $id;
	var $optgroupid="";
	var $formula="";
	var $caption="";
    var $description="";
    var $defselect;
    var $disporder;

	function productoption() {
		$db	=& JFactory::getDBO();
	   	$this->__construct( '#__sc_productoptions', 'id', $db );
	}
    
    
    function getbygroup($optgroupid) {
        $query="select * from ".$this->_tbl." where `optgroupid` = '$optgroupid' order by `disporder` asc ";
        $this->_db->setQuery($query);
        $lst=$this->_db->loadObjectList();
        return $lst;
    }
}

class optiongroups extends JTable { // option groups
    var $id;
    var $prodcode;	 	 	 	 	 	 	 
    var $title;		 	 	 	 	 	 	 
    var $showas;	 	 	 	 	 	 	
    var $disporder;
    
    var $redirect;
    var $message;
    
    function optiongroups() {
		$db	=& JFactory::getDBO();
	   	$this->__construct( '#__sc_prodoptiongroups', 'id', $db );
	}
    
    function redirect() {
    }
    
    function redirect2() {
        global $mainframe;
        $mainframe->redirect($this->redirect, $this->message);
    }
    
    function addgroup($prodcode) {
        $this->id=null;
        $this->title=stripslashes( JText::_('SC_UNTITLED_OPTION'));
        $this->showas=1;
        $this->prodcode=$prodcode;
        $this->disporder=0;
        $this->store();
    }
    
    function remove() {
        $id=JRequest::getVar("optgrid");
        $prodid=JRequest::getVar("productid");
        $this->delete($id);            
        $this->message="Option group deleted";
        $this->redirect="index2.php?option=com_caddy&action=products&task=edit&cid[0]=$prodid";

        $this->redirect2();
        
    }
    
    function getgroups($prodcode) {
        $query="select * from ".$this->_tbl." where `prodcode` = '$prodcode' order by `disporder` asc ";
        $this->_db->setQuery($query);
        $lst=$this->_db->loadObjectList();
        return $lst;
    }
    
    function show() {
        $id=JRequest::getVar("optgrid");
        $productid=JRequest::getVar("productid");
        $this->load($id);
        display::showoptgroup($this, $productid);
    }
    
    function saveoptiongroup() {
        print_r($_REQUEST);
        
        $prodid=JRequest::getVar("productid");
        $this->id=JRequest::getVar("id");
        $this->title=JRequest::getVar("title");
        $this->showas=JRequest::getVar("showas");
        $this->prodcode=JRequest::getVar("prodcode");
        $this->disporder=JRequest::getVar("disporder");
        $this->store();
        
        $this->message="Option group saved";
        $this->redirect="index2.php?option=com_caddy&action=products&task=edit&cid[0]=$prodid";

        $this->redirect2();
    }
}

class options extends JTable {
    var $id;
    var $optgroupid;	 	 	 	 	 	 	
    var $description;	 	 	 	 	 	 	 
    var $formula;		 	 	 	 	 	 	 
    var $caption;
    var $defselect;
    var $disporder;
    
    var $redirect;
    var $message;
    
    function options() {
		$db	=& JFactory::getDBO();
	   	$this->__construct( '#__sc_productoptions', 'id', $db );
	}
    
    function redirect() {
    }
    
    function redirect2() {
        global $mainframe;
        $mainframe->redirect($this->redirect, $this->message);
    }
    
    function getindoption($optgrid) {
        $query="select * from `". $this->_tbl."` where `optgrid` = '$optgrid'";
        $this->_db->setQuery($query);
        $lst=$this->_db->loadObjectList();
        return $lst;
    }
    
    function showindoptions() {
        $id=JRequest::getVar("optgrid");
        $productid=JRequest::getVar("productid");
        $po=new productoption();
        $lst=$po->getbygroup($id);
        display::showindoptions($lst, $id, $productid);
    }
    
    function saveoptions() {
        printf("<pre>%s</pre>", print_r($_REQUEST, 1));
        $optgrid=JRequest::getVar("optgrid");
        $productid=JRequest::getVar("productid");
        $optionid=JRequest::getVar("optionid");
        $optionshorttext=JRequest::getVar("optionshorttext");
        $optionformula=JRequest::getVar("optionformula");
        $optioncaption=JRequest::getVar("optioncaption");
        $optiondefselect=JRequest::getVar("optiondefselect");
        $optiondisporder=JRequest::getVar("optiondisporder");
        
        // first, get rid of the existing options, if any
        $query="delete from `".$this->_tbl."` where `optgroupid` = '$optgrid' ";
        $this->_db->setQuery($query);
        $this->_db->query();
        
        // now recreate the new options
        foreach($optionid as $key=>$value) {
            $this->id=null;
            $this->optgroupid=$optgrid;
            $this->description=$optionshorttext[$key];
            $this->formula=$optionformula[$key];
            $this->caption=$optioncaption[$key];
            $this->defselect=($key==$optiondefselect?"1":"0");
            $this->disporder=$optiondisporder[$key];
            $this->store();
        }
        $this->redirect="index.php?option=com_caddy&action=products&task=edit&cid[0]=$productid";
        $this->message=JText::_("Individual options saved");
        $this->redirect2();
    }
    
    function saveoptiongroup() {
//        print_r($_REQUEST);
        
        $prodid=JRequest::getVar("prodid");
        $this->id=JRequest::getVar("id");
        $this->title=JRequest::getVar("title");
        $this->showas=JRequest::getVar("showas");
        $this->prodcode=JRequest::getVar("prodcode");
        $this->disporder=JRequest::getVar("disporder");
        $this->store();
        
        $this->message="Option group saved";
        $this->redirect="index2.php?option=com_caddy&action=products&task=edit&cid[0]=$prodid";

        $this->redirect2();
    }
}

class products extends JTable {
	var $id=null;
	var $prodcode="";
	var $shorttext="";
	var $av_qty=0;
	var $unitprice=0;
	var $published=0;
	var $showas=1;
	var $options="";
	var $optionstitle="";
	var $category="";
	var $shippoints=0;

	function products() {
		$db	=& JFactory::getDBO();
	   	$this->__construct( '#__sc_products', 'id', $db );
	}

	function getProduct($id) {
		$this->load($id);
	}
    
    function getProductCodeList() {
        $query="select `prodcode`, `shorttext` from {$this->_tbl} ";
        $this->_db->setQuery($query);
        $lst=$this->_db->loadObjectList();
        return $lst;
    }

	function getAllProducts($filter=null, $field=null, $orderby=null) {
		global $mosConfig_list_limit, $mainframe;
		$query="select count(*) as total from ".$this->_tbl;
		if($filter) $query .= " where `category` = '$filter'";
		$this->_db->setQuery($query);
		$total=$this->_db->loadResult();
		
		$limit = intval( $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit ) );
		$limitstart = intval( $mainframe->getUserStateFromRequest( "view{scp}limitstart", 'limitstart', 0 ) );
		jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit );
		$query="select * from ".$this->_tbl;
		if ($filter) $query .= " where `category` = '$filter'";
		if ($field) $query .= " order by `$field` $orderby ";
		$this->_db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
		$lst=$this->_db->loadObjectList();
		
		$query="select distinct `category` from ".$this->_tbl;
		$this->_db->setQuery($query);
		$lstcategories=$this->_db->loadObjectList();
	
		$categories[]=array('value'=>"", 'text'=>"", "");
		if ($lstcategories) {
			foreach ($lstcategories as $cat) {
				$categories[]=array('value'=>$cat->category, 'text'=>$cat->category, $filter );
			}
		}
		else
		{
			$categories[]=array('value'=>'None defined', 'text'=>"No categories selectable");
		}
		$lists['category'] = JHTML::_('select.genericlist',  $categories, 'search', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter);
		
		$res['lst']=$lst;
		$res['nav']=$pageNav;
		$res['lists']=$lists;
		return $res;
	}

	function getPublishedProducts() {
		$query="select * from ".$this->_tbl." where published=1";
		$query .= " order by `category`, `shorttext` asc ";
		$this->_db->setQuery($query);
		$lst=$this->_db->loadObjectList();
		return $lst;
	}

	function saveproduct() {
		$this->bind($_REQUEST);
//	printf("<pre>%s</pre>", print_r($_REQUEST, 1));
		$ashorttext=JRequest::getVar("optionshorttext");
		$aformula=JRequest::getVar( "optionformula");
		$acaption=JRequest::getVar( "optioncaption" , null, null, null, JREQUEST_ALLOWHTML);
		$defselect=JRequest::getVar( "optiondefselect");
		$options="";

		if (count($ashorttext)>0) {
			foreach ($ashorttext as $key=>$value) {
				$option=$ashorttext[$key].":".$aformula[$key].":".$acaption[$key].":".($defselect==$key?"default":"");
				$options .= $option . "\r\n";
			}
			$options=trim($options);
		}
		$this->options=$options;
		$this->store();
		return $this->id;
	}

	function getproductByProdCode($id) {
		$query="select * from ".$this->_tbl." where prodcode='$id'";
		$this->_db->setQuery($query);
		$p=$this->_db->loadObject();
		return $p;
	}

	function publishProduct( $cid=null, $publish=1) {
		$cids = implode( ',', $cid );
		$query = "UPDATE ".$this->_tbl
		. "\n SET published = " . intval( $publish )
		. "\n WHERE id IN ( $cids )"
		;
		$this->_db->setQuery( $query );
		$this->_db->query();
	}

	function RemoveProducts($cid=null) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM ".$this->_tbl." WHERE id IN ( $cids )";
		$this->_db->setQuery( $query );
		$this->_db->query();
	}

	function decfromstore($pid, $qty) {
		$query = "UPDATE ".$this->_tbl." set av_qty= av_qty - $qty WHERE prodcode = '$pid' limit 1";
		$this->_db->setQuery( $query );
		$this->_db->query();
	}
}

class fields extends JTable {
	var $id=null;
	var $name="";
	var $caption="";
	var $type="text";
	var $length=0;
	var $classname="inputbox";
	var $required=0;
	var $ordering;
	var $published=1;
	var $checkfunction="checkfilled";
	var $fieldcontents;

	function fields() {
		$db	=& JFactory::getDBO();
	   	$this->__construct( '#__sc_fields', 'id', $db );
	}

	function getField($id) {
		$this->load($id);
	}
	
	function getFieldNames() { // internal use only
		$query="select `name` from ".$this->_tbl;
		$this->_db->setQuery($query);
		return $this->_db->loadResultArray();
	}

	function getAllFields() {
		global $mosConfig_list_limit, $mainframe;
		$query="select count(*) as total from ".$this->_tbl;
		$this->_db->setQuery($query);
		$total=$this->_db->loadResult();
		
		$limit = intval( $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit ) );
		$limitstart = intval( $mainframe->getUserStateFromRequest( "view{scp}limitstart", 'limitstart', 0 ) );
		jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit );
		$query="select * from ".$this->_tbl. " order by `ordering` ASC ";
		$this->_db->setQuery($query, $pageNav->limitstart, $pageNav->limit);
		$lst=$this->_db->loadObjectList();
		if ($this->_db->getErrorNum()==1146) {
			return null;
		}
		$res['lst']=$lst;
		$res['nav']=$pageNav;
		return $res;
	}

	function getPublishedFields() {
		$query="select * from ".$this->_tbl." where published=1 order by `ordering` asc ";
		$this->_db->setQuery($query);
		$lst=$this->_db->loadObjectList();
		return $lst;
	}

	function getPublishedFieldsArray() {
		$query="select name from ".$this->_tbl." where published=1 order by `ordering` asc ";
		$this->_db->setQuery($query);
		$lst=$this->_db->loadResultArray();
		return $lst;
	}

	function saveField() {
		$this->bind($_REQUEST);
		$this->name=strtolower($this->name);
		$this->name=str_replace(" ","", $this->name);
		$this->store();
		return $this->id;
	}

	function publishField( $cid=null, $publish=1) {
		$cids = implode( ',', $cid );
		$query = "UPDATE ".$this->_tbl
		. "\n SET published = " . intval( $publish )
		. "\n WHERE id IN ( $cids )"
		;
		$this->_db->setQuery( $query );
		$this->_db->query();
	}

	function RemoveFields($cid=null) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM ".$this->_tbl." WHERE id IN ( $cids )";
		$this->_db->setQuery( $query );
		$this->_db->query();
	}
}


class sc_configuration {
	var $cfgset=0;
	
	function sc_configuration($cfgset=0) {
			$this->cfgset=$cfgset;			
	}
	
	function get($kw) {
	$db	=& JFactory::getDBO();
		$query="select setting from #__sc_config where keyword='$kw' and cfgset='$this->cfgset'";
		$db->setQuery($query);
		return trim($db->loadResult());
	}

	function getAll() {
	$db	=& JFactory::getDBO();
		$query="select * from #__sc_config where `cfgset`='$this->cfgset' order by pagename";
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	function setAll() {
	$db	=& JFactory::getDBO();
		$req=array();
		$req=$_REQUEST;
		foreach ($req as $key=>$value) {
			if (substr($key, 0, 3)=="edt") // only edt* fields
			{
				$cfg=new JTable("#__sc_config", "keyword", $db);
				$setting=substr($key,3,32);
//				$value=htmlspecialchars($value);
				$query="UPDATE #__sc_config SET `setting`='$value' WHERE keyword='$setting' AND `cfgset`='$this->cfgset' LIMIT 1;";
				$db->setQuery($query);
				$r=$db->query();
			}
		}
	}
	
	function show() {
		jimport( 'joomla.methods' );
		$cfg=$this->getAll();
		JToolBarHelper::title( JText::_( 'SimpleCaddy Configuration' )); 
		JToolBarHelper::custom( 'saveconfig', 'save.png', 'save_f2.png', 'Save', false,  false );
		JToolBarHelper::cancel();
		JToolBarHelper::custom( 'control', 'back.png', 'back.png', 'Main', false,  false );
	?>
		<form method="post" name="adminForm" action="index2.php">
		<?php
			$currentpage='';
			$i=0;
			foreach ($cfg as $conf) {
				if ($currentpage<>$conf->pagename) {
					if ($currentpage) {
						echo "</tbody></table></fieldset>\n";
					}
					echo "<fieldset class='adminform'>";
					$currentpage=$conf->pagename;
					echo "<legend>".JText::_($currentpage)."</legend>";
					echo "\n<table class='admintable' cellspacing='1'><tbody>";
					$i++;
				}

				echo "\n<tr><td class='configkey'>".JText::_($conf->description)."</td>";
				switch ($conf->type) {
				case "text": 	echo "<td><input type=\"text\" name=\"edt$conf->keyword\" value=\"$conf->setting\" size=\"$conf->sh\">";
							echo "</td></tr>\n";
							break;
				case "textarea": echo "<td><textarea name=\"edt$conf->keyword\" cols=\"$conf->sh\" rows=\"$conf->sv\">$conf->setting</textarea>";
							echo "</td></tr>\n";
							break;
				case "richtext": echo "<td>";
				 			editorArea( 'editor1', $conf->setting, "edt$conf->keyword", '100%', '350', '75', '20' ) ;
				 			echo "</td></tr>\n";
							break;
				case "yesno": 	echo "<td>";
							echo "<input type='radio' name='edt$conf->keyword' ".($conf->setting==0?" checked='checked'":"")." value='0' />".JText::_('No');
							echo "<input type='radio' name='edt$conf->keyword' ".($conf->setting==1?" checked='checked'":"")." value='1' />".JText::_('Yes');
							echo "</td></tr>\n";
							break;
				case "list": 	echo "<td>";
							echo "<select name='edt$conf->keyword'>";
							$txtoptlist=trim($conf->indopts);
							$pairoptlist=explode("\r\n",$txtoptlist);
							foreach ($pairoptlist as $k=>$value) {
								$aline=explode(":", trim($value));
								echo "<option value='".$aline[1]."'".($conf->setting==$aline[1]?" selected":"").">".$aline[0]."</option>\n";
							}
							echo "</select>";
							echo "</td></tr>\n";
							break;
				}
				echo "\n";
			}
		?>
		</td></tr>
		</table>
		<input type="hidden" name="option" value="com_caddy" />
		<input type="hidden" name="action" value="configuration" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0" />
		</form>
	<?php
	}
	
}

class order extends JTable {
	var $id;
	var $name;		 	 	 	 	 	 	 
	var $email;		 	 	 	 	 	 	 
	var $address;		 	 	 				 
	var $codepostal;	 	 	 	 	 	 	 
	var $city;		 	 	 	 	 	 	 
	var $telephone;	 	 	 	 	 	 	 
	var $ordercode="";	 	 	 	 	 	 	 
	var $orderdt;	 	 	 	 	
	var $total; 	 	 	 	 	
	var $tax;	 	 	 	 	
	var $status;
	var $customfields;
	var $ipaddress;
	var $archive=0;
	var $shipRegion;
	var $shipCost;
    var $j_user_id;
	
	function order() {
		$db	=& JFactory::getDBO();
	   	$this->__construct( '#__sc_orders', 'id', $db );
	}
	
	function orderheadcsv() {
		//$this->to
	}
	
	function ordertostring($cids) {
		$field=new fields();
		$aflds=$field->getFieldNames();
		$afields=unserialize($this->customfields);
		$this->afields=$aflds;
		foreach ($aflds as $key=>$value) {
			$this->$value=$afields["$value"];
		}
		$csv="";
		$fldsep=",";
		$recsep="\r\n";
		$csvheader = "orderid".$fldsep."orderdate".$fldsep."total".$fldsep."tax".$fldsep."Shipping Cost".$fldsep."Shipping Region".$fldsep."status";
		foreach ($aflds as $key=>$value) {
			$csvheader .= $fldsep . "$value";
		}
		
		$csvheader .= $fldsep . "productcode".$fldsep."qty".$fldsep."unitprice".$fldsep."total".$fldsep."shorttext".$fldsep."option".$recsep;
		$f=fopen("components/com_caddy/exports/export.txt", "w+");
		fwrite($f, $csvheader);
		foreach ($cids as $key=>$orderid) {
			$this->load($orderid);
//			printf("<pre>%s</pre>", print_r($this, 1));
			$csvline="$this->id".$fldsep."$this->orderdt".$fldsep."$this->total".$fldsep."$this->tax".$fldsep."$this->shipCost".$fldsep."$this->shipRegion".$fldsep."$this->status".$fldsep;
			foreach ($aflds as $key=>$value) {
				$csvline .= $this->$value . $fldsep ;
			}
			
			$detlin="";
			$details=new orders();
			$lst=$details->getOrderDetails($this->id);
			$afields=unserialize($this->customfields);
			foreach ($lst as $d) {
				$detlin .= $csvline . $d->prodcode . $fldsep . $d->qty . $fldsep . $d->unitprice . $fldsep . $d->total . $fldsep . $d->shorttext . $fldsep . $d->option ;
				foreach ($aflds as $key=>$value) {
					$detlin .= $fldsep .$afields["$value"] ;
				}
				$detlin .= $recsep;
				fwrite($f, $detlin);
				$detlin="";			
			}
		}
		
		fclose($f);
	//	$csvline .= $recsep;

		$csv=$csvheader;
		$csv .= $detlin;
		
		return $csv;
	}
}

class orderdetail extends JTable {
    var $id;
    var $orderid;
    var $prodcode;
    var $qty;
    var $unitprice;
    var $total;
    var $shorttext;
    var $option;

	function orderdetail() {
		$db	=& JFactory::getDBO();
	   	$this->__construct( '#__sc_odetails', 'id', $db );
	}
    
    function getDetailsByOrderId($orderid){
        $query="select * from {$this->_tbl} where `orderid` = '$orderid'";
        $this->_db->setQuery($query);
        $lst=$this->_db->loadObjectList();
        return $lst;
    }
    
}

class orders {
	function store_new_order($cart,$ship) { 
    	if (count($cart)==0) return;
//   	$db	=& JFactory::getDBO();
    	//get statuses
    	$cfg=new sc_configuration();
    	$statuses=explode("\n", trim($cfg->get("ostatus")));
        // get the first status from the list
    	$status=(isset($statuses[0])?trim($statuses[0]):JText::_('SC_NO_STATUS') );
        
        $juser=JFactory::getUser();
    
    	//create order info from the details page
    	$o=new order();
    	$o->bind($_POST);
    	$o->id=null; // ensure a new order is created here
        $o->j_user_id=$juser->id; // add the user id
    	$o->orderdt=mktime();
    	$o->status=$status;
    	$o->customfields=serialize($_REQUEST);
    	if($ship['enabled']){
    		$o->shipCost=$ship['cost'];
    		$o->shipRegion=$ship['region'];
    	}
    	$o->store();
    
    	$orderid=$o->_db->insertid();
    
    	$gtotal=0;
    	//create order details from cookie
    	foreach ($cart as $key=>$product) {
    		unset($odet);
    		$odet=new orderdetail();
    		$odet->id=null;
    		$odet->orderid=$orderid;
    		$odet->prodcode=$product->prodcode;
    		$odet->qty=$product->quantity;
    		$odet->unitprice=$product->finalprice;
    		$odet->total=$product->quantity*$product->finalprice;
    		$odet->shorttext=$product->prodname;
    		$odet->option=$product->option;
            $odet->store();
    		$gtotal=$gtotal+$odet->total;
    		//$db->insertObject("#__sc_odetails", $odet);
    	}
        // get taxes based on shipping region (if any)        
        $ctax=new taxes();
        $taxrate=$ctax->getTax(@$ship['region']);

        $o = new order();
        $o->load($orderid);

    	$o->total=$gtotal;
    	$o->tax=$gtotal*$taxrate;
//		$o->id=$orderid;
        $o->store();
//		$db->updateObject("#__sc_orders", $o, "id");
//		echo $db->getErrorMsg();
		return $orderid;
	}


	function getAllOrders($field=null, $type='', $special=0, $filter=null, $archive=0) {
	global $mainframe, $mosConfig_list_limit, $mosConfig_absolute_path, $option;
	$db	=& JFactory::getDBO();
		$query="select * from #__sc_orders where archive=$archive ";

		if ($filter) {
			if (is_numeric($filter)) {
				$query .= " and #__sc_orders.id = '$filter' ";
			}
			else
			{
				$query .= " and name like '%$filter%' ";
			}
		}

		if ($field) {
			$query .= " order by `$field` $type";
		}
		$db->setQuery($query);
		$lst=$db->loadObjectList();

		$limit = intval( $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit ) );
		$limitstart = intval( $mainframe->getUserStateFromRequest( "view{orders}limitstart", 'limitstart', 0 ) );
		$total=count($lst);

		jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit );

		$db->setQuery($query, $limitstart, $limit);
		$lst=$db->loadObjectList();
		if ($db->getErrorNum()) {
			echo $db->getErrorMsg();
			echo $db->getQuery();
		}
		$res=array();
		$search	= $mainframe->getUserStateFromRequest( 'search', 'search', '', 'string' );
		$search	= JString::strtolower($search);
		$res['search'] = $search;
		$res['lst']=$lst;
		$res['nav']=$pageNav;
		return $res;
	}

	function getorder($id) {
	$db	=& JFactory::getDBO();
		$query="select * from #__sc_orders where id='$id'";
		$db->setQuery($query);
		$p=$db->loadObject();
		return $p;
	}

	function getOrderDetails($orderid) {
	global $mainframe, $mosConfig_list_limit, $mosConfig_absolute_path, $option;
	$db	=& JFactory::getDBO();
		$query="select * from #__sc_odetails where `orderid`='$orderid'";
		$db->setQuery($query);
		$lst=$db->loadObjectList();
		return $lst;
	}

	function getODetails($id) {
	global $mainframe, $mosConfig_list_limit, $mosConfig_absolute_path, $option;
	$db	=& JFactory::getDBO();
		$query="select * from #__sc_odetails where orderid='$id'";
		$db->setQuery($query);
		$lst=$db->loadObjectList();

		$limit = intval( $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit ) );
		$limitstart = intval( $mainframe->getUserStateFromRequest( "view{items}limitstart", 'limitstart', 0 ) );
		$total=count($lst);

		jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit );

		$db->setQuery($query, $limitstart, $limit);
		$lst=$db->loadObjectList();
		if ($db->getErrorNum()) {
			echo $db->getErrorMsg();
			echo $db->getQuery();
		}
		$res=array();
		$res['lst']=$lst;
		$res['nav']=$pageNav;
		return $res;
	}

	function RemoveOrders($cid=null) {
	$db	=& JFactory::getDBO();
	//remove the orders
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__sc_orders WHERE id IN ( $cids )";
		$db->setQuery( $query );
		$db->query();
		$query = "DELETE FROM #__sc_odetails WHERE orderid IN ( $cids )";
		$db->setQuery( $query );
		$db->query();
	}

	function saveOrder() {
		//save an edited order. only field changed is the status!
	$db	=& JFactory::getDBO();
		$id=JRequest::getVar( 'id', 'cp error');
		$status=JRequest::getVar( "edtostatus");
		$archive=JRequest::getVar("archive");
		$order=$this->getorder($id);
		$order->status=$status;
		$db->updateObject("#__sc_orders", $order, "id");
	}
}

class taxes {
	function getTax($shipZone=null){
		$cfg=new sc_configuration();

        // calculate the standard tax rate
		$cfp=$cfg->get ("taxrate");
		$cfp=str_replace("%", "", $cfp);
		if ($cfp>1) {
			$cfp=$cfp/100;
		}
		$taxrate=$cfp;
        
        if (!$shipZone) { // no shipping, show the standard tax rate
            return $taxrate;
        }
        else
        {
            $who=$cfg->get("whopaystax");
            if ($who=="all") { // get the standard tax rate for everyone, same as no shipping
                return $taxrate;
            }
            if ($who=="none") { // get the standard tax rate for everyone, same as no shipping
                return 0;
            }
            if ($who=="special") { // only some regions pay tax
                $stp=$cfg->get("taxpays"); // get the regions
                $atp=explode("\r\n", $stp);
                foreach ($atp as $key=>$value) { // walk through regions for a match
                    if (strpos("$value", ":") ) { // if region contains a : then specific tax per regions are set
                        list($tzone, $cfp)=explode(":", $value); // separate the region from taxrate
                        if ($tzone==$shipZone) { // match for the region
                    		// normalize the taxrate
                            $cfp=str_replace("%", "", $cfp);
                    		if ($cfp>1) {
                    			$cfp=$cfp/100;
                    		}
                    		$taxrate=$cfp;
                            return $taxrate;    
                        } 
                    }
                    else
                    { // simple regions entered, just check for match and return standard rate
                        if ($shipZone==$value) {
                            return $taxrate;
                        }
                    }
                }
                return 0; // zone not found => no tax info return zero
            }
        }
	}
    
}

?>
