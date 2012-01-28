<?php
/*
<<<<<<< HEAD
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
=======
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
>>>>>>> refs/heads/stable_plus


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

    // Check consistency for groups and questions
    $failedgroupcheck = checkGroup($postsid);
    $failedcheck = checkQuestions($postsid, $surveyid, $qtypes);

    //IF ANY OF THE CHECKS FAILED, PRESENT THIS SCREEN
    if ((isset($failedcheck) && $failedcheck) || (isset($failedgroupcheck) && $failedgroupcheck))
    {
        $activateoutput .= "<br />\n<div class='messagebox ui-corner-all'>\n";
        $activateoutput .= "<div class='header ui-widget-header'>".$clang->gT("Activate Survey")." ($surveyid)</div>\n";
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

    $activateoutput .= "<br />\n<div class='messagebox ui-corner-all'>\n";
    $activateoutput .= "<div class='header ui-widget-header'>".$clang->gT("Activate Survey")." ($surveyid)</div>\n";
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
<<<<<<< HEAD
    $activateoutput = activateSurvey($postsid,$surveyid);
=======
	//Create the survey responses table
	$createsurvey = "id I NOTNULL AUTO PRIMARY,\n";
	$createsurvey .= " submitdate T,\n";
	$createsurvey .= " startlanguage C(20) NOTNULL ,\n";
	//Check for any additional fields for this survey and create necessary fields (token and datestamp)
	$pquery = "SELECT private, allowregister, datestamp, ipaddr, refurl FROM {$dbprefix}surveys WHERE sid={$_GET['sid']}";
	$presult=db_execute_assoc($pquery);
	while($prow=$presult->FetchRow())
	{
		if ($prow['private'] == "N")
		{
			$createsurvey .= "  token C(36),\n";
			$surveynotprivate="TRUE";
		}
		if ($prow['allowregister'] == "Y")
		{
			$surveyallowsregistration="TRUE";
		}
		if ($prow['datestamp'] == "Y")
		{
			$createsurvey .= " datestamp T NOTNULL,\n";
		}
		if ($prow['ipaddr'] == "Y")
		{
			$createsurvey .= " ipaddr X,\n";
		}
		//Check to see if 'refurl' field is required.
		if ($prow['refurl'] == "Y")
		{
			$createsurvey .= " refurl X,\n";
		}
	}
	//Get list of questions for the base language
	$aquery = " SELECT * FROM ".db_table_name('questions').", ".db_table_name('groups')
	." WHERE ".db_table_name('questions').".gid=".db_table_name('groups').".gid "
	." AND ".db_table_name('questions').".sid={$_GET['sid']} "
	." AND ".db_table_name('groups').".language='".GetbaseLanguageFromSurveyid($_GET['sid']). "' "
	." AND ".db_table_name('questions').".language='".GetbaseLanguageFromSurveyid($_GET['sid']). "' "
	." ORDER BY ".db_table_name('groups').".group_order, title";
	$aresult = db_execute_assoc($aquery);
	while ($arow=$aresult->FetchRow()) //With each question, create the appropriate field(s)
	{
		if ( substr($createsurvey, strlen($createsurvey)-2, 2) != ",\n") {$createsurvey .= ",\n";}

		if ($arow['type'] != "M" && $arow['type'] != "A" && $arow['type'] != "B" &&
		$arow['type'] != "C" && $arow['type'] != "E" && $arow['type'] != "F" &&
		$arow['type'] != "H" && $arow['type'] != "P" && $arow['type'] != "R" &&
		$arow['type'] != "Q" && $arow['type'] != "^" && $arow['type'] != "J")
		{
			$createsurvey .= "  `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}`";
			switch($arow['type'])
			{
				case "N":  //NUMERICAL
				$createsurvey .= " C(20)";
				break;
				case "S":  //SHORT TEXT
				$createsurvey .= " C(255)";
				break;
				case "L":  //LIST (RADIO)
				case "!":  //LIST (DROPDOWN)
				case "W":
				case "Z":
					$createsurvey .= " C(5)";
					if ($arow['other'] == "Y")
					{
						$createsurvey .= ",\n`{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other` X";
					}
					break;
				case "I":  // CSV ONE
				$createsurvey .= " C(5)";
				break;
				case "O": //DROPDOWN LIST WITH COMMENT
				$createsurvey .= " C(5),\n `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}comment` X";
				break;
				case "T":  //LONG TEXT
				$createsurvey .= " X";
				break;
				case "U":  //HUGE TEXT
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
			}
		}
		elseif ($arow['type'] == "M" || $arow['type'] == "A" || $arow['type'] == "B" ||
		$arow['type'] == "C" || $arow['type'] == "E" || $arow['type'] == "F" ||
		$arow['type'] == "H" || $arow['type'] == "P" || $arow['type'] == "^")
		{
			//MULTI ENTRY
			$abquery = "SELECT a.*, q.other FROM {$dbprefix}answers as a, {$dbprefix}questions as q"
                       ." WHERE a.qid=q.qid AND sid={$_GET['sid']} AND q.qid={$arow['qid']} "
                       ." AND a.language='".GetbaseLanguageFromSurveyid($_GET['sid']). "' "
                       ." AND q.language='".GetbaseLanguageFromSurveyid($_GET['sid']). "' "
                       ." ORDER BY a.sortorder, a.answer";
			$abresult=db_execute_assoc($abquery) or die ("Couldn't get perform answers query<br />$abquery<br />".$connect->ErrorMsg());
			while ($abrow=$abresult->FetchRow())
			{
				$createsurvey .= "  `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}` C(5),\n";
				if ($abrow['other']=="Y") {$alsoother="Y";}
				if ($arow['type'] == "P")
				{
					$createsurvey .= "  `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}comment` X,\n";
				}
			}
			if ((isset($alsoother) && $alsoother=="Y") && ($arow['type']=="M" || $arow['type']=="P"))
			{
				$createsurvey .= " `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other` C(255),\n";
				if ($arow['type']=="P")
				{
					$createsurvey .= " `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}othercomment` X,\n";
				}
			}
		}
		elseif ($arow['type'] == "Q")
		{
			$abquery = "SELECT a.*, q.other FROM {$dbprefix}answers as a, {$dbprefix}questions as q WHERE a.qid=q.qid AND sid={$_GET['sid']} AND q.qid={$arow['qid']} "
                                   ." AND a.language='".GetbaseLanguageFromSurveyid($_GET['sid']). "' "
                                   ." AND q.language='".GetbaseLanguageFromSurveyid($_GET['sid']). "' "
                                   ." ORDER BY a.sortorder, a.answer";
			$abresult=db_execute_assoc($abquery) or die ("Couldn't get perform answers query<br />$abquery<br />".$connect->ErrorMsg());
			while ($abrow = $abresult->FetchRow())
			{
				$createsurvey .= "  `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}` C(255),\n";
			}
		}
/*		elseif ($arow['type'] == "J")
		{
			$abquery = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND sid={$_GET['sid']} AND {$dbprefix}questions.qid={$arow['qid']} ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
			$abresult=db_execute_assoc($abquery) or die ("Couldn't get perform answers query<br />$abquery<br />".$connect->ErrorMsg());
			while ($abrow = $abresultt->FetchRow())
			{
				$createsurvey .= "  `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}` C(5),\n";
			}
		}*/
		elseif ($arow['type'] == "R")
		{
			//MULTI ENTRY
    		$abquery = "SELECT a.*, q.other FROM {$dbprefix}answers as a, {$dbprefix}questions as q"
                       ." WHERE a.qid=q.qid AND sid={$_GET['sid']} AND q.qid={$arow['qid']} "
                       ." AND a.language='".GetbaseLanguageFromSurveyid($_GET['sid']). "' "
                       ." AND q.language='".GetbaseLanguageFromSurveyid($_GET['sid']). "' "
                       ." ORDER BY a.sortorder, a.answer";
			$abresult=$connect->Execute($abquery) or die ("Couldn't get perform answers query<br />$abquery<br />".$connect->ErrorMsg());
			$abcount=$abresult->RecordCount();
			for ($i=1; $i<=$abcount; $i++)
			{
				$createsurvey .= "  `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}$i` C(5),\n";
			}
		}

	}

	// If last question is of type MCABCEFHP^QJR let's get rid of the ending coma in createsurvey
	$createsurvey = rtrim($createsurvey, ",\n")."\n"; // Does nothing if not ending with a coma
	$tabname = "{$dbprefix}survey_{$_GET['sid']}"; # not using db_table_name as it quotes the table name (as does CreateTableSQL)

	$taboptarray = array('mysql' => 'TYPE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci');
	$dict = NewDataDictionary($connect);
	$sqlarray = $dict->CreateTableSQL($tabname, $createsurvey, $taboptarray);

	$execresult=$dict->ExecuteSQLArray($sqlarray,1);
	if ($execresult==0 || $execresult==1)
	{
	$activateoutput .= "<br />\n<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n" .
	"<tr bgcolor='#555555'><td height='4'><font size='1' face='verdana' color='white'><strong>".$clang->gT("Activate Survey")." ($surveyid)</strong></font></td></tr>\n" .
	"<tr><td>\n" .
	"<font color='red'>".$clang->gT("Survey could not be actived.")."</font><br />\n" .
	"<center><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a></center>\n" .
	"DB ".$clang->gT("Error").":<br />\n<font color='red'>" . $connect->ErrorMsg() . "</font>\n" .
	"<pre>$createsurvey</pre>\n" .
	"</td></tr></table></br>&nbsp;\n" .
	"</body>\n</html>";
	}
	if ($execresult != 0 && $execresult !=1)
	{
		$anquery = "SELECT autonumber_start FROM {$dbprefix}surveys WHERE sid={$_GET['sid']}";
		if ($anresult=db_execute_assoc($anquery))
		{
			//if there is an autonumber_start field, start auto numbering here
			while($row=$anresult->FetchRow())
			{
				if ($row['autonumber_start'] > 0)
				{
					$autonumberquery = "ALTER TABLE {$dbprefix}survey_{$_GET['sid']} AUTO_INCREMENT = ".$row['autonumber_start'];
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

		$activateoutput .= "<br />\n<table class='alertbox'>\n";
		$activateoutput .= "\t\t\t\t<tr><td height='4'><strong>".$clang->gT("Activate Survey")." ($surveyid)</td></tr>\n";
		$activateoutput .= "\t\t\t\t<tr><td align='center'><font color='green'>".$clang->gT("Survey has been activated. Results table has been successfully created.")."<br /><br />\n";

		$acquery = "UPDATE {$dbprefix}surveys SET active='Y' WHERE sid={$_GET['sid']}";
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
			$activateoutput .= "<input type='submit' value='".$clang->gT("Initialise Tokens")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid={$_GET['sid']}&amp;createtable=Y', '_top')\" />\n";
		}
		else
		{
			$activateoutput .= $clang->gT("This survey is now active, and responses can be recorded.")."<br /><br />\n";
			$activateoutput .= "<strong>".$clang->gT("Open-access mode").":</strong> ".$clang->gT("No invitation code is needed to complete the survey.")."<br />".$clang->gT("You can switch to the closed-access mode by initialising a token table with the button below.")."<br /><br />\n";
			$activateoutput .= $clang->gT("Optional").": <input type='submit' value='".$clang->gT("Switch to closed-access mode")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid={$_GET['sid']}&amp;createtable=Y', '_top')\" />\n";
		}
		$activateoutput .= "\t\t\t\t</font></font></td></tr></table><br />&nbsp;\n";
	}

>>>>>>> refs/heads/stable_plus
}

