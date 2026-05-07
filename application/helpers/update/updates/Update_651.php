<?php

namespace LimeSurvey\Helpers\Update;

use CException;
use Yii;

class Update_651 extends DatabaseUpdateBase
{
    /**
     * Adds default 'type' and 'type_options' keys to participant attribute descriptions
     * for surveys that were created before AT-1771 (participant attribute types).
     *
     * @throws CException If a database update operation fails.
     */
    public function up()
    {
        if (in_array(Yii::app()->db->getDriverName(), ['mssql', 'sqlsrv', 'dblib'])) {
            $questionArchives = $this->db->createCommand(
                "
                    SELECT CONCAT('alter table ', TABLE_NAME, ' ADD CONSTRAINT pk_', TABLE_NAME, ' PRIMARY KEY (qid)') AS alter_command
                    FROM information_schema.tables
                    WHERE TABLE_CATALOG = db_name() AND TABLE_NAME LIKE '%old_questions%';
                "
            )->query();
            foreach ($questionArchives as $questionArchive) {
                $this->db->createCommand($questionArchive['alter_command'])->execute();
            }
        }
    }
}