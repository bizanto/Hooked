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

{actor} has recently posted a new discussion in the group <?php echo $this->escape($group); ?>. Below is the discussion snippet that has been created.

Subject:
<?php echo $subject; ?>

Message:
<?php echo $message; ?>


To post a reply, you may visit the site at <a href="{url}">{url}</a>

Have a nice day!
