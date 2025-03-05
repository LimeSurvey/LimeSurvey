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

 /**
 *
 * @param string $sql
 * @param array|bool $inputarr
 * @param boolean $silent
 * @return bool|CDbDataReader
 * @throws Exception
 * @deprecated Do not use anymore. If you see this replace it with a proper ActiveRecord Model query
 */
function dbExecuteAssoc($sql, $inputarr = false, $silent = true)
{
    $error = '';
    try {
        if ($inputarr) {
            $dataset = Yii::app()->db->createCommand($sql)->bindValues($inputarr)->query(); //Checked
        } else {
            $dataset = Yii::app()->db->createCommand($sql)->query();
        }
    } catch (CDbException $e) {
        $error = $e->getMessage();
        $dataset = false;
    }

    if (!$dataset && (Yii::app()->getConfig('debug') > 0 || !$silent)) {
        // Exception is better than safeDie, because you can see the backtrace.
        throw new \Exception('Error executing query in dbExecuteAssoc:' . $error);
    }
    return $dataset;
}

/**
 * Return the database-specific random function to use in ORDER BY sql statements
 *
 * @return string
 */
function dbRandom()
{
    $driver = Yii::app()->db->getDriverName();
    switch ($driver) {
        case 'dblib':
        case 'mssql':
        case 'sqlsrv':
            $srandom = 'NEWID()';
            break;

        case 'pgsql':
            $srandom = 'RANDOM()';
            break;

        case 'mysql':
        case 'mysqli':
            $srandom = 'RAND()';
            break;

        default:
            //Some db type that is not mentioned above, could fail and if so should get an entry above.
            $srandom = 0 + lcg_value() * (abs(1));
            break;
    }

    return $srandom;
}

/**
 *  Return a sql statement for finding LIKE named tables
 *  Be aware that you have to escape underscore chars by using a backslash
 * otherwise you might get table names returned you don't want
 *
 * @param mixed $table
 * @return string
 */
function dbSelectTablesLike($table)
{
    switch (Yii::app()->db->getDriverName()) {
        case 'mysqli':
        case 'mysql':
            return "SHOW TABLES LIKE '$table'";
        case 'dblib':
        case 'mssql':
        case 'sqlsrv':
            return "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES where TABLE_TYPE='BASE TABLE' and TABLE_NAME LIKE '$table' ESCAPE '\'";
        case 'pgsql':
            return "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' and table_name like '$table'";
        default:
            safeDie("Couldn't create 'select tables like' query for connection type '" . Yii::app()->db->getDriverName() . "'");
    }
}

/**
* Gets the table names. Do not prefix.
* @param string $table String to match
* @uses dbSelectTablesLike() To get the tables like sql query
* @return array Array of matched table names
*/
function dbGetTablesLike($table)
{
    return (array) Yii::app()->db->createCommand(dbSelectTablesLike("{{{$table}}}"))->queryColumn();
}

/**
 * Returns the tables which
 * @param int $sid
 * @return array
 */
function getTableArchivesAndTimestamps(int $sid)
{
    return (array) Yii::app()->db->createCommand("
        SELECT GROUP_CONCAT(t1.TABLE_NAME) AS tables, SUBSTRING_INDEX(t1.TABLE_NAME, '_', -1) AS timestamp, MAX(t2.TABLE_ROWS) AS cnt
        FROM information_schema.tables t1
        JOIN information_schema.tables t2
        ON t1.TABLE_SCHEMA = t2.TABLE_SCHEMA AND
           t2.TABLE_NAME LIKE CONCAT('%_old_survey_{$sid}_', SUBSTRING_INDEX(t1.TABLE_NAME, '_', -1))
        WHERE t1.TABLE_SCHEMA = DATABASE() AND
              t1.TABLE_NAME LIKE '%old%' AND
              t1.TABLE_NAME LIKE '%{$sid}%'
        GROUP BY SUBSTRING_INDEX(t1.TABLE_NAME, '_', -1)
    ")->queryAll();
}
