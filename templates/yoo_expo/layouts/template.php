<?php
/**
* @package   yoo_expo Template
* @file      template.php
* @version   5.5.2 December 2010
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) 2007 - 2010 YOOtheme GmbH
* @license   YOOtheme Proprietary Use License (http://www.yootheme.com/license)
*/

// get template configuration
include(dirname(__FILE__).'/template.config.php');
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->warp->config->get('language'); ?>" lang="<?php echo $this->warp->config->get('language'); ?>" dir="<?php echo $this->warp->config->get('direction'); ?>" >
<head>
<?php
/** HTG **/
# MENU
$menu = &JSite::getMenu();
$active = $menu->getActive();
$default = $menu->getDefault();
if (is_object( $active )) {
	$params = new JParameter( $active->params );
	$pageclass = $params->get( 'pageclass_sfx' );
}

# CHECK IF IPHONE OR IPAD
$iMobile = (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone')
         || strstr($_SERVER['HTTP_USER_AGENT'], 'iPad'));

# CHECK USER
$checkuser = JFactory::getUser();
if ($checkuser->get('gid')>0) { $loggedin=1; } else $loggedin="";
if ($loggedin && $active == $default) {
	$app = &JFactory::getApplication();
	$app->redirect("index.php?option=com_community&view=frontpage&Itemid=2");
}

echo $this->warp->template->render('head'); ?>
<?php if ($active == $default): ?>
<link rel="stylesheet" href="templates/yoo_expo/css/homepage.css">
<?php endif; ?>
<link rel="apple-touch-icon" href="<?php echo $this->warp->path->url('template:apple_touch_icon.png'); ?>" />
<script type="text/javascript">
jQuery(function ($) {
	$('a.iphoneTrigger').click(function () {
		$('#iphone-img').attr('src', 'images/iphone-' + $(this).attr('rel') + '.png');
	});
});
</script>
<script type="text/javascript">

 var _gaq = _gaq || [];
 _gaq.push(['_setAccount', 'UA-16936007-5']);
 _gaq.push(['_trackPageview']);

 (function() {
   var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
   ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
   var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
 })();

</script>
</head>

<body id="page" class="yoopage <?php echo $this->warp->config->get('columns'); ?> <?php echo $this->warp->config->get('itemcolor'); ?> <?php echo $this->warp->config->get('toolscolor'); ?> <?php echo 'style-'.$this->warp->config->get('style'); ?> <?php echo 'wrapper-'.$this->warp->config->get('wrapper'); ?> <?php echo 'background-'.$this->warp->config->get('background'); ?> <?php echo 'font-'.$this->warp->config->get('font'); ?> <?php echo $this->warp->config->get('webfonts'); ?>">
<div id="feedback">
    <?php echo $this->warp->modules->render('feedback'); ?>
</div>

