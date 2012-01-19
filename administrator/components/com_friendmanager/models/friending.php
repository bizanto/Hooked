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
class FriendManagersModelFriending extends JModel
{
	
	function __construct()
	{
	 	parent::__construct();
	
	}//function
		
        /**
         * Save a friend request to stranger. Stranger will have to approve, approval makes connection both ways
         * @param	$id		int		stranger user id
         * @param   $fromid int     owner's id
         */
        function addFriendRequest($id, $fromid, $msg='')
        {
            $my = JFactory::getUser();
            $db = $this->_db;
            $wheres[] = 'block = 0';

            if ($my->id == $id)
            {
                JError::raiseError(500, JText::_('Cannot Add Your Self As A Friend'));
            }

            //@todo escape code
            $date	=& JFactory::getDate(); //get the time without any offset!
            $query	= "INSERT INTO #__community_connection SET"
				. ' `connect_from` = '.$db->Quote($fromid)
            	. ', `connect_to` = '.$db->Quote($id)
            	. ', `status` = 0'
            	. ', `created` = ' . $db->Quote($date->toMySQL())
				. ', `msg` = ' . $db->Quote($msg);

            $db->setQuery($query);
            $db->query();
            if ($db->getErrorNum())
            {
                JError::raiseError(500, $db->stderr());
            }

        }

        /**
         * Save connection between two people, auto approved and count added
         * @param	$id		int		stranger user id
         */
        function addFriend($id, $fromid)
        {
            $my = JFactory::getUser();
            $db = $this->_db;
            $wheres[] = 'block = 0';

            if ($my->id == $id)
            {
               return; //JError::raiseError(500, JText::_('Cannot Add Your Self As A Friend'));
            }

			$date	=& JFactory::getDate(); //get the time without any offset!
            //@todo escape code
            $query = "INSERT INTO #__community_connection SET"
            	. ' `connect_from`='.$db->Quote($fromid)
            	. ', `connect_to`='.$db->Quote($id)
            	. ', `status`=1'
            	. ', `created` = ' . $db->Quote($date->toMySQL());

            $db->setQuery($query);
            $db->query();
            if ($db->getErrorNum())
            {
                JError::raiseError(500, $db->stderr());
            }
            else
            {
            	$this->addFriendCount($from);            	
            }


            //@todo escape code
            $query = "INSERT INTO #__community_connection SET"
            	. ' `connect_from`='.$db->Quote($id)
            	. ', `connect_to`='.$db->Quote($fromid)
            	. ', `status`=1'
            	. ', `created` = ' . $db->Quote($date->toMySQL());

            $db->setQuery($query);
            $db->query();
            if ($db->getErrorNum())
            {
                JError::raiseError(500, $db->stderr());
            }
            else 
            {
            	$this->addFriendCount($id);
            }
        }

        function addFriendCount($userId)
        {
            $db = $this->_db;

            $query = 'SELECT '.$db->nameQuote('friendcount').' '
            .'FROM '.$db->nameQuote('#__community_users')
            .'WHERE '.$db->nameQuote('userid').'='.$db->Quote($userId);

            $db->setQuery($query);

            $count = $db->loadResult();

            if ($db->getErrorNum())
            {
                JError::raiseError(500, $db->stderr());
            }
            $count += 1;

            $query = 'UPDATE '.$db->nameQuote('#__community_users')
            .'SET '.$db->nameQuote('friendcount').'='.$db->Quote($count)
            .'WHERE '.$db->nameQuote('userid').'='.$db->Quote($userId);
            $db->setQuery($query);
            $db->query();

            if ($db->getErrorNum())
            {
                JError::raiseError(500, $db->stderr());
            }
            return;
        }
        
    	function resetFriendCounts()
    	{
    		$db = $this->_db;

            $query = 'UPDATE '.$db->nameQuote('#__community_users')
            .'SET '.$db->nameQuote('friendcount').'= 0';
            $db->setQuery($query);
            $db->query();

            if ($db->getErrorNum())
            {
                JError::raiseError(500, $db->stderr());
            }
            return true;
    	}

    	function correctFriendCounts()
    	{
    		$db = $this->_db;
			
    		$users = $this->_getAllUsers();
			//var_dump($users);
			foreach ($users AS $userId)
			{
				//$this->correctAutoIncrement($userId->userid);
	    		$count = $this->getFriendsCount($userId->userid);
	    		
	            $query = 'UPDATE '.$db->nameQuote('#__community_users')
	            .'SET '.$db->nameQuote('friendcount').'='.$db->Quote($count)
	            .'WHERE '.$db->nameQuote('userid').'='.$db->Quote($userId->userid);
	            $db->setQuery($query);
	            $db->query();
	
	            if ($db->getErrorNum())
	            {
	                JError::raiseError(500, $db->stderr());
            	}
			}
            return true;
    	} 

