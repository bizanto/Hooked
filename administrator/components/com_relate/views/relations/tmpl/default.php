<?php
defined('_JEXEC') or die('Restricted Access');
?>

<script type="text/javascript">
window.addEvent('domready', function () {
	var srcOptions = [];
	var relations = [];
	var srcCurrent = 0;

	new XHR({
		method: 'get',
		onSuccess: function (response) {
			srcOptions = Json.evaluate(response);
		}
	}).send('index.php', 'option=com_relate&task=srcOptions&controller=relations');

	$$('.relTo').each(function (el) { 
		el.setProperty('disabled', 'disabled');
	});
	
	$$('.srcSelect').addEvent('click', function () {
		var srcID = this.value;
		srcCurrent = srcID;
		$$('.relTo').each(function (el) {
			if (el.value != srcID) {
				el.removeProperty('disabled');
			}
			else {
				el.setProperty('disabled', 'disabled');
			}
			el.setProperty('checked', '');
		});
		
		var options;		
		if (typeof relations[srcID] != 'undefined') {
			options = relations[srcID];
		}
		else {
			options = srcOptions[srcID].relatable;
		}
		options.split(',').each(function (el, idx) {
			if (el == '') return;
			$('relCats'+el).setProperty('checked', 'checked');
		});
	});

	$$('.relTo').addEvent('click', function () {
		var relatables = [];
		$$('.relTo').each(function (el) {
			if (el.getProperty('checked')) {
				relatables.push(el.value);
			}
		});
		relations[srcCurrent] = relatables.join(",");
		
		// make category relation selection two way...
		if (this.getProperty('checked')) {
			// check if relations for checked item have been set
			if (typeof relations[this.value] == 'undefined') 
				relations[this.value] = srcOptions[this.value].relatable;

			// check relations of checked item and add source if it isn't already there	
			var ids = relations[this.value].split(",");
			var hasid = false;
			for (var i = 0; i < ids.length; i++) {
				if (ids[i] == srcCurrent) hasid = true;
			}
			if (!hasid) {
				relations[this.value] += ((relations[this.value].length > 0) ? "," : "") + srcCurrent;
			}
		}
		else {
			var removeId = function(arr, id) {
				for (var i = 0; i < arr.length; i++) {
					if (arr[i] == id) {
						arr.splice(i, 1);
					}
				}
			};

			var ids = [];
			// remove relation from checked item to source item
			if (typeof relations[this.value] != 'undefined') ids = relations[this.value].split(",");
			else ids = srcOptions[this.value].relatable.split(",");	
			removeId(ids, srcCurrent);
			relations[this.value] = ids.join(",");
			
			// remove relation from source to checked
			if (typeof relations[srcCurrent] != 'undefined') ids = relations[srcCurrent].split(","); 
			else ids = srcOptions[srcCurrent].relatable.split(",");
			removeId(ids, this.value);
			relations[srcCurrent] = ids.join(",");
		}
	});

	// setting onsubmit directly since $('adminForm').addEvent('submit') wasn't working...
	$('adminForm').onsubmit = function () {
		for (var i = 0; i < relations.length; i++) {
			if (typeof relations[i] != 'undefined') {
				if (relations[i] == '') relations[i] = '-';
				this.appendChild(new Element('input', {
					name:'relations['+i+']', value: relations[i], type: 'hidden'
				}));
			}
		}
	};
});
</script>

<style type="text/css">
@import url('/administrator/components/com_relate/assets/relate.css');
</style>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<div class="srcPanel">
<fieldset>
<legend><?php echo JText::_('Category'); ?></legend>
<div class="catSelect">
	<?php 
	$catslist = JHTML::_('select.radiolist', $this->categories, 'srcCats', 'class="srcSelect"', 'id', 'title'); 
        $catslist = str_replace('<input', '<div class="fieldOption"><input', $catslist);
	$catslist = str_replace('</label>', '</label></div>', $catslist);
	echo $catslist;
	?>
</div>
</fieldset>
</div>

<div class="relPanel">
<fieldset>
<legend><?php echo JText::_('Is Relatable To'); ?></legend>
<div class="catSelect">
<?php foreach ($this->categories as $category): ?>
	<?php $cid = $category->id; ?>
    <div class="fieldOption">
	<input id="relCats<?php echo $cid; ?>" class="relTo" value="<?php echo $cid; ?>" type="checkbox" />
	<label for="relCats<?php echo $cid; ?>"><?php echo $category->title; ?></label>
	</div>
<?php endforeach; ?>
</div>
</fieldset>
</div>

<input type="hidden" name="option" value="com_relate" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="relations" />
</form>

