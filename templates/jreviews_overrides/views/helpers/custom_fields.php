<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class CustomFieldsHelper extends MyHelper
{		
	var $helpers = array('html','form','time');
	
	var $output = array();
    
    var $form_id = null; // Used to create unique element ids
	
	var $types = array(
		'text'=>'text',
		'select'=>'select',
		'radiobuttons'=>'radio',
		'selectmultiple'=>'select',
		'checkboxes'=>'checkbox',
		'website'=>'text',
		'email'=>'text',
		'decimal'=>'text',
		'integer'=>'text',
		'textarea'=>'textarea',
		'code'=>'textarea',
		'date'=>'date',
		'media'=>'',
        'hidden'=>'hidden'
	);
	
	var $legendTypes = array('radio','checkbox');
	
	var $multipleTypes = array('selectmultiple');
	
	var $multipleOptionTypes = array('select','selectmultiple','checkboxes','radiobuttons');		
	
	var $operatorTypes = array('decimal','integer','date');
	
	
	function getFieldsForComparison($listings) {
				
		$groups = array();

		foreach ($listings as $listing) {
					
			if(!empty($listing['Field']['pairs']))
            {
                foreach($listing['Field']['pairs'] AS $field)	
				{
					$viewAccess = $field['properties']['access_view'];               
                    if($field['properties']['compareview'] && $this->Access->in_groups($viewAccess))
                    {
                        $groups[$field['group_name']]['fields'][$field['name']]['name'] = $field['name'];
                        $groups[$field['group_name']]['fields'][$field['name']]['title'] = $field['title'];
						$groups[$field['group_name']]['group']['id'] = $field['group_id'];
						$groups[$field['group_name']]['group']['name'] = $field['group_name'];
						$groups[$field['group_name']]['group']['title'] = $field['group_title'];
						$groups[$field['group_name']]['group']['group_show_title'] = $field['group_show_title'];	
					}
                }
            }
		}
		return $groups;	
	}
	
    	
	function field($name, &$entry, $click2search = true, $outputReformat = true, $separator = ' &#8226; ') 
    {
        $name = strtolower($name);
		if(empty($entry['Field']) || !isset($entry['Field']['pairs'][$name])) {
            return false;            
		}

        $viewAccess = $entry['Field']['pairs'][$name]['properties']['access_view'];
        if(!$this->Access->in_groups($viewAccess)){
            return false;        
        }
        
        $output = $this->display($name, $entry, $click2search, $outputReformat);		
		
		return implode($separator,$output);		
	}
	
	function fieldValue($name,&$entry) {
        $name = strtolower($name);
		if(isset($entry['Field']['pairs'][$name])){
			return $this->onDisplay($entry['Field']['pairs'][$name],false,true,true);
		} else {
			return false;
		}
	}
	
	/**
	 * Shows text values for field options even if they have an image assigned.
	 */
	function fieldText($name, &$entry, $click2search = true, $outputReformat = true, $separator = ' &#8226; ') 
    {
        $name = strtolower($name);
		if(empty($entry['Field']) || !isset($entry['Field']['pairs'][$name])) {
			return false;
		}
        $entry['Field']['pairs'][$name]['properties']['option_images'] = 0;
		$output = $this->display($name, $entry, $click2search, $outputReformat, false);		
		return implode($separator,$output);				
	}
	
	function display($name, &$element, $click2search = true, $outputReformat = true) 
	{
		$Itemid = '';
		if(isset($element['Listing']) && Sanitize::getInt($element['Listing'],'menu_id') > 0) {
			$Itemid = $element['Listing']['menu_id'];        
        } 
		$criteriaid = $element['Criteria']['criteria_id'];	
        $catid = $element['Listing']['cat_id'];			
		$fields = $element['Field']['pairs'];
		$this->output = array();

		// Field specific processing
        $showImage = Sanitize::getInt($fields[$name]['properties'],'option_images',1);
        
		$this->onDisplay($fields[$name], $showImage);			

		if(Sanitize::getBool($fields[$name]['properties'],'formatbeforeclick'))
        {
            # Output reformat        
            if ($outputReformat) 
            {
                $this->outputReformat($name, $fields, $element);
            }
            # Click2search
            if (in_array($fields[$name]['properties']['location'],array('listing','content')) 
                && ($click2search && $fields[$name]['properties']['click2search'])) 
                {
                $this->click2Search($fields[$name], $criteriaid, $catid, $Itemid);        
            }
        }
        else
        {
            # Click2search
            if (in_array($fields[$name]['properties']['location'],array('listing','content')) 
                && ($click2search && $fields[$name]['properties']['click2search'])) 
                {
                $this->click2Search($fields[$name], $criteriaid, $catid, $Itemid);        
            }
            # Output reformat        
            if ($outputReformat) 
            {
                $this->outputReformat($name, $fields, $element);
            }
        }
				
		return $this->output;
	}	
	
	/**
	 * Returns true if there's a date field. Used to check whether datepicker library is loaded
	 *
	 * @param array $fields
	 * @return boolean
	 */
	function findDateField($fields) {
		if(!empty($fields)) {
			foreach($fields AS $group=>$group_fields) {
				foreach($group_fields AS $field) {
					foreach($field AS $field_array) {
						if($field_array['type']=='date') {
							return true;
						}
					}
				}
			}
		}
		return false;
	}
	
	function label($name, &$entry) {

		if(empty($entry['Field']) || !isset($entry['Field']['pairs'][$name])) {
			return null;
		}
		
		return $entry['Field']['pairs'][$name]['title'];
		
	}	
	
	function isMultipleOption($name,$element) {
		if(isset($element['Field']['pairs'][$name]) && in_array($element['Field']['pairs'][$name]['type'],$this->multipleOptionTypes)) {
			return true;
		}
		return false;
	}
	
	function onDisplay($field, $showImage = true, $value = false, $return = false) {

		if(empty($field)) {
			return null;
		} 

		$values = array();
		
		$option = $value ? 'value' : 'text';
		foreach($field[$option] AS $key=>$text) 
		{
			switch($field['type']) 
			{
				case 'date':
					$format = Sanitize::getString($field['properties'],'date_format');
					$text = $this->Time->nice($text,$format,0);
					break;
				case 'integer':
					$text = Sanitize::getInt($field['properties'],'curr_format') ? number_format($text) : $text;
					break;
				case 'decimal':
					$text = Sanitize::getInt($field['properties'],'curr_format') ? number_format($text,2,__l('DECIMAL_SEPARATOR',true),__l('THOUSANDS_SEPARATOR',true)) : round($text,2);
					break;
				case 'email':
					break;
				case 'website':
					$text = S2ampReplace($text);
					break;
				case 'code':                      
					$text = stripslashes($text);
					break;					
				case 'textarea': case 'text':
                        if(!Sanitize::getBool($field['properties'],'allow_html'))
                        {
                            $text = nl2br($text);
                        }
					break;
				case 'selectmultiple':
				case 'checkboxes':
				case 'select':
				case 'radiobuttons':
					$imgSrc = '';
                    
					if ($showImage && isset($field['image'][$key]) && $field['image'][$key] != '')  // Image assigned to this option
					{
                        if($imgSrc = $this->locateThemeFile('theme_images',cmsFramework::language() . '.' . $field['image'][$key],'',true)) {
							$imgSrc = pathToUrl($imgSrc);
					    } elseif ($imgSrc = $this->locateThemeFile('theme_images',$field['image'][$key],'',true)) {
                            $imgSrc = pathToUrl($imgSrc);
					    }

					    if ($imgSrc != '') {
						    $text = '<img src="'.$imgSrc.'" title="'.$text.'" alt="'.$text.'" border="0" />';
					    }										
					}
				break;
				default:
					$text = stripslashes($text);
				break;				
			}
		
			$values[] = $text;
			$this->output[] = $text;
		}
		if($return){
			return $values;
		}
	}
	
	function click2Search($field, $criteriaid, $catid, $Itemid) 
    {	
		if (isset($field['properties']['click2search'])) {
					
			$Itemid = $Itemid ? $Itemid : '';

			if(isset($field['properties']['click2searchlink']) && $field['properties']['click2searchlink'] != '') {
				$click2searchlink = $field['properties']['click2searchlink'];
			} else {
				$click2searchlink = 'index.php?option='.S2Paths::get('jreviews','S2_CMSCOMP').'&amp;Itemid={ITEMID}&amp;url=tag/{FIELDNAME}/{FIELDTEXT}/criteria'._PARAM_CHAR.'{CRITERIAID}';
			}

			foreach ($this->output AS $key=>$text) 
			{
				if($field['type'] == 'date') {

					$field['value'][$key] = str_replace(' 00:00:00','',$field['value'][$key]);	
				}

				$url = $click2searchlink;
				if($Itemid>0) {
					$url = str_replace('{ITEMID}',$Itemid,$url);
				} else {
					$url = str_replace(array('_m{ITEMID}','&Itemid={ITEMID}'),'',$url);					
				}

				$url = str_ireplace(
                    array(
                        '{FIELDNAME}',
                        '{FIELDTEXT}',
                        '{CRITERIAID}',
                        '{CATID}'
                    ),
                    array(
                        substr($field['name'],3),
                        urlencode($field['value'][$key]),
                        urlencode($criteriaid),
                        urlencode($catid)
                    ),
                    $url
                );

				$url = s2ampReplace($url);
				$url = cmsFramework::route($url);
				$this->output[$key] = "<a href=\"$url\">$text</a>";		
            }
		}		
	}	

	function outputReformat($name, &$fields, $element = array()) 
    {
		$field_names = array_keys($fields);
        
        // Listing vars
        $title = isset($element['Listing']) ? $element['Listing']['title'] : '';                
        $category = isset($element['Listing']) ? Sanitize::getString($element['Category'],'title') : '';                
        $section = isset($element['Listing']) ? Sanitize::getString($element['Section'],'title') : '';                

		// Check if there's anything to do
		if (isset($fields[$name]['properties']['output_format']) 
			&& trim($fields[$name]['properties']['output_format']) != '{FIELDTEXT}') {

			$format = $fields[$name]['properties']['output_format'];

			$curr_value = '';
	
			// Find all custom field tags to replace in the output format
			$matches = array();			
			$regex = '/jr_[a-z]{1,}/i';
			preg_match_all( $regex, $format, $matches );			
			$matches = $matches[0];

			// Loop through each field and make output format {tag} replacements
            foreach ($this->output AS $key=>$text) 
			{
                $text = str_ireplace('{FIELDTEXT}', $text, $format);
                           
				$text = str_ireplace('{FIELDTITLE}', $fields[$name]['title'], $text);
				
                !empty($title) and $text = str_ireplace('{title}', $title, $text);                    
                
                !empty($category) and $text = str_ireplace('{category}', $category, $text);                    

                !empty($section) and $text = str_ireplace('{section}', $section, $text);                    

				strstr($text,'{OPTIONVALUE}') and $text = str_ireplace('{OPTIONVALUE}',$fields[$name]['value'][$key],$text);
					
				// Quick check to see if there are custom fields to replace
				if (empty($matches)) {
					$this->output[$key] = $text;
				}

				foreach ($matches AS $curr_key) 
				{		
					$curr_text = stripslashes($fields[strtolower($curr_key)]['text'][0]);

					if (isset($fields[$curr_key]) && $fields[$curr_key]['type'] == 'date') {
						$curr_text = $this->Time->format($curr_text,$format);
					}
	
					$text = str_replace('{'.strtoupper($curr_key).'}', $curr_text, $text);
				}

				$this->output[$key] = $text;			
			}					
		}
	}
	
	/**
	 * Dynamic form creation for custom fields with default layout
	 *
	 * @param unknown_type $formFields
	 * @param unknown_type $fieldLocation
	 * @param unknown_type $search
	 * @param unknown_type $selectLabel
	 * @return unknown
	 */
	function makeFormFields(&$formFields, $fieldLocation, $search = null, $selectLabel = 'Select', &$listing)
    {
 		if(!is_array($formFields)) {
			return '';
		}
		
		$groupSet = array();
		
		$fieldLocation = Inflector::camelize($fieldLocation);
		foreach($formFields AS $group=>$fields) 
        {
			$inputs = array();

			foreach($fields['Fields'] AS $key=>$value) 
			{
   
				if((!$search && $this->Access->in_groups($value['properties']['access'])) || ($search && $this->Access->in_groups($value['properties']['access_view']))) {

					// Convert radio button to checkbox if multiple search is enabled in the config settings
					if($search && $this->Config->search_field_conversion && $value['type']=='radiobuttons') {
						$value['type'] = 'checkboxes';
					}					
					
					if($search && $this->Config->search_field_conversion && $value['type']=='select') {
						$value['type'] = 'selectmultiple';
					}					

					$inputs["data[Field][$fieldLocation][$key]"] = array(
						'id'=>		$value['name'],
						'type'=>	$this->types[$value['type']]
					);
	
                    //  Assign field classes
                    switch($value['type']){
                        case 'decimal': case 'integer':
                            $inputs["data[Field][$fieldLocation][$key]"]['class'] = 'shortField';
                        break;
                        case 'website':
                            $inputs["data[Field][$fieldLocation][$key]"]['class'] = 'mediumField';                        
                        break;
                        case 'text':
                            $inputs["data[Field][$fieldLocation][$key]"]['class'] = 'mediumField';                        
                        break;
                    }
        
					$inputs["data[Field][$fieldLocation][$key]"]['label']['text'] = $value['title'];
                    $inputs["data[Field][$fieldLocation][$key]"]['label']['class'] = $value['name'].'_label';      
 
                    # Add tooltip
                    if(!$search && Sanitize::getString($value,'description',null)) {
                        $inputs["data[Field][$fieldLocation][$key]"]['label']['text'] .= '<span class="jr_infoTip" title="'.htmlspecialchars($value['description'],ENT_QUOTES,cmsFramework::getCharset()).'">&nbsp;</span>';
                    }
								
					if(!$search && $value['required']){
						$inputs["data[Field][$fieldLocation][$key]"]['label']['text'] .= '<span class="required">'.__t("*",true).'</span>';
					}
					
					if(in_array($value['type'],$this->multipleTypes)) 
					{
						$inputs["data[Field][$fieldLocation][$key]"]['multiple'] = 'multiple';		
					}
					
					if(isset($value['optionList']) && $value['type'] == 'select') 
					{					
						$value['optionList'] = array(''=>$selectLabel) + $value['optionList'];
					}
					
					if(isset($value['optionList'])){
						$inputs["data[Field][$fieldLocation][$key]"]['options'] = $value['optionList'];
	
					}
	
					# Add click2add capability for select lists
					if($fieldLocation == 'Listing' && !$search && $this->types[$value['type']] == 'select' && $value['properties']['click2add'])
					{
						$click2AddLink = $this->Html->link(
							$this->Html->image($this->viewImages . 'option_add.png',array('border'=>0,'style'=>'margin-left:5px;')),
							'javascript:void(0);',
							array('sef'=>false,'onclick'=>"jQuery('#click2Add_{$value['field_id']}').toggle('slow');")
						);
						
						$click2AddInput = $this->Form->text(
							'jr_fieldOption'.$value['field_id'],
							array('id'=>'jr_fieldOption'.$value['field_id'])
						);
						
						$click2AddButton = $this->Form->button(
							__t("Submit",true),
							array(
								'onclick'=>"jreviews.field.addOption(this,{$value['field_id']},'{$value['name']}');",
								'div'=>false,
								'id'=>'submitButton'.$value['field_id'],
								'class'=>'button'
							)
						);
						
						$inputs["data[Field][$fieldLocation][$key]"]['after'] = 
						  $click2AddLink
						. "<div id='click2Add_{$value['field_id']}' class='jr_fieldDiv jr_newFieldOption'>"
						. $click2AddInput . ' '
						. $click2AddButton
						. "<span class=\"jr_loadingSmall jr_hidden\"></span>"
						. '</div>'
						;
					}	
					
					# Prefill values when editing                 
					if(isset($value['selected'])) {
						$inputs["data[Field][$fieldLocation][$key]"]['value'] = $value['selected'];
					}
					
					# Add search operator fields for date, decimal and integer fields
					if($search && in_array($value['type'],$this->operatorTypes)) 
					{
						$options = array(
							'equal'=>'=',
							'higher'=>'&gt;=',
							'lower'=>'&lt;='
                            ,'between'=>__t("between",true)
						);		
						
                        $inputs["data[Field][$fieldLocation][$key]"]['multiple'] = true; // convert field to array input for range searches                        
                                                                              
                        $attributes = array('id'=>$key.'high','multiple'=>true,'class'=>'shortField');
                        
                        if($this->types[$value['type']] == 'date') 
                        {
                            $attributes['class'] = 'dateField datepicker';
                        }
                        
                        // This is the high value input in a range search
                        $inputs["data[Field][$fieldLocation][$key]"]['after'] = '<span id="'.$key.'highDiv" style="display:none;">&nbsp;'.$this->Form->text("data[Field][Listing][{$key}]",$attributes).'</span>';                        
						$inputs["data[Field][$fieldLocation][$key]"]['between'] = $this->Form->select("data[Field][Listing][{$key}_operator]",$options,null,array('class'=>'jr_dateOperator input','onchange'=>"jreviews.search.showRange(this,'{$key}high');"));				
					}
					
					# Input styling
					$inputs["data[Field][$fieldLocation][$key]"]['div'] = 'jr_fieldDiv ' . $value['name']; 					
									
					if($this->types[$value['type']] == 'date') {
						$inputs["data[Field][$fieldLocation][$key]"]['class'] = 'dateField datepicker';
						//$inputs["data[Field][$fieldLocation][$key]"]['readonly'] = 'readonly';
					}
					
	
					if(in_array($this->types[$value['type']],$this->legendTypes)) {
						// Input styling
						$inputs["data[Field][$fieldLocation][$key]"]['option_class'] = 'jr_fieldOption';
						
						$inputs["data[Field][$fieldLocation][$key]"]['after'] = $this->Html->div('clr',' '); // To break the float					
					} 
					
				} // end access check
			} // end foreach

			if(!empty($inputs)) 
			{
				$groupSet[$group] = array(
					'fieldset'=>true,
					'legend'=>$group
				);
	
				foreach($inputs AS $dataKey=>$dataValue) {
					$groupSet[$group][$dataKey] = $dataValue;
				}
			}
			
		}
		
		/** HTGMOD **/
		// #309 - add link to edit bait type
		if ($listing && $listing['Category']['cat_id'] == 14) {
			include_once('templates/jreviews_overrides/views/themes/geomaps/listings/rel-summary.php');

			$bait = getRelatedList($listing['Listing']['listing_id'],array(101,102));
			$bait = array_shift($bait);
			
			if (!$bait) {
				$bait = new stdClass();
				$bait->title = __t('Add', true).' '.__t('Bait', true);
			}

			$groupSet['Fangstdata']['bait'] = array('id' => 'bait', 'type' => 'text', 'class' => 'mediumField jr_hidden', 
				'label' => array('text' => __t('Bait', true), 'class' => 'bait_label'), 'value' => array($bait->title), 
				'div' => 'jr_fieldDiv', 
				'after' => '<ul><li><a href="index.php?option=com_relate&id='.$listing['Listing']['listing_id'].'&cat=101,102&ss=1" class="rel8win">'.$bait->title.'</a></li></ul>');
		}
		
		// #1095 - relate fly tying pattern to insect
		if ($listing && $listing['Listing']['section_id'] == 23) {
			include_once('templates/jreviews_overrides/views/themes/geomaps/listings/rel-summary.php');
			
			if ($listing['Listing']['listing_id']) {
				$insects = getRelatedList($listing['Listing']['listing_id'],array(18,118,119,120,121,122,123));
				$insect_list = array();
				foreach ($insects as $insect) {
					$insect_list[] = $insect->title;
				}
				$insect_list = '<li>'.implode('</li><li>', $insect_list).'</li>';
				$relinput = '';
				$rel_link = 'index.php?option=com_relate&id='.$listing['Listing']['listing_id'].'&cat=s25';
				
				$relsave = <<<ENDSCRIPT
Relations.onAfterSave = function () {
	jQuery('#insectList li').first().siblings().remove();
	jQuery('.selected .itemTitle, .related .itemTitle').each(function () {
		jQuery('#insectList').append(jQuery('<li>'+jQuery(this).text()+'</li>'));
	});
}
ENDSCRIPT;
			}
			else {
				$insect_list = '';
				$relinput = '<input id="relate_insect" name="relate_id" type="hidden" value="" />';
				$rel_link = 'index.php?option=com_relate&cat=s25';
				
				$relsave = <<<ENDSCRIPT
Relations.save = function () {
	jQuery('#relate_insect').val(this.add_ids.listings.join());
	jQuery('#insectList li').first().siblings().remove();
	jQuery('.selected .itemTitle').each(function () {
		jQuery('#insectList').append(jQuery('<li>'+jQuery(this).text()+'</li>'));
	});
}
ENDSCRIPT;
			}
			
			echo '<script type="text/javascript">'.$relsave.'</script>';

			$groupSet['Flueinfo']['insect'] = array('id' => 'insect', 'type' => 'text', 'class' => 'mediumField jr_hidden', 
				'label' => array('text' => __t('Which insect / prey pattern to imitate?', true), 
				'class' => 'insect_label'), 'value' => '', 
				'div' => 'jr_fieldDiv', 
				'after' => '<ul id="insectList"><li><a href="'.$rel_link.'" class="rel8win">'.__t('Choose insect / prey', true).'</a></li>'.$insect_list.'</ul>'.$relinput);

		}
		/** END HTGMOD **/
		
		$output = '';
		foreach($groupSet AS $group=>$form) {
			$output .= $this->Form->inputs($form);
		}

		return $output;
	}
	
	/**
	 * Dynamic form creation for custom fields using custom layout - {field tags} in view file
	 *
	 * @param unknown_type $formFields
	 * @param unknown_type $fieldLocation
	 * @param unknown_type $search
	 * @param unknown_type $selectLabel
	 * @return array of form inputs for each field
	 */
	function getFormFields(&$formFields, $fieldLocation = 'listing', $search = null, $selectLabel = 'Select' ) {

		if(!is_array($formFields)) {
			return '';
		}

		$groupSet = array();
		
		$fieldLocation = Inflector::camelize($fieldLocation);

		foreach($formFields AS $group=>$fields) {

			$inputs = array();

			foreach($fields['Fields'] AS $key=>$value) 
			{
				// Convert radio button to checkbox if multiple search is enabled in the config settings
				if($search && $this->Config->search_field_conversion && $value['type']=='radiobuttons') {
					$value['type'] = 'checkboxes';
				}

				$inputs["data[Field][$fieldLocation][$key]"] = array(
					'id'=>		$value['name'].$this->form_id,
                    'type'=>	$this->types[$value['type']]
				);		

//				$inputs["data[Field][$fieldLocation][$key]"]['label'] = $value['title'];
				$inputs["data[Field][$fieldLocation][$key]"]['div'] = array();
               
				# Add tooltip
				if(!$search && Sanitize::getString($value,'description',null)) {
                                                                                                                                
                        $inputs["data[Field][$fieldLocation][$key]"]['label']['text'] .= '<span class="jr_infoTip" title="'.htmlspecialchars($value['description'],ENT_QUOTES,cmsFramework::getCharset()).'">&nbsp;</span>'; 
				}
							
                //  Assign field classes
                switch($value['type']){
                    case 'decimal': case 'integer':
                        $inputs["data[Field][$fieldLocation][$key]"]['class'] = 'shortField';
                    break;
                    case 'website':
                        $inputs["data[Field][$fieldLocation][$key]"]['class'] = 'mediumField';                        
                    break;
                    case 'text':
                        $inputs["data[Field][$fieldLocation][$key]"]['class'] = 'mediumField';                        
                    break;
                }
                
				if(in_array($value['type'],$this->multipleTypes)) 
				{
					$inputs["data[Field][$fieldLocation][$key]"]['multiple'] = 'multiple';		
					$inputs["data[Field][$fieldLocation][$key]"]['size'] = $value['properties']['size'];		
				}
				
				if(isset($value['optionList']) && $value['type'] == 'select') 
				{					
					$value['optionList'] = array(''=>$selectLabel) + $value['optionList'];
				}
				
				if(isset($value['optionList'])){
					$inputs["data[Field][$fieldLocation][$key]"]['options'] = $value['optionList'];

				}

				# Add click2add capability for select lists
				if($fieldLocation == 'Listing' && !$search && $this->types[$value['type']] == 'select' && $value['properties']['click2add'])
				{

					$click2AddLink = $this->Html->link(
						$this->Html->image($this->viewImages . 'option_add.png',array('border'=>0,'style'=>'margin-left:5px;')),
						'javascript:void(0);',
						array('sef'=>false,'onclick'=>"jQuery('#click2Add_{$value['field_id']}').toggle('slow');")
					);
					
					$click2AddInput = $this->Form->text(
						'option'.$value['field_id'],
						array('id'=>'option'.$value['field_id'])
					);
					
					$click2AddButton = $this->Form->button(
						__t("Submit",true),
						array(
							'onclick'=>"submitOption({$value['field_id']},'{$value['name']}');",
							'div'=>false,
							'id'=>'submitButton'.$value['field_id'],
							'class'=>'button'
						)
					);
					
					$inputs["data[Field][$fieldLocation][$key]"]['after'] = 
					  $click2AddLink
					. "<div id='click2Add_{$value['field_id']}' class='jr_fieldDiv jr_newFieldOption'>"
					. $click2AddInput . ' '
					. $click2AddButton
					. "<span id='spinner".$value['field_id']."' style='display: none;'><img alt='loading' src='".$this->viewImages."loading.gif' /></span>"
					. '</div>'
					;
				}	
				
				# Prefill values when editing
				if(isset($value['selected'])) {
					$inputs["data[Field][$fieldLocation][$key]"]['value'] = $value['selected'];
				}
				
				# Add search operator fields for date, decimal and integer fields
				if($search && in_array($value['type'],$this->operatorTypes)) 
				{
                    $options = array(
                        'equal'=>'=',
                        'higher'=>'&gt;=',
                        'lower'=>'&lt;='
                        ,'between'=>__t("between",true)
                    );        
                    
                    $inputs["data[Field][$fieldLocation][$key]"]['multiple'] = true; // convert field to array input for range searches                        
                                                                          
                    $attributes = array('id'=>$key.'high','multiple'=>true,'class'=>'shortField');
                    $selected='';
                   
                    if($this->types[$value['type']] == 'date') 
                    {
                        $attributes['class'] = 'dateField datepicker';
                    }
                                       // This is the high value input in a range search
                    $inputs["data[Field][$fieldLocation][$key]"]['after'] = '<span id="'.$key.'highDiv" style="display:none;">&nbsp;'.$this->Form->text("data[Field][Listing][{$key}]",$attributes).'</span>';                                                
					$inputs["data[Field][$fieldLocation][$key]"]['between'] = $this->Form->select("data[Field][Listing][{$key}_operator]",$options,$selected,array('class'=>'jr_dateOperator input','onchange'=>"jreviews.search.showRange(this,'{$key}high');"));								                
                }
				
				# Input styling
				if($this->types[$value['type']] == 'date') {
					$inputs["data[Field][$fieldLocation][$key]"]['class'] = 'dateField datepicker';
					$inputs["data[Field][$fieldLocation][$key]"]['readonly'] = 'readonly';
				}				

				if(in_array($this->types[$value['type']],$this->legendTypes)) {
					// Input styling
					$inputs["data[Field][$fieldLocation][$key]"]['option_class'] = 'jr_fieldOption';
					
					$inputs["data[Field][$fieldLocation][$key]"]['after'] = $this->Html->div('clr',' '); // To break the float					
				} 			
			}

			$groupSet[$group] = array(
				'fieldset'=>false,
				'legend'=>false
			);

			foreach($inputs AS $dataKey=>$dataValue) {
				$groupSet[$group][$dataKey] = $dataValue;
			}
			
		}
		
		$output = array();
		foreach($groupSet AS $group=>$form) {
			$output = array_merge($output,$this->Form->inputs($form,null,null,true));
		}
            
		return $output;
	}	
	
			
}

