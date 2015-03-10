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

/**
 * Load the globals helper as early as possible. Only earlier solution is to use
 * index.php
 */
require_once(dirname(dirname(__FILE__)) . '/helpers/globals.php');
/**
 * Implements global  config
 * @property CLogRouter $log Log router component.
 * @property LocalizedFormatter $format
 * @property \ls\pluginmanager\PluginManager $pluginManager 
 * @property WebUser $user
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
    
    public function onBeginRequest($event) {
        parent::onBeginRequest($event);
        /**
         * Add support for maintenance mode.
         */
        if ($this->maintenanceMode) {
            $this->catchAllRequest = [
                'upgrade'
            ];
        }
        return true;
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
        $this->getAssetManager()->setBaseUrl(Yii::app()->getBaseUrl(false) . '/tmp/assets');
        $this->initLanguage();
        // These take care of dynamically creating a class for each token / response table.
		Yii::import('application.helpers.ClassFactory');
		ClassFactory::registerClass('Token_', 'Token');
		ClassFactory::registerClass('Response_', 'Response');
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
        Yii::import('application.helpers.' . $helper . '_helper', true);
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
     * Set a 'flash message'.
     *
     * A flahs message will be shown on the next request and can contain a message
     * to tell that the action was successful or not. The message is displayed and
     * cleared when it is shown in the view using the widget:
     * <code>
     * $this->widget('application.extensions.FlashMessage.FlashMessage');
     * </code>
     *
     * @param string $message
     * @param string $type
     * @return WebApplication Provides a fluent interface
     */
    public function setFlashMessage($message,$type='default')
    {
        $aFlashMessage=$this->session['aFlashMessage'];
        $aFlashMessage[]=array('message'=>$message,'type'=>$type);
        $this->session['aFlashMessage'] = $aFlashMessage;
        return $this;
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
        $config = require_once(APPPATH . '/config/' . $file . '.php');
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
     * @return PluginManager
     */
    public function getPluginManager()
    {
        return $this->getComponent('pluginManager');
    }

    /**
	 * Check that installation was already done by looking for config.php
	 * Will redirect to the installer script if not exists.
	 *
	 * @access protected
	 * @return void
	 */
	public function runController($route) {
        $file_name = __DIR__ . '/../config/config.php';
        if (!file_exists($file_name) && substr_compare('installer', $route, 0, 9) != 0) {
			$this->request->redirect($this->urlManager->createUrl('/installer'));
        }
        return parent::runController($route);
	}
}

