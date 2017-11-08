--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5.8
-- Dumped by pg_dump version 9.5.8

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: lime_answers; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_answers (
    qid integer DEFAULT 0 NOT NULL,
    code character varying(5) DEFAULT ''::character varying NOT NULL,
    answer text NOT NULL,
    sortorder integer NOT NULL,
    language character varying(20) DEFAULT 'en'::character varying NOT NULL,
    assessment_value integer DEFAULT 0 NOT NULL,
    scale_id integer DEFAULT 0 NOT NULL
);


ALTER TABLE lime_answers OWNER TO limesurvey;

--
-- Name: lime_assessments; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_assessments (
    id integer NOT NULL,
    sid integer DEFAULT 0 NOT NULL,
    scope character varying(5) DEFAULT ''::character varying NOT NULL,
    gid integer DEFAULT 0 NOT NULL,
    name text NOT NULL,
    minimum character varying(50) DEFAULT ''::character varying NOT NULL,
    maximum character varying(50) DEFAULT ''::character varying NOT NULL,
    message text NOT NULL,
    language character varying(20) DEFAULT 'en'::character varying NOT NULL
);


ALTER TABLE lime_assessments OWNER TO limesurvey;

--
-- Name: lime_assessments_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_assessments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_assessments_id_seq OWNER TO limesurvey;

--
-- Name: lime_assessments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_assessments_id_seq OWNED BY lime_assessments.id;


--
-- Name: lime_boxes; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_boxes (
    id integer NOT NULL,
    "position" integer,
    url text NOT NULL,
    title text NOT NULL,
    ico text,
    "desc" text NOT NULL,
    page text NOT NULL,
    usergroup integer NOT NULL
);


ALTER TABLE lime_boxes OWNER TO limesurvey;

--
-- Name: lime_boxes_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_boxes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_boxes_id_seq OWNER TO limesurvey;

--
-- Name: lime_boxes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_boxes_id_seq OWNED BY lime_boxes.id;


--
-- Name: lime_conditions; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_conditions (
    cid integer NOT NULL,
    qid integer DEFAULT 0 NOT NULL,
    cqid integer DEFAULT 0 NOT NULL,
    cfieldname character varying(50) DEFAULT ''::character varying NOT NULL,
    method character varying(5) DEFAULT ''::character varying NOT NULL,
    value character varying(255) DEFAULT ''::character varying NOT NULL,
    scenario integer DEFAULT 1 NOT NULL
);


ALTER TABLE lime_conditions OWNER TO limesurvey;

--
-- Name: lime_conditions_cid_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_conditions_cid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_conditions_cid_seq OWNER TO limesurvey;

--
-- Name: lime_conditions_cid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_conditions_cid_seq OWNED BY lime_conditions.cid;


--
-- Name: lime_defaultvalues; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_defaultvalues (
    qid integer DEFAULT 0 NOT NULL,
    scale_id integer DEFAULT 0 NOT NULL,
    sqid integer DEFAULT 0 NOT NULL,
    language character varying(20) NOT NULL,
    specialtype character varying(20) DEFAULT ''::character varying NOT NULL,
    defaultvalue text
);


ALTER TABLE lime_defaultvalues OWNER TO limesurvey;

--
-- Name: lime_expression_errors; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_expression_errors (
    id integer NOT NULL,
    errortime character varying(50),
    sid integer,
    gid integer,
    qid integer,
    gseq integer,
    qseq integer,
    type character varying(50),
    eqn text,
    prettyprint text
);


ALTER TABLE lime_expression_errors OWNER TO limesurvey;

--
-- Name: lime_expression_errors_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_expression_errors_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_expression_errors_id_seq OWNER TO limesurvey;

--
-- Name: lime_expression_errors_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_expression_errors_id_seq OWNED BY lime_expression_errors.id;


--
-- Name: lime_failed_login_attempts; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_failed_login_attempts (
    id integer NOT NULL,
    ip character varying(40) NOT NULL,
    last_attempt character varying(20) NOT NULL,
    number_attempts integer NOT NULL
);


ALTER TABLE lime_failed_login_attempts OWNER TO limesurvey;

--
-- Name: lime_failed_login_attempts_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_failed_login_attempts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_failed_login_attempts_id_seq OWNER TO limesurvey;

--
-- Name: lime_failed_login_attempts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_failed_login_attempts_id_seq OWNED BY lime_failed_login_attempts.id;


--
-- Name: lime_groups; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_groups (
    gid integer NOT NULL,
    sid integer DEFAULT 0 NOT NULL,
    group_name character varying(100) DEFAULT ''::character varying NOT NULL,
    group_order integer DEFAULT 0 NOT NULL,
    description text,
    language character varying(20) DEFAULT 'en'::character varying NOT NULL,
    randomization_group character varying(20) DEFAULT ''::character varying NOT NULL,
    grelevance text
);


ALTER TABLE lime_groups OWNER TO limesurvey;

--
-- Name: lime_groups_gid_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_groups_gid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_groups_gid_seq OWNER TO limesurvey;

--
-- Name: lime_groups_gid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_groups_gid_seq OWNED BY lime_groups.gid;


