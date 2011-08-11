-- phpMyAdmin SQL Dump
-- version 3.3.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 26, 2011 at 09:30 PM
-- Server version: 5.5.8
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `limesurvey_cpdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `lime_answers`
--

CREATE TABLE IF NOT EXISTS `lime_answers` (
  `qid` int(11) NOT NULL DEFAULT '0',
  `code` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `answer` text COLLATE utf8_unicode_ci NOT NULL,
  `assessment_value` int(11) NOT NULL DEFAULT '0',
  `sortorder` int(11) NOT NULL,
  `language` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `scale_id` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`qid`,`code`,`language`,`scale_id`),
  KEY `answers_idx2` (`sortorder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `lime_answers`
--

INSERT INTO `lime_answers` (`qid`, `code`, `answer`, `assessment_value`, `sortorder`, `language`, `scale_id`) VALUES
(3, 'A4', 'New answer option', 1, 4, 'ar', 0),
(3, 'A1', 'Some example answer option', 0, 1, 'be', 0),
(3, 'A4', 'New answer option', 1, 4, 'en', 0),
(3, 'A3', 'New answer option', 1, 3, 'ar', 0),
(3, 'A2', 'New answer option', 1, 2, 'ar', 0),
(3, 'A3', 'New answer option', 1, 3, 'en', 0),
(3, 'A1', 'Some example answer option', 0, 1, 'ar', 0),
(3, 'A2', 'New answer option', 1, 2, 'en', 0),
(3, 'A1', 'Some example answer option', 0, 1, 'en', 0),
(3, 'A2', 'New answer option', 1, 2, 'be', 0),
(3, 'A3', 'New answer option', 1, 3, 'be', 0),
(3, 'A4', 'New answer option', 1, 4, 'be', 0);

-- --------------------------------------------------------

--
-- Table structure for table `lime_assessments`
--

CREATE TABLE IF NOT EXISTS `lime_assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL DEFAULT '0',
  `scope` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `gid` int(11) NOT NULL DEFAULT '0',
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `minimum` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `maximum` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  PRIMARY KEY (`id`,`language`),
  KEY `assessments_idx2` (`sid`),
  KEY `assessments_idx3` (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `lime_assessments`
--


-- --------------------------------------------------------

--
-- Table structure for table `lime_conditions`
--

CREATE TABLE IF NOT EXISTS `lime_conditions` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `qid` int(11) NOT NULL DEFAULT '0',
  `scenario` int(11) NOT NULL DEFAULT '1',
  `cqid` int(11) NOT NULL DEFAULT '0',
  `cfieldname` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `method` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`cid`),
  KEY `conditions_idx2` (`qid`),
  KEY `conditions_idx3` (`cqid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `lime_conditions`
--


-- --------------------------------------------------------

--
-- Table structure for table `lime_defaultvalues`
--

