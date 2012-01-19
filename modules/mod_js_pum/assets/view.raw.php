<?php

/**
	* @package JomSocial People You Many Know
	* @copyright Copyright (C) 2010-2011 Techjoomla, Tekdi Web Solutions . All rights reserved.
	* @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
	* @link     http://www.techjoomla.com
	*/ 
	defined('_JEXEC') or die();
	jimport( 'joomla.application.component.view');
	class jompumViewjompum extends CommunityView
	{
		function display()
		{
			$database = &JFactory::getDBO();
			$my = &JFactory::getUser();

			$ignore_id=JRequest::getVar('js_ignore_id');
			if($ignore_id)
			{
				//Query to find if logged in user has already blocked the same user...
				$qry1= "SELECT user_id, ignore_id FROM #__community_sug_ig WHERE user_id=".$my->id." AND ignore_id=".$ignore_id;
				$database->setQuery($qry1);
				$existing=$database->loadObjectList(); 
	
				if(!$existing)
				{
					$data = new stdClass;
					$data->id = NULL;
					$data->user_id = $my->id;
					$data->ignore_id = $ignore_id;
					 if (!$database->insertObject( '#__community_sug_ig', $data )) {	
					 	echo "0";
					 }
					 else{	
					 	echo "1";
					 }
				}
			}
		}
	}
