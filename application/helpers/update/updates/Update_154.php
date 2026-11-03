<?php

namespace LimeSurvey\Helpers\Update;

class Update_154 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            $this->db->createCommand()->addColumn('{{groups}}', 'grelevance', "text");
    }
}
