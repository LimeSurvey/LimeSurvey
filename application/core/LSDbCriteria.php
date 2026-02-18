<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

class LSDbCriteria extends CDbCriteria
{
    /**
     * Basic initialiser to the base controller class
     *
     * @access public
     * @param string $column The name of the column to be searched
     * @param mixed $value The column value to be compared with
     * @param boolean $partialMatch Whether the value should consider partial text match
     * @param string $operator The operator used to concatenate the new condition with the existing one
     * @param boolean $escape Whether the value should be escaped if $partialMatch is true and the value contains characters % or _
     * @return void
     */
    // public function compare(string $column, mixed $value, boolean $partialMatch=false, string $operator='AND', boolean $escape=true)
    public function compare($column, $value, $partialMatch = false, $operator = 'AND', $escape = true)
    {
        if ($partialMatch && Yii::app()->db->getDriverName() == 'pgsql') {
            $this->addSearchCondition($column, $value, true, $operator, 'ILIKE');
        } else {
            parent::compare($column, $value, $partialMatch, $operator, $escape);
        }
    }

    /**
     * inherit doc
     * Replace escape systemfor MSSQL, mantis issue #18550
     */
    public function addSearchCondition($column, $keyword, $escape = true, $operator = 'AND', $like = 'LIKE')
    {
        if ($keyword !== '' && $escape && in_array(App()->db->driverName, ['sqlsrv', 'dblib', 'mssql'])) {
                /* Escape are bad in Yii1, fix it, issue #18550 */
                $escapingReplacements = [
                    '%' => '[%]',
                    '_' => '[_]',
                    '[' => '[[]',
                    ']' => '[]]',
                    '\\' => '[\\]',
                ];
                $keyword = '%' . strtr($keyword, $escapingReplacements) . '%';
                $escape = false;
        }
        return parent::addSearchCondition($column, $keyword, $escape, $operator, $like);
    }

    /**
     * Safely apply conditions to a CDbCriteria object.
     * Hardens against SQL injection by validating column names.
     * @param model $oModel : can be \Token or \Survey or anything else
     * @param string $condition conditions to limit the list, either as a
     * @throws BadRequestException
     * @return void
     */
    public function addUnsureSearchStringCondition($oModel,$condition)
    {
        if ($condition === '') {
            return;
        }
        if (!is_string($condition) || !preg_match('/^([a-zA-Z0-9_]+)\s*(<=|>=|<>|=|<|>)\s*(.*)$/', $condition, $matches)) {
            throw new BadRequestException('Invalid expression for condition');
        }
        $this->addUnsureSearchCondition($oModel, [$matches[1]=>[$matches[2], $matches[3]]]);
    }

    /**
     * Safely apply conditions to a CDbCriteria object.
     * Hardens against SQL injection by validating column names.
     * @param model $oModel : can be \Token or \Survey or anything else
     * @param array $aConditions conditions to limit the list, either as a
     *              key=>value search value in column key  : sample ['tid' => '2']
     *              key=>array(operator,value[,value[...]]) using an operator : sample ['tid'=>['=','2']]
     *                  Valid operators are  ['<', '>', '>=', '<=', '=', '<>', 'LIKE', 'IN']
     *                  Only the IN operator allows for several values.
     *              All conditions are connected by AND.
     * @throws BadRequestException
     * @return void
     */
    public function addUnsureSearchCondition($oModel, $aConditions = [])
    {
        if (empty($aConditions)) {
            return;
        }
        $columnsNames = array_flip($oModel->getMetaData()->tableSchema->columnNames);
        foreach ($aConditions as $columnName => $valueOrTuple) {
            if (!array_key_exists($columnName, $columnsNames)) {
                throw new BadRequestException('Invalid column name: ' . $columnName);
            }
            if (is_array($valueOrTuple)) {
                if (count($valueOrTuple) < 2) {
                    throw new BadRequestException('Invalid number of element for ' . $columnName);
                }
                /** @var string[] List of operators allowed in query. */
                $allowedOperators = ['<', '>', '>=', '<=', '=', '<>', 'LIKE', 'IN'];
                /** @var string */
                $operator = $valueOrTuple[0];
                if (!is_string($operator)) {
                    throw new BadRequestException('Illegal operator for column ' . $columnName);
                }
                if (!in_array($operator, $allowedOperators, true)) {
                    throw new BadRequestException('Illegal operator: ' . $operator . ' for column ' . $columnName);
                }
                switch ($operator) {
                    case 'LIKE':
                        /** @var scalar */
                        $value = $valueOrTuple[1];
                        if (!is_scalar($value)) {
                            throw new BadRequestException('LIKE operator requires a string value for column ' . $columnName);
                        }
                        $this->addSearchCondition($columnName, $value);
                        break;
                    case 'IN':
                        /** @var scalar[] */
                        $values = array_slice($valueOrTuple, 1);
                        foreach ($values as $v) {
                            if (!is_scalar($v) && !is_null($v)) {
                                throw new BadRequestException('IN operator requires scalar values for column ' . $columnName);
                            }
                        }
                        $this->addInCondition($columnName, $values);
                        break;
                    default:
                        /** @var scalar*/
                        $value = $valueOrTuple[1];
                        if (!is_scalar($value)) {
                            throw new BadRequestException('Comparison operators require a scalar value for column ' . $columnName);
                        }
                        $this->compare($columnName, $operator . $value);
                }
            } elseif (is_scalar($valueOrTuple) || is_null($valueOrTuple)) {
                $this->addColumnCondition([$columnName => $valueOrTuple]);
            } else {
                throw new BadRequestException('Invalid value type for column ' . $columnName);
            }
        }
    }
}
