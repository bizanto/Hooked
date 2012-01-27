jQuery(document).ready(function() 
{
    jQuery('#toolbar-box').remove();
    jQuery('#submenu-box').remove();
    
    jreviews_admin.menu.init();
    
       /* Set jQuery ajax defaults */
    jQuery.ajaxSetup({url: s2AjaxUri,global: true,type: "POST",cache: false});
      
    /* jQuery ajax defaults */
    jQuery("#spinner").ajaxSend( function() { 
        jQuery(this).show();
        jQuery('#s2AjaxResponse').remove();    
        jQuery("body").append('<div id="s2AjaxResponse" style="display:none;"></div>');         
    });
     jQuery("#spinner").ajaxComplete(function() {
            jQuery(this).fadeOut();
            jQuery('.ui-dialog-buttonpane :button').each(function() { 
                jQuery(this).removeClass('ui-button ui-corner-all').addClass('ui-button ui-corner-all');
            });            
     });

    /* Review moderation */
    jQuery('#jr_ownerReplyEdit').dialog({
        autoOpen: false,
        modal: true,    
        width:640,
        height: 420
    });    
    /* initializes tabs */
    jQuery("#jr_tabs").tabs();
    
    /* initialize datepicker global defaults */
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
});

jreviews_admin =
    {
        apply: false, 
        claims:
            {
                moderate: function(form_id)
                    {
                        jQuery.ajax({
                            url: s2AjaxUri+'&url=admin_claims/_save',
                            type: 'POST',
                            data:jQuery('#'+form_id).serialize(),
                            dataType: 'json', 
                            success: function(s2Out){
                                jQuery('#s2AjaxResponse').html(s2Out.response);
                            }
                        });
                    }
            },        
        criteria:
            {
              save: function(form_id)
                  {
                      jreviews_admin.tools.saveUpdateRow(form_id);                      
                  }  
            },
        category:
            {
                submit: function(form_id)
                    {
                        jQuery.post(s2AjaxUri,
                            jQuery('#'+form_id).serialize(),
                            function(s2Out)
                            {  
                                s2Out = s2Out.response;
                                switch(s2Out.action)
                                {
                                    case 'error':
                                        s2Alert(s2Out.text);
                                    break;
                                    case 'success':
                                        jreviews_admin.dialog.close();
                                        jQuery('#page').html(s2Out.page).fadeIn(1500,function(){
                                            jQuery.each(s2Out.cat_ids, function(key,row) {
                                                jQuery('#category'+row.cat_id).effect('highlight',{},4000);   
                                            });
                                        });
                                    break;
                                }
                            },
                            'json'
                        );                        
                    },
                edit: function(params,options)
                    {
                        options.buttons =  {
                            'Submit': function() 
                                {
                                    jreviews_admin.category.submit('jr_categoryForm');                                  
                                },
                            'Cancel': function() { jQuery(this).dialog('close'); }
                            }
                        jreviews_admin.dialog.form('categories','edit', params, options);                        
                    },
                add: function(params,options)
                    {
                        options.buttons =  {
                            'Submit': function() 
                                {
                                    jreviews_admin.category.submit('jr_categoryFormNew');                                  
                                },
                            'Cancel': function() { jQuery(this).dialog('close'); }
                            }
                        jreviews_admin.dialog.form('categories','create', params, options);                        
                    }                
                                    
            },            
        directory:
            {
                submit: function(form_id)
                    {
                        jQuery.post(s2AjaxUri,
                            jQuery('#'+form_id).serialize(),
                            function(s2Out)
                            {  
                                s2Out = s2Out.response;
                                switch(s2Out.action)
                                {
                                    case 'error':
                                        s2Alert(s2Out.text);
                                    break;
                                    case 'success':
                                        jreviews_admin.dialog.close();
                                        jQuery('#title').val('');
                                        jQuery('#desc').val('');
                                        jQuery('#directorytable').html(s2Out.page);
                                        jreviews_admin.tools.flashRow(s2Out.row_id);                                                
                                    break;
                                }
                            },
                            'json'
                        );                        
                    },
                edit: function(params,options)
                    {
                        options.buttons =  {
                            'Submit': function() 
                                {
                                    jreviews_admin.directory.submit('directoryForm');                                  
                                },
                            'Cancel': function() { jQuery(this).dialog('close'); }
                            };
                        jreviews_admin.dialog.form('directories','edit', params, options);                        
                    },
                remove: function(dir_id)
                    {
                        if(confirm('Are you sure you want to delete this directory?'))
                            xajax.request({xjxfun:'xajaxDispatch'},{URI:xajaxUri,parameters:['admin/directories','delete','jreviews',{row_id:dir_id}]});
                    }                
            },
        discussion:
            {
                moderate: function(form_id)
                    {
                        jQuery.ajax({
                            url: s2AjaxUri+'&url=admin_discussions/_save',
                            type: 'POST',
                            data:jQuery('#'+form_id).serialize(),
                            dataType: 'json', 
                            success: function(s2Out){
                                jQuery('#s2AjaxResponse').html(s2Out.response);
                            }
                        });                        
                    }
            },                
        field:
            {
                submit: function(form_id)
                    {
                        var formData = jQuery('#'+form_id).serialize();
                        if(jreviews_admin.apply) {
                            formData += encodeURI('&data[apply]=1');
                            jreviews_admin.apply = false;
                        }                        
                        jQuery.post(s2AjaxUri,
                            formData,
                            function(s2Out)
                            {  
                                s2Out = s2Out.response;
                                switch(s2Out.action)
                                {
                                    case 'apply':
                                        jreviews_admin.tools.apply();
                                    break;                                    
                                    case 'error':
                                        s2Alert(s2Out.text);
                                    break;
                                    case 'success':
                                        jQuery('#page').fadeOut('fast',function(){
                                            jQuery(this).html(s2Out.page).fadeIn('fast',function(){
                                                jQuery('#'+s2Out.row_id).effect('highlight',{},4000);                                                   
                                            });                                            
                                        });
                                    break;
                                }
                            },
                            'json'
                        );                        
                    }
            }, 
        fieldoption:
            {
                edit: function(params,options)
                    {
                        options.buttons =  {
                            'Submit': function() 
                                {
                                    jreviews_admin.fieldoption.submit('jr_editFieldOptionsForm');                                  
                                },
                            'Cancel': function() { jQuery(this).dialog('close'); }
                            };
                        jreviews_admin.dialog.form('fieldoptions','edit', params, options);                        
                    },
                submit: function(form_id)
                    {
                        jQuery.post(s2AjaxUri,
                            jQuery('#'+form_id).serialize(),
                            function(s2Out)
                            {  
                                switch(s2Out.action)
                                {
                                    case 'error':
                                        s2Alert(s2Out.update_text);
                                    break;
                                    case 'success':
                                        jreviews_admin.dialog.close();
                                        jQuery('#optionlist').html(s2Out.page).fadeIn('fast',function(){
                                            jQuery('#text').val('');jQuery('#value').val('');jQuery('#image').val('');
                                            jreviews_admin.tools.flashRow('fieldoption'+s2Out.option_id);   
                                        });
                                    break;
                                }
                            },
                            'json'
                        );                        
                    }                                        
            },                                                           
        group: 
            {
                changeType: function(element,form_id)
                    {   
                        jQuery('#group_type').val(element.value);
                        jQuery('#page_number').val(1);                        
                        jQuery('#action').val('index');
                        jQuery.post(s2AjaxUri,
                            jQuery('#'+form_id).serialize(),
                            function(s2Out)
                            {                          
                                jQuery('#page').html(s2Out);
                            }
                            ,'html'
                        );                        
                    },
                edit: function(params,options)
                    {
                        options.buttons =  {
                            'Submit': function() {
                                jreviews_admin.tools.saveUpdateRow('groupsForm','jr_formDialog');
                            },
                            'Cancel': function() { jQuery(this).dialog('close'); }
                        };
                        jreviews_admin.dialog.form('groups','edit', params, options);                        
                    },                         
                submit: function(form_id,validation_id)
                    {
                        jreviews_admin.tools.saveUpdateRow(form_id,'jr_groupNew');                                              
                    },
                reorder: function(group_id,direction)
                    {
                        jreviews_admin.tools.reorder('groups','_changeOrder',group_id,direction,'fieldgroup');
                    },
                reorder_page: function()
                    {   
                        jQuery('#action').val('_saveOrder');
                        jQuery.post(s2AjaxUri,jQuery('#adminForm').serialize(),function(s2Out){
                            s2Out = s2Out.response;
                            if(s2Out.page!=undefined)
                            {
                                jQuery('#page').html(s2Out.page);
                            }
                            s2Alert(s2Out.text);
                        },'json');
                    },
                remove: function(group_id,field_count,confirm_text,options)
                    {
                        if(field_count>0){
                            alert("To delete this group you first need to delete all the fields associated with it in the Fields Manager.");
                        } else {
                            jreviews_admin.dialog.remove('groups','_delete',group_id,confirm_text,options);
                        }                        
                    },                 
                toggleTitle: function(group_id)
                    {
                        jQuery.get(s2AjaxUri+'&url=groups/toggleTitle/group_id:'+group_id,
                            function(s2Out)
                            {
                                if(s2Out == '1'){               
                                    jQuery('#showTitle_'+group_id).find('img').attr('src','images/tick.png');
                                } else {
                                    jQuery('#showTitle_'+group_id).find('img').attr('src','images/publish_x.png');
                                }  
                                jreviews_admin.tools.flashRow('fieldgroup'+group_id);                                                                      
                            }
                            ,'text'
                        );
                    }                    
            },
         listing:
            {
                moderate: function(form_id)
                    {
                        jQuery.ajax({
                            url: s2AjaxUri+'&url=admin_listings/_saveModeration',
                            type: 'POST',
                            data:jQuery('#'+form_id).serialize(),
                            dataType: 'json', 
                            success: function(s2Out){
                                jQuery('#s2AjaxResponse').html(s2Out.response);
                            }
                        });                        
                    },
                moderateLoadMore: function()
                {
                    jQuery('#jr_loadMoreSpinner').css('display','inline');
                    var page = parseInt(jQuery('#jr_page').val());
                    var new_page = page+1;
                    var num_pages = jQuery('#jr_num_pages').val();
                    jQuery('#jr_page').val(new_page);
                    jQuery.ajax({
                        url: s2AjaxUri+'&url=admin_listings/moderation',
                        type: 'POST',
                        data:jQuery('#jr_pageScroll').serialize(),
                        dataType: 'html', 
                        success: function(s2Out){
                            jQuery('#jr_loadMoreSpinner').css('display','none');
                            jQuery('#jr_loadMore').before(s2Out);
                            if(num_pages == new_page){
                                jQuery('#jr_loadMore').remove();
                            }
                            
                        }
                    });                                            
                },    
                submit: function()
                {
                    var form = jQuery('#jr_listingForm');
                    form.find('#section').val(form.find('#section_id option:selected').text());
                    form.find('#category').val(form.find('#cat_id option:selected').text());
                    jQuery('.wysiwyg_editor').RemoveTinyMCE();
                    form.submit();
                },
            deleteImage: function(element,options)
                {
                    jQuery(element).s2Confirm(
                        {
                            dialog:{title:options.title},
                            submitData:
                                {
                                    'data[controller]':'listings',
                                    'data[action]':'_imageDelete',
                                    'data[listing_id]':options.listing_id,
                                    'data[delete_key]':options.delete_key,
                                    'data[image_path]':options.image_path
                                }
                        },options.text
                    );                    
                }                                    
            },                  
        menu:
            {
                init: function()
                    {
                        jQuery('#listing_moderation').click(function() { jreviews_admin.menu.load('admin_listings','moderation')});
                            
                        jQuery('#review_moderation').click(function() { jreviews_admin.menu.load('reviews','moderation')});
                            
                        jQuery('#claims').click(function() { jreviews_admin.menu.load('admin_claims','moderation')});
                      
                        jQuery('#owner_reply_moderation').click(function() { jreviews_admin.menu.load('admin_owner_replies','index')});

                        jQuery('#discussion_moderation').click(function() { jreviews_admin.menu.load('admin_discussions','index')});
                        
                        jQuery('#reports').click(function() { jreviews_admin.menu.load('admin_reports','index')});

                        jQuery('#groups').click(function() { jreviews_admin.menu.load('groups','index')});

                        jQuery('#fields').click(function() { jreviews_admin.menu.load('fields','index')});
                             
                        jQuery('#criterias').click(function() { jreviews_admin.menu.load('criterias','index')});
                        
                        jQuery('#categories').click(function() { jreviews_admin.menu.load('categories','index')});

                        jQuery('#directories').click(function() { jreviews_admin.menu.load('directories','index')});

                        jQuery('#themes').click(function() { jreviews_admin.menu.load('themes','index')});
                        
                        jQuery('#seo').click(function() { jreviews_admin.menu.load('seo','index')});

                        jQuery('#predefined_replies').click(function() { jreviews_admin.menu.load('admin_predefined_replies','index')});
                           
                        jQuery('#updater').click(function() { jreviews_admin.menu.load('admin_updater','index')});

                        jQuery('#clear_cache').click( function() {jreviews_admin.tools.clearCache();} );

                        jQuery('#clear_registry').click( function() {jreviews_admin.tools.clearRegistry();} );
                    },
                    
                load: function(controller,action) 
                    {
                        jQuery.get(s2AjaxUri,
                            {'data[controller]':'admin/'+controller,'data[action]':action},
                            function(page)
                            {  
                                jQuery('#page').fadeOut('fast').delay(1).queue(function(n) {
                                    jQuery(this).html(page);
                                    n();
                                    if(jQuery('.dialog').is(':data(dialog)')) 
                                    {
                                        jQuery('.dialog').dialog('destroy').remove();                                                                                                                                                      
                                    }  
                                    if(!jQuery('#xajax-admin').length)
                                    {       
                                        jQuery.getScript(jr_xajaxAdminJS,function(){
                                            jQuery('body').append('<div id=\"xajax-admin\" style=\"display:none\"></div>');
                                        });
                                    };                                                                       
                                }).fadeIn('fast');
                            },
                            'html'
                        );                         
                    },
                    moderation_counter: function(element_id)
                        {
                            $index = jQuery('#'+element_id);
                            var val = parseInt($index.html());
                            $index.html(--val);
                            if(val==0){
                                $index.parents('li').remove();
                            }                     
                        }
            },
        report:
            {
                moderate: function(form_id)
                    {
                        jQuery.ajax({
                            url: s2AjaxUri+'&url=admin_reports/_save',
                            type: 'POST',
                            data:jQuery('#'+form_id).serialize(),
                            dataType: 'json', 
                            success: function(s2Out){
                                jQuery('#s2AjaxResponse').html(s2Out.response);
                            }
                        });
                    }
            },
        tools:
            {
                apply: function()
                    {
                        jQuery('#status').html("Your changes were applied.").fadeIn('medium',function(){jQuery(this).fadeOut(3000);})    
                    },
                clearCache: function() 
                    {
                        jQuery.post(s2AjaxUri,{'data[controller]':'admin/common','data[action]':'clearCache'},function(s2Out){s2Alert(s2Out);});   
                    },
                clearRegistry: function() 
                    {
                        jQuery.post(s2AjaxUri,{'data[controller]':'admin/common','data[action]':'clearFileRegistry'},function(s2Out){s2Alert(s2Out);});   
                    },
                flashRow: function(row_id) 
                {
                        jQuery('#'+row_id).effect('highlight',{},4000);    
                },
                saveUpdateRow: function(form_id,validation_id)
                    {
                        if(validation_id!=undefined){
                            $form = jQuery('#'+validation_id);                            
                        } else {
                            $form = jQuery('#'+form_id);                                                        
                        }
                        $form.find('.jr_validation').remove();
                        var formData = jQuery('#'+form_id).serialize();
                        if(jreviews_admin.apply) {
                            formData += encodeURI('&data[apply]=1');
                            jreviews_admin.apply = false;
                        }
                        jQuery.post(s2AjaxUri,
                            formData,
                            function(s2Out)
                            {  
                                s2Out = s2Out.response;
                                switch(s2Out.action)
                                {
                                    case 'apply':
                                        jreviews_admin.tools.apply();
                                    break;
                                    case 'error':
                                        $form.append('<div class="jr_validation">'+s2Out.text+'</div>');
                                        //s2Alert(s2Out.text);
                                    break;
                                    case 'success':
                                        jreviews_admin.dialog.close();
                                        jQuery('.dialog').dialog('destroy').remove();                                                                                                              
                                        if(s2Out.fade==false)
                                        {
                                            jQuery('#page').html(s2Out.page).fadeIn('normal',function(){
                                                jreviews_admin.tools.flashRow(s2Out.row_id)                                                
                                            });
                                        } else {
                                            jQuery('#page').append('&nbsp;').fadeOut('normal',function()
                                            {                
                                                jQuery('#page').html(s2Out.page).fadeIn('normal',function(){
                                                    jreviews_admin.tools.flashRow(s2Out.row_id)                                                
                                                });
                                            });
                                        }
                                    break;
                                }
                            },
                            'json'
                        );                             
                    },
                removeRow: function(row_id) 
                    {
                    jQuery('#'+row_id).effect('highlight',{},1000).fadeOut('slow').remove();
                    },                    
                reorder: function(controller,action,entry_id,direction,row_prefix)
                    {
                        jQuery.get(s2AjaxUri+'&url='+controller+'/'+action+'/entry_id:'+entry_id+'/direction:'+direction,
                            function(s2Out)
                            {
                                jQuery('#page').html(s2Out).fadeIn('fast',function(){
                                    jreviews_admin.tools.flashRow(row_prefix+entry_id);                                                                                                          
                                });
                            }
                            ,'html'
                        );
                    },
                moderateLoadMore: function(controller,action)
                {
                    jQuery('#jr_loadMoreSpinner').css('display','inline');
                    var page = parseInt(jQuery('#jr_page').val());
                    var new_page = page+1;
                    var num_pages = jQuery('#jr_num_pages').val();
                    jQuery('#jr_page').val(new_page);
                    jQuery.ajax({
                        url: s2AjaxUri+'&url='+controller+'/'+action,
                        type: 'POST',
                        data:jQuery('#jr_pageScroll').serialize(),
                        dataType: 'html', 
                        success: function(s2Out){
                            jQuery('#jr_loadMoreSpinner').css('display','none');
                            jQuery('#jr_loadMore').before(s2Out);
                            if(num_pages == new_page){
                                jQuery('#jr_loadMore').remove();
                            }
                            
                        }
                    });
                }                                                
            },
        dialog:
            {   
                close: function()
                    {
                        jQuery('.dialog').dialog('close');    
                    },
                remove: function(controller,action,entry_id,confirm_text,options)
                    {
                        var confirm_element = '<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>'+confirm_text+'</p>';
                        var defaults = {
                            'title': 'Delete confirmation',
                            'modal': true,
                            'autoOpen': true,
                            'width': '600px',
                            'height': 'auto',                                
                            'buttons':
                                {
                                    'Delete': function() {
                                        jQuery.post(s2AjaxUri,{'data[controller]':'admin/'+controller,'data[action]':action,'data[entry_id]':entry_id},
                                            function(s2Out) {
                                                jQuery('#s2AjaxResponse').html(s2Out.response);                                          
                                            },
                                            'json'
                                        );
                                    },
                                    'Cancel': function() {
                                        jQuery(this).dialog('close');
                                    }                                    
                                }
                        };
                        var settings = jQuery.extend(defaults, options);
                        jQuery('.dialog').dialog('destroy').remove();    
                        jQuery("body").append('<div id="jr_deleteDialog" class="dialog"></div>');
                        jQuery('#jr_deleteDialog').html(confirm_element).dialog(settings);   
                    },                            
                preview: function(html_id,options)
                    {
                        var dialog_id = 'jr_previewDialog';
                        var defaults = {
                            'modal': true,
                            'autoOpen': true,
                            'buttons': function() {},
                            'width': '600px',
                            'height': 'auto'
                        }
                        var settings = jQuery.extend(defaults, options);
                        jQuery('.dialog').dialog('destroy').remove();    
                        jQuery("body").append('<div id="'+dialog_id+'" class="dialog"></div>');
                        jQuery('#'+dialog_id).html(jQuery('#'+html_id).html()).dialog(settings);                            
                    },
               form: function(controller,action,params,options)
                   {
                            var dialog_id = 'jr_formDialog';
                            
                            var defaults = {
                                'modal': true,
                                'autoOpen': true,
                                'buttons': function() {},
                                'width': '600px',
                                'height': 'auto'
                            }
                            var settings = jQuery.extend(defaults, options);       
                                            
                            if(jQuery('.dialog').is(':data(dialog)')) 
                            {                            
                                jQuery('.dialog').dialog('destroy').remove();    
                            }
                            jQuery("body").append('<div id="'+dialog_id+'" class="dialog"></div>');

                            jQuery('#'+dialog_id).load
                            (
                                s2AjaxUri+'&url='+controller+'/'+action+'&'+params,
                                function(){
                                    jQuery(this).dialog(settings);                            

                                    jQuery('.ui-dialog-buttonpane :button').each(function() { 
                                        jQuery(this).removeClass('ui-button ui-corner-all').addClass('ui-button ui-corner-all');
                                    });
                                }
                            );                   
                   }                                                 
            },
         review:
            {
                moderate: function(form_id)
                    {
                        jQuery.ajax({
                            url: s2AjaxUri+'&url=reviews/_save',
                            type: 'POST',
                            data:jQuery('#'+form_id).serialize(),
                            dataType: 'json', 
                            success: function(s2Out){
                                jQuery('#s2AjaxResponse').html(s2Out.response);
                            }
                        });                        
                    },
                moderateLoadMore: function()
                    {
                        jQuery('#jr_loadMoreSpinner').css('display','inline');
                        var page = parseInt(jQuery('#jr_page').val());
                        var new_page = page+1;
                        var num_pages = jQuery('#jr_num_pages').val();
                        jQuery('#jr_page').val(new_page);
                        jQuery.ajax({
                            url: s2AjaxUri+'&url=reviews/moderation',
                            type: 'POST',
                            data:jQuery('#jr_pageScroll').serialize(),
                            dataType: 'html', 
                            success: function(s2Out){
                                jQuery('#jr_loadMoreSpinner').css('display','none');
                                jQuery('#jr_loadMore').before(s2Out);
                                if(num_pages == new_page){
                                    jQuery('#jr_loadMore').remove();
                                }
                                
                            }
                        });                                            
                    }                    
            },
         seo:
            {
                groupFilter: function(form_id)
                    {
                        jQuery.ajax({
                            url: s2AjaxUri,
                            type: 'POST',
                            data:jQuery('#adminForm').serialize(),
                            dataType: 'html', 
                            success: function(s2Out){
                                jQuery('#page').html(s2Out);
                            }
                        });                        
                    }
            }                                  
    }

