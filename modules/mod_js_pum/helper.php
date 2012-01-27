<?php 
/**
* @package JomSocial People You Many Know
* @copyright Copyright (C) 2010 -2011 Techjoomla, Tekdi Web Solutions . All rights reserved.
* @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
* @link     http://www.techjoomla.com
*/   

defined( '_JEXEC' ) or die( 'Restricted Access' );

class modPumHelper
{

	function getitemid()
	{
		$database = &JFactory::getDBO();
		$Itemid = 0;
		if ($Itemid < 1)
		{
			$database->setQuery("SELECT id FROM #__menu WHERE link LIKE '%index.php?option=com_community%' AND published = 1");
			$Itemid = $database->loadResult();
			if ($Itemid < 1){
		 	   $Itemid = 0;
			}
		}
		return $Itemid;
	}
	function Check_Jbolo()
	{
		$jspath = JPATH_ROOT.DS.'components'.DS.'com_jbolo';
		if(JFolder::exists($jspath)){
			return 1;
		}
	}

	function getprobables(&$params)
	{
		//Requiured Declarations
		$doc      = &JFactory::getDocument();
		$database = &JFactory::getDBO();
		$my       = &JFactory::getUser();
		
		//$photoz      = intval( $params->get( 'photo', 1 ) );
		//$rando      = intval( $params->get( 'rando', 0 ) );

		//Check if we are on a JS page
		$page=JRequest::getVar('option');
		if($page != 'com_community'){
			$doc->addScript(JURI::base()."components/com_community/assets/window-1.0.js");
			$doc->addStyleSheet( JURI::base()."components/com_community/assets/window.css" );
		}
		
		$filename = JPATH_SITE . DS .'components'. DS .'com_community'.DS.'views'.DS.'jompum'.DS.'view.raw.php';
		$dirname  = JPATH_SITE . DS .'components'. DS .'com_community'.DS.'views'.DS.'jompum';
		$source1  = JPATH_SITE . DS .'modules'. DS .'mod_js_pum'. DS .'assets'.DS.'view.raw.php';
		$source2  = JPATH_SITE . DS .'modules'. DS .'mod_js_pum'. DS .'assets'.DS.'jompum.php';
	
		if(!JFile::exists($filename))
		{
			if(!is_dir($dirname)){
				mkdir(JPATH_SITE . DS .'components'. DS .'com_community'.DS.'views'.DS.'jompum');
			}
			JFile::copy($source1,$filename);
		}
		
		$filename = JPATH_SITE . DS .'components'. DS .'com_community'.DS.'controllers'.DS.'jompum.php';
		if(!JFile::exists($filename)){
			JFile::copy($source2,$filename);
		}
		$doc->addScript(JURI::base()."modules/mod_js_pum/js/pum.js");

		// Check if user is logged in & show appropriate message
		if(!$my->id) 
		{
			echo JText::_('MOD_JS_PUM_LOGIN_MSG');
			return;
		}

		// Get connected users
		$con_query = "SELECT connect_to " .
		"FROM #__community_connection " .
		"WHERE connect_from=".$my->id." AND status=1";
		$database->setQuery($con_query);
		$connections_aa = $database->loadResultArray();
		$cons_frnds = implode(',',$connections_aa);
		$not_cons = $cons_frnds;
		
		//Check If there are any connected users , if not the return
		if(empty($connections_aa))
		{
			echo '<div>';
			echo JText::_('MAT_MSGFG' );
			echo "</div>";return null;
		}
						 
		//Get all users to whom request has been already sent by user
		$con_query = "SELECT connect_to " .
		"FROM #__community_connection " .
		"WHERE connect_from=".$my->id." AND status=0  ";
		$database->setQuery($con_query);
		$connections_invited = $database->loadResultArray();

		if(!empty($connections_invited)){	
			$conn_invited = implode(',',$connections_invited); $not_cons .= ",".$conn_invited;
		}

		//Get all users who have already sent request to user
		$con_query = "SELECT connect_from " .
		"FROM #__community_connection " .
		"WHERE connect_to=".$my->id." AND status=0  ";
		$database->setQuery($con_query);
		$connections_frominvited = $database->loadResultArray();
		if(!empty($connections_frominvited)){	
			$conn_from_invited = implode(',',$connections_frominvited); $not_cons .= ",".$conn_from_invited;
		}

		//create table if not exists to store ignored users.
		$query = 'CREATE TABLE IF NOT EXISTS `#__community_sug_ig` (
		`id` int(10) NOT NULL auto_increment,
		`user_id` int(11) NOT NULL,
		`ignore_id` int(11) NOT NULL,
		PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1';
		$database->setQuery($query);
		$database->query();

		//Get all ignored users
		$ignore_qry = "SELECT ignore_id FROM #__community_sug_ig WHERE user_id=".$my->id;
		$database->setQuery($ignore_qry);
		$existing   = $database->loadResultArray(); 

		if(!empty($existing)){	
			$cons_existing = implode(',',$existing); $not_cons .= ",".$cons_existing;
		}

		//Get all suggestions  */
		/*	
		$conp_query = "SELECT connect_to FROM #__community_connection	WHERE connect_from IN ($cons_frnds)	
		AND connect_to NOT IN ($not_cons,$my->id) ORDER BY RAND()";	
		*/
		//query changed by manoj to hide pending requests
		$conp_query = "SELECT connect_to FROM #__community_connection	WHERE connect_from IN ($cons_frnds)	
		AND connect_to NOT IN ($not_cons,$my->id) AND status=1 ORDER BY RAND()";	
		
		$database->setQuery($conp_query);
		$connections_ua = $database->loadResultArray();

		if(!empty($connections_ua)){
			return $connections_ua;
		}
		else
		{
			echo '<div>';
			echo JText::_('MAT_MSGN');
			echo "</div>";
			return;				
		}
	}		
	
