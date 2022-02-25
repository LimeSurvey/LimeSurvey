<?php

namespace LimeSurvey\Helpers\Update;

class Update_448 extends DatabaseUpdateBase
{
    public function up()
    {
            $this->db->createCommand('UPDATE {{question_themes}} SET settings=\'{"subquestions":"1","answerscales":"2","hasdefaultvalues":"0","assessable":"1","class":"array-flexible-dual-scale"}\' WHERE name=\'arrays/dualscale\'')->execute();
    }
}
