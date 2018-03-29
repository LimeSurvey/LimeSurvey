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

/**
* GlobalSettings Controller
*
*
* @package        LimeSurvey
* @subpackage    Backend
*/
class GlobalSettings extends Survey_Common_Action
{

    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        if (!Permission::model()->hasGlobalPermission('settings', 'read')) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->getController()->redirect(App()->createUrl("/admin"));
        }
    }

    /**
     * Shows the index page
     *
     * @access public
     * @return void
     */
    public function index()
    {
        if (!empty(Yii::app()->getRequest()->getPost('action'))) {
            $this->_saveSettings();
        }
        $this->_displaySettings();
    }

    public function showphpinfo()
    {
        if (!Yii::app()->getConfig('demoMode')) {
            phpinfo();
        }
    }

    public function refreshAssets()
    {
        // Only people who can create or update themes should be allowed to refresh the assets
        if (Permission::model()->hasGlobalPermission('templates', 'create')) {
            SettingGlobal::increaseCustomAssetsversionnumber();
            $this->getController()->redirect(array("admin/globalsettings"));
        }
    }

    private function _displaySettings()
    {
        Yii::app()->loadHelper('surveytranslator');
        $data = [];
        $data['title'] = "hi";
        $data['message'] = "message";
        foreach ($this->_checkSettings() as $key => $row) {
            $data[$key] = $row;
        }
        Yii::app()->loadLibrary('Date_Time_Converter');
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        $datetimeobj = new date_time_converter(dateShift(getGlobalSetting("updatelastcheck"), 'Y-m-d H:i:s', getGlobalSetting('timeadjust')), 'Y-m-d H:i:s');
        $data['updatelastcheck'] = $datetimeobj->convert($dateformatdetails['phpdate']." H:i:s");

        $data['updateavailable'] = (getGlobalSetting("updateavailable") && Yii::app()->getConfig("updatable"));
        $data['updatable'] = Yii::app()->getConfig("updatable");
        $data['updateinfo'] = getGlobalSetting("updateinfo");
        $data['updatebuild'] = getGlobalSetting("updatebuild");
        $data['updateversion'] = getGlobalSetting("updateversion");
        $data['aUpdateVersions'] = json_decode(getGlobalSetting("updateversions"), true);
        $data['allLanguages'] = getLanguageData(false, Yii::app()->session['adminlang']);
        if (trim(Yii::app()->getConfig('restrictToLanguages')) == '') {
            $data['restrictToLanguages'] = array_keys($data['allLanguages']);
            $data['excludedLanguages'] = array();
        } else {
            $data['restrictToLanguages'] = explode(' ', trim(Yii::app()->getConfig('restrictToLanguages')));
            $data['excludedLanguages'] = array_diff(array_keys($data['allLanguages']), $data['restrictToLanguages']);
        }

        $data['fullpagebar']['savebutton']['form'] = 'frmglobalsettings';
        $data['fullpagebar']['saveandclosebutton']['form'] = 'frmglobalsettings';
        $data['fullpagebar']['closebutton']['url'] = Yii::app()->createUrl('admin/'); // Close button

        // List of available encodings
        $data['aEncodings'] = aEncodingsArray();

        // Get current setting from DB
        $data['thischaracterset'] = getGlobalSetting('characterset');
        $data['sideMenuBehaviour'] = getGlobalSetting('sideMenuBehaviour');
        $data['aListOfThemeObjects'] = AdminTheme::getAdminThemeList();

        $this->_renderWrappedTemplate('', 'globalSettings_view', $data);
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
        $data['templateSize'] = humanFilesize(folderSize($uploaddir.'/templates'), $decimals);
        $data['surveySize']   = humanFilesize(folderSize($uploaddir.'/surveys'), $decimals);
        $data['labelSize']    = humanFilesize(folderSize($uploaddir.'/labels'), $decimals);

        $data['surveys']   = $this->getSurveyFolderStorage($uploaddir, $decimals);
        $data['templates'] = $this->getTemplateFolderStorage($uploaddir, $decimals);

        $html = Yii::app()->getController()->renderPartial(
            '/admin/global_settings/_storage_ajax',
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
        $surveyFolders = array_filter(glob($uploaddir.'/surveys/*'), 'is_dir');
        $surveys = array();
        foreach ($surveyFolders as $folder) {
            $parts = explode('/', $folder);
            $surveyId = (int) end($parts);
            $surveyinfo = getSurveyInfo($surveyId);
            $size = folderSize($folder);
            if ($size > 0) {
                $surveys[] = array(
                    'sizeInBytes' => $size,
                    'size'        => humanFilesize($size, $decimals),
                    'name'        => $surveyinfo === false ? '('.gT('deleted').')' : $surveyinfo['name'],
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
        $templateFolders = array_filter(glob($uploaddir.'/templates/*'), 'is_dir');
        $templates = array();
        foreach ($templateFolders as $folder) {
            $parts = explode('/', $folder);
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

    private function _saveSettings()
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

        $defaultlang = sanitize_languagecode(Yii::app()->getRequest()->getPost('defaultlang'));
        $aRestrictToLanguages = explode(' ', sanitize_languagecodeS(Yii::app()->getRequest()->getPost('restrictToLanguages')));
        if (!in_array($defaultlang, $aRestrictToLanguages)) {
// Force default language in restrictToLanguages
            $aRestrictToLanguages[] = $defaultlang;
        }
        if (count(array_diff(array_keys(getLanguageData(false, Yii::app()->session['adminlang'])), $aRestrictToLanguages)) == 0) {
            $aRestrictToLanguages = '';
        } else {
            $aRestrictToLanguages = implode(' ', $aRestrictToLanguages);
        }

        setGlobalSetting('defaultlang', $defaultlang);
        setGlobalSetting('restrictToLanguages', trim($aRestrictToLanguages));
        setGlobalSetting('sitename', strip_tags(Yii::app()->getRequest()->getPost('sitename')));
        setGlobalSetting('defaulthtmleditormode', sanitize_paranoid_string(Yii::app()->getRequest()->getPost('defaulthtmleditormode')));
        setGlobalSetting('defaultquestionselectormode', sanitize_paranoid_string(Yii::app()->getRequest()->getPost('defaultquestionselectormode', 'default')));
        setGlobalSetting('defaultthemeteeditormode', sanitize_paranoid_string(Yii::app()->getRequest()->getPost('defaultthemeteeditormode', 'default')));
        setGlobalSetting('javascriptdebugbcknd', sanitize_paranoid_string(Yii::app()->getRequest()->getPost('javascriptdebugbcknd', false)));
        setGlobalSetting('javascriptdebugfrntnd', sanitize_paranoid_string(Yii::app()->getRequest()->getPost('javascriptdebugfrntnd', false)));

        if (!Yii::app()->getConfig('demoMode')) {
            $sTemplate = Yii::app()->getRequest()->getPost("defaulttheme");
            if (array_key_exists($sTemplate, getTemplateList())) {
// Filter template name
                setGlobalSetting('defaulttheme', $sTemplate);
            }
            setGlobalSetting('x_frame_options', Yii::app()->getRequest()->getPost('x_frame_options'));
            setGlobalSetting('force_ssl', Yii::app()->getRequest()->getPost('force_ssl'));
        }

        // we set the admin theme
        $sAdmintheme = sanitize_paranoid_string(Yii::app()->getRequest()->getPost('admintheme'));
        setGlobalSetting('admintheme', $sAdmintheme);

        setGlobalSetting('emailmethod', strip_tags(Yii::app()->getRequest()->getPost('emailmethod')));
        setGlobalSetting('emailsmtphost', strip_tags(returnGlobal('emailsmtphost')));
        if (returnGlobal('emailsmtppassword') != 'somepassword') {
            setGlobalSetting('emailsmtppassword', strip_tags(returnGlobal('emailsmtppassword')));
        }
        setGlobalSetting('bounceaccounthost', strip_tags(returnGlobal('bounceaccounthost')));
        setGlobalSetting('bounceaccounttype', Yii::app()->request->getPost('bounceaccounttype', 'off'));
        setGlobalSetting('bounceencryption', Yii::app()->request->getPost('bounceencryption', 'off'));
        setGlobalSetting('bounceaccountuser', strip_tags(returnGlobal('bounceaccountuser')));

        if (returnGlobal('bounceaccountpass') != 'enteredpassword') {
            setGlobalSetting('bounceaccountpass', strip_tags(returnGlobal('bounceaccountpass')));
        }

        setGlobalSetting('emailsmtpssl', sanitize_paranoid_string(Yii::app()->request->getPost('emailsmtpssl', '')));
        setGlobalSetting('emailsmtpdebug', sanitize_int(Yii::app()->request->getPost('emailsmtpdebug', '0')));
        setGlobalSetting('emailsmtpuser', strip_tags(returnGlobal('emailsmtpuser')));
        setGlobalSetting('filterxsshtml', strip_tags(Yii::app()->getRequest()->getPost('filterxsshtml')));
        $warning = '';
        // make sure emails are valid before saving them
        if (Yii::app()->request->getPost('siteadminbounce', '') == ''
            || validateEmailAddress(Yii::app()->request->getPost('siteadminbounce'))) {
            setGlobalSetting('siteadminbounce', strip_tags(Yii::app()->request->getPost('siteadminbounce')));
        } else {
            $warning .= gT("Warning! Admin bounce email was not saved because it was not valid.").'<br/>';
        }
        if (Yii::app()->request->getPost('siteadminemail', '') == ''
            || validateEmailAddress(Yii::app()->request->getPost('siteadminemail'))) {
            setGlobalSetting('siteadminemail', strip_tags(Yii::app()->request->getPost('siteadminemail')));
        } else {
            $warning .= gT("Warning! Admin email was not saved because it was not valid.").'<br/>';
        }
        setGlobalSetting('siteadminname', strip_tags(Yii::app()->getRequest()->getPost('siteadminname')));
        setGlobalSetting('shownoanswer', sanitize_int(Yii::app()->getRequest()->getPost('shownoanswer')));
        setGlobalSetting('showxquestions', (Yii::app()->getRequest()->getPost('showxquestions')));
        setGlobalSetting('showgroupinfo', (Yii::app()->getRequest()->getPost('showgroupinfo')));
        setGlobalSetting('showqnumcode', (Yii::app()->getRequest()->getPost('showqnumcode')));
        $repeatheadingstemp = (int) (Yii::app()->getRequest()->getPost('repeatheadings'));
        if ($repeatheadingstemp == 0) {
            $repeatheadingstemp = 25;
        }
        setGlobalSetting('repeatheadings', $repeatheadingstemp);

        setGlobalSetting('maxemails', sanitize_int($maxemails));
        $iSessionExpirationTime = (int) (Yii::app()->getRequest()->getPost('iSessionExpirationTime',7200));
        if ($iSessionExpirationTime == 0) {
            $iSessionExpirationTime = 7200;
        }
        setGlobalSetting('iSessionExpirationTime', $iSessionExpirationTime);
        setGlobalSetting('ipInfoDbAPIKey', Yii::app()->getRequest()->getPost('ipInfoDbAPIKey'));
        setGlobalSetting('pdffontsize', $iPDFFontSize);
        setGlobalSetting('pdfshowheader', Yii::app()->getRequest()->getPost('pdfshowheader') == '1' ? 'Y' : 'N');
        setGlobalSetting('pdflogowidth', $iPDFLogoWidth);
        setGlobalSetting('pdfheadertitle', Yii::app()->getRequest()->getPost('pdfheadertitle'));
        setGlobalSetting('pdfheaderstring', Yii::app()->getRequest()->getPost('pdfheaderstring'));
        setGlobalSetting('bPdfQuestionFill', sanitize_int(Yii::app()->getRequest()->getPost('bPdfQuestionFill')));
        setGlobalSetting('bPdfQuestionBold', sanitize_int(Yii::app()->getRequest()->getPost('bPdfQuestionBold')));
        setGlobalSetting('bPdfQuestionBorder', sanitize_int(Yii::app()->getRequest()->getPost('bPdfQuestionBorder')));
        setGlobalSetting('bPdfResponseBorder', sanitize_int(Yii::app()->getRequest()->getPost('bPdfResponseBorder')));
        setGlobalSetting('googleMapsAPIKey', Yii::app()->getRequest()->getPost('googleMapsAPIKey'));
        setGlobalSetting('googleanalyticsapikey', Yii::app()->getRequest()->getPost('googleanalyticsapikey'));
        setGlobalSetting('googletranslateapikey', Yii::app()->getRequest()->getPost('googletranslateapikey'));
        setGlobalSetting('surveyPreview_require_Auth', Yii::app()->getRequest()->getPost('surveyPreview_require_Auth'));
        setGlobalSetting('RPCInterface', Yii::app()->getRequest()->getPost('RPCInterface'));
        setGlobalSetting('rpc_publish_api', (bool) Yii::app()->getRequest()->getPost('rpc_publish_api'));
        setGlobalSetting('characterset', Yii::app()->getRequest()->getPost('characterset'));
        setGlobalSetting('sideMenuBehaviour', Yii::app()->getRequest()->getPost('sideMenuBehaviour', 'adaptive'));
        $savetime = intval((float) Yii::app()->getRequest()->getPost('timeadjust') * 60).' minutes'; //makes sure it is a number, at least 0
        if ((substr($savetime, 0, 1) != '-') && (substr($savetime, 0, 1) != '+')) {
            $savetime = '+'.$savetime;
        }
        setGlobalSetting('timeadjust', $savetime);
        setGlobalSetting('usercontrolSameGroupPolicy', strip_tags(Yii::app()->getRequest()->getPost('usercontrolSameGroupPolicy')));

        Yii::app()->session['flashmessage'] = $warning.gT("Global settings were saved.");

        // Redirect if user clicked save-and-close-button
        if (Yii::app()->getRequest()->getPost('saveandclose')) {
            $url = Yii::app()->getRequest()->getUrlReferrer(Yii::app()->createUrl('admin'));
            Yii::app()->getController()->redirect($url);
        } else {
            Yii::app()->getController()->redirect(App()->createUrl('admin/globalsettings'));
        }
    }

    private function _checkSettings()
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
            if (strpos($table, Yii::app()->db->tablePrefix."old_tokens_") !== false) {
                $oldtokenlist[] = $table;
            } elseif (strpos($table, Yii::app()->db->tablePrefix."tokens_") !== false) {
                $tokenlist[] = $table;
            } elseif (strpos($table, Yii::app()->db->tablePrefix."old_survey_") !== false) {
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
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = '', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'globalsettings.js');
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
