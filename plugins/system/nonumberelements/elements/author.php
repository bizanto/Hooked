<?php
/**
 * Element: Author
 * Displays a selectbox of authors
 *
 * @package     NoNumber! Elements
 * @version     2.1.1
 *
 * @author      Peter van Westen <peter@nonumber.nl>
 * @link        http://www.nonumber.nl
 * @copyright   Copyright © 2010 NoNumber! All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Author Element
 */
class JElementAuthor extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Author';

	function fetchElement( $name, $value, &$node, $control_name )
	{
		return JHTML::_( 'list.users', $control_name.'['.$name.']', $value, 1 );
	}
}