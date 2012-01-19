<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );
                  
class CommunityHelper extends MyHelper {
                                     
    var $helpers = array('html');
    
    function profileLink($name,$user_id,$menu_id) 
    {
        if($user_id > 0) {
            $community_url = Configure::read('Community.profileUrl');
            $url = sprintf($community_url,$user_id,$menu_id);            
            return $this->Html->sefLink($name,$url,array(),false);
        }else {
            return $name;
        }
    }    
    
    function avatar($entry) 
    {
        if(isset($entry['Community']) && $entry['User']['user_id'] > 0) 
        {
            $screenName = $this->screenName($entry,null,false);
                       
            if(isset($entry['Community']['avatar_path']) && $entry['Community']['avatar_path'] != '') {
                return $this->profileLink($this->Html->image($entry['Community']['avatar_path'],array('class'=>'jr_avatar','alt'=>$screenName,'border'=>0)),$entry['Community']['community_user_id'],$entry['Community']['menu_id']);
            } else {
                return $this->profileLink($this->Html->image($this->viewImages.'tnnophoto.jpg',array('class'=>'jr_avatar','alt'=>$screenName,'border'=>0)),$entry['Community']['community_user_id'],$entry['Community']['menu_id']);
            }
        }
    }
    
    function screenName(&$entry, $link = true) 
    {
        // $Config param not being used
        $screenName = $this->Config->name_choice == 'realname' ? $entry['User']['name'] : $entry['User']['username'];

        if($link && !empty($entry['Community']) && $entry['User']['user_id'] > 0) {
            return $this->profileLink($screenName,$entry['Community']['community_user_id'],$entry['Community']['menu_id']);
        } 
        
        $screenName = $screenName == '' ? __t("Guest",true) : $screenName;
        
        return $screenName;
    }    
    
}