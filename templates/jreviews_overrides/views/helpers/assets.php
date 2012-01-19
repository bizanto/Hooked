<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2009 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

/**
* All required css/js assets are conviniently defined here per controller and controller action (per page)
*/
defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );
        
class AssetsHelper extends MyHelper
{
    var $helpers = array('html','libraries','custom_fields','editor');
    var $assetParams = array();
    /**
    * These arrays can be set at the controller level 
    * and in plugin callbacks with any extra css or js files that should be loaded
    * 
    * @var mixed
    */
    var $assets = array('js'=>array(),'css'=>array());
    
    function load()
    {                            
        $assetParams = func_get_args();
        $this->assetParams = array_merge($this->assetParams,$assetParams);
        $methodAction = Inflector::camelize($this->name.'_'.$this->action);
        $methodName = Inflector::camelize($this->name);

        if(method_exists($this,$methodAction)){
            $this->{$methodAction}();
        } elseif(method_exists($this,$methodName)) {
            $this->{$methodName}();            
        } elseif(!empty($this->assets))
        {
            $this->send($this->assets);
        }
    }
    
    function send($assets,$inline=false)
    {         
        # Load javascript libraries
        $findjQuery = false;
        $this->Html->app = $this->app;
        
        // Incorporate controller set assets before sending
        if(!empty($this->assets['js'])) 
            $assets['js'] = array_merge($assets['js'],$this->assets['js']);
  
        if(!empty($this->assets['css'])) 
            $assets['css'] = array_merge($assets['css'],$this->assets['css']);
            
        cmsFramework::isRTL() and $assets['css'][] = 'rtl';
                
         // For CB and JomSocial prevent jQuery from loading twice
         // Check is done against constants defined in those applications
        if(isset($assets['js']) && !empty($assets['js']))
            {
                $findjQuery = array_search('jquery',$assets['js']);
                $findjQueryUI = array_search('jq.ui.core',$assets['js']);
                $findjQueryUICss = array_search('jq.ui.core',$assets['css']);
                if($findjQuery !== false)
                {
                    if (defined('J_JQUERY_LOADED') || defined('C_ASSET_JQUERY')) 
                    {
                        unset($assets['js'][$findjQuery]);
//                        unset($assets['js'][$findjQueryUI],$assets['css'][$findjQueryUI]);                            
                    } else {
                        define( 'J_JQUERY_LOADED', 1 );  
                        define( 'C_ASSET_JQUERY', 1 );
                    }
                }                
            }

        if(isset($assets['js']) && !empty($assets['js']))
            {
                $this->Html->js(arrayFilter($assets['js'], $this->Libraries->js()),$inline);            
            }

        # Load CSS stylesheets
        if(isset($assets['css']) && !empty($assets['css']))
            {       
                $findjQueryUI = array_search('jq.ui.core',$assets['css']);
                if($findjQueryUI !== false)
                {
                    if (defined('J_JQUERYUI_LOADED')) {
                        unset($assets['css'][array_search('jq.ui.core',$assets['css'])]);
                    } else {
                        define( 'J_JQUERYUI_LOADED', 1 );  
                    }
                } 
                $this->Html->css(arrayFilter($assets['css'], $this->Libraries->css()),$inline);                             } 
            
        # Set jQuery defaults
        if($findjQuery && isset($assets['js']['jreviews'])){
        ?>
            <script type="text/javascript">
            jreviews.ajax_init();
            </script>
        <?php            
        }

        if(Sanitize::getBool($this->Config,'ie6pngfix'))
        {
            $App = &App::getInstance($this->app);
            $AppPaths = $App->{$this->app.'Paths'};            
            $jsUrl = isset($AppPaths['Javascript']['jquery/jquery.pngfix.pack.js']) ? $AppPaths['Javascript']['jquery/jquery.pngfix.pack.js'] : false;         
            if($jsUrl)
            {
                cmsFramework::addScript('<!--[if lte IE 6]><script type="text/javascript" src="'.$jsUrl.'"></script><script type="text/javascript">jQuery(document).ready(function(){jQuery(document).pngFix();});</script><![endif]-->');        
            }
            unset($App,$AppPaths);
        }
    }

/**********************************************************************************
 *  Categories Controller
 **********************************************************************************/   
     function Categories()             
     {                                 
        $assets = array(
            'js'=>array('jreviews','jquery','jreviews.compare','jq.ui.core','jq.ui.slider','jq.json','jq.jsoncookie'),
            'css'=>array('theme','theme.list','paginator','jq.ui.core')
        );
              
        $this->_user->id>0 and array_push($assets['js'],'jq.jreviews.plugins');     
        ?>

        <script type="text/javascript">
        jQuery(document).ready(function() {                                                 
            jreviewsCompare.set({
                'numberOfListingsPerPage':<?php echo Sanitize::getInt($this->Config,'list_compare_columns',3);?>,
                'lang': {
                    'compare_all':'<?php __t("Compare All",false,true);?>',
                    'remove_all':'<?php __t("Remove All",false,true);?>',
                    'select_more':'<?php __t("You need to select more than one listing for comparison!",false,true);?>'
                },
                'compareURL':'<?php echo cmsFramework::route('index.php?option=com_jreviews&url=categories/compare/type:type_id/');?>'
            });
            <?php if($this->action == 'compare'):?>jreviewsCompare.initComparePage();<?php endif; ?>    
            jreviewsCompare.initCompareDashboard();
            <?php if($this->action != 'compare'): ?>jreviewsCompare.initListPage();<?php endif; ?>
            // fix the height of divs on comparison page after everything is loaded    
            jQuery(window).load(function(){ jreviewsCompare.fixCompareImagesAlignment(); });             
        });
        </script>   
       
		<?php
        $this->send($assets);        
     }
         
/**********************************************************************************
 *  ComContent Controller
 **********************************************************************************/ 
    function ComContentComContentView()
    {
        $assets = array(
        // Use this one to enable tabs
            'js'=>array('jreviews','jquery','jq.ui.core','jq.ui.tabs','jq.jreviews.plugins','jq.fancybox'), 
            //'js'=>array('jreviews','jquery','jq.ui.core','jq.jreviews.plugins','jq.fancybox'),
            'css'=>array('theme','theme.detail','theme.form','paginator','jq.ui.core','jq.fancybox')
        );
                
        if(!isset($this->assetParams['review_fields'])){
            $datePickerCheck = true;
            $addReviewCheck = true;
        } else {
            $datePickerCheck = $this->CustomFields->findDateField($this->assetParams['review_fields']);
            $addReviewCheck = $this->Access->canAddReview(); 
        }
                
        if($addReviewCheck) 
        {                 
            if($datePickerCheck) { // Check to determine whether datepicker library is loaded
                $assets['js']['jq.ui.datepicker'] = 'jq.ui.datepicker';
            }
            
            if($this->Config->rating_selector == 'stars'){
                $assets['js'][] = 'jq.ui.rating';                                
                $assets['css'][] = 'jq.ui.rating';
            }            
            
            $assets['js'][] = 'jq.tooltip';
            $assets['css'][] = 'jq.tooltip';

            if($this->Access->isManager())
            {             
                $assets['js'][] = 'jq.autocomplete';
                $assets['css'][] = 'jq.autocomplete';
            }              
        }
        ?>
        
        <script type="text/javascript">    
        jQuery(document).ready(function() 
        {         
            jreviews.lightbox();
            <?php if($addReviewCheck): // Init tooltips for reviews ?>
            jreviews.tooltip();
            <?php endif;?>
            <?php if($addReviewCheck && $datePickerCheck): // Init datepicker for review fields?>
            jreviews.datepicker();
            <?php endif;?> 
            
            <?php if($this->Access->canAddReview() && !$this->Access->moderateReview() && $this->Config->facebook_enable && $this->Config->facebook_reviews):?>                               
            if(!jQuery('#fb-root').length) jQuery("body").append('<div id="fb-root"></div>');
            jreviews.facebook.init({
                'appid':'<?php echo $this->Config->facebook_appid;?>',
                'optout':<?php echo $this->Config->facebook_optout;?>,
                'success':function(){
                    jreviews.facebook.checkPermissions({
                        'onPermission':function(){jreviews.facebook.setCheckbox('jr_submitButton',true);},
                        'onNoSession':function(){jreviews.facebook.setCheckbox('jr_submitButton',false);}
                    });
                },
                'publish_text': '<?php __t("Publish to Facebook");?>'
            });           
            <?php endif;  ?>
			jQuery("#jr_tabs").tabs();   //	Add this line	
			//jQuery("#jr_tabs").tabs({ event: 'mouseover' });
        });       
        </script> 
        <?php  
                  
        $this->send($assets);
    }
    
