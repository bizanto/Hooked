<?php
/**
 * jReviews - Reviews Extension
 * Copyright (C) 2006-2008 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');

class plgCommunityJreviews_myfavorites extends CApplications
{

    var $name         = "MyFavorites";
    var $_name        = 'myfavorites';
    var $_path        = '';
    var $_user        = '';
    var $_my        = '';

    function plgCommunityJreviews_myfavorites(& $subject, $config)
    {
        $this->_path    = JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_jreviews';
        $this->_user    = CFactory::getActiveProfile();
        $this->_my        = CFactory::getUser();
            
        parent::__construct($subject, $config);
    }

    function onProfileDisplay()
    {
        if( !file_exists( $this->_path . DS . 'admin.jreviews.php' ) ){
            return JText::_('jReviews is not installed. Please contact site administrator.');
        }else{
            $user        = CFactory::getActiveProfile();
            $userId = $user->id;
            
            $cacheSetting = $this->params->get('cache', 1) ? JApplication::getCfg('caching') : 0;            
            
            # Load CSS stylesheets -- done here because when cache is on css is not loaded
            if($cacheSetting) {
                # MVC initalization script
                if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);    
                require('components' . DS . 'com_jreviews' . DS . 'jreviews' . DS . 'framework.php');
    
                $cache_file = 'jreviews_config_'.md5(cmsFramework::getConfig('secret'));            
                $Config = &S2Cache::read($cache_file);
                if(!is_object($Config))
                {
                    //Create config file
                    $eParams['data']['controller'] = 'common';
                    $eParams['data']['action'] = 'index';
                    $Dispatcher = new S2Dispatcher('jreviews',false, true);
                    $Dispatcher->dispatch($eParams);                    
                    $Config = &S2Cache::read($cache_file);
                    unset($Dispatcher);
                }                
                App::import('Helper','html');
                $Html = &RegisterClass::getInstance('HtmlHelper');
                $Html->viewTheme = $Config->template;
                $Html->app = 'jreviews';        
                $Html->startup();        
                App::import('Helper','libraries','jreviews');
                $Libraries = &RegisterClass::getInstance('LibrariesHelper');
                $Html->css(array('theme','plugins','paginator'));                
                $Html->js(array('jreviews','jquery'=>'jquery','jq.scrollable'), $Libraries->js());
            }
            
            $cache =& JFactory::getCache('plgCommunityJreviews_myfavorites');
            $cache->setCaching($cacheSetting);
            $callback = array('plgCommunityJreviews_myfavorites', '_getPage');
            $contents = $cache->call($callback, $userId, $this->params, $cacheSetting);
            return $contents;                                        
        }    
    }
    
    function _getPage($userId, $params, $cacheSetting)
    {
        if(!$cacheSetting) {
                # MVC initalization script
                if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);    
                require('components' . DS . 'com_jreviews' . DS . 'jreviews' . DS . 'framework.php');            
        }
        
        Configure::write('Libraries.disableJS',array('jquery'));
        
        # Populate $params array with module settings
        $eParams['page'] = 1;
        $eParams['user'] = $userId;        
        $eParams['module'] = stringToArray($params->_raw);        
        $eParams['module']['community'] = true;
        $eParams['module_id'] = 'plugin_myfavorites'.$userId;
        $eParams['page'] = 1;
        $eParams['data']['module'] = true;    
            
        $eParams['data']['controller'] = 'community_listings';
        $eParams['data']['action'] = 'favorites';
        $eParams['data']['module_limit'] = $params->get('limit',10);
        
        $Dispatcher = new S2Dispatcher('jreviews',true, false);
        return $Dispatcher->dispatch($eParams);    
    }
    
}
