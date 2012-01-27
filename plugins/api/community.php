<?php
/**
 * @package	API
 * @version 1.5
 * @author 	Brian Edgerton
 * @link 	http://www.edgewebworks.com
 * @copyright Copyright (C) 2011 Edge Web Works, LLC. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgAPICommunity extends ApiPlugin {
	
	public function __construct()
	{
		parent::__construct();
		ApiResource::addIncludePath(dirname(__FILE__).'/community');
		JLoader::register('CommunityAPIHelper', dirname(__FILE__).'/community/helper.php');
		CommunityAPIHelper::setup();
		
		$this->setResourceAccess('authenticate', 'public', 'POST');
		
	}
	
	public function dump_obj($obj)
	{
		ob_start();
		var_dump($obj);
		$text = ob_get_contents();
		ob_end_clean();
		$this->debug($text);
	}

	public function debug($string, $file='log.txt') {
		$file = dirname(__FILE__).'/'.$file;
		jimport('joomla.filesystem.file');
		
		$buffer = null;
		if (JFile::exists($file))
		{
			$contents = JFile::read($file);
			$buffer .= $contents."\n\n--------------\n\n";
		}
		
		$buffer .= "Date: ".date("Y-m-d H:i:s")."\n";
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