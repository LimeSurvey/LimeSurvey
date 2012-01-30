<?php
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *	$Id$
 */

// There will be a file for each database (accordingly named to the dbADO scheme)
// where based on the current database version the database is upgraded
// For this there will be a settings table which holds the last time the database was upgraded

function db_upgrade($oldversion) {
    /// This function does anything necessary to upgrade
    /// older versions to match current functionality
    global $modifyoutput;
    $clang = Yii::app()->lang;
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
            modifyDatabase("","update [prefix_answers] set [language]='$newlang' where language='$oldlang'");  echo $modifyoutput; flush();ob_flush();
            modifyDatabase("","update [prefix_questions] set [language]='$newlang' where language='$oldlang'");echo $modifyoutput; flush();ob_flush();
            modifyDatabase("","update [prefix_groups] set [language]='$newlang' where language='$oldlang'");echo $modifyoutput; flush();ob_flush();
            modifyDatabase("","update [prefix_labels] set [language]='$newlang' where language='$oldlang'");echo $modifyoutput; flush();ob_flush();
            modifyDatabase("","update [prefix_surveys] set [language]='$newlang' where language='$oldlang'");echo $modifyoutput; flush();ob_flush();
            modifyDatabase("","update [prefix_surveys_languagesettings] set [surveyls_language]='$newlang' where surveyls_language='$oldlang'");echo $modifyoutput; flush();ob_flush();
            modifyDatabase("","update [prefix_users] set [lang]='$newlang where lang='$oldlang'");echo $modifyoutput; flush();ob_flush();
        }



        $resultdata=Yii::app()->db->createCommand("select * from {{labelsets}}")->queryAll();
        foreach ($resultdata as $datarow ){
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
            modifyDatabase("","update [prefix_labelsets] set [languages`='$toreplace' where lid=".$datarow['lid']);echo $modifyoutput;flush();ob_flush();
        }


        $resultdata=Yii::app()->db->createCommand("select * from {{surveys}}")->queryAll();
        foreach ($resultdata as $datarow ){
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
            modifyDatabase("","update [prefix_surveys] set [additional_languages`='$toreplace' where sid=".$datarow['sid']);echo $modifyoutput;flush();ob_flush();
        }
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='111' where stg_name='DBVersion'"); echo $modifyoutput;

    }

    if ($oldversion < 112) {
        //The size of the users_name field is now 64 char (20 char before version 112)
        modifyDatabase("","ALTER TABLE [prefix_users] ALTER COLUMN [users_name] VARCHAR( 64 ) NOT NULL"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='112' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 113) {
        //No action needed
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='113' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 114) {
        modifyDatabase("","ALTER TABLE [prefix_saved_control] ALTER COLUMN [email] VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE [prefix_surveys] ALTER COLUMN [adminemail] VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE [prefix_users] ALTER COLUMN [email] VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("",'INSERT INTO [prefix_settings_global] VALUES (\'SessionName\', \'$sessionname\');');echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='114' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 126) {
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD  [printanswers] CHAR(1) DEFAULT 'N'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD  [listpublic] CHAR(1) DEFAULT 'N'"); echo $modifyoutput; flush();ob_flush();
        upgrade_survey_tables117();
        upgrade_survey_tables118();
        //119
        modifyDatabase("","CREATE TABLE [prefix_quota] (
              [id] int NOT NULL IDENTITY (1,1),
              [sid] int,
              [name] varchar(255) ,
              [qlimit] int ,
              [action] int ,
              [active] int NOT NULL default '1',
              PRIMARY KEY  ([id])
            );");echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","CREATE TABLE [prefix_quota_members] (
              [id] int NOT NULL IDENTITY (1,1),
              [sid] int ,
              [qid] int ,
              [quota_id] int ,
              [code] varchar(5) ,
              PRIMARY KEY  ([id])
            );");echo $modifyoutput; flush();ob_flush();

        // Rename Norwegian language code from NO to NB
        $oldnewlanguages=array('no'=>'nb');
        foreach  ($oldnewlanguages as $oldlang=>$newlang)
        {
            modifyDatabase("","update [prefix_answers] set [language]='$newlang' where [language]='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modifyDatabase("","update [prefix_questions] set [language]='$newlang' where [language]='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modifyDatabase("","update [prefix_groups] set [language]='$newlang' where [language]='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modifyDatabase("","update [prefix_labels] set [language]='$newlang' where [language]='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modifyDatabase("","update [prefix_surveys] set [language]='$newlang' where [language]='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modifyDatabase("","update [prefix_surveys_languagesettings] set [surveyls_language]='$newlang' where surveyls_language='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modifyDatabase("","update [prefix_users] set [lang]='$newlang' where lang='$oldlang'");echo $modifyoutput;flush();ob_flush();
        }

        $resultdata=Yii::app()->db->createCommand("select * from {{labelsets}}")->queryAll();
        foreach ( $resultdata as $datarow ){
            $toreplace=$datarow['languages'];
            $toreplace2=str_replace('no','nb',$toreplace);
            if ($toreplace2!=$toreplace) {modifyDatabase("","update  [prefix_labelsets] set [languages]='$toreplace' where lid=".$datarow['lid']);echo $modifyoutput;flush();ob_flush();}
        }

        $resultdata=Yii::app()->db->createCommand("select * from {{surveys}}")->queryAll();
        foreach ( $resultdata as $datarow ){
            $toreplace=$datarow['additional_languages'];
            $toreplace2=str_replace('no','nb',$toreplace);
            if ($toreplace2!=$toreplace) {modifyDatabase("","update [prefix_surveys] set [additional_languages]='$toreplace' where sid=".$datarow['sid']);echo $modifyoutput;flush();ob_flush();}
        }

        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD [htmlemail] CHAR(1) DEFAULT 'N'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD [usecaptcha] CHAR(1) DEFAULT 'N'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD [tokenanswerspersistence] CHAR(1) DEFAULT 'N'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE [prefix_users] ADD [htmleditormode] CHAR(7) DEFAULT 'default'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","CREATE TABLE [prefix_templates_rights] (
              [uid] int NOT NULL,
              [folder] varchar(255) NOT NULL,
              [use] int NOT NULL,
              PRIMARY KEY  ([uid],[folder])
              );");echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","CREATE TABLE [prefix_templates] (
              [folder] varchar(255) NOT NULL,
              [creator] int NOT NULL,
              PRIMARY KEY  ([folder])
              );");echo $modifyoutput; flush();ob_flush();
        //123
        modifyDatabase("","ALTER TABLE [prefix_conditions] ALTER COLUMN [value] VARCHAR(255)"); echo $modifyoutput; flush();ob_flush();
        mssql_drop_constraint('title','labels');
        modifyDatabase("","ALTER TABLE [prefix_labels] ALTER COLUMN [title] varchar(4000)"); echo $modifyoutput; flush();ob_flush();
        //124
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD [bounce_email] text"); echo $modifyoutput; flush();ob_flush();
        //125
        upgrade_token_tables125();
        modifyDatabase("","EXEC sp_rename 'prefix_users.move_user','superadmin'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","UPDATE [prefix_users] SET [superadmin]=1 where ([create_survey]=1 AND [create_user]=1 AND [delete_user]=1 AND [configurator]=1)"); echo $modifyoutput; flush();ob_flush();
        //126
        modifyDatabase("","ALTER TABLE [prefix_questions] ADD [lid1] int NOT NULL DEFAULT '0'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","UPDATE [prefix_conditions] SET [method]='==' where ( [method] is null) or [method]='' or [method]='0'"); echo $modifyoutput; flush();ob_flush();

        modifyDatabase("","update [prefix_settings_global] set [stg_value]='126' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 127) {
        modifyDatabase("","create index [answers_idx2] on [prefix_answers] ([sortorder])"); echo $modifyoutput;
        modifyDatabase("","create index [assessments_idx2] on [prefix_assessments] ([sid])"); echo $modifyoutput;
        modifyDatabase("","create index [assessments_idx3] on [prefix_assessments] ([gid])"); echo $modifyoutput;
        modifyDatabase("","create index [conditions_idx2] on [prefix_conditions] ([qid])"); echo $modifyoutput;
        modifyDatabase("","create index [conditions_idx3] on [prefix_conditions] ([cqid])"); echo $modifyoutput;
        modifyDatabase("","create index [groups_idx2] on [prefix_groups] ([sid])"); echo $modifyoutput;
        modifyDatabase("","create index [question_attributes_idx2] on [prefix_question_attributes] ([qid])"); echo $modifyoutput;
        modifyDatabase("","create index [questions_idx2] on [prefix_questions] ([sid])"); echo $modifyoutput;
        modifyDatabase("","create index [questions_idx3] on [prefix_questions] ([gid])"); echo $modifyoutput;
        modifyDatabase("","create index [questions_idx4] on [prefix_questions] ([type])"); echo $modifyoutput;
        modifyDatabase("","create index [quota_idx2] on [prefix_quota] ([sid])"); echo $modifyoutput;
        modifyDatabase("","create index [saved_control_idx2] on [prefix_saved_control] ([sid])"); echo $modifyoutput;
        modifyDatabase("","create index [user_in_groups_idx1] on [prefix_user_in_groups] ([ugid], [uid])"); echo $modifyoutput;
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='127' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 128) {
        upgrade_token_tables128();
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='128' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 129) {
        //128
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD [startdate] DATETIME"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD [usestartdate] char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='129' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 130)
    {
        modifyDatabase("","ALTER TABLE [prefix_conditions] ADD [scenario] int NOT NULL DEFAULT '1'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","UPDATE [prefix_conditions] SET [scenario]=1 where ( [scenario] is null) or [scenario]='' or [scenario]=0"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='130' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 131)
    {
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD [publicstatistics] char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='131' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 132)
    {
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD [publicgraphs] char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='132' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 133)
    {
        modifyDatabase("","ALTER TABLE [prefix_users] ADD [one_time_pw] text"); echo $modifyoutput; flush();ob_flush();
        // Add new assessment setting
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD [assessments] char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        // add new assessment value fields to answers & labels
        modifyDatabase("","ALTER TABLE [prefix_answers] ADD [assessment_value] int NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE [prefix_labels] ADD [assessment_value] int NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        // copy any valid codes from code field to assessment field
        modifyDatabase("","update [prefix_answers] set [assessment_value]=CAST([code] as int)");// no output here is intended
        modifyDatabase("","update [prefix_labels] set [assessment_value]=CAST([code] as int)");// no output here is intended
        // activate assessment where assesment rules exist
        modifyDatabase("","update [prefix_surveys] set [assessments]='Y' where [sid] in (SELECT [sid] FROM [prefix_assessments] group by [sid])"); echo $modifyoutput; flush();ob_flush();
        // add language field to assessment table
        modifyDatabase("","ALTER TABLE [prefix_assessments] ADD [language] varchar(20) NOT NULL default 'en'"); echo $modifyoutput; flush();ob_flush();
        // update language field with default language of that particular survey
        modifyDatabase("","update [prefix_assessments] set [language]=(select [language] from [prefix_surveys] where [sid]=[prefix_assessments].[sid])"); echo $modifyoutput; flush();ob_flush();
        // copy assessment link to message since from now on we will have HTML assignment messages
        modifyDatabase("","update [prefix_assessments] set [message]=cast([message] as varchar) +'<br /><a href=\"'+[link]+'\">'+[link]+'</a>'"); echo $modifyoutput; flush();ob_flush();
        // drop the old link field
        modifyDatabase("","ALTER TABLE [prefix_assessments] DROP COLUMN [link]"); echo $modifyoutput; flush();ob_flush();
        // change the primary index to include language
        // and fix missing translations for assessments
        upgrade_survey_tables133a();

        // Add new fields to survey language settings
        modifyDatabase("","ALTER TABLE [prefix_surveys_languagesettings] ADD [surveyls_url] varchar(255)"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE [prefix_surveys_languagesettings] ADD [surveyls_endtext] text"); echo $modifyoutput; flush();ob_flush();
        // copy old URL fields ot language specific entries
        modifyDatabase("","update [prefix_surveys_languagesettings] set [surveyls_url]=(select [url] from [prefix_surveys] where [sid]=[prefix_surveys_languagesettings].[surveyls_survey_id])"); echo $modifyoutput; flush();ob_flush();
        // drop old URL field
        mssql_drop_constraint('url','surveys');
        modifyDatabase("","ALTER TABLE [prefix_surveys] DROP COLUMN [url]"); echo $modifyoutput; flush();ob_flush();

        modifyDatabase("","update [prefix_settings_global] set [stg_value]='133' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 134)
    {
        // Add new assessment setting
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD [usetokens] char(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        mssql_drop_constraint('attribute1','surveys');
        mssql_drop_constraint('attribute2','surveys');
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD [attributedescriptions] varchar(max);"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE [prefix_surveys] DROP COLUMN [attribute1]"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE [prefix_surveys] DROP COLUMN [attribute2]"); echo $modifyoutput; flush();ob_flush();
        upgrade_token_tables134();
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='134' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 135)
    {
        mssql_drop_constraint('value','question_attributes');
        modifyDatabase("","ALTER TABLE [prefix_question_attributes] ALTER COLUMN [value] text"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE [prefix_answers] ALTER COLUMN [answer] varchar(8000)"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='135' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 136) //New quota functions
    {
        modifyDatabase("", "ALTER TABLE[prefix_quota] ADD [autoload_url] int NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","CREATE TABLE [prefix_quota_languagesettings] (
                [quotals_id] int NOT NULL IDENTITY (1,1),
              [quotals_quota_id] int,
                [quotals_language] varchar(45) NOT NULL default 'en',
                [quotals_name] varchar(255),
                [quotals_message] text,
                [quotals_url] varchar(255),
                [quotals_urldescrip] varchar(255),
                PRIMARY KEY ([quotals_id])
              );");echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='136' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 137) //New date format specs
    {
        modifyDatabase("", "ALTER TABLE [prefix_surveys_languagesettings] ADD [surveyls_dateformat] int NOT NULL default '1'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_users] ADD [dateformat] int NOT NULL default '1'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "update [prefix_surveys] set startdate=null where usestartdate='N'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "update [prefix_surveys] set expires=null where useexpiry='N'"); echo $modifyoutput; flush();ob_flush();
        mssql_drop_constraint('usestartdate','surveys');
        mssql_drop_constraint('useexpiry','surveys');
        modifyDatabase("", "ALTER TABLE [prefix_surveys] DROP COLUMN usestartdate"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] DROP COLUMN useexpiry"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='137' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 138) //Modify quota field
    {
        modifyDatabase("", "ALTER TABLE [prefix_quota_members] ALTER COLUMN [code] VARCHAR(11) NULL"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "UPDATE [prefix_settings_global] SET [stg_value]='138' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 139) //Modify quota field
    {
        upgrade_survey_tables139();
        modifyDatabase("", "UPDATE [prefix_settings_global] SET [stg_value]='139' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 140) //Modify surveys table
    {
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD [emailresponseto] text"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "UPDATE [prefix_settings_global] SET [stg_value]='140' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 141) //Modify surveys table
    {
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD [tokenlength] tinyint NOT NULL default '15'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "UPDATE [prefix_settings_global] SET [stg_value]='141' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 142) //Modify surveys table
    {
        upgrade_question_attributes142();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ALTER COLUMN [startdate] datetime NULL"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ALTER COLUMN [expires] datetime NULL"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "UPDATE [prefix_question_attributes] SET [value]='0' WHERE cast([value] as varchar)='false'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "UPDATE [prefix_question_attributes] SET [value]='1' WHERE cast([value] as varchar)='true'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "UPDATE [prefix_settings_global] SET [stg_value]='142' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 143) //Modify surveys table
    {
        modifyDatabase("", "ALTER TABLE [prefix_questions] ADD parent_qid integer NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_answers] ADD scale_id tinyint NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_questions] ADD scale_id tinyint NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_questions] ADD same_default tinyint NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        mssql_drop_primary_index('answers');
        modifyDatabase("","ALTER TABLE [prefix_answers] ADD CONSTRAINT pk_answers_qcls PRIMARY KEY ([qid],[code],[language],[scale_id])"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "CREATE TABLE [prefix_defaultvalues] (
                              [qid] integer NOT NULL default '0',
                              [scale_id] tinyint NOT NULL default '0',
                              [sqid] integer NOT NULL default '0',
                              [language] varchar(20) NOT NULL,
                              [specialtype] varchar(20) NOT NULL default '',
                              [defaultvalue] text,
                              CONSTRAINT pk_defaultvalues_qlss PRIMARY KEY ([qid] , [scale_id], [language], [specialtype], [sqid]))"); echo $modifyoutput; flush();ob_flush();

        // -Move all 'answers' that are subquestions to the questions table
        // -Move all 'labels' that are answers to the answers table
        // -Transscribe the default values where applicable
        // -Move default values from answers to questions
        upgrade_tables143();

        mssql_drop_constraint('default_value','answers');
        modifyDatabase("", "ALTER TABLE [prefix_answers] DROP COLUMN [default_value]"); echo $modifyoutput; flush();ob_flush();
        mssql_drop_constraint('lid','questions');
        modifyDatabase("", "ALTER TABLE [prefix_questions] DROP COLUMN lid"); echo $modifyoutput; flush();ob_flush();
        mssql_drop_constraint('lid1','questions');
        modifyDatabase("", "ALTER TABLE [prefix_questions] DROP COLUMN lid1"); echo $modifyoutput; flush();ob_flush();
        // add field for timings and table for extended conditions
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD savetimings char(1) default 'N'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "CREATE TABLE prefix_sessions(
                              sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
                              expiry DATETIME NOT NULL ,
                              expireref VARCHAR( 250 ) DEFAULT '',
                              created DATETIME NOT NULL ,
                              modified DATETIME NOT NULL ,
                              sessdata text,
                              CONSTRAINT pk_sessions_sesskey PRIMARY KEY ( [sesskey] ))"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "create index [idx_expiry] on [prefix_sessions] ([expiry])"); echo $modifyoutput;
        modifyDatabase("", "create index [idx_expireref] on [prefix_sessions] ([expireref])"); echo $modifyoutput;
        modifyDatabase("", "UPDATE [prefix_settings_global] SET stg_value='143' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();






    }

    if ($oldversion < 145) //Modify surveys table
    {
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD showxquestions CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD showgroupinfo CHAR(1) NULL default 'B'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD shownoanswer CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD showqnumcode CHAR(1) NULL default 'X'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD bouncetime BIGINT NULL"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD bounceprocessing VARCHAR(1) NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD bounceaccounttype VARCHAR(4) NULL"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD bounceaccounthost VARCHAR(200) NULL "); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD bounceaccountpass VARCHAR(100) NULL"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD bounceaccountencryption VARCHAR(3) NULL"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD bounceaccountuser VARCHAR(200) NULL"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD showwelcome CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD showprogress CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD allowjumps CHAR(1) NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD navigationdelay tinyint NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD nokeyboard CHAR(1) NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_surveys] ADD alloweditaftercompletion CHAR(1) NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "CREATE TABLE [prefix_survey_permissions] (
                            [sid] INT NOT NULL,
                            [uid] INT NOT NULL,
                            [permission] VARCHAR(20) NOT NULL,
                            [create_p] TINYINT NOT NULL default '0',
                            [read_p] TINYINT NOT NULL default '0',
                            [update_p] TINYINT NOT NULL default '0',
                            [delete_p] TINYINT NOT NULL default '0',
                            [import_p] TINYINT NOT NULL default '0',
                            [export_p] TINYINT NOT NULL default '0',
                            PRIMARY KEY ([sid], [uid],[permission])
                        );"); echo $modifyoutput; flush();ob_flush();
    upgrade_surveypermissions_table145();
        modifyDatabase("", "DROP TABLE [prefix_surveys_rights]"); echo $modifyoutput; flush();ob_flush();

        // Add new fields for email templates
        modifyDatabase("", "ALTER TABLE prefix_surveys_languagesettings ADD
                              email_admin_notification_subj  VARCHAR(255) NULL,
                              email_admin_notification varchar(max) NULL,
                              email_admin_responses_subj VARCHAR(255) NULL,
                              email_admin_responses varchar(max) NULL");

        //Add index to questions table to speed up subquestions
        modifyDatabase("", "create index [parent_qid_idx] on [prefix_questions] ([parent_qid])"); echo $modifyoutput; flush();ob_flush();

        modifyDatabase("", "ALTER TABLE prefix_surveys ADD emailnotificationto text DEFAULT NULL"); echo $modifyoutput; flush();ob_flush();
        upgrade_survey_table145();
        mssql_drop_constraint('notification','surveys');
        modifyDatabase("", "ALTER TABLE [prefix_surveys] DROP COLUMN [notification]"); echo $modifyoutput; flush();ob_flush();

        // modify length of method in conditions
        modifyDatabase("","ALTER TABLE [prefix_conditions] ALTER COLUMN [method] CHAR( 5 ) NOT NULL"); echo $modifyoutput; flush();ob_flush();

        //Add index to questions table to speed up subquestions
        modifyDatabase("", "create index [parent_qid] on [prefix_questions] ([parent_qid])"); echo $modifyoutput; flush();ob_flush();

        modifyDatabase("","UPDATE prefix_surveys set [private]='N' where [private] is NULL;"); echo $modifyoutput; flush();ob_flush();

        modifyDatabase("","EXEC sp_rename 'prefix_surveys.private','anonymized'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE [prefix_surveys] ALTER COLUMN [anonymized] char(1) NOT NULL;"); echo $modifyoutput; flush();ob_flush();
        mssql_drop_constraint('anonymized','surveys');
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD CONSTRAINT DF_surveys_anonymized DEFAULT 'N' FOR [anonymized];"); echo $modifyoutput; flush();ob_flush();

        modifyDatabase("", "CREATE TABLE [prefix_failed_login_attempts] (
                              [id] INT NOT NULL IDENTITY (1,1) PRIMARY KEY,
                              [ip] varchar(37) NOT NULL,
                              [last_attempt] varchar(20) NOT NULL,
                              [number_attempts] int NOT NULL );"); echo $modifyoutput; flush();ob_flush();

        modifyDatabase("", "ALTER TABLE  [prefix_surveys_languagesettings] ADD  [surveyls_numberformat] INT default 0 NOT NULL"); echo $modifyoutput; flush();ob_flush();

        upgrade_token_tables145();
        modifyDatabase("", "UPDATE [prefix_settings_global] SET stg_value='145' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();


    }
    if ($oldversion < 146) //Modify surveys table
    {
        upgrade_timing_tables146();
        modifyDatabase("", "UPDATE [prefix_settings_global] SET stg_value='146' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 147)
    {
        modifyDatabase("", "ALTER TABLE [prefix_users] ADD templateeditormode VARCHAR(7) NOT NULL default 'default'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_users] ADD questionselectormode VARCHAR(7) NOT NULL default 'default'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "UPDATE [prefix_settings_global] SET stg_value='147' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 148)
    {
        modifyDatabase("","CREATE TABLE [prefix_participants] (
            [participant_id] varchar(50) NOT NULL,
            [firstname] varchar(40) NOT NULL,
            [lastname] varchar(40) NOT NULL,
            [email] varchar(80) NOT NULL,
            [language] varchar(2) NOT NULL,
            [blacklisted] varchar(1) NOT NULL,
            [owner_uid] int(20) NOT NULL,
            PRIMARY KEY  ([participant_id])
            );");echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","CREATE TABLE [prefix_participant_attribute] (
            [participant_id] varchar(50) NOT NULL,
            [attribute_id] int(11) NOT NULL,
            [value] varchar(50) NOT NULL,
            PRIMARY KEY  ([participant_id],[attribute_id])
            );");echo $modifyoutput; flush();ob_flush();
       modifyDatabase("","CREATE TABLE [prefix_participant_attribute_names] (
            [attribute_id] int(11) NOT NULL AUTO_INCREMENT,
            [attribute_type] varchar(4) NOT NULL,
            [visible] char(5) NOT NULL,
            PRIMARY KEY  ([attribute_id],[attribute_type])
            );");echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","CREATE TABLE [prefix_participant_attribute_names_lang] (
            [attribute_id] int(11) NOT NULL,
            [attribute_name] varchar(30) NOT NULL,
            [lang] varchar(20) NOT NULL,
            PRIMARY KEY  ([attribute_id],[lang])
            );");echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","CREATE TABLE [prefix_participant_attribute_values] (
            [attribute_id] int(11) NOT NULL,
            [value_id] int(11) NOT NULL AUTO_INCREMENT,
            [value] varchar(20) NOT NULL,
            PRIMARY KEY  ([value_id])
            );");echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","CREATE TABLE [prefix_participant_shares] (
            [participant_id] varchar(50) NOT NULL,
            [share_uid] int(11) NOT NULL,
            [date_added] datetime,
            [can_edit] text NOT NULL,
            PRIMARY KEY  ([participant_id],[share_uid])
            );");echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","CREATE TABLE [prefix_survey_links] (
            [participant_id] varchar(50) NOT NULL,
            [token_id] int(11) NOT NULL,
            [survey_id] int(11) NOT NULL,
            [date_created] datetime,
            PRIMARY KEY  ([participant_id],[token_id],[survey_id])
            );");echo $modifyoutput; flush();ob_flush();
        modifyDatabase("", "ALTER TABLE [prefix_users] ADD [participant_panel] int NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();

        // Add language field to question_attributes table
        modifyDatabase("","ALTER TABLE [prefix_question_attributes] ADD [language] varchar(20)"); echo $modifyoutput; flush();ob_flush();
        upgrade_question_attributes148();
        fixSubquestions();
        modifyDatabase("", "UPDATE [prefix_settings_global] SET stg_value='148' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 153)
    {
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD [sendconfirmation] CHAR(1) DEFAULT 'Y'"); echo $modifyoutput; flush();ob_flush();
    upgrade_survey_table152();
    }
    if ($oldversion < 154)
    {
        modifyDatabase("","ALTER TABLE [prefix_groups] ADD [grelevance] text DEFAULT NULL;"); echo $modifyoutput; flush();@ob_flush();
        LimeExpressionManager::UpgradeConditionsToRelevance();
        modifyDatabase("","update [prefix_settings_global] set [stg_value]='154' where stg_name='DBVersion'"); echo $modifyoutput; flush();@ob_flush();
    }
    if ($oldversion < 155)
    {
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD [googleanalyticsstyle] char(1) DEFAULT NULL;"); echo $modifyoutput; flush();@ob_flush();
        modifyDatabase("","ALTER TABLE [prefix_surveys] ADD [googleanalyticsapikey] varchar(25) DEFAULT NULL;"); echo $modifyoutput; flush();@ob_flush();
        modifyDatabase("","EXEC sp_rename 'prefix_surveys.showXquestions','showxquestions'"); echo $modifyoutput; flush();@ob_flush();
        modifyDatabase("", "UPDATE [prefix_settings_global] SET [stg_value]='155' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    echo '<br /><br />'.sprintf($clang->gT('Database update finished (%s)'),date('Y-m-d H:i:s')).'<br /><br />';
    return true;
}

function upgrade_survey_tables117()
{
    global $modifyoutput;
    $surveyidquery = "SELECT sid FROM {{surveys}} WHERE active='Y' and datestamp='Y'";
    $surveyidresult = Yii::app()->db->createCommand($surveyidquery)->queryAll();
    if (empty($surveyidresult)) {return "Database Error";}
    else
    {
        foreach ( $surveyidresult as $sv )
        {
            modifyDatabase("","ALTER TABLE ".db_table_name('survey_'.$sv[0])." ADD [startdate] datetime"); echo $modifyoutput; flush();ob_flush();
        }
    }
}


function upgrade_survey_tables118()
{
    global $modifyoutput;
    $tokentables = dbGetTablesLike('tokens');
    foreach ($tokentables as $sv)
    {
        modifyDatabase("","ALTER TABLE ".$sv." ALTER COLUMN [token] VARCHAR(36)"); echo $modifyoutput; flush();ob_flush();
    }
}


function upgrade_token_tables125()
{
    global $modifyoutput;
    $tokentables = dbGetTablesLike('tokens');
    foreach ($tokentables as $sv)
    {
        modifyDatabase("","ALTER TABLE ".$sv." ADD [emailstatus] VARCHAR(300) DEFAULT 'OK'"); echo $modifyoutput; flush();ob_flush();
    }
}


function upgrade_token_tables128()
{
    global $modifyoutput;
    $tokentables = dbGetTablesLike('tokens');
    foreach ($tokentables as $sv)
    {
        modifyDatabase("","ALTER TABLE ".$sv." ADD [remindersent] VARCHAR(17) DEFAULT 'OK'"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE ".$sv." ADD [remindercount] int DEFAULT '0'"); echo $modifyoutput; flush();ob_flush();
    }
}


function upgrade_survey_tables133a()
{
    global $modifyoutput;
    mssql_drop_primary_index('assessments');
    // add the new primary key
    modifyDatabase("","ALTER TABLE [prefix_assessments] ADD CONSTRAINT pk_assessments_id_lang PRIMARY KEY ([id],[language])"); echo $modifyoutput; flush();ob_flush();
    $surveyidquery = "SELECT sid,additional_languages FROM ".db_table_name('surveys');
    $surveyidresult = db_execute_num($surveyidquery);
    while ( $sv = $surveyidresult->FetchRow() )
    {
        fixLanguageConsistency($sv[0],$sv[1]);
    }
}


function upgrade_token_tables134()
{
    global $modifyoutput;
    $tokentables = dbGetTablesLike('tokens');
    foreach ($tokentables as $sv)
    {
        modifyDatabase("","ALTER TABLE ".$sv." ADD [validfrom] DATETIME"); echo $modifyoutput; flush();ob_flush();
        modifyDatabase("","ALTER TABLE ".$sv." ADD [validuntil] DATETIME"); echo $modifyoutput; flush();ob_flush();
    }
}

// Add the usesleft field to all existing token tables
function upgrade_token_tables145()
{
    global $modifyoutput;
    $tokentables = dbGetTablesLike('tokens');
    foreach ($tokentables as $sv) {
            modifyDatabase("","ALTER TABLE ".$sv." ADD [usesleft] int NOT NULL DEFAULT '1'"); echo $modifyoutput; flush();ob_flush();
            modifyDatabase("","UPDATE ".$sv." SET usesleft=0 WHERE completed<>'N'"); echo $modifyoutput; flush();ob_flush();
    }
}


function mssql_drop_primary_index($tablename)
{
     global $modifyoutput;
    // find out the constraint name of the old primary key
    $pkquery = "SELECT CONSTRAINT_NAME "
              ."FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS "
              ."WHERE     (TABLE_NAME = '{{{$tablename}}}') AND (CONSTRAINT_TYPE = 'PRIMARY KEY')";

    $primarykey = Yii::app()->db->createCommand($pkquery)->query();
    if ($primarykey!=false)
    {
        modifyDatabase("","ALTER TABLE [prefix_{$tablename}] DROP CONSTRAINT {$primarykey}"); echo $modifyoutput; flush();ob_flush();
    }
}


function mssql_drop_constraint($fieldname, $tablename)
{
    global $modifyoutput;

    // find out the name of the default constraint
    // Did I already mention that this is the most suckiest thing I have ever seen in MSSQL database?
    $dfquery ="SELECT c_obj.name AS constraint_name
                FROM  sys.sysobjects AS c_obj INNER JOIN
                      sys.sysobjects AS t_obj ON c_obj.parent_obj = t_obj.id INNER JOIN
                      sys.sysconstraints AS con ON c_obj.id = con.constid INNER JOIN
                      sys.syscolumns AS col ON t_obj.id = col.id AND con.colid = col.colid
                WHERE (c_obj.xtype = 'D') AND (col.name = '$fieldname') AND (t_obj.name='{{{$tablename}}}')";
    $defaultname = Yii::app()->db->createCommand($dfquery)->query();
    if ($defaultname!=false)
    {
        modifyDatabase("","ALTER TABLE [prefix_$tablename] DROP CONSTRAINT {$defaultname['constraint_name']}"); echo $modifyoutput; flush();ob_flush();
    }

}

function upgrade_survey_tables139()
{
    global $modifyoutput;
    $surveyidresult = dbGetTablesLike("survey\_%");
    if (empty($surveyidresult)) {return "Database Error";}
    else
    {
        foreach ( $surveyidresult as $sv )
        {
            modifyDatabase("","ALTER TABLE ".$sv[0]." ADD [lastpage] int"); echo $modifyoutput; flush();ob_flush();
        }
    }
}

function upgrade_question_attributes142()
{
    global $modifyoutput;
    $attributequery="Select qid from {{question_attributes}} where attribute='exclude_all_other'  group by qid having count(qid)>1 ";
    $questionids = Yii::app()->db->createCommand($attributequery)->queryRow();
    foreach ($questionids as $questionid)
    {
        //Select all affected question attributes
        $attributevalues=dbSelectColumn("SELECT value from {{question_attributes}} where attribute='exclude_all_other' and qid=".$questionid);
        modifyDatabase("","delete from {{question_attributes}} where attribute='exclude_all_other' and qid=".$questionid); echo $modifyoutput; flush();ob_flush();
        $record['value']=implode(';',$attributevalues);
        $record['attribute']='exclude_all_other';
        $record['qid']=$questionid;
        Yii::app()->db->createCommand()->insert('{{question_attributes}}', $record)->execute();
    }
}

function upgrade_tables143()
{
    global $modifyoutput;

    $aQIDReplacements=array();
    $answerquery = "select a.*, q.sid, q.gid from {{answers}} a,{{questions}} q where a.qid=q.qid and q.type in ('L','O','!') and a.default_value='Y'";
    $answerresult = Yii::app()->db->createCommand($answerquery)->queryAll();
    if (empty($answerresult)) {return "Database Error";}
    else
    {
        foreach ( $answerresult as $row )
        {
            modifyDatabase("","INSERT INTO {{defaultvalues}} (qid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},0,".dbQuoteAll($row['language']).",'',".dbQuoteAll($row['code']).")"); echo $modifyoutput; flush();ob_flush();
        }
    }

    // Convert answers to subquestions

    $answerquery = "select a.*, q.sid, q.gid, q.type from {{answers}} a,{{questions}} q where a.qid=q.qid and a.language=q.language and q.type in ('1','A','B','C','E','F','H','K',';',':','M','P','Q')";
    $answerresult = Yii::app()->db->createCommand($answerquery)->queryAll();
    if (empty($answerresult)) {return "Database Error";}
    else
    {
        foreach ( $answerresult as $row )
        {

            $aInsert=array();
            if (isset($aQIDReplacements[$row['qid'].'_'.$row['code']]))
            {
                $aInsert['qid']=$aQIDReplacements[$row['qid'].'_'.$row['code']];
                switchMSSQLIdentityInsert('questions',true);
            }
            $aInsert['sid']=$row['sid'];
            $aInsert['gid']=$row['gid'];
            $aInsert['parent_qid']=$row['qid'];
            $aInsert['type']=$row['type'];
            $aInsert['title']=$row['code'];
            $aInsert['question']=$row['answer'];
            $aInsert['question_order']=$row['sortorder'];
            $aInsert['language']=$row['language'];
            $tablename="{{questions}}";
            $query=Yii::app()->db->createCommand()->insert($tablename, $aInsert)->getText();
            modifyDatabase("",$query); echo $modifyoutput; flush();ob_flush();
            if (!isset($aInsert['qid']))
            {
               $aQIDReplacements[$row['qid'].'_'.$row['code']]=Yii::app()->db->getLastInsertID();
               $iSaveSQID=$aQIDReplacements[$row['qid'].'_'.$row['code']];
            }
            else
            {
               $iSaveSQID=$aInsert['qid'];
                switchMSSQLIdentityInsert('questions',false);
            }
            if (($row['type']=='M' || $row['type']=='P') && $row['default_value']=='Y')
            {
                modifyDatabase("","INSERT INTO {{defaultvalues}} (qid, sqid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},{$iSaveSQID},0,".dbQuoteAll($row['language']).",'','Y')"); echo $modifyoutput; flush();ob_flush();
            }
        }
    }
    modifyDatabase("","delete {{answers}} from {{answers}} LEFT join {{questions}} ON {{answers}}.qid={{questions}}.qid where {{questions}}.type in ('1','F','H','M','P','W','Z')"); echo $modifyoutput; flush();ob_flush();

    // Convert labels to answers
    $answerquery = "select qid ,type ,lid ,lid1, language from {{questions}} where parent_qid=0 and type in ('1','F','H','M','P','W','Z')";
    $answerresult = Yii::app()->db->createCommand($answerquery)->queryAll();
    if (empty($answerresult))
    {
        return "Database Error";
    }
    else
    {
        foreach ( $answerresult as $row )
        {
            $labelquery="Select * from {{labels}} where lid={$row['lid']} and language=".dbQuoteAll($row['language']);
            $labelresult = Yii::app()->db->createCommand($labelquery)->queryAll();
            foreach ( $labelresult as $lrow )
            {
                modifyDatabase("","INSERT INTO {{answers}} (qid, code, answer, sortorder, language, assessment_value) VALUES ({$row['qid']},".dbQuoteAll($lrow['code']).",".dbQuoteAll($lrow['title']).",{$lrow['sortorder']},".dbQuoteAll($lrow['language']).",{$lrow['assessment_value']})"); echo $modifyoutput; flush();ob_flush();
                //$labelids[]
            }
            if ($row['type']=='1')
            {
                $labelquery="Select * from {{labels}} where lid={$row['lid1']} and language=".dbQuoteAll($row['language']);
                $labelresult = Yii::app()->db->createCommand($labelquery)->queryAll();
                foreach ( $labelresult as $lrow )
                {
                    modifyDatabase("","INSERT INTO {{answers}} (qid, code, answer, sortorder, language, scale_id, assessment_value) VALUES ({$row['qid']},".dbQuoteAll($lrow['code']).",".dbQuoteAll($lrow['title']).",{$lrow['sortorder']},".dbQuoteAll($lrow['language']).",1,{$lrow['assessment_value']})"); echo $modifyoutput; flush();ob_flush();
                }
            }
        }
    }

    // Convert labels to subquestions
    $answerquery = "select * from {{questions}} where parent_qid=0 and type in (';',':')";
    $answerresult = Yii::app()->db->createCommand($answerquery)->queryAll();
    if (empty($answerresult))
    {
        return "Database Error";
    }
    else
    {
        foreach ( $answerresult as $row )
        {
            $labelquery="Select * from {{labels}} where lid={$row['lid']} and language=".dbQuoteAll($row['language']);
            $labelresult = Yii::app()->db->createCommand($labelquery)->queryAll();
            foreach ( $labelresult as $lrow )
            {
                $aInsert=array();
                if (isset($aQIDReplacements[$row['qid'].'_'.$lrow['code'].'_1']))
                {
                    $aInsert['qid']=$aQIDReplacements[$row['qid'].'_'.$lrow['code'].'_1'];
                    switchMSSQLIdentityInsert('questions',true);

                }
                $aInsert['sid']=$row['sid'];
                $aInsert['gid']=$row['gid'];
                $aInsert['type']=$row['type'];
                $aInsert['parent_qid']=$row['qid'];
                $aInsert['title']=$lrow['code'];
                $aInsert['question']=$lrow['title'];
                $aInsert['question_order']=$lrow['sortorder'];
                $aInsert['language']=$lrow['language'];
                $aInsert['scale_id']=1;
                $tablename="{{questions}}";
                $query=Yii::app()->db->createCommand()->insert($tablename,$aInsert)->getText();
                modifyDatabase("",$query); echo $modifyoutput; flush();ob_flush();
                if (isset($aInsert['qid']))
                {
                   $aQIDReplacements[$row['qid'].'_'.$lrow['code'].'_1']=Yii::app()->db->getLastInsertId();
                   switchMSSQLIdentityInsert('questions',false);

                }
            }
        }
    }


    $updatequery = "update {{questions}} set type='!' where type='W'";
    modifyDatabase("",$updatequery); echo $modifyoutput; flush();ob_flush();
    $updatequery = "update {{questions}} set type='L' where type='Z'";
    modifyDatabase("",$updatequery); echo $modifyoutput; flush();ob_flush();

    // Now move all non-standard templates to the /upload dir
    global $usertemplaterootdir, $standardtemplates,$standardtemplaterootdir;

    if (!$usertemplaterootdir) {die("getTemplateList() no template directory");}
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
    global $modifyoutput;
    $aTimingTables = dbGetTablesLike("%timings");
    foreach ($aTimingTables as $sTable) {
        modifyDatabase("","EXEC sp_rename '{$sTable}.interviewTime','interviewtime'"); echo $modifyoutput; flush(); ob_flush();
    }
}
