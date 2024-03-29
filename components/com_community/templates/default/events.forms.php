<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @params	categories Array	An array of categories
 */
defined('_JEXEC') or die();
?>
<style type="text/css">
div#community-wrap .calendar{
	vertical-align: middle; 
	padding-left: 4px;
	padding-right:4px; 
	border: medium none;
}
</style>

<form method="post" action="<?php echo CRoute::getURI(); ?>" id="createEvent" name="createEvent" class="community-form-validate">
<script type="text/javascript">
function saveContent()
{
	<?php echo $editor->save( 'description' ); ?>
	return true;
}
</script>
<div id="community-events-wrap">
<?php if(!$event->id && $eventcreatelimit != 0 ) { ?>
	<div class="hints">
		<?php echo JText::sprintf('CC EVENT CREATION LIMIT STATUS', $eventCreated, $eventcreatelimit );?>
	</div>
<?php } ?>
	<table class="formtable" cellspacing="1" cellpadding="0">
	<!-- events name -->
	<tr>
		<td class="key">
			<label for="title" class="label title jomTips" title="<?php echo JText::_('CC EVENTS TITLE');?>::<?php echo JText::_('CC EVENTS TITLE TIPS'); ?>">
				*<?php echo JText::_('CC EVENTS TITLE'); ?>
			</label>
		</td>
		<td class="value">
			<input name="title" id="title" type="text" size="45" maxlength="255" class="required inputbox" value="<?php echo $this->escape($event->title); ?>" />
		</td>
	</tr>
	<!-- events description -->
	<tr>
		<td class="key">
			<label for="description" class="label title jomTips" title="<?php echo JText::_('CC EVENTS DESCRIPTION');?>::<?php echo JText::_('CC EVENTS DESCRIPTION TIPS');?>">
				<?php echo JText::_('CC EVENTS DESCRIPTION');?>
			</label>
		</td>
		<td class="value">
			<?php if( $config->get( 'htmleditor' ) == 'none' && $config->getBool('allowhtml') ) { ?>
   				<div class="htmlTag"><?php echo JText::_('CC HTML TAGS ALLOWED');?></div>
			<?php } ?>
			
			<?php
			if( !CStringHelper::isHTML($event->description) 
				&& $config->get('htmleditor') != 'none' 
				&& $config->getBool('allowhtml') )
			{
				$event->description = CStringHelper::nl2br($event->description);
			}
			
			?>
			<?php echo $editor->display( 'description',  $event->description , '95%', '350', '10', '20' , false ); ?>
	
		</td>
	</tr>
	<!-- events category -->
	<tr>
		<td class="key">
			<label for="catid" class="label title jomTips" title="<?php echo JText::_('CC EVENTS CATEGORY');?>::<?php echo JText::_('CC EVENTS CATEGORY TIPS');?>">
				*<?php echo JText::_('CC EVENTS CATEGORY');?>
			</label>
		</td>
		<td class="value">
			<?php echo $lists['categoryid']; ?>
		</td>
	</tr>
	<!-- events location -->
	<tr>
		<td class="key">
			<label for="location" class="label title jomTips" title="<?php echo JText::_('CC EVENTS LOCATION');?>::<?php echo JText::_('CC EVENTS LOCATION TIPS'); ?>">
				*<?php echo JText::_('CC EVENTS LOCATION'); ?>
			</label>
		</td>
		<td class="value">
			<input name="location" id="location" type="text" size="45" maxlength="255" class="required inputbox" value="<?php echo $this->escape($event->location); ?>" />
			<div class="small">
				<?php echo JText::_('CC EVENTS LOCATION DESCRIPTION');?>
			</div>
		</td>
	</tr>	
	<!-- events start datetime -->
	<tr>
		<td class="key">
			<label class="label title jomTips" title="<?php echo JText::_('CC EVENTS START TIME');?>::<?php echo JText::_('CC EVENTS START TIME TIPS'); ?>">
				*<?php echo JText::_('CC EVENTS START TIME'); ?>
			</label>
		</td>
		<td class="value">			
			<span>
				<?php echo JHTML::_('calendar',  $startDate->toFormat( '%Y-%m-%d' ) , 'startdate', 'startdate', '%Y-%m-%d', array('class'=>'required inputbox', 'size'=>'10',  'maxlength'=>'10' , 'readonly' => 'true') );?>
				<?php echo $startHourSelect; ?>:<?php  echo $startMinSelect; ?> <?php echo $startAmPmSelect;?>
			</span>
		</td>
	</tr>
	<!-- events end datetime -->
	<tr>
		<td class="key">
			<label class="label title jomTips" title="<?php echo JText::_('CC EVENTS END TIME');?>::<?php echo JText::_('CC EVENTS END TIME TIPS'); ?>">
				*<?php echo JText::_('CC EVENTS END TIME'); ?>
			</label>
		</td>
		<td class="value">			
			<span>
				<?php echo JHTML::_('calendar',  $endDate->toFormat( '%Y-%m-%d' ) , 'enddate', 'enddate', '%Y-%m-%d', array('class'=>'required inputbox', 'size'=>'10',  'maxlength'=>'10' , 'readonly' => 'true') );?>
				<?php echo $endHourSelect; ?>:<?php echo $endMinSelect; ?> <?php echo $endAmPmSelect;?>
			</span>
		</td>
	</tr>
	<?php
	if( $config->get('eventshowtimezone') )
	{
	?>
	<tr>
		<td class="key">
			<label class="label title jomTips" title="<?php echo JText::_('CC TIMEZONE');?>::<?php echo JText::_('CC EVENTS SET TIMEZONE'); ?>">
				*<?php echo JText::_('CC TIMEZONE'); ?>
			</label>
		</td>
		<td class="value">			
			<span>
				<select name="offset">
				<?php
				foreach( $timezones as $offset => $value ){
				?>
					<option value="<?php echo $offset;?>"<?php echo $event->offset == $offset ? ' selected="selected"' : '';?>><?php echo $value;?></option>
				<?php
				}
				?>
				</select>
			</span>
		</td>
	</tr>
	<?php
	}
	?>	
	<?php
	if( $helper->hasPrivacy() )
	{
	?>
	<!-- events type -->
	<tr>
		<td class="key">
			<label class="label title jomTips" title="<?php echo JText::_('CC EVENTS TYPE');?>::<?php echo JText::_('CC EVENTS TYPE TIPS');?>">
				<?php echo JText::_('CC EVENTS TYPE'); ?>
			</label>
		</td>
		<td class="value">
			<div>
				<input type="radio" name="permission" id="permission-open" value="0"<?php echo ($event->permission == COMMUNITY_PUBLIC_EVENT ) ? ' checked="checked"' : '';?> />
				<label for="permission-open" class="label lblradio"><?php echo JText::_('CC OPEN EVENTS');?></label>
			</div>
			<div class="small">
				<?php echo JText::_('CC OPEN EVENTS DESCRIPTION');?>
			</div>
			
			<div>
				<input type="radio" name="permission" id="permission-private" value="1"<?php echo ($event->permission == COMMUNITY_PRIVATE_EVENT ) ? ' checked="checked"' : '';?> />
				<label for="permission-private" class="label lblradio"><?php echo JText::_('CC PRIVATE EVENTS');?></label>
			</div>
			<div class="small">
				<?php echo JText::_('CC PRIVATE EVENTS DESCRIPTION');?>
			</div>
		</td>
	</tr>
	<?php
	}
	?>
	<!-- events tickets -->
	<tr>
		<td class="key">
			<label for="ticket" class="label title jomTips" title="<?php echo JText::_('CC EVENTS NUM TICKET');?>::<?php echo JText::_('CC EVENTS NUM TICKET DESCRIPTION'); ?>">
				*<?php echo JText::_('CC EVENTS NUM TICKET'); ?>
			</label>
		</td>
		<td class="value">
			<input name="ticket" id="ticket" type="text" size="10" maxlength="5" class="required inputbox" value="<?php echo (empty($event->ticket)) ? '0' : $this->escape($event->ticket); ?>" />
			<div class="small">
				<?php echo JText::_('CC EVENTS NUM TICKET DESCRIPTION');?>
			</div>
		</td>
	</tr>
	<?php
	if( $helper->hasInvitation() )
	{
	?>	
	<!-- events allow guest to invite -->
	<tr>
		<td class="key">
			<label class="label title jomTips" title="<?php echo JText::_('CC EVENTS ALLOW GUEST INVITE');?>::<?php echo JText::_('CC EVENTS ALLOW GUEST INVITE TIPS'); ?>">
				*<?php echo JText::_('CC EVENTS ALLOW GUEST INVITE'); ?>
			</label>
		</td>
		<td class="value">
			<!-- <?php echo JHTML::_('select.booleanlist', 'allowinvite', 'class="inputbox"', $event->allowinvite ); ?> -->
			<div>
				<input type="radio" name="allowinvite" id="allowinvite0" value="1"<?php echo ($event->allowinvite ) ? ' checked="checked"' : '';?> />
				<label for="allowinvite0" class="label lblradio"><?php echo JText::_('CC YES');?></label>
			</div>
			<div class="small">
				<?php echo JText::_('CC EVENTS ALLOW INVITE DESCRIPTION');?>
			</div>
			
			<div>
				<input type="radio" name="allowinvite" id="allowinvite1" value="0"<?php echo (!$event->allowinvite ) ? ' checked="checked"' : '';?> />
				<label for="allowinvite1" class="label lblradio"><?php echo JText::_('CC NO');?></label>
			</div>
			<div class="small">
				<?php echo JText::_('CC EVENTS DISALLOW INVITE DESCRIPTION');?>
			</div>
			
		</td>
	</tr>
	<?php
	}
	?>
	<tr>
			<td class="key"></td>
			<td class="value"><span class="hints"><?php echo JText::_( 'CC_REG_REQUIRED_FILEDS' ); ?></span></td>
		</tr>
	
	<!-- event buttons -->
	<tr>
		<td class="key"></td>
		<td class="value">
			<?php echo JHTML::_( 'form.token' ); ?>
			<?php if(!$event->id): ?>
			<input name="action" type="hidden" value="save" />
			<?php endif;?>
			<input type="hidden" name="eventid" value="<?php echo $event->id;?>" />
			<input type="submit" value="<?php echo ($event->id) ? JText::_('CC BUTTON SAVE') : JText::_('CC BUTTON CREATE EVENTS');?>" class="button validateSubmit" onclick="saveContent();" />
			<input type="button" class="button" onclick="history.go(-1);return false;" value="<?php echo JText::_('CC BUTTON CANCEL');?>" />
		</td>
	</tr>
	</table>
