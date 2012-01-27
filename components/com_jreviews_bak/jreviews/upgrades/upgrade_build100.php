<?php
defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

// Move existing review votes to new home
$query = "
    SELECT 
        reviewid, sum(yes)+sum(no) AS vote_total, sum(yes) AS vote_helpful 
    FROM 
        #__jreviews_votes
    GROUP BY 
        reviewid        
";

$this->_db->setQuery($query);

if($rows = $this->_db->loadObjectList())
{
    foreach ( $rows as $row )
    {
        $query = "
            UPDATE 
                #__jreviews_comments
            SET 
                vote_total = {$row->vote_total}, vote_helpful = {$row->vote_helpful}
            WHERE 
                id = {$row->reviewid}
        ";
        
        $this->_db->setQuery($query);
        $this->_db->query();
    }                         
}

// Now create the new votes table
$query = "DROP TABLE IF EXISTS `#__jreviews_votes`";
$this->_db->setQuery($query);
$this->_db->query();

$query ="CREATE TABLE `#__jreviews_votes` (
  `vote_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vote_yes` int(11) NOT NULL DEFAULT '0',
  `vote_no` int(11) NOT NULL DEFAULT '0',
  `ipaddress` TINYTEXT NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `user_id` (`user_id`),
  KEY `review_id` (`review_id`)
) ENGINE=MyISAM;";

$this->_db->setQuery($query);
$this->_db->query();

$query = "DROP TABLE IF EXISTS `#__jreviews_votes_tmp`";
$this->_db->setQuery($query);
$this->_db->query();
