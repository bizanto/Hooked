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

class ApiResourceAlbum extends ApiResource {
		
	public function get() {
		$id = JRequest::getInt('id', 0);
		if (!$id)
		{
			$this->plugin->setResponse(null);
			return;
		}	
	
		$user = $this->plugin->get('user');
		$model = CFactory::getModel('Photos');
			
		$album = $model->getAlbum($id);
		// Set to standard class for encoding
		$this->plugin->setResponse($album);
	}
	
	public function post() {
		
		JLoader::register('APIPhotos', dirname(__FILE__).'/libraries/photos.php');
		JLoader::register('APIVideos', dirname(__FILE__).'/libraries/videos.php');
		
		$files = JRequest::get('files');
		$request = JRequest::get('request');
		
		$this->plugin->dump_obj($files);
		
		$album_id = $this->getAlbum($request['type'], $this->plugin->get('user')->id);
		JRequest::setVar('albumid', $album_id);
		
		if ($request['media'] == 'photo')
		{
			$handler = new APIPhotos($this->plugin);
			$response = $handler->upload();
			$media_id = $response[0]['image_id'];
			$r = $response[0];
			$output = new stdClass();
			$output->image_id = $r['image_id'];
			$output->image = $r['image'];
			$output->thumbnail = $r['thumbnail'];
			$output->storage = $r['storage'];
		} else {
			$handler = new APIVideos($this->plugin);
			$response = $handler->upload();
			$media_id = $response->id;
			$output = new stdClass();
			$output->video_id = $response->id;
			$output->path = $response->path;
			$output->thumb = $response->thumb;
			$output->storage = $response->storage;
		}

		$this->plugin->dump_obj($output);

		if ($error = $handler->getError())
		{
			$this->plugin->setResponse($error);
			return;
		}
		
		$this->addRelation($request['media'], $media_id, $request['listing_id'], $this->plugin->get('user')->id);
		
		$this->plugin->setResponse($output);
	}

	protected function getAlbum($type, $user_id)
	{
		$map = array('report' => 'Fangstrapporter', 'spot' => 'Fiskeplasser');
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT id FROM #__community_photos_albums WHERE creator = $user_id AND name = '".$map[$type]."'");
		$album = $db->loadResult();
		
		return $album;
	}
	
	protected function addRelation($type, $media_id, $listing_id, $creator)
	{
	
		if ($type == 'photo')
		{
			$table = '#__relate_photos';
			$field = 'photo_id';
		} else {
			$table = '#__relate_videos';
			$field = 'video_id';
		}
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT COUNT(*) FROM $table WHERE $field = $media_id AND listing_id = $listing_id");
		$exists = $db->loadResult();
		$this->plugin->dump_obj($db);
		if (!$exists)
		{
			$db->setQuery("INSERT INTO $table (listing_id, $field, creator) VALUES ($listing_id, $media_id, $creator)");
			$db->query();
			$this->plugin->dump_obj($db);
		}
	}
	
}