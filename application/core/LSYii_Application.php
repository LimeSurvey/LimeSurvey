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
     * The DB version, used to check if setup is all OK
     * @var integer|null
     */
    protected $dbVersion;

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
        $configdir = $baseConfig['configdir'];

        if (file_exists( $configdir .  '/config.php')) {
            $userConfigs = require(  $configdir .'/config.php');
            if (is_array($userConfigs['config'])) {
                $baseConfig = array_merge($baseConfig, $userConfigs['config']);
            }
        }

        /* Set the runtime path according to tempdir if needed */
        if (!isset($aApplicationConfig['runtimePath'])) {
            $aApplicationConfig['runtimePath'] = $baseConfig['tempdir'].DIRECTORY_SEPARATOR.'runtime';
        } /* No need to test runtimePath validity : Yii return an exception without issue */


        /* If LimeSurvey is configured to load custom Twig exstensions, add them to Twig Component */
        if (array_key_exists('use_custom_twig_extensions',$baseConfig ) && $baseConfig ['use_custom_twig_extensions'] ){
          $aApplicationConfig = $this->getTwigCustomExtensionsConfig($baseConfig['usertwigextensionrootdir'], $aApplicationConfig);
        }

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

        // TODO: check the whole configuration process. It must be easier and clearer. Too many repitions

        /* Default config */
        $coreConfig = require(__DIR__.'/../config/config-defaults.php');
        $emailConfig = require(__DIR__.'/../config/email.php');
        $versionConfig = require(__DIR__.'/../config/version.php');
        $updaterVersionConfig = require(__DIR__.'/../config/updater_version.php');
        $this->config = array_merge($this->config,$coreConfig, $emailConfig, $versionConfig, $updaterVersionConfig);

        /* Custom config file */
        $configdir = $coreConfig['configdir'];
        if (file_exists( $configdir .  '/config.php')) {
            $userConfigs = require(  $configdir .'/config.php');
            if (is_array($userConfigs['config'])) {

                $this->config = array_merge($this->config, $userConfigs['config']);

            }
        }

        if(!file_exists(__DIR__.'/../config/config.php')) {
            /* Set up not done : then no other part to update */
            return;
        }
        /* User file config */
        $userConfigs = require(__DIR__.'/../config/config.php');
        if (is_array($userConfigs['config'])) {
             $this->config = array_merge($this->config, $userConfigs['config']);
        }
        /* Check DB : let throw error if DB is broken issue #14875 */
        $settingsTableExist = Yii::app()->db->schema->getTable('{{settings_global}}');
        /* No table settings_global : not installable or updatable */
        if(empty($settingsTableExist)) {
            /* settings_global was created before 1.80 : not updatable version or not installed (but table exist) */
            Yii::log("LimeSurvey table settings_global not found in database",'error');
            throw new CDbException("LimeSurvey table settings_global not found in database");
        }
        $dbConfig = CHtml::listData(SettingGlobal::model()->findAll(), 'stg_name', 'stg_value');
        $this->config = array_merge($this->config, $dbConfig);
        /* According to updatedb_helper : no update can be done before settings_global->DBVersion > 183, then set it only if upper to 183 */
        if(!empty($dbConfig['DBVersion']) && $dbConfig['DBVersion'] > 183) {
            $this->dbVersion = $dbConfig['DBVersion'];
        }
        /* Add some specific config using exiting other configs */
        $this->setConfig('globalAssetsVersion', /* Or create a new var ? */
            Yii::getVersion().
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
        if(!Yii::app() instanceof CWebApplication) {
            /* Don't update for CLI */
            return;
        }
        if(defined('PHP_ENV') && PHP_ENV == 'test') {
            // If run from phpunit, die with exception message.
            die($event->exception->getMessage());
        }
        if(!$this->dbVersion) {
            /* Not installed or DB broken or to old */
            return;
        }
        if($this->dbVersion < 200) {
            /* Activate since DBVersion for 2.50 and up (i know it include previous line, but stay clear) */
            return;
        }
        $statusCode = isset($event->exception->statusCode) ? $event->exception->statusCode : null; // Needed ?
        if (Yii::app()->getConfig('debug') > 1) {
            /* debug ro 2 : always send Yii debug even 404 */
            return;
        }
        if (Yii::app()->getConfig('debug') > 0 && $statusCode != '404') {
            /* debug is set and not a 404 : always send Yii debug*/
            return;
        }
        Yii::app()->setComponent('errorHandler', array(
            'errorAction'=>'surveys/error',
        ));
    }

    /**
     * Check if a file (with a full path) is inside a specific directory
     * @var string $filePath complete file path
     * @var string $baseDir the directory where it must be, default to upload dir
     * @var boolean|null $throwException if security issue
     * Throw Exception
     * @return boolean
     */
    public function is_file($filePath,$baseDir = null,$throwException = null)
    {
        if(is_null($baseDir)) {
            $baseDir = $this->getConfig('uploaddir');
        }
        if(is_null($throwException)) {
            $throwException = boolval($this->getConfig('debug'));
        }
        $realFilePath = realpath($filePath);
        $baseDir = realpath($baseDir);
        
        if(!is_file($realFilePath)) {
            /* Not existing file */
            Yii::log("Try to read invalid file ".$filePath, 'warning', 'application.security.files.is_file');
            return false;
        }
        if(substr($realFilePath, 0, strlen($baseDir)) !== $baseDir) {
            /* Security issue */
            Yii::log("Disable access to ".$realFilePath." directory", 'error', 'application.security.files.is_file');
            if($throwException) {
                throw new CHttpException(403,"Disable for security reasons.");
            }
            return false;
        }
        return $filePath;
    }

    /**
     * Look for user custom twig extension in upload directory, and add load their manifest in Twig Application and its sandbox
     * TODO: database uploader + admin interface grid view instead of XML parsing.
     *
     * @var string $sUsertwigextensionrootdir $baseConfig['usertwigextensionrootdir']
     * @var array $aApplicationConfig the application configuration
     */
    public function getTwigCustomExtensionsConfig( $sUsertwigextensionrootdir, $aApplicationConfig )
    {

        // First we look for each custom extension manifest.
        $directory = new \RecursiveDirectoryIterator($sUsertwigextensionrootdir);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = array();

        foreach ($iterator as $info) {
          $ext = pathinfo($info->getPathname(), PATHINFO_EXTENSION);
          if ($ext=='xml') {
            $CustomTwigExtensionsManifestFiles[] = $info->getPathname();
          }
        }

        // Then we read each manifest and add their functions to Twig Component
        $bOldEntityLoaderState = libxml_disable_entity_loader(true);             // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection

        foreach ($CustomTwigExtensionsManifestFiles as $ctemFile){
          $sXMLConfigFile        = file_get_contents( realpath ($ctemFile));  // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string
          $oXMLConfig = simplexml_load_string($sXMLConfigFile);

          // Get the functions.
          // TODO: get the tags, filters, etc
          $aFunctions = (array) $oXMLConfig->xpath("//function");
          $extensionClass =  (string) $oXMLConfig->metadata->name;

          if (!empty($aFunctions) && !empty($extensionClass) ){

            // We add the extension to twig user extensions to load
            // See: https://github.com/LimeSurvey/LimeSurvey/blob/cec66adb1a74a518525e6a4fc4fe208c50595067/third_party/Twig/ETwigViewRenderer.php#L125-L133
            $aApplicationConfig['components']['twigRenderer']['user_extensions'][] = $extensionClass;

            // Then we add the functions to the Twig Component and its sandbox
            // See:  https://github.com/LimeSurvey/LimeSurvey/blob/cec66adb1a74a518525e6a4fc4fe208c50595067/application/config/internal.php#L233-#L398
            foreach($aFunctions as $function){
              $functionNameInTwig = (string) $function['twig-name'];
              $functionNameInExt  = (string) $function['extension-name'];
              $aApplicationConfig['components']['twigRenderer']['functions'][$functionNameInTwig] =  $functionNameInExt;
              $aApplicationConfig['components']['twigRenderer']['sandboxConfig']['functions'][] = $functionNameInTwig;
            }
          }
        }

        libxml_disable_entity_loader($bOldEntityLoaderState);                   // Put back entity loader to its original state, to avoid contagion to other applications on the server

        return $aApplicationConfig;
    }
}
