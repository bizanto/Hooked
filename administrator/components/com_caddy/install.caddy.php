<?php
/**
* @package SimpleCaddy 1.75 for Joomla 1.5
* @copyright Copyright (C) 2006-2011 Henk von Pickartz. All rights reserved.
*/
// ensure this file is being included by a parent file
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

function com_install() {
	global $mainframe;
	$db=JFactory::getDBO();
    
    $iq=array();
    $iq[]="DROP TABLE IF EXISTS `#__sc_config`;";
    $iq[]="CREATE TABLE IF NOT EXISTS `#__sc_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  `setting` text NOT NULL,
  `cfgset` int(11) NOT NULL DEFAULT '0',
  `type` enum('text','textarea','richtext','yesno','list') NOT NULL,
  `indopts` text NOT NULL,
  `sh` int(11) NOT NULL,
  `sv` int(11) NOT NULL,
  `pagename` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `kw` (`keyword`)
);";
    $iq[]="CREATE TABLE IF NOT EXISTS `#__sc_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NULL,
  `caption` varchar(32) NULL,
  `type` varchar(16) NULL,
  `length` int(11) NULL,
  `classname` varchar(64)  NULL,
  `required` int(11) NULL,
  `ordering` int(11) NULL,
  `published` int(11) NULL,
  `checkfunction` varchar(64) NULL,
  `fieldcontents` text NULL,
  PRIMARY KEY (`id`)
);";
    
    $iq[]="CREATE TABLE IF NOT EXISTS `#__sc_odetails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderid` int(11) NULL,
  `prodcode` varchar(10)  NULL,
  `qty` int(11)  NULL,
  `unitprice` float  NULL,
  `total` float  NULL,
  `shorttext` varchar(255)  NULL,
  `option` varchar(32)  NULL,
  PRIMARY KEY (`id`)
);";

    $iq[]="CREATE TABLE IF NOT EXISTS `#__sc_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255)  NULL,
  `email` varchar(255)  NULL,
  `address` text  NULL,
  `codepostal` varchar(15)  NULL,
  `city` varchar(32)  NULL,
  `telephone` varchar(32)  NULL,
  `ordercode` varchar(16)  NULL,
  `ipaddress` varchar(16)  NULL,
  `customfields` text  NULL,
  `orderdt` int(11)  NULL,
  `total` float  NULL,
  `status` varchar(16)  NULL,
  `tax` float  NULL,
  `archive` int(11)  NULL,
  `shipCost` varchar(10)  NULL,
  `shipRegion` varchar(255)  NULL,
  PRIMARY KEY (`id`)
);";
  
    $iq[]="CREATE TABLE IF NOT EXISTS `#__sc_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prodcode` varchar(13)  NULL,
  `shorttext` varchar(255)  NULL,
  `av_qty` int(11)  NULL,
  `unitprice` float  NULL,
  `published` int(11)  NULL,
  `optionstitle` varchar(32)  NULL,
  `options` text  NULL,
  `showas` int(11)  NULL,
  `category` varchar(255)  NULL,
  `shippoints` varchar(10)  NULL,
  PRIMARY KEY (`id`)
);";
    
    $iq[]="CREATE TABLE IF NOT EXISTS `#__sc_ship_zones` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100)  NULL,
  `points_lower` float  NULL,
  `price` varchar(100)  NULL,
  `points_upper` float  NULL,
  PRIMARY KEY (`id`)
);";

    $iq[]="CREATE TABLE IF NOT EXISTS `#__sc_vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255)  NULL,
  `formula` varchar(255)  NULL,	
  `avqty` int(11)  NULL,
  `qtylimited` int(11)  NULL,
  `validfrom` int(11)  NULL,
  `validto` int(11)  NULL,
  `datelimited` int(11)  NULL,
  `published` int(11)  NULL,
  `lot` varchar(32)  NULL,
  PRIMARY KEY (`id`)
);
";
    
    $iq[]="CREATE TABLE IF NOT EXISTS `#__sc_prodoptiongroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prodcode` varchar(32) NOT NULL,
  `title` varchar(255) NOT NULL,
  `showas` int(11) NOT NULL,
  `disporder` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);";

    $iq[]="CREATE TABLE IF NOT EXISTS `#__sc_productoptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `optgroupid` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `formula` varchar(255) NOT NULL,
  `caption` varchar(255) NOT NULL,
  `defselect` int(11) NOT NULL,
  `disporder` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);";

    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(1, 'currency', 'Currency symbol', 'CAD', 0, 'text', '', 0, 0, 'Finance');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(2, 'show_emptycart', 'Show Empty Cart button', '1', 0, 'yesno', '', 0, 0, 'Frontend Display');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(3, 'thousand_sep', 'Thousands separator', ',', 0, 'text', '', 0, 0, 'Frontend Display');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(4, 'decimal_sep', 'Decimal separator', '.', 0, 'text', '', 0, 0, 'Frontend Display');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(5, 'decimals', 'Number of decimals', '2', 0, 'text', '', 0, 0, 'Frontend Display');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(6, 'remove_button', 'Show remove button', '1', 0, 'yesno', '', 0, 0, 'Frontend Display');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(7, 'email_customer', 'Send confirmation email to customer', '1', 0, 'yesno', '', 0, 0, 'Communication');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(8, 'email_copies', 'Send confirmation email COPIES to', 'me@mysite.com\r\nyou@mysite.com', 0, 'textarea', '', 40, 10, 'Communication');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(9, 'curralign', 'Currency symbol position', '0', 0, 'list', 'Before amount:1\r\nAfter amount:0', 0, 0, 'Frontend Display');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(10, 'ostatus', 'Order statuses (one per line)', 'New\r\nReviewed\r\nReview\r\nCancelled\r\nTreated\r\nArchive', 0, 'textarea', '', 20, 10, 'Order statuses');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(11, 'taxrate', 'Tax rate to apply (VAT/PDV/BTW etc)', '0', 0, 'text', '', 0, 0, 'Finance');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(12, 'emailhtml', 'Email is in HTML format', '0', 0, 'yesno', '', 0, 0, 'Communication');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(13, 'dateformat', 'Date format', 'd-m-y', 0, 'text', '', 0, 0, 'Frontend Display');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(14, 'timeformat', 'Time format', 'h:i:s', 0, 'text', '', 0, 0, 'Frontend Display');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(15, 'reselleremail', 'Reseller email for PayPal', 'change@me.com', 0, 'text', '', 40, 0, 'PayPal');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(16, 'usepaypal', 'Use PayPal for checkout', '1', 0, 'yesno', '', 0, 0, 'PayPal');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(17, 'paypalcurrency', 'PayPal currency', 'CAD', 0, 'list', 'Australian Dollars (AUD):AUD \r\nCanadian Dollars (CAD):CAD \r\nEuros (EUR):EUR \r\nPounds Sterling (GBP):GBP \r\nYen (YEN):JPY \r\nU.S. Dollars (USD):USD \r\nNew Zealand Dollar (NZD):NZD \r\nSwiss Franc:CHF \r\nHong Kong Dollar (HKD):HKD \r\nSingapore Dollar (SGD):SGD \r\nSwedish Krona:SEK \r\nDanish Krone:DKK \r\nPolish Zloty:PLN \r\nNorwegian Krone:NOK \r\nHungarian Forint:HUF \r\nCzech Koruna:CZK \r\nMexican Pesos:MXN \r\nSouth African Rand:R', 0, 0, 'PayPal');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(18, 'usecidasemail', 'Use content as email', '0', 0, 'yesno', '', 0, 0, 'Communication');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(19, 'usestdproduct', 'Use the Systematic Product', '0', 0, 'yesno', '', 10, 0, 'Finance');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(20, 'emailcid', 'Confirmation content ID', '0', 0, 'text', '', 3, 0, 'Communication');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(21, 'cart_fee_product', 'Systematic Product (code) to add to cart', 'extracost', 0, 'text', '', 10, 0, 'Finance');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(22, 'prodcats', 'Product categories (one per line)', 'Food\r\nDrinks\r\nPlants\r\nChemistry\r\nNon-Food', 0, 'textarea', '', 40, 5, 'Product categories');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(23, 'ppreturnsuccess', 'PayPal Success return url', '', 0, 'text', '', 80, 0, 'PayPal');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(24, 'ppreturncancel', 'PayPal Failure/Cancel return url', '', 0, 'text', '', 80, 0, 'PayPal');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(25, 'pgclassname', 'Payment gateway classname', 'scpaypal', 0, 'text', '', 0, 0, 'Payment gateway');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(26, 'mailengine', 'Mail engine', 'joomla', 0, 'list', 'Joomla:joomla\r\nAlternative:alternative', 0, 0, 'Communication');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(27, 'shippingenabled', 'If Enabled, Shipping Addon will be used to calculate Shipping Cost', '0', 0, 'yesno', '', 0, 0, 'Shipping');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(28, 'checkminqty', 'Check minimum quantity in DB', '0', 0, 'yesno', '', 0, 0, 'Checking out');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(29, 'mincheckout', 'Minimum AMOUNT For checkout', '0', 0, 'text', '', 0, 0, 'Checking out');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(30, 'usevouchers', 'Use the vouchers?', '0', 0, 'yesno', '', 0, 0, 'Checking out');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(31, 'pretextid', 'Content ID above cart display', '0', 0, 'text', '', 0, 0, 'Frontend Display');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(32, 'posttextid', 'Content ID below cart display', '0', 0, 'text', '', 0, 0, 'Frontend Display');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(36, 'email_fromname', 'Send email from name', '', 0, 'text', '', 40, 10, 'Communication');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(37, 'email_fromname', 'Send email from', '', 0, 'text', '', 40, 10, 'Communication');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(33, 'whopaystax', 'Who pays tax', 'special', 0, 'list', 'Everybody:all\r\nSpecific destinations:special\r\nNobody:none\r\n', 0, 0, 'Finance');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(34, 'taxpays', 'Specific tax destinations', 'SK:10\r\nBC:15\r\nMB:20', 0, 'textarea', '', 60, 10, 'Finance');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(35, 'taxonshipping', 'Pay tax on shipping', '0', 0, 'yesno', '', 0, 0, 'Finance');";
    $iq[]="INSERT IGNORE INTO `#__sc_config` VALUES(38, 'ppenvironment', 'Environment', '0', 0, 'list', 'Production:0\r\nSandbox:1', 0, 0, 'PayPal');";

	foreach ($iq as $key=>$query) {
		$db->setquery($query);
		$db->query();
	}

      
	$query="show columns from #__sc_orders";
	$db->setQuery($query);
	$lstcf=$db->loadObjectList();
	$customfield=false;
	$ipaddress=false;
	$archive=false;
	foreach ($lstcf as $f) {
		if ($f->Field=='customfields') {
			$customfield=true;
			if ($f->Type=="varchar(255)") {
				// change the field type
				$query="ALTER TABLE `#__sc_orders` CHANGE `customfields` `customfields` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;";
				$db->setQuery($query);
				$db->query();
				$customfield=true;
				echo "Changing customfields type to TEXT; <br />";
			}
		}
		if ($f->Field=='ipaddress') {
			$ipaddress=true;
		}
		if ($f->Field=='archive') {
			$archive=true;
		}
		if ($f->Field=='shipCost') {
			$shipcost=true;
		}
		if ($f->Field=='shipRegion') {
			$shipregion=true;
		}
		if ($f->Field=='j_user_id') {
			$juserid=true;
		}
	}
	if (!$archive) {
		// add the customfield
		$query="ALTER TABLE `#__sc_orders` ADD  `archive` INT(11) NULL;";
		$db->setQuery($query);
		$db->query();
		echo "Adding archive to database";
	}
	if (!$customfield) {
		// add the customfield
		$query="ALTER TABLE `#__sc_orders` ADD  `customfields` TEXT NULL;";
		$db->setQuery($query);
		$db->query();
		echo "Adding customfields to database";
	}
	if (!$ipaddress) {
		// add the customfield
		$query="ALTER TABLE `#__sc_orders` ADD  `ipaddress` varchar(32) NULL;";
		$db->setQuery($query);
		$db->query();
		echo "Adding ipaddress to database";
	}
	if (!$shipcost) {
		// add the customfield
		$query="ALTER TABLE `#__sc_orders` ADD  `shipCost` varchar(10) NULL;";
		$db->setQuery($query);
		$db->query();
		echo "Adding shipping cost to database";
	}
	if (!$shipregion) {
		// add the customfield
		$query="ALTER TABLE `#__sc_orders` ADD  `shipRegion` varchar(255) NULL;";
		$db->setQuery($query);
		$db->query();
		echo "Adding shipping region to database";
	}
	if (!$juserid) {
		// add the customfield
		$query="ALTER TABLE `#__sc_orders` ADD  `j_user_id`  int(11) NULL DEFAULT NULL;";
		$db->setQuery($query);
		$db->query();
		echo "Adding userid to orders table";
	}
    

	$db=JFactory::getDBO();
	$query="show columns from #__sc_fields";
	$db->setQuery($query);
	$lstcf=$db->loadObjectList();
	$fieldcontents=false;
	foreach ($lstcf as $f) {
		if ($f->Field=='fieldcontents') {
			$fieldcontents=true;
		}
	}
	if (!$fieldcontents) {
		// add the customfield
		$query="ALTER TABLE `#__sc_fields` ADD  `fieldcontents` TEXT NULL;";
		$db->setQuery($query);
		$db->query();
		echo "Adding fieldcontents to database";
	}
