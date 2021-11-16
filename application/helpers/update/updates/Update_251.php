<?php

namespace LimeSurvey\Helpers\Update;

class Update_251 extends DatabaseUpdateBase
{
    public function up()
    {
            upgradeBoxesTable251();

            // Update DBVersion
    }
}
