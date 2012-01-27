<?php
/**
 * @package	API
 * @version 1.5
 * @author 	Brian Edgerton
 * @link 	http://www.edgewebworks.com
 * @copyright Copyright (C) 2011 Edge Web Works, LLC. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class ApiResourceAuthenticate extends ApiResource {
	
	public function post() {
		$username = JRequest::getVar('username', '', 'post');
		$password = JRequest::getVar('password', '', 'post');
		if (!$username || !$password)
		{
			$error = new JException('Credentials Not Found');
			$this->plugin->setResponse($error);
			return;
		}	
		
		$db = JFactory::getDBO();
		$query = "SELECT id, password FROM #__users WHERE LOWER(username) = LOWER(".$db->Quote($username).")";
		$db->setQuery($query);
		$result = $db->loadObject();
		
		if (!$result)
		{
			// Login failed
			$error = new JException('Incorrect username or password.');
			$this->plugin->setResponse($error);
			return;
		}
		
		jimport('joomla.user.helper');
		
		$parts	= explode( ':', $result->password );
		$crypt	= $parts[0];
		$salt	= @$parts[1];
		$testcrypt = JUserHelper::getCryptedPassword($password, $salt);
		if ($crypt == $testcrypt)
		{
			// Login success, return API Key
			$query = "SELECT hash FROM #__api_keys WHERE user_id = ".$db->Quote($result->id);
			$db->setQuery($query);
			$key = $db->loadResult();
			if ($key)
			{
				// Key found
				$this->plugin->setResponse($key);
				return;
			}
			else
			{
				// No key found
				$error = new JException('API Key Not Found');
				$this->plugin->setResponse($error);
				return;
			}
			
		}
		else
		{
			// Login failed
			$error = new JException('Incorrect username or password.');
			$this->plugin->setResponse($error);
			return;
		}
	}

}