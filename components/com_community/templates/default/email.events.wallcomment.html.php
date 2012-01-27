<?php
/**
 * @package		JomSocial
 * @subpackage	Core 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
Hi {target},

{actor} just posted a comment in your wall post. 

Message:

<?php echo $message; ?>

You can read the message at:


<a href="{url}">{url}</a>


Have a nice day!

