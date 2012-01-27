<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class FieldOptionsController extends MyController {
	
	var $uses = array('field_option');
	var $components = array('config','access');
	
	function beforeFilter() {
							
		# Init Access
		$this->Access->init($this->Config);

	}	
		
	/*
	* Adds new option to select list and updates the select list
	*/
	function _addOption() 
    {
        $this->autoRender = false;
        $this->autoLayout = false;
        $response = array();
                                                                                                       
		$option = $this->data['FieldOption']['text'] = Sanitize::getString($this->data,'text');
		$value = $this->data['FieldOption']['value'] = Sanitize::stripAll($this->data,'text');	
		$fieldid = $this->data['FieldOption']['fieldid'] = Sanitize::getInt($this->data,'field_id');
		$fieldName = Sanitize::getString($this->data,'name');
							
		// Begin validation		
		if ($value == '') {
            $validation = __t("The field is empty.",true);
            $response[] = "jQuery('#jr_fieldOption{$fieldid}').siblings('.jr_loadingSmall').after('<span class=\"jr_validation\">&nbsp;".$validation."</span>');";
			return $this->ajaxResponse($response);
		}
		
		// Save
		$result = $this->FieldOption->save($this->data);

        switch($result)            
            {
                case 'success':
                    // Begin update display        
                    $option = $this->data['FieldOption']['text'];
                    $value = $this->data['FieldOption']['value'];    
                    
                    $response = "
                        jQuery('#{$fieldName}').addOption('{$value}','".addslashes($option)."');
                        jQuery('#jr_fieldOption{$fieldid}').val('');            
                        jQuery('#submitButton{$fieldid}').removeAttr('disabled');
                    ";        
                    return $this->ajaxResponse($response);

                case 'duplicate':
                    $validation = sprintf(__t("%s already exists",true),$value);
                break;
                
                case 'db_error':
                    $validation = s2Messages::submitErrorGeneric();
                break;                
            }
           
        $response[] = "jQuery('#{$fieldName}').selectOptions('".addslashes($option)."');";
        $response[] = "jQuery('#jr_fieldOption{$fieldid}').siblings('.jr_loadingSmall').after('<span class=\"jr_validation\">&nbsp;".$validation."</span>');";
        return $this->ajaxResponse($response);

	}	
	
}