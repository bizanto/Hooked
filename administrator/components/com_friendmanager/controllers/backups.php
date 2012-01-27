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

jimport('joomla.application.component.controller');

/**
 * GiftExchange Controller
 *
 * @package    GiftExchange
 * @subpackage Controllers
 */
class FriendManagersControllerBackups extends FriendManagersController
{
    /**
     * Constructor (registers additional tasks to methods).
     */
    function __construct()
    {
        parent::__construct();
    }//function

    /**
     * Save a record (and redirect to main page).
     *
     * @return void
     */
    function restore()
    {
        $model = $this->getModel('Backup');

        if($model->restoreDB())
        {
            $msg = JText::_('Community connections table restored!');            
        }
        else
        {
            $msg = JText::_('Error Restoring Connection Table');
        }
        $this->setRedirect('index.php?option=com_friendmanager&view=backups', $msg);
    }//function

    /**
     * remove record(s)
     * @return void
     */
    function remove()
    {
        $model = $this->getModel('Backups');
        $link = 'index.php?option=com_friendmanager&view=backups';

        if($model->delete())
        {
            $msg = JText::_('Record(s) deleted');
            $this->setRedirect($link, $msg);
        }
        else
        {
            $msg = JText::_('One or more records could not be deleted');
            $this->setRedirect($link, $msg, 'error');
        }
    }//function

    /**
     * cancel editing a record
     * @return void
     */
    function cancel()
    {
        $msg = JText::_('Operation Cancelled');
        $this->setRedirect('index.php?option=com_friendmanager&view=backups', $msg);
    }//function

}//class
