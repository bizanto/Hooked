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

class ApiResourceStatus extends ApiResource {
	
	public function get() {
		$user = $this->plugin->get('user');
			
		$profile = CFactory::getUser($user->get('id'));
		
		$this->plugin->setResponse($profile->getStatus());
	}

	public function post() {
		$status = JRequest::getVar('status', '', 'post', 'string');
		if (!$status)
		{
			$this->plugin->setResponse(null);
		}
		
		$user = $this->plugin->get('user');
			
		$profile = CFactory::getUser($user->get('id'));
		$profile->setStatus($status);
		$this->plugin->setResponse($profile->getStatus());
	}

}