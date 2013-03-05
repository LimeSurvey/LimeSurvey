<?php
class PluginEvent
{
    /**
     * The name of this event
     * 
     * @var string 
     */
    protected $_event = '';
    
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
        if (!is_null($sender) && is_object($sender))
        {
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
        if ($key != null)
        {
            $keys = explode('.', $key);
            $array = $this->_parameters;

            // Retrieve using dot notation.
            while (count($keys) > 1)
            {
                $first = array_shift($keys);
                if  (isset($array[$first]))
                {
                    $array = $array[$first];
                }
                else
                {
                    return $default;
                }
            }

            if (isset($array[$keys[0]]))
            {
                return $array[$keys[0]];
            }
            else
            {
                return $default;
            }
        }
        else
        {
            return $this->_parameters;
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
     * Set a key/value pair to be used by plugins hanlding this event.
     * 
     * @param string $key
     * @param mixed $value
     * @return \PluginEvent Fluent interface
     */
    public function set($key, $value)
    {
        // Split by . to allow for arrays using dotnotation.
        $keys = explode('.', $key);
        while (count($keys) > 0)
        {
            $key = array_pop($keys);
            if ($key == '')
            {
                $value = array($value);
            }
            else
            {
                $value = array($key => $value);
            }
            
        }
        $this->_parameters = array_merge($this->_parameters, $value);
        return $this;
    }
    
    /**
     * Halt execution of this event by other plugins
     */
    public function stop()
    {
        $this->_stop = true;
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
}