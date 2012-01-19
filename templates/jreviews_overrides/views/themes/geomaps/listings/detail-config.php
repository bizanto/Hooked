<?php

$this_cat = $listing["Category"]["cat_id"];
$this_sec = $listing["Section"]["section_id"];

$maxdesc = 120;

$jr_price = $CustomFields->field('jr_price', $listing, false, false);
$jr_startdate = $CustomFields->field('jr_startdate', $listing, false, false);
$jr_enddate = $CustomFields->field('jr_enddate', $listing, false, false);
$jr_time = $CustomFields->field('jr_time', $listing, false, false);

//get lat & long
$jr_lat = $CustomFields->field('jr_lat', $listing, false, false); 
$jr_long = $CustomFields->field('jr_long', $listing, false, false);

//define section ids which are locations (for map & weather tab)
$location_sections = array(1,2,3,6);
if (in_array($this_sec, $location_sections))
	$is_location =1;
else
	$is_location="";

// Relationships
$hasSpots = array(1,2,100,13,14,15,17,18,24,48,79);
if (in_array($this_cat, $hasSpots)) {
	$hasSpots = 1;
	// set the spot type depending if the 
	if ($this_cat==1||$this_cat==2) {
		$spotType = 4;
		$spots_type = 4;
	}
	elseif ($this_cat==100) {
		$spotType = 3;
		$spots_type = 3;
	}
	else {
		$spotType = "1,2,3,4,100"; // list of possible child spot types (categories)
		$spots_type = "s1"; // which type of spots to Add to catch reports, fish, techniques, etc.
	}
}
else
	$hasSpots = '';

$hasCatches = array(1,2,3,4,100,13,17,101,102,55,56,57);
if (in_array($this_cat, $hasCatches))
	$hasCatches = 1;
else
	$hasCatches = '';

$hasTrips = array(1,2,3,4,100,14,15);
if (in_array($this_cat, $hasTrips))
	$hasTrips = ''; // $hasTrips = 1; // disabled
else
	$hasTrips = '';

$hasHatches = array(1,2,3,4,100,13,118,119,120,121,122,123/*18*/);
if (in_array($this_cat, $hasHatches))
	$hasHatches = ''; //disabled
	
else
	$hasHatches = '';

// Everything has photos and videos
$hasPhotos = 1;
$hasVideos = 1;

$hasTechniques = array(1,2,3,4,100);
if (in_array($this_cat, $hasTechniques)) {
	$hasTechniques = 1;
	$editTechniques = 1;
	
	if ($this_cat==1 || $this_cat==2 || $this_cat==4)
		$technique_type = '24,79';
	elseif ($this_cat==100 || $this_cat==3)
		$technique_type = '48,79';
	else
		$technique_type = '24,48,79';
}

else
	$hasTechniques = '';

$hasFish = array(1,2,3,4,100,14);
if (in_array($this_cat, $hasFish)) {
	$hasFish = 1;
	if ($this_cat==14 && !$isOwner)
		$editFish = '';
	else
		$editFish = 1;
		
}
else
	$hasFish = '';
	
	
$hasInsects = array(15,23);
if (in_array($this_sec, $hasInsects)) {
	$hasInsects = 1;
	if ($this_cat==15 && !$isOwner)
		$editInsect = '';
	else
		$editInsect = 1;
	
	$insect_type = '118,119,120,121,122,123';
}
else
	$hasInsects = '';

// bait
$hasBait = 14;
if ($this_cat == 14) {
	$hasBait = 1;
	$bait_type = array(101,102);
}
else {
	$hasBait = '';
	$bait_type = '';
}


// set custom field variables