--
-- Name: lime_labels; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_labels (
    lid integer DEFAULT 0 NOT NULL,
    code character varying(5) DEFAULT ''::character varying NOT NULL,
    title text,
    sortorder integer NOT NULL,
    assessment_value integer DEFAULT 0 NOT NULL,
    language character varying(20) DEFAULT 'en'::character varying NOT NULL
);


ALTER TABLE lime_labels OWNER TO limesurvey;

--
-- Name: lime_labelsets; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_labelsets (
    lid integer NOT NULL,
    label_name character varying(100) DEFAULT ''::character varying NOT NULL,
    languages character varying(200) DEFAULT 'en'::character varying
);


ALTER TABLE lime_labelsets OWNER TO limesurvey;

--
-- Name: lime_labelsets_lid_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_labelsets_lid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_labelsets_lid_seq OWNER TO limesurvey;

--
-- Name: lime_labelsets_lid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_labelsets_lid_seq OWNED BY lime_labelsets.lid;


--
-- Name: lime_notifications; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_notifications (
    id integer NOT NULL,
    entity character varying(15) NOT NULL,
    entity_id integer NOT NULL,
    title character varying(255) NOT NULL,
    message text NOT NULL,
    status character varying(15) DEFAULT 'new'::character varying NOT NULL,
    importance integer DEFAULT 1 NOT NULL,
    display_class character varying(31) DEFAULT 'default'::character varying,
    hash character varying(64) DEFAULT NULL::character varying,
    created timestamp without time zone NOT NULL,
    first_read timestamp without time zone
);


ALTER TABLE lime_notifications OWNER TO limesurvey;

--
-- Name: lime_notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_notifications_id_seq OWNER TO limesurvey;

--
-- Name: lime_notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_notifications_id_seq OWNED BY lime_notifications.id;


--
-- Name: lime_participant_attribute; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_participant_attribute (
    participant_id character varying(50) NOT NULL,
    attribute_id integer NOT NULL,
    value text NOT NULL
);


ALTER TABLE lime_participant_attribute OWNER TO limesurvey;

--
-- Name: lime_participant_attribute_names; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_participant_attribute_names (
    attribute_id integer NOT NULL,
    attribute_type character varying(4) NOT NULL,
    defaultname character varying(255) NOT NULL,
    visible character varying(5) NOT NULL
);


ALTER TABLE lime_participant_attribute_names OWNER TO limesurvey;

--
-- Name: lime_participant_attribute_names_attribute_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_participant_attribute_names_attribute_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_participant_attribute_names_attribute_id_seq OWNER TO limesurvey;

--
-- Name: lime_participant_attribute_names_attribute_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_participant_attribute_names_attribute_id_seq OWNED BY lime_participant_attribute_names.attribute_id;


--
-- Name: lime_participant_attribute_names_lang; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_participant_attribute_names_lang (
    attribute_id integer NOT NULL,
    attribute_name character varying(255) NOT NULL,
    lang character varying(20) NOT NULL
);


ALTER TABLE lime_participant_attribute_names_lang OWNER TO limesurvey;

--
-- Name: lime_participant_attribute_values; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_participant_attribute_values (
    value_id integer NOT NULL,
    attribute_id integer NOT NULL,
    value text NOT NULL
);


ALTER TABLE lime_participant_attribute_values OWNER TO limesurvey;

--
-- Name: lime_participant_attribute_values_value_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_participant_attribute_values_value_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_participant_attribute_values_value_id_seq OWNER TO limesurvey;

--
-- Name: lime_participant_attribute_values_value_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_participant_attribute_values_value_id_seq OWNED BY lime_participant_attribute_values.value_id;


--
-- Name: lime_participant_shares; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_participant_shares (
    participant_id character varying(50) NOT NULL,
    share_uid integer NOT NULL,
    date_added timestamp without time zone NOT NULL,
    can_edit character varying(5) NOT NULL
);


ALTER TABLE lime_participant_shares OWNER TO limesurvey;

--
-- Name: lime_participants; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_participants (
    participant_id character varying(50) NOT NULL,
    firstname character varying(150),
    lastname character varying(150),
    email text,
    language character varying(40),
    blacklisted character varying(1) NOT NULL,
    owner_uid integer NOT NULL,
    created_by integer NOT NULL,
    created timestamp without time zone,
    modified timestamp without time zone
);


ALTER TABLE lime_participants OWNER TO limesurvey;

--
-- Name: lime_permissions; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_permissions (
    id integer NOT NULL,
    entity character varying(50) NOT NULL,
    entity_id integer NOT NULL,
    uid integer NOT NULL,
    permission character varying(100) NOT NULL,
    create_p integer DEFAULT 0 NOT NULL,
    read_p integer DEFAULT 0 NOT NULL,
    update_p integer DEFAULT 0 NOT NULL,
    delete_p integer DEFAULT 0 NOT NULL,
    import_p integer DEFAULT 0 NOT NULL,
    export_p integer DEFAULT 0 NOT NULL
);


ALTER TABLE lime_permissions OWNER TO limesurvey;

--
-- Name: lime_permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_permissions_id_seq OWNER TO limesurvey;

--
-- Name: lime_permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_permissions_id_seq OWNED BY lime_permissions.id;


--
-- Name: lime_plugin_settings; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_plugin_settings (
    id integer NOT NULL,
    plugin_id integer NOT NULL,
    model character varying(50),
    model_id integer,
    key character varying(50) NOT NULL,
    value text
);


