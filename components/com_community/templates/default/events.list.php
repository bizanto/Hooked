<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @params	groups		An array of events objects.
 */
defined('_JEXEC') or die();

if( $events )
{
	for( $i = 0; $i < count( $events ); $i++ )
	{
		$event				=& $events[$i];
?>
	<div class="community-events-results-item">
		<div class="community-events-results-left">
			<a href="<?php echo $event->getLink();?>"><img class="avatar" src="<?php echo $event->getThumbAvatar();?>" border="0" alt="<?php echo $this->escape($event->title); ?>"/></a>
			<div class="eventDate"><?php echo CEventHelper::formatStartDate($event, $config->get('eventdateformat') ); ?></div>
		</div>
		<div class="community-events-results-right">
			<h3 class="eventName">
				<a href="<?php echo $event->getLink();?>"><?php echo $this->escape($event->title); ?></a>
			</h3>
			<div class="eventLocation"><?php echo $this->escape($event->location);?></div>
            <div class="eventTime"><?php echo JText::sprintf('CC EVENT TIME', JHTML::_('date', $event->getStartDate( false )->toMySQL() , JText::_('DATE_FORMAT_LC2') , '' ), JHTML::_('date', $event->getEndDate( false )->toMySQL() , JText::_('DATE_FORMAT_LC2') , '' )); ?></div>
			<div class="eventActions">
				<span class="icon-group" style="margin-right: 5px;">
					<a href="<?php echo $event->getGuestLink( COMMUNITY_EVENT_STATUS_ATTEND );?>"><?php echo JText::sprintf((cIsPlural($event->confirmedcount)) ? 'CC GUESTS COUNT MANY':'CC GUESTS COUNT', $event->confirmedcount);?></a>
				</span>
			</div>
		</div>
		<?php if( $isExpired || CEventHelper::isPast($event) ) { ?>
		    <span class="icon-offline-overlay">&nbsp;<?php echo JText::_('CC EVENTS PAST'); ?>&nbsp;</span>
		<?php } else if(CEventHelper::isToday($event)) { ?>
			<span class="icon-online-overlay">&nbsp;<?php echo JText::_('CC EVENTS ONGOING'); ?>&nbsp;</span>
		<?php }?>
		<div style="clear: both;"></div>
	</div>
<?php
	}
} else {
?>
	<div class="event-not-found"><?php echo JText::_('CC NO EVENTS FOUND'); ?></div>
<?php } ?>

<?php if (!is_null($pagination)) {?>
<div class="pagination-container">
	<?php echo $pagination->getPagesLinks(); ?>
</div>
<?php }?>