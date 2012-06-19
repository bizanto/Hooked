<?php
defined('_JEXEC') or die('Restricted Access');

$user =& JFactory::getUser();
if ($user->guest) {
	echo JText::_("YOU MUST LOGIN FIRST");
	return; 
}

// include JomSocial core libraries
require_once( JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
$config 	= CFactory::getConfig();

$document =& JFactory::getDocument();

// JomSocial / cWindow includes
$document->addScript(JURI::base()."components/com_community/assets/window-1.0.js");
$document->addScript(JURI::base()."components/com_community/assets/joms.jquery.js");
$document->addScript(JURI::base()."components/com_community/assets/script-1.2.js");
$document->addScript(JURI::base()."components/com_community/assets/joms.ajax.js");
$document->addStyleSheet(JURI::base()."components/com_community/assets/window.css");

// include geomaps for map marker screen
//$document->addScript("templates/jreviews_overrides/views/js/geomaps.js");
$document->addStyleSheet('components/com_relate/assets/jquery-ui-1.8.11.custom.css');

// com_relate includes
$document->addStyleSheet('components/com_relate/assets/edit.css');
$document->addStyleSheet('components/com_relate/assets/create.css');
$document->addScript('components/com_relate/assets/relate.js');
$document->addScript('components/com_relate/assets/create.js');
?>

<script type="text/javascript">
jQuery(function ($) {
	// __ relate_step __
	// opens the popup
	$('.addmore').click(function () {
		var relLink = this.getAttribute('href');
		var listing_id = '', cat_id = '';

		if (matches = relLink.match(/id=(\d+)/)) listing_id = matches[1];
		if (matches = relLink.match(/cat=([\w\d,]+)/)) cat_id = matches[1];

		var ajaxCall = "jax.call('relate', 'relate,ajaxEditRelations', '" + listing_id + "', '" + cat_id + "');";	
		cWindowShow(ajaxCall, '<?php echo JText::_("User Contributions"); ?>', 630, 100);

		return false;
	});
	
	// __ upload_step __
	// ** media upload ** in: [catches] catch report names/# 
	//                  | out: urls/ids of uploaded media to be related to new content after creation
	joms.uploader.postUrl 		= '<?php echo JRoute::_('index.php?option=com_community&view=photos&task=jsonupload&no_html=1&tmpl=component&defaultphoto=DEFAULT_PHOTOS&nextupload=NXUP&albumid=' . $this->albumId );?>';
	joms.uploader.uploadText	= '<?php echo JText::_('CC PHOTO UPLOADING');?>';
	joms.uploader.originalPostUrl = joms.uploader.postUrl;
	$(document).ready(function () {
		$('#photoupload').hide();
		joms.uploader.addNewUpload();
	});
	
	// __ Multi-Step Create Form! __
	$('.next').click(function () {
		Stepper.next();
	});
	$('.back').click(function () {
		Stepper.prev();
	});

	
	// set up the steps
	$('.step-container').hide();

	Stepper.data.type = '<?php echo $this->listing_type; ?>';
	var tab_descs;

	location_step.type = Stepper.data.type;
	thankyou_step.base = '<?php echo JURI::base(); ?>';

<?php if ($this->listing_type == 'catch'): ?>
		$('#create_title').text('<?php echo JText::_('ADD A CATCH'); ?>');
		
		Stepper.steps = [location_step, catches_step, upload_step, thankyou_step];
		tab_descs = ["<?php echo JText::_('LOCATION TAB'); ?>", "<?php echo JText::_('SPECIES TAB'); ?>", "<?php echo JText::_('IMAGES TAB'); ?>", "<?php echo JText::_('CONFIRMATION TAB'); ?>"];
		
		location_step.message = '<?php echo JText::_('WHICH LOCATION CATCH'); ?>';
		location_step.nudge   = true;
		mapmarker_step.show_spots = false;

		thankyou_step.content_type = '<?php echo JText::_('CATCH REPORT'); ?>';
		
<?php elseif ($this->listing_type == 'spot'): ?>
		$('#create_title').text('<?php echo JText::_('ADD A SPOT'); ?>');
		
		Stepper.steps = [location_step, mapmarker_step, nameit_step, relate_step, thankyou_step];
		tab_descs = ["<?php echo JText::_('LOCATION TAB'); ?>", "<?php echo JText::_('MAP MARKER TAB'); ?>", "<?php echo JText::_('NAME DESC TAB'); ?>", "<?php echo JText::_('RELATED TAB'); ?>", "<?php echo JText::_('CONFIRMATION TAB'); ?>"];
		
		location_step.message = '<?php echo JText::_('WHICH LOCATION SPOT'); ?>';
		location_step.nudge   = false;
		$('.locationSelect').slice(1).remove();
		
		mapmarker_step.show_spots = true;
		
		relate_step.message = '<?php echo JText::_('RELATE STEP MSG SPOTS'); ?>';
	<?php if (isset($this->location)) { ?>
		relate_step.catid = '<?php echo $this->location->catid; ?>';
	<?php } ?>

		thankyou_step.content_type = '<?php echo JText::_('FISHING SPOT'); ?>';
		
<?php elseif ($this->listing_type == 'trip'): ?>
		$('#create_title').text('<?php echo JText::_('ADD A TRIP'); ?>');

		Stepper.steps = [nameit_step, location_step, upload_step, relate_step, thankyou_step];
		tab_descs = ["<?php echo JText::_('TITLE DESC TAB'); ?>","<?php echo JText::_('LOCATION TAB'); ?>", "<?php echo JText::_('IMAGES TAB'); ?>", "<?php echo JText::_('CATCH REPORTS'); ?>", "<?php echo JText::_('CONFIRMATION TAB'); ?>"];
		
		location_step.multiselect = true;
		location_step.nudge = false;
		location_step.message = '<?php echo JText::_('WHICH LOCATION TRIP'); ?>';
		
		nameit_step.message = '<?php echo JText::_('TRIP TITLE'); ?>';
		thankyou_step.content_type = '<?php echo JText::_('TRIP REPORT'); ?>';
		
<?php elseif ($this->listing_type == 'hatch'): ?>
		$('#create_title').text('<?php echo JText::_('ADD A HATCH'); ?>');

		Stepper.steps = [location_step, hatches_step, upload_step, thankyou_step];
		tab_descs = ["<?php echo JText::_('LOCATION TAB'); ?>", "<?php echo JText::_('HATCHES TAB'); ?>", "<?php echo JText::_('IMAGES TAB'); ?>", "<?php echo JText::_('CONFIRMATION TAB'); ?>"];
		
		location_step.message = '<?php echo JText::_('WHICH LOCATION HATCH'); ?>';
		location_step.nudge   = true;
		mapmarker_step.show_spots = false;
		
		relate_step.message = '<?php echo JText::_('RELATE STEP MSG TRIPS'); ?>';
		
		thankyou_step.content_type = '<?php echo JText::_('HATCH REPORT'); ?>';

<?php elseif ($this->listing_type == 'lake'): ?>
		$('#create_title').text('<?php echo JText::_('ADD A LAKE'); ?>');

		Stepper.steps = [mapmarker_step, nameit_step, relate_step, upload_step, thankyou_step];
		tab_descs = ["<?php echo JText::_('MAP MARKER TAB'); ?>", "<?php echo JText::_('NAME DESC TAB'); ?>", "<?php echo JText::_('RELATED TAB'); ?>", "<?php echo JText::_('IMAGES TAB'); ?>", "<?php echo JText::_('CONFIRMATION TAB'); ?>"];

		nameit_step.message = '<?php echo JText::_('ADD NAME AND DESC LAKE'); ?>';

		relate_step.message = '<?php echo JText::_('RELATE STEP MSG SPOTS'); ?>';
		
		thankyou_step.content_type = '<?php echo JText::_('LAKE TYPE'); ?>';
		
<?php endif; ?>

	$('.content-type').text(thankyou_step.content_type);
	
	$('#totalSteps').text(Stepper.steps.length);
	
	$.each(Stepper.steps, function (idx) {
		var el = $('<span class="tab-wrap"><em>' + tab_descs[idx] + '</em><br /><span id="progTab'+idx+'" class="prog-tab">' + (idx+1) + '</span></span>');
		$('#progress_tabs').append(el);
	});
	
	Stepper.next(); // let's get started.. 
});
</script>

<script type="text/javascript" src="<?php echo JURI::base(); ?>components/com_relate/assets/jquery-ui-1.8.11.custom.min.js"></script>

<?php if (!$this->isFbConnect): ?>
<div id="fb-root"></div>
<script type="text/javascript" src="http://connect.facebook.net/en_US/all.js#appId=<?php echo $config->get('fbconnectkey');?>&xfbml=1"></script>
<script type="text/javascript">
window.fbAsyncInit = function() {
	var $ = jQuery;
	
    FB.init({appId: '<?php echo $config->get('fbconnectkey');?>', status: false, cookie: true, xfbml: true, oauth: true});

	FB.Event.subscribe('auth.login', function (response) {
		$('input[name="fb_post"]').attr('checked', true).fadeIn();
		$('.fblogin').fadeOut();
	});
	$('input[name="fb_post"]').hide();
};
</script>
<?php endif; ?>

<div class="title-container">
<h1 id="create_title"> </h1>
</div>

<div class="progress">
<?php echo JText::_('PROGRESS STEP'); ?> <span id="curStep">1</span> <?php echo JText::_('of'); ?> <span id="totalSteps">4</span>
<div id="progress_tabs"></div>
<div class="clear"></div>
</div>

<div class="nav">
<a class="back" href="javascript:void(0);"><?php echo JText::_('back'); ?></a> <a class="next" href="javascript:void(0);"><?php echo JText::_('next'); ?></a>
</div>

<div id="step-error"></div>

<div id="location_search" class="step-container">
	<span class="step-msg"></span>
	<div class="locationSelect">
		<input id="locsearch" name="location" type="radio" checked /> <label for="locsearch"><?php echo JText::_('Search'); ?></label><br />
		<div id="locationSearch">
			<div id="locTypeahead"> 
				<input id="locName" size="45" type="text" value="<?php if (isset($this->location)) echo $this->location->title; else echo JText::_('ENTER LOCATION'); ?>" onfocus="if (this.value=='<?php echo JText::_('ENTER LOCATION'); ?>') this.value='';" />
				<ul id="locationsList">
				</ul>
			</div>
			<span><?php echo JText::_('LIMIT TO STATE'); ?></span>
			<?php echo $this->lists['states']; ?>
		</div>
		<div class="clear"></div>
	</div>
	<?php if ($this->listing_type == "trip"): ?>
	<div class="trip-locations rel-items"></div>
	<?php else: ?>
	<div class="locationSelect">
		<input id="favorites" name="location" type="radio" /> <label for="favorites"><?php echo JText::_('PICK FAVORITES'); ?></label><br />
		<div id="locFavs" style="display:none"><?php echo $this->lists['favorites']; ?></div>
	</div>
	<?php endif; ?>
	<?php if ($this->listing_type != "hatch"): ?>
	<div class="locationSelect">
		<input id="nodisclose" name="location" type="radio" /> <label for="nodisclose"><?php echo JText::_('NO DISCLOSE SPOT'); ?></label><br />
	</div>
	<?php endif; ?>
	<input type="hidden" id="location_id" value="<?php if (isset($this->location)) echo $this->location->id; ?>" />
	<div id="nudge" class="">
	<?php echo JText::_('NUDGE LOCATION'); ?>
	</div>
</div>

<script type="text/javascript">
function saveDescription() {
<?php
	$editor =& JFactory::getEditor();
	echo $editor->save( 'description' );
?>
}
</script>

<?php if ($this->listing_type == "trip" || $this->listing_type == "spot" || $this->listing_type == "lake"): ?>
<div id="name_it" class="step-container">
	<span class="step-msg"><?php echo JText::_('ADD NAME AND DESC'); ?></span>
	<div>
		<span class="reqField">*</span><label><?php if ($this->listing_type == "trip") echo JText::_('TITLE'); else echo JText::_('NAME'); ?></label><br />
		<input id="title" type="text" name="title" />
	</div>
	<?php if ($this->listing_type == "trip"): ?>
		<div class="cal-div">
		<label><span class="reqField">*</span><?php echo JText::_('FROM'); ?></label>
		<input id="trip_from" class="validate" type="text" />
		<input type="hidden" id="jr_startdate" name="jr_startdate" />
		</div>
		<div class="cal-div">
		<label><?php echo JText::_('TO'); ?></label>
		<input id="trip_to" type="text" />
		<input type="hidden" id="jr_enddate" name="jr_enddate" />
		</div>
		<div class="clear"></div>
	<?php endif; ?>
	<div>
		<label><?php echo JText::_('DESCRIPTION'); ?></label><br />
		<?php
			$editor =& JFactory::getEditor();
			echo $editor->display('description', '', '550', '400', '60', '20', false);
		?>
	</div>
	<?php if ($this->listing_type == "trip"): ?>
		<span class="fieldRow prv">
		<h3><?php echo JText::_('PRIVACY'); ?></h3>
		<?php foreach ($this->privacySettings as $pset): ?>
		<label for="<?php echo $pset->value; ?>0">
	        <input id="<?php echo $pset->value; ?>0" class="fields[jr_privacy]" name="fields[jr_privacy]" value="<?php echo '*'.$pset->value.'*'; ?>" type="radio" <?php if ($pset->value == "offentlig") echo 'checked="checked"'; ?> />
        	<?php echo $pset->text; ?>
        </label>
		<?php endforeach; ?>
		</span>
	<?php endif; ?>
	<div id="other_fields">
	<?php if ($this->listing_type == "spot" || $this->listing_type == "lake"): ?>
		<label><?php echo JText::_('OTHER FIELDS'); ?></label><br />
		<div class="spot-tags">
		<?php foreach ($this->spotTags as $stag): ?>
		<input id="<?php echo $stag->value; ?>" name="<?php echo $stag->value; ?>" type="checkbox" /><label for="<?php echo $stag->value; ?>"><?php echo $stag->text; ?></label>
		<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php if ($this->listing_type == "spot"): ?>
		<div class="privacy-settings">
		<label><?php echo JText::_('PRIVACY SETTINGS'); ?>:</label><br />
		<?php foreach ($this->privacySettings as $pset): ?>
		<input id="<?php echo $pset->value; ?>" class="jr_privacy" name="jr_privacy" value="<?php echo '*'.$pset->value.'*'; ?>" type="radio" <?php if ($pset->value == "offentlig") echo 'checked="checked"'; ?> /><label for="<?php echo $pset->value; ?>"><?php echo $pset->text; ?></label>
		<?php endforeach; ?>
		</div>
	<?php elseif ($this->listing_type == "lake"): ?>
		<div class="privacy-settings" style="display:none">
			<input type="radio" checked="checked" value="*offentlig*" name="jr_privacy" class="jr_privacy" id="offentlig" />
		</div>
	<?php endif; ?>
	
		<div class="">
		<label for="fb_post">
		<span class="fbook">&nbsp;<?php echo JText::_('POST TO FACEBOOK'); ?> 
		<input id="fb_post" name="fb_post" type="checkbox" />
		</span>
		<?php if (!$this->isFbConnect): ?>
		<fb:login-button class="fblogin" perms="publish_stream"></fb:login-button>
		<?php endif; ?>
		</label>
		</div>

	<?php if ($this->listing_type == "lake"): ?>
		<div>
			<label for="jr_size">Areal (km<sup>2</sup>)</label><br />
			<input type="text" class="mediumField" id="jr_size" name="fields[jr_size]">
		</div>
		<div>
			<label for="jr_area">Område</label><br />
			<input type="text" class="mediumField" id="jr_area" name="fields[jr_area]">
		</div>
		<div>
			<label for="jr_elevation">Meter over havet</label><br />
			<input type="text" class="mediumField" id="jr_elevation" name="fields[jr_elevation]">
		</div>
	<?php endif; ?>

	</div>
</div>
<?php endif; ?>

<script type="text/javascript">
var GeomapsGoogleApi = "http://maps.google.no/maps?file=api&v=2&async=2&key=<?php echo $this->gapi_key; ?>&sensor=false";
var jr_lat = "jr_lat"; var jr_lon = "jr_long";
</script>
<script type="text/javascript" src="<?php echo JURI::base(); ?>templates/jreviews_overrides/views/js/geomaps.js"></script>
<div id="map_marker" class="step-container">
	<span class="step-msg"><?php echo JText::_('DRAG MARKER'); ?></span>
	<h2 id="spot_title"></h2>
	<div id="gm_mapPopupCanvas">
	</div>
	<?php if ($this->listing_type == 'spot'): ?>
	<div class="marker-desc">
	<span class="marker"></span><div class="fl"> &mdash; <?php echo JText::_('MARKER NEW SPOT'); ?> </div><span class="marker-red"></span><div class="fl"> &mdash; <?php echo JText::_('MARKER EXISTING SPOTS'); ?> </div>
	<div class="clear"></div>
	</div>
	<?php endif; ?>
	<div>
		<h2><?php echo JText::_('COORDINATES'); ?></h2>
		<input id="jr_lat" type="text" name="fields[jr_lat]" /> <input id="jr_long" type="text" name="fields[jr_long]" />
	</div>
</div>

<?php if ($this->listing_type == "catch"): ?>
<div id="add_catches" class="step-container">
	<span class="step-msg"><?php echo JText::_('ADD CATCHES MSG'); ?></span>
	<div id="catches">
		<div class="catch-0">
		<span class="fieldRow fl width49 r1">
			 <div class="clear">
                 <label for="startdate"><?php echo JText::_('DATE'); ?>:<span class="reqField">*</span></label>
                 <input class="startdate" name="startdate" type="text" size="12" id="startdate" /><input id="jr_date" type="hidden" name="fields[jr_startdate]" size="12" class="validate" />
             </div>
             <div class="clear">
			 <label for="jr_time"><?php echo JText::_('TIME'); ?> (hh:mm):</label>
			 <input id="jr_time" type="text" name="fields[jr_time]" size="12" />
             </div>
		</span>
		
		<span class="fieldRow fr width49 r1">
        	<div class="clear">
                <label for="relatedspecies"><span class="reqField">*</span><?php echo JText::_('FISH SPECIES'); ?></label>
                <?php echo $this->lists['species']; ?>
			</div>
            <div class="clear">
                <label for="relatedbait"><?php echo JText::_('BAIT'); ?></label>
                <?php echo $this->lists['bait']; ?>
            </div>
		</span>
		<div class="clear"></div>
		<span class="fieldRow r2">
            <div class="fl width33">
                <label for="jr_catchweight"><span class="reqField">*</span><?php echo JText::_('WEIGHT'); ?> (gr)</label>
                <input id="jr_catchweight" type="text" name="fields[jr_catchweight]" size="4" class="validate" />
            </div>
            <div class="fl width33">	
				<label for="jr_catchlength"><?php echo JText::_('LENGTH'); ?> (cm)</label>
                <input id="jr_catchlength" type="text" name="fields[jr_catchlength]" size="4" />
            </div>			
            <div class="fl width33">	
                <label class="full" for="jr_catchreleased"><?php echo JText::_('CATCH RELEASED'); ?> <input id="jr_catchreleased" type="checkbox" name="fields[jr_catchreleased]" class="checkbox" /> </label>
			</div>
		</span>
		<div class="clear"></div>
		<span class="fieldRow r3">
		<label for="desc"><?php echo JText::_('DESCRIPTION'); ?>:</label> 
		<?php
			$editor =& JFactory::getEditor();
			echo $editor->display('description', '', '550', '400', '60', '20', false);
		?>
		</span>
		<div class="clear"></div>
		
		<span class="fieldRow fbc">
		<label for="fb_post0">
		<span class="fbook">&nbsp;<?php echo JText::_('POST TO FACEBOOK'); ?> 
		<input id="fb_post0" name="fb_post" type="checkbox" /></span>
		</label>
		<?php if (!$this->isFbConnect): ?>
		<fb:login-button class="fblogin" perms="publish_stream"></fb:login-button>
		<?php endif; ?>
		</span>
		<div class="clear"></div>
		
		
		<span class="fieldRow prv">
		<h3><?php echo JText::_('PRIVACY'); ?></h3>
		<?php foreach ($this->privacySettings as $pset): ?>
		<label for="<?php echo $pset->value; ?>0">
	        <input id="<?php echo $pset->value; ?>0" class="fields[jr_privacy]" name="fields[jr_privacy]" value="<?php echo '*'.$pset->value.'*'; ?>" type="radio" <?php if ($pset->value == "offentlig") echo 'checked="checked"'; ?> />
        	<?php echo $pset->text; ?>
        </label>
		<?php endforeach; ?>
		</span>
		<div class="clear"></div>		
		<span class="fieldRow">
			<label for="jr_catchanonymous"><?php echo JText::_('ANONYMIZE LOCATION'); ?></label>
			<input id="jr_catchanonymous" type="checkbox" name="fields[jr_catchanonymous]" /> 
		</span>
		<div class="clear"></div>
		</div>
	</div> 
</div>
<?php endif; ?>

<?php if ($this->listing_type == "hatch"): ?>
<div id="add_hatches" class="step-container">
	<span class="step-msg"><?php echo JText::_('HATCH INFO'); ?></span>
	<span class="fieldRow fl width49 r1">
		 <div class="clear">
             <label for="hatchdate"><?php echo JText::_('DATE'); ?>:<span class="reqField">*</span></label>
             <input class="hatchdate" name="hatchdate" type="text" size="12" id="hatchdate" /><input id="jr_hatchdate" type="hidden" name="fields[jr_startdate]" size="12" class="validate" />
         </div>
         <div class="clear">
		 <label for="jr_hatchtime"><?php echo JText::_('TIME'); ?> <?php echo JText::_('FROM'); ?> (hh:mm):<span class="reqField">*</span></label>
		 <input id="jr_hatchtime" type="text" name="fields[jr_time]" size="12" />
         </div>
         <div class="clear">
		 <label for="jr_hatch_endtime"><?php echo JText::_('TIME'); ?> <?php echo JText::_('TO'); ?> (hh:mm):</label>
		 <input id="jr_hatch_endtime" type="text" name="fields[jr_endtime]" size="12" />
         </div>
	</span>
	<span class="fieldRow fr width49 r1">
		<div class="clear">
	        <label for="relatedinsects"><span class="reqField">*</span><?php echo JText::_('INSECT'); ?>:</label>
	        <?php echo $this->lists['insects']; ?>
	        <div class="insect-support"><?php echo JText::_('INSECT SUPPORT TEXT'); ?></div>
		</div>
		<div class="clear">
			<label for="fieldsjr_hatchdegree"><?php echo JText::_('Omfang'); ?>:</label>
			<?php echo $this->lists['hatchdegree']; ?>
		</div>
		<div class="clear">
			<label for="fieldsjr_weatherinfo"><?php echo JText::_('Weather'); ?>:</label>
			<?php echo $this->lists['hatchweather']; ?>
		</div>
	</span>
	<div class="clear"></div>
	<span class="fieldRow r3">
		<label for="desc"><?php echo JText::_('DESCRIPTION'); ?>:</label> 
		<?php
			$editor =& JFactory::getEditor();
			echo $editor->display('description', '', '550', '400', '60', '20', false);
		?>
	</span>
	<div class="clear"></div>

	<span class="fieldRow fbc">
	<label for="fb_post0">
	<span class="fbook">&nbsp;<?php echo JText::_('POST TO FACEBOOK'); ?> 
	<input id="fb_post0" name="fb_post" type="checkbox" /></span>
	</label>
	<?php if (!$this->isFbConnect): ?>
	<fb:login-button class="fblogin" perms="publish_stream"></fb:login-button>
	<?php endif; ?>
	</span>
	<div class="clear"></div>

	<span class="fieldRow prv">
	<h3><?php echo JText::_('PRIVACY'); ?></h3>
	<?php foreach ($this->privacySettings as $pset): ?>
	<label for="<?php echo $pset->value; ?>0">
		<?php echo $pset->text; ?>
        <input id="<?php echo $pset->value; ?>0" class="fields[jr_privacy]" name="fields[jr_privacy]" value="<?php echo '*'.$pset->value.'*'; ?>" type="radio" <?php if ($pset->value == "offentlig") echo 'checked="checked"'; ?> />
    </label>
	<?php endforeach; ?>
	</span>
	<div class="clear"></div>
</div>
<?php endif; ?>

<div id="add_existing" class="step-container">
	<span class="step-msg"><?php echo JText::_('RELATE STEP MSG TRIPS'); ?></span>
<?php if ($this->listing_type == "trip"): ?>
	<div class="add-category">
		<span class="cat-title"><?php echo JText::_('CATCH REPORTS'); ?></span>
		<div id="related-items14" class="rel-items"></div>
		<a class="addmore" href="index.php?option=com_relate&cat=14"><img src="/images/bg-rel8-plus.png" /><?php echo JText::_('ADD MORE'); ?></a>
	</div>
	<div class="add-category">
		<span class="cat-title"><?php echo JText::_('CATCH SUMMARY'); ?></span>
		<div class="catch-summary">
		<textarea id="jr_catchsummary" name="jr_catchsummary"></textarea>
		</div>
	</div>
<?php elseif ($this->listing_type == "spot" || $this->listing_type == "lake"): ?>
	<div class="add-category f-species">
		<span class="cat-title"><?php echo JText::_('FISH SPECIES'); ?></span>
		<div id="related-items17" class="rel-items"></div>
		<a class="addmore" href="index.php?option=com_relate&cat=17"><img src="/images/bg-rel8-plus.png" /><?php echo JText::_('ADD MORE'); ?></a>
	</div>
	<div class="add-category f-techniques">
		<span class="cat-title"><?php echo JText::_('FISHING TECHNIQUES'); ?></span>
		<div id="related-items2479" class="rel-items"></div> <!-- 1,2,4 -> 24,79 | 3, 100 -> 48,79 -->
		<a class="addmore" href="index.php?option=com_relate&cat=24,79"><img src="/images/bg-rel8-plus.png" /><?php echo JText::_('ADD MORE'); ?></a>
	</div>
<?php endif; ?>
</div>

<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_community/assets/ajaxfileupload.js"></script>
<div id="media_upload" class="step-container">
	<span class="step-msg"><?php echo JText::_('MEDIA STEP MSG'); ?></span>
	<div id="community-photo-items" class="photo-list-item" style="display:none">
		<div class="container"></div>
	</div>
	<div id="photoupload-container">
		<div id="photoupload" class="upload-form">
			<span class="upload-photo">&nbsp;</span>
			<a class="remove" href="javascript:void(0);"><img src="/images/bg-rel8-remove.png" alt="Remove" /></a>
			<input class="text input" type="file" size="20" name="Filedata" id="Filedata" />
			<input class="file-description" type="text" size="40" name="caption" />
			<input type="hidden" name="elementIndex" class="elementIndex" />
		</div>
	</div>
	<div class="add-uploads">
	<button class="add-img" onclick="joms.uploader.addNewUpload()"><?php echo JText::_('ADD IMAGE'); ?></button>
	<?php /* <!-- <button class="add-vid" onclick="joms.videos.addVideo()">Add Video</button>  --> */ ?>
	</div>
</div>

<div id="thank_you" class="step-container">
	<div class="loading"></div>
	<p style="display:none">
	<?php echo JText::_('CONGRATULATIONS ADDED'); ?> <span class="content-type"> </span>!
	</p>
	<div class="content-data">
	</div>
	<div id="create_results">
	</div>
	<button style="display:none" onclick="window.location.reload()"><?php echo JText::_('ADD ANOTHER'); ?> <span class="content-type"> </span></button>
</div>

<div class="nav">
<a class="back" href="javascript:void(0);"><?php echo JText::_('back'); ?></a> <a class="next" href="javascript:void(0);"><?php echo JText::_('next'); ?></a>
</div>
<div class="clear"></div>

