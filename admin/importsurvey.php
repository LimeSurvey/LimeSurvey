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
//importsurvey.php should be called from cmdline_importsurvey.php or http_importsurvey.php, they set the $importingfrom variable
if ((!isset($importingfrom) && !isset($copyfunction)) || isset($_REQUEST['importingfrom']))
{
    die("Cannot run this script directly");
}
require_once('import_functions.php');

if (!isset($copyfunction))
{
    $sFullFilepath=$the_full_file_path;
    $aPathInfo = pathinfo($sFullFilepath);
    $sExtension = $aPathInfo['extension'];
}
                  
$bImportFailed=false;  
if (isset($sExtension) && strtolower($sExtension)=='csv')
{
    $aImportResults=CSVImportSurvey($sFullFilepath);
}
elseif (isset($sExtension) && strtolower($sExtension)=='lss')
{
    $aImportResults=XMLImportSurvey($sFullFilepath,null,null, null,(isset($_POST['translinksfields'])));
} elseif (isset($copyfunction))
{
    $aImportResults=XMLImportSurvey('',$copysurveydata,$sNewSurveyName);
}
else
{
    $bImportFailed=true;
}

// Create old fieldnames
                 
                
if ((!$bImportFailed && isset($importingfrom) && $importingfrom == "http") || isset($copyfunction))
{
    $importsurvey .= "<br />\n<div class='successheader'>".$clang->gT("Success")."</div><br /><br />\n";
    if (isset($copyfunction))
    {
        $importsurvey .= "<strong><u>".$clang->gT("Survey copy summary")."</u></strong><br />\n";        
    } else
    {
        $importsurvey .= "<strong><u>".$clang->gT("Survey import summary")."</u></strong><br />\n";        
    }    
    $importsurvey .= "<ul style=\"text-align:left;\">\n\t<li>".$clang->gT("Surveys").": {$aImportResults['surveys']}</li>\n";
    $importsurvey .= "\t<li>".$clang->gT("Languages").": {$aImportResults['languages']}</li>\n";
    $importsurvey .= "\t<li>".$clang->gT("Question groups").": {$aImportResults['groups']}</li>\n";
    $importsurvey .= "\t<li>".$clang->gT("Questions").": {$aImportResults['questions']}</li>\n";
    $importsurvey .= "\t<li>".$clang->gT("Answers").": {$aImportResults['answers']}</li>\n";
    if (isset($aImportResults['subquestions']))
    {
        $importsurvey .= "\t<li>".$clang->gT("Subquestions").": {$aImportResults['subquestions']}</li>\n";     
    }
    if (isset($aImportResults['defaultvalues']))
    {
        $importsurvey .= "\t<li>".$clang->gT("Default answers").": {$aImportResults['defaultvalues']}</li>\n";     
    }
    if (isset($aImportResults['conditions']))
    {
<<<<<<< HEAD
        $importsurvey .= "\t<li>".$clang->gT("Conditions").": {$aImportResults['conditions']}</li>\n";     
    }
    if (isset($aImportResults['labelsets']))
    {
        $importsurvey .= "\t<li>".$clang->gT("Label sets").": {$aImportResults['labelsets']}</li>\n";
    }
    if (isset($aImportResults['deniedcountls']) && $aImportResults['deniedcountls']>0)
    {
        $importsurvey .= "\t<li>".$clang->gT("Not imported label sets").": {$aImportResults['deniedcountls']} ".$clang->gT("(Label sets were not imported since you do not have the permission to create new label sets.)")."</li>\n";
    }
    $importsurvey .= "\t<li>".$clang->gT("Question attributes").": {$aImportResults['question_attributes']}</li>\n";
    $importsurvey .= "\t<li>".$clang->gT("Assessments").": {$aImportResults['assessments']}</li>\n";
    $importsurvey .= "\t<li>".$clang->gT("Quotas").": {$aImportResults['quota']} ({$aImportResults['quotamembers']} ".$clang->gT("quota members")." ".$clang->gT("and")." {$aImportResults['quotals']} ".$clang->gT("quota language settings").")</li>\n</ul><br />\n";
    if (count($aImportResults['importwarnings'])>0) 
    {
        $importsurvey .= "<div class='warningheader'>".$clang->gT("Warnings").":</div><ul style=\"text-align:left;\">";
        foreach ($aImportResults['importwarnings'] as $warning)
=======
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
         $newlanguage='no';
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
    $surveylsrowdata['surveyls_email_invite_subj']=$surveyrowdata['email_invite_subj'];
    $surveylsrowdata['surveyls_email_invite']=$surveyrowdata['email_invite'];
    $surveylsrowdata['surveyls_email_remind_subj']=$surveyrowdata['email_remind_subj'];
    $surveylsrowdata['surveyls_email_remind']=$surveyrowdata['email_remind'];
    $surveylsrowdata['surveyls_email_register_subj']=$surveyrowdata['email_register_subj'];
    $surveylsrowdata['surveyls_email_register']=$surveyrowdata['email_register'];
    $surveylsrowdata['surveyls_email_confirm_subj']=$surveyrowdata['email_confirm_subj'];
    $surveylsrowdata['surveyls_email_confirm']=$surveyrowdata['email_confirm'];
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
    // import the survey language-specific settings
    $values=array_values($surveylsrowdata);
    $values=array_map(array(&$connect, "qstr"),$values); // quote everything accordingly
    $insert = "insert INTO {$dbprefix}surveys_languagesettings (".implode(',',array_keys($surveylsrowdata)).") VALUES (".implode(',',$values).")"; //handle db prefix
    $iresult = $connect->Execute($insert) or die("<br />".$clang->gT("Import of this survey file failed")."<br />\n<font size='1'>[$insert]</font><hr>$surveyarray[0]<br /><br />\n" . $connect->ErrorMsg() . "</body>\n</html>");



    }



if (isset($surveyrowdata['datecreated'])) {$surveyrowdata['datecreated']=$connect->BindTimeStamp($surveyrowdata['datecreated']);}
if (isset($surveyrowdata['expires']) && $surveyrowdata['expires'] !='') {$surveyrowdata['expires']=$connect->BindTimeStamp($surveyrowdata['expires']);}
if (!isset($surveyrowdata['datecreated']) || $surveyrowdata['datecreated']=='' || $surveyrowdata['datecreated']=='null') {$surveyrowdata['datecreated']=$connect->BindTimeStamp(date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust));}

