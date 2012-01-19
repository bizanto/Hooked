--
-- Database query file
-- For uninstallation
--
-- @package     Advanced Module Manager
-- @version     1.16.1
--
-- @author      Peter van Westen <peter@nonumber.nl>
-- @link        http://www.nonumber.nl
-- @copyright   Copyright © 2010 NoNumber! All Rights Reserved
-- @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
--

DELETE FROM `#__plugins` WHERE folder = 'system' AND element = 'advancedmodules';