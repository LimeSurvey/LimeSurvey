<?php

namespace LimeSurvey\Helpers\Update;

class Update_414 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            $this->db->createCommand()->addColumn('{{users}}', 'lastLogin', "datetime NULL");
    }
}
