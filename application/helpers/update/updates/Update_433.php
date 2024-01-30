<?php

namespace LimeSurvey\Helpers\Update;

use Exception;

class Update_433 extends DatabaseUpdateBase
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
                        try {
                            setTransactionBookmark();
                            $this->db->createCommand()->createIndex('idx_email', $sTableName, 'email(30)', false);
                        } catch (\Exception $e) {
                            rollBackToTransactionBookmark();
                        }
                        break;
                    case 'pgsql':
                        try {
                            setTransactionBookmark();
                            $this->db->createCommand()->createIndex('idx_email', $sTableName, 'email', false);
                        } catch (\Exception $e) {
                            rollBackToTransactionBookmark();
                        }
                        break;
                        // MSSQL does not support indexes on text fields so no dice
                }
            } catch (\Exception $e) {
                rollBackToTransactionBookmark();
            }
        }
    }
}
