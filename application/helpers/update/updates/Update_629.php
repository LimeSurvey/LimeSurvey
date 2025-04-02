<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_629 extends DatabaseUpdateBase
{
    protected $scriptMapping = [
        'responses' => "
            SELECT TABLE_NAME AS old_name, REPLACE(TABLE_NAME, 'survey', 'responses') AS new_name
            FROM information_schema.tables
            WHERE TABLE_SCHEMA = DATABASE() AND
                  TABLE_NAME REGEXP '^.*survey_[0-9]*(_[0-9]*)?$'
        ",
        'timings' => "
            SELECT TABLE_NAME AS old_name, REPLACE(REPLACE(TABLE_NAME, '_timings', ''), 'survey', 'timings') AS new_name
            FROM information_schema.tables
            WHERE TABLE_SCHEMA = DATABASE() AND
                  TABLE_NAME LIKE '%timings%';
        ",
        'fields' => "
            SELECT TABLE_NAME, COLUMN_NAME
            FROM information_schema.columns
            WHERE TABLE_SCHEMA = DATABASE() AND (
                  (
                      COLUMN_NAME REGEXP '^[0-9]*X[0-9]*X[0-9]*(.*)$' AND
                      (TABLE_NAME LIKE '%survey%')
                  ) OR
                  (
                      COLUMN_NAME REGEXP '^[0-9]*X[0-9]*(X[0-9]*)?(.*)$' AND
                      (TABLE_NAME LIKE '%survey%')
                  )
            )
            ORDER BY TABLE_NAME, COLUMN_NAME
        ",
    ];

    /** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
    public function up()
    {
        $scripts = [];
        $responsesTables = $this->db->createCommand($this->scriptMapping['responses'])->queryAll();
        foreach ($responsesTables as $responsesTable) {
            $scripts[$responsesTable['old_name']] = [
                'new_name' => $responsesTable['new_name'],
                'old_name' => $responsesTable['old_name']
            ];
            $createTable = $this->db->createCommand("SHOW CREATE TABLE {$responsesTable['old_name']}")->queryRow();
            $scripts[$responsesTable['old_name']]['CREATE'] = $createTable["Create Table"];
            $scripts[$responsesTable['old_name']]['DROP'] = "DROP TABLE {$responsesTable['old_name']}";
            $scripts[$responsesTable['old_name']]['columns'] = $this->db->createCommand("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = database() AND TABLE_NAME = '{$responsesTable['old_name']}'")->queryAll();
        }
        $timingsTables = $this->db->createCommand($this->scriptMapping['timings'])->queryAll();
        foreach ($timingsTables as $timingsTable) {
            $scripts[$timingsTable['old_name']] = [
                'new_name' => $timingsTable['new_name'],
                'old_name' => $timingsTable['old_name']
            ];
            $createTable = $this->db->createCommand("SHOW CREATE TABLE {$timingsTable['old_name']}")->queryRow();
            $scripts[$timingsTable['old_name']]['CREATE'] = $createTable["Create Table"];
            $scripts[$timingsTable['old_name']]['DROP'] = "DROP TABLE {$timingsTable['old_name']}";
            $scripts[$timingsTable['old_name']]['columns'] = $this->db->createCommand("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = database() AND TABLE_NAME = '{$timingsTable['old_name']}'")->queryAll();
        }
        $fields = $this->db->createCommand($this->scriptMapping['fields'])->queryAll();
        $fieldMap = [];
        foreach ($fields as $field) {
            $tableName = $field['TABLE_NAME'];
            if (!isset($fieldMap[$field['TABLE_NAME']])) {
                $fieldMap[$field['TABLE_NAME']] = [];
            }
            $fieldName = $field['COLUMN_NAME'];
            $split = explode("X", $fieldName);
            $sid = $split[0];
            $gid = $split[1];
            $qids = [];
            $position = 0;
            if (count($split) > 2) {
                while (($position < strlen($split[2])) && ctype_digit($split[2][$position])) {
                    $qids [] = (count($qids) ? ($qids[count($qids) - 1] . $split[2][$position]) : $split[2][$position]);
                    $position++;
                }
                $commaSeparatedQIDs = implode(",", $qids);
                $questions = \Question::model()->findAll([
                    'condition' => "sid = {$sid} and gid = {$gid} and (qid in ({$commaSeparatedQIDs}) or parent_qid in ({$commaSeparatedQIDs}))"
                ]);
            }
            if (count($questions) || ((strpos($tableName, "timings") !== false) && ($split > 1))) {
                $fieldMap[$tableName][$fieldName] = getFieldName($tableName, $fieldName, $questions, $sid, $gid);
            }
        }
        foreach ($fieldMap as $TABLE_NAME => $fields) {
            $scripts[$TABLE_NAME]['CREATE'] = str_replace("`{$TABLE_NAME}`", "`{$scripts[$TABLE_NAME]['new_name']}`", $scripts[$TABLE_NAME]['CREATE']);
            foreach ($fields as $oldField => $newField) {
                $scripts[$TABLE_NAME]['CREATE'] = str_replace("`{$oldField}`", "`{$newField}`", $scripts[$TABLE_NAME]['CREATE']);
            }
            $fromColumns = [];
            $toColumns = [];
            foreach ($scripts[$TABLE_NAME]['columns'] as $column) {
                $fromColumns [] = "`" . $column['COLUMN_NAME'] . "`";
                if (isset($fieldMap[$TABLE_NAME][$column['COLUMN_NAME']])) {
                    $toColumns [] = "`" . $fieldMap[$TABLE_NAME][$column['COLUMN_NAME']] . "`";
                } else {
                    $toColumns [] = "`" . $column['COLUMN_NAME'] . "`";
                }
            }
            $from = implode(",", $fromColumns);
            $to = implode(",", $toColumns);
            $scripts[$TABLE_NAME]['INSERT'] = "
                INSERT INTO `{$scripts[$TABLE_NAME]['new_name']}`({$to})
                SELECT {$from}
                FROM `{$TABLE_NAME}`
            ";
            $this->db->createCommand($scripts[$TABLE_NAME]['CREATE'])->execute();
            $this->db->createCommand($scripts[$TABLE_NAME]['INSERT'])->execute();
            $this->db->createCommand($scripts[$TABLE_NAME]['DROP'])->execute();
        }
    }
}
