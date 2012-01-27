<?php
/**
 * @package		ActivityComment
 * @copyright	Copyright (C) 2009 - 2010 SocialCode. All rights reserved.
 * @license		GNU/GPL
 * JSUsernames is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses.
 */
defined('_JEXEC') or die('Restricted access');
require_once( JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_activitycomment'.DS.'helper.php');

$method = JRequest::getMethod();
JToolBarHelper::title( JText::_( 'Activity comments' )  , 'categories.png' );
if(_BUILD < 206 )
{
	echo '<h2>This plugin only works with build 206 onwards!</h2>';
	return;
}

if( $method == 'POST' )
{
	$mainframe=&JFactory::getApplication();
	$action = JRequest::getVar('action', false , 'REQUEST');
	
	switch($action)
	{
		case 'patch':
			$tmpl	= JRequest::getVar( 'sitetemplate' , '1' , 'REQUEST' );
			
			if($tmpl=='1')
			{
				echo 'Invalid template';
			}
			else
			{
				if(ActivityCommentHelper::patch( $tmpl ))
				{
					$mainframe->redirect('index.php?option=com_activitycomment','Patch success');
				}
			}
			break;
		case 'revert':
			$tmpl	= JRequest::getVar( 'sitetemplate' , '1' , 'REQUEST' );
			
			if($tmpl=='1')
			{
				echo 'Invalid template';
			}
			else
			{
				if(ActivityCommentHelper::restore( $tmpl ))
				{
					$mainframe->redirect('index.php?option=com_activitycomment','Restore success');
				}
			}
		break;
		default:
			echo 'unknown process';
			break;
	}
}
$templates = ActivityCommentHelper::getTemplates();
$patchedTemplates = ActivityCommentHelper::getPatchedTemplates();
?>
<h2>Please remember to patch your current template in order for activity comments to work!</h2>
<p style="font-weight:bold;">If you are using a template that is not listed here, you need to add the codes <a href="<?php echo JURI::root() . 'administrator/components/com_activitycomment/patch.txt';?>" target="_blank">HERE</a> into the activity.index.php file in your template</p>
<form name="adminForm" action="index.php?option=com_activitycomment" method="post">
<fieldset style="width: 300px;">
	<legend>File patcher</legend>
	<p>Patch jomsocial template so that plugin will appear in the activities.</p>
	<select name="sitetemplate">
		<option selected="true" value="1">Select your template</option>
	<?php
		foreach($templates as $template)
		{
	?>
		<option value="<?php echo $template;?>"><?php echo $template;?></option>
	<?php
		}
	?>
	</select>
	<input type="hidden" name="action" value="patch" />
	<input type="submit" value="Patch!" />
</fieldset>
</form>

<form name="adminForm" action="index.php?option=com_activitycomment&action=revert" method="post">
<fieldset style="width: 300px;">
	<legend>Revert patched templates</legend>
	<p>If you want to revert the patch, proceed here. Restoring the original patch file will cause the plugin to not work</p>
	<select name="sitetemplate">
		<option selected="true" value="1">Select your template</option>
	<?php
		foreach($patchedTemplates as $template)
		{
	?>
		<option value="<?php echo $template;?>"><?php echo $template;?></option>
	<?php
		}
	?>
	</select>
	<input type="hidden" name="action" value="revert" />
	<input type="submit" value="Restore!" />
</fieldset>
</form>