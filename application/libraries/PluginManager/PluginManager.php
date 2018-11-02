<?php
namespace LimeSurvey\PluginManager;
use \Yii;
use Plugin;

/**
 * Factory for limesurvey plugin objects.
 * @method mixed dispatchEvent
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
     * @var type
     */
    protected $guidToQuestion = array();

    /**
     * @var ?
     */
    protected $plugins = array();

    protected $pluginDirs = array(
        'webroot.plugins', // User plugins
        'application.core.plugins' // Core plugins
    );

    protected $stores = array();

    /**
     * @var array<string, array> Array with string key to tuple value like 'eventName' => array($plugin, $method)
     */
    protected $subscriptions = array();

    /**
     * Creates the plugin manager.
     *
     *
     * a reference to an already constructed reference.
     */
    public function init()
    {
        parent::init();
        if (!is_object($this->api)) {
            $class = $this->api;
            $this->api = new $class;
        }
        $this->loadPlugins();
    }
    /**
     * Return a list of installed plugins, but only if the files are still there
     *
     * This prevents errors when a plugin was installed but the files were removed
     * from the server.
     *
     * @return array
     */
    public function getInstalledPlugins()
    {
        $pluginModel = Plugin::model();
        $records = $pluginModel->findAll();

        $plugins = array();

        foreach ($records as $record) {
            // Only add plugins we can find
            if ($this->loadPlugin($record->name) !== false) {
                $plugins[$record->id] = $record;
            }
        }
        return $plugins;
    }

    /**
     * Return the status of plugin (true/active or false/desactive)
     *
     * @param sPluginName Plugin name
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
     */
    public function getStore($storageClass)
    {
        if (!class_exists($storageClass)
                && class_exists('LimeSurvey\\PluginManager\\'.$storageClass)) {
            $storageClass = 'LimeSurvey\\PluginManager\\'.$storageClass;
        }
        if (!isset($this->stores[$storageClass])) {
            $this->stores[$storageClass] = new $storageClass();
        }
        return $this->stores[$storageClass];
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
                if (!$event->isStopped()
                 && (empty($target) || in_array(get_class($subscription[0]), $target))) {
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
     */
    public function scanPlugins($forceReload = false)
    {
        $result = array();
        foreach ($this->pluginDirs as $pluginDir) {
            $currentDir = Yii::getPathOfAlias($pluginDir);
            if (is_dir($currentDir)) {
                foreach (new \DirectoryIterator($currentDir) as $fileInfo) {
                    if (!$fileInfo->isDot() && $fileInfo->isDir()) {
                        // Check if the base plugin file exists.
                        // Directory name Example most contain file ExamplePlugin.php.
                        $pluginName = $fileInfo->getFilename();
                        $file = Yii::getPathOfAlias($pluginDir.".$pluginName.{$pluginName}").".php";
                        if (file_exists($file) && $this->_checkWhitelist($pluginName)) {
                            $result[$pluginName] = $this->getPluginInfo($pluginName, $pluginDir);
                        }
                    }

                }
            }
        }

        return $result;
    }

    /**
     * Gets the description of a plugin. The description is accessed via a
     * static function inside the plugin file.
     *
     * @param string $pluginClass The classname of the plugin
     * @return array|null
     */
    public function getPluginInfo($pluginClass, $pluginDir = null)
    {
        $result = array();
        $class = "{$pluginClass}";

        if (!class_exists($class, false)) {
            $found = false;
            if (!is_null($pluginDir)) {
                $dirs = array($pluginDir);
            } else {
                $dirs = $this->pluginDirs;
            }

            foreach ($this->pluginDirs as $pluginDir) {
                $file = Yii::getPathOfAlias($pluginDir.".$pluginClass.{$pluginClass}").".php";
                if (file_exists($file)) {
                    Yii::import($pluginDir.".$pluginClass.*");
                    $found = true;
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
            $result['description'] = call_user_func(array($class, 'getDescription'));
            $result['pluginName'] = call_user_func(array($class, 'getName'));
            $result['pluginClass'] = $class;
            return $result;
        }
    }

    /**
     * Returns the instantiated plugin
     *
     * @param string $pluginName
     * @param int $id Identifier used for identifying a specific plugin instance.
     * If ommitted will return the first instantiated plugin with the given name.
     * @return iPlugin|null The plugin or null when missing
     */
    public function loadPlugin($pluginName, $id = null)
    {
        // If the id is not set we search for the plugin.
        if (!isset($id)) {
            foreach ($this->plugins as $plugin) {
                if (!is_null($plugin) && get_class($plugin) == $pluginName) {
                    return $plugin;
                }
            }
        } else {
            if ((!isset($this->plugins[$id]) || get_class($this->plugins[$id]) !== $pluginName)) {
                if ($this->getPluginInfo($pluginName) !== false) {
                    if (class_exists($pluginName)) {
                        $this->plugins[$id] = new $pluginName($this, $id);

                        if (method_exists($this->plugins[$id], 'init')) {
                            $this->plugins[$id]->init();
                        }
                    } else {
                        $this->plugins[$id] = null;
                    }
                } else {
                    $this->plugins[$id] = null;
                }
            }
            return $this->plugins[$id];
        }
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
        $dbVersion = \SettingGlobal::model()->find("stg_name=:name", array(':name'=>'DBVersion')); // Need table SettingGlobal, but settings from DB is set only in controller, not in App, see #11294
        if ($dbVersion && $dbVersion->stg_value >= 165) {
            $pluginModel = Plugin::model();
            $records = $pluginModel->findAllByAttributes(array('active'=>1));

            foreach ($records as $record) {
                $this->loadPlugin($record->name, $record->id);
            }
        } else {
            // Log it ? tracevar ?
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
            $this->loadPlugin($record->name, $record->id);
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
                    $parts = explode('.', $path);

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
     * Construct a question object from a GUID.
     * @param string $guid
     * @param int $questionId,
     * @param int $responseId
     * @return iQuestion
     */
    public function constructQuestionFromGUID($guid, $questionId = null, $responseId = null)
    {
        $this->loadQuestionObjects();
        if (isset($this->guidToQuestion[$guid])) {
            $questionClass = $this->guidToQuestion[$guid]['class'];
            $questionObject = new $questionClass($this->loadPlugin($this->guidToQuestion[$guid]['plugin']), $this->api, $questionId, $responseId);
            return $questionObject;
        }
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


    private function _checkWhitelist($pluginName){
        if(App()->getConfig('usePluginWhitelist')) {

            $whiteList = App()->getConfig('pluginWhitelist');
            $coreList = App()->getConfig('pluginCoreList');
            $allowedPlugins =  array_merge($coreList, $whiteList);
            return array_search($pluginName, $allowedPlugins) !== false;
        }
        return true;
    }

}