	function getfinalprobables($connections_ua,&$params)
	{
		$photoz      = intval( $params->get( 'photo', 1 ) );
		//echo $photoz;die;
		$no_of_users = $params->get('no_of_users',5);
		$rando      = intval( $params->get( 'rando', 1 ) );
		
		$database = &JFactory::getDBO();
		$arranged = array();
		$arranged = array_unique($connections_ua);
		$arranged = array_values($arranged);
		//$compare_fields = $params->get('fields');
		
		
		//if (is_array($compare_fields))		
		///	$compare_fields = implode(',', $compare_fields);
			//print_r($compare_fields);die;
		
		
		$z=count($arranged);
		if($z==0)
		{
			echo '<div>';
			echo JText::_('MAT_MSGN');
			echo "</div>";
			return;
		}
		$k = 0 ;
		while($k<count($arranged))		
		{
			$q="SELECT a.id,a.username,a.name,b.thumb ,s.session_id 
			FROM #__users a,#__community_users b 
			LEFT JOIN #__session s ON s.userid=".$arranged[$k].
			" WHERE a.id=b.userid AND a.id=".$arranged[$k];
	
			if ($photoz == 1)
				{
					$q.= " AND b.avatar like 'images/avatar%' ";
					
				}
				
				
			
			//echo $q;die;
			$database->setQuery($q);
		
			$users[$k]= $database->loadObject();
			//print_r($users[$k]);die;
			$k++;
		}
		if( $rando == 1 && !empty($users))
				{
					shuffle ( $users);
					$rows1=array_slice($users, 0, $no_of_users);
				}
				else
					$rows1=$users;
			
		//$con_matches=count($users);
		$con_matches=count($rows1);
		if ($con_matches){
			return $rows1;
		}
		else
		{
			echo '<div>';
			echo JText::_('MAT_MSGN');
			echo "</div>";
		}
	}
}
