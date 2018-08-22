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
require_once(dirname(dirname(__FILE__)).'/helpers/globals.php');

/**
* Implements global config
* @property CLogRouter $log Log router component.
* @property string $language Returns the language that the user is using and the application should be targeted to.
* @property CClientScript $clientScript CClientScript manages JavaScript and CSS stylesheets for views.
* @property CHttpRequest $request The request component. 
* @property CDbConnection $db The database connection.
* @property string $baseUrl The relative URL for the application.
* @property CWebUser $user The user session information.
* @property LSETwigViewRenderer $twigRenderer Twig rendering plugin
* @property PluginManager $pluginManager The LimeSurvey Plugin manager
* @property TbApi $bootstrap The bootstrap renderer
* @property CHttpSession $session The HTTP session
* 
*/
class LSYii_Application extends CWebApplication
{
    protected $config = array();

    /**
     * @var LimesurveyApi
     */
    protected $api;

    /**
     * If a plugin action is accessed through the PluginHelper,
     * store it here.
     * @var iPlugin
     */
    protected $plugin;

    /**
     *
     * Initiates the application
     *
     * @access public
     * @param array $aApplicationConfig
     */
    public function __construct($aApplicationConfig = null)
    {
        /* Using some config part for app config, then load it before*/
        $baseConfig = require(__DIR__.'/../config/config-defaults.php');
        if (file_exists(__DIR__.'/../config/config.php')) {
            $userConfigs = require(__DIR__.'/../config/config.php');
            if (is_array($userConfigs['config'])) {
                $baseConfig = array_merge($baseConfig, $userConfigs['config']);
            }
        }

        /* Set the runtime path according to tempdir if needed */
        if (!isset($aApplicationConfig['runtimePath'])) {
            $aApplicationConfig['runtimePath'] = $baseConfig['tempdir'].DIRECTORY_SEPARATOR.'runtime';
        } /* No need to test runtimePath validity : Yii return an exception without issue */

        /* Construct CWebApplication */
        parent::__construct($aApplicationConfig);

        /* Because we have app now : we have to call again the config (usage of Yii::app() for publicurl) */
        $this->setConfigs();

        /* Update asset manager path and url only if not directly set in aApplicationConfig (from config.php),
         *  must do after reloading to have valid publicurl (the tempurl) */
        if (!isset($aApplicationConfig['components']['assetManager']['baseUrl'])) {
            App()->getAssetManager()->setBaseUrl($this->config['tempurl'].'/assets');
        }
        if (!isset($aApplicationConfig['components']['assetManager']['basePath'])) {
            App()->getAssetManager()->setBasePath($this->config['tempdir'].'/assets');
        }
    }

    /* @inheritdoc */
    public function init()
    {
        parent::init();
        $this->initLanguage();
        // These take care of dynamically creating a class for each token / response table.
        Yii::import('application.helpers.ClassFactory');
        ClassFactory::registerClass('Token_', 'Token');
        ClassFactory::registerClass('Response_', 'Response');
    }

    /* @inheritdoc */
    public function initLanguage()
    {
        // Set language to use.
        if ($this->request->getParam('lang') !== null) {
            $this->setLanguage($this->request->getParam('lang'));
        } elseif (isset(App()->session['_lang'])) {
            // See: http://www.yiiframework.com/wiki/26/setting-and-maintaining-the-language-in-application-i18n/
            $this->setLanguage(App()->session['_lang']);
        }
    }

