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
 *	$Id: import_functions.php 10193 2011-06-05 12:20:37Z c_schmitz $
 *	Files Purpose:
 */

/**
 * CSVImportLabelset()
 * Function responsible to import label set from CSV format.
 * @param mixed $sFullFilepath
 * @param mixed $options
 * @return
 */
function CSVImportLabelset($sFullFilepath, $options)
        {
            //global $dbprefix, $connect, $clang;
            $CI =& get_instance();
            $CI->load->helper('database');
            $clang = $CI->limesurvey_lang;
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
                return $results['fatalerror']=$clang->gT("This file is not a LimeSurvey label set file. Import failed.");
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

                    $newvalues=array_values($labelsetrowdata);
                    //$newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                    $lsainsert = "insert INTO ".$CI->db->dbprefix."labelsets (".implode(',',array_keys($labelsetrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
                    $lsiresult= db_execute_assoc($lsainsert);
                    //$lsiresult=$connect->Execute($lsainsert);
                    $results['labelsets']++;

                    // Get the new insert id for the labels inside this labelset
                    $newlid=$CI->db->insert_id(); //$connect->Insert_ID("{$dbprefix}labelsets",'lid');

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
                                $labelrowdata['title']=translink('label', $oldlid, $newlid, $labelrowdata['title']);
                                if (!isset($labelrowdata["assessment_value"]))
                                {
                                    $labelrowdata["assessment_value"]=(int)$labelrowdata["code"];
                                }

                                $newvalues=array_values($labelrowdata);
                                //$newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                                $lainsert = "insert INTO ".$CI->db->dbprefix."labels (".implode(',',array_keys($labelrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
                                $liresult=db_execute_assoc($lainsert);
                                //$liresult=$connect->Execute($lainsert);
                                $results['labels']++;
                            }
                        }
                    }

                    //CHECK FOR DUPLICATE LABELSETS

                    if (isset($_POST['checkforduplicates']))
                    {
                        $thisset="";
                        $query2 = "SELECT code, title, sortorder, language, assessment_value
                                   FROM ".$CI->db->dbprefix."labels
                                   WHERE lid=".$newlid."
                                   ORDER BY language, sortorder, code";
                        $result2 = db_execute_assoc($query2) or show_error("Died querying labelset $lid<br />$query2<br />");
                        foreach($result2->result_array() as $row2)
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
                                //			echo $val."-".$newcs."<br/>";  For debug purposes
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
                            $query = "DELETE FROM ".$CI->db->dbprefix."labels WHERE lid=$newlid";
                            $result=$connect->Execute($query) or show_error("Couldn't delete labels<br />$query<br />");
                            $query = "DELETE FROM ".$CI->db->dbprefix."labelsets WHERE lid=$newlid";
                            $result=$connect->Execute($query) or show_error("Couldn't delete labelset<br />$query<br />");
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
        //global $connect, $dbprefix, $clang;
        $CI =& get_instance();
        $CI->load->helper('database');
        $clang = $CI->limesurvey_lang;
        $xml = simplexml_load_file($sFullFilepath);
        if ($xml->LimeSurveyDocType!='Label set') show_error('This is not a valid LimeSurvey label set structure XML file.');
        $dbversion = (int) $xml->DBVersion;
        $csarray=buildLabelSetCheckSumArray();
        $aLSIDReplacements=array();
        $results['labelsets']=0;
        $results['labels']=0;
        $results['warnings']=array();
        $dbprefix = $CI->db->dbprefix;

        // Import labels table ===================================================================================

        //$tablename=$dbprefix.'labelsets';
        foreach ($xml->labelsets->rows->row as $row)
        {
           $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $oldlsid=$insertdata['lid'];
            unset($insertdata['lid']); // save the old qid

            $CI->load->model('labelsets_model');


            // Insert the new question
            //$query=$connect->GetInsertSQL($tablename,$insertdata);
            $result = $CI->labelsets_model->insertRecords($insertdata) or show_error($clang->gT("Error").": Failed to insert data<br />");
            $results['labelsets']++;

            $newlsid=$CI->db->insert_id(); //$connect->Insert_ID($tablename,"lid"); // save this for later
            $aLSIDReplacements[$oldlsid]=$newlsid; // add old and new lsid to the mapping array
        }


        // Import labels table ===================================================================================

        //$tablename=$dbprefix.'labels';
        foreach ($xml->labels->rows->row as $row)
        {
           $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['lid']=$aLSIDReplacements[$insertdata['lid']];
            $CI->load->model('labels_model');

            //$query=$connect->GetInsertSQL($tablename,$insertdata);
            $result = $CI->labels_model->insertRecords($insertdata) or show_error($clang->gT("Error").": Failed to insert data<br />");
            $results['labels']++;
        }

        //CHECK FOR DUPLICATE LABELSETS

        if (isset($_POST['checkforduplicates']))
        {
            foreach (array_values($aLSIDReplacements) as $newlid)
            {
                $thisset="";
                $query2 = "SELECT code, title, sortorder, language, assessment_value
                           FROM ".$CI->db->dbprefix."labels
                           WHERE lid=".$newlid."
                           ORDER BY language, sortorder, code";
                $result2 = db_execute_assoc($query2) or show_error("Died querying labelset $lid<br />");
                foreach($result2->result_array() as $row2)
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
                    $query = "DELETE FROM ".$CI->db->dbprefix."labels WHERE lid=$newlid";
                    $result=db_execute_assoc($query) or show_error("Couldn't delete labels<br />$query<br />");
                    $results['labels']=$results['labels']-$CI->db->affected_rows();
                    $query = "DELETE FROM ".$CI->db->dbprefix."labelsets WHERE lid=$newlid";
                    $result=db_execute_assoc($query) or show_error("Couldn't delete labelset<br />$query<br />");
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
function CSVImportSurvey($sFullFilepath,$iDesiredSurveyId=NULL)
{
    //global $dbprefix, $connect, $timeadjust, $clang;

    $CI =& get_instance();
    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;
    require_once ($CI->config->item('rootdir').'/application/third_party/adodb/adodb.inc.php');
    $connect = ADONewConnection($CI->db->dbdriver);

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
    $oldsid=$surveyrowdata["sid"];

    if($iDesiredSurveyId!=NULL)
        $oldsid = $iDesiredSurveyId;

    if (!$oldsid)
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
            echo $clang->gT("Import of this survey file failed")."\n".$clang->gT("File does not contain LimeSurvey data in the correct format.")."\n";
            return;
        }
    }

    $newsid = GetNewSurveyID($oldsid);

    $insert=$surveyarray[0];
    $sfieldorders  =convertCSVRowToArray($surveyarray[0],',','"');
    $sfieldcontents=convertCSVRowToArray($surveyarray[1],',','"');
    $surveyrowdata=array_combine($sfieldorders,$sfieldcontents);
    // Set new owner ID
    $surveyrowdata['owner_id']=$CI->session->userdata('loginID');
    // Set new survey ID
    $surveyrowdata['sid']=$newsid;
    $surveyrowdata['active']='N';

    if (validate_templatedir($surveyrowdata['template'])!==$surveyrowdata['template']) $importresults['importwarnings'][] = sprintf($clang->gT('Template %s not found, please review when activating.'),$surveyrowdata['template']);

    if (isset($surveyrowdata['datecreated'])) {$surveyrowdata['datecreated']=$connect->BindTimeStamp($surveyrowdata['datecreated']);}
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
    if (!isset($surveyrowdata['datecreated']) || $surveyrowdata['datecreated']=='' || $surveyrowdata['datecreated']=='null') {$surveyrowdata['datecreated']=$connect->BindTimeStamp(date_shift(date("Y-m-d H:i:s"), "Y-m-d", $CI->config->item('timeadjust'))); }

    $CI->load->model('surveys_model');
    $iresult = $CI->surveys_model->insertNewSurvey($surveyrowdata) or show_error("<br />".$clang->gT("Import of this survey file failed")."<br />{$surveyarray[0]}<br /><br />\n" );
    /**

    $values=array_values($surveyrowdata);
    $values=array_map(array(&$connect, "qstr"),$values); // quote everything accordingly
    $insert = "INSERT INTO ".$CI->db->dbprefix."surveys (".implode(',',array_keys($surveyrowdata)).") VALUES (".implode(',',$values).")"; //handle db prefix
    $iresult = db_execute_assoc($insert) or safe_die("<br />".$clang->gT("Import of this survey file failed")."<br />\n[$insert]<br />{$surveyarray[0]}<br /><br />\n" );
    */
    // Now import the survey language settings
    $fieldorders=convertCSVRowToArray($surveylsarray[0],',','"');
    unset($surveylsarray[0]);
    foreach ($surveylsarray as $slsrow) {
        $fieldcontents=convertCSVRowToArray($slsrow,',','"');
        $surveylsrowdata=array_combine($fieldorders,$fieldcontents);
        // convert back the '\'.'n' char from the CSV file to true return char "\n"
        $surveylsrowdata=array_map('convertCsvreturn2return', $surveylsrowdata);
        // Convert the \n return char from welcometext to <br />

        // translate internal links
        $surveylsrowdata['surveyls_title']=translink('survey', $oldsid, $newsid, $surveylsrowdata['surveyls_title']);
        $surveylsrowdata['surveyls_description']=translink('survey', $oldsid, $newsid, $surveylsrowdata['surveyls_description']);
        $surveylsrowdata['surveyls_welcometext']=translink('survey', $oldsid, $newsid, $surveylsrowdata['surveyls_welcometext']);
        $surveylsrowdata['surveyls_urldescription']=translink('survey', $oldsid, $newsid, $surveylsrowdata['surveyls_urldescription']);
        $surveylsrowdata['surveyls_email_invite']=translink('survey', $oldsid, $newsid, $surveylsrowdata['surveyls_email_invite']);
        $surveylsrowdata['surveyls_email_remind']=translink('survey', $oldsid, $newsid, $surveylsrowdata['surveyls_email_remind']);
        $surveylsrowdata['surveyls_email_register']=translink('survey', $oldsid, $newsid, $surveylsrowdata['surveyls_email_register']);
        $surveylsrowdata['surveyls_email_confirm']=translink('survey', $oldsid, $newsid, $surveylsrowdata['surveyls_email_confirm']);
        unset($surveylsrowdata['lastpage']);
        $surveylsrowdata['surveyls_survey_id']=$newsid;

        $CI->load->model('surveys_languagesettings_model');
        $lsiresult = $CI->surveys_languagesettings_model->insertNewSurvey($surveylsrowdata) or show_error("<br />".$clang->gT("Import of this survey file failed")."<br />");
        /**
        $newvalues=array_values($surveylsrowdata);
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
        $lsainsert = "INSERT INTO {$dbprefix}surveys_languagesettings (".implode(',',array_keys($surveylsrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
        $lsiresult=$connect->Execute($lsainsert) or safe_die("<br />".$clang->gT("Import of this survey file failed")."<br />\n[$lsainsert]<br />\n" . $connect->ErrorMsg() );
        */
    }

    // The survey languagesettings are imported now
    $aLanguagesSupported = array();  // this array will keep all the languages supported for the survey

    $sBaseLanguage = GetBaseLanguageFromSurveyID($newsid);
    $aLanguagesSupported[]=$sBaseLanguage;     // adds the base language to the list of supported languages
    $aLanguagesSupported=array_merge($aLanguagesSupported,GetAdditionalLanguagesFromSurveyID($newsid));


    // DO SURVEY_RIGHTS
    GiveAllSurveyPermissions($_SESSION['loginID'],$newsid);
    $importresults['deniedcountls'] =0;


    $qtypes = getqtypelist("" ,"array");
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

            $CI->load->model('labelsets_model');
            $lsiresult = $CI->labelsets_model->insertRecords($labelsetrowdata);
            /**

            $newvalues=array_values($labelsetrowdata);
            $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
            $lsainsert = "INSERT INTO {$dbprefix}labelsets (".implode(',',array_keys($labelsetrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
            $lsiresult=$connect->Execute($lsainsert);
            */
            $results['labelsets']++;
            // Get the new insert id for the labels inside this labelset
            $newlid=$CI->db->insert_id(); //$connect->Insert_ID("{$dbprefix}labelsets",'lid');

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


                        $CI->load->model('labels_model');
                        $liresult = $CI->labels_model->insertRecords($labelrowdata);
                        /**
                        $newvalues=array_values($labelrowdata);
                        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                        $lainsert = "INSERT INTO {$dbprefix}labels (".implode(',',array_keys($labelrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
                        $liresult=$connect->Execute($lainsert);
                        */
                        if ($liresult!==false) $results['labels']++;
                    }
                }
            }

            //CHECK FOR DUPLICATE LABELSETS
            $thisset="";

            $query2 = "SELECT code, title, sortorder, language, assessment_value
                       FROM ".$CI->db->dbprefix."labels
                       WHERE lid=".$newlid."
                       ORDER BY language, sortorder, code";
            $result2 = db_execute_assoc($query2) or safe_die("Died querying labelset $lid<br />$query2<br />".$connect->ErrorMsg());
            foreach($result2->result_array() as $row2)
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
            if (isset($lsmatch) || ($CI->session->userdata('USER_RIGHT_MANAGE_LABEL') != 1))
            {
                //There is a matching labelset or the user is not allowed to edit labels -
                // So, we will delete this one and refer to the matched one.
                $query = "DELETE FROM ".$CI->db->dbprefix."labels WHERE lid=$newlid";
                $result=db_execute_assoc($query) or safe_die("Couldn't delete labels<br />$query<br />");
                $results['labels']=$results['labels']-$CI->db->affected_rows();

                $query = "DELETE FROM ".$CI->db->dbprefix."labelsets WHERE lid=$newlid";
                $result=db_execute_assoc($query) or safe_die("Couldn't delete labelset<br />$query<br />");
                $results['labelsets']=$results['labelsets']-$CI->db->affected_rows();
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
            if ($grouprowdata['sid'] != $oldsid)
            {
                $results['fatalerror'] = $clang->gT("A group in the CSV/SQL file is not part of the same survey. The import of the survey was stopped.")."<br />\n";
                return $results;
            }
            $grouprowdata['sid']=$newsid;
            // remember group id
            $oldgid=$grouprowdata['gid'];

            //update/remove the old group id
            if (isset($aGIDReplacements[$oldgid]))
            $grouprowdata['gid'] = $aGIDReplacements[$oldgid];
            else
            unset($grouprowdata['gid']);

            // Everything set - now insert it
            $grouprowdata=array_map('convertCsvreturn2return', $grouprowdata);

            // translate internal links
            $grouprowdata['group_name']=translink('survey', $oldsid, $newsid, $grouprowdata['group_name']);
            $grouprowdata['description']=translink('survey', $oldsid, $newsid, $grouprowdata['description']);

            if (isset($grouprowdata['gid'])) db_switchIDInsert('groups',true);

            $CI->load->model('groups_model');
            $gres = $CI->groups_model->insertRecords($grouprowdata) or show_error($clang->gT('Error').": Failed to insert group<br />\<br />\n");

            /**
            $tablename=$CI->db->dbprefix.'groups';
            $ginsert = $connect->GetinsertSQL($tablename,$grouprowdata);
            $gres = $connect->Execute($ginsert) or safe_die($clang->gT('Error').": Failed to insert group<br />\n$ginsert<br />\n".$connect->ErrorMsg());
            */
            if (isset($grouprowdata['gid'])) db_switchIDInsert('groups',false);
            //GET NEW GID
            if (!isset($grouprowdata['gid'])) {$aGIDReplacements[$oldgid]=$CI->db->insert_id(); }//$connect->Insert_ID("{$dbprefix}groups","gid");}
        }
        // Fix sortorder of the groups  - if users removed groups manually from the csv file there would be gaps
        fixSortOrderGroups($newsid);
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
            $questionrowdata=array_map('convertCsvreturn2return', $questionrowdata);
            $questionrowdata["type"]=strtoupper($questionrowdata["type"]);

            // Skip not supported languages
            if (!in_array($questionrowdata['language'],$aLanguagesSupported))
                continue;

            // replace the sid
            $questionrowdata["sid"] = $newsid;
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
            $questionrowdata['title']=translink('survey', $oldsid, $newsid, $questionrowdata['title']);
            $questionrowdata['question']=translink('survey', $oldsid, $newsid, $questionrowdata['question']);
            $questionrowdata['help']=translink('survey', $oldsid, $newsid, $questionrowdata['help']);


            if (isset($questionrowdata['qid'])) {
                db_switchIDInsert('questions',true);
            }

            $CI->load->model('questions_model');
            $qres = $CI->questions_model->insertRecords($questionrowdata) or show_error ($clang->gT("Error").": Failed to insert question<br />");

            /**
            $tablename=$dbprefix.'questions';
            $qinsert = $connect->GetInsertSQL($tablename,$questionrowdata);
            $qres = $connect->Execute($qinsert) or safe_die ($clang->gT("Error").": Failed to insert question<br />\n$qinsert<br />\n".$connect->ErrorMsg());
            */
            if (isset($questionrowdata['qid'])) {
                db_switchIDInsert('questions',false);
                $saveqid=$questionrowdata['qid'];
            }
            else
            {
                $aQIDReplacements[$oldqid]=$CI->db->insert_id(); //$connect->Insert_ID("{$dbprefix}questions",'qid');
                $saveqid=$aQIDReplacements[$oldqid];
            }


            // Now we will fix up old label sets where they are used as answers
            if (((isset($oldlid1) && isset($aLIDReplacements[$oldlid1])) || (isset($oldlid2) && isset($aLIDReplacements[$oldlid2]))) && ($qtypes[$questionrowdata['type']]['answerscales']>0 || $qtypes[$questionrowdata['type']]['subquestions']>1))
            {
                $query="select * from ".$CI->db->dbprefix."labels where lid={$aLIDReplacements[$oldlid1]} and language='{$questionrowdata['language']}'";
                $oldlabelsresult=db_execute_assoc($query);
                foreach($oldlabelsresult->result_array() as $labelrow)
                {
                    if (in_array($labelrow['language'],$aLanguagesSupported))
                    {

                        if ($qtypes[$questionrowdata['type']]['subquestions']<2)
                        {
                            $qinsert = "insert INTO ".$CI->db->dbprefix."answers (qid,code,answer,sortorder,language,assessment_value)
                                        VALUES ({$aQIDReplacements[$oldqid]},'".$labelrow['code']."','".$labelrow['title']."','".$labelrow['sortorder']."','".$labelrow['language']."','".$labelrow['assessment_value']."')";
                            $qres = db_execute_assoc($qinsert) or show_error ($clang->gT("Error").": Failed to insert answer (lid1) <br />\n$qinsert<br />\n");
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

                            $qinsert = "insert INTO ".$CI->db->dbprefix."questions ($fieldname parent_qid,title,question,question_order,language,scale_id,type, sid, gid)
                                        VALUES ($data{$aQIDReplacements[$oldqid]},'".$labelrow['code']."','".$labelrow['title']."','".$labelrow['sortorder']."','".$labelrow['language']."',1,'{$questionrowdata['type']}',{$questionrowdata['sid']},{$questionrowdata['gid']})";
                            $qres = db_execute_assoc($qinsert) or show_error ($clang->gT("Error").": Failed to insert question <br />\n$qinsert<br />\n");
                            if ($fieldname=='')
                            {
                               $aSQIDReplacements[$labelrow['code'].'_'.$saveqid]=$CI->db->insert_id(); //$connect->Insert_ID("{$dbprefix}questions","qid");
                            }
                        }
                    }
                }
                if (isset($oldlid2) && $qtypes[$questionrowdata['type']]['answerscales']>1)
                {
                    $query="select * from ".$CI->db->dbprefix."labels where lid={$aLIDReplacements[$oldlid2]} and language='{$questionrowdata['language']}'";
                    $oldlabelsresult=db_execute_assoc($query);
                    foreach($oldlabelsresult->result_array() as $labelrow)
                    {
                        $qinsert = "insert INTO ".$CI->db->dbprefix."answers (qid,code,answer,sortorder,language,assessment_value,scale_id)
                                    VALUES ({$aQIDReplacements[$oldqid]},'".$labelrow['code']."','".$labelrow['title']."','".$labelrow['sortorder']."','".$labelrow['language']."','".$labelrow['assessment_value']."',1)";
                        $qres = db_execute_assoc($qinsert) or safe_die ($clang->gT("Error").": Failed to insert answer (lid2)<br />\n$qinsert<br />\n");
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
        $CI->load->model('defaultvalues_model');
        $CI->load->model('answers_model');
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
            $query1 = 'select type,gid from '.$CI->db->dbprefix.'questions where qid='.$answerrowdata["qid"];
            $resultquery1 = db_execute_assoc($query1);
            $questiontemp=$resultquery1->row_array(); //$connect->GetRow('select type,gid from '.$CI->db->dbprefix.'questions where qid='.$answerrowdata["qid"]);)
            $oldquestion['newtype']=$questiontemp['type'];
            $oldquestion['gid']=$questiontemp['gid'];
            if ($answerrowdata['default_value']=='Y' && ($oldquestion['newtype']=='L' || $oldquestion['newtype']=='O' || $oldquestion['newtype']=='!'))
            {
                $insertdata=array();
                $insertdata['qid']=$newqid;
                $insertdata['language']=$answerrowdata['language'];
                $insertdata['defaultvalue']=$answerrowdata['answer'];
                $qres = $CI->defaultvalues_model->insertRecords($insertdata) or show_error ("Error: Failed to insert defaultvalue <br />");
                /**
                $query=$connect->GetInsertSQL($dbprefix.'defaultvalues',$insertdata);
                $qres = $connect->Execute($query) or safe_die ("Error: Failed to insert defaultvalue <br />{$query}<br />\n".$connect->ErrorMsg());
                */

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
                }
                $questionrowdata['parent_qid']=$answerrowdata['qid'];;
                $questionrowdata['sid']=$newsid;
                $questionrowdata['gid']=$oldquestion['gid'];
                $questionrowdata['title']=$answerrowdata['code'];
                $questionrowdata['question']=$answerrowdata['answer'];
                $questionrowdata['question_order']=$answerrowdata['sortorder'];
                $questionrowdata['language']=$answerrowdata['language'];
                $questionrowdata['type']=$oldquestion['newtype'];



                /**
                $tablename=$dbprefix.'questions';
                $query=$connect->GetInsertSQL($tablename,$questionrowdata); */
                if (isset($questionrowdata['qid'])) db_switchIDInsert('questions',true);
                $qres= $CI->answers_model->insertRecords($questionrowdata) or show_error("Error: Failed to insert subquestion <br />");
                //$qres = $connect->Execute($query) or safe_die ("Error: Failed to insert subquestion <br />{$query}<br />".$connect->ErrorMsg());
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
                    //$tablename=$dbprefix.'defaultvalues';
                    //$query=$connect->GetInsertSQL($tablename,$insertdata);
                    $qres = $CI->defaultvalues_model->insertRecords($insertdata) or show_error("Error: Failed to insert defaultvalue <br />");
                }

            }
            else   // insert answers
            {
                unset($answerrowdata['default_value']);
                //$tablename=$dbprefix.'answers';
                //$query=$connect->GetInsertSQL($tablename,$answerrowdata);
                $ares = $CI->answers_model->insertRecords($answerrowdata) or show_error("Error: Failed to insert answer<br />");
                $results['answers']++;
            }

        }
    }

    // get all group ids and fix questions inside each group
    $gquery = "SELECT gid FROM ".$CI->db->dbprefix."groups where sid=$newsid group by gid ORDER BY gid"; //Get last question added (finds new qid)
    $gres = db_execute_assoc($gquery);
    foreach ($gres->result_array() as $grow)
    {
        fixsortorderQuestions($grow['gid'], $newsid);
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

            $newvalues=array_values($qarowdata);
            $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
            $qainsert = "INSERT INTO ".$CI->db->dbprefix."question_attributes (".implode(',',array_keys($qarowdata)).") VALUES (".implode(',',$newvalues).")";
            $result=db_execute_assoc($qainsert); // no safe_die since some LimeSurvey version export duplicate question attributes - these are just ignored
            if ($CI->db->affected_rows()>0) {$importresults['question_attributes']++;}
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

            $asrowdata["sid"]=$newsid;
            unset($asrowdata["id"]);
            $CI->load->model('assessments_model');

            //$tablename=$dbprefix.'assessments';
            //$asinsert = $connect->GetInsertSQL($tablename,$asrowdata);
            $result=$CI->assessments_model->insertRecords($asrowdata) or show_error("Couldn't insert assessment<br />");

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

            $oldsid=$asrowdata["sid"];
            foreach ($substitutions as $subs) {
                if ($oldsid==$subs[0]) {$newsid=$subs[3];}
            }

            $asrowdata["sid"]=$newsid;
            $oldid = $asrowdata["id"];
            unset($asrowdata["id"]);
            $quotadata[]=$asrowdata; //For use later if needed
            $CI->load->model('quotas_model');
            //$tablename=$dbprefix.'quota';
            //$asinsert = $connect->getInsertSQL($tablename,$asrowdata);
            $result=$CI->quotas_model->insertRecords($asrowdata) or safe_die ("Couldn't insert quota<br />");
            $aQuotaReplacements[$oldid] = $CI->db->insert_id(); // $connect->Insert_ID(db_table_name_nq('quota'),"id");
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

            $newquotaid=$aQuotaReplacements[$oldquotaid];

            $asrowdata["sid"]=$newsid;
            $asrowdata["qid"]=$newqid;
            $asrowdata["quota_id"]=$newquotaid;
            unset($asrowdata["id"]);
            $CI->load->model('quota_members_model');
            //$tablename=$dbprefix.'quota_members';
            //$asinsert = $connect->getInsertSQL($tablename,$asrowdata);

            $result=$CI->quota_members_model->insertRecords($asrowdata) or show_error("Couldn't insert quota<br />");

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
            $CI->load->model('quota_languagesettings_model');
            //$tablename=$dbprefix.'quota_languagesettings';
            //$asinsert = $connect->getInsertSQL($tablename,$asrowdata);
            $result=$CI->quota_languagesettings_model->insertRecords($asrowdata) or show_error("Couldn't insert quota<br />");
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
        //$tablename=$dbprefix.'quota_languagesettings';
        $CI->load->model('quota_languagesettings_model');
        //$asinsert = $connect->getInsertSQL($tablename,$asrowdata);
        $result=$CI->quota_languagesettings_model->insertRecords($asrowdata) or show_error("Couldn't insert quota<br />");
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
            $oldgid=array_search($connect->GetOne('select gid from '.db_table_name('questions').' where qid='.$aQIDReplacements[$conditionrowdata["cqid"]]),$aGIDReplacements);
            $conditionrowdata["qid"]=$aQIDReplacements[$conditionrowdata["qid"]];
            $conditionrowdata["cqid"]=$aQIDReplacements[$conditionrowdata["cqid"]];
            $oldcfieldname=$conditionrowdata["cfieldname"];
            $conditionrowdata["cfieldname"]=str_replace($oldsid.'X'.$oldgid.'X'.$oldcqid,$newsid.'X'.$aGIDReplacements[$oldgid].'X'.$conditionrowdata["cqid"],$conditionrowdata["cfieldname"]);

            //$tablename=$dbprefix.'conditions';
            $CI->load->model('conditions_model');
            $conditioninsert = $connect->getInsertSQL($tablename,$conditionrowdata);
            $result=$CI->conditions_model->insertRecords($conditionrowdata) or show_error("Couldn't insert condition<br />");

        }
    }
    $importresults['importversion']=$importversion;
    $importresults['newsid']=$newsid;
    $importresults['oldsid']=$oldsid;
    return $importresults;
}



