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

class ApiResourceReview extends ApiResource {
	
	public function get() {
		$id = JRequest::getInt('id', 0);
		if (!$id)
		{
			$this->plugin->setResponse(null);
			return;
		}	
		
		$db = JFactory::getDBO();
		$query = "SELECT c.id, c.name, c.username, c.email, c.created, c.title, c.comments "
				."FROM #__jreviews_comments AS c "
				."WHERE c.published = 1 "
				."AND c.id = ".$id
				;
		
		$db->setQuery($query);
		$listing = $db->loadObject();
		$this->plugin->setResponse($listing);
	}

	public function post() {
		$post = JRequest::get('post');
		$this->plugin->setResponse('here is a post request');
	}

	public function put() {
		$this->plugin->setResponse('here is a put request');
	}

	public function delete() {
		$this->plugin->setResponse('here is a delete request');
	}

}