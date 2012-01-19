<?php
defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

$_addon = array(
    'name'=>'GeoMaps',
    'description'=>'Adds distance search and mapping functionality to JReviews',
    'version'=>'0.4.20',                    
    'min_app_version_required'=>'2.2.02.167',  
    'type'=>'Commercial'
);
 
echo json_encode($_addon);