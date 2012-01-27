<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 */
defined('_JEXEC') or die();
?>

<div class="greybox">
	<div>
	    <div>
			<table cellpadding="0" cellspacing="0" border="0" width="100%">
			    <tr>
			        <td width="100%">
					    <div class="loginform">
					    	<form action="<?php echo CRoute::getURI();?>" method="post" name="login" id="form-login" >
					            <div><label>
									<?php echo JText::_('CC USERNAME'); ?><br />
					                <input type="text" class="inputbox frontlogin" name="username" id="username" />
					            </label></div>

					            <div><label>
									<?php echo JText::_('CC PASSWORD'); ?><br />
					                <input type="password" class="inputbox frontlogin" name="passwd" id="password" />
					            </label></div>

                                <?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
								<label for="remember">
									<input type="checkbox" alt="<?php echo JText::_('CC REMEMBER MY DETAILS'); ?>" value="yes" id="remember" name="remember"/>
									<?php echo JText::_('CC REMEMBER MY DETAILS'); ?>
								</label>
								<?php endif; ?>

								<div class="login-submit">
								    <input type="submit" value="<?php echo JText::_('CC BUTTON LOGIN');?>" name="submit" id="submit" class="button" />
									<input type="hidden" name="option" value="com_user" />
									<input type="hidden" name="task" value="login" />
									<input type="hidden" name="return" value="<?php echo $return; ?>" />
									<?php echo JHTML::_( 'form.token' ); ?>
								</div>
								<div class="pad10">
								<span>
									<?php echo JText::_('CC FORGOT YOUR'); ?> <a href="<?php echo CRoute::_( 'index.php?option=com_user&view=reset' ); ?>" class="login-forgot-password"><span><?php echo JText::_('CC LPASSWORD'); ?></span></a> /
									<a href="<?php echo CRoute::_( 'index.php?option=com_user&view=remind' ); ?>" class="login-forgot-username"><span><?php echo JText::_('CC LUSERNAME'); ?></span></a>?
								</span>
								<br />									
								<a href="<?php echo CRoute::_( 'index.php?option=com_community&view=register&task=activation' ); ?>" class="login-forgot-username">
									<span><?php echo JText::_('CC RESEND ACTIVATION CODE'); ?></span>
								</a></div>
					        </form>
					        <?php echo $fbHtml;?>
					    </div>
			        </td>
			    </tr>
			</table>
	    </div>
	</div>
</div>