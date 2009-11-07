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
include_once("login_check.php");

// A FILE TO IMPORT A DUMPED question FILE, AND CREATE A NEW SURVEY

$importquestion = "<br /><table width='100%' align='center'><tr><td>\n"
."<table class='alertbox' >\n"
."\t<tr><td colspan='2' height='4'><strong>"
.$clang->gT("Import Question")."</strong></td></tr>\n"
."\t<tr><td align='center'>\n";

$the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];

if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
{
	$importquestion .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
    $importquestion .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$tempdir)."<br /><br />\n"
	."<input type='submit' value='"
	.$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n"
	."</td></tr></table>\n";
	unlink($the_full_file_path);
	return;
}

// validate that we have a SID and GID
if (!returnglobal('sid'))
{
    $importquestion .= $clang->gT("No SID (Survey) has been provided. Cannot import question.")."<br /><br />\n"
    ."<input type='submit' value='"
    .$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n"
    ."</td></tr></table>\n";
    unlink($the_full_file_path);
    return;
}
else
{
	$postsid=returnglobal('sid');
}

if (!returnglobal('gid'))
{
    $importquestion .= $clang->gT("No GID (Group) has been provided. Cannot import question")."<br /><br />\n"
    ."</td></tr></table>\n";
    unlink($the_full_file_path);
    return;
}
else
{
	$postgid=returnglobal('gid');
}

// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY
$importquestion .= "<strong><font class='successtitle'>".$clang->gT("Success")."</font></strong><br />\n"
.$clang->gT("File upload succeeded.")."<br /><br />\n"
.$clang->gT("Reading file..")."\n";
$handle = fopen($the_full_file_path, "r");
while (!feof($handle))
{
	$buffer = fgets($handle); //To allow for very long survey welcomes (up to 10k)
	$bigarray[] = $buffer;
}
fclose($handle);

// Now we try to determine the dataformat of the survey file.
if ((substr($bigarray[1], 0, 24) == "# SURVEYOR QUESTION DUMP")&& (substr($bigarray[4], 0, 29) == "# http://www.phpsurveyor.org/"))
{
    $importversion = 100;  // version 1.0 file
}
elseif 
   ((substr($bigarray[1], 0, 24) == "# SURVEYOR QUESTION DUMP")&& (substr($bigarray[4], 0, 37) == "# http://phpsurveyor.sourceforge.net/"))
{
    $importversion = 99;  // Version 0.99 file or older - carries a different URL
}
elseif 
   (substr($bigarray[0], 0, 26) == "# LimeSurvey Question Dump" || substr($bigarray[0], 0, 27) == "# PHPSurveyor Question Dump")
    {  // Wow.. this seems to be a >1.0 version file - these files carry the version information to read in line two
      $importversion=substr($bigarray[1], 12, 3);
    }
else    // unknown file - show error message
  {
      $importquestion .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
      $importquestion .= $clang->gT("This file is not a LimeSurvey question file. Import failed.")."<br /><br />\n";
      $importquestion .= "</font></td></tr></table>\n";
      $importquestion .= "</body>\n</html>\n";
      unlink($the_full_file_path);
      return;
  }

for ($i=0; $i<9; $i++) //skipping the first lines that are not needed
{
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
	if ($i<$stoppoint-2) {$questionarray[] = $bigarray[$i];}
	unset($bigarray[$i]);
}
$bigarray = array_values($bigarray);