<div id="topbar"></div>
<div<?php if ($active == $default) echo ' id="home"'; else echo ' id="inner"'; ?>>

	<?php if ($this->warp->modules->count('absolute')) : ?>
	<div id="absolute">
		<?php echo $this->warp->modules->render('absolute'); ?>
	</div>
	<?php endif; ?>
    <?php if ($active==$default) : ?>
		<?php include('homepage.php'); ?>
	<?php else: ?>
    <div id="top-area">
        <div class="toppad">
            <div id="topleft">
                <a href="/" id="hooked">Hooked</a>
            
            </div>
            <div id="topright">
                <?php if ($this->warp->modules->count('global')) : ?>
                <div id="global">
                    <?php echo $this->warp->modules->render('global'); ?>
                </div>
                <?php endif; ?>
                <?php if ($this->warp->modules->count('global-buttons')) : ?>
                <div id="global">
                    <?php echo $this->warp->modules->render('global-buttons'); ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <?php endif; ?>
	<?php if ($active != $default) : ?>
	<div id="page-body">

		<div class="wrapper-bg">
		<div class="wrapper">
			
			<div class="wrapper-t1">
				<div class="wrapper-t2">
					<div class="wrapper-t3"></div>
				</div>
			</div>
			
			<div class="wrapper-1">
				<div class="wrapper-2">
					<div class="wrapper-3">
						<div class="wrapper-4">
						
							<div id="header">

								<div id="toolbar">
								
									<?php if ($this->warp->modules->count('toolbarleft')) : ?>
									<div class="left">
										<?php echo $this->warp->modules->render('toolbarleft'); ?>
									</div>
									<?php endif; ?>
									
									<?php if ($this->warp->modules->count('toolbarright')) : ?>
									<div class="right">
										<?php echo $this->warp->modules->render('toolbarright'); ?>
									</div>
									<?php endif; ?>
									
									<?php if($this->warp->config->get('date')) : ?>
									<div id="date">
										<?php echo $this->warp->config->get('actual_date'); ?>
									</div>
									<?php endif; ?>
									
								</div>
                                
                                <?php if ($this->warp->modules->count('maintop')) : ?>
                                <div id="maintop">
                                    <?php echo $this->warp->modules->render('maintop', array('wrapper'=>"maintopbox float-left", 'layout'=>$this->warp->config->get('maintop'))); ?>
                                </div>
                                <!-- maintop end -->
                                <?php endif; ?>
								
								<?php if ($this->warp->modules->count('search')) : ?>
								<div id="search">
									<?php echo $this->warp->modules->render('search'); ?>
								</div>
								<?php endif; ?>
	
								<div id="headerbar">
				
									<?php if($this->warp->modules->count('headerleft')) : ?>
									<div class="left">
										<?php echo $this->warp->modules->render('headerleft'); ?>
									</div>
									<?php endif; ?>
									
									<?php if($this->warp->modules->count('headerright')) : ?>
									<div class="right">
										<?php echo $this->warp->modules->render('headerright'); ?>
									</div>
									<?php endif; ?>
									
								</div>
								
								<?php if ($this->warp->modules->count('logo')) : ?>		
								<div id="logo">
									<?php echo $this->warp->modules->render('logo'); ?>
								</div>
								<?php endif; ?>
								
								<?php  if (/*$active!=$default && */$this->warp->modules->count('menu + menuright')) : ?>
								<div id="menu">
									
									<?php if ($this->warp->modules->count('menu')) : ?>
									<div class="left">
										<?php echo $this->warp->modules->render('menu'); ?>
									</div>
									<?php endif; ?>
									
									<?php if ($this->warp->modules->count('menuright')) : ?>
									<div class="right">
										<?php echo $this->warp->modules->render('menuright'); ?>
									</div>
									<?php endif; ?>
									
								</div>
								<?php endif; ?>
								
								<?php if ($this->warp->modules->count('banner')) : ?>
								<div id="banner">
									<?php echo $this->warp->modules->render('banner'); ?>
								</div>
								<?php endif;  ?>

							</div>
							<!-- header end -->
							<?php if ($default!=$active) : ?>
							<div class="inner-wrapper-t1">
								<div class="inner-wrapper-t2">
									<div class="inner-wrapper-t3"></div>
								</div>
							</div>
							
							<div class="inner-wrapper-1">
								<div class="inner-wrapper-2">
									<div class="inner-wrapper-3">

										<?php if ($this->warp->modules->count('top + topblock')) : ?>
										<div id="top" <?php if (!$this->warp->modules->count('top')) echo 'class="no-line"'; ?>>
							
											<?php if($this->warp->modules->count('topblock')) : ?>
											<div class="topblock width100">
												<?php echo $this->warp->modules->render('topblock'); ?>
											</div>
											<?php endif; ?>
							
											<?php if ($this->warp->modules->count('top')) : ?>
												<?php echo $this->warp->modules->render('top', array('wrapper'=>"topbox float-left", 'layout'=>$this->warp->config->get('top'))); ?>
											<?php endif; ?>

										</div>
										<!-- top end -->
										<?php endif; ?>
										
										<div class="middle-wrapper">
											<div id="middle">
												<div id="middle-expand">
								
													<div id="main">
														<div id="main-shift">
															<div id="mainmiddle">
																<div id="mainmiddle-expand">															
																	<div id="content">
																		<div id="content-shift">
								
																			<?php if ($this->warp->modules->count('contenttop')) : ?>
																			<div id="contenttop">
																				<?php echo $this->warp->modules->render('contenttop', array('wrapper'=>"contenttopbox float-left", 'layout'=>$this->warp->config->get('contenttop'))); ?>
																			</div>
																			<!-- contenttop end -->
																			<?php endif; ?>
																			
																			<?php if ($this->warp->modules->count('breadcrumbs')) : ?>
																				<?php echo $this->warp->modules->render('breadcrumbs'); ?>
																			<?php endif; ?>
																			<div id="component" class="floatbox">
																				<?php echo $this->warp->template->render('content'); ?>
																			</div>
																			<?php if ($this->warp->modules->count('contentbottom')) : ?>
																			<div id="contentbottom">
																				<?php echo $this->warp->modules->render('contentbottom', array('wrapper'=>"contentbottombox float-left", 'layout'=>$this->warp->config->get('contentbottom'))); ?>
																			</div>
																			<!-- mainbottom end -->
																			<?php endif; ?>
																		
																		</div>
																	</div>
																	<!-- content end -->
																	
																	<?php if($this->warp->modules->count('contentleft')) : ?>
																	<div id="contentleft">
																		<?php echo $this->warp->modules->render('contentleft'); ?>
																	</div>
																	<?php endif; ?>
																	
																	<?php if($this->warp->modules->count('contentright')) : ?>
																	<div id="contentright">
																		<?php echo $this->warp->modules->render('contentright'); ?>
																	</div>
																	<?php endif; ?>
																	
																</div>
															</div>
															<!-- mainmiddle end -->												
														</div>
													</div>
								
													<?php if($this->warp->modules->count('left')) : ?>
													<div id="left">
														<?php echo $this->warp->modules->render('left'); ?>
													</div>
													<?php endif; ?>
													
													<?php if($this->warp->modules->count('right')) : ?>
													<div id="right">
														<?php echo $this->warp->modules->render('right'); ?>
													</div>
													<?php endif; ?>
								
												</div>
											</div>
										</div>
							
										<?php if ($this->warp->modules->count('bottom + bottomblock')) : ?>
										<div id="bottom" <?php if (!$this->warp->modules->count('bottom')) echo 'class="no-line"'; ?>>
											
											<?php if($this->warp->modules->count('bottomblock')) : ?>
											<div class="bottomblock width100">
												<?php echo $this->warp->modules->render('bottomblock'); ?>
											</div>
											<?php endif; ?>
							
											<?php if ($this->warp->modules->count('bottom')) : ?>
												<?php echo $this->warp->modules->render('bottom', array('wrapper'=>"bottombox float-left", 'layout'=>$this->warp->config->get('bottom'))); ?>
											<?php endif; ?>
						
										</div>
										<!-- bottom end -->
										<?php endif; ?>
									
									</div>
								</div>
							</div>
							
							<div class="inner-wrapper-b1">
								<div class="inner-wrapper-b2">
									<div class="inner-wrapper-b3"></div>
								</div>
							</div>
                            <?php else: ?>
                            <div class="clear dots"></div>
                            <?php endif; ?>
                            
							<?php if ($this->warp->modules->count('mainbottom')) : ?>
                            <div id="mainbottom">
                                <?php echo $this->warp->modules->render('mainbottom', array('wrapper'=>"mainbottombox float-left", 'layout'=>$this->warp->config->get('mainbottom'))); ?>
                            </div>
                            <!-- mainbottom end -->
                            <?php endif; ?>
							<!-- footer end -->
							
						</div>
					</div>
				</div>
			</div>
			
			<div class="wrapper-b1">
				<div class="wrapper-b2">
					<div class="wrapper-b3"></div>
				</div>
			</div>
			
		</div>
        </div>
	</div>
    <?php endif; ?>
    <div id="footer-container">
	    <div class="footer-top"></div>							
        <div id="footer">
			<?php if ($this->warp->modules->count('footer')) : ?>
                <?php echo $this->warp->modules->render('footer', array('wrapper'=>"footerbox float-left", 'layout'=>$this->warp->config->get('footer'))); ?>
            <?php endif; ?>
            <?php echo $this->warp->modules->render('debug'); ?>
            <div class="clear"></div>
            <a class="anchor" href="#page"></a>
        </div>        
    </div>
	
	<?php echo $this->render('footer'); ?>
</div>	
</body>
</html>