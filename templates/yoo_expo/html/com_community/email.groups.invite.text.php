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

{actor} inviterer deg til å delta i gruppen (<?php echo $groupname;?>). Nedenfor er meldingen fra {actor},

Melding:

<?php echo $message; ?>

Du kan besøke gruppen her:

<a href="{url}">{url}</a>


Ha en fin dag!

