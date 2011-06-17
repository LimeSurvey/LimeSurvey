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

    $file_index = (int)$_GET['file_index'];
    $fieldname = $_GET['fieldname'];
    $filename = "tmp/upload/".$_SESSION[$fieldname]['files'][$file_index]['filename'];
    $name = $_SESSION[$fieldname]['files'][$file_index]['name'];

    $fh = fopen($filename, 'w') or die("can't open file");
    fclose($fh);

    if (unlink($filename))
    {
        echo sprintf($clang->gT('File %s deleted'), rawurldecode($name));
        for ($i = $file_index; $i < $_SESSION[$fieldname]['filecount']; $i++)
        {
            $_SESSION[$fieldname]['files'][$i]['name'] = $_SESSION[$fieldname]['files'][$i + 1]['name'];
            $_SESSION[$fieldname]['files'][$i]['size'] = $_SESSION[$fieldname]['files'][$i + 1]['size'];
            $_SESSION[$fieldname]['files'][$i]['ext']  = $_SESSION[$fieldname]['files'][$i + 1]['ext'];
            $_SESSION[$fieldname]['files'][$i]['filename']   = $_SESSION[$fieldname]['files'][$i + 1]['filename'];
        }
        $_SESSION[$fieldname]['files'][$_SESSION[$fieldname]['filecount']] = NULL;
        $_SESSION[$fieldname]['filecount'] -= 1;
    }
    else
        echo $clang->gT('Oops, There was an error deleting the file');

?>