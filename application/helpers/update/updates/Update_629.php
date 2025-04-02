<?php

namespace LimeSurvey\Helpers\Update;

class Update_629 extends DatabaseUpdateBase
{
    public function up()
    {
        $updates = [
            'browserdetect' => 'themes/question/browserdetect/survey/questions/answer/shortfreetext/assets/browserdetect.png',
            'inputondemand' => 'themes/question/inputondemand/survey/questions/answer/multipleshorttext/assets/inputondemand.png',
            'image_select-multiplechoice' => 'themes/question/image_select-multiplechoice/survey/questions/answer/multiplechoice/assets/image_select-multiplechoice.png',
            'ranking_advanced' => 'themes/question/ranking_advanced/survey/questions/answer/ranking/assets/ranking_advanced.png',
        ];

        foreach ($updates as $name => $imagePath) {
            $this->db->createCommand()->update(
                '{{question_themes}}',
                ['image_path' => $imagePath],
                'name = :name',
                [':name' => $name]
            );
        }   
    }
}