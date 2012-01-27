<?php
/**
 * CSV Import Component for Content and jReviews
 * Copyright (C) 2008 NakedJoomla and Alejandro Schmeichler
 * This is not free software. Do not distribute it.
 * For license information visit http://www.nakedjoomla.com/license/csv_import_license.html
 * or contact info@nakedjoomla.com
**/

// no direct access
(defined('_VALID_MOS') OR defined('_JEXEC')) or die('Direct Access to this location is not allowed.');

/**
 * Enter the key=>value pairs of characters/symbols to replace while importing data. 
 * Some characters need to be converted to properly store them in the database
 */

$character_map = array(
	chr(145)	=>	"'",
    chr(146)	=>	"'",
    chr(147)	=>	'"', 
    chr(148)	=>	'"', 
    chr(151)	=>	'-',
    '™'			=>	'&#0153;',
	'©'			=>	'&#0169;',
	'®'			=>	'&#0174;',
	"\n"		=>	'<br />',
	'«' 		=>  '&#171;',
	'»'			=>	'&#187;'
);
?>