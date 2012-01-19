jQuery(".jr_tabs").tabs();

jQuery(function() {
    jQuery(".datepicker").datepicker({
        showOn: 'both', 
        buttonImage: datePickerImage, 
        buttonImageOnly: true,
        buttonText: 'Calendar',
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true                            
    });
});