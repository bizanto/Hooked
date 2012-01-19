<?php
/**
 * @category	Module
 * @package		JomSocial
 * @subpackage	HelloMe
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');

if( $my->isOnline() && $my->id != 0 )
{
	$inboxModel			= CFactory::getModel('inbox');
	$filter				= array();
	$filter['user_id']	= $my->id;
	$friendModel		= CFactory::getModel ( 'friends' );
	$profileid 			= JRequest::getVar('userid' , 0 , 'GET');
	
	$params->def('unreadCount',	$inboxModel->countUnRead ( $filter ));
	$params->def('pending', $friendModel->countPending( $my->id ));
	$params->def('myLink', CRoute::_('index.php?option=com_community&view=profile&userid='.$my->id));
	$params->def('myName', $my->getDisplayName());
	$params->def('myAvatar', $my->getAvatar());
	$params->def('myId', $my->id);
	$params->def('myKarma', CUserPoints::getPointsImage($my));
	$params->def('enablephotos', $config->get('enablephotos'));
	$params->def('enablevideos', $config->get('enablevideos'));
	$params->def('enablegroups', $config->get('enablegroups'));
	$params->def('enableevents', $config->get('enableevents'));
	
	# HTGMOD
	$params->def('moduleclass_sfx', $config->get('moduleclass_sfx'));
	
	$enablekarma	= $config->get('enablekarma') ? $params->get('show_karma' , 1 ) : $config->get('enablekarma');
	$params->def('enablekarma', $enablekarma);

	$js		= modHelloMeHelper::getHelloMeScript( $my->getStatus() , COwnerHelper::isMine($my->id, $profileid) );
	$document	= JFactory::getDocument();
	$document->addScriptDeclaration($js);
	
	if($params->get('enable_facebookconnect', '1'))
	{
		$params->def('facebookuser', modHelloMeHelper::isFacebookUser());
	}
	else
	{
		$params->def('facebookuser', false);
	}
	
	CFactory::load( 'helpers' , 'string');
	
	$unreadCount 	= $params->get('unreadCount', 1);
	$pending 		= $params->get('pending', 1);
	$myLink 		= $params->get('myLink', 1);
	$myName 		= $params->get('myName', 1);
	$myAvatar 		= $params->get('myAvatar', 1);
	$myId 			= $params->get('myId', 1);
	$myKarma 		= $params->get('myKarma', 1);
	$enablephotos 	= $params->get('enablephotos', 1);
	$enablevideos 	= $params->get('enablevideos', 1);
	$enablegroups 	= $params->get('enablegroups', 1);
	$enableevents 	= $params->get('enableevents', 1);
	$show_avatar 	= $params->get('show_avatar', 1);
	$show_karma 	= $params->get('enablekarma', 1);
	$show_myblog 	= $params->get('show_myblog', 1);
	
	$class_suffix   = $params->get('moduleclass_sfx', 1);
	
	$facebookuser 	= $params->get('facebookuser', false);
	$config	= CFactory::getConfig();
	$uri	= CRoute::_('index.php?option=com_community' , false );
	$uri	= base64_encode($uri);

	CFactory::load('helpers' , 'string' );
	
	?>
    
    <?php if ($class_suffix == " status-only") : ?>
    	<div class="helloMeStatusText">
			<div id="helloMeEdit" style="display: none;">
				<input name="helloMeStatusText" id="helloMeStatusText" type="text" class="status inputbox" value="" onblur="helloMe.saveStatus();return false;" onkeyup="helloMe.saveChanges(event);return false;" />
			</div>
			<div id="helloMeDisplay">
				<span href="javascript:void(0);" id="helloMeStatusLink" onclick="helloMe.changeStatus();">
					<span id="helloMeStatus" style="text-decoration: none;"></span>
				</span>
			</div>
		</div>
        
        <div class="status-links">
		<a href="javascript:void(0);" id="saveLink" style="display: none;" onclick="helloMe.saveStatus();" class="status-icon-save"><?php echo JText::_('MOD_HELLOME SAVE MY STATUS'); ?></a>
		<a href="javascript:void(0);" id="editLink" style="display: block;" onclick="helloMe.changeStatus();" class="status-icon-edit"><?php echo JText::_('MOD_HELLOME EDIT MY STATUS'); ?></a>
		</div>

    <?php elseif ($class_suffix == " login-module") : ?>
    

	<form action="index.php" method="post" name="hellomelogout" id="hellomelogout">	
		<input type="hidden" name="option" value="com_user" />
		<input type="hidden" name="task" value="logout" />
		<input type="hidden" name="return" value="<?php echo $uri; ?>" />
	</form>
        
        <div class="logout-wrap">
            <div class="logout-btn">
                <a href="javascript:void(0);" onclick="helloMe.logout();<?php /* FB.Connect.logout(); */ ?> return false;"><?php echo JText::_('MOD_HELLOME MY LOGOUT'); ?></a>
            </div>
        </div>
        
    <?php else : ?>
    
	<div class="hello-container">
		<a class="hello-name" href ="<?php echo $myLink; ?>"><?php echo CStringHelper::escape( $myName ); ?></a>
		<?php
		if($show_avatar)
		{
		?>
			<a class="hello-edit-profile" href="<?php echo $myLink; ?>">
            	<img src="<?php echo $myAvatar; ?>" alt="<?php echo CStringHelper::escape( $myName ); ?>" class="hello-avatar" />
            </a>
		<?php
		}
		?>
        <?php /* <a class="hello-edit-profile" href="index.php?option=com_community&view=profile&task=editDetails&Itemid=47"><?php echo JText::_('CC EDIT PROFILE'); ?></a> */ ?>
		<?php	
		if($show_karma && !1)
		{
		?>
			<img src="<?php echo $myKarma; ?>" alt="<?php echo JText::_('MOD_HELLOME KARMA'); ?>" class="hello-karma" />
		<?php
		}
		?>

			<a class="new-msg<?php if ($unreadCount) echo ' new-msg-alert'; ?>" href="<?php echo CRoute::_('index.php?option=com_community&view=inbox'); ?>"><?php echo $unreadCount; ?></a>
			<a class="new-req<?php if ($pending) echo ' new-req-alert'; ?>" href="<?php echo CRoute::_('index.php?option=com_community&view=friends&task=pending'); ?>"><?php echo $pending; ?></a>

	</div>
	
	<?php endif; ?>
	

