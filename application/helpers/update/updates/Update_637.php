<?php

namespace LimeSurvey\Helpers\Update;

class Update_637 extends DatabaseUpdateBase
{
    public function up()
    {
        // Fix serialized attribute_descriptions if they are not yet in JSON format
        $command = \Yii::app()->db->createCommand();
        $surveys = $command->select('*')->from('{{surveys}}')->queryAll();

        foreach ($surveys as $survey) {
            if (!empty($survey['attributedescriptions']) && substr($survey['attributedescriptions'], 0, 1) != '{' && substr($survey['attributedescriptions'], 0, 1) != '[') {
                $sSerialType = getSerialClass($survey['attributedescriptions']);
                if ($sSerialType == 'array') {
                    $newAttributeDescriptions = json_encode(unserialize($survey['attributedescriptions'], ["allowed_classes" => false]) ?? []);

                    $updateCommand = \Yii::app()->db->createCommand();
                    $updateCommand->update(
                        '{{surveys}}',
                        array('attributedescriptions' => $newAttributeDescriptions),
                        'sid = :sid',
                        array(':sid' => $survey['sid'])
                    );
                }
            }
        }
    }
}
