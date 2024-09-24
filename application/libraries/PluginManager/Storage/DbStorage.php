<?php

namespace LimeSurvey\PluginManager;

use PluginSetting;

class DbStorage implements iPluginStorage
{
    /**
     * NB: Needed even if empty.
     */
    public function __construct()
    {
    }

    /**
     * @param iPlugin $plugin
     * @param string $key Key for the setting; passing null will return all keys.
     * @param string $model Optional model name to which the data was attached.
     * @param int $id Optional id of the model instance to which the data was attached.
     * @param mixed $default Default value to return if key could not be found.
     * @param string $language Optional language identifier used for retrieving the setting.
     * @return mixed Returns the value from the database or null if not set.
     */
    public function get(iPlugin $plugin, $key = null, $model = null, $id = null, $default = null, $language = null)
    {
        $functionName = 'get' . ucfirst((string)$model);
        if ($model == null || !method_exists($this, $functionName)) {
            return $this->getGeneric($plugin, $key, $model, $id, $default);
        } else {
            return $this->$functionName($plugin, $key, $model, $id, $default, $language);
        }
    }

    /**
     * @param iPlugin $plugin
     * @param string $key
     * @param string $model Optional model name to which the data was attached.
     * @param int $id Optional id of the model instance to which the data was attached.
     * @param mixed $default Default value to return if key could not be found.
     * @return mixed Returns the value from the database or null if not set.
     */
    protected function getGeneric(iPlugin $plugin, $key, $model, $id, $default)
    {
        $attributes = array(
            'plugin_id' => $plugin->getId(),
            'model'     => $model,
            'model_id'  => $id,
        );
        if ($key != null) {
            $attributes['key'] = $key;
        }

        $records = \PluginSetting::model()->findAllByAttributes($attributes);
        if (count($records) > 1) {
            foreach ($records as $record) {
                $result[] = json_decode((string) $record->value, true);
            }
        } elseif (count($records) == 1) {
            $result = json_decode((string) $records[0]->value, true);
        } else {
            $result = $default;
        }
        return $result;
    }

    /**
     * @param iPlugin $plugin
     * @param string $key
     * @param mixed data Default value to return if key could not be found.
     * @param string $model Optional model name to which the data was attached.
     * @param int $id Optional id of the model instance to which the data was attached.
     * @param string $language Optional language identifier used for storing the setting.
     * @return boolean
     */
    public function set(iPlugin $plugin, $key, $data, $model = null, $id = null, $language = null)
    {
        $functionName = 'set' . ucfirst((string)$model);
        if ($model == null || !method_exists($this, $functionName)) {
            return $this->setGeneric($plugin, $key, $data, $model, $id, $language);
        } else {
            return $this->$functionName($plugin, $key, $data, $model, $id, $language);
        }
    }

    /**
     * @param iPlugin $plugin
     * @param string $key
     * @param mixed data Default value to return if key could not be found.
     * @param string $model Optional model name to which the data was attached.
     * @param int $id Optional id of the model instance to which the data was attached.
     * @param string $language Optional language identifier used for storing the setting.
     * @return boolean
     */
    protected function setGeneric(iPlugin $plugin, $key, $data, $model, $id, $language)
    {
        if ($id == null && $model != null) {
            throw new \Exception("DbStorage::set cannot store setting for model $model without valid id.");
        }
        $attributes = array(
            'plugin_id' => $plugin->getId(),
            'model'     => $model,
            'model_id'  => $id,
            'key'       => $key
        );
        $record = PluginSetting::model()->findByAttributes($attributes);
        if (is_null($record)) {
            // New setting
            $record = PluginSetting::model()->populateRecord($attributes);
            $record->setIsNewRecord(true);
        }
        $record->value = json_encode($data);
        $result = $record->save();

        return $result;
    }
}
