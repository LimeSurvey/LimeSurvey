<?php

namespace LimeSurvey\Helpers\Update;

class Update_434 extends DatabaseUpdateBase
{
    public function up()
    {
            $defaultSetting = \LsDefaultDataSets::getDefaultUserAdministrationSettings();

            $this->db->createCommand()->delete('{{settings_global}}', 'stg_name=:name', [':name' => 'sendadmincreationemail']);
            $this->db->createCommand()->delete('{{settings_global}}', 'stg_name=:name', [':name' => 'admincreationemailsubject']);
            $this->db->createCommand()->delete('{{settings_global}}', 'stg_name=:name', [':name' => 'admincreationemailtemplate']);

            $this->db->createCommand()->insert(
                '{{settings_global}}',
                [
                    "stg_name" => 'sendadmincreationemail',
                    "stg_value" => $defaultSetting['sendadmincreationemail'],
                ]
            );

            $this->db->createCommand()->insert(
                '{{settings_global}}',
                [
                    "stg_name" => 'admincreationemailsubject',
                    "stg_value" => $defaultSetting['admincreationemailsubject'],
                ]
            );

            $this->db->createCommand()->insert(
                '{{settings_global}}',
                [
                    "stg_name" => 'admincreationemailtemplate',
                    "stg_value" => $defaultSetting['admincreationemailtemplate'],
                ]
            );
    }
}
