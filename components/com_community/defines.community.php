<?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

define('COMMUNITY_DEFAULT_VIEW', 'profile');

/**
 * To fix for existing 3rd party templates as earlier we do have this check for Free versions.
 **/ 
define('COMMUNITY_FREE_VERSION' , false );

define('COMMUNITY_COM_PATH', JPATH_ROOT .DS.'components'.DS.'com_community');
define('COMMUNITY_COM_URL' , JURI::base() . 'components/com_community' );


define('COMMUNITY_PRIVACY_PRIVATE'	, 0);	
define('COMMUNITY_PRIVACY_PUBLIC'	, 1);
define('COMMUNITY_PRIVACY_FRIENDS'	, 2);
define('COMMUNITY_PRIVACY_CUSTOM'	, 3);

define('COMMUNITY_OVERSAMPLING_FACTOR', 2);
define('COMMUNITY_SMALL_AVATAR_WIDTH', 64);
define('COMMUNITY_PHOTO_THUMBNAIL_SIZE', 128);

define('PRIVACY_FORCE_PUBLIC'	, -1);
define('PRIVACY_PUBLIC',  10);
define('PRIVACY_MEMBERS', 20);
define('PRIVACY_FRIENDS', 30);
define('PRIVACY_PRIVATE', 40);

// Application privacy constants.
define('PRIVACY_APPS_PUBLIC' , 0 );
define('PRIVACY_APPS_FRIENDS' , 10 );
define('PRIVACY_APPS_SELF' , 20 );

define('CONNECTION_FRIENDS', 1);
define('CONNECTION_HUSBAND', 2);
define('CONNECTION_WIFE', 	 3);

define( 'CC_RANDOMIZE', true);

define( 'ACTIVITY_INTERVAL_DAY', 1);
define( 'ACTIVITY_INTERVAL_WEEK', 7);
define( 'ACTIVITY_INTERVAL_MONTH', 30);

define( 'MAGICK_FILTER' , 13 );

define( 'COMMUNITY_PRIVATE_GROUP' , 1 );
define( 'COMMUNITY_PUBLIC_GROUP' , 0 );

define( 'SUBMENU_LEFT', False);
define( 'SUBMENU_RIGHT', True);

define( 'FACEBOOK_FAVICON' , COMMUNITY_COM_PATH . DS . 'assets' . DS . 'favicon' . DS . 'facebook.gif' );
define( 'FACEBOOK_BUTTON_CSS' , 'http://www.facebook.com/css/connect/connect_button.css' );
define( 'FACEBOOK_LOGIN_NOT_REQUIRED' , false );

define( 'DEFAULT_USER_AVATAR' , 'components/com_community/assets/default.jpg' );
define( 'DEFAULT_USER_THUMB' , 'components/com_community/assets/default_thumb.jpg' );

define( 'DEFAULT_GROUP_AVATAR' , 'components/com_community/assets/group.jpg');
define( 'DEFAULT_GROUP_THUMB' , 'components/com_community/assets/group_thumb.jpg' );

define( 'TOOLBAR_HOME', 'HOME');
define( 'TOOLBAR_PROFILE', 'PROFILE');
define( 'TOOLBAR_FRIEND', 'FRIEND');
define( 'TOOLBAR_APP', 'APP');
define( 'TOOLBAR_INBOX', 'INBOX');

define( 'FEATURED_GROUPS' , 'groups' );
define( 'FEATURED_USERS' , 'users' );
define( 'FEATURED_VIDEOS' , 'videos' );
define( 'FEATURED_ALBUMS' , 'albums' );

define( 'PHOTOS_USER_TYPE' , 'user' );
define( 'PHOTOS_GROUP_TYPE' , 'group' );
define( 'COMMUNITY_GROUP_ADMIN' , 1 );
define( 'COMMUNITY_GROUP_MEMBER' , 0 );
define( 'COMMUNITY_GROUP_BANNED' , -1 );

define( 'VIDEO_USER_TYPE' , 'user' );
define( 'VIDEO_GROUP_TYPE' , 'group' );

define( 'DISCUSSION_ORDER_BYCREATION' , 1 );
define( 'DISCUSSION_ORDER_BYLASTACTIVITY' , 0 );


define( 'GROUP_PHOTO_PERMISSION_DISABLE' , -1 );
define( 'GROUP_PHOTO_PERMISSION_MEMBERS' , 0 );
define( 'GROUP_PHOTO_PERMISSION_ADMINS' , 1 );

define( 'GROUP_VIDEO_PERMISSION_DISABLE' , -1 );
define( 'GROUP_VIDEO_PERMISSION_MEMBERS' , 0 );
define( 'GROUP_VIDEO_PERMISSION_ADMINS' , 1 );

define( 'GROUP_EVENT_PERMISSION_DISABLE' , -1 );
define( 'GROUP_EVENT_PERMISSION_MEMBERS' , 0 );
define( 'GROUP_EVENT_PERMISSION_ADMINS' , 1 );
define( 'GROUP_EVENT_RECENT_LIMIT' , 6 );
define( 'GROUP_PHOTO_RECENT_LIMIT' , 6 );
define( 'GROUP_VIDEO_RECENT_LIMIT' , 6 );

define( 'FRIEND_SUGGESTION_LEVEL' , 2 );

define( 'VIDEO_FOLDER_NAME', 'videos' );
define( 'ORIGINAL_VIDEO_FOLDER_NAME', 'originalvideos' );
define( 'VIDEO_THUMB_FOLDER_NAME', 'thumbs' );

