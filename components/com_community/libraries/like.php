<?php
/**
 * @category	Libraries
 * @package		JomSocial
 * @copyright (C) 2010 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
 
defined('_JEXEC') or die('Restricted access');

class CLike 
{

	public function addLike( $element, $itemId )
	{
		$my		=   CFactory::getUser();

		$likesModel	=&  CFactory::getModel( 'Like' );
		$info		=   $likesModel->getInfo( $element, $itemId );

		if( empty($info) )
		{
		    $likes		=   new stdClass();
		    $likes->element	=   $element;
		    $likes->uid		=   $itemId;
		    $likes->like	=   $my->id . ',';
		    $likes->dislike	=   '';
		    $likesModel->addLike( $likes );

		    return true;
		}

		$likes	=&  JTable::getInstance( 'Like' , 'CTable' );
		$likes->load( $info->id );

		// Check if user already like
		$likesInArray	=   explode( ',', $likes->like );

		if( in_array( $my->id, $likesInArray ) )
		{
			return false;
		}

		// Check if the user already dislike
		$dislikesInArray	=   explode( ',', $likes->dislike );

		if( in_array( $my->id, $dislikesInArray ) )
		{
			// Remove user like from array
			$key	=   array_search( $my->id, $dislikesInArray );
			unset( $dislikesInArray[$key] );

			$likes->dislike	=   implode( ',', $dislikesInArray );
		}

		$likes->element	=   $likes->element;
		$likes->uid	=   $likes->uid;
		$likes->like	=   $likes->like . $my->id . ',';
		$likes->dislike	=   $likes->dislike;
		$likes->store();

		return true;

	}

	public function addDislike( $element, $itemId )
	{
		$my		=   CFactory::getUser();

		$dislikesModel	=&  CFactory::getModel( 'Like' );
		$info		=   $dislikesModel->getInfo( $element, $itemId );

		if( empty($info) )
		{
		    $dislikes		=   new stdClass();
		    $dislikes->element	=   $element;
		    $dislikes->uid	=   $itemId;
		    $dislikes->like	=   '';
		    $dislikes->dislike	=   $my->id . ',';
		    $dislikesModel->addDislike( $dislikes );

		    return true;
		}

		$dislikes	=&  JTable::getInstance( 'Like' , 'CTable' );
		$dislikes->load( $info->id );

		$dislikesInArray	=   explode( ',', $dislikes->dislike );

		// Check if the user already dislike
		if( in_array( $my->id, $dislikesInArray ) )
		{
			return false;
		}

		// Check if the user already like
		$likesInArray	=   explode( ',', $dislikes->like );

		if( in_array( $my->id, $likesInArray ) )
		{
			// Remove user like from array
			$key	=   array_search( $my->id, $likesInArray );
			unset( $likesInArray[$key] );

			$dislikes->like	    =   implode( ',', $likesInArray );
			
		}

		$dislikes->element  =   $dislikes->element;
		$dislikes->uid	    =   $dislikes->uid;
		$dislikes->like	    =	$dislikes->like;
		$dislikes->dislike  =   $dislikes->dislike . $my->id . ',';
		$dislikes->store();

		return true;

	}

	public function unlike( $element, $itemId )
	{
		$my	=   CFactory::getUser();

		$model	=&  CFactory::getModel( 'Like' );
		$info	=   $model->getInfo( $element, $itemId );

		if( !$info )
		{
			return false;
		}

		$likes	=&  JTable::getInstance( 'Like' , 'CTable' );
		$likes->load( $info->id );

		// Check if user already like
		$likesInArray	=   explode( ',', $likes->like );

		if( in_array( $my->id, $likesInArray ) )
		{
			// Remove user like from array
			$key	=   array_search( $my->id, $likesInArray );
			unset( $likesInArray[$key] );

			$likes->like	    =   implode( ',', $likesInArray );
		}

		$dislikesInArray	=   explode( ',', $likes->dislike );

		if( in_array( $my->id, $dislikesInArray ) )
		{
			// Remove user like from array
			$key	=   array_search( $my->id, $dislikesInArray );
			unset( $dislikesInArray[$key] );

			$likes->dislike	=   implode( ',', $dislikesInArray );
		}

		$likes->element	=   $likes->element;
		$likes->uid	=   $likes->uid;
		$likes->like	=   $likes->like;
		$likes->dislike	=   $likes->dislike;
		$likes->store();

		return true;
	}

	public function undislike( $element, $itemId )
	{
		$my	=   CFactory::getUser();

		$model	=&  CFactory::getModel( 'Like' );
		$info	=   $model->getInfo( $element, $itemId );

		if( !$info )
		{
			return false;
		}

		$likes	=&  JTable::getInstance( 'Like' , 'CTable' );
		$likes->load( $info->id );

		// Check if user already like
		$dislikesInArray	=   explode( ',', $likes->dislike );

		if( in_array( $my->id, $likesInArray ) )
		{
			// Remove user like from array
			$key	=   array_search( $my->id, $likesInArray );
			unset( $likesInArray[$key] );

			$dislikes->like	    =   implode( ',', $likesInArray );
		}

		$likesInArray	=   explode( ',', $likes->like );

		if( in_array( $my->id, $likesInArray ) )
		{
			// Remove user like from array
			$key	=   array_search( $my->id, $likesInArray );
			unset( $likesInArray[$key] );

			$likes->like	=   implode( ',', $likesInArray );
		}

		$likes->element	=   $likes->element;
		$likes->uid		=   $likes->uid;
		$likes->like	=   $likes->like;
		$likes->dislike	=   $likes->dislike;
		$likes->store();

		return true;
	}

	// Check if the user like this
	// Returns:
	// -1	- Unlike
	// 1	- Like
	// 0	- Dislike
	public function userLiked( $element, $itemId, $userId )
	{
		$likesModel	=&  CFactory::getModel( 'Like' );
		$info		=   $likesModel->getInfo( $element, $itemId );
		
		if( empty($info) )
		{
			//Return -1 as neutral
			return COMMUNITY_UNLIKE;
		}

		$likes	=&  JTable::getInstance( 'Like' , 'CTable' );
		$likes->load( $info->id );

		// Check if user already like
		$likesInArray	=   explode( ',', $likes->like );

		if( in_array( $userId, $likesInArray ) )
		{
			// Return 1, the user is liked
			return COMMUNITY_LIKE;
		}

		// Check if user already dislike
		$dislikesInArray	=   explode( ',', $likes->dislike );

		if( in_array( $userId, $dislikesInArray ) )
		{
			// Return 0, the user is disliked
			return COMMUNITY_DISLIKE;
		}

		// Return -1 as neutral
		return COMMUNITY_UNLIKE;
	}

	/**
	 * Can current $my user 'like' an item ?
	 * - rule: friend can like friend's item (photos/vidoes/event)
	 * @return bool
	 */
	public function canLike()
	{
		$my =	CFactory::getInstance();

		return ( $my->id != 0 );
	}

	/**
	 *
	 * @return string
	 */
	public function getHTML( $element, $itemId, $userId )
	{
		// @rule: Only display likes html codes when likes is allowed.
		$config		=& CFactory::getConfig();
		
		if( !$config->get( 'likes_' . $element ) )
		{
			return;
		}

		// TODO: WRAP into another function
		$likesModel	=&  CFactory::getModel( 'Like' );
		$info		=   $likesModel->getInfo( $element, $itemId );

		$likes	    = 0;
		$dislikes   = 0;
		$userLiked  = COMMUNITY_UNLIKE;

		if( $info )
		{
			$like	=&  JTable::getInstance( 'Like' , 'CTable' );
			$like->load( $info->id );

			$likesInArray	    =	array();
			$dislikesInArray    =	array();

			if( $like )
			{
				$likesInArray	    =   explode( ',', $like->like );
				$dislikesInArray    =   explode( ',', $like->dislike );
			}

			$likes	    =	count( $likesInArray )-1;
			$dislikes   =	count( $dislikesInArray )-1;
			$userLiked  =	$this->userLiked( $element, $itemId, $userId );
		}
		
		$tmpl	= new CTemplate();
		$tmpl->set( 'likeId' ,      'like'.'-'.$element.'-'.$itemId );
		$tmpl->set( 'likes',	    $likes );
		$tmpl->set( 'dislikes',	    $dislikes );
		$tmpl->set( 'userLiked',    $userLiked );
		
		if(!COwnerHelper::isRegisteredUser()){ 
			return $this->getHtmlPublic( $element, $itemId );
		}else{                                  
			return $tmpl->fetch( 'like.html' );
		}                              
			
	}

	/**
	 * Display like/dislike for public
	 * @return string
	 */
	public function getHtmlPublic( $element, $itemId )
	{   
		$config		= CFactory::getConfig();     
		$likesModel	=& CFactory::getModel( 'Like' );  
		$info		= $likesModel->getInfo( $element, $itemId );
		
		$likes	    = 0;
		$dislikes   = 0;

		if( $info )
		{
			$like	=&  JTable::getInstance( 'Like' , 'CTable' );
			$like->load( $info->id );

			$likesInArray	    =	array();
			$dislikesInArray    =	array();

			if( $like )
			{
				$likesInArray	    =   explode( ',', $like->like );
				$dislikesInArray    =   explode( ',', $like->dislike );
			}

			$likes	    =	count( $likesInArray )-1;
			$dislikes   =	count( $dislikesInArray )-1;
		}

		$tmpl	= new CTemplate();
		$tmpl->set( 'likes',	    $likes );
		$tmpl->set( 'dislikes',	    $dislikes );
		
		if( $config->get('show_like_public') )
			return $tmpl->fetch( 'like.public' );
		
	}
	
}