<?php

namespace LimeSurvey\Helpers\Update;

class Update_496 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand('UPDATE {{question_themes}} SET settings=\'{"subquestions":"1","answerscales":"1","hasdefaultvalues":"0","assessable":"1","hidesubquestionrelevance":"1","class":"array-flexible-column"}\' WHERE name=\'arrays/column\'')->execute();
    }
}
