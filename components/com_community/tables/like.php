<?php
/**
 * @category	Tables
 * @package	JomSocial
 * @subpackage	Activities
 * @copyright	(C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license	GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

class CTableLike extends CTableCache
{
    
	// Tables' fileds
	var $id		=   null;
	var $element	=   null;
	var $uid	=   null;
	var $like	=   null;
	var $dislike	=   null;

	/**
	 * Constructor
	 */
	function __construct( &$db )
	{
		parent::__construct( '#__community_likes', 'id', $db );
	}

	function store()
	{
		return parent::store();
	}
}