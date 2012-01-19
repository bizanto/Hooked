/**
 * JReviews - Reviews Extension
 * Copyright (C) 2009 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/ 
jreviews = 
{       
    ajax_params: function() 
        {
            return '&format=raw&Itemid='+jr_publicMenu; 
        },
    ajax_init: function()
        {
            jQuery(document).ready(function() 
                {               
                    /* Set jQuery ajax defaults */
                    jQuery.ajaxSetup({
                      url: s2AjaxUri, // pass controller/action as hidden fields in form data[controller],data[action]
                      global: true,
                      cache: false
                    });
                    
                    /* jQuery ajax actions */
                    jQuery().ajaxSend( function( r, s ) {
                        jQuery('#s2AjaxResponse').remove();    
                        jQuery("body").append('<div id="s2AjaxResponse" style="display:none;"></div>'); 
                    });
                    
                    jQuery().ajaxStop( function( r, s ) {
                    });
                });          
        }, 
    getScript: function(script,callback)
    {
        jQuery.ajax({type: "GET",url: script, success: function(){if(undefined!=callback) callback();},dataType: "script", cache: true});            
    },   
    datepicker: function() 
        {              
                jQuery(document).ready(function() 
                {        
                    if(jQuery('.datepicker').length>0){
                        jQuery.datepicker.setDefaults({
                            showOn: 'button', 
                            buttonImage: datePickerImage, 
                            buttonImageOnly: true,
                            buttonText: 'Calendar',
                            dateFormat: 'yy-mm-dd',
                            changeMonth: true,
                            changeYear: true  
                            });    
                        /* attach datepicker to all date input fields */
                        jQuery('.datepicker').datepicker();    
                    }
            });          
        },
    discussion:
        {
            edit: function(element,options)
                {
                    jQuery(element).s2Dialog('jr_postEdit',{dialog:{width:'640px',height:'auto',title:options.title},dialogData:{url:'discussions/_edit/post_id:'+options.discussion_id}});                    
                },
            remove: function(element,options) 
                {
                    jQuery(element).s2Confirm(
                            {dialog:{title:options.title},
                             submitData:{'url':'discussions/_delete/post_id:'+options.discussion_id+'/token:'+options.token}
                            },options.text
                    );                    
                },    
            submit: function(element)
                {
                    jQuery(element).s2SubmitForm();
                },
            cancel: function(element,options)
                {
                    jQuery('#jr_postCommentFormOuter'+options.discussion_id).slideUp('slow',function(){
                        jQuery('#jr_postCommentAction'+options.discussion_id).slideDown('slow');
                    });                    
                },
            parentCommentPopOver: function()
                {
                    jQuery('.jr_popOver-target').each(function() 
                    {                       
                        var post_id = jQuery(this).attr('name');
                        jQuery(this).ezpz_tooltip({
                            contentPosition: 'bottomLeft',            
                            stayOnContent: true,  
                            offset: 0,      
                            beforeShow: function(content){
                                if (content.html() == "") {
                                    content.html('<span class="jr_loadingMedium"></span>');
                                    jQuery.ajax({
                                        url: xajaxUri+'&url=discussions/getPost/'+post_id+jreviews.ajax_params(),
                                        type: 'GET',
                                        dataType: 'html',
                                        success: function(response){ content.html(response); }
                                    });
                                }
                            }
                        });
                    });    
                },                
            showForm: function(element,options)
                {
                    jQuery(element).parents('div:eq(0)').slideUp('slow',function(){
                        jQuery('#jr_postCommentHeader'+options.discussion_id).css('display','block');
                        jQuery('#jr_postCommentFormOuter'+options.discussion_id).slideDown('slow');
                    });             
                }      
        },
    field: 
        {
            addOption: function(element,fieldid,fieldname)
            {
                var data = {
                    'data[controller]':'field_options',
                    'data[action]':'_addOption',
                    'data[text]':jQuery('#jr_fieldOption'+fieldid).val(),
                    'data[field_id]':fieldid,
                    'data[name]':fieldname
                };
                jQuery(element).siblings('.jr_validation').remove();
                jQuery(element).s2SubmitNoForm(
                    'field_options',
                    '_addOption',
                    jQuery.param(data)
                );
            }               
        },
    inquiry:
        {
            submit: function(element,options)
                {
                    jQuery('#jr_inquiryForm .jr_validation').hide();
                    var $spinner = jQuery(element).siblings('.jr_loadingSmall');
                    $spinner.fadeIn();
                    jQuery(element).attr('disabled','disabled');
                    jQuery.ajax({
                        url: s2AjaxUri,
                        type: 'POST',
                        dataType: 'json',
                        data: jQuery('#jr_inquiryForm').serialize()
                        ,success: function(s2Out){
                            if(s2Out.error != undefined){ 
                                jQuery('#jr_inquiryResponse').html(s2Out.error);
                                jQuery('#jr_inquirySubmit').removeAttr('disabled');                          
                            }
                           if(s2Out.html != undefined){                                        
                                jQuery('#jr_inquiryForm').fadeOut('slow',function(){
                                   jQuery(this).html(options.submit_text).slideDown(); 
                                });
                            }                             
                            $spinner.hide();
                        }
                    });                    
                }
        },
    favorite:
        {
            add: function(element,options)
                {
                    jQuery(element).s2SubmitNoForm('listings','_favoritesAdd','data[listing_id]='+options.listing_id);                       
                },
            remove: function(element,options)
                {
                    jQuery(element).s2SubmitNoForm('listings','_favoritesDelete','data[listing_id]='+options.listing_id);                       
                } 
        },
    module: 
        {
            /* deprecated */
            changePage: function(element,options)
                {
                    //var overlay = jQuery('#jr_modContainer'+options.module_id+' .jr_moduleOverlay');
                    //overlay.fadeIn(); 
                    jQuery.ajax({
                       type: 'GET',
                       url: s2AjaxUri,
                       data: 'data[controller]='+options.name+'&data[action]='+options.action+'&data[extension]='+options.extension+'&data[module_page]='+options.page+'&data[module_limit]='+options.limit+'&data[module_id]='+options.module_id+jreviews.ajax_params(),
                       dataType: 'json',
                       success: function(s2Out){
                            //jQuery('#jr_modContainer'+options.module_id).fadeTo("normal",0.6,function(){
                              //  jQuery(this).html(s2Out.response).fadeTo("normal",1);
                            //})
                            
                       }
                    });                    
                },
            pageNavInit: function(module_id, page, page_count)
                {
                    jQuery("div#jr_modSlider"+module_id).scrollable({
                        size: 1,
                        clickable: false,
                        loop: false,
                        interval: 0,
                        easing: 'swing',
                        speed: 1000,
                        items: '.jr_modItems',
                        prevPage: '.jr_modPrev',
                        nextPage: '.jr_modNext',
                        disabledClass: 'jr_modDisabled', 
                        keyboard: false,            
                        onSeek: function() { 
                            var pageAmount = this.getPageAmount();
                            if(pageAmount == 2 && page_count > 2) {
                                // If user clicks on the forward arrow, load two pages the first time to keep two in queue
                                jreviews.module.getPage(module_id);                                
                            }
                            if((this.getPageIndex() + 2) >= pageAmount && (pageAmount+1) < page_count) {
                                // Always keep two pages in queue and don't do anything if the last page had already been loaded
                                setTimeout('jreviews.module.getPage('+module_id+');',500); // delay so 2nd call always finishes last
                            }
                        }             
                    });
                    
                    if(page==1 && page_count > 1){                      
                        jreviews.module.getPage(module_id);                        
                    }  
                },    
            getPage: function(module_id)
                {
                    var controller = jQuery('#jr_modController'+module_id).val();
                    var action = jQuery('#jr_modAction'+module_id).val();
                    var extension = jQuery('#jr_modExtension'+module_id).val();
                    var page = parseInt(jQuery('#jr_modPage'+module_id).val()) + 1;
                    var limit = jQuery('#jr_modLimit'+module_id).val();
                    var page_count = jQuery('#jr_modPageCount'+module_id).val();                    
                    jQuery('#jr_modPage'+module_id).val(page); 
                    
                    jQuery.ajax({                                                                                                  
                       type: 'POST',                                                                                                          
                       url: s2AjaxUri,
                       data: 'data[controller]='+controller+'&data[action]='+action+'&data[extension]='+extension+'&data[module_page]='+page+'&data[module_limit]='+limit+'&data[module_id]='+module_id+jreviews.ajax_params(),
                       dataType: 'json',
                       success: function(s2Out)
                       {
                           var html = s2Out.response;
                            // get handle to scrollable api 
                            var api = jQuery("div#jr_modSlider"+module_id).scrollable(); 
                             
                            // append new item using jQuery's append() method 
                            api.getItemWrap().append(html); 

                            // rebuild scrollable and move to the end to see what happened     
                            api.reload();        
                            
                            if(api.getPageIndex() + 1 < page_count) {
                                jQuery('#jr_modNext'+module_id).removeClass('jr_modDisabled');
                            }
                       }
                    });                    
                }                
        },
    lightbox: function()
        {
            if(jQuery('a.fancybox').size()) 
                {
                    jQuery("a.fancybox").fancybox({
                        'zoomSpeedIn': 700, 
                        'zoomSpeedOut': 600, 
                        'overlayShow': true,
                        'zoomOpacity': true,
                        'padding': 4
                    }); 
                }                
        },        
    listing: 
        {
            claim: function (element,options)
                {
                    jQuery(element).s2Dialog('jr_claimListing',{
                            dialog:{width:'640px',height:'auto',title:options.title},
                            dialogData:{url:'claims/create/listing_id:'+options.listing_id}
                    });
                },
            remove: function (element,options)
                {                                                                       
                    var data = {'url':'listings/_delete/id:'+options.listing_id}
                    data[options.token] = 1;
                    jQuery(element).s2Confirm({'dialog':{'title':options.title},'submitData':data},options.text);   
                },  
            feature: function (element,options)
                {
                    jQuery.ajax({
                        url: s2AjaxUri,                        
                        data: 'url=listings/_feature&data[Listing][id]='+options.listing_id+'&data[Listing][featured]='+options.state+jreviews.ajax_params()+'&'+options.token+'=1',
                        type: 'POST',                        
                        dataType: 'json',
                        success: function(s2Out){
                            if(s2Out.error == false)
                            {
                                var listing_id = options.listing_id;
                                if(jQuery('#jr_featuredLink'+listing_id).is('.jr_published')){
                                    jQuery('#jr_featuredLink'+listing_id).removeClass().addClass('jr_unpublished').html(options.unpublished);                        
                                } else {
                                    jQuery('#jr_featuredLink'+listing_id).removeClass().addClass('jr_published').html(options.published);                        
                                }
                            } else 
                            {
                                s2Alert(s2Out.msg);
                            }
                        }
                    });                    
                },   
            frontpage: function (element,options)
                {
                    jQuery.ajax({
                        url: s2AjaxUri,                        
                        data: 'url=listings/_frontpage&data[Listing][id]='+options.listing_id+'&data[Listing][frontpage]='+options.state+jreviews.ajax_params()+'&'+options.token+'=1',
                        type: 'POST',                        
                        dataType: 'json',                                    
                        success: function(s2Out){
                            if(s2Out.error == false)
                            {
                                var listing_id = options.listing_id;
                                if(jQuery('#jr_frontpageLink'+listing_id).is('.jr_published')){
                                    jQuery('#jr_frontpageLink'+listing_id).removeClass().addClass('jr_unpublished').html(options.unpublished);                        
                                } else {
                                    jQuery('#jr_frontpageLink'+listing_id).removeClass().addClass('jr_published').html(options.published);                        
                                }
                            } else 
                            {
                                s2Alert(s2Out.msg);
                            }
                        }
                    });                    
                },                                                  
            publish: function (element,options)
                {
                    jQuery.ajax({
                        url: s2AjaxUri,                        
                        data: 'url=listings/_publish&data[Listing][id]='+options.listing_id+jreviews.ajax_params()+'&'+options.token+'=1',
                        type: 'POST',
                        dataType: 'json',                        
                        success: function(s2Out){
                            if(s2Out.error == false)
                            {
                                var listing_id = options.listing_id;
                                if(jQuery('#jr_publishLink'+listing_id).is('.jr_published')){
                                    jQuery('#jr_publishLink'+listing_id).removeClass().addClass('jr_unpublished').html(options.unpublished);                        
                                } else {
                                    jQuery('#jr_publishLink'+listing_id).removeClass().addClass('jr_published').html(options.published);                        
                                }
                            } else 
                            {
                                s2Alert(s2Out.msg);
                            }
                        }
                    });                    
                },              
            submit: function (element)
                {
                    var form = jQuery('#jr_listingForm');
                    form.find('#section').val(form.find('#section_id option:selected').text());
                    form.find('#category').val(form.find('#cat_id option:selected').text());
                    jQuery('.wysiwyg_editor').RemoveTinyMCE();
                    jQuery(element).siblings('.jr_loadingSmall').fadeIn();
                    jQuery('#jr_listingForm .button').attr('disabled','disabled');
                    document.jr_listingForm.submit();
                },
            submitSection: function (element)
                {
                    var $parentForm = jQuery('#jr_listingForm');
                    $parentForm.append('<input type="hidden" id="controller" name="data[controller]" value="listings" />');
                    $parentForm.append('<input type="hidden" id="action" name="data[action]" value="_loadCategories" />');
                    jQuery(element).s2SubmitForm();
                    jQuery('#controller').remove();
                    jQuery('#action').remove();   
                },
            submitCategory: function (element)
                {                   
                    jQuery('.wysiwyg_editor').RemoveTinyMCE(); /* required so the editor can be added again on new section/category changes*/
                    var $parentForm = jQuery('#jr_listingForm');                   
                    $parentForm.append('<input type="hidden" id="action" name="data[action]" value="_loadForm" />');
                    $parentForm.append('<input type="hidden" id="controller" name="data[controller]" value="listings" />');
                    var callbacks = {
                        onAfterResponse: function(){   
                            jQuery('.wysiwyg_editor').tinyMCE();
                            jreviews.tooltip();
                            jreviews.datepicker();
                            // Facebook integration           
                            if(jreviews.facebook.enable == true) {    
                                jreviews.facebook.checkPermissions({
                                    'onPermission':function(){jreviews.facebook.setCheckbox('jr_submitListing',true);},
                                    'onNoSession':function(){jreviews.facebook.setCheckbox('jr_submitListing',false);}
                                });
                            };  
                        }
                    };
                    jQuery(element).s2SubmitForm(callbacks);
                    jQuery('#controller').remove();
                    jQuery('#action').remove();   
                },
            setMainImage: function(element,options)
                {
                    jQuery(element).s2SubmitNoForm('listings','_imageSetMain','data[listing_id]='+options.listing_id+'&data[image_path]='+options.image_path+'&'+options.token+'=1');                    
                },
            deleteImage: function(element,options)
                {
                    var data =  {
                        'url':'listings/_imageDelete/',
                        'data[listing_id]':options.listing_id,
                        'data[delete_key]':options.delete_key,
                        'data[image_path]':options.image_path
                    };
                    data[options.token] = 1;
                    jQuery(element).s2Confirm({'dialog':{'title':options.title},'submitData': data},options.text);                    
                }        
        },
     review:
        {             
            starRating: function(suffix,inc)
                {
                    if(undefined == inc) inc = 1;
                    jQuery("div[id^='jr_stars"+suffix+"']").each(function(i) {
                        if( this.id != '' ) {
							jQuery(this).append('<span id="jr-rating-wrapper-' + this.id + '" style="line-height: 16px; margin-left: 4px;"></span>');
							var splitStars = 1/inc; // 2 for half star ratings
							jQuery("#"+this.id).stars({
								split: splitStars,
								captionEl: jQuery("#jr-rating-wrapper-" + this.id )
							});
                        }
                    });                        
                },
            showForm: function(element)
                {
                    jQuery(element).hide('slow',function(){
                        jQuery('#jr_review0Form').slideDown(1000,function(){
                            jQuery('#jr_review0Form').scrollTo({duration:1000,offset:-50});
                        });
                    });                
                },
            hideForm: function()
                {
                    jQuery('#review_button').show();
                    jQuery('#review_button').scrollTo({duration:500,offset:-50}, function(){jQuery('#jr_review0Form').fadeOut('slow');});                
                },
            edit: function(element,options)
                {
                    jQuery(element).s2Dialog('jr_review'+options.review_id,
                        {
                            dialog:{width:800,height:600,title:options.title},
                            dialogData:{url:'reviews/_edit/review_id:'+options.review_id},
                            onAfterDisplay: function() {jreviews.tooltip();}
                        });
                },
            submit: function(element)
                {
                    jQuery(element).s2SubmitForm();                    
                },
            reply: function(element,options)
                {
                    jQuery(element).s2Dialog('jr_ownerReply',
                        {
                            dialog:{width:'640px',height:'auto',title:options.title},
                            dialogData:{url:'owner_replies/create/review_id:'+options.review_id}
                        });                    
                },        
            voteNo: function(element,options)
                {
                    jQuery(element).s2SubmitNoForm('votes','_save','data[Vote][review_id]='+options.review_id+'&data[Vote][vote_no]=1');                    
                }, 
            voteYes: function(element,options)
                {
                    jQuery(element).s2SubmitNoForm('votes','_save','data[Vote][review_id]='+options.review_id+'&data[Vote][vote_yes]=1');                    
                }    
            
        },
     report:
        {
            showForm: function(element,options)
                {
                    jQuery(element).s2Dialog('jr_report',
                        {
                            dialog:{width:'640px',height:'auto',title:options.title},
                            dialogData:{url:'reports/create/listing_id:'+options.listing_id+'/review_id:'+options.review_id+'/post_id:'+options.post_id+'/extension:'+options.extension}
                        });                    
                }  
        },           
     search:
         {
            showRange: function(element,field) 
            {
                if(jQuery(element).val()=='between'){
                    jQuery('#'+field+'Div').fadeIn();
                } else {
                    jQuery('#'+field+'Div').fadeOut();                
                }    
            }
         },
     tooltip: function() 
         {
            if(jQuery.tooltip) 
            {
                jQuery('.jr_infoTip').tooltip({
                    track: false,
                    delay: 0,
                    showURL: false,
                    opacity: 0.95,
                    fixPNG: true             
                });             
            }
         },    
     user:
        {
            autocomplete: function(element,options)
                {
                    if(undefined == options.target_user_id) options.target_user_id = 'jr_reviewUserid';
                    if(undefined == options.target_name) options.target_name = 'jr_reviewName';
                    if(undefined == options.target_username) options.target_username = 'jr_reviewUsername';
                    if(undefined == options.target_email) options.target_email = 'jr_reviewEmail';
                    
                    var ac = element.autocomplete(s2AjaxUri+'&data[controller]=users&data[action]=_getList&tmpl=component&format=raw&no_html=1', 
                        { 
                //            autoFill: true,
                            delay:10,
                            width:250,
                            minChars:2, 
                            matchSubset:1, 
                            matchContains:1,
                            maxItemsToShow: 20, 
                            cacheLength:10, 
                            selectOnly:1, 
                            onItemSelect: function(li)
                                {
                                    if(li.extra)
                                        {       
                                            jQuery('#'+options.target_user_id).val(li.extra[0]);
                                            jQuery('#'+options.target_name).val(li.selectValue);
                                            jQuery('#'+options.target_username).val(li.extra[1]);
                                            jQuery('#'+options.target_email).val(li.extra[2]);                                                            
                                        }
                                },
                            onFindValue: function(li)
                            {
                                if(li == null) { // When value entered is not from the autosuggest list reset hidden fields
                                    jQuery('#'+options.target_user_id).val(0);
                                    jQuery('#'+options.target_name).val(jQuery(ac[0]).val());
                                    jQuery('#'+options.target_username).val(jQuery(ac[0]).val()); 
                                }
                            },   
                            formatItem: function(row)
                                {
                                    return row[0] + "- <i>" + row[2] + "</i> ("+ row[1] +")";                
                                }
                        }
                    );
                    element.val(options.default_val); 
                    element.blur(function(){
                       ac[0].autocompleter.findValue(); 
                    });  
                }
        },
    facebook:
    {
        enable: false,
        permissions: false, 
        uid: null,
        init: function(options) {
            if(undefined!=options) jreviews.facebook.options = options;    
            if('undefined'==typeof(FB)) {  // Load facebook js only if not already loaded
                jQuery.ajax({
                    type: "GET",
                    url: "http://connect.facebook.net/en_US/all.js",
                    success: function(){    
                        FB.init({appId: options.appid, status: false, cookie: true, xfbml: true});  
                        if(undefined!=options.success) options.success();
                    },
                    dataType: "script",
                    cache: true
                });            
            } else if(undefined!=options.success) {
                FB.init({appId: options.appid, status: false, cookie: true, xfbml: true});  
                options.success();                
            }
        },
        login: function()
        {     
            if(null == jreviews.facebook.uid) {
                FB.login(function(response) {
                    if (response.session && response.perms) {
                          // user is logged in and granted some permissions.
                          jreviews.facebook.uid = response.session.uid;
                    } else {
                        jQuery('#fb_publish').attr('checked',false);
                    } 
                }, {perms:'publish_stream'});              
            }
        },
        checkPermissions: function(options) {
            if(undefined==options) options = {};
            jQuery("body").data('fb.options',options);    
            FB.getLoginStatus(function(response) 
            {                   
                if(response.session) 
                {         
                      // logged in and connected user
                      jreviews.facebook.uid = response.session.uid;
                      FB.api({
                                method: 'fql.query',
                                query: 'SELECT publish_stream FROM permissions WHERE uid= ' + response.session.uid
                            },
                            function(response) { 
                                if(!response[0].publish_stream)
                                {
                                    // re-request publish_stream permission
                                    FB.login(function(response) {
                                        if (response.session && response.perms) 
                                        {                 
                                            // user is logged in and granted some permissions.
                                            var options = jQuery("body").data('fb.options');
                                            if(undefined!=options.onPermission) options.onPermission();  
                                            jreviews.facebook.permissions = true;
                                        }
                                    },{perms:'publish_stream'});                
                                } else {                
                                    var options = jQuery("body").data('fb.options');
                                    if(undefined!=options.onPermission) options.onPermission();                                            
                                    jreviews.facebook.permissions = true;
                                }
                          }
                    );  
                } 
                else   // User not logged in or has not granted publish_stream permission
                {   
                    jreviews.facebook.permissions = false;
                    if(undefined!=options.onNoSession) options.onNoSession();  
                }
            });    
        },
        setCheckbox: function(id,hidden) {
            if(hidden == true && !jreviews.facebook.options.optout) {                                                                                       
                jQuery('#'+id).before('<input id="fb_publish" name="data[fb_publish]" value="1" type="hidden"/>');                                                                                      
            }
            else
            {
                var fbcheckbox = '<input id="fb_publish" name="data[fb_publish]" type="checkbox" onclick="if(this.checked) jreviews.facebook.login();" />'
                    +'&nbsp;<div class="fb_button fb_button_medium"><span class="fb_button_text"><label for="fb_publish">'
                    +jreviews.facebook.options.publish_text
                    +'</label></span></div><br /><br />';  
                jQuery('#'+id).before(fbcheckbox);            
                if(hidden && jreviews.facebook.options.optout) jQuery('#fb_publish').attr("checked","checked");
            }    
        }
    }                     
}