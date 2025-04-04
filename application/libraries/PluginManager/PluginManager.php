<?php

namespace LimeSurvey\PluginManager;

use Yii;
use Plugin;
use ExtensionConfig;

/**
 * Factory for limesurvey plugin objects.
 * @method mixed dispatchEvent(): void
 */
class PluginManager extends \CApplicationComponent
{
    /**
     * Object containing any API that the plugins can use.
     * @var mixed $api The class name of the API class to load, or
     */
    public $api;

    /**
     * Array mapping guids to question object class names.
     * @var array
     */
    protected $guidToQuestion = [];

    /**
     * @var array
     */
    protected $plugins = [];

    /**
     * @var array
     */
    public $pluginDirs = [
        // User plugins installed through command line.
        'user' => 'webroot.plugins',
        // Core plugins.
        'core' => 'application.core.plugins',
        // Uploaded plugins installed through ZIP file.
        'upload' => 'uploaddir.plugins'
    ];

    /**
     * @var array
     */
    protected $stores = [];

    /**
     * @var array<string, array> Array with string key to tuple value like 'eventName' => array($plugin, $method)
     */
    protected $subscriptions = [];

    /**
     * Created at init.
     * Used to deal with syntax errors etc in plugins during load.
     * @var PluginManagerShutdownFunction
     */
    public $shutdownObject;

    /**
     * Creates the plugin manager.  Loads all active plugins.
     * If $plugin->save() is used in this method, it can lead to an infinite event loop,
     * since beforeSave tries to get the PluginManager, which executes init() again.
     *
     * @return void
     */
    public function init()
    {
        // NB: The shutdown object is disabled by default. Must be enabled
        // before attempting to load plugins (and disabled after).
        $this->shutdownObject = new PluginManagerShutdownFunction();
        register_shutdown_function($this->shutdownObject);

        \Yii::setPathOfAlias('uploaddir', Yii::app()->getConfig('uploaddir'));

        parent::init();
        if (!is_object($this->api)) {
            $class = $this->api;
            $this->api = new $class();
        }
        $this->loadPlugins();
    }
    /**
     * Return a list of installed plugins, but only if the files are still there
     * @deprecated unused in 5.3.8
     * This prevents errors when a plugin was installed but the files were removed
     * from the server.
     *
     * @return array
     */
    public function getInstalledPlugins()
    {
        $pluginModel = Plugin::model();
        $records = $pluginModel->findAll(['order' => 'priority DESC']);

        $plugins = array();

        foreach ($records as $record) {
            // Only add plugins we can find
            if ($this->loadPlugin($record->name, $record->id, $record->active) !== false) {
                $plugins[$record->id] = $record;
            }
        }
        return $plugins;
    }

    /**
     * @param string $destdir
     * @return array [boolean $result, string $errorMessage]
     */
    public function installUploadedPlugin($destdir)
    {
        $configFile = $destdir . '/config.xml';
        $extensionConfig = \ExtensionConfig::loadFromFile($configFile);
        if (empty($extensionConfig)) {
            return [false, gT('Could not parse config.xml file.')];
        } else {
            return $this->installPlugin($extensionConfig, 'upload');
        }
    }

    /**
     * Install a plugin given a plugin configuration and plugin type (core or user).
     * @param string $pluginName Unique plugin class name/folder name.
     * @param string $pluginType 'user' or 'core', depending on location of folder.
     * @return array [boolean $result, string $errorMessage]
     */
    public function installPlugin(\ExtensionConfig $extensionConfig, $pluginType)
    {
        if (!$extensionConfig->validate()) {
            return [false, gT('Extension configuration file is not valid.')];
        }

        if (!$extensionConfig->isCompatible()) {
            return [false, gT('Extension is not compatible with your LimeSurvey version.')];
        }

        $newName = (string) $extensionConfig->xml->metadata->name;
        if (!$this->isWhitelisted($newName)) {
            return [false, gT('The plugin is not in the plugin allowlist.')];
        }

        $otherPlugin = Plugin::model()->findAllByAttributes(['name' => $newName]);
        if (!empty($otherPlugin)) {
            return [false, sprintf(gT('Extension "%s" is already installed.'), $newName)];
        }

        $plugin = new Plugin();
        $plugin->name        = $newName;
        $plugin->version     = (string) $extensionConfig->xml->metadata->version;
        if (!empty($extensionConfig->xml->priority)) {
            $plugin->priority   = (int) $extensionConfig->xml->priority;
        }
        $plugin->plugin_type = $pluginType;
        $plugin->save();
        return [true, null];
    }

