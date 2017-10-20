SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;
SET search_path = public, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;


--
-- Table structure for table answers
--
CREATE TABLE prefix_answers (
    "qid" integer DEFAULT 0 NOT NULL,
    "code" character varying(5) DEFAULT '' NOT NULL,
    "answer" text NOT NULL,
    "sortorder" integer NOT NULL,
    "language" character varying(20) DEFAULT 'en',
    "assessment_value" integer DEFAULT 0 NOT NULL,
    "scale_id" integer DEFAULT 0 NOT NULL,
    CONSTRAINT prefix_answers_pkey PRIMARY KEY (qid, code, "language", scale_id)
);


--
-- Table structure for table assessments
--
CREATE TABLE prefix_assessments (
    "id" serial NOT NULL,
    "sid" integer DEFAULT 0 NOT NULL,
    "scope" character varying(5) DEFAULT '' NOT NULL,
    "gid" integer DEFAULT 0 NOT NULL,
    "name" text NOT NULL,
    "minimum" character varying(50) DEFAULT '' NOT NULL,
    "maximum" character varying(50) DEFAULT '' NOT NULL,
    "message" text NOT NULL,
    "language" character varying(20) DEFAULT 'en' NOT NULL,
    CONSTRAINT prefix_assessments_pkey PRIMARY KEY (id,language)
);


--
-- Table structure for table conditions
--
CREATE TABLE prefix_conditions (
    "cid" serial NOT NULL,
    "qid" integer DEFAULT 0 NOT NULL,
    "cqid" integer DEFAULT 0 NOT NULL,
    "cfieldname" character varying(50) DEFAULT '' NOT NULL,
    "method" character varying(5) DEFAULT '' NOT NULL,
    "value" character varying(255) DEFAULT '' NOT NULL,
    "scenario" integer DEFAULT 1 NOT NULL,
    CONSTRAINT prefix_conditions_pkey PRIMARY KEY (cid)
);


--
-- Table structure for table defaultvalues
--
CREATE TABLE prefix_defaultvalues (
    "qid" integer NOT NULL default '0',
    "scale_id" integer NOT NULL default '0',
    "sqid" integer NOT NULL default '0',
    "language" character varying(20) NOT NULL,
    "specialtype" character varying(20) NOT NULL default '',
    "defaultvalue" text,
    CONSTRAINT prefix_defaultvalues_pkey PRIMARY KEY (qid , specialtype, language, scale_id, sqid)
);


--
-- Table structure for table expression_errors
--

CREATE TABLE prefix_expression_errors (
  "id" serial NOT NULL,
  "errortime" character varying(50),
  "sid" integer,
  "gid" integer,
  "qid" integer,
  "gseq" integer,
  "qseq" integer,
  "type" character varying(50) ,
  "eqn" text,
  "prettyprint" text,
  CONSTRAINT prefix_expression_errors_pkey PRIMARY KEY (id)
);


--
-- Create failed_login_attempts
--
CREATE TABLE prefix_failed_login_attempts (
  "id" serial PRIMARY KEY NOT NULL,
  "ip" character varying(40) NOT NULL,
  "last_attempt" character varying(20) NOT NULL,
  "number_attempts" integer NOT NULL
);


--
-- Table structure for table groups
--
CREATE TABLE prefix_groups (
    "gid" serial NOT NULL,
    "sid" integer DEFAULT 0 NOT NULL,
    "group_name" character varying(100) DEFAULT '' NOT NULL,
    "group_order" integer DEFAULT 0 NOT NULL,
    "description" text,
    "language" character varying(20) DEFAULT 'en',
    "randomization_group" character varying(20) DEFAULT '' NOT NULL,
    "grelevance" text DEFAULT NULL,
    CONSTRAINT prefix_groups_pkey PRIMARY KEY (gid, "language")
);


--
-- Table structure for table labels
--
CREATE TABLE prefix_labels (
    "lid" integer DEFAULT 0 NOT NULL,
    "code" character varying(5) DEFAULT '' NOT NULL,
    "title" text,
    "sortorder" integer NOT NULL,
    "assessment_value" integer DEFAULT 0 NOT NULL,
    "language" character varying(20) DEFAULT 'en' NOT NULL,
    CONSTRAINT prefix_labels_pkey PRIMARY KEY (lid, sortorder, "language")
);


--
-- Table structure for table labelsets
--
CREATE TABLE prefix_labelsets (
    "lid" serial NOT NULL,
    "label_name" character varying(100) DEFAULT '' NOT NULL,
    "languages" character varying(200) DEFAULT 'en',
    CONSTRAINT prefix_labelsets_pkey PRIMARY KEY (lid)
);


--
-- Table structure for table participant_attribute
--
CREATE TABLE prefix_participant_attribute (
  "participant_id" character varying( 50 ) NOT NULL,
  "attribute_id" integer NOT NULL,
  "value" text NOT NULL,
  CONSTRAINT prefix_participant_attribut_pkey PRIMARY KEY (participant_id,attribute_id)
);


