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

class TOOLBAR_csv_import {
	function _STEP1() {
		global $id;
		mosMenuBar::startTable();		
		mosMenuBar::custom('','cancel.png','cancel.png','Cancel',false);
		mosMenuBar::spacer();
		mosMenuBar::custom('process_step1','forward.png','forward.png','Next',false);		
		mosMenuBar::endTable();
	}
	
	function _STEP2() {
		global $id;
		mosMenuBar::startTable();		
		mosMenuBar::custom('','cancel.png','cancel.png','Cancel',false);
		mosMenuBar::spacer();
		mosMenuBar::custom('process_step2','forward.png','forward.png','Next',false);		
		mosMenuBar::endTable();
	}
	
	
	function _STEP3() {
		global $id;
		mosMenuBar::startTable();		
		mosMenuBar::custom('','cancel.png','cancel.png','Cancel',false);
		mosMenuBar::spacer();
		mosMenuBar::custom('process_step3','forward.png','forward.png','Next',false);		
		mosMenuBar::endTable();
	}
	
	
	function _STEP4() {
		global $id;
		mosMenuBar::startTable();		
		mosMenuBar::custom('','cancel.png','cancel.png','Cancel',false);
		mosMenuBar::spacer();
		mosMenuBar::custom('process_step4','forward.png','forward.png','Next',false);		
		mosMenuBar::endTable();
	}
	
	function _RESULTS() {
		mosMenuBar::startTable();
		mosMenuBar::spacer();
		mosMenuBar::addNew('show_step1');
		mosMenuBar::spacer();		
		mosMenuBar::custom('','config.png','config.png','Home',false);
		mosMenuBar::endTable();
	}	
	
	function _DEFAULT() {
		mosMenuBar::startTable();
		mosMenuBar::spacer();
		mosMenuBar::addNew('show_step1');
		mosMenuBar::spacer();		
		mosMenuBar::deleteList();		
		mosMenuBar::endTable();
	}
}
?>