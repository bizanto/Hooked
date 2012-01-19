<?php defined('_JEXEC') or die('Restricted Access'); ?>

<style type="text/css">
@import url('/administrator/components/com_relate/assets/relate.css');
</style>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<div class="categoryPanel">
<fieldset>
	<legend>Allow Relations For</legend>
	<div class="categoryList">
	<?php foreach ($this->categories as $category): ?>
		<?php 
		$cid = $category->id; 
		$checked = ($category->allowed) ? ' checked="checked"' : '';
		?>
        <div class="fieldOption">
		<input id="cat<?php echo $cid; ?>" name="categories[]" value="<?php echo $cid; ?>" type="checkbox"<?php echo $checked; ?> />
        <label for="cat<?php echo $cid; ?>"><?php echo $category->title; ?></label>
		</div>
	<?php endforeach; ?>
	</div>
</fieldset>
</div>

<input type="hidden" name="option" value="com_relate" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="categories" />
</form>