    /**
     * Return the status of plugin (true/active or false/desactive)
     *
     * @param string sPluginName Plugin name
     * @return boolean
     */
    public function isPluginActive($sPluginName)
    {
        $pluginModel = Plugin::model();
        $record = $pluginModel->findByAttributes(array('name' => $sPluginName, 'active' => '1'));
        if ($record == false) {
            return false;
        } else {
            return true;
        }
    }

/**
     * Returns the storage instance of type $storageClass.
     * If needed initializes the storage object.
     * @param string $storageClass
     * @return mixed
     */
    public function getStore($storageClass)
    {
        if (isset($this->stores[$storageClass])) {
            return $this->stores[$storageClass];
        }
        $withoutNamespace = class_exists($storageClass, false);
        $withNamespace = class_exists('LimeSurvey\\PluginManager\\' . $storageClass, false);
        if (
            !$withoutNamespace && $withNamespace
        ) {
            $storageClass = 'LimeSurvey\\PluginManager\\' . $storageClass;
        } else if (!($withoutNamespace || $withNamespace)) {
            $relativePath = App()->getConfig('rootdir') . "/application/libraries/PluginManager/Storage/{$storageClass}.php";
            if (file_exists($relativePath)) {
                require_once $relativePath;
                $storageClass = 'LimeSurvey\\PluginManager\\' . $storageClass;
            }
        }
        return $this->stores[$storageClass] = new $storageClass();
    }

    /**
     * This function returns an API object, exposing an API to each plugin.
     * In the current case this is the LimeSurvey API.
     * @return LimesurveyApi
     */
    public function getAPI()
    {
        return $this->api;
    }
    /**
     * Registers a plugin to be notified on some event.
     * @param iPlugin $plugin Reference to the plugin.
     * @param string $event Name of the event.
     * @param string $function Optional function of the plugin to be called.
     */
    public function subscribe(iPlugin $plugin, $event, $function = null)
    {
        if (!isset($this->subscriptions[$event])) {
            $this->subscriptions[$event] = array();
        }
        if (!$function) {
            $function = $event;
        }
        $subscription = array($plugin, $function);
        // Subscribe only if not yet subscribed.
        if (!in_array($subscription, $this->subscriptions[$event])) {
            $this->subscriptions[$event][] = $subscription;
        }
    }

    /**
     * Unsubscribes a plugin from an event.
     * @param iPlugin $plugin Reference to the plugin being unsubscribed.
     * @param string $event Name of the event. Use '*', to unsubscribe all events for the plugin.
     */
    public function unsubscribe(iPlugin $plugin, $event)
    {
        // Unsubscribe recursively.
        if ($event == '*') {
            foreach ($this->subscriptions as $event) {
                $this->unsubscribe($plugin, $event);
            }
        } elseif (isset($this->subscriptions[$event])) {
            foreach ($this->subscriptions[$event] as $index => $subscription) {
                if ($subscription[0] == $plugin) {
                    unset($this->subscriptions[$event][$index]);
                }
            }
        }
    }

    /**
     * This function dispatches an event to all registered plugins.
     * @param PluginEvent $event Object holding all event properties
     * @param string|array $target Optional name of plugin to fire the event on
     *
     * @return PluginEvent
     */
    public function dispatchEvent(PluginEvent $event, $target = array())
    {
        $eventName = $event->getEventName();
        if (is_string($target)) {
            $target = array($target);
        }
        if (isset($this->subscriptions[$eventName])) {
            foreach ($this->subscriptions[$eventName] as $subscription) {
                if (
                    !$event->isStopped()
                    && (empty($target) || in_array(get_class($subscription[0]), $target))
                ) {
                    $subscription[0]->setEvent($event);
                    call_user_func($subscription);
                }
            }
        }

        return $event;
    }

