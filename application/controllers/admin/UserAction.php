<?php

use LimeSurvey\PluginManager\AuthPluginBase;

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
*/

/**
* User Controller
*
* This controller performs user actions
*
* @package        LimeSurvey
* @subpackage    Backend
*/
class UserAction extends SurveyCommonAction
{
    /**
     * Constructor
     * @param $controller
     * @param $id
     */
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        Yii::app()->loadHelper('database');
    }

    /**
     * Manage user personal settings
     */
    public function personalsettings()
    {
        // Save Data
        if (Yii::app()->request->getPost("action")) {
            $oUserModel = User::model()->findByPk(Yii::app()->session['loginID']);
            $uresult = true;

            if (Yii::app()->request->getPost('newpasswordshown') == "1") {
                if (Yii::app()->getConfig('demoMode')) {
                    Yii::app()->setFlashMessage(gT("You can't change password if demo mode is active."), 'error');
                    $this->getController()->redirect(array("admin/user/sa/personalsettings"));
                }

                $oldPassword = Yii::app()->request->getPost('oldpassword');

                // Check the current password
                $currentPasswordOk = $oUserModel->checkPassword($oldPassword);
                if (!$currentPasswordOk) {
                    Yii::app()->setFlashMessage(gT('The current password is not correct.'), 'error');
                    $this->getController()->redirect(array("admin/user/sa/personalsettings"));
                }

                $newPassword = Yii::app()->request->getPost('password');
                $repeatPassword = Yii::app()->request->getPost('repeatpassword');

                if ($newPassword !== '' && $repeatPassword !== '') {
                    $error = $oUserModel->validateNewPassword($newPassword, $oldPassword ?? '', $repeatPassword);

                    if ($error !== '') {
                        Yii::app()->setFlashMessage(gT($error), 'error');
                        $this->getController()->redirect(array("admin/user/sa/personalsettings"));
                    } else {
                        // We can update
                        $oUserModel->setPassword($newPassword);
                    }
                }
            }

            if (Yii::app()->request->getPost('newemailshown') == "1") {
                if (Yii::app()->getConfig('demoMode')) {
                    Yii::app()->setFlashMessage(gT("You can't change your email adress if demo mode is active."), 'error');
                    $this->getController()->redirect(array("admin/user/sa/personalsettings"));
                }

                $oldPassword = Yii::app()->request->getPost('oldpassword');

                // Check the current password
                $currentPasswordOk = $oUserModel->checkPassword($oldPassword);
                if (!$currentPasswordOk) {
                    Yii::app()->setFlashMessage(gT('The current password is not correct.'), 'error');
                    $this->getController()->redirect(array("admin/user/sa/personalsettings"));
                }

                $oUserModel->email = Yii::app()->request->getPost('newemail');
                $uresult = $oUserModel->save();
            }

            $oUserModel->lang                 = Yii::app()->request->getPost('lang');
            $oUserModel->dateformat           = Yii::app()->request->getPost('dateformat');
            $oUserModel->htmleditormode       = Yii::app()->request->getPost('htmleditormode');
            $oUserModel->questionselectormode = Yii::app()->request->getPost('questionselectormode');
            $oUserModel->templateeditormode   = Yii::app()->request->getPost('templateeditormode');
            $oUserModel->full_name            = Yii::app()->request->getPost('fullname');
            $uresult = $uresult && $oUserModel->save();
            if ($uresult) {
                if (Yii::app()->request->getPost('lang') == 'auto') {
                    $sLanguage = getBrowserLanguage();
                } else {
                    $sLanguage = Yii::app()->request->getPost('lang');
                }
                Yii::app()->session['adminlang'] = $sLanguage;
                Yii::app()->setLanguage($sLanguage);

                Yii::app()->session['htmleditormode'] = Yii::app()->request->getPost('htmleditormode');
                Yii::app()->session['questionselectormode'] = Yii::app()->request->getPost('questionselectormode');
                Yii::app()->session['templateeditormode'] = Yii::app()->request->getPost('templateeditormode');
                Yii::app()->session['dateformat'] = Yii::app()->request->getPost('dateformat');

                SettingsUser::setUserSetting('preselectquestiontype', Yii::app()->request->getPost('preselectquestiontype'));
                SettingsUser::setUserSetting('preselectquestiontheme', Yii::app()->request->getPost('preselectquestiontheme'));
                SettingsUser::setUserSetting('showScriptEdit', Yii::app()->request->getPost('showScriptEdit'));
                SettingsUser::setUserSetting('noViewMode', Yii::app()->request->getPost('noViewMode'));
                SettingsUser::setUserSetting('lock_organizer', Yii::app()->request->getPost('lock_organizer'));
                SettingsUser::setUserSetting('createsample', Yii::app()->request->getPost('createsample'));

                Yii::app()->setFlashMessage(gT("Your personal settings were successfully saved."));
            } else {
                // Show list of error if needed
                Yii::app()->setFlashMessage(CHtml::errorSummary($oUserModel, gT("There was an error when saving your personal settings.")), 'error');
            }

            if (Yii::app()->request->getPost("saveandclose")) {
                $this->getController()->redirect(array("dashboard/view"));
            }
        }

        // Page size
        if (App()->request->getParam('pageSize')) {
            App()->user->setState('pageSize', (int) App()->request->getParam('pageSize'));
        }

        // Get user lang
        $oUser = User::model()->findByPk(Yii::app()->session['loginID']);

        $aLanguageData = array('auto' => gT("(Autodetect)"));
        foreach (getLanguageData(true, Yii::app()->session['adminlang']) as $langkey => $languagekind) {
            $aLanguageData[$langkey] = html_entity_decode($languagekind['nativedescription'] . ' - ' . $languagekind['description'], ENT_COMPAT, 'utf-8');
        }

        $aData = array();
        $aData['aLanguageData'] = $aLanguageData;
        $aData['sSavedLanguage'] = $oUser->lang;
        $aData['sUsername'] = $oUser->users_name;
        $aData['sFullname'] = $oUser->full_name;
        $aData['sEmailAdress'] = $oUser->email;
        $aData['passwordHelpText'] = $oUser->getPasswordHelpText();

        $aData['topbar']['title'] = gT('Account');
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/layouts/partial_topbar/right_close_saveclose_save',
            [
                'isCloseBtn' => true,
                'isSaveBtn' => true,
                'isSaveAndCloseBtn' => true,
                'formIdSave' => 'personalsettings',
                'formIdSaveClose' => 'personalsettings'
            ],
            true
        );

        //Get data for personal menues
        $oSurveymenu = Surveymenu::model();
        $oSurveymenu->user_id = $oUser->uid;
        $oSurveymenuEntries = SurveymenuEntries::model();
        $oSurveymenuEntries->user_id = $oUser->uid;
        $aRawUserSettings = SettingsUser::model()->findAllByAttributes(['uid' => $oUser->uid]);

        $aUserSettings = [];
        array_walk($aRawUserSettings, function ($oUserSetting) use (&$aUserSettings) {
            $aUserSettings[$oUserSetting->stg_name] = $oUserSetting->stg_value;
        });

        $currentPreselectedQuestiontype = array_key_exists('preselectquestiontype', $aUserSettings) ? $aUserSettings['preselectquestiontype'] : App()->getConfig('preselectquestiontype');
        $currentPreselectedQuestionTheme = array_key_exists('preselectquestiontheme', $aUserSettings) ? $aUserSettings['preselectquestiontheme'] : App()->getConfig('preselectquestiontheme');

        $aData['currentPreselectedQuestiontype'] = $currentPreselectedQuestiontype;
        $aData['currentPreselectedQuestionTheme'] = $currentPreselectedQuestionTheme;
        $aData['aUserSettings'] = $aUserSettings;
        $aData['aQuestionTypeList'] = QuestionTheme::findAllQuestionMetaDataForSelector();
        $aData['selectedQuestion'] = QuestionTheme::findQuestionMetaData($currentPreselectedQuestiontype, $currentPreselectedQuestionTheme);

        $aData['surveymenu_data']['model'] = $oSurveymenu;
        $aData['surveymenuentry_data']['model'] = $oSurveymenuEntries;
        // Render personal settings view
        if (isset($_POST['saveandclose'])) {
            $this->getController()->redirect(array("admin/user/sa/index"));
        } else {
            $this->renderWrappedTemplate('user', 'personalsettings', $aData);
        }
    }

     /**
     * Toggle Setting
     * @param int $surveyid
     */
    public function togglesetting($surveyid = 0)
    {
        $setting  = Yii::app()->request->getPost('setting');
        $newValue = Yii::app()->request->getPost('newValue');

        $result = SettingsUser::setUserSetting($setting, $newValue);

        $this->renderJSON([
            "result" => SettingsUser::getUserSettingValue($setting)
        ]);
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string       $sAction     Current action, the folder to fetch views from
     * @param string|array $aViewUrls   View url(s)
     * @param array        $aData       Data to be passed on. Optional.
     * @param bool         $sRenderFile
     */
    protected function renderWrappedTemplate($sAction = 'user', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
