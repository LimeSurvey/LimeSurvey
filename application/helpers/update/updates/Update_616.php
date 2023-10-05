<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_616 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()->dropIndex('{{answers_idx}}', '{{answers}}');
    }
}
