<?php
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/


/**
* This function imports an old-school question group file (*.csv,*.sql)
*
* @param mixed $sFullFilepath Full file patch to the import file
* @param mixed $iNewSID  Survey ID to which the question is attached
*/
function CSVImportGroup($sFullFilepath, $iNewSID)
{
    $clang = Yii::app()->lang;

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
        $results['fatalerror'] = $clang->gT("This file is too old. Only files from LimeSurvey version 1.50 (DBVersion 112) and newer are supported.");
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

    //Question attributes
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

    $sBaseLanguage = Survey::model()->findByPk($iNewSID)->language;
    $aLanguagesSupported[]=$sBaseLanguage;     // adds the base language to the list of supported languages
    $aLanguagesSupported=array_merge($aLanguagesSupported,Survey::model()->findByPk($iNewSID)->additionalLanguages);




    // Let's check that imported objects support at least the survey's baselang
    $langcode = Survey::model()->findByPk($iNewSID)->language;
    if (isset($grouparray))
    {
        $groupfieldnames = convertCSVRowToArray($grouparray[0],',','"');
        $langfieldnum = array_search("language", $groupfieldnames);
        $gidfieldnum = array_search("gid", $groupfieldnames);
        $groupssupportbaselang = doesImportArraySupportLanguage($grouparray,Array($gidfieldnum),$langfieldnum,$sBaseLanguage,true);
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
        $questionssupportbaselang = doesImportArraySupportLanguage($questionarray,Array($qidfieldnum), $langfieldnum,$sBaseLanguage,true);
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
        $answerssupportbaselang = doesImportArraySupportLanguage($answerarray,$answercodekeysarr,$langfieldnum,$sBaseLanguage);
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
        $labelsetssupportbaselang = doesImportArraySupportLanguage($labelsetsarray,Array($lidfilednum),$langfieldnum,$sBaseLanguage,true);
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
    $qtypes = getQuestionTypeList("" ,"array");
    $results['labels']=0;
    $results['labelsets']=0;
    $results['answers']=0;
    $results['subquestions']=0;

    //Do label sets
    if (isset($labelsetsarray) && $labelsetsarray)
    {
        $csarray=buildLabelSetCheckSumArray();   // build checksums over all existing labelsets
        $count=0;
        foreach ($labelsetsarray as $lsa) {
            $fieldorders  =convertCSVRowToArray($labelsetsarray[0],',','"');
            $fieldcontents=convertCSVRowToArray($lsa,',','"');
            if ($count==0) {$count++; continue;}

            $labelsetrowdata=array_combine($fieldorders,$fieldcontents);

            // Save old labelid
            $oldlid=$labelsetrowdata['lid'];

            unset($labelsetrowdata['lid']);
            $newvalues=array_values($labelsetrowdata);
            $lsainsert = "INSERT INTO {{labelsets}} (".implode(',',array_keys($labelsetrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
            $lsiresult=Yii::app()->db->createCommand($lsainsert)->query();
            $results['labelsets']++;
            // Get the new insert id for the labels inside this labelset
            $newlid=getLastInsertID('{{labelsets}}');

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
                        $labelrowdata['title']=translateLinks('label', $oldlid, $newlid, $labelrowdata['title']);

                        $newvalues=array_values($labelrowdata);
                        $lainsert = "INSERT INTO {{labels}} (".implode(',',array_keys($labelrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
                        $liresult=Yii::app()->db->createCommand($lainsert)->query();
                        if ($liresult!==false) $results['labels']++;
                    }
                }
            }

            //CHECK FOR DUPLICATE LABELSETS
            $thisset="";

            $query2 = "SELECT code, title, sortorder, language, assessment_value
            FROM {{labels}}
            WHERE lid=".$newlid."
            ORDER BY language, sortorder, code";
            $result2 = Yii::app()->db->createCommand($query2);
            foreach($result2->readAll() as $row2)
            {
                $row2 = array_values($row2);
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
            if (isset($lsmatch) || (Yii::app()->session['USER_RIGHT_MANAGE_LABEL'] != 1))
            {
                //There is a matching labelset or the user is not allowed to edit labels -
                // So, we will delete this one and refer to the matched one.
                $query = "DELETE FROM {{labels}} WHERE lid=$newlid";
                $result=Yii::app()->db->createCommand($query)->execute();
                $results['labels']=$results['labels'] - $result;

                $query = "DELETE FROM {{labelsets}} WHERE lid=$newlid";
                $result=Yii::app()->db->createCommand($query)->execute();
                $results['labelsets']=$results['labelsets']-$result;
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
            $gacfieldcontents=convertCSVRowToArray($ga,',','"');
            $grouprowdata=array_combine($gafieldorders,$gacfieldcontents);

            // Skip not supported languages
            if (!in_array($grouprowdata['language'],$aLanguagesSupported))
            {
                $skippedlanguages[]=$grouprowdata['language'];  // this is for the message in the end.
                continue;
            }

            // replace the sid
            $iOldSID=$grouprowdata['sid'];
            $grouprowdata['sid']=$iNewSID;

            // replace the gid  or remove it if needed (it also will calculate the group order if is a new group)
            $oldgid=$grouprowdata['gid'];
            if ($newgid == 0)
            {
                unset($grouprowdata['gid']);

                // find the maximum group order and use this grouporder+1 to assign it to the new group
                $qmaxgo = "select max(group_order) as maxgo from {{groups}} where sid=$iNewSID";
                $gres = Yii::app()->db->createCommand($qmaxgo)->query();
                $grow=$gres->read();
                $group_order = $grow['maxgo']+1;
            }
            else
                $grouprowdata['gid'] = $newgid;

            $grouprowdata["group_order"]= $group_order;

            // Everything set - now insert it
            $grouprowdata=array_map('convertCSVReturnToReturn', $grouprowdata);

            // translate internal links
            $grouprowdata['group_name']=translateLinks('survey', $iOldSID, $iNewSID, $grouprowdata['group_name']);
            $grouprowdata['description']=translateLinks('survey', $iOldSID, $iNewSID, $grouprowdata['description']);

            $gres = Yii::app()->db->createCommand()->insert('{{groups}}', $grouprowdata);

            //GET NEW GID  .... if is not done before and we count a group if a new gid is required
            if ($newgid == 0)
            {
                $newgid = getLastInsertID('{{groups}}');
                $countgroups++;
            }
        }
        // GROUPS is DONE

        // Import questions
        if (isset($questionarray) && $questionarray)
        {

            foreach ($questionarray as $qa)
            {
                $qacfieldcontents=convertCSVRowToArray($qa,',','"');
                $questionrowdata=array_combine($questionfieldnames,$qacfieldcontents);
                $questionrowdata=array_map('convertCSVReturnToReturn', $questionrowdata);
                $questionrowdata["type"]=strtoupper($questionrowdata["type"]);

                // Skip not supported languages
                if (!in_array($questionrowdata['language'],$aLanguagesSupported))
                    continue;

                // replace the sid
                $questionrowdata["sid"] = $iNewSID;

                // replace the gid (if the gid is not in the oldgid it means there is a problem with the exported record, so skip it)
                if ($questionrowdata['gid'] == $oldgid)
                    $questionrowdata['gid'] = $newgid;
                else
                    continue; // a problem with this question record -> don't consider

                if (isset($aQIDReplacements[$questionrowdata['qid']]))
                {
                    $questionrowdata['qid']=$aQIDReplacements[$questionrowdata['qid']];
                }
                else
                {
                    $oldqid = $questionrowdata['qid'];
                    unset($questionrowdata['qid']);
                }

                // Save the following values - will need them for proper conversion later                if ((int)$questionrowdata['lid']>0)
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

                if (!isset($questionrowdata["question_order"]) || $questionrowdata["question_order"]=='') {$questionrowdata["question_order"]=0;}

                $questionrowdata=array_map('convertCSVReturnToReturn', $questionrowdata);

                // translate internal links
                $questionrowdata['title']=translateLinks('survey', $iOldSID, $iNewSID, $questionrowdata['title']);
                $questionrowdata['question']=translateLinks('survey', $iOldSID, $iNewSID, $questionrowdata['question']);
                $questionrowdata['help']=translateLinks('survey', $iOldSID, $iNewSID, $questionrowdata['help']);

                $newvalues=array_values($questionrowdata);
                $qres = Yii::app()->db->createCommand()->insert('{{questions}}', $questionrowdata);

                $results['questions']++;

                //GET NEW QID  .... if is not done before and we count a question if a new qid is required
                if (isset($questionrowdata['qid']))
                {
                    $saveqid=$questionrowdata['qid'];
                }
                else
                {
                    $aQIDReplacements[$oldqid]=getLastInsertID('{{questions}}');
                    $saveqid=$aQIDReplacements[$oldqid];
                }
                $qtypes = getQuestionTypeList("" ,"array");
                $aSQIDReplacements=array();

                // Now we will fix up old label sets where they are used as answers
                if ((isset($oldlid1) || isset($oldlid2)) && ($qtypes[$questionrowdata['type']]['answerscales']>0 || $qtypes[$questionrowdata['type']]['subquestions']>1))
                {
                    $query="select * from {{labels}} where lid={$aLIDReplacements[$oldlid1]} and language='{$questionrowdata['language']}'";
                    $oldlabelsresult=Yii::app()->db->createCommand($query)->query();
                    foreach($oldlabelsresult->readAll() as $labelrow)
                    {
                        if (in_array($labelrow['language'],$aLanguagesSupported))
                        {

                            if ($qtypes[$questionrowdata['type']]['subquestions']<2)
                            {
                                $qinsert = "insert INTO {{answers}} (qid,code,answer,sortorder,language,assessment_value)
                                VALUES ({$aQIDReplacements[$oldqid]},'".$labelrow['code']."','".$labelrow['title']."','".$labelrow['sortorder']."','".$labelrow['language']."','".$labelrow['assessment_value']."')";
                                $qres = Yii::app()->db->createCommand($qinsert)->query() or safeDie($clang->gT("Error").": Failed to insert answer (lid1) <br />\n$qinsert<br />\n");
                            }
                            else
                            {
                                if (isset($aSQIDReplacements[$labelrow['code'].'_'.$saveqid])){
                                    $fieldname='qid,';
                                    $data=$aSQIDReplacements[$labelrow['code'].'_'.$saveqid].',';
                                }
                                else
                                {
                                    $fieldname='' ;
                                    $data='';
                                }

                                $qinsert = "insert INTO {{questions}} ($fieldname parent_qid,title,question,question_order,language,scale_id,type, sid, gid)
                                VALUES ($data{$aQIDReplacements[$oldqid]},'".$labelrow['code']."','".$labelrow['title']."','".$labelrow['sortorder']."','".$labelrow['language']."',1,'{$questionrowdata['type']}',{$questionrowdata['sid']},{$questionrowdata['gid']})";
                                $qres = Yii::app()->db->createCommand($qinsert)->query() or safeDie ($clang->gT("Error").": Failed to insert question <br />\n$qinsert<br />\n");
                                if ($fieldname=='')
                                {
                                    $aSQIDReplacements[$labelrow['code'].'_'.$saveqid]=getLastInsertID('{{questions}}');
                                }
                            }
                        }
                    }
                    if (isset($oldlid2) && $qtypes[$questionrowdata['type']]['answerscales']>1)
                    {
                        $query="select * from {{labels}} where lid={$aLIDReplacements[$oldlid2]} and language='{$questionrowdata['language']}'";
                        $oldlabelsresult=Yii::app()->db->createCommand($query)->query();
                        foreach($oldlabelsresult->readAll() as $labelrow)
                        {
                            $qinsert = "insert INTO {{answers}} (qid,code,answer,sortorder,language,assessment_value,scale_id)
                            VALUES ({$aQIDReplacements[$oldqid]},'".$labelrow['code']."','".$labelrow['title']."','".$labelrow['sortorder']."','".$labelrow['language']."','".$labelrow['assessment_value']."',1)";
                            $qres = Yii::app()->db->createCommand($qinsert)->query() or safeDie ($clang->gT("Error").": Failed to insert answer (lid2)<br />\n$qinsert<br />\n");
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
                $query = 'select type,gid from {{questions}} where qid='.$answerrowdata["qid"];
                $res = Yii::app()->db->createCommand($query)->query();
                $questiontemp = $res->read();
                $oldquestion['newtype']=$questiontemp['type'];
                $oldquestion['gid']=$questiontemp['gid'];
                if ($answerrowdata['default_value']=='Y' && ($oldquestion['newtype']=='L' || $oldquestion['newtype']=='O' || $oldquestion['newtype']=='!'))
                {
                    $insertdata=array();
                    $insertdata['qid']=$newqid;
                    $insertdata['language']=$answerrowdata['language'];
                    $insertdata['defaultvalue']=$answerrowdata['answer'];
                    $qres = Yii::app()->db->createCommand()->insert('{{defaultvalues}}', $insertdata);
                }
                // translate internal links
                $answerrowdata['answer']=translateLinks('survey', $iOldSID, $iNewSID, $answerrowdata['answer']);
                // Everything set - now insert it
                $answerrowdata = array_map('convertCSVReturnToReturn', $answerrowdata);

                if ($qtypes[$oldquestion['newtype']]['subquestions']>0) //hmmm.. this is really a subquestion
                {
                    $questionrowdata=array();
                    if (isset($aSQIDReplacements[$answerrowdata['code'].$answerrowdata['qid']])){
                        $questionrowdata['qid']=$aSQIDReplacements[$answerrowdata['code'].$answerrowdata['qid']];
                    }
                    $questionrowdata['parent_qid']=$answerrowdata['qid'];;
                    $questionrowdata['sid']=$iNewSID;
                    $questionrowdata['gid']=$oldquestion['gid'];
                    $questionrowdata['title']=$answerrowdata['code'];
                    $questionrowdata['question']=$answerrowdata['answer'];
                    $questionrowdata['question_order']=$answerrowdata['sortorder'];
                    $questionrowdata['language']=$answerrowdata['language'];
                    $questionrowdata['type']=$oldquestion['newtype'];

                    $qres = Yii::app()->db->createCommand()->insert('{{questions}}', $questionrowdata);
                    if (!isset($questionrowdata['qid']))
                    {
                        $aSQIDReplacements[$answerrowdata['code'].$answerrowdata['qid']]=getLastInsertID('{{questions}}');
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
                        $qres = Yii::app()->db->createCommand()->insert('{{defaultvalues}}', $insertdata);
                    }

                }
                else   // insert answers
                {
                    unset($answerrowdata['default_value']);
                    $ares = Yii::app()->db->createCommand()->insert('{{answers}}', $answerrowdata);
                    $results['answers']++;
                }

            }
        }
        // ANSWERS is DONE

        // Fix sortorder of the groups  - if users removed groups manually from the csv file there would be gaps
        fixSortOrderGroups($surveyid);
        //... and for the questions inside the groups
        // get all group ids and fix questions inside each group
        $gquery = "SELECT gid FROM {{groups}} where sid=$iNewSID group by gid ORDER BY gid"; //Get last question added (finds new qid)
        $gres = Yii::app()->db->createCommand($gquery)->query();
        foreach ($gres->readAll() as $grow)
        {
            Questions::model()->updateQuestionOrder($grow['gid'], $iNewSID);
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

            $result = Yii::app()->db->createCommand()->insert('{{question_attributes}}', $qarowdata);
            if ($result!==false) $results['question_attributes']++;
        }
    }
    // ATTRIBUTES is DONE


    // TMSW Conditions->Relevance:  Anything needed here, other than call to LEM->ConvertConditionsToRelevance() when done?

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
                $newcfieldname = '+'.$iNewSID . "X" . $newgid . "X" . $conditionrowdata["cqid"] .substr($oldqidanscode,strlen($oldqid));
            }
            else
            {
                $newcfieldname = $iNewSID . "X" . $newgid . "X" . $conditionrowdata["cqid"] .substr($oldqidanscode,strlen($oldqid));
            }

            $conditionrowdata["cfieldname"] = $newcfieldname;
            if (!isset($conditionrowdata["method"]) || trim($conditionrowdata["method"])=='')
            {
                $conditionrowdata["method"]='==';
            }
            $newvalues=array_values($conditionrowdata);
            $conditioninsert = "insert INTO {{conditions}} (".implode(',',array_keys($conditionrowdata)).") VALUES (".implode(',',$newvalues).")";
            $result=Yii::app()->db->createCommand($conditioninsert)->query() or safeDie("Couldn't insert condition<br />$conditioninsert<br />");
            $results['conditions']++;
        }
    }
    LimeExpressionManager::RevertUpgradeConditionsToRelevance($iNewSID);
    LimeExpressionManager::UpgradeConditionsToRelevance($iNewSID);

    $results['groups']=1;
    $results['newgid']=$newgid;
    return $results;
}


/**
* This function imports a LimeSurvey .lsg question group XML file
*
* @param mixed $sFullFilepath  The full filepath of the uploaded file
* @param mixed $iNewSID The new survey id - the group will always be added after the last group in the survey
*/
function XMLImportGroup($sFullFilepath, $iNewSID)
{
    $clang = Yii::app()->lang;

    $aLanguagesSupported = array();  // this array will keep all the languages supported for the survey

    $sBaseLanguage = Survey::model()->findByPk($iNewSID)->language;
    $aLanguagesSupported[]=$sBaseLanguage;     // adds the base language to the list of supported languages
    $aLanguagesSupported=array_merge($aLanguagesSupported,Survey::model()->findByPk($iNewSID)->additionalLanguages);

    $xml = @simplexml_load_file($sFullFilepath);
    if ($xml==false || $xml->LimeSurveyDocType!='Group') safeDie('This is not a valid LimeSurvey group structure XML file.');
    $iDBVersion = (int) $xml->DBVersion;
    $aQIDReplacements=array();
    $results['defaultvalues']=0;
    $results['answers']=0;
    $results['question_attributes']=0;
    $results['subquestions']=0;
    $results['conditions']=0;
    $results['groups']=0;

    $importlanguages=array();
    foreach ($xml->languages->language as $language)
    {
        $importlanguages[]=(string)$language;
    }

    if (!in_array($sBaseLanguage,$importlanguages))
    {
        $results['fatalerror'] = $clang->gT("The languages of the imported group file must at least include the base language of this survey.");
        return $results;
    }
    // First get an overview of fieldnames - it's not useful for the moment but might be with newer versions
    /*
    $fieldnames=array();
    foreach ($xml->questions->fields->fieldname as $fieldname )
    {
    $fieldnames[]=(string)$fieldname;
    };*/


    // Import group table ===================================================================================


    $query = "SELECT MAX(group_order) AS maxgo FROM {{groups}} WHERE sid=$iNewSID";
    $iGroupOrder = Yii::app()->db->createCommand($query)->queryScalar();
    if ($iGroupOrder === false)
    {
        $iNewGroupOrder=0;
    }
    else
    {
        $iNewGroupOrder=$iGroupOrder+1;
    }

    foreach ($xml->groups->rows->row as $row)
    {
        $insertdata=array();
        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }
        $iOldSID=$insertdata['sid'];
        $insertdata['sid']=$iNewSID;
        $insertdata['group_order']=$iNewGroupOrder;
        $oldgid=$insertdata['gid']; unset($insertdata['gid']); // save the old qid

        // now translate any links
        $insertdata['group_name']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['group_name']);
        $insertdata['description']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['description']);
        // Insert the new question
        if (isset($aGIDReplacements[$oldgid]))
        {
            $insertdata['gid']=$aGIDReplacements[$oldgid];
        }
        if (isset($insertdata['gid'])) switchMSSQLIdentityInsert('groups',true);
        
        $result = Yii::app()->db->createCommand()->insert('{{groups}}', $insertdata);

        if (isset($insertdata['gid'])) switchMSSQLIdentityInsert('groups',false);
        $results['groups']++;

        if (!isset($aGIDReplacements[$oldgid]))
        {
            $newgid=getLastInsertID('{{groups}}');
            $aGIDReplacements[$oldgid]=$newgid; // add old and new qid to the mapping array
        }
    }


    // Import questions table ===================================================================================

    // We have to run the question table data two times - first to find all main questions
    // then for subquestions (because we need to determine the new qids for the main questions first)


    $results['questions']=0;
    foreach ($xml->questions->rows->row as $row)
    {
        $insertdata=array();
        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }
        $iOldSID=$insertdata['sid'];
        $insertdata['sid']=$iNewSID;
        if (!isset($aGIDReplacements[$insertdata['gid']]) || trim($insertdata['title'])=='') continue; // Skip questions with invalid group id
        $insertdata['gid']=$aGIDReplacements[$insertdata['gid']];
        $oldqid=$insertdata['qid']; unset($insertdata['qid']); // save the old qid

        // now translate any links
        $insertdata['title']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['title']);
        $insertdata['question']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
        $insertdata['help']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']);
        // Insert the new question
        if (isset($aQIDReplacements[$oldqid]))
        {
            $insertdata['qid']=$aQIDReplacements[$oldqid];
        }
        if (isset($insertdata['qid'])) switchMSSQLIdentityInsert('questions',true);
        
        $result = Yii::app()->db->createCommand()->insert('{{questions}}', $insertdata);
        if (isset($insertdata['qid'])) switchMSSQLIdentityInsert('questions',false);
        if (!isset($aQIDReplacements[$oldqid]))
        {
            $newqid=getLastInsertID('{{questions}}');
            $aQIDReplacements[$oldqid]=$newqid; // add old and new qid to the mapping array
            $results['questions']++;
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
            $insertdata['sid']=$iNewSID;
            if (!isset($aGIDReplacements[$insertdata['gid']])) continue; // Skip questions with invalid group id
            $insertdata['gid']=$aGIDReplacements[(int)$insertdata['gid']];;
            $oldsqid=(int)$insertdata['qid']; unset($insertdata['qid']); // save the old qid
            if (!isset($aQIDReplacements[(int)$insertdata['parent_qid']])) continue; // Skip subquestions with invalid parent_qids
            $insertdata['parent_qid']=$aQIDReplacements[(int)$insertdata['parent_qid']]; // remap the parent_qid

            // now translate any links
            $insertdata['title']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['title']);
            $insertdata['question']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
            $insertdata['help']=translateLinks('survey', $iOldSID, $iNewSID, !empty($insertdata['help']) ? $insertdata['help'] : '');
            if (isset($aQIDReplacements[$oldsqid])){
                $insertdata['qid']=$aQIDReplacements[$oldsqid];
            }
            if (isset($insertdata['qid'])) switchMSSQLIdentityInsert('questions',true);

            $result = Yii::app()->db->createCommand()->insert('{{questions}}', $insertdata);
            $newsqid=getLastInsertID('{{questions}}');
            if (isset($insertdata['qid'])) switchMSSQLIdentityInsert('questions',true);
            
            if (!isset($insertdata['qid']))
            {
                $aQIDReplacements[$oldsqid]=$newsqid; // add old and new qid to the mapping array
            }

            $results['subquestions']++;
        }
    }

    // Import answers --------------------------------------------------------------
    if(isset($xml->answers))
    {


        foreach ($xml->answers->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            if (!isset($aQIDReplacements[(int)$insertdata['qid']])) continue; // Skip questions with invalid group id

            $insertdata['qid']=$aQIDReplacements[(int)$insertdata['qid']]; // remap the parent_qid

            // now translate any links
            $result = Yii::app()->db->createCommand()->insert('{{answers}}', $insertdata);
            $results['answers']++;
        }
    }

    // Import questionattributes --------------------------------------------------------------
    if(isset($xml->question_attributes))
    {


        $aAllAttributes=questionAttributes(true);

        foreach ($xml->question_attributes->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            unset($insertdata['qaid']);
            if (!isset($aQIDReplacements[(int)$insertdata['qid']])) continue; // Skip questions with invalid group id
            $insertdata['qid']=$aQIDReplacements[(int)$insertdata['qid']]; // remap the parent_qid


            if ($iDBVersion<156 && isset($aAllAttributes[$insertdata['attribute']]['i18n']) && $aAllAttributes[$insertdata['attribute']]['i18n'])
            {
                foreach ($importlanguages as $sLanguage)
                {
                    $insertdata['language']=$sLanguage;
                    $result = Yii::app()->db->createCommand()->insert('{{question_attributes}}', $insertdata);
                }
            }
            else
            {
                $result = Yii::app()->db->createCommand()->insert('{{question_attributes}}', $insertdata);
            }
            $results['question_attributes']++;
        }
    }


    // Import defaultvalues --------------------------------------------------------------
    if(isset($xml->defaultvalues))
    {


        $results['defaultvalues']=0;
        foreach ($xml->defaultvalues->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['qid']=$aQIDReplacements[(int)$insertdata['qid']]; // remap the qid
            if ($insertdata['sqid']>0) 
            {
                if (!isset($aQIDReplacements[(int)$insertdata['sqid']])) continue;  // If SQID is invalid skip the default value
                $insertdata['sqid']=$aQIDReplacements[(int)$insertdata['sqid']]; // remap the subquestion id    
            }

            // now translate any links
            $result = Yii::app()->db->createCommand()->insert('{{defaultvalues}}', $insertdata);
            $results['defaultvalues']++;
        }
    }

    // Import conditions --------------------------------------------------------------
    if(isset($xml->conditions))
    {


        foreach ($xml->conditions->rows->row as $row)
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
                $newcfieldname = '+'.$iNewSID . "X" . $newgid . "X" . $insertdata["cqid"] .substr($oldqidanscode,strlen($oldqid));
            }
            else
            {
                $newcfieldname = $iNewSID . "X" . $newgid . "X" . $insertdata["cqid"] .substr($oldqidanscode,strlen($oldqid));
            }

            $insertdata["cfieldname"] = $newcfieldname;
            if (trim($insertdata["method"])=='')
            {
                $insertdata["method"]='==';
            }

            // now translate any links
            $result = Yii::app()->db->createCommand()->insert('{{conditions}}', $insertdata);
            $results['conditions']++;
        }
    }
    LimeExpressionManager::RevertUpgradeConditionsToRelevance($iNewSID);
    LimeExpressionManager::UpgradeConditionsToRelevance($iNewSID);

    $results['newgid']=$newgid;
    $results['labelsets']=0;
    $results['labels']=0;
    return $results;
}



