<?php

namespace LimeSurvey\Helpers\Update;

class Update_646 extends DatabaseUpdateBase
{
    public function up()
    {
        // Fix serialized attachments, get only needed surveys_languagesettings row
        $surveyslanguage = \Yii::app()->db->createCommand()
            ->select(['surveyls_survey_id', 'surveyls_language', 'attachments'])
            ->from('{{surveys_languagesettings}}')
            ->where("attachments <> '' AND attachments IS NOT NULL AND attachments <> :emptyarray")
            ->params([":emptyarray" => serialize([]))
            ->queryAll();
        foreach ($surveyslanguage as $surveylanguage) {
            /* Check if it can be a json */
            if (!empty($surveylanguage['attachments']) && substr($surveylanguage['attachments'], 0, 1) != '{' && substr($surveylanguage['attachments'], 0, 1) != '[') {
                $sSerialType = getSerialClass($surveylanguage['attachments']);
                if ($sSerialType == 'array') {
                    $unserialized = unserialize($surveylanguage['attachments'], ["allowed_classes" => false]);
                    if (empty($unserialized) || !is_array($unserialized)) {
                        /* Save broken as empty string */
                        $newAttachments = "";
                    } else {
                        $newAttachments = json_encode($unserialized);
                        if ($newAttachments === false) {
                            /* JSON encoding failed, save as empty string */
                            $newAttachments = "";
                        }
                    }
                } else {
                    /* All other as empty string */
                    $newAttachments = "";
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
