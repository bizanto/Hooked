<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 */
defined('_JEXEC') or die();

if( !empty( $events ) )
{
?>
<h3><?php echo JText::_('CC EVENTS UPCOMING');?></h3>
<ul class="cResetList clrfix">
	<?php foreach( $events as $event ){ ?>
	<li class="jomTips" title="<?php echo $this->escape( $event->title);?>::<?php echo CStringHelper::truncate( $this->escape( strip_tags($event->description) ) , $config->get('tips_desc_length') );?>">
		<div class="jsEvDate">
			<div class="jsDD"><?php echo CEventHelper::formatStartDate($event, JText::_('%d') ); ?></div>
			<div class="jsMM"><?php echo CEventHelper::formatStartDate($event, JText::_('%b') ); ?></div>
		</div>
		<div class="jsDetail" style="margin-left:45px">
			<div class="small">
				<b><a href="<?php echo $event->getLink();?>"><?php echo $this->escape( $event->title ); ?></a></b>
			</div>
			<div class="small">
				<?php echo $this->escape( $event->location );?>
			</div>
			<div class="small">
				<a href="<?php echo $event->getGuestLink( COMMUNITY_EVENT_STATUS_ATTEND );?>">
					<?php echo JText::sprintf((cIsPlural($event->confirmedcount)) ? 'CC GUESTS COUNT MANY2':'CC GUESTS COUNT2', $event->confirmedcount);?>
				</a>
			</div>
		</div>
		<div class="clr"></div>
	</li>
	<?php } ?>
</ul>
<div class="app-box-footer">
	<a href="<?php echo CRoute::_('index.php?option=com_community&view=events'); ?>"><?php echo JText::_('CC SHOW ALL'); ?></a>
</div>

<?php
}