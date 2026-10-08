<?php

namespace LimeSurvey\Helpers\Update;

class Update_184 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            fixKCFinder184();
    }
}
