<?php

namespace LimeSurvey\Helpers\Update;

use CException;
use Yii;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Update_631 extends DatabaseUpdateBase
{
    protected $scriptMapping;

    public function doPreparations()
    {
        $scripts = [];
        switch (Yii::app()->db->getDriverName()) {
            case 'pgsql':
                $scripts[] =
                "
                CREATE OR REPLACE FUNCTION show_create_table(table_name text, join_char text = E'\n' ) 
                  RETURNS text AS 
                \$BODY\$
                SELECT 'CREATE TABLE ' || $1 || ' (' || $2 || '' || 
                    string_agg(column_list.column_expr, ', ' || $2 || '') || 
                    '' || $2 || ');'
                FROM (
                  SELECT '    \"' || column_name || '\" ' || data_type || 
                       coalesce('(' || character_maximum_length || ')', '') || 
                       case when is_nullable = 'YES' then '' else ' NOT NULL' end as column_expr
                  FROM information_schema.columns
                  WHERE table_schema = 'public' AND table_name = $1
                  ORDER BY ordinal_position) column_list;
                \$BODY\$
                  LANGUAGE SQL STABLE
                ;
                "
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
            case 'mssql':
            case 'sqlsrv':
                return "
                SELECT TABLE_NAME AS old_name, REPLACE(TABLE_NAME, 'survey', 'responses') AS new_name
                FROM information_schema.tables
                WHERE TABLE_CATALOG = db_name() AND
                      TABLE_NAME LIKE '%survey_%' AND
                      TABLE_NAME NOT LIKE '%timings%' AND
                      RIGHT(TABLE_NAME, 1) NOT IN ('s', 'u');
                ";
            default:
                return "";
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
            case 'mssql':
            case 'sqlsrv':
                return "
                SELECT TABLE_NAME AS old_name, REPLACE(REPLACE(TABLE_NAME, '_timings', ''), 'survey', 'timings') AS new_name
                FROM information_schema.tables
                WHERE TABLE_CATALOG = db_name() AND
                      TABLE_NAME LIKE '%timings%';
                ";
            default:
                return "";
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
            case 'mssql':
            case 'sqlsrv':
                return "
                SELECT TABLE_NAME, COLUMN_NAME
                FROM information_schema.columns
                WHERE TABLE_CATALOG = db_name() AND (
                      (
					      TABLE_NAME LIKE '%survey_[0-9]%' AND
                          COLUMN_NAME LIKE '%X%'
                      )
                )
                ORDER BY TABLE_NAME, COLUMN_NAME;
                ";
            default:
                return "";
        }
    }

    public function showCreateTable(string $tableName)
    {
        switch (Yii::app()->db->getDriverName()) {
            case 'mysqli':
            case 'mysql':
                return "SHOW CREATE TABLE " . $tableName;
            case 'pgsql':
                return "SELECT show_create_table('" . $tableName . "') AS \"Create Table\"";
            case 'mssql':
            case 'sqlsrv':
                return "
                SELECT
                	'CREATE TABLE '  + SCHEMA_NAME(t.schema_id) + '.' + t.name + ' (' +
                		STUFF ((
                			SELECT ', [' + c2.name + '] ' + type_name(c2.user_type_id) + 
							    CASE
								    WHEN type_name(c2.user_type_id) = 'nvarchar' THEN ' (256)'
									ELSE ''
								END +
                				CASE
                					WHEN c2.is_nullable = 1 THEN ' NULL'
                					ELSE ' NOT NULL'
                				END + 
                				CASE
                					WHEN c2.column_id = 1 AND c2.is_identity = 1 THEN ' IDENTITY (1,1)'
                					ELSE ''
                				END +
                				CASE
                					WHEN pk.column_id IS NOT NULL THEN ' PRIMARY KEY'
                					ELSE ''
                				END
                			FROM sys.columns c2
                			LEFT JOIN (
                				SELECT ic.object_id, ic.column_id, ic.index_column_id
                				FROM sys.index_columns ic
                				JOIN sys.indexes i ON 
                						i.object_id  = ic.object_id
                					AND i.index_id = ic.index_id
                				WHERE i.is_primary_key = 1
                			) pk ON 
                					pk.object_id = c2.object_id
                				AND pk.column_id = c2.column_id
                			WHERE c2.object_id = t.object_id
                			ORDER BY c2.column_id
                			FOR XML PATH (''), TYPE
                			).value('.', 'NVARCHAR(MAX)'), 1, 2, '') + 
                		')' AS [Create Table]
                FROM sys.tables t
                JOIN sys.schemas s ON t.schema_id = s.schema_id
                WHERE s.name = 'dbo' and t.name = '{$tableName}'
                ORDER BY t.name;
                ";
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
        switch (Yii::app()->db->getDriverName()) {
            case 'mssql':
            case 'sqlsrv':
                $script["Create Table"] = str_replace("[id] int NOT NULL PRIMARY KEY", "[id] int IDENTITY(1, 1) PRIMARY KEY", $script["Create Table"]);
                break;
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
            case 'mssql':
            case 'sqlsrv':
                return "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_CATALOG = db_name() AND TABLE_NAME = '{$tableName}'";
            default:
                return "";
        }
    }

    /** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
    public function up()
    {
        $leftSeparator = $rightSeparator = "`";
        if (Yii::app()->db->getDriverName() === 'pgsql') {
            $leftSeparator = $rightSeparator = '"';
        } elseif (in_array(Yii::app()->db->getDriverName(), ['mssql', 'sqlsrv'])) {
            $leftSeparator = "[";
            $rightSeparator = "]";
        }
        $this->doPreparations();
        $this->scriptMapping = [
            'responses' => $this->getResponsesScript(),
            'timings' => $this->getTimingScript(),
            'fields' => $this->getFieldsScript()
        ];
        $scripts = [];
        $responsesTables = $this->db->createCommand($this->scriptMapping['responses'])->queryAll();
        if (!count($responsesTables)) {
            return; //the script already ran
        }
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
            $preinsert = "";
            $postinsert = "";
            if (in_array(Yii::app()->db->getDriverName(), ['mssql', 'sqlsrv'])) {
                $preinsert = "SET IDENTITY_INSERT {$scripts[$TABLE_NAME]['new_name']} ON;";
                $postinsert = "SET IDENTITY_INSERT {$scripts[$TABLE_NAME]['new_name']} OFF;";
            }
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
                FROM {$TABLE_NAME};
            ";
            $this->db->createCommand($scripts[$TABLE_NAME]['CREATE'])->execute();
            $this->db->createCommand($preinsert . $scripts[$TABLE_NAME]['INSERT'] . $postinsert)->execute();
            $this->db->createCommand($scripts[$TABLE_NAME]['DROP'])->execute();
        }
    }
}
