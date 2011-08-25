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
 * $Id: upgrade-mysql.php 7108 2009-06-15 05:43:21Z jcleeland $
 */

// There will be a file for each database (accordingly named to the dbADO scheme)
// where based on the current database version the database is upgraded
// For this there will be a settings table which holds the last time the database was upgraded

function db_upgrade($oldversion) {
    /// This function does anything necessary to upgrade
    /// older versions to match current functionality
    global $modifyoutput, $databasename, $databasetabletype, $connect, $clang;
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
            modify_database("","update `prefix_answers` set `language`='$newlang' where language='$oldlang'");  echo $modifyoutput;      flush();ob_flush();
            modify_database("","update `prefix_questions` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modify_database("","update `prefix_groups` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modify_database("","update `prefix_labels` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modify_database("","update `prefix_surveys` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modify_database("","update `prefix_surveys_languagesettings` set `surveyls_language`='$newlang' where surveyls_language='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modify_database("","update `prefix_users` set `lang`='$newlang' where lang='$oldlang'");echo $modifyoutput;flush();ob_flush();
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
            modify_database("","update  `prefix_labelsets` set `languages`='$toreplace' where lid=".$datarow['lid']);echo $modifyoutput;flush();ob_flush();
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
            modify_database("","update  `prefix_surveys` set `additional_languages`='$toreplace' where sid=".$datarow['sid']);echo $modifyoutput;flush();ob_flush();
        }
        modify_database("","update `prefix_settings_global` set `stg_value`='111' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();

    }


    if ($oldversion < 112) {
        //The size of the users_name field is now 64 char (20 char before version 112)
        modify_database("","ALTER TABLE `prefix_users` CHANGE `users_name` `users_name` VARCHAR( 64 ) NOT NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='112' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 113) {
        //Fixes the collation for the complete DB, tables and columns
        echo "<strong>Attention:</strong>The following upgrades will update your MySQL Database collations. This may take some time.<br />If for any reason you should get a timeout just re-run the upgrade procedure. The updating will continue where it left off.<br /><br />"; flush();ob_flush();
        fix_mysql_collation();
        modify_database("","ALTER DATABASE `$databasename` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;");echo $modifyoutput; flush();ob_flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='113' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 114) {
        modify_database("","ALTER TABLE `prefix_saved_control` CHANGE `email` `email` VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `adminemail` `adminemail` VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_users` CHANGE `email` `email` VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("",'INSERT INTO `prefix_settings_global` VALUES (\'SessionName\', \'$sessionname\');');echo $modifyoutput; flush();ob_flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='114' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 126) {
        //Adds new "public" field
        modify_database("","ALTER TABLE `prefix_surveys` ADD `printanswers` CHAR(1) default 'N' AFTER allowsave"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` ADD `listpublic` CHAR(1) default 'N' AFTER `datecreated`"); echo $modifyoutput; flush();ob_flush();
        upgrade_survey_tables117();
        upgrade_survey_tables118();
        // 119
        modify_database("","CREATE TABLE `prefix_quota` (
 				            `id` int(11) NOT NULL auto_increment,
  							`sid` int(11) default NULL,
  							`qlimit` int(8) default NULL,
  							`name` varchar(255) collate utf8_unicode_ci default NULL,
  							`action` int(2) default NULL,
  							`active` int(1) NOT NULL default '1',
  							PRIMARY KEY  (`id`)
							)   CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","CREATE TABLE `prefix_quota_members` (
   		 				   `id` int(11) NOT NULL auto_increment,
						   `sid` int(11) default NULL,
  						   `qid` int(11) default NULL,
  						   `quota_id` int(11) default NULL,
  						   `code` varchar(5) collate utf8_unicode_ci default NULL,
  						   PRIMARY KEY  (`id`),
  						   UNIQUE KEY `sid` (`sid`,`qid`,`quota_id`,`code`)
						   )    CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();

        // Rename Norwegian language code from NO to NB
        $oldnewlanguages=array('no'=>'nb');
        foreach  ($oldnewlanguages as $oldlang=>$newlang)
        {
            modify_database("","update `prefix_answers` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modify_database("","update `prefix_questions` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modify_database("","update `prefix_groups` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modify_database("","update `prefix_labels` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modify_database("","update `prefix_surveys` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modify_database("","update `prefix_surveys_languagesettings` set `surveyls_language`='$newlang' where surveyls_language='$oldlang'");echo $modifyoutput;flush();ob_flush();
            modify_database("","update `prefix_users` set `lang`='$newlang' where lang='$oldlang'");echo $modifyoutput;flush();ob_flush();
        }

        $resultdata=db_execute_assoc("select * from ".db_table_name("labelsets"));
        while ($datarow = $resultdata->FetchRow()){
            $toreplace=$datarow['languages'];
            $toreplace2=str_replace('no','nb',$toreplace);
            if ($toreplace2!=$toreplace) {modify_database("","update  `prefix_labelsets` set `languages`='$toreplace' where lid=".$datarow['lid']);echo $modifyoutput;flush();ob_flush();}
        }

        $resultdata=db_execute_assoc("select * from ".db_table_name("surveys"));
        while ($datarow = $resultdata->FetchRow()){
            $toreplace=$datarow['additional_languages'];
            $toreplace2=str_replace('no','nb',$toreplace);
            if ($toreplace2!=$toreplace) {modify_database("","update `prefix_surveys` set `additional_languages`='$toreplace' where sid=".$datarow['sid']);echo $modifyoutput;flush();ob_flush();}
        }


        modify_database("","ALTER TABLE `prefix_surveys` ADD `htmlemail` CHAR(1) default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` ADD `tokenanswerspersistence` CHAR(1) default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` ADD `usecaptcha` CHAR(1) default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_users` ADD `htmleditormode` CHAR(7) default 'default'"); echo $modifyoutput; flush();ob_flush();
        //122
        modify_database("","CREATE TABLE `prefix_templates_rights` (
						   `uid` int(11) NOT NULL,
						   `folder` varchar(255) NOT NULL,
						   `use` int(1) NOT NULL,
						   PRIMARY KEY  (`uid`,`folder`)
						   )  CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","CREATE TABLE `prefix_templates` (
						   `folder` varchar(255) NOT NULL,
						   `creator` int(11) NOT NULL,
						   PRIMARY KEY  (`folder`)
						   )  CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();

        //123
        modify_database("","ALTER TABLE `prefix_conditions` CHANGE `value` `value` VARCHAR(255) NOT NULL default ''"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_labels` CHANGE `title` `title` text"); echo $modifyoutput; flush();ob_flush();
        //124
        modify_database("","ALTER TABLE `prefix_surveys` ADD `bounce_email` text"); echo $modifyoutput; flush();ob_flush();
        //125
        upgrade_token_tables125();
        modify_database("","ALTER TABLE `prefix_users` ADD `superadmin` tinyint(1) NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_users` SET `superadmin`=1 where (create_survey=1 AND create_user=1 AND move_user=1 AND delete_user=1 AND configurator=1)"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_users` DROP COLUMN `move_user`"); echo $modifyoutput; flush();ob_flush();
        //126
        modify_database("","ALTER TABLE `prefix_questions` ADD `lid1` integer NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_conditions` SET `method`='==' where (`method` is null) or `method`='' or `method`='0'"); echo $modifyoutput; flush();ob_flush();

        modify_database("","update `prefix_settings_global` set `stg_value`='126' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 127) {
        modify_database("","create index `assessments_idx2` on `prefix_assessments` (`sid`)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index `assessments_idx3` on `prefix_assessments` (`gid`)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index `conditions_idx2` on `prefix_conditions` (`qid`)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index `groups_idx2` on `prefix_groups` (`sid`)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index `questions_idx2` on `prefix_questions` (`sid`)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index `questions_idx3` on `prefix_questions` (`gid`)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index `question_attributes_idx2` on `prefix_question_attributes` (`qid`)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index `quota_idx2` on `prefix_quota` (`sid`)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index `saved_control_idx2` on `prefix_saved_control` (`sid`)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index `user_in_groups_idx1` on `prefix_user_in_groups`  (`ugid`, `uid`)"); echo $modifyoutput;  flush();ob_flush();
        modify_database("","create index `answers_idx2` on `prefix_answers` (`sortorder`)"); echo $modifyoutput; flush();ob_flush();
        modify_database("","create index `conditions_idx3` on `prefix_conditions` (`cqid`)"); echo $modifyoutput; flush();ob_flush();
        modify_database("","create index `questions_idx4` on `prefix_questions` (`type`)"); echo $modifyoutput; flush();ob_flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='127' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 128) {
        //128
        upgrade_token_tables128();
        modify_database("","update `prefix_settings_global` set `stg_value`='128' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 129) {
        //129
        modify_database("","ALTER TABLE `prefix_surveys` ADD `startdate` DATETIME"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` ADD `usestartdate` varchar(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='129' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 130)
    {
        modify_database("","ALTER TABLE `prefix_conditions` ADD `scenario` integer NOT NULL default '1' AFTER `qid`"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_conditions` SET `scenario`=1 where (`scenario` is null) or `scenario`='' or `scenario`=0"); echo $modifyoutput; flush();ob_flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='130' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 131)
    {
        modify_database("","ALTER TABLE `prefix_surveys` ADD `publicstatistics` varchar(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='131' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 132)
    {
        modify_database("","ALTER TABLE `prefix_surveys` ADD `publicgraphs` varchar(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='132' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 133)
    {
        modify_database("","ALTER TABLE `prefix_users` ADD `one_time_pw` blob"); echo $modifyoutput; flush();ob_flush();
        // Add new assessment setting
        modify_database("","ALTER TABLE `prefix_surveys` ADD `assessments` varchar(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        // add new assessment value fields to answers & labels
        modify_database("","ALTER TABLE `prefix_answers` ADD `assessment_value` int(11) NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_labels` ADD `assessment_value` int(11) NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        // copy any valid codes from code field to assessment field
        modify_database("","update `prefix_answers` set `assessment_value`=CAST(`code` as SIGNED) where `code` REGEXP '^-?[0-9]+$'");echo $modifyoutput; flush();ob_flush();
        modify_database("","update `prefix_labels` set `assessment_value`=CAST(`code` as SIGNED) where `code` REGEXP '^-?[0-9]+$'");echo $modifyoutput; flush();ob_flush();
        // activate assessment where assesment rules exist
        modify_database("","update `prefix_surveys` set `assessments`='Y' where `sid` in (SELECT `sid` FROM `prefix_assessments` group by `sid`)"); echo $modifyoutput; flush();ob_flush();
        // add language field to assessment table
        modify_database("","ALTER TABLE `prefix_assessments` ADD `language` varchar(20) NOT NULL default 'en'"); echo $modifyoutput; flush();ob_flush();
        // update language field with default language of that particular survey
        modify_database("","update `prefix_assessments` set `language`=(select `language` from `prefix_surveys` where `sid`=`prefix_assessments`.`sid`)"); echo $modifyoutput; flush();ob_flush();
        // copy assessment link to message since from now on we will have HTML assignment messages
        modify_database("","update `prefix_assessments` set `message`=concat(replace(`message`,'/''',''''),'<br /><a href=\"',`link`,'\">',`link`,'</a>')"); echo $modifyoutput; flush();ob_flush();
        // drop the old link field
        modify_database("","ALTER TABLE `prefix_assessments` DROP COLUMN `link`"); echo $modifyoutput; flush();ob_flush();
        // change the primary index to include language
        modify_database("","ALTER TABLE `prefix_assessments` DROP PRIMARY KEY, ADD PRIMARY KEY  USING BTREE(`id`, `language`)"); echo $modifyoutput; flush();ob_flush();
        //finally fix missing translations for assessments
        upgrade_survey_tables133();
        // Add new fields to survey language settings
        modify_database("","ALTER TABLE `prefix_surveys_languagesettings` ADD `surveyls_url` varchar(255)"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys_languagesettings` ADD `surveyls_endtext` text"); echo $modifyoutput; flush();ob_flush();
        // copy old URL fields ot language specific entries
        modify_database("","update `prefix_surveys_languagesettings` set `surveyls_url`=(select `url` from `prefix_surveys` where `sid`=`prefix_surveys_languagesettings`.`surveyls_survey_id`)"); echo $modifyoutput; flush();ob_flush();
        // drop old URL field
        modify_database("","ALTER TABLE `prefix_surveys` DROP COLUMN `url`"); echo $modifyoutput; flush();ob_flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='133' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 134)
    {
        // Add new tokens setting
        modify_database("","ALTER TABLE `prefix_surveys` ADD `usetokens` varchar(1) NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` ADD `attributedescriptions` TEXT;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` DROP COLUMN `attribute1`"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` DROP COLUMN `attribute2`"); echo $modifyoutput; flush();ob_flush();
        upgrade_token_tables134();
        modify_database("","update `prefix_settings_global` set `stg_value`='134' where stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 135)
    {
        modify_database("","ALTER TABLE `prefix_question_attributes` MODIFY `value` text"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_settings_global` SET `stg_value`='135' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 136) //New Quota Functions
    {
        modify_database("","ALTER TABLE `prefix_quota` ADD `autoload_url` int(1) NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","CREATE TABLE `prefix_quota_languagesettings` (
								         `quotals_id` int(11) NOT NULL auto_increment,
										 `quotals_quota_id` int(11) NOT NULL default '0',
										 `quotals_language` varchar(45) NOT NULL default 'en',
										 `quotals_name` varchar(255) collate utf8_unicode_ci default NULL,
										 `quotals_message` text NOT NULL,
										 `quotals_url` varchar(255),
										 `quotals_urldescrip` varchar(255),
										 PRIMARY KEY (`quotals_id`)
										 )   CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_settings_global` SET `stg_value`='136' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 137) //New Quota Functions
    {
        modify_database("", "ALTER TABLE `prefix_surveys_languagesettings` ADD `surveyls_dateformat` int(1) NOT NULL default '1'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_users` ADD `dateformat` int(1) NOT NULL default '1'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE `prefix_surveys` set `startdate`=null where `usestartdate`='N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE `prefix_surveys` set `expires`=null where `useexpiry`='N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` DROP COLUMN `useexpiry`"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` DROP COLUMN `usestartdate`"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='137' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    if ($oldversion < 138) //Modify quota field
    {
        modify_database("", "ALTER TABLE `prefix_quota_members` CHANGE `code` `code` VARCHAR(11) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='138' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 139) //Modify quota field
    {
        upgrade_survey_tables139();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='139' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 140) //Modify surveys table
    {
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `emailresponseto` text DEFAULT NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='140' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 141) //Modify surveys table
    {
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `tokenlength` tinyint(2) NOT NULL default '15'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='141' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 142) //Modify surveys table
    {
        upgrade_question_attributes142();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `expires` `expires` datetime"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `startdate` `startdate` datetime"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE `prefix_question_attributes` SET `value`='0' WHERE `value`='false'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE `prefix_question_attributes` SET `value`='1' WHERE `value`='true'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='142' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 143) //Modify surveys table
    {
        modify_database("", "ALTER TABLE `prefix_questions` ADD `parent_qid` integer NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_answers` ADD `scale_id` tinyint NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_questions` ADD `scale_id` tinyint NOT NULL default '0'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_questions` ADD `same_default` tinyint NOT NULL default '0' COMMENT 'Saves if user set to use the same default value across languages in default options dialog'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_answers` DROP PRIMARY KEY, ADD PRIMARY KEY (`qid`,`code`,`language`,`scale_id`)"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "CREATE TABLE `prefix_defaultvalues` (
                              `qid` int(11) NOT NULL default '0',
                              `scale_id` int(11) NOT NULL default '0',
                              `sqid` int(11) NOT NULL default '0',
                              `language` varchar(20) NOT NULL,
                              `specialtype` varchar(20) NOT NULL default '',
                              `defaultvalue` text,
                              PRIMARY KEY  (`qid` , `scale_id`, `language`, `specialtype`, `sqid` )
                            )  CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();

        // -Move all 'answers' that are subquestions to the questions table
        // -Move all 'labels' that are answers to the answers table
        // -Transscribe the default values where applicable
        // -Move default values from answers to questions
        upgrade_tables143();

        modify_database("", "ALTER TABLE `prefix_answers` DROP COLUMN `default_value`"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_questions` DROP COLUMN `lid`"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_questions` DROP COLUMN `lid1`"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "CREATE TABLE prefix_sessions(
                              sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
                              expiry DATETIME NOT NULL ,
                              expireref VARCHAR( 250 ) DEFAULT '',
                              created DATETIME NOT NULL ,
                              modified DATETIME NOT NULL ,
                              sessdata LONGTEXT,
                              PRIMARY KEY ( sesskey ) ,
                              INDEX sess2_expiry( expiry ),
                              INDEX sess2_expireref( expireref ))  CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();   
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='143' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();

        
        


    }

    if ($oldversion < 145)
    {
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `savetimings` CHAR(1) NULL default 'N' AFTER `format`"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `showXquestions` CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `showgroupinfo` CHAR(1) NULL default 'B'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `shownoanswer` CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `showqnumcode` CHAR(1) NULL default 'X'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `bouncetime` BIGINT(20) NULL "); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `bounceprocessing` VARCHAR(1) NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `bounceaccounttype` VARCHAR(4) NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `bounceaccounthost` VARCHAR(200) NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `bounceaccountpass` VARCHAR(100) NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `bounceaccountencryption` VARCHAR(3) NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `bounceaccountuser` VARCHAR(200) NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `showwelcome` CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `showprogress` char(1) default 'Y'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `allowjumps` char(1) default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `navigationdelay` tinyint(2) default '0'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `nokeyboard` char(1) default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `alloweditaftercompletion` char(1) default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "CREATE TABLE `prefix_survey_permissions` (
                                `sid` int(10) unsigned NOT NULL,
                                `uid` int(10) unsigned NOT NULL,
                                `permission` varchar(20) NOT NULL,
                                `create_p` tinyint(1) NOT NULL default '0',
                                `read_p` tinyint(1) NOT NULL default '0',
                                `update_p` tinyint(1) NOT NULL default '0',
                                `delete_p` tinyint(1) NOT NULL default '0',
                                `import_p` tinyint(1) NOT NULL default '0',
                                `export_p` tinyint(1) NOT NULL default '0',
                                PRIMARY KEY (sid, uid, permission)
                            )  CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();
                            
		upgrade_surveypermissions_table145();
        
        // drop the old survey rights table
        modify_database("", "DROP TABLE `prefix_surveys_rights`"); echo $modifyoutput; flush();ob_flush();
        
        // Add new fields for email templates
        modify_database("", "ALTER TABLE `prefix_surveys_languagesettings` ADD 
                             (`email_admin_notification_subj`  VARCHAR(255) NULL,    
                              `email_admin_notification` TEXT NULL,        
                              `email_admin_responses_subj` VARCHAR(255) NULL,    
                              `email_admin_responses` TEXT NULL)");
        
        //Add index to questions table to speed up subquestions
        modify_database("", "create INDEX parent_qid_idx on prefix_questions( parent_qid );"); echo $modifyoutput; flush();ob_flush();   
        
                   
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `emailnotificationto` text DEFAULT NULL AFTER `emailresponseto`"); echo $modifyoutput; flush();ob_flush();
        upgrade_survey_table145();                                           
        modify_database("", "ALTER TABLE `prefix_surveys` DROP COLUMN `notification`"); echo $modifyoutput; flush();ob_flush();
                   
        modify_database("","ALTER TABLE `prefix_conditions` CHANGE `method` `method` CHAR( 5 ) NOT NULL default '';"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_surveys` set `private`='N' where `private` is NULL;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `private` `anonymized` char(1) collate utf8_unicode_ci NOT NULL default 'N';"); echo $modifyoutput; flush();ob_flush();
        
        
        //now we clean up things that were not properly set in previous DB upgrades

        modify_database("","UPDATE `prefix_answers` SET `answer`='' where `answer` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_answers` CHANGE `answer` `answer` text collate utf8_unicode_ci NOT NULL;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_answers` CHANGE `assessment_value` `assessment_value` int(11) NOT NULL default '0' AFTER `answer`;"); echo $modifyoutput; flush();ob_flush();

        modify_database("","UPDATE `prefix_assessments` SET `scope`='' where `scope` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_assessments` CHANGE `scope` `scope` varchar(5) collate utf8_unicode_ci NOT NULL default '';"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_assessments` SET `name`='' where `name` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_assessments` CHANGE `name` `name` text collate utf8_unicode_ci NOT NULL;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_assessments` SET `message`='' where `message` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_assessments` CHANGE `message` `message` text collate utf8_unicode_ci NOT NULL;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_assessments` SET `minimum`='' where `minimum` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_assessments` CHANGE `minimum` `minimum` varchar(50) collate utf8_unicode_ci NOT NULL default '';"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_assessments` SET `maximum`='' where `maximum` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_assessments` CHANGE `maximum` `maximum` varchar(50) collate utf8_unicode_ci NOT NULL default '';"); echo $modifyoutput; flush();ob_flush();
        
        modify_database("","ALTER TABLE `prefix_assessments` CHANGE `id` `id` int(11) NOT NULL;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_assessments` DROP PRIMARY KEY;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_assessments` ADD PRIMARY KEY (`id`,`language`);"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_assessments` CHANGE `id` `id` int(11) NOT NULL auto_increment;"); echo $modifyoutput; flush();ob_flush();

        modify_database("","ALTER TABLE `prefix_conditions` CHANGE `cfieldname` `cfieldname` varchar(50) collate utf8_unicode_ci NOT NULL default '';"); echo $modifyoutput; flush();ob_flush();
                                                                                                                  
        modify_database("","ALTER TABLE `prefix_defaultvalues` CHANGE `specialtype` `specialtype` varchar(20) collate utf8_unicode_ci NOT NULL default '' AFTER `qid`;"); echo $modifyoutput; flush();ob_flush();

        modify_database("","UPDATE `prefix_groups` SET `group_name`='' where `group_name` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_groups` CHANGE `group_name` `group_name` varchar(100) collate utf8_unicode_ci NOT NULL default '';"); echo $modifyoutput; flush();ob_flush();

        modify_database("","UPDATE `prefix_labels` SET `code`='' where `code` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_labels` CHANGE `code` `code` varchar(5) collate utf8_unicode_ci NOT NULL default '';"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_labels` CHANGE `language` `language` varchar(20) collate utf8_unicode_ci NOT NULL default 'en' AFTER `assessment_value`;"); echo $modifyoutput; flush();ob_flush();
        
        modify_database("","UPDATE `prefix_labelsets` SET `label_name`='' WHERE `label_name` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_labelsets` CHANGE `label_name` `label_name` varchar(100) collate utf8_unicode_ci NOT NULL default '';"); echo $modifyoutput; flush();ob_flush();

        modify_database("","ALTER TABLE `prefix_questions` CHANGE `parent_qid` `parent_qid` int(11) NOT NULL default '0' AFTER `qid`;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_questions` SET `type`='T' where `type` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_questions` CHANGE `type` `type` char(1) collate utf8_unicode_ci NOT NULL default 'T';"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_questions` SET `title`='' where `type` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_questions` CHANGE `title` `title` varchar(20) collate utf8_unicode_ci NOT NULL default '';"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_questions` SET `question`='' where `question` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_questions` CHANGE `question` `question` text collate utf8_unicode_ci NOT NULL;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_questions` SET `other`='N' where `other` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_questions` CHANGE `other` `other` char(1) collate utf8_unicode_ci NOT NULL default 'N';"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_questions` CHANGE `mandatory` `mandatory` char(1) collate utf8_unicode_ci default NULL;"); echo $modifyoutput; flush();ob_flush();

        modify_database("","ALTER TABLE `prefix_question_attributes` CHANGE `attribute` `attribute` varchar(50) collate utf8_unicode_ci default NULL;"); echo $modifyoutput; flush();ob_flush();

        modify_database("","ALTER TABLE `prefix_quota` CHANGE `qlimit` `qlimit` int(8) default NULL AFTER `name`;"); echo $modifyoutput; flush();ob_flush();

        modify_database("","UPDATE `prefix_saved_control` SET `identifier`='' where `identifier` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_saved_control` CHANGE `identifier` `identifier` text collate utf8_unicode_ci NOT NULL;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_saved_control` SET `access_code`='' where `access_code` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_saved_control` CHANGE `access_code` `access_code` text collate utf8_unicode_ci NOT NULL;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_saved_control` CHANGE `email` `email` varchar(320) collate utf8_unicode_ci default NULL;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_saved_control` SET `ip`='' where `ip` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_saved_control` CHANGE `ip` `ip` text collate utf8_unicode_ci NOT NULL;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_saved_control` SET `saved_thisstep`='' where `access_code` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_saved_control` CHANGE `saved_thisstep` `saved_thisstep` text collate utf8_unicode_ci NOT NULL;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_saved_control` SET `status`='' where `access_code` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_saved_control` CHANGE `status` `status` char(1) collate utf8_unicode_ci NOT NULL default '';"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_saved_control` SET `saved_date`='0000-00-00 00:00:00' where `saved_date` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_saved_control` CHANGE `saved_date` `saved_date` datetime NOT NULL;"); echo $modifyoutput; flush();ob_flush();

        modify_database("","UPDATE `prefix_settings_global` SET `stg_value`='' where `stg_value` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_settings_global` CHANGE `stg_value` `stg_value` varchar(255) collate utf8_unicode_ci NOT NULL default ''"); echo $modifyoutput; flush();ob_flush();
        
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `admin` `admin` varchar(50) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_surveys` SET `active`='N' where `active` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `active` `active` char(1) collate utf8_unicode_ci NOT NULL default 'N';"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `startdate` `startdate` datetime default NULL AFTER `expires`"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `adminemail` `adminemail` varchar(320) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `anonymized` `anonymized` char(1) collate utf8_unicode_ci NOT NULL default 'N'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `faxto` `faxto` varchar(20) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `format` `format` char(1) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `language` `language` varchar(50) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `additional_languages` `additional_languages` varchar(255) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `printanswers` `printanswers` char(1) collate utf8_unicode_ci default 'N' AFTER `allowprev`"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `publicstatistics` `publicstatistics` char(1) collate utf8_unicode_ci default 'N' after `datecreated`"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `publicgraphs` `publicgraphs` char(1) collate utf8_unicode_ci default 'N' AFTER `publicstatistics`"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `assessments` `assessments` char(1) collate utf8_unicode_ci default 'N' AFTER `tokenanswerspersistence`"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `usetokens` `usetokens` char(1) collate utf8_unicode_ci default 'N' AFTER `usecaptcha`"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `bounce_email` `bounce_email` varchar(320) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `tokenlength` `tokenlength` tinyint(2) default '15'"); echo $modifyoutput; flush();ob_flush();

        modify_database("","UPDATE `prefix_surveys_languagesettings` SET `surveyls_title`='' where `surveyls_title` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys_languagesettings` CHANGE `surveyls_title` `surveyls_title` varchar(200) collate utf8_unicode_ci NOT NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys_languagesettings` CHANGE `surveyls_endtext` `surveyls_endtext` text collate utf8_unicode_ci AFTER `surveyls_welcometext`"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys_languagesettings` CHANGE `surveyls_url` `surveyls_url` varchar(255) collate utf8_unicode_ci default NULL   AFTER `surveyls_endtext`"); echo $modifyoutput; flush();ob_flush();

        modify_database("","ALTER TABLE `prefix_surveys_languagesettings` CHANGE `surveyls_urldescription` `surveyls_urldescription` varchar(255) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys_languagesettings` CHANGE `surveyls_email_invite_subj` `surveyls_email_invite_subj` varchar(255) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys_languagesettings` CHANGE `surveyls_email_remind_subj` `surveyls_email_remind_subj` varchar(255) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys_languagesettings` CHANGE `surveyls_email_register_subj` `surveyls_email_register_subj` varchar(255) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys_languagesettings` CHANGE `surveyls_email_confirm_subj` `surveyls_email_confirm_subj` varchar(255) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_surveys_languagesettings` CHANGE `surveyls_dateformat` `surveyls_dateformat` int(10) unsigned NOT NULL default '1'"); echo $modifyoutput; flush();ob_flush();

        modify_database("","UPDATE `prefix_users` SET `users_name`='' where `users_name` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_users` CHANGE `users_name` `users_name` varchar(64) collate utf8_unicode_ci NOT NULL default ''"); echo $modifyoutput; flush();ob_flush();
        modify_database("","UPDATE `prefix_users` SET `full_name`='' where `full_name` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_users` CHANGE `full_name` `full_name` varchar(50) collate utf8_unicode_ci NOT NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_users` CHANGE `lang` `lang` varchar(20) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_users` CHANGE `email` `email` varchar(320) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_users` CHANGE `superadmin` `superadmin` tinyint(1) NOT NULL default '0' AFTER `delete_user`"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_users` CHANGE `htmleditormode` `htmleditormode` varchar(7) collate utf8_unicode_ci default 'default'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_users` CHANGE `dateformat` `dateformat` int(10) unsigned NOT NULL default '1'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_users` DROP INDEX `email`;"); 

        modify_database("","UPDATE `prefix_user_groups` SET `name`='' where `name` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_user_groups` CHANGE `name` `name` varchar(20) collate utf8_unicode_ci NOT NULL"); echo $modifyoutput; flush();ob_flush();
        
        modify_database("","UPDATE `prefix_user_groups` SET `description`='' where `description` is null;"); echo $modifyoutput; flush();ob_flush();
        modify_database("","ALTER TABLE `prefix_user_groups` CHANGE `description` `description` text collate utf8_unicode_ci NOT NULL"); echo $modifyoutput; flush();ob_flush();

        modify_database("","ALTER TABLE `prefix_user_in_groups` DROP INDEX `user_in_groups_idx1`"); // Don't show output because this key might not exist
        modify_database("","ALTER TABLE `prefix_user_in_groups` ADD PRIMARY KEY (`ugid`, `uid`)"); // Don't show output because this might already be set
        modify_database("", "ALTER TABLE  `prefix_surveys_languagesettings` ADD  `surveyls_numberformat` int(11) NOT NULL DEFAULT 0 AFTER  `surveyls_dateformat`"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "CREATE TABLE `prefix_failed_login_attempts` (
                              `id` int(11) NOT NULL AUTO_INCREMENT,
                              `ip` varchar(37) NOT NULL,
                              `last_attempt` varchar(20) NOT NULL,
                              `number_attempts` int(11) NOT NULL,
                              PRIMARY KEY (`id`)
                            )  CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();
        upgrade_token_tables145();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='145' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

    if ($oldversion < 146) //Modify surveys table
    {
        upgrade_timing_tables146();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='146' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }

     if ($oldversion < 147)
    {
        modify_database("", "ALTER TABLE `prefix_users` ADD `templateeditormode` VARCHAR( 7 )NOT NULL DEFAULT 'default' AFTER `htmleditormode`"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "ALTER TABLE `prefix_users` ADD `questionselectormode` VARCHAR( 7 )NOT NULL DEFAULT 'default' AFTER `templateeditormode`"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='147' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }   
    if ($oldversion < 148)
    {
        modify_database("","ALTER TABLE `prefix_users` ADD `participant_panel` tinyint(1) NOT NULL default '1'"); echo $modifyoutput; flush();ob_flush();
        modify_database("","CREATE TABLE `prefix_participants` (`participant_id` varchar(50) NOT NULL,
                                                                `firstname` varchar(40) default NULL,
                                                                `lastname` varchar(40) default NULL,
                                                                `email` varchar(80) collate utf8_unicode_ci default NULL,
                                                                `language` varchar(40) default NULL,
                                                                `blacklisted` varchar(1) NOT NULL,
                                                                `owner_uid` int(20) NOT NULL ,
                                                                PRIMARY KEY  (`participant_id`)
                                                                )   CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();
                                                
        modify_database("","CREATE TABLE `prefix_participant_attribute` (
        `participant_id` varchar(50) NOT NULL,
        `attribute_id` int(11) NOT NULL,
        `value` varchar(50) NOT NULL,
        PRIMARY KEY  (`participant_id`,`attribute_id`)
        )   CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();

                                                          
        modify_database("","CREATE TABLE `prefix_participant_attribute_names` (
        `attribute_id` int(11) NOT NULL AUTO_INCREMENT,
        `attribute_type` varchar(4) NOT NULL,
        `visible` char(5) NOT NULL,
        PRIMARY KEY  (`attribute_id`,`attribute_type`)
        )   CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();
        
        modify_database("","CREATE TABLE `prefix_participant_attribute_names_lang` (
        `attribute_id` int(11) NOT NULL,
        `attribute_name` varchar(30) NOT NULL,
        `lang` varchar(20) NOT NULL,
        PRIMARY KEY  (`attribute_id`,`lang`)
)   CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();
        
        modify_database("","CREATE TABLE `prefix_participant_attribute_values` (
        `attribute_id` int(11) NOT NULL,
        `value_id` int(11) NOT NULL AUTO_INCREMENT,
        `value` varchar(20) NOT NULL,
        PRIMARY KEY  (`value_id`)
        )   CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();
        
        modify_database("","CREATE TABLE `prefix_participant_shares` (
        `participant_id` varchar(50) NOT NULL,
        `share_uid` int(11) NOT NULL,
        `date_added` datetime NOT NULL,
        `can_edit` varchar(5) NOT NULL,
        PRIMARY KEY  (`participant_id`,`share_uid`)
        )   CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();
 
        modify_database("","CREATE TABLE `prefix_survey_links` (
        `participant_id` varchar(50) NOT NULL,
        `token_id` int(11) NOT NULL,
        `survey_id` int(11) NOT NULL,
        `date_created` datetime NOT NULL,
        PRIMARY KEY  (`participant_id`,`token_id`,`survey_id`)
        )   CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();ob_flush();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='148' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();ob_flush();
    }
    echo '<br /><br />'.sprintf($clang->gT('Database update finished (%s)'),date('Y-m-d H:i:s')).'<br />';
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
            modify_database("","ALTER TABLE ".db_table_name('survey_'.$sv[0])." ADD `startdate` datetime AFTER `datestamp`"); echo $modifyoutput; flush();ob_flush();
        }
    }
}

function upgrade_survey_tables118()
{
    global $modifyoutput,$dbprefix;
    $surveyidquery = "SHOW TABLES LIKE '".$dbprefix."tokens%'";
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    else
    {
        while ( $sv = $surveyidresult->FetchRow() )
        {
            modify_database("","ALTER TABLE ".$sv[0]." CHANGE `token` `token` VARCHAR(15)"); echo $modifyoutput; flush();ob_flush();
        }
    }
}

function upgrade_token_tables125()
{
    global $modifyoutput,$dbprefix;
    $surveyidquery = "SHOW TABLES LIKE '".$dbprefix."tokens%'";
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    else
    {
        while ( $sv = $surveyidresult->FetchRow() )
        {
            modify_database("","ALTER TABLE ".$sv[0]." ADD `emailstatus` varchar(300) NOT NULL DEFAULT 'OK'"); echo $modifyoutput; flush();ob_flush();
        }
    }
}

// Add the reminders tracking fields
function upgrade_token_tables128()
{
    global $modifyoutput,$dbprefix;
    $surveyidquery = "SHOW TABLES LIKE '".$dbprefix."tokens%'";
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    else
    {
        while ( $sv = $surveyidresult->FetchRow() )
        {
            modify_database("","ALTER TABLE ".$sv[0]." ADD `remindersent` VARCHAR(17) DEFAULT 'N'"); echo $modifyoutput; flush();ob_flush();
            modify_database("","ALTER TABLE ".$sv[0]." ADD `remindercount` INT(11)  DEFAULT 0"); echo $modifyoutput; flush();ob_flush();
        }
    }
}




function upgrade_survey_tables133()
{
    $surveyidquery = "SELECT sid,additional_languages FROM ".db_table_name('surveys');
    $surveyidresult = db_execute_num($surveyidquery);
    while ( $sv = $surveyidresult->FetchRow() )
    {
        FixLanguageConsistency($sv[0],$sv[1]);
    }
}


// Add the reminders tracking fields
function upgrade_token_tables134()
{
    global $modifyoutput,$dbprefix;
    $surveyidquery = "SHOW TABLES LIKE '".$dbprefix."tokens%'";
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    else
    {
        while ( $sv = $surveyidresult->FetchRow() )
        {
            modify_database("","ALTER TABLE ".$sv[0]." ADD `validfrom` Datetime"); echo $modifyoutput; flush();ob_flush();
            modify_database("","ALTER TABLE ".$sv[0]." ADD `validuntil` Datetime"); echo $modifyoutput; flush();ob_flush();
        }
    }
}

// Add the usesleft field to all existing token tables
function upgrade_token_tables145()
{
    global $modifyoutput,$dbprefix;
    $surveyidquery = "SHOW TABLES LIKE '".$dbprefix."tokens%'";
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    else
    {
        while ( $sv = $surveyidresult->FetchRow() )
        {
            modify_database("","ALTER TABLE ".$sv[0]." ADD `usesleft` int(11) NOT NULL default '1'"); echo $modifyoutput; flush();ob_flush();
            modify_database("","UPDATE ".$sv[0]." SET `usesleft`='0' WHERE completed<>'N'"); echo $modifyoutput; flush();ob_flush();
        }
    }
}


function fix_mysql_collation()
{
    global $connect, $modifyoutput, $dbprefix;
    $sql = 'SHOW TABLE STATUS';
    $result = db_execute_assoc($sql);
    if (!$result) {
        $modifyoutput .= 'SHOW TABLE - SQL Error';
    }

    while ( $tables = $result->FetchRow() ) {
        // Loop through all tables in this database
        $table = $tables['Name'];
        $tablecollation=$tables['Collation'];
        if (strpos($table,'old_')===false  && ($dbprefix==''  || ($dbprefix!='' && strpos($table,$dbprefix)!==false)))
        {
            if ($tablecollation!='utf8_unicode_ci')
            {
                modify_database("","ALTER TABLE $table COLLATE utf8_unicode_ci");
                echo $modifyoutput; flush();ob_flush();
            }

            # Now loop through all the fields within this table
            $result2 = db_execute_assoc("SHOW FULL COLUMNS FROM ".$table);
            while ( $column = $result2->FetchRow())
            {
                if ($column['Collation']!= 'utf8_unicode_ci' )
                {
                    $field_name = $column['Field'];
                    $field_type = $column['Type'];
                    $field_default = $column['Default'];
                    if ($field_default!='NULL') {$field_default="'".$field_default."'";}
                    # Change text based fields
                    $skipped_field_types = array('char', 'text', 'enum', 'set');

                    foreach ( $skipped_field_types as $type )
                    {
                        if ( strpos($field_type, $type) !== false )
                        {
                            $modstatement="ALTER TABLE $table CHANGE `$field_name` `$field_name` $field_type CHARACTER SET utf8 COLLATE utf8_unicode_ci";
                            if ($type!='text') {$modstatement.=" DEFAULT $field_default";}
                            modify_database("",$modstatement);
                            echo $modifyoutput; flush();ob_flush();
                        }
                    }
                }
            }
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
            if (strpos($sv[0],$dbprefix."survey_")!==false)
            {
                modify_database("","ALTER TABLE ".$sv[0]." ADD `lastpage` integer"); echo $modifyoutput; flush();ob_flush();
            }
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
            $insertarray['parent_qid']=$row['qid'];
            $insertarray['type']=$row['type'];
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
    // Sanitize data
    modify_database("","delete {$dbprefix}answers from {$dbprefix}answers LEFT join {$dbprefix}questions ON {$dbprefix}answers.qid={$dbprefix}questions.qid where {$dbprefix}questions.type in ('1','F','H','M','P','W','Z')"); echo $modifyoutput; flush();ob_flush();

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
                $insertarray['gid']=$row['gid'];
                $insertarray['parent_qid']=$row['qid'];
                $insertarray['type']=$row['type'];
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
        modify_database("","ALTER TABLE {$sTable} CHANGE `interviewTime` `interviewtime` DOUBLE NULL default 0;"); echo $modifyoutput; flush();ob_flush();
    }
}