ALTER TABLE lime_plugin_settings OWNER TO limesurvey;

--
-- Name: lime_plugin_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_plugin_settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_plugin_settings_id_seq OWNER TO limesurvey;

--
-- Name: lime_plugin_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_plugin_settings_id_seq OWNED BY lime_plugin_settings.id;


--
-- Name: lime_plugins; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_plugins (
    id integer NOT NULL,
    name character varying(50) NOT NULL,
    active integer DEFAULT 0 NOT NULL,
    version character varying(32) DEFAULT NULL::character varying
);


ALTER TABLE lime_plugins OWNER TO limesurvey;

--
-- Name: lime_plugins_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_plugins_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_plugins_id_seq OWNER TO limesurvey;

--
-- Name: lime_plugins_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_plugins_id_seq OWNED BY lime_plugins.id;


--
-- Name: lime_question_attributes; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_question_attributes (
    qaid integer NOT NULL,
    qid integer DEFAULT 0 NOT NULL,
    attribute character varying(50),
    value text,
    language character varying(20)
);


ALTER TABLE lime_question_attributes OWNER TO limesurvey;

--
-- Name: lime_question_attributes_qaid_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_question_attributes_qaid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_question_attributes_qaid_seq OWNER TO limesurvey;

--
-- Name: lime_question_attributes_qaid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_question_attributes_qaid_seq OWNED BY lime_question_attributes.qaid;


--
-- Name: lime_questions; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_questions (
    qid integer NOT NULL,
    parent_qid integer DEFAULT 0 NOT NULL,
    sid integer DEFAULT 0 NOT NULL,
    gid integer DEFAULT 0 NOT NULL,
    type character varying(1) DEFAULT 'T'::character varying NOT NULL,
    title character varying(20) DEFAULT ''::character varying NOT NULL,
    question text NOT NULL,
    preg text,
    help text,
    other character varying(1) DEFAULT 'N'::character varying NOT NULL,
    mandatory character varying(1),
    question_order integer NOT NULL,
    language character varying(20) DEFAULT 'en'::character varying NOT NULL,
    scale_id integer DEFAULT 0 NOT NULL,
    same_default integer DEFAULT 0 NOT NULL,
    relevance text,
    modulename character varying(255)
);


ALTER TABLE lime_questions OWNER TO limesurvey;

--
-- Name: lime_questions_qid_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_questions_qid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_questions_qid_seq OWNER TO limesurvey;

--
-- Name: lime_questions_qid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_questions_qid_seq OWNED BY lime_questions.qid;


--
-- Name: lime_quota; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_quota (
    id integer NOT NULL,
    sid integer,
    name character varying(255),
    qlimit integer,
    action integer,
    active integer DEFAULT 1 NOT NULL,
    autoload_url integer DEFAULT 0 NOT NULL
);


ALTER TABLE lime_quota OWNER TO limesurvey;

--
-- Name: lime_quota_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_quota_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_quota_id_seq OWNER TO limesurvey;

--
-- Name: lime_quota_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_quota_id_seq OWNED BY lime_quota.id;


--
-- Name: lime_quota_languagesettings; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_quota_languagesettings (
    quotals_id integer NOT NULL,
    quotals_quota_id integer DEFAULT 0 NOT NULL,
    quotals_language character varying(45) DEFAULT 'en'::character varying NOT NULL,
    quotals_name character varying(255),
    quotals_message text NOT NULL,
    quotals_url character varying(255),
    quotals_urldescrip character varying(255)
);


ALTER TABLE lime_quota_languagesettings OWNER TO limesurvey;

--
-- Name: lime_quota_languagesettings_quotals_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_quota_languagesettings_quotals_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_quota_languagesettings_quotals_id_seq OWNER TO limesurvey;

--
-- Name: lime_quota_languagesettings_quotals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_quota_languagesettings_quotals_id_seq OWNED BY lime_quota_languagesettings.quotals_id;


--
-- Name: lime_quota_members; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_quota_members (
    id integer NOT NULL,
    sid integer,
    qid integer,
    quota_id integer,
    code character varying(11)
);


ALTER TABLE lime_quota_members OWNER TO limesurvey;

--
-- Name: lime_quota_members_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_quota_members_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_quota_members_id_seq OWNER TO limesurvey;

--
-- Name: lime_quota_members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_quota_members_id_seq OWNED BY lime_quota_members.id;


--
-- Name: lime_saved_control; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_saved_control (
    scid integer NOT NULL,
    sid integer DEFAULT 0 NOT NULL,
    srid integer DEFAULT 0 NOT NULL,
    identifier text NOT NULL,
    access_code text NOT NULL,
    email character varying(254),
    ip text NOT NULL,
    saved_thisstep text NOT NULL,
    status character varying(1) DEFAULT ''::character varying NOT NULL,
    saved_date timestamp without time zone NOT NULL,
    refurl text
);


ALTER TABLE lime_saved_control OWNER TO limesurvey;

--
-- Name: lime_saved_control_scid_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_saved_control_scid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_saved_control_scid_seq OWNER TO limesurvey;

--
-- Name: lime_saved_control_scid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_saved_control_scid_seq OWNED BY lime_saved_control.scid;


--
-- Name: lime_sessions; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_sessions (
    id character varying(32) NOT NULL,
    expire integer,
    data bytea
);


