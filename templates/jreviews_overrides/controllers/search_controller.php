<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class SearchController extends MyController {
	
	var $uses = array('menu','user','field','criteria');
	
	var $helpers = array('assets','html','libraries','custom_fields','form','time');

	var $components = array('config','access','zipcode_search');
	
	var $autoRender = false; //Output is returned
	
	var $autoLayout = true;
	
	function beforeFilter() {
		
		# Call beforeFilter of MyController parent class
		parent::beforeFilter();
		
	}	

	function index($params) {
				
		$this->action = 'advanced'; // Set view name
		
		$this->autoRender = true;

		$criteria_id = Sanitize::getInt($this->params,'criteria');
	
		$dateFields = array();
	
		// Check if the criteria list should be limited to specified ids
		$separator = "_"; // For url specified criterias

		$used_criterias = array();
		
		if($criteria_id > 0) {

			$criterias = array($criteria_id);
		
		} else {

			if(isset($criteria_id) && is_array($criteria_id)) 
			{
				$criterias_tmp = explode("_",urldecode($criteriaid));
			
				for ($i=0;$i<count($criterias_tmp);$i++) 
				{
					if ( (int) $criterias_tmp[$i] > 0) {
						$used_criterias[$i] = $criterias_tmp[$i];
					}
				}
			
				if (count($used_criterias)==1) 
				{
					$separator = ","; // For menu param specified criterias
					$criterias_tmp = explode(",",urldecode($criteriaid));
					$used_criterias = array();
					for ($i=0;$i<count($criterias_tmp);$i++) {
						if ( (int) $criterias_tmp[$i] > 0) {
							$used_criterias[$i] = $criterias_tmp[$i];
						}
					}
				}
			}
		
			if (empty($used_criterias))
			{
				// Find the criteria that has been assigned to com_content categories
				$sql = "SELECT DISTINCTROW criteriaid FROM #__jreviews_categories WHERE `option`='com_content'";
				$this->_db->setQuery( $sql );
				
				$used_criterias = $this->_db->loadResultArray();
			}
		
			$used_criterias = implode(',', $used_criterias);
		
			$sql = "SELECT id AS value,title AS text FROM #__jreviews_criteria"
			. "\n WHERE groupid <> '' AND id in ($used_criterias)"
			. "\n ORDER BY title";
			$this->_db->setQuery( $sql );
			
			$criterias = $this->_db->loadObjectList();
			
			if (count($criterias) == 1)
			{	
				$criterias = array($criterias[0]->value);
			}
			
		}

		// With one listing type, there's no need to select it to see the form.
		if (count($criterias) == 1)
		{	
			$criteria_id = $criterias[0];
	
			# Process custom fields
			$search = 1;
			$searchFields = $this->Field->getFieldsArrayNew($criteria_id, 'listing', null, $search);
				
			# Get category list for selected criteria
			$query = "SELECT Category.id as value, CONCAT(Section.title,' - ', Category.title) AS text"
			. "\n FROM #__categories AS Category"
			. "\n INNER JOIN #__jreviews_categories AS JreviewsCategory ON Category.id = JreviewsCategory.id AND JreviewsCategory.option = 'com_content'"
			. "\n LEFT JOIN #__sections AS Section on Category.section = Section.id"
			. ($criteria_id ? "\n WHERE JreviewsCategory.criteriaid = '".$criteria_id."'" : '')
			. "\n ORDER BY Section.title, Category.title"
			;

			$this->_db->setQuery($query);
			
			$categoryList = $this->_db->loadAssocList('value');
						
			$this->set(
				array(
					'Access'=>$this->Access,
					'criteria_id'=>$criteria_id,
					'categoryList'=>$categoryList,
					'searchFields'=>$searchFields
				)
			);						
	
		// If there's more than one criteria show the criteria select list
		} elseif (count($criterias) >= 1) {
	
				$this->set(
					array(
						'Access'=>$this->Access,
						'criterias'=>$criterias
					)
				);				
				
		}

	}
		
	function _process($params) 
    {    
        $urlSeparator = "_";

		$simple_search = Sanitize::getInt($this->data,'simple_search');
		
		$keywords = Sanitize::getVar($this->data,'keywords');
		
		$criteria = isset($this->data['Search']) ? Sanitize::getInt($this->data['Search'],'criteria_id') : null;
		
		$dir = str_replace(array(',',' '),array($urlSeparator,''),Sanitize::getString($this->data,'dir'));

        $cat = str_replace(array(',',' '),array($urlSeparator,''),Sanitize::getString($this->data,'cat'));

        $section = str_replace(array(',',' '),array($urlSeparator,''),Sanitize::getString($this->data,'section'));
        
		$order = Sanitize::getVar($this->data,'order');

		$query_type = Sanitize::getVar($this->data,'search_query_type');
		
		$scope = Sanitize::getVar($this->data,'contentoptions');
		
		$author = Sanitize::getString($this->data,'author');
		
		$categories = Sanitize::getVar($this->data,'categories');
	
		$menu_id = Sanitize::getInt($this->data,'menu_id');
        
        $tmpl_suffix = Sanitize::getString($this->data,'tmpl_suffix');

        $illegal_chars = array('/','?',':',urldecode('%E3%80%80')); // Last one is japanese double space
        
		$sort = '';
		
		# Load Routes helper
		App::import('Helper','routes','jreviews');
		$Routes = new RoutesHelper();		
					
		# Get the Itemid
		$menu_id_param = $menu_id > 0 ? $menu_id : '';
	
		$url_params = '';
	
		# SIMPLE SEARCH
		if ($simple_search) {
	
			# Build the query string
			if (trim($keywords) != '') {
                $url_params .= (cmsFramework::mosCmsSef() ? '' : '/') . 'keywords'._PARAM_CHAR.str_replace(' ','+',urlencode(str_replace($illegal_chars,' ',$keywords)));
			}
	
			$dir and $url_params .= "/dir"._PARAM_CHAR.$dir;

            $section and $url_params .= "/cat"._PARAM_CHAR.'s'.$section;

            $cat and $url_params .= "/cat"._PARAM_CHAR.$cat;

            $tmpl_suffix and $url_params.= '/tmpl_suffix'._PARAM_CHAR.$tmpl_suffix;

            $order and $sort = '/order'._PARAM_CHAR.$order;

			# Checks if need to keep the Itemid on the result page
			if ($this->Config->search_itemid && $menu_id) 
			{
				$url = $Routes->search_results($menu_id_param,'');
			} else {
				$url = $Routes->search_results(null,'');
			}

			$url = cmsFramework::route($url . $url_params . $sort);

			cmsFramework::redirect($url);
			
			exit;
				
		}
		
		# ADVANCED SEARCH
		
		$url_params = array();
		
		$related = Sanitize::getVar($this->data,'related');
		if ($related) {
			$related = array_filter($related, "strlen"); // filter out blank ids
			$ids = implode($urlSeparator, $related);
			if ($ids != '') {
				$url_params[] = "related"._PARAM_CHAR.implode($urlSeparator, $related); 
			}
		}
		
		$criteria_param = $criteria ? (cmsFramework::mosCmsSef() ? '' : '/') . 'criteria:'.$criteria : '';
	
		// Search query type
		$query_type and $url_params[] = "query"._PARAM_CHAR.$query_type;
	
		$dir != '' and $url_params[] = "dir"._PARAM_CHAR.$dir;
	                    
		// Listing and reviews
		if ($keywords) {	
            if($scope)
            {
                $url_params[] = "scope"._PARAM_CHAR.urlencode(implode($urlSeparator,$scope));
            }
            $url_params[] = "keywords"._PARAM_CHAR.urlencode(str_replace($illegal_chars,' ',$keywords));
		}

		// Author
        $author and $url_params[] = "author"._PARAM_CHAR.urlencode($author);
	
		// Categories
		if (is_array($categories)) {
			// Remove empty values from array
			foreach ($categories as $index => $value) {
			   if (empty($value)) unset($categories[$index]);
			}
	
			if (!empty($categories)) {
				$url_params[] = "cat"._PARAM_CHAR.urlencode( implode($urlSeparator,$categories) );
			}
		} elseif($categories != '') { // Single select category list
			$url_params[] = "cat"._PARAM_CHAR.$categories;		
		}

		// First pass to process numeric values, need to merge operator and operand into one parameter
		if(isset($this->data['Field'])) 
		{
			foreach ($this->data['Field']['Listing'] as $key=>$value)
			{	
				if (substr($key, -9, 9) == '_operator') {
		
					$operand = substr ($key, 0, -9);
					
					if ((is_array($this->data['Field']['Listing'][$operand]) && is_numeric($this->data['Field']['Listing'][$operand][0])) 
                        || is_numeric($this->data['Field']['Listing'][$operand]) 
                    )
					{	
						$this->data['Field']['Listing'][$operand] = $value.$urlSeparator.trim(implode('_',$this->data['Field']['Listing'][$operand]));					
					} 
                    elseif ((is_array($this->data['Field']['Listing'][$operand]) && trim($this->data['Field']['Listing'][$operand][0])!='') 
                        || (!is_array($this->data['Field']['Listing'][$operand]) && trim($this->data['Field']['Listing'][$operand])!='')
                    )
                    { // Assume it's a date field
						$this->data['Field']['Listing'][$operand] = $value.$urlSeparator."date_".implode('_',$this->data['Field']['Listing'][$operand]);										
					} else {
						$this->data['Field']['Listing'][$operand] = '';
					}

                    // Remove trailing separator char
                    $this->data['Field']['Listing'][$operand] = rtrim($this->data['Field']['Listing'][$operand],$urlSeparator);
                }
			}

			// Second pass to process everything
			foreach ($this->data['Field']['Listing'] as $key=>$value) {
		
				$key_parts = explode("_",$key);
				$imploded_value = '';
			
				if (substr($key,0,3) == "jr_" && substr($key, -9, 9) != '_operator' && @$key_parts[2] != 'reset') {
		
					// multiple option field
					if (is_array($value)) {
		
						if(is_array($value[0]) && !empty($value[0]) ) {					
				
								$imploded_value = implode($urlSeparator,$value[0]);
						
						} elseif(!is_array($value[0]) && implode('',$value) != '') {
		
								$imploded_value = implode($urlSeparator,$value);
						}
						
						if($key != '' && $imploded_value != '') {
		
							$url_params[] = "$key"._PARAM_CHAR.urlencode(trim($imploded_value));
						
						}				
						
					// single option field	
					} elseif ( !is_array($value) && trim($value) != '') { 
						
						$url_params[] = "$key"._PARAM_CHAR.urlencode(trim($value));
					}
				}		
			}
		} // End isset $this->Data['Field']
	   
        $tmpl_suffix and $url_params[] = 'tmpl_suffix'._PARAM_CHAR.$tmpl_suffix;

		$url_params[] = "order"._PARAM_CHAR. ($order ? $order : $this->Config->list_order_default);

		# Remove empty values from array
		foreach ($url_params as $index => $value) {
		   if (empty($value)) unset($url_params[$index]);
		}
		
		$url_params = (cmsFramework::mosCmsSef() ? '' : '/') . implode ('/',$url_params);
	
		# Uncomment this line and comment the one below to keep the Itemid on the result page
		if ($this->Config->search_itemid && $menu_id) 
		{
			$url = $Routes->search_results($menu_id_param,'');
		} else {
			$url = $Routes->search_results(null,'');
		}	
		
		// Params outside route function because it messes up the urlencoding 
		$url = cmsFramework::route($url . $criteria_param . $url_params);
		
		cmsFramework::redirect($url);
	}
	
	/*
	* Loads the search form
	*/
	function _loadForm($params) {
		
		$this->autoRender = false;
        $this->autoLayout = false;
        $response = array();
		
		$this->action = 'advanced_form';
		
		$criteria_id = Sanitize::getInt($this->data['Search'],'criteria_id');
           
		$dateFieldsEntry = array();
	
		if ($criteria_id > 0) 
            {
			    # Process custom fields
			    $search = 1;
			    $searchFields = $this->Field->getFieldsArrayNew($criteria_id, 'listing', null, $search);
				    
			    # Get category list for selected criteria
			    $query = "SELECT Category.id as value, CONCAT(Section.title,' - ', Category.title) AS text"
			    . "\n FROM #__categories AS Category"
			    . "\n INNER JOIN #__jreviews_categories AS JreviewsCategory ON Category.id = JreviewsCategory.id AND JreviewsCategory.option = 'com_content'"
			    . "\n LEFT JOIN #__sections AS Section on Category.section = Section.id"
			    . ($criteria_id ? "\n WHERE JreviewsCategory.criteriaid = " . $criteria_id : '')
			    . "\n ORDER BY Section.title, Category.title"
			    ;

			    $this->_db->setQuery($query);
			    
			    $categoryList = $this->_db->loadAssocList('value');

			    $this->set(
				    array(
					    'Access'=>$this->Access,
					    'criteria_id'=>$criteria_id,
					    'categoryList'=>$categoryList,
					    'searchFields'=>$searchFields
				    )
			    );
			    
			    $page = $this->render();
			    $response[] = "jreviews.datepicker();";        
 
			    return $this->ajaxUpdateElement('search_fields',$page,$response);
		    
		    } 
        else 
            {
			    $response[] =  "jQuery('#search_fields').fadeOut();";
                return $this->ajaxResponse($response);
		    }	
	}

}