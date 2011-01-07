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

// A FILE TO IMPORT A DUMPED SURVEY FILE, AND CREATE A NEW SURVEY

$importlabeloutput = "<div class='header ui-widget-header'>".$clang->gT("Import Label Set")."</div>\n";

$sFullFilepath = $tempdir . DIRECTORY_SEPARATOR . $_FILES['the_file']['name'];
$aPathInfo = pathinfo($sFullFilepath);
$sExtension = $aPathInfo['extension'];

if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath))
{
    $importlabeloutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
    $importlabeloutput .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$tempdir)."<br /><br />\n";
    $importlabeloutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\"><br /><br />\n";
    return;
}

$importlabeloutput .= "<div class='messagebox ui-corner-all'><div class='successheader'>".$clang->gT("Success")."</div><br />\n";
$importlabeloutput .= $clang->gT("File upload succeeded.")."<br /><br />\n";
$importlabeloutput .= $clang->gT("Reading file..")."<br /><br />\n";
$options['checkforduplicates']='off';
if (isset($_POST['checkforduplicates']))
{
    $options['checkforduplicates']=$_POST['checkforduplicates'];
}

if (strtolower($sExtension)=='csv')
{
    $aImportResults=CSVImportLabelset($sFullFilepath, $options);
}
elseif (strtolower($sExtension)=='lsl')
{
    $aImportResults=XMLImportLabelsets($sFullFilepath, $options);
}
  else
{
    $importlabeloutput .= "<br />\n<div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
    $importlabeloutput .= "<strong><u>".$clang->gT("Label set import summary")."</u></strong><br />\n";
    $importlabeloutput .= $clang->gT("Uploaded label set file needs to have an .lsl extension.")."<br /><br />\n";
    $importlabeloutput .= "<input type='submit' value='".$clang->gT("Return to label set administration")."' onclick=\"window.open('$scriptname?action=labels', '_top')\" />\n";
    $importlabeloutput .= "</div><br />\n";
}
unlink($sFullFilepath);  

if (isset($aImportResults))
{
if (count($aImportResults['warnings'])>0)
{
    $importlabeloutput .= "<br />\n<div class='warningheader'>".$clang->gT("Warnings")."</div><ul>\n";
    foreach ($aImportResults['warnings'] as $warning)
    {
        $importlabeloutput .= '<li>'.$warning.'</li>';
    } 
    $importlabeloutput .= "</ul>\n";
}

$importlabeloutput .= "<br />\n<div class='successheader'>".$clang->gT("Success")."</div><br />\n";
$importlabeloutput .= "<strong><u>".$clang->gT("Label set import summary")."</u></strong><br />\n";
$importlabeloutput .= "<ul style=\"text-align:left;\">\n\t<li>".$clang->gT("Label sets").": {$aImportResults['labelsets']}</li>\n";
$importlabeloutput .= "\t<li>".$clang->gT("Labels").": {$aImportResults['labels']}</li></ul>\n";
$importlabeloutput .= "<p><strong>".$clang->gT("Import of label set(s) is completed.")."</strong><br /><br />\n";
$importlabeloutput .= "<input type='submit' value='".$clang->gT("Return to label set administration")."' onclick=\"window.open('$scriptname?action=labels', '_top')\" />\n";
$importlabeloutput .= "</div><br />\n";
}


$importlabeloutput .= "<br />\n<div class='successheader'>".$clang->gT("Success")."</div><br />\n";
$importlabeloutput .= "<strong><u>".$clang->gT("Label set import summary")."</u></strong><br />\n";
$importlabeloutput .= "<ul style=\"text-align:left;\">\n\t<li>".$clang->gT("Label sets").": {$aImportResults['labelsets']}</li>\n";
$importlabeloutput .= "\t<li>".$clang->gT("Labels").": {$aImportResults['labels']}</li></ul>\n";
$importlabeloutput .= "<p><strong>".$clang->gT("Import of label set(s) is completed.")."</strong><br /><br />\n";
$importlabeloutput .= "<input type='submit' value='".$clang->gT("Return to label set administration")."' onclick=\"window.open('$scriptname?action=labels', '_top')\" />\n";
$importlabeloutput .= "</div><br />\n";



// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY
function CSVImportLabelset($sFullFilepath, $options)
{
    global $dbprefix, $connect, $clang;
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
            $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
            $lsainsert = "insert INTO {$dbprefix}labelsets (".implode(',',array_keys($labelsetrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
            $lsiresult=$connect->Execute($lsainsert);
            $results['labelsets']++;            

            // Get the new insert id for the labels inside this labelset
            $newlid=$connect->Insert_ID("{$dbprefix}labelsets",'lid');

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
                        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                        $lainsert = "insert INTO {$dbprefix}labels (".implode(',',array_keys($labelrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
                        $liresult=$connect->Execute($lainsert);
                        $results['labels']++;
                    }
                }
            }

            //CHECK FOR DUPLICATE LABELSETS

            if (isset($_POST['checkforduplicates']))
            {
                $thisset="";
                $query2 = "SELECT code, title, sortorder, language, assessment_value
                           FROM ".db_table_name('labels')."
                           WHERE lid=".$newlid."
                           ORDER BY language, sortorder, code";
                $result2 = db_execute_num($query2) or safe_die("Died querying labelset $lid<br />$query2<br />".$connect->ErrorMsg());
                while($row2=$result2->FetchRow())
                {
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
                    $query = "DELETE FROM {$dbprefix}labels WHERE lid=$newlid";
                    $result=$connect->Execute($query) or safe_die("Couldn't delete labels<br />$query<br />".$connect->ErrorMsg());
                    $query = "DELETE FROM {$dbprefix}labelsets WHERE lid=$newlid";
                    $result=$connect->Execute($query) or safe_die("Couldn't delete labelset<br />$query<br />".$connect->ErrorMsg());
                    $newlid=$lsmatch;
                    $results['warnings'][]=$clang->gT("Label set was not imported because the same label set already exists.")." ".sprintf($clang->gT("Existing LID: %s"),$newlid);
                     
                }
                //END CHECK FOR DUPLICATES
            }
        }
    }

    return $results;
}


function XMLImportLabelsets($sFullFilepath, $options)
{
    global $connect, $dbprefix, $clang;
    
    $xml = simplexml_load_file($sFullFilepath);    
    if ($xml->LimeSurveyDocType!='Label set') safe_die('This is not a valid LimeSurvey label set structure XML file.');
    $dbversion = (int) $xml->DBVersion;
    $csarray=buildLabelSetCheckSumArray();
    $aLSIDReplacements=array();     
    $results['labelsets']=0;
    $results['labels']=0;
    $results['warnings']=array();
   
                           
    // Import labels table ===================================================================================

    $tablename=$dbprefix.'labelsets';
    foreach ($xml->labelsets->rows->row as $row)
    {
       $insertdata=array(); 
        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }
        $oldlsid=$insertdata['lid'];
        unset($insertdata['lid']); // save the old qid

        // Insert the new question    
        $query=$connect->GetInsertSQL($tablename,$insertdata); 
        $result = $connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />{$query}<br />\n".$connect->ErrorMsg());
        $results['labelsets']++;

        $newlsid=$connect->Insert_ID($tablename,"lid"); // save this for later
        $aLSIDReplacements[$oldlsid]=$newlsid; // add old and new lsid to the mapping array
    }
                           
                                                                                      
    // Import labels table ===================================================================================

    $tablename=$dbprefix.'labels';
    foreach ($xml->labels->rows->row as $row)
    {
       $insertdata=array(); 
        foreach ($row as $key=>$value)
        {
            $insertdata[(string)$key]=(string)$value;
        }
        $insertdata['lid']=$aLSIDReplacements[$insertdata['lid']];
        $query=$connect->GetInsertSQL($tablename,$insertdata); 
        $result = $connect->Execute($query) or safe_die ($clang->gT("Error").": Failed to insert data<br />{$query}<br />\n".$connect->ErrorMsg());
        $results['labels']++;
    }
    
    //CHECK FOR DUPLICATE LABELSETS

    if (isset($_POST['checkforduplicates']))
    {
        foreach (array_values($aLSIDReplacements) as $newlid)
        {
            $thisset="";
            $query2 = "SELECT code, title, sortorder, language, assessment_value
                       FROM ".db_table_name('labels')."
                       WHERE lid=".$newlid."
                       ORDER BY language, sortorder, code";
            $result2 = db_execute_num($query2) or safe_die("Died querying labelset $lid<br />$query2<br />".$connect->ErrorMsg());
            while($row2=$result2->FetchRow())
            {
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
                $query = "DELETE FROM {$dbprefix}labels WHERE lid=$newlid";
                $result=$connect->Execute($query) or safe_die("Couldn't delete labels<br />$query<br />".$connect->ErrorMsg());
                $results['labels']=$results['labels']-$connect->Affected_Rows();
                $query = "DELETE FROM {$dbprefix}labelsets WHERE lid=$newlid";
                $result=$connect->Execute($query) or safe_die("Couldn't delete labelset<br />$query<br />".$connect->ErrorMsg());
                $results['labelsets']--;
                $newlid=$lsmatch;
                $results['warnings'][]=$clang->gT("Label set was not imported because the same label set already exists.")." ".sprintf($clang->gT("Existing LID: %s"),$newlid);
                 
            }
        }
        //END CHECK FOR DUPLICATES
    }    
    return $results;
}

// Closing PHP tag intentionall left out