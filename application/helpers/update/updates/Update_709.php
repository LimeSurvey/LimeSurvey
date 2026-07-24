<?php

namespace LimeSurvey\Helpers\Update;

use CException;
use Yii;

class Update_709 extends DatabaseUpdateBase
{
    /**
     * Alters MySQL table by adding a new JSON field, updating it with values and removing the original fields
     * @param int $sid
     * @param int $parent_qid
     * @param array $cols
     * @return void
     */
    protected function alterMySQL(int $sid, int $parent_qid, array $cols)
    {
        addColumn("{{responses_" . $sid . "}}", "Q{$parent_qid}", "JSON");
        $alterElements = [];
        foreach ($cols as $col) {
            $alterElements[] = (!count($alterElements) ?
            "CASE WHEN LENGTH(" . $this->db->quoteColumnName($col) . ") > 0 THEN CONCAT('\"', " . $this->db->quoteColumnName($col) . ", '\"') ELSE '' END," :
            "CASE WHEN LENGTH(" . $this->db->quoteColumnName($col) . ") > 0 THEN CONCAT(',\"', " . $this->db->quoteColumnName($col) . ", '\"') ELSE '' END,");
        }
        $updateCommand = "UPDATE {{responses_" . $sid . "}} SET Q{$parent_qid} = CONCAT('[', " . implode($alterElements) . " ']')";
        $this->db->createCommand($updateCommand)->execute();
        foreach ($cols as $col) {
            dropColumn("{{responses_" . $sid . "}}", $col);
        }
    }

    /**
     * Alters SQL Server table by adding a new JSON field, updating it with values and removing the original fields
     * @param int $sid
     * @param int $parent_qid
     * @param array $cols
     * @return void
     */
    protected function alterSQLServer(int $sid, int $parent_qid, array $cols)
    {
        addColumn("{{responses_" . $sid . "}}", "Q{$parent_qid}", "json");
        $alterElements = [];
        foreach ($cols as $col) {
            $alterElements[] = (!count($alterElements) ?
            "CASE WHEN LEN(" . $this->db->quoteColumnName($col) . ") > 0 THEN CONCAT('\"', " . $this->db->quoteColumnName($col) . ", '\"') ELSE '' END," :
            "CASE WHEN LEN(" . $this->db->quoteColumnName($col) . ") > 0 THEN CONCAT(',\"', " . $this->db->quoteColumnName($col) . ", '\"') ELSE '' END,");
        }
        $updateCommand = "UPDATE {{responses_" . $sid . "}} SET Q{$parent_qid} = CONCAT('[', " . implode($alterElements) . " ']')";
        $this->db->createCommand($updateCommand)->execute();
        foreach ($cols as $col) {
            dropColumn("{{responses_" . $sid . "}}", $col);
        }
    }

    /**
     * Alters PostgreSQL table by adding a new JSON field, updating it with values and removing the original fields
     * @param int $sid
     * @param int $parent_qid
     * @param array $cols
     * @return void
     */
    protected function alterPostgreSQL(int $sid, int $parent_qid, array $cols)
    {
        $newColumn = $this->db->quoteColumnName("Q{$parent_qid}");
        $this->db->createCommand("alter table {{responses_" . $sid . "}} add column {$newColumn} json")->execute();

        $alterElements = [];
        foreach ($cols as $col) {
            $alterElements[] = (!count($alterElements) ?
            "CASE WHEN LENGTH(" . $this->db->quoteColumnName($col) . ") > 0 THEN CONCAT('\"', " . $this->db->quoteColumnName($col) . ", '\"') ELSE '' END," :
            "CASE WHEN LENGTH(" . $this->db->quoteColumnName($col) . ") > 0 THEN CONCAT(',\"', " . $this->db->quoteColumnName($col) . ", '\"') ELSE '' END,");
        }

        $updateCommand = "UPDATE {{responses_{$sid}}} SET {$newColumn} = (CONCAT('[', " . implode($alterElements) . " ']')::json)";
        $this->db->createCommand($updateCommand)->execute();
        foreach ($cols as $col) {
            dropColumn("{{responses_" . $sid . "}}", $col);
        }
    }

