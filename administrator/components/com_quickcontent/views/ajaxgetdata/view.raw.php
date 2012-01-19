<?php

/*
* Quickcontent Component for Joomla 1.5.x
* @version 1.0.1
* @Date 2009.08.04
* @copyright (C) 2009 Thomas Lengler
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* www.einszuzwei.de
*/

//this raw type document handels the ajax requests to the joomla! framework and does not render output

defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


class QuickcontentViewAjaxgetdata extends JView
{
	function display($tpl = null)
	{
	    
	    $action = JRequest::getVar('action', '');
	    
	    switch ($action) {
	        case getsections:
				header('Content-type: application/json');
	            echo $this->sectioncats();
	            break;
	        case storesection:
	            $msg = $this->storesection();
	            echo $msg;
	            break;
	        case getmenus:
	            $menus = $this->getMenus(); 
				header('Content-type: application/json');   
	            echo $menus;
	            break;
	        case storecategorie:
	        	$msg = $this->storecategorie();
	        	echo $msg;
	            break;
	        case storearticle:
	        	$msg = $this->storearticle();
	        	echo $msg;
	        	break;            
	        }
	}
	
	
	function sectioncats() {
	    
	    $model = $this->getModel();
	    $sec = $model->getSections();
	    $cat = $model->getCats();
	    $seccats['s'] = $sec;
	    $seccats['c'] = $cat; 
	    
	    $res = json_encode($seccats);
	    
	    return $res;
	    
	}
	
	function storesection() {
	    
	    $model = $this->getModel();
	    if ($model->storeSection()) {
	       $msg = "<span style='color:green'>".JText::_('Section saved!')."</span>";
	    } else {
	       $msg = "<span style='color:red'>".JText::_('Error saving section or creating menu!')."</span>"; 
	    }
	    return $msg;
	}
	
	function storecategorie() {
		$model = $this->getModel();
	    if ($model->storeCategorie()) {
	       $msg = "<span style='color:green'>".JText::_('Category saved!')."</span>";
		} else {
	       $msg = "<span style='color:red'>".JText::_('Error saving categorie or creating menu!')."</span>"; 
	    }
	    return $msg;
	}
	       
	
	function getMenus() {
	    $model = $this->getModel();
	    $menus = $model->getMenus();
	    $res = json_encode($menus);
	    
	    return $res;
	}
	
	function storearticle() {
		$model = $this->getModel();
	    if ($model->storeArticle()) {
	       $msg = "<span style='color:green'>".JText::_('Article saved!')."</span>";
	       
	    } else {
	       $msg = "<span style='color:red'>".JText::_( 'Error saving article or creating menu!' )."</span>"; 
	    }
	    return $msg;

	}
	
}	