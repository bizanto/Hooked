<?php
/**
 * @package		ActivityComment
 * @copyright	Copyright (C) 2009 - 2010 SocialCode. All rights reserved.
 * @license		GNU/GPL
 * ActivityComment is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses.
 */
defined('_JEXEC') or die('Restricted access');
JPlugin::loadLanguage( 'plg_activitycomment', JPATH_ADMINISTRATOR );
require_once( JPATH_PLUGINS . DS . 'community' . DS . 'activitycomment' . DS . 'helper.php' );
$current = JRequest::getInt('userid');
$task	= JRequest::getVar( 'func' );
if( $task == 'activities,ajaxGetActivities')
	return;
	
if( empty( $current) )
{
	$current	= CFactory::getRequestUser();
	$current  = $current->id;
}
$o 		= JRequest::getVar( 'option' );
$task	= JRequest::getVar( 'task' );
$params	= ActivityComments::getParams();
$view	= JRequest::getVar( 'view' , 'frontpage');

if( !$params->get( 'sharefrontpage' , 1) && $view == 'frontpage' && ($o == 'com_community' || $o == 'community') )
{
	return false;
}

if( !$params->get( 'shareprofile' , 1 ) && $view == 'profile' && ($o == 'com_community' || $o == 'community') )
{
	return false;
}

if( $my->id != 0 && $my->id == $current && ($o == 'com_community' || $o == 'community') )
{
?>
<script type="text/javascript">
joms.jQuery(document).ready( function(){
	
	if( joms.jQuery('#activity-share-note').length )
	{
		activityshareshow('message');
	}
	else
	{
		if( joms.jQuery('#activity-share-photos').length )
		{
			activityshareshow('photos');
			return;
		}
		else
		{
			if( joms.jQuery('#activity-share-links').length )
			{
				activityshareshow('links');
				return;
			}
		}
	}
});
</script>
<div class="app-box">
	<div class="app-box-content">
		<div class="activity-share-actions">
			<?php
			if( $params->get('sharemessage' , 1) )
			{
			?>
				<span class="activity-share-note-wrapper">
				<span id="share-action-note" class="activity-share-note share-selected" onclick="activityshareshow('message');"><?php echo JText::_('MSG');?></span>
				</span>
			<?php
			}
			?>
			<?php
			if( $params->get('sharephoto' , 1 ) )
			{
			?> 
				<span class="activity-share-photos-wrapper"><span id="share-action-photos" class="activity-share-photo" onclick="activityshareshow('photos');"><?php echo JText::_('PHOTOS');?></span></span>
			<?php
			}
			?>
			<?php
			if( $params->get('shareurl' , 1 ) )
			{
			?>
				<span class="activity-share-links-wrapper"><span id="share-action-links" class="activity-share-links" onclick="activityshareshow('links');"><?php echo JText::_('URL');?></span></span>
			<?php
			}
			?>
		</div>
		<div class="activity-share-content">
			<?php
			if( $params->get('sharemessage' , 1) )
			{
			?>
				<div id="activity-share-note">
					<p class="activity-share-info"><em><?php echo JText::_('SHARE MSG INFO');?></em></p>
					<table width="100%" cellpadding="5">
						<tr>
							<td width="85%" valign="top"><textarea id="share-note" style="height: 40px;"></textarea></td>
							<td align="center"><input class="button" type="button" onclick="activityAddNote('<?php echo $view;?>');" value="<?php echo JText::_('POST');?>" /></td>
						</tr>
					</table>
					<div><span id="post-message"></span></div>
				</div>
			<?php
			}
			?>
			<?php
			if( $params->get('sharephoto' , 1 ) )
			{
			?> 
				<div id="activity-share-photos">
					<p class="activity-share-info"><em><?php echo JText::_('SHARE PHOTO INFO');?></em></p>
					<div><?php echo JText::_('MSG');?></div>
					<div><textarea id="share-photo-title" style="height: 40px;width: 95%"></textarea></div>
					<div style="margin-top: 10px;">
						<?php echo JText::_('PHOTO URL');?><input type="text" id="share-photo" style="width: 70%;" /> 
						<input class="button" type="button" onclick="activityAddPhoto('<?php echo $view;?>');" value="<?php echo JText::_('POST');?>" />
					</div>
					<div style="text-align:right;"><span id="post-message"></span></div>
				</div>
			<?php
			}
			?>
			<?php
			if( $params->get('shareurl' , 1 ) )
			{
			?>
				<div id="activity-share-links">
					<p class="activity-share-info"><em><?php echo JText::_('SHARE LINK INFO');?></em></p>
					<div>
						<?php echo JText::_('URL: ');?><input type="text" id="share-url" style="width: 60%;" />
						<input type="button" class="button" onclick="showwait();" id="process-url-button" value="<?php echo JText::_('PROCESS');?>" />
						<input type="button" class="button" onclick="activityAddUrl('<?php echo $view;?>');" id="share-url-button" value="<?php echo JText::_('POST');?>" />
					</div>
					<div id="share-url-meta"></div>
					<div style="text-align:right;margin-top: 3px;">
						<span id="post-message"></span>

					</div>
				</div>
			<?php
			}
			?>			
		</div>
	</div>
</div>
<?php
}
?>