ALTER TABLE lime_sessions OWNER TO limesurvey;

--
-- Name: lime_settings_global; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_settings_global (
    stg_name character varying(50) DEFAULT ''::character varying NOT NULL,
    stg_value text NOT NULL
);


ALTER TABLE lime_settings_global OWNER TO limesurvey;

--
-- Name: lime_settings_user; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_settings_user (
    uid integer NOT NULL,
    entity character varying(15) DEFAULT NULL::character varying NOT NULL,
    entity_id character varying(31) DEFAULT NULL::character varying NOT NULL,
    stg_name character varying(63) NOT NULL,
    stg_value text
);


ALTER TABLE lime_settings_user OWNER TO limesurvey;

--
-- Name: lime_survey_links; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_survey_links (
    participant_id character varying(50) NOT NULL,
    token_id integer NOT NULL,
    survey_id integer NOT NULL,
    date_created timestamp without time zone,
    date_invited timestamp without time zone,
    date_completed timestamp without time zone
);


ALTER TABLE lime_survey_links OWNER TO limesurvey;

--
-- Name: lime_survey_url_parameters; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_survey_url_parameters (
    id integer NOT NULL,
    sid integer NOT NULL,
    parameter character varying(50) NOT NULL,
    targetqid integer,
    targetsqid integer
);


ALTER TABLE lime_survey_url_parameters OWNER TO limesurvey;

--
-- Name: lime_survey_url_parameters_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_survey_url_parameters_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_survey_url_parameters_id_seq OWNER TO limesurvey;

--
-- Name: lime_survey_url_parameters_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_survey_url_parameters_id_seq OWNED BY lime_survey_url_parameters.id;


--
-- Name: lime_surveymenu; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_surveymenu (
    id integer NOT NULL,
    parent_id integer,
    survey_id integer,
    user_id integer,
    ordering integer DEFAULT 0,
    level integer DEFAULT 0,
    title character varying(255) DEFAULT ''::character varying NOT NULL,
    "position" character varying(255) DEFAULT 'side'::character varying NOT NULL,
    description text,
    changed_at timestamp without time zone,
    changed_by integer DEFAULT 0 NOT NULL,
    created_at timestamp without time zone,
    created_by integer DEFAULT 0 NOT NULL,
    active boolean DEFAULT false
);


ALTER TABLE lime_surveymenu OWNER TO limesurvey;

--
-- Name: lime_surveymenu_entries; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_surveymenu_entries (
    id integer NOT NULL,
    menu_id integer,
    user_id integer,
    ordering integer DEFAULT 0,
    name character varying(255) DEFAULT ''::character varying NOT NULL,
    title character varying(255) DEFAULT ''::character varying NOT NULL,
    menu_title character varying(255) DEFAULT ''::character varying NOT NULL,
    menu_description text,
    menu_icon character varying(255) DEFAULT ''::character varying NOT NULL,
    menu_icon_type character varying(255) DEFAULT ''::character varying NOT NULL,
    menu_class character varying(255) DEFAULT ''::character varying NOT NULL,
    menu_link character varying(255) DEFAULT ''::character varying NOT NULL,
    action character varying(255) DEFAULT ''::character varying NOT NULL,
    template character varying(255) DEFAULT ''::character varying NOT NULL,
    partial character varying(255) DEFAULT ''::character varying NOT NULL,
    classes character varying(255) DEFAULT ''::character varying NOT NULL,
    permission character varying(255) DEFAULT ''::character varying NOT NULL,
    permission_grade character varying(255) DEFAULT NULL::character varying,
    data text,
    getdatamethod character varying(255) DEFAULT ''::character varying NOT NULL,
    language character varying(255) DEFAULT 'en-GB'::character varying NOT NULL,
    changed_at timestamp without time zone,
    changed_by integer DEFAULT 0 NOT NULL,
    created_at timestamp without time zone,
    created_by integer DEFAULT 0 NOT NULL,
    active boolean DEFAULT false
);


ALTER TABLE lime_surveymenu_entries OWNER TO limesurvey;

--
-- Name: lime_surveymenu_entries_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_surveymenu_entries_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_surveymenu_entries_id_seq OWNER TO limesurvey;

--
-- Name: lime_surveymenu_entries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_surveymenu_entries_id_seq OWNED BY lime_surveymenu_entries.id;


--
-- Name: lime_surveymenu_id_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_surveymenu_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_surveymenu_id_seq OWNER TO limesurvey;

--
-- Name: lime_surveymenu_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_surveymenu_id_seq OWNED BY lime_surveymenu.id;


