
RegExp.escape = function(text) {
    return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
}

var Stepper;

(function ($) {

Stepper = {
	steps: [],
	current_step: -1, // starts at -1, call Stepper.next() to begin
	data: {},
	
	next: function () {
		var fadein = false;

		if (this.current_step > -1) {
			if ($.isFunction(this.steps[this.current_step].validate)) {
				if (!this.steps[this.current_step].validate()) {
					return;
				}
			}
			
			$('#step-error').hide();
			
			var stepData = this.steps[this.current_step].getData();
			$.extend(true, this.data, stepData);

			fadein = true;
		}
		
		if (this.current_step < this.steps.length - 1) {
			this.current_step++;
		}
		
		$('#curStep').text(this.current_step + 1);
		$('.prog-tab').removeClass('cur-tab');
		$('#progTab' + this.current_step).addClass('cur-tab');
		
		if (fadein) {
			var self = this; // javascript is now python
			$('#' + this.steps[this.current_step-1].id).fadeOut(function () {
				$('#' + self.steps[self.current_step].id).fadeIn();
				self.steps[self.current_step].initialize();
			});
		}
		else {
			$('#' + this.steps[this.current_step].id).show();
			this.steps[this.current_step].initialize();
		}
	},
	
	prev: function () {
		if (this.current_step > 0) {
			this.current_step--;
			
			var self = this;
			$('#' + this.steps[this.current_step+1].id).fadeOut(function () {
				$('#' + self.steps[self.current_step].id).show();
			});
		}
		
		$('#curStep').text(this.current_step + 1);	
		$('.prog-tab').removeClass('cur-tab');
		$('#progTab' + this.current_step).addClass('cur-tab');	
	}
};

// steps should look something like this:
var _step = {
	id: '',
	
	initialize: function () {
		if (this.message) {
			$('#' + this.id + ' .step-msg').text(this.message);
		}
	},
	
	validate: function () { // optional
		return true;
	},
	
	getData: function () {
		return {};
	}
};

// object to hold utility function (this is getting messy...)
var util = {
	updateList: function (add, remove, arr) {
		for (var i = 0; i < add.length; i++) {
			if ($.inArray(add[i], arr) == -1)
				arr.push(add[i]);
		}
		for (var i = 0; i < remove.length; i++) {
			var idx = $.inArray(remove[i], arr);
			if (idx != -1) arr.splice(idx, 1);
		}
	}
};

// *** location select *** in: nothing | out: id(s) of related location
location_step = {
	id: 'location_search',
	type: '',
	searchword: '',
	state: '',
	
	initialize: function () {
		var self = this;
		this.locations = $('#locationsList').children();
		this.selector = '#locationsList';
		
		if (this.message) {
			$('#' + this.id + ' .step-msg').text(this.message);
		}
		
		$('#states').change(function () {
			var state = $(this).val();
			self.state = state;
			self.getLocations();
		});
	
		$('#favoritesList').change(function () {
			$('#location_id').val($(this).val());
			$('#locName').val($(this).find(':selected').text());
			if (self.nudge) self.startNudge();
		});

		$('#locTypeahead').focusin(function () { 
			$('#locationsList').show(); 
			$('#locationSearch').addClass('expanded');
		});
		$(document).click(function (event) { 
			if (event.target != $('#locName')[0] && $(event.target).closest('#locationSearch').length == 0) {
				$('#locationsList').hide();
				$('#locationSearch').removeClass('expanded');
			}
		});
		
		this.scroll = true;
		$('#locationsList').scroll(function () {
			if (!self.scroll) return;
			var $list = $('#locationsList');
			if ($list.outerHeight() + $list.scrollTop() >= $list[0].scrollHeight) {
				self.getLocations(self.locations.length);
				self.scroll = false;
			}
		});
		
		$('#locName').keyup(function (e) {
			var key = (e.which || e.keyCode);

			$current = $(self.selector).find('.current');
			if (key == 37) { // left arrow
				return;
			}
		 	else if (key == 38) {  // up arrow
		 		$el = $current.parent().prev().find('a')
		 		
		 		if ($el.length) {
		 			$el.addClass('current');
		 			$(this).val($el.text());
		 		}
		 		
				$current.removeClass('current');
		 		return;
		 	}
		 	else if (key == 39) {
		 		return;
		 	}
			else if (key == 40) { // down arrow
				if (!$current.length) {
					$el = $(self.selector).find('a').first();
				}
				else {
					$el = $current.parent().next().find('a');
					$current.removeClass('current');
				}
				$el.addClass('current');
				$(this).val($el.text());
				return;
			}
			else if (key == 13) { // enter
				if ($current.length) {
					$current.click();
					return;
				}
			}

			self.searchword = $(this).val();
			self.getLocations();
		});
		
		$('#nudge').hide();	

		if (this.multiselect) {
			this.loc_ids = [];
			
			$('.locItemDel').live('click', function () {
				var location_id = $(this).attr('rel');
				util.updateList([], [location_id], self.loc_ids);
				$(this).parent().remove();
			});
		}
		
		$('.locationA').live('click', function () {
			var location_id = $(this).attr('rel');
			var location_title = $(this).text();
			
			if (self.multiselect) {
				if ($.inArray(location_id, self.loc_ids) == -1) {
					util.updateList([location_id], [], self.loc_ids);
					
					var listing_html = '<div class="odd">';
					listing_html += '<span>' + location_title + '</span>'; 
					listing_html += '<a class="locItemDel" rel="' + location_id + '" href="javascript:void(0);"><img src="/images/bg-rel8-remove.png" /></a>';
					listing_html += '<div class="clear"></div></div>';
					
					$listing = $(listing_html);
					$('.trip-locations').append($listing);
				}
			}
			else {
				$('#locName').val(location_title);
				$('#location_id').val(location_id);
				relate_step.catid = $(this).parent().attr('rel');
				if (self.nudge) self.startNudge();
			}
			
			$('#locationsList').hide();
			$('#locationSearch').removeClass('expanded');
		});
	
		$('input[name=location]').click(function () {
			$('.locationSelect').children('div').hide();
			$(this).siblings().show();
		});
		
		$('#nodisclose').change(function () {
			if ($(this).is(':checked')) {
				$('#location_id').val(0);
				
				// hide nudge map and clear lat/lng data if a location was previously selected
				$('#nudge').hide();	
				$('#' + mapmarker_step.id).fadeOut();
				Stepper.data.fields = {};
			}
		});
		
		if ($('#location_id').val() && this.nudge) this.startNudge();
	},
	
	// show nudge message, then load/display map marker step
	// since it does the same thing we want nudge location to do
	startNudge: function () {
		$.extend(Stepper.data, this.getData());

		$('#nudge').show();	
				
		$('#' + mapmarker_step.id).fadeIn(function () {
			mapmarker_step.initialize();
		});
	},
	
	getLocations: function (start) {
		var self = this;
		$.getJSON('index.php',
			{ 
				option: 'com_relate', controller: 'create',
				task: 'locations', searchword: this.searchword,
				type: this.type, state: this.state, start: start
			},
			function (data) {
				if (start) self.locations = self.locations.concat(data);
				else self.locations = data;
				
				if (!start || data.length > 0) {
					self.makeItems(start);
					self.rebuildList(start);
				}
			}
		);
	},
	
	makeItems: function (start) {
		if (!start) this.locationItems = [];
		for (var i = start || 0; i < this.locations.length; i++) {
			var html = '<li rel="%catid%"><a rel="%id%" class="locationA" href="javascript:void(0)">%title% (%category%)</a></li>';
			for (var field in this.locations[i]) {
				html = html.replace('%'+field+'%', this.locations[i][field]);
			}
			var $el = $(html);
			if (this.locations[i].child) {
				$el.addClass('child-spot');
			}
			this.locationItems.push($el);
		}
	},
	
	rebuildList: function (start) {
		var searchword = this.searchword;
		var locations = this.locationItems;
		var $list = $(this.selector);
		var regexsearch;

		if (searchword != '') {
			regexsearch = new RegExp(RegExp.escape(searchword), 'i');
		}

		if (!start) {
			$list.children().remove();
		}
		
		for (var i = start || 0; i < locations.length; i++) {
			var $loc = $(locations[i]);
			var locTitle = $loc.find('a').text();
			if (searchword == '') {
				$loc.find('a').html($loc.find('a').html().replace(/<\/?strong>/g, ''));
				$list.append($loc);
			}
			else if (regexsearch.test(locTitle)) {
				$loc.find('a').html(locTitle.replace(regexsearch, '<strong>$&</strong>'));
				$list.append($loc);
			}
		}
		
		this.scroll = true;
	},
	
	validate: function () {
		if ($('#nodisclose').is(':checked')) {
			return true;
		}
		else if (this.multiselect) {
			return true;
		}
		else if (!$('#location_id').val() || $('#location_id').val() == 0) {
			$('#step-error').text('You must choose a location!').show();
			return false;
		}
		
		return true;
	},
	
	getData: function () {
		if (this.multiselect) {
			// BRUTAL HACK
			// so users can go back in the form and remove some locations if they like 
			// (its cause Stepper uses recursive version of $.extend...)
			if (Stepper.data.locations) Stepper.data.locations = [];
			
			if ($('#nodisclose').is(':checked')) {
				this.loc_ids = [];
				$('.trip-locations').children().remove();
				return { "locations": [] };
			}
			else {
				return { "locations": this.loc_ids };
			}
		}
		
		var location_id = $('#location_id').val();
		
		$('#nudge, #' + mapmarker_step.id).hide();
		
		if (this.nudge) {
			return $.extend({ "location_id": location_id }, mapmarker_step.getData());
		}
		else {
			return { "location_id": location_id };
		}
	}
};

nameit_step = {
	id: 'name_it',
	
	initialize: function () {
		if (this.message) {
			$('#' + this.id + ' .step-msg').text(this.message);
		}
		
		if (Stepper.data.type == 'trip') {
			$('#trip_from').datepicker({ dateFormat: 'dd.mm.yy', altField: '#jr_startdate', altFormat: 'yy-mm-dd', maxDate: 0 }),
			$('#trip_to').datepicker({ dateFormat: 'dd.mm.yy', altField: '#jr_enddate', altFormat: 'yy-mm-dd', maxDate: 0 });
		}
	},
	
	validate: function () {
		var valid = true;
		var errors = [];
		
		if (!$('#title').val()) {
			if (Stepper.data.type == 'trip') {
				errors.push('Du må gi turen en tittel');
			}
			else {
				errors.push('You must name this spot!');
			}
			
			valid = false;
		}
		
		if (Stepper.data.type == 'trip') {
			if (!$('#jr_startdate').val()) {
				errors.push('You must enter a starting date!');
				valid = false;
			}
		}
		
		if (!valid) {			
			$('#step-error').html(errors.join('<br />')).show();;
		}
		
		return valid;
	},
	
	getData: function () {
		// save data from WYSIWYG into description field 
		if ($.isFunction(saveDescription)) {
			saveDescription();
		}
		
		var data = { 
			"title": $('#title').val(), 
			"description": $('#description').val(),
			"fb_share": ( $('#fb_post').is(':checked') ? 1 : 0)
		};
		
		if (Stepper.data.type == 'spot') {
			var tags = [];
			var spottags = '';
			var privacy = '';
			
			$('#other_fields .spot-tags input').each(function () {
				if ($(this).is(':checked')) {
					tags.push($(this).attr('name'));
				}
			});
			
			if (tags.length) {
				spottags = '*' + tags.join('*') + '*';
			}
			
			privacy = $('#other_fields .privacy-settings input:checked').val();
			
			data.fields = { "jr_fspottags": spottags, "jr_privacy": privacy };
		}
		else if (Stepper.data.type == 'trip') {
			data.fields = { "jr_startdate" : $('#jr_startdate').val(),
							"jr_enddate": $('#jr_enddate').val(),
							"jr_privacy": $('.fields\\[jr_privacy\\]:checked').val() };
		}
		
		return data;
	}
};

mapmarker_step = {
	id: 'map_marker',
	
	initialize: function () {
		if (this.message) {
			$('#' + this.id + ' .step-msg').text(this.message);
		}
		
		$('#spot_title').text($('#locName').val());

		var self = this;
		$.get('index.php',
		      { option: 'com_relate', controller: 'create',
		        task: 'coords', id: Stepper.data.location_id },
		      function (data) {
			      self.initMap($.parseJSON(data));
		      });
	},

	initMap: function (points) {
		var lat = points.coords[0];
		var lon = points.coords[1];

		geomaps.initializeMap(lat, lon, false);
		geomaps.marker.setImage('/components/com_jreviews_addons/geomaps/icons/blue.png');
		
		$('#' + jr_lat).val(lat); $('#' + jr_lon).val(lon);
		
		$('#' + jr_lat + ', #' + jr_lon).keyup(function () {
			var lat = $('#' + jr_lat).val();
			var lon = $('#' + jr_lon).val();
			var point = new GLatLng(lat, lon);
			
			geomaps.marker.setLatLng(point);
			geomaps.map.setCenter(point);
		});

		if (this.show_spots) {
			geomaps.map.removeOverlay(geomaps.marker);
			for (var i = 0; i < points.spots.length; i++) {
				geomaps.map.addOverlay(new GMarker(new GLatLng(points.spots[i][0], points.spots[i][1])));
			}
			geomaps.map.addOverlay(geomaps.marker);
			geomaps.marker.setImage('/components/com_jreviews_addons/geomaps/icons/blue.png');
		}
	},

	getData: function () {
		return { fields: {
		         jr_lat:  $('#' + jr_lat).val(),
		         jr_long: $('#' + jr_lon).val()
		}};
	}
};

// *** add catches *** in: n/a | out: json data for catch reports [{}, {}, ...] 
catches_step = {
	id: 'add_catches',
	num_catches: 1,
	
	initialize: function () {
		var self = this;
		
		if (this.message) {
			$('#' + this.id + ' .step-msg').text(this.message);
		}
		
		if (Stepper.data.location_id == 0) {
			$('#jr_catchanonymous').attr('checked', true);
			$('#jr_catchanonymous').parent().hide();
		}
		else {
			$('#jr_catchanonymous').attr('checked', false);
			$('#jr_catchanonymous').parent().show();
		}
		
		$('.startdate').datepicker({
			dateFormat: 'dd.mm.yy',
			altField: '#jr_date',
			altFormat: 'yy-mm-dd',
			maxDate: '0'
		});
	},
	
	validate: function () {
		var catchesValid = true;
		var errors = [];
		
		$('#catches').find('.validate').each(function () {
			if ($(this).val() == '') {
				catchesValid = false;
			}
		});
		
		if (!catchesValid) {
			errors.push('Each catch must have a date, species and weight!');
		}
		
		var time = $('#jr_time').val();
		if (time != '' && ! /^\d{1,2}:\d{2}$/.test(time)) {
			errors.push('Time must be in a valid format! (hh:mm)');
			catchesValid = false; 	
		}
		
		var weight = $('#jr_catchweight').val();
		if (weight != '' && ! /^\d{1,6}$/.test(weight)) {
			errors.push('Weight must be a number! (max. 6 digits)');
			catchesValid = false;
		}
		
		if (!catchesValid) {			
			$('#step-error').html(errors.join('<br />')).show();;
		}
		
		return catchesValid;
	},

	saveCatches: function () {
		// save data from WYSIWYG into description field 
		if ($.isFunction(saveDescription)) {
			saveDescription();
		}
		
		var catchList = [];
		for (var i = 0; i < this.num_catches; i++) {
			var fangst = {};
			$('.catch-' + i).find('input, select, textarea').each(function () {
				var value;
				if ($(this).attr('type') == 'checkbox') {
					value = $(this).is(':checked') ? '*ja*' : '*nei*';
				}
				else if ($(this).attr('type') == 'radio') {
					value = $('.' + $(this).attr('name').replace(/[\[\]]/g, '\\$&') + ':checked').val();
				}
				else {
					value = $(this).val();
				}

				var name = $(this).attr('name');
				if (matches = name.match(/(\w+)\[(\w+)\]/)) {
					fangst[matches[1]] = fangst[matches[1]] || {};
					fangst[matches[1]][matches[2]] = value;
				}
				else {
					fangst[name] = value; 
				}
			});
			if (Stepper.data.fields && fangst.fields) { // hack to get jr_lat, jr_long in each catch
				$.extend(true, fangst.fields, Stepper.data.fields);
			}
			catchList.push(fangst);
		}

		return catchList;
	},
	
	getData: function () {
		return { "catches": this.saveCatches() };
	}
};

hatches_step = {
	id: 'add_hatches',
	
	initialize: function () {
		if (this.message) {
			$('#' + this.id + ' .step-msg').text(this.message);
		}
		
		if (Stepper.data.location_id == 0) {
			$('#jr_hatchanonymous').attr('checked', true);
			$('#jr_hatchanonymous').parent().hide();
		}
		else {
			$('#jr_hatchanonymous').attr('checked', false);
			$('#jr_hatchanonymous').parent().show();
		}
		
		$('.hatchdate').datepicker({
			dateFormat: 'dd.mm.yy',
			altField: '#jr_hatchdate',
			altFormat: 'yy-mm-dd',
			maxDate: 0
		});
	},
	
	validate: function () {
		var hatchesValid = true;
		var errors = [];
		
		$('#' + this.id).find('.validate').each(function () {
			if ($(this).val() == '') {
				hatchesValid = false;
			}
		});
		
		if (!hatchesValid) {
			errors.push('Each hatch must have a date and insect!');
		}
		
		var time = $('#jr_hatchtime').val();
		if ( ! /^\d{1,2}:\d{2}$/.test(time)) {
			errors.push('Time must be in a valid format! (hh:mm)');
			hatchesValid = false; 	
		}
		
		var time = $('#jr_hatch_endtime').val();
		if (time != '' && ! /^\d{1,2}:\d{2}$/.test(time)) {
			errors.push('End Time must be in a valid format! (hh:mm)');
			hatchesValid = false; 	
		}
		
		if (!hatchesValid) {			
			$('#step-error').html(errors.join('<br />')).show();;
		}
		
		return hatchesValid;
	},
	
	getData: function () {
		// save data from WYSIWYG into description field 
		if ($.isFunction(saveDescription)) {
			saveDescription();
		}
		
		var hatch = {};
		$('#' + this.id).find('input, select, textarea').each(function () {
			var value;
			if ($(this).attr('type') == 'checkbox') {
				value = $(this).is(':checked') ? '*ja*' : '*nei*';
			}
			else if ($(this).attr('type') == 'radio') {
				value = $('.' + $(this).attr('name').replace(/[\[\]]/g, '\\$&') + ':checked').val();
			}
			else {
				value = $(this).val();
			}

			var name = $(this).attr('name');
			if (matches = name.match(/(\w+)\[(\w+)\]/)) {
				hatch[matches[1]] = hatch[matches[1]] || {};
				hatch[matches[1]][matches[2]] = value;
			}
			else {
				hatch[name] = value; 
			}
		});
		if (Stepper.data.fields && hatch.fields) { // hack to get jr_lat, jr_long in each catch
			$.extend(true, hatch.fields, Stepper.data.fields);
		}
		return hatch;
	}
};

// ** add existing ** in: category ids | out: related ids[] 
relate_step = {
	id: 'add_existing',
	related_ids: [],
	listing_elements: {},
	
	initialize: function () {
		var self = this;
		
		if (this.message) {
			$('#' + this.id + ' .step-msg').text(this.message);
		}
				
		// change available techniques depending on freshwater/saltwater type of location
		// (location catid) -> (technique catids)
		// 1,2,4 -> 24,79 | 3, 100 -> 48,79
		if (Stepper.data.type == 'spot' && this.catid) {
			var catid = this.catid;
			if (catid == 1 || catid == 2 || catid == 4) {
				$('.f-techniques .rel-items').attr('id', 'related-items2479');
				$('.f-techniques .addmore').attr('href', 'index.php?option=com_relate&cat=24,79');
			}
			else if (catid == 3 || catid == 100) {
				$('.f-techniques .rel-items').attr('id', 'related-items4879');
				$('.f-techniques .addmore').attr('href', 'index.php?option=com_relate&cat=48,79');
			}
		}
	
		// redefine _initEditRelations function for some custom popup behavior.....
		_initEditRelations = function (listing_id, cat_id) {
			var related_ids = self.related_ids;
			
			Relations.init(listing_id);
			Listings.init({listing_id: listing_id, selector: '.rel8Listings'});
	
			$('#lsearch').keyup(function () {
				Listings.setSearch(this.value);
			});
	
			if (cat_id !== '') {
				Listings.catID = cat_id;
				Listings.get(cat_id, '.rel8CatTitle');
				$('#addLink').hide();
				$('.rel8Add').show();
			}
			
			// clear cached listing elements 
			self.listing_elements = {};
			
			// get rid of default save action  
			$('#btnsave')[0].onclick = null;
	
			$('#btnsave').click(function () {
				util.updateList(Relations.add_ids.listings, Relations.remove_ids.listings, related_ids);
				
				var selector = '#related-items' + cat_id.replace(/,/g, '');
				$(selector).children().remove();
				for (var i = 0; i < related_ids.length; i++) {
					//var listing_data = Listings.listings[related_ids[i]];
					var listing_data = self.listing_elements[related_ids[i]];

					if (!listing_data) continue;
					var listing_html = '<div class="rel8Item">';
					if (listing_data.thumbnail) {
						listing_html += '<img class="listingThumb" src="' + listing_data.thumbnail + '" />';
					}
					listing_html += '<span>' + listing_data.title + '</span>'; 
					listing_html += '<a class="rel8ItemDel" rel="' + listing_data.id + '" href="javascript:void(0);"><img src="/images/bg-rel8-remove.png" /></a>';
					listing_html += '<div class="clear"></div></div>';
					var $listing = $(listing_html);
					if ((i + 1) % 2) $listing.addClass('odd');
					$(selector).append($listing);
				}
	
				cWindowHide();
			});
		}
	
		// run after Listings.rebuild so we can show previously added items as related
		Listings.onRebuild = function () {
			for (var i = 0; i < self.related_ids.length; i++) {
				$('li[listing_id="' + self.related_ids[i] + '"]').children('a').addClass('related');
				var id = self.related_ids[i];
				if (Listings.listings[id]) {
					self.listing_elements[id] = Listings.listings[id];
				}
			}
		};
		
		// cache listing data for elements that are selected/unselected
		// because they may not be available when we need them later
		// (for example, if some of them have been filtered out by a search) 
		Relations.onAfterAdd = function (id, node) {
			if (!self.listing_elements[id]) {
				self.listing_elements[id] = Listings.listings[id];
			}
		};
	
		$('.rel8ItemDel').live('click', function () {
			util.updateList([], [$(this).attr('rel')], self.related_ids);
			$(this).parent().remove();	
		});
	},
	
	getData: function () {
		var data = { "related_ids": this.related_ids };
		
		if (Stepper.data.type == "trip") {
			data.fields = { "jr_catchsummary": $('#jr_catchsummary').val() }
		}
		
		return data;
	}
};

upload_step = {
	id: 'media_upload',
	photos: [],
	videos: [],
	descriptions: {}, 
	
	initialize: function () {
		var self = this;
		
		if (this.message) {
			$('#' + this.id + ' .step-msg').text(this.message);
		}	
		
		$('.next').unbind('click');
		
		$('.next').click(function () {
			var desc_texts = []; 
			$('.file-description').each(function (idx) {
				if (idx == 0) return;
				desc_texts.push($(this).val());
			});
			
			joms.jQuery(document).ajaxStop(function (){
				joms.jQuery(this).unbind("ajaxStop");
				$('#community-photo-items').find('img').each(function (idx) {
					self.photos.push($(this).attr('src'));
					if (desc_texts[idx]) {
						self.descriptions[$(this).attr('src')] = desc_texts[idx];
					}
				});
				Stepper.next();
			});
			joms.uploader.startUpload();
		});
		
		var clickReset = function () {
			$('.next').unbind('click');
			$('.next').click(function () {
				Stepper.next();
			});
			$('.back').unbind('click', clickReset);
		};
		
		$('.back').click(clickReset);
	},
	
	getData: function () {
		return { media: { photos: this.photos, videos: this.videos, descriptions: this.descriptions } };
	}
};

thankyou_step = {
	id: 'thank_you',
	
	initialize: function () {
		if (this.tycalled) return;
		
		this.tycalled = true;
		
		var self = this;
		
		if (this.content_type) {
			$('#' + this.id + ' .content-type').text(this.content_type);
		}
		
		//$('.content-data').text(Json.toString(Stepper.data));
		$.post('index.php?option=com_relate&controller=create&task=save', Stepper.data, 
			function (data) {
				$('#thank_you .loading').fadeOut();
				var response = $.parseJSON(data);
				if (response && response.length) {
					for (var i = 0; i < response.length; i++) {
						$listing = self.checkoutInfo(response[i]);
						if ((i + 1) % 2) $listing.addClass('odd');
						$('#create_results').append($listing);
						$('#thank_you button, #thank_you p').fadeIn();
					}
				}
			}
		);
		
		$('.back, .next').hide();
	},
	
	checkoutInfo: function (listing_data) {
		var listing_html = '<div class="rel8Item">';
		if (listing_data.thumbnail) {
			listing_html += '<img class="listingThumb" src="' + listing_data.thumbnail + '" />';
		}
		listing_html += '<span class="listingTitle">' + listing_data.title + '</span>'; 
		listing_html += '<a class="listingLink" href="' + listing_data.link + '">Check it out</a>';

		listing_html += '<div class="clear"></div></div>';
		var $listing = $(listing_html);

		return $listing;
	},
	
	getData: function () {
		return {}; 
	}
};

}) (jQuery);
