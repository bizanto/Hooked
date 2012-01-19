<?php
define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);
require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');
$mainframe &= JFactory::getApplication('site');

$req_limit = 10000;

// get listings which don't currently have a state/zip
$db =& JFactory::getDBO();
$sql = "SELECT jr.*, c.title FROM #__jreviews_content jr ".
       "LEFT JOIN #__content c ON c.id = jr.contentid ".
       "WHERE (jr_zip = '' AND jr_state = '') AND ".
       "(jr_lat IS NOT NULL AND jr_long IS NOT NULL) LIMIT $req_limit";
$db->setQuery($sql);
$listings = $db->loadObjectList();

$count = 0;

// reverse geocode up to limit
foreach ($listings as $listing) {
	$geodata = reverse_geocode($listing->jr_lat, $listing->jr_long);

	// format state name like a jreviews field option 
	if ($geodata['state'] != '') {
		$geodata['state'] = '*'.str_replace(' ', '-', strtolower($geodata['state'])).'*';
	}

	// store results back in db
	$sql = "UPDATE #__jreviews_content SET jr_zip = '".$geodata['zip']."', jr_state = '".$geodata['state']."' ".
	       "WHERE contentid = ".$listing->contentid;
	$db->setQuery($sql);
	$db->query();

	echo $listing->title." (".$listing->jr_lat.", ".$listing->jr_long."): ".$geodata['state'].", ".$geodata['zip']."\n";
	$count++; 
}

echo "$count rows updated\n";

function reverse_geocode($jr_lat, $jr_long) {
	$geocode_url = 'http://where.yahooapis.com/geocode?q=%s,%s&gflags=R&flags=J';
	$geocode_url = sprintf($geocode_url, $jr_lat, $jr_long);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $geocode_url);

	$geo_data = curl_exec($ch);

	curl_close($ch);
	$geo_data = json_decode($geo_data);
	
	$spot_state = $geo_data->ResultSet->Results[0]->county;
	$postalcode = $geo_data->ResultSet->Results[0]->uzip;

	return array("state" => ($spot_state) ? $spot_state : '', "zip" => ($postalcode) ? $postalcode : '');
}

