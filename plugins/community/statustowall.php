<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');

jimport('joomla.html.pagination');

class plgCommunityStatusToWall extends CApplications
{
	var $name		= 'StatusToWall';
	var $_name		= 'statustowall';

    function plgCommunityStatusToWall(& $subject, $config)
    {
		$this->_user	=& CFactory::getActiveProfile();
		$this->_my		=& CFactory::getUser();

		parent::__construct($subject, $config);
    }
    
	function onProfileStatusUpdate( &$userid, &$old_status, &$new_status) 
	{
		JPlugin::loadLanguage('plg_statustowall', JPATH_ADMINISTRATOR);
			
		$my	=& CFactory::getUser();
		$user=& CFactory::getUser($userid);
		include_once JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'wall.php';
		CWallLibrary::saveWall( $userid , JText::sprintf('STATUS UPDATE', $new_status ), 'user' , $my , ( $my->id == $user->id ) );
	}
}