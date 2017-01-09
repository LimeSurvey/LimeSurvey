<?php

namespace ls\pluginmanager;

/**
 * Base class for plugins.
 */
abstract class PluginBase implements iPlugin {
    /**
     *
     * @var LimesurveyApi
     */
    protected $api = null;
    
    /**
     *
     * @var PluginEvent
     */
    protected $event = null;
    
    protected $id = null;
    protected $storage = 'DummyStorage';
    
    static protected $description = 'Base plugin object';
    static protected $name = 'PluginBase';
    private $store = null;
    protected $settings = array();
    
    /**
     * This holds the pluginmanager that instantiated the plugin
     * 
     * @var PluginManager
     */
    protected $pluginManager;

    /**
     * Constructor for the plugin
     * @todo Add proper type hint in 3.0
     * @param PluginManager $manager    The plugin manager instantiating the object
     * @param int           $id         The id for storage
     */
    public function __construct(\PluginManager $manager, $id)
    {
        $this->pluginManager = $manager;
        $this->id = $id;
        $this->api = $manager->getAPI();
    }
    
    /**
     * This function retrieves plugin data. Do not cache this data; the plugin storage
     * engine will handling caching. After the first call to this function, subsequent 
     * calls will only consist of a few function calls and array lookups. 
     * 
     * @param string $key
     * @param string $model
     * @param int $id
     * @param mixed $default The default value to use when not was set
     * @return boolean
     */
    protected function get($key = null, $model = null, $id = null, $default = null)
    {
        return $this->getStore()->get($this, $key, $model, $id, $default);
    }
    
    /**
     * Return the description for this plugin
     */
    public static function getDescription()
    {
        return static::$description;
    }
    
    /**
     * Get the current event this plugin is responding to
     * 
     * @return PluginEvent
     */
    public function getEvent()
    {
        return $this->event;
    }
    
    /**
     * Returns the id of the plugin
     * 
     * Used by storage model to find settings specific to this plugin
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Provides meta data on the plugin settings that are available for this plugin.
     * This does not include enable / disable; a disabled plugin is never loaded.
     * 
     */
    public function getPluginSettings($getValues = true)
    {
        
        $settings = $this->settings;
        foreach ($settings as $name => &$setting)
        {
            if ($getValues)
            {
                $setting['current'] = $this->get($name, null, null, isset($setting['default']) ? $setting['default'] : null );
            }
            if ($setting['type'] == 'logo')
            {
                $setting['path'] = $this->publish($setting['path']);
            }
        }
        return $settings;
    }

    public static function getName()
    {
        return static::$name;
    }
    /**
     * Returns the plugin storage and takes care of
     * instantiating it
     * 
     * @return iPluginStorage
     */
    public function getStore()
    {
        if (is_null($this->store)) {
            $this->store = $this->pluginManager->getStore($this->storage);
        }
        
        return $this->store;
    }
    
    /** 
     * Publishes plugin assets.
     */
    public function publish($fileName)
    {
        // Check if filename is relative.
        if (strpos('//', $fileName) === false)
        {
            // This is a limesurvey relative path.
            if (strpos('/', $fileName) === 0)
            {
                $url = \Yii::getPathOfAlias('webroot') . $fileName;
                
            }
            else // This is a plugin relative path.
            {
                $path = \Yii::getPathOfAlias('webroot.plugins.' . get_class($this)) . DIRECTORY_SEPARATOR . $fileName;
                /*
                 * By using the asset manager the assets are moved to a publicly accessible path.
                 * This approach allows a locked down plugin directory that is not publicly accessible.
                 */
                $url = App()->assetManager->publish($path);
            }
        }
        else
        {
            $url = $fileName;
        }
        return $url;
    }
    
    /**
     * 
     * @param string $name Name of the setting.         
     * The type of the setting is either a basic type or choice.
     * The choice type is either a single or a multiple choice setting.
     * @param array $options
     * Contains parameters for the setting. The 'type' key contains the parameter type.
     * The type is one of: string, int, float, choice.
     * Supported keys per type:
     * String: max-length(int), min-length(int), regex(string).
     * Int: max(int), min(int).
     * Float: max(float), min(float).
     * Choice: choices(array containing values as keys and names as values), multiple(bool)
     * Note that the values for choice will be translated.
     */
    protected function registerSetting($name, $options = array('type' => 'string'))
    {
        $this->settings[$name] = $options;
    }
    
    /**
     * 
     * @param type $settings
     */
    public function saveSettings($settings)
    {
        foreach ($settings as $name => $setting)
        {
            $this->set($name, $setting);
        }
    }
    
    
    /**
     * This function stores plugin data.
     * 
     * @param string $key
     * @param mixed $data
     * @param string $model
     * @param int $id
     * @return boolean
     */
    protected function set($key, $data, $model = null, $id = null)
    {
        return $this->getStore()->set($this, $key, $data, $model, $id);
    }
    
    /**
     * Set the event to the plugin, this method is executed by the PluginManager
     * just before dispatching the event.
     * 
     * @param PluginEvent $event
     * @return PluginBase
     */
    public function setEvent(PluginEvent $event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * Here you should handle subscribing to the events your plugin will handle
     */
    //abstract public function registerEvents();
    
    /**
     * This function subscribes the plugin to receive an event.
     * 
     * @param string $event
     * @param string $function
     */
    protected function subscribe($event, $function = null)
    {
        return $this->pluginManager->subscribe($this, $event, $function);
    }
    
    /**
     * This function unsubscribes the plugin from an event.
     * @param string $event
     */        
    protected function unsubscribe($event)
    {
        return $this->pluginManager->unsubscribe($this, $event);
    }

    /**
     * Look for views in plugin views/ folder and render it (no echo)
     *
     * @param string $viewfile Filename of view in views/ folder
     * @param array $data
     * @param boolean $return
     * @param boolean $processOutput
     * @return string;
     */
    public function renderPartial($viewfile, $data, $return = false, $processOutput = false)
    {
        $alias = 'plugin_views_folder' . $this->id;
        \Yii::setPathOfAlias($alias, $this->getDir());
        return \Yii::app()->controller->renderPartial($alias .'.views.' . $viewfile, $data, $return, $processOutput);
    }

    /**
     * To find the plugin locale file, we need late runtime result of __DIR__.
     * Solution copied from http://stackoverflow.com/questions/18100689/php-dir-evaluated-runtime-late-binding
     *
     * @return string
     */
    protected function getDir()
    {
        $reflObj = new \ReflectionObject($this);
        $fileName = $reflObj->getFileName();
        return dirname($fileName);
    }

}
