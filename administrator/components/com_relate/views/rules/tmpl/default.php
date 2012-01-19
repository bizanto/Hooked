<?php
defined('_JEXEC') or die('Restricted Access');
?>

<script type="text/javascript">
window.addEvent('domready', function () {
	var srcOptions = [];
	var setRules = [];
	var srcCurrent = 0;

	new XHR({
		method: 'get',
		onSuccess: function (response) {
			srcOptions = Json.evaluate(response);
		}
	}).send('index.php', 'option=com_relate&task=srcOptions&controller=rules');

	$$('.rulesPanel input').each(function (el) {
		el.setProperty('disabled', 'disabled');
	});

	$$('.srcSelect').addEvent('click', function () {
		srcID = this.value;
		srcCurrent = srcID;

		if (!setRules[srcCurrent]) setRules[srcCurrent] = srcOptions[srcID];
		
		$$('.rulesPanel input').each(function (el) {
			el.removeProperty('checked');
			el.removeProperty('disabled');
		});
		$('menuSelect').setStyle('display', 'none'); 

		var options;		
		if (setRules[srcID]) {
			options = setRules[srcID];
		}
		else {
			options = srcOptions[srcID];
		}
		for (var field in options) {
			if (field == "author") {
				$('author'+options[field]).setProperty('checked', 'checked');
			}
			else if (field != "catID" && options[field] == "1") {
				$(field).setProperty('checked', 'checked');
				if (field == "can_add") {
					$('menuSelect').setStyle('display', (options[field] == "1") ? 'block' : 'none');
					if (options['menu_link'] != '') {
						var item = $$('option[value="'+options['menu_link']+'"]');
						if (item.length) item[0].setProperty('selected', 'selected');
					}
				}
			}
		}
	});

	$$('input[name=author]').addEvent('click', function () {
		setRules[srcCurrent].author = this.value;
	});

	$('featured').addEvent('click', function () {
		setRules[srcCurrent].featured = (this.checked) ? 1 : 0;
	});

	$('can_add').addEvent('click', function () {
		setRules[srcCurrent].can_add = (this.checked) ? 1 : 0;
		if (this.checked) {
			$('menuSelect').setStyle('display', 'block');
		}
		else {
			$('menuSelect').setStyle('display', 'none');
		}
	});

	$('menu_link').addEvent('change', function () {
		setRules[srcCurrent].menu_link = this.value;
	});

	$('adminForm').onsubmit = function () {
		for (var i = 0; i < setRules.length; i++) {
			if (typeof setRules[i] == 'undefined') continue;
			for (var field in setRules[i]) {
				if (field == "catID") continue;
				this.appendChild(new Element('input', {
					name:'setRules['+i+']['+field+']', value: setRules[i][field], type: 'hidden'
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

<div class="categoryPanel">
<fieldset>
<legend>Category</legend>
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

<div class="rulesPanel">
<fieldset>
	<legend>Access Rules</legend>
    <div>
        <div class="fieldOption">
        <label>Author:</label>
            <input id="author0" name="author" type="radio" value="0" /><label for="author0">Any</label>
            <input id="author1" name="author" type="radio" value="1" /><label for="author1">User</label>
            <input id="author2" name="author" type="radio" value="2" /><label for="author2">Admin</label><br />
        </div>
        <div class="fieldOption">
        <input id="featured" name="featured" type="checkbox" />
        <label for="featured">Featured only?</label>
        </div>
        <div class="fieldOption">
        <input id="can_add" name="can_add" type="checkbox" />
        <label for="can_add">User add?</label>
        </div>
        
        <div id="menuSelect">
        <label>Menu link to add:</label>
		<?php echo $this->lists['menuitems']; ?>
        </div>
    </div>
</fieldset>
</div>

<input type="hidden" name="option" value="com_relate" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="rules" />
</form>
