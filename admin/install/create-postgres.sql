--
-- PostgreSQL database dump
--

-- Started on 2007-11-18 18:48:15

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;
SET search_path = public, pg_catalog;
SET default_tablespace = '';
SET default_with_oids = false;

--
-- TOC entry 1300 (class 1259 OID 16405)
-- Dependencies: 1655 1656 1657 1658 4
-- Name: prefix_answers; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
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
-- TOC entry 1302 (class 1259 OID 16418)
-- Dependencies: 1660 1661 1662 1663 1664 4
-- Name: prefix_assessments; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
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


--
-- TOC entry 1304 (class 1259 OID 16433)
-- Dependencies: 1666 1667 1668 1669 1670 4
-- Name: prefix_conditions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_conditions (
    cid serial,
    qid integer DEFAULT 0 NOT NULL,
    scenario integer DEFAULT 1 NOT NULL,
    cqid integer DEFAULT 0 NOT NULL,
    cfieldname character varying(50) DEFAULT ''::character varying NOT NULL,
    method character(2) DEFAULT ''::bpchar NOT NULL,
    value character varying(255) DEFAULT ''::character varying NOT NULL
);


CREATE TABLE prefix_defaultvalues (
      qid integer NOT NULL default '0',
      scale_id integer NOT NULL default '0',
      sqid integer NOT NULL default '0',
      language character varying(20) NOT NULL,
      specialtype character varying(20) NOT NULL default '',
      defaultvalue text);

ALTER TABLE prefix_defaultvalues ADD CONSTRAINT prefix_defaultvalues_pkey PRIMARY KEY (qid , scale_id, language, specialtype, sqid);


--
-- TOC entry 1306 (class 1259 OID 16445)
-- Dependencies: 1672 1673 1674 1675 4
-- Name: prefix_groups; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_groups (
    gid serial,
    sid integer DEFAULT 0 NOT NULL,
    group_name character varying(100) DEFAULT ''::character varying NOT NULL,
    group_order integer DEFAULT 0 NOT NULL,
    description text,
    "language" character varying(20) DEFAULT 'en'::character varying NOT NULL
);


--
-- TOC entry 1307 (class 1259 OID 16457)
-- Dependencies: 1676 1677 1678 1679 4
-- Name: prefix_labels; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_labels (
    lid integer DEFAULT 0 NOT NULL,
    code character varying(5) DEFAULT ''::character varying NOT NULL,
    title text,
    sortorder integer NOT NULL,
    assessment_value integer DEFAULT 0 NOT NULL,
    "language" character varying(20) DEFAULT 'en'::character varying NOT NULL
);



--
-- TOC entry 1309 (class 1259 OID 16468)
-- Dependencies: 1681 1682 4
-- Name: prefix_labelsets; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_labelsets (
    lid serial NOT NULL,
    label_name character varying(100) DEFAULT ''::character varying NOT NULL,
    languages character varying(200) DEFAULT 'en'::character varying
);


--
-- TOC entry 1313 (class 1259 OID 16494)
-- Dependencies: 1692 4
-- Name: prefix_question_attributes; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_question_attributes (
    qaid serial NOT NULL,
    qid integer DEFAULT 0 NOT NULL,
    attribute character varying(50),
    value text NULL
);

-- 
-- Table structure for table `quota`
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

ALTER TABLE ONLY prefix_quota
    ADD CONSTRAINT prefix_quota_pkey PRIMARY KEY (id);

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

ALTER TABLE ONLY prefix_quota_languagesettings
  ADD CONSTRAINT prefix_quota_languagesettings_pkey PRIMARY KEY (quotals_id);

CREATE TABLE prefix_quota_members (
  id serial,
  sid integer,
  qid integer,
  quota_id integer,
  code character varying(11)
);

ALTER TABLE ONLY prefix_quota_members
    ADD CONSTRAINT prefix_quota_members_pkey PRIMARY KEY (id);
