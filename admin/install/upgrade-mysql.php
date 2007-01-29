<?PHP

// There will be a file for each database (accordingly named to the dbADO scheme)
// where based on the current database version the database is upgraded
// For this there will be a settings table which holds the last time the database was upgraded

function db_upgrade($oldversion) {
/// This function does anything necessary to upgrade 
/// older versions to match current functionality 

    if ($oldversion < 109) {
      modify_database("","ALTER TABLE `prefix_answers` ADD `language` varchar(20) default 'en'");
      modify_database("","ALTER TABLE `prefix_questions` ADD `language` varchar(20) default 'en'");
      modify_database("","ALTER TABLE `prefix_groups` ADD `language` varchar(20) default 'en'");
      modify_database("","ALTER TABLE `prefix_labels` ADD `language` varchar(20) default 'en'");
      modify_database("","UPDATE  `prefix_settings_global` SET stg_value='109' where stg_name ='DBVersion'");
    }

    if ($oldversion < 110) {
      modify_database("","ALTER TABLE `prefix_surveys` ADD `additional_languages` varchar(255)");
      modify_database("","DROP TABLE IF EXISTS `prefix_surveys_languagesettings`;");
      modify_database("","CREATE TABLE `prefix_surveys_languagesettings` (
                        `surveyls_survey_id` INT UNSIGNED NOT NULL DEFAULT 0,
                        `surveyls_language` VARCHAR(45) NULL DEFAULT 'en',
                        `surveyls_title` VARCHAR(200) NOT NULL,
                        `surveyls_description` TEXT NULL,
                        `surveyls_welcometext` TEXT NULL,
                        `surveyls_urldescription` VARCHAR(255) NULL,
                        `surveyls_email_invite_subj` VARCHAR(255) NULL,
                        `surveyls_email_invite` TEXT NULL,
                        `surveyls_email_remind_subj` VARCHAR(255) NULL,
                        `surveyls_email_remind` TEXT NULL,
                        `surveyls_email_register_subj` VARCHAR(255) NULL,
                        `surveyls_email_register` TEXT NULL,
                        `surveyls_email_confirm_subj` VARCHAR(255) NULL,
                        `surveyls_email_confirm` TEXT NULL,
                        PRIMARY KEY (`surveyls_survey_id`, `surveyls_language`)
                            )
                    TYPE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");  
      modify_database("","ALTER TABLE `prefix_groups` DROP PRIMARY KEY");     
      modify_database("","ALTER TABLE `prefix_groups` ADD PRIMARY KEY (`gid`,`language`)");     
      modify_database("","ALTER TABLE `prefix_questions` DROP PRIMARY KEY");     
      modify_database("","ALTER TABLE `prefix_questions` ADD PRIMARY KEY (`qid`,`language`)");     
      modify_database("","ALTER TABLE `prefix_answers` DROP PRIMARY KEY");     
      modify_database("","ALTER TABLE `prefix_answers` ADD PRIMARY KEY (`qid`,` code`,`language`)");     

      modify_database("","ALTER TABLE `prefix_labelsets` ADD `languages` varchar(200) default 'en'");
      modify_database("","ALTER TABLE `prefix_labels` MODIFY `sortorder` int(11) NOT NULL");
      modify_database("","ALTER TABLE `prefix_answers` MODIFY `sortorder` int(11) NOT NULL");
                    
      modify_database("","UPDATE  `prefix_settings_global` SET stg_value='110' where stg_name ='DBVersion'");
    }

    
    return true;
}

















?>