/**
* This function imports an old-school question file (*.csv,*.sql)
*
* @param mixed $sFullFilepath Full file patch to the import file
* @param mixed $iNewSID  Survey ID to which the question is attached
* @param mixed $newgid  Group ID top which the question is attached
*/
function CSVImportQuestion($sFullFilepath, $iNewSID, $newgid)
{
    $clang = Yii::app()->lang;

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
        while (trim(reset($answerarray))=='')
        {
            array_shift($answerarray);
        }
        $countanswers = count($answerarray);
    }
    else {$countanswers=0;}
    if (isset($labelsetsarray)) {$countlabelsets = count($labelsetsarray)-1;}  else {$countlabelsets=0;}
    if (isset($labelsarray)) {$countlabels = count($labelsarray)-1;}  else {$countlabels=0;}
    if (isset($question_attributesarray)) {$countquestion_attributes = count($question_attributesarray)-1;} else {$countquestion_attributes=0;}

    $aLanguagesSupported = array();  // this array will keep all the languages supported for the survey

    $sBaseLanguage = Survey::model()->findByPk($iNewSID)->language;
    $aLanguagesSupported[]=$sBaseLanguage;     // adds the base language to the list of supported languages
    $aLanguagesSupported=array_merge($aLanguagesSupported,Survey::model()->findByPk($iNewSID)->additionalLanguages);


    // Let's check that imported objects support at least the survey's baselang

    if (isset($questionarray))
    {
        $langfieldnum = array_search("language", $questionfieldnames);
        $qidfieldnum = array_search("qid", $questionfieldnames);
        $questionssupportbaselang = doesImportArraySupportLanguage($questionarray, array($qidfieldnum), $langfieldnum, $sBaseLanguage);
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
        $answerssupportbaselang = doesImportArraySupportLanguage($answerarray,$answercodekeysarr,$langfieldnum,$sBaseLanguage);
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
        $labelsetssupportbaselang = doesImportArraySupportLanguage($labelsetsarray,Array($lidfilednum),$langfieldnum,$sBaseLanguage,true);
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
            $lsainsert = "INSERT INTO {{labelsets}} (".implode(',',array_keys($labelsetrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
            $lsiresult=Yii::app()->db->createCommand($lsainsert)->query();

            // Get the new insert id for the labels inside this labelset
            $newlid=getLastInsertID('{{labelsets}}');

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
                        $labelrowdata['title']=translateLinks('label', $oldlid, $newlid, $labelrowdata['title']);

                        $newvalues=array_values($labelrowdata);
                        if ($newvalues)
                            XSSFilterArray($newvalues);
                        $lainsert = "INSERT INTO {{labels}} (".implode(',',array_keys($labelrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
                        $liresult=Yii::app()->db->createCommand($lainsert)->query();
                        $results['labels']++;
                    }
                }
            }

            //CHECK FOR DUPLICATE LABELSETS
            $thisset="";
            $query2 = "SELECT code, title, sortorder, language, assessment_value
            FROM {{labels}}
            WHERE lid=".$newlid."
            ORDER BY language, sortorder, code";
            $result2 = Yii::app()->db->createCommand($query2)->query() or safeDie("Died querying labelset $lid<br />$query2<br />");

            foreach($result2->readAll() as $row2)
            {
                $row2 = array_values($row2);
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
                $query = "DELETE FROM {{labels}} WHERE lid=$newlid";
                $result=Yii::app()->db->createCommand($query)->query() or safeDie("Couldn't delete labels<br />$query<br />");
                $query = "DELETE FROM {{labelsets}} WHERE lid=$newlid";
                $result=Yii::app()->db->createCommand($query)->query() or safeDie("Couldn't delete labelset<br />$query<br />");
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
        $query = "SELECT MAX(question_order) AS maxqo FROM {{questions}} WHERE sid=$iNewSID AND gid=$newgid";
        $aRow = Yii::app()->db->createCommand($query)->queryRow();

        if ($aRow == false)
        {
            $newquestionorder=0;
        }
        else
        {
            $newquestionorder = $aRow['maxqo'];
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
            $iOldSID = $questionrowdata['sid'];
            $oldgid = $questionrowdata['gid'];

            // Remove qid field if there is no newqid; and set it to newqid if it's set
            if (!isset($newqid))
            {
                unset($questionrowdata['qid']);
            }
            else
            {
                $questionrowdata['qid'] = $newqid;
            }

            $questionrowdata["sid"] = $iNewSID;
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

            $questionrowdata=array_map('convertCSVReturnToReturn', $questionrowdata);

            // translate internal links
            $questionrowdata['question']=translateLinks('survey', $iOldSID, $iNewSID, $questionrowdata['question']);
            $questionrowdata['help']=translateLinks('survey', $iOldSID, $iNewSID, $questionrowdata['help']);

            $newvalues=array_values($questionrowdata);
            if ($newvalues)
                XSSFilterArray($newvalues);
            $questionrowdata=array_combine(array_keys($questionrowdata),$newvalues);
            $iQID=Questions::model()->insertRecords($questionrowdata);

            // set the newqid only if is not set
            if (!isset($newqid))
            {
                $newqid=$iQID;
            }
        }
        $qtypes = getQuestionTypeList("" ,"array");
        $results['answers']=0;
        $results['subquestions']=0;


        // Now we will fix up old label sets where they are used as answers
        if ((isset($oldquestion['lid1']) || isset($oldquestion['lid2'])) && ($qtypes[$oldquestion['newtype']]['answerscales']>0 || $qtypes[$oldquestion['newtype']]['subquestions']>1))
        {
            $query="select * from {{labels}} where lid={$aLIDReplacements[$oldquestion['lid1']]} ";
            $oldlabelsresult=Yii::app()->db->createCommand($query)->query();
            foreach($oldlabelsresult->readAll() as $labelrow)
            {
                if (in_array($labelrow['language'],$aLanguagesSupported)){
                    if ($labelrow)
                        XSSFilterArray($labelrow);
                    if ($qtypes[$oldquestion['newtype']]['subquestions']<2)
                    {
                        $qinsert = "insert INTO {{answers}} (qid,code,answer,sortorder,language,assessment_value,scale_id)
                        VALUES ($newqid,'".$labelrow['code']."','".$labelrow['title']."','".$labelrow['sortorder']."','".$labelrow['language']."','".$labelrow['assessment_value']."',0)";
                        $qres = Yii::app()->db->createCommand($qinsert)->query() or safeDie ("Error: Failed to insert answer <br />\n$qinsert<br />\n");
                        $results['answers']++;
                    }
                    else
                    {
                        if (isset($aSQIDReplacements[$labelrow['code']])){
                            $fieldname='qid,';
                            $data=$aSQIDReplacements[$labelrow['code']].',';
                        }
                        else{
                            $fieldname='' ;
                            $data='';
                        }

                        $qinsert = "insert INTO {{questions}} ($fieldname sid,gid,parent_qid,title,question,question_order,language,scale_id,type)
                        VALUES ($data $iNewSID,$newgid,$newqid,'".$labelrow['code']."','".$labelrow['title']."','".$labelrow['sortorder']."','".$labelrow['language']."',1,'".$oldquestion['newtype']."')";
                        $qres = Yii::app()->db->createCommand($qinsert)->query() or safeDie ("Error: Failed to insert subquestion <br />\n$qinsert<br />\n");
                        if ($fieldname=='')
                        {
                            $aSQIDReplacements[$labelrow['code']]=getLastInsertID('{{questions}}');
                        }

                    }
                }
            }

            if (isset($oldquestion['lid2']) && $qtypes[$oldquestion['newtype']]['answerscales']>1)
            {
                $query="select * from {{labels}} where lid={$aLIDReplacements[$oldquestion['lid2']]}";
                $oldlabelsresult=Yii::app()->db->createCommand($query)->query();
                foreach($oldlabelsresult->readAll() as $labelrow)
                {
                    if ($labelrow)
                        XSSFilterArray($labelrow);
                    if (in_array($labelrow['language'],$aLanguagesSupported)){
                        $qinsert = "insert INTO {{answers}} (qid,code,answer,sortorder,language,assessment_value,scale_id)
                        VALUES ($newqid,'".$labelrow['code']."','".$labelrow['title']."','".$labelrow['sortorder']."','".$labelrow['language']."','".$labelrow['assessment_value']."',1)";
                        $qres = Yii::app()->db->createCommand($qinsert)->query() or safeDie($clang->gT("Error").": Failed to insert answer <br />\n$qinsert<br />\n");
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

                    $dvalue = new Defaultvalues;
                    foreach ($insertdata as $k => $v)
                        $dvalue->$k = $v;
                    $qres = $dvalue->save();

                }
                // translate internal links
                $answerrowdata['answer']=translateLinks('survey', $iOldSID, $iNewSID, $answerrowdata['answer']);
                // Everything set - now insert it
                $answerrowdata = array_map('convertCSVReturnToReturn', $answerrowdata);

                if ($qtypes[$oldquestion['newtype']]['subquestions']>0) //hmmm.. this is really a subquestion
                {
                    $questionrowdata=array();
                    if (isset($aSQIDReplacements[$answerrowdata['code'].$answerrowdata['qid']])){
                        $questionrowdata['qid']=$aSQIDReplacements[$answerrowdata['code'].$answerrowdata['qid']];
                    }
                    $questionrowdata['parent_qid']=$answerrowdata['qid'];
                    $questionrowdata['sid']=$iNewSID;
                    $questionrowdata['gid']=$newgid;
                    $questionrowdata['title']=$answerrowdata['code'];
                    $questionrowdata['question']=$answerrowdata['answer'];
                    $questionrowdata['question_order']=$answerrowdata['sortorder'];
                    $questionrowdata['language']=$answerrowdata['language'];
                    $questionrowdata['type']=$oldquestion['newtype'];
                    if ($questionrowdata)
                        XSSFilterArray($questionrowdata);
                    $question = new Questions;
                    foreach ($questionrowdata as $k => $v)
                        $question->$k = $v;
                    $qres = $question->save();
                    if (!isset($questionrowdata['qid']))
                    {
                        $aSQIDReplacements[$answerrowdata['code'].$answerrowdata['qid']]=getLastInsertID($question->tableName());
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

                        $qres = $CI->defaultvalues_model->insertRecords($insertdata) or safeDie("Error: Failed to insert defaultvalue <br />\n");
                    }

                }
                else   // insert answers
                {
                    unset($answerrowdata['default_value']);

                    $answer = new Answers;
                    foreach ($answerrowdata as $k => $v)
                        $answer->$k = $v;
                    $ares = $answer->save();
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
                $attr = new Question_attributes;
                if ($qarowdata)
                    XSSFilterArray($qarowdata);
                foreach ($qarowdata as $k => $v)
                    $attr->$k = $v;
                $result = $attr->save();
                $results['question_attributes']++;

            }
        }

    }
    LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting

    $results['newqid']=$newqid;
    $results['questions']=1;
    $results['newqid']=$newqid;
    return $results;
}



/**
* This function imports a LimeSurvey .lsq question XML file
*
* @param mixed $sFullFilepath  The full filepath of the uploaded file
* @param mixed $iNewSID The new survey id
* @param mixed $newgid The new question group id -the question will always be added after the last question in the group
*/
function XMLImportQuestion($sFullFilepath, $iNewSID, $newgid)
{
    $clang = Yii::app()->lang;
    $aLanguagesSupported = array();  // this array will keep all the languages supported for the survey

    $sBaseLanguage = Survey::model()->findByPk($iNewSID)->language;
    $aLanguagesSupported[]=$sBaseLanguage;     // adds the base language to the list of supported languages
    $aLanguagesSupported=array_merge($aLanguagesSupported,Survey::model()->findByPk($iNewSID)->additionalLanguages);

    $xml = simplexml_load_file($sFullFilepath);
    if ($xml->LimeSurveyDocType!='Question') safeDie('This is not a valid LimeSurvey question structure XML file.');
    $iDBVersion = (int) $xml->DBVersion;
    $aQIDReplacements=array();
    $aSQIDReplacements=array(0=>0);

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


    $query = "SELECT MAX(question_order) AS maxqo FROM {{questions}} WHERE sid=$iNewSID AND gid=$newgid";
    $res = Yii::app()->db->createCommand($query)->query();
    $resrow = $res->read();
    $newquestionorder = $resrow['maxqo'] + 1;
    if (is_null($newquestionorder))
    {
        $newquestionorder=0;
    }
    else
    {
        $newquestionorder++;
    }
    foreach ($xml->questions->rows->row as $row)
    {
        $insertdata=array();
        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }
        $iOldSID=$insertdata['sid'];
        $insertdata['sid']=$iNewSID;
        $insertdata['gid']=$newgid;
        $insertdata['question_order']=$newquestionorder;
        $oldqid=$insertdata['qid']; unset($insertdata['qid']); // save the old qid

        // now translate any links
        $insertdata['title']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['title']);
        $insertdata['question']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
        $insertdata['help']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']);
        // Insert the new question
        if (isset($aQIDReplacements[$oldqid]))
        {
            $insertdata['qid']=$aQIDReplacements[$oldqid];
        }

        $ques = new Questions;
        if ($insertdata)
            XSSFilterArray($insertdata);
        foreach ($insertdata as $k => $v)
            $ques->$k = $v;
        $result = $ques->save();
        if (!isset($aQIDReplacements[$oldqid]))
        {
            $newqid=getLastInsertID($ques->tableName());
            $aQIDReplacements[$oldqid]=$newqid; // add old and new qid to the mapping array
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
            $insertdata['sid']=$iNewSID;
            $insertdata['gid']=$newgid;
            $oldsqid=(int)$insertdata['qid']; unset($insertdata['qid']); // save the old qid
            $insertdata['parent_qid']=$aQIDReplacements[(int)$insertdata['parent_qid']]; // remap the parent_qid

            // now translate any links

            $insertdata['question']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
            if (isset($insertdata['help']))
            {
                $insertdata['help']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']);
            }
            if (isset($aQIDReplacements[$oldsqid])){
                $insertdata['qid']=$aQIDReplacements[$oldsqid];
            }
            if ($insertdata)
                XSSFilterArray($insertdata);
            $ques = new Questions;
            foreach ($insertdata as $k => $v)
                $ques->$k = $v;
            $result = $ques->save();
            $newsqid=getLastInsertID($ques->tableName());
            if (!isset($insertdata['qid']))
            {
                $aQIDReplacements[$oldsqid]=$newsqid; // add old and new qid to the mapping array
            }
            $results['subquestions']++;
        }
    }

    // Import answers --------------------------------------------------------------
    if(isset($xml->answers))
    {


        foreach ($xml->answers->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['qid']=$aQIDReplacements[(int)$insertdata['qid']]; // remap the parent_qid

            // now translate any links
            $answers = new Answers;
            if ($insertdata)
                XSSFilterArray($insertdata);
            foreach ($insertdata as $k => $v)
                $answers->$k = $v;
            $result = $answers->save();
            $results['answers']++;
        }
    }

    // Import questionattributes --------------------------------------------------------------
    if(isset($xml->question_attributes))
    {


        $aAllAttributes=questionAttributes(true);
        foreach ($xml->question_attributes->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            unset($insertdata['qaid']);
            $insertdata['qid']=$aQIDReplacements[(integer)$insertdata['qid']]; // remap the parent_qid


            if ($iDBVersion<156 && isset($aAllAttributes[$insertdata['attribute']]['i18n']) && $aAllAttributes[$insertdata['attribute']]['i18n'])
            {
                foreach ($importlanguages as $sLanguage)
                {
                    $insertdata['language']=$sLanguage;
                    $attributes = new Question_attributes;
                    if ($insertdata)
                        XSSFilterArray($insertdata);
                    foreach ($insertdata as $k => $v)
                        $attributes->$k = $v;
                    $result = $attributes->save();
                }
            }
            else
            {
                $attributes = new Question_attributes;
                if ($insertdata)
                    XSSFilterArray($insertdata);
                foreach ($insertdata as $k => $v)
                    $attributes->$k = $v;
                $result = $attributes->save();
            }
            $results['question_attributes']++;
        }
    }


    // Import defaultvalues --------------------------------------------------------------
    if(isset($xml->defaultvalues))
    {


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
            $default = new Defaultvalues;
            if ($insertdata)
                XSSFilterArray($insertdata);
            foreach ($insertdata as $k => $v)
                $default->$k = $v;
            $result = $default->save();
            $results['defaultvalues']++;
        }
    }
    LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting

    $results['newqid']=$newqid;
    $results['questions']=1;
    $results['labelsets']=0;
    $results['labels']=0;
    return $results;
}




