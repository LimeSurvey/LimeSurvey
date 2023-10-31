<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_618 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        $this->db->createCommand()->addColumn('{{users}}', 'status', 'BOOLEAN DEFAULT TRUE');
    }
}
