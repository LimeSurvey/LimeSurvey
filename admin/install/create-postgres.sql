--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- TOC entry 1633 (class 0 OID 0)
-- Dependencies: 4
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'Standard public schema';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- TOC entry 1205 (class 1259 OID 16405)
-- Dependencies: 1545 1546 1547 1548 4
-- Name: answers; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE answers (
    qid integer DEFAULT 0 NOT NULL,
    code character varying(5) DEFAULT ''::character varying NOT NULL,
    answer text DEFAULT ''::text NOT NULL,
    default_value character(1) DEFAULT 'N'::bpchar NOT NULL,
    sortorder character varying(5)
);



--
-- TOC entry 1207 (class 1259 OID 16418)
-- Dependencies: 1550 1551 1552 1553 1554 1555 1556 1557 4
-- Name: assessments; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE assessments (
    id serial NOT NULL,
    sid integer DEFAULT 0 NOT NULL,
    scope character varying(5) DEFAULT ''::character varying NOT NULL,
    gid integer DEFAULT 0 NOT NULL,
    name text DEFAULT ''::text NOT NULL,
    minimum character varying(50) DEFAULT ''::character varying NOT NULL,
    maximum character varying(50) DEFAULT ''::character varying NOT NULL,
    message text DEFAULT ''::text NOT NULL,
    link text DEFAULT ''::text NOT NULL
);



--
-- TOC entry 1209 (class 1259 OID 16436)
-- Dependencies: 1559 1560 1561 1562 1563 4
-- Name: conditions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE conditions (
    cid serial NOT NULL,
    qid integer DEFAULT 0 NOT NULL,
    cqid integer DEFAULT 0 NOT NULL,
    cfieldname character varying(50) DEFAULT ''::character varying NOT NULL,
    method character(2) DEFAULT ''::bpchar NOT NULL,
    value character varying(5) DEFAULT ''::character varying NOT NULL
);



--
-- TOC entry 1211 (class 1259 OID 16448)
-- Dependencies: 1565 1566 4
-- Name: groups; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE groups (
    gid serial NOT NULL,
    sid integer DEFAULT 0 NOT NULL,
    group_name character varying(100) DEFAULT ''::character varying NOT NULL,
    group_order character varying(45),
    description text,
    sortorder character varying(5)
);


--
-- TOC entry 1212 (class 1259 OID 16458)
-- Dependencies: 1567 1568 1569 4
-- Name: labels; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE labels (
    lid integer DEFAULT 0 NOT NULL,
    code character varying(5) DEFAULT ''::character varying NOT NULL,
    title character varying(100) DEFAULT ''::character varying NOT NULL,
    sortorder character varying(5)
);



--
-- TOC entry 1214 (class 1259 OID 16467)
-- Dependencies: 1571 4
-- Name: labelsets; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE labelsets (
    lid serial NOT NULL,
    label_name character varying(100) DEFAULT ''::character varying NOT NULL
);


--
-- TOC entry 1216 (class 1259 OID 16475)
-- Dependencies: 1573 4
-- Name: question_attributes; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE question_attributes (
    qaid serial NOT NULL,
    qid integer DEFAULT 0 NOT NULL,
    attribute character varying(50),
    value character varying(20)
);



--
-- TOC entry 1218 (class 1259 OID 16483)
-- Dependencies: 1575 1576 1577 1578 1579 1580 1581 4
-- Name: questions; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE questions (
    qid serial NOT NULL,
    sid integer DEFAULT 0 NOT NULL,
    gid integer DEFAULT 0 NOT NULL,
    "type" character(1) DEFAULT 'T'::bpchar NOT NULL,
    title character varying(20) DEFAULT ''::character varying NOT NULL,
    question text DEFAULT ''::text NOT NULL,
    preg text,
    help text,
    other character(1) DEFAULT 'N'::bpchar NOT NULL,
    mandatory character(1),
    lid integer DEFAULT 0 NOT NULL
);



--
-- TOC entry 1220 (class 1259 OID 16500)
-- Dependencies: 1583 1584 1585 1586 1587 1588 1589 4
-- Name: saved_control; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE saved_control (
    scid serial NOT NULL,
    sid integer DEFAULT 0 NOT NULL,
    srid integer DEFAULT 0 NOT NULL,
    identifier text DEFAULT ''::text NOT NULL,
    access_code text DEFAULT ''::text NOT NULL,
    email character varying(200),
    ip text DEFAULT ''::text NOT NULL,
    saved_thisstep text DEFAULT ''::text NOT NULL,
    status character(1) DEFAULT ''::bpchar NOT NULL,
    saved_date timestamp without time zone NOT NULL,
    refurl text
);


--
-- TOC entry 1221 (class 1259 OID 16515)
-- Dependencies: 1590 1591 4
-- Name: settings_global; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE settings_global (
    stg_name character varying(50) DEFAULT ''::character varying NOT NULL,
    stg_value character varying(255) DEFAULT ''::character varying NOT NULL
);