        function getFriendsCount($id)
        {
            // For visitor with id=0, obviously he won't have any friend!
            if ( empty($id))
            return 0;

            $db = $this->_db;

            // Search those we send connection
            $query = "SELECT count(*) "
            .' FROM #__community_connection as a, #__users as b'
            .' WHERE a.`connect_from`='.$db->Quote($id)
            .' AND a.`status`=1 '
            .' AND a.`connection_id` > 0 '
            .' AND a.`connect_to`=b.`id` '
            .' ORDER BY a.`connection_id` DESC ';

            $db->setQuery($query);
            $total = $db->loadResult();
            return $total;
        }    	

        function correctAutoIncrement($backup)
        {
        	$backup->backup('Backup before correcting Increment');
            $db = $this->_db;

            // Search those we send connection
            $query = "SELECT * "
            .' FROM #__community_connection as a'
            .' WHERE a.`connection_id`=0'
            .' ORDER BY a.`connection_id` DESC ';

            $db->setQuery($query);
            $badrecords = $db->loadObjectList();
            
            if ($badrecords)
            {
	            $query = "ALTER TABLE `#__community_connection` ADD PRIMARY KEY(`connection_id`)";
	            $db->setQuery($query);
		        $db->query();
		        
		        //$query = "ALTER IGNORE TABLE `#__community_connection` ADD UNIQUE KEY(`connection_id`)";
		        //$db->setQuery($query);
		        //$db->query();
		        
	            $query = "ALTER TABLE `#__community_connection`  CHANGE `connection_id` `connection_id` INT( 11 ) NOT NULL AUTO_INCREMENT";
	            $db->setQuery($query);
	            $db->query();            
	             
	            foreach ($badrecords AS $record)
	            {
	            	$query = "INSERT INTO #__community_connection SET"
	            	. ' `connect_from`='.$db->Quote($record->connect_from)
	            	. ', `connect_to`='.$db->Quote($record->connect_to)
	            	. ', `status`= '.$db->Quote($record->status)
	            	. ', `group`= '.$db->Quote($record->group)
	            	. ', `created` = '.$db->Quote($record->created)
	            	. ', `msg` = '.$db->Quote($record->msg);
	            	$db->setQuery($query);
		            $db->query();
		            
	            	$query = "DELETE FROM #__community_connection WHERE `connection_id` = 0"
	            	." AND `connect_from` = ".$db->Quote($record->connect_from)
	            	." AND `connect_to` = ".$db->Quote($record->connect_to)
	            	." AND `status` = ".$db->Quote($record->status)
	            	." AND `group` = ".$db->Quote($record->group);
	            	$db->setQuery($query);
		            $db->query();
		
		            if ($db->getErrorNum())
		            {
		                JError::raiseWarning(500, $db->stderr());
	            	}
	            }
            }
            
            return true;
        }
        
        
		function removeDuplicates($backup)
		{
			$backup->backup('Backup before remove duplicates');
            $db = $this->_db;

            
			$query = "CREATE TABLE IF NOT EXISTS #__community_connection_TEMP LIKE #__community_connection";
	            	$db->setQuery($query);
		            $db->query();	
				    if ($db->getErrorNum())
		            {
		                JError::raiseWarning(500, $db->stderr());
	            	}
			
			$query = "INSERT INTO #__community_connection_TEMP(`connect_from`,`connect_to`,`status`,`group`,`msg`) SELECT DISTINCT `connect_from`,`connect_to`,`status`,`group`,`msg` FROM #__community_connection";
	            	$db->setQuery($query);
		            $db->query();	
				    if ($db->getErrorNum())
		            {
		                JError::raiseWarning(500, $db->stderr());
	            	}        		
			$query = "TRUNCATE #__community_connection";
	            	$db->setQuery($query);
		            $db->query();	
				    if ($db->getErrorNum())
		            {
		                JError::raiseWarning(500, $db->stderr());
	            	}		            		
			$query = "INSERT INTO #__community_connection(`connect_from`,`connect_to`,`status`,`group`,`created`,`msg`) SELECT `connect_from`,`connect_to`,`status`,`group`,`created`,`msg` FROM #__community_connection_TEMP";
	            	$db->setQuery($query);
		            $db->query();
				    if ($db->getErrorNum())
		            {
		                JError::raiseWarning(500, $db->stderr());
	            	}		            
			$query = "DROP TABLE #__community_connection_TEMP";
	            	$db->setQuery($query);
		            $db->query();
				    if ($db->getErrorNum())
		            {
		                JError::raiseWarning(500, $db->stderr());
	            	}		            		            
		            return true;
		}        
        
        /**
         *get Friend Connection
         *
         *@param connect_from int owner's id
         *@param connect_to stranger's id
         *return db object
         */

