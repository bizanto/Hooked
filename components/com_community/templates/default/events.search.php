<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @param	posted	boolean	Determines whether the current state is a posted event.
 * @param	search	string	The text that the user used to search 
 */
defined('_JEXEC') or die();
?>
<style type="text/css">
div#community-wrap .calendar{
	vertical-align: middle; 
	padding-left: 4px;
	padding-right:4px; 
	border: medium none;
}
</style>
<div id="community-events-wrap">

	<!--SEARCH FORM-->
	<div class="event-search-form">
		<form name="jsform-events-search" method="get" action="">
			
			<?php if(!empty($beforeFormDisplay)){ ?>
			<table class="formtable" cellspacing="1" cellpadding="0" style="width: 98%;">
				<?php echo $beforeFormDisplay; ?>
			</table>
			<?php } ?>
			
			<input type="text" class="inputbox" name="search" value="<?php echo $search; ?>" size="50" />
			<input type="hidden" value="com_community" name="option" />
			<input type="hidden" value="events" name="view" />
			<input type="hidden" value="search" name="task" />
			<input type="hidden" value="<?php echo CRoute::getItemId();?>" name="Itemid" />

			<?php if(!empty($afterFormDisplay)){ ?>
			<table class="formtable" cellspacing="1" cellpadding="0" style="width: 98%;">
				<?php echo $afterFormDisplay; ?>
			</table>
			<?php } ?>
			<?php echo JHTML::_( 'form.token' ); ?>
			<input type="submit" value="<?php echo JText::_('CC SEARCH BUTTON');?>" class="button" />
		
			<table class="formtable" cellspacing="1" cellpadding="0">
			    <!-- events category -->
			    <tr>
				<td class="key">
				    <label for="catid" class="label title jomTips" title="<?php echo JText::_('CC EVENTS CATEGORY');?>::<?php echo JText::_('CC EVENTS CATEGORY TIPS');?>">
					    <?php echo JText::_('CC EVENTS CATEGORY');?>
				    </label>
				</td>
				<td class="value">
				    <select name="catid" id="catid" class="required inputbox">
					    <option value="0" selected></option>
				    <?php
				    foreach( $categories as $category )
				    {
				    ?>
					    <option value="<?php echo $category->id; ?>" <?php if( $category->id == $catId ) { ?>selected<?php } ?>><?php echo JText::_( $this->escape($category->name) ); ?></option>
				    <?php
				    }
				    ?>
				    </select>
				</td>
			    </tr>
			    <!-- events start datetime -->
			    <tr>
				<td class="key">
					<label class="label title jomTips" title="<?php echo JText::_('CC EVENTS START TIME');?>::<?php echo JText::_('CC EVENTS START TIME TIPS'); ?>">
						<?php echo JText::_('CC EVENTS START DATE'); ?>
					</label>
				</td>
				<td class="value">
					<span>
						<?php echo JHTML::_('calendar',  $advance['startdate'] , 'startdate', 'startdate', '%Y-%m-%d', array('class'=>'required inputbox', 'size'=>'10',  'maxlength'=>'10' , 'readonly' => 'true') );?>
					</span>
				</td>
			    </tr>
			    <!-- events end datetime -->
			    <tr>
				<td class="key">
					<label class="label title jomTips" title="<?php echo JText::_('CC EVENTS END TIME');?>::<?php echo JText::_('CC EVENTS END TIME TIPS'); ?>">
						<?php echo JText::_('CC EVENTS END DATE'); ?>
					</label>
				</td>
				<td class="value">
					<span>
						<?php echo JHTML::_('calendar',  $advance['enddate'], 'enddate', 'enddate', '%Y-%m-%d', array('class'=>'required inputbox', 'size'=>'10',  'maxlength'=>'10' , 'readonly' => 'true') );?>
					</span>
				</td>
			    </tr>
			    <tr>
				<td class="key">
					<label class="label title jomTips" title="<?php echo JText::_('CC EVENTS FROM'); ?>::<?php echo JText::_('CC SEARCH EVENTS FROM TIPS'); ?>">
						<?php echo JText::_('CC EVENTS FROM'); ?>
					</label>
				</td>
				<td class="value">
					<script type="text/javascript">
						joms.jQuery('document').ready(function()
 						{
						    validateFormValue();

						    // Check if the browsers support W3C Geolocation API
						    // If yes, show the auto-detect link
						    if( navigator.geolocation )
						    {
							    joms.jQuery('#proto__detectButton').show();
						    }
						});

						function get_current_location()
						{
						    joms.jQuery('#proto__currentLocationValue').hide();
						    joms.jQuery('#proto__detectButton').hide();
						    joms.jQuery('#proto__detectingCurrentLocation').show();

						    navigator.geolocation.getCurrentPosition(function(location)
						    {
							var lat	=   location.coords.latitude;
							var lng	=   location.coords.longitude;

							// Reverse Geocoding
							geocoder    =   new google.maps.Geocoder();
							var latlng  =   new google.maps.LatLng( lat, lng );

							geocoder.geocode({'latLng': latlng}, function(results, status){
							    if( status == google.maps.GeocoderStatus.OK ){
								if ( results[4] ){
								    var newLocation = results[4].formatted_address;

								    if( newLocation.length != 0 )
								    {
									joms.jQuery("#proto_selectRadius").removeAttr("disabled");
								    }

								    joms.jQuery("#proto__detectingCurrentLocation").hide();
								    joms.jQuery("#proto__currentLocationValue").attr("value", newLocation).show();
								}
							    } else {
								alert("Geocoder failed due to: " + status);
							    }
							});
							
							joms.jQuery("#proto__detectButton").show();
						    });
						}

						function validateFormValue()
						{
						    var input = joms.jQuery("#proto__currentLocationValue").val();

						    if( input.length != 0 )
						    {
							joms.jQuery("#proto_selectRadius").removeAttr("disabled");
						    }
						    else
						    {
							joms.jQuery("#proto_selectRadius").attr("disabled", "disabled");
						    }
						}
					</script>

					<div id="proto__currentLocation">
						<input type="text" name="location" id="proto__currentLocationValue" value="<?php echo $advance['fromlocation']; ?>" class="inputbox" onkeyup="validateFormValue();" />
						<span id="proto__detectingCurrentLocation" class="loading" style="float: left;"></span>
						<a id="proto__detectButton" href="javascript: void(0)" style="display: none;" onclick="get_current_location();" title="<?php echo JText::_('CC EVENTS LOCATION AUTODETECT TIPS'); ?>"><?php echo JText::_('CC EVENTS LOCATION AUTODETECT'); ?></a>
					</div>
				</td>
			    </tr>
			    <tr>
				<td class="key">
					<label class="label title jomTips" title="<?php echo JText::_('CC EVENTS WITHIN');?>::<?php echo JText::_('CC SEARCH EVENTS WITHIN TIPS'); ?>">
						<?php echo JText::_('CC EVENTS WITHIN'); ?>
					</label>
				</td>
				<td class="value">
					<select id="proto_selectRadius" name="radius" class="required inputbox" disabled="disabled">
						<option value="<?php echo null; ?>" <?php if( empty($advance['radius']) ){ ?>selected<?php } ?>></option>
						<option value="<?php echo COMMUNITY_EVENT_WITHIN_5; ?>" <?php if( $advance['radius'] == COMMUNITY_EVENT_WITHIN_5 ){ ?>selected<?php } ?>><?php echo COMMUNITY_EVENT_WITHIN_5 . JText::_( 'CC MILES' ); ?></option>
						<option value="<?php echo COMMUNITY_EVENT_WITHIN_10; ?>" <?php if( $advance['radius'] == COMMUNITY_EVENT_WITHIN_10 ){ ?>selected<?php } ?>><?php echo COMMUNITY_EVENT_WITHIN_10 . JText::_( 'CC MILES' ); ?></option>
						<option value="<?php echo COMMUNITY_EVENT_WITHIN_20; ?>" <?php if( $advance['radius'] == COMMUNITY_EVENT_WITHIN_20 ){ ?>selected<?php } ?>><?php echo COMMUNITY_EVENT_WITHIN_20 . JText::_( 'CC MILES' ); ?></option>
						<option value="<?php echo COMMUNITY_EVENT_WITHIN_50; ?>" <?php if( $advance['radius'] == COMMUNITY_EVENT_WITHIN_50 ){ ?>selected<?php } ?>><?php echo COMMUNITY_EVENT_WITHIN_50 . JText::_( 'CC MILES' ); ?></option>
					</select>
				</td>
			    </tr>
			</table>

		</form>
	</div>
	<!--SEARCH FORM-->
		
	<?php
	if( $posted )
	{
	?>
	<!--SEARCH DETAIL-->
	<div class="event-search-detail">
		
		<span class="search-detail-left">
			<?php echo JText::sprintf( 'CC SEARCH RESULT' , $search ); ?>
		</span>
		
		<span class="search-detail-right">
			<?php echo JText::sprintf( (CStringHelper::isPlural($eventsCount)) ? 'CC EVENTS SEARCH RESULT TOTAL MANY' : 'CC EVENTS SEARCH RESULT TOTAL' , $eventsCount ); ?></span>
		<div style="clear:both;"></div>
		
	</div>
	<!--SEARCH DETAIL-->
	
	<?php echo $eventsHTML; ?>
	<?php
	}
	?>
</div>