<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$CI =& get_instance(); 

// Number fetch mode not supported by CI.
/**
function &db_execute_num($sql,$inputarr=false)
{
    global $connect;

    $connect->SetFetchMode(ADODB_FETCH_NUM);
    $dataset=$connect->Execute($sql,$inputarr);  //Checked
    return $dataset;
}

function &db_select_limit_num($sql,$numrows=-1,$offset=-1,$inputarr=false)
{
    global $connect;

    $connect->SetFetchMode(ADODB_FETCH_NUM);
    $dataset=$connect->SelectLimit($sql,$numrows,$offset,$inputarr=false) or safe_die($sql);
    return $dataset;
}
*/
function &db_execute_assoc($sql,$inputarr=false,$silent=false)
{
    //global $connect;
    global $CI;
    //$connect->SetFetchMode(ADODB_FETCH_ASSOC);
    if($inputarr)
    {
        $dataset=$CI->db->query($sql,$inputarr);    //Checked
    }
    else
    {
        $dataset=$CI->db->query($sql);
    }
    
    if (!$silent && !$dataset)  {safe_die('Error executing query in db_execute_assoc:'.$sql);}
    return $dataset;
}

function &db_select_limit_assoc($sql,$numrows=0,$offset=0,$inputarr=false,$dieonerror=true)
{
    //global $connect;
    global $CI;
    //$connect->SetFetchMode(ADODB_FETCH_ASSOC);
    if ($numrows)
    {
        if ($offset)
        {
            $CI->db->limit($numrows,$offset);
        }
        else
        {
            $CI->db->limit($numrows);
        }
    }
    if($inputarr)
    {
        $dataset=$CI->db->query($sql,$inputarr);    //Checked
    }
    else
    {
        $dataset=$CI->db->query($sql);
    }
    if (!$dataset && $dieonerror) {safe_die('Error executing query in db_select_limit_assoc:'.$sql);}
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
    //global $connect;
    global $CI;
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
 /**
function db_quote_id($id)
{
    global $databasetype;
    // WE DONT HAVE nor USE other thing that alfanumeric characters in the field names
    //  $quote = $connect->nameQuote;
    //  return $quote.str_replace($quote,$quote.$quote,$id).$quote;

    switch ($databasetype)
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
        case "postgres":
            return "\"".$id."\"";
            break;
        default:
            return "`".$id."`";
    }
}
*/
function db_random()
{
    //global $connect,$databasetype;
    global $CI;
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
    //global $databasetype;
    global $CI;
    switch ($CI->db->dbdriver) {
        case 'mysqli':
        case 'mysql' :
            return "SHOW TABLES LIKE '$table'";
        case 'odbtp' :
        case 'mssql_n' :
        case 'mssqlnative':
        case 'odbc_mssql' :
            return "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES where TABLE_TYPE='BASE TABLE' and TABLE_NAME LIKE '$table'";
        case 'postgres' :
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
    global $CI;
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