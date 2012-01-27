<?php 
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');

class RelateViewRelations extends JView
{
	function display($tpl = null)
	{
		$db =& JFactory::getDBO();
		$q = "SELECT c.id, c.title, r.relatable FROM #__categories c, #__relate_categories r ".
		     "WHERE c.id = r.catID ORDER BY c.section, c.ordering";
		$db->setQuery($q);
		
		$categories = $db->loadObjectList();
		
		$this->assignRef('categories', $categories);
		
		JToolBarHelper::title(JText::_('Relations'), 'generic.png');
		JToolBarHelper::save();
		JToolBarHelper::cancel();
		
		parent::display($tpl);
	}
}
