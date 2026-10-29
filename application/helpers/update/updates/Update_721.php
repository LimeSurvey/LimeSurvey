<?php

namespace LimeSurvey\Helpers\Update;

use Yii;

class Update_721 extends DatabaseUpdateBase
{
    /**
     * The PasswordRequirement core plugin is now the only place for the password rules of administration users:
     * Activate it, and copy the rules of the removed passwordValidationRules setting when they are stricter.
     * Without the setting in config.php, these are the former defaults from config-defaults.php.
     */
    #[\Override]
    public function up()
    {
        $pluginId = $this->db->createCommand()
            ->select('id')
            ->from('{{plugins}}')
            ->where('name = :name', [':name' => 'PasswordRequirement'])
            ->queryScalar();
        if (empty($pluginId)) {
            // Same values as LsDefaultDataSets::getDefaultPluginsData()
            $this->db->createCommand()->insert(
                '{{plugins}}',
                [
                    'name' => 'PasswordRequirement',
                    'plugin_type' => 'core',
                    'active' => 1,
                    'version' => '1.2.0',
                    'load_error' => 0,
                    'load_error_message' => null,
                    'priority' => 1,
                ]
            );
            $pluginId = $this->db->getLastInsertID();
        } else {
            // A plugin with a load error is not loaded at all, give it a new chance
            $this->db->createCommand()->update(
                '{{plugins}}',
                ['active' => 1, 'version' => '1.2.0', 'load_error' => 0, 'load_error_message' => null],
                'id = :id',
                [':id' => $pluginId]
            );
        }
        $pluginId = (int) $pluginId;

        $rules = Yii::app()->getConfig('passwordValidationRules');
        if (!is_array($rules)) {
            // The former config-defaults.php values, config.php replaced them completely when set
            $rules = ['min' => 8, 'max' => 0, 'lower' => 0, 'upper' => 1, 'numeric' => 1, 'symbol' => 0];
        }
        $switches = [
            'lower' => 'needsLowercase',
            'upper' => 'needsUppercase',
            'numeric' => 'needsNumber',
            'symbol' => 'needsNonAlphanumeric',
        ];
        foreach ($switches as $rule => $setting) {
            if ((int) ($rules[$rule] ?? 0) > 0) {
                $this->setPluginSetting($pluginId, $setting, '1');
            }
        }

        $minimumSize = (int) ($rules['min'] ?? 0);
        $currentMinimumSize = $this->getPluginSetting($pluginId, 'minimumSize');
        if ($currentMinimumSize === null || $currentMinimumSize === '') {
            // PasswordRequirement::DEFAULT_MINIMUM_SIZE, the plugin class isn't always loaded during updates
            $currentMinimumSize = 12;
        }
        if ($minimumSize > (int) $currentMinimumSize) {
            $this->setPluginSetting($pluginId, 'minimumSize', (string) $minimumSize);
        }

        $maximumSize = (int) ($rules['max'] ?? 0);
        $currentMaximumSize = (int) $this->getPluginSetting($pluginId, 'maximumSize');
        if ($maximumSize > 0 && ($currentMaximumSize <= 0 || $maximumSize < $currentMaximumSize)) {
            $this->setPluginSetting($pluginId, 'maximumSize', (string) $maximumSize);
        }
    }

    /**
     * Get a global plugin setting
     * @param int $pluginId
     * @param string $key
     * @return mixed null if not set
     */
    private function getPluginSetting(int $pluginId, string $key)
    {
        $value = $this->db->createCommand()
            ->select('value')
            ->from('{{plugin_settings}}')
            ->where(['and', 'plugin_id = :plugin_id', 'model IS NULL', 'model_id IS NULL', $this->db->quoteColumnName('key') . ' = :key'])
            ->queryScalar([':plugin_id' => $pluginId, ':key' => $key]);
        if ($value === false) {
            return null;
        }
        return json_decode((string) $value, true);
    }

    /**
     * Set a global plugin setting, stored as JSON like DbStorage does
     * @param int $pluginId
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function setPluginSetting(int $pluginId, string $key, $value)
    {
        $where = ['and', 'plugin_id = :plugin_id', 'model IS NULL', 'model_id IS NULL', $this->db->quoteColumnName('key') . ' = :key'];
        $params = [':plugin_id' => $pluginId, ':key' => $key];
        $exists = $this->db->createCommand()
            ->select('id')
            ->from('{{plugin_settings}}')
            ->where($where)
            ->queryScalar($params);
        if ($exists) {
            $this->db->createCommand()->update('{{plugin_settings}}', ['value' => json_encode($value)], 'id = :id', [':id' => $exists]);
        } else {
            $this->db->createCommand()->insert(
                '{{plugin_settings}}',
                [
                    'plugin_id' => $pluginId,
                    'model' => null,
                    'model_id' => null,
                    'key' => $key,
                    'value' => json_encode($value),
                ]
            );
        }
    }
}
