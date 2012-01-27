<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );
App::import('Helper','routes','jreviews');

class EverywhereComContentModel extends MyModel  {
		
	var $UI_name = 'Content';
	
	var $name = 'Listing';
	
	var $useTable = '#__content AS Listing';
	
	var $primaryKey = 'Listing.listing_id';
	
	var $realKey = 'id';
	
	/**
	 * Used for listing module - latest listings ordering
	 */
	var $dateKey = 'created';
   	
	var $extension = 'com_content';
	
	var $fields = array(
		'Listing.id AS `Listing.listing_id`',
		'Listing.title AS `Listing.title`',
		'Listing.introtext AS `Listing.summary`',
		'Listing.fulltext AS `Listing.description`',
		'Listing.images AS `Listing.images`',
		'Listing.hits AS `Listing.hits`',
		'Listing.sectionid AS `Listing.section_id`',
		'Listing.catid AS `Listing.cat_id`',
		'Listing.created_by AS `Listing.user_id`',
		'Listing.created_by_alias AS `Listing.author_alias`',
		'Listing.created AS `Listing.created`',
        'Listing.modified AS `Listing.modified`',       
		'Listing.access AS `Listing.access`',
		'Listing.state AS `Listing.state`',
		'Listing.publish_up AS `Listing.publish_up`',		
		'Listing.publish_down AS `Listing.publish_down`',
		'Listing.metakey AS `Listing.metakey`',
		'Listing.metadesc AS `Listing.metadesc`',
		'\'com_content\' AS `Listing.extension`',
		'Section.id AS `Section.section_id`',
		'Section.title AS `Section.title`',
		'Category.id AS `Category.cat_id`',
		'Category.title AS `Category.title`',
		'Category.image AS `Listing.category_image`',
		'Directory.id AS `Directory.dir_id`',
		'Directory.desc AS `Directory.title`',
		'Directory.title AS `Directory.slug`',
		'criteria'=>'Criteria.id AS `Criteria.criteria_id`',
		'Criteria.title AS `Criteria.title`',
		'Criteria.criteria AS `Criteria.criteria`',
		'Criteria.tooltips AS `Criteria.tooltips`',
		'Criteria.weights AS `Criteria.weights`',
        'Criteria.required AS `Criteria.required`',       
		'Criteria.state AS `Criteria.state`',
        'Criteria.config AS `ListingType.config`',
		'`Field`.featured AS `Listing.featured`',
        'Frontpage.content_id AS `Listing.frontpage`',        
		'User.id AS `User.user_id`',
		'User.name AS `User.name`',
		'User.username AS `User.username`',
		'email'=>'User.email AS `User.email`',
/* BLOCK BELOW IS FOR SUMMARY REVIEW INFO IN JREVIEWS LISTINGS PAGE */
        // User reviews
        'user_rating'=>'Totals.user_rating AS `Review.user_rating`',
        'Totals.user_rating_count AS `Review.user_rating_count`',
        'Totals.user_criteria_rating AS `Review.user_criteria_rating`',
        'Totals.user_criteria_rating_count AS `Review.user_criteria_rating_count`',
        'Totals.user_comment_count AS `Review.review_count`',
		// Editor reviews
        'editor_rating'=>'Totals.editor_rating AS `Review.editor_rating`', 
        'Totals.editor_rating_count AS `Review.editor_rating_count`',
        'Totals.editor_criteria_rating AS `Review.editor_criteria_rating`',
        'Totals.editor_criteria_rating_count AS `Review.editor_criteria_rating_count`',
        'Totals.editor_comment_count AS `Review.editor_review_count`',
        'Claim.approved AS `Claim.approved`'        
/* END BLOCK */ 
	);	
	
