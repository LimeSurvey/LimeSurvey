<?php

namespace LimeSurvey\Helpers\Update;

use SettingGlobal;
use LSActiveRecord;

class Update_416 extends DatabaseUpdateBase
{
    public function up()
    {
        // encrypt values in db
        SettingGlobal::setSetting(
            'emailsmtppassword',
            LSActiveRecord::encryptSingle(App()->getConfig('emailsmtppassword'))
        );
        SettingGlobal::setSetting(
            'bounceaccountpass',
            LSActiveRecord::encryptSingle(App()->getConfig('bounceaccountpass'))
        );

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
                            $aSurvey['bounceaccountpass']
                        )
                    ],
                    "sid=" . $aSurvey['sid']
                );
            }
        }
    }
}
