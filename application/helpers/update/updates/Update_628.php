<?php

namespace LimeSurvey\Helpers\Update;

use LimeSurvey\Helpers\Update\DatabaseUpdateBase;

class Update_628 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()->addColumn('{{surveys}}', 'lastModified', 'TIMESTAMP ON UPDATE  CURRENT_TIMESTAMP');
    }
}
