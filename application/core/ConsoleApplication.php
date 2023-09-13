<?php

/**
* Load the globals helper as early as possible. Only earlier solution is to use
* index.php
*/

require_once(dirname(dirname(__FILE__)) . '/helpers/globals.php');
require_once __DIR__ . '/Traits/LSApplicationTrait.php';

use LimeSurvey\PluginManager\LimesurveyApi;

class ConsoleApplication extends CConsoleApplication
{
    use LSApplicationTrait;

    protected $config = array();

    /**
     * @var LimesurveyApi
     */
    protected $api;

    public function getSession()
    {
        return $this->getComponent('session');
    }

    public function __construct($aApplicationConfig = null)
    {

        /* Using some config part for app config, then load it before*/
        $baseConfig = require(__DIR__ . '/../config/config-defaults.php');
        if (file_exists(__DIR__ . '/../config/config.php')) {
            $userConfigs = require(__DIR__ . '/../config/config.php');
            if (is_array($userConfigs['config'])) {
                $baseConfig = array_merge($baseConfig, $userConfigs['config']);
            }
        }

        /* Set the runtime path according to tempdir if needed */
        if (!isset($aApplicationConfig['runtimePath'])) {
            $aApplicationConfig['runtimePath'] = $baseConfig['tempdir'] . DIRECTORY_SEPARATOR . 'runtime';
        } /* No need to test runtimePath validity : Yii return an exception without issue */

        /* Construct CWebApplication */
        parent::__construct($aApplicationConfig);

        // Set webroot alias.
        Yii::setPathOfAlias('webroot', realpath(Yii::getPathOfAlias('application') . '/../'));
        /* Because we have app now : we have to call again the config : can be done before : no real usage of url in console, but usage of getPathOfAlias */
        $coreConfig = require(__DIR__ . '/../config/config-defaults.php');
        $consoleConfig = require(__DIR__ . '/../config/console.php'); // Only for console : replace some config-defaults
        $emailConfig = require(__DIR__ . '/../config/email.php');
        $versionConfig = require(__DIR__ . '/../config/version.php');
        $updaterVersionConfig = require(__DIR__ . '/../config/updater_version.php');

        $lsConfig = array_merge(
            $coreConfig,
            $consoleConfig,
            $emailConfig,
            $versionConfig,
            $updaterVersionConfig
        );

        if (file_exists(__DIR__ . '/../config/security.php')) {
            $securityConfig = require(__DIR__ . '/../config/security.php');
            if (is_array($securityConfig)) {
                $lsConfig = array_merge($lsConfig, $securityConfig);
            }
        }
        /* Custom config file */
        $configdir = $coreConfig['configdir'];
        if (file_exists($configdir .  '/security.php')) {
            $securityConfig = require($configdir . '/security.php');
            if (is_array($securityConfig)) {
                $lsConfig = array_merge($lsConfig, $securityConfig);
            }
        }

        if (file_exists(__DIR__ . '/../config/config.php')) {
            $userConfigs = require(__DIR__ . '/../config/config.php');
            if (is_array($userConfigs['config'])) {
                $lsConfig = array_merge($lsConfig, $userConfigs['config']);
            }
        }
        $this->config = array_merge($this->config, $lsConfig);
        
        /* encrypt emailsmtppassword value, because emailsmtppassword in database is also encrypted
           it would be decrypted in LimeMailer when needed */
           $this->config['emailsmtppassword'] = LSActiveRecord::encryptSingle($this->config['emailsmtppassword']);

        /* Load the database settings : if available */
        try {
            $settingsTableExist = Yii::app()->db->schema->getTable('{{settings_global}}');
            if (is_object($settingsTableExist)) {
                $dbConfig = CHtml::listData(SettingGlobal::model()->findAll(), 'stg_name', 'stg_value');
                $this->config = array_merge($this->config, $dbConfig);
            }
        } catch (Exception $exception) {
            // Allow exception (install for example)
        }
    }

    /**
     * Get the Api object.
     */
    public function getApi()
    {
        if (!isset($this->api)) {
            $this->api = new LimesurveyApi();
        }
        return $this->api;
    }

    /**
     * This function is implemented since em_core_manager incorrectly requires
     * it to create urls.
     */
    public function getController()
    {
        return $this;
    }


    /**
     * Returns a config variable from the config
     *
     * @access public
     * @param string $name
     * @return mixed
     */
    public function getConfig($name = null)
    {
        if (isset($this->$name)) {
            return $this->name;
        } elseif (isset($this->config[$name])) {
            return $this->config[$name];
        } else {
            return false;
        }
    }

    /**
     * This method handles initialization of the plugin manager
     *
     * When you want to insert your own plugin manager, or experiment with different settings
     * then this is where you should do that.
     */


    /**
     * Loads a helper
     *
     * @access public
     * @param string $helper
     * @return void
     */
    public function loadHelper($helper)
    {
        Yii::import('application.helpers.' . $helper . '_helper', true);
    }

    /**
     * Sets a configuration variable into the config
     *
     * @access public
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setConfig($name, $value)
    {
        $this->config[$name] = $value;
    }


    /**
     * @return CAssetManager the asset manager component
     */
    public function getAssetManager()
    {
        return $this->getComponent('assetManager');
    }

    /**
     * Returns the client script manager.
     *
     * @return CClientScript the client script manager
     */
    public function getClientScript()
    {
        return $this->getComponent('clientScript');
    }

    /**
     * Returns the plugin manager
     * @return IApplicationComponent
     */
    public function getPluginManager()
    {
        return $this->getComponent('pluginManager');
    }

    /**
     * Creates an absolute URL based on the given controller and action information.
     * @param string $route the URL route. This should be in the format of 'ControllerID/ActionID'.
     * @param array $params additional GET parameters (name=>value). Both the name and value will be URL-encoded.
     * @param string $schema schema to use (e.g. http, https). If empty, the schema used for the current request will be used.
     * @param string $ampersand the token separating name-value pairs in the URL.
     * @return string the constructed URL
     */
    public function createPublicUrl($route, $params = array(), $schema = '', $ampersand = '&')
    {
        $sPublicUrl = $this->getPublicBaseUrl(true);
        $sActualBaseUrl = Yii::app()->getBaseUrl(true);
        if ($sPublicUrl !== $sActualBaseUrl) {
            $url = parent::createAbsoluteUrl($route, $params, $schema, $ampersand);
            if (substr((string)$url, 0, strlen((string)$sActualBaseUrl)) == $sActualBaseUrl) {
                $url = substr((string)$url, strlen((string)$sActualBaseUrl));
            }
            return trim((string)$sPublicUrl, "/") . $url;
        } else {
            return parent::createAbsoluteUrl($route, $params, $schema, $ampersand);
        }
    }

    /**
     * Returns the relative URL for the application while
     * considering if a "publicurl" config parameter is set to a valid url
     * @param boolean $absolute whether to return an absolute URL. Defaults to false, meaning returning a relative one.
     * @return string the relative or the configured public URL for the application
     */
    public function getPublicBaseUrl($absolute = false)
    {
        $sPublicUrl = Yii::app()->getConfig("publicurl");
        $aPublicUrl = parse_url($sPublicUrl);
        $baseUrl = parent::getBaseUrl($absolute);
        if (isset($aPublicUrl['scheme']) && isset($aPublicUrl['host'])) {
            $baseUrl = $sPublicUrl;
        }
        return $baseUrl;
    }
}
