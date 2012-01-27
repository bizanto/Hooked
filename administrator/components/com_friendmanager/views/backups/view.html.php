<?php
/**
 * @version $Id$
 * @package    FriendManager
 * @subpackage _ECR_SUBPACKAGE_
 * @author     Socialable Studios {@link http://www.Socialables.com}
 * @author     Created on 16-Jan-2010
 * @copyright	Copyright (C) 2005 - 2010 Socialables.com All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

//-- No direct access
defined('_JEXEC') or die('=;)');

jimport('joomla.application.component.view');

class FriendManagersViewBackups extends JView
{
 	
	function display($tpl = null)
	{
		global $mainframe, $option;
		
		$db		=& JFactory::getDBO();
		$uri	=& JFactory::getURI();

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'a.ordering',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$search				= $mainframe->getUserStateFromRequest( $option.'search',			'search',			'',				'string' );
		$search				= JString::strtolower( $search );
		
		JToolBarHelper::title(   JText::_( 'Backups Manager' ), 'generic.png' );
		JToolBarHelper::deleteList();		
		JToolBarHelper::divider();
		JToolBarHelper::custom(  'restore', 'restore', 'restore', JText::_( 'Restore Backup' ), false );
		JToolBarHelper::back();

		// Get data from the model
	 	$items =& $this->get('Data');
	 	$total		= & $this->get( 'Total');		
	 	$pagination =& $this->get('Pagination');
	 	
		// build list of categories
		$javascript 	= 'onchange="document.adminForm.submit();"';
		//$lists['catid'] = JHTML::_('list.category',  'filter_catid', $option, intval( $filter_catid ), $javascript );

		// state filter
		$lists['state']	= JHTML::_('grid.state',  $filter_state );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		// search filter
		$lists['search']= $search;	 	
	
		// push data into the template
		$this->assignRef('lists', $lists);		
		$this->assignRef('items', $items);
		$this->assignRef('pagination', $pagination);

		parent::display($tpl);
	}//function	

}//class
