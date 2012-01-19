<?php
defined('_JEXEC') or die ('Restricted Access');

require_once(JApplicationHelper::getPath('admin_html'));
JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

switch($task)
{
	case 'edit':
		editContent($option);
		break;
	case 'apply':
	case 'save':
		saveContent($option,$task);
		break;
	default:
		showContent($option);
		break;
}


function _displayError($row)
{
	echo "<script type=\"text/javascript\"> alert('".$row->getError()."'); window.history.go(-1);</script>\n";
	exit();
}

function editContent($option)
{
	$row =& JTable::getInstance('saasycontent','Table');
	$cid = JRequest::getVar('cid',array(0),'','array');
	$id = $cid[0];
	$row->load($id);

	$db = JFactory::getDBO();

	HTML_saasy::editContent($row,$option);
}

function saveContent($option,$task)
{
	global $mainframe;

	$row =& JTable::getInstance('saasycontent','Table');

	if(!$row->bind(JRequest::get('post')))
		_displayError($row);

	$row->content = JRequest::getVar('content','','post','string',JREQUEST_ALLOWRAW);	
		
	if(!$row->store())
		_displayError($row);

	switch($task)
	{
		case 'apply':
			$msg = 'Changes to Content Saved';
			$link = "index.php?option={$option}&task=edit&cid[]={$row->id}";
			break;
		case 'save':
		default:
			$msg = 'Content Saved';
			$link = "index.php?option={$option}";
			break;
	}

	$mainframe->redirect($link,$msg);
}

function showContent($option)
{
	global $mainframe;


	$db =& JFactory::getDBO();
	$query = "select * from #__saasy_content order by title asc";
	$db->setQuery($query);
	$rows = $db->loadObjectList();

	if($db->getErrorNum())
	{
		echo $db->stderr();
		return false;
	}


	HTML_saasy::showContent($option,$rows);
}

?>
