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
    //$connect->SetFetchMode(ADODB_FETCH_ASSOC);
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
    //$connect->SetFetchMode(ADODB_FETCH_ASSOC);
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
    //$connect->SetFetchMode(ADODB_FETCH_ASSOC);
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
	$CI = &get_instance();
    //$connect->SetFetchMode(ADODB_FETCH_NUM);
    $dataset=$CI->db->query($sql);
    $fields = $dataset->list_fields();
    $firstfield = $fields[0];
    $resultarray=array();
    if ($dataset->num_rows() > 0)
    {
        foreach ($dataset->result_array() as $row)
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
    // WE DONT HAVE nor USE other thing that alfanumeric characters in the field names
    //  $quote = $connect->nameQuote;
    //  return $quote.str_replace($quote,$quote.$quote,$id).$quote;

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
	$CI = &get_instance();
    if ($CI->db->dbdriver == 'odbc_mssql' || $CI->db->dbdriver == 'mssql_n' || $CI->db->dbdriver == 'odbtp')  {$srandom='NEWID()';}
    else {$srandom= 0 + lcg_value()*(abs(1));}
    return $srandom;

}
/**
function db_quote($str,$ispostvar=false)
// This functions escapes the string only inside
{
    global $connect;
    if ($ispostvar) { return $connect->escape($str, get_magic_quotes_gpc());}
    else {return $connect->escape($str);}
}

function db_quoteall($str,$ispostvar=false)
// This functions escapes the string inside and puts quotes around the string according to the used db type
// IF you are quoting a variable from a POST/GET then set $ispostvar to true so it doesnt get quoted twice.
{
    global $connect;
    if ($ispostvar) { return $connect->qstr($str, get_magic_quotes_gpc());}
    else {return $connect->qstr($str);}

}

function db_table_name($name)
{
    global $dbprefix;
    return db_quote_id($dbprefix.$name);
}
*/
/**
 * returns the table name without quotes
 *
 * @param mixed $name
 */
 /**
function db_table_name_nq($name)
{
    global $dbprefix;
    return $dbprefix.$name;
}
*/
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
 *  Return a boolean stating if the table(s) exist(s)
 *  Accepts '%' in names since it uses the 'like' statement
 *
 * @param mixed $table
 */
function db_tables_exist($table)
{
	$CI = &get_instance();
    return $CI->db->table_exists($table);
    /**global $connect;

    $surveyHasTokensTblQ = db_select_tables_like("$table");
    $surveyHasTokensTblResult = db_execute_num($surveyHasTokensTblQ); //Checked

    if ($surveyHasTokensTblResult->RecordCount() >= 1)
    {
        return TRUE;
    }
    else
    {
        return FALSE;
    }*/
}
