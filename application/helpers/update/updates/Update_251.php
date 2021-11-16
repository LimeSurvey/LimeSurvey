<?php

namespace LimeSurvey\Helpers\Update;

class Update_251 extends DatabaseUpdateBase
{
    public function run()
    {
            upgradeBoxesTable251();

            // Update DBVersion
    }
}
