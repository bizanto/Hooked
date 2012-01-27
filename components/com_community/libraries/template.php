<?php
/**
 * @package		JomSocial
 * @subpackage	Core 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');
CFactory::load('helpers', 'template');

require_once(COMMUNITY_COM_PATH.DS.'libraries'.DS.'browser.php');

$browser = CBrowser::getInstance();
$screen  = 'desktop';
if( $browser->_mobile )
{
	// Determine whether to display
	// desktop or mobile screen.
	$mySess = JFactory::getSession();
	
	// If a screen was given from the URL,
	// it means we are switching screens,
	// so we'll save preferred screen to session data.
	$screen = JRequest::getVar('screen', null, 'GET');
	if(!empty($screen))
	{
		$mySess->set('screen', $screen);
	}

	// Get preferred screen from session data 
	$screen = $mySess->get('screen');

	// If preferred screen was not found in session data,
	// get it from user preferences.
	if (empty($screen))
	{
		$my     = CFactory::getUser();
		if( $my->id==0 )
		{
			// Use 'mobile' as default screen for guests
			$screen = 'mobile';
		} else {
			$params = $my->getParams();
			$screen = ($params->get('mobileView')) ? 'mobile' : 'desktop';
		}
	}
}
define('COMMUNITY_TEMPLATE_SCREEN', $screen);

/**
 * Templating system for JomSocial
 */
class CTemplate
{
    var $vars; // Holds all the template variables

    /**
     * Constructor
     *
     * @param $file string the file name you want to load
     */
    function __construct($file=null)
	{
        $this->file = $file;

        @ini_set('short_open_tag', 'On');
    }
	
    /**
     * Set a template variable.
     */
    function set($name, $value) {
        $this->vars[$name] = $value; //is_object($value) ? $value->fetch() : $value;
    }
    
    /**
     * Set a template variable by reference
     */
    function setRef($name, &$value) {
        $this->vars[$name] =& $value; //is_object($value) ? $value->fetch() : $value;
    }

    /**
     * Open, parse, and return the template file.
     *
     * @param $file string the template file name
     */
    function fetch($file=null)
	{
    	$template 	= new CTemplateHelper();
		$tmpFile	= $file;
		
        if(empty($file))
		{
			$file = $this->file;
		}
		
		if($this->mobileTemplate())
		{
			$file = $template->getMobileTemplateFile($file);
		}
		else
		{
			$file = $template->getTemplateFile($file);
		}

		// Extract template parameters for template providers.
		if( !isset($this->params) && empty($this->params) )
		{
			$this->params = $this->getTemplateParams();
		}

		// Template variable: $my;
		$my = CFactory::getUser();
		$this->setRef( 'my' , $my );

		// Template variable: $config;
		if( !isset( $this->vars['config'] ) && empty($this->vars['config']) )
		{
			$this->vars['config'] = CFactory::getConfig();
		}
	
		// Template variable: the rest.
		if($this->vars)
		{
        	extract($this->vars, EXTR_REFS);
		}
		
		if( !JFile::exists( $file ) )
		{
			$mainframe	=& JFactory::getApplication();
			$mainframe->enqueueMessage( JText::sprintf('CC TEMPLATE FILE NOT FOUND' , $tmpFile . '.php' ) , 'error' );
			return;
		}
		
        ob_start();                    // Start output buffering
        require($file);                // Include the file
        $contents = ob_get_contents(); // Get the contents of the buffer
        ob_end_clean();                // End buffering and discard
        return $contents;              // Return the contents
    }

	/***
	 * Allow a template to include other template and inherit all the variable
	 */	 	
	function load($file)
	{
		$template = new CTemplateHelper;

		if($this->vars)
		{
        	extract($this->vars, EXTR_REFS); 
        } 	
    	
		$file = $template->getTemplateFile($file);

		include($file);

		return $this;
	}

    function getTemplateParams()
    {
    	$template = new CTemplateHelper();

    	$defaultParam  = $template->getTemplatePath('params.ini', 'default');
    	$templateParam = $template->getTemplatePath('params.ini');
		$overrideParam = $template->getOverrideTemplatePath('params.ini');

		$params	 = new JParameter('');

		if(JFile::exists($defaultParam))
		{
			$params->bind( JFile::read($defaultParam) );
		}

		if(JFile::exists($templateParam))
		{
			$params->bind( JFile::read($templateParam) );
		}

		if(JFile::exists($overrideParam))
		{
			$params->bind( JFile::read($overrideParam) );
		}

		return $params;
	}
    
    function getTemplateEnvironment()
	{
        jimport('joomla.environment.browser'); 

        $app     =& JFactory::getApplication();
        $browser =  JBrowser::getInstance();
		
		$environment = new stdClass();
        $environment->joomlaTemplate = $app->getTemplate();
        $environment->browserName    = $browser->getBrowser();

        return $environment;
    }

