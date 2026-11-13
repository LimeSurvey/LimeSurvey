<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

abstract class LSYii_Controller extends CController
{
    /**
     * This array contains the survey / group / question id used by the menu widget.
     * @var array
     */
    public $navData = array();
    /**
     * Basic initialiser to the base controller class
     *
     * @access public
     * @param string $id
     * @param CWebModule $module
     */
    public function __construct($id, $module = null)
    {
        parent::__construct($id, $module);
        $this->_checkInstallation();

        $this->loadHelper('expressions.em_manager');
        $this->loadHelper('replacements');
        $this->customInit();
    }

    /**
     * Check that installation was already done by looking for config.php
     * Will redirect to the installer script if not exists.
     *
     * @access protected
     * @return void
     */
    protected function _checkInstallation()
    {
        $file_name = Yii::app()->getConfig('rootdir') . '/application/config/config.php';
        if (!file_exists($file_name)) {
            $this->redirect(array('/installer'));
        }
    }

    /**
     * Ends the request with HTTP 503 and a "site under maintenance" message when the maintenance
     * mode "hard-cli" locks the administration, see SettingGlobal::isAdministrationLocked().
     * Called first thing by every administrative entry point (web interface, RemoteControl, REST API).
     *
     * @param string $format 'html' for the maintenance page, 'text' for a plain text body (RemoteControl, AJAX), 'json' for a JSON body (REST API)
     * @return void
     */
    protected function endRequestIfAdministrationLocked($format = 'html')
    {
        if (!SettingGlobal::isAdministrationLocked()) {
            return;
        }
        $this->endRequestWithMaintenanceMessage(
            gT('The administration of this site is temporarily locked for maintenance, for example while a backup or an update is being made. Please try again later.'),
            $format
        );
    }

    /**
     * Ends the request with HTTP 503, a Retry-After header and the given maintenance message.
     *
     * @param string $message The message to show
     * @param string $format 'html' for the admin maintenance page, 'text' for a plain text body, 'json' for a JSON body
     * @return void
     */
    protected function endRequestWithMaintenanceMessage($message, $format)
    {
        http_response_code(503);
        header('Retry-After: 300');
        if ($format === 'json') {
            header('Content-Type: application/json');
            echo CJSON::encode(['error' => ['code' => 'MaintenanceMode', 'message' => $message]]);
        } elseif ($format === 'text') {
            header('Content-Type: text/plain; charset=UTF-8');
            echo $message;
        } else {
            $this->renderPartial('/admin/super/administrationlocked', ['message' => $message], false, true);
        }
        Yii::app()->end();
    }

    /**
     * Filter for the controllers of participant pages that write data (registration, opt-in, opt-out):
     * while the maintenance mode keeps participants out ("hard" and "hard-cli"), no action runs, every
     * request ends with a maintenance page. A controller enables it by listing 'participantsLocked' in
     * its filters(), so new actions are covered without further code.
     * The survey ID ("surveyid", else "sid") and the language ("langcode", else "lang") only choose how
     * the page looks: the survey's theme and language for an existing survey, the site's maintenance
     * page otherwise. The actions read their parameters in different ways, so the filter never relies
     * on a missing or malformed survey ID to keep an action from writing data.
     *
     * @param CFilterChain $filterChain
     * @return void
     */
    public function filterParticipantsLocked($filterChain)
    {
        if (!SettingGlobal::areParticipantsLocked()) {
            $filterChain->run();
            return;
        }
        $request = Yii::app()->request;
        $surveyId = (int) ($request->getParam('surveyid') ?: $request->getParam('sid'));
        $survey = $surveyId > 0 ? Survey::model()->findByPk($surveyId) : null;
        $language = (string) $request->getParam('langcode', $request->getParam('lang', ''));
        if ($survey) {
            if (!in_array($language, $survey->getAllLanguages(), true)) {
                $language = $survey->language;
            }
            Yii::app()->setLanguage($language);
            $this->endRequestIfParticipantsLocked($survey);
        }
        $this->endRequestWithSiteMaintenancePage($language);
    }

    /**
     * Renders the maintenance page of the site in the default theme, as the public survey list does
     * during maintenance, and ends the request.
     *
     * @param string $language The requested language, the default language is used if the site does not offer it
     * @return void
     */
    protected function endRequestWithSiteMaintenancePage($language)
    {
        if (!isset(getLanguageDataRestricted(false, 'short')[$language])) {
            $language = Yii::app()->getConfig('defaultlang');
        }
        Yii::app()->setLanguage($language);
        LimeExpressionManager::SetSurveyId(0);
        $template = Template::model()->getInstance(Yii::app()->getConfig('defaulttheme'));
        $siteName = Yii::app()->getConfig('sitename');
        $surveyInfo = [
            'oTemplate' => $template,
            'sSiteName' => $siteName,
            'sSiteAdminName' => Yii::app()->getConfig('siteadminname'),
            'sSiteAdminEmail' => Yii::app()->getConfig('siteadminemail'),
            'bShowClearAll' => false,
            'surveyls_title' => $siteName,
            'alanguageChanger' => ['show' => false],
        ];
        // Renders the page and ends the request
        Yii::app()->twigRenderer->renderTemplateFromFile('layout_maintenance.twig', ['aSurveyInfo' => $surveyInfo], false);
        Yii::app()->end();
    }

