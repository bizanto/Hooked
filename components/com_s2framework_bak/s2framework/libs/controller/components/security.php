<?php
/**
 * jReviews - Reviews Extension
 * Copyright (C) 2006-2008 Alejandro Schmeichler
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class SecurityComponent extends S2Component {
			
	function startup(&$controller) 
    {
        $controller->invalidToken = false;

        if(isset($controller->data) && !empty($controller->data)) 
            {
                $this->data = & $controller->data;

                $tokenKeys = cmsFramework::getToken(false);

                if(!empty($tokenKeys))
                {                
                    #Validate token
                    if (!isset($this->data['__Token']['Key']) || !in_array($this->data['__Token']['Key'], $tokenKeys['Keys'])) 
                        {                
                            // pass back to xajax controller action for alert
                            $controller->invalidToken = true;
                        } 
                    else 
                        {
                            # Delete used token from session and post data
                            cmsFramework::removeToken($this->data['__Token']['Key']);
                            unset($this->data['__Token']);
                            unset($this->data['__raw']['__Token']);
                        } 
                }
            }

	}
	
	/**
	 * Used in xajax forms when validation fails because the original token is destroyed
	 *
	 */
	function reissueToken() {
		return cmsFramework::getToken();
	}
}
