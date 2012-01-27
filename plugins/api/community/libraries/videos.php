<?php

class APIVideos {
	public $plugin = null;
	public $errors = array();
	
	public function __construct(APIPlugin $plugin) {
		$this->plugin = $plugin;
	}
	
	public function setError($msg) {
		$this->errors[] = $msg;
	}
	
	public function getError() {
		if (!empty($this->errors))
		{
			$last = count($this->errors) - 1;
			return $this->errors[$last];
		}
		
		return null;
	}


	private function _showUploadError($hasError, $msg) {
		if (!$msg)
		{
			$msg = $this->getError();
		}
		
		echo $msg;
	}




	public function upload()
	{
		
		$document		= JFactory::getDocument();
		
		$mainframe		= JFactory::getApplication();
		$my				= $this->plugin->get('user');
		$creatorType	= JRequest::getVar( 'creatortype' , VIDEO_USER_TYPE );
		$groupid 		= ($creatorType==VIDEO_GROUP_TYPE)? JRequest::getInt( 'groupid' , 0 ) : 0;
		$config			= CFactory::getConfig();
		
		CFactory::load('helpers', 'videos');
		CFactory::load('libraries', 'videos');
		$redirect		= CVideosHelper::getVideoReturnUrlFromRequest();

		// Process according to video creator type
		if(!empty($groupid))
		{
			CFactory::load( 'helpers' , 'group' );
			$allowManageVideos	= CGroupHelper::allowManageVideo($groupid);
			$creatorType		= VIDEO_GROUP_TYPE;
			$videoLimit			= $config->get( 'groupvideouploadlimit' );
			CError::assert($allowManageVideos, '', '!empty', __FILE__ , __LINE__ );
		} else {
			$creatorType		= VIDEO_USER_TYPE;
			$videoLimit			= $config->get('videouploadlimit');
		}
		
		// Check is video upload is permitted
		CFactory::load('helpers' , 'limits' );
		if(CLimitsHelper::exceededVideoUpload($my->id, $creatorType))
		{
			$message		= JText::sprintf('CC VIDEOS CREATION REACH LIMIT', $videoLimit);
			$this->setError($message);
			return false;
		}
		if (!$config->get('enablevideos'))
		{
			$this->setError(JText::_('CC VIDEO DISABLED', 'notice'));
			return false;
		}
		if (!$config->get('enablevideosupload'))
		{
			$this->setError(JText::_('CC VIDEO UPLOAD DISABLED', 'notice'));
			return false;
		}
		
		// Check if the video file is valid
		$files		= JRequest::get('files');
		
		$videoFile	= !empty($files['video']) ? $files['video'] : array();
		if (empty($files) || (empty($videoFile['name']) && $videoFile['size'] < 1))
		{
			$this->setError(JText::_('CC VIDEO UPLOAD ERROR', 'error'));
			return false;
		}
		
		// Check file type.
		$fileType	= $videoFile['type'];
		
		// Override from iphone
		$fileType = 'video/quicktime';
		
		$allowable	= CVideosHelper::getValidMIMEType();
		
		if (!in_array($fileType, $allowable))
		{
			$this->setError(JText::sprintf('CC VIDEO FILE TYPE NOT SUPPORTED', $fileType));
			return false;
		}
		
		
		
		// Check if the video file exceeds file size limit
		$uploadLimit	= $config->get('maxvideouploadsize') * 1024 * 1024;
		$videoFileSize	= sprintf("%u", filesize($videoFile['tmp_name']));
		if( ($uploadLimit>0) && ($videoFileSize>$uploadLimit) )
		{
			$this->setError(JText::sprintf('CC VIDEO FILE SIZE EXCEEDED', $uploadLimit));
			return false;
		}
		
		// Passed all checking, attempt to save the video file
		CFactory::load('helpers', 'file');
		$folderPath		= CVideoLibrary::getPath($my->id, 'original');
		$randomFileName	= CFileHelper::getRandomFilename( $folderPath , $videoFile['name'] , '' );
		$destination	= JPATH::clean($folderPath . DS . $randomFileName);
		
		if( !CFileHelper::upload( $videoFile , $destination ) )
		{
			$this->setError(JText::_('CC VIDEO UPLOAD FAILED', 'error'));
			return false;
		}
		
		$config	= CFactory::getConfig();
		$videofolder = $config->get('videofolder');
		
		CFactory::load( 'models' , 'videos' );
		$video	= JTable::getInstance( 'Video' , 'CTable' );
		$video->set('path',			$videofolder. '/originalvideos/' . $my->id . '/' . $randomFileName);
		$video->set('title',		JRequest::getVar('title'));
		$video->set('description',	JRequest::getVar('description'));
		$video->set('category_id',	JRequest::getInt('category', 0, 'post'));
		$video->set('permissions',	JRequest::getInt('privacy', 0, 'post'));
		$video->set('creator',		$my->id);
		$video->set('creator_type',	$creatorType);
		$video->set('groupid',		$groupid);
		$video->set('filesize',		$videoFileSize);
		
		if (!$video->store())
		{
			$this->setError(JText::_('CC VIDEO SAVE ERROR', 'error'));
			return false;
		}

		// Trigger for onVideoCreate
		$this->_triggerEvent( 'onVideoCreate' , $video );

		// Video saved, redirect
		return $video;
	}
	
	private function _triggerEvent( $event , $args )
	{
		// Trigger for onVideoCreate
		CFactory::load( 'libraries' , 'apps' );
		$apps   =& CAppPlugins::getInstance();
		$apps->loadApplications();
		$params		= array();
		$params[]	= & $args;
		$apps->triggerEvent( $event , $params );
	}
	
}