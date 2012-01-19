<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @param	my	Current browser's CUser object.
 **/
defined('_JEXEC') or die();
?>

<?php if ($firstLogin) { ?>
<div class="skipLink">
	<a href="<?php echo $skipLink; ?>"class="saveButton"><span><?php echo JText::_('CC SKIP UPLOAD AVATAR'); ?></span></a>
</div>
<?php } ?>

<!-- JS and CSS for imagearea selection -->
<link rel="stylesheet" type="text/css" href="<?php echo JURI::root(); ?>components/com_community/assets/imgareaselect/css/imgareaselect-default.css" />
<script type="text/javascript" src="<?php echo JURI::root(); ?>components/com_community/assets/imgareaselect/scripts/jquery.imgareaselect.js"></script>


<div class="cLayout clrfix">
    <div class="cSidebar clrfix">
        <?php $this->renderModules( 'joms-left' ); ?>
    </div>
    <div class="cMain">


                <div class="cModule">
                
                    <p class="info"><?php echo JText::_('CC UPLOAD NEW PICTURE DESCRIPTION'); ?></p>
                    <form name="jsform-profile-uploadavatar" action="<?php echo CRoute::getURI(); ?>" id="uploadForm" method="post" enctype="multipart/form-data">
                        <input class="inputbox button" type="file" id="file-upload" name="Filedata" />
                        <input class="button" size="30" type="submit" id="file-upload-submit" value="<?php echo JText::_('CC BUTTON UPLOAD PICTURE'); ?>">
                        <input type="hidden" name="action" value="doUpload" />
                        <input type="hidden" name="profileType" value="<?php echo $profileType;?>" />
                    </form>
                    <?php if( $uploadLimit != 0 ){ ?>
                    <p class="info"><?php echo JText::sprintf('CC MAX FILE SIZE FOR UPLOAD' , $uploadLimit ); ?></p>
                    <?php } ?>
                    <div style="margin-top: 15px;"><a href="javascript:void(0);" onclick="joms.profile.confirmRemoveAvatar();"><?php echo JText::_('CC REMOVE PROFILE PICTURE');?></a></div>
                </div>
                
                
                <div class="cModule avatarPreview leftside">	
                    <h3><?php echo JText::_('CC PICTURE LARGE HEADING');?></h3>
                
                    <p><?php echo JText::_('CC LARGE PICTURE DESCRIPTION'); ?></p>
                    
                    <div class="imagePreview">
                        <img id="large-profile-pic" src="<?php echo $user->getAvatar();?>" alt="<?php echo JText::_('CC LARGE PICTURE DESCRIPTION'); ?>" title="<?php echo JText::_('CC LARGE PICTURE DESCRIPTION'); ?>" />
                    </div>
                    <p><a href="javascript:updateThumbnail()" id="update-thumbnail"><?php echo JText::_('CC UPDATE THUMBNAIL'); ?></a><br />
                    </p>
                    <div id="update-thumbnail-guide" style="display: none;"><?php echo JText::_('CC UPDATE THUMBNAIL GUIDE'); ?></div>
                </div>
                
                <div class="cModule avatarPreview rightside">		
                    <h3><?php echo JText::_('CC PICTURE THUMB HEADING');?></h3>
                    
                    <p><?php echo JText::_('CC SMALL PICTURE DESCRIPTION'); ?></p>
                    
                    <div class="imagePreview">		
                        <img id="thumbnail-profile-pic" src="<?php echo $user->getThumbAvatar();?>" alt="<?php echo JText::_('CC SMALL PICTURE DESCRIPTION'); ?>" title="<?php echo JText::_('CC SMALL PICTURE DESCRIPTION'); ?>" />
                    </div>
                    
                    
                </div>
                
                <!-- Start thumbnail selection -->
                <script type="text/javascript">
                joms.jQuery(document).ready(function () { 
                    joms.jQuery('#large-profile-pic').imgAreaSelect(
                        { maxWidth: 160, maxHeight: 160, handles: true ,aspectRatio: '1:1',
                          x1: 0, y1: 0, x2: 160, y2: 160,
                          minHeight:<?php echo COMMUNITY_SMALL_AVATAR_WIDTH; ?>, minWidth:<?php echo COMMUNITY_SMALL_AVATAR_WIDTH; ?> });
                });
                
                function saveThumbnail(){
                    joms.jQuery( '#update-thumbnail-guide' ).hide();
                    joms.jQuery('#large-profile-pic').imgAreaSelect({ hide: true , disable: true });
                }
                
                function updateThumbnail(){
                    var ias = joms.jQuery('#large-profile-pic').imgAreaSelect({ instance: true , hide: false , disable: false });
                    var obj = ias.getSelection();
                    var hideSave	= joms.jQuery( '#update-thumbnail-guide' ).css('display') == 'none' ? false : true;
                    jax.call('community', 'profile,ajaxUpdateThumbnail', obj.x1, obj.y1, obj.width, obj.height , hideSave );
                }
                
                function refreshThumbnail(){
                    var src = joms.jQuery('#thumbnail-profile-pic').attr('src');
                    joms.jQuery('#thumbnail-profile-pic').attr('src', src+'?'+Math.random());
                }
                </script>
    </div>
</div>