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
    global $modifyoutput, $databasename, $databasetabletype;
    echo str_pad('Starting database update ('.date('Y-m-d H:i:s').')',4096)."<br />\n";
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
            modify_database("","update `prefix_answers` set `language`='$newlang' where language='$oldlang'");  echo $modifyoutput;      flush();
            modify_database("","update `prefix_questions` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update `prefix_groups` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update `prefix_labels` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update `prefix_surveys` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update `prefix_surveys_languagesettings` set `surveyls_language`='$newlang' where surveyls_language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update `prefix_users` set `lang`='$newlang' where lang='$oldlang'");echo $modifyoutput;flush();
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
            modify_database("","update  `prefix_labelsets` set `languages`='$toreplace' where lid=".$datarow['lid']);echo $modifyoutput;flush();
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
            modify_database("","update  `prefix_surveys` set `additional_languages`='$toreplace' where sid=".$datarow['sid']);echo $modifyoutput;flush();
        }
        modify_database("","update `prefix_settings_global` set `stg_value`='111' where stg_name='DBVersion'"); echo $modifyoutput; flush();

    }


    if ($oldversion < 112) {
        //The size of the users_name field is now 64 char (20 char before version 112)
        modify_database("","ALTER TABLE `prefix_users` CHANGE `users_name` `users_name` VARCHAR( 64 ) NOT NULL"); echo $modifyoutput; flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='112' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 113) {
        //Fixes the collation for the complete DB, tables and columns
        echo "<strong>Attention:</strong>The following upgrades will update your MySQL Database collations. This may take some time.<br />If for any reason you should get a timeout just re-run the upgrade procedure. The updating will continue where it left off.<br /><br />"; flush();
        fix_mysql_collation();
        modify_database("","ALTER DATABASE `$databasename` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;");echo $modifyoutput; flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='113' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 114) {
        modify_database("","ALTER TABLE `prefix_saved_control` CHANGE `email` `email` VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `adminemail` `adminemail` VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_users` CHANGE `email` `email` VARCHAR(320) NOT NULL"); echo $modifyoutput; flush();
        modify_database("",'INSERT INTO `prefix_settings_global` VALUES (\'SessionName\', \'$sessionname\');');echo $modifyoutput; flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='114' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 126) {
        //Adds new "public" field
        modify_database("","ALTER TABLE `prefix_surveys` ADD `printanswers` CHAR(1) default 'N' AFTER allowsave"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_surveys` ADD `listpublic` CHAR(1) default 'N' AFTER `datecreated`"); echo $modifyoutput; flush();
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
							)  ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();
        modify_database("","CREATE TABLE `prefix_quota_members` (
   		 				   `id` int(11) NOT NULL auto_increment,
						   `sid` int(11) default NULL,
  						   `qid` int(11) default NULL,
  						   `quota_id` int(11) default NULL,
  						   `code` varchar(5) collate utf8_unicode_ci default NULL,
  						   PRIMARY KEY  (`id`),
  						   UNIQUE KEY `sid` (`sid`,`qid`,`quota_id`,`code`)
						   )   ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();

        // Rename Norwegian language code from NO to NB
        $oldnewlanguages=array('no'=>'nb');
        foreach  ($oldnewlanguages as $oldlang=>$newlang)
        {
            modify_database("","update `prefix_answers` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update `prefix_questions` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update `prefix_groups` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update `prefix_labels` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update `prefix_surveys` set `language`='$newlang' where language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update `prefix_surveys_languagesettings` set `surveyls_language`='$newlang' where surveyls_language='$oldlang'");echo $modifyoutput;flush();
            modify_database("","update `prefix_users` set `lang`='$newlang' where lang='$oldlang'");echo $modifyoutput;flush();
        }

        $resultdata=db_execute_assoc("select * from ".db_table_name("labelsets"));
        while ($datarow = $resultdata->FetchRow()){
            $toreplace=$datarow['languages'];
            $toreplace2=str_replace('no','nb',$toreplace);
            if ($toreplace2!=$toreplace) {modify_database("","update  `prefix_labelsets` set `languages`='$toreplace' where lid=".$datarow['lid']);echo $modifyoutput;flush();}
        }

        $resultdata=db_execute_assoc("select * from ".db_table_name("surveys"));
        while ($datarow = $resultdata->FetchRow()){
            $toreplace=$datarow['additional_languages'];
            $toreplace2=str_replace('no','nb',$toreplace);
            if ($toreplace2!=$toreplace) {modify_database("","update `prefix_surveys` set `additional_languages`='$toreplace' where sid=".$datarow['sid']);echo $modifyoutput;flush();}
        }


        modify_database("","ALTER TABLE `prefix_surveys` ADD `htmlemail` CHAR(1) default 'N'"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_surveys` ADD `tokenanswerspersistence` CHAR(1) default 'N'"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_surveys` ADD `usecaptcha` CHAR(1) default 'N'"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_users` ADD `htmleditormode` CHAR(7) default 'default'"); echo $modifyoutput; flush();
        //122
        modify_database("","CREATE TABLE `prefix_templates_rights` (
						   `uid` int(11) NOT NULL,
						   `folder` varchar(255) NOT NULL,
						   `use` int(1) NOT NULL,
						   PRIMARY KEY  (`uid`,`folder`)
						   ) ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();
        modify_database("","CREATE TABLE `prefix_templates` (
						   `folder` varchar(255) NOT NULL,
						   `creator` int(11) NOT NULL,
						   PRIMARY KEY  (`folder`)
						   ) ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();

        //123
        modify_database("","ALTER TABLE `prefix_conditions` CHANGE `value` `value` VARCHAR(255) NOT NULL default ''"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_labels` CHANGE `title` `title` text"); echo $modifyoutput; flush();
        //124
        modify_database("","ALTER TABLE `prefix_surveys` ADD `bounce_email` text"); echo $modifyoutput; flush();
        //125
        upgrade_token_tables125();
        modify_database("","ALTER TABLE `prefix_users` ADD `superadmin` tinyint(1) NOT NULL default '0'"); echo $modifyoutput; flush();
        modify_database("","UPDATE `prefix_users` SET `superadmin`=1 where (create_survey=1 AND create_user=1 AND move_user=1 AND delete_user=1 AND configurator=1)"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_users` DROP COLUMN `move_user`"); echo $modifyoutput; flush();
        //126
        modify_database("","ALTER TABLE `prefix_questions` ADD `lid1` integer NOT NULL default '0'"); echo $modifyoutput; flush();
        modify_database("","UPDATE `prefix_conditions` SET `method`='==' where (`method` is null) or `method`='' or `method`='0'"); echo $modifyoutput; flush();

        modify_database("","update `prefix_settings_global` set `stg_value`='126' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 127) {
        modify_database("","create index `assessments_idx2` on `prefix_assessments` (`sid`)"); echo $modifyoutput;  flush();
        modify_database("","create index `assessments_idx3` on `prefix_assessments` (`gid`)"); echo $modifyoutput;  flush();
        modify_database("","create index `conditions_idx2` on `prefix_conditions` (`qid`)"); echo $modifyoutput;  flush();
        modify_database("","create index `groups_idx2` on `prefix_groups` (`sid`)"); echo $modifyoutput;  flush();
        modify_database("","create index `questions_idx2` on `prefix_questions` (`sid`)"); echo $modifyoutput;  flush();
        modify_database("","create index `questions_idx3` on `prefix_questions` (`gid`)"); echo $modifyoutput;  flush();
        modify_database("","create index `question_attributes_idx2` on `prefix_question_attributes` (`qid`)"); echo $modifyoutput;  flush();
        modify_database("","create index `quota_idx2` on `prefix_quota` (`sid`)"); echo $modifyoutput;  flush();
        modify_database("","create index `saved_control_idx2` on `prefix_saved_control` (`sid`)"); echo $modifyoutput;  flush();
        modify_database("","create index `user_in_groups_idx1` on `prefix_user_in_groups`  (`ugid`, `uid`)"); echo $modifyoutput;  flush();
        modify_database("","create index `answers_idx2` on `prefix_answers` (`sortorder`)"); echo $modifyoutput; flush();
        modify_database("","create index `conditions_idx3` on `prefix_conditions` (`cqid`)"); echo $modifyoutput; flush();
        modify_database("","create index `questions_idx4` on `prefix_questions` (`type`)"); echo $modifyoutput; flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='127' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 128) {
        //128
        upgrade_token_tables128();
        modify_database("","update `prefix_settings_global` set `stg_value`='128' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 129) {
        //129
        modify_database("","ALTER TABLE `prefix_surveys` ADD `startdate` DATETIME"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_surveys` ADD `usestartdate` varchar(1) NOT NULL default 'N'"); echo $modifyoutput; flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='129' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 130)
    {
        modify_database("","ALTER TABLE `prefix_conditions` ADD `scenario` integer NOT NULL default '1' AFTER `qid`"); echo $modifyoutput; flush();
        modify_database("","UPDATE `prefix_conditions` SET `scenario`=1 where (`scenario` is null) or `scenario`='' or `scenario`=0"); echo $modifyoutput; flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='130' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 131)
    {
        modify_database("","ALTER TABLE `prefix_surveys` ADD `publicstatistics` varchar(1) NOT NULL default 'N'"); echo $modifyoutput; flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='131' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 132)
    {
        modify_database("","ALTER TABLE `prefix_surveys` ADD `publicgraphs` varchar(1) NOT NULL default 'N'"); echo $modifyoutput; flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='132' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 133)
    {
        modify_database("","ALTER TABLE `prefix_users` ADD `one_time_pw` blob"); echo $modifyoutput; flush();
        // Add new assessment setting
        modify_database("","ALTER TABLE `prefix_surveys` ADD `assessments` varchar(1) NOT NULL default 'N'"); echo $modifyoutput; flush();
        // add new assessment value fields to answers & labels
        modify_database("","ALTER TABLE `prefix_answers` ADD `assessment_value` int(11) NOT NULL default '0'"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_labels` ADD `assessment_value` int(11) NOT NULL default '0'"); echo $modifyoutput; flush();
        // copy any valid codes from code field to assessment field
        modify_database("","update `prefix_answers` set `assessment_value`=CAST(`code` as SIGNED) where `code` REGEXP '^-?[0-9]+$'");echo $modifyoutput; flush();
        modify_database("","update `prefix_labels` set `assessment_value`=CAST(`code` as SIGNED) where `code` REGEXP '^-?[0-9]+$'");echo $modifyoutput; flush();
        // activate assessment where assesment rules exist
        modify_database("","update `prefix_surveys` set `assessments`='Y' where `sid` in (SELECT `sid` FROM `prefix_assessments` group by `sid`)"); echo $modifyoutput; flush();
        // add language field to assessment table
        modify_database("","ALTER TABLE `prefix_assessments` ADD `language` varchar(20) NOT NULL default 'en'"); echo $modifyoutput; flush();
        // update language field with default language of that particular survey
        modify_database("","update `prefix_assessments` set `language`=(select `language` from `prefix_surveys` where `sid`=`prefix_assessments`.`sid`)"); echo $modifyoutput; flush();
        // copy assessment link to message since from now on we will have HTML assignment messages
        modify_database("","update `prefix_assessments` set `message`=concat(replace(`message`,'/''',''''),'<br /><a href=\"',`link`,'\">',`link`,'</a>')"); echo $modifyoutput; flush();
        // drop the old link field
        modify_database("","ALTER TABLE `prefix_assessments` DROP COLUMN `link`"); echo $modifyoutput; flush();
        // change the primary index to include language
        modify_database("","ALTER TABLE `prefix_assessments` DROP PRIMARY KEY, ADD PRIMARY KEY  USING BTREE(`id`, `language`)"); echo $modifyoutput; flush();
        //finally fix missing translations for assessments
        upgrade_survey_tables133();
        // Add new fields to survey language settings
        modify_database("","ALTER TABLE `prefix_surveys_languagesettings` ADD `surveyls_url` varchar(255)"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_surveys_languagesettings` ADD `surveyls_endtext` text"); echo $modifyoutput; flush();
        // copy old URL fields ot language specific entries
        modify_database("","update `prefix_surveys_languagesettings` set `surveyls_url`=(select `url` from `prefix_surveys` where `sid`=`prefix_surveys_languagesettings`.`surveyls_survey_id`)"); echo $modifyoutput; flush();
        // drop old URL field
        modify_database("","ALTER TABLE `prefix_surveys` DROP COLUMN `url`"); echo $modifyoutput; flush();
        modify_database("","update `prefix_settings_global` set `stg_value`='133' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 134)
    {
        // Add new tokens setting
        modify_database("","ALTER TABLE `prefix_surveys` ADD `usetokens` varchar(1) NOT NULL default 'N'"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_surveys` ADD `attributedescriptions` TEXT;"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_surveys` DROP COLUMN `attribute1`"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_surveys` DROP COLUMN `attribute2`"); echo $modifyoutput; flush();
        upgrade_token_tables134();
        modify_database("","update `prefix_settings_global` set `stg_value`='134' where stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 135)
    {
        modify_database("","ALTER TABLE `prefix_question_attributes` MODIFY `value` text"); echo $modifyoutput; flush();
        modify_database("","UPDATE `prefix_settings_global` SET `stg_value`='135' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 136) //New Quota Functions
    {
        modify_database("","ALTER TABLE `prefix_quota` ADD `autoload_url` int(1) NOT NULL default '0'"); echo $modifyoutput; flush();
        modify_database("","CREATE TABLE `prefix_quota_languagesettings` (
								         `quotals_id` int(11) NOT NULL auto_increment,
										 `quotals_quota_id` int(11) NOT NULL default '0',
										 `quotals_language` varchar(45) NOT NULL default 'en',
										 `quotals_name` varchar(255) collate utf8_unicode_ci default NULL,
										 `quotals_message` text NOT NULL,
										 `quotals_url` varchar(255),
										 `quotals_urldescrip` varchar(255),
										 PRIMARY KEY (`quotals_id`)
										 )  ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();
        modify_database("","UPDATE `prefix_settings_global` SET `stg_value`='136' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 137) //New Quota Functions
    {
        modify_database("", "ALTER TABLE `prefix_surveys_languagesettings` ADD `surveyls_dateformat` int(1) NOT NULL default '1'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_users` ADD `dateformat` int(1) NOT NULL default '1'"); echo $modifyoutput; flush();
        modify_database("", "UPDATE `prefix_surveys` set `startdate`=null where `usestartdate`='N'"); echo $modifyoutput; flush();
        modify_database("", "UPDATE `prefix_surveys` set `expires`=null where `useexpiry`='N'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_surveys` DROP COLUMN `useexpiry`"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_surveys` DROP COLUMN `usestartdate`"); echo $modifyoutput; flush();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='137' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }
    if ($oldversion < 138) //Modify quota field
    {
        modify_database("", "ALTER TABLE `prefix_quota_members` CHANGE `code` `code` VARCHAR(11) collate utf8_unicode_ci default NULL"); echo $modifyoutput; flush();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='138' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 139) //Modify quota field
    {
        upgrade_survey_tables139();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='139' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 140) //Modify surveys table
    {
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `emailresponseto` text DEFAULT NULL"); echo $modifyoutput; flush();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='140' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 141) //Modify surveys table
    {
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `tokenlength` tinyint(2) NOT NULL default '15'"); echo $modifyoutput; flush();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='141' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 142) //Modify surveys table
    {
        upgrade_question_attributes142();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `expires` `expires` datetime"); echo $modifyoutput; flush();
        modify_database("","ALTER TABLE `prefix_surveys` CHANGE `startdate` `startdate` datetime"); echo $modifyoutput; flush();
        modify_database("", "UPDATE `prefix_question_attributes` SET `value`='0' WHERE `value`='false'"); echo $modifyoutput; flush();
        modify_database("", "UPDATE `prefix_question_attributes` SET `value`='1' WHERE `value`='true'"); echo $modifyoutput; flush();
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='142' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 143) //Modify surveys table
    {
        modify_database("", "ALTER TABLE `prefix_questions` ADD `parent_qid` integer NOT NULL default '0'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_answers` ADD `scale_id` tinyint NOT NULL default '0'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_questions` ADD `scale_id` tinyint NOT NULL default '0'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_questions` ADD `same_default` tinyint NOT NULL default '0' COMMENT 'Saves if user set to use the same default value across languages in default options dialog'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_answers` DROP PRIMARY KEY, ADD PRIMARY KEY (`qid`,`code`,`language`,`scale_id`)"); echo $modifyoutput; flush();
        modify_database("", "CREATE TABLE `prefix_defaultvalues` (
                              `qid` int(11) NOT NULL default '0',
                              `scale_id` int(11) NOT NULL default '0',
                              `sqid` int(11) NOT NULL default '0',
                              `language` varchar(20) NOT NULL,
                              `specialtype` varchar(20) NOT NULL default '',
                              `defaultvalue` text,
                              PRIMARY KEY  (`qid` , `scale_id`, `language`, `specialtype`, `sqid` )
                            ) ENGINE=$databasetabletype CHARACTER SET utf8 COLLATE utf8_unicode_ci;"); echo $modifyoutput; flush();

        // -Move all 'answers' that are subquestions to the questions table
        // -Move all 'labels' that are answers to the answers table
        // -Transscribe the default values where applicable
        // -Move default values from answers to questions
        upgrade_tables143();

        modify_database("", "ALTER TABLE `prefix_answers` DROP COLUMN `default_value`"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_questions` DROP COLUMN `lid`"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_questions` DROP COLUMN `lid1`"); echo $modifyoutput; flush();
        modify_database("", "CREATE TABLE prefix_sessions(
                              sesskey VARCHAR( 64 ) NOT NULL DEFAULT '',
                              expiry DATETIME NOT NULL ,
                              expireref VARCHAR( 250 ) DEFAULT '',
                              created DATETIME NOT NULL ,
                              modified DATETIME NOT NULL ,
                              sessdata LONGTEXT,
                              PRIMARY KEY ( sesskey ) ,
                              INDEX sess2_expiry( expiry ),
                              INDEX sess2_expireref( expireref ))"); echo $modifyoutput; flush();          
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='143' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }

    if ($oldversion < 145) //Modify surveys table
    {
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `showXquestions` CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `showgroupinfo` CHAR(1) NULL default 'B'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `shownoanswer` CHAR(1) NULL default 'Y'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `showqnumcode` CHAR(1) NULL default 'X'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `timestamp` BIGINT(20) NULL "); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `bounceprocessing` VARCHAR(1) NULL default 'N'"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `bounceaccounttype` VARCHAR(4) NULL"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `bounceaccounthost` VARCHAR(20) NULL"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `bounceaccountpass` VARCHAR(20) NULL"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `bounceaccountencryption` VARCHAR(4) NULL"); echo $modifyoutput; flush();
        modify_database("", "ALTER TABLE `prefix_surveys` ADD `bounceaccountuser` VARCHAR(320) NULL"); echo $modifyoutput; flush();
              
        modify_database("", "UPDATE `prefix_settings_global` SET `stg_value`='145' WHERE stg_name='DBVersion'"); echo $modifyoutput; flush();
    }


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
            modify_database("","ALTER TABLE ".db_table_name('survey_'.$sv[0])." ADD `startdate` datetime AFTER `datestamp`"); echo $modifyoutput; flush();
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
            modify_database("","ALTER TABLE ".$sv[0]." CHANGE `token` `token` VARCHAR(15)"); echo $modifyoutput; flush();
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
            modify_database("","ALTER TABLE ".$sv[0]." ADD `emailstatus` varchar(300) NOT NULL DEFAULT 'OK'"); echo $modifyoutput; flush();
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
            modify_database("","ALTER TABLE ".$sv[0]." ADD `remindersent` VARCHAR(17) DEFAULT 'N'"); echo $modifyoutput; flush();
            modify_database("","ALTER TABLE ".$sv[0]." ADD `remindercount` INT(11)  DEFAULT 0"); echo $modifyoutput; flush();
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
            modify_database("","ALTER TABLE ".$sv[0]." ADD `validfrom` Datetime"); echo $modifyoutput; flush();
            modify_database("","ALTER TABLE ".$sv[0]." ADD `validuntil` Datetime"); echo $modifyoutput; flush();
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
                echo $modifyoutput; flush();
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
                            echo $modifyoutput; flush();
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
    $surveyidquery = db_select_tables_like($dbprefix."survey_%");
    $surveyidresult = db_execute_num($surveyidquery);
    if (!$surveyidresult) {return "Database Error";}
    else
    {
        while ( $sv = $surveyidresult->FetchRow() )
        {
            if (strpos($sv[0],$dbprefix."survey_")!==false)
            {
                modify_database("","ALTER TABLE ".$sv[0]." ADD `lastpage` integer"); echo $modifyoutput; flush();
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
            }
            if (($row['type']=='M' || $row['type']=='P') && $row['default_value']=='Y')
            {
                modify_database("","INSERT INTO {$dbprefix}defaultvalues (qid, sqid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},{$iSaveSQID},0,".db_quoteall($row['language']).",'','Y')"); echo $modifyoutput; flush();
            }
        }
    }
    modify_database("","delete {$dbprefix}answers from {$dbprefix}answers LEFT join {$dbprefix}questions ON {$dbprefix}answers.qid={$dbprefix}questions.qid where {$dbprefix}questions.type in ('1','A','B','C','E','F','H',';',':')"); echo $modifyoutput; flush();

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
                modify_database("","INSERT INTO {$dbprefix}answers (qid, code, answer, sortorder, language) VALUES ({$row['qid']},".db_quoteall($lrow['code']).",".db_quoteall($lrow['title']).",{$lrow['sortorder']},".db_quoteall($lrow['language']).")"); echo $modifyoutput; flush();
                //$labelids[]
            }
            if ($row['type']=='1')
            {
                $labelquery="Select * from {$dbprefix}labels where lid={$row['lid1']} and language=".db_quoteall($row['language']);
                $labelresult = db_execute_assoc($labelquery);
                while ( $lrow = $labelresult->FetchRow() )
                {
                    modify_database("","INSERT INTO {$dbprefix}answers (qid, code, answer, sortorder, language, scale_id) VALUES ({$row['qid']},".db_quoteall($lrow['code']).",".db_quoteall($lrow['title']).",{$lrow['sortorder']},".db_quoteall($lrow['language']).",1)"); echo $modifyoutput; flush();
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
                }                
            }
        }
    }

    // convert Ranking question type

    $query="SELECT a.qid, count(a.qid) as answercount FROM {$dbprefix}answers a, {$dbprefix}questions q where a.qid=q.qid and q.type='R' group by a.qid";
    $queryresult = db_execute_assoc($query);
    if (!$queryresult)
    {
        return "Database Error";
    }
    else
    {
        while ( $row = $queryresult->FetchRow() )
        {
            modify_database("","INSERT INTO {$dbprefix}question_attributes (qid, attribute, value) VALUES ({$row['qid']},'ranking_slots',".db_quoteall($row['answercount']).")"); echo $modifyoutput; flush();
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
