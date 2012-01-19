 <?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>
<div class="invitation-bg">
<form name="invitation-form" id="community-invitation-form">
<div id="invitation-error"></div>
<?php
if( $displayFriends )
{
?>
	<div class="head-note"><?php echo JText::_('CC INVITE SELECT FRIENDS TIPS');?></div>
	<div id="community-invitation">
<?php
	if( !empty( $friends ) )
	{
?>

		<ul id="community-invitation-list" class="clrfix">			
<?php
		//s:foreach
		foreach( $friends as $id )
		{
			$user			= CFactory::getUser( $id );
			$invited		= in_array( $user->id , $selected );
			$selectedClass	= $invited ? ' invitation-item-invited' : '';
			$checked		= $invited ? ' checked="checked"' : '';
			$disabled		= $invited ? ' disabled="disabled"' : '';
?>
		<li id="invitation-friend-<?php echo $id;?>">
		<div class="invitation-wrap <?php echo $selectedClass;?> clrfix">
			<img src="<?php echo $user->getThumbAvatar();?>" class="invitation-avatar" />
			<div class="invitation-detail">
				
				<div class="invitation-name">
					<?php echo $user->getDisplayName();?>
				</div>
				<?php
				if( $invited )
				{
				?>
				<div><?php echo JText::_('CC INVITE INVITED');?></div>
				<?php
				}
				else
				{
				?>
				<div class="invitation-check">
					<input<?php echo $disabled;?> type="checkbox"<?php echo $checked;?> id="friend-<?php echo $user->id;?>" name="friends" value="<?php echo $user->id;?>" 
						onclick="joms.invitation.selectMember('#invitation-friend-<?php echo $user->id;?>');" 
						/>
					<label for="friend-<?php echo $user->id;?>">
						<?php echo JText::_('CC INVITE SELECTED');?>
					</label>
				</div>
				<?php
				}
				?>
			</div>
		</div>
		</li>				
<?php
		}
		//e:foreach
?>
		</ul>
<?php
	} 
	else 
	{
?>
	<div><?php echo JText::_('CC INVITE NO FRIENDS');?></div>
<?php
	}
?>		
	</div>
	
	
	<div class="invitation-option">
<?php
}
if( $displayEmail )
{
?>
		<div class="option email-container">
			<div class="textarea-label">
				<?php echo JText::_('CC INVITE BY EMAIL TIPS');?>
			</div>
			<div class="textarea-wrap">
				<textarea name="emails" id="emails"></textarea>
			</div>
		</div>
<?php
}
?>
		<div class="option invitation-message-container">
			<div class="textarea-label">
				<?php echo JText::_('CC INVITE PERSONAL MESSAGE');?>
			</div>
			<div class="textarea-wrap">
				<textarea name="message" id="message"></textarea>
			</div>
		</div>
	</div>
</form>
</div>
