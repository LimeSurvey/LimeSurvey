-- MySQL dump 10.15  Distrib 10.0.29-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: localhost
-- ------------------------------------------------------
-- Server version	10.0.29-MariaDB-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `lime_answers`
--

DROP TABLE IF EXISTS `lime_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_answers` (
  `qid` int(11) NOT NULL DEFAULT '0',
  `code` varchar(5) NOT NULL DEFAULT '',
  `answer` text NOT NULL,
  `sortorder` int(11) NOT NULL,
  `assessment_value` int(11) NOT NULL DEFAULT '0',
  `language` varchar(20) NOT NULL DEFAULT 'en',
  `scale_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`qid`,`code`,`language`,`scale_id`),
  KEY `answers_idx2` (`sortorder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_answers`
--

LOCK TABLES `lime_answers` WRITE;
/*!40000 ALTER TABLE `lime_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_assessments`
--

DROP TABLE IF EXISTS `lime_assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL DEFAULT '0',
  `scope` varchar(5) NOT NULL DEFAULT '',
  `gid` int(11) NOT NULL DEFAULT '0',
  `name` text NOT NULL,
  `minimum` varchar(50) NOT NULL DEFAULT '',
  `maximum` varchar(50) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `language` varchar(20) NOT NULL DEFAULT 'en',
  PRIMARY KEY (`id`,`language`),
  KEY `assessments_idx2` (`sid`),
  KEY `assessments_idx3` (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_assessments`
--

LOCK TABLES `lime_assessments` WRITE;
/*!40000 ALTER TABLE `lime_assessments` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_assessments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_boxes`
--

DROP TABLE IF EXISTS `lime_boxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_boxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) DEFAULT NULL COMMENT 'position of the box',
  `url` text NOT NULL COMMENT 'URL the box points',
  `title` text NOT NULL COMMENT 'Box title',
  `ico` varchar(255) DEFAULT NULL COMMENT 'the ico name in font',
  `desc` text NOT NULL COMMENT 'Box description',
  `page` text NOT NULL COMMENT 'Page name where the box should be shown ',
  `usergroup` int(11) NOT NULL COMMENT 'Those boxes will be shown for that user group',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_boxes`
--

LOCK TABLES `lime_boxes` WRITE;
/*!40000 ALTER TABLE `lime_boxes` DISABLE KEYS */;
INSERT INTO `lime_boxes` VALUES (1,1,'admin/survey/sa/newsurvey','Create survey','add','Create a new survey','welcome',-2),(2,2,'admin/survey/sa/listsurveys','List surveys','list','List available surveys','welcome',-1),(3,3,'admin/globalsettings','Global settings','settings','Edit global settings','welcome',-2),(4,4,'admin/update','ComfortUpdate','shield','Stay safe and up to date','welcome',-2),(5,5,'admin/labels/sa/view','Label sets','label','Edit label sets','welcome',-2),(6,6,'admin/templates/sa/view','Template editor','templates','Edit LimeSurvey templates','welcome',-2);
/*!40000 ALTER TABLE `lime_boxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_conditions`
--

DROP TABLE IF EXISTS `lime_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_conditions` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `qid` int(11) NOT NULL DEFAULT '0',
  `cqid` int(11) NOT NULL DEFAULT '0',
  `cfieldname` varchar(50) NOT NULL DEFAULT '',
  `method` varchar(5) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  `scenario` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`cid`),
  KEY `conditions_idx2` (`qid`),
  KEY `conditions_idx3` (`cqid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_conditions`
--

LOCK TABLES `lime_conditions` WRITE;
/*!40000 ALTER TABLE `lime_conditions` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_conditions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_defaultvalues`
--

DROP TABLE IF EXISTS `lime_defaultvalues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_defaultvalues` (
  `qid` int(11) NOT NULL DEFAULT '0',
  `scale_id` int(11) NOT NULL DEFAULT '0',
  `sqid` int(11) NOT NULL DEFAULT '0',
  `language` varchar(20) NOT NULL,
  `specialtype` varchar(20) NOT NULL DEFAULT '',
  `defaultvalue` text,
  PRIMARY KEY (`qid`,`specialtype`,`language`,`scale_id`,`sqid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_defaultvalues`
--

LOCK TABLES `lime_defaultvalues` WRITE;
/*!40000 ALTER TABLE `lime_defaultvalues` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_defaultvalues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_expression_errors`
--

DROP TABLE IF EXISTS `lime_expression_errors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_expression_errors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `errortime` varchar(50) DEFAULT NULL,
  `sid` int(11) DEFAULT NULL,
  `gid` int(11) DEFAULT NULL,
  `qid` int(11) DEFAULT NULL,
  `gseq` int(11) DEFAULT NULL,
  `qseq` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `eqn` text,
  `prettyprint` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_expression_errors`
--

LOCK TABLES `lime_expression_errors` WRITE;
/*!40000 ALTER TABLE `lime_expression_errors` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_expression_errors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_failed_login_attempts`
--

DROP TABLE IF EXISTS `lime_failed_login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_failed_login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(40) NOT NULL,
  `last_attempt` varchar(20) NOT NULL,
  `number_attempts` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_failed_login_attempts`
--

LOCK TABLES `lime_failed_login_attempts` WRITE;
/*!40000 ALTER TABLE `lime_failed_login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_failed_login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_groups`
--

DROP TABLE IF EXISTS `lime_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_groups` (
  `gid` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL DEFAULT '0',
  `group_name` varchar(100) NOT NULL DEFAULT '',
  `group_order` int(11) NOT NULL DEFAULT '0',
  `description` text,
  `language` varchar(20) NOT NULL DEFAULT 'en',
  `randomization_group` varchar(20) NOT NULL DEFAULT '',
  `grelevance` text,
  PRIMARY KEY (`gid`,`language`),
  KEY `groups_idx2` (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_groups`
--

LOCK TABLES `lime_groups` WRITE;
/*!40000 ALTER TABLE `lime_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_labels`
--

DROP TABLE IF EXISTS `lime_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_labels` (
  `lid` int(11) NOT NULL DEFAULT '0',
  `code` varchar(5) NOT NULL DEFAULT '',
  `title` text,
  `sortorder` int(11) NOT NULL,
  `language` varchar(20) NOT NULL DEFAULT 'en',
  `assessment_value` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`lid`,`sortorder`,`language`),
  KEY `labels_code_idx` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_labels`
--

LOCK TABLES `lime_labels` WRITE;
/*!40000 ALTER TABLE `lime_labels` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_labels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_labelsets`
--

DROP TABLE IF EXISTS `lime_labelsets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_labelsets` (
  `lid` int(11) NOT NULL AUTO_INCREMENT,
  `label_name` varchar(100) NOT NULL DEFAULT '',
  `languages` varchar(200) DEFAULT 'en',
  PRIMARY KEY (`lid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_labelsets`
--

LOCK TABLES `lime_labelsets` WRITE;
/*!40000 ALTER TABLE `lime_labelsets` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_labelsets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_notifications`
--

DROP TABLE IF EXISTS `lime_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity` varchar(15) NOT NULL COMMENT 'Should be either survey or user',
  `entity_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(15) NOT NULL DEFAULT 'new' COMMENT 'new or read',
  `importance` int(11) NOT NULL DEFAULT '1',
  `display_class` varchar(31) DEFAULT 'default' COMMENT 'Bootstrap class, like warning, info, success',
  `hash` varchar(64) DEFAULT NULL COMMENT 'Hash of title, message and entity to avoid duplication',
  `created` datetime NOT NULL,
  `first_read` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entity` (`entity`,`entity_id`,`status`),
  KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_notifications`
--

LOCK TABLES `lime_notifications` WRITE;
/*!40000 ALTER TABLE `lime_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_participant_attribute`
--

DROP TABLE IF EXISTS `lime_participant_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_participant_attribute` (
  `participant_id` varchar(50) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`participant_id`,`attribute_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_participant_attribute`
--

LOCK TABLES `lime_participant_attribute` WRITE;
/*!40000 ALTER TABLE `lime_participant_attribute` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_participant_attribute` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_participant_attribute_names`
--

DROP TABLE IF EXISTS `lime_participant_attribute_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_participant_attribute_names` (
  `attribute_id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_type` varchar(4) NOT NULL,
  `defaultname` varchar(255) NOT NULL,
  `visible` varchar(5) NOT NULL,
  PRIMARY KEY (`attribute_id`,`attribute_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_participant_attribute_names`
--

LOCK TABLES `lime_participant_attribute_names` WRITE;
/*!40000 ALTER TABLE `lime_participant_attribute_names` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_participant_attribute_names` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_participant_attribute_names_lang`
--

DROP TABLE IF EXISTS `lime_participant_attribute_names_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_participant_attribute_names_lang` (
  `attribute_id` int(11) NOT NULL,
  `attribute_name` varchar(255) NOT NULL,
  `lang` varchar(20) NOT NULL,
  PRIMARY KEY (`attribute_id`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_participant_attribute_names_lang`
--

LOCK TABLES `lime_participant_attribute_names_lang` WRITE;
/*!40000 ALTER TABLE `lime_participant_attribute_names_lang` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_participant_attribute_names_lang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_participant_attribute_values`
--

DROP TABLE IF EXISTS `lime_participant_attribute_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_participant_attribute_values` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT,
  `attribute_id` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`value_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_participant_attribute_values`
--

LOCK TABLES `lime_participant_attribute_values` WRITE;
/*!40000 ALTER TABLE `lime_participant_attribute_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_participant_attribute_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_participant_shares`
--

DROP TABLE IF EXISTS `lime_participant_shares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_participant_shares` (
  `participant_id` varchar(50) NOT NULL,
  `share_uid` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  `can_edit` varchar(5) NOT NULL,
  PRIMARY KEY (`participant_id`,`share_uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_participant_shares`
--

LOCK TABLES `lime_participant_shares` WRITE;
/*!40000 ALTER TABLE `lime_participant_shares` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_participant_shares` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_participants`
--

DROP TABLE IF EXISTS `lime_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_participants` (
  `participant_id` varchar(50) NOT NULL,
  `firstname` varchar(150) DEFAULT NULL,
  `lastname` varchar(150) DEFAULT NULL,
  `email` text,
  `language` varchar(40) DEFAULT NULL,
  `blacklisted` varchar(1) NOT NULL,
  `owner_uid` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`participant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_participants`
--

LOCK TABLES `lime_participants` WRITE;
/*!40000 ALTER TABLE `lime_participants` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_permissions`
--

DROP TABLE IF EXISTS `lime_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL,
  `create_p` int(11) NOT NULL DEFAULT '0',
  `read_p` int(11) NOT NULL DEFAULT '0',
  `update_p` int(11) NOT NULL DEFAULT '0',
  `delete_p` int(11) NOT NULL DEFAULT '0',
  `import_p` int(11) NOT NULL DEFAULT '0',
  `export_p` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxPermissions` (`entity_id`,`entity`,`permission`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_permissions`
--

LOCK TABLES `lime_permissions` WRITE;
/*!40000 ALTER TABLE `lime_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_plugin_settings`
--

DROP TABLE IF EXISTS `lime_plugin_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_plugin_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_id` int(11) NOT NULL,
  `model` varchar(50) DEFAULT NULL,
  `model_id` int(11) DEFAULT NULL,
  `key` varchar(50) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_plugin_settings`
--

LOCK TABLES `lime_plugin_settings` WRITE;
/*!40000 ALTER TABLE `lime_plugin_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_plugin_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_plugins`
--

DROP TABLE IF EXISTS `lime_plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `version` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_plugins`
--

LOCK TABLES `lime_plugins` WRITE;
/*!40000 ALTER TABLE `lime_plugins` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_plugins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_question_attributes`
--

DROP TABLE IF EXISTS `lime_question_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_question_attributes` (
  `qaid` int(11) NOT NULL AUTO_INCREMENT,
  `qid` int(11) NOT NULL DEFAULT '0',
  `attribute` varchar(50) DEFAULT NULL,
  `value` text,
  `language` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`qaid`),
  KEY `question_attributes_idx2` (`qid`),
  KEY `question_attributes_idx3` (`attribute`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_question_attributes`
--

LOCK TABLES `lime_question_attributes` WRITE;
/*!40000 ALTER TABLE `lime_question_attributes` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_question_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_questions`
--

DROP TABLE IF EXISTS `lime_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_questions` (
  `qid` int(11) NOT NULL AUTO_INCREMENT,
  `parent_qid` int(11) NOT NULL DEFAULT '0',
  `sid` int(11) NOT NULL DEFAULT '0',
  `gid` int(11) NOT NULL DEFAULT '0',
  `type` varchar(1) NOT NULL DEFAULT 'T',
  `title` varchar(20) NOT NULL DEFAULT '',
  `question` text NOT NULL,
  `preg` text,
  `help` text,
  `other` varchar(1) NOT NULL DEFAULT 'N',
  `mandatory` varchar(1) DEFAULT NULL,
  `question_order` int(11) NOT NULL,
  `language` varchar(20) NOT NULL DEFAULT 'en',
  `scale_id` int(11) NOT NULL DEFAULT '0',
  `same_default` int(11) NOT NULL DEFAULT '0' COMMENT 'Saves if user set to use the same default value across languages in default options dialog',
  `relevance` text,
  `modulename` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`qid`,`language`),
  KEY `questions_idx2` (`sid`),
  KEY `questions_idx3` (`gid`),
  KEY `questions_idx4` (`type`),
  KEY `parent_qid_idx` (`parent_qid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_questions`
--

LOCK TABLES `lime_questions` WRITE;
/*!40000 ALTER TABLE `lime_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_quota`
--

DROP TABLE IF EXISTS `lime_quota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_quota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `qlimit` int(11) DEFAULT NULL,
  `action` int(11) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT '1',
  `autoload_url` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `quota_idx2` (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_quota`
--

LOCK TABLES `lime_quota` WRITE;
/*!40000 ALTER TABLE `lime_quota` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_quota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_quota_languagesettings`
--

DROP TABLE IF EXISTS `lime_quota_languagesettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_quota_languagesettings` (
  `quotals_id` int(11) NOT NULL AUTO_INCREMENT,
  `quotals_quota_id` int(11) NOT NULL DEFAULT '0',
  `quotals_language` varchar(45) NOT NULL DEFAULT 'en',
  `quotals_name` varchar(255) DEFAULT NULL,
  `quotals_message` text NOT NULL,
  `quotals_url` varchar(255) DEFAULT NULL,
  `quotals_urldescrip` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`quotals_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_quota_languagesettings`
--

LOCK TABLES `lime_quota_languagesettings` WRITE;
/*!40000 ALTER TABLE `lime_quota_languagesettings` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_quota_languagesettings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_quota_members`
--

DROP TABLE IF EXISTS `lime_quota_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_quota_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) DEFAULT NULL,
  `qid` int(11) DEFAULT NULL,
  `quota_id` int(11) DEFAULT NULL,
  `code` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sid` (`sid`,`qid`,`quota_id`,`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_quota_members`
--

LOCK TABLES `lime_quota_members` WRITE;
/*!40000 ALTER TABLE `lime_quota_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_quota_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_saved_control`
--

DROP TABLE IF EXISTS `lime_saved_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_saved_control` (
  `scid` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL DEFAULT '0',
  `srid` int(11) NOT NULL DEFAULT '0',
  `identifier` text NOT NULL,
  `access_code` text NOT NULL,
  `email` varchar(254) DEFAULT NULL,
  `ip` text NOT NULL,
  `saved_thisstep` text NOT NULL,
  `status` varchar(1) NOT NULL DEFAULT '',
  `saved_date` datetime NOT NULL,
  `refurl` text,
  PRIMARY KEY (`scid`),
  KEY `saved_control_idx2` (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_saved_control`
--

LOCK TABLES `lime_saved_control` WRITE;
/*!40000 ALTER TABLE `lime_saved_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_saved_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_sessions`
--

DROP TABLE IF EXISTS `lime_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_sessions` (
  `id` varchar(32) NOT NULL,
  `expire` int(11) DEFAULT NULL,
  `data` longblob,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_sessions`
--

LOCK TABLES `lime_sessions` WRITE;
/*!40000 ALTER TABLE `lime_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_settings_global`
--

DROP TABLE IF EXISTS `lime_settings_global`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_settings_global` (
  `stg_name` varchar(50) NOT NULL DEFAULT '',
  `stg_value` text NOT NULL,
  PRIMARY KEY (`stg_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_settings_global`
--

LOCK TABLES `lime_settings_global` WRITE;
/*!40000 ALTER TABLE `lime_settings_global` DISABLE KEYS */;
INSERT INTO `lime_settings_global` VALUES ('DBVersion','296');
/*!40000 ALTER TABLE `lime_settings_global` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_settings_user`
--

DROP TABLE IF EXISTS `lime_settings_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_settings_user` (
  `uid` int(11) NOT NULL,
  `entity` varchar(15) NOT NULL DEFAULT '',
  `entity_id` varchar(31) NOT NULL DEFAULT '',
  `stg_name` varchar(63) NOT NULL,
  `stg_value` text,
  PRIMARY KEY (`uid`,`entity`,`entity_id`,`stg_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_settings_user`
--

LOCK TABLES `lime_settings_user` WRITE;
/*!40000 ALTER TABLE `lime_settings_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_settings_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_survey_links`
--

DROP TABLE IF EXISTS `lime_survey_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_survey_links` (
  `participant_id` varchar(50) NOT NULL,
  `token_id` int(11) NOT NULL,
  `survey_id` int(11) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_invited` datetime DEFAULT NULL,
  `date_completed` datetime DEFAULT NULL,
  PRIMARY KEY (`participant_id`,`token_id`,`survey_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_survey_links`
--

LOCK TABLES `lime_survey_links` WRITE;
/*!40000 ALTER TABLE `lime_survey_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_survey_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_survey_url_parameters`
--

DROP TABLE IF EXISTS `lime_survey_url_parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_survey_url_parameters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sid` int(11) NOT NULL,
  `parameter` varchar(50) NOT NULL,
  `targetqid` int(11) DEFAULT NULL,
  `targetsqid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_survey_url_parameters`
--

LOCK TABLES `lime_survey_url_parameters` WRITE;
/*!40000 ALTER TABLE `lime_survey_url_parameters` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_survey_url_parameters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_surveymenu`
--

DROP TABLE IF EXISTS `lime_surveymenu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_surveymenu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `survey_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT '0',
  `level` int(11) DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `position` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'side',
  `description` text COLLATE utf8mb4_unicode_ci,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `changed_by` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order` (`order`),
  KEY `title` (`title`(191))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_surveymenu`
--

LOCK TABLES `lime_surveymenu` WRITE;
/*!40000 ALTER TABLE `lime_surveymenu` DISABLE KEYS */;
INSERT INTO `lime_surveymenu` VALUES (1,NULL,NULL,NULL,0,0,'surveymenu','side','Main survey menu','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(2,NULL,NULL,NULL,0,0,'quickmenue','collapsed','quickmenu','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0);
/*!40000 ALTER TABLE `lime_surveymenu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_surveymenu_entries`
--

DROP TABLE IF EXISTS `lime_surveymenu_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_surveymenu_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
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
  `created_at` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `menu_id` (`menu_id`),
  KEY `order` (`order`),
  KEY `title` (`title`(191)),
  KEY `menu_title` (`menu_title`(191)),
  CONSTRAINT `lime_surveymenu_entries_ibfk_1` FOREIGN KEY (`menu_id`) REFERENCES `lime_surveymenu` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_surveymenu_entries`
--

LOCK TABLES `lime_surveymenu_entries` WRITE;
/*!40000 ALTER TABLE `lime_surveymenu_entries` DISABLE KEYS */;
INSERT INTO `lime_surveymenu_entries` VALUES (1,1,NULL,1,'overview','Survey overview','Overview','Open general survey overview and quick action','list','fontawesome','','admin/survey/sa/view','','','','','','',NULL,'','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(2,1,NULL,2,'generalsettings','Edit survey general settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(3,1,NULL,3,'surveytexts','Edit survey text elements','Survey texts','Edit survey text elements','file-text-o','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/tab_edit_view','','surveylocale','read',NULL,'_getTextEditData','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(4,1,NULL,4,'participants','Survey participants','Survey participants','Go to survey participant and token settings','user','fontawesome','','admin/tokens/sa/index/','','','','','surveysettings','update',NULL,'','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(5,1,NULL,4,'presentation','Presentation &amp; navigation settings','Presentation','Edit presentation and navigation settings','eye-slash','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_presentation_panel','','surveylocale','read',NULL,'_tabPresentationNavigation','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(6,1,NULL,5,'publication','Publication and access control settings','Publication &amp; access','Edit settings for publicationa and access control','key','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_publication_panel','','surveylocale','read',NULL,'_tabPublicationAccess','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(7,1,NULL,6,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read',NULL,'','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(8,1,NULL,7,'tokens','Token handling','Participant tokens','Define how tokens should be treated or generated','users','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read',NULL,'_tabTokens','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(9,1,NULL,8,'quotas','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read',NULL,'','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(10,1,NULL,9,'assessments','Edit assessments','Assessments','Edit and look at the asessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read',NULL,'','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(11,1,NULL,10,'notification','Notification and data management settings','Data management','Edit settings for notification and data management','feed','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_notification_panel','','surveylocale','read',NULL,'_tabNotificationDataManagement','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(12,1,NULL,11,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','assessments','read',NULL,'','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(13,1,NULL,12,'panelintegration','Edit survey panel integration','Panel integration','Define panel integrations for your survey','link','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_integration_panel','','surveylocale','read',NULL,'_tabPanelIntegration','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(14,1,NULL,13,'ressources','Add/Edit ressources to the survey','Ressources','Add/Edit ressources to the survey','file','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_resources_panel','','surveylocale','read',NULL,'_tabResourceManagement','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(15,2,NULL,1,'activateSurvey','Activate survey','Activate survey','Activate survey','play','fontawesome','','admin/survey/sa/activate','','','','','surveyactivation','update','{\"render\": {\"isActive\": false}}','','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(16,2,NULL,2,'deactivateSurvey','Stop this survey','Stop this survey','Stop this survey','stop','fontawesome','','admin/survey/sa/deactivate','','','','','surveyactivation','update','{\"render\": {\"isActive\": true}}','','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(17,2,NULL,3,'testSurvey','Go to survey','Go to survey','Go to survey','cog','fontawesome','','survey/index/','','','','','','','{\"render\": {\"link\": {\"external\": true, \"data\": {\"sid\": [\"this\",\"getSurveyId\"], newtest: \"Y\", lang: [\"this\",\"getLanguage\"]}}}','','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(18,2,NULL,4,'listQuestions','List questions','List questions','List questions','list','fontawesome','','admin/survey/sa/listquestions','','','','','surveycontent','read',NULL,'','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(19,2,NULL,5,'listQuestionGroups','List question groups','List question groups','List question groups','th-list','fontawesome','','admin/survey/sa/listquestiongroups','','','','','surveycontent','read',NULL,'','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(20,2,NULL,6,'generalsettings','Edit survey general settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(21,2,NULL,7,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read',NULL,'','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(22,2,NULL,8,'quotas','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read',NULL,'','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(23,2,NULL,9,'assessments','Edit assessments','Assessments','Edit and look at the asessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read',NULL,'','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(24,2,NULL,10,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','surveylocale','read',NULL,'','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(25,2,NULL,11,'surveyLogicFile','Survey logic file','Survey logic file','Survey logic file','sitemap','fontawesome','','admin/expressions/sa/survey_logic_file/','','','','','surveycontent','read',NULL,'','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(26,2,NULL,12,'tokens','Token handling','Participant tokens','Define how tokens should be treated or generated','user','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read',NULL,'_tabTokens','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(27,2,NULL,13,'cpdb','Central participant database','Central participant database','Central participant database','users','fontawesome','','admin/participants/sa/displayParticipants','','','','','tokens','read','{render: {\"link\": {}}','','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(28,2,NULL,14,'responses','Responses','Responses','Responses','icon-browse','iconclass','','admin/responses/sa/browse/','','','','','responses','read','{\"render\": {\"isActive\": true}}','','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(29,2,NULL,15,'statistics','Statistics','Statistics','Statistics','bar-chart','fontawesome','','admin/statistics/sa/index/','','','','','statistics','read','{\"render\": {\"isActive\": true}}','','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0),(30,2,NULL,16,'reorder','Reorder questions/question groups','Reorder questions/question groups','Reorder questions/question groups','icon-organize','iconclass','','admin/survey/sa/organize/','','','','','surveycontent','update','{\"render\": {\"isActive\": false}}','','en-GB','2017-07-17 14:56:23',0,'2017-07-17 16:56:23',0);
/*!40000 ALTER TABLE `lime_surveymenu_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_surveys`
--

DROP TABLE IF EXISTS `lime_surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_surveys` (
  `sid` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `admin` varchar(50) DEFAULT NULL,
  `active` varchar(1) NOT NULL DEFAULT 'N',
  `expires` datetime DEFAULT NULL,
  `startdate` datetime DEFAULT NULL,
  `adminemail` varchar(254) DEFAULT NULL,
  `anonymized` varchar(1) NOT NULL DEFAULT 'N',
  `faxto` varchar(20) DEFAULT NULL,
  `format` varchar(1) DEFAULT NULL,
  `savetimings` varchar(1) NOT NULL DEFAULT 'N',
  `template` varchar(100) DEFAULT 'default',
  `language` varchar(50) DEFAULT NULL,
  `additional_languages` varchar(255) DEFAULT NULL,
  `datestamp` varchar(1) NOT NULL DEFAULT 'N',
  `usecookie` varchar(1) NOT NULL DEFAULT 'N',
  `allowregister` varchar(1) NOT NULL DEFAULT 'N',
  `allowsave` varchar(1) NOT NULL DEFAULT 'Y',
  `autonumber_start` int(11) NOT NULL DEFAULT '0',
  `autoredirect` varchar(1) NOT NULL DEFAULT 'N',
  `allowprev` varchar(1) NOT NULL DEFAULT 'N',
  `printanswers` varchar(1) NOT NULL DEFAULT 'N',
  `ipaddr` varchar(1) NOT NULL DEFAULT 'N',
  `refurl` varchar(1) NOT NULL DEFAULT 'N',
  `datecreated` date DEFAULT NULL,
  `publicstatistics` varchar(1) NOT NULL DEFAULT 'N',
  `publicgraphs` varchar(1) NOT NULL DEFAULT 'N',
  `listpublic` varchar(1) NOT NULL DEFAULT 'N',
  `htmlemail` varchar(1) NOT NULL DEFAULT 'N',
  `sendconfirmation` varchar(1) NOT NULL DEFAULT 'Y',
  `tokenanswerspersistence` varchar(1) NOT NULL DEFAULT 'N',
  `assessments` varchar(1) NOT NULL DEFAULT 'N',
  `usecaptcha` varchar(1) NOT NULL DEFAULT 'N',
  `usetokens` varchar(1) NOT NULL DEFAULT 'N',
  `bounce_email` varchar(254) DEFAULT NULL,
  `attributedescriptions` text,
  `emailresponseto` text,
  `emailnotificationto` text,
  `tokenlength` int(11) NOT NULL DEFAULT '15',
  `showxquestions` varchar(1) DEFAULT 'Y',
  `showgroupinfo` varchar(1) DEFAULT 'B',
  `shownoanswer` varchar(1) DEFAULT 'Y',
  `showqnumcode` varchar(1) DEFAULT 'X',
  `bouncetime` int(11) DEFAULT NULL,
  `bounceprocessing` varchar(1) DEFAULT 'N',
  `bounceaccounttype` varchar(4) DEFAULT NULL,
  `bounceaccounthost` varchar(200) DEFAULT NULL,
  `bounceaccountpass` varchar(100) DEFAULT NULL,
  `bounceaccountencryption` varchar(3) DEFAULT NULL,
  `bounceaccountuser` varchar(200) DEFAULT NULL,
  `showwelcome` varchar(1) DEFAULT 'Y',
  `showprogress` varchar(1) DEFAULT 'Y',
  `questionindex` int(11) NOT NULL DEFAULT '0',
  `navigationdelay` int(11) NOT NULL DEFAULT '0',
  `nokeyboard` varchar(1) DEFAULT 'N',
  `alloweditaftercompletion` varchar(1) DEFAULT 'N',
  `googleanalyticsstyle` varchar(1) DEFAULT NULL,
  `googleanalyticsapikey` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_surveys`
--

LOCK TABLES `lime_surveys` WRITE;
/*!40000 ALTER TABLE `lime_surveys` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_surveys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_surveys_languagesettings`
--

DROP TABLE IF EXISTS `lime_surveys_languagesettings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_surveys_languagesettings` (
  `surveyls_survey_id` int(11) NOT NULL,
  `surveyls_language` varchar(45) NOT NULL DEFAULT 'en',
  `surveyls_title` varchar(200) NOT NULL,
  `surveyls_description` text,
  `surveyls_welcometext` text,
  `surveyls_endtext` text,
  `surveyls_url` text,
  `surveyls_urldescription` varchar(255) DEFAULT NULL,
  `surveyls_email_invite_subj` varchar(255) DEFAULT NULL,
  `surveyls_email_invite` text,
  `surveyls_email_remind_subj` varchar(255) DEFAULT NULL,
  `surveyls_email_remind` text,
  `surveyls_email_register_subj` varchar(255) DEFAULT NULL,
  `surveyls_email_register` text,
  `surveyls_email_confirm_subj` varchar(255) DEFAULT NULL,
  `surveyls_email_confirm` text,
  `surveyls_dateformat` int(11) NOT NULL DEFAULT '1',
  `surveyls_attributecaptions` text,
  `email_admin_notification_subj` varchar(255) DEFAULT NULL,
  `email_admin_notification` text,
  `email_admin_responses_subj` varchar(255) DEFAULT NULL,
  `email_admin_responses` text,
  `surveyls_numberformat` int(11) NOT NULL DEFAULT '0',
  `attachments` text,
  PRIMARY KEY (`surveyls_survey_id`,`surveyls_language`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_surveys_languagesettings`
--

LOCK TABLES `lime_surveys_languagesettings` WRITE;
/*!40000 ALTER TABLE `lime_surveys_languagesettings` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_surveys_languagesettings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_template_configuration`
--

DROP TABLE IF EXISTS `lime_template_configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_template_configuration` (
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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_template_configuration`
--

LOCK TABLES `lime_template_configuration` WRITE;
/*!40000 ALTER TABLE `lime_template_configuration` DISABLE KEYS */;
INSERT INTO `lime_template_configuration` VALUES (1,'default',NULL,NULL,NULL,'{\"add\": [\"css/template.css\", \"css/animate.css\"]}','{\"add\": [\"scripts/template.js\"]}','{\"add\":\"css/print_template.css\",}','{\"ajaxmode\":\"on\",\"brandlogo\":\"on\",\"backgroundimage\":\"on\",\"animatebody\":\"on\",\"bodyanimation\":\"lightSpeedIn\",\"animatequestion\":\"on\",\"questionanimation\":\"flipInX\",\"animatealert\":\"on\",\"alertanimation\":\"shake\"}','bootstrap','{\"replace\": [\"css/bootstrap.css\"]}','','','','');
/*!40000 ALTER TABLE `lime_template_configuration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_templates`
--

DROP TABLE IF EXISTS `lime_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_templates` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_templates`
--

LOCK TABLES `lime_templates` WRITE;
/*!40000 ALTER TABLE `lime_templates` DISABLE KEYS */;
INSERT INTO `lime_templates` VALUES ('default','default','Advanced Template','2017-07-12 08:00:00','Louis Gac','louis.gac@limesurvey.org','https://www.limesurvey.org/','Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.','License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.','1.0','3.0','views','files','LimeSurvey Advanced Template:\\r\\nMany options for user customizations. \\r\\n',NULL,1,'');
/*!40000 ALTER TABLE `lime_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_user_groups`
--

DROP TABLE IF EXISTS `lime_user_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_user_groups` (
  `ugid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `owner_id` int(11) NOT NULL,
  PRIMARY KEY (`ugid`),
  UNIQUE KEY `lug_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_user_groups`
--

LOCK TABLES `lime_user_groups` WRITE;
/*!40000 ALTER TABLE `lime_user_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_user_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_user_in_groups`
--

DROP TABLE IF EXISTS `lime_user_in_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_user_in_groups` (
  `ugid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  PRIMARY KEY (`ugid`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_user_in_groups`
--

LOCK TABLES `lime_user_in_groups` WRITE;
/*!40000 ALTER TABLE `lime_user_in_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_user_in_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lime_users`
--

DROP TABLE IF EXISTS `lime_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lime_users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `users_name` varchar(64) NOT NULL DEFAULT '',
  `password` blob NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `lang` varchar(20) DEFAULT NULL,
  `email` varchar(254) DEFAULT NULL,
  `htmleditormode` varchar(7) DEFAULT 'default',
  `templateeditormode` varchar(7) NOT NULL DEFAULT 'default',
  `questionselectormode` varchar(7) NOT NULL DEFAULT 'default',
  `one_time_pw` blob,
  `dateformat` int(11) NOT NULL DEFAULT '1',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `users_name` (`users_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lime_users`
--

LOCK TABLES `lime_users` WRITE;
/*!40000 ALTER TABLE `lime_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `lime_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-07-17 16:56:23
