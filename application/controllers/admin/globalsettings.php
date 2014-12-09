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

        if (!Permission::model()->hasGlobalPermission('settings','read')) {
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
                             'admin/user/sa/setusertemplates'=>'admin/user/sa/index'
                            );
        $refurl= str_replace(array_keys($aReplacements),array_values($aReplacements),$refurl);
        Yii::app()->session['refurl'] = htmlspecialchars($refurl); //just to be safe!

        $data['clang'] = $this->getController()->lang;
        $data['title'] = "hi";
        $data['message'] = "message";
        foreach ($this->_checkSettings() as $key => $row)
        {
            $data[$key] = $row;
        }
        $data['thisupdatecheckperiod'] = getGlobalSetting('updatecheckperiod');
        $data['sUpdateNotification'] = getGlobalSetting('updatenotification');
        Yii::app()->loadLibrary('Date_Time_Converter');
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        $datetimeobj = new date_time_converter(dateShift(getGlobalSetting("updatelastcheck"),'Y-m-d H:i:s',getGlobalSetting('timeadjust')), 'Y-m-d H:i:s');
        $data['updatelastcheck']=$datetimeobj->convert($dateformatdetails['phpdate'] . " H:i:s");

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
        if ($_POST['action'] !== "globalsettingssave") {
            return;
        }

        if (!Permission::model()->hasGlobalPermission('settings','update')) {
            $this->getController()->redirect(array('/admin'));
        }
        $clang = $this->getController()->lang;
        Yii::app()->loadHelper('surveytranslator');

        $iPDFFontSize = sanitize_int($_POST['pdffontsize']);
        if ($iPDFFontSize < 1)
        {
            $iPDFFontSize = 9;
        }

        $iPDFLogoWidth = sanitize_int($_POST['pdflogowidth']);
        if ($iPDFLogoWidth < 1)
        {
            $iPDFLogoWidth = 50;
        }

        $maxemails = $_POST['maxemails'];
        if (sanitize_int($_POST['maxemails']) < 1) {
            $maxemails = 1;
        }

        $defaultlang = sanitize_languagecode($_POST['defaultlang']);
        $aRestrictToLanguages = explode(' ', sanitize_languagecodeS($_POST['restrictToLanguages']));
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
        setGlobalSetting('sitename', strip_tags($_POST['sitename']));
        setGlobalSetting('updatecheckperiod', (int)($_POST['updatecheckperiod']));
        setGlobalSetting('updatenotification', strip_tags($_POST['updatenotification']));
        setGlobalSetting('defaulthtmleditormode', sanitize_paranoid_string($_POST['defaulthtmleditormode']));
        setGlobalSetting('defaultquestionselectormode', sanitize_paranoid_string($_POST['defaultquestionselectormode']));
        setGlobalSetting('defaulttemplateeditormode', sanitize_paranoid_string($_POST['defaulttemplateeditormode']));
        if (!Yii::app()->getConfig('demoMode'))
        {
            setGlobalSetting('defaulttemplate', sanitize_paranoid_string($_POST['defaulttemplate']));
        }
        setGlobalSetting('admintheme', sanitize_paranoid_string($_POST['admintheme']));
        setGlobalSetting('adminthemeiconsize', trim(file_get_contents(Yii::app()->getConfig("styledir").DIRECTORY_SEPARATOR.sanitize_paranoid_string($_POST['admintheme']).DIRECTORY_SEPARATOR.'iconsize')));
        setGlobalSetting('emailmethod', strip_tags($_POST['emailmethod']));
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
        setGlobalSetting('filterxsshtml', strip_tags($_POST['filterxsshtml']));
        $warning = '';
        // make sure emails are valid before saving them
        if (Yii::app()->request->getPost('siteadminbounce', '') == ''
            || validateEmailAddress(Yii::app()->request->getPost('siteadminbounce'))) {
            setGlobalSetting('siteadminbounce', strip_tags(Yii::app()->request->getPost('siteadminbounce')));
        } else {
            $warning .= $clang->gT("Warning! Admin bounce email was not saved because it was not valid.").'<br/>';
        }
        if (Yii::app()->request->getPost('siteadminemail', '') == ''
            || validateEmailAddress(Yii::app()->request->getPost('siteadminemail'))) {
            setGlobalSetting('siteadminemail', strip_tags(Yii::app()->request->getPost('siteadminemail')));
        } else {
            $warning .= $clang->gT("Warning! Admin email was not saved because it was not valid.").'<br/>';
        }
        setGlobalSetting('siteadminname', strip_tags($_POST['siteadminname']));
        setGlobalSetting('shownoanswer', sanitize_int($_POST['shownoanswer']));
        setGlobalSetting('showxquestions', ($_POST['showxquestions']));
        setGlobalSetting('showgroupinfo', ($_POST['showgroupinfo']));
        setGlobalSetting('showqnumcode', ($_POST['showqnumcode']));
        $repeatheadingstemp = (int)($_POST['repeatheadings']);
        if ($repeatheadingstemp == 0) $repeatheadingstemp = 25;
        setGlobalSetting('repeatheadings', $repeatheadingstemp);

        setGlobalSetting('maxemails', sanitize_int($maxemails));
        $iSessionExpirationTime = (int)($_POST['iSessionExpirationTime']);
        if ($iSessionExpirationTime == 0) $iSessionExpirationTime = 7200;
        setGlobalSetting('iSessionExpirationTime', $iSessionExpirationTime);
        setGlobalSetting('ipInfoDbAPIKey', $_POST['ipInfoDbAPIKey']);
        setGlobalSetting('pdffontsize', $iPDFFontSize);
        setGlobalSetting('pdfshowheader', $_POST['pdfshowheader']);
        setGlobalSetting('pdflogowidth', $iPDFLogoWidth);
        setGlobalSetting('pdfheadertitle', $_POST['pdfheadertitle']);
        setGlobalSetting('pdfheaderstring', $_POST['pdfheaderstring']);
        setGlobalSetting('googleMapsAPIKey', $_POST['googleMapsAPIKey']);
        setGlobalSetting('googleanalyticsapikey',$_POST['googleanalyticsapikey']);
        setGlobalSetting('googletranslateapikey',$_POST['googletranslateapikey']);
        setGlobalSetting('force_ssl', $_POST['force_ssl']);
        setGlobalSetting('surveyPreview_require_Auth', $_POST['surveyPreview_require_Auth']);
        setGlobalSetting('RPCInterface', $_POST['RPCInterface']);
        setGlobalSetting('rpc_publish_api', (bool) $_POST['rpc_publish_api']);
        $savetime = ((float)$_POST['timeadjust'])*60 . ' minutes'; //makes sure it is a number, at least 0
        if ((substr($savetime, 0, 1) != '-') && (substr($savetime, 0, 1) != '+')) {
            $savetime = '+' . $savetime;
        }
        setGlobalSetting('timeadjust', $savetime);
        setGlobalSetting('usercontrolSameGroupPolicy', strip_tags($_POST['usercontrolSameGroupPolicy']));

        Yii::app()->session['flashmessage'] = $warning.$clang->gT("Global settings were saved.");

        $url = htmlspecialchars_decode(Yii::app()->session['refurl']);
        if($url){Yii::app()->getController()->redirect($url);}
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
