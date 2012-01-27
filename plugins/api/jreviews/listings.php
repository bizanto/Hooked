<?php
/**
 * @package	API
 * @version 1.5
 * @author 	Brian Edgerton
 * @link 	http://www.edgewebworks.com
 * @copyright Copyright (C) 2011 Edge Web Works, LLC. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class ApiResourceListings extends ApiResource {
	
	private $wheres	= array();
	
	public function get() {
		JLoader::register('JReviewsListingsHelper', dirname(__FILE__).'/listings.helper.php');
		JLoader::register('RelateAPIHelper', JPATH_SITE.'/plugins/api/relate/helper.php');
		
		$helper = new JReviewsListingsHelper($this->plugin);

		$listings = $helper->getListings();
		$this->plugin->setResponse($listings);
	}
	
}