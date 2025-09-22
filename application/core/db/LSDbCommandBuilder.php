<?php

class LSDbCommandBuilder extends \CDbCommandBuilder
{
    protected $_schema;

    protected $_connection;
	/**
	 * Creates a multiple INSERT command.
	 * This method compose the SQL expression via given part templates, providing ability to adjust
	 * command for different SQL syntax.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array[] $data list data to be inserted, each value should be an array in format (column name=>column value).
	 * If a key is not a valid column name, the corresponding value will be ignored.
	 * @param array $templates templates for the SQL parts.
	 * @return \CDbCommand multiple insert command
	 * @throws \CDbException if $data is empty.
	 */
	protected function composeMultipleInsertCommand($table,array $data,array $templates=array())
	{
		if (empty($data))
			throw new \CDbException(\Yii::t('yii','Can not generate multiple insert command with empty data set.'));
		$templates=array_merge(
			array(
				'main'=>'INSERT INTO {{tableName}} ({{columnInsertNames}}) VALUES {{rowInsertValues}}',
				'columnInsertValue'=>'{{value}}',
				'columnInsertValueGlue'=>', ',
				'rowInsertValue'=>'({{columnInsertValues}})',
				'rowInsertValueGlue'=>', ',
				'columnInsertNameGlue'=>', ',
			),
			$templates
		);
		$this->ensureTable($table);
		$tableName=$table->rawName;
		$params=array();
		$columnInsertNames=array();
		$rowInsertValues=array();

		$columns=array();
		foreach($data as $rowData)
		{
			foreach($rowData as $columnName=>$columnValue)
			{
				if(!in_array($columnName,$columns,true))
					if($table->getColumn($columnName)!==null)
						$columns[]=$columnName;
			}
		}
		foreach($columns as $name)
			$columnInsertNames[$name]=$this->getDbConnection()->quoteColumnName($name);
		$columnInsertNamesSqlPart=implode($templates['columnInsertNameGlue'],$columnInsertNames);

		foreach($data as $rowKey=>$rowData)
		{
			$columnInsertValues=array();
			foreach($columns as $columnName)
			{
				$placeholder = str_replace("#", "hashtag", $columnName);
				$column=$table->getColumn($columnName);
				$columnValue=array_key_exists($columnName,$rowData) ? $rowData[$columnName] : new \CDbExpression('NULL');
				if($columnValue instanceof \CDbExpression)
				{
					$columnInsertValue=$columnValue->expression;
					foreach($columnValue->params as $columnValueParamName=>$columnValueParam)
						$params[$columnValueParamName]=$columnValueParam;
				}
				else
				{
					$columnInsertValue=':'.$placeholder.'_'.$rowKey;
					$params[':'.$placeholder.'_'.$rowKey]=$column->typecast($columnValue);
				}
				$columnInsertValues[]=strtr($templates['columnInsertValue'],array(
					'{{column}}'=>$columnInsertNames[$columnName],
					'{{value}}'=>$columnInsertValue,
				));
			}
			$rowInsertValues[]=strtr($templates['rowInsertValue'],array(
				'{{tableName}}'=>$tableName,
				'{{columnInsertNames}}'=>$columnInsertNamesSqlPart,
				'{{columnInsertValues}}'=>implode($templates['columnInsertValueGlue'],$columnInsertValues)
			));
		}

		$sql=strtr($templates['main'],array(
			'{{tableName}}'=>$tableName,
			'{{columnInsertNames}}'=>$columnInsertNamesSqlPart,
			'{{rowInsertValues}}'=>implode($templates['rowInsertValueGlue'], $rowInsertValues),
		));
		$command=$this->getDbConnection()->createCommand($sql);

		foreach($params as $name=>$value)
			$command->bindValue($name,$value);

		return $command;
	}
    /**
     * Summary of mergeTemplates
     * @param array $templates
     * @return array{columnInsertNameGlue: string, columnInsertValue: string, columnInsertValueGlue: string, main: string, rowInsertValue: string, rowInsertValueGlue: string}
     */
    private function mergeTemplates(array $templates)
    {
        return array_merge(
            [
                'main' => 'INSERT INTO {{tableName}} ({{columnInsertNames}}) VALUES {{rowInsertValues}}',
                'columnInsertValue' => '{{value}}',
                'columnInsertValueGlue' => ', ',
                'rowInsertValue' => '({{columnInsertValues}})',
                'rowInsertValueGlue' => ', ',
                'columnInsertNameGlue' => ', ',
            ],
            $templates
        );
    }

