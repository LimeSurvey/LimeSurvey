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

use LimeSurvey\Libraries\FormExtension\Inputs\TextInput;

/**
* GlobalSettings Controller
*
*
* @package        LimeSurvey
* @subpackage    Backend
*/
class GlobalSettings extends SurveyCommonAction
{
    /**
     * GlobalSettings Constructor
     * @param $controller
     * @param $id
     **/
    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        if (!Permission::model()->hasGlobalPermission('settings', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
    }

    /**
     * Shows the index page
     *
     * @access public
     * @return void
     * @throws CHttpException
     */
    public function index()
    {
        if (!empty(Yii::app()->getRequest()->getPost('action'))) {
            $this->saveSettings();
        }
        $this->displaySettings();
    }

    /**
     * Show PHP Info
     */
    public function showphpinfo()
    {
        if (Yii::app()->getConfig('demoMode') || !Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        phpinfo();
    }

    /**
     * Refresh Assets
     */
    public function refreshAssets()
    {
        // Only people who can create or update themes should be allowed to refresh the assets
        if (Permission::model()->hasGlobalPermission('templates', 'create')) {
            SettingGlobal::increaseCustomAssetsversionnumber();
            $this->getController()->redirect(array("admin/globalsettings"));
        }
    }

    /**
     * Displays the settings.
     * @throws CHttpException
     */
    private function displaySettings()
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'read')) {
            throw new CHttpException(403, gT("You do not have permission to access this page."));
        }
        Yii::app()->loadHelper('surveytranslator');
        $data = [];
        $data['title'] = "hi";
        $data['message'] = "message";
        foreach ($this->checkSettings() as $key => $row) {
            $data[$key] = $row;
        }
        Yii::app()->loadLibrary('Date_Time_Converter');
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        $datetimeobj = new Date_Time_Converter(dateShift(getGlobalSetting("updatelastcheck"), 'Y-m-d H:i:s'), 'Y-m-d H:i:s');
        $data['updatelastcheck'] = $datetimeobj->convert($dateformatdetails['phpdate'] . " H:i:s");

        // @todo getGlobalSetting is deprecated!
        $data['updateavailable'] = (getGlobalSetting("updateavailable") && Yii::app()->getConfig("updatable"));
        $data['updatable'] = Yii::app()->getConfig("updatable");
        $data['updateinfo'] = getGlobalSetting("updateinfo");
        $data['updatebuild'] = getGlobalSetting("updatebuild");
        $data['updateversion'] = getGlobalSetting("updateversion");
        $data['aUpdateVersions'] = json_decode((string) getGlobalSetting("updateversions"), true);
        $data['allLanguages'] = getLanguageData(false, Yii::app()->session['adminlang']);
        if (trim((string) Yii::app()->getConfig('restrictToLanguages')) == '') {
            $data['restrictToLanguages'] = array_keys($data['allLanguages']);
            $data['excludedLanguages'] = array();
        } else {
            $data['restrictToLanguages'] = explode(' ', trim((string) Yii::app()->getConfig('restrictToLanguages')));
            $data['excludedLanguages'] = array_diff(array_keys($data['allLanguages']), $data['restrictToLanguages']);
        }

        $data['topbar']['title'] = gT('Global settings');
        $data['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/layouts/partial_topbar/right_close_saveclose_save',
            [
                'isCloseBtn' => true,
                'isSaveAndCloseBtn' => true,
                'isSaveBtn' => true,
                'backUrl' => Yii::app()->createUrl('dashboard/view'),
                'formIdSaveClose' => 'frmglobalsettings',
                'formIdSave' => 'frmglobalsettings'
            ],
            true
        );

        // List of available encodings
        $data['aEncodings'] = aEncodingsArray();

        // Get user administration settings
        $data['sGlobalSendAdminCreationEmail'] = getGlobalSetting('sendadmincreationemail');
        $data['sGlobalAdminCreationEmailTemplate'] = getGlobalSetting('admincreationemailtemplate');
        $data['sGlobalAdminCreationEmailSubject'] = getGlobalSetting('admincreationemailsubject');

