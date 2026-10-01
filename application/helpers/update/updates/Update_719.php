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
class Update_719 extends DatabaseUpdateBase
{
    /**
     * Alter TEXT columns to MEDIUMTEXT for Long free text (T) and Huge free text (U)
     * question types in existing MySQL/MariaDB response tables.
     *
     * All columns of a table are changed in a single ALTER TABLE statement so the
     * table is only rebuilt once. Columns that are not TEXT anymore are skipped, so
     * the update can be safely re-run if it failed half-way (MySQL DDL is not transactional).
     * Any failure is thrown so the DB version is not raised and the update is retried.
     *
     * @return void
     * @throws \CDbException
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
            if (!preg_match('/responses_(\d+)$/', $sTableName, $matches)) {
                continue;
            }
            $surveyId = (int) $matches[1];

            // Find Long free text (T) and Huge free text (U) questions for this survey.
            // Response column name format: Q{qid}
            $aQids = $this->db->createCommand()
                ->select('qid')
                ->from('{{questions}}')
                ->where(
                    'sid = :sid AND type IN (:typeT, :typeU) AND parent_qid = 0',
                    [
                        ':sid' => $surveyId,
                        ':typeT' => 'T',
                        ':typeU' => 'U',
                    ]
                )
                ->queryColumn();

            $aModifyClauses = [];
            foreach ($aQids as $qid) {
                $columnName = 'Q' . $qid;
                $oColumn = $oTableSchema->getColumn($columnName);
                if ($oColumn && in_array(strtolower((string) $oColumn->dbType), ['text', 'tinytext'])) {
                    $aModifyClauses[] = 'MODIFY ' . $this->db->quoteColumnName($columnName) . ' MEDIUMTEXT';
                }
            }
            if (!empty($aModifyClauses)) {
                $this->db->createCommand(
                    'ALTER TABLE ' . $this->db->quoteTableName($sTableName) . ' ' . implode(', ', $aModifyClauses)
                )->execute();
            }
        }
    }
}