// fill in the standard custom fields
	$q[]="INSERT IGNORE INTO `#__sc_fields` VALUES(1, 'name', 'Name', 'text', 60, 'inputbox', 0, 0, 1, 'checkfilled', '');";
	$q[]="INSERT IGNORE INTO `#__sc_fields` VALUES(2, 'address', 'Address', 'textarea', 0, 'inputbox', 1, 3, 1, 'checkfilled', '');";
	$q[]="INSERT IGNORE INTO `#__sc_fields` VALUES(3, 'codepostal', 'Postal code / Zipcode', 'text', 10, 'inputbox', 0, 4, 1, 'checkfilled', '');";
	$q[]="INSERT IGNORE INTO `#__sc_fields` VALUES(10, 'deliverybefore', 'Deliver before', 'date', 0, 'inputbox', 1, 99, 1, 'checkfilled', '');";
	$q[]="INSERT IGNORE INTO `#__sc_fields` VALUES(5, 'city', 'City', 'text', 40, 'inputbox', 0, 5, 1, 'checkfilled', '');";
	$q[]="INSERT IGNORE INTO `#__sc_fields` VALUES(6, 'telephone', 'Phone', 'text', 40, 'inputbox', 0, 15, 1, 'checkfilled', '');";
	$q[]="INSERT IGNORE INTO `#__sc_fields` VALUES(7, 'email', 'Email', 'text', 40, 'inputbox', 0, 20, 1, 'checkfilled', '');";
	$q[]="INSERT IGNORE INTO `#__sc_fields` VALUES(8, 'dropdown', 'My Dropdown Field', 'dropdown', 0, 'inputbox', 1, 0, 1, 'checkfilled', 'een;twee;drie;vier;vijf');";
	$q[]="INSERT IGNORE INTO `#__sc_fields` VALUES(11, 'div1', 'Extra info', 'divider', 0, 'divider', 0, 10, 1, 'checkfilled', '');";
	$q[]="INSERT IGNORE INTO `#__sc_fields` VALUES(12, 'coupon', 'Enter coupon (if any)', 'voucher', 0, 'inputbox', 0, 0, 1, 'checkfilled', '');";