	var $joins = array(
        "INNER JOIN #__jreviews_categories AS JreviewsCategory ON Listing.catid = JreviewsCategory.id AND JreviewsCategory.`option` = 'com_content'",
        "LEFT JOIN #__categories AS Category ON JreviewsCategory.id = Category.id",
        "LEFT JOIN #__sections AS Section ON Category.section = Section.id",
		'Total'=>"LEFT JOIN #__jreviews_listing_totals AS Totals ON Totals.listing_id = Listing.id AND Totals.extension = 'com_content'",
		"LEFT JOIN #__jreviews_content AS Field ON Field.contentid = Listing.id",
		"LEFT JOIN #__jreviews_directories AS Directory ON JreviewsCategory.dirid = Directory.id",
		"LEFT JOIN #__jreviews_criteria AS Criteria ON JreviewsCategory.criteriaid = Criteria.id",
		'User'=>"LEFT JOIN #__users AS User ON User.id = Listing.created_by",
        'Claim'=>"LEFT JOIN #__jreviews_claims AS Claim ON Claim.listing_id = Listing.id AND Claim.approved = 1",        
        'Frontpage'=>"LEFT JOIN #__content_frontpage AS Frontpage ON Frontpage.content_id = Listing.id"        
	);        
	
	/**
	 * Used to complete the listing information for reviews based on the Review.pid. The list of fields for the listing is not as
	 * extensive as the one above used for the full listing view
	 */
	var $joinsReviews = array(
		'LEFT JOIN #__content AS Listing ON Review.pid = Listing.id',
		"INNER JOIN #__jreviews_categories AS JreviewsCategory ON Listing.catid = JreviewsCategory.id AND JreviewsCategory.`option` = 'com_content'",
		"LEFT JOIN #__categories AS Category ON Category.id = JreviewsCategory.id",
		'LEFT JOIN #__jreviews_criteria AS Criteria ON JreviewsCategory.criteriaid = Criteria.id'
	);
	
	var $conditions = array();

	var $limit;
	var $offset;
    var $order = array();

//	var $group = array('Listing.id');
	
	function __construct() 
    {
		parent::__construct();
		
		$this->tag = __t("Listing",true);  // Used in MyReviews page to differentiate from other component reviews
		
		// Uncomment line below to show tag in My Reviews page
//		$this->fields[] = "'{$this->tag }' AS `Listing.tag`";

		if(getCmsVersion() == CMS_JOOMLA15) {
			// Add listing, category aliases to fields
            $this->fields[] = 'Listing.alias AS `Listing.slug`';
            $this->fields[] = 'Category.alias AS `Category.slug`';
            $this->fields[] = 'Section.alias AS `Section.slug`';
        } else {
			$this->fields[] = 'Listing.title_alias AS `Listing.slug`';
			$this->fields[] = 'Category.name AS `Category.slug`';
			$this->fields[] = 'Section.name AS `Section.slug`';
		}
        
        // PaidListings integration - when completing review info needs to be triggered here
        if(class_exists('PaidListingsComponent'))
        {
            PaidListingsComponent::applyBeforeFindListingChanges($this);
        } 
        
        $this->Routes =  RegisterClass::getInstance('RoutesHelper');       			
	}		
    
	function exists() {
		return (bool) file_exists(PATH_ROOT . 'components' . _DS . 'com_content' . _DS . 'content.php');
	}                         
		
	function listingUrl($listing) 
    {
		return $this->Routes->content('',$listing,array('return_url'=>true,'sef'=>false));
	} 
	
	function getTemplateSettings($listing_id) 
    {
		# Check for cached version
		$cache_prefix = 'everywhere_content_themesettings';
		$cache_key = func_get_args();
		if($cache = S2cacheRead($cache_prefix,$cache_key)){
			return $cache;
		}		
				
		$fields = array(
			'JreviewsSection.tmpl AS `Section.tmpl_list`',
			'JreviewsSection.tmpl_suffix AS	`Section.tmpl_suffix`',
			'JreviewsCategory.tmpl AS `Category.tmpl_list`',
			'JreviewsCategory.tmpl_suffix AS `Category.tmpl_suffix`'		
		);
		
		$query = "
            SELECT 
                " . implode(',',$fields) . "
		    FROM 
                #__content AS Listing
		    INNER JOIN 
                #__jreviews_categories AS JreviewsCategory ON Listing.catid = JreviewsCategory.id AND JreviewsCategory.option = 'com_content'
		    LEFT JOIN 
                #__categories AS Category ON JreviewsCategory.id = Category.id 
		    LEFT JOIN 
                #__sections AS Section ON Category.section = Section.id
		    LEFT JOIN 
                #__jreviews_sections AS JreviewsSection ON Section.id = JreviewsSection.sectionid
		    WHERE 
                Listing.id = " . $listing_id
		;
		
		$this->_db->setQuery($query);
		
		$result = end($this->__reformatArray($this->_db->loadAssocList()));
		
		# Send to cache
		S2cacheWrite($cache_prefix,$cache_key,$result);		
		
		return $result;
	}		

