--
-- Table structure for table `consultants`
--

CREATE TABLE IF NOT EXISTS `consultants` (
  `student_id` int(11) NOT NULL DEFAULT '0',
  `first_name` char(30) NOT NULL DEFAULT '',
  `last_name` char(30) NOT NULL DEFAULT '',
  `full_name` char(60) NOT NULL DEFAULT '',
  `engr_username` char(20) NOT NULL DEFAULT '',
  `account_type` char(20) NOT NULL DEFAULT '',
  `hov_auth` int(11) NOT NULL DEFAULT '0',
  `shifts_temped` int(11) DEFAULT '0',
  PRIMARY KEY (`student_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `consultants`
--

INSERT INTO `consultants` (`student_id`, `first_name`, `last_name`, `full_name`, `engr_username`, `account_type`, `hov_auth`, `shifts_temped`) VALUES
(930599040, 'Mike', 'Harrold', 'Mike Harrold', 'harroldm', 'Administrator', 1, 7);

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE IF NOT EXISTS `email_queue` (
  `emailid` int(11) NOT NULL AUTO_INCREMENT,
  `to` varchar(100) NOT NULL DEFAULT '',
  `header` varchar(100) NOT NULL DEFAULT '',
  `subject` varchar(100) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `sent` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`emailid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `email_queue`
--


-- --------------------------------------------------------

--
-- Table structure for table `labs`
--

CREATE TABLE IF NOT EXISTS `labs` (
  `lab_id` int(11) NOT NULL AUTO_INCREMENT,
  `display_name` varchar(30) NOT NULL DEFAULT '',
  `number_consultants` int(11) NOT NULL DEFAULT '0',
  `display_order` int(11) NOT NULL DEFAULT '0',
  `sunday_setup` int(11) DEFAULT '0',
  `monday_setup` int(11) DEFAULT '0',
  `tuesday_setup` int(11) DEFAULT '0',
  `wednesday_setup` int(11) DEFAULT '0',
  `thursday_setup` int(11) DEFAULT '0',
  `friday_setup` int(11) DEFAULT '0',
  `saturday_setup` int(11) DEFAULT '0',
  `auth_required` int(11) DEFAULT '0',
  PRIMARY KEY (`lab_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `labs`
--

INSERT INTO `labs` (`lab_id`, `display_name`, `number_consultants`, `display_order`, `sunday_setup`, `monday_setup`, `tuesday_setup`, `wednesday_setup`, `thursday_setup`, `friday_setup`, `saturday_setup`, `auth_required`) VALUES
(8, 'WHD', 2, 1, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `logid` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(40) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `hour` int(11) DEFAULT NULL,
  `minute` int(11) DEFAULT NULL,
  `consultant` int(11) DEFAULT NULL,
  `shift_start_time` int(11) DEFAULT NULL,
  `shift_end_time` int(11) DEFAULT NULL,
  `shift_month` int(11) DEFAULT NULL,
  `shift_day` int(11) DEFAULT NULL,
  `shift_year` int(11) DEFAULT NULL,
  `labid` int(11) DEFAULT NULL,
  `email` int(11) DEFAULT NULL,
  `emailTo` varchar(100) DEFAULT NULL,
  `emailHeaders` varchar(100) DEFAULT NULL,
  `emailSubject` varchar(100) DEFAULT NULL,
  `emailBody` text,
  PRIMARY KEY (`logid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=735 ;

--
-- Table structure for table `schedule`
--

CREATE TABLE IF NOT EXISTS `schedule` (
  `schedule_id` int(11) NOT NULL AUTO_INCREMENT,
  `consultant1` int(11) NOT NULL DEFAULT '0',
  `consultant2` int(11) DEFAULT NULL,
  `dayofweek` int(11) NOT NULL DEFAULT '0',
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `lab_id` int(11) NOT NULL DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`schedule_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=135 ;

--
-- Table structure for table `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '',
  `value` varchar(90) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`) VALUES
(1, 'email_from', 'consultants@eecs.oregonstate.edu'),
(2, 'email_to', 'consultants@eecs.oregonstate.edu'),
(3, 'admin_email', 'mike@michaelharrold.com'),
(4, 'developer_email', 'mike@michaelharrold.com'),
(5, 'test_mode', 'No');

-- --------------------------------------------------------

--
-- Table structure for table `tempshifts`
--

CREATE TABLE IF NOT EXISTS `tempshifts` (
  `tempshift_id` int(11) NOT NULL AUTO_INCREMENT,
  `regular_consultant` int(11) NOT NULL DEFAULT '0',
  `taken` int(11) DEFAULT NULL,
  `temp_consultant` int(11) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `month` int(11) NOT NULL DEFAULT '0',
  `day` int(11) NOT NULL DEFAULT '0',
  `year` int(11) NOT NULL DEFAULT '0',
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `lab_id` int(11) NOT NULL DEFAULT '0',
  `reason` text,
  `time_taken` datetime DEFAULT NULL,
  `notified` char(3) DEFAULT NULL,
  PRIMARY KEY (`tempshift_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=229 ;