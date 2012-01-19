<?php

/*
* Quickcontent Component for Joomla 1.5.x
* @version 1.0.2
* @Date 2009.08.25
* @copyright (C) 2009 Thomas Lengler
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* www.einszuzwei.de
*/


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class QuickcontentModelAjaxgetdata extends JModel
{
	
	var $g_tree = array();
	var $ret_tree = array();
	    	
	function __construct()
	{
		parent::__construct();
		
		$params = JComponentHelper::getParams('com_quickcontent');
		$this->paramenu = $params->get('paraformenu');

	}

	
	function getSections() {
	    $query = "SELECT id, title FROM #__sections ORDER BY title";
	    $this->_db->setQuery($query);
	    $rows = $this->_db->loadObjectList();
	    return $rows;
	}
	
	function getCats() {
	    $query = "SELECT id, title, section FROM #__categories WHERE section REGEXP ('[0-9]') ORDER BY title";
	    $this->_db->setQuery($query);
	    $rows = $this->_db->loadObjectList();
	    return $rows;
	}
	
	function getMenus() {
		
	    $query = "SELECT id, menutype FROM #__menu_types";
	    $this->_db->setQuery($query);
	    $menutypes = $this->_db->loadObjectList();
	    
	    $query2 = "SELECT id, menutype, name, parent, sublevel FROM #__menu ORDER BY menutype";
	    $this->_db->setQuery($query2);
	    $menuitems = $this->_db->loadAssocList();
	    
		$this->g_tree = $menuitems;
		$this->tree(0,0);
		
				
	    $menus = array();
	    $menus['types'] = $menutypes;
	    $menus['items'] = $this->ret_tree;
	    return $menus;
	}


	/*
	 * recursive function to build tree for html select field menu item parent 
	 */
	function tree($id_parent = 0, $indent = 0)
    {
        $tt = array();
        $x = 0;
        $loop = 0;

        foreach($this->g_tree as $n)
        {         
            if( $n['parent'] == $id_parent)
            {
                $tt[$x++] = $loop;  
            }  
            $loop++;
        }
        /*-- if there are some parent ids --*/
        if( $x != 0){             
            foreach($tt as $d)
            {
                $tmp = array();
                
                foreach($this->g_tree[$d] as $key => $value)
                {                
                    $tmp[$key] = $value; 
                }
                $tmp['indent'] = $indent;

                $this->ret_tree[] = $tmp;
                
                $this->tree($tmp['id'], $indent+1);
            }
        }
        else
        {
            return;
        }
    }

	
	function storeSection() {
	    
            global $mainframe;
            $post = JRequest::get('post');

        	$row =& JTable::getInstance('section');
        	
			if (!$row->bind($post)) {
        		return false;
        	}
        	
        	if (!$row->check()) {
        		return false;
        	}
        	
        	// if new item order last in appropriate group
        	if (!$row->id) {
        		$row->ordering = $row->getNextOrder();
        	}
        
        	if (!$row->store()) {
        		return false;
        	}
        	
			// create menu item when link to menu option 
        	if (JRequest::getInt('linkmenu') == 1 && JRequest::getVar('mt') != "") {
        		
        		$lastId = $row->id;
        		
				//get data in new array
        		$postmenu = array();
        		$postmenu['parent'] = JRequest::getVar('mi');
				
				if ($postmenu['parent'] > 0) {
					$query	= 'SELECT sublevel FROM #__menu WHERE id = '.(int) $postmenu['parent'];
					$this->_db->setQuery( $query );
					$sublevel = $this->_db->loadResult() + 1;
				}
				
				$postmenu['sublevel'] = $sublevel;
				
        		$postmenu['menutype'] = JRequest::getVar('mt');
        		//set section name if no menu name
        		$postmenu['name'] = JRequest::getVar('menuname') == "" ? $post = JRequest::getVar('title') : JRequest::getVar('menuname');
        		if ($this->paramenu == 1) {
					$postmenu['published'] = JRequest::getVar('published');
        			$postmenu['access'] = JRequest::getVar('access');
				} else {
					$postmenu['published'] = 0;
        			$postmenu['access'] = 0;
				}
        		if (JRequest::getVar('bloglayout') == 1) {
        			$postmenu['link'] = 'index.php?option=com_content&view=section&layout=blog&id='.$lastId;
        	    } else {
        	    	$postmenu['link'] = 'index.php?option=com_content&view=section&id='.$lastId;
        	    }
        	    
        	    //get id from component content
        	    $query = "SELECT id FROM #__components WHERE `option` = 'com_content' LIMIT 1 ";
        	    $this->_db->setQuery($query);
        	    $contid = $this->_db->loadObject();
        	    
        	    $postmenu['componentid'] = $contid->id;
        	    $postmenu['type'] = 'component';
        	            	    
	        	$rowm =& JTable::getInstance('menu');
	        	if (!$rowm->bind($postmenu)) {
	        		return false;
	        	}
	        	
	        	if (!$rowm->check()) {
	        		return false;
	        	}
	        	
	        	// if new item order last in appropriate group
	        	if (!$rowm->id) {
	        		$where = "menutype = " . $this->_db->Quote($postmenu['menutype']) . " AND published >= 0 AND parent = ".(int) $postmenu['parent'];
					$rowm->ordering = $rowm->getNextOrder( $where );
	        	}
	        
	        	if (!$rowm->store()) {
	        		return false;
	        	}
           	}   
		return true;
	
	}
	
	
	
	function storeCategorie() {
		
		global $mainframe;
            $post = JRequest::get('post');

        	$row =& JTable::getInstance('category');
        	
			if (!$row->bind($post)) {
        		return false;
        	}
        	
        	if (!$row->check()) {
	        	return false;
        	}
        	
        	// if new item order last in appropriate group
        	if (!$row->id) {
        		$row->ordering = $row->getNextOrder();
        	}
        
        	if (!$row->store()) {
        		return false;
        	}
        	
			// create menu item when link to menu option  
        	if (JRequest::getInt('linkmenu') == 1 && JRequest::getVar('mt') != "") {
        		
        		$lastId = $row->id;
        		
        		$postmenu = array();
        		$postmenu['parent'] = JRequest::getVar('mi');
        		
				if ($postmenu['parent'] > 0) {
					$query	= 'SELECT sublevel FROM #__menu WHERE id = '.(int) $postmenu['parent'];
					$this->_db->setQuery( $query );
					$sublevel = $this->_db->loadResult() + 1;
				}
				
				$postmenu['sublevel'] = $sublevel;
				
				$postmenu['menutype'] = JRequest::getVar('mt');
        		//set section name if no menu name
        		$postmenu['name'] = JRequest::getVar('menuname') == "" ? $post = JRequest::getVar('title') : JRequest::getVar('menuname');
        		
				if ($this->paramenu == 1) {
					$postmenu['published'] = JRequest::getVar('published');
        			$postmenu['access'] = JRequest::getVar('access');
				} else {
					$postmenu['published'] = 0;
        			$postmenu['access'] = 0;
				}
				
        		
        		if (JRequest::getVar('bloglayout') == 1) {
        			$postmenu['link'] = 'index.php?option=com_content&view=category&layout=blog&id='.$lastId;
        	    } else {
        	    	$postmenu['link'] = 'index.php?option=com_content&view=category&id='.$lastId;
        	    }
        	    
        	    //get id from component content
        	    $query = "SELECT id FROM #__components WHERE `option` = 'com_content' LIMIT 1 ";
        	    $this->_db->setQuery($query);
        	    $contid = $this->_db->loadObject();
        	    
        	    $postmenu['componentid'] = $contid->id;
        	    $postmenu['type'] = 'component';
        	    
        	    
	        	$rowm =& JTable::getInstance('menu');
	        	if (!$rowm->bind($postmenu)) {
	        		return false;
	        	}
	        	
	        	if (!$rowm->check()) {
	        		return false;
	        	}
	        	
	        	// if new item order last in appropriate group
	        	if (!$rowm->id) {
	        		$where = "menutype = " . $this->_db->Quote($postmenu['menutype']) . " AND published >= 0 AND parent = ".(int) $postmenu['parent'];
					$rowm->ordering = $rowm->getNextOrder( $where );
	        	}
	        
	        	if (!$rowm->store()) {
	        		return false;
	        	}
           	}   

		return true;
		
	}
	
	
	function storeArticle() {
		
		global $mainframe;
        
		$db		= & JFactory::getDBO();
		$user	= & JFactory::getUser();
		
		$row = & JTable::getInstance('content');
		
		if (!$row->bind(JRequest::get('post'))) {
			return false;
		}
	
		$row->created_by = $row->created_by ? $row->created_by : $user->get('id');

		if ($row->created && strlen(trim( $row->created )) <= 10) {
			$row->created 	.= ' 00:00:00';
		}

		$config =& JFactory::getConfig();
		$tzoffset = $config->getValue('config.offset');
		$date =& JFactory::getDate($row->created, $tzoffset);
		$row->created = $date->toMySQL();

		// Append time if not added to publish date
		if (strlen(trim($row->publish_up)) <= 10) {
			$row->publish_up .= ' 00:00:00';
		}

		$date =& JFactory::getDate($row->publish_up, $tzoffset);
		$row->publish_up = $date->toMySQL();

		// Handle never unpublish date
		if (trim($row->publish_down) == JText::_('Never') || trim( $row->publish_down ) == '')
		{
			$row->publish_down = $nullDate;
		}
		else
		{
			if (strlen(trim( $row->publish_down )) <= 10) {
				$row->publish_down .= ' 00:00:00';
			}
			$date =& JFactory::getDate($row->publish_down, $tzoffset);
			$row->publish_down = $date->toMySQL();
		}

		// Get a state and parameter variables from the request
		$row->state	= JRequest::getVar( 'state', 0, '', 'int' );
		
		// Get submitted text from the request variables
		$text = JRequest::getVar( 'text', '', 'post', 'string', JREQUEST_ALLOWRAW );

		// Clean text for xhtml transitional compliance
		$text		= str_replace( '<br>', '<br />', $text );

		// Search for the {readmore} tag and split the text up accordingly.
		$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
		$tagPos	= preg_match($pattern, $text);

		if ( $tagPos == 0 )
		{
			$row->introtext	= $text;
		} else
		{
			list($row->introtext, $row->fulltext) = preg_split($pattern, $text, 2);
		}

		// Filter settings
		jimport( 'joomla.application.component.helper' );
		$config	= JComponentHelper::getParams( 'com_content' );
		$user	= &JFactory::getUser();
		$gid	= $user->get( 'gid' );

		$filterGroups	=  $config->get( 'filter_groups' );
		
		// convert to array if one group selected
		if ( (!is_array($filterGroups) && (int) $filterGroups > 0) ) { 
			$filterGroups = array($filterGroups);
		}

		if (is_array($filterGroups) && in_array( $gid, $filterGroups ))
		{
			$filterType		= $config->get( 'filter_type' );
			$filterTags		= preg_split( '#[,\s]+#', trim( $config->get( 'filter_tags' ) ) );
			$filterAttrs	= preg_split( '#[,\s]+#', trim( $config->get( 'filter_attritbutes' ) ) );
			switch ($filterType)
			{
				case 'NH':
					$filter	= new JFilterInput();
					break;
				case 'WL':
					$filter	= new JFilterInput( $filterTags, $filterAttrs, 0, 0, 0);  // turn off xss auto clean
					break;
				case 'BL':
				default:
					$filter	= new JFilterInput( $filterTags, $filterAttrs, 1, 1 );
					break;
			}
			$row->introtext	= $filter->clean( $row->introtext );
			$row->fulltext	= $filter->clean( $row->fulltext );
		} elseif(empty($filterGroups) && $gid != '25') { // no default filtering for super admin (gid=25)
			$filter = new JFilterInput( array(), array(), 1, 1 );
			$row->introtext	= $filter->clean( $row->introtext );
			$row->fulltext	= $filter->clean( $row->fulltext );
		}
		

		// Make sure the data is valid
		if (!$row->check()) {
			return false;
		}

		// Increment the content version number
		$row->version++;

		// Store the content to the database
		if (!$row->store()) {
			return false;
		}

		// Check the article and update item order
		$row->checkin();
		$row->reorder('catid = '.(int) $row->catid.' AND state >= 0');


		require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_frontpage'.DS.'tables'.DS.'frontpage.php');
		$fp = new TableFrontPage($db);

		// Is the article viewable on the frontpage?
		if (JRequest::getVar( 'frontpage', 0, '', 'int' ))
		{
			// Is the item already viewable on the frontpage?
			if (!$fp->load($row->id))
			{
				// Insert the new entry
				$query = 'INSERT INTO #__content_frontpage' .
						' VALUES ( '. (int) $row->id .', 1 )';
				$db->setQuery($query);
				if (!$db->query())
				{
					JError::raiseError( 500, $db->stderr() );
					return false;
				}
				$fp->ordering = 1;
			}
		}
		
		$fp->reorder();

		$cache = & JFactory::getCache('com_content');
		$cache->clean();
		
		
		//create menu item when link to menu option  
		
		if (JRequest::getInt('linkmenu') == 1 && JRequest::getVar('mt') != "") {
        		
        		$lastId = $row->id;
        		
        		$postmenu = array();
        		$postmenu['parent'] = JRequest::getVar('mi');
        		
				if ($postmenu['parent'] > 0) {
					$query	= 'SELECT sublevel FROM #__menu WHERE id = '.(int) $postmenu['parent'];
					$this->_db->setQuery( $query );
					$sublevel = $this->_db->loadResult() + 1;
				}
				
				$postmenu['sublevel'] = $sublevel;
				
				$postmenu['menutype'] = JRequest::getVar('mt');
        		//set section name if no menu name
        		$postmenu['name'] = JRequest::getVar('menuname') == "" ? $post = JRequest::getVar('title') : JRequest::getVar('menuname');
        		
				if ($this->paramenu == 1) {
					$postmenu['published'] = JRequest::getVar('state');
        			$postmenu['access'] = JRequest::getVar('access');
				} else {
					$postmenu['published'] = 0;
        			$postmenu['access'] = 0;
				}
				
        		$postmenu['link'] = 'index.php?option=com_content&view=article&id='.$lastId;
        	    
        	    
        	    //get id from component content
        	    $query = "SELECT id FROM #__components WHERE `option` = 'com_content' LIMIT 1 ";
        	    $this->_db->setQuery($query);
        	    $contid = $this->_db->loadObject();
        	    
        	    $postmenu['componentid'] = $contid->id;
        	    $postmenu['type'] = 'component';
        	    
        	    
	        	$rowm =& JTable::getInstance('menu');
	        	if (!$rowm->bind($postmenu)) {
	        		return false;
	        	}
	        	
	        	if (!$rowm->check()) {
	        		return false;
	        	}
	        	
	        	// if new item order last in appropriate group
	        	if (!$rowm->id) {
	        		$where = "menutype = " . $this->_db->Quote($postmenu['menutype']) . " AND published >= 0 AND parent = ".(int) $postmenu['parent'];
					$rowm->ordering = $rowm->getNextOrder( $where );
	        	}
	        
	        	if (!$rowm->store()) {
	        		return false;
	        	}
        	}
	
		return true;	
		
	}
	
	

}