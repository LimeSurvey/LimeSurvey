<?php
namespace ls\pluginmanager;
use \Yii;
use Plugin;
    /**
     * Factory for limesurvey plugin objects.
     */
    class PluginManager extends \CApplicationComponent{
        protected $_apis = [];
        public $apiMap;
        /**
         * Array mapping guids to question object class names.
         * @var type 
         */
        protected $guidToQuestion = array();
        
        protected $plugins = [];
        
        public $pluginDirs = [];
        public $oldPluginDirs = [];
        /**
         *
         * @var \Composer\Autoload\ClassLoader
         */
        public $loader;
        
        protected $stores = array();

        protected $subscriptions = array();
        
        /**
         * Creates the plugin manager.
         * 
         * 
         * a reference to an already constructed reference.
         */
        public function init() {
            parent::init();
            PluginConfig::$pluginManager = $this;
            $this->registerNamespaces();
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
         * Returns the storage instance of type $storageClass.
         * If needed initializes the storage object.
         * @param string $storageClass
         */
        public function getStore($storageClass)
        {
            if (!isset($this->stores[$storageClass]))
            {
                if (!class_exists($storageClass)) {
                    $storageClass = "\\ls\\pluginmanager\\$storageClass";
                }
                $this->stores[$storageClass] = new $storageClass();
            }
            return $this->stores[$storageClass];
        }

        
        /**
         * This function returns an API object, exposing an API to each plugin.
         * In the current case this is the LimeSurvey API.
         */
        public function getApi($version = "1.0")
        {
            if (!isset($this->_apis[$version])) {
                if (!isset($this->apiMap[$version])) {
                    throw new \Exception("Unknown API version: " . $version);
                }
                $class = $this->apiMap[$version];
                $this->_apis[$version] = new $class;
                
                
            }
            return $this->_apis[$version];
        }
        /**
         * Registers a callback to be called on some event.
         * @param string $event Name of the event.
         * @param callable $$callback.
         */
        public function subscribe($event, callable $callback)
        {
            $this->subscriptions[$event][$this->hashCallable($callback)] = $callback;
        }
        
        /**
         * Generates a hash for any callable.
         * Special handling for plugins; their id has a prefix.
         * @param \ls\pluginmanager\callable $callback
         * @return type
         */
        protected function hashCallable(callable $callback, $ignoreFunction = false) {
            if ($callback instanceof \Closure) {
                return spl_object_hash($callback);
            } elseif (is_array($callback) && is_object($callback[0])) {
                return md5(get_class($callback[0]) . spl_object_hash($callback[0])) . ($ignoreFunction ? '' : $callback[1]);
            } elseif (is_string($callback)) {
                return md5($callback);
            }
        }

        /**
         * Unsubscribes a plugin from an event.
         * @param iPlugin $plugin Reference to the plugin being unsubscribed.
         * @param string $event Name of the event. Use '*', to unsubscribe all events for the plugin.
         * @param string $function Optional function of the plugin that was registered.
         */
        public function unsubscribe($event, callable $callback)
        {
            
            if ($event == '*' && is_array($callback) && is_object($callback[0])) {
                // Get object hash.
                $hash = $this->hashCallable($callback, true);
                foreach ($this->subscriptions as $event => $eventSubscriptions) {
                    foreach ($eventSubscriptions as $index => $subscription) {
                        die($hash);
                    }
                }
            } else {
                unset($this->subscriptions[$event][$this->hashCallable($callback)]);
            }
        }


        /**
         * This function dispatches an event to all registered plugins.
         * @param PluginEvent $event Object holding all event properties
         * @return PluginEvent
         */
        public function dispatchEvent(PluginEvent $event)
        {
            $eventName = $event->name;
            if (isset($this->subscriptions[$eventName])) {
                foreach($this->subscriptions[$eventName] as $subscription) {
                    if (!$event->isStopped()) {
                        call_user_func($subscription, $event);
                    } else {
                        break;
                    }
                }
            }
            
            return $event;
        }

        /**
         * Scans the plugin directories for plugins.
         */
        public function scanPlugins()
        {
            $plugins = [];
            foreach($this->pluginDirs as $pluginDir) {
                $plugins = array_merge($plugins, \ls\pluginmanager\PluginConfig::registerAll($pluginDir));
            }
            return $plugins;
        }

        /**
         * Gets the description of a plugin. 
         * The description is accessed via a function inside the plugin class.
         *
         * @param string $pluginClass The classname of the plugin
         */
        public function getPluginInfo($pluginClass, $pluginDir = null)
        {
            die('deprecated getplugininfo');
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
                    $file = Yii::getPathOfAlias($pluginDir . ".$pluginClass.{$pluginClass}") . ".php";
                    if (file_exists($file)) {
                        Yii::import($pluginDir . ".$pluginClass.*");
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    return false;
                }
            }
            $plugin = new $class($this, null, false);
            $result['description'] = $plugin->getDescription();
            $result['pluginName'] = $plugin->getName();
            $result['pluginClass'] = $class;            
            return $result;
        }
        
        /**
         * Returns the instantiated plugin
         *
         * @param PluginConfig $pluginConfig
         * @return PluginBase The plugin or null when missing
         */
        public function loadPlugin(PluginConfig $pluginConfig)
        {
            if (!isset($this->plugins[$pluginConfig->id])) {
                if ($pluginConfig->type == 'simple') {
                    $this->plugins[$pluginConfig->id] = $this->loadSimplePlugin($pluginConfig);
                } else {
                    throw new \Exception("Only simple is supported for now.");
                }                   
            }
            return $this->getPlugin($pluginConfig->id);
        }
        
        public function getPlugin($id) {
            if (!isset($this->plugins[$id])) {
                throw new \Exception("Plugin $id not found.");
            }
            return $this->plugins[$id];
        }
        
        /**
         * 
         * @param \ls\pluginmanager\PluginConfig $pluginConfig
         * @return 
         */
        public function loadSimplePlugin(PluginConfig $pluginConfig) {
            return $pluginConfig->createPlugin($this);
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
            $result = array_map([$this, 'loadPlugin'], PluginConfig::findAll());
            $this->dispatchEvent(new PluginEvent('afterPluginLoad'));    // Alow plugins to do stuff after all plugins are loaded
            return $result;
        }
        
        public function registerNamespaces() 
        {
            foreach (PluginConfig::findAll() as $pluginConfig) {
                $pluginConfig->registerNamespace($this->loader);
            }   
        }
        
        public function getAuthenticators($activeOnly = false) {
            $result = array_filter($this->loadPlugins(), function ($plugin) {
                return $plugin instanceOf AuthPluginBase;
            });
            if ($activeOnly) {
                $authPlugins = \SettingGlobal::get('authenticationPlugins');
                $result = array_intersect_key($result, array_flip($authPlugins));
            }
            return $result;
        }
        public function getAuthorizers() {
            return array_filter($this->loadPlugins(), function ($plugin) {
                return $plugin instanceOf \IAuthManager;
            });
        }
        
    }
?>
