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

$importquestion = "<div class='header ui-widget-header'>".$clang->gT("Import Question")."</div>\n";
$importquestion .= "<div class='messagebox ui-corner-all'>\n";

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
    $aImportResults=CSVImportQuestion($sFullFilepath, $surveyid, $gid);
}
elseif (strtolower($sExtension)=='lsq')
{
    $aImportResults=XMLImportQuestion($sFullFilepath, $surveyid, $gid);
}
else die('Unknown file extension');
FixLanguageConsistency($surveyid);

if (isset($aImportResults['fatalerror']))
{
        $importquestion .= "<div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
        $importquestion .= $aImportResults['fatalerror']."<br /><br />\n";
        $importquestion .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";
        $importquestion .=  "</div>\n";
        unlink($sFullFilepath);
        return;
}

$importquestion .= "<div class='successheader'>".$clang->gT("Success")."</div><br />\n"
."<strong><u>".$clang->gT("Question import summary")."</u></strong><br />\n"
."<ul style=\"text-align:left;\">\n"
."\t<li>".$clang->gT("Questions").": ".$aImportResults['questions']."</li>\n"
."\t<li>".$clang->gT("Subquestions").": ".$aImportResults['subquestions']."</li>\n"
."\t<li>".$clang->gT("Answers").": ".$aImportResults['answers']."</li>\n";
if (strtolower($sExtension)=='csv')  {
    $importquestion.="\t<li>".$clang->gT("Label sets").": ".$aImportResults['labelsets']." (".$aImportResults['labels'].")</li>\n";
}
$importquestion.="\t<li>".$clang->gT("Question attributes:").$aImportResults['question_attributes']."</li>"
."</ul>\n";

