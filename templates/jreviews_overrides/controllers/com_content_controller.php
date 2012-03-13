<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class ComContentController extends MyController {
	
	var $uses = array('user','menu','captcha','criteria','directory','field','favorite','review','category','vote');
	
	var $helpers = array('assets','routes','libraries','html','form','text','time','jreviews','thumbnail','custom_fields','rating','community');	
	
	var $components = array('config','access','everywhere');
	
	var $autoRender = false; //Output is returned
	
	var $autoLayout = true;
    
    var $listingResults;
    
	function beforeFilter() 
    {					
 		# Call beforeFilter of MyController parent class
		parent::beforeFilter();	
		
		# Make configuration available in models
		$this->Listing->Config = &$this->Config;
	}
	
    function afterFilter()
    {                
        if(isset($this->review_fields))
        {            
            $Assets = RegisterClass::getInstance('AssetsHelper');
            $Assets->assetParams['review_fields'] = $this->review_fields;
            $Assets->assetParams['owner_id'] = $this->owner_id;
            unset($this->review_fields);            
        }
        parent::afterFilter();          
    }
    
    // Need to return object by reference for PHP4
    function &getPluginModel() 
    { 
        return $this->Listing;
    }
        
    // Need to return object by reference for PHP4
    function &getObserverModel() 
    {
        return $this->Listing;
    }    
        
	function com_content_view($passedArgs) 
    {   
		$this->layout = 'detail';
		
		$content_row = $passedArgs['row'];
		$content_params = $passedArgs['params'];

        // Escape quotes in meta tags
        $content_row->metadesc = htmlspecialchars($content_row->metadesc,ENT_QUOTES,'UTF-8');
        $content_row->metakey = htmlspecialchars($content_row->metakey,ENT_QUOTES,'UTF-8');
        
		$editor_review = array();
		$editor_ratings_summary = array(); 
		$editor_review_count = null; 
		$reviews  = array();
		$ratings_summary = array();
		$review_count = null;	
        $crumbs = array();
								
		// Check if item category is configured for jreviews		
		if(!$this->Category->isJreviewsCategory($content_row->catid)) 
        {			
			return array('row'=>$content_row,'params'=>$content_params);
		}
        
		# Override content page parameter settings
//		prx($content_params);
//		$content_params->set('show_pdf_icon',0);
//		$content_params->set('show_print_icon',0);
//		$content_params->set('show_email_icon',0);

		$content_params->set('show_title',0);
		$content_params->set('item_title',0); // J1.0.x & Mambo 4.6.x
		
		$content_params->set('show_category',0);
		$content_params->set('category',0); // J1.0.x & Mambo 4.6.x
		
		$content_params->set('show_section',0);
		$content_params->set('section',0); // J1.0.x & Mambo 4.6.x
		
		$content_params->set('show_author',0);
		$content_params->set('author',0); // J1.0.x & Mambo 4.6.x
		
		$content_params->set('show_create_date',0);
		$content_params->set('createdate',0); // J1.0.x & Mambo 4.6.x
		
		$content_params->set('show_vote',0);
		$content_params->set('rating',0); // J1.0.x & Mambo 4.6.x

		$content_params->set('show_modify_date',0);
		$content_params->set('modifydate',0); // J1.0.x & Mambo 4.6.x
		
		$content_params->set('page_title','');
		$content_params->set('show_page_title',0); // J1.5.4+		
				
		$content_params->set('show_hits',0);
		
		$content_params->set('show_item_navigation',0);
		$content_params->set('item_navigation',0); // J1.0.x & Mambo 4.6.x		

		# Get listing and review summary data
		$fields = array(
			'Criteria.criteria AS `Criteria.criteria`',
			'Criteria.tooltips AS `Criteria.tooltips`',
		);

		// Need to query the listing even if view cache enabled because otherwise there's no way to set breadcrumbs and meta data in the content plugin 
        $listing = $this->Listing->findRow(array('fields'=>$fields,'conditions'=>array('Listing.id = '. $content_row->id)));

        # Override global configuration
        isset($listing['ListingType']) and $this->Config->override($listing['ListingType']['config']);
        
        // Override CMS breadcrumbs
        if($this->Config->breadcrumb_detail_override)
        {
            App::import('Helper','routes');
            $Routes = RegisterClass::getInstance('RoutesHelper');
            $Routes->Config = $this->Config;            
            $this->Config->dir_show_breadcrumb = false; // Disable the JReviews breadcrumb because there's no need two breadcrumbs on the page
            if($this->Config->breadcrumb_detail_directory)
            {
                $crumbs[] = array('name'=>$listing['Directory']['title'],'link'=>$Routes->directory($listing,array('return_url'=>true)));
            }
            if($this->Config->breadcrumb_detail_section)
            {
               $crumbs[] = array('name'=>$listing['Section']['title'],'link'=>$Routes->section($listing,$listing['Section'],array('return_url'=>true))); 
            }
            if($this->Config->breadcrumb_detail_category)
            {
                $crumbs[] = array('name'=>$listing['Category']['title'],'link'=>$Routes->category($listing,$listing['Section'],$listing['Category'],array('return_url'=>true)));
            }
            $crumbs[] = array('name'=>$listing['Listing']['title'],'link'=>'');
        }
                
        # Get cached vesion
        if($this->_user->id === 0) 
        {    
            $page = $this->cached($this->here . 'plugin');
            if($page) {
                $content_row->text = $page;            
                return array('row'=>$content_row,'params'=>$content_params,'listing'=>$listing,'crumbs'=>$crumbs);
            }
        }

        $this->owner_id = $listing['Listing']['user_id']; // Used in AssetsHelper
          
		// Check if the listing has any html tags, and if it does, then strip the double /r/r added by J1.5, otherwise it is
		// required for proper spacing of summary and description fields
		if(preg_match('/(<\w+)(\s*[^>]*)(>)/',$content_row->text)) {
			$listing['Listing']['text'] = str_replace("\r",'',$content_row->text); // Elimites double break between summary and description 
		} else {
			$listing['Listing']['text'] = $content_row->text; 
		}

        $regex = '/{mosimage\s*.*?}/i';
		$listing['Listing']['text'] = preg_replace( $regex, '', $listing['Listing']['text'] );

		# Get editor review data
		if ($this->Config->author_review) 
		{
			$fields = array(
				'Criteria.id AS `Criteria.criteria_id`',
				'Criteria.criteria AS `Criteria.criteria`',
				'Criteria.state AS `Criteria.state`',
				'Criteria.tooltips AS `Criteria.tooltips`',
				'Criteria.weights AS `Criteria.weights`'			
			);
			
//			$joins = $this->Listing->joinsReviews;
						
			$conditions = array(
				'Review.pid = '. $listing['Listing']['listing_id'],
				'Review.author = 1',
				'Review.published = 1'
			);
			
			$queryData = array(
				'fields'=>$fields,
				'conditions'=>$conditions,
//				'joins'=>$joins,
				'offset'=>0,
				'limit'=>$this->Config->editor_limit,
				'order'=>array($this->Review->processSorting())				
			);
			
			$editor_review = $this->Review->findAll($queryData);

			$editor_review_count = $this->Review->findCount($queryData);
			
			if ( $editor_review_count <= 1 && $this->Config->author_review == 1 )
			{
                // used for the separate display routine when we are in single-editor-review mode, and also for backwards compat with older templates
				$editor_review = array_shift($editor_review); 
			}

			$editor_ratings_summary = array(
				'Rating' => array(
					'average_rating' => $listing['Review']['editor_rating'],
					'ratings' => explode(',', $listing['Review']['editor_criteria_rating']),
					'criteria_rating_count' => explode(',', $listing['Review']['editor_criteria_rating_count'])
				),
				'Criteria' => $listing['Criteria'],
				'summary' => 1
			);
		}

		# Ger user review data
		if ($this->Config->user_reviews) 
		{
			$fields = array(
                'Review.owner_reply_approved As `Review.owner_reply_approved`',            
                'Review.owner_reply_text As `Review.owner_reply_text`',
				'Criteria.id AS `Criteria.criteria_id`',
				'Criteria.criteria AS `Criteria.criteria`',
				'Criteria.state AS `Criteria.state`',
				'Criteria.tooltips AS `Criteria.tooltips`',
				'Criteria.weights AS `Criteria.weights`'			
			);
			
//			$joins = $this->Listing->joinsReviews;
									
			$conditions = array(
				'Review.pid= '. $listing['Listing']['listing_id'],
				'Review.author = 0',
				'Review.published = 1',
				'Review.mode = \'com_content\'',
				'JreviewsCategory.`option` = \'com_content\''
			);

			$queryData = array
			(	
				'fields'=>$fields,
				'conditions'=>$conditions,
//				'joins'=>$joins,
				'offset'=>0,
				'limit'=>$this->Config->user_limit,
				'order'=>array($this->Review->processSorting($this->Config->user_review_order))				
			);

			$reviews = $this->Review->findAll($queryData);

			$review_count = $this->Review->findCount($queryData);

			$ratings_summary = array(
				'Rating' => array(
					'average_rating' => $listing['Review']['user_rating'],
					'ratings' => explode(',', $listing['Review']['user_criteria_rating']),
					'criteria_rating_count' => explode(',', $listing['Review']['user_criteria_rating_count']) 
				),
				'Criteria' => $listing['Criteria'],
				'summary' => 1
			);
		}

		# Get custom fields for review form if form is shown on page
		$review_fields = $this->review_fields = $this->Field->getFieldsArrayNew($listing['Criteria']['criteria_id'], 'review');

		$security_code = '';

		if($this->Access->showCaptcha) {

			$captcha = $this->Captcha->displayCode();

			$security_code = $captcha['image'];
		}

		# Initialize review array and set Criteria and extension keys
		$review = $this->Review->init();
		$review['Criteria'] = $listing['Criteria'];
		$review['Review']['extension'] = $listing['Listing']['extension'];

        # check for duplicate reviews   
        $is_jr_editor = $this->Access->isJreviewsEditor($this->_user->id);
        $this->_user->duplicate_review = false;
        
        // It's a guest so we only care about checking the IP address if this feature is not disabled and
        // server is not localhost
        if(!$this->_user->id)
        {
            if(!$this->Config->review_ipcheck_disable && $this->ipaddress != '127.0.0.1')
            {
                // Do the ip address check everywhere except in localhost
               $this->_user->duplicate_review = (bool) $this->Review->findCount(array('conditions'=>array(
                    'Review.pid = '.$content_row->id,
                    "Review.ipaddress = '{$this->ipaddress}'",
                    "Review.mode = 'com_content'",
                    "Review.author = 0",
                    "Review.published >= 0" 
                )));        
            }
        } 
        elseif( 
            (!$is_jr_editor && !$this->Config->user_multiple_reviews)  // registered user and one review per user allowed when multiple reviews is disabled
            ||
            ($is_jr_editor && $this->Config->author_review == 2) // editor and one review per editor allowed when multiple editor reviews is enabled
        ) 
        {
            $this->_user->duplicate_review = (bool) $this->Review->findCount(array('conditions'=>array(
                'Review.pid = '.$content_row->id,
                "(Review.userid = {$this->_user->id}" . 
                    (  
                        $this->ipaddress != '127.0.0.1' && !$this->Config->review_ipcheck_disable && !$is_jr_editor //&& (!$is_jr_editor || !$this->Config->review_ipcheck_disable)
                    ? 
                        " OR Review.ipaddress = '{$this->ipaddress}') "
                    : 
                        ')' 
                    ),
                "Review.mode = 'com_content'",
                "Review.published >= 0", 
                ($this->Config->author_review == 0 ? "Review.author = 0" : "Review.author >= 0") 
            )));
        }
        
        /** HTGMOD - #1490 **/
   		if (isset($listing['Field']['pairs']['jr_brufoss']) && 
   		          $listing['Field']['pairs']['jr_brufoss']['value'][0] == 1) {
   			$this->viewSuffix = '_brufoss';
   		}
   		/** END HTGMOD **/
        
  	    $this->set(array(
				'extension'=>'com_content',
				'Access'=>$this->Access,
				'User'=>$this->_user,
				'listing'=>$listing,
				'editor_review'=>$editor_review,
				'reviews'=>$reviews,
				'ratings_summary'=>$ratings_summary,
				'editor_ratings_summary'=>$editor_ratings_summary, 
				'review_count'=>$review_count,
				'editor_review_count'=>$editor_review_count, 
				'review_fields'=>$review_fields,
				'review'=>$review,
				'captcha'=>$security_code
			)
		);
		
		$content_row->text = $this->render('listings','detail'); 
		# Save cached version		
		if($this->_user->id ===0) {	
			$this->cacheView('listings','detail',$this->here . 'plugin', $content_row->text);
		}
		return array('row'=>$content_row,'params'=>$content_params,'listing'=>$listing,'crumbs'=>$crumbs);
	}

	function com_content_blog($passedArgs)
	{                
		$this->autoLayout = true;
		$this->layout = 'cmsblog';

		$content_row = $passedArgs['row'];
		$content_params = $passedArgs['params'];	
		
//		return array('row'=>$content_row,'params'=>$content_params);
							
		// Check if item category is configured for jreviews
		if(!$this->Category->isJreviewsCategory($content_row->catid)) {
			return array('row'=>$content_row,'params'=>$content_params);			
		} 
		
		# Override content page parameter settings
//		prx($content_params);
//		$content_params->set('show_title',0);
//		$content_params->set('show_category',0);
//		$content_params->set('show_section',0);
//		$content_params->set('page_title','');
//		$content_params->set('show_hits',0);			

		$content_params->set('show_author',0);
		$content_params->set('author',0); // J1.0.x & Mambo 4.6.x
		
		$content_params->set('show_create_date',0);
		$content_params->set('createdate',0); // J1.0.x & Mambo 4.6.x
		
		$content_params->set('show_vote',0);
		$content_params->set('rating',0); // J1.0.x & Mambo 4.6.x

		$content_params->set('show_modify_date',0);
		$content_params->set('modifydate',0); // J1.0.x & Mambo 4.6.x
		
		
		# Get listing and review summary data
		$fields = array(
			'Criteria.criteria AS `Criteria.criteria`',
			'Criteria.tooltips AS `Criteria.tooltips`',
			'Criteria.weights AS `Criteria.weights`'
		);
		$listing = $this->Listing->findRow(array('fields'=>$fields,'conditions'=>array('Listing.id = '. $content_row->id)));

		$listing['Listing']['text'] = $content_row->text;

		$regex = '/{mosimage\s*.*?}/i';
		$listing['Listing']['text'] = preg_replace( $regex, '', $listing['Listing']['text'] );
                
		$this->set(array(
				'Access'=>$this->Access,
				'User'=>$this->_user,
				'listing'=>$listing
		));
		
		$content_row->text = $this->render('listings','cmsblog');
								
		return array('row'=>$content_row,'params'=>$content_params);
		
	}
		
	function __getContentTmplSuffix($setup) {
		if ($setup->cat_suffix) {
			$tmpl_suffix = $setup->cat_suffix;
		} elseif ($setup->sec_suffix) {
			$tmpl_suffix = $setup->sec_suffix;
		} else {
			$tmpl_suffix = '';
		}
		return $tmpl_suffix;
	}	
}
