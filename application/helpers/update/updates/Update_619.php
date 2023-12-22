<?php

namespace LimeSurvey\Helpers\Update;

use LimeSurvey\Helpers\Update\DatabaseUpdateBase;

class Update_619 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        switch ($this->db->driverName)
        {
            case 'mssql':
              \addColumn('{{users}}', 'user_status', 'BIT DEFAULT 1 NOT NULL');
              break;
            default:
            \addColumn('{{users}}', 'user_status', 'BOOLEAN DEFAULT TRUE');
        }
        //$this->db->createCommand()->addColumn('{{users}}', 'user_status', 'BOOLEAN DEFAULT TRUE');
    }
}
