<?php 
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.controller');

class RelateController extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);
	}
	
	function display()
	{	
		if (JRequest::getVar('view', '') == '') { 
			JRequest::setVar('view', 'edit');
		}

		parent::display();
	}
	
	function ajaxEditRelations($listing_id, $cat_id = '', $single_select = 0)
	{
		$objResponse = new JAXResponse();

		if (is_numeric($listing_id)) JRequest::setVar('listing_id', $listing_id);
		if ($cat_id) JRequest::setVar('cat', $cat_id);
		if ($single_select) JRequest::setVar('ss', $single_select);
		JRequest::setVar('view', 'edit');
		JFactory::getDocument()->setType('html');

		ob_start();
		$this->display();	
		$html = ob_get_contents();
		ob_end_clean();

		$objResponse->addAssign('cWindowContent', 'innerHTML', $html);
		$objResponse->addScriptCall('cWindowResize', 395);
		$objResponse->addScriptCall('_initEditRelations', $listing_id, $cat_id, $single_select);	

		return $objResponse->sendResponse();
	}
	
	function listings() 
	{
		$id = JRequest::getInt('id');
		$cat_id = JRequest::getVar('cat');
		
		$searchword = JRequest::getVar('searchword'); 		
 		$start = JRequest::getInt('start', 0);
 		
		$model = $this->getModel('edit');
		
		$listings = $model->getListings($id, $cat_id, $searchword, $start);
		echo json_encode($listings);
		exit();
	}
	
	// add/remove relations for a content id
	// todo: should check permissions before adding relations 
	function relate()
	{
		$listing_id = JRequest::getInt('id');
		
		$addIds = JRequest::getVar('add', array());
		$remIds = JRequest::getVar('rem', array());
		
		$cAdd = 0; $cDel = 0;

		$model =& $this->getModel('relate');

		foreach ($addIds as $type => $ids) {
			$relatefunc = "add_$type";
			if (method_exists($model, $relatefunc)) {
				$cAdd += $model->$relatefunc($listing_id, $ids);
			}
		}

		foreach ($remIds as $type => $ids) {
			$relatefunc = "remove_$type";
			if (method_exists($model, $relatefunc)) {
				$cDel += $model->$relatefunc($listing_id, $ids);
			}
		}

		echo "$cAdd rows added, $cDel rows deleted";
		exit();
	}

	// save content id in session so it can later be related to a newly created listing
	function saveId()
	{
		$listing_id = JRequest::getInt('id');
		
		$session =& JFactory::getSession();

		$session->set('relate_id', $listing_id);
		exit();
	}
}
