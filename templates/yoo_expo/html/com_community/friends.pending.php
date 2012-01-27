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
<div class="cLayout clrfix">
            <div class="cMain">
        <div id="request-notice"></div>
        <?php
        if ( $rows ) {
            foreach( $rows as $row )
            {
        ?>
        <div id="pending-<?php echo $row->connection_id; ?>" class="mini-profile jsFriendPending">
            <div class="mini-profile-avatar">
                <a href="<?php echo $row->user->profileLink; ?>"><img class="avatar" src="<?php echo $row->user->getThumbAvatar(); ?>" alt="<?php echo $row->user->getDisplayName(); ?>" /></a>
            </div>
            
            <div class="mini-profile-details jsRel">
                <h3 class="name">
                    <a href="<?php echo $row->user->profileLink; ?>"><strong><?php echo $row->user->getDisplayName(); ?></strong></a>
                </h3>
                <?php if(!empty($row->msg)) { ?>
                <div class="mini-profile-details-status" style="padding-bottom:30px">
                    <?php echo $row->msg; ?>
                </div>
                <?php } ?>
            </div>
            
            <div class="mini-profile-details-action jsAbs jsFriendAction">
                <span class="icon-group">
                    <?php echo JText::sprintf( (CStringHelper::isPlural($row->user->friendsCount)) ? 'CC FRIENDS COUNT MANY' : 'CC FRIENDS COUNT' , $row->user->friendsCount);?>
                </span>
        
                <?php if( $my->id != $row->user->id && $config->get('enablepm') ): ?>
                <span class="icon-write">
                    <a onclick="joms.messaging.loadComposeWindow(<?php echo $row->user->id; ?>)" href="javascript:void(0);">
                    <?php echo JText::_('CC WRITE MESSAGE'); ?>
                    </a>
                </span>
                <?php endif; ?>
            </div>
            
            <div class="jsAbs jsFriendRespond">
                <input type="submit" class="button" style="margin:0" onclick="jax.call('community' , 'friends,ajaxApproveRequest' , '<?php echo $row->connection_id; ?>');" value="<?php echo JText::_('CC PENDING ACTION APPROVE'); ?>" />
                or
                <a href="javascript:void(0);" onclick="jax.call('community','friends,ajaxRejectRequest','<?php echo $row->connection_id; ?>');">
                    <?php echo JText::_('CC REMOVE'); ?>
                </a>
            </div>
        </div>
    </div>
        
            <?php if($row->user->isOnline()): ?>
            <span class="icon-online-overlay">
                <?php echo JText::_('CC ONLINE'); ?>
            </span>
            <?php endif; ?>
            
            <div class="clr"></div>
        
        </div>
        <?php
            }
        ?>
            <div class="pagination-container">
            <?php echo $pagination->getPagesLinks(); ?>
            </div>
        <?php
        }
        else {
        ?>
        <div class="community-empty-list">
            <?php echo JText::_('CC PENDING APPROVAL EMPTY'); ?>
        </div>
        <?php } ?>