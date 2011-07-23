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
 * $Id$
 */

// Security Checked: POST, GET, SESSION, REQUEST, returnglobal, DB

require_once(dirname(__FILE__).'/classes/core/startup.php');


require_once(dirname(__FILE__).'/config-defaults.php');
require_once(dirname(__FILE__).'/common.php');
require_once(dirname(__FILE__).'/classes/core/language.php');
@ini_set('session.gc_maxlifetime', $sessionlifetime);

$loadname=returnglobal('loadname');
$loadpass=returnglobal('loadpass');
$scid=returnglobal('scid');
$thisstep=returnglobal('thisstep');
$move=sanitize_paranoid_string(returnglobal('move'));
$clienttoken=sanitize_token(returnglobal('token'));


if (!isset($thisstep))
{
    $thisstep = "";
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

//DEFAULT SETTINGS FOR TEMPLATES
if (!$publicdir)
{
    $publicdir=".";
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
session_set_cookie_params(0,$relativeurl.'/');
if (!isset($_SESSION) || empty($_SESSION)) // the $_SESSION variable can be empty if register_globals is on
	@session_start();



// First check if survey is active
// if not: copy some vars from the admin session
// to a new user session

if ($surveyid)
{
    $issurveyactive=false;
    $aRow=$connect->GetRow("SELECT * FROM ".db_table_name('surveys')." WHERE sid=$surveyid");
    if (isset($aRow['active']))
    {
        $surveyexists=true;
        if($aRow['active']=='Y')
        {
        $issurveyactive=true;
    }
}
    else
    {
        $surveyexists=false;
    }
}

if ($clienttoken != '' && isset($_SESSION['token']) &&
$clienttoken != $_SESSION['token'])
{
    require_once(dirname(__FILE__).'/classes/core/language.php');
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    $clang = new limesurvey_lang($baselang);
    // Let's first regenerate a session id
    killSession();
    // Let's redirect the client to the same URL after having reseted the session
    header("Location: $rooturl/index.php?" .$_SERVER['QUERY_STRING']);
    sendcacheheaders();
    doHeader();

	echo templatereplace(file_get_contents("$standardtemplaterootdir/default/startpage.pstpl"));
    echo "\t<div id='wrapper'>\n"
    ."\t<p id='tokenmessage'>\n"
    ."\t<span class='error'>".$clang->gT("Token mismatch")."</span><br /><br />\n"
    ."\t".$clang->gT("The token you provided doesn't match the one in your session.")."<br /><br />\n"
    ."\t".$clang->gT("Please wait to begin with a new session.")."<br /><br />\n"
    ."\t</p>\n"
    ."\t</div>\n";

	echo templatereplace(file_get_contents("$standardtemplaterootdir/default/endpage.pstpl"));
    doFooter();
    exit;
}

if (isset($_SESSION['finished']) && $_SESSION['finished'] === true)
{
    require_once(dirname(__FILE__).'/classes/core/language.php');
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    $clang = new limesurvey_lang($baselang);
    // Let's first regenerate a session id
    killSession();
    // Let's redirect the client to the same URL after having reseted the session
    header("Location: $rooturl/index.php?" .$_SERVER['QUERY_STRING']);
    sendcacheheaders();
    doHeader();

	echo templatereplace(file_get_contents("$standardtemplaterootdir/default/startpage.pstpl"));
    echo "\t<div id='wrapper'>\n"
    ."\t<p id='tokenmessage'>\n"
    ."\t<span class='error'>".$clang->gT("Previous session is set to be finished.")."</span><br /><br />\n"
    ."\t".$clang->gT("Your browser reports that it was used previously to answer this survey. We are resetting the session so that you can start from the beginning.")."<br /><br />\n"
    ."\t".$clang->gT("Please wait to begin with a new session.")."<br /><br />\n"
    ."\t</p>\n"
    ."\t</div>\n";

	echo templatereplace(file_get_contents("$standardtemplaterootdir/default/endpage.pstpl"));
    doFooter();
    exit;
}
$previewgrp = false;
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'previewgroup')){
	$rightquery="SELECT uid FROM {$dbprefix}survey_permissions WHERE sid=".db_quote($surveyid)." AND uid = ".db_quote($_SESSION['loginID'].' group by uid');
	$rightresult = db_execute_assoc($rightquery);
	if ($rightresult->RecordCount() > 0 || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
		$previewgrp = true;
	}
}

if (($surveyid &&
$issurveyactive===false && $surveyexists &&
isset ($surveyPreview_require_Auth) &&
$surveyPreview_require_Auth == true) &&  $previewgrp == false)
{
    // admin session and permission have not already been imported
    // for this particular survey
    if ( !isset($_SESSION['USER_RIGHT_PREVIEW']) ||
    $_SESSION['USER_RIGHT_PREVIEW'] != $surveyid)
    {
        // Store initial session name
        $initial_session_name=session_name();

        // One way (not implemented here) would be to start the
        // user session from a duplicate of the admin session
        // - destroy the new session
        // - load admin session (with correct session name)
        // - close admin session
        // - change used session name to default
        // - open new session (takes admin session id)
        // - regenerate brand new session id for this session

        // The solution implemented here is to copy some
        // fields from the admin session to the new session
        // - first destroy the new (empty) user session
        // - then open admin session
        // - record interresting values from the admin session
        // - duplicate admin session under another name and Id
        // - destroy the duplicated admin session
        // - start a brand new user session
        // - copy interresting values in this user session

        @session_destroy();	// make it silent because for
        // some strange reasons it fails sometimes
        // which is not a problem
        // but if it throws an error then future
        // session functions won't work because
        // headers are already sent.
        if (isset($stg_SessionName) && $stg_SessionName)
        {
            @session_name($stg_SessionName);
        }
        else
        {
            session_name("LimeSurveyAdmin");
        }
        session_start(); // Loads Admin Session

        $previewright=false;
        $savesessionvars=Array();
        if (isset($_SESSION['loginID']))
        {
            $rightquery="SELECT uid FROM {$dbprefix}survey_permissions WHERE sid=".db_quote($surveyid)." AND uid = ".db_quote($_SESSION['loginID'].' group by uid');
            $rightresult = db_execute_assoc($rightquery);      //Checked

            // Currently it is enough to be listed in the survey
            // user operator list to get preview access
            if ($rightresult->RecordCount() > 0 || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
            {
                $previewright=true;
                $savesessionvars["USER_RIGHT_PREVIEW"]=$surveyid;
                $savesessionvars["loginID"]=$_SESSION['loginID'];
                $savesessionvars["user"]=$_SESSION['user'];
            }
        }

        // change session name and id
        // then delete this new session
        // ==> the original admin session remains valid
        // ==> it is possible to start a new session
        session_name($initial_session_name);
        if ($sessionhandler=='db')
        {
            adodb_session_regenerate_id();
        }
        elseif (session_regenerate_id() === false)
        {
            safe_die("Error Regenerating Session Id");
        }
        @session_destroy();

        // start new session
        @session_start();
        // regenerate id so that the header geenrated by previous
        // regenerate_id is overwritten
        // needed after clearall
        if ($sessionhandler=='db')
        {
            adodb_session_regenerate_id();
        }
        elseif (session_regenerate_id() === false)
        {
            safe_die("Error Regenerating Session Id");
        }

        if ( $previewright === true)
        {
            foreach ($savesessionvars as $sesskey => $sessval)
            {
                $_SESSION[$sesskey]=$sessval;
            }
        }
    }
    else
    { // already authorized
        $previewright = true;
    }

    if ($previewright === false)
    {
        // print an error message
        if (isset($_REQUEST['rootdir']))
        {
            safe_die('You cannot start this script directly');
        }
        require_once(dirname(__FILE__).'/classes/core/language.php');
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $clang = new limesurvey_lang($baselang);
        //A nice exit
        sendcacheheaders();
        doHeader();

		echo templatereplace(file_get_contents("$standardtemplaterootdir/default/startpage.pstpl"));
        echo "\t<div id='wrapper'>\n"
        ."\t<p id='tokenmessage'>\n"
        ."\t<span class='error'>".$clang->gT("ERROR")."</span><br /><br />\n"
        ."\t".$clang->gT("We are sorry but you don't have permissions to do this.")."<br /><br />\n"
        ."\t".sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$siteadminname,encodeEmail($siteadminemail))."<br /><br />\n"
        ."\t</p>\n"
        ."\t</div>\n";

		echo templatereplace(file_get_contents("$standardtemplaterootdir/default/endpage.pstpl"));
        doFooter();
        exit;
    }
}
if (isset($_SESSION['srid']))
{
    $saved_id = $_SESSION['srid'];
}

if (!isset($_SESSION['s_lang'])  && (isset($move)) )
// geez ... a session time out! RUN!
{
    if (isset($_REQUEST['rootdir']))
    {
        safe_die('You cannot start this script directly');
    }
    require_once(dirname(__FILE__).'/classes/core/language.php');
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    $clang = new limesurvey_lang($baselang);
    //A nice exit
    sendcacheheaders();
    doHeader();

	echo templatereplace(file_get_contents("$standardtemplaterootdir/default/startpage.pstpl"));
    echo "\t<div id='wrapper'>\n"
    ."\t<p id='tokenmessage'>\n"
    ."\t<span class='error'>".$clang->gT("ERROR")."</span><br /><br />\n"
    ."\t".$clang->gT("We are sorry but your session has expired.")."<br /><br />\n"
    ."\t".$clang->gT("Either you have been inactive for too long, you have cookies disabled for your browser, or there were problems with your connection.")."<br /><br />\n"
    ."\t".sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$siteadminname,$siteadminemail)."<br /><br />\n"
    ."\t</p>\n"
    ."\t</div>\n";

	echo templatereplace(file_get_contents("$standardtemplaterootdir/default/endpage.pstpl"));
    doFooter();
    exit;
};

// Set the language of the survey, either from POST, GET parameter of session var
if (isset($_POST['lang']) && $_POST['lang']!='')  // this one comes from the language question
{
    $templang = sanitize_languagecode($_POST['lang']);
    $clang = SetSurveyLanguage( $surveyid, $templang);
    UpdateSessionGroupList($templang);  // to refresh the language strings in the group list session variable

    UpdateFieldArray();        // to refresh question titles and question text
}
else
if (isset($_GET['lang']) && $surveyid)
{
    $templang = sanitize_languagecode($_GET['lang']);
    $clang = SetSurveyLanguage( $surveyid, $templang);
    UpdateSessionGroupList($templang);  // to refresh the language strings in the group list session variable
    UpdateFieldArray();        // to refresh question titles and question text
}

if (isset($_SESSION['s_lang']))
{
    $clang = SetSurveyLanguage( $surveyid, $_SESSION['s_lang']);
}
elseif (isset($surveyid) && $surveyid)
{
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    $clang = SetSurveyLanguage( $surveyid, $baselang);
}
else
{
    $baselang=$defaultlang;
}

if (isset($_REQUEST['embedded_inc']))
{
    safe_die('You cannot start this script directly');
}


//CHECK FOR REQUIRED INFORMATION (sid)
if (!$surveyid)
{
    if(isset($_GET['lang']))
    {
        $baselang = sanitize_languagecode($_GET['lang']);
    }
    elseif (!isset($baselang))
    {
        $baselang=$defaultlang;
    }
    $clang = new limesurvey_lang($baselang);
    if(!isset($defaulttemplate))
    {
        $defaulttemplate="default";
    }
    $languagechanger = makelanguagechanger();
    //Find out if there are any publicly available surveys
    $query = "SELECT a.sid, b.surveyls_title, a.publicstatistics
	          FROM ".db_table_name('surveys')." AS a
			  INNER JOIN ".db_table_name('surveys_languagesettings')." AS b
			  ON ( surveyls_survey_id = a.sid )
			  WHERE surveyls_language='$baselang'
			  AND a.active='Y'
			  AND a.listpublic='Y'
			  AND ((a.expires >= '".date("Y-m-d H:i")."') OR (a.expires is null))
              AND ((a.startdate <= '".date("Y-m-d H:i")."') OR (a.startdate is null))
			  ORDER BY surveyls_title";
    $result = db_execute_assoc($query,false,true) or die("Could not connect to database. If you try to install LimeSurvey please refer to the <a href='http://docs.limesurvey.org'>installation docs</a> and/or contact the system administrator of this webpage."); //Checked
    $list=array();
    if($result->RecordCount() > 0)
    {
        while($rows = $result->FetchRow())
        {
            $link = "<li><a href='$rooturl/index.php?sid=".$rows['sid'];
            if (isset($_GET['lang']))
            {
                $link .= "&lang=".sanitize_languagecode($_GET['lang']);
            }
            if (isset($_GET['lang']))
            {
                $link .= "&amp;lang=".sanitize_languagecode($_GET['lang']);
            }
            $link .= "'  class='surveytitle'>".$rows['surveyls_title']."</a>\n";
            if ($rows['publicstatistics'] == 'Y') $link .= "<a href='{$relativeurl}/statistics_user.php?sid={$rows['sid']}'>(".$clang->gT('View statistics').")</a>";
            $link .= "</li>\n";
            $list[]=$link;
        }
    }
    if(count($list) < 1)
    {
        $list[]="<li class='surveytitle'>".$clang->gT("No available surveys")."</li>";
    }
    $surveylist=array(
            "nosid"=>$clang->gT("You have not provided a survey identification number"),
            "contact"=>sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$siteadminname,encodeEmail($siteadminemail)),
            "listheading"=>$clang->gT("The following surveys are available:"),
            "list"=>implode("\n",$list),
    );

    $thissurvey['name']=$sitename;
    $thissurvey['templatedir']=$defaulttemplate;

    //A nice exit
    sendcacheheaders();
    doHeader();
	echo templatereplace(file_get_contents(sGetTemplatePath($defaulttemplate)."/startpage.pstpl"));

	echo templatereplace(file_get_contents(sGetTemplatePath($defaulttemplate)."/surveylist.pstpl"));

	echo templatereplace(file_get_contents(sGetTemplatePath($defaulttemplate)."/endpage.pstpl"));
    doFooter();
    exit;
}

// Get token
if (!isset($token))
{
    $token=$clienttoken;
}

//GET BASIC INFORMATION ABOUT THIS SURVEY
$totalBoilerplatequestions =0;
$thissurvey=getSurveyInfo($surveyid, $_SESSION['s_lang']);

if (isset($_GET['newtest']) && $_GET['newtest'] == "Y")
{
	//Removes any existing timer cookies so timers will start again
    setcookie ("limesurvey_timers", "", time() - 3600);
}



//SEE IF SURVEY USES TOKENS AND GROUP TOKENS
$i = 0; //$tokensexist = 0;
if ($surveyexists == 1 && tableExists('tokens_'.$thissurvey['sid']))
{
    $tokensexist = 1;

}
else
{
    $tokensexist = 0;
    unset ($_POST['token']);
    unset ($_GET['token']);
    unset($token);
    unset($clienttoken);
}




//SET THE TEMPLATE DIRECTORY
if (!$thissurvey['templatedir'])
{
    $thistpl=sGetTemplatePath($defaulttemplate);
}
else
{
    $thistpl=sGetTemplatePath($thissurvey['templatedir']);
}



//MAKE SURE SURVEY HASN'T EXPIRED
if ($thissurvey['expiry']!='' and date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)>$thissurvey['expiry'] && $thissurvey['active']!='N')
{

    sendcacheheaders();
    doHeader();

	echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
    echo "\t<div id='wrapper'>\n"
    ."\t<p id='tokenmessage'>\n"
    ."\t".$clang->gT("This survey is no longer available.")."<br /><br />\n"
    ."\t".sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail']).".<br /><br />\n"
	."\t</p>\n"
    ."\t</div>\n";

	echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
    doFooter();
    exit;
}

