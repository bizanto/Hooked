<?php
define(LF, "<br />\n");

$km_limit = '100';
$limit = '3';

$jr_lat = '63.308';
$jr_long = '10.4507';

$url = "http://mobil.inatur.no/api/hooked.php?lng=%s&lat=%s&km_limit=%s&limit=%s";

$url = sprintf($url, $jr_long, $jr_lat, $km_limit, $limit);

echo "<fieldset><legend>API url</legend>".$url."</fieldset>".LF.LF;

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);

$points = curl_exec($ch);

echo "<fieldset><legend>API Response</legend>";
var_dump($points); echo "</fieldset>".LF.LF;

curl_close($ch);


$points = json_decode($points);
// echo "<fieldset><legend>PHP Object</legend>";
var_dump($points);
// echo "</fieldset>".LF.LF;

/*
echo "<fieldset><legend>Closest Infopage</legend>";
$firstkey = '';
foreach ($points as $key => $val) {
	$firstkey = $key; break;
}
echo $points->$firstkey->url;
echo "</fieldset>".LF.LF;
*/

?>