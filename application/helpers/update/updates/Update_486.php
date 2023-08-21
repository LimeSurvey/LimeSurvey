<?php

namespace LimeSurvey\Helpers\Update;

use LsDefaultDataSets;

class Update_486 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->update(
            "{{question_themes}}",
            [
                'xml_path' => 'themes/question/bootstrap_buttons_multi/survey/questions/answer/multiplechoice',
                'image_path' => '/themes/question/bootstrap_buttons_multi/survey/questions/answer/multiplechoice/assets/bootstrap_buttons_multiplechoice.png',
            ],
            "name='bootstrap_buttons_multi'"
        );
    }
}
