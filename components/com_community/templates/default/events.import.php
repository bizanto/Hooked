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
<form name="jsforms-events-import" action="<?php echo CRoute::getURI();?>" method="post" enctype="multipart/form-data">
<div class="ctitle">
	<h2><?php echo JText::_('CC EVENTS IMPORT ICAL DESCRIPTION');?></h2>
</div>
<div class="jsiCalOption">
	<ul class="cResetList">
		<li class="jsiCalSel">
			<input type="radio" id="upload" name="type" checked="checked" onclick="joms.events.switchImport('file');" class="jsFlLf jsReset" />
			<label for="upload"><?php echo JText::_('CC EVENTS IMPORT LOCAL');?></label>
		</li>
		<li class="jsiCalSel">
			<input type="radio" id="link" name="type" onclick="joms.events.switchImport('url');" class="jsFlLf jsReset" />
			<label for="link"><?php echo JText::_('CC EVENTS IMPORT EXTERNAL');?></label>
		</li>
		<li id="event-import-file">
			<input type="file" name="file" style="width: 200px;" />
		</li>
		<li id="event-import-url" style="display: none;">
			<input type="text" name="url" style="width: 200px;" />
		</li>
		<li>
			<input type="submit" value="<?php echo JText::_('CC EVENTS IMPORT');?>" class="button" />
			<input type="hidden" value="file" name="type" id="import-type" />
			<span><?php echo JText::_('CC EVENTS IMPORT FORMAT ERROR');?></span>
		</li>
	</ul>
</div>
</form>
<?php if( $events ) { ?>
<form action="<?php echo CRoute::_('index.php?option=com_community&view=events&task=saveImport');?>" method="post">
	<div class="ctitle" style="padding-top:30px !important">
		<?php echo JText::_('CC AVAILABLE EVENTS EXPORTED');?>
	</div>
	<p><?php echo JText::_('CC EVENTS IMPORT SELECT');?></p>
	<ul class="jsiCal cResetList">
		<?php
		$i	= 1;
		foreach($events as $event){
		?>
		<li>
			<div class="jsiCalHead jsRel">
				<span class="jsAbs">
				<input type="checkbox" name="events[]" id="event-<?php echo $i;?>" value="<?php echo $i;?>" class="jsReset" />
				</span>
				<label for="event-<?php echo $i;?>"><?php echo $event->getTitle();?></label>
			</div>
			<div class="jsiCalDesc">
			<?php if ( $event->getDescription() ) {?>
				<p><?php echo $event->getDescription();?></p>
			<?php } else { ?>
				<p><?php echo JText::_('CC EVENTS DESCRIPTION ERROR');?></p>
			<?php } ?>
			</div>
			<div class="jsiCalDetail">
				<div class="clrfix">
					<span class="jsiCalLabel jsFlLf small"><?php echo JText::_('CC EVENTS START TIME');?></span>
					<div class="jsiCalData small">: <?php echo $event->getStartDate();?></div>
				</div>
				<div class="clrfix">
					<span class="jsiCalLabel jsFlLf small"><?php echo JText::_('CC EVENTS END TIME');?></span>
					<div class="jsiCalData small">: <?php echo $event->getEndDate();?></div></div>
				<div class="clrfix">
					<span class="jsiCalLabel jsFlLf small"><?php echo JText::_('CC EVENTS LOCATION');?></span>
					<div class="jsiCalData small">: <?php echo ( $event->getLocation() != '' ) ? $event->getLocation() : JText::_('CC EVENTS LOCATION NOT AVAILABLE');?></div>
				</div>
				<div class="clrfix">
					<span class="jsiCalLabel jsFlLf small"><?php echo JText::_('CC EVENTS CATEGORY');?></span>
					<div class="jsiCalData small">
						<select name="event-<?php echo $i;?>-catid" id="event-<?php echo $i;?>-catid" class="required inputbox">
						<?php
						foreach( $categories as $category )
						{
						?>
							<option value="<?php echo $category->id; ?>"><?php echo JText::_( $this->escape($category->name) ); ?></option>
						<?php
						}
						?>
						</select>
					</div>
				</div>
				<div class="clrfix">
					<span class="jsiCalLabel jsFlLf small"><?php echo JText::_('CC EVENTS ALLOW GUEST INVITE');?></span>
					<div class="jsiCalData small">
						<input type="radio" name="event-<?php echo $i;?>-invite" id="event-<?php echo $i;?>-invite-allowed" value="1" checked="checked" />
						<label for="event-<?php echo $i;?>-invite-allowed" class="label lblradio"><?php echo JText::_('CC YES');?></label>
						<input type="radio" name="event-<?php echo $i;?>-invite" id="event-<?php echo $i;?>-invite-disallowed" value="0" />
						<label for="event-<?php echo $i;?>-invite-disallowed" class="label lblradio"><?php echo JText::_('CC NO');?></label>
					</div>
				</div>
				<div class="clrfix">
					<span class="jsiCalLabel jsFlLf small"><?php echo JText::_('CC EVENTS TYPE'); ?></span>
					<div class="jsiCalData small">
						<input type="radio" name="event-<?php echo $i;?>-permission" id="event-<?php echo $i;?>-permission-open" value="0" checked="checked" />
						<label for="event-<?php echo $i;?>-permission-open" class="label lblradio"><?php echo JText::_('CC OPEN EVENTS');?></label>
						<input type="radio" name="event-<?php echo $i;?>-permission" id="event-<?php echo $i;?>-permission-private" value="1" />
						<label for="event-<?php echo $i;?>-permission-private" class="label lblradio"><?php echo JText::_('CC PRIVATE EVENTS');?></label>
					</div>
				</div>
				<input name="event-<?php echo $i;?>-startdate" value="<?php echo $event->getStartDate();?>" type="hidden" />
				<input name="event-<?php echo $i;?>-enddate" value="<?php echo $event->getEndDate();?>" type="hidden" />
				<input name="event-<?php echo $i;?>-title" value="<?php echo $event->getTitle();?>" type="hidden" />
				<input name="event-<?php echo $i;?>-location" value="<?php echo $event->getLocation();?>" type="hidden" />
				<input name="event-<?php echo $i;?>-description" value="<?php echo $event->getDescription();?>" type="hidden" />
			</div>
		</li>
		<?php
			$i++;
		}
		?>
	</ul>
	<div style="text-align: center;margin-top: 10px;"><input type="submit" value="<?php echo JText::_('CC EVENTS IMPORT');?>" class="button" /></div>
</form>
<?php } ?>