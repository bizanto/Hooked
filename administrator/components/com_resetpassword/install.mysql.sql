CREATE TABLE IF NOT EXISTS `#__resetpasswordtoken` (
  `user_id` int(11) NOT NULL,
  `token` varchar(512) NOT NULL,
  `expire` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `#__resetpasswordlog` (
  `user_id` int(11) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;