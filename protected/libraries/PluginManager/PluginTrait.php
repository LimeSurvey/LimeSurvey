<?php
namespace ls\pluginmanager;

trait PluginTrait
{
    protected $settings = [];
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
     * 
     * @param type $settings
     */
    public function saveSettings(array $settings)
    {
        foreach ($settings as $name => $setting) {
            $this->set($name, $setting);
        }
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
}
