<?php
/**
 * @category	Helper
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Handle cache generate / remove in the model class & cache remove in cac
 *
 */
class CCache
{
	// Cache action constant
	const ACTION_SAVE    = 'save'; 
	const ACTION_REMOVE	 = 'delete';

	// Table's method name for cache
	const METHOD_DEL     = 'delete';
	const METHOD_SAVE	 = 'save'; 
	const METHOD_STORE   = 'store';

	public $aSetting  = array();

    // Pass in any obj to find the embeded caching object
    // This is to make sure there is a standard way to embed and use this caching object in any object
    public static function load($obj) {
        if (isset($obj->oCache) && $obj->oCache instanceof CCache) {
            return $obj->oCache;
        } else {
            return FALSE;
        }
    }

    // Pass in any obj to embed the caching object
    // This is to make sure there is a standard way to embed and use this caching object in any object
    public static function inject($obj) {
        $obj->oCache = new self();
        return $obj->oCache;
    }
    
    // Add method that need to be cache.
    public function addMethod($method, $action, $tag)
    {
    	$this->aSetting[$method] = array('action' => $action, 'tag' => $tag);
	}
	
	// Get the method cache flag.
	public function getMethod($method)
	{
		if (isset($this->aSetting[$method])) {
			return $this->aSetting[$method];
		} else {
			return FALSE;
		}
	}
	
	// Delete cache.
	public function remove($tag)
	{
		$oZendCache = CFactory::getCache('core');
		
		if ($tag == COMMUNITY_CACHE_TAG_ALL) {
			$oZendCache->clean(Zend_Cache::CLEANING_MODE_ALL);
		} else {
			$oZendCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tag);
		}
	}
	
}