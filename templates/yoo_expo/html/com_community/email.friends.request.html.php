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
Hei {target},

{actor} la deg til som venn, men du må selv velge om du vil akseptere vedkommendes forespørsel. 

<?php 
	if(!empty($msg))
	{
		echo $msg;
	}
?>

Om du vil legge til {actor} som din venn går du bare til følgende side:

<a href="{url}">{url}</a>


Ha en fin dag!
