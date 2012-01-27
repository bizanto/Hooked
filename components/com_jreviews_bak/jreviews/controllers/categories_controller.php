<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class CategoriesController extends MyController 
{
	
	var $uses = array('user','menu','criteria','directory','section','category','field','favorite');
	var $helpers = array('assets','cache','routes','libraries','html','text','jreviews','time','paginator','rating','thumbnail','custom_fields','community');
	var $components = array('config','access','feeds','everywhere');

	var $autoRender = false; //Output is returned
	var $autoLayout = true;
    var $layout = 'listings';
    var $click2search = false;
		
	function beforeFilter() 
    {
		# Call beforeFilter of MyController parent class
		parent::beforeFilter();
        $this->Listing->controller = $this->name;        
        $this->Listing->action = $this->action; 
                
		# Make configuration available in models
		$this->Listing->Config = &$this->Config;	
	}
    
    function afterFilter() 
    {               
        parent::afterFilter();
    }    
	
    // Need to return object by reference for PHP4
    function &getPluginModel() {
        return $this->Listing;
    }
        
	// Need to return object by reference for PHP4
	function &getObserverModel() {
		return $this->Listing;
	}	
	
	function alphaindex() { $this->listings(); }
		
	function section() { $this->listings(); }
		
	function category() { $this->listings(); }

	function favorites() { $this->listings(); }

	function featured() { 
        $this->Listing->conditions[] = 'Field.featured > 0';
        $this->listings(); 
    }
	
    function featuredrandom() { 
        $this->Listing->conditions[] = 'Field.featured > 0';
        $this->listings(); 
    }    

	function latest() { $this->listings(); }	

	function mylistings() { $this->listings(); }	

	function mostreviews() { 
        $this->Listing->conditions[] = 'Totals.user_comment_count > 0';
        $this->listings(); 
    }	
		
	function toprated() { 
        $this->Listing->conditions[] = 'Totals.user_rating > 0';
        $this->listings(); 
    }

	function topratededitor() { 
        $this->Listing->conditions[] = 'Totals.editor_rating > 0';
        $this->listings(); 
    }
	
	function popular() { $this->listings(); }	
    
    function random() { $this->listings(); }    
		
	function listings()
	{	         
        $this->name = 'categories';   // Required for assets helper
                                                                  
        if($this->_user->id === 0 && $this->action != 'search')  
        {                              
			$this->cacheAction = Configure::read('Cache.expires');
		}
           
		$this->autoRender = false;
         
		$action = Sanitize::paranoid($this->action);
		$dir_id = str_replace(array('_',' '),array(',',''),Sanitize::getString($this->params,'dir'));
		$section_id = Sanitize::getString($this->params,'section');
		$cat_id = Sanitize::getString($this->params,'cat');
        $criteria_id = Sanitize::getString($this->params,'criteria');
		$user_id = Sanitize::getInt($this->params,'user',$this->_user->id);
		$index = Sanitize::getString($this->params,'index');
		$sort = Sanitize::getString($this->params,'order',Sanitize::getString($this->Config,'list_order_field'));
		$sort == '' and $sort = Sanitize::getString($this->Config,'list_order_default');
        $menu_id = Sanitize::getInt($this->params,'menu',Sanitize::getString($this->params,'Itemid'));
        // Avoid running the listing query if in section page and listings disabled
        $query_listings = $this->action != 'section' || ($this->action == 'section' && $this->Config->list_show_sectionlist);
       
		$listings = array();
		$count = 0;
		
		switch($action) {
			case 'section':
				!$section_id and $adminmsg[] = "Admin: You need to specify a valid section id in the menu parameters.";
				break;
			case 'category':
				if (!$cat_id) 
                {
					$adminmsg[] = "Admin: You need to specify a valid category id in the menu parameters.";
				} 
                else 
                {
					// Find directory and section id
					$category = $this->Category->findRow(
						array(
							'conditions'=>array('Category.id = ' . $cat_id)
						)
					);

					if($category) 
                    {
                        $section_id = $this->params['section'] = $category['Category']['section_id'];
						$dir_id = $this->params['dir'] = $category['Category']['dir_id'];
                        
                        # Override global configuration
                        isset($category['ListingType']) and $this->Config->override($category['ListingType']['config']);
                        $sort = Sanitize::getString($this->params,'order',Sanitize::getString($this->Config,'list_order_field'));
                        $sort == '' and $sort = Sanitize::getString($this->Config,'list_order_default');
                    }          
				}
				break;
				
			case 'favorites':
				if(!$user_id && !$this->_user->id) {
					cmsFramework::noAccess();
					return;					
				}
				break;	
		}

		if (isset($adminmsg) && !empty($adminmsg)) {
			echo implode ("<br />", $adminmsg); 
			return;
		}		
							
		# Get section and category database information
		if (in_array($action,array('section','category')) ) 
		{
            $fields = array();
  		    # Get all categories for page
            if($this->Config->dir_cat_num_entries || $this->Config->dir_category_hide_empty)
            {
                $fields = array(' 
                            (SELECT 
                              count(*) 
                              FROM #__content AS Listing
                              INNER JOIN #__jreviews_categories AS JreviewsCategory ON JreviewsCategory.id = Listing.catid AND JreviewsCategory.`option` = "com_content"
                              WHERE 
                                    Listing.sectionid = ' . $section_id .'
                                    AND Listing.catid = Category.id         
                                    AND Listing.state = 1 
                                    AND Listing.access <= ' . $this->_user->gid . '
                                    AND ( Listing.publish_up = "'.NULL_DATE.'" OR Listing.publish_up <= "'._CURRENT_SERVER_TIME.'" ) 
                                    AND ( Listing.publish_down = "'.NULL_DATE.'" OR Listing.publish_down >= "'._CURRENT_SERVER_TIME.'" )
                            ) AS `Category.listing_count`                    
                        ');
            }
			$categories = $this->Category->findAll(
				array(
                    'fields'=>$fields, 
					'conditions'=>array('Category.section = ' . (int) $section_id,'Category.published = 1'),
					'order'=>($this->Config->dir_category_order ? 'Category.title ASC' : 'Category.ordering ASC')
				)
			);

            $category_tmp = current($categories);
            $dir_id = $category_tmp['Category']['dir_id'];   
            
            $section = $this->Section->findRow(
                array(
                    'fields'=>array((int) $dir_id . ' AS `Section.dir_id`'),
                    'conditions'=>array('Section.id = '. (int) $section_id)
                )
            );            
		}

		if( (isset($section) && !empty($section) && ($section['Section']['access'] > $this->_user->gid || !$section['Section']['published'] ))
			|| ($this->action == 'category' && isset($category) && !empty($category) && (!$category['Category']['published'] || $category['Category']['access'] > $this->_user->gid))
			) {
				cmsFramework::noAccess();
				return;
		}
							
		# Remove unnecessary fields from model query
		$this->Listing->modelUnbind('Listing.fulltext AS `Listing.description`');
				
		$conditions = array();
		$joins = array();
		
		# Get listings
	
		# Modify and perform database query based on lisPage type
		if ( ($action == 'section' && $this->Config->list_show_sectionlist) || $action != 'section' )
		{
			// Build where statement
			switch($action) {			
				case 'alphaindex':  
//					$index = isset($index{0}) ? $index{0} : '';
                    $conditions[] = ($index == '0' ? 'Listing.title REGEXP "^[0-9]"' : 'Listing.title LIKE '.$this->quote($index.'%'));
					break;
				case 'category':
					# Shows only links users can access
					$conditions[] = $category['Category']['access'] . ' <= ' . $this->_user->gid;
					break;
				case 'mylistings':
					if (!$user_id) {
						echo cmsFramework::noAccess();
						$this->autoRender = false;
						return;
					}
					$conditions[] = 'Listing.created_by = '.$user_id;	
					break;
				case 'section':
					break;
				case 'favorites':
					if (!$user_id) {
						echo cmsFramework::noAccess();
						$this->autoRender = false;
						return;
					}
					$joins[] = 	'INNER JOIN #__jreviews_favorites AS Favorite ON Listing.id = Favorite.content_id AND Favorite.user_id = ' . $user_id;
					break;
				default:
				break;
			}

            $section_id = cleanIntegerCommaList($section_id);
            $cat_id     = cleanIntegerCommaList($cat_id);
            $dir_id     = cleanIntegerCommaList($dir_id);
            $criteria_id = cleanIntegerCommaList($criteria_id);
            
            $cat_id != '' and $conditions[] = 'Listing.catid IN ('.$cat_id.')';
                                     
            $cat_id == '' and $section_id != '' and $conditions[] = 'Listing.sectionid IN ('.$section_id.')';

            $cat_id == '' and $dir_id != '' and $conditions[] = 'JreviewsCategory.dirid IN ('.$dir_id.')';
            
            $cat_id == '' and $criteria_id != '' and $conditions[] = 'JreviewsCategory.criteriaid IN ('.$criteria_id.')';
            
			if (($this->action == 'mylistings' && $user_id == $this->_user->id) || $this->Access->isPublisher())
            {
				$conditions[] = 'Listing.state >= 0';
			} 
            else 
            {
                $conditions[] = 'Listing.state = 1';
                $conditions[] = '( Listing.publish_up = "'.NULL_DATE.'" OR Listing.publish_up <= "'._CURRENT_SERVER_TIME.'" )';
                $conditions[] = '( Listing.publish_down = "'.NULL_DATE.'" OR Listing.publish_down >= "'._CURRENT_SERVER_TIME.'" )';
            }

			# Shows only links users can access
			$conditions[] = 'Listing.access <= ' . $this->_user->gid;

			$queryData = array(
				/*'fields' they are set in the model*/
				'joins'=>$joins,
				'conditions'=>$conditions,
				'limit'=>$this->limit,
				'offset'=>$this->offset
			);

			# Modify query for correct ordering. Change FIELDS, ORDER BY and HAVING BY directly in Listing Model variables
			$this->Listing->processSorting($action,$sort);		

			// This is used in Listings model to know whether this is a list page to remove the plugin tags
			$this->Listing->controller = 'categories';

            // Check if review scope checked in advancd search
            $scope = explode('_',Sanitize::getString($this->params,'scope'));

            if($this->action == 'search' && in_array('reviews',$scope)) 
            {
                $queryData['joins'][] = "LEFT JOIN #__jreviews_comments AS Review ON Listing.id = Review.pid AND Review.published = 1 AND Review.mode = 'com_content'";
                $queryData['group'][] = "Listing.id"; // Group By required due to one to many relationship between listings => reviews table
            } 
            
            $query_listings and $listings = $this->Listing->findAll($queryData);   
        
            # If only one result then redirect to it
            if($this->Config->search_one_result && count($listings)==1 && $this->action == 'search' && $this->page == 1)
            {   
                $listing = array_shift($listings);
                $url = cmsFramework::makeAbsUrl($listing['Listing']['url'],array('sef'=>true));
                cmsFramework::redirect($url);
            }            

            # Get the listing count
			if(in_array($action,array('section','category'))) 
            {
				unset($queryData['joins']);
				$this->Listing->joins = array(
                    "LEFT JOIN #__jreviews_listing_totals AS Totals ON Totals.listing_id = Listing.id AND Totals.extension = 'com_content'",
                    "LEFT JOIN #__jreviews_content AS Field ON Field.contentid = Listing.id",
					"INNER JOIN #__jreviews_categories AS JreviewsCategory ON Listing.catid = JreviewsCategory.id AND JreviewsCategory.`option` = 'com_content'",
					"LEFT JOIN #__jreviews_directories AS Directory ON JreviewsCategory.dirid = Directory.id"				
                );				
			} 
            elseif($action != 'favorites') 
            {
				unset($queryData['joins']);
				$this->Listing->joins = array(
                    "LEFT JOIN #__jreviews_listing_totals AS Totals ON Totals.listing_id = Listing.id AND Totals.extension = 'com_content'",
                    "LEFT JOIN #__jreviews_content AS Field ON Field.contentid = Listing.id",
					"INNER JOIN #__jreviews_categories AS JreviewsCategory ON Listing.catid = JreviewsCategory.id AND JreviewsCategory.`option` = 'com_content'",
					"LEFT JOIN #__jreviews_directories AS Directory ON JreviewsCategory.dirid = Directory.id"				
				);			

                if($this->action == 'search' && in_array('reviews',$scope)) 
                {
                    $queryData['joins'][] = "LEFT JOIN #__jreviews_comments AS Review ON Listing.id = Review.pid AND Review.published = 1 AND Review.mode = 'com_content'";
                }                
            }
            
            // Need to add user table join for author searches
            if(isset($this->params['author']))
            {
                $queryData['joins'][] = "LEFT JOIN #__users AS User ON User.id = Listing.created_by";
            }

			if($query_listings && !isset($this->Listing->count)) 
            {     
				$count = $this->Listing->findCount($queryData, ($this->action == 'search' && in_array('reviews',$scope)) ? 'DISTINCT Listing.id' : '*');
            } 
            else 
            {
				$count = $this->Listing->count;
			}

			if(Sanitize::getInt($this->data,'total_special') && Sanitize::getInt($this->data,'total_special') < $count) 
            {
				$count = Sanitize::getInt($this->data,'total_special');
			}
		}			
        
        # Get directory info for breadcrumb if dir id is a url parameter
        $directory = array();
        
        if(is_numeric($dir_id)) {
            $directory = $this->Directory->findRow(array(
                'fields'=>array(
                    'Directory.id AS `Directory.dir_id`',
                    'Directory.title AS `Directory.slug`',
                    'Directory.desc AS `Directory.title`'
                ),
                'conditions'=>array('Directory.id = ' . $dir_id)
            ));
        }
                        
		/******************************************************************
        * Process page title and description
        *******************************************************************/
		$name_choice = ($this->Config->name_choice == 'alias' ? 'username' : 'name');
		
		$page['show_title'] = 1;
		$page['show_description'] = 1;

		switch($action) 
        {
			case 'section':
                $menuParams = $this->Menu->getMenuParams($menu_id);
                $page = $section['Section'];
                $page['title'] = trim(Sanitize::getString($menuParams,'title')) != '' ? Sanitize::getString($menuParams,'title') : $section['Section']['title'];            
				$page['show_title'] = Sanitize::getInt($this->data,'dirtitle',1);
				$page['show_description'] = 1;
				break;
			
			case 'category':
                $menuParams = $this->Menu->getMenuParams($menu_id);
				$page = $category['Category'];
                $page['title'] = trim(Sanitize::getString($menuParams,'title')) != '' ? Sanitize::getString($menuParams,'title') : $category['Category']['title'];            
				$page['show_title'] = Sanitize::getInt($this->data,'dirtitle',1);;
				$page['show_description'] = 1;
                break;
		
			case 'alphaindex':
                $title = isset($directory['Directory']) ? Sanitize::getString($directory['Directory'],'title','') : '';   
                $page['title'] = $title != '' ? $title . ' - ' . ($index == '0' ? '0-9' : $index) : ($index == '0' ? '0-9' : $index);
				break;
			
			case 'mylistings':
				if($user_id > 0) 
				{
					$user_name = $this->User->findOne(
						array(
							'fields'=>array('User.' . $name_choice. ' AS `User.name`'),
							'conditions'=>array('User.id = ' . $user_id)
						)
					);
					
				} elseif($this->_user->id > 0)
				{
					$user_name = $this->_user->{$name_choice};
				}
				
				$page['title'] = sprintf(__t("Listings by %s",true),$user_name);			
				break;			
			case 'favorites':
				// Not running from CB Plugin so we change the page title
				if(!isset($this->Config->in_cb)) 
				{
					if($user_id > 0) 
					{
						$user_name = $this->User->findOne(
							array(
								'fields'=>array('User.' . $name_choice. ' AS `User.name`'),
								'conditions'=>array('User.id = ' . $user_id)
							)
						);
						
					} elseif($this->_user->id>0) {
						$user_name = $this->_user->{$name_choice};
					}
					$page['title'] = sprintf(__t("Favorites by %s",true), $user_name);
				}
				break;	
			case 'list':
			case 'search':
					$this->__seo_fields($page);
				break;
			case 'featured':
			case 'latest':
			case 'mostreviews':
			case 'popular':	
			case 'toprated':
			case 'topratededitor':
				$menuParams = $this->Menu->getMenuParams($menu_id);
				$page['show_title'] = Sanitize::getInt($menuParams,'dirtitle');
				$page['title'] = Sanitize::getString($menuParams,'title');     
				if(!$page['title'] && isset($this->Menu->menues[$menu_id])) {
					$page['title'] = $this->Menu->menues[$menu_id]->name;					
				}
				break;	
			default:
				$page['title'] = $this->Menu->getMenuName($menu_id);
				break;		
		}
        
        /******************************************************************
        * Generate SEO titles for re-ordered pages (most reviews, top user rated, etc.)
        *******************************************************************/
        # Category ids to be used for ordering list
        $cat_ids = array();
        
        if(in_array($action,array('search','category'))) 
        {
            $cat_ids = $cat_id;
        } 
        elseif(!empty($categories)) 
        {
            $cat_ids = implode(',',array_keys($categories));        
        }

        $field_order_array = $this->Field->getOrderList($cat_ids,'listing',$this->action,array('section','category','search','alphaindex'));        
        
        isset($page['title']) and $page['title_seo'] = $page['title'];
        
        if(($this->action !='search' || Sanitize::getVar($this->params,'tag')) && isset($this->params['order']) && $sort != '') 
        {
            App::import('helper','jreviews','jreviews');
            $ordering_options = JreviewsHelper::orderingOptions();
            $tmp_order = str_replace('rjr','jr',$sort);
            if(isset($ordering_options[$sort]))
            {
                $page['title_seo'] .= ' ' . sprintf(__t("ordered by %s",true), mb_strtolower($ordering_options[$sort],'UTF-8'));                
            } 
            elseif(isset($field_order_array[$tmp_order]))
            {
                if($order{0} == 'r')
                {
                    $page['title_seo'] .= ' ' . sprintf(__t("ordered by %s desc",true), mb_strtolower($field_order_array[$tmp_order]['text'],'UTF-8'));                
                } 
                else
                {
                    $page['title_seo'] .= ' ' . sprintf(__t("ordered by %s",true), mb_strtolower($field_order_array[$sort]['text'],'UTF-8'));                
                }
            }
        }        
        
        $this->params['order'] = $sort; // This is the param read in the views so we need to update it

        /******************************************************************
        * Set view (theme) vars 
        *******************************************************************/
        $this->set(
			array(
				'Config'=>$this->Config,
				'Access'=>$this->Access,
				'User'=>$this->_user,
				'subclass'=>'listing',
				'page'=>$page,
				'directory'=>$directory,
				'section'=>isset($section) ? $section : array(), // Section list
				'category'=>isset($category) ? $category : array(), // Category list
				'categories'=>isset($categories) ? $categories : array(),
				'listings'=>$listings,
				'pagination'=>array('total'=>$count))
		);
        
        $query_listings and $this->set('order_list',$field_order_array);

        /******************************************************************
        * RSS Feed: caches and displays feed when xml action param is present
        *******************************************************************/
        $this->Feeds->saveFeed(PATH_ROOT . DS .'cache' . DS . 'jreviewsfeed_'.md5($this->here.$this->_user->gid).'.xml','listings');
		
        echo $this->render('listings','listings_' . $this->tmpl_list);
	}
    
    function compare()
    {
		$listings = array();
        
        $listingType = Sanitize::getInt($this->params,'type');

        if($jrCompareCookie = Sanitize::getVar($_COOKIE,'jrCompare'.$listingType))
        {    
            $listings = json_decode($jrCompareCookie,true);
						
            !empty($listings) and $listing_ids = array_filter(array_unique(array_map('intval',str_replace('listing', '', array_keys($listings))))); 
			
            if(!empty($listing_ids))
            {
                $conditions[] = "Listing.id IN (".implode(",",$listing_ids).")";
                $conditions[] = 'Listing.state = 1';
                $conditions[] = '( Listing.publish_up = "'.NULL_DATE.'" OR Listing.publish_up <= "'._CURRENT_SERVER_TIME.'" )';
                $conditions[] = '( Listing.publish_down = "'.NULL_DATE.'" OR Listing.publish_down >= "'._CURRENT_SERVER_TIME.'" )';
                # Shows only links users can access
                $conditions[] = 'Listing.access <= ' . $this->_user->gid;
                $conditions[] = 'Listing.catid > 0';        
                
                $listings = $this->Listing->findAll(array('conditions'=>$conditions));

                $this->set(array(
                    'Config'=>$this->Config,
                    'Access'=>$this->Access,
                    'User'=>$this->_user,
                    'listings'=>$listings,
                ));               
                return $this->render('listings','listings_compare');
            }
        }
        
        return __t("No listings selected for comparison.",true);
    }

	function search() 
    {
		$urlSeparator = "_"; //Used for url parameters that pass something more than just a value		
		$simplesearch_custom_fields = 1 ; // Search custom fields in simple search
		$simplesearch_query_type = 'all'; // any|all
		$min_word_chars = 3; // Only words with min_word_chars or higher will be used in any|all query types
		$category_ids = '';		
		$criteria_ids = Sanitize::getString($this->params,'criteria');
        $dir_id = Sanitize::getString($this->params,'dir','');        
		$accepted_query_types = array ('any','all','exact');		
		$query_type = Sanitize::getString($this->params,'query');
		$keywords = urldecode(Sanitize::getString($this->params,'keywords'));
		$scope = Sanitize::getString($this->params,'scope');
		$author = urldecode(Sanitize::getString($this->params,'author'));

		if (!in_array($query_type,$accepted_query_types)) {
			$query_type = 'all'; // default value if value used is not recognized
		}
	
		// Build search where statement for standard fields
		$wheres = array();

		if ($keywords != '' &&  $scope=='') {
//			$scope = array("Listing.title","Listing.introtext","Listing.fulltext","Review.comments","Review.title");
			$scope = array("Listing.title","Listing.introtext","Listing.fulltext","Listing.metakey");
			
			$words = explode( ' ', $keywords);
			// Include custom fields	
			if ($simplesearch_custom_fields == 1) {
				$tbcols = $this->_db->getTableFields(array('#__jreviews_content'));
				$fields = array_keys($tbcols['#__jreviews_content']);
				$ignore = array("email","contentid","featured");
				// TODO: find out which fields have predefined selection values to get the searchable values instead of reference
			}
	
			$whereFields = array();	
	
			foreach ($scope as $contentfield) {
				
				$whereContentFields = array();

				foreach ($words as $word) {
					if(strlen($word)>=$min_word_chars){
						$word = urldecode(trim($word));
						$whereContentFields[] = "$contentfield LIKE " . $this->quoteLike($word);
					}
				}
	
                if(!empty($whereContentFields)){
                    $whereFields[] = "\n(" . implode( ($simplesearch_query_type == 'all' ? ') AND (' : ') OR ('), $whereContentFields ) . ')';
                }
			}
	
			if ($simplesearch_custom_fields == 1) {
				
				// add custom fields to where statement	
				foreach ($fields as $field) {
	
					$whereCustomFields = array();
	
					foreach ($words as $word) {
						$word = urldecode($word);						
						if(strlen($word)>=$min_word_chars){
							if (!in_array($field,$ignore)) {
								$whereCustomFields[] 	= "$field LIKE ".$this->quoteLike($word);
							}
						}
					}
	
					if (!empty($whereCustomFields) && !in_array($field,$ignore)) {
						$whereFields[] = "\n(" . implode( ($simplesearch_query_type == 'all' ? ') AND (' : ') OR ('), $whereCustomFields ) . ')';
					}
				}
	
			}
	
            if(!empty($whereFields))
            {
            $wheres[] = "\n(" . implode(  ') OR (', $whereFields ) . ')';
            }
	
	
		} else {
		// ADVANCED SEARCH
			// Process core content fields and reviews
			if ($keywords != '' && $scope != '') {
	
				$allowedContentFields = array("title","introtext","fulltext","reviews","metakey");
				
				$scope = explode($urlSeparator,$scope);
                $scope[] = 'metakey';
                
				switch ($query_type)
				{
					case 'exact':
						foreach ($scope as $contentfield) {
	
							if (in_array($contentfield,$allowedContentFields)) {
	
								$w 	= array();
	
								if ($contentfield == 'reviews') {
									$w[] = " Review.comments LIKE ".$this->quoteLike($keywords);
									$w[] = " Review.title LIKE ".$this->quoteLike($keywords);
								} else {
									$w[] = " Listing.$contentfield LIKE ".$this->quoteLike($keywords);
								}
								$whereContentOptions[] 	= "\n" . implode( ' OR ', $w);
							}
	
						}
	
						$wheres[] 	= implode( ' OR ', $whereContentOptions);
	
					break;
					case 'any':
					case 'all':
					default:
	
						$words = explode( ' ', $keywords );
						$whereFields = array();
	
						foreach ($scope as $contentfield) {
	
							if (in_array($contentfield,$allowedContentFields)) {
	
								$whereContentFields = array();
								$whereReviewComment = array();
								$whereReviewTitle = array();
	
								foreach ($words as $word) 
								{	
									if ($contentfield == 'reviews') {
										$whereReviewComment[] = "Review.comments LIKE ".$this->quoteLike($word);
										$whereReviewTitle[] = "Review.title LIKE ".$this->quoteLike($word);
									} else {
                                        $whereContentFields[] = "Listing.$contentfield LIKE ".$this->quoteLike($word);
									}	
								}
	
								if ($contentfield == 'reviews') 
								{
									
									$whereFields[] = "\n(" . implode( ($query_type == 'all' ? ') AND (' : ') OR ('), $whereReviewTitle ) . ")";
									$whereFields[] = "\n(" . implode( ($query_type == 'all' ? ') AND (' : ') OR ('), $whereReviewComment ) . ")";
								} else {
									$whereFields[] = "\n(" . implode( ($query_type == 'all' ? ') AND (' : ') OR ('), $whereContentFields ) . ")";
								}
	
							}
	
						}

						$wheres[] = '(' . implode(  ') OR (', $whereFields ) . ')';
	
					break;
	
				}
	
			} else {
	
				$scope = array();
	
			}
	
			// Process author field
			if ($author && $this->Config->search_item_author) {
				$wheres[] = "( User.name LIKE ".$this->quoteLike($author)." OR "
				."\n User.username LIKE ".$this->quoteLike($author)." OR "
				."\n Listing.created_by_alias LIKE ".$this->quoteLike($author)
				." )"
				;                
			}
	
			// Process custom fields
			$query_string = Sanitize::getString($this->passedArgs,'url');

			if($tag = Sanitize::getVar($this->params,'tag')) 
            {
                $this->click2search = true;
                
				// Field value underscore fix: remove extra menu parameter not removed in routes regex 
				$tag['value'] = preg_replace(array('/_m[0-9]+$/','/_m$/','/_$/'),'',$tag['value']);
				
				// Below is included fix for dash to colon change in J1.5
				$query_string = 'jr_'.$tag['field']. _PARAM_CHAR .str_replace(':','-',$tag['value']) . '/'.$query_string;
			}

			$url_array = explode ("/", $query_string);

			// Include external parameters for custom fields - this is required for components such as sh404sef
			foreach($this->params AS $varName=>$varValue) {
				if(substr($varName,0,3)=="jr_" && false === array_search($varName . _PARAM_CHAR . $varValue,$url_array)) {
					$url_array[] = $varName . _PARAM_CHAR . $varValue;
				}
			}

			// Get names of custom fields to eliminate queries on non-existent fields
			$customFieldsMeta = $this->_db->getTableFields(array('#__jreviews_content'));
	
			$customFields = is_array($customFieldsMeta['#__jreviews_content']) ? array_keys($customFieldsMeta['#__jreviews_content']) : array();

			foreach ($url_array as $url_param) 
            {
				$param = explode (":",$url_param);
				$key = $param[0];
				// Cleans url search text of any malicious code
				$value = Sanitize::getVar($param,'1',null); // '1' is the key where the value is stored in $param

				if (substr($key,0,3)=="jr_" && in_array($key,$customFields) && !is_null($value) && $value != '') { // Check if the field exists
	
					$searchValues = explode($urlSeparator, $value);
	
					if (substr_count($value,$urlSeparator)) {

						// Check if it is a numeric or date value
						$allowedOperators = array("equal"=>'=',"higher"=>'>=',"lower"=>'<=', "between"=>'between');
						$operator = $searchValues[0];

						$isDate = false;
						if ($searchValues[1] == "date") {
							$isDate = true;
						}

						if (in_array($operator,array_keys($allowedOperators)) && (is_numeric($searchValues[1]) || $isDate)) {

							if ($operator == "between") {

    							if ($isDate) {
                                    @$searchValues[1] = low($searchValues[2]) == 'today' ? _CURRENT_SERVER_TIME : $searchValues[2];
                                    @$searchValues[2] = low($searchValues[3]) == 'today' ? _CURRENT_SERVER_TIME : $searchValues[3];
								}

                                $low = is_numeric($searchValues[1]) ? $searchValues[1] : $this->quote($searchValues[1]);
                                $high = is_numeric($searchValues[2]) ? $searchValues[2] : $this->quote($searchValues[2]);
								$wheres[] = "\n".$key." BETWEEN " . $low . ' AND ' . $high;
	
							} else {
	
								if ($searchValues[1] == "date") {
									$searchValues[1] = low($searchValues[2]) == 'today' ? _CURRENT_SERVER_TIME : $searchValues[2];
								}
                                $value = is_numeric($searchValues[1]) ? $searchValues[1] : $this->quote($searchValues[1]);
								$wheres[] = "\n".$key.$allowedOperators[$operator].$value;
							}
	
						// This is a field with multiple options
						} else {
                                             
							// Find out the field type to determine whether it's an AND or OR search
							$query = "SELECT type FROM #__jreviews_fields WHERE name= ".$this->quote($key);
							$this->_db->setQuery($query);
							$fieldType = $this->_db->loadResult();
	
							$OR_fields = array("select","radiobuttons");
							$AND_fields = array("selectmultiple","checkboxes");
	
							$whereFields = array();
	
							if(isset($tag) && $key = 'jr_'.$tag['field']) {
								// Field value underscore fix
								$whereFields[] = "\n $key LIKE '%".$this->_db->getEscaped(urldecode($value))."%'";
							} elseif(!empty($searchValues)) {
								foreach ($searchValues as $searchValue) {
									$searchValue = urldecode($searchValue);
									$whereFields[] = "\n $key LIKE '%".$this->_db->getEscaped($searchValue)."%'";
								}
							}
	
							if (in_array($fieldType,$OR_fields)) { // Single option field
								$wheres[] = '(' . implode( ') OR (', $whereFields ) . ')';
							} elseif (in_array($fieldType,$AND_fields)) { // Multiple option field
								$wheres[] = '(' . implode( ') AND (', $whereFields ) . ')';
							}
						}
					} else {

						$value = urldecode($value);

						// Find out the field type
						$query = "SELECT type FROM #__jreviews_fields WHERE name=".$this->quote($key);
						$this->_db->setQuery($query);
						$fieldType = $this->_db->loadResult();
	
						$whereFields = array();

						if (in_array($fieldType,array('select','radiobuttons','selectmultiple','checkboxes'))) {
							// Does an exact search for multiple option fields because the asteriscs are included before and after the field option value
							$whereFields[] = "\n $key LIKE '%*".$this->_db->getEscaped($value)."*%'";
						
						} elseif(in_array($fieldType,array('integer','decimal')))  {
							// Does an exact search for numeric fields
							$words = explode(' ',trim($value));
							
							foreach ($words as $word) {
								$whereFields[] = "$key = ".$this->_db->Quote($word);
							}
						} else {
							$whereFields[] = "\n $key LIKE " . $this->quoteLike($value);
							
						}
	
						$wheres[] = "\n(" . implode(  ') AND (', $whereFields ) . ")";
					}
	
				}
	
			} // endforeach
		}

		$where = !empty($wheres) ? "\n (" . implode( ") AND (", $wheres ) . ")" : '';

		// Determine which categories to include in the queries
		if (Sanitize::getString($this->params,'cat')) 
		{		
			$section_ids = array();	
			$category_ids = explode($urlSeparator,$this->params['cat']);
            
			// Remove empty or nonpositive values from array
			foreach ($category_ids as $index => $value) 
            {
                // Check if it's a section
                if($value{0} == 's' && is_numeric(substr($value,1)) && substr($value,1) > 0) {
                    $section_ids[] = substr($value,1);
                    unset($category_ids[$index]); // It's a section, not a category
                }    
                elseif (empty($value) || $value < 1 || !is_numeric($value)) 
                {
                    unset($category_ids[$index]);                                    
                }
			}

			$section_ids = implode(',',$section_ids);
			$category_ids = implode (',',$category_ids);
			$category_ids != '' and $this->params['cat'] = $category_ids;	
			$section_ids != '' and $this->params['section'] = $section_ids;
		} 
        elseif (isset($criteria_ids) && trim($criteria_ids) != '') 
        {                 
			$criteria_ids = str_replace($urlSeparator,',',$criteria_ids);
            $criteria_ids != '' and $this->params['criteria'] = $criteria_ids;
		} 
        elseif (isset($dir_id) && trim($dir_id) != '') 
        {            
            $dir_id = str_replace($urlSeparator,',',$dir_id);
            $dir_id != '' and $this->params['dir'] = $dir_id;
        }

		# Add search conditions to Listing model
		$where != '' and $this->Listing->conditions[] = $where;

		return $this->listings();
	}
				
	function __seo_fields(&$page) 
	{							
		if($tag = Sanitize::getVar($this->params,'tag'))
		    {
			    $field = 'jr_'.$tag['field'];
    //			$value = $tag['value'];
			    // Field value underscore fix: remove extra menu parameter not removed in routes regex 
			    $value = preg_replace(array('/_m[0-9]+$/','/_m$/','/_$/','/:/'),array('','','','-'),$tag['value']);	

			    $query = "
                    SELECT 
                        fieldid,type,metatitle,metakey,metadesc 
                    FROM 
                        #__jreviews_fields 
                    WHERE 
                        name = ".$this->quote($field)." AND `location` = 'content'
                ";
			    $this->_db->setQuery($query);
			    $meta = $this->_db->loadObjectList();

			    if($meta) 
			    {
				    $meta = $meta[0];

				    $multichoice = array('select','selectmultiple','checkboxes','radiobuttons');
                    
				    if (in_array($meta->type,$multichoice)) 
                        {
					        $query = "
                                SELECT 
                                    optionid, text 
                                FROM 
                                    #__jreviews_fieldoptions 
                                WHERE 
                                    fieldid = '{$meta->fieldid}' AND value = ".$this->quote(stripslashes($value))
                                ;
					        $this->_db->setQuery($query);
					        $fieldValue = array_shift($this->_db->loadAssocList());
                            $fieldValue = $fieldValue['text'];
				        }
                     else 
                        {
					        $fieldValue = urldecode($value);
				        }
				    
				    $page['title'] = $meta->metatitle == '' ? $fieldValue : str_replace("{FIELDVALUE}",$fieldValue,$meta->metatitle);
				    $page['keywords'] = str_replace("{FIELDVALUE}",$fieldValue,$meta->metakey);
				    $page['description'] = str_replace("{FIELDVALUE}",$fieldValue,$meta->metadesc);
				    $page['show_title'] = $this->Config->seo_title;
				    $page['show_description'] = $this->Config->seo_description;							
			    }	
		    }		
		
	} // __seo_fields				
}