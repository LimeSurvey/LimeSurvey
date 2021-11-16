<?php

namespace LimeSurvey\Helpers\Update;

class Update_433 extends DatabaseUpdateBase
{
    public function run()
    {
            $aTableNames = dbGetTablesLike("tokens%");
            $this->db = Yii::app()->getDb();
        foreach ($aTableNames as $sTableName) {
            try {
                setTransactionBookmark();
                switch (Yii::app()->db->driverName) {
                    case 'mysql':
                    case 'mysqli':
                        try {
                            setTransactionBookmark();
                            $this->db->createCommand()->createIndex('idx_email', $sTableName, 'email(30)', false);
                        } catch (Exception $e) {
                            rollBackToTransactionBookmark();
                        }
                        break;
                    case 'pgsql':
                        try {
                            setTransactionBookmark();
                            $this->db->createCommand()->createIndex('idx_email', $sTableName, 'email', false);
                        } catch (Exception $e) {
                            rollBackToTransactionBookmark();
                        }
                        break;
                    // MSSQL does not support indexes on text fields so no dice
                }
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            }
        }
    }
}
