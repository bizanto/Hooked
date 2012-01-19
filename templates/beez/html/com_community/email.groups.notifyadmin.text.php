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

{actor} har nylig opprettet en ny gruppe <?php echo $groupName;?>. Du vil måtte godkjenne eller avvise gruppen ettersom moderering av grupper er aktivert.

Du finner administrasjonen her <a href="{url}">{url}</a>

Ha en fin dag!
