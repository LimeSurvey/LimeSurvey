<?php

namespace LimeSurvey\PluginManager;

class DummyStorage implements iPluginStorage
{
    /**
     * Always fail to get.
     */
    public function get($plugin, $key = null, $model = null, $id = null, $default = null, $language = null)
    {
        return false;
    }

    /**
     * Always fail to save.
     */
    public function set($plugin, $key, $data, $model = 'NULL', $id = 'NULL', $language = 'NULL')
    {
        echo "DummyStorage::set('" . get_class($plugin) . "', '$key', " . serialize($data) . ", '$model', '$id', '$language')<br>";
        return false;
    }


    public function __construct()
    {
    }
}