CREATE INDEX prefix_quota_members_ixcode_idx ON prefix_quota_members USING btree (sid,qid,quota_id,code);

--
-- Name: prefix_questions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
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

ALTER TABLE ONLY prefix_questions
    ADD CONSTRAINT prefix_questions_pkey PRIMARY KEY (qid, "language");

--
-- Name: prefix_saved_control; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
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
-- Dependencies: 1697 1698 4
-- Name: prefix_settings_global; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_settings_global (
    stg_name character varying(50) DEFAULT ''::character varying NOT NULL,
    stg_value character varying(255) DEFAULT ''::character varying NOT NULL
);



--
-- Name: prefix_surveys; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_surveys (
    sid integer NOT NULL,
    owner_id integer NOT NULL,
    "admin" character varying(50),
    active character(1) DEFAULT 'N'::bpchar NOT NULL,
    startdate timestamp,
    expires timestamp,
    adminemail character varying(320) NOT NULL,
    private character(1),
    faxto character varying(20),
    format character(1),
    "template" character varying(100) DEFAULT 'default'::character varying,
    "language" character varying(50),
    additional_languages character varying(255),
    datestamp character(1) DEFAULT 'N'::bpchar,
    usecookie character(1) DEFAULT 'N'::bpchar,
    notification character(1) DEFAULT '0'::bpchar,
    allowregister character(1) DEFAULT 'N'::bpchar,
    allowsave character(1) DEFAULT 'Y'::bpchar,
    printanswers character(1) DEFAULT 'N'::bpchar,
    autonumber_start integer DEFAULT 0,
    autoredirect character(1) DEFAULT 'N'::bpchar,
    showXquestions character(1) DEFAULT 'Y'::bpchar,
    showgroupinfo character(1) DEFAULT 'B'::bpchar,
    shownoanswer character(1) DEFAULT 'Y'::bpchar,
    showqnumcode character(1) DEFAULT 'X'::bpchar,
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
    usetokens character(1) DEFAULT 'N'::bpchar,
    "bounce_email" character varying(320) NOT NULL,
    attributedescriptions text,
	emailresponseto text,
	tokenlength smallint DEFAULT '15'
);




-- Dependencies: 1713 1714 4
-- Name: prefix_surveys_languagesettings; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
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
    surveyls_dateformat integer DEFAULT 1 NOT NULL
);



--
-- TOC entry 1319 (class 1259 OID 16549)
-- Dependencies: 1715 1716 1717 1718 1719 1720 1721 1722 4
-- Name: prefix_surveys_rights; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_surveys_rights (
    sid integer DEFAULT 0 NOT NULL,
    uid integer DEFAULT 0 NOT NULL,
    edit_survey_property integer DEFAULT 0 NOT NULL,
    define_questions integer DEFAULT 0 NOT NULL,
    browse_response integer DEFAULT 0 NOT NULL,
    export integer DEFAULT 0 NOT NULL,
    delete_survey integer DEFAULT 0 NOT NULL,
    activate_survey integer DEFAULT 0 NOT NULL
);


-- Name: prefix_user_groups; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 

CREATE TABLE prefix_user_groups (
    ugid serial NOT NULL,
    name character varying(20) NOT NULL,
    description text NOT NULL,
    owner_id integer NOT NULL
);



CREATE TABLE prefix_user_in_groups (
    ugid integer NOT NULL,
    uid integer NOT NULL
);

--
-- Name: prefix_users; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
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
	one_time_pw bytea,
    "dateformat" integer DEFAULT 1 NOT NULL
);

CREATE TABLE prefix_templates_rights (
  "uid" integer NOT NULL,
  "folder" character varying(255) NOT NULL,
  "use" integer NOT NULL
);

ALTER TABLE ONLY prefix_templates_rights
    ADD CONSTRAINT prefix_templates_rights_pkey PRIMARY KEY ("uid","folder");


CREATE TABLE prefix_templates (
  "folder" character varying(255) NOT NULL,
  "creator" integer NOT NULL
);

