<?php
/**
 * @category	Model
 * @package	JomSocial
 * @subpackage	Groups
 * @copyright	(C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license	GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

require_once ( JPATH_ROOT .DS.'components'.DS.'com_community'.DS.'models'.DS.'models.php');

// Deprecated since 1.8.x to support older modules / plugins
CFactory::load( 'tables', 'like' );

class CommunityModelLike extends JCCModel
{
	function addLike( $obj )
	{
		$db	=&  JFactory::getDBO();

		$db->insertObject( '#__community_likes', $obj );
	}

	function addDislike( $obj )
	{
		$db	=&  JFactory::getDBO();

		$db->insertObject( '#__community_likes', $obj );
	}

	// Check if the vote for the item is exist
	function getInfo( $element, $itemId )
	{
		$db	=&  JFactory::getDBO();

		$query	=   'SELECT * FROM ' . $db->nameQuote('#__community_likes') . ' '
			    . 'WHERE ' . $db->nameQuote('element') . '=' . $db->Quote( $element ) . ' '
			    . 'AND ' . $db->nameQuote('uid') . '=' . $db->Quote( $itemId );

		$db->setQuery( $query );

		$result	=   $db->loadObject();

		if($db->getErrorNum()) {
			JError::raiseError( 500, $db->stderr());
		}

		return $result;
	}
}

?>
