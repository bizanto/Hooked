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

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the FriendManager Component
 *
 * @package    FriendManager
 * @subpackage Views
 */

class FriendManagersViewFriends extends JView
{
	/**
	 * FriendManagers view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		global $mainframe, $option;
		//$filter_state		= $mainframe->getUserStateFromRequest( $option.'filter_state',		'filter_state',		'',				'word' );
		JToolBarHelper::title(   JText::_( 'Friends Manager' ), 'generic.png' );
		JToolBarHelper::deleteList();
		JToolBarHelper::back();
		//JToolBarHelper::editListX();
		//JToolBarHelper::addNewX();

		// Get data from the model
	 	$items =& $this->get('Data');	
	 	$pagination =& $this->get('Pagination');
	
		// push data into the template
		$this->assignRef('items', $items);
		$this->assignRef('pagination', $pagination);

		parent::display($tpl);
	}//function

}//class