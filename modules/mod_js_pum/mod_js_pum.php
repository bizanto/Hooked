<?php
	/**
	* @package JomSocial People You Many Know
	* @copyright Copyright (C) 2010 -2011 Techjoomla, Tekdi Web Solutions . All rights reserved.
	* @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
	* @link     http://www.techjoomla.com
	*/ 
	defined('_JEXEC') or die('Restricted access');
	require_once(JPATH_SITE . DS .'components'. DS .'com_community'. DS .'libraries'. DS .'core.php');
	require_once (dirname(__FILE__).DS.'helper.php');
	require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'tooltip.php' );
	$js='/assets/script-1.2.js';
	CAssets::attach($js, 'js');
	
	$users = null;
	$disp_name = $params->get('disp_name',0);
	$no_of_users = $params->get('no_of_users',5);
	$no_of_rows = $params->get('no_of_rows',5);
	$int_jbolo = intval( $params->get( 'int_jbolo', 1 ) );
	//$photoz=$params->get('photo',1);
	if($no_of_rows==0){
		$no_of_rows=1;
	}
	$no_of_columns = $params->get('no_of_columns',1);
	if($no_of_columns==0){
		$no_of_columns=1;
	}
	$squares=$no_of_columns*$no_of_rows;
	if($squares<$no_of_users){
		$no_of_users=$squares;
	}
	$image = intval( $params->get( 'image', 1 ) );	
	$photoz = intval( $params->get( 'photo', 0 ) );			
	$width=94/$no_of_columns;
	$width--;	
		
	$Itemid = modPumHelper::getitemid();
	
	
	$exists			= modPumHelper::Check_Jbolo();
	
	if(!$exists){ 
		$int_jbolo = 0 ;
	} 	
	else
	{
		 include(JPATH_SITE . DS .'components'. DS .'com_jbolo'. DS .'config'. DS .'config.php');
		if($chat_config['fonly']) {
			$int_jbolo = 0 ;
		}
	}
$users_crude = modPumHelper::getprobables($params);
	if($users_crude)
	{
		$most_popular = array_count_values($users_crude); 
		$users = modPumHelper::getfinalprobables($users_crude,$params); 
		if(count($users)<$no_of_columns && count($users)!=0)
		{
			$width=94/count($users);
			$width--;	
		} 	
		require(JModuleHelper::getLayoutPath('mod_js_pum'));
	}
?>
