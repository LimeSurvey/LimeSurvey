<?PHP
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id: upgrade-odbc_mssql.php 3631 2007-11-12 18:13:06Z c_schmitz $
 */

// There will be a file for each database (accordingly named to the dbADO scheme)
// where based on the current database version the database is upgraded
// For this there will be a settings table which holds the last time the database was upgraded

function db_upgrade($oldversion) {
    global $modifyoutput, $databasename, $databasetabletype;
    $CI =& get_instance();
    $clang = $CI->limesurvey_lang;


    if ($oldversion < 127) {
        modify_database("","create index answers_idx2 on prefix_answers (sortorder)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index assessments_idx2 on prefix_assessments (sid)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index assessments_idx on prefix_assessments (gid)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index conditions_idx2 on prefix_conditions (qid)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index conditions_idx3 on prefix_conditions (cqid)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index groups_idx2 on prefix_groups (sid)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index question_attributes_idx2 on prefix_question_attributes (qid)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index questions_idx2 on prefix_questions (sid)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index questions_idx3 on prefix_questions (gid)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index questions_idx4 on prefix_questions (type)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index quota_idx2 on prefix_quota (sid)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index saved_control_idx2 on prefix_saved_control (sid)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index user_in_groups_idx1 on prefix_user_in_groups (ugid, uid)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","update prefix_settings_global set stg_value='127' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 128) {
        //128
        upgrade_token_tables128();
        modify_database("","update prefix_settings_global set stg_value='128' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 129) {
        //129
        modify_database("","ALTER TABLE prefix_surveys ADD startdate date"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE prefix_surveys ADD usestartdate char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","update prefix_settings_global set stg_value='129' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 130)
    {
        modify_database("","ALTER TABLE prefix_conditions ADD scenario integer NOT NULL default '1'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE prefix_conditions SET scenario=1 where (scenario is null) or scenario=0"); echo $modifyoutput; flush();ob_flush();
        modify_database("","update prefix_settings_global set stg_value='130' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 131)
    {
        modify_database("","ALTER TABLE prefix_surveys ADD publicstatistics char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","update prefix_settings_global set stg_value='131' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 132)
    {
        modify_database("","ALTER TABLE prefix_surveys ADD publicgraphs char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","update prefix_settings_global set stg_value='132' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 133)
    {
        modify_database("","ALTER TABLE prefix_users ADD one_time_pw bytea"); echo $modifyoutput; flush();ob_flush();

        // Add new assessment setting
        modify_database("","ALTER TABLE prefix_surveys ADD assessments char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        // add new assessment value fields to answers & labels
        modify_database("","ALTER TABLE prefix_answers ADD assessment_value integer NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE prefix_labels ADD assessment_value integer NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        // copy any valid codes from code field to assessment field
        modify_database("","update [prefix_answers set assessment_value=CAST(code as integer)");// no output here is intended
        modify_database("","update prefix_labels set assessment_value=CAST(code as integer)");// no output here is intended
        // activate assessment where assesment rules exist
        modify_database("","update prefix_surveys set assessments='Y' where sid in (SELECT sid FROM prefix_assessments group by sid)"); echo $modifyoutput; flush();ob_flush();
        // add language field to assessment table
        modify_database("","ALTER TABLE prefix_assessments ADD language character varying(20) NOT NULL default 'en'"); echo $modifyoutput; flush();ob_flush();
        // update language field with default language of that particular survey
        modify_database("","update prefix_assessments set language=(select language from prefix_surveys where sid=prefix_assessments.sid)"); echo $modifyoutput; flush();ob_flush();
        // copy assessment link to message since from now on we will have HTML assignment messages
        modify_database("","update prefix_assessments set message=cast(message as character) ||'<br /><a href=\"'||link||'\">'||link||'</a>'"); echo $modifyoutput; flush();ob_flush();
        // drop the old link field
        modify_database("","ALTER TABLE prefix_assessments DROP COLUMN link"); echo $modifyoutput; flush();ob_flush();
        // change the primary index to include language
        modify_database("","ALTER TABLE prefix_assessments DROP CONSTRAINT prefix_assessments_pkey"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE prefix_assessments ADD CONSTRAINT prefix_assessments_pkey PRIMARY KEY (id,language)"); echo $modifyoutput; flush();ob_flush();
        // and fix missing translations for assessments
        upgrade_survey_tables133();

        // Add new fields to survey language settings
        modify_database("","ALTER TABLE prefix_surveys_languagesettings ADD surveyls_url character varying(255)"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE prefix_surveys_languagesettings ADD surveyls_endtext text"); echo $modifyoutput; flush();ob_flush();

        // copy old URL fields ot language specific entries
        modify_database("","update prefix_surveys_languagesettings set surveyls_url=(select url from prefix_surveys where sid=prefix_surveys_languagesettings.surveyls_survey_id)"); echo $modifyoutput; flush();ob_flush();
        // drop old URL field
        modify_database("","ALTER TABLE prefix_surveys DROP COLUMN url"); echo $modifyoutput; flush();ob_flush();

        modify_database("","update prefix_settings_global set stg_value='133' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 134)
    {
        modify_database("","ALTER TABLE prefix_surveys ADD usetokens char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE prefix_surveys ADD attributedescriptions TEXT;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE prefix_surveys DROP COLUMN attribute1"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE prefix_surveys DROP COLUMN attribute2"); echo $modifyoutput; flush();ob_flush();
        upgrade_token_tables134();
        modify_database("","update prefix_settings_global set stg_value='134' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();

    }
    if ($oldversion < 135)
    {
        modify_database("","ALTER TABLE prefix_question_attributes ALTER COLUMN value TYPE text"); echo $modifyoutput; flush();ob_flush();
        modify_database("","update prefix_settings_global set stg_value='135' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 136)
    {
        modify_database("","ALTER TABLE prefix_quota ADD autoload_url integer NOT NULL DEFAULT 0"); echo $modifyoutput; flush();ob_flush();
        modify_database("","CREATE TABLE prefix_quota_languagesettings (
                            quotals_id serial NOT NULL,
                            quotals_quota_id integer NOT NULL DEFAULT 0,
                            quotals_language character varying(45) NOT NULL DEFAULT 'en'::character varying,
                            quotals_name character varying(200),
                            quotals_message text NOT NULL,
                            quotals_url character varying(255),
                            quotals_urldescrip character varying(255));"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE ONLY prefix_quota_languagesettings
  	   					   ADD CONSTRAINT prefix_quota_languagesettings_pkey PRIMARY KEY (quotals_id);"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE ONLY prefix_users ADD CONSTRAINT prefix_users_pkey PRIMARY KEY (uid)"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE ONLY prefix_users ADD CONSTRAINT prefix_user_name_key UNIQUE (users_name)"); echo $modifyoutput; flush();ob_flush();
        modify_database("","update prefix_settings_global set stg_value='136' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();

    }

    if ($oldversion < 137) //New date format specs
    {
        modify_database("", "ALTER TABLE prefix_surveys_languagesettings ADD surveyls_dateformat integer NOT NULL default 1"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_users ADD \"dateformat\" integer NOT NULL default 1"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "update prefix_surveys set startdate=null where usestartdate='N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "update prefix_surveys set expires=null where useexpiry='N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys DROP COLUMN usestartdate"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys DROP COLUMN useexpiry"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "update prefix_settings_global set stg_value='137' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 138) //Modify quota field
    {
        modify_database("", "ALTER TABLE prefix_quota_members ALTER COLUMN code TYPE character varying(11)"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE prefix_settings_global SET stg_value='138' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 139) //Modify quota field
    {
        upgrade_survey_tables139();
        modify_database("", "UPDATE prefix_settings_global SET stg_value='139' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 140) //Modify surveys table
    {
        modify_database("", "ALTER TABLE prefix_surveys ADD \"emailresponseto\" TEXT"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE prefix_settings_global SET stg_value='140' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 141) //Modify surveys table
    {
        modify_database("", "ALTER TABLE prefix_surveys ADD \"tokenlength\" smallint NOT NULL DEFAULT '15'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE prefix_settings_global SET stg_value='141' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 142) //Modify surveys table
    {
        upgrade_question_attributes142();
        modify_database("", "ALTER TABLE prefix_surveys ALTER COLUMN \"startdate\" TYPE timestamp"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ALTER COLUMN \"expires\" TYPE timestamp"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE prefix_question_attributes SET value='0' WHERE value='false'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE prefix_question_attributes SET value='1' WHERE value='true'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE prefix_settings_global SET stg_value='142' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 143) //Modify surveys table
    {
        modify_database("", "ALTER TABLE prefix_questions ADD parent_qid integer NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_answers ADD scale_id smallint NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_questions ADD scale_id smallint NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_questions ADD same_default smallint NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_answers DROP CONSTRAINT prefix_answers_pkey"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_answers ADD CONSTRAINT prefix_answers_pkey PRIMARY KEY (qid,code,language,scale_id)"); echo $modifyoutput; flush();ob_flush();

        modify_database("", "CREATE TABLE prefix_defaultvalues (
                              qid integer NOT NULL default '0',
                              scale_id integer NOT NULL default '0',
                              sqid integer NOT NULL default '0',
                              language character varying(20) NOT NULL,
                              specialtype character varying(20) NOT NULL default '',
                              defaultvalue text)"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE prefix_defaultvalues ADD CONSTRAINT prefix_defaultvalues_pkey PRIMARY KEY (qid , scale_id, language, specialtype, sqid)"); echo $modifyoutput; flush();ob_flush();

        // -Move all 'answers' that are subquestions to the questions table
        // -Move all 'labels' that are answers to the answers table
        // -Transscribe the default values where applicable
        // -Move default values from answers to questions
        upgrade_tables143();

        modify_database("", "ALTER TABLE prefix_answers DROP COLUMN default_value"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_questions DROP COLUMN lid"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_questions DROP COLUMN lid1"); echo $modifyoutput; flush();ob_flush();
        // add field for timings and table for extended conditions
        modify_database("", "ALTER TABLE prefix_surveys ADD savetimings char(1) default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "CREATE TABLE prefix_sessions(
                             sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
                             expiry TIMESTAMP NOT NULL ,
                             expireref VARCHAR( 250 ) DEFAULT '',
                             created TIMESTAMP NOT NULL ,
                             modified TIMESTAMP NOT NULL ,
                             sessdata TEXT DEFAULT '',
                             PRIMARY KEY ( sesskey )
                             );"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "create INDEX sess2_expiry on prefix_sessions( expiry );"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "create INDEX sess2_expireref on prefix_sessions ( expireref );"); echo $modifyoutput; flush();ob_flush();

        modify_database("", "UPDATE prefix_settings_global SET stg_value='143' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();



    }
    if ($oldversion < 145)
    {
        modify_database("", "ALTER TABLE prefix_surveys ADD savetimings CHAR(1) NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD showXquestions CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD showgroupinfo CHAR(1) NULL default 'B'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD shownoanswer CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD showqnumcode CHAR(1) NULL default 'X'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD bouncetime bigint NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD bounceprocessing character varying(1) NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD bounceaccounttype character varying(4) NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD bounceaccounthost character varying(200) NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD bounceaccountpass character varying(100) NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD bounceaccountencryption character varying(3) NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD bounceaccountuser character varying(200) NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD showwelcome CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD showprogress CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD allowjumps CHAR(1) NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD navigationdelay smallint NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD nokeyboard char(1) default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_surveys ADD alloweditaftercompletion char(1) default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "CREATE TABLE prefix_survey_permissions (
                            sid integer DEFAULT 0 NOT NULL,
                            uid integer DEFAULT 0 NOT NULL,
                            permission character varying(20) NOT NULL,
                            create_p integer DEFAULT 0 NOT NULL,
                            read_p integer DEFAULT 0 NOT NULL,
                            update_p integer DEFAULT 0 NOT NULL,
                            delete_p integer DEFAULT 0 NOT NULL,
                            import_p integer DEFAULT 0 NOT NULL,
                            export_p integer DEFAULT 0 NOT NULL
                        );"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE ONLY prefix_survey_permissions ADD CONSTRAINT prefix_survey_permissions_pkey PRIMARY KEY (sid,uid,permission);"); echo $modifyoutput; flush();ob_flush();
		upgrade_surveypermissions_table145();

        // drop the old survey rights table
        modify_database("", "DROP TABLE prefix_surveys_rights"); echo $modifyoutput; flush();ob_flush();

        // Add new fields for email templates
        modify_database("", "ALTER TABLE prefix_surveys_languagesettings ADD email_admin_notification_subj character varying(255)");
        modify_database("", "ALTER TABLE prefix_surveys_languagesettings ADD email_admin_responses_subj character varying(255)");
        modify_database("", "ALTER TABLE prefix_surveys_languagesettings ADD email_admin_notification text");
        modify_database("", "ALTER TABLE prefix_surveys_languagesettings ADD email_admin_responses text");

        //Add index to questions table to speed up subquestions
        modify_database("", "create INDEX parent_qid_idx on prefix_questions( parent_qid );"); echo $modifyoutput; flush();ob_flush();

        modify_database("", "ALTER TABLE prefix_surveys ADD emailnotificationto text DEFAULT NULL"); echo $modifyoutput; flush();ob_flush();
        upgrade_survey_table145();
        modify_database("", "ALTER TABLE prefix_surveys DROP COLUMN notification"); echo $modifyoutput; flush();ob_flush();

        modify_database("","ALTER TABLE prefix_conditions ALTER COLUMN method TYPE CHAR(5)"); echo $modifyoutput; flush();ob_flush();

        modify_database("","UPDATE prefix_surveys set private='N' where private is NULL;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE prefix_surveys RENAME COLUMN private TO anonymized;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE prefix_surveys ALTER COLUMN anonymized TYPE char(1);"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE prefix_surveys ALTER COLUMN anonymized SET DEFAULT 'N';"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE prefix_surveys ALTER COLUMN anonymized SET NOT NULL ;"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "CREATE TABLE prefix_failed_login_attempts (
                                  id serial PRIMARY KEY NOT NULL,
                                  ip character varying(37) NOT NULL,
                                  last_attempt character varying(20) NOT NULL,
                                  number_attempts integer NOT NULL
                                );"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE  prefix_surveys_languagesettings ADD surveyls_numberformat integer default 0 NOT NULL"); echo $modifyoutput; flush();ob_flush();
        upgrade_token_tables145();
        modify_database("", "UPDATE prefix_settings_global SET stg_value='145' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 146) //Modify surveys table
    {
        upgrade_timing_tables146();
        modify_database("", "UPDATE prefix_settings_global SET stg_value='146' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 147)
    {
        modify_database("", "ALTER TABLE prefix_users ADD templateeditormode character varying(7) NOT NULL DEFAULT 'default'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE prefix_users ADD questionselectormode character varying(7) NOT NULL DEFAULT 'default'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE prefix_settings_global SET stg_value='147' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 148)
    {
        modify_database("","CREATE TABLE prefix_participants (
        participant_id VARCHAR( 50 ) NOT NULL,
        firstname VARCHAR( 40 ) NOT NULL,
        lastname VARCHAR( 40 ) NOT NULL,
        email VARCHAR( 80 ) NOT NULL,
        language VARCHAR( 2 ) NOT NULL,
        blacklisted VARCHAR( 1 ) NOT NULL,
        owner_uid integer NOT NULL,
        PRIMARY KEY (participant_id)
        );"); echo $modifyoutput; flush();ob_flush();

        modify_database("","CREATE TABLE prefix_participant_attribute (
        participant_id VARCHAR( 50 ) NOT NULL,
        attribute_id integer NOT NULL,
        value integer NOT NULL,
        PRIMARY KEY (participant_id,attribute_id)
        );"); echo $modifyoutput; flush();ob_flush();

        modify_database("","CREATE TABLE prefix_participant_attribute_names (
        attribute_id integer NOT NULL AUTO_INCREMENT,
        attribute_type VARCHAR( 30 ) NOT NULL,
        visible CHAR( 5 ) NOT NULL,
        PRIMARY KEY (attribute_type,attribute_id)
        );"); echo $modifyoutput; flush();ob_flush();

        modify_database("","CREATE TABLE prefix_participant_attribute_names_lang (
        id integer NOT NULL AUTO_INCREMENT,
        attribute_id integer NOT NULL,
        attribute_name VARCHAR( 30 ) NOT NULL,
        lang CHAR( 20 ) NOT NULL,
        PRIMARY KEY (lang,attribute_id)
        );"); echo $modifyoutput; flush();ob_flush();

        modify_database("","CREATE TABLE prefix_participant_attribute_values (
        attribute_id integer NOT NULL,
        value_id integer NOT NULL AUTO_INCREMENT,
        value VARCHAR( 20 ) NOT NULL,
        PRIMARY KEY (value_id)
        );"); echo $modifyoutput; flush();ob_flush();

        modify_database("","CREATE TABLE prefix_participant_shares (
        participant_id VARCHAR( 50 ) NOT NULL,
        shared_uid integer NOT NULL,
        date_added date NOT NULL,
        can_edit VARCHAR( 5 ) NOT NULL,
        PRIMARY KEY (lang,attribute_id)
        );"); echo $modifyoutput; flush();ob_flush();

        modify_database("","CREATE TABLE prefix_survey_links (
        participant_id VARCHAR( 50 ) NOT NULL,
        token_id integer NOT NULL,
        survey_id integer NOT NULL,
        date_created date NOT NULL,
        PRIMARY KEY (participant_id,token_id,survey_id)
        );"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE prefix_user ADD participant_panel integer NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        // add language field to question_attributes table
        modify_database("","ALTER TABLE prefix_question_attributes ADD language character varying(20)"); echo $modifyoutput; flush();ob_flush();
        upgrade_question_attributes148();
        fixSubquestions();
        modify_database("", "UPDATE prefix_settings_global SET stg_value='148' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();

    }

    echo '<br /><br />'.sprintf($clang->gT('Database update finished (%s)'),date('Y-m-d H:i:s')).'<br />';
    return true;
}

function upgrade_token_tables128()
{
    global $modifyoutput,$dbprefix;
    $surveyidquery = db_select_tables_like($dbprefix."tokens%");
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    else
    {
        while ( $sv = $surveyidresult->FetchRow() )
        {
            modify_database("","ALTER TABLE ".$sv['0']." ADD remindersent character varying(17) DEFAULT 'N'"); echo $modifyoutput; flush();ob_flush();
            modify_database("","ALTER TABLE ".$sv['0']." ADD remindercount INTEGER DEFAULT 0"); echo $modifyoutput; flush();ob_flush();
        }
    }
}

function upgrade_survey_tables133()
{
    global $modifyoutput;

    $surveyidquery = "SELECT sid, additional_languages FROM ".db_table_name('surveys');
    $surveyidresult = db_execute_num($surveyidquery);
    while ( $sv = $surveyidresult->FetchRow() )
    {
        FixLanguageConsistency($sv['0'],$sv['1']);
    }
}

function upgrade_token_tables134()
{
    global $modifyoutput,$dbprefix;
    $surveyidquery = db_select_tables_like($dbprefix."tokens%");
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    else
    {
        while ( $sv = $surveyidresult->FetchRow() )
        {
            modify_database("","ALTER TABLE ".$sv[0]." ADD validfrom timestamp"); echo $modifyoutput; flush();ob_flush();
            modify_database("","ALTER TABLE ".$sv[0]." ADD validuntil timestamp"); echo $modifyoutput; flush();ob_flush();
        }
    }
}
// Add the usesleft field to all existing token tables
function upgrade_token_tables145()
{
    global $modifyoutput,$dbprefix;
    $surveyidquery = db_select_tables_like($dbprefix."tokens%");
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    else
    {
        while ( $sv = $surveyidresult->FetchRow() )
        {
            modify_database("","ALTER TABLE ".$sv[0]." ADD usesleft integer DEFAULT 1 NOT NULL"); echo $modifyoutput; flush();ob_flush();
            modify_database("","UPDATE ".$sv[0]." SET usesleft=0 WHERE completed<>'N'"); echo $modifyoutput; flush();ob_flush();
        }
    }
}

function upgrade_survey_tables139()
{
    global $modifyoutput,$dbprefix;
    $surveyidquery = db_select_tables_like($dbprefix."survey\_%");
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    else
    {
        while ( $sv = $surveyidresult->FetchRow() )
        {
            modify_database("","ALTER TABLE ".$sv[0]." ADD lastpage integer"); echo $modifyoutput; flush();ob_flush();
        }
    }
}

function upgrade_question_attributes142()
{
    global $modifyoutput,$dbprefix, $connect;
    $attributequery="Select qid from {$dbprefix}question_attributes where attribute='exclude_all_other' group by qid having count(qid)>1 ";
    $questionids = db_select_column($attributequery);
    foreach ($questionids as $questionid)
    {
        //Select all affected question attributes
        $attributevalues=db_select_column("SELECT value from {$dbprefix}question_attributes where attribute='exclude_all_other' and qid=".$questionid);
        modify_database("","delete from {$dbprefix}question_attributes where attribute='exclude_all_other' and qid=".$questionid); echo $modifyoutput; flush();ob_flush();
        $record['value']=implode(';',$attributevalues);
        $record['attribute']='exclude_all_other';
        $record['qid']=$questionid;
        $connect->AutoExecute("{$dbprefix}question_attributes", $record, 'INSERT');
    }
}

function upgrade_tables143()
{
    global $modifyoutput,$dbprefix, $connect;


    $aQIDReplacements=array();
    $answerquery = "select a.*, q.sid, q.gid from {$dbprefix}answers a,{$dbprefix}questions q where a.qid=q.qid and q.type in ('L','O','!') and a.default_value='Y'";
    $answerresult = db_execute_assoc($answerquery);
    if (!$answerresult) {return "Database Error";}
    else
    {
        while ( $row = $answerresult->FetchRow() )
        {
            modify_database("","INSERT INTO {$dbprefix}defaultvalues (qid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},0,".db_quoteall($row['language']).",'',".db_quoteall($row['code']).")"); echo $modifyoutput; flush();ob_flush();
        }
    }

    // Convert answers to subquestions

    $answerquery = "select a.*, q.sid, q.gid, q.type from {$dbprefix}answers a,{$dbprefix}questions q where a.qid=q.qid and a.language=q.language and q.type in ('1','A','B','C','E','F','H','K',';',':','M','P','Q')";
    $answerresult = db_execute_assoc($answerquery);
    if (!$answerresult) {return "Database Error";}
    else
    {
        while ( $row = $answerresult->FetchRow() )
        {

            $insertarray=array();
            if (isset($aQIDReplacements[$row['qid'].'_'.$row['code']]))
            {
                $insertarray['qid']=$aQIDReplacements[$row['qid'].'_'.$row['code']];
            }
            $insertarray['sid']=$row['sid'];
            $insertarray['gid']=$row['gid'];
            $insertarray['type']=$row['type'];
            $insertarray['parent_qid']=$row['qid'];
            $insertarray['title']=$row['code'];
            $insertarray['question']=$row['answer'];
            $insertarray['question_order']=$row['sortorder'];
            $insertarray['language']=$row['language'];
            $tablename="{$dbprefix}questions";
            $query=$connect->GetInsertSQL($tablename,$insertarray);
            modify_database("",$query); echo $modifyoutput; flush();ob_flush();
            if (!isset($insertarray['qid']))
            {
               $aQIDReplacements[$row['qid'].'_'.$row['code']]=$connect->Insert_ID("{$dbprefix}questions","qid");
               $iSaveSQID=$aQIDReplacements[$row['qid'].'_'.$row['code']];
            }
            else
            {
               $iSaveSQID=$insertarray['qid'];
            }
            if (($row['type']=='M' || $row['type']=='P') && $row['default_value']=='Y')
            {
                modify_database("","INSERT INTO {$dbprefix}defaultvalues (qid, sqid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},{$iSaveSQID},0,".db_quoteall($row['language']).",'','Y')"); echo $modifyoutput; flush();ob_flush();
            }
        }
    }
    modify_database("","delete from {$dbprefix}answers using {$dbprefix}questions where {$dbprefix}answers.qid={$dbprefix}questions.qid and {$dbprefix}questions.type in ('1','A','B','C','E','F','H',';',':')"); echo $modifyoutput; flush();ob_flush();

    // Convert labels to answers
    $answerquery = "select qid ,type ,lid ,lid1, language from {$dbprefix}questions where parent_qid=0 and type in ('1','F','H','M','P','W','Z')";
    $answerresult = db_execute_assoc($answerquery);
    if (!$answerresult)
    {
        return "Database Error";
    }
    else
    {
        while ( $row = $answerresult->FetchRow() )
        {
            $labelquery="Select * from {$dbprefix}labels where lid={$row['lid']} and language=".db_quoteall($row['language']);
            $labelresult = db_execute_assoc($labelquery);
            while ( $lrow = $labelresult->FetchRow() )
            {
                modify_database("","INSERT INTO {$dbprefix}answers (qid, code, answer, sortorder, language, assessment_value) VALUES ({$row['qid']},".db_quoteall($lrow['code']).",".db_quoteall($lrow['title']).",{$lrow['sortorder']},".db_quoteall($lrow['language']).",{$lrow['assessment_value']})"); echo $modifyoutput; flush();ob_flush();
                //$labelids[]
            }
            if ($row['type']=='1')
            {
                $labelquery="Select * from {$dbprefix}labels where lid={$row['lid1']} and language=".db_quoteall($row['language']);
                $labelresult = db_execute_assoc($labelquery);
                while ( $lrow = $labelresult->FetchRow() )
                {
                    modify_database("","INSERT INTO {$dbprefix}answers (qid, code, answer, sortorder, language, scale_id, assessment_value) VALUES ({$row['qid']},".db_quoteall($lrow['code']).",".db_quoteall($lrow['title']).",{$lrow['sortorder']},".db_quoteall($lrow['language']).",1,{$lrow['assessment_value']})"); echo $modifyoutput; flush();ob_flush();
                }
            }
        }
    }

    // Convert labels to subquestions
    $answerquery = "select * from {$dbprefix}questions where parent_qid=0 and type in (';',':')";
    $answerresult = db_execute_assoc($answerquery);
    if (!$answerresult)
    {
        return "Database Error";
    }
    else
    {
        while ( $row = $answerresult->FetchRow() )
        {
            $labelquery="Select * from {$dbprefix}labels where lid={$row['lid']} and language=".db_quoteall($row['language']);
            $labelresult = db_execute_assoc($labelquery);
            while ( $lrow = $labelresult->FetchRow() )
            {
                $insertarray=array();
                if (isset($aQIDReplacements[$row['qid'].'_'.$lrow['code'].'_1']))
                {
                    $insertarray['qid']=$aQIDReplacements[$row['qid'].'_'.$lrow['code'].'_1'];
                }
                $insertarray['sid']=$row['sid'];
                $insertarray['type']=$row['type'];
                $insertarray['gid']=$row['gid'];
                $insertarray['parent_qid']=$row['qid'];
                $insertarray['title']=$lrow['code'];
                $insertarray['question']=$lrow['title'];
                $insertarray['question_order']=$lrow['sortorder'];
                $insertarray['language']=$lrow['language'];
                $insertarray['scale_id']=1;
                $tablename="{$dbprefix}questions";
                $query=$connect->GetInsertSQL($tablename,$insertarray);
                modify_database("",$query); echo $modifyoutput; flush();ob_flush();
                if (isset($insertarray['qid']))
                {
                   $aQIDReplacements[$row['qid'].'_'.$lrow['code'].'_1']=$connect->Insert_ID("{$dbprefix}questions","qid");
                }
            }
        }
    }




    $updatequery = "update {$dbprefix}questions set type='!' where type='W'";
    modify_database("",$updatequery); echo $modifyoutput; flush();ob_flush();
    $updatequery = "update {$dbprefix}questions set type='L' where type='Z'";
    modify_database("",$updatequery); echo $modifyoutput; flush();ob_flush();

    // Now move all non-standard templates to the /upload dir
    global $usertemplaterootdir, $standardtemplates,$standardtemplaterootdir;

    if (!$usertemplaterootdir) {die("gettemplatelist() no template directory");}
    if ($handle = opendir($standardtemplaterootdir))
    {
        while (false !== ($file = readdir($handle)))
        {
            if (!is_file("$standardtemplaterootdir/$file") && $file != "." && $file != ".." && $file!=".svn" && !isStandardTemplate($file))
            {
                if (!rename($standardtemplaterootdir.DIRECTORY_SEPARATOR.$file,$usertemplaterootdir.DIRECTORY_SEPARATOR.$file))
                {
                   echo "There was a problem moving directory '".$standardtemplaterootdir.DIRECTORY_SEPARATOR.$file."' to '".$usertemplaterootdir.DIRECTORY_SEPARATOR.$file."' due to missing permissions. Please do this manually.<br />";
                };
            }
        }
        closedir($handle);
    }

}

function upgrade_timing_tables146()
{
    global $modifyoutput,$dbprefix, $connect;
    $aTimingTables=$connect->MetaTables('TABLES',false, "%timings");
    foreach ($aTimingTables as $sTable) {
        modify_database("","ALTER TABLE {$sTable} RENAME COLUMN \"interviewTime\" TO interviewtime;"); echo $modifyoutput; flush();ob_flush();
    }
}