define( 'STREAM_CONTENT_LENGTH', 150 );
define( 'PROFILE_MAX_FRIEND_LIMIT', 12 );

define( 'VIDEO_TIPS_LENGTH' , 450 );

define( 'WALLS_GROUP_TYPE' , 'groups' );
define( 'SHOW_GROUP_ADMIN' , true );

define( 'COMMUNITY_PRIVATE_EVENT' , 1 );
define( 'COMMUNITY_PUBLIC_EVENT' , 0 );

define( 'COMMUNITY_TEMPLATE_XML' , 'templateDetails.xml' );

define( 'COMMUNITY_EVENT_ADMINISTRATOR' 		, -1 );
define( 'COMMUNITY_EVENT_STATUS_INVITED' 		, 0 );
define( 'COMMUNITY_EVENT_STATUS_ATTEND' 		, 1 );
define( 'COMMUNITY_EVENT_STATUS_WONTATTEND' 	, 2 );
define( 'COMMUNITY_EVENT_STATUS_MAYBE' 			, 3 );
define( 'COMMUNITY_EVENT_STATUS_BLOCKED' 		, 4 );
define( 'COMMUNITY_EVENT_STATUS_IGNORE'			, 5 );
define( 'COMMUNITY_EVENT_STATUS_REQUESTINVITE' 	, 6 );
define( 'COMMUNITY_EVENT_STATUS_NOTINVITED'		, 7 );
define( 'COMMUNITY_RAW_STATUS' , true );

// Zend caching tag.
define( 'COMMUNITY_CACHE_TAG_MEMBERS'    , 'members' );
define( 'COMMUNITY_CACHE_TAG_VIDEOS'     , 'videos' );
define( 'COMMUNITY_CACHE_TAG_VIDEOS_CAT' , 'videos_category' );
define( 'COMMUNITY_CACHE_TAG_ACTIVITIES' , 'activities' );
define( 'COMMUNITY_CACHE_TAG_GROUPS' 	 , 'groups' );
define( 'COMMUNITY_CACHE_TAG_GROUPS_CAT' , 'groups_category' );
define( 'COMMUNITY_CACHE_TAG_PHOTOS' 	 , 'photos' );  
define( 'COMMUNITY_CACHE_TAG_ALBUMS' 	 , 'albums' );
define( 'COMMUNITY_CACHE_TAG_EVENTS' 	 , 'events' );
define( 'COMMUNITY_CACHE_TAG_EVENTS_CAT' , 'events_category' );
define( 'COMMUNITY_CACHE_TAG_ALL' 		 , 'all' );

define( 'COMMUNITY_CACHE_RANDOM_MIN' 	 , 1 );
define( 'COMMUNITY_CACHE_RANDOM_MAX' 	 , 3 );
define( 'COMMUNITY_WALLS_EDIT_INTERVAL' , 900 );

define( 'COMMUNITY_HIDE' , 0 );
define( 'COMMUNITY_SHOW' , 1 );
define( 'COMMUNITY_MEMBERS_ONLY' , 2 );
/* Location */
define( 'COMMUNITY_LOCATION_NULL' 	, 255 );


define ('COMMUNITY_DATE_FIXED', 'fixed');
define ('COMMUNITY_DATE_LAPSE', 'lapse');

/* 1.8.6 */
define ('COMMUNITY_EVENT_WITHIN_5', 5);
define ('COMMUNITY_EVENT_WITHIN_10', 10);
define ('COMMUNITY_EVENT_WITHIN_20', 20);
define ('COMMUNITY_EVENT_WITHIN_50', 50);

/* 2.0.1 */
define( 'COMMUNITY_GROUPS_NO_LIMIT' , 0 );
define( 'COMMUNITY_GROUPS_NO_RANDOM' , false );
define( 'COMMUNITY_GROUPS_ONLY_APPROVED' , true );
define( 'COMMUNITY_GROUPS_SHOW_ADMINS' , true );
define( 'COMMUNITY_SHOW_ACTIVITY_MORE' , true );
define( 'COMMUNITY_SHOW_ACTIVITY_ARCHIVED' , true );

define( 'COMMUNITY_DAY_HOURS' , 24 );
// Relative path to the watermarks folder.
define( 'COMMUNITY_WATERMARKS_PATH' , 'images' . DS . 'watermarks' );	

/* 2.0.2 */
define( 'COMMUNITY_ORDERING_BY_ORDER' , 'ordering' );
define( 'COMMUNITY_ORDERING_BY_CREATED' , 'created' );
define( 'COMMUNITY_ORDER_BY_DESC' , 'DESC' );
define( 'COMMUNITY_ORDER_BY_ASC' , 'ASC' );

class CDefined
{
	const STREAM_CONTENT_LENGTH = 150;
}

define( 'COMMUNITY_TEMPLATE_PATH', COMMUNITY_COM_PATH . DS . 'templates' );
define( 'COMMUNITY_TEMPLATE_URL' , JURI::base() . 'components/com_community/templates' );

define( 'COMMUNITY_DISLIKE', 0 );
define( 'COMMUNITY_UNLIKE', -1 );
define( 'COMMUNITY_LIKE', 1 );

define( 'COMMUNITY_NO_PARENT', 0 );
define( 'COMMUNITY_ALL_CATEGORIES', 'all' );
define( 'COMMUNITY_DEFAULT_PROFILE' , 0 );