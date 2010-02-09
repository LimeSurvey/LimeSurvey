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
//importsurvey.php should be called from cmdline_importsurvey.php or http_importsurvey.php, they set the $importingfrom variable
if (!isset($importingfrom) || isset($_REQUEST['importingfrom'])) {die("Cannot run this script directly");}

$handle = fopen($the_full_file_path, "r");
while (!feof($handle))
{
 
	$buffer = fgets($handle);
	$bigarray[] = $buffer;
}
fclose($handle);

if (isset($bigarray[0])) $bigarray[0]=removeBOM($bigarray[0]);
// Now we try to determine the dataformat of the survey file.
 
if (isset($bigarray[1]) && isset($bigarray[4])&& (substr($bigarray[1], 0, 22) == "# SURVEYOR SURVEY DUMP")&& (substr($bigarray[4], 0, 29) == "# http://www.phpsurveyor.org/"))
{
	$importversion = 100;  // version 1.0 file
}
elseif 
   (isset($bigarray[1]) && isset($bigarray[4])&& (substr($bigarray[1], 0, 22) == "# SURVEYOR SURVEY DUMP")&& (substr($bigarray[4], 0, 37) == "# http://phpsurveyor.sourceforge.net/"))
{
	$importversion = 99;  // Version 0.99 file or older - carries a different URL
}
elseif 
   (substr($bigarray[0], 0, 24) == "# LimeSurvey Survey Dump" || substr($bigarray[0], 0, 25) == "# PHPSurveyor Survey Dump")
    {  // Wow.. this seems to be a >1.0 version file - these files carry the version information to read in line two
      $importversion=substr($bigarray[1], 12, 3);
    }
else    // unknown file - show error message
  {
  	if ($importingfrom == "http")
  	{
	    $importsurvey .= "<div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
	  	$importsurvey .= $clang->gT("This file is not a LimeSurvey survey file. Import failed.")."<br /><br />\n";
		$importsurvey .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";
	  	$importsurvey .= "</div>\n";
	  	unlink($the_full_file_path);
	  	return;
	  }
	  else 
	  {
	  	echo $clang->gT("This file is not a LimeSurvey survey file. Import failed.")."\n";
	  	return;
	  }
  }


// okay.. now lets drop the first 9 lines and get to the data
// This works for all versions
for ($i=0; $i<9; $i++)
{
	unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);



//SURVEYS
if (array_search("# GROUPS TABLE\n", $bigarray))
{
	$stoppoint = array_search("# GROUPS TABLE\n", $bigarray);
}
elseif (array_search("# GROUPS TABLE\r\n", $bigarray))
{
	$stoppoint = array_search("# GROUPS TABLE\r\n", $bigarray);
}
for ($i=0; $i<=$stoppoint+1; $i++)
{
	if ($i<$stoppoint-2) {$surveyarray[] = $bigarray[$i];}
	unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);

//GROUPS
if (array_search("# QUESTIONS TABLE\n", $bigarray))
{
	$stoppoint = array_search("# QUESTIONS TABLE\n", $bigarray);
}
elseif (array_search("# QUESTIONS TABLE\r\n", $bigarray))
{
	$stoppoint = array_search("# QUESTIONS TABLE\r\n", $bigarray);
}
else
{
	$stoppoint = count($bigarray)-1;
}
for ($i=0; $i<=$stoppoint+1; $i++)
{
	if ($i<$stoppoint-2) {$grouparray[] = $bigarray[$i];}
	unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);

//QUESTIONS
if (array_search("# ANSWERS TABLE\n", $bigarray))
{
	$stoppoint = array_search("# ANSWERS TABLE\n", $bigarray);
}
elseif (array_search("# ANSWERS TABLE\r\n", $bigarray))
{
	$stoppoint = array_search("# ANSWERS TABLE\r\n", $bigarray);
}
else
{
	$stoppoint = count($bigarray)-1;
}
for ($i=0; $i<=$stoppoint+1; $i++)
{
	if ($i<$stoppoint-2)
	{
		$questionarray[] = $bigarray[$i];
	}
	unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);

//ANSWERS
if (array_search("# CONDITIONS TABLE\n", $bigarray))
{
	$stoppoint = array_search("# CONDITIONS TABLE\n", $bigarray);
}
elseif (array_search("# CONDITIONS TABLE\r\n", $bigarray))
{
	$stoppoint = array_search("# CONDITIONS TABLE\r\n", $bigarray);
}
else
{
	$stoppoint = count($bigarray)-1;
}
for ($i=0; $i<=$stoppoint+1; $i++)
{
	if ($i<$stoppoint-2)
	{
		$answerarray[] = str_replace("`default`", "`default_value`", $bigarray[$i]);
	}
	unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);

//CONDITIONS
if (array_search("# LABELSETS TABLE\n", $bigarray))
{
	$stoppoint = array_search("# LABELSETS TABLE\n", $bigarray);
}
elseif (array_search("# LABELSETS TABLE\r\n", $bigarray))
{
	$stoppoint = array_search("# LABELSETS TABLE\r\n", $bigarray);
}
else
{ //There is no labelsets information, so presumably this is a pre-0.98rc3 survey.
	$stoppoint = count($bigarray);
}
for ($i=0; $i<=$stoppoint+1; $i++)
{
	if ($i<$stoppoint-2) {$conditionsarray[] = $bigarray[$i];}
	unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);

//LABELSETS
if (array_search("# LABELS TABLE\n", $bigarray))
{
	$stoppoint = array_search("# LABELS TABLE\n", $bigarray);
}
elseif (array_search("# LABELS TABLE\r\n", $bigarray))
{
	$stoppoint = array_search("# LABELS TABLE\r\n", $bigarray);
}
else
{
	$stoppoint = count($bigarray)-1;
}
for ($i=0; $i<=$stoppoint+1; $i++)
{
	if ($i<$stoppoint-2) {$labelsetsarray[] = $bigarray[$i];}
	unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);

//LABELS
if (array_search("# QUESTION_ATTRIBUTES TABLE\n", $bigarray))
{
	$stoppoint = array_search("# QUESTION_ATTRIBUTES TABLE\n", $bigarray);
}
elseif (array_search("# QUESTION_ATTRIBUTES TABLE\r\n", $bigarray))
{
	$stoppoint = array_search("# QUESTION_ATTRIBUTES TABLE\r\n", $bigarray);
}
else
{
	$stoppoint = count($bigarray)-1;
}

for ($i=0; $i<=$stoppoint+1; $i++)
{
	if ($i<$stoppoint-2) {$labelsarray[] = $bigarray[$i];}
	unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);

//QUESTION_ATTRIBUTES
if (array_search("# ASSESSMENTS TABLE\n", $bigarray))
{
	$stoppoint = array_search("# ASSESSMENTS TABLE\n", $bigarray);
}
elseif (array_search("# ASSESSMENTS TABLE\r\n", $bigarray))
{
	$stoppoint = array_search("# ASSESSMENTS TABLE\r\n", $bigarray);
}
else
{
	$stoppoint = count($bigarray)-1;
}
for ($i=0; $i<=$stoppoint+1; $i++)
{
	if ($i<$stoppoint-2) {$question_attributesarray[] = $bigarray[$i];}
	unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);


//ASSESSMENTS
if (array_search("# SURVEYS_LANGUAGESETTINGS TABLE\n", $bigarray))
{
	$stoppoint = array_search("# SURVEYS_LANGUAGESETTINGS TABLE\n", $bigarray);
}
elseif (array_search("# SURVEYS_LANGUAGESETTINGS TABLE\r\n", $bigarray))
{
	$stoppoint = array_search("# SURVEYS_LANGUAGESETTINGS TABLE\r\n", $bigarray);
}
else
{
	$stoppoint = count($bigarray)-1;
}
for ($i=0; $i<=$stoppoint+1; $i++)
{
//	if ($i<$stoppoint-2 || $i==count($bigarray)-1)
	if ($i<$stoppoint-2)
	{
		$assessmentsarray[] = $bigarray[$i];
	}
	unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);

