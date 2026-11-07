<?php

namespace LimeSurvey\Helpers\Update;

use LSActiveRecord;

class Update_416 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
        // encrypt values in db
        // Read raw values from DB : App()->getConfig() decrypts crypted settings, values are still plain text here
        // Do not use SettingGlobal::setSetting : it encrypts crypted settings itself
        foreach (['emailsmtppassword', 'bounceaccountpass'] as $settingName) {
            $setting = $this->db->createCommand()
                ->select('stg_value')
                ->from('{{settings_global}}')
                ->where('stg_name = :stg_name', [':stg_name' => $settingName])
                ->queryRow();
            if (empty($setting) || $setting['stg_value'] === '' || $setting['stg_value'] === null) {
                continue;
            }
            // Force Basic method : historical update, must not depend on current default
            $encryptedValue = LSActiveRecord::encryptSingle($setting['stg_value'], 'B');
            $this->db->createCommand()->update(
                '{{settings_global}}',
                ['stg_value' => $encryptedValue],
                'stg_name = :stg_name',
                [':stg_name' => $settingName]
            );
            // Keep current loaded config consistent with DB
            App()->setConfig($settingName, $encryptedValue);
        }

        // encrypt bounceaccountpass value in db
        \alterColumn('{{surveys}}', 'bounceaccountpass', "text", true, 'NULL');
        $sSurveyQuery = "SELECT * from {{surveys}} order by sid";
        $aSurveys = $this->db->createCommand($sSurveyQuery)->queryAll();
        foreach ($aSurveys as $aSurvey) {
            if (!empty($aSurvey['bounceaccountpass'])) {
                $this->db->createCommand()->update(
                    '{{surveys}}',
                    [
                        'bounceaccountpass' => LSActiveRecord::encryptSingle(
                            $aSurvey['bounceaccountpass'],
                            'B'
                        )
                    ],
                    "sid=" . $aSurvey['sid']
                );
            }
        }
    }
}
