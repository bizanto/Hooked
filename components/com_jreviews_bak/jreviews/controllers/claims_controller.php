<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2009 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class ClaimsController extends MyController {
        
    var $uses = array('user','menu','claim');
    
    var $helpers = array('html','form','routes');
    
    var $components = array('config','everywhere','notifications','activities');
            
    function beforeFilter() 
    {        
        # Call beforeFilter of MyController parent class
        parent::beforeFilter();
    }    
    
    // Need to return object by reference for PHP4
    function &getNotifyModel() {
        return $this->Claim;
    }    

    // Need to return object by reference for PHP4
    function &getActivityModel() {
        return $this->Claim;
    }     
        
    function create() 
    {
        $this->autoRender = false;
        $this->autoLayout = false;
        $listing_id = Sanitize::getInt($this->params,'listing_id');
                
        if($listing_id)
        {                
            $this->set('listing_id',$listing_id);
            return $this->ajaxResponse($this->render('claims','create'),false);            
        }
        
        return $this->ajaxError(s2Messages::accessDenied());        
    }
    
    function _save() 
    {
        $this->autoRender = false;
        $this->autoLayout = false;        
        $this->components = array('security');
        $this->__initComponents();        
        $listing_id = Sanitize::getInt($this->data['Claim'],'listing_id');
        $response = array();
            
        # Validate form token      
        if($this->invalidToken) {
            return $this->ajaxError(s2Messages::invalidToken());
        }    
        
        if(!$listing_id) {
            return $this->ajaxError(s2Messages::accessDenied());
        }   

        if($this->Config->claims_enable && $this->_user->id ) 
        {    
            $this->data['Claim']['claim_text'] = Sanitize::getString($this->data['Claim'],'claim_text');
            
            if ($this->data['Claim']['claim_text'] != '') {
                
                // Check if this user already has a claim for this listing to update it
                $claim_id = $this->Claim->findOne(array(
                    'fields'=>array('Claim.claim_id AS `Claim.claim_id`'),
                    'conditions'=>array(
                        'Claim.user_id = ' . (int) $this->_user->id,
                        'Claim.listing_id = ' . $listing_id,
                        'Claim.approved <= 0'
                        )
                ));
                
                if($claim_id > 0) {
                    $this->data['Claim']['claim_id'] = $claim_id;
                }
                $this->data['Claim']['user_id'] = $this->_user->id;
                $this->data['Claim']['created'] = date('Y-m-d H:i:s');
                $this->data['Claim']['approved'] = 0;                
                                
                if($this->Claim->store($this->data))
                {
                    $update_text = __t("Your claim was submitted, thank you.",true);                
                    $response[] =  "jQuery('#jr_claimImg{$listing_id}').remove();";
                    return $this->ajaxUpdateDialog($update_text,$response);
                }
                        
            } else 
            {
                # Validation failed
                if(isset($this->Security))
                {
                    $response[] = "jQuery('#jr_claimToken').val('".$this->Security->reissueToken()."');";
                }                
                return $this->ajaxValidation(__t("The message is empty.",true),$response);
            }
        
        }
        
        return $this->ajaxError(s2Messages::submitErrorDb());
    }
}
