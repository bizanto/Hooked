<?php
/**
 * @category	Core
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<fieldset class="adminform">
	<legend><?php echo JText::_( 'CC EVENTS' ); ?></legend>
	<table class="admintable" cellspacing="1">
		<tbody>
			<tr>
				<td width="350" class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC ENABLE EVENTS' ); ?>::<?php echo JText::_('CC ENABLE EVENT TIPS'); ?>">
						<?php echo JText::_( 'CC ENABLE EVENTS' ); ?>
					</span>
				</td>
				<td valign="top">
					<?php echo JHTML::_('select.booleanlist' , 'enableevents' , null , $this->config->get('enableevents') , JText::_('CC YES') , JText::_('CC NO') ); ?>
				</td>
			</tr>
			<tr>
				<td width="350" class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC ENABLE EVENTS SEARCH' ); ?>::<?php echo JText::_('CC ENABLE EVENTS SEARCH TIPS'); ?>">
						<?php echo JText::_( 'CC ENABLE EVENTS SEARCH' ); ?>
					</span>
				</td>
				<td valign="top">
					<?php echo JHTML::_('select.booleanlist' , 'enableguestsearchevents' , null , $this->config->get('enableguestsearchevents') , JText::_('CC YES') , JText::_('CC NO') ); ?>
				</td>
			</tr>
			<tr>
				<td width="350" class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC ENABLE EVENT MODERATION' ); ?>::<?php echo JText::_('CC ENABLE EVENT MODERATION TIPS'); ?>">
						<?php echo JText::_( 'CC ENABLE EVENT MODERATION' ); ?>
					</span>
				</td>
				<td valign="top">
					<?php echo JHTML::_('select.booleanlist' , 'event_moderation' , null , $this->config->get('event_moderation') , JText::_('CC YES') , JText::_('CC NO') ); ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC ALLOW EVENTS CREATION' ); ?>::<?php echo JText::_('CC ALLOW EVENTS CREATION TIPS'); ?>">
						<?php echo JText::_( 'CC ALLOW EVENTS CREATION' ); ?>
					</span>
				</td>
				<td valign="top">
					<?php echo JHTML::_('select.booleanlist' , 'createevents' , null , $this->config->get('createevents') , JText::_('CC YES') , JText::_('CC NO') ); ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC EVENT CREATION LIMIT' ); ?>::<?php echo JText::_('CC EVENT CREATION LIMIT TIPS'); ?>">
						<?php echo JText::_( 'CC EVENT CREATION LIMIT' ); ?>
					</span>
				</td>
				<td valign="top">
					<input type="text" name="eventcreatelimit" value="<?php echo $this->config->get('eventcreatelimit' );?>" size="10" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC ENABLE ICAL EXPORT' ); ?>::<?php echo JText::_('CC EVENT CREATION LIMIT TIPS'); ?>">
						<?php echo JText::_( 'CC ENABLE ICAL EXPORT' ); ?>
					</span>
				</td>
				<td valign="top">
					<?php echo JHTML::_('select.booleanlist' , 'eventexportical' , null , $this->config->get('eventexportical') , JText::_('CC YES') , JText::_('CC NO') ); ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC ENABLE ICAL IMPORT' ); ?>::<?php echo JText::_('CC ENABLE ICAL IMPORT TIPS'); ?>">
						<?php echo JText::_( 'CC ENABLE ICAL IMPORT' ); ?>
					</span>
				</td>
				<td valign="top">
					<?php echo JHTML::_('select.booleanlist' , 'event_import_ical' , null , $this->config->get('event_import_ical') , JText::_('CC YES') , JText::_('CC NO') ); ?>
				</td>
			</tr>	
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC EVENT SHOW MAP' ); ?>::<?php echo JText::_('CC EVENT SHOW MAP TIPS'); ?>">
						<?php echo JText::_( 'CC EVENT SHOW MAP' ); ?>
					</span>
				</td>
				<td valign="top">
					<?php echo JHTML::_('select.booleanlist' , 'eventshowmap' , null , $this->config->get('eventshowmap') , JText::_('CC YES') , JText::_('CC NO') ); ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC EVENT NEARBY RADIUS' ); ?>::<?php echo JText::_('CC EVENT NEARBY RADIUS TIPS'); ?>">
						<?php echo JText::_( 'CC EVENT NEARBY RADIUS' ); ?>
					</span>
				</td>
				<td valign="top">
					<select name="event_nearby_radius">
							<option value="<?php echo COMMUNITY_EVENT_WITHIN_5; ?>"<?php echo ( $this->config->get('event_nearby_radius') == COMMUNITY_EVENT_WITHIN_5 ) ? ' selected="true"' : '';?>><?php echo JText::_('CC EVENT 5 MILES');?></option>
							<option value="<?php echo COMMUNITY_EVENT_WITHIN_10; ?>"<?php echo ( $this->config->get('event_nearby_radius') == COMMUNITY_EVENT_WITHIN_10 ) ? ' selected="true"' : '';?>><?php echo JText::_('CC EVENT 10 MILES');?></option>
							<option value="<?php echo COMMUNITY_EVENT_WITHIN_20; ?>"<?php echo ( $this->config->get('event_nearby_radius') == COMMUNITY_EVENT_WITHIN_20 ) ? ' selected="true"' : '';?>><?php echo JText::_('CC EVENT 20 MILES');?></option>
							<option value="<?php echo COMMUNITY_EVENT_WITHIN_50; ?>"<?php echo ( $this->config->get('event_nearby_radius') == COMMUNITY_EVENT_WITHIN_50 ) ? ' selected="true"' : '';?>><?php echo JText::_('CC EVENT 50 MILES');?></option>
					</select>
				</td>
			</tr>
			
		</tbody>
	</table>
</fieldset>

<fieldset class="adminform">
	<legend><?php echo JText::_( 'CC EVENT LEGEND TIME FORMAT' ); ?></legend>	
	<table class="admintable" cellspacing="1">
		<tbody>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC EVENT LISTING DATE FORMAT' ); ?>::<?php echo JText::_('CC EVENT LISTING DATE FORMAT TIPS'); ?>">
						<?php echo JText::_( 'CC EVENT LISTING DATE FORMAT' ); ?>
					</span>
				</td>
				<td valign="top">
					<select name="eventdateformat">
							<option value="%b %d"<?php echo ( $this->config->get('eventdateformat') == '%b %d' ) ? ' selected="true"' : '';?>><?php echo JText::_('CC EVENT MONTH DAY FORMAT');?></option>
							<option value="%d %b"<?php echo ( $this->config->get('eventdateformat') == '%d %b' ) ? ' selected="true"' : '';?>><?php echo JText::_('CC EVENT DAY MONTH FORMAT');?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC EVENT TIME FORMAT' ); ?>::<?php echo JText::_('CC EVENT TIME FORMAT TIPS'); ?>">
						<?php echo JText::_( 'CC EVENT TIME FORMAT' ); ?>
					</span>
				</td>
				<td valign="top">
					<select name="eventshowampm">
							<option value="1"<?php echo ( $this->config->get('eventshowampm') == '1' ) ? ' selected="true"' : '';?>><?php echo JText::_('CC EVENT SHOW AMPM');?></option>
							<option value="0"<?php echo ( $this->config->get('eventshowampm') == '0' ) ? ' selected="true"' : '';?>><?php echo JText::_('CC EVENT SHOW 2400');?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="key">
					<span class="hasTip" title="<?php echo JText::_( 'CC SHOW EVENT TIMEZONE' ); ?>::<?php echo JText::_('CC SHOW EVENT TIMEZONE TIPS'); ?>">
						<?php echo JText::_( 'CC SHOW EVENT TIMEZONE' ); ?>
					</span>
				</td>
				<td valign="top">
					<?php echo JHTML::_('select.booleanlist' , 'eventshowtimezone' , null , $this->config->get('eventshowtimezone') , JText::_('CC YES') , JText::_('CC NO') ); ?>
				</td>
			</tr>
			
		</tbody>
	</table>
</fieldset>