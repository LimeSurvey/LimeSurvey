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
    var questgrppreview  = '.$_GET['preview'].';
</script>';

$meta .='<script type="text/javascript" src="scripts/ajaxupload.js"></script>
<script type="text/javascript" src="scripts/uploader.js"></script>
<link type="text/css" href="scripts/uploader.css" rel="stylesheet" />';

$baselang = GetBaseLanguageFromSurveyID($surveyid);
$clang = new limesurvey_lang($baselang);

$header = getHeader($meta);

echo $header;

$body = '
        <div id="notice"></div>
        <input type="hidden" id="ia"                value="'.$_SESSION['fieldname'].'" />
        <input type="hidden" id="minfiles"          value="'.$_SESSION['minfiles'].'" />
        <input type="hidden" id="maxfiles"          value="'.$_SESSION['maxfiles'].'" />
        <input type="hidden" id="maxfilesize"       value="'.$_SESSION['maxfilesize'].'" />
        <input type="hidden" id="allowed_filetypes" value="'.$_SESSION['allowed_filetypes'].'" />
        <input type="hidden" id="preview"           value="'.$_SESSION['preview'].'" />
        <input type="hidden" id="show_comment"      value="'.$_SESSION['show_comment'].'" />
        <input type="hidden" id="show_title"        value="'.$_SESSION['show_title'].'" />
        <input type="hidden" id="licount"           value="0" />
        <input type="hidden" id="filecount"         value="0" />

        <!-- The upload button -->
        <div align="center" class="upload-div">
            <button id="button1" class="upload-button" type="button" >Select file</button>
        </div>
        
        <p class="uploadmsg">You can upload '.$_SESSION['allowed_filetypes'].' under '.$_SESSION['maxfilesize'].' KB each</p>
        <div class="uploadstatus" id="uploadstatus"></div>

        <!-- The list of uploaded files -->
        <ul id="listfiles"></ul>

    </body>
</html>';
echo $body;
?>
