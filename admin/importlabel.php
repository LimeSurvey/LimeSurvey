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

$importlabeloutput = "<br />\n";
$importlabeloutput .= "<table class='alertbox'>\n";
$importlabeloutput .= "\t<tr><td colspan='2' height='4'><strong>".$clang->gT("Import Label Set")."</strong></td></tr>\n";
$importlabeloutput .= "\t<tr><td align='center'>\n";

$the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];

if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
{
	$importlabeloutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
    $importlabeloutput .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$tempdir)."<br /><br />\n";
	$importlabeloutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n";
	$importlabeloutput .= "</td></tr></table><br />&nbsp;\n";
	return;
}

// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY

$csarray=buildLabelSetCheckSumArray();
//$csarray is now a keyed array with the Checksum of each of the label sets, and the lid as the key

$importlabeloutput .= "<strong><font class='successtitle'>".$clang->gT("Success")."</font></strong><br />\n";
$importlabeloutput .= $clang->gT("File upload succeeded.")."<br /><br />\n";
$importlabeloutput .= $clang->gT("Reading file..")."<br />\n";
$handle = fopen($the_full_file_path, "r");
while (!feof($handle))
{
	$buffer = fgets($handle, 10240); //To allow for very long survey welcomes (up to 10k)
	$bigarray[] = $buffer;
}
fclose($handle);
if (substr($bigarray[0], 0, 27) != "# LimeSurvey Label Set Dump" && substr($bigarray[0], 0, 28) != "# PHPSurveyor Label Set Dump")
{
	$importlabeloutput .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
	$importlabeloutput .= $clang->gT("This file is not a LimeSurvey label set file. Import failed.")."<br /><br />\n";
	$importlabeloutput .= "<input type='submit' value='".$clang->gT("Return to Labels Admin")."' onclick=\"window.open('$scriptname?action=labels', '_top')\">\n";
	$importlabeloutput .= "</td></tr></table>\n";
	$importlabeloutput .= "</body>\n</html>\n";
    unlink($the_full_file_path);
	return;
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
        
        $countlang=count(explode(' ',trim($labelsetrowdata['languages'])));
        $newvalues=array_values($labelsetrowdata);
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
        $lsainsert = "insert INTO {$dbprefix}labelsets (".implode(',',array_keys($labelsetrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
		$lsiresult=$connect->Execute($lsainsert);
		
		// Get the new insert id for the labels inside this labelset
		$newlid=$connect->Insert_ID("{$dbprefix}labelsets",'lid');

		if ($labelsarray) {
		    $count=0;
            $lfieldorders  =convertCSVRowToArray($labelsarray[0],',','"');
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
				}
			}
		}

		//CHECK FOR DUPLICATE LABELSETS
		$thisset="";
		$query2 = "SELECT code, title, sortorder, language, assessment_value 
                   FROM ".db_table_name('labels')."
                   WHERE lid=".$newlid."
                   ORDER BY sortorder, code";
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
	        $importlabeloutput.="<p><i><font color='red'>".$clang->gT("There was a duplicate labelset, so this set was not imported. The duplicate will be used instead.")."</font></i>\n";
            $importlabeloutput .= "<strong>Existing LID:</strong> $newlid</p><br />\n";
			
		}
		else
		{
        $importlabeloutput .= "<strong>LID:</strong> $newlid<br />\n";
        $importlabeloutput .= "<br />\n<strong><font class='successtitle'>".$clang->gT("Success")."</font></strong><br />\n";
        $importlabeloutput .= "<strong><u>".$clang->gT("Label Set Import Summary")."</u></strong><br />\n";
        $importlabeloutput .= "\t<li>".$clang->gT("Label Sets").": $countlabelsets</li>\n";
        $importlabeloutput .= "\t<li>".$clang->gT("Labels").": $countlabels</li>\n";
        $importlabeloutput .= "\t<li>".$clang->gT("Languages").": $countlang</li></ul><br />\n";
		}
		//END CHECK FOR DUPLICATES
	}
}


$importlabeloutput .= "<strong>".$clang->gT("Import of Label Set is completed.")."</strong><br />\n";
$importlabeloutput .= "<input type='submit' value='".$clang->gT("Return to Labels Admin")."' onclick=\"window.open('$scriptname?action=labels&amp;lid=$newlid', '_top')\">\n";

$importlabeloutput .= "</td></tr></table>\n";
unlink($the_full_file_path);



?>
