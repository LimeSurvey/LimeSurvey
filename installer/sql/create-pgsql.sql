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
    "stg_value" character varying(255) DEFAULT '' NOT NULL,
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

--
-- Table structure for table templates
--
CREATE TABLE prefix_templates (
  "folder" character varying(50) NOT NULL,
  "creator" integer NOT NULL,
  CONSTRAINT prefix_templates_pkey PRIMARY KEY ("folder")
);

--
-- Table structure & data for boxes
--

--CREATE SEQUENCE prefix_boxes;

CREATE TABLE prefix_boxes (
  "id" SERIAL,
  "position" int DEFAULT NULL ,
  "url" text NOT NULL ,
  "title" text NOT NULL ,
  "ico" text DEFAULT NULL,
  "desc" text NOT NULL ,
  "page" text NOT NULL ,
  "usergroup" integer NOT NULL,
  PRIMARY KEY (id)
);

INSERT INTO "prefix_boxes" ("id", "position", "url", "title", "ico", "desc", "page", "usergroup") VALUES
(1, 1, 'admin/survey/sa/newsurvey', 'Create survey', 'add', 'Create a new survey', 'welcome', '-2'),
(2, 2, 'admin/survey/sa/listsurveys', 'List surveys', 'list', 'List available surveys', 'welcome', '-1'),
(3, 3, 'admin/globalsettings', 'Global settings', 'settings', 'Edit global settings', 'welcome', '-2'),
(4, 4, 'admin/update', 'ComfortUpdate', 'shield', 'Stay safe and up to date', 'welcome', '-2'),
(5, 5, 'admin/labels/sa/view', 'Label sets', 'label', 'Edit label sets', 'welcome', '-2'),
(6, 6, 'admin/templates/sa/view', 'Template editor', 'templates', 'Edit LimeSurvey templates', 'welcome', '-2');

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
-- Notification table
--
CREATE TABLE prefix_notifications (
    "id" SERIAL,
    "entity" character varying(15) NOT NULL,
    "entity_id" integer NOT NULL,
    "title" character varying(255) NOT NULL,
    "message" TEXT NOT NULL,
    "status" character varying(15) NOT NULL DEFAULT 'new',
    "importance" integer NOT NULL DEFAULT 1,
    "display_class" character varying(31) DEFAULT 'default',
    "created" timestamp NOT NULL,
    "first_read" timestamp DEFAULT NULL,
    CONSTRAINT prefix_notifications_pkey PRIMARY KEY (id)
);
CREATE INDEX prefix_index ON prefix_notifications USING btree (entity, entity_id, status);

--
-- Version Info
--
INSERT INTO prefix_settings_global VALUES ('DBVersion', '260');