/**
* CSVImportLabelset()
* Function responsible to import label set from CSV format.
* @param mixed $sFullFilepath
* @param mixed $options
* @return
*/
function CSVImportLabelset($sFullFilepath, $options)
{
    $clang = Yii::app()->lang;
    $results['labelsets']=0;
    $results['labels']=0;
    $results['warnings']=array();
    $csarray=buildLabelSetCheckSumArray();
    //$csarray is now a keyed array with the Checksum of each of the label sets, and the lid as the key

    $handle = fopen($sFullFilepath, "r");
    while (!feof($handle))
    {
        $buffer = fgets($handle); //To allow for very long survey welcomes (up to 10k)
        $bigarray[] = $buffer;
    }
    fclose($handle);
    if (substr($bigarray[0], 0, 27) != "# LimeSurvey Label Set Dump" && substr($bigarray[0], 0, 28) != "# PHPSurveyor Label Set Dump")
    {
        $results['fatalerror']=$clang->gT("This file is not a LimeSurvey label set file. Import failed.");
        return $results;
    }

    for ($i=0; $i<9; $i++) //skipping the first lines that are not needed
    {
        unset($bigarray[$i]);
    }
    $bigarray = array_values($bigarray);

    //LABEL SETS
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
    $stoppoint = count($bigarray)-1;

    for ($i=0; $i<$stoppoint; $i++)
    {
        // do not import empty lines
        if (trim($bigarray[$i])!='')
        {
            $labelsarray[] = $bigarray[$i];
        }
        unset($bigarray[$i]);
    }



    $countlabelsets = count($labelsetsarray)-1;
    $countlabels = count($labelsarray)-1;


    if (isset($labelsetsarray) && $labelsetsarray) {
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

            if ($newvalues)
                XSSFilterArray($newvalues);
            // Insert the label set entry and get the new insert id for the labels inside this labelset
            $newlid=Labelsets::model()->insertRecords($labelsetrowdata);
            $results['labelsets']++;


            if ($labelsarray) {
                $count=0;
                $lfieldorders=convertCSVRowToArray($labelsarray[0],',','"');
                unset($labelsarray[0]);
                foreach ($labelsarray as $la) {

                    $lfieldcontents=convertCSVRowToArray($la,',','"');
                    // Combine into one array with keys and values since its easier to handle
                    $labelrowdata=array_combine($lfieldorders,$lfieldcontents);
                    $labellid=$labelrowdata['lid'];

                    if ($labellid == $oldlid) {
                        $labelrowdata['lid']=$newlid;

                        // translate internal links
                        $labelrowdata['title']=translateLinks('label', $oldlid, $newlid, $labelrowdata['title']);
                        if (!isset($labelrowdata["assessment_value"]))
                        {
                            $labelrowdata["assessment_value"]=(int)$labelrowdata["code"];
                        }

                        if ($newvalues)
                            XSSFilterArray($newvalues);
                        Label::model()->insertRecords($labelrowdata);
                        $results['labels']++;
                    }
                }
            }

            //CHECK FOR DUPLICATE LABELSETS

            if (isset($_POST['checkforduplicates']))
            {
                $thisset="";
                $query2 = "SELECT code, title, sortorder, language, assessment_value
                FROM {{labels}}
                WHERE lid=".$newlid."
                ORDER BY language, sortorder, code";
                $result2 = Yii::app()->db->createCommand($query2)->query() or safeDie("Died querying labelset $lid<br />$query2<br />");
                foreach($result2->readAll() as $row2)
                {
                    $row2 = array_values($row2);
                    $thisset .= implode('.', $row2);
                } // while
                $newcs=dechex(crc32($thisset)*1);
                unset($lsmatch);

                if (isset($csarray) && $options['checkforduplicates']=='on')
                {
                    foreach($csarray as $key=>$val)
                    {
                        //            echo $val."-".$newcs."<br/>";  For debug purposes
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
                    $query = "DELETE FROM {{labels}} WHERE lid=$newlid";
                    $result = Yii::app()->db->createCommand($query)->execute() or safeDie("Couldn't delete labels<br />$query<br />");
                    $query = "DELETE FROM {{labelsets}} WHERE lid=$newlid";
                    $result = Yii::app()->db->createCommand($query)->execute() or safeDie("Couldn't delete labelset<br />$query<br />");
                    $newlid=$lsmatch;
                    $results['warnings'][]=$clang->gT("Label set was not imported because the same label set already exists.")." ".sprintf($clang->gT("Existing LID: %s"),$newlid);

                }
                //END CHECK FOR DUPLICATES
            }
        }
    }

    return $results;
}


