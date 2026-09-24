<?php

namespace LimeSurvey\Helpers\Update;

class Update_317 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {

            transferPasswordFieldToText($this->db);
    }
}