</div>
</form>
<script type="text/javascript">
	cvalidate.init();
	cvalidate.setSystemText('REM','<?php echo addslashes(JText::_("CC REQUIRED ENTRY MISSING")); ?>');
	cvalidate.noticeTitle	= '<?php echo addslashes(JText::_('CC NOTICE') );?>';
	
	/*
		The calendar.js does not display properly under IE when a page has been
		scrolled down. This behaviour is present everywhere within the Joomla site.
		We are injecting our fixes into their code by adding the following
		at the end of the fixPosition() function:
		if (joms.jQuery(el).parents('#community-wrap').length>0)
		{
			var anchor   = joms.jQuery(el);
			var calendar = joms.jQuery(self.element);
			box.x = anchor.offset().left - calendar.outerWidth() + anchor.outerWidth();
			box.y = anchor.offset().top - calendar.outerHeight();
		}
		Unobfuscated version of "JOOMLA/media/system/js/calendar.js" was taken from
		http://www.dynarch.com/static/jscalendar-1.0/calendar.js for reference.		
	*/
	joms.jQuery(document).ready(function()
	{
		Calendar.prototype.showAtElement=function(c,d){var a=this;var e=Calendar.getAbsolutePos(c);if(!d||typeof d!="string"){this.showAt(e.x,e.y+c.offsetHeight);return true}function b(j){if(j.x<0){j.x=0}if(j.y<0){j.y=0}var l=document.createElement("div");var i=l.style;i.position="absolute";i.right=i.bottom=i.width=i.height="0px";document.body.appendChild(l);var h=Calendar.getAbsolutePos(l);document.body.removeChild(l);if(Calendar.is_ie){h.y+=document.body.scrollTop;h.x+=document.body.scrollLeft}else{h.y+=window.scrollY;h.x+=window.scrollX}var g=j.x+j.width-h.x;if(g>0){j.x-=g}g=j.y+j.height-h.y;if(g>0){j.y-=g}if(joms.jQuery(c).parents("#community-wrap").length>0){var f=joms.jQuery(c);var k=joms.jQuery(a.element);j.x=f.offset().left-k.outerWidth()+f.outerWidth();j.y=f.offset().top-k.outerHeight()}}this.element.style.display="block";Calendar.continuation_for_the_fucking_khtml_browser=function(){var f=a.element.offsetWidth;var i=a.element.offsetHeight;a.element.style.display="none";var g=d.substr(0,1);var j="l";if(d.length>1){j=d.substr(1,1)}switch(g){case"T":e.y-=i;break;case"B":e.y+=c.offsetHeight;break;case"C":e.y+=(c.offsetHeight-i)/2;break;case"t":e.y+=c.offsetHeight-i;break;case"b":break}switch(j){case"L":e.x-=f;break;case"R":e.x+=c.offsetWidth;break;case"C":e.x+=(c.offsetWidth-f)/2;break;case"l":e.x+=c.offsetWidth-f;break;case"r":break}e.width=f;e.height=i+40;a.monthsCombo.style.display="none";b(e);a.showAt(e.x,e.y)};if(Calendar.is_khtml){setTimeout("Calendar.continuation_for_the_fucking_khtml_browser()",10)}else{Calendar.continuation_for_the_fucking_khtml_browser()}};		
	});	
</script>