<?php
namespace ls\pluginmanager;
use \Yii;
use Plugin;
    /**
     * Factory for limesurvey plugin objects.
     * @property-read iAuthenticationPlugin[] $authenticators
     * @property-read iAuthorizationPlugin $authorizer
     */
    class PluginManager extends \CApplicationComponent{
        public $pluginFile;
        public $enabledPluginDir;
        protected $_apis = [];
        public $apiMap;

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
            PluginConfig::$pluginManager = $this;
            $this->loadPlugins();
            if (count($this->getAuthenticators(true)) == 0) {

                // Enable auth db.
                $result = $this->enablePlugin('ls_core_plugins_AuthDb');
                if (!$result) {
                    throw new \Exception("No authentication plugins available.");
                }
            };
            if ($this->getAuthorizer() == null && $reload = true && null === $this->enablePlugin('ls_core_plugins_PermissionDb')) {
                throw new \Exception("No authorization plugin available.");
            };
        }

        /**
         * @param $id
         * @return bool True if the file no longer exists.
         */
        public function disablePlugin($id)
        {
            return !empty($id) && (!file_exists("{$this->enabledPluginDir}/$id") || unlink("{$this->enabledPluginDir}/$id"));
        }

        /**
         * @param $id
         * @return bool True when the file was created.
         */
        public function enablePlugin($id)
        {
            return !empty($id) && touch("{$this->enabledPluginDir}/$id");
        }

        /**
         * @param $id
         * @return bool
         */
        public function isActive($id) {
            return !empty($id) && file_exists("{$this->enabledPluginDir}/$id");
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
         * @param PluginInterface $plugin Reference to the plugin being unsubscribed.
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
         * @return PluginConfig[]
         */
        public function scanPlugins()
        {
            Yii::log('scanplugins');
            $plugins = [];
            foreach($this->pluginDirs as $pluginDir) {
                $plugins = array_merge($plugins, \ls\pluginmanager\PluginConfig::readAll($pluginDir));
            }
            // Write to file.
            $file = new \ls\components\PhpConfigFile($this->pluginFile);
            $file->setConfig(array_map(function(PluginConfig $config) {
                return $config->attributes;
            }, $plugins), false);
            $file->save();
            return $plugins;
        }

       
        
        /**
         * Returns the instantiated plugin
         *
         * @param PluginConfig $pluginConfig
         * @return PluginBase The plugin or null when missing
         */
        public function loadPlugin(PluginConfig $pluginConfig)
        {
            Yii::log(__CLASS__, 'Loading plugin: ' . $pluginConfig->id);
            if ($pluginConfig->validate() && $this->isActive($pluginConfig->id)) {
                $pluginConfig->registerNamespace($this->loader);
                if (!isset($this->plugins[$pluginConfig->id])) {
                    if ($pluginConfig->type == 'simple') {
                        $this->plugins[$pluginConfig->id] = $this->loadSimplePlugin($pluginConfig);
                    } else {
                        $this->plugins[$pluginConfig->id] = $this->loadModulePlugin($pluginConfig);
                    }                   
                }
                return $this->getPlugin($pluginConfig->id);
            } else {
                Yii::log(__CLASS__, 'Pluginconfig not valid' . print_r($pluginConfig->errors, true));
            }
        }
        
        public function getPlugin($id) {
            return isset($this->plugins[$id]) ? $this->plugins[$id] : null;
        }

        /**
         * @return PluginInterface[]
         */
        public function getPlugins() {
            return $this->plugins;
        }
        /**
         * 
         * @param \ls\pluginmanager\PluginConfig $pluginConfig
         * @return 
         */
        public function loadSimplePlugin(PluginConfig $pluginConfig) {
            Yii::log(__CLASS__, 'Loading simple plugin: ' . $pluginConfig->id);
            return $pluginConfig->createPlugin($this);
        }
        
        /**
         * 
         * @param \ls\pluginmanager\PluginConfig $pluginConfig
         * @return 
         */
        public function loadModulePlugin(PluginConfig $pluginConfig) {
            $id = $pluginConfig->getId();
            $shortId = strtolower(substr($id, strrpos($id, '_') + 1));
            /* @var PluginModule $result */
            App()->setModules([
                $shortId => [
                    'class' => $pluginConfig->class,
                    'pluginConfig' => $pluginConfig
                ]
            ]);
            $module = App()->getModule($shortId);
            
//            $rules = [
//                "$shortId/" => $pluginConfig->getId(),
//                "$shortId/<controller>/<action>" => "{$id}/<controller>/<action>",
//            ];
//            App()->urlManager->addRules($rules);
                
            return $module;
        }

        /**
         * Handles loading all active plugins
         *
         * Possible improvement would be to load them for a specific context.
         * For instance 'survey' for runtime or 'admin' for backend. This needs
         * some thinking before implementing.
         */
        protected function loadPlugins()
        {
            if (!file_exists($this->pluginFile)) {
                $this->scanPlugins();
            }
            $config = include($this->pluginFile);
            foreach (PluginConfig::loadMultiple($config) as $pluginConfig) {
                $result[$pluginConfig->id] = $this->loadPlugin($pluginConfig);
            }
            
            $this->dispatchEvent(new PluginEvent('afterPluginLoad'));    // Alow plugins to do stuff after all plugins are loaded
            return empty($result);
        }

        /**
         * @param bool $activeOnly
         * @return iAuthenticationPlugin[]
         */
        public function getAuthenticators($activeOnly = false) {
            $result = array_filter($this->plugins, function ($plugin) {
                return $plugin instanceOf iAuthenticationPlugin;
            });
            if ($activeOnly) {
                // If no active plugins are in the configuration we give enable AuthDb.
                $authPlugins = \ls\models\SettingGlobal::get('authenticationPlugins', ['ls_core_plugins_AuthDb']);
                $result = array_intersect_key($result, array_flip($authPlugins));
            }
            return $result;
        }

        /**
         * @return iAuthorizationPlugin
         */
        public function getAuthorizer() {
            $authorizers = $this->getAuthorizers(true);
            // If not set use PermissionDb.
            $authPlugin = \ls\models\SettingGlobal::get('authorizationPlugin', 'ls_core_plugins_PermissionDb');
            if (isset($authorizers[$authPlugin])) {
                return $authorizers[$authPlugin];
            }
        }
        public function getAuthorizers($activeOnly = false) {
            $result = array_filter($this->plugins, function ($plugin) {
                return $plugin instanceOf iAuthorizationPlugin;
            });
            return $result;
        }

        /**
         * Iterates over all active authenticators looking for a user with $id.
         * Since the $id should be globally unique will return only the first result.
         * @param $id
         */
        public function getUser($id)
        {
            foreach($this->getAuthenticators(true) as $authenticator) {
                if (null !== $result = $authenticator->getUser($id)) {
                    return $result;
                }
            }
    }


    }

