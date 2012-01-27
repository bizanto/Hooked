<?php
/**
 * @package	JomSocial
 * @subpackage Core 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
?>
<?php if( $showProfileType ){ ?>
<div class="com-notice">
		<?php if( $multiprofile->id != COMMUNITY_DEFAULT_PROFILE ){ ?>
			<?php echo JText::sprintf('CC CURRENT PROFILE TYPE' , $multiprofile->name );?>
		<?php } else { ?>
			<?php echo JText::_('CC CURRENT DEFAULT PROFILE TYPE');?>
		<?php } ?>
		[ <a href="<?php echo CRoute::_('index.php?option=com_community&view=multiprofile&task=changeprofile');?>"><?php echo JText::_('CC CHANGE');?></a> ]
</div>
<?php } ?>
<div id="profile-edit">
<form name="jsform-profile-edit" id="frmSaveProfile" action="<?php echo CRoute::getURI(); ?>" method="POST" class="community-form-validate">
<?php if(!empty($beforeFormDisplay)){ ?>
	<table class="formtable" cellspacing="1" cellpadding="0" style="width: 98%;">
		<?php echo $beforeFormDisplay; ?>
	</table>
<?php } ?>
<?php
foreach ( $fields as $name => $fieldGroup )
{
		if ($name != 'ungrouped')
		{
?>
		<div class="ctitle">
			<h2><?php echo JText::_( $name );?></h2>
		</div>
 		
<?php
		}
?>
		<table class="formtable" cellspacing="1" cellpadding="0" style="width: 98%;">
		<tbody>
			<?php
				foreach ( $fieldGroup as $f )
				{
					$f = JArrayHelper::toObject ( $f );
					$f->value	= $this->escape( $f->value );
			?>
					<tr>
	 					<td class="key"><label id="lblfield<?php echo $f->id;?>" for="field<?php echo $f->id;?>" class="label"><?php if($f->required == 1) echo '*'; ?><?php echo JText::_( $f->name );?></label></td>	 					
	 					<td class="value"><?php echo CProfileLibrary::getFieldHTML( $f , '' ); ?></td>
	 				</tr>
	 		<?php
				}
			?>
		</tbody>
		</table>
<?php
}
?>
		<table class="formtable" cellspacing="1" cellpadding="0">
			<tr>
				<td class="key"></td>
				<td class="value"><span class="hints"><?php echo JText::_( 'CC_REG_REQUIRED_FILEDS' ); ?></span></td>
			</tr>
		</table>

<?php if(!empty($afterFormDisplay)){ ?>
	<table class="formtable" cellspacing="1" cellpadding="0" style="width: 98%;">
		<?php echo $afterFormDisplay; ?>
	</table>
<?php } ?>
		<table class="formtable" cellspacing="1" cellpadding="0" style="width: 98%;">
		<tbody>
			<tr>
			    <td class="key"></td>
			    <td class="value">
					<input type="hidden" name="action" value="save" />
                    <input class="validateSubmit button" type="submit" value="<?php echo JText::_('CC BUTTON SAVE'); ?>" />
			    </td>
			</tr>
		</tbody>
		</table>
</form>
	<script type="text/javascript">
	    cvalidate.init();
	    cvalidate.setSystemText('REM','<?php echo addslashes(JText::_("CC REQUIRED ENTRY MISSING")); ?>');
	    cvalidate.setSystemText('JOINTEXT','<?php echo addslashes(JText::_("CC AND")); ?>');
	</script>
</div>