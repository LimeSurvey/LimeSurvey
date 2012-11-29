<?php
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *
 */
/**
 * emailtemplates
 *
 * @package LimeSurvey
 * @copyright 2011
 * @version $Id$
 * @access public
 */

class emailtemplates extends Survey_Common_Action {

    /**
     * Load edit email template screen.
     * @param mixed $iSurveyId
     * @return
     */
    function index($iSurveyId)
    {
        $clang = $this->getController()->lang;
        $iSurveyId = sanitize_int($iSurveyId);
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('adminstyleurl')."superfish.css");

        Yii::app()->loadHelper('admin.htmleditor');
        Yii::app()->loadHelper('surveytranslator');

        Yii::app()->session['FileManagerContext'] = "edit:emailsettings:{$iSurveyId}";

        if(isset($iSurveyId) && getEmailFormat($iSurveyId) == 'html')
        {
            $ishtml = true;
        }
        else
        {
            $ishtml = false;
        }

        $grplangs = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
        $baselang = Survey::model()->findByPk($iSurveyId)->language;
        array_unshift($grplangs,$baselang);

        $sEditScript = PrepareEditorScript(false, $this->getController());
        $aData['attrib'] = array();
        $aData['bplangs'] = array();
        $aData['defaulttexts'] = array();
        if ($ishtml)
        {
            $sEscapeMode='html';
        }
        else
        {
            $sEscapeMode='unescaped';
        }
        foreach ($grplangs as $key => $grouplang)
        {
            $aData['bplangs'][$key] = new limesurvey_lang($grouplang);
            $aData['attrib'][$key] = Surveys_languagesettings::model()->find('surveyls_survey_id = :ssid AND surveyls_language = :ls', array(':ssid' => $iSurveyId, ':ls' => $grouplang));
            $aData['defaulttexts'][$key] = templateDefaultTexts($aData['bplangs'][$key],$sEscapeMode);
        }

        $aData['surveyid'] = $iSurveyId;
        $aData['ishtml'] = $ishtml;
        $aData['grplangs'] = $grplangs;
        $this->_renderWrappedTemplate('emailtemplates', array('output' => $sEditScript, 'emailtemplates_view'), $aData);
    }

    /**
     * Function responsible to process any change in email template.
     * @return
     */
    function update($iSurveyId)
    {
        $clang = $this->getController()->lang;
        if (hasSurveyPermission($iSurveyId, 'surveylocale','update'))
        {
            $languagelist = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
            $languagelist[] = Survey::model()->findByPk($iSurveyId)->language;
            array_filter($languagelist);
            foreach ($languagelist as $langname)
            {
                $attributes = array(
                        'surveyls_email_invite_subj' => $_POST['email_invite_subj_'.$langname],
                        'surveyls_email_invite' => $_POST['email_invite_'.$langname],
                        'surveyls_email_remind_subj' => $_POST['email_remind_subj_'.$langname],
                        'surveyls_email_remind' => $_POST['email_remind_'.$langname],
                        'surveyls_email_register_subj' => $_POST['email_register_subj_'.$langname],
                        'surveyls_email_register' => $_POST['email_register_'.$langname],
                        'surveyls_email_confirm_subj' => $_POST['email_confirm_subj_'.$langname],
                        'surveyls_email_confirm' => $_POST['email_confirm_'.$langname],
                        'email_admin_notification_subj' => $_POST['email_admin_notification_subj_'.$langname],
                        'email_admin_notification' => $_POST['email_admin_notification_'.$langname],
                        'email_admin_responses_subj' => $_POST['email_admin_responses_subj_'.$langname],
                        'email_admin_responses' => $_POST['email_admin_responses_'.$langname]
                        );
                $usquery = Surveys_languagesettings::model()->updateAll($attributes,'surveyls_survey_id = :ssid AND surveyls_language = :sl', array(':ssid' => $iSurveyId, ':sl' => $langname));
            }
            Yii::app()->session['flashmessage'] = $clang->gT("Email templates successfully saved.");
        }
        $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$iSurveyId));
    }


    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'emailtemplates', $aViewUrls = array(), $aData = array())
	{
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . 'emailtemplates.js');

        $aData['display']['menu_bars']['surveysummary'] = 'editemailtemplates';

        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

}
