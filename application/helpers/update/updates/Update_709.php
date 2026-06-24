<?php

namespace LimeSurvey\Helpers\Update;

use CException;
use Question;
use SurveyDynamic;
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
     * Alters MySQL table by adding a new JSON field, updating it with values and removing the original fields
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
                //TODO: Implement PostgreSQL equivalent
                break;
            case 'mssql':
            case 'sqlsrv':
            case 'dblib':
                $this->alterSQLServer($sid, $parent_qid, $cols);
                break;
        }
    }

    /**
     * Adjust ranking questions to be of JSON type
     *
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        $rankingKey = Question::QT_R_RANKING;
        $rankingQuestionQuery = "
            SELECT s.sid AS sid, q1.qid AS parent_qid, q2.qid AS qid
            FROM {{surveys}} s
            JOIN {{questions}} q1
            ON s.sid = q1.sid AND s.active = 'Y'
            JOIN {{questions}} q2
            ON q1." . $this->db->quoteColumnName("type") . " = '{$rankingKey}' AND q1.qid = q2.parent_qid
            ORDER BY q1.sid, q1.qid, q2.question_order
        ";
        $rankingQuestionResult = $this->db->createCommand($rankingQuestionQuery)->query();
        $sid = null;
        $alterMap = [];
        $model = null;
        $columns = null;
        foreach ($rankingQuestionResult as $rqr) {
            if ($rqr['sid'] !== $sid) {
                $sid = $rqr['sid'];
                $alterMap[$sid] = [];
                $model = SurveyDynamic::model($sid);
                $columns = $model->metaData->columns;
            }
            if (!isset($alterMap[$sid][$rqr['parent_qid']])) {
                $alterMap[$sid][$rqr['parent_qid']] = [];
            }
            if (isset($columns["Q{$rqr["parent_qid"]}_S{$rqr["qid"]}"])) {
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
