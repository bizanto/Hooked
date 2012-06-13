<?php
/**
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

defined('_JEXEC') or die('Restricted access');

$url = clone(JURI::getInstance());

// include JomSocial core libraries
require_once( JPATH_ROOT.DS.'components'.DS.'com_community'.DS.'libraries'.DS.'core.php');
$config 	= CFactory::getConfig();

?>
<?php echo '<?xml version="1.0" encoding="utf-8"?'.'>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" ><head>

<?php

# CHECK USER
$checkuser = JFactory::getUser();
if ($checkuser->get('gid')>0) { $loggedin=1; } else $loggedin=0;

# CHECK IF IPHONE OR IPAD
$iMobile = (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPad') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPod Touch'));

# SET ACTIVE AND DEFAULT MENU VARS
$menu = &JSite::getMenu();
$active = $menu->getActive();
$default = $menu->getDefault();

if (JRequest::getString("Itemid")) {
	$item_id = JRequest::getVar("Itemid"); //set current itemid
}
else {
    $item_id = 0;
}

if ($active == $default || $item_id==334) $isHome=1; else $isHome="";
if (is_object( $active )) {
	$params = new JParameter( $active->params );
	$pageclass = $params->get( 'pageclass_sfx' );
}

$regid = 211; 	// itemid of registration page
$loginid = 231; //itemid of login


if ($active && $loginid == $active->id || JRequest::getString("view")=="login") $pageclass = " loginn";

if ($item_id == $regid || $item_id==$loginid || JRequest::getString("view")=="remind" || JRequest::getString("view")=="login")   $registration_page = 1;

else $registration_page = "";

# REDIRECT IPHONES
// don't redirect per #926 - need clarification...
if (false && $iMobile && $isHome && !$loggedin) {
	$app = &JFactory::getApplication();
	$app->redirect("index.php?option=com_user&view=login&Itemid=231");
}
elseif ($loggedin && $registration_page) {
	$app = &JFactory::getApplication();
	$app->redirect("index.php?option=com_community&view=frontpage&Itemid=2");
}

elseif (JRequest::getString('logginn') && !$loggedin) {
	$app = &JFactory::getApplication();
	$app->redirect("index.php?option=com_user&view=login&Itemid=231");
}

$document =& JFactory::getDocument();

if (JRequest::getVar('option') != 'com_community') {	
	// JomSocial / cWindow includes
	$document->addScript(JURI::base()."components/com_community/assets/window-1.0.js");
	$document->addScript(JURI::base()."components/com_community/assets/joms.jquery.js");
	$document->addScript(JURI::base()."components/com_community/assets/script-1.2.js");
	$document->addScript(JURI::base()."components/com_community/assets/joms.ajax.js");
	$document->addStyleSheet(JURI::base()."components/com_community/assets/window.css");
} 

if (!isset($document->_metaTags['property']['og:image'])) {
	$document->_metaTags['property']['og:image'] = 'http://www.hooked.no/hooked.png';
}

$homelink = 'http://www.hooked.no/';

$title = $document->getTitle();
if (!$title) $title = 'HOOKED.no';

?>


	<jdoc:include type="head" />
    <?php
	if ($iMobile) : ?>
	<meta name="apple-mobile-web-app-capable" content="yes"> 
	<meta name="apple-mobile-web-app-status-bar-style" content="black" /> 
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" /> 
	<?php endif; ?>

    <?php if ($registration_page && !$iMobile) : ?>
    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template;?>/css/simple.css" type="text/css" />
    <?php elseif ($registration_page && $iMobile) : ?>
    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template;?>/css/simple-mobile.css" type="text/css" />
    <?php else : ?>
    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template;?>/css/template.css" type="text/css" />
    <?php endif; ?>
	<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template;?>/css/print.css" type="text/css" media="Print" />
    <meta property="og:title" content="<?php echo $title; ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo JURI::getInstance()->toString(); ?>" />
    <?php //<meta property="og:image" content="http://www.hooked.no/hooked.png" /> ?>
    <meta property="og:site_name" content="HOOKED" />
    <meta property="fb:app_id" content="<?php echo $config->get('fbconnectkey');?>" />

	<!--[if lte IE 6]>
		<link href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template;?>/css/ieonly.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<!--[if IE 7]>
		<link href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template;?>/css/ie7only.css" rel="stylesheet" type="text/css" />
	<![endif]-->
<?php 

?>

<?php if((JRequest::getString('option') != "com_jreviews") && !(class_exists('cmsFramework') && $jreviews =& cmsFramework::getInstance() && isset($jreviews->listing))) : ?>
<!-- mvcjr -->
<?php endif; ?>

<?php if (!$registration_page) : ?>
<script type="text/javascript">
jQuery(function ($) {
	$('#lnav ul.menu > li > a').each(
		function () {
			if ($(this).parent().find('ul').length > 0) {
				$(this).click(function () {
					$(this).parent().siblings().children('ul').css('display','none');
					$(this).siblings('ul').slideToggle('fast');
					return false;
				});
			} else {
				return true;
			}
	});
	// refer friend
	$('.mailfriend').click(function() {
		$(this).toggleClass('hide');
		$(this).parent().parent().siblings('.mail_this_page').toggleClass('hide');
		$(this).parent().parent().parent().parent().toggleClass('wider');
		return false;
    });	
	$('#cancelfriend').click(function() {
		$(this).parent().parent().parent().parent().parent().parent().toggleClass('wider');
		$(this).parent().parent().parent().parent().siblings('.article-tweets').children().children('.mailfriend').toggleClass('hide');
		$(this).parent().parent().parent().parent().toggleClass('hide');
		return false;
    });	
});
<?php if (!$loggedin) : ?>

function cWindowLogin() {
	var ajaxCall = 'jax.call("community", "profile,ajaxLogin", "")';
	cWindowShow(ajaxCall, '<?php JText::_('CC MEMBER LOGIN'); ?>', 480, 100);
}
function onLoginWindowLoad() {
	jQuery('#username').focus();
}
<?php endif; ?>
</script>
<?php endif; ?>

<?php if (!$registration_page) : ?>
<?php // Get Satisfaction Widget ?>
<script type="text/javascript" charset="utf-8">
  var is_ssl = ("https:" == document.location.protocol);
  var asset_host = is_ssl ? "https://s3.amazonaws.com/getsatisfaction.com/" : "http://s3.amazonaws.com/getsatisfaction.com/";
  document.write(unescape("%3Cscript src='" + asset_host + "javascripts/feedback-v2.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<?php // */ ?>
