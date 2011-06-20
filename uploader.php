<?php
require_once(dirname(__FILE__).'/classes/core/startup.php');
require_once(dirname(__FILE__).'/config-defaults.php');
require_once(dirname(__FILE__).'/common.php');
require_once($homedir.'/classes/core/class.progressbar.php');
require_once(dirname(__FILE__).'/classes/core/language.php');

if (isset($_GET['filegetcontents']))
{
    $handle = file_get_contents("tmp/upload/".$_GET['filegetcontents']);
    echo $handle;
    exit();
}

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
session_set_cookie_params(0,$relativeurl.'/');
@session_start();

if (empty($_SESSION) || !isset($_SESSION['fieldname']))
{
    die("You don't have a valid session !");
}

$meta = '<script type="text/javascript">
    var surveyid = "'.$surveyid.'";
    var fieldname = "'.$_GET['fieldname'].'";
    var questgrppreview  = '.$_GET['preview'].';
</script>';

$meta .='<script type="text/javascript" src="scripts/ajaxupload.js"></script>
<script type="text/javascript" src="scripts/uploader.js"></script>
<link type="text/css" href="scripts/uploader.css" rel="stylesheet" />';

$baselang = GetBaseLanguageFromSurveyID($surveyid);
$clang = new limesurvey_lang($baselang);

$header = getHeader($meta);

echo $header;

echo "<script type='text/javascript'>
        var translt = {
             titleFld: '" . $clang->gT('Title') . "',
             commentFld: '" . $clang->gT('Comment') . "',
             errorNoMoreFiles: '" . $clang->gT('Sorry, no more files can be uploaded!') . "',
             errorOnlyAllowed: '" . $clang->gT('Sorry, only %s files can be uploaded for this question!') . "',
             uploading: '" . $clang->gT('Uploading') . "',
             selectfile: '" . $clang->gT('Select file') . "',
             errorNeedMore: '" . $clang->gT('Please upload %s more file(s).') . "',
             errorMoreAllowed: '" . $clang->gT('If you wish, you may upload %s more file(s); else you may return back to survey.') . "',
             errorMaxReached: '" . $clang->gT('The maximum number of files has been uploaded. You may return back to survey.') . "',
             errorTooMuch: '" . $clang->gT('The maximum number of files has been uploaded. You may return back to survey.') . "',
             errorNeedMoreConfirm: '" . $clang->gT("You need to upload %s more files for this question.\nAre you sure you want to exit?") . "'
            };
    </script>\n";

$fn = $_GET['fieldname'];
$qid = $_GET['qid'];
$qidattributes=getQuestionAttributes($qid);

$body = '
        <div id="notice"></div>
        <input type="hidden" id="ia"                value="'.$fn.'" />
        <input type="hidden" id="'.$fn.'_minfiles"          value="'.$qidattributes['min_num_of_files'].'" />
        <input type="hidden" id="'.$fn.'_maxfiles"          value="'.$qidattributes['max_num_of_files'].'" />
        <input type="hidden" id="'.$fn.'_maxfilesize"       value="'.$qidattributes['max_filesize'].'" />
        <input type="hidden" id="'.$fn.'_allowed_filetypes" value="'.$qidattributes['allowed_filetypes'].'" />
        <input type="hidden" id="preview"                   value="'.$_SESSION['preview'].'" />
        <input type="hidden" id="'.$fn.'_show_comment"      value="'.$qidattributes['show_comment'].'" />
        <input type="hidden" id="'.$fn.'_show_title"        value="'.$qidattributes['show_title'].'" />
        <input type="hidden" id="'.$fn.'_licount"           value="0" />
        <input type="hidden" id="'.$fn.'_filecount"         value="0" />

        <!-- The upload button -->
        <div align="center" class="upload-div">
            <button id="button1" class="upload-button" type="button" >'.$clang->gT("Select file").'</button>
        </div>

        <p class="uploadmsg">'.sprintf($clang->gT("You can upload %s under %s KB each."),$qidattributes['allowed_filetypes'],$qidattributes['max_filesize']).'</p>
        <div class="uploadstatus" id="uploadstatus"></div>

        <!-- The list of uploaded files -->
        <ul id="'.$fn.'_listfiles"></ul>

    </body>
</html>';
echo $body;
?>