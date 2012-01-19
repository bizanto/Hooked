<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgUserApps extends JPlugin
{
	function plgUserApps(& $subject, $config)
	{
		
		parent::__construct($subject, $config);
	}


	function onLoginUser($user, $options)
	{		
		$device = JRequest::getVar('device', '');
		
		if ($_SERVER['REMOTE_ADDR'] == '174.111.57.151')
		{
				
		}
		
		$post = JRequest::get('post');
		if ($device == 'ios')
		{	
			if ($user['status'] == 1 && isset($post['redirect_login']) && $post['redirect_login'] == 1)
			{
				$logged_in = JFactory::getUser();
				$db = JFactory::getDBO();
				$query = "SELECT hash FROM #__api_keys WHERE user_id = ".$db->Quote($logged_in->id);
				$db->setQuery($query);
				$apikey = $db->loadResult();
				
				if (!$apikey)
				{
					jimport('joomla.application.component.model');
					
					JTable::addIncludePath(JPATH_SITE.'/components/com_api/tables');
					JModel::addIncludePath(JPATH_SITE.'/components/com_api/models');
					JLoader::register('ApiModel', JPATH_SITE.'/components/com_api/libraries/model.php');

					$model = JModel::getInstance('Key', 'ApiModel');					
					$data = array('user_id' => $logged_in->id, 'domain' => 'localhost', 'published' => 1);
					$key = $model->save($data);
					$apikey = $key->hash;
				}
				
				//$url = 'index.php?option=com_api&app=community&resource=user&data=1&key='.$apikey;
				$url = 'hooked://'.$apikey;
				//JFactory::getApplication()->redirect($url);
				header("Location: ".$url);
				exit();
			}
			else 
			{	
				JFactory::getApplication()->redirect($_SERVER['HTTP_REFERER'], JText::_('INCORRECT LOGIN'));
				exit();
			}
			
		}
		return true;
	}
	
	function onLogoutUser($user)
	{
		
		return true;
	}
	
}
