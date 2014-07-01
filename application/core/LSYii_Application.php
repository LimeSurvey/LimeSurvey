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
*/
class LSYii_Application extends CWebApplication
{
    protected $config = array();
    /**
     * @var Limesurvey_lang 
     */
    public $lang = null;

    /**
     *
     * @var PluginManager
     */
    protected $pluginManager;
    /**
     * @var LimesurveyApi
     */
    protected $api;
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
        if (is_string($config) && !file_exists($config))
        {
            $config = __DIR__ . '/../config/config-sample-mysql' . EXT;
        } 
        if(is_string($config)) {
            $config = require($config);
        }

        if (isset($config['config']['debug']) && $config['config']['debug'] == 2)
        {
            // If debug = 2 we add firebug / console logging for all trace messages
            // If you want to var_dump $config you could do:
            // 
            // Yii::trace(CVarDumper::dumpAsString($config), 'vardump');
            // 
            // or shorter:
            // 
            //traceVar($config);
            // 
            // This statement won't cause any harm or output when debug is 1 or 0             
            $config['preload'][] = 'log';
            if (array_key_exists('components', $config) && array_key_exists('log', $config['components'])) {
                // We already have some custom logging, only add our own
            } else {
                // No logging yet, set it up
                $config['components']['log'] = array(
                    'class' => 'CLogRouter');
            }
            // Add logging of trace
            $config['components']['log']['routes'][] = array(
                'class'                      => 'CWebLogRoute', // you can include more levels separated by commas... trace is shown on debug only
                'levels'                     => 'trace',        // you can include more separated by commas
                'categories'                 => 'vardump',      // show in firebug/console
                'showInFireBug'              => true
            );
            
            // if debugsql = 1 we add sql logging to the output
            if (array_key_exists('debugsql', $config['config']) && $config['config']['debugsql'] == 1) {
                // Add logging of trace
                $config['components']['log']['routes'][] = array(
                    'class'                      => 'CWebLogRoute', // you can include more levels separated by commas... trace is shown on debug only
                    'levels'                     => 'trace',        // you can include more separated by commas
                    'categories'                 => 'system.db.*',      // show in firebug/console
                    'showInFireBug'              => true
                );
                $config['components']['db']['enableProfiling'] = true;
                $config['components']['db']['enableParamLogging'] = true;
            }
        }

        if (!isset($config['components']['request']))
        {
            $config['components']['request']=array();
        }
        if (!isset($config['components']['session']))
        {
            $config['components']['session']=array();
        }        
        $config['components']['session']=array_merge_recursive($config['components']['session'],array(
            'cookieParams' => array(
                'httponly' => true,
            ),
        ));        

        if (!isset($config['components']['assetManager']))
        {
            $config['components']['assetManager']=array();
        }        
        $config['components']['assetManager']=array_merge_recursive($config['components']['assetManager'],array(
            'basePath'=> dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'assets'   // Enable to activate cookie protection
        ));

        parent::__construct($config);
        Yii::setPathOfAlias('bootstrap' , Yii::getPathOfAlias('ext.bootstrap'));
        // Load the default and environmental settings from different files into self.
        $ls_config = require(__DIR__ . '/../config/config-defaults.php');
        $email_config = require(__DIR__ . '/../config/email.php');
        $version_config = require(__DIR__ . '/../config/version.php');
        $settings = array_merge($ls_config, $version_config, $email_config);
        
        if(file_exists(__DIR__ . '/../config/config.php'))
        {
            $ls_config = require(__DIR__ . '/../config/config.php');
            if(is_array($ls_config['config']))
            {
                $settings = array_merge($settings, $ls_config['config']);
            }
        }

        foreach ($settings as $key => $value)
            $this->setConfig($key, $value);

        App()->getAssetManager()->setBaseUrl(Yii::app()->getBaseUrl(false) . '/tmp/assets');
        // Now initialize the plugin manager
        $this->initPluginManager(); 
        
    }


	public function init() {
		parent::init();
		Yii::import('application.helpers.ClassFactory');
		ClassFactory::registerClass('Token_', 'Token');
		ClassFactory::registerClass('Response_', 'Response');
	}
    /**
     * This method handles initialization of the plugin manager
     * 
     * When you want to insert your own plugin manager, or experiment with different settings
     * then this is where you should do that.
     */
    public function initPluginManager()
    {
        Yii::import('application.libraries.PluginManager.*');
        Yii::import('application.libraries.PluginManager.Storage.*');
        Yii::import('application.libraries.PluginManager.Question.*');
        $this->pluginManager = new PluginManager($this->getApi());
        
        // And load the active plugins
        $this->pluginManager->loadPlugins();
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
     * @return LSYii_Application Provides a fluent interface
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
    * @param Limesurvey_lang
    * @return void
    */
    public function setLang(Limesurvey_lang $lang)
    {
        $this->lang = $lang;
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
        return $this->pluginManager;
    }


}