/**
* XMLImportLabelsets()
* Function resp[onsible to import a labelset from XML format.
* @param mixed $sFullFilepath
* @param mixed $options
* @return
*/
function XMLImportLabelsets($sFullFilepath, $options)
{
    $clang = Yii::app()->lang;
    $xml = simplexml_load_file($sFullFilepath);
    if ($xml->LimeSurveyDocType!='Label set') safeDie('This is not a valid LimeSurvey label set structure XML file.');
    $iDBVersion = (int) $xml->DBVersion;
    $csarray=buildLabelSetCheckSumArray();
    $aLSIDReplacements=array();
    $results['labelsets']=0;
    $results['labels']=0;
    $results['warnings']=array();

    // Import labels table ===================================================================================


    foreach ($xml->labelsets->rows->row as $row)
    {
        $insertdata=array();
        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }
        $oldlsid=$insertdata['lid'];
        unset($insertdata['lid']); // save the old qid

        if ($insertdata)
            XSSFilterArray($insertdata);
        // Insert the new question
        $result = Yii::app()->db->createCommand()->insert('{{labelsets}}', $insertdata);
        $results['labelsets']++;

        $newlsid=getLastInsertID('{{labelsets}}');
        $aLSIDReplacements[$oldlsid]=$newlsid; // add old and new lsid to the mapping array
    }


    // Import labels table ===================================================================================


    if (isset($xml->labels->rows->row))
        foreach ($xml->labels->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['lid']=$aLSIDReplacements[$insertdata['lid']];
            if ($insertdata)
                XSSFilterArray($insertdata);
            $result = Yii::app()->db->createCommand()->insert('{{labels}}', $insertdata);
            $results['labels']++;
    }

    //CHECK FOR DUPLICATE LABELSETS

    if (isset($_POST['checkforduplicates']))
    {
        foreach (array_values($aLSIDReplacements) as $newlid)
        {
            $thisset="";
            $query2 = "SELECT code, title, sortorder, language, assessment_value
            FROM {{labels}}
            WHERE lid=".$newlid."
            ORDER BY language, sortorder, code";
            $result2 = Yii::app()->db->createCommand($query2)->query();
            foreach($result2->readAll() as $row2)
            {
                $row2 = array_values($row2);
                $thisset .= implode('.', $row2);
            } // while
            $newcs=dechex(crc32($thisset)*1);
            unset($lsmatch);

            if (isset($csarray) && $options['checkforduplicates']=='on')
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
                $query = "DELETE FROM {{labels}} WHERE lid=$newlid";
                $result=Yii::app()->db->createCommand($query)->execute();
                $results['labels']=$results['labels']-$result;
                $query = "DELETE FROM {{labelsets}} WHERE lid=$newlid";
                $result=Yii::app()->db->createCommand($query)->query();

                $results['labelsets']--;
                $newlid=$lsmatch;
                $results['warnings'][]=$clang->gT("Label set was not imported because the same label set already exists.")." ".sprintf($clang->gT("Existing LID: %s"),$newlid);

            }
        }
        //END CHECK FOR DUPLICATES
    }
    return $results;
}