// add new fields to the config table
    $q[]="INSERT IGNORE INTO `#__sc_config` VALUES (36, 'email_fromname',  'Send email from name',  '".$mainframe->getCfg('fromname')."',  '0',  'text',  '',  '40',  '10',  'Communication');";
    $q[]="INSERT IGNORE INTO `#__sc_config` VALUES (37, 'email_fromname',  'Send email from',  '".$mainframe->getCfg('mailfrom')."',  '0',  'text',  '',  '40',  '10',  'Communication');";
    $q[]="INSERT IGNORE INTO `#__sc_config` VALUES (32, 'posttextid', 'Content ID below cart display', '0', 0, 'text', '', 0, 0, 'Frontend Display');";
    $q[]="INSERT IGNORE INTO `#__sc_config` VALUES (33, 'whopaystax', 'Who pays tax', 'special', 0, 'list', 'Everybody:all\r\nSpecific destinations:special\r\nNobody:none\r\n', 0, 0, 'Finance');";
    $q[]="INSERT IGNORE INTO `#__sc_config` VALUES (34, 'taxpays', 'Specific tax destinations', 'SK:10\r\nBC:15\r\nMB:20', 0, 'textarea', '', 60, 10, 'Finance');";
    $q[]="INSERT IGNORE INTO `#__sc_config` VALUES (35, 'taxonshipping', 'Pay tax on shipping', '0', 0, 'yesno', '', 0, 0, 'Finance');";
	


	foreach ($q as $key=>$query) {
		$db->setquery($query);
		$db->query();
	}

	$db=JFactory::getDBO();
	$query="show columns from #__sc_products";
	$db->setQuery($query);
	$lstcf=$db->loadObjectList();
	$shippoints=false;
	foreach ($lstcf as $f) {
		if ($f->Field=='shippoints') {
			$shippoints=true;
		}
	}
	if (!$shippoints) {
		// add the customfield
		$query="ALTER TABLE `#__sc_products` ADD  `shippoints` INT(11);";
		$db->setQuery($query);
		$db->query();
		echo "Adding Shipping points to database";
	}


}
?>