ALTER TABLE ONLY prefix_templates
    ADD CONSTRAINT prefix_templates_pkey PRIMARY KEY ("folder");


--
-- TOC entry 1736 (class 2606 OID 16430)
-- Dependencies: 1302 1302
-- Name: prefix_assessments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY prefix_assessments
    ADD CONSTRAINT prefix_assessments_pkey PRIMARY KEY (id,language);


--
-- TOC entry 1738 (class 2606 OID 16442)
-- Dependencies: 1304 1304
-- Name: prefix_conditions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY prefix_conditions
    ADD CONSTRAINT prefix_conditions_pkey PRIMARY KEY (cid);


--
-- TOC entry 1740 (class 2606 OID 16456)
-- Dependencies: 1306 1306 1306
-- Name: prefix_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY prefix_groups
    ADD CONSTRAINT prefix_groups_pkey PRIMARY KEY (gid, "language");


--
-- TOC entry 1743 (class 2606 OID 16464)
-- Dependencies: 1307 1307 1307 1307
-- Name: prefix_labels_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY prefix_labels
    ADD CONSTRAINT prefix_labels_pkey PRIMARY KEY (lid, sortorder, "language");


--
-- TOC entry 1745 (class 2606 OID 16474)
-- Dependencies: 1309 1309
-- Name: prefix_labelsets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY prefix_labelsets
    ADD CONSTRAINT prefix_labelsets_pkey PRIMARY KEY (lid);


--
-- TOC entry 1749 (class 2606 OID 16499)
-- Dependencies: 1313 1313
-- Name: prefix_question_attributes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY prefix_question_attributes
    ADD CONSTRAINT prefix_question_attributes_pkey PRIMARY KEY (qaid);


ALTER TABLE ONLY prefix_saved_control
    ADD CONSTRAINT prefix_saved_control_pkey PRIMARY KEY (scid);


--
-- TOC entry 1753 (class 2606 OID 16518)
-- Dependencies: 1316 1316
-- Name: prefix_settings_global_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY prefix_settings_global
    ADD CONSTRAINT prefix_settings_global_pkey PRIMARY KEY (stg_name);


--
-- TOC entry 1757 (class 2606 OID 16548)
-- Dependencies: 1318 1318 1318
-- Name: prefix_surveys_languagesettings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY prefix_surveys_languagesettings
    ADD CONSTRAINT prefix_surveys_languagesettings_pkey PRIMARY KEY (surveyls_survey_id, surveyls_language);


--
-- TOC entry 1755 (class 2606 OID 16539)
-- Dependencies: 1317 1317
-- Name: prefix_surveys_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY prefix_surveys
    ADD CONSTRAINT prefix_surveys_pkey PRIMARY KEY (sid);


--
-- TOC entry 1759 (class 2606 OID 16560)
-- Dependencies: 1319 1319 1319
-- Name: prefix_surveys_rights_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY prefix_surveys_rights
    ADD CONSTRAINT prefix_surveys_rights_pkey PRIMARY KEY (sid, uid);


--
-- TOC entry 1741 (class 1259 OID 16465)
-- Dependencies: 1307
-- Name: prefix_labels_ixcode_idx; Type: INDEX; Schema: public; Owner: postgres; Tablespace: 
--

CREATE INDEX prefix_labels_ixcode_idx ON prefix_labels USING btree (code);

--
-- Table `settings_global`
--

INSERT INTO prefix_settings_global VALUES ('DBVersion', '144');
INSERT INTO prefix_settings_global VALUES ('SessionName', '$sessionname');



--
-- indexes 
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



--
-- Create admin user
--

INSERT INTO prefix_users(
            users_name, "password", full_name, parent_id, lang, email, 
            create_survey, create_user, delete_user, superadmin, configurator, 
            manage_template, manage_label,htmleditormode)
            VALUES ('$defaultuser', '$defaultpass', '$siteadminname', 0, '$defaultlang', '$siteadminemail',1,1,1,1,1,1,1,'default');