--
-- Name: lime_surveys; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_surveys (
    sid integer NOT NULL,
    owner_id integer NOT NULL,
    admin character varying(50),
    active character varying(1) DEFAULT 'N'::character varying NOT NULL,
    expires timestamp without time zone,
    startdate timestamp without time zone,
    adminemail character varying(254),
    anonymized character varying(1) DEFAULT 'N'::character varying NOT NULL,
    faxto character varying(20),
    format character varying(1),
    savetimings character varying(1) DEFAULT 'N'::character varying NOT NULL,
    template character varying(100) DEFAULT 'default'::character varying,
    language character varying(50),
    additional_languages character varying(255),
    datestamp character varying(1) DEFAULT 'N'::character varying NOT NULL,
    usecookie character varying(1) DEFAULT 'N'::character varying NOT NULL,
    allowregister character varying(1) DEFAULT 'N'::character varying NOT NULL,
    allowsave character varying(1) DEFAULT 'Y'::character varying NOT NULL,
    autonumber_start integer DEFAULT 0 NOT NULL,
    autoredirect character varying(1) DEFAULT 'N'::character varying NOT NULL,
    allowprev character varying(1) DEFAULT 'N'::character varying NOT NULL,
    printanswers character varying(1) DEFAULT 'N'::character varying NOT NULL,
    ipaddr character varying(1) DEFAULT 'N'::character varying NOT NULL,
    refurl character varying(1) DEFAULT 'N'::character varying NOT NULL,
    datecreated date,
    publicstatistics character varying(1) DEFAULT 'N'::character varying NOT NULL,
    publicgraphs character varying(1) DEFAULT 'N'::character varying NOT NULL,
    listpublic character varying(1) DEFAULT 'N'::character varying NOT NULL,
    htmlemail character varying(1) DEFAULT 'N'::character varying NOT NULL,
    sendconfirmation character varying(1) DEFAULT 'Y'::character varying NOT NULL,
    tokenanswerspersistence character varying(1) DEFAULT 'N'::character varying NOT NULL,
    assessments character varying(1) DEFAULT 'N'::character varying NOT NULL,
    usecaptcha character varying(1) DEFAULT 'N'::character varying NOT NULL,
    usetokens character varying(1) DEFAULT 'N'::character varying NOT NULL,
    bounce_email character varying(254),
    attributedescriptions text,
    emailresponseto text,
    emailnotificationto text,
    tokenlength integer DEFAULT 15 NOT NULL,
    showxquestions character varying(1) DEFAULT 'Y'::character varying,
    showgroupinfo character varying(1) DEFAULT 'B'::character varying,
    shownoanswer character varying(1) DEFAULT 'Y'::character varying,
    showqnumcode character varying(1) DEFAULT 'X'::character varying,
    bouncetime integer,
    bounceprocessing character varying(1) DEFAULT 'N'::character varying,
    bounceaccounttype character varying(4),
    bounceaccounthost character varying(200),
    bounceaccountpass character varying(100),
    bounceaccountencryption character varying(3),
    bounceaccountuser character varying(200),
    showwelcome character varying(1) DEFAULT 'Y'::character varying,
    showprogress character varying(1) DEFAULT 'Y'::character varying,
    questionindex integer DEFAULT 0 NOT NULL,
    navigationdelay integer DEFAULT 0 NOT NULL,
    nokeyboard character varying(1) DEFAULT 'N'::character varying,
    alloweditaftercompletion character varying(1) DEFAULT 'N'::character varying,
    googleanalyticsstyle character varying(1),
    googleanalyticsapikey character varying(25),
    gsid integer
);


ALTER TABLE lime_surveys OWNER TO limesurvey;

--
-- Name: lime_surveys_groups; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_surveys_groups (
    gsid integer NOT NULL,
    name character varying(45) NOT NULL,
    title character varying(100) DEFAULT NULL::character varying,
    description text,
    "order" integer NOT NULL,
    owner_uid integer,
    parent_id integer,
    created timestamp without time zone,
    modified timestamp without time zone,
    created_by integer NOT NULL,
    template character varying(128) DEFAULT 'default'::character varying
);


ALTER TABLE lime_surveys_groups OWNER TO limesurvey;

--
-- Name: lime_surveys_groups_gsid_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_surveys_groups_gsid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_surveys_groups_gsid_seq OWNER TO limesurvey;

--
-- Name: lime_surveys_groups_gsid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_surveys_groups_gsid_seq OWNED BY lime_surveys_groups.gsid;


--
-- Name: lime_surveys_languagesettings; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_surveys_languagesettings (
    surveyls_survey_id integer NOT NULL,
    surveyls_language character varying(45) DEFAULT 'en'::character varying NOT NULL,
    surveyls_title character varying(200) NOT NULL,
    surveyls_description text,
    surveyls_welcometext text,
    surveyls_endtext text,
    surveyls_url text,
    surveyls_urldescription character varying(255),
    surveyls_email_invite_subj character varying(255),
    surveyls_email_invite text,
    surveyls_email_remind_subj character varying(255),
    surveyls_email_remind text,
    surveyls_email_register_subj character varying(255),
    surveyls_email_register text,
    surveyls_email_confirm_subj character varying(255),
    surveyls_email_confirm text,
    surveyls_dateformat integer DEFAULT 1 NOT NULL,
    surveyls_attributecaptions text,
    email_admin_notification_subj character varying(255),
    email_admin_notification text,
    email_admin_responses_subj character varying(255),
    email_admin_responses text,
    surveyls_numberformat integer DEFAULT 0 NOT NULL,
    attachments text
);


ALTER TABLE lime_surveys_languagesettings OWNER TO limesurvey;

--
-- Name: lime_template_configuration; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_template_configuration (
    id integer NOT NULL,
    template_name character varying(150) NOT NULL,
    sid integer,
    gsid integer,
    uid integer,
    files_css text,
    files_js text,
    files_print_css text,
    options text,
    cssframework_name character varying(45) DEFAULT NULL::character varying,
    cssframework_css text,
    cssframework_js text,
    packages_to_load text,
    packages_ltr text,
    packages_rtl text
);