/**
* This function imports the old CSV data from 1.50 to 1.87 or older. Starting with 1.90 (DBVersion 143) there is an XML format instead
*
* @param array $sFullFilepath
* @returns array Information of imported questions/answers/etc.
*/
function CSVImportSurvey($sFullFilepath,$iDesiredSurveyId=NULL,$bTranslateLinks=true)
{
    Yii::app()->loadHelper('database');
    $clang = Yii::app()->lang;

    $handle = fopen($sFullFilepath, "r");
    while (!feof($handle))
    {

        $buffer = fgets($handle);
        $bigarray[] = $buffer;
    }
    fclose($handle);

    $aIgnoredAnswers=array();
    $aSQIDReplacements=array();
    $aLIDReplacements=array();
    $aGIDReplacements=array();
    $substitutions=array();
    $aQuotaReplacements=array();
    $importresults['error']=false;
    $importresults['importwarnings']=array();
    $importresults['question_attributes']=0;

    if (isset($bigarray[0])) $bigarray[0]=removeBOM($bigarray[0]);

    // Now we try to determine the dataformat of the survey file.
    $importversion=0;
    if (isset($bigarray[1]) && isset($bigarray[4])&& (substr($bigarray[1], 0, 22) == "# SURVEYOR SURVEY DUMP"))
    {
        $importversion = 100;  // Version 0.99 or  1.0 file
    }
    elseif
    (substr($bigarray[0], 0, 24) == "# LimeSurvey Survey Dump" || substr($bigarray[0], 0, 25) == "# PHPSurveyor Survey Dump")
    {  // Seems to be a >1.0 version file - these files carry the version information to read in line two
        $importversion=substr($bigarray[1], 12, 3);
    }
    else    // unknown file - show error message
    {
        $importresults['error'] = $clang->gT("This file is not a LimeSurvey survey file. Import failed.")."\n";
        return $importresults;
    }

    if  ((int)$importversion<112)
    {
        $importresults['error'] = $clang->gT("This file is too old. Only files from LimeSurvey version 1.50 (DBVersion 112) and newer are supported.");
        return $importresults;
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

    //Question attributes
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
        //    if ($i<$stoppoint-2 || $i==count($bigarray)-1)
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
        //    if ($i<$stoppoint-2 || $i==count($bigarray)-1)
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
        //    if ($i<$stoppoint-2 || $i==count($bigarray)-1)
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
        //    if ($i<$stoppoint-2 || $i==count($bigarray)-1)
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

    if (isset($surveyarray)) {$importresults['surveys'] = count($surveyarray);} else {$importresults['surveys'] = 0;}
    if (isset($surveylsarray)) {$importresults['languages'] = count($surveylsarray)-1;} else {$importresults['languages'] = 1;}
    if (isset($grouparray)) {$importresults['groups'] = count($grouparray)-1;} else {$importresults['groups'] = 0;}
    if (isset($questionarray)) {$importresults['questions'] = count($questionarray);} else {$importresults['questions']=0;}
    if (isset($answerarray)) {$importresults['answers'] = count($answerarray);} else {$importresults['answers']=0;}
    if (isset($conditionsarray)) {$importresults['conditions'] = count($conditionsarray);} else {$importresults['conditions']=0;}
    if (isset($labelsetsarray)) {$importresults['labelsets'] = count($labelsetsarray);} else {$importresults['labelsets']=0;}
    if (isset($assessmentsarray)) {$importresults['assessments']=count($assessmentsarray);} else {$importresults['assessments']=0;}
    if (isset($quotaarray)) {$importresults['quota']=count($quotaarray);} else {$importresults['quota']=0;}
    if (isset($quotamembersarray)) {$importresults['quotamembers']=count($quotamembersarray);} else {$importresults['quotamembers']=0;}
    if (isset($quotalsarray)) {$importresults['quotals']=count($quotalsarray);} else {$importresults['quotals']=0;}

    // CREATE SURVEY

    if ($importresults['surveys']>0){$importresults['surveys']--;};
    if ($importresults['answers']>0){$importresults['answers']=($importresults['answers']-1)/$importresults['languages'];};
    if ($importresults['groups']>0){$countgroups=($importresults['groups']-1)/$importresults['languages'];};
    if ($importresults['questions']>0){$importresults['questions']=($importresults['questions']-1)/$importresults['languages'];};
    if ($importresults['assessments']>0){$importresults['assessments']--;};
    if ($importresults['conditions']>0){$importresults['conditions']--;};
    if ($importresults['labelsets']>0){$importresults['labelsets']--;};
    if ($importresults['quota']>0){$importresults['quota']--;};
    $sfieldorders  =convertCSVRowToArray($surveyarray[0],',','"');
    $sfieldcontents=convertCSVRowToArray($surveyarray[1],',','"');
    $surveyrowdata=array_combine($sfieldorders,$sfieldcontents);
    $iOldSID=$surveyrowdata["sid"];

    if (!$iOldSID)
    {
        if ($importingfrom == "http")
        {
            $importsurvey .= "<br /><div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
            $importsurvey .= $clang->gT("Import of this survey file failed")."<br />\n";
            $importsurvey .= $clang->gT("File does not contain LimeSurvey data in the correct format.")."<br /><br />\n"; //Couldn't find the SID - cannot continue
            $importsurvey .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";
            $importsurvey .= "</div>\n";
            unlink($sFullFilepath); //Delete the uploaded file
            return;
        }
        else
        {
            $clang->eT("Import of this survey file failed")."\n".$clang->gT("File does not contain LimeSurvey data in the correct format.")."\n";
            return;
        }
    }
    if($iDesiredSurveyId!=NULL)
    {
        $iNewSID = GetNewSurveyID($iDesiredSurveyId);
    }
    else
    {
        $iNewSID = GetNewSurveyID($iOldSID);
    }


    $insert=$surveyarray[0];
    $sfieldorders  =convertCSVRowToArray($surveyarray[0],',','"');
    $sfieldcontents=convertCSVRowToArray($surveyarray[1],',','"');
    $surveyrowdata=array_combine($sfieldorders,$sfieldcontents);
    // Set new owner ID
    $surveyrowdata['owner_id']=Yii::app()->session['loginID'];
    // Set new survey ID
    $surveyrowdata['sid']=$iNewSID;
    $surveyrowdata['active']='N';

    if (validateTemplateDir($surveyrowdata['template'])!==$surveyrowdata['template']) $importresults['importwarnings'][] = sprintf($clang->gT('Template %s not found, please review when activating.'),$surveyrowdata['template']);

    //if (isset($surveyrowdata['datecreated'])) {$surveyrowdata['datecreated'] = $connect->BindTimeStamp($surveyrowdata['datecreated']);}
    unset($surveyrowdata['expires']);
    unset($surveyrowdata['attribute1']);
    unset($surveyrowdata['attribute2']);
    unset($surveyrowdata['usestartdate']);
    unset($surveyrowdata['notification']);
    unset($surveyrowdata['useexpiry']);
    unset($surveyrowdata['url']);
    unset($surveyrowdata['lastpage']);
    if (isset($surveyrowdata['private'])){
        $surveyrowdata['anonymized']=$surveyrowdata['private'];
        unset($surveyrowdata['private']);
    }
    if (isset($surveyrowdata['startdate'])) {unset($surveyrowdata['startdate']);}
    $surveyrowdata['bounce_email']=$surveyrowdata['adminemail'];
    if (empty($surveyrowdata['datecreated'])) {$surveyrowdata['datecreated'] = new CDbExpression('NOW()'); }

    $iNewSID = Survey::model()->insertNewSurvey($surveyrowdata) or safeDie ("<br />".$clang->gT("Import of this survey file failed")."<br />{$surveyarray[0]}<br /><br />\n" );

    // Now import the survey language settings
    $fieldorders=convertCSVRowToArray($surveylsarray[0],',','"');
    unset($surveylsarray[0]);
    foreach ($surveylsarray as $slsrow) {
        $fieldcontents=convertCSVRowToArray($slsrow,',','"');
        $surveylsrowdata=array_combine($fieldorders,$fieldcontents);
        // convert back the '\'.'n' char from the CSV file to true return char "\n"
        $surveylsrowdata=array_map('convertCSVReturnToReturn', $surveylsrowdata);
        // Convert the \n return char from welcometext to <br />

        // translate internal links
        if ($bTranslateLinks)
        {
            $surveylsrowdata['surveyls_title']=translateLinks('survey', $iOldSID, $iNewSID, $surveylsrowdata['surveyls_title']);
            $surveylsrowdata['surveyls_description']=translateLinks('survey', $iOldSID, $iNewSID, $surveylsrowdata['surveyls_description']);
            $surveylsrowdata['surveyls_welcometext']=translateLinks('survey', $iOldSID, $iNewSID, $surveylsrowdata['surveyls_welcometext']);
            $surveylsrowdata['surveyls_urldescription']=translateLinks('survey', $iOldSID, $iNewSID, $surveylsrowdata['surveyls_urldescription']);
            $surveylsrowdata['surveyls_email_invite']=translateLinks('survey', $iOldSID, $iNewSID, $surveylsrowdata['surveyls_email_invite']);
            $surveylsrowdata['surveyls_email_remind']=translateLinks('survey', $iOldSID, $iNewSID, $surveylsrowdata['surveyls_email_remind']);
            $surveylsrowdata['surveyls_email_register']=translateLinks('survey', $iOldSID, $iNewSID, $surveylsrowdata['surveyls_email_register']);
            $surveylsrowdata['surveyls_email_confirm']=translateLinks('survey', $iOldSID, $iNewSID, $surveylsrowdata['surveyls_email_confirm']);
        }
        unset($surveylsrowdata['lastpage']);
        $surveylsrowdata['surveyls_survey_id']=$iNewSID;

        $lsiresult = Surveys_languagesettings::model()->insertNewSurvey($surveylsrowdata) or safeDie("<br />".$clang->gT("Import of this survey file failed")."<br />");

    }

    // The survey languagesettings are imported now
    $aLanguagesSupported = array();  // this array will keep all the languages supported for the survey

    $sBaseLanguage = Survey::model()->findByPk($iNewSID)->language;
    $aLanguagesSupported[]=$sBaseLanguage;     // adds the base language to the list of supported languages
    $aLanguagesSupported=array_merge($aLanguagesSupported,Survey::model()->findByPk($iNewSID)->additionalLanguages);


    // DO SURVEY_RIGHTS

    Survey_permissions::model()->giveAllSurveyPermissions(Yii::app()->session['loginID'],$iNewSID);

    $importresults['deniedcountls'] =0;


    $qtypes = getQuestionTypeList("" ,"array");
    $results['labels']=0;
    $results['labelsets']=0;
    $results['answers']=0;
    $results['subquestions']=0;

    //Do label sets
    if (isset($labelsetsarray) && $labelsetsarray)
    {
        $csarray=buildLabelSetCheckSumArray();   // build checksums over all existing labelsets
        $count=0;
        foreach ($labelsetsarray as $lsa) {
            $fieldorders  =convertCSVRowToArray($labelsetsarray[0],',','"');
            $fieldcontents=convertCSVRowToArray($lsa,',','"');
            if ($count==0) {$count++; continue;}

            $labelsetrowdata=array_combine($fieldorders,$fieldcontents);

            // Save old labelid
            $oldlid=$labelsetrowdata['lid'];

            unset($labelsetrowdata['lid']);

            $lblsets=Labelsets::model();
            $lsiresult = $lblsets->insertRecords($labelsetrowdata);

            $results['labelsets']++;
            // Get the new insert id for the labels inside this labelset
            $newlid=Yii::app()->db->createCommand('Select LAST_INSERT_ID()')->query()->read();
            $newlid=$newlid['LAST_INSERT_ID()'];

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
                        if ($bTranslateLinks) $labelrowdata['title']=translateLinks('label', $oldlid, $newlid, $labelrowdata['title']);

                        $liresult = Label::model()->insertRecords($labelrowdata);

                        if ($liresult!==false) $results['labels']++;
                    }
                }
            }

            //CHECK FOR DUPLICATE LABELSETS
            $thisset="";

            $query2 = "SELECT code, title, sortorder, language, assessment_value
            FROM {{labels}}
            WHERE lid=".$newlid."
            ORDER BY language, sortorder, code";
            $result2 = Yii::app()->db->createCommand($query2)->query() or die("Died querying labelset $lid<br />");

            foreach($result2->readAll() as $row2)
            {
                $row2 = array_values($row2);
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
            if (isset($lsmatch) || Yii::app()->session['USER_RIGHT_MANAGE_LABEL'] != 1)
            {
                //There is a matching labelset or the user is not allowed to edit labels -
                // So, we will delete this one and refer to the matched one.

                $query = "DELETE FROM {{labels}} WHERE lid=$newlid";
                $result=Yii::app()->db->createCommand($query)->execute();
                $results['labels']=$results['labels']-$result;

                $query = "DELETE FROM {{labelsets}} WHERE lid=$newlid";
                $result=Yii::app()->db->createCommand($query)->execute();
                $results['labelsets']=$results['labelsets']-$result;
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
        foreach ($grouparray as $ga)
        {
            $gacfieldcontents=convertCSVRowToArray($ga,',','"');
            $grouprowdata=array_combine($gafieldorders,$gacfieldcontents);

            //Now an additional integrity check if there are any groups not belonging into this survey
            if ($grouprowdata['sid'] != $iOldSID)
            {
                $results['fatalerror'] = $clang->gT("A group in the CSV/SQL file is not part of the same survey. The import of the survey was stopped.")."<br />\n";
                return $results;
            }
            $grouprowdata['sid']=$iNewSID;
            // remember group id
            $oldgid=$grouprowdata['gid'];

            //update/remove the old group id
            if (isset($aGIDReplacements[$oldgid]))
                $grouprowdata['gid'] = $aGIDReplacements[$oldgid];
            else
                unset($grouprowdata['gid']);

            // Everything set - now insert it
            $grouprowdata=array_map('convertCSVReturnToReturn', $grouprowdata);

            // translate internal links
            if ($bTranslateLinks)
            {
                $grouprowdata['group_name']=translateLinks('survey', $iOldSID, $iNewSID, $grouprowdata['group_name']);
                $grouprowdata['description']=translateLinks('survey', $iOldSID, $iNewSID, $grouprowdata['description']);
            }

            if (isset($grouprowdata['gid'])) switchMSSQLIdentityInsert('groups',true);


            $gres = Groups::model()->insertRecords($grouprowdata) or safeDie($clang->gT('Error').": Failed to insert group<br />\<br />\n");

            if (isset($grouprowdata['gid'])) switchMSSQLIdentityInsert('groups',false);
            if (!isset($grouprowdata['gid']))
            {
                $aGIDReplacements[$oldgid]=Yii::app()->db->createCommand('Select LAST_INSERT_ID()')->query()->read();
                $aGIDReplacements[$oldgid]=$aGIDReplacements[$oldgid]['LAST_INSERT_ID()'];
            }
        }
        // Fix sortorder of the groups  - if users removed groups manually from the csv file there would be gaps
        fixSortOrderGroups($iNewSID);
    }
    // GROUPS is DONE

    // Import questions
    if (isset($questionarray) && $questionarray)
    {
        $qafieldorders=convertCSVRowToArray($questionarray[0],',','"');
        unset($questionarray[0]);
        foreach ($questionarray as $qa)
        {
            $qacfieldcontents=convertCSVRowToArray($qa,',','"');
            $questionrowdata=array_combine($qafieldorders,$qacfieldcontents);
            $questionrowdata=array_map('convertCSVReturnToReturn', $questionrowdata);
            $questionrowdata["type"]=strtoupper($questionrowdata["type"]);

            // Skip not supported languages
            if (!in_array($questionrowdata['language'],$aLanguagesSupported))
                continue;

            // replace the sid
            $questionrowdata["sid"] = $iNewSID;
            // Skip if gid is invalid
            if (!isset($aGIDReplacements[$questionrowdata['gid']])) continue;
            $questionrowdata["gid"] = $aGIDReplacements[$questionrowdata['gid']];
            if (isset($aQIDReplacements[$questionrowdata['qid']]))
            {
                $questionrowdata['qid']=$aQIDReplacements[$questionrowdata['qid']];
            }
            else
            {
                $oldqid=$questionrowdata['qid'];
                unset($questionrowdata['qid']);
            }

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
                $aIgnoredAnswers[]=$oldqid;
            }

            if (!isset($questionrowdata["question_order"]) || $questionrowdata["question_order"]=='') {$questionrowdata["question_order"]=0;}
            // translate internal links
            if ($bTranslateLinks)
            {
                $questionrowdata['question']=translateLinks('survey', $iOldSID, $iNewSID, $questionrowdata['question']);
                $questionrowdata['help']=translateLinks('survey', $iOldSID, $iNewSID, $questionrowdata['help']);
            }


            if (isset($questionrowdata['qid'])) {
                switchMSSQLIdentityInsert('questions',true);
            }


            $qres = Questions::model()->insertRecords($questionrowdata) or safeDie ($clang->gT("Error").": Failed to insert question<br />");

            if (isset($questionrowdata['qid'])) {
                switchMSSQLIdentityInsert('questions',false);
                $saveqid=$questionrowdata['qid'];
            }
            else
            {
                $aQIDReplacements[$oldqid]=Yii::app()->db->createCommand('Select LAST_INSERT_ID()')->query()->read();
                $aQIDReplacements[$oldqid]=$aQIDReplacements[$oldqid]['LAST_INSERT_ID()'];
                $saveqid=$aQIDReplacements[$oldqid];
            }

            // Now we will fix up old label sets where they are used as answers
            if (((isset($oldlid1) && isset($aLIDReplacements[$oldlid1])) || (isset($oldlid2) && isset($aLIDReplacements[$oldlid2]))) && ($qtypes[$questionrowdata['type']]['answerscales']>0 || $qtypes[$questionrowdata['type']]['subquestions']>1))
            {

                $query="select * from {{labels}} where lid={$aLIDReplacements[$oldlid1]} and language='{$questionrowdata['language']}'";
                $oldlabelsresult=Yii::app()->db->createCommand($query)->query();
                foreach($oldlabelsresult->readAll() as $labelrow)
                {
                    if (in_array($labelrow['language'],$aLanguagesSupported))
                    {

                        if ($qtypes[$questionrowdata['type']]['subquestions']<2)
                        {
                            $qinsert = "insert INTO {{answers}} (qid,code,answer,sortorder,language,assessment_value)
                            VALUES ({$aQIDReplacements[$oldqid]},'".$labelrow['code']."','".$labelrow['title']."','".$labelrow['sortorder']."','".$labelrow['language']."','".$labelrow['assessment_value']."')";
                            $qres = Yii::app()->db->createCommand($qinsert)->query() or safeDie ($clang->gT("Error").": Failed to insert answer (lid1) <br />\n$qinsert<br />\n");
                        }
                        else
                        {
                            if (isset($aSQIDReplacements[$labelrow['code'].'_'.$saveqid])){
                                $fieldname='qid,';
                                $data=$aSQIDReplacements[$labelrow['code'].'_'.$saveqid].',';
                            }
                            else{
                                $fieldname='' ;
                                $data='';
                            }

                            $qinsert = "insert INTO {{questions}} ($fieldname parent_qid,title,question,question_order,language,scale_id,type, sid, gid)
                            VALUES ($data{$aQIDReplacements[$oldqid]},'".$labelrow['code']."','".$labelrow['title']."','".$labelrow['sortorder']."','".$labelrow['language']."',1,'{$questionrowdata['type']}',{$questionrowdata['sid']},{$questionrowdata['gid']})";
                            $qres = Yii::app()->db->createCommand($qinsert)->query() or safeDie ($clang->gT("Error").": Failed to insert question <br />\n$qinsert<br />\n");
                            if ($fieldname=='')
                            {
                                $aSQIDReplacements[$labelrow['code'].'_'.$saveqid]=getLastInsertID('{{questions}}');
                            }
                        }
                    }
                }
                if (isset($oldlid2) && $qtypes[$questionrowdata['type']]['answerscales']>1)
                {

                    $query="select * from {{labels}} where lid={$aLIDReplacements[$oldlid2]} and language='{$questionrowdata['language']}'";
                    $oldlabelsresult=Yii::app()->db->createCommand($query)->query();
                    foreach($oldlabelsresult->readAll() as $labelrow)

                    {
                        $qinsert = "insert INTO {{answers}} (qid,code,answer,sortorder,language,assessment_value,scale_id)
                        VALUES ({$aQIDReplacements[$oldqid]},'".$labelrow['code']."','".$labelrow['title']."','".$labelrow['sortorder']."','".$labelrow['language']."','".$labelrow['assessment_value']."',1)";
                        $qres = Yii::app()->db->createCommand($qinsert)->query() or safeDie ($clang->gT("Error").": Failed to insert answer (lid2)<br />\n$qinsert<br />\n");
                    }
                }
            }
        }
    }

    //Do answers
    if (isset($answerarray) && $answerarray)
    {
        $answerfieldnames = convertCSVRowToArray($answerarray[0],',','"');
        unset($answerarray[0]);

        foreach ($answerarray as $aa)
        {
            $answerfieldcontents = convertCSVRowToArray($aa,',','"');
            $answerrowdata = array_combine($answerfieldnames,$answerfieldcontents);
            if (in_array($answerrowdata['qid'],$aIgnoredAnswers))
            {
                // Due to a bug in previous LS versions there may be orphaned answers with question type Z (which is now L)
                // this way they are ignored
                continue;
            }
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
            $query1 = 'select type,gid from {{questions}} where qid='.$answerrowdata["qid"];

            $resultquery1 = Yii::app()->db->createCommand($query1)->query();
            $questiontemp=$resultquery1->read();

            $oldquestion['newtype']=$questiontemp['type'];
            $oldquestion['gid']=$questiontemp['gid'];
            if ($answerrowdata['default_value']=='Y' && ($oldquestion['newtype']=='L' || $oldquestion['newtype']=='O' || $oldquestion['newtype']=='!'))
            {
                $insertdata=array();
                $insertdata['qid']=$newqid;
                $insertdata['language']=$answerrowdata['language'];
                $insertdata['defaultvalue']=$answerrowdata['answer'];
                $qres = Defaultvalues::model()->insertRecords($insertdata) or safeDie ("Error: Failed to insert defaultvalue <br />");
            }
            // translate internal links
            if ($bTranslateLinks)
            {
                $answerrowdata['answer']=translateLinks('survey', $iOldSID, $iNewSID, $answerrowdata['answer']);
            }
            // Everything set - now insert it
            $answerrowdata = array_map('convertCSVReturnToReturn', $answerrowdata);

            if ($qtypes[$oldquestion['newtype']]['subquestions']>0) //hmmm.. this is really a subquestion
            {
                $questionrowdata=array();
                if (isset($aSQIDReplacements[$answerrowdata['code'].$answerrowdata['qid']])){
                    $questionrowdata['qid']=$aSQIDReplacements[$answerrowdata['code'].$answerrowdata['qid']];
                }
                $questionrowdata['parent_qid']=$answerrowdata['qid'];;
                $questionrowdata['sid']=$iNewSID;
                $questionrowdata['gid']=$oldquestion['gid'];
                $questionrowdata['title']=$answerrowdata['code'];
                $questionrowdata['question']=$answerrowdata['answer'];
                $questionrowdata['question_order']=$answerrowdata['sortorder'];
                $questionrowdata['language']=$answerrowdata['language'];
                $questionrowdata['type']=$oldquestion['newtype'];



                if (isset($questionrowdata['qid'])) switchMSSQLIdentityInsert('questions',true);
                if ($questionrowdata)
                    XSSFilterArray($questionrowdata);
                $qres= Questions::model()->insertRecords($questionrowdata) or safeDie("Error: Failed to insert subquestion <br />");

                if (!isset($questionrowdata['qid']))
                {
                    $aSQIDReplacements[$answerrowdata['code'].$answerrowdata['qid']]=Yii::app()->db->createCommand('Select LAST_INSERT_ID()')->query()->read();
                    $aSQIDReplacements[$answerrowdata['code'].$answerrowdata['qid']]=$aSQIDReplacements[$answerrowdata['code'].$answerrowdata['qid']]['LAST_INSERT_ID()'];
                }
                else
                {
                    switchMSSQLIdentityInsert('questions',false);
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
                    if ($insertdata)
                        XSSFilterArray($insertdata);
                    $qres = Defaultvalues::model()->insertRecords($insertdata) or safeDie("Error: Failed to insert defaultvalue <br />");
                }

            }
            else   // insert answers
            {
                unset($answerrowdata['default_value']);
                if ($answerrowdata)
                    XSSFilterArray($answerrowdata);
                $ares = Answers::model()->insertRecords($answerrowdata) or safeDie("Error: Failed to insert answer<br />");
                $results['answers']++;
            }

        }
    }

    // get all group ids and fix questions inside each group
    $gquery = "SELECT gid FROM {{groups}} where sid=$iNewSID group by gid ORDER BY gid"; //Get last question added (finds new qid)
    $gres = Yii::app()->db->createCommand($gquery)->query();
    foreach ($gres->readAll() as $grow)
    {
        Questions::model()->updateQuestionOrder($grow['gid'], $iNewSID);
    }

    //We've built two arrays along the way - one containing the old SID, GID and QIDs - and their NEW equivalents
    //and one containing the old 'extended fieldname' and its new equivalent.  These are needed to import conditions and question_attributes.
    if (isset($question_attributesarray) && $question_attributesarray) {//ONLY DO THIS IF THERE ARE QUESTION_ATTRIBUES
        $fieldorders  =convertCSVRowToArray($question_attributesarray[0],',','"');
        unset($question_attributesarray[0]);
        foreach ($question_attributesarray as $qar) {
            $fieldcontents=convertCSVRowToArray($qar,',','"');
            $qarowdata=array_combine($fieldorders,$fieldcontents);
            $newqid="";
            $qarowdata["qid"]=$aQIDReplacements[$qarowdata["qid"]];
            unset($qarowdata["qaid"]);
            $result=Question_attributes::model()->insertRecords($qarowdata);
            if ($result>0) {$importresults['question_attributes']++;}
        }
    }

    if (isset($assessmentsarray) && $assessmentsarray) {//ONLY DO THIS IF THERE ARE QUESTION_ATTRIBUTES
        $fieldorders=convertCSVRowToArray($assessmentsarray[0],',','"');
        unset($assessmentsarray[0]);
        foreach ($assessmentsarray as $qar)
        {
            $fieldcontents=convertCSVRowToArray($qar,',','"');
            $asrowdata=array_combine($fieldorders,$fieldcontents);
            if (isset($asrowdata['link']))
            {
                if (trim($asrowdata['link'])!='') $asrowdata['message']=$asrowdata['message'].'<br /><a href="'.$asrowdata['link'].'">'.$asrowdata['link'].'</a>';
                unset($asrowdata['link']);
            }
            if  ($asrowdata["gid"]>0)
            {
                $asrowdata["gid"]=$aGIDReplacements[$asrowdata["gid"]];
            }

            $asrowdata["sid"]=$iNewSID;
            unset($asrowdata["id"]);

            $result=Assessments::model()->insertRecords($asrowdata) or safeDie("Couldn't insert assessment<br />");

            unset($newgid);
        }
    }

    if (isset($quotaarray) && $quotaarray) {//ONLY DO THIS IF THERE ARE QUOTAS
        $fieldorders=convertCSVRowToArray($quotaarray[0],',','"');
        unset($quotaarray[0]);
        foreach ($quotaarray as $qar)
        {
            $fieldcontents=convertCSVRowToArray($qar,',','"');

            $asrowdata=array_combine($fieldorders,$fieldcontents);

            $iOldSID=$asrowdata["sid"];
            foreach ($substitutions as $subs) {
                if ($iOldSID==$subs[0]) {$iNewSID=$subs[3];}
            }

            $asrowdata["sid"]=$iNewSID;
            $oldid = $asrowdata["id"];
            unset($asrowdata["id"]);
            $quotadata[]=$asrowdata; //For use later if needed
            $result=Quota::model()->insertRecords($asrowdata) or safeDie ("Couldn't insert quota<br />");
            $aQuotaReplacements[$oldid]=Yii::app()->db->createCommand('Select LAST_INSERT_ID()')->query()->read();
            $aQuotaReplacements[$oldid]=$aQuotaReplacements[$oldid]['LAST_INSERT_ID()'];
        }
    }

    if (isset($quotamembersarray) && $quotamembersarray) {//ONLY DO THIS IF THERE ARE QUOTA MEMBERS
        $count=0;
        foreach ($quotamembersarray as $qar) {

            $fieldorders  =convertCSVRowToArray($quotamembersarray[0],',','"');
            $fieldcontents=convertCSVRowToArray($qar,',','"');
            if ($count==0) {$count++; continue;}

            $asrowdata=array_combine($fieldorders,$fieldcontents);

            $iOldSID=$asrowdata["sid"];
            $newqid="";
            $newquotaid="";
            $oldqid=$asrowdata['qid'];
            $oldquotaid=$asrowdata['quota_id'];

            foreach ($substitutions as $subs) {
                if ($iOldSID==$subs[0]) {$iNewSID=$subs[3];}
                if ($oldqid==$subs[2]) {$newqid=$subs[5];}
            }

            $newquotaid=$aQuotaReplacements[$oldquotaid];

            $asrowdata["sid"]=$iNewSID;
            $asrowdata["qid"]=$newqid;
            $asrowdata["quota_id"]=$newquotaid;
            unset($asrowdata["id"]);

            $result=Quota_members::model()->insertRecords($asrowdata) or safeDie("Couldn't insert quota<br />");

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

            $newquotaid=$aQuotaReplacements[$oldquotaid];

            $asrowdata["quotals_quota_id"]=$newquotaid;
            unset($asrowdata["quotals_id"]);

            $result=Quota_languagesettings::model()->insertRecords($asrowdata) or safeDie("Couldn't insert quota<br />");
        }
    }

    //if there are quotas, but no quotals, then we need to create default dummy for each quota (this handles exports from pre-language quota surveys)
    if ($importresults['quota'] > 0 && (!isset($importresults['quotals']) || $importresults['quotals'] == 0)) {
        $i=0;
        $defaultsurveylanguage=isset($defaultsurveylanguage) ? $defaultsurveylanguage : "en";
        foreach($aQuotaReplacements as $oldquotaid=>$newquotaid) {
            $asrowdata=array("quotals_quota_id" => $newquotaid,
            "quotals_language" => $defaultsurveylanguage,
            "quotals_name" => $quotadata[$i]["name"],
            "quotals_message" => $clang->gT("Sorry your responses have exceeded a quota on this survey."),
            "quotals_url" => "",
            "quotals_urldescrip" => "");
            $i++;
        }

        $result=Quota_languagesettings::model()->insertRecords($asrowdata) or safeDie("Couldn't insert quota<br />");
        $countquotals=$i;
    }

    // Do conditions
    if (isset($conditionsarray) && $conditionsarray) {//ONLY DO THIS IF THERE ARE CONDITIONS!
        $fieldorders  =convertCSVRowToArray($conditionsarray[0],',','"');
        unset($conditionsarray[0]);
        // Exception for conditions based on attributes
        $aQIDReplacements[0]=0;
        foreach ($conditionsarray as $car) {
            $fieldcontents=convertCSVRowToArray($car,',','"');
            $conditionrowdata=array_combine($fieldorders,$fieldcontents);

            unset($conditionrowdata["cid"]);
            if (!isset($conditionrowdata["method"]) || trim($conditionrowdata["method"])=='')
            {
                $conditionrowdata["method"]='==';
            }
            if (!isset($conditionrowdata["scenario"]) || trim($conditionrowdata["scenario"])=='')
            {
                $conditionrowdata["scenario"]=1;
            }
            $oldcqid=$conditionrowdata["cqid"];
            $query = 'select gid from {{questions}} where qid='.$aQIDReplacements[$conditionrowdata["cqid"]];
            $res=Yii::app()->db->createCommand($query)->query();
            $resrow = $res->read();

            $oldgid=array_search($resrow['gid'],$aGIDReplacements);
            $conditionrowdata["qid"]=$aQIDReplacements[$conditionrowdata["qid"]];
            $conditionrowdata["cqid"]=$aQIDReplacements[$conditionrowdata["cqid"]];
            $oldcfieldname=$conditionrowdata["cfieldname"];
            $conditionrowdata["cfieldname"]=str_replace($iOldSID.'X'.$oldgid.'X'.$oldcqid,$iNewSID.'X'.$aGIDReplacements[$oldgid].'X'.$conditionrowdata["cqid"],$conditionrowdata["cfieldname"]);

            $result=Conditions::model()->insertRecords($conditionrowdata) or safeDie("Couldn't insert condition<br />");

        }
    }
    LimeExpressionManager::RevertUpgradeConditionsToRelevance($iNewSID);
    LimeExpressionManager::UpgradeConditionsToRelevance($iNewSID);
    LimeExpressionManager::SetSurveyId($iNewSID);

    $importresults['importversion']=$importversion;
    $importresults['newsid']=$iNewSID;
    $importresults['oldsid']=$iOldSID;
    return $importresults;
}


