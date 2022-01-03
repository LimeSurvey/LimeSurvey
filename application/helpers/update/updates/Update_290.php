<?php

namespace LimeSurvey\Helpers\Update;

class Update_290 extends DatabaseUpdateBase
{
    public function up()
    {
            $aTables = dbGetTablesLike("survey\_%");
            $oSchema = \Yii::app()->db->schema;
        foreach ($aTables as $sTableName) {
            $oTableSchema = $oSchema->getTable($sTableName);
            // Only update the table if it really is a survey response table - there are other tables that start the same
            if (!in_array('lastpage', $oTableSchema->columnNames)) {
                continue;
            }
            //If seed already exists, due to whatsoever
            if (in_array('seed', $oTableSchema->columnNames)) {
                continue;
            }
            removeMysqlZeroDate($sTableName, $oTableSchema, $this->db);
            // If survey has active table, create seed column
            \Yii::app()->db->createCommand()->addColumn($sTableName, 'seed', 'string(31)');

            // RAND is RANDOM in Postgres
            switch (\Yii::app()->db->driverName) {
                case 'pgsql':
                    \Yii::app()->db->createCommand(
                        "UPDATE {$sTableName} SET seed = ROUND(RANDOM() * 10000000)"
                    )->execute();
                    break;
                default:
                    \Yii::app()->db->createCommand(
                        "UPDATE {$sTableName} SET seed = ROUND(RAND() * 10000000, 0)"
                    )->execute();
                    break;
            }
        }
    }
}
