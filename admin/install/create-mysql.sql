
CREATE TABLE `prefix_answers` (
  `qid` int(11) NOT NULL default '0',
  `code` varchar(5) NOT NULL default '',
  `answer` text NOT NULL,
  `assessment_value` int(11) NOT NULL default '0',
  `sortorder` int(11) NOT NULL,
  `language` varchar(20) default 'en',
  `scale_id` tinyint NOT NULL default '0',
  PRIMARY KEY  (`qid`,`code`,`language`,`scale_id`)
) ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_assessments` (
  `id` int(11) NOT NULL auto_increment,
  `sid` int(11) NOT NULL default '0',
  `scope` varchar(5) NOT NULL default '',
  `gid` int(11) NOT NULL default '0',
  `name` text NOT NULL,
  `minimum` varchar(50) NOT NULL default '',
  `maximum` varchar(50) NOT NULL default '',
  `message` text NOT NULL,
  `language` varchar(20) NOT NULL default 'en',
  PRIMARY KEY  (`id`,`language`)
) ENGINE=$databasetabletype AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_conditions` (
  `cid` int(11) NOT NULL auto_increment,
  `qid` int(11) NOT NULL default '0',
  `scenario` int(11) NOT NULL default '1',
  `cqid` int(11) NOT NULL default '0',
  `cfieldname` varchar(50) NOT NULL default '',
  `method` char(2) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`cid`)
) ENGINE=$databasetabletype AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_unicode_ci;


CREATE TABLE `prefix_defaultvalues` (
  `qid` int(11) NOT NULL default '0',
  `specialtype` varchar(20) NOT NULL default '',
  `scale_id` int(11) NOT NULL default '0',
  `sqid` int(11) NOT NULL default '0',
  `language` varchar(20) NOT NULL,
  `defaultvalue` text,
  PRIMARY KEY  (`qid` , `scale_id`, `language`, `specialtype`, `sqid` )
) ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;


