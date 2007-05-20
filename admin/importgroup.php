<?php
/*
#############################################################
# >>> LimeSurvey  										#
#############################################################
# > Author:  Jason Cleeland									#
# > E-mail:  jason@cleeland.org								#
# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
# >          CARLTON SOUTH 3053, AUSTRALIA
# > Date: 	 20 February 2003								#
#															#
# This set of scripts allows you to develop, publish and	#
# perform data-entry on surveys.							#
#############################################################
#															#
#	Copyright (C) 2003  Jason Cleeland						#
#															#
# This program is free software; you can redistribute 		#
# it and/or modify it under the terms of the GNU General 	#
# Public License Version 2 as published by the Free         #
# Software Foundation.										#
#															#
#															#
# This program is distributed in the hope that it will be 	#
# useful, but WITHOUT ANY WARRANTY; without even the 		#
# implied warranty of MERCHANTABILITY or FITNESS FOR A 		#
# PARTICULAR PURPOSE.  See the GNU General Public License 	#
# for more details.											#
#															#
# You should have received a copy of the GNU General 		#
# Public License along with this program; if not, write to 	#
# the Free Software Foundation, Inc., 59 Temple Place - 	#
# Suite 330, Boston, MA  02111-1307, USA.					#
#############################################################
*/
//Ensure script is not run directly, avoid path disclosure
if (empty($homedir)) {die ("Cannot run this script directly");}
include_once("login_check.php");


// A FILE TO IMPORT A DUMPED SURVEY FILE, AND CREATE A NEW SURVEY

$importgroup = "<br /><table width='100%' align='center'><tr><td>\n";
$importgroup .= "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
$importgroup .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>".$clang->gT("Import Group")."</strong></font></td></tr>\n";
$importgroup .= "\t<tr bgcolor='#CCCCCC'><td align='center'>\n";

$the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];

if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
{
	$importgroup .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
	$importgroup .= $clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your admin folder.")."<br /><br />\n";
	$importgroup .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n";
	$importgroup .= "</td></tr></table>\n";
	return;
}

// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY

$importgroup .= "<strong><font color='green'>".$clang->gT("Success")."</font></strong><br />\n";
$importgroup .= $clang->gT("File upload succeeded.")."<br /><br />\n";
$importgroup .= $clang->gT("Reading file...")."<br />\n";
$handle = fopen($the_full_file_path, "r");
while (!feof($handle))
{
	$buffer = fgets($handle, 10240); //To allow for very long survey welcomes (up to 10k)
	$bigarray[] = $buffer;
}
fclose($handle);

if (substr($bigarray[0], 0, 24) != "# LimeSurvey Group Dump")
{
	$importgroup .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
	$importgroup .= $clang->gT("This file is not a LimeSurvey group file. Import failed.")."<br /><br />\n";
	$importgroup .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n";
	$importgroup .= "</td></tr></table>\n";
	unlink($the_full_file_path);
	return;
}

for ($i=0; $i<9; $i++)
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

