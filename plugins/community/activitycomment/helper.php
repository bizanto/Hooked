<?php
/**
 * @package		ActivityComment
 * @copyright	Copyright (C) 2009 - 2010 SocialCode. All rights reserved.
 * @license		GNU/GPL
 * ActivityComment is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses.
 */
defined('_JEXEC') or die('Restricted access');
class ActivityComments{

	function getjs(){
		static $cmd = null;
		
		if(is_null($cmd))
		{
			$xmlParser =& JFactory::getXMLParser('Simple');
			$file = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_community'.DS.'community.xml';
			$xmlParser->loadFile($file);
			$document =& $xmlParser->document;
			$element =& $document->getElementByPath( 'version' );
			
			$version = explode('.',$element->data());
			
			$cmd	= 'jQuery';
			if( $version[1] >= 7)
				$cmd	= 'joms.jQuery';
		}
		return $cmd;
	}

	public function replaceURL( $message , $noFollow = false , $newWindow = false )
	{
		$replace	= ($noFollow) ? ' rel="nofollow"' : '';
		$replace	.= ($newWindow) ? ' target="_blank"' : '';
		
		$message	= preg_replace('@((http|ftp)+(s)?://([-\w\.]+)+(:\d+)?(([\w/_\.\-\+])*([a-zA-Z=_.?\-/&amp;\d])+)+)@', '<a href="$1"' . $replace . '>$1</a>', $message );
		
		return $message;
	}
	
	function subscribed( $activity_id ){
		$my = JFactory::getUser();
		$db = JFactory::getDBO();
		
		$a = 'select count(*) from #__activity_subscribe where '
			. '`activity_id`=' . $db->Quote( $activity_id ) . ' and '
			. '`user_id`=' . $db->Quote( $my->id );
		$db->setQuery( $a );
		$subscribed = $db->loadResult();
		return $subscribed;
	}
	
	function getAct( $id ){
		$db=& JFactory::getDBO();
		
		$q = 'select * from #__community_activities where id=' . $db->Quote($id);
		$db->setQuery($q);
		
		return $db->loadObject();
	}
	
	function isFriends( $target )
	{
		$my=JFactory::getUser();
		require_once( JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'helpers'.DS.'friends.php');
		return friendIsConnected( $my->id , $target );
	}
	
	function getComments( $id , $getMore = false ){
		$db=& JFactory::getDBO();
		
		$params	= ActivityComments::getParams();
		$order	= $params->get('commentordering' , 'asc' );
		$limit	= $params->get('commentlimit' , '5' );
		$query = 'select a.* from #__community_wall AS a ';
		$query	.= 'inner join #__users AS b on a.post_by=b.id ';
		$query	.= 'where a.contentid=' . $db->Quote($id) . ' and a.type=' . $db->Quote('activity');
		$query	.= 'and b.block=0';
		$query .= ' order by a.id ' . $order;

		if($limit != 0 && !$getMore)
		{
			$query	.= ' LIMIT 0 ,' . $limit;
		}
		
		if( $getMore )
		{
			$total	= ActivityComments::getTotalComments($id);

			$query	.= ' LIMIT ' . $limit . ',' . $total;
		}
		//echo $query;exit;
		$db->setQuery($query);
		$rows=$db->loadObjectList();
		
		$config = JFactory::getConfig();
		$my = JFactory::getUser();
		
		$offset = $my->getParam( 'timezone' , $config->getValue( 'offset' ) );
		$config = CFactory::getConfig();
		//Format date
		foreach($rows as $row )
		{
			$date = JFactory::getDate( $row->date );
			$date->setOffset( $offset + $config->get('daylightsavingoffset') );
			$row->date	= $date->toMySQL( true );
		}
		return $rows;
	}

	function getTotalComments( $id ){
		$db=& JFactory::getDBO();
		
		$query = 'select count(*) from #__community_wall AS a ';
		$query	.= 'inner join #__users AS b on a.post_by=b.id ';
		$query	.= 'where contentid=' . $db->Quote($id) . ' and type=' . $db->Quote('activity') . ' ';
		$query	.= 'and b.block=0';

		$db->setQuery($query);
		$total	= $db->loadResult();
		return $total;
	}
	
	function hasMoreComments( $id ){
		$db=& JFactory::getDBO();
		
		$params	= ActivityComments::getParams();

		$limit	= $params->get('commentlimit' , '5' );
		$query = 'select count(*) from #__community_wall AS a ';
		$query	.= 'inner join #__users AS b ON a.post_by=b.id ';
		$query	.= 'where contentid=' . $db->Quote($id) . ' and type=' . $db->Quote('activity') . ' ';
		$query	.= 'and b.block=0';
		$db->setQuery($query);
		if($limit == 0 )
			return false;
		
		$total	= $db->loadResult();
		
		if( $total > $limit )
			return true;
			
		return false;
	}
	
	function getCurrent(){

		$url		= 'index.php?';
		$segments	=& $_GET;
			
		$i			= 0;
		$total		= count( $segments );
		foreach( $segments as $key => $value )
		{
			++$i;
			$url	.= $key . '=' . $value;
			
			if( $i != $total )
			{
				$url	.= '&';
			}					
		}

		// @rule: clean url
		$url	= urldecode( $url );
		$url 	= str_replace('"', '&quot;',$url);
		$url 	= str_replace('<', '&lt;',$url);
		$url	= str_replace('>', '&gt;',$url);
		$url	= preg_replace('/eval\((.*)\)/', '', $url);
		$url 	= preg_replace('/[\\\"\\\'][\\s]*javascript:(.*)[\\\"\\\']/', '""', $url);
 		
		return $url;
	}
	
	function getLikes($id){
		$db=&JFactory::getDBO();
		$query = 'select * from #__activitylikes AS a ';
		$query	.= 'inner join #__users AS b on a.userid=b.id ';
		$query	.= 'where a.activityid=' . $db->Quote($id) . ' ';
		$query	.= 'and b.block=0';
		
		$db->setQuery($query);
		$rows=$db->loadObjectList();
return $rows;
	}

	function getLikesCount($id){
		$db=&JFactory::getDBO();
		$query = 'select count(*) from #__activitylikes where activityid=' . $db->Quote($id);
		$db->setQuery($query);
		$rows=$db->loadResult();
return $rows;
	}
	
	function hasLike($id){
		$my=&JFactory::getUser();
		$db=& JFactory::getDBO();
		$query = 'select count(*) from #__activitylikes where activityid=' . $db->Quote($id). ' and userid=' . $db->Quote($my->id);
		$db->setQuery($query);
		$rows=$db->loadResult() >= 1 ? true : false;

return $rows;
	}

	function isGuest(){
	$my =& JFactory::getUser();
	
	if($my->id == 0 )
	return true;
	
	return false;
	}
	
	function isSiteAdmin(){
	$my =& JFactory::getUser();
	
		if( $my->usertype == 'Super Administrator' || $my->usertype == 'Administrator' || $my->usertype == 'Manager' )
		return true;
		
		return false;
	}
	
	function getParams(){
		$db	= JFactory::getDBO();
		$query = 'SELECT params FROM #__plugins WHERE element="activitycomment" AND folder="community"';
		$db->setQuery($query);
		$params	= new JParameter($db->loadResult());
		return $params;
	}
}