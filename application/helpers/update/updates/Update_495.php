<?php

namespace LimeSurvey\Helpers\Update;

class Update_495 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
        $this->db->createCommand()->addColumn('{{users}}', 'expires', 'datetime');
    }
}
