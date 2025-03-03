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
require_once __DIR__ . '/Traits/LSApplicationTrait.php';

use LimeSurvey\Yii\Application\AppErrorHandler;

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
    use LSApplicationTrait;

    protected $config = array();

    /**
     * @var \LimeSurvey\PluginManager\LimesurveyApi
     */
    protected $api;

    /**
     * If a plugin action is accessed through the PluginHelper,
     * store it here.
     * @var \LimeSurvey\PluginManager\iPlugin
     */
    protected $plugin;

    /**
     * The DB version, used to check if setup is all OK
     * @var integer|null
     */
    protected $dbVersion;

    /* @var integer| null the current userId for all action */
    private $currentUserId;

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
        $baseConfig = require(__DIR__ . '/../config/config-defaults.php');
        $configdir = $baseConfig['configdir'];

        if (file_exists($configdir .  '/config.php')) {
            $userConfigs = require($configdir . '/config.php');
            if (is_array($userConfigs['config'])) {
                $baseConfig = array_merge($baseConfig, $userConfigs['config']);
            }
        }

        /* Set the runtime path according to tempdir if needed */
        if (!isset($aApplicationConfig['runtimePath'])) {
            $aApplicationConfig['runtimePath'] = $baseConfig['tempdir'] . DIRECTORY_SEPARATOR . 'runtime';
        } /* No need to test runtimePath validity : Yii return an exception without issue */

        /* If LimeSurvey is configured to load custom Twig exstensions, add them to Twig Component */
        if (array_key_exists('use_custom_twig_extensions', $baseConfig) && $baseConfig ['use_custom_twig_extensions']) {
            $aApplicationConfig = $this->getTwigCustomExtensionsConfig($baseConfig['usertwigextensionrootdir'], $aApplicationConfig);
        }

        /* Construct CWebApplication */
        parent::__construct($aApplicationConfig);

        /* Because we have app now : we have to call again the config (usage of Yii::app() for publicurl) */
        $this->setConfigs();
        /* Since session can be set by DB : need to be set again … */
        $this->setSessionByDB($aApplicationConfig);

        /* Update asset manager path and url only if not directly set in aApplicationConfig (from config.php),
         *  must do after reloading to have valid publicurl (the tempurl) */
        if (!isset($aApplicationConfig['components']['assetManager']['baseUrl'])) {
            App()->getAssetManager()->setBaseUrl($this->config['tempurl'] . '/assets');
        }
        if (!isset($aApplicationConfig['components']['assetManager']['basePath'])) {
            App()->getAssetManager()->setBasePath($this->config['tempdir'] . '/assets');
        }

        // Load common helper
        $this->loadHelper("common");
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
    public function setConfigs()
    {

        // TODO: check the whole configuration process. It must be easier and clearer. Too many repitions

        /* Default config */
        $coreConfig = require(__DIR__ . '/../config/config-defaults.php');
        $emailConfig = require(__DIR__ . '/../config/email.php');
        $versionConfig = require(__DIR__ . '/../config/version.php');
        $updaterVersionConfig = require(__DIR__ . '/../config/updater_version.php');
        $this->config = array_merge($this->config, $coreConfig, $emailConfig, $versionConfig, $updaterVersionConfig);

        /* Custom config file */
        $configdir = $coreConfig['configdir'];
        if (file_exists($configdir .  '/security.php')) {
            $securityConfig = require($configdir . '/security.php');
            if (is_array($securityConfig)) {
                $this->config = array_merge($this->config, $securityConfig);
            }
        }
        if (file_exists($configdir .  '/config.php')) {
            $userConfigs = require($configdir . '/config.php');
            if (is_array($userConfigs['config'])) {
                $this->config = array_merge($this->config, $userConfigs['config']);
            }
        }

        if (!file_exists(__DIR__ . '/../config/config.php')) {
            /* Set up not done : then no other part to update */
            return;
        }
        /* User file config */
        $userConfigs = require(__DIR__ . '/../config/config.php');
        if (is_array($userConfigs['config'])) {
            $this->config = array_merge($this->config, $userConfigs['config']);
        }

        /* encrypt emailsmtppassword value, because emailsmtppassword in database is also encrypted
           it would be decrypted in LimeMailer when needed */
        $this->config['emailsmtppassword'] = LSActiveRecord::encryptSingle($this->config['emailsmtppassword']);

        /* Check DB : let throw error if DB is broken issue #14875 */
        $settingsTableExist = Yii::app()->db->schema->getTable('{{settings_global}}');
        /* No table settings_global : not installable or updatable */
        if (empty($settingsTableExist)) {
            /* settings_global was created before 1.80 : not updatable version or not installed (but table exist) */
            Yii::log("LimeSurvey table settings_global not found in database", 'error');
            throw new CDbException("LimeSurvey table settings_global not found in database");
        }
        $dbConfig = CHtml::listData(SettingGlobal::model()->findAll(), 'stg_name', 'stg_value');
        $this->config = array_merge($this->config, $dbConfig);
        /* According to updatedb_helper : no update can be done before settings_global->DBVersion > 183, then set it only if upper to 183 */
        if (!empty($dbConfig['DBVersion']) && $dbConfig['DBVersion'] > 183) {
            $this->dbVersion = $dbConfig['DBVersion'];
        }
        /* Add some specific config using exiting other configs */
        $this->setConfig(
            'globalAssetsVersion', /* Or create a new var ? */
            Yii::getVersion() .
            $this->getConfig('assetsversionnumber', 0) .
            $this->getConfig('versionnumber', 0) .
            $this->getConfig('dbversionnumber', 0) .
            $this->getConfig('customassetversionnumber', 1)
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
        Yii::import('application.helpers.' . $helper . '_helper', true);
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
        Yii::import('application.libraries.' . $library, true);
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
        $aFlashMessage[] = array('message' => $message, 'type' => $type);
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
        return $this->config[$name] ?? $default;
    }

    /**
     * Returns the array of available configurations
     *
     * @access public
     * @return array
     */
    public function getAvailableConfigs()
    {
        return $this->config;
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
        if ($this->request->getParam('lang') !== null && in_array('authentication', explode('/', (string) Yii::app()->request->url))) {
            $sLanguage = $this->request->getParam('lang');
        }

        $sLanguage = preg_replace('/[^a-z0-9-]/i', '', (string) $sLanguage);
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
     * @return \LimeSurvey\PluginManager\PluginManager
     */
    public function getPluginManager(): \LimeSurvey\PluginManager\PluginManager
    {
        /** @var \LimeSurvey\PluginManager\PluginManager $pluginManager */
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
        (new AppErrorHandler())->onException($this->dbVersion, $event);
    }

    /**
     * @see http://www.yiiframework.com/doc/api/1.1/CApplication#onError-detail

     * @param CErrorEvent $event
     * @return void
     */
    public function onError($event)
    {
        (new AppErrorHandler())->onError($this->dbVersion, $event);
    }

    /**
     * Check if a file (with a full path) is inside a specific directory
     * @var string $filePath complete file path
     * @var string $baseDir the directory where it must be, default to upload dir
     * @var boolean|null $throwException if security issue
     * Throw Exception
     * @return boolean
     */
    public function is_file($filePath, $baseDir = null, $throwException = null)
    {
        if (is_null($baseDir)) {
            $baseDir = $this->getConfig('uploaddir');
        }
        if (is_null($throwException)) {
            $throwException = boolval($this->getConfig('debug'));
        }
        $realFilePath = realpath($filePath);
        $baseDir = realpath($baseDir);
        if (!is_file($realFilePath)) {
            /* Not existing file */
            Yii::log("Try to read invalid file " . $filePath, 'warning', 'application.security.files.is_file');
            return false;
        }
        if (substr($realFilePath, 0, strlen($baseDir)) !== $baseDir) {
            /* Security issue */
            Yii::log("Disable access to " . $realFilePath . " directory", 'error', 'application.security.files.is_file');
            if ($throwException) {
                throw new CHttpException(403, "Disable for security reasons.");
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
    public function getTwigCustomExtensionsConfig($sUsertwigextensionrootdir, $aApplicationConfig)
    {

        // First we look for each custom extension manifest.
        $directory = new \RecursiveDirectoryIterator($sUsertwigextensionrootdir);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = array();

        foreach ($iterator as $info) {
            $ext = pathinfo((string) $info->getPathname(), PATHINFO_EXTENSION);
            if ($ext == 'xml') {
                $CustomTwigExtensionsManifestFiles[] = $info->getPathname();
            }
        }

        // Then we read each manifest and add their functions to Twig Component
        if (\PHP_VERSION_ID < 80000) {
            $bOldEntityLoaderState = libxml_disable_entity_loader(true);             // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection
        }

        foreach ($CustomTwigExtensionsManifestFiles as $ctemFile) {
            $sXMLConfigFile = file_get_contents(realpath($ctemFile));  // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string
            $oXMLConfig = simplexml_load_string($sXMLConfigFile);

            // Get the functions.
            // TODO: get the tags, filters, etc
            $aFunctions = (array)$oXMLConfig->xpath("//function");
            $extensionClass = (string)$oXMLConfig->metadata->name;

            if (!empty($aFunctions) && !empty($extensionClass)) {
                // We add the extension to twig user extensions to load
                // See: https://github.com/LimeSurvey/LimeSurvey/blob/cec66adb1a74a518525e6a4fc4fe208c50595067/third_party/Twig/ETwigViewRenderer.php#L125-L133
                $aApplicationConfig['components']['twigRenderer']['user_extensions'][] = $extensionClass;

                // Then we add the functions to the Twig Component and its sandbox
                // See:  https://github.com/LimeSurvey/LimeSurvey/blob/cec66adb1a74a518525e6a4fc4fe208c50595067/application/config/internal.php#L233-#L398
                foreach ($aFunctions as $function) {
                    $functionNameInTwig = (string)$function['twig-name'];
                    $functionNameInExt = (string)$function['extension-name'];
                    $aApplicationConfig['components']['twigRenderer']['functions'][$functionNameInTwig] = $functionNameInExt;
                    $aApplicationConfig['components']['twigRenderer']['sandboxConfig']['functions'][] = $functionNameInTwig;
                }
            }
        }

        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader($bOldEntityLoaderState);                   // Put back entity loader to its original state, to avoid contagion to other applications on the server
        }

        return $aApplicationConfig;
    }

    /**
     * @inheritdoc
     * Special handling for SEO friendly URLs
     */
    public function createController($route, $owner = null)
    {
        $controller = parent::createController($route, $owner);

        // If no controller is found by standard ways, check if the route matches
        // an existing survey's alias.
        if (is_null($controller)) {
            $controller = $this->createControllerFromShortUrl($route);
        }

        return $controller;
    }

    /**
     * Create controller from short url if the route matches a survey alias.
     * @param string $route the route of the request.
     * @return array<mixed>|null
     */
    private function createControllerFromShortUrl($route)
    {
        $route = ltrim($route, "/");
        $alias = explode("/", $route)[0];
        if (empty($alias)) {
            return null;
        }

        // When updating from versions that didn't support short urls, this code runs before the update process,
        // so we cannot asume the field exists. We try to retrieve the Survey Language Settings and, if it fails,
        // just don't do anything.
        try {
            $criteria = new CDbCriteria();
            $criteria->addCondition('surveyls_alias = :alias');
            $criteria->params[':alias'] = $alias;
            $criteria->index = 'surveyls_language';

            $languageSettings = SurveyLanguageSetting::model()->find($criteria);
        } catch (CDbException $ex) {
            // It's probably just because the field doesn't exist, so don't do anything.
        }

        if (empty($languageSettings)) {
            return null;
        }

        // If no language is specified in the request, add a GET param based on the survey's language for this alias
        $language = $this->request->getParam('lang');
        if (empty($language)) {
            $_GET['lang'] = $languageSettings->surveyls_language;
        }
        return parent::createController("survey/index/sid/" . $languageSettings->surveyls_survey_id);
    }

    /**
     * Set the session after start,
     * Limited to DbHttpSession
     * @param array Application config
     * @return void
     */
    private function setSessionByDB($aApplicationConfig)
    {
        if (empty($aApplicationConfig['components']['session']['class'])) {
            /* No specific session */
            return;
        }
        if ($aApplicationConfig['components']['session']['class'] != "application.core.web.DbHttpSession") {
            /* Not included DbHttpSession */
            return;
        }
        if (!empty($aApplicationConfig['components']['session']['cookieParams']['lifetime'])) {
            /* lifetime already updated */
            return;
        }
        $lifetime = intval(App()->getConfig('iSessionExpirationTime', ini_get('session.cookie_lifetime')));
        App()->getSession()->setCookieParams([
            'lifetime' => $lifetime
        ]);
    }

    /**
     * Creates a table based on another
     *
     * @param string $table
     * @param string $pattern
     * @param array $columns
     * @param array $where
     * @return integer number of rows affected by the execution.
     * @throws CDbException execution failed
     */
    public function createTableFromPattern($table, $pattern, $columns = [], $where = [])
    {
        if (!is_array($columns)) {
            $columns = [];
        }
        if (!is_array($where)) {
            $where = [];
        }
        $whereClause = "";
        $criterias = [];
        if (count($where)) {
            foreach ($where as $field => $value) {
                $criterias[] = $this->db->quoteColumnName($field) . " = " . $this->db->quoteValue($value);
            }
            $whereClause = " WHERE " . implode(" AND ", $criterias);
        }
        if (count($columns)) {
            foreach ($columns as $index => $column) {
                if (!ctype_alnum($column)) {
                    $columns[$index] = $this->db->quoteColumnName($column);
                }
            }
            $command = "CREATE TABLE " . $this->db->quoteTableName($table) . " AS SELECT " . implode(",", $columns) . " FROM " . $this->db->quoteTableName($pattern) . $whereClause;
        } else {
            $command = "CREATE TABLE " . $this->db->quoteTableName($table) . " LIKE " . $this->db->quoteTableName($pattern) . ";";
        }
        return $this->db->createCommand($command)->execute();
    }

    /**
     * Generates a temporary table creation script
     *
     * @param string $source
     * @param string $destination
     * @return string
     */
    protected function generateTemporaryTableCreate(string $source, string $destination)
    {
        return  "
            CREATE TEMPORARY TABLE {$destination}
            SELECT *
            FROM (
                SELECT SUBSTRING_INDEX(temp.COLUMN_NAME, 'X', 1) AS sid,
                       SUBSTRING_INDEX(SUBSTRING_INDEX(temp.COLUMN_NAME, 'X', 2), 'X', -1) AS gid,
                       SUBSTRING_INDEX(temp.COLUMN_NAME, 'X', -1) AS qidsuffix,
                       temp.COLUMN_NAME
                FROM information_schema.columns temp
                WHERE temp.TABLE_SCHEMA = DATABASE() AND 
                      temp.TABLE_NAME = '{$source}'
            ) t;
        ";
    }

    /**
     * Generates a drop statement for a temporary table
     *
     * @param string $name
     * @return string
     */
    protected function generateTemporaryTableDrop(string $name)
    {
        return "DROP TEMPORARY TABLE {$name};";
    }

    /**
     * Generates temporary table creation scripts from the arrays received and returns the scripts that were generated,
     * we expect count($sourceTables) and count($destinationTables) to be the same
     *
     * @param array $sourceTables
     * @param array $destinationTables
     * @return array
     */
    protected function generateTemporaryTableCreates(array $sourceTables, array $destinationTables)
    {
        $output = [];
        for ($index = 0; $index < count($sourceTables); $index++) {
            $output [] = $this->generateTemporaryTableCreate($sourceTables[$index], $destinationTables[$index]);
        }
        return $output;
    }

    /**
     * Generates temporary table drops for the tables received and returns the scripts
     *
     * @param array $tables
     * @return array
     */
    protected function generateTemporaryTableDrops(array $tables)
    {
        $output = [];
        foreach ($tables as $table) {
            $output [] = $this->generateTemporaryTableDrop($table);
        }
        return $output;
    }

    /**
     * Gets the unchanged columns
     *
     * @param int $sid
     * @param int $qTimestamp
     * @param int $sTimestamp
     * @return array all rows of the query result. Each array element is an array representing a row.
     * An empty array is returned if the query results in nothing.
     * @throws CException execution failed
     */
    public function getUnchangedColumns($sid, $sTimestamp, $qTimestamp)
    {
        $sourceTables = [
            $this->db->tablePrefix . "survey_" . $sid,
            $this->db->tablePrefix . "survey_" . $sid,
            $this->db->tablePrefix . "survey_" . $sid,
            $this->db->tablePrefix . "old_survey_{$sid}_{$sTimestamp}",
            $this->db->tablePrefix . "old_survey_{$sid}_{$sTimestamp}",
            $this->db->tablePrefix . "old_survey_{$sid}_{$sTimestamp}",
        ];
        $destinationTables = [
            'new_s_c',
            'new_parent1',
            'new_parent2',
            'old_s_c',
            'old_parent1',
            'old_parent2'
        ];
        $this->db->createCommand(implode("\n\n", $this->generateTemporaryTableCreates($sourceTables, $destinationTables)))->execute();
        $command =
        "
            SELECT old_s_c.COLUMN_NAME AS old_c, new_s_c.COLUMN_NAME AS new_c
            FROM " . $this->db->tablePrefix . "old_questions_" . $sid . "_" . $qTimestamp . " old_q
            JOIN " . $this->db->tablePrefix . "questions new_q
            ON old_q.qid = new_q.qid AND old_q.type = new_q.type
            JOIN new_s_c
            ON new_s_c.sid = new_q.sid AND
               new_s_c.gid = new_q.gid AND
               new_s_c.qidsuffix like concat(new_q.qid, '%')
            JOIN old_s_c
            ON old_s_c.sid = old_q.sid AND
               old_s_c.gid = old_q.gid AND
               old_s_c.qidsuffix LIKE CONCAT(old_q.qid, '%') AND
               old_s_c.qidsuffix = new_s_c.qidsuffix
            LEFT JOIN new_parent1
            ON new_s_c.sid = new_parent1.sid AND
               new_s_c.gid = new_parent1.gid AND
               new_s_c.qidsuffix <> new_parent1.qidsuffix AND
               new_parent1.qidsuffix LIKE CONCAT(new_s_c.qidsuffix, '%')
            LEFT JOIN new_parent2
            ON new_s_c.sid = new_parent2.sid AND
               new_s_c.gid = new_parent2.gid AND
               new_s_c.qidsuffix <> new_parent2.qidsuffix AND new_parent1.qidsuffix <> new_parent2.qidsuffix AND
               new_parent2.qidsuffix LIKE CONCAT(new_s_c.qidsuffix, '%')
            LEFT JOIN old_parent1
            ON old_s_c.sid = old_parent1.sid AND
               old_s_c.gid = old_parent1.gid AND
               old_s_c.qidsuffix <> old_parent1.qidsuffix AND
               old_parent1.qidsuffix LIKE CONCAT(old_s_c.qidsuffix, '%')
            LEFT JOIN old_parent2
               ON old_s_c.sid = old_parent2.sid AND
                  old_s_c.gid = old_parent2.gid AND
                  old_s_c.qidsuffix <> old_parent2.qidsuffix AND old_parent1.qidsuffix <> old_parent2.qidsuffix AND
                  old_parent2.qidsuffix LIKE CONCAT(old_s_c.qidsuffix, '%')
            WHERE (new_parent2.sid IS NULL) AND
                  (old_parent2.sid IS NULL) AND
                  (((new_parent1.sid IS NULL) AND (old_parent1.sid IS NULL)) OR
                   (
                    (new_parent1.sid = old_parent1.sid) AND
                    (new_parent1.gid = old_parent1.gid) AND
                    (new_parent1.qidsuffix = old_parent1.qidsuffix)
                   )
                  )
            ;
        "
        ;

        $rawResults = $this->db->createCommand($command)->queryAll();
        $results = ['old_c' => [], 'new_c' => []];
        foreach ($rawResults as $rawResult) {
            $results['old_c'][] = $rawResult['old_c'];
            $results['new_c'][] = $rawResult['new_c'];
        }
        $this->db->createCommand(implode("\n\n", $this->generateTemporaryTableDrops($destinationTables)))->execute();
        return $results;
    }

    /**
     * Finds the newest archive table from each kind
     *
     * @param int $sid
     * @return array all rows of the query result. Each array element is an array representing a row.
     * An empty array is returned if the query results in nothing.
     * @throws CException execution failed
     */
    public function getNewestArchives($sid)
    {
        $sid = intval($sid);
        $command = "
            SELECT n, MAX(TABLE_NAME) AS TABLE_NAME
            FROM information_schema.tables
            JOIN (
                SELECT 'survey' AS n
                UNION
                SELECT 'tokens' AS n
                UNION
                SELECT 'timings' AS n
                UNION
                SELECT 'questions' AS n
            ) t
            ON TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE CONCAT('%', n, '%') AND TABLE_NAME LIKE '%old%' AND TABLE_NAME LIKE '%{$sid}%' AND
            ((n <> 'survey') OR (TABLE_NAME NOT LIKE '%timings%'))
            GROUP BY n;
        ";
        $rawResults = $this->db->createCommand($command)->queryAll();
        $results = [];
        foreach ($rawResults as $rawResult) {
            $results[$rawResult['n']] = $rawResult["TABLE_NAME"];
        }
        return $results;
    }

    /**
     * Recovers archived survey responses
     *
     * @param int $surveyId survey ID
     * @param string $archivedResponseTableName archived response table name to be imported
     * @param bool $preserveIDs if archived response IDs should be preserved
     * @param array $validatedColumns the columns that are validated and can be inserted again
     * @return integer number of rows affected by the execution.
     * @throws Exception execution failed
     */
    public function recoverSurveyResponses(int $surveyId, string $archivedResponseTableName, $preserveIDs, array $validatedColumns = []): int
    {
        if (!is_array($validatedColumns)) {
            $validatedColumns = [];
        }
        $pluginDynamicArchivedResponseModel = PluginDynamic::model($archivedResponseTableName);
        $targetSchema = SurveyDynamic::model($surveyId)->getTableSchema();
        $encryptedAttributes = Response::getEncryptedAttributes($surveyId);
        if (strpos($archivedResponseTableName, App()->db->tablePrefix) === 0) {
            $tbl_name = str_replace('old_survey', 'old_tokens', substr($archivedResponseTableName, strlen(App()->db->tablePrefix)));
        }
        $archivedTableSettings = ArchivedTableSettings::model()->findByAttributes(['tbl_name' => $tbl_name, 'tbl_type' => 'response']);
        $archivedEncryptedAttributes = [];
        if ($archivedTableSettings) {
            $archivedEncryptedAttributes = json_decode($archivedTableSettings->properties, true);
        }
        $archivedResponses = new CDataProviderIterator(new CActiveDataProvider($pluginDynamicArchivedResponseModel), 500);

        $tableName = "{{survey_$surveyId}}";
        $importedResponses = 0;
        $batchData = [];
        foreach ($archivedResponses as $archivedResponse) {
            $dataRow = [];
            // Using plugindynamic model because I dont trust surveydynamic.
            $targetResponse = new PluginDynamic($tableName);
            if ($preserveIDs) {
                $targetResponse->id = $archivedResponse->id;
                $dataRow['id'] = $archivedResponse->id;
            }

            $to = 'new_c';
            $from = 'old_c';
            for ($index = 0; $index < count($validatedColumns[$to]); $index++) {
                $source = $validatedColumns[$from][$index];
                $target = $validatedColumns[$to][$index];
                $targetResponse->{$target} = $archivedResponse[$source];
                if (in_array($source, $archivedEncryptedAttributes, false) && !in_array($target, $encryptedAttributes, false)) {
                    $targetResponse->{$target} = $archivedResponse->decryptSingle($archivedResponse[$source]);
                } elseif (!in_array($source, $archivedEncryptedAttributes, false) && in_array($target, $encryptedAttributes, false)) {
                    $targetResponse->{$target} = $archivedResponse->encryptSingle($archivedResponse[$source]);
                } else {
                    $targetResponse->{$target} = $archivedResponse[$source];
                }
                $dataRow[$target] = $targetResponse->{$target};
            }

            $additionalFields = [
                'token',
                'submitdate',
                'lastpage',
                'startlanguage',
                'seed',
                'startdate',
                'datestamp',
                'version_number'
            ];

            if (isset($targetSchema->columns['startdate']) && empty($targetResponse['startdate'])) {
                $targetResponse->{'startdate'} = date("Y-m-d H:i", (int)mktime(0, 0, 0, 1, 1, 1980));
                $dataRow['startdate'] = $targetResponse->{'startdate'};
            }

            if (isset($targetSchema->columns['datestamp']) && empty($targetResponse['datestamp'])) {
                $targetResponse->{'datestamp'} = date("Y-m-d H:i", (int)mktime(0, 0, 0, 1, 1, 1980));
                $dataRow['datestamp'] = $targetResponse->{'datestamp'};
            }

            foreach ($additionalFields as $additionalField) {
                if (isset($archivedResponse->{$additionalField}) && isset($targetSchema->columns[$additionalField])) {
                    $dataRow[$additionalField] = $archivedResponse->{$additionalField};
                }
            }

            $beforeDataEntryImport = new PluginEvent('beforeDataEntryImport');
            $beforeDataEntryImport->set('iSurveyID', $surveyId);
            $beforeDataEntryImport->set('oModel', $targetResponse);
            App()->getPluginManager()->dispatchEvent($beforeDataEntryImport);

            if ($targetResponse->validate()){
                $batchData[] = $dataRow;
            }
            if (count($batchData) % 500 === 0) {
                if ($preserveIDs) {
                    switchMSSQLIdentityInsert("survey_$surveyId", true);
                }
                $builder = App()->db->getCommandBuilder();
                $command = $builder->createMultipleInsertCommand($tableName, $batchData);
                $importedResponses += $command->execute();
                if ($preserveIDs) {
                    switchMSSQLIdentityInsert("survey_$surveyId", false);
                }
                $batchData = [];
            }

            unset($targetResponse);
        }

        if (count($batchData)) {
            if ($preserveIDs) {
                switchMSSQLIdentityInsert("survey_$surveyId", true);
            }
            $builder = App()->db->getCommandBuilder();
            $command = $builder->createMultipleInsertCommand($tableName, $batchData);
            $importedResponses += $command->execute();
            if ($preserveIDs) {
                switchMSSQLIdentityInsert("survey_$surveyId", false);
            }
        }
        return $importedResponses;
    }

    /**
     * Copying all data from source table to a target table having the same structure
     *
     * @param string $source
     * @param string $destination
     * @return integer number of rows affected by the execution.
     * @throws CDbException execution failed
     */
    public function copyFromOneTableToTheOther($source, $destination)
    {
        return $this->db->createCommand("INSERT INTO " . $this->db->quoteTableName($destination) . " SELECT * FROM " . $this->db->quoteTableName($source))->execute();
    }
}
