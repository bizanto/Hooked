<?php
/**
 * jReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

(defined( '_VALID_MOS') || defined( '_JEXEC')) or die( 'Direct Access to this location is not allowed.' );

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

if(isset($_GET['update']) && $_GET['update'] == 1)
{
    $path_root = dirname(dirname(dirname(dirname(__FILE__))));
    $path_app_admin = $path_root . DS . 'administrator' . DS . 'components' . DS . 'com_jreviews' . DS;
    $package = $path_app_admin . 'jreviews.s2';
    $target = $path_root . DS . 'components' . DS . 'com_jreviews' . DS;    

    if(file_exists($package))
    { // Install app
        if (!ini_get('safe_mode')) {
            set_time_limit(2000);
        }                
        
        # Multi CSM constants
        if(!defined('CMS_JOOMLA15')) define('CMS_JOOMLA15','CMS_JOOMLA15');
        if(!defined('CMS_JOOMLA10')) define('CMS_JOOMLA10','CMS_JOOMLA10');
        if(!defined('CMS_MAMBO46'))     define('CMS_MAMBO46','CMS_MAMBO46');    
                            
        if(getCmsVersionInstall() == CMS_JOOMLA15) 
        {
            jimport( 'joomla.filesystem.file' );
            jimport( 'joomla.filesystem.folder' );
            jimport( 'joomla.filesystem.archive' );
            jimport( 'joomla.filesystem.path' );

            $adapter = & JArchive::getAdapter('zip');
            $result = @$adapter->extract($package, $target);
        } 
        else 
        {
            require_once ($path_root . DS . 'administrator' . DS . 'includes' . DS . 'pcl' . DS . 'pclzip.lib.php');
            require_once ($path_root . DS . 'administrator' . DS . 'includes' . DS . 'pcl' . DS . 'pclerror.lib.php');

            $extract = new PclZip ( $package );
            
            if ((substr ( PHP_OS, 0, 3 ) == 'WIN')) {
                if(!defined('OS_WINDOWS')) define('OS_WINDOWS',1);
            } else {
                if(!defined('OS_WINDOWS')) define('OS_WINDOWS',0);
            }
                    
            $result = @$extract->extract( PCLZIP_OPT_PATH, $target );        
        }

        @unlink($package);    
        
        if($result)
        {
            echo json_encode(array('error'=>false,'html'=>'<div style="color:green;">New package extracted successfully.</div>'));
        } 
        else 
        {
            echo json_encode(array('error'=>true,'html'=>'<div style="color:red;">There was a problem extracting the files from the downloaded package.</div>'));
        }
    }    
} 
else
{
    $path_root = dirname(dirname(dirname(dirname(__FILE__))));
    $path_app_admin = $path_root . DS . 'administrator' . DS . 'components' . DS . 'com_jreviews' . DS;

    $package = $path_app_admin . 'jreviews.s2';
    $target = $path_root . DS . 'components' . DS . 'com_jreviews' . DS;
        
    define('MVC_FRAMEWORK_ADMIN',1);
        
    // If framework and app installed, then run app
    if(file_exists($path_root . DS . 'components' . DS . 'com_jreviews' . DS . 'jreviews' . DS . 'framework.php') && 
        file_exists($path_root . DS . 'components' . DS . 'com_s2framework' . DS . 's2framework' . DS . 'basics.php')) 
    {
        // Run some checks on the tmp folders first
        $msg = array();
        $tmp_path = $path_root . DS . 'components' . DS . 'com_s2framework' . DS . 'tmp' . DS . 'cache' . DS;
        $folders = array('__data','assets','core','views');
        foreach($folders AS $folder){
            if(!file_exists( $tmp_path . $folder)) {
                if(@!mkdir($tmp_path . $folder,755)){
                    $msg[] = 'You need to create the '.  $tmp_path . $folder. ' folder and make sure it is writable (755) and has correct ownership';
                }
            } 
            if(!is_writable( $tmp_path . $folder . DS)){
                if(@!chmod($tmp_path . $folder . DS,755)){
                    $msg[] = 'You need to make the '.  $tmp_path . $folder. ' folder writable (755) and or change its ownership';                
                }
            }        
        }    
        
        if(empty($msg)){
            // MVC initalization script
            $S2_ROOT = dirname(dirname(dirname(dirname(__FILE__))));
            require( $S2_ROOT . DS . 'components' . DS . 'com_jreviews' . DS . 'jreviews' . DS . 'index.php' );
        } else {
            echo implode('<br />',$msg);
        }
        
    } elseif(file_exists($path_root . DS . 'components' . DS . 'com_jreviews' . DS . 'jreviews' . DS . 'framework.php') && 
        !file_exists($path_root . DS . 'components' . DS . 'com_s2framework' . DS . 's2framework' . DS . 'basics.php')) {
        ?>
        <div style="font-size:12px;border:1px solid #000;background-color:#FBFBFB;padding:10px;">
        The S2 Framework required to run jReviews is not installed. Please install the com_s2framework component included in the jReviews package.
        </div>
        <?php

    } elseif(file_exists($path_root . DS . 'administrator' . DS . 'components' . DS . 'com_jreviews' .DS . 'jreviews.s2'))
    { // Install app
        if (!ini_get('safe_mode')) {
            set_time_limit(2000);
        }                
        
        $install_bypass = isset($_GET['bypass']) ? true : false;    
        
        # Multi CSM constants
        if(!defined('CMS_JOOMLA15')) define('CMS_JOOMLA15','CMS_JOOMLA15');
        if(!defined('CMS_JOOMLA10')) define('CMS_JOOMLA10','CMS_JOOMLA10');
        if(!defined('CMS_MAMBO46'))     define('CMS_MAMBO46','CMS_MAMBO46');    
                            
        if(getCmsVersionInstall() == CMS_JOOMLA15 && $install_bypass === false) {
        
            jimport( 'joomla.filesystem.file' );
            jimport( 'joomla.filesystem.folder' );
            jimport( 'joomla.filesystem.archive' );
            jimport( 'joomla.filesystem.path' );

            $adapter = & JArchive::getAdapter('zip');
            $result = @$adapter->extract($package, $target);
        }

        if(!file_exists($target . 'jreviews' . DS . 'index.php')) 
        { // Extract 2nd attempt
            
            require_once ($path_root . DS . 'administrator' . DS . 'includes' . DS . 'pcl' . DS . 'pclzip.lib.php');
            require_once ($path_root . DS . 'administrator' . DS . 'includes' . DS . 'pcl' . DS . 'pclerror.lib.php');

            $extract = new PclZip ( $package );
            
            if ((substr ( PHP_OS, 0, 3 ) == 'WIN')) {
                if(!defined('OS_WINDOWS')) define('OS_WINDOWS',1);
            } else {
                if(!defined('OS_WINDOWS')) define('OS_WINDOWS',0);
            }
                    
            $result = @$extract->extract( PCLZIP_OPT_PATH, $target );        
        }    
                
        if(file_exists($target . 'jreviews' . DS . 'index.php')) 
        { // If extracted, run installer
            @unlink($path_app_admin . 'jreviews.s2');    
            
            $S2_ROOT = dirname(dirname(dirname(dirname(__FILE__))));
            require( $S2_ROOT . DS . 'components' . DS . 'com_jreviews' . DS . 'jreviews' . DS . 'framework.php' );
                        
            $Dispatcher = new S2Dispatcher('jreviews');    
            
            $Dispatcher->dispatch('install/index',array());    
        }
        
    } else 
    { // Can't install app
        ?>
        <div style="font-size:12px;border:1px solid #000;background-color:#FBFBFB;padding:10px;">
        There was a problem extracting the jReviews. <br />
        1) Locate the jreviews.s2 file in the component installation package you just tried to install.<br />
        2) Rename it to jreviews.zip and extract it to your hard drive<br />
        3) Upload it to the frontend /components/com_jreviews/ directory.
        </div>
        <?php
    }    
}

/**
 * Returns CMS version
**/
function getCmsVersionInstall()
{    
    if(defined('_JEXEC') && class_exists('JFactory')){
        return CMS_JOOMLA15;
    } else if(defined('_VALID_MOS') && class_exists('joomlaVersion')){
        return CMS_JOOMLA10;
    }elseif(defined('_VALID_MOS') && class_exists('mamboCore')){
        return CMS_MAMBO46;
    }
    
}