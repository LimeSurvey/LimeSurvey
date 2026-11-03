<?php

namespace LimeSurvey\Helpers\Update;

class Update_250 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            createBoxes250();
    }
}
