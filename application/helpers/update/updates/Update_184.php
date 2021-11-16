<?php

namespace LimeSurvey\Helpers\Update;

class Update_184 extends DatabaseUpdateBase
{
    public function up()
    {
            fixKCFinder184();
    }
}
