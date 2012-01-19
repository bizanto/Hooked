<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class InstallController extends MyController 
{
    var $uses = array('menu');            
    var $helpers = array('html');
    var $components = array('config');
    
    var $autoRender = false;
    var $autoLayout = false;
    var $layout = 'empty';
            
    # Run right after component installation
    function index() 
    {      
        $response = array();
        
        if(Sanitize::getString($this->params,'task')=='upgrade')
        {   // Where running the install script for upgrade we want a json object returned
            $this->autoLayout = false;
            $this->autoRender = false;
        } else {
            $this->autoLayout = true;
            $this->autoRender = true;
        }

        $this->name = 'install';
        
        # Create database tables    
        // Start db upgrade logic
        $action = array();
        $action['db_install'] = true;
        $tables = $this->_db->getTableList();
        $dbprefix = cmsFramework::getConfig('dbprefix');
        $old_build = 0;
       
        if(is_array($tables) && in_array($dbprefix . 'jreviews_categories',array_values($tables))) 
        {
            // Tables exist so we check the current build and upgrade accordingly, otherwise it's a clean install and no upgrade is necessary
            $query = "SELECT value FROM #__jreviews_config WHERE id = 'version'";
            $this->_db->setQuery($query);
            $old_version = trim(strip_tags($this->_db->loadResult()));

            if($old_version!='') {
                $version_parts = explode('.',$old_version);
                $old_build = array_pop($version_parts);
            }
            
            // Get current version number
            $xml = file(S2_CMS_ADMIN . 'jreviews.xml');
            foreach($xml AS $xml_line) {
                if(strstr($xml_line,'version')) {
                    $new_version = trim(strip_tags($xml_line));
                    continue;
                }
            }
            $version_parts = explode('.',$new_version);
            $new_build = array_pop($version_parts);

            if(Sanitize::getBool($this->params,'sql')) 
            {
                $old_build = 0;
            }                
//            prx($old_build . '<br/>' . $new_build) ;
            
            if($new_build > $old_build) 
            {
                $i = $old_build+1;
                for($i = $old_build+1; $i<=$new_build; $i++) {

                    // Run sql updates
                    $sql_file = S2Paths::get('jreviews','S2_APP') . 'upgrades' . DS . 'upgrade_build'.$i.'.sql';
                          
                    if(file_exists($sql_file)) {  
                                          
                        $action['db_install'] = $this->__parseMysqlDump($sql_file,$dbprefix) && $action['db_install'];
                    }
                    
                    // Run php updates
                    $php_file = S2Paths::get('jreviews','S2_APP') . 'upgrades' . DS . 'upgrade_build'.$i.'.php';
                    
                    if(file_exists($php_file)) {                        
                        include($php_file);
                    }
                
                }
            }
            
        } 
        else 
        {

            // It's a clean install so we use the whole jReviews sql file
            $sql_file = S2Paths::get('jreviews','S2_APP') . 'upgrades' . DS . 'jreviews.sql';
            
            $action['db_install'] = $this->__parseMysqlDump($sql_file,$dbprefix);

        }                
        
        # Update component id in pre-existing jReviews menus
        if(getCmsVersion()==CMS_JOOMLA16)
        {
            $query = "
                SELECT 
                    extension_id AS id
                FROM 
                    #__extensions 
                WHERE 
                    element = '".S2Paths::get('jreviews','S2_CMSCOMP')."' AND type = 'component'
            ";
        }
        else 
        {
            $query = "
                SELECT 
                    id 
                FROM 
                    #__components 
                WHERE 
                    admin_menu_link = 'option=".S2Paths::get('jreviews','S2_CMSCOMP')."'
            ";
        }
        $this->_db->setQuery($query);
        
        if($id = $this->_db->loadResult()) 
        {
            if(getCmsVersion()==CMS_JOOMLA16)
            {
                $query = "
                    UPDATE 
                        `#__menu` 
                    SET 
                        component_id = $id 
                    WHERE 
                        type IN ('component','components') 
                            AND 
                        link = 'index.php?option=".S2Paths::get('jreviews','S2_CMSCOMP')."'
                ";
            } 
            else 
            {
                $query = "
                    UPDATE 
                        `#__menu` 
                    SET 
                        componentid = $id 
                    WHERE 
                        type IN ('component','components') 
                            AND 
                        link = 'index.php?option=".S2Paths::get('jreviews','S2_CMSCOMP')."'
                ";
            }
            $this->_db->setQuery($query);
            $this->_db->query();        
        }
        
        $result = false;
        
        // Install jReviews plugin
        $package = PATH_ROOT . 'administrator' . DS . 'components' . DS . 'com_jreviews' . DS . 'jreviews.plugin.s2';
        if(getCmsVersion() == CMS_JOOMLA16)
        {
            @mkdir(PATH_ROOT . _PLUGIN_DIR_NAME . DS . 'content' . DS . 'jreviews');
            $target = PATH_ROOT . _PLUGIN_DIR_NAME . DS . 'content' . DS . 'jreviews';
        } 
        else 
        {
            $target = PATH_ROOT . _PLUGIN_DIR_NAME . DS . 'content';
        }
        $target_file = $target . DS . 'jreviews.php';
        $first_pass = false;    
 
        jimport( 'joomla.filesystem.file' );
        jimport( 'joomla.filesystem.folder' );
        jimport( 'joomla.filesystem.archive' );
        jimport( 'joomla.filesystem.path' );
        $adapter = & JArchive::getAdapter('zip');
        $result = $adapter->extract ( $package, $target );    
        
        if($result)
        {
            $this->Config->version = $new_version;
            $this->Config->store();            
        }
            
        if(getCmsVersion() == CMS_JOOMLA15 && !file_exists($target_file))
        {
            require_once ( PATH_ROOT . 'administrator' . DS . 'includes' . DS . 'pcl' . DS . 'pclzip.lib.php');
            require_once (PATH_ROOT . 'administrator' . DS . 'includes' . DS . 'pcl' . DS . 'pclerror.lib.php');
            
            $extract = new PclZip ( $package );
            
            if ((substr ( PHP_OS, 0, 3 ) == 'WIN')) {
                if(!defined('OS_WINDOWS')) define('OS_WINDOWS',1);
            } else {
                if(!defined('OS_WINDOWS')) define('OS_WINDOWS',0);
            }
                    
            $result = $extract->extract ( PCLZIP_OPT_PATH, $target );
            
        }    
                        
        if (!file_exists($target_file)) 
        {
            $action['plugin_install'] = false;

        } 
        else 
        {
            $action['plugin_install'] = true;

            if(getCmsVersion() == CMS_JOOMLA16)
            {
                // Add/create plugin db entry
                $query = "
                    SELECT 
                        extension_id AS ud 
                    FROM 
                        #__extensions 
                    WHERE 
                        type = 'plugin' AND element = 'jreviews' AND folder = 'content'
                ";
                $this->_db->setQuery($query);
                $result = $this->_db->loadResult();
                if (!$result || empty($result)) 
                {
                    $query = "
                        INSERT INTO 
                            #__extensions 
                                (`name`, `type`,`element`, `folder`, `access`, `ordering`, `enabled`, `client_id`, `checked_out`, `checked_out_time`, `params`)
                            VALUES 
                                ('JReviews Plugin', 'plugin', 'jreviews', 'content', 0, 0, 1, 0, 0, '0000-00-00 00:00:00', '');
                    ";
                    $this->_db->setQuery($query);
                    if($this->_db->query()) {
                        $action['plugin_install'] = true;
                    } else {
                        $action['plugin_install'] = false;
                    }
                }
            } // end if 
            else 
            {
                // Add/create plugin db entry
                $query = "
                    SELECT 
                        id 
                    FROM 
                        #__plugins 
                    WHERE 
                        element = 'jreviews' AND folder = 'content'
                ";
                
                $this->_db->setQuery($query);
                
                $result = $this->_db->loadResult();
                            
                if (!$result || empty($result)) {

                    $query = "
                        INSERT INTO #__plugins 
                            (`name`, `element`, `folder`, `access`, `ordering`, `published`, `iscore`, `client_id`, `checked_out`, `checked_out_time`, `params`)
                        VALUES 
                            ('jReviews Comment Plugin', 'jreviews', 'content', 0, 0, 1, 0, 0, 0, '0000-00-00 00:00:00', '');
                    ";
                    $this->_db->setQuery($query);
                    if($this->_db->query()) {
                        $action['plugin_install'] = true;
                    } else {
                        $action['plugin_install'] = false;
                    }
                }
            } // end else           
        }
        
        # Create image upload and thumbnail folders
        if(!is_dir(PATH_ROOT . _JR_PATH_IMAGES . 'jreviews' . DS)) 
        {
            $Config = new JConfig();
    
            if(isset($Config->ftp_enable) && $Config->ftp_enable) {
                                        
                // set up basic connection
                $conn_id = ftp_connect($Config->ftp_host,$Config->ftp_port);
                
                // login with username and password
                $login_result = ftp_login($conn_id, $Config->ftp_user, $Config->ftp_pass);
                    
                ftp_chdir($conn_id,$Config->ftp_root);
                    
                ftp_mkdir($conn_id, _JR_PATH_IMAGES . 'jreviews');
    
                ftp_mkdir($conn_id, _JR_PATH_IMAGES . 'jreviews' . DS . 'tn');

                ftp_close($conn_id);
                    
                @copy(PATH_ROOT . _JR_PATH_IMAGES . 'index.html', PATH_ROOT . _JR_PATH_IMAGES . 'jreviews' . DS . 'index.html');
                @copy(PATH_ROOT . _JR_PATH_IMAGES . 'index.html', PATH_ROOT . _JR_PATH_IMAGES . 'jreviews' . DS . 'tn' . DS . 'index.html');
                    
            }
        }         

        if (!is_dir(PATH_ROOT . _JR_PATH_IMAGES . 'jreviews' . DS) ) 
        {            
            $result = mkdir(PATH_ROOT . _JR_PATH_IMAGES . 'jreviews' . DS , 0755);
            
            if (!$result) {

                $action['thumbnail_dir'] = false;
            
            } else {
                
                @copy(PATH_ROOT . _JR_PATH_IMAGES . 'index.html', PATH_ROOT . _JR_PATH_IMAGES . 'jreviews' . DS . 'index.html');
                $result = mkdir(PATH_ROOT . _JR_PATH_IMAGES . 'jreviews' . DS . 'tn', 0755);

                if (!$result) {
                    $action['thumbnail_dir'] = false;
                
                } else {
                    @copy(PATH_ROOT . _JR_PATH_IMAGES . 'index.html', PATH_ROOT . _JR_PATH_IMAGES . 'jreviews' . DS . 'tn' . DS . 'index.html');
                }
            }
        }
        
        if (!is_dir(PATH_ROOT . _JR_PATH_IMAGES . 'jreviews' . DS . 'tn' . DS) ) 
        {            
            $result = mkdir(PATH_ROOT . _JR_PATH_IMAGES . 'jreviews' . DS .'tn', 0755);

            if (!$result) {
                $action['thumbnail_dir'] = false;
            
            } else {
                @copy(PATH_ROOT . _JR_PATH_IMAGES . 'index.html', PATH_ROOT . _JR_PATH_IMAGES . 'jreviews' . DS . 'tn' . DS . 'index.html');
            }
        }
        
        if(is_dir(PATH_ROOT . _JR_PATH_IMAGES . 'jreviews' . DS) && is_dir(PATH_ROOT . _JR_PATH_IMAGES . 'jreviews' . DS . 'tn' . DS)){
            $action['thumbnail_dir'] = true;
        }
            
        // Clear data and core caches  
        clearCache('', '__data');        
        clearCache('', 'core');        
        
        //var_dump($action);
        
        if(Sanitize::getString($this->params,'task')=='upgrade')
        {
            $response = array('error'=>false,'html'=>'');
            // {"db_install":true,"plugin_install":true,"thumbnail_dir":true}
            if(!$action['db_install'])
            {
                $response['error'] = true;
                $response['html'] = '<div style="color:red>There was a problem upgrading the database</div>';  
            }
            if(!$action['plugin_install'])
            {
                $response['error'] = true;
                $response['html'] .= '<div style="color:red>There was a problem upgrading the JReviews plugin</div>';  
            }
            return json_encode($response);
        } 
        
        $this->set(array(
            'action'=>$action
        ));
    }
    
    # Tools to fix installation problems any time
    function _installfix() 
    {
        // Load fields model
        App::import('Model','field','jreviews');
        $FieldModel = new FieldModel();
        
        $task = Sanitize::getString($this->data,'task');

        $msg = '';
        $mambot_error = 0;

        switch($task) {
                    
            case 'fix_install_jreviews':
    
                $query = "SELECT id,published FROM #__"._PLUGIN_DIR_NAME." WHERE element = 'jreviews' AND folder = 'content'";
                $this->_db->setQuery($query);
    
                $jReviewsMambot = $this->_db->loadObjectList(); 
                
                if (!$jReviewsMambot || empty($jReviewsMambot)) {
                    // Install in DB
                    $this->_db->setQuery( "INSERT INTO #__"._PLUGIN_DIR_NAME." (`name`, `element`, `folder`, `access`, `ordering`, `published`, `iscore`, `client_id`, `checked_out`, `checked_out_time`, `params`) VALUES ('jReviews Comment Plugin', 'jreviews', 'content', 0, 0, 1, 0, 0, 0, '0000-00-00 00:00:00', '');");
                    $this->_db->query();
    
                } else {
                    // Publish
                    $jReviewsMambot = $jReviewsMambot[0];
                    if (!$jReviewsMambot->published) {
                        $this->_db->setQuery("UPDATE #__"._PLUGIN_DIR_NAME." SET published = '1' WHERE id='{$jReviewsMambot->id}'");
                        $this->_db->query();
                    }
    
                }
    
                if (!file_exists(PATH_ROOT . _PLUGIN_DIR_NAME . DS . 'content' . DS . 'jreviews.php')) {
                    
                    $package = PATH_ROOT . 'administrator' . DS . 'components' . DS . 'com_jreviews' . DS . 'jreviews.plugin.s2';
                    $target = PATH_ROOT . _PLUGIN_DIR_NAME . DS . 'content';
                    $target_file = $target . DS . 'jreviews.php';
                        
                    if(getCmsVersion() == CMS_JOOMLA15) {
                
                        jimport( 'joomla.filesystem.file' );
                        jimport( 'joomla.filesystem.folder' );
                        jimport( 'joomla.filesystem.archive' );
                        jimport( 'joomla.filesystem.path' );
                
                        $adapter = & JArchive::getAdapter('zip');
                        $result = $adapter->extract ($package, $target);
                                    
                    }
                    
                    if(!file_exists($target_file)) {
                        
                        require_once ( PATH_ROOT . 'administrator' . DS . 'includes' . DS . 'pcl' . DS . 'pclzip.lib.php');
                        require_once (PATH_ROOT . 'administrator' . DS . 'includes' . DS . 'pcl' . DS . 'pclerror.lib.php');
                        
                        $extract = new PclZip ( $package );
                        
                        if ((substr ( PHP_OS, 0, 3 ) == 'WIN')) {
                            if(!defined('OS_WINDOWS')) define('OS_WINDOWS',1);
                        } else {
                            if(!defined('OS_WINDOWS')) define('OS_WINDOWS',0);
                        }
                                
                        $result = $extract->extract ( PCLZIP_OPT_PATH, $target );
                        
                    }
                    
                    if (!$result) {
                        $mambot_error = true;
                    } else {
                        $mambot_error = false;
                    }
                }
    
                if ($mambot_error) {
                    $msg = "It was not possible to copy the mambot/plugin files. Make sure the /"._PLUGIN_DIR_NAME."/content folder is writable and try again.";
                }
    
                break;
        
            case 'fix_content_fields':
    
                $output = '';
                $rows = $this->_db->getTableFields(array('#__jreviews_content'));
                $columns = array_keys($rows['#__jreviews_content']);
    
                $sql = "SELECT name,type FROM #__jreviews_fields WHERE location = 'content'";
                $this->_db->setQuery($sql);
                $fields = $this->_db->loadObjectList('name');
    
                $missing = array();
                
                foreach ($fields AS $field) {
                    if (!in_array($field->name,$columns)) {
                        $output = $FieldModel->addTableColumn($field->name,$field->type,'content');
                    }
                }
                
                $query = "DELETE FROM #__jreviews_fields WHERE name = ''";
                $this->_db->setQuery($query);
                $output = $this->_db->query();
    
                if ($output != '') {
                    $msg = "There was a problem fixing one or more of the content fields";
                }
    
                break;
    
            case 'fix_review_fields':
    
                $output = '';
                $rows = $this->_db->getTableFields(array('#__jreviews_review_fields'));
                $columns = array_keys($rows['#__jreviews_review_fields']);
    
                $sql = "SELECT name,type FROM #__jreviews_fields WHERE location = 'review'";
                $this->_db->setQuery($sql);
                $fields = $this->_db->loadObjectList('name');
    
                $missing = array();
                foreach ($fields AS $field) {
                    if (!in_array($field->name,$columns)) {
                        $output = $FieldModel->addTableColumn($field->name,$field->type,'review');
                    }
                }
    
                $query = "DELETE FROM #__jreviews_fields WHERE name = ''";
                $this->_db->setQuery($query);
                $output = $this->_db->query();
                                
                if ($output != '') {
                    $msg = "There was a problem fixing one or more of the review fields";
                }
    
                break;
    
            default:
                break;
        }
        cmsFramework::redirect("index.php?option=com_jreviews",$msg);
    }       
}