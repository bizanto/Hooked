<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<div id="login-module">
<?php if($type == 'logout') : ?>
<form action="index.php" method="post" name="login" id="form-login">
<?php if ($params->get('greeting')) : ?>
	<div>
	<?php if ($params->get('name')) : {
		echo JText::sprintf( 'HINAME', $user->get('name') );
	} else : {
		echo JText::sprintf( 'HINAME', $user->get('username') );
	} endif; ?>
	</div>
<?php endif; ?>
	<div align="center">
		<input type="submit" name="Submit" class="logout-button button" value="<?php echo JText::_( 'BUTTON_LOGOUT'); ?>" id="modlgn_submit" />
	</div>

	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="logout" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
</form>
<?php else : ?>
<?php if(JPluginHelper::isEnabled('authentication', 'openid')) :
		$lang->load( 'plg_authentication_openid', JPATH_ADMINISTRATOR );
		$langScript = 	'var JLanguage = {};'.
						' JLanguage.WHAT_IS_OPENID = \''.JText::_( 'WHAT_IS_OPENID' ).'\';'.
						' JLanguage.LOGIN_WITH_OPENID = \''.JText::_( 'LOGIN_WITH_OPENID' ).'\';'.
						' JLanguage.NORMAL_LOGIN = \''.JText::_( 'NORMAL_LOGIN' ).'\';'.
						' var modlogin = 1;';
		$document = &JFactory::getDocument();
		$document->addScriptDeclaration( $langScript );
		JHTML::_('script', 'openid.js');
endif; ?>
<form action="<?php echo JRoute::_( 'index.php', true, $params->get('usesecure')); ?>" method="post" name="login" id="form-login" >
	<?php echo $params->get('pretext'); ?>
	<fieldset class="input">
	<!-- <p id="form-login-username"> -->
		<? /* <label for="modlgn_username"><?php echo JText::_('Username') ?></label><br /> */ ?>
		<input id="modlgn_username" type="text" name="username" class="inputbox" alt="username" size="18" />
	<!-- </p> -->
	<!-- <p id="form-login-password"> -->
		<? /* <label for="modlgn_passwd"><?php echo JText::_('Password') ?></label><br /> */ ?>
		<input id="modlgn_passwd" type="password" name="passwd" class="inputbox" size="18" alt="password" />
	<!-- </p> -->
	   	<input type="submit" name="Submit" class="button" value="<?php echo JText::_('LOGIN') ?>" id="modlgn_submit" />
	<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
    <div class="clear"></div>
	<div id="form-login-remember">
	    <input id="modlgn_remember" type="checkbox" name="remember" class="inputbox" value="yes" alt="Remember Me" />
		<label for="modlgn_remember"><?php echo JText::_('Remember me') ?></label>
        <span id="forgot"><a href="<?php echo JRoute::_( 'index.php?option=com_user&view=remind' ); ?>"><?php # echo JText::_('FORGOT_YOUR_USERNAME'); ?>Forgot username</a> 
        <a href="<?php echo JRoute::_( 'index.php?option=com_user&view=reset' ); ?>"><?php # echo JText::_('FORGOT_YOUR_PASSWORD'); ?>or password?</a></span>
	</div>
	<?php endif; ?>
	</fieldset>
		<?php
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		if ($usersConfig->get('allowUserRegistration') && 2==1) : ?>
			<a href="<?php echo JRoute::_( 'index.php?option=com_user&view=register' ); ?>">
				<?php echo JText::_('REGISTER'); ?></a>
		<?php endif; ?>
	<?php echo $params->get('posttext'); ?>

	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php endif; ?>
</div>