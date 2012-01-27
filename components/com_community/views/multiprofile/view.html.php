<?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');
jimport ( 'joomla.application.component.view' );

class CommunityViewMultiprofile extends CommunityView
{
	function _addSubmenu()
	{
        $config		= CFactory::getConfig();

		$this->addSubmenuItem ( 'index.php?option=com_community&view=profile&task=uploadAvatar', JText::_('CC EDIT AVATAR') );

		if($config->get('enableprofilevideo'))
		{
			$this->addSubmenuItem ( 'index.php?option=com_community&view=profile&task=linkVideo', JText::_('CC EDIT PROFILE VIDEO') );
		}
                
		$this->addSubmenuItem ( 'index.php?option=com_community&view=profile&task=edit', JText::_('CC EDIT PROFILE') );
		$this->addSubmenuItem ( 'index.php?option=com_community&view=profile&task=editDetails', JText::_('CC EDIT DETAILS') );
		$this->addSubmenuItem ( 'index.php?option=com_community&view=profile&task=privacy', JText::_('CC EDIT PRIVACY') );
		$this->addSubmenuItem ( 'index.php?option=com_community&view=profile&task=preferences', JText::_('CC EDIT PREFERENCES') );
		
		if( $config->get('profile_deletion') )
		{
			$this->addSubmenuItem ( 'index.php?option=com_community&view=profile&task=deleteProfile', JText::_('CC DELETE PROFILE'), '', SUBMENU_RIGHT );
		}
	}
	
	public function display()
	{
		$this->changeProfile();
	}
	
	/**
	 * Allows user to change their profile type
	 **/	 	 	
	public function changeProfile()
	{
		$mainframe	=& JFactory::getApplication();
		$document	=& JFactory::getDocument();
		$my			= CFactory::getUser();
		
		$document->setTitle( JText::_('CC MULTIPROFILE CHANGE TYPE') );
		$this->addPathway( JText::_('CC PROFILE') , CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id ) );
		$this->addPathway( JText::_('CC MULTIPROFILE CHANGE TYPE') );
		$model	= CFactory::getModel( 'Profile' );
		$tmp	= $model->getProfileTypes();
		$profileTypes	= array();
		
		$showNotice		= false;
		
		foreach( $tmp as $profile )
		{
			$table	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
			$table->load( $profile->id );

			if( $table->approvals )
				$showNotice	= true;
				
			$profileTypes[]	= $table;
		}
		
		$tmpl		= new CTemplate();
		$tmpl->set( 'showNotice'	, $showNotice );
		$tmpl->set( 'profileTypes' 	, $profileTypes );
		$tmpl->set( 'default'		, $my->getProfileType() );
		$tmpl->set( 'message'		, JText::_('CC MULTIPROFILE SWITCH INFO') );
		echo $tmpl->fetch( 'register.profiletype' );
	}
	
	/**
	 * Once a user changed their profile, request them to update their profile
	 **/	 	
	public function updateProfile()
	{
		$profileType	= JRequest::getVar( 'profileType' , '' );
		$document		= JFactory::getDocument();
		$document->setTitle( JText::_('CC MULTIPROFILE UPDATE') );
		$my				= CFactory::getUser();
		
		$this->addPathway( JText::_('CC PROFILE') , CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id ) );
		$this->addPathway( JText::_('CC MULTIPROFILE CHANGE TYPE') ,  CRoute::_('index.php?option=com_community&view=multiprofile&task=changeprofile' ) );
		$this->addPathway( JText::_('CC MULTIPROFILE UPDATE') );
		
		$model 			= CFactory::getModel('profile');
		$profileType	= JRequest::getVar( 'profileType' , 0 );
		
		// Get all published custom field for profile
		$filter		= array('published'=>'1', 'registration' => '1' );		
//		$fields		=& $model->getAllFields( $filter , $profileType );
		$result		= $model->getEditableProfile( $my->id , $profileType );

		$empty_html = array();
		$post = JRequest::get('post');
						
		// Bind result from previous post into the field object
		if(! empty($post)){
		
			foreach($fields as $group)
			{
			    $field = $group->fields;
			    for($i = 0; $i <count($field); $i++)
				{
	 				$fieldid    = $field[$i]->id;
	 				$fieldType  = $field[$i]->type;
	 				
					if(!empty($post['field'.$fieldid]))
					{
						if(is_array($post['field'.$fieldid]))
						{
						   if($fieldType != 'date')
						   {
						        $values = $post['field'.$fieldid];
						        $value  = '';
								foreach($values as $listValue)
								{
									$value	.= $listValue . ',';
								}
						        $field[$i]->value = $value;
						   }
						   else 
						   {
						       $field[$i]->value = $post['field'.$fieldid];
						   }
						} 
						else 
						{
						    $field[$i]->value = $post['field'.$fieldid];						
						}
					}
                }
			}
			
		} 
		
		$config		=& CFactory::getConfig();
		
		$js	= 'assets/validate-1.5'.(( $config->getBool('usepackedjavascript') ) ? '.pack.js' : '.js');
        CAssets::attach($js, 'js');

		$profileType	= JRequest::getVar( 'profileType' , 0 , 'GET' );
		
		CFactory::load( 'libraries' , 'profile' );
		$tmpl	= new CTemplate();
		$tmpl->set( 'fields' , $result['fields'] );
		$tmpl->set( 'profileType' , $profileType );
		
		echo $tmpl->fetch( 'multiprofile.update' );
	}
	
	/**
	 * Displays message for the user when their profile is updated.
	 **/
	public function profileUpdated()
	{
		$document		=& JFactory::getDocument();
		$profileType	= JRequest::getVar( 'profileType' , COMMUNITY_DEFAULT_PROFILE );
		$multiprofile	=& JTable::getInstance( 'Multiprofile' , 'CTable' );
		$multiprofile->load( $profileType );
		CFactory::load( 'helper' , 'owner' );
		
		$tmpl			= new CTemplate();
		$tmpl->set( 'multiprofile' , $multiprofile );
		$tmpl->set( 'isCommunityAdmin' , COwnerHelper::isCommunityAdmin() );
		echo $tmpl->fetch( 'multiprofile.message' );		
	}
}