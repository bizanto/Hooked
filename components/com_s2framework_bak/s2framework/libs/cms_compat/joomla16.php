<?php
/**
 * jReviews - Reviews Extension
 * Copyright (C) 2006-2009 Alejandro Schmeichler
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class cmsFramework 
{    
    var $scripts;
    var $site_route_init;
    var $sef_plugins = array('sef','sef_advance','shsef','acesef'/*not supported*/);
    var $jMainframe;
    var $newMainframe;
        
    function &getInstance() 
    {
        static $instance = array();

        if (!isset($instance[0]) || !$instance[0]) {
            $instance[0] = new cmsFramework();
        }
        return $instance[0];
    }    
        
    function init(&$object) 
    {     
        global $mainframe;
        $object->_db = &JFactory::getDBO();
        $object->_mainframe = $mainframe;
        $object->_user = &JFactory::getUser();
        $object->_user->group_ids = implode(',',array_keys($object->_user->groups)); /* J16 make group ids easier to compare */           
        $object->_acl = &JFactory::getACL();
    }
    
    function getConnection(&$db)
    {
        return $db->getConnection();
    }
    
    function isAdmin() 
    {        
        global $mainframe;

        if(defined('MVC_FRAMEWORK_ADMIN') /*|| $mainframe->isAdmin()*/) {
            return true;
        } else {
            return false;
        }
    }
    
    function packageUnzip($file,$target)
    {
        jimport( 'joomla.filesystem.file' );
        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.archive' );
        jimport( 'joomla.filesystem.path' );        
        $extract1 = & JArchive::getAdapter('zip');
        $result = @$extract1->extract($file, $target);        
        if($result!=true)
        {      
            require_once (PATH_ROOT . DS . 'administrator' . DS . 'includes' . DS . 'pcl' . DS . 'pclzip.lib.php');
            require_once (PATH_ROOT . DS . 'administrator' . DS . 'includes' . DS . 'pcl' . DS . 'pclerror.lib.php');
            if ((substr ( PHP_OS, 0, 3 ) == 'WIN')) {
                if(!defined('OS_WINDOWS')) define('OS_WINDOWS',1);
            } else {
                if(!defined('OS_WINDOWS')) define('OS_WINDOWS',0);
            }
            $extract2 = new PclZip ( $file );
            $result = @$extract2->extract( PCLZIP_OPT_PATH, $target );            
        }
        unset($extract1,$extract2);
        return $result;
    }
    
    function getDB() {
        $db = &JFactory::getDBO();        
        return $db;
    }
    
    function getTemplate(){      
        return JFactory::getApplication()->getTemplate();
    }
    
    function getUser() 
    {
        return JFactory::getUser();
    }
    
    function addScript($text, $inline=false, $duress = false)
    {
        $_this = & cmsFramework::getInstance();

        if($text != '' && ($duress || !isset($_this->scripts[md5($text)]))) 
        {
            if($inline) 
            {
                echo $text;
            
            } else 
            {
                $doc =& JFactory::getDocument();
                method_exists($doc,'addCustomTag') and $doc->addCustomTag($text);
            }
            
            $_this->scripts[md5($text)] = true;
        }
    }
    
    function getCharset() 
    {        
        return 'UTF-8';
    }
    
    function &getCache($group='')
    {    
        return JFactory::getCache($group);
    }
        
    function cleanCache($group=false)
    {
        $cache =& JFactory::getCache($group);
        $cache->clean($group);
    }    
    
    function getConfig($var, $default = null) 
    {        
        # Will need to add a conversion table for configuration variable names once they start differing between CMSs
        $cmsConfig = RegisterClass::getInstance('JConfig');

        if(isset($cmsConfig->{$var})){
          return $cmsConfig->{$var};
        } else {
          return $default;
        }                             
    }
    
    function setSessionVar($key,$var,$namespace)
    {
        $session =& JFactory::getSession();
        $session->set($key,$var,$namespace);
    }

    function getSessionVar($key,$namespace)
    {
        $session =& JFactory::getSession();
        return $session->get($key, array(), $namespace);
    }   
     
    function getToken($new = true, $app = 'jreviews') 
    {
        if($new) 
        {            
            $token = md5(uniqid(rand(), TRUE));

            $session =& JFactory::getSession();

            $tokenKeys = $session->get('__Token', array(),S2Paths::get($app, 'S2_CMSCOMP'));

            $tokenKeys['Keys'][] = $token;
            
            $session->set('__Token',$tokenKeys,S2Paths::get($app, 'S2_CMSCOMP'));
                                
            return $token;            
        } 
        else 
        {
            $session =& JFactory::getSession();
            $tokenKeys = $session->get('__Token', array(), S2Paths::get($app, 'S2_CMSCOMP'));
            if(!is_array($tokenKeys))
            {
                $tokenKeys = array();
            }
            return $tokenKeys;
        }
    }
    
    function removeToken($token, $app = 'jreviews')
    {
        $tokenKeys = cmsFramework::getToken(false);
        $session =& JFactory::getSession();
        unset($tokenKeys['Keys'][array_search($token,$tokenKeys['Keys'])]);
    }
    
    function localDate($date = 'now', $offset = null, $format = 'M d Y H:i:s') 
    {        
        if(is_null($offset)) {
            $offset = cmsFramework::getConfig('offset')*3600;
        } else {
            $offset = 0;
        }
        
        if($date == 'now') 
        {
            $date = strtotime(gmdate($format, time()));
        } 
        else 
        {
            $date = strtotime($date);
        }        
        $date = $date + $offset;        
        $date = date($format, $date);        
        return $date;        
    }

