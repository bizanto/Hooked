<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class CaptchaModel extends MyModel  {
	
	var $useTable = '#__jreviews_captcha AS Captcha';
	var $primaryKey = 'Captcha.captcha_id';
	    	
	function displayCode() {

		App::import('Vendor', 'captcha' . DS . 'captcha_pi');
		
		$vals = array(
						'word'		 => '',
						'img_path'	 => S2_CMS_CACHE,
						'img_url'	 => S2_CMS_CACHE_URL,
						'font_path'	 => 'texb.ttf',
						'img_width'	 => '100',
						'img_height' => 30,
						'expiration' => 3600
					);

		$captcha = create_captcha($vals);
		
		$query = "INSERT INTO #__jreviews_captcha (captcha_time,word,ip_address)"
		. "\n VALUES ('{$captcha['time']}','{$captcha['word']}','".s2GetIpAddress()."')";
		
		$this->_db->setQuery($query);
		
		$this->_db->query();
		
		return $captcha;

	}

	function checkCode($word,$ipaddress)
     {
		// First, delete old captchas
		$expiration = time()-3600; // Two hour limit
		$this->_db->setQuery("DELETE FROM #__jreviews_captcha WHERE captcha_time < ".$expiration);
		$this->_db->query();

		// Then see if a captcha exists:
		$sql = "SELECT COUNT(*) AS count "
		. "\n FROM #__jreviews_captcha "
		. "\n WHERE word = '$word' AND ip_address = '$ipaddress' AND captcha_time > $expiration"
		;
		
		$query = $this->_db->setQuery($sql);
		
		if ($this->_db->loadResult()) {
			return true;
		} else {
			return false;
		}
	}
}