function jreviewsDispatch(controller,action,formName) {
    xajax.request({xjxfun:'xajaxDispatch'},{URI:'index.php?option=com_jreviews&tmpl=component&no_html=1',parameters:[controller,action,'jreviews',xajax.getFormValues(formName)]});
}
 
function closeModalWindow(){
    jQuery('.dialog').dialog('close');    
}
 

/* jQuery table effects */
function flashRow(row_id) {
        jQuery('#'+row_id).effect('highlight',{},4000);    
}
function removeRow(row_id) {
    jQuery('#'+row_id).css({backgroundColor: '#ff0'}).fadeOut('slow');
}

/* xajax loading effects */
if(typeof xajax != "undefined") 
{
    xajax.callback.global.onRequest = function() {
        if (xajax.$('spinner') != null) {
            xajax.$('spinner').style.display = 'inline';
        }
    };
    xajax.callback.global.onComplete = function() {
        if (xajax.$('spinner') != null) {
            xajax.$('spinner').style.display = 'none';
        }
    }
}

/* Configuration functions */
function clearSelect(name) {
    var element = document.getElementById(name);
    count = element.length;
    for (i=0; i < count; i++) {
        element.options[i].selected = '';
    }
}

/* Paginate functions */
function setPage(page) {
    document.getElementById('page_number').value = page;
}