    function ComContentComContentBlog()
    {
        $assets = array(
            'js'=>array('jreviews'),
            'css'=>array('theme','theme.list')
        );

        $this->send($assets);       
    } 
     
/**********************************************************************************
 *  Community Listings Plugin   Controller
 **********************************************************************************/   
     function CommunityListings()
     {
        $assets = array();
        $inline = in_array(getCmsVersion(),array(CMS_JOOMLA10,CMS_MAMBO46));
        
        if(Sanitize::getInt($this->params['module'],'ajax_nav',1)) {
            $assets['js'] = array('jreviews','jquery'=>'jquery','jq.scrollable');
        }                                           
        $assets['css'] = array('theme','plugins','paginator');

        $this->send($assets,$inline);        
     } 
     
/**********************************************************************************
 *  Community Reviews Plugin   Controller
 **********************************************************************************/   
     function CommunityReviews()
     {
        $assets = array();
        $inline = in_array(getCmsVersion(),array(CMS_JOOMLA10,CMS_MAMBO46));

        if(Sanitize::getInt($this->params['module'],'ajax_nav',1)) {
            $assets['js'] = array('jreviews','jquery'=>'jquery','jq.scrollable');
        }                                           
        $assets['css'] = array('theme','plugins','paginator');

        $this->send($assets,$inline);          
     }          
     
/**********************************************************************************
 *  Directories Controller
 **********************************************************************************/   
     function DirectoriesDirectory()
     {
         $assets = array(
            'css'=>array('theme','theme.directory')
         );
         
        $this->send($assets);        
     }
         
/**********************************************************************************
 *  Discussions Controller
 **********************************************************************************/   
     function Discussions()
     {
        $assets = array(
            'js'=>array('jreviews','jquery','jq.ui.core','jq.jreviews.plugins','jq.popover'),
            'css'=>array('theme','jq.ui.core','theme.discussion','theme.detail','theme.form','paginator')
        );

        $this->send($assets);        

        ?>
        <script type="text/javascript">
        jQuery(document).ready(function() {
            jreviews.discussion.parentCommentPopOver();
        });
        </script>    
        <?php     
     }
               
/**********************************************************************************
 *  Everywhere Controller
 **********************************************************************************/     
    function EverywhereIndex() 
    {                      
        // need to load jQuery for review edit/report and voting
        $assets = array(
            'js'=>array('jreviews','jquery'=>'jquery','jq.ui.core','jq.jreviews.plugins'),
            'css'=>array('theme','theme.detail','theme.form','jq.ui.core','paginator')
        );

        if(!isset($this->assetParams['review_fields'])){
            $datePickerCheck = true;
            $addReviewCheck = true;
        } else {
            $datePickerCheck = $this->CustomFields->findDateField($this->assetParams['review_fields']);
            $addReviewCheck = $this->Access->canAddReview(); 
        }
        
        if($addReviewCheck) 
        {                 
            if($datePickerCheck) { // Check to determine whether datepicker library is loaded
                $assets['js']['jq.ui.datepicker'] = 'jq.ui.datepicker';
            }
            
            if($this->Config->rating_selector == 'stars'){
                $assets['js'][] = 'jq.ui.rating';                                
                $assets['css'][] = 'jq.ui.rating';
            }            
            
            $assets['js'][] = 'jq.tooltip';
            $assets['css'][] = 'jq.tooltip';

            if($this->Access->isManager())
            {             
                $assets['js'][] = 'jq.autocomplete';
                $assets['css'][] = 'jq.autocomplete';
            }              
        }      
       ?>
        <script type="text/javascript">
        jQuery(document).ready(function() 
        {
            <?php if($addReviewCheck): // Init tooltips for reviews ?>
            jreviews.tooltip();
            <?php endif;?>
            <?php if($addReviewCheck && $datePickerCheck): // Init datepicker for review fields?>
            jreviews.datepicker();
            <?php endif;?> 
            <?php if($this->Access->canAddReview && !$this->Access->moderateReview() && $this->Config->facebook_enable && $this->Config->facebook_reviews):?>
            if(!jQuery('#fb-root').length) jQuery("body").append('<div id="fb-root"></div>');
            jreviews.facebook.init({
                'appid':'<?php echo $this->Config->facebook_appid;?>',
                'optout':<?php echo $this->Config->facebook_optout;?>,
                'success':function(){
                    jreviews.facebook.checkPermissions({
                        'onPermission':function(){jreviews.facebook.setCheckbox('jr_submitButton',true);},
                        'onNoSession':function(){jreviews.facebook.setCheckbox('jr_submitButton',false);}
                    });
                },
                'publish_text': '<?php __t("Publish to Facebook");?>'
            });
            <?php endif;?>
        });
        </script>    
        <?php            
                
        $this->send($assets);               
    }
    
