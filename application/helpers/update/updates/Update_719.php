<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Pluggable CAPTCHA: add the captchatype survey setting (with inheritance) and activate the CoreCaptcha plugin
 */
class Update_719 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function up()
    {
        $surveysTable = $this->db->schema->getTable('{{surveys}}');
        if (!isset($surveysTable->columns['captchatype'])) {
            $this->db->createCommand()->addColumn(
                '{{surveys}}',
                'captchatype',
                "string(50) NOT NULL DEFAULT 'inherit'"
            );
        }

        $surveysGroupsettingsTable = $this->db->schema->getTable('{{surveys_groupsettings}}');
        if (!isset($surveysGroupsettingsTable->columns['captchatype'])) {
            $this->db->createCommand()->addColumn(
                '{{surveys_groupsettings}}',
                'captchatype',
                "string(50) NOT NULL DEFAULT 'inherit'"
            );
            // The accessible ALTCHA CAPTCHA is the global default, survey groups inherit it
            $this->db->createCommand()->update('{{surveys_groupsettings}}', ['captchatype' => 'altcha'], 'gsid=0');
        }

        $pluginExists = $this->db->createCommand()
            ->select('id')
            ->from('{{plugins}}')
            ->where('name = :name', [':name' => 'CoreCaptcha'])
            ->queryRow();
        if (!$pluginExists) {
            $this->db->createCommand()->insert(
                '{{plugins}}',
                [
                    'name' => 'CoreCaptcha',
                    'plugin_type' => 'core',
                    'active' => 1,
                    'version' => '1.0.0',
                    'load_error' => 0,
                    'load_error_message' => null,
                    'priority' => 1,
                ]
            );
        }
    }
}
