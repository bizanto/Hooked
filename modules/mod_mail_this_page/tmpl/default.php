<?php
/**
* 
* 
* @copyright	Inspiration Web Design
* License GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div class="mail_this_page hide <?php echo $params->get('moduleclass_sfx'); ?>">
<h3><?php echo JText::_("EMAILTOFRIEND"); ?></h3>
<div class="clear"></div>
<?php // if ($lists->error){ echo $lists->error_message; } else { echo $lists->success_message;} ?>
<div id="mail_this_link_form">
<form method="post" action="" id="mtp_form" onsubmit="return mtp_validateForm();">
 <div class="width50 fl"><label for="mtp_user_name"><?php echo JText::_("YOURNAME"); ?></label>
 <input type="text" name="mtp_user_name" id="mtp_user_name" size="12" maxlength="100" value="" />
 </div>
 
 <div class="width50 fl"><label for="mtp_user_email"><?php echo JText::_("YOUREMAIL"); ?></label>
 <input type="text" name="mtp_user_email" id="mtp_user_email" size="12" maxlength="100" value="" />
 </div>
 
 <div class="width50 fl"><label for="mtp_friend_name"><?php echo JText::_("FRIENDSNAME"); ?></label>
 <input type="text" name="mtp_friend_name" id="mtp_friend_name" size="12" maxlength="100" value="" />
 </div>
 
 <div class="width50 fl"><label for="mtp_friend_email"><?php echo JText::_("FRIENDSEMAIL"); ?></label>
 <input type="text" name="mtp_friend_email" id="mtp_friend_email" size="12" maxlength="100" value="" />
 </div>
 <div class=" clear"></div>
 <div class="tar">
 <input type="hidden" name="mtp_ident_form" id="ident_form" value="mtp" />
 <?php echo $lists->token; ?>
 <input type="submit" value="<?php echo $lists->submit; ?>" />
 <input type="reset" value="Avbryt" id="cancelfriend" />
 </div>
</form>
<?php 
if(! empty($lists->scripts))
{
?>
   <script  type="text/javascript" src="<?php echo $lists->scripts; ?>"></script>
<?php
}
?>
</div>

</div>
