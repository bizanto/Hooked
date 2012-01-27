<?php
defined('_JEXEC') or die('Restricted Access');

$document =& JFactory::getDocument();
$document->addStyleSheet('components/com_relate/assets/edit.css');
$document->addScript('components/com_relate/assets/relate.js');

$cat = JRequest::getVar('cat');
?>

<?php if (JRequest::getVar('task') != 'azrul_ajax'): ?>
<script type="text/javascript">
jQuery(function ($) {
	_initEditRelations(<?php echo $this->listing_id; ?>, '<?php echo JRequest::getVar('cat', ''); ?>', '<?php echo JRequest::getInt('ss', 0); ?>');
});
</script>
<?php endif; ?>

<div class="rel8Container rel8Cats" style="display: none">
	<ul class="rel8Categories">
	<?php foreach ($this->categories as $category): ?>
		<li cat_id="<?php echo $category->id; ?>"><a class="catLink" href="<?php echo ($category->can_add) ? $category->menu_link : '/'; ?>"><?php echo $category->title; ?></a></li>
	<?php endforeach; ?>
		<li cat_id="photos"><a class="catLink" href="index.php?option=com_community&view=photos&task=uploader&Itemid=100"><?php echo JText::_('PHOTOS'); ?></a></li>
		<li cat_id="videos"><a class="catLink" href="joms.videos.addVideo()"><?php echo JText::_('VIDEOS'); ?></a></li>
	</ul>
</div>

<div class="rel8Container rel8Add" style="display:none">
	<label for="lsearch"><?php echo JText::_('Add'); ?> <span class="rel8CatTitle"></span><?php if ($this->listing_title):?> to <?php endif;?><strong><?php echo $this->listing_title; ?></strong>:</label> 
	<a id="addLink" href="#">(<?php if ($cat == 'photos') { echo JText::_('ADD NEW PHOTO'); } else if ($cat == 'videos') { echo JText::_('ADD NEW VIDEO'); } else { echo JText::_('add new item'); }?>)</a>
	<input id="lsearch" name="lsearch" type="text" onfocus="if (this.value=='<?php echo JText::_('TYPE TO SEARCH'); ?>') this.value='';" value="<?php echo JText::_('TYPE TO SEARCH'); ?>" />
	<br />
	<div class="rel8ListingsContainer">
		<ul class="rel8Listings">
		</ul>
	</div>
	<input type="button" onclick="Relations.save(function() { cWindowHide(); });" id="btnsave" value="<?php echo JText::_('Save'); ?>" class="rel8button" /><input onclick="cWindowHide();" id="btncancel" class="rel8button" type="button" value="<?php echo JText::_('Cancel'); ?>" />
</div>

