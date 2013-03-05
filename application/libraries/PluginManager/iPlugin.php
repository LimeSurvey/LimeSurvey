<?php

interface iPlugin {

    /**
     * Should return the description for this plugin
     * Constructor for the plugin
     * 
     * @param PluginManager $manager    The plugin manager instantiating the object
     * @param int           $id         The id for storage
     */
    public function __construct(PluginManager $manager, $id);

    /**
     * Return the description for this plugin
     */
    public static function getDescription();

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
     * Returns a reference to the storage interface for the plugin.
     * @return iPluginStorage 
     */
    public function getStore();
}