--
-- Table structure for table participant_attribute_lang
--
CREATE TABLE prefix_participant_attribute_names_lang (
  "attribute_id" integer NOT NULL,
  "attribute_name" character varying( 255 ) NOT NULL,
  "lang" character varying( 20 ) NOT NULL,
  CONSTRAINT prefix_participant_attribute_names_lang_pkey PRIMARY KEY (attribute_id,lang)
);


--
-- Table structure for table participant_attribute_names
--
CREATE TABLE prefix_participant_attribute_names (
  "attribute_id" serial NOT NULL,
  "attribute_type" character varying( 4 ) NOT NULL,
  "defaultname" character varying(255) NOT NULL,
  "visible" character varying( 5 ) NOT NULL,
  CONSTRAINT prefix_participant_attribute_names_pkey PRIMARY KEY (attribute_id, attribute_type)
);


--
-- Table structure for table participant_attribute_values
--
CREATE TABLE prefix_participant_attribute_values (
  "value_id" serial PRIMARY KEY NOT NULL,
  "attribute_id" integer NOT NULL,
  "value" text NOT NULL
);


--
-- Table structure for table participant_shares
--
CREATE TABLE prefix_participant_shares (
  "participant_id" character varying( 50 ) NOT NULL,
  "share_uid" integer NOT NULL,
  "date_added" timestamp NOT NULL,
  "can_edit" character varying( 5 ) NOT NULL,
  CONSTRAINT prefix_participant_shares_pkey PRIMARY KEY (participant_id,share_uid)
);


--
-- Table structure for table participants
--
CREATE TABLE prefix_participants (
  "participant_id" character varying(50) PRIMARY KEY NOT NULL,
  "firstname" character varying(150),
  "lastname" character varying(150),
  "email" text,
  "language" character varying(40),
  "blacklisted" character varying(1) NOT NULL,
  "owner_uid" integer NOT NULL,
  "created_by" integer NOT NULL,
  "created" timestamp,
  "modified" timestamp
);


--
-- Table structure for table permissions
--
CREATE TABLE prefix_permissions (
    "id" serial NOT NULL,
    "entity" character varying(50) NOT NULL,
    "entity_id" integer NOT NULL,
    "uid" integer NOT NULL,
    "permission" character varying(100) NOT NULL,
    "create_p" integer DEFAULT 0 NOT NULL,
    "read_p" integer DEFAULT 0 NOT NULL,
    "update_p" integer DEFAULT 0 NOT NULL,
    "delete_p" integer DEFAULT 0 NOT NULL,
    "import_p" integer DEFAULT 0 NOT NULL,
    "export_p" integer DEFAULT 0 NOT NULL,
    CONSTRAINT prefix_permissions_pkey PRIMARY KEY (id)
);


--
-- Table structure for table plugins
--
CREATE TABLE prefix_plugins (
  "id" serial NOT NULL,
  "name" character varying(50) NOT NULL,
  "active" integer NOT NULL default '0',
  "version" character varying(32) default NULL,
  CONSTRAINT prefix_plugins_pkey PRIMARY KEY (id)
);


--
-- Table structure for table plugin_settings
--
CREATE TABLE prefix_plugin_settings (
  "id" serial NOT NULL,
  "plugin_id" integer NOT NULL,
  "model" character varying(50) NULL,
  "model_id" integer NULL,
  "key" character varying(50) NOT NULL,
  "value" text NULL,
  CONSTRAINT prefix_plugin_settings_pkey PRIMARY KEY (id)
);


--
-- Table structure for table question_attributes
--
CREATE TABLE prefix_question_attributes (
    "qaid" serial NOT NULL,
    "qid" integer DEFAULT 0 NOT NULL,
    "attribute" character varying(50),
    "value" text NULL,
    "language" character varying(20),
    CONSTRAINT prefix_question_attributes_pkey PRIMARY KEY (qaid)
);


--
-- Table structure for table questions
--
CREATE TABLE prefix_questions (
    "qid" serial NOT NULL,
    "parent_qid" integer DEFAULT 0 NOT NULL,
    "sid" integer DEFAULT 0 NOT NULL,
    "gid" integer DEFAULT 0 NOT NULL,
    "type" character varying(1) DEFAULT 'T' NOT NULL,
    "title" character varying(20) DEFAULT '' NOT NULL,
    "question" text NOT NULL,
    "preg" text,
    "help" text,
    "other" character varying(1) DEFAULT 'N' NOT NULL,
    "mandatory" character varying(1),
    "question_order" integer NOT NULL,
    "language" character varying(20) DEFAULT 'en' NOT NULL,
    "scale_id" integer DEFAULT 0 NOT NULL,
    "same_default" integer DEFAULT 0 NOT NULL,
    "relevance" text,
    "modulename" character varying(255),
    CONSTRAINT prefix_questions_pkey PRIMARY KEY (qid, "language")
);


