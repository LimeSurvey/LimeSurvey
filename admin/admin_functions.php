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
 *	$Id$
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
 * Returns true if a user has permissions in the particular survey
 *
 * @param $iSID The survey ID
 * @param $sPermission
 * @param $sCRUD
 * @param $iUID User ID - if not given the one of the current user is used
 * @return bool
 */
function bHasSurveyPermission($iSID, $sPermission, $sCRUD, $iUID=null)
{
    global $dbprefix, $connect;
    if (!in_array($sCRUD,array('create','read','update','delete','import','export'))) return false;
    $sCRUD=$sCRUD.'_p';
    $iSID = (int)$iSID;
    global $aSurveyPermissionCache;
    
    if (is_null($iUID))
    {
      if (isset($_SESSION['loginID'])) $iUID = $_SESSION['loginID']; 
       else return false;
      if ($_SESSION['USER_RIGHT_SUPERADMIN']==1) return true; //Superadmin has access to all
    }

    if (!isset($aSurveyPermissionCache[$iSID][$iUID][$sPermission][$sCRUD]))
    {
        $sSQL = "SELECT {$sCRUD} FROM " . db_table_name('survey_permissions') . " 
                WHERE sid={$iSID} AND uid = {$iUID}
                and permission=".db_quoteall($sPermission); //Getting rights for this survey
        $bPermission = $connect->GetOne($sSQL);
        if ($bPermission==0 || is_null($bPermission)) $bPermission=false;
        if ($bPermission==1) $bPermission=true;
        $aSurveyPermissionCache[$iSID][$iUID][$sPermission][$sCRUD]=$bPermission;
    }
    return $aSurveyPermissionCache[$iSID][$iUID][$sPermission][$sCRUD];
}

/**
 * Returns true if the given survey has a File Upload Question Type
 * @param $surveyid The survey ID
 * @return bool
 */
function bHasFileUploadQuestion($surveyid) {
    $fieldmap = createFieldMap($surveyid);

    foreach ($fieldmap as $field) {
        foreach ($field as $key => $value) {
            if ($value == "|")
                return true;
        }
    }
}

/**
 * Returns true if a user has global permission for a certain action. Available permissions are
 * 
 * USER_RIGHT_CREATE_SURVEY
 * USER_RIGHT_CONFIGURATOR
 * USER_RIGHT_CREATE_USER
 * USER_RIGHT_DELETE_USER
 * USER_RIGHT_SUPERADMIN
 * USER_RIGHT_MANAGE_TEMPLATE
 * USER_RIGHT_MANAGE_LABEL
 *
 * @param $sPermission
 * @return bool
 */
function bHasGlobalPermission($sPermission)
{
    global $dbprefix, $connect;
    global $aSurveyGlobalPermissionCache;

    if (isset($_SESSION['loginID'])) $iUID = $_SESSION['loginID']; 
        else return false;
    if ($_SESSION['USER_RIGHT_SUPERADMIN']==1) return true; //Superadmin has access to all
    if ($_SESSION[$sPermission]==1)
    {
        return true;
    }
    else
    {
        return false;
    }

}

/**
* Set the survey permissions for a user. Beware that all survey permissions for the particual survey are removed before the new ones are written.
* 
* @param int $iUserID The User ID
* @param int $iSurveyID The Survey ID 
* @param array $aPermissions  Array with permissions in format <permissionname>=>array('create'=>0/1,'read'=>0/1,'update'=>0/1,'delete'=>0/1)
*/
function SetSurveyPermissions($iUserID, $iSurveyID, $aPermissions)
{
    global $connect, $surveyid;
    $iUserID=sanitize_int($iUserID);
    $sQuery = "delete from ".db_table_name('survey_permissions')." WHERE sid = {$iSurveyID} AND uid = {$iUserID}";
    $connect->Execute($sQuery);
    $bResult=true;
    
    foreach($aPermissions as $sPermissionname=>$aPermissions)
    {
        if (!isset($aPermissions['create'])) {$aPermissions['create']=0;}
        if (!isset($aPermissions['read'])) {$aPermissions['read']=0;}
        if (!isset($aPermissions['update'])) {$aPermissions['update']=0;}
        if (!isset($aPermissions['delete'])) {$aPermissions['delete']=0;}
        if (!isset($aPermissions['import'])) {$aPermissions['import']=0;}
        if (!isset($aPermissions['export'])) {$aPermissions['export']=0;}
        if ($aPermissions['create']==1 || $aPermissions['read']==1 ||$aPermissions['update']==1 || $aPermissions['delete']==1  || $aPermissions['import']==1  || $aPermissions['export']==1)
        {
            $sQuery = "INSERT INTO ".db_table_name('survey_permissions')." (sid, uid, permission, create_p, read_p, update_p, delete_p, import_p, export_p)
                       VALUES ({$iSurveyID},{$iUserID},'{$sPermissionname}',{$aPermissions['create']},{$aPermissions['read']},{$aPermissions['update']},{$aPermissions['delete']},{$aPermissions['import']},{$aPermissions['export']})";
            $bResult=$connect->Execute($sQuery);
        }
    }
    return $bResult;
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


