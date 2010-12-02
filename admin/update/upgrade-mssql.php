<?php
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
 * $Id: upgrade-mssql.php 7556 2009-09-01 23:48:37Z c_schmitz $
 */

// There will be a file for each database (accordingly named to the dbADO scheme)
// where based on the current database version the database is upgraded
// For this there will be a settings table which holds the last time the database was upgraded

function db_upgrade($oldversion) {
    /// This function does anything necessary to upgrade
    /// older versions to match current functionality
    global $modifyoutput, $dbprefix;
    if ($oldversion < 111) {

        // Language upgrades from version 110 to 111 since the language names did change

        $oldnewlanguages=array('german_informal'=>'german-informal',
                              'cns'=>'cn-Hans',
                              'cnt'=>'cn-Hant',
                              'pt_br'=>'pt-BR',
                              'gr'=>'el',
                              'jp'=>'ja',
                              'si'=>'sl',
                              'se'=>'sv',
                              'vn'=>'vi');

        foreach  ($oldnewlanguages as $oldlang=>$newlang)
        {
            modify_database("","update [prefix_answers] set [language]='$newlang' where language='$oldlang'");  echo $modifyoutput; flush();
            modify_database("","update [prefix_questions] set [language]='$newlang' where language='$oldlang'");echo $modifyoutput; flush();
            modify_database("","update [prefix_groups] set [language]='$newlang' where language='$oldlang'");echo $modifyoutput; flush();
            modify_database("","update [prefix_labels] set [language]='$newlang' where language='$oldlang'");echo $modifyoutput; flush();
            modify_database("","update [prefix_surveys] set [language]='$newlang' where language='$oldlang'");echo $modifyoutput; flush();
            modify_database("","update [prefix_surveys_languagesettings] set [surveyls_language]='$newlang' where surveyls_language='$oldlang'");echo $modifyoutput; flush();
            modify_database("","update [prefix_users] set [lang]='$newlang where lang='$oldlang'");echo $modifyoutput; flush();
        }



        $resultdata=db_execute_assoc("select * from ".db_table_name("labelsets"));
        while ($datarow = $resultdata->FetchRow()){
            $toreplace=$datarow['languages'];
            $toreplace=str_replace('german_informal','german-informal',$toreplace);
            $toreplace=str_replace('cns','cn-Hans',$toreplace);
            $toreplace=str_replace('cnt','cn-Hant',$toreplace);
            $toreplace=str_replace('pt_br','pt-BR',$toreplace);
            $toreplace=str_replace('gr','el',$toreplace);
            $toreplace=str_replace('jp','ja',$toreplace);
            $toreplace=str_replace('si','sl',$toreplace);
            $toreplace=str_replace('se','sv',$toreplace);
            $toreplace=str_replace('vn','vi',$toreplace);
            modify_database("","update [prefix_labelsets] set [languages`='$toreplace' where lid=".$datarow['lid']);echo $modifyoutput;flush();
        }


        $resultdata=db_execute_assoc("select * from ".db_table_name("surveys"));
        while ($datarow = $resultdata->FetchRow()){
            $toreplace=$datarow['additional_languages'];
            $toreplace=str_replace('german_informal','german-informal',$toreplace);
            $toreplace=str_replace('cns','cn-Hans',$toreplace);
            $toreplace=str_replace('cnt','cn-Hant',$toreplace);
            $toreplace=str_replace('pt_br','pt-BR',$toreplace);
            $toreplace=str_replace('gr','el',$toreplace);
            $toreplace=str_replace('jp','ja',$toreplace);
            $toreplace=str_replace('si','sl',$toreplace);
            $toreplace=str_replace('se','sv',$toreplace);
            $toreplace=str_replace('vn','vi',$toreplace);
            modify_database("","update [prefix_surveys] set [additional_languages`='$toreplace' where sid=".$datarow['sid']);echo $modifyoutput;flush();
        }
        modify_database("","update [prefix_settings_global] set [stg_value]='111' where stg_name='DBVersion'"); echo $modifyoutput;

    }

    if ($oldversion < 112) {
        //The size of the users_name field is now 64 char (20 char before version 112)
        modify_database("","ALTER TABLE [prefix_users] ALTER COLUMN [users_name] VARCHAR( 64 ) NOT NULL"); echo $modifyoutput; flush();
        modify_database("","update [prefix_settings_global] set [stg_value]='112' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 113) {
        //No action needed
        modify_database("","update [prefix_settings_global] set [stg_value]='113' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 114) {
        modify_database("","ALTER TABLE [prefix_saved_control] ALTER COLUMN [email] VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_surveys] ALTER COLUMN [adminemail] VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_users] ALTER COLUMN [email] VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();
        modify_database("",'INSERT INTO [prefix_settings_global] VALUES (\'SessionName\', \'$sessionname\');');echo $modifyoutput; flush();
        modify_database("","update [prefix_settings_global] set [stg_value]='114' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 126) {
        modify_database("","ALTER TABLE [prefix_surveys] ADD  [printanswers] CHAR(1) DEFAULT 'N'"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_surveys] ADD  [listpublic] CHAR(1) DEFAULT 'N'"); echo $modifyoutput; flush();
        upgrade_survey_tables117();
        upgrade_survey_tables118();
        //119
        modify_database("","CREATE TABLE [prefix_quota] (
						  [id] int NOT NULL IDENTITY (1,1),
						  [sid] int,
						  [name] varchar(255) ,
						  [qlimit] int ,
						  [action] int ,
						  [active] int NOT NULL default '1',
						  PRIMARY KEY  ([id])
						);");echo $modifyoutput; flush();
        modify_database("","CREATE TABLE [prefix_quota_members] (
						  [id] int NOT NULL IDENTITY (1,1),
						  [sid] int ,
						  [qid] int ,
						  [quota_id] int ,
						  [code] varchar(5) ,
						  PRIMARY KEY  ([id])
						);");echo $modifyoutput; flush();

        // Rename Norwegian language code from NO to NB
        $oldnewlanguages=array('no'=>'nb');
        foreach  ($oldnewlanguages as $oldlang=>$newlang)
        {
            modify_database("","update [prefix_answers] set [language]='$newlang' where [language]='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update [prefix_questions] set [language]='$newlang' where [language]='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update [prefix_groups] set [language]='$newlang' where [language]='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update [prefix_labels] set [language]='$newlang' where [language]='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update [prefix_surveys] set [language]='$newlang' where [language]='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update [prefix_surveys_languagesettings] set [surveyls_language]='$newlang' where surveyls_language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update [prefix_users] set [lang]='$newlang' where lang='$oldlang'");echo $modifyoutput;flush();
        }

        $resultdata=db_execute_assoc("select * from ".db_table_name("labelsets"));
        while ($datarow = $resultdata->FetchRow()){
            $toreplace=$datarow['languages'];
            $toreplace2=str_replace('no','nb',$toreplace);
            if ($toreplace2!=$toreplace) {modify_database("","update  [prefix_labelsets] set [languages]='$toreplace' where lid=".$datarow['lid']);echo $modifyoutput;flush();}
        }

        $resultdata=db_execute_assoc("select * from ".db_table_name("surveys"));
        while ($datarow = $resultdata->FetchRow()){
            $toreplace=$datarow['additional_languages'];
            $toreplace2=str_replace('no','nb',$toreplace);
            if ($toreplace2!=$toreplace) {modify_database("","update [prefix_surveys] set [additional_languages]='$toreplace' where sid=".$datarow['sid']);echo $modifyoutput;flush();}
        }

        modify_database("","ALTER TABLE [prefix_surveys] ADD [htmlemail] CHAR(1) DEFAULT 'N'"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_surveys] ADD [usecaptcha] CHAR(1) DEFAULT 'N'"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_surveys] ADD [tokenanswerspersistence] CHAR(1) DEFAULT 'N'"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_users] ADD [htmleditormode] CHAR(7) DEFAULT 'default'"); echo $modifyoutput; flush();
        modify_database("","CREATE TABLE [prefix_templates_rights] (
						  [uid] int NOT NULL,
						  [folder] varchar(255) NOT NULL,
						  [use] int NOT NULL,
						  PRIMARY KEY  ([uid],[folder])
						  );");echo $modifyoutput; flush();
        modify_database("","CREATE TABLE [prefix_templates] (
						  [folder] varchar(255) NOT NULL,
						  [creator] int NOT NULL,
						  PRIMARY KEY  ([folder])
						  );");echo $modifyoutput; flush();
        //123
        modify_database("","ALTER TABLE [prefix_conditions] ALTER COLUMN [value] VARCHAR(255)"); echo $modifyoutput; flush();
        // There is no other way to remove the previous default value
        /*modify_database("","DECLARE @STR VARCHAR(100)
         SET @STR = (
         SELECT NAME
         FROM SYSOBJECTS SO
         JOIN SYSCONSTRAINTS SC ON SO.ID = SC.CONSTID
         WHERE OBJECT_NAME(SO.PARENT_OBJ) = 'lime_labels'
         AND SO.XTYPE = 'D' AND SC.COLID =
         (SELECT COLID FROM SYSCOLUMNS WHERE ID = OBJECT_ID('lime_labels') AND NAME = 'title'))
         SET @STR = 'ALTER TABLE lime_labels DROP CONSTRAINT ' + @STR
         exec (@STR);"); echo $modifyoutput; flush();     */

        modify_database("","ALTER TABLE [prefix_labels] ALTER COLUMN [title] varchar(4000)"); echo $modifyoutput; flush();
        //124
        modify_database("","ALTER TABLE [prefix_surveys] ADD [bounce_email] text"); echo $modifyoutput; flush();
        //125
        upgrade_token_tables125();
        modify_database("","EXEC sp_rename 'prefix_users.move_user','superadmin'"); echo $modifyoutput; flush();
        modify_database("","UPDATE [prefix_users] SET [superadmin]=1 where ([create_survey]=1 AND [create_user]=1 AND [delete_user]=1 AND [configurator]=1)"); echo $modifyoutput; flush();
        //126
        modify_database("","ALTER TABLE [prefix_questions] ADD [lid1] int NOT NULL DEFAULT '0'"); echo $modifyoutput; flush();
        modify_database("","UPDATE [prefix_conditions] SET [method]='==' where ( [method] is null) or [method]='' or [method]='0'"); echo $modifyoutput; flush();

        modify_database("","update [prefix_settings_global] set [stg_value]='126' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 127) {
        modify_database("","create index [answers_idx2] on [prefix_answers] ([sortorder])"); echo $modifyoutput;
        modify_database("","create index [assessments_idx2] on [prefix_assessments] ([sid])"); echo $modifyoutput;
        modify_database("","create index [assessments_idx3] on [prefix_assessments] ([gid])"); echo $modifyoutput;
        modify_database("","create index [conditions_idx2] on [prefix_conditions] ([qid])"); echo $modifyoutput;
        modify_database("","create index [conditions_idx3] on [prefix_conditions] ([cqid])"); echo $modifyoutput;
        modify_database("","create index [groups_idx2] on [prefix_groups] ([sid])"); echo $modifyoutput;
        modify_database("","create index [question_attributes_idx2] on [prefix_question_attributes] ([qid])"); echo $modifyoutput;
        modify_database("","create index [questions_idx2] on [prefix_questions] ([sid])"); echo $modifyoutput;
        modify_database("","create index [questions_idx3] on [prefix_questions] ([gid])"); echo $modifyoutput;
        modify_database("","create index [questions_idx4] on [prefix_questions] ([type])"); echo $modifyoutput;
        modify_database("","create index [quota_idx2] on [prefix_quota] ([sid])"); echo $modifyoutput;
        modify_database("","create index [saved_control_idx2] on [prefix_saved_control] ([sid])"); echo $modifyoutput;
        modify_database("","create index [user_in_groups_idx1] on [prefix_user_in_groups] ([ugid], [uid])"); echo $modifyoutput;
        modify_database("","update [prefix_settings_global] set [stg_value]='127' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 128) {
        upgrade_token_tables128();
        modify_database("","update [prefix_settings_global] set [stg_value]='128' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 129) {
        //128
        modify_database("","ALTER TABLE [prefix_surveys] ADD [startdate] DATETIME"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_surveys] ADD [usestartdate] char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();
        modify_database("","update [prefix_settings_global] set [stg_value]='129' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 130)
    {
        modify_database("","ALTER TABLE [prefix_conditions] ADD [scenario] int NOT NULL DEFAULT '1'"); echo $modifyoutput; flush();
        modify_database("","UPDATE [prefix_conditions] SET [scenario]=1 where ( [scenario] is null) or [scenario]='' or [scenario]=0"); echo $modifyoutput; flush();
        modify_database("","update [prefix_settings_global] set [stg_value]='130' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 131)
    {
        modify_database("","ALTER TABLE [prefix_surveys] ADD [publicstatistics] char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();
        modify_database("","update [prefix_settings_global] set [stg_value]='131' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 132)
    {
        modify_database("","ALTER TABLE [prefix_surveys] ADD [publicgraphs] char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();
        modify_database("","update [prefix_settings_global] set [stg_value]='132' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 133)
    {
        modify_database("","ALTER TABLE [prefix_users] ADD [one_time_pw] text"); echo $modifyoutput; flush();
        // Add new assessment setting
        modify_database("","ALTER TABLE [prefix_surveys] ADD [assessments] char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();
        // add new assessment value fields to answers & labels
        modify_database("","ALTER TABLE [prefix_answers] ADD [assessment_value] int NOT NULL default '0'"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_labels] ADD [assessment_value] int NOT NULL default '0'"); echo $modifyoutput; flush();
        // copy any valid codes from code field to assessment field
        modify_database("","update [prefix_answers] set [assessment_value]=CAST([code] as int)");// no output here is intended
        modify_database("","update [prefix_labels] set [assessment_value]=CAST([code] as int)");// no output here is intended
        // activate assessment where assesment rules exist
        modify_database("","update [prefix_surveys] set [assessments]='Y' where [sid] in (SELECT [sid] FROM [prefix_assessments] group by [sid])"); echo $modifyoutput; flush();
        // add language field to assessment table
        modify_database("","ALTER TABLE [prefix_assessments] ADD [language] varchar(20) NOT NULL default 'en'"); echo $modifyoutput; flush();
        // update language field with default language of that particular survey
        modify_database("","update [prefix_assessments] set [language]=(select [language] from [prefix_surveys] where [sid]=[prefix_assessments].[sid])"); echo $modifyoutput; flush();
        // copy assessment link to message since from now on we will have HTML assignment messages
        modify_database("","update [prefix_assessments] set [message]=cast([message] as varchar) +'<br /><a href=\"'+[link]+'\">'+[link]+'</a>'"); echo $modifyoutput; flush();
        // drop the old link field
        modify_database("","ALTER TABLE [prefix_assessments] DROP COLUMN [link]"); echo $modifyoutput; flush();
        // change the primary index to include language
        // and fix missing translations for assessments
        upgrade_survey_tables133a();

        // Add new fields to survey language settings
        modify_database("","ALTER TABLE [prefix_surveys_languagesettings] ADD [surveyls_url] varchar(255)"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_surveys_languagesettings] ADD [surveyls_endtext] text"); echo $modifyoutput; flush();
        // copy old URL fields ot language specific entries
        modify_database("","update [prefix_surveys_languagesettings] set [surveyls_url]=(select [url] from [prefix_surveys] where [sid]=[prefix_surveys_languagesettings].[surveyls_survey_id])"); echo $modifyoutput; flush();
        // drop old URL field
        mssql_drop_constraint('url','surveys');
        modify_database("","ALTER TABLE [prefix_surveys] DROP COLUMN [url]"); echo $modifyoutput; flush();

        modify_database("","update [prefix_settings_global] set [stg_value]='133' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 134)
    {
        // Add new assessment setting
        modify_database("","ALTER TABLE [prefix_surveys] ADD [usetokens] char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();
        mssql_drop_constraint('attribute1','surveys');
        mssql_drop_constraint('attribute2','surveys');
        modify_database("", "ALTER TABLE [prefix_surveys] ADD [attributedescriptions] TEXT;"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_surveys] DROP COLUMN [attribute1]"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_surveys] DROP COLUMN [attribute2]"); echo $modifyoutput; flush();
        upgrade_token_tables134();
        modify_database("","update [prefix_settings_global] set [stg_value]='134' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 135)
    {
        mssql_drop_constraint('value','question_attributes');
        modify_database("","ALTER TABLE [prefix_question_attributes] ALTER COLUMN [value] text"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_answers] ALTER COLUMN [answer] varchar(8000)"); echo $modifyoutput; flush();
        modify_database("","update [prefix_settings_global] set [stg_value]='135' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 136) //New quota functions
    {
        modify_database("", "ALTER TABLE[prefix_quota] ADD [autoload_url] int NOT NULL default '0'"); echo $modifyoutput; flush();
        modify_database("","CREATE TABLE [prefix_quota_languagesettings] (
  							[quotals_id] int NOT NULL IDENTITY (1,1),
							[quotals_quota_id] int,
  							[quotals_language] varchar(45) NOT NULL default 'en',
  							[quotals_name] varchar(255),
  							[quotals_message] text,
  							[quotals_url] varchar(255),
  							[quotals_urldescrip] varchar(255),
  							PRIMARY KEY ([quotals_id])
							);");echo $modifyoutput; flush();
        modify_database("","update [prefix_settings_global] set [stg_value]='136' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 137) //New date format specs
    {
        modify_database("", "ALTER TABLE [prefix_surveys_languagesettings] ADD [surveyls_dateformat] int NOT NULL default '1'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_users] ADD [dateformat] int NOT NULL default '1'"); echo $modifyoutput; flush();
        modify_database("", "update [prefix_surveys] set startdate=null where usestartdate='N'"); echo $modifyoutput; flush();
        modify_database("", "update [prefix_surveys] set expires=null where useexpiry='N'"); echo $modifyoutput; flush();
        mssql_drop_constraint('usestartdate','surveys');
        mssql_drop_constraint('useexpiry','surveys');
        modify_database("", "ALTER TABLE [prefix_surveys] DROP COLUMN usestartdate"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] DROP COLUMN useexpiry"); echo $modifyoutput; flush();
        modify_database("","update [prefix_settings_global] set [stg_value]='137' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 138) //Modify quota field
    {
        modify_database("", "ALTER TABLE [prefix_quota_members] ALTER COLUMN [code] VARCHAR(11) NULL"); echo $modifyoutput; flush();
        modify_database("", "UPDATE [prefix_settings_global] SET [stg_value]='138' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 139) //Modify quota field
    {
        upgrade_survey_tables139();
        modify_database("", "UPDATE [prefix_settings_global] SET [stg_value]='139' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 140) //Modify surveys table
    {
        modify_database("", "ALTER TABLE [prefix_surveys] ADD [emailresponseto] text"); echo $modifyoutput; flush();
        modify_database("", "UPDATE [prefix_settings_global] SET [stg_value]='140' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 141) //Modify surveys table
    {
        modify_database("", "ALTER TABLE [prefix_surveys] ADD [tokenlength] tinyint NOT NULL default '15'"); echo $modifyoutput; flush();
        modify_database("", "UPDATE [prefix_settings_global] SET [stg_value]='141' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 142) //Modify surveys table
    {
        upgrade_question_attributes142();
        modify_database("", "ALTER TABLE [prefix_surveys] ALTER COLUMN [startdate] datetime NULL"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ALTER COLUMN [expires] datetime NULL"); echo $modifyoutput; flush();
        modify_database("", "UPDATE [prefix_question_attributes] SET [value]='0' WHERE cast([value] as varchar)='false'"); echo $modifyoutput; flush();
        modify_database("", "UPDATE [prefix_question_attributes] SET [value]='1' WHERE cast([value] as varchar)='true'"); echo $modifyoutput; flush();
        modify_database("", "UPDATE [prefix_settings_global] SET [stg_value]='142' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 143) //Modify surveys table
    {
        modify_database("", "ALTER TABLE [prefix_questions] ADD parent_qid integer NOT NULL default '0'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_answers] ADD scale_id tinyint NOT NULL default '0'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_questions] ADD scale_id tinyint NOT NULL default '0'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_questions] ADD same_default tinyint NOT NULL default '0'"); echo $modifyoutput; flush();
        mssql_drop_primary_index('answers');
        modify_database("","ALTER TABLE [prefix_answers] ADD CONSTRAINT pk_answers_qcls PRIMARY KEY ([qid],[code],[language],[scale_id])"); echo $modifyoutput; flush();
        modify_database("", "CREATE TABLE [prefix_defaultvalues] (
                              [qid] integer NOT NULL default '0',
                              [scale_id] tinyint NOT NULL default '0',
                              [sqid] integer NOT NULL default '0',
                              [language] varchar(20) NOT NULL,
                              [specialtype] varchar(20) NOT NULL default '',
                              [defaultvalue] text,
                              CONSTRAINT pk_defaultvalues_qlss PRIMARY KEY ([qid] , [scale_id], [language], [specialtype], [sqid]))"); echo $modifyoutput; flush();

        // -Move all 'answers' that are subquestions to the questions table
        // -Move all 'labels' that are answers to the answers table
        // -Transscribe the default values where applicable
        // -Move default values from answers to questions
        upgrade_tables143();

        mssql_drop_constraint('default_value','answers');
        modify_database("", "ALTER TABLE [prefix_answers] DROP COLUMN [default_value]"); echo $modifyoutput; flush();
        mssql_drop_constraint('lid','questions');
        modify_database("", "ALTER TABLE [prefix_questions] DROP COLUMN lid"); echo $modifyoutput; flush();
        mssql_drop_constraint('lid1','questions');
        modify_database("", "ALTER TABLE [prefix_questions] DROP COLUMN lid1"); echo $modifyoutput; flush();
        // add field for timings and table for extended conditions
        modify_database("", "ALTER TABLE [prefix_surveys] ADD savetimings char(1) default 'N'"); echo $modifyoutput; flush();
        modify_database("", "CREATE TABLE prefix_sessions(
                              sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
                              expiry DATETIME NOT NULL ,
                              expireref VARCHAR( 250 ) DEFAULT '',
                              created DATETIME NOT NULL ,
                              modified DATETIME NOT NULL ,
                              sessdata text,
                              CONSTRAINT pk_sessions_sesskey PRIMARY KEY ( [sesskey] ))"); echo $modifyoutput; flush();          
        modify_database("", "create index [idx_expiry] on [prefix_sessions] ([expiry])"); echo $modifyoutput;
        modify_database("", "create index [idx_expireref] on [prefix_sessions] ([expireref])"); echo $modifyoutput;
        modify_database("", "UPDATE [prefix_settings_global] SET stg_value='143' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();


        



    }

    if ($oldversion < 145) //Modify surveys table
    {
        modify_database("", "ALTER TABLE [prefix_surveys] ADD showXquestions CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD showgroupinfo CHAR(1) NULL default 'B'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD shownoanswer CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD showqnumcode CHAR(1) NULL default 'X'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD bouncetime BIGINT NULL"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD bounceprocessing VARCHAR(1) NULL default 'N'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD bounceaccounttype VARCHAR(4) NULL"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD bounceaccounthost VARCHAR(200) NULL "); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD bounceaccountpass VARCHAR(100) NULL"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD bounceaccountencryption VARCHAR(3) NULL"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD bounceaccountuser VARCHAR(200) NULL"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD showwelcome CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD showprogress CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD allowjumps CHAR(1) NULL default 'N'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD navigationdelay tinyint default '0'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE [prefix_surveys] ADD nokeyboard CHAR(1) NULL default 'N'"); echo $modifyoutput; flush();
        modify_database("", "CREATE TABLE [prefix_survey_permissions] (
                            [sid] INT NOT NULL,         
                            [uid] INT NOT NULL,         
                            [permission] VARCHAR(20) NOT NULL,       
                            [create_p] TINYINT NOT NULL default '0', 
                            [read_p] TINYINT NOT NULL default '0', 
                            [update_p] TINYINT NOT NULL default '0', 
                            [delete_p] TINYINT NOT NULL default '0', 
                            [import_p] TINYINT NOT NULL default '0', 
                            [export_p] inTINYINT NOT NULL default '0', 
                            PRIMARY KEY ([sid], [uid],[permission])
                        );");
		upgrade_surveypermissions_table145();
        modify_database("", "DROP TABLE [prefix_surveys_rights]"); echo $modifyoutput; flush();
        
        // Add new fields for email templates
        modify_database("", "ALTER TABLE prefix_surveys_languagesettings ADD 
                              email_admin_notification_subj  VARCHAR(255) NULL,    
                              email_admin_notification TEXT NULL,        
                              email_admin_responses_subj VARCHAR(255) NULL,    
                              email_admin_responses TEXT NULL");
        
        //Add index to questions table to speed up subquestions
        modify_database("", "create index [parent_qid_idx] on [prefix_questions] ([parent_qid])"); echo $modifyoutput; flush();
                                   
        modify_database("", "ALTER TABLE prefix_surveys ADD emailnotificationto text DEFAULT NULL"); echo $modifyoutput; flush();
        upgrade_survey_table145();                                           
        mssql_drop_constraint('notification','surveys');
        modify_database("", "ALTER TABLE [prefix_surveys] DROP COLUMN [notification]"); echo $modifyoutput; flush();
                   
        // modify length of method in conditions
        modify_database("","ALTER TABLE [prefix_conditions] ALTER COLUMN [method] CHAR( 5 ) NOT NULL"); echo $modifyoutput; flush();

        //Add index to questions table to speed up subquestions
        modify_database("", "create index [parent_qid] on [prefix_questions] ([parent_qid])"); echo $modifyoutput; flush();
        
        modify_database("","EXEC sp_rename 'prefix_surveys.private','anonymized'"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE [prefix_surveys] ALTER COLUMN [anonymized] char(1) NOT NULL default 'N';"); echo $modifyoutput; flush();
        
        
        modify_database("", "UPDATE [prefix_settings_global] SET stg_value='145' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();

        modify_database("", "CREATE TABLE [prefix_failed_login_attempts] (
                              [id] int(11) NOT NULL AUTO_INCREMENT,
                              [ip] varchar(37) NOT NULL,
                              [last_attempt] varchar(20) NOT NULL,
                              [number_attempts] int(11) NOT NULL,
                              PRIMARY KEY ([id]));"); echo $modifyoutput; flush();

    }

    modify_database("", "ALTER TABLE  [prefix_surveys_languagesettings] ADD  [surveyls_numberformat] int(11) default 0 NOT NULL AFTER  [surveyls_dateformat]"); echo $modifyoutput; flush();
                              
    echo '<br /><br />Database update finished ('.date('Y-m-d H:i:s').')<br />';
    return true;
}

function upgrade_survey_tables117()
{
    global $modifyoutput;
    $surveyidquery = "SELECT sid FROM ".db_table_name('surveys')." WHERE active='Y' and datestamp='Y'";
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    else
    {
        while ( $sv = $surveyidresult->FetchRow() )
        {
            modify_database("","ALTER TABLE ".db_table_name('survey_'.$sv[0])." ADD [startdate] datetime"); echo $modifyoutput; flush();
        }
    }
}


function upgrade_survey_tables118()
{
    global $connect,$modifyoutput,$dbprefix;
    $tokentables=$connect->MetaTables('TABLES',false,$dbprefix."tokens%");
    foreach ($tokentables as $sv)
    {
        modify_database("","ALTER TABLE ".$sv." ALTER COLUMN [token] VARCHAR(36)"); echo $modifyoutput; flush();
    }
}


function upgrade_token_tables125()
{
    global $connect,$modifyoutput,$dbprefix;
    $tokentables=$connect->MetaTables('TABLES',false,$dbprefix."tokens%");
    foreach ($tokentables as $sv)
    {
        modify_database("","ALTER TABLE ".$sv." ADD [emailstatus] VARCHAR(300) DEFAULT 'OK'"); echo $modifyoutput; flush();
    }
}


function upgrade_token_tables128()
{
    global $connect,$modifyoutput,$dbprefix;
    $tokentables=$connect->MetaTables('TABLES',false,$dbprefix."tokens%");
    foreach ($tokentables as $sv)
    {
        modify_database("","ALTER TABLE ".$sv." ADD [remindersent] VARCHAR(17) DEFAULT 'OK'"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE ".$sv." ADD [remindercount] int DEFAULT '0'"); echo $modifyoutput; flush();
    }
}


function upgrade_survey_tables133a()
{
    global $dbprefix, $connect, $modifyoutput;
    mssql_drop_primary_index('assessments');
    // add the new primary key
    modify_database("","ALTER TABLE [prefix_assessments] ADD CONSTRAINT pk_assessments_id_lang PRIMARY KEY ([id],[language])"); echo $modifyoutput; flush();
    $surveyidquery = "SELECT sid,additional_languages FROM ".db_table_name('surveys');
    $surveyidresult = db_execute_num($surveyidquery);
    while ( $sv = $surveyidresult->FetchRow() )
    {
        FixLanguageConsistency($sv[0],$sv[1]);
    }
}


function upgrade_token_tables134()
{
    global $connect,$modifyoutput,$dbprefix;
    $tokentables=$connect->MetaTables('TABLES',false,$dbprefix."tokens%");
    foreach ($tokentables as $sv)
    {
        modify_database("","ALTER TABLE ".$sv." ADD [validfrom] DATETIME"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE ".$sv." ADD [validuntil] DATETIME"); echo $modifyoutput; flush();
    }
}

function mssql_drop_primary_index($tablename)
{
     global $dbprefix, $connect, $modifyoutput;
    // find out the constraint name of the old primary key
    $pkquery = "SELECT CONSTRAINT_NAME "
              ."FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS "
              ."WHERE     (TABLE_NAME = '{$dbprefix}{$tablename}') AND (CONSTRAINT_TYPE = 'PRIMARY KEY')";

    $primarykey=$connect->GetOne($pkquery);
    if ($primarykey!=false)
    {
        modify_database("","ALTER TABLE [prefix_{$tablename}] DROP CONSTRAINT {$primarykey}"); echo $modifyoutput; flush();
    }
}


function mssql_drop_constraint($fieldname, $tablename)
{
    global $dbprefix, $connect, $modifyoutput;
    // find out the name of the default constraint
    // Did I already mention that this is the most suckiest thing I have ever seen in MSSQL database?
    $dfquery ="SELECT c_obj.name AS constraint_name
                FROM  sys.sysobjects AS c_obj INNER JOIN
                      sys.sysobjects AS t_obj ON c_obj.parent_obj = t_obj.id INNER JOIN
                      sys.sysconstraints AS con ON c_obj.id = con.constid INNER JOIN
                      sys.syscolumns AS col ON t_obj.id = col.id AND con.colid = col.colid
                WHERE (c_obj.xtype = 'D') AND (col.name = '$fieldname') AND (t_obj.name='$dbprefix$tablename')";
    $defaultname=$connect->GetRow($dfquery);
    if ($defaultname!=false)
    {
        modify_database("","ALTER TABLE [prefix_$tablename] DROP CONSTRAINT {$defaultname['constraint_name']}"); echo $modifyoutput; flush();
    }


}

function upgrade_survey_tables139()
{
    global $modifyoutput,$dbprefix;
    $surveyidquery = db_select_tables_like($dbprefix."survey_%");
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    else
    {
        while ( $sv = $surveyidresult->FetchRow() )
        {
            modify_database("","ALTER TABLE ".$sv[0]." ADD [lastpage] int"); echo $modifyoutput; flush();
        }
    }
}

function upgrade_question_attributes142()
{
    global $modifyoutput,$dbprefix, $connect;
    $attributequery="Select qid from {$dbprefix}question_attributes where attribute='exclude_all_other'  group by qid having count(qid)>1 ";
    $questionids = db_select_column($attributequery);
    foreach ($questionids as $questionid)
    {
        //Select all affected question attributes
        $attributevalues=db_select_column("SELECT value from {$dbprefix}question_attributes where attribute='exclude_all_other' and qid=".$questionid);
        modify_database("","delete from {$dbprefix}question_attributes where attribute='exclude_all_other' and qid=".$questionid); echo $modifyoutput; flush();
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
            modify_database("","INSERT INTO {$dbprefix}defaultvalues (qid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},0,".db_quoteall($row['language']).",'',".db_quoteall($row['code']).")"); echo $modifyoutput; flush();
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
                db_switchIDInsert('questions',true);                
            }
            $insertarray['sid']=$row['sid'];
            $insertarray['gid']=$row['gid'];
            $insertarray['parent_qid']=$row['qid'];
            $insertarray['title']=$row['code'];
            $insertarray['question']=$row['answer'];
            $insertarray['question_order']=$row['sortorder'];
            $insertarray['language']=$row['language'];
            $tablename="{$dbprefix}questions";
            $query=$connect->GetInsertSQL($tablename,$insertarray);
            modify_database("",$query); echo $modifyoutput; flush();
            if (!isset($insertarray['qid']))
            {
               $aQIDReplacements[$row['qid'].'_'.$row['code']]=$connect->Insert_ID("{$dbprefix}questions","qid"); 
               $iSaveSQID=$aQIDReplacements[$row['qid'].'_'.$row['code']];
            }
            else
            {
               $iSaveSQID=$insertarray['qid'];
                db_switchIDInsert('questions',false);                
            }
            if (($row['type']=='M' || $row['type']=='P') && $row['default_value']=='Y')
            {
                modify_database("","INSERT INTO {$dbprefix}defaultvalues (qid, sqid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},{$iSaveSQID},0,".db_quoteall($row['language']).",'','Y')"); echo $modifyoutput; flush();
            }
        }
    }
    modify_database("","delete {$dbprefix}answers from {$dbprefix}answers LEFT join {$dbprefix}questions ON {$dbprefix}answers.qid={$dbprefix}questions.qid where {$dbprefix}questions.type in ('1','F','H','M','P','W','Z')"); echo $modifyoutput; flush();

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
                modify_database("","INSERT INTO {$dbprefix}answers (qid, code, answer, sortorder, language, assessment_value) VALUES ({$row['qid']},".db_quoteall($lrow['code']).",".db_quoteall($lrow['title']).",{$lrow['sortorder']},".db_quoteall($lrow['language']).",{$lrow['assessment_value']})"); echo $modifyoutput; flush();
                //$labelids[]
            }
            if ($row['type']=='1')
            {
                $labelquery="Select * from {$dbprefix}labels where lid={$row['lid1']} and language=".db_quoteall($row['language']);
                $labelresult = db_execute_assoc($labelquery);
                while ( $lrow = $labelresult->FetchRow() )
                {
                    modify_database("","INSERT INTO {$dbprefix}answers (qid, code, answer, sortorder, language, scale_id, assessment_value) VALUES ({$row['qid']},".db_quoteall($lrow['code']).",".db_quoteall($lrow['title']).",{$lrow['sortorder']},".db_quoteall($lrow['language']).",1,{$lrow['assessment_value']})"); echo $modifyoutput; flush();
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
                    db_switchIDInsert('questions',true);                
                    
                }
                $insertarray['sid']=$row['sid'];
                $insertarray['gid']=$row['gid'];
                $insertarray['parent_qid']=$row['qid'];
                $insertarray['title']=$lrow['code'];
                $insertarray['question']=$lrow['title'];
                $insertarray['question_order']=$lrow['sortorder'];
                $insertarray['language']=$lrow['language'];
                $insertarray['scale_id']=1;
                $tablename="{$dbprefix}questions";
                $query=$connect->GetInsertSQL($tablename,$insertarray);
                modify_database("",$query); echo $modifyoutput; flush();
                if (isset($insertarray['qid']))
                {
                   $aQIDReplacements[$row['qid'].'_'.$lrow['code'].'_1']=$connect->Insert_ID("{$dbprefix}questions","qid"); 
                   db_switchIDInsert('questions',false);                

                }                
            }
        }
    }


    $updatequery = "update {$dbprefix}questions set type='!' where type='W'";
    modify_database("",$updatequery); echo $modifyoutput; flush();
    $updatequery = "update {$dbprefix}questions set type='L' where type='Z'";
    modify_database("",$updatequery); echo $modifyoutput; flush();
    
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
