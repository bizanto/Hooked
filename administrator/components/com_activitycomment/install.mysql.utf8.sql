CREATE TABLE IF NOT EXISTS `#__activitylikes` (
  `id` int(11) NOT NULL auto_increment,
  `activityid` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `activityid` (`activityid`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__activity_subscribe` (
  `id` int(11) NOT NULL auto_increment,
  `activity_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `activity_id` (`activity_id`),
  KEY `user_id` (`user_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;