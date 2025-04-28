<?php

namespace LimeSurvey\Helpers\Update;

use CException;
use Yii;

class Update_630 extends DatabaseUpdateBase
{
    protected $scriptMapping;

    public function doPreparations()
    {
        $scripts = [];
        switch (Yii::app()->db->getDriverName()) {
            case 'pgsql':
                $scripts[] =
<<<EOD
CREATE OR REPLACE FUNCTION show_create_table(table_name text, join_char text = E'\n' ) 
  RETURNS text AS 
\$BODY\$
SELECT 'CREATE TABLE ' || $1 || ' (' || $2 || '' || 
    string_agg(column_list.column_expr, ', ' || $2 || '') || 
    '' || $2 || ');'
FROM (
  SELECT '    "' || column_name || '" ' || data_type || 
       coalesce('(' || character_maximum_length || ')', '') || 
       case when is_nullable = 'YES' then '' else ' NOT NULL' end as column_expr
  FROM information_schema.columns
  WHERE table_schema = 'public' AND table_name = $1
  ORDER BY ordinal_position) column_list;
\$BODY\$
  LANGUAGE SQL STABLE
;
EOD
                ;
            break;
        }
        foreach ($scripts as $script) {
            $this->db->createCommand($script)->execute();
        }
    }

    public function getResponsesScript()
    {
        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
                return "
                SELECT TABLE_NAME AS old_name, REPLACE(TABLE_NAME, 'survey', 'responses') AS new_name
                FROM information_schema.tables
                WHERE TABLE_SCHEMA = DATABASE() AND
                      TABLE_NAME REGEXP '^.*survey_[0-9]*(_[0-9]*)?$'
                ";
            case 'pgsql':
                return "
                SELECT TABLE_NAME AS old_name, REPLACE(TABLE_NAME, 'survey', 'responses') AS new_name
                FROM information_schema.tables
                WHERE TABLE_CATALOG = current_database() AND
                      REGEXP_COUNT(TABLE_NAME, '^.*survey_[0-9]*(_[0-9]*)?$') > 0;
                ";
            default: return "";
        }
    }

    public function getTimingScript()
    {
        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
                return "
                SELECT TABLE_NAME AS old_name, REPLACE(REPLACE(TABLE_NAME, '_timings', ''), 'survey', 'timings') AS new_name
                FROM information_schema.tables
                WHERE TABLE_SCHEMA = DATABASE() AND
                      TABLE_NAME LIKE '%timings%';
                ";
            case 'pgsql':
                return "
                SELECT TABLE_NAME AS old_name, REPLACE(REPLACE(TABLE_NAME, '_timings', ''), 'survey', 'timings') AS new_name
                FROM information_schema.tables
                WHERE TABLE_CATALOG = current_database() AND
                      TABLE_NAME LIKE '%timings%';
                ";
            default: return "";
        }
    }

    public function getFieldsScript()
    {
        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
                return "
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
                ";
            case 'pgsql':
                return "
                SELECT TABLE_NAME, COLUMN_NAME
                FROM information_schema.columns
                WHERE TABLE_CATALOG = current_database() AND (
                      (
                          REGEXP_COUNT(COLUMN_NAME, '^[0-9]*X[0-9]*X[0-9]*(.*)$') > 0 AND
                          (TABLE_NAME LIKE '%survey%')
                      ) OR
                      (
                          REGEXP_COUNT(COLUMN_NAME, '^[0-9]*X[0-9]*(X[0-9]*)?(.*)$') > 0 AND
                          (TABLE_NAME LIKE '%survey%')
                      )
                )
                ORDER BY TABLE_NAME, COLUMN_NAME;
                ";
            default: return "";
        }
    }

    public function showCreateTable(string $tableName) {
        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
                return "SHOW CREATE TABLE " . $tableName;
            case 'pgsql':
                return "SELECT show_create_table('" . $tableName . "') AS \"Create Table\"";
        }
    }

    public function adjustShowCreateTable(array $script, string $tableName)
    {
        if (strpos($tableName, "old") === false) {
            switch (Yii::app()->db->getDriverName()) {
                case 'pgsql':
                    $script["Create Table"] = str_replace("\"id\" integer NOT NULL", "\"id\" serial PRIMARY KEY", $script["Create Table"]);
                break;
            }
        }
        return $script;
    }

    public function getFieldsFromTableScript($tableName)
    {
        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
                return "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = database() AND TABLE_NAME = '{$tableName}'";
            case 'pgsql':
                return "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_CATALOG = current_database() AND TABLE_NAME = '{$tableName}'";
            default: return "";
        }
    }

    /** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
    public function up()
    {
        $leftSeparator = $rigtSeparator = "`";
        if (Yii::app()->db->getDriverName() === 'pgsql') {
            $leftSeparator = $rightSeparator = '"';
        }
        $this->doPreparations();
        $this->scriptMapping = [
            'responses' => $this->getResponsesScript(),
            'timings' => $this->getTimingScript(),
            'fields' => $this->getFieldsScript()
        ];
        $scripts = [];
        $responsesTables = $this->db->createCommand($this->scriptMapping['responses'])->queryAll();
        foreach ($responsesTables as $responsesTable) {
            $scripts[$responsesTable['old_name']] = [
                'new_name' => $responsesTable['new_name'],
                'old_name' => $responsesTable['old_name']
            ];
            $createTable = $this->adjustShowCreateTable($this->db->createCommand($this->showCreateTable($responsesTable['old_name']))->queryRow(), $responsesTable['old_name']);
            $scripts[$responsesTable['old_name']]['CREATE'] = $createTable["Create Table"];
            $scripts[$responsesTable['old_name']]['DROP'] = "DROP TABLE {$responsesTable['old_name']}";
            $scripts[$responsesTable['old_name']]['columns'] = $this->db->createCommand($this->getFieldsFromTableScript($responsesTable['old_name']))->queryAll();
        }
        $timingsTables = $this->db->createCommand($this->scriptMapping['timings'])->queryAll();
        foreach ($timingsTables as $timingsTable) {
            $scripts[$timingsTable['old_name']] = [
                'new_name' => $timingsTable['new_name'],
                'old_name' => $timingsTable['old_name']
            ];
            $createTable = $this->adjustShowCreateTable($this->db->createCommand($this->showCreateTable($timingsTable['old_name']))->queryRow(), $responsesTable['old_name']);
            $scripts[$timingsTable['old_name']]['CREATE'] = $createTable["Create Table"];
            $scripts[$timingsTable['old_name']]['DROP'] = "DROP TABLE {$timingsTable['old_name']}";
            $scripts[$timingsTable['old_name']]['columns'] = $this->db->createCommand($this->getFieldsFromTableScript($timingsTable['old_name']))->queryAll();
        }
        $fields = $this->db->createCommand($this->scriptMapping['fields'])->queryAll();
        $fieldMap = [];
        foreach ($fields as $field) {
            if (!isset($field['TABLE_NAME'])) {
                if (isset($field['table_name'])) {
                    $field['TABLE_NAME'] = $field['table_name'];
                }
            }
            if (!isset($field['COLUMN_NAME'])) {
                if (isset($field['column_name'])) {
                    $field['COLUMN_NAME'] = $field['column_name'];
                }
            }
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
                $fieldMap[$tableName][$fieldName] = getFieldName($tableName, $fieldName, $questions, (int)$sid, (int)$gid);
            }
        }
        foreach ($fieldMap as $TABLE_NAME => $fields) {
            $scripts[$TABLE_NAME]['CREATE'] = str_replace("{$TABLE_NAME}", "{$scripts[$TABLE_NAME]['new_name']}", $scripts[$TABLE_NAME]['CREATE']);
            foreach ($fields as $oldField => $newField) {
                $scripts[$TABLE_NAME]['CREATE'] = str_replace("{$oldField}", "{$newField}", $scripts[$TABLE_NAME]['CREATE']);
            }
            $fromColumns = [];
            $toColumns = [];
            foreach ($scripts[$TABLE_NAME]['columns'] as $column) {
                if (!isset($column['COLUMN_NAME'])) {
                    if (isset($column['column_name'])) {
                        $column['COLUMN_NAME'] = $column['column_name'];
                    }
                }
                $fromColumns [] = $leftSeparator . $column['COLUMN_NAME'] . $rightSeparator;
                if (isset($fieldMap[$TABLE_NAME][$column['COLUMN_NAME']])) {
                    $toColumns [] = $leftSeparator . $fieldMap[$TABLE_NAME][$column['COLUMN_NAME']] . $rightSeparator;
                } else {
                    $toColumns [] = $leftSeparator . $column['COLUMN_NAME'] . $rightSeparator;
                }
            }
            $from = implode(",", $fromColumns);
            $to = implode(",", $toColumns);
            $scripts[$TABLE_NAME]['INSERT'] = "
                INSERT INTO {$scripts[$TABLE_NAME]['new_name']}({$to})
                SELECT {$from}
                FROM {$TABLE_NAME}
            ";
            $this->db->createCommand($scripts[$TABLE_NAME]['CREATE'])->execute();
            $this->db->createCommand($scripts[$TABLE_NAME]['INSERT'])->execute();
            $this->db->createCommand($scripts[$TABLE_NAME]['DROP'])->execute();
        }
    }
}
