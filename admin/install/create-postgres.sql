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
    qid integer DEFAULT 0 NOT NULL,
    code character varying(5) DEFAULT ''::character varying NOT NULL,
    answer text NOT NULL,
    sortorder integer NOT NULL,
    assessment_value integer DEFAULT 0 NOT NULL,
    "language" character varying(20) DEFAULT 'en'::character varying NOT NULL,
    scale_id smallint DEFAULT 0 NOT NULL
);

ALTER TABLE ONLY prefix_answers
    ADD CONSTRAINT prefix_answers_pkey PRIMARY KEY (qid, code, "language", scale_id);

    
-- 
-- Table structure for table assessments
-- 
CREATE TABLE prefix_assessments (
    id serial,
    sid integer DEFAULT 0 NOT NULL,
    scope character varying(5) DEFAULT ''::character varying NOT NULL,
    gid integer DEFAULT 0 NOT NULL,
    name text NOT NULL,
    minimum character varying(50) DEFAULT ''::character varying NOT NULL,
    maximum character varying(50) DEFAULT ''::character varying NOT NULL,
    message text NOT NULL,
    language character(20) DEFAULT 'en'::bpchar NOT NULL
);

ALTER TABLE ONLY prefix_assessments
    ADD CONSTRAINT prefix_assessments_pkey PRIMARY KEY (id,language);


-- 
-- Table structure for table conditions
--
CREATE TABLE prefix_conditions (
    cid serial,
    qid integer DEFAULT 0 NOT NULL,
    scenario integer DEFAULT 1 NOT NULL,
    cqid integer DEFAULT 0 NOT NULL,
    cfieldname character varying(50) DEFAULT ''::character varying NOT NULL,
    method character(5) DEFAULT ''::bpchar NOT NULL,
    value character varying(255) DEFAULT ''::character varying NOT NULL
);

ALTER TABLE ONLY prefix_conditions
    ADD CONSTRAINT prefix_conditions_pkey PRIMARY KEY (cid);


-- 
-- Table structure for table defaultvalues
--
CREATE TABLE prefix_defaultvalues (
      qid integer NOT NULL default '0',
      scale_id integer NOT NULL default '0',
      sqid integer NOT NULL default '0',
      language character varying(20) NOT NULL,
      specialtype character varying(20) NOT NULL default '',
      defaultvalue text);

ALTER TABLE prefix_defaultvalues ADD CONSTRAINT prefix_defaultvalues_pkey PRIMARY KEY (qid , scale_id, language, specialtype, sqid);


-- 
-- Table structure for table groups
--
CREATE TABLE prefix_groups (
    gid serial,
    sid integer DEFAULT 0 NOT NULL,
    group_name character varying(100) DEFAULT ''::character varying NOT NULL,
    group_order integer DEFAULT 0 NOT NULL,
    description text,
    "language" character varying(20) DEFAULT 'en'::character varying NOT NULL
);

ALTER TABLE ONLY prefix_groups
    ADD CONSTRAINT prefix_groups_pkey PRIMARY KEY (gid, "language");


-- 
-- Table structure for table labels
--
CREATE TABLE prefix_labels (
    lid integer DEFAULT 0 NOT NULL,
    code character varying(5) DEFAULT ''::character varying NOT NULL,
    title text,
    sortorder integer NOT NULL,
    assessment_value integer DEFAULT 0 NOT NULL,
    "language" character varying(20) DEFAULT 'en'::character varying NOT NULL
);

ALTER TABLE ONLY prefix_labels ADD CONSTRAINT prefix_labels_pkey PRIMARY KEY (lid, sortorder, "language");
CREATE INDEX prefix_labels_ixcode_idx ON prefix_labels USING btree (code);


-- 
-- Table structure for table labelsets
--
CREATE TABLE prefix_labelsets (
    lid serial NOT NULL,
    label_name character varying(100) DEFAULT ''::character varying NOT NULL,
    languages character varying(200) DEFAULT 'en'::character varying
);

ALTER TABLE ONLY prefix_labelsets ADD CONSTRAINT prefix_labelsets_pkey PRIMARY KEY (lid);


-- 
-- Table structure for table question_attributes
--
CREATE TABLE prefix_question_attributes (
    qaid serial NOT NULL,
    qid integer DEFAULT 0 NOT NULL,
    attribute character varying(50),
    value text NULL
);

ALTER TABLE ONLY prefix_question_attributes ADD CONSTRAINT prefix_question_attributes_pkey PRIMARY KEY (qaid);


