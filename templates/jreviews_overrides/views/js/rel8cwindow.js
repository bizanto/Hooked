jQuery(function ($) {
    $('.rel8win, .add-new').click(function () {
        var relLink = this.getAttribute('href');

        var listing_id, cat_id = '', ss = '';

        if (matches = relLink.match(/id=(\d+)/)) listing_id = matches[1];

        if (matches = relLink.match(/cat=([\w\d,]+)/)) cat_id = matches[1];
    
        if (matches = relLink.match(/ss=(\d)/)) ss = matches[1];

        if (!listing_id && !cat_id) return false;
		
        var ajaxCall = "jax.call('relate', 'relate,ajaxEditRelations', '" + listing_id + "', '" + cat_id + "', '" + ss + "');";	
        cWindowShow(ajaxCall, '<?php __t("Edit related items"); ?>', 630, 100);
        $('#cWindowContentWrap').css('background-color', '#3D3E18');

        return false;
    });
});