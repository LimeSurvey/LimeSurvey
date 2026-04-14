<?php

namespace LimeSurvey\Helpers\Update;

use QuestionTheme;
use Question;

class Update_703 extends DatabaseUpdateBase
{
    /**
     * Remove nokeyboard column from surveys and surveys_group_settings tables.
     * The on-screen keyboard functionality has been deprecated as modern systems
     * provide native virtual keyboards at the OS/browser level.
     */
    public function up()
    {
        $rankingThemes = QuestionTheme::model()->findAll("question_type = :question_type", [":question_type" => Question::QT_R_RANKING]);
        foreach ($rankingThemes as $rankingTheme) {
            $settings = json_decode($rankingTheme->settings ?? '{}', true);
            $settings['subquestions'] = "1";
            $settings['answerscales'] = "0";
            $rankingTheme->settings = json_encode($settings);
            $rankingTheme->save();
        }
    }
}
