<?php

namespace LimeSurvey\Helpers\Update;

class Update_644 extends DatabaseUpdateBase
{
    public function up()
    {
        // Fix serialized attachments
        $surveyslanguage = \Yii::app()->db->createCommand()
            ->select(['surveyls_survey_id', 'surveyls_language', 'attachments'])
            ->from('{{surveys_languagesettings}}')
            ->where("attachments <> '' AND attachments IS NOT NULL")
            ->queryAll();
        foreach ($surveyslanguage as $surveylanguage) {
            if (!empty($surveylanguage['attachments']) && substr($surveylanguage['attachments'], 0, 1) != '{' && substr($surveylanguage['attachments'], 0, 1) != '[') {
                $sSerialType = getSerialClass($surveylanguage['attachments']);
                if ($sSerialType == 'array') {
                    $unserialized = unserialize($surveylanguage['attachments'], ["allowed_classes" => false]);
                    if (empty($unserialized) || !is_array($unserialized)) {
                        /* Save broken as empty string */
                        $newAttachments = "";
                    } else {
                        $newAttachments = json_encode($unserialized);
                    }
                    $updateCommand = \Yii::app()->db->createCommand();
                    $updateCommand->update(
                        '{{surveys_languagesettings}}',
                        array('attachments' => $newAttachments),
                        'surveyls_survey_id = :sid and surveyls_language = :language',
                        array(':sid' => $surveylanguage['surveyls_survey_id'], ':language' => $surveylanguage['surveyls_language'])
                    );
                }
            }
        }
    }
}
