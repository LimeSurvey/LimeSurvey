<?php
namespace LimeSurvey\PluginManager;
use Yii;
use Hash;
Yii::import('application.helpers.Hash');

class PluginEvent
{
    /**
     * The name of this event
     * 
     * @var string 
     */
    protected $_event = '';
    
    /**
     * This array holds the content blocks that plugins generate, idexed by plugin name
     * 
     * @var array of PluginEventContent
     */
    protected $_content = array();
    
    /**
     * The class who fired the event, or null when not set
     * 
     * @var object 
     */
    protected $_sender = null;
    
    /**
     * When true it prevents delegating the event to other plugins.
     * 
     * @var boolean 
     */
    protected $_stop = false;
    
    /**
     * Internal storage for event data. Can be used to communicate between sender
     * and plugin or between different plugins handling the event.
     * 
     * @var array 
     */
    protected $_parameters = array();
    
    /**
     * Constructor for the PluginEvent
     * 
     * @param string $event    Name of the event fired 
     * @param object $sender   The object sending the event
     * @return \PluginEvent
     */
    public function __construct($event, $sender = null)
    {
        if (!is_null($sender) && is_object($sender)) {
            $this->_sender = $sender;
        }
        
        $this->_event = $event;
        
        return $this;
    }

    /**
     * Get a value for the given key. 
     * 
     * When the value is not set, it will return the given default or null when
     * no default was given.
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        if (!Hash::check($this->_parameters, $key)) {
            return $default;
        } else {
            return Hash::get($this->_parameters, $key);
        }
    }
    
    /**
     * Return an array of pluginname / PluginEventContent but only when it has content
     * 
     * @return PluginEventContent[]
     */
    public function getAllContent()
    {
        $output = array();
        foreach ($this->_content as $plugin => $content) {
            /* @var $content PluginEventContent */
            if ($content->hasContent()) {
                $output[$plugin] = $content; 
            }
        }
        
        return $output;
    }
    
    /**
     * Returns content for the given plugin(name)
     * 
     * When there is no content yet, it will return an empty content object.
     * 
     * @param PluginBase|string $plugin The plugin we want content for or a string name
     * @return PluginEventContent
     */
    public function getContent($plugin)
    {
        $pluginName = '';
        if (is_string($plugin)) {
            $pluginName = $plugin;
        } elseif ($plugin instanceof PluginBase) {
            $pluginName = get_class($plugin);
        }
        
        if (array_key_exists($pluginName, $this->_content)) {
            return $this->_content[$pluginName];
        } else {
            return $this->setContent($pluginName);
        }
    }
    
    /**
     * Return the name of the event
     * 
     * @return string
     */
    public function getEventName()
    {
        return $this->_event;
    }
    
    /**
     * Return the sender of the event
     * 
     * Normally the class that fired the event, but can return false when not set.
     * 
     * @return object The object sending the event, or false when unknown
     */
    public function getSender()
    {
        if (!is_null($this->_sender)) {
            return $this->_sender;
        } else {
            return false;
        }
    }
    
    /**
     * Returns true when execution of this event was stopped using $this->stop()
     * 
     * @return boolean
     */
    public function isStopped()
    {
        return $this->_stop;
    }
    
    /**
     * Set a key/value pair to be used by plugins hanlding this event.
     * 
     * @param string $key
     * @param mixed $value
     * @return \PluginEvent Fluent interface
     */
    public function set($key, $value)
    {
        $this->_parameters = Hash::insert($this->_parameters, $key, $value);
        return $this;
    }

    /**
     * Appends a new value into the old.
     *
     * $value has to be an array in this case, since it is
     * assumed that old value was an array. The new and old
     * array value will be merged.
     *
     * @param string $key
     * @param array $value
     * @return \PluginEvent Fluent interface
     */
    public function append($key, array $value)
    {
        if (!Hash::check($this->_parameters, $key)) {
            $oldValue = array();
        } else {
            $oldValue = Hash::get($this->_parameters, $key);
        }

        $value = array_merge($value, $oldValue);

        $this->_parameters = Hash::insert($this->_parameters, $key, $value);
        return $this;
    }
    
    /**
     * Set content for $plugin, replacing any preexisting content
     * 
     * @param string $plugin The plugin setting the context or a string name
     * @param string $content
     * @param string $cssClass
     * @param string $id
     * @return PluginEventContent
     */
    public function setContent($plugin, $content = null, $cssClass = null, $id = null)
    {
        if (is_string($plugin)) {
            $pluginName = $plugin;
        } elseif ($plugin instanceof PluginBase) {
            $pluginName = get_class($plugin);
        }
        
        $contentObject = new PluginEventContent($content, $cssClass, $id);
        if (isset($pluginName)) {
            $this->_content[$pluginName] = $contentObject;
        } else {
            $this->_content[] = $contentObject;
        }
        
        return $contentObject;        
    }
    
    /**
     * Halt execution of this event by other plugins
     */
    public function stop()
    {
        $this->_stop = true;
    }
}