function setLimit(limit) {
    document.getElementById('limit').value = limit;
}

function deleteListing(listing_id) {
    if(confirm('This action will delete the listing along with its custom fields and reviews. Are you sure you want to continue?'))
        xajax.request({xjxfun:'xajaxDispatch'},{URI:xajaxUri,parameters:['admin/admin_listings','_delete','jreviews',{data:{Listing:{id:listing_id}}}]});
}

/* Review functions */
function deleteReview(reviewid,uri) {
    if(confirm('Are you sure you want to delete this review?'))
        xajax.request({xjxfun:'xajaxDispatch'},{URI:xajaxUri,parameters:['admin/reviews','_delete','jreviews',{row_id:reviewid}]});
}

/* Review Reports functions */
function deleteReport(reportid) {
    if(confirm('Are you sure you want to delete this report?'))
        xajax.request({xjxfun:'xajaxDispatch'},{URI:xajaxUri,parameters:['admin/admin_review_reports','_delete','jreviews',{data:{ReviewReport:{id:reportid}}}]});
}

/* Field functions */
function deleteField(fieldid) {
    xajax.$('fieldid').value = fieldid;
    if(confirm('This action will also delete all the information already stored for this field. Do you want to continue?.'))
        xajax.request({xjxfun:'xajaxDispatch'},{URI:xajaxUri,parameters:['admin/fields','_delete','jreviews',xajax.getFormValues('adminForm')]});
}

