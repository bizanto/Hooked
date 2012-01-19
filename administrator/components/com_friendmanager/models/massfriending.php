<?php
/**
 * @version $Id$
 * @package    FriendManager
 * @subpackage _ECR_SUBPACKAGE_
 * @author     Socialable Studios {@link http://www.Socialables.com}
 * @author     Created on 16-Jan-2010
 * @copyright	Copyright (C) 2005 - 2010 Socialables.com All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

//-- No direct access
defined('_JEXEC') or die('=;)');

jimport( 'joomla.application.component.model' );

/**
 * FriendManager Model
 *
 * @package    FriendManager
 * @subpackage Models
 */
class FriendManagersModelMassFriending extends JModel
{
	
	function __construct()
	{
	 	parent::__construct();
	
	}//function
	
	function massUpdate($backup, $friends)
		{
			//$model =& $this->getModel('Backup');
			if ($backup->backup())$backup->clearDB();
			//$friends =& $this->getModel('Friending');		
			$userids = $this->_getAllUsers();	
			$uids = $userids;

			$i=0;														
			foreach ($userids as $user)
			{																			
				$output = array_slice($uids, $i);	
																		
					foreach ($output as $all)
					{	//start with the +1 of the current row				   //to friend		from friend																			
						if ($all->userid != $user->userid)$friends->addFriend($all->userid, $user->userid);															
					}
						
				$i++;																													
			}
			$friends->correctFriendCounts();
			if (count($output) <= 1) return true;
			else return false;
		}

	function ffUpdate($backup, $friending)
		{
			//$model =& $this->getModel('Backup');
			$backup->backup('Backup before First Friend Update');
			$plugin =& JPluginHelper::getPlugin('user', 'firstfriend');
			$this->params 	= new JParameter( $plugin->params );
			//$friending =& $this->getModel('Friending');
			
				$firstfriend = $this->params->get( 'firstfriend' , 62 );
				$friends = explode(',', $firstfriend);
				$userids = $this->_getAllUsers();
				$method = $this->params->get( 'method' , 0 );
				if ($friends[1])
					{	
						foreach ($friends as $friend)
						{					
							//parse through all id's
							foreach ($userids AS $row)
									{
										//for each user send a friend request to the user from the firstfriend
										if (!$friending->getFriendConnection($row->userid, $friend))
										if ($row->userid != $friend)$friending->addFriend($row->userid, $friend);								
										
									}
						}
					}
				else
					{
												//parse through all id's
						foreach ($userids AS $row)
								{
									//for each user send a friend request to the user from the firstfriend
									if (!$friending->getFriendConnection($row->userid, $firstfriend))
									if ($row->userid != $firstfriend)$friending->addFriend($row->userid, $firstfriend);							
								}
					}
					$friending->correctFriendCounts();
					return true;
						
		}		
	
	function _getAllUsers()
	{
		//$db = &JFactory::getDBO();

			$query="SELECT ". $this->_db->nameQuote( 'userid' ) . " FROM ". $this->_db->nameQuote( '#__community_users' );
				$this->_db->setQuery($query);
				$result = $this->_db->loadObjectList();

		return $result;		
	}	      
}//class