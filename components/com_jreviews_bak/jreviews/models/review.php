<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class ReviewModel extends MyModel {
        
    var $name = 'Review';
    
    var $useTable = '#__jreviews_comments AS Review';

    var $primaryKey = 'Review.review_id';
    
    var $realKey = 'id';
    
    var $fields = array(
        'Review.id AS `Review.review_id`',
        'Review.pid AS `Review.listing_id`', 
        'Review.mode AS `Review.extension`',
        'Review.created AS `Review.created`',
        'Review.modified AS `Review.modified`',
        'Review.userid AS `User.user_id`',
        'CASE WHEN CHAR_LENGTH(User.name) THEN User.name ELSE Review.name END AS `User.name`',        
        'CASE WHEN CHAR_LENGTH(User.username) THEN User.username ELSE Review.username END AS `User.username`',        
        'Review.email AS `User.email`',
        'Review.ipaddress AS `User.ipaddress`',
        'Review.title AS `Review.title`',
        'Review.comments AS `Review.comments`',
        'Review.posts AS `Review.posts`',
        'Review.author AS `Review.editor`',
        'Review.published AS `Review.published`',
        'Rating.ratings AS `Rating.ratings`',
        '(Rating.ratings_sum/Rating.ratings_qty) AS `Rating.average_rating`',
        'Review.vote_helpful AS `Vote.yes`',
        '(Review.vote_total - Review.vote_helpful) AS `Vote.no`',
        '(Review.vote_helpful/Review.vote_total)*100 AS `Vote.helpful`',
        'Review.owner_reply_text AS `Review.owner_reply_text`',
        'Review.owner_reply_approved AS `Review.owner_reply_approved`'
//        'Criteria.id AS `Criteria.criteria_id`',
//        'Criteria.criteria AS `Criteria.criteria`',
//        'Criteria.tooltips AS `Criteria.tooltips`',
//        'Criteria.weights AS `Criteria.weights`'
    );
    
    var $joins = array(
        'ratings'=>'LEFT JOIN #__jreviews_ratings AS Rating ON Review.id = Rating.reviewid',
//        'listings'=>'LEFT JOIN #__content AS Listing ON Review.pid = Listing.id', // Overriden in controller for jReviewsEverywhere
//        'jreviews_categories'=>'LEFT JOIN #__jreviews_categories AS JreviewsCategory ON Listing.catid = JreviewsCategory.id', // AND JreviewsCategory.`option`=\'com_content\'
//        'criteria'=>'LEFT JOIN #__jreviews_criteria AS Criteria ON JreviewsCategory.criteriaid = Criteria.id',
        'user'=>'LEFT JOIN #__users AS User ON Review.userid = User.id'
    );
    
    var $conditions = array();
    
//    var $group = array('Review.id');
    
    var $runProcessRatings = true;
    
    var $valid_fields = array(); // Review fields
        
        
    function addReviewInfo($results, $modelName, $reviewKey)
    {
        // First get the review ids
        foreach($results AS $key=>$row)
            {                 
                if(isset($row[$modelName][$reviewKey])){
                    $review_ids[$row[$modelName][$reviewKey]] = $row[$modelName][$reviewKey];                
                }
            }       

        if(!empty($review_ids))
            {
                $fields = $this->fields; 
                
                $this->fields = array(
                    'Review.id AS `Review.review_id`',
                    'Review.title AS `Review.title`',
                    'Review.`mode` AS `Listing.extension`',
                    'Review.pid AS `Listing.listing_id`',                    
                );
                
                $reviews = $this->findAll(array('conditions'=>array('Review.id IN ('.implode(',',$review_ids).')')),array());
                $reviews = $this->changeKeys($reviews,'Review','review_id');
               
                foreach($results AS $key=>$row)
                {
                    if(isset($reviews[$row[$modelName][$reviewKey]]))
                        {
                        $results[$key] = array_merge($results[$key],$reviews[$row[$modelName][$reviewKey]]);
                        }
                }
            }

        return $results;
    }
            
    /*
    * Centralized review delete function
    * @param array $review_ids
    */
    function delete($review_ids){

        $tables_rel = array();

        $del_id = 'id';
        $del_id_rel = 'reviewid';
        $table_rel = array();
        $table = "#__jreviews_comments";
        $tables_rel[] = "#__jreviews_ratings";
        $tables_rel[] = "#__jreviews_review_fields";
       
        if (!empty($review_ids))
        {
            $this->data['review_id'] = current($review_ids);
            $this->plgBeforeDelete('Review.id',$review_ids); // Only works for single review deletion

            $review_ids = implode(',', $review_ids);
            
            // Get listings info before review id is lost
            $this->_db->setQuery("
                SELECT DISTINCT Review.pid AS listing_id, Review.mode AS extension
                FROM #__jreviews_comments AS Review
                WHERE Review.id IN ($review_ids)
            ");
            $listings = $this->_db->loadObjectList();
            
            $this->_db->setQuery("DELETE FROM $table WHERE $del_id IN ($review_ids)");
            if (!$this->_db->query()) {
                return $this->_db->getErrorMsg();
            }
    
            if (count($tables_rel)) {
                foreach ($tables_rel as $table_rel) {
                    $this->_db->setQuery("DELETE FROM $table_rel WHERE $del_id_rel IN ($review_ids)");
                    if (!$this->_db->query()) {
                        return $this->_db->getErrorMsg();
                    }
                }
            }            

            $this->_db->setQuery("DELETE FROM #__jreviews_reports WHERE review_id IN ($review_ids)");
            if (!$this->_db->query()) {
                return $this->_db->getErrorMsg();
            }
                        
            $this->_db->setQuery("DELETE FROM #__jreviews_votes WHERE review_id IN ($review_ids)");
            if (!$this->_db->query()) {
                return $this->_db->getErrorMsg();
            }
            
            // Update listing totals        
            $err = array();
            foreach ( $listings as $listing )
            {
                if ( !$this->saveListingTotals($listing->listing_id, $listing->extension) )
                {
                    $err[] = $listing->listing_id;
                }
            }
            
            if ( !empty($err) )
            {
                return
                    'NOTICE! There was an error updating totals for listing ID # '.implode(',', $err)
                    ."\n".'Average ratings may be incorrect.'
                    ."\n".'Use the Sync Ratings button in Criteria Manager to update the averages.'
                ;
            }            
        }
        
        // Clear cache
        clearCache('', 'views');
        clearCache('', '__data');
                    
        return true;                
        
    }    
        
    function getRankList() 
    {        
        # Check for cached version        
        $cache_prefix = 'review_model_ranklist';
        $cache_key = func_get_args();
        if($cache = S2cacheRead($cache_prefix,$cache_key)){
            return $cache;
        }
                        
        $excludeEditorReviews = Configure::read('Jreviews.editor_rank_exclude');
                        
        $query = "SELECT Review.userid, count(Review.userid) AS review_count,"
        . "\n SUM(Review.vote_helpful)/SUM(Review.vote_total) AS helpful,"
        . "\n (COUNT(Review.id)*(SUM(Review.vote_helpful)/SUM(Review.vote_total))) IS NULL AS vote_null"
        . "\n FROM #__jreviews_comments AS Review"
        . "\n WHERE Review.published = 1 AND Review.userid > 0"
        . ($excludeEditorReviews ? "\n  AND Review.author = 0" : "")
        . "\n GROUP BY Review.userid"
        . "\n ORDER BY review_count DESC, helpful DESC, vote_null ASC"
        ;

        $this->_db->setQuery($query);

        $rows = $this->_db->loadAssocList();

        $userids = array();

        if ($rows) {
            $i = 0;
            while(isset($rows[$i])) {
               $userids[$rows[$i]['userid']] = $i+1;
               $i++;
            }
        }

        # Send to cache
        S2cacheWrite($cache_prefix,$cache_key,$userids);
                
        return $userids;
    }
    
    function getReviewExtension($review_id) {

        $query = "SELECT Review.`mode` FROM #__jreviews_comments AS Review WHERE Review.id = " . (int) $review_id;
        $this->_db->setQuery($query);
        return $this->_db->loadResult();
    }
    
    function getReviewerTotal() {
        
        $this->_db->setQuery(
            "SELECT COUNT(DISTINCT(userid))"
            ."\n FROM #__jreviews_comments AS Review"
            ."\n WHERE Review.published = 1 AND Review.userid > 0" // AND Review.author = 0 
        );
        
        return $this->_db->loadResult();
    }
    
    function getRankPage($page,$limit) 
    {         
        # Check for cached version        
        $cache_prefix = 'review_model_rankpage';
        $cache_key = func_get_args();
        if($cache = S2cacheRead($cache_prefix,$cache_key)){
            return $cache;
        }    

        $offset = (int)($page-1)*$limit;
        
        $fields = array(
            'Review.userid AS `User.user_id`',
            'User.name AS `User.name`',
            'User.username AS `User.username`',
            'count(Review.userid) AS `Review.count`',
            'SUM(Review.vote_helpful)/SUM(Review.vote_total) AS `Vote.helpful`',
            'SUM(Review.vote_total) AS `Vote.count`',
            '(count(Review.id)*(SUM(Review.vote_total))) is null AS `Vote.is_null`' 
        );
        
        $excludeEditorReviews = Configure::read('Jreviews.editor_rank_exclude');        
        
        $query = "SELECT " . implode(',',$fields)
         ."\n FROM #__jreviews_comments AS Review"
         ."\n RIGHT JOIN #__users AS User ON Review.userid = User.id"
         ."\n WHERE Review.published = 1 AND Review.userid > 0"
        . ($excludeEditorReviews ? "\n  AND Review.author = 0" : "")
         ."\n GROUP BY Review.userid"
         ."\n ORDER BY `Review.count` DESC, `Vote.helpful` DESC, `Vote.is_null` ASC"
         ."\n LIMIT $offset, $limit"
         ;
         
        $this->_db->setQuery($query);

        $temp = $this->_db->loadObjectList();

        $temp = $this->__reformatArray($temp);

        # Add Community info to results array
        if(!defined('MVC_FRAMEWORK_ADMIN') && class_exists('CommunityModel')) {
            $Community = registerClass::getInstance('CommunityModel');
            $results = $Community->addProfileInfo($temp, 'User', 'user_id');
        }

        # Send to cache
        S2cacheWrite($cache_prefix,$cache_key,$results);        
        
        return $results;         
    }
        
    /**
     * Saves totals for the listing after any kind of reviews update (save, publish, delete, change weights etc.)
     * @return    boolean
     */
    function saveListingTotals($listing_id, $extension, $weights = array())
    {          
        if (empty($weights)) {
            // Load listings' Everywhere model
            $file_name = 'everywhere' . '_' . $extension;          
            $class_name = inflector::camelize($file_name).'Model';
            App::import('Model',$file_name,'jreviews');
            $ListingModel = new $class_name();
                                       
            $weights = $ListingModel->findRow(array(
                'fields' => 'Criteria.weights AS `Criteria.weights`',
                'conditions' => "Listing.{$ListingModel->realKey} = {$listing_id}"
                ),
                array() // No callback functions
            );

            unset($ListingModel);
        
            $weights = explode("\n", trim($weights['Criteria']['weights']));        
        }
       
        $reviewTypes['user'] = 0; # user reviews
        
        # editor reviews only in com_content
        if ( $extension == 'com_content' )
        {
            $reviewTypes['editor'] = 1; 
        }
        
        # initiate the results array now moved before the foreach
        $data['Totals'] = array(
            'listing_id' => $listing_id
            ,'extension' => $extension
        );
        
        # encompassing all calculations with foreach (procedures like changing the review type can affect both averages)
        foreach ( $reviewTypes as $reviewType => $reviewTypeValue )
        {
            # count comments
            $query = "
                SELECT COUNT(*)
                FROM #__jreviews_comments
                WHERE
                    pid = $listing_id
                    AND mode = '$extension'
                    AND published = 1
                    AND author = $reviewTypeValue
            "; 
            $this->_db->setQuery($query);

            $data['Totals'][$reviewType.'_comment_count'] = $this->_db->loadResult();
            
            if ( empty($data['Totals'][$reviewType.'_comment_count']) )
            {
                # listing deletion moved after the foreach. instead populate the relevant array elements with empty values and move on
                $data['Totals'] += array_fill_keys(array($reviewType.'_rating',$reviewType.'_rating_count',$reviewType.'_criteria_rating',$reviewType.'_criteria_rating_count'), '');
                
                continue;
            }
            
            $reviewsExist = 1; # to be used after the foreach
            
            // Now, do ratings exist?
            $query = "SELECT Rating.ratings"
                ."\n FROM #__jreviews_comments AS Review"
                ."\n INNER JOIN #__jreviews_ratings AS Rating ON Review.id = Rating.reviewid"
                ."\n WHERE Review.pid = '$listing_id' AND Review.published = 1"
                ."\n AND Review.author = $reviewTypeValue"
                ."\n AND Review.mode = '$extension'"
        //        ."\n GROUP BY reviews.id"
            ;
            $this->_db->setQuery($query);
            $rows = $this->_db->loadAssocList();
                
            if (!empty($rows)) {
                // Ratings exist, begin calculations

                $weighted = (is_array($weights) && array_sum($weights) == 100 ? 1 : 0);
                
                $reviewCount = 0;
                
                $sumRatings = array(); # must init so values from previous foreach iteration won't be used
                
                // This is used like reviewCount, but for each criterion separately. 
                // Preparing the inital array here, see later on for its use
                $reviewCountForCriterion = array_fill(0, count(explode(',', $rows[0]['ratings'])), 0); 
                
                foreach ($rows as $rating) {

                    $ratings_array = explode(',',$rating['ratings']);
                    
                    // if all is N/A, do not count this review towards the average
                    if ( array_sum($ratings_array) != 0 ) # recall 'na' == 0 equals true
                    {
                        $reviewCount++;
                    }
                    
                    // Calculates the totals for each criteria
                    for ($j = 0;$j<count($ratings_array);$j++) 
                    {
                        if (isset($sumRatings[$j])) 
                        {
                            $sumRatings[$j] += $ratings_array[$j];
                        } else 
                        {
                            $sumRatings[$j] = $ratings_array[$j];
                        }
                        
                        /// If value is N/A, do not count this review towards the criterion average. 
                        if ( $ratings_array[$j] != 0 ) # recall 'na' == 0 equals true
                        {
                            $reviewCountForCriterion[$j]++;
                        }
                        
                    }

                }
                
                # creates criteria averages.
                $ratings = 
                    array_map(
                        create_function(
                            '$el, $revCount', 
                            'return empty($revCount) ? "na" : number_format($el / $revCount, 4);'
                        ), 
                        $sumRatings,
                        $reviewCountForCriterion
                    )
                ;
                
                $userRating = 'na';
                
                if ( $reviewCount > 0 ) # if there's at least one not-n/a rating somewhere!
                {
                    if ( $weighted )
                    {
                        # calculate sum of valid weights (=whose ratings aren't n/a)
                        $sumWeights = 
                            array_sum(
                                array_intersect_key(
                                    $weights, 
                                    array_filter(
                                        $sumRatings,
                                        create_function(
                                            '$el', 'return !empty($el) && $el != "na";' # both conditions must be checked so to include a case of only one review! (never mind, just don't change it..)
                                        )
                                    )
                                )
                            )
                        ;
                        
                        if ( $sumWeights > 0 )
                        {
                            foreach ( $ratings as $k => $v )
                            {
                                $userRating += $v * $weights[$k] / $sumWeights;
                            }
                        }
                        
                    } # if ( $weighted )
                    
                    else 
                    {
                        # calculate the average, count criteria averages without the n/a ones
                        $userRating = 
                            array_sum($ratings) 
                            /
                            count(
                                array_filter(
                                    $ratings, 
                                    create_function(
                                        '$el', 'return !empty($el) && $el != "na";' # both conditions must be checked so to include a case of only one review! (never mind, just don't change it..)
                                    )
                                )
                            )
                        ;
                    }
                
                } # if ( $reviewCount > 0 )
            
                // populate saving array for jreviews_listing_totals table
                $data['Totals'] += array(
                    $reviewType.'_rating' => is_numeric($userRating) ? number_format($userRating, 4) : $userRating
                    ,$reviewType.'_rating_count' => $reviewCount
                    ,$reviewType.'_criteria_rating' => implode(',', $ratings)
                    ,$reviewType.'_criteria_rating_count' => implode(',', $reviewCountForCriterion)
                );
                
            } # if ($rows)
            
        } # foreach ( $reviewTypes as $reviewType )
               
        // ready to update database!
        
        # reviews exist (user or editor). delete listing row
        if ( empty($reviewsExist) )
        {
            appLogMessage('*******Deleting listing totals for listing ID '.$listing_id.' extension '.$extension, 'database');
            
            # not using s2 db function since it will use the wrong table
            $query = "
                DELETE FROM #__jreviews_listing_totals
                WHERE
                    listing_id = $listing_id
                    AND extension = '$extension'
            ";
            $this->_db->setQuery($query);
            if ( !$this->_db->query() )
            {
                appLogMessage('*******There was a problem deleting listing totals for listing ID '.$listing_id.' extension '.$extension, 'database');
                return false;
            }
            
            return true;
        }
        
        // Reviews exist, proceed to save
        appLogMessage('*******Save listing totals for listing ID '.$listing_id.' extension '.$extension, 'database');
        
        if( !$this->replace('#__jreviews_listing_totals', 'Totals', $data, 'listing_id') )
        {
            appLogMessage('*******There was a problem saving the listing totals for listing ID '.$listing_id.' extension '.$extension, 'database');    
            return false;    
        }
                            
        return true;
    }
    
    function processSorting($selected = null) 
    {
        $order = '';
            
        switch ( $selected ) {
              case 'rdate':
                  $order = '`Review.created` DESC';
                  break;
              case 'date':
                  $order = '`Review.created` ASC';
                  break;
              case 'rating':
                  $order = '`Rating.average_rating` DESC, `Review.created` DESC';
                  break;
              case 'rrating':
                  $order = '`Rating.average_rating` ASC, `Review.created` DESC';
                  break;
              case 'helpful':
                  $order = 'Review.vote_helpful DESC, `Rating.average_rating` DESC';
                  break;
              case 'rhelpful':
                  $order = 'Review.vote_helpful ASC, `Rating.average_rating` DESC';
                  break;                  
            default:
                $order = '`Review.created` DESC';
                 break;
        }
    
        return $order;
    }    
    
    function updatePostCount($review_id,$value)
    {
        if($value != 0)
        {
            $query = "
                UPDATE 
                    #__jreviews_comments AS Review
                SET 
                    Review.posts = Review.posts " . ($value == 1 ? '+1' : '-1') . "
                WHERE
                    Review.id = ". (int) $review_id                
            ;
            $this->_db->setQuery($query);
            if(!$this->_db->query()){
                return false;
            } 
            return true;  
        }
    }
    
    function updateVoteHelpfulCount($review_id,$value)
    {
        $query = "
            UPDATE 
                #__jreviews_comments AS Review
            SET 
                Review.vote_helpful = Review.vote_helpful + " . $value . ",
                Review.vote_total = Review.vote_total + 1
            WHERE
                Review.id = ". (int) $review_id                
        ;
        $this->_db->setQuery($query);
        if(!$this->_db->query()){
            return false;
        } 
        return true;              
    }
    
    function save(&$data,$Access,$validFields)
    {       
        $Config = Configure::read('JreviewsSystem.Config');
        $userid = $this->_user->id;
        $this->valid_fields = $validFields;
                
        # Check if this is a new review or an updated review
        $isNew = (int) $data['Review']['id'] > 0 ? false : true;
        $review_id = (int) $data['Review']['id'];

        $output = array("err" => '', "reviewid" => '', "author" => 0 );

        # If new then assign the logged in user info. Zero if it's a guest
        if ($isNew) {
            # Validation passed, so proceed with saving review to DB
            $data['Review']['ipaddress'] = s2GetIpAddress();
            $data['Review']['userid'] = $this->_user->id;
            $data['Review']['created'] = gmdate('Y-m-d H:i:s');
        }

        # Edited review
        if (!$isNew) 
        {
            appLogMessage('*********Load current info because we are editing the review','database');
            
            // Load the review info
            $row = $this->findRow(array('conditions'=>array('Review.id = ' . $review_id)),array() /* stop callbacks*/ );
            $data['ratings_col_empty'] = Sanitize::getString($row['Rating'],'ratings','') == ''; // Used in afterFind
            
            // Capture ip address of reviewer
            if ( $this->_user->id == $row['User']['user_id']) {
                $data['Review']['ipaddress'] = s2GetIpAddress();
            }
            
            $data['Review']['modified'] = gmdate('Y-m-d H:i:s'); // Capture last modified date        
            $data['Review']['author'] = $row['Review']['editor'];            
        } 
        
        # Complete user info for new reviews
        if ($isNew && $this->_user->id > 0) 
            {
                $data['Review']['name'] = $this->_user->name;
                $data['Review']['username'] = $this->_user->username;            
                $data['Review']['email'] = $this->_user->email;            
            } 
        elseif(!$isNew && !$Access->isManager()) 
            {
                unset($data['Review']['name']);
                unset($data['Review']['username']);
                unset($data['Review']['email']);            
            }
        
        if(!defined('MVC_FRAMEWORK_ADMIN'))
        {            
            $data['Review']['published'] = (int) ! (
                    ( $Access->moderateReview() && $isNew && !$data['Review']['author'] )
                ||    ( $Config->moderation_editor_reviews && $isNew && $data['Review']['author'] )
                ||    ( $Access->moderateReview() && $Config->moderation_review_edit && !$isNew && !$data['Review']['author'] )
                ||    ( $Access->moderateReview() && $Config->moderation_editor_review_edit && !$isNew && $data['Review']['author'] )
            );
        }

        # Get criteria info    to process ratings
        appLogMessage('*******Get criteria info to process ratings','database');

        $CriteriaModel = RegisterClass::getInstance('CriteriaModel');
        $criteria = $CriteriaModel->findRow(
            array(
                'conditions'=>array('Criteria.id = '. $data['Criteria']['id'])
            )
        );
        // Complete review info with $criteria info
        $data = array_insert($data,$criteria);
        
        $data['new'] = $isNew ? 1 : 0;

        # Save standard review fields
        appLogMessage('*******Save standard review fields','database');
        $save = $this->store($data);

        if(!$save) {
            appLogMessage('*******There was a problem saving the review fields','database');    
            $output['err'] = "There was a problem saving the review fields";    
        }
        
        return $output;        
    }        
 
    /**
    * Saves review ratings, fields and recalculates listing totals
    * 
    * @param mixed $status
    */
    function afterSave($status)
    {               
        $isNew = Sanitize::getBool($this->data,'new');
        $ratings_col_empty = Sanitize::getBool($this->data,'ratings_col_empty');
        $weights = '';
       
        if(isset($this->data['Criteria']) && Sanitize::getInt($this->data['Criteria'],'state') == 1) 
        {
            // Process rating data
            // to account for "n/a" values in the ratings and weights, changing the source arrays rather than the whole computation procedure.
            
            // init variables
            $applicableRatings = array_filter($this->data['Rating']['ratings'], create_function('$el', 'return is_numeric($el);')); 
            $ratings_qty = count($applicableRatings);
            $this->data['average_rating'] = $ratings_sum = 'na';
            
            if ( $ratings_qty > 0 )
            {
                if (trim($this->data['Criteria']['weights'])!='') 
                {
                    $weights = explode ("\n", $this->data['Criteria']['weights']);
                    
                    // we have to remove the irrelevant weights so to produce clean weights_sum to be used later for proportion calculations
                    $sumWeights = array_sum(array_intersect_key($weights, $applicableRatings));
                    
                    if ( $sumWeights > 0 ) 
                    {
                        foreach ($applicableRatings  as $key=>$rating)
                            {
                                $ratings_sum += $rating * $weights[$key] / $sumWeights; 
                            }
                        
                        $ratings_sum = $ratings_sum*$ratings_qty; // This is not the real sum, but it is divided again in the queries.
                    }

                } else {
                    $ratings_sum = array_sum($applicableRatings);
                }
                
                // Makes average rating easily available in Everywhere model afterSave method        
                $this->data['average_rating'] = $ratings_sum / $ratings_qty;
                $this->data['Rating']['ratings_sum'] = $ratings_sum;
                $this->data['Rating']['ratings_qty'] = $ratings_qty;
                
            } # if ( $ratings_qty > 0  )i            
            
            
            $this->data['Rating']['reviewid'] = $this->data['Review']['id'];
            $this->data['Rating']['ratings'] = implode(',',$this->data['Rating']['ratings']);
           
            # Save rating fields
            appLogMessage('*******Save standard rating fields','database');
            if($isNew || (!$isNew && $ratings_col_empty)) {
                $save = $this->insert( '#__jreviews_ratings', 'Rating', $this->data, 'reviewid');
            } else {
                $save = $this->update( '#__jreviews_ratings', 'Rating', $this->data, 'reviewid');
            }
           
            if(!$save) {
                appLogMessage('*******There was a problem saving the ratings','database');    
                return false;    
            }
            
        } # if ( $criteria['Criteria']['state'] == 1 )
        
        // save listing totals
        if ( !$this->saveListingTotals($this->data['Review']['pid'], $this->data['Review']['mode'], $weights) )
        {     
            return false;
        }
        
        # Save custom fields
        appLogMessage('*******Save review custom fields','database');
        $this->data['Field']['Review']['reviewid'] = $this->data['Review']['id'];
        App::import('Model','field','jreviews');
        $FieldModel = RegisterClass::getInstance('FieldModel');
       if(count($this->data['Field']['Review'])> 1 && !$FieldModel->save($this->data, 'review', $isNew, $this->valid_fields))
       {
            return false;    
       }
    }   

    function afterFind($results) 
    {
        if (empty($results)) {
            return $results;
        }
                    
        $sumRatings = array();
                
        # Add Community Builder info to results array
        if(!defined('MVC_FRAMEWORK_ADMIN') && class_exists('CommunityModel')) {
            $Community = registerClass::getInstance('CommunityModel');
            $results = $Community->addProfileInfo($results, 'User', 'user_id');
        }

        # Add custom field info to results array
        App::import('Model','field','jreviews');
        $CustomFields = new FieldModel();
        $results = $CustomFields->addFields($results,'review');
        
        # User rank
        if(!defined('MVC_FRAMEWORK_ADMIN') && !isset($this->rankList)) {                     
            $this->rankList = $this->getRankList();
        }

        # Preprocess criteria and rating information
        if($this->runProcessRatings) {  
            $results = $this->processRatings($results);
        }
                
        return $results;
    }
    
    /**
     * Pre-process criteria and rating information
     */    
    function processRatings($results) {
                           
        $single_row = false;
         
        foreach($results AS $key=>$result) 
        {                  
            if($result['Criteria']['state'] != 1 )
            {                  
                if($results[$key]['User']['user_id']>0 && isset($this->rankList[$results[$key]['User']['user_id']])) {

                    $results[$key]['User']['review_rank'] = $this->rankList[$results[$key]['User']['user_id']];
                } else {
                    $results[$key]['User']['review_rank'] = null;
                }
            } 
            
            if(isset($results[$key]['Rating']) && is_string($results[$key]['Rating']['ratings'])) {
                
                // check if all is n/a. if this is not checked the average of a totally n/a review will be zero and not n/a. easier to do here than to create a complex query. cannot do == 0 check since 0 ratings may be implemented in the future. 
                if ( strlen(trim($results[$key]['Rating']['ratings'], 'na,')) == 0 )
                {
                    $results[$key]['Rating']['average_rating'] = 'na';
                }
                                
                $results[$key]['Rating']['ratings'] = explode(',',$results[$key]['Rating']['ratings']);
            }
            
            if(isset($result['Criteria']['criteria']) && $result['Criteria']['criteria'] != '' && is_string($result['Criteria']['criteria'])) {
                $results[$key]['Criteria']['criteria'] = explode("\n",$results[$key]['Criteria']['criteria']);
            }

            if(isset($result['Criteria']['tooltips']) && $result['Criteria']['tooltips'] != '' && is_string($result['Criteria']['tooltips'])) {
                $results[$key]['Criteria']['tooltips'] = explode("\n",$results[$key]['Criteria']['tooltips']);
            }

            # Calculate weighted average rating for each review
            if(
                isset($result['Criteria']['weights'])
                && $result['Criteria']['weights'] != '' 
                && is_string($result['Criteria']['weights']) 
                && !empty($results[$key]['Rating']['ratings']) // since could be comments without ratings
                && $results[$key]['Rating']['average_rating'] != 'na'
            ) {
                
                $results[$key]['Criteria']['weights'] = explode("\n",$results[$key]['Criteria']['weights']);
            
                $weighted_average = 0;
                
                if (array_sum($results[$key]['Criteria']['weights']) == 100) {    
                    // see function save() for explanations. basically this extracts the relevant weights (without N/A rates) and sums them.                    
                    $sumWeights = 
                        array_sum(
                            array_intersect_key(
                                $results[$key]['Criteria']['weights'], 
                                array_filter(
                                    $results[$key]['Rating']['ratings'],
                                    create_function(
                                        '$el', 'return is_numeric($el);'
                                    )
                                )
                            )
                        )
                    ;        
                    if ( $sumWeights > 0 )
                    {
                        $i = 0;
                        while(isset($results[$key]['Rating']['ratings'][$i])) 
                        {
                                                        
                            $weighted_average += $results[$key]['Rating']['ratings'][$i] * $results[$key]['Criteria']['weights'][$i] / $sumWeights;
                        
                            $i++;
                        }
                    }

                    $results[$key]['Rating']['average_rating'] = $weighted_average;
                }            
            }

            if($results[$key]['User']['user_id']>0 && isset($this->rankList[$results[$key]['User']['user_id']])) {

                $results[$key]['User']['review_rank'] = $this->rankList[$results[$key]['User']['user_id']];
            } else {
                $results[$key]['User']['review_rank'] = null;
            }

        }
        
        return $results;        
    }
    
    function getTemplateSettings($review_id) {
        
        # Check for cached version        
        $cache_prefix = 'review_model_themesettings';
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
        . "\n FROM #__jreviews_comments AS Review"
        . "\n LEFT JOIN #__content AS Listing ON Review.pid = Listing.id"        
        . "\n LEFT JOIN #__categories AS Category ON Listing.catid = Category.id"
        . "\n LEFT JOIN #__jreviews_categories AS JreviewsCategory ON Category.id = JreviewsCategory.id"
        . "\n LEFT JOIN #__sections AS Section ON Category.section = Section.id"
        . "\n LEFT JOIN #__jreviews_sections AS JreviewsSection ON Section.id = JreviewsSection.sectionid"
        . "\n WHERE JreviewsCategory.option = 'com_content' AND Review.id = " . $review_id
        ;
        
        $this->_db->setQuery($query);
        
        $result = end($this->__reformatArray($this->_db->loadAssocList()));        

        # Send to cache
        S2cacheWrite($cache_prefix,$cache_key,$result);
        
        return $result;
    }    
}
