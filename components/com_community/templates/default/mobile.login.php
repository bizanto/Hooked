<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 */
defined('_JEXEC') or die();

$uri	= CRoute::_('index.php?option=com_community&view=profile' , false );
$return	= base64_encode($uri);
?>

<!--sepatutnya kat atas ni false, but cuba jadi true dan tambah &screen=mobile -->

<h2>Members Login</h2>


<script type="text/javascript">
function clearInputs()
{
	joms.jQuery('#username').val('');
	joms.jQuery('#password').val('');
	joms.jQuery('#username').focus();
}
window.scrollTo(0, 1);
</script>




<form action="<?php echo CRoute::getURI() . 'screen=mobile' ?>" method="post" name="login" id="form-login">


<div class="loginform">
	<div class="loginform-label"><?php echo JText::_('CC USERNAME'); ?></div>
	
	<div class="loginform-input">
		<input type="text" class="inputbox frontlogin" name="username" id="username" size="18" value="your username" onFocus="if(this.value==this.defaultValue) this.value='';" onBlur="if(this.value=='') this.value=this.defaultValue;"/>
	</div>
			<div class="clear"></div>
	<div class="loginform-label"><?php echo JText::_('CC PASSWORD'); ?></div>
	<div class="loginform-input"><input type="password" class="inputbox frontlogin" name="passwd" id="password" value="password" onFocus="if(this.value==this.defaultValue) this.value='';" onBlur="if(this.value=='') this.value=this.defaultValue;"/>

</div>
	
<div class="clear"></div>

</div><!--end of loginform-->



<div class="buttons-area">
	<input type="submit" value="<?php echo JText::_('CC IPHONE BUTTON LOGIN');?>" name="submit" id="submit" class="button" />
	<input type="button" value="<?php echo JText::_('CC IPHONE BUTTON CLEAR');?>" name="clear" id="clear" class="button" onclick="clearInputs();return false;" />
	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</div>
		
		<!--
		<a href="<?php echo CRoute::_( 'index.php?option=com_user&view=reset' ); ?>" class="login-forgot-password">
			<span><?php echo JText::_('CC FORGOT PASSWORD'); ?></span>
		</a><br />
		<a href="<?php echo CRoute::_( 'index.php?option=com_user&view=remind' ); ?>" class="login-forgot-username">
			<span><?php echo JText::_('CC FORGOT USERNAME'); ?></span>
		</a>
		-->
</form>
    
    
    <h2>Not yet a member? Join us now!</h2>
    
    <ul class="benefit-lists">
    	<li>Connect and expand your network</li>
    	<li>View profiles and add new friends</li>
    	<li>Share your photos and videos</li>
    	<li>Create your own groups or join others</li>
    </ul>