CREATE TABLE IF NOT EXISTS `lime_defaultvalues` (
  `qid` int(11) NOT NULL DEFAULT '0',
  `specialtype` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `scale_id` int(11) NOT NULL DEFAULT '0',
  `sqid` int(11) NOT NULL DEFAULT '0',
  `language` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `defaultvalue` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`qid`,`scale_id`,`language`,`specialtype`,`sqid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `lime_defaultvalues`
--


-- --------------------------------------------------------

--
-- Table structure for table `lime_failed_login_attempts`
--

CREATE TABLE IF NOT EXISTS `lime_failed_login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(37) COLLATE utf8_unicode_ci NOT NULL,
  `last_attempt` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `number_attempts` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12 ;

--
-- Dumping data for table `lime_failed_login_attempts`
--


-- --------------------------------------------------------

--
-- Table structure for table `lime_groups`
--

CREATE TABLE IF NOT EXISTS `lime_groups` (
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL DEFAULT '0',
  `group_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `group_order` int(11) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8_unicode_ci,
  `language` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  PRIMARY KEY (`gid`,`language`),
  KEY `groups_idx2` (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `lime_groups`
--

INSERT INTO `lime_groups` (`gid`, `sid`, `group_name`, `group_order`, `description`, `language`) VALUES
(2, 26314, 'qwe', 0, '', 'ar'),
(2, 26314, 'qwe', 0, '', 'en'),
(2, 26314, 'qwe', 0, '', 'be'),
(3, 54494, 'kacx', 0, '', 'zh-Hans'),
(5, 39547, 'abc', 0, '', 'en'),
(5, 39547, 'abc', 0, '', 'sq'),
(5, 39547, 'abc', 0, '', 'be');

-- --------------------------------------------------------

--
-- Table structure for table `lime_labels`
--

CREATE TABLE IF NOT EXISTS `lime_labels` (
  `lid` int(11) NOT NULL DEFAULT '0',
  `code` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `title` text COLLATE utf8_unicode_ci,
  `sortorder` int(11) NOT NULL,
  `assessment_value` int(11) NOT NULL DEFAULT '0',
  `language` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  PRIMARY KEY (`lid`,`sortorder`,`language`),
  KEY `ixcode` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `lime_labels`
--


-- --------------------------------------------------------

--
-- Table structure for table `lime_labelsets`
--

CREATE TABLE IF NOT EXISTS `lime_labelsets` (
  `lid` int(11) NOT NULL AUTO_INCREMENT,
  `label_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `languages` varchar(200) COLLATE utf8_unicode_ci DEFAULT 'en',
  PRIMARY KEY (`lid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `lime_labelsets`
--


-- --------------------------------------------------------

--
-- Table structure for table `lime_participants`
--

CREATE TABLE IF NOT EXISTS `lime_participants` (
  `participant_id` varchar(50) NOT NULL,
  `firstname` varchar(40) NOT NULL,
  `lastname` varchar(40) NOT NULL,
  `email` varchar(80) NOT NULL,
  `language` varchar(2) NOT NULL,
  `blacklisted` varchar(1) NOT NULL,
  `owner_uid` int(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lime_participants`
--

INSERT INTO `lime_participants` (`participant_id`, `firstname`, `lastname`, `email`, `language`, `blacklisted`, `owner_uid`) VALUES
('6313c556-53ba-4f1d-bba1-dfeea3cfaa9f', 'Marcel', 'Minke', 'marcel@limesurvey.org', 'en', 'N', 1),
('d8d00b5f-a6d2-4102-a6d5-397c7686fc46', 'Jason', 'Cleeland ', 'jason@cleeland.org', 'en', 'N', 1),
('a9e509b1-312e-4688-850f-144dd0e544e9', 'Aniessh', 'Sethh', 'aniesshsethh@gmail.com', 'en', 'N', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lime_participant_attribute`
--

CREATE TABLE IF NOT EXISTS `lime_participant_attribute` (
  `participant_id` varchar(50) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `value` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lime_participant_attribute`
--


-- --------------------------------------------------------

--
-- Table structure for table `lime_participant_attribute_names`
--

CREATE TABLE IF NOT EXISTS `lime_participant_attribute_names` (
  `attribute_id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_type` varchar(30) NOT NULL,
  `visible` char(5) NOT NULL,
  PRIMARY KEY (`attribute_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=160 ;

--
-- Dumping data for table `lime_participant_attribute_names`
--

INSERT INTO `lime_participant_attribute_names` (`attribute_id`, `attribute_type`, `visible`) VALUES
(155, 'TB', 'TRUE'),
(154, 'TB', 'TRUE');

-- --------------------------------------------------------

--
-- Table structure for table `lime_participant_attribute_names_lang`
--

CREATE TABLE IF NOT EXISTS `lime_participant_attribute_names_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(11) NOT NULL,
  `attribute_name` varchar(30) NOT NULL,
  `lang` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `lime_participant_attribute_names_lang`
--

INSERT INTO `lime_participant_attribute_names_lang` (`id`, `attribute_id`, `attribute_name`, `lang`) VALUES
(3, 155, 'age', 'en'),
(4, 154, 'sex', 'en');

-- --------------------------------------------------------

--
-- Table structure for table `lime_participant_attribute_values`
--

CREATE TABLE IF NOT EXISTS `lime_participant_attribute_values` (
  `attribute_id` int(11) NOT NULL,
  `value_id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(20) NOT NULL,
  UNIQUE KEY `value_id` (`value_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=28 ;

--
-- Dumping data for table `lime_participant_attribute_values`
--

INSERT INTO `lime_participant_attribute_values` (`attribute_id`, `value_id`, `value`) VALUES
(154, 25, 'female'),
(154, 24, 'male');

-- --------------------------------------------------------

--
-- Table structure for table `lime_participant_shares`
--

CREATE TABLE IF NOT EXISTS `lime_participant_shares` (
  `participant_id` varchar(50) NOT NULL,
  `shared_uid` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  `can_edit` text NOT NULL,
  PRIMARY KEY (`shared_uid`,`participant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lime_participant_shares`
--


-- --------------------------------------------------------

--
-- Table structure for table `lime_questions`
--

CREATE TABLE IF NOT EXISTS `lime_questions` (
  `qid` int(11) NOT NULL AUTO_INCREMENT,
  `parent_qid` int(11) NOT NULL DEFAULT '0',
  `sid` int(11) NOT NULL DEFAULT '0',
  `gid` int(11) NOT NULL DEFAULT '0',
  `type` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'T',
  `title` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `question` text COLLATE utf8_unicode_ci NOT NULL,
  `preg` text COLLATE utf8_unicode_ci,
  `help` text COLLATE utf8_unicode_ci,
  `other` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `mandatory` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `question_order` int(11) NOT NULL,
  `language` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `scale_id` tinyint(4) NOT NULL DEFAULT '0',
  `same_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Saves if user set to use the same default value across languages in default options dialog',
  PRIMARY KEY (`qid`,`language`),
  KEY `questions_idx2` (`sid`),
  KEY `questions_idx3` (`gid`),
  KEY `questions_idx4` (`type`),
  KEY `parent_qid_idx` (`parent_qid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Dumping data for table `lime_questions`
--

INSERT INTO `lime_questions` (`qid`, `parent_qid`, `sid`, `gid`, `type`, `title`, `question`, `preg`, `help`, `other`, `mandatory`, `question_order`, `language`, `scale_id`, `same_default`) VALUES
(3, 0, 26314, 2, 'F', '23', 'sfda<br />', '', '', 'N', 'N', 0, 'be', 0, 0),
(3, 0, 26314, 2, 'F', '23', 'My name is Aniessh Sethh<br />', '', '', 'N', 'N', 0, 'en', 0, 0),
(3, 0, 26314, 2, 'F', '23', 'My Name is Aniessh<br />', '', '', 'N', 'N', 0, 'ar', 0, 0),
(5, 0, 39547, 5, 'T', 'def', '', '', '', 'N', 'N', 0, 'en', 0, 0),
(5, 0, 39547, 5, 'T', 'def', '', '', '', 'N', 'N', 0, 'sq', 0, 0),
(5, 0, 39547, 5, 'T', 'def', '', '', '', 'N', 'N', 0, 'be', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `lime_question_attributes`
--

CREATE TABLE IF NOT EXISTS `lime_question_attributes` (
  `qaid` int(11) NOT NULL AUTO_INCREMENT,
  `qid` int(11) NOT NULL DEFAULT '0',
  `attribute` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`qaid`),
  KEY `question_attributes_idx2` (`qid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=100 ;

--
-- Dumping data for table `lime_question_attributes`
--

INSERT INTO `lime_question_attributes` (`qaid`, `qid`, `attribute`, `value`) VALUES
(76, 3, 'scale_export', '0'),
(75, 3, 'use_dropdown', '0'),
(74, 3, 'random_order', '0'),
(73, 3, 'public_statistics', '0'),
(50, 3, 'page_break', '0'),
(48, 3, 'hidden', '0'),
(72, 3, 'array_filter_exclude', ''),
(71, 3, 'array_filter', ''),
(70, 3, 'answer_width', ''),
(69, 3, 'random_group', ''),
(77, 5, 'display_rows', ''),
(78, 5, 'hidden', '0'),
(79, 5, 'maximum_chars', ''),
(80, 5, 'page_break', '0'),
(81, 5, 'text_input_width', ''),
(82, 5, 'time_limit', ''),
(83, 5, 'time_limit_action', '1'),
(84, 5, 'time_limit_disable_next', '0'),
(85, 5, 'time_limit_disable_prev', '0'),
(86, 5, 'time_limit_countdown_message', ''),
(87, 5, 'time_limit_timer_style', ''),
(88, 5, 'time_limit_message_delay', ''),
(89, 5, 'time_limit_message', ''),
(90, 5, 'time_limit_message_style', ''),
(91, 5, 'time_limit_warning', ''),
(92, 5, 'time_limit_warning_display_time', ''),
(93, 5, 'time_limit_warning_message', ''),
(94, 5, 'time_limit_warning_style', ''),
(95, 5, 'time_limit_warning_2', ''),
(96, 5, 'time_limit_warning_2_display_time', ''),
(97, 5, 'time_limit_warning_2_message', ''),
(98, 5, 'time_limit_warning_2_style', ''),
(99, 5, 'random_group', '');

-- --------------------------------------------------------

--
-- Table structure for table `lime_quota`
--

CREATE TABLE IF NOT EXISTS `lime_quota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `qlimit` int(8) DEFAULT NULL,
  `action` int(2) DEFAULT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `autoload_url` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `quota_idx2` (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `lime_quota`
--

INSERT INTO `lime_quota` (`id`, `sid`, `name`, `qlimit`, `action`, `active`, `autoload_url`) VALUES
(1, 26314, 'sex', 4, 2, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `lime_quota_languagesettings`
--

CREATE TABLE IF NOT EXISTS `lime_quota_languagesettings` (
  `quotals_id` int(11) NOT NULL AUTO_INCREMENT,
  `quotals_quota_id` int(11) NOT NULL DEFAULT '0',
  `quotals_language` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `quotals_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quotals_message` text COLLATE utf8_unicode_ci NOT NULL,
  `quotals_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quotals_urldescrip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`quotals_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `lime_quota_languagesettings`
--

INSERT INTO `lime_quota_languagesettings` (`quotals_id`, `quotals_quota_id`, `quotals_language`, `quotals_name`, `quotals_message`, `quotals_url`, `quotals_urldescrip`) VALUES
(1, 1, 'fi', 'sex', 'Sorry your responses have exceeded a quota on this survey.', '', ''),
(2, 1, 'en', 'sex', 'Sorry your responses have exceeded a quota on this survey.', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `lime_quota_members`
--

CREATE TABLE IF NOT EXISTS `lime_quota_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) DEFAULT NULL,
  `qid` int(11) DEFAULT NULL,
  `quota_id` int(11) DEFAULT NULL,
  `code` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sid` (`sid`,`qid`,`quota_id`,`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `lime_quota_members`
--


-- --------------------------------------------------------

--
-- Table structure for table `lime_saved_control`
--

CREATE TABLE IF NOT EXISTS `lime_saved_control` (
  `scid` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL DEFAULT '0',
  `srid` int(11) NOT NULL DEFAULT '0',
  `identifier` text COLLATE utf8_unicode_ci NOT NULL,
  `access_code` text COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(320) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` text COLLATE utf8_unicode_ci NOT NULL,
  `saved_thisstep` text COLLATE utf8_unicode_ci NOT NULL,
  `status` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `saved_date` datetime NOT NULL,
  `refurl` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`scid`),
  KEY `saved_control_idx2` (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `lime_saved_control`
--


-- --------------------------------------------------------

--
-- Table structure for table `lime_sessions`
--

CREATE TABLE IF NOT EXISTS `lime_sessions` (
  `sesskey` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `expiry` datetime NOT NULL,
  `expireref` varchar(250) COLLATE utf8_unicode_ci DEFAULT '',
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `sessdata` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`sesskey`),
  KEY `sess2_expiry` (`expiry`),
  KEY `sess2_expireref` (`expireref`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `lime_sessions`
--


-- --------------------------------------------------------

--
-- Table structure for table `lime_settings_global`
--

CREATE TABLE IF NOT EXISTS `lime_settings_global` (
  `stg_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `stg_value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`stg_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `lime_settings_global`
--

INSERT INTO `lime_settings_global` (`stg_name`, `stg_value`) VALUES
('DBVersion', '145'),
('SessionName', 'ls19849161677154253162'),
('force_ssl', 'neither'),
('showXquestions', 'choose'),
('showgroupinfo', 'choose'),
('showqnumcode', 'choose'),
('siteadminbounce', 'aniesshsethh@gmail.com'),
('updatecheckperiod', '7'),
('sitename', 'LimeSurvey'),
('defaultlang', 'en'),
('defaulttemplate', 'default'),
('defaulthtmleditormode', 'inline'),
('timeadjust', '+0 hours'),
('usepdfexport', '0'),
('addTitleToLinks', '0'),
('sessionlifetime', '3600'),
('ipInfoDbAPIKey', ''),
('googleMapsAPIKey', ''),
('siteadminemail', 'aniesshsethh@gmail.com'),
('siteadminname', 'Aniessh Sethh'),
('emailmethod', 'smtp'),
('emailsmtphost', 'smtp.gmail.com:465'),
('emailsmtpuser', 'aniesshsethh'),
('emailsmtpssl', 'ssl'),
('emailsmtpdebug', ''),
('maxemails', '50'),
('surveyPreview_require_Auth', '1'),
('filterxsshtml', '1'),
('usercontrolSameGroupPolicy', '1'),
('shownoanswer', '11'),
('repeatheadings', '25'),
('emailsmtppassword', 'summerofcode'),
('bounceaccounthost', 'imap.gmail.com:995'),
('bounceaccounttype', 'IMAP'),
('bounceencryption', 'SSL'),
('bounceaccountuser', 'aniesshsethh'),
('bounceaccountpass', 'summerofcode'),
('userideditable', 'Y'),
('updateavailable', '1'),
('updatelastcheck', '2011-07-26 21:26:58'),
('updatebuild', '10563'),
('updateversion', '1.91+');

-- --------------------------------------------------------

--
-- Table structure for table `lime_surveys`
--

CREATE TABLE IF NOT EXISTS `lime_surveys` (
  `sid` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `admin` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `expires` datetime DEFAULT NULL,
  `startdate` datetime DEFAULT NULL,
  `adminemail` varchar(320) COLLATE utf8_unicode_ci DEFAULT NULL,
  `anonymized` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `faxto` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `format` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `savetimings` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `template` varchar(100) COLLATE utf8_unicode_ci DEFAULT 'default',
  `language` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_languages` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `datestamp` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `usecookie` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `allowregister` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `allowsave` char(1) COLLATE utf8_unicode_ci DEFAULT 'Y',
  `autonumber_start` bigint(11) DEFAULT '0',
  `autoredirect` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `allowprev` char(1) COLLATE utf8_unicode_ci DEFAULT 'Y',
  `printanswers` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `ipaddr` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `refurl` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `datecreated` date DEFAULT NULL,
  `publicstatistics` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `publicgraphs` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `listpublic` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `htmlemail` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `tokenanswerspersistence` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `assessments` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `usecaptcha` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `usetokens` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `bounce_email` varchar(320) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attributedescriptions` text COLLATE utf8_unicode_ci,
  `emailresponseto` text COLLATE utf8_unicode_ci,
  `emailnotificationto` text COLLATE utf8_unicode_ci,
  `tokenlength` tinyint(2) DEFAULT '15',
  `showXquestions` char(1) COLLATE utf8_unicode_ci DEFAULT 'Y',
  `showgroupinfo` char(1) COLLATE utf8_unicode_ci DEFAULT 'B',
  `shownoanswer` char(1) COLLATE utf8_unicode_ci DEFAULT 'Y',
  `showqnumcode` char(1) COLLATE utf8_unicode_ci DEFAULT 'X',
  `bouncetime` bigint(20) DEFAULT NULL,
  `bounceprocessing` varchar(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `bounceaccounttype` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bounceaccounthost` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bounceaccountpass` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bounceaccountencryption` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bounceaccountuser` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `showwelcome` char(1) COLLATE utf8_unicode_ci DEFAULT 'Y',
  `showprogress` char(1) COLLATE utf8_unicode_ci DEFAULT 'Y',
  `allowjumps` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `navigationdelay` tinyint(2) DEFAULT '0',
  `nokeyboard` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  `alloweditaftercompletion` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `lime_surveys`
--


-- --------------------------------------------------------

--
-- Table structure for table `lime_surveys_languagesettings`
--

CREATE TABLE IF NOT EXISTS `lime_surveys_languagesettings` (
  `surveyls_survey_id` int(10) unsigned NOT NULL DEFAULT '0',
  `surveyls_language` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `surveyls_title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `surveyls_description` text COLLATE utf8_unicode_ci,
  `surveyls_welcometext` text COLLATE utf8_unicode_ci,
  `surveyls_endtext` text COLLATE utf8_unicode_ci,
  `surveyls_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surveyls_urldescription` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surveyls_email_invite_subj` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surveyls_email_invite` text COLLATE utf8_unicode_ci,
  `surveyls_email_remind_subj` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surveyls_email_remind` text COLLATE utf8_unicode_ci,
  `surveyls_email_register_subj` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surveyls_email_register` text COLLATE utf8_unicode_ci,
  `surveyls_email_confirm_subj` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `surveyls_email_confirm` text COLLATE utf8_unicode_ci,
  `surveyls_dateformat` int(10) unsigned NOT NULL DEFAULT '1',
  `email_admin_notification_subj` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_admin_notification` text COLLATE utf8_unicode_ci,
  `email_admin_responses_subj` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email_admin_responses` text COLLATE utf8_unicode_ci,
  `surveyls_numberformat` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`surveyls_survey_id`,`surveyls_language`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `lime_surveys_languagesettings`
--


-- --------------------------------------------------------

--
-- Table structure for table `lime_survey_links`
--

CREATE TABLE IF NOT EXISTS `lime_survey_links` (
  `participant_id` varchar(40) NOT NULL DEFAULT '',
  `token_id` int(11) NOT NULL DEFAULT '0',
  `survey_id` int(11) NOT NULL DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`participant_id`,`token_id`,`survey_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lime_survey_links`
--


-- --------------------------------------------------------

--
-- Table structure for table `lime_survey_permissions`
--

CREATE TABLE IF NOT EXISTS `lime_survey_permissions` (
  `sid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `permission` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `create_p` tinyint(1) NOT NULL DEFAULT '0',
  `read_p` tinyint(1) NOT NULL DEFAULT '0',
  `update_p` tinyint(1) NOT NULL DEFAULT '0',
  `delete_p` tinyint(1) NOT NULL DEFAULT '0',
  `import_p` tinyint(1) NOT NULL DEFAULT '0',
  `export_p` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`,`uid`,`permission`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `lime_survey_permissions`
--

INSERT INTO `lime_survey_permissions` (`sid`, `uid`, `permission`, `create_p`, `read_p`, `update_p`, `delete_p`, `import_p`, `export_p`) VALUES
(26314, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(26314, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(26314, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(26314, 1, 'survey', 0, 1, 0, 1, 0, 0),
(26314, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(26314, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(26314, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(26314, 1, 'responses', 1, 1, 1, 1, 1, 1),
(26314, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(26314, 1, 'translations', 0, 1, 1, 0, 0, 0),
(26314, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(26314, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(54494, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(54494, 1, 'translations', 0, 1, 1, 0, 0, 0),
(54494, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(54494, 1, 'responses', 1, 1, 1, 1, 1, 1),
(54494, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(54494, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(54494, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(54494, 1, 'survey', 0, 1, 0, 1, 0, 0),
(54494, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(54494, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(54494, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(54494, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(14326, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(14326, 1, 'translations', 0, 1, 1, 0, 0, 0),
(14326, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(14326, 1, 'responses', 1, 1, 1, 1, 1, 1),
(14326, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(14326, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(14326, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(14326, 1, 'survey', 0, 1, 0, 1, 0, 0),
(14326, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(14326, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(14326, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(14326, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(87665, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(87665, 1, 'translations', 0, 1, 1, 0, 0, 0),
(87665, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(87665, 1, 'responses', 1, 1, 1, 1, 1, 1),
(87665, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(87665, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(87665, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(87665, 1, 'survey', 0, 1, 0, 1, 0, 0),
(87665, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(87665, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(87665, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(87665, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(57563, 3, 'assessments', 1, 1, 1, 1, 0, 0),
(57563, 3, 'translations', 0, 1, 1, 0, 0, 0),
(57563, 3, 'quotas', 1, 1, 1, 1, 0, 0),
(57563, 3, 'responses', 1, 1, 1, 1, 1, 1),
(57563, 3, 'statistics', 0, 1, 0, 0, 0, 0),
(57563, 3, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(57563, 3, 'surveycontent', 1, 1, 1, 1, 1, 1),
(57563, 3, 'survey', 0, 1, 0, 1, 0, 0),
(57563, 3, 'surveylocale', 0, 1, 1, 0, 0, 0),
(57563, 3, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(57563, 3, 'surveysettings', 0, 1, 1, 0, 0, 0),
(57563, 3, 'tokens', 1, 1, 1, 1, 1, 1),
(77443, 3, 'assessments', 1, 1, 1, 1, 0, 0),
(77443, 3, 'translations', 0, 1, 1, 0, 0, 0),
(77443, 3, 'quotas', 1, 1, 1, 1, 0, 0),
(77443, 3, 'responses', 1, 1, 1, 1, 1, 1),
(77443, 3, 'statistics', 0, 1, 0, 0, 0, 0),
(77443, 3, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(77443, 3, 'surveycontent', 1, 1, 1, 1, 1, 1),
(77443, 3, 'survey', 0, 1, 0, 1, 0, 0),
(77443, 3, 'surveylocale', 0, 1, 1, 0, 0, 0),
(77443, 3, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(77443, 3, 'surveysettings', 0, 1, 1, 0, 0, 0),
(77443, 3, 'tokens', 1, 1, 1, 1, 1, 1),
(81327, 1, 'survey', 0, 1, 0, 1, 0, 0),
(81327, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(81327, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(81327, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(81327, 1, 'responses', 1, 1, 1, 1, 1, 1),
(81327, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(81327, 1, 'translations', 0, 1, 1, 0, 0, 0),
(81327, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(43282, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(43282, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(43282, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(43282, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(43282, 1, 'survey', 0, 1, 0, 1, 0, 0),
(43282, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(43282, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(43282, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(43282, 1, 'responses', 1, 1, 1, 1, 1, 1),
(43282, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(43282, 1, 'translations', 0, 1, 1, 0, 0, 0),
(43282, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(71139, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(71139, 1, 'translations', 0, 1, 1, 0, 0, 0),
(71139, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(71139, 1, 'responses', 1, 1, 1, 1, 1, 1),
(71139, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(71139, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(71139, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(71139, 1, 'survey', 0, 1, 0, 1, 0, 0),
(71139, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(71139, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(71139, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(71139, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(75675, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(75675, 1, 'translations', 0, 1, 1, 0, 0, 0),
(75675, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(75675, 1, 'responses', 1, 1, 1, 1, 1, 1),
(75675, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(75675, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(75675, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(75675, 1, 'survey', 0, 1, 0, 1, 0, 0),
(75675, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(75675, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(75675, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(75675, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(95232, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(95232, 1, 'translations', 0, 1, 1, 0, 0, 0),
(95232, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(95232, 1, 'responses', 1, 1, 1, 1, 1, 1),
(95232, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(95232, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(95232, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(95232, 1, 'survey', 0, 1, 0, 1, 0, 0),
(95232, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(95232, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(95232, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(95232, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(23838, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(23838, 1, 'translations', 0, 1, 1, 0, 0, 0),
(23838, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(23838, 1, 'responses', 1, 1, 1, 1, 1, 1),
(23838, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(23838, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(23838, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(23838, 1, 'survey', 0, 1, 0, 1, 0, 0),
(23838, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(23838, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(23838, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(23838, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(41348, 3, 'assessments', 1, 1, 1, 1, 0, 0),
(41348, 3, 'translations', 0, 1, 1, 0, 0, 0),
(41348, 3, 'quotas', 1, 1, 1, 1, 0, 0),
(41348, 3, 'responses', 1, 1, 1, 1, 1, 1),
(41348, 3, 'statistics', 0, 1, 0, 0, 0, 0),
(41348, 3, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(41348, 3, 'surveycontent', 1, 1, 1, 1, 1, 1),
(41348, 3, 'survey', 0, 1, 0, 1, 0, 0),
(41348, 3, 'surveylocale', 0, 1, 1, 0, 0, 0),
(41348, 3, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(41348, 3, 'surveysettings', 0, 1, 1, 0, 0, 0),
(41348, 3, 'tokens', 1, 1, 1, 1, 1, 1),
(99939, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(99939, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(99939, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(99939, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(99939, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(99939, 1, 'survey', 0, 1, 0, 1, 0, 0),
(99939, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(99939, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(41915, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(41915, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(41915, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(41915, 1, 'survey', 0, 1, 0, 1, 0, 0),
(41915, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(41915, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(41915, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(41915, 1, 'responses', 1, 1, 1, 1, 1, 1),
(41915, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(41915, 1, 'translations', 0, 1, 1, 0, 0, 0),
(99939, 1, 'responses', 1, 1, 1, 1, 1, 1),
(99939, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(99939, 1, 'translations', 0, 1, 1, 0, 0, 0),
(99939, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(41915, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(41915, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(81327, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(81327, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(81327, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(81327, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(54152, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(54152, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(54152, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(54152, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(54152, 1, 'survey', 0, 1, 0, 1, 0, 0),
(54152, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(54152, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(54152, 1, 'responses', 1, 1, 1, 1, 1, 1),
(54152, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(54152, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(54152, 1, 'translations', 0, 1, 1, 0, 0, 0),
(54152, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(65797, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(65797, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(65797, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(65797, 1, 'responses', 1, 1, 1, 1, 1, 1),
(65797, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(65797, 1, 'translations', 0, 1, 1, 0, 0, 0),
(65797, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(65797, 1, 'survey', 0, 1, 0, 1, 0, 0),
(65797, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(65797, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(65797, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(65797, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(45614, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(45614, 1, 'translations', 0, 1, 1, 0, 0, 0),
(45614, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(45614, 1, 'responses', 1, 1, 1, 1, 1, 1),
(45614, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(45614, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(45614, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(45614, 1, 'survey', 0, 1, 0, 1, 0, 0),
(45614, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(45614, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(45614, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(45614, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(42439, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(42439, 1, 'translations', 0, 1, 1, 0, 0, 0),
(42439, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(42439, 1, 'responses', 1, 1, 1, 1, 1, 1),
(42439, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(42439, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(42439, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(42439, 1, 'survey', 0, 1, 0, 1, 0, 0),
(42439, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(42439, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(42439, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(42439, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(23634, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(23634, 1, 'translations', 0, 1, 1, 0, 0, 0),
(23634, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(23634, 1, 'responses', 1, 1, 1, 1, 1, 1),
(23634, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(23634, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(23634, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(23634, 1, 'survey', 0, 1, 0, 1, 0, 0),
(23634, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(23634, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(23634, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(23634, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(39547, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(39547, 1, 'translations', 0, 1, 1, 0, 0, 0),
(39547, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(39547, 1, 'responses', 1, 1, 1, 1, 1, 1),
(39547, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(39547, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(39547, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(39547, 1, 'survey', 0, 1, 0, 1, 0, 0),
(39547, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(39547, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(39547, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(39547, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(41455, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(41455, 1, 'translations', 0, 1, 1, 0, 0, 0),
(41455, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(41455, 1, 'responses', 1, 1, 1, 1, 1, 1),
(41455, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(41455, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(41455, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(41455, 1, 'survey', 0, 1, 0, 1, 0, 0),
(41455, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(41455, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(41455, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(41455, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(36975, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(36975, 1, 'translations', 0, 1, 1, 0, 0, 0),
(36975, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(36975, 1, 'responses', 1, 1, 1, 1, 1, 1),
(36975, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(36975, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(36975, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(36975, 1, 'survey', 0, 1, 0, 1, 0, 0),
(36975, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(36975, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(36975, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(36975, 1, 'tokens', 1, 1, 1, 1, 1, 1),
(92545, 1, 'assessments', 1, 1, 1, 1, 0, 0),
(92545, 1, 'translations', 0, 1, 1, 0, 0, 0),
(92545, 1, 'quotas', 1, 1, 1, 1, 0, 0),
(92545, 1, 'responses', 1, 1, 1, 1, 1, 1),
(92545, 1, 'statistics', 0, 1, 0, 0, 0, 0),
(92545, 1, 'surveyactivation', 0, 0, 1, 0, 0, 0),
(92545, 1, 'surveycontent', 1, 1, 1, 1, 1, 1),
(92545, 1, 'survey', 0, 1, 0, 1, 0, 0),
(92545, 1, 'surveylocale', 0, 1, 1, 0, 0, 0),
(92545, 1, 'surveysecurity', 1, 1, 1, 1, 0, 0),
(92545, 1, 'surveysettings', 0, 1, 1, 0, 0, 0),
(92545, 1, 'tokens', 1, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `lime_templates`
--

CREATE TABLE IF NOT EXISTS `lime_templates` (
  `folder` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creator` int(11) NOT NULL,
  PRIMARY KEY (`folder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `lime_templates`
--

INSERT INTO `lime_templates` (`folder`, `creator`) VALUES
('basic', 1),
('bluengrey', 1),
('citronade', 1),
('clear_logo', 1),
('default', 1),
('eirenicon', 1),
('limespired', 1),
('mint_idea', 1),
('sherpa', 1),
('vallendar', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lime_templates_rights`
--

CREATE TABLE IF NOT EXISTS `lime_templates_rights` (
  `uid` int(11) NOT NULL,
  `folder` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `use` int(1) NOT NULL,
  PRIMARY KEY (`uid`,`folder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `lime_templates_rights`
--

INSERT INTO `lime_templates_rights` (`uid`, `folder`, `use`) VALUES
(2, 'default', 1),
(3, 'default', 1),
(4, 'default', 1),
(5, 'default', 1),
(6, 'default', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lime_users`
--

CREATE TABLE IF NOT EXISTS `lime_users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `users_name` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` blob NOT NULL,
  `full_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  `lang` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(320) COLLATE utf8_unicode_ci DEFAULT NULL,
  `create_survey` tinyint(1) NOT NULL DEFAULT '0',
  `create_user` tinyint(1) NOT NULL DEFAULT '0',
  `delete_user` tinyint(1) NOT NULL DEFAULT '0',
  `superadmin` tinyint(1) NOT NULL DEFAULT '0',
  `configurator` tinyint(1) NOT NULL DEFAULT '0',
  `manage_template` tinyint(1) NOT NULL DEFAULT '0',
  `manage_label` tinyint(1) NOT NULL DEFAULT '0',
  `htmleditormode` varchar(7) COLLATE utf8_unicode_ci DEFAULT 'default',
  `one_time_pw` blob,
  `dateformat` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `users_name` (`users_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `lime_users`
--

INSERT INTO `lime_users` (`uid`, `users_name`, `password`, `full_name`, `parent_id`, `lang`, `email`, `create_survey`, `create_user`, `delete_user`, `superadmin`, `configurator`, `manage_template`, `manage_label`, `htmleditormode`, `one_time_pw`, `dateformat`) VALUES
(1, 'admin', 0x35653838343839386461323830343731353164306535366638646336323932373733363033643064366161626264643632613131656637323164313534326438, 'Your Name', 0, 'en', 'your-email@example.net', 1, 1, 1, 1, 1, 1, 1, 'default', NULL, 1),
(3, 'aniessh', 0x35653838343839386461323830343731353164306535366638646336323932373733363033643064366161626264643632613131656637323164313534326438, 'Aniessh Sethh', 1, 'en', 'aniesshsethh@gmail.com', 1, 1, 1, 0, 1, 1, 1, 'default', NULL, 1),
(6, 'Jason', 0x35653838343839386461323830343731353164306535366638646336323932373733363033643064366161626264643632613131656637323164313534326438, 'Jason Cleeland', 1, 'en', 'jason@cleeland.org', 1, 1, 1, 0, 1, 1, 1, 'default', NULL, 1),
(5, 'Marcel', 0x35653838343839386461323830343731353164306535366638646336323932373733363033643064366161626264643632613131656637323164313534326438, 'Marcel Minke', 1, 'auto', 'marcel@minke.com', 0, 0, 0, 0, 0, 0, 0, 'default', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `lime_user_groups`
--

CREATE TABLE IF NOT EXISTS `lime_user_groups` (
  `ugid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ugid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `lime_user_groups`
--

INSERT INTO `lime_user_groups` (`ugid`, `name`, `description`, `owner_id`) VALUES
(2, 'aniessh', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `lime_user_in_groups`
--

CREATE TABLE IF NOT EXISTS `lime_user_in_groups` (
  `ugid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ugid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `lime_user_in_groups`
--

INSERT INTO `lime_user_in_groups` (`ugid`, `uid`) VALUES
(2, 1),
(2, 4);