//MAKE SURE SURVEY IS ALREADY VALID
if ($thissurvey['startdate']!='' and  date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)<$thissurvey['startdate'] && $thissurvey['active']!='N')
{
    sendcacheheaders();
    doHeader();

    echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
    echo "\t<div id='wrapper'>\n"
    ."\t<p id='tokenmessage'>\n"
    ."\t".$clang->gT("This survey is not yet started.")."<br /><br />\n"
    ."\t".sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail']).".<br /><br />\n"
    ."\t</p>\n"
    ."\t</div>\n";

    echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
    doFooter();
    exit;
}

//CHECK FOR PREVIOUSLY COMPLETED COOKIE
//If cookies are being used, and this survey has been completed, a cookie called "PHPSID[sid]STATUS" will exist (ie: SID6STATUS) and will have a value of "COMPLETE"
$cookiename="PHPSID".returnglobal('sid')."STATUS";
if (isset($_COOKIE[$cookiename]) && $_COOKIE[$cookiename] == "COMPLETE" && $thissurvey['usecookie'] == "Y" && $tokensexist != 1 && (!isset($_GET['newtest']) || $_GET['newtest'] != "Y"))
{
    sendcacheheaders();
    doHeader();

	echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
    echo "\t<div id='wrapper'>\n"
    ."\t<p id='tokenmessage'>\n"
    ."\t<span class='error'>".$clang->gT("Error")."</span><br /><br />\n"
    ."\t".$clang->gT("You have already completed this survey.")."<br /><br />\n"
    ."\t".sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])."\n"
    ."\t</p>\n"
    ."\t</div>\n";

	echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
    doFooter();
    exit;
}




//CHECK IF SURVEY ID DETAILS HAVE CHANGED
if (isset($_SESSION['oldsid']))
{
    $oldsid=$_SESSION['oldsid'];
}

if (!isset($oldsid))
{
    $_SESSION['oldsid'] = $surveyid;
}

if (isset($oldsid) && $oldsid && $oldsid != $surveyid)
{
    $savesessionvars=Array();
    if (isset($_SESSION['USER_RIGHT_PREVIEW']))
    {
        $savesessionvars["USER_RIGHT_PREVIEW"]=$surveyid;
        $savesessionvars["loginID"]=$_SESSION['loginID'];
        $savesessionvars["user"]=$_SESSION['user'];
    }
    session_unset();
    $_SESSION['oldsid']=$surveyid;
    foreach ($savesessionvars as $sesskey => $sessval)
    {
        $_SESSION[$sesskey]=$sessval;
    }
}



if (isset($_GET['loadall']) && $_GET['loadall'] == "reload")
{
    if (returnglobal('loadname') && returnglobal('loadpass'))
    {
        $_POST['loadall']="reload";
    }
}

//LOAD SAVED SURVEY
if (isset($_POST['loadall']) && $_POST['loadall'] == "reload")
{
    $errormsg="";
    // if (loadname is not set) or if ((loadname is set) and (loadname is NULL))
    if (!isset($loadname) || (isset($loadname) && ($loadname == null)))
    {
        $errormsg .= $clang->gT("You did not provide a name")."<br />\n";
    }
    // if (loadpass is not set) or if ((loadpass is set) and (loadpass is NULL))
    if (!isset($loadpass) || (isset($loadpass) && ($loadpass == null)))
    {
        $errormsg .= $clang->gT("You did not provide a password")."<br />\n";
    }

    // if security question answer is incorrect
    // Not called if scid is set in GET params (when using email save/reload reminder URL)
    if (function_exists("ImageCreate") && captcha_enabled('saveandloadscreen',$thissurvey['usecaptcha']))
    {
        if ( (!isset($_POST['loadsecurity']) ||
        !isset($_SESSION['secanswer']) ||
        $_POST['loadsecurity'] != $_SESSION['secanswer']) &&
        !isset($_GET['scid']))
        {
            $errormsg .= $clang->gT("The answer to the security question is incorrect.")."<br />\n";
        }
    }

    // Load session before loading the values from the saved data
    if (isset($_GET['loadall']))
    {
        buildsurveysession();
    }

    $_SESSION['holdname']=$loadname; //Session variable used to load answers every page.
    $_SESSION['holdpass']=$loadpass; //Session variable used to load answers every page.

    if ($errormsg == "") loadanswers();
    $move = "movenext";

    if ($errormsg)
    {
        $_POST['loadall'] = $clang->gT("Load Unfinished Survey");
    }
}
//Allow loading of saved survey
if (isset($_POST['loadall']) && $_POST['loadall'] == $clang->gT("Load Unfinished Survey"))
{
    require_once("load.php");
}


//Check if TOKEN is used for EVERY PAGE
//This function fixes a bug where users able to submit two surveys/votes
//by checking that the token has not been used at each page displayed.
// bypass only this check at first page (Step=0) because
// this check is done in buildsurveysession and error message
// could be more interresting there (takes into accound captcha if used)
if ($tokensexist == 1 && isset($token) && $token &&
	isset($_SESSION['step']) && $_SESSION['step']>0 && db_tables_exist($dbprefix.'tokens_'.$surveyid))
{
	//check if tokens actually haven't been already used
	$areTokensUsed = usedTokens(db_quote(trim(strip_tags(returnglobal('token')))));
	// check if token actually does exist
	// check also if it is allowed to change survey after completion
	if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
    	$tkquery = "SELECT * FROM ".db_table_name('tokens_'.$surveyid)." WHERE token='".db_quote($token)."' ";
	} else {
    	$tkquery = "SELECT * FROM ".db_table_name('tokens_'.$surveyid)." WHERE token='".db_quote($token)."' AND (completed = 'N' or completed='')";
    }
    $tkresult = db_execute_num($tkquery); //Checked
    $tokendata = $tkresult->FetchRow();
    if ($tkresult->RecordCount()==0 || ($areTokensUsed && $thissurvey['alloweditaftercompletion'] != 'Y'))
    {
        sendcacheheaders();
        doHeader();
        //TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

        echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
        echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));
        echo "\t<div id='wrapper'>\n"
        ."\t<p id='tokenmessage'>\n"
        ."\t".$clang->gT("This is a controlled survey. You need a valid token to participate.")."<br /><br />\n"
        ."\t".$clang->gT("The token you have provided is either not valid, or has already been used.")."\n"
        ."\t".sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname']
        ." (<a href='mailto:{$thissurvey['adminemail']}'>"
        ."{$thissurvey['adminemail']}</a>)")."\n"
        ."\t</p>\n"
        ."\t</div>\n";

        echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
	    killSession();
        doFooter();
        exit;
}
}
if ($tokensexist == 1 && isset($token) && $token && db_tables_exist($dbprefix.'tokens_'.$surveyid)) //check if token is in a valid time frame
{

	// check also if it is allowed to change survey after completion
	if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
        $tkquery = "SELECT * FROM ".db_table_name('tokens_'.$surveyid)." WHERE token='".db_quote($token)."' ";
    } else {
        $tkquery = "SELECT * FROM ".db_table_name('tokens_'.$surveyid)." WHERE token='".db_quote($token)."' AND (completed = 'N' or completed='')";
    }
    $tkresult = db_execute_assoc($tkquery); //Checked
    $tokendata = $tkresult->FetchRow();
    if ((trim($tokendata['validfrom'])!='' && $tokendata['validfrom']>date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)) ||
    (trim($tokendata['validuntil'])!='' && $tokendata['validuntil']<date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)))
    {
        sendcacheheaders();
        doHeader();
        //TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

        echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
        echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));
        echo "\t<div id='wrapper'>\n"
        ."\t<p id='tokenmessage'>\n"
        ."\t".$clang->gT("We are sorry but you are not allowed to enter this survey.")."<br /><br />\n"
        ."\t".$clang->gT("Your token seems to be valid but can be used only during a certain time period.")."<br />\n"
        ."\t".sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname']
        ." (<a href='mailto:{$thissurvey['adminemail']}'>"
        ."{$thissurvey['adminemail']}</a>)")."\n"
        ."\t</p>\n"
        ."\t</div>\n";

        echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
        doFooter();
	    killSession();
        exit;
    }
}



//Clear session and remove the incomplete response if requested.
if (isset($_GET['move']) && $_GET['move'] == "clearall")
{
    $s_lang = $_SESSION['s_lang'];
    if (isset($_SESSION['srid']))
    {
        // find out if there are any fuqt questions - checked
        $fieldmap = createFieldMap($surveyid);
        foreach ($fieldmap as $field)
        {
            if ($field['type'] == "|" && !strpos($field['fieldname'], "_filecount"))
            {
                if (!isset($qid)) { $qid = array(); }
                $qid[] = $field['fieldname'];
            }
        }

        // if yes, extract the response json to those questions
        if (isset($qid))
        {
            $query = "SELECT * FROM ".db_table_name("survey_".$surveyid)." WHERE id=".$_SESSION['srid'];
            $result = db_execute_assoc($query);
            while ($row = $result->FetchRow())
            {
                foreach ($qid as $question)
                {
                    $json = $row[$question];
                    if ($json == "" || $json == NULL)
                        continue;

                    // decode them
                    $phparray = json_decode($json);

                    foreach ($phparray as $metadata)
                    {
                        $target = "{$uploaddir}/surveys/{$surveyid}/files/";
                        // delete those files
                        unlink($target.$metadata->filename);
                    }
                }
            }
        }
        // done deleting uploaded files


        // delete the response but only if not already completed
        $connect->query('DELETE FROM '.db_table_name('survey_'.$surveyid).' WHERE id='.$_SESSION['srid']." AND submitdate IS NULL");

        // also delete a record from saved_control when there is one
        $connect->query('DELETE FROM '.db_table_name('saved_control'). ' WHERE srid='.$_SESSION['srid'].' AND sid='.$surveyid);
    }
    session_unset();
    session_destroy();
    setcookie(session_name(),"EXPIRED",time()-120);
    sendcacheheaders();
    if (isset($_GET['redirect']))
    {
        session_write_close();
        header("Location: {$_GET['redirect']}");
    }
    doHeader();
    echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
    echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n"
    ."\t<script type='text/javascript'>\n"
    ."\t<!--\n"
    ."function checkconditions(value, name, type)\n"
    ."\t{\n"
    ."\t}\n"
    ."\t//-->\n"
    ."\t</script>\n\n";

    //Present the clear all page using clearall.pstpl template
    echo templatereplace(file_get_contents("$thistpl/clearall.pstpl"));

    echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
    doFooter();
    exit;
}

if (isset($_GET['newtest']) && $_GET['newtest'] == "Y")
{
    $savesessionvars=Array();
    if (isset($_SESSION['USER_RIGHT_PREVIEW']))
    {
        $savesessionvars["USER_RIGHT_PREVIEW"]=$surveyid;
        $savesessionvars["loginID"]=$_SESSION['loginID'];
        $savesessionvars["user"]=$_SESSION['user'];
    }
    session_unset();
    $_SESSION['oldsid']=$surveyid;
    foreach ($savesessionvars as $sesskey => $sessval)
    {
        $_SESSION[$sesskey]=$sessval;
    }
    //DELETE COOKIE (allow to use multiple times)
    setcookie($cookiename, "INCOMPLETE", time()-120);
    //echo "Reset Cookie!";
}

//Check to see if a refering URL has been captured.
GetReferringUrl();
// Let's do this only if
//  - a saved answer record hasn't been loaded through the saved feature
//  - the survey is not anonymous
//  - the survey is active
//  - a token information has been provided
//  - the survey is setup to allow token-response-persistence
if ($thissurvey['tokenanswerspersistence'] == 'Y' && !isset($_SESSION['srid']) && $thissurvey['anonymized'] == "N" && $thissurvey['active'] == "Y" && isset($token) && $token !='')
{
    // load previous answers if any (dataentry with nosubmit)
    $srquery="SELECT id FROM {$thissurvey['tablename']}"
    . " WHERE {$thissurvey['tablename']}.token='".db_quote($token)."'\n";

    $result = $connect->GetOne($srquery);
    if ($result !== false && !is_null($result))
    {
        $_SESSION['srid'] = $result;
    }
    buildsurveysession();
    loadanswers();
}

// SAVE POSTED ANSWERS TO DATABASE IF MOVE (NEXT,PREV,LAST, or SUBMIT) or RETURNING FROM SAVE FORM
if (isset($move) || isset($_POST['saveprompt']))
{
    require_once("save.php");

    // RELOAD THE ANSWERS INCASE SOMEONE ELSE CHANGED THEM
    if ($thissurvey['active'] == "Y" &&
            ( $thissurvey['allowsave'] == "Y" || $thissurvey['tokenanswerspersistence'] == "Y") )
    {
        loadanswers();
    }
}

if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'previewgroup')){
        $thissurvey['format'] = 'G';
        buildsurveysession();
}

sendcacheheaders();
//CALL APPROPRIATE SCRIPT
switch ($thissurvey['format'])
{
    case "A": //All in one
        require_once("survey.php");
        break;
    case "S": //One at a time
        require_once("question.php");
        break;
    case "G": //Group at a time
        require_once("group.php");
        break;
    default:
        require_once("question.php");
}

if (isset($_POST['saveall']) || isset($flashmessage))
{
    echo "<script language='JavaScript'> $(document).ready( function() {alert('".$clang->gT("Your responses were successfully saved.","js")."');}) </script>";
}

function loadanswers()
{
    global $dbprefix,$surveyid,$errormsg;
    global $thissurvey, $thisstep, $clang;
    global $databasetype, $clienttoken;
    $scid=returnglobal('scid');
    if (isset($_POST['loadall']) && $_POST['loadall'] == "reload")
    {
        $query = "SELECT * FROM ".db_table_name('saved_control')." INNER JOIN {$thissurvey['tablename']}
			ON ".db_table_name('saved_control').".srid = {$thissurvey['tablename']}.id
			WHERE ".db_table_name('saved_control').".sid=$surveyid\n";
        if (isset($scid)) //Would only come from email

        {
            $query .= "AND ".db_table_name('saved_control').".scid={$scid}\n";
        }
        $query .="AND ".db_table_name('saved_control').".identifier = '".auto_escape($_SESSION['holdname'])."' ";

        if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n' || $databasetype=='mssqlnative')
        {
            $query .="AND CAST(".db_table_name('saved_control').".access_code as varchar(32))= '".md5(auto_unescape($_SESSION['holdpass']))."'\n";
        }
        else
        {
            $query .="AND ".db_table_name('saved_control').".access_code = '".md5(auto_unescape($_SESSION['holdpass']))."'\n";
        }
    }
    elseif (isset($_SESSION['srid']))
    {
        $query = "SELECT * FROM {$thissurvey['tablename']}
			WHERE {$thissurvey['tablename']}.id=".$_SESSION['srid']."\n";
    }
    else
    {
        return;
    }
    $result = db_execute_assoc($query) or safe_die ("Error loading results<br />$query<br />".$connect->ErrorMsg());   //Checked
    if ($result->RecordCount() < 1)
    {
        $errormsg .= $clang->gT("There is no matching saved survey")."<br />\n";
    }
    else
    {
        //A match has been found. Let's load the values!
        //If this is from an email, build surveysession first
        $row=$result->FetchRow();
        foreach ($row as $column => $value)
        {
            if ($column == "token")
            {
                $clienttoken=$value;
                $token=$value;
            }
            elseif ($column == "saved_thisstep" && $thissurvey['alloweditaftercompletion'] != 'Y' )
            {
                $_SESSION['step']=$value;
                $thisstep=$value-1;
            }
            elseif ($column =='lastpage' && isset($_GET['token']) && $thissurvey['alloweditaftercompletion'] != 'Y' )
            {
                if ($value<1) $value=1;
                $_SESSION['step']=$value;
                $thisstep=$value-1;
            }
            /*
               Commented this part out because otherwise startlanguage would overwrite any other language during a running survey.
               We will need a new field named 'endlanguage' to save the current language (for example for returning participants)
               /the language the survey was completed in.
            elseif ($column =='startlanguage')
            {
                $clang = SetSurveyLanguage( $surveyid, $value);
                UpdateSessionGroupList($value);  // to refresh the language strings in the group list session variable
                UpdateFieldArray();        // to refresh question titles and question text
            }*/
            elseif ($column == "scid")
            {
                $_SESSION['scid']=$value;
            }
            elseif ($column == "srid")
            {
                $_SESSION['srid']=$value;
            }
            elseif ($column == "datestamp")
            {
                $_SESSION['datestamp']=$value;
            }
            if ($column == "startdate")
            {
                $_SESSION['startdate']=$value;
            }
            else
            {
                //Only make session variables for those in insertarray[]
                if (in_array($column, $_SESSION['insertarray']))
                {
                    if (($_SESSION['fieldmap'][$column]['type'] == 'N' ||
                            $_SESSION['fieldmap'][$column]['type'] == 'K' ||
                            $_SESSION['fieldmap'][$column]['type'] == 'D') && $value == null)
                    {   // For type N,K,D NULL in DB is to be considered as NoAnswer in any case.
                        // We need to set the _SESSION[field] value to '' in order to evaluate conditions.
                        // This is especially important for the deletenonvalue feature,
                        // otherwise we would erase any answer with condition such as EQUALS-NO-ANSWER on such
                        // question types (NKD)
                        $_SESSION[$column]='';
                    }
                    else
                    {
                    $_SESSION[$column]=$value;
                }
                }  // if (in_array(
            }  // else
        } // foreach
    }
    return true;
}

