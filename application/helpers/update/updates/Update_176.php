<?php

namespace LimeSurvey\Helpers\Update;

class Update_176 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            upgradeTokens176();
    }
}
