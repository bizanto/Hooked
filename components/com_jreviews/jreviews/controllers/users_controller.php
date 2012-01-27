<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2009 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class UsersController extends MyController {
        
    var $uses = array('menu','user');
    
    var $helpers = array();
    
    var $components = array('config','access');

    var $autoRender = false;
    
    var $autoLayout = false;
                
    function beforeFilter() 
    {        
        # Call beforeFilter of MyController parent class
        parent::beforeFilter();
    }    
    
    function _getList()
    {      
        $this->Access->init($this->Config);
        if(!$this->_user || !$this->Access->isEditor()) return;
        
        $query = $this->User->makeSafe(strtolower(Sanitize::getString($this->params,'q')));
        if (!$query) return;

        $fields = array('
            User.id AS `User.user_id`,
            User.name AS `User.name`,
            User.username AS `User.username`,
            User.email AS `User.email`
        ');
        
        $users = $this->User->findAll(array(
            'fields'=>$fields,
            'conditions'=>array(
                "User.username LIKE '%{$query}%' OR User.name LIKE '%{$query}%'"
            )
        ));
        
        foreach ($users as $user) {
            echo "{$user['User']['name']}|{$user['User']['user_id']}|{$user['User']['username']}|{$user['User']['email']}\n";
        }         
    }    
}