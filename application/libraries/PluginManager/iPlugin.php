<?php
namespace LimeSurvey\PluginManager;
interface iPlugin
{

    /**
     * Should return the description for this plugin
     * Constructor for the plugin
     * @param PluginManager $manager    The plugin manager instantiating the object
     * @param int           $id         The id for storage
     */
    public function __construct(PluginManager $manager, $id);

    /**
     * Return the description for this plugin
     */
    public static function getDescription();
    
    /**
     * Get the current event this plugin is responding to
     * 
     * @return PluginEvent
     */
    public function getEvent();

    /**
     * Get the id of this plugin (set by PluginManager on instantiation)
     * 
     * @return int
     */
    public function getId();

    /**
     * Provides meta data on the plugin settings that are available for this plugin.
     * This does not include enable / disable; a disabled plugin is never loaded.
     * @param boolean $getValues Set to false to not get the current value for each plugin setting.
     * @return array
     */
    public function getPluginSettings($getValues = true);

    /**
     * Gets the name for the plugin, this must be unique.
     * @return string Plugin name, max length: 20.
     */
    public static function getName();
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
     * @param array $aSettings An array with key/value pairs for all plugin settings
     */
    public function saveSettings($aSettings);
    
    /**
     * Set the event to the plugin, this method is executed by the PluginManager
     * just before dispatching the event.
     * 
     * @param PluginEvent $event
     * @return PluginBase
     */
    public function setEvent(PluginEvent $event);
}
