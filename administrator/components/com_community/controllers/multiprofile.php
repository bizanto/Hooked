<?php
/**
 * @category	Core
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.controller' );

class CommunityControllerMultiProfile extends CommunityController
{
	function __construct()
	{
		parent::__construct();
		$this->registerTask( 'publish' , 'savePublish' );
		$this->registerTask( 'unpublish' , 'savePublish' );	
	}

	public function savePublish()
	{
		parent::savePublish( 'CTable' );
	}	

	function ajaxTogglePublish( $id , $type )
	{
		$user	=& JFactory::getUser();

		// @rule: Disallow guests.
		if ( $user->get('guest'))
		{
			JError::raiseError( 403, JText::_('CC ACCESS FORBIDDEN') );
			return;
		}

		$response	= new JAXResponse();

		// Load the JTable Object.
		$row	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
		$row->load( $id );
		$row->publish( $row->id , (int) !$row->published );
		$row->load( $id );
		
		$image	= $row->published ? 'publish_x.png' : 'tick.png';

		$view	=& $this->getView( 'multiprofile' , 'html' );

		$html	= $view->getPublish( $row , 'published' , 'multiprofile,ajaxTogglePublish' );
	   	
	   	$response->addAssign( $type . $id , 'innerHTML' , $html );
	   	
	   	return $response->sendResponse();
	}
	
	public function save()
	{
		jimport( 'joomla.filesystem.folder' );
		jimport( 'joomla.filesystem.file' );
		CFactory::load( 'helpers' , 'image' );
		
		$id			= JRequest::getInt( 'id' , 0 , 'POST' );
		$post		= JRequest::get( 'POST' );
		$fields		= JRequest::getVar( 'fields' , '' );
		$tmpParents	= JRequest::getVar( 'parents' , '' );
		$mainframe	=& JFactory::getApplication();
		$isNew	= $id == 0 ? true : false;
		
		$multiprofile	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
		$multiprofile->load( $id );
		$multiprofile->bind( $post );
		
		$date			=& JFactory::getDate();
		$isNew			= $multiprofile->id == 0;

		if( $isNew )
		{
			$multiprofile->created	= $date->toMySQL();
		}
		
		// Store watermarks for profile types.
		$watermark		= JRequest::getVar( 'watermark' , '' , 'FILES');
		
		// Do not allow image size to exceed maximum width and height
		if( isset($watermark['name']) && !empty($watermark['name']) )
		{
			list( $width , $height ) = getimagesize( $watermark[ 'tmp_name' ] );
			
			if( $width > 64 || $height > 64 )
			{
				$mainframe->redirect( 'index.php?option=com_community&view=multiprofile&layout=edit' , JText::_( 'CC WATERMARK IMAGE EXCEEDS SIZE' ) , 'error' );
				exit;
			}
		}
		$multiprofile->store();

		// If image file is specified, we need to store the thumbnail.
		if( isset($watermark['name']) && !empty($watermark['name']) )
		{
			
			$watermarkFile	= 'watermark_' . $multiprofile->id . CImageHelper::getExtension( $watermark['type'] );
			JFile::copy( $watermark[ 'tmp_name' ] , JPATH_ROOT . DS . COMMUNITY_WATERMARKS_PATH . DS . $watermarkFile );
			
			$multiprofile->watermark	= JString::str_ireplace( DS , '/' , COMMUNITY_WATERMARKS_PATH ) .  '/' . $watermarkFile;
			$multiprofile->store();
		}
		
		// @rule: Create the watermarks folder if doesn't exists.
		if( !JFolder::exists( COMMUNITY_WATERMARKS_PATH ) )
		{
			if(!JFolder::create( COMMUNITY_WATERMARKS_PATH ) )
			{			
				$mainframe->enqueueMessage( JText::_('CC UNABLE TO CREATE WATERMARKS FOLDER') );
			}
		}
		
		// @rule: Create original folder within watermarks to store original user photos.
		if( !JFolder::exists( COMMUNITY_WATERMARKS_PATH . DS . 'original' ) )
		{
			if(!JFolder::create( COMMUNITY_WATERMARKS_PATH . DS . 'original' ) )
			{			
				$mainframe->enqueueMessage( JText::_('CC UNABLE TO CREATE WATERMARKS FOLDER') );
			}
		}
		
		// Create default watermarks for avatar and thumbnails.
		if( isset($watermark['name']) && !empty( $watermark['name'] ) || !empty( $multiprofile->watermark) )
		{
			CFactory::load( 'helpers' , 'image' );
			
			// Generate filename
			$fileName		= CImageHelper::getHashName( $multiprofile->id . time() ). '.jpg';
			$thumbFileName	= 'thumb_' . $fileName;
			
			// Paths where the thumbnail and avatar should be saved.
			$thumbPath	= JPATH_ROOT . DS . COMMUNITY_WATERMARKS_PATH . DS . $thumbFileName;
			$avatarPath	= JPATH_ROOT . DS . COMMUNITY_WATERMARKS_PATH . DS . $fileName;

			// Copy existing default thumbnails into the path first.
			JFile::copy( JPATH_ROOT . DS . DEFAULT_USER_THUMB , $thumbPath );
			JFile::copy( JPATH_ROOT . DS . DEFAULT_USER_AVATAR , $avatarPath );
			
			$watermarkPath		= $watermark[ 'tmp_name'];

			list( $watermarkWidth , $watermarkHeight )	= getimagesize( $watermarkPath );
			
			$oldDefaultAvatar	= $multiprofile->avatar;
			$oldDefaultThumb	= $multiprofile->thumb;

			// Avatar Properties
			$avatarInfo		= getimagesize( $avatarPath );
			$avatarWidth	= $avatarInfo[ 0 ];
			$avatarHeight	= $avatarInfo[ 1 ];
			$avatarMime		= $avatarInfo[ 'mime' ];
			$avatarPosition	= $this->_getPositions( $multiprofile->watermark_location , $avatarWidth , $avatarHeight , $watermarkWidth , $watermarkHeight );
			CImageHelper::addWatermark( $avatarPath , $avatarPath , 'image/jpg' , $watermarkPath , $avatarPosition->x , $avatarPosition->y );
			$multiprofile->avatar	= JString::str_ireplace( DS , '/' , COMMUNITY_WATERMARKS_PATH ) . '/' . $fileName;
			
			// Thumbnail properties.
			$thumbInfo		= getimagesize( $thumbPath );
			$thumbWidth		= $thumbInfo[ 0 ];
			$thumbHeight	= $thumbInfo[ 1 ];
			$thumbMime		= $thumbInfo[ 'mime' ];
			$thumbPosition	= $this->_getPositions( $multiprofile->watermark_location , $thumbWidth , $thumbHeight , $watermarkWidth , $watermarkHeight );
			CImageHelper::addWatermark( $thumbPath , $thumbPath , $thumbMime , $watermarkPath , $thumbPosition->x , $thumbPosition->y );
			$multiprofile->thumb	= JString::str_ireplace( DS , '/' , COMMUNITY_WATERMARKS_PATH ) . '/' . $thumbFileName;
			
			
			// Since the default thumbnail is used by current users, we need to update their existing values.
			$multiprofile->updateUserDefaultImage( 'avatar' , $oldDefaultAvatar );
			$multiprofile->updateUserDefaultImage( 'thumb' , $oldDefaultThumb );
			
			$multiprofile->watermark_hash	= md5( $watermark['name'] . time() );
			$multiprofile->store();
		}
		
		// Since it would be very tedious to check if previous fields were enabled or disabled.
		// We delete all existing mapping and remap it again to ensure data integrity.
		if( !$isNew && !empty($fields) )
		{
			$multiprofile->deleteChilds();
		}
		
		if( !empty( $fields ) )
		{
			$parents	= array();
			
			// We need to unique the parents first.
			foreach($fields as $id )
			{
				$customProfile		=& JTable::getInstance( 'Profiles' , 'CommunityTable' );
				$customProfile->load( $id );
				
				// Need to only
				$parent				= $customProfile->getCurrentParentId();

				if( in_array( $parent , $tmpParents ) )
				{
					$parents[]	= $parent;
				}
			}
			$parents	= array_unique( $parents );
			
			$fields		= array_merge( $fields, $parents );

			foreach( $fields as $id )
			{
				
				$field				=& JTable::getInstance( 'MultiProfileFields' , 'CTable' );
				$field->parent		= $multiprofile->id;
				$field->field_id	= $id;
				
				$field->store();
			}
		}
		
		$message	= JText::_( 'CC MULTIPROFILE UPDATED SUCCESSFULLY' );
		if( $isNew )
		{
			$message	= JText::_('CC MULTIPROFILE CREATED SUCCESSFULLY');
		}
		
		$mainframe->redirect( 'index.php?option=com_community&view=multiprofile' , $message );
	}
	
	/**
	 * Retrieve the proper x and y position depending on the user's choice of the watermark position.
	 **/
	private function _getPositions( $location , $imageWidth , $imageHeight , $watermarkWidth , $watermarkHeight )
	{
		$position	= new stdClass();
		
		// @rule: Get the appropriate X/Y position for the avatar
		switch( $location )
		{
			case 'top':
				$position->x	= ($imageWidth / 2) - ( $watermarkWidth / 2 );
				$position->y	= 0;
				break;
			case 'bottom':
				$position->x	= ($imageWidth / 2) - ( $watermarkWidth / 2 );
				$position->y	= $imageHeight - $watermarkHeight;
				break;
			case 'left':
				$position->x	= 0;
				$position->y	= ( $imageHeight / 2 ) - ($watermarkHeight / 2);
				break;
			case 'right':
				$position->x 	= $imageWidth - $watermarkWidth;
				$position->y	= ( $imageHeight / 2 ) - ($watermarkHeight / 2);
				break;
		}
		return $position;
	}
	
	public function display()
	{
		$viewName	= JRequest::getCmd( 'view' , 'community' );

		// Set the default layout and view name
		$layout		= JRequest::getCmd( 'layout' , 'default' );

		// Get the document object
		$document	=& JFactory::getDocument();

		// Get the view type
		$viewType	= $document->getType();
		
		$view		= $this->getView( $viewName , $viewType );
		$profile	= $this->getModel( 'Profiles' );
		$view->setModel( $profile , false );

		parent::display();
	}
	
	public function add()
	{
		$mainframe	=& JFactory::getApplication();
		$mainframe->redirect( 'index.php?option=com_community&view=multiprofile&layout=edit' );
	}
	
	/**
	 * Responsible for deleting single or multiple profile types.
	 **/	 	
	function delete()
	{
		$mainframe	=& JFactory::getApplication();
		$data		= JRequest::getVar( 'cid' , '' , 'post' );
		$error		= array();
		$profile	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
		
		if( !is_array( $data ) )
		{
			$data[]	= $data;
		}
		
		if( empty($data) )
		{
			JError::raiseError( '500' , JText::_('CC INVALID ID') );
		}
		
		foreach($data as $id)
		{
			$profile->load( $id );
			
			if( $profile->hasUsers() )
			{
				$mainframe->redirect( 'index.php?option=com_community&view=multiprofile' , JText::sprintf('CC UNABLE TO DELETE MULTIPROFILE' , $profile->name ) , 'error' );
			}
			else
			{
				if( !$profile->delete() )
				{
					$error[]	= true;
				}
			}
		}
		

		if( in_array( $error , true ) )
		{
			$mainframe->redirect( 'index.php?option=com_community&view=multiprofile' , JText::_('CC ERROR REMOVING MULTIPROFILE') , 'error' );
		}
		else
		{
			$mainframe->redirect( 'index.php?option=com_community&view=multiprofile' , JText::_('CC MULTIPROFILE DELETED') );
		}
	}
}