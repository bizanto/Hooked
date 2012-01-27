<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgSystemApps extends JPlugin
{
	function plgSystemApps(& $subject, $config)
	{
		
		
		parent::__construct($subject, $config);
	}


	function onAfterInitialise()
	{
		
		$device = JRequest::getVar('device', '');
		// only need to set if we are actually viewing from a device
		if ($device != '') { 
			JFactory::getSession()->set('device', $device);
		}
		
		if ($_SERVER['REMOTE_ADDR'] == '174.111.57.151' && JFactory::getApplication()->isSite())
		{
			$request = JRequest::get('request');
			//if ($request['option'] == 'community')
			//{
				$this->debug_obj($_REQUEST);
			//}
		}
		
		return true;
	}
	
	function onAfterDispatch()
	{
		if ($_SERVER['REMOTE_ADDR'] == '174.111.57.151')
		{
			$request = JRequest::get('request');
			if ($request['option'] == 'community')
			{
				//$this->debug_obj($_REQUEST);
			}
		}
	}
	
	function onLoginFailure($response) 
	{
		global $mainframe;
		
		if (JRequest::getVar('device') == 'ios') {
			$return = 'index.php?option=com_user&view=login&Itemid=231&device=ios';
			$mainframe->redirect($return);
		}
	}
	
	public function debug_obj($object, $file = null)
	{
		ob_start();
		var_dump($object);
		$string = ob_get_contents();
		ob_end_clean();
		$this->debug($string, $file);
	}
	
	public function debug($string, $file='log.txt') {
		if ($file == null)
		{
			$file = 'log.txt';
		}
	
		$file = dirname(__FILE__).'/'.$file;
		jimport('joomla.filesystem.file');
		
		$buffer = null;
		if (JFile::exists($file))
		{
			$contents = JFile::read($file);
			$buffer .= $contents."\n\n";
		}
		
		$buffer .= $string;
		
		JFile::write($file, $buffer);
		
		
		/*
		$m = JFactory::getMailer();
		$m->addRecipient('brian@edgewebworks.com');
		$m->setSubject('API Test');
		$m->setBody($string);
		$m->send();
		*/
	}

	
}
