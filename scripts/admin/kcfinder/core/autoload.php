<?php

/** This file is part of KCFinder project
  *
  *      @desc Autoload classes magic function
  *   @package KCFinder
  *   @version 2.21
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

require_once(dirname(__FILE__).'/../../../../config-defaults.php');
require_once(dirname(__FILE__).'/../../../../common.php');
require_once(dirname(__FILE__).'/../../../admin_functions.php');

$usquery = "SELECT stg_value FROM ".db_table_name("settings_global")." where stg_name='SessionName'";
$usresult = db_execute_assoc($usquery,'',true);
if ($usresult)
{
    $usrow = $usresult->FetchRow();
    @session_name($usrow['stg_value']);
}
else
{
    session_name("LimeSurveyAdmin");
}

session_set_cookie_params(0,$relativeurl.'/');

if (session_id() == "") @session_start();

$_SESSION['KCFINDER'] = array();

$sAllowedExtensions = implode(' ',array_map('trim',explode(',',$allowedresourcesuploads)));
$_SESSION['KCFINDER']['types']=array('files'=>$sAllowedExtensions,
                                     'flash'=>$sAllowedExtensions,
                                     'images'=>$sAllowedExtensions);

if ($demoModeOnly === false &&
    isset($_SESSION['loginID']) &&
    isset($_SESSION['FileManagerContext']))
{
    // disable upload at survey creation time
    // because we don't know the sid yet
    if (preg_match('/^(create|edit):(question|group|answer)/',$_SESSION['FileManagerContext']) != 0 ||
        preg_match('/^edit:survey/',$_SESSION['FileManagerContext']) !=0 ||
        preg_match('/^edit:assessments/',$_SESSION['FileManagerContext']) !=0 ||
        preg_match('/^edit:emailsettings/',$_SESSION['FileManagerContext']) != 0)
    {
        $contextarray=explode(':',$_SESSION['FileManagerContext'],3);
        $surveyid=$contextarray[2];



        if(bHasSurveyPermission($surveyid,'surveycontent','update'))
        {
            $_SESSION['KCFINDER']['disabled'] = false ;
            $_SESSION['KCFINDER']['uploadURL'] = "{$relativeurl}/upload/surveys/{$surveyid}/" ;
            $_SESSION['KCFINDER']['uploadDir'] = $uploaddir.'/surveys/'.$surveyid;
        }

    }
    elseif (preg_match('/^edit:label/',$_SESSION['FileManagerContext']) != 0)
    {
        $contextarray=explode(':',$_SESSION['FileManagerContext'],3);
        $labelid=$contextarray[2];
        // check if the user has label management right and labelid defined
        if ($_SESSION['USER_RIGHT_MANAGE_LABEL']==1 && isset($labelid) && $labelid != '')
        {
            $_SESSION['KCFINDER']['disabled'] = false ;
            $_SESSION['KCFINDER']['uploadURL'] = "{$relativeurl}/upload/labels/{$labelid}/" ;
            $_SESSION['KCFINDER']['uploadDir'] = "{$uploaddir}/labels/{$labelid}" ;
        }
    }

}

function kcfinder_autoload($class) {
    if ($class == "uploader")
        require "core/uploader.php";
    elseif ($class == "browser")
        require "core/browser.php";
    elseif (file_exists("core/types/$class.php"))
        require "core/types/$class.php";
    elseif (file_exists("lib/class_$class.php"))
        require "lib/class_$class.php";
    elseif (file_exists("lib/helper_$class.php"))
        require "lib/helper_$class.php";
}

spl_autoload_register('kcfinder_autoload');

?>