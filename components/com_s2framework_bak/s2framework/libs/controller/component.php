<?php
/**
 * jReviews - Reviews Extension
 * Copyright (C) 2006 Alejandro Schmeichler
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
**/

defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class S2Component extends S2Object{
	
	function __initModels($models = null, $app = 'jreviews') {

		if(!empty($models)) {
			
			if(!empty($models)) {
	
				App::import('Model',$models,$app);

				foreach($models AS $model) {
									
					$method_name = inflector::camelize($model);
	
					$class_name = $method_name.'Model';
					
					$this->{$method_name} = new $class_name();
	
				}
			}
		}			
	}	
	
}