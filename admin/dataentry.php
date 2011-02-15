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

/*
 * We need this later:
 *  1 - Array (Flexible Labels) Dual Scale
 5 - 5 Point Choice
 A - Array (5 Point Choice)
 B - Array (10 Point Choice)
 C - Array (Yes/No/Uncertain)
 D - Date
 E - Array (Increase, Same, Decrease)
 F - Array (Flexible Labels)
 G - Gender
 H - Array (Flexible Labels) by Column
 I - Language Switch
 K - Multiple Numerical Input
 L - List (Radio)
 M - Multiple choice
 N - Numerical Input
 O - List With Comment
 P - Multiple choice with comments
 Q - Multiple Short Text
 R - Ranking
 S - Short Free Text
 T - Long Free Text
 U - Huge Free Text
 X - Boilerplate Question
 Y - Yes/No
 ! - List (Dropdown)
 : - Array (Flexible Labels) multiple drop down
 ; - Array (Flexible Labels) multiple texts
 | - File Upload Question


 */

include_once("login_check.php");
$sDataEntryLanguage = $_SESSION['adminlang'];
//RL: set language for questions and labels to current admin language for browsing responses

$action = returnglobal('action');
$surveyid = returnglobal('sid');
$id = returnglobal('id');
$sDataEntryLanguage = returnglobal('lang');    
$saver['scid']=returnglobal('save_scid');
$surveytable = db_table_name("survey_".$surveyid);
$dataentryoutput ='';

include_once("login_check.php");

$dateformatdetails=getDateFormatData($_SESSION['dateformat']);

if (!isset($sDataEntryLanguage))
{
    $sDataEntryLanguage = GetBaseLanguageFromSurveyID($surveyid);
}
$surveyinfo=getSurveyInfo($surveyid);

