--
-- PostgreSQL database dump
--

-- Started on 2007-11-18 18:48:15

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- TOC entry 1763 (class 0 OID 0)
-- Dependencies: 4
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'Standard public schema';


--
-- TOC entry 295 (class 2612 OID 16386)
-- Name: plpgsql; Type: PROCEDURAL LANGUAGE; Schema: -; Owner: postgres
--


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
    default_value character(1) DEFAULT 'N'::bpchar NOT NULL,
    sortorder integer NOT NULL,
    "language" character varying(20) DEFAULT 'en'::character varying NOT NULL
);



--
-- TOC entry 1302 (class 1259 OID 16418)
-- Dependencies: 1660 1661 1662 1663 1664 4
-- Name: prefix_assessments; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_assessments (
    id integer NOT NULL,
    sid integer DEFAULT 0 NOT NULL,
    scope character varying(5) DEFAULT ''::character varying NOT NULL,
    gid integer DEFAULT 0 NOT NULL,
    name text NOT NULL,
    minimum character varying(50) DEFAULT ''::character varying NOT NULL,
    maximum character varying(50) DEFAULT ''::character varying NOT NULL,
    message text NOT NULL,
    link text NOT NULL
);



--
-- TOC entry 1301 (class 1259 OID 16416)
-- Dependencies: 1302 4
-- Name: prefix_assessments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE prefix_assessments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;



--
-- TOC entry 1765 (class 0 OID 0)
-- Dependencies: 1301
-- Name: prefix_assessments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE prefix_assessments_id_seq OWNED BY prefix_assessments.id;


--
-- TOC entry 1304 (class 1259 OID 16433)
-- Dependencies: 1666 1667 1668 1669 1670 4
-- Name: prefix_conditions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_conditions (
    cid integer NOT NULL,
    qid integer DEFAULT 0 NOT NULL,
    cqid integer DEFAULT 0 NOT NULL,
    cfieldname character varying(50) DEFAULT ''::character varying NOT NULL,
    method character(2) DEFAULT ''::bpchar NOT NULL,
    value character varying(5) DEFAULT ''::character varying NOT NULL
);



--
-- TOC entry 1303 (class 1259 OID 16431)
-- Dependencies: 1304 4
-- Name: prefix_conditions_cid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE prefix_conditions_cid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;



--
-- TOC entry 1766 (class 0 OID 0)
-- Dependencies: 1303
-- Name: prefix_conditions_cid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE prefix_conditions_cid_seq OWNED BY prefix_conditions.cid;


--
-- TOC entry 1306 (class 1259 OID 16445)
-- Dependencies: 1672 1673 1674 1675 4
-- Name: prefix_groups; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_groups (
    gid integer NOT NULL,
    sid integer DEFAULT 0 NOT NULL,
    group_name character varying(100) DEFAULT ''::character varying NOT NULL,
    group_order integer DEFAULT 0 NOT NULL,
    description text,
    "language" character varying(20) DEFAULT 'en'::character varying NOT NULL
);



--
-- TOC entry 1305 (class 1259 OID 16443)
-- Dependencies: 1306 4
-- Name: prefix_groups_gid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE prefix_groups_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;



--
-- TOC entry 1767 (class 0 OID 0)
-- Dependencies: 1305
-- Name: prefix_groups_gid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE prefix_groups_gid_seq OWNED BY prefix_groups.gid;


--
-- TOC entry 1307 (class 1259 OID 16457)
-- Dependencies: 1676 1677 1678 1679 4
-- Name: prefix_labels; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_labels (
    lid integer DEFAULT 0 NOT NULL,
    code character varying(5) DEFAULT ''::character varying NOT NULL,
    title character varying(100) DEFAULT ''::character varying NOT NULL,
    sortorder integer NOT NULL,
    "language" character varying(20) DEFAULT 'en'::character varying NOT NULL
);



--
-- TOC entry 1309 (class 1259 OID 16468)
-- Dependencies: 1681 1682 4
-- Name: prefix_labelsets; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_labelsets (
    lid integer NOT NULL,
    label_name character varying(100) DEFAULT ''::character varying NOT NULL,
    languages character varying(200) DEFAULT 'en'::character varying
);



--
-- TOC entry 1308 (class 1259 OID 16466)
-- Dependencies: 1309 4
-- Name: prefix_labelsets_lid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE prefix_labelsets_lid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;



--
-- TOC entry 1768 (class 0 OID 0)
-- Dependencies: 1308
-- Name: prefix_labelsets_lid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE prefix_labelsets_lid_seq OWNED BY prefix_labelsets.lid;


--
-- TOC entry 1313 (class 1259 OID 16494)
-- Dependencies: 1692 4
-- Name: prefix_question_attributes; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_question_attributes (
    qaid integer NOT NULL,
    qid integer DEFAULT 0 NOT NULL,
    attribute character varying(50),
    value character varying(20)
);



--
-- TOC entry 1312 (class 1259 OID 16492)
-- Dependencies: 1313 4
-- Name: prefix_question_attributes_qaid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE prefix_question_attributes_qaid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;



