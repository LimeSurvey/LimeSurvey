<?php

namespace LimeSurvey\Helpers\Update;

class Update_337 extends DatabaseUpdateBase
{
    public function run()
    {
            resetTutorials337($oDB);
    }
}
