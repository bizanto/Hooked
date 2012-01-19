/**
* @version		$Id: ndeditorswitchplugin.js 9 2008-09-26 10:30:11Z netdream $
* @package		NDEditorSwitch
* @subpackage	Ajax
* @copyright	Copyright (C) 2008 Netdream - Como,Italy. All rights reserved.
* @license		GNU/GPLv2
*/
var NDEditorSwitchPlugin = new Class ({
	options: {
		submiturl: 'index.php?option=com_ndeditorswitch'
	},
	initialize: function(options){
		this.setOptions(options);
		this.elements = [];

		this.build();
	},
	build: function () {
	var arrOfElements = $$('form#ndEditorForm');
	
		if ($type(arrOfElements) != 'array') {
			return false;
		}
		
		if (!arrOfElements.length) {
			return false;			
		}
		
		var _class = this;
		arrOfElements.each(  function (el) {
			var actual_el = el.getElement('select#ndeditor_assigned');
			var select_fx = new Fx.Styles(document.getElement('select#ndeditor_assigned'), {duration:200, wait:false});
			var div_fx = new Fx.Styles(document.getElement('div#ndEditorFormBox'), {duration:200, wait:false});
			
			actual_el.addEvent('change', function (e) {
				select_fx.start({'opacity':0.2,'background-color': '#ff0000'});
				//div_fx.start({'opacity':0.2,'background-color': '#ff5555'});
				new Ajax(_class.options.submiturl, { 
					method: 'post', 
					data: 'task=ajaxswitch&format=raw&editor='+actual_el.options[actual_el.selectedIndex].value,
					onComplete: function(response){	
								select_fx.start({'opacity':1,'background-color': '#ffffdd'});
								//div_fx.start({'opacity':1,'background-color': '#ffffdd'});
								}	
				}).request();
				
			});
		});		
	}
});

NDEditorSwitchPlugin.implement(new Options);