/* Criteria functions */
function deleteCriteria(criteriaid,uri) {
    if(confirm('If you delete this criteria set all reviews for items that have this criteria assigned will also be deleted. Do you want to continue?'))
        xajax.request({xjxfun:'xajaxDispatch'},{URI:xajaxUri,parameters:['admin/criterias','delete','jreviews',{row_id:criteriaid}]});
}

function addNewCriteria(rowId)
{
    var tbody = document.getElementById('criteria_list').tBodies[0]; 
    var row = document.createElement('tr');
    row.setAttribute('id', rowId);
    var cell1 = document.createElement('td'); 
    var inp1 = document.createElement('input'); 
    inp1.setAttribute('name','data[Criteria][criteria]['+rowId+']');
    inp1.setAttribute('size',35);
    cell1.appendChild(inp1);
    var cell2 = document.createElement('td');
    cell2.style.textAlign = 'center';
    var inp2a = document.createElement('input');
    inp2a.setAttribute('name','data[Criteria][required]['+rowId+']');
    inp2a.setAttribute('type','hidden');
    inp2a.setAttribute('value','0');
    cell2.appendChild(inp2a);
    var inp2b = document.createElement('input');
    inp2b.setAttribute('name','data[Criteria][required]['+rowId+']'); // if checked, will override first 'Required' element value
    inp2b.setAttribute('type','checkbox');
    inp2b.setAttribute('value','1');
    inp2b.setAttribute('id', 'required'+rowId);
    /*inp2b.onclick = function () { disableWeight(row.getAttribute('id')) }; // must be done exactly in this format for IE*/
    cell2.appendChild(inp2b); // append first to overcome IE bug with 'checked'
    inp2b.setAttribute('checked','checked');
    var cell3 = document.createElement('td'); 
    var inp3 = document.createElement('input'); 
    inp3.setAttribute('name','data[Criteria][weights]['+rowId+']');
    inp3.setAttribute('size',5);
    inp3.setAttribute('id', 'weight'+rowId);
    inp3.onkeyup = function () { sumWeights() }; // must be done exactly in this format for IE
    cell3.appendChild(inp3);
    var cell4 = document.createElement('td');
    var inp4 = document.createElement('input'); 
    inp4.setAttribute('name','data[Criteria][tooltips]['+rowId+']');
    inp4.setAttribute('size',50);
    cell4.appendChild(inp4);
    var cell5 = document.createElement('td');
    var inp5 = document.createElement('button'); 
    inp5.innerHTML = 'Remove';
    inp5.setAttribute('class','ui-button');
    inp5.setAttribute('className','ui-button'); // IE
    // inp5.setAttribute('onclick', 'removeCriteria('+rowId+')'); works only in FF
    inp5.onclick = function () { removeCriteria(row.getAttribute('id'));return false; }; // must be done exactly in this format for IE
    cell5.appendChild(inp5);
    row.appendChild(cell1);
    row.appendChild(cell2); 
    row.appendChild(cell3); 
    row.appendChild(cell4); 
    row.appendChild(cell5); 
    tbody.appendChild(row);
    return ++rowId;
}
function removeCriteria(rowId)
{
    var tbl=document.getElementById('criteria_list').tBodies[0];

    for ( var i = 1; i < tbl.rows.length; i++ ) // we don't need the first row, it's titles
    {    
        if ( tbl.rows.length == 2 ) // there is only one input row - don't remove it, clean it
        {
            var inputs = tbl.rows[1].getElementsByTagName("input");
            
            for ( var j = 0; j < inputs.length; j++ )
            {
                if ( inputs[j].type == 'checkbox' ) // 'Required' checkbox defaults to yes
                {
                    inputs[j].checked = true;
                }
                
                else if ( inputs[j].type != 'button' )
                {
                    inputs[j].value = '';
                //    inputs[j].disabled = false;
                }
                
            }
            
            return;
        }
        
        if ( tbl.rows[i].getAttribute('id') == rowId )
        {    
            var deltr = tbl.rows[i];
            break;
        }
    }
    
    tbl.removeChild(deltr);
    
    sumWeights();
}
function sumWeights()
{
    var tbl = document.getElementById('criteria_list').tBodies[0].rows;
    var sumw = 0;
    
    for ( var i = 1; i < tbl.length; i++ ) // no title row
    {
        sumw += document.getElementById('weight'+tbl[i].id).value * 1; // using tbl[i].id allows it to work even when rows are removed and ID's get scrambled
    }
    
    document.getElementById('title_weights').style.display = 'inline';
    document.getElementById('sum_weights').innerHTML = ( isNaN(sumw) ? 'Invalid' : ( sumw == 0 ? 'No weights' : sumw) );
    
    document.getElementById('sum_weights').style.color = sumw == 100 ? 'blue' : 'black';
}

