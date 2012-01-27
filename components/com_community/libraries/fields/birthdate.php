<?php
/**
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.utilities.date');

require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php' );
require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'fields' . DS.'date.php');

class CFieldsBirthdate extends CFieldsDate
{
	function getFieldData( $field )
	{
		$value = $field['value'];
		
		if( empty( $value ) )
			return $value;
		
		$params	= new JParameter($field['params']);
		$format = $params->get('display');
		
		if(! class_exists('CFactory'))
		{
			require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php' );
		}
		
		$ret = '';
		
		if ($format == 'age')
		{
			$ret = floor((time() - strtotime($value))/(60*60*24*365.2425));
		} else
		{
			require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'models' . DS . 'profile.php' );
			$model	= CFactory::getModel( 'profile' );				
			$ret = $model->formatDate($value); 
		}
		
		return $ret;
	}
}