<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
?>
Hei {target},

{actor} har nylig postet et svar på diskusjonen <?php echo $this->escape($group->name); ?> som du har deltatt i.

Emne:
<?php echo $subject; ?>

Melding:
<?php echo $message; ?>


Du kan poste et svar her <?php echo $url; ?>

Ha en fin dag!