if ($this_sec=="1") { 
	//contact
    $jr_address      = $CustomFields->field('jr_address', $listing, false, false);
    $jr_adresstwo    = $CustomFields->field('jr_addresstwo', $listing, false, false);
    $jr_city         = $CustomFields->field('jr_city', $listing, false, false);
    $jr_zip          = $CustomFields->field('jr_zip', $listing, false, false);
    $jr_state        = $CustomFields->field('jr_state', $listing, false, false);
    $jr_country      = $CustomFields->field('jr_country', $listing, false, false);
    $jr_licenseprice = $CustomFields->field('jr_licenseprice', $listing, false, false);			
    
    $jr_size      = $CustomFields->field('jr_size', $listing, false, false);
    // fishing spot
    $jr_fspottags = $CustomFields->field('jr_fspottags', $listing, false, false);
    $jr_elevation = $CustomFields->field('jr_elevation', $listing, false, false);
    $jr_area      = $CustomFields->field('jr_area', $listing, false, false);
    
    //contact
    $jr_ctname  = $CustomFields->field('jr_ctname', $listing, false, false);
    $jr_ctphone = $CustomFields->field('jr_ctphone', $listing, false, false);
    $jr_ctemail = $CustomFields->field('jr_ctemail', $listing, false, false);
    $jr_ctlink  = $CustomFields->field('jr_ctlink', $listing, false, false);
    
}
elseif ($this_cat =="17") {
	// fish
    $jr_fishlatin   = $CustomFields->field('jr_fishlatin', $listing, false, false);
    $jr_fishclass   = $CustomFields->field('jr_fishclass', $listing, false, false);
    $jr_fishorder   = $CustomFields->field('jr_fishorder', $listing, false, false);
    $jr_fishfamily  = $CustomFields->field('jr_fishfamily', $listing, false, false);
    $jr_fishspecies	= $CustomFields->field('jr_fishspecies', $listing, false, false);
    $jr_fishwater   = $CustomFields->field('jr_fishwater', $listing, false, false);
    $jr_fishseason  = $CustomFields->field('jr_fishseason', $listing, false, false);
    $jr_fishhabitat	= $CustomFields->field('jr_fishhabitat', $listing, false, false);
    $jr_fishfound   = $CustomFields->field('jr_fishfound', $listing, false, false);
}
elseif ($this_sec=="25") {
	// insects
    $jr_insectfamily = $CustomFields->field('jr_insectfamily', $listing, false, false);
    $jr_insectwater  = $CustomFields->field('jr_insectwater', $listing, false, false);
    $jr_insectfamily = $CustomFields->field('jr_insectfamily', $listing, false, false);
    $jr_insectlength = $CustomFields->field('jr_insectlength', $listing, false, false);
    $jr_insectperiod = $CustomFields->field('jr_insectperiod', $listing, false, false);
    $jr_insecttail   = $CustomFields->field('jr_insecttail', $listing, false, false);
    $jr_insectstate  = $CustomFields->field('jr_insectstate', $listing, false, false);
}
elseif ($this_cat==13) {
	$jr_catchsummary = $CustomFields->field('jr_catchsummary', $listing, false, false);	
}
elseif ($this_cat==14) {
    $jr_catchanonymous = $CustomFields->fieldValue('jr_catchanonymous', $listing, false, false);
    $jr_catchanonymous = $jr_catchanonymous[0];
    if ($jr_catchanonymous=="ja")
    	$anon_location = 1;
    else
    	$anon_location = "";
    
   	$jr_catchweight   = $CustomFields->field('jr_catchweight', $listing, false, false);
   	$jr_catchlength   = $CustomFields->field('jr_catchlength', $listing, false, false);
   	$jr_catchreleased = $CustomFields->field('jr_catchreleased', $listing, false, false);
   	$jr_catchlice     = $CustomFields->field('jr_catchlice', $listing, false, false);
   	$jr_catchgender   = $CustomFields->field('jr_catchgender', $listing, false, false);
   	$jr_catchfarmed   = $CustomFields->field('jr_catchfarmed', $listing, false, false);

}
elseif ($this_cat==15) {
	$jr_hatchdegree = $CustomFields->field('jr_hatchdegree', $listing, false, false);
	$jr_weatherinfo = $CustomFields->field('jr_weatherinfo', $listing, false, false); 
}
				

?>