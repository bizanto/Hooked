<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 *
 * @param	{target}	string The name of the target
 * @param	$url		string	The URL to the specific group
 * @param	$user		string	The name of the user
 * @param	$group		string	The name of the group
 */
defined('_JEXEC') or die();
?>
Hi {target},

{actor} just sent you a new message.

<?php if( !empty( $title ) ){ ?>
Title:
<?php echo $title; ?>
<?php } ?>


Message:
<?php echo $message; ?>


You can also read your new message on the site here:

{url}


Have a nice day!