//LANGAUGE SETTINGS
if (array_search("# QUOTA TABLE\n", $bigarray))
{
	$stoppoint = array_search("# QUOTA TABLE\n", $bigarray);
}
elseif (array_search("# QUOTA TABLE\r\n", $bigarray))
{
	$stoppoint = array_search("# QUOTA TABLE\r\n", $bigarray);
}
else
{
	$stoppoint = count($bigarray)-1;
}
for ($i=0; $i<=$stoppoint+1; $i++)
{
//	if ($i<$stoppoint-2 || $i==count($bigarray)-1)
                           //$bigarray[$i]=        trim($bigarray[$i]);
	if (isset($bigarray[$i]) && (trim($bigarray[$i])!=''))
	{
		if (strpos($bigarray[$i],"#")===0)
        {
            unset($bigarray[$i]);
            unset($bigarray[$i+1]);
            unset($bigarray[$i+2]);
            break ;
        }
        else
        {
            $surveylsarray[] = $bigarray[$i];
        }
	}
    unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);

//QUOTA
if (array_search("# QUOTA_MEMBERS TABLE\n", $bigarray))
{
	$stoppoint = array_search("# QUOTA_MEMBERS TABLE\n", $bigarray);
}
elseif (array_search("# QUOTA_MEMBERS TABLE\r\n", $bigarray))
{
	$stoppoint = array_search("# QUOTA_MEMBERS TABLE\r\n", $bigarray);
}
else
{
	$stoppoint = count($bigarray)-1;
}
for ($i=0; $i<=$stoppoint+1; $i++)
{
//	if ($i<$stoppoint-2 || $i==count($bigarray)-1)
	if ($i<$stoppoint-2)
	{
		$quotaarray[] = $bigarray[$i];
	}
	unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);

//QUOTA MEMBERS
if (array_search("# QUOTA_LANGUAGESETTINGS TABLE\n", $bigarray))
{
	$stoppoint = array_search("# QUOTA_LANGUAGESETTINGS TABLE\n", $bigarray);
}
elseif (array_search("# QUOTA_LANGUAGESETTINGS TABLE\r\n", $bigarray))
{
	$stoppoint = array_search("# QUOTA_LANGUAGESETTINGS TABLE\r\n", $bigarray);
}
else
{
	$stoppoint = count($bigarray)-1;
}
for ($i=0; $i<=$stoppoint+1; $i++)
{
//	if ($i<$stoppoint-2 || $i==count($bigarray)-1)
	if ($i<$stoppoint-2)
	{
		$quotamembersarray[] = $bigarray[$i];
	}
	unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);


//Whatever is the last table - currently 
//QUOTA LANGUAGE SETTINGS
$stoppoint = count($bigarray)-1;
for ($i=0; $i<$stoppoint-1; $i++)
{
	if ($i<=$stoppoint) {$quotalsarray[] = $bigarray[$i];}
	unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);

if (isset($surveyarray)) {$countsurveys = count($surveyarray);} else {$countsurveys = 0;}
if (isset($surveylsarray)) {$countlanguages = count($surveylsarray)-1;} else {$countlanguages = 1;}
if (isset($grouparray)) {$countgroups = count($grouparray);} else {$countgroups = 0;}
if (isset($questionarray)) {$countquestions = count($questionarray);} else {$countquestions=0;}
if (isset($answerarray)) {$countanswers = count($answerarray);} else {$countanswers=0;}
if (isset($conditionsarray)) {$countconditions = count($conditionsarray);} else {$countconditions=0;}
if (isset($labelsetsarray)) {$countlabelsets = count($labelsetsarray);} else {$countlabelsets=0;}
if (isset($question_attributesarray)) {$countquestion_attributes = count($question_attributesarray);} else {$countquestion_attributes=0;}
if (isset($assessmentsarray)) {$countassessments=count($assessmentsarray);} else {$countassessments=0;}
if (isset($quotaarray)) {$countquota=count($quotaarray);} else {$countquota=0;}
if (isset($quotamembersarray)) {$countquotamembers=count($quotamembersarray);} else {$countquotamembers=0;}
if (isset($quotalsarray)) {$countquotals=count($quotalsarray);} else {$countquotals=0;}

// CREATE SURVEY

if ($importversion>=111)
{
    if ($countsurveys>0){$countsurveys--;};
    if ($countanswers>0){$countanswers=($countanswers-1)/$countlanguages;}; 
    if ($countgroups>0){$countgroups=($countgroups-1)/$countlanguages;};
    if ($countquestions>0){$countquestions=($countquestions-1)/$countlanguages;}; 
    if ($countassessments>0){$countassessments--;};
    if ($countconditions>0){$countconditions--;};
    if ($countlabelsets>0){$countlabelsets--;};
    if ($countquestion_attributes>0){$countquestion_attributes--;};
    if ($countquota>0){$countquota--;};
    $sfieldorders  =convertCSVRowToArray($surveyarray[0],',','"');
    $sfieldcontents=convertCSVRowToArray($surveyarray[1],',','"');
}
else
    {
    $sfieldorders=convertToArray($surveyarray[0], "`, `", "(`", "`)");
    $sfieldcontents=convertToArray($surveyarray[0], "', '", "('", "')");
    }
$surveyrowdata=array_combine($sfieldorders,$sfieldcontents);
$surveyid=$surveyrowdata["sid"];


if (!$surveyid)
{
	if ($importingfrom == "http")
	{
		$importsurvey .= "<br /><div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
		$importsurvey .= $clang->gT("Import of this survey file failed")."<br />\n";
		$importsurvey .= $clang->gT("File does not contain LimeSurvey data in the correct format.")."<br /><br />\n"; //Couldn't find the SID - cannot continue
		$importsurvey .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";
		$importsurvey .= "</div>\n";
		unlink($the_full_file_path); //Delete the uploaded file
		return;
	}
	else 
	{
		echo $clang->gT("Import of this survey file failed")."\n".$clang->gT("File does not contain LimeSurvey data in the correct format.")."\n";
		return;
	}
}

// Use the existing surveyid if it does not already exists
// This allows the URL links to the survey to keep working because the sid did not change
	$newsid = $surveyid; 
	$isquery = "SELECT sid FROM {$dbprefix}surveys WHERE sid=$newsid";
	$isresult = db_execute_assoc($isquery);
	if ($isresult->RecordCount()>0)
	{
		// Get new random ids until one is found that is not used
		do
		{
			$newsid = getRandomID();
			$isquery = "SELECT sid FROM {$dbprefix}surveys WHERE sid=$newsid";
			$isresult = db_execute_assoc($isquery);
		}
		while ($isresult->RecordCount()>0);
	}

$importwarning = "";	// used to save the warnings while processing questions
$insert=$surveyarray[0];
if ($importversion>=111)
{
    $sfieldorders  =convertCSVRowToArray($surveyarray[0],',','"');
    $sfieldcontents=convertCSVRowToArray($surveyarray[1],',','"');
}
else
    {
    $sfieldorders=convertToArray($surveyarray[0], "`, `", "(`", "`)");
    $sfieldcontents=convertToArray($surveyarray[0], "', '", "('", "')");
    }
$surveyrowdata=array_combine($sfieldorders,$sfieldcontents);
// Set new owner ID
$surveyrowdata['owner_id']=$_SESSION['loginID'];
// Set new survey ID
$surveyrowdata['sid']=$newsid;
$surveyrowdata['active']='N';

if (validate_templatedir($surveyrowdata['template'])!==$surveyrowdata['template']) $importwarning .= "<li>". sprintf($clang->gT('Template %s not found, please review when activating.'),$surveyrowdata['template']) ."</li>";

