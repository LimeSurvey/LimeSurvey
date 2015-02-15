<?php
namespace ls\pluginmanager;
/**
 * Base class for plugins.
 */
abstract class PluginBase extends \CComponent implements iPlugin {
    use PluginTrait;
    /**
     *
     * @var LimesurveyApi
     */
    public $api = null;

    protected $storage = 'DummyStorage';

    private $store = null;
    

    public $name;
    /**
     * This holds the pluginmanager that instantiated the plugin
     * 
     * @var PluginManager
     */
    protected $pluginManager;
    
    public $id;

    /**
     * Constructor for the plugin
     * 
     * @param PluginManager $manager    The plugin manager instantiating the object
     * @param int           $id         The id for storage
     */
    public function __construct(PluginManager $manager, $init = true)
    {
        $this->pluginManager = $manager;
        $this->api = $manager->getAPI();
        if ($init) {
            $this->init();
        }
    }

    /**
     * Init function, called after constructor.
     */
    public function init() {

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
    public function getDescription()
    {
        return static::$description;
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
                $url = Yii::getPathOfAlias('webroot') . $fileName;

            }
            else // This is a plugin relative path.
            {
                $path = Yii::getPathOfAlias('webroot.plugins.' . get_class($this)) . DIRECTORY_SEPARATOR . $fileName;
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
     * Here you should handle subscribing to the events your plugin will handle
     */
    //abstract public function registerEvents();

    /**
     * This function subscribes the plugin to receive an event.
     * 
     * @param string $event
     */
    protected function subscribe($event, $function = null)
    {
        return $this->pluginManager->subscribe($event, [$this, isset($function) ? $function : $event]);
    }

    /**
     * This function unsubscribes the plugin from an event.
     * @param string $event
     */        
    protected function unsubscribe($event)
    {
        return $this->pluginManager->unsubscribe($this, $event);
    }

}