//		return $this->Form->inputs
//			(
//				array(
//					'fieldset'=>true,
//					'legend'=>'Group XYZ',
//					'data[Field][jr_text]'=>
//					array(
//						'label'=>array('for'=>'jr_text','text'=>'Text Field'),
//						'id'=>'jr_text',
//						'type'=>'text',
//						'size'=>'10',
//						'maxlength'=>'100',
//						'class'=>'{required:true}'
//					),
//					'data[Field][jr_select]'=>
//					array(
//						'label'=>array('for'=>'select','text'=>'Select Field'),
//						'id'=>'select',
//						'type'=>'select',
//						'options'=>array('1'=>'1','2'=>'2'),
//						'selected'=>2
//					),
//					'data[Field][jr_selectmultiple]'=>
//					array(
//						'label'=>array('for'=>'selectmultiple','text'=>'Multiple Select Field'),
//						'id'=>'selectmultiple',
//						'type'=>'select',
//						'multiple'=>'multiple',
//						'size'=>'2',
//						'options'=>array('1'=>'email','2'=>'asdfasdf'),
//						'value'=>array(1,2)
//					),
//					'data[Field][jr_checkbox]'=>
//					array(
//						'label'=>false,
//						'legend'=>'Checkboxes',
//						'type'=>'checkbox',
//						'options'=>array('1'=>'Option 1','2'=>'Option 2'),
//						'value'=>array(2),
//						'class'=>'{required:true,minLength:2}'
//					),
//					'data[Field][jr_radio]'=>
//					array(
//						'legend'=>'Radio Buttons',
//						'type'=>'radio',
//						'options'=>array('1'=>'Option 1','2'=>'Option 2'),
//						'value'=>1,
//						'class'=>'{required:true}'
//					)		
//					
//				)	
//			);		

?>