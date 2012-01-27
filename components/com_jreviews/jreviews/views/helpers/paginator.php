<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

App::import('Helper','form','jreviews');

class PaginatorHelper extends MyHelper {
    
    var $base_url = null;
    var $items_per_page;
    var $items_total;
    var $current_page;
    var $num_pages;
    var $mid_range = 7;
    var $num_pages_threshold = 10; // After this number the previous/next buttons show up.
    var $return;
    var $return_module;
    var $module_id = 0;
    var $default_limit = 25;
    var $controller = null;
    var $action = 'index';
    var $ajax_scroll = true; // Scrolls up to defined element on ajax pagination
    var $scroll_id = 'page';
    var $form_id = 'adminForm'; // Target form having all paginator related inputs
    var $update_id = 'page'; // Id of element to update with the new page info
    
    function __construct()
    {
        $this->current_page = 1;
        $this->items_per_page = (!empty($_GET['limit'])) ? (int) $_GET['limit'] : $this->default_limit;  
    }
    
    function initialize($params = array())
    {
        if (count($params) > 0)
        {
            foreach ($params as $key => $val)
            {
                $this->$key = $val;
            }        
        }

        # Construct new route
        if(isset($this->passedArgs) && is_null($this->base_url))
            $this->base_url = cmsFramework::constructRoute($this->passedArgs,array('page','limit','lang'));         
    }
    
    function addPagination($page,$limit) 
    {          
        if(cmsFramework::isAdmin()) /* no need for sef urls in admin */
        {
            $url = rtrim($this->base_url,'/') . ($page > 1 ? '/' . 'page'._PARAM_CHAR.$page.'/limit'._PARAM_CHAR.$limit.'/' : '');
        }
        else 
        {
            $url = cmsFramework::route(rtrim($this->base_url,'/') . ($page > 1 ? '/' . 'page'._PARAM_CHAR.$page.'/limit'._PARAM_CHAR.$limit.'/' : '') );
        }
        $page == 1 and !strstr($url,'order') and $url = str_replace('&amp;url=menu','',$url);
        return $url;
    }
    
