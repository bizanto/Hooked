<?php
if ($_REQUEST['cron_pass'] != 'hektargc11') exit();
define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);
require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');
$mainframe &= JFactory::getApplication('site');

$req_limit = 2000;

// get listings which don't currently have a state/zip
// order by catid so catches/spots get processed before lakes & rivers
$db =& JFactory::getDBO();
$sql = "SELECT jr.*, c.title FROM #__jreviews_content jr ".
       "LEFT JOIN #__content c ON c.id = jr.contentid ".
       "WHERE (jr_zip = '' OR jr_state = '') ".
       "AND (jr_lat IS NOT NULL AND jr_long IS NOT NULL) ".
       "ORDER BY c.catid DESC LIMIT $req_limit";
$db->setQuery($sql);
$listings = $db->loadObjectList();

$count = 0;
$oql_count = 0; // number of times a geocode request returns OVER_QUERY_LIMIT
$oql_max   = 10;

ob_start();

// reverse geocode up to limit
foreach ($listings as $listing) {
	$geodata = reverse_geocode($listing->jr_lat, $listing->jr_long);
	
	if ($geodata == 0) continue;
	if ($geodata == -1) {
		if ($oql_count > $oql_max){
			break;
		}
		else {
			usleep($oql_count * 1000000); 
			$oql_count++;
			continue;
		}
	}
	
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

$f = fopen('logs/geocode'.time().'.txt', 'w');
fwrite($f, ob_get_contents());
fclose($f);

ob_end_flush();

function reverse_geocode($jr_lat, $jr_long) {
	$geocode_url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=%s,%s&sensor=false';
	$geocode_url = sprintf($geocode_url, $jr_lat, $jr_long);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $geocode_url);

	$geo_data = curl_exec($ch);

	curl_close($ch);
	$geo_data = json_decode($geo_data);
	
	if ($geo_data->status != "OK") {
		var_dump($geo_data);
		if ($geo_data->status == "OVER_QUERY_LIMIT") {
			return -1;
		}
		else {
			return 0;
		} 
	}

	foreach ($geo_data->results as $result) {
		foreach ($result->address_components as $component) {
			if (!isset($postalcode) && $component->types[0] == "postal_code") {
				$postalcode = $component->long_name;
			}
			if (!isset($spot_state) && $component->types[0] == "administrative_area_level_1") {
				$spot_state = $component->long_name;
			}
			
		}
	}

	return array("state" => ($spot_state) ? $spot_state : '', "zip" => ($postalcode) ? $postalcode : '');
}

