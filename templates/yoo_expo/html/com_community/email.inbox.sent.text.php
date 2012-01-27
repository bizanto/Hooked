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
Hei {target},

{actor} har sendt deg en ny melding.

<?php if( !empty( $title ) ){ ?>
Tittel:
<?php echo $title; ?>
<?php } ?>


Melding:
<?php echo $message; ?>


Du kan også lese meldingen på nettsiden her:

{url}


Ha en fin dag!
