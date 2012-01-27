<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2008 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class CategoryModel extends MyModel  {
    
    var $useTable = '#__categories AS Category';
    var $primaryKey = 'Category.cat_id';
    var $realKey = 'id';
    var $fields = array(
        'Category.id AS `Category.cat_id`',
        'Category.section AS `Category.section_id`',
        'Category.title AS `Category.title`',
        'Category.alias AS `Category.slug`',        
        'Category.image AS `Category.image`',
        'Category.image_position AS `Category.image_position`',
        'Category.description AS `Category.description`',        
        'Category.access AS `Category.access`',
        'Category.published AS `Category.published`',
        'JreviewsCategory.dirid AS `Category.dir_id`',
        'JreviewsCategory.criteriaid AS `Category.criteria_id`',
        'JreviewsCategory.tmpl AS `Category.tmpl`',
        'JreviewsCategory.tmpl_suffix AS `Category.tmpl_suffix`',
        'Directory.desc AS `Directory.title`',
        'ListingType.config AS `ListingType.config`'
    );
            
    var $joins = array(
        'INNER JOIN #__jreviews_categories AS JreviewsCategory ON Category.id = JreviewsCategory.id AND JreviewsCategory.option = "com_content"',
        'LEFT JOIN #__jreviews_directories AS Directory ON JreviewsCategory.dirid = Directory.id',
        'LEFT JOIN #__jreviews_criteria AS ListingType ON JreviewsCategory.criteriaid = ListingType.id'
    );
    
    function __construct() {
        
        parent::__construct();
        
    }
    
    /**
     * Generate Section-Category tree array
     * Used in advanced search module
     */
    function categoryTree($gid, $settings) 
    {
        # Check for cached version        
        $cache_prefix = 'category_model_categorytree';
        $cache_key = func_get_args();
        if($cache = S2cacheRead($cache_prefix,$cache_key)){
            return $cache;
        }            
        
        # Get module parameters
        $module_id = Sanitize::getInt($settings,'module_id');
        $criteria_id = cleanIntegerCommaList(Sanitize::getString($settings['module'],'criteria_id'));
        $dir_id = cleanIntegerCommaList(Sanitize::getString($settings['module'],'dir_id'));
        $section_id = cleanIntegerCommaList(Sanitize::getString($settings['module'],'section_id'));
        $category_id = cleanIntegerCommaList(Sanitize::getString($settings['module'],'cat_id'));        
        $cat_order_alpha = Sanitize::getInt($settings['module'],'cat_order_alpha',1);
        $section_title = Sanitize::getString($settings['module'],'section_title',1);
        $section_bg = Sanitize::getString($settings['module'],'section_bg','#CCCCCC');
        $category_bg = Sanitize::getString($settings['module'],'category_bg','#FFFFFF');
        $section_color = Sanitize::getString($settings['module'],'section_color','#000000');
        $category_color = Sanitize::getString($settings['module'],'category_color','#000000');
        $option_length = Sanitize::getInt($settings['module'],'option_length','');
        $cat_auto = Sanitize::getInt($settings['module'],'cat_auto');
    
        # Selected categories and sections
        $selOption = explode('_',Sanitize::getString($settings,'cat'));
        $cat_auto and is_numeric($category_id) and $selOption = array($category_id);
        $selSection = Sanitize::getString($settings,'section');
        $cat_auto and is_numeric($section_id) and $selSection = $section_id;
                
        $order = array();
        $conditions = array();
        
        $order[] = $cat_order_alpha ? "Section.title ASC" : "Section.ordering ASC";        
        $order[] = $cat_order_alpha ? "Category.title ASC" : "Category.ordering ASC";
        
            
        if (!$section_id && $dir_id) {
            $conditions[] = "JreviewCategory.dirid IN ($dir_id)";
        } 
        
        if (!$section_id && $criteria_id) {
            $conditions[] = "JreviewCategory.criteriaid IN ($criteria_id)";
        }
        
        if ($section_id) 
        {
            $conditions[] = "Category.section IN ($section_id)";
        } 
        elseif ($category_id) 
        {
            $conditions[] = "Category.section IN (SELECT section FROM #__categories WHERE id IN ({$category_id}))";
        }
        
        $conditions[] = "Category.published = 1";
        $conditions[] = "Category.access <= '". $gid."'";
        $conditions[] = "JreviewCategory.option = 'com_content'";
    
        if($cat_auto && $section_id == '' && $category_id == '' && $criteria_id == '' && $dir_id == '')
        {
            array_pop($order);
            $query = "SELECT DISTINCT Category.section AS sectionid,"
            . ($option_length > 0 ? "\n CONCAT(SUBSTR(Section.title,1,".$option_length."),'...') AS section" : "\n Section.title AS section")
            . "\n FROM #__jreviews_categories AS JreviewCategory"
            . "\n LEFT JOIN #__categories AS Category ON Category.id = JreviewCategory.id"
            . "\n LEFT JOIN #__sections AS Section ON Category.section = Section.id"
            . "\n WHERE " . implode(" AND \n", $conditions)
            . "\n ORDER BY " . implode(",",$order)
            ;
        }
        else 
        {
            $query = "SELECT Category.id AS catid, Category.section AS sectionid,"
            . ($option_length > 0 ? "\n CONCAT(SUBSTR(Category.title,1,".$option_length."),'...') AS category," : "\n Category.title AS category,")
            . ($option_length > 0 ? "\n CONCAT(SUBSTR(Section.title,1,".$option_length."),'...') AS section" : "\n Section.title AS section")
            . "\n FROM #__jreviews_categories AS JreviewCategory"
            . "\n LEFT JOIN #__categories AS Category ON Category.id = JreviewCategory.id"
            . "\n LEFT JOIN #__sections AS Section ON Category.section = Section.id"
            . "\n WHERE " . implode(" AND \n", $conditions)
            . "\n ORDER BY " . implode(",",$order)
            ;
        }
            
        $this->_db->setQuery($query);
        $options = $this->_db->loadObjectList();
        
        $selSection > 0 and array_push($selOption,'s'.$selSection);    
        
        // Start building section/category select list
        $categoryList = array();
        $categoryList[] = '<select name="data[categories]" id="jr_advSearchCategories'.$module_id.'">';
        $categoryList[] = '<option value="">'.__t("Select Category",true) . '</option>';

        isset($options[0]) and $prevSection = $options[0]->sectionid;

        if($options) 
        {
            foreach($options AS $key=>$option) 
            {
                $selected = '';
                
                if( ($option->sectionid == $prevSection && $key > 0) || !$section_title)  
                { // Add categories

                    if(in_array($option->catid,$selOption)) {
                        $selected = 'selected="selected"';
                    }

                    isset($option->catid) and $categoryList[] = '<option value="'.$option->catid.'" style="color:'.$category_color.';background-color:'.$category_bg.'" '.$selected.'>&nbsp;&nbsp;&nbsp;' . stripslashes($option->category) . '</option>';

                } 
                else 
                { // Add section

                    in_array('s'.$option->sectionid,$selOption) and $selected = 'selected="selected"';
        
                    $categoryList[] = '<option value="s'.$option->sectionid.'" style="font-weight:bold;color:'.$section_color.';background-color:'.$section_bg.';" '.$selected.'>'. stripslashes($option->section) . '</option>';

                    $selected = '';

                    if(isset($option->catid)) 
                    {
                        in_array($option->catid,$selOption) and $selected = 'selected="selected"';
                        $categoryList[] = '<option value="'.$option->catid.'" style="color:'.$category_color.';background-color:'.$category_bg.'" '.$selected.'>&nbsp;&nbsp;&nbsp;' . stripslashes($option->category) . '</option>';
                    }

                }
                $prevSection = $option->sectionid;
            }
        }

        $categoryList[] = '</select>';
        
        $categorySelect = implode("\n",$categoryList);
        
        # Send to cache
        S2cacheWrite($cache_prefix,$cache_key,$categorySelect);
                
        return $categorySelect;
    }
    
    /**
    * Returns simple tree array
    * Uses: cat tree in paidlistings
    * @param mixed $options
    */
    function getTree($options = array())
    {
        $nodes = array();
        $json = Sanitize::getBool($options,'json');
        $conditions = Sanitize::getVar($options,'conditions') ? implode(' AND ', $options['conditions']) : false;
        $query = "
            SELECT 
               Section.id AS section_id, Section.title AS section_title, Category.id AS cat_id, Category.title AS cat_title
            FROM 
                #__categories AS Category
            RIGHT JOIN 
                #__sections AS Section ON Section.id = Category.section
            WHERE 
                1 = 1 
            " . ($conditions ? " AND (" . $conditions . ") " : '') . "
             ORDER BY
               Section.title, Category.title        
        ";
        $this->_db->setQuery($query);
        $rows = $this->_db->loadAssocList();
        // Build auxiliary arrays
        foreach($rows AS $row)
        {
            $sections[$row['section_id']] = array(
                "attributes"=>array("id"=>"s".$row['section_id']),
                "data"=>$row['section_title'],
                "state"=>"closed"
            );
            $cat = array(
                "attributes"=>array("id"=>$row['cat_id']),
                "data"=>$row['cat_title']
            );
            $categories[$row['section_id']][] = $cat;
        }
        foreach($sections AS $section_id=>$section)
        {
            $section['children'] = $categories[$section_id];
            $nodes[] = $section;
        }
        return $json ? json_encode($nodes) : $nodes;    
    }
    
    /**
     * Checks if core category is setup for jReviews
     */
    function isJreviewsCategory($cat_id) {
        
        # Check for cached version        
        $cache_prefix = 'category_model_isjreviewscategory';
        $cache_key = func_get_args();
        if($cache = S2cacheRead($cache_prefix,$cache_key)){
            return $cache;
        }                    

        $query = "SELECT JreviewCategory.id"
        . "\n FROM #__jreviews_categories AS JreviewCategory"
        . "\n WHERE JreviewCategory.`option` = 'com_content' AND JreviewCategory.id = " . (int) $cat_id;
        
        $this->_db->setQuery($query);
        
        $result = $this->_db->loadResult();
    
        # Send to cache
        S2cacheWrite($cache_prefix,$cache_key,$result);
                
        return $result;        
    }
    
    /**
     * Used in Administration in controllers:
     *         admin_listings_controller.php
     * Also used in Frontend listings_controller.php in create function.
     */
    function getList($section_id, $cat_ids = '') 
    {                    
        $query = "
            SELECT 
                Category.id AS value, Category.title AS text, Criteria.config AS config, Criteria.id 
            FROM 
                #__categories AS Category
            RIGHT JOIN 
                #__jreviews_categories AS JreviewsCategory ON JreviewsCategory.id = Category.id AND JreviewsCategory.`option` = 'com_content'
            LEFT JOIN
                #__jreviews_criteria AS Criteria On JreviewsCategory.criteriaid = Criteria.id
            WHERE 
                Category.section IN ({$section_id})
                ". ($cat_ids != '' ? "\n AND Category.id IN ($cat_ids)" : '') . "
            ORDER 
                BY Category.title
        ";

        $this->_db->setQuery($query);
        
        $categories = $this->_db->loadObjectlist();    

        // For admin use return all categories
        if(defined('MVC_FRAMEWORK_ADMIN')) return $categories;
        
        $Access = Configure::read('JreviewsSystem.Access');   
        foreach($categories AS $key=>$cat)
        {
            if($cat->config != '')
            {   
                $config = json_decode($cat->config,true);
                if(!$Access->canAddListing(Sanitize::getVar($config,'addnewaccess')))
                {
                    unset($categories[$key]);
                }
            }
        }
        return $categories;
    }
    
    /**
     * Used in Administration in controllers:
     *         categories_controller.php
     *         themes_controller.php
     */
    function getRows($sectionid, $limitstart=0, $limit, &$total) 
    {
        $where = $sectionid ? "\n AND sec.id = '$sectionid'" : '';
    
        // get the total number of records
        $query = "SELECT COUNT(*) FROM `#__jreviews_categories` AS jrcat"
        . "\n LEFT JOIN #__categories AS cat ON cat.id = jrcat.id"
        . "\n LEFT JOIN #__sections AS sec ON sec.id = cat.section"
        ."\n WHERE jrcat.option = 'com_content'"
        . $where
        ;
        $this->_db->setQuery( $query );
        
        $total = $this->_db->loadResult();
    
        $query = "SELECT jrcat.*, c.title as cat, d.desc as dir, sec.title as section, cr.title as criteria"
         ."\n FROM #__jreviews_categories jrcat"
         ."\n LEFT JOIN #__categories c on jrcat.id = c.id"
         ."\n LEFT JOIN #__sections sec on c.section = sec.id"
         ."\n LEFT JOIN #__jreviews_criteria cr on jrcat.criteriaid = cr.id"
         ."\n LEFT JOIN #__jreviews_directories d on jrcat.dirid = d.id"
         ."\n WHERE jrcat.option = 'com_content'"
         . $where
         ."\n ORDER BY section ASC, cat ASC"
         ."\n LIMIT $limitstart, $limit"
         ;
        
        $this->_db->setQuery($query);
        
        $rows = $this->_db ->loadObjectList();
        
        if(!$rows) {
            $rows = array();
        }
        return $rows;
    }
    
    /**
     * Used in Administration... need to clean up
     * Generates a list of new categories to set up. Used in controllers:
     *         categories_controller.php
     */    
    function getSelectList() 
    {
        # Find category ids already set up
        $query = "SELECT id FROM #__jreviews_categories"
        . "\n WHERE `option` = 'com_content'"
        ;
        $this->_db->setQuery($query);
    
        if($exclude = $this->_db->loadResultArray()) {
            $exclude = implode(',',$exclude);
        } else {
            $exclude = '';
        }
    
        $query = "SELECT Category.id AS value, CONCAT(Section.title,'>>',Category.title) AS text"
        . "\n FROM #__categories AS Category"
        . "\n INNER JOIN  #__sections AS Section ON Category.section = Section.id"
        . ($exclude != '' ? "\n WHERE Category.id NOT IN ($exclude)" : '')
        . "\n ORDER BY Section.title ASC, Category.title ASC"
        ;
        $this->_db->setQuery($query);
        
        $results = $this->_db->loadObjectList();

        return $results;    
    }
    
    function getTemplateSettings($cat_id) 
    {
        # Check for cached version        
        $cache_prefix = 'category_model_themesettings';
        $cache_key = func_get_args();
        if($cache = S2cacheRead($cache_prefix,$cache_key)){
            return $cache;
        }            
                
        $fields = array(
            'JreviewsSection.tmpl AS `Section.tmpl_list`',
            'JreviewsSection.tmpl_suffix AS    `Section.tmpl_suffix`',
            'JreviewsCategory.tmpl AS `Category.tmpl_list`',
            'JreviewsCategory.tmpl_suffix AS `Category.tmpl_suffix`'        
        );
        
        $query = "SELECT " . implode(',',$fields)
        . "\n FROM #__categories AS Category"
        . "\n LEFT JOIN #__jreviews_categories AS JreviewsCategory ON Category.id = JreviewsCategory.id"
        . "\n LEFT JOIN #__sections AS Section ON Category.section = Section.id"
        . "\n LEFT JOIN #__jreviews_sections AS JreviewsSection ON Section.id = JreviewsSection.sectionid"
        . "\n WHERE JreviewsCategory.option = 'com_content' AND Category.id = " . $cat_id
        ;
        
        $this->_db->setQuery($query);
        
        $result = end($this->__reformatArray($this->_db->loadAssocList()));
        
        # Send to cache
        S2cacheWrite($cache_prefix,$cache_key,$result);
        
        return $result;
    }
    
    function afterFind($results) 
    {        
        $Menu = registerClass::getInstance('MenuModel');
        
        $results = $Menu->addMenuCategory($results);
        
        foreach($results AS $key=>$result)
        {
            isset($result['ListingType']['config']) and $results[$key]['ListingType']['config'] = json_decode($result['ListingType']['config'],true);
            !is_array($results[$key]['ListingType']['config']) and $results[$key]['ListingType']['config'] = array();
        }

        return $results;
    }        
}
