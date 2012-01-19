<?php
/**
* 
* 
* @copyright	Inspiration Web Design http://www.iswebdesign.co.uk
* License GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html');
jimport('joomla.mail.helper');
jimport('joomla.utilities.utility');
jimport('joomla.html.html.behaviour');


class modMailThisPageHelper
{
  function addScripts(&$params)
  {
  
	//JHTML::_('behaviour.mootools');
	/*if ( method_exists('JHTMLBehavior','mootools')){
	   JHTMLBehavior::mootools();
	}
	else if ( method_exists(  'JHTML','_' ))
	{
       JHTML::_('behaviour.mootools');	
	}*/
	JHTML::_('behavior.mootools');
	
	$document =& JFactory::getDocument();
	$js = 'var user_name_blank = "'. JText::_("Ditt navn er tomt") . '";';
	$js .= 'var friend_name_blank = "'. JText::_("Din venn sin e-postadresse er tom") . '";';	
	$js .= 'var invalid_user_mail = "'. JText::_("Din e-postadresse er ugyldig") . '";';	
	$js .= 'var invalid_friend_mail = "'. JText::_("Din venn sin e-postadresse er ugyldig") . '";';	
    $baseurl = JURI::base();
	$document->addScript($baseurl.'modules/mod_mail_this_page/assets/validate.js');
	//$document->addScript($baseurl.'modules/mod_mail_this_page/assets/slide.js');	
    $document->addScriptDeclaration($js);
	
    if ( $params->get('use_slider') == '1')
	{

		if ( $params->get('use_cookie') == '1')
		{
		   return $baseurl.'modules/mod_mail_this_page/assets/slide.js';
		}
		else
		{
		   return $baseurl.'modules/mod_mail_this_page/assets/slide_no_cookie.js';	
		}
	}
	else
	{
	  return '';	
	}
  }
  
  function checkThisform()
  {
    $mtp_ident_form = JString::substr(JRequest::getVar('mtp_ident_form',''),0,100);
	if ($mtp_ident_form != 'mtp')
	{
	  return false;
	}
	else
	{
	  return true;
	}
	
  }
  
  function getPostValues(&$params)
  {
 	
    $lists->mtp_bcc = JString::trim( $params->get('bcc')); //not a fatal error if this is blank
    $lists->error = false;
	$lists->error_message = '';
	$lists->success_message = '';
	
  
    $lists->mtp_from_email = JString::trim( $params->get('fromEmail'));
    if ($lists->mtp_from_email == '')
	{
	  $lists->error = true;
	  $lists->error_message = JText::_("FROMEMAILINVALID");
	  return $lists;
	}	
 
	$lists->mtp_user_name = JString::substr(JString::trim(JRequest::getVar('mtp_user_name','')),0,100);
	if ($lists->mtp_user_name == '')
	{
	  $lists->error = true;
	  $lists->error_message = JText::_("MESSAGENAMEBLANK");
	  return $lists;
	}
	$lists->mtp_user_email = JString::substr(JString::trim(JRequest::getVar('mtp_user_email','')),0,100);
	if ($lists->mtp_user_email == '')
	{
	  $lists->error = true;
	  $lists->error_message = JText::_("YOUREMAILBLANK");
	  return $lists;
	}	
	$lists->mtp_friend_name = JString::substr(JString::trim(JRequest::getVar('mtp_friend_name','')),0,100);
	if ($lists->mtp_friend_name == '')
	{
	  $lists->error = true;
	  $lists->error_message = JText::_("MESSAGEFRIENDNAMEBLANK");
	  return $lists;
	}
	$lists->mtp_friend_email = JString::substr(JString::trim(JRequest::getVar('mtp_friend_email','')),0,100);
	if ($lists->mtp_friend_email == '')
	{
	  $lists->error = true;
	  $lists->error_message = JText::_("FRIENDEMAILBLANK");
	  return $lists;
	}	
	
	//validate
	
	if (!JMailHelper::isEmailAddress( $lists->mtp_user_email ))
	{
      $lists->error = true;
	  $lists->error_message = JText::_("MESSAGEEMAILINVALID");
	  return $lists;
	}	
	if (!JMailHelper::isEmailAddress( $lists->mtp_friend_email ))
	{
      $lists->error = true;
	  $lists->error_message = JText::_("MESSAGEFRIENDEMAILINVALID");
	  return $lists;
	}	
	if (!JMailHelper::isEmailAddress( $lists->mtp_from_email ))
	{
      $lists->error = true;
	  $lists->error_message = JText::_("FROMEMAILINVALID");
	  return $lists;
	}			
		
	
	$regexp = '/[<\[]/';  //spam filter
	foreach ($_REQUEST as $requestVar)// use raw request for this because JRequest filters out < tage
	{
	  if(isset($requestVar)){
        if (preg_match($regexp, is_array($requestVar)? $requestVar[0]: $requestVar )){
	       $lists->error = true;
	       $lists->error_message = JText::_("INVALIDHTMLTAGS");
	       return $lists;
	    }
	  }		  
	}
	
   /* if (preg_match($regexp, $lists->mtp_user_name)){
	  $lists->error = true;
	  $lists->error_message = JText::_("invalid name: no html tags");
	  return $lists;
	}	
	if (preg_match($regexp, $lists->mtp_friend_email)){
	  $lists->error = true;
	  $lists->error_message = JText::_("invalid name: no html tags");
	  return $lists;
	}	*/
	
	if($params->get('filterEmails') != '')
	{
		$emailFilter = explode(';', JString::trim($params->get('filterEmails'))); 
		foreach ($emailFilter as $filter )
		{
		   $filter = preg_quote( $filter, '/' );
		   if (preg_match('/'.$filter.'/', $lists->mtp_friend_email))
		   {
			  $lists->error = true;
			  $lists->error_message = JText::_("SORRYCANNOTEMAIL");
			  return $lists;
		   }
		}
	}
	
				
    return $lists;
  
  }
  
  function sendTheMessage(&$lists,&$params)
  {
   if (JRequest::checkToken())
  {
       $mainframe = &JFactory::getApplication();;
	   $document =& JFactory::getDocument();	
	   $uri	 = & JURI::getInstance();
	   $currentURL = $uri->current();
	   if(($currentURL == JURI::root()) || (preg_match('/index.php$/',$currentURL)))
	   {
		  //current url may not be reported correctly
		  if($params->get('use_detection','0') == '1')
		  {
		  
			$getvars = JRequest::get('GET');
			
			if((is_array($getvars)) && (count($getvars) > 0))
			{
				$value_pairs = array();
				
				foreach ($getvars as $key=>$val)
				{
				   $value_pairs[] = rawurlencode(modMailThisPageHelper::clean($key)).'='.rawurlencode(modMailThisPageHelper::clean($val)); 			  
				}
				$querystring = '?'.implode('&',$value_pairs);
				
				//$currentURL = JURI::base(). 'index.php' . $querystring;
				$currentURL = (JURI::getInstance()->toString());
			
			}
		  
		  }
		  
		   
	   }
	      
	   $message = JText::_("YOURFRIEND").' '. $lists->mtp_user_name .', '. JText::_("EMAILADDRESS") .' '. $lists->mtp_user_email .', '. JText::_("RECOMMENDSPAGE") ."\r\n";
	   $message .= '<a href="'. $currentURL . '">'. "\r\n";
	   $message .= $currentURL . '</a><br /><br />' . "\r\n";
	   $message .= JText::_("ENJOYVISIT");
	   //$message .= '<br /><hr /><br />';
	   //$message .= 'This message is sent using the Mail This Page Module by '. "\r\n";
	   //$message .= '<a href="http://www.spiralscripts.co.uk">Spiral</a>';
	   
       $mailer =& JFactory::getMailer();
	   $mailer->setSender( $lists->mtp_from_email );
	   $mailer->addRecipient($lists->mtp_friend_email);
	   if (JMailHelper::isEmailAddress($lists->mtp_bcc))
	   {
	      $mailer->addBCC($lists->mtp_bcc);
	   }
	   $mailer->setSubject(JText::_('MESSAGEFROM') .' '. $mainframe->getCfg('sitename'));
	   $mailer->setBody($message);
	   $mailer->IsHTML(true);
	   
	   if ( $mailer->Send() !== true )
	   {
	     $lists->error = true;
		 $lists->error_message = JText::_("ERRORSENDINGMESSAGE");	 
         $js = 'window.alert("'. JText::_("ERRORSENDINGMESSAGE") .'");';
	   }
	   else
	   {
	     $lists->success_message = JText::_("SUCCESSSENDINGMESSAGE");	
         $js = 'window.alert("'. JText::_("SUCCESSSENDINGMESSAGE") .'");';
		 
	   }
	   $domready = "window.addEvent('domready', function() { ". $js ." });";
	   $document->addScriptDeclaration($domready);	
	   
	 }  
  
  
  }

  function clean($v = '')
  {
				$val = str_replace('"', '&quot;',$v);
				$val = str_replace('<', '&lt;',$val);
				$val = str_replace('>', '&gt;',$val);
				$val = preg_replace('/eval\((.*)\)/', '', $val);
				$val = preg_replace('/[\\\"\\\'][\\s]*javascript:(.*)[\\\"\\\']/', '""', $val);
	            return $val;
	  
  }

}
