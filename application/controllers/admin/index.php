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
 */
class Index extends SurveyCommonAction
{
    public function run()
    {
        App()->loadHelper('surveytranslator');
        $aData = [];
        $aData['issuperadmin'] = false;
        if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $aData['issuperadmin'] = true;
        }

        // display old dashboard interface
        $aData['oldDashboard'] = App()->getConfig('display_old_dashboard') === '1';
        // We get the last survey visited by user
        $setting_entry = 'last_survey_' . Yii::app()->user->getId();
        $lastsurvey = getGlobalSetting($setting_entry);
        if ($lastsurvey) {
            try {
                $survey = Survey::model()->findByPk($lastsurvey);
                if ($survey) {
                    $aData['showLastSurvey'] = true;
                    $iSurveyID = $lastsurvey;
                    $aData['surveyTitle'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyID . ")";
                    $aData['surveyUrl'] = $this->getController()->createUrl("surveyAdministration/view/surveyid/{$iSurveyID}");
                } else {
                    $aData['showLastSurvey'] = false;
                }
            } catch (Exception $e) {
                $aData['showLastSurvey'] = false;
            }
        } else {
            $aData['showLastSurvey'] = false;
        }

        // We get the last question visited by user
        $setting_entry = 'last_question_' . Yii::app()->user->getId();
        $lastquestion = getGlobalSetting($setting_entry);

        // the question group of this question
        $setting_entry = 'last_question_gid_' . Yii::app()->user->getId();
        $lastquestiongroup = getGlobalSetting($setting_entry);

        // the sid of this question : last_question_sid_1
        $setting_entry = 'last_question_sid_' . Yii::app()->user->getId();
        $lastquestionsid = getGlobalSetting($setting_entry);
        if ($lastquestion && $lastquestiongroup && $lastquestionsid) {
            $survey = Survey::model()->findByPk($lastquestionsid);
            if ($survey) {
                $baselang = $survey->language;
                $aData['showLastQuestion'] = true;
                $qid = $lastquestion;
                $gid = $lastquestiongroup;
                $sid = $lastquestionsid;
                $qrrow = Question::model()->findByAttributes(array('qid' => $qid, 'gid' => $gid, 'sid' => $sid));
                if ($qrrow) {
                    $aData['last_question_name'] = $qrrow['title'];
                    if (!empty($qrrow->questionl10ns[$baselang]['question'])) {
                        $aData['last_question_name'] .= ' : ' . $qrrow->questionl10ns[$baselang]['question'];
                    }
                    $aData['last_question_link'] = $this->getController()->createUrl("questionAdministration/view/surveyid/$sid/gid/$gid/qid/$qid");
                } else {
                    $aData['showLastQuestion'] = false;
                }
            } else {
                $aData['showLastQuestion'] = false;
            }
        } else {
            $aData['showLastQuestion'] = false;
        }

        $aData['countSurveyList'] = Survey::model()->count();

        //show banner after welcome logo
        $event = new PluginEvent('beforeWelcomePageRender');
        App()->getPluginManager()->dispatchEvent($event);
        $belowLogoHtml = $event->get('html');

        // We get the home page display setting
        $aData['bShowSurveyList'] = (getGlobalSetting('show_survey_list') == "show");
        $aData['bShowSurveyListSearch'] = (getGlobalSetting('show_survey_list_search') == "show");
        $aData['bShowLogo'] = (getGlobalSetting('show_logo') == "show");
        $aData['oSurveySearch'] = new Survey('search');
        $aData['bShowLastSurveyAndQuestion'] = (getGlobalSetting('show_last_survey_and_question') == "show");
        $aData['iBoxesByRow'] = (int) getGlobalSetting('boxes_by_row');
        $aData['sBoxesOffSet'] = (int) getGlobalSetting('boxes_offset');
        $aData['bBoxesInContainer'] = (getGlobalSetting('boxes_in_container') == 'yes');
        $aData['belowLogoHtml'] = $belowLogoHtml;
        $this->renderWrappedTemplate('super', 'welcome', $aData);
    }
}
