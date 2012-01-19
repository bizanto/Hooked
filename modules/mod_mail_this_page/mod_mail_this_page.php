<?php
/**
* 
* 
* @copyright	Inspiration Web Design http://www.iswebdesign.co.uk
* License GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

//$list = modMailThisPageHelper::getList($params);


if (modMailThisPageHelper::checkThisForm())
{
	 $lists = modMailThisPageHelper::getPostValues($params);
	 if (!$lists->error){
		  modMailThisPageHelper::sendTheMessage($lists,$params);
	}
	else
	{  
	   $document =& JFactory::getDocument();		 
	   $js = 'window.alert("' . $lists->error_message . '");';
	   $domready = "window.addEvent('domready', function() { ". $js ." });";
	   $document->addScriptDeclaration($domready);		
	
	}
	   
}
else
{
  $lists->error=false;
  $lists->error_message='';
  $lists->success_message='';
} 
$lists->token = '<input type="hidden" name="' . JUtility::getToken(). '" value="1" />';
$lists->submit = JText::_("Send");  
$lists->scripts = modMailThisPageHelper::addScripts($params);


require(JModuleHelper::getLayoutPath('mod_mail_this_page'));