ALTER TABLE lime_template_configuration OWNER TO limesurvey;

--
-- Name: lime_templates; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_templates (
    name character varying(150) NOT NULL,
    folder character varying(45) DEFAULT NULL::character varying,
    title character varying(100) NOT NULL,
    creation_date timestamp without time zone DEFAULT now() NOT NULL,
    author character varying(150) DEFAULT NULL::character varying,
    author_email character varying(255) DEFAULT NULL::character varying,
    author_url character varying(255) DEFAULT NULL::character varying,
    copyright text,
    license text,
    version character varying(45) DEFAULT NULL::character varying,
    api_version character varying(45) NOT NULL,
    view_folder character varying(45) NOT NULL,
    files_folder character varying(45) NOT NULL,
    description text,
    last_update timestamp without time zone,
    owner_id integer,
    extends_template_name character varying(150) DEFAULT NULL::character varying
);


ALTER TABLE lime_templates OWNER TO limesurvey;

--
-- Name: lime_user_groups; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_user_groups (
    ugid integer NOT NULL,
    name character varying(20) NOT NULL,
    description text NOT NULL,
    owner_id integer NOT NULL
);


ALTER TABLE lime_user_groups OWNER TO limesurvey;

--
-- Name: lime_user_groups_ugid_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_user_groups_ugid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_user_groups_ugid_seq OWNER TO limesurvey;

--
-- Name: lime_user_groups_ugid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_user_groups_ugid_seq OWNED BY lime_user_groups.ugid;


--
-- Name: lime_user_in_groups; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_user_in_groups (
    ugid integer NOT NULL,
    uid integer NOT NULL
);


ALTER TABLE lime_user_in_groups OWNER TO limesurvey;

--
-- Name: lime_users; Type: TABLE; Schema: public; Owner: limesurvey
--

CREATE TABLE lime_users (
    uid integer NOT NULL,
    users_name character varying(64) DEFAULT ''::character varying NOT NULL,
    password bytea NOT NULL,
    full_name character varying(50) NOT NULL,
    parent_id integer NOT NULL,
    lang character varying(20),
    email character varying(254),
    htmleditormode character varying(7) DEFAULT 'default'::character varying,
    templateeditormode character varying(7) DEFAULT 'default'::character varying NOT NULL,
    questionselectormode character varying(7) DEFAULT 'default'::character varying NOT NULL,
    one_time_pw bytea,
    dateformat integer DEFAULT 1 NOT NULL,
    created timestamp without time zone,
    modified timestamp without time zone
);


ALTER TABLE lime_users OWNER TO limesurvey;

--
-- Name: lime_users_uid_seq; Type: SEQUENCE; Schema: public; Owner: limesurvey
--

CREATE SEQUENCE lime_users_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE lime_users_uid_seq OWNER TO limesurvey;

--
-- Name: lime_users_uid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: limesurvey
--

