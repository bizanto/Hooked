<?php
exit();
define( '_JEXEC', 1 );
define('JPATH_BASE', dirname(__FILE__) );
define( 'DS', DIRECTORY_SEPARATOR );
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
$mainframe =& JFactory::getApplication('site');

$ents = array('�'=>'&Agrave;', '�'=>'&agrave;', '�'=>'&Aacute;', '�'=>'&aacute;', '�'=>'&Acirc;', '�'=>'&acirc;', '�'=>'&Atilde;', '�'=>'&atilde;', '�'=>'&Auml;', '�'=>'&auml;', '�'=>'&Aring;', '�'=>'&aring;', '�'=>'&AElig;', '�'=>'&aelig;', '�'=>'&Ccedil;', '�'=>'&ccedil;', '�'=>'&ETH;', '�'=>'&eth;', '�'=>'&Egrave;', '�'=>'&egrave;', '�'=>'&Eacute;', '�'=>'&eacute;', '�'=>'&Ecirc;', '�'=>'&ecirc;', '�'=>'&Euml;', '�'=>'&euml;', '�'=>'&Igrave;', '�'=>'&igrave;', '�'=>'&Iacute;', '�'=>'&iacute;', '�'=>'&Icirc;', '�'=>'&icirc;', '�'=>'&Iuml;', '�'=>'&iuml;', '�'=>'&Ntilde;', '�'=>'&ntilde;', '�'=>'&Ograve;', '�'=>'&ograve;', '�'=>'&Oacute;', '�'=>'&oacute;', '�'=>'&Ocirc;', '�'=>'&ocirc;', '�'=>'&Otilde;', '�'=>'&otilde;', '�'=>'&Ouml;', '�'=>'&ouml;', '�'=>'&Oslash;', '�'=>'&oslash;', '�'=>'&OElig;', '�'=>'&oelig;', '�'=>'&szlig;', '�'=>'&THORN;', '�'=>'&thorn;', '�'=>'&Ugrave;', '�'=>'&ugrave;', '�'=>'&Uacute;', '�'=>'&uacute;', '�'=>'&Ucirc;', '�'=>'&ucirc;', '�'=>'&Uuml;', '�'=>'&uuml;', '�'=>'&Yacute;', '�'=>'&yacute;', '�'=>'&Yuml;', '�'=>'&yuml;');
$search = array_values($ents);
$replace = array_keys($ents);

$db =& JFactory::getDBO();
$q = "SELECT id, title FROM #__content WHERE title != ''";
$db->setQuery($q);
$results = $db->loadObjectList();
echo count($results)."<br/>\n";
$c = 0;
foreach ($results as $listing) {
	$sql = "UPDATE #__content SET title='".html_entity_decode($listing->title, ENT_COMPAT, 'UTF-8')."' WHERE id=".$listing->id;
	$db->setQuery($sql);
	echo $db->_sql."<br/>\n"; 
	//$db->query();
	//echo $db->_errorMsg."<br/>\n";
	$c++; 
}

echo $c." rows updated";