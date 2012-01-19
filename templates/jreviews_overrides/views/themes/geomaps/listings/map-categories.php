<?php

?>
<div class="map-categories">
    <ul>
<?php // if business, show business listing types ?>
<?php if ( $listing["Section"]["section_id"] == 3 ) : ?>
			<?php /*
			<li><label for="jr_lake"><input type="checkbox" id="jr_lake" name="toggle_cat[]" class="toggle_cat" value="38" /><?php __t("Lakes"); ?></label></li>
	        <li><label for="jr_river"><input type="checkbox" id="jr_river" name="toggle_cat[]" class="toggle_cat" value="39" /><?php __t("Rivers"); ?></label></li>
	        <li><label for="jr_fjord"><input type="checkbox" id="jr_fjord" name="toggle_cat[]" class="toggle_cat" value="42" /><?php __t("Fjords"); ?></label></li>
	        <li><label for="jr_1"><input type="checkbox" id="jr_1" name="toggle_cat[]" class="toggle_cat" value="20" /><?php __t("Fishing Spots"); ?></label></li>
			<li><label for="jr_5"><input type="checkbox" id="jr_5" name="toggle_cat[]" class="toggle_cat" value="24" /><?php __t("Catch Reports"); ?></label></li>
	        <li><label for="jr_6"><input type="checkbox" id="jr_6" name="toggle_cat[]" class="toggle_cat" value="26" /><?php __t("Trip Reports"); ?></label></li>
			<li><label for="jr_7"><input type="checkbox" id="jr_7" name="toggle_cat[]" class="toggle_cat" value="25" /><?php __t("Hatch Reports"); ?></label></li>
			
			<li><label for="jr_2"><input type="checkbox" id="jr_2" name="toggle_cat[]" class="toggle_cat" value="27" checked="checked" /><?php __t("Accommodations"); ?></label></li>
	        <li><label for="jr_3"><input type="checkbox" id="jr_3" name="toggle_cat[]" class="toggle_cat" value="34" checked="checked" /><?php __t("Businesses"); ?></label></li>
	        */ ?>

<?php else : ?>
	        <li><label for="jr_lake"><input type="checkbox" id="jr_lake" name="toggle_cat[]" class="toggle_cat" value="38" checked="checked" /><?php __t("Lakes"); ?></label></li>
	        <li><label for="jr_river"><input type="checkbox" id="jr_river" name="toggle_cat[]" class="toggle_cat" value="39" checked="checked" /><?php __t("Rivers"); ?></label></li>
	        <li><label for="jr_fjord"><input type="checkbox" id="jr_fjord" name="toggle_cat[]" class="toggle_cat" value="42" checked="checked" /><?php __t("Fjords"); ?></label></li>
	        <li><label for="jr_1"><input type="checkbox" id="jr_1" name="toggle_cat[]" class="toggle_cat" value="20" checked /><?php __t("Fishing Spots"); ?></label></li>
	        <?php /* <li><label for="jr_2"><input type="checkbox" id="jr_2" name="toggle_cat[]" class="toggle_cat" value="27" /><?php __t("Accommodations"); ?></label></li> */ ?>
	        <?php /* <li><label for="jr_3"><input type="checkbox" id="jr_3" name="toggle_cat[]" class="toggle_cat" value="34" /><?php __t("Businesses"); ?></label></li> */ ?>
	        <?php /* <li><label for="jr_4"><input type="checkbox" id="jr_4" name="toggle_cat[]" class="toggle_cat" value="31" /><?php __t("Points of Interest"); ?></label></li> */ ?>
			<li><label for="jr_5"><input type="checkbox" id="jr_5" name="toggle_cat[]" class="toggle_cat" value="24" checked="checked" /><?php __t("Catch Reports"); ?></label></li>
	        <?php if ($isAdmin) : ?>
	        <li><label for="jr_6"><input type="checkbox" id="jr_6" name="toggle_cat[]" class="toggle_cat" value="26" checked="checked" /><?php __t("Trip Reports"); ?></label></li>
			<li><label for="jr_7"><input type="checkbox" id="jr_7" name="toggle_cat[]" class="toggle_cat" value="25" checked="checked" /><?php __t("Hatch Reports"); ?></label></li>
			<?php endif; ?>

<?php endif; ?>

    </ul>
        
</div>

<?php                            
$coords = array();

if (isset($jr_lat) && isset($jr_long)) {
	
	$coords = $CustomFields->field('jr_extracoords', $listing, false, false);
						
	if ($coords) {
		$coords = json_decode($coords);
	}
}
			