    function EverywhereCategory()
    {
        if(Sanitize::getString($this->params,'option')!='com_comprofiler'){
            $assets = array('css'=>array('theme'));
            $this->send($assets);
        }        
    }
    
/**********************************************************************************
 *  Listings Controller
 **********************************************************************************/    
    function ListingsCreate()
    {
        $assets = array(
            'js'=>array('jreviews','jquery','jq.ui.core','jq.ui.rating','jq.ui.datepicker'
                ,'jq.tooltip','jq.selectboxes','jq.jreviews.plugins'),
            'css'=>array('theme','theme.form','jq.tooltip','jq.ui.core','jq.ui.rating')
        );
        $this->send($assets);
        
        # Transforms class="wysiwyg_editor" textareas
        if($this->Access->loadWysiwygEditor()) {
            $this->Editor->load(); 
//            $this->Editor->transform();
        }
        if($this->Config->facebook_enable && $this->Config->facebook_listings && !$this->Access->moderateListing()):?>
            <script type="text/javascript">
            jQuery(document).ready(function() {
                jreviews.facebook.enable = true;
                if(!jQuery('#fb-root').length) jQuery("body").append('<div id="fb-root"></div>');
                jreviews.facebook.init({
                    'appid':'<?php echo $this->Config->facebook_appid;?>',
                    'optout':<?php echo $this->Config->facebook_optout;?>,
                    'publish_text': '<?php __t("Publish to Facebook");?>'
                });  
            });
            </script>
        <?php endif;      
    }

