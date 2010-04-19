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

$importquestion = "<div class='header'>".$clang->gT("Import Question")."</div>\n";
$importquestion .= "<div class='messagebox'>\n";

$sFullFilepath = $tempdir . DIRECTORY_SEPARATOR . $_FILES['the_file']['name'];
$aPathInfo = pathinfo($sFullFilepath);
$sExtension = $aPathInfo['extension'];


if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath))
{
    $fatalerror = sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$tempdir);
}

// validate that we have a SID and GID
if (!returnglobal('sid'))
{
    $fatalerror .= $clang->gT("No SID (Survey) has been provided. Cannot import question.");
}
else
{
    $surveyid=returnglobal('sid');
}

if (!returnglobal('gid'))
{
    $fatalerror .= $clang->gT("No GID (Group) has been provided. Cannot import question");
    return;
}
else
{
    $postgid=returnglobal('gid');
}

if (isset($fatalerror))
{
    $importquestion .= "<div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
    $importquestion .= $fatalerror."<br /><br />\n";
    $importquestion .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br /><br />\n";
    $importquestion .= "</div>\n";
    unlink($sFullFilepath);
    return;
}

// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY
$importquestion .= "<div class='successheader'>".$clang->gT("Success")."</div>&nbsp;<br />\n"
.$clang->gT("File upload succeeded.")."<br /><br />\n"
.$clang->gT("Reading file..")."<br /><br />\n";
if (strtolower($sExtension)=='csv')
{
    $importresults=CSVImportQuestion($sFullFilepath, $surveyid, $gid);
}
elseif (strtolower($sExtension)=='lsq')
{
    $importresults=XMLImportQuestion($sFullFilepath, $surveyid, $gid);
}
else die('Unknown file extension');
FixLanguageConsistency($surveyid);

if (isset($importresults['fatalerror']))
{
        $importquestion .= "<div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
        $importquestion .= $importresults['fatalerror']."<br /><br />\n";
        $importquestion .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";
        $importquestion .=  "</div>\n";
        unlink($sFullFilepath);
        return;
}

$importquestion .= "<div class='successheader'>".$clang->gT("Success")."</div><br />\n"
."<strong><u>".$clang->gT("Question import summary")."</u></strong><br />\n"
."<ul style=\"text-align:left;\">\n"
."\t<li>".$clang->gT("Questions").": ".$importresults['questions']."</li>\n"
."\t<li>".$clang->gT("Subquestions").": ".$importresults['subquestions']."</li>\n"
."\t<li>".$clang->gT("Answers").": ".$importresults['answers']."</li>\n";
if (strtolower($sExtension)=='csv')  {
    $importquestion.="\t<li>".$clang->gT("Label sets").": ".$importresults['labelsets']." (".$importresults['labels'].")</li>\n";
}
$importquestion.="\t<li>".$clang->gT("Question attributes:").$importresults['question_attributes']."</li>"
."</ul>\n";

$importquestion .= "<strong>".$clang->gT("Question import is complete.")."</strong><br />&nbsp;\n";
$importquestion .= "<input type='submit' value='".$clang->gT("Go to question")."' onclick=\"window.open('$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid={$importresults['newqid']}', '_top')\" />\n";
$importquestion .= "</div><br />\n";

unlink($sFullFilepath);



