<?php
    if($_SERVER['REMOTE_ADDR']!=$_SERVER['SERVER_ADDR']) exit;
    define( '_JEXEC', 1 );
    define('JPATH_BASE', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
    define( 'DS', DIRECTORY_SEPARATOR );

    $results = array();

    require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
    require( JPATH_LIBRARIES .DS.'joomla'.DS.'import.php');
    require( JPATH_LIBRARIES .DS.'joomla'.DS.'user'.DS.'user.php');
    require( JPATH_LIBRARIES .DS.'joomla'.DS.'language'.DS.'language.php');
    require( JPATH_LIBRARIES .DS.'joomla'.DS.'environment'.DS.'uri.php');
    require( JPATH_LIBRARIES .DS.'joomla'.DS.'event'.DS.'dispatcher.php');
    require( JPATH_LIBRARIES .DS.'joomla'.DS.'utilities'.DS.'string.php');
    require( JPATH_LIBRARIES .DS.'joomla'.DS.'application'.DS.'menu.php');

    $mainframe =& JFactory::getApplication('site');
    $mainframe->initialise();

    $plugins = JPluginHelper::_load();
    $total = count($plugins);
    for($i = 0; $i < $total; $i++) {
        if($plugins[$i]->type == 'system' && in_array($plugins[$i]->name,array('sef','sef_advance')))
        {
            JPluginHelper::_import( $plugins[$i] );
        }
    }

    $mainframe->triggerEvent('onAfterInitialise');

    foreach($_POST['pages'] AS $url)
    {
        $url = urldecode($url);
        $results[] = JRoute::_($url);
    }
    print_r($results);
    unset($mainframe);        
    return $results;
?>