--
-- Table structure for table quota
--
CREATE TABLE prefix_quota (
    "id" serial NOT NULL,
    "sid" integer,
    "name" character varying(255),
    "qlimit" integer,
    "action" integer,
    "active" integer NOT NULL default '1',
    "autoload_url" integer NOT NULL DEFAULT 0,
    CONSTRAINT prefix_quota_pkey PRIMARY KEY (id)
);


--
-- Table structure for table quota_languagesettings
--
CREATE TABLE prefix_quota_languagesettings
(
    "quotals_id" serial NOT NULL,
    "quotals_quota_id" integer NOT NULL DEFAULT 0,
    "quotals_language" character varying(45) NOT NULL DEFAULT 'en',
    "quotals_name" character varying(255),
    "quotals_message" text NOT NULL,
    "quotals_url" character varying(255),
    "quotals_urldescrip" character varying(255),
    CONSTRAINT prefix_quota_languagesettings_pkey PRIMARY KEY (quotals_id)
);


--
-- Table structure for table quota_members
--
CREATE TABLE prefix_quota_members (
    "id" serial NOT NULL,
    "sid" integer,
    "qid" integer,
    "quota_id" integer,
    "code" character varying(11),
    CONSTRAINT prefix_quota_members_pkey PRIMARY KEY (id)
);
CREATE INDEX prefix_quota_members_ixcode_idx ON prefix_quota_members USING btree (sid, qid, quota_id, code);


--
-- Table structure for table saved_control
--
CREATE TABLE prefix_saved_control (
    "scid" serial NOT NULL,
    "sid" integer DEFAULT 0 NOT NULL,
    "srid" integer DEFAULT 0 NOT NULL,
    "identifier" text NOT NULL,
    "access_code" text NOT NULL,
    "email" character varying(254),
    "ip" text NOT NULL,
    "saved_thisstep" text NOT NULL,
    "status" character varying(1) DEFAULT '' NOT NULL,
    "saved_date" timestamp NOT NULL,
    "refurl" text,
    CONSTRAINT prefix_saved_control_pkey PRIMARY KEY (scid)
);


--
-- Table structure for table sessions
--
CREATE TABLE prefix_sessions(
      "id" character varying(32) NOT NULL,
      "expire" integer DEFAULT NULL,
      "data" bytea,
      CONSTRAINT prefix_sessions_pkey PRIMARY KEY ( id )
);


--
-- Table structure for table settings_global
--
CREATE TABLE prefix_settings_global (
    "stg_name" character varying(50) DEFAULT '' NOT NULL,
    "stg_value" text NOT NULL,
    CONSTRAINT prefix_settings_global_pkey PRIMARY KEY (stg_name)
);


--
-- Table structure for table survey_links
--
CREATE TABLE prefix_survey_links (
  "participant_id" character varying ( 50 ) NOT NULL,
  "token_id" integer NOT NULL,
  "survey_id" integer NOT NULL,
  "date_created" timestamp,
  "date_invited" timestamp,
  "date_completed" timestamp,
  CONSTRAINT prefix_survey_links_pkey PRIMARY KEY (participant_id,token_id,survey_id)
);


--
-- Table structure for table survey_url_parameters
--
CREATE TABLE prefix_survey_url_parameters (
    "id" serial PRIMARY KEY NOT NULL,
    "sid" integer NOT NULL,
    "parameter" character varying(50) NOT NULL,
    "targetqid" integer NULL,
    "targetsqid" integer NULL
);