/**
* This function imports an old-school question file (*.csv,*.sql)
* 
* @param mixed $sFullFilepath Full file patch to the import file
* @param mixed $newsid  Survey ID to which the question is attached
* @param mixed $newgid  Group ID top which the question is attached
*/
function CSVImportQuestion($sFullFilepath, $newsid, $newgid)
{
    global $dbprefix, $connect;
    $handle = fopen($sFullFilepath, "r");
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
    elseif ((substr($bigarray[1], 0, 24) == "# SURVEYOR QUESTION DUMP")&& (substr($bigarray[4], 0, 37) == "# http://phpsurveyor.sourceforge.net/"))
    {
        $importversion = 99;  // Version 0.99 file or older - carries a different URL
    }
    elseif (substr($bigarray[0], 0, 26) == "# LimeSurvey Question Dump" || substr($bigarray[0], 0, 27) == "# PHPSurveyor Question Dump")
    {  // This is a >1.0 version file - these files carry the version information to read in line two
        $importversion=(integer)substr($bigarray[1], 12, 3);
    }
    else    // unknown file - show error message
    {
        $results['fatalerror'] = $clang->gT("This file is not a LimeSurvey question file. Import failed.");
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
    $stoppoint = count($bigarray);
    for ($i=0; $i<=$stoppoint+1; $i++)
    {
        if ($i<$stoppoint-1) {$question_attributesarray[] = $bigarray[$i];}
        unset($bigarray[$i]);
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

    $aLanguagesSupported = array();  // this array will keep all the languages supported for the survey

    $sBaseLanguage = GetBaseLanguageFromSurveyID($newsid);
    $aLanguagesSupported[]=$sBaseLanguage;     // adds the base language to the list of supported languages
    $aLanguagesSupported=array_merge($aLanguagesSupported,GetAdditionalLanguagesFromSurveyID($newsid));


    // Let's check that imported objects support at least the survey's baselang
    
    if ($countquestions > 0)
    {
        $questionfieldnames = convertCSVRowToArray($questionarray[0],',','"');
        $langfieldnum = array_search("language", $questionfieldnames);
        $qidfieldnum = array_search("qid", $questionfieldnames);
        $questionssupportbaselang = bDoesImportarraySupportsLanguage($questionarray,Array($qidfieldnum), $langfieldnum,$sBaseLanguage,true);
        if (!$questionssupportbaselang)
        {
            $results['fatalerror']=$clang->gT("You can't import a question which doesn't support at least the survey base language.");
            return $results;
        }
    }

    if ($countanswers > 0)
    {
        $langfieldnum = array_search("language", $answerfieldnames);
        $answercodefilednum1 =  array_search("qid", $answerfieldnames);
        $answercodefilednum2 =  array_search("code", $answerfieldnames);
        $answercodekeysarr = Array($answercodefilednum1,$answercodefilednum2);
        $answerssupportbaselang = bDoesImportarraySupportsLanguage($answerarray,$answercodekeysarr,$langfieldnum,$sBaseLanguage);
        if (!$answerssupportbaselang)
        {
            $results['fatalerror']=$clang->gT("You can't import answers which doesn't support at least the survey base language.");
            return $results;
            
        }
    
    }

    if ($countlabelsets > 0)
    {
        $labelsetfieldname = convertCSVRowToArray($labelsetsarray[0],',','"');
        $langfieldnum = array_search("languages", $labelsetfieldname);
        $lidfilednum =  array_search("lid", $labelsetfieldname);
        $labelsetssupportbaselang = bDoesImportarraySupportsLanguage($labelsetsarray,Array($lidfilednum),$langfieldnum,$sBaseLanguage,true);
        if (!$labelsetssupportbaselang)
        {
            $results['fatalerror']=$clang->gT("You can't import label sets which don't support the current survey's base language");
            return $results;
        }
    }
    // I assume that if a labelset supports the survey's baselang,
    // then it's labels do support it as well

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
            $labelreplacements[$oldlid]=$newlid;
        }
    }



    // QUESTIONS, THEN ANSWERS FOR QUESTIONS IN A NESTED FORMAT!
    if (isset($questionarray) && $questionarray) {
        $qafieldorders=convertCSVRowToArray($questionarray[0],',','"');
        unset($questionarray[0]);

        //Assuming we will only import one question at a time we will now find out the maximum question order in this group
        //and save it for later
        $newquestionorder = $connect->GetOne("SELECT MAX(question_order) AS maxqo FROM ".db_table_name('questions')." WHERE sid=$newsid AND gid=$newgid");
        if ($newquestionorder===false) 
        {
            $newquestionorder=0;
        }
        else {
            $newquestionorder++;
        }

        foreach ($questionarray as $qa) {
            $qacfieldcontents=convertCSVRowToArray($qa,',','"');
            $newfieldcontents=$qacfieldcontents;
            $questionrowdata=array_combine($qafieldorders,$qacfieldcontents);
            if (in_array($questionrowdata["language"],$aLanguagesSupported))
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

                // Save the following values - will need them for proper conversion later
                $oldquestion['lid1']=$questionrowdata['lid'];
                $oldquestion['lid2']=$questionrowdata['lid1'];
                $oldquestion['oldtype']=$questionrowdata['type'];
                
                // Unset label set IDs and convert question types
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
                $oldquestion['newtype']=$questionrowdata['type'];
                
                $questionrowdata=array_map('convertCsvreturn2return', $questionrowdata);

                // translate internal links
                $questionrowdata['title']=translink('survey', $oldsid, $newsid, $questionrowdata['title']);
                $questionrowdata['question']=translink('survey', $oldsid, $newsid, $questionrowdata['question']);
                $questionrowdata['help']=translink('survey', $oldsid, $newsid, $questionrowdata['help']);
                
                $newvalues=array_values($questionrowdata);
                $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                if (isset($questionrowdata['qid'])) {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('questions').' ON');}

                $qinsert = "INSERT INTO {$dbprefix}questions (".implode(',',array_keys($questionrowdata)).") VALUES (".implode(',',$newvalues).")";
                $qres = $connect->Execute($qinsert) or safe_die ("Error: Failed to insert question<br />\n$qinsert<br />\n".$connect->ErrorMsg());

                if (isset($questionrowdata['qid'])) {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('questions').' OFF');}

                // set the newqid only if is not set
                if (!isset($newqid))
                $newqid=$connect->Insert_ID("{$dbprefix}questions","qid");
            }
        }
        $qtypes = getqtypelist("" ,"array");   
        $subquestionids=array();
        $results['answers']=0;
        $results['subquestions']=0;
        
        //NOW DO ANSWERS FOR THIS QID - Is called just once and only if there was a question
        if (isset($answerarray) && $answerarray) {
            foreach ($answerarray as $aa) {
                $answerfieldcontents=convertCSVRowToArray($aa,',','"');
                $answerrowdata=array_combine($answerfieldnames,$answerfieldcontents);
                if ($answerrowdata===false)
                {
                    $importquestion.='<br />'.$clang->gT("Faulty line in import - fields and data don't match").":".implode(',',$answerfieldcontents);
                }
                if (in_array($answerrowdata["language"],$aLanguagesSupported))
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
                    unset($answerrowdata['default_value']);
                    $newvalues=array_values($answerrowdata);
                    $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                    
                    if ($qtypes[$oldquestion['newtype']]['subquestions']>0)
                    {
                        if (isset($subquestionids[$answerrowdata['code']])){
                           $fieldname='qid,';
                           $data=$subquestionids[$answerrowdata['code']].',';
                        }  
                        else{
                           $fieldname='' ;
                           $data='';
                        }
                        $qinsert = "insert INTO ".db_table_name('questions')." ({$fieldname}parent_qid,sid,gid,title,question,question_order,language)
                                    VALUES ({$data}{$answerrowdata['qid']},$newsid,$newgid,".db_quoteall($answerrowdata['code']).",".db_quoteall($answerrowdata['answer']).",".db_quoteall($answerrowdata['sortorder']).",".db_quoteall($answerrowdata['language']).")"; 
                        $qres = $connect->Execute($qinsert) or safe_die ($clang->gT("Error").": Failed to insert answer <br />\n$qinsert<br />\n".$connect->ErrorMsg());
                        if ($fieldname=='')
                        {
                           $subquestionids[$answerrowdata['code']]=$connect->Insert_ID("{$dbprefix}questions","qid");   
                        }
                        $results['subquestions']++;
                    }
                    else
                    {
                        $ainsert = "insert INTO {$dbprefix}answers (".implode(',',array_keys($answerrowdata)).") VALUES (".implode(',',$newvalues).")";
                        $ares = $connect->Execute($ainsert) or safe_die ($clang->gT("Error").": Failed to insert answer<br />\n$ainsert<br />\n".$connect->ErrorMsg());
                        $results['answers']++;                        
                    }
                }
            }
        }
        
        // Now we will fix up old label sets where they are used as answers
        if ((isset($oldquestion['lid1']) || isset($oldquestion['lid2'])) && ($qtypes[$oldquestion['newtype']]['answerscales']>0 || $qtypes[$oldquestion['newtype']]['subquestions']>1))
        {
            $query="select * from ".db_table_name('labels')." where lid={$labelreplacements[$oldquestion['lid1']]} ";
            $oldlabelsresult=db_execute_assoc($query);
            while($labelrow=$oldlabelsresult->FetchRow())
            {
                if (in_array($labelrow['language'],$aLanguagesSupported)){
                    
                    if ($qtypes[$oldquestion['newtype']]['subquestions']<2)
                    {
                        $qinsert = "insert INTO ".db_table_name('answers')." (qid,code,answer,sortorder,language,assessment_value,scale_id)
                                    VALUES ($newqid,".db_quoteall($labelrow['code']).",".db_quoteall($labelrow['title']).",".db_quoteall($labelrow['sortorder']).",".db_quoteall($labelrow['language']).",".db_quoteall($labelrow['assessment_value']).",0)"; 
                        $qres = $connect->Execute($qinsert) or safe_die ($clang->gT("Error").": Failed to insert answer <br />\n$qinsert<br />\n".$connect->ErrorMsg());
                        $results['answers']++;                        
                    }
                    else
                    {
                        if (isset($subquestionids[$answerrowdata['code']])){
                           $fieldname='qid,';
                           $data=$subquestionids[$answerrowdata['code']].',';
                        }  
                        else{
                           $fieldname='' ;
                           $data='';
                        }
                        
                        $qinsert = "insert INTO ".db_table_name('questions')." ($fieldname,parent_qid,title,question,question_order,language,scale_id)
                                    VALUES ($data, $newqid,".db_quoteall($labelrow['code']).",".db_quoteall($labelrow['title']).",".db_quoteall($labelrow['sortorder']).",".db_quoteall($labelrow['language']).",0)"; 
                        $qres = $connect->Execute($qinsert) or safe_die ($clang->gT("Error").": Failed to insert answer <br />\n$qinsert<br />\n".$connect->ErrorMsg());
                        if ($fieldname=='')
                        {
                           $subquestionids[$answerrowdata['code']]=$connect->Insert_ID("{$dbprefix}questions","qid");   
                        }
                        
                    }
                }
            }
                
            if (isset($oldquestion['lid2']) && $qtypes[$oldquestion['newtype']]['answerscales']>1)
            {
                $query="select * from ".db_table_name('labels')." where lid={$labelreplacements[$oldquestion['lid2']]}";
                $oldlabelsresult=db_execute_assoc($query);
                while($labelrow=$oldlabelsresult->FetchRow())
                {
                    if (in_array($labelrow['language'],$aLanguagesSupported)){
                        $qinsert = "insert INTO ".db_table_name('answers')." (qid,code,answer,sortorder,language,assessment_value,scale_id)
                                    VALUES ($newqid,".db_quoteall($labelrow['code']).",".db_quoteall($labelrow['title']).",".db_quoteall($labelrow['sortorder']).",".db_quoteall($labelrow['language']).",".db_quoteall($labelrow['assessment_value']).",1)"; 
                        $qres = $connect->Execute($qinsert) or safe_die ($clang->gT("Error").": Failed to insert answer <br />\n$qinsert<br />\n".$connect->ErrorMsg());
                    }
                }
            }
        }        

        $results['question_attributes']=0;
        // Finally the question attributes - it is called just once and only if there was a question
        if (isset($question_attributesarray) && $question_attributesarray) {//ONLY DO THIS IF THERE ARE QUESTION_ATTRIBUES
            $fieldorders  =convertCSVRowToArray($question_attributesarray[0],',','"');
            unset($question_attributesarray[0]);
            foreach ($question_attributesarray as $qar) {
                $fieldcontents=convertCSVRowToArray($qar,',','"');
                $qarowdata=array_combine($fieldorders,$fieldcontents);
                $qarowdata["qid"]=$newqid;
                unset($qarowdata["qaid"]);

                $tablename="{$dbprefix}question_attributes";
                $qainsert=$connect->GetInsertSQL($tablename,$qarowdata);
                $result=$connect->Execute($qainsert) or safe_die ("Couldn't insert question_attribute<br />$qainsert<br />".$connect->ErrorMsg());
                $results['question_attributes']++;
                
            }
        }

    }
    $results['newqid']=$newqid;
    $results['questions']=1;
    $results['labelsets']=0;
    $results['labels']=0;
    $results['newqid']=$newqid;
    return $results;         
}