    /**
     * Renders the theme's maintenance page and ends the request when the maintenance mode keeps
     * participants from taking surveys ("hard" and "hard-cli"), see SettingGlobal::areParticipantsLocked().
     * The language must be set before, so that the page is shown in the survey's theme and language.
     *
     * @param Survey $survey The survey the request belongs to
     * @return void
     */
    protected function endRequestIfParticipantsLocked(Survey $survey)
    {
        if (!SettingGlobal::areParticipantsLocked()) {
            return;
        }
        Template::model()->getInstance('', $survey->sid);
        // Renders the page and ends the request
        Yii::app()->twigRenderer->renderTemplateFromFile(
            'layout_maintenance.twig',
            ['oSurvey' => $survey, 'aSurveyInfo' => getSurveyInfo($survey->sid, App()->language)],
            false
        );
    }

    /**
     * Loads a helper
     *
     * @access public
     * @param string $helper
     * @return void
     */
    public function loadHelper($helper)
    {
        Yii::app()->loadHelper($helper);
    }

    /**
     * Loads a library
     *
     * @access public
     * @param string $library
     * @return void
     */
    public function loadLibrary($library)
    {
        Yii::app()->loadLibrary($library);
    }

    protected function customInit()
    {
        // Check for most necessary requirements
        // Now check for PHP & db version
        // Do not localize/translate this!

        $dieoutput = '';
        if (version_compare(PHP_VERSION, InstallerConfigForm::MINIMUM_PHP_VERSION, '<')) {
            $dieoutput .= 'LimeSurvey requires PHP version ' . InstallerConfigForm::MINIMUM_PHP_VERSION . ' or later. Your version: ' . PHP_VERSION . '<br />';
        }

        if (!function_exists('mb_convert_encoding')) {
            $dieoutput .= "This script needs the PHP Multibyte String Functions library installed: See <a href='http://manual.limesurvey.org/wiki/Installation_FAQ'>FAQ</a> and <a href='http://de.php.net/manual/en/ref.mbstring.php'>PHP documentation</a><br />";
        }

        if ($dieoutput != '') {
            throw new CException($dieoutput);
        }

        if (ini_get("max_execution_time") < intval(Yii::app()->getConfig('max_execution_time'))) {
            if (!@set_time_limit(intval(Yii::app()->getConfig('max_execution_time')))) {
                Yii::log("Unable to set time limit to " . Yii::app()->getConfig('max_execution_time'), \CLogger::LEVEL_WARNING, 'application.controller.customInit.max_execution_time');
            }
        }
        if (ini_get('memory_limit') != -1 && convertPHPSizeToBytes(ini_get("memory_limit")) < convertPHPSizeToBytes(Yii::app()->getConfig('memory_limit') . 'M')) {
            if (@ini_set("memory_limit", Yii::app()->getConfig('memory_limit') . 'M') === false) {
                Yii::log("Unable to set memory_limit to " . App()->getConfig('memory_limit') . 'M', \CLogger::LEVEL_WARNING, 'application.controller.customInit.memory_limit');
            }
        }

        // 1% chance of cleaning up the temp/cache directories of old files (older than 1 day)
        if (rand(1, 100) == 1) {
            cleanCacheTempDirectoryDaily();
        }

        //GlobalSettings Helper
        Yii::import("application.helpers.globalsettings");

        enforceSSLMode(); // This really should be at the top but for it to utilise Yii::app()->getConfig() it has to be here

        //SET LOCAL TIME
        $displayTimezone = Yii::app()->getConfig("displayTimezone");
        // if the time zone is empty or does not exist, use UTC
        if (empty($displayTimezone) || !in_array($displayTimezone, timezone_identifiers_list())) {
            Yii::app()->setConfig("displayTimezone", "UTC");
        }

        // If an admin user is logged in, override with their personal timezone preference if set
        $loginID = Yii::app()->session['loginID'] ?? null;
        if (!empty($loginID)) {
            $userTimezone = SettingsUser::getUserSettingValue('displayTimezone', $loginID);
            if (!empty($userTimezone) && in_array($userTimezone, timezone_identifiers_list())) {
                Yii::app()->setConfig("displayTimezone", $userTimezone);
            }
        }

        /* Set the default language, other controller can update if wanted */
        Yii::app()->setLanguage(Yii::app()->getConfig("defaultlang"));
    }

    /**
     * Returns an absolute URL based on the given controller and action information.
     * The functionality was moved to
     * \LSYii_Application::createPublicUrl, to be safe the function remains here.
     * @param string $route the URL route. This should be in the format of 'ControllerID/ActionID'.
     * @param array $params additional GET parameters (name=>value). Both the name and value will be URL-encoded.
     * @param string $schema schema to use (e.g. http, https). If empty, the schema used for the current request will be used.
     * @param string $ampersand the token separating name-value pairs in the URL.
     * @return string the constructed URL
     */
    public function createAbsoluteUrl($route, $params = array(), $schema = '', $ampersand = '&')
    {
        return App()->createPublicUrl($route, $params, $schema, $ampersand);
    }

    /**
     * Loads page states from a hidden input.
     * @return array the loaded page states
     */
    protected function loadPageStates()
    {
        return array();
    }
}
