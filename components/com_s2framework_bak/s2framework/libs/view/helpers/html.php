<?php
/**
 * Html Helper class file.
 *
 * Simplifies the construction of HTML elements.
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2007, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 *  
 * @modified	by Alejandro Schmeichler
 * @lastmodified 2008-03-06
 */

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class HtmlHelper extends MyHelper
{
	var $viewSuffix = '';
	
	var $tags = array(
		'metalink' => '<link href="%s" title="%s"%s />',
		'link' => '<a href="%s" %s>%s</a>',
		'mailto' => '<a href="mailto:%s" %s>%s</a>',
		'form' => '<form %s>',
		'formend' => '</form>',
		'input' => '<input name="%s" %s />',
		'text' => '<input type="text" name="%s" %s/>',
		'textarea' => '<textarea name="%s" %s>%s</textarea>',
		'hidden' => '<input type="hidden" name="%s" %s/>',
		'textarea' => '<textarea name="%s" %s>%s</textarea>',
		'checkbox' => '<input type="checkbox" name="%s[]" id="%s" %s/>&nbsp;%s',
		'checkboxmultiple' => '<input type="checkbox" name="%s[]" id="%s" %s />&nbsp;%s',
		'radio' => '<input type="radio" name="%s" id="%s" %s />&nbsp;%s',
		'selectstart' => '<select name="%s"%s>',
		'selectmultiplestart' => '<select name="%s[]"%s>',
		'selectempty' => '<option value=""%s>&nbsp;</option>',
		'selectoption' => '<option value="%s"%s>%s</option>',
		'selectend' => '</select>',
		'optiongroup' => '<optgroup label="%s"%s>',
		'optiongroupend' => '</optgroup>',
		'password' => '<input type="password" name="%s" %s />',
		'file' => '<input type="file" name="%s" %s/>',
		'file_no_model' => '<input type="file" name="%s" %s />',
		'submit' => '<input type="submit" %s/>',
		'submitimage' => '<input type="image" src="%s" %s />',
		'button' => '<input type="button" %s />',
		'imagebutton' => '<input type="image" %s />',		
		'image' => '<img src="%s" %s />',
		'tableheader' => '<th%s>%s</th>',
		'tableheaderrow' => '<tr%s>%s</tr>',
		'tablecell' => '<td%s>%s</td>',
		'tablerow' => '<tr%s>%s</tr>',
		'block' => '<div%s>%s</div>',
		'blockstart' => '<div%s>',
		'blockend' => '</div>',
		'para' => '<p%s>%s</p>',
		'parastart' => '<p%s>',
		'label' => '<label for="%s"%s>%s</label>',
        'label_no_for'=>'<label %s>%s</label>',
		'fieldset' => '<fieldset %s><legend>%s</legend>%s</fieldset>',
		'fieldsetstart' => '<fieldset><legend>%s</legend>',
		'fieldsetend' => '</fieldset>',
		'legend' => '<legend>%s</legend>',
		'css' => '<link rel="%s" type="text/css" href="%s" %s/>',
		'style' => '<style type="text/css" %s>%s</style>',
		'charset' => '<meta http-equiv="Content-Type" content="text/html; charset=%s" />',
		'javascriptlink' => '<script type="text/javascript" src="%s"></script>',
		'javascriptcode' => '<script type="text/javascript">%s</script>',		
		'ul' => '<ul%s>%s</ul>',
		'ol' => '<ol%s>%s</ol>',
		'li' => '<li%s>%s</li>'
	);
			
    var $AppPaths = array();
    
    function startup()
    {         
        if(empty($this->AppPaths))
        {
            $App = &App::getInstance($this->app);
            $this->AppPaths = $App->{$this->app.'Paths'};    
        }
    }   
             
	function ccss($files) 
    {
        if(isset($this->xajaxRequest) && $this->xajaxRequest) {
			return;
		}
	
		// Register in header to prevent duplicates
		$headCheck = RegisterClass::getInstance('HeadTracking');
				
		if (is_array($files)) {
			
			$out = '';
			$css = array();

			foreach ($files as $i) {
				// Check if already in header
				if(!$headCheck->check($i,'css')) {
					$css[] = $i.".css";
                    $headCheck->register($i,'css');                
				}
			}
            
			if(empty($css)) {
				return;
			}
		
        } else {
            
            if(!$headCheck->check($files,'css')) {
                $css[] = $files.".css";
                $headCheck->register($files,'css');                
            }
            
        }

		// Create minify script url
        $parts = parse_url(WWW_ROOT);                                                                                                                                      
        $folder = $parts['path'] != '/' ? ltrim($parts['path'],'/') : '';               
        foreach($css AS $css_file)
        {
            $no_ext = str_replace(array('.css',_DS),array('',DS),$css_file);
            $path = $this->locateThemeFile('theme_css',$no_ext,'.css');
            if($path) $css_array[] = $folder.str_replace(array(PATH_ROOT,DS),array('',_DS),$path);
        }
		$css_files = implode(',',$css_array); 
        if($css_files!='')
        {
            $url = WWW_ROOT . "components/com_s2framework/vendors/min/f={$css_files}";
            $rel = 'stylesheet';
            $out = sprintf($this->tags['css'], $rel, $url, '');
            cmsFramework::addScript($out, false);
        }               
	}		
	
	function css($files, $inline = false) 
    {	
/**
 * BYPASSES THE CSS METHOD IN FAVOR OF CCSS (cached)
 */
		if(Configure::read('Cache.assets_css') && !defined('MVC_FRAMEWORK_ADMIN') && !$inline) {
			$this->ccss($files);
			return;
		}
		
		// Register in header to prevent duplicates
		$headCheck = RegisterClass::getInstance('HeadTracking');
				
		if (is_array($files)) {
			
			$out = '';
			
			foreach ($files as $i) {
				// Check if already in header
				if(!$headCheck->check($i,'css')) {
					$out .= "\n\t" . $this->css($i, $inline);
				}
			}
			
			if ($out != '' && $inline)  {
				return $out . "\n";
			}
			
			return;
		}

        // Create minify script url   
        $no_ext = str_replace(array(MVC_ADMIN._DS,'.css',_DS),array('','',DS),$files);
        $ThemeFolder = false!==strpos($files,MVC_ADMIN) ? 'AdminTheme' : 'Theme';
        $cssPath = $this->locateThemeFile('theme_css',$no_ext,'.css',$ThemeFolder);
		$cssUrl = pathToUrl($cssPath);
		$headCheck->register($files,'css');
		$rel = 'stylesheet';
		$out = sprintf($this->tags['css'], $rel, $cssUrl, '');
		cmsFramework::addScript($out,$inline);		
	}
	

	function cjs($files, $duress = false) 
    {
		if(isset($this->xajaxRequest) && $this->xajaxRequest) {
			return;
		}
				
		// Register in header to prevent duplicates
		$headCheck = RegisterClass::getInstance('HeadTracking');
						
		if (is_array($files)) {
			
			$out = '';
			$js = array();
			
			foreach ($files as $i) {
				// Check if already in header
				if($duress || !$headCheck->check($i,'js')) {
					$js[] = $i . '.js';
                    $headCheck->register($i,'js');                
				}
			}
			
			if(empty($js)) {
				return;
			}
            
		} else {
            
            if(!$headCheck->check($files,'js')) {
                $js[] = $files.".js";
                $headCheck->register($files,'js');                
            }
            
        }

		// Create minify script url
        $parts = parse_url(WWW_ROOT);                                                                                                                                      
        $folder = $parts['path'] != '/' ? ltrim($parts['path'],'/') : '';               
        foreach($js AS $js_file)
        {         
            $no_ext = str_replace(array('.js',_DS),array('',DS),$js_file);
            $path = $this->locateScript($no_ext);
            if($path) $js_array[] = $folder.str_replace(array(WWW_ROOT,_DS),array('',_DS),$path);
        }
        $js_files = implode(',',$js_array);        
        if($js_files!='')
        {
            $url = WWW_ROOT . "components/com_s2framework/vendors/min/f={$js_files}";
            $out = sprintf($this->tags['javascriptlink'], $url);
            cmsFramework::addScript($out,false, $duress);        
        }
	}
		
	function js($files, $inline = false, $duress = false, $nocache = false) 
    {      
/**
 * BYPASSES THE JS METHOD IN FAVOR OF CJS (cached)
 */
		if(Configure::read('Cache.assets_js') && !defined('MVC_FRAMEWORK_ADMIN') && !$inline && $nocache === false) {
			$this->cjs($files, $duress);
			return;
		}		
		
		// Register in header to prevent duplicates
		$headCheck = RegisterClass::getInstance('HeadTracking');			
        
		if (is_array($files)) {
			$out = '';
			
			foreach ($files as $i) {
				// Check if already in header
				if($duress || !$headCheck->check($i,'js')) {
					$out .= "\n\t" . $this->js($i, $inline, $duress, $nocache);
				}
			}
			
			if ($out != '' && $inline)  {
				echo $out . "\n";
			}
			
			return;
		}
		          
		$headCheck->register($files,'js');
        
        if(empty($this->AppPaths))
        {
            $App = &App::getInstance($this->app);
            $this->AppPaths = $App->{$this->app.'Paths'};    
        }
                 
        if(!strstr($files,'.js')) $files = $files.'.js';
		if(false!==strpos($files,MVC_ADMIN)) { // Automatic routing to admin path
            $files = str_replace(MVC_ADMIN .'/', '', $files);
            $jsUrl = $this->locateScript($files,true);
		} else {
            $jsUrl = $this->locateScript($files);
		}

        if($jsUrl)
        {
            $out = sprintf($this->tags['javascriptlink'], $jsUrl);
            cmsFramework::addScript($out,$inline, $duress);        
        }
	}		
		
	function getCrumbs($crumbs, $separator = '&raquo;', $startText = false) 
	{	
		if (count($crumbs)) {
			
			$out = array();
			
			if ($startText) {
				$out[] = $this->sefLink($startText, '/');
			}

			foreach ($crumbs as $crumb) {
				if (!empty($crumb['link'])) {
					$out[] = $this->sefLink($crumb['text'], $crumb['link']);
				} else {
					$out[] = $crumb['text'];
				}
			}
			
			return implode($separator, $out);
			
		} else {
			return null;
		}
	}
	
	function link($title, $url = null, $attributes = array()) 
    {
		if(isset($attributes['sef']) && !$attributes['sef']) 
        {
            if(isset($attributes['return_url'])){
                return $url;
            }
            unset($attributes['sef']);
			$attributes = $this->_parseAttributes($attributes);
			return sprintf($this->tags['link'],$url,$attributes,$title);			
		}
		return $this->sefLink($title, $url, $attributes);	
	}
	
	function sefLink($title, $url = null, $attributes = array()) 
    {
		$url = str_replace('{_PARAM_CHAR}',_PARAM_CHAR,$url);
        if(isset($attributes['return_url'])){
            return cmsFramework::route($url);
        }

		$attributes = $this->_parseAttributes($attributes);
        
        return sprintf($this->tags['link'],cmsFramework::route($url),$attributes,$title);            
	}
	
	function image($src,$attributes = array()) {
		$attributes = $this->_parseAttributes($attributes);
		return sprintf($this->tags['image'],$src,$attributes);
	}
	
	function div($class = null, $text = null, $attributes = array()) {

		if ($class != null && !empty($class)) {
			$attributes['class'] = $class;
		}
		if ($text === null) {
			$tag = 'blockstart';
		} else {
			$tag = 'block';
		}
		return $this->output(sprintf($this->tags[$tag], $this->_parseAttributes($attributes), $text));
	}	
}