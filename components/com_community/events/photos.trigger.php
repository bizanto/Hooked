<?php
/**
 * @category	Events
 * @package		JomSocial
 * @copyright (C) 2010 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

class CPhotosTrigger
{
	public function onAfterAlbumDelete($album)
	{
		
	}
	
	public function onAfterPhotoDelete( $photo )
	{
		// @todo: delete 1 particular activity since we cannot identify any one activity (activity only store album id) 
		// just delete 1 activity with a matching album id
		$model	= CFactory::getModel('activities');

		// Introducing the new remove activity function for accuracy
		$model->removeOnePhotoActivity('photos', $photo->albumid , $photo->created, $photo->id, $photo->thumbnail );
	}
}