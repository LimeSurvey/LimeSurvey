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
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */

/**
* GlobalSettings Controller
 *
 *
 * @package        LimeSurvey
 * @subpackage    Backend
 */
class GlobalSettings extends CAction
{
    /**
     * Routes to the correct sub-page
     *
     * @access public
     * @return void
     */
    public function run()
    {
        if (isset($_GET['showphpinfo'])) {
            $this->showphpinfo();
        } else {
            $this->index();
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
        if (Yii::app()->session['USER_RIGHT_CONFIGURATOR'] == 1) {
            if (!empty($_POST['action'])) {
                $this->_saveSettings();
            }
            $this->_displaySettings();
        }
    }

    public function showphpinfo()
    {
        if (Yii::app()->session['USER_RIGHT_CONFIGURATOR'] == 1 && !Yii::app()->getConfig('demoMode')) {
            phpinfo();
        }
    }

    private function _displaySettings()
    {
        Yii::app()->loadHelper('surveytranslator');

        $data['clang'] = $this->getController()->lang;
        $data['title'] = "hi";
        $data['message'] = "message";
        foreach ($this->_checkSettings() as $key => $row)
        {
            $data[$key] = $row;
        }
        $data['thisupdatecheckperiod'] = getGlobalSetting('updatecheckperiod');
        $data['updatelastcheck'] = Yii::app()->getConfig("updatelastcheck");
        $data['updateavailable'] = Yii::app()->getConfig("updateavailable");
        $data['updateinfo'] = Yii::app()->getConfig("updateinfo");
        $data['allLanguages'] = getLanguageData();
        if (trim(Yii::app()->getConfig('restrictToLanguages')) == '') {
            $data['restrictToLanguages'] = array_keys($data['allLanguages']);
            $data['excludedLanguages'] = array();
        }
        else
        {
            $data['restrictToLanguages'] = explode(' ', trim(Yii::app()->getConfig('restrictToLanguages')));
            $data['excludedLanguages'] = array_diff(array_keys($data['allLanguages']), $data['restrictToLanguages']);
        }
        $this->_renderAdmin($data);
    }

    private function _saveSettings()
    {
        if ($_POST['action'] !== "globalsettingssave") {
            return;
        }

        if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1) {
            $this->getController()->redirect($this->getController()->createUrl('/admin'));
        }
        $clang = $this->getController()->lang;
        Yii::app()->loadHelper('surveytranslator');

        $maxemails = $_POST['maxemails'];
        if (sanitize_int($_POST['maxemails']) < 1) {
            $maxemails = 1;
        }

        $aRestrictToLanguages = explode(' ', sanitize_languagecodeS($_POST['restrictToLanguages']));
        if (count(array_diff(array_keys(getLanguageData()), $aRestrictToLanguages)) == 0) {
            $aRestrictToLanguages = '';
        } else {
            $aRestrictToLanguages = implode(' ', $aRestrictToLanguages);
        }

        setGlobalSetting('restrictToLanguages', trim($aRestrictToLanguages));
        setGlobalSetting('sitename', strip_tags($_POST['sitename']));
        setGlobalSetting('updatecheckperiod', (int)($_POST['updatecheckperiod']));
        setGlobalSetting('addTitleToLinks', sanitize_paranoid_string($_POST['addTitleToLinks']));
        setGlobalSetting('defaultlang', sanitize_languagecode($_POST['defaultlang']));
        setGlobalSetting('defaulthtmleditormode', sanitize_paranoid_string($_POST['defaulthtmleditormode']));
        setGlobalSetting('defaulttemplate', sanitize_paranoid_string($_POST['defaulttemplate']));
        setGlobalSetting('emailmethod', strip_tags($_POST['emailmethod']));
        setGlobalSetting('emailsmtphost', strip_tags(returnglobal('emailsmtphost')));
        if (returnglobal('emailsmtppassword') != 'somepassword') {
            setGlobalSetting('emailsmtppassword', strip_tags(returnglobal('emailsmtppassword')));
        }
        setGlobalSetting('bounceaccounthost', strip_tags(returnglobal('bounceaccounthost')));
        setGlobalSetting('bounceaccounttype', strip_tags(returnglobal('bounceaccounttype')));
        setGlobalSetting('bounceencryption', strip_tags(returnglobal('bounceencryption')));
        setGlobalSetting('bounceaccountuser', strip_tags(returnglobal('bounceaccountuser')));

        if (returnglobal('bounceaccountpass') != 'enteredpassword') {
            setGlobalSetting('bounceaccountpass', strip_tags(returnglobal('bounceaccountpass')));
        }
        setGlobalSetting('emailsmtpssl', sanitize_paranoid_string(returnglobal('emailsmtpssl')));
        setGlobalSetting('emailsmtpdebug', sanitize_int(returnglobal('emailsmtpdebug')));
        setGlobalSetting('emailsmtpuser', strip_tags(returnglobal('emailsmtpuser')));
        setGlobalSetting('filterxsshtml', strip_tags($_POST['filterxsshtml']));
        setGlobalSetting('siteadminbounce', strip_tags($_POST['siteadminbounce']));
        setGlobalSetting('siteadminemail', strip_tags($_POST['siteadminemail']));
        setGlobalSetting('siteadminname', strip_tags($_POST['siteadminname']));
        setGlobalSetting('shownoanswer', sanitize_int($_POST['shownoanswer']));
        setGlobalSetting('showXquestions', ($_POST['showXquestions']));
        setGlobalSetting('showgroupinfo', ($_POST['showgroupinfo']));
        setGlobalSetting('showqnumcode', ($_POST['showqnumcode']));
        $repeatheadingstemp = (int)($_POST['repeatheadings']);
        if ($repeatheadingstemp == 0) $repeatheadingstemp = 25;
        setGlobalSetting('repeatheadings', $repeatheadingstemp);

        setGlobalSetting('maxemails', sanitize_int($maxemails));
        $iSessionExpirationTime = (int)($_POST['sess_expiration']);
        if ($iSessionExpirationTime == 0) $iSessionExpirationTime = 3600;
        setGlobalSetting('sess_expiration', $iSessionExpirationTime);
        setGlobalSetting('ipInfoDbAPIKey', $_POST['ipInfoDbAPIKey']);
        setGlobalSetting('googleMapsAPIKey', $_POST['googleMapsAPIKey']);
        setGlobalSetting('force_ssl', $_POST['force_ssl']);
        setGlobalSetting('surveyPreview_require_Auth', $_POST['surveyPreview_require_Auth']);
        setGlobalSetting('enableXMLRPCInterface', $_POST['enableXMLRPCInterface']);
        $savetime = trim(strip_tags((float)$_POST['timeadjust']) . ' hours'); //makes sure it is a number, at least 0
        if ((substr($savetime, 0, 1) != '-') && (substr($savetime, 0, 1) != '+')) {
            $savetime = '+' . $savetime;
        }
        setGlobalSetting('timeadjust', $savetime);
        setGlobalSetting('usepdfexport', strip_tags($_POST['usepdfexport']));
        setGlobalSetting('usercontrolSameGroupPolicy', strip_tags($_POST['usercontrolSameGroupPolicy']));

        Yii::app()->session['flashmessage'] = $clang->gT("Global settings were saved.");
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

    private function _renderAdmin($data)
    {
        $this->getController()->_js_admin_includes(Yii::app()->baseUrl . "scripts/jquery/jquery.selectboxes.min.js");
        $this->getController()->_js_admin_includes(Yii::app()->baseUrl . "scripts/admin/globalsettings.js");
        $this->getController()->_getAdminHeader();
        $this->getController()->_showadminmenu();
        $this->getController()->render('/admin/globalSettings_view', $data);
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
    }
}
