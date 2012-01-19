<?php
defined('_JEXEC') or die ('Restricted Access');

class TableSaasyContent extends JTable
{
	var $id = null;
	var $title = null;
	var $content = null;
	
	function __construct(&$db)
	{
		parent::__construct('#__saasy_content','id',$db);
	}
}

?>
