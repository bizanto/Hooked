<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class FieldModel extends MyModel  {
    
    var $name = 'Field';
    
    var $useTable = '#__jreviews_fields AS `Field`';
    
    var $primaryKey = 'Field.fieldid';
    
    var $realKey = 'fieldid';
    
    var $fieldOptions;
    
    var $fields = array(
        'Field.fieldid AS `Field.fieldid`',
        'Field.name AS `Field.name`',
        'Field.title AS `Field.title`',
        'Field.type AS `Field.type`',
        'Field.required AS `Field.required`',
        'Field.access AS `Field.access`',
        'Field.access_view AS `Field.access_view`',
        'Field.options AS `Field.options`',
        'Field.showtitle AS `Field.showtitle`',
       'Field.description AS `Field.description`',
       'Field.groupid AS `Field.groupid`',
       'Field.location AS `Field.location`',
       'Field.size AS `Field.size`',
       'Field.maxlength AS `Field.maxlength`',
       'Field.cols AS `Field.cols`',
       'Field.rows AS `Field.rows`',
       'Field.contentview AS `Field.contentview`',
       'Field.ordering AS `Field.ordering`',
       'Field.listview AS `Field.listview`',
       'Field.compareview AS `Field.compareview`',
       'Field.listsort AS `Field.listsort`',
       'Field.search AS `Field.search`',
       'Field.published AS `Field.published`'        
    );
            
    function afterFind($results) {

        if(!is_array($results)) {
            return $results;
        }
        
        # Convert field options into _params array         
        foreach($results AS $key=>$result)    
        {
            if(!isset($result['Field']['options'])) continue;
                
            $params = explode("\n",$result['Field']['options']);

            foreach ($params AS $param) 
            { 
                $param_name = current(explode("=",$param));
                $param_value = end(explode("=",$param));
                $results[$key]['Field']['_params'][$param_name] = $param_value;
            }            
            
        }

        return $results;    
    }
    
        
    function getList($location, $groupid, $limitstart, $limit, &$total, $type = array()) 
    {
        $type = implode(",",$type);
        
        // get the total number of records
        $query = "SELECT count(f.fieldid)"
        . "\n FROM #__jreviews_fields AS f"
        . "\n INNER JOIN #__jreviews_groups AS g ON g.groupid = f.groupid"
        . "\n WHERE f.location = '$location'"
        . ($groupid > 0 ? "\n AND f.groupid = '$groupid'" : '')
        . ($type != '' ? "\n AND f.type in ($type)" : '')
        ;
        $this->_db->setQuery( $query );
        $total = $this->_db->loadResult();
    
        $query = "SELECT f.fieldid, f.groupid, f.name, f.title, f.showtitle, f.required, f.type, f.ordering, f.contentview,"
        . "\n f.listview, f.listsort, f.compareview, f.search, f.published, f.metatitle, f.metakey, f.metadesc,"
        . "\n g.name as `group`"
        . "\n FROM #__jreviews_fields AS f"
        . "\n INNER JOIN #__jreviews_groups AS g on g.groupid = f.groupid"
        . "\n WHERE f.location = '$location'"
        . ($groupid > 0 ? "\n AND f.groupid = '$groupid'" : '')
        . ($type != '' ? "\n AND f.type in ($type)" : '')
        . "\n ORDER BY groupid, f.ordering"
        . "\n LIMIT $limitstart,$limit"
        ;
    
        $this->_db->setQuery($query);
    
        $rows = $this->_db->loadObjectList();
    
        return $rows;
    }


    /**
     * Used for deleting of new fields in administration
     */    
    function deleteTableColumn($fields, $location) {

        $output = array();
    
        if ($location == 'content') {
            foreach ($fields as $field) {
                $this->_db->setQuery("ALTER TABLE #__jreviews_content DROP $field");
                if (!$this->_db->query()) {
                        $output[] = "It was not possible to delete the field ".$field." from the #__jreviews_content table.\n".$this->_db->getErrorMsg();
                }
            }
        } elseif ($location == 'review' ) {
            foreach ($fields as $field) {
                $this->_db->setQuery("ALTER TABLE #__jreviews_review_fields DROP $field");
                if (!$this->_db->query()) {
                        $output[] = "It was not possible to delete the field ".$field." from the #__jreviews_review_fields table.\n".$this->_db->getErrorMsg();
                }
            }
        }
    
        if (count($output)>0) return implode("\n",$output); else return true;
    }

    /**
     * Used for creation of new fields in administration
     */
    function addTableColumn ($field, $type, $location) {
    
        $output = '';
        $null = 'NOT NULL';
        
        switch ($type) {
            case 'text': case 'email': case 'select': case 'website': case 'radiobutton':
                $dbtype = "MEDIUMTEXT";
                break;
            case 'selectmultiple': case 'checkboxes':
                $dbtype = "MEDIUMTEXT";
            break;
            case 'textarea': case 'code':
                $dbtype = "MEDIUMTEXT";
                break;
            case 'integer':
                $dbtype = "INT(13)";
                $null = '';
                break;
            case 'decimal':
                $dbtype = "DECIMAL(20,7)";
                $null = '';
                break;
            case 'date':
                $dbtype = "DATETIME";
                break;
            default:
                $dbtype = "MEDIUMTEXT";
            break;
        }
        if ($location == 'content') {
            $this->_db->setQuery("ALTER TABLE #__jreviews_content ADD $field $dbtype " . $null);
        } elseif ($location == 'review') {
            $this->_db->setQuery("ALTER TABLE #__jreviews_review_fields ADD $field $dbtype " . $null);
        }
    
        if (!$this->_db->query()) {
            $output = "It was not possible to add the new field to the #__jreviews_content table so we could not create the field.".$this->_db->getErrorMsg();
        }
    
        return $output;
    
    }

    ################## CHECK WHICH FUNCTIONS STAY AND WHICH GO 
    
    function _category2Criteria($cat_ids) {

        $out = array();
        
        if (is_array($cat_ids))
        {
            $cat_ids = implode(',',$cat_ids);
        }
        
        $query = "SELECT criteriaid FROM #__jreviews_categories"
        . "\n WHERE `option` = 'com_content'"
        . ($cat_ids != '' ? "\n AND id IN ($cat_ids)" : '')
        ;
        
        $this->_db->setQuery($query);

        $criteria_ids = $this->_db->loadObjectList();

        if(is_array($criteria_ids))
        {
            foreach($criteria_ids AS $criteria) {
                $out[] = $criteria->criteriaid;    
            }
        }

        return array_unique($out);
    }

    function _criteria2Groups($criteria_ids,$type)
    {
        
        if (is_array($criteria_ids) && isset($criteria_ids[0]) && $criteria_ids[0] != '') 
        {
            $criteria_ids = array_unique($criteria_ids);
        
        } elseif (!is_array($criteria_ids) && $criteria_ids != '') {
            
            $criteria_ids = array($criteria_ids);
            
        } else {
            
            $this->group_ids = array();
            
            return array();            
            
        }

        //build group_ids array to get the list of fields and field values for the page
        $Criteria = RegisterClass::getInstance('CriteriaModel');
                
        $group_ids = $Criteria->findAll(
            array(
                'fields'=>array('Criteria.groupid'),
                'conditions'=>array('Criteria.id IN ('.implode(',',$criteria_ids).')')
            )
        ); 
        
        if (!$group_ids || empty($group_ids)) {
            $this->group_ids = array();
            return array();
        }

        $group_group_ids = array();

        foreach ($group_ids AS $group_id) {
            if ($group_id['Criteria']['groupid'] != '')
                $group_group_ids[] = $group_id['Criteria']['groupid'];
        }

        if(!empty($group_group_ids))
        {
            if($type == 'listing') {
                $type = 'content';
            }
            
            //now leave only the group ids for the current type
            $sql = "SELECT groupid FROM #__jreviews_groups"
            . "\n WHERE groupid IN (".implode(',',$group_group_ids).")"
            . "\n AND type = '$type'"
            ;
            $this->_db->setQuery($sql);
            
            $this->group_ids = $this->_db->loadResultArray(); 
        
        } else {
            $this->group_ids = array();
        
        }

        return $this->group_ids;    
        
    }
    
    function _extractFieldIds($fields, $field_types = array()) {
        
        $field_ids = array();
        
        foreach ($fields AS $field) 
        {
            $field = (array) $field;

            if(in_array($field['Field.type'],$field_types) || empty($field_types))
            {
                $field_ids[] = $field['Field.id'];
            }    
        }

        return $field_ids;
    
    }
    
    function _getFieldOptions()
    {

        if(!empty($this->field_ids)) 
        {

            # Get the field options for all multiple choice fields ordered by a-z ASC
            $sql = "SELECT FieldValue.optionid, FieldValue.fieldid, FieldValue.text, FieldValue.value, FieldValue.image, FieldValue.ordering"
            ."\n FROM #__jreviews_fieldoptions AS FieldValue"
            ."\n WHERE FieldValue.fieldid IN (".implode(',',$this->field_ids).")"
            ."\n ORDER BY FieldValue.text ASC"
            ;
            $this->_db->setQuery($sql);
            
            $rows = $this->_db->loadObjectList();
                
            $fieldValues = array();
            $fieldOrdering = array();
            
            foreach ($rows AS $row) {
                
                $this->field_options_alpha[$row->fieldid][$row->value] = 
                    array(
                        'optionid'=>$row->optionid,
                        'value'=>$row->value,
                        'text'=>$row->text,
                        'image'=>$row->image,
                        'ordering'=>$row->ordering
                    );

                $this->field_optionsList_alpha[$row->fieldid][$row->value] = $row->text;
    
            }
            
            # Get the field options for all multiple choice fields ordered by ordering
            $sql = "SELECT FieldValue.optionid, FieldValue.fieldid, FieldValue.text, FieldValue.value, FieldValue.image, FieldValue.ordering"
            ."\n FROM #__jreviews_fieldoptions AS FieldValue"
            ."\n WHERE FieldValue.fieldid IN (".implode(',',$this->field_ids).")"
            ."\n ORDER BY FieldValue.ordering ASC"
            ;
            $this->_db->setQuery($sql);
            
            $rows = $this->_db->loadObjectList();
                
            $fieldValues = array();
            $fieldOrdering = array();
            
            foreach ($rows AS $row) {
                
                $this->field_options_ordering[$row->fieldid][$row->value] = 
                    array(
                        'optionid'=>$row->optionid,
                        'value'=>$row->value,
                        'text'=>$row->text,
                        'image'=>$row->image,
                        'ordering'=>$row->ordering
                    );

                $this->field_optionsList_ordering[$row->fieldid][$row->value] = $row->text;
    
            }            
            
        }
    }
        
    function addFields($entries, $type) 
    {
        $this->getFieldsArray($entries, $type);
        
        switch($type) {
            case 'listing':
                    $field_key = 'listing_id';
                break;
            case 'review':
                    $field_key = 'review_id';
                break;
        }

        foreach($entries AS $key=>$value) 
        {                                                                               
            if(isset($this->custom_fields[$value[inflector::camelize($type)][$field_key]]))
            {                                                                            
                $entries[$key]['Field']['groups'] = $this->custom_fields[$value[inflector::camelize($type)][$field_key]];
                $entries[$key]['Field']['pairs'] = $this->field_pairs[$value[inflector::camelize($type)][$field_key]];            
            } else {
                $entries[$key]['Field']['groups'] = '';
                $entries[$key]['Field']['pairs'] = '';
            }
            
        }
        return $entries;
    }        
    
    /**
     * Creates the custom field group array with group info and fields values and attributes
     *
     * @param array $entries Entry array must have keys for entry id and criteriaid
     */
    function getFieldsArray($elements, $type = 'listing') 
    {      
        $fields = array();
        $field_pairs = array();
        $element_ids = array();
        $rows = array();
        $this->criteria_ids = array(); // Alejandro = for discussion functionality

        //build entry_ids and criteria_ids array
        switch($type) {
            
            case 'listing':
                foreach ($elements AS $key=>$element) {
                    if(isset($element['Criteria']))
                    {
                        $element_ids[] = $element[inflector::camelize($type)]['listing_id'];
                        if($element['Criteria']['criteria_id']!='')
                            $this->criteria_ids[] = $element['Criteria']['criteria_id'];
                    }
                }

                break;
            case 'review':
                   foreach ($elements AS $element) {
                    if(isset($element['Criteria']))
                    {
                        $element_ids[] = $element[inflector::camelize($type)]['review_id'];
                        if($element['Criteria']['criteria_id']!='')
                            $this->criteria_ids[] = $element['Criteria']['criteria_id'];                        
                    }
                }        
                break;
        }

        $this->group_ids = $this->_criteria2Groups($this->criteria_ids, $type);
    
        $criteria_ids = implode(',',$this->criteria_ids);
        $element_ids = implode(',',array_unique($element_ids));

        if (empty($this->group_ids)){
            return;
        }
        
        $group_ids = implode(',',$this->group_ids);        
        
        $field_type = $type == 'listing' ? 'content' : $type;
        
        // Get field attributes and field values
        $query = "SELECT Field.fieldid AS `Field.field_id`, Field.groupid AS `Field.group_id`, Field.name AS `Field.name`, Field.title AS `Field.title`,"
        . "\n Field.showtitle AS `Field.showTitle`, Field.description AS `Field.description`, Field.required AS `Field.required`,"
        . "\n Field.type AS `Field.type`, Field.location AS `Field.location`, Field.options AS `Field.params`,"
        . "\n Field.contentview AS `Field.contentView`, Field.listview AS `Field.listView`, Field.compareview AS `Field.compareView`, Field.listsort AS `Field.listSort`,"
        . "\n Field.search AS `Field.search`, Field.access AS `Field.access`, Field.access_view AS `Field.accessView`,"
        . "\n Field.published As `Field.published`,"
        . "\n `Group`.groupid AS `Group.group_id`, `Group`.title AS `Group.title`, `Group`.name AS `Group.name`, `Group`.showtitle AS `Group.showTitle`"
        . "\n FROM #__jreviews_fields AS Field"
        . "\n INNER JOIN #__jreviews_groups AS `Group` ON (`Group`.groupid = Field.groupid AND "
        . "\n `Group`.groupid IN ($group_ids) AND `Group`.type =  '$field_type' )"
        . "\n WHERE Field.location = '$field_type' AND Field.published = 1"        
        . "\n ORDER BY Group.ordering, Field.ordering"
        ;
        
        $this->_db->setQuery($query);
        
        $rows = $this->_db->loadObjectList('Field.name');

        if (!$rows || empty($rows)) {
            return;
        }

        # Extract list of field names from array
        $fieldNames = array();
        $fieldNamesByType = array();
        $fieldRows = array();
        $optionFields = array('selectmultiple','checkboxes','select','radiobuttons');
        
        foreach($rows AS $key=>$row) {
            
            $fieldNames[] = $row->{'Field.name'};
            $fieldIds[$row->{'Field.name'}] = $row->{'Field.field_id'};
            $fieldRows[$key] = (array) $row;
                    
            if(in_array($row->{'Field.type'},$optionFields)) {

                $query = "SELECT * FROM #__jreviews_fieldoptions"
                . "\n WHERE fieldid = ".$row->{'Field.field_id'}
                . "\n ORDER BY ordering ASC ,optionid ASC";
                $this->_db->setQuery($query);

                $this->fieldOptions[$row->{'Field.field_id'}] = $this->_db->loadObjectList('value');                                                    
            }
        }
        
        # Get field values from current element ids
        switch($type) 
        {            
            case 'listing':        
                # PaidListings integration    
                if(Configure::read('ListingEdit') && Configure::read('PaidListings.enabled'))
                {   // Load the paid_listing_fields table instead of the jos_content table so users can see all their 
                    // fields when editing a listing
                    Configure::write('ListingEdit',false);
                    $fieldValues = PaidListingFieldModel::edit($element_ids);
                    if($fieldValues) break;                
                } 
                $query = "SELECT Listing.contentid AS element_id," . implode(',',$fieldNames)
                . "\n FROM #__jreviews_content AS Listing"
                . "\n WHERE Listing.contentid IN (" . $element_ids . ")"
                ;
                $this->_db->setQuery($query);
                $fieldValues = $this->_db->loadObjectList('element_id');
            break;
            case 'review':
                $query = "SELECT Review.reviewid AS element_id," . implode(',',$fieldNames)
                . "\n FROM #__jreviews_review_fields AS Review"
                . "\n WHERE Review.reviewid IN (" . $element_ids . ")"
                ;                
                $this->_db->setQuery($query);
                $fieldValues = $this->_db->loadObjectList('element_id');
            break;                    
        }
        
        # Now for each option field add array of selected value,text,images
        $elementFields = array();

        if(!empty($fieldValues)) 
        {
            foreach($fieldValues AS $fieldValue)
            {
                $fieldValue = (array) $fieldValue;
                
                foreach($fieldValue AS $key=>$value)
                {
                    if ($key != 'element_id' && $value != '' && isset($rows[$key]))
                    {
                        !in_array($rows[$key]->{'Field.type'},array('textarea','code')) and $value = htmlspecialchars($value,ENT_QUOTES,'UTF-8');
                        
                        if($rows[$key]->{'Field.type'} != 'date' || ($rows[$key]->{'Field.type'} == 'date' && $value != NULL_DATE)) 
                        {
                            $elementFields[$fieldValue['element_id']]['field_id'] = $fieldRows[$key]['Field.field_id'];
                                                        
                            if(!in_array($rows[$key]->{'Field.type'},$optionFields) ) 
                            {
                                $elementFields[$fieldValue['element_id']][$key]['Field.text'][] = $value;
                                $elementFields[$fieldValue['element_id']][$key]['Field.value'][] = $value;
                                $elementFields[$fieldValue['element_id']][$key]['Field.image'][] = '';
                                
                            } elseif(in_array($rows[$key]->{'Field.type'},$optionFields) ) {
        
                                $fieldOptions = $this->fieldOptions[$rows[$key]->{'Field.field_id'}];
        
                                $selOptions = explode('*',$value);
    
                                foreach($selOptions AS $selOption) 
                                {
                                    if(isset($fieldOptions[$selOption])) {
                                        $elementFields[$fieldValue['element_id']][$key]['Field.value'][] = $fieldOptions[$selOption]->value;
                                        $elementFields[$fieldValue['element_id']][$key]['Field.text'][] = $fieldOptions[$selOption]->text;
                                        $elementFields[$fieldValue['element_id']][$key]['Field.image'][] = $fieldOptions[$selOption]->image;;    
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
            
        // Reformat array so array keys match element ids
        foreach ($elementFields AS $key=>$elementField) 
        {
            $element_id = $key;
            $field_id = $elementField['field_id'];
            
            unset($elementField['field_id']);

            $field_name = key($elementField);
                                                                    
            foreach($elementField AS $field_name=>$field_options) 
            {
                
                //FieldGroups array
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Group']['group_id'] = $fieldRows[$field_name]['Field.group_id'];;
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Group']['title'] = $fieldRows[$field_name]['Group.title'];
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Group']['name'] = $fieldRows[$field_name]['Group.name'];
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Group']['show_title'] = $fieldRows[$field_name]['Group.showTitle'];
            
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['id'] = $field_id;
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['group_id'] = $fieldRows[$field_name]['Field.group_id'];
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['name'] = $fieldRows[$field_name]['Field.name'];
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['type'] = $fieldRows[$field_name]['Field.type'];
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['title'] = $fieldRows[$field_name]['Field.title'];    
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['description'] = $fieldRows[$field_name]['Field.description'];    

                // Field values
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['value'] = $field_options['Field.value'];
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['text'] = $field_options['Field.text'];
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['image'] = $field_options['Field.image'];
                
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['properties']['show_title'] = $fieldRows[$field_name]['Field.showTitle'];                        
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['properties']['location'] = $fieldRows[$field_name]['Field.location'];
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['properties']['contentview'] = $fieldRows[$field_name]['Field.contentView'];                
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['properties']['listview'] = $fieldRows[$field_name]['Field.listView'];
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['properties']['compareview'] = $fieldRows[$field_name]['Field.compareView'];
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['properties']['listsort'] = $fieldRows[$field_name]['Field.listSort'];
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['properties']['search'] = $fieldRows[$field_name]['Field.search'];
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['properties']['access'] = $fieldRows[$field_name]['Field.access'];
                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['properties']['access_view'] = $fieldRows[$field_name]['Field.accessView'];

                //FieldPairs associative array with field name as key and field value as value
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['group_id'] = $fieldRows[$field_name]['Field.group_id'];
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['group_show_title'] = $fieldRows[$field_name]['Group.showTitle'];                   
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['group_title'] = $fieldRows[$field_name]['Group.title'];                   
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['group_name'] = $fieldRows[$field_name]['Group.name'];                
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['name'] = $fieldRows[$field_name]['Field.name'];
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['title'] = $fieldRows[$field_name]['Field.title'];
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['value'] = $field_options['Field.value'];
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['text'] = $field_options['Field.text'];
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['image'] = $field_options['Field.image'];
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['type'] = $fieldRows[$field_name]['Field.type'];
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['properties']['show_title'] = $fieldRows[$field_name]['Field.showTitle'];                        
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['properties']['location'] = $fieldRows[$field_name]['Field.location'];
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['properties']['contentview'] = $fieldRows[$field_name]['Field.contentView'];                
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['properties']['listview'] = $fieldRows[$field_name]['Field.listView'];
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['properties']['compareview'] = $fieldRows[$field_name]['Field.compareView'];
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['properties']['listsort'] = $fieldRows[$field_name]['Field.listSort'];
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['properties']['search'] = $fieldRows[$field_name]['Field.search'];
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['properties']['access'] = $fieldRows[$field_name]['Field.access'];
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['properties']['access_view'] = $fieldRows[$field_name]['Field.accessView'];                    
                
                $properties = stringToArray($fieldRows[$field_name]['Field.params']);

                $fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['properties'] = array_merge($fields[$element_id][$fieldRows[$field_name]['Group.name']]['Fields'][$fieldRows[$field_name]['Field.name']]['properties'],$properties);
                $field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['properties'] = array_merge($field_pairs[$element_id][$fieldRows[$field_name]['Field.name']]['properties'],$properties);

                //$params = explode("\n",$fieldRows[$field_name]['Field.params']); 
            }
        }

        $this->custom_fields = $fields;    
        $this->field_pairs = $field_pairs;            
    }
    
    /**
     * Used in forms
     * Creates the custom field group array with group info and fields attributes
     * The selected key is filled if an $entry array with field values is passed
     */
    function getFieldsArrayNew($criteria_ids, $location = 'listing', $entry = null, $search = null) 
    {
        $rows = false;

        # Check for cached version        
        $cache_prefix = 'field_model_new';
        $cache_key = func_get_args();
        if(isset($cache_key[2])) unset($cache_key[2]); // $entry not required to cache the results
        if($cache = S2cacheRead($cache_prefix,$cache_key)){
            $rows = $cache;
        }        
        
        if(false==$rows || $rows == '') {
            
            $fields = array();
            
            $location = ($location == 'listing' ? 'content' : $location);
            
            $group_ids = $this->_criteria2Groups($criteria_ids,$location);        
    
            if (empty($group_ids))
            {
                return;
            }
    
            //get field attributes only, no values
            $sql = "SELECT Field.fieldid AS `Field.id`, Field.name AS `Field.name`, Field.title AS `Field.title`,"
            . "\n Field.showtitle AS `Field.showTitle`, Field.description AS `Field.description`, Field.required AS `Field.required`,"
            . "\n Field.type AS `Field.type`, Field.location AS `Field.location`, Field.options AS `Field.params`,"
            . "\n Field.search AS `Field.search`, Field.access AS `Field.access`, Field.access_view AS `Field.accessView`,"
            . "\n Field.published As `Field.published`,"
            . "\n `Group`.groupid AS `Group.group_id`, `Group`.title AS `Group.title`, `Group`.showtitle AS `Group.showTitle`"
            . "\n FROM #__jreviews_fields AS Field"
            . "\n INNER JOIN #__jreviews_groups AS `Group` ON (`Group`.groupid = Field.groupid AND "
            . "\n `Group`.groupid IN (".implode(',',$group_ids).") AND `Group`.type =  '$location' )"
            . "\n WHERE Field.published = 1 AND Field.location = '$location'"
            . ($search ? "\n AND search = 1" : '')
            . "\n GROUP BY Field.fieldid"
            . "\n ORDER BY Group.ordering, Field.ordering"
            ;
            
            $this->_db->setQuery($sql);
            $rows = $this->_db->loadObjectList();
        
            # Send to cache
            S2cacheWrite($cache_prefix,$cache_key,$rows);
        }        

        if (!$rows || empty($rows))
        {
            return;
        }
        
        //extract field ids from array
        $this->field_ids = $multi_field_ids = $this->_extractFieldIds($rows,array('select','selectmultiple','radiobuttons','checkboxes'));

        //get the field options for multiple choice fields
        $this->_getFieldOptions();        

        // Reformat array and add field options to each multiple choice field
        foreach ($rows AS $row) 
        {
            $row = (array) $row;
    
            //FieldGroups array
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['field_id'] = $row['Field.id'];
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['name'] = $row['Field.name'];
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['type'] = $row['Field.type'];
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['title'] = $row['Field.title'];
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['description'] = $row['Field.description'];
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['required'] = $row['Field.required'];            

            # $entry is passed when editing listing or review and includes the existing field values
            if(is_array($entry) && !empty($entry['Field']['pairs']) && isset($entry['Field']['pairs'][$row['Field.name']]['value']))
            {
                $fields[$row['Group.title']]['Fields'][$row['Field.name']]['selected'] = $entry['Field']['pairs'][$row['Field.name']]['value'];
            }

            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['properties']['show_title'] = $row['Field.showTitle'];        
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['properties']['location'] = $row['Field.location'];
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['properties']['search'] = $row['Field.search'];
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['properties']['access'] = $row['Field.access'];
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['properties']['access_view'] = $row['Field.accessView'];
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['properties']['published'] = $row['Field.published'];
            
            $params = explode("\n",$row['Field.params']);
    
            foreach ($params AS $param) { 
                $key = current(explode("=",$param));
                $value = end(explode("=",$param));
                $fields[$row['Group.title']]['Fields'][$row['Field.name']]['properties'][$key] = $value;
            }

            if(isset($this->field_options_alpha[$row['Field.id']])) {
                
                $ordering = isset($fields[$row['Group.title']]['Fields'][$row['Field.name']]['properties']['option_ordering']) && $fields[$row['Group.title']]['Fields'][$row['Field.name']]['properties']['option_ordering'] ? 'alpha' : 'ordering';
                        
                $method = 'field_options_'.$ordering;
                $methodList = 'field_optionsList_'.$ordering;
                $fields[$row['Group.title']]['Fields'][$row['Field.name']]['options'] = $this->{$method}[$row['Field.id']];
                $fields[$row['Group.title']]['Fields'][$row['Field.name']]['optionList'] = $this->{$methodList}[$row['Field.id']];                        
            }
            
        }                    
                
        return $fields;    
            
    }
    
    /**
     * Used in dynamic forms
     * Creates an array of fields with attributes and options using an array of field names as input
     */
    function getFieldsArrayFromNames($names, $location = 'listing',  $entry = null) 
    {
        if(empty($names) || (count($names)==1 & $names[0] == 'category')) {
            return array();
        }

        $rows = false;
        
        # Check for cached version        
        $cache_prefix = 'field_model_names';
        $cache_key = func_get_args();
        if(isset($cache_key[2])) unset($cache_key[2]); // $entry not required to cache the results        
        if($cache = S2cacheRead($cache_prefix,$cache_key)){
            $rows = $cache;
        }        

        if(false==$rows || $rows == '') {

            $location = ($location == 'listing' ? 'content' : $location);
            
            foreach($names AS $name) {
                $quoted_names[] = "'".$name."'";
            }
            
            $quoted_names = implode(',',$quoted_names);
            
            //get field attributes only, no values
            $sql = "SELECT Field.fieldid AS `Field.id`, Field.name AS `Field.name`, Field.title AS `Field.title`,"
            . "\n Field.type AS `Field.type`, Field.options AS `Field.params`,  Field.required AS `Field.required`,"
            . "\n `Group`.groupid AS `Group.group_id`, `Group`.title AS `Group.title`"
            . "\n FROM #__jreviews_fields AS Field"
            . "\n INNER JOIN #__jreviews_groups AS `Group` ON (`Group`.groupid = Field.groupid AND "
            . "\n `Group`.groupid AND `Group`.type =  '$location' )"        
            . "\n WHERE Field.name IN ($quoted_names) AND Field.location = '$location'"
            ;
            
            $this->_db->setQuery($sql);
            $rows = $this->_db->loadObjectList();

            # Send to cache
            S2cacheWrite($cache_prefix,$cache_key,$rows);        
        }
        
        if (!$rows || empty($rows))
        {
            return;
        }
        
        //extract field ids from array
        $this->field_ids = $multi_field_ids = $this->_extractFieldIds($rows,array('select','selectmultiple','radiobuttons','checkboxes'));

        //get the field options for multiple choice fields
        $this->_getFieldOptions();        

        // Reformat array and add field options to each multiple choice field
        foreach ($rows AS $row) 
        {           
            $row = (array) $row;
    
            //FieldGroups array
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['field_id'] = $row['Field.id'];
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['name'] = $row['Field.name'];
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['type'] = $row['Field.type'];
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['title'] = $row['Field.title'];
            $fields[$row['Group.title']]['Fields'][$row['Field.name']]['required'] = $row['Field.required'];

            # $entry is passed when editing listing or review and includes the existing field values
            if(is_array($entry) && isset($entry['Field']['pairs'][$row['Field.name']]['value']))
            {
                $fields[$row['Group.title']]['Fields'][$row['Field.name']]['selected'] = $entry['Field']['pairs'][$row['Field.name']]['value'];
            }

            
            $params = explode("\n",$row['Field.params']);
    
            foreach ($params AS $param) { 
                $key = current(explode("=",$param));
                $value = end(explode("=",$param));
                $fields[$row['Group.title']]['Fields'][$row['Field.name']]['properties'][$key] = $value;
            }

            if(isset($this->field_options_alpha[$row['Field.id']])) {
                
                $ordering = isset($fields[$row['Group.title']]['Fields'][$row['Field.name']]['properties']['option_ordering']) && $fields[$row['Group.title']]['Fields'][$row['Field.name']]['properties']['option_ordering'] ? 'alpha' : 'ordering';
                        
                $method = 'field_options_'.$ordering;
                $methodList = 'field_optionsList_'.$ordering;
                $fields[$row['Group.title']]['Fields'][$row['Field.name']]['options'] = $this->{$method}[$row['Field.id']];
                $fields[$row['Group.title']]['Fields'][$row['Field.name']]['optionList'] = $this->{$methodList}[$row['Field.id']];                        
            }
            
        }                    

        return $fields;    
            
    }    
    
    /**
     * Auxiliary function - used for custom coding
     * Creates an array of fields with field names as array keys and attributes and options using an array of field names as input
     */
    function getFields($names, $location = 'listing') 
    {
        if(empty($names) || (count($names)==1 & $names[0] == 'category')) {
            return array();
        }

        $rows = false;
        
        # Check for cached version        
        $cache_prefix = 'field_model_names';
        $cache_key = func_get_args();
        if(isset($cache_key[2])) unset($cache_key[2]); // $entry not required to cache the results        
        if($cache = S2cacheRead($cache_prefix,$cache_key)){
            $rows = $cache;
        }        

        if(false==$rows || $rows == '') {

            $location = ($location == 'listing' ? 'content' : $location);
            
            foreach($names AS $name) {
                $quoted_names[] = "'".$name."'";
            }
            
            $quoted_names = implode(',',$quoted_names);
            
            //get field attributes only, no values
            $sql = "SELECT Field.fieldid AS `Field.id`, Field.name AS `Field.name`, Field.title AS `Field.title`,"
            . "\n Field.type AS `Field.type`, Field.options AS `Field.params`,  Field.required AS `Field.required`, Field.access_view AS `Field.access_view`,"
            . "\n `Group`.groupid AS `Group.group_id`, `Group`.title AS `Group.title`"
            . "\n FROM #__jreviews_fields AS Field"
            . "\n INNER JOIN #__jreviews_groups AS `Group` ON (`Group`.groupid = Field.groupid AND "
            . "\n `Group`.groupid AND `Group`.type =  '$location' )"        
            . "\n WHERE Field.name IN ($quoted_names) AND Field.location = '$location'"
            ;
            
            $this->_db->setQuery($sql);
            $rows = $this->_db->loadObjectList();

            # Send to cache
            S2cacheWrite($cache_prefix,$cache_key,$rows);        
        }
        
        if (!$rows || empty($rows))
        {
            return;
        }
        
        //extract field ids from array
        $this->field_ids = $multi_field_ids = $this->_extractFieldIds($rows,array('select','selectmultiple','radiobuttons','checkboxes'));

        //get the field options for multiple choice fields
        $this->_getFieldOptions();        

        // Reformat array and add field options to each multiple choice field
        foreach ($rows AS $row) {
            
            $row = (array) $row;
    
            //FieldGroups array
            $fields[$row['Field.name']]['field_id'] = $row['Field.id'];
            $fields[$row['Field.name']]['name'] = $row['Field.name'];
            $fields[$row['Field.name']]['type'] = $row['Field.type'];
            $fields[$row['Field.name']]['title'] = $row['Field.title'];
            $fields[$row['Field.name']]['required'] = $row['Field.required'];
            $fields[$row['Field.name']]['properties']['access_view'] = $row['Field.access_view'];            
            $params = explode("\n",$row['Field.params']);

            foreach ($params AS $param) { 
                $parts = explode("=",$param); // Need to use this approach because click2search field has equal signs in the value of the property
                $key = array_shift($parts);
                $value = implode('=',$parts);
                $fields[$row['Field.name']]['properties'][$key] = $value;
            }

            if(isset($this->field_options_alpha[$row['Field.id']])) {
                
                $ordering = isset($fields[$row['Field.name']]['Fields'][$row['Field.name']]['properties']['option_ordering']) && $fields[$row['Group.title']]['Fields'][$row['Field.name']]['properties']['option_ordering'] ? 'alpha' : 'ordering';
                        
                $method = 'field_options_'.$ordering;
                $methodList = 'field_optionsList_'.$ordering;
                $fields[$row['Field.name']]['options'] = $this->{$method}[$row['Field.id']];
                $fields[$row['Field.name']]['optionList'] = $this->{$methodList}[$row['Field.id']];                        
            }
            
        }                    

        return $fields;    
            
    }    
            
    /**
     * This is the list of values used for the order select list
     */
    function getOrderList($cat_ids, $location, $task, $field_list_tasks) 
    {
        if(empty($cat_ids) && $task != 'alphaindex') {
            return null;
        }
        
        $list = array();
        $field_order_array = array();
        
        if($location == 'listing') {
            $location = 'content';
        }

        // Check the number of criteria defined and if more than one check if all have a common group
        // A common set of groups means the criteria is different because of the ratings, not the fields
        // This is to show the custom fields in the dropdown select list for search results if the fields are common to all entries
        $query = "SELECT count(DISTINCT groupid)"
        . "\n FROM #__jreviews_criteria"
        . "\n WHERE groupid != ''"
        ;
        $this->_db->setQuery($query);
        
        $criteria_groups = $this->_db->loadResult();

        $criteria_ids = $this->_category2Criteria($cat_ids);

        if(($criteria_groups == 1 || count($criteria_ids) ==1) && in_array($task, $field_list_tasks)) 
        {
            $group_ids = $this->_criteria2Groups($criteria_ids, $location);

            if(!empty($group_ids)) 
            {
                // Need to include fieldid for multilingual support Joomfish
                $query = "SELECT Field.fieldid, Field.title AS text, Field.name AS value, Field.access_view AS access"
                . "\n FROM #__jreviews_fields AS Field"
                . "\n WHERE Field.location = '".$location."' "
                . "\n AND Field.listsort = 1"
                . "\n AND Field.groupid IN (" . implode(',',$group_ids) . ")"
                ;
                $this->_db->setQuery($query);
                
                if($list = $this->_db->loadObjectList()) {
                    foreach ($list AS $key=>$row) {
                        $field_order_array[$row->value] = (array) $row;
                    }
                }
            }
        }
        
        return $field_order_array;
    }
    
    function save(&$data, $location = 'listing', $isNew, &$validFields) 
    {
        $msg = '';
        $fieldLocation = inflector::camelize($location);
        
        // Check if there are custom fields to save or exit
        if (isset($data['Field']) 
            && 
            (!is_array($data['Field'][$fieldLocation]) || count($data['Field'][$fieldLocation]) == 1
            )
        ) {
            return $msg;
        }

        // Define field types that accept predefined options to store the reference values
        $optionsArray = array ("select","selectmultiple","checkboxes","radiobuttons");
       
        if(!empty($validFields))
        {            
            foreach ($validFields as $validField) 
            {
                $fieldName = $validField['name'];
                
                $inputValue = '';

                if (Sanitize::getVar($data['Field'][$fieldLocation],$fieldName,'') != '' 
                    || ($validField['type'] == 'code' && Sanitize::getVar($data['__raw']['Field'][$fieldLocation],$fieldName,'') != '')) 
                {
                    switch($validField['type']) 
                    {
                        case 'selectmultiple': case 'checkboxes':
                            //Checks for types with options
                            $multi_options = Sanitize::getVar($data['Field'][$fieldLocation],$fieldName,'');
                            $inputValue = '*'.implode('*',$multi_options).'*';
                        break;
                        case 'select': case 'radiobuttons':
                            //Checks for types with options
                            $inputValue = '*'.Sanitize::getString($data['Field'][$fieldLocation],$fieldName).'*';
                        break;
                        case 'code':                    
                            // Affiliate code left unfiltered
                            $inputValue = Sanitize::getString($data['__raw']['Field'][$fieldLocation],$fieldName,'');
                        break;
                        case 'decimal':
                            $inputValue = Sanitize::getFloat($data['Field'][$fieldLocation],$fieldName);
                        break;
                        case 'integer':
                            $inputValue = Sanitize::getInt($data['Field'][$fieldLocation],$fieldName);
                        break;
                        case 'date':
                            if(Sanitize::getString($data['Field'][$fieldLocation],$fieldName) != '' && Sanitize::getString($data['Field'][$fieldLocation],$fieldName) != null) {
                                $inputValue = strftime( _CURRENT_SERVER_TIME_FORMAT, strtotime(Sanitize::getString($data['Field'][$fieldLocation],$fieldName)));
                            } else {
                                $inputValue = '';
                            }                        
                        break;
                        case 'textarea': case 'text':
                            if (isset($validField['_params']['allow_html']) && $validField['_params']['allow_html'] == 1 ) {
                                $inputValue = Sanitize::stripScripts(Sanitize::getVar($data['__raw']['Field'][$fieldLocation],$fieldName));
                                $inputValue = stripslashes($inputValue);
                            } else {
                                $inputValue = Sanitize::getString($data['Field'][$fieldLocation],$fieldName,'');
                            }
                        break;
                        case 'website': case 'email':
                            $inputValue = Sanitize::stripScripts(Sanitize::getVar($data['Field'][$fieldLocation],$fieldName));
                        break;
                        default:
                            $inputValue = Sanitize::getVar($data['Field'][$fieldLocation],$fieldName);
                        break;
                    }                

                    # Modify form post arrays to current values
                    if($inputValue === '' || $inputValue === '**') {
                        $inputValue = '';
                    }          
                    
                    $data['Field'][$fieldLocation][$fieldName] = $inputValue;

                } else { // To clear multiple choice fields

                    switch($validField['type']) 
                    {
                        case 'decimal':
                        case 'integer':
                            $data['Field'][$fieldLocation][$fieldName] = null;
                        break;                        
                        default:
                            $data['Field'][$fieldLocation][$fieldName] = '';
                        break;
                    }

                }
                
                // Debug custom fields array
                $msg .=  "{$validField['name']}=>{$inputValue}"."<br />";            
            }
        }

        # Need to check if jreviews_content or jreviews_reviews record exists to decide whether to insert or update the table
        if($location == 'review') {
            App::import('Model','jreviews_review_field','jreviews');
            $JreviewsReviewFieldModel = new JreviewsReviewFieldModel();
            $recordExists = $JreviewsReviewFieldModel->findCount(array('conditions'=>array('JreviewsReviewField.reviewid= ' . $data['Field']['Review']['reviewid'])));            
        } else {
            App::import('Model','jreviews_content','jreviews');
            $JreviewsContentModel = new JreviewsContentModel();
            $recordExists = $JreviewsContentModel->findCount(array('conditions'=>array('JreviewsContent.contentid = ' . $data['Listing']['id'])));
        }
        
        $dbAction = $recordExists ? 'update' : 'insert';

        if($location == 'review') 
        {
            $this->$dbAction('#__jreviews_review_fields',$fieldLocation,$data['Field'],'reviewid');    
        } 
        else 
        {
            if(Configure::read('PaidListings.enabled') && Sanitize::getInt($data,'paid_category'))
            {
                # PaidListings integration - saves all fields to jreviews_paid_listing_fields table and removes unpaid fields from jreviews_content table
                $PaidListingField = RegisterClass::getInstance('PaidListingFieldModel');
                $PaidListingField->save($data);
            }   
            $this->$dbAction('#__jreviews_content',$fieldLocation,$data['Field'],'contentid');      
         }
    }    
    
    function validate(&$data, $fieldLocation, $Access) 
    {
        if(!isset($data['Field'])) {
            return;
        }        

        $location = $fieldLocation == 'listing' ? 'content' : 'review'; 

        $query = "
            SELECT 
                groupid 
            FROM 
                #__jreviews_criteria 
            WHERE 
                id = " . (int) $data['Criteria']['id'];
        
        $this->_db->setQuery($query);
        
        $groupids = $this->_db->loadResult();        
        if ($groupids) 
        {
            appLogMessage("*********Validate fields",'database');
            
            # PaidListings integration to remove hidden fields from validation
            $plan_fields = isset($data['Paid']) ? explode(",",Sanitize::getString($data['Paid'],'fields')) : '';
            !empty($plan_fields) and $plan_fields =  "'" . implode("','", $plan_fields) . "'"; 
            
            $queryData = array(
                    'conditions'=>array(
                        'Field.groupid IN (' . $groupids . ')',
                        'Field.published = 1',
                        "Field.location = '$location'"  
                    )
                );
            
            $plan_fields != '' and $queryData['conditions'][] = "Field.name IN (" . $plan_fields . ")";    
            $fields = $this->findAll($queryData);

            if (!$fields) {
                return;
            }

            $valid_fields = array();
            
            $fieldLocation = inflector::camelize($fieldLocation);

            foreach ($fields as $field) {

                // Check validation only for displayed fields *access rights*
                if (in_array($Access->gid,explode(",",$field['Field']['access']))) {

                    $value = Sanitize::getVar($data['Field'][$fieldLocation],$field['Field']['name'],'');
//                    $value = isset($data['Field'][$fieldLocation][$field['Field']['name']]) ? $data['Field'][$fieldLocation][$field['Field']['name']] : '';

                    $label = sprintf(__t("You must fill in a valid value for %s.",true),$field['Field']['title']);
                    
                    $name = $field['Field']['name'];
                    
                    $type = $field['Field']['type'];
                    
                    $required = $field['Field']['required'];
                            
                    $valid_fields[] = $field['Field'];

                    $regex = '';

                    if(!isset($field['Field']['_params']['valid_regex'])) {

                        switch($field['Field']['type']) {
                            case 'integer':
                                $regex = "^[0-9]+$";
                                break;
                            case 'decimal':
                                $regex = "^(\.[0-9]+|[0-9]+(\.[0-9]*)?)$";
                                break;
                            case 'website':
                                $regex = "^(ftp|http|https)+(:\/\/)+[a-z0-9_-]+\.+[a-z0-9_-]";
                                break;
                            case 'email':
                                $regex = ".+@.*";
                                break;
                            default:
                                $regex = '';
                                break;
                        }
                        
                    } elseif ($type != 'date') {

                        $regex = $field['Field']['_params']['valid_regex'];
                    }
                    
                    if (!is_array($value)) {
                        $value = array($value);

                    } elseif($type == 'selectmultiple' && is_array($value[0])) {
                        $data['Field'][$fieldLocation][$field['Field']['name']] = $data['Field'][$fieldLocation][$field['Field']['name']][0];
                        $value = $value[0];
                    }
                    
                    $value = trim(implode(',',$value));

                    $this->validateInput($value, $name, $type, $label, $required, $regex);

                }
            }

            return $valid_fields;

        }
    }

}
