<?php

namespace LimeSurvey\Helpers\Update;

class Update_604 extends DatabaseUpdateBase
{
    public function up()
    {

        $fixedXmlPaths = [
            'image_select-listradio' => 'themes/question/image_select-listradio/survey/questions/answer/listradio',
            'image_select-multiplechoice' => 'themes/question/image_select-multiplechoice/survey/questions/answer/multiplechoice',
        ];

        foreach ($fixedXmlPaths as $themeName => $newXmlPath) {
            $this->db->createCommand()->update('{{question_themes}}', array('xml_path' => $newXmlPath), "name='$themeName'");
        }
    }
}
