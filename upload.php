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

    $randfilename = randomkey(15);
    $uploaddir = 'tmp/upload/';
    $randfileloc = $uploaddir . $randfilename;
    $filename = $_FILES['uploadfile']['name'];
    $size = 0.001 * $_FILES['uploadfile']['size'];
    $valid_extensions = strtolower($_POST['valid_extensions']);
    $maxfilesize = $_POST['maxfilesize'];
    $preview = $_POST['preview'];

    $valid_extensions_array = explode(",", $valid_extensions);

    for ($i = 0; $i < count($valid_extensions_array); $i++)
        $valid_extensions_array[$i] = trim($valid_extensions_array[$i]);
    
    $pathinfo = pathinfo($_FILES['uploadfile']['name']);
    $ext = $pathinfo['extension'];

    // check to see that this file type is allowed
    // it is also  checked at the client side, but jst double checking
    if (!in_array(strtolower($ext), $valid_extensions_array))
    {
        $return = array(
                        "success" => false,
                        "msg" => "Sorry, This file extension (".$ext.") is not allowed !"
                    );

        echo json_encode($return);
    }

    // If this is just a preview, don't save the file
    if ($preview)
    {
        if ($size > $maxfilesize)
        {
            $return = array(
                "success" => false,
                "msg" => "Sorry, This file is too large. Only files upto ".$maxfilesize." KB are allowed"
            );
            echo json_encode($return);
        }

        else if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $randfileloc))
        {
            if (!isset($_SESSION['filecount']))
                $_SESSION['filecount'] = 0;

            $_SESSION['filecount'] += 1;
            $_SESSION['files'][$_SESSION['filecount']]['name'] = rawurlencode(basename($filename));
            $_SESSION['files'][$_SESSION['filecount']]['size'] = $size;
            $_SESSION['files'][$_SESSION['filecount']]['ext']  = $ext;
            $_SESSION['files'][$_SESSION['filecount']]['filename']   = $randfilename;

            $return = array(
                        "success" => true,
                        "size"    => $size,
                        "name"    => rawurlencode(basename($filename)),
                        "ext"     => $ext,
                        "filename"      => $randfilename,
                        "msg"     => "The file has been successfuly uploaded."
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
                "msg" => "Sorry, This file is too large. Only files upto ".$maxfilesize." KB are allowed"
            );
            echo json_encode($return);
        }

        if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $randfileloc))
        {
            if (!isset($_SESSION['filecount']))
                $_SESSION['filecount'] = 0;

            $_SESSION['filecount'] += 1;
            $_SESSION['files'][$_SESSION['filecount']]['name'] = rawurlencode(basename($filename));
            $_SESSION['files'][$_SESSION['filecount']]['size'] = $size;
            $_SESSION['files'][$_SESSION['filecount']]['ext']  = $ext;
            $_SESSION['files'][$_SESSION['filecount']]['filename']   = $randfilename;

            $return = array(
                "success" => true,
                "file_index" => $_SESSION['filecount'],
                "size"    => $size,
                "name"    => rawurlencode(basename($filename)),
                "ext"     => $ext,
                "filename"      => $randfilename,
                "msg"     => "The file has been successfuly uploaded"
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
                                "msg" => "Sorry, there was an error uplodaing your file"
                            );

                echo json_encode($return);
            }
            // check to ensure that the file does not cross the maximum file size
            else if ( $_FILES['uploadfile']['error'] == 1 ||  $_FILES['uploadfile']['error'] == 2 || $size > $maxfilesize)
            {
                $return = array(
                                "success" => false,
                                "msg" => "Sorry, This file is too large. Only files upto ".$maxfilesize." KB are allowed"
                            );

                echo json_encode($return);
            }
            else
            {
                $return = array(
                            "success" => false,
                            "msg" => "Unknown error"
                        );
                echo json_encode($return);
            }
        }
    }
?>