<?php
/**
 * @category	Core
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

$current	= JRequest::getWord( 'current' , 'main' , 'COOKIE' );
?>
<script type="text/javascript">
joms.jQuery(document).ready(function(){
	joms.jQuery('#submenu #<?php echo $current; ?>').attr('class', 'active');
});
</script>

<div class="submenu-box">
	<div class="submenu-pad">
		<ul id="submenu">
			<li><a id="main"<?php echo $current == 'main' ? ' class="active"' : '';?>><?php echo JText::_( 'CC SITE' ); ?></a></li>
			<li><a id="media"<?php echo $current == 'media' ? ' class="active"' : '';?>><?php echo JText::_( 'CC MEDIA' ); ?></a></li>
			<li><a id="groups"<?php echo $current == 'groups' ? ' class="active"' : '';?>><?php echo JText::_( 'CC GROUPS' ); ?></a></li>
			<li><a id="events"<?php echo $current == 'events' ? ' class="active"' : '';?>><?php echo JText::_( 'CC EVENTS' ); ?></a></li>
			<li><a id="layout"<?php echo $current == 'layout' ? ' class="active"' : '';?>><?php echo JText::_( 'CC LAYOUT' ); ?></a></li>
			<li><a id="privacy"<?php echo $current == 'privacy' ? ' class="active"' : '';?>><?php echo JText::_( 'CC PRIVACY' ); ?></a></li>
			<li><a id="network"<?php echo $current == 'network' ? ' class="active"' : '';?>><?php echo JText::_( 'CC NETWORK' ); ?></a></li>
			<li><a id="facebook-connect"<?php echo $current == 'facebook-connect' ? ' class="active"' : '';?>><?php echo JText::_( 'CC FACEBOOK CONNECT' ); ?></a></li>
			<li><a id="remote-storage"<?php echo $current == 'remotestorage' ? ' class="active"' : '';?>><?php echo JText::_( 'CC REMOTE STORAGE' ); ?></a></li>
			<li><a id="integrations"<?php echo $current == 'integrations' ? ' class="active"' : '';?>><?php echo JText::_( 'CC INTEGRATIONS' ); ?></a></li>
		</ul>
		<div class="clr"></div>
	</div>
</div>
<div class="clr"></div>
