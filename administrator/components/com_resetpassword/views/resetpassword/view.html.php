<?php
/*
 * Created on Jan 28, 2011
 *
 */
 
 defined('_JEXEC') or die( 'Restricted access' );

 jimport( 'joomla.application.component.view');
 
 class ResetPasswordViewResetPassword extends JView {
 	
 	
 	public function display($tmpl = null ) {
 		JToolBarHelper::title( JText::_( 'Reset Password' ), 'generic.png' );
 		
 		
 		$data = $this->get('Data') ;
 		$this->assignRef('items' , $data ) ;
 		
 		$total = $this->get('Total') ;
 		$this->assignRef('total' , $total) ;
 		
 		$pagination = $this->get('Pagination');
 		$this->assignRef('pagination', $pagination);
 		
 	
 		
 		parent::display($tmpl) ;
 	}
 	
 }
 
?>