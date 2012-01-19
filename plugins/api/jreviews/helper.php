<?php

class JReviewsAPIHelper {

	function setup()
	{
		define('S2_ROOT', JPATH_SITE.'/components/com_s2framework');
		require_once(JPATH_SITE.'/components/com_jreviews/jreviews/framework.php');
	}

}