if ($importversion<=100)
// find the old language field and replace its contents with the new language shortcuts
    {
    $oldlanguage=$surveyrowdata['language'];
    $newlanguage='en'; //Default
    switch ($oldlanguage) 
      {
      case "bulgarian":
         $newlanguage='bg';
         break;
      case "chinese-simplified":
         $newlanguage='zh-Hans';
         break;
      case "chinese-traditional":
         $newlanguage='zh-Hant-HK';
         break;
      case "croatian":
         $newlanguage='hr';
         break;
      case "danish":
         $newlanguage='da';
         break;
      case "dutch":
         $newlanguage='nl';
         break;
      case "english":
         $newlanguage='en';
         break;
      case "french":
         $newlanguage='fr';
         break;
      case "german-informal":
         $newlanguage='de-informal';
         break;
      case "german":
         $newlanguage='de';
         break;
      case "greek":
         $newlanguage='el';
         break;
      case "hungarian":
         $newlanguage='hu';
         break;
      case "italian":
         $newlanguage='it';
         break;
      case "japanese":
         $newlanguage='ja';
         break;
      case "lithuanian":
         $newlanguage='lt';
         break;
      case "norwegian":
         $newlanguage='nb';
         break;
      case "portuguese":
         $newlanguage='pt';
         break;
      case "romanian":
         $newlanguage='ro';
         break;
      case "russian":
         $newlanguage='ru';
         break;
      case "slovenian":
         $newlanguage='sl';
         break;
      case "spanish":
         $newlanguage='es';
         break;
      case "swedish":
         $newlanguage='sv';
         break;
      }	

    $surveyrowdata['language']=$newlanguage;
    
    // copy the survey row data
    
     // now prepare the languagesettings table and drop according values from the survey array
    $surveylsrowdata=array();     
    $surveylsrowdata['surveyls_survey_id']=$newsid;     
    $surveylsrowdata['surveyls_language']=$newlanguage;     
    $surveylsrowdata['surveyls_title']=$surveyrowdata['short_title'];
    $surveylsrowdata['surveyls_description']=$surveyrowdata['description'];
    $surveylsrowdata['surveyls_welcometext']=$surveyrowdata['welcome'];
    $surveylsrowdata['surveyls_urldescription']=$surveyrowdata['urldescrip'];
    if (isset($surveyrowdata['email_invite_subj'])) $surveylsrowdata['surveyls_email_invite_subj']=$surveyrowdata['email_invite_subj'];
    $surveylsrowdata['surveyls_email_invite']=$surveyrowdata['email_invite'];
    if (isset($surveyrowdata['email_remind_subj']))     $surveylsrowdata['surveyls_email_remind_subj']=$surveyrowdata['email_remind_subj'];
    $surveylsrowdata['surveyls_email_remind']=$surveyrowdata['email_remind'];
    if (isset($surveyrowdata['email_register_subj']))     $surveylsrowdata['surveyls_email_register_subj']=$surveyrowdata['email_register_subj'];
    $surveylsrowdata['surveyls_email_register']=$surveyrowdata['email_register'];
    if (isset($surveyrowdata['email_confirm_subj'])) $surveylsrowdata['surveyls_email_confirm_subj']=$surveyrowdata['email_confirm_subj'];
    $surveylsrowdata['surveyls_email_confirm']=$surveyrowdata['email_confirm'];
	if(!isset($defaultsurveylanguage)) {$defaultsurveylanguage=$newlanguage;}
    unset($surveyrowdata['short_title']);
    unset($surveyrowdata['description']);
    unset($surveyrowdata['welcome']);
    unset($surveyrowdata['urldescrip']);
    unset($surveyrowdata['email_invite_subj']);
    unset($surveyrowdata['email_invite']);
    unset($surveyrowdata['email_remind_subj']);
    unset($surveyrowdata['email_remind']);
    unset($surveyrowdata['email_register_subj']);
    unset($surveyrowdata['email_register']);
    unset($surveyrowdata['email_confirm_subj']);
    unset($surveyrowdata['email_confirm']);


    // translate internal links
    $surveylsrowdata['surveyls_title']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_title']);
    $surveylsrowdata['surveyls_description']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_description']);
    $surveylsrowdata['surveyls_welcometext']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_welcometext']);
    $surveylsrowdata['surveyls_urldescription']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_urldescription']);
    $surveylsrowdata['surveyls_email_invite']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_email_invite']);
    $surveylsrowdata['surveyls_email_remind']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_email_remind']);
    $surveylsrowdata['surveyls_email_register']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_email_register']);
    $surveylsrowdata['surveyls_email_confirm']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_email_confirm']);
    
    

    // import the survey language-specific settings
    $values=array_values($surveylsrowdata);
    $values=array_map(array(&$connect, "qstr"),$values); // quote everything accordingly
    $insert = "insert INTO {$dbprefix}surveys_languagesettings (".implode(',',array_keys($surveylsrowdata)).") VALUES (".implode(',',$values).")"; //handle db prefix
    $iresult = $connect->Execute($insert) or safe_die("<br />".$clang->gT("Import of this survey file failed")."<br />\n[$insert]<br />{$surveyarray[0]}<br /><br />\n" . $connect->ErrorMsg());
    }



if (isset($surveyrowdata['datecreated'])) {$surveyrowdata['datecreated']=$connect->BindTimeStamp($surveyrowdata['datecreated']);}
unset($surveyrowdata['expires']);
unset($surveyrowdata['attribute1']);
unset($surveyrowdata['attribute2']);
unset($surveyrowdata['usestartdate']);
unset($surveyrowdata['useexpiry']);
unset($surveyrowdata['url']);           
if (isset($surveyrowdata['startdate'])) {unset($surveyrowdata['startdate']);}
$surveyrowdata['bounce_email']=$surveyrowdata['adminemail'];
if (!isset($surveyrowdata['datecreated']) || $surveyrowdata['datecreated']=='' || $surveyrowdata['datecreated']=='null') {$surveyrowdata['datecreated']=$connect->BindTimeStamp(date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust));}

$values=array_values($surveyrowdata);
$values=array_map(array(&$connect, "qstr"),$values); // quote everything accordingly
$insert = "INSERT INTO {$dbprefix}surveys (".implode(',',array_keys($surveyrowdata)).") VALUES (".implode(',',$values).")"; //handle db prefix
$iresult = $connect->Execute($insert) or safe_die("<br />".$clang->gT("Import of this survey file failed")."<br />\n[$insert]<br />{$surveyarray[0]}<br /><br />\n" . $connect->ErrorMsg());

$oldsid=$surveyid;

