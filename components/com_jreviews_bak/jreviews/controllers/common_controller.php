<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class CommonController extends MyController {
    
    var $uses = array();
    
    var $helpers = array();
    
    var $components = array('config');
        
    var $autoRender = false; //Output is returned
    
    var $autoLayout = false;
        
    function beforeFilter() 
    {
        # Call beforeFilter of MyController parent class
        parent::beforeFilter();
    }        
 
    /**
    * Called in community plugins to initialize $Config object if Joomla cache is enabled
    * and JReviews cache has been cleared
    * Don't add anything to this method!
    *         
    */
    function index() {}

    /**
    * Category auto-detect
    */
    function _discoverIDs(&$controller)
    {             
        // Initialize variables        
        $id = Sanitize::getInt($controller->params,'id');        
        $cat_id = Sanitize::getInt($controller->params,'catid');        
        $option = Sanitize::getString($controller->params,'option');        
        $view = Sanitize::getString($controller->params,'view');
        $task = Sanitize::getString($controller->params,'task');
        switch($option) 
        {
            case 'com_jreviews':
                # Get url params for current controller/action
                $url = Sanitize::getString($controller->passedArgs,'url');
                $route['url']['url'] = $url;
                $route = S2Router::parse($route,true,'jreviews');
                isset($route['data']['action']) and $route['data']['action'] == 'search' and $route = $route['url'];         

                $dir_id = Sanitize::getString($route,'dir');
                $section_id = Sanitize::getString($route,'section');
                $cat_id = Sanitize::getString($route,'cat');
                $criteria_id = Sanitize::getString($route,'criteria');

                if ($cat_id != '') 
                {
                    if($cat_id{0}=='s') 
                    {           
                        $section_id =  CommonController::makeModParamsUsable(str_replace('s','',$cat_id)); 
                        $cat_id = ''; 
                        break;                            
                    }                               
                    $cat_id = CommonController::makeModParamsUsable($cat_id);
                } 
                elseif ($section_id != '') 
                {                          
                    $section_id = CommonController::makeModParamsUsable($section_id);
                } 
                elseif($criteria_id != '')    
                { 
                    $criteria_id = CommonController::makeModParamsUsable($criteria_id);
                } 
                elseif($dir_id != '')    
                { 
                    $dir_id = CommonController::makeModParamsUsable($dir_id);
                } 
                else 
                { //Discover the params from the menu_id
                
                    $menu_id = Sanitize::getString($controller->params,'Itemid');
                    $params = $controller->Menu->getMenuParams($menu_id);
                    $dir_id = cleanIntegerCommaList(Sanitize::getString($params,'dirid'));
                    $cat_id = cleanIntegerCommaList(Sanitize::getString($params,'catid'));
                    $section_id = cleanIntegerCommaList(Sanitize::getString($params,'sectionid'));
                }
                break;
    
            case 'com_content':
    
                    if ('article' == $view || 'view' == $task) 
                    {
                        // If cat id was not available in url then we need to query it, otherwise it was already read above
                        if(!$cat_id)
                        {
                            $query = "
                                SELECT 
                                    catid 
                                FROM 
                                    #__content
                                WHERE 
                                    id = " . $id
                             ;
                             $this->_db->setQuery($query);
                             $cat_id = $this->_db->loadResult();    
                        }
                    } 
                    elseif ($view=="section") 
                    {
                        $section_id = $id;
                    } 
                    elseif ($view=="category") 
                    {
                        $cat_id = $id;
                    }
                break; 
    
            default:
                $cat_id = null; // Catid not detected because the page is neither content nor jreviews
                break;
        }
       
        $ids = array();
        isset($dir_id) and !empty($dir_id) and $ids['dir_id'] = $dir_id; 
        isset($section_id) and !empty($section_id) and $ids['section_id'] = $section_id; 
        isset($cat_id) and !empty($cat_id) and $ids['cat_id'] = $cat_id; 
        isset($criteria_id) and !empty($criteria_id) and $ids['criteria_id'] = $criteria_id; 
        return $ids;
    }
    
    /**
    * Used in modules
    * 
    * @param mixed $param
    * @return string
    */
    function makeModParamsUsable($param) 
    {                                  
        if(empty($param)) return null;
        $urlSeparator = "_";
        return cleanIntegerCommaList(str_replace($urlSeparator,",",urldecode($param)));
    }    
    
}
