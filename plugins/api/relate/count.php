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

class ApiResourceCount extends ApiResource {

	public function get() {
		$id = JRequest::getInt('id', 0);
		if (!$id)
		{
			$this->plugin->setResponse(null);
			return;
		}	
		
		$catid = JRequest::getInt('catid', 0);
		if (!$catid)
		{
			$this->plugin->setResponse(null);
			return;
		}	
		
		$db =& JFactory::getDBO();
		$query = "SELECT COUNT(c.id) FROM `#__content` c, `#__relate_listings` r "
		        ."WHERE (r.id1 = $id AND r.id2 = c.id) AND c.catid = $catid AND c.state > 0";
		$db->setQuery($query);
		$count = $db->loadResult();
		
		$this->plugin->setResponse($count);
	}
	
}
