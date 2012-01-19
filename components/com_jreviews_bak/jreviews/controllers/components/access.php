<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2009 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );
                     
class AccessComponent extends S2Component {
	
    var $gid = null;    
	// Joomla/Mambo groups
    var $superusers = array(20,21,23,24,25); // Includes editor and above
    var $publishers = array(21,23,24,25); // Includes publisher and above
    var $managers = array(23,24,25); // Includes mabager and above
    var $admins = array(24,25); // admin, superadmin
    var $members = array(18,19,20,21,23,24,25); // Registered users and above
    
	// jReviews access
	var $canAddMeta = null;
	var $canAddReview = null;
	var $showCaptcha = null;
	
	var $Config;
	var $_user;
	var $_db;
	
	function startup(&$controller) 
    {          
		$this->_user = &$controller->_user;
		$this->_db = &$controller->_db;
	}

	function init(&$Config) 
    {         
		$this->Config = &$Config;
		// jReviews
		$this->gid = (int)$this->getGroupId($this->_user->id);
		$this->canAddMeta = $this->canAddMeta(); // Deprecated       		
		$this->canAddReview = $this->canAddReview(); // Deprecated		
		$this->showCaptcha = $this->showCaptcha();
        Configure::write('JreviewsSystem.Access',$this);
    }
    
    function showCaptcha()
    {                         
        return $this->Config->security_image == '' ? false : in_array($this->gid, explode(',',$this->Config->security_image));        
    } 
	
	function loadWysiwygEditor() 
    {	
		return in_array($this->gid, explode(',',Sanitize::getVar($this->Config,'addnewwysiwyg')));				
	}

    function isAdmin()
    {
        return in_array($this->gid,$this->admins);
    }

    function isEditor()
    {        
        return in_array($this->gid,$this->superusers);
    }

    function isManager()
    {
        return in_array($this->gid,$this->managers);
    }
            
    function isMember()
    {
        return in_array($this->gid,$this->members);
    }

    function isPublisher()
    {
        return in_array($this->gid,$this->publishers);
    }

	function isJreviewsEditor($user_id)
    {
		$jr_editor_ids = is_integer($this->Config->authorids) ? array($this->Config->authorids) : explode(',',$this->Config->authorids); 
		if($this->Config->author_review && $user_id > 0 && in_array($user_id,$jr_editor_ids)){
			return true;
		}
		return false;
	}
    
	function in_groups($groups) 
    {
		if($groups == 'all') return true;
        return in_array($this->gid, explode(',',$groups));
	}

	function getGroupId($user_id) 
    {
		if (!$user_id) {
			return false;
		}
		$query = "SELECT gid FROM #__users WHERE id = " . $user_id;
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}
    
    function canAddListing($override = null)
    {           
        $groups = !is_null($override) && $override != -1 ? $override : $this->Config->addnewaccess;
        return $groups !='' && in_array($this->gid, explode(',',$groups)); 
    }
    
    function canAddMeta()
    {
        return $this->Config->addnewmeta!='' && in_array($this->gid, explode(',',$this->Config->addnewmeta));
    }
    
    function moderateListing()
    {
        return $this->Config->moderation_item!='' && in_array($this->gid, explode(',',$this->Config->moderation_item));
    }
    
    function canVoteHelpful($reviewer_id = null) 
    {
        if($reviewer_id && $reviewer_id == $this->_user->id) return false;
        return $this->Config->user_vote_public!='' && in_array($this->gid, explode(',',$this->Config->user_vote_public));
    }
    
    function canEditListing($owner_id = null)
    {                          
         return $this->canMemberDoThis($owner_id,'editaccess');
    }
    
    function canPublishListing($owner_id)
    {        
         return $this->canMemberDoThis($owner_id,'listing_publish_access');
    }   
    
    function canDeleteListing($owner_id)
    {      
         return $this->canMemberDoThis($owner_id,'listing_delete_access');
    }     

    function canAddReview($owner_id = null)
    {            
        if(
            // First check the access groups        
            (!in_array($this->gid, explode(',',$this->Config->addnewaccess_reviews)) || $this->Config->addnewaccess_reviews == 'none')
            ||
            // If it's not a jReviewsEditor then check the owner listing
            (!$this->isJreviewsEditor($this->_user->id) && $this->Config->user_owner_disable && !is_null($owner_id) && $owner_id != 0 && $this->_user->id == $owner_id)            
        ) {
            return false;
        }        
        return true;
    }
    
    function canEditReview($owner_id) 
    {     
        return $this->canMemberDoThis($owner_id,'editaccess_reviews');
    }     
        
    function moderateReview()
    {
        return $this->Config->moderation_reviews!='' && in_array($this->gid, explode(',',$this->Config->moderation_reviews)); 
    }   
     
    function canAddPost()
    {
        return $this->Config->addnewaccess_posts!='' && in_array($this->gid, explode(',',$this->Config->addnewaccess_posts));
    }        
    
    function canEditPost($owner_id)
    {        
        return $this->canMemberDoThis($owner_id,'post_edit_access');
    }
    
    function canDeletePost($owner_id)
    {        
        return $this->canMemberDoThis($owner_id,'post_delete_access');
    }
    
    function moderatePost()
    { 
        return $this->Config->moderation_posts!='' && in_array($this->gid, explode(',',$this->Config->moderation_posts)) ? true : false;   
    }  
    
    function canAddOwnerReply(&$listing,&$review) 
    {
        return $this->_user->id >0 
            && isset($listing['User']['user_id']) && $this->Config->owner_replies 
            && $review['Review']['editor']==0 
            && $review['Review']['owner_reply_approved']<=0
            && $listing['User']['user_id'] == $this->_user->id
        ;
    } 
    
    function canClaimListing(&$listing) 
    {
        return $this->Config->claims_enable 
            && $this->_user->id > 0
            && ($listing['Listing']['user_id'] != $this->_user->id)
            && $listing['Claim']['approved']<=0 
            && (
                $this->Config->claims_enable_userids == ''
                || (
                    $this->Config->claims_enable_userids != ''
                    &&
                    in_array($listing['Listing']['user_id'],explode(',',$this->Config->claims_enable_userids))
                )
            )
        ;
    }     
 
 // Wrapper functions
    function canMemberDoThis($owner_id ,$config_setting)
    {
        $allowedGroups = explode(',',$this->Config->{$config_setting});
        if ($this->_user->id == 0 || !$this->gid) {
            return false;            
        } elseif (
            (in_array($this->gid,$this->superusers) && in_array($this->gid,$allowedGroups))
            ||        
            ($this->_user->id == $owner_id && $owner_id >0 && in_array($this->gid,$allowedGroups))
        ) {            
            return true;        
        }
        return false;
    }
    
}
