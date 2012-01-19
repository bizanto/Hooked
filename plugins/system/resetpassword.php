<?php defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class plgSystemResetPassword extends JPlugin
{
	function plgSystemResetPassword( &$subject, $params )
	{
		parent::__construct( $subject, $params );	
	}

    function onAfterRender()
	{
      $html = JResponse::getBody() ;
      $onepointsix = '' ;
      
      if(file_exists(JPATH_SITE . '/components/com_users') ) {
      	$onepointsix = 's' ;
      }
   
      $passwordlink = JRoute::_( 'index.php?option=com_user'.$onepointsix.'&view=reset' ) ; 
      
      
	  $html = str_replace( $passwordlink , JRoute::_( 'index.php?option=com_resetpassword' )  , $html );  
	  
	  
	  JResponse::setBody($html);
	}
}

?>