--
-- TOC entry 1222 (class 1259 OID 16521)
-- Dependencies: 1592 1593 1594 1595 1596 1597 1598 1599 1600 1601 1602 1603 1604 1605 1606 4
-- Name: surveys; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE surveys (
    sid integer DEFAULT 0 NOT NULL,
    short_title character varying(200) DEFAULT ''::character varying NOT NULL,
    description text,
    "admin" character varying(50),
    active character(1) DEFAULT 'N'::bpchar NOT NULL,
    welcome text,
    expires date,
    adminemail character varying(100),
    private character(1),
    faxto character varying(20),
    format character(1),
    "template" character varying(100) DEFAULT 'default'::character varying,
    url character varying(255),
    urldescrip character varying(255),
    "language" character varying(50),
    datestamp character(1) DEFAULT 'N'::bpchar,
    usecookie character(1) DEFAULT 'N'::bpchar,
    notification character(1) DEFAULT '0'::bpchar,
    allowregister character(1) DEFAULT 'N'::bpchar,
    attribute1 character varying(255),
    attribute2 character varying(255),
    email_invite_subj character varying(255),
    email_invite text,
    email_remind_subj character varying(255),
    email_remind text,
    email_register_subj character varying(255),
    email_register text,
    email_confirm_subj character varying(255),
    email_confirm text,
    allowsave character(1) DEFAULT 'Y'::bpchar,
    autonumber_start bigint DEFAULT 0,
    autoredirect character(1) DEFAULT 'N'::bpchar,
    allowprev character(1) DEFAULT 'Y'::bpchar,
    ipaddr character(1) DEFAULT 'N'::bpchar,
    useexpiry character(1) DEFAULT 'N'::bpchar NOT NULL,
    refurl character(1) DEFAULT 'N'::bpchar,
    datecreated date
);



--
-- TOC entry 1223 (class 1259 OID 16543)
-- Dependencies: 1607 1608 1609 4
-- Name: users; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE users (
    "user" character varying(20) DEFAULT ''::character varying NOT NULL,
    "password" character varying(20) DEFAULT ''::character varying NOT NULL,
    "security" character varying(10) DEFAULT ''::character varying NOT NULL
);




--
-- TOC entry 1611 (class 2606 OID 16415)
-- Dependencies: 1205 1205 1205
-- Name: answers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY answers
    ADD CONSTRAINT answers_pkey PRIMARY KEY (qid, code);


--
-- TOC entry 1613 (class 2606 OID 16433)
-- Dependencies: 1207 1207
-- Name: assessments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY assessments
    ADD CONSTRAINT assessments_pkey PRIMARY KEY (id);


--
-- TOC entry 1615 (class 2606 OID 16445)
-- Dependencies: 1209 1209
-- Name: conditions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY conditions
    ADD CONSTRAINT conditions_pkey PRIMARY KEY (cid);


--
-- TOC entry 1617 (class 2606 OID 16457)
-- Dependencies: 1211 1211
-- Name: groups_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY groups
    ADD CONSTRAINT groups_pkey PRIMARY KEY (gid);


--
-- TOC entry 1619 (class 2606 OID 16464)
-- Dependencies: 1212 1212 1212
-- Name: labels_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY labels
    ADD CONSTRAINT labels_pkey PRIMARY KEY (lid, code);


--
-- TOC entry 1621 (class 2606 OID 16472)
-- Dependencies: 1214 1214
-- Name: labelsets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY labelsets
    ADD CONSTRAINT labelsets_pkey PRIMARY KEY (lid);


--
-- TOC entry 1623 (class 2606 OID 16480)
-- Dependencies: 1216 1216
-- Name: question_attributes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY question_attributes
    ADD CONSTRAINT question_attributes_pkey PRIMARY KEY (qaid);


--
-- TOC entry 1625 (class 2606 OID 16497)
-- Dependencies: 1218 1218
-- Name: questions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY questions
    ADD CONSTRAINT questions_pkey PRIMARY KEY (qid);


--
-- TOC entry 1627 (class 2606 OID 16514)
-- Dependencies: 1220 1220
-- Name: saved_control_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY saved_control
    ADD CONSTRAINT saved_control_pkey PRIMARY KEY (scid);


--
-- TOC entry 1629 (class 2606 OID 16520)
-- Dependencies: 1221 1221
-- Name: settings_global_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY settings_global
    ADD CONSTRAINT settings_global_pkey PRIMARY KEY (stg_name);


--
-- TOC entry 1631 (class 2606 OID 16542)
-- Dependencies: 1222 1222
-- Name: surveys_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres; Tablespace: 
--

ALTER TABLE ONLY surveys
    ADD CONSTRAINT surveys_pkey PRIMARY KEY (sid);




