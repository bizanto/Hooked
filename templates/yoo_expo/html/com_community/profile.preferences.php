<?php
/**
 * @package	JomSocial
 * @subpackage Core 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
?>


        <form method="post" action="<?php echo CRoute::getURI();?>" name="saveProfile">
        
        
        <?php if( $jConfig->getValue('sef') ){ ?>
        <div class="ctitle"><h2><?php echo JText::_('CC YOUR PROFILE URL'); ?></h2></div>
        <div class="cRow" style="padding: 5px 0 0;">
            <?php echo JText::sprintf('CC YOUR CURRENT PROFILE URL' , $prefixURL );?>
            
        </div>
        <?php }?>
        <div class="ctitle"><h2><?php echo JText::_('CC EDIT PREFERENCES'); ?></h2></div>
        <table class="formtable" cellspacing="1" cellpadding="0">
        <?php echo $beforeFormDisplay;?>
        <tr>
            <td class="key" style="width: 300px;">
                <label for="activityLimit" class="label title">
                    <?php echo JText::_('CC PREFERENCES ACTIVITY LIMIT'); ?>
                </label>
            </td>
            <td class="value">
                    <input type="text" id="activityLimit" name="activityLimit" value="<?php echo $params->get('activityLimit', 20 );?>" size="5" maxlength="3" />
            </td>
        </tr>
        <tr>
            <td class="key" style="width: 300px;">
                <label for="profileLikes" class="label title">
                    <?php echo JText::_('CC PROFILE LIKE ENABLE'); ?>
                </label>
            </td>
            <td class="value">
                    <input type="radio" value="1" id="profileLikes-yes" name="profileLikes" <?php if($params->get('profileLikes', 1) == 1)  { ?>checked="checked" <?php } ?>/>
                <label for="profileLikes-yes" class="lblradio"><?php echo JText::_('CC YES'); ?></label>
                
                <input type="radio" value="0" id="profileLikes-no" name="profileLikes" <?php if($params->get('profileLikes') == '0') { ?>checked="checked" <?php } ?>/>
                <label for="profileLikes-no" class="lblradio"><?php echo JText::_('CC NO'); ?></label>
            </td>
        </tr>
        <?php echo $afterFormDisplay;?>
        </table>
        
        <div style="text-align: center;"><input type="submit" class="button" value="<?php echo JText::_('CC BUTTON SAVE'); ?>" /></div>
        </form>
