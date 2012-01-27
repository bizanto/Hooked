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
JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
CAssets::attach( 'style.css' , 'css' , 'plugins/community/activitycomment/' );
?>
<script type="text/javascript" src="<?php echo JURI::root();?>plugins/community/activitycomment/activitycomment.js"></script>