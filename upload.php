<?php

require_once(dirname(__FILE__).'/classes/core/startup.php');
require_once(dirname(__FILE__).'/config-defaults.php');
require_once(dirname(__FILE__).'/common.php');
require_once(dirname(__FILE__).'/common_functions.php');
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
        if (isset($_GET['preview']) && $_GET['preview'] == 1)
        {
            @session_name($stg_SessionName);
        }
        else
        {
            @session_name($stg_SessionName.'-runtime-'.$surveyid);
        }
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

    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    $clang = new limesurvey_lang($baselang);

    $randfilename = 'futmp_'.sRandomChars(15);
    $sTempUploadDir = $tempdir.'/upload/';
    $randfileloc = $sTempUploadDir . $randfilename;
    $filename = $_FILES['uploadfile']['name'];
    $size = 0.001 * $_FILES['uploadfile']['size'];
    $valid_extensions = strtolower($_POST['valid_extensions']);
    $maxfilesize = (int) $_POST['max_filesize'];
    $preview = $_POST['preview'];
    $fieldname = $_POST['fieldname'];
    $aFieldMap=createFieldMap($surveyid);
    if (!isset($aFieldMap[$fieldname])) die();
    $aAttributes=getQuestionAttributes($aFieldMap[$fieldname]['qid'],$aFieldMap[$fieldname]['type']);

    $valid_extensions_array = explode(",", $aAttributes['allowed_filetypes']);
    $valid_extensions_array = array_map('trim',$valid_extensions_array);

    $pathinfo = pathinfo($_FILES['uploadfile']['name']);
    $ext = $pathinfo['extension'];

    // check to see that this file type is allowed
    // it is also  checked at the client side, but jst double checking
    if (!in_array(strtolower($ext), $valid_extensions_array))
    {
        $return = array(
                        "success" => false,
                        "msg" => sprintf($clang->gT("Sorry, this file extension (%s) is not allowed!"),$ext)
                    );

        echo json_encode($return);
        exit ();
    }

    // If this is just a preview, don't save the file
    if ($preview)
    {
        if ($size > $maxfilesize)
        {
            $return = array(
                "success" => false,
                "msg" => sprintf($clang->gT("Sorry, this file is too large. Only files upto %s KB are allowed."), $maxfilesize)
            );
            echo json_encode($return);
        }

        else if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $randfileloc))
        {

            $return = array(
                        "success"       => true,
                        "file_index"    => $filecount,
                        "size"          => $size,
                        "name"          => rawurlencode(basename($filename)),
                        "ext"           => $ext,
                        "filename"      => $randfilename,
                        "msg"           => $clang->gT("The file has been successfuly uploaded.")
                    );
            echo json_encode($return);

            // TODO : unlink this file since this is just a preview
            // unlink($randfileloc);
        }
    }
    else
    {    // if everything went fine and the file was uploaded successfuly,
         // send the file related info back to the client
        if ($size > $maxfilesize)
        {
            $return = array(
                "success" => false,
                 "msg" => sprintf($clang->gT("Sorry, this file is too large. Only files up to %s KB are allowed.",'unescaped'), $maxfilesize)
            );
            echo json_encode($return);
        }
        elseif ($iFileUploadTotalSpaceMB>0 && ((fCalculateTotalFileUploadUsage()+($size/1024/1024))>$iFileUploadTotalSpaceMB))
        {
            $return = array(
                "success" => false,
                 "msg" => $clang->gT("We are sorry but there was a system error and your file was not saved. An email has been dispatched to notify the survey administrator.",'unescaped')
            );
            echo json_encode($return);
        }
        elseif (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $randfileloc))
        {


            $return = array(
                "success" => true,
                "size"    => $size,
                "name"    => rawurlencode(basename($filename)),
                "ext"     => $ext,
                "filename"      => $randfilename,
                "msg"     => $clang->gT("The file has been successfuly uploaded.")
            );

            echo json_encode($return);
        }
        // if there was some error, report error message
        else
        {
            // check for upload error
            if ($_FILES['uploadfile']['error'] > 2)
            {
                $return = array(
                                "success" => false,
                                "msg" => $clang->gT("Sorry, there was an error uploading your file")
                            );

                echo json_encode($return);
            }
            // check to ensure that the file does not cross the maximum file size
            else if ( $_FILES['uploadfile']['error'] == 1 ||  $_FILES['uploadfile']['error'] == 2 || $size > $maxfilesize)
            {
                $return = array(
                                "success" => false,
                                "msg" => sprintf($clang->gT("Sorry, this file is too large. Only files upto %s KB are allowed."), $maxfilesize)
                            );

                echo json_encode($return);
            }
            else
            {
                $return = array(
                            "success" => false,
                            "msg" => $clang->gT("Unknown error")
                        );
                echo json_encode($return);
            }
        }
    }
?>