	/**
	* @package JomSocial People You Many Know
	* @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
	* @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
	* @link     http://www.techjoomla.com
	*/ 
	
	var time_flag=0;
	function gjconnect(id,selector)
	{
		joms.friends.connect(id);
		timedelayloop(id,selector);
	} 

	function timedelayloop(id,selector)
	{
		jQuery("[name=save]").click(function(){
			jQuery("#js_ignore_id, [value="+id+"]").each(function (i){
				(jQuery(selector).parent().parent().parent().parent().remove());
			});
		});
		time_flag=time_flag+1;
		if(jQuery("[name=save]").length<=0)
		{
			setTimeout((function(){timedelayloop(id,selector)}),50);}
		}

	function ignoreAjaxpum(el)
	{
		var remove_id=jQuery(el).prev().val();
		url1= 'index.php';
		jQuery.ajax({
		url: url1,
		type: 'GET',
		dataType: 'html',
		data : {option:'com_community',view:'jompum',format:'raw',js_ignore_id:remove_id},
		timeout: 3500,
		error: function(){
		alert('Error loading XML document');
		},

		success: function(someResponse)
		{
			if(someResponse)
			{
				var el_par = jQuery(el).parent().parent().parent();
				el_par.remove();
				jQuery(':input').each(
				function()
				{
					if(jQuery(this).val()==remove_id)
					{
						var el_par1 = jQuery(this).parent().parent().parent();
						el_par1.remove();
					}
				}
				);
			}
		}
		});
	}		