--
-- Table structure for table surveys
--
CREATE TABLE prefix_surveys (
    "sid" integer NOT NULL,
    "gsid" integer NOT NULL,
    "owner_id" integer NOT NULL,
    "admin" character varying(50),
    "active" character varying(1) DEFAULT 'N' NOT NULL,
    "expires" timestamp,
    "startdate" timestamp,
    "adminemail" character varying(254),
    "anonymized" character varying(1) DEFAULT 'N' NOT NULL,
    "faxto" character varying(20),
    "format" character varying(1),
    "savetimings" character varying(1) DEFAULT 'N' NOT NULL,
    "template" character varying(100) DEFAULT 'default',
    "language" character varying(50),
    "additional_languages" character varying(255),
    "datestamp" character varying(1) DEFAULT 'N' NOT NULL,
    "usecookie" character varying(1) DEFAULT 'N' NOT NULL,
    "allowregister" character varying(1) DEFAULT 'N' NOT NULL,
    "allowsave" character varying(1) DEFAULT 'Y' NOT NULL,
    "autonumber_start" integer DEFAULT 0 NOT NULL,
    "autoredirect" character varying(1) DEFAULT 'N' NOT NULL,
    "allowprev" character varying(1) DEFAULT 'N' NOT NULL,
    "printanswers" character varying(1) DEFAULT 'N' NOT NULL,
    "ipaddr" character varying(1) DEFAULT 'N' NOT NULL,
    "refurl" character varying(1) DEFAULT 'N' NOT NULL,
    "datecreated" date,
    "publicstatistics" character varying(1) DEFAULT 'N' NOT NULL,
    "publicgraphs" character varying(1) DEFAULT 'N' NOT NULL,
    "listpublic" character varying(1) DEFAULT 'N' NOT NULL,
    "htmlemail" character varying(1) DEFAULT 'N' NOT NULL,
    "sendconfirmation" character varying(1) DEFAULT 'Y' NOT NULL,
    "tokenanswerspersistence" character varying(1) DEFAULT 'N' NOT NULL,
    "assessments" character varying(1) DEFAULT 'N' NOT NULL,
    "usecaptcha" character varying(1) DEFAULT 'N' NOT NULL,
    "usetokens" character varying(1) DEFAULT 'N' NOT NULL,
    "bounce_email" character varying(254),
    "attributedescriptions" text,
    "emailresponseto" text,
    "emailnotificationto" text,
    "tokenlength" integer DEFAULT '15' NOT NULL,
    "showxquestions" character varying(1) DEFAULT 'Y',
    "showgroupinfo" character varying(1) DEFAULT 'B',
    "shownoanswer" character varying(1) DEFAULT 'Y',
    "showqnumcode" character varying(1) DEFAULT 'X',
    "bouncetime" integer,
    "bounceprocessing" character varying(1) default 'N',
    "bounceaccounttype" character varying(4),
    "bounceaccounthost" character varying(200),
    "bounceaccountpass" character varying(100),
    "bounceaccountencryption" character varying(3),
    "bounceaccountuser" character varying(200),
    "showwelcome" character varying(1) DEFAULT 'Y',
    "showprogress" character varying(1) DEFAULT 'Y',
    "questionindex" integer DEFAULT '0' NOT NULL,
    "navigationdelay" integer DEFAULT '0' NOT NULL,
    "nokeyboard" character varying(1) DEFAULT 'N',
    "alloweditaftercompletion" character varying(1) DEFAULT 'N',
    "googleanalyticsstyle" character varying(1),
    "googleanalyticsapikey" character varying(25),
    CONSTRAINT prefix_surveys_pkey PRIMARY KEY (sid)
);


--
-- Table structure for table surveys_languagesettings
--
CREATE TABLE prefix_surveys_languagesettings (
    "surveyls_survey_id" integer NOT NULL,
    "surveyls_language" character varying(45) DEFAULT 'en',
    "surveyls_title" character varying(200) NOT NULL,
    "surveyls_description" text,
    "surveyls_welcometext" text,
    "surveyls_endtext" text,
    "surveyls_url" text,
    "surveyls_urldescription" character varying(255),
    "surveyls_email_invite_subj" character varying(255),
    "surveyls_email_invite" text,
    "surveyls_email_remind_subj" character varying(255),
    "surveyls_email_remind" text,
    "surveyls_email_register_subj" character varying(255),
    "surveyls_email_register" text,
    "surveyls_email_confirm_subj" character varying(255),
    "surveyls_email_confirm" text,
    "surveyls_dateformat" integer DEFAULT 1 NOT NULL,
    "surveyls_attributecaptions" text,
    "email_admin_notification_subj" character varying(255),
    "email_admin_notification" text,
    "email_admin_responses_subj" character varying(255),
    "email_admin_responses" text,
    "surveyls_numberformat" integer NOT NULL DEFAULT 0,
    "attachments" text DEFAULT NULL,
    CONSTRAINT prefix_surveys_languagesettings_pkey PRIMARY KEY (surveyls_survey_id, surveyls_language)
);


--
-- Table structure for table user_groups
--
CREATE TABLE prefix_user_groups (
    "ugid" serial PRIMARY KEY NOT NULL,
    "name" character varying(20) NOT NULL,
    "description" text NOT NULL,
    "owner_id" integer NOT NULL
);


--
-- Table structure for table user_in_groups
--
CREATE TABLE prefix_user_in_groups (
    "ugid" integer NOT NULL,
    "uid" integer NOT NULL,
    CONSTRAINT prefix_user_in_groups_pkey PRIMARY KEY (ugid, uid)
);


--
-- Table structure for table users
--
CREATE TABLE prefix_users (
    "uid" serial PRIMARY KEY NOT NULL,
    "users_name" character varying(64) DEFAULT '' UNIQUE NOT NULL,
    "password" bytea NOT NULL,
    "full_name" character varying(50) NOT NULL,
    "parent_id" integer NOT NULL,
    "lang" character varying(20),
    "email" character varying(254),
    "htmleditormode" character varying(7) DEFAULT 'default',
    "templateeditormode" character varying(7) DEFAULT 'default' NOT NULL,
    "questionselectormode" character varying(7) DEFAULT 'default' NOT NULL,
    "one_time_pw" bytea,
    "dateformat" integer DEFAULT 1 NOT NULL,
    "created" timestamp,
    "modified" timestamp
);


