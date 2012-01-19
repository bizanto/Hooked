<?php
/**
 * @category	Core
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view' );

class CommunityViewMultiProfile extends JView
{
	/**
	 * The default method that will display the output of this view which is called by
	 * Joomla
	 * 
	 * @param	string template	Template file name
	 **/	 	
	function display( $tpl = null )
	{
		JHTML::_('behavior.tooltip', '.hasTip');
		
		if( $this->getLayout() == 'edit' )
		{
			$this->_displayEditLayout( $tpl );
			return;
		}


		// Set the titlebar text
		JToolBarHelper::title( JText::_('CC MULTIPLE PROFILES'), 'multiprofile' );

		// Add the necessary buttons
		JToolBarHelper::back('Home' , 'index.php?option=com_community');
		JToolBarHelper::divider();
		JToolBarHelper::publishList('publish', JText::_('CC PUBLISH'));
		JToolBarHelper::unpublishList('unpublish', JText::_('CC UNPUBLISH'));
		JToolBarHelper::divider();
		JToolBarHelper::trash('delete', JText::_('CC DELETE'));
		JToolBarHelper::addNew('add', JText::_('CC NEW'));
		
		$profiles	= $this->get( 'MultiProfiles' );
		$pagination	= $this->get( 'Pagination' );
		
 		$this->assignRef( 'profiles'	, $profiles );
		$this->assignRef( 'pagination'	, $pagination );
		parent::display( $tpl );
	}

	function _displayEditLayout( $tpl )
	{
		JToolBarHelper::title( JText::_('CC MULTIPLE PROFILES') , 'multiprofile' );
		
 		// Add the necessary buttons
 		JToolBarHelper::back('Back' , 'index.php?option=com_community&view=multiprofile');
 		JToolBarHelper::divider();
		JToolBarHelper::save();

		$id				= JRequest::getVar( 'id' , '' , 'REQUEST' );
		$multiprofile	=& JTable::getInstance( 'MultiProfile' , 'CTable' );
		$multiprofile->load( $id );

		$profile	= $this->getModel( 'Profiles' );
		$fields		= $profile->getFields();

		$config		= CFactory::getConfig();
		
		$this->assignRef( 'multiprofile', $multiprofile );
		$this->assignRef( 'fields'		, $fields );
		$this->assignRef( 'config'		, $config );
 		
 		parent::display( $tpl );
	}
	
	function getWatermarkLocations()
	{
		$locations	= array(
			JHTML::_('select.option', 'top', 'Top'),
			JHTML::_('select.option', 'right', 'Right'),
			JHTML::_('select.option', 'bottom', 'Bottom'),
			JHTML::_('select.option', 'left', 'Left'),
		);
		return $locations;
	}

	/**
	 * Return the total number of users for specific profile
	 **/	 	
	public function getTotalUsers( $profileId )
	{
		$db		=& JFactory::getDBO();
		$query	= 'SELECT COUNT(1) FROM #__community_users WHERE `profile_id`=' . $db->Quote( $profileId );
		$db->setQuery( $query );
		return $db->loadResult();
	}
	
	/**
	 * Method to get the publish status HTML
	 *
	 * @param	object	Field object
	 * @param	string	Type of the field
	 * @param	string	The ajax task that it should call
	 * @return	string	HTML source
	 **/	 	
	function getPublish( &$row , $type , $ajaxTask )
	{
	
		$imgY	= 'tick.png';
		$imgX	= 'publish_x.png';
		
		$image	= $row->$type ? $imgY : $imgX;
		
		$alt	= $row->$type ? JText::_('CC PUBLISHED') : JText::_('CC UNPUBLISHED');
		
		$href = '<a href="javascript:void(0);" onclick="azcommunity.togglePublish(\'' . $ajaxTask . '\',\'' . $row->id . '\',\'' . $type . '\');">';
		$href  .= '<span><img src="images/' . $image . '" border="0" alt="' . $alt . '" /></span></a>';
		
		return $href;
	}
	
	/**
	 * Method to get the publish status HTML
	 *
	 * @param	object	Field object
	 * @param	string	Type of the field
	 * @param	string	The ajax task that it should call
	 * @return	string	HTML source
	 **/	 	
	function getItemsPublish( $isPublished , $fieldId )
	{
		$imgY	= 'tick.png';
		$imgX	= 'publish_x.png';
		$image	= '';
		
		if( $isPublished )
		{
			$image	= $imgY;
		}
		else
		{
			$image	= $imgX;
		}
		
		$href = '<a href="javascript:void(0);" onclick="azcommunity.toggleMultiProfileChild(' . $fieldId . ');"><img src="images/' . $image . '" border="0" /></a>';
		return $href;
	}
	
	
	/**
	 * Private method to set the toolbar for this view
	 * 
	 * @access private
	 * 
	 * @return null
	 **/	 	 
	function setToolBar()
	{
	}
}