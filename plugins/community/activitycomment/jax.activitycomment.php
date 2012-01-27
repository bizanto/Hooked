<?php
/**
 * @package		ActivityComment
 * @copyright	Copyright (C) 2009 - 2010 SocialCode. All rights reserved.
 * @license		GNU/GPL
 * ActivityComment is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses.
 */
defined('_JEXEC') or die('Restricted access');
global $jaxFuncNames;

// First argument should always be plugins to let Community know that its a plugin AJAX call.
// Second argument should be the plugin name, for instance 'profile'
// Third argument should be the plugin's function name to be called.
// It must be comma separated.
$jaxFuncNames[]	= 'plugins,activitycomment,savecomment';
$jaxFuncNames[]	= 'plugins,activitycomment,removecomment';
$jaxFuncNames[]	= 'plugins,activitycomment,morecomments';
$jaxFuncNames[]	= 'plugins,activitycomment,likeitem';
$jaxFuncNames[]	= 'plugins,activitycomment,unlikeitem';
$jaxFuncNames[]	= 'plugins,activitycomment,addNote';
$jaxFuncNames[]	= 'plugins,activitycomment,addPhoto';
$jaxFuncNames[]	= 'plugins,activitycomment,getMeta';
$jaxFuncNames[]	= 'plugins,activitycomment,waiting';
$jaxFuncNames[]	= 'plugins,activitycomment,addUrl';
$jaxFuncNames[]	= 'plugins,activitycomment,subscribe';
$jaxFuncNames[]	= 'plugins,activitycomment,unsubscribe';
$jaxFuncNames[]	= 'plugins,activitycomment,repost';