<?php
defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class RelatedFieldsController extends MyController 
{            
    function beforeFilter(){}
    
    function findChildOptions() 
    {
        $response = array();
        $childField = Sanitize::getString($this->data,'childField');
        $childSelected = Sanitize::getString($this->data,'childSelected');
        $parentValue = Sanitize::getString($this->data,'parentValue');
        $module_id = Sanitize::getInt($this->data,'module_id');
        
        if($parentValue==''){
            $ret = '<option value="">'.__t("Select",true,true).'</option>';  
            $response[] = "jQuery(\"#{$childField}{$module_id}\").html('{$ret}').attr('disabled','disabled');";
            return implode(' ',$response);
        }
        
        $query = "                                                                                           
            SELECT 
                FieldOption.optionid, FieldOption.text, FieldOption.value
            FROM #__jreviews_fieldoptions AS FieldOption
            INNER JOIN #__jreviews_fields AS Field ON FieldOption.fieldid = Field.fieldid AND Field.name = '".$childField."'
            WHERE FieldOption.value LIKE '".$parentValue."-%'
        ";     
                   
        $this->_db->setQuery($query);
        $options = $this->_db->loadAssocList();
        $ret = '<option value="">'.__t("Select",true,true).'</option>';  
                                        
        foreach ($options as $option)
        {
            if($childSelected != '' && $option['value'] == $childSelected){   
                $ret .= '<option selected="selected" value="'.$option['value'].'">'.$option['text'].'</option>';                   
            } else {
                $ret .= '<option value="'.$option['value'].'">'.$option['text'].'</option>';
            }
        }

        $response[] = "jQuery(\"#{$childField}{$module_id}\").html('{$ret}').removeAttr('disabled');";
        return implode(' ',$response);
    }    
}