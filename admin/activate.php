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


//Ensure script is not run directly, avoid path disclosure
include_once("login_check.php");  //Login Check dies also if the script is started directly
include_once("activate_functions.php");
$postsid=returnglobal('sid');
$activateoutput='';
$qtypes=getqtypelist('','array');

if (!isset($_POST['ok']) || !$_POST['ok'])
{
    if (isset($_GET['fixnumbering']) && $_GET['fixnumbering'])
    {
        fixNumbering($_GET['fixnumbering']);
    }
    $failedgroupcheck = checkGroup($postsid);
    $failedcheck = checkQestions($postsid, $surveyid, $qtypes);

    //IF ANY OF THE CHECKS FAILED, PRESENT THIS SCREEN
    if ((isset($failedcheck) && $failedcheck) || (isset($failedgroupcheck) && $failedgroupcheck))
    {
        $activateoutput .= "<br />\n<div class='messagebox'>\n";
        $activateoutput .= "<div class='header'>".$clang->gT("Activate Survey")." ($surveyid)</div>\n";
        $activateoutput .= "<div class='warningheader'>\n".$clang->gT("Error")."<br />\n";
        $activateoutput .= $clang->gT("Survey does not pass consistency check")."</div>\n";
        $activateoutput .= "<p>\n";
        $activateoutput .= "<strong>".$clang->gT("The following problems have been found:")."</strong><br />\n";
        $activateoutput .= "<ul>\n";
        if (isset($failedcheck) && $failedcheck)
        {
            foreach ($failedcheck as $fc)
            {
                $activateoutput .= "<li> Question qid-{$fc[0]} (\"<a href='$scriptname?sid=$surveyid&amp;gid=$fc[3]&amp;qid=$fc[0]'>{$fc[1]}</a>\"){$fc[2]}</li>\n";
            }
        }
        if (isset($failedgroupcheck) && $failedgroupcheck)
        {
            foreach ($failedgroupcheck as $fg)
            {
                $activateoutput .= "\t\t\t\t<li> Group gid-{$fg[0]} (\"<a href='$scriptname?sid=$surveyid&amp;gid=$fg[0]'>{$fg[1]}</a>\"){$fg[2]}</li>\n";
            }
        }
        $activateoutput .= "</ul>\n";
        $activateoutput .= $clang->gT("The survey cannot be activated until these problems have been resolved.")."\n";
        $activateoutput .= "</div><br />&nbsp;\n";

        return;
    }

    $activateoutput .= "<br />\n<div class='messagebox'>\n";
    $activateoutput .= "<div class='header'>".$clang->gT("Activate Survey")." ($surveyid)</div>\n";
    $activateoutput .= "<div class='warningheader'>\n";
    $activateoutput .= $clang->gT("Warning")."<br />\n";
    $activateoutput .= $clang->gT("READ THIS CAREFULLY BEFORE PROCEEDING")."\n";
    $activateoutput .= "\t</div>\n";
    $activateoutput .= $clang->gT("You should only activate a survey when you are absolutely certain that your survey setup is finished and will not need changing.")."<br /><br />\n";
    $activateoutput .= $clang->gT("Once a survey is activated you can no longer:")."<ul><li>".$clang->gT("Add or delete groups")."</li><li>".$clang->gT("Add or delete questions")."</li><li>".$clang->gT("Add or delete subquestions or change their codes")."</li></ul>\n";
    $activateoutput .= $clang->gT("However you can still:")."<ul><li>".$clang->gT("Edit your questions code/title/text and advanced options")."</li><li>".$clang->gT("Edit your group names or descriptions")."</li><li>".$clang->gT("Add, remove or edit answer options")."</li><li>".$clang->gT("Change survey name or description")."</li></ul>\n";
    $activateoutput .= $clang->gT("Once data has been entered into this survey, if you want to add or remove groups or questions, you will need to deactivate this survey, which will move all data that has already been entered into a separate archived table.")."<br /><br />\n";
    $activateoutput .= "\t<input type='submit' value=\"".$clang->gT("Activate Survey")."\" onclick=\"".get2post("$scriptname?action=activate&amp;ok=Y&amp;sid={$_GET['sid']}")."\" />\n";
    $activateoutput .= "</div><br />&nbsp;\n";

}
else
{
    $createsurvey='';
    //Check for any additional fields for this survey and create necessary fields (token and datestamp)
    $pquery = "SELECT private, allowregister, datestamp, ipaddr, refurl FROM {$dbprefix}surveys WHERE sid={$postsid}";
    $presult=db_execute_assoc($pquery);
    $prow=$presult->FetchRow();
    if ($prow['allowregister'] == "Y")
    {
        $surveyallowsregistration="TRUE";
    }
    //strip trailing comma and new line feed (if any)
    $createsurvey = rtrim($createsurvey, ",\n");

    //Get list of questions for the base language
    $fieldmap=createFieldMap($surveyid);

    foreach ($fieldmap as $arow) //With each question, create the appropriate field(s)
    {
       if ($createsurvey!='') {$createsurvey .= ",\n";}
        $createsurvey .= " `{$arow['fieldname']}`";
        switch($arow['type'])
        {
            case 'startlanguage':
                $createsurvey .= " C(20) NOTNULL";
                break;
            case 'id':
                $createsurvey .= " I NOTNULL AUTO PRIMARY";
                break;
            case "startdate":
            case "datestamp":
                $createsurvey .= " T NOTNULL";
                break;
            case "submitdate":
                $createsurvey .= " T";
                break;
            case "lastpage":
                $createsurvey .= " I";
                break;
            case "N":  //NUMERICAL
                $createsurvey .= " F";
                break;
            case "S":  //SHORT TEXT
                if ($databasetype=='mysql' || $databasetype=='mysqli')	{$createsurvey .= " X";}
                else  {$createsurvey .= " C(255)";}
                break;
            case "L":  //LIST (RADIO)
            case "!":  //LIST (DROPDOWN)
            case "M":  //Multiple options
            case "P":  //Multiple options with comment
            case "O":  //DROPDOWN LIST WITH COMMENT
                if ($arow['aid'] != 'other' && $arow['aid'] != 'comment' && $arow['aid'] != 'othercomment')
                {
                    $createsurvey .= " C(5)";
                }
                else
                {
                    $createsurvey .= " X";
                }
                break;
            case "K":  // Multiple Numerical
                $createsurvey .= " F";
                break;
            case "U":  //Huge text
            case "Q":  //Multiple short text
            case "T":  //LONG TEXT
            case ";":  //Multi Flexi
            case ":":  //Multi Flexi
                $createsurvey .= " X";
                break;
            case "D":  //DATE
                $createsurvey .= " D";
                break;
            case "5":  //5 Point Choice
            case "G":  //Gender
            case "Y":  //YesNo
            case "X":  //Boilerplate
                $createsurvey .= " C(1)";
                break;
            case "I":  //Language switch
                $createsurvey .= " C(20)";
                break;
            case "|":
                $createsurveydirectory = true;
                if (strpos($arow['fieldname'], "_"))
                    $createsurvey .= " I1";
                else
                    $createsurvey .= " X";
                break;
            case "ipaddress":
                if ($prow['ipaddr'] == "Y")
                    $createsurvey .= " X";
                break;
            case "url":
                if ($prow['refurl'] == "Y")
                    $createsurvey .= " X";
                break;
            case "token":
                if ($prow['private'] == "N")
                {
                    $createsurvey .= " C(36)";
                    $surveynotprivate="TRUE";
                }
                break;
            default:
                $createsurvey .= " C(5)";
        }
    }

    // If last question is of type MCABCEFHP^QKJR let's get rid of the ending coma in createsurvey
    $createsurvey = rtrim($createsurvey, ",\n")."\n"; // Does nothing if not ending with a comma
    $tabname = "{$dbprefix}survey_{$postsid}"; # not using db_table_name as it quotes the table name (as does CreateTableSQL)

    $taboptarray = array('mysql' => 'ENGINE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci',
                         'mysqli'=> 'ENGINE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    $dict = NewDataDictionary($connect);
    $sqlarray = $dict->CreateTableSQL($tabname, $createsurvey, $taboptarray);
    $execresult=$dict->ExecuteSQLArray($sqlarray,1);
    if ($execresult==0 || $execresult==1)
    {
        $activateoutput .= "<br />\n<div class='messagebox'>\n" .
	    "<div class='header'>".$clang->gT("Activate Survey")." ($surveyid)</div>\n" .
	    "<div class='warningheader'>".$clang->gT("Survey could not be actived.")."</div>\n" .
	    "<p>" .
        $clang->gT("Database error:")."\n <font color='red'>" . $connect->ErrorMsg() . "</font>\n" .
	    "<pre>$createsurvey</pre>\n
        <a href='$scriptname?sid={$postsid}'>".$clang->gT("Main Admin Screen")."</a>\n</div>" ;
    }
    if ($execresult != 0 && $execresult !=1)
    {
        $anquery = "SELECT autonumber_start FROM {$dbprefix}surveys WHERE sid={$postsid}";
        if ($anresult=db_execute_assoc($anquery))
        {
            //if there is an autonumber_start field, start auto numbering here
            while($row=$anresult->FetchRow())
            {
                if ($row['autonumber_start'] > 0)
                {
                    $autonumberquery = "ALTER TABLE {$dbprefix}survey_{$postsid} AUTO_INCREMENT = ".$row['autonumber_start'];
                    if ($result = $connect->Execute($autonumberquery))
                    {
                        //We're happy it worked!
                    }
                    else
                    {
                        //Continue regardless - it's not the end of the world
                    }
                }
            }
        }

        $activateoutput .= "<br />\n<div class='messagebox'>\n";
        $activateoutput .= "<div class='header'>".$clang->gT("Activate Survey")." ($surveyid)</div>\n";
        $activateoutput .= "<div class='successheader'>".$clang->gT("Survey has been activated. Results table has been successfully created.")."</div><br /><br />\n";

        // create the survey directory where the uploaded files can be saved
        if ($createsurveydirectory)
            if (!(mkdir("../upload/surveys/" . $postsid . "/files", 0777, true)))
                $activateoutput .= "<div class='warningheader'>".
                    $clang->gT("The required directory for saving the uploaded files couldn't be created. Please check file premissions on the limesurvey/upload/surveys directory.") . "</div>";

        $acquery = "UPDATE {$dbprefix}surveys SET active='Y' WHERE sid=".returnglobal('sid');
        $acresult = $connect->Execute($acquery);

        // Private means data privacy, not closed access survey
        //		if (isset($surveynotprivate) && $surveynotprivate) //This survey is tracked, and therefore a tokens table MUST exist
        //		{
        //			$activateoutput .= $clang->gT("This is not an anonymous survey. A token table must also be created.")."<br /><br />\n";
        //			$activateoutput .= "<input type='submit' value='".$clang->gT("Initialise Tokens")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid={$_GET['sid']}&amp;createtable=Y', '_top')\" />\n";
        //		}
        //		elseif (isset($surveyallowsregistration) && $surveyallowsregistration == "TRUE")
        if (isset($surveyallowsregistration) && $surveyallowsregistration == "TRUE")
        {
            $activateoutput .= $clang->gT("This survey allows public registration. A token table must also be created.")."<br /><br />\n";
            $activateoutput .= "<input type='submit' value='".$clang->gT("Initialise Tokens")."' onclick=\"".get2post("$scriptname?action=tokens&amp;sid={$postsid}&amp;createtable=Y")."\" />\n";
        }
        else
        {
            $activateoutput .= $clang->gT("This survey is now active, and responses can be recorded.")."<br /><br />\n";
            $activateoutput .= "<strong>".$clang->gT("Open-access mode").":</strong> ".$clang->gT("No invitation code is needed to complete the survey.")."<br />".$clang->gT("You can switch to the closed-access mode by initialising a token table with the button below.")."<br /><br />\n";
            $activateoutput .= "<input type='submit' value='".$clang->gT("Switch to closed-access mode")."' onclick=\"".get2post("$scriptname?action=tokens&amp;sid={$postsid}&amp;createtable=Y")."\" />\n";
            $activateoutput .= "<input type='submit' value='".$clang->gT("No, thanks.")."' onclick=\"".get2post("$scriptname?sid={$postsid}")."\" />\n";
        }
        $activateoutput .= "</div><br />&nbsp;\n";
    }

}

?>
