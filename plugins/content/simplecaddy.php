<?php
/**
* @package SimpleCaddy 1.75 for Joomla 1.5
* @copyright Copyright (C) 2006-2011 Henk von Pickartz. All rights reserved.
*/
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die();
$debug=0;

jimport( 'joomla.plugin.plugin' );
JPlugin::loadLanguage( 'plg_simplecaddy' );

$mainframe->registerEvent( 'onPrepareContent', 'plgContentCaddy' );
require_once (JPATH_ROOT.DS.'components'.DS.'com_caddy'.DS.'caddy.class.php');
require_once (JPATH_ROOT.DS.'components'.DS.'com_caddy'.DS.'caddy.cart.class.php');

class dis {
	var $tsep;
	var $dsep;
	var $decs;
	var $currency;
	var $curralign;
	var $separator;
	function dis () {
		$cfg=new sc_configuration();
		$this->tsep=$cfg->get('thousand_sep');
		$this->dsep=$cfg->get('decimal_sep');
		$this->decs=$cfg->get('decimals');
		$this->currency=$cfg->get('currency');
		$this->curralign=$cfg->get('curralign');
	}	

    function optionshow1($lstoptions, $product, $groupid, $classsuffix, $picname="") {// horizontal radio buttons
        $html="";
        foreach ($lstoptions as $po) {
            $optid=$po->id;
            $shorttext=$po->description;
            $formula=$po->formula;
            $caption=$po->caption;
            $defselect=$po->defselect;
            $id=md5($product->prodcode.$shorttext.$picname);
            $checked="";
            if (trim($defselect) == "1") $checked=" checked='checked' ";
            $html .= "<input type='radio' name='edtoption[$groupid]' value='$id:$optid' $checked />".stripslashes($shorttext)."\n";
        }
        return $html;
    }

    function optionshow2($lstoptions, $product, $groupid, $classsuffix, $picname="") {// dropdown list
        $html="";
		$html.="<div class='scoptionselect$classsuffix'><select name='edtoption[$groupid]'>\n";
		foreach ($lstoptions as $po) {
            $optid=$po->id;
            $shorttext=$po->description;
            $formula=$po->formula;
            $caption=$po->caption;
            $defselect=$po->defselect;
            $id=md5($product->prodcode.$shorttext.$picname);
            $checked="";
            if (trim($defselect) == "1") $checked=" selected='selected' ";
            $html .= "<option value='$id:$optid' $checked>".stripslashes($shorttext)." $caption</option>\n";
		}
		$html .= "</select></div>\n";
        return $html;
    }

    function optionshow3($lstoptions, $product, $groupid, $classsuffix, $picname="") {// standard list
        $html="";
		$html.="<div class='scoptionselect$classsuffix'><select name='edtoption[$groupid]' size='10'>\n";
		foreach ($lstoptions as $po) {
            $optid=$po->id;
            $shorttext=$po->description;
            $formula=$po->formula;
            $caption=$po->caption;
            $defselect=$po->defselect;
            $id=md5($product->prodcode.$shorttext.$picname);
            $checked="";
            if (trim($defselect) == "1") $checked=" selected='selected' ";
            $html .= "<option value='$id:$optid' $checked>".stripslashes($shorttext)." $caption</option>\n";
		}
		$html .= "</select></div>\n";
        return $html;
    }

    function optionshow4($lstoptions, $product, $groupid, $classsuffix, $picname="") {// vertical radio buttons
        $html="";
		foreach ($lstoptions as $po) {
            $optid=$po->id;
            $shorttext=$po->description;
            $formula=$po->formula;
            $caption=$po->caption;
            $defselect=$po->defselect;
            $id=md5($product->prodcode.$shorttext.$picname);
            $checked="";
            if (trim($defselect) == "1") $checked=" checked='checked' ";
            $html .= "\n<input type='radio' name='edtoption[$groupid]' value='$id:$optid' $checked />".stripslashes($shorttext);
            $html.=" $caption<br />";
		}
        return $html;
    }

