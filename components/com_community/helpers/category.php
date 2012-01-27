<?php
/**
 * @category	Helper
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

class CCategoryHelper
{
	static public function getCategories($rows)
	{
				
		
		// Reset array key
		foreach( $rows as $key=>$row)
		{
			$row				= (array)$row;
			$keyId				= $row['id'];
			$tmpRows[$keyId]	= $row;
		}  

		foreach( $tmpRows as $key=>$row )
		{	                      
			$row['nodeText']	= CCategoryHelper::_getCat( $tmpRows, $row['id'] );

			$row['nodeId']		= explode( ',',CCategoryHelper::_getCatId( $tmpRows, $row['id'] ) );
			$sort1[$key]		= $row['nodeId'][0];
			$sort2[$key]		= $row['parent'];
			
			$categories[]		= $row;
		}
	
		array_multisort($sort1, SORT_ASC, $sort2, SORT_ASC, $categories);   
		
		return	$categories;

	} 
		
	static private function _getCat($rows,$id) 
	{   
	    if($rows[$id]['parent'] > 0 && $rows[$id]['parent'] != $rows[$id]['id']) {
	        return CCategoryHelper::_getCat($rows, $rows[$id]['parent']) . ' &rsaquo; ' . $rows[$id]['name'];
	    }
	    else {
			return $rows[$id]['name']; 
	    }
	}
	 		
	static private function _getCatId($rows,$id) 
	{   
	    if($rows[$id]['parent'] > 0 && $rows[$id]['parent'] != $rows[$id]['id']) {
	        return CCategoryHelper::_getCatId($rows, $rows[$id]['parent']) . ',' . $rows[$id]['id'];
	    }
	    else {
			return $rows[$id]['id']; 
	    }
	}
}