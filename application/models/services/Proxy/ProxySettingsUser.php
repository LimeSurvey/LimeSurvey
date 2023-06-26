<?php

namespace LimeSurvey\Models\Services\Proxy;


/**
 * ProxySettingsUser Service
 *
 * Wraps static methods on SettingsUser to make them injectable to other services.
 */
class ProxySettingsUser
{
    /**
     * Gets a user settings value depending on the given parameters
     * Shorthand function
     *
     * @param string $stg_name
     * @param integer|null $uid | Can be omitted to just take the currently logged in users id
     * @param integer|null $entity | optional defaults to 'null'
     * @param integer|null $entity_id | optional defaults to 'null'
     * @param mixed $default | optional defaults to 'null'
     * @return mixed|null  The current settings value or null id there is no setting
     */
    public function getUserSettingValue($stg_name, $uid = null, $entity = null, $entity_id = null, $default = null)
    {
        $setting = \SettingsUser::getUserSetting($stg_name, $uid, $entity, $entity_id);
        return $setting != null ? $setting->getAttribute('stg_value') : $default;
    }
}
