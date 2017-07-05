
CREATE TABLE `lime_surveymenu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `survey_id` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT '0',
  `level` int(11) DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `changed_by` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `order` (`order`),
  KEY `title` (`title`(250))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `lime_surveymenu` VALUES (1,NULL,NULL,0,0,'surveymenu','Main survey menu','2017-07-03 02:25:11',0,'2017-07-03 02:25:11',0);

CREATE TABLE `lime_surveymenu_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT '0',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `menu_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `menu_description` text COLLATE utf8mb4_unicode_ci,
  `menu_icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `menu_icon_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `menu_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `menu_link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `template` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `partial` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `classes` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `permission` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `permission_grade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` text COLLATE utf8mb4_unicode_ci,
  `getdatamethod` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `language` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en-GB',
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `changed_by` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `menu_id` (`menu_id`),
  KEY `order` (`order`),
  KEY `title` (`title`(191)),
  KEY `menu_title` (`menu_title`(191))
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `lime_surveymenu_entries` VALUES 
(1,1,1,'overview','Survey overview','Overview','Open general survey overview and quick action','list','fontawesome','','admin/survey/sa/view','','','','','','',NULL,'','en-GB','2017-06-29 11:32:53',0,'2017-06-29 10:27:17',0),
(2,1,2,'generalsettings','Edit survey general settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB','2017-06-29 11:32:53',0,'2017-06-28 12:23:05',0),
(3,1,3,'surveytexts','Edit survey text elements','Survey texts','Edit survey text elements','file-text-o','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/tab_edit_view','','surveylocale','read',NULL,'_getTextEditData','en-GB','2017-06-29 11:32:53',0,'2017-06-28 12:23:05',0),
(4,1,4,'presentation','Presentation &amp; navigation settings','Presentation','Edit presentation and navigation settings','eye-slash','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_presentation_panel','','surveylocale','read',NULL,'_tabPresentationNavigation','en-GB','2017-06-29 11:32:53',0,'2017-06-28 12:23:05',0),
(5,1,5,'publication','Publication and access control settings','Publication &amp; access','Edit settings for publicationa and access control','key','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_publication_panel','','surveylocale','read',NULL,'_tabPublicationAccess','en-GB','2017-06-29 11:32:53',0,'2017-06-28 12:23:05',0),
(6,1,6,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read',NULL,'','en-GB','2017-06-29 12:05:38',0,'2017-06-29 11:31:40',0),
(7,1,7,'tokens','Token handling','Participant tokens','Define how tokens should be treated or generated','users','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read',NULL,'_tabTokens','en-GB','2017-06-29 11:32:53',0,'2017-06-28 12:23:05',0),
(8,1,8,'quotas','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read',NULL,'','en-GB','2017-06-29 11:56:59',0,'2017-06-29 11:56:59',0),
(9,1,9,'assessments','Edit assessments','Assessments','Edit and look at the asessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read',NULL,'','en-GB','2017-06-29 12:05:09',0,'2017-06-29 12:05:09',0),
(10,1,10,'notification','Notification and data management settings','Data management','Edit settings for notification and data management','feed','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_notification_panel','','surveylocale','read',NULL,'_tabNotificationDataManagement','en-GB','2017-06-30 08:52:19',0,'2017-06-30 08:51:05',0),
(11,1,11,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','assessments','read',NULL,'','en-GB','2017-06-29 12:05:09',0,'2017-06-29 12:05:09',0),
(12,1,12,'panelintegration','Edit survey panel integration','Panel integration','Define panel integrations for your survey','link','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_integration_panel','','surveylocale','read',NULL,'_tabPanelIntegration','en-GB','2017-06-29 11:32:53',0,'2017-06-28 12:23:05',0),
(13,1,13,'ressources','Add/Edit ressources to the survey','Ressources','Add/Edit ressources to the survey','file','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_resources_panel','','surveylocale','read',NULL,'_tabResourceManagement','en-GB','2017-06-29 11:32:53',0,'2017-06-28 12:23:05',0);

