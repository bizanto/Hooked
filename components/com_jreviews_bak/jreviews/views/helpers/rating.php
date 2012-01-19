<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class RatingHelper extends MyHelper
{	
	var $no_rating_text = null; // Default no rating output
	var $rating_average_all = 0;
	var $rating_value = 0;
	var $review_count = 0;
	var $tmpl_suffix;
	
	function options($scale, $default = _JR_RATING_OPTIONS, $na=1) 
    {
		$options = array();
		
        if($this->Config->rating_selector == 'select')
        {
            $options = array(''=>$default);
        }
		
		// recall 1 = Required ; 0 = Not Required = allow N/A
		if ($na == 0 )
		{
			$options['na'] = __t('No rating', true);
		}
		
        $inc = !$this->Config->rating_increment ? 1 : $this->Config->rating_increment;
        for($i=$inc;$i<=$scale;$i=$i+$inc) 
        {
            $options[(string)$i] = (string)$i;
        }        
		
		// You can customize the text of the options by commenting the code above and using the one below:
//      $options['na'] = 'N/A';         
//		$options[1] = 'Terrible'; 
//		$options[2] = 'Not so bad';
//		$options[3] = 'Just ok';
//		$options[4] = 'Good';
//		$options[5] = 'Excellent';
		
		return $options;
	}
	
	// Converts numeric ratings into graphical output
	function drawStars($rating, $scale, $graphic, $type) 
	{
		$round_to = $scale > 10 ? 0 : 1;		
		
		$rating_graphic = $graphic ? 'rating_star_' : 'rating_bar_';
		
		$class = $rating_graphic . $type; // builds the class based on graphic and rating type
		
		$ratingPercent = number_format(($rating/$scale)*100,0);

		if ($rating > 0) {
			
			return "<div class=\"$class\"><div style=\"width:{$ratingPercent}%;\">&nbsp;</div></div>";
		
		} elseif ($this->no_rating_text) {

			return $this->no_rating_text;
		} else {

			return "<div class=\"$class\"><div style=\"width:0%;\">&nbsp;</div></div>";
		}
	}
	
	function round($value, $scale) 
	{
		if(is_numeric($value)) {
			$value = ceil($value * 100) / 100; // extra math forces ceil() to work with decimals
		        $round = $scale > 10 ? 0 : 1;
		        return number_format($value,$round);
		} else {
		 	return empty($value) ? '0.0' : '<span class="jr_noRating" title="'.__t('Not rated', true).'">'.__t('N/A', true).'</span>';
		}
	}

	function getRank($userid,$rank,$limit,$Itemid) {

		$pag_start = '';
		$start = floor($rank/$limit)*$limit;
		
		switch ($rank) {
			 case ($rank==1): $user_rank = _JR_RANK_TOP1; break;
			 case ($rank<=10 && $rank>0): $user_rank = _JR_RANK_TOP10; break;
			 case ($rank<=50 && $rank>10): $user_rank = _JR_RANK_TOP50; break;
			 case ($rank<=100 && $rank>50): $user_rank = _JR_RANK_TOP100; break;
			 case ($rank<=500 && $rank>100): $user_rank = _JR_RANK_TOP500; break;
			 case ($rank<=1000 && $rank>500): $user_rank = _JR_RANK_TOP1000; break;
			 default: $user_rank = '';
		}

		if ($start > 1) {
			$pag_start = "&amp;limit=$limit&amp;limitstart=$start";
		}


		if ($user_rank != '') {
			$url = $this->link($user_rank,'index.php?option='.S2Paths::get('jreviews','S2_CMSCOMP').'&amp;task=reviewrank&amp;user='.$userid.$pag_start.'#$userid');
			return $url;
		}
	}	
	
}