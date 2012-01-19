<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2009 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class VotesController extends MyController {
	
	var $uses = array('menu','review','vote');
		
	var $components = array('config','access','activities','everywhere');

	var $autoRender = false;

	var $autoLayout = false;

	function beforeFilter() {
        parent::beforeFilter();				
	}
	
	// Need to return object by reference for PHP4
	function &getActivityModel() {
		return $this->Vote;
	}	
    
    // Need to return object by reference for PHP4
    function &getEverywhereModel() {
        return $this->Review;
    } 
        
    function &getPluginModel() {
        return $this->Vote;
    }
							
    function _save() 
    {        
        $response = array();
         
        $this->data['Vote']['user_id'] = $this->_user->id;
        $this->data['Vote']['review_id'] = (int)$this->data['Vote']['review_id'];
        $this->data['Vote']['vote_yes'] = Sanitize::getInt($this->data['Vote'],'vote_yes');
        $this->data['Vote']['vote_no'] = Sanitize::getInt($this->data['Vote'],'vote_no');        
        $this->data['Vote']['created'] = gmdate('Y-m-d H:i:s');
        $this->data['Vote']['ipaddress'] = $this->ipaddress;          
              
        if(!$this->data['Vote']['review_id']){
            return $this->ajaxError(s2Messages::submitErrorGeneric());
        }                

        // Find duplicates
        $duplicate = $this->Vote->findCount(array(
            'conditions'=>array(
                'review_id = ' . $this->data['Vote']['review_id'],
                'ipaddress = ' . $this->Vote->Quote($this->data['Vote']['ipaddress'])                    
        )));

        // It's a guest so we only care about checking the IP address if this feature is not disabled and
        // server is not localhost
        if(!$this->_user->id)
        {
            if(!$this->Config->vote_ipcheck_disable && $this->ipaddress != '127.0.0.1')
            {
                // Do the ip address check everywhere except in localhost
               $duplicate = $this->Vote->findCount(array('conditions'=>array(
                    'review_id = ' . $this->data['Vote']['review_id'],
                    'ipaddress = ' . $this->Vote->Quote($this->ipaddress)                    
               )));        
            }
        } 
        else
        // It's a registered user 
        {
            $duplicate = $this->Vote->findCount(array('conditions'=>array(
                'review_id = ' . $this->data['Vote']['review_id'],
                "(user_id = {$this->_user->id}" . 
                    (  
                        $this->ipaddress != '127.0.0.1' && !$this->Config->vote_ipcheck_disable 
                    ? 
                        " OR ipaddress = ". $this->Vote->Quote($this->ipaddress) .") "
                    : 
                        ')' 
                    )
            )));
        }        
        
        if($duplicate>0){
            # Hides vote buttons and shows message alert
            $response[] = "jQuery('#jr_reviewVote{$this->data['Vote']['review_id']}').fadeOut('medium',function(){
                jQuery(this).html('".__t("You already voted.",true,true)."').fadeIn();
            });";            
            return $this->ajaxResponse($response);        
        }   

        if($this->Vote->store($this->data))
        {
            # Hides vote buttons and shows message alert
            $response[] = "jQuery('#jr_reviewVote{$this->data['Vote']['review_id']}').fadeOut('medium',function(){
                jQuery(this).html('".__t("Thank you for your vote.",true,true)."').fadeIn();
            });";            
            
            # Facebook wall integration only for positive votes
            $facebook_integration = Sanitize::getBool($this->Config,'facebook_enable') && Sanitize::getBool($this->Config,'facebook_votes');
            $facebook_integration and $this->data['Vote']['vote_yes'] and $response[] = "
                jQuery.ajax({url:s2AjaxUri+jreviews.ajax_params()+'&url=facebook/_postVote/id:{$this->data['Vote']['review_id']}',dataType:'script'});
            ";
            return $this->ajaxResponse($response);
        }

        return $this->ajaxError(s2Messages::submitErrorDb());        
    }
}
