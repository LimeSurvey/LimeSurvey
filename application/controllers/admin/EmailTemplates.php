<?php

/*
 * LimeSurvey
 * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
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
 * Class emailtemplates
 *
 * @package LimeSurvey
 * @copyright 2011
 * @access public
 */
class EmailTemplates extends SurveyCommonAction
{
    /**
     * Load edit email template screen.
     * @param mixed $iSurveyId
     * @return void
     */
    public function index($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $survey = Survey::model()->findByPk($iSurveyId);

        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'surveylocale', 'read')) {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."), 'error');
            $this->getController()->redirect(array('surveyAdministration/view', 'surveyid' => $iSurveyId));
        }

        Yii::app()->loadHelper('admin.htmleditor');
        Yii::app()->loadHelper('surveytranslator');

        Yii::app()->session['FileManagerContext'] = "edit:emailsettings:{$iSurveyId}";
        initKcfinder();

        if (isset($iSurveyId) && getEmailFormat($iSurveyId) == 'html') {
            $ishtml = true;
        } else {
            $ishtml = false;
        }

        $grplangs = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
        $baselang = Survey::model()->findByPk($iSurveyId)->language;
        array_unshift($grplangs, $baselang);

        $sEditScript = PrepareEditorScript(false, $this->getController());
        $aData['attrib'] = array();
        $aData['bplangs'] = array();
        $aData['defaulttexts'] = array();
        if ($ishtml) {
            $sEscapeMode = 'html';
        } else {
            $sEscapeMode = 'unescaped';
        }
        $uploadDir = realpath(Yii::app()->getConfig('uploaddir'));
        foreach ($grplangs as $key => $grouplang) {
            $aData['bplangs'][$key] = $grouplang;
            $aData['attrib'][$key] = SurveyLanguageSetting::model()->find('surveyls_survey_id = :ssid AND surveyls_language = :ls', array(':ssid' => $iSurveyId, ':ls' => $grouplang));
            $attachments = $aData['attrib'][$key]['attachments'];
            if (is_string($attachments)) {
                $attachments = unserialize($attachments);
            }
            if (is_array($attachments)) {
                foreach ($attachments as &$template) {
                    foreach ($template as &$attachment) {
                        // If the file is missing we add an error message
                        if (!LimeMailer::attachmentExist($iSurveyId, $attachment, false)) {
                            $attachment['error'] = gT("File not found.");
                        }
                        // For security reasons we don't include the full upload path in the frontend
                        if (substr((string) $attachment['url'], 0, strlen($uploadDir)) == $uploadDir) {
                            $attachment['url'] = str_replace('\\', '/', substr((string) $attachment['url'], strlen($uploadDir)));
                        }
                    }
                }
            }
            $aData['attrib'][$key]['attachments'] = $attachments;
            $aData['defaulttexts'][$key] = templateDefaultTexts($aData['bplangs'][$key], $sEscapeMode);
        }

        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title . " (" . gT("ID") . ":" . $iSurveyId . ")";

        $aData['surveyid'] = $iSurveyId;
        $aData['subaction'] = gT("Edit email templates");
        $aData['ishtml'] = $ishtml;
        $aData['grplangs'] = $grplangs;
        $aData['showSaveButton'] = true;
        $topbarData = TopbarConfiguration::getSurveyTopbarData($iSurveyId);
        $topbarData = array_merge($topbarData, $aData);
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
            $topbarData,
            true
        );

        App()->getClientScript()->registerPackage('expressionscript');

        $this->renderWrappedTemplate('emailtemplates', array('output' => $sEditScript, 'emailtemplates_view'), $aData);
    }

    /**
     * Function responsible to process any change in email template.
     * @return void
     */
    public function update($iSurveyId)
    {
        $sBaseUrl = Yii::app()->getBaseUrl();
        $uploadUrl = Yii::app()->getConfig('uploadurl');
        if (substr((string) $uploadUrl, 0, strlen((string) $sBaseUrl)) == $sBaseUrl) {
            $uploadUrl = substr((string) $uploadUrl, strlen((string) $sBaseUrl));
        }
        $sBaseAbsoluteUrl = Yii::app()->getBaseUrl(true);
        $sPublicUrl = Yii::app()->getConfig("publicurl");
        $aPublicUrl = parse_url((string) $sPublicUrl);
        if (isset($aPublicUrl['scheme']) && isset($aPublicUrl['host'])) {
            $sBaseAbsoluteUrl = $sPublicUrl;
        }
        $uploadUrl = trim((string) $sBaseAbsoluteUrl, "/") . $uploadUrl;
        // We need the real path since we check that the resolved file name starts with this path.
        $uploadDir = realpath(Yii::app()->getConfig('uploaddir'));
        $sSaveMethod = Yii::app()->request->getPost('save', '');
        if (Permission::model()->hasSurveyPermission($iSurveyId, 'surveylocale', 'update') && $sSaveMethod != '') {
            $languagelist = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
            $languagelist[] = Survey::model()->findByPk($iSurveyId)->language;
            array_filter($languagelist);
            foreach ($languagelist as $langname) {
                if (isset($_POST['attachments'][$langname])) {
                    foreach ($_POST['attachments'][$langname] as $template => &$attachments) {
                        foreach ($attachments as $index => &$attachment) {
                            // We again take the real path.
                            $localName = realpath(urldecode($uploadDir . str_replace($uploadUrl, '', (string) $attachment['url'])));
                            if ($localName !== false) {
                                if (strpos($localName, $uploadDir) === 0) {
                                    $attachment['url'] = $localName;
                                    $attachment['size'] = filesize($localName);
                                } else {
                                    unset($attachments[$index]);
                                }
                            } else {
                                unset($attachments[$index]);
                            }
                        }
                        unset($attachments);
                    }
                } else {
                    $_POST['attachments'][$langname] = array();
                }

                $attributes = array(
                    'surveyls_email_invite_subj' => $_POST['email_invitation_subj_' . $langname],
                    'surveyls_email_invite' => $_POST['email_invitation_' . $langname],
                    'surveyls_email_remind_subj' => $_POST['email_reminder_subj_' . $langname],
                    'surveyls_email_remind' => $_POST['email_reminder_' . $langname],
                    'surveyls_email_register_subj' => $_POST['email_registration_subj_' . $langname],
                    'surveyls_email_register' => $_POST['email_registration_' . $langname],
                    'surveyls_email_confirm_subj' => $_POST['email_confirmation_subj_' . $langname],
                    'surveyls_email_confirm' => $_POST['email_confirmation_' . $langname],
                    'email_admin_notification_subj' => $_POST['email_admin_notification_subj_' . $langname],
                    'email_admin_notification' => $_POST['email_admin_notification_' . $langname],
                    'email_admin_responses_subj' => $_POST['email_admin_detailed_notification_subj_' . $langname],
                    'email_admin_responses' => $_POST['email_admin_detailed_notification_' . $langname],
                    'attachments' => serialize($_POST['attachments'][$langname])
                );

                $aLanguageSetting = SurveyLanguageSetting::model()->find('surveyls_survey_id = :ssid AND surveyls_language = :sl', array(':ssid' => $iSurveyId, ':sl' => $langname));
                $aLanguageSetting->setAttributes($attributes);
                if (!$aLanguageSetting->save()) {
                    $sErrors = '<br/>';
                    foreach ($aLanguageSetting->getErrors() as $sError) {
                        $sErrors .= $sError[0] . '<br/>';
                    }
                    Yii::app()->setFlashMessage(sprintf(gT("Failed to update email templates. Message: %s"), $sErrors), 'error');
                    $this->getController()->redirect(array('admin/emailtemplates/sa/index/surveyid/' . $iSurveyId));
                }
            }
            Yii::app()->session['flashmessage'] = gT("Email templates successfully saved.");
            if (Yii::app()->request->getPost('close-after-save') == 'true') {
                $this->getController()->redirect(array('surveyAdministration/view/surveyid/' . $iSurveyId));
            }

            $this->getController()->redirect(array('admin/emailtemplates/sa/index/surveyid/' . $iSurveyId));
        }
        self::index($iSurveyId);
    }

    public static function getTemplateTypes()
    {
        return [
        'invitation',
        'reminder',
        'confirmation',
        'registration',
        'admin_notification',
        'admin_detailed_notification'
        ];
    }

    public static function getTabTypeArray($iSurveyId, $language = null)
    {
        $oSurvey = Survey::model()->findByPk($iSurveyId);

        $language = $language == null ? $oSurvey->language : $language;

        $aDefaultTexts = LsDefaultDataSets::getTemplateDefaultTexts('html', $language);

        $array = array(
            'invitation' => array(
                'title' => gT("Invitation"),
                'subject' => gT("Invitation email subject:"),
                'body' => gT("Invitation email body:"),
                'attachments' => gT("Invitation attachments:"),
                'field' => array(
                    'subject' => 'surveyls_email_invite_subj',
                    'body' => 'surveyls_email_invite'
                ),
                'default' => array(
                    'subject' => $aDefaultTexts['invitation_subject'],
                    'body' => $aDefaultTexts['invitation']
                )
            ),
            'reminder' => array(
                'title' => gT("Reminder"),
                'subject' => gT("Reminder email subject:"),
                'body' => gT("Reminder email body:"),
                'attachments' => gT("Reminder attachments:"),
                'field' => array(
                    'subject' => 'surveyls_email_remind_subj',
                    'body' => 'surveyls_email_remind'
                ),
                'default' => array(
                    'subject' => $aDefaultTexts['reminder_subject'],
                    'body' => $aDefaultTexts['reminder']
                )
            ),
            'confirmation' => array(
                'title' => gT("Confirmation"),
                'subject' => gT("Confirmation email subject:"),
                'body' => gT("Confirmation email body:"),
                'attachments' => gT("Confirmation attachments:"),
                'field' => array(
                    'subject' => 'surveyls_email_confirm_subj',
                    'body' => 'surveyls_email_confirm'
                ),
                'default' => array(
                    'subject' => $aDefaultTexts['confirmation_subject'],
                    'body' => $aDefaultTexts['confirmation'],
                )
            ),
            'registration' => array(
                'title' => gT("Registration"),
                'subject' => gT("Registration email subject:"),
                'body' => gT("Registration email body:"),
                'attachments' => gT("Registration attachments:"),
                'field' => array(
                    'subject' => 'surveyls_email_register_subj',
                    'body' => 'surveyls_email_register'
                ),
                'default' => array(
                    'subject' => $aDefaultTexts['registration_subject'],
                    'body' => $aDefaultTexts['registration'],
                )
            ),
            'admin_notification' => array(
                'title' => gT("Basic admin notification"),
                'subject' => gT("Basic admin notification subject:"),
                'body' => gT("Basic admin notification email body:"),
                'attachments' => gT("Basic notification attachments:"),
                'field' => array(
                    'subject' => 'email_admin_notification_subj',
                    'body' => 'email_admin_notification'
                ),
                'default' => array(
                    'subject' => $aDefaultTexts['admin_notification_subject'],
                    'body' => $aDefaultTexts['admin_notification'],
                )
            ),
            'admin_detailed_notification' => array(
                'title' => gT("Detailed admin notification"),
                'subject' => gT("Detailed admin notification subject:"),
                'body' => gT("Detailed admin notification email body:"),
                'attachments' => gT("Detailed notification attachments:"),
                'field' => array(
                    'subject' => 'email_admin_responses_subj',
                    'body' => 'email_admin_responses'
                ),
                'default' => array(
                    'subject' => $aDefaultTexts['admin_detailed_notification_subject'],
                    'body' => $aDefaultTexts['admin_detailed_notification'],
                )
            )
        );
        return $array;
    }

    public function getTemplateOfType($type, $language = null, $survey = 0)
    {
        $language = $language ?? App()->getLanguage();
        $oSurvey = Survey::model()->findByPk($survey);
        $aDefaultTexts = LsDefaultDataSets::getTemplateDefaultTexts('unescaped', $language);

        $out = $aDefaultTexts[$type];
        if ($oSurvey->htmlemail == 'Y') {
            $out = nl2br((string) $out);
        }
        echo $out;
        App()->end();
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function renderWrappedTemplate($sAction = 'emailtemplates', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        App()->getClientScript()->registerPackage('emailtemplates');
        $aData['display']['menu_bars']['surveysummary'] = 'editemailtemplates';
        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
