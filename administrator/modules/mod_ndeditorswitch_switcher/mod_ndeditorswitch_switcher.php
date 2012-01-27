<?php
/**
* @version		$Id: mod_ndeditorswitch_switcher.php 9 2008-09-26 10:30:11Z netdream $
* @package		NDEditorSwitch
* @subpackage	AdminModule
* @copyright	Copyright (C) 2008 Netdream - Como,Italy. All rights reserved.
* @license		GNU/GPLv2
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$db			=& JFactory::getDBO();
$query = "SELECT element,name"
. " FROM #__plugins"
. " WHERE folder = 'editors'"
. " AND published = 1";
$db->setQuery($query);
$rows = $db->loadObjectList();
$user =& JFactory::getUser();

$script='
<script type="text/javascript">'."
	window.addEvent('domready', function(){
	  var nd_edsw=new NDEditorSwitchPlugin({submiturl: '".JURI::base()."index.php?option=com_ndeditorswitch'});
	});".
'</script>
<script type="text/javascript" src="'.JURI::base().'components/com_ndeditorswitch/js/ndeditorswitchplugin.js"></script>';		

if(!isset($addScriptNDEditorSwitchPlugin)){	
	$addScriptNDEditorSwitchPlugin = 1;
	JApplication::addCustomHeadTag($script);
}
?>
<div id="ndEditorFormBox" style="display:inline;">
<form action="index.php?option=com_ndeditorswitch" method="post" id="ndEditorForm"  name="ndEditorForm" style="display:inline;">
<select name="editor" id="ndeditor_assigned" class="inputbox" size="1" style="background-color:#ffffdd;margin-top:3px;margin-right:3px;">
<option value="">- <?php echo JText::_('CHANGE_EDITOR');?> -</option>
<?php
foreach ($rows as $row)
{
	?>
	<option value="<?php echo htmlspecialchars($row->element, ENT_QUOTES, 'UTF-8');?>"
	 <?php if ($row->element == $user->getParam('editor')) { echo ' selected="selected"'; }; ?>
	><?php echo htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8');?></option>
	<?php
}
?>
</select>
<input type="hidden" name="task" value="switch" />
</form>
</div>