// if business, show business cats
if ( $listing["Section"]["section_id"] == 3 ) {
	$mapcats = "28,29,30,31,32,33,89,91";
	$mapcats = "";
	$this_listing = $listing['Listing']['listing_id'];
} 
else {
	$mapcats = "1,2,3,4,100,14,15,28,29,89,30,31,32,33,90,91,92";
	$this_listing = '';
}
			
$moduleParams = array(
	'module_id'=>'geomaps'.$listing['Listing']['listing_id'],
	'module'=>array(
	'tmpl_suffix'=>'',
	'moduleclass_sfx'=>'',
	'cache_map'=>'0',
	'mode'=>'2', 					//'0' Normal (Use filtering settings)|'1'>Geo Targeting (IP Address to Location)|'2'>Custom center &amp; zoom
	'radius'=>'30',					//Defines the radius for listings lookup around the center found via Geo Targeting or Custom Center modes. Will use Miles or Km based on the GeoMaps configuration. If left blank all listings in the database are added to the map which can affect site performance if there are thousands of markers.
	'zoom'=>'12',						//Default zoom level for Geo Targeting and Custom Center modes.  Zoom (0-20+)
	'custom_lat'=>$jr_lat,				//Used when Custom Center &amp; Zoom option selected.
	'custom_lon'=>$jr_long,				//Used when Custom Center &amp; Zoom option selected.
	'search_bar'=>'',				//Allows users to quickly find a location on the map
	'filter'=>'none',				//Shows only featured listings on the map - |none|featured|
	'custom_where'=>'',				//Custom WHERE' description='Custom WHERE for query. Example: (Field.jr_brand LIKE '%Agfa%' OR Field.jr_brand LIKE '%Canon%')
	'click2search_auto'=>'',		//|0=No|1=yes  Automatically filters results when in click2search pages using the value from the click2search field.   
	'cat_auto'=>'',					//Filters results for current category.
	'detail_view'=>'0',				//1'>Show listing marker only  0'>Show all category markers
	'dir'=>'',						//Comma separated list, no spaces. To filter by IDs only one of the id parameters needs to be filled in.
	'section'=>'',// implode(",", $location_sections),					//Comma separated list, no spaces. To filter by IDs only one of the id parameters needs to be filled in
	'category'=>$mapcats,					//Comma separated list, no spaces. To filter by IDs only one of the id parameters needs to be filled in
	'listing'=>$this_listing,					//Comma separated list, no spaces. To filter by IDs only one of the id parameters needs to be filled in
	'map_width'=>'100%',			//Remember to include px or %. For example 100% or 500px
	'map_height'=>'400px',			//Remember to include px or %. For example 100% or 500px
	'custom_fields'=>'jr_extracoords',			//Comma separated list, no spaces. To improve performance you need to specify exactly which fields you will be showing in the marker tooltips and modify the /geomaps/map_infowindow.thtml
	'limit_results'=>'',		
	'clustering'=>'1',				//0=no | 1=yes  Groups markers at high zoom levels. Must be used when showing a large number of markers for performance gains.
	'clustering_min_markers'=>'10',	//When clustering is enabled, any number of markers above this setting will trigger the marker clustering functionality.
	'ui_maptype'=>'2',				// 2='Global'|buttons='Buttons'|menu='Menu'|none='None'
	'ui_map'=>'2',					//2='Global|0='No'|1='yes'
	'ui_hybrid'=>'2',				//2|0|1
	'ui_satellite'=>'2',			//2|0|1
	'ui_terrain'=>'2',				//2|0|1
	'ui_maptype_def'=>'G_NORMAL_MAP',			//2='Global'|G_NORMAL_MAP'|G_SATELLITE_MAP|G_HYBRID_MAP|G_PHYSICAL_MAP
	'ui_panzoom'=>'2',				//2|0|1
	'ui_scale'=>'2',				//2|0|1
	'ui_scrollwheel'=>'1',			//2|0|1
	'ui_doubleclick'=>'2',			//2|0|1
	'ui_trimtitle_module'=>'2',		//0|1
	'ui_trimtitle_chars'=>'',		//
	'extracoords' => $coords				// Added for Hooked
)
); 
# echo $this->renderControllerView('geomaps','map_detail',array('width'=>'100%','height'=>'300'));
echo '<div class="map-box">';
	echo $this->requestAction('module_geomaps/listings/',$moduleParams);		
echo '</div>';
?>