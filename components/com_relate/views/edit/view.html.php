<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');

class RelateViewEdit extends JView
{
	function display($tpl = null)
	{
		$listing_id = JRequest::getInt('id', '');
		
		$db =& JFactory::getDBO();
		$sql = "SELECT title FROM #__content WHERE id = '$listing_id'";
		$db->setQuery($sql);
		$listing_title = $db->loadResult();
		
		$model =& $this->getModel();
		
		$categories = $model->getAllowedCategories($listing_id);
		
		$this->assignRef('listing_id', $listing_id);
		$this->assignRef('listing_title', $listing_title);
		
		$this->assignRef('categories', $categories);

		parent::display($tpl);
	}
}