    function paginate($params, $update_id = 'page')
    {                     
        $this->return = '';
        $this->update_id = $update_id;
        $this->initialize($params);

        if(!is_numeric($this->items_per_page) OR $this->items_per_page <= 0) {
            $this->items_per_page = $this->default_limit;
        }
        
        $this->num_pages = ceil($this->items_total/$this->items_per_page);

        if($this->current_page < 1 || !is_numeric($this->current_page)) $this->current_page = 1;

        if($this->current_page > $this->num_pages) $this->current_page = $this->num_pages;

        $prev_page = $this->current_page-1;
        
        $next_page = $this->current_page+1;

        # More than num_pages_threshold pages
        if($this->num_pages > $this->num_pages_threshold)
        {
            // PREVIOUS PAGE
            if($this->ajaxRequest || $this->xajaxRequest) {
                $onclick = "
                    " . ($this->ajax_scroll ? "jQuery('#".$this->scroll_id."').scrollTo(500,100);" : '')."        
                    jQuery(this).parents('form').find('input[name=data\[page\]]').val($prev_page);
                    jQuery(this).parents('form').find('input[name=data\[limit\]]').val($this->items_per_page);
                    jQuery(this).parents('form').find('input[name=data\[action\]]').val('".$this->action."');
                    jQuery.post(s2AjaxUri,jQuery(this).parents('form').serialize(),function(s2Out){jQuery('#".$this->update_id."').html(s2Out);},'html');
                    return false;
                ";                

                $this->return = ($this->current_page != 1 && $this->items_total >= 10) ? '<a class="paginate" href="javascript:void(0);" onclick="'.$onclick.'">'.__t("&laquo; Previous",true).'</a> ' : '<span class="inactive" href="#">'.__t("&laquo; Previous",true).'</span>';                
            } else {
                $url = $this->addPagination($prev_page,$this->items_per_page);
                $this->return = ($this->current_page != 1 && $this->items_total >= 10) ? '<a class="paginate" href="'.$url.'">'.__t("&laquo; Previous",true).'</a> ' : '<span class="inactive" href="#">'.__t("&laquo; Previous",true).'</span> ';                
            }

            $this->start_range = $this->current_page - floor($this->mid_range/2);

            $this->end_range = $this->current_page + floor($this->mid_range/2);

            if($this->start_range <= 0)
            {
                $this->end_range += abs($this->start_range)+1;
                $this->start_range = 1;
            }
            if($this->end_range > $this->num_pages)
            {
                $this->start_range -= $this->end_range-$this->num_pages;
                $this->end_range = $this->num_pages;
            }
            $this->range = range($this->start_range,$this->end_range);

            // INDIVIDUAL PAGES
            for($i=1;$i<=$this->num_pages;$i++)
            {
                if($this->range[0] > 2 && $i == $this->range[0]) $this->return .= " ... ";
                
                // loop through all pages. if first, last, or in range, display
                if($i==1 Or $i==$this->num_pages || in_array($i,$this->range))
                {
                    if($this->ajaxRequest || $this->xajaxRequest) {
                        $onclick = "
                            " . ($this->ajax_scroll ? "jQuery('#".$this->scroll_id."').scrollTo(500,100);" : '') ."        
                            jQuery(this).parents('form').find('input[name=data\[page\]]').val($i);
                            jQuery(this).parents('form').find('input[name=data\[limit\]]').val({$this->items_per_page});
                            jQuery(this).parents('form').find('input[name=data\[action\]]').val('".$this->action."');
                            jQuery.post(s2AjaxUri,jQuery('#".$this->form_id."').serialize(),function(s2Out){jQuery('#".$this->update_id."').html(s2Out);},'html');
                            return false;
                        ";
                                    
                        $this->return .= ($i == $this->current_page) ?
                        '<a title="'.sprintf(__t("Go to page %s",true),$i,$i,$this->num_pages).'" class="current" href="#">'.$i.'</a> ' : 
                        '<a class="paginate" title="'.sprintf(__t("Go to page %s of %s",true),$i,$this->num_pages).'" href="javascript:void(0);" onclick="'.$onclick.'">'.$i.'</a> ';
                        
                    } else {
                        $url = $this->addPagination($i,$this->items_per_page);    
                        $this->return .= ($i == $this->current_page) ? 
                        '<a title="'.sprintf(__t("Go to page %s",true),$i,$i,$this->num_pages).'" class="current" href="#">'.$i.'</a> ' : 
                        '<a class="paginate" title="'.sprintf(__t("Go to page %s of %s",true),$i,$this->num_pages).'" href="'.$url.'">'.$i.'</a> ';
                    }
                }
                
                if($this->range[$this->mid_range-1] < $this->num_pages-1 && $i == $this->range[$this->mid_range-1]) $this->return .= " ... ";
            }
            
            // NEXT PAGE
            if($this->ajaxRequest || $this->xajaxRequest) {        
                $onclick = "
                    " . ($this->ajax_scroll ? "jQuery('#".$this->scroll_id."').scrollTo(500,100);" : '') ."        
                    jQuery(this).parents('form').find('input[name=data\[page\]]').val($next_page);
                    jQuery(this).parents('form').find('input[name=data\[limit\]]').val({$this->items_per_page});
                    jQuery(this).parents('form').find('input[name=data\[action\]]').val('".$this->action."');
                    jQuery.post(s2AjaxUri,jQuery('#".$this->form_id."').serialize(),function(s2Out){jQuery('#".$this->update_id."').html(s2Out);},'html');
                    return false;
                ";

                $this->return .= ($this->current_page != $this->num_pages && $this->items_total >= 10) ? 
                "<a class=\"paginate\" href=\"javascript:void(0);\" onclick=\"$onclick\">".__t("Next &raquo;",true)."</a>\n" : "<span class=\"inactive\" href=\"#\">".__t("Next &raquo;",true)."</span>\n";            
            } else {
                $url = $this->addPagination($next_page,$this->items_per_page);            
                $this->return .= ($this->current_page != $this->num_pages && $this->items_total >= 10) ? 
                "<a class=\"paginate\" href=\"$url\">".__t("Next &raquo;",true)."</a>\n" : "<span class=\"inactive\" href=\"#\">".__t("Next &raquo;",true)."</span>\n";                
            }
        
        }
        # num_pages_threshold pages or less
        else {

            // INDIVIDUAL PAGES            
            for($i=1;$i<=$this->num_pages;$i++)
            {
                // Ajax request
                if($this->ajaxRequest || $this->xajaxRequest) {
                    $onclick = "
                        " . ($this->ajax_scroll ? "jQuery('#".$this->scroll_id."').scrollTo(500,100);" : '') ."        
                        jQuery(this).parents('form').find('input[name=data\[page\]]').val($i);
                        jQuery(this).parents('form').find('input[name=data\[limit\]]').val({$this->items_per_page});
                        jQuery(this).parents('form').find('input[name=data\[action\]]').val('".$this->action."');
                        jQuery.post(s2AjaxUri,jQuery('#".$this->form_id."').serialize(),function(s2Out){jQuery('#".$this->update_id."').html(s2Out);},'html');
                        return false;
                    ";
                                                            
                    $this->return .= ($i == $this->current_page) ? '<span class="inactive" href="#">'.$i.'</span> ' : '<a class="paginate" href="javascript:void(0);" onclick="'.$onclick.'">'.$i.'</a> ';
                // Get request
                } else {
                    $url = $this->addPagination($i,$this->items_per_page);
                    $this->return .= ($i == $this->current_page) ? '<span class="inactive" href="#">'.$i.'</span> ' : '<a class="paginate" href="'.$url.'">'.$i.'</a> ';
                }
                
            }
        }
        
        # -------------------------------------------------------------------
        # Module Ajax Navigation
        # -------------------------------------------------------------------
        if(isset($this->params['module']))
        {
            $extension = Sanitize::getString($this->params['module'],'extension');
            $action = $this->action;

            // PREVIOUS PAGE
            $onclick = "jreviews.module.changePage(this,{module_id:{$this->module_id},name:'{$this->name}',action:'{$action}',extension:'{$extension}',page:{$prev_page},limit:{$this->items_per_page}});return false;";
            $this->return_module = ($this->current_page != 1) 
                ? 
                '<a class="paginate" href="#" onclick="'.$onclick.'">&lt;</a> ' : '<span class="inactive">&lt;</span> ';

            // NEXT PAGE
            $onclick = "jreviews.module.changePage(this,{module_id:{$this->module_id},name:'{$this->name}',action:'{$action}',extension:'{$extension}',page:{$next_page},limit:{$this->items_per_page}});return false;";
            $this->return_module .= ($this->current_page != $this->num_pages) 
                ? 
                "<a class=\"paginate\" href=\"#\" onclick=\"$onclick\">&gt;</a>" : "<span class=\"inactive\">&gt;</span>";            

        }
    }

