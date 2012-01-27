<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class VoteModel extends MyModel  {
		
	var $name = 'Vote';
	
	var $useTable = '#__jreviews_votes AS `Vote`';
	
    var $primaryKey = 'Vote.vote_id';
    
    var $realKey = 'vote_id'; 
    
    function afterSave($status)
    {
        if($status)
        {            
            // Update vote count in review table
            App::import('Model','review','jreviews');
            $Review = RegisterClass::getInstance('ReviewModel');
            $Review->updateVoteHelpfulCount($this->data['Vote']['review_id'],$this->data['Vote']['vote_yes']);                    
        }                
    }       
}