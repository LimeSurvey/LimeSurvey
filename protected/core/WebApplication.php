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
*/
use ls\components\LocalizedFormatter;
use ls\components\MigrationManager;
use ls\components\SurveySessionManager;
use ls\components\WebUser;
use ls\models\SettingGlobal;

/**
 * Implements global  config
 * @property CLogRouter $log Log router component.
 * @property LocalizedFormatter $format
 * @property \ls\pluginmanager\PluginManager $pluginManager
 * @property DbConnection $db
 * @property SurveySessionManager $surveySessionManager
 * @property \HttpRequest $request;
 * @property WebUser $user
 * @property CCache $cache
 * @property CHttpSession $session
 * @property CClientScript $clientScript
 * @property MigrationManager $migrationManager
 * @property CSecurityManager $securityManager
 * @property \ls\components\ThemeManager $themeManager
 * @property-read string $publicUrl The url of the public folder.
 * @property CTheme $theme
 */
class WebApplication extends CWebApplication
{

    /**
     *
     * @var Composer\Autoload\ClassLoader
     */
    public $loader;
    protected $config = array();

    /**
     * @var LimesurveyApi
     */
    protected $api;
    
    protected $_supportedLanguages;
    
    public function getMaintenanceMode() {
        return file_exists(__DIR__ . '/../config/MAINTENANCE');
    }
    public function setMaintenanceMode($value) {
        @unlink(__DIR__ . '/../config/MAINTENANCE');
    }
    

    public function processRequest()
    {
        /**
         * Hook installer.
         */
        if (!$this->isInstalled && strncmp('installer', $this->getUrlManager()->parseUrl($this->getRequest()), 9) != 0) {
            $this->catchAllRequest = [
                'installer'
            ];
        }
        /**
         * Add support for maintenance mode.
         */
        if ($this->maintenanceMode && strncmp('upgrade', $this->getUrlManager()->parseUrl($this->getRequest()), 7) != 0) {
            $this->catchAllRequest = [
                'upgrade'
            ];
        }
        return parent::processRequest();
    }

    public function setSupportedLanguages($value) {
        foreach($value as $code => $language) {
            $language['code'] = $code;
            $this->_supportedLanguages[$code] = $language;
        }
    }
    
    public function getSupportedLanguages() {
        return $this->_supportedLanguages;
    }

    public function getIsInstalled() {
        $components = $this->getComponents(false);
        return is_object($components['db'])
            || isset($components['db']['connectionString']);
    }
    /**
     *
    * Initiates the application
    *
    * @access public
    * @param array $config
    * @return void
    */
    public function __construct($config = null)
    {
        parent::__construct($config);


        Yii::import('application.helpers.common_helper', true);

        // Load the default and environmental settings from different files into self.
        $ls_config = require(__DIR__ . '/../config/config-defaults.php');
        $email_config = require(__DIR__ . '/../config/email.php');
        $settings = array_merge($ls_config, $email_config);

        foreach ($settings as $key => $value)
            $this->setConfig($key, $value);

    }



	public function init() {
		parent::init();
        $this->name = SettingGlobal::get('sitename', 'LimeSurvey');
        $this->initLanguage();
        // These take care of dynamically creating a class for each token / response table.
		ClassFactory::registerClass('ls\models\Token_', \ls\models\Token::class);
		ClassFactory::registerClass('ls\models\Response_', \ls\models\Response::class);
	}

    public function initLanguage()
    {
        // Set language to use.
        if ($this->request->getParam('lang') !== null) {
            $this->session->add('language', $this->request->getParam('lang'));
        }
        if (isset($this->session['language'])) {
            $this->setLanguage($this->session['language']);
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
        return Yii::import('application.helpers.' . $helper . '_helper', true);
    }

    /**
    * Loads a library
    *
    * @access public
    * @param string $helper
    * @return void
    */
    public function loadLibrary($library)
    {
        Yii::import('application.libraries.'.$library, true);
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
    * Loads a config from a file
    *
    * @access public
    * @param string $file
    * @return void
    */
    public function loadConfig($file)
    {
        $config = require_once(Yii::getPathOfAlias('application.config') . "/$file.php");
        if(is_array($config))
        {
            foreach ($config as $k => $v)
                $this->setConfig($k, $v);
        }
    }

    /**
    * Returns a config variable from the config
    *
    * @access public
    * @param string $name
    * @param type $default Value to return when not found, default is false
    * @return mixed
    */
    public function getConfig($name, $default = false)
    {
        if ($name == 'adminstyleurl') {
            return $this->getPublicUrl() . '/styles/gringegreen/';
        }
        return isset($this->config[$name]) ? $this->config[$name] : $default;
    }


    /**
    * For future use, cache the language app wise as well.
    *
    * @access public
    * @return void
    */
    public function setLanguage( $sLanguage )
    {
        $this->messages->catalog = $sLanguage;
        parent::setLanguage($sLanguage);
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
     * Get the pluginManager
     *
     * @return \ls\pluginmanager\PluginManager
     */
    public function getPluginManager()
    {
        return $this->getComponent('pluginManager');
    }

   public function disableWebLogRoutes() {
       foreach ($this->log->routes as $route)
       {
           $route->enabled = $route->enabled && !($route instanceOf CWebLogRoute);
       }
   }

    public function getPublicUrl($absolute = false) {
        return $this->getBaseUrl($absolute) . Yii::getPathOfAlias('public');
    }
}

