<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class CommunityModel extends MyModel  {
    
    var $name = 'Community';
    
    var $useTable = '#__community_users AS Community';
    
    var $primaryKey = 'Community.user_id';
    
    var $realKey = 'userid';

    var $community = false;
    
    var $profileUrl = 'index.php?option=com_community&view=profile&userid=%s&Itemid=%s';
        
    var $registerUrl = 'index.php?option=com_community&amp;view=register&amp;Itemid=%s';   
        
    var $menu_id;
    
    function __construct(){    
                        
        parent::__construct();
        
        Configure::write('Community.profileUrl',$this->profileUrl);
        
        if (file_exists(PATH_ROOT . 'components' . _DS . 'com_community' . _DS . 'community.php')) {
            $this->community = true;
        
            $Menu = registerClass::getInstance('MenuModel');

            $this->menu_id = $Menu->getComponentMenuId('com_community&view=profile');
            
            if(!$this->menu_id)
            {
                $this->menu_id = $Menu->getComponentMenuId('com_community');
            }
            
            Configure::write('Community.register_url',sprintf($this->registerUrl,$this->menu_id));
        }
    
    }
    
    function getListingFavorites($listing_id, $user_id, $passedArgs) 
    {                
        $conditions = array();
        $avatar    = Sanitize::getInt($passedArgs['module'],'avatar',1); // Only show users with avatars
        $count     = Sanitize::getInt($passedArgs['module'],'module_limit',5);
        $module_id = Sanitize::getInt($passedArgs,'module_id');
        $rand = Sanitize::getFloat($passedArgs,'rand');

        $fields = array(
            'Community.'.$this->realKey. ' AS `User.user_id`',
            'User.name AS `User.name`',
            'User.username AS `User.username`'
        );
                
        if($avatar) {
            $conditions[] = 'Community.thumb <> "components/com_community/assets/default_thumb.jpg"';
        }
        
        if($listing_id) {
            $conditions[] = 'Community.'.$this->realKey. ' in (SELECT user_id FROM #__jreviews_favorites WHERE content_id = ' . $listing_id . ')';
        }
        
        $order = array('RAND('.$rand.')');

        $joins = array('LEFT JOIN #__users AS User ON Community.'.$this->realKey. ' = User.id');
            
        $profiles = $this->findAll(array(
            'fields'=>$fields,
            'conditions'=>$conditions,
            'order'=>$order,
            'joins'=>$joins
        ));

        if(Sanitize::getInt($passedArgs['module'],'ajax_nav',1)) {
            $fields = array('count(Community.'.$this->realKey. ')');
            $group = array('Community.'.$this->realKey);
            
            $this->count = $this->findCount(array(
                'fields'=>$fields,
                'conditions'=>$conditions,
                'group'=>$group,
                'joins'=>$joins
            ));
        } else {
            $this->count = Sanitize::getInt($passedArgs['module'],'module_limit',5);
        }

        return $this->addProfileInfo($profiles,'User','user_id');

    }

    function __getOwnerIds($results, $modelName, $userKey) {
        
        $owner_ids = array();
        
        foreach($results AS $result) {
            // Add only if not guests
            if($result[$modelName][$userKey]) {
                $owner_ids[] = $result[$modelName][$userKey];
            }
            
        }

        return array_unique($owner_ids);
    }
    
    function addProfileInfo($results, $modelName, $userKey)
    {

        if(!$this->community) {
            return $results;
        }
        
        $owner_ids = $this->__getOwnerIds($results, $modelName, $userKey);

        if(empty($owner_ids)) {
            return $results;
        }
        
        unset($this->limit);
        unset($this->offset);

        $profiles = $this->findAll(array(
            'fields'=>array('Community.userid AS `Community.user_id`','Community.thumb AS `Community.avatar`'),
            'conditions'=>array($this->realKey . ' IN (' . implode(',',$owner_ids) . ')'),        
        ));

        $profiles = $this->changeKeys($profiles,$this->name,'user_id');

        $menu_id = $this->menu_id;
        
        # Add avatar_path to Model results
        foreach ($profiles AS $key=>$value) 
        {    
            $profiles[$value[$this->name][$userKey]][$this->name]['community_user_id'] = $value[$this->name]['user_id'];
            $avatar = $profiles[$value[$this->name][$userKey]][$this->name]['avatar'];
            $avatar == '' and $avatar = 'components/com_community/assets/default_thumb.jpg';
            $profiles[$value[$this->name][$userKey]][$this->name]['avatar_path'] = WWW_ROOT. $avatar;
        }

        # Add Community Model to parent Model
        foreach ($results AS $key=>$result) {

            if(isset($profiles[$results[$key][$modelName][$userKey]])) {
                $results[$key] = array_merge($results[$key], $profiles[$results[$key][$modelName][$userKey]]);
            }
                
            $results[$key][$this->name]['menu_id'] = $menu_id;
            
        }

        return $results;        
    }    
    
}