-- 
-- Table structure for table quota
-- 
CREATE TABLE prefix_quota (
  id serial NOT NULL,
  sid integer,
  name character varying(255),
  qlimit integer,
  "action" integer,
  "active" integer NOT NULL default '1',
  autoload_url integer NOT NULL DEFAULT 0
);

ALTER TABLE ONLY prefix_quota ADD CONSTRAINT prefix_quota_pkey PRIMARY KEY (id);

    
-- 
-- Table structure for table quota_languagesettings
-- 
CREATE TABLE prefix_quota_languagesettings
(
  quotals_id serial NOT NULL,
  quotals_quota_id integer NOT NULL DEFAULT 0,
  quotals_language character varying(45) NOT NULL DEFAULT 'en'::character varying,
  quotals_name character varying(200),
  quotals_message text NOT NULL,
  quotals_url character varying(255),
  quotals_urldescrip character varying(255)
);

ALTER TABLE ONLY prefix_quota_languagesettings ADD CONSTRAINT prefix_quota_languagesettings_pkey PRIMARY KEY (quotals_id);

  
-- 
-- Table structure for table quota_members
--   
CREATE TABLE prefix_quota_members (
  id serial,
  sid integer,
  qid integer,
  quota_id integer,
  code character varying(11)
);

ALTER TABLE ONLY prefix_quota_members ADD CONSTRAINT prefix_quota_members_pkey PRIMARY KEY (id);
CREATE INDEX prefix_quota_members_ixcode_idx ON prefix_quota_members USING btree (sid,qid,quota_id,code);


-- 
-- Table structure for table questions
--   
CREATE TABLE prefix_questions (
    qid serial NOT NULL,
    parent_qid integer DEFAULT 0 NOT NULL,
    sid integer DEFAULT 0 NOT NULL,
    gid integer DEFAULT 0 NOT NULL,
    "type" character(1) DEFAULT 'T'::bpchar NOT NULL,
    title character varying(20) DEFAULT ''::character varying NOT NULL,
    question text NOT NULL,
    preg text,
    help text,
    other character(1) DEFAULT 'N'::bpchar NOT NULL,
    mandatory character(1),
    question_order integer NOT NULL,
    "language" character varying(20) DEFAULT 'en'::character varying NOT NULL,
    scale_id smallint DEFAULT 0 NOT NULL,
    same_default smallint DEFAULT 0 NOT NULL
);

ALTER TABLE ONLY prefix_questions ADD CONSTRAINT prefix_questions_pkey PRIMARY KEY (qid, "language");

    
-- 
-- Table structure for table saved_control
--
CREATE TABLE prefix_saved_control (
    scid serial NOT NULL,
    sid integer DEFAULT 0 NOT NULL,
    srid integer DEFAULT 0 NOT NULL,
    identifier text,
    access_code text NOT NULL,
    email character varying(320) NOT NULL,
    ip text NOT NULL,
    saved_thisstep text NOT NULL,
    status character(1) DEFAULT ''::bpchar NOT NULL,
    saved_date timestamp without time zone NOT NULL,
    refurl text
);

ALTER TABLE ONLY prefix_saved_control ADD CONSTRAINT prefix_saved_control_pkey PRIMARY KEY (scid);

-- 
-- Table structure for table sessions
--
CREATE TABLE prefix_sessions(
     sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
     expiry TIMESTAMP NOT NULL ,
     expireref VARCHAR( 250 ) DEFAULT '',
     created TIMESTAMP NOT NULL ,
     modified TIMESTAMP NOT NULL ,
     sessdata TEXT DEFAULT '',
     PRIMARY KEY ( sesskey )
     );
create INDEX sess_expiry on prefix_sessions( expiry );
create INDEX sess_expireref on prefix_sessions ( expireref );


-- 
-- Table structure for table settings_global
--
CREATE TABLE prefix_settings_global (
    stg_name character varying(50) DEFAULT ''::character varying NOT NULL,
    stg_value character varying(255) DEFAULT ''::character varying NOT NULL
);

ALTER TABLE ONLY prefix_settings_global ADD CONSTRAINT prefix_settings_global_pkey PRIMARY KEY (stg_name);


