<?php
/**
 * @package		ActivityComment
 * @copyright	Copyright (C) 2009 - 2010 SocialCode. All rights reserved.
 * @license		GNU/GPL
 * JSUsernames is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses.
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');


$xmlParser =& JFactory::getXMLParser('Simple');
$file = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_community'.DS.'community.xml';
$xmlParser->loadFile($file);
$document =& $xmlParser->document;
$element =& $document->getElementByPath( 'version' );
$version = explode('.',$element->data());

define( '_BACKUP' , JPATH_PLUGINS.DS.'community'.DS.'activitycomment'.DS.'backup' );

if(!JFolder::exists(_BACKUP) )
{
	if(!JFolder::create( _BACKUP ))
		echo JText::_('Error creating backup folder: ' . _BACKUP . '. Please check permissions');
}

if($version[2] > 266 )
	$version[2]	= 267;

if( ($version[0] == '1' && $version[1] == '7' ) || ($version[0] == '1' && $version[1] =='8') )
	$version[2]	= '501';

if( $version[0] == '2' )
{
	$version[2]	= '601';
}

define('_BUILD',$version[2]);
define( '_PATCH_TEMPLATES' , JPATH_PLUGINS.DS.'community'.DS.'activitycomment'.DS.'patches');
define('_JS_TEMPLATES',JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'templates');

class ActivityCommentHelper
{
	function patchExists()
	{
		$data = JFolder::files(_BACKUP);
		return empty($data);
	}
	
	function getPatchedTemplates()
	{
		if(!JFolder::exists(_BACKUP.DS._BUILD))
			return false;
	
		$data = JFolder::folders(_BACKUP.DS._BUILD);
		$arr = array();
		
		foreach( $data as $tmpl )
		{
			if( JFile::exists(_BACKUP.DS._BUILD.DS.$tmpl.DS.'activities.index.php') )
			{
				$arr[]	= $tmpl;
			}
		}
		return $arr;
	}
	
	function getTemplates()
	{
		$data = JFolder::folders(_PATCH_TEMPLATES.DS._BUILD);
		$arr = array();

		foreach( $data as $tmpl )
		{
			if( JFile::exists(_JS_TEMPLATES.DS.$tmpl.DS.'activities.index.php') )
			{
				$arr[]	= $tmpl;
			}
		}
		return $arr;
	}
	
	function patch( $tmpl )
	{
		if(!JFolder::exists(_BACKUP.DS._BUILD))
		{
			JFolder::create(_BACKUP.DS._BUILD);
		}

		if(!JFolder::exists(_BACKUP.DS._BUILD.DS.$tmpl))
		{
			JFolder::create(_BACKUP.DS._BUILD.DS.$tmpl);
		}
		
		if(!JFile::copy(_JS_TEMPLATES.DS.$tmpl.DS.'activities.index.php', _BACKUP.DS._BUILD.DS.$tmpl.DS.'activities.index.php') )
		{
			echo 'error copying file to '. _BACKUP.DS._BUILD.DS.$tmpl.DS.'activities.index.php'.'. Possibly some permission errors';
			return false;
		}

		if(!JFile::copy(_PATCH_TEMPLATES.DS._BUILD.DS.$tmpl.DS.'activities.index.php', _JS_TEMPLATES.DS.$tmpl.DS.'activities.index.php') )
		{
			echo 'error copying file to '. _JS_TEMPLATES.DS.$tmpl.DS.'activities.index.php'.'. Possibly some permission errors';
			return false;
		}
		return true;
	}

	function restore( $tmpl )
	{
		if(!JFile::delete(_JS_TEMPLATES.DS.$tmpl.DS.'activities.index.php') )
		{
			echo 'error deleting '. _JS_TEMPLATES.DS.$tmpl.DS.'activities.index.php'.'. Possibly some permission errors';
			return false;
		}

		if(!JFile::move(_BACKUP.DS._BUILD.DS.$tmpl.DS.'activities.index.php', _JS_TEMPLATES.DS.$tmpl.DS.'activities.index.php') )
		{
			echo 'error moving file to '._JS_TEMPLATES.DS.$tmpl.DS.'activities.index.php'.'. Possibly some permission errors';
			return false;
		}

		return true;
	}
}