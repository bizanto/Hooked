<?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>

<div id="fb-root"></div>
<script src="http://connect.facebook.net/en_US/all.js"></script>
<script type="text/javascript">
window.fbAsyncInit = function() {
    FB.init({appId: '<?php echo $config->get('fbconnectkey');?>', status: false, cookie: true, xfbml: true});

    /* All the events registered */
    FB.Event.subscribe('auth.login', function(response) {
		joms.connect.update();
  });
};
</script>
<fb:login-button autologoutlink="true" perms="read_stream,publish_stream,offline_access,email,user_birthday,status_update,user_status"><?php echo JText::_('CC SIGN IN WITH FACEBOOK');?></fb:login-button>

