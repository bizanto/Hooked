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

{actor} added you as friend. You still need to approve this request.

<?php 
	if(!empty($msg))
	{
		echo $msg;
	}
?>

To add {actor} as your friend, just go to your friends request page below:

{url}


Have a nice day!
