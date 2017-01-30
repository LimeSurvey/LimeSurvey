<?php

/**
* Load the globals helper as early as possible. Only earlier solution is to use
* index.php
*/
require_once(dirname(dirname(__FILE__)) . '/helpers/globals.php');

class ConsoleApplication extends CConsoleApplication
{
    protected $config = array();

    /**
    * @var LimesurveyApi
    */
    protected $api;

    public function getSession()
    {
        return $this->getComponent('session');
    }

    public function __construct($aApplicationConfig = null) {

        $coreConfig = require(__DIR__ . '/../config/config-defaults.php');
        $consoleConfig = require(__DIR__ . '/../config/console.php');
        $emaiConfig = require(__DIR__ . '/../config/email.php');
        $versionConfig = require(__DIR__ . '/../config/version.php');
        $updaterVersionConfig = require(__DIR__ . '/../config/updater_version.php');
        $lsConfig = array_merge($coreConfig, $consoleConfig, $emaiConfig, $versionConfig, $updaterVersionConfig);
        if(file_exists(__DIR__ . '/../config/config.php'))
        {
            $userConfig = require(__DIR__ . '/../config/config.php');
            if(is_array($userConfig['config']))
            {
                $lsConfig = array_merge($lsConfig, $userConfig['config']);
            }
        }
        // Runtime path has to be set before  parent constructor is executed
        // User can set it in own config using Yii
        if(!isset($aApplicationConfig['runtimePath'])){
            $aApplicationConfig['runtimePath']=$lsConfig['tempdir'] . DIRECTORY_SEPARATOR. 'runtime';
        }
        parent::__construct($aApplicationConfig);

        // Set webroot alias.
        Yii::setPathOfAlias('webroot', realpath(Yii::getPathOfAlias('application') . '/../'));

        $this->config = array_merge($this->config, $lsConfig);
    }

    /**
    * Get the Api object.
    */
    public function getApi()
    {
        if (!isset($this->api))
        {
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
        if (isset($this->$name))
        {
            return $this->name;
        }
        elseif (isset($this->config[$name]))
        {
            return $this->config[$name];
        }
        else
        {
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

    public function getPluginManager()
    {
        return $this->getComponent('pluginManager');
    }

}
?>
