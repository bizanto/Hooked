<?php
/**
 * @package		ZEND library
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
(defined('_VALID_MOS') OR defined('_JEXEC')) or die('Direct Access to this location is not allowed.');


/** Define some constants that can be used by the system **/
if( !defined( 'ZEND_PATH' ) )
{
	// Get the real system path.
	$system	= rtrim(  dirname( __FILE__ ) , '/' );

	define( 'ZEND_PATH' , $system );
} 
