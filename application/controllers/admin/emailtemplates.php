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
     * Routes to the correct sub-action
     *
     * @access public
     * @return void
     */
    public function run($sa)
    {
        if ($sa == 'edit')
        {
            $this->route('edit', array('surveyid'));
        }
        else if ($sa == 'update')
        {
            $this->route('update', array('surveyid', 'action'));
        }
    }

    /**
     * emailtemplates::edit()
     * Load edit email template screen.
     * @param mixed $surveyid
     * @return
     */
    function edit($surveyid)
    {
        $clang = $this->getController()->lang;
        $surveyid = sanitize_int($surveyid);
        $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
        Yii::app()->setConfig("css_admin_includes", $css_admin_includes);

        Yii::app()->loadHelper('admin.htmleditor');
        Yii::app()->loadHelper('surveytranslator');

        if(isset($surveyid) && getEmailFormat($surveyid) == 'html')
        {
            $ishtml = true;
        }
        else
        {
            $ishtml = false;
        }

        $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($grplangs,$baselang);

        PrepareEditorScript(true, $this->getController());
        $data['attrib'] = array();
        $data['bplangs'] = array();
        $data['defaulttexts'] = array();
        foreach ($grplangs as $key => $grouplang)
        {
            $data['bplangs'][$key] = new limesurvey_lang(array($grouplang));
            $data['attrib'][$key] = Surveys_languagesettings::model()->find('surveyls_survey_id = :ssid AND surveyls_language = :ls', array(':ssid' => $surveyid, ':ls' => $grouplang));
            $data['defaulttexts'][$key] = aTemplateDefaultTexts($data['bplangs'][$key]);
        }
        $data['clang'] = $clang;
        $data['surveyid'] = $surveyid;
        $data['ishtml'] = $ishtml;
        $data['grplangs'] = $grplangs;
        $this->_renderWrappedTemplate('emailtemplates_view', $data);
    }

    /**
     * emailtemplates::update()
     * Function responsible to process any change in email template.
     * @return
     */
    function update($surveyid, $action)
    {
        $clang = $this->getController()->lang;
        if ($action == "updateemailtemplates" && bHasSurveyPermission($surveyid, 'surveylocale','update'))
        {
            $languagelist = GetAdditionalLanguagesFromSurveyID($surveyid);
            $languagelist[] = GetBaseLanguageFromSurveyID($surveyid);
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
                $usquery = Surveys_languagesettings::model()->updateAll($attributes,'surveyls_survey_id = :ssid AND surveyls_language = :sl', array(':ssid' => $surveyid, ':sl' => $langname));
                if ($usquery <= 0)
                    die("Error updating<br />".$usquery."<br /><br />");
            }
            Yii::app()->session['flashmessage'] = $clang->gT("Email templates successfully saved.");
        }
        $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$surveyid));
    }


    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    function _renderWrappedTemplate($aViewUrls = array(), $aData = array())
	{
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . 'emailtemplates.js');

        $aData['display']['menu_bars']['surveysummary'] = 'editemailtemplates';

        parent::_renderWrappedTemplate('emailtemplates', $aViewUrls, $aData);
    }

}