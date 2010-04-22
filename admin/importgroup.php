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

$importgroup = "<div class='header'>".$clang->gT("Import question group")."</div>\n";
$importgroup .= "<div class='messagebox'>\n";

$sFullFilepath = $tempdir . DIRECTORY_SEPARATOR . $_FILES['the_file']['name'];
$aPathInfo = pathinfo($sFullFilepath);
$sExtension = $aPathInfo['extension'];

if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath))
{
    $fatalerror = sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$tempdir);
}

// validate that we have a SID
if (!returnglobal('sid'))
{
    $fatalerror .= $clang->gT("No SID (Survey) has been provided. Cannot import question.");
}
else
{
    $surveyid=returnglobal('sid');
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
$importgroup .= "<div class='successheader'>".$clang->gT("Success")."</div>&nbsp;<br />\n"
.$clang->gT("File upload succeeded.")."<br /><br />\n"
.$clang->gT("Reading file..")."<br /><br />\n";
if (strtolower($sExtension)=='csv')
{
    $aImportResults=CSVImportGroup($sFullFilepath, $surveyid);
}
elseif (strtolower($sExtension)=='lsg')
{
    $aImportResults=XMLImportGroup($sFullFilepath, $surveyid);
}
else die('Unknown file extension');
FixLanguageConsistency($surveyid);

if (isset($aImportResults['fatalerror']))
{
        $importgroup .= "<div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
        $importgroup .= $aImportResults['fatalerror']."<br /><br />\n";
        $importgroup .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";
        $importgroup .=  "</div>\n";
        unlink($sFullFilepath);
        return;
}

$importgroup .= "<div class='successheader'>".$clang->gT("Success")."</div><br />\n"
."<strong><u>".$clang->gT("Question group import summary")."</u></strong><br />\n"
."<ul style=\"text-align:left;\">\n"
."\t<li>".$clang->gT("Groups").": ".$aImportResults['groups']."</li>\n"
."\t<li>".$clang->gT("Questions").": ".$aImportResults['questions']."</li>\n"
."\t<li>".$clang->gT("Subquestions").": ".$aImportResults['subquestions']."</li>\n"
."\t<li>".$clang->gT("Answers").": ".$aImportResults['answers']."</li>\n"
."\t<li>".$clang->gT("Conditions").": ".$aImportResults['conditions']."</li>\n";
if (strtolower($sExtension)=='csv')  {
    $importgroup.="\t<li>".$clang->gT("Label sets").": ".$aImportResults['labelsets']." (".$aImportResults['labels'].")</li>\n";
}
$importgroup.="\t<li>".$clang->gT("Question attributes:").$aImportResults['question_attributes']."</li>"
."</ul>\n";

$importgroup .= "<strong>".$clang->gT("Question group import is complete.")."</strong><br />&nbsp;\n";
$importgroup .= "<input type='submit' value='".$clang->gT("Go to question group")."' onclick=\"window.open('$scriptname?sid=$surveyid&amp;gid={$aImportResults['newgid']}', '_top')\" />\n";
$importgroup .= "</div><br />\n";

unlink($sFullFilepath);


/**
* This function imports an old-school question group file (*.csv,*.sql)
* 
* @param mixed $sFullFilepath Full file patch to the import file
* @param mixed $newsid  Survey ID to which the question is attached
*/
function CSVImportGroup($sFullFilepath, $newsid)    
{
    global $dbprefix, $connect;       
    $aLIDReplacements=array();
    $aQIDReplacements = array(); // this array will have the "new qid" for the questions, the key will be the "old qid"
    $aGIDReplacements = array();
    $handle = fopen($sFullFilepath, "r");
    while (!feof($handle))
    {
        $buffer = fgets($handle);
        $bigarray[] = $buffer;
    }
    fclose($handle);

    if (substr($bigarray[0], 0, 23) != "# LimeSurvey Group Dump")
    {
        $results['fatalerror'] = $clang->gT("This file is not a LimeSurvey question file. Import failed.");
        $importversion=0; 
    }
    else
    {
        $importversion=(int)trim(substr($bigarray[1],12));
    }

    if  ((int)$importversion<112)
    {
        $results['fatalerror'] = $clang->gT("This file is too old. Only files from LimeSurvey version 1.50 (DBVersion 112) and later are support.");
    }
        
    for ($i=0; $i<9; $i++) //skipping the first lines that are not needed
    {
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
        if ($i<$stoppoint-2) {$questionarray[] = $bigarray[$i];}
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
        if ($i<$stoppoint-2) {$answerarray[] = $bigarray[$i];}
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
    {
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

    //Question_attributes
    if (!isset($noconditions) || $noconditions != "Y")
    {
        // stoppoint is the last line number
        // this is an empty line after the QA CSV lines
        $stoppoint = count($bigarray)-1;
        for ($i=0; $i<=$stoppoint+1; $i++)
        {
            if ($i<=$stoppoint-1) {$question_attributesarray[] = $bigarray[$i];}
            unset($bigarray[$i]);
        }
    }
    $bigarray = array_values($bigarray);

    $countgroups=0;
    if (isset($questionarray))
    {
        $questionfieldnames=convertCSVRowToArray($questionarray[0],',','"');
        unset($questionarray[0]);
        $countquestions = 0;
    }

    if (isset($answerarray))
    {
        $answerfieldnames=convertCSVRowToArray($answerarray[0],',','"');
        unset($answerarray[0]);
        $countanswers = count($answerarray);
    }
    else {$countanswers=0;}

    $aLanguagesSupported = array();  // this array will keep all the languages supported for the survey

    $sBaseLanguage = GetBaseLanguageFromSurveyID($newsid);
    $aLanguagesSupported[]=$sBaseLanguage;     // adds the base language to the list of supported languages
    $aLanguagesSupported=array_merge($aLanguagesSupported,GetAdditionalLanguagesFromSurveyID($newsid));




    // Let's check that imported objects support at least the survey's baselang
    $langcode = GetBaseLanguageFromSurveyID($newsid);
    if (isset($grouparray))
    {
        $groupfieldnames = convertCSVRowToArray($grouparray[0],',','"');
        $langfieldnum = array_search("language", $groupfieldnames);
        $gidfieldnum = array_search("gid", $groupfieldnames);
        $groupssupportbaselang = bDoesImportarraySupportsLanguage($grouparray,Array($gidfieldnum),$langfieldnum,$sBaseLanguage,true);
        if (!$groupssupportbaselang)
        {
            $results['fatalerror']=$clang->gT("You can't import a group which doesn't support at least the survey base language.");
            return $results;
        }
    }

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

    if (count($labelsetsarray) > 1)
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
    $results['labelsets']=0;
    $results['labels']=0;
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
            $results['labelsets']++;
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
                        if ($liresult!==false) $results['labels']++;
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
                $results['labels']=$results['labels']-$connect->Affected_Rows();

                $query = "DELETE FROM {$dbprefix}labelsets WHERE lid=$newlid";
                $result=$connect->Execute($query) or safe_die("Couldn't delete labelset<br />$query<br />".$connect->ErrorMsg());
                $results['labelsets']=$results['labelsets']-$connect->Affected_Rows();
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

    // Import groups
    if (isset($grouparray) && $grouparray)
    {
        // do GROUPS
        $gafieldorders=convertCSVRowToArray($grouparray[0],',','"');
        unset($grouparray[0]);
        $newgid = 0;
        $group_order = 0;   // just to initialize this variable
        foreach ($grouparray as $ga)
        {
            //GET ORDER OF FIELDS
            $gacfieldcontents=convertCSVRowToArray($ga,',','"');
            $grouprowdata=array_combine($gafieldorders,$gacfieldcontents);

            // Skip not supported languages
            if (!in_array($grouprowdata['language'],$aLanguagesSupported))
            {
                $skippedlanguages[]=$grouprowdata['language'];  // this is for the message in the end.
                continue;
            }

            // replace the sid
            $oldsid=$grouprowdata['sid'];
            $grouprowdata['sid']=$newsid;

            // replace the gid  or remove it if needed (it also will calculate the group order if is a new group)
            $oldgid=$grouprowdata['gid'];
            if ($newgid == 0)
            {
                unset($grouprowdata['gid']);

                // find the maximum group order and use this grouporder+1 to assign it to the new group
                $qmaxgo = "select max(group_order) as maxgo from ".db_table_name('groups')." where sid=$newsid";
                $gres = db_execute_assoc($qmaxgo) or safe_die ($clang->gT("Error")." Failed to find out maximum group order value<br />\n$qmaxqo<br />\n".$connect->ErrorMsg());
                $grow=$gres->FetchRow();
                $group_order = $grow['maxgo']+1;
            }
            else
            $grouprowdata['gid'] = $newgid;

            $grouprowdata["group_order"]= $group_order;

            // Everything set - now insert it
            $grouprowdata=array_map('convertCsvreturn2return', $grouprowdata);


            // translate internal links
            $grouprowdata['group_name']=translink('survey', $oldsid, $newsid, $grouprowdata['group_name']);
            $grouprowdata['description']=translink('survey', $oldsid, $newsid, $grouprowdata['description']);

            $newvalues=array_values($grouprowdata);
            $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
            if (isset($grouprowdata['gid'])) {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('groups')." ON");}

            $ginsert = "insert INTO {$dbprefix}groups (".implode(',',array_keys($grouprowdata)).") VALUES (".implode(',',$newvalues).")";
            $gres = $connect->Execute($ginsert) or safe_die($clang->gT("Error").": Failed to insert group<br />\n$ginsert<br />\n".$connect->ErrorMsg());
            if (isset($grouprowdata['gid'])) {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('groups').' OFF');}

            //GET NEW GID  .... if is not done before and we count a group if a new gid is required
            if ($newgid == 0)
            {
                $newgid = $connect->Insert_ID("{$dbprefix}groups",'gid');
                $countgroups++;
            }
        }
        // GROUPS is DONE

        // Import questions
        $results['questions']=0;
        $results['answers']=0;
        if (isset($questionarray) && $questionarray) 
        {
            foreach ($questionarray as $qa)
            {
                $qacfieldcontents=convertCSVRowToArray($qa,',','"');
                $questionrowdata=array_combine($questionfieldnames,$qacfieldcontents);

                // Skip not supported languages
                if (!in_array($questionrowdata['language'],$aLanguagesSupported))
                    continue;

                // replace the sid
                $questionrowdata["sid"] = $newsid;

                // replace the gid (if the gid is not in the oldgid it means there is a problem with the exported record, so skip it)
                if ($questionrowdata['gid'] == $oldgid)
                $questionrowdata['gid'] = $newgid;
                else
                continue; // a problem with this question record -> don't consider

                // replace the qid or remove it if needed
                $oldqid = $questionrowdata['qid'];
                if (isset($aQIDReplacements[$oldqid]))
                $questionrowdata['qid'] = $aQIDReplacements[$oldqid];
                else
                unset($questionrowdata['qid']);

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
                $qinsert = "insert INTO {$dbprefix}questions (".implode(',',array_keys($questionrowdata)).") VALUES (".implode(',',$newvalues).")";
                if (isset($questionrowdata['qid'])) {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('questions').' ON');}
                $qres = $connect->Execute($qinsert) or safe_die ($clang->gT("Error")."Failed to insert question<br />\n$qinsert<br />\n".$connect->ErrorMsg());
                if (isset($questionrowdata['qid'])) {@$connect->Execute('SET IDENTITY_INSERT '.db_table_name('questions').' OFF');}
                $results['questions']++;

                //GET NEW QID  .... if is not done before and we count a question if a new qid is required
                if (!isset($aQIDReplacements[$oldqid]))
                {
                    $aQIDReplacements[$oldqid] = $connect->Insert_ID("{$dbprefix}questions",'qid');
                }
                $qtypes = getqtypelist("" ,"array");   
                $subquestionids=array();
        
                
                // Now we will fix up old label sets where they are used as answers
                if ((isset($oldquestion['lid1']) || isset($oldquestion['lid2'])) && ($qtypes[$oldquestion['newtype']]['answerscales']>0 || $qtypes[$oldquestion['newtype']]['subquestions']>1))
                {
                    $query="select * from ".db_table_name('labels')." where lid={$aLIDReplacements[$oldquestion['lid1']]} and language='{$questionrowdata['language']}'";
                    $oldlabelsresult=db_execute_assoc($query);
                    while($labelrow=$oldlabelsresult->FetchRow())
                    {
                        if (in_array($labelrow['language'],$aLanguagesSupported)){
                            
                            if ($qtypes[$oldquestion['newtype']]['subquestions']<2)
                            {
                                $qinsert = "insert INTO ".db_table_name('answers')." (qid,code,answer,sortorder,language,assessment_value,scale_id)
                                            VALUES ({$aQIDReplacements[$oldqid]},".db_quoteall($labelrow['code']).",".db_quoteall($labelrow['title']).",".db_quoteall($labelrow['sortorder']).",".db_quoteall($labelrow['language']).",".db_quoteall($labelrow['assessment_value']).",0)"; 
                                $qres = $connect->Execute($qinsert) or safe_die ("Error: Failed to insert answer <br />{$qinsert}<br />".$connect->ErrorMsg());
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
                                            VALUES ($data, {$aQIDReplacements[$oldqid]},".db_quoteall($labelrow['code']).",".db_quoteall($labelrow['title']).",".db_quoteall($labelrow['sortorder']).",".db_quoteall($labelrow['language']).",0)"; 
                                $qres = $connect->Execute($qinsert) or safe_die ($clang->gT("Error").": Failed to insert answer <br />\n$qinsert<br />\n".$connect->ErrorMsg());
                                $results['questions']++;
                                if ($fieldname=='')
                                {
                                   $subquestionids[$answerrowdata['code']]=$connect->Insert_ID("{$dbprefix}questions","qid");   
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
            }
        }

        //Do answers
        $results['subquestions']=0;
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

                // replace the qid for the new one (if there is no new qid in the $aQIDReplacements array it mean that this answer is orphan -> error, skip this record)
                if (isset($aQIDReplacements[$answerrowdata["qid"]]))
                $answerrowdata["qid"] = $aQIDReplacements[$answerrowdata["qid"]];
                else
                continue; // a problem with this answer record -> don't consider

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
                    if (isset($subquestionids[$answerrowdata['code']])){
                       $questionrowdata['qid']=$subquestionids[$answerrowdata['code']];
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
                    $qres = $connect->Execute($query) or safe_die ("Error: Failed to insert questions <br />{$qinsert}<br />".$connect->ErrorMsg());
                    if (!isset($questionrowdata['qid']))
                    {
                       $subquestionids[$answerrowdata['code']]=$connect->Insert_ID("{$dbprefix}questions","qid");   
                    }
                    $results['subquestions']++;
                    // also convert default values subquestions for multiple choice
                    if ($answerrowdata['default_value']=='Y' && ($oldquestion['newtype']=='M' || $oldquestion['newtype']=='P'))
                    {                    
                        $insertdata=array();                      
                        $insertdata['qid']=$newqid;
                        $insertdata['sqid']=$subquestionids[$answerrowdata['code']];
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
        // ANSWERS is DONE

        // Fix Group sortorder
        fixsortorderGroups();
        //... and for the questions inside the groups
        // get all group ids and fix questions inside each group
        $gquery = "SELECT gid FROM {$dbprefix}groups where sid=$newsid group by gid ORDER BY gid"; //Get last question added (finds new qid)
        $gres = db_execute_assoc($gquery);
        while ($grow = $gres->FetchRow())
        {
            fixsortorderQuestions($grow['gid'], $newsid);
        }
    }

    $results['question_attributes']=0;
    // Finally the question attributes - it is called just once and only if there was a question
    if (isset($question_attributesarray) && $question_attributesarray) 
    {//ONLY DO THIS IF THERE ARE QUESTION_ATTRIBUES
        $fieldorders=convertCSVRowToArray($question_attributesarray[0],',','"');
        unset($question_attributesarray[0]);
        foreach ($question_attributesarray as $qar) {
            $fieldcontents=convertCSVRowToArray($qar,',','"');
            $qarowdata=array_combine($fieldorders,$fieldcontents);

            // replace the qid for the new one (if there is no new qid in the $aQIDReplacements array it mean that this attribute is orphan -> error, skip this record)
            if (isset($aQIDReplacements[$qarowdata["qid"]]))
            $qarowdata["qid"] = $aQIDReplacements[$qarowdata["qid"]];
            else
            continue; // a problem with this answer record -> don't consider

            unset($qarowdata["qaid"]);

            $tablename="{$dbprefix}question_attributes";
            $qainsert=$connect->GetInsertSQL($tablename,$qarowdata);
            $result=$connect->Execute($qainsert);
            if ($result!==false) $results['question_attributes']++;
        }
    }
    // ATTRIBUTES is DONE


    // do CONDITIONS
    $results['conditions']=0;
    if (isset($conditionsarray) && $conditionsarray)
    {
        $fieldorders=convertCSVRowToArray($conditionsarray[0],',','"');
        unset($conditionsarray[0]);
        foreach ($conditionsarray as $car) {
            $fieldcontents=convertCSVRowToArray($car,',','"');
            $conditionrowdata=array_combine($fieldorders,$fieldcontents);

            $oldqid = $conditionrowdata["qid"];
            $oldcqid = $conditionrowdata["cqid"];

            // replace the qid for the new one (if there is no new qid in the $aQIDReplacements array it mean that this condition is orphan -> error, skip this record)
            if (isset($aQIDReplacements[$oldqid]))
            $conditionrowdata["qid"] = $aQIDReplacements[$oldqid];
            else
            continue; // a problem with this answer record -> don't consider

            // replace the cqid for the new one (if there is no new qid in the $aQIDReplacements array it mean that this condition is orphan -> error, skip this record)
            if (isset($aQIDReplacements[$oldcqid]))
            $conditionrowdata["cqid"] = $aQIDReplacements[$oldcqid];
            else
            continue; // a problem with this answer record -> don't consider

            list($oldcsid, $oldcgid, $oldqidanscode) = explode("X",$conditionrowdata["cfieldname"],3);

            if ($oldcgid != $oldgid)    // this means that the condition is in another group (so it should not have to be been exported -> skip it
            continue;

            unset($conditionrowdata["cid"]);

            // recreate the cfieldname with the new IDs
            if (preg_match("/^\+/",$oldcsid))
            {
                $newcfieldname = '+'.$newsid . "X" . $newgid . "X" . $conditionrowdata["cqid"] .substr($oldqidanscode,strlen($oldqid));
            }
            else
            {
                $newcfieldname = $newsid . "X" . $newgid . "X" . $conditionrowdata["cqid"] .substr($oldqidanscode,strlen($oldqid));
            }

            $conditionrowdata["cfieldname"] = $newcfieldname;
            if (!isset($conditionrowdata["method"]) || trim($conditionrowdata["method"])=='')
            {
                $conditionrowdata["method"]='==';
            }
            $newvalues=array_values($conditionrowdata);
            $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
            $conditioninsert = "insert INTO {$dbprefix}conditions (".implode(',',array_keys($conditionrowdata)).") VALUES (".implode(',',$newvalues).")";
            $result=$connect->Execute($conditioninsert) or safe_die ("Couldn't insert condition<br />$conditioninsert<br />".$connect->ErrorMsg());
            $results['conditions']++;
        }
    }
    $results['groups']=1;
    $results['newgid']=$newgid;
    return $results;          
}


/**
* This function imports a LimeSurvey .lsg question group XML file
* 
* @param mixed $sFullFilepath  The full filepath of the uploaded file
* @param mixed $newsid The new survey id - the group will always be added after the last group in the survey   
*/
function XMLImportGroup($sFullFilepath, $newsid)
{
    global $connect, $dbprefix, $clang;
    $aLanguagesSupported = array();  // this array will keep all the languages supported for the survey

    $sBaseLanguage = GetBaseLanguageFromSurveyID($newsid);
    $aLanguagesSupported[]=$sBaseLanguage;     // adds the base language to the list of supported languages
    $aLanguagesSupported=array_merge($aLanguagesSupported,GetAdditionalLanguagesFromSurveyID($newsid));
    
    $xml = simplexml_load_file($sFullFilepath);    
    if ($xml->LimeSurveyDocType!='Group') safe_die('This is not a valid LimeSurvey group structure XML file.');
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
        $results['fatalerror'] = $clang->gT("The languages of the imported group file must at least include the base language of this survey.");
        return;
    }
    // First get an overview of fieldnames - it's not useful for the moment but might be with newer versions
    /*
    $fieldnames=array();
    foreach ($xml->questions->fields->fieldname as $fieldname )
    {
        $fieldnames[]=(string)$fieldname;
    };*/
    
                           
    // Import group table ===================================================================================

    $tablename=$dbprefix.'groups';
    $newgrouporder=$connect->GetOne("SELECT MAX(group_order) AS maxqo FROM ".db_table_name('group')." WHERE sid=$newsid")+1;
    if ($newgrouporder===false) 
    {
        $newgrouporder=0;
    }
    else {
        $newgrouporder++;
    }
    foreach ($xml->groups->rows->row as $row)
    {
       $insertdata=array(); 
        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }
        $oldsid=$insertdata['sid'];
        $insertdata['sid']=$newsid;
        $insertdata['group_order']=$newgrouporder;
        $oldgid=$insertdata['gid']; unset($insertdata['gid']); // save the old qid

        // now translate any links
        $insertdata['group_name']=translink('survey', $oldsid, $newsid, $insertdata['group_name']);
        $insertdata['description']=translink('survey', $oldsid, $newsid, $insertdata['description']);
        // Insert the new question    
        if (isset($aGIDReplacements[$oldgid]))
        {
           $insertdata['gid']=$aGIDReplacements[$oldgid]; 
        }   
        $query=$connect->GetInsertSQL($tablename,$insertdata); 
        $result = $connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />{$query}<br />\n".$connect->ErrorMsg());
        if (!isset($aGIDReplacements[$oldgid]))
        {
            $newgid=$connect->Insert_ID($tablename,"gid"); // save this for later
            $aGIDReplacements[$oldgid]=$newgid; // add old and new qid to the mapping array
        }
    }
                           
                                                                                      
    // Import questions table ===================================================================================

    // We have to run the question table data two times - first to find all main questions
    // then for subquestions (because we need to determine the new qids for the main questions first)
    $tablename=$dbprefix.'questions';
    $results['questions']=0;
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
        $insertdata['gid']=$aGIDReplacements[$insertdata['gid']];
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
        }   
        $query=$connect->GetInsertSQL($tablename,$insertdata); 
        $result = $connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />{$query}<br />\n".$connect->ErrorMsg());
        if (!isset($aQIDReplacements[$oldqid]))
        {
            $newqid=$connect->Insert_ID($tablename,"qid"); // save this for later
            $aQIDReplacements[$oldqid]=$newqid; // add old and new qid to the mapping array
            $results['questions']++;
        }
    }

    // Import subquestions --------------------------------------------------------------
    foreach ($xml->subquestions->rows->row as $row)
    {
        $insertdata=array(); 
        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }
        $insertdata['sid']=$newsid;
        $insertdata['gid']=$aGIDReplacements[(int)$insertdata['gid']];;
        $oldsqid=(int)$insertdata['qid']; unset($insertdata['qid']); // save the old qid
        $insertdata['parent_qid']=$aQIDReplacements[(int)$insertdata['parent_qid']]; // remap the parent_qid

        // now translate any links
        $insertdata['title']=translink('survey', $oldsid, $newsid, $insertdata['title']);
        $insertdata['question']=translink('survey', $oldsid, $newsid, $insertdata['question']);
        $insertdata['help']=translink('survey', $oldsid, $newsid, $insertdata['help']);
        if (isset($aQIDReplacements[$oldsqid])){
           $insertdata['qid']=$aQIDReplacements[$oldsqid];
        }
        
        $query=$connect->GetInsertSQL($tablename,$insertdata); 
        $result = $connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />{$query}<br />\n".$connect->ErrorMsg());
        $newsqid=$connect->Insert_ID($tablename,"qid"); // save this for later
        if (!isset($insertdata['qid']))
        {
            $aQIDReplacements[$oldsqid]=$newsqid; // add old and new qid to the mapping array                
        }
        $results['subquestions']++;
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
            $insertdata['sqid']=$aQIDReplacements[(int)$insertdata['sqid']]; // remap the subqeustion id

            // now translate any links
            $query=$connect->GetInsertSQL($tablename,$insertdata); 
            $result=$connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />\$query<br />\n".$connect->ErrorMsg());
            $results['defaultvalues']++;
        }             
    }

    // Import conditions --------------------------------------------------------------
    if(isset($xml->conditions))
    {
        $tablename=$dbprefix.'conditions';
        
        $results['conditions']=0;
        foreach ($xml->defaultvalues->rows->row as $row)
        {
           $insertdata=array(); 
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            // replace the qid for the new one (if there is no new qid in the $aQIDReplacements array it mean that this condition is orphan -> error, skip this record)
            if (isset($aQIDReplacements[$insertdata['qid']]))
            {
                $insertdata['qid']=$aQIDReplacements[$insertdata['qid']]; // remap the qid
            }
            else continue; // a problem with this answer record -> don't consider
            if (isset($aQIDReplacements[$insertdata['cqid']]))
            {
                $insertdata['cqid']=$aQIDReplacements[$insertdata['cqid']]; // remap the qid
            }
            else continue; // a problem with this answer record -> don't consider

            list($oldcsid, $oldcgid, $oldqidanscode) = explode("X",$insertdata["cfieldname"],3);

            if ($oldcgid != $oldgid)    // this means that the condition is in another group (so it should not have to be been exported -> skip it
            continue;

            unset($insertdata["cid"]);

            // recreate the cfieldname with the new IDs
            if (preg_match("/^\+/",$oldcsid))
            {
                $newcfieldname = '+'.$newsid . "X" . $newgid . "X" . $insertdata["cqid"] .substr($oldqidanscode,strlen($oldqid));
            }
            else
            {
                $newcfieldname = $newsid . "X" . $newgid . "X" . $insertdata["cqid"] .substr($oldqidanscode,strlen($oldqid));
            }

            $insertdata["cfieldname"] = $newcfieldname;
            if (trim($insertdata["method"])=='')
            {
                $insertdata["method"]='==';
            }            

            // now translate any links
            $query=$connect->GetInsertSQL($tablename,$insertdata); 
            $result=$connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />\$query<br />\n".$connect->ErrorMsg());
            $results['conditions']++;
        }             
    }

    
    $results['newgid']=$newgid;
    $results['labelsets']=0;
    $results['labels']=0;
    return $results;
}