//ANSWERS
if (array_search("# LABELSETS TABLE\n", $bigarray))
{
	$stoppoint = array_search("# LABELSETS TABLE\n", $bigarray);
}
elseif (array_search("# LABELSETS TABLE\r\n", $bigarray))
{
	$stoppoint = array_search("# LABELSETS TABLE\r\n", $bigarray);
}
else
{
	$stoppoint = count($bigarray)-1;
}
for ($i=0; $i<=$stoppoint+1; $i++)
{
	if ($i<$stoppoint-2) {$answerarray[] = str_replace("`default`", "`default_value`", $bigarray[$i]);}
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

//Question_attributes
if (!isset($noconditions) || $noconditions != "Y")
{
	$stoppoint = count($bigarray);
	for ($i=0; $i<=$stoppoint+1; $i++)
	{
		if ($i<$stoppoint-1) {$question_attributesarray[] = $bigarray[$i];}
		unset($bigarray[$i]);
	}
}
$bigarray = array_values($bigarray);

if (isset($questionarray)) {$countquestions = count($questionarray)-1;}  else {$countquestions=0;}
if (isset($answerarray)) 
    {
        $answerfieldnames=convertCSVRowToArray($answerarray[0],',','"');
        unset($answerarray[0]);
        $countanswers = count($answerarray);
    }  
	  else {$countanswers=0;}
if (isset($labelsetsarray)) {$countlabelsets = count($labelsetsarray)-1;}  else {$countlabelsets=0;}
if (isset($labelsarray)) {$countlabels = count($labelsarray)-1;}  else {$countlabels=0;}
if (isset($question_attributesarray)) {$countquestion_attributes = count($question_attributesarray)-1;} else {$countquestion_attributes=0;}

$languagesSupported = array();  // this array will keep all the languages supported for the survey

// Let's check that imported objects support at least the survey's baselang
$langcode = GetBaseLanguageFromSurveyID($postsid);

$languagesSupported[$langcode] = 1;     // adds the base language to the list of supported languages

if ($countquestions > 0)
{
	$questionfieldnames = convertCSVRowToArray($questionarray[0],',','"');
	$langfieldnum = array_search("language", $questionfieldnames);
	$qidfieldnum = array_search("qid", $questionfieldnames);
	$questionssupportbaselang = bDoesImportarraySupportsLanguage($questionarray,Array($qidfieldnum), $langfieldnum,$langcode,true);
	if (!$questionssupportbaselang)
	{
		$importquestion .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n"
		.$clang->gT("You can't import a question which doesn't support the current survey's base language")."<br /><br />\n"
		."</td></tr></table>\n";
		unlink($the_full_file_path);
		return;
	}
}

foreach (GetAdditionalLanguagesFromSurveyID($postsid) as $language)
{
    $languagesSupported[$language] = 1;
}

// Let's assume that if the questions do support tye baselang
// Then the answers do support it as well.
// ==> So the following section is commented for now
//if ($countanswers > 0)
//{
//	$langfieldnum = array_search("language", $answerfieldnames);
//	$answercodefilednum1 =  array_search("qid", $answerfieldnames);
//	$answercodefilednum2 =  array_search("code", $answerfieldnames);
//	$answercodekeysarr = Array($answercodefilednum1,$answercodefilednum2);
//	$answerssupportbaselang = bDoesImportarraySupportsLanguage($answerarray,$answercodekeysarr,$langfieldnum,$langcode);
//	if (!$answerssupportbaselang)
//	{
//		$importquestion .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n"
//		.$clang->gT("You can't import answers which don't support current survey's base language")."<br /><br />\n"
//		."</td></tr></table>\n";
//		return;
//	}
//	
//}

if ($countlabelsets > 0)
{
	$labelsetfieldname = convertCSVRowToArray($labelsetsarray[0],',','"');
	$langfieldnum = array_search("languages", $labelsetfieldname);
	$lidfilednum =  array_search("lid", $labelsetfieldname);
	$labelsetssupportbaselang = bDoesImportarraySupportsLanguage($labelsetsarray,Array($lidfilednum),$langfieldnum,$langcode,true);
	if (!$labelsetssupportbaselang)
	{
		$importquestion .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n"
		.$clang->gT("You can't import label sets which don't support the current survey's base language")."<br /><br />\n"
		."</td></tr></table>\n";
		unlink($the_full_file_path);
		return;
	}
}
// I assume that if a labelset supports the survey's baselang,
// then it's labels do support it as well

// GET SURVEY AND GROUP DETAILS
$surveyid=$postsid;
$gid=$postgid;
$newsid=$surveyid;
$newgid=$gid;

//DO ANY LABELSETS FIRST, SO WE CAN KNOW WHAT THEIR NEW LID IS FOR THE QUESTIONS
if (isset($labelsetsarray) && $labelsetsarray) {
    $csarray=buildLabelSetCheckSumArray();   // build checksums over all existing labelsets
    $count=0;
    foreach ($labelsetsarray as $lsa) {
        $fieldorders  =convertCSVRowToArray($labelsetsarray[0],',','"');
        $fieldcontents=convertCSVRowToArray($lsa,',','"');
        if ($count==0) {$count++; continue;}

        $labelsetrowdata=array_combine($fieldorders,$fieldcontents);
        
        // Save old labelid
        $oldlid=$labelsetrowdata['lid'];
        // set the new language
        unset($labelsetrowdata['lid']);
        $newvalues=array_values($labelsetrowdata);
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
        $lsainsert = "INSERT INTO {$dbprefix}labelsets (".implode(',',array_keys($labelsetrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
        $lsiresult=$connect->Execute($lsainsert);
        
        // Get the new insert id for the labels inside this labelset
        $newlid=$connect->Insert_ID("{$dbprefix}labelsets","lid");

        if ($labelsarray) {
            $count=0;
            foreach ($labelsarray as $la) {
                $lfieldorders  =convertCSVRowToArray($labelsarray[0],',','"');
                $lfieldcontents=convertCSVRowToArray($la,',','"');
                if ($count==0) {$count++; continue;}

                // Combine into one array with keys and values since its easier to handle
                 $labelrowdata=array_combine($lfieldorders,$lfieldcontents);
                $labellid=$labelrowdata['lid'];
                if ($importversion<=132)
                {
                   $labelrowdata["assessment_value"]=(int)$labelrowdata["code"];
                }
                
                if ($labellid == $oldlid) {
                    $labelrowdata['lid']=$newlid;

			        // translate internal links
			        $labelrowdata['title']=translink('label', $oldlid, $newlid, $labelrowdata['title']);

                    $newvalues=array_values($labelrowdata);
                    $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                    $lainsert = "INSERT INTO {$dbprefix}labels (".implode(',',array_keys($labelrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
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
        if (isset($lsmatch))
        {
            //There is a matching labelset. So, we will delete this one and refer
            //to the matched one.
            $query = "DELETE FROM {$dbprefix}labels WHERE lid=$newlid";
            $result=$connect->Execute($query) or safe_die("Couldn't delete labels<br />$query<br />".$connect->ErrorMsg());
            $query = "DELETE FROM {$dbprefix}labelsets WHERE lid=$newlid";
            $result=$connect->Execute($query) or safe_die("Couldn't delete labelset<br />$query<br />".$connect->ErrorMsg());
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



// QUESTIONS, THEN ANSWERS FOR QUESTIONS IN A NESTED FORMAT!
if (isset($questionarray) && $questionarray) {
    $qafieldorders=convertCSVRowToArray($questionarray[0],',','"');
    unset($questionarray[0]);

    //Assuming we will only import one question at a time we will now find out the maximum question order in this group 
    //and save it for later
    $qmaxqo = "SELECT MAX(question_order) AS maxqo FROM ".db_table_name('questions')." WHERE sid=$newsid AND gid=$newgid";
    $qres = db_execute_assoc($qmaxqo) or safe_die ($clang->gT("Error").": Failed to find out maximum question order value<br />\n$qmaxqo<br />\n".$connect->ErrorMsg());
    $qrow=$qres->FetchRow();
    $newquestionorder=$qrow['maxqo']+1;

	foreach ($questionarray as $qa) {
        $qacfieldcontents=convertCSVRowToArray($qa,',','"');
		$newfieldcontents=$qacfieldcontents;
    	$questionrowdata=array_combine($qafieldorders,$qacfieldcontents);
        if (isset($languagesSupported[$questionrowdata["language"]]))
        {
		    $oldqid = $questionrowdata['qid'];
		    $oldsid = $questionrowdata['sid'];
		    $oldgid = $questionrowdata['gid'];

    	    // Remove qid field if there is no newqid; and set it to newqid if it's set
            if (!isset($newqid))
		        unset($questionrowdata['qid']);
            else
                $questionrowdata['qid'] = $newqid;
                
		    $questionrowdata["sid"] = $newsid;
		    $questionrowdata["gid"] = $newgid;
            $questionrowdata["question_order"] = $newquestionorder;

            
            // Now we will fix up the label id 
		    $type = $questionrowdata["type"]; //Get the type
			if ($type == "F" || $type == "H" || $type == "W" || 
			    $type == "Z" || $type == "1" || $type == ":" ||
				$type == ";" ) 
                {//IF this is a flexible label array, update the lid entry
			    if (isset($labelreplacements)) {
				    foreach ($labelreplacements as $lrp) {
					    if ($lrp[0] == $questionrowdata["lid"]) {
						    $questionrowdata["lid"]=$lrp[1];
					       }
                        if ($lrp[0] == $questionrowdata["lid1"]) {
                            $questionrowdata["lid1"]=$lrp[1];
                           }
				        }
			         }
                }
		    $other = $questionrowdata["other"]; //Get 'other' field value
		    $oldlid = $questionrowdata["lid"];
            $questionrowdata=array_map('convertCsvreturn2return', $questionrowdata);

		// translate internal links
		$questionrowdata['title']=translink('survey', $oldsid, $newsid, $questionrowdata['title']);
		$questionrowdata['question']=translink('survey', $oldsid, $newsid, $questionrowdata['question']);
		$questionrowdata['help']=translink('survey', $oldsid, $newsid, $questionrowdata['help']);

            $newvalues=array_values($questionrowdata);
            $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
            $qinsert = "INSERT INTO {$dbprefix}questions (".implode(',',array_keys($questionrowdata)).") VALUES (".implode(',',$newvalues).")"; 
		    $qres = $connect->Execute($qinsert) or safe_die ($clang->gT("Error").": Failed to insert question<br />\n$qinsert<br />\n".$connect->ErrorMsg());

            // set the newqid only if is not set
            if (!isset($newqid))
		        $newqid=$connect->Insert_ID("{$dbprefix}questions","qid");
        }
    }

    //NOW DO ANSWERS FOR THIS QID - Is called just once and only if there was a question
    if (isset($answerarray) && $answerarray) {
        foreach ($answerarray as $aa) {
            $answerfieldcontents=convertCSVRowToArray($aa,',','"');
            $answerrowdata=array_combine($answerfieldnames,$answerfieldcontents);
            if ($answerrowdata===false)
            {
              $importquestion.='<br />'.$clang->gT("Faulty line in import - fields and data don't match").":".implode(',',$answerfieldcontents);
            }
            if (isset($languagesSupported[$answerrowdata["language"]]))
            {
                $code=$answerrowdata["code"];
                $thisqid=$answerrowdata["qid"];
                $answerrowdata["qid"]=$newqid;

			    // translate internal links
			    $answerrowdata['answer']=translink('survey', $oldsid, $newsid, $answerrowdata['answer']);
                if ($importversion<=132)
                {
                   $answerrowdata["assessment_value"]=(int)$answerrowdata["code"];
                }
                $newvalues=array_values($answerrowdata);
                $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                $ainsert = "INSERT INTO {$dbprefix}answers (".implode(',',array_keys($answerrowdata)).") VALUES (".implode(',',$newvalues).")"; 
                $ares = $connect->Execute($ainsert) or safe_die ($clang->gT("Error").": Failed to insert answer<br />\n$ainsert<br />\n".$connect->ErrorMsg());
            }
        }
    }

    // Finally the question attributes - Is called just once and only if there was a question  
    if (isset($question_attributesarray) && $question_attributesarray) {//ONLY DO THIS IF THERE ARE QUESTION_ATTRIBUES
        $fieldorders  =convertCSVRowToArray($question_attributesarray[0],',','"');
        unset($question_attributesarray[0]);
        foreach ($question_attributesarray as $qar) {
            $fieldcontents=convertCSVRowToArray($qar,',','"');
            $qarowdata=array_combine($fieldorders,$fieldcontents);
            $qarowdata["qid"]=$newqid;
            unset($qarowdata["qaid"]);

            $newvalues=array_values($qarowdata);
            $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
            $qainsert = "INSERT INTO {$dbprefix}question_attributes (".implode(',',array_keys($qarowdata)).") VALUES (".implode(',',$newvalues).")"; 
            $result=$connect->Execute($qainsert) or safe_die ("Couldn't insert question_attribute<br />$qainsert<br />".$connect->ErrorMsg());
        }
    }

}


$importquestion .= "<strong><font class='successtitle'>".$clang->gT("Success")."</font></strong><br /><br />\n"
."<strong><u>".$clang->gT("Question Import Summary")."</u></strong><br />\n"
."\t<li>".$clang->gT("Questions").": ";
if (isset($countquestions)) {$importquestion .= $countquestions;}
$importquestion .= "</li>\n"
."\t<li>".$clang->gT("Answers").": ";
if (isset($countanswers)) {$importquestion .= $countanswers;}
$importquestion .= "</li>\n"
."\t<li>".$clang->gT("Label Sets").": ";
if (isset($countlabelsets)) {$importquestion .= $countlabelsets;}
$importquestion .= " (";
if (isset($countlabels)) {$importquestion .= $countlabels;}
$importquestion .= ")</li>\n";
$importquestion .= "\t<li>".$clang->gT("Question Attributes:");
if (isset($countquestion_attributes)) {$importquestion .= $countquestion_attributes;}
$importquestion .= "</li></ul><br />\n";

$importquestion .= "<strong>".$clang->gT("Question import is complete.")."</strong><br />&nbsp;\n";
$importquestion .= "<a href='$scriptname?sid=$newsid&amp;gid=$newgid&amp;qid=$newqid'>".$clang->gT("Go to question")."</a><br />\n";
$importquestion .= "</td></tr></table><br/></td></tr></table>\n";


unlink($the_full_file_path);


?>
