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

<?php
if( $approved )
{
	echo JText::sprintf( 'CC NEW MEMBER JOIN EMAIL' , $user , $group , '{url}' );
}
else
{
	echo JText::sprintf( 'CC NEW MEMBER REQUESTED TO JOIN GROUP EMAIL' , $user , $group , '{url}' );
}