    // Used to check whether reviews can be posted by listing owners
    function getListingOwner($result_id) 
    {
        $query = "
            SELECT 
                Listing.created_by AS user_id, User.name, User.email 
            FROM 
                #__content AS Listing 
            LEFT JOIN
                #__users AS User ON Listing.created_by = User.id                
            WHERE 
                Listing.id = " . (int) ($result_id);
        $this->_db->setQuery($query);
        appLogMessage($this->_db->getErrorMsg(),'owner_listing');
        return current($this->_db->loadAssocList());        
    }
	
    function afterFind($results) 
    {  
        if (empty($results)) 
        {
            return $results;
        }
        
        App::import('Model',array('menu','favorite','field','criteria'),'jreviews');
        
        # Add Menu ID info for each row (Itemid)
        $Menu = RegisterClass::getInstance('MenuModel');
        $results = $Menu->addMenuListing($results);
                                    
        # Reformat image and criteria info
        foreach ($results AS $key=>$listing) 
        {         
            // Check for guest user submissions
            if(isset($listing['User']) 
                && ($listing['User']['user_id'] == 0 
                || ($listing['User']['user_id'] == 62 && $listing['Listing']['author_alias']!=''))) 
            {
                $results[$key]['User']['name'] = $listing['Listing']['author_alias'];
                $results[$key]['User']['username'] = $listing['Listing']['author_alias'];
                $results[$key]['User']['user_id'] = 0;
            }            
        
            // Remove plugin tags
            if(isset($results[$key]['Listing']['summary']) && Sanitize::getString($this,'controller')=='categories') { // Not in edit mode
                $regex = "#{[a-z0-9]*(.*?)}(.*?){/[a-z0-9]*}#s";
                $results[$key]['Listing']['summary'] = preg_replace( $regex, '', $results[$key]['Listing']['summary'] );            
            }
            
             // Escape quotes in meta tags
            isset($listing['Listing']['metakey']) and $listing['Listing']['metakey'] = htmlspecialchars($listing['Listing']['metakey'],ENT_QUOTES,'UTF-8');
            isset($listing['Listing']['metadesc']) and $listing['Listing']['metadesc'] = htmlspecialchars($listing['Listing']['metadesc'],ENT_QUOTES,'UTF-8');

            # Config overrides
            isset($listing['ListingType']) and $results[$key]['ListingType']['config'] = json_decode($listing['ListingType']['config'],true);
             
            $results[$key][$this->name]['url'] = $this->listingUrl($listing);                    

            if(isset($listing['Listing']['images']))
            {             
                if (is_array($listing['Listing']['images'])) { // Mambo 4.5 compat
                    $listing['Listing']['images'] = implode( "\n",$listing['Listing']['images']);
                }
        
                $images = explode("\n",$listing['Listing']['images']);
                unset($results[$key]['Listing']['images']);
                $results[$key]['Listing']['images'] = array();
                
                if(!empty($images[0]))
                {
                    foreach($images as $image) 
                    {                        
                        $image_parts = explode("|", $image);
                        if($image_parts[0]!='') {
                            $results[$key]['Listing']['images'][] = array(
                                'path'=>trim($image_parts[0]),
                                'caption'=>isset($image_parts[4]) ? $image_parts[4] : ''
                            );
                        }
                    }
                }
            }
      
            if(isset($listing['Criteria']['criteria']) && $listing['Criteria']['criteria'] != '') 
            {
                $results[$key]['Criteria']['criteria'] = explode("\n",$listing['Criteria']['criteria']);
                
                $results[$key]['Criteria']['required'] = explode("\n",$listing['Criteria']['required']);
                // every criteria must have 'Required' set (0 or 1). if not, either it's data error or data from older version of jr, so default to all 'Required'
                if ( count($results[$key]['Criteria']['required']) != count($results[$key]['Criteria']['criteria']) )
                {
                    $results[$key]['Criteria']['required'] = array_fill(0, count($results[$key]['Criteria']['criteria']), 1);
                }
            }

            if(isset($listing['Criteria']['tooltips']) && $listing['Criteria']['tooltips'] != '') {
                $results[$key]['Criteria']['tooltips'] = explode("\n",$listing['Criteria']['tooltips']);
            }

            if(isset($listing['Criteria']['weights']) && $listing['Criteria']['weights'] != '') {
                $results[$key]['Criteria']['weights'] = explode("\n",$listing['Criteria']['weights']);
            }
            
            // Add detailed rating info
            if(!Configure::read('EverywhereReviewModel') && isset($listing['Review']))
            {
                $results[$key]['Rating'] = array(
                        'average_rating' => $listing['Review']['user_rating'] > 0 ? $listing['Review']['user_rating'] : false,
                        'ratings' => explode(',', $listing['Review']['user_criteria_rating']),
                        'criteria_rating_count' => explode(',', $listing['Review']['user_criteria_rating_count'])
                    );
            }
        }        
        
        if(!defined('MVC_FRAMEWORK_ADMIN') || MVC_FRAMEWORK_ADMIN == 0) {
            # Add Community info to results array
            if(isset($listing['User']) && !defined('MVC_FRAMEWORK_ADMIN') && class_exists('CommunityModel')) {
                $Community = registerClass::getInstance('CommunityModel');
                $results = $Community->addProfileInfo($results, 'User', 'user_id');
            }

            # Add Favorite info to results array
            $Favorite = RegisterClass::getInstance('FavoriteModel');
            $Favorite->Config = &$this->Config;        
            $results = $Favorite->addFavorite($results);
        }
        
        # Add custom field info to results array
        $CustomFields = RegisterClass::getInstance('FieldModel');        
        $results = $CustomFields->addFields($results,'listing');
       
        /* Call to model initiated via review module controller
         * This was added to process paid listing info (i.e. images) for reviews 
         * because the paid listing plugin cannot be triggered in the reviews module controller
         */
        if(!defined('MVC_FRAMEWORK_ADMIN') && Configure::read('EverywhereReviewModel') && class_exists('PaidListingsComponent'))
        {                 
            Configure::write('EverywhereReviewModel',false);
            $PaidListings = RegisterClass::getInstance('PaidListingsComponent');
            $PaidListings->processPaidData($results);
        }
        return $results; 
    }
	/**
	 * This can be used to add post save actions, like synching with another table
	 *
	 * @param array $model
	 */
    function afterSave(&$model) 
    {
        if(isset($model->name))
        {
            switch($model->name)  
            {
                case 'Review':break;
                case 'Listing':break;            
            }
        }
    }
	
