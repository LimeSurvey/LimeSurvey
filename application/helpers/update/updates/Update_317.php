<?php

namespace LimeSurvey\Helpers\Update;

class Update_317 extends DatabaseUpdateBase
{
    public function run()
    {

            transferPasswordFieldToText($oDB);

    }
}