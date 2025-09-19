<?php

namespace LimeSurvey\Models\Services;

class LSCDbCommandBuilder extends \CDbCommandBuilder
{
    /**
     * Creates a multiple INSERT command.
     * This method compose the SQL expression via given part templates, providing ability to adjust
     * command for different SQL syntax.
     *
     * @param  mixed   $table     the table schema ({@link CDbTableSchema}) or the table name (string).
     * @param  array[] $data      list data to be inserted, each value should be an array in format (column name=>column value).
     *                            If a key is not a valid column name, the corresponding value will be ignored.
     * @param  array   $templates templates for the SQL parts.
     * @return \CDbCommand multiple insert command
     * @throws \CDbException if $data is empty.
     */
    protected function composeMultipleInsertCommand($table, array $data, array $templates = array())
    {
        if (empty($data)) {
            throw new \CDbException(\Yii::t('yii', 'Can not generate multiple insert command with empty data set.'));
        }
        $templates = array_merge(
            array(
                'main' => 'INSERT INTO {{tableName}} ({{columnInsertNames}}) VALUES {{rowInsertValues}}',
                'columnInsertValue' => '{{value}}',
                'columnInsertValueGlue' => ', ',
                'rowInsertValue' => '({{columnInsertValues}})',
                'rowInsertValueGlue' => ', ',
                'columnInsertNameGlue' => ', ',
            ),
            $templates
        );
        $this->ensureTable($table);
        $tableName = $table->rawName;
        $columnInsertNames = array();

        $columns = array();
        foreach ($data as $rowData) {
            foreach ($rowData as $columnName => $columnValue) {
                if (!in_array($columnName, $columns, true)) {
                    if ($table->getColumn($columnName) !== null) {
                        $columns[] = $columnName;
                    }
                }
            }
        }
        foreach ($columns as $name) {
            $columnInsertNames[$name] = $this->getDbConnection()->quoteColumnName($name);
        }
        $columnInsertNamesSqlPart = implode($templates['columnInsertNameGlue'], $columnInsertNames);

        list($rowInsertValues, $params) = $this->buildRowValues(
            $data,
            $columns,
            $table,
            $templates,
            $columnInsertNames,
            $columnInsertNamesSqlPart
        );

        $sql = strtr(
            $templates['main'],
            array(
                '{{tableName}}' => $tableName,
                '{{columnInsertNames}}' => $columnInsertNamesSqlPart,
                '{{rowInsertValues}}' => implode($templates['rowInsertValueGlue'], $rowInsertValues),
            )
        );
        $command = $this->bindParamsToCommand($sql, $params);

        return $command;
    }

    private function bindParamsToCommand($sql, array $params)
    {
        $command = $this->getDbConnection()->createCommand($sql);
        foreach ($params as $name => $value) {
            $command->bindValue($name, $value);
        }
        return $command;
    }

    private function buildRowValues($table, array $data, array $columns, array $templates, array $columnInsertNames, array $columnInsertNamesSqlPart)
    {
        $params = array();
        $rowInsertValues = array();
        $tableName = $table->rawName;

        foreach ($data as $rowKey => $rowData) {
            $columnInsertValues = array();
            foreach ($columns as $columnName) {
                $placeholder = str_replace("#", "hashtag", $columnName);
                $column = $table->getColumn($columnName);
                $columnValue = array_key_exists($columnName, $rowData) ? $rowData[$columnName] : new \CDbExpression('NULL');
                if ($columnValue instanceof \CDbExpression) {
                    $columnInsertValue = $columnValue->expression;
                    foreach ($columnValue->params as $columnValueParamName => $columnValueParam) {
                        $params[$columnValueParamName] = $columnValueParam;
                    }
                } else {
                    $columnInsertValue = ':' . $placeholder . '_' . $rowKey;
                    $params[':' . $placeholder . '_' . $rowKey] = $column->typecast($columnValue);
                }
                $columnInsertValues[] = strtr(
                    $templates['columnInsertValue'],
                    array(
                        '{{column}}' => $columnInsertNames[$columnName],
                        '{{value}}' => $columnInsertValue,
                    )
                );
            }
            $rowInsertValues[] = strtr(
                $templates['rowInsertValue'],
                array(
                    '{{tableName}}' => $tableName,
                    '{{columnInsertNames}}' => $columnInsertNamesSqlPart,
                    '{{columnInsertValues}}' => implode($templates['columnInsertValueGlue'], $columnInsertValues)
                )
            );
        }

        return [$rowInsertValues, $params];
    }
}