function importSurveyFile($sFullFilepath, $bTranslateLinksFields, $sNewSurveyName=NULL, $DestSurveyID=NULL)
{
    $aPathInfo = pathinfo($sFullFilepath);
    if (isset($aPathInfo['extension']))
    {
        $sExtension = $aPathInfo['extension'];
    }
    else
    {
        $sExtension = "";
    }
    if (isset($sExtension) && strtolower($sExtension) == 'csv')
    {
        return CSVImportSurvey($sFullFilepath, $DestSurveyID, $bTranslateLinksFields);
    }
    elseif (isset($sExtension) && strtolower($sExtension) == 'lss')
    {
        return XMLImportSurvey($sFullFilepath, null, $sNewSurveyName, $DestSurveyID, $bTranslateLinksFields);
    }
    elseif (isset($sExtension) && strtolower($sExtension) == 'txt')
    {
        return TSVImportSurvey($sFullFilepath);
    }
    elseif (isset($sExtension) && strtolower($sExtension) == 'lsa')  // Import a survey archive
    {
        Yii::import("application.libraries.admin.pclzip.pclzip", true);
        $pclzip = new PclZip(array('p_zipname' => $sFullFilepath));
        $aFiles = $pclzip->listContent();

        if ($pclzip->extract(PCLZIP_OPT_PATH, Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR, PCLZIP_OPT_BY_EREG, '/(lss|lsr|lsi|lst)$/') == 0)
        {
            unset($pclzip);
        }
        // Step 1 - import the LSS file and activate the survey
        foreach ($aFiles as $aFile)
        {
            if (pathinfo($aFile['filename'], PATHINFO_EXTENSION) == 'lss')
            {
                //Import the LSS file
                $aImportResults = XMLImportSurvey(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $aFile['filename'], null, null, null, true);
                // Activate the survey
                Yii::app()->loadHelper("admin/activate");
                $activateoutput = activateSurvey($aImportResults['newsid']);
                unlink(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $aFile['filename']);
                break;
            }
        }
        // Step 2 - import the responses file
        foreach ($aFiles as $aFile)
        {
            if (pathinfo($aFile['filename'], PATHINFO_EXTENSION) == 'lsr')
            {
                //Import the LSS file
                $aResponseImportResults = XMLImportResponses(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $aFile['filename'], $aImportResults['newsid'], $aImportResults['FieldReMap']);
                $aImportResults = array_merge($aResponseImportResults, $aImportResults);
                unlink(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $aFile['filename']);
                break;
            }
        }
        // Step 3 - import the tokens file - if exists
        foreach ($aFiles as $aFile)
        {
            if (pathinfo($aFile['filename'], PATHINFO_EXTENSION) == 'lst')
            {
                Yii::app()->loadHelper("admin/token");
                if (createTokenTable($aImportResults['newsid']))
                    $aTokenCreateResults = array('tokentablecreated' => true);
                $aImportResults = array_merge($aTokenCreateResults, $aImportResults);
                $aTokenImportResults = XMLImportTokens(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $aFile['filename'], $aImportResults['newsid']);
                $aImportResults = array_merge($aTokenImportResults, $aImportResults);
                unlink(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $aFile['filename']);
                break;
            }
        }
        // Step 4 - import the timings file - if exists
        Yii::app()->db->schema->refresh();
        foreach ($aFiles as $aFile)
        {
            if (pathinfo($aFile['filename'], PATHINFO_EXTENSION) == 'lsi' && tableExists("survey_{$aImportResults['newsid']}_timings"))
            {
                $aTimingsImportResults = XMLImportTimings(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $aFile['filename'], $aImportResults['newsid'], $aImportResults['FieldReMap']);
                $aImportResults = array_merge($aTimingsImportResults, $aImportResults);
                unlink(Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $aFile['filename']);
                break;
            }
        }
        return $aImportResults;
    }
    else
    {
        return null;
    }

}



/**
* This function imports a LimeSurvey .lss survey XML file
*
* @param mixed $sFullFilepath  The full filepath of the uploaded file
*/
function XMLImportSurvey($sFullFilepath,$sXMLdata=NULL,$sNewSurveyName=NULL,$iDesiredSurveyId=NULL, $bTranslateInsertansTags=true)
{
    Yii::app()->loadHelper('database');
    $clang = Yii::app()->lang;

    $aGIDReplacements = array();
    if ($sXMLdata == NULL)
    {
        $xml = simplexml_load_file($sFullFilepath);
    } else
    {
        $xml = simplexml_load_string($sXMLdata);
    }

    if ($xml->LimeSurveyDocType!='Survey')
    {
        $results['error'] = $clang->gT("This is not a valid LimeSurvey survey structure XML file.");
        return $results;
    }

    $iDBVersion = (int) $xml->DBVersion;
    $aQIDReplacements=array();
    $aQuotaReplacements=array();
    $results['defaultvalues']=0;
    $results['answers']=0;
    $results['surveys']=0;
    $results['questions']=0;
    $results['subquestions']=0;
    $results['question_attributes']=0;
    $results['groups']=0;
    $results['assessments']=0;
    $results['quota']=0;
    $results['quotals']=0;
    $results['quotamembers']=0;
    $results['survey_url_parameters']=0;
    $results['importwarnings']=array();


    $aLanguagesSupported=array();
    foreach ($xml->languages->language as $language)
    {
        $aLanguagesSupported[]=(string)$language;
    }
    $results['languages']=count($aLanguagesSupported);

    // Import surveys table ====================================================

    foreach ($xml->surveys->rows->row as $row)
    {
        $insertdata=array();

        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }

        $iOldSID=$results['oldsid']=$insertdata['sid'];
        if($iDesiredSurveyId!=NULL)
        {
            $insertdata['wishSID']=GetNewSurveyID($iDesiredSurveyId);
        }
        else
        {
            $insertdata['wishSID']=$iOldSID;
        }

        if ($iDBVersion<=143)
        {
            if(isset($insertdata['private'])) $insertdata['anonymized']=$insertdata['private'];
            unset($insertdata['private']);
            unset($insertdata['notification']);
        }

        unset($insertdata['expires']);
        unset($insertdata['startdate']);

        //Make sure it is not set active
        $insertdata['active']='N';
        //Set current user to be the owner
        $insertdata['owner_id']=Yii::app()->session['loginID'];

        if (isset($insertdata['bouncetime']) && $insertdata['bouncetime'] == '')
        {
            $insertdata['bouncetime'] = NULL;
        }

        if (isset($insertdata['showXquestions']))
        {
            $insertdata['showxquestions']=$insertdata['showXquestions'];
            unset($insertdata['showXquestions']);
        }
        $iNewSID = $results['newsid'] = Survey::model()->insertNewSurvey($insertdata) or safeDie($clang->gT("Error").": Failed to insert data [1]<br />");

        $results['surveys']++;
    }


    // Import survey languagesettings table ===================================================================================



    foreach ($xml->surveys_languagesettings->rows->row as $row)
    {

        $insertdata=array();
        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }
        if (!in_array($insertdata['surveyls_language'],$aLanguagesSupported)) continue;

        $insertdata['surveyls_survey_id']=$iNewSID;
        if ($bTranslateInsertansTags)
        {
            if ($sNewSurveyName == NULL)
            {
                $insertdata['surveyls_title']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_title']);
            } else {
                $insertdata['surveyls_title']=translateLinks('survey', $iOldSID, $iNewSID, $sNewSurveyName);
            }
            if (isset($insertdata['surveyls_description'])) $insertdata['surveyls_description']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_description']);
            if (isset($insertdata['surveyls_welcometext'])) $insertdata['surveyls_welcometext']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_welcometext']);
            if (isset($insertdata['surveyls_urldescription']))$insertdata['surveyls_urldescription']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_urldescription']);
            if (isset($insertdata['surveyls_email_invite'])) $insertdata['surveyls_email_invite']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_email_invite']);
            if (isset($insertdata['surveyls_email_remind'])) $insertdata['surveyls_email_remind']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_email_remind']);
            if (isset($insertdata['surveyls_email_register'])) $insertdata['surveyls_email_register']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_email_register']);
            if (isset($insertdata['surveyls_email_confirm'])) $insertdata['surveyls_email_confirm']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['surveyls_email_confirm']);
        }


        $result = Surveys_languagesettings::model()->insertNewSurvey($insertdata) or safeDie($clang->gT("Error").": Failed to insert data [2]<br />");
    }


    // Import groups table ===================================================================================


    if (isset($xml->groups->rows->row))
    {
        foreach ($xml->groups->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            if (!in_array($insertdata['language'],$aLanguagesSupported)) continue;
            $iOldSID=$insertdata['sid'];
            $insertdata['sid']=$iNewSID;
            $oldgid=$insertdata['gid']; unset($insertdata['gid']); // save the old qid

            // now translate any links
            if ($bTranslateInsertansTags)
            {
                $insertdata['group_name']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['group_name']);
                $insertdata['description']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['description']);
            }
            // Insert the new group
            if (isset($aGIDReplacements[$oldgid]))
            {
                switchMSSQLIdentityInsert('groups',true);
                $insertdata['gid']=$aGIDReplacements[$oldgid];
            }
            $newgid = Groups::model()->insertRecords($insertdata) or safeDie($clang->gT("Error").": Failed to insert data [3]<br />");
            $results['groups']++;

            if (!isset($aGIDReplacements[$oldgid]))
            {
                $aGIDReplacements[$oldgid]=$newgid; // add old and new qid to the mapping array
            }
            else
            {
                switchMSSQLIdentityInsert('groups',false);
            }
        }
    }

    // Import questions table ===================================================================================

    // We have to run the question table data two times - first to find all main questions
    // then for subquestions (because we need to determine the new qids for the main questions first)
    if(isset($xml->questions))  // there could be surveys without a any questions
    {

        foreach ($xml->questions->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            if (!in_array($insertdata['language'],$aLanguagesSupported) || $insertdata['gid']==0) continue;
            $iOldSID=$insertdata['sid'];
            $insertdata['sid']=$iNewSID;
            $insertdata['gid']=$aGIDReplacements[$insertdata['gid']];
            $oldqid=$insertdata['qid']; unset($insertdata['qid']); // save the old qid

            // now translate any links
            if ($bTranslateInsertansTags)
            {
                $insertdata['question']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
                $insertdata['help']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']);
            }
            // Insert the new question
            if (isset($aQIDReplacements[$oldqid]))
            {
                $insertdata['qid']=$aQIDReplacements[$oldqid];
                switchMSSQLIdentityInsert('questions',true);

            }
            if ($insertdata)
                XSSFilterArray($insertdata);
            $newqid = Questions::model()->insertRecords($insertdata) or safeDie($clang->gT("Error").": Failed to insert data [4]<br />");
            if (!isset($aQIDReplacements[$oldqid]))
            {
                $aQIDReplacements[$oldqid]=$newqid;
                $results['questions']++;
            }
            else
            {
                switchMSSQLIdentityInsert('questions',false);
            }
        }
    }

    // Import subquestions -------------------------------------------------------
    if(isset($xml->subquestions))
    {

        foreach ($xml->subquestions->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            if (!in_array($insertdata['language'],$aLanguagesSupported) || $insertdata['gid']==0) continue;
            if (!isset($insertdata['mandatory']) || trim($insertdata['mandatory'])=='')
            {
                $insertdata['mandatory']='N';
            }
            $insertdata['sid']=$iNewSID;
            $insertdata['gid']=$aGIDReplacements[(int)$insertdata['gid']];;
            $oldsqid=(int)$insertdata['qid']; unset($insertdata['qid']); // save the old qid
            $insertdata['parent_qid']=$aQIDReplacements[(int)$insertdata['parent_qid']]; // remap the parent_qid

            // now translate any links
            if ($bTranslateInsertansTags)
            {
                $insertdata['question']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['question']);
                if (isset($insertdata['help'])) $insertdata['help']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['help']);
            }
            if (isset($aQIDReplacements[$oldsqid])){
                $insertdata['qid']=$aQIDReplacements[$oldsqid];
                switchMSSQLIdentityInsert('questions',true);
            }
            if ($insertdata)
                XSSFilterArray($insertdata);
            $newsqid =Questions::model()->insertRecords($insertdata) or safeDie($clang->gT("Error").": Failed to insert data [5]<br />");
            if (!isset($insertdata['qid']))
            {
                $aQIDReplacements[$oldsqid]=$newsqid; // add old and new qid to the mapping array
            }
            else
            {
                switchMSSQLIdentityInsert('questions',false);
            }
            $results['subquestions']++;
        }
    }

    // Import answers ------------------------------------------------------------
    if(isset($xml->answers))
    {

        foreach ($xml->answers->rows->row as $row)
        {
            $insertdata=array();

            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            if (!in_array($insertdata['language'],$aLanguagesSupported) || !isset($aQIDReplacements[(int)$insertdata['qid']])) continue;
            $insertdata['qid']=$aQIDReplacements[(int)$insertdata['qid']]; // remap the parent_qid

            // now translate any links
            if ($bTranslateInsertansTags)
            {
                $insertdata['answer']=translateLinks('survey', $iOldSID, $iNewSID, $insertdata['answer']);
            }
            if ($insertdata)
                XSSFilterArray($insertdata);
            $result=Answers::model()->insertRecords($insertdata) or safeDie($clang->gT("Error").": Failed to insert data[6]<br />");
            $results['answers']++;
        }
    }

    // Import questionattributes -------------------------------------------------
    if(isset($xml->question_attributes))
    {


        $aAllAttributes=questionAttributes(true);
        foreach ($xml->question_attributes->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            unset($insertdata['qaid']);
            if (!isset($aQIDReplacements[(int)$insertdata['qid']])) continue;
            $insertdata['qid']=$aQIDReplacements[(integer)$insertdata['qid']]; // remap the qid
            if ($iDBVersion<156 && isset($aAllAttributes[$insertdata['attribute']]['i18n']) && $aAllAttributes[$insertdata['attribute']]['i18n'])
            {
                foreach ($aLanguagesSupported as $sLanguage)
                {
                    $insertdata['language']=$sLanguage;
                    if ($insertdata)
                        XSSFilterArray($insertdata);
                    $result=Question_attributes::model()->insertRecords($insertdata) or safeDie($clang->gT("Error").": Failed to insert data[7]<br />");
                }
            }
            else
            {
                $result=Question_attributes::model()->insertRecords($insertdata) or safeDie($clang->gT("Error").": Failed to insert data[8]<br />");
            }
            $results['question_attributes']++;
        }
    }

    // Import defaultvalues ------------------------------------------------------
    if(isset($xml->defaultvalues))
    {


        $results['defaultvalues']=0;
        foreach ($xml->defaultvalues->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['qid']=$aQIDReplacements[(int)$insertdata['qid']]; // remap the qid
            if (isset($aQIDReplacements[(int)$insertdata['sqid']])) $insertdata['sqid']=$aQIDReplacements[(int)$insertdata['sqid']]; // remap the subquestion id
            if ($insertdata)
                XSSFilterArray($insertdata);
            // now translate any links
            $result=Defaultvalues::model()->insertRecords($insertdata) or safeDie($clang->gT("Error").": Failed to insert data[9]<br />");
            $results['defaultvalues']++;
        }
    }
    $aOldNewFieldmap=reverseTranslateFieldNames($iOldSID,$iNewSID,$aGIDReplacements,$aQIDReplacements);

    // Import conditions ---------------------------------------------------------
    if(isset($xml->conditions))
    {


        $results['conditions']=0;
        foreach ($xml->conditions->rows->row as $row)
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
            if ($insertdata['cqid'] != 0)
            {
                if (isset($aQIDReplacements[$insertdata['cqid']]))
                {
                    $oldcqid = $insertdata['cqid']; //Save for cfield transformation
                    $insertdata['cqid']=$aQIDReplacements[$insertdata['cqid']]; // remap the qid
                }
                else continue; // a problem with this answer record -> don't consider

                list($oldcsid, $oldcgid, $oldqidanscode) = explode("X",$insertdata["cfieldname"],3);

                // replace the gid for the new one in the cfieldname(if there is no new gid in the $aGIDReplacements array it means that this condition is orphan -> error, skip this record)
                if (!isset($aGIDReplacements[$oldcgid]))
                    continue;
            }

            unset($insertdata["cid"]);

            // recreate the cfieldname with the new IDs
            if ($insertdata['cqid'] != 0)
            {
                if (preg_match("/^\+/",$oldcsid))
                {
                    $newcfieldname = '+'.$iNewSID . "X" . $aGIDReplacements[$oldcgid] . "X" . $insertdata["cqid"] .substr($oldqidanscode,strlen($oldcqid));
                }
                else
                {
                    $newcfieldname = $iNewSID . "X" . $aGIDReplacements[$oldcgid] . "X" . $insertdata["cqid"] .substr($oldqidanscode,strlen($oldcqid));
                }
            }
            else
            { // The cfieldname is a not a previous question cfield but a {XXXX} replacement field
                $newcfieldname = $insertdata["cfieldname"];
            }
            $insertdata["cfieldname"] = $newcfieldname;
            if (trim($insertdata["method"])=='')
            {
                $insertdata["method"]='==';
            }

            // Now process the value and replace @sgqa@ codes
            if (preg_match("/^@(.*)@$/",$insertdata["value"],$cfieldnameInCondValue))
            {
                if (isset($aOldNewFieldmap[$cfieldnameInCondValue[1]]))
                {
                    $newvalue = '@'.$aOldNewFieldmap[$cfieldnameInCondValue[1]].'@';
                    $insertdata["value"] = $newvalue;
                }

            }

            // now translate any links
            $result=Conditions::model()->insertRecords($insertdata) or safeDie ($clang->gT("Error").": Failed to insert data[10]<br />");
            $results['conditions']++;
        }
    }
    // TMSW Conditions->Relevance:  Call  LEM->ConvertConditionsToRelevance

    // Import assessments --------------------------------------------------------
    if(isset($xml->assessments))
    {


        foreach ($xml->assessments->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            if  ($insertdata['gid']>0)
            {
                $insertdata['gid']=$aGIDReplacements[(int)$insertdata['gid']]; // remap the qid
            }

            $insertdata['sid']=$iNewSID; // remap the survey id
            unset($insertdata['id']);
            // now translate any links
            $result=Assessment::model()->insertRecords($insertdata) or safeDie($clang->gT("Error").": Failed to insert data[11]<br />");
            $results['assessments']++;
        }
    }

    // Import quota --------------------------------------------------------------
    if(isset($xml->quota))
    {


        foreach ($xml->quota->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['sid']=$iNewSID; // remap the survey id
            $oldid=$insertdata['id'];
            unset($insertdata['id']);
            // now translate any links
            $result=Quota::model()->insertRecords($insertdata) or safeDie($clang->gT("Error").": Failed to insert data[12]<br />");
            $aQuotaReplacements[$oldid] = getLastInsertID('{{quota}}');
            $results['quota']++;
        }
    }

    // Import quota_members ------------------------------------------------------
    if(isset($xml->quota_members))
    {

        foreach ($xml->quota_members->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['sid']=$iNewSID; // remap the survey id
            $insertdata['qid']=$aQIDReplacements[(int)$insertdata['qid']]; // remap the qid
            $insertdata['quota_id']=$aQuotaReplacements[(int)$insertdata['quota_id']]; // remap the qid
            unset($insertdata['id']);
            // now translate any links
            $result=Quota_members::model()->insertRecords($insertdata) or safeDie($clang->gT("Error").": Failed to insert data[13]<br />");
            $results['quotamembers']++;
        }
    }

    // Import quota_languagesettings----------------------------------------------
    if(isset($xml->quota_languagesettings))
    {

        foreach ($xml->quota_languagesettings->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['quotals_quota_id']=$aQuotaReplacements[(int)$insertdata['quotals_quota_id']]; // remap the qid
            unset($insertdata['quotals_id']);
            $result=Quota_languagesettings::model()->insertRecords($insertdata) or safeDie($clang->gT("Error").": Failed to insert data<br />");
            $results['quotals']++;
        }
    }

    // Import survey_url_parameters ----------------------------------------------
    if(isset($xml->survey_url_parameters))
    {

        foreach ($xml->survey_url_parameters->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['sid']=$iNewSID; // remap the survey id
            if (isset($insertdata['targetsqid']) && $insertdata['targetsqid']!='')
            {
                $insertdata['targetsqid'] =$aSQIDReplacements[(int)$insertdata['targetsqid']]; // remap the qid
            }
            if (isset($insertdata['targetqid']) && $insertdata['targetqid']!='')
            {
                $insertdata['targetqid'] =$aQIDReplacements[(int)$insertdata['targetqid']]; // remap the qid
            }
            unset($insertdata['id']);
            $result=Survey_url_parameters::model()->insertRecord($insertdata) or safeDie($clang->gT("Error").": Failed to insert data[14]<br />");
            $results['survey_url_parameters']++;
        }
    }

    // Set survey rights
    Survey_permissions::model()->giveAllSurveyPermissions(Yii::app()->session['loginID'],$iNewSID);
    $aOldNewFieldmap=reverseTranslateFieldNames($iOldSID,$iNewSID,$aGIDReplacements,$aQIDReplacements);
    $results['FieldReMap']=$aOldNewFieldmap;
    LimeExpressionManager::SetSurveyId($iNewSID);
    translateInsertansTags($iNewSID,$iOldSID,$aOldNewFieldmap);
    LimeExpressionManager::RevertUpgradeConditionsToRelevance($iNewSID);
    LimeExpressionManager::UpgradeConditionsToRelevance($iNewSID);
    return $results;
}