/* Category functions */ 
function removeCategories() {
    if(xajax.$('cat_id').value > 0) {
        xajax.$('boxchecked').value = 0;
    }
    if(confirm('Are you sure you want to remove the selected categories from working with jReviews. The categories will NOT be deleted, but the review system will no longer work for listings in the selected categories.'))
        xajax.request({xjxfun:'xajaxDispatch'},{URI:xajaxUri,parameters:['admin/categories','delete','jreviews',xajax.getFormValues('adminForm')]});
}

function deleteFieldOption(optionid) {
    if(confirm('Are you sure you want to delete this option?'))
        xajax.request({xjxfun:'xajaxDispatch'},{URI:xajaxUri,parameters:['admin/fieldoptions','delete','jreviews',{data:{FieldOption:{optionid:optionid}}}]});
}

function toggleImage(element,img1,img2) {
    element.src = element.src.search(img1) > 0 ? img2 : img1;
}


function fieldValidate(str) {
  if (str.value != '')
    str.value= str.value.replace(/[^a-zA-Z]+/g,'');
    str.value = str.value.toLowerCase();
}
function createOptionsInput(type,location,demo) {
    var multipleOption1 = 'multiple';
    var multipleOption2 = 'checkboxes';
    var multipleOption3 = 'website';
    var multipleOption4 = 'email';

    if (type == 'code') {

        document.getElementById('type_desc').innerHTML = 'Add code for paypal, amazon, ect. <span style="color: red;font-weight:bold;">Careful with the access for this field.</span>';
        if (demo) {
            document.getElementById('type_desc').innerHTML = 'This field type is disabled in the demo';
            document.getElementById('type').value = '';
        }
    } else if ( type.search(multipleOption3)>=0 || type.search(multipleOption4)>=0) {

        document.getElementById('jr_click2search').innerHTML = ('<b>This field type cannot be enabled for click2search.</b>').fontcolor("Red");
        document.getElementById('jr_click2search_tr').style.backgroundColor = "#FFF82A";

    } else if ( location=='content' && (type.search(multipleOption1)>=0 || type.search(multipleOption2)>=0) ) {

        document.getElementById('jr_sortlist').innerHTML = ('<b>This field type is not sortable.</b>').fontcolor("Red");
        document.getElementById('jr_sortlist_tr').style.backgroundColor = "#FFF82A";

    } else if ( location=='content') {
        document.getElementById('type_desc').innerHTML = '';

        // Return sort feature to default status
        document.getElementById('jr_sortlist').innerHTML = 'Shows the field in the dropdown list';
        document.getElementById('jr_sortlist_tr').style.backgroundColor = "#FFFFFF";

        // Return click2search feature to default status
        document.getElementById('jr_click2search').innerHTML = 'Makes field text clickable to find other items with the same value, except website field.';
        document.getElementById('jr_click2search_tr').style.backgroundColor = "#FFFFFF";

    } else {
        // Return website feature to default status
        document.getElementById('website_title').style.display = "none";
    }
}

/* Owner reply functions */
function saveReply(formName) {
    xajax.request({xjxfun:'xajaxDispatch'},{URI:xajaxUri,parameters:['admin/admin_owner_replies','_save','jreviews',xajax.getFormValues(formName)]});
}

/* Predefined email reply functions */
function showCannedResponse(recordId, predefinedReplyId, suffix){
    if(predefinedReplyId!=''){
        jQuery('#jr_emailBody'+suffix+recordId).val( jQuery('#jr_cannedResponse'+suffix+predefinedReplyId).html() );
        jQuery('#jr_emailSubject'+recordId).val(jQuery('#jr_cannedResponseSelect'+recordId+' option:selected').text());
    } else {
        jQuery('#jr_emailBody'+suffix+recordId).val('');
    }
}

/* Field functions */
/* Click2Add */
function submitOption(fieldid,fieldname) 
{
    xajax.$('spinner'+fieldid).style.display = 'inline';
    xajax.$('submitButton'+fieldid).disabled=true;
    xajax.request({xjxfun:'xajaxDispatch'},{URI:xajaxUri,parameters:['field_options','_addOption',{data:{text:xajax.$('option'+fieldid).value,field_id:fieldid,name:fieldname}}]});
}
