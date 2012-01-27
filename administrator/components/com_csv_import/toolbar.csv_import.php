<?php
/**
 * CSV Import Component for Content and jReviews
 * Copyright (C) 2008 NakedJoomla and Alejandro Schmeichler
 * This is not free software. Do not distribute it.
 * For license information visit http://www.nakedjoomla.com/license/csv_import_license.html
 * or contact info@nakedjoomla.com
**/

// no direct access
(defined('_VALID_MOS') OR defined('_JEXEC')) or die('Direct Access to this location is not allowed.');

require_once( $mainframe->getPath( 'toolbar_html' ) );

switch ($task) {
	case 'new':
	case 'edit':
	case 'editA':
		TOOLBAR_csv_import::_EDIT();
		break;
	case "show_step1":
		TOOLBAR_csv_import::_STEP1();	
		break;
	case "process_step1":
		TOOLBAR_csv_import::_STEP2();	
		break;	
	case "process_step2":
		TOOLBAR_csv_import::_STEP3();	
		break;		
	case "process_step3":
		TOOLBAR_csv_import::_STEP4();
		break;
	case "process_step4":
		TOOLBAR_csv_import::_RESULTS();	
		break;	
	default:
		TOOLBAR_csv_import::_DEFAULT();
		break;
}
?>