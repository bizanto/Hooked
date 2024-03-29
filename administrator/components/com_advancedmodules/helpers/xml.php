<?php
/**
 * @package     Advanced Module Manager
 * @version     1.16.1
 *
 * @author      Peter van Westen <peter@nonumber.nl>
 * @link        http://www.nonumber.nl
 * @copyright   Copyright © 2010 NoNumber! All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * BASE ON JOOMLA CORE FILE:
 * /administrator/components/com_modules/helpers/xml.php
 */

/**
 * @version		$Id: xml.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * @package		Joomla
 * @subpackage	Modules
 */
class ModulesHelperXML
{
	function parseXMLModuleFile( &$rows  )
	{
		foreach ( $rows as $i => $row ) {
			if ( $row->module == '' ) {
				$rows[$i]->name 	= 'custom';
				$rows[$i]->module 	= 'custom';
				$rows[$i]->descrip 	= 'Custom created module, using Module Manager `New` function';
			} else {
				$data = JApplicationHelper::parseXMLInstallFile( $row->path.DS.$row->file);

				if ( $data['type'] == 'module' ) {
					$rows[$i]->name		= $data['name'];
					$rows[$i]->descrip	= $data['description'];
				}
			}
		}
	}
}