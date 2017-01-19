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

    public function __construct($config = null) {
        parent::__construct($config);

        // Set webroot alias.
        Yii::setPathOfAlias('webroot', realpath(Yii::getPathOfAlias('application') . '/../'));
        /* Load all core configs */
        $lsConfig = require(Yii::getPathOfAlias('application.config.config-defaults') . '.php');
        $consoleConfig = require(Yii::getPathOfAlias('application.config.console') . '.php');
        $emailConfig = require(Yii::getPathOfAlias('application.config.email') . '.php');
        $versionConfig = require(Yii::getPathOfAlias('application.config.version') . '.php');
        $updaterVersionConfig = require(Yii::getPathOfAlias('application.config.updater_version') . '.php');
        $this->config = array_merge($this->config, $lsConfig,$consoleConfig,$emailConfig,$versionConfig,$updaterVersionConfig);

        /* Load this installation config part if exist */
        $userConfigFile=Yii::getPathOfAlias('application.config.config') . '.php';
        if(file_exists($userConfigFile)) {
            $appSettings = require($userConfigFile);
            if(is_array($appSettings['config'])) {
                $this->config = array_merge($this->config,$appSettings['config']);
            }
        }

        /* Load the database settings : if available */
        $settingsTableExist = Yii::app()->db->schema->getTable('{{settings_global}}');
        if(is_object($settingsTableExist)){
            $dbConfig=CHtml::listData(SettingGlobal::model()->findAll(), 'stg_name', 'stg_value');
            $this->config = array_merge($this->config,$dbConfig);
        }
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
