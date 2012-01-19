<?php
defined('_JEXEC') or die ('Restricted Access');

class TOOLBAR_saasy
{
	function _EDIT()
	{
		JToolBarHelper::title(JText::_('Edit Account Page Content'),'generic.png');

		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
	}

	function _DEFAULT()
	{
		JToolBarHelper::title(JText::_('Account Pages'),'generic.png');

		JToolBarHelper::editList();
		JToolBarHelper::preferences('com_saasy',300);
	}
}
?>