    /**
     * Set the LimeSUrvey config array according to files and DB
     * @return void
     */
    public function setConfigs() {
        /* Default config */
        $coreConfig = require(__DIR__.'/../config/config-defaults.php');
        $emailConfig = require(__DIR__.'/../config/email.php');
        $versionConfig = require(__DIR__.'/../config/version.php');
        $updaterVersionConfig = require(__DIR__.'/../config/updater_version.php');
        $this->config = array_merge($this->config,$coreConfig, $emailConfig, $versionConfig, $updaterVersionConfig);
        if(!file_exists(__DIR__.'/../config/config.php')) {
            /* Set up not done : then no other part to update */
            return;
        }
        /* User file config */
        $userConfigs = require(__DIR__.'/../config/config.php');
        if (is_array($userConfigs['config'])) {
             $this->config = array_merge($this->config, $userConfigs['config']);
        }
        /* Database config */
        try {
            $settingsTableExist = Yii::app()->db->schema->getTable('{{settings_global}}');
            if (is_object($settingsTableExist)) {
                $dbConfig = CHtml::listData(SettingGlobal::model()->findAll(), 'stg_name', 'stg_value');
                $this->config = array_merge($this->config, $dbConfig);
            }
        } catch (Exception $exception) {
            /* Even if database can exist : don't throw exception, */
            /* @todo : find when settings_global was created with stg_name and stg_value, maybe can Throw Exception ? */
            Yii::log("Table settings_global not found");// Log it as LEVEL_INFO , application category
        }
        /* Add some specific config using exiting other configs */
        $this->setConfig('globalAssetsVersion', /* Or create a new var ? */
            $this->getConfig('assetsversionnumber',0).
            $this->getConfig('versionnumber',0).
            $this->getConfig('dbversionnumber',0).
            $this->getConfig('customassetversionnumber',1)
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
        Yii::import('application.helpers.'.$helper.'_helper', true);
    }

    /**
     * Loads a library
     *
     * @access public
     * @param string $library Libraby name
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
     * @param string $message The message you want to show on next page load
     * @param string $type Type can be 'success','info','warning','danger','error' which relate to the particular bootstrap alert classes - see http://getbootstrap.com/components/#alerts . Note: Option 'error' is synonymous to 'danger'
     * @return LSYii_Application Provides a fluent interface
     */
    public function setFlashMessage($message, $type = 'success')
    {
        $aFlashMessage = $this->session['aFlashMessage'];
        $aFlashMessage[] = array('message'=>$message, 'type'=>$type);
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
        $config = require_once(APPPATH.'/config/'.$file.'.php');
        if (is_array($config)) {
            foreach ($config as $k => $v) {
                            $this->setConfig($k, $v);
            }
        }
    }

    /**
     * Returns a config variable from the config
     *
     * @access public
     * @param string $name
     * @param boolean|mixed $default Value to return when not found, default is false
     * @return string
     */
    public function getConfig($name, $default = false)
    {
        return isset($this->config[$name]) ? $this->config[$name] : $default;
    }


    /**
     * For future use, cache the language app wise as well.
     *
     * @access public
     * @param string $sLanguage
     * @return void
     */
    public function setLanguage($sLanguage)
    {
        // This method is also called from AdminController and LSUser
        // But if a param is defined, it should always have the priority
        // eg: index.php/admin/authentication/sa/login/&lang=de
        if ($this->request->getParam('lang') !== null && in_array('authentication', explode('/', Yii::app()->request->url))) {
            $sLanguage = $this->request->getParam('lang');
        }

        $sLanguage = preg_replace('/[^a-z0-9-]/i', '', $sLanguage);
        $this->messages->catalog = $sLanguage;
        App()->session['_lang'] = $sLanguage; // See: http://www.yiiframework.com/wiki/26/setting-and-maintaining-the-language-in-application-i18n/
        parent::setLanguage($sLanguage);
    }

    /**
     * Get the Api object.
     */
    public function getApi()
    {
        if (!isset($this->api)) {
            $this->api = new \LimeSurvey\PluginManager\LimesurveyApi();
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
        /** @var PluginManager $pluginManager */
        $pluginManager = $this->getComponent('pluginManager');
        return $pluginManager;
    }

    /**
     * The pre-filter for controller actions.
     * This method is invoked before the currently requested controller action and all its filters
     * are executed. You may override this method with logic that needs to be done
     * before all controller actions.
     * @param CController $controller the controller
     * @param CAction $action the action
     * @return boolean whether the action should be executed.
     */
    public function beforeControllerAction($controller, $action)
    {
        /**
         * Plugin event done before all web controller action
         * Can set run to false to deactivate action
         */
        $event = new PluginEvent('beforeControllerAction');
        $event->set('controller', $controller->getId());
        $event->set('action', $action->getId());
        $event->set('subaction', Yii::app()->request->getParam('sa'));
        App()->getPluginManager()->dispatchEvent($event);
        return $event->get("run", parent::beforeControllerAction($controller, $action));
    }

    /**
     * Used by PluginHelper to make the controlling plugin
     * available from everywhere, e.g. from the plugin's models.
     * Corresponds to Yii::app()->getController()
     *
     * @param $plugin
     * @return void
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Return plugin, if any
     * @return object
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @see http://www.yiiframework.com/doc/api/1.1/CApplication#onException-detail
     * Set surveys/error for 404 error
     * @param CExceptionEvent $event
     * @return void
     */
    public function onException($event)
    {
        if (Yii::app() instanceof CWebApplication) {
            $configExists = file_exists(__DIR__.'/../config/config.php');
            $usingTestEnv = defined('PHP_ENV') && PHP_ENV == 'test';
            if ($usingTestEnv || !$configExists) {
                // If run from phpunit, die with exception message.
                die($event->exception->getMessage());
            } else {
                /* Here we have different possibility : maybe 400/401/403/404 with debug < 2 except for forced superadmin (currently : the 4 part don't show intersting information with debug*/
                /* 500 always if debug and for (forced) superadmin even if no debug is set (and try to show complete error in this case (see issue by olle about white page and log */
                if ((Yii::app()->getConfig('debug')<1  /* || Permission::hasGlobalPermission('superadmin') */) || $event->exception->statusCode=='404') {
                    Yii::app()->setComponent('errorHandler', array(
                        'errorAction'=>'surveys/error',
                    ));
                }
            }
        }
    }
}