    /**
     * Scans the plugin directory for plugins.
     * This function is not efficient so should only be used in the admin interface
     * that specifically deals with enabling / disabling plugins.
     * @param boolean $includeInstalledPlugins If set, also return plugins even if already installed in database.
     * @return array
     * @todo Factor out
     */
    public function scanPlugins($includeInstalledPlugins = false)
    {
        $this->shutdownObject->enable();

        $result = array();
        foreach ($this->pluginDirs as $pluginType => $pluginDir) {
            $currentDir = Yii::getPathOfAlias($pluginDir);
            if (is_dir($currentDir)) {
                foreach (new \DirectoryIterator($currentDir) as $fileInfo) {
                    if (!$fileInfo->isDot() && $fileInfo->isDir()) {
                        // Check if the base plugin file exists.
                        // Directory name Example must contain file ExamplePlugin.php.
                        $pluginName = $fileInfo->getFilename();
                        $this->shutdownObject->setPluginName($pluginName);
                        $file = Yii::getPathOfAlias($pluginDir . ".$pluginName.{$pluginName}") . ".php";
                        $plugin = Plugin::model()->find('name = :name', [':name' => $pluginName]);
                        if (
                            empty($plugin)
                            || ($includeInstalledPlugins && !$plugin->getLoadError())
                        ) {
                            if (file_exists($file) && $this->isWhitelisted($pluginName)) {
                                try {
                                    $result[$pluginName] = $this->getPluginInfo($pluginName, $pluginDir);
                                    // getPluginInfo returns false instead of an array when config is not found.
                                    // So we build an "empty" array
                                    if (!$result[$pluginName]) {
                                        $result[$pluginName] = array(
                                            'extensionConfig' => null,
                                            'pluginType' => $pluginType,
                                            'load_error' => 0,
                                        );
                                    }
                                } catch (\Throwable $ex) {
                                    // Load error.
                                    $error = [
                                        'message' => $ex->getMessage(),
                                        'file'  => $ex->getFile()
                                    ];
                                    $saveResult = Plugin::handlePluginLoadError($plugin, $pluginName, $error);
                                    if (!$saveResult) {
                                        // If handlePluginLoadError return 0 because debug is set
                                        if (App()->getConfig('debug') >= 2) {
                                            throw $ex;
                                        }
                                        // If handlePluginLoadError fail without debug : have a DB related issue
                                        $this->shutdownObject->disable();
                                        throw new \Exception(
                                            'Internal error: Could not save load error for plugin ' . $pluginName
                                        );
                                    }
                                }
                            }
                        } elseif ($plugin->getLoadError()) {
                            // List faulty plugins in scan files view.
                            $result[$pluginName] = [
                                'pluginName' => $pluginName,
                                'load_error' => 1,
                                'isCompatible' => false,
                                'pluginType' => $plugin->plugin_type,
                            ];
                        } else {
                        }
                    }
                }
            }
        }

        $this->shutdownObject->disable();

        return $result;
    }

    /**
     * Gets the description of a plugin. The description is accessed via a
     * static function inside the plugin file.
     *
     * @todo Read config.xml instead.
     * @param string $pluginClass The classname of the plugin
     * @return array|null
     */
    public function getPluginInfo($pluginClass, $pluginDir = null)
    {
        $result       = [];
        $class        = "{$pluginClass}";
        $extensionConfig = null;
        $pluginType   = null;

        if (!class_exists($class, false)) {
            $found = false;

            foreach ($this->pluginDirs as $type => $pluginDir) {
                $file = Yii::getPathOfAlias($pluginDir . ".$pluginClass.{$pluginClass}") . ".php";
                if (file_exists($file)) {
                    Yii::import($pluginDir . ".$pluginClass.*");

                    $configFile = Yii::getPathOfAlias($pluginDir)
                        . DIRECTORY_SEPARATOR . $pluginClass
                        . DIRECTORY_SEPARATOR . 'config.xml';
                    $extensionConfig = \ExtensionConfig::loadFromFile($configFile);
                    if ($extensionConfig) {
                        $pluginType = $type;
                        $found = true;
                    }
                    break;
                }
            }

            if (!$found) {
                return false;
            }
        }

        if (!class_exists($class)) {
            return null;
        } else {
            $result['description']  = $this->getPluginDescription($class, $extensionConfig);
            $result['pluginName']   = $this->getPluginName($class, $extensionConfig);
            $result['pluginClass']  = $class;
            $result['extensionConfig'] = $extensionConfig;
            $result['isCompatible'] = $extensionConfig == null ? false : $extensionConfig->isCompatible();
            $result['load_error']   = 0;
            $result['pluginType']   = $pluginType;
            return $result;
        }
    }