$importquestion .= "<strong>".$clang->gT("Question import is complete.")."</strong><br />&nbsp;\n";
$importquestion .= "<input type='submit' value='".$clang->gT("Go to question")."' onclick=\"window.open('$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid={$aImportResults['newqid']}', '_top')\" />\n";
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
    global $dbprefix, $connect, $clang;
    $aLIDReplacements=array();
    $aQIDReplacements=array(); // this array will have the "new qid" for the questions, the key will be the "old qid"
    $aSQIDReplacements=array();     
    $results['labelsets']=0;
    $results['labels']=0;
    
    $handle = fopen($sFullFilepath, "r");
    while (!feof($handle))
    {
        $buffer = fgets($handle); //To allow for very long survey welcomes (up to 10k)
        $bigarray[] = $buffer;
    }
    fclose($handle);
    $importversion=0;
    // Now we try to determine the dataformat of the survey file.
    if (substr($bigarray[1], 0, 24) == "# SURVEYOR QUESTION DUMP")
    {
        $importversion = 100;  // version 1.0 or 0.99 file
    }
    elseif (substr($bigarray[0], 0, 26) == "# LimeSurvey Question Dump" || substr($bigarray[0], 0, 27) == "# PHPSurveyor Question Dump")
    {  // This is a >1.0 version file - these files carry the version information to read in line two
        $importversion=(integer)substr($bigarray[1], 12, 3);
    }
    else    // unknown file - show error message
    {
        $results['fatalerror'] = $clang->gT("This file is not a LimeSurvey question file. Import failed.");
        return  $results;
    }
    
    if  ((int)$importversion<112)
    {
        $results['fatalerror'] = $clang->gT("This file is too old. Only files from LimeSurvey version 1.50 (DBVersion 112) and newer are supported.");
        return  $results;
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
        if ($i<$stoppoint-2) {$answerarray[] = $bigarray[$i];}
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

    if (isset($questionarray))
    {
        $questionfieldnames=convertCSVRowToArray($questionarray[0],',','"');
        unset($questionarray[0]);
		$countquestions = count($questionarray)-1;
    }
	else {$countquestions=0;}

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
    
    if (isset($questionarray))
    {
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

            $results['labelsets']++;

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
            $newlid=$connect->Insert_ID("{$dbprefix}labelsets",'lid');

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
                        $results['labels']++;
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
            $aLIDReplacements[$oldlid]=$newlid;
        }
    }


    // Import questions
    if (isset($questionarray) && $questionarray) {

        //Assuming we will only import one question at a time we will now find out the maximum question order in this group
        //and save it for later
        $newquestionorder = $connect->GetOne("SELECT MAX(question_order) AS maxqo FROM ".db_table_name('questions')." WHERE sid=$newsid AND gid=$newgid");
        if (is_null($newquestionorder))
        {
            $newquestionorder=0;
        }
        else 
        {
            $newquestionorder++;
        }

        foreach ($questionarray as $qa) 
        {
            $qacfieldcontents=convertCSVRowToArray($qa,',','"');
            $questionrowdata=array_combine($questionfieldnames,$qacfieldcontents);
            
            // Skip not supported languages
            if (!in_array($questionrowdata['language'],$aLanguagesSupported))
                continue;

            // replace the sid
            $oldqid = $questionrowdata['qid'];
            $oldsid = $questionrowdata['sid'];
            $oldgid = $questionrowdata['gid'];

            // Remove qid field if there is no newqid; and set it to newqid if it's set
            if (!isset($newqid))
            {
                unset($questionrowdata['qid']);
            }
            else
            {
                db_switchIDInsert('questions',true);
                $questionrowdata['qid'] = $newqid;
            }

            $questionrowdata["sid"] = $newsid;
            $questionrowdata["gid"] = $newgid;
            $questionrowdata["question_order"] = $newquestionorder;

            // Save the following values - will need them for proper conversion later                if ((int)$questionrowdata['lid']>0)
                if ((int)$questionrowdata['lid']>0)
                {
                  $oldquestion['lid1']=(int)$questionrowdata['lid'];
                }
                if ((int)$questionrowdata['lid1']>0)
                {
                  $oldquestion['lid2']=(int)$questionrowdata['lid1'];
                }
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
            $qinsert = "INSERT INTO {$dbprefix}questions (".implode(',',array_keys($questionrowdata)).") VALUES (".implode(',',$newvalues).")";
            $qres = $connect->Execute($qinsert) or safe_die ("Error: Failed to insert question<br />\n$qinsert<br />\n".$connect->ErrorMsg());

            // set the newqid only if is not set
            if (!isset($newqid))
            {
                $newqid=$connect->Insert_ID("{$dbprefix}questions","qid");
            }
            else
            {
                db_switchIDInsert('questions',false);
            }
            
        }
        $qtypes = getqtypelist("" ,"array");   
        $results['answers']=0;
        $results['subquestions']=0;
        
       
        // Now we will fix up old label sets where they are used as answers
        if ((isset($oldquestion['lid1']) || isset($oldquestion['lid2'])) && ($qtypes[$oldquestion['newtype']]['answerscales']>0 || $qtypes[$oldquestion['newtype']]['subquestions']>1))
        {
            $query="select * from ".db_table_name('labels')." where lid={$aLIDReplacements[$oldquestion['lid1']]} ";
            $oldlabelsresult=db_execute_assoc($query);
            while($labelrow=$oldlabelsresult->FetchRow())
            {
                if (in_array($labelrow['language'],$aLanguagesSupported)){
                    
                    if ($qtypes[$oldquestion['newtype']]['subquestions']<2)
                    {
                        $qinsert = "insert INTO ".db_table_name('answers')." (qid,code,answer,sortorder,language,assessment_value,scale_id)
                                    VALUES ($newqid,".db_quoteall($labelrow['code']).",".db_quoteall($labelrow['title']).",".db_quoteall($labelrow['sortorder']).",".db_quoteall($labelrow['language']).",".db_quoteall($labelrow['assessment_value']).",0)"; 
                        $qres = $connect->Execute($qinsert) or safe_die ("Error: Failed to insert answer <br />\n$qinsert<br />\n".$connect->ErrorMsg());
                        $results['answers']++;                        
                    }
                    else
                    {
                        if (isset($aSQIDReplacements[$labelrow['code']])){
                           $fieldname='qid,';
                           $data=$aSQIDReplacements[$labelrow['code']].',';
                           db_switchIDInsert('questions',true);
                        }  
                        else{
                           $fieldname='' ;
                           $data='';
                        }
                        
                        $qinsert = "insert INTO ".db_table_name('questions')." ($fieldname sid,gid,parent_qid,title,question,question_order,language,scale_id,type)
                                    VALUES ($data $newsid,$newgid,$newqid,".db_quoteall($labelrow['code']).",".db_quoteall($labelrow['title']).",".db_quoteall($labelrow['sortorder']).",".db_quoteall($labelrow['language']).",1,".db_quoteall($oldquestion['newtype']).")"; 
                        $qres = $connect->Execute($qinsert) or safe_die ("Error: Failed to insert subquestion <br />\n$qinsert<br />\n".$connect->ErrorMsg());
                        if ($fieldname=='')
                        {
                           $aSQIDReplacements[$labelrow['code']]=$connect->Insert_ID("{$dbprefix}questions","qid");   
                        }
                        else
                        { 
                            db_switchIDInsert('questions',false);
                        }
                        
                    }
                }
            }
                
            if (isset($oldquestion['lid2']) && $qtypes[$oldquestion['newtype']]['answerscales']>1)
            {
                $query="select * from ".db_table_name('labels')." where lid={$aLIDReplacements[$oldquestion['lid2']]}";
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

        //Do answers
        if (isset($answerarray) && $answerarray) 
        {
            foreach ($answerarray as $aa) 
            {
                $answerfieldcontents=convertCSVRowToArray($aa,',','"');
                $answerrowdata=array_combine($answerfieldnames,$answerfieldcontents);
                if ($answerrowdata===false)
                {
                    $importquestion.='<br />'.$clang->gT("Faulty line in import - fields and data don't match").":".implode(',',$answerfieldcontents);
                }
                // Skip not supported languages
                if (!in_array($answerrowdata['language'],$aLanguagesSupported))
                    continue;                
                $code=$answerrowdata["code"];
                $thisqid=$answerrowdata["qid"];
                $answerrowdata["qid"]=$newqid;


                if ($importversion<=132)
                {
                    $answerrowdata["assessment_value"]=(int)$answerrowdata["code"];
                }

                // Convert default values for single select questions
                if ($answerrowdata['default_value']=='Y' && ($oldquestion['newtype']=='L' || $oldquestion['newtype']=='O' || $oldquestion['newtype']=='!'))
                {                    
                    $insertdata=array();                      
                    $insertdata['qid']=$newqid;
                    $insertdata['language']=$answerrowdata['language'];
                    $insertdata['defaultvalue']=$answerrowdata['answer']; 
                    $query=$connect->GetInsertSQL($dbprefix.'defaultvalues',$insertdata);  
                    $qres = $connect->Execute($query) or safe_die ("Error: Failed to insert defaultvalue <br />{$query}<br />\n".$connect->ErrorMsg());
                                           
                }
                // translate internal links
                $answerrowdata['answer']=translink('survey', $oldsid, $newsid, $answerrowdata['answer']);
                // Everything set - now insert it
                $answerrowdata = array_map('convertCsvreturn2return', $answerrowdata);

                if ($qtypes[$oldquestion['newtype']]['subquestions']>0) //hmmm.. this is really a subquestion
                {
                    $questionrowdata=array();
                    if (isset($aSQIDReplacements[$answerrowdata['code'].$answerrowdata['qid']])){
                        $questionrowdata['qid']=$aSQIDReplacements[$answerrowdata['code'].$answerrowdata['qid']];
                        db_switchIDInsert('questions',true);
                    }  
                    $questionrowdata['parent_qid']=$answerrowdata['qid'];
                    $questionrowdata['sid']=$newsid;
                    $questionrowdata['gid']=$newgid;
                    $questionrowdata['title']=$answerrowdata['code'];
                    $questionrowdata['question']=$answerrowdata['answer'];
                    $questionrowdata['question_order']=$answerrowdata['sortorder'];
                    $questionrowdata['language']=$answerrowdata['language'];
                    $questionrowdata['type']=$oldquestion['newtype'];
                    
                    $tablename=$dbprefix.'questions'; 
                    $query=$connect->GetInsertSQL($tablename,$questionrowdata);                         
                    $qres = $connect->Execute($query) or safe_die ("Error: Failed to insert question <br />{$query}<br />\n".$connect->ErrorMsg());
                    if (!isset($questionrowdata['qid']))
                    {
                       $aSQIDReplacements[$answerrowdata['code'].$answerrowdata['qid']]=$connect->Insert_ID("{$dbprefix}questions","qid");   
                    }
                    else
                    {
                        db_switchIDInsert('questions',false);
                    }
                    $results['subquestions']++;
                    // also convert default values subquestions for multiple choice
                    if ($answerrowdata['default_value']=='Y' && ($oldquestion['newtype']=='M' || $oldquestion['newtype']=='P'))
                    {                    
                        $insertdata=array();                      
                        $insertdata['qid']=$newqid;
                        $insertdata['sqid']=$aSQIDReplacements[$answerrowdata['code']];
                        $insertdata['language']=$answerrowdata['language'];
                        $insertdata['defaultvalue']='Y';
                        $tablename=$dbprefix.'defaultvalues'; 
                        $query=$connect->GetInsertSQL($tablename,$insertdata);                         
                        $qres = $connect->Execute($query) or safe_die ("Error: Failed to insert defaultvalue <br />{$query}<br />\n".$connect->ErrorMsg());
                    }
                    
                }
                else   // insert answers
                {
                    unset($answerrowdata['default_value']);
                    $tablename=$dbprefix.'answers'; 
                    $query=$connect->GetInsertSQL($tablename,$answerrowdata);                         
                    $ares = $connect->Execute($query) or safe_die ("Error: Failed to insert answer<br />{$query}<br />\n".$connect->ErrorMsg());
                    $results['answers']++;                        
                }
            }
        }
                
        $results['question_attributes']=0;
        // Finally the question attributes - it is called just once and only if there was a question
        if (isset($question_attributesarray) && $question_attributesarray) 
        {//ONLY DO THIS IF THERE ARE QUESTION_ATTRIBUES
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
    $results['newqid']=$newqid;
    return $results;         
}



/**
* This function imports a LimeSurvey .lsq question XML file
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
    $aQIDReplacements=array();     
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
        return $results;
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
    if (is_null($newquestionorder))
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
        if (isset($aQIDReplacements[$oldqid]))
        {
           $insertdata['qid']=$aQIDReplacements[$oldqid]; 
           db_switchIDInsert('questions',true);
        }   
        $query=$connect->GetInsertSQL($tablename,$insertdata); 
        $result = $connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />{$query}<br />\n".$connect->ErrorMsg());
        if (!isset($aQIDReplacements[$oldqid]))
        {
            $newqid=$connect->Insert_ID($tablename,"qid"); // save this for later
            $aQIDReplacements[$oldqid]=$newqid; // add old and new qid to the mapping array
        }
        else
        {
            db_switchIDInsert('questions',false);
        }
    }

    // Import subquestions --------------------------------------------------------------
    if (isset($xml->subquestions))
    {
        
        foreach ($xml->subquestions->rows->row as $row)
        {
            $insertdata=array(); 
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['sid']=$newsid;
            $insertdata['gid']=$newgid;
            $oldsqid=(int)$insertdata['qid']; unset($insertdata['qid']); // save the old qid
            $insertdata['parent_qid']=$aQIDReplacements[(int)$insertdata['parent_qid']]; // remap the parent_qid

            // now translate any links
            $insertdata['title']=translink('survey', $oldsid, $newsid, $insertdata['title']);
            $insertdata['question']=translink('survey', $oldsid, $newsid, $insertdata['question']);
            $insertdata['help']=translink('survey', $oldsid, $newsid, $insertdata['help']);
            if (isset($aQIDReplacements[$oldsqid])){
               $insertdata['qid']=$aQIDReplacements[$oldsqid];
               db_switchIDInsert('questions',true);
            }
            
            $query=$connect->GetInsertSQL($tablename,$insertdata); 
            $result = $connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />{$query}<br />\n".$connect->ErrorMsg());
            $newsqid=$connect->Insert_ID($tablename,"qid"); // save this for later
            if (!isset($insertdata['qid']))
            {
                $aQIDReplacements[$oldsqid]=$newsqid; // add old and new qid to the mapping array                
            }
            else
            {
               db_switchIDInsert('questions',false);
            }
            $results['subquestions']++;
        }
    }
    // Import answers --------------------------------------------------------------
    if(isset($xml->answers))
    {
        $tablename=$dbprefix.'answers';
        
        foreach ($xml->answers->rows->row as $row)
        {
           $insertdata=array(); 
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['qid']=$aQIDReplacements[(int)$insertdata['qid']]; // remap the parent_qid

            // now translate any links
            $query=$connect->GetInsertSQL($tablename,$insertdata); 
            $result=$connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />{$query}<br />\n".$connect->ErrorMsg());
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
                $insertdata[(string)$key]=(string)$value;
            }
            unset($insertdata['qaid']);
            $insertdata['qid']=$aQIDReplacements[(integer)$insertdata['qid']]; // remap the parent_qid

            // now translate any links
            $query=$connect->GetInsertSQL($tablename,$insertdata); 
            $result=$connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />{$query}<br />\n".$connect->ErrorMsg());
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
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['qid']=$aQIDReplacements[(int)$insertdata['qid']]; // remap the qid
            $insertdata['sqid']=$aSQIDReplacements[(int)$insertdata['sqid']]; // remap the subquestion id

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
