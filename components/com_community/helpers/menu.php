<?php
/**
 * @category	Helper
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php' );

class CMenuHelper
{
	/**
	 *	Returns an object of data containing user's address information
	 *
	 *	@access	static
	 *	@params	int	$userId
	 *	@return stdClass Object	 	 	 
	 **/
	static public function getComponentId()
	{
		$db		=& JFactory::getDBO();
		
		$query	= 'SELECT ' . $db->nameQuote( 'id' ) . ' FROM '
				. $db->nameQuote( '#__components' ) . ' WHERE '
				. $db->nameQuote( 'option' ) . '=' . $db->Quote( 'com_community' ) . ' '
				. 'AND ' . $db->nameQuote( 'link' ) . '=' . $db->Quote( 'option=com_community' );
		$db->setQuery( $query );
		return $db->loadResult();
	}
}