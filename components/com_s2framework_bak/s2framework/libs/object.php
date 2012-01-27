<?php
/**
 * Object class, allowing __construct and __destruct in PHP4.
 *
 * Also includes methods for logging and the special method RequestAction,
 * to call other Controllers' Actions from anywhere.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 * 
 * @modified by Alejandro Schmeichler
 * @lastmodified 2008-03-12 
 */

class S2Object {

    var $cmsVersion;
    var $viewSuffix;
    var $viewTheme;   
    var $app;
    var $file_prefix = false;
    
	function S2Object() 
	{
        $args = func_get_args();

        if (method_exists($this, '__destruct')) 
        {
            register_shutdown_function(array(&$this, '__destruct'));
        }
        
        call_user_func_array(array(&$this, '__construct'), $args);
    }

    function __construct() 
    {                    
        $this->cmsVersion = getCmsVersion();
    }  

    function locateThemeFile($action,$file,$ext='.thtml', $forceFrontEnd = false)
    {                                     
        # Run an additional set of checks to dynamically load j16 themes if present
        if($this->cmsVersion == CMS_JOOMLA16 && !$this->file_prefix)
        {
            $this->file_prefix = true; 
            $path = $this->locateThemeFile($action,'j16.'.$file,$ext,$forceFrontEnd); 
            if($path) return $path;   
        }
        
        $path = false;
        $action = strtolower($action);
        $App = &App::getInstance($this->app);
        $suffix = strtolower($this->viewSuffix);
        if(is_string($forceFrontEnd))
        {
            $location = $forceFrontEnd;            
        } else {
            $location = $forceFrontEnd ? 'Theme' : (defined('MVC_FRAMEWORK_ADMIN') ? 'AdminTheme' : 'Theme');  
        }
//        echo 'app: ' . $this->app . '<br />';
//        echo 'theme: ' . $this->viewTheme. '<br />';
//        echo 'suffix: ' . $this->viewSuffix. '<br />';
//        echo $location.DS.$this->viewTheme.DS.$action.DS.$file.$this->viewSuffix.$ext.'<br />';

        if(isset($App->{$this->app.'Paths'}[$location][$this->viewTheme][$action][$file.$suffix.$ext]))
            { // Selected theme w/ suffix
                $path =  $App->{$this->app.'Paths'}[$location][$this->viewTheme][$action][$file.$suffix.$ext] == $file.$suffix.$ext 
                        ? 
                            $App->{$this->app.'Paths'}[$location][$this->viewTheme]['.info']['path']
                            . $action . DS
                            . $file.$suffix.$ext
                        :
                            $App->{$this->app.'Paths'}[$location][$this->viewTheme][$action][$file.$suffix.$ext]
                        ;
            } 
        elseif(isset($App->{$this->app.'Paths'}[$location]['default'][$action][$file.$suffix.$ext])) 
            { // Default theme w/ suffix
                   $path =  $App->{$this->app.'Paths'}[$location]['default'][$action][$file.$suffix.$ext] == $file.$suffix.$ext 
                    ? 
                        $App->{$this->app.'Paths'}[$location]['default']['.info']['path']
                        . $action . DS
                        . $file.$suffix.$ext
                    :
                        $App->{$this->app.'Paths'}[$location]['default'][$action][$file.$suffix.$ext]
                    ;
            } 
        elseif(isset($App->{$this->app.'Paths'}[$location][$this->viewTheme][$action][$file.$ext]))
            { // Selected theme w/o suffix
                    $path = $App->{$this->app.'Paths'}[$location][$this->viewTheme][$action][$file.$ext] == $file.$ext 
                        ? 
                            $App->{$this->app.'Paths'}[$location][$this->viewTheme]['.info']['path']
                            . $action . DS
                            . $file.$ext
                        :
                            $App->{$this->app.'Paths'}[$location][$this->viewTheme][$action][$file.$ext]
                        ;
            } 
        elseif(isset($App->{$this->app.'Paths'}[$location]['default'][$action][$file.$ext])) 
            {   // Default theme w/o suffix
                    $path = $App->{$this->app.'Paths'}[$location]['default'][$action][$file.$ext] == $file.$ext 
                    ? 
                        $App->{$this->app.'Paths'}[$location]['default']['.info']['path']
                        . $action . DS
                        . $file.$ext
                    :
                        $App->{$this->app.'Paths'}[$location]['default'][$action][$file.$ext]
                    ;
            } 
            
        return $path ? PATH_ROOT . $path : false;
    } 
    
    function locateScript($file,$admin = false)
    {
        if(!strstr($file,'.js')) $file = $file.'.js';
        $file = str_replace(DS,_DS,$file);
        $App = &App::getInstance($this->app);

        if(isset($App->{$this->app.'Paths'}[($admin ? 'Admin' : '').'Javascript'][$file]))
        {
            return WWW_ROOT.$App->{$this->app.'Paths'}[($admin ? 'Admin' : '').'Javascript'][$file];
        }
        else
            return false;
    }    
            
/**
 * Calls a controller's method from any location.
 *
 * @param string $url URL in the form of Cake URL ("/controller/method/parameter")
 * @param array $extra if array includes the key "return" it sets the AutoRender to true.
 * @return mixed Success (true/false) or contents if 'return' is set in $extra
 * @access public
 */
	function requestAction($url, $extra = array()) 
    {
 		$app = Sanitize::getString($extra,'app','jreviews');
		$xajax = Sanitize::getVar($extra,'xajax',false);
		unset($extra['app']);
		unset($extra['xajax']);
		
		if (empty($url)) {
			return false;
		}
		if (!class_exists('S2Dispatcher')) {
			require S2_FRAMEWORK . DS . 'dispatcher.php';
		}
		if (in_array('return', $extra, true)) {
			$extra = array_merge($extra, array('return' => 0, 'autoRender' => 1));
		}
		
		$params = array_merge(array('autoRender' => 0, 'return' => 1, 'bare' => 1, 'requested' => 1), $extra);
		
		$disable404 = true;
		$dispatcher = new S2Dispatcher($app,$xajax,$disable404);

		return $dispatcher->dispatch($url, $params);
 	}
 	
 /**
 * Stop execution of the current script
 *
 * @param $status see http://php.net/exit for values
 * @return void
 * @access public
 */
	function _stop($status = 0) {
		exit($status);
	}	
}
