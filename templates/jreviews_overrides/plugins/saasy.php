<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2006-2010 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
 **/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class SaasyComponent extends S2Component {

	var $plugin_order = 102;

	var $name = 'saasy';

	var $published = true;

	function startup(&$controller)
	{
		$this->inAdmin = defined('MVC_FRAMEWORK_ADMIN');

		App::import('Helper',array('routes','html'),'jreviews');
		$this->Routes = RegisterClass::getInstance('RoutesHelper');
		$this->Html = RegisterClass::getInstance('HtmlHelper');
		$this->c = & $controller;
	}

	function plgAfterSave(&$model)
	{
		//If there is a new listing being created
		if($model->name == "Listing" && $model->isNew)
		{
			//get the user
			$user =& JFactory::getUser();
			
			//If listing is in the business section
			if($model->data['Listing']['sectionid'] == 3)
			{			
				//Store listing ID in cookie
				$listingID = $model->data['Listing']['id'];
				setcookie("createdListingID",$listingID,time()+(24*60*60),"/");
				
				//redirect to saasy component - created listing page
				echo '<script type="text/javascript">window.parent.location="/index.php?option=com_saasy&task=details&createdListing=1&Itemid=436";</script>';
			}
		}
	}
}
