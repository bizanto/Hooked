<?php
/**
 * jReviews - Reviews Extension
 * Copyright (C) 2006-2008 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

(defined( '_VALID_MOS') || defined( '_JEXEC')) or die( 'Direct Access to this location is not allowed.' );

# Only run in frontend
if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'],'administrator')) {
    return;
}

if ((string) @$_GET['option'] != 'com_content' && (string) @$_GET['option'] != 'com_frontpage' && (string) @$_GET['option'] != '') {
    return;
}
    
# MVC initalization script
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if(defined('JPATH_SITE')){    
    $root = JPATH_SITE . DS;
} else {
    global $mainframe;
    $root = $mainframe->getCfg('absolute_path') . DS;
}
require($root . 'components' . DS . 'com_jreviews' . DS . 'jreviews' . DS . 'framework.php');

cmsFramework::init($CMS);
        
$option = Sanitize::getString($_REQUEST, 'option', '');
$task = Sanitize::getString($_REQUEST, 'task', '');
$view = Sanitize::getString($_REQUEST, 'view', '');
$layout = Sanitize::getString($_REQUEST, 'layout', '');
$id = explode(':',Sanitize::getInt($_REQUEST,'id'));
$id = $id[0];

# Plugins table
if(!defined('_PLUGIN_DIR_NAME')) 
{
    if(getCmsVersion() == CMS_JOOMLA15) {
        define('_PLUGIN_DIR_NAME','plugins');
    } else {
        define('_PLUGIN_DIR_NAME','mambots');
    }
}

$query = "SELECT published,params FROM #__"._PLUGIN_DIR_NAME." WHERE element = 'jreviews' AND folder = 'content' LIMIT 1";
$CMS->_db->setQuery($query);
$jrbot = current($CMS->_db->loadObjectList());

$params = stringToArray($jrbot->params);
$published = $jrbot->published;

if ((int) !$published) {
    return;
}
$frontpageOff =  Sanitize::getVar($params,'frontpage');
$blogLayoutOff =  Sanitize::getVar($params, 'blog');

# Get theme, suffix and load CSS so it's not killed by the built-in cache

if(getCmsVersion() == CMS_JOOMLA10 || getCmsVersion() == CMS_MAMBO46) {
    if (($option=='com_content' && ($task=='category' || $task=='section' || ($blogLayoutOff && $task=='blogsection') || ($blogLayoutOff && $task=='blogcategory'))) || ($frontpageOff && $option == 'com_frontpage')) {
        return;
    }
} elseif ($blogLayoutOff && $option=='com_content' && ($view == 'category' || $view == 'section') && ($layout == 'blog' || $layout == 'blogfull')) {
        return;
} elseif (($frontpageOff && $view == 'frontpage')) {
    return ;
}

jimport('joomla.plugin.plugin');

class plgContentJreviews extends JPlugin
{
    function plgContentJreviews(& $subject, $params )
    {        
        parent::__construct( $subject, $params );
    }

    function onBeforeDisplayContent( &$article, &$params)
    {      
        if (!class_exists('cmsFramework')) return;

       // Make sure this is a Joomla article page
        $option = Sanitize::getString($_REQUEST, 'option', '');
        $view = Sanitize::getString($_REQUEST, 'view', '');
        $layout = Sanitize::getString($_REQUEST, 'layout', '');
        $id = Sanitize::getInt($_REQUEST,'id');

        if(!($option == 'com_content' && $view == 'article' && $id)) return;
        
        /**
        * Retrieve $listing array from memory 
        */
        $_this = &cmsFramework::getInstance();
        
        $Config = Configure::read('JreviewsSystem.Config');

        $title = trim(Sanitize::getString($Config,'type_metatitle'));
        $keywords = trim(Sanitize::getString($Config,'type_metakey'));
        $description = trim(Sanitize::getString($Config,'type_metadesc'));

        $listing = &$_this->listing; // Has all the data that's also available in the detail.thtml theme file so you can create any sort of conditionals with it
       
        if($title != '' || $keywords != '' || $description != '')
        {                  
            
            if(isset($_this->listing) && is_array($_this->listing))
            {
                // Instantiate the CustomFields helper class
                $CustomFields = &RegisterClass::getInstance('CustomFieldsHelper');

                // Get and process all tags
                $tags = plgContentJreviews::extractTags($title.$keywords.$description);
                $tags_array = array();
                foreach($tags AS $tag)
                {
                    switch($tag)
                    {
                        case 'title':
                            $tags_array['{title}'] = Sanitize::stripAll($listing['Listing'],'title'); 
                        break;
                        case 'section':
                            $tags_array['{section}'] = Sanitize::stripAll($listing['Section'],'title'); 
                        break;
                        case 'category':
                            $tags_array['{category}'] = Sanitize::stripAll($listing['Category'],'title'); 
                        break;
                        case 'metakey':
                            $tags_array['{metakey}'] = Sanitize::stripAll($listing['Listing'],'metakey');                
                        break;
                        case 'metadesc':
                            $tags_array['{metadesc}'] = Sanitize::stripAll($listing['Listing'],'metadesc');
                        break;
                        default:
                            if(substr($tag,0,3) == 'jr_')
                            {
                                $field = $CustomFields->fieldText($tag,$listing,false,false,','/*separator for multiple choice fields*/);
                                $tags_array['{'.$tag.'}'] = !empty($field) ? $field : '';
                            }
                        break;
                    }                    
                }

                # Process title             
                $title != '' and $title = str_replace(array_keys($tags_array),$tags_array,$title) and cmsFramework::meta('title', $title); 

                # Process description
                $description != '' and $description= str_replace(array_keys($tags_array),$tags_array,$description) and cmsFramework::meta('description', $description);

                # Process keywords
                $keywords != '' and $keywords = str_replace(array_keys($tags_array),$tags_array,$keywords) and cmsFramework::meta('keywords', $keywords);
            }            
        } 
        elseif(
            isset($article->parameters) 
            && $article->parameters->get('show_page_title') 
            && $article->parameters->get('num_leading_articles') == '' /* run only if it's an article menu */
            && $article->parameters->get('filter_type') == '' /* run only if it's an article menu */
        ) {                    
                $title = $article->parameters->get('page_title');
                $title != '' and cmsFramework::meta('title', $title);
        }

        if(isset($_this->crumbs) && !empty($_this->crumbs))
        {
            cmsFramework::setPathway($_this->crumbs);
        }
        unset($_this);
    }        
    
    function onPrepareContent( &$article, &$params)
    {      
        if (!class_exists('cmsFramework')) return;

        // Check whether to perform the replacement or not
        $option = Sanitize::getString($_REQUEST, 'option', '');
        $view = Sanitize::getString($_REQUEST, 'view', '');
        $layout = Sanitize::getString($_REQUEST, 'layout', '');
        $id = Sanitize::getInt($_REQUEST,'id');
                                          
        if(
            $option == 'com_content' 
            && 
            in_array($view,array('article','category','section','frontpage')) 
            && ($layout != '' || in_array($view,array('article','frontpage')))
        ) 
        {
            $row = &$article;
            if(
                (isset($row->params) || isset($row->parameters))
                && isset($row->id) 
                && isset($row->catid) 
                && isset($row->sectionid) 
                && $row->id > 0 
                && $row->catid > 0 
                && $row->sectionid > 0
            ) {

                $Dispatcher = new S2Dispatcher('jreviews',true);

                if ($option=='com_content' && $view == 'article' & $id > 0) {
                    
                    $_GET['url'] = 'com_content/com_content_view';

                } elseif ($option=='com_content' && ((($layout == 'blog' || $layout == 'blogfull') && ($view=='category' || $view=='section')) || $view == 'frontpage')) {

                    $_GET['url'] = 'com_content/com_content_blog';

                } 
                
                $passedArgs = array(
                    'params'=>$params,
                    'row'=>$row,
                    'component'=>'com_content'
                    );

                $passedArgs['cat'] = $row->catid;
                $passedArgs['section'] = $row->sectionid;
                $passedArgs['listing_id'] = $row->id;

                $output = $Dispatcher->dispatch($passedArgs);

                if($output){
                    $row = &$output['row'];
                    $params = &$output['params'];
                }

                /**
                * Store a copy of the $listing and $crumbs arrays in memory for use in the onBeforeDisplayContent method
                */
                $_this = &cmsFramework::getInstance();
                $_this->listing = &$output['listing'];
                $_this->crumbs = &$output['crumbs'];
                // Destroy pathway
                if(isset($_this->crumbs) && !empty($_this->crumbs))
                {
                    cmsFramework::setPathway(array());
                }
                unset($Dispatcher);
            }
            
        }
    }
    
    function extractTags($text) 
    {
        $pattern = '/{([a-z0-9_|]*)}/i';

        $matches = array();

        $result = preg_match_all( $pattern, $text, $matches );

        if( $result == false ) {        
            return array();
        }

        return array_unique(array_values($matches[1]));
    }    
}