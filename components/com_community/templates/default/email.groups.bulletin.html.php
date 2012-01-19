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

<?php echo $this->escape($group); ?> group just posted a new bulletin.

Subject: <?php echo $this->escape($subject); ?>


You can read your new message here:


<a href="{url}">{url}</a>


Have a nice day!