function makegraph($currentstep, $total)
{
    global $thissurvey;
    global $publicurl, $clang, $js_header_includes, $css_header_includes;

    $js_header_includes[] = '/scripts/jquery/jquery-ui.js';
    $css_header_includes[]= '/scripts/jquery/css/start/jquery-ui.css';
    $css_header_includes[]= '/scripts/jquery/css/start/lime-progress.css';

    $size = intval(($currentstep-1)/$total*100);

    $graph = '<script type="text/javascript">
	$(function() {
		$("#progressbar").progressbar({
			value: '.$size.'
		});
	});';
    if (getLanguageRTL($clang->langcode))
    {
        $graph.='
		$(document).ready(function() {
			$("div.ui-progressbar-value").removeClass("ui-corner-left");
			$("div.ui-progressbar-value").addClass("ui-corner-right");
		});';
    }
    $graph.='
	</script>

	<div id="progress-wrapper">
	<span class="hide">'.sprintf($clang->gT('You have completed %s%% of this survey'),$size).'</span>
		<div id="progress-pre">';
    if (getLanguageRTL($clang->langcode))
    {
        $graph.='100%';
    }
    else
    {
        $graph.='0%';
    }

    $graph.='</div>
		<div id="progressbar"></div>
		<div id="progress-post">';
    if (getLanguageRTL($clang->langcode))
    {
        $graph.='0%';
    }
    else
    {
        $graph.='100%';
    }
    $graph.='</div>
	</div>';

    if ($size == 0) // Progress bar looks dumb if 0

    {
        $graph.='
		<script type="text/javascript">
			$(document).ready(function() {
				$("div.ui-progressbar-value").hide();
			});
		</script>';
    }

    return $graph;
}


function makelanguagechanger()
{
    global $relativeurl;
    if (!isset($surveyid))
    {
        $surveyid=returnglobal('sid');
    }
    if (isset($surveyid))
    {
        $slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    }

    $token = sanitize_token(returnglobal('token'));
    if ($token != '')
    {
        $tokenparam = "&token=$token";
    }
    else
    {
        $tokenparam = "";
    }
    $previewgrp = false;
    if (isset($_REQUEST['action']))
        if ($_REQUEST['action']=='previewgroup')
            $previewgrp = true;

    if (!empty($slangs))
    {
        if (isset($_SESSION['s_lang']) && $_SESSION['s_lang'] != '')
        {
            $lang = sanitize_languagecode($_SESSION['s_lang']);
        }
        else if(isset($_POST['lang']) && $_POST['lang']!='')
        {
            $lang = sanitize_languagecode($_POST['lang']);
        }
        else if (isset($_GET['lang']) && $_GET['lang'] != '')
        {
            $lang = sanitize_languagecode($_GET['lang']);
        }
        else
        $lang = GetBaseLanguageFromSurveyID($surveyid);

        $htmlcode ="<select name=\"select\" class='languagechanger' onchange=\"javascript:window.location=this.value\">\n";
        $htmlcode .= "<option value=\"$relativeurl/index.php?sid=". $surveyid ."&amp;lang=". $lang ."$tokenparam\">".getLanguageNameFromCode($lang,false)."</option>\n";
        $sAddToURL = "";
        $sTargetURL = "$relativeurl/index.php";
        if ($previewgrp){
            $sAddToURL = "&amp;action=previewgroup&amp;gid={$_REQUEST['gid']}";
            $sTargetURL = "";
        }
        foreach ($slangs as $otherlang)
        {
            if($otherlang != $lang)
            $htmlcode .= "\t<option value=\"$sTargetURL?sid=". $surveyid ."&amp;lang=". $otherlang ."$tokenparam$sAddToURL\" >".getLanguageNameFromCode($otherlang,false)."</option>\n";
        }
        if($lang != GetBaseLanguageFromSurveyID($surveyid))
        {
            $htmlcode .= "<option value=\"$sTargetURL?sid=".$surveyid."&amp;lang=".GetBaseLanguageFromSurveyID($surveyid)."$tokenparam$sAddToURL\">".getLanguageNameFromCode(GetBaseLanguageFromSurveyID($surveyid),false)."</option>\n";
        }

        $htmlcode .= "</select>\n";
        //    . "</form>";

        return $htmlcode;
    } elseif (!isset($surveyid))
    {
        global $defaultlang, $baselang;
        $htmlcode = "<select name=\"select\" class='languagechanger' onchange=\"javascript:window.location=this.value\">\n";
        $htmlcode .= "<option value=\"$relativeurl/index.php?lang=". $defaultlang ."$tokenparam\">".getLanguageNameFromCode($defaultlang,false)."</option>\n";
        foreach(getlanguagedata() as $key=>$val)
        {
            $htmlcode .= "\t<option value=\"$relativeurl/index.php?lang=".$key."$tokenparam\" ";
            if($key == $baselang)
            {
                $htmlcode .= " selected=\"selected\" ";
            }
            $htmlcode .= ">".getLanguageNameFromCode($key,false)."</option>\n";
        }
        $htmlcode .= "</select>\n";
        return $htmlcode;
    }
}


function checkgroupfordisplay($gid)
{
    //This function checks all the questions in a group to see if they have
    //conditions, and if the do - to see if the conditions are met.
    //If none of the questions in the group are set to display, then
    //the function will return false, to indicate that the whole group
    //should not display at all.
    global $dbprefix, $connect;
    $countQuestionsInThisGroup=0;
    $countConditionalQuestionsInThisGroup=0;
    $countQuestionsWithRelevanceIntThisGroup=0;

    // Initialize LimeExpressionManager for this group
    LimeExpressionManager::StartProcessingGroup($gid);

    foreach ($_SESSION['fieldarray'] as $ia) //Run through all the questions

    {
        if ($ia[5] == $gid) //If the question is in the group we are checking:

        {
            // Check if this question is hidden
            $qidattributes=getQuestionAttributes($ia[0]);
            if ($qidattributes!==false && ($qidattributes['hidden']==0 || $ia[4]=='*'))
            {
                $countQuestionsInThisGroup++;
                if ($ia[7] == "Y") //This question is conditional

                {
                    $countConditionalQuestionsInThisGroup++;
                    $QuestionsWithConditions[]=$ia; //Create an array containing all the conditional questions
                }
                if (isset($qidattributes['relevance']) && ($qidattributes['relevance'] != 1))
                {
                    $countQuestionsWithRelevanceIntThisGroup++;
                    $QuestionsWithRelevance[]=$qidattributes['relevance'];  // Create an array containing all of the questions whose Relevance Equaation must be processed.
                }
            }
        }
    }
    if ($countQuestionsInThisGroup===0)
    {
        return false;
    }
    elseif (($countQuestionsInThisGroup != $countConditionalQuestionsInThisGroup || !isset($QuestionsWithConditions))
            && ($countQuestionsInThisGroup != $countQuestionsWithRelevanceIntThisGroup || !isset($QuestionsWithRelevance)))
    {
        //One of the questions in this group is NOT conditional, therefore
        //the group MUST be displayed
        return true;
    }
    else
    {
        //All of the questions in this group are conditional. Now we must
        //check every question, to see if the condition for each has been met.
        //If 1 or more have their conditions met, then the group should
        //be displayed.
        if (isset($QuestionsWithConditions)) {
            foreach ($QuestionsWithConditions as $cc)
            {
                if (checkquestionfordisplay($cc[0], $gid) === true)
                {
                    return true;
                }
            }
        }
        if (isset($QuestionsWithRelevance)) {
            foreach ($QuestionsWithRelevance as $relevance)
            {
                if (LimeExpressionManager::ProcessRelevance($relevance))
                {
                    return true;
                }
            }
        }
        //Since we made it this far, there mustn't have been any conditions met.
        //Therefore the group should not be displayed.
        return false;
    }
}