	function processSorting($controller_action, $order) 
    {
		# Order by custom field
        if (false !== (strpos($order,'jr_'))) 
        {
            $this->__orderByField($order);				
        } 
	    else 
        {                 
            # If special task, then set the correct ordering processed in urlToSqlOrderBy
            switch($controller_action) 
            {
                case 'section':
                case 'category':    
                    if ($order == '') {
                        $order = $this->Config->list_order_default;        
                    }
                    break;
                case 'toprated':
                    $order = 'rating';
                    break;
                case 'topratededitor':
                    $order = 'editor_rating';
                    break;
                case 'mostreviews':
                    $order = 'reviews';   
                    break;
                case 'latest':
                    $order = 'rdate';
                    break;
                case 'popular':
                    $order = 'rhits';
                    break;
                case 'featured':
                    $order = 'featured';
                    break;
                case 'search':                
                case 'alphaindex':
                case 'mylistings':
                    // Nothing
                    break;    
                case 'random':
                case 'featuredrandom':                
                    $order = 'random';
                    break;
                default: 
                    $order = $controller_action;
                break;
            }
            $this->order[] = $this->__urlToSqlOrderBy($order);                        
        }
	}	
	
    function __orderByField($field)
    {
        $direction = 'ASC';

        if (false !== (strpos($field,'rjr_'))) {
            $field = substr($field,1);
            $direction = 'DESC';
        }

        $CustomFields = RegisterClass::getInstance('FieldModel');

        $queryData = array(
            'fields'=>array('Field.fieldid AS `Field.field_id`'),
            'conditions'=>array(
                'Field.name = "'.$field.'"',
//                    'Field.listsort = 1'
                ) 
        );

        $field_id = $CustomFields->findOne($queryData);
        
        if ($field_id) 
        {
            $this->fields[] = 'Field.' . $field . ' AS `Field.' . $field . '`';
            $this->fields[] = 'IF (Field.' .$field . ' IS NULL, IF(Field.' .$field . ' = "",1,0), 1) AS `Field.notnull`';
            $this->order[] = '`Field.notnull` DESC';
//            $this->conditions[] = 'Field.' . $field . ' IS NOT NULL';
//            $this->conditions[] = 'Field.' . $field . '<> ""';
            $this->order[] = 'Field.' . $field . ' ' .$direction;        
            $this->order[] = 'Listing.created DESC';                        
        }        
    }
    
