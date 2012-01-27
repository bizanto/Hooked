<?php
/**
 * @category	Model
 * @package		JomSocial
 * @subpackage	Activities 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

require_once ( JPATH_ROOT .DS.'components'.DS.'com_community'.DS.'models'.DS.'models.php');

/**
 *
 */ 
class CommunityModelActivities extends JCCModel
{

	function __construct()
	{
		// Get cache object.
 	 	$oCache = CCache::inject($this);
 	 	$oCache->addMethod('add'              , CCache::ACTION_REMOVE, array(COMMUNITY_CACHE_TAG_ACTIVITIES));
 	 	$oCache->addMethod('removeOneActivity', CCache::ACTION_REMOVE, array(COMMUNITY_CACHE_TAG_ACTIVITIES));
 	 	$oCache->addMethod('removeActivity'   , CCache::ACTION_REMOVE, array(COMMUNITY_CACHE_TAG_ACTIVITIES));
 	 	$oCache->addMethod('deleteActivity'   , CCache::ACTION_REMOVE, array(COMMUNITY_CACHE_TAG_ACTIVITIES));
		
		parent::__construct();
	}

	/**
	 * Return an object with a single activity item
	 */	 	
	function getActivity($activityId) 
	{
		$db	=& $this->getDBO();
		$query	= 'SELECT * '
				. 'FROM ' . $db->nameQuote( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'id' ) . '=' . $db->Quote( $activityId );
		
		$db->setQuery( $query );
		$act	= $db->loadObject();
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		
		return $act;
	}
	
	/**
	 * Retrieves the activity content for specific activity
	 * @return string
	 **/	 
	function getActivityContent( $activityId )
	{
		$db	=& $this->getDBO();
		
		$query	= 'SELECT ' . $db->nameQuote( 'content' ) . ' '
				. 'FROM ' . $db->nameQuote( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'id' ) . '=' . $db->Quote( $activityId );
		
		$db->setQuery( $query );
		
		$content	= $db->loadResult();
		
		// If the content is a command, execute them
		
		return $content;
	}
	
	/**
	 * Retrieves the activity stream for specific activity
	 *
	 **/	 
	function getActivityStream( $activityId )
	{
		$db	=& $this->getDBO();
		
		$query	= 'SELECT * '
				. 'FROM ' . $db->nameQuote( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'id' ) . '=' . $db->Quote( $activityId );
		
		$db->setQuery( $query );
		
		$content	= $db->loadObject();
		
		return $content;
	}	
	
	function add($actor, $target, $title, $content, $appname = '', $cid=0, $params='', $points = 1, $access = 0){
		jimport('joomla.utilities.date');
		
		$db	 = &$this->getDBO();
		$today =& JFactory::getDate();
		
		$obj = new StdClass();
		$obj->actor 	= $actor;
		$obj->target 	= $target;
		$obj->title		= $title;
		$obj->content	= $content;
		$obj->app		= $appname;
		$obj->cid		= $cid;
		$obj->params	= $params;
		$obj->created	= $today->toMySQL();
		$obj->points	= $points;
		$obj->access	= $access;
		
		// Trigger for onBeforeStreamCreate event.
		CFactory::load( 'libraries' , 'apps' );
		$appsLib	=& CAppPlugins::getInstance();
		$appsLib->loadApplications();		
		
		$params		= array();
		$params[]	= &$obj;
		
		$result			= $appsLib->triggerEvent( 'onBeforeStreamCreate' , $params);

		if( in_array( true , $result ) || empty($result) )
		{
			return $db->insertObject('#__community_activities', $obj);
		}

		return false;
	}
	
	
	/**
	 * For photo upload, we should delete all aggregated photo upload activity,
	 * instead of just 1 photo uplaod activity	 
	 */	 	
	function hide($userId , $activityId )
	{
		$db		=& $this->getDBO();
		
		// 1st we compare if the activity stream author match the userId. If yes,
		// archive the record. if not, insert into hide table.
		$activity	= $this->getActivityStream($activityId);
		
		if(! empty($activity))
		{
			if($activity->actor == $userId)
			{
				$query	= 'UPDATE `#__community_activities`';
				$query	.= ' SET `archived` = 1';
				$query	.= ' WHERE `app` = ' . $db->Quote($activity->app);
				$query	.= ' AND `cid` = ' . $db->Quote($activity->cid);
				$query	.= ' AND `title` = ' . $db->Quote($activity->title);
				$query	.= ' AND DATEDIFF( created, ' . $db->Quote($activity->created) . ' )=0';
				$db->setQuery($query);
				$db->query();
				if($db->getErrorNum()) {
					JError::raiseError( 500, $db->stderr());
				}				
			}
			else
			{
				$query	= 'SELECT `id` FROM `#__community_activities`';
				$query	.= ' WHERE `app` = ' . $db->Quote($activity->app);
				$query	.= ' AND `cid` = ' . $db->Quote($activity->cid);
				$query	.= ' AND `title` = ' . $db->Quote($activity->title);
				$query	.= ' AND DATEDIFF( created, ' . $db->Quote($activity->created) . ' )=0';
				
				$db->setQuery($query);
				$db->query();
				if($db->getErrorNum()) {
					JError::raiseError( 500, $db->stderr());
				}
				
				$rows	= $db->loadResultArray();
				
				if(!empty($rows))
				{
					foreach($rows as $key=>$value)
					{
						$obj				= new stdClass();
						$obj->user_id		= $userId;
						$obj->activity_id	= $value;
						
						$db->insertObject('#__community_activities_hide' , $obj);
						if($db->getErrorNum()) {
							JError::raiseError( 500, $db->stderr());
						}
					}
				}
				
			}
		}
		
		return true;
	}