function checkconfield($value)
{
    global $dbprefix, $connect,$surveyid,$thissurvey,$qattributes;
    $fieldisdisplayed=true;
    if (!is_array($thissurvey))
    {
        $local_thissurvey=getSurveyInfo($surveyid);
    }
    else
    {
        $local_thissurvey=$thissurvey;
    }

    // we know the true fieldname $value (for instance SGQA for each checkboxes)
    // and we want to compare it to the values stored in $_SESSION['fieldarray'] which are simple fieldnames
    // ==> We first translate $value to the simple fieldname (let's call it the masterFieldName) from
    //     the $_SESSION['fieldnamesInfo'] translation table
    if (isset($_SESSION['fieldnamesInfo'][$value]))
    {
        $masterFieldName = $_SESSION['fieldnamesInfo'][$value];
    }
    else
    { // for token refurl, ipaddr...
        $masterFieldName = 'token';
    }
    $value_qid=0;
    $value_type='';
    $value_isconditionnal='N';

    //$value is the fieldname for the field we are checking for conditions
    foreach ($_SESSION['fieldarray'] as $sfa) //Go through each field
    {
    // record the qid and question type for future use
        if ($sfa[1]  == $masterFieldName)
        {
            $value_qid=$sfa[0];
            $value_type=$sfa[4];
            $value_isconditionnal=$sfa[7];
            break;
        }
    }

    // check if this question is conditionnal ($sfa[7]): if yes eval conditions
    if ($value_isconditionnal  == "Y" && isset($_SESSION[$value]) ) //Do this if there is a condition based on this answer
    {

        $scenarioquery = "SELECT DISTINCT scenario FROM ".db_table_name("conditions")
        ." WHERE ".db_table_name("conditions").".qid=$sfa[0] ORDER BY scenario";
        $scenarioresult=db_execute_assoc($scenarioquery);
        $matchfound=0;
        //$scenario=1;
        //while ($scenario > 0)
        $evalNextScenario = true;
        while ($evalNextScenario === true && $scenariorow=$scenarioresult->FetchRow())
        {
            $aAllCondrows=Array();
            $cqval=Array();
            $container=Array();

            $scenario = $scenariorow['scenario'];
            $currentcfield="";
            $query = "SELECT ".db_table_name('conditions').".*, ".db_table_name('questions').".type "
            . "FROM ".db_table_name('conditions').", ".db_table_name('questions')." "
            . "WHERE ".db_table_name('conditions').".cqid=".db_table_name('questions').".qid "
            . "AND ".db_table_name('conditions').".qid=$value_qid "
            . "AND ".db_table_name('conditions').".scenario=$scenario "
            . "AND ".db_table_name('conditions').".cfieldname NOT LIKE '{%' "
            . "ORDER BY ".db_table_name('conditions').".qid,".db_table_name('conditions').".cfieldname";
            $result=db_execute_assoc($query) or safe_die($query."<br />".$connect->ErrorMsg());         //Checked
            $conditionsfound = $result->RecordCount();

            $querytoken = "SELECT ".db_table_name('conditions').".*, '' as type "
            . "FROM ".db_table_name('conditions')." "
            . "WHERE "
            . " ".db_table_name('conditions').".qid=$value_qid "
            . "AND ".db_table_name('conditions').".scenario=$scenario "
            . "AND ".db_table_name('conditions').".cfieldname LIKE '{%' "
            . "ORDER BY ".db_table_name('conditions').".qid,".db_table_name('conditions').".cfieldname";
            $resulttoken=db_execute_assoc($querytoken) or safe_die($querytoken."<br />".$connect->ErrorMsg());         //Checked
            $conditionsfoundtoken = $resulttoken->RecordCount();
            $conditionsfound = $conditionsfound + $conditionsfoundtoken;

            while ($Condrow = $resulttoken->FetchRow())
            {
                $aAllCondrows[] = $Condrow;
            }
            while ($Condrow = $result->FetchRow())
            {
                $aAllCondrows[] = $Condrow;
            }


            foreach ($aAllCondrows as $rows)
            {
                if (preg_match("/^\+(.*)$/",$rows['cfieldname'],$cfieldnamematch))
                { // this condition uses a single checkbox as source
                    $rows['type'] = "+".$rows['type'];
                    $rows['cfieldname'] = $cfieldnamematch[1];
                }

                if($rows['type'] == "M" || $rows['type'] == "P")
                {
                    $matchfield=$rows['cfieldname'].$rows['value'];
                    $matchmethod=$rows['method'];
                    $matchvalue="Y";
                }
                else
                {
                    $matchfield=$rows['cfieldname'];
                    $matchmethod=$rows['method'];
                    $matchvalue=$rows['value'];
                }
                $cqval[]=array("cfieldname"=>$rows['cfieldname'],
                        "value"=>$rows['value'],
                        "type"=>$rows['type'],
                        "matchfield"=>$matchfield,
                        "matchvalue"=>$matchvalue,
                        "matchmethod"=>$matchmethod
                );
                if ($rows['cfieldname'] != $currentcfield)
                {
                    $container[]=$rows['cfieldname'];
                }
                $currentcfield=$rows['cfieldname'];
            }
            if ($conditionsfound > 0)
            {
                //At least one match must be found for each "$container"
                $total=0;
                foreach($container as $con)
                {
                    $conditionCanBeEvaluated=true;
                    $addon=0;
                    foreach($cqval as $cqv)
                    {//Go through each condition
                        // Replace @SGQA@ condition values
                        // By corresponding value
                        if (preg_match('/^@([0-9]+X[0-9]+X[^@]+)@/',$cqv["matchvalue"], $targetconditionfieldname))
                        {
                            if (isset($_SESSION[$targetconditionfieldname[1]]))
                            {
                                $cqv["matchvalue"] = $_SESSION[$targetconditionfieldname[1]];
                            }
                            else
                            {
                                $conditionCanBeEvaluated=false;
                            }
                        }
                        // Replace {TOKEN:XXX} condition values
                        // By corresponding value
                        if ($local_thissurvey['anonymized'] == 'N' &&
                        preg_match('/^{TOKEN:([^}]*)}$/',$cqv["matchvalue"], $targetconditiontokenattr))
                        {
                            if (isset($_SESSION['token']) && in_array(strtolower($targetconditiontokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
                            {
                                $cqv["matchvalue"] = GetAttributeValue($surveyid,strtolower($targetconditiontokenattr[1]),$_SESSION['token']);
                            }
                            else
                            {
                                $conditionCanBeEvaluated=false;
                            }
                        }
                        // Use == as default operator
                        if (trim($cqv['matchmethod'])=='')
                        {
                            $cqv['matchmethod']='==';
                        }
                        if($cqv['cfieldname'] == $con && $conditionCanBeEvaluated === true)
                        {
                            if (!preg_match("/^{/",$cqv['cfieldname']))
                            {
                                if (isset($_SESSION[$cqv['matchfield']]))
                                {
                                    $comparisonLeftOperand =  $_SESSION[$cqv['matchfield']];
                                }
                                else
                                {
                                    $comparisonLeftOperand = null;
                                }
                            }
                            elseif ($local_thissurvey['anonymized'] == "N" && preg_match('/^{TOKEN:([^}]*)}$/',$cqv['cfieldname'],$sourceconditiontokenattr))
                            {
                                if ( isset($_SESSION['token']) &&
                                in_array(strtolower($sourceconditiontokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
                                {
                                    $comparisonLeftOperand = GetAttributeValue($surveyid,strtolower($sourceconditiontokenattr[1]),$_SESSION['token']);
                                }
                                else
                                {
                                    $comparisonLeftOperand = null;
                                }

                            }
                            else
                            {
                                $comparisonLeftOperand = null;
                            }

                            if ($cqv['matchmethod'] != "RX")
                            {
                                if (preg_match("/^a(.*)b$/",$cqv['matchmethod'],$matchmethods))
                                {
                                    // strings comparizon operator in PHP are the same as numerical operators
                                    $matchOperator = $matchmethods[1];
                                }
                                else
                                {
                                    $matchOperator = $cqv['matchmethod'];
                                }
                                if (isset($comparisonLeftOperand) && !is_null($comparisonLeftOperand) && eval('if (trim($comparisonLeftOperand) '.$matchOperator.' trim($cqv["matchvalue"]) ) {return true;} else {return false;}'))
                                {//plug successful matches into appropriate container
                                    $addon=1;
                                }
                            }
                            elseif ( isset($comparisonLeftOperand) && !is_null($comparisonLeftOperand) && preg_match('/'.$cqv["matchvalue"].'/',$comparisonLeftOperand))
                            {
                                $addon=1;
                            }
                        }
                    }
                    if($addon==1)
                    {
                        $total++;
                    }
                }
                if($total==count($container))
                {
                    $matchfound=1;
                    $evalNextScenario=false; // Don't look for other scenario's.
                }
                unset($cqval);
                unset($container);
            } else
            {
                //Curious there is no condition for this question in this scenario
                // this is not a normal behaviour, but I propose to defaults to a
                // condition-matched state in this case
                $matchfound=1;
                $evalNextScenario=false;
            }
        } // while ($scenario)
        if($matchfound==0)
        {
            //If this is not a "moveprev" then
            // Reset the value in SESSION
            //if(isset($move) && $move != "moveprev")
            //{
            $_SESSION[$value]="";
            $fieldisdisplayed=false;
            //}
        }
    }

    if ($value_qid != 0)
    { // not token masterFieldname
        $value_qa=getQuestionAttributes($value_qid,$value_type);
    }
    if ($fieldisdisplayed === true && isset($value_qa) && (
    (isset($value_qa['array_filter'])  && trim($value_qa['array_filter']) != '') ||
    (isset($value_qa['array_filter_exclude']) && trim($value_qa['array_filter_exclude']) != '') ))
    { // check if array_filter//array_filter_exclude have hidden the field
        $value_code = preg_replace("/$masterFieldName(.*)/","$1",$value);
        //If this question is a multi-flexible, the value_code will be both the array_filter value
        // (at the beginning) and then a labelset value after an underscore
        // ie: 2_1 for answer code=2 and labelset code=1 then 2_2 for answer_code=2 and
        // labelset code=2. So for these question types we need to split it again at the underscore!
        // 1. Find out if this is question type ":" or ";"
        if($value_type==";" || $value_type==":")
        {
            list($value_code, $value_label)=explode("_", $value_code);
        }
        if (isset($value_qa['array_filter_exclude']))
        {
        $arrayfilterXcludes_selected_codes = getArrayFilterExcludesForQuestion($value_qid);
        if ( $arrayfilterXcludes_selected_codes !== false &&
        in_array($value_code,$arrayfilterXcludes_selected_codes))
        {
            $fieldisdisplayed=false;
        }
        }
        elseif (isset($value_qa['array_filter']))
        {
            $arrayfilter_selected_codes = getArrayFiltersForQuestion($value_qid);
            if ( $arrayfilter_selected_codes !== false &&
            !in_array($value_code,$arrayfilter_selected_codes))
            {
                $fieldisdisplayed=false;
            }
        }
    }
    return $fieldisdisplayed;
}

function checkmandatorys($move, $backok=null)
{
    global $clang, $thisstep;
    if ((isset($_POST['mandatory']) && $_POST['mandatory']) && (!isset($backok) || $backok != "Y"))
    {
        $chkmands=explode("|", $_POST['mandatory']); //These are the mandatory questions to check
        $mfns=explode("|", $_POST['mandatoryfn']); //These are the fieldnames of the mandatory questions
        $mi=0;
        foreach ($chkmands as $cm)
        {
            if (!isset($multiname) || (isset($multiname) && $multiname != "MULTI$mfns[$mi]"))  //no multiple type mandatory set, or does not match this question (set later on for first time)

            {
                if ((isset($multiname) && $multiname) && (isset($_POST[$multiname]) && $_POST[$multiname])) //This isn't the first time (multiname exists, and is a posted variable)

                {
                    if ($$multiname == $$multiname2 && isset($visibleanswers) && $visibleanswers > 0) //The number of questions not answered is equal to the number of questions

                    {
                        //The number of questions not answered is equal to the number of questions
                        //This section gets used if it is a multiple choice type question
                            $_SESSION['step'] = $thisstep;
                        $notanswered[]=substr($multiname, 5, strlen($multiname));
                        $$multiname=0;
                        $$multiname2=0;
                    }
                }
                $multiname="MULTI$mfns[$mi]";
                $multiname2=$multiname."2"; //Make a copy, to store a second version
                $$multiname=0;
                $$multiname2=0;
            }
            else
            {
                $multiname="MULTI$mfns[$mi]";
            }
            $dtcm = "tbdisp$cm";
            if (isset($_SESSION[$cm]) && ($_SESSION[$cm] == "0" || $_SESSION[$cm]))
            {
            }
            elseif ((!isset($_POST[$multiname]) || !$_POST[$multiname]) && (!isset($_POST[$dtcm]) || $_POST[$dtcm] == "on"))
            {
                //One of the mandatory questions hasn't been asnwered
                    $_SESSION['step'] = $thisstep;
                $notanswered[]=$mfns[$mi];
            }
            else
            {
                //One of the mandatory questions hasn't been answered
                $$multiname++;
            }
            /* We need to have some variable to use later that indicates whether any of the
             multiple option answers were actually displayed (since it's impossible to
             answer them if they aren't). The $visibleanswers field is created here to
             record how many of the answers were actually available to be answered */
            if(!isset($visibleanswers) && (isset($_POST[$dtcm]) && $_POST[$dtcm] == "off" || isset($_POST[$dtcm])))
            {
                $visibleanswers=0;
            }
            if(isset($_POST[$dtcm]) && $_POST[$dtcm] == "on")
            {
                $visibleanswers++;
            }

            $$multiname2++;
            $mi++;
        }
        if ($multiname && isset($_POST[$multiname]) && $_POST[$multiname]) // Catch the last Multiple choice question in the lot

        {
            if ($$multiname == $$multiname2 && isset($visibleanswers) && $visibleanswers > 0) //so far all multiple choice options are unanswered

            {
                //The number of questions not answered is equal to the number of questions
                if (isset($move) && $move == "moveprev")
                {
                    $_SESSION['step'] = $thisstep;
                }
                if (isset($move) && $move == "movenext")
                {
                    $_SESSION['step'] = $thisstep;
                }
                $notanswered[]=substr($multiname, 5, strlen($multiname));
                $$multiname="";
                $$multiname2="";
            }
        }
    }
    if (!isset($notanswered))
    {
        return false;
    }//$notanswered=null;}
    return $notanswered;
}

function checkconditionalmandatorys($move, $backok=null)
{
    global $thisstep;
    if ((isset($_POST['conmandatory']) && $_POST['conmandatory']) && (!isset($backok) || $backok != "Y")) //Mandatory conditional questions that should only be checked if the conditions for displaying that question are met

    {
        $chkcmands=explode("|", $_POST['conmandatory']);
        $cmfns=explode("|", $_POST['conmandatoryfn']);
        $mi=0;
        foreach ($chkcmands as $ccm)
        {
            if (!isset($multiname) || $multiname != "MULTI$cmfns[$mi]") //the last multipleanswerchecked is different to this one

            {
                if (isset($multiname) && $multiname && isset($_POST[$multiname]) && $_POST[$multiname])
                {
                    if ($$multiname == $$multiname2) //For this lot all multiple choice options are unanswered

                    {
                        //The number of questions not answered is equal to the number of questions
                            $_SESSION['step'] = $thisstep;
                        $notanswered[]=substr($multiname, 5, strlen($multiname));
                        $$multiname=0;
                        $$multiname2=0;
                    }
                }
                $multiname="MULTI$cmfns[$mi]";
                $multiname2=$multiname."2"; //POSSIBLE CORRUPTION OF PROCESS - CHECK LATER
                $$multiname=0;
                $$multiname2=0;
            }
            else
            {
                $multiname="MULTI$cmfns[$mi]";
            }
            $dccm="display$cmfns[$mi]";
            $dtccm = "tbdisp$ccm";
            if (isset($_SESSION[$ccm]) && ($_SESSION[$ccm] == "0" || $_SESSION[$ccm]) && isset($_POST[$dccm]) && $_POST[$dccm] == "on") //There is an answer

            {
                //The question has an answer, and the answer was displaying
            }
            elseif ((isset($_POST[$dccm]) && $_POST[$dccm] == "on") && (!isset($_POST[$multiname]) || !$_POST[$multiname]) && (!isset($_POST[$dtccm]) || $_POST[$dtccm] == "on")) // Question and Answers is on, there is no answer, but it's a multiple

            {
                if (isset($move) && $move == "moveprev")
                {
                    $_SESSION['step'] = $thisstep;
                }
                if (isset($move) && $move == "movenext")
                {
                    $_SESSION['step'] = $thisstep;
                }
                $notanswered[]=$cmfns[$mi];
            }
            elseif (isset($_POST[$dccm]) && $_POST[$dccm] == "on")
            {
                //One of the conditional mandatory questions was on, but hasn't been answered
                $$multiname++;
            }
            $$multiname2++;
            $mi++;
        }
        if (isset($multiname) && $multiname && isset($_POST[$multiname]) && $_POST[$multiname])
        {
            if ($$multiname == $$multiname2) //so far all multiple choice options are unanswered

            {
                //The number of questions not answered is equal to the number of questions
                if (isset($move) && $move == "moveprev")
                {
                    $_SESSION['step'] = $thisstep;
                }
                if (isset($move) && $move == "movenext")
                {
                    $_SESSION['step'] = $thisstep;
                }
                $notanswered[]=substr($multiname, 5, strlen($multiname));
            }
        }
    }
    if (!isset($notanswered))
    {
        return false;
    }//$notanswered=null;}
    return $notanswered;
}

function checkUploadedFileValidity($move, $backok=null)
{
    global $connect, $thisstep, $clang;
    if (!isset($backok) || $backok != "Y")
    {
        global $dbprefix;
        $fieldmap = createFieldMap(returnglobal('sid'));

        if (isset($_POST['fieldnames']) && $_POST['fieldnames']!="")
        {
            $fields = explode("|", $_POST['fieldnames']);

            foreach ($fields as $field)
            {
                if ($fieldmap[$field]['type'] == "|" && !strrpos($fieldmap[$field]['fieldname'], "_filecount"))
                {
                    $validation = array();

                    $query = "SELECT * FROM ".$dbprefix."question_attributes WHERE qid = ".$fieldmap[$field]['qid'];
                    $result = db_execute_assoc($query);
                    while ($row = $result->FetchRow())
                        $validation[$row['attribute']] = $row['value'];

                    $filecount = 0;

                    $json = $_POST[$field];
                    // if name is blank, its basic, hence check
                    // else, its ajax, don't check, bypass it.

                    if ($json != "" && $json != "[]")
                    {
                        $phparray = json_decode(stripslashes($json));
                        if ($phparray[0]->size != "")
                        { // ajax
                            $filecount = count($phparray);
                        }
                        else
                        { // basic
                            for ($i = 1; $i <= $validation['max_num_of_files']; $i++)
                            {
                                if (!isset($_FILES[$field."_file_".$i]) || $_FILES[$field."_file_".$i]['name'] == '')
                                    continue;

                                $filecount++;

                                $file = $_FILES[$field."_file_".$i];

                                // File size validation
                                if ($file['size'] > $validation['max_filesize'] * 1000)
                                {
                                    $filenotvalidated = array();
                                    $filenotvalidated[$field."_file_".$i] = sprintf($clang->gT("Sorry, the uploaded file (%s) is larger than the allowed filesize of %s KB."), $file['size'], $validation['max_filesize']);
                                    $append = true;
                                }

                                // File extension validation
                                $pathinfo = pathinfo(basename($file['name']));
                                $ext = $pathinfo['extension'];

                                $validExtensions = explode(",", $validation['allowed_filetypes']);
                                if (!(in_array($ext, $validExtensions)))
                                {
                                    if (isset($append) && $append)
                                    {
                                        $filenotvalidated[$field."_file_".$i] .= sprintf($clang->gT("Sorry, only %s extensions are allowed!"),$validation['allowed_filetypes']);
                                        unset($append);
                                    }
                                    else
                                    {
                                        $filenotvalidated = array();
                                        $filenotvalidated[$field."_file_".$i] .= sprintf($clang->gT("Sorry, only %s extensions are allowed!"),$validation['allowed_filetypes']);
                                    }
                                }
                            }
                        }
                    }
                    else
                        $filecount = 0;

                    if ($filecount < $validation['min_num_of_files'])
                    {
                        $filenotvalidated = array();
                        $filenotvalidated[$field] = $clang->gT("The minimum number of files has not been uploaded.");
                    }
                }
            }
        }
        if (isset($filenotvalidated))
        {
            if (isset($move) && $move == "moveprev")
                $_SESSION['step'] = $thisstep;
            if (isset($move) && $move == "movenext")
                $_SESSION['step'] = $thisstep;
            return $filenotvalidated;
        }
    }
    if (!isset($filenotvalidated))
        return false;
    else
        return $filenotvalidated;
}


function checkpregs($move,$backok=null)
{
    global $connect, $thisstep;
    if (!isset($backok) || $backok != "Y")
    {
        global $dbprefix;
        $fieldmap=createFieldMap(returnglobal('sid'));
        if (isset($_POST['fieldnames']))
        {
            $fields=explode("|", $_POST['fieldnames']);
            foreach ($fields as $field)
            {
                //Get question information
                if (isset($_POST[$field]) && isset($_SESSION['s_lang']) && ($_POST[$field] == "0" || $_POST[$field])) //Only do this if there is an answer

                {
                    $fieldinfo=$fieldmap[$field];
                    $pregquery="SELECT preg\n"
                    ."FROM ".db_table_name('questions')."\n"
                    ."WHERE qid=".$fieldinfo['qid']." "
                    . "AND language='".$_SESSION['s_lang']."'";
                    $pregresult=db_execute_assoc($pregquery) or safe_die("ERROR: $pregquery<br />".$connect->ErrorMsg());      //Checked
                    while($pregrow=$pregresult->FetchRow())
                    {
                        $preg=trim($pregrow['preg']);
                    } // while
                    if (isset($preg) && $preg)
                    {
                        if (!@preg_match($preg, $_POST[$field]))
                        {
                            $notvalidated[]=$field;
                            continue;
                        }
                    }

                    // check for other question attributes
                    $qidattributes=getQuestionAttributes($fieldinfo['qid'],$fieldinfo['type']);

                    if ($fieldinfo['type'] == 'N')
                    {
                        $neg = true;
                        if (trim($qidattributes['max_num_value_n'])!='' &&
                            $qidattributes['max_num_value_n'] >= 0)
                        {
                            $neg = false;
                        }

                        if (trim($qidattributes['num_value_int_only'])==1 &&
                        !preg_match("/^" . ($neg? "-?": "") . "[0-9]+$/", $_POST[$field]))
                        {
                            $notvalidated[]=$field;
                            continue;
                        }

                        if (trim($qidattributes['max_num_value_n'])!='' &&
                            $_POST[$field] > $qidattributes['max_num_value_n'])
                        {
                            $notvalidated[]=$field;
                            continue;
                        }
                        if (trim($qidattributes['min_num_value_n'])!='' &&
                            $_POST[$field] < $qidattributes['min_num_value_n'])
                        {
                            $notvalidated[]=$field;
                            continue;
                        }
                    }
                }
            }
        }
        //The following section checks for question attribute validation, looking for values in a particular field
        if (isset($_POST['qattribute_answer']))
        {
            foreach ($_POST['qattribute_answer'] as $maxvalueanswer)
            {
                //$maxvalue_answername="maxvalue_answer".$maxvalueanswer;
                if (!empty($_POST['qattribute_answer'.$maxvalueanswer]) && $_POST['display'.$maxvalueanswer] == "on")
                {
                        $_SESSION['step'] = $thisstep;
                    $notvalidated[]=$maxvalueanswer;
                    return $notvalidated;
                }
            }
        }

        if (isset($notvalidated) && is_array($notvalidated))
        {
            if (isset($move) && $move == "moveprev")
            {
                $_SESSION['step'] = $thisstep;
            }
            if (isset($move) && $move == "movenext")
            {
                $_SESSION['step'] = $thisstep;
            }
            return $notvalidated;
        }
    }
}

function addtoarray_single($array1, $array2)
{
    //Takes two single element arrays and adds second to end of first if value exists
    if (is_array($array2))
    {
        foreach ($array2 as $ar)
        {
            if ($ar && $ar !== null)
            {
                $array1[]=$ar;
            }
        }
    }
    return $array1;
}

function remove_nulls_from_array($array)
{
    foreach ($array as $ar)
    {
        if ($ar !== null)
        {
            $return[]=$ar;
        }
    }
    if (isset($return))
    {
        return $return;
    }
    else
    {
        return false;
    }
}


/**
 * Marks a tokens as completed and sends a confirmation email to the participiant.
 * If $quotaexit is set to true then the user exited the survey due to a quota
 * restriction and the according token is only marked as 'Q'
 *
 * @param mixed $quotaexit
 */
function submittokens($quotaexit=false)
{
    global $thissurvey, $timeadjust, $emailcharset ;
    global $dbprefix, $surveyid, $connect;
    global $sitename, $thistpl, $clang, $clienttoken;

    // Shift the date due to global timeadjust setting
    $today = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust);

    // check how many uses the token has left
    $usesquery = "SELECT usesleft FROM {$dbprefix}tokens_$surveyid WHERE token='".db_quote($clienttoken)."'";
    $usesresult = db_execute_assoc($usesquery);
    $usesrow = $usesresult->FetchRow();
    if (isset($usesrow)) { $usesleft = $usesrow['usesleft']; }

    $utquery = "UPDATE {$dbprefix}tokens_$surveyid\n";
    if ($quotaexit==true)
    {
        $utquery .= "SET completed='Q', usesleft=usesleft-1\n";
    }
    elseif (bIsTokenCompletedDatestamped($thissurvey))
    {
        if (isset($usesleft) && $usesleft<=1)
        {
			$utquery .= "SET usesleft=usesleft-1, completed='$today'\n";
    }
    else
    {
			$utquery .= "SET usesleft=usesleft-1\n";
    }
    }
    else
    {
        if (isset($usesleft) && $usesleft<=1)
        {
			$utquery .= "SET usesleft=usesleft-1, completed='Y'\n";
		}
		else
		{
			$utquery .= "SET usesleft=usesleft-1\n";
		}
    }
    $utquery .= "WHERE token='".db_quote($clienttoken)."'";

    $utresult = $connect->Execute($utquery) or safe_die ("Couldn't update tokens table!<br />\n$utquery<br />\n".$connect->ErrorMsg());     //Checked

    if ($quotaexit==false)
    {
        // TLR change to put date into sent and completed
        $cnfquery = "SELECT * FROM ".db_table_name("tokens_$surveyid")." WHERE token='".db_quote($clienttoken)."' AND completed!='N' AND completed!=''";

        $cnfresult = db_execute_assoc($cnfquery);       //Checked
        $cnfrow = $cnfresult->FetchRow();
        if (isset($cnfrow))
        {
            $from = "{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";
            $to = $cnfrow['email'];
            $subject=$thissurvey['email_confirm_subj'];

            $fieldsarray["{ADMINNAME}"]=$thissurvey['adminname'];
            $fieldsarray["{ADMINEMAIL}"]=$thissurvey['adminemail'];
            $fieldsarray["{SURVEYNAME}"]=$thissurvey['name'];
            $fieldsarray["{SURVEYDESCRIPTION}"]=$thissurvey['description'];
            $fieldsarray["{FIRSTNAME}"]=$cnfrow['firstname'];
            $fieldsarray["{LASTNAME}"]=$cnfrow['lastname'];
            $fieldsarray["{TOKEN}"]=$clienttoken;
            $attrfieldnames=GetAttributeFieldnames($surveyid);
            foreach ($attrfieldnames as $attr_name)
            {
                $fieldsarray["{".strtoupper($attr_name)."}"]=$cnfrow[$attr_name];
            }

            $dateformatdatat=getDateFormatData($thissurvey['surveyls_dateformat']);
            $numberformatdatat = getRadixPointData($thissurvey['surveyls_numberformat']);
            $fieldsarray["{EXPIRY}"]=convertDateTimeFormat($thissurvey["expiry"],'Y-m-d H:i:s',$dateformatdatat['phpdate']);

            $subject=ReplaceFields($subject, $fieldsarray, true);

            if ($thissurvey['anonymized'] == "N")
            {
                // Survey is not anonymous, we can translate insertAns placeholder
                $subject=dTexts::run($subject);
            }

            $subject=html_entity_decode($subject,ENT_QUOTES,$emailcharset);

            if (getEmailFormat($surveyid) == 'html')
            {
                $ishtml=true;
            }
            else
            {
                $ishtml=false;
            }

            if (trim(strip_tags($thissurvey['email_confirm'])) != "")
            {
                $message=$thissurvey['email_confirm'];
                $message=ReplaceFields($message, $fieldsarray, true);

                if ($thissurvey['anonymized'] == "N")
                {
                    // Survey is not anonymous, we can translate insertAns placeholder
                    $message=dTexts::run($message);
                }

                if (!$ishtml)
                {
                    $message=strip_tags(br2nl(html_entity_decode($message,ENT_QUOTES,$emailcharset)));
                }
                else
                {
                    $message=html_entity_decode($message,ENT_QUOTES, $emailcharset );
                }

                //Only send confirmation email if there is a valid email address
                if (validate_email($cnfrow['email']))
                {
                    SendEmailMessage($message, $subject, $to, $from, $sitename,$ishtml);
                }
            }
            else
            {
                //There is nothing in the message, so don't send a confirmation email
                //This section only here as placeholder to indicate new feature :-)
            }
        }
    }
}

/**
* Send a submit notification to the email address specified in the notifications tab in the survey settings
*/
function SendSubmitNotifications()
{
    global $thissurvey, $debug;
    global $dbprefix, $clang, $emailcharset;
    global $sitename, $homeurl, $surveyid, $publicurl, $maildebug, $tokensexist;

    $bIsHTML = ($thissurvey['htmlemail'] == 'Y');

    $aReplacementVars=array();


    if ($thissurvey['allowsave'] == "Y" && isset($_SESSION['scid']))
    {
        $aReplacementVars['RELOADURL']="{$publicurl}/index.php?sid={$surveyid}&loadall=reload&scid=".$_SESSION['scid']."&loadname=".urlencode($_SESSION['holdname'])."&loadpass=".urlencode($_SESSION['holdpass']);
        if ($bIsHTML)
        {
            $aReplacementVars['RELOADURL']="<a href='{$aReplacementVars['RELOADURL']}'>{$aReplacementVars['RELOADURL']}</a>";
        }
    }
    else
    {
        $aReplacementVars['RELOADURL']='';
    }

    $aReplacementVars['ADMINNAME'] = $thissurvey['adminname'];
    $aReplacementVars['ADMINEMAIL'] = $thissurvey['adminemail'];
    $aReplacementVars['VIEWRESPONSEURL']="{$homeurl}/admin.php?action=browse&sid={$surveyid}&subaction=id&id={$_SESSION['srid']}";
    $aReplacementVars['EDITRESPONSEURL']="{$homeurl}/admin.php?action=dataentry&sid={$surveyid}&subaction=edit&surveytable=survey_{$surveyid}&id=".$_SESSION['srid'];
    $aReplacementVars['STATISTICSURL']="{$homeurl}/admin.php?action=statistics&sid={$surveyid}";
    if ($bIsHTML)
    {
        $aReplacementVars['VIEWRESPONSEURL']="<a href='{$aReplacementVars['VIEWRESPONSEURL']}'>{$aReplacementVars['VIEWRESPONSEURL']}</a>";
        $aReplacementVars['EDITRESPONSEURL']="<a href='{$aReplacementVars['EDITRESPONSEURL']}'>{$aReplacementVars['EDITRESPONSEURL']}</a>";
        $aReplacementVars['STATISTICSURL']="<a href='{$aReplacementVars['STATISTICSURL']}'>{$aReplacementVars['STATISTICSURL']}</a>";
    }
    $aReplacementVars['ANSWERTABLE']='';
    $aEmailResponseTo=array();
    $aEmailNotificationTo=array();
    $sResponseData="";

    if (!empty($thissurvey['emailnotificationto']))
    {
        $aRecipient=explode(";", $thissurvey['emailnotificationto']);
        {
            foreach($aRecipient as $sRecipient)
            {
                $sRecipient=dTexts::run($sRecipient);
                if(validate_email($sRecipient))
                {
                    $aEmailNotificationTo[]=$sRecipient;
                }
            }
        }
    }

    if (!empty($thissurvey['emailresponseto']))
    {
		if (isset($_SESSION['token']) && $_SESSION['token'] != '' && db_tables_exist($dbprefix.'tokens_'.$surveyid))
        {
            //Gather token data for tokenised surveys
            $_SESSION['thistoken']=getTokenData($surveyid, $_SESSION['token']);
        }
        // there was no token used so lets remove the token field from insertarray
        elseif ($_SESSION['insertarray'][0]=='token')
        {
            unset($_SESSION['insertarray'][0]);
        }
        //Make an array of email addresses to send to
        $aRecipient=explode(";", $thissurvey['emailresponseto']);
        {
            foreach($aRecipient as $sRecipient)
            {
                $sRecipient=dTexts::run($sRecipient);
                if(validate_email($sRecipient))
                {
                    $aEmailResponseTo[]=$sRecipient;
                }
            }
        }

        $aFullResponseTable=aGetFullResponseTable($surveyid,$_SESSION['srid'],$_SESSION['s_lang']);
        $ResultTableHTML = "<table class='printouttable' >\n";
        $ResultTableText ="\n\n";
        $oldgid = 0;
        $oldqid = 0;
        foreach ($aFullResponseTable as $sFieldname=>$fname)
        {
            if (substr($sFieldname,0,4)=='gid_')
            {

               $ResultTableHTML .= "\t<tr class='printanswersgroup'><td colspan='2'>{$fname[0]}</td></tr>\n";
               $ResultTableText .="\n{$fname[0]}\n\n";
            }
            elseif (substr($sFieldname,0,4)=='qid_')
            {
                $ResultTableHTML .= "\t<tr class='printanswersquestionhead'><td  colspan='2'>{$fname[0]}</td></tr>\n";
                $ResultTableText .="\n{$fname[0]}\n";
            }
            else
            {
                $ResultTableHTML .= "\t<tr class='printanswersquestion'><td>{$fname[0]} {$fname[1]}</td><td class='printanswersanswertext'>{$fname[2]}</td></tr>";
                $ResultTableText .="     {$fname[0]} {$fname[1]}: {$fname[2]}\n";
            }
        }

        $ResultTableHTML .= "</table>\n";
        $ResultTableText .= "\n\n";
        if ($bIsHTML)
        {
            $aReplacementVars['ANSWERTABLE']=$ResultTableHTML;
        }
        else
        {
            $aReplacementVars['ANSWERTABLE']=$ResultTableText;
        }
    }

    $sFrom = $thissurvey['adminname'].' <'.$thissurvey['adminemail'].'>';
    if (count($aEmailNotificationTo)>0)
    {
        $sMessage=templatereplace($thissurvey['email_admin_notification'],$aReplacementVars,($thissurvey['anonymized'] == "Y"));
        $sSubject=templatereplace($thissurvey['email_admin_notification_subj'],$aReplacementVars,($thissurvey['anonymized'] == "Y"));
        foreach ($aEmailNotificationTo as $sRecipient)
        {
            if (!SendEmailMessage($sMessage, $sSubject, $sRecipient, $sFrom, $sitename, $bIsHTML, getBounceEmail($surveyid)))
            {
                if ($debug>0)
                {
                    echo '<br />Email could not be sent. Reason: '.$maildebug.'<br/>';
                }
            }
        }
    }

    if (count($aEmailResponseTo)>0)
    {
        $sMessage=templatereplace($thissurvey['email_admin_responses'],$aReplacementVars);
        $sSubject=templatereplace($thissurvey['email_admin_responses_subj'],$aReplacementVars);
        foreach ($aEmailResponseTo as $sRecipient)
        {
            if (!SendEmailMessage($sMessage, $sSubject, $sRecipient, $sFrom, $sitename, $bIsHTML, getBounceEmail($surveyid)))
            {
                if ($debug>0)
                {
                    echo '<br />Email could not be sent. Reason: '.$maildebug.'<br/>';
                }
            }
        }
    }


}

function submitfailed($errormsg='')
{
    global $debug;
    global $thissurvey, $clang;
    global $thistpl, $subquery, $surveyid, $connect;

    $completed = "<br /><strong><font size='2' color='red'>"
    . $clang->gT("Did Not Save")."</strong></font><br /><br />\n\n"
    . $clang->gT("An unexpected error has occurred and your responses cannot be saved.")."<br /><br />\n";
    if ($thissurvey['adminemail'])
    {
        $completed .= $clang->gT("Your responses have not been lost and have been emailed to the survey administrator and will be entered into our database at a later point.")."<br /><br />\n";
        if ($debug>0)
        {
            $completed.='Error message: '.htmlspecialchars($errormsg).'<br />';
        }
        $email=$clang->gT("An error occurred saving a response to survey id","unescaped")." ".$thissurvey['name']." - $surveyid\n\n";
        $email .= $clang->gT("DATA TO BE ENTERED","unescaped").":\n";
        foreach ($_SESSION['insertarray'] as $value)
        {
            $email .= "$value: {$_SESSION[$value]}\n";
        }
        $email .= "\n".$clang->gT("SQL CODE THAT FAILED","unescaped").":\n"
        . "$subquery\n\n"
        . $clang->gT("ERROR MESSAGE","unescaped").":\n"
        . $errormsg."\n\n";
        SendEmailMessage($email, $clang->gT("Error saving results","unescaped"), $thissurvey['adminemail'], $thissurvey['adminemail'], "LimeSurvey", false, getBounceEmail($surveyid));
        //echo "<!-- EMAIL CONTENTS:\n$email -->\n";
        //An email has been sent, so we can kill off this session.
        session_unset();
        session_destroy();
    }
    else
    {
        $completed .= "<a href='javascript:location.reload()'>".$clang->gT("Try to submit again")."</a><br /><br />\n";
        $completed .= $subquery;
    }
    return $completed;
}

/**
* This function builds all the required session variables when a survey is first started and
* it loads any answer defaults from command line or from the table defaultvalues
* It is called from the related format script (group.php, question.php, survey.php)
* if the survey has just started.
*
* @returns  $totalquestions Total number of questions in the survey
*
*/
function buildsurveysession()
{
    global $thissurvey, $secerror, $clienttoken, $databasetype;
    global $tokensexist, $thistpl;
    global $surveyid, $dbprefix, $connect;
    global $register_errormsg, $clang;
    global $totalBoilerplatequestions;
    global $templang, $move, $rooturl, $publicurl;

    if (!isset($templang) || $templang=='')
    {
        $templang=$thissurvey['language'];
    }

    $totalBoilerplatequestions = 0;
    $loadsecurity = returnglobal('loadsecurity');
    // NO TOKEN REQUIRED BUT CAPTCHA ENABLED FOR SURVEY ACCESS
    if ($tokensexist == 0 &&
    captcha_enabled('surveyaccessscreen',$thissurvey['usecaptcha']))
    {

        // IF CAPTCHA ANSWER IS NOT CORRECT OR NOT SET
        if (!isset($loadsecurity) ||
        !isset($_SESSION['secanswer']) ||
        $loadsecurity != $_SESSION['secanswer'])
        {
            sendcacheheaders();
            doHeader();
            // No or bad answer to required security question

            echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
            //echo makedropdownlist();
            echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));

            if (isset($loadsecurity))
            { // was a bad answer
                echo "<font color='#FF0000'>".$clang->gT("The answer to the security question is incorrect.")."</font><br />";
            }

            echo "<p class='captcha'>".$clang->gT("Please confirm access to survey by answering the security question below and click continue.")."</p>
			        <form class='captcha' method='get' action='{$publicurl}/index.php'>
			        <table align='center'>
				        <tr>
					        <td align='right' valign='middle'>
					        <input type='hidden' name='sid' value='".$surveyid."' id='sid' />
					        <input type='hidden' name='lang' value='".$templang."' id='lang' />";
            // In case we this is a direct Reload previous answers URL, then add hidden fields
            if (isset($_GET['loadall']) && isset($_GET['scid'])
            && isset($_GET['loadname']) && isset($_GET['loadpass']))
            {
                echo "
						<input type='hidden' name='loadall' value='".htmlspecialchars($_GET['loadall'])."' id='loadall' />
						<input type='hidden' name='scid' value='".returnglobal('scid')."' id='scid' />
						<input type='hidden' name='loadname' value='".htmlspecialchars($_GET['loadname'])."' id='loadname' />
						<input type='hidden' name='loadpass' value='".htmlspecialchars($_GET['loadpass'])."' id='loadpass' />";
            }

            echo "
				        </td>
			        </tr>";
            if (function_exists("ImageCreate") && captcha_enabled('surveyaccessscreen', $thissurvey['usecaptcha']))
            {
                echo "<tr>
				                <td align='center' valign='middle'><label for='captcha'>".$clang->gT("Security question:")."</label></td><td align='left' valign='middle'><table><tr><td valign='middle'><img src='$rooturl/verification.php?sid=$surveyid' alt='captcha' /></td>
                                <td valign='middle'><input id='captcha' type='text' size='5' maxlength='3' name='loadsecurity' value='' /></td></tr></table>
				                </td>
			                </tr>";
            }
            echo "<tr><td colspan='2' align='center'><input class='submit' type='submit' value='".$clang->gT("Continue")."' /></td></tr>
		        </table>
		        </form>";

            echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
            doFooter();
            exit;
        }
    }

    //BEFORE BUILDING A NEW SESSION FOR THIS SURVEY, LET'S CHECK TO MAKE SURE THE SURVEY SHOULD PROCEED!

    // TOKEN REQUIRED BUT NO TOKEN PROVIDED
    if ($tokensexist == 1 && !returnglobal('token'))
    {
        if ($thissurvey['nokeyboard']=='Y')
        {
            vIncludeKeypad();
            $kpclass = "text-keypad";
        }
        else
        {
            $kpclass = "";
        }

        // DISPLAY REGISTER-PAGE if needed
        // DISPLAY CAPTCHA if needed
        sendcacheheaders();
        doHeader();

        echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
        //echo makedropdownlist();
        echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));
        if (isset($thissurvey) && $thissurvey['allowregister'] == "Y")
        {
            echo templatereplace(file_get_contents("$thistpl/register.pstpl"));
        }
        else
        {
            if (isset($secerror)) echo "<span class='error'>".$secerror."</span><br />";
            echo '<div id="wrapper"><p id="tokenmessage">'.$clang->gT("This is a controlled survey. You need a valid token to participate.")."<br />";
            echo $clang->gT("If you have been issued a token, please enter it in the box below and click continue.")."</p>
            <script type='text/javascript'>var focus_element='#token';</script>
	        <form id='tokenform' method='get' action='{$publicurl}/index.php'>
                <ul>
                <li>
            <label for='token'>".$clang->gT("Token")."</label><input class='text $kpclass' id='token' type='text' name='token' />";

            echo "<input type='hidden' name='sid' value='".$surveyid."' id='sid' />
				<input type='hidden' name='lang' value='".$templang."' id='lang' />";
            if (isset($_GET['newtest']) && $_GET['newtest'] == "Y")
            {
                  echo "  <input type='hidden' name='newtest' value='Y' id='newtest' />";

            }

            // If this is a direct Reload previous answers URL, then add hidden fields
            if (isset($_GET['loadall']) && isset($_GET['scid'])
            && isset($_GET['loadname']) && isset($_GET['loadpass']))
            {
                echo "
					<input type='hidden' name='loadall' value='".htmlspecialchars($_GET['loadall'])."' id='loadall' />
					<input type='hidden' name='scid' value='".returnglobal('scid')."' id='scid' />
					<input type='hidden' name='loadname' value='".htmlspecialchars($_GET['loadname'])."' id='loadname' />
					<input type='hidden' name='loadpass' value='".htmlspecialchars($_GET['loadpass'])."' id='loadpass' />";
            }
            echo "</li>";

            if (function_exists("ImageCreate") && captcha_enabled('surveyaccessscreen', $thissurvey['usecaptcha']))
            {
                echo "<li>
			                <label for='captchaimage'>".$clang->gT("Security Question")."</label><img id='captchaimage' src='$rooturl/verification.php?sid=$surveyid' alt='captcha' /><input type='text' size='5' maxlength='3' name='loadsecurity' value='' />
		                  </li>";
            }
            echo "<li>
                        <input class='submit' type='submit' value='".$clang->gT("Continue")."' />
                      </li>
            </ul>
	        </form></div>";
        }

        echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
        doFooter();
        exit;
    }
    // TOKENS REQUIRED, A TOKEN PROVIDED
    // SURVEY WITH NO NEED TO USE CAPTCHA
    elseif ($tokensexist == 1 && returnglobal('token') &&
    !captcha_enabled('surveyaccessscreen',$thissurvey['usecaptcha']))
    {
        //check if tokens actually haven't been already used
		$areTokensUsed = usedTokens(db_quote(trim(strip_tags(returnglobal('token')))));
        //check if token actually does exist
	    // check also if it is allowed to change survey after completion
		if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
          $tkquery = "SELECT COUNT(*) FROM ".db_table_name('tokens_'.$surveyid)." WHERE token='".db_quote(trim(strip_tags(returnglobal('token'))))."' ";
		} else {
        	$tkquery = "SELECT COUNT(*) FROM ".db_table_name('tokens_'.$surveyid)." WHERE token='".db_quote(trim(strip_tags(returnglobal('token'))))."' AND (completed = 'N' or completed='')";
		}

        $tkresult = db_execute_num($tkquery);    //Checked
        list($tkexist) = $tkresult->FetchRow();
        if (!$tkexist || ($areTokensUsed && $thissurvey['alloweditaftercompletion'] != 'Y'))
        {
            //TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

            killSession();
            sendcacheheaders();
            doHeader();

            echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
            echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));
            echo '<div id="wrapper"><p id="tokenmessage">'.$clang->gT("This is a controlled survey. You need a valid token to participate.")."<br /><br />\n"
            ."\t".$clang->gT("The token you have provided is either not valid, or has already been used.")."<br />\n"
            ."\t".sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname'])
            ." (<a href='mailto:{$thissurvey['adminemail']}'>"
            ."{$thissurvey['adminemail']}</a>)</p></div>\n";

            echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
            doFooter();
            exit;
        }
    }
    // TOKENS REQUIRED, A TOKEN PROVIDED
    // SURVEY CAPTCHA REQUIRED
    elseif ($tokensexist == 1 && returnglobal('token') && captcha_enabled('surveyaccessscreen',$thissurvey['usecaptcha']))
    {

        // IF CAPTCHA ANSWER IS CORRECT
        if (isset($loadsecurity) &&
        isset($_SESSION['secanswer']) &&
        $loadsecurity == $_SESSION['secanswer'])
        {
            //check if tokens actually haven't been already used
            $areTokensUsed = usedTokens(db_quote(trim(strip_tags(returnglobal('token')))));
            //check if token actually does exist
            if ($thissurvey['alloweditaftercompletion'] == 'Y' )
            {
                $tkquery = "SELECT COUNT(*) FROM ".db_table_name('tokens_'.$surveyid)." WHERE token='".db_quote(trim(sanitize_xss_string(strip_tags(returnglobal('token')))))."'";
            }
            else
            {
                $tkquery = "SELECT COUNT(*) FROM ".db_table_name('tokens_'.$surveyid)." WHERE token='".db_quote(trim(sanitize_xss_string(strip_tags(returnglobal('token')))))."' AND (completed = 'N' or completed='')";
            }
            $tkresult = db_execute_num($tkquery);     //Checked
            list($tkexist) = $tkresult->FetchRow();
            if (!$tkexist || ($areTokensUsed && $thissurvey['alloweditaftercompletion'] != 'Y') )
            {
                sendcacheheaders();
                doHeader();
                //TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

                echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
                echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));
                echo "\t<div id='wrapper'>\n"
                ."\t<p id='tokenmessage'>\n"
                ."\t".$clang->gT("This is a controlled survey. You need a valid token to participate.")."<br /><br />\n"
                ."\t".$clang->gT("The token you have provided is either not valid, or has already been used.")."<br/>\n"
                ."\t".sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname'])
                ." (<a href='mailto:{$thissurvey['adminemail']}'>"
                ."{$thissurvey['adminemail']}</a>)\n"
                ."\t</p>\n"
                ."\t</div>\n";

                echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
                doFooter();
                exit;
            }
        }
        // IF CAPTCHA ANSWER IS NOT CORRECT
        else if (!isset($move) || is_null($move))
        {
            $gettoken = $clienttoken;
            sendcacheheaders();
            doHeader();
            // No or bad answer to required security question
            echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
            echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));
            // If token wasn't provided and public registration
            // is enabled then show registration form
            if ( !isset($gettoken) && isset($thissurvey) && $thissurvey['allowregister'] == "Y")
            {
                echo templatereplace(file_get_contents("$thistpl/register.pstpl"));
            }
            else
            { // only show CAPTCHA

                echo '<div id="wrapper"><p id="tokenmessage">';
                if (isset($loadsecurity))
                { // was a bad answer
                    echo "<span class='error'>".$clang->gT("The answer to the security question is incorrect.")."</span><br />";
                }

                echo $clang->gT("This is a controlled survey. You need a valid token to participate.")."<br /><br />";
                // IF TOKEN HAS BEEN GIVEN THEN AUTOFILL IT
                // AND HIDE ENTRY FIELD
                if (!isset($gettoken))
                {
                    echo $clang->gT("If you have been issued a token, please enter it in the box below and click continue.")."</p>
			            <form id='tokenform' method='get' action='{$publicurl}/index.php'>
                        <ul>
                        <li>
					        <input type='hidden' name='sid' value='".$surveyid."' id='sid' />
						    <input type='hidden' name='lang' value='".$templang."' id='lang' />";
                    if (isset($_GET['loadall']) && isset($_GET['scid'])
                    && isset($_GET['loadname']) && isset($_GET['loadpass']))
                    {
                        echo "<input type='hidden' name='loadall' value='".htmlspecialchars($_GET['loadall'])."' id='loadall' />
						        <input type='hidden' name='scid' value='".returnglobal('scid')."' id='scid' />
						        <input type='hidden' name='loadname' value='".htmlspecialchars($_GET['loadname'])."' id='loadname' />
						        <input type='hidden' name='loadpass' value='".htmlspecialchars($_GET['loadpass'])."' id='loadpass' />";
                    }

                    echo '<label for="token">'.$clang->gT("Token")."</label><input class='text' type='text' id='token' name='token'></li>";
                }
                else
                {
                    echo $clang->gT("Please confirm the token by answering the security question below and click continue.")."</p>
			            <form id='tokenform' method='get' action='{$publicurl}/index.php'>
                        <ul>
			            <li>
					            <input type='hidden' name='sid' value='".$surveyid."' id='sid' />
						        <input type='hidden' name='lang' value='".$templang."' id='lang' />";
                    if (isset($_GET['loadall']) && isset($_GET['scid'])
                    && isset($_GET['loadname']) && isset($_GET['loadpass']))
                    {
                        echo "<input type='hidden' name='loadall' value='".htmlspecialchars($_GET['loadall'])."' id='loadall' />
                              <input type='hidden' name='scid' value='".returnglobal('scid')."' id='scid' />
                              <input type='hidden' name='loadname' value='".htmlspecialchars($_GET['loadname'])."' id='loadname' />
                              <input type='hidden' name='loadpass' value='".htmlspecialchars($_GET['loadpass'])."' id='loadpass' />";
                    }
                    echo '<label for="token">'.$clang->gT("Token:")."</label><span id='token'>$gettoken</span>"
                    ."<input type='hidden' name='token' value='$gettoken'></li>";
                }


                if (function_exists("ImageCreate") && captcha_enabled('surveyaccessscreen', $thissurvey['usecaptcha']))
                {
                    echo "<li>
                            <label for='captchaimage'>".$clang->gT("Security Question")."</label><img id='captchaimage' src='$rooturl/verification.php?sid=$surveyid' alt='captcha' /><input type='text' size='5' maxlength='3' name='loadsecurity' value='' />
                          </li>";
                }
                echo "<li><input class='submit' type='submit' value='".$clang->gT("Continue")."' /></li>
		                </ul>
		                </form>
		                </id>";
            }

            echo '</div>'.templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
            doFooter();
            exit;
        }
    }

    //RESET ALL THE SESSION VARIABLES AND START AGAIN
    unset($_SESSION['grouplist']);
    unset($_SESSION['fieldarray']);
    unset($_SESSION['insertarray']);
    unset($_SESSION['thistoken']);
    unset($_SESSION['fieldnamesInfo']);
    $_SESSION['fieldnamesInfo'] = Array();


    //RL: multilingual support

	if (isset($_GET['token']) && db_tables_exist($dbprefix.'tokens_'.$surveyid))
    {
        //get language from token (if one exists)
        $tkquery2 = "SELECT * FROM ".db_table_name('tokens_'.$surveyid)." WHERE token='".db_quote($clienttoken)."' AND (completed = 'N' or completed='')";
        //echo $tkquery2;
        $result = db_execute_assoc($tkquery2) or safe_die ("Couldn't get tokens<br />$tkquery<br />".$connect->ErrorMsg());    //Checked
        while ($rw = $result->FetchRow())
        {
            $tklanguage=$rw['language'];
        }
    }
    if (returnglobal('lang'))
    {
        $language_to_set=returnglobal('lang');
    } elseif (isset($tklanguage))
    {
        $language_to_set=$tklanguage;
    }
    else
    {
        $language_to_set = $thissurvey['language'];
    }

    if (!isset($_SESSION['s_lang']))
    {
        SetSurveyLanguage($surveyid, $language_to_set);
    }


    UpdateSessionGroupList($_SESSION['s_lang']);



    // Optimized Query
    // Change query to use sub-select to see if conditions exist.
    $query = "SELECT ".db_table_name('questions').".*, ".db_table_name('groups').".*,\n"
    ." (SELECT count(1) FROM ".db_table_name('conditions')."\n"
    ." WHERE ".db_table_name('questions').".qid = ".db_table_name('conditions').".qid) AS hasconditions,\n"
    ." (SELECT count(1) FROM ".db_table_name('conditions')."\n"
    ." WHERE ".db_table_name('questions').".qid = ".db_table_name('conditions').".cqid) AS usedinconditions\n"
    ." FROM ".db_table_name('groups')." INNER JOIN ".db_table_name('questions')." ON ".db_table_name('groups').".gid = ".db_table_name('questions').".gid\n"
    ." WHERE ".db_table_name('questions').".sid=".$surveyid."\n"
    ." AND ".db_table_name('groups').".language='".$_SESSION['s_lang']."'\n"
    ." AND ".db_table_name('questions').".language='".$_SESSION['s_lang']."'\n"
    ." AND ".db_table_name('questions').".parent_qid=0\n"
    ." ORDER BY ".db_table_name('groups').".group_order,".db_table_name('questions').".question_order";

    //var_dump($_SESSION);
    $result = db_execute_assoc($query);    //Checked

    $arows = $result->GetRows();

    $totalquestions = $result->RecordCount();

    //2. SESSION VARIABLE: totalsteps
    //The number of "pages" that will be presented in this survey
    //The number of pages to be presented will differ depending on the survey format
    switch($thissurvey['format'])
    {
        case "A":
            $_SESSION['totalsteps']=1;
            break;
        case "G":
            if (isset($_SESSION['grouplist']))
            {
                $_SESSION['totalsteps']=count($_SESSION['grouplist']);
            }
            break;
        case "S":
            $_SESSION['totalsteps']=$totalquestions;
    }

    if ($totalquestions == "0")	//break out and crash if there are no questions!
    {
        sendcacheheaders();
        doHeader();

        echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
        echo templatereplace(file_get_contents("$thistpl/survey.pstpl"));
        echo "\t<div id='wrapper'>\n"
        ."\t<p id='tokenmessage'>\n"
        ."\t".$clang->gT("This survey does not yet have any questions and cannot be tested or completed.")."<br /><br />\n"
        ."\t".sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname'])
        ." (<a href='mailto:{$thissurvey['adminemail']}'>"
        ."{$thissurvey['adminemail']}</a>)<br /><br />\n"
		."\t</p>\n"
        ."\t</div>\n";

        echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
        doFooter();
        exit;
    }

    //Perform a case insensitive natural sort on group name then question title of a multidimensional array
    //	usort($arows, 'GroupOrderThenQuestionOrder');

    //3. SESSION VARIABLE - insertarray
    //An array containing information about used to insert the data into the db at the submit stage
    //4. SESSION VARIABLE - fieldarray
    //See rem at end..
    $_SESSION['token'] = $clienttoken;

    if ($thissurvey['anonymized'] == "N")
    {
        $_SESSION['insertarray'][]= "token";
    }

	if ($tokensexist == 1 && $thissurvey['anonymized'] == "N"  && db_tables_exist($dbprefix.'tokens_'.$surveyid))
    {
        //Gather survey data for "non anonymous" surveys, for use in presenting questions
        $_SESSION['thistoken']=getTokenData($surveyid, $clienttoken);
    }
    $qtypes=getqtypelist('','array');
    $fieldmap=createFieldMap($surveyid,'full',false,false,$_SESSION['s_lang']);

    // Randomization Groups

    // Find all defined randomization groups through question attribute values
    $randomGroups=array();
    if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n' || $databasetype=='mssqlnative')
    {
        $rgquery = "SELECT attr.qid, CAST(value as varchar(255)) FROM ".db_table_name('question_attributes')." as attr right join ".db_table_name('questions')." as quests on attr.qid=quests.qid WHERE attribute='random_group' and CAST(value as varchar(255)) <> '' and sid=$surveyid GROUP BY attr.qid, CAST(value as varchar(255))";
    }
    else
    {
        $rgquery = "SELECT attr.qid, value FROM ".db_table_name('question_attributes')." as attr right join ".db_table_name('questions')." as quests on attr.qid=quests.qid WHERE attribute='random_group' and value <> '' and sid=$surveyid GROUP BY attr.qid, value";
    }
    $rgresult = db_execute_assoc($rgquery);
    while($rgrow = $rgresult->FetchRow())
    {
        // Get the question IDs for each randomization group
        $randomGroups[$rgrow['value']][] = $rgrow['qid'];
    }

    // If we have randomization groups set, then lets cycle through each group and
    // replace questions in the group with a randomly chosen one from the same group
    if (count($randomGroups) > 0)
    {
        $copyFieldMap = array();
        $oldQuestOrder = array();
        $newQuestOrder = array();
        $randGroupNames = array();
        foreach ($randomGroups as $key=>$value)
        {
            $oldQuestOrder[$key] = $randomGroups[$key];
            $newQuestOrder[$key] = $oldQuestOrder[$key];
            // We shuffle the question list to get a random key->qid which will be used to swap from the old key
            shuffle($newQuestOrder[$key]);
            $randGroupNames[] = $key;
        }

        // Loop through the fieldmap and swap each question as they come up
        while (list($fieldkey,$fieldval) = each($fieldmap))
        {
            $found = 0;
            foreach ($randomGroups as $gkey=>$gval)
            {
                // We found a qid that is in the randomization group
                if (isset($fieldval['qid']) && in_array($fieldval['qid'],$oldQuestOrder[$gkey]))
                {
                    // Get the swapped question
                    $oldQuestFlip = array_flip($oldQuestOrder[$gkey]);
                    $qfieldmap = createFieldMap($surveyid,'full',true,$newQuestOrder[$gkey][$oldQuestFlip[$fieldval['qid']]],$_SESSION['s_lang']);
                    unset($qfieldmap['id']);
                    unset($qfieldmap['submitdate']);
                    unset($qfieldmap['lastpage']);
                    unset($qfieldmap['lastpage']);
                    unset($qfieldmap['token']);
                    foreach ($qfieldmap as $tkey=>$tval)
                    {
                        // Assign the swapped question (Might be more than one field)
                        $tval['random_gid'] = $fieldval['gid'];
                        //$tval['gid'] = $fieldval['gid'];
                        $copyFieldMap[$tkey]=$tval;
                    }
                    $found = 1;
                    break;
                } else
                {
                    $found = 2;
                }
            }
            if ($found == 2)
            {
                $copyFieldMap[$fieldkey]=$fieldval;
            }
            reset($randomGroups);
        }
        $fieldmap=$copyFieldMap;

    }
