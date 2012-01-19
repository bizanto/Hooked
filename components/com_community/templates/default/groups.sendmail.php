<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @param	$group		CTableGroup object
 * @param	$message	String from post
 * @param	$title		String from post
 * @param	$editor		JEditor object    
 */
defined('_JEXEC') or die(); 
?>
<!--FORM-->
<form name="jsform-groups-sendmail" action="<?php echo CRoute::getURI();?>" method="post" class="event-email">
	<!--INSTRUCTION-->
	<div class="instruction"><?php echo JText::sprintf('CC GROUP SEND EMAIL TO MEMBERS DESCRIPTION', $group->getMembersCount() );?></div>
	<!--INSTRUCTION-->
	<!--EMAIL TITLE-->
	<label>*<?php echo JText::_('CC TITLE'); ?>:</label>
	<div class="event-email-row"><input type="text" name="title" value="<?php echo $this->escape($title);?>" class="required" /></div>
	
	<!--EMAIL MESSAGE-->
	<label><?php echo JText::_('CC MESSAGE'); ?>:</label>
	<div class="event-email-row"><?php echo $editor->display( 'message',  $message , '98%', '450', '10', '20' , false ); ?></div>
	
	<div class="event-email-row"><span class="hints"><?php echo JText::_( 'CC_REG_REQUIRED_FILEDS' ); ?></span></div>
	
	<!--SUBMIT BUTTON-->
	<input type="submit" class="button" value="<?php echo JText::_('CC SEND'); ?>">
	<input type="hidden" name="groupid" value="<?php echo $group->id;?>">
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<!--FORM-->