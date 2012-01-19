/**
 * GeoMaps Addon for JReviews
 * Copyright (C) 2006-2009 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/
 
 jQuery(document).unload(function(){GUnload();});    
 if(typeof GMap2 != 'function' && typeof GeomapsGoogleApi == 'string') { 
     jQuery.ajax({
       type: "GET",
       url: GeomapsGoogleApi,
       success: function(){},
       dataType: "script",
       cache: true
    });
 }    
 /* jr_lat,jr_lon need to be predefined as global vars */
 
// Mapping function for results, module and detail pages   
function GeomapsDisplayMap(mapCanvas,options) 
{
    // private members
    var data_ = {'infowindow':'','count':0,'icons':{},'payload':{}};
    var config_ = [];
    var icons_ = [];
    var gicons_ = [];
    var defaultIcon = null;
    var gdir_ = null; 
    var map = null;    
    var mapCanvas_ = mapCanvas;
    var mapTooltip_ = mapCanvas_+'Tooltip';
    var mapTypes_ = {'G_NORMAL_MAP':G_NORMAL_MAP,'G_HYBRID_MAP':G_HYBRID_MAP,'G_SATELLITE_MAP':G_SATELLITE_MAP,'G_PHYSICAL_MAP':G_PHYSICAL_MAP};
    var markers_ = [];
    var markerClusterer = null;
    var bounds = new GLatLngBounds();
    var clustering_ = false;
    var clusteringMinMarkers_ = 250; // number of markers to trigger clustering when clustering enabled
    var directions_ = false;
    var panoClient_ = null;
    var myPano_ = null;
    var streetViewStatus_ = false;
    var streetViewOverlay_ = false;
    var lastLatLng_ = null;
    var markerClickTracker_ = false;
               
    if (typeof options === "object" && options !== null) 
    {
        if (typeof options.clustering === "boolean") {
          clustering_ = options.clustering;
        }
        if (typeof options.clusteringMinMarkers === "number" && options.clusteringMinMarkers > 0) {
          clusteringMinMarkers_ = options.clusteringMinMarkers;
        }
        if (typeof options.directions === "boolean") {
          directions_ = options.directions;
        }               
        if (typeof options.streetview === "boolean") {
          streetViewStatus_ = options.streetview;
        }               
        if (typeof options.streetview_overlay === "boolean") {
          streetViewOverlay_ = options.streetview_overlay;
        }               
    }

    icons_['default'] = {
        'type':'custom', 
        'url':'http://chart.apis.google.com/chart?chst=d_map_spin&chld=0.6|0|FE766A|12|_|',
        'size':[20,34]
    };
    icons_['default_hover'] = {
        'type':'custom', 
        'url':'http://chart.apis.google.com/chart?chst=d_map_spin&chld=0.6|0|FDFF0F|12|_|',
        'size':[20,34]
    };
    icons_['default_featured'] = {
        'type':'custom',
        'url':'http://chart.apis.google.com/chart?chst=d_map_spin&chld=0.6|0|5F8AFF|12|_|',
        'size':[20,34]
    };
    icons_['numbered'] = {
        'type':'numbered', 
        'url':'http://chart.apis.google.com/chart?chst=d_map_spin&chld=0.6|0|FE766A|12|_|{index}',
        'size':[20,34]
    };
    icons_['numbered_hover'] = {
        'type':'numbered', 
        'url':'http://chart.apis.google.com/chart?chst=d_map_spin&chld=0.6|0|FDFF0F|12|_|{index}',
        'size':[20,34]
    };
    icons_['numbered_featured'] = {
        'type':'numbered',
        'url':'http://chart.apis.google.com/chart?chst=d_map_spin&chld=0.6|0|5F8AFF|12|_|{index}',
        'size':[20,34]
    };    

    this.init = function()
    {                   
        if(GBrowserIsCompatible()) 
        {        
            if(map == null)
            {
                map = new GMap2(document.getElementById(mapCanvas_));
                map.setUI(setUIOptions());
                map.setMapType(mapTypes_[data_.mapUI.maptypes.def]);
    	        
                // Close non-google infowindow
                GEvent.addListener(map, "dragstart", function() { 
                    closeTooltip();
                });
                GEvent.addListener(map, "movestart", function() { 
                    closeTooltip();
                });
                GEvent.addListener(map, "moveend", function() { 
                    closeTooltip();
                });
                            
                // Make the copyright wrap instead of overflowing outside the map div                          
                GEvent.addListener(map, "tilesloaded", function() { 
                    jQuery('.gmnoprint').next('div').css({'white-space':'normal','font-size':'9px'});                 
                });                
    
                // Load custom icons
                for (var i in data_.icons) {
                    icons_[i] = data_.icons[i];
                }                        
    	            
                // Auto-disable clustering based on min clustering markers
                if(clustering_ == true && data_.count > clusteringMinMarkers_) 
                {                                     
                    for (var i in data_.payload) {                   
//                        if(i.match(/^id[0-9]+$/)!=null){
                            var latlng = new GLatLng(data_.payload[i].lat, data_.payload[i].lon);
                            bounds.extend(latlng); 
                            markers_.push(createMarker(latlng,data_.payload[i])); // Must use markers_.push or cluseting doesn't work otherwise
//                        }
                    }
                    this.centerAndZoomOnBounds();
                    refreshMap(markers_);
                } else {        
                    for (var i in data_.payload) {
//                        if(i.match(/^id[0-9]+$/)!=null){
                            var latlng = new GLatLng(data_.payload[i].lat, data_.payload[i].lon);
                            bounds.extend(latlng); 
                            markers_[i] = createMarker(latlng,data_.payload[i]);
                            map.addOverlay(markers_[i]);
//                        }
                    }
                    this.centerAndZoomOnBounds();
                }   
                
                // Initialize directions
                if(directions_ == true)
                {                      
                    gdir_ = new GDirections(map, document.getElementById(mapCanvas_+'_results'));
                    GEvent.addListener(gdir_, "error", handleDirectionErrors);
                }
                
                panoClient_ = new GStreetviewClient();  
                myPano_ = new GStreetviewPanorama(document.getElementById(mapCanvas+'_streetview'));
                GEvent.addListener(myPano_, "error", handleNoFlash);     
                if(streetViewStatus_==true)
                {
                    // Trigger streetview on current location                 
                    panoClient_.getNearestPanorama(map.getCenter(), showPanoData);

                    // Enables streetview changes on map clicks, not just marker clicks
                    GEvent.addListener(map, "click", function(overlay,latlng) {
                        if(markerClickTracker_ == false)
                        {
                            panoClient_.getNearestPanorama(latlng, showPanoData);
                        }                              
                        markerClickTracker_ = false;
                    });  
                }  
                if(streetViewOverlay_==true)
                {
                    svOverlay = new GStreetviewOverlay();
                    map.addOverlay(svOverlay);                
                } 

            } else {
                map.checkResize();
                this.centerAndZoomOnBounds(); 
            }                    
        }      
    } 
    
    this.addMarker = function(marker) { data_.payload.push(marker); }   

    this.setCount = function(count) { data_.count = count; }
    
    this.setData = function(data) 
    { 
        data_ = data; 
        if(undefined==data_.infowindow)  data_.infowindow = 'google';
    }

    this.getData = function() { return data_; }
    
    this.addIcon = function(icon) { icons_.push(icon); }   
    
    this.getMap = function() { return map; }
    
    this.findCenter = function()
    {
        var center_lat = (bounds.getNorthEast().lat() + bounds.getSouthWest().lat()) / 2.0;
        var center_lng = (bounds.getNorthEast().lng() + bounds.getSouthWest().lng()) / 2.0;
        if(bounds.getNorthEast().lng() < bounds.getSouthWest().lng()){
            center_lng += 180;
        } 
        return new GLatLng(center_lat,center_lng)
    }   
            
    this.panToCenter = function()
    {
        map.panTo(this.findCenter());        
    }
        
    this.centerAndZoomOnBounds = function()
    {       
        var zoom;                    
        if(data_.mapUI.zoom.start != '')
        {            
            zoom = data_.mapUI.zoom.start;
        } else {                   
            zoom = map.getBoundsZoomLevel(bounds);
        }
        map.setCenter(this.findCenter(), zoom); 
    }
    
    this.toggleSizeMap = function(object, width)
    {
        var streetView = jQuery('#'+mapCanvas_+'_streetview');
        if(jQuery('#'+mapCanvas).width() <= width)
        {
            jQuery('#gm_resizeL').hide('fast',function(){jQuery(this).css('display','none');});
            jQuery('#gm_resizeS').show('fast',function(){jQuery(this).css('display','');});
            jQuery('#'+mapCanvas_+'_above').animate({width: 600},"slow");
            if(streetView.css('display')=='none'){
                streetView.css('width',600)                
            } else if(streetView.css('display')!='') {
                streetView.animate({width: 600},"slow",function(){ if(lastLatLng_!=null) panoClient_.getNearestPanorama(lastLatLng_, showPanoData);});         
            } 
            jQuery('#'+mapCanvas_).animate({width: 600, height: 500},"slow",function(){object.init();});         
        } else {
            jQuery('#gm_resizeS').hide('fast',function(){jQuery(this).css('display','none');});
            jQuery('#gm_resizeL').show('fast',function(){jQuery(this).css('display','');});           
            jQuery('#'+mapCanvas_+'_above').animate({width: width},"slow");
            if(streetView.css('display')=='none'){
                streetView.css('width',width)                
            } else if(streetView.css('display')!='') {
                streetView.animate({width: width},"slow",function(){if(lastLatLng_!=null) panoClient_.getNearestPanorama(lastLatLng_, showPanoData);});             
            }
            jQuery('#'+mapCanvas_).animate({width: width, height: width},"slow",function(){object.init();});         
        }        
    }
    
    function setUIOptions()
    {
        var customUI = map.getDefaultUI();
        customUI.maptypes.normal = data_.mapUI.maptypes.map;
        customUI.maptypes.hybrid = data_.mapUI.maptypes.hybrid;
        customUI.maptypes.satellite = data_.mapUI.maptypes.satellite;
        customUI.maptypes.physical = data_.mapUI.maptypes.terrain;
        customUI.zoom.scrollwheel = data_.mapUI.zoom.scrollwheel;
        customUI.zoom.doubleclick = data_.mapUI.zoom.doubleclick;
        customUI.controls.scalecontrol = data_.mapUI.controls.scalecontrol;
        customUI.controls.largemapcontrol3d = data_.mapUI.controls.largemapcontrol3d;
        customUI.controls.smallzoomcontrol3d = data_.mapUI.controls.smallzoomcontrol3d;
        customUI.controls.maptypecontrol = data_.mapUI.controls.maptypecontrol;
        customUI.controls.menumaptypecontrol = data_.mapUI.controls.menumaptypecontrol;
        return customUI;        
    }
          
    function refreshMap() 
    {
        if (markerClusterer != null) {
          markerClusterer.clearMarkers();
        }
        var zoom = 15;
        var size = 30; // Grid size of a cluster, the higher the quicker 
        //var style = document.getElementById("style").value;
        zoom = zoom == -1 ? null : zoom;
        size = size == -1 ? null : size;
        //style = style == "-1" ? null: parseInt(style, 10);
        //markerClusterer = new MarkerClusterer(map, markers, {maxZoom: zoom, gridSize: size, styles: styles[style]});
        markerClusterer = new MarkerClusterer(map, markers_, {maxZoom: zoom, gridSize: size});
    }
           
    function createMarker(latlng,markerData)
    {         
        if(markerData.icon == '' || markerData.icon == undefined) markerData.icon = 'default';
        if(markerData.featured == 1 && undefined!=icons_[markerData.icon+'_featured'])
        {                              
            markerData.icon = markerData.icon+'_featured';
        }
        var icon = makeIcon(markerData.icon,markerData.index);
        var marker = new GMarker(latlng, {icon: icon, title: markerData.title});
        marker.icon_name = markerData.icon;
        marker.id = markerData.id;
        marker.data = markerData;
        marker.data.latlng = latlng;
        GEvent.addListener(marker, "click", function() {     
            showTooltip(marker);   
            if(streetViewStatus_ == true)
            {                  
                panoClient_.getNearestPanorama(latlng, showPanoData);
                lastLatLng_ = latlng;
                markerClickTracker_ = true;
            }
        });
        GEvent.addListener(marker, "infowindowbeforeclose", function() {
            markerClickTracker_ = true;
        });
        GEvent.addListener(marker, "mouseover", function() {
            switchMarkerImage(marker,'_hover');
            return false;
        });
        GEvent.addListener(marker, "mouseout", function() { 
            switchMarkerImage(marker,'');
        });
        
        return marker;            
    }  
    
    function makeIcon(name,index)
    {                      
        if(null!=gicons_[name] && name != 'numbered') return gicons_[name];
        if(undefined==icons_[name]) name = 'default';
        var icon;
        switch(icons_[name].type)
        {
            case 'custom': icon = makeCustomIcon(icons_[name]); break;
            case 'numbered': icon = makeNumberedIcon(icons_[name],index); break;
            case 'default': icon = makeDefaultIcon(icons_[name]); break;
            default: icon = makeDefaultIcon(icons_[name]); break;
        }
        gicons_[name] = icon;
        return icon;
    }
    
    function makeCustomIcon(iconData)
    {
        var customIcon = new GIcon();
        customIcon.image = iconData.url;
        customIcon.iconSize = new GSize(iconData.size[0],iconData.size[1]);
        customIcon.iconAnchor = new GPoint(9,34);
        customIcon.infoWindowAnchor = new GPoint(9,2);
        return customIcon;       
    }
            
    function makeDefaultIcon(iconData)
    {   
        var defaultIcon = new GIcon(G_DEFAULT_ICON);
        defaultIcon.shadow = "http://www.google.com/mapfiles/shadow50.png";
        defaultIcon.iconSize = new GSize(iconData.size[0], iconData.size[1]);
        defaultIcon.shadowSize = new GSize(37, 34);
        defaultIcon.iconAnchor = new GPoint(9, 34);
        defaultIcon.infoWindowAnchor = new GPoint(9, 2);           
        return new GIcon(defaultIcon, iconData.url);
    }
    
    function makeNumberedIcon(iconData,index)
    {          
        if(null == defaultIcon)
        {
            defaultIcon = new GIcon(G_DEFAULT_ICON);
            defaultIcon.shadow = "http://www.google.com/mapfiles/shadow50.png";
            defaultIcon.iconSize = new GSize(20, 34);
            defaultIcon.shadowSize = new GSize(37, 34);
            defaultIcon.iconAnchor = new GPoint(9, 34);
            defaultIcon.infoWindowAnchor = new GPoint(9, 2);           
        }    
        if(index!='')
        {
            return new GIcon(defaultIcon, iconData.url.replace('{index}',(0+index)));
        } else {
            return new GIcon(defaultIcon, iconData.url.replace('{index}',''));
        }
    }    
          
    function switchMarkerImage(marker,status)
    {                        
        if(undefined!=marker && icons_[marker.icon_name+status])
        {
            marker.setImage(icons_[marker.icon_name+status].url.replace('{index}',marker.data.index));        
        }
    } 
    
    this.switchMarkerImageById = function(id,status)
    {
        switchMarkerImage(markers_['id'+id],status); 
    }       

    function renderTooltip(data,useTabs)
    {                     
        // Standard fields
        var roundDecimals = 1;
        var infoWindowContainer = jQuery('#gm_infowindowContainer').clone();
        var infoWindow = infoWindowContainer.find('.gm_infowindow');
        if(data_.mapUI.title.trim == true && data_.mapUI.title.trimchars > 0)
        {
            data.title = truncate(data.title,data_.mapUI.title.trimchars);
        }
        infoWindow.find('.gm-title').html(data.title);
        infoWindow.find('.gm-title').attr('href',data.url);
        if(false!=data.image){
            infoWindow.find('.gm-image').attr('src',data.image);
        } else {
            infoWindow.find('.gm_image').css('display','none');
        }
        infoWindow.find('.gm-user-rating-star').css('width',(data.user_rating/data.rating_scale)*100+'%');
        infoWindow.find('.gm-user-rating-value').html(Math.round(0+(data.user_rating)*Math.pow(10,roundDecimals))/Math.pow(10,roundDecimals));
        infoWindow.find('.gm-user-rating-count').html(parseInt(0+data.user_rating_count));
        infoWindow.find('.gm-editor-rating-star').css('width',(data.editor_rating/data.rating_scale)*100+'%');
        infoWindow.find('.gm-editor-rating-value').html(Math.round(0+(data.editor_rating)*Math.pow(10,roundDecimals))/Math.pow(10,roundDecimals));

        for(var i in data.field)
        {                                  
            infoWindow.find('.gm-'+i).html(data.field[i]);
        }
        if(useTabs==false)
        {
            return infoWindowContainer.html();      
        } else {
            var tabs = [];
            infoWindowContainer.find('div.gm_tab').each(function()
            {            
                tabs.push(new GInfoWindowTab(jQuery(this).attr('title'),jQuery(this).html()));
            });
            return tabs;
        }
    }
    
    this.showTooltipById = function(id)
    {                       
        showTooltip(markers_['id'+id]);        
    }        
    
    this.closeTooltip = function()
    {
        closeTooltip();    
    }   
    
    function showTooltip(marker)
    {
        if(undefined!=marker)
        {          
            switch(data_.infowindow)
            {
                case 'google': 
                    marker.openInfoWindowHtml(renderTooltip(marker.data,false));
                break;
                case 'google_tabs': 
                    marker.openInfoWindowTabsHtml(renderTooltip(marker.data,true));
                break;
                default:
                    if(jQuery('#'+mapTooltip_).length == 0)
                    {
                        jQuery("#"+mapCanvas_).append('<div id="'+mapTooltip_+'" class="gm_mapInfowindow"></div>'); 
                    }
                    var tooltip = jQuery('#'+mapTooltip_);
                    tooltip.html('');
                    tooltip.marker = marker;
                    closeTooltip();
                    tooltip.html(renderTooltip(marker.data,false));
                      // Attach close onclick event
                    jQuery('.gm_infowindow').find('.gm-close-tooltip').unbind().click(function(){closeTooltip();});
                    positionTooltip(tooltip);
                break;
            }
        }
   }    
        
    function positionTooltip(tooltip)
    {
        var mapBounds = map.getBounds();
        if ( !mapBounds.contains(tooltip.marker.getPoint()) ) {
            map.setCenter(tooltip.marker.getPoint());
        }
        // Get relative positioning for tooltip
        var pointDivPixel = map.fromLatLngToContainerPixel(tooltip.marker.getPoint());
        tooltip.css('left',parseInt(pointDivPixel.x-367+parseInt(data_.mapUI.anchor.x))+'px');
        tooltip.css('top',parseInt(pointDivPixel.y-102+parseInt(data_.mapUI.anchor.y))+'px');                
        tooltip.fadeIn('slow');
    }
    
    function closeTooltip(){
        jQuery('#'+mapTooltip_).css('display','none');
        return false;
    }
   
    function truncate(text,len)
    {        
        if (text.length > len) 
        {
            var copy;
            text = copy = text.substring(0, len);
            text = text.replace(/\w+$/, '');
            if(text == '') text = copy;
            text += '...';
        }
        return text;    
    }
    
    /******************************
    * Street view functions
    ******************************/
    this.setStreetView = function(status)
    {          
        streetViewStatus_ = status;
    }
    
    this.getStreetViewById = function(id)
    {
        if(streetViewStatus_ == true)
        {
            var streetView = jQuery('#'+mapCanvas_+'_streetview');
            if(streetView.css('display')!='block') streetView.slideDown();
            panoClient_.getNearestPanorama(markers_['id'+id].data.latlng, showPanoData);
            lastLatLng_ = markers_['id'+id].data.latlng;
        }        
    }

    function showPanoData(panoData) {
      if (panoData.code != 200) {
        jQuery('#'+mapCanvas_+'_streetview').html('<div id="gm_streetview_msg" style="margin:10px;">Street view not available: ' + panoData.code + '</div>');
        return;
      } else {
          jQuery('#gm_streetview_msg').remove();
      }
      myPano_.setLocationAndPOV(panoData.location.latlng);
    }
    
    function handleNoFlash(errorCode) {
      if (errorCode == 603) {
        jQuery('#'+mapCanvas_+'_streetview').html('<div id="gm_streetview_msg" style="margin:10px;">Flash doesn\'t appear to be supported by your browser.</div>');
        return;
      }
    }     
    
    /******************************
    * Get direction functions
    ******************************/
    function handleDirectionErrors()
    {
       if (gdir_.getStatus().code == G_GEO_UNKNOWN_ADDRESS)
         showMessage("No corresponding geographic location could be found for one of the specified addresses. This may be due to the fact that the address is relatively new, or it may be incorrect.\nError code: " + gdir_.getStatus().code);
       else if (gdir_.getStatus().code == G_GEO_SERVER_ERROR)
         showMessage("A geocoding or directions request could not be successfully processed, yet the exact reason for the failure is not known.\n Error code: " + gdir_.getStatus().code);
       else if (gdir_.getStatus().code == G_GEO_MISSING_QUERY)
         showMessage("The HTTP q parameter was either missing or had no value. This means that no query was specified in the input.\n Error code: " + gdir_.getStatus().code);
       else if (gdir_.getStatus().code == G_GEO_BAD_KEY)
         showMessage("The given key is either invalid or does not match the domain for which it was given. \n Error code: " + gdir_.getStatus().code);
       else if (gdir_.getStatus().code == G_GEO_BAD_REQUEST)
         showMessage("A directions request could not be successfully parsed.\n Error code: " + gdir_.getStatus().code);
       else showMessage("The was an error processing the request.");
    }
    
    this.getDirections = function(fromAddress, toAddress, locale) 
    {
      hideErrorMessage();
      var results = jQuery('#'+mapCanvas_+'_results');
      if(results.css('display') == 'none' && jQuery('#'+mapCanvas_).css('width') == '100%') // Change map width and bring direction results into view
      {
          var directionsWidth = results.width();
          var mapWidth = parseInt(jQuery('#'+mapCanvas_).width() - directionsWidth - 20);
          jQuery('#'+mapCanvas_).css('width',mapWidth+'px');
      }                                              
      jQuery('#'+mapCanvas_+'_results').show();
      gdir_.load("from: " + fromAddress + " to: " + toAddress,  {"locale":locale, "travelMode": parseInt(jQuery('#gm_direction_travelmode').val())}); 
    }

    this.swapInputs = function()
    {
        var tmp = jQuery('#from_point').val();
        jQuery('#from_point').val(jQuery('#to_point').val());
        jQuery('#to_point').val(tmp);        
    }        
    function showMessage(text)
    {
        jQuery('#'+mapCanvas_+'_results').html(text).fadeIn();
    }
    function hideErrorMessage()
    {
        jQuery('#'+mapCanvas_+'_results').html('');
    }
    this.setCenterAndZoom = function(lat,lon,zoom)
    {             
        map.setCenter(new GLatLng(lat,lon),zoom);
    }
}     
      