//die(print_r($fieldmap));

    $_SESSION['fieldmap']=$fieldmap;
    foreach ($fieldmap as $field)
    {
        if (isset($field['qid']) && $field['qid']!='')
        {
            $_SESSION['fieldnamesInfo'][$field['fieldname']]=$field['sid'].'X'.$field['gid'].'X'.$field['qid'];
            $_SESSION['insertarray'][]=$field['fieldname'];
            //fieldarray ARRAY CONTENTS -
            //            [0]=questions.qid,
            //			[1]=fieldname,
            //			[2]=questions.title,
            //			[3]=questions.question
            //                 	[4]=questions.type,
            //			[5]=questions.gid,
            //			[6]=questions.mandatory,
            //			[7]=conditionsexist,
            //			[8]=usedinconditions
            //			[8]=usedinconditions
            //			[9]=used in group.php for question count
            //			[10]=new group id for question in randomization group (GroupbyGroup Mode)
            if (!isset($_SESSION['fieldarray'][$field['sid'].'X'.$field['gid'].'X'.$field['qid']]))
            {
                $_SESSION['fieldarray'][$field['sid'].'X'.$field['gid'].'X'.$field['qid']]=array($field['qid'],
                $field['sid'].'X'.$field['gid'].'X'.$field['qid'],
                $field['title'],
                $field['question'],
                $field['type'],
                $field['gid'],
                $field['mandatory'],
                $field['hasconditions'],
                $field['usedinconditions']);
            }
            if (isset($field['random_gid']))
            {
                $_SESSION['fieldarray'][$field['sid'].'X'.$field['gid'].'X'.$field['qid']][10] = $field['random_gid'];
            }
        }

    }

    // Prefill question/answer from defaultvalues
    foreach ($fieldmap as $field)
    {
        if (isset($field['defaultvalue']))
        {
            $_SESSION[$field['fieldname']]=$field['defaultvalue'];
        }
    }
    // Prefill questions/answers from command line params
    if (isset($_SESSION['insertarray']))
    {
        foreach($_SESSION['insertarray'] as $field)
        {
            if (isset($_GET[$field]) && $field!='token')
            {
                $_SESSION[$field]=$_GET[$field];
            }
        }
    }

    $_SESSION['fieldarray']=array_values($_SESSION['fieldarray']);

    // Check if the current survey language is set - if not set it
    // this way it can be changed later (for example by a special question type)
    //Check if a passthru label and value have been included in the query url
    if(isset($_GET['passthru']) && $_GET['passthru'] != "")
    {
        if(isset($_GET[$_GET['passthru']]) && $_GET[$_GET['passthru']] != "")
        {
            $_SESSION['passthrulabel']=$_GET['passthru'];
            $_SESSION['passthruvalue']=$_GET[$_GET['passthru']];
        }

    }
    // New: If no passthru variable is explicitely set, save the whole query_string - above method is obsolete and the new way should only be used
    elseif (isset($_SERVER['QUERY_STRING']))
    {
        $_SESSION['ls_initialquerystr']=$_SERVER['QUERY_STRING'];
    }
    // END NEW

    // Fix totalquestions by substracting Test Display questions
    $sNoOfTextDisplayQuestions=(int) $connect->GetOne("SELECT count(*)\n"
        ." FROM ".db_table_name('questions')
        ." WHERE type in ('X','*')\n"
        ." AND sid={$surveyid}"
        ." AND language='".$_SESSION['s_lang']."'"
        ." AND parent_qid=0");

    $_SESSION['therearexquestions'] = $totalquestions - $sNoOfTextDisplayQuestions; // must be global for THEREAREXQUESTIONS replacement field to work

    return $totalquestions-$sNoOfTextDisplayQuestions;
}

