<?php

namespace LimeSurvey\Helpers\Update;

use Yii;

class Update_721 extends DatabaseUpdateBase
{
    /** The passwordValidationRules rules and the PasswordRequirement settings switching them on */
    private const SWITCHES = [
        'lower' => 'needsLowercase',
        'upper' => 'needsUppercase',
        'numeric' => 'needsNumber',
        'symbol' => 'needsNonAlphanumeric',
    ];

    /**
     * The PasswordRequirement core plugin is now the only place for the password rules of administration users:
     * Activate it, and copy the rules of the removed passwordValidationRules setting.
     * Without the setting in config.php, these are the former defaults from config-defaults.php.
     */
    #[\Override]
    public function up()
    {
        $plugin = $this->db->createCommand()
            ->select('id, active, load_error')
            ->from('{{plugins}}')
            ->where('name = :name', [':name' => 'PasswordRequirement'])
            ->queryRow();
        // Only an active plugin without load error checked passwords before
        $wasActive = $plugin && (int) $plugin['active'] === 1 && (int) $plugin['load_error'] === 0;
        if (empty($plugin)) {
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
            // getLastInsertID() without a sequence name fails on Postgres
            $pluginId = (int) $this->db->createCommand()
                ->select('id')
                ->from('{{plugins}}')
                ->where('name = :name', [':name' => 'PasswordRequirement'])
                ->queryScalar();
        } else {
            $pluginId = (int) $plugin['id'];
            // A plugin with a load error is not loaded at all, give it a new chance
            $this->db->createCommand()->update(
                '{{plugins}}',
                ['active' => 1, 'version' => '1.2.0', 'load_error' => 0, 'load_error_message' => null],
                'id = :id',
                [':id' => $pluginId]
            );
        }

        $rules = Yii::app()->getConfig('passwordValidationRules');
        if (!is_array($rules)) {
            // The former config-defaults.php values, config.php replaced them completely when set
            $rules = ['min' => 8, 'max' => 0, 'lower' => 0, 'upper' => 1, 'numeric' => 1, 'symbol' => 0];
        }
        if ($wasActive) {
            $this->addStricterRules($pluginId, $rules);
        } else {
            $this->replaceRules($pluginId, $rules);
        }
        $this->dropImpossibleMaximumSize($pluginId);
    }

    /**
     * Clear a maximum password length below the minimum length or the number of required characters:
     * No password could match these rules, and random passwords would be cut to the maximum.
     * The maximum gives way because dropping it doesn't weaken the rules.
     * Same check as PasswordRequirement::saveSettings().
     * @param int $pluginId
     * @return void
     */
    private function dropImpossibleMaximumSize(int $pluginId)
    {
        $maximumSize = (int) $this->getPluginSetting($pluginId, 'maximumSize');
        if ($maximumSize <= 0) {
            return;
        }
        $minimumSize = $this->getPluginSetting($pluginId, 'minimumSize');
        if ($minimumSize === null || $minimumSize === '') {
            // PasswordRequirement::DEFAULT_MINIMUM_SIZE
            $minimumSize = 12;
        }
        // Random passwords always have a lowercase character, and one character of each other required kind.
        // Unsaved settings use the plugin defaults.
        $requiredCharacters = 1;
        foreach (['needsUppercase' => true, 'needsNumber' => true, 'needsNonAlphanumeric' => false] as $setting => $default) {
            $value = $this->getPluginSetting($pluginId, $setting);
            $requiredCharacters += (int) ($value === null ? $default : !empty($value));
        }
        if ($maximumSize < max((int) $minimumSize, $requiredCharacters)) {
            $this->setPluginSetting($pluginId, 'maximumSize', '');
        }
    }

    /**
     * Replace the plugin settings by the former core rules, the only rules that applied while the plugin was inactive
     * @param int $pluginId
     * @param array $rules the former passwordValidationRules setting
     * @return void
     */
    private function replaceRules(int $pluginId, array $rules)
    {
        foreach (self::SWITCHES as $rule => $setting) {
            $this->setPluginSetting($pluginId, $setting, (int) ($rules[$rule] ?? 0) > 0 ? '1' : '0');
        }
        $this->setPluginSetting($pluginId, 'minimumSize', (string) (int) ($rules['min'] ?? 0));
        $maximumSize = (int) ($rules['max'] ?? 0);
        $this->setPluginSetting($pluginId, 'maximumSize', $maximumSize > 0 ? (string) $maximumSize : '');
    }

    /**
     * Add the former core rules to the plugin settings where they are stricter, both applied while the plugin was active
     * @param int $pluginId
     * @param array $rules the former passwordValidationRules setting
     * @return void
     */
    private function addStricterRules(int $pluginId, array $rules)
    {
        foreach (self::SWITCHES as $rule => $setting) {
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