/**
* This function imports a LimeSurvey .lss survey XML file
*
* @param mixed $sFullFilepath  The full filepath of the uploaded file
*/
function XMLImportSurvey($sFullFilepath,$sXMLdata=NULL,$sNewSurveyName=NULL,$iDesiredSurveyId=NULL, $bTranslateInsertansTags=true)
{
    //global $connect, $dbprefix, $clang, $timeadjust;

    $CI =& get_instance();

    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;
    require_once ($CI->config->item('rootdir').'/application/third_party/adodb/adodb.inc.php');
    $connect = ADONewConnection($CI->db->dbdriver);
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

    $dbversion = (int) $xml->DBVersion;
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
    $results['importwarnings']=array();


    $aLanguagesSupported=array();
    foreach ($xml->languages->language as $language)
    {
        $aLanguagesSupported[]=(string)$language;
    }
    $results['languages']=count($aLanguagesSupported);


    // First get an overview of fieldnames - it's not useful for the moment but might be with newer versions
    /*
    $fieldnames=array();
    foreach ($xml->questions->fields->fieldname as $fieldname )
    {
        $fieldnames[]=(string)$fieldname;
    };*/


    // Import surveys table ===================================================================================

    //$tablename=$CI->db->dbprefix.'surveys';
    $CI->load->model('surveys_model');

    foreach ($xml->surveys->rows->row as $row)
    {
        $insertdata=array();

        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }

        $oldsid=$insertdata['sid'];
        if($iDesiredSurveyId!=NULL)
        {
            $newsid=GetNewSurveyID($iDesiredSurveyId);
        }
        else
        {
            $newsid=GetNewSurveyID($oldsid);
        }

        //Now insert the new SID and change some values
        $insertdata['sid']=$newsid;
        //Make sure it is not set active
        $insertdata['active']='N';
        //Set current user to be the owner
        $insertdata['owner_id']=$CI->session->userdata('loginID');
        //Change creation date to import date

        $insertdata['datecreated']=$connect->BindTimeStamp(date_shift(date("Y-m-d H:i:s"), "Y-m-d", $CI->config->item('timeadjust')));

        if ($insertdata['expires'] == '')
        {
            $insertdata['expires'] = NULL;
        }
        if ($insertdata['startdate'] == '')
        {
            $insertdata['startdate'] = NULL;
        }
        if ($insertdata['bouncetime'] == '')
        {
            $insertdata['bouncetime'] = NULL;
        }

        db_switchIDInsert('surveys',true);
        //$query=$connect->GetInsertSQL($tablename,$insertdata);

        $result = $CI->surveys_model->insertNewSurvey($insertdata) or show_error($clang->gT("Error").": Failed to insert data<br />");

        $results['surveys']++;
        db_switchIDInsert('surveys',false);
    }

    $results['newsid']=$newsid;

    // Import survey languagesettings table ===================================================================================

    //$tablename=$dbprefix.'surveys_languagesettings';
    $CI->load->model('surveys_languagesettings_model');
    foreach ($xml->surveys_languagesettings->rows->row as $row)
    {
        
        $insertdata=array();
        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }
        $insertdata['surveyls_survey_id']=$newsid;
        if ($sNewSurveyName == NULL)
        {
            $insertdata['surveyls_title']=translink('survey', $oldsid, $newsid, $insertdata['surveyls_title']);
        } else {
            $insertdata['surveyls_title']=translink('survey', $oldsid, $newsid, $sNewSurveyName);
        }

        $insertdata['surveyls_description']=translink('survey', $oldsid, $newsid, $insertdata['surveyls_description']);
        $insertdata['surveyls_welcometext']=translink('survey', $oldsid, $newsid, $insertdata['surveyls_welcometext']);
        $insertdata['surveyls_urldescription']=translink('survey', $oldsid, $newsid, $insertdata['surveyls_urldescription']);
        $insertdata['surveyls_email_invite']=translink('survey', $oldsid, $newsid, $insertdata['surveyls_email_invite']);
        $insertdata['surveyls_email_remind']=translink('survey', $oldsid, $newsid, $insertdata['surveyls_email_remind']);
        $insertdata['surveyls_email_register']=translink('survey', $oldsid, $newsid, $insertdata['surveyls_email_register']);
        $insertdata['surveyls_email_confirm']=translink('survey', $oldsid, $newsid, $insertdata['surveyls_email_confirm']);

        //$query=$connect->GetInsertSQL($tablename,$insertdata);
        $result = $CI->surveys_languagesettings_model->insertNewSurvey($insertdata) or show_error($clang->gT("Error").": Failed to insert data<br />");
    }


    // Import groups table ===================================================================================

    //$tablename=$dbprefix.'groups';
    $CI->load->model('groups_model');
    foreach ($xml->groups->rows->row as $row)
    {
       $insertdata=array();
        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }
        $oldsid=$insertdata['sid'];
        $insertdata['sid']=$newsid;
        $oldgid=$insertdata['gid']; unset($insertdata['gid']); // save the old qid

        // now translate any links
        $insertdata['group_name']=translink('survey', $oldsid, $newsid, $insertdata['group_name']);
        $insertdata['description']=translink('survey', $oldsid, $newsid, $insertdata['description']);
        // Insert the new group
        if (isset($aGIDReplacements[$oldgid]))
        {
           db_switchIDInsert('groups',true);
           $insertdata['gid']=$aGIDReplacements[$oldgid];
        }
        //$query=$connect->GetInsertSQL($tablename,$insertdata);
        $result = $CI->groups_model->insertRecords($insertdata) or show_error($clang->gT("Error").": Failed to insert data<br />");
        $results['groups']++;

        if (!isset($aGIDReplacements[$oldgid]))
        {
            $newgid=$CI->db->insert_id(); //$connect->Insert_ID($tablename,"gid"); // save this for later
            $aGIDReplacements[$oldgid]=$newgid; // add old and new qid to the mapping array
        }
        else
        {
           db_switchIDInsert('groups',false);
        }
    }


    // Import questions table ===================================================================================

    // We have to run the question table data two times - first to find all main questions
    // then for subquestions (because we need to determine the new qids for the main questions first)
    if(isset($xml->questions))  // there could be surveys without a any questions
    {
        //$tablename=$dbprefix.'questions';
        $CI->load->model('questions_model');
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
            //$query=$connect->GetInsertSQL($tablename,$insertdata);
            $result = $CI->questions_model->insertRecords($insertdata) or show_error($clang->gT("Error").": Failed to insert data<br />");
            if (!isset($aQIDReplacements[$oldqid]))
            {
                $newqid=$CI->db->insert_id(); //$connect->Insert_ID($tablename,"qid"); // save this for later
                $aQIDReplacements[$oldqid]=$newqid; // add old and new qid to the mapping array
                $results['questions']++;
            }
            else
            {
               db_switchIDInsert('questions',false);
            }
        }
    }

    // Import subquestions --------------------------------------------------------------
    if(isset($xml->subquestions))
    {
        //$tablename=$dbprefix.'questions';
        $CI->load->model('questions_model');
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
               db_switchIDInsert('questions',true);
            }

            //$query=$connect->GetInsertSQL($tablename,$insertdata);
            $result = $CI->questions_model->insertRecords($insertdata) or show_error($clang->gT("Error").": Failed to insert data<br />");
            $newsqid=$CI->db->insert_id(); //$connect->Insert_ID($tablename,"qid"); // save this for later
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
        //$tablename=$dbprefix.'answers';
        $CI->load->model('answers_model');
        foreach ($xml->answers->rows->row as $row)
        {
           $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['qid']=$aQIDReplacements[(int)$insertdata['qid']]; // remap the parent_qid

            // now translate any links
            $insertdata['answer']=translink('survey', $oldsid, $newsid, $insertdata['answer']);
            //$query=$connect->GetInsertSQL($tablename,$insertdata);
            $result=$CI->answers_model->insertRecords($insertdata) or show_error($clang->gT("Error").": Failed to insert data<br />");
            $results['answers']++;
        }
    }

    // Import questionattributes --------------------------------------------------------------
    if(isset($xml->question_attributes))
    {
        //$tablename=$dbprefix.'question_attributes';
        $CI->load->model('question_attributes_model');
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
            //$query=$connect->GetInsertSQL($tablename,$insertdata);
            $result=$CI->question_attributes_model->insertRecords($insertdata) or show_error($clang->gT("Error").": Failed to insert data<br />");
            $results['question_attributes']++;
        }
    }


    // Import defaultvalues --------------------------------------------------------------
    if(isset($xml->defaultvalues))
    {
        //$tablename=$dbprefix.'defaultvalues';
        $CI->load->model('defaultvalues_model');
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

            // now translate any links
            //$query=$connect->GetInsertSQL($tablename,$insertdata);
            $result=$CI->defaultvalues_model->insertRecords($insertdata) or show_error($clang->gT("Error").": Failed to insert data<br />");
            $results['defaultvalues']++;
        }
    }

    // Import conditions --------------------------------------------------------------
    if(isset($xml->conditions))
    {
        //$tablename=$dbprefix.'conditions';
        $CI->load->model('conditions_model');
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
                    $newcfieldname = '+'.$newsid . "X" . $aGIDReplacements[$oldcgid] . "X" . $insertdata["cqid"] .substr($oldqidanscode,strlen($oldqid));
                }
                else
                {
                    $newcfieldname = $newsid . "X" . $aGIDReplacements[$oldcgid] . "X" . $insertdata["cqid"] .substr($oldqidanscode,strlen($oldqid));
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

            // now translate any links
            //$query=$connect->GetInsertSQL($tablename,$insertdata);
            $result=$CI->conditions_model->insertRecords($insertdata) or show_error ($clang->gT("Error").": Failed to insert data<br />");
            $results['conditions']++;
        }
    }

    // Import assessments --------------------------------------------------------------
    if(isset($xml->assessments))
    {
        //$tablename=$dbprefix.'assessments';
        $CI->load->model('assessments_model');
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

            $insertdata['sid']=$newsid; // remap the survey id

            // now translate any links
            //$query=$connect->GetInsertSQL($tablename,$insertdata);
            $result=$CI->assessments_model->insertRecords($insertdata) or show_error($clang->gT("Error").": Failed to insert data<br />");
            $results['assessments']++;
        }
    }

    // Import quota --------------------------------------------------------------
    if(isset($xml->quota))
    {
        //$tablename=$dbprefix.'quota';
        $CI->load->model('quota_model');
        foreach ($xml->quota->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['sid']=$newsid; // remap the survey id
            $oldid=$insertdata['id'];
            unset($insertdata['id']);
            // now translate any links
            //$query=$connect->GetInsertSQL($tablename,$insertdata);
            $result=$CI->quota_model->insertRecords($insertdata) or show_error($clang->gT("Error").": Failed to insert data<br />");
            $aQuotaReplacements[$oldid] = $CI->db->insert_id(); //$connect->Insert_ID(db_table_name_nq('quota'),"id");
            $results['quota']++;
        }
    }

    // Import quota_members --------------------------------------------------------------
    if(isset($xml->quota_members))
    {
        //$tablename=$dbprefix.'quota_members';
        $CI->load->model('quota_members_model');
        foreach ($xml->quota_members->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['sid']=$newsid; // remap the survey id
            $insertdata['qid']=$aQIDReplacements[(int)$insertdata['qid']]; // remap the qid
            $insertdata['quota_id']=$aQuotaReplacements[(int)$insertdata['quota_id']]; // remap the qid
            unset($insertdata['id']);
            // now translate any links
            //$query=$connect->GetInsertSQL($tablename,$insertdata);
            $result=$CI->quota_members_model->insertRecords($insertdata) or show_error($clang->gT("Error").": Failed to insert data<br />");
            $results['quotamembers']++;
        }
    }

    // Import quota_languagesettings --------------------------------------------------------------
    if(isset($xml->quota_languagesettings))
    {
        //$tablename=$dbprefix.'quota_languagesettings';
        $CI->load->model('quota_languagesettings_model');
        foreach ($xml->quota_languagesettings->rows->row as $row)
        {
            $insertdata=array();
            foreach ($row as $key=>$value)
            {
                $insertdata[(string)$key]=(string)$value;
            }
            $insertdata['quotals_quota_id']=$aQuotaReplacements[(int)$insertdata['quotals_quota_id']]; // remap the qid
            unset($insertdata['quotals_id']);
            // now translate any links
            //$query=$connect->GetInsertSQL($tablename,$insertdata);
            $result=$CI->quota_languagesettings_model->insertRecords($insertdata) or show_error($clang->gT("Error").": Failed to insert data<br />");
            $results['quotals']++;
        }
    }
    
    
    
    // Set survey rights
    GiveAllSurveyPermissions($CI->session->userdata('loginID'),$newsid);
    if ($bTranslateInsertansTags)
    {
        
        $aOldNewFieldmap=aReverseTranslateFieldnames($oldsid,$newsid,$aGIDReplacements,$aQIDReplacements);
        TranslateInsertansTags($newsid,$oldsid,$aOldNewFieldmap);
        
    }

    return $results;
}