	function __urlToSqlOrderBy($sort) 
	{
		$order = '';
		switch ( $sort ) 
        {
            case 'featured':
                $order = '`Listing.featured` DESC, Listing.created DESC';
              break;
            case 'editor_rating':  
            case 'author_rating':
                $order = 'Totals.editor_rating DESC, Totals.editor_rating_count DESC';
//                $this->conditions[] = 'Totals.editor_rating > 0';
              break;
            case 'reditor_rating':
                $order = 'Totals.editor_rating ASC, Totals.editor_rating_count DESC';
//                $this->conditions[] = 'Totals.editor_rating > 0';
              break;
            case 'rating':
                $order = 'Totals.user_rating DESC, Totals.user_rating_count DESC';
//                $this->conditions[] = 'Totals.user_rating > 0';
              break;
            case 'rrating':
                $order = 'Totals.user_rating ASC, Totals.user_rating_count DESC';
//                $this->conditions[] = 'Totals.user_rating > 0';
              break;
            case 'reviews':
                $order = 'Totals.user_comment_count DESC'; 
//                $this->conditions[] = 'Totals.user_comment_count > 0';
                break;
			case 'date':
				$order = 'Listing.created';
				break;
			case 'rdate':
				$order = 'Listing.created DESC';
				break;
//			case 'alias':
//				$order = 'Listing.alias DESC';
//				break;
			case 'alpha':
				$order = 'Listing.title';
				break;
			case 'ralpha':
				$order = 'Listing.title DESC';
				break;
			case 'hits':
				$order = 'Listing.hits ASC';
				break;
			case 'rhits':
				$order = 'Listing.hits DESC';
				break;
			case 'order':
				$order = 'Listing.ordering';
				break;
			case 'author':
				if ($this->Config->name_choice == 'realname') {
					$order = 'User.name, Listing.created';
				} else {
					$order = 'User.username, Listing.created';
				}
				break;
			case 'rauthor':
				if ($this->Config->name_choice == 'realname') {
					$order = 'User.name DESC, Listing.created';
				} else {
					$order = 'User.username DESC, Listing.created';
				}
				break;
		    case 'random':
		        $order = 'RAND()';
		    break;    
			default:
				$order = 'Listing.title';
		 		break;
		}
		return $order;
	}	
	    
