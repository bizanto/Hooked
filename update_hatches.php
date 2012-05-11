<?php
define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);
require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');
$mainframe &= JFactory::getApplication('site');

$db =& JFactory::getDBO();

$q = "SELECT text, value FROM #__jreviews_fieldoptions WHERE fieldid = 65";
$db->setQuery($q);
$fields = $db->loadObjectList();

$states = array();
foreach ($fields as $field) {
	$states[$field->value] = $field->text;
}

$q = "SELECT c.id, c.title, jr.* FROM `#__jreviews_content` jr ".
     "LEFT JOIN `#__content` c ON c.id = jr.contentid ".
     "WHERE c.catid = 15";

$db->setQuery($q);
$hatches = $db->loadObjectList();

foreach ($hatches as $hatch) {
	echo $hatch->title;
	
	$statename = str_replace('*', '', $hatch->jr_state);
	if (isset($states[$statename])) {
		$statename = $states[$statename];
	}
	
	$newtitle = $hatch->title.' - '.$statename;
	echo " => ".$newtitle;
	echo "<br/>\n";
	
	$sql = "UPDATE #__content SET title='$newtitle' WHERE id = ".$hatch->id;
	echo $sql."<br/>\n";
	
	$db->setQuery($sql);
	$db->query();
}
