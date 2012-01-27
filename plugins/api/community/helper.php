<?php

class CommunityAPIHelper {

	function setup()
	{
		require_once ( JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'defines.community.php');
		require_once (COMMUNITY_COM_PATH.DS.'libraries'.DS.'error.php');
		require_once (COMMUNITY_COM_PATH.DS.'controllers'.DS.'controller.php');
		require_once (COMMUNITY_COM_PATH.DS.'libraries'.DS.'apps.php' );
		require_once (COMMUNITY_COM_PATH.DS.'libraries'.DS.'core.php');
		require_once (COMMUNITY_COM_PATH.DS.'libraries'.DS.'template.php');
		require_once (COMMUNITY_COM_PATH.DS.'views'.DS.'views.php');
		require_once (COMMUNITY_COM_PATH.DS.'helpers'.DS.'url.php');
		require_once (COMMUNITY_COM_PATH.DS.'helpers'.DS.'ajax.php');
		require_once (COMMUNITY_COM_PATH.DS.'helpers'.DS.'time.php');
		require_once (COMMUNITY_COM_PATH.DS.'helpers'.DS.'owner.php');
		require_once (COMMUNITY_COM_PATH.DS.'helpers'.DS.'azrul.php');
		require_once (COMMUNITY_COM_PATH.DS.'helpers'.DS.'string.php');
		require_once (COMMUNITY_COM_PATH.DS.'events'.DS.'router.php');
	}

}