	/**
	 * Return rows of activities
	 */	 	
	function getActivities($userid='', $friends='', $afterDate = null, $maxEntries=20 , $respectPrivacy = true , $exclusions = null , $displayArchived = false ){
		$db	 = &$this->getDBO();
		$my  = CFactory::getuser();

		$todayDate	= new JDate();

		// Oversampling, to cater for aggregated activities
		$maxEntries = ($maxEntries < 0) ? 0 : $maxEntries;
		$maxEntries = $maxEntries*8;

		$orWhere = array();
		$andWhere = array();
		$onActor = '';
		//default the 1st condition here so that if the date is null, it wont give sql error.
		if( !$displayArchived )
		{
			$andWhere[] = "`archived`=0";
		}
		
		if(!empty($userid)){
			$orWhere[] = "(a.`actor`=" . $db->Quote($userid) .")";
			$onActor = " AND (a.`actor`=". $db->Quote($userid) .")";
		}

		// 
		if(!empty($friends)) {
			$orWhere[] = "(a.`actor` IN (".implode(',',$friends). ")" .")";
		}
		
		if(!empty($userid))
			$orWhere[] = "(a.`target`=" . $db->Quote($userid).")";
		
		if(!empty($afterDate))
			$andWhere[] = "(a.`created` between ".$db->Quote($afterDate->toMySQL())." and ".$db->Quote($todayDate->toMySQL()).")" ;
		
		if( !is_null( $exclusions) )
		{
			
			$exclusionQuery	= '(a.`id` NOT IN (';

			for($i=0; $i < count( $exclusions);$i++)
			{
				$exclusion	= $exclusions[ $i ];
				$exclusionQuery	.= $db->Quote( $exclusion );
				
				if( $i != (count( $exclusions ) - 1) )
				{
					$exclusionQuery	.= ',';
				}
			}
			$exclusionQuery .= ') )';
			$andWhere[]	= $exclusionQuery;
		}
		
		if( $respectPrivacy )
		{
			// Add friends limits, but admin should be able to see all
			// @todo: should use global admin code check instead
			if($my->id == 0){
				// for guest, it is enough to just test access <= 0
				$andWhere[] = "(a.`access` <= 10)";
				
			}elseif( !( $my->usertype == 'Super Administrator'
					|| $my->usertype == 'Administrator'
					|| $my->usertype == 'Manager' ))
			{
				$orWhere[] = "((a.`access` = 0) {$onActor})";
				$orWhere[] = "((a.`access` = 10) {$onActor})";
				$orWhere[] = "( (a.`access` = 20) AND ({$my->id} != 0)  {$onActor})";
				if($my->id != 0)
				{
					$orWhere[] = "( (a.access = 30) AND (a.actor = {$my->id}) {$onActor})";
					$orWhere[] = "( (a.access = 30) AND (a.actor IN (SELECT c.`connect_to`
							FROM `#__community_connection` as c
							WHERE
								c.`connect_from` = {$my->id}
							AND
								c.`status` = 1) ) {$onActor} )";
				}
			} 
		}

		if(!empty($userid))
		{
			//get the list of acitivity id in archieve table 1st.
			$subQuery	= 'SELECT b.`activity_id` FROM #__community_activities_hide as b WHERE b.`user_id` = '. $db->Quote($userid);
			$db->setQuery($subQuery);
			$subResult	= $db->loadResultArray();
			$subString	= implode(',', $subResult);
		
			if( ! empty($subString))
				$andWhere[] = "a.`id` NOT IN ($subString) ";
	    }			
		
		$whereOr = implode(' OR ', $orWhere);
		$whereAnd = implode(' AND ', $andWhere);
		
		// Actors can also be your friends
		// We load 100 activities to cater for aggregated content
		$date	= CTimeHelper::getDate(); //we need to compare where both date with offset so that the day diff correctly.
		
		$sql = "SELECT a.*, TO_DAYS(".$db->Quote($date->toMySQL(true)).") -  TO_DAYS( DATE_ADD(a.`created`, INTERVAL ".$date->getOffset()." HOUR ) ) as 'daydiff' "
			." FROM #__community_activities as a "
			." WHERE "
			." ( $whereOr ) AND "
			." $whereAnd ORDER BY a.`created` DESC LIMIT " . $maxEntries;				  
	
		// Remove the bracket if it is not needed
		$sql = JString::str_ireplace("WHERE  (  ) AND", ' WHERE ', $sql);
