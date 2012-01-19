<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 */
defined('_JEXEC') or die( 'Restricted Access' );
?>
Hei {target},

<?php echo $user->getDisplayName(); ?> postet en kommentar på veggen din.

Melding:

<?php echo $message; ?>

Du kan lese meldingen her:


<?php echo $url; ?>


Ha en fin dag!

