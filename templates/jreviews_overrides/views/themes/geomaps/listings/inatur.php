<?php
define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);
require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');
$mainframe &= JFactory::getApplication('site');

define(LF, "<br />\n");

$lat = $jr_lat;
$lng = $jr_long;
$km_limit = '100';
$limit = '3';

$url = "http://mobil.inatur.no/api/hooked.php?lng=%s&lat=%s&km_limit=%s&limit=%s";

$url = sprintf($url, $lng, $lat, $km_limit, $limit);

// echo "<fieldset><legend>API url</legend>".$url."</fieldset>".LF.LF;

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);

$points = curl_exec($ch);
//echo "<fieldset><legend>API Response</legend>";
// var_dump($points); echo "</fieldset>".LF.LF;

curl_close($ch);

$points = json_decode($points);
//echo "<fieldset><legend>PHP Object</legend>";
?>
	<div class="license-container paper">
    	<h3><?php __t("License Info"); ?></h3>
<?php
foreach ($points as $point) : ?>
		<div class="license-item">
            <h4><?php echo $point->name; ?></h4>
            <strong><span class="smalltext"><?php if ($point->municipal) echo $point->municipal; if ($point->county && ($point->municipal != $point->county)) { if ($point->municipal) echo ', '; echo $point->county; } ?></span></strong>
            <div class="buy-license">
                <a target="_blank" href="<?php echo $point->url; ?>?ref=hooked.no"><?php __t("PURCHASE"); ?></a>
            </div>
        </div>	
<?php endforeach; ?>
<?php if (!$point): ?>
	<p><?php __t("No license information available."); ?></p>
<?php endif; ?>

		<div>
        	{loadposition inatur}
        </div>
        
        
    </div>
<?php 
// var_dump($points);
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