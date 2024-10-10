<?php

namespace LimeSurvey\Helpers\Update;

use Exception;

class Update_363 extends DatabaseUpdateBase
{
    public function up()
    {
        $aTableNames = dbGetTablesLike("tokens%");
        foreach ($aTableNames as $sTableName) {
            try {
                setTransactionBookmark();
                switch ($this->db->driverName) {
                    case 'mysql':
                    case 'mysqli':
                        $this->db->createCommand()->createIndex('idx_email', $sTableName, 'email(30)', false);
                        break;
                    case 'pgsql':
                        $this->db->createCommand()->createIndex(
                            'idx_email_' . substr((string) $sTableName, 7) . '_' . rand(1, 50000),
                            $sTableName,
                            'email',
                            false
                        );
                        break;
                        // MSSQL does not support indexes on text fields so no dice
                }
            } catch (\Exception $e) {
                rollBackToTransactionBookmark();
            }
        }
    }
}