$values=array_values($surveyrowdata);
$values=array_map(array(&$connect, "qstr"),$values); // quote everything accordingly
$insert = "INSERT INTO {$dbprefix}surveys (".implode(',',array_keys($surveyrowdata)).") VALUES (".implode(',',$values).")"; //handle db prefix
$iresult = $connect->Execute($insert) or die("<br />".$clang->gT("Import of this survey file failed")."<br />\n<font size='1'>[$insert]</font><hr>$surveyarray[0]<br /><br />\n" . $connect->ErrorMsg());

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
        $surveylsrowdata['surveyls_survey_id']=$newsid;     
        $newvalues=array_values($surveylsrowdata);
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
        $lsainsert = "INSERT INTO {$dbprefix}surveys_languagesettings (".implode(',',array_keys($surveylsrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
		$lsiresult=$connect->Execute($lsainsert) or die("<br />".$clang->gT("Import of this survey file failed")."<br />\n<font size='1'>[$lsainsert]</font><hr><br />\n" . $connect->ErrorMsg() );
	}	
		
}


// DO SURVEY_RIGHTS
$isrquery = "INSERT INTO {$dbprefix}surveys_rights VALUES($newsid,".$_SESSION['loginID'].",1,1,1,1,1,1)";
@$isrresult = $connect->Execute($isrquery);