    /**
     * Alters table by adding a new JSON field, updating it with values and removing the original fields
     * @param int $sid
     * @param int $parent_qid
     * @param array $cols
     * @return void
     */
    protected function alter(int $sid, int $parent_qid, array $cols)
    {
        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
                $this->alterMySQL($sid, $parent_qid, $cols);
                break;
            case 'pgsql':
                $this->alterPostgreSQL($sid, $parent_qid, $cols);
                break;
            case 'mssql':
            case 'sqlsrv':
            case 'dblib':
                $this->alterSQLServer($sid, $parent_qid, $cols);
                break;
        }
    }

    /**
     * Updates MySQL sub-questions of ranking questions from type 'T' to type 'R'
     *
     * @param string $typeCol
     * @param string $rankingKey
     * @return void
     */
    protected function updateRankingSubQuestionTypesMySQL(string $typeCol, string $rankingKey)
    {
        $this->db->createCommand("
            UPDATE {{questions}} sub
            JOIN {{questions}} parent ON sub.parent_qid = parent.qid
            SET sub.{$typeCol} = 'R'
            WHERE parent.{$typeCol} = '{$rankingKey}'
              AND parent.parent_qid = 0
        ")->execute();
    }

    /**
     * Updates PostgreSQL sub-questions of ranking questions from type 'T' to type 'R'
     *
     * @param string $typeCol
     * @param string $rankingKey
     * @return void
     */
    protected function updateRankingSubQuestionTypesPostgreSQL(string $typeCol, string $rankingKey)
    {
        $table = $this->db->quoteTableName('{{questions}}');

        $this->db->createCommand("
            UPDATE {$table} sub
            SET {$typeCol} = 'R'
            FROM {$table} parent
            WHERE sub.parent_qid = parent.qid
              AND parent.{$typeCol} = '{$rankingKey}'
              AND parent.parent_qid = 0
        ")->execute();
    }

    /**
     * Updates SQLServer sub-questions of ranking questions from type 'T' to type 'R'
     *
     * @param string $typeCol
     * @param string $rankingKey
     * @return void
     */
    protected function updateRankingSubQuestionTypesSQLServer(string $typeCol, string $rankingKey)
    {
        $table = $this->db->quoteTableName('{{questions}}');

        $this->db->createCommand("
            UPDATE sub
            SET sub.{$typeCol} = 'R'
            FROM {$table} sub
            JOIN {$table} parent ON sub.parent_qid = parent.qid
            WHERE parent.{$typeCol} = '{$rankingKey}'
              AND parent.parent_qid = 0
        ")->execute();
    }

    /**
     * Updates sub-questions of ranking questions from type 'T' to type 'R'.
     *
     * @return void
     */
    protected function updateRankingSubQuestionTypes()
    {
        $typeCol = $this->db->quoteColumnName('type');
        $rankingKey = 'R';

        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
                $this->updateRankingSubQuestionTypesMySQL($typeCol, $rankingKey);
                break;
            case 'pgsql':
                $this->updateRankingSubQuestionTypesPostgreSQL($typeCol, $rankingKey);
                break;
            case 'mssql':
            case 'sqlsrv':
            case 'dblib':
                $this->updateRankingSubQuestionTypesSQLServer($typeCol, $rankingKey);
                break;
        }
    }

    /**
     * Collects the table column names using Yii's database-agnostic schema method
     * Works with MySQL, PostgreSQL, SQL Server and any other Yii1-supported driver
     * @param string $table the table name
     * @return array array of column names
     */
    protected function findColumns(string $table): array
    {
        $tableSchema = $this->db->getSchema()->getTable($table);
        if ($tableSchema === null) {
            return [];
        }
        return array_keys($tableSchema->columns);
    }
    /**
     * Adjust ranking questions to be of JSON type
     *
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        $this->updateRankingSubQuestionTypes();

        $rankingKey = 'R';
        $rankingQuestionResult = $this->db->createCommand()
            ->select('s.sid AS sid, q1.qid AS parent_qid, q2.qid AS qid')
            ->from('{{surveys}} s')
            ->join('{{questions}} q1', 's.sid = q1.sid AND s.active = :active', [':active' => 'Y'])
            ->join('{{questions}} q2', 'q1.' . $this->db->quoteColumnName('type') . ' = :rankingKey AND q1.qid = q2.parent_qid', [':rankingKey' => $rankingKey])
            ->order('q1.sid, q1.qid, q2.question_order')
            ->query();
        $sid = null;
        $alterMap = [];
        $columns = null;
        foreach ($rankingQuestionResult as $rqr) {
            if ($rqr['sid'] !== $sid) {
                $sid = $rqr['sid'];
                $alterMap[$sid] = [];
                $columns = $this->findColumns((Yii::app()->db->tablePrefix ?? "") . "responses_{$sid}");
            }
            if (!isset($alterMap[$sid][$rqr['parent_qid']])) {
                $alterMap[$sid][$rqr['parent_qid']] = [];
            }
            if (in_array("Q{$rqr["parent_qid"]}_S{$rqr["qid"]}", $columns)) {
                $alterMap[$sid][$rqr['parent_qid']][] = "Q{$rqr["parent_qid"]}_S{$rqr["qid"]}";
            };
        }
        foreach ($alterMap as $sid => $changeSet) {
            foreach ($changeSet as $parent_qid => $cols) {
                if (count($cols)) {
                    $this->alter($sid, $parent_qid, $cols);
                }
            }
        }
    }
}