/**
* This function returns a new random sid if the existing one is taken,
* otherwise it returns the old one.
*
* @param mixed $oldsid
*/
function GetNewSurveyID($oldsid)
{
    //global $connect, $dbprefix;
    $CI =& get_instance();
    $CI->load->helper('database');
    //$clang = $CI->limesurvey_lang;
    $query = "SELECT sid FROM ".$CI->db->dbprefix."surveys WHERE sid=$oldsid";

    $res = db_execute_assoc($query);
    $isresult = $res->row_array(); //$connect->GetOne("SELECT sid FROM {$dbprefix}surveys WHERE sid=$oldsid");)

    //if (!is_null($isresult))
    if($res->num_rows > 0)
    {
        // Get new random ids until one is found that is not used
        do
        {
            $newsid = sRandomChars(5,'123456789');
            $query = "SELECT sid FROM ".$CI->db->dbprefix."surveys WHERE sid=$newsid";
            $res = db_execute_assoc($query);
            //$isresult = $res->row_array(); //$connect->GetOne("SELECT sid FROM {$dbprefix}surveys WHERE sid=$newsid");
        }
        while ($res->num_rows > 0);

        return $newsid;
    }
    else
    {
        return $oldsid;
    }
}

function GiveAllSurveyPermissions($iUserID, $iSurveyID)
{
     $aPermissions=aGetBaseSurveyPermissions();
     $aPermissionsToSet=array();
     foreach ($aPermissions as $sPermissionName=>$aPermissionDetails)
     {
         foreach ($aPermissionDetails as $sPermissionDetailKey=>$sPermissionDetailValue)
         {
           if (in_array($sPermissionDetailKey,array('create','read','update','delete','import','export')) && $sPermissionDetailValue==true)
           {
               $aPermissionsToSet[$sPermissionName][$sPermissionDetailKey]=1;
           }

         }
     }
     SetSurveyPermissions($iUserID, $iSurveyID, $aPermissionsToSet);
}

