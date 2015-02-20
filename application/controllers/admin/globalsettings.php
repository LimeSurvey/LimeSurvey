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

    function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        if (!App()->user->checkAccess('settings')) {
            die();
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
        if (!empty($_POST['action'])) {
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

    public function updatecheck()
    {
        updateCheck();
        $this->getController()->redirect(array('admin/globalsettings'));
    }

    private function _displaySettings()
    {
        Yii::app()->loadHelper('surveytranslator');

        //save refurl from where global settings screen is called!
        $refurl = Yii::app()->getRequest()->getUrlReferrer();

        // Some URLs are not to be allowed to refered back to.
        // These exceptions can be added to the $aReplacements array
        $aReplacements=array('admin/update/sa/step4b'=>'admin/sa/index',
                             'admin/user/sa/adduser'=>'admin/user/sa/index',
                             'admin/user/sa/setusertemplates'=>'admin/user/sa/index',
                             'admin/user/setusertemplates'=>'admin/user/sa/index'
                            );
        $refurl= str_replace(array_keys($aReplacements),array_values($aReplacements),$refurl);
        // Don't update refurl is it's globalsetting
        if(strpos($refurl,'globalsettings')===false)
            Yii::app()->session['refurl'] = htmlspecialchars($refurl); //just to be safe!

        $data['title'] = "hi";
        $data['message'] = "message";
        foreach ($this->_checkSettings() as $key => $row)
        {
            $data[$key] = $row;
        }
        $data['thisupdatecheckperiod'] = getGlobalSetting('updatecheckperiod');
        $data['sUpdateNotification'] = getGlobalSetting('updatenotification');
        $data['updatelastcheck']= App()->locale->getDateFormatter()->formatDateTime(SettingGlobal::get("updatelastcheck"));

        $data['updateavailable'] = (getGlobalSetting("updateavailable") &&  Yii::app()->getConfig("updatable"));
        $data['updatable'] = Yii::app()->getConfig("updatable");
        $data['updateinfo'] = getGlobalSetting("updateinfo");
        $data['updatebuild'] = getGlobalSetting("updatebuild");
        $data['updateversion'] = getGlobalSetting("updateversion");
        $data['aUpdateVersions'] = json_decode(getGlobalSetting("updateversions"),true);
        $data['allLanguages'] = getLanguageData(false, Yii::app()->session['adminlang']);
        if (trim(Yii::app()->getConfig('restrictToLanguages')) == '') {
            $data['restrictToLanguages'] = array_keys($data['allLanguages']);
            $data['excludedLanguages'] = array();
        }
        else
        {
            $data['restrictToLanguages'] = explode(' ', trim(Yii::app()->getConfig('restrictToLanguages')));
            $data['excludedLanguages'] = array_diff(array_keys($data['allLanguages']), $data['restrictToLanguages']);
        }

        $this->_renderWrappedTemplate('', 'globalSettings_view', $data);
    }

    private function _saveSettings()
    {
        if (App()->request->getPost('action') !== "save" && App()->request->getPost('action') !== "savequit") // Why not use App()->request->isPostRequest ?
        {
            return;
        }

        if (!App()->user->checkAccess('settings', ['crud' => 'update']))
        {
            $this->getController()->redirect(array('/admin'));
        }
        Yii::app()->loadHelper('surveytranslator');

        $iPDFFontSize = sanitize_int(App()->request->getPost('pdffontsize'));
        if ($iPDFFontSize < 1)
        {
            $iPDFFontSize = 9;
        }

        $iPDFLogoWidth = sanitize_int(App()->request->getPost('pdflogowidth'));
        if ($iPDFLogoWidth < 1)
        {
            $iPDFLogoWidth = 50;
        }

        $maxemails = (int)App()->request->getPost('maxemails');
        if ($maxemails < 1) {
            $maxemails = 1;
        }

        $defaultlang = sanitize_languagecode(App()->request->getPost('defaultlang'));
        $aRestrictToLanguages = App()->request->getPost('restrictToLanguages',array());
        if (!in_array($defaultlang,$aRestrictToLanguages)){ // Force default language in restrictToLanguages
            $aRestrictToLanguages[]=$defaultlang;
        }
        if (count(array_diff(array_keys(getLanguageData(false,Yii::app()->session['adminlang'])), $aRestrictToLanguages)) == 0) {
            $aRestrictToLanguages = '';
        } else {
            $aRestrictToLanguages = implode(' ', $aRestrictToLanguages);
        }

        setGlobalSetting('defaultlang', $defaultlang);
        setGlobalSetting('restrictToLanguages', trim($aRestrictToLanguages));
        setGlobalSetting('sitename', strip_tags(App()->request->getPost('sitename')));
        setGlobalSetting('updatecheckperiod', (int)App()->request->getPost('updatecheckperiod'));
        setGlobalSetting('updatenotification', strip_tags(App()->request->getPost('updatenotification')));
        setGlobalSetting('defaulthtmleditormode', sanitize_paranoid_string(App()->request->getPost('defaulthtmleditormode')));
        setGlobalSetting('defaultquestionselectormode', sanitize_paranoid_string(App()->request->getPost('defaultquestionselectormode')));
        setGlobalSetting('defaulttemplateeditormode', sanitize_paranoid_string(App()->request->getPost('defaulttemplateeditormode')));
        if (!Yii::app()->getConfig('demoMode'))
        {
            setGlobalSetting('defaulttemplate', Template::templateNameFilter(App()->request->getPost('defaulttemplate')));
        }
        $sAdminTheme=sanitize_paranoid_string(App()->request->getPost('admintheme'));
        setGlobalSetting('admintheme', $sAdminTheme);
        setGlobalSetting('adminthemeiconsize', trim(file_get_contents(Yii::app()->getConfig("styledir").DIRECTORY_SEPARATOR.$sAdminTheme.DIRECTORY_SEPARATOR.'iconsize')));
        setGlobalSetting('emailmethod', strip_tags(App()->request->getPost('emailmethod')));
        setGlobalSetting('emailsmtphost', strip_tags(returnGlobal('emailsmtphost')));
        if (returnGlobal('emailsmtppassword') != 'somepassword') {
            setGlobalSetting('emailsmtppassword', strip_tags(returnGlobal('emailsmtppassword')));
        }
        setGlobalSetting('bounceaccounthost', strip_tags(returnGlobal('bounceaccounthost')));
        setGlobalSetting('bounceaccounttype', strip_tags(returnGlobal('bounceaccounttype')));
        setGlobalSetting('bounceencryption', strip_tags(returnGlobal('bounceencryption')));
        setGlobalSetting('bounceaccountuser', strip_tags(returnGlobal('bounceaccountuser')));

        if (returnGlobal('bounceaccountpass') != 'enteredpassword') setGlobalSetting('bounceaccountpass', strip_tags(returnGlobal('bounceaccountpass')));

        setGlobalSetting('emailsmtpssl', sanitize_paranoid_string(Yii::app()->request->getPost('emailsmtpssl','')));
        setGlobalSetting('emailsmtpdebug', sanitize_int(Yii::app()->request->getPost('emailsmtpdebug','0')));
        setGlobalSetting('emailsmtpuser', strip_tags(returnGlobal('emailsmtpuser')));
        setGlobalSetting('filterxsshtml', strip_tags(App()->request->getPost('filterxsshtml')));
        // make sure emails are valid before saving them
        if (Yii::app()->request->getPost('siteadminbounce', '') == ''
            || validateEmailAddress(Yii::app()->request->getPost('siteadminbounce'))) {
            setGlobalSetting('siteadminbounce', strip_tags(Yii::app()->request->getPost('siteadminbounce')));
        } else {
            Yii::app()->setFlashMessage(gT("Warning! Admin bounce email was not saved because it was not valid."),'error');
        }
        if (Yii::app()->request->getPost('siteadminemail', '') == ''
            || validateEmailAddress(Yii::app()->request->getPost('siteadminemail'))) {
            setGlobalSetting('siteadminemail', strip_tags(Yii::app()->request->getPost('siteadminemail')));
        } else {
            Yii::app()->setFlashMessage(gT("Warning! Admin email was not saved because it was not valid."),'error');
        }
        setGlobalSetting('siteadminname', strip_tags(App()->request->getPost('siteadminname')));
        setGlobalSetting('shownoanswer', sanitize_int(App()->request->getPost('shownoanswer')));
        setGlobalSetting('showxquestions', App()->request->getPost('showxquestions'));
        setGlobalSetting('showgroupinfo', App()->request->getPost('showgroupinfo'));
        setGlobalSetting('showqnumcode', App()->request->getPost('showqnumcode'));
        $repeatheadingstemp = (int)(App()->request->getPost('repeatheadings'));
        if ($repeatheadingstemp <= 0) $repeatheadingstemp = 25;
        setGlobalSetting('repeatheadings', $repeatheadingstemp);

        setGlobalSetting('maxemails', sanitize_int($maxemails));
        $iSessionExpirationTime = (int)App()->request->getPost('iSessionExpirationTime',getGlobalSetting('iSessionExpirationTime'));// If not in post : don't replace it
        if ($iSessionExpirationTime <= 0) $iSessionExpirationTime = 7200;
        setGlobalSetting('iSessionExpirationTime', $iSessionExpirationTime);
        setGlobalSetting('GeoNamesUsername', App()->request->getPost('GeoNamesUsername'));
        setGlobalSetting('googleMapsAPIKey', App()->request->getPost('googleMapsAPIKey'));
        setGlobalSetting('ipInfoDbAPIKey', App()->request->getPost('ipInfoDbAPIKey'));
        setGlobalSetting('pdffontsize', $iPDFFontSize);
        setGlobalSetting('pdfshowheader', App()->request->getPost('pdfshowheader'));
        setGlobalSetting('pdflogowidth', $iPDFLogoWidth);
        setGlobalSetting('pdfheadertitle', App()->request->getPost('pdfheadertitle'));
        setGlobalSetting('pdfheaderstring', App()->request->getPost('pdfheaderstring'));
        setGlobalSetting('googleanalyticsapikey',App()->request->getPost('googleanalyticsapikey'));
        setGlobalSetting('googletranslateapikey',App()->request->getPost('googletranslateapikey'));
        setGlobalSetting('force_ssl', App()->request->getPost('force_ssl'));
        setGlobalSetting('surveyPreview_require_Auth', App()->request->getPost('surveyPreview_require_Auth'));
        setGlobalSetting('RPCInterface', App()->request->getPost('RPCInterface'));
        setGlobalSetting('rpc_publish_api', (bool) App()->request->getPost('rpc_publish_api'));
        $savetime = ((float)App()->request->getPost('timeadjust'))*60 . ' minutes'; //makes sure it is a number, at least 0
        if ((substr($savetime, 0, 1) != '-') && (substr($savetime, 0, 1) != '+')) {
            $savetime = '+' . $savetime;
        }
        setGlobalSetting('timeadjust', $savetime);
        setGlobalSetting('usercontrolSameGroupPolicy', strip_tags(App()->request->getPost('usercontrolSameGroupPolicy')));

        Yii::app()->setFlashMessage(gT("Global settings were saved."));
        if(App()->request->getPost('action') == "savequit")
        {
            $url = htmlspecialchars_decode(Yii::app()->session['refurl']);
            if($url)
            {
                Yii::app()->getController()->redirect($url);
            }
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
        if ($surveycount == false) {
            $surveycount = 0;
        }

        $tablelist = Yii::app()->db->schema->getTableNames();
        foreach ($tablelist as $table)
        {
            if (strpos($table, Yii::app()->db->tablePrefix . "old_tokens_") !== false) {
                $oldtokenlist[] = $table;
            }
            elseif (strpos($table, Yii::app()->db->tablePrefix . "tokens_") !== false)
            {
                $tokenlist[] = $table;
            }
            elseif (strpos($table, Yii::app()->db->tablePrefix . "old_survey_") !== false)
            {
                $oldresultslist[] = $table;
            }
        }

        if (isset($oldresultslist) && is_array($oldresultslist)) {
            $deactivatedsurveys = count($oldresultslist);
        } else {
            $deactivatedsurveys = 0;
        }
        if (isset($oldtokenlist) && is_array($oldtokenlist)) {
            $deactivatedtokens = count($oldtokenlist);
        } else {
            $deactivatedtokens = 0;
        }
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
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = '', $aViewUrls = array(), $aData = array())
    {
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "globalsettings.js");

        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}
