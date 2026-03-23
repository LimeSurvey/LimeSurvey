<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Update response table columns for Long free text (T) and Huge free text (U)
 * question types from TEXT to MEDIUMTEXT on MySQL/MariaDB.
 *
 * TEXT in MySQL is limited to 64KB which is too small for long text responses.
 * MEDIUMTEXT allows up to 16MB.
 *
 * @see https://bugs.limesurvey.org/view.php?id=18275
 */
class Update_702 extends DatabaseUpdateBase
{
    /**
     * Alter TEXT columns to MEDIUMTEXT for Long free text (T) and Huge free text (U)
     * question types in existing MySQL/MariaDB response tables.
     *
     * @return void
     */
    public function up()
    {
        // Only MySQL/MariaDB needs this change.
        // PostgreSQL TEXT is unlimited, MSSQL nvarchar(max) is 2GB.
        if ($this->db->driverName != 'mysql') {
            return;
        }

        $aTables = \dbGetTablesLike("responses\_%");
        $oSchema = $this->db->schema;

        foreach ($aTables as $sTableName) {
            $oTableSchema = $oSchema->getTable($sTableName);
            // Only update the table if it really is a survey response table
            if (!$oTableSchema || !in_array('lastpage', $oTableSchema->columnNames)) {
                continue;
            }

            // Extract survey ID from table name (e.g. lime_responses_123456)
            if (!preg_match('/responses_(\d+)/', $sTableName, $matches)) {
                continue;
            }
            $surveyId = (int) $matches[1];

            // Find Long free text (T) and Huge free text (U) questions for this survey.
            // Field name format: {sid}X{gid}X{qid}
            $rows = $this->db->createCommand()
                ->select('q.qid, q.gid')
                ->from('{{questions}} q')
                ->where(
                    'q.sid = :sid AND q.type IN (:typeT, :typeU) AND q.parent_qid = 0',
                    [
                        ':sid' => $surveyId,
                        ':typeT' => 'T',
                        ':typeU' => 'U',
                    ]
                )
                ->queryAll();

            foreach ($rows as $row) {
                $columnName = $surveyId . 'X' . $row['gid'] . 'X' . $row['qid'];
                if (in_array($columnName, $oTableSchema->columnNames)) {
                    try {
                        \alterColumn($sTableName, $columnName, 'mediumtext');
                    } catch (\Exception $e) {
                        \Yii::log(
                            "Update_702: Failed to alter column '$columnName' in table '$sTableName': " . $e->getMessage(),
                            'error',
                            'application.db.upgrade'
                        );
                    }
                }
            }
        }
    }
}
