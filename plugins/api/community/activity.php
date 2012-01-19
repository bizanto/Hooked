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

class ApiResourceActivity extends ApiResource {
	
	public function get() {
		$user = $this->plugin->get('user');
		$model = CFactory::getModel('Activities');
			
		$activities = $model->getActivities($user->get('id'), '', '', 20, false);
	
		$this->plugin->setResponse($activities);
	}

}