CREATE TABLE `prefix_groups` (
  `gid` int(11) NOT NULL auto_increment,
  `sid` int(11) NOT NULL default '0',
  `group_name` varchar(100) NOT NULL default '',
  `group_order` int(11) NOT NULL default '0',
  `description` text,
  `language` varchar(20) default 'en',
  PRIMARY KEY  (`gid`,`language`)
) ENGINE=$databasetabletype AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_labels` (
  `lid` int(11) NOT NULL default '0',
  `code` varchar(5) NOT NULL default '',
  `title` text,
  `sortorder` int(11) NOT NULL,
  `assessment_value` int(11) NOT NULL default '0',
  `language` varchar(20) default 'en',
  PRIMARY KEY  (`lid`,`sortorder`,`language`),
  KEY `ixcode` (`code`)
) ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_labelsets` (
  `lid` int(11) NOT NULL auto_increment,
  `label_name` varchar(100) NOT NULL default '',
  `languages` varchar(200) default 'en',
  PRIMARY KEY  (`lid`)
) ENGINE=$databasetabletype AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_question_attributes` (
  `qaid` int(11) NOT NULL auto_increment,
  `qid` int(11) NOT NULL default '0',
  `attribute` varchar(50) default NULL,
  `value` text default NULL,
  PRIMARY KEY  (`qaid`)
) ENGINE=$databasetabletype AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_quota` (
  `id` int(11) NOT NULL auto_increment,
  `sid` int(11) default NULL,
  `name` varchar(255) collate utf8_unicode_ci default NULL,
  `qlimit` int(8) default NULL,
  `action` int(2) default NULL,
  `active` int(1) NOT NULL default '1',
  `autoload_url` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)  ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_quota_languagesettings` (
  `quotals_id` int(11) NOT NULL auto_increment,
  `quotals_quota_id` int(11) NOT NULL default '0',
  `quotals_language` varchar(45) NOT NULL default 'en',
  `quotals_name` varchar(255) collate utf8_unicode_ci default NULL,
  `quotals_message` text NOT NULL,
  `quotals_url` varchar(255),
  `quotals_urldescrip` varchar(255),
  PRIMARY KEY (`quotals_id`)
)  ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_quota_members` (
  `id` int(11) NOT NULL auto_increment,
  `sid` int(11) default NULL,
  `qid` int(11) default NULL,
  `quota_id` int(11) default NULL,
  `code` varchar(11) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `sid` (`sid`,`qid`,`quota_id`,`code`)
)   ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_questions` (
  `qid` int(11) NOT NULL auto_increment,
  `parent_qid` int(11) NOT NULL default '0',
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
  `lid1` int(11) NOT NULL default '0',
  `question_order` int(11) NOT NULL,
  `language` varchar(20) default 'en',
  `default_value` text,
  `scale_id` tinyint NOT NULL default '0',
  `same_default` tinyint NOT NULL default '0' COMMENT 'Saves if user set to use the same default value across languages in default options dialog',
  PRIMARY KEY  (`qid`,`language`)
) ENGINE=$databasetabletype AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_saved_control` (
  `scid` int(11) NOT NULL auto_increment,
  `sid` int(11) NOT NULL default '0',
  `srid` int(11) NOT NULL default '0',
  `identifier` text NOT NULL,
  `access_code` text NOT NULL,
  `email` varchar(320) default NULL,
  `ip` text NOT NULL,
  `saved_thisstep` text NOT NULL,
  `status` char(1) NOT NULL default '',
  `saved_date` datetime NOT NULL,
  `refurl` text,
  PRIMARY KEY  (`scid`)
) ENGINE=$databasetabletype AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_settings_global` (
  `stg_name` varchar(50) NOT NULL default '',
  `stg_value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`stg_name`)
) ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_surveys` (
  `sid` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `admin` varchar(50) default NULL,
  `active` char(1) NOT NULL default 'N',
  `expires` datetime default NULL,
  `startdate` datetime default NULL,
  `adminemail` varchar(320) default NULL,
  `private` char(1) default NULL,
  `faxto` varchar(20) default NULL,
  `format` char(1) default NULL,
  `template` varchar(100) default 'default',
  `language` varchar(50) default NULL,
  `additional_languages` varchar(255) default NULL,
  `datestamp` char(1) default 'N',
  `usecookie` char(1) default 'N',
  `notification` char(1) default '0',
  `allowregister` char(1) default 'N',
  `allowsave` char(1) default 'Y',
  `autonumber_start` bigint(11) default '0',
  `autoredirect` char(1) default 'N',
  `allowprev` char(1) default 'Y',
  `printanswers` char(1) default 'N',
  `ipaddr` char(1) default 'N',
  `refurl` char(1) default 'N',
  `datecreated` date default NULL,
  `publicstatistics` char(1) default 'N',
  `publicgraphs` char(1) default 'N',
  `listpublic` char(1) default 'N',
  `htmlemail` char(1) default 'N',
  `tokenanswerspersistence` char(1) default 'N',
  `assessments` char(1) default 'N', 
  `usecaptcha` char(1) default 'N',
  `usetokens` char(1) default 'N',
  `bounce_email` varchar(320) default NULL,
  `attributedescriptions` text,
  `emailresponseto` text default NULL,
  `tokenlength` tinyint(2) default '15',
  PRIMARY KEY  (`sid`)
) ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;



DROP TABLE IF EXISTS `prefix_surveys_languagesettings`;
CREATE TABLE `prefix_surveys_languagesettings` (
  `surveyls_survey_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `surveyls_language` VARCHAR(45) NULL DEFAULT 'en',
  `surveyls_title` VARCHAR(200) NOT NULL,
  `surveyls_description` TEXT NULL,
  `surveyls_welcometext` TEXT NULL,
  `surveyls_endtext` TEXT NULL,
  `surveyls_url` VARCHAR(255) NULL,
  `surveyls_urldescription` VARCHAR(255) NULL,
  `surveyls_email_invite_subj` VARCHAR(255) NULL,
  `surveyls_email_invite` TEXT NULL,
  `surveyls_email_remind_subj` VARCHAR(255) NULL,
  `surveyls_email_remind` TEXT NULL,
  `surveyls_email_register_subj` VARCHAR(255) NULL,
  `surveyls_email_register` TEXT NULL,
  `surveyls_email_confirm_subj` VARCHAR(255) NULL,
  `surveyls_email_confirm` TEXT NULL,
  `surveyls_dateformat` INT UNSIGNED NOT NULL DEFAULT 1, 
  
  PRIMARY KEY (`surveyls_survey_id`, `surveyls_language`)
)
TYPE = $databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_surveys_rights` (
	`sid` int(10) unsigned NOT NULL default '0',
	`uid` int(10) unsigned NOT NULL default '0',
	`edit_survey_property` tinyint(1) NOT NULL default '0',
	`define_questions` tinyint(1) NOT NULL default '0',
	`browse_response` tinyint(1) NOT NULL default '0',
	`export` tinyint(1) NOT NULL default '0',
	`delete_survey` tinyint(1) NOT NULL default '0',
	`activate_survey` tinyint(1) NOT NULL default '0',
	PRIMARY KEY (sid, uid)
) ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_user_groups` (
	`ugid` int(10) unsigned NOT NULL auto_increment PRIMARY KEY,
	`name` varchar(20) NOT NULL UNIQUE,
	`description` TEXT NOT NULL,
	`owner_id` int(10) unsigned NOT NULL
) ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_user_in_groups` (
	`ugid` int(10) unsigned NOT NULL,
	`uid` int(10) unsigned NOT NULL,
     PRIMARY KEY  (`ugid`,`uid`)	
) ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_users` (
  `uid` int(11) NOT NULL auto_increment PRIMARY KEY,
  `users_name` varchar(64) NOT NULL UNIQUE default '',
  `password` BLOB NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  `lang` varchar(20),
  `email` varchar(320),
  `create_survey` tinyint(1) NOT NULL default '0',
  `create_user` tinyint(1) NOT NULL default '0',
  `delete_user` tinyint(1) NOT NULL default '0',
  `superadmin` tinyint(1) NOT NULL default '0',
  `configurator` tinyint(1) NOT NULL default '0',
  `manage_template` tinyint(1) NOT NULL default '0',
  `manage_label` tinyint(1) NOT NULL default '0',
  `htmleditormode` varchar(7) default 'default',
  `one_time_pw` BLOB,
  `dateformat` INT UNSIGNED NOT NULL DEFAULT 1 
) ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_templates_rights` (
  `uid` int(11) NOT NULL,
  `folder` varchar(255) NOT NULL,
  `use` int(1) NOT NULL,
  PRIMARY KEY  (`uid`,`folder`)
) ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;



CREATE TABLE `prefix_templates` (
  `folder` varchar(255) NOT NULL,
  `creator` int(11) NOT NULL,
  PRIMARY KEY  (`folder`)
) ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;


-- Secondary indexes 
create index `answers_idx2` on `prefix_answers` (`sortorder`);
create index `assessments_idx2` on `prefix_assessments` (`sid`);
create index `assessments_idx3` on `prefix_assessments` (`gid`);
create index `conditions_idx2` on `prefix_conditions` (`qid`);
create index `conditions_idx3` on `prefix_conditions` (`cqid`);
create index `groups_idx2` on `prefix_groups` (`sid`);
create index `question_attributes_idx2` on `prefix_question_attributes` (`qid`);
create index `questions_idx2` on `prefix_questions` (`sid`);
create index `questions_idx3` on `prefix_questions` (`gid`);
create index `questions_idx4` on `prefix_questions` (`type`);
create index `quota_idx2` on `prefix_quota` (`sid`);
create index `saved_control_idx2` on `prefix_saved_control` (`sid`);


-- data
INSERT INTO `prefix_settings_global` VALUES ('DBVersion', '143');
INSERT INTO `prefix_settings_global` VALUES ('SessionName', '$sessionname');

INSERT INTO `prefix_users` (`users_name`, `password`, `full_name`, `parent_id`, `lang` ,`email`, `create_survey`,`create_user` ,`delete_user` ,`superadmin` ,`configurator` ,`manage_template` , `manage_label`) VALUES ('$defaultuser', '$defaultpass', '$siteadminname', 0, '$defaultlang', '$siteadminemail', 1,1,1,1,1,1,1);

