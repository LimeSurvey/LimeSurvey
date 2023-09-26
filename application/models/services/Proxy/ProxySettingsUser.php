<?php

namespace LimeSurvey\Models\Services\Proxy;

use SettingsUser;

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
    public function getUserSettingValue(
        $stg_name,
        $uid = null,
        $entity = null,
        $entity_id = null,
        $default = null
    ) {
        return SettingsUser::getUserSettingValue(
            $stg_name,
            $uid,
            $entity,
            $entity_id,
            $default
        );
    }

    /**
     * Changes or creates a user setting
     *
     * @param string $stg_name
     * @param integer $stg_value
     * @param integer $uid | Can be omitted to just take the currently logged in users id
     * @param string $entity | optional defaults to 'null'
     * @param integer $entity_id | optional defaults to 'null'
     * @return boolean Saving success/failure
     */
    public static function setUserSetting($stg_name, $stg_value, $uid = null, $entity = null, $entity_id = null)
    {
        return SettingsUser::setUserSetting(
            $stg_name,
            $stg_value,
            $uid,
            $entity,
            $entity_id
        );
    }

    /**
     * Deletes user setting
     *
     * @param string $stg_name
     * @param integer $uid | Can be omitted to just take the currently logged in users id
     * @param string $entity | optional defaults to 'null'
     * @param integer $entity_id | optional defaults to 'null'
     * @return boolean Deleting success/failure
     */
    public static function deleteUserSetting($stg_name, $uid = null, $entity = null, $entity_id = null)
    {
        return SettingsUser::deleteUserSetting(
            $stg_name,
            $uid,
            $entity,
            $entity_id
        );
    }
}
