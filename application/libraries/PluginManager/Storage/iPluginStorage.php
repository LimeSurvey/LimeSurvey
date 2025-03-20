<?php

namespace LimeSurvey\PluginManager\Storage;
use LimeSurvey\PluginManager\iPlugin;

interface iPluginStorage
{

    /**
     * Returns plugin data.
     * @param object $plugin The plugin object getting its data.
     * @param string | null $key The storage key, if null will return all data for the plugin.
     * @param string $model Name of a model in case its model specific plugin data, like for a specific question or survey.
     * @param int $id Id of the model for which the data is retreived
     * @param mixed $default The default value to use when none present
     * @param string $language The optional language to use
     * @return mixed The data stored.
     */
    public function get(iPlugin $plugin, $key = null, $model = null, $id = null, $default = null, $language = null);

    /**
     * Stores plugin data.
     * @param object $plugin The plugin object getting its data.
     * @param string $key The storage key to identify the data.
     * @param mixed $data The data to be stored, serialized using serialize.
     * @param string $model Name of a model in case its model specific plugin data, like for a specific question or survey.
     * @param int $id Id of the model for which the data is retreived
     * @param string $language The optional language to use
     */
    public function set(iPlugin $plugin, $key, $data, $model = null, $id = null, $language = null);


    /**
     * Constructor must not take argument.
     * The constructor must be part of the interface since our Plugin factory
     * calls it and thus must know its arguments.
     */
    public function __construct();
}
