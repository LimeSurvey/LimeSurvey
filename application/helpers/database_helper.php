<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */
function &db_execute_assoc($sql,$inputarr=false,$silent=false)
{
	try {
		if($inputarr)
		{
				$dataset=Yii::app()->db->createCommand($sql)->bindValues($inputarr)->query();	//Checked
		}
		else
		{
				$dataset=Yii::app()->db->createCommand($sql)->query();

		}
	} catch(CDbException $e) {
		$dataset=false;
	}

    if (!$silent && !$dataset)  { safe_die('Error executing query in db_execute_assoc:'.$sql); }
    return $dataset;
}

function &db_execute($sql,$inputarr=false,$silent=false)
{
	try {
		if($inputarr)
		{
				$affected=Yii::app()->db->createCommand($sql)->bindValues($inputarr)->execute();	//Checked
		}
		else
		{
				$affected=Yii::app()->db->createCommand($sql)->execute();

		}
	} catch(CDbException $e) {
		$affected=false;
	}

    if (!$silent && !$affected)  {safe_die('Error executing query in db_execute_assoc:'.$sql);}
    return $affected;
}

function &db_query_or_false($sql)
{
	try {
        $dataset=Yii::app()->db->createCommand($sql)->query();
	} catch(CDbException $e) {
		$dataset=false;
	}
	return $dataset;
}

/**
  * Returns the number of records found in the database
  *
  * @param string $sql
  * @return int
*/
function &db_records_count($sql)
{
	$yii = Yii::app();
	$count = 0;
	try
	{
		$result = $yii->db->createCommand($sql)->query();
		$count = $result->count();
	}
	catch(CDbException $e)
	{
		$count = FALSE;
	}

	return $count;
}

function &db_select_limit_assoc($sql,$numrows=0,$offset=0,$inputarr=false,$dieonerror=true)
{
	$query = Yii::app()->db->createCommand($sql.= " ");
    if ($numrows)
    {
        if ($offset)
        {
            $query->limit($numrows, $offset);
        }
        else
        {
            $query->limit($numrows, 0);
        }
    }
    if($inputarr)
    {
        $query->bindValues($inputarr);    //Checked
    }
	try
    {
		$dataset=$query->query();
    }
	catch (CDbException $e)
	{
		$dataset=false;
	}
	if (!$dataset && $dieonerror) {safe_die('Error executing query in db_select_limit_assoc:'.$query->text);}
    return $dataset;
}


/**
 * Returns the first row of values of the $sql query result
 * as a 1-dimensional array
 *
 * @param mixed $sql
 */
function &db_select_column($sql)
{
    $dataset=Yii::app()->db->createCommand($sql)->query();
    if ($dataset->count() > 0)
    {
            $fields = array_keys($dataset[0]);
        $firstfield = $fields[0];
        $resultarray=array();
        foreach ($dataset->readAll() as $row)
        {
            $resultarray[] = $row[$firstfield];
        }
        /**while ($row = $dataset->fetchRow()) {
            $resultarray[]=$row[0];
        }*/
    }
    else
    safe_die('No results were returned from the query :'.$sql);
    return $resultarray;
}


/**
 * This functions quotes fieldnames accordingly
 *
 * @param mixed $id Fieldname to be quoted
 */

function db_quote_id($id)
{
    switch (Yii::app()->db->getDriverName())
    {
        case "mysqli" :
        case "mysql" :
            return "`".$id."`";
            break;
        case "mssql_n" :
        case "mssql" :
		case "mssqlnative" :
        case "odbc_mssql" :
            return "[".$id."]";
            break;
        case "postgre":
            return "\"".$id."\"";
            break;
        default:
            return "`".$id."`";
    }
}

function db_random()
{
    if (Yii::app()->db->getDriverName() == 'odbc_mssql' || Yii::app()->db->getDriverName() == 'mssql_n' || Yii::app()->db->getDriverName() == 'odbtp')  {$srandom='NEWID()';}
    else {$srandom= 0 + lcg_value()*(abs(1));}
    return $srandom;

}

/**
 *  Return a sql statement for finding LIKE named tables
 *  Be aware that you have to escape underscor chars by using a backslash
 * otherwise you might get table names returned you don't want
 *
 * @param mixed $table
 */
function db_select_tables_like($table)
{
    switch (Yii::app()->db->getDriverName()) {
        case 'mysqli':
        case 'mysql' :
            return "SHOW TABLES LIKE '$table'";
        case 'mssql' :
        case 'odbc' :
            return "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES where TABLE_TYPE='BASE TABLE' and TABLE_NAME LIKE '$table'";
        case 'postgre' :
            $table=str_replace('\\','\\\\',$table);
            return "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' and table_name like '$table'";
        default: safe_die ("Couldn't create 'select tables like' query for connection type 'databaseType'");
    }
}

/**
 * Gets the table names. Do not prefix.
 * @param string $table String to match
 * @uses db_select_tables_like() To get the tables like sql query
 * @return array Array of matched table names
 */
function db_get_tables_like($table)
{
    return (array) Yii::app()->db->createCommand(db_select_tables_like("{{{$table}}}"))->queryAll();
}

/**
 *  Return a boolean stating if the table(s) exist(s)
 *  Accepts '%' in names since it uses the 'like' statement
 *
 * @param mixed $table
 */
function db_tables_exist($table)
{
    return !!Yii::app()->db->schema->getTable($table);
}
