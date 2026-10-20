<?php

use LimeSurvey\PluginManager\AuthPluginBase;

/*
* LimeSurvey
* Copyright (C) 2013-2026 The LimeSurvey Project Team
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
     * Default subaction: there is no user overview page, so redirect to the personal settings
     *
     * @return void
     */
    public function index()
    {
        $this->getController()->redirect(array("admin/user/sa/personalsettings"));
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

                if ($newPassword !== '' || $repeatPassword !== '') {
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
                    Yii::app()->setFlashMessage(gT("You can't change your email address if demo mode is active."), 'error');
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
                // Keep this session valid: it just rotated its own session
                // token (see User::setPassword()), so the cached value used
                // by LSApplicationTrait::getCurrentUserId() must be refreshed
                // or this same session would be logged out on its next request.
                Yii::app()->session['session_token'] = $oUserModel->session_token;
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
                SettingsUser::setUserSetting('answeroptionprefix', Yii::app()->request->getPost('answeroptionprefix'));
                SettingsUser::setUserSetting('subquestionprefix', Yii::app()->request->getPost('subquestionprefix'));
                SettingsUser::setUserSetting('lock_organizer', Yii::app()->request->getPost('lock_organizer'));
                SettingsUser::setUserSetting('createsample', Yii::app()->request->getPost('createsample'));
                SettingsUser::setUserSetting('breadcrumbMode', Yii::app()->request->getPost('breadcrumbMode'));
                
                // Validate and save displayTimezone: accept empty string or valid timezone identifier
                $postTimezone = Yii::app()->request->getPost('displayTimezone', '');
                $displayTimezone = is_string($postTimezone) ? trim($postTimezone) : '';
                if ($displayTimezone === '' || in_array($displayTimezone, timezone_identifiers_list(), true)) {
                    SettingsUser::setUserSetting('displayTimezone', $displayTimezone);
                }
                // If invalid timezone, silently skip saving (preserves existing setting)

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
        $aData['sEmailAddress'] = $oUser->email;
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

        //Get data for personal menus
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

        $globalDisplayTimezone = SettingGlobal::model()->findByPk('displayTimezone');
        $aData['globalDisplayTimezone'] = ($globalDisplayTimezone && !empty($globalDisplayTimezone->stg_value)) ? $globalDisplayTimezone->stg_value : 'UTC';

        $aData['currentPreselectedQuestiontype'] = $currentPreselectedQuestiontype;
        $aData['currentPreselectedQuestionTheme'] = $currentPreselectedQuestionTheme;
        $aData['aUserSettings'] = $aUserSettings;
        $aData['aQuestionTypeList'] = QuestionTheme::findAllQuestionMetaDataForSelector();
        $aData['selectedQuestion'] = QuestionTheme::findQuestionMetaData($currentPreselectedQuestiontype, $currentPreselectedQuestionTheme);

        $aData['surveymenu_data']['model'] = $oSurveymenu;
        $aData['surveymenuentry_data']['model'] = $oSurveymenuEntries;

        // API tokens, the token created just before is shown once
        $aData['apiTokens'] = UserApiToken::model()->findAllByAttributes(['uid' => $oUser->uid], ['order' => 'created DESC']);
        $aData['newApiToken'] = Yii::app()->session['newApiToken'] ?? null;
        unset(Yii::app()->session['newApiToken']);
        // Render personal settings view
        if (isset($_POST['saveandclose'])) {
            $this->getController()->redirect(array("admin/user/sa/index"));
        } else {
            $this->renderWrappedTemplate('user', 'personalsettings', $aData);
        }
    }

    /**
     * Create or edit an API token of the current user, including its permissions
     *
     * A new token is shown once on the personal settings page after it was created.
     *
     * @param int $tokenid ID of the token to edit, 0 to create a new token
     * @return void
     * @throws CHttpException If the token doesn't exist or belongs to another user
     */
    public function apitoken($tokenid = 0)
    {
        $apiToken = $this->getApiTokenForCurrentUser((int) $tokenid);
        $request = App()->request;

        if ($request->getIsPostRequest()) {
            $personalSettingsUrl = App()->createUrl('admin/user/sa/personalsettings') . '#api-tokens';
            if (App()->getConfig('demoMode')) {
                App()->setFlashMessage(gT("You can't create or change API tokens if demo mode is active."), 'error');
                $this->getController()->redirect($personalSettingsUrl);
            }
            $name = trim((string) $request->getPost('name', ''));
            if ($name === '') {
                App()->setFlashMessage(gT("Please enter a name for the API token."), 'error');
                $this->getController()->redirect(['admin/user/sa/apitoken', 'tokenid' => (int) $apiToken->id]);
            }
            $permissions = $request->getPost('ApiTokenPermission', []);
            $transaction = App()->db->beginTransaction();
            try {
                if ($apiToken->isNewRecord) {
                    $lifetime = (int) $request->getPost('lifetime', 90);
                    if (!in_array($lifetime, UserApiToken::LIFETIMES, true)) {
                        $lifetime = 90;
                    }
                    [$apiToken, $plainToken] = UserApiToken::createForUser($apiToken->uid, $name, $lifetime);
                } else {
                    $apiToken->name = $name;
                    if (!$apiToken->save()) {
                        throw new CException(CHtml::errorSummary($apiToken, ''));
                    }
                }
                $apiToken->setPermissions(is_array($permissions) ? $permissions : []);
                $transaction->commit();
            } catch (CException $exception) {
                $transaction->rollback();
                App()->setFlashMessage(gT("The API token could not be saved."), 'error');
                $this->getController()->redirect($personalSettingsUrl);
            }
            if (isset($plainToken)) {
                App()->session['newApiToken'] = $plainToken;
                App()->setFlashMessage(gT("The API token was created."));
            } else {
                App()->setFlashMessage(gT("The API token was saved."));
            }
            $this->getController()->redirect($personalSettingsUrl);
        }

        $globalPermissions = $apiToken->getPermissionGrid('global');
        $surveyPermissions = [];
        if (!$apiToken->isNewRecord) {
            foreach ($apiToken->getSurveysWithPermissions() as $survey) {
                $surveyPermissions[$survey->sid] = [
                    'survey' => $survey,
                    'permissions' => $apiToken->getPermissionGrid('survey', $survey->sid),
                ];
            }
        }
        $aData = [
            'apiToken' => $apiToken,
            'globalPermissions' => $globalPermissions,
            'surveyPermissions' => $surveyPermissions,
            'surveyOptions' => Survey::model()->permission(App()->user->id)->with('languagesettings')->findAll(['order' => 't.sid']),
        ];
        $aData['topbar']['title'] = $apiToken->isNewRecord ? gT('Create API token') : gT('Edit API token');
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/layouts/partial_topbar/right_close_saveclose_save',
            [
                'isCloseBtn' => true,
                'backUrl' => App()->createUrl('admin/user/sa/personalsettings') . '#api-tokens',
                'isSaveBtn' => true,
                'isSaveAndCloseBtn' => false,
                'formIdSave' => 'apitoken-form',
            ],
            true
        );
        $this->renderWrappedTemplate('user', 'apitoken', $aData);
    }

    /**
     * Render the permission grid of a survey for the API token form, when a survey is added to the token
     *
     * @param int $surveyid The survey ID
     * @return void
     * @throws CHttpException If the survey doesn't exist or the user can't read it
     */
    public function apitokensurveypermissions($surveyid = 0)
    {
        $survey = Survey::model()->findByPk((int) $surveyid);
        if (!$survey || !Permission::model()->hasSurveyPermission($survey->sid, 'survey', 'read')) {
            throw new CHttpException(404, gT("The survey you selected does not exist"));
        }
        $apiToken = new UserApiToken();
        $apiToken->uid = (int) App()->user->id;
        $this->getController()->renderPartial('/admin/user/_apitokensurvey', [
            'survey' => $survey,
            'permissions' => $apiToken->getPermissionGrid('survey', $survey->sid),
        ]);
    }

    /**
     * Revoke an API token of the current user. Sessions opened with the token end immediately.
     *
     * @return void
     * @throws CHttpException If the request is not a POST request, or the token doesn't exist or belongs to another user
     */
    public function revokeapitoken()
    {
        if (!App()->request->getIsPostRequest()) {
            throw new CHttpException(405, gT("Invalid action"));
        }
        $apiToken = $this->getApiTokenForCurrentUser((int) App()->request->getPost('tokenid'));
        if ($apiToken->isNewRecord || !$apiToken->delete()) {
            App()->setFlashMessage(gT("The API token could not be revoked."), 'error');
        } else {
            App()->setFlashMessage(gT("The API token was revoked."));
        }
        $this->getController()->redirect(App()->createUrl('admin/user/sa/personalsettings') . '#api-tokens');
    }

    /**
     * Get an API token of the current user, or a new unsaved one
     *
     * @param int $tokenId ID of the token, 0 for a new token
     * @return UserApiToken The token
     * @throws CHttpException If the token doesn't exist or belongs to another user
     */
    private function getApiTokenForCurrentUser($tokenId)
    {
        $userId = (int) App()->user->id;
        if ($tokenId === 0) {
            $apiToken = new UserApiToken();
            $apiToken->uid = $userId;
            return $apiToken;
        }
        $apiToken = UserApiToken::model()->findByAttributes(['id' => $tokenId, 'uid' => $userId]);
        if ($apiToken === null) {
            throw new CHttpException(404, gT("API token not found."));
        }
        return $apiToken;
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