/* J16 - deprecated */    
/*    function language() 
    {
        $lang = & JFactory::getLanguage();
        return $lang->getBackwardLang();
    }  */
    
    function isRTL()
    {
        $lang    = & JFactory::getLanguage();
        return $lang->isRTL();
    }
        
    function locale() 
    {
        $lang    = & JFactory::getLanguage();
        $locale = $lang->getTag();
        $locale = low(str_replace('_','-',$locale));
        $parts = explode('-',$locale);
        if(count($parts)>1 && $parts[0]==$parts[1]){
            $locale = $parts[0];
        }
        return $locale;
    }
    
    function listImages( $name, &$active, $javascript=NULL, $directory=NULL ) 
    {
        return JHTML::_('list.images', $name, $active, $javascript, $directory);
    }
    
    function listPositions( $name, $active=NULL, $javascript=NULL, $none=1, $center=1, $left=1, $right=1, $id=false ) 
    {
        return JHTML::_('list.positions', $name, $active, $javascript, $none, $center, $left, $right, $id);
    }
    
    /**
     * Check for Joomla/Mambo sef status
     *
     * @return unknown
     */
    function mosCmsSef() {
        return false;
    }        
    
    function meta($type,$text) 
    {
        global $mainframe;
        if($text == '') {
            return;
        }

        switch($type) {
            case 'title':
                $document =& JFactory::getDocument();
                $document->setTitle($text);           
                break;            
            case 'keywords':
            case 'description':
            default:    
                $document = & JFactory::getDocument();
                if($type == 'description') {
                    $document->description = strip_tags($text);
                } else {
                    $document->setMetaData($type,strip_tags($text));
                }
            break;            
        }        
    }
            
    
    function noAccess() 
    {
        echo JText::_('ALERTNOTAUTH');
    }
    
    function formatDate($date) 
    {
        return JHTML::_('date', $date );
    }
    
    /**
     * Different function names used in different CMSs
     *
     * @return unknown
     */
    function reorderList() 
    {
        return 'reorder';
    }
    
    function redirect($url,$msg = '') 
    {
        $url = str_replace('&amp;','&',$url);        
        if (headers_sent()) {     
            echo "<script>document.location.href='$url';</script>\n";
        } else {
            header( 'HTTP/1.1 301 Moved Permanently' );
            header( 'Location: ' . $url );
        }
        exit;
    }
      
    /**
    * Convert relative urls to absolute for use in feeds, emails, etc.
    */
    function makeAbsUrl($url,$options=array())
    {
        $options = array_merge(array('sef'=>false,'ampreplace'=>false),$options);
        $options['sef'] and $url = cmsFramework::route($url);
        $options['ampreplace'] and $url = str_replace('&amp;','&',$url);
        if(!strstr($url,'http')) {
            $url_parts = parse_url(WWW_ROOT);
            $url = $url_parts['scheme'] . '://' . $url_parts['host'] . $url;
        } 
        return $url;        
    }
            
      /**
    * This function is used as a replacement to JRoute::_() to generate sef urls in Joomla admin
    * 
    * @param mixed $urls
    * @param mixed $xhtml
    * @param mixed $ssl
    */
    function siteRoute($urls,$xhtml = true, $ssl = null)
    {
        $mainframe = JFactory::getApplication();
        !is_array($urls) and $urls = array($urls);
        $parsed_urls = array();
        
        // Check if plugins already processed
        $_this = cmsFramework::getInstance();
        if(!is_object($_this->jMainframe))
        {
            // Backup original mainframe object
            $_this->jMainframe= clone($mainframe);

            // Get a new mainframe object to process the sef plugins
            $mainframe = JApplication::getInstance('site');
            $router = $mainframe->getRouter();
            
            $plugins = JPluginHelper::getPlugin('system');
            $total = count($plugins);
            for($i = 0; $i < $total; $i++) {
                $plugins[$i]->type == 'system' 
                    and in_array($plugins[$i]->name,$_this->sef_plugins)
                    and JPluginHelper::importPlugin( 'system', $plugins[$i]->name );
            }
            
            if(class_exists('plgSystemSEF_Advance')) // Newer versions of SEF Advance implement a different trigger
            {
                plgSystemSEF_Advance::trigger();    
            }
            else
            {
                $mainframe->triggerEvent('onAfterInitialise');
            }
                        
            // Store modified mainframe object with sef plugin router
            $_this->newMainframe = $mainframe;
        } 
        else 
        {
            // Load the modified mainframe object with sef plugin router
            $mainframe = $_this->newMainframe;
        }
        
        $router = $mainframe->getRouter();
        
        foreach($urls AS $url)
        {
            $uri = $router->build($url); 
            $parsed_url = $uri->toString();
            $parsed_urls[] = str_replace('/administrator','',$parsed_url);                
        }
        
        // Restore original mainframe object
        $mainframe = $_this->jMainframe;
        
        return count($parsed_urls) == 1 ? array_shift($parsed_urls) : $parsed_urls;
    }    
    
    function route($link, $xhtml = true, $ssl = null) 
    {        
        if(false===strpos($link,'index.php') && false===strpos($link,'index2.php')) 
        {
                $link = 'index.php?'.$link;
        }

        // Check core sef
        $sef = cmsFramework::getConfig('sef');
        $sef_rewrite = cmsFramework::getConfig('sef_rewrite');

        if(false===strpos($link,'option=com_jreviews') && !$sef) 
        {                    
            $url = cmsFramework::isAdmin() ? cmsFramework::siteRoute($link,$xhtml,$ssl) : JRoute::_($link,$xhtml,$ssl);
            if(false === strpos($url,'http')) {
                $parsedUrl = parse_url(WWW_ROOT);
                $port = isset($parsedUrl['port']) && $parsedUrl['port'] != '' ? ':' . $parsedUrl['port'] : '';
                $url = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $port . $url;
            }
            return $url;
        }
        elseif(false===strpos($link,'option=com_jreviews')) 
        {
            $url = cmsFramework::isAdmin() ? cmsFramework::siteRoute($link,$xhtml,$ssl) : JRoute::_($link,$xhtml,$ssl);
            return $url;
        }                    
        
        // Fixes component menu urls with pagination and ordering parameters when core sef is enabled.
        $link = str_replace('//','/',$link);

        if($sef) 
        {  
            if(substr($link,0,10)==='index.php')
            {                
                $link = str_replace('/','%2F',$link);
            }

            // Core sef doesn't know how to deal with colons, so we convert them to something else and then replace them again.
            $link = str_replace(_PARAM_CHAR,'*@*',$link);
            $sefUrl = cmsFramework::isAdmin() ? cmsFramework::siteRoute($link,$xhtml,$ssl) : JRoute::_($link,$xhtml,$ssl);
            $sefUrl = str_replace('%2A%40%2A',_PARAM_CHAR,$sefUrl); 
            $sefUrl = str_replace('*@*',_PARAM_CHAR,$sefUrl); // For non sef links
            $link = $sefUrl;
        } 

        if(false!==strpos($link,'http')) 
            {
                return $link;
            } 
        else 
            {
                $parsedUrl = parse_url(WWW_ROOT);
                $port = isset($parsedUrl['port']) && $parsedUrl['port'] != '' ? ':' . $parsedUrl['port'] : '';                
                $www_root = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $port . ($sef ? _DS : $parsedUrl['path']);                
                return $www_root . ltrim($link, _DS);
            } 
    }
        
    function constructRoute($passedArgs,$excludeParams = null, $app = 'jreviews') 
    {
        $segments = '';
        $url_param = array();

        if(defined('MVC_FRAMEWORK_ADMIN')) 
        {
            $base_url = 'index2.php?option='.S2Paths::get($app, 'S2_CMSCOMP');                    
        } else 
        {
            $Itemid = Sanitize::getInt($passedArgs,'Itemid') > 0 ? Sanitize::getInt($passedArgs,'Itemid') : '';
            $base_url = 'index.php?option='.S2Paths::get($app, 'S2_CMSCOMP').'&amp;Itemid=' . $Itemid;
        }
        
        // Get segments without named params
        if(isset($passedArgs['url'])) {
            $parts = explode('/',$passedArgs['url']);
            foreach($parts AS $bit) {
                if(false===strpos($bit,_PARAM_CHAR)) {
                    $segments[] = $bit;
                }
            }
        } else {
            $segments[] = 'menu';
        }
        
        unset($passedArgs['option'], $passedArgs['Itemid'], $passedArgs['url']);
        if(is_array($excludeParams)) {
            foreach($excludeParams AS $exclude) {
                unset($passedArgs[$exclude]);        
            }
        }
        
        foreach($passedArgs AS $paramName=>$paramValue) {
            if(is_string($paramValue)){
                $url_param[] = $paramName . _PARAM_CHAR . urlencodeParam($paramValue);
            }
        }        
        
        $new_route = $base_url . '&amp;url=' . implode('/',$segments) . '/' . implode('/',$url_param);
        
        return $new_route;    
    }
    
    /**
    * Overrides CMSs breadcrumbs
    * $paths is an array of associative arrays with keys "name" and "link"
    */   
    function setPathway($crumbs) 
    {
        global $mainframe;
        foreach($crumbs AS $key=>$crumb)
        {
            $crumbs[$key] = (object)$crumb;
        }
        $app = &JFactory::getApplication();
        $pathway = &$app->getPathway();
        $pathway->setPathway($crumbs);        
    }
}