        //Prepare editor script for global settings tabs / Textarea fields
        App()->loadHelper("admin.htmleditor");
        $data['scripts'] = PrepareEditorScript(false, $this->getController());

        // Get current setting from DB
        $data['thischaracterset'] = getGlobalSetting('characterset');
        $data['sideMenuBehaviour'] = getGlobalSetting('sideMenuBehaviour');
        $data['aListOfThemeObjects'] = AdminTheme::getAdminThemeList();

        // List of available email plugins
        $event = new PluginEvent('listEmailPlugins', $this);
        Yii::app()->getPluginManager()->dispatchEvent($event);
        $emailPlugins = $event->get('plugins');
        $data['emailPlugins'] = $emailPlugins;

        $this->renderWrappedTemplate('globalsettings', 'globalSettings_view', $data);
    }

    /**
     * Loaded by Ajax when user clicks "Calculate storage".
     * @return void
     */
    public function getStorageData()
    {
        Yii::import('application.helpers.admin.ajax_helper', true);
        $data = array();

        $uploaddir = Yii::app()->getConfig("uploaddir");
        $decimals = 1;

        $data['totalStorage'] = humanFilesize(folderSize($uploaddir), $decimals);
        $data['templateSize'] = humanFilesize(folderSize($uploaddir . '/themes'), $decimals);
        $data['surveySize']   = humanFilesize(folderSize($uploaddir . '/surveys'), $decimals);
        $data['labelSize']    = humanFilesize(folderSize($uploaddir . '/labels'), $decimals);

        $data['surveys']   = $this->getSurveyFolderStorage($uploaddir, $decimals);
        $data['templates'] = $this->getTemplateFolderStorage($uploaddir, $decimals);

        $html = Yii::app()->getController()->renderPartial(
            '/admin/globalsettings/_storage_ajax',
            $data,
            true
        );

        ls\ajax\AjaxHelper::outputHtml($html, 'global-settings-storage');
    }

    /**
     * Get storage of folder storage.
     * @param string $uploaddir
     * @param int $decimals
     * @return array
     */
    protected function getSurveyFolderStorage($uploaddir, $decimals)
    {
        $surveyFolders = array_filter(glob($uploaddir . '/surveys/*'), 'is_dir');
        $surveys = array();
        foreach ($surveyFolders as $folder) {
            $parts = explode('/', (string) $folder);
            $surveyId = (int) end($parts);
            $surveyinfo = getSurveyInfo($surveyId);
            $size = folderSize($folder);
            if ($size > 0) {
                $surveys[] = array(
                    'sizeInBytes' => $size,
                    'size'        => humanFilesize($size, $decimals),
                    'name'        => $surveyinfo === false ? '(' . gT('deleted') . ')' : $surveyinfo['name'],
                    'deleted'     => $surveyinfo === false,
                    'showPurgeButton' => Permission::model()->hasGlobalPermission('superadmin', 'delete')
                                         && $surveyinfo === false,
                    'sid'         => $surveyId
                );
            }
        }
        return $surveys;
    }

    /**
     * Get storage of template folders.
     * @param string $uploaddir
     * @param int $decimals
     * @return array
     */
    protected function getTemplateFolderStorage($uploaddir, $decimals)
    {
        $templateFolders = array_filter(glob($uploaddir . '/templates/*'), 'is_dir');
        $templates = array();
        foreach ($templateFolders as $folder) {
            $parts = explode('/', (string) $folder);
            $templateName = end($parts);
            $size = folderSize($folder);
            if ($size > 0) {
                $templates[] = array(
                    'sizeInBytes' => $size,
                    'size'        => humanFilesize($size, $decimals),
                    'name'        => $templateName
                );
            }
        }
        return $templates;
    }

    /**
     * Save Settings
     */
    private function saveSettings()
    {
        if (Yii::app()->getRequest()->getPost('action') !== "globalsettingssave") {
            return;
        }

        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            $this->getController()->redirect(array('/admin'));
        }
        Yii::app()->loadHelper('surveytranslator');

        $iPDFFontSize = sanitize_int(Yii::app()->getRequest()->getPost('pdffontsize'));
        if ($iPDFFontSize < 1) {
            $iPDFFontSize = 9;
        }

        $iPDFLogoWidth = sanitize_int(Yii::app()->getRequest()->getPost('pdflogowidth'));
        if ($iPDFLogoWidth < 1) {
            $iPDFLogoWidth = 50;
        }

        $maxemails = Yii::app()->getRequest()->getPost('maxemails');
        if (sanitize_int(Yii::app()->getRequest()->getPost('maxemails')) < 1) {
            $maxemails = 1;
        }

        $sendingrate = Yii::app()->getRequest()->getPost('sendingrate');
        if (sanitize_int(Yii::app()->getRequest()->getPost('sendingrate')) < 1) {
            $sendingrate = 60;
        }

        $defaultlang = sanitize_languagecode(Yii::app()->getRequest()->getPost('defaultlang'));
        $aRestrictToLanguages = explode(' ', (string) sanitize_languagecodeS(Yii::app()->getRequest()->getPost('restrictToLanguages')));
        if (!in_array($defaultlang, $aRestrictToLanguages)) {
            // Force default language in restrictToLanguages
            $aRestrictToLanguages[] = $defaultlang;
        }
        if (count(array_diff(array_keys(getLanguageData(false, Yii::app()->session['adminlang'])), $aRestrictToLanguages)) == 0) {
            $aRestrictToLanguages = '';
        } else {
            $aRestrictToLanguages = implode(' ', $aRestrictToLanguages);
        }

        SettingGlobal::setSetting('defaultlang', $defaultlang);
        SettingGlobal::setSetting('restrictToLanguages', trim($aRestrictToLanguages));
        SettingGlobal::setSetting('sitename', strip_tags(Yii::app()->getRequest()->getPost('sitename', '')));
        SettingGlobal::setSetting('defaulthtmleditormode', sanitize_paranoid_string(Yii::app()->getRequest()->getPost('defaulthtmleditormode')));
        SettingGlobal::setSetting('defaultquestionselectormode', sanitize_paranoid_string(Yii::app()->getRequest()->getPost('defaultquestionselectormode', 'default')));
        SettingGlobal::setSetting('defaultthemeteeditormode', sanitize_paranoid_string(Yii::app()->getRequest()->getPost('defaultthemeteeditormode', 'default')));
        SettingGlobal::setSetting('javascriptdebugbcknd', sanitize_paranoid_string(Yii::app()->getRequest()->getPost('javascriptdebugbcknd', false)));
        SettingGlobal::setSetting('javascriptdebugfrntnd', sanitize_paranoid_string(Yii::app()->getRequest()->getPost('javascriptdebugfrntnd', false)));
        SettingGlobal::setSetting('maintenancemode', sanitize_paranoid_string(Yii::app()->getRequest()->getPost('maintenancemode', 'off')));

        //security: for failed login attempts by user/admin
        SettingGlobal::setSetting('maxLoginAttempt', sanitize_int(Yii::app()->getRequest()->getPost('maxLoginAttempt', 3)));
        SettingGlobal::setSetting('timeOutTime', sanitize_int(Yii::app()->getRequest()->getPost('timeOutTime', 600)));

        //security: for failed attempts wrong access token by participant
        SettingGlobal::setSetting('maxLoginAttemptParticipants', sanitize_int(Yii::app()->getRequest()->getPost('maxLoginAttemptParticipants', 3)));
        SettingGlobal::setSetting('timeOutParticipants', sanitize_int(Yii::app()->getRequest()->getPost('timeOutParticipants', 600)));

        // Unstable extensions can only be changed by super admin.
        if (Permission::model()->hasGlobalPermission('superadmin', 'delete')) {
            SettingGlobal::setSetting('allow_unstable_extension_update', sanitize_paranoid_string(Yii::app()->getRequest()->getPost('allow_unstable_extension_update', false)));
        }

        SettingGlobal::setSetting('createsample', Yii::app()->getRequest()->getPost('createsample'));

        if (!Yii::app()->getConfig('demoMode')) {
            $sTemplate = Yii::app()->getRequest()->getPost("defaulttheme");
            if (array_key_exists($sTemplate, Template::getTemplateList())) {
                // Filter template name
                SettingGlobal::setSetting('defaulttheme', $sTemplate);
            }
            SettingGlobal::setSetting('x_frame_options', Yii::app()->getRequest()->getPost('x_frame_options'));
            SettingGlobal::setSetting('force_ssl', Yii::app()->getRequest()->getPost('force_ssl'));
        }

        $warning = '';
        $validatedLoginIpWhitelistInput = $this->validateIpAddresses(Yii::app()->getRequest()->getPost('loginIpWhitelist'));
        SettingGlobal::setSetting('loginIpWhitelist', $validatedLoginIpWhitelistInput['valid']);
        if (!empty($validatedLoginIpWhitelistInput['invalid'])) {
            $warning .= sprintf(gT("Warning! Invalid IP addresses have been excluded from '%s' setting."), gT("IP allowlist for administration login")) . '<br/>';
        }
        $validatedTokenIpWhitelistInput = $this->validateIpAddresses(Yii::app()->getRequest()->getPost('tokenIpWhitelist'));
        SettingGlobal::setSetting('tokenIpWhitelist', $validatedTokenIpWhitelistInput['valid']);
        if (!empty($validatedTokenIpWhitelistInput['invalid'])) {
            $warning .= sprintf(gT("Warning! Invalid IP addresses have been excluded from '%s' setting."), gT("IP allowlist for participants with access code")) . '<br/>';
        }

        // we set the admin theme
        $sAdmintheme = sanitize_paranoid_string(Yii::app()->getRequest()->getPost('admintheme'));
        SettingGlobal::setSetting('admintheme', $sAdmintheme);

        $emailMethod = strip_tags(Yii::app()->getRequest()->getPost('emailmethod', ''));
        SettingGlobal::setSetting('emailmethod', $emailMethod);
        SettingGlobal::setSetting('emailsmtphost', strip_tags((string) returnGlobal('emailsmtphost')));
        if (returnGlobal('emailsmtppassword') != 'somepassword') {
            SettingGlobal::setSetting('emailsmtppassword', LSActiveRecord::encryptSingle(returnGlobal('emailsmtppassword')));
        }
        SettingGlobal::setSetting('bounceaccounthost', strip_tags((string) returnGlobal('bounceaccounthost')));
        SettingGlobal::setSetting('bounceaccounttype', Yii::app()->request->getPost('bounceaccounttype', 'off'));
        SettingGlobal::setSetting('bounceencryption', Yii::app()->request->getPost('bounceencryption', 'off'));
        SettingGlobal::setSetting('bounceaccountuser', strip_tags((string) returnGlobal('bounceaccountuser')));

        if (returnGlobal('bounceaccountpass') != 'enteredpassword') {
            SettingGlobal::setSetting('bounceaccountpass', LSActiveRecord::encryptSingle(returnGlobal('bounceaccountpass')));
        }

        SettingGlobal::setSetting('emailsmtpssl', sanitize_paranoid_string(Yii::app()->request->getPost('emailsmtpssl', '')));
        SettingGlobal::setSetting('emailsmtpdebug', sanitize_int(Yii::app()->request->getPost('emailsmtpdebug', '0')));
        SettingGlobal::setSetting('emailsmtpuser', strip_tags((string) returnGlobal('emailsmtpuser')));
        SettingGlobal::setSetting('filterxsshtml', strip_tags(Yii::app()->getRequest()->getPost('filterxsshtml', '')));
        SettingGlobal::setSetting('disablescriptwithxss', strip_tags(Yii::app()->getRequest()->getPost('disablescriptwithxss', '')));

        $oldEmailPlugin = Yii::app()->getConfig('emailplugin');
        $emailPlugin = strip_tags(Yii::app()->getRequest()->getPost('emailplugin', ''));
        SettingGlobal::setSetting('emailplugin', $emailPlugin);
        // If the email plugin has changed, dispatch an event to allow the new plugin to do any necessary setup.
        if ($emailMethod == LimeMailer::MethodPlugin && $oldEmailPlugin != $emailPlugin) {
            $event = new PluginEvent('afterSelectEmailPlugin', $this);
            Yii::app()->getPluginManager()->dispatchEvent($event, $emailPlugin);
            $emailPluginWarning = $event->get('warning');
            if (!empty($emailPluginWarning)) {
                $warning .= $emailPluginWarning . '<br/>';
            }
        }

        // make sure emails are valid before saving them
        if (
            Yii::app()->request->getPost('siteadminbounce', '') == ''
            || validateEmailAddress(Yii::app()->request->getPost('siteadminbounce'))
        ) {
            SettingGlobal::setSetting('siteadminbounce', strip_tags(Yii::app()->request->getPost('siteadminbounce', '')));
        } else {
            $warning .= gT("Warning! Admin bounce email was not saved because it was not valid.") . '<br/>';
        }
        if (
            Yii::app()->request->getPost('siteadminemail', '') == ''
            || validateEmailAddress(Yii::app()->request->getPost('siteadminemail'))
        ) {
            SettingGlobal::setSetting('siteadminemail', strip_tags(Yii::app()->request->getPost('siteadminemail', '')));
        } else {
            $warning .= gT("Warning! Administrator email address was not saved because it was not valid.") . '<br/>';
        }
        SettingGlobal::setSetting('siteadminname', strip_tags(Yii::app()->getRequest()->getPost('siteadminname', '')));
        $repeatheadingstemp = (int) (Yii::app()->getRequest()->getPost('repeatheadings'));
        if ($repeatheadingstemp == 0) {
            $repeatheadingstemp = 25;
        }
        SettingGlobal::setSetting('repeatheadings', $repeatheadingstemp);

        SettingGlobal::setSetting('maxemails', sanitize_int($maxemails));
        SettingGlobal::setSetting('sendingrate', sanitize_int($sendingrate));
        $iSessionExpirationTime = (int) (Yii::app()->getRequest()->getPost('iSessionExpirationTime', 7200));
        if ($iSessionExpirationTime == 0) {
            $iSessionExpirationTime = 7200;
        }
        SettingGlobal::setSetting('iSessionExpirationTime', $iSessionExpirationTime);
        SettingGlobal::setSetting('ipInfoDbAPIKey', Yii::app()->getRequest()->getPost('ipInfoDbAPIKey'));
        SettingGlobal::setSetting('pdffontsize', $iPDFFontSize);
        SettingGlobal::setSetting('pdfshowsurveytitle', Yii::app()->getRequest()->getPost('pdfshowsurveytitle') == '1' ? 'Y' : 'N');
        SettingGlobal::setSetting('pdfshowheader', Yii::app()->getRequest()->getPost('pdfshowheader') == '1' ? 'Y' : 'N');
        SettingGlobal::setSetting('pdflogowidth', $iPDFLogoWidth);
        SettingGlobal::setSetting('pdfheadertitle', Yii::app()->getRequest()->getPost('pdfheadertitle'));
        SettingGlobal::setSetting('pdfheaderstring', Yii::app()->getRequest()->getPost('pdfheaderstring'));
        SettingGlobal::setSetting('bPdfQuestionFill', sanitize_int(Yii::app()->getRequest()->getPost('bPdfQuestionFill')));
        SettingGlobal::setSetting('bPdfQuestionBold', sanitize_int(Yii::app()->getRequest()->getPost('bPdfQuestionBold')));
        SettingGlobal::setSetting('bPdfQuestionBorder', sanitize_int(Yii::app()->getRequest()->getPost('bPdfQuestionBorder')));
        SettingGlobal::setSetting('bPdfResponseBorder', sanitize_int(Yii::app()->getRequest()->getPost('bPdfResponseBorder')));
        SettingGlobal::setSetting('googleMapsAPIKey', Yii::app()->getRequest()->getPost('googleMapsAPIKey'));
        SettingGlobal::setSetting('googleanalyticsapikey', Yii::app()->getRequest()->getPost('googleanalyticsapikey'));
        SettingGlobal::setSetting('googletranslateapikey', Yii::app()->getRequest()->getPost('googletranslateapikey'));
        SettingGlobal::setSetting('surveyPreview_require_Auth', Yii::app()->getRequest()->getPost('surveyPreview_require_Auth'));
        SettingGlobal::setSetting('RPCInterface', Yii::app()->getRequest()->getPost('RPCInterface'));
        SettingGlobal::setSetting('rpc_publish_api', Yii::app()->getRequest()->getPost('rpc_publish_api'));
        SettingGlobal::setSetting('add_access_control_header', Yii::app()->getRequest()->getPost('add_access_control_header'));
        SettingGlobal::setSetting('characterset', Yii::app()->getRequest()->getPost('characterset'));
        SettingGlobal::setSetting('sideMenuBehaviour', Yii::app()->getRequest()->getPost('sideMenuBehaviour', 'adaptive'));
        SettingGlobal::setSetting('overwritefiles', Yii::app()->getRequest()->getPost('overwritefiles') == '1' ? 'Y' : 'N');
        SettingGlobal::setSetting('maxDatabaseSizeForDump', Yii::app()->getRequest()->getPost('global-settings-max-size-for-db-dump'));
        //Save user administration settings
        SettingGlobal::setSetting('sendadmincreationemail', App()->getRequest()->getPost('sendadmincreationemail'));
        SettingGlobal::setSetting('admincreationemailsubject', App()->getRequest()->getPost('admincreationemailsubject'));
        SettingGlobal::setSetting('admincreationemailtemplate', App()->getRequest()->getPost('admincreationemailtemplate'));

        // Check if time zone exists, then save it
        $timezone = App()->getRequest()->getPost('displayTimezone');
        if (in_array($timezone, DateTimeZone::listIdentifiers())) {
            SettingGlobal::setSetting('displayTimezone', $timezone);
        }

        SettingGlobal::setSetting('usercontrolSameGroupPolicy', strip_tags(Yii::app()->getRequest()->getPost('usercontrolSameGroupPolicy', '')));

        $request = App()->request;
        Yii::app()->formExtensionService->applySave('globalsettings', $request);

        if (!empty($warning)) {
            Yii::app()->setFlashMessage($warning, 'warning');
        }
        Yii::app()->setFlashMessage(gT("Global settings were saved."), 'success');

        // Redirect if user clicked save-and-close-button
        if (Yii::app()->getRequest()->getPost('saveandclose')) {
            $url = Yii::app()->getRequest()->getUrlReferrer(Yii::app()->createUrl('admin'));
            Yii::app()->getController()->redirect($url);
        } else {
            Yii::app()->getController()->redirect(App()->createUrl('admin/globalsettings'));
        }
    }

    /**
     * Check Settings
     */
    private function checkSettings()
    {
        $surveycount = Survey::model()->count();

        $activesurveycount = Survey::model()->active()->count();

        $usercount = User::model()->count();

        if ($activesurveycount == false) {
            $activesurveycount = 0;
        }
        if ($surveycount === false) {
            $surveycount = 0;
        }
        $oldtokenlist = [];
        $tablelist = Yii::app()->db->schema->getTableNames();
        foreach ($tablelist as $table) {
            if (strpos((string) $table, Yii::app()->db->tablePrefix . "old_tokens_") !== false) {
                $oldtokenlist[] = $table;
            } elseif (strpos((string) $table, Yii::app()->db->tablePrefix . "tokens_") !== false) {
                $tokenlist[] = $table;
            } elseif (strpos((string) $table, Yii::app()->db->tablePrefix . "old_survey_") !== false) {
                $oldresultslist[] = $table;
            }
        }

        if (isset($oldresultslist) && is_array($oldresultslist)) {
            $deactivatedsurveys = count($oldresultslist);
        } else {
            $deactivatedsurveys = 0;
        }
        $deactivatedtokens = count($oldtokenlist);

        if (isset($tokenlist) && is_array($tokenlist)) {
            $activetokens = count($tokenlist);
        } else {
            $activetokens = 0;
        }
        return array(
        'usercount' => $usercount,
        'surveycount' => $surveycount,
        'activesurveycount' => $activesurveycount,
        'deactivatedsurveys' => $deactivatedsurveys,
        'activetokens' => $activetokens,
        'deactivatedtokens' => $deactivatedtokens
        );
    }

    /**
     * Update global survey settings
     */
    public function surveySettings()
    {
        $bRedirect = 0;
        $gsid = 0; // global setting in SurveysGroupsettings model
        $oSurveyGroupSetting = SurveysGroupsettings::model()->findByPk($gsid);
        $oSurveyGroupSetting->setOptions();

        $sPartial = Yii::app()->request->getParam('partial', '_generaloptions_panel');

        if (!empty($_POST)) {
            $oSurveyGroupSetting->attributes = $_POST;
            $oSurveyGroupSetting->gsid = 0;
            $oSurveyGroupSetting->usecaptcha = Survey::saveTranscribeCaptchaOptions();

            //todo: when changing ipanonymiez from "N" to "Y", call the function that anonymizes the ip-addresses

            if ($oSurveyGroupSetting->save()) {
                $bRedirect = 1;
                Yii::app()->setFlashMessage(gT("Global survey settings were saved."));
            } else {
                Yii::app()->setFlashMessage(
                    CHtml::errorSummary(
                        $oSurveyGroupSetting,
                        CHtml::tag("p", ['class' => 'strong'], gT("Global survey settings could not be updated, please fix the following error:"))
                    ),
                    "error"
                );
            }
        }

        $users = getUserList();
        $aData['users'] = array();
        foreach ($users as $user) {
            $aData['users'][$user['uid']] = $user['user'] . ($user['full_name'] ? ' - ' . $user['full_name'] : '');
        }
        // Sort users by name
        asort($aData['users']);

        $aData['oSurvey'] = $oSurveyGroupSetting;

        if ($bRedirect && App()->request->getPost('saveandclose') !== null) {
            $this->getController()->redirect($this->getController()->createUrl('dashboard/view'));
        }

        Yii::app()->clientScript->registerPackage('bootstrap-switch', LSYii_ClientScript::POS_BEGIN);
        Yii::app()->clientScript->registerPackage('globalsidepanel');

        $aData['aDateFormatDetails'] = getDateFormatData(Yii::app()->session['dateformat']);
        $aData['jsData'] = [
            'baseLinkUrl' => 'admin/globalsettings/sa/surveysettings',
            'getUrl' => Yii::app()->createUrl('admin/globalsettings/sa/surveysettingmenues'),
            'i10n' => [
                'Survey settings' => gT('Survey settings')
            ]
        ];
        $aData['partial'] = $sPartial;

        $aData['topbar']['title'] = gT('Global survey settings');
        $aData['topbar']['rightButtons'] = Yii::app()->getController()->renderPartial(
            '/layouts/partial_topbar/right_close_saveclose_save',
            [
                'isCloseBtn' => true,
                'isSaveAndCloseBtn' => true,
                'isSaveBtn' => true,
                'backUrl' => Yii::app()->createUrl('dashboard/view'),
                'formIdSaveClose' => 'frmglobalsettings',
                'formIdSave' => 'frmglobalsettings'
            ],
            true
        );

        $this->renderWrappedTemplate('globalsettings', 'surveySettings', $aData);
    }

    /**
     * Survey Setting Menues
     */
    public function surveysettingmenues()
    {
        $menues = Surveymenu::model()->getMenuesForGlobalSettings();
        Yii::app()->getController()->renderPartial('super/_renderJson', ['data' => $menues[0]]);
    }

    /**
     * Send Test Email
     */
    public function sendTestEmail()
    {
        $sSiteName = Yii::app()->getConfig('sitename');

        //Use the current user details for the default administrator name and email
        $user = User::model()->findByPk(Yii::app()->session['loginID']);
        $sTo = $user->full_name . " <" . $user->email . ">";
        $sFrom = Yii::app()->getConfig("siteadminname") . " <" . Yii::app()->getConfig("siteadminemail") . ">";
        $sSubject = sprintf(gT('Test email from %s'), $sSiteName);

        $body   = array();
        $body[] = sprintf(gT('This is a test email from %s'), $sSiteName);
        $body   = implode("\n", $body);

        $this->sendEmailAndShowResult($body, $sSubject, $sTo, $sFrom);
    }

    /**
     * Send Email and show result
     * @param string $body
     * @param string $sSubject
     * @param string $sTo
     * @param string $sFrom
     */
    private function sendEmailAndShowResult($body, $sSubject, $sTo, $sFrom)
    {
        $mailer = new \LimeMailer();
        $mailer->emailType = 'settings_test';
        $mailer->rawBody = $body;
        $mailer->rawSubject = $sSubject;
        $mailer->SMTPDebug = 2;
        $mailer->setTo($sTo);
        $mailer->setFrom($sFrom);

        $success = $mailer->sendMessage();

        if ($success) {
            $content = gT('Email sent successfully');
        } else {
            $content = sprintf(gT("Email sending failure: %s"), $mailer->getError());
        }

        $data = [];
        $data['message'] = $content;
        $data['success'] = $success;
        $data['maildebug'] = $mailer->getDebug('html');

        $this->renderWrappedTemplate('globalsettings', '_emailTestResults', $data);
    }

    /**
     * Send Test Email Confirmation
     */
    public function sendTestEmailConfirmation()
    {
        $user = User::model()->findByPk(Yii::app()->session['loginID']);
        $aData = [
            'testEmail' => $user->email,
            'siteadminemail' => Yii::app()->getConfig("siteadminemail"),
            'siteadminname' => Yii::app()->getConfig("siteadminname"),
            'emailmethod' => Yii::app()->getConfig("emailmethod"),
            'emailsmtphost' => Yii::app()->getConfig("emailsmtphost"),
            'emailsmtpuser' => Yii::app()->getConfig("emailsmtpuser"),
            'emailsmtppassword' => 'somepassword',
            'emailsmtpssl' => Yii::app()->getConfig("emailsmtpssl"),
        ];
        $this->getController()->renderPartial("globalsettings/_emailTestConfirmation", $aData);
    }

    /**
     * Resets (deletes) failed login attempts for participants
     *
     * @return void
     */
    public function resetFailedLoginParticipants()
    {
        FailedLoginAttempt::model()->deleteAttempts(FailedLoginAttempt::TYPE_TOKEN);
        Yii::app()->setFlashMessage(gT("Failed login attempts of participants have been reset."), 'success');
        $this->getController()->redirect(array("admin/globalsettings"));
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction     Current action, the folder to fetch views from
     * @param string $aViewUrls   View url(s)
     * @param array  $aData       Data to be passed on. Optional.
     * @param bool   $sRenderFile
     */
    protected function renderWrappedTemplate($sAction = '', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'globalsettings.js');
        parent::renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

    /**
     * Splits list of IP addresses into lists of valid and invalid addresses
     *
     * @param string $ipList list of IP addresses to validate, separated by comma or new line
     *
     * @return array<string,string> an array of the form ['valid' => validlist, 'invalid' => invalidlist]
     *                              where each list is a comma separated string.
     */
    protected function validateIpAddresses($ipList)
    {
        $inputAddresses = preg_split('/\n|,/', $ipList);
        $validAddresses = [];
        $invalidAddresses = [];
        foreach ($inputAddresses as $inputAddress) {
            $inputAddress = trim((string) $inputAddress);
            if (check_ip_address($inputAddress)) {
                $validAddresses[] = $inputAddress;
            } else {
                $invalidAddresses[] = $inputAddress;
            }
        }
        return [
            'valid' => implode(",", $validAddresses),
            'invalid' => implode(",", $invalidAddresses)
        ];
    }
}