    private function extractColumns($table, array $data)
    {
        $columns = [];
        foreach ($data as $rowData) {
            foreach ($rowData as $columnName => $columnValue) {
                if (!in_array($columnName, $columns, true) && $table->getColumn($columnName) !== null) {
                    $columns[] = $columnName;
                }
            }
        }
        return $columns;
    }

    private function quoteColumnNames(array $columns)
    {
        $quoted = [];
        foreach ($columns as $name) {
            $quoted[$name] = $this->getDbConnection()->quoteColumnName($name);
        }
        return $quoted;
    }

    private function buildRowValues($table, array $data, array $columns, array $columnInsertNames, $columnInsertNamesSqlPart, array $templates, $tableName)
    {
        $params = [];
        $rowInsertValues = [];

        foreach ($data as $rowKey => $rowData) {
            $columnInsertValues = [];
            foreach ($columns as $columnName) {
                list($columnInsertValue, $newParams) = $this->buildColumnValue($table, $rowKey, $columnName, $rowData);
                $params = array_merge($params, $newParams);

                $columnInsertValues[] = strtr($templates['columnInsertValue'], [
                    '{{column}}' => $columnInsertNames[$columnName],
                    '{{value}}'  => $columnInsertValue,
                ]);
            }

            $rowInsertValues[] = strtr($templates['rowInsertValue'], [
                '{{tableName}}'         => $tableName,
                '{{columnInsertNames}}' => $columnInsertNamesSqlPart,
                '{{columnInsertValues}}' => implode($templates['columnInsertValueGlue'], $columnInsertValues),
            ]);
        }

        return [$rowInsertValues, $params];
    }

    private function buildColumnValue($table, $rowKey, $columnName, $rowData)
    {
        $params = [];
        $placeholder = str_replace("#", "hashtag", $columnName);
        $column = $table->getColumn($columnName);
        $columnValue = array_key_exists($columnName, $rowData) ? $rowData[$columnName] : new \CDbExpression('NULL');

        if ($columnValue instanceof \CDbExpression) {
            $value = $columnValue->expression;
            foreach ($columnValue->params as $paramName => $paramValue) {
                $params[$paramName] = $paramValue;
            }
        } else {
            $value = ':' . $placeholder . '_' . $rowKey;
            $params[$value] = $column->typecast($columnValue);
        }

        return [$value, $params];
    }

    private function buildSql(array $templates, $tableName, $columnInsertNamesSqlPart, array $rowInsertValues)
    {
        return strtr($templates['main'], [
            '{{tableName}}'         => $tableName,
            '{{columnInsertNames}}' => $columnInsertNamesSqlPart,
            '{{rowInsertValues}}'   => implode($templates['rowInsertValueGlue'], $rowInsertValues),
        ]);
    }

    private function bindParamsToCommand($sql, array $params)
    {
        $command = $this->getDbConnection()->createCommand($sql);
        foreach ($params as $name => $value) {
            $command->bindValue($name, $value);
        }
        return $command;
    }

	/**
	 * Creates a DELETE command.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param CDbCriteria $criteria the query criteria
	 * @return CDbCommand delete command.
	 */
	public function createDeleteCommand($table,$criteria)
	{
		$this->ensureTable($table);
		$sql="DELETE {$table->rawName} FROM {$table->rawName}";
		$sql=$this->applyJoin($sql,$criteria->join);
		$sql=$this->applyCondition($sql,$criteria->condition);
		$sql=$this->applyGroup($sql,$criteria->group);
		$sql=$this->applyHaving($sql,$criteria->having);
		$sql=$this->applyOrder($sql,$criteria->order);
		$sql=$this->applyLimit($sql,$criteria->limit,$criteria->offset);
		$command=$this->getDbConnection()->createCommand($sql);
		$this->bindValues($command,$criteria->params);
		return $command;
	}
}