--CREATE SEQUENCE prefix_boxes;

CREATE TABLE prefix_boxes (
  "id" serial PRIMARY KEY NOT NULL,
  "position" integer DEFAULT NULL ,
  "url" text NOT NULL ,
  "title" text NOT NULL ,
  "ico" text DEFAULT NULL,
  "desc" text NOT NULL ,
  "page" text NOT NULL ,
  "usergroup" integer NOT NULL
);

INSERT INTO "prefix_boxes" ("id", "position", "url", "title", "ico", "desc", "page", "usergroup") VALUES
(1, 1, 'admin/survey/sa/newsurvey', 'Create survey', 'add', 'Create a new survey', 'welcome', '-2'),
(2, 2, 'admin/survey/sa/listsurveys', 'List surveys', 'list', 'List available surveys', 'welcome', '-1'),
(3, 3, 'admin/globalsettings', 'Global settings', 'settings', 'Edit global settings', 'welcome', '-2'),
(4, 4, 'admin/update', 'ComfortUpdate', 'shield', 'Stay safe and up to date', 'welcome', '-2'),
(5, 5, 'admin/labels/sa/view', 'Label sets', 'label', 'Edit label sets', 'welcome', '-2'),
(6, 6, 'admin/templateoptions', 'Templates', 'templates', 'View templates list', 'welcome', '-2');

--
-- Secondary indexes
--
create index answers_idx2 on prefix_answers (sortorder);
create index assessments_idx2 on prefix_assessments (sid);
create index assessments_idx3 on prefix_assessments (gid);
create index conditions_idx2 on prefix_conditions (qid);
create index conditions_idx3 on prefix_conditions (cqid);
create index groups_idx2 on prefix_groups (sid);
create index question_attributes_idx2 on prefix_question_attributes (qid);
create index question_attributes_idx3 on prefix_question_attributes (attribute);
create index questions_idx2 on prefix_questions (sid);
create index questions_idx3 on prefix_questions (gid);
create index questions_idx4 on prefix_questions (type);
create index quota_idx2 on prefix_quota (sid);
create index saved_control_idx2 on prefix_saved_control (sid);
create index parent_qid_idx on prefix_questions (parent_qid);
create index labels_code_idx on prefix_labels (code);
create unique index permissions_idx2 ON prefix_permissions (entity_id, entity, uid, permission);

--
-- Table notifications
--
CREATE TABLE prefix_notifications (
    "id" serial PRIMARY KEY NOT NULL,
    "entity" character varying(15) NOT NULL,
    "entity_id" integer NOT NULL,
    "title" character varying(255) NOT NULL,
    "message" TEXT NOT NULL,
    "status" character varying(15) NOT NULL DEFAULT 'new',
    "importance" integer NOT NULL DEFAULT 1,
    "display_class" character varying(31) DEFAULT 'default',
    "hash" character varying(64) DEFAULT NULL,
    "created" timestamp NOT NULL,
    "first_read" timestamp DEFAULT NULL
);
CREATE INDEX prefix_index ON prefix_notifications USING btree (entity, entity_id, status);
CREATE INDEX notif_hash_index ON prefix_notifications USING btree (hash);

--
-- Table settings_user 
--
CREATE TABLE prefix_settings_user (
    "id" serial PRIMARY KEY NOT NULL,
    "uid" integer NOT NULL,
    "entity" character varying(15) DEFAULT NULL,
    "entity_id" character varying(31) DEFAULT NULL,
    "stg_name" character varying(63) NOT NULL,
    "stg_value" text NULL
);


--
-- Surveymenu
--
CREATE TABLE prefix_surveymenu (
  "id" serial PRIMARY KEY NOT NULL,
  "parent_id" integer DEFAULT NULL,
  "survey_id" integer DEFAULT NULL,
  "user_id" integer DEFAULT NULL,
  "ordering" integer DEFAULT '0',
  "level" integer DEFAULT '0',
  "title" character varying(255)  NOT NULL DEFAULT '',
  "position" character varying(255) NOT NULL DEFAULT 'side',
  "description" text ,
  "active" integer NOT NULL DEFAULT '0',
  "changed_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "changed_by" integer NOT NULL DEFAULT '0',
  "created_at" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "created_by" integer NOT NULL DEFAULT '0'
);

create index surveymenu_ordering_index on prefix_surveymenu ("ordering");
create index surveymenu_title_index on prefix_surveymenu ("title");


INSERT INTO prefix_surveymenu VALUES (1,NULL,NULL,NULL,0,0,'surveymenu','side','Main survey menu',1, NOW(),0,NOW(),0);
INSERT INTO prefix_surveymenu VALUES (2,NULL,NULL,NULL,0,0,'quickmenue','collapsed','quickmenu',1, NOW(),0,NOW(),0);