-- 
-- Table structure for table surveys
--
CREATE TABLE prefix_surveys (
    sid integer NOT NULL,
    owner_id integer NOT NULL,
    "admin" character varying(50),
    active character(1) DEFAULT 'N'::bpchar NOT NULL,
    startdate timestamp,
    expires timestamp,
    adminemail character varying(320) NOT NULL,
    anonymized character(1),
    faxto character varying(20),
    format character(1),
    savetimings character(1) DEFAULT 'N'::bpchar,
    "template" character varying(100) DEFAULT 'default'::character varying,
    "language" character varying(50),
    additional_languages character varying(255),
    datestamp character(1) DEFAULT 'N'::bpchar,
    usecookie character(1) DEFAULT 'N'::bpchar,
    allowregister character(1) DEFAULT 'N'::bpchar,
    allowsave character(1) DEFAULT 'Y'::bpchar,
    printanswers character(1) DEFAULT 'N'::bpchar,
    autonumber_start integer DEFAULT 0,
    autoredirect character(1) DEFAULT 'N'::bpchar,
    showXquestions character(1) DEFAULT 'Y'::bpchar,
    showgroupinfo character(1) DEFAULT 'B'::bpchar,
    shownoanswer character(1) DEFAULT 'Y'::bpchar,
    showqnumcode character(1) DEFAULT 'X'::bpchar,
    showwelcome character(1) DEFAULT 'Y'::bpchar,
    allowprev character(1) DEFAULT 'Y'::bpchar,
    ipaddr character(1) DEFAULT 'N'::bpchar,
    refurl character(1) DEFAULT 'N'::bpchar,
    datecreated date,
    listpublic character(1) DEFAULT 'N'::bpchar,
    publicstatistics character(1) DEFAULT 'N'::bpchar,
    publicgraphs character(1) DEFAULT 'N'::bpchar,
    htmlemail character(1) DEFAULT 'N'::bpchar,
    tokenanswerspersistence character(1) DEFAULT 'N'::bpchar,
    assessments character(1) DEFAULT 'N'::bpchar,
    usecaptcha character(1) DEFAULT 'N'::bpchar,
    bouncetime bigint,
    bounceprocessing character(1) default 'N'::bpchar,
    bounceaccounttype character(4),
    bounceaccounthost character(200),
    bounceaccountuser character(200),
    bounceaccountpass character(100),
    bounceaccountencryption character(3),
    usetokens character(1) DEFAULT 'N'::bpchar,
    "bounce_email" character varying(320) NOT NULL,
    attributedescriptions text,
	emailresponseto text,
    emailnotificationto text,
	tokenlength smallint DEFAULT '15',
    showprogress character(1) DEFAULT 'N'::bpchar,
    allowjumps character(1) DEFAULT 'N'::bpchar,
    navigationdelay smallint DEFAULT '0',
    nokeyboard character(1) DEFAULT 'N'::bpchar,
    alloweditaftercompletion character(1) DEFAULT 'N'::bpchar
);

ALTER TABLE ONLY prefix_surveys ADD CONSTRAINT prefix_surveys_pkey PRIMARY KEY (sid);


-- 
-- Table structure for table surveys_languagesettings
--
CREATE TABLE prefix_surveys_languagesettings (
    surveyls_survey_id integer DEFAULT 0 NOT NULL,
    surveyls_language character varying(45) DEFAULT 'en'::character varying NOT NULL,
    surveyls_title character varying(200) NOT NULL,
    surveyls_description text,
    surveyls_welcometext text,
    surveyls_url character varying(255),
    surveyls_urldescription character varying(255),
    surveyls_endtext text,
    surveyls_email_invite_subj character varying(255),
    surveyls_email_invite text,
    surveyls_email_remind_subj character varying(255),
    surveyls_email_remind text,
    surveyls_email_register_subj character varying(255),
    surveyls_email_register text,
    surveyls_email_confirm_subj character varying(255),
    surveyls_email_confirm text,
    surveyls_dateformat integer DEFAULT 1 NOT NULL,
    email_admin_notification_subj character varying(255),
    email_admin_notification text,
    email_admin_responses_subj character varying(255),
    email_admin_responses text,
    surveyls_numberformat integer NOT NULL DEFAULT 1
);

ALTER TABLE ONLY prefix_surveys_languagesettings ADD CONSTRAINT prefix_surveys_languagesettings_pkey PRIMARY KEY (surveyls_survey_id, surveyls_language);