<script type="text/javascript" charset="utf-8">
  var feedback_widget_options = {};

  feedback_widget_options.display = "overlay";  
  feedback_widget_options.company = "hookedno";
  feedback_widget_options.placement = "right";
  feedback_widget_options.color = "#222";
  feedback_widget_options.style = "question";
  var feedback_widget = new GSFN.feedback_widget(feedback_widget_options);
</script>


<?php endif; ?>

<?php // google analytics ?>
<script type="text/javascript">

 var _gaq = _gaq || [];
 _gaq.push(['_setAccount', 'UA-16936007-1']);
 _gaq.push(['_trackPageview']);

 (function() {
   var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
   ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
   var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
 })();
 
<?php if ($isHome && !1) : ?>
 jQuery(function ($) {
	$('.home-bottom1 a').mouseenter(function() {
		$(this).children('.overlay').slideToggle(80);
		$(this).children('p').toggle();
    });	
	$('.home-bottom1 a').mouseleave(function() {
		$(this).children('.overlay').slideToggle(80);
		$(this).children('p').toggle();
    });	
	
});
<?php endif; ?>
</script>


</head>
<body<?php echo ' class="body'; if ($registration_page) echo ' simple'; if (isset($pageclass)) echo ' '.$pageclass.''; echo '"'; ?><?php if ($iMobile) echo ' style="-webkit-text-size-adjust:none"'; ?>>
<?php if (!$registration_page) : ?><div id="mtns"></div><?php endif; ?>

    <div id="doc"<?php if ($isHome) echo ' class="frontpage"'; ?>>
    	<div class="main-wrapper<?php if ($registration_page) echo '-simple'; ?>">
            <div id="header">
                <div class="left">
                    <a href="<?php echo $homelink; ?>" id="hooked-logo" title="Hooked.no">HOOKED.no</a>
                </div>
                <div class="right">
					<?php if ($this->countModules('global') && !$registration_page) : ?>
                    <jdoc:include type="modules" name="global" style="xhtml" />
                    <?php endif; ?>
                    <?php if ($this->countModules('global-search') && !$registration_page) : ?>
                    <div class="fr width25">
                    <jdoc:include type="modules" name="global-search" style="xhtml" />
                    </div>
                    <?php endif; ?>
                    <?php if ($this->countModules('user-status')) : ?>
                    <div class="fr width75">
                    	<div class="status-label">Status</div>
                        <div class="user-status">
                            <jdoc:include type="modules" name="user-status" style="xhtml" />
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($this->countModules('topbanner')) : ?>
            <div id="topBanner">
                <jdoc:include type="modules" name="topbanner" style="xhtml" />
            </div>
            <?php endif; ?>
            
            <?php if ($this->countModules('rightbanner')) : ?>
            <div id="rightBanner">
                <jdoc:include type="modules" name="rightbanner" style="xhtml" />
            </div>
            <?php endif; ?>
            
            <div id="main-container-wrap-outer">
	            <div class="stitches-top"></div>
                <div id="main-container-wrap">
                    <div class="stitches-right">
                        <div id="main-container-bg">
                            <div id="main-container-top">
                                <div id="main-container">
                                	<?php  if ($isHome) : // new homepage ?>
            						<div class="homepageHeader">
                                		<?php if ($this->countModules('home-title')) : ?>
                                        	<jdoc:include type="modules" name="home-title" style="xhtml" />
                                        <?php endif; ?>
                                        <?php if ($this->countModules('home-topright')) : ?>
                                        	<jdoc:include type="modules" name="home-topright" style="xhtml" />
                                        <?php endif; ?>
                                	</div>
            						<?php endif; // end new homepage ?>
            						
                                    <?php if (!$registration_page) : ?>
                                    <div id="lnav-container<?php  if ($isHome) : // new homepage ?>-wide<?php endif; ?>">
                                    <?php if (!$isHome) : // not new homepage ?>
	                                    <?php if ($this->countModules('badge')) : ?>
	                                        <div id="badge">
	                                        <jdoc:include type="modules" name="badge" style="xhtml" />
	                                        </div>
	                                    <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if ($this->countModules('left')) : ?>
                                        <div id="lnav">
                                        <jdoc:include type="modules" name="left" style="xhtml" />
                                    <?php if ($this->countModules('news-listings')) :?>
                                  		<jdoc:include type="modules" name="news-listings" style="xhtml" />
                                  	<?php endif;?>    
                                        </div>
                                    <?php endif; ?>
                                  	<?php if (!$isHome) : // not new homepage ?>
                                        <div class="lnav-bottom"></div>
                                    <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    <div class="main-content" id="content<?php  if ($isHome) : // new homepage ?>-home<?php endif; ?>">
                                    <?php if (JRequest::getString('option')!='com_community' && JRequest::getString('option')!='com_user') : ?>
                                        <?php if ($this->countModules('content-top')) : ?>
                                                <jdoc:include type="modules" name="content-top" style="xhtml" />
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    
									<?php if ($this->getBuffer('message') && !JRequest::getString('logginn')) : ?>
                                    <?php
                                    $html = '';
                                    if (JRequest::getVar('option') != 'com_community') {
                                        $html .= '<script type="text/javascript" src="'.$this->baseurl.'/components/com_community/assets/window-1.0.js"></script>
                                        <script type="text/javascript" src="'.$this->baseurl.'/components/com_community/assets/joms.jquery.js"></script>
                                        <script type="text/javascript" src="'.$this->baseurl.'/components/com_community/assets/script-1.2.js"></script>
                                        <link rel="stylesheet" type="text/css" href="'.$this->baseurl.'/components/com_community/assets/window.css" />';
                                    }
                                    $html .= '<script type="text/javascript">
                                    jQuery(document).ready(function () {
                                    message = jQuery(\''.str_replace("\n", "", $this->getBuffer('message')).'\');
                                    cWindowShow(\'jQuery("#cWindowContent").html(message);\', \'Notice\', 400, 150);
                                    });
                                    </script>';
                                    $this->addCustomTag($html);
                                    ?>
                                    <?php endif; ?>
                                    
                                    <jdoc:include type="component" />
                                    
                                    <div class="content-bottom">
                                    <?php if ($this->countModules('content-bottom')) : ?>
                                        <jdoc:include type="modules" name="content-bottom" style="xhtml" />
                                    <?php endif; ?>
                                    </div>


                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <?php if (!$registration_page) : ?>
                        <div class="stitches-top"></div>
                        <div id="footer">
                            <?php /* if ($this->countModules('mainbottom')) : ?>
                            <jdoc:include type="modules" name="mainbottom" style="xhtml" />
                            <div class="clear"></div>
                            <?php endif; */ ?>
                            <?php if ($this->countModules('footer-left')) : ?>
                            <div class="width33 fl">
                                <jdoc:include type="modules" name="footer-left" style="xhtml" />
                            </div>
                            <?php endif; ?>
                            <?php if ($this->countModules('footer-middle')) : ?>
                            <div class="width33 fl">
                                <jdoc:include type="modules" name="footer-middle" style="xhtml" />
                            </div>
                            <?php endif; ?>
                            <?php if ($this->countModules('footer-right')) : ?>
                            <div class="width33 fl">
                                <jdoc:include type="modules" name="footer-right" style="xhtml" />
                            </div>
                            <?php endif; ?>
                            <div class="clear"></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="clear"></div>
	            <div class="stitches-bottom"></div>
            </div>
            <?php if ($isHome && !1) : ?>
				<?php if ($this->countModules('home-middle')) : ?>
	            <div class="home-middle">
	                <jdoc:include type="modules" name="home-middle" style="xhtml" />
	            </div>
	            <?php endif; ?>
	            <?php if ($this->countModules('home-bottom1')) : ?>
	            <div class="home-bottom1">
	                <jdoc:include type="modules" name="home-bottom1" style="xhtml" />
	                <div class="clear"></div>
	            </div>
	            <?php endif; ?>
	            <div class="home-footer">              
	                <?php if ($this->countModules('home-bottom2')) : ?>
	                <div class="home-bottom2">
	                    <jdoc:include type="modules" name="home-bottom2" style="xhtml" />
	                </div>
	                <?php endif; ?>
	                
	                <div class="clear"></div>
	            </div>
            <?php endif; ?>
            <?php if ($this->countModules('copyright')) : ?>
            <div class="copyright">
	            <jdoc:include type="modules" name="copyright" style="xhtml" />
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!$registration_page) : ?>
    <div class="bottomfooter">
    	<?php if ($this->countModules('bottom-footer')) : ?>
        <div class="bottom-footer">
            <jdoc:include type="modules" name="bottom-footer" style="xhtml" />
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if ($this->countModules('debug')) : ?>
    <div class="debug">
        <jdoc:include type="modules" name="debug" style="xhtml" />
    </div>
    <?php endif; ?>
</body>
</html>
