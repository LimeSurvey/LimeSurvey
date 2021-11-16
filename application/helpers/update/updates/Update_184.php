<?php

namespace LimeSurvey\Helpers\Update;

class Update_184 extends DatabaseUpdateBase
{
    public function run()
    {
            fixKCFinder184();
    }
}