/**
* This function imports a .lsq XML file
* 
* @param mixed $sFullFilepath  The full filepath of the uploaded file
* @param mixed $newsid The new survey id 
* @param mixed $newgid The new question group id -the question will always be added after the last question in the group
*/
function XMLImportQuestion($sFullFilepath, $newsid, $newgid)
{
    global $connect, $dbprefix, $clang;
    $aLanguagesSupported = array();  // this array will keep all the languages supported for the survey

    $sBaseLanguage = GetBaseLanguageFromSurveyID($newsid);
    $aLanguagesSupported[]=$sBaseLanguage;     // adds the base language to the list of supported languages
    $aLanguagesSupported=array_merge($aLanguagesSupported,GetAdditionalLanguagesFromSurveyID($newsid));
    
    $xml = simplexml_load_file($sFullFilepath);    
    if ($xml->LimeSurveyDocType!='Question') safe_die('This is not a valid LimeSurvey question structure XML file.');
    $dbversion = (int) $xml->DBVersion;
    $qidmappings=array();     
    $results['defaultvalues']=0;
    $results['answers']=0;
    $results['question_attributes']=0;
    $results['subquestions']=0;
    
    $importlanguages=array();
    foreach ($xml->languages->language as $language)
    {
        $importlanguages[]=(string)$language;
    }     

    if (!in_array($sBaseLanguage,$importlanguages))
    {
        $results['fatalerror'] = $clang->gT("The languages of the imported question file must at least include the base language of this survey.");
        return;
    }
    // First get an overview of fieldnames - it's not useful for the moment but might be with newer versions
    /*
    $fieldnames=array();
    foreach ($xml->questions->fields->fieldname as $fieldname )
    {
        $fieldnames[]=(string)$fieldname;
    };*/
    
                                                                                      
    // Import questions table ===================================================================================

    // We have to run the question table data two times - first to find all main questions
    // then for subquestions (because we need to determine the new qids for the main questions first)
    $tablename=$dbprefix.'questions';
    $newquestionorder=$connect->GetOne("SELECT MAX(question_order) AS maxqo FROM ".db_table_name('questions')." WHERE sid=$newsid AND gid=$newgid")+1;
    if ($newquestionorder===false) 
    {
        $newquestionorder=0;
    }
    else {
        $newquestionorder++;
    }
    foreach ($xml->questions->rows->row as $row)
    {
       $insertdata=array(); 
        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }
        $oldsid=$insertdata['sid'];
        $insertdata['sid']=$newsid;
        $insertdata['gid']=$newgid;
        $insertdata['question_order']=$newquestionorder;
        $oldqid=$insertdata['qid']; unset($insertdata['qid']); // save the old qid

        // now translate any links
        $insertdata['title']=translink('survey', $oldsid, $newsid, $insertdata['title']);
        $insertdata['question']=translink('survey', $oldsid, $newsid, $insertdata['question']);
        $insertdata['help']=translink('survey', $oldsid, $newsid, $insertdata['help']);
        // Insert the new question       
        $query=$connect->GetInsertSQL($tablename,$insertdata); 
        $result = $connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />{$query}<br />\n".$connect->ErrorMsg());
        $newqid=$connect->Insert_ID($tablename,"qid"); // save this for later
        $qidmappings[$oldqid]=$newqid; // add old and new qid to the mapping array
    }

    // Import subquestions --------------------------------------------------------------
    foreach ($xml->subquestions->rows->row as $row)
    {
        $insertdata=array(); 
        foreach ($row as $key=>$value)
        {
            $insertdata[$key]=$value;
        }
        $insertdata['sid']=$newsid;
        $insertdata['gid']=$newgid;
        $oldsqid=(int)$insertdata['qid']; unset($insertdata['qid']); // save the old qid
        $insertdata['parent_qid']=$qidmappings[(int)$insertdata['parent_qid']]; // remap the parent_qid

        // now translate any links
        $insertdata['title']=translink('survey', $oldsid, $newsid, $insertdata['title']);
        $insertdata['question']=translink('survey', $oldsid, $newsid, $insertdata['question']);
        $insertdata['help']=translink('survey', $oldsid, $newsid, $insertdata['help']);
        if (isset($qidmappings[$oldsqid])){
           $insertdata['qid']=$qidmappings[$oldsqid];
        }
        
        $query=$connect->GetInsertSQL($tablename,$insertdata); 
        $result = $connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />\$query<br />\n".$connect->ErrorMsg());
        $newsqid=$connect->Insert_ID($tablename,"qid"); // save this for later
        if (!isset($insertdata['qid']))
        {
            $qidmappings[$oldsqid]=$newsqid; // add old and new qid to the mapping array                
        }
        $results['subquestions']++;
    }

    // Import answer --------------------------------------------------------------
    if(isset($xml->answers))
    {
        $tablename=$dbprefix.'answers';
        
        foreach ($xml->answers->rows->row as $row)
        {
           $insertdata=array(); 
            foreach ($row as $key=>$value)
            {
                $insertdata[$key]=$value;
            }
            $insertdata['qid']=$qidmappings[(int)$insertdata['qid']]; // remap the parent_qid

            // now translate any links
            $query=$connect->GetInsertSQL($tablename,$insertdata); 
            $result=$connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />\$query<br />\n".$connect->ErrorMsg());
            $results['answers']++;
        }            
    }

    // Import questionattributes --------------------------------------------------------------
    if(isset($xml->question_attributes))
    {
        $tablename=$dbprefix.'question_attributes';
        
        foreach ($xml->question_attributes->rows->row as $row)
        {
            $insertdata=array(); 
            foreach ($row as $key=>$value)
            {
                $insertdata[$key]=$value;
            }
            unset($insertdata['qaid']);
            $insertdata['qid']=$qidmappings[(integer)$insertdata['qid']]; // remap the parent_qid

            // now translate any links
            $query=$connect->GetInsertSQL($tablename,$insertdata); 
            $result=$connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />\$query<br />\n".$connect->ErrorMsg());
            $results['question_attributes']++;
        }        
    }
    
    
    // Import defaultvalues --------------------------------------------------------------
    if(isset($xml->defaultvalues))
    {
        $tablename=$dbprefix.'defaultvalues';
        
        $results['defaultvalues']=0;
        foreach ($xml->defaultvalues->rows->row as $row)
        {
           $insertdata=array(); 
            foreach ($row as $key=>$value)
            {
                $insertdata[$key]=$value;
            }
            $insertdata['qid']=$qidmappings[(int)$insertdata['qid']]; // remap the qid
            $insertdata['sqid']=$qidmappings[(int)$insertdata['sqid']]; // remap the subqeustion id

            // now translate any links
            $query=$connect->GetInsertSQL($tablename,$insertdata); 
            $result=$connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />\$query<br />\n".$connect->ErrorMsg());
            $results['defaultvalues']++;
        }             
    }
    
    $results['newqid']=$newqid;
    $results['questions']=1;
    $results['labelsets']=0;
    $results['labels']=0;
    return $results;
}

?>
