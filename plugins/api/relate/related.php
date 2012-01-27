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

class ApiResourceRelated extends ApiResource {
		
	public function get() {
		$id = JRequest::getInt('id', 0);
		if (!$id)
		{
			$this->plugin->setResponse(null);
			return;
		}
		
		$related = array();
		
		$cat = JRequest::getVar('catid', '');
		if (is_numeric($cat)) {
			$related = RelateAPIHelper::getRelated($id, $cat);
		}
		else if ($cat == 'photos') {
			$related = RelateAPIHelper::getPhotos($id);
		}
		else if ($cat == 'videos') {
			$related = RelateAPIHelper::getVideos($id);
		}
		else {
			$related = RelateAPIHelper::getRelated($id);
		}		
		
		$this->plugin->setResponse($related);
	}
	
	public function post() {
		$post = JRequest::get('post');
		
		if (!isset($post['id'])) 
		{
			$this->plugin->setResponse(null);
			return;
		}
		
	}
}
