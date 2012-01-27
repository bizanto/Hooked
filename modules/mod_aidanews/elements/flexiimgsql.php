<?php
/************************************************************************************
 mod_aidanews for Joomla 1.5 by Danilo A.

 @author: Danilo A. - dan@cdh.it 

This file is a modification of standard Joomla sql parameter.
Original file's copyright: 
 @version		$Id:sql.php 6961 2007-03-15 16:06:53Z tcp $
 @package		Joomla.Framework
 @subpackage	Parameter
 @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 @license		GNU/GPL
 Joomla! is free software. This version may have been modified pursuant
 to the GNU General Public License, and as distributed it includes or
 is derivative of works licensed under the GNU General Public License or
 other free or open source software licenses.

 ----- This file is part of the AiDaNews Module. -----

    AiDaNews Module is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    AiDaNews is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this module.  If not, see <http://www.gnu.org/licenses/>.
************************************************************************************/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a SQL element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementflexiimgsql extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'flexiimgsql';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db			= & JFactory::getDBO();
		$filename = 'components/com_flexicontent/index.html';

		if (file_exists($filename)) {
			$query = 'SELECT COUNT(*) FROM #__flexicontent_fields WHERE field_type = "image"';
						$db->setQuery($query);
						$imgfieldcheck = $db->loadResult();
			if (empty($imgfieldcheck)) {
				echo '<div style="text-align: center; font-weight: bold;">No FLEXIcontent image fields were found. Option disabled.<br>If you get any display error on frontend, create an image field (even if you will not use it) to make the module work.</div>';
			} else {
				$db->setQuery($node->attributes('query'));
				$key = ($node->attributes('key_field') ? $node->attributes('key_field') : 'value');
				$val = ($node->attributes('value_field') ? $node->attributes('value_field') : $name);
				return JHTML::_('select.genericlist',  $db->loadObjectList(), ''.$control_name.'['.$name.']', 'class="inputbox"', $key, $val, $value, $control_name.$name);
			}
		} else {
			echo "";
		} 
	}
}
?>