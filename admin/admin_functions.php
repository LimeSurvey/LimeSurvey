<?php
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *	$Id:$
 *	Files Purpose:
 */


function get2post($url)
{
    $url = preg_replace('/&amp;/i','&',$url);
    list($calledscript,$query) = explode('?',$url);
    $aqueryitems = explode('&',$query);
    $arrayParam = Array();
    $arrayVal = Array();

    foreach ($aqueryitems as $queryitem)
    {
        list($paramname, $value) = explode ('=', $queryitem);
        $arrayParam[] = "'".$paramname."'";
        $arrayVal[] = substr($value, 0, 9) != "document." ? "'".$value."'" : $value;
    }
    //	$Paramlist = "[" . implode(",",$arrayParam) . "]";
    //	$Valuelist = "[" . implode(",",$arrayVal) . "]";
    $Paramlist = "new Array(" . implode(",",$arrayParam) . ")";
    $Valuelist = "new Array(" . implode(",",$arrayVal) . ")";
    $callscript = "sendPost('$calledscript','".$_SESSION['checksessionpost']."',$Paramlist,$Valuelist);";
    return $callscript;
}

/**
* This function switches identity insert on/off for the MSSQL database
*
* @param string $table table name (without prefix)
* @param mixed $state  Set to true to activate ID insert, or false to deactivate
*/
function db_switchIDInsert($table,$state)
{
    global $databasetype, $connect;
    if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n' || $databasetype=='mssqlnative')
    {
        if ($state==true)
        {
            $connect->Execute('SET IDENTITY_INSERT '.db_table_name($table).' ON');
        }
        else
        {
            $connect->Execute('SET IDENTITY_INSERT '.db_table_name($table).' OFF');
        }
    }
}

/**
 * Returns true if a user has a given right in the particular survey
 *
 * @param $sid
 * @param $right
 * @return bool
 */
function bHasRight($sid, $right = null)
{
    global $dbprefix, $connect;

    static $cache = array();

    if (isset($_SESSION['loginID'])) $uid = $_SESSION['loginID']; else return false;

    if ($_SESSION['USER_RIGHT_SUPERADMIN']==1) return true; //Superadmin has access to all

    if (!isset($cache[$sid][$uid]))
    {
        $sql = "SELECT * FROM " . db_table_name('surveys_rights') . " WHERE sid=".db_quote($sid)." AND uid = ".db_quote($uid); //Getting rights for this survey
        $result = db_execute_assoc($sql);
        $rights = $result->FetchRow();
        if ($rights===false)
        {
            return false;
        } else {
            $cache[$sid][$uid]=$rights;
        }
    }
    if (empty($right)) return true;
    if (isset($cache[$sid][$uid][$right]) && $cache[$sid][$uid][$right] == 1) return true; else return false;
}


function gettemplatelist()
{
    global $usertemplaterootdir, $standardtemplates,$standardtemplaterootdir;

    if (!$usertemplaterootdir) {die("gettemplatelist() no template directory");}
    if ($handle = opendir($standardtemplaterootdir))
    {
        while (false !== ($file = readdir($handle)))
        {
            if (!is_file("$standardtemplaterootdir/$file") && $file != "." && $file != ".." && $file!=".svn" && isStandardTemplate($file))
            {
                $list_of_files[$file] = $standardtemplaterootdir.DIRECTORY_SEPARATOR.$file;
            }
        }
        closedir($handle);
    }

    if ($handle = opendir($usertemplaterootdir))
    {
        while (false !== ($file = readdir($handle)))
        {
            if (!is_file("$usertemplaterootdir/$file") && $file != "." && $file != ".." && $file!=".svn")
            {
                $list_of_files[$file] = $usertemplaterootdir.DIRECTORY_SEPARATOR.$file;
            }
        }
        closedir($handle);
    }
    ksort($list_of_files);
    return $list_of_files;
}


/**
* This function set a question attribute to a certain value
*
* @param mixed $qid
* @param mixed $sAttributeName
* @param mixed $sAttributeValue
*/
function setQuestionAttribute($qid,$sAttributeName,$sAttributeValue)
{
    global $dbprefix,$connect;
    $tablename=$dbprefix.'question_attributes';
    $aInsertArray=array('qid'=>$qid,
                        'attribute'=>$sAttributeName,
                        'value'=>$sAttributeValue);
    $sQuery=$connect->GetInsertSQL($tablename,$aInsertArray);
    $connect->Execute('delete from '.db_table_name('question_attributes')." where qid={$qid} and attribute=".db_quoteall($sAttributeName));
    $connect->Execute($sQuery);
}
// Closing PHP tag intentionally left out - yes, it is okay


