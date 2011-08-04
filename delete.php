<?php

require_once(dirname(__FILE__).'/classes/core/startup.php');
require_once(dirname(__FILE__).'/config-defaults.php');
require_once(dirname(__FILE__).'/common.php');
require_once($homedir.'/classes/core/class.progressbar.php');
require_once(dirname(__FILE__).'/classes/core/language.php');

if (!isset($surveyid))
{
    $surveyid=returnglobal('sid');
}
else
{
    //This next line ensures that the $surveyid value is never anything but a number.
    $surveyid=sanitize_int($surveyid);
}


// Compute the Session name
// Session name is based:
// * on this specific limesurvey installation (Value SessionName in DB)
// * on the surveyid (from Get or Post param). If no surveyid is given we are on the public surveys portal
$usquery = "SELECT stg_value FROM ".db_table_name("settings_global")." where stg_name='SessionName'";
$usresult = db_execute_assoc($usquery,'',true);          //Checked
if ($usresult)
{
    $usrow = $usresult->FetchRow();
    $stg_SessionName=$usrow['stg_value'];
    if ($surveyid)
    {
        @session_name($stg_SessionName.'-runtime-'.$surveyid);
    }
    else
    {
        @session_name($stg_SessionName.'-runtime-publicportal');
    }
}
else
{
    session_name("LimeSurveyRuntime-$surveyid");
}
session_set_cookie_params(0,$relativeurl.'/admin/');
@session_start();
$baselang = GetBaseLanguageFromSurveyID($surveyid);
$clang = new limesurvey_lang($baselang);

if (empty($_SESSION) || !isset($_SESSION['fieldname']))
{
    die("You don't have a valid session !");
}

    $sFieldname = $_GET['fieldname'];
    $sFilename = sanitize_filename($_GET['filename']);
    $sOriginalFileName=sanitize_filename($_GET['name']);
    if (substr($sFilename,0,6)=='futmp_')
    {
        $sFileDir = $tempdir.'/upload/';
    }
    elseif(substr($sFilename,0,3)=='fu_'){
        $sFileDir = "{$uploaddir}/surveys/{$surveyid}/files/";
    }
    else die('Invalid filename');

    $sJSON = $_SESSION[$fieldname];
    $aFiles = json_decode(stripslashes($sJSON),true);

    if(substr($sFilename,0,3)=='fu_'){
        $iFileIndex=0;
        $found=false;
        foreach ($aFiles as $aFile)
        {
           if ($aFile['filename']==$sFilename)
           {
            $found=true;
            break;
           }
           $iFileIndex++;
        }
        if ($found==true) unset($aFiles[$iFileIndex]);
       $_SESSION[$fieldname] = json_encode($aFiles);
    }

    if (@unlink($sFileDir.$sFilename))
    {
       echo sprintf($clang->gT('File %s deleted'), $sOriginalFileName);
    }
    else
        echo $clang->gT('Oops, There was an error deleting the file');

?>