<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Password requirements moved from the PasswordRequirement core plugin into the core
 * (bug #20551). Migrate the former plugin settings into the settings_global table and
 * remove the now-obsolete plugin.
 */
class Update_711 extends DatabaseUpdateBase
{
    public function up()
    {
        $plugin = $this->db->createCommand()
            ->select('id')
            ->from('{{plugins}}')
            ->where('name = :name', [':name' => 'PasswordRequirement'])
            ->queryRow();

        if ($plugin === false || !isset($plugin['id'])) {
            return;
        }
        $pluginId = (int) $plugin['id'];

        // Read the former plugin settings (values are JSON encoded, keyed by "key").
        $rows = $this->db->createCommand()
            ->select('*')
            ->from('{{plugin_settings}}')
            ->where('plugin_id = :pid', [':pid' => $pluginId])
            ->queryAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = json_decode((string) $row['value'], true);
        }

        // Administration account rules (start from the core defaults, override with migrated values).
        $adminRules = ['min' => 8, 'max' => 0, 'lower' => 0, 'upper' => 1, 'numeric' => 1, 'symbol' => 0];
        if (isset($settings['minimumSize']) && $settings['minimumSize'] !== '' && $settings['minimumSize'] !== null) {
            $adminRules['min'] = (int) $settings['minimumSize'];
        }
        if (array_key_exists('needsUppercase', $settings)) {
            $adminRules['upper'] = !empty($settings['needsUppercase']) ? 1 : 0;
        }
        if (array_key_exists('needsNumber', $settings)) {
            $adminRules['numeric'] = !empty($settings['needsNumber']) ? 1 : 0;
        }
        if (array_key_exists('needsNonAlphanumeric', $settings)) {
            $adminRules['symbol'] = !empty($settings['needsNonAlphanumeric']) ? 1 : 0;
        }

        // "Save and return later" rules. The check is always enforced now, so when the former
        // plugin toggle was off we migrate empty (disabled) rules to preserve the previous behaviour.
        $saveRules = ['min' => 0, 'max' => 0, 'lower' => 0, 'upper' => 0, 'numeric' => 0, 'symbol' => 0];
        if (!empty($settings['surveySaveActive'])) {
            if (isset($settings['surveySaveMinimumSize']) && $settings['surveySaveMinimumSize'] !== '' && $settings['surveySaveMinimumSize'] !== null) {
                $saveRules['min'] = (int) $settings['surveySaveMinimumSize'];
            }
            if (!empty($settings['surveySaveNeedsUppercase'])) {
                $saveRules['upper'] = 1;
            }
            if (!empty($settings['surveySaveNeedsNumber'])) {
                $saveRules['numeric'] = 1;
            }
            if (!empty($settings['surveySaveNeedsNonAlphanumeric'])) {
                $saveRules['symbol'] = 1;
            }
        }

        $this->setGlobalSetting('passwordValidationRules', json_encode($adminRules));
        $this->setGlobalSetting('passwordValidationRulesSurveySave', json_encode($saveRules));

        // Remove the obsolete plugin and its settings.
        $this->db->createCommand()->delete('{{plugin_settings}}', 'plugin_id = :pid', [':pid' => $pluginId]);
        $this->db->createCommand()->delete('{{plugins}}', 'id = :pid', [':pid' => $pluginId]);
    }

    /**
     * Inserts or updates a single settings_global row without using models.
     * @param string $name
     * @param string $value
     * @return void
     */
    private function setGlobalSetting($name, $value)
    {
        $exists = $this->db->createCommand()
            ->select('stg_name')
            ->from('{{settings_global}}')
            ->where('stg_name = :name', [':name' => $name])
            ->queryScalar();
        if ($exists === false) {
            $this->db->createCommand()->insert('{{settings_global}}', ['stg_name' => $name, 'stg_value' => $value]);
        } else {
            $this->db->createCommand()->update('{{settings_global}}', ['stg_value' => $value], 'stg_name = :name', [':name' => $name]);
        }
    }
}
