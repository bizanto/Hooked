<?php
  /**
 * GeoMaps Addon for JReviews
 * Copyright (C) 2006-2009 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );
class ModuleGeomapsController extends MyController {
		
	var $uses = array('user','menu','field','criteria');
	var $helpers = array('routes','libraries','html','text','jreviews','rating','thumbnail','custom_fields');
	var $components = array('access','config','everywhere');

	var $autoRender = false; //Output is returned
	var $autoLayout = false;

    var $jr_lat;
    var $jr_lon;
		
    // Need to return object by reference for PHP4
    function &getPluginModel() {
        return $this->Listing;
    }  
            
	function beforeFilter() 
    {      
        # Call beforeFilter of MyController parent class
		parent::beforeFilter();
        $this->jr_lat = Sanitize::getString($this->Config,'geomaps.latitude');
        $this->jr_lon = Sanitize::getString($this->Config,'geomaps.longitude');
	}
	           
	function listings() 
    {
    	// Initialize variables		
		$id = Sanitize::getInt($this->params,'id');		
		$option = Sanitize::getString($this->params,'option');		
		$view = Sanitize::getString($this->params,'view');
		$task = Sanitize::getString($this->params,'task');
		$menu_id = Sanitize::getString($this->params,'Itemid');		

		// Read params
		$cat_id = '';
		$criteria_ids = '';
        $detail_view = 1;
		$dir_id = Sanitize::getString($this->params,'dir');
		$section_id = Sanitize::getString($this->params,'section');
		$cat_id = Sanitize::getString($this->params,'cat');

		$extension = 'com_content';
        $custom_where = null;        
        $custom_fields = array();
        $click2search_auto = false;
        $cache = 0;
        $radius = 0;
        $mode = 0;

		if(isset($this->params['module']))
		{
			// Read module parameters
            $click2search_auto = Sanitize::getBool($this->params['module'],'click2search_auto',false);
            $custom_where = Sanitize::getString($this->params['module'],'custom_where');
            $filter = Sanitize::getString($this->params['module'],'filter');
            $detail_view = Sanitize::getString($this->params['module'],'detail_view',1);
			$dir_id = Sanitize::getString($this->params['module'],'dir');
			$section_id = Sanitize::getString($this->params['module'],'section');
			$cat_id = Sanitize::getString($this->params['module'],'category');
			$listing_id = Sanitize::getString($this->params['module'],'listing');
			$criteria_ids = Sanitize::getString($this->params['module'],'criteria');
            $custom_fields = Sanitize::getString($this->params['module'],'custom_fields','');
            $custom_fields = $custom_fields != '' ? explode(',',str_replace(' ','',$custom_fields)) : array();
            $limit_results = Sanitize::getInt($this->params['module'],'limit_results');
            $mode = Sanitize::getInt($this->params['module'],'mode',0);
            /**
            * 0 - Normal
            * 1 - GeoTargeting
            * 2 - Custom center and zoom
            */
            $radius = Sanitize::getInt($this->params['module'],'radius');
            $cache = $mode == 1 ? 0 : Sanitize::getInt($this->params['module'],'cache_map');
            $custom_lat = Sanitize::getFloat($this->params['module'],'custom_lat');
            $custom_lon = Sanitize::getFloat($this->params['module'],'custom_lon');
            if($mode == 2 && ($custom_lat == 0 || $custom_lon == 0))
            {
                echo __t("You selected the Custom Center mode, but did not specify the coordinates."); return;
            }
        }
              
        $in_detail_view = $id > 0 && ('article' == $view || 'view' == $task) && 'com_content' == $option;        
        $detail_view = $this->params['module']['detail_view'] = $detail_view && $in_detail_view;
          
        # Custom WHERE
        if($custom_where) {
            $conditions[] = $custom_where;
        }

        if($click2search_auto && isset($this->params['tag']))
        {
            $field = 'jr_'.Sanitize::getString($this->params['tag'],'field');
            $value = Sanitize::getString($this->params['tag'],'value');
            $query = "SELECT Field.type FROM #__jreviews_fields AS Field WHERE Field.name = " . $this->quote($field);
            $this->_db->setQuery($query);
            $type = $this->_db->loadResult();
            if(in_array($type,array('select','selectmultiple','checkboxes','radiobuttons')))
            {
                $conditions[] = "Field.{$field} LIKE " . $this->quoteLike('*'.$value.'*');
            } else {
                $conditions[] = "Field.{$field} = " . $this->quote($value);
            }
        }
        
        # Category auto detect
        if(isset($this->params['module']) && Sanitize::getInt($this->params['module'],'cat_auto') && $extension == 'com_content') 
        { // Only works for core articles        
            switch($option) 
            {
                case 'com_jreviews':
                    # Get url params for current controller/action
                    $url = Sanitize::getString($this->passedArgs,'url');
                    $route['url']['url'] = $url;
                    $route = S2Router::parse($route);
//                    $route = $route['url'];
                    $dir_id = Sanitize::getString($route,'dir');
                    $section_id = Sanitize::getString($route,'section');
                    $cat_id = Sanitize::getString($route,'cat');
                    $criteria_ids = Sanitize::getString($route,'criteria');

                    if ($cat_id != '') 
                    {
                        $category_ids = $this->makeParamsUsable($cat_id);
                        $category_ids = explode (",",$category_ids);
                        $this->cleanArray($category_ids);
                        $cat_id = implode (",",$category_ids);                    
        
                    } elseif ($section_id != '') {
                        
                        $cat_id = $this->sectionToCat($section_id);
                        
                    } elseif($criteria_ids != '')    { // check criteriaids {
    
                            $criteriaids_url = $this->makeParamsUsable($criteria_ids);
                            $cat_id = $this->criteriaToCat($criteria_ids);

                    } else { //Discover the params from the menu_id
                                                
                        $params = $this->Menu->getMenuParams($menu_id);

                        $dir_id = Sanitize::getString($params,'dirid');
                        $cat_id = Sanitize::getString($params,'catid');
                        $section_id = Sanitize::getString($params,'sectionid');
                                                                
                    }
                    break;
                case 'com_content':
        
                    if ('article' == $view || 'view' == $task) {
        
                        $sql = "SELECT catid FROM #__content WHERE id = " . $id;
                        $this->_db->setQuery($sql);
                        $cat_id = $this->_db->loadResult();
                        
                    } elseif ($view=="section") {
        
                        $cat_id = $this->sectionToCat($id);
        
                    } elseif ($view=="category") {
        
                        $cat_id = $id;
        
                    }
                    break;
                default:
//                    $cat_id = null; // Catid not detected because the page is neither content nor jreviews
                    break;
            }
        }        
        
        $autodetect = compact('dir_id','section_id','cat_id');
        
        // Check for cached version if cache enabled    
        if($cache)
        {          
            $params = array();
            foreach($this->params AS $key=>$value){
                if((!is_array($value)||$key=='module') && !in_array($key,array('page','limit','order','Itemid'))){
                    $params[$key] = $value;
                }
            }
            $cache_key = array_merge($params,$autodetect,Sanitize::getVar($this->params,'tag',array()));
            
            $json_filename = 'geomaps_'.md5(serialize($cache_key)).'.json';
            $json_data = S2Cache::read($json_filename);
            if($json_data && $json_data!='')
            {
                $this->set('json_data',$json_data);
                S2Cache::write($json_filename,$json_data);            
                return $this->render('modules','geomaps');        
            }
        }
                
        $this->Listing->fields = array(
            'Listing.id AS `Listing.listing_id`',
            'Listing.title AS `Listing.title`',
            'Listing.images AS `Listing.images`',
            'CASE WHEN CHAR_LENGTH(Listing.alias) THEN Listing.alias ELSE "" END AS `Listing.slug`',
            'Category.id AS `Listing.cat_id`',
            'CASE WHEN CHAR_LENGTH(Category.alias) THEN Category.alias ELSE Category.title END AS `Category.slug`',
            'Listing.sectionid AS `Listing.section_id`',
            'JreviewsCategory.criteriaid AS `Criteria.criteria_id`',
            'JreviewsCategory.dirid AS `Directory.dir_id`',
            'Field.featured AS `Listing.featured`',
            'Totals.user_rating AS `Review.user_rating`',
            'Totals.user_rating_count AS `Review.user_rating_count`',
            'Totals.editor_rating AS `Review.editor_rating`', 
            'Totals.editor_rating_count AS `Review.editor_rating_count`',
            "Field.{$this->jr_lat} `Geomaps.lat`",
            "Field.{$this->jr_lon} `Geomaps.lon`",
            'JreviewsCategory.marker_icon AS `Geomaps.icon`'            
        );

        // Geo Targeting OR Custom Center modes
        if($mode == 1 || $mode == 2)
        {
            if($mode == 1)  // Geo Targeting
            {
                $ch = curl_init();
                curl_setopt ($ch, CURLOPT_URL, 'http://www.geoplugin.net/php.gp?ip='.s2GetIpAddress());
                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
                $geoData = unserialize(curl_exec($ch));       
                curl_close($ch);
                 if(!empty($geoData) && $geoData['geoplugin_latitude']!='' && $geoData['geoplugin_longitude']!='')
                 {
                    $center = array('lon'=>$geoData['geoplugin_longitude'],'lat'=>$geoData['geoplugin_latitude']);               
                 }       
                 $this->set('geoLocation',$geoData);
            }
                 
            if($mode == 2)
            {
                $center = array('lon'=>$custom_lon,'lat'=>$custom_lat);
            }
             
            if(!empty($center) && $radius > 0)
            {
                $distanceIn =  Sanitize::getString($this->Config,'geomaps.radius_metric','mi');
                $degreeDistance = $distanceIn == 'mi' ? 69.172 : 40076/360;         
                // Send center coordinates to theme
                $this->set('GeomapsCenter',$center);
                $lat_range = $radius/$degreeDistance; 
                $lon_range = $radius/abs(cos($center['lat']*pi()/180)*$degreeDistance); 
                $min_lat = $center['lat'] - $lat_range;
                $max_lat = $center['lat'] + $lat_range;
                $min_lon = $center['lon'] - $lon_range;
                $max_lon = $center['lon'] + $lon_range;
                $squareArea = "`Field`.{$this->jr_lat} BETWEEN $min_lat AND $max_lat AND `Field`.{$this->jr_lon} BETWEEN $min_lon AND $max_lon";
                $conditions[] = $squareArea;            
            }
        }      
                  
        // Create marker_icons array
        $marker_icons = array();
        $icon_fields = array();
        $field_images = array(); 
        $query = "SELECT DISTINCT marker_icon FROM #__jreviews_categories WHERE marker_icon != ''";
        $this->_db->setQuery($query);
        $icon_rows = $this->_db->loadAssocList();
        foreach($icon_rows AS $icons)
        {
            $icon = (array)json_decode($icons['marker_icon']); 
            if($icon['field']!='') 
            {
                $icon_fields[$icon['field']] = "'".$icon['field']."'";
            }
        }

        if(!empty($icon_fields))
        {
            foreach($icon_fields AS $field_key=>$field)
            {
                $this->Listing->fields[] = "Field.{$field_key} AS `Field.{$field_key}`";
            }
        }   
             
        if(!empty($custom_fields))
        {
            foreach($custom_fields AS $field)
            {
                $this->Listing->fields[] = "Field.{$field} AS `Field.{$field}`";
            }
        }

        $this->Listing->joins = array(
            "LEFT JOIN #__categories AS Category ON Listing.catid = Category.id",
            "LEFT JOIN #__jreviews_listing_totals AS Totals ON Totals.listing_id = Listing.id AND Totals.extension = 'com_content'",
            "LEFT JOIN #__jreviews_content AS `Field` ON Field.contentid = Listing.id",
            "INNER JOIN #__jreviews_categories AS JreviewsCategory ON Listing.catid = JreviewsCategory.id AND JreviewsCategory.`option` = 'com_content'",
            "LEFT JOIN #__jreviews_directories AS Directory ON JreviewsCategory.dirid = Directory.id",
        );
            
        // Don't regroup the results by model name keys to save time
        $this->Listing->primaryKey = false;

		# Set conditionals based on configuration parameters
        if($detail_view){
            $conditions[] = 'Listing.id = ' . $id;            
        } 
        
		if($dir_id) {
			$conditions[] = 'JreviewsCategory.dirid IN (' . $dir_id . ')';
		}

		if($section_id) {	
			$conditions[] = 'Listing.sectionid IN (' . $section_id. ')';
		}

		if($cat_id) {	
			$conditions[] = 'Listing.catid IN (' . $cat_id. ')';
		}
        
        if($listing_id)
        {
            $conditions[] = 'Listing.id IN (' . $listing_id . ')';
        }
        
        if($filter == 'featured' && !$detail_view)
        {                          
            $conditions[] = 'Field.featured = 1';
        }
				
		$conditions[] = "Field.{$this->jr_lat} <> ''";
		$conditions[] = "Field.{$this->jr_lon} <> ''";
        $conditions[] = 'Listing.state = 1';
        
        // Paid Listings - add plan cat id
        isset($this->PaidListings) and $this->PaidListings->applyBeforeFindListingChanges($this->Listing); 
		
        $listings = $this->Listing->findAll(array('conditions'=>$conditions,'limit'=>$limit_results),array());
        $custom_fields = array_filter(array_merge($custom_fields,array_keys($icon_fields)));
        $fields = $this->Field->getFields($custom_fields);
        
        $json_data = $this->Geomaps->makeJsonObject($listings,$fields,$this->params['module']);

        $this->set('json_data',$json_data);
        if($cache)
        {
            S2Cache::write($json_filename,$json_data);   
        }            
        return $this->render('modules','geomaps');
 	}
	
	function criteriaToCat($criteriaid) {
		$query = "SELECT DISTINCT id FROM #__jreviews_categories"
		." \n WHERE criteriaid IN ($criteriaid) "
		." \n AND `option` = 'com_content'";
		$this->_db->setQuery($query);
		$catids = implode(",",$this->_db->loadResultArray());
		return $catids;
	}
		
	function sectionToCat($sectionid) {
		$sectionid = $this->makeParamsUsable($sectionid);
		$sql = "SELECT DISTINCT category.id FROM #__categories AS category"
		. "\n INNER JOIN #__jreviews_categories AS jr_category ON category.id = jr_category.id AND jr_category.option = 'com_content'"
		. "\n WHERE category.section IN ($sectionid)"
		;

		$this->_db->setQuery($sql);
		$catids = implode(",",$this->_db->loadResultArray());
		return $catids;
	}

	function cleanArray(&$array) {
		// Remove empty or nonpositive values from array
		foreach ($array as $index => $value) {
		   if (empty($value) || $value < 1 || !is_numeric($value)) unset($array[$index]);
		}
	}

	function makeParamsUsable($param) {
		$urlSeparator = "_";
		return str_replace($urlSeparator,",",urldecode($param));
	}	
	
}
