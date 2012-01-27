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
<div id="community-events-wrap">
	<?php if ( $index && $handler->showCategories() ) : ?>
	<div class="cRow">
		<div class="ctitle"><?php echo JText::_('CC CATEGORIES');?></div>
		<ul class="c3colList">
			<li>
				<?php if( $category->parent == COMMUNITY_NO_PARENT && $category->id == COMMUNITY_NO_PARENT ){ ?>
					<a href="<?php echo CRoute::_('index.php?option=com_community&view=events');?>"><?php echo JText::_( 'CC ALL EVENTS' ); ?> </a>
				<?php }else{ ?>
					<a href="<?php echo CRoute::_('index.php?option=com_community&view=events&categoryid=' . $category->parent ); ?>"><?php echo JText::_('CC BACK TO PARENT'); ?></a>
				<?php }  ?>
			</li>
			<?php if( $categories ): ?>
				<?php foreach( $categories as $row ): ?>
				<li>
					<a href="<?php echo CRoute::_('index.php?option=com_community&view=events&categoryid=' . $row->id ); ?>"><?php echo JText::_( $this->escape($row->name) ); ?></a> <?php if( $row->count > 0 ){ ?>( <?php echo $row->count; ?> )<?php } ?>
				</li>
				<?php endforeach; ?>
			<?php else: ?>
				<li><?php echo JText::_('CC NO CATEGORIES CREATED'); ?></li>
			<?php endif; ?>
		</ul>
		<div class="clr"></div>
	</div>
	<?php endif; ?>
	
	<?php echo $sortings; ?>
	<div id="community-event-nearby" class="cSidebar clrfix">
		<div id="community-event-nearby-form" class="cModule clrfix">
			<h3><?php echo JText::_('CC EVENTS SEARCH NEARBY'); ?></h3>
			<span id="showNearByEventsForm">
				<input type="text" id="userInputLocation" name="userInputLocation" value="">
				<div class="small">
					<?php echo JText::_('CC EVENTS LOCATION DESCRIPTION');?>
				</div>
				<button class="button" onclick="joms.geolocation.validateNearByEventsForm();"><?php echo JText::_('CC SEARCH'); ?></button>
				<span id="autodetectLocation" style="display: none;">&nbsp;<?php echo JText::_('CC OR') ?>&nbsp;<a href="javascript:void(0);" onclick="joms.geolocation.showNearByEvents();"><?php echo JText::_('CC EVENTS LOCATION AUTODETECT') ?></a></span>
			</span>
			<div id="community-event-nearby-listing" style="display: none">
				<span id="showNearByEventsLoading" class="loading" style="display: none; float: left; margin-top: 10px; margin-left: 80px;"></span>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		joms.jQuery(document).ready(function(){
			// Get the Current Location from cookie
			var location =	joms.geolocation.getCookie( 'currentLocation' );

			if( location.length != 0 )
			{
				joms.jQuery('#showNearByEventsLoading').show();
				joms.geolocation.showNearByEvents( location );
			}

			// Check if the browsers support W3C Geolocation API
			// If yes, show the auto-detect link
			if( navigator.geolocation )
			{
			    joms.jQuery('#autodetectLocation').show();
			}
		});
	</script>

	<div id="community-events-results-wrapper" class="cMain jsApLf mvLf jsItms">
		<?php echo $eventsHTML;?>
	</div>
</div>