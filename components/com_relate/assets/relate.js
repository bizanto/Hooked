var Relations;
var Listings;
var _initEditRelations;

(function ($) {

_initEditRelations = function (listing_id, cat_id, ss) {
	Relations.init(listing_id);
	Listings.init({listing_id: listing_id, selector: '.rel8Listings'});

	if (typeof ss === 'undefined' || ss != 1) {
		ss = false;
	}
	Relations.single_select = ss;
	
	$('#lsearch').keyup(function () {
		Listings.setSearch(this.value);
	});

	if (cat_id !== '') {
		Listings.catID = cat_id;
		Listings.get(cat_id, '.rel8CatTitle');
		 
		var add_link = $('li[cat_id='+cat_id+'] a').attr('href');
		if (add_link && add_link != '/') $('#addLink').attr('href', add_link+'&relate_id='+listing_id).show();
		else $('#addLink').hide();

		$('.rel8Add').show();
	}
	else {
		$('.rel8Cats').show();
	}

	$('.catLink').click(function () {
		$('.rel8CatTitle').text($(this).text());
		
		$('.catLink').removeClass('cselect');
		$(this).addClass('cselect');
		
		$('.rel8Cats').fadeOut(function () {
			$('.rel8Add').fadeIn();
		});
		
		var catID = this.parentNode.getAttribute('cat_id');
		Listings.catID = catID;
		Listings.get(catID);

		var href = $(this).attr('href');
		if (href.indexOf('com_jreviews') != -1) href += '&relate_id='+listing_id;
		if (href != '/') $('#addLink').attr('href', href).show();
		else $('#addLink').hide();
		
		return false;
	});

	$('#btncancel').click(function () {
		Relations.clear();
		$('.rel8Listings').children().remove();
		$('.rel8Add').fadeOut(function () {
			// $('.rel8Cats').fadeIn();
		});
	});

	$('#addLink').click(function () {
		_href = $(this).attr('href');

		if (_href.indexOf('videos') != -1) {
			$.post('index.php', { option: 'com_relate', task: 'saveId', id: listing_id },
				function () { joms.videos.addVideo(); });
			return false;
		}
		else {
			return true;
		}
	});
	
	if (!$.isFunction(Relations.onAfterSave)) {
		Relations.onAfterSave = function () { 
			window.location.reload();
		};
	}
};

Relations = {
	add_ids: { listings: [], photos: [], videos: [] },
	remove_ids: { listings: [], photos: [], videos: [] },

	init: function (listing_id) {
		this.listing_id = listing_id;
		this.clear();
	},

	clear: function () {
		this.add_ids = { listings: [], photos: [], videos: [] };
		this.remove_ids = { listings: [], photos: [], videos: [] };
	},

	add: function(node) {
		var id = node.parentNode.getAttribute('listing_id');

		// add id to arr[] only if it doesn't already exist in arr[]
		var addId = function (arr, id) {
			for (var i = 0; i < arr.length; i++) {
				if (arr[i] == id) {
					return;
				}
			}
			
			arr.push(id);
		};

		// remove all occurences of id from arr[]
		var removeId = function(arr, id) {
			for (var i = 0; i < arr.length; i++) {
				if (arr[i] == id) {
					arr.splice(i, 1);
				}
			}
		};

		node = $(node); // wrap with $ for hasClass, etc.

		var addNodes, remNodes;
		if (node.hasClass('photos')) {
			addNodes = this.add_ids.photos;
			remNodes = this.remove_ids.photos;
		}
		else if (node.hasClass('videos')) {
			addNodes = this.add_ids.videos;
			remNodes = this.remove_ids.videos;
		}
		else {
			addNodes = this.add_ids.listings;
			remNodes = this.remove_ids.listings;
		}
		
		if (node.hasClass('related')) {
			addId(remNodes, id);
			node.removeClass('related');
			node.addClass('remove');
		}
		else if (node.hasClass('remove')) {
			removeId(remNodes, id);
			node.removeClass('remove');
			node.addClass('related');
		}
		else if (!node.hasClass('selected')) {
			if (this.single_select) {
				$('.selected').removeClass('selected');
				// make sure old relation gets removed
				$('.related').each(function () {
					addId(remNodes, $(this).parent().attr('listing_id'));
				});
				// if new id was set to be removed before (say user re-selects existing related item)
				// make sure it doesnt still get removed
				removeId(remNodes, id);
				$('.related').removeClass('related');
				addNodes.splice(0, addNodes.length); 
			}
			addId(addNodes, id);
			node.addClass('selected');
		}
		else {
			removeId(addNodes, id);
			node.removeClass('selected');
		}
		
		if ($.isFunction(this.onAfterAdd)) {
			this.onAfterAdd(id, node);
		}
	},

	save: function (cc) {
		var self = this;
		$.ajax({
			type: "post",
			url: "index.php",
			data: {
				option: 'com_relate',
				task: 'relate',
				id: this.listing_id,
				add: this.add_ids,
				rem: this.remove_ids
			},
			success: function (response) {
				$('.selected').addClass('related').removeClass('selected');
				$('.remove').removeClass('remove');
				self.clear();
				if ($.isFunction(self.onAfterSave)) {
					self.onAfterSave();
				}
				if (cc) cc(); // call continuation
			}
		});
	}
};

Listings = {
	listings: [],
	searchword: '',

	init: function (options) {
		for (var key in options) {
			this[key] = options[key];
		}
		
		var self = this;
		
		this.searchword = '';
		
		this.scroll = true;
		var scrollPane = '.rel8ListingsContainer';
		$(scrollPane).scroll(function () {
			if (!self.scroll) return;
			var $list = $(scrollPane);
			if ($list.outerHeight() + $list.scrollTop() >= $list[0].scrollHeight) {
				// listings is an object (not array) so we have to count number of items manually
				var count = 0;
				for (var i in self.listings) {
					count++;
				}
				self.get(self.catID, '', count);
				self.scroll = false;
			}
		});
	},

	makeItems: function (extraClass, start) {
		for (var i in this.listings) {
			if (this.listings[i].$item) continue; // don't re-create items that were already there
			
			var listing = this.listings[i];
			var $li = $('<li listing_id="' + listing.id + '"></li>');
			
			var ahtml = '';
			if (listing.thumbnail) {
				ahtml = '<img src="' + listing.thumbnail + '" />';
			}
			ahtml += '<span class="itemTitle">' + listing.title + '</span>';

			var $a = $('<a href="#" onclick="Relations.add(this); return false;">' + ahtml + '</a>');
			if (listing.related > 0) $a.addClass("related");
			
			if (extraClass !== undefined && extraClass != '') {
				$a.addClass(extraClass);
			}
			
			// make sure user selections still show when list items are re-created by a search
			if (extraClass == 'photos' || extraClass == 'videos') {
				if ($.inArray(listing.id, Relations.add_ids[extraClass]) != -1) $a.addClass("selected");
				else if ($.inArray(listing.id, Relations.remove_ids[extraClass]) != -1) $a.addClass("remove");
			}
			else {
				if ($.inArray(listing.id, Relations.add_ids.listings) != -1) $a.addClass("selected");
				else if ($.inArray(listing.id, Relations.remove_ids.listings) != -1) $a.addClass("remove");
			}
			
			$li.append($a);
			listing.$item = $li;
		}
	},

	rebuild: function () {
		var $list = $(this.selector);
		var regexsearch;
		
		if (this.searchword != '') {
			regexsearch = new RegExp(this.searchword, 'i');
		}

		$list.children().remove();

		for (var i in this.listings) {
			var listing = this.listings[i];
			if (this.searchword == '') {
				listing.$item.find('.itemTitle').html(listing.title);
				$list.append(listing.$item);
			} 
			else if (regexsearch.test(listing.title)) {
				listing.$item.find('.itemTitle').html(listing.title.replace(regexsearch, '<strong>$&</strong>'));
				$list.append(listing.$item);
			}
		}

		if ($.isFunction(this.onRebuild)) {
			this.onRebuild();
		}
		
		this.scroll = true;
	},

	setSearch: function (search) {
		this.searchword = search;
		this.get(this.catID);	
	},

	get: function (catID, elTitle, start) {
		var self = this;
		$.getJSON("index.php", 
			{ option: 'com_relate', task: 'listings', 
			  cat: catID, id: this.listing_id,
			  searchword: this.searchword, start: start },
			function (data) {
				if (start) self.listings = $.extend(self.listings, data.listings);
				else self.listings = data.listings;
				
				if (typeof elTitle != "undefined") $(elTitle).text(data.type);

				var itemclass = '';
				if (typeof catID == "string") itemclass = catID;
				
				// check if there's any items in data.listings (can't use data.length since its an object)
				var hasListings = false;
				for (var i in data.listings) {
					hasListings = true; break;
				}
				
				if (!start || hasListings) {
					self.makeItems(itemclass, start);
					self.rebuild();
				}
			});
	}
};

}) (jQuery);