/**
* Set the survey permissions for a user. Beware that all survey permissions for the particual survey are removed before the new ones are written.
*
* @param int $iUserID The User ID
* @param int $iSurveyID The Survey ID
* @param array $aPermissions  Array with permissions in format <permissionname>=>array('create'=>0/1,'read'=>0/1,'update'=>0/1,'delete'=>0/1)
*/
function SetSurveyPermissions($iUserID, $iSurveyID, $aPermissions)
{
    //global $connect, $surveyid;
    $CI =& get_instance();
    $CI->load->helper('database');
    $iUserID=sanitize_int($iUserID);
    $sQuery = "delete from ".$CI->db->dbprefix."survey_permissions WHERE sid = {$iSurveyID} AND uid = {$iUserID}";
    db_execute_assoc($sQuery);
    $bResult=true;

    foreach($aPermissions as $sPermissionname=>$aPermissions)
    {
        if (!isset($aPermissions['create'])) {$aPermissions['create']=0;}
        if (!isset($aPermissions['read'])) {$aPermissions['read']=0;}
        if (!isset($aPermissions['update'])) {$aPermissions['update']=0;}
        if (!isset($aPermissions['delete'])) {$aPermissions['delete']=0;}
        if (!isset($aPermissions['import'])) {$aPermissions['import']=0;}
        if (!isset($aPermissions['export'])) {$aPermissions['export']=0;}
        if ($aPermissions['create']==1 || $aPermissions['read']==1 ||$aPermissions['update']==1 || $aPermissions['delete']==1  || $aPermissions['import']==1  || $aPermissions['export']==1)
        {
            $sQuery = "INSERT INTO ".$CI->db->dbprefix."survey_permissions (sid, uid, permission, create_p, read_p, update_p, delete_p, import_p, export_p)
                       VALUES ({$iSurveyID},{$iUserID},'{$sPermissionname}',{$aPermissions['create']},{$aPermissions['read']},{$aPermissions['update']},{$aPermissions['delete']},{$aPermissions['import']},{$aPermissions['export']})";
            $bResult=db_execute_assoc($sQuery);
        }
    }
    return $bResult;
}
