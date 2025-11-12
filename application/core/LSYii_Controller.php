<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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

        //Yii::app()->session->init();
        // Deprecated function
        $this->loadHelper('globalsettings');
        // tracevar function
        //$this->loadHelper('common');
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
        if (version_compare(PHP_VERSION, '5.3.3', '<')) {
            $dieoutput .= 'This script can only be run on PHP version 5.3.3 or later! Your version: ' . PHP_VERSION . '<br />';
        }

        if (!function_exists('mb_convert_encoding')) {
            $dieoutput .= "This script needs the PHP Multibyte String Functions library installed: See <a href='http://manual.limesurvey.org/wiki/Installation_FAQ'>FAQ</a> and <a href='http://de.php.net/manual/en/ref.mbstring.php'>PHP documentation</a><br />";
        }

        if ($dieoutput != '') {
            throw new CException($dieoutput);
        }

        if (ini_get("max_execution_time") < intval(App()->getConfig('max_execution_time'))) {
            if(!@set_time_limit(intval(App()->getConfig('max_execution_time')))) {
                Yii::log("Unable to set time limit to " . App()->getConfig('max_execution_time'), \CLogger::LEVEL_WARNING, 'application.controller');
            }
        }
        if (ini_get('memory_limit') != -1 && convertPHPSizeToBytes(ini_get("memory_limit")) < convertPHPSizeToBytes(Yii::app()->getConfig('memory_limit') . 'M')) {
            if (@ini_set("memory_limit", Yii::app()->getConfig('memory_limit') . 'M') === false) {
                Yii::log("Unable to set memory_limit to " . App()->getConfig('memory_limit') . 'M', \CLogger::LEVEL_WARNING, 'application.controller');
            }
        }

        // 1% chance of cleaning up the temp directory of old files (older than 1 day)
        if (rand(1, 100) == 1) {
            cleanTempDirectory();
        }

        //GlobalSettings Helper
        Yii::import("application.helpers.globalsettings");

        enforceSSLMode(); // This really should be at the top but for it to utilise getGlobalSetting() it has to be here

        //SET LOCAL TIME
        $timeadjust = Yii::app()->getConfig("timeadjust");
        if (substr((string) $timeadjust, 0, 1) != '-' && substr((string) $timeadjust, 0, 1) != '+') {
            $timeadjust = '+' . $timeadjust;
        }
        if (strpos((string) $timeadjust, 'hours') === false && strpos((string) $timeadjust, 'minutes') === false && strpos((string) $timeadjust, 'days') === false) {
            Yii::app()->setConfig("timeadjust", $timeadjust . ' hours');
        }
        /* Set the default language, other controller can update if wanted */
        Yii::app()->setLanguage(Yii::app()->getConfig("defaultlang"));
    }

    /**
     * Returns an absolute URL based on the given controller and action information.
     * The functionalty was moved to
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
