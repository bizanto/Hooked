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
Hi there,

A user would like to share a link with you.

You can view the link at:
<a href="<?php echo $uri; ?>"><?php echo $uri; ?></a>

<?php
if( !empty($message) )
{
?>
Message:
===============================================================================

<?php echo $message; ?>


===============================================================================
<?php
}
?>