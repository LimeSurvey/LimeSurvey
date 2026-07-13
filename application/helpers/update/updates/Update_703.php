<?php

namespace LimeSurvey\Helpers\Update;

class Update_703 extends DatabaseUpdateBase
{
    /**
     * Update question_themes settings for ranking question type ('R'):
     * set subquestions=1 and answerscales=0 in the settings JSON column.
     */
    public function up()
    {
        $db = \Yii::app()->db;
        $rankingThemes = $db->createCommand(
            "SELECT id, settings FROM {{question_themes}} WHERE question_type = :question_type"
        )->queryAll(true, [':question_type' => 'R']);

        foreach ($rankingThemes as $row) {
            $settings = json_decode($row['settings'] ?? '{}', true);
            if (!is_array($settings)) {
                $settings = [];
            }
            $settings['subquestions'] = "1";
            $settings['answerscales'] = "0";
            $db->createCommand()->update(
                '{{question_themes}}',
                ['settings' => json_encode($settings)],
                'id = :id',
                [':id' => $row['id']]
            );
        }
    }
}
