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
 */

global $errormsg;   // since neeeded by savecontrol()

    function showsaveform()
    {
    //Show 'SAVE FORM' only when click the 'Save so far' button the first time, or when duplicate is found on SAVE FORM.
    global $thistpl, $errormsg, $thissurvey, $surveyid, $clang, $clienttoken, $relativeurl, $thisstep;
    sendcacheheaders();
    doHeader();
    foreach(file("$thistpl/startpage.pstpl") as $op)
    {
        echo templatereplace($op);
    }
    echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n"
    ."\t<script type='text/javascript'>\n"
    ."\t<!--\n"
    ."function checkconditions(value, name, type, evt_type)\n"
    ."\t{\n"
    ."\t}\n"
    ."\t//-->\n"
    ."\t</script>\n\n";

    echo "<form method='post' action='$relativeurl/index.php'>\n";
    //PRESENT OPTIONS SCREEN
    if (isset($errormsg) && $errormsg != "")
    {
        $errormsg .= "<p>".$clang->gT("Please try again.")."</p>";
    }
    foreach(file("$thistpl/save.pstpl") as $op)
    {
        echo templatereplace($op);
    }
    //END
    echo "<input type='hidden' name='sid' value='$surveyid' />\n";
    echo "<input type='hidden' name='thisstep' value='",$thisstep,"' />\n";
    echo "<input type='hidden' name='token' value='",$clienttoken,"' />\n";
    echo "<input type='hidden' name='saveprompt' value='Y' />\n";
    echo "</form>";

    foreach(file("$thistpl/endpage.pstpl") as $op)
    {
        echo templatereplace($op);
    }
    echo "</html>\n";
    exit;
    }



    function savedcontrol()
    {
    //This data will be saved to the "saved_control" table with one row per response.
    // - a unique "saved_id" value (autoincremented)
    // - the "sid" for this survey
    // - the "srid" for the survey_x row id
    // - "saved_thisstep" which is the step the user is up to in this survey
    // - "saved_ip" which is the ip address of the submitter
    // - "saved_date" which is the date ofthe saved response
    // - an "identifier" which is like a username
    // - a "password"
    // - "fieldname" which is the fieldname of the saved response
    // - "value" which is the value of the response
    //We start by generating the first 5 values which are consistent for all rows.

    global $connect, $surveyid, $dbprefix, $thissurvey, $errormsg, $publicurl, $sitename, $timeadjust, $clang, $clienttoken, $thisstep;

    //Check that the required fields have been completed.
    $errormsg="";
    if (!isset($_POST['savename']) || !$_POST['savename']) {$errormsg.=$clang->gT("You must supply a name for this saved session.")."<br />\n";}
    if (!isset($_POST['savepass']) || !$_POST['savepass']) {$errormsg.=$clang->gT("You must supply a password for this saved session.")."<br />\n";}
    if ((isset($_POST['savepass']) && !isset($_POST['savepass2'])) || $_POST['savepass'] != $_POST['savepass2'])
    {$errormsg.=$clang->gT("Your passwords do not match.")."<br />\n";}
    // if security question asnwer is incorrect
    if (function_exists("ImageCreate") && captcha_enabled('saveandloadscreen',$thissurvey['usecaptcha']))
    {
        if (!isset($_POST['loadsecurity']) ||
        !isset($_SESSION['secanswer']) ||
        $_POST['loadsecurity'] != $_SESSION['secanswer'])
        {
            $errormsg .= $clang->gT("The answer to the security question is incorrect.")."<br />\n";
        }
    }

    if (trim($_POST['saveemail'])!='' && !validate_email($_POST['saveemail']))
    {
            $errormsg .= $clang->gT("The email address is not valid. Please leave the email field blank or give a valid email address.")."<br />\n";
    }


    if ($errormsg)
    {
        return;
    }
    //All the fields are correct. Now make sure there's not already a matching saved item
    $query = "SELECT COUNT(*) FROM {$dbprefix}saved_control\n"
    ."WHERE sid=$surveyid\n"
    ."AND identifier=".db_quoteall($_POST['savename'],true);
    $result = db_execute_num($query) or safe_die("Error checking for duplicates!<br />$query<br />".$connect->ErrorMsg());   // Checked
    list($count) = $result->FetchRow();
    if ($count > 0)
    {
        $errormsg.=$clang->gT("This name has already been used for this survey. You must use a unique save name.")."<br />\n";
        return;
    }
    else
    {
        //INSERT BLANK RECORD INTO "survey_x" if one doesn't already exist
        if (!isset($_SESSION['srid']))
        {
            $today = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust);
            $sdata = array("datestamp"=>$today,
			"ipaddr"=>$_SERVER['REMOTE_ADDR'],
			"startlanguage"=>$_SESSION['s_lang'],
			"refurl"=>getenv("HTTP_REFERER"));
            //One of the strengths of ADOdb's AutoExecute() is that only valid field names for $table are updated
            if ($connect->AutoExecute($thissurvey['tablename'], $sdata,'INSERT'))    // Checked
            {
                $srid = $connect->Insert_ID($thissurvey['tablename'],"sid");
                $_SESSION['srid'] = $srid;
            }
            else
            {
                safe_die("Unable to insert record into survey table.<br /><br />".$connect->ErrorMsg());
            }
        }
        //CREATE ENTRY INTO "saved_control"
        $today = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust);
        $scdata = array("sid"=>$surveyid,
		"srid"=>$_SESSION['srid'],
		"identifier"=>$_POST['savename'], // Binding does escape , so no quoting/escaping necessary
		"access_code"=>md5($_POST['savepass']),
		"email"=>$_POST['saveemail'],
		"ip"=>$_SERVER['REMOTE_ADDR'],
		"refurl"=>getenv("HTTP_REFERER"),
		"saved_thisstep"=>$thisstep,
		"status"=>"S",
		"saved_date"=>$today);


        if ($connect->AutoExecute("{$dbprefix}saved_control", $scdata,'INSERT'))   // Checked
        {
            $scid = $connect->Insert_ID("{$dbprefix}saved_control",'scid');
            $_SESSION['scid'] = $scid;
        }
        else
        {
            safe_die("Unable to insert record into saved_control table.<br /><br />".$connect->ErrorMsg());
        }

        $_SESSION['holdname']=$_POST['savename']; //Session variable used to load answers every page. Unsafe - so it has to be taken care of on output
        $_SESSION['holdpass']=$_POST['savepass']; //Session variable used to load answers every page.  Unsafe - so it has to be taken care of on output

        //Email if needed
        if (isset($_POST['saveemail']) )
        {
            $subject=$clang->gT("Saved Survey Details") . " - " . $thissurvey['name'];
            $message=$clang->gT("Thank you for saving your survey in progress.  The following details can be used to return to this survey and continue where you left off.  Please keep this e-mail for your reference - we cannot retrieve the password for you.","unescaped");
            $message.="\n\n".$thissurvey['name']."\n\n";
            $message.=$clang->gT("Name","unescaped").": ".$_POST['savename']."\n";
            $message.=$clang->gT("Password","unescaped").": ".$_POST['savepass']."\n\n";
            $message.=$clang->gT("Reload your survey by clicking on the following link (or pasting it into your browser):","unescaped")."\n";
            $message.=$publicurl."/index.php?sid=$surveyid&loadall=reload&scid=".$scid."&loadname=".urlencode($_POST['savename'])."&loadpass=".urlencode($_POST['savepass']);

            if ($clienttoken){$message.="&token=".$clienttoken;}
            $from="{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";
            if (SendEmailMessage(null, $message, $subject, $_POST['saveemail'], $from, $sitename, false, getBounceEmail($surveyid)))
            {
                $emailsent="Y";
            }
            else
            {
                echo "Error: Email failed, this may indicate a PHP Mail Setup problem on your server. Your survey details have still been saved, however you will not get an email with the details. You should note the \"name\" and \"password\" you just used for future reference.";
            }
        }
        return  $clang->gT('Your survey was successfully saved.');
    }
    }