ALTER SEQUENCE lime_users_uid_seq OWNED BY lime_users.uid;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_assessments ALTER COLUMN id SET DEFAULT nextval('lime_assessments_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_boxes ALTER COLUMN id SET DEFAULT nextval('lime_boxes_id_seq'::regclass);


--
-- Name: cid; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_conditions ALTER COLUMN cid SET DEFAULT nextval('lime_conditions_cid_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_expression_errors ALTER COLUMN id SET DEFAULT nextval('lime_expression_errors_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_failed_login_attempts ALTER COLUMN id SET DEFAULT nextval('lime_failed_login_attempts_id_seq'::regclass);


--
-- Name: gid; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_groups ALTER COLUMN gid SET DEFAULT nextval('lime_groups_gid_seq'::regclass);


--
-- Name: lid; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_labelsets ALTER COLUMN lid SET DEFAULT nextval('lime_labelsets_lid_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_notifications ALTER COLUMN id SET DEFAULT nextval('lime_notifications_id_seq'::regclass);


--
-- Name: attribute_id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_participant_attribute_names ALTER COLUMN attribute_id SET DEFAULT nextval('lime_participant_attribute_names_attribute_id_seq'::regclass);


--
-- Name: value_id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_participant_attribute_values ALTER COLUMN value_id SET DEFAULT nextval('lime_participant_attribute_values_value_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_permissions ALTER COLUMN id SET DEFAULT nextval('lime_permissions_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_plugin_settings ALTER COLUMN id SET DEFAULT nextval('lime_plugin_settings_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_plugins ALTER COLUMN id SET DEFAULT nextval('lime_plugins_id_seq'::regclass);


--
-- Name: qaid; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_question_attributes ALTER COLUMN qaid SET DEFAULT nextval('lime_question_attributes_qaid_seq'::regclass);


--
-- Name: qid; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_questions ALTER COLUMN qid SET DEFAULT nextval('lime_questions_qid_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_quota ALTER COLUMN id SET DEFAULT nextval('lime_quota_id_seq'::regclass);


--
-- Name: quotals_id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_quota_languagesettings ALTER COLUMN quotals_id SET DEFAULT nextval('lime_quota_languagesettings_quotals_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_quota_members ALTER COLUMN id SET DEFAULT nextval('lime_quota_members_id_seq'::regclass);


--
-- Name: scid; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_saved_control ALTER COLUMN scid SET DEFAULT nextval('lime_saved_control_scid_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_survey_url_parameters ALTER COLUMN id SET DEFAULT nextval('lime_survey_url_parameters_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_surveymenu ALTER COLUMN id SET DEFAULT nextval('lime_surveymenu_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_surveymenu_entries ALTER COLUMN id SET DEFAULT nextval('lime_surveymenu_entries_id_seq'::regclass);


--
-- Name: gsid; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_surveys_groups ALTER COLUMN gsid SET DEFAULT nextval('lime_surveys_groups_gsid_seq'::regclass);


--
-- Name: ugid; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_user_groups ALTER COLUMN ugid SET DEFAULT nextval('lime_user_groups_ugid_seq'::regclass);


--
-- Name: uid; Type: DEFAULT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_users ALTER COLUMN uid SET DEFAULT nextval('lime_users_uid_seq'::regclass);


--
-- Name: lime_answers_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_answers
    ADD CONSTRAINT lime_answers_pkey PRIMARY KEY (qid, code, language, scale_id);


--
-- Name: lime_assessments_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_assessments
    ADD CONSTRAINT lime_assessments_pkey PRIMARY KEY (id, language);


--
-- Name: lime_boxes_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_boxes
    ADD CONSTRAINT lime_boxes_pkey PRIMARY KEY (id);


--
-- Name: lime_conditions_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_conditions
    ADD CONSTRAINT lime_conditions_pkey PRIMARY KEY (cid);


--
-- Name: lime_defaultvalues_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_defaultvalues
    ADD CONSTRAINT lime_defaultvalues_pkey PRIMARY KEY (qid, specialtype, language, scale_id, sqid);


--
-- Name: lime_expression_errors_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_expression_errors
    ADD CONSTRAINT lime_expression_errors_pkey PRIMARY KEY (id);


--
-- Name: lime_failed_login_attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_failed_login_attempts
    ADD CONSTRAINT lime_failed_login_attempts_pkey PRIMARY KEY (id);


--
-- Name: lime_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_groups
    ADD CONSTRAINT lime_groups_pkey PRIMARY KEY (gid, language);


--
-- Name: lime_labels_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_labels
    ADD CONSTRAINT lime_labels_pkey PRIMARY KEY (lid, sortorder, language);


--
-- Name: lime_labelsets_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_labelsets
    ADD CONSTRAINT lime_labelsets_pkey PRIMARY KEY (lid);


--
-- Name: lime_notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_notifications
    ADD CONSTRAINT lime_notifications_pkey PRIMARY KEY (id);


--
-- Name: lime_participant_attribut_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_participant_attribute
    ADD CONSTRAINT lime_participant_attribut_pkey PRIMARY KEY (participant_id, attribute_id);


--
-- Name: lime_participant_attribute_names_lang_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_participant_attribute_names_lang
    ADD CONSTRAINT lime_participant_attribute_names_lang_pkey PRIMARY KEY (attribute_id, lang);


--
-- Name: lime_participant_attribute_names_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_participant_attribute_names
    ADD CONSTRAINT lime_participant_attribute_names_pkey PRIMARY KEY (attribute_id, attribute_type);


--
-- Name: lime_participant_attribute_values_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_participant_attribute_values
    ADD CONSTRAINT lime_participant_attribute_values_pkey PRIMARY KEY (value_id);


--
-- Name: lime_participant_shares_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_participant_shares
    ADD CONSTRAINT lime_participant_shares_pkey PRIMARY KEY (participant_id, share_uid);


--
-- Name: lime_participants_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_participants
    ADD CONSTRAINT lime_participants_pkey PRIMARY KEY (participant_id);


--
-- Name: lime_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_permissions
    ADD CONSTRAINT lime_permissions_pkey PRIMARY KEY (id);


--
-- Name: lime_plugin_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_plugin_settings
    ADD CONSTRAINT lime_plugin_settings_pkey PRIMARY KEY (id);


--
-- Name: lime_plugins_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_plugins
    ADD CONSTRAINT lime_plugins_pkey PRIMARY KEY (id);


--
-- Name: lime_question_attributes_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_question_attributes
    ADD CONSTRAINT lime_question_attributes_pkey PRIMARY KEY (qaid);


--
-- Name: lime_questions_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_questions
    ADD CONSTRAINT lime_questions_pkey PRIMARY KEY (qid, language);


--
-- Name: lime_quota_languagesettings_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_quota_languagesettings
    ADD CONSTRAINT lime_quota_languagesettings_pkey PRIMARY KEY (quotals_id);


--
-- Name: lime_quota_members_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_quota_members
    ADD CONSTRAINT lime_quota_members_pkey PRIMARY KEY (id);


--
-- Name: lime_quota_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_quota
    ADD CONSTRAINT lime_quota_pkey PRIMARY KEY (id);


--
-- Name: lime_saved_control_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_saved_control
    ADD CONSTRAINT lime_saved_control_pkey PRIMARY KEY (scid);


--
-- Name: lime_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_sessions
    ADD CONSTRAINT lime_sessions_pkey PRIMARY KEY (id);


--
-- Name: lime_settings_global_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_settings_global
    ADD CONSTRAINT lime_settings_global_pkey PRIMARY KEY (stg_name);


--
-- Name: lime_survey_links_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_survey_links
    ADD CONSTRAINT lime_survey_links_pkey PRIMARY KEY (participant_id, token_id, survey_id);


--
-- Name: lime_survey_url_parameters_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_survey_url_parameters
    ADD CONSTRAINT lime_survey_url_parameters_pkey PRIMARY KEY (id);


--
-- Name: lime_surveymenu_entries_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_surveymenu_entries
    ADD CONSTRAINT lime_surveymenu_entries_pkey PRIMARY KEY (id);


--
-- Name: lime_surveymenu_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_surveymenu
    ADD CONSTRAINT lime_surveymenu_pkey PRIMARY KEY (id);


--
-- Name: lime_surveys_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_surveys_groups
    ADD CONSTRAINT lime_surveys_groups_pkey PRIMARY KEY (gsid);


--
-- Name: lime_surveys_languagesettings_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_surveys_languagesettings
    ADD CONSTRAINT lime_surveys_languagesettings_pkey PRIMARY KEY (surveyls_survey_id, surveyls_language);


--
-- Name: lime_surveys_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_surveys
    ADD CONSTRAINT lime_surveys_pkey PRIMARY KEY (sid);


--
-- Name: lime_template_configuration_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_template_configuration
    ADD CONSTRAINT lime_template_configuration_pkey PRIMARY KEY (id);


--
-- Name: lime_templates_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_templates
    ADD CONSTRAINT lime_templates_pkey PRIMARY KEY (name);


--
-- Name: lime_user_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_user_groups
    ADD CONSTRAINT lime_user_groups_pkey PRIMARY KEY (ugid);


--
-- Name: lime_user_in_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_user_in_groups
    ADD CONSTRAINT lime_user_in_groups_pkey PRIMARY KEY (ugid, uid);


--
-- Name: lime_user_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_settings_user
    ADD CONSTRAINT lime_user_settings_pkey PRIMARY KEY (uid, entity, entity_id, stg_name);


--
-- Name: lime_users_pkey; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_users
    ADD CONSTRAINT lime_users_pkey PRIMARY KEY (uid);


--
-- Name: lime_users_users_name_key; Type: CONSTRAINT; Schema: public; Owner: limesurvey
--

ALTER TABLE ONLY lime_users
    ADD CONSTRAINT lime_users_users_name_key UNIQUE (users_name);


--
-- Name: answers_idx2; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX answers_idx2 ON lime_answers USING btree (sortorder);


--
-- Name: assessments_idx2; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX assessments_idx2 ON lime_assessments USING btree (sid);


--
-- Name: assessments_idx3; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX assessments_idx3 ON lime_assessments USING btree (gid);


--
-- Name: conditions_idx2; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX conditions_idx2 ON lime_conditions USING btree (qid);


--
-- Name: conditions_idx3; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX conditions_idx3 ON lime_conditions USING btree (cqid);


--
-- Name: groups_idx2; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX groups_idx2 ON lime_groups USING btree (sid);


--
-- Name: idx_menu_id; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX idx_menu_id ON lime_surveymenu_entries USING btree (menu_id);


--
-- Name: idx_menu_title; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX idx_menu_title ON lime_surveymenu_entries USING btree (menu_title);


--
-- Name: idx_ordering; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX idx_ordering ON lime_surveymenu USING btree (ordering);


--
-- Name: idx_ordering_entries; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX idx_ordering_entries ON lime_surveymenu_entries USING btree (ordering);


--
-- Name: idx_title; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX idx_title ON lime_surveymenu USING btree (title);


--
-- Name: idx_title_entries; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX idx_title_entries ON lime_surveymenu_entries USING btree (title);


--
-- Name: labels_code_idx; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX labels_code_idx ON lime_labels USING btree (code);


--
-- Name: lime_index; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX lime_index ON lime_notifications USING btree (entity, entity_id, status);


--
-- Name: lime_quota_members_ixcode_idx; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX lime_quota_members_ixcode_idx ON lime_quota_members USING btree (sid, qid, quota_id, code);


--
-- Name: notif_hash_index; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX notif_hash_index ON lime_notifications USING btree (hash);


--
-- Name: parent_qid_idx; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX parent_qid_idx ON lime_questions USING btree (parent_qid);


--
-- Name: permissions_idx2; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE UNIQUE INDEX permissions_idx2 ON lime_permissions USING btree (entity_id, entity, uid, permission);


--
-- Name: question_attributes_idx2; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX question_attributes_idx2 ON lime_question_attributes USING btree (qid);


--
-- Name: question_attributes_idx3; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX question_attributes_idx3 ON lime_question_attributes USING btree (attribute);


--
-- Name: questions_idx2; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX questions_idx2 ON lime_questions USING btree (sid);


--
-- Name: questions_idx3; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX questions_idx3 ON lime_questions USING btree (gid);


--
-- Name: questions_idx4; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX questions_idx4 ON lime_questions USING btree (type);


--
-- Name: quota_idx2; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX quota_idx2 ON lime_quota USING btree (sid);


--
-- Name: saved_control_idx2; Type: INDEX; Schema: public; Owner: limesurvey
--

CREATE INDEX saved_control_idx2 ON lime_saved_control USING btree (sid);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