    function ListingsEdit()
    {
        $assets = array(
            'js'=>array('jreviews','jquery','jq.ui.core','jq.ui.rating','jq.ui.datepicker'
                ,'jq.tooltip','jq.selectboxes','jq.jreviews.plugins'),
            'css'=>array('theme','theme.form','jq.tooltip','jq.ui.core','jq.ui.rating')
        );
        
        $this->send($assets);
        
        # Transforms class="wysiwyg_editor" textareas
        if($this->Access->loadWysiwygEditor()) {
            $this->Editor->load(); 
            $this->Editor->transform();
        }
        
        ?>
        <script type="text/javascript">jreviews.datepicker();</script>    
        <?php               
    }
    
    function ListingsDetail()
    {
        $assets = array(
            'js'=>array('jreviews','jquery','jq.ui.core','jq.ui.rating','jq.jreviews.plugins'),
            'css'=>array('theme','theme.detail','paginator','jq.ui.core','jq.ui.rating')
        );
        
        if($this->Access->isManager())
        {             
            $assets['js'][] = 'jq.autocomplete';
            $assets['css'][] = 'jq.autocomplete';
        }              
                    
        $this->send($assets);        
    }    
    
/**********************************************************************************
 *  Module Advanced Search Controller
 **********************************************************************************/    
    function ModuleAdvancedSearch()
    {
        $inline = in_array(getCmsVersion(),array(CMS_JOOMLA10,CMS_MAMBO46));                    
        $assets = array(
             'js'=>array('jreviews','jquery'),
             'css'=>array('theme','theme.form')
        );
        $this->send($assets,$inline);        
    } 
    
/**********************************************************************************
 *  Module Directories Controller
 **********************************************************************************/    
    function ModuleDirectories()
    {
        $module_id = Sanitize::getInt($this->params,'module_id',rand());        
        $inline = in_array(getCmsVersion(),array(CMS_JOOMLA10,CMS_MAMBO46));                    
        $assets = array('js'=>array('jquery','jq.treeview'),'css'=>array('theme','jq.treeview'));
        $this->send($assets,$inline);        
        // Render tree view
        cmsFramework::addScript("
            <script type=\"text/javascript\">
            jQuery(document).ready(function() {
                        jQuery('#jr_treeView{$module_id}').treeview({
                            animated: 'fast',
                            unique: true,
                            collaped: false,
                            persist: 'location'
                        });
                    });   
             </script>            
        ");
    }          
      
/**********************************************************************************
 *  Module Favorite Users Controller
 **********************************************************************************/    
    function ModuleFavoriteCbusers()
    {
        $assets= array();
        $inline = in_array(getCmsVersion(),array(CMS_JOOMLA10,CMS_MAMBO46));
        if(Sanitize::getInt($this->params['module'],'ajax_nav',1)) {
            $assets['js'] = array('jreviews','jquery','jq.scrollable');    
        }
        $assets['css'] = array('modules','paginator');
        $this->send($assets,$inline);        
    } 
       
/**********************************************************************************
 *  Module Fields Controller
 **********************************************************************************/    
    function ModuleFields()
    {
        $assets= array();
        $inline = in_array(getCmsVersion(),array(CMS_JOOMLA10,CMS_MAMBO46));
        $assets['css'] = array('theme','modules');
        $this->send($assets,$inline);        
    } 
    
/**********************************************************************************
 *  Module Range Controller
 **********************************************************************************/    
    function ModuleRange()
    {
        $assets= array();
        $inline = in_array(getCmsVersion(),array(CMS_JOOMLA10,CMS_MAMBO46));
        $assets['css'] = array('theme','modules');
        $this->send($assets,$inline);        
    }     
           
/**********************************************************************************
 *  Module Listings Controller
 **********************************************************************************/    
    function ModuleListings()
    {
        $assets = array();
        $inline = in_array(getCmsVersion(),array(CMS_JOOMLA10,CMS_MAMBO46));
      
        if(Sanitize::getInt($this->params['module'],'ajax_nav',1)) {
            $assets['js'] = array('jreviews','jquery'=>'jquery','jq.scrollable');
        }                                           
        
        $assets['css'] = array('theme','modules','paginator');
      
        $this->send($assets,$inline);        
    } 

/**********************************************************************************
 *  Module Listings Controller
 **********************************************************************************/    
    function ModuleReviews()
    {
        $assets = array();
        $inline = in_array(getCmsVersion(),array(CMS_JOOMLA10,CMS_MAMBO46));
       
        if(Sanitize::getInt($this->params['module'],'ajax_nav',1)) {
            $assets['js'] = array('jreviews','jquery'=>'jquery','jq.scrollable');
        }                                           
        
        $assets['css'] = array('theme','modules','paginator');
            
        $this->send($assets,$inline);        
    } 
                    
/**********************************************************************************
 *  Reviews Controller
 **********************************************************************************/    
    function ReviewsCreate()
    {
        //
    }
       
    function ReviewsLatest()
    {
        $assets = array(
            'js'=>array('jreviews','jquery','jq.ui.core','jq.jreviews.plugins'),
            'css'=>array('theme','theme.detail','theme.form','jq.ui.core','paginator')
        );
        if($this->Access->isManager())
        {             
            $assets['js'][] = 'jq.autocomplete';
            $assets['css'][] = 'jq.autocomplete';
        }    
        $this->send($assets);        
    }  

    function ReviewsMyReviews()
    {
        $assets = array(
            'js'=>array('jreviews','jquery','jq.ui.core','jq.jreviews.plugins'),
            'css'=>array('theme','theme.detail','theme.form','jq.ui.core','paginator')
        );
        if($this->Access->isManager())
        {             
            $assets['js'][] = 'jq.autocomplete';
            $assets['css'][] = 'jq.autocomplete';
        }    
        if($this->Config->rating_selector== 'stars' && $this->_user->id > 0){
            $assets['js'][] = 'jq.ui.rating';                
            $assets['css'][] = 'jq.ui.rating';
        }  
        $this->send($assets);        
    }  
    
    function ReviewsRankings()
    {
        $assets = array(
            'css'=>array('theme','paginator')
        );
        $this->send($assets);        
    }      
     
/**********************************************************************************
 *  Search Controller
 **********************************************************************************/    
    function SearchAdvanced()
    {
        $assets = array(
            'js'=>array('jreviews','jquery','jq.ui.core','jq.tooltip','jq.ui.datepicker','jq.jreviews.plugins'),
            'css'=>array('theme','theme.form','jq.tooltip','jq.ui.core')
        );
        
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function() 
        {
            jreviews.datepicker();
        });
        </script>    
        <?php
        $this->send($assets);        
    }    
}