    function optionshow5($lstoptions, $product, $groupid, $classsuffix, $picname="") {// free text
        $html="";
		$html.="<input type='text' name='edtoption[$groupid]'>\n";
		$html.="<input type='hidden' name='edtoption2' value='hidden'>\n";
        return $html;
    }

    function optionshow6($lstoptions, $product, $groupid, $picname="") {// calendar control
        $html="";
        $html.="<script type='text/JavaScript' src='components/com_caddy/js/jacs.js'></script>";
		$html.="<input type='text' name='edtoption[$groupid]' onClick='JACS.show(this,event);'>\n";
		$html.="<input type='hidden' name='edtoption2' value='hidden'>\n";
        return $html;
    }


}

	function plgContentCaddy( &$article, &$params, $limitstart ) {
		global $mainframe;
		// get a style sheet only valid if you call the plugin ONCE per page!
//		$mainframe->addCustomHeadTag('<link rel="stylesheet" href="components/com_caddy/css/simplecaddy.css" type="text/css" />');
		
	 	$regex = '/{(simplecaddy)\s*(.*?)}/i';
	 	$plugin =& JPluginHelper::getPlugin('content', 'simplecaddy');
		// to get any params for the plugin
		$pluginParams	= new JParameter( $plugin->params );
	
		//get global params from config
		$dis=new dis();
		$parms=array();
		$matches = array();
		preg_match_all( $regex, $article->text, $matches, PREG_SET_ORDER );
		foreach ($matches as $elm) {
			$line=str_replace("&nbsp;", " ", $elm[2]);
			$line=str_replace(" ", "&", $line);
			parse_str( $line, $parms );
			if (isset($parms['category'])){
				$html=getSCbyCategory($parms, $pluginParams, 1);
			}
			else
			{
				$html=getSCSingle($parms, $pluginParams, 1);
			}
			$article->text = preg_replace($regex, $html, $article->text, 1);
		}
		return true;
	}
    
	function getSCSingle($params, $pluginParams, $debugmode=0) {
		global $Itemid, $mainframe;
		$dis=new dis();
//	printf("<pre>%s</pre>", print_r($pluginParams,1));
		$classsuffix=isset($params['classsfx']) ? $params['classsfx'] : "";
		$prodcode=$params['code'];
		$defaultqty=isset($params['defqty']) ? $params['defqty'] : 1; // default qty set in qty edit box
		$minqty=isset($params['minqty']) ? $params['minqty'] : 0;
		$strqties=isset($params['qties']) ? $params['qties'] : null;
		$checkoos=isset($params['checkoos']) ? $params['checkoos'] : 0;
		$picname=isset($params['picname']) ? $params['picname'] : "";
		$aqties=explode(",", $strqties);
	
		$db	=& JFactory::getDBO();
		$query="SELECT * FROM #__sc_products WHERE prodcode='$prodcode'";
		$db->setQuery( $query );
		$product = $db->loadObject();
	
		if (!$product) {
			$html  ="<div class='sccart$classsuffix'>";
            $str=JText::sprintf("SC_PRODUCT_NOT_FOUND", $prodcode);
			$html .= $str;
			$html .="</div>";
			return $html;
		}
	
		if ($product->published=='0') {
			$html  ="<div class='sccart$classsuffix'>";
            $str= JText::sprintf("SC_PRODUCT_NOT_PUBLISHED", $prodcode);
			$html .= $str;
			$html .="</div>";
			return $html;
		}
	
		if (!$db->query()) {
			echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}
		
		$prodpresentation="";
		
		if ($pluginParams->get('showproductcodetext')) $prodpresentation .= "\n<div class='scproduct$classsuffix'>#PRODUCTCODETEXT# $dis->separator</div>";
		if ($pluginParams->get('showproductcode')) $prodpresentation.="\n<div class='scprodcode$classsuffix'>#PRODUCTCODE# $dis->separator</div>";
		if ($pluginParams->get('showtitle')) $prodpresentation.="\n<div class='scshorttext$classsuffix'>#SHORTTEXT# $dis->separator</div>";
		if ($pluginParams->get('showunitpricetext')) $prodpresentation.="\n<div class='scunitpricetext$classsuffix'>#UNITPRICETEXT# $dis->separator</div>";
		if ($pluginParams->get('showunitprice')) $prodpresentation.="\n<div class='scunitprice$classsuffix'>#UNITPRICE# $dis->separator</div>";
		if ($pluginParams->get('showquantitytext')) $prodpresentation.="\n<div class='scqtytext$classsuffix'>#QUANTITYTEXT# $dis->separator</div>";
		$prodpresentation.="\n<div class='scqty$classsuffix'>#QUANTITY# $dis->separator</div>";
		$prodpresentation.="\n#CARTOPTIONS#";	
		$prodpresentation.="\n<div class='atczone$classsuffix'>#ADDTOCARTBUTTON#</div>";
	
		$html  ="\n<div class='sccart$classsuffix'>";
		$html .="\n<form name='addtocart$product->id' action='index.php' method='post'>";
	
		$amount = number_format($product->unitprice, $dis->decs, $dis->dsep, $dis->tsep);
		if ($dis->curralign==1) {
			$amount = $dis->currency ."&nbsp;". $amount;
		}
		else
		{
			$amount = $amount ."&nbsp;". $dis->currency;
		}
	
		$html .= $prodpresentation;
	   
        $productoptions=new productoption();
        
		$optionhtml ="<div class='cartoptions$classsuffix'>";
		if ($pluginParams->get('showcartoptionstitle')) $optionhtml.="\n<div class='cartoptionstitle$classsuffix'>#CARTOPTIONSTITLE#</div>";

		$hasoptions=0;

        $optgroups=new optiongroups();
        $optiongroups=$optgroups->getgroups($product->prodcode);
        if (count($optiongroups)) {
    		foreach ($optiongroups as $optiongroup) {
                $groupid=$optiongroup->id;
                $options=new productoption();
                $lstoptions=$options->getbygroup($groupid);
                $show="optionshow".$optiongroup->showas;
                if ($pluginParams->get('showcartoptionstitle')) $optionhtml.="\n<div class='cartoptionstitle$classsuffix'>{$optiongroup->title}</div>";
                $optionhtml.= $dis->$show($lstoptions, $product, $groupid, $classsuffix, $picname);
            }
		}
		else
		{ // product without options - generate id from productcode
			$id=md5($product->prodcode.$picname);
			$optionhtml .= "\n<input type='hidden' value='$id' name='edtoption' />";
		}
		$optionhtml .="</div>";
        
        
		if ( $checkoos==1 ) { // check for minimum quantitites/ out of stock
			if ($product->av_qty>=1) { // product still available
				$atcbtn ="\n<input type='submit' name='submit' value='".JText::_('SC_ADD_TO_CART')."' class='scp_atc$classsuffix' />";
			}
			else
			{ // product quantity is 0
				$atcbtn ="\n<input type='submit' name='submit' value='".JText::_('SC_OUT_OF_STOCK')."' class='scp_atc$classsuffix' disabled='disabled' />";
			}
		}
		else
		{
			$atcbtn ="\n<input type='submit' name='submit' value='".JText::_('SC_ADD_TO_CART')."' class='scp_atc$classsuffix' />";
		}	
			
		$qtyfield="\n<input type='". ($pluginParams->get('showquantity')?'text':'hidden') ."' name='edtqty' value='$defaultqty' class='scp_qty$classsuffix' />";

		if ($strqties) { // specific quantities given
			$qtyfield="<select name='edtqty' class='scp_selectqty$classsuffix'>";
			foreach ($aqties as $key=>$value) {
				$qtyfield .= "<option value='$value'>$value</option>";
			}
			$qtyfield .= "</select>";
		}
		$html .="\n<input type='hidden' name='edtprodcode' value='$product->prodcode' />";
		$html .="\n<input type='hidden' name='edtshorttext' value='$product->shorttext' />";
		$html .="\n<input type='hidden' name='edtunitprice' value='$product->unitprice' />";
		$html .="\n<input type='hidden' name='option' value='com_caddy' />";
		$html .="\n<input type='hidden' name='action' value='addtocart' />";
		$html .="\n<input type='hidden' name='picname' value='$picname' />";
		$html .="\n<input type='hidden' name='Itemid' value='$Itemid' />";

		if ($minqty>0) { // check for minimum quantity in the component
			$html .="\n<input type='hidden' name='minqty' value='$minqty' />";
		}

		if ($pluginParams->get('stayonpage')==1) {
			$html .="\n<input type='hidden' name='lasturl' value='".urlencode($_SERVER['REQUEST_URI'])."' />";	
			$html .="\n<input type='hidden' name='stayonpage' value='1' />";	
		}
		$html .="\n</form>";
		$html .="</div>";
	
	//now replace any variables
		$html=str_replace("#PRODUCTCODETEXT#", JText::_('SC_PRODUCT'), $html);
		$html=str_replace("#PRODUCTCODE#", $product->prodcode, $html);
		$html=str_replace("#SHORTTEXT#", stripslashes( $product->shorttext ), $html);
		$html=str_replace("#UNITPRICETEXT#", JText::_('SC_PRICE_PER_UNIT'), $html);
		$html=str_replace("#UNITPRICE#", $amount, $html);
		$html=str_replace("#QUANTITY#", $qtyfield, $html);
		$html=str_replace("#QUANTITYTEXT#", JText::_('SC_QUANTITY'), $html);
		$html=str_replace("#ADDTOCARTBUTTON#", $atcbtn, $html);
		$html=str_replace("#CARTOPTIONS#", $optionhtml, $html);

		if (trim($product->options)) {
			$html=str_replace("#CARTOPTIONSTITLE#", JText::_('SC_OPTIONS'), $html);
		}
		else
		{
			$html=str_replace("#CARTOPTIONSTITLE#", "", $html);
		}

		return $html;
	}

	function getSCbyCategory($params, $pluginParams, $debugmode=0) {
		global $Itemid, $mainframe;
		$dis=new dis();
	//printf("<pre>%s</pre>", print_r($plgParams,1));
		$category=$params['category'];
		$defaultqty=isset($params['defqty']) ? $params['defqty'] : 1;
		$classsuffix=isset($params['classsfx']) ? $params['classsfx'] : "";
	
		$db	=& JFactory::getDBO();
		$query="SELECT * FROM #__sc_products WHERE category='$category' and `published`=1";
		$db->setQuery( $query );
		$lstproduct = $db->loadObjectList();
	
		if (!$lstproduct) {
			$html  ="<div class='sccart$classsuffix'>";
			$html .="<h3>The category ($category) is not found.</h3>";
			$html .="</div>";
			return $html;
		}
	
		if (!$db->query()) {
			echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}
		$html  ="";

		foreach ($lstproduct as $product) {
			$html .="\n<form name='addtocart$product->id' action='index.php' method='post'>";
			$prodpresentation="\n<div class='sccart$classsuffix'>";
			if ($pluginParams->get('showproductcodetext')) $prodpresentation .= "\n<div class='scproduct$classsuffix'>#PRODUCTCODETEXT# $dis->separator</div>";
			if ($pluginParams->get('showproductcode')) $prodpresentation.="\n<div class='scprodcode$classsuffix'>#PRODUCTCODE# $dis->separator</div>";
			if ($pluginParams->get('showtitle')) $prodpresentation.="\n<div class='scshorttext$classsuffix'>#SHORTTEXT# $dis->separator</div>";
			if ($pluginParams->get('showunitpricetext')) $prodpresentation.="\n<div class='scunitpricetext$classsuffix'>#UNITPRICETEXT# $dis->separator</div>";
			if ($pluginParams->get('showunitprice')) $prodpresentation.="\n<div class='scunitprice$classsuffix'>#UNITPRICE# $dis->separator</div>";
			if ($pluginParams->get('showquantitytext')) $prodpresentation.="\n<div class='scqtytext$classsuffix'>#QUANTITYTEXT# $dis->separator</div>";
			$prodpresentation.="\n<div class='scqty$classsuffix'>#QUANTITY# $dis->separator</div>";
			$prodpresentation.="\n#CARTOPTIONS#";	
			$prodpresentation.="\n<div class='atczone$classsuffix'>#ADDTOCARTBUTTON#</div>";
		
		
			$amount = number_format($product->unitprice, $dis->decs, $dis->dsep, $dis->tsep);
			if ($dis->curralign==1) {
				$amount = $dis->currency ."&nbsp;". $amount;
			}
			else
			{
				$amount = $amount ."&nbsp;". $dis->currency;
			}
		
			$html .= $prodpresentation;
		
			$optionhtml ="<div class='cartoptions$classsuffix'>";
			if ($pluginParams->get('showcartoptionstitle')) $optionhtml.="\n<div class='cartoptions$classsuffix'>#CARTOPTIONSTITLE#</div>";
		
		//add the options line here
			$hasoptions=0;
			if (trim($product->options)) {
				$hasoptions=1;
                $show="optionshow".$product->showas;
                $optionhtml.= $dis->$show($product, $picname);
			}
			else
			{ // product without options - generate id from productcode
				$id=md5($product->prodcode);
				$optionhtml .= "\n<input type='hidden' value='$id' name='edtoption' />";
			}
			$optionhtml .="</div>";
			if ($product->av_qty>=1) {
				$atcbtn ="\n<input type='submit' name='submit' value='".JText::_('SC_ADD_TO_CART')."' class='scbutton$classsuffix' />";
			}
			else
			{
				$atcbtn ="\n<input type='submit' name='submit' value='".JText::_('SC_OUT_OF_STOCK')."' class='scbutton$classsuffix' disabled='disabled' />";
			}
			$qtyfield="\n<input type='". ($pluginParams->get('showquantity')?'text':'hidden') ."' name='edtqty' value='$defaultqty' class='scp_qty$classsuffix' />";
			$html .="\n<input type='hidden' name='edtprodcode' value='$product->prodcode' />";
			$html .="\n<input type='hidden' name='edtshorttext' value='$product->shorttext' />";
			$html .="\n<input type='hidden' name='edtunitprice' value='$product->unitprice' />";
			$html .="\n<input type='hidden' name='option' value='com_caddy' />";
			$html .="\n<input type='hidden' name='action' value='addtocart' />";
			if ($pluginParams->get('stayonpage')==1) {
				$html .="\n<input type='hidden' name='lasturl' value='".urlencode($_SERVER['REQUEST_URI'])."' />";	
				$html .="\n<input type='hidden' name='stayonpage' value='1' />";	
			}
			$html .="\n<input type='hidden' name='Itemid' value='$Itemid' />";
			$html .="</div>";
			$html .="\n</form>";
		
		//now replace any variables
			$html=str_replace("#PRODUCTCODETEXT#", JText::_('SC_PRODUCT'), $html);
			$html=str_replace("#PRODUCTCODE#", $product->prodcode, $html);
			$html=str_replace("#SHORTTEXT#", stripslashes( $product->shorttext ), $html);
			$html=str_replace("#UNITPRICETEXT#", JText::_('SC_PRICE_PER_UNIT'), $html);
			$html=str_replace("#UNITPRICE#", $amount, $html);
			$html=str_replace("#QUANTITY#", $qtyfield, $html);
			$html=str_replace("#QUANTITYTEXT#", JText::_('SC_QUANTITY'), $html);
			$html=str_replace("#ADDTOCARTBUTTON#", $atcbtn, $html);
			$html=str_replace("#CARTOPTIONS#", $optionhtml, $html);
			if (trim($product->options)) {
				$html=str_replace("#CARTOPTIONSTITLE#", stripslashes($product->optionstitle), $html);
			}
			else
			{
				$html=str_replace("#CARTOPTIONSTITLE#", "", $html);
			}
		}
		return $html;
	}


?>