/**
    * savesilent() saves survey responses when the "Resume later" button
    * is press but has no interaction. i.e. it does not ask for email,
    * username or password or capture.
    *
    * @return string confirming successful save.
*/
function savedsilent()
{
    global $connect, $surveyid, $dbprefix, $thissurvey, $errormsg, $publicurl, $sitename, $timeadjust, $clang, $clienttoken, $thisstep, $modrewrite;
    submitanswer();
    // Prepare email
    $tokenentryquery = 'SELECT * from '.$dbprefix.'tokens_'.$surveyid.' WHERE token=\''.sanitize_paranoid_string($clienttoken).'\';';
    $tokenentryresult = db_execute_assoc($tokenentryquery);
    $tokenentryarray = $tokenentryresult->FetchRow();

    $from = $thissurvey['adminname'].' <'.$thissurvey['adminemail'].'>';
    $to = $tokenentryarray['firstname'].' '.$tokenentryarray['lastname'].' <'.$tokenentryarray['email'].'>';
    $subject = $clang->gT("Saved Survey Details") . " - " . $thissurvey['name'];
    $message = $clang->gT("Thank you for saving your survey in progress. You can return to the survey at the same point you saved it at any time using the link from this or any previous email sent to regarding this survey.","unescaped")."\n\n";
    $message .= $clang->gT("Reload your survey by clicking on the following link (or pasting it into your browser):","unescaped")."\n";
    $language = $tokenentryarray['language'];

    if($modrewrite)
    {
        $message .= "\n\n$publicurl/$surveyid/lang-$language/tk-$clienttoken";
    }
    else
    {
        $message .= "\n\n$publicurl/index.php?lang=$language&sid=$surveyid&token=$clienttoken";
    }
    if (SendEmailMessage(null, $message, $subject, $to, $from, $sitename, false, getBounceEmail($surveyid)))
    {
        $emailsent="Y";
    }
    else
    {
        echo "Error: Email failed, this may indicate a PHP Mail Setup problem on your server. Your survey details have still been saved, however you will not get an email with the details. You should note the \"name\" and \"password\" you just used for future reference.";
    }
    return  $clang->gT('Your survey was successfully saved.');
}