if (bHasSurveyPermission($surveyid, 'responses','read') || bHasSurveyPermission($surveyid, 'responses','create')  || bHasSurveyPermission($surveyid, 'responses','update'))
{

    $surveyoptions = browsemenubar($clang->gT("Data entry"));
    if (!$surveyid && !$subaction)
    {
        $dataentryoutput .= "<div class='header ui-widget-header'>".$clang->gT("Data entry")."</div>\n";
        $dataentryoutput .= "<div class='messagebox ui-corner-all'>\n"
        ."<div class='warningheader'>".$clang->gT("Error")."</div><br />\n"
        .$clang->gT("You have not selected a survey for data-entry.")."<br /><br />\n"
        ."<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br />\n"
        ."</div>\n";
        return;
    }

    if ($subaction == "insert" && bHasSurveyPermission($surveyid,'responses','create'))
    {
        $thissurvey=getSurveyInfo($surveyid);
        $errormsg="";
        $dataentryoutput .= $surveyoptions."<div class='header ui-widget-header'>".$clang->gT("Data entry")."</div>\n"
        ."\t<div class='messagebox ui-corner-all'>\n";

        $lastanswfortoken=''; // check if a previous answer has been submitted or saved
        $rlanguage='';
        if (isset($_POST['token']) && $_POST['token'])
        {
            $tokencompleted = "";
            $tokentable = db_table_name("tokens_".$surveyid);
            $tcquery = "SELECT completed from $tokentable WHERE token=".db_quoteall($_POST['token'],true);
            $tcresult = db_execute_assoc($tcquery);
            $tccount = $tcresult->RecordCount();
            while ($tcrow = $tcresult->FetchRow())
            {
                $tokencompleted = $tcrow['completed'];
            }

            if ($tccount < 1)
            { // token doesn't exist in token table
                $lastanswfortoken='UnknownToken';
            }
            elseif ($thissurvey['anonymized'] == "Y")
            { // token exist but survey is anonymous, check completed state
                if ($tokencompleted != "" && $tokencompleted != "N")
                { // token is completed
                    $lastanswfortoken='PrivacyProtected';
                }
            }
            else
            { // token is valid, survey not anonymous, try to get last recorded response id
                $aquery = "SELECT id,startlanguage FROM $surveytable WHERE token=".db_quoteall($_POST['token'],true);
                $aresult = db_execute_assoc($aquery);
                while ($arow = $aresult->FetchRow())
                {
					if ($tokencompleted != "N") { $lastanswfortoken=$arow['id']; }
                    $rlanguage=$arow['startlanguage'];
                }
            }
        }

        if (tableExists('tokens_'.$thissurvey['sid']) && (!isset($_POST['token']) || !$_POST['token']))
        {// First Check if the survey uses tokens and if a token has been provided
            $errormsg="<strong><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("This is a closed-access survey, so you must supply a valid token.  Please contact the administrator for assistance.")."</strong>\n";
        }
        elseif (tableExists('tokens_'.$thissurvey['sid']) && $lastanswfortoken == 'UnknownToken')
        {
            $errormsg="<div class='warningheader'>".$clang->gT("Error")."</div> ".$clang->gT("The token you have provided is not valid or has already been used.")."<p>\n";
        }
        elseif (tableExists('tokens_'.$thissurvey['sid']) && $lastanswfortoken != '')
        {
            $errormsg="<strong><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("There is already a recorded answer for this token")."</strong>\n";
            if ($lastanswfortoken != 'PrivacyProtected')
            {
                $errormsg .= "<br /><br />".$clang->gT("Follow the following link to update it").":\n"
                . "<a href='$scriptname?action=dataentry&amp;subaction=edit&amp;id=$lastanswfortoken&amp;sid=$surveyid&amp;language=$rlanguage'"
                . "title='".$clang->gT("Edit this entry")."'>[id:$lastanswfortoken]</a>";
            }
            else
            {
                $errormsg .= "<br /><br />".$clang->gT("This surveys uses anonymized responses, so you can't update your response.")."\n";
            }
        }
        else
        {
            if (isset($_POST['save']) && $_POST['save'] == "on")
            {
                $saver['identifier']=returnglobal('save_identifier');
                $saver['language']=returnglobal('save_language');
                $saver['password']=returnglobal('save_password');
                $saver['passwordconfirm']=returnglobal('save_confirmpassword');
                $saver['email']=returnglobal('save_email');
                if (!returnglobal('redo'))
                {
                    $password=md5($saver['password']);
                }
                else
                {
                    $password=$saver['password'];
                }
                $errormsg="";
                if (!$saver['identifier']) {$errormsg .= $clang->gT("Error").": ".$clang->gT("You must supply a name for this saved session.");}
                if (!$saver['password']) {$errormsg .= $clang->gT("Error").": ".$clang->gT("You must supply a password for this saved session.");}
                if ($saver['password'] != $saver['passwordconfirm']) {$errormsg .= $clang->gT("Error").": ".$clang->gT("Your passwords do not match.");}
                if ($errormsg)
                {
                    $dataentryoutput .= $errormsg;
                    $dataentryoutput .= $clang->gT("Try again").":<br />
    				 <form method='post'>
					  <table class='outlinetable' cellspacing='0' align='center'>
					  <tr>
					   <td align='right'>".$clang->gT("Identifier:")."</td>
					   <td><input type='text' name='save_identifier' value='".$_POST['save_identifier']."' /></td></tr>
					  <tr><td align='right'>".$clang->gT("Password:")."</td>
					   <td><input type='password' name='save_password' value='".$_POST['save_password']."' /></td></tr>
					  <tr><td align='right'>".$clang->gT("Confirm Password:")."</td>
					   <td><input type='password' name='save_confirmpassword' value='".$_POST['save_confirmpassword']."' /></td></tr>
					  <tr><td align='right'>".$clang->gT("Email:")."</td>
					   <td><input type='text' name='save_email' value='".$_POST['save_email']."' />
					  <tr><td align='right'>".$clang->gT("Start Language:")."</td>
					   <td><input type='text' name='save_language' value='".$_POST['save_language']."' />\n";
                    foreach ($_POST as $key=>$val)
                    {
                        if (substr($key, 0, 4) != "save" && $key != "action" && $key !="sid" && $key != "datestamp" && $key !="ipaddr")
                        {
                            $dataentryoutput .= "<input type='hidden' name='$key' value='$val' />\n";
                        }
                    }
                    $dataentryoutput .= "</td></tr><tr><td></td><td><input type='submit' value='".$clang->gT("Submit")."' />
					 <input type='hidden' name='sid' value='$surveyid' />
					 <input type='hidden' name='subaction' value='".$_POST['subaction']."' />
					 <input type='hidden' name='language' value='".$_POST['language']."' />
					 <input type='hidden' name='save' value='on' /></td>";
                    if (isset($_POST['datestamp']))
                    {
                        $dataentryoutput .= "<input type='hidden' name='datestamp' value='".$_POST['datestamp']."' />\n";
                    }
                    if (isset($_POST['ipaddr']))
                    {
                        $dataentryoutput .= "<input type='hidden' name='ipaddr' value='".$_POST['ipaddr']."' />\n";
                    }
                    $dataentryoutput .= "</table></form>\n";
                }
            }
            //BUILD THE SQL TO INSERT RESPONSES
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            $fieldmap= createFieldMap($surveyid);
            $columns=array();
            $values=array();
            $_POST['startlanguage']=$baselang;
            if ($thissurvey['datestamp'] == "Y") {$_POST['startdate']=$_POST['datestamp'];}
            if (isset($_POST['closerecord']))
            {
                if ($thissurvey['datestamp'] == "Y") 
                {
                    $_POST['submitdate']=date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust);
                }
                else
                {
                    $_POST['submitdate']=date("Y-m-d H:i:s",mktime(0,0,0,1,1,1980));
                }
            }
            foreach ($fieldmap as $irow)
            {
                $fieldname = $irow['fieldname'];
                if (isset($_POST[$fieldname]))
                {
                    if ($_POST[$fieldname] == "" && ($irow['type'] == 'D' || $irow['type'] == 'N' || $irow['type'] == 'K'))
                    { // can't add '' in Date column
                      // Do nothing
                    }
                    else if ($irow['type'] == '|')
                    {
                        if (!strpos($irow['fieldname'], "_filecount"))
                        {
                            $json = $_POST[$fieldname];
                            $phparray = json_decode(stripslashes($json));
                            $filecount = 0;

                            for ($i = 0; $filecount < count($phparray); $i++)
                            {
                                if ($_FILES[$fieldname."_file_".$i]['error'] != 4)
                                {
                                    $target = dirname(getcwd())."/upload/surveys/". $thissurvey['sid'] ."/files/".sRandomChars(20);
                                    $size = 0.001 * $_FILES[$fieldname."_file_".$i]['size'];
                                    $name = rawurlencode($_FILES[$fieldname."_file_".$i]['name']);

                                    if (move_uploaded_file($_FILES[$fieldname."_file_".$i]['tmp_name'], $target))
                                    {
                                        $phparray[$filecount]->filename = basename($target);
                                        $phparray[$filecount]->name = $name;
                                        $phparray[$filecount]->size = $size;
                                        $pathinfo = pathinfo($_FILES[$fieldname."_file_".$i]['name']);
                                        $phparray[$filecount]->ext = $pathinfo['extension'];
                                        $filecount++;
                                    }
                                }
                            }

                            $columns[] .= db_quote_id($fieldname);
                            $values[] .= db_quoteall(json_encode($phparray), true);
                        }
                        else
                        {
                            $columns[] .= db_quote_id($fieldname);
                            $values[] .= db_quoteall(count($phparray), true);
                        }
                    }
                    elseif ($irow['type'] == 'D')
                    {
                        $datetimeobj = new Date_Time_Converter($_POST[$fieldname],$dateformatdetails['phpdate']);
                        $columns[] .= db_quote_id($fieldname);
                        $values[] .= db_quoteall($datetimeobj->convert("Y-m-d H:i:s"),true);
                    }
                    else
                    {
                        $columns[] .= db_quote_id($fieldname);
                        $values[] .= db_quoteall($_POST[$fieldname],true);
                    }
                }
            }
            
            $SQL = "INSERT INTO $surveytable
					(".implode(',',$columns).")
					VALUES 
					(".implode(',',$values).")";

            $iinsert = $connect->Execute($SQL) or safe_die ("Could not insert your data:<br />$SQL<br />\n" .$connect->ErrorMsg());

            if (isset($_POST['closerecord']) && isset($_POST['token']) && $_POST['token'] != '') // submittoken
            {
                // get submit date
                if (isset($_POST['closedate']))
                    { $submitdate = $_POST['closedate']; }
                else
                    { $submitdate = date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust); }
                
				// check how many uses the token has left
				$usesquery = "SELECT usesleft FROM {$dbprefix}tokens_$surveyid WHERE token=".db_quoteall($_POST['token'],true);
				$usesresult = db_execute_assoc($usesquery);
				$usesrow = $usesresult->FetchRow();
				if (isset($usesrow)) { $usesleft = $usesrow['usesleft']; }
				
                // query for updating tokens
                $utquery = "UPDATE {$dbprefix}tokens_$surveyid\n";
                if (bIsTokenCompletedDatestamped($thissurvey))
                {
					if (isset($usesleft) && $usesleft<=1)
					{
						$utquery .= "SET usesleft=usesleft-1, completed='$submitdate'\n";
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
                $utquery .= "WHERE token=".db_quoteall($_POST['token']);
                $utresult = $connect->Execute($utquery) or safe_die ("Couldn't update tokens table!<br />\n$utquery<br />\n".$connect->ErrorMsg());
                
                // save submitdate into survey table
                $srid = $connect->Insert_ID();
                $sdquery = "UPDATE {$dbprefix}survey_$surveyid SET submitdate='{$submitdate}' WHERE id={$srid}\n";
                $sdresult = $connect->Execute($sdquery) or safe_die ("Couldn't set submitdate response in survey table!<br />\n$sdquery<br />\n".$connect->ErrorMsg());
            }
            if (isset($_POST['save']) && $_POST['save'] == "on")
            {
                $srid = $connect->Insert_ID();
                //CREATE ENTRY INTO "saved_control"
                $scdata = array("sid"=>$surveyid,
				"srid"=>$srid,
				"identifier"=>$saver['identifier'],
				"access_code"=>$password,
				"email"=>$saver['email'],
				"ip"=>$_SERVER['REMOTE_ADDR'],
				"refurl"=>getenv("HTTP_REFERER"),
				'saved_thisstep' => 0,
				"status"=>"S",
				"saved_date"=>date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust));

                if ($connect->AutoExecute("{$dbprefix}saved_control", $scdata,'INSERT'))
                {
                    $scid = $connect->Insert_ID("{$dbprefix}saved_control","scid");

                    $dataentryoutput .= "<font class='successtitle'>".$clang->gT("Your survey responses have been saved successfully.  You will be sent a confirmation e-mail. Please make sure to save your password, since we will not be able to retrieve it for you.")."</font><br />\n";

                    $tkquery = "SELECT * FROM ".db_table_name("tokens_$surveyid");
                    if ($tkresult = $connect->Execute($tkquery)) //If the query fails, assume no tokens table exists
                    {
                        $tokendata = array (
                    "firstname"=> $saver['identifier'],	
                    "lastname"=> $saver['identifier'], 	
    				        "email"=>$saver['email'],
                    "token"=>sRandomChars(15),
                    "language"=>$saver['language'],
                    "sent"=>date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust), 	
                    "completed"=>"N");
                        $connect->AutoExecute(db_table_name("tokens_".$surveyid), $tokendata,'INSERT');
                        $dataentryoutput .= "<font class='successtitle'>".$clang->gT("A token entry for the saved survey has been created too.")."</font><br />\n";

                    }

                    if ($saver['email'])
                    {
                        //Send email
                        if (validate_email($saver['email']) && !returnglobal('redo'))
                        {
                            $subject=$clang->gT("Saved Survey Details");
                            $message=$clang->gT("Thank you for saving your survey in progress.  The following details can be used to return to this survey and continue where you left off.  Please keep this e-mail for your reference - we cannot retrieve the password for you.");
                            $message.="\n\n".$thissurvey['name']."\n\n";
                            $message.=$clang->gT("Name").": ".$saver['identifier']."\n";
                            $message.=$clang->gT("Password").": ".$saver['password']."\n\n";
                            $message.=$clang->gT("Reload your survey by clicking on the following link (or pasting it into your browser):").":\n";
                            $message.=$publicurl."/index.php?sid=$surveyid&loadall=reload&scid=".$scid."&lang=".urlencode($saver['language'])."&loadname=".urlencode($saver['identifier'])."&loadpass=".urlencode($saver['password']);
                            if (isset($tokendata['token'])) {$message.="&token=".$tokendata['token'];}
                            $from = $thissurvey['adminemail'];

                            if (SendEmailMessage($message, $subject, $saver['email'], $from, $sitename, false, getBounceEmail($surveyid)))
                            {
                                $emailsent="Y";
                                $dataentryoutput .= "<font class='successtitle'>".$clang->gT("An email has been sent with details about your saved survey")."</font><br />\n";
                            }
                        }
                    }

                }
                else
                {
                    safe_die("Unable to insert record into saved_control table.<br /><br />".$connect->ErrorMsg());
                }

            }
            $dataentryoutput .= "\t<div class='successheader'>".$clang->gT("Success")."</div>\n";
            $thisid=$connect->Insert_ID();
            $dataentryoutput .= "\t".$clang->gT("The entry was assigned the following record id: ")." {$thisid}<br /><br />\n";
        }

        $dataentryoutput .= $errormsg;
        $dataentryoutput .= "\t<input type='submit' value='".$clang->gT("Add Another Record")."' onclick=\"window.open('$scriptname?action=dataentry&amp;sid=$surveyid&amp;language=".$_POST['language']."', '_top')\" /><br /><br />\n";
        $dataentryoutput .= "\t<input type='submit' value='".$clang->gT("Return to survey administration")."' onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" /><br /><br />\n";
        if (isset($thisid))
        {
            $dataentryoutput .= "\t<input type='submit' value='".$clang->gT("View This Record")."' onclick=\"window.open('$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=id&amp;id=$thisid', '_top')\" /><br /><br />\n";
        }
        if (isset($_POST['save']) && $_POST['save'] == "on")
        {
            $dataentryoutput .= "\t<input type='submit' value='".$clang->gT("Browse Saved Responses")."' onclick=\"window.open('$scriptname?action=saved&amp;sid=$surveyid&subaction=all', '_top')\" /><br /><br />\n";
        }
        $dataentryoutput .= "</div>\n";

    }

    elseif (($subaction == "edit" || $subaction == "editsaved") && bHasSurveyPermission($surveyid,'responses','update'))
    {
        $dataentryoutput .= $surveyoptions;

        //FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
        $fnquery = "SELECT * FROM ".db_table_name("questions").", ".db_table_name("groups")." g, ".db_table_name("surveys")." WHERE
		".db_table_name("questions").".gid=g.gid AND 
		".db_table_name("questions").".language = '{$sDataEntryLanguage}' AND g.language = '{$sDataEntryLanguage}' AND
		".db_table_name("questions").".sid=".db_table_name("surveys").".sid AND ".db_table_name("questions").".sid='$surveyid'
        order by group_order, question_order";
        $fnresult = db_execute_assoc($fnquery);
        $fncount = $fnresult->RecordCount();
        //$dataentryoutput .= "$fnquery<br /><br />\n";
        $fnrows = array(); //Create an empty array in case FetchRow does not return any rows
        while ($fnrow = $fnresult->FetchRow())
        {
            $fnrows[] = $fnrow;
            $private=$fnrow['anonymized'];
            $datestamp=$fnrow['datestamp'];
            $ipaddr=$fnrow['ipaddr'];
        } // Get table output into array
        // Perform a case insensitive natural sort on group name then question title of a multidimensional array
        // $fnames = (Field Name in Survey Table, Short Title of Question, Question Type, Field Name, Question Code, Predetermined Answers if exist)

        $fnames['completed'] = array('fieldname'=>"completed", 'question'=>$clang->gT("Completed"), 'type'=>'completed');

        $fnames=array_merge($fnames,createFieldMap($surveyid,'full',false,false,$sDataEntryLanguage));
        $nfncount = count($fnames)-1;

        //SHOW INDIVIDUAL RECORD

        if ($subaction == "edit" && bHasSurveyPermission($surveyid,'responses','update'))
        {
            $idquery = "SELECT * FROM $surveytable WHERE id=$id";
            $idresult = db_execute_assoc($idquery) or safe_die ("Couldn't get individual record<br />$idquery<br />".$connect->ErrorMsg());
            while ($idrow = $idresult->FetchRow())
            {
                $results[]=$idrow;
            }
        }
        elseif ($subaction == "editsaved" && bHasSurveyPermission($surveyid,'responses','update'))
        {
            if (isset($_GET['public']) && $_GET['public']=="true")
            {
                $password=md5($_GET['accesscode']);
            }
            else
            {
                $password=$_GET['accesscode'];
            }
            $svquery = "SELECT * FROM ".db_table_name("saved_control")."
						WHERE sid=$surveyid
						AND identifier='".$_GET['identifier']."'
						AND access_code='".$password."'";
            $svresult=db_execute_assoc($svquery) or safe_die("Error getting save<br />$svquery<br />".$connect->ErrorMsg());
            while($svrow=$svresult->FetchRow())
            {
                $saver['email']=$svrow['email'];
                $saver['scid']=$svrow['scid'];
                $saver['ip']=$svrow['ip'];
            }
            $svquery = "SELECT * FROM ".db_table_name("saved_control")." WHERE scid=".$saver['scid'];
            $svresult=db_execute_assoc($svquery) or safe_die("Error getting saved info<br />$svquery<br />".$connect->ErrorMsg());
            while($svrow=$svresult->FetchRow())
            {
                $responses[$svrow['fieldname']]=$svrow['value'];
            } // while
            $fieldmap = createFieldMap($surveyid);
            foreach($fieldmap as $fm)
            {
                if (isset($responses[$fm['fieldname']]))
                {
                    $results1[$fm['fieldname']]=$responses[$fm['fieldname']];
                }
                else
                {
                    $results1[$fm['fieldname']]="";
                }
            }
            $results1['id']="";
            $results1['datestamp']=date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust);
            $results1['ipaddr']=$saver['ip'];
            $results[]=$results1;
        }
        //	$dataentryoutput .= "<pre>";print_r($results);$dataentryoutput .= "</pre>";

        $dataentryoutput.="<div class='header ui-widget-header'>".$clang->gT("Data entry")."</div>\n"
        ."\t<div class='header ui-widget-header'>";
        if ($subaction=='edit')
        {
            $dataentryoutput .= sprintf($clang->gT("Editing response (ID %s)"),$id);
        }
        else
        {
            $dataentryoutput .= sprintf($clang->gT("Viewing response (ID %s)"),$id);
        }
        $dataentryoutput .="</div>\n";


        $dataentryoutput .= "<form method='post' action='{$scriptname}?action=dataentry' name='editresponse' id='editresponse'>\n"
        ."<table id='responsedetail' width='99%' align='center' cellpadding='0' cellspacing='0'>\n";
        $highlight=false;
        unset($fnames['lastpage']);
        
        // unset timings
        foreach ($fnames as $fname)
        {
            if ($fname['type'] == "interview_time" || $fname['type'] == "page_time" || $fname['type'] == "answer_time")
            {
                unset($fnames[$fname['fieldname']]);
                $nfncount--;
            }
        }
        
        foreach ($results as $idrow)
        {
            //$dataentryoutput .= "<pre>"; print_r($idrow);$dataentryoutput .= "</pre>";
            //for ($i=0; $i<$nfncount+1; $i++)
            $fname=reset($fnames);
	        do
            {
                //$dataentryoutput .= "<pre>"; print_r($fname);$dataentryoutput .= "</pre>";
                if (isset($idrow[$fname['fieldname']])) $answer = $idrow[$fname['fieldname']];
                $question=$fname['question'];
                $dataentryoutput .= "\t<tr";
                if ($highlight) $dataentryoutput .=" class='odd'";
                   else $dataentryoutput .=" class='even'";
                 
                $highlight=!$highlight;
                $dataentryoutput .=">\n"
                ."<td valign='top' align='right' width='25%'>"
                ."\n";
                $dataentryoutput .= "\t<strong>".strip_javascript($question)."</strong>\n";
                $dataentryoutput .= "</td>\n"
                ."<td valign='top' align='left'>\n";
                //$dataentryoutput .= "\t-={$fname[3]}=-"; //Debugging info
                switch ($fname['type'])
                {
                    case "completed":
                        // First compute the submitdate
                        if ($private == "Y")
                        {
                            // In case of anonymized responses survey with no datestamp
                            // then the the answer submutdate gets a conventional timestamp
                            // 1st Jan 1980
                            $mysubmitdate = date("Y-m-d H:i:s",mktime(0,0,0,1,1,1980));
                        }
                        else
                        {
                            $mysubmitdate = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust);
                        }
                        $completedate= empty($idrow[$fname['fieldname']]) ? $mysubmitdate : $idrow[$fname['fieldname']];

                        $dataentryoutput .= "                <select name='completed'>\n";
                        $dataentryoutput .= "                    <option value=";
                        if(empty($idrow['submitdate'])) { $dataentryoutput .= "'' selected"; }
                        else    { $dataentryoutput .= "'N'"; }
                        $dataentryoutput .= ">".$clang->gT("No")."</option>\n";
                        $dataentryoutput .= "                    <option value=";
                        if(!empty($idrow['submitdate'])) { $dataentryoutput .= "'' selected"; }
                        else     { $dataentryoutput .= "'$completedate'"; }
                        $dataentryoutput .= ">".$clang->gT("Yes")."</option>\n";
                        $dataentryoutput .= "                </select>\n";
                        break;
                    case "X": //Boilerplate question
                        $dataentryoutput .= "";
                        break;
                    case "Q":
                    case "K":
                        $dataentryoutput .= "\t{$fname['subquestion']}&nbsp;<input type='text' name='{$fname['fieldname']}' value='"
                        .$idrow[$fname['fieldname']] . "' />\n";
                        break;
                    case "id":
                        $dataentryoutput .= "<span style='font-weight:bold;'>&nbsp;{$idrow[$fname['fieldname']]}</span>";
                        break;
                    case "5": //5 POINT CHOICE radio-buttons
                        for ($x=1; $x<=5; $x++)
                        {
                            $dataentryoutput .= "\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='$x'";
                            if ($idrow[$fname['fieldname']] == $x) {$dataentryoutput .= " checked";}
                            $dataentryoutput .= " />$x \n";
                        }
                        break;
                    case "D": //DATE
                        $thisdate='';
                        if ($idrow[$fname['fieldname']]!='')
                        {
                            $datetimeobj = new Date_Time_Converter($idrow[$fname['fieldname']] , "Y-m-d H:i:s");
                            $thisdate=$datetimeobj->convert($dateformatdetails['phpdate']);
                        }
                        $dataentryoutput .= "\t<input type='text' class='popupdate' size='12' name='{$fname['fieldname']}' value='{$thisdate}' />\n";
                        break;
                    case "G": //GENDER drop-down list
                        $dataentryoutput .= "\t<select name='{$fname['fieldname']}'>\n"
                        ."<option value=''";
                        if ($idrow[$fname['fieldname']] == "") {$dataentryoutput .= " selected='selected'";}
                        $dataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n"
                        ."<option value='F'";
                        if ($idrow[$fname['fieldname']] == "F") {$dataentryoutput .= " selected='selected'";}
                        $dataentryoutput .= ">".$clang->gT("Female")."</option>\n"
                        ."<option value='M'";
                        if ($idrow[$fname['fieldname']] == "M") {$dataentryoutput .= " selected='selected'";}
                        $dataentryoutput .= ">".$clang->gT("Male")."</option>\n"
                        ."\t</select>\n";
                        break;
                    case "L": //LIST drop-down
                    case "!": //List (Radio)
                        $qidattributes=getQuestionAttributes($fname['qid']);
                        if (isset($qidattributes['category_separator']) && trim($qidattributes['category_separator'])!='')
                        {
                            $optCategorySeparator = $qidattributes['category_separator'];
                        }
                        else
                        {
                            unset($optCategorySeparator);
                        }

                        if (substr($fname['fieldname'], -5) == "other")
                        {
                            $dataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='"
                            .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "' />\n";
                        }
                        else
                        {
                            $lquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$fname['qid']} AND language = '{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                            $lresult = db_execute_assoc($lquery);
                            $dataentryoutput .= "\t<select name='{$fname['fieldname']}'>\n"
                            ."<option value=''";
                            if ($idrow[$fname['fieldname']] == "") {$dataentryoutput .= " selected='selected'";}
                            $dataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n";

                            if (!isset($optCategorySeparator))
                            {
                                while ($llrow = $lresult->FetchRow())
                                {
                                    $dataentryoutput .= "<option value='{$llrow['code']}'";
                                    if ($idrow[$fname['fieldname']] == $llrow['code']) {$dataentryoutput .= " selected='selected'";}
                                    $dataentryoutput .= ">{$llrow['answer']}</option>\n";
                                }
                            }
                            else
                            {
                                $defaultopts = array();
                                $optgroups = array();
                                while ($llrow = $lresult->FetchRow())
                                {
                                    list ($categorytext, $answertext) = explode($optCategorySeparator,$llrow['answer']);
                                    if ($categorytext == '')
                                    {
                                        $defaultopts[] = array ( 'code' => $llrow['code'], 'answer' => $answertext, 'default_value' => $llrow['default_value']);
                                    }
                                    else
                                    {
                                        $optgroups[$categorytext][] = array ( 'code' => $llrow['code'], 'answer' => $answertext, 'default_value' => $llrow['default_value']);
                                    }
                                }

                                foreach ($optgroups as $categoryname => $optionlistarray)
                                {
                                    $dataentryoutput .= "<optgroup class=\"dropdowncategory\" label=\"".$categoryname."\">\n";
                                    foreach ($optionlistarray as $optionarray)
                                    {
                                        $dataentryoutput .= "\t<option value='{$optionarray['code']}'";
                                        if ($idrow[$fname['fieldname']] == $optionarray['code']) {$dataentryoutput .= " selected='selected'";}
                                        $dataentryoutput .= ">{$optionarray['answer']}</option>\n";
                                    }
                                    $dataentryoutput .= "</optgroup>\n";
                                }
                                foreach ($defaultopts as $optionarray)
                                {
                                    $dataentryoutput .= "<option value='{$optionarray['code']}'";
                                    if ($idrow[$fname['fieldname']] == $optionarray['code']) {$dataentryoutput .= " selected='selected'";}
                                    $dataentryoutput .= ">{$optionarray['answer']}</option>\n";
                                }

                            }

                            $oquery="SELECT other FROM ".db_table_name("questions")." WHERE qid={$fname['qid']} AND ".db_table_name("questions").".language = '{$sDataEntryLanguage}'";
                            $oresult=db_execute_assoc($oquery) or safe_die("Couldn't get other for list question<br />".$oquery."<br />".$connect->ErrorMsg());
                            while($orow = $oresult->FetchRow())
                            {
                                $fother=$orow['other'];
                            }
                            if ($fother =="Y")
                            {
                                $dataentryoutput .= "<option value='-oth-'";
                                if ($idrow[$fname['fieldname']] == "-oth-"){$dataentryoutput .= " selected='selected'";}
                                $dataentryoutput .= ">".$clang->gT("Other")."</option>\n";
                            }
                            $dataentryoutput .= "\t</select>\n";
                        }
                        break;
                    case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
                        $lquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$fname['qid']} AND language = '{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                        $lresult = db_execute_assoc($lquery);
                        $dataentryoutput .= "\t<select name='{$fname['fieldname']}'>\n"
                        ."<option value=''";
                        if ($idrow[$fname['fieldname']] == "") {$dataentryoutput .= " selected='selected'";}
                        $dataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n";

                        while ($llrow = $lresult->FetchRow())
                        {
                            $dataentryoutput .= "<option value='{$llrow['code']}'";
                            if ($idrow[$fname['fieldname']] == $llrow['code']) {$dataentryoutput .= " selected='selected'";}
                            $dataentryoutput .= ">{$llrow['answer']}</option>\n";
                        }
                        $fname=next($fnames);
                        $dataentryoutput .= "\t</select>\n"
                        ."\t<br />\n"
                        ."\t<textarea cols='45' rows='5' name='{$fname['fieldname']}'>"
                        .htmlspecialchars($idrow[$fname['fieldname']]) . "</textarea>\n";
                        break;
                    case "R": //RANKING TYPE QUESTION
                        $thisqid=$fname['qid'];
                        $currentvalues=array();
                        $myfname=$fname['sid'].'X'.$fname['gid'].'X'.$fname['qid'];
                        while (isset($fname['type']) && $fname['type'] == "R" && $fname['qid']==$thisqid)
                        {
                            //Let's get all the existing values into an array
                            if ($idrow[$fname['fieldname']])
                            {
                                $currentvalues[] = $idrow[$fname['fieldname']];
                            }
                            $fname=next($fnames);
                        }
                        $ansquery = "SELECT * FROM ".db_table_name("answers")." WHERE language = '{$sDataEntryLanguage}' AND qid=$thisqid ORDER BY sortorder, answer";
                        $ansresult = db_execute_assoc($ansquery);
                        $anscount = $ansresult->RecordCount();
                        $dataentryoutput .= "\t<script type='text/javascript'>\n"
                        ."\t<!--\n"
                        ."function rankthis_$thisqid(\$code, \$value)\n"
                        ."\t{\n"
                        ."\t\$index=document.editresponse.CHOICES_$thisqid.selectedIndex;\n"
                        ."\tfor (i=1; i<=$anscount; i++)\n"
                        ."{\n"
                        ."\$b=i;\n"
                        ."\$b += '';\n"
                        ."\$inputname=\"RANK_$thisqid\"+\$b;\n"
                        ."\$hiddenname=\"d$myfname\"+\$b;\n"
                        ."\$cutname=\"cut_$thisqid\"+i;\n"
                        ."document.getElementById(\$cutname).style.display='none';\n"
                        ."if (!document.getElementById(\$inputname).value)\n"
                        ."\t{\n"
                        ."\tdocument.getElementById(\$inputname).value=\$value;\n"
                        ."\tdocument.getElementById(\$hiddenname).value=\$code;\n"
                        ."\tdocument.getElementById(\$cutname).style.display='';\n"
                        ."\tfor (var b=document.getElementById('CHOICES_$thisqid').options.length-1; b>=0; b--)\n"
                        ."{\n"
                        ."if (document.getElementById('CHOICES_$thisqid').options[b].value == \$code)\n"
                        ."\t{\n"
                        ."\tdocument.getElementById('CHOICES_$thisqid').options[b] = null;\n"
                        ."\t}\n"
                        ."}\n"
                        ."\ti=$anscount;\n"
                        ."\t}\n"
                        ."}\n"
                        ."\tif (document.getElementById('CHOICES_$thisqid').options.length == 0)\n"
                        ."{\n"
                        ."document.getElementById('CHOICES_$thisqid').disabled=true;\n"
                        ."}\n"
                        ."\tdocument.editresponse.CHOICES_$thisqid.selectedIndex=-1;\n"
                        ."\t}\n"
                        ."function deletethis_$thisqid(\$text, \$value, \$name, \$thisname)\n"
                        ."\t{\n"
                        ."\tvar qid='$thisqid';\n"
                        ."\tvar lngth=qid.length+4;\n"
                        ."\tvar cutindex=\$thisname.substring(lngth, \$thisname.length);\n"
                        ."\tcutindex=parseFloat(cutindex);\n"
                        ."\tdocument.getElementById(\$name).value='';\n"
                        ."\tdocument.getElementById(\$thisname).style.display='none';\n"
                        ."\tif (cutindex > 1)\n"
                        ."{\n"
                        ."\$cut1name=\"cut_$thisqid\"+(cutindex-1);\n"
                        ."\$cut2name=\"d$myfname\"+(cutindex);\n"
                        ."document.getElementById(\$cut1name).style.display='';\n"
                        ."document.getElementById(\$cut2name).value='';\n"
                        ."}\n"
                        ."\telse\n"
                        ."{\n"
                        ."\$cut2name=\"d$myfname\"+(cutindex);\n"
                        ."document.getElementById(\$cut2name).value='';\n"
                        ."}\n"
                        ."\tvar i=document.getElementById('CHOICES_$thisqid').options.length;\n"
                        ."\tdocument.getElementById('CHOICES_$thisqid').options[i] = new Option(\$text, \$value);\n"
                        ."\tif (document.getElementById('CHOICES_$thisqid').options.length > 0)\n"
                        ."{\n"
                        ."document.getElementById('CHOICES_$thisqid').disabled=false;\n"
                        ."}\n"
                        ."\t}\n"
                        ."\t//-->\n"
                        ."\t</script>\n";
                        while ($ansrow = $ansresult->FetchRow()) //Now we're getting the codes and answers
                        {
                            $answers[] = array($ansrow['code'], $ansrow['answer']);
                        }
                        //now find out how many existing values there are

                        $chosen[]=""; //create array
                        if (!isset($ranklist)) {$ranklist="";}

                        if (isset($currentvalues))
                        {
                            $existing = count($currentvalues);
                        }
                        else {$existing=0;}
                        for ($j=1; $j<=$anscount; $j++) //go through each ranking and check for matching answer
                        {
                            $k=$j-1;
                            if (isset($currentvalues) && isset($currentvalues[$k]) && $currentvalues[$k])
                            {
                                foreach ($answers as $ans)
                                {
                                    if ($ans[0] == $currentvalues[$k])
                                    {
                                        $thiscode=$ans[0];
                                        $thistext=$ans[1];
                                    }
                                }
                            }
                            $ranklist .= "$j:&nbsp;<input class='ranklist' id='RANK_$thisqid$j'";
                            if (isset($currentvalues) && isset($currentvalues[$k]) && $currentvalues[$k])
                            {
                                $ranklist .= " value='".$thistext."'";
                            }
                            $ranklist .= " onFocus=\"this.blur()\"  />\n"
                            . "<input type='hidden' id='d$myfname$j' name='$myfname$j' value='";
                            if (isset($currentvalues) && isset($currentvalues[$k]) && $currentvalues[$k])
                            {
                                $ranklist .= $thiscode;
                                $chosen[]=array($thiscode, $thistext);
                            }
                            $ranklist .= "' />\n"
                            . "<img src='$imageurl/cut.gif' alt='".$clang->gT("Remove this item")."' title='".$clang->gT("Remove this item")."' ";
                            if ($j != $existing)
                            {
                                $ranklist .= "style='display:none'";
                            }
                            $ranklist .= " id='cut_$thisqid$j' onclick=\"deletethis_$thisqid(document.editresponse.RANK_$thisqid$j.value, document.editresponse.d$myfname$j.value, document.editresponse.RANK_$thisqid$j.id, this.id)\" /><br />\n\n";
                        }

                        if (!isset($choicelist)) {$choicelist="";}
                        $choicelist .= "<select class='choicelist' size='$anscount' name='CHOICES' id='CHOICES_$thisqid' onclick=\"rankthis_$thisqid(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" >\n";
                        foreach ($answers as $ans)
                        {
                            if (!in_array($ans, $chosen))
                            {
                                $choicelist .= "\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
                            }
                        }
                        $choicelist .= "</select>\n";
                        $dataentryoutput .= "\t<table align='left' border='0' cellspacing='5'>\n"
                        ."<tr>\n"
                        ."\t<td align='left' valign='top' width='200'>\n"
                        ."<strong>"
                        .$clang->gT("Your Choices").":</strong><br />\n"
                        .$choicelist
                        ."\t</td>\n"
                        ."\t<td align='left'>\n"
                        ."<strong>"
                        .$clang->gT("Your Ranking").":</strong><br />\n"
                        .$ranklist
                        ."\t</td>\n"
                        ."</tr>\n"
                        ."\t</table>\n"
                        ."\t<input type='hidden' name='multi' value='$anscount' />\n"
                        ."\t<input type='hidden' name='lastfield' value='";
                        if (isset($multifields)) {$dataentryoutput .= $multifields;}
                        $dataentryoutput .= "' />\n";
                        $choicelist="";
                        $ranklist="";
                        unset($answers);
                        $fname=prev($fnames);
                        break;

                    case "M": //Multiple choice checkbox
                        $qidattributes=getQuestionAttributes($fname['qid']);
                        if (trim($qidattributes['display_columns'])!='')
                        {
                            $dcols=$qidattributes['display_columns'];
                        }
                        else
                        {
                            $dcols=0;
                        }

                        //					while ($fname[3] == "M" && $question != "" && $question == $fname['type'])
                        $thisqid=$fname['qid'];
                        while ($fname['qid'] == $thisqid)
                        {
                            $fieldn = substr($fname['fieldname'], 0, strlen($fname['fieldname']));
                            //$dataentryoutput .= substr($fname['fieldname'], strlen($fname['fieldname'])-5, 5)."<br />\n";
                            if (substr($fname['fieldname'], -5) == "other")
                            {
                                $dataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='"
                                .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "' />\n";
                            }
                            else
                            {
                                $dataentryoutput .= "\t<input type='checkbox' class='checkboxbtn' name='{$fname['fieldname']}' value='Y'";
                                if ($idrow[$fname['fieldname']] == "Y") {$dataentryoutput .= " checked";}
                                $dataentryoutput .= " />{$fname['subquestion']}<br />\n";
                            }

                            $fname=next($fnames);
                            }
                        $fname=prev($fnames);

                                break;

                    case "I": //Language Switch
                        $lquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$fname['qid']} AND language = '{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                        $lresult = db_execute_assoc($lquery);


                        $slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                        $baselang = GetBaseLanguageFromSurveyID($surveyid);
                        array_unshift($slangs,$baselang);

                        $dataentryoutput.= "<select name='{$fname['fieldname']}'>\n";
                        $dataentryoutput .= "<option value=''";
                        if ($idrow[$fname['fieldname']] == "") {$dataentryoutput .= " selected='selected'";}
                        $dataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n";

                        foreach ($slangs as $lang)
                        {
                            $dataentryoutput.="<option value='{$lang}'";
                            if ($lang == $idrow[$fname['fieldname']]) {$dataentryoutput .= " selected='selected'";}
                            $dataentryoutput.=">".getLanguageNameFromCode($lang,false)."</option>\n";
                        }
                        $dataentryoutput .= "</select>";
                        break;

                    case "P": //Multiple choice with comments checkbox + text
                        $dataentryoutput .= "<table>\n";
                        while (isset($fname) && $fname['type'] == "P")
                        {
                            $thefieldname=$fname['fieldname'];
                            if (substr($thefieldname, -7) == "comment")
                            {
                                $dataentryoutput .= "<td><input type='text' name='{$fname['fieldname']}' size='50' value='"
                                .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "' /></td>\n"
                                ."\t</tr>\n";
                            }
                            elseif (substr($fname['fieldname'], -5) == "other")
                            {
                                $dataentryoutput .= "\t<tr>\n"
                                ."<td>\n"
                                ."\t<input type='text' name='{$fname['fieldname']}' size='30' value='"
                                .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "' />\n"
                                ."</td>\n"
                                ."<td>\n";
                                $fname=next($fnames);
                                $dataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' size='50' value='"
                                .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "' />\n"
                                ."</td>\n"
                                ."\t</tr>\n";
                            }
                            else
                            {
                                $dataentryoutput .= "\t<tr>\n"
                                ."<td><input type='checkbox' class='checkboxbtn' name=\"{$fname['fieldname']}\" value='Y'";
                                if ($idrow[$fname['fieldname']] == "Y") {$dataentryoutput .= " checked";}
                                $dataentryoutput .= " />{$fname['subquestion']}</td>\n";
                            }
                            $fname=next($fnames);
                        }
                        $dataentryoutput .= "</table>\n";
                        $fname=prev($fnames);
                        break;
                    case "|": //FILE UPLOAD
                        $dataentryoutput .= "<table>\n";
                        if ($fname['aid']!=='filecount')
                        {//file metadata
                            $metadata = json_decode($idrow[$fname['fieldname']], true);
                            $qAttributes = getQuestionAttributes($fname['qid']);
                            
                            for ($i = 0; $i < $idrow[$fname['fieldname']]['max_files'], isset($metadata[$i]); $i++)
                            {
                                if ($qAttributes['show_title'])
                                    $dataentryoutput .= '<tr><td width="25%">Title    </td><td><input type="text" class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_title_'.$i   .'" name="title"    size=50 value="'.htmlspecialchars($metadata[$i]["title"])   .'" /></td></tr>';
                                if ($qAttributes['show_comment'])
                                    $dataentryoutput .= '<tr><td width="25%">Comment  </td><td><input type="text" class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_comment_'.$i .'" name="comment"  size=50 value="'.htmlspecialchars($metadata[$i]["comment"]) .'" /></td></tr>';

                                $dataentryoutput .= '<tr><td>        File name</td><td><input   class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_name_'.$i    .'" name="name" size=50 value="'.htmlspecialchars(rawurldecode($metadata[$i]["name"]))    .'" /></td></tr>'
                                                   .'<tr><td></td><td><input type="hidden" class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_size_'.$i    .'" name="size"     size=50 value="'.htmlspecialchars($metadata[$i]["size"])    .'" /></td></tr>'
                                                   .'<tr><td></td><td><input type="hidden" class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_ext_'.$i     .'" name="ext"      size=50 value="'.htmlspecialchars($metadata[$i]["ext"])     .'" /></td></tr>'
                                                   .'<tr><td></td><td><input type="hidden"  class="'.$fname['fieldname'].'" id="'.$fname['fieldname'].'_filename_'.$i    .'" name="filename" size=50 value="'.htmlspecialchars(rawurldecode($metadata[$i]["filename"]))    .'" /></td></tr>';
                            }
                            $dataentryoutput .= '<tr><td></td><td><input type="hidden" id="'.$fname['fieldname'].'" name="'.$fname['fieldname'].'" size=50 value="'.htmlspecialchars($idrow[$fname['fieldname']]).'" /></td></tr>';
                            $dataentryoutput .= '</table>';
                            $dataentryoutput .= '<script type="text/javascript">
                                                     $(function() {
                                                        $(".'.$fname['fieldname'].'").keyup(function() {
                                                            var filecount = $("#'.$fname['fieldname'].'_filecount").val();
                                                            var jsonstr = "[";
                                                            var i;
                                                            for (i = 0; i < filecount; i++)
                                                            {
                                                                if (i != 0)
                                                                    jsonstr += ",";
                                                                jsonstr += \'{"title":"\'+$("#'.$fname['fieldname'].'_title_"+i).val()+\'",\';
                                                                jsonstr += \'"comment":"\'+$("#'.$fname['fieldname'].'_comment_"+i).val()+\'",\';
                                                                jsonstr += \'"size":"\'+$("#'.$fname['fieldname'].'_size_"+i).val()+\'",\';
                                                                jsonstr += \'"ext":"\'+$("#'.$fname['fieldname'].'_ext_"+i).val()+\'",\';
                                                                jsonstr += \'"filename":"\'+$("#'.$fname['fieldname'].'_filename_"+i).val()+\'",\';
                                                                jsonstr += \'"name":"\'+encodeURIComponent($("#'.$fname['fieldname'].'_name_"+i).val())+\'"}\';
                                                            }
                                                            jsonstr += "]";
                                                            $("#'.$fname['fieldname'].'").val(jsonstr);

                                                        });
                                                     });
                                                 </script>';
                        }
                        else
                        {//file count
                            $dataentryoutput .= '<input readonly id="'.$fname['fieldname'].'" name="'.$fname['fieldname'].'" value ="'.htmlspecialchars($idrow[$fname['fieldname']]).'" /></td></table>';
                        }
                        break;
                    case "N": //NUMERICAL TEXT
                        $dataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='{$idrow[$fname['fieldname']]}' "
                        ."onkeypress=\"return goodchars(event,'0123456789.,')\" />\n";
                        break;
                    case "S": //SHORT FREE TEXT
                        $dataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='"
                        .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "' />\n";
                        break;
                    case "T": //LONG FREE TEXT
                        $dataentryoutput .= "\t<textarea rows='5' cols='45' name='{$fname['fieldname']}'>"
                        .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "</textarea>\n";
                        break;
                    case "U": //HUGE FREE TEXT
                        $dataentryoutput .= "\t<textarea rows='50' cols='70' name='{$fname['fieldname']}'>"
                        .htmlspecialchars($idrow[$fname['fieldname']], ENT_QUOTES) . "</textarea>\n";
                        break;
                    case "Y": //YES/NO radio-buttons
                        $dataentryoutput .= "\t<select name='{$fname['fieldname']}'>\n"
                        ."<option value=''";
                        if ($idrow[$fname['fieldname']] == "") {$dataentryoutput .= " selected='selected'";}
                        $dataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n"
                        ."<option value='Y'";
                        if ($idrow[$fname['fieldname']] == "Y") {$dataentryoutput .= " selected='selected'";}
                        $dataentryoutput .= ">".$clang->gT("Yes")."</option>\n"
                        ."<option value='N'";
                        if ($idrow[$fname['fieldname']] == "N") {$dataentryoutput .= " selected='selected'";}
                        $dataentryoutput .= ">".$clang->gT("No")."</option>\n"
                        ."\t</select>\n";
                        break;
                    case "A": //ARRAY (5 POINT CHOICE) radio-buttons
                        $dataentryoutput .= "<table>\n";
                        $thisqid=$fname['qid'];
                        while ($fname['qid'] == $thisqid)
                        {
                            $dataentryoutput .= "\t<tr>\n"
                            ."<td align='right'>{$fname['subquestion']}</td>\n"
                            ."<td>\n";
                            for ($j=1; $j<=5; $j++)
                            {
                                $dataentryoutput .= "\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='$j'";
                                if ($idrow[$fname['fieldname']] == $j) {$dataentryoutput .= " checked";}
                                $dataentryoutput .= " />$j&nbsp;\n";
                            }
                            $dataentryoutput .= "</td>\n"
                            ."\t</tr>\n";
                            $fname=next($fnames);
                        }
                        $dataentryoutput .= "</table>\n";
                        $fname=prev($fnames);
                        break;
                    case "B": //ARRAY (10 POINT CHOICE) radio-buttons
                        $dataentryoutput .= "<table>\n";
                        $thisqid=$fname['qid'];
                        while ($fname['qid'] == $thisqid)
                        {
                            $fieldn = substr($fname['fieldname'], 0, strlen($fname['fieldname']));
                            $dataentryoutput .= "\t<tr>\n"
                            ."<td align='right'>{$fname['subquestion']}</td>\n"
                            ."<td>\n";
                            for ($j=1; $j<=10; $j++)
                            {
                                $dataentryoutput .= "\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='$j'";
                                if ($idrow[$fname['fieldname']] == $j) {$dataentryoutput .= " checked";}
                                $dataentryoutput .= " />$j&nbsp;\n";
                            }
                            $dataentryoutput .= "</td>\n"
                            ."\t</tr>\n";
                            $fname=next($fnames);
                        }
                        $fname=prev($fnames);
                        $dataentryoutput .= "</table>\n";
                        break;
                    case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                        $dataentryoutput .= "<table>\n";
                        $thisqid=$fname['qid'];
                        while ($fname['qid'] == $thisqid)
                        {
                            $fieldn = substr($fname['fieldname'], 0, strlen($fname['fieldname']));
                            $dataentryoutput .= "\t<tr>\n"
                            ."<td align='right'>{$fname['subquestion']}</td>\n"
                            ."<td>\n"
                            ."\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='Y'";
                            if ($idrow[$fname['fieldname']] == "Y") {$dataentryoutput .= " checked";}
                            $dataentryoutput .= " />".$clang->gT("Yes")."&nbsp;\n"
                            ."\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='U'";
                            if ($idrow[$fname['fieldname']] == "U") {$dataentryoutput .= " checked";}
                            $dataentryoutput .= " />".$clang->gT("Uncertain")."&nbsp;\n"
                            ."\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='N'";
                            if ($idrow[$fname['fieldname']] == "N") {$dataentryoutput .= " checked";}
                            $dataentryoutput .= " />".$clang->gT("No")."&nbsp;\n"
                            ."</td>\n"
                            ."\t</tr>\n";
                            $fname=next($fnames);
                        }
                        $fname=prev($fnames);
                        $dataentryoutput .= "</table>\n";
                        break;
                    case "E": //ARRAY (Increase/Same/Decrease) radio-buttons
                        $dataentryoutput .= "<table>\n";
                        $thisqid=$fname['qid'];
                        while ($fname['qid'] == $thisqid)
                        {
                            $fieldn = substr($fname['fieldname'], 0, strlen($fname['fieldname']));
                            $dataentryoutput .= "\t<tr>\n"
                            ."<td align='right'>{$fname['subquestion']}</td>\n"
                            ."<td>\n"
                            ."\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='I'";
                            if ($idrow[$fname['fieldname']] == "I") {$dataentryoutput .= " checked";}
                            $dataentryoutput .= " />Increase&nbsp;\n"
                            ."\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='S'";
                            if ($idrow[$fname['fieldname']] == "I") {$dataentryoutput .= " checked";}
                            $dataentryoutput .= " />Same&nbsp;\n"
                            ."\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='D'";
                            if ($idrow[$fname['fieldname']] == "D") {$dataentryoutput .= " checked";}
                            $dataentryoutput .= " />Decrease&nbsp;\n"
                            ."</td>\n"
                            ."\t</tr>\n";
                            $fname=next($fnames);
                        }
                        $fname=prev($fnames);
                        $dataentryoutput .= "</table>\n";
                        break;
                    case "F": //ARRAY (Flexible Labels)
                    case "H":
                    case "1":
                        $dataentryoutput .= "<table>\n";
                        $thisqid=$fname['qid'];
                        while (isset($fname['qid']) && $fname['qid'] == $thisqid)
                        {
                            $fieldn = substr($fname['fieldname'], 0, strlen($fname['fieldname']));
                            $dataentryoutput .= "\t<tr>\n"
                            ."<td align='right' valign='top'>{$fname['subquestion']}";
                            if (isset($fname['scale']))
                            {
                                $dataentryoutput .= " (".$fname['scale'].')';
                            }
                            $dataentryoutput .="</td>\n";
                            $scale_id=0;
                            if (isset($fname['scale_id'])) $scale_id=$fname['scale_id'];
                            $fquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid='{$fname['qid']}' and scale_id={$scale_id} and language='$sDataEntryLanguage' order by sortorder, answer";
                            $fresult = db_execute_assoc($fquery);
                            $dataentryoutput .= "<td>\n";
                            while ($frow=$fresult->FetchRow())
                            {
                                $dataentryoutput .= "\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value='{$frow['code']}'";
                                if ($idrow[$fname['fieldname']] == $frow['code']) {$dataentryoutput .= " checked";}
                                $dataentryoutput .= " />".$frow['answer']."&nbsp;\n";
                            }
                            //Add 'No Answer'
                            $dataentryoutput .= "\t<input type='radio' class='radiobtn' name='{$fname['fieldname']}' value=''";
                            if ($idrow[$fname['fieldname']] == '') {$dataentryoutput .= " checked";}
                            $dataentryoutput .= " />".$clang->gT("No answer")."&nbsp;\n";

                            $dataentryoutput .= "</td>\n"
                            ."\t</tr>\n";
                            $fname=next($fnames);
                        }
                        $fname=prev($fnames);
                        $dataentryoutput .= "</table>\n";
                        break;
                    case ":": //ARRAY (Multi Flexi) (Numbers)
                        $qidattributes=getQuestionAttributes($fname['qid']);
                        if (trim($qidattributes['multiflexible_max'])!='' && trim($qidattributes['multiflexible_min']) ==''){
                            $maxvalue=$qidattributes['multiflexible_max'];
                            $minvalue=1;
                        }
                        if (trim($qidattributes['multiflexible_min'])!='' && trim($qidattributes['multiflexible_max']) ==''){
                            $minvalue=$qidattributes['multiflexible_min'];
                            $maxvalue=$qidattributes['multiflexible_min'] + 10;
                        }
                        if (trim($qidattributes['multiflexible_min'])=='' && trim($qidattributes['multiflexible_max']) ==''){
                            $minvalue=1;
                            $maxvalue=10;
                        }
                        if (trim($qidattributes['multiflexible_min']) !='' && trim($qidattributes['multiflexible_max']) !=''){
                            if($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']){
                                $minvalue=$qidattributes['multiflexible_min'];
                                $maxvalue=$qidattributes['multiflexible_max'];
                            }
                        }


                        if (trim($qidattributes['multiflexible_step'])!='') {
                            $stepvalue=$qidattributes['multiflexible_step'];
                        } else {
                            $stepvalue=1;
                        }
                        if ($qidattributes['multiflexible_checkbox']!=0) {
                            $minvalue=0;
                            $maxvalue=1;
                            $stepvalue=1;
                        }
                        $dataentryoutput .= "<table>\n";
                        $thisqid=$fname['qid'];
                        while (isset($fname['qid']) && $fname['qid'] == $thisqid)
                        {
                            $fieldn = substr($fname['fieldname'], 0, strlen($fname['fieldname']));
                            $dataentryoutput .= "\t<tr>\n"
                            . "<td align='right' valign='top'>{$fname['subquestion1']}:{$fname['subquestion2']}</td>\n";
                            $dataentryoutput .= "<td>\n";
                            if ($qidattributes['input_boxes']!=0) {
                                $dataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='";
                                if (!empty($idrow[$fname['fieldname']])) {$datentryoutput .= $idrow[$fname['fieldname']];}
                                $dataentryoutput .= "' size=4 />";
                            } else {
                                $dataentryoutput .= "\t<select name='{$fname['fieldname']}'>\n";
                                $dataentryoutput .= "<option value=''>...</option>\n";
                                for($ii=$minvalue;$ii<=$maxvalue;$ii+=$stepvalue)
                                {
                                    $dataentryoutput .= "<option value='$ii'";
                                    if($idrow[$fname['fieldname']] == $ii) {$dataentryoutput .= " selected";}
                                    $dataentryoutput .= ">$ii</option>\n";
                                }
                            }

                            $dataentryoutput .= "</td>\n"
                            ."\t</tr>\n";
                            $fname=next($fnames);
                        }
                        $fname=prev($fnames);
                        $dataentryoutput .= "</table>\n";
                        break;
                    case ";": //ARRAY (Multi Flexi)
                        $dataentryoutput .= "<table>\n";
                        $thisqid=$fname['qid'];
                        while (isset($fname['qid']) && $fname['qid'] == $thisqid)
                        {
                            $fieldn = substr($fname['fieldname'], 0, strlen($fname['fieldname']));
                            $dataentryoutput .= "\t<tr>\n"
                            . "<td align='right' valign='top'>{$fname['subquestion1']}:{$fname['subquestion2']}</td>\n";
                            $dataentryoutput .= "<td>\n";
                            $dataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='";
                            if(!empty($idrow[$fname['fieldname']])) {$dataentryoutput .= $idrow[$fname['fieldname']];}
                            $dataentryoutput .= "' /></td>\n"
                            ."\t</tr>\n";
                            $fname=next($fnames);
                        }
                        $fname=prev($fnames);
                        $dataentryoutput .= "</table>\n";
                        break;
                    default: //This really only applies to tokens for non-private surveys
                        $dataentryoutput .= "\t<input type='text' name='{$fname['fieldname']}' value='"
                        .$idrow[$fname['fieldname']] . "' />\n";
                        break;
                }

                $dataentryoutput .= "		</td>
							</tr>\n";
            } while ($fname=next($fnames));
            }
        $dataentryoutput .= "</table>\n"
        ."<p>\n";
        if (!bHasSurveyPermission($surveyid, 'responses','update'))
        { // if you are not survey owner or super admin you cannot modify responses
            $dataentryoutput .= "<input type='button' value='".$clang->gT("Save")."' disabled='disabled'/>\n";
        }
        elseif ($subaction == "edit" && bHasSurveyPermission($surveyid,'responses','update'))
        {
            $dataentryoutput .= "
						 <input type='submit' value='".$clang->gT("Save")."' />
						 <input type='hidden' name='id' value='$id' />
						 <input type='hidden' name='sid' value='$surveyid' />
						 <input type='hidden' name='subaction' value='update' />
						 <input type='hidden' name='language' value='".$sDataEntryLanguage."' />";
        }
        elseif ($subaction == "editsaved" && bHasSurveyPermission($surveyid,'responses','update'))
        {


            $dataentryoutput .= "<script type='text/javascript'>
				  <!--
					function saveshow(value)
						{
						if (document.getElementById(value).checked == true)
							{
							document.getElementById(\"closerecord\").checked=false;
							document.getElementById(\"closerecord\").disabled=true;
							document.getElementById(\"saveoptions\").style.display=\"\";
							}
						else
							{
							document.getElementById(\"saveoptions\").style.display=\"none\";
							document.getElementById(\"closerecord\").disabled=false;
							}
						}
				  //-->
				  </script>\n";
            $dataentryoutput .= "<table><tr><td align='left'>\n";
            $dataentryoutput .= "\t<input type='checkbox' class='checkboxbtn' name='closerecord' id='closerecord' /><label for='closerecord'>".$clang->gT("Finalize response submission")."</label></td></tr>\n";
            $dataentryoutput .="<input type='hidden' name='closedate' value='".date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)."' />\n";
            $dataentryoutput .= "\t<tr><td align='left'><input type='checkbox' class='checkboxbtn' name='save' id='save' onclick='saveshow(this.id)' /><label for='save'>".$clang->gT("Save for further completion by survey user")."</label>\n";
            $dataentryoutput .= "</td></tr></table>\n";
            $dataentryoutput .= "<div name='saveoptions' id='saveoptions' style='display: none'>\n";
            $dataentryoutput .= "<table align='center' class='outlinetable' cellspacing='0'>
				  <tr><td align='right'>".$clang->gT("Identifier:")."</td>
				  <td><input type='text' name='save_identifier'";
            if (returnglobal('identifier'))
            {
                $dataentryoutput .= " value=\"".stripslashes(stripslashes(returnglobal('identifier')))."\"";
            }
            $dataentryoutput .= " /></td></tr>
				  </table>\n"
				  ."<input type='hidden' name='save_password' value='".returnglobal('accesscode')."' />\n"
				  ."<input type='hidden' name='save_confirmpassword' value='".returnglobal('accesscode')."' />\n"
				  ."<input type='hidden' name='save_email' value='".$saver['email']."' />\n"
				  ."<input type='hidden' name='save_scid' value='".$saver['scid']."' />\n"
				  ."<input type='hidden' name='redo' value='yes' />\n";
				  $dataentryoutput .= "</td>\n";
				  $dataentryoutput .= "\t</tr>"
				  ."</div>\n";
				  $dataentryoutput .= "	<tr>
					<td align='center'>
					 <input type='submit' value='".$clang->gT("Submit")."' />
					 <input type='hidden' name='sid' value='$surveyid' />
					 <input type='hidden' name='subaction' value='insert' />
					 <input type='hidden' name='language' value='".$sDataEntryLanguage."' />
					</td>
				</tr>\n";
        }

        $dataentryoutput .= "</form>\n";
    }


    elseif ($subaction == "update"  && bHasSurveyPermission($surveyid,'responses','update'))
    {
        if ($_SESSION['USER_RIGHT_SUPERADMIN'] != 1  && $surveyinfo['owner_id'] != $_SESSION['loginID'])
        {
            safe_die('You are not allowed to update an existing response.');
        }

        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $dataentryoutput .= $surveyoptions."<div class='header ui-widget-header'>".$clang->gT("Data entry")."</div>\n";

        $fieldmap= createFieldMap($surveyid);

        // unset timings
        foreach ($fieldmap as $fname)
        {
            if ($fname['type'] == "interview_time" || $fname['type'] == "page_time" || $fname['type'] == "answer_time")
            {
                unset($fieldmap[$fname['fieldname']]);
            }
        }
        
        $updateqr = "UPDATE $surveytable SET \n";

        foreach ($fieldmap as $irow)
        {
            $fieldname=$irow['fieldname'];
            if (isset($_POST[$fieldname]))
            {
                $thisvalue=$_POST[$fieldname];
            }
            else
            {
                $thisvalue="";
            }
            if ($irow['type'] == 'lastpage')
            {
                $thisvalue=0;
            }
            elseif ($irow['type'] == 'D')
            {
                if ($thisvalue == "")
                {
                    $updateqr .= db_quote_id($fieldname)." = NULL, \n";
                }
                else
                {
                    $datetimeobj = new Date_Time_Converter($thisvalue,$dateformatdetails['phpdate']);
                    $updateqr .= db_quote_id($fieldname)." = '{$datetimeobj->convert("Y-m-d H:i:s")}', \n";
                }
            }
            elseif (($irow['type'] == 'N' || $irow['type'] == 'K') && $thisvalue == "")
            {
                $updateqr .= db_quote_id($fieldname)." = NULL, \n";
            }
            elseif ($irow['type'] == '|' && strpos($irow['fieldname'], '_filecount') && $thisvalue == "")
            {
                $updateqr .= db_quote_id($fieldname)." = NULL, \n";
            }
            elseif (($irow['type'] == 'submitdate'))
            {
                if (isset($_POST['completed']) && ($_POST['completed']== "N"))
                {
                    $updateqr .= db_quote_id($fieldname)." = NULL, \n";
                }
                elseif ($thisvalue=="")
                {
                    $updateqr .= db_quote_id($fieldname)." = " . db_quoteall($_POST['completed'],true) . ", \n";
                }
                else
                {
                    $updateqr .= db_quote_id($fieldname)." = " . db_quoteall($thisvalue,true) . ", \n";
                }
            }
            else
            {
                $updateqr .= db_quote_id($fieldname)." = " . db_quoteall($thisvalue,true) . ", \n";
            }
        }
        $updateqr = substr($updateqr, 0, -3);
        $updateqr .= " WHERE id=$id";

        $updateres = $connect->Execute($updateqr) or safe_die("Update failed:<br />\n" . $connect->ErrorMsg() . "<br />$updateqr");
        $thissurvey=getSurveyInfo($surveyid);
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        $dataentryoutput .= "<div class='messagebox ui-corner-all'><div class='successheader'>".$clang->gT("Success")."</div>\n"
        .$clang->gT("Record has been updated.")."<br /><br />\n"
        ."<input type='submit' value='".$clang->gT("View This Record")."' onclick=\"window.open('$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=id&amp;id=$id', '_top')\" /><br /><br />\n"
        ."<input type='submit' value='".$clang->gT("Browse Responses")."' onclick=\"window.open('$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=all', '_top')\" />\n"
        ."</div>\n";
    }

    elseif ($subaction == "delete"  && bHasSurveyPermission($surveyid,'responses','delete'))
    {
        $dataentryoutput .= "<div class='header ui-widget-header'>".$clang->gT("Data entry")."</div>\n";
        $dataentryoutput .= "<div class='messagebox ui-corner-all'>\n";

        $thissurvey=getSurveyInfo($surveyid);

        $delquery = "DELETE FROM $surveytable WHERE id=$id";

        $delresult = $connect->Execute($delquery) or safe_die ("Couldn't delete record $id<br />\n".$connect->ErrorMsg());

        $dataentryoutput .= "<div class='successheader'>".$clang->gT("Record Deleted")." (ID: $id)</div><br /><br />\n"
        ."<input type='submit' value='".$clang->gT("Browse Responses")."' onclick=\"window.open('$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=all', '_top')\" /><br /><br />\n"
        ."</div>\n";
    }
    else
    {
        $slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        array_unshift($slangs,$baselang);

        if(!isset($_GET['language']) || !in_array($_GET['language'],$slangs))
        {
            $sDataEntryLanguage = $baselang;
            $blang = $clang;
        } else {
            $blang = new limesurvey_lang($_GET['language']);
            $sDataEntryLanguage = $_GET['language'];
        }

        $langlistbox = languageDropdown($surveyid,$sDataEntryLanguage);
        $thissurvey=getSurveyInfo($surveyid);
        //This is the default, presenting a blank dataentry form
        $fieldmap=createFieldMap($surveyid);
        // PRESENT SURVEY DATAENTRY SCREEN
        $dataentryoutput .= $surveyoptions;

        $dataentryoutput .= "<div class='header ui-widget-header'>".$clang->gT("Data entry")."</div>\n";

        $dataentryoutput .= "<form action='$scriptname?action=dataentry' enctype='multipart/form-data' name='addsurvey' method='post' id='addsurvey'>\n"
        ."<table class='data-entry-tbl' cellspacing='0'>\n"
        ."\t<tr>\n"
        ."\t<td colspan='3' align='center'>\n"
        ."\t<strong>".$thissurvey['name']."</strong>\n"
        ."\t<br />".FlattenText($thissurvey['description'])."\n"
        ."\t</td>\n"
        ."\t</tr>\n";

        $dataentryoutput .= "\t<tr class='data-entry-separator'><td colspan='3'></td></tr>\n";

        if (count(GetAdditionalLanguagesFromSurveyID($surveyid))>0)
        {
            $dataentryoutput .= "\t<tr>\n"
            ."\t<td colspan='3' align='center'>\n"
            ."\t".$langlistbox."\n"
            ."\t</td>\n"
            ."\t</tr>\n";

            $dataentryoutput .= "\t<tr class='data-entry-separator'><td colspan='3'></td></tr>\n";
        }

        if (tableExists('tokens_'.$thissurvey['sid'])) //Give entry field for token id
        {
            $dataentryoutput .= "\t<tr>\n"
            ."<td valign='top' width='1%'></td>\n"
            ."<td valign='top' align='right' width='30%'><font color='red'>*</font><strong>".$blang->gT("Token").":</strong></td>\n"
            ."<td valign='top'  align='left' style='padding-left: 20px'>\n"
            ."\t<input type='text' id='token' name='token'";
            // if group tokens are active, the group token will activate the submit button instead
   
            $dataentryoutput .= "/>\n"
            ."</td>\n"
            ."\t</tr>\n";

            $dataentryoutput .= "\t<tr class='data-entry-separator'><td colspan='3'></td></tr>\n";

            $dataentryoutput .= "\n"
            . "\t<script type=\"text/javascript\"><!-- \n"
            . "\tfunction activateSubmit(me)\n"
            . "\t{"
            . "if (me.value != '')"
            . "{\n"
            . "\tdocument.getElementById('submitdata').disabled = false;\n"
            . "}\n"
            . "else\n"
            . "{\n"
            . "\tdocument.getElementById('submitdata').disabled = true;\n"
            . "}\n"
            . "\t}"
            . "\t//--></script>\n";
        }

    
        if ($thissurvey['datestamp'] == "Y") //Give datestampentry field
        {
            $localtimedate=date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust);
            $dataentryoutput .= "\t<tr>\n"
            ."<td valign='top' width='1%'></td>\n"
            ."<td valign='top' align='right' width='30%'><strong>"
            .$blang->gT("Datestamp").":</strong></td>\n"
            ."<td valign='top'  align='left' style='padding-left: 20px'>\n"
            ."\t<input type='text' name='datestamp' value='$localtimedate' />\n"
            ."</td>\n"
            ."\t</tr>\n";

            $dataentryoutput .= "\t<tr class='data-entry-separator'><td colspan='3'></td></tr>\n";
        }

        if ($thissurvey['ipaddr'] == "Y") //Give ipaddress field
        {
            $dataentryoutput .= "\t<tr>\n"
            ."<td valign='top' width='1%'></td>\n"
            ."<td valign='top' align='right' width='30%'><strong>"
            .$blang->gT("IP address").":</strong></td>\n"
            ."<td valign='top'  align='left' style='padding-left: 20px'>\n"
            ."\t<input type='text' name='ipaddr' value='NULL' />\n"
            ."</td>\n"
            ."\t</tr>\n";

            $dataentryoutput .= "\t<tr class='data-entry-separator'><td colspan='3'></td></tr>\n";
        }

        // SURVEY NAME AND DESCRIPTION TO GO HERE
        $degquery = "SELECT * FROM ".db_table_name("groups")." WHERE sid=$surveyid AND language='{$sDataEntryLanguage}' ORDER BY ".db_table_name("groups").".group_order";
        $degresult = db_execute_assoc($degquery);
        // GROUP NAME
        while ($degrow = $degresult->FetchRow())
        {
            $deqquery = "SELECT * FROM ".db_table_name("questions")." WHERE sid=$surveyid AND parent_qid=0 AND gid={$degrow['gid']} AND language='{$sDataEntryLanguage}'";
            $deqresult = db_execute_assoc($deqquery);
            $dataentryoutput .= "\t<tr>\n"
            ."<td colspan='3' align='center'><strong>".FlattenText($degrow['group_name'])."</strong></td>\n"
            ."\t</tr>\n";
            $gid = $degrow['gid'];

            $dataentryoutput .= "\t<tr class='data-entry-separator'><td colspan='3'></td></tr>\n";

            $deqrows = array(); //Create an empty array in case FetchRow does not return any rows
            while ($deqrow = $deqresult->FetchRow()) {$deqrows[] = $deqrow;} //Get table output into array

            // Perform a case insensitive natural sort on group name then question title of a multidimensional array
            usort($deqrows, 'GroupOrderThenQuestionOrder');

            foreach ($deqrows as $deqrow)
            {
                //GET ANY CONDITIONS THAT APPLY TO THIS QUESTION
                $explanation = ""; //reset conditions explanation
                $s=0;
                $scenarioquery="SELECT DISTINCT scenario FROM ".db_table_name("conditions")." WHERE ".db_table_name("conditions").".qid={$deqrow['qid']} ORDER BY scenario";
                $scenarioresult=db_execute_assoc($scenarioquery);
                while ($scenariorow=$scenarioresult->FetchRow())
                {
                    if ($s == 0 && $scenarioresult->RecordCount() > 1) { $explanation .= " <br />-------- <i>Scenario {$scenariorow['scenario']}</i> --------<br />";}
                    if ($s > 0) { $explanation .= " <br />-------- <i>".$clang->gT("OR")." Scenario {$scenariorow['scenario']}</i> --------<br />";}

                    $x=0;
                    $distinctquery="SELECT DISTINCT cqid, ".db_table_name("questions").".title FROM ".db_table_name("conditions").", ".db_table_name("questions")." WHERE ".db_table_name("conditions").".cqid=".db_table_name("questions").".qid AND ".db_table_name("conditions").".qid={$deqrow['qid']} AND ".db_table_name("conditions").".scenario={$scenariorow['scenario']} ORDER BY cqid";
                    $distinctresult=db_execute_assoc($distinctquery);

                    while ($distinctrow=$distinctresult->FetchRow())
                    {
                        if ($x > 0) {$explanation .= " <i>".$blang->gT("AND")."</i><br />";}
                        $conquery="SELECT cid, cqid, cfieldname, ".db_table_name("questions").".title, ".db_table_name("questions").".question, value, ".db_table_name("questions").".type, method FROM ".db_table_name("conditions").", ".db_table_name("questions")." WHERE ".db_table_name("conditions").".cqid=".db_table_name("questions").".qid AND ".db_table_name("conditions").".cqid={$distinctrow['cqid']} AND ".db_table_name("conditions").".qid={$deqrow['qid']} AND ".db_table_name("conditions").".scenario={$scenariorow['scenario']}";
                        $conresult=db_execute_assoc($conquery);
                        while ($conrow=$conresult->FetchRow())
                        {
                            if ($conrow['method']=="==") {$conrow['method']="= ";} else {$conrow['method']=$conrow['method']." ";}
                            switch($conrow['type'])
                            {
                                case "Y":
                                    switch ($conrow['value'])
                                    {
                                        case "Y": $conditions[]=$conrow['method']."'".$blang->gT("Yes")."'"; break;
                                        case "N": $conditions[]=$conrow['method']."'".$blang->gT("No")."'"; break;
                                    }
                                    break;
                                case "G":
                                    switch($conrow['value'])
                                    {
                                        case "M": $conditions[]=$conrow['method']."'".$blang->gT("Male")."'"; break;
                                        case "F": $conditions[]=$conrow['method']."'".$blang->gT("Female")."'"; break;
                                    } // switch
                                    break;
                                case "A":
                                case "B":
                                    $conditions[]=$conrow['method']."'".$conrow['value']."'";
                                    break;
                                case "C":
                                    switch($conrow['value'])
                                    {
                                        case "Y": $conditions[]=$conrow['method']."'".$blang->gT("Yes")."'"; break;
                                        case "U": $conditions[]=$conrow['method']."'".$blang->gT("Uncertain")."'"; break;
                                        case "N": $conditions[]=$conrow['method']."'".$blang->gT("No")."'"; break;
                                    } // switch
                                    break;
                                case "1":
                                    $value=substr($conrow['cfieldname'], strpos($conrow['cfieldname'], "X".$conrow['cqid'])+strlen("X".$conrow['cqid']), strlen($conrow['cfieldname']));
                                    $fquery = "SELECT * FROM ".db_table_name("labels")."\n"
                                    . "WHERE lid='{$conrow['lid']}'\n and language='$sDataEntryLanguage' "
                                    . "AND code='{$conrow['value']}'";
                                    $fresult=db_execute_assoc($fquery) or safe_die("$fquery<br />".$connect->ErrorMsg());
                                    while($frow=$fresult->FetchRow())
                                    {
                                        $postans=$frow['title'];
                                        $conditions[]=$conrow['method']."'".$frow['title']."'";
                                    } // while
                                    break;

                                case "E":
                                    switch($conrow['value'])
                                    {
                                        case "I": $conditions[]=$conrow['method']."'".$blang->gT("Increase")."'"; break;
                                        case "D": $conditions[]=$conrow['method']."'".$blang->gT("Decrease")."'"; break;
                                        case "S": $conditions[]=$conrow['method']."'".$blang->gT("Same")."'"; break;
                                    }
                                    break;
                                case "F":
                                case "H":
                                default:
                                    $value=substr($conrow['cfieldname'], strpos($conrow['cfieldname'], "X".$conrow['cqid'])+strlen("X".$conrow['cqid']), strlen($conrow['cfieldname']));
                                    $fquery = "SELECT * FROM ".db_table_name("questions")."\n"
                                    . "WHERE qid='{$conrow['cqid']}'\n and language='$sDataEntryLanguage' "
                                    . "AND title='{$conrow['title']}' and scale_id=0";
                                    $fresult=db_execute_assoc($fquery) or safe_die("$fquery<br />".$connect->ErrorMsg());
                                    if ($fresult->RecordCount() <= 0) die($fquery);
                                    while($frow=$fresult->FetchRow())
                                    {
                                        $postans=$frow['title'];
                                        $conditions[]=$conrow['method']."'".$frow['title']."'";
                                    } // while
                                    break;
                            } // switch
                            $answer_section="";
                            switch($conrow['type'])
                            {

                                case "1":
                                    $ansquery="SELECT answer FROM ".db_table_name("answers")." WHERE qid='{$conrow['cqid']}' AND code='{$conrow['value']}' AND language='{$baselang}'";
                                    $ansresult=db_execute_assoc($ansquery);
                                    while ($ansrow=$ansresult->FetchRow())
                                    {
                                        $conditions[]=$conrow['method']."'".$ansrow['answer']."'";
                                    }
                                    $operator=$clang->gT("OR");
                                    if (isset($conditions)) $conditions = array_unique($conditions);
                                    break;

                                case "A":
                                case "B":
                                case "C":
                                case "E":
                                case "F":
                                case "H":
                                case ":":
                                case ";":
                                    $thiscquestion=$fieldmap[$conrow['cfieldname']];
                                    $ansquery="SELECT answer FROM ".db_table_name("answers")." WHERE qid='{$conrow['cqid']}' AND code='{$thiscquestion['aid']}' AND language='{$sDataEntryLanguage}'";
                                    $ansresult=db_execute_assoc($ansquery);
                                    $i=0;
                                    while ($ansrow=$ansresult->FetchRow())
                                    {
                                        if (isset($conditions) && count($conditions) > 0)
                                        {
                                            $conditions[sizeof($conditions)-1]="(".$ansrow['answer'].") : ".end($conditions);
                                        }
                                    }
                                    $operator=$blang->gT("AND");	// this is a dirty, DIRTY fix but it works since only array questions seem to be ORd
                                    break;
                                default:
                                    $ansquery="SELECT answer FROM ".db_table_name("answers")." WHERE qid='{$conrow['cqid']}' AND code='{$conrow['value']}' AND language='{$sDataEntryLanguage}'";
                                    $ansresult=db_execute_assoc($ansquery);
                                    while ($ansrow=$ansresult->FetchRow())
                                    {
                                        $conditions[]=$conrow['method']."'".$ansrow['answer']."'";
                                    }
                                    $operator=$blang->gT("OR");
                                    if (isset($conditions)) $conditions = array_unique($conditions);
                                    break;
                            }
                        }
                        if (isset($conditions) && count($conditions) > 1)
                        {
                            $conanswers = implode(" ".$operator." ", $conditions);
                            $explanation .= " -" . str_replace("{ANSWER}", $conanswers, $blang->gT("to question {QUESTION}, answer {ANSWER}"));
                        }
                        else
                        {
                            if(empty($conditions[0])) $conditions[0] = "'".$blang->gT("No Answer")."'";
                            $explanation .= " -" . str_replace("{ANSWER}", $conditions[0], $blang->gT("to question {QUESTION}, answer {ANSWER}"));
                        }
                        unset($conditions);
                        $explanation = str_replace("{QUESTION}", "'{$distinctrow['title']}$answer_section'", $explanation);
                        $x++;
                    }
                    $s++;
                }
                if ($explanation)
                {
                    if ($bgc == "even") {$bgc = "odd";} else {$bgc = "even";} //Do no alternate on explanation row
                    $explanation = "[".$blang->gT("Only answer this if the following conditions are met:")."]<br />$explanation\n";
                    $dataentryoutput .= "<tr class ='data-entry-explanation'><td class='data-entry-small-text' colspan='3' align='left'>$explanation</td></tr>\n";
                }

                //END OF GETTING CONDITIONS

                //Alternate bgcolor for different groups
                if (!isset($bgc)) {$bgc = "even";}
                if ($bgc == "even") {$bgc = "odd";}
                else {$bgc = "even";}

                $qid = $deqrow['qid'];
                $fieldname = "$surveyid"."X"."$gid"."X"."$qid";

                $dataentryoutput .= "\t<tr class='$bgc'>\n"
                ."<td class='data-entry-small-text' valign='top' width='1%'>{$deqrow['title']}</td>\n"
                ."<td valign='top' align='right' width='30%'>";
                if ($deqrow['mandatory']=="Y") //question is mandatory
                {
                    $dataentryoutput .= "<font color='red'>*</font>";
                }
                $dataentryoutput .= "<strong>".FlattenText($deqrow['question'])."</strong></td>\n"
                ."<td valign='top'  align='left' style='padding-left: 20px'>\n";
                //DIFFERENT TYPES OF DATA FIELD HERE
                if ($deqrow['help'])
                {
                    $hh = addcslashes($deqrow['help'], "\0..\37'\""); //Escape ASCII decimal 0-32 plus single and double quotes to make JavaScript happy.
                    $hh = htmlspecialchars($hh, ENT_QUOTES); //Change & " ' < > to HTML entities to make HTML happy.
                    $dataentryoutput .= "\t<img src='$imageurl/help.gif' alt='".$blang->gT("Help about this question")."' align='right' onclick=\"javascript:alert('Question {$deqrow['title']} Help: $hh')\" />\n";
                }
                switch($deqrow['type'])
                {
                    case "5": //5 POINT CHOICE radio-buttons
                        $dataentryoutput .= "\t<select name='$fieldname'>\n"
                        ."<option value=''>".$blang->gT("No answer")."</option>\n";
                        for ($x=1; $x<=5; $x++)
                        {
                            $dataentryoutput .= "<option value='$x'>$x</option>\n";
                        }
                        $dataentryoutput .= "\t</select>\n";
                        break;
                    case "D": //DATE
                        $datetimeobj = new Date_Time_Converter('', "Y-m-d H:i:s");
                        $thisdate=$datetimeobj->convert($dateformatdetails['phpdate']);
                        $dataentryoutput .= "\t<input type='text' class='popupdate' size='12' name='$fieldname'/>\n";
                        break;
                    case "G": //GENDER drop-down list
                        $dataentryoutput .= "\t<select name='$fieldname'>\n"
                        ."<option selected='selected' value=''>".$blang->gT("Please choose")."..</option>\n"
                        ."<option value='F'>".$blang->gT("Female")."</option>\n"
                        ."<option value='M'>".$blang->gT("Male")."</option>\n"
                        ."\t</select>\n";
                        break;
                    case "Q": //MULTIPLE SHORT TEXT
                    case "K":
                        $deaquery = "SELECT question,title FROM ".db_table_name("questions")." WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                        $dearesult = db_execute_assoc($deaquery);
                        $dataentryoutput .= "\t<table>\n";
                        while ($dearow = $dearesult->FetchRow())
                        {
                            $dataentryoutput .= "<tr><td align='right'>"
                            .$dearow['question']
                            ."</td>\n"
                            ."\t<td><input type='text' name='$fieldname{$dearow['title']}' /></td>\n"
                            ."</tr>\n";
                        }
                        $dataentryoutput .= "\t</table>\n";
                        break;

                    case "1": // multi scale^
                        $deaquery = "SELECT * FROM ".db_table_name("questions")." WHERE parent_qid={$deqrow['qid']} AND language='{$baselang}' ORDER BY question_order";
                        $dearesult = db_execute_assoc($deaquery);
                        $dataentryoutput .='<table><tr><td></td><th>'.sprintf($clang->gT('Label %s'),'1').'</th><th>'.sprintf($clang->gT('Label %s'),'2').'</th></tr>';

                        while ($dearow = $dearesult->FetchRow())
                        {
                            // first scale
                            $delquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' and scale_id=0 ORDER BY sortorder, code";
                            $delresult = db_execute_assoc($delquery);
                            $dataentryoutput .= "<tr><td>{$dearow['question']}</td><td>";
                            $dataentryoutput .= "<select name='$fieldname{$dearow['title']}#0'>\n";
                            $dataentryoutput .= "<option selected='selected' value=''>".$clang->gT("Please choose...")."</option>\n";
                            while ($delrow = $delresult->FetchRow())
                            {
                                $dataentryoutput .= "<option value='{$delrow['code']}'";
                                $dataentryoutput .= ">{$delrow['answer']}</option>\n";
                            }
                            // second scale
                            $dataentryoutput .= "</select></td>\n";
                            $delquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' and scale_id=1 ORDER BY sortorder, code";
                            $delresult = db_execute_assoc($delquery);
                            $dataentryoutput .= "<td>";
                            $dataentryoutput .="<select name='$fieldname{$dearow['title']}#1'>\n";
                            $dataentryoutput .= "<option selected='selected' value=''>".$clang->gT("Please choose...")."</option>\n";
                            while ($delrow = $delresult->FetchRow())
                            {
                                $dataentryoutput .= "<option value='{$delrow['code']}'";
                                $dataentryoutput .= ">{$delrow['answer']}</option>\n";
                            }
                            $dataentryoutput .= "</select></td></tr>\n";
                        }
                        $oquery="SELECT other FROM ".db_table_name("questions")." WHERE qid={$deqrow['qid']} AND language='{$baselang}'";
                        $oresult=db_execute_assoc($oquery) or safe_die("Couldn't get other for list question<br />".$oquery."<br />".$connect->ErrorMsg());
                        while($orow = $oresult->FetchRow())
                        {
                            $fother=$orow['other'];
                        }
                        if ($fother == "Y")
                        {
                            $dataentryoutput .= "<option value='-oth-'>".$clang->gT("Other")."</option>\n";
                        }
                        // $dataentryoutput .= "\t</select>vvv\n";
                        if ($fother == "Y")
                        {
                            $dataentryoutput .= "\t"
                            .$clang->gT("Other").":"
                            ."<input type='text' name='{$fieldname}other' value='' />\n";
                        }
                        $dataentryoutput .= "</tr></table>";
                        break;

                    case "L": //LIST drop-down/radio-button list
                    case "!":
                        $qidattributes=getQuestionAttributes($deqrow['qid']);
                        if ($deqrow['type']=='!' && trim($qidattributes['category_separator'])!='')
                        {
                            $optCategorySeparator = $qidattributes['category_separator'];
                        }
                        else
                        {
                            unset($optCategorySeparator);
                        }
                        $defexists="";
                        $deaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                        $dearesult = db_execute_assoc($deaquery);
                        $dataentryoutput .= "\t<select name='$fieldname'>\n";
                        $datatemp='';
                        if (!isset($optCategorySeparator))
                        {
                            while ($dearow = $dearesult->FetchRow())
                            {
                                $datatemp .= "<option value='{$dearow['code']}'";
                                //if ($dearow['default_value'] == "Y") {$datatemp .= " selected='selected'"; $defexists = "Y";}
                                $datatemp .= ">{$dearow['answer']}</option>\n";
                            }
                        }
                        else
                        {
                            $defaultopts = array();
                            $optgroups = array();
                            while ($dearow = $dearesult->FetchRow())
                            {
                                list ($categorytext, $answertext) = explode($optCategorySeparator,$dearow['answer']);
                                if ($categorytext == '')
                                {
                                    $defaultopts[] = array ( 'code' => $dearow['code'], 'answer' => $answertext, 'default_value' => $dearow['default_value']);
                                }
                                else
                                {
                                    $optgroups[$categorytext][] = array ( 'code' => $dearow['code'], 'answer' => $answertext, 'default_value' => $dearow['default_value']);
                                }
                            }
                            foreach ($optgroups as $categoryname => $optionlistarray)
                            {
                                $datatemp .= "<optgroup class=\"dropdowncategory\" label=\"".$categoryname."\">\n";
                                foreach ($optionlistarray as $optionarray)
                                {
                                    $datatemp .= "\t<option value='{$optionarray['code']}'";
                                    //if ($optionarray['default_value'] == "Y") {$datatemp .= " selected='selected'"; $defexists = "Y";}
                                    $datatemp .= ">{$optionarray['answer']}</option>\n";
                                }
                                $datatemp .= "</optgroup>\n";
                            }
                            foreach ($defaultopts as $optionarray)
                            {
                                $datatemp .= "\t<option value='{$optionarray['code']}'";
                                //if ($optionarray['default_value'] == "Y") {$datatemp .= " selected='selected'"; $defexists = "Y";}
                                $datatemp .= ">{$optionarray['answer']}</option>\n";
                            }
                        }

                        if ($defexists=="") {$dataentryoutput .= "<option selected='selected' value=''>".$blang->gT("Please choose")."..</option>\n".$datatemp;}
                        else {$dataentryoutput .=$datatemp;}

                        $oquery="SELECT other FROM ".db_table_name("questions")." WHERE qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}'";
                        $oresult=db_execute_assoc($oquery) or safe_die("Couldn't get other for list question<br />".$oquery."<br />".$connect->ErrorMsg());
                        while($orow = $oresult->FetchRow())
                        {
                            $fother=$orow['other'];
                        }
                        if ($fother == "Y")
                        {
                            $dataentryoutput .= "<option value='-oth-'>".$blang->gT("Other")."</option>\n";
                        }
                        $dataentryoutput .= "\t</select>\n";
                        if ($fother == "Y")
                        {
                            $dataentryoutput .= "\t"
                            .$blang->gT("Other").":"
                            ."<input type='text' name='{$fieldname}other' value='' />\n";
                        }
                        break;
                    case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
                        $defexists="";
                        $deaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                        $dearesult = db_execute_assoc($deaquery);
                        $dataentryoutput .= "\t<select name='$fieldname'>\n";
                        $datatemp='';
                        while ($dearow = $dearesult->FetchRow())
                        {
                            $datatemp .= "<option value='{$dearow['code']}'";
                            //if ($dearow['default_value'] == "Y") {$datatemp .= " selected='selected'"; $defexists = "Y";}
                            $datatemp .= ">{$dearow['answer']}</option>\n";
                        }
                        if ($defexists=="") {$dataentryoutput .= "<option selected='selected' value=''>".$blang->gT("Please choose")."..</option>\n".$datatemp;}
                        else  {$dataentryoutput .= $datatemp;}
                        $dataentryoutput .= "\t</select>\n"
                        ."\t<br />".$blang->gT("Comment").":<br />\n"
                        ."\t<textarea cols='40' rows='5' name='$fieldname"
                        ."comment'></textarea>\n";
                        break;
                    case "R": //RANKING TYPE QUESTION
                        $thisqid=$deqrow['qid'];
                        $ansquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid=$thisqid AND language='{$sDataEntryLanguage}' ORDER BY sortorder, answer";
                        $ansresult = db_execute_assoc($ansquery);
                        $anscount = $ansresult->RecordCount();
                        $dataentryoutput .= "\t<script type='text/javascript'>\n"
                        ."\t<!--\n"
                        ."function rankthis_$thisqid(\$code, \$value)\n"
                        ."\t{\n"
                        ."\t\$index=document.addsurvey.CHOICES_$thisqid.selectedIndex;\n"
                        ."\tfor (i=1; i<=$anscount; i++)\n"
                        ."{\n"
                        ."\$b=i;\n"
                        ."\$b += '';\n"
                        ."\$inputname=\"RANK_$thisqid\"+\$b;\n"
                        ."\$hiddenname=\"d$fieldname\"+\$b;\n"
                        ."\$cutname=\"cut_$thisqid\"+i;\n"
                        ."document.getElementById(\$cutname).style.display='none';\n"
                        ."if (!document.getElementById(\$inputname).value)\n"
                        ."\t{\n"
                        ."\tdocument.getElementById(\$inputname).value=\$value;\n"
                        ."\tdocument.getElementById(\$hiddenname).value=\$code;\n"
                        ."\tdocument.getElementById(\$cutname).style.display='';\n"
                        ."\tfor (var b=document.getElementById('CHOICES_$thisqid').options.length-1; b>=0; b--)\n"
                        ."{\n"
                        ."if (document.getElementById('CHOICES_$thisqid').options[b].value == \$code)\n"
                        ."\t{\n"
                        ."\tdocument.getElementById('CHOICES_$thisqid').options[b] = null;\n"
                        ."\t}\n"
                        ."}\n"
                        ."\ti=$anscount;\n"
                        ."\t}\n"
                        ."}\n"
                        ."\tif (document.getElementById('CHOICES_$thisqid').options.length == 0)\n"
                        ."{\n"
                        ."document.getElementById('CHOICES_$thisqid').disabled=true;\n"
                        ."}\n"
                        ."\tdocument.addsurvey.CHOICES_$thisqid.selectedIndex=-1;\n"
                        ."\t}\n"
                        ."function deletethis_$thisqid(\$text, \$value, \$name, \$thisname)\n"
                        ."\t{\n"
                        ."\tvar qid='$thisqid';\n"
                        ."\tvar lngth=qid.length+4;\n"
                        ."\tvar cutindex=\$thisname.substring(lngth, \$thisname.length);\n"
                        ."\tcutindex=parseFloat(cutindex);\n"
                        ."\tdocument.getElementById(\$name).value='';\n"
                        ."\tdocument.getElementById(\$thisname).style.display='none';\n"
                        ."\tif (cutindex > 1)\n"
                        ."{\n"
                        ."\$cut1name=\"cut_$thisqid\"+(cutindex-1);\n"
                        ."\$cut2name=\"d$fieldname\"+(cutindex);\n"
                        ."document.getElementById(\$cut1name).style.display='';\n"
                        ."document.getElementById(\$cut2name).value='';\n"
                        ."}\n"
                        ."\telse\n"
                        ."{\n"
                        ."\$cut2name=\"d$fieldname\"+(cutindex);\n"
                        ."document.getElementById(\$cut2name).value='';\n"
                        ."}\n"
                        ."\tvar i=document.getElementById('CHOICES_$thisqid').options.length;\n"
                        ."\tdocument.getElementById('CHOICES_$thisqid').options[i] = new Option(\$text, \$value);\n"
                        ."\tif (document.getElementById('CHOICES_$thisqid').options.length > 0)\n"
                        ."{\n"
                        ."document.getElementById('CHOICES_$thisqid').disabled=false;\n"
                        ."}\n"
                        ."\t}\n"
                        ."\t//-->\n"
                        ."\t</script>\n";
                        while ($ansrow = $ansresult->FetchRow())
                        {
                            $answers[] = array($ansrow['code'], $ansrow['answer']);
                        }
                        for ($i=1; $i<=$anscount; $i++)
                        {
                            if (isset($fname))
                            {
                                $myfname=$fname.$i;
                            }
                            if (isset($myfname) && $_SESSION[$myfname])
                            {
                                $existing++;
                            }
                        }
                        for ($i=1; $i<=$anscount; $i++)
                        {
                            if (isset($fname))
                            {
                                $myfname = $fname.$i;
                            }
                            if (isset($myfname) && $_SESSION[$myfname])
                            {
                                foreach ($answers as $ans)
                                {
                                    if ($ans[0] == $_SESSION[$myfname])
                                    {
                                        $thiscode=$ans[0];
                                        $thistext=$ans[1];
                                    }
                                }
                            }
                            if (!isset($ranklist)) {$ranklist="";}
                            $ranklist .= "&nbsp;<font color='#000080'>$i:&nbsp;<input class='ranklist' type='text' name='RANK$i' id='RANK_$thisqid$i'";
                            if (isset($myfname) && $_SESSION[$myfname])
                            {
                                $ranklist .= " value='";
                                $ranklist .= $thistext;
                                $ranklist .= "'";
                            }
                            $ranklist .= " onFocus=\"this.blur()\"  />\n";
                            $ranklist .= "<input type='hidden' id='d$fieldname$i' name='$fieldname$i' value='";
                            $chosen[]=""; //create array
                            if (isset($myfname) && $_SESSION[$myfname])
                            {
                                $ranklist .= $thiscode;
                                $chosen[]=array($thiscode, $thistext);
                            }
                            $ranklist .= "' /></font>\n";
                            $ranklist .= "<img src='$imageurl/cut.gif' alt='".$blang->gT("Remove this item")."' title='".$blang->gT("Remove this item")."' ";
                            if (!isset($existing) || $i != $existing)
                            {
                                $ranklist .= "style='display:none'";
                            }
                            $mfn=$fieldname.$i;
                            $ranklist .= " id='cut_$thisqid$i' onclick=\"deletethis_$thisqid(document.addsurvey.RANK_$thisqid$i.value, document.addsurvey.d$fieldname$i.value, document.addsurvey.RANK_$thisqid$i.id, this.id)\" /><br />\n\n";
                        }
                        if (!isset($choicelist)) {$choicelist="";}
                        $choicelist .= "<select size='$anscount' class='choicelist' name='CHOICES' id='CHOICES_$thisqid' onclick=\"rankthis_$thisqid(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" >\n";
                        foreach ($answers as $ans)
                        {
                            if (_PHPVERSION < "4.2.0")
                            {
                                if (!array_in_array($ans, $chosen))
                                {
                                    $choicelist .= "\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
                                }
                            }
                            else
                            {
                                if (!in_array($ans, $chosen))
                                {
                                    $choicelist .= "\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
                                }
                            }
                        }
                        $choicelist .= "</select>\n";

                        $dataentryoutput .= "\t<table align='left' border='0' cellspacing='5'>\n"
                        ."<tr>\n"
                        ."\t<td align='left' valign='top' width='200'>\n"
                        ."<strong>"
                        .$blang->gT("Your Choices").":</strong><br />\n"
                        .$choicelist
                        ."\t</td>\n"
                        ."\t<td align='left'>\n"
                        ."<strong>"
                        .$blang->gT("Your Ranking").":</strong><br />\n"
                        .$ranklist
                        ."\t</td>\n"
                        ."</tr>\n"
                        ."\t</table>\n"
                        ."\t<input type='hidden' name='multi' value='$anscount' />\n"
                        ."\t<input type='hidden' name='lastfield' value='";
                        if (isset($multifields)) {$dataentryoutput .= $multifields;}
                        $dataentryoutput .= "' />\n";
                        $choicelist="";
                        $ranklist="";
                        unset($answers);
                        break;
                    case "M": //Multiple choice checkbox (Quite tricky really!)
                        $qidattributes=getQuestionAttributes($deqrow['qid']);
                        if (trim($qidattributes['display_columns'])!='')
                        {
                            $dcols=$qidattributes['display_columns'];
                        }
                        else
                        {
                            $dcols=0;
                        }
                        $meaquery = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                        $mearesult = db_execute_assoc($meaquery);
                        $meacount = $mearesult->RecordCount();
                        if ($deqrow['other'] == "Y") {$meacount++;}
                        if ($dcols > 0 && $meacount >= $dcols)
                        {
                            $width=sprintf("%0d", 100/$dcols);
                            $maxrows=ceil(100*($meacount/$dcols)/100); //Always rounds up to nearest whole number
                            $divider=" </td>\n <td valign='top' width='$width%' nowrap='nowrap'>";
                            $upto=0;
                            $dataentryoutput .= "<table class='question'><tr>\n <td valign='top' width='$width%' nowrap='nowrap'>";
                            while ($mearow = $mearesult->FetchRow())
                            {
                                if ($upto == $maxrows)
                                {
                                    $dataentryoutput .= $divider;
                                    $upto=0;
                                }
                                $dataentryoutput .= "\t<input type='checkbox' class='checkboxbtn' name='$fieldname{$mearow['title']}' id='answer$fieldname{$mearow['title']}' value='Y'";
                                //if ($mearow['default_value'] == "Y") {$dataentryoutput .= " checked";}
                                $dataentryoutput .= " /><label for='answer$fieldname{$mearow['title']}'>{$mearow['question']}</label><br />\n";
                                $upto++;
                            }
                            if ($deqrow['other'] == "Y")
                            {
                                $dataentryoutput .= "\t".$blang->gT("Other")." <input type='text' name='$fieldname";
                                $dataentryoutput .= "other' />\n";
                            }
                            $dataentryoutput .= "</td></tr></table>\n";
                            //Let's break the presentation into columns.
                        }
                        else
                        {
                            while ($mearow = $mearesult->FetchRow())
                            {
                                $dataentryoutput .= "\t<input type='checkbox' class='checkboxbtn' name='$fieldname{$mearow['code']}' id='answer$fieldname{$mearow['code']}' value='Y'";
                                if ($mearow['default_value'] == "Y") {$dataentryoutput .= " checked";}
                                $dataentryoutput .= " /><label for='$fieldname{$mearow['code']}'>{$mearow['answer']}</label><br />\n";
                            }
                            if ($deqrow['other'] == "Y")
                            {
                                $dataentryoutput .= "\t".$blang->gT("Other")." <input type='text' name='$fieldname";
                                $dataentryoutput .= "other' />\n";
                            }
                        }
                        break;
                    case "I": //Language Switch
                        $slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                        $sbaselang = GetBaseLanguageFromSurveyID($surveyid);
                        array_unshift($slangs,$sbaselang);

                        $dataentryoutput.= "<select name='{$fieldname}'>\n";
                        $dataentryoutput .= "<option value=''";
                        $dataentryoutput .= " selected='selected'";
                        $dataentryoutput .= ">".$blang->gT("Please choose")."..</option>\n";

                        foreach ($slangs as $lang)
                        {
                            $dataentryoutput.="<option value='{$lang}'";
                            //if ($lang == $idrow[$fnames[$i]['fieldname']]) {$dataentryoutput .= " selected='selected'";}
                            $dataentryoutput.=">".getLanguageNameFromCode($lang,false)."</option>\n";
                        }
                        $dataentryoutput .= "</select>";
                        break;
                    case "P": //Multiple choice with comments checkbox + text
                        $dataentryoutput .= "<table border='0'>\n";
                        $meaquery = "SELECT * FROM ".db_table_name("questions")." WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order, question";
                        $mearesult = db_execute_assoc($meaquery);
                        while ($mearow = $mearesult->FetchRow())
                        {
                            $dataentryoutput .= "\t<tr>\n";
                            $dataentryoutput .= "<td>\n";
                            $dataentryoutput .= "\t<input type='checkbox' class='checkboxbtn' name='$fieldname{$mearow['title']}' value='Y'";
                            //if ($mearow['default_value'] == "Y") {$dataentryoutput .= " checked";}
                            $dataentryoutput .= " />{$mearow['question']}\n";
                            $dataentryoutput .= "</td>\n";
                            //This is the commments field:
                            $dataentryoutput .= "<td>\n";
                            $dataentryoutput .= "\t<input type='text' name='$fieldname{$mearow['title']}comment' size='50' />\n";
                            $dataentryoutput .= "</td>\n";
                            $dataentryoutput .= "\t</tr>\n";
                        }
                        if ($deqrow['other'] == "Y")
                        {
                            $dataentryoutput .= "\t<tr>\n";
                            $dataentryoutput .= "<td  align='left'><label>".$blang->gT("Other").":</label>\n";
                            $dataentryoutput .= "\t<input type='text' name='$fieldname"."other' size='10'/>\n";
                            $dataentryoutput .= "</td>\n";
                            $dataentryoutput .= "<td align='left'>\n";
                            $dataentryoutput .= "\t<input type='text' name='$fieldname"."othercomment' size='50'/>\n";
                            $dataentryoutput .= "</td>\n";
                            $dataentryoutput .= "\t</tr>\n";
                        }
                        $dataentryoutput .= "</table>\n";
                        break;
                    case "|":
                        $qidattributes = getQuestionAttributes($deqrow['qid']);

                        // JS to update the json string
                        $dataentryoutput .= "<script type='text/javascript'>

                            function updateJSON".$fieldname."() {

                                var jsonstr = '[';
                                var i;
                                var filecount = 0;

                                for (i = 0; i < " . $qidattributes['max_num_of_files'] . "; i++)
                                {
                                    if ($('#".$fieldname."_file_'+i).val() != '')
                                    {";

                        if ($qidattributes['show_title'])
                            $dataentryoutput .= "jsonstr += '{\"title\":\"'+$('#".$fieldname."_title_'+i).val()+'\",';";
                        else
                            $dataentryoutput .= "jsonstr += '{\"title\":\"\",';";

                        
                        if ($qidattributes['show_comment'])
                            $dataentryoutput .= "jsonstr += '\"comment\":\"'+$('#".$fieldname."_comment_'+i).val()+'\",';";
                        else
                            $dataentryoutput .= "jsonstr += '\"comment\":\"\",';";

                        $dataentryoutput .= "jsonstr += '\"name\":\"'+$('#".$fieldname."_file_'+i).val()+'\"}';";

                        $dataentryoutput .= "jsonstr += ',';\n
                            filecount++;
                                    }
                                }
                                // strip trailing comma
                                if (jsonstr.charAt(jsonstr.length - 1) == ',')
                                jsonstr = jsonstr.substring(0, jsonstr.length - 1);
                                
                                jsonstr += ']';
                                $('#" . $fieldname . "').val(jsonstr);
                                $('#" . $fieldname . "_filecount').val(filecount);
                            }
                        </script>\n";

                        $dataentryoutput .= "<table border='0'>\n";


                        if ($qidattributes['show_title'] && $qidattributes['show_title'])
                            $dataentryoutput .= "<tr><th>Title</th><th>Comment</th>";
                        else if ($qidattributes['show_title'])
                            $dataentryoutput .= "<tr><th>Title</th>";
                        else if ($qidattributes['show_comment'])
                            $dataentryoutput .= "<tr><th>Comment</th>";

                        $dataentryoutput .= "<th>Select file</th></tr>\n";

                        $maxfiles = $qidattributes['max_num_of_files'];
                        for ($i = 0; $i < $maxfiles; $i++)
                        {
                            $dataentryoutput .= "<tr>\n";
                            if ($qidattributes['show_title'])
                                $dataentryoutput .= "<td align='center'><input type='text' id='".$fieldname."_title_".$i  ."' maxlength='100' onChange='updateJSON".$fieldname."()' /></td>\n";

                            if ($qidattributes['show_comment'])
                                $dataentryoutput .= "<td align='center'><input type='text' id='".$fieldname."_comment_".$i."' maxlength='100' onChange='updateJSON".$fieldname."()' /></td>\n";

                            $dataentryoutput .= "<td align='center'><input type='file' name='".$fieldname."_file_".$i."' id='".$fieldname."_file_".$i."' onChange='updateJSON".$fieldname."()' /></td>\n</tr>\n";
                        }
                        $dataentryoutput .= "<tr><td align='center'><input type='hidden' name='".$fieldname."' id='".$fieldname."' value='' /></td>\n</tr>\n";
                        $dataentryoutput .= "<tr><td align='center'><input type='hidden' name='".$fieldname."_filecount' id='".$fieldname."_filecount' value='' /></td>\n</tr>\n";
                        $dataentryoutput .= "</table>\n";
                        break;
                    case "N": //NUMERICAL TEXT
                        $dataentryoutput .= "\t<input type='text' name='$fieldname' onkeypress=\"return goodchars(event,'0123456789.,')\" />";
                        break;
                    case "S": //SHORT FREE TEXT
                        $dataentryoutput .= "\t<input type='text' name='$fieldname' />\n";
                        break;
                    case "T": //LONG FREE TEXT
                        $dataentryoutput .= "\t<textarea cols='40' rows='5' name='$fieldname'></textarea>\n";
                        break;
                    case "U": //LONG FREE TEXT
                        $dataentryoutput .= "\t<textarea cols='50' rows='70' name='$fieldname'></textarea>\n";
                        break;
                    case "Y": //YES/NO radio-buttons
                        $dataentryoutput .= "\t<select name='$fieldname'>\n";
                        $dataentryoutput .= "<option selected='selected' value=''>".$blang->gT("Please choose")."..</option>\n";
                        $dataentryoutput .= "<option value='Y'>".$blang->gT("Yes")."</option>\n";
                        $dataentryoutput .= "<option value='N'>".$blang->gT("No")."</option>\n";
                        $dataentryoutput .= "\t</select>\n";
                        break;
                    case "A": //ARRAY (5 POINT CHOICE) radio-buttons
                        $meaquery = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                        $mearesult = db_execute_assoc($meaquery);
                        $dataentryoutput .= "<table>\n";
                        while ($mearow = $mearesult->FetchRow())
                        {
                            $dataentryoutput .= "\t<tr>\n";
                            $dataentryoutput .= "<td align='right'>{$mearow['question']}</td>\n";
                            $dataentryoutput .= "<td>\n";
                            $dataentryoutput .= "\t<select name='$fieldname{$mearow['title']}'>\n";
                            $dataentryoutput .= "<option value=''>".$blang->gT("Please choose")."..</option>\n";
                            for ($i=1; $i<=5; $i++)
                            {
                                $dataentryoutput .= "<option value='$i'>$i</option>\n";
                            }
                            $dataentryoutput .= "\t</select>\n";
                            $dataentryoutput .= "</td>\n";
                            $dataentryoutput .= "\t</tr>\n";
                        }
                        $dataentryoutput .= "</table>\n";
                        break;
                    case "B": //ARRAY (10 POINT CHOICE) radio-buttons
                        $meaquery = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                        $mearesult = db_execute_assoc($meaquery);
                        $dataentryoutput .= "<table>\n";
                        while ($mearow = $mearesult->FetchRow())
                        {
                            $dataentryoutput .= "\t<tr>\n";
                            $dataentryoutput .= "<td align='right'>{$mearow['question']}</td>\n";
                            $dataentryoutput .= "<td>\n";
                            $dataentryoutput .= "\t<select name='$fieldname{$mearow['title']}'>\n";
                            $dataentryoutput .= "<option value=''>".$blang->gT("Please choose")."..</option>\n";
                            for ($i=1; $i<=10; $i++)
                            {
                                $dataentryoutput .= "<option value='$i'>$i</option>\n";
                            }
                            $dataentryoutput .= "</select>\n";
                            $dataentryoutput .= "</td>\n";
                            $dataentryoutput .= "\t</tr>\n";
                        }
                        $dataentryoutput .= "</table>\n";
                        break;
                    case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                        $meaquery = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                        $mearesult=db_execute_assoc($meaquery);
                        $dataentryoutput .= "<table>\n";
                        while ($mearow = $mearesult->FetchRow())
                        {
                            $dataentryoutput .= "\t<tr>\n";
                            $dataentryoutput .= "<td align='right'>{$mearow['question']}</td>\n";
                            $dataentryoutput .= "<td>\n";
                            $dataentryoutput .= "\t<select name='$fieldname{$mearow['title']}'>\n";
                            $dataentryoutput .= "<option value=''>".$blang->gT("Please choose")."..</option>\n";
                            $dataentryoutput .= "<option value='Y'>".$blang->gT("Yes")."</option>\n";
                            $dataentryoutput .= "<option value='U'>".$blang->gT("Uncertain")."</option>\n";
                            $dataentryoutput .= "<option value='N'>".$blang->gT("No")."</option>\n";
                            $dataentryoutput .= "\t</select>\n";
                            $dataentryoutput .= "</td>\n";
                            $dataentryoutput .= "</tr>\n";
                        }
                        $dataentryoutput .= "</table>\n";
                        break;
                    case "E": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                        $meaquery = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid={$deqrow['qid']} AND language='{$sDataEntryLanguage}' ORDER BY question_order";
                        $mearesult=db_execute_assoc($meaquery) or safe_die ("Couldn't get answers, Type \"E\"<br />$meaquery<br />".$connect->ErrorMsg());
                        $dataentryoutput .= "<table>\n";
                        while ($mearow = $mearesult->FetchRow())
                        {
                            $dataentryoutput .= "\t<tr>\n";
                            $dataentryoutput .= "<td align='right'>{$mearow['question']}</td>\n";
                            $dataentryoutput .= "<td>\n";
                            $dataentryoutput .= "\t<select name='$fieldname{$mearow['title']}'>\n";
                            $dataentryoutput .= "<option value=''>".$blang->gT("Please choose")."..</option>\n";
                            $dataentryoutput .= "<option value='I'>".$blang->gT("Increase")."</option>\n";
                            $dataentryoutput .= "<option value='S'>".$blang->gT("Same")."</option>\n";
                            $dataentryoutput .= "<option value='D'>".$blang->gT("Decrease")."</option>\n";
                            $dataentryoutput .= "\t</select>\n";
                            $dataentryoutput .= "</td>\n";
                            $dataentryoutput .= "</tr>\n";
                        }
                        $dataentryoutput .= "</table>\n";
                        break;
                    case ":": //ARRAY (Multi Flexi)
                        $qidattributes=getQuestionAttributes($deqrow['qid']);
                        if (trim($qidattributes['multiflexible_max'])!='' && trim($qidattributes['multiflexible_min']) =='') {
                            $maxvalue=$qidattributes['multiflexible_max'];
                            $minvalue=1;
                        }
                        if (trim($qidattributes['multiflexible_min'])!='' && trim($qidattributes['multiflexible_max']) =='') {
                            $minvalue=$qidattributes['multiflexible_min'];
                            $maxvalue=$qidattributes['multiflexible_min'] + 10;
                        }
                        if (trim($qidattributes['multiflexible_min'])=='' && trim($qidattributes['multiflexible_max']) =='') {
                            $minvalue=1;
                            $maxvalue=10;
                        }
                        if (trim($qidattributes['multiflexible_min']) !='' && trim($qidattributes['multiflexible_max']) !='') {
                            if($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']){
                                $minvalue=$qidattributes['multiflexible_min'];
                                $maxvalue=$qidattributes['multiflexible_max'];
                            }
                        }
                         
                        if (trim($qidattributes['multiflexible_step'])!='') {
                            $stepvalue=$qidattributes['multiflexible_step'];
                        } else {
                            $stepvalue=1;
                        }
                        if ($qidattributes['multiflexible_checkbox']!=0)
                        {
                            $minvalue=0;
                            $maxvalue=1;
                            $stepvalue=1;
                        }
                         
                        $dataentryoutput .= "<table>\n";
                        $dataentryoutput .= "  <tr><td></td>\n";
                        $labelcodes=array();
                        $lquery = "SELECT question, title FROM ".db_table_name("questions")." WHERE parent_qid={$deqrow['qid']} and scale_id=1 and language='{$sDataEntryLanguage}' ORDER BY question_order";
                        $lresult=db_execute_assoc($lquery) or die ("Couldn't get labels, Type \":\"<br />$lquery<br />".htmlspecialchars($connect->ErrorMsg()));
                        while ($data=$lresult->FetchRow())
                        {
                            $dataentryoutput .= "    <th>{$data['question']}</th>\n";
                            $labelcodes[]=$data['title'];
                        }
                         
                        $dataentryoutput .= "  </tr>\n";
                         
                        $meaquery = "SELECT question, title FROM ".db_table_name("questions")." WHERE parent_qid={$deqrow['qid']} and scale_id=0 and language='{$sDataEntryLanguage}' ORDER BY question_order";
                        $mearesult=db_execute_assoc($meaquery) or die ("Couldn't get answers, Type \":\"<br />$meaquery<br />".htmlspecialchars($connect->ErrorMsg()));
                        $i=0;
                        while ($mearow=$mearesult->FetchRow())
                        {
                            if (strpos($mearow['question'],'|'))
                            {
                                $answerleft=substr($mearow['question'],0,strpos($mearow['question'],'|'));
                                $answerright=substr($mearow['question'],strpos($mearow['question'],'|')+1);
                            }
                            else
                            {
                                $answerleft=$mearow['question'];
                                $answerright='';
                            }
                            $dataentryoutput .= "\t<tr>\n";
                            $dataentryoutput .= "<td align='right'>{$answerleft}</td>\n";
                            foreach($labelcodes as $ld)
                            {
                                $dataentryoutput .= "<td>\n";
                                if ($qidattributes['input_boxes']!=0) {
                                    $dataentryoutput .= "\t<input type='text' name='$fieldname{$mearow['title']}_$ld' size=4 />";
                                } else {
                                    $dataentryoutput .= "\t<select name='$fieldname{$mearow['title']}_$ld'>\n";
                                    $dataentryoutput .= "<option value=''>...</option>\n";
                                    for($ii=$minvalue;$ii<=$maxvalue;$ii+=$stepvalue)
                                    {
                                        $dataentryoutput .= "<option value='$ii'";
                                        $dataentryoutput .= ">$ii</option>\n";
                                    }
                                    $dataentryoutput .= "</select>";
                                }
                                $dataentryoutput .= "</td>\n";
                            }
                            $dataentryoutput .= "\t</tr>\n";
                            $i++;
                        }
                        $i--;
                        $dataentryoutput .= "</table>\n";
                        break;
                    case ";": //ARRAY (Multi Flexi)
                        $dataentryoutput .= "<table>\n";
                        $dataentryoutput .= "  <tr><td></td>\n";
                        $lquery = "SELECT * FROM ".db_table_name("questions")." WHERE scale_id=1 and parent_qid={$deqrow['qid']} and language='{$sDataEntryLanguage}' ORDER BY question_order";
                        $lresult=db_execute_assoc($lquery) or die ("Couldn't get labels, Type \":\"<br />$lquery<br />".htmlspecialchars($connect->ErrorMsg()));
                        $labelcodes=array();
                        while ($data=$lresult->FetchRow())
                        {
                            $dataentryoutput .= "    <th>{$data['question']}</th>\n";
                            $labelcodes[]=$data['title'];
                        }
                         
                        $dataentryoutput .= "  </tr>\n";
                         
                        $meaquery = "SELECT * FROM ".db_table_name("questions")." WHERE scale_id=0 and parent_qid={$deqrow['qid']} and language='{$sDataEntryLanguage}' ORDER BY question_order";
                        $mearesult=db_execute_assoc($meaquery) or die ("Couldn't get answers, Type \":\"<br />$meaquery<br />".htmlspecialchars($connect->ErrorMsg()));
                        $i=0;
                        while ($mearow=$mearesult->FetchRow())
                        {
                            if (strpos($mearow['question'],'|'))
                            {
                                $answerleft=substr($mearow['question'],0,strpos($mearow['question'],'|'));
                                $answerright=substr($mearow['question'],strpos($mearow['question'],'|')+1);
                            }
                            else
                            {
                                $answerleft=$mearow['question'];
                                $answerright='';
                            }
                            $dataentryoutput .= "\t<tr>\n";
                            $dataentryoutput .= "<td align='right'>{$answerleft}</td>\n";
                            foreach($labelcodes as $ld)
                            {
                                $dataentryoutput .= "<td>\n";
                                $dataentryoutput .= "\t<input type='text' name='$fieldname{$mearow['title']}_$ld' />";
                                $dataentryoutput .= "</td>\n";
                            }
                            $dataentryoutput .= "\t</tr>\n";
                            $i++;
                        }
                        $i--;
                        $dataentryoutput .= "</table>\n";
                        break;
                    case "F": //ARRAY (Flexible Labels)
                    case "H":
                        $meaquery = "SELECT * FROM ".db_table_name("questions")." WHERE parent_qid={$deqrow['qid']} and language='{$sDataEntryLanguage}' ORDER BY question_order";
                        $mearesult=db_execute_assoc($meaquery) or safe_die ("Couldn't get answers, Type \"E\"<br />$meaquery<br />".$connect->ErrorMsg());
                        $dataentryoutput .= "<table>\n";
                        while ($mearow = $mearesult->FetchRow())
                        {
                            if (strpos($mearow['question'],'|'))
                            {
                                $answerleft=substr($mearow['question'],0,strpos($mearow['question'],'|'));
                                $answerright=substr($mearow['question'],strpos($mearow['question'],'|')+1);
                            }
                            else
                            {
                                $answerleft=$mearow['question'];
                                $answerright='';
                            }
                            $dataentryoutput .= "\t<tr>\n";
                            $dataentryoutput .= "<td align='right'>{$answerleft}</td>\n";
                            $dataentryoutput .= "<td>\n";
                            $dataentryoutput .= "\t<select name='$fieldname{$mearow['title']}'>\n";
                            $dataentryoutput .= "<option value=''>".$blang->gT("Please choose")."..</option>\n";
                            $fquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} and language='{$sDataEntryLanguage}' ORDER BY sortorder, code";
                            $fresult = db_execute_assoc($fquery);
                            while ($frow = $fresult->FetchRow())
                            {
                                $dataentryoutput .= "<option value='{$frow['code']}'>".$frow['answer']."</option>\n";
                            }
                            $dataentryoutput .= "\t</select>\n";
                            $dataentryoutput .= "</td>\n";
                            $dataentryoutput .= "<td align='left'>{$answerright}</td>\n";
                            $dataentryoutput .= "</tr>\n";
                        }
                        $dataentryoutput .= "</table>\n";
                        break;
                }
                //$dataentryoutput .= " [$surveyid"."X"."$gid"."X"."$qid]";
                $dataentryoutput .= "</td>\n";
                $dataentryoutput .= "\t</tr>\n";
                $dataentryoutput .= "\t<tr class='data-entry-separator'><td colspan='3'></td></tr>\n";
            }
        }
        if ($thissurvey['active'] == "Y")
        {
            // Show Finalize response option
            $dataentryoutput .= "<script type='text/javascript'>
				  <!--
					function saveshow(value)
						{
						if (document.getElementById(value).checked == true)
							{
							document.getElementById(\"closerecord\").checked=false;
							document.getElementById(\"closerecord\").disabled=true;
							document.getElementById(\"saveoptions\").style.display=\"\";
							}
						else
							{
							document.getElementById(\"saveoptions\").style.display=\"none\";
							 document.getElementById(\"closerecord\").disabled=false;
							}
						}
				  //-->
				  </script>\n";
            $dataentryoutput .= "\t<tr>\n";
            $dataentryoutput .= "<td colspan='3' align='center'>\n";
            $dataentryoutput .= "<table><tr><td align='left'>\n";
            $dataentryoutput .= "\t<input type='checkbox' class='checkboxbtn' name='closerecord' id='closerecord' checked='checked'/><label for='closerecord'>".$clang->gT("Finalize response submission")."</label></td></tr>\n";
            $dataentryoutput .="<input type='hidden' name='closedate' value='".date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)."' />\n";

            if ($thissurvey['allowsave'] == "Y")
            {
                //Show Save Option
                $dataentryoutput .= "\t<tr><td align='left'><input type='checkbox' class='checkboxbtn' name='save' id='save' onclick='saveshow(this.id)' /><label for='save'>".$clang->gT("Save for further completion by survey user")."</label>\n";
                $dataentryoutput .= "</td></tr></table>\n";
                $dataentryoutput .= "<div name='saveoptions' id='saveoptions' style='display: none'>\n";
                $dataentryoutput .= "<table align='center' class='outlinetable' cellspacing='0'>
					  <tr><td align='right'>".$clang->gT("Identifier:")."</td>
					  <td><input type='text' name='save_identifier' /></td></tr>
					  <tr><td align='right'>".$clang->gT("Password:")."</td>
					  <td><input type='password' name='save_password' /></td></tr>
					  <tr><td align='right'>".$clang->gT("Confirm Password:")."</td>
					  <td><input type='password' name='save_confirmpassword' /></td></tr>
					  <tr><td align='right'>".$clang->gT("Email:")."</td>
					  <td><input type='text' name='save_email' /></td></tr>
					  <tr><td align='right'>".$clang->gT("Start Language:")."</td>
					  <td>";
                $slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                $sbaselang = GetBaseLanguageFromSurveyID($surveyid);
                array_unshift($slangs,$sbaselang);
                $dataentryoutput.= "<select name='save_language'>\n";
                foreach ($slangs as $lang)
                {
                    if ($lang == $baselang) $dataentryoutput .= "\t<option value='{$lang}' selected='selected'>".getLanguageNameFromCode($lang,false)."</option>\n";
                    else {$dataentryoutput.="\t<option value='{$lang}'>".getLanguageNameFromCode($lang,false)."</option>\n";}
                }
                $dataentryoutput .= "</select>";


                $dataentryoutput .= "</td></tr></table></div>\n";
                $dataentryoutput .= "</td>\n";
                $dataentryoutput .= "\t</tr>\n";
            }
            $dataentryoutput .= "\t<tr>\n";
            $dataentryoutput .= "<td colspan='3' align='center'>\n";
            $dataentryoutput .= "\t<input type='submit' id='submitdata' value='".$clang->gT("Submit")."'";

            if (tableExists('tokens_'.$thissurvey['sid']))
            {
                $dataentryoutput .= " disabled='disabled'/>\n";
            }
            else
            {
                $dataentryoutput .= " />\n";
            }
            $dataentryoutput .= "</td>\n";
            $dataentryoutput .= "\t</tr>\n";
        }
        elseif ($thissurvey['active'] == "N")
        {
            $dataentryoutput .= "\t<tr>\n";
            $dataentryoutput .= "<td colspan='3' align='center'>\n";
            $dataentryoutput .= "\t<font color='red'><strong>".$clang->gT("This survey is not yet active. Your response cannot be saved")."\n";
            $dataentryoutput .= "</strong></font></td>\n";
            $dataentryoutput .= "\t</tr>\n";
        }
        else
        {
            $dataentryoutput .= "</form>\n";
            $dataentryoutput .= "\t<tr>\n";
            $dataentryoutput .= "<td colspan='3' align='center'>\n";
            $dataentryoutput .= "\t<font color='red'><strong>".$clang->gT("Error")."</strong></font><br />\n";
            $dataentryoutput .= "\t".$clang->gT("The survey you selected does not exist")."<br /><br />\n";
            $dataentryoutput .= "\t<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";
            $dataentryoutput .= "</td>\n";
            $dataentryoutput .= "\t</tr>\n";
            $dataentryoutput .= "</table>";
            return;
        }
        $dataentryoutput .= "\t<tr>\n";
        $dataentryoutput .= "\t<td>\n";
        $dataentryoutput .= "\t<input type='hidden' name='subaction' value='insert' />\n";
        $dataentryoutput .= "\t<input type='hidden' name='sid' value='$surveyid' />\n";
        $dataentryoutput .= "\t<input type='hidden' name='language' value='$sDataEntryLanguage' />\n";
        $dataentryoutput .= "\t</td>\n";
        $dataentryoutput .= "\t</tr>\n";
        $dataentryoutput .= "</table>\n";
        $dataentryoutput .= "\t</form>\n";
    }
    $dataentryoutput .= "&nbsp;";
}
else
{
    $action = "browse_response";
    include("access_denied.php");
    include("admin.php");
}


function array_in_array($needle, $haystack)
{
    foreach ($haystack as $value)
    {
        if ($needle == $value)
        return true;
    }
    return false;
}

?>
