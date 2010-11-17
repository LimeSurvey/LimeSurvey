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

if (empty($_SESSION) || !isset($_SESSION['fieldname']))
{
    die("You don't have a valid session !");
}

    $file_index = $_GET['file_index'];

    $fileid = "upload/tmp/".$_SESSION['files'][$file_index]['id'];
    $filename = $_SESSION['files'][$file_index]['name'];
    $fh = fopen($fileid, 'w') or die("can't open file");
    fclose($fh);

    if (unlink($fileid))
    {
        echo 'File '.rawurldecode($filename).' deleted';
        for ($i = $file_index; $i < $_SESSION['filecount']; $i++)
        {
            $_SESSION['files'][$i]['name'] = $_SESSION['files'][$i + 1]['name'];
            $_SESSION['files'][$i]['size'] = $_SESSION['files'][$i + 1]['size'];
            $_SESSION['files'][$i]['ext']  = $_SESSION['files'][$i + 1]['ext'];
            $_SESSION['files'][$i]['id']   = $_SESSION['files'][$i + 1]['id'];
        }
        $_SESSION['files'][$_SESSION['filecount']] = NULL;
        $_SESSION['filecount'] -= 1;
    }
    else
        echo 'Oops, There was an error deleting the file';

?>