<?php
namespace ls\pluginmanager;
interface iPlugin {

       /**
     * Provides meta data on the plugin settings that are available for this plugin.
     * This does not include enable / disable; a disabled plugin is never loaded.
     * @param boolean $getValues Set to false to not get the current value for each plugin setting.
     * @return array
     */
    public function getPluginSettings($getValues = true);

    /**
     * Returns a reference to the storage interface for the plugin.
     * @return iPluginStorage 
     */
    public function getStore();
    
    /**
     * Saves the settings for this plugin
     * 
     * Assumes an array with valid key/value pairs is passed.
     * 
     * @param array $settings An array with key/value pairs for all plugin settings
     */
    public function saveSettings(array $settings);
    
    /**
     * Set the event to the plugin, this method is executed by the PluginManager
     * just before dispatching the event.
     * 
     * @param PluginEvent $event
     * @return PluginBase
     */
   public function setEvent(PluginEvent $event);
}