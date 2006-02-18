# phpMyAdmin SQL Dump
# Generation Time: Mar 03, 2005 at 09:43 AM
# Server version: 3.23.52
# PHP Version: 4.2.3
# 
# 

# --------------------------------------------------------

#
# Table structure for table `answers`
#

CREATE TABLE `answers` (
  `qid` int(11) NOT NULL default '0',
  `code` varchar(5) NOT NULL default '',
  `answer` text NOT NULL,
  `default_value` char(1) NOT NULL default 'N',
  `sortorder` varchar(5) default NULL
) TYPE=MyISAM;

ALTER TABLE `answers` ADD PRIMARY KEY ( `qid` , `code` ) ;

# --------------------------------------------------------

#
# Table structure for table `assessments`
#

CREATE TABLE `assessments` (
  `id` int(11) NOT NULL auto_increment,
  `sid` int(11) NOT NULL default '0',
  `scope` varchar(5) NOT NULL default '',
  `gid` int(11) NOT NULL default '0',
  `name` text NOT NULL,
  `minimum` varchar(50) NOT NULL default '',
  `maximum` varchar(50) NOT NULL default '',
  `message` text NOT NULL,
  `link` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `conditions`
#

CREATE TABLE `conditions` (
  `cid` int(11) NOT NULL auto_increment,
  `qid` int(11) NOT NULL default '0',
  `cqid` int(11) NOT NULL default '0',
  `cfieldname` varchar(50) NOT NULL default '',
  `method` char(2) NOT NULL default '',
  `value` varchar(5) NOT NULL default '',
  PRIMARY KEY  (`cid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `groups`
#

CREATE TABLE `groups` (
  `gid` int(11) NOT NULL auto_increment,
  `sid` int(11) NOT NULL default '0',
  `group_name` varchar(100) NOT NULL default '',
  `description` text,
  PRIMARY KEY  (`gid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `labels`
#

CREATE TABLE `labels` (
  `lid` int(11) NOT NULL default '0',
  `code` varchar(5) NOT NULL default '',
  `title` varchar(50) NOT NULL default '',
  `sortorder` varchar(5) default NULL
) TYPE=MyISAM;

ALTER TABLE `labels` ADD PRIMARY KEY ( `lid` , `code` ) ;


# --------------------------------------------------------

#
# Table structure for table `labelsets`
#

CREATE TABLE `labelsets` (
  `lid` int(11) NOT NULL auto_increment,
  `label_name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`lid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `question_attributes`
#

CREATE TABLE `question_attributes` (
  `qaid` int(11) NOT NULL auto_increment,
  `qid` int(11) NOT NULL default '0',
  `attribute` varchar(50) default NULL,
  `value` varchar(20) default NULL,
  PRIMARY KEY  (`qaid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `questions`
#

CREATE TABLE `questions` (
  `qid` int(11) NOT NULL auto_increment,
  `sid` int(11) NOT NULL default '0',
  `gid` int(11) NOT NULL default '0',
  `type` char(1) NOT NULL default 'T',
  `title` varchar(20) NOT NULL default '',
  `question` text NOT NULL,
  `preg` text,
  `help` text,
  `other` char(1) NOT NULL default 'N',
  `mandatory` char(1) default NULL,
  `lid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`qid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `saved`
#

CREATE TABLE `saved` (
  `saved_id` int(11) NOT NULL auto_increment,
  `scid` int(11) NOT NULL default '0',
  `datestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `fieldname` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`saved_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `saved_control`
#

CREATE TABLE `saved_control` (
  `scid` int(11) NOT NULL auto_increment,
  `sid` int(11) NOT NULL default '0',
  `identifier` text NOT NULL,
  `access_code` text NOT NULL,
  `email` varchar(200) default NULL,
  `ip` text NOT NULL,
  `saved_thisstep` text NOT NULL,
  `status` char(1) NOT NULL default '',
  `saved_date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`scid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `surveys`
#

CREATE TABLE `surveys` (
  `sid` int(11) NOT NULL auto_increment,
  `short_title` varchar(200) NOT NULL default '',
  `description` text,
  `admin` varchar(50) default NULL,
  `active` char(1) NOT NULL default 'N',
  `welcome` text,
  `expires` date default NULL,
  `adminemail` varchar(100) default NULL,
  `private` char(1) default NULL,
  `faxto` varchar(20) default NULL,
  `format` char(1) default NULL,
  `template` varchar(100) default 'default',
  `url` varchar(255) default NULL,
  `urldescrip` varchar(255) default NULL,
  `language` varchar(50) default '',
  `datestamp` char(1) default 'N',
  `usecookie` char(1) default 'N',
  `notification` char(1) default '0',
  `allowregister` char(1) default 'N',
  `attribute1` varchar(255) default NULL,
  `attribute2` varchar(255) default NULL,
  `email_invite_subj` varchar(255) default NULL,
  `email_invite` text,
  `email_remind_subj` varchar(255) default NULL,
  `email_remind` text,
  `email_register_subj` varchar(255) default NULL,
  `email_register` text,
  `email_confirm_subj` varchar(255) default NULL,
  `email_confirm` text,
  `allowsave` char(1) default 'Y',
  `autonumber_start` bigint(11) default '0',
  `autoredirect` char(1) default 'N',
  `allowprev` char(1) default 'Y',
  PRIMARY KEY  (`sid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `users`
#

CREATE TABLE `users` (
  `user` varchar(20) NOT NULL default '',
  `password` varchar(20) NOT NULL default '',
  `security` varchar(10) NOT NULL default ''
) TYPE=MyISAM;