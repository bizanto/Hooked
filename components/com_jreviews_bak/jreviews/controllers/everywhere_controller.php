<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class EverywhereController extends MyController {
	
	var $uses = array('menu','user','captcha','review','field','criteria','vote');
	
	var $helpers = array('assets','routes','libraries','html','form','time','jreviews','custom_fields','rating','community');
	
	var $components = array('config','access','everywhere');

	var $autoRender = false;

	var $autoLayout = false;
    
    var $review_fields = null;

	function beforeFilter() 
    {
		# Call beforeFilter of MyController parent class
		parent::beforeFilter();
	}
		
    function afterFilter()
    {
        App::import('Helper','assets','jreviews');            
        $Assets = RegisterClass::getInstance('AssetsHelper');
        $Assets->name = $this->name;
        $Assets->action = $this->action;
        $Assets->params = $this->params;
        $Assets->Access = $this->Access;
        $Assets->Config = $this->Config;        
        $Assets->assetParams['review_fields'] = $this->review_fields;
        unset($this->review_fields);
        parent::afterFilter();          
    }
            			
	/**
	 * Method used in Everywhere extensions detail pages
	 *
	 * @return array with html output, listing, reviews, rating summary
	 */
	function index() 
	{														
		$listing_id = Sanitize::getInt($this->data,'listing_id');
		$listing = $this->Listing->findRow(array('conditions'=>"Listing.{$this->Listing->realKey} = $listing_id"));
        if(!is_array($listing) || empty($listing)) {
            return false;    
        }

        $listing['Criteria']['required'] = explode("\n",$listing['Criteria']['required']);
        
		$extension = isset($this->Listing->extension_alias) ? $this->Listing->extension_alias : $this->Listing->extension;		
		
		$fields = array(
			'Criteria.id AS `Criteria.criteria_id`',
			'Criteria.criteria AS `Criteria.criteria`',
			'Criteria.tooltips AS `Criteria.tooltips`',
			'Criteria.weights AS `Criteria.weights`',
			'Criteria.state AS `Criteria.state`', 
			'Criteria.required AS `Criteria.required`' // this is needed to determine if n/a ratings is a possibility for the criteria set, used in detailed_ratings template
		);
		
		$conditions = array(
			'Review.pid= '. $listing_id,
			'Review.author = 0',
			'Review.published = 1',
			"Review.mode = " . $this->quote($extension),
			"JreviewsCategory.`option` = " . $this->quote($extension)
		);
		
		$this->limit = Sanitize::getInt($this->data,'limit_special',$this->Config->user_limit);		

		$queryData = array(
			'fields'=>$fields,
			'conditions'=>$conditions,
			'offset'=>0,
			'limit'=>$this->limit,
			'order'=>array('Review.created DESC'),
		);
		
		$reviews = $this->Review->findAll($queryData);

		// Remove unnecessary query parameters for findCount
		$this->Review->joins = array(); // Only need to query comments table
		unset($conditions[4]); // JreviewsCategory join above

		$queryData = array(
			'conditions'=>$conditions
		);		
		
		$review_count = $this->Review->findCount($queryData);

		// prepare ratings_summary array
		$query = "
			SELECT
				user_rating, user_criteria_rating, user_rating_count, user_criteria_rating_count
			FROM
				#__jreviews_listing_totals
			WHERE
				listing_id = $listing_id
                AND extension = " . $this->quote($extension)
		;
		$this->_db->setQuery($query);
		$totals = current($this->_db->loadAssocList());
                		
		$ratings_summary = array(
			'Rating' => array(
				'average_rating' => $totals['user_rating'],
				'ratings' => explode(',', $totals['user_criteria_rating']),
				'criteria_rating_count' => explode(',', $totals['user_criteria_rating_count'])
			),
			'Criteria' => $listing['Criteria'],
			'summary' => 1
		);         
        
		$ratings_summary['Criteria']['required'] = $listing['Criteria']['required'];
		$review_fields = $this->review_fields = $this->Field->getFieldsArrayNew($listing['Criteria']['criteria_id'], 'review');

		$security_code = '';

		if($this->Access->showCaptcha) {

			$captcha = $this->Captcha->displayCode();

			$security_code = $captcha['image'];
		}
		
		# Initialize review array and set Criteria and extension keys
		$review = $this->Review->init();
		$review['Review']['extension'] = $extension;
		$review = array_merge($review,$ratings_summary); // Adds the missing required criteria array 
        # check for duplicate reviews   
        $this->_user->duplicate_review = false;

        // It's a guest so we only care about checking the IP address if this feature is not disabled and
        // server is not localhost
        if(!$this->_user->id)
        {
            if(!$this->Config->review_ipcheck_disable && $this->ipaddress != '127.0.0.1')
            {
                // Do the ip address check everywhere except in localhost
               $this->_user->duplicate_review = (bool) $this->Review->findCount(array('conditions'=>array(
                    'Review.pid = '.$listing_id,
                    "Review.ipaddress = '{$this->ipaddress}'",
                    "Review.mode = '{$extension}'",
                    "Review.author = 0",
                    "Review.published >= 0" 
                )));        
            }
        } 
        else
        // It's a registered user and multiple reviews not allowed
        {
            if(!$this->Config->user_multiple_reviews)
            {
                $this->_user->duplicate_review = (bool) $this->Review->findCount(array('conditions'=>array(
                    'Review.pid = '.$listing_id,
                    "(Review.userid = {$this->_user->id}" . 
                        (  
                            $this->ipaddress != '127.0.0.1' && !$this->Config->review_ipcheck_disable
                        ? 
                            " OR Review.ipaddress = '{$this->ipaddress}') "
                        : 
                            ')' 
                        ),
                    "Review.mode = '{$extension}'",
                    "Review.author = 0",
                    "Review.published >= 0" 
                )));
            }
        }        
     
		$this->set(array(
				'Access'=>$this->Access,
				'User'=>$this->_user,
				'listing'=>$listing,
				'reviews'=>$reviews,
				'ratings_summary'=>$ratings_summary,
				'reviewType'=>'user',
				'review_count'=>$review_count,
				'user_rating_count'=>$totals['user_rating_count'],
				'review_fields'=>$review_fields,
				'review'=>$review,
				'captcha'=>$security_code
			)
		);

		$output = array(
			'output'=>$this->render($this->name,'reviews'),
			'summary'=>$this->render($this->name,'summary'),
			'detailed_ratings'=>$this->render('elements','detailed_ratings'), // Ratings graph
			'listing'=>$listing,
			'reviews'=>$reviews,
			'review_count'=>$review_count,
			'ratings'=>$ratings_summary // Ratings array
		);
		
		return $output;
	}
	
	/**
	 * Method used in Everywhere extensions category pages
	 *
	 * @return array with html output, rating summary
	 */	
	function category() 
	{
        $listing_id = $this->data['listing_id'];
		$extension = $this->data['extension'];

		$listing = $this->Listing->findRow(array('conditions'=>"Listing.{$this->Listing->realKey} = $listing_id"));

		if(!is_array($listing) || empty($listing)) {
			return false;	
		}
				
		$conditions = array(
			'Review.pid= '. $listing_id,
			'Review.author = 0',
			'Review.published = 1',
			"Review.mode = " . $this->quote($this->data['extension']),
		);

		$queryData = array(
			'conditions'=>$conditions
		);
		
		// Remove unnecessary query parameters for findCount
		$this->Review->joins = array(); // Only need to query comments table

		$review_count = $this->Review->findCount($queryData);

        // prepare ratings_summary array
        $query = "
            SELECT
                user_rating, user_criteria_rating, user_rating_count, user_criteria_rating_count
            FROM
                #__jreviews_listing_totals
            WHERE
                listing_id = $listing_id
                AND extension = " . $this->quote($extension)
        ;
        $this->_db->setQuery($query);
        $totals = current($this->_db->loadAssocList());

        $ratings_summary = array(
            'Rating' => array(
                'average_rating' => $totals['user_rating'] > 0 ? $totals['user_rating'] : false,
                'ratings' => explode(',', $totals['user_criteria_rating']),
                'criteria_rating_count' => explode(',', $totals['user_criteria_rating_count'])
            ),
            'Criteria' => $listing['Criteria'],
            'summary' => 1
        ); 
                
        # Initialize review array and set Criteria and extension keys
        $review = $this->Review->init();
        $review['Criteria'] = $listing['Criteria'];
        $review['Review']['extension'] = $extension;
        $review = array_merge($review,$ratings_summary);
        
        // Make sure that detailed rating is processed as such in category page independent of detailed rating setting in reviews tab
        unset($review['Review']); 
        $this->Config->user_rating = 1;
                      
        $ratings_summary['Criteria']['required'] = explode("\n", $review['Criteria']['required']); 
                                                                        
		$this->set(array(
				'reviewType'=>'user',
				'review'=>$review,
				'ratings_summary'=>$ratings_summary,
				'review_count'=>$review_count,
				'user_rating_count'=>$totals['user_rating_count'],
			)
		);

		return array(
			'output'=>$this->render($this->name,'summary'),
			'listing'=>$listing,
			'review_count'=>$review_count,
			'detailed_ratings'=>$this->render('elements','detailed_ratings'), // Ratings graph			
			'ratings'=>$ratings_summary // Ratings array
		);			
	}
			
}