-- 
-- Table structure for table survey_permissions
--
CREATE TABLE prefix_survey_permissions (
	sid integer DEFAULT 0 NOT NULL,
	uid integer DEFAULT 0 NOT NULL,
	permission character varying(20) NOT NULL,
	create_p integer DEFAULT 0 NOT NULL,
    read_p integer DEFAULT 0 NOT NULL,
	update_p integer DEFAULT 0 NOT NULL,
	delete_p integer DEFAULT 0 NOT NULL,
    import_p integer DEFAULT 0 NOT NULL,
    export_p integer DEFAULT 0 NOT NULL
);

ALTER TABLE ONLY prefix_survey_permissions ADD CONSTRAINT prefix_survey_permissions_pkey PRIMARY KEY (sid,uid,permission);


-- 
-- Table structure for table user_groups
--    
CREATE TABLE prefix_user_groups (
    ugid serial NOT NULL,
    name character varying(20) NOT NULL,
    description text NOT NULL,
    owner_id integer NOT NULL
);


-- 
-- Table structure for table user_in_groups
--                               
CREATE TABLE prefix_user_in_groups (
    ugid integer NOT NULL,
    uid integer NOT NULL
);


-- 
-- Table structure for table users
--   
CREATE TABLE prefix_users (
    uid serial PRIMARY KEY NOT NULL,
    users_name character varying(64) DEFAULT ''::character varying UNIQUE NOT NULL,
    "password" bytea NOT NULL,
    full_name character varying(50) NOT NULL,
    parent_id integer NOT NULL,
    lang character varying(20),
    email character varying(320) NOT NULL,
    create_survey integer DEFAULT 0 NOT NULL,
    create_user integer DEFAULT 0 NOT NULL,
    delete_user integer DEFAULT 0 NOT NULL,
    superadmin integer DEFAULT 0 NOT NULL,
    configurator integer DEFAULT 0 NOT NULL,
    manage_template integer DEFAULT 0 NOT NULL,
    manage_label integer DEFAULT 0 NOT NULL,
    htmleditormode character(7) DEFAULT 'default'::bpchar,
    templateeditormode character(7) DEFAULT 'default'::bpchar,
    questionselectormode character(7) DEFAULT 'default'::bpchar,
	one_time_pw bytea,
    "dateformat" integer DEFAULT 1 NOT NULL
);


-- 
-- Table structure for table templates_rights
--   
CREATE TABLE prefix_templates_rights (
  "uid" integer NOT NULL,
  "folder" character varying(255) NOT NULL,
  "use" integer NOT NULL
);

ALTER TABLE ONLY prefix_templates_rights ADD CONSTRAINT prefix_templates_rights_pkey PRIMARY KEY ("uid","folder");


-- 
-- Table structure for table templates
--      
CREATE TABLE prefix_templates (
  "folder" character varying(255) NOT NULL,
  "creator" integer NOT NULL
);

ALTER TABLE ONLY prefix_templates ADD CONSTRAINT prefix_templates_pkey PRIMARY KEY ("folder");

--
-- Create failed_login_attempts
--

CREATE TABLE prefix_failed_login_attempts (
  id serial PRIMARY KEY NOT NULL,
  ip character varying(37) NOT NULL,
  last_attempt character varying(20) NOT NULL,
  number_attempts integer NOT NULL
);

--
-- Secondary indexes 
--
create index assessments_idx2 on prefix_assessments (sid);
create index assessments_idx3 on prefix_assessments (gid);
create index conditions_idx2 on prefix_conditions (qid);
create index groups_idx2 on prefix_groups (sid);
create index question_attributes_idx2 on prefix_question_attributes (qid);
create index questions_idx2 on prefix_questions (sid);
create index questions_idx3 on prefix_questions (gid);
create index quota_idx2 on prefix_quota (sid);
create index saved_control_idx2 on prefix_saved_control (sid);
create index user_in_groups_idx1 on prefix_user_in_groups  (ugid, uid);
create index parent_qid_idx on prefix_questions (parent_qid);


--
-- Version Info
--
INSERT INTO prefix_settings_global VALUES ('DBVersion', '146');
INSERT INTO prefix_settings_global VALUES ('SessionName', '$sessionname');


--
-- Create admin user
--

INSERT INTO prefix_users(
            users_name, "password", full_name, parent_id, lang, email, 
            create_survey, create_user, delete_user, superadmin, configurator, 
            manage_template, manage_label,htmleditormode)
            VALUES ('$defaultuser', '$defaultpass', '$siteadminname', 0, '$defaultlang', '$siteadminemail',1,1,1,1,1,1,1,'default');
