<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );
		
class JreviewsHelper extends MyHelper
{
	var $helpers = array('html','form','time');
    	
	function __construct()
    {
		parent::__construct();
		App::import('Helper','community');
		$this->Community = &RegisterClass::getInstance('CommunityHelper');			
    }
    
    function orderingOptions()
    {
        $order_options_array = array (
            'featured'        =>__t("Featured",true),
            'alpha'            =>__t("Title",true),
//            'alias'            =>__t("Alias",true),
//            'ralpha'        =>__t("Title DESC",true),
            'rdate'            =>__t("Most recent",true),
//            'date'            =>__t("Oldest",true),
            'rhits'            =>__t("Most popular",true),
//            'hits'            =>__t("Least popular",true),
            'rating'        =>__t("Highest user rating",true),
            'rrating'        =>__t("Lowest user rating",true),
            'editor_rating'    =>__t("Highest editor rating",true),
            'reditor_rating'=>__t("Lowest editor rating",true),
            'reviews'        =>__t("Most reviews",true),
            'author'        =>__t("Author",true)
        );
        return $order_options_array;
    }
		
	function orderingList($selected, $options, $fields = array(), $return = false)
	{	     
        $options_array = $this->orderingOptions();
        
		if(!empty($options)) {
			foreach($options AS $key) {
				if(isset($options_array[$key])) {
					$orderingList[$key] = $options_array[$key];
				}
			}

		} else {
			$orderingList = $options_array;
		}
		
		if(Configure::read('geomaps.enabled')==true) {
			$orderingList['distance'] = __t("Distance",true);
            if($selected=='') $selected = 'distance';
		}		

		if(!$this->Config->user_reviews) {
			unset($orderingList['reviews']);
			unset($orderingList['rating']);
			unset($orderingList['rrating']);
		}

		if(!$this->Config->author_review) {
			unset($orderingList['editor_rating']);
			unset($orderingList['reditor_rating']);
		}		
                  
		if(!empty($fields))
		{
			foreach($fields AS $field)
			{
                if($this->Access->in_groups($field['access'])) {
                    $orderingList[$field['value']] = $field['text'] . ' ' . __t("ASC",true);
                    $orderingList['r' . $field['value']] = $field['text'] . ' ' .  __t("DESC",true);
                }                
			}
		}
		
		if($return) {
			return $orderingList;
		}
		
		$attributes = array(
			'size'=>'1',
			'onchange'=>"window.location=this.value;return false;"
		);

		return $this->generateFormSelect($orderingList,$selected,$attributes);
	}
	
	function orderingListReviews($selected, $options = array(), $params = false) {
				
		$options_array = array(
			'rdate'			=>__t("Most recent",true),
			'date'			=>__t("Oldest",true),
			'rating'		=>__t("Highest user rating",true),
			'rrating'		=>__t("Lowest user rating",true),
			'helpful'		=>__t("Most helpful",true),
			'rhelpful'		=>__t("Least helpful",true)
		);

		if(!empty($options)) {
			foreach($options AS $key) {
				if(isset($options_array[$key])) {
					$orderingList[$key] = $options_array[$key];
				}
			}

		} else {
			$orderingList = $options_array;
		}
			
        if(Sanitize::getBool($params,'return')) return $orderingList;
                    	
		$attributes = array(
			'size'=>'1',
			'onchange'=>"window.location=this.value;return false;"
		);
						
		return $this->generateFormSelect($orderingList,$selected,$attributes);
	}
    
    function orderingListPosts($selected, $options = array()) {
                
        $options_array = array(
            'date'            =>__t("Oldest",true),
            'rdate'            =>__t("Most recent",true),
//            'helpful'        =>__t("Most helpful",true),
//            'rhelpful'        =>__t("Least helpful",true)
        );

        if(!empty($options)) {
            foreach($options AS $key) {
                if(isset($options_array[$key])) {
                    $orderingList[$key] = $options_array[$key];
                }
            }

        } else {
            $orderingList = $options_array;
        }
                
        $attributes = array(
            'size'=>'1',
            'onchange'=>"window.location=this.value;return false;"
        );
                        
        return $this->generateFormSelect($orderingList,$selected,$attributes);
    }    
	
	function generateFormSelect($orderingList,$selected,$attributes) {
						
		# Construct new route
		$new_route = cmsFramework::constructRoute($this->passedArgs,array('order','page')); 
		
		$selectList = array();

		foreach($orderingList AS $value=>$text) {
			$selectList[] = array('value'=>cmsFramework::route($new_route . '/order' . _PARAM_CHAR . $value),'text'=>$text);	
		}

		$selected = cmsFramework::route($new_route . '/order' . _PARAM_CHAR . $selected);

		return $this->Form->select('order',$selectList,$selected,$attributes);		
	}
	
	function newIndicator($days, $date) {
		return $this->Time->wasWithinLast($days . ' days', $date);
	}
	
	function userRank($rank) {
		
		switch ($rank) {
			 case ($rank==1): $toprank = __t("#1 Reviewer",true); break;
			 case ($rank<=10 && $rank>0): $toprank = __t("Top 10 Reviewer",true); break;
			 case ($rank<=50 && $rank>10): $toprank = __t("Top 50 Reviewer",true); break;
			 case ($rank<=100 && $rank>50): $toprank = __t("Top 100 Reviewer",true); break;
			 case ($rank<=500 && $rank>100): $toprank = __t("Top 500 Reviewer",true); break;
			 case ($rank<=1000 && $rank>500): $toprank = __t("Top 1000 Reviewer",true); break;
			 default: $toprank = '';
		}
		
		return $toprank;
		
	}
		
    /**
    * This method was moved to the community.php helper and should no longer be used in this one
    * It's kept here for compatibility with older theme files.
    */        
	function screenName(&$entry) {

		$screenName = $this->Config->name_choice == 'realname' ? $entry['User']['name'] : $entry['User']['username'];

		if(isset($entry['Community']) && is_array($entry['Community']) && $entry['User']['user_id'] > 0) {
			return $this->Community->profileLink($screenName,$entry['User']['user_id'],$entry['Community']['menu_id']);
		} elseif(isset($entry['CommunityBuilder']) && is_array($entry['CommunityBuilder']) && $entry['User']['user_id'] > 0) {
			return $this->CommunityBuilder->profileLink($screenName,$entry['User']['user_id'],$entry['CommunityBuilder']['menu_id']);
		} else {
			return $screenName;
		}
	}
	
}