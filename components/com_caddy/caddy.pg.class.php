<?php
/**
* @package SimpleCaddy 1.75 for Joomla 1.5
* @copyright Copyright (C) 2006-2011 Henk von Pickartz. All rights reserved.
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class scphocag {
    var $redirect;
    var $message="";
    
    function show() {
        display::header();
        pgdisplay::main();
    }
    
    function redirect() {
        $mainframe=JFactory::getApplication();
        if($this->redirect)
        $mainframe->redirect("$this->redirect", "$this->message");
    }
    
    function getPGCategories(){
    	$db	=& JFactory::getDBO();
    	$query="select `id`, `title` from #__phocagallery_categories order by `title` ASC";
    	$db->setQuery($query);
    	$lst=$db->loadObjectList();
        return $lst;
    }

    
    function showaddsctopgcatid() {
        $catlist=$this->getPGCategories();
        $prodcodes=new products();
        $prodcodelist=$prodcodes->getProductCodeList();
        display::header();
        pgdisplay::addtocat($prodcodelist, $catlist);
    }

    function showaddsctoall() {
        $prodcodes=new products();
        $prodcodelist=$prodcodes->getProductCodeList();
        display::header();
        pgdisplay::addtoall($prodcodelist);
    }

    function showremscfrompgcatid() {
        $catlist=$this->getPGCategories();
        $prodcodes=new products();
        $prodcodelist=$prodcodes->getProductCodeList();
        display::header();
        pgdisplay::remfromcat($prodcodelist, $catlist);
    }

    function addsctopgcatid() {
        $catid=JRequest::getVar("pgcatid");
        $prodcode=JRequest::getVar("prodcode");
        $msg = "Adding plugin code to category: $catid";
        $phocaimg=new scphocaimg();
        $lst=$phocaimg->getImagesByCategory($catid);
        $img .= " Images treated: ".count($lst);
        foreach ($lst as $pgimg) {
            $phimg=new scphocaimg();
            $phimg->load($pgimg->id);
			$filename = basename($phimg->filename);
            
			$description = $phimg->description;
			$code = "{simplecaddy code=$prodcode picname=$filename classsfx=-pg}";
			$phimg->description = $code . "\r\n" . $description;
			$phimg->store();
        }
        $this->redirect="index.php?option=com_caddy&action=scphocag";
        $this->message="$msg";
    }
    
    function addsctoall() {
        $prodcode=JRequest::getVar("prodcode");
        $msg = "Adding plugin code to all images";
        $phocaimg=new scphocaimg();
        $lst=$phocaimg->getImages();
        $msg .= " Images treated: ".count($lst);
        foreach ($lst as $pgimg) {
            $phimg=new scphocaimg();
            $phimg->load($pgimg->id);
			$filename = basename($phimg->filename);
			$description = $phimg->description;
			$code = "{simplecaddy code=$prodcode picname=$filename classsfx=-pg}";
			$phimg->description = $code . "\r\n" . $description;
			$phimg->store();
        }
        $this->redirect="index.php?option=com_caddy&action=scphocag";
        $this->message="$msg";
    }
    
    function remscfrompgcatid() {
        $catid=JRequest::getVar("pgcatid");
        $msg = "Removing plugin code from category: $catid";
        $phocaimg=new scphocaimg();
        $lst=$phocaimg->getImagesByCategory($catid);
        $msg .= " Images treated: ".count($lst);
        foreach ($lst as $pgimg) {
            $phimg=new scphocaimg();
            $phimg->load($pgimg->id);
			$filename = $phimg->filename;
			$description = $phimg->description;
            $description= preg_replace('/\{simplecaddy(.+)\}/', '', $description);
			$phimg->description = $description;
			$phimg->store();
        }
        $this->redirect="index.php?option=com_caddy&action=scphocag";
        $this->message="$msg";
    }

    function remscfromall() {
        $msg = "Removing plugin code from all images";
        $phocaimg=new scphocaimg();
        $lst=$phocaimg->getImages($catid);
        $msg .= " Images treated: ".count($lst);
        foreach ($lst as $pgimg) {
            $phimg=new scphocaimg();
            $phimg->load($pgimg->id);
			$filename = $phimg->filename;
			$description = $phimg->description;
            $description= preg_replace('/\{simplecaddy(.+)\}/', '', $description);
			$phimg->description = $description;
			$phimg->store();
        }
        $this->redirect="index.php?option=com_caddy&action=scphocag";
        $this->message="$msg";
    }
}

/**
 * This is a simple clone of the Phoca Gallery table class
 * For the purpose this is easier to work with
 * Obviously better/MVC methods exist
 */
class scphocaimg extends JTable {
    var $id;
    var $catid;
    var $sid;
    var $title;
    var $alias;
    var $filename;
    var $description;
    var $date;
    var $hits;
    var $exto;
    var $exth;
    var $extw;
    var $exts;
    var $extm;
    var $extl;
    var $extid;
    var $imgorigsize;
    var $vmproductid;
    var $videocode;
    var $latitude;
    var $longitude;
    var $zoom;
    var $geotitle;
    var $published;
    var $approved;
    var $checked_out;
    var $checked_out_time;
    var $ordering;
    var $params;
    var $metadesc;
    var $metakey;
    var $extlink1;
    var $extlink2;


	function scphocaimg() {
		$db	=& JFactory::getDBO();
	   	$this->__construct( '#__phocagallery', 'id', $db );
	}

    function getImagesByCategory($catid) {
    	$db	=& JFactory::getDBO();
    	$query="select * from {$this->_tbl} where `catid`='$catid'";
    	$db->setQuery($query);
    	$lst=$db->loadObjectList();
        return $lst;
    }
    
    function getImages($catid) {
    	$db	=& JFactory::getDBO();
    	$query="select * from {$this->_tbl}";
    	$db->setQuery($query);
    	$lst=$db->loadObjectList();
        return $lst;
    }
    
}

?>