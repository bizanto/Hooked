<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

App::import('Controller','common','jreviews');

class ModuleAdvancedSearchController extends MyController {
	
	var $uses = array('menu','field','category');
	
	var $helpers = array('libraries','html','assets','form','custom_fields');
	
	var $components = array('config');

	var $autoRender = false;
	
	var $autoLayout = false;
	
	var $fieldTags;
		
	function beforeFilter() 
    {
        parent::beforeFilter();
											
		$this->viewSuffix = Sanitize::getString($this->params['module'],'tmpl_suffix');
	
		# Set Theme	
		$this->viewTheme = $this->Config->template;
		$this->viewImages = S2Paths::get('jreviews', 'S2_THEMES_URL'). $this->viewTheme . _DS . 'theme_images' . _DS;	
	
	}
	
	/**
	 * Dynamically replace the field tags with their labels/form field equivalents
	 */
	function afterFilter() {

		$output = &$this->output;
		
		$names = array();
		$labels = array();
		$select = array();
		$cat_tag = false;
		$date_field = false;

        $dir_id = $section_id = $cat_id = $criteria_id = '';
				
		# Initialize FormHelper
		$Form = new FormHelper();
		$CustomFields = new CustomFieldsHelper();
		$CustomFields->Config = &$this->Config;
          
		# Process custom field tag attributes
		foreach($this->fieldTags AS $key=>$value) 
		{
			$var = explode('|',$value);

			if(!strstr($value,'_label')) {

				$names[$var[0]] = $value;
			
			} elseif (strstr($value,'_label')) {
				
				$labels[] = substr($value,0,-6);
				
			}
			
			if($value == 'category') {
				
				$cat_tag = true;
/************************/	
				if(isset($var[1]) && $var[1] == 'm') {
					$category_select_type = ' multiple="multiple"';
				}
				
				if(isset($var[2]) && (int) $var[2] > 0) {
					$category_select_size = ' size="'.$var[2].'"';
				}				
/************************/								
			}

			if (isset($var[1]) && strtolower($var[1]) == 'm') {         
				$select[$var[0]] = 'selectmultiple';
			} elseif (isset($var[1]) && strtolower($var[1]) == 's') {
				$select[$var[0]] = 'select';
			}
			
			$select_size[$var[0]] = isset($var[2]) ? $var[2] : 5;
					
			# Check for category select list
			if($var[0] == 'category') {
				if(isset($var[1]) && strtolower($var[1]) == 's') {
					$category_select_type=' multiple="multiple"';
				}
				if(isset($var[2]) && (int) $var[2] > 0) {
					$category_select_size = ' size="'.$var[2].'"';
				}
				
			}
		}

		# Get selected values from url
		$entry = array();
		foreach($this->params AS $key=>$value) {
			if(substr($key,0,3) == 'jr_') {
				$entry['Field']['pairs'][$key]['value'] = explode('_',$value);
			}
			// Add categories/sections
		}
        
		if(isset($this->params['tag'])) {
			$entry['Field']['pairs']['jr_'.$this->params['tag']['field']]['value'] = array($this->params['tag']['value']);
		}
		
		# Generate category list if tag found in view
		if($cat_tag)                  
        {		
            # Category auto detect
            if(Sanitize::getInt($this->params['module'],'cat_auto')) 
            {            
                $ids = CommonController::_discoverIDs($this);
                extract($ids);
            }

            if($section_id == '' && $cat_id != '')
            {
                $sql = "SELECT section FROM #__categories WHERE id IN (".$cat_id.")";
                $this->_db->setQuery($sql);
                $section_id = $this->_db->loadResult();                                  
            }
                                        
            $cat_id != '' and $this->params['module']['cat_id'] = $cat_id;
            $cat_id == '' and $section_id != '' and $this->params['module']['section_id'] = $section_id;                    
            $cat_id == '' and $criteria_id != '' and $this->params['module']['criteria_id'] = $criteria_id;                    
     
            $categorySelect = $this->Category->categoryTree($this->_user->gid, $this->params);

			$output = str_replace('{'.$names['category'].'}',$categorySelect,$output);			
		}

		$fields = $this->Field->getFieldsArrayFromNames(array_keys($names),'listing',$entry);

		# Replace label tags and change field type based on view atttributes
		if($fields)
		{
			foreach($fields AS $key=>$group) {
				
				foreach($group['Fields'] AS $name=>$field) {
	
					if(isset($field['optionList']) && isset($select[$name])) 
					    {		
						    $fields[$key]['Fields'][$name]['type'] = $select[$name];
						    $fields[$key]['Fields'][$name]['properties']['size'] = $select_size[$name];		
					    } 
                    elseif($fields[$key]['Fields'][$name]['type'] == 'textarea') 
                        {
                            $fields[$key]['Fields'][$name]['type'] = 'text';
                        }
	
					if(in_array($name,$labels)) {
						$output = str_replace('{'.$name.'_label}',$field['title'],$output);
					}

					if($field['type']=='date') {
						$date_field = true;
					}
				}			
				
			}

			$search = true;
			$location = 'listing';
            
            $CustomFields->form_id = Sanitize::getInt($this->params,'module_id');
            
			$formFields = $CustomFields->getFormFields($fields, $location, $search, __t("Select",true));

			# Replace input tags
            foreach($names AS $key=>$name) 
            {            
                if(isset($formFields["data[Field][Listing][{$key}]"])) {
                    $output = str_replace('{'.$names[$key].'}',$formFields["data[Field][Listing][{$key}]"],$output);
                }
            }
			
			# Load js and css			
			if($date_field) 
            {
				$Html = RegisterClass::getInstance('HtmlHelper');
				$Html->app = 'jreviews';
                $Html->startup();
				$Libraries = RegisterClass::getInstance('LibrariesHelper');
				$Html->js(arrayFilter(array('jq.ui.core','jq.ui.datepicker'), $Libraries->js()),$inline);
				$Html->css(arrayFilter(array('jq.ui.core'), $Libraries->css()),false);
                ?>
                <script type="text/javascript">jreviews.datepicker();</script>
                <?php
			}		
		}

		return $output;
	}
	
	function index()
	{			
        $file = S2Object::locateThemeFile('modules','advanced_search');
		$this->fieldTags = $this->extractTags(file_get_contents($file));					
		return $this->render('modules','advanced_search');
	}

	function extractTags($view) {

		$pattern = '/{([a-z0-9_|]*)}/i';

		$matches = array();

		$result = preg_match_all( $pattern, $view, $matches );

		if( $result == false ) {		
			return array();
		}

		return array_unique(array_values($matches[1]));
	}
}