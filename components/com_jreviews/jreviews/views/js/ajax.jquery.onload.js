/* Attach tooltip to jtooltip class */
jreviews.tooltip();

/* Attach datepicker to date fields */
jreviews.datepicker();

/* initialize rating stars */
jQuery("div[id^='jr_stars-new']").each(function(i) {
	if( this.id != '' ) {
	    jQuery("#"+this.id).stars();
	}
});	
