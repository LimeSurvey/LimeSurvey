<?php

namespace LimeSurvey\Helpers\Update;

class Update_447 extends DatabaseUpdateBase
{
    public function up()
    {

            $this->db->createCommand()->addColumn('{{users}}', 'validation_key', 'string(38)');
            $this->db->createCommand()->addColumn('{{users}}', 'validation_key_expiration', 'datetime');

            //override existing email text (take out password there)
            $sqlGetAdminCreationEmailTemplat = "SELECT stg_value FROM {{settings_global}} WHERE stg_name='admincreationemailtemplate'";
            $adminCreationEmailTemplateValue = $this->db->createCommand($sqlGetAdminCreationEmailTemplat)->queryAll();
        if ($adminCreationEmailTemplateValue) {
            if ($adminCreationEmailTemplateValue[0]['stg_value'] === null || $adminCreationEmailTemplateValue[0]['stg_value'] === '') {
                // if text in admincreationemailtemplate is empty use the default from LsDafaultDataSets
                $defaultCreationEmailContent = \LsDefaultDataSets::getDefaultUserAdministrationSettings();
                $replaceValue = $defaultCreationEmailContent['admincreationemailtemplate'];
            } else { // if not empty replace PASSWORD with *** and write it back to DB
                $replaceValue = str_replace('PASSWORD', '***', (string) $adminCreationEmailTemplateValue[0]['stg_value']);
            }
            $this->db->createCommand()->update(
                '{{settings_global}}',
                array('stg_value' => $replaceValue),
                "stg_name='admincreationemailtemplate'"
            );
        }
    }
}