//LAST LOT (now question_attributes)
if (!isset($noconditions) || $noconditions != "Y")
{
	$stoppoint = count($bigarray)-1;
	for ($i=0; $i<=$stoppoint+1; $i++)
	{
		if ($i<$stoppoint-1) {$question_attributesarray[] = $bigarray[$i];}
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
    $countanswers = 0;
    }

$countconditions = 0;
$countlabelsets = 0;
$countlabels = 0;
$countquestion_attributes = 0;

$newsid = $_POST["sid"];

//DO ANY LABELSETS FIRST, SO WE CAN KNOW WHAT THEY'RE NEW LID IS FOR THE QUESTIONS
if (isset($labelsetsarray) && $labelsetsarray) {
	$csarray=buildLabelsetCSArray();
    $fieldorders=convertCSVRowToArray($labelsetsarray[0],',','"');
    unset($labelsetsarray[0]);
	foreach ($labelsetsarray as $lsa) {
        $fieldcontents=convertCSVRowToArray($lsa,',','"');
        $labelsetrowdata=array_combine($fieldorders,$fieldcontents);
		$oldcid=$labelsetrowdata["cid"];
		$oldqid=$labelsetrowdata["qid"];
		$oldlid=$labelsetrowdata["lid"];
		unset($labelsetrowdata["lid"]);
        $newvalues=array_values($labelsetrowdata);
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
        $lsainsert = "insert INTO {$dbprefix}labelsets (".implode(',',array_keys($labelsetrowdata)).") VALUES (".implode(',',$newvalues).")"; //handle db prefix
		$lsiresult=$connect->Execute($lsainsert);
		$newlid=$connect->Insert_ID();
        $countlabelsets++; 
         
		if ($labelsarray) {
            $lfieldorders=convertCSVRowToArray($labelsarray[0],',','"');
            unset($labelsarray[0]);
			foreach ($labelsarray as $la) {
				//GET ORDER OF FIELDS
                $lfieldcontents=convertCSVRowToArray($la,',','"');
         		$labelrowdata=array_combine($lfieldorders,$lfieldcontents);
				$labellid=$labelrowdata['lid'];
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
		$query2 = "SELECT code, title, sortorder
				   FROM {$dbprefix}labels
				   WHERE lid=".$newlid."
				   ORDER BY sortorder, code";
		$result2 = db_execute_num($query2) or die("Died querying labelset $lid<br />$query2<br />".$connect->ErrorMsg());
		while($row2=$result2->FetchRow())
		{
			$thisset .= implode('.',$row2);
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
			$countlabelsets--;
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


// DO GROUPS, QUESTIONS FOR GROUPS, THEN ANSWERS FOR QUESTIONS IN A NESTED FORMAT!
if (isset($grouparray) && $grouparray) {
    $gafieldorders=convertCSVRowToArray($grouparray[0],',','"');
    unset($grouparray[0]);
	foreach ($grouparray as $ga) {
		//GET ORDER OF FIELDS
        $gacfieldcontents=convertCSVRowToArray($ga,',','"');
		$grouprowdata=array_combine($gafieldorders,$gacfieldcontents);
		
		
		$surveylanguages=GetAdditionalLanguagesFromSurveyID($surveyid);
		$surveylanguages[]=GetBaseLanguageFromSurveyID($surveyid);
		if (!in_array($grouprowdata['language'],$surveylanguages)) 
		{
            $skippedlanguages[]=$grouprowdata['language'];
            continue ;
             
        }
		
		
		
		$oldgid=$grouprowdata['gid'];
		$oldsid=$grouprowdata['sid'];
        unset($grouprowdata['gid']);
        $grouprowdata['sid']=$newsid;
   
        // find tou the maximum group order and use this grouporder+1 to assign it to the new group 
        $qmaxgo = "select max(group_order) as maxgo from ".db_table_name('groups')." where sid=$newsid";
		$gres = db_execute_assoc($qmaxgo) or die ("<strong>".$clang->gT("Error")."</strong> Failed to find out maximum group order value<br />\n$qmaxqo<br />\n".$connect->ErrorMsg()."</body>\n</html>");
        $grow=$gres->FetchRow();
		$grouprowdata["group_order"]= $grow['maxgo']+1; 

        // Everything set - now insert it
        $newvalues=array_values($grouprowdata);
        $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
        $ginsert = "insert INTO {$dbprefix}groups (".implode(',',array_keys($grouprowdata)).") VALUES (".implode(',',$newvalues).")"; 
		$gres = $connect->Execute($ginsert) or die("<strong>".$clang->gT("Error")."</strong> Failed to insert group<br />\n$ginsert<br />\n".$connect->ErrorMsg()."</body>\n</html>");
        $countgroups++;
        
		//GET NEW GID
		$newgid=$connect->Insert_ID();
		
		//NOW DO NESTED QUESTIONS FOR THIS GID
		if (isset($questionarray) && $questionarray) {
            $currentqid='';
			foreach ($questionarray as $qa) {
                $qacfieldcontents=convertCSVRowToArray($qa,',','"');
        		$questionrowdata=array_combine($questionfieldnames,$qacfieldcontents);
                if ($currentqid=='' || ($currentqid!=$questionrowdata['qid'])) {$currentqid=$questionrowdata['qid'];$newquestion=true;}
                  else 
                    if ($currentqid==$questionrowdata['qid']) {$newquestion=false;}   
                     		
				$thisgid=$questionrowdata['gid'];
				if ($thisgid == $oldgid) {
					$qid = $questionrowdata['qid'];
					// Remove qid field
					if ($newquestion) {unset($questionrowdata['qid']);}
					   else {$questionrowdata['qid']=$newqid;}
					   
					$questionrowdata["sid"] = $newsid;
					$questionrowdata["gid"] = $newgid;
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
					$other = $questionrowdata["other"]; //Get 'other' field value
                    $oldlid = $questionrowdata['lid'];
                     $newvalues=array_values($questionrowdata);
                    $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                    $qinsert = "insert INTO {$dbprefix}questions (".implode(',',array_keys($questionrowdata)).") VALUES (".implode(',',$newvalues).")"; 
					$qres = $connect->Execute($qinsert) or die ("<strong>".$clang->gT("Error")."</strong> Failed to insert question<br />\n$qinsert<br />\n".$connect->ErrorMsg()."</body>\n</html>");
                    if ($newquestion) {$newqid=$connect->Insert_ID();}
                    $countquestions++;
					$newrank=0;
					$substitutions[]=array($oldsid, $oldgid, $oldqid, $newsid, $newgid, $newqid);
					//NOW DO NESTED ANSWERS FOR THIS QID
					if ($answerarray) {
						foreach ($answerarray as $aa) {
                            $aacfieldcontents=convertCSVRowToArray($aa,',','"');
                    		$answerrowdata=array_combine($answerfieldnames,$aacfieldcontents);
							$code=$answerrowdata["code"];
							$thisqid=$answerrowdata["qid"];
							if ($thisqid == $qid) 
                            {
								$answerrowdata["qid"]=$newqid;
                                $newvalues=array_values($answerrowdata);
                                $newvalues=array_map(array(&$connect, "qstr"),$newvalues); // quote everything accordingly
                                $ainsert = "insert INTO {$dbprefix}answers (".implode(',',array_keys($answerrowdata)).") VALUES (".implode(',',$newvalues).")"; 
								$ares = $connect->Execute($ainsert) or die ("<strong>".$clang->gT("Error")."</strong> Failed to insert answer<br />\n$ainsert<br />\n".$connect->ErrorMsg()."</body>\n</html>");
								$countanswers++;
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
//We've built two arrays along the way - one containing the old SID, GID and QIDs - and their NEW equivalents
//and one containing the old 'extended fieldname' and its new equivalent.  These are needed to import conditions.
if (isset($question_attributesarray) && $question_attributesarray) {//ONLY DO THIS IF THERE ARE QUESTION_ATTRIBUES
    $fieldorders  =convertCSVRowToArray($question_attributesarray[0],',','"');
    unset($question_attributesarray[0]);
	foreach ($question_attributesarray as $qar) {
        $fieldcontents=convertCSVRowToArray($qar,',','"');
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
        $countquestion_attributes++;		
	}
}


if (isset($conditionsarray) && $conditionsarray) {//ONLY DO THIS IF THERE ARE CONDITIONS!
    $fieldorders=convertCSVRowToArray($conditionsarray[0],',','"');
    unset($conditionsarray[0]);
	foreach ($conditionsarray as $car) {
        $fieldcontents=convertCSVRowToArray($car,',','"');
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
			$importgroup .= "<font size=1>".$clang->gT("Condition for $oldqid skipped ($oldcqid does not exist)")."</font><br />";
		}
		unset($newcqid);
	}
}


if (isset($skippedlanguages))
{
    $importgroup.='<font class="successtitle">'.$clang->gT("Import partially successful.")."</font><br /><br />";
    $importgroup.=$clang->gT("The following languages in this group were not imported since the survey does not contain such a language: ")."<br />";
    foreach  ($skippedlanguages as $sl)
    {
    $importgroup.= getLanguageNameFromCode($grouprowdata['language'], false).'<br />';
    }
    $importgroup.='<br />';
}
else
{
    $importgroup .= "<br />\n<strong><font class='successtitle'>".$clang->gT("Success")."</font></strong><br />\n";
}
$importgroup .="<strong><u>".$clang->gT("Group Import Summary")."</u></strong><br />\n"
."<ul>\n\t<li>".$clang->gT("Groups").": ";
if (isset($countgroups)) {$importgroup .= $countgroups;}
$importgroup .= "</li>\n"
    ."\t<li>".$clang->gT("Questions").": ";
if (isset($countquestions)) {$importgroup .= $countquestions;}
$importgroup .= "</li>\n"
    ."\t<li>".$clang->gT("Answers").": ";
if (isset($countanswers)) {$importgroup .= $countanswers;}
$importgroup .= "</li>\n"
    ."\t<li>".$clang->gT("Conditions").": ";
if (isset($countconditions)) {$importgroup .= $countconditions;}
$importgroup .= "</li>\n"
."\t<li>".$clang->gT("Label Set").": ";
if (isset($countlabelsets)) {$importgroup .= $countlabelsets;}
$importgroup .= " (".$clang->gT("Labels").": ";
if (isset($countlabels)) {$importgroup .= $countlabels;}
$importgroup .= ")</li>\n";
$importgroup .= "\t<li>".$clang->gT("Question Attributes:");
$importgroup .= " $countquestion_attributes";
$importgroup .= ")</li>\n</ul>\n";
$importgroup .= "<strong>".$clang->gT("Import of group is completed.")."</strong><br />&nbsp;\n"
."</td></tr></table><br />&nbsp;\n";


unlink($the_full_file_path);

?>