	function addStylesheet($file)
	{
    	$template = new CTemplateHelper();
    	$asset = $template->getTemplateAsset($file, 'css');

    	CAssets::attach($asset->filename, 'css', $asset->path);
	}

	function addScript($file)
	{
		$template = new CTemplateHelper();
		$asset = $template->getTemplateAsset($file, 'js');

		CAssets::attach($asset->filename, 'js', $asset->path);
	}

    function renderModules($position, $attribs = array())
    {
    	jimport( 'joomla.application.module.helper' );
    	
		$modules 	= JModuleHelper::getModules( $position );
		$modulehtml = '';
		
		foreach($modules as $module)
		{			
			// If style attributes are not given or set, we enforce it to use the xhtml style
			// so the title will display correctly.
			if( !isset($attribs['style'] ) )
				$attribs['style']	= 'xhtml';

			$modulehtml .= JModuleHelper::renderModule($module, $attribs);
		}

		// Add placholder code for onModuleRender search/replace
		$modulehtml .= '<!-- '.$position. ' -->';
		echo $modulehtml;
    }

	function escape( $text )
	{
		CFactory::load('helpers', 'string');
		return CStringHelper::escape( $text );
	}

	function mobileTemplate()
	{
		return COMMUNITY_TEMPLATE_SCREEN=='mobile';
	}

	static public function getPoweredByLink()
	{
		return " ";
		$jConfig	= JFactory::getConfig();
		$siteName	= $jConfig->getValue( 'sitename' );
		
		return 'Powered by <a href="http://www.jomsocial.com/">JomSocial</a> for <a href="' . JURI::root() . '">' . $siteName . '</a>';
	}

    function object_to_array($obj)
	{
       $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
       $arr = array();
       foreach ($_arr as $key => $val) {
               $val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
               $arr[$key] = $val;
       }
       return $arr;
	}

	/*
	 * Deprecated since 2.0, use:
	 * getFile($file) or getFolder() from CTemplateHelper 
	 */
	function _getTemplateFolder( $file )
	{
		$folder = dirname($this->_getTemplateFullpath($file));
		return $folder;
	}

	/*
	 * Deprecated since 2.0, use:
	 * getFile($file) from CTemplateHelper 
	 */
	function _getTemplateFullpath($file)
    {
    	$template = new CTemplateHelper();
    	$file = $template->getFile($file);
    	return $file;
    }

	/*
	 * Deprecated since 2.0, use:
	 * getTemplateParams(); 
	 */
    function _getTemplateParams( $currentFolder )
    {
    	$params = $this->getTemplateParams();

    	$currentParam = $currentFolder . DS . 'params.ini';
		if (JFile::exists($currentParam))
		{
			$params->bind( JFile::read($currentParam) );
		}

		return $params;
    }

	public function setMetaTags( $app, $data )
	{
		$document	= JFactory::getDocument();
		$config		= CFactory::getConfig();

		$description	=   '';
		$groupName	=   '';
		
		if( isset( $data->description ) )
		{
			$description	= strip_tags( $data->description );
		}
		
		switch ($app)
		{
			case 'event' :
				$description	=   CStringHelper::truncate( CStringHelper::escape( $description ), $config->getInt('streamcontentlength') );
				$document->addHeadLink( $data->getThumbAvatar(), 'image_src', 'rel' );
				break;
			case 'video' :
				$description	=   CStringHelper::truncate( CStringHelper::escape( $description ), $config->getInt('streamcontentlength') );
				$document->setMetaData('medium', 'video');
				$document->addHeadLink( $data->getThumbnail(), 'image_src', 'rel' ); //cannot exceed 130x110 pixels (facebook)
				break;
			case 'group' :
				$groupName	=   $data->approvals == COMMUNITY_PRIVATE_GROUP ? $data->name . ' (' . JText::_( 'CC PRIVATE GROUP') . ')' : $data->name;
				$data->title	=   $groupName;
				$description	=   JText::sprintf( 'CC GROUP META DESCRIPTION', CStringHelper::escape($data->name), $config->get('sitename') , CStringHelper::escape( $description ) );
				$document->addHeadLink( $data->getThumbAvatar(), 'image_src', 'rel' );
				break;
			default :
				$description	=   CStringHelper::truncate( CStringHelper::escape($description), $config->getInt('streamcontentlength') );
		}

		$document->setTitle( $data->title ); // JDocument will perform htmlspecialchars escape
		$document->setMetaData('title', CStringHelper::escape( $data->title )); // hack the above line
		$document->setDescription( $description );
	}
}