// 		echo $sql;exit;	
		$db->setQuery( $sql );
		$result = $db->loadObjectList();
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		
		// @todo: write a plugin that return the html part of the whole system
		return $result;
	}
	
	/**
	 * Return all activities by the given apps
	 */	 	
	function getAppActivities($appname, $identifier = null , $limit = '100' , $respectPrivacy = true , $exclusions = null , $displayArchived = false ){
		
		$db	 = &$this->getDBO();
		
		// Double the number of limit to allow for aggregator
		$limit = ($limit < 0) ? 0 : $limit;
		$limit = $limit*2;

		$displayArchived	= $displayArchived ? 1 : 0;

		$appsWhere = " `archived`=" . $db->Quote( $displayArchived ) . " AND `app`=".$db->Quote($appname);
				
		if($identifier != null)
			$appsWhere .= " AND `cid`=" . $db->Quote($identifier);
		
		if( !is_null( $exclusions) )
		{
			$appsWhere	.= ' AND (a.`id` NOT IN ('. implode( ',' , $exclusions) . ') )';
		}
		// Actors can also be your friends
		$date	= CTimeHelper::getDate(); //we need to compare where both date with offset so that the day diff correctly.

		$sql = "SELECT a.* , (DAY( '".$date->toMySQL(true)."' ) - DAY( DATE_ADD(a.`created`,INTERVAL ".$date->getOffset()." HOUR ) )) as 'daydiff' FROM #__community_activities as a WHERE "
			. $appsWhere
			." ORDER BY `created` DESC "
			."LIMIT " . $limit;
		
		$db->setQuery( $sql );
		$result = $db->loadObjectList();
		
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		
		// @todo: write a plugin that return the html part of the whole system
		return $result;
	}
	
	/**
	 * Remove any recently changed activities
	 */	 	
	function removeRecent($actor, $title, $app, $timeDiff){
	}
	
	/*
	 * Remove One Photo Activity
	 * As it's tricky to remove the activity since there's no photo id in the
	 * activity data. Here we get all the activities of 5 seconds within the
	 * activity creation time, then we try to match the photo id in the activity 
	 * params, and also the thumbnail in the activity content field. When all 
	 * fails, we fallback to removeOneActivity()	 
	 */
	function removeOnePhotoActivity( $app, $uniqueId, $datetime, $photoId, $thumbnail )
	{
		$db		= JFactory::getDBO();
		$query	= 'SELECT * FROM ' . $db->nameQuote( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'app' ) . '=' . $db->Quote( $app ) . ' '
				. 'AND ' . $db->nameQuote( 'cid' ) . '=' . $db->Quote( $uniqueId ) . ' '
				. 'AND ( ' . $db->nameQuote( 'created' ) . ' BETWEEN ' . $db->Quote( $datetime ) . ' '
				. 'AND ( ADDTIME(' . $db->Quote($datetime) . ', ' . $db->Quote('00:00:05') . ' ) ) ) '
				;
		$db->setQuery($query);
		$result	= $db->loadObjectList();
		
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		
		$activityId = null;
		$handler = new JParameter(null);
		
		// the activity data contains photoid and the photo thumbnail
		// which can be useful for us to find the correct activity id
		foreach ($result as $activity)
		{
			$handler->loadINI($activity->params);
			if ($handler->getValue('photoid')==$photoId)
			{
				$activityId = $activity->id;
				break;
			}
			if ( JString::strpos($activity->content, $thumbnail)!== false )
			{
				$activityId = $activity->id;
				break;
			}
		}
		
		if (is_null($activityId))
		{
			return $this->removeOneActivity($app, $uniqueId);
		}
		
		$query	= 'DELETE FROM ' . $db->nameQuote( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'id' ) . '=' . $db->Quote( $activityId ) . ' '
				. 'LIMIT 1 ' ;
		$db->setQuery( $query );
		$status	= $db->query();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $status;
	}
	
	function removeOneActivity( $app , $uniqueId )
	{
		$db		=& $this->getDBO();
		
		$query	= 'DELETE FROM ' . $db->nameQuote( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'app' ) . '=' . $db->Quote( $app ) . ' '
				. 'AND ' . $db->nameQuote( 'cid' ) . '=' . $db->Quote( $uniqueId ) . ' ' 
				. 'LIMIT 1 ' ;

		$db->setQuery( $query );
		$status	= $db->query();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $status;
	}
	
	function removeActivity( $app , $uniqueId )
	{
		$db		=& $this->getDBO();
		
		$query	= 'DELETE FROM ' . $db->nameQuote( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'app' ) . '=' . $db->Quote( $app ) . ' '
				. 'AND ' . $db->nameQuote( 'cid' ) . '=' . $db->Quote( $uniqueId ) ;

		$db->setQuery( $query );
		$status	= $db->query();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $status;
	} 
	
	function deleteActivity( $app , $uniqueId )
	{
		$db		=& $this->getDBO();
		
		$query	= 'DELETE FROM ' . $db->nameQuote( '#__community_activities' ) . ' '
				. 'WHERE ' . $db->nameQuote( 'app' ) . '=' . $db->Quote( $app ) . ' '
				. 'AND ' . $db->nameQuote( 'id' ) . '=' . $db->Quote( $uniqueId ) ;

		$db->setQuery( $query );
		$status	= $db->query();

		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		return $status;
	}
	
	/**
	 * Return the number of total activity by a given user 
	 */	 	
	function getActivityCount($userid) {
		$db	 = &$this->getDBO();
		
		
		$sql = "SELECT SUM(`points`) FROM #__community_activities WHERE "
			." `actor`=" . $db->Quote($userid);
		
		$db->setQuery( $sql );
		$result = $db->loadResult();
		
		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}
		
		// @todo: write a plugin that return the html part of the whole system
		return $result;
	}
	
	/**
	 * Retrieves total number of activities throughout the site.
	 * 
	 * @return	int	$total	Total number of activities.	 	 
	 **/	 	
	public function getTotalActivities(){
		$db		= JFactory::getDBO();
		$query	= 'SELECT COUNT(1) FROM #__community_activities';
		$db->setQuery( $query );
		$total	= $db->loadResult();
		
		return $total;
	}
	
	function updatePermission($access, $previousAccess , $actorId, $app = '' , $cid = '')
	{
		$db	 = &$this->getDBO();
		
		$query	= 'UPDATE `#__community_activities` SET `access` = ' . $db->Quote($access);
		$query	.= ' WHERE `actor` = ' . $db->Quote($actorId);

		if( $previousAccess > $access )
		{
			$query	.= ' AND `access` <' . $db->Quote( $access );
		}

		if( !empty( $app ) )
		{
			$query	.= ' AND `app` = ' . $db->Quote($app);
		}
		
		if(! empty($cid))
		{
			$query	.= ' AND `cid` = ' . $db->Quote($cid);
		}

		$db->setQuery( $query );
		$db->query();
		
		if($db->getErrorNum())
		{
			JError::raiseError( 500, $db->stderr());
		}
		
		return true;
	}
}