//DO ANY LABELSETS FIRST, SO WE CAN KNOW WHAT THEIR NEW LID IS FOR THE QUESTIONS
if (isset($labelsetsarray) && $labelsetsarray) {
	$csarray=buildLabelsetCSArray();   // build checksums over all existing labelsets
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
		$newlid=$connect->Insert_ID();

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
				$labellid=$labelrowdata['lid'];
		        if ($importversion<=100)
                {
                $labelrowdata['language']=$newlanguage;
                } 				
				if ($labellid == $oldlid) {
					$labelrowdata['lid']=$newlid;
                    $newvalues=array_values($labelrowdata);
                    $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                    $lainsert = "insert INTO {$dbprefix}labels (".implode(',',array_keys($labelrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
					$liresult=$connect->Execute($lainsert);
				}
			}
		}

		//CHECK FOR DUPLICATE LABELSETS
		$thisset="";
		$query2 = "SELECT code, title, sortorder, language
                   FROM {$dbprefix}labels
                   WHERE lid=".$newlid."
                   ORDER BY sortorder, code";
		$result2 = db_execute_num($query2) or die("Died querying labelset $lid<br />$query2<br />".$connect->ErrorMsg());
		while($row2=$result2->FetchRow())
		{
			$thisset .= implode('.', $row2);
		} // while
		$newcs=dechex(crc32($thisset)*1);
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
		if (isset($lsmatch))
		{
			//There is a matching labelset. So, we will delete this one and refer
			//to the matched one.
			$query = "DELETE FROM {$dbprefix}labels WHERE lid=$newlid";
			$result=$connect->Execute($query) or die("Couldn't delete labels<br />$query<br />".$connect->ErrorMsg());
			$query = "DELETE FROM {$dbprefix}labelsets WHERE lid=$newlid";
			$result=$connect->Execute($query) or die("Couldn't delete labelset<br />$query<br />".$connect->ErrorMsg());
			$newlid=$lsmatch;
		}
		else
		{
			//There isn't a matching labelset, add this checksum to the $csarray array
			$csarray[$newlid]=$newcs;
		}
		//END CHECK FOR DUPLICATES
		$labelreplacements[]=array($oldlid, $newlid);
	}
}

