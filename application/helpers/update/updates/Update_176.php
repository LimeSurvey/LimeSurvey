<?php

namespace LimeSurvey\Helpers\Update;

class Update_176 extends DatabaseUpdateBase
{
    public function up()
    {
            upgradeTokens176();
    }
}
