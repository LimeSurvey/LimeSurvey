<?php

namespace LimeSurvey\Helpers\Update;

class Update_251 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            upgradeBoxesTable251();

            // Update DBVersion
    }
}