--
-- TOC entry 1769 (class 0 OID 0)
-- Dependencies: 1312
-- Name: prefix_question_attributes_qaid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE prefix_question_attributes_qaid_seq OWNED BY prefix_question_attributes.qaid;


--
-- TOC entry 1311 (class 1259 OID 16477)
-- Dependencies: 1684 1685 1686 1687 1688 1689 1690 4
-- Name: prefix_questions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_questions (
    qid integer NOT NULL,
    sid integer DEFAULT 0 NOT NULL,
    gid integer DEFAULT 0 NOT NULL,
    "type" character(1) DEFAULT 'T'::bpchar NOT NULL,
    title character varying(20) DEFAULT ''::character varying NOT NULL,
    question text NOT NULL,
    preg text,
    help text,
    other character(1) DEFAULT 'N'::bpchar NOT NULL,
    mandatory character(1),
    lid integer DEFAULT 0 NOT NULL,
    question_order integer NOT NULL,
    "language" character varying(20) DEFAULT 'en'::character varying NOT NULL
);



--
-- TOC entry 1310 (class 1259 OID 16475)
-- Dependencies: 1311 4
-- Name: prefix_questions_qid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE prefix_questions_qid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;



--
-- TOC entry 1770 (class 0 OID 0)
-- Dependencies: 1310
-- Name: prefix_questions_qid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE prefix_questions_qid_seq OWNED BY prefix_questions.qid;


