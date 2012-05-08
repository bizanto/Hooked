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

class ApiResourceComments extends ApiResource {
	
	private $wheres	= array();
	
	public function get() {
		$db = JFactory::getDBO();
		
		$listing = JRequest::getVar('listing', null);
		if ($listing)
		{
			$this->wheres[] = $this->splitForQuery($listing, 'c.pid');
		}
		
		if ($id = JRequest::getInt('id', 0))
		{
			$this->wheres[] = 'c.id = '.$id;
		}
		
		$date = JRequest::getInt('date', 0);
		if ($date)
		{
			$date = gmdate("Y-m-d H:i:s", $date);
			$this->wheres[] = "c.created >= ".$db->Quote($date);
		}
	
		$query = $this->getQuery();
		
		$db->setQuery($query);
		$reviews = $db->loadObjectList();
		$this->plugin->setResponse($reviews);
	}

	private function getQuery()
	{
		$where = $this->buildWhere();
		$query = "SELECT c.id, c.pid AS listing_id, c.name, c.username, c.created, c.title, c.comments, c.userid, r.ratings_sum AS rating "
				."FROM #__jreviews_comments AS c "
				."LEFT JOIN #__jreviews_ratings AS r ON r.reviewid = c.id "
				.$where
				." ORDER BY created DESC"
				;
				
		return $query;
	}

	private function buildWhere()
	{
		$this->wheres[] = "c.published = 1";
		
		$where = "WHERE ".implode(" AND ", $this->wheres);
		
		return $where;
	}
	
	private function splitForQuery($string, $field)
	{
		$db =& JFactory::getDBO();
		
		$ids = preg_split('/\s*,\s*/', $string);
		JArrayHelper::toInteger($ids);
		if (count($ids) == 1)
		{
			$where = $field.' = '.$db->Quote($ids[0]);
		} 
		else
		{
			$where = $field.' IN ('.implode(',',$ids).')';
		}
	
		return $where;
	}
	
	public function post()
	{
		$post = JRequest::get('post');
		
		$data = json_decode($post['JSON_object']);
		
		$table = JTable::getInstance('Comment', 'JTable');
		$table->created = gmdate("Y-m-d H:i:s");
		$table->name = $this->plugin->get('user')->name;
		$table->username = $this->plugin->get('user')->username;
		$table->userid = $this->plugin->get('user')->id;
		$table->email = $this->plugin->get('user')->email;
		$table->comments = $data->comments;
		$table->pid = $data->listing_id;
		
		$table->store();
		
		// Create rating record here
		$rating = JTable::getInstance('Rating', 'JTable');
		$rating->reviewid = $table->id;
		$rating->ratings = isset($post['rating']) ? (float)$post['rating'] : 'na';
		$rating->ratings_sum = is_numeric($rating->ratings) ? $rating->ratings : 0;
		$rating->ratings_qty = ($rating->ratings == 'na') ? 0 : 1;
		$rating->store();
		
		require_once(JPATH_SITE.'/components/com_jreviews/jreviews/framework.php');
		require_once(JPATH_SITE.'/components/com_jreviews/jreviews/models/review.php');
		
		$model = new ReviewModel();
		$model->saveListingTotals($data->listing_id, 'com_content');
		
		$this->addStream($table);
		
		if ($table->email) {
			$this->sendEmail($table);
		}
		
		JRequest::setVar('id', $table->id);
		$this->get();
	}

	public function addStream($comment)
	{
		include_once( JPATH_BASE . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php');

		if(!class_exists('ContentHelperRoute')) require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');  

		$db =& JFactory::getDBO();
		$q = "SELECT id, title, catid, sectionid FROM #__content WHERE id = ".$comment->pid;
		$db->setQuery($q);
		$listing = $db->loadObject();

		$listing_uri = ContentHelperRoute::getArticleRoute($listing->id, $listing->catid, $listing->sectionid);
		$listing_link = '<a href="'.$listing_uri.'">'.$listing->title.'</a>';  

		$title = sprintf('{actor} kommenterte %1$s.', $listing_link);

		// remove tags and truncate to 25 words or less
		$text = strip_tags($comment->comments);
		$text = explode(' ', $text, 25);
		if (count($text) == 25) {
			array_pop($text);
			$text = implode(' ', $text).'...';
		}
		else {
			$text = implode(' ', $text);
		}
		fwrite($f, $title.$text);

	
		$content  = '<ul class="cDetailList clrfix">';
		$content .= '<div class="newsfeed-quote">'.$text.'</div>';
		$content .= '</ul>';

		$act = new stdClass();
		$act->cmd      = 'wall.write';
		$act->actor    = $comment->userid;
		$act->target   = 0; // no target
		$act->title    = $title;
		$act->content  = $content;
		$act->app      = 'wall';
		$act->cid      = 0;
		CFactory::load('libraries', 'activities');
		CActivityStream::add($act); 
	}
	
	public function sendEmail($comment)
	{
		$email_template = JPATH_BASE.'/templates/jreviews_overrides/views/themes/geomaps/email_templates/owner_review_notification.thtml';
		
		$listing_id = $comment->pid;
		$db =& JFactory::getDBO();
		$q = "SELECT c.id, c.title, c.catid, c.sectionid, u.email FROM #__content c, #__users u ".
		     "WHERE u.id = c.created_by AND c.id ='".$listing_id."'";
		$db->setQuery($q);
		$listing = $db->loadObject();
		
		$subject = sprintf('Ny omtale: %s', $listing->title);
		
		$is_published = 1;
		$isNew = true;
		$api = true;

		ob_start();
		require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

		require($email_template);
		
		$email_body = ob_get_clean();
				
		$mailer =& JFactory::getMailer();
		
		$config =& JFactory::getConfig();
		$sender = array($config->getValue('config.mailfrom'), $config->getValue('config.fromname'));
		
		$mailer->setSender($sender);
		$mailer->isHTML(true);
		$mailer->addRecipient($listing->email);
		$mailer->setSubject($subject);
		$mailer->setBody($email_body);
		
		return $mailer->Send();
	}
}
class JTableComment extends JTable
{
	var $id			= null;
	var $pid		= null;
	var $mode		= 'com_content';
	var $created 	= null;
	var $userid		= null;
	var $name		= null;
	var $username	= null;
	var $email		= null;
	var $title		= null;
	var $comments	= null;
	var $published	= 1;
	

	function __construct(&$db)
	{	
		parent::__construct('#__jreviews_comments', 'id', $db);
	}

	
}

class JTableRating extends JTable
{
	var $rating_id		= null;
	var $reviewid		= null;
	var $ratings		= null;
	var $ratings_sum 	= null;
	var $ratings_qty	= null;

	function __construct(&$db)
	{	
		parent::__construct('#__jreviews_ratings', 'id', $db);
	}

	
}