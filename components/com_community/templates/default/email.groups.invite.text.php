<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
?>
Hi {target},

{actor} invited you to join a group (<?php echo $groupname;?>). Below is a message that is sent by {actor},

Message:

<?php echo $message; ?>

To view the group, access the URL at the following location:

<a href="{url}">{url}</a>


Have a nice day!