/**
* This function returns a new random sid if the existing one is taken,
* otherwise it returns the old one.
*
* @param mixed $iOldSID
*/
function GetNewSurveyID($iOldSID)
{
    Yii::app()->loadHelper('database');
    $query = "SELECT sid FROM {{surveys}} WHERE sid=$iOldSID";

    $aRow = Yii::app()->db->createCommand($query)->queryRow();

    //if (!is_null($isresult))
    if($aRow!==false)
    {
        // Get new random ids until one is found that is not used
        do
        {
            $iNewSID = randomChars(5,'123456789');
            $query = "SELECT sid FROM {{surveys}} WHERE sid=$iNewSID";
            $aRow = Yii::app()->db->createCommand($query)->queryRow();
        }
        while ($aRow!==false);

        return $iNewSID;
    }
    else
    {
        return $iOldSID;
    }
}


function XMLImportTokens($sFullFilepath,$iSurveyID,$sCreateMissingAttributeFields=true)
{
    Yii::app()->loadHelper('database');
    $clang = Yii::app()->lang;
    $xml = simplexml_load_file($sFullFilepath);

    if ($xml->LimeSurveyDocType!='Tokens')
    {
        $results['error'] = $clang->gT("This is not a valid token data XML file.");
        return $results;
    }

    if (!isset($xml->tokens->fields))
    {
        $results['tokens']=0;
        return $results;
    }
    
    $results['tokens']=0;
    $results['tokenfieldscreated']=0;

    $aLanguagesSupported=array();
    foreach ($xml->languages->language as $language)
    {
        $aLanguagesSupported[]=(string)$language;
    }
    $results['languages']=count($aLanguagesSupported);

    if ($sCreateMissingAttributeFields)
    {
        // Get a list with all fieldnames in the XML
        $aXLMFieldNames=array();
        foreach ($xml->tokens->fields->fieldname as $sFieldName )
        {
            $aXLMFieldNames[]=(string)$sFieldName;
        }
        // Get a list of all fieldnames in the token table
        $aTokenFieldNames=Yii::app()->db->getSchema()->getTable("{{tokens_$iSurveyID}}",true);
        $aTokenFieldNames=array_keys($aTokenFieldNames->columns);
        $aFieldsToCreate=array_diff($aXLMFieldNames, $aTokenFieldNames);
        Yii::app()->loadHelper('update/updatedb');

        foreach ($aFieldsToCreate as $sField)
        {
            if (strpos($sField,'attribute')!==false)
            {
                addColumn('{{tokens_'.$iSurveyID.'}}',$sField, 'string');
            }
        }
    }

    switchMSSQLIdentityInsert('tokens_'.$iSurveyID,true);
    foreach ($xml->tokens->rows->row as $row)
    {
        $insertdata=array();

        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }

        $result = Tokens_dynamic::model($iSurveyID)->insertToken($iSurveyID,$insertdata) or safeDie($clang->gT("Error").": Failed to insert data[15]<br />");

        $results['tokens']++;
    }
    switchMSSQLIdentityInsert('tokens_'.$iSurveyID,false);

    return $results;
}


function XMLImportResponses($sFullFilepath,$iSurveyID,$aFieldReMap=array())
{
    Yii::app()->loadHelper('database');
    $clang = Yii::app()->lang;

    switchMSSQLIdentityInsert('survey_'.$iSurveyID, true);
    $results['responses']=0;
    $oXMLReader = new XMLReader();
    $oXMLReader->open($sFullFilepath);
    $DestinationFields = Yii::app()->db->schema->getTable('{{survey_'.$iSurveyID.'}}')->getColumnNames();
    while ($oXMLReader->read()) {
        if ($oXMLReader->name === 'LimeSurveyDocType' && $oXMLReader->nodeType == XMLReader::ELEMENT)
        {
            $oXMLReader->read();
            if ($oXMLReader->value!='Responses')
            {
                $results['error'] = $clang->gT("This is not a valid response data XML file.");
                return $results;
            }
        }
        if ($oXMLReader->name === 'rows' && $oXMLReader->nodeType == XMLReader::ELEMENT)
        {
            while ($oXMLReader->read()) {
                if ($oXMLReader->name === 'row' && $oXMLReader->nodeType == XMLReader::ELEMENT)
                {
                    $aInsertData=array();
                    while ($oXMLReader->read() && $oXMLReader->name != 'row') {
                        $sFieldname=$oXMLReader->name;
                        if ($sFieldname[0]=='_') $sFieldname=substr($sFieldname,1);
                        $sFieldname=str_replace('-','#',$sFieldname);
                        if (isset($aFieldReMap[$sFieldname]))
                        {
                            $sFieldname=$aFieldReMap[$sFieldname];
                        }
                        if (!$oXMLReader->isEmptyElement)
                        {
                            $oXMLReader->read();
                            if(in_array($sFieldname,$DestinationFields)) // some old response tables contain invalid column names due to old bugs
                                $aInsertData[$sFieldname]=$oXMLReader->value;
                            $oXMLReader->read();
                        }else
                        {
                            if(in_array($sFieldname,$DestinationFields))
                                $aInsertData[$sFieldname]='';
                        }
                    }
                    
                    $result = Survey_dynamic::model($iSurveyID)->insertRecords($aInsertData) or safeDie($clang->gT("Error").": Failed to insert data[16]<br />");
                    $results['responses']++;
                }
            }

        }
    }

    switchMSSQLIdentityInsert('survey_'.$iSurveyID,false);

    return $results;
}


function XMLImportTimings($sFullFilepath,$iSurveyID,$aFieldReMap=array())
{

    Yii::app()->loadHelper('database');
    $clang = Yii::app()->lang;

    $xml = simplexml_load_file($sFullFilepath);

    if ($xml->LimeSurveyDocType!='Timings')
    {
        $results['error'] = $clang->gT("This is not a valid timings data XML file.");
        return $results;
    }

    $results['responses']=0;

    $aLanguagesSupported=array();
    foreach ($xml->languages->language as $language)
    {
        $aLanguagesSupported[]=(string)$language;
    }
    $results['languages']=count($aLanguagesSupported);
     // Return if there are no timing records to import
    if (!isset($xml->timings->rows)) 
    {
        return $results;   
    }
    switchMSSQLIdentityInsert('survey_'.$iSurveyID.'_timings',true);
    foreach ($xml->timings->rows->row as $row)
    {
        $insertdata=array();

        foreach ($row as $key=>$value)
        {
            if ($key[0]=='_') $key=substr($key,1);
            if (isset($aFieldReMap[substr($key,0,-4)]))
            {
                $key=$aFieldReMap[substr($key,0,-4)].'time';
            }
            $insertdata[$key]=(string)$value;
        }

        $result = Survey_timings::model($iSurveyID)->insertRecords($insertdata) or safeDie($clang->gT("Error").": Failed to insert data[17]<br />");

        $results['responses']++;
    }
    switchMSSQLIdentityInsert('survey_'.$iSurveyID.'_timings',false);

    return $results;
}

function XSSFilterArray(&$array)
{
    if(Yii::app()->getConfig('filterxsshtml') && Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1)
    {
        $filter = new CHtmlPurifier();
        $filter->options = array('URI.AllowedSchemes'=>array(
        'http' => true,
        'https' => true,
        ));
        foreach($array as &$value)
        {
            $value = $filter->purify($value);
        }
    }
}

