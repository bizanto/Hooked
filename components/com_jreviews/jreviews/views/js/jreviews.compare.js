jreviewsCompare = {
    numberOfListingsPerPage: 3,
    compareURL: 'index.php?option=com_jreviews&url=categories/compare/type:type_id/',
    lang: {
        'compare_all':'Compare All',
        'remove_all':'Remove All',
        'select_more':'You need to select more than one listing for comparison!'
    },
    listingTypeID: null,
    set: function(options)
    {
        if(options!=undefined)jQuery.extend(jreviewsCompare, options); 
    },
    initComparePage: function()
    {
    	var compareContainer = jQuery('#jr_compareview').parent();
        compareContainer.width(compareContainer.parent().width());
		
        //scrollpane parts
        var scrollPane = jQuery('.scroll-pane');
        var scrollContent = jQuery('.scroll-content');
        
        var numberOfListings = jQuery('div.scroll-content-item').length;
        
        if (numberOfListings >= jreviewsCompare.numberOfListingsPerPage) {
            var itemWidth = scrollPane.width() / jreviewsCompare.numberOfListingsPerPage;
        }
        else {
            var itemWidth = scrollPane.width() / numberOfListings;
        }
        
        var contentWidth = itemWidth * numberOfListings;
        
        scrollContent.width(contentWidth);
        jQuery('div.scroll-content-item').width(itemWidth);
                            
        // build slider
        var scrollbar = jQuery(".scroll-bar").slider({
            slide: function(e, ui){
                if ( scrollContent.width() > scrollPane.width() ){ 
                    scrollContent.css('margin-left', Math.round( ui.value / 100 * ( scrollPane.width() - scrollContent.width() )) + 'px'); 
                }
                else { 
                    scrollContent.css('margin-left', 0); 
                }
                jQuery(".scroll-bar").slider("option", "value", ui.value);
            }
        });
        
        //append icon to handle
        var handleHelper = scrollbar.find('.ui-slider-handle')
        .mousedown(function(){
            scrollbar.width( handleHelper.width() );
        })
        .mouseup(function(){
            scrollbar.width( '100%' );
        })
        .append('<span class="ui-icon ui-icon-grip-dotted-vertical"></span>')
        .wrap('<div class="ui-handle-helper-parent"></div>').parent();
        
        //change overflow to hidden now that slider handles the scrolling
        scrollPane.css('overflow','hidden');
        
        adjustScrollbar();
        
        //size scrollbar and handle proportionally to scroll distance
        function sizeScrollbar(){
            var remainder = scrollContent.width() - scrollPane.width();
            var proportion = remainder / scrollContent.width();
            var handleSize = scrollPane.width() - (proportion * scrollPane.width());
            scrollbar.find('.ui-slider-handle').css({
                width: handleSize,
                'margin-left': -handleSize/2
            });
            handleHelper.width('').width( scrollbar.width() - handleSize);
        }
        
        //reset slider value based on scroll content position
        function resetValue(){
            var remainder = scrollPane.width() - scrollContent.width();
            var leftVal = scrollContent.css('margin-left') == 'auto' ? 0 : parseInt(scrollContent.css('margin-left'));
            var percentage = Math.round(leftVal / remainder * 100);
            scrollbar.slider("value", percentage);
        }
        
        //if the slider is 100% and window gets larger, reveal content
        function reflowContent(){
                var showing = scrollContent.width() + parseInt( scrollContent.css('margin-left') );
                var gap = scrollPane.width() - showing;
                if(gap > 0){
                    scrollContent.css('margin-left', parseInt( scrollContent.css('margin-left') ) + gap);
                }
        }

        // hide scrollbar if there are less items than overall width
        function adjustScrollbar() {
            if (numberOfListings <= jreviewsCompare.numberOfListingsPerPage) {
                jQuery('img.removeListing').hide();
                jQuery('div.scroll-bar-wrap').hide();
            }        
        }
        
        //change handle position on window resize
        jQuery(window)
        .resize(function(){
                resetValue();
                sizeScrollbar();
                reflowContent();
        });
        
        //init scrollbar size
        setTimeout(sizeScrollbar,10);//safari wants a timeout
        
        jreviewsCompare.fixCompareAlignment();
    
        jQuery("div.comparedListings div.scroll-content-item").hover(
            function(){                    
                var listing = jQuery(this).attr('class').split(' ').slice(-1); 
                var listingID = listing[0].substring(4);
                var listingWidth = jQuery(this).width();
                
                if (numberOfListings > jreviewsCompare.numberOfListingsPerPage) {
                    jQuery("div.scroll-content-item."+listing + " img.removeListing").live('click', function(){
                        jQuery("div.scroll-content-item."+listing).fadeOut('slow', function() {
                            jQuery("div.scroll-content-item."+listing).remove();
                            numberOfListings = jQuery('div.scroll-content-item').length;
                            scrollContent.width(itemWidth * numberOfListings);
                            resetValue();
                            sizeScrollbar();
                            reflowContent();
                            adjustScrollbar();
                            // remove the listing from comparison list and cookie also
                            jQuery('span#removelisting'+listingID).trigger('click');
                        });                                                    
                    });
                }
            }
        );
    },
    fixCompareAlignment: function()
    {
        function eqHeight(group) {
           tallest = 0;
           group.each(function() {
              thisHeight = jQuery(this).height();
              if(thisHeight > tallest) {
                 tallest = thisHeight;
              }
           });
           group.height(tallest);
        }                    
        function fixHeight(group) {
            group.each(function() {
                var firstclass = jQuery(this).attr('class').split(' ').slice(0,1); 
                eqHeight(jQuery('div.'+firstclass));
            });
        }
        var compareFields = jQuery('div.scroll-content-item div.compareField');
        fixHeight(compareFields);        
    },
    fixCompareImagesAlignment: function()
    {
        function eqHeight(group) {
           tallest = 0;
           group.each(function() {
              thisHeight = jQuery(this).height();
              if(thisHeight > tallest) {
                 tallest = thisHeight;
              }
           });
           jQuery('div.itemThumbnail.compareField').height(tallest);
        }                                    
        var listingThumbs = jQuery('div.itemThumbnail img');
        eqHeight(listingThumbs);      
    },    
    initCompareDashboard: function()
    {
        var cmpSpan = jQuery('span.compareListing span:first');
        var cmpDiv = jQuery('div#jr_compareview');
        if (jQuery('span.compareListing span:first').length) {
            var pageListingType = jQuery('span.compareListing span:first').attr('class').substring(11);    
        } else if(cmpDiv.length){
            var pageListingType = cmpDiv.attr('class').substring(11);    
        }
        var cookies = jreviewsCompare.getCookies();
        if (cookies) {            
            jQuery('#jr_compareListings').slideDown('slow');
            jQuery('p#comparisonMessage').hide();
            jQuery('#jr_compareListingsInner').children().remove();    
            jQuery.each(cookies, function(cookieName, listingTypeTitle) {                                
                var cookieListings = jreviewsCompare.getListingsFromCookie(cookieName);        
                var listingTypeID = cookieName.substring(9);        
                if (cookieListings) {                        
                    compareURL = jreviewsCompare.compareURL.replace('type_id',listingTypeID);
                    compareAllLink = '<a href="' + compareURL + '" class="compareListings ui-corner-all listingType' +listingTypeID + '">'+jreviewsCompare.lang.compare_all+'</a>';                            
                    removeAllLink = '<a href="#" id="jrCompare' + listingTypeID + '" class="removeListings ui-corner-all listingType' + listingTypeID + '">'+jreviewsCompare.lang.remove_all+'</a>';
                    jQuery('#jr_compareListingsInner').append('<div class="listingTypeCompare jrCompare'+ listingTypeID +'"><div class="listingTypeTitle ui-corner-all">' + listingTypeTitle + removeAllLink + compareAllLink + '</div><ul class="ui-helper-clearfix listings' + listingTypeID + '" style="display: none;"></ul></div>');                    
                    jQuery.each(cookieListings, function(key, value) {
                        var listingTitle = value.title;
                        if (value.thumbnail != undefined) {
                            var listingThumbSrc = value.thumbnail;
                            if (listingThumbSrc.length > 0) {
                                listingThumb = '<img class="compareThumb" src="'+ listingThumbSrc +'" />';
                                var titlePadding = '';
                            } else {
                                listingThumb = '';
                                var titlePadding = 'style="padding-left: 35px;"';
                            }
                        } else {
                            listingThumb = '';
                            var titlePadding = 'style="padding-left: 35px;"';
                        }               
                        jQuery('div.listingTypeCompare ul.listings' + listingTypeID).append('<li class="' + key + '">' + listingThumb + '<span class="compareItemTitle"' + titlePadding + '>' + listingTitle + '</span><span id="remove' + key + '" class="removeItem">' + listingTypeID + '</span></li>');
                        jQuery('input#'+key).attr('checked', 'checked');
                    });                        
                }
            });
            if (jQuery('div.listingTypeCompare ul.listings'+pageListingType).length > 0 && jQuery('div#jr_compareview').length != 1) {
                // slideDown listings that belong to the currently viewed page
                jQuery('div.listingTypeCompare ul.listings'+pageListingType).slideDown('slow');
            }
            else if (jQuery('div#jr_compareview').length == 1) {
                // slideDown first listing type listings
                jQuery('div.listingTypeCompare ul').hide('fast');                    
            }
            
            else {
                jQuery('#jr_compareListingsInner').hide();
                // slideDown first listing type listings
                jQuery('div.listingTypeCompare ul:first').slideDown('slow');                    
            }
        }
        
        jQuery('#jr_compareListingsInnerTop').click(function() {
            jQuery('#jr_compareListingsInner').slideToggle(800);
        });                    

        // accordion
        jQuery('div.listingTypeTitle').live('click', function(e){
            if ( e.isPropagationStopped() ) { return false; }
            var checkElement = jQuery(this).next();
            if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
                checkElement.slideUp('slow');
                return false;
            }
            if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
                jQuery('div.listingTypeCompare ul:visible').slideUp('slow');
                checkElement.slideDown('slow');
                return false;
            }
        });        
    
        // remove listing from comparison after icon clicked
        jQuery('span.removeItem').live('click',function() {                            
            var item = jQuery(this).attr('id');                        
            var listingID = item.substring(13);
            var listingType = jQuery(this).text();        
            listing = 'listing'+listingID;    
            jQuery('input#'+listing).attr('checked', false);
            if(jQuery('li.'+listing).siblings().length > 0) {
                jQuery('li.'+listing).slideUp('slow', function(){
                    jQuery(this).remove();
                });
            }
            else {
                jQuery('li.'+listing).parent().parent().slideUp('slow', function(){
                    jQuery(this).remove();
                    jreviewsCompare.removeListingTypeFromCookie('jrCompare'+listingType);
                    if(jQuery('div.listingTypeCompare').length < 1){
                        jQuery('div#jr_compareListings').slideUp('slow');
                    }
                });
                jQuery('div.listingTypeCompare ul:eq(1)').slideDown('slow');
            }
            jreviewsCompare.removeListingFromCookie(listing, listingType);
        });    


        // remove all listings from comparison
        jQuery('a.removeListings').live('click', function(e) {    
            e.stopPropagation();
            e.preventDefault();
            var cookieName = jQuery(this).attr('id');
            jQuery('div.'+cookieName).slideUp(800, function(){
                // remove listing type group and slideDown first hidden listings group
                jQuery(this).remove();
                var visibleFieldGroups = jQuery('div.listingTypeCompare ul:visible');
                if (visibleFieldGroups.length < 1) {
                    var hiddenFieldGroups = jQuery('div.listingTypeCompare ul:hidden');
                    if (hiddenFieldGroups.length > 0){
                        jQuery(hiddenFieldGroups).eq(0).slideDown('slow');
                    }
                    else {
                        jQuery('#jr_compareListings').slideUp('slow');
                        jQuery('#jr_compareListingsInner').children().remove();                    
                    }
                }
                else {
                    return false;
                }
            });
            // get listings inside the cookie to uncheck checkboxes
            var cookieListings = jreviewsCompare.getListingsFromCookie(cookieName);                    
            jQuery.each(cookieListings, function(key, value) {
                jQuery('input#'+key).attr('checked', false);
            });    
            jQuery.extendedjsoncookie("removeCookie", cookieName); 
            jreviewsCompare.removeListingTypeFromCookie(cookieName);
        });    
        
        // compare all listings
        jQuery('a.compareListings').live('click', function(e) {    
            e.stopPropagation();
            var compareCount = jQuery(this).parent().next().children().length;
            var listingType = jQuery(this).attr('class').split(' ').slice(-1);
            if (compareCount < 2) {
                if (jQuery(this).parent().next().is(':hidden')){
                    jQuery('div.listingTypeCompare ul:visible').slideUp('slow');
                    jQuery(this).parent().next().slideDown('slow');
                }
                if (jQuery('p.comparisonMessage.'+listingType).val() != ''){                    
                    jQuery(this).parent().parent().append('<p class="comparisonMessage '+ listingType +'">'+jreviewsCompare.lang.select_more+'</p>');
                    jQuery('p.comparisonMessage').hide().slideDown('slow', function(){
                        setTimeout( function(){ 
                            jQuery('p.comparisonMessage').slideUp('slow', function(){
                                jQuery(this).remove();
                            });
                        }, 3000 ); 
                    });
                }
                return false;
            }
            else {
                var url = jQuery(this).attr('href');
                window.location.href = url;
            }
        });            
    },
    initListPage: function()
    {
        // select listing for comparison - checkbox
        jQuery('input.checkListing').live('click', function() {
            var listingID = jQuery(this).attr('value');
            var listingTypeID = jQuery('span#listingID'+listingID).attr('class').substring(11);
            var listingTypeTitle = jQuery('span#listingID'+listingID).text();    
            var listingTitle = jQuery(this).attr('name');
            if (jQuery('img#thumb'+listingID).length > 0) {
                var listingThumbSrc = jQuery('img#thumb'+listingID).attr('src');
                listingThumb = '<img class="compareThumb" src="'+ listingThumbSrc +'" />';
                var titlePadding = '';   
            } else {
                listingThumb = '';
                var titlePadding = 'style="padding-left: 35px;"';
            }
            
            var listingData = '<li class="listing' + listingID + '">'+ listingThumb +'<span class="compareItemTitle"' + titlePadding + '>' + listingTitle + '</span><span id="removelisting' + listingID + '" class="removeItem">' + listingTypeID + '</span></li>';        
            if (jQuery(this).attr('checked')) {                            
                if (jQuery('#jr_compareListings').is(':hidden')) {
                    jQuery('#jr_compareListings').slideDown('slow');
                }
                if (jQuery('#jr_compareListingsInner').is(':hidden')) {
                    jQuery('#jr_compareListingsInner').slideDown('slow');
                }                
                if (jQuery('div.listingTypeCompare ul.listings'+listingTypeID).length > 0) {
                    if (jQuery('div.listingTypeCompare ul.listings'+listingTypeID).is(':hidden')) {
                        jQuery('div.listingTypeCompare ul:visible').slideUp('slow');
                        jQuery('div.listingTypeCompare ul.listings'+listingTypeID).slideDown('slow');
                    }
                }
                else {                
                    jQuery('div.listingTypeCompare ul:visible').slideUp('slow');
                    compareURL = jreviewsCompare.compareURL.replace('type_id',listingTypeID); 
                    compareAllLink = '<a href="' + compareURL + '" class="compareListings ui-corner-all listingType' + listingTypeID + '">'+jreviewsCompare.lang.compare_all+'</a>';                            
                    removeAllLink = '<a href="#" id="jrCompare' + listingTypeID + '" class="removeListings ui-corner-all listingType' + listingTypeID + '">'+jreviewsCompare.lang.remove_all+'</a>';
                    var listingsGroup = '<div class="listingTypeCompare jrCompare'+ listingTypeID +'"><div class="listingTypeTitle ui-corner-all">' + listingTypeTitle + removeAllLink + compareAllLink + '</div><ul class="ui-helper-clearfix listings' + listingTypeID + '" style="display: none;"></ul></div>'
                    jQuery(listingsGroup).prependTo('#jr_compareListingsInner').hide().slideDown('slow', function(){
                        jQuery('div.listingTypeCompare ul.listings'+listingTypeID).css('height','30px').slideDown('slow', function(){
                            jQuery(this).css('height','auto');
                        });
                    });                        
                }                
                jQuery(listingData).appendTo('.listingTypeCompare ul.listings' + listingTypeID).hide().slideDown('slow');
                jQuery.extendedjsoncookie( "setCookieVariable","jrCompare"+listingTypeID, "listing"+listingID, {'title': listingTitle, 'thumbnail' : listingThumbSrc } );
                jQuery.extendedjsoncookie( "setCookieVariable","jrCompareIDs", "jrCompare"+listingTypeID, listingTypeTitle );
            }
            else {
                listing = 'listing'+listingID;    
                if(jQuery('li.'+listing).siblings().length > 0) {
                    jQuery('li.'+listing).slideUp('slow', function(){
                        jQuery(this).remove();
                    });
                } 
                else {
                    jQuery('li.'+listing).parent().parent().slideUp('slow', function(){
                        jQuery(this).remove();
                        jreviewsCompare.removeListingTypeFromCookie('jrCompare'+listingTypeID);
                        if(jQuery('div.listingTypeCompare').length < 1){
                            jQuery('div#jr_compareListings').slideUp('slow');
                        }                            
                    });
                    jQuery('div.listingTypeCompare ul:eq(1)').slideDown('slow');                        
                }
                jreviewsCompare.removeListingFromCookie(listing, listingTypeID);
            }    
        });           
    },
    
    // returns cookie names and listing type names
    getCookies: function(){        
        var cookieListingTypes = jQuery.extendedjsoncookie( "getCookieValueDecoded","jrCompareIDs");
        if (cookieListingTypes) {            
            var listingTypes = jQuery.evalJSON(cookieListingTypes);                        
            return listingTypes;
        }
        else { return false; }    
    },            
    
    // returns all listings from a cookie
    getListingsFromCookie: function(cookieName) {
        var cookie = jQuery.extendedjsoncookie( "getCookieValueDecoded", cookieName);
        if (cookie) {                
            var cookieListings = jQuery.evalJSON(cookie);                
            return cookieListings;
        } 
        else { return false; }        
    },
    
    // removes a listing from cookie
    removeListingFromCookie: function (listing, listingType) {
        var cookieValue = jQuery.extendedjsoncookie( "getCookieValueDecoded","jrCompare"+listingType);            
        compareListings = jQuery.evalJSON(cookieValue);
        delete compareListings[listing];
        jQuery.extendedjsoncookie( "removeCookie","jrCompare"+listingType);
        if (compareListings) {
            jQuery.each(compareListings, function(key, value) {
                jQuery.extendedjsoncookie("setCookieVariable","jrCompare"+listingType, key, value); 
            });
        }        
    },
    
    // removes listing type from cookie
    removeListingTypeFromCookie: function(listingType) {
        var cookieValue = jQuery.extendedjsoncookie( "getCookieValueDecoded","jrCompareIDs");
        compareListingTypes = jQuery.evalJSON(cookieValue);
        delete compareListingTypes[listingType];
        jQuery.extendedjsoncookie( "removeCookie","jrCompareIDs");
        if (compareListingTypes) {
            jQuery.each(compareListingTypes, function(key, value) {
                jQuery.extendedjsoncookie( "setCookieVariable","jrCompareIDs", key, value); 
            });
        }
    }        
    
}
