<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class AdminRoutesHelper extends MyHelper
{	
	var $helpers = array('html');
	
	var $routes = array(
		'user10'=>'index.php?option=com_users&amp;task=edit&amp;hidemainmenu=1&id=%s',	
        'user15'=>'index.php?option=com_users&amp;view=user&amp;task=edit&cid[]=%s' 
	);

	function user($title,$user_id,$attributes) 
    {
        if($user_id == 0) {
            return '"'.$title.'"';
        }		
		switch(getCmsVersion()) {
			case CMS_JOOMLA10: 
			case CMS_MAMBO46:
				$route = $this->routes['user10'];
				$url = sprintf($route,$user_id); 
			break;			
			case CMS_JOOMLA15:
				$route = $this->routes['user15'];			
				$url = sprintf($route,$user_id); 				
			break;						
		}
        $attributes['sef']=false;
        return $this->Html->link($title,$url,$attributes);

    }
}