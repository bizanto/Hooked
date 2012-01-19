<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @param	posted	boolean	Determines whether the current state is a posted event.
 * @param	search	string	The text that the user used to search 
 */
defined('_JEXEC') or die();
?>
<div id="community-groups-wrap">
	<!--SEARCH FORM-->
	<div class="group-search-form">
	<form name="jsform-groups-search" method="get" action="">
		<?php if(!empty($beforeFormDisplay)){ ?>
			<table class="formtable" cellspacing="1" cellpadding="0" style="width: 98%;">
				<?php echo $beforeFormDisplay; ?>
			</table>
		<?php } ?>
		
		<input type="text" class="inputbox" name="search" value="<?php echo $search; ?>" size="50" />
		<?php if(!empty($afterFormDisplay)){ ?>
			<table class="formtable" cellspacing="1" cellpadding="0" style="width: 98%;">
				<?php echo $afterFormDisplay; ?>
			</table>
		<?php } ?>
		<input type="submit" value="<?php echo JText::_('CC SEARCH BUTTON');?>" class="button" /> 
		<?php echo JHTML::_( 'form.token' ); ?>
		<input type="hidden" value="com_community" name="option" />
		<input type="hidden" value="groups" name="view" />
		<input type="hidden" value="search" name="task" />
		<input type="hidden" value="<?php echo CRoute::getItemId();?>" name="Itemid" />
		<table class="formtable" cellspacing="1" cellpadding="0">
		    <!-- Group Category -->
		    <tr>
			<td class="key">
			    <label for="catid" class="label title jomTips" title="<?php echo JText::_('CC GROUP CATEGORY');?>::<?php echo JText::_('CC GROUP CATEGORY TIPS');?>">
				    <?php echo JText::_('CC GROUP CATEGORY');?>
			    </label>
			</td>
			<td class="value">
			    <select name="catid" id="catid" class="required inputbox">
				<option value="0" selected></option>
				<?php
				    foreach( $categories as $category )
				    {
				    ?>
					    <option value="<?php echo $category->id; ?>" <?php if( $category->id == $catId ) { ?>selected<?php } ?>><?php echo JText::_( $this->escape($category->name) ); ?></option>
				    <?php
				    }
				    ?>
			    </select>
			</td>
		    </tr>
		</table>
	</form>
	</div>
	<!--SEARCH FORM-->
	<?php
	if( $posted )
	{
	?>
		<!--SEARCH DETAIL-->
		<div class="group-search-detail">
			<span class="search-detail-left">
				<?php echo JText::sprintf( 'CC SEARCH RESULT' , $search ); ?>
			</span>
			<span class="search-detail-right">
				<?php echo JText::sprintf( (CStringHelper::isPlural($groupsCount)) ? 'CC SEARCH RESULT TOTAL MANY' : 'CC SEARCH RESULT TOTAL' , $groupsCount ); ?>
			</span>
			<div style="clear:both;"></div>
		</div>
		<!--SEARCH DETAIL-->
		<?php echo $groupsHTML; ?>
	<?php
	}
	?>
</div>