<?php 
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');

class RelateViewRules extends JView
{
	function display($tpl = null)
	{
		$db =& JFactory::getDBO();
		$q = "SELECT c.id, c.title FROM #__categories c, #__relate_categories r ".
		     "WHERE c.id = r.catID ORDER BY c.section, c.ordering";
		$db->setQuery($q);
		
		$categories = $db->loadObjectList();
		
		$q = "SELECT name, CONCAT(link, '&Itemid=', id) AS menulink FROM #__menu";
		$db->setQuery($q);
		
		$menuitems = $db->loadObjectList();

		$lists['menuitems'] = JHTML::_('select.genericlist', $menuitems, 'menu_link', '', 'menulink', 'name');
		
		$this->assignRef('categories', $categories);
		$this->assignRef('lists', $lists);

		JToolBarHelper::title(JText::_('Relations'), 'generic.png');
		JToolBarHelper::save();
		JToolBarHelper::cancel();
		
		parent::display($tpl);
	}
}