--
-- Surveymenu entries
--
CREATE TABLE prefix_surveymenu_entries (
  "id" serial PRIMARY KEY NOT NULL,
  "menu_id" integer DEFAULT NULL,
  "user_id" integer DEFAULT NULL,
  "ordering" integer DEFAULT '0',
  "name" character varying(255)  NOT NULL DEFAULT '',
  "title" character varying(255)  NOT NULL DEFAULT '',
  "menu_title" character varying(255)  NOT NULL DEFAULT '',
  "menu_description" text ,
  "menu_icon" character varying(255)  NOT NULL DEFAULT '',
  "menu_icon_type" character varying(255)  NOT NULL DEFAULT '',
  "menu_class" character varying(255)  NOT NULL DEFAULT '',
  "menu_link" character varying(255)  NOT NULL DEFAULT '',
  "action" character varying(255)  NOT NULL DEFAULT '',
  "template" character varying(255)  NOT NULL DEFAULT '',
  "partial" character varying(255)  NOT NULL DEFAULT '',
  "classes" character varying(255)  NOT NULL DEFAULT '',
  "permission" character varying(255)  NOT NULL DEFAULT '',
  "permission_grade" character varying(255)  DEFAULT NULL,
  "data" text ,
  "getdatamethod" character varying(255)  NOT NULL DEFAULT '',
  "language" character varying(255)  NOT NULL DEFAULT 'en-GB',
  "changed_at" timestamp NULL,
  "changed_by" integer NOT NULL DEFAULT '0',
  "created_at" timestamp NOT NULL,
  "created_by" integer NOT NULL DEFAULT '0'
);

create index surveymenu_entries_menu_id_index on prefix_surveymenu_entries ("menu_id");
create index surveymenu_entries_ordering_index on prefix_surveymenu_entries ("ordering");
create index surveymenu_entries_title_index on prefix_surveymenu_entries (title);
create index surveymenu_entries_menu_title_index on prefix_surveymenu_entries (menu_title);