    function delete(&$data) 
    {
        $listing_id = $this->data['listing_id'] = (int) $data['Listing']['id'];
        
        $this->plgBeforeDelete('Listing.id',$listing_id); // Only works for single listing deletion
                    
        $query = "DELETE FROM #__content WHERE id = '$listing_id'";
        $this->_db->setQuery( $query );
        $this->_db->query();
        
        $query = "DELETE FROM #__content_frontpage WHERE content_id = '$listing_id'";
        $this->_db->setQuery( $query );
        $this->_db->query();
    
        $query = "DELETE FROM #__jreviews_content WHERE contentid = '$listing_id'";
        $this->_db->setQuery( $query );
        $this->_db->query();
        
        $query = "DELETE FROM #__jreviews_votes"
        . "\n WHERE review_id IN (SELECT id FROM #__jreviews_comments WHERE pid = $listing_id)";
        $this->_db->setQuery( $query );
        $this->_db->query();        
        
        $query = "DELETE FROM #__jreviews_votes"
        . "\n WHERE review_id IN (SELECT id FROM #__jreviews_comments WHERE pid = $listing_id)";
        $this->_db->setQuery( $query );
        $this->_db->query();  
        
        // delete ratings
        $query = "
            DELETE Rating FROM 
                #__jreviews_ratings AS Rating
            INNER JOIN
                #__jreviews_comments AS Review ON Review.id = Rating.reviewid
            WHERE
                Review.pid = $listing_id
        ";
        $this->_db->setQuery($query);
        $this->_db->query();
        
        $query = "DELETE FROM #__jreviews_comments WHERE pid = '$listing_id' AND `mode` = 'com_content'";
        $this->_db->setQuery( $query );
        $this->_db->query();
        
        // delete listing totals
        $query = "DELETE FROM #__jreviews_listing_totals WHERE listing_id = '$listing_id' AND extension = 'com_content'";
        $this->_db->setQuery( $query );
        $this->_db->query();
        
        // delete claims
        $query = "DELETE FROM #__jreviews_claims WHERE listing_id = '$listing_id'";
        $this->_db->setQuery( $query );
        $this->_db->query();   
        
        // delete reports
        $query = "DELETE FROM #__jreviews_reports WHERE listing_id = '$listing_id' AND extension = 'com_content'";
        $this->_db->setQuery( $query );
        $this->_db->query();                   
                
        # delete thumbnails
        App::import('Model','thumbnail','jreviews');
        $Thumbnail = new ThumbnailModel();
        
        $error = $Thumbnail->delete($data);
        
        $query = "SELECT id FROM #__content WHERE id = $listing_id";
        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();
    
        if (!$result) 
            {
                // Clear cache
                clearCache('', 'views');
                clearCache('', '__data');

                // Trigger plugin callback
                $this->data = &$data;
                $this->plgAfterDelete($data);            
                return true;
            } 
        else 
            {
                return false;
            }
    }
        
    function frontpage($listing_id,$state)
    {
        App::import('Model','frontpage','jreviews');
        $Frontpage = RegisterClass::getInstance('FrontpageModel');
        
        $listing_id = (int) $listing_id;
        
        $row = $Frontpage->findRow(array('conditions'=>array('content_id = ' . $listing_id)));

        $data = array('Frontpage'=>array('content_id'=>$listing_id)); 
        
        if($row)                                                            
            {            
                // Already in frontpage so we delete it
                $result = $Frontpage->delete('content_id',$listing_id);
            } 
        else 
            {            
                // Put in frontpage
                $this->data['Frontpage']['ordering'] = 0;
                $result = $Frontpage->insert('#__content_frontpage','Frontpage',$data);
            }
                
        if($result)
            {
                $Frontpage->reorder();
                
                // Clear cache
                clearCache('', 'views');
                clearCache('', '__data');        
                return true;            
            }
            
        return false;                
    }  
    
    function feature($listing_id,$state)
    {
        $listing_id = (int) $listing_id;
        $new_state = (int) $state;
        $query = "
            INSERT INTO 
                #__jreviews_content (contentid,featured) 
            VALUES 
                ($listing_id,$new_state)
            ON DUPLICATE KEY UPDATE 
                featured = $new_state;
        ";
        
        $this->_db->setQuery($query);
        
        if($this->_db->query())
            {
                // Clear cache
                clearCache('', 'views');
                clearCache('', '__data');        
                return true;            
            }
        
        return false;
    }        
}