    /**
     * Generates the dropdown list for number of items per page
     * @return html select list
     */
    function display_items_per_page()
    {
        $args = func_get_args();
        if(func_num_args()==2) // For compat with old themes that had the update id var as the 1st param
        {
            $this->update_id = array_shift($args);
            $items_per_page = array_shift($args);            
        } else {
            $items_per_page = array(5,10,15,20,25,30,35,40,45,50);            
        }
        
        $Form = RegisterClass::getInstance('FormHelper');
        
        $segments = '';
        $url_param = array();
        $passedArgs = $this->passedArgs;

        if($this->ajaxRequest || $this->xajaxRequest) 
        {                    
            foreach($items_per_page as $limit) {
                $selectList[] = array('value'=>$limit ,'text'=>$limit);
            }
    
            $selected = $this->limit; //Sanitize::getInt($this->data,'limit');
            
            $onchange = "
                " .($this->ajax_scroll ? "jQuery('#".$this->scroll_id."').scrollTo(500,100);" : ''). "        
                jQuery(this).parents('form').find('input[name=data\[page\]]').val(1);
                jQuery(this).parents('form').find('input[name=data\[limit\]]').val(this.value);
                jQuery(this).parents('form').find('input[name=data\[action\]]').val('".$this->action."');
                jQuery.post(s2AjaxUri,jQuery(this).parents('form').serialize(),function(s2Out){jQuery('#".$this->update_id."').html(s2Out);},'html');
            ";

            return __t("Results per page",true). ': ' . $Form->select('order_limit',$selectList,$selected,array('onchange'=>$onchange));            
        
        } else {
            
            foreach($items_per_page as $limit) 
            {
                if(defined('MVC_FRAMEWORK_ADMIN'))
                {
                    $url = $this->base_url . 'page' . _PARAM_CHAR . '1/limit' . _PARAM_CHAR . $limit;
                }
                else
                {
                    $url = cmsFramework::route($this->base_url . '/page' . _PARAM_CHAR . '1/limit' . _PARAM_CHAR . $limit . (cmsFramework::mosCmsSef() ? '' : '/'));
                }
                $selectList[] = array('value'=>$url,'text'=>$limit);
            }

            if(defined('MVC_FRAMEWORK_ADMIN'))
            {
                $selected = $this->base_url . 'page' . _PARAM_CHAR . '1/limit' . _PARAM_CHAR . $this->limit;
            }
            else
            {
                $selected = cmsFramework::route($this->base_url . '/page' . _PARAM_CHAR . '1/limit' . _PARAM_CHAR . $this->limit . (cmsFramework::mosCmsSef() ? '' : '/'));
            }

            return __t("Results per page",true). ': ' . $Form->select('order_limit',$selectList,$selected,array('onchange'=>"window.location=this.value"));
                        
        }
        
    }

    function display_pages()
    {
        return $this->return;
    }
    
    function display_pages_module() {
        return $this->return_module;
    }
}
