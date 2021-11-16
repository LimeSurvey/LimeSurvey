<?php

namespace LimeSurvey\Helpers\Update;

class Update_363 extends DatabaseUpdateBase
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
                        $this->db->createCommand()->createIndex('idx_email', $sTableName, 'email(30)', false);
                        break;
                    case 'pgsql':
                        $this->db->createCommand()->createIndex(
                            'idx_email_' . substr($sTableName, 7) . '_' . rand(1, 50000),
                            $sTableName,
                            'email',
                            false
                        );
                        break;
                    // MSSQL does not support indexes on text fields so no dice
                }
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            }
        }
    }
}
