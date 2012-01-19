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
Hi {target},

<?php echo $user->getDisplayName(); ?> just posted a comment on your wall. 

Message:

<?php echo $message; ?>

You can read the message at:


<?php echo $url; ?>


Have a nice day!

