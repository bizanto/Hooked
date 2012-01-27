<?php
/**
 * @category	Module
 * @package		JomSocial
 * @subpackage	HelloMe
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');
require_once( JPATH_BASE . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');
CFactory::load( 'libraries' , 'userpoints' );
CFactory::load( 'libraries' , 'window' );
CFactory::load( 'helpers' , 'owner' );
CFactory::load( 'libraries' , 'facebook' );
CWindow::load();

$config	= CFactory::getConfig();
$my		= CFactory::getUser();
$js		= 'assets/script-1.2';
$js		.= ( $config->getBool('usepackedjavascript') ) ? '.pack.js' : '.js';
CAssets::attach($js, 'js');


require(JModuleHelper::getLayoutPath('mod_hellome'));