--
-- TOC entry 1315 (class 1259 OID 16502)
-- Dependencies: 1694 1695 1696 4
-- Name: prefix_saved_control; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_saved_control (
    scid integer NOT NULL,
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



--
-- TOC entry 1314 (class 1259 OID 16500)
-- Dependencies: 1315 4
-- Name: prefix_saved_control_scid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE prefix_saved_control_scid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;



--
-- TOC entry 1771 (class 0 OID 0)
-- Dependencies: 1314
-- Name: prefix_saved_control_scid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE prefix_saved_control_scid_seq OWNED BY prefix_saved_control.scid;


--
-- TOC entry 1316 (class 1259 OID 16513)
-- Dependencies: 1697 1698 4
-- Name: prefix_settings_global; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_settings_global (
    stg_name character varying(50) DEFAULT ''::character varying NOT NULL,
    stg_value character varying(255) DEFAULT ''::character varying NOT NULL
);



--
-- TOC entry 1317 (class 1259 OID 16519)
-- Dependencies: 1699 1700 1701 1702 1703 1704 1705 1706 1707 1708 1709 1710 1711 1712 4
-- Name: prefix_surveys; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_surveys (
    sid integer NOT NULL,
    owner_id integer NOT NULL,
    "admin" character varying(50),
    active character(1) DEFAULT 'N'::bpchar NOT NULL,
    expires date,
    adminemail character varying(320) NOT NULL,
    private character(1),
    faxto character varying(20),
    format character(1),
    "template" character varying(100) DEFAULT 'default'::character varying,
    url character varying(255),
    "language" character varying(50),
    additional_languages character varying(255),
    datestamp character(1) DEFAULT 'N'::bpchar,
    usecookie character(1) DEFAULT 'N'::bpchar,
    notification character(1) DEFAULT '0'::bpchar,
    allowregister character(1) DEFAULT 'N'::bpchar,
    attribute1 character varying(255),
    attribute2 character varying(255),
    allowsave character(1) DEFAULT 'Y'::bpchar,
    printanswers character(1) DEFAULT 'N'::bpchar,
    autonumber_start integer DEFAULT 0,
    autoredirect character(1) DEFAULT 'N'::bpchar,
    allowprev character(1) DEFAULT 'Y'::bpchar,
    ipaddr character(1) DEFAULT 'N'::bpchar,
    useexpiry character(1) DEFAULT 'N'::bpchar NOT NULL,
    refurl character(1) DEFAULT 'N'::bpchar,
    datecreated date,
    listpublic character(1) DEFAULT 'N'::bpchar
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
    surveyls_urldescription character varying(255),
    surveyls_email_invite_subj character varying(255),
    surveyls_email_invite text,
    surveyls_email_remind_subj character varying(255),
    surveyls_email_remind text,
    surveyls_email_register_subj character varying(255),
    surveyls_email_register text,
    surveyls_email_confirm_subj character varying(255),
    surveyls_email_confirm text
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



--
-- TOC entry 1323 (class 1259 OID 16579)
-- Dependencies: 4
-- Name: prefix_user_groups; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_user_groups (
    ugid integer NOT NULL,
    name character varying(20) NOT NULL,
    description text NOT NULL,
    owner_id integer NOT NULL
);



--
-- TOC entry 1322 (class 1259 OID 16577)
-- Dependencies: 1323 4
-- Name: prefix_user_groups_ugid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE prefix_user_groups_ugid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;



--
-- TOC entry 1772 (class 0 OID 0)
-- Dependencies: 1322
-- Name: prefix_user_groups_ugid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE prefix_user_groups_ugid_seq OWNED BY prefix_user_groups.ugid;


--
-- TOC entry 1324 (class 1259 OID 16585)
-- Dependencies: 4
-- Name: prefix_user_in_groups; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_user_in_groups (
    ugid integer NOT NULL,
    uid integer NOT NULL
);



--
-- TOC entry 1321 (class 1259 OID 16563)
-- Dependencies: 1724 1725 1726 1727 1728 1729 1730 1731 4
-- Name: prefix_users; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE prefix_users (
    uid integer NOT NULL,
    users_name character varying(64) DEFAULT ''::character varying NOT NULL,
    "password" bytea NOT NULL,
    full_name character varying(50) NOT NULL,
    parent_id integer NOT NULL,
    lang character varying(20),
    email character varying(320) NOT NULL,
    create_survey integer DEFAULT 0 NOT NULL,
    create_user integer DEFAULT 0 NOT NULL,
    delete_user integer DEFAULT 0 NOT NULL,
    move_user integer DEFAULT 0 NOT NULL,
    configurator integer DEFAULT 0 NOT NULL,
    manage_template integer DEFAULT 0 NOT NULL,
    manage_label integer DEFAULT 0 NOT NULL
);



--
-- TOC entry 1320 (class 1259 OID 16561)
-- Dependencies: 4 1321
-- Name: prefix_users_uid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE prefix_users_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1773 (class 0 OID 0)
-- Dependencies: 1320
-- Name: prefix_users_uid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE prefix_users_uid_seq OWNED BY prefix_users.uid;


--
-- TOC entry 1659 (class 2604 OID 16420)
-- Dependencies: 1302 1301 1302
-- Name: id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE prefix_assessments ALTER COLUMN id SET DEFAULT nextval('prefix_assessments_id_seq'::regclass);


--
-- TOC entry 1665 (class 2604 OID 16435)
-- Dependencies: 1304 1303 1304
-- Name: cid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE prefix_conditions ALTER COLUMN cid SET DEFAULT nextval('prefix_conditions_cid_seq'::regclass);


--
-- TOC entry 1671 (class 2604 OID 16447)
-- Dependencies: 1306 1305 1306
-- Name: gid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE prefix_groups ALTER COLUMN gid SET DEFAULT nextval('prefix_groups_gid_seq'::regclass);


--
-- TOC entry 1680 (class 2604 OID 16470)
-- Dependencies: 1309 1308 1309
-- Name: lid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE prefix_labelsets ALTER COLUMN lid SET DEFAULT nextval('prefix_labelsets_lid_seq'::regclass);


--
-- TOC entry 1691 (class 2604 OID 16496)
-- Dependencies: 1313 1312 1313
-- Name: qaid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE prefix_question_attributes ALTER COLUMN qaid SET DEFAULT nextval('prefix_question_attributes_qaid_seq'::regclass);


--
-- TOC entry 1683 (class 2604 OID 16479)
-- Dependencies: 1310 1311 1311
-- Name: qid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE prefix_questions ALTER COLUMN qid SET DEFAULT nextval('prefix_questions_qid_seq'::regclass);


--
-- TOC entry 1693 (class 2604 OID 16504)
-- Dependencies: 1314 1315 1315
-- Name: scid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE prefix_saved_control ALTER COLUMN scid SET DEFAULT nextval('prefix_saved_control_scid_seq'::regclass);


--
-- TOC entry 1732 (class 2604 OID 16581)
-- Dependencies: 1323 1322 1323
-- Name: ugid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE prefix_user_groups ALTER COLUMN ugid SET DEFAULT nextval('prefix_user_groups_ugid_seq'::regclass);


--
-- TOC entry 1723 (class 2604 OID 16565)
-- Dependencies: 1320 1321 1321
-- Name: uid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE prefix_users ALTER COLUMN uid SET DEFAULT nextval('prefix_users_uid_seq'::regclass);


--
-- TOC entry 1734 (class 2606 OID 16415)
-- Dependencies: 1300 1300 1300 1300
-- Name: prefix_answers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY prefix_answers
    ADD CONSTRAINT prefix_answers_pkey PRIMARY KEY (qid, code, "language");


--
-- TOC entry 1736 (class 2606 OID 16430)
-- Dependencies: 1302 1302
-- Name: prefix_assessments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY prefix_assessments
    ADD CONSTRAINT prefix_assessments_pkey PRIMARY KEY (id);


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


--
-- TOC entry 1747 (class 2606 OID 16491)
-- Dependencies: 1311 1311 1311
-- Name: prefix_questions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY prefix_questions
    ADD CONSTRAINT prefix_questions_pkey PRIMARY KEY (qid, "language");


--
-- TOC entry 1751 (class 2606 OID 16512)
-- Dependencies: 1315 1315
-- Name: prefix_saved_control_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

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

INSERT INTO prefix_settings_global VALUES ('DBVersion', '118');
INSERT INTO prefix_settings_global VALUES ('SessionName', '$sessionname');

--
-- Table `users`
--

INSERT INTO prefix_users(
            users_name, "password", full_name, parent_id, lang, email, 
            create_survey, create_user, delete_user, move_user, configurator, 
            manage_template, manage_label)
            VALUES ('$defaultuser', '$defaultpass', '$siteadminname', 0, '$defaultlang', '$siteadminemail', 1,1,1,1,1,1,1);

