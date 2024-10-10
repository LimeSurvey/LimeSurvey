<?php

namespace LimeSurvey\Helpers\Update;

use Exception;

class Update_171 extends DatabaseUpdateBase
{
    public function up()
    {
        try {
            dropColumn('{{sessions}}', 'data');
        } catch (\Exception $e) {
        }
        switch ($this->db->driverName) {
            case 'mysql':
                addColumn('{{sessions}}', 'data', 'longbinary');
                break;
            case 'sqlsrv':
            case 'dblib':
            case 'mssql':
                addColumn('{{sessions}}', 'data', 'VARBINARY(MAX)');
                break;
            case 'pgsql':
                addColumn('{{sessions}}', 'data', 'BYTEA');
                break;
        }
    }
}
