<?php

class APIPhotos {
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
		$my = $this->plugin->get('user');
		$config	= CFactory::getConfig();
		$returns = array();
		
		// Load up required models and properties
		CFactory::load('controllers', 'photos');
		CFactory::load('libraries', 'photos');
		CFactory::load( 'models' , 'photos' );
		CFactory::load('helpers', 'image');

		$photos		= JRequest::get('Files');
		$albumId	= JRequest::getVar( 'albumid' , '' , 'REQUEST' );
		$album		=& JTable::getInstance( 'Album' , 'CTable' );
		$album->load( $albumId );
		
		$handler	= $this->_getHandler( $album );
		
		foreach( $photos as $imageFile )
		{
			if( !$this->_validImage( $imageFile ) )
			{
				$this->_showUploadError( true , $this->getError() );
				return;
			}
			
			if( $this->_imageLimitExceeded( filesize( $imageFile['tmp_name'] ) ) )
			{
				$this->_showUploadError( true , JText::_('CC IMAGE FILE SIZE EXCEEDED') );
				return;
			}

			// We need to read the filetype as uploaded always return application/octet-stream
			// regardless od the actual file type
			$info			= getimagesize( $imageFile['tmp_name'] );				
			$isDefaultPhoto	= JRequest::getVar( 'defaultphoto' , false , 'REQUEST' );

			if( $album->id == 0 || ( ($my->id != $album->creator ) && $album->type != PHOTOS_GROUP_TYPE ) )
			{
				$this->_showUploadError( true , JText::_('CC INVALID ALBUM') );
				return;
			}
		
			if( !$album->hasAccess( $my->id , 'upload') )
			{
				$this->_showUploadError( true , JText::_('CC INVALID ALBUM') );
				return;
			}

			// Hash the image file name so that it gets as unique possible
			$fileName		= JUtility::getHash( $imageFile['tmp_name'] . time() );
			$hashFilename	= JString::substr( $fileName , 0 , 24 );
			$imgType		= image_type_to_mime_type($info[2]);
			
			// Load the tables
			$photoTable		=& JTable::getInstance( 'Photo' , 'CTable' );

			// @todo: configurable paths?
			$storage		= JPATH_ROOT . DS . $config->getString('photofolder');
			$albumPath		= (empty($album->path)) ? '' : $album->id . DS;

			// Test if the photos path really exists.
			jimport('joomla.filesystem.file');
			jimport('joomla.filesystem.folder');
			CFactory::load( 'helpers' , 'limits' );
			
			$originalPath	= $handler->getOriginalPath( $storage , $albumPath , $album->id );
			
			CFactory::load( 'helpers' , 'owner' );
			// @rule: Just in case user tries to exploit the system, we should prevent this from even happening.
			if( $handler->isExceedUploadLimit() && !COwnerHelper::isCommunityAdmin() )
			{
				$config			= CFactory::getConfig();
				$photoLimit		= $config->get( 'groupphotouploadlimit' );
				
				echo JText::sprintf('CC GROUP PHOTO UPLOAD LIMIT REACHED' , $photoLimit );
				return;
			}
					
			if( !JFolder::exists( $originalPath ) )
			{
				if( ! JFolder::create( $originalPath , (int) octdec( $config->get('folderpermissionsphoto') ) ) )
				{
					$this->_showUploadError( true , JText::_('CC ERROR CREATING USERS PHOTO FOLDER') );
					return;
				}
			}

			$locationPath	= $handler->getLocationPath( $storage , $albumPath , $album->id );

			if( !JFolder::exists( $locationPath ) )
			{
				if( ! JFolder::create( $locationPath, (int) octdec( $config->get('folderpermissionsphoto') ) ) )
				{
					$this->_showUploadError( true , JText::_('CC ERROR CREATING USERS PHOTO FOLDER') );
					return;
				}
			}
			
			$thumbPath	= $handler->getThumbPath( $storage, $album->id );
			$thumbPath	= $thumbPath . DS . $albumPath . 'thumb_' . $hashFilename . CImageHelper::getExtension( $imageFile['type'] );
			CPhotos::generateThumbnail($imageFile['tmp_name'] , $thumbPath , $imgType);
			
			// Original photo need to be kept to make sure that, the gallery works
			$useAlbumId		= (empty($album->path)) ? 0 : $album->id;
			$originalFile	= $originalPath . $hashFilename . CImageHelper::getExtension($imgType);
			
			$this->_storeOriginal($imageFile['tmp_name'] , $originalFile , $useAlbumId);
			$photoTable->original		= JString::str_ireplace( JPATH_ROOT . DS , '' , $originalFile );
	
			// Set photos properties
			$photoTable->albumid		= $albumId;
			$photoTable->caption		= $imageFile['name'];
			$photoTable->creator		= $my->id;
			$photoTable->created		= gmdate('Y-m-d H:i:s');
			
			// Remove the filename extension from the caption
			if(JString::strlen($photoTable->caption) > 4)
			{
				$photoTable->caption = JString::substr($photoTable->caption, 0 , JString::strlen($photoTable->caption) - 4);
			}
			
			// @todo: configurable options?
			// Permission should follow album permission
			$photoTable->published		= '1';
			$photoTable->permissions	= $album->permissions;
	
			// Set the relative path.
			// @todo: configurable path?
			$storedPath				= $handler->getStoredPath( $storage , $albumId );
			$storedPath				= $storedPath . DS . $albumPath . $hashFilename . CImageHelper::getExtension($imageFile['type']);
			
			$photoTable->image		= JString::str_ireplace( JPATH_ROOT . DS , '' , $storedPath ); 
			$photoTable->thumbnail	= JString::str_ireplace( JPATH_ROOT . DS , '' , $thumbPath );
			
			//photo filesize, use sprintf to prevent return of unexpected results for large file.
			$photoTable->filesize = sprintf("%u", filesize($originalPath));
			
			// @rule: Set the proper ordering for the next photo upload.
			$photoTable->setOrdering();
			
			// Store the object
			$photoTable->store();
			
			// We need to see if we need to rotate this image, from EXIF orientation data
			// Only for jpeg image.
			if( $config->get('photos_auto_rotate') && $imgType== 'image/jpeg'){
				// Read orientation data from original file
				$orientation = CImageHelper::getOrientation($imageFile['tmp_name']);
				
				//echo $orientation; exit;
				
				// A newly uplaoded image might not be resized yet, do it now
				$displayWidth	= $config->getInt('photodisplaysize');
				JRequest::setVar('imgid', $photoTable->id, 'GET');
				JRequest::setVar('maxW', $displayWidth, 'GET');
				JRequest::setVar('maxH', $displayWidth, 'GET');
				
				$this->showimage(false);
				
				// Rotata resized files ince it is smaller
				switch($orientation)
			    {
			        case 1: // nothing
			        	break;
			
			        case 2: // horizontal flip
			            // $image->flipImage($public,1);
			        	break;
			                               
			        case 3: // 180 rotate left
			            //  $image->rotateImage($public,180);
			            CImageHelper::rotate($storedPath, $storedPath, 180);
			            CImageHelper::rotate($thumbPath, $thumbPath, 180);
			        	break;
			                   
			        case 4: // vertical flip
			            //  $image->flipImage($public,2);
			        	break;
			               
			        case 5: // vertical flip + 90 rotate right
			            //$image->flipImage($public, 2);
			            //$image->rotateImage($public, -90);
			        	break;
			               
			        case 6: // 90 rotate right
			            // $image->rotateImage($public, -90);
			            CImageHelper::rotate($storedPath, $storedPath, -90);
			            CImageHelper::rotate($thumbPath, $thumbPath, -90);
			        	break;
			               
			        case 7: // horizontal flip + 90 rotate right
// 			            $image->flipImage($public,1);   
// 			            $image->rotateImage($public, -90);
			        	break;
			               
			        case 8:    // 90 rotate left
// 			            $image->rotateImage($public, 90);
						CImageHelper::rotate($storedPath, $storedPath, 90);
						CImageHelper::rotate($thumbPath, $thumbPath, 90);
			        	break;
			    }
	 
			}

			// Trigger for onPhotoCreate
			CFactory::load( 'libraries' , 'apps' );
			$apps   =& CAppPlugins::getInstance();
			$apps->loadApplications();
			$params		= array();
			$params[]	= & $photoTable;
			$apps->triggerEvent( 'onPhotoCreate' , $params );

			// Set image as default if necessary
			// Load photo album table
			if( $isDefaultPhoto )
			{
				// Set the photo id
				$album->photoid	= $photoTable->id;
 				$album->store();
			}

			// @rule: Set first photo as default album cover if enabled
			if( !$isDefaultPhoto && $config->get('autoalbumcover') )
			{
				$photosModel	= CFactory::getModel( 'Photos' );
				$totalPhotos	= $photosModel->getTotalPhotos( $album->id );

				if( $totalPhotos <= 1 )
				{
					$album->photoid	= $photoTable->id;
					$album->store();
				}
			}
			
			//if( $handler->isPublic( $album->id ) )
			{
				$act = new stdClass();
				$act->cmd 		= 'photo.upload';
				$act->actor   	= $my->id;
				$act->access	= $my->getParam('privacyPhotoView');
				$act->target  	= 0;
				$act->title	  	= JText::sprintf( $handler->getUploadActivityTitle() , '{photo_url}', $album->name );
				$act->content	= '<img src="' . rtrim( JURI::root() , '/' ) . '/' . $photoTable->thumbnail . '" style=\"border: 1px solid #eee;margin-right: 3px;" />';
				$act->app		= 'photos';
				$act->cid		= $albumId;
	
		 		$params = new JParameter('');
		 		$params->set('multiUrl'	, $handler->getAlbumURI( $albumId , false ) );
		 		$params->set('photoid'	, $photoTable->id);
				$params->set('action'	, 'upload' );
				$params->set('photo_url', $handler->getPhotoURI( $albumId , $photoTable->id , false ) );
						
				// Add activity logging
				CFactory::load ( 'libraries', 'activities' );
				CActivityStream::add( $act , $params->toString() );
			}

			//add user points
			CFactory::load( 'libraries' , 'userpoints' );		
			CUserPoints::assignPoint('photo.upload');

			// Photo upload was successfull, display a proper message
			//$this->_showUploadError( false , JText::sprintf('CC PHOTO UPLOADED SUCCESSFULLY', $photoTable->caption ) , $photoTable->getThumbURI(), $albumId );
			$returns[] = array(
				'album_id' => $albumId, 
				'image_id' => $photoTable->id,
				'caption'  => $photoTable->caption,
				'created'  => $photoTable->created,
				'storage'  => $photoTable->storage,
				'thumbnail' => $photoTable->getThumbURI(),
				'image'    => $photoTable->getImageURI()
			);
			
		}
			return $returns;
		exit;
	}

	/**
	 * Return photos handlers
	 */	 	
	private function _getHandler( CTableAlbum $album )
	{
		$handler = null;
		
		// During AJAX calls, we might not be able to determine the groupid
		$groupId	= JRequest::getInt( 'groupid' , $album->groupid , 'REQUEST' );
		$type		= PHOTOS_USER_TYPE;

		if(!empty($groupId) )
		{
			// group photo
			$handler = new CommunityControllerPhotoGroupHandler( $this );
		}
		else
		{
			// user photo
			$handler = new CommunityControllerPhotoUserHandler( $this );
		}
		
		return $handler;
	}

	private function _imageLimitExceeded( $size )
	{
		$config			= CFactory::getConfig();
		$uploadLimit	= (double) $config->get('maxuploadsize');

		if( $uploadLimit == 0 )
		{
			return false;
		}
		
		$uploadLimit	= ( $uploadLimit * 1024 * 1024 );
		
		return $size > $uploadLimit;
	}
	
	private function _validImage( $image )
	{
		CFactory::load( 'helpers' , 'image' );
		$config		= CFactory::getConfig();
		
		if($image['error'] > 0 && $image['error'] !== 'UPLOAD_ERR_OK')
		{
			$this->setError('Upload Error '.$image['error']);
			return false;
		}
		
		if( empty($image['tmp_name'] ) )
		{
			$this->setError('Missing upload filename');
			return false;
		}
		
		// This is only applicable for html uploader because flash uploader uploads all 'files' as application/octet-stream
		//if( !$config->get('flashuploader') && !CImageHelper::isValidType( $image['type'] ) )
		//{
		//	$this->setError(JText::_('CC IMAGE FILE NOT SUPPORTED'));
		//	return false;
		//}
		
		if( !CImageHelper::isValid( $image['tmp_name'] ) )
		{
			$this->setError(JText::_('CC IMAGE FILE NOT SUPPORTED'));
			return false;
		}
		
		return true;
	}

	private function _storeOriginal($tmpPath, $destPath, $albumId = 0)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.utilities.utility');
		
		// First we try to get the user object.
		$my			= $this->plugin->get('user');
		
		
		$config = CFactory::getConfig(); 
		 
		// @todo: We assume now that the config is using the relative path to the
		// default images folder in Joomla.
		// @todo:  this folder creation should really be in its own function
		$albumPath			= ($albumId == 0) ? '' : DS . $albumId;
		$originalPathFolder	= JPATH_ROOT . DS . $config->getString('photofolder') . DS . JPath::clean( $config->get('originalphotopath') );
		$originalPathFolder	= $originalPathFolder . DS . $my->id . $albumPath;
		
		if( !JFile::exists( $originalPathFolder ) )
		{
			JFolder::create( $originalPathFolder, (int) octdec( $config->get('folderpermissionsphoto') ) );
		}

		if(!JFile::copy( $tmpPath, $destPath ) )
		{
			JError::raiseWarning(21, JText::sprintf('CC ERROR MOVING UPLOADED FILE' , $destPath));
		}
	}

	public function showimage($showPhoto = true)
	{
		jimport('joomla.filesystem.file');
		$imgid 		= JRequest::getVar('imgid', '', 'GET');
		$maxWidth 	= JRequest::getVar('maxW', '', 'GET');
		$maxHeight	= JRequest::getVar('maxH', '', 'GET');
		
		// round up the w/h to the nearest 10
		$maxWidth	= round($maxWidth, -1);
		$maxHeight	= round($maxHeight, -1); 
		
		$photoModel		= CFactory::getModel('photos');
		$photo			=& JTable::getInstance( 'Photo' , 'CTable' );
		$photo->loadFromImgPath( $imgid );

		CFactory::load('helpers', 'image');
		
		$photoPath		= JPATH_ROOT . DS .$photo->image;
		$config			= CFactory::getConfig();

		if(!JFile::exists( $photoPath ))
		{			
			$displayWidth	= $config->getInt('photodisplaysize');
			$info			= getimagesize( JPATH_ROOT . DS . $photo->original );
    		$imgType		= image_type_to_mime_type($info[2]);
    		$displayWidth 	= ($info[0] < $displayWidth) ? $info[0] : $displayWidth;
    		
			CImageHelper::resizeProportional( JPATH_ROOT . DS . $photo->original, $photoPath , $imgType, $displayWidth );
			
			if( $config->get( 'deleteoriginalphotos') )
			{
				$originalPath	= JPATH_ROOT . DS . $photo->original;
				if( JFile::exists( $originalPath ) )
				{
					JFile::delete( $originalPath );
				}
			}
		}
		
		// Show photo if required
		if($showPhoto){
			$info	= getimagesize( JPATH_ROOT . DS .$photo->image );
	
			// @rule: Clean whitespaces as this might cause errors when header is used.
			$ob_active = ob_get_length () !== FALSE;
	
			if($ob_active)
			{
				while (@ ob_end_clean());
					if(function_exists('ob_clean'))
					{
						@ob_clean();
					}
			}
				
			header('Content-type: '.$info['mime']);
			echo JFile::read( $photoPath );
			exit;
		}
	}


}