        function getFriendConnection($connect_from, $connect_to)
        {

            $db = $this->_db;

            $query = "SELECT * FROM #__community_connection
		        WHERE (`connect_from` = ".$db->Quote($connect_from)." AND `connect_to` =".$db->Quote($connect_to).")
				OR ( `connect_from` = ".$db->Quote($connect_to)." AND `connect_to` =".$db->Quote($connect_from).")";

            $db->setQuery($query);
            if ($db->getErrorNum())
            {
                JError::raiseError(500, $db->stderr());

            }

            $result = $db->loadObjectList();

            return $result;
        }
                
	function _getAllUsers()
	{
		//$db = &JFactory::getDBO();

			$query="SELECT ". $this->_db->nameQuote( 'userid' ) . " FROM ". $this->_db->nameQuote( '#__community_users' );
				$this->_db->setQuery($query);
				$result = $this->_db->loadObjectList();

		return $result;		
	}
	
        /**
         * Count total pending request.
         **/
        function countPending($id)
        {
        	$db = $this->_db;
        	
        	$query = "SELECT count(*) "
            .' FROM #__community_connection as a, #__users as b'
            .' WHERE a.`connect_to`='.$db->Quote($id)
            .' AND a.`status`=0 '
            .' AND a.`connect_from`=b.`id` '
            .' ORDER BY a.`connection_id` DESC ';

            $db->setQuery($query);
            if ($db->getErrorNum())
            {
                JError::raiseError(500, $db->stderr());
            }
            
            return $db->loadResult();
        }
        	
        /**
         * Get all people what are waiting to get user's approval
         * @param	id	int		userid of the user responsible for approving it
         */
        function getPending($id)
        {
			if($id == 0)
			{
				// guest obviouly hasn't send any request
				return null;
			}
			
            $db = $this->_db;

            $wheres[] = 'block = 0';
			
            $total = $this->countPending($id);

			
			$query = 'SELECT b.*, a.connection_id, a.msg '
            .' FROM #__community_connection as a, #__users as b'
            .' WHERE a.`connect_from`='.$db->Quote($id)
            .' AND a.`status`=0 '
            .' AND a.`connect_from`=b.`id` '
            .' ORDER BY a.`connection_id` DESC ';

            $db->setQuery($query);
            if ($db->getErrorNum())
            {
                JError::raiseError(500, $db->stderr());
            }
            $result = $db->loadObjectList();
            return $result;
        }	

 /**
         * approve the requested friend connection
         * @param	id 	int		the connection request id
         * @return	true if everything is ok
         */
        function approveRequest($id)
        {
            $connection = array ();
            $db = $this->_db;
            //get connect_from and connect_to
            $query = "SELECT `connect_from`,`connect_to`"
            ." FROM #__community_connection "
            ." WHERE `connection_id` =".$db->Quote($id);

            $db->setQuery($query);
            $conn = $db->loadObject();

            if (! empty($conn))
            {
                $connect_from = $conn->connect_from;
                $connect_to = $conn->connect_to;

                $connection[] = $connect_from;
                $connection[] = $connect_to;

                //delete connection id
                $query = "DELETE FROM #__community_connection"
                ." WHERE `connection_id`=".$db->Quote($id);

                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum())
                {
                    JError::raiseError(500, $db->stderr());
                }


				$date	=& JFactory::getDate(); //get the time without any offset!
                //do double entry                
                //@todo escape code
                $query = "INSERT INTO #__community_connection SET"
                	. ' `connect_from`='.$db->Quote($connect_from)
                	. ', `connect_to`='.$db->Quote($connect_to)
                	. ', `status`=1'
                	. ', `created` = ' . $db->Quote($date->toMySQL());

                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum())
                {
                    JError::raiseError(500, $db->stderr());
                }


                //@todo escape code
                $query = "INSERT INTO #__community_connection SET"
                	. ' `connect_from`='.$db->Quote($connect_to)
                	. ', `connect_to`='.$db->Quote($connect_from)
                	. ', `status`=1'
                	. ', `created` = ' . $db->Quote($date->toMySQL());

                $db->setQuery($query);
                $db->query();
                if ($db->getErrorNum())
                {
                    JError::raiseError(500, $db->stderr());
                }

                return $connection;
            }
            else
            {
                // Return null is null
                return null;
            }
        }        
        
function ffApprove($backup)
		{
			//$model =& $this->getModel('Backup');
			$backup->backup('Backup before First Friend Approve');
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
							$pending = $this->getPending($friend);
							foreach ($pending AS $connect)
							{
								$this->approveRequest($connect->connection_id);
							}
						}
					}
				else
					{
							//parse through all id's
							$pending = $this->getPending($firstfriend);
							foreach ($pending AS $connect)
							{
								$this->approveRequest($connect->connection_id);
							}
					}
					$this->correctFriendCounts();
					return true;
						
		}			

}//class