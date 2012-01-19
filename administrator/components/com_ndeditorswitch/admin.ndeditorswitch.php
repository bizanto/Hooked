<?php
/**
* @version		$Id: admin.ndeditorswitch.php 11 2008-09-26 10:43:27Z netdream $
* @package		NDEditorSwitch
* @subpackage	Component
* @copyright	Copyright (C) 2008 Netdream - Como,Italy. All rights reserved.
* @license		GNU/GPLv2
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$document = &JFactory::getDocument();
$document->addStyleSheet( JURI::base().'components/com_ndeditorswitch/media/css/default.css' );

switch (JRequest::getWord('task'))
			{
			case 'ajaxswitch':
					ajaxSwitchEditor();
					break;
					
			case 'switch':
					switchEditor();
					break;

			default:
					dontswitchEditor();
					break;
			}
?>

<?php
function getProductVersion() {
	return "1.3";
}


function showDashboard() {	

$db			=& JFactory::getDBO();
$query = "SELECT element,name"
. " FROM #__plugins"
. " WHERE folder = 'editors'"
. " AND published = 1";
$db->setQuery($query);
$rows = $db->loadObjectList();
$user =& JFactory::getUser();
?>

<div id="extension-data">
<h2>
		<?php echo JText::_('UPDATE_YOUR_EDITOR');?>
</h2>
<div id="ndEditorFormBoxComponent" style="display:inline;">
<form action="index.php?option=com_ndeditorswitch" method="post" id="ndEditorFormComponent"  name="ndEditorForm">
<?php
foreach ($rows as $row)
{
	?>
	<input type="radio" name="editor" class="button" value="<?php echo htmlspecialchars($row->element, ENT_QUOTES, 'UTF-8');?>"
	 <?php if ($row->element == $user->getParam('editor')) { echo ' checked="checked"'; }; ?>
	><?php echo htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8');?><br />
	<?php
}
?>
<input type="submit" name="submit" class="button" value="<?php echo JText::_('UPDATE');?>">
<input type="hidden" name="task" value="switch" />
</form>
</div>
<hr />
	<h1 style="float:right;">
		<img src="components/com_ndeditorswitch/media/images/logo-200-nd.png" alt="ndEditorSwitch" />
	</h1>

	<h2>
		<?php echo JText::_('ND_SUPPORT');?>
	</h2>
	<div>
		<a href="http://joomla.netdream.it/forum" target="_new">joomla.netdream.it/forum</a>
	</div>
	<h2>
		<?php echo JText::_('ND_HOME');?>
	</h2>
	<div>
		<a href="http://joomla.netdream.it" target="_new">joomla.netdream.it</a>
	</div>
	<h2>
		<?php echo JText::_('ND_EDITORSWITCH');?>
	</h2>
	<div>
		<?php echo JText::sprintf('ND_VERSION',getProductVersion());?>
	</div>
	<h2>
		<?php echo JText::_('ND_CHANGELOG');?>
	</h2>
	<div>
		<dl>
		<dt>1.3 - 2009/04/05</dt>
		<dd>Fixed Installer for J! 1.5.10+</dd>
		<dt>1.2 - 2008/09/26</dt>
		<dd>Fixed undef variable usage in module</dd>
		<dt>1.1 - 2008/09/10</dt>
		<dd>Added AJAX support to the module</dd>
		<dd>Added "change your editor" form inside the component (you can disable the module and use just the component...)</dd>
		<dd>Added Italian language pack</dd>
		<dt>1.0 - 2008/08/31</dt>
		<dd>First released version</dd>
		</dl>
	</div>


</div>
<?php }; ?>

<?php
function dontswitchEditor() {	
	global $mainframe;
	$user = & JFactory::getUser();
	$oldEditor = $user->getParam('editor');
	$mainframe->enqueueMessage(JText::sprintf( 'CURRENT_EDITOR',$oldEditor ));
	showDashboard(); 
}; 
function doSwitchEditor($output) {
	global $mainframe;
	$user = & JFactory::getUser();
	$editor	= JRequest::getWord('editor');
	if ($editor <> '') {
		$user =& JFactory::getUser();
		$oldEditor = $user->getParam('editor');
		$user->setParam('editor',$editor);
		$user->save(true);
		if($output) { $mainframe->enqueueMessage(JText::sprintf( 'CHANGED_EDITOR',$oldEditor,$editor )); };
	} else {
		$user =& JFactory::getUser();
		$oldEditor = $user->getParam('editor');
		if($output) { $mainframe->enqueueMessage(JText::sprintf( 'CURRENT_EDITOR',$oldEditor )); };
	};
}

function ajaxSwitchEditor() {	
	doSwitchEditor(false);

	JResponse::clearHeaders();
	JResponse::setHeader('Pragma', 'public', true);
	JResponse::setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT', true);            // Date in the past
	JResponse::setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT', true);
	JResponse::setHeader('Cache-Control', 'no-store, no-cache, must-revalidate', true);   // HTTP/1.1
	JResponse::setHeader('Cache-Control: pre-check=0, post-check=0, max-age=0', true);   // HTTP/1.1
	JResponse::setHeader('Pragma', 'no-cache', true);
	JResponse::setHeader('Expires', '0', true);
	JResponse::setHeader('Content-Transfer-Encoding', 'none', true);
	JResponse::setHeader('Content-Type', 'text/xml', true); // joomla will overwrite this...
	$d = JFactory::getDocument();
	$d->setMimeEncoding('text/xml');
	JResponse::sendHeaders();

}; 

function switchEditor() {	
	doSwitchEditor(true);
	showDashboard(); 
}; 
?>