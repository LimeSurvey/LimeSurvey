--
-- Table structure for table answers
--
CREATE TABLE `prefix_answers` (
  `qid` int(11) NOT NULL default '0',
  `code` varchar(5) NOT NULL default '',
  `answer` text NOT NULL,
  `sortorder` int(11) NOT NULL,
  `assessment_value` int(11) NOT NULL default '0',
  `language` varchar(20) default 'en',
  `scale_id` int(11) NOT NULL default '0',
  PRIMARY KEY (`qid`,`code`,`language`,`scale_id`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table assessments
--
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
  PRIMARY KEY (`id`,`language`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table conditions
--
CREATE TABLE `prefix_conditions` (
  `cid` int(11) NOT NULL auto_increment,
  `qid` int(11) NOT NULL default '0',
  `cqid` int(11) NOT NULL default '0',
  `cfieldname` varchar(50) NOT NULL default '',
  `method` varchar(5) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `scenario` int(11) NOT NULL default '1',
  PRIMARY KEY (`cid`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table defaultvalues
--
CREATE TABLE `prefix_defaultvalues` (
  `qid` int(11) NOT NULL default '0',
  `scale_id` int(11) NOT NULL default '0',
  `sqid` int(11) NOT NULL default '0',
  `language` varchar(20) NOT NULL,
  `specialtype` varchar(20) NOT NULL default '',
  `defaultvalue` text,
  PRIMARY KEY (`qid`, `specialtype`, `language`, `scale_id`, `sqid`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table expression_errors
--
CREATE TABLE `prefix_expression_errors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `errortime` varchar(50) DEFAULT NULL,
  `sid` int(11) DEFAULT NULL,
  `gid` int(11) DEFAULT NULL,
  `qid` int(11) DEFAULT NULL,
  `gseq` int(11) DEFAULT NULL,
  `qseq` int(11) DEFAULT NULL,
  `type` varchar(50),
  `eqn` text,
  `prettyprint` text,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Create failed_login_attempts
--
CREATE TABLE `prefix_failed_login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(40) NOT NULL,
  `last_attempt` varchar(20) NOT NULL,
  `number_attempts` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table groups
--
CREATE TABLE `prefix_groups` (
  `gid` int(11) NOT NULL auto_increment,
  `sid` int(11) NOT NULL default '0',
  `group_name` varchar(100) NOT NULL default '',
  `group_order` int(11) NOT NULL default '0',
  `description` text,
  `language` varchar(20) default 'en',
  `randomization_group` varchar(20) NOT NULL default '',
  `grelevance` text DEFAULT NULL,
  PRIMARY KEY (`gid`,`language`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table labels
--
CREATE TABLE `prefix_labels` (
  `lid` int(11) NOT NULL default '0',
  `code` varchar(5) NOT NULL default '',
  `title` text,
  `sortorder` int(11) NOT NULL,
  `language` varchar(20) default 'en',
  `assessment_value` int(11) NOT NULL default '0',
  PRIMARY KEY (`lid`,`sortorder`,`language`),
  KEY `labels_code_idx` (`code`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table labelsets
--
CREATE TABLE `prefix_labelsets` (
  `lid` int(11) NOT NULL auto_increment,
  `label_name` varchar(100) NOT NULL default '',
  `languages` varchar(200) default 'en',
  PRIMARY KEY (`lid`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table participant_attribute
--
CREATE TABLE `prefix_participant_attribute` (
  `participant_id` varchar(50) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`participant_id`,`attribute_id`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table participant_attribute_names_lang
--
CREATE TABLE `prefix_participant_attribute_names_lang` (
  `attribute_id` int(11) NOT NULL,
  `attribute_name` varchar(255) NOT NULL,
  `lang` varchar(20) NOT NULL,
  PRIMARY KEY (`attribute_id`,`lang`)
 ) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table participant_attribute_names
--
CREATE TABLE `prefix_participant_attribute_names` (
  `attribute_id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_type` varchar(4) NOT NULL,
  `defaultname` varchar(255) NOT NULL,
  `visible` varchar(5) NOT NULL,
  PRIMARY KEY (`attribute_id`,`attribute_type`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table participant_attribute_values
--
CREATE TABLE `prefix_participant_attribute_values` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`value_id`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table participant_shares
--
CREATE TABLE `prefix_participant_shares` (
  `participant_id` varchar(50) NOT NULL,
  `share_uid` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  `can_edit` varchar(5) NOT NULL,
  PRIMARY KEY (`participant_id`,`share_uid`)
 ) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table participants
--
CREATE TABLE `prefix_participants` (
  `participant_id` varchar(50) NOT NULL,
  `firstname` varchar(150) DEFAULT NULL,
  `lastname` varchar(150) DEFAULT NULL,
  `email` text,
  `language` varchar(40) DEFAULT NULL,
  `blacklisted` varchar(1) NOT NULL,
  `owner_uid` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` datetime,
  `modified` datetime,
  PRIMARY KEY (`participant_id`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table permissions
--
CREATE TABLE `prefix_permissions` (
  `id` int(11) NOT NULL auto_increment,
  `entity` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `create_p` int(11) NOT NULL default '0',
  `read_p` int(11) NOT NULL default '0',
  `update_p` int(11) NOT NULL default '0',
  `delete_p` int(11) NOT NULL default '0',
  `import_p` int(11) NOT NULL default '0',
  `export_p` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxPermissions` (`entity_id`,`entity`,`permission`,`uid`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table plugins
--
CREATE TABLE `prefix_plugins` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `active` int(11) NOT NULL default '0',
  `version` varchar(32) default null,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table plugin_settings
--
CREATE TABLE `prefix_plugin_settings` (
  `id` int(11) NOT NULL auto_increment,
  `plugin_id` int(11) NOT NULL,
  `model` varchar(50) NULL,
  `model_id` int(11) NULL,
  `key` varchar(50) NOT NULL,
  `value` text NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table question_attributes
--
CREATE TABLE `prefix_question_attributes` (
  `qaid` int(11) NOT NULL auto_increment,
  `qid` int(11) NOT NULL default '0',
  `attribute` varchar(50) default NULL,
  `value` text default NULL,
  `language` varchar(20) default NULL,
  PRIMARY KEY (`qaid`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table questions
--
CREATE TABLE `prefix_questions` (
  `qid` int(11) NOT NULL auto_increment,
  `parent_qid` int(11) NOT NULL default '0',
  `sid` int(11) NOT NULL default '0',
  `gid` int(11) NOT NULL default '0',
  `type` varchar(1) NOT NULL default 'T',
  `title` varchar(20) NOT NULL default '',
  `question` text NOT NULL,
  `preg` text,
  `help` text,
  `other` varchar(1) NOT NULL default 'N',
  `mandatory` varchar(1) default NULL,
  `question_order` int(11) NOT NULL,
  `language` varchar(20) default 'en',
  `scale_id` int(11) NOT NULL default '0',
  `same_default` int(11) NOT NULL default '0',
  `relevance` text,
  `modulename` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`qid`,`language`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table quota
--
CREATE TABLE `prefix_quota` (
  `id` int(11) NOT NULL auto_increment,
  `sid` int(11) default NULL,
  `name` varchar(255) default NULL,
  `qlimit` int(11) default NULL,
  `action` int(11) default NULL,
  `active` int(11) NOT NULL default '1',
  `autoload_url` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table quota_languagesettings
--
CREATE TABLE `prefix_quota_languagesettings` (
  `quotals_id` int(11) NOT NULL auto_increment,
  `quotals_quota_id` int(11) NOT NULL default '0',
  `quotals_language` varchar(45) NOT NULL default 'en',
  `quotals_name` varchar(255) default NULL,
  `quotals_message` text NOT NULL,
  `quotals_url` varchar(255),
  `quotals_urldescrip` varchar(255),
  PRIMARY KEY (`quotals_id`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table quota_members
--
CREATE TABLE `prefix_quota_members` (
  `id` int(11) NOT NULL auto_increment,
  `sid` int(11) default NULL,
  `qid` int(11) default NULL,
  `quota_id` int(11) default NULL,
  `code` varchar(11) default NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sid` (`sid`,`qid`,`quota_id`,`code`)
)  ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table saved_control
--
CREATE TABLE `prefix_saved_control` (
  `scid` int(11) NOT NULL auto_increment,
  `sid` int(11) NOT NULL default '0',
  `srid` int(11) NOT NULL default '0',
  `identifier` text NOT NULL,
  `access_code` text NOT NULL,
  `email` varchar(192),
  `ip` text NOT NULL,
  `saved_thisstep` text NOT NULL,
  `status` varchar(1) NOT NULL default '',
  `saved_date` datetime NOT NULL,
  `refurl` text,
  PRIMARY KEY (`scid`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table sessions
--
CREATE TABLE `prefix_sessions`(
  `id` varchar(32) NOT NULL,
  `expire` int(11) DEFAULT NULL,
  `data` blob,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table settings_global
--
CREATE TABLE `prefix_settings_global` (
  `stg_name` varchar(50) NOT NULL default '',
  `stg_value` text NOT NULL,
  PRIMARY KEY (`stg_name`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table survey_links
--
CREATE TABLE `prefix_survey_links` (
  `participant_id` varchar(50) NOT NULL,
  `token_id` int(11) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `date_created` datetime,
  `date_invited` datetime,
  `date_completed` datetime,
  PRIMARY KEY (`participant_id`,`token_id`,`survey_id`)
 ) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table survey_url_parameters
--
CREATE TABLE `prefix_survey_url_parameters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL,
  `parameter` varchar(50) NOT NULL,
  `targetqid` int(11) NULL,
  `targetsqid` int(11) NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;

--
-- Table structure for table surveys
--
CREATE TABLE `prefix_surveys` (
  `sid` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `gsid` int(11) default '1',
  `admin` varchar(50) default NULL,
  `active` varchar(1) NOT NULL default 'N',
  `expires` datetime default NULL,
  `startdate` datetime default NULL,
  `adminemail` varchar(254) default NULL,
  `anonymized` varchar(1) NOT NULL default 'N',
  `faxto` varchar(20) default NULL,
  `format` varchar(1) default NULL,
  `savetimings` varchar(1) NOT NULL default 'N',
  `template` varchar(100) default 'default',
  `language` varchar(50) default NULL,
  `additional_languages` varchar(255) default NULL,
  `datestamp` varchar(1) NOT NULL default 'N',
  `usecookie` varchar(1) NOT NULL default 'N',
  `allowregister` varchar(1) NOT NULL default 'N',
  `allowsave` varchar(1) NOT NULL default 'Y',
  `autonumber_start` int(11) NOT NULL default '0',
  `autoredirect` varchar(1) NOT NULL default 'N',
  `allowprev` varchar(1) NOT NULL default 'N',
  `printanswers` varchar(1) NOT NULL default 'N',
  `ipaddr` varchar(1) NOT NULL default 'N',
  `refurl` varchar(1) NOT NULL default 'N',
  `datecreated` date default NULL,
  `publicstatistics` varchar(1) NOT NULL default 'N',
  `publicgraphs` varchar(1) NOT NULL default 'N',
  `listpublic` varchar(1) NOT NULL default 'N',
  `htmlemail` varchar(1) NOT NULL default 'N',
  `sendconfirmation` varchar(1) NOT NULL default 'Y',
  `tokenanswerspersistence` varchar(1) NOT NULL default 'N',
  `assessments` varchar(1) NOT NULL default 'N',
  `usecaptcha` varchar(1) NOT NULL default 'N',
  `usetokens` varchar(1) NOT NULL default 'N',
  `bounce_email` varchar(254) default NULL,
  `attributedescriptions` text,
  `emailresponseto` text default NULL,
  `emailnotificationto` text default NULL,
  `tokenlength` int(11) NOT NULL default '15',
  `showxquestions` varchar(1) default 'Y',
  `showgroupinfo` varchar(1) default 'B',
  `shownoanswer` varchar(1) default 'Y',
  `showqnumcode` varchar(1) default 'X',
  `bouncetime` int(11),
  `bounceprocessing` varchar(1) default 'N',
  `bounceaccounttype` varchar(4),
  `bounceaccounthost` varchar(200),
  `bounceaccountpass` varchar(100),
  `bounceaccountencryption` varchar(3),
  `bounceaccountuser` varchar(200),
  `showwelcome` varchar(1) default 'Y',
  `showprogress` varchar(1) default 'Y',
  `questionindex` int(11) default '0' NOT NULL,
  `navigationdelay` int(11) NOT NULL default '0',
  `nokeyboard` varchar(1) default 'N',
  `alloweditaftercompletion` varchar(1) default 'N',
  `googleanalyticsstyle` varchar(1) DEFAULT NULL,
  `googleanalyticsapikey` VARCHAR(25) DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE = MYISAM CHARACTER SET utf8mb4 ;

--
-- Table structure for table surveys_languagesettings
--
CREATE TABLE `prefix_surveys_languagesettings` (
  `surveyls_survey_id` int(11) NOT NULL,
  `surveyls_language` varchar(45) NOT NULL DEFAULT 'en',
  `surveyls_title` varchar(200) NOT NULL,
  `surveyls_description` TEXT NULL,
  `surveyls_welcometext` TEXT NULL,
  `surveyls_endtext` TEXT NULL,
  `surveyls_url` TEXT NULL,
  `surveyls_urldescription` varchar(255) NULL,
  `surveyls_email_invite_subj` varchar(255) NULL,
  `surveyls_email_invite` TEXT NULL,
  `surveyls_email_remind_subj` varchar(255) NULL,
  `surveyls_email_remind` TEXT NULL,
  `surveyls_email_register_subj` varchar(255) NULL,
  `surveyls_email_register` TEXT NULL,
  `surveyls_email_confirm_subj` varchar(255) NULL,
  `surveyls_email_confirm` TEXT NULL,
  `surveyls_dateformat` int(11) NOT NULL DEFAULT 1,
  `surveyls_attributecaptions` TEXT NULL,
  `email_admin_notification_subj` varchar(255) NULL,
  `email_admin_notification` TEXT NULL,
  `email_admin_responses_subj` varchar(255) NULL,
  `email_admin_responses` TEXT NULL,
  `surveyls_numberformat` INT NOT NULL DEFAULT 0,
  `attachments` text DEFAULT NULL,
  PRIMARY KEY (`surveyls_survey_id`, `surveyls_language`)
) ENGINE = MYISAM CHARACTER SET utf8mb4 ;

--
-- Table structure for table user_groups
--
CREATE TABLE `prefix_user_groups` (
  `ugid` int(11) NOT NULL auto_increment,
  `name` varchar(20) NOT NULL,
  `description` TEXT NOT NULL,
  `owner_id` int(11) NOT NULL,
  PRIMARY KEY (`ugid`),
  UNIQUE KEY `lug_name` (`name`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table user_in_groups
--
CREATE TABLE `prefix_user_in_groups` (
  `ugid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`ugid`,`uid`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure for table users
--
CREATE TABLE `prefix_users` (
  `uid` int(11) NOT NULL auto_increment,
  `users_name` varchar(64) NOT NULL default '',
  `password` BLOB NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `lang` varchar(20),
  `email` varchar(192),
  `htmleditormode` varchar(7) default 'default',
  `templateeditormode` varchar(7) NOT NULL default 'default',
  `questionselectormode` varchar(7) NOT NULL default 'default',
  `one_time_pw` BLOB,
  `dateformat` INT(11) NOT NULL DEFAULT 1,
  `created` datetime,
  `modified` datetime,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `users_name` (`users_name`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


--
-- Table structure & data for table boxes
--
CREATE TABLE IF NOT EXISTS `prefix_boxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) DEFAULT NULL,
  `url` text NOT NULL,
  `title` text NOT NULL,
  `ico` varchar(255) DEFAULT NULL,
  `desc` text NOT NULL,
  `page` text NOT NULL,
  `usergroup` INT(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;


INSERT INTO `prefix_boxes` (`id`, `position`, `url`, `title`, `ico`, `desc`, `page`,`usergroup` ) VALUES
(1, 1, 'admin/survey/sa/newsurvey', 'Create survey', 'add', 'Create a new survey', 'welcome', '-2'),
(2, 2, 'admin/survey/sa/listsurveys', 'List surveys', 'list', 'List available surveys', 'welcome', '-1'),
(3, 3, 'admin/globalsettings', 'Global settings', 'settings', 'Edit global settings', 'welcome', '-2'),
(4, 4, 'admin/update', 'ComfortUpdate', 'shield', 'Stay safe and up to date', 'welcome', '-2'),
(5, 5, 'admin/labels/sa/view', 'Label sets', 'label', 'Edit label sets', 'welcome', '-2'),
(6, 6, 'admin/templateoptions', 'Templates', 'templates', 'View templates list', 'welcome', '-2');
--
-- Secondary indexes
--
CREATE INDEX `answers_idx2` ON `prefix_answers` (`sortorder`);
CREATE INDEX `assessments_idx2` ON `prefix_assessments` (`sid`);
CREATE INDEX `assessments_idx3` ON `prefix_assessments` (`gid`);
CREATE INDEX `conditions_idx2` ON `prefix_conditions` (`qid`);
CREATE INDEX `conditions_idx3` ON `prefix_conditions` (`cqid`);
CREATE INDEX `groups_idx2` ON `prefix_groups` (`sid`);
CREATE INDEX `question_attributes_idx2` ON `prefix_question_attributes` (`qid`);
CREATE INDEX `question_attributes_idx3` ON `prefix_question_attributes` (`attribute`);
CREATE INDEX `questions_idx2` ON `prefix_questions` (`sid`);
CREATE INDEX `questions_idx3` ON `prefix_questions` (`gid`);
CREATE INDEX `questions_idx4` ON `prefix_questions` (`type`);
CREATE INDEX `saved_control_idx2` ON `prefix_saved_control` (`sid`);
CREATE INDEX `quota_idx2` ON `prefix_quota` (`sid`);
CREATE INDEX `parent_qid_idx` ON `prefix_questions` (`parent_qid`);

--
-- Notification table
--
CREATE TABLE IF NOT EXISTS `prefix_notifications` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `entity` VARCHAR(15) NOT NULL,
    `entity_id` INT(11) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `status` VARCHAR(15) NOT NULL DEFAULT 'new',
    `importance` INT(11) NOT NULL DEFAULT 1,
    `display_class` VARCHAR(31) DEFAULT 'default',
    `hash` VARCHAR(64) DEFAULT NULL,
    `created` DATETIME,
    `first_read` DATETIME,
    PRIMARY KEY (`id`),
    INDEX(`entity`, `entity_id`, `status`),
    INDEX(`hash`)
) ENGINE=MYISAM CHARACTER SET utf8mb4 ;

--
-- User settings table
--
CREATE TABLE IF NOT EXISTS `prefix_settings_user` (
    `id` int(11) NOT NULL auto_increment,
    `uid` int(11) NOT NULL,
    `entity` VARCHAR(15) DEFAULT NULL,
    `entity_id` VARCHAR(31) DEFAULT NULL,
    `stg_name` VARCHAR(63) NOT NULL,
    `stg_value` TEXT DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = MYISAM CHARACTER SET utf8mb4 ;

--
-- Surveymenu
--

CREATE TABLE `prefix_surveymenu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `survey_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ordering` int(11) DEFAULT '0',
  `level` int(11) DEFAULT '0',
  `title` varchar(168)  NOT NULL DEFAULT '',
  `position` varchar(192)  NOT NULL DEFAULT 'side',
  `description` text ,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `changed_at` datetime,
  `changed_by` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime,
  `created_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ordering` (`ordering`),
  KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `prefix_surveymenu` VALUES (1,NULL,NULL,NULL,0,0,'surveymenu','side','Main survey menu',1, NOW(),0,NOW(),0);
INSERT INTO `prefix_surveymenu` VALUES (2,NULL,NULL,NULL,0,0,'quickmenue','collapsed','quickmenu',1, NOW(),0,NOW(),0);

CREATE TABLE `prefix_surveymenu_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ordering` int(11) DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(168) NOT NULL DEFAULT '',
  `menu_title` varchar(168)  NOT NULL DEFAULT '',
  `menu_description` text ,
  `menu_icon` varchar(192)  NOT NULL DEFAULT '',
  `menu_icon_type` varchar(192)  NOT NULL DEFAULT '',
  `menu_class` varchar(192)  NOT NULL DEFAULT '',
  `menu_link` varchar(192)  NOT NULL DEFAULT '',
  `action` varchar(192)  NOT NULL DEFAULT '',
  `template` varchar(192)  NOT NULL DEFAULT '',
  `partial` varchar(192)  NOT NULL DEFAULT '',
  `classes` varchar(192)  NOT NULL DEFAULT '',
  `permission` varchar(192)  NOT NULL DEFAULT '',
  `permission_grade` varchar(192)  DEFAULT NULL,
  `data` text ,
  `getdatamethod` varchar(192)  NOT NULL DEFAULT '',
  `language` varchar(32)  NOT NULL DEFAULT 'en-GB',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `changed_at` datetime,
  `changed_by` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `menu_id` (`menu_id`),
  KEY `ordering` (`ordering`),
  KEY `title` (`title`),
  KEY `menu_title` (`menu_title`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `prefix_surveymenu_entries` VALUES
(1,1,NULL,1,'overview','Survey overview','Overview','Open general survey overview and quick action','list','fontawesome','','admin/survey/sa/view','','','','','','','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(2,1,NULL,2,'generalsettings','Edit survey general settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB',1, NOW(),0,NOW(),0),
(3,1,NULL,3,'surveytexts','Edit survey text elements','Survey texts','Edit survey text elements','file-text-o','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/tab_edit_view','','surveylocale','read',NULL,'_getTextEditData','en-GB',1, NOW(),0,NOW(),0),
(4,1,NULL,4,'template_options','Template options','Template options','Edit Template options for this survey','paint-brush','fontawesome','','admin/templateoptions/sa/updatesurvey','','','','','templates','read','{"render": {"link": { "pjaxed": false, "data": {"surveyid": ["survey","sid"], "gsid":["survey","gsid"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(5,1,NULL,5,'participants','Survey participants','Survey participants','Go to survey participant and token settings','user','fontawesome','','admin/tokens/sa/index/','','','','','surveysettings','update','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(6,1,NULL,6,'presentation','Presentation &amp; navigation settings','Presentation','Edit presentation and navigation settings','eye-slash','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_presentation_panel','','surveylocale','read',NULL,'_tabPresentationNavigation','en-GB',1, NOW(),0,NOW(),0),
(7,1,NULL,7,'publication','Publication and access control settings','Publication &amp; access','Edit settings for publicationa and access control','key','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_publication_panel','','surveylocale','read',NULL,'_tabPublicationAccess','en-GB',1, NOW(),0,NOW(),0),
(8,1,NULL,8,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(9,1,NULL,9,'tokens','Token handling','Participant tokens','Define how tokens should be treated or generated','users','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read',NULL,'_tabTokens','en-GB',1, NOW(),0,NOW(),0),
(10,1,NULL,10,'quotas','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(11,1,NULL,11,'assessments','Edit assessments','Assessments','Edit and look at the asessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(12,1,NULL,12,'notification','Notification and data management settings','Data management','Edit settings for notification and data management','feed','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_notification_panel','','surveylocale','read',NULL,'_tabNotificationDataManagement','en-GB',1, NOW(),0,NOW(),0),
(13,1,NULL,13,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','assessments','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(14,1,NULL,14,'panelintegration','Edit survey panel integration','Panel integration','Define panel integrations for your survey','link','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_integration_panel','','surveylocale','read',NULL,'_tabPanelIntegration','en-GB',1, NOW(),0,NOW(),0),
(15,1,NULL,15,'resources','Add/Edit resources to the survey','Resources','Add/Edit resources to the survey','file','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_resources_panel','','surveylocale','read',NULL,'_tabResourceManagement','en-GB',1, NOW(),0,NOW(),0),
(16,2,NULL,1,'activateSurvey','Activate survey','Activate survey','Activate survey','play','fontawesome','','admin/survey/sa/activate','','','','','surveyactivation','update','{\"render\": {\"isActive\": false, \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(17,2,NULL,2,'deactivateSurvey','Stop this survey','Stop this survey','Stop this survey','stop','fontawesome','','admin/survey/sa/deactivate','','','','','surveyactivation','update','{\"render\": {\"isActive\": true, \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(18,2,NULL,3,'testSurvey','Go to survey','Go to survey','Go to survey','cog','fontawesome','','survey/index/','','','','','','','{\"render\"\: {\"link\"\: {\"external\"\: true, \"data\"\: {\"sid\"\: [\"survey\",\"sid\"], \"newtest\"\: \"Y\", \"lang\"\: [\"survey\",\"language\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(19,2,NULL,4,'listQuestions','List questions','List questions','List questions','list','fontawesome','','admin/survey/sa/listquestions','','','','','surveycontent','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(20,2,NULL,5,'listQuestionGroups','List question groups','List question groups','List question groups','th-list','fontawesome','','admin/survey/sa/listquestiongroups','','','','','surveycontent','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(21,2,NULL,6,'generalsettings','Edit survey general settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB',1, NOW(),0,NOW(),0),
(22,2,NULL,7,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(23,2,NULL,8,'quotas','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(24,2,NULL,9,'assessments','Edit assessments','Assessments','Edit and look at the asessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(25,2,NULL,10,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','surveylocale','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(26,2,NULL,11,'surveyLogicFile','Survey logic file','Survey logic file','Survey logic file','sitemap','fontawesome','','admin/expressions/sa/survey_logic_file/','','','','','surveycontent','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0),
(27,2,NULL,12,'tokens','Token handling','Participant tokens','Define how tokens should be treated or generated','user','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','_tabTokens','en-GB',1, NOW(),0,NOW(),0),
(28,2,NULL,13,'cpdb','Central participant database','Central participant database','Central participant database','users','fontawesome','','admin/participants/sa/displayParticipants','','','','','tokens','read','{"render": {"link": {}}}','','en-GB',1, NOW(),0,NOW(),0),
(29,2,NULL,14,'responses','Responses','Responses','Responses','icon-browse','iconclass','','admin/responses/sa/browse/','','','','','responses','read','{\"render\"\: {\"isActive\"\: true}}','','en-GB',1, NOW(),0,NOW(),0),
(30,2,NULL,15,'statistics','Statistics','Statistics','Statistics','bar-chart','fontawesome','','admin/statistics/sa/index/','','','','','statistics','read','{\"render\"\: {\"isActive\"\: true}}','','en-GB',1, NOW(),0,NOW(),0),
(31,2,NULL,16,'reorder','Reorder questions/question groups','Reorder questions/question groups','Reorder questions/question groups','icon-organize','iconclass','','admin/survey/sa/organize/','','','','','surveycontent','update','{\"render\": {\"isActive\": false, \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',1, NOW(),0,NOW(),0);


-- -----------------------------------------------------
-- Table `prefix_templates`
-- -----------------------------------------------------
CREATE TABLE `prefix_templates` (
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `folder` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `author` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copyright` mediumtext COLLATE utf8mb4_unicode_ci,
  `license` mediumtext COLLATE utf8mb4_unicode_ci,
  `version` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_version` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `view_folder` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `files_folder` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `last_update` datetime DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `extends_templates_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



INSERT INTO `prefix_templates` VALUES
  ('default', 'default', 'Advanced Template', '2017-07-12 10:00:00', 'Louis Gac', 'louis.gac@limesurvey.org', 'https://www.limesurvey.org/', 'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.', 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.', '1.0', '3.0', 'views', 'files', '<strong>LimeSurvey Advanced Template</strong><br>A template with custom options to show what it\'s possible to do with the new engines. Each template provider will be able to offer its own option page (loaded from template)', NULL, 1, '');
INSERT INTO `prefix_templates` VALUES
  ('minimal', 'minimal', 'Minimal Template', '2017-07-12 10:00:00', 'Louis Gac', 'louis.gac@limesurvey.org', 'https://www.limesurvey.org/', 'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.', 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.', '1.0', '3.0', 'views', 'files', '<strong>LimeSurvey Minimal Template</strong><br>A clean and simple base that can be used by developers to create their own solution.', NULL, 1, '');
INSERT INTO `prefix_templates` VALUES
    ('material', 'material', 'Material Template', '2017-07-12 10:00:00', 'Louis Gac', 'louis.gac@limesurvey.org', 'https://www.limesurvey.org/', 'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.', 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.', '1.0', '3.0', 'views', 'files', '<strong>LimeSurvey Advanced Template</strong><br> A template extending default, to show the inheritance concept. Notice the options, differents from Default.<br><small>uses FezVrasta\'s Material design theme for Bootstrap 3</small>', NULL, 1, 'default');


-- -----------------------------------------------------
-- Table `prefix_template_configuration`
-- -----------------------------------------------------
CREATE TABLE `prefix_template_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `templates_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sid` int(11) DEFAULT NULL,
  `gsid` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `files_css` mediumtext COLLATE utf8mb4_unicode_ci,
  `files_js` mediumtext COLLATE utf8mb4_unicode_ci,
  `files_print_css` mediumtext COLLATE utf8mb4_unicode_ci,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cssframework_name` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cssframework_css` mediumtext COLLATE utf8mb4_unicode_ci,
  `cssframework_js` mediumtext COLLATE utf8mb4_unicode_ci,
  `packages_to_load` mediumtext COLLATE utf8mb4_unicode_ci,
  `packages_ltr` mediumtext COLLATE utf8mb4_unicode_ci,
  `packages_rtl` mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY(`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `prefix_template_configuration`  VALUES
    (1,'default',NULL,NULL,NULL,'{"add": ["css/template.css", "css/animate.css"]}','{"add": ["scripts/template.js"]}','{"add":"css/print_template.css",}','{"ajaxmode":"off","brandlogo":"on", "boxcontainer":"on", "backgroundimage":"on","animatebody":"on","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}','bootstrap','{"replace": [["css/bootstrap.css","css/flatly.css"]]}','','','','');
INSERT INTO `prefix_template_configuration`  VALUES
    (2,'minimal',NULL,NULL,NULL,'{"add": ["css/template.css"]}','{"add": ["scripts/template.js"]}','{"add":"css/print_template.css",}','{}','bootstrap','{}','','','','');
INSERT INTO `prefix_template_configuration`  VALUES
    (3,'material',NULL,NULL,NULL,'{"add": ["css/template.css", "css/bootstrap-material-design.css", "css/ripples.min.css"]}','{"add": ["scripts/template.js", "scripts/material.js", "scripts/ripples.min.js"]}','{"add":"css/print_template.css",}','{"ajaxmode":"off","brandlogo":"on", "animatebody":"on","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}','bootstrap','{"replace": [["css/bootstrap.css","css/bootstrap.css"]]}','','','','');


-- -----------------------------------------------------
-- Table `prefix_surveys_groups`
-- -----------------------------------------------------
CREATE TABLE `prefix_surveys_groups` (
  `gsid` int(11) NOT NULL,
  `name` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT 'default',
  `description` text COLLATE utf8mb4_unicode_ci,
  `order` int(11) NOT NULL,
  `owner_uid` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `prefix_surveys_groups`
  ADD PRIMARY KEY (`gsid`);

ALTER TABLE `prefix_surveys_groups`
  MODIFY `gsid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

INSERT INTO `prefix_surveys_groups` (`gsid`, `name`, `title`, `description`, `order`, `owner_uid`, `parent_id`, `created`, `modified`, `created_by`) VALUES
  (1, 'default', 'Default Survey Group', 'LimeSurvey core default survey group', 0, 1, NULL, '2017-07-20 17:09:30', '2017-07-20 17:09:30', 1);


--
-- Version Info
--
INSERT INTO `prefix_settings_global` VALUES ('DBVersion', '314');
