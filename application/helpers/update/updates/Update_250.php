<?php

namespace LimeSurvey\Helpers\Update;

class Update_250 extends DatabaseUpdateBase
{
    public function run()
    {
            createBoxes250();
    }
}