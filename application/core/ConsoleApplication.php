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
     * Returns a configuration variable from the config array or object properties.
     *
     * This method searches for the requested configuration value in the following order:
     * 1. As a property of the current object
     * 2. In the config array
     * 3. If not found, returns the provided default value, otherwise false.
     *
     * @access public
     * @param string|null $name The name of the configuration variable to retrieve. If null, the default value will be returned.
     * @param mixed $default The default value to return if the configuration variable is not found. Defaults to false.
     * @return mixed The value of the configuration variable if found, otherwise the default value or false if no default value was provided
     */
    public function getConfig($name = null, $default = false)
    {
        if (isset($this->$name)) {
            return $this->name;
        } elseif (isset($this->config[$name])) {
            return $this->config[$name];
        } else {
            return $default;
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
}