INSERT INTO prefix_surveymenu_entries VALUES
(1,1,NULL,1,'overview','Survey overview','Overview','Open general survey overview and quick action','list','fontawesome','','admin/survey/sa/view','','','','','','','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(2,1,NULL,2,'generalsettings','Edit survey general settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB',NOW(),0,NOW(),0),
(3,1,NULL,3,'surveytexts','Edit survey text elements','Survey texts','Edit survey text elements','file-text-o','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/tab_edit_view','','surveylocale','read',NULL,'_getTextEditData','en-GB',NOW(),0,NOW(),0),
(4,1,NULL,4,'template_options','Template options','Template options','Edit Template options for this survey','paint-brush','fontawesome','','admin/templateoptions/sa/updatesurvey','','','','','templates','read','{"render": {"link": { "pjaxed": false, "data": {"surveyid": ["survey","sid"], "gsid":["survey","gsid"]}}}}','','en-GB',NOW(),0,NOW(),0),
(5,1,NULL,5,'participants','Survey participants','Survey participants','Go to survey participant and token settings','user','fontawesome','','admin/tokens/sa/index/','','','','','surveysettings','update','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(6,1,NULL,6,'presentation','Presentation &amp; navigation settings','Presentation','Edit presentation and navigation settings','eye-slash','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_presentation_panel','','surveylocale','read',NULL,'_tabPresentationNavigation','en-GB',NOW(),0,NOW(),0),
(7,1,NULL,7,'publication','Publication and access control settings','Publication &amp; access','Edit settings for publicationa and access control','key','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_publication_panel','','surveylocale','read',NULL,'_tabPublicationAccess','en-GB',NOW(),0,NOW(),0),
(8,1,NULL,8,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(9,1,NULL,9,'tokens','Token handling','Participant tokens','Define how tokens should be treated or generated','users','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read',NULL,'_tabTokens','en-GB',NOW(),0,NOW(),0),
(10,1,NULL,10,'quotas','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(11,1,NULL,11,'assessments','Edit assessments','Assessments','Edit and look at the asessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(12,1,NULL,12,'notification','Notification and data management settings','Data management','Edit settings for notification and data management','feed','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_notification_panel','','surveylocale','read',NULL,'_tabNotificationDataManagement','en-GB',NOW(),0,NOW(),0),
(13,1,NULL,13,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','assessments','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(14,1,NULL,14,'panelintegration','Edit survey panel integration','Panel integration','Define panel integrations for your survey','link','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_integration_panel','','surveylocale','read',NULL,'_tabPanelIntegration','en-GB',NOW(),0,NOW(),0),
(15,1,NULL,15,'ressources','Add/Edit ressources to the survey','Ressources','Add/Edit ressources to the survey','file','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_resources_panel','','surveylocale','read',NULL,'_tabResourceManagement','en-GB',NOW(),0,NOW(),0),
(16,2,NULL,1,'activateSurvey','Activate survey','Activate survey','Activate survey','play','fontawesome','','admin/survey/sa/activate','','','','','surveyactivation','update','{\"render\": {\"isActive\": false, \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(17,2,NULL,2,'deactivateSurvey','Stop this survey','Stop this survey','Stop this survey','stop','fontawesome','','admin/survey/sa/deactivate','','','','','surveyactivation','update','{\"render\": {\"isActive\": true, \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(18,2,NULL,3,'testSurvey','Go to survey','Go to survey','Go to survey','cog','fontawesome','','survey/index/','','','','','','','{\"render\"\: {\"link\"\: {\"external\"\: true, \"data\"\: {\"sid\"\: [\"survey\",\"sid\"], \"newtest\"\: \"Y\", \"lang\"\: [\"survey\",\"language\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(19,2,NULL,4,'listQuestions','List questions','List questions','List questions','list','fontawesome','','admin/survey/sa/listquestions','','','','','surveycontent','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(20,2,NULL,5,'listQuestionGroups','List question groups','List question groups','List question groups','th-list','fontawesome','','admin/survey/sa/listquestiongroups','','','','','surveycontent','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(21,2,NULL,6,'generalsettings','Edit survey general settings','General settings','Open general survey settings','gears','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_generaloptions_panel','','surveysettings','read',NULL,'_generalTabEditSurvey','en-GB',NOW(),0,NOW(),0),
(22,2,NULL,7,'surveypermissions','Edit surveypermissions','Survey permissions','Edit permissions for this survey','lock','fontawesome','','admin/surveypermission/sa/view/','','','','','surveysecurity','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(23,2,NULL,8,'quotas','Edit quotas','Survey quotas','Edit quotas for this survey.','tasks','fontawesome','','admin/quotas/sa/index/','','','','','quotas','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(24,2,NULL,9,'assessments','Edit assessments','Assessments','Edit and look at the asessements for this survey.','comment-o','fontawesome','','admin/assessments/sa/index/','','','','','assessments','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(25,2,NULL,10,'emailtemplates','Email templates','Email templates','Edit the templates for invitation, reminder and registration emails','envelope-square','fontawesome','','admin/emailtemplates/sa/index/','','','','','surveylocale','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(26,2,NULL,11,'surveyLogicFile','Survey logic file','Survey logic file','Survey logic file','sitemap','fontawesome','','admin/expressions/sa/survey_logic_file/','','','','','surveycontent','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0),
(27,2,NULL,12,'tokens','Token handling','Participant tokens','Define how tokens should be treated or generated','user','fontawesome','','','updatesurveylocalesettings','editLocalSettings_main_view','/admin/survey/subview/accordion/_tokens_panel','','surveylocale','read','{\"render\": { \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','_tabTokens','en-GB',NOW(),0,NOW(),0),
(28,2,NULL,13,'cpdb','Central participant database','Central participant database','Central participant database','users','fontawesome','','admin/participants/sa/displayParticipants','','','','','tokens','read','{render\: {\"link\"\: {}}','','en-GB',NOW(),0,NOW(),0),
(29,2,NULL,14,'responses','Responses','Responses','Responses','icon-browse','iconclass','','admin/responses/sa/browse/','','','','','responses','read','{\"render\"\: {\"isActive\"\: true}}','','en-GB',NOW(),0,NOW(),0),
(30,2,NULL,15,'statistics','Statistics','Statistics','Statistics','bar-chart','fontawesome','','admin/statistics/sa/index/','','','','','statistics','read','{\"render\"\: {\"isActive\"\: true}}','','en-GB',NOW(),0,NOW(),0),
(31,2,NULL,16,'reorder','Reorder questions/question groups','Reorder questions/question groups','Reorder questions/question groups','icon-organize','iconclass','','admin/survey/sa/organize/','','','','','surveycontent','update','{\"render\": {\"isActive\": false, \"link\": {\"data\": {\"surveyid\": [\"survey\",\"sid\"]}}}}','','en-GB',NOW(),0,NOW(),0);


-- -----------------------------------------------------
-- Table prefix_templates
-- -----------------------------------------------------
CREATE TABLE prefix_templates (
  "id" serial PRIMARY KEY NOT NULL,
  "name" character varying(150)  NOT NULL,
  "folder" character varying(45)  DEFAULT NULL,
  "title" character varying(100)  NOT NULL,
  "creation_date" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  "author" character varying(150)  DEFAULT NULL,
  "author_email" character varying(255)  DEFAULT NULL,
  "author_url" character varying(255)  DEFAULT NULL,
  "copyright" text ,
  "license" text ,
  "version" character varying(45)  DEFAULT NULL,
  "api_version" character varying(45)  NOT NULL,
  "view_folder" character varying(45)  NOT NULL,
  "files_folder" character varying(45)  NOT NULL,
  "description" text ,
  "last_update" timestamp DEFAULT NULL,
  "owner_id" int DEFAULT NULL,
  "extends" character varying(150)  DEFAULT NULL
);



INSERT INTO prefix_templates VALUES
(1,'default', 'default', 'Advanced Template', '2017-07-12 10:00:00', 'Louis Gac', 'louis.gac@limesurvey.org', 'https://www.limesurvey.org/', 'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.', 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.', '1.0', '3.0', 'views', 'files', 'LimeSurvey Advanced Template:\\r\\nMany options for user customizations. \\r\\n', NULL, 1, '');
INSERT INTO prefix_templates VALUES
(2,'minimal', 'minimal', 'Minimal Template', '2017-07-12 10:00:00', 'Louis Gac', 'louis.gac@limesurvey.org', 'https://www.limesurvey.org/', 'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.', 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.', '1.0', '3.0', 'views', 'files', '<strong>LimeSurvey Minimal Template</strong><br>A clean and simple base that can be used by developers to create their own solution.', NULL, 1, '');
INSERT INTO prefix_templates VALUES
(3,'material', 'material', 'Material Template', '2017-07-12 10:00:00', 'Louis Gac', 'louis.gac@limesurvey.org', 'https://www.limesurvey.org/', 'Copyright (C) 2007-2017 The LimeSurvey Project Team\\r\\nAll rights reserved.', 'License: GNU/GPL License v2 or later, see LICENSE.php\\r\\n\\r\\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.', '1.0', '3.0', 'views', 'files', '<strong>LimeSurvey Advanced Template</strong><br> A template extending default, to show the inheritance concept. Notice the options, differents from Default.<br><small>uses FezVrasta''s Material design theme for Bootstrap 3</small>', NULL, 1, 'default');

-- -----------------------------------------------------
-- Table prefix_template_configuration
-- -----------------------------------------------------
CREATE TABLE prefix_template_configuration (
  "id" serial PRIMARY KEY NOT NULL,
  template_name character varying(150)  NOT NULL,
  sid integer DEFAULT NULL,
  gsid integer DEFAULT NULL,
  uid integer DEFAULT NULL,
  files_css text ,
  files_js text ,
  files_print_css text ,
  options text ,
  cssframework_name character varying(45)  DEFAULT NULL,
  cssframework_css text ,
  cssframework_js text ,
  packages_to_load text ,
  packages_ltr text ,
  packages_rtl text 
);


INSERT INTO prefix_template_configuration VALUES
  (1,'default',NULL,NULL,NULL,'{"add": ["css/template.css", "css/animate.css"]}','{"add": ["scripts/template.js"]}','{"add":"css/print_template.css",}','{"ajaxmode":"off","brandlogo":"on", "boxcontainer":"on", "backgroundimage":"on","animatebody":"on","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}','bootstrap','{"replace": [["css/bootstrap.css","css/flatly.css"]]}','','','','');

INSERT INTO prefix_template_configuration VALUES
  (2, 'minimal', NULL, NULL, NULL, '{"add": ["css/template.css"]}', '{"add": ["scripts/template.js"]}', '{"add":"css/print_template.css",}', '{}', 'bootstrap', '{}', '', '', '', '');

INSERT INTO prefix_template_configuration VALUES
  (3,'material',NULL,NULL,NULL,'{"add": ["css/template.css", "css/bootstrap-material-design.css", "css/ripples.min.css"]}','{"add": ["scripts/template.js", "scripts/material.js", "scripts/ripples.min.js"]}','{"add":"css/print_template.css",}','{"ajaxmode":"off","brandlogo":"on", "animatebody":"on","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}','bootstrap','{"replace": [["css/bootstrap.css","css/bootstrap.css"]]}','','','','');

-- -----------------------------------------------------
-- Table prefix_surveys_groups
-- -----------------------------------------------------
CREATE TABLE "prefix_surveys_groups" (
  "gsid" serial PRIMARY KEY NOT NULL,
  "name" character varying(45) NOT NULL,
  "title" character varying(100) DEFAULT NULL,
  "template" character varying(128) DEFAULT 'default',
  "description" text ,
  "sortorder" integer NOT NULL,
  "owner_uid" integer DEFAULT NULL,
  "parent_id" integer DEFAULT NULL,
  "created" timestamp DEFAULT NULL,
  "modified" timestamp DEFAULT NULL,
  "created_by" integer NOT NULL
);


INSERT INTO "prefix_surveys_groups" ("gsid", "name", "title", "description", "sortorder", "owner_uid", "parent_id", "created", "modified", "created_by") VALUES
  (1, 'default', 'Default Survey Group', 'LimeSurvey core default survey group', 0, 1, NULL, '2017-07-20 17:09:30', '2017-07-20 17:09:30', 1);

--
-- Version Info
--
INSERT INTO prefix_settings_global VALUES ('DBVersion', '315');