function surveymover()
{
    //This function creates the form elements in the survey navigation bar
    //with "<<PREV" or ">>NEXT" in them. The "submit" value determines how the script moves from
    //one survey page to another. It is a hidden element, updated by clicking
    //on the  relevant button - allowing "NEXT" to be the default setting when
    //a user presses enter.
    //
    //Attribute accesskey added for keyboard navigation.
    global $thissurvey, $clang;
    global $surveyid, $presentinggroupdescription;
    $surveymover = "";

    if ($thissurvey['navigationdelay'] > 0 && (
        isset($_SESSION['maxstep']) && $_SESSION['maxstep'] > 0 && $_SESSION['maxstep'] == $_SESSION['step']))
    {
        $disabled = "disabled=\"disabled\"";
        $surveymover .= "<script type=\"text/javascript\">\n"
        . "  navigator_countdown(" . $thissurvey['navigationdelay'] . ");\n"
        . "</script>\n";
    }
    else
    {
        $disabled = "";
    }

    if (isset($_SESSION['step']) && $_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && !$presentinggroupdescription && $thissurvey['format'] != "A")
    {
        $surveymover .= "<input type=\"hidden\" name=\"move\" value=\"movesubmit\" id=\"movesubmit\" />";
    }
    else
    {
        $surveymover .= "<input type=\"hidden\" name=\"move\" value=\"movenext\" id=\"movenext\" />";
    }

    if (isset($_SESSION['step']) && $thissurvey['format'] != "A" && ($thissurvey['allowprev'] != "N" || $thissurvey['allowjumps'] == "Y") &&
	($_SESSION['step'] > 0 || (!$_SESSION['step'] && $presentinggroupdescription && $thissurvey['showwelcome'] == 'Y')))
    {
        //To prevent too much complication in the if statement above I put it here...
        if ($thissurvey['showwelcome'] == 'N' && $_SESSION['step'] == 1) {
           //first step and we do not want to go back to the welcome screen since we don't show that...
           //so skip the prev button
        } else {
            $surveymover .= "<input class='submit' accesskey='p' type='button' onclick=\"javascript:document.limesurvey.move.value = 'moveprev'; $('#limesurvey').submit();\" value=' &lt;&lt; "
            . $clang->gT("Previous")." ' name='move2' id='moveprevbtn' $disabled />\n";
        }
    }
    if (isset($_SESSION['step']) && $_SESSION['step'] && (!$_SESSION['totalsteps'] || ($_SESSION['step'] < $_SESSION['totalsteps'])))
    {
        $surveymover .=  "\t<input class='submit' type='submit' accesskey='n' onclick=\"javascript:document.limesurvey.move.value = 'movenext';\" value=' "
        . $clang->gT("Next")." &gt;&gt; ' name='move2' id='movenextbtn' $disabled />\n";
    }
    // here, in some lace, is where I must modify to turn the next button conditionable
    if (!isset($_SESSION['step']) || !$_SESSION['step'])
    {
        $surveymover .=  "\t<input class='submit' type='submit' accesskey='n' onclick=\"javascript:document.limesurvey.move.value = 'movenext';\" value=' "
        . $clang->gT("Next")." &gt;&gt; ' name='move2' id='movenextbtn' $disabled />\n";
    }
    if (isset($_SESSION['step']) && $_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && $presentinggroupdescription == "yes")
    {
        $surveymover .=  "\t<input class='submit' type='submit' onclick=\"javascript:document.limesurvey.move.value = 'movenext';\" value=' "
        . $clang->gT("Next")." &gt;&gt; ' name='move2' id=\"movenextbtn\" $disabled />\n";
    }
    if (($_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && !$presentinggroupdescription) || $thissurvey['format'] == 'A')
    {
        $surveymover .= "\t<input class=\"submit\" type=\"submit\" accesskey=\"l\" onclick=\"javascript:document.limesurvey.move.value = 'movesubmit';\" value=\""
        . $clang->gT("Submit")."\" name=\"move2\" id=\"movesubmitbtn\" $disabled />\n";
    }

    //	$surveymover .= "<input type='hidden' name='PHPSESSID' value='".session_id()."' id='PHPSESSID' />\n";
    return $surveymover;
}