<?php
}
else
{

	$class_suffix   = $params->get('moduleclass_sfx', 1);

	if ($class_suffix == " login-module") { ?>
	    
    <div class="loginout">
            <ul>            
                <li><a class="loginlink" href="index.php?option=com_user&view=login&Itemid=231" <?php /* onclick="javascript:cWindowLogin(); return false;" */ ?>><?php echo JText::_('LOGIN') ?></a></li>                
                <li>
                    <div class="logout-wrap">
                        <div class="logout-btn">
                        <a class="registerlink" href="index.php?option=com_community&view=register&Itemid=211"><?php echo JText::_('MOD_HELLOME_REGISTER'); ?></a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    <?php 
	
	} else {
        
	$content = '';
	
	if($params->get('enable_login', '1'))
	{
		$uri	= CRoute::_('index.php?option=com_community&view=profile' , false );
		$uri	= base64_encode($uri);
		$html	= '';

		if(JPluginHelper::isEnabled('authentication', 'openid')) 
		{
			JHTML::_('script', 'openid');
		}
?>
		<form action="<?php echo CRoute::_( 'index.php?option=com_user&task=login' ); ?>" method="post" name="form-login" id="form-login" >
			<?php echo $params->get('pretext'); ?>
			<fieldset class="input">
			<p id="form-login-username">
				<label for="username">
					<?php echo JText::_('Username') ?><br />
					<input name="username" id="username" type="text" class="inputbox" alt="username" size="18" />
				</label>
			</p>
			<p id="form-login-password">
				<label for="passwd">
					<?php echo JText::_('Password') ?><br />
					<input type="password" name="passwd" id="passwd" class="inputbox" size="18" alt="password" />
				</label>
			</p>
			<?php 
			if(JPluginHelper::isEnabled('system', 'remember'))
			{
			?>
			<p id="form-login-remember">
				<label for="remember">
					<?php echo JText::_('Remember me') ?>
					<input type="checkbox" name="remember" id="remember" value="yes" alt="Remember Me" />
				</label>
			</p>
			<?php 
			}
			?>
			<input type="submit" name="Submit" class="button" value="<?php echo JText::_('LOGIN') ?>" />
			</fieldset>
			<div style="margin-left:5px;">
				<div>
					<a href="<?php echo JRoute::_( 'index.php?option=com_user&view=reset' ); ?>">
					<?php echo JText::_('MOD_HELLOME_FORGOT_YOUR_PASSWORD'); ?>
					</a>
				</div>
				<div>
					<a href="<?php echo JRoute::_( 'index.php?option=com_user&view=remind' ); ?>">
					<?php echo JText::_('MOD_HELLOME_FORGOT_YOUR_USERNAME'); ?>
					</a>
				</div>
				<?php
				$usersConfig = &JComponentHelper::getParams( 'com_users' );
				if ($usersConfig->get('allowUserRegistration')) 
				{
				?>
				<div>
					<a href="<?php echo CRoute::_( 'index.php?option=com_community&view=register' ); ?>">
						<?php echo JText::_('MOD_HELLOME_REGISTER'); ?>
					</a>
				</div>
				<?php
				}
				?>
			</div>
			<?php echo $params->get('posttext'); ?>
		
			<input type="hidden" name="option" value="com_user" />
			<input type="hidden" name="task" value="login" />
			<input type="hidden" name="return" value="<?php echo $uri; ?>" />
			<?php echo JHTML::_( 'form.token' ); ?>
		</form>
<?php
	}
	
	if($params->get('enable_facebookconnect', '1'))
	{
		if( $my->id == 0 )
		{
			if( $config->get('fbconnectkey') && $config->get('fbconnectsecret') )
			{
		?>
			<div id="fb-root"></div>
			<script type="text/javascript">
			var count	= 1;
			window.fbAsyncInit = function() {
			    FB.init({appId: '<?php echo $config->get('fbconnectkey');?>', status: false, cookie: true, xfbml: true});
			
			    /* All the events registered */
			    FB.Event.subscribe('auth.login', function(response) {
			    	if( count == 1 )
						joms.connect.update();
						
					count++;
			    });
			};
			(function() {
			    var e = document.createElement('script');
			    e.type = 'text/javascript';
			    e.src = document.location.protocol +
			        '//connect.facebook.net/en_US/all.js';
			    e.async = true;
			    document.getElementById('fb-root').appendChild(e);
			}());
			</script>
			<fb:login-button autologoutlink="true" perms="read_stream,publish_stream,offline_access,email,user_birthday,status_update,user_status"><?php echo JText::_('CC SIGN IN WITH FACEBOOK');?></fb:login-button>
		<?php
			}
		}
	}
}
} // end class suffix condition
?>