geomaps = 
{
    map: null,
    geocoder: null,
    marker: null,
    coordinates: ['',''],
    initializeMap: function(lat,lon)
    {
        geomaps.map = new GMap2(document.getElementById('gm_mapPopupCanvas'));
        geomaps.map.setCenter(new GLatLng(lat, lon), 15); 
        geomaps.map.setUIToDefault();
        geomaps.marker = new GMarker(new GLatLng(lat,lon), {draggable: true});
        GEvent.addListener(geomaps.marker, "dragstart", function() {
          geomaps.map.closeInfoWindow();
          });
        GEvent.addListener(geomaps.marker, "dragend", function() { 
          jQuery('#'+jr_lat).val(geomaps.marker.getPoint().lat());
          jQuery('#'+jr_lon).val(geomaps.marker.getPoint().lng());
          });
        geomaps.map.addOverlay(geomaps.marker);        
    },
    getAddress: function()
    {
        var address = '';
        jQuery.each(geoAddressObj, function(index, item){        
            if(null!=jQuery('#'+item).val()) 
            {
                address = address + ' ' + jQuery('#'+item).val();
            }
        });   
        if(jQuery('#'+geoAddressObj.country).length == 0 && undefined!=jr_country_def)
        {
            address = address + ' ' + jr_country_def;
        } 
        return address;
    },     
    showAddressOnMap: function()
    {                                 
        var address = geomaps.getAddress();
        if(null==geomaps.map)
        {
             geomaps.initializeMap(0,0);
        }           
        geomaps.geocoder = new GClientGeocoder();
        geomaps.geocoder.getLatLng(
            address,
            function(latlng) 
            {
              if (null==latlng) {
                jQuery('#gm_popupMsg').css({'color':'red'}).html('Address could not be geocoded. Modify the address and click on the Geocode Address button to try again.').show();
              } else { 
                geomaps.map.setCenter(latlng,15);                   
                geomaps.marker.setLatLng(latlng); 
                geomaps.marker.openInfoWindowHtml(address);
                jQuery('#'+jr_lat).val(geomaps.marker.getPoint().lat());
                jQuery('#'+jr_lon).val(geomaps.marker.getPoint().lng());                
              }
            }
        );
    }, 
    /* Popup includes address and lat/lon fields */
    mapPopupFull: function(controller,options,title,lat,lon)
    {
        var defaults = {
            'listing_id': null,
            'criteria_id': null
        }
        var params = jQuery.extend(defaults, options); 
        geomaps.dialog(
            controller,
            '_geocodePopup',
            '&task=single&listing_id='+params.listing_id+'&criteria_id='+params.criteria_id,
            {'title':title,'open':function(event,ui){
                if(lat != '' && lon != '' && lat!=0 && lon!=0) {
                    geomaps.initializeMap(lat,lon);    
                }else{
                    geomaps.showAddressOnMap();
                }
            }
        });
    },
    /* Popup only allows marker dragging for fine-tuning of location */
    mapPopupSimple: function()
    {              
        jQuery('#form_container').append('<div style="display:none;" id="gmDelayDiv"></div>'); // Delay trick
        // Clear current lon, lat
        
        if(jQuery('#'+jr_lat).length > 0)
        {
            // something
        } else {
            jQuery('#jr_newFields').after(
                '<input type="hidden" id="'+jr_lat+'" name="data[Field][Listing]['+jr_lat+']" >'+
                '<input type="hidden" id="'+jr_lon+'" name="data[Field][Listing]['+jr_lon+']" >'
            );
        }

        var lat =  jQuery('#'+jr_lat).val();
        var lon =  jQuery('#'+jr_lon).val();
        if(lat == '' && lon == '')
        {   
            geomaps.geocodeAddress();
        } else {
            geomaps.mapPopupSimpleDialog(lat,lon);            
        }
    },
    mapPopupSimpleDialog: function(lat,lon)
    {
        var dialog_id = 'jr_formDialog';
        var settings = {
            'modal': false,/* otherwise the marker cannot be dragged with jQuery UI 1.8.5*/
            'autoOpen': true,
            'buttons': function() {},
            'width': '600px',
            'height': 'auto',
            'title':'',
            'open': function() {geomaps.initializeMap(lat,lon);}
        };        
        
        jQuery('.dialog').dialog('destroy').remove(); 
        jQuery("body").append('<div id="'+dialog_id+'" class="dialog">'
            +'<div class="ui-widget" style="font-size:11px;margin-bottom: 5px;">'
            +'<div style="padding: 0pt 0.7em;" class="ui-state-highlight ui-corner-all">'
            +'<p><span style="float:left;margin-right:0.3em;" class="ui-icon ui-icon-info"></span><strong>Drag the marker to fine-tune the geographic location on the map.</strong></p>'
            +'</div></div><div id="gm_mapPopupCanvas" style="width: 580px; height: 300px"></div></div>');
        jQuery('#'+dialog_id).dialog(settings);        
    },
    geocodeAddress: function()
    {
        // geocode first        
        var address = geomaps.getAddress();   
        geomaps.geocoder = new GClientGeocoder();
        geomaps.geocoder.getLatLng(
            address,
            function(latlng) 
            {
              if (null==latlng) {
                alert('Address could not be geocoded. Modify the address and click on the Geocode button to try again.');
              } else { 
                var point = String(latlng);   
                center = point.replace('(','').replace(')','').replace(' ','');
                geomaps.coordinates = center.split(',');
                jQuery('#'+jr_lat).val(geomaps.coordinates[0]);
                jQuery('#'+jr_lon).val(geomaps.coordinates[1]); 
                geomaps.mapPopupSimpleDialog(geomaps.coordinates[0],geomaps.coordinates[1]);
              }
            }
        );
    },
    clearLatLng: function()
    {
        jQuery('#'+jr_lat).val('');
        jQuery('#'+jr_lon).val('');        
    },
    dialog: function(controller,action,params,options)
    {
        var dialog_id = 'jr_formDialog';
        
        var defaults = {
            'modal': false,/* otherwise the marker cannot be dragged with jQuery UI 1.8.5*/
            'autoOpen': true,
            'buttons': function() {},
            'width': '600px',
            'height': 'auto'
        };
        
        var settings = jQuery.extend(defaults, options);       
                        
        jQuery('.dialog').dialog('destroy').remove();    
        jQuery("body").append('<div id="'+dialog_id+'" class="dialog"></div>');

        jQuery('#'+dialog_id).load
        (
            s2AjaxUri+'&url='+controller+'/'+action+'&'+params,
            function(){
                jQuery(this).dialog(settings);                            
            }
        );          
    }
}; 