/**
 * Caculate assessement scores
 *
 * @param mixed $surveyid
 * @param mixed $returndataonly - only returns an array with data
 */
function doAssessment($surveyid, $returndataonly=false)
{
    global $dbprefix, $thistpl, $connect;
    $baselang=GetBaseLanguageFromSurveyID($surveyid);
    $total=0;
    if (!isset($_SESSION['s_lang']))
    {
        $_SESSION['s_lang']=$baselang;
    }
    $query = "SELECT * FROM ".db_table_name('assessments')."
			  WHERE sid=$surveyid and language='{$_SESSION['s_lang']}'
			  ORDER BY scope,id";
    if ($result = db_execute_assoc($query))   //Checked

    {
        if ($result->RecordCount() > 0)
        {
            while ($row=$result->FetchRow())
            {
                if ($row['scope'] == "G")
                {
                    $assessment['group'][$row['gid']][]=array("name"=>$row['name'],
                            "min"=>$row['minimum'],
                            "max"=>$row['maximum'],
                            "message"=>$row['message']);
                }
                else
                {
                    $assessment['total'][]=array( "name"=>$row['name'],
                            "min"=>$row['minimum'],
                            "max"=>$row['maximum'],
                            "message"=>$row['message']);
                }
            }
            $fieldmap=createFieldMap($surveyid, "full");
            $i=0;
            $total=0;
            $groups=array();
            foreach($fieldmap as $field)
            {
                if (in_array($field['type'],array('1','F','H','W','Z','L','!','M','O','P',":")))
                {
                    $fieldmap[$field['fieldname']]['assessment_value']=0;
                    if (isset($_SESSION[$field['fieldname']]))
                    {
                        if ($field['type']==':') //Multiflexi numbers  - result is the assessment value

                        {
                            $fieldmap[$field['fieldname']]['assessment_value']=$_SESSION[$field['fieldname']];
                            $total=$total+$_SESSION[$field['fieldname']];
                        }
                        else
                        {

                                    $usquery = "SELECT assessment_value FROM ".db_table_name("answers")." where qid=".$field['qid']." and language='$baselang' and code=".db_quoteall($_SESSION[$field['fieldname']]);
                            $usresult = db_execute_assoc($usquery);          //Checked
                            if ($usresult)
                            {
                                $usrow = $usresult->FetchRow();

                                if (($field['type'] == "M") || ($field['type'] == "P"))
                                {
                                    if ($_SESSION[$field['fieldname']] == "Y")     // for Multiple choice type questions
                                    {
                                        $aAttributes=getQuestionAttributes($field['qid'],$field['type']);
                                        $fieldmap[$field['fieldname']]['assessment_value']=(int)$aAttributes['assessment_value'];
                                        $total=$total+$usrow['assessment_value'];
                                    }
                                }
                                else     // any other type of question

                                {
                                    $fieldmap[$field['fieldname']]['assessment_value']=$usrow['assessment_value'];
                                    $total=$total+$usrow['assessment_value'];
                                }
                            }
                        }
                    }
                    $groups[]=$field['gid'];
                }
                $i++;
            }

            $groups=array_unique($groups);

            foreach($groups as $group)
            {
                $grouptotal=0;
                foreach ($fieldmap as $field)
                {
                    if ($field['gid'] == $group && isset($field['assessment_value']))
                    {
                        //$grouptotal=$grouptotal+$field['answer'];
                        if (isset ($_SESSION[$field['fieldname']]))
                        {
                            if (($field['type'] == "M") and ($_SESSION[$field['fieldname']] == "Y")) 	// for Multiple choice type questions
                            $grouptotal=$grouptotal+$field['assessment_value'];
                            else																		// any other type of question
                            $grouptotal=$grouptotal+$field['assessment_value'];
                        }
                    }
                }
                $subtotal[$group]=$grouptotal;
            }
        }
        $assessments = "";
        if (isset($subtotal) && is_array($subtotal))
        {
            foreach($subtotal as $key=>$val)
            {
                if (isset($assessment['group'][$key]))
                {
                    foreach($assessment['group'][$key] as $assessed)
                    {
                        if ($val >= $assessed['min'] && $val <= $assessed['max'] && $returndataonly===false)
                        {
                            $assessments .= "\t<!-- GROUP ASSESSMENT: Score: $val Min: ".$assessed['min']." Max: ".$assessed['max']."-->
        					    <table class='assessments' align='center'>
								 <tr>
								  <th>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $total), $assessed['name'])."
								  </th>
								 </tr>
								 <tr>
								  <td align='center'>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $total), $assessed['message'])."
								 </td>
								</tr>
							   </table><br />\n";
                        }
                    }
                }
            }
        }

        if (isset($assessment['total']))
        {
            foreach($assessment['total'] as $assessed)
            {
                if ($total >= $assessed['min'] && $total <= $assessed['max'] && $returndataonly===false)
                {
                    $assessments .= "\t\t\t<!-- TOTAL ASSESSMENT: Score: $total Min: ".$assessed['min']." Max: ".$assessed['max']."-->
						<table class='assessments' align='center'><tr><th>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $total), stripslashes($assessed['name']))."
						 </th></tr>
						 <tr>
						  <td align='center'>".str_replace(array("{PERC}", "{TOTAL}"), array($val, $total), stripslashes($assessed['message']))."
						  </td>
						 </tr>
						</table>\n";
                }
            }
        }
        if ($returndataonly==true)
        {
            return array('total'=>$total);
        }
        else
        {
            return $assessments;
        }
    }
}

