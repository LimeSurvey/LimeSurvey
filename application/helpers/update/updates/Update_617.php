<?php

namespace LimeSurvey\Helpers\Update;

use LimeSurvey\Helpers\Update\DatabaseUpdateBase;

class Update_617 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()->dropIndex('{{answers_idx}}', '{{answers}}');
    }
}