// Now import the survey language settings
if ($importversion>=111)
{
    $fieldorders=convertCSVRowToArray($surveylsarray[0],',','"');
    unset($surveylsarray[0]);
	foreach ($surveylsarray as $slsrow) {
        $fieldcontents=convertCSVRowToArray($slsrow,',','"');
	$surveylsrowdata=array_combine($fieldorders,$fieldcontents);
	// convert back the '\'.'n' cahr from the CSV file to true return char "\n"
	$surveylsrowdata=array_map('convertCsvreturn2return', $surveylsrowdata);
	// Convert the \n return char from welcometext to <br />

    // translate internal links
    $surveylsrowdata['surveyls_title']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_title']);
    $surveylsrowdata['surveyls_description']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_description']);
    $surveylsrowdata['surveyls_welcometext']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_welcometext']);
    $surveylsrowdata['surveyls_urldescription']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_urldescription']);
    $surveylsrowdata['surveyls_email_invite']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_email_invite']);
    $surveylsrowdata['surveyls_email_remind']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_email_remind']);
    $surveylsrowdata['surveyls_email_register']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_email_register']);
    $surveylsrowdata['surveyls_email_confirm']=translink('survey', $surveyid, $newsid, $surveylsrowdata['surveyls_email_confirm']);

        $surveylsrowdata['surveyls_survey_id']=$newsid;     
        $newvalues=array_values($surveylsrowdata);
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
        $lsainsert = "INSERT INTO {$dbprefix}surveys_languagesettings (".implode(',',array_keys($surveylsrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
		$lsiresult=$connect->Execute($lsainsert) or safe_die("<br />".$clang->gT("Import of this survey file failed")."<br />\n[$lsainsert]<br />\n" . $connect->ErrorMsg() );
	}	
		
}


// DO SURVEY_RIGHTS
$isrquery = "INSERT INTO {$dbprefix}surveys_rights VALUES($newsid,".$_SESSION['loginID'].",1,1,1,1,1,1)";
@$isrresult = $connect->Execute($isrquery);
$deniedcountlabelsets =0;


//DO ANY LABELSETS FIRST, SO WE CAN KNOW WHAT THEIR NEW LID IS FOR THE QUESTIONS
if (isset($labelsetsarray) && $labelsetsarray) {
	$csarray=buildLabelSetCheckSumArray();   // build checksums over all existing labelsets
	$count=0;
	foreach ($labelsetsarray as $lsa) {
	    
        if ($importversion>=111)
        {
            $fieldorders  =convertCSVRowToArray($labelsetsarray[0],',','"');
            $fieldcontents=convertCSVRowToArray($lsa,',','"');
            if ($count==0) {$count++; continue;}
        }
        else
            {
                $fieldorders=convertToArray($lsa, "`, `", "(`", "`)");
        		$fieldcontents=convertToArray($lsa, "', '", "('", "')");
            }		
		$labelsetrowdata=array_combine($fieldorders,$fieldcontents);
		
		// Save old labelid
		$oldlid=$labelsetrowdata['lid'];
		// set the new language
		if ($importversion<=100)
            {
            $labelsetrowdata['languages']=$newlanguage;
            } 
        unset($labelsetrowdata['lid']);
        $newvalues=array_values($labelsetrowdata);
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
        $lsainsert = "insert INTO {$dbprefix}labelsets (".implode(',',array_keys($labelsetrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
		$lsiresult=$connect->Execute($lsainsert);
		
		// Get the new insert id for the labels inside this labelset
		$newlid=$connect->Insert_ID("{$dbprefix}labelsets","lid");

//		$importsurvey .= "OLDLID: $oldlid   NEWLID: $newlid";  
//      For debugging label import

		if ($labelsarray) {
		    $count=0;
			foreach ($labelsarray as $la) {
                if ($importversion>=111)
                {
                    $lfieldorders  =convertCSVRowToArray($labelsarray[0],',','"');
                    $lfieldcontents=convertCSVRowToArray($la,',','"');
                    if ($count==0) {$count++; continue;}
                }
                else
                    {
        				//Get field names into array
        				$lfieldorders=convertToArray($la, "`, `", "(`", "`)");
        				//Get field values into array
        				$lfieldcontents=convertToArray($la, "', '", "('", "')");
                    }		
        		// Combine into one array with keys and values since its easier to handle
         		$labelrowdata=array_combine($lfieldorders,$lfieldcontents);
                if ($importversion<=132)
                {
                   $labelrowdata["assessment_value"]=(int)$labelrowdata["code"];
                }            
				$labellid=$labelrowdata['lid'];
		        if ($importversion<=100)
                {
                $labelrowdata['language']=$newlanguage;
                } 				
				if ($labellid == $oldlid) {
					$labelrowdata['lid']=$newlid;

    		// translate internal links
		    $labelrowdata['title']=translink('label', $oldlid, $newlid, $labelrowdata['title']);

                    $newvalues=array_values($labelrowdata);
                    $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                    $lainsert = "insert INTO {$dbprefix}labels (".implode(',',array_keys($labelrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
					$liresult=$connect->Execute($lainsert);

				}
			}
		}

		//CHECK FOR DUPLICATE LABELSETS
		$thisset="";
        
        $query2 = "SELECT code, title, sortorder, language, assessment_value
                   FROM {$dbprefix}labels
                   WHERE lid=".$newlid."
                   ORDER BY language, sortorder, code";
		$result2 = db_execute_num($query2) or safe_die("Died querying labelset $lid<br />$query2<br />".$connect->ErrorMsg());
		while($row2=$result2->FetchRow())
		{
			$thisset .= implode('.', $row2);
		} // while
		$newcs=dechex(crc32($thisset)*1);
        unset($lsmatch);
		if (isset($csarray))
		{
			foreach($csarray as $key=>$val)
			{
				if ($val == $newcs)           
				{
					$lsmatch=$key;
				}
			}
		}
		if (isset($lsmatch) || ($_SESSION['USER_RIGHT_MANAGE_LABEL'] != 1))
		{
			//There is a matching labelset or the user is not allowed to edit labels -  
            // So, we will delete this one and refer to the matched one.
			$query = "DELETE FROM {$dbprefix}labels WHERE lid=$newlid";
			$result=$connect->Execute($query) or safe_die("Couldn't delete labels<br />$query<br />".$connect->ErrorMsg());
			$query = "DELETE FROM {$dbprefix}labelsets WHERE lid=$newlid";
			$result=$connect->Execute($query) or safe_die("Couldn't delete labelset<br />$query<br />".$connect->ErrorMsg());
			if (isset($lsmatch)) {$newlid=$lsmatch;}
              else {++$deniedcountlabelsets;--$countlabelsets;}
		}
		else
		{
			//There isn't a matching labelset, add this checksum to the $csarray array
			$csarray[$newlid]=$newcs;
		}
		//END CHECK FOR DUPLICATES
		$labelreplacements[$oldlid]=$newlid;
	}
}

$qtypes = getqtypelist("" ,"array");

// DO GROUPS, QUESTIONS FOR GROUPS, THEN ANSWERS FOR QUESTIONS IN A NESTED FORMAT!
if (isset($grouparray) && $grouparray) {
    $count=0;
    $currentgid='';
	foreach ($grouparray as $ga) {
        if ($importversion>=111)
        {
            $gafieldorders   =convertCSVRowToArray($grouparray[0],',','"');
            $gacfieldcontents=convertCSVRowToArray($ga,',','"');
            if ($count==0) {$count++; continue;}
        }
        else
            {
				//Get field names into array
        		$gafieldorders=convertToArray($ga, "`, `", "(`", "`)");
				//Get field values into array
        		$gacfieldcontents=convertToArray($ga, "', '", "('", "')");
            }
		$grouprowdata=array_combine($gafieldorders,$gacfieldcontents);
        // remember group id
        if ($currentgid=='' || ($currentgid!=$grouprowdata['gid'])) {$currentgid=$grouprowdata['gid'];$newgroup=true;}
          else 
            if ($currentgid==$grouprowdata['gid']) {$newgroup=false;}    		
		$gid=$grouprowdata['gid'];
		$gsid=$grouprowdata['sid'];
		//Now an additional integrity check if there are any groups not belonging into this survey
		if ($gsid != $surveyid)
		{
			if ($importingfrom == "http") 
            { 
                $importsurvey .= "<br />\n<font color='red'><strong>".$clang->gT("Error")."</strong></font>"
                                ."<br />\n".$clang->gT("A group in the CSV/SQL file is not part of the same survey. The import of the survey was stopped.")."<br /><br />\n";
            }
            else
            {
                echo $clang->gT("Error").": A group in the CSV/SQL file is not part of the same Survey. The import of the survey was stopped.\n";
            }
			return;
		}
		//remove the old group id
		if ($newgroup) {unset($grouprowdata['gid']);} 
            else {$grouprowdata['gid']=$newgid;}
        //replace old surveyid by new surveyid
        $grouprowdata['sid']=$newsid;  
        // Version <=100 dont have a language field yet so we set it now
		if ($importversion<=100)  
            {
            $grouprowdata['language']=$newlanguage;
            } 
		$oldgid=$gid; // save it for later
        $grouprowdata=array_map('convertCsvreturn2return', $grouprowdata);

    		// translate internal links
		    $grouprowdata['group_name']=translink('survey', $surveyid, $newsid, $grouprowdata['group_name']);
		    $grouprowdata['description']=translink('survey', $surveyid, $newsid, $grouprowdata['description']);
        
        $newvalues=array_values($grouprowdata);
        
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly

        if (isset($grouprowdata['gid'])) {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('groups')." ON");}
        $ginsert = 'insert INTO '.db_table_name('groups').' ('.implode(',',array_keys($grouprowdata)).') VALUES ('.implode(',',$newvalues).')'; 
		$gres = $connect->Execute($ginsert) or safe_die($clang->gT('Error').": Failed to insert group<br />\n$ginsert<br />\n".$connect->ErrorMsg());
        if (isset($grouprowdata['gid'])) {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('groups').' OFF');}
		//GET NEW GID
		if ($newgroup) {$newgid=$connect->Insert_ID("{$dbprefix}groups","gid");}

		//NOW DO NESTED QUESTIONS FOR THIS GID
		
		if (isset($questionarray) && $questionarray && $newgroup) {
		    $count=0;  
            $currentqid='';
			foreach ($questionarray as $qa) {
                if ($importversion>=111)
                {
                    $qafieldorders   =convertCSVRowToArray($questionarray[0],',','"');
                    $qacfieldcontents=convertCSVRowToArray($qa,',','"');
                    if ($count==0) {$count++; continue;}
                }
                else
                {
	        				$qafieldorders=convertToArray($qa, "`, `", "(`", "`)");
	        				$qacfieldcontents=convertToArray($qa, "', '", "('", "')");
                }
 	          		$questionrowdata=array_combine($qafieldorders,$qacfieldcontents);
                $questionrowdata=array_map('convertCsvreturn2return', $questionrowdata);

                if ($currentqid=='' || ($currentqid!=$questionrowdata['qid'])) {$currentqid=$questionrowdata['qid'];$newquestion=true;}
                  else 
                    if ($currentqid==$questionrowdata['qid']) {$newquestion=false;}

				if (!array_key_exists($questionrowdata["type"], $qtypes) && $questionrowdata["type"]!='W' && $questionrowdata["type"]!='Z')
                {
                	$questionrowdata["type"] = strtoupper($questionrowdata["type"]);
                	if (!array_key_exists($questionrowdata["type"], $qtypes))
                	{
                		$importwarning .= "<li>" . sprintf($clang->gT("Question \"%s - %s\" was NOT imported because the question type is unknown."), $questionrowdata["title"], $questionrowdata["question"]) . "</li>";
                		$countquestions--;
                		continue;
                	}
                	else	// the upper case worked well
                	{
                		$importwarning .= "<li>" . sprintf($clang->gT("Question \"%s - %s\" was imported but the type was set to '%s' because it is the most similiar one."), $questionrowdata["title"], $questionrowdata["question"], $qtypes[$questionrowdata["type"]]['description']) . "</li>";
                	}
                }
                        		

				$thisgid=$questionrowdata['gid'];
				if ($thisgid == $gid) {
					$qid = $questionrowdata['qid'];
					// Remove qid field
					if ($newquestion) {unset($questionrowdata['qid']);}
					   else {$questionrowdata['qid']=$newqid;}
					
					$questionrowdata["sid"] = $newsid;
					$questionrowdata["gid"] = $newgid;
                    // Version <=100 doesn't have a language field yet so we set it now
            		if ($importversion<=100)  
                    {
                        $questionrowdata['language']=$newlanguage;
                    } 
					$oldqid=$qid;

                    if ($importversion<143) 
                    {
                        unset($oldlid1); unset($oldlid2);
                        if ((isset($questionrowdata['lid']) && $questionrowdata['lid']>0))
                        {
                            $oldlid1=$questionrowdata['lid'];
					    }
                        if ((isset($questionrowdata['lid1']) && $questionrowdata['lid1']>0))
						{
                            $oldlid2=$questionrowdata['lid1'];
                        }
                        unset($questionrowdata['lid']);
                        unset($questionrowdata['lid1']);
                        if ($questionrowdata['type']=='W')
						{
                            $questionrowdata['type']='!';
						}
                        elseif ($questionrowdata['type']=='Z')
						{
                            $questionrowdata['type']='L';
						}
                        
                    }
                    
                    if (!isset($questionrowdata["question_order"]) || $questionrowdata["question_order"]=='') {$questionrowdata["question_order"]=0;} 
		            $other = $questionrowdata["other"]; //Get 'other' field value
                    $type = $questionrowdata['type'];
    		        // translate internal links
		            $questionrowdata['title']=translink('survey', $surveyid, $newsid, $questionrowdata['title']);
		            $questionrowdata['question']=translink('survey', $surveyid, $newsid, $questionrowdata['question']);
		            $questionrowdata['help']=translink('survey', $surveyid, $newsid, $questionrowdata['help']);

                    $newvalues=array_values($questionrowdata);
                    if (isset($questionrowdata['qid'])) {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('questions').' ON');}

                    $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                    $qinsert = "insert INTO {$dbprefix}questions (".implode(',',array_keys($questionrowdata)).") VALUES (".implode(',',$newvalues).")"; 

			        $qres = $connect->Execute($qinsert) or safe_die ($clang->gT("Error").": Failed to insert question<br />\n$qinsert<br />\n".$connect->ErrorMsg());
                    if (isset($questionrowdata['qid'])) {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('questions').' OFF');}
		            if ($newquestion)
			        {
				        $newqid=$connect->Insert_ID("{$dbprefix}questions","qid");
			        }
					
                    
                    // Now we will fix up old label sets where they are used as answers
                    if ($importversion<143 && (isset($oldlid1) || isset($oldlid2)) && ($qtypes[$type]['answerscales']>0 || $qtypes[$type]['subquestions']>1))
                    {             
                        $query="select * from ".db_table_name('labels')." where lid={$labelreplacements[$oldlid1]}";
                        $oldlabelsresult=db_execute_assoc($query);
                        while($labelrow=$oldlabelsresult->FetchRow())
                        {
                            
                            if ($qtypes[$type]['subquestions']<2)
                            {
                                $qinsert = "insert INTO ".db_table_name('answers')." (qid,code,answer,sortorder,language,assessment_value) 
                                            VALUES ($newqid,".db_quoteall($labelrow['code']).",".db_quoteall($labelrow['title']).",".db_quoteall($labelrow['sortorder']).",".db_quoteall($labelrow['language']).",".db_quoteall($labelrow['assessment_value']).")"; 
                                $qres = $connect->Execute($qinsert) or safe_die ($clang->gT("Error").": Failed to insert answer <br />\n$qinsert<br />\n".$connect->ErrorMsg());
                            }
                            else
                            {
                                $qinsert = "insert INTO ".db_table_name('questions')." (parent_qid,title,question,question_order,language,scale_id) 
                                            VALUES ($newqid,".db_quoteall($labelrow['code']).",".db_quoteall($labelrow['title']).",".db_quoteall($labelrow['sortorder']).",".db_quoteall($labelrow['language']).",1)"; 
                                $qres = $connect->Execute($qinsert) or safe_die ($clang->gT("Error").": Failed to insert answer <br />\n$qinsert<br />\n".$connect->ErrorMsg());
                            }
                        }
                        if (isset($oldlid2) && $qtypes[$type]['answerscales']>1)
                        {
                            $query="select * from ".db_table_name('labels')." where lid={$labelreplacements[$oldlid2]}";
                            $oldlabelsresult=db_execute_assoc($query);
                            while($labelrow=$oldlabelsresult->FetchRow())
                            {
                                $qinsert = "insert INTO ".db_table_name('answers')." (qid,code,answer,sortorder,language,assessment_value,scale_id) 
                                            VALUES ($newqid,".db_quoteall($labelrow['code']).",".db_quoteall($labelrow['title']).",".db_quoteall($labelrow['sortorder']).",".db_quoteall($labelrow['language']).",".db_quoteall($labelrow['assessment_value']).",1)"; 
                                $qres = $connect->Execute($qinsert) or safe_die ($clang->gT("Error").": Failed to insert answer <br />\n$qinsert<br />\n".$connect->ErrorMsg());
                            }
                        }
                          

                    }
                    
					$newrank=0;
					$substitutions[]=array($oldsid, $oldgid, $oldqid, $newsid, $newgid, $newqid);
					
					//NOW DO NESTED ANSWERS FOR THIS QID
					if (isset($answerarray) && $answerarray && $newquestion) {
					    $count=0; 
						foreach ($answerarray as $aa) {
                            if ($importversion>=111)
                            {
                                $aafieldorders   =convertCSVRowToArray($answerarray[0],',','"');
                                $aacfieldcontents=convertCSVRowToArray($aa,',','"');
                                if ($count==0) {$count++; continue;}
                            }
                            else
                                {
        							$aafieldorders=convertToArray($aa, "`, `", "(`", "`)");
        							$aacfieldcontents=convertToArray($aa, "', '", "('", "')");
                                }		
                    		$answerrowdata=array_combine($aafieldorders,$aacfieldcontents);
                            if ($importversion<=132)
                            {
                               $answerrowdata["assessment_value"]=(int)$answerrowdata["code"];
                            }
							$code=$answerrowdata["code"];
							$thisqid=$answerrowdata["qid"];
							if ($thisqid == $qid) 
                            {
								$answerrowdata["qid"]=$newqid;
                                // Version <=100 doesn't have a language field yet so we set it now
                        		if ($importversion<=100)  
                                {
                                    $answerrowdata['language']=$newlanguage;
                                } 
    				
			                    // translate internal links
		    		            $answerrowdata['answer']=translink('survey', $surveyid, $newsid, $answerrowdata['answer']);

                                $newvalues=array_values($answerrowdata);
                                $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                                
                                // Now we will fix up old answers where they are really subquestions
                                if ($importversion<143 && $qtypes[$type]['subquestions']>0)
                                {
                                    $qinsert = "insert INTO ".db_table_name('questions')." (parent_qid,sid,gid,title,question,question_order,language) 
                                                VALUES ({$answerrowdata['qid']},$newsid,$newgid,".db_quoteall($answerrowdata['code']).",".db_quoteall($answerrowdata['answer']).",".db_quoteall($answerrowdata['sortorder']).",".db_quoteall($answerrowdata['language']).")"; 
                                    $qres = $connect->Execute($qinsert) or safe_die ($clang->gT("Error").": Failed to insert answer <br />\n$qinsert<br />\n".$connect->ErrorMsg());
                                }
                                else 
                                {
                                    $ainsert = "insert INTO {$dbprefix}answers (".implode(',',array_keys($answerrowdata)).") VALUES (".implode(',',$newvalues).")"; 
								    $ares = $connect->Execute($ainsert) or safe_die ($clang->gT("Error").": Failed to insert answer<br />\n$ainsert<br />\n".$connect->ErrorMsg());
                                }                                
								
								if ($type == "M" || $type == "P") {
									$fieldnames[]=array("oldcfieldname"=>$oldsid."X".$oldgid."X".$oldqid,
									"newcfieldname"=>$newsid."X".$newgid."X".$newqid,
									"oldfieldname"=>$oldsid."X".$oldgid."X".$oldqid.$code,
									"newfieldname"=>$newsid."X".$newgid."X".$newqid.$code);
									$fieldnames[]=array("oldcfieldname"=>'+'.$oldsid."X".$oldgid."X".$oldqid.$code,
									"newcfieldname"=>'+'.$newsid."X".$newgid."X".$newqid.$code,
									"oldfieldname"=>"+".$oldsid."X".$oldgid."X".$oldqid.$code,
									"newfieldname"=>"+".$newsid."X".$newgid."X".$newqid.$code);
									if ($type == "P") {
										$fieldnames[]=array("oldcfieldname"=>$oldsid."X".$oldgid."X".$oldqid."comment",
										"newcfieldname"=>$newsid."X".$newgid."X".$newqid.$code."comment",
										"oldfieldname"=>$oldsid."X".$oldgid."X".$oldqid.$code."comment",
										"newfieldname"=>$newsid."X".$newgid."X".$newqid.$code."comment");
									}
								}
								elseif ($type == "A" || $type == "B" || $type == "C" || $type == "F" || $type == "H" || $type == "E" || $type == "Q" || $type == "K" || $type == "1") {
									$fieldnames[]=array("oldcfieldname"=>$oldsid."X".$oldgid."X".$oldqid.$code,
									"newcfieldname"=>$newsid."X".$newgid."X".$newqid.$code,
									"oldfieldname"=>$oldsid."X".$oldgid."X".$oldqid.$code,
									"newfieldname"=>$newsid."X".$newgid."X".$newqid.$code);
								}
								elseif ($type == ":" || $type == ";" ) {
									// read all label codes from $questionrowdata["lid"]
									// for each one (as L) set SGQA_L
	/*								$labelq="SELECT DISTINCT code FROM {$dbprefix}labels WHERE lid=".$questionrowdata["lid"];
									$labelqresult=db_execute_num($labelq) or safe_die("Died querying labelset $lid<br />$query2<br />".$connect->ErrorMsg());
									while ($labelqrow=$labelqresult->FetchRow())
									{
										$fieldnames[]=array("oldcfieldname"=>$oldsid."X".$oldgid."X".$oldqid.$code."_".$labelqrow[0],
												"newcfieldname"=>$newsid."X".$newgid."X".$newqid.$code."_".$labelqrow[0],
												"oldfieldname"=>$oldsid."X".$oldgid."X".$oldqid.$code."_".$labelqrow[0],
												"newfieldname"=>$newsid."X".$newgid."X".$newqid.$code."_".$labelqrow[0]);
									}  */
									}
								elseif ($type == "R") {
									$newrank++;
								}
							}
						}
						if (($type == "A" || $type == "B" || $type == "C" || $type == "M" || $type == "P" || $type == "L") && ($other == "Y")) {
							$fieldnames[]=array("oldcfieldname"=>$oldsid."X".$oldgid."X".$oldqid."other",
							"newcfieldname"=>$newsid."X".$newgid."X".$newqid."other",
							"oldfieldname"=>$oldsid."X".$oldgid."X".$oldqid."other",
							"newfieldname"=>$newsid."X".$newgid."X".$newqid."other");
							if ($type == "P") {
								$fieldnames[]=array("oldcfieldname"=>$oldsid."X".$oldgid."X".$oldqid."othercomment",
								"newcfieldname"=>$newsid."X".$newgid."X".$newqid."othercomment",
								"oldfieldname"=>$oldsid."X".$oldgid."X".$oldqid."othercomment",
								"newfieldname"=>$newsid."X".$newgid."X".$newqid."othercomment");
							}
						}
						if ($type == "R" && $newrank >0) {
							for ($i=1; $i<=$newrank; $i++) {
								$fieldnames[]=array("oldcfieldname"=>$oldsid."X".$oldgid."X".$oldqid.$i,
								"newcfieldname"=>$newsid."X".$newgid."X".$newqid.$i,
								"oldfieldname"=>$oldsid."X".$oldgid."X".$oldqid.$i,
								"newfieldname"=>$newsid."X".$newgid."X".$newqid.$i);
							}
						}
						if ($type != "A" && $type != "B" && $type != "C" && $type != "R" && $type != "M" && $type != "P") {
							$fieldnames[]=array("oldcfieldname"=>$oldsid."X".$oldgid."X".$oldqid,
							"newcfieldname"=>$newsid."X".$newgid."X".$newqid,
							"oldfieldname"=>$oldsid."X".$oldgid."X".$oldqid,
							"newfieldname"=>$newsid."X".$newgid."X".$newqid);
							if ($type == "O") {
								$fieldnames[]=array("oldcfieldname"=>$oldsid."X".$oldgid."X".$oldqid."comment",
								"newcfieldname"=>$newsid."X".$newgid."X".$newqid."comment",
								"oldfieldname"=>$oldsid."X".$oldgid."X".$oldqid."comment",
								"newfieldname"=>$newsid."X".$newgid."X".$newqid."comment");
							}
						}
					} else {
						$fieldnames[]=array("oldcfieldname"=>$oldsid."X".$oldgid."X".$oldqid,
						"newcfieldname"=>$newsid."X".$newgid."X".$newqid,
						"oldfieldname"=>$oldsid."X".$oldgid."X".$oldqid,
						"newfieldname"=>$newsid."X".$newgid."X".$newqid);
					}
				}
			}
		}
	}
}

   // Fix sortorder of the groups  - if users removed groups manually from the csv file there would be gaps
   fixsortorderGroups();
   //... and for the questions inside the groups
   // get all group ids and fix questions inside each group
   $gquery = "SELECT gid FROM {$dbprefix}groups where sid=$newsid group by gid ORDER BY gid"; //Get last question added (finds new qid)
   $gres = db_execute_assoc($gquery);
   while ($grow = $gres->FetchRow()) 
        {
        fixsortorderQuestions($grow['gid'], $surveyid);
        }

        //We've built two arrays along the way - one containing the old SID, GID and QIDs - and their NEW equivalents
//and one containing the old 'extended fieldname' and its new equivalent.  These are needed to import conditions and question_attributes.
if (isset($question_attributesarray) && $question_attributesarray) {//ONLY DO THIS IF THERE ARE QUESTION_ATTRIBUES
    $count=0;
	foreach ($question_attributesarray as $qar) {
        if ($importversion>=111)
        {
            $fieldorders  =convertCSVRowToArray($question_attributesarray[0],',','"');
            $fieldcontents=convertCSVRowToArray($qar,',','"');
            if ($count==0) {$count++; continue;}
        }
        else
            {
        		$fieldorders=convertToArray($qar, "`, `", "(`", "`)");
        		$fieldcontents=convertToArray($qar, "', '", "('", "')");
            }		
        $qarowdata=array_combine($fieldorders,$fieldcontents);
		$newqid="";
		$oldqid=$qarowdata['qid'];
		foreach ($substitutions as $subs) {
			if ($oldqid==$subs[2]) {$newqid=$subs[5];}
		}

		$qarowdata["qid"]=$newqid;
		unset($qarowdata["qaid"]);

        $newvalues=array_values($qarowdata);
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
        $qainsert = "insert INTO {$dbprefix}question_attributes (".implode(',',array_keys($qarowdata)).") VALUES (".implode(',',$newvalues).")"; 
		$result=$connect->Execute($qainsert) or safe_die ("Couldn't insert question_attribute<br />$qainsert<br />".$connect->ErrorMsg());
	}
}

if (isset($assessmentsarray) && $assessmentsarray) {//ONLY DO THIS IF THERE ARE QUESTION_ATTRIBUTES
    $count=0; 
	foreach ($assessmentsarray as $qar) {
        if ($importversion>=111)
        {
            $fieldorders  =convertCSVRowToArray($assessmentsarray[0],',','"');
            $fieldcontents=convertCSVRowToArray($qar,',','"');
            if ($count==0) {$count++; continue;}
        }
        else
            {
        		$fieldorders=convertToArray($qar, "`, `", "(`", "`)");
        		$fieldcontents=convertToArray($qar, "', '", "('", "')");
            }		
        $asrowdata=array_combine($fieldorders,$fieldcontents);
        if (isset($asrowdata['link']))
        {
            if (trim($asrowdata['link'])!='') $asrowdata['message']=$asrowdata['message'].'<br /><a href="'.$asrowdata['link'].'">'.$asrowdata['link'].'</a>';
            unset($asrowdata['link']);
        }
		$oldsid=$asrowdata["sid"];
		$oldgid=$asrowdata["gid"];
        if  ($oldgid>0)
        {
            foreach ($substitutions as $subs) {
                if ($oldsid==$subs[0]) {$newsid=$subs[3];}
                if ($oldgid==$subs[1]) {$newgid=$subs[4];}
            }
        }
        else
        {
            $newgid=0;
        }

		$asrowdata["sid"]=$newsid;
		$asrowdata["gid"]=$newgid;
		unset($asrowdata["id"]);


        $newvalues=array_values($asrowdata);
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
        $asinsert = "insert INTO {$dbprefix}assessments (".implode(',',array_keys($asrowdata)).") VALUES (".implode(',',$newvalues).")"; 
		$result=$connect->Execute($asinsert) or safe_die ("Couldn't insert assessment<br />$asinsert<br />".$connect->ErrorMsg());

		unset($newgid);
	}
}

if (isset($quotaarray) && $quotaarray) {//ONLY DO THIS IF THERE ARE QUOTAS
    $count=0;
	foreach ($quotaarray as $qar) {
        
        $fieldorders=convertCSVRowToArray($quotaarray[0],',','"');
        $fieldcontents=convertCSVRowToArray($qar,',','"');
        if ($count==0) {$count++; continue;}
	
        $asrowdata=array_combine($fieldorders,$fieldcontents);

		$oldsid=$asrowdata["sid"];
		foreach ($substitutions as $subs) {
			if ($oldsid==$subs[0]) {$newsid=$subs[3];}
		}

		$asrowdata["sid"]=$newsid;
		$oldid = $asrowdata["id"];
		unset($asrowdata["id"]);
		$quotadata[]=$asrowdata; //For use later if needed

        $newvalues=array_values($asrowdata);
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly

        $asinsert = "insert INTO {$dbprefix}quota (".implode(',',array_keys($asrowdata)).") VALUES (".implode(',',$newvalues).")"; 
		$result=$connect->Execute($asinsert) or safe_die ("Couldn't insert quota<br />$asinsert<br />".$connect->ErrorMsg());
		$quotaids[] = array($oldid,$connect->Insert_ID(db_table_name_nq('quota'),"id"));

	}
}

if (isset($quotamembersarray) && $quotamembersarray) {//ONLY DO THIS IF THERE ARE QUOTA MEMBERS
    $count=0;
	foreach ($quotamembersarray as $qar) {
        
        $fieldorders  =convertCSVRowToArray($quotamembersarray[0],',','"');
        $fieldcontents=convertCSVRowToArray($qar,',','"');
        if ($count==0) {$count++; continue;}
	
        $asrowdata=array_combine($fieldorders,$fieldcontents);

		$oldsid=$asrowdata["sid"];
		$newqid="";
		$newquotaid="";
		$oldqid=$asrowdata['qid'];
		$oldquotaid=$asrowdata['quota_id'];

		foreach ($substitutions as $subs) {
			if ($oldsid==$subs[0]) {$newsid=$subs[3];}
			if ($oldqid==$subs[2]) {$newqid=$subs[5];}
		}

		foreach ($quotaids as $quotaid) {
			if ($oldquotaid==$quotaid[0]) {$newquotaid=$quotaid[1];}
		}
		
		$asrowdata["sid"]=$newsid;
		$asrowdata["qid"]=$newqid;
		$asrowdata["quota_id"]=$newquotaid;
		unset($asrowdata["id"]);

        $newvalues=array_values($asrowdata);
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly

        $asinsert = "insert INTO {$dbprefix}quota_members (".implode(',',array_keys($asrowdata)).") VALUES (".implode(',',$newvalues).")"; 
		$result=$connect->Execute($asinsert) or safe_die ("Couldn't insert quota<br />$asinsert<br />".$connect->ErrorMsg());

	}
}

if (isset($quotalsarray) && $quotalsarray) {//ONLY DO THIS IF THERE ARE QUOTA LANGUAGE SETTINGS
    $count=0;
	foreach ($quotalsarray as $qar) {
        
        $fieldorders  =convertCSVRowToArray($quotalsarray[0],',','"');
        $fieldcontents=convertCSVRowToArray($qar,',','"');
        if ($count==0) {$count++; continue;}
	
        $asrowdata=array_combine($fieldorders,$fieldcontents);

		$newquotaid="";
		$oldquotaid=$asrowdata['quotals_quota_id'];

		foreach ($quotaids as $quotaid) {
			if ($oldquotaid==$quotaid[0]) {$newquotaid=$quotaid[1];}
		}
		
		$asrowdata["quotals_quota_id"]=$newquotaid;
		unset($asrowdata["quotals_id"]);

        $newvalues=array_values($asrowdata);
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly

        $asinsert = "INSERT INTO {$dbprefix}quota_languagesettings (".implode(',',array_keys($asrowdata)).") VALUES (".implode(',',$newvalues).")"; 
		$result=$connect->Execute($asinsert) or safe_die ("Couldn't insert quota<br />$asinsert<br />".$connect->ErrorMsg());
	}
}

//if there are quotas, but no quotals, then we need to create default dummy for each quota (this handles exports from pre-language quota surveys)
if ($countquota > 0 && (!isset($countquotals) || $countquotals == 0)) {
	$i=0;
	$defaultsurveylanguage=isset($defaultsurveylanguage) ? $defaultsurveylanguage : "en";
	foreach($quotaids as $quotaid) {
	    $newquotaid=$quotaid[1];
		$asrowdata=array("quotals_quota_id" => $newquotaid,
						 "quotals_language" => $defaultsurveylanguage,
						 "quotals_name" => $quotadata[$i]["name"],
						 "quotals_message" => $clang->gT("Sorry your responses have exceeded a quota on this survey."),
						 "quotals_url" => "",
						 "quotals_urldescrip" => "");
		$i++;
	}
	$newvalues = array_values($asrowdata);
	$newvalues = array_map(array(&$connect, "qstr"),$newvalues);
	
    $asinsert = "INSERT INTO {$dbprefix}quota_languagesettings (".implode(',',array_keys($asrowdata)).") VALUES (".implode(',',$newvalues).")"; 
	$result=$connect->Execute($asinsert) or safe_die ("Couldn't insert quota<br />$asinsert<br />".$connect->ErrorMsg());
	$countquotals=$i;
}

if (isset($conditionsarray) && $conditionsarray) {//ONLY DO THIS IF THERE ARE CONDITIONS!
    $count='0';  
	foreach ($conditionsarray as $car) {
        if ($importversion>=111)
        {
            $fieldorders  =convertCSVRowToArray($conditionsarray[0],',','"');
            $fieldcontents=convertCSVRowToArray($car,',','"');
            if ($count==0) {$count++; continue;}
        }
        else
            {
        		$fieldorders=convertToArray($car, "`, `", "(`", "`)");
        		$fieldcontents=convertToArray($car, "', '", "('", "')");
            }
        $conditionrowdata=array_combine($fieldorders,$fieldcontents);

		$oldcid=$conditionrowdata["cid"];
		$oldqid=$conditionrowdata["qid"];
		$oldcfieldname=$conditionrowdata["cfieldname"];
		$oldcqid=$conditionrowdata["cqid"];
		$thisvalue=$conditionrowdata["value"];
		$newvalue=$thisvalue;
		$newcfieldname=$oldcfieldname;
		
		foreach ($substitutions as $subs) {
			if ($oldqid==$subs[2])  {$newqid=$subs[5];}
			if ($oldcqid==$subs[2]) {$newcqid=$subs[5];}
		}
        // Exception for conditions based on attributes
        if ($oldcqid==0) {$newcqid=0;}
        
		if (preg_match('/^@([0-9]+)X([0-9]+)X([^@]+)@/',$thisvalue,$targetcfieldname))
		{
			foreach ($substitutions as $subs) {
				if ($targetcfieldname[1]==$subs[0])  {$targetcfieldname[1]=$subs[3];}
				if ($targetcfieldname[2]==$subs[1])  {$targetcfieldname[2]=$subs[4];}
				if ($targetcfieldname[3]==$subs[2])  {$targetcfieldname[3]=$subs[5];}
			}
			$newvalue='@'.$targetcfieldname[1].'X'.$targetcfieldname[2].'X'.$targetcfieldname[3].'@';
		}
		foreach($fieldnames as $fns) {
			//if the $fns['oldcfieldname'] is not the same as $fns['oldfieldname'] then this is a multiple type question
			if ($fns['oldcfieldname'] == $fns['oldfieldname']) { //The normal method - non multiples
				if ($oldcfieldname==$fns['oldcfieldname']) {
					$newcfieldname=$fns['newcfieldname'];
				}
			} else {
				if ($oldcfieldname == $fns['oldcfieldname'] && $oldcfieldname.$thisvalue == $fns['oldfieldname']) {
					$newcfieldname=$fns['newcfieldname'];
				}
			}
		}
		if (!isset($newcfieldname)) {$newcfieldname="";}
		unset($conditionrowdata["cid"]);
		$conditionrowdata["qid"]=$newqid;
		$conditionrowdata["cfieldname"]=$newcfieldname;
		$conditionrowdata["value"]=$newvalue;

		
		if (isset($newcqid)) {
			$conditionrowdata["cqid"]=$newcqid;
            if (!isset($conditionrowdata["method"]) || trim($conditionrowdata["method"])=='') 
            {
                $conditionrowdata["method"]='==';
            }
            if (!isset($conditionrowdata["scenario"]) || trim($conditionrowdata["scenario"])=='') 
            {
                $conditionrowdata["scenario"]=1;
            }
            $newvalues=array_values($conditionrowdata);
            $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
            $conditioninsert = "insert INTO {$dbprefix}conditions (".implode(',',array_keys($conditionrowdata)).") VALUES (".implode(',',$newvalues).")"; 
			$result=$connect->Execute($conditioninsert) or safe_die ("Couldn't insert condition<br />$conditioninsert<br />".$connect->ErrorMsg());
		} else {
			$importsurvey .= "<font size=1>".sprintf($clang->gT("Condition for %d skipped (%d does not exist)"),$oldqid,$oldcqid)."</font><br />";
			if ($importingfrom != "http") echo sprintf($clang->gT("Condition for %d skipped (%d does not exist)"),$oldqid,$oldcqid)."\n";
		}
		unset($newcqid);
	}
}

// Translate INSERTANS codes
if (isset($fieldnames))
{
    transInsertAns($newsid,$oldsid,$fieldnames);
}




if ($importingfrom == "http")
{
	$importsurvey .= "<br />\n<div class='successheader'>".$clang->gT("Success")."</div><br /><br />\n";
	$importsurvey .= "<strong><u>".$clang->gT("Survey Import Summary")."</u></strong><br />\n";
	$importsurvey .= "<ul style=\"text-align:left;\">\n\t<li>".$clang->gT("Surveys").": $countsurveys</li>\n";
	if ($importversion>=111)
	    {
	    $importsurvey .= "\t<li>".$clang->gT("Languages").": $countlanguages</li>\n";
	    }
	$importsurvey .= "\t<li>".$clang->gT("Question groups").": $countgroups</li>\n";
	$importsurvey .= "\t<li>".$clang->gT("Questions").": $countquestions</li>\n";
	$importsurvey .= "\t<li>".$clang->gT("Answers").": $countanswers</li>\n";
	$importsurvey .= "\t<li>".$clang->gT("Conditions").": $countconditions</li>\n";
	$importsurvey .= "\t<li>".$clang->gT("Label Sets").": $countlabelsets</li>\n";
    if ($deniedcountlabelsets>0) 
    {
        $importsurvey .= "\t<li>".$clang->gT("Not imported Label Sets").": $deniedcountlabelsets ".$clang->gT("(Label Sets were not imported since you do not have the permission to create new label sets.)")."</li>\n"; 
    }
	$importsurvey .= "\t<li>".$clang->gT("Question Attributes").": $countquestion_attributes</li>\n";
	$importsurvey .= "\t<li>".$clang->gT("Assessments").": $countassessments</li>\n";
	$importsurvey .= "\t<li>".$clang->gT("Quotas").": $countquota ($countquotamembers ".$clang->gT("quota members")." ".$clang->gT("and")." $countquotals ".$clang->gT("quota language settings").")</li>\n</ul>\n";
	
	$importsurvey .= "<strong>".$clang->gT("Import of Survey is completed.")."</strong><br />\n"
			. "<a href='$scriptname?sid=$newsid'>".$clang->gT("Go to survey")."</a><br />\n";
	if ($importwarning != "") $importsurvey .= "<br /><strong>".$clang->gT("Warnings").":</strong><br /><ul style=\"text-align:left;\">" . $importwarning . "</ul><br />\n";
	$importsurvey .= "</div><br />\n";
	unlink($the_full_file_path);
	unset ($surveyid);  // Crazy but necessary because else the html script will search for user rights
}
else
{
	echo "\n".$clang->gT("Success")."\n\n";
	echo $clang->gT("Survey Import Summary")."\n";
	echo $clang->gT("Surveys").": $countsurveys\n";
	if ($importversion>=111)
	    {
	    echo $clang->gT("Languages").": $countlanguages\n";
	    }
	echo $clang->gT("Groups").": $countgroups\n";
	echo $clang->gT("Questions").": $countquestions\n";
	echo $clang->gT("Answers").": $countanswers\n";
	echo $clang->gT("Conditions").": $countconditions\n";
	echo $clang->gT("Label Sets").": $countlabelsets\n";
    if ($deniedcountlabelsets>0) echo $clang->gT("Not imported Label Sets").": $deniedcountlabelsets (".$clang->gT("(Label Sets were not imported since you do not have the permission to create new label sets.)");
	echo $clang->gT("Question Attributes").": $countquestion_attributes\n";
	echo $clang->gT("Assessments").": $countassessments\n\n";
	
	echo $clang->gT("Import of Survey is completed.")."\n";
	if ($importwarning != "") echo "\n".$clang->gT("Warnings").":\n" . $importwarning . "\n";
	$surveyid=$newsid;

}