function UpdateSessionGroupList($language)
//1. SESSION VARIABLE: grouplist
//A list of groups in this survey, ordered by group name.

{
    global $surveyid;
    unset ($_SESSION['grouplist']);
    $query = "SELECT * FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='".$language."' ORDER BY group_order";
    $result = db_execute_assoc($query) or safe_die ("Couldn't get group list<br />$query<br />".$connect->ErrorMsg());  //Checked
    while ($row = $result->FetchRow())
    {
        $_SESSION['grouplist'][]=array($row['gid'], $row['group_name'], $row['description']);
    }
}

function UpdateFieldArray()
//The FieldArray contains all necessary information regarding the questions
//This function is needed to update it in case the survey is switched to another language

{
    global $surveyid;

    if (isset($_SESSION['fieldarray']))
    {
        reset($_SESSION['fieldarray']);
        while ( list($key) = each($_SESSION['fieldarray']) )
        {
            $questionarray =& $_SESSION['fieldarray'][$key];

            $query = "SELECT * FROM ".db_table_name('questions')." WHERE qid=".$questionarray[0]." AND language='".$_SESSION['s_lang']."'";
            $result = db_execute_assoc($query) or safe_die ("Couldn't get question <br />$query<br />".$connect->ErrorMsg());      //Checked
            $row = $result->FetchRow();
            $questionarray[2]=$row['title'];
            $questionarray[3]=$row['question'];
            unset($questionarray);
        }
    }

}


/**
 * check_quota() returns quota information for the current survey
 * @param string $checkaction - action the function must take after completing:
 * 								enforce: Enforce the Quota action
 * 								return: Return the updated quota array from getQuotaAnswers()
 * @param string $surveyid - Survey identification number
 * @return array - nested array, Quotas->Members->Fields, includes quota status and which members matched in session.
 */
function check_quota($checkaction,$surveyid)
{
    if (!isset($_SESSION['s_lang'])){
        return;
    }
    global $thistpl, $clang, $clienttoken, $publicurl;
    $global_matched = false;
    $quota_info = getQuotaInformation($surveyid, $_SESSION['s_lang']);
    $x=0;

    if(count($quota_info) > 0) // Quota's have to exist
    {
        // Check each quota on saved data to see if it is full
        $querycond = array();
        foreach ($quota_info as $quota)
        {
            if (count($quota['members']) > 0) // Quota can't be empty
            {
                $fields_list = array(); // Keep a list of fields for easy reference
                $y=0;
                // We need to make the conditions for the select statement here
                unset($querycond);
                // fill the array of value and query for each fieldnames
                $fields_value_array = array();
                $fields_query_array = array();
                foreach($quota['members'] as $member)
                {
                    foreach($member['fieldnames'] as $fieldname)
                    {

                        if (!in_array($fieldname,$fields_list))
                        {
                            $fields_list[] = $fieldname;
                            $fields_value_array[$fieldname] = array();
                            $fields_query_array[$fieldname] = array();
                        }
                        $fields_value_array[$fieldname][]=$member['value'];
                        $fields_query_array[$fieldname][]= db_quote_id($fieldname)." = '{$member['value']}'";
                    }

                }
                // fill the $querycond array with each fields_query grouped by fieldname
                foreach($fields_list as $fieldname)
                {
                    $select_query = " ( ".implode(' OR ',$fields_query_array[$fieldname]).' )';
                    $querycond[] = $select_query;
                }
                // Test if the fieldname is in the array of value in the session
                foreach($quota['members'] as $member)
                {
                    foreach($member['fieldnames'] as $fieldname)
                    {
                	if (isset($_SESSION[$fieldname]))
                        {
                        if (in_array($_SESSION[$fieldname],$fields_value_array[$fieldname])){
                            $quota_info[$x]['members'][$y]['insession'] = "true";
                            }
                        }
                    }
                   $y++;
                }
                unset($fields_query_array);unset($fields_value_array);

                // Lets only continue if any of the quota fields is in the posted page
                $matched_fields = false;
                if (isset($_POST['fieldnames']))
                {
                    $posted_fields = explode("|",$_POST['fieldnames']);
                    foreach ($fields_list as $checkfield)
                    {
                        if (in_array($checkfield,$posted_fields))
                        {
                            $matched_fields = true;
                            $global_matched = true;
                        }
                    }
                }

                // A field was submitted that is part of the quota

                if ($matched_fields == true)
                {

                    // Check the status of the quota, is it full or not
                    $querysel = "SELECT id FROM ".db_table_name('survey_'.$surveyid)."
					             WHERE ".implode(' AND ',$querycond)." "."
								 AND submitdate IS NOT NULL";

                    $result = db_execute_assoc($querysel) or safe_die($connect->ErrorMsg());    //Checked
                    $quota_check = $result->FetchRow();

                    if ($result->RecordCount() >= $quota['Limit']) // Quota is full!!

                    {
                        // Now we have to check if the quota matches in the current session
                        // This will let us know if this person is going to exceed the quota

                        $counted_matches = 0;
                        foreach($quota_info[$x]['members'] as $member)
                        {
                            if (isset($member['insession']) && $member['insession'] == "true") $counted_matches++;
                        }
                        if($counted_matches == count($quota['members']))
                        {
                            // They are going to exceed the quota if data is submitted
                            $quota_info[$x]['status']="matched";

                        } else
                        {
                            $quota_info[$x]['status']="notmatched";
                        }

                    } else
                    {
                        // Quota is no in danger of being exceeded.
                        $quota_info[$x]['status']="notmatched";
                    }
                }

            }
            $x++;
        }

    } else
    {
        return false;
    }

    // Now we have all the information we need about the quotas and their status.
    // Lets see what we should do now
    if ($checkaction == 'return')
    {
        return $quota_info;
    } else if ($global_matched == true && $checkaction == 'enforce')
    {
        // Need to add Quota action enforcement here.
        reset($quota_info);

        $tempmsg ="";
        $found = false;
        foreach($quota_info as $quota)
        {
            if ((isset($quota['status']) && $quota['status'] == "matched") && (isset($quota['Action']) && $quota['Action'] == "1"))
            {
                // If a token is used then mark the token as completed
                if (isset($clienttoken) && $clienttoken)
                {
                    submittokens(true);
                }
                session_destroy();
                sendcacheheaders();
                if($quota['AutoloadUrl'] == 1 && $quota['Url'] != "")
                {
                    header("Location: ".$quota['Url']);
                }
                doHeader();
                echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
                echo "\t<div class='quotamessage'>\n";
                echo "\t".$quota['Message']."<br /><br />\n";
                echo "\t<a href='".$quota['Url']."'>".$quota['UrlDescrip']."</a><br />\n";
                echo "\t</div>\n";
                echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
                doFooter();
                exit;
            }

            if ((isset($quota['status']) && $quota['status'] == "matched") && (isset($quota['Action']) && $quota['Action'] == "2"))
            {

                sendcacheheaders();
                doHeader();
                echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
                echo "\t<div class='quotamessage'>\n";
                echo "\t".$quota['Message']."<br /><br />\n";
                echo "\t<a href='".$quota['Url']."'>".$quota['UrlDescrip']."</a><br />\n";
                echo "<form method='post' action='{$publicurl}/index.php' id='limesurvey' name='limesurvey'><input type=\"hidden\" name=\"move\" value=\"movenext\" id=\"movenext\" /><input class='submit' accesskey='p' type='button' onclick=\"javascript:document.limesurvey.move.value = 'moveprev'; document.limesurvey.submit();\" value=' &lt;&lt; ". $clang->gT("Previous")." ' name='move2' />
					<input type='hidden' name='thisstep' value='".($_SESSION['step'])."' id='thisstep' />
					<input type='hidden' name='sid' value='".returnglobal('sid')."' id='sid' />
					<input type='hidden' name='token' value='".$clienttoken."' id='token' />
					</form>\n";
                echo "\t</div>\n";
                echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
                doFooter();
                exit;
            }
        }


    } else
    {
        // Unknown value
        return false;
    }

}

/**
 * put your comment there...
 *
 * @param mixed $mail
 * @param mixed $text
 * @param mixed $class
 * @param mixed $params
 */
function encodeEmail($mail, $text="", $class="", $params=array())
{
    $encmail ="";
    for($i=0; $i<strlen($mail); $i++)
    {
        $encMod = rand(0,2);
        switch ($encMod)
        {
            case 0: // None
                $encmail .= substr($mail,$i,1);
                break;
            case 1: // Decimal
                $encmail .= "&#".ord(substr($mail,$i,1)).';';
                break;
            case 2: // Hexadecimal
                $encmail .= "&#x".dechex(ord(substr($mail,$i,1))).';';
                break;
        }
    }

    if(!$text)
    {
        $text = $encmail;
    }
    return $text;
}



/**
 * GetReferringUrl() returns the reffering URL
 */
function GetReferringUrl()
{
    global $clang,$stripQueryFromRefurl;
    if (isset($_SESSION['refurl']))
    {
        return; // do not overwrite refurl
    }

    // refurl is not set in session, read it from server variable
    if(isset($_SERVER["HTTP_REFERER"]))
    {
        if(!preg_match('/'.$_SERVER["SERVER_NAME"].'/', $_SERVER["HTTP_REFERER"]))
        {
            if (!isset($stripQueryFromRefurl) || !$stripQueryFromRefurl)
            {
                $_SESSION['refurl'] = $_SERVER["HTTP_REFERER"];
            }
            else
            {
                $aRefurl = explode("?",$_SERVER["HTTP_REFERER"]);
                $_SESSION['refurl'] = $aRefurl[0];
            }
        }
        else
        {
            $_SESSION['refurl'] = '-';
        }
    }
    else
    {
        $_SESSION['refurl'] = null;
    }
}

/**
 * Shows the welcome page, used in group by group and question by question mode
 */
 function display_first_page() {
    global $clang, $thistpl, $token, $surveyid, $thissurvey, $navigator,$publicurl;
    sendcacheheaders();
    doHeader();

    echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"));
    echo "\n<form method='post' action='{$publicurl}/index.php' id='limesurvey' name='limesurvey' autocomplete='off'>\n";

    echo "\n\n<!-- START THE SURVEY -->\n";

    echo templatereplace(file_get_contents("$thistpl/welcome.pstpl"))."\n";
    if ($thissurvey['anonymized'] == "Y")
    {
        echo templatereplace(file_get_contents("$thistpl/privacy.pstpl"))."\n";
    }
    $navigator = surveymover();
    echo templatereplace(file_get_contents("$thistpl/navigator.pstpl"));
    if ($thissurvey['active'] != "Y")
    {
        echo "<p style='text-align:center' class='error'>".$clang->gT("This survey is currently not active. You will not be able to save your responses.")."</p>\n";
    }
    echo "\n<input type='hidden' name='sid' value='$surveyid' id='sid' />\n";
    if (isset($token) && !empty($token)) {
        echo "\n<input type='hidden' name='token' value='$token' id='token' />\n";
    }
    echo "\n<input type='hidden' name='lastgroupname' value='_WELCOME_SCREEN_' id='lastgroupname' />\n"; //This is to ensure consistency with mandatory checks, and new group test
    $loadsecurity = returnglobal('loadsecurity');
    if (isset($loadsecurity)) {
        echo "\n<input type='hidden' name='loadsecurity' value='$loadsecurity' id='loadsecurity' />\n";
    }
    echo "\n</form>\n";
    echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"));
    doFooter();
}
// Closing PHP tag intentionally left out - yes, it is okay