    /**
     * @param ExtensionConfig $config
     * @param string $pluginType User, core or upload
     */
    public function getPluginFolder(\ExtensionConfig $config, $pluginType)
    {
        $alias = $this->pluginDirs[$pluginType];
        if (empty($alias)) {
            return null;
        }
        $folder = Yii::getPathOfAlias($alias) . '/' . $config->getName();
        return $folder;
    }

    /**
     * Returns the instantiated plugin
     *
     * @param string $pluginName
     * @param int $id Identifier used for identifying a specific plugin instance.
     * @param boolean $init launch init function (if exist)
     * If ommitted will return the first instantiated plugin with the given name.
     * @return iPlugin|null The plugin or null when missing
     */
    public function loadPlugin($pluginName, $id = null, $init = true)
    {
        $return = null;
        $this->shutdownObject->enable();
        $this->shutdownObject->setPluginName($pluginName);
        try {
            // If the id is not set we search for the plugin.
            if (!isset($id)) {
                foreach ($this->plugins as $plugin) {
                    if (!is_null($plugin) && get_class($plugin) == $pluginName) {
                        $return = $plugin;
                    }
                }
            } else {
                if (!isset($this->plugins[$id]) || get_class($this->plugins[$id]) !== $pluginName) {
                    if ($this->isWhitelisted($pluginName) && $this->getPluginInfo($pluginName) !== false) {
                        if (class_exists($pluginName)) {
                            $this->plugins[$id] = new $pluginName($this, $id);
                            if ($init && method_exists($this->plugins[$id], 'init')) {
                                $this->plugins[$id]->init();
                            }
                        } else {
                            $this->plugins[$id] = null;
                        }
                    } else {
                        $this->plugins[$id] = null;
                    }
                }
                $return = $this->plugins[$id];
            }
        } catch (\Throwable $ex) {
            // Load error.
            $error = [
                'message' => $ex->getMessage(),
                'file'  => $ex->getFile()
            ];
            $plugin = Plugin::model()->find('name = :name', [':name' => $pluginName]);
            $saveResult = Plugin::handlePluginLoadError($plugin, $pluginName, $error);
            if (!$saveResult) {
                // If handlePluginLoadError return 0 because debug is set
                if (App()->getConfig('debug') >= 2) {
                    throw $ex;
                }
                // If handlePluginLoadError fail without debug : have a DB related issue
                $this->shutdownObject->disable();
                throw new \Exception(
                    'Internal error: Could not save load error for plugin ' . $pluginName
                );
            }
        }
        $this->shutdownObject->disable();
        return $return;
    }

    /**
     * Handles loading all active plugins
     *
     * Possible improvement would be to load them for a specific context.
     * For instance 'survey' for runtime or 'admin' for backend. This needs
     * some thinking before implementing.
     */
    public function loadPlugins()
    {
        // If DB version is less than 165 : plugins table don't exist. 175 update it (boolean to integer for active).
        $dbVersion = \SettingGlobal::model()->find("stg_name=:name", array(':name' => 'DBVersion')); // Need table SettingGlobal, but settings from DB is set only in controller, not in App, see #11294
        // @todo This previous line seems to be an unnecessary query on every page load, better would be to make the settings available to console command properly, see #11291
        if ($dbVersion && $dbVersion->stg_value >= 165) {
            $pluginModel = Plugin::model();
            if ($dbVersion->stg_value >= 411) {
                /* Before DB 411 version, unable to set order, must check to load before upgrading */
                $records = $pluginModel->findAllByAttributes(array('active' => 1), ['order' => 'priority DESC']);
            } else {
                $records = $pluginModel->findAllByAttributes(array('active' => 1));
            }

            foreach ($records as $record) {
                if (
                    !$record->getLoadError()
                    // NB: Authdb is hardcoded since updating sometimes causes error.
                    // @see https://bugs.limesurvey.org/view.php?id=15908
                    || $record->name == 'Authdb'
                ) {
                    $this->loadPlugin($record->name, $record->id);
                }
            }
        } else {
            // Log it?
        }
        $this->dispatchEvent(new PluginEvent('afterPluginLoad', $this)); // Alow plugins to do stuff after all plugins are loaded
    }

    /**
     * Load ALL plugins, active and non-active
     * @return void
     */
    public function loadAllPlugins()
    {
        $records = Plugin::model()->findAll();
        foreach ($records as $record) {
            if (!$record->getLoadError()) {
                $this->loadPlugin($record->name, $record->id, $record->active);
            }
        }
    }