/**
* Import survey from an TSV file template that does not require or allow assigning of GID or QID values.
* NOTE:  This currently only supports import of one language
* @global type $connect
* @global type $dbprefix
* @global type $clang
* @global type $timeadjust
* @param type $sFullFilepath
* @return type
*
* @author TMSWhite
*/
function TSVImportSurvey($sFullFilepath)
{
    $clang = Yii::app()->lang;

    $insertdata=array();
    $results=array();
    $results['error']=false;
    $baselang = 'en';   // TODO set proper default

    $encoding='';
    $handle = fopen($sFullFilepath, 'r');
    $bom = fread($handle, 2);
    rewind($handle);

    // Excel tends to save CSV as UTF-16, which PHP does not properly detect
    if($bom === chr(0xff).chr(0xfe)  || $bom === chr(0xfe).chr(0xff)){
        // UTF16 Byte Order Mark present
        $encoding = 'UTF-16';
    } else {
        $file_sample = fread($handle, 1000) + 'e'; //read first 1000 bytes
        // + e is a workaround for mb_string bug
        rewind($handle);

        $encoding = mb_detect_encoding($file_sample , 'UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP');
    }
    if ($encoding && $encoding != 'UTF-8'){
        stream_filter_append($handle, 'convert.iconv.'.$encoding.'/UTF-8');
    }

    $file = stream_get_contents($handle);
    fclose($handle);

    // fix Excel non-breaking space
    $file = str_replace("0xC20xA0",' ',$file);
    $filelines = explode("\n",$file);
    $row = array_shift($filelines);
    $headers = explode("\t",$row);
    $rowheaders = array();
    foreach ($headers as $header)
    {
        $rowheaders[] = trim($header);
    }
    // remove BOM from the first header cell, if needed
    $rowheaders[0] = preg_replace("/^\W+/","",$rowheaders[0]);
    if (preg_match('/class$/',$rowheaders[0]))
    {
        $rowheaders[0] = 'class';   // second attempt to remove BOM
    }

    $adata = array();
    foreach ($filelines as $rowline)
    {
        $rowarray = array();
        $row = explode("\t",$rowline);
        for ($i = 0; $i < count($rowheaders); ++$i)
        {
            $val = (isset($row[$i]) ? $row[$i] : '');
            // if Excel was used, it surrounds strings with quotes and doubles internal double quotes.  Fix that.
            if (preg_match('/^".*"$/',$val))
            {
                $val = str_replace('""','"',substr($val,1,-1));
            }
            $rowarray[$rowheaders[$i]] = $val;
        }
        $adata[] = $rowarray;
    }

    $results['defaultvalues']=0;
    $results['answers']=0;
    $results['surveys']=0;
    $results['languages']=0;
    $results['questions']=0;
    $results['subquestions']=0;
    $results['question_attributes']=0;
    $results['groups']=0;
    $results['importwarnings']=array();
    // these aren't used here, but are needed to avoid errors in post-import display
    $results['assessments']=0;
    $results['quota']=0;
    $results['quotamembers']=0;
    $results['quotals']=0;

    // collect information about survey and its language settings
    $surveyinfo = array();
    $surveyls = array();
    foreach ($adata as $row)
    {
        switch($row['class'])
        {
            case 'S':
                if (isset($row['text']) && $row['name'] != 'datecreated')
                {
                    $surveyinfo[$row['name']] = $row['text'];
                }
                break;
            case 'SL':
                if (!isset($surveyls[$row['language']]))
                {
                    $surveyls[$row['language']] = array();
                }
                if (isset($row['text']))
                {
                    $surveyls[$row['language']][$row['name']] = $row['text'];
                }
                break;
        }
    }

    $iOldSID = 1;
    if (isset($surveyinfo['sid']))
    {
        $iOldSID = (int) $surveyinfo['sid'];
    }

    // Create the survey entry
    $surveyinfo['startdate']=NULL;
    $surveyinfo['active']='N';
   // unset($surveyinfo['datecreated']);
    switchMSSQLIdentityInsert('surveys',true);
    $iNewSID = Survey::model()->insertNewSurvey($surveyinfo) ; //or safeDie($clang->gT("Error").": Failed to insert survey<br />");
    if ($iNewSID==false)
    {
        $results['error'] = Survey::model()->getErrors();
        $results['bFailed'] = true;
        return $results;
    }
    $surveyinfo['sid']=$iNewSID;
    $results['surveys']++;
    switchMSSQLIdentityInsert('surveys',false);
    $results['newsid']=$iNewSID;

    $gid=0;
    $gseq=0;    // group_order
    $qid=0;
    $qseq=0;    // question_order
    $qtype='T';
    $aseq=0;    // answer sortorder

    // set the language for the survey
    $_title='Missing Title';
    foreach ($surveyls as $_lang => $insertdata)
    {
        $insertdata['surveyls_survey_id'] = $iNewSID;
        $insertdata['surveyls_language'] = $_lang;
        if (isset($insertdata['surveyls_title']))
        {
            $_title = $insertdata['surveyls_title'];
        }
        else
        {
            $insertdata['surveyls_title'] = $_title;
        }


        $result = Surveys_languagesettings::model()->insertNewSurvey($insertdata);//
        if(!$result){
            $results['error'][] = $clang->gT("Error")." : ".$clang->gT("Failed to insert survey language");
            break;
        }
        $results['languages']++;
    }

    $ginfo=array();
    $qinfo=array();
    $sqinfo=array();

    if (isset($surveyinfo['language']))
    {
        $baselang = $surveyinfo['language'];    // the base language
    }

    $rownumber = 1;
    foreach ($adata as $row)
    {
        $rownumber += 1;
        switch($row['class'])
        {
            case 'G':
                // insert group
                $insertdata = array();
                $insertdata['sid'] = $iNewSID;
                $gname = ((isset($row['name']) ? $row['name'] : 'G' . $gseq));
                $insertdata['group_name'] = $gname;
                $insertdata['grelevance'] = (isset($row['relevance']) ? $row['relevance'] : '');
                $insertdata['description'] = (isset($row['text']) ? $row['text'] : '');
                $insertdata['language'] = (isset($row['language']) ? $row['language'] : $baselang);
                // For multi numeric survey : same title
                if (isset($ginfo[$gname]))
                {
                    $gseq = $ginfo[$gname]['group_order'];
                    $gid = $ginfo[$gname]['gid'];
                    $insertdata['gid'] = $gid;
                    $insertdata['group_order'] = $gseq;
                }
                else
                {
                    $insertdata['group_order'] = $gseq;
                }
                $newgid = Groups::model()->insertRecords($insertdata);
                if(!$newgid){
                    $results['error'][] = $clang->gT("Error")." : ".$clang->gT("Failed to insert group").". ".$clang->gT("Text file row number ").$rownumber." (".$gname.")";
                    break;
                }
                if (!isset($ginfo[$gname]))
                {
                    $results['groups']++;
                    $gid=$newgid; // save this for later
                    $ginfo[$gname]['gid'] = $gid;
                    $ginfo[$gname]['group_order'] = $gseq++;
                }
                $qseq=0;    // reset the question_order
                break;

            case 'Q':
                // insert question
                $insertdata = array();
                $insertdata['sid'] = $iNewSID;
                $qtype = (isset($row['type/scale']) ? $row['type/scale'] : 'T');
                $qname = (isset($row['name']) ? $row['name'] : 'Q' . $qseq);
                $insertdata['gid'] = $gid;
                $insertdata['type'] = $qtype;
                $insertdata['title'] = $qname;
                $insertdata['question'] = (isset($row['text']) ? $row['text'] : '');
                $insertdata['relevance'] = (isset($row['relevance']) ? $row['relevance'] : '');
                $insertdata['preg'] = (isset($row['validation']) ? $row['validation'] : '');
                $insertdata['help'] = (isset($row['help']) ? $row['help'] : '');
                $insertdata['language'] = (isset($row['language']) ? $row['language'] : $baselang);
                $insertdata['mandatory'] = (isset($row['mandatory']) ? $row['mandatory'] : '');
                $insertdata['other'] = (isset($row['other']) ? $row['other'] : 'N');
                $insertdata['same_default'] = (isset($row['same_default']) ? $row['same_default'] : 0);
                $insertdata['parent_qid'] = 0;

                // For multi numeric survey : same name, add the gid to have same name on different gid. Bad for EM.
                $fullqname="G{$gid}_".$qname;
                if (isset($qinfo[$fullqname]))
                {
                    $qseq = $qinfo[$fullqname]['question_order'];
                    $qid = $qinfo[$fullqname]['qid'];
                    $insertdata['qid']  = $qid;
                    $insertdata['question_order'] = $qseq;
                }
                else
                {
                    $insertdata['question_order'] = $qseq;
                }
                // Insert question and keep the qid for multi language survey
                $result = Questions::model()->insertRecords($insertdata);
                if(!$result){
                    $results['error'][] = $clang->gT("Error")." : ".$clang->gT("Could not insert question").". ".$clang->gT("Text file row number ").$rownumber." (".$qname.")";
                    break;
                }
                $newqid = $result;
                if (!isset($qinfo[$fullqname]))
                {
                    $results['questions']++;
                    $qid=$newqid; // save this for later
                    $qinfo[$fullqname]['qid'] = $qid;
                    $qinfo[$fullqname]['question_order'] = $qseq++;
                }
                $aseq=0;    //reset the answer sortorder
                $sqseq = 0;    //reset the sub question sortorder
                // insert question attributes
                foreach ($row as $key=>$val)
                {
                    switch($key)
                    {
                        case 'class':
                        case 'type/scale':
                        case 'name':
                        case 'text':
                        case 'validation':
                        case 'relevance':
                        case 'help':
                        case 'language':
                        case 'mandatory':
                        case 'other':
                        case 'same_default':
                        case 'default':
                            break;
                        default:
                            if ($key != '' && $val != '')
                            {
                                $insertdata = array();
                                $insertdata['qid'] = $qid;
                                $insertdata['language'] = (isset($row['language']) ? $row['language'] : $baselang);
                                $insertdata['attribute'] = $key;
                                $insertdata['value'] = $val;
                                $result=Question_attributes::model()->insertRecords($insertdata);//
                                if(!$result){
                                    $results['importwarnings'][] = $clang->gT("Warning")." : ".$clang->gT("Failed to insert question attribute").". ".$clang->gT("Text file row number ").$rownumber." ({$key})";
                                    break;
                                }
                                $results['question_attributes']++;
                            }
                            break;
                    }
                }

                // insert default value
                if (isset($row['default']))
                {
                    $insertdata=array();
                    $insertdata['qid'] = $qid;
                    $insertdata['language'] = (isset($row['language']) ? $row['language'] : $baselang);
                    $insertdata['defaultvalue'] = $row['default'];
                    $result = Defaultvalues::model()->insertRecords($insertdata);
                    if(!$result){
                        $results['importwarnings'][] = $clang->gT("Warning")." : ".$clang->gT("Failed to insert default value").". ".$clang->gT("Text file row number ").$rownumber;
                        break;
                    }
                    $results['defaultvalues']++;
                }
                break;

            case 'SQ':
                $sqname = (isset($row['name']) ? $row['name'] : 'SQ' . $sqseq);
                if ($qtype == 'O' || $qtype == '|')
                {
                    ;   // these are fake rows to show naming of comment and filecount fields
                }
                elseif ($sqname == 'other' && ($qtype == '!' || $qtype == 'L'))
                    {
                        // only want to set default value for 'other' in these cases - not a real SQ row
                        // TODO - this isn't working
                        if (isset($row['default']))
                        {
                            $insertdata=array();
                            $insertdata['qid'] = $qid;
                            $insertdata['specialtype'] = 'other';
                            $insertdata['language'] = (isset($row['language']) ? $row['language'] : $baselang);
                            $insertdata['defaultvalue'] = $row['default'];
                            $result = Defaultvalues::model()->insertRecords($insertdata);
                            if(!$result){
                                $results['importwarnings'][] = $clang->gT("Warning")." : ".$clang->gT("Failed to insert default value").". ".$clang->gT("Text file row number ").$rownumber;
                                break;
                            }
                            $results['defaultvalues']++;
                        }
                }
                else
                {
                    $insertdata = array();
                    $scale_id = (isset($row['type/scale']) ? $row['type/scale'] : 0);
                    $insertdata['sid'] = $iNewSID;
                    $insertdata['gid'] = $gid;
                    $insertdata['parent_qid'] = $qid;
                    $insertdata['type'] = $qtype;
                    $insertdata['title'] = $sqname;
                    $insertdata['question'] = (isset($row['text']) ? $row['text'] : '');
                    $insertdata['relevance'] = (isset($row['relevance']) ? $row['relevance'] : '');
                    $insertdata['preg'] = (isset($row['validation']) ? $row['validation'] : '');
                    $insertdata['help'] = (isset($row['help']) ? $row['help'] : '');
                    $insertdata['language'] = (isset($row['language']) ? $row['language'] : $baselang);
                    $insertdata['mandatory'] = (isset($row['mandatory']) ? $row['mandatory'] : '');
                    $insertdata['scale_id'] = $scale_id;
                    // For multi nueric language, qid is needed, why not gid. name is not unique.
                    $fullsqname = "G{$gid}Q{$qid}_{$sqname}";
                    if (isset($sqinfo[$fullsqname]))
                    {
                        $qseq = $sqinfo[$fullsqname]['question_order'];
                        $sqid = $sqinfo[$fullsqname]['sqid'];
                        $insertdata['question_order'] = $qseq;
                        $insertdata['qid'] = $sqid;
                    }
                    else
                    {
                        $insertdata['question_order'] = $qseq;
                    }
                    // Insert sub question and keep the sqid for multi language survey
                    $newsqid = Questions::model()->insertRecords($insertdata);
                    if(!$newsqid){
                        $results['error'][] = $clang->gT("Error")." : ".$clang->gT("Could not insert sub question").". ".$clang->gT("Text file row number ").$rownumber." (".$qname.")";
                        break;
                    }
                    if (!isset($sqinfo[$fullsqname]))
                    {
                        $sqinfo[$fullsqname]['question_order'] = $qseq++;
                        $sqid=$newsqid; // save this for later
                        $sqinfo[$fullsqname]['sqid'] = $sqid;
                        $results['subquestions']++;
                    }

                    // insert default value
                    if (isset($row['default']))
                    {
                        $insertdata=array();
                        $insertdata['qid'] = $qid;
                        $insertdata['sqid'] = $sqid;
                        $insertdata['scale_id'] = $scale_id;
                        $insertdata['language'] = (isset($row['language']) ? $row['language'] : $baselang);
                        $insertdata['defaultvalue'] = $row['default'];
                        $result = Defaultvalues::model()->insertRecords($insertdata);
                        if(!$result){
                            $results['importwarnings'][] = $clang->gT("Warning")." : ".$clang->gT("Failed to insert default value").". ".$clang->gT("Text file row number ").$rownumber;
                            break;
                        }
                        $results['defaultvalues']++;
                    }
                }
                break;
            case 'A':
                $insertdata = array();
                $insertdata['qid'] = $qid;
                $insertdata['code'] = (isset($row['name']) ? $row['name'] : 'A' . $aseq);
                $insertdata['answer'] = (isset($row['text']) ? $row['text'] : '');
                $insertdata['scale_id'] = (isset($row['type/scale']) ? $row['type/scale'] : 0);
                $insertdata['language']= (isset($row['language']) ? $row['language'] : $baselang);
                $insertdata['assessment_value'] = (isset($row['relevance']) ? $row['relevance'] : '');
                $insertdata['sortorder'] = ++$aseq;
                $result = Answers::model()->insertRecords($insertdata); // or safeDie("Error: Failed to insert answer<br />");
                if(!$result){
                    $results['error'][] = $clang->gT("Error")." : ".$clang->gT("Could not insert answer").". ".$clang->gT("Text file row number ").$rownumber;
                }
                $results['answers']++;
                break;
        }

    }

    // Delete the survey if error found
    if(is_array($results['error']))
    {
        $result = Survey::model()->deleteSurvey($iNewSID);
    }
    else
    {
        LimeExpressionManager::SetSurveyId($iNewSID);
        LimeExpressionManager::RevertUpgradeConditionsToRelevance($iNewSID);
        LimeExpressionManager::UpgradeConditionsToRelevance($iNewSID);
    }
    
    return $results;
}