$importwarning = "";	// used to save the warnings while processing questions
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
			$importsurvey .= "<br />\n<font color='red'><strong>".$clang->gT("Error")."</strong></font>"
			."<br />\nA group in the sql file does not come from the same Survey. Import of survey stopped.<br /><br />\n";
			if ($importingfrom != "http") echo $clang->gT("Error").": A group in the sql file does not come from the same Survey. Import of survey stopped.\n";
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
        
        $newvalues=array_values($grouprowdata);
        
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
        $ginsert = "insert INTO {$dbprefix}groups (".implode(',',array_keys($grouprowdata)).") VALUES (".implode(',',$newvalues).")"; 
		$gres = $connect->Execute($ginsert) or die("<strong>".$clang->gT("Error")."</strong> Failed to insert group<br />\n$ginsert<br />\n".$connect->ErrorMsg()."</body>\n</html>");
		//GET NEW GID
		if ($newgroup) {$newgid=$connect->Insert_ID();}

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

								if (!array_key_exists($questionrowdata["type"], $qtypes))
                {
                	$questionrowdata["type"] = strtoupper($questionrowdata["type"]);
                	if (!array_key_exists($questionrowdata["type"], $qtypes))
                	{
                		$importwarning .= "<li>" . $clang->gT("Question") . " \"{$questionrowdata["title"]} - {$questionrowdata["question"]}\" " . $clang->gT("was NOT imported, because the question type is not know") . "</li>";
                		$countquestions--;
                		continue;
                	}
                	else	// the upper case worked well
                	{
                		$importwarning .= "<li>" . $clang->gT("Question") . " \"{$questionrowdata["title"]} - {$questionrowdata["question"]}\" " . $clang->gT("was imported, but the type was set to \"" . $qtypes[$questionrowdata["type"]]  . "\", because is the closest one") . "</li>";
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

                    // Now we will fix up the label id 
					$type = $questionrowdata["type"]; //Get the type
					if ($type == "F" || $type == "H" || $type == "W" || $type == "Z") 
                    {//IF this is a flexible label array, update the lid entry
						if (isset($labelreplacements)) {
							foreach ($labelreplacements as $lrp) {
								if ($lrp[0] == $questionrowdata["lid"]) {
									$questionrowdata["lid"]=$lrp[1];
								}
							}
						}
                    }
                    if (!isset($questionrowdata["question_order"]) || $questionrowdata["question_order"]=='') {$questionrowdata["question_order"]=0;} 
					$other = $questionrowdata["other"]; //Get 'other' field value
                    $newvalues=array_values($questionrowdata);
                    $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                    $qinsert = "insert INTO {$dbprefix}questions (".implode(',',array_keys($questionrowdata)).") VALUES (".implode(',',$newvalues).")"; 
					$qres = $connect->Execute($qinsert) or die ("<strong>".$clang->gT("Error")."</strong> Failed to insert question<br />\n$qinsert<br />\n".$connect->ErrorMsg()."</body>\n</html>");
		            if ($newquestion) {$newqid=$connect->Insert_ID();}
					
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
                                $newvalues=array_values($answerrowdata);
                                $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                                $ainsert = "insert INTO {$dbprefix}answers (".implode(',',array_keys($answerrowdata)).") VALUES (".implode(',',$newvalues).")"; 
								$ares = $connect->Execute($ainsert) or die ("<strong>".$clang->gT("Error")."</strong> Failed to insert answer<br />\n$ainsert<br />\n".$connect->ErrorMsg()."</body>\n</html>");
								
								if ($type == "M" || $type == "P") {
									$fieldnames[]=array("oldcfieldname"=>$oldsid."X".$oldgid."X".$oldqid,
									"newcfieldname"=>$newsid."X".$newgid."X".$newqid,
									"oldfieldname"=>$oldsid."X".$oldgid."X".$oldqid.$code,
									"newfieldname"=>$newsid."X".$newgid."X".$newqid.$code);
									if ($type == "P") {
										$fieldnames[]=array("oldcfieldname"=>$oldsid."X".$oldgid."X".$oldqid."comment",
										"newcfieldname"=>$newsid."X".$newgid."X".$newqid.$code."comment",
										"oldfieldname"=>$oldsid."X".$oldgid."X".$oldqid.$code."comment",
										"newfieldname"=>$newsid."X".$newgid."X".$newqid.$code."comment");
									}
								}
								elseif ($type == "A" || $type == "B" || $type == "C" || $type == "F" || $type == "H") {
									$fieldnames[]=array("oldcfieldname"=>$oldsid."X".$oldgid."X".$oldqid.$code,
									"newcfieldname"=>$newsid."X".$newgid."X".$newqid.$code,
									"oldfieldname"=>$oldsid."X".$oldgid."X".$oldqid.$code,
									"newfieldname"=>$newsid."X".$newgid."X".$newqid.$code);
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

if ($importversion<=100)  
   {
   // Fix sortorder of the groups from older survey version
   fixsortorderGroups();
   //... and for the questions inside the groups
   // get all group ids and fix questions inside each group
   $gquery = "SELECT gid FROM {$dbprefix}groups where sid=$newsid group by gid ORDER BY gid"; //Get last question added (finds new qid)
   $gres = db_execute_assoc($gquery);
   while ($grow = $gres->FetchRow()) 
        {
        fixsortorderQuestions(0,$grow['gid']);
        }
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
		$result=$connect->Execute($qainsert) or die ("Couldn't insert question_attribute<br />$qainsert<br />".$connect->ErrorMsg());
	}
}

if (isset($assessmentsarray) && $assessmentsarray) {//ONLY DO THIS IF THERE ARE QUESTION_ATTRIBUES
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
		$oldsid=$asrowdata["sid"];
		$oldgid=$asrowdata["gid"];
		foreach ($substitutions as $subs) {
			if ($oldsid==$subs[0]) {$newsid=$subs[3];}
			if ($oldgid==$subs[1]) {$newgid=$subs[4];}
		}

		$asrowdata["sid"]=$newsid;
		$asrowdata["gid"]=$newgid;
		unset($asrowdata["id"]);


        $newvalues=array_values($asrowdata);
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
        $asinsert = "insert INTO {$dbprefix}assessments (".implode(',',array_keys($asrowdata)).") VALUES (".implode(',',$newvalues).")"; 
		$result=$connect->Execute($asinsert) or die ("Couldn't insert assessment<br />$asinsert<br />".$connect->ErrorMsg());

		unset($newgid);
	}
}

if (isset($conditionsarray) && $conditionsarray) {//ONLY DO THIS IF THERE ARE CONDITIONS!
    $count='0';  
	foreach ($conditionsarray as $car) {
        if ($importversion>=111)
>>>>>>> refs/heads/stable_plus
        {
            $importsurvey .='<li>'.$warning.'</li>';
        }
<<<<<<< HEAD
        $importsurvey .= "</ul><br />\n";
    }
    if (isset($copyfunction))
    {
        $importsurvey .= "<strong>".$clang->gT("Copy of survey is completed.")."</strong><br />\n"
        . "<a href='$scriptname?sid={$aImportResults['newsid']}'>".$clang->gT("Go to survey")."</a><br />\n";        
    } else
    {
        $importsurvey .= "<strong>".$clang->gT("Import of Survey is completed.")."</strong><br />\n"
        . "<a href='$scriptname?sid={$aImportResults['newsid']}'>".$clang->gT("Go to survey")."</a><br />\n";        
    }
    $importsurvey .= "</div><br />\n";
    if (!isset($copyfunction))
    {
        unlink($sFullFilepath);    
    }
=======
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
		
		foreach ($substitutions as $subs) {
			if ($oldqid==$subs[2])  {$newqid=$subs[5];}
			if ($oldcqid==$subs[2]) {$newcqid=$subs[5];}
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
		
		if (isset($newcqid)) {
			$conditionrowdata["cqid"]=$newcqid;

            $newvalues=array_values($conditionrowdata);
            $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
            $conditioninsert = "insert INTO {$dbprefix}conditions (".implode(',',array_keys($conditionrowdata)).") VALUES (".implode(',',$newvalues).")"; 
			$result=$connect->Execute($conditioninsert) or die ("Couldn't insert condition<br />$conditioninsert<br />".$connect->ErrorMsg());
		} else {
			$importsurvey .= "<font size=1>Condition for $oldqid skipped ($oldcqid does not exist)</font><br />";
			if ($importingfrom != "http") echo "Condition for $oldqid skipped ($oldcqid does not exist)\n";
		}
		unset($newcqid);
	}
>>>>>>> refs/heads/stable_plus
}
elseif (isset($bImportFailed) && $bImportFailed==true)
{
    echo "\n".$clang->gT("Error")."\n\n";
    echo $clang->gT("Import failed. You specified an invalid file.")."\n";
    
}
else
{
<<<<<<< HEAD
    echo "\n".$clang->gT("Success")."\n\n";
    echo $clang->gT("Survey import summary")."\n";
    echo $clang->gT("Surveys").": {$aImportResults['surveys']}\n";
    if ($aImportResults['importversion']>=111)
    {
        echo $clang->gT("Languages").": {$aImportResults['languages']}\n";
    }
    echo $clang->gT("Groups").": {$aImportResults['groups']}\n";
    echo $clang->gT("Questions").": {$aImportResults['questions']}\n";
    echo $clang->gT("Answers").": {$aImportResults['answers']}\n";
    if (isset($aImportResults['subquestions']))
    {
        echo $clang->gT("Subquestions").": {$aImportResults['subquestions']}\n";      
    }
    if (isset($aImportResults['defaultvalues']))
    {
        echo $clang->gT("Default answers").": {$aImportResults['defaultvalues']}\n";     
    }
    if (isset($aImportResults['conditions']))
    {
        echo $clang->gT("Conditions").": {$aImportResults['conditions']}\n";       
    }
    if (isset($aImportResults['labelsets']))
    {
        echo $clang->gT("Label sets").": {$aImportResults['labelsets']}\n";
    }
    if ($importresults['deniedcountls']>0) echo $clang->gT("Not imported label sets").": {$importresults['deniedcountls']} (".$clang->gT("(Label sets were not imported since you do not have the permission to create new label sets.)");
    echo $clang->gT("Question Attributes").": {$aImportResults['question_attributes']}\n";
    echo $clang->gT("Assessments").": {$aImportResults['assessments']}\n\n";

    echo $clang->gT("Import of Survey is completed.")."\n";
    if ($aImportResults['importwarnings'] != "") echo "\n".$clang->gT("Warnings").":\n" . $aImportResults['importwarnings'] . "\n";
    $surveyid=$newsid;

}
=======
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
	echo $clang->gT("Label Sets").": $countlabelsets (".$clang->gT("Labels").": $countlabels)\n";
	echo $clang->gT("Question Attributes").": $countquestion_attributes\n";
	echo $clang->gT("Assessments").": $countassessments\n\n";
	
	echo $clang->gT("Import of Survey is completed.")."\n";
	if ($importwarning != "") echo "\n".$clang->gT("Warnings").":\n" . $importwarning . "\n";
	$surveyid=$newsid;

}
	
>>>>>>> refs/heads/stable_plus