    /**
     * Get a list of question objects and load some information about them.
     * This registers the question object classes with Yii.
     */
    public function loadQuestionObjects($forceReload = false)
    {
        if (empty($this->guidToQuestion) || $forceReload) {
            $event = new PluginEvent('listQuestionPlugins');
            $this->dispatchEvent($event);


            foreach ($event->get('questionplugins', array()) as $pluginClass => $paths) {
                foreach ($paths as $path) {
                    Yii::import("webroot.plugins.$pluginClass.$path");
                    $parts = explode('.', (string) $path);

                    // Get the class name.
                    $className = array_pop($parts);

                    // Get the GUID for the question object.
                    $guid = forward_static_call(array($className, 'getGUID'));

                    // Save the GUID-class mapping.
                    $this->guidToQuestion[$guid] = array(
                        'class' => $className,
                        'guid' => $guid,
                        'plugin' => $pluginClass,
                        'name' => $className::$info['name']
                    );
                }
            }
        }

        return $this->guidToQuestion;
    }

    /**
     * Read all plugin config files and updates information
     * in database if plugin version differs.
     * @return void
     */
    public function readConfigFiles()
    {
        $this->loadAllPlugins();
        foreach ($this->plugins as $plugin) {
            if (is_object($plugin)) {
                $plugin->readConfigFile();
            } else {
                // Do nothing, plugin is deleted next time plugin manager is visited and loadPlugin validate if class exist
            }
        }
        $this->plugins = array();
        $this->subscriptions = array();
        $this->loadPlugins();
    }

    /**
     * Get plugin description.
     * First look in config.xml, then in plugin class.
     * @param string $class
     * @param ExtensionConfig $extensionConfig
     * @return string
     * @todo Localization.
     */
    protected function getPluginDescription(string $class, \ExtensionConfig $extensionConfig = null)
    {
        $desc = null;

        if ($extensionConfig) {
            $desc = $extensionConfig->getDescription();
        }

        if (empty($desc)) {
            $desc = call_user_func(array($class, 'getDescription'));
        }

        if (empty($desc)) {
            $desc = '-';
        }

        return $desc;
    }

    /**
     * Get plugin name.
     * First look in config.xml, then in plugin class.
     * @param string $class
     * @param ExtensionConfig $extensionConfig
     * @return string
     * @todo Localization.
     */
    protected function getPluginName(string $class, \ExtensionConfig $extensionConfig = null)
    {
        $name = null;

        if ($extensionConfig) {
            $name = $extensionConfig->getName();
        }

        if (empty($name)) {
            $name = call_user_func(array($class, 'getName'));
        }

        if (empty($name)) {
            $name = '-';
        }

        return $name;
    }

    /**
     * Returns true if the plugin name is allowlisted or the allowlist is disabled.
     * @param string $pluginName
     * @return boolean
     */
    public function isWhitelisted($pluginName)
    {
        if (App()->getConfig('usePluginWhitelist')) {
            // Get the user plugins allowlist
            $whiteList = App()->getConfig('pluginWhitelist');
            // Get the list of allowed core plugins
            $coreList = $this->getAllowedCorePluginList();
            $allowedPlugins = array_merge($coreList, $whiteList);
            return array_search($pluginName, $allowedPlugins) !== false;
        }
        return true;
    }

    /**
     * Return the core plugin list
     * No way to update by php or DB
     * @return string[]
     */
    private static function getCorePluginList()
    {
        return [
            'AuditLog',
            'Authdb',
            'AuthLDAP',
            'Authwebserver',
            'ComfortUpdateChecker',
            'customToken',
            'ExportR',
            'ExportSPSSsav',
            'ExportSTATAxml',
            'expressionFixedDbVar',
            'expressionQuestionForAll',
            'expressionQuestionHelp',
            'mailSenderToFrom',
            'oldUrlCompat',
            'PasswordRequirement',
            'statFunctions',
            'TwoFactorAdminLogin',
            'UpdateCheck',
            'AzureOAuthSMTP',
            'GoogleOAuthSMTP',
        ];
    }

    /**
     * Return the list of core plugins allowed to be loaded.
     * That is, all core plugins not in the blocklist.
     * @return string[]
     */
    private function getAllowedCorePluginList()
    {
        $corePlugins = self::getCorePluginList();
        $blackList = Yii::app()->getConfig('corePluginBlacklist');
        $allowedCorePlugins = array_diff($corePlugins, $blackList);
        return $allowedCorePlugins;
    }
}
