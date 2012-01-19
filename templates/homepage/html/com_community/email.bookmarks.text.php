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
Hei,

En bruker vil dele en lenke med deg.

Du kan se lenken her:
<?php echo $uri; ?>

<?php
if( !empty($message) )
{
?>
Melding:
===============================================================================

<?php echo $message; ?>

===============================================================================
<?php
}
?>