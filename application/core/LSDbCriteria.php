<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
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
}
