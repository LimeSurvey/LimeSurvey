<?php

namespace LimeSurvey\Helpers\Update;

class Update_440 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            $this->db->createCommand()->createIndex('sess_expire', '{{sessions}}', 'expire');
    }
}
