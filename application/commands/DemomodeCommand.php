<?php

/*
* LimeSurvey (tm)
* Copyright (C) 2011-2026 The LimeSurvey Project Team
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
class DemomodeCommand extends CConsoleCommand
{
    /**
     * @return int
     */
    public function run($args)
    {
        if (isset($args) && isset($args[0]) && $args[0] = 'yes') {
            echo "\n###### Restoring installation to demomode #####\n";
            echo "|| Resetting Database\n";
            $this->resetDatabase();
            echo "|| Resetting Files\n";
            $this->resetFiles();
            echo "|| Installing demo surveys\n";
            $this->createDemo();
            echo "##### Done recreating demo state #####\n";
            return 0;
        } else {
            // TODO: a valid error process
            echo 'This CLI command wipes a LimeSurvey installation clean (including all user except for the user ID 1 and user-uploaded content). '
               . 'For security reasons this command can only started if you add the parameter \'yes\' to the command line.';
            return 1;
        }
    }

    private function resetDatabase()
    {
        Yii::import('application.helpers.common_helper', true);
        Yii::import('application.helpers.database_helper', true);

        //Truncate most of the tables
        $truncatableTables = [
            '{{assessments}}',
            '{{answers}}',
            '{{answer_l10ns}}',
            '{{boxes}}',
            '{{conditions}}',
            '{{defaultvalues}}',
            '{{defaultvalue_l10ns}}',
            '{{failed_login_attempts}}',
            '{{groups}}',
            '{{group_l10ns}}',
            '{{labels}}',
            '{{label_l10ns}}',
            '{{labelsets}}',
            '{{participants}}',
            '{{participant_attribute_names}}',
            '{{participant_attribute_names_lang}}',
            '{{participant_attribute_values}}',
            '{{participant_shares}}',
            '{{questions}}',
            '{{question_attributes}}',
            '{{question_l10ns}}',
            '{{quota}}',
            '{{quota_members}}',
            '{{quota_languagesettings}}',
            '{{settings_user}}',
            '{{saved_control}}',
            '{{surveys}}',
            '{{surveys_languagesettings}}',
            '{{survey_links}}',
            '{{templates}}',
            '{{asset_version}}',
            '{{template_configuration}}',
            '{{user_in_groups}}',
            '{{user_groups}}',
            '{{user_in_permissionrole}}',
        ];
        foreach ($truncatableTables as $table) {
            $quotedTable = Yii::app()->db->quoteTableName($table);
            $actquery = "truncate table " . $quotedTable;
            Yii::app()->db->createCommand($actquery)->execute();
        }
        //Now delete the basics in all other tables
        $actquery = "delete from {{permissions}} where uid<>1 or entity<>'global'";
        Yii::app()->db->createCommand($actquery)->execute();
        $actquery = "delete from {{surveys_groupsettings}} where gsid>1";
        Yii::app()->db->createCommand($actquery)->execute();
        $actquery = "update {{surveys_groupsettings}} set template = 'fruity' where gsid=0";
        Yii::app()->db->createCommand($actquery)->execute();
        $actquery = "update {{surveys_groupsettings}} set template = 'inherit' where gsid=1";
        Yii::app()->db->createCommand($actquery)->execute();
        $actquery = "delete from {{surveys_groups}} where gsid>1";
        Yii::app()->db->createCommand($actquery)->execute();
        $actquery = "delete from {{users}} where uid<>1";
        Yii::app()->db->createCommand($actquery)->execute();
        $actquery = "update {{users}} set lang='auto'";
        Yii::app()->db->createCommand($actquery)->execute();
        $actquery = "delete from {{settings_user}} where stg_name LIKE 'last_question'";
        Yii::app()->db->createCommand($actquery)->execute();
        $actquery = "delete from {{settings_user}} where stg_name LIKE 'last_survey'";
        Yii::app()->db->createCommand($actquery)->execute();
        $actquery = "update {{users}} set email = 'test@domain.test', full_name='Administrator'";
        Yii::app()->db->createCommand($actquery)->execute();
        $actquery = "update {{settings_global}} set stg_value='' where stg_name IN ('googleanalyticsapikey','googleMapsAPIKey','googletranslateapikey','ipInfoDbAPIKey','pdfheadertitle','pdfheaderstring')";
        Yii::app()->db->createCommand($actquery)->execute();
        $actquery = "update {{settings_global}} set stg_value='test@domain.test' where stg_name='siteadminbounce' or stg_name='siteadminemail'";
        Yii::app()->db->createCommand($actquery)->execute();
        $actquery = "update {{settings_global}} set stg_value='Administrator' where stg_name='siteadminname'";
        Yii::app()->db->createCommand($actquery)->execute();
        $actquery = "update {{settings_global}} set stg_value='Sea_Green' where stg_name='admintheme'";
        Yii::app()->db->createCommand($actquery)->execute();
        $actquery = "update {{settings_global}} set stg_value='' where stg_name='restrictToLanguages'";
        Yii::app()->db->createCommand($actquery)->execute();

        $surveyidresult = dbGetTablesLike("tokens%");
        foreach ($surveyidresult as $sv) {
            Yii::app()->db->createCommand("drop table " . $sv)->execute();
        }

        $surveyidresult = dbGetTablesLike("old\_%");
        foreach ($surveyidresult as $sv) {
            Yii::app()->db->createCommand("drop table " . $sv)->execute();
        }

        $surveyidresult = dbGetTablesLike("survey\_%");
        foreach ($surveyidresult as $sv) {
            if (strpos((string) $sv, 'survey_links') === false && strpos((string) $sv, 'survey_url_parameters') === false) {
                                Yii::app()->db->createCommand("drop table " . $sv)->execute();
            }
        }

        //Add the general boxes again
        foreach ($templateData = LsDefaultDataSets::getBoxesData() as $boxes) {
            Yii::app()->db->createCommand()->insert("{{boxes}}", $boxes);
        }
        // At last reset the basic themes
        foreach ($templateData = LsDefaultDataSets::getTemplatesData() as $template) {
            Yii::app()->db->createCommand()->insert("{{templates}}", $template);
        }
        foreach ($templateConfigurationData = LsDefaultDataSets::getTemplateConfigurationData() as $templateConfiguration) {
            Yii::app()->db->createCommand()->insert("{{template_configuration}}", $templateConfiguration);
        }
    }

    private function resetFiles()
    {

        $sBaseUploadDir = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'upload';

        SureRemoveDir($sBaseUploadDir . DIRECTORY_SEPARATOR . 'surveys', false, ['index.html']);
        SureRemoveDir($sBaseUploadDir . DIRECTORY_SEPARATOR . 'global', false, ['index.html']);
        SureRemoveDir($sBaseUploadDir . DIRECTORY_SEPARATOR . 'templates', false);
        SureRemoveDir($sBaseUploadDir . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'survey', false, ['index.html']);
        SureRemoveDir($sBaseUploadDir . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'question', false);
    }

    private function createDemo()
    {
        Yii::app()->loadHelper('admin.import');
        require_once(dirname(dirname(dirname(__FILE__))) . '/application/helpers/replacements_helper.php');
        require_once(dirname(dirname(dirname(__FILE__))) . '/application/helpers/expressions/em_manager_helper.php');
        require_once(dirname(dirname(dirname(__FILE__))) . '/application/helpers/expressions/em_core_helper.php');
        require_once(dirname(dirname(dirname(__FILE__))) . '/application/helpers/admin/activate_helper.php');

        Yii::app()->session->add('loginID', 1);
        $documentationSurveyPath = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'demosurveys' . DIRECTORY_SEPARATOR;
        $aSamplesurveys = scandir($documentationSurveyPath);
        $surveysToActivate = [];
        foreach ($aSamplesurveys as $sSamplesurvey) {
            $result = null;
            if ($sSamplesurvey[0] == '.') {
                continue;
            }
            echo "Importing {$sSamplesurvey}\n";
            //Try catch for console application to be able to import surveys

            $result = @ XMLImportSurvey($documentationSurveyPath . $sSamplesurvey);

            if (in_array($sSamplesurvey, ['ls205_sample_survey_multilingual.lss', 'ls205_randomization_group_test.lss', 'ls205_cascading_array_filter_exclude.lss'])) {
                $surveysToActivate[] = $result['newsid'];
            }
        }
        //require_once(__DIR__ . '/../helpers/admin/activate_helper.php');
        foreach ($surveysToActivate as $surveyID) {
            $survey = \Survey::model()->findByPk($surveyID);
            $surveyActivator = new SurveyActivator($survey);
            $result = $surveyActivator->activate();
        }
    }
}

function SureRemoveDir($dir, $DeleteMe, $excludes = [])
{
    if (!$dh = @opendir($dir)) {
        return;
    }
    while (false !== ($obj = readdir($dh))) {
        if ($obj == '.' || $obj == '..' || in_array($obj, $excludes)) {
            continue;
        }
        if (!@unlink($dir . '/' . $obj)) {
            SureRemoveDir($dir . '/' . $obj, true);
        }
    }
    closedir($dh);
    if ($DeleteMe) {
        if (!@rmdir($dir)) {
            echo "Error: could not delete " . $dir;
        }
    }
}