// submitanswer sets the submitdate
// Only used by question.php and group.php if next pages
// should not display due to conditions and generally used by survey.php
// In this case all answers have already been updated by save.php
// but movesubmit status was only set after calling save.php
// ==> thus we need to update submitdate here.
function submitanswer()
{
    global $thissurvey,$timeadjust;
    global $surveyid, $connect, $clang, $move;

    if ($thissurvey['anonymized'] =="Y" && $thissurvey['datestamp'] =="N")
    {
        // In case of anonymized responses survey with no datestamp
        // then the the answer submitdate gets a conventional timestamp
        // 1st Jan 1980
        $mysubmitdate = date("Y-m-d H:i:s",mktime(0,0,0,1,1,1980));
    }
    else
    {
        $mysubmitdate = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust);
    }

    $query = "";
    if (isset($move) && ($move == "movesubmit") && ($thissurvey['active'] == "Y"))
    {
        if (!isset($_SESSION['srid']))
        { //due to conditions no answer was displayed and yet we must submit
            $query=createinsertquery();
            if ($result=$connect->Execute($query))
            {
                $tempID=$connect->Insert_ID($thissurvey['tablename'],"id");
                $_SESSION['srid'] = $tempID;
            }
        }
        $query = "UPDATE {$thissurvey['tablename']} SET ";
        $query .= " submitdate = ".$connect->DBDate($mysubmitdate);
        $query .= " WHERE id=" . $_SESSION['srid'];
    }

    $result=$connect->Execute($query);    // Checked
    return $result;
}

 /**
 * This functions saves the answer time for question/group and whole survey.
 * [ It compares current time with the time in $_POST['start_time'] ]
 * The times are saved in table: {prefix}{surveytable}_timings
 * @return void
 */
function set_answer_time()
{
	global $connect, $thissurvey, $surveyid;
    if (!isset($_POST['start_time']))
    {
        return; // means haven't passed welcome page yet.
    }

    if (isset($_POST['lastanswer']))
    {
        $setField = $_POST['lastanswer'];
    }
    else if (isset($_POST['lastgroup']))
    {
		$setField = $_POST['lastgroup'];
    }

	$passedTime = round(microtime(true) - $_POST['start_time'],2);

    $tablename = db_table_name('survey_' . $surveyid . '_timings');

	if(!isset($setField)){ //we show the whole survey on one page - we don't have to save time for group/question
        $query = "UPDATE ".$tablename." SET "
            ."interviewtime = interviewtime" ." + " .$passedTime
            ." WHERE id = " .$_SESSION['srid'];
		$connect->execute($query);
		return;
	}
    else
    {
        $setField .= "time";
        //saving the times
        $query = "UPDATE ".$tablename." SET "
            ."interviewtime = interviewtime" ." + " .$passedTime .","
            .db_quote_id($setField) ." = " .db_quote_id($setField) ." + " .$passedTime
            ." WHERE id = " .$_SESSION['srid'];
        $connect->execute($query);
    }
}
?>
