<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
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
	# Public License as published by the Free Software 			#
	# Foundation; either version 2 of the License, or (at your 	#
	# option) any later version.								#
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
// A FILE TO IMPORT A DUMPED SURVEY FILE, AND CREATE A NEW SURVEY

echo "<br />\n";
echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._IMPORTSURVEY."</b></td></tr>\n";
echo "\t<tr height='22' bgcolor='#CCCCCC'><td align='center'>$setfont\n";

$the_full_file_path = $homedir . "/" . $_FILES['the_file']['name'];

if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
	{
	echo "<b><font color='red'>"._ERROR."</font></b><br />\n";
	echo _IS_FAILUPLOAD."<br /><br />\n";
	echo "<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\">\n";
	echo "</td></tr></table>\n";
	echo "</body>\n</html>\n";
	exit;
	}

// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY

echo "<b><font color='green'>"._SUCCESS."</font></b><br />\n";
echo _IS_OKUPLOAD."<br /><br />\n";
echo _IS_READFILE."<br />\n";
$handle = fopen($the_full_file_path, "r");
while (!feof($handle))
	{
	//$buffer = fgets($handle, 1024); //Length parameter is required for PHP versions < 4.2.0
	$buffer = fgets($handle, 10240); //To allow for very long survey welcomes (up to 10k)
	$bigarray[] = $buffer;
	}
fclose($handle);

if (substr($bigarray[1], 0, 22) != "# SURVEYOR SURVEY DUMP")
	{
	echo "<b><font color='red'>"._ERROR."</font></b><br />\n";
	echo _IS_WRONGFILE."<br /><br />\n";
	echo "<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\">\n";
	echo "</td></tr></table>\n";
	echo "</body>\n</html>\n";
	unlink($the_full_file_path);
	exit;
	}

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
	if ($i<$stoppoint-2) {$tablearray[] = $bigarray[$i];}
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
	{ //There is no labelsets information, so presumably this is a pre-0.98rc3 survey.
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
if ($noconditions != "Y")
	{
	$stoppoint = count($bigarray)-1;
	for ($i=0; $i<=$stoppoint+1; $i++)
		{
		if ($i<$stoppoint-1) {$labelsarray[] = $bigarray[$i];}
		unset($bigarray[$i]);
		}
	}




$countsurveys = count($tablearray);
$countgroups = count($grouparray);
$countquestions = count($questionarray);
$countanswers = count($answerarray);
$countconditions = count($conditionsarray);
$countlabelsets = count($labelsetsarray);
$countlabels = count($labelsarray);

// CREATE SURVEY
//GET ORDER OF FIELDS
$sstart=strpos($tablearray[0], "(`")+2;
$slen=strpos($tablearray[0], "`)")-$sstart;
$sfieldorder=substr($tablearray[0], $sstart, $slen);
$sfieldorders=explode("`, `", $sfieldorder);
//GET CONTENTS OF FIELDS
$sfstart=strpos($tablearray[0], "('")+2;
$sflen=strpos($tablearray[0], "')")-$sfstart;
$sffieldcontent=substr($tablearray[0], $sfstart, $sflen);
$sffieldcontents=explode("', '", $sffieldcontent);

$sidpos=array_search("sid", $sfieldorders);
$sid=$sffieldcontents[$sidpos];

if (!$sid) 
	{
	echo "<br /><b><font color='red'>"._ERROR."</b></font><br />\n";
	echo _IS_IMPFAILED."<br />\n";
	echo _IS_FILEFAILS."<br />\n"; //NOT A PHPSURVEYOR EXPORT FILE
	echo "<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\">\n";
	echo "</td></tr></table>\n";
	echo "</body>\n</html>\n";
	unlink($the_full_file_path); //Delete the uploaded file
	exit;
	}
$insert = str_replace("('$sid'", "(''", $tablearray[0]);
$insert = str_replace("INTO surveys", "INTO {$dbprefix}surveys", $insert); //handle db prefix
//$insert = substr($insert, 0, -1);
$iresult = mysql_query($insert) or die("<br />"._IS_IMPFAILED."<br />\n<font size='1'>[$insert]</font><hr>$tablearray[0]<br /><br />\n" . mysql_error() . "</body>\n</html>");

$oldsid=$sid;

//GET NEW SID
$sidquery = "SELECT sid FROM {$dbprefix}surveys ORDER BY sid DESC LIMIT 1";
$sidres = mysql_query($sidquery);
while ($srow = mysql_fetch_row($sidres)) {$newsid = $srow[0];}

//DO ANY LABELSETS FIRST, SO WE CAN KNOW WHAT THEY'RE NEW LID IS FOR THE QUESTIONS
if ($labelsetsarray)
	{
	foreach ($labelsetsarray as $lsa)
		{
		//GET ORDER OF FIELDS
		$fostart=strpos($lsa, "(`")+2;
		$folen=strpos($lsa, "`)")-$fostart;
		$fieldorder=substr($lsa, $fostart, $folen);
		$fieldorders=explode("`, `", $fieldorder);
		//GET CONTENTS OF FIELDS
		$fcstart=strpos($lsa, "('")+2;
		$fclen=strpos($lsa, "')")-$fcstart;
		$fieldcontent=substr($lsa, $fcstart, $fclen);
		$fieldcontents=explode("', '", $fieldcontent);

		$oldlidpos=array_search("lid", $fieldorders);
		$oldlid=$fieldcontents[$oldlidpos];
		
		$lsainsert = str_replace("VALUES ('$oldlid", "VALUES ('", $lsa);
		$lsainsert = str_replace("INTO labelsets", "INTO {$dbprefix}labelsets", $lsainsert); //db prefix handler
		$lsiresult=mysql_query($lsainsert);
		
		//GET NEW LID
		$nlidquery="SELECT lid FROM {$dbprefix}labelsets ORDER BY lid DESC LIMIT 1";
		$nlidresult=mysql_query($nlidquery);
		while ($nlidrow=mysql_fetch_array($nlidresult)) {$newlid=$nlidrow['lid'];}
		$labelreplacements[]=array($oldlid, $newlid);
		if ($labelsarray)
			{
			foreach ($labelsarray as $la)
				{
				//GET ORDER OF FIELDS
				$lfostart=strpos($la, "(`")+2;
				$lfolen=strpos($la, "`)")-$lfostart;
				$lfieldorder=substr($la, $lfostart, $lfolen);
				$lfieldorders=explode("`, `", $lfieldorder);
				//GET CONTENTS OF FIELDS
				$lfcstart=strpos($la, "('")+2;
				$lfclen=strpos($la, "')")-$lfcstart;
				$lfieldcontent=substr($la, $lfcstart, $lfclen);
				$lfieldcontents=explode("', '", $lfieldcontent);
	
				$labellidpos=array_search("lid", $lfieldorders);
				$labellid=$lfieldcontents[$labellidpos];
				if ($labellid == $oldlid)
					{
					$lainsert = str_replace("VALUES ('$labellid", "VALUES ('$newlid", $la);
					$lainsert = str_replace ("INTO labels", "INTO {$dbprefix}labels", $lainsert);
					$liresult=mysql_query($lainsert);
					}
				}
			}
		}
	}

// DO GROUPS, QUESTIONS FOR GROUPS, THEN ANSWERS FOR QUESTIONS IN A NESTED FORMAT!
if ($grouparray)
	{
	foreach ($grouparray as $ga)
		{
		//GET ORDER OF FIELDS
		$gastart=strpos($ga, "(`")+2;
		$galen=strpos($ga, "`)")-$gastart;
		$gafieldorder=substr($ga, $gastart, $galen);
		$gafieldorders=explode("`, `", $gafieldorder);
		//GET CONTENTS OF FIELDS
		$gacstart=strpos($ga, "('")+2;
		$gaclen=strpos($ga, "')")-$gacstart;
		$gacfieldcontent=substr($ga, $gacstart, $gaclen);
		$gacfieldcontents=explode("', '", $gacfieldcontent);

		$gidpos=array_search("gid", $gafieldorders);
		$gid=$gacfieldcontents[$gidpos];
		
		//$gid = substr($ga, strpos($ga, "('")+2, (strpos($ga, "',")-(strpos($ga, "('")+2)));
		$ginsert = str_replace("('$gid', '$sid',", "('', '$newsid',", $ga);
		$ginsert = str_replace("INTO groups", "INTO {$dbprefix}groups", $ginsert);
		$oldgid=$gid;
		//$ginsert = substr($ginsert, 0, -1);
		$gres = mysql_query($ginsert);
		//GET NEW GID
		$gidquery = "SELECT gid FROM {$dbprefix}groups ORDER BY gid DESC LIMIT 1";
		$gidres = mysql_query($gidquery);
		while ($grow = mysql_fetch_row($gidres)) {$newgid = $grow[0];}
		//NOW DO NESTED QUESTIONS FOR THIS GID
		if ($questionarray)
			{
			foreach ($questionarray as $qa)
				{
				$sidpos = ", '$sid'";
				$start = strpos($qa, "$sidpos")+2+strlen($sid)+5;
				$end = strpos($qa, "'", $start)-$start;
				if (substr($qa, $start, $end) == $gid)
					{
					$qid = substr($qa, strpos($qa, "('")+2, (strpos($qa, "',")-(strpos($qa, "('")+2)));
					$oldqid=$qid;
					$qinsert = str_replace("('$qid', '$sid', '$gid',", "('$newsid', '$newgid',", $qa);
					$qinsert = str_replace("(`qid`, ", "(", $qinsert);
					$qinsert = str_replace("INTO questions", "INTO {$dbprefix}questions", $qinsert);
					//$qinsert = substr(trim($qinsert), 0, -1);
					//FIELDNAME ARRAY GENERATION
					$typepos = "('$qid', '$sid', '$gid', '";
					$type = substr($qa, strpos($qa, $typepos)+strlen($typepos), 1);
					$otherpos = "')";
					$other = substr($qa, strpos($qa, $otherpos)-6, 1);

					$qres = mysql_query($qinsert) or die ("<b>"._ERROR."</b> Failed to insert question<br />\n$qinsert<br />\n".mysql_error()."</body>\n</html>");
					//GET NEW QID
					$qidquery = "SELECT qid, lid FROM {$dbprefix}questions ORDER BY qid DESC LIMIT 1";
					$qidres = mysql_query($qidquery);
					while ($qrow = mysql_fetch_array($qidres)) {$newqid = $qrow['qid']; $oldlid=$qrow['lid'];}
					//IF this is a flexible label array, update the lid entry
					if ($type == "F")
						{
						foreach ($labelreplacements as $lrp)
							{
							if ($lrp[0] == $oldlid)
								{
								$lrupdate="UPDATE {$dbprefix}questions SET lid='{$lrp[1]}' WHERE qid=$newqid";
								$lrresult=mysql_query($lrupdate);
								}
							}
						}

					$newrank=0;
					//NOW DO NESTED ANSWERS FOR THIS QID
					if ($answerarray)
						{
						foreach ($answerarray as $aa)
							{
							$qidpos = "('";
							$astart = strpos($aa, "$qidpos")+2;
							$aend = strpos($aa, "'", $astart)-$astart;
							$codepos1=strpos($aa, "', '")+4;
							$codepos2=strpos($aa, "', '", strpos($aa, "', '")+1);
							$codelength=$codepos2-$codepos1;
							$code = substr($aa, $codepos1, $codelength);
							if (substr($aa, $astart, $aend) == ($qid))
								{
								$ainsert = str_replace("('$qid", "('$newqid", $aa);
								$ainsert = str_replace("INTO answers", "INTO {$dbprefix}answers", $ainsert);
								//$ainsert = substr(trim($ainsert), 0, -1);
								$ares = mysql_query($ainsert) or die ("<b>"._ERROR."</b> Failed to insert answer<br />\n$ainsert<br />\n".mysql_error()."</body>\n</html>");
								if ($type == "A" || $type == "B" || $type == "C" || $type == "M" || $type == "P" || $type == "F")
									{
									$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid.$code, $newsid."X".$newgid."X".$newqid.$code);
									if ($type == "P")
										{
										$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid.$code."comment", $newsid."X".$newgid."X".$newqid.$code."comment");
										}
									}
								elseif ($type == "R")
									{
									$newrank++;
									}
								}			
							}
						if (($type == "A" || $type == "B" || $type == "C" || $type == "M" || $type == "P") && ($other == "Y"))
							{
							$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid."other", $newsid."X".$newgid."X".$newqid."other");
							if ($type == "P")
								{
								$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid."othercomment", $newsid."X".$newgid."X".$newqid."othercomment");
								}
							}
						if ($type == "R" && $newrank >0)
							{
							for ($i=1; $i<=$newrank; $i++)
								{
								$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid.$i, $newsid."X".$newgid."X".$newqid.$i);
								}
							}
						if ($type != "A" && $type != "B" && $type != "C" && $type != "R" && $type != "M" && $type != "P")
							{
							$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid, $newsid."X".$newgid."X".$newqid);
							if ($type == "O")
								{
								$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid."comment", $newsid."X".$newgid."X".$newqid."comment");
								}
							}
						$substitutions[]=array($oldsid, $oldgid, $oldqid, $newsid, $newgid, $newqid);
						}
					else
						{
						$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid, $newsid."X".$newgid."X".$newqid);
						$substitutions[]=array($oldsid, $oldgid, $oldqid, $newsid, $newgid, $newqid);
						}
					}
				}
			}
		}
	}
//We've built two arrays along the way - one containing the old SID, GID and QIDs - and their NEW equivalents
//and one containing the old 'extended fieldname' and its new equivalent.  These are needed to import conditions.

if ($conditionsarray) //ONLY DO THIS IF THERE ARE CONDITIONS!
	{
	foreach ($conditionsarray as $car)
		{
		//GET ORDER OF FIELDS
		$fostart=strpos($car, "(`")+2;
		$folen=strpos($car, "`)")-$fostart;
		$fieldorder=substr($car, $fostart, $folen);
		$fieldorders=explode("`, `", $fieldorder);
		//GET CONTENTS OF FIELDS
		$fcstart=strpos($car, "('")+2;
		$fclen=strpos($car, "')")-$fcstart;
		$fieldcontent=substr($car, $fcstart, $fclen);
		$fieldcontents=explode("', '", $fieldcontent);

		$oldcidpos=array_search("cid", $fieldorders);
		$oldcid=$fieldcontents[$oldcidpos];
		$oldqidpos=array_search("qid", $fieldorders);
		$oldqid=$fieldcontents[$oldqidpos];
		$oldcfieldnamepos=array_search("cfieldname", $fieldorders);
		$oldcfieldname=$fieldcontents[$oldcfieldnamepos];
		$oldcqidpos=array_search("cqid", $fieldorders);
		$oldcqid=$fieldcontents[$oldcqidpos];
		foreach ($substitutions as $subs)
			{
			if ($oldqid==$subs[2])	{$newqid=$subs[5];}
			if ($oldcqid==$subs[2])	{$newcqid=$subs[5];}
			}
		foreach($fieldnames as $fns)
			{
			if ($oldcfieldname==$fns[0]) {$newcfieldname=$fns[1];}
			}
		$insert=str_replace("'$oldcid'", "''", $car); //replace cid (remove it)
		$insert=str_replace("'$oldqid'", "'$newqid'", $insert); //replace qid
		$insert=str_replace("'$oldcfieldname'", "'$newcfieldname'", $insert); //replace cfieldname
		$insert=str_replace("$oldcqid'", "$newcqid'", $insert); //replace cqid
		$insert=str_replace("INTO conditions", "INTO {$dbprefix}conditions", $insert);
		$result=mysql_query($insert) or die ("Couldn't insert condition<br />$insert<br />".mysql_error());
		}
	}

echo "<br />\n<b><font color='green'>"._SUCCESS."</font></b><br />\n";
echo "<b><u>"._IS_IMPORTSUMMARY."</u></b><br />\n";
echo "<ul>\n\t<li>"._SURVEYS.": $countsurveys</li>\n";
echo "\t<li>"._GROUPS.": $countgroups</li>\n";
echo "\t<li>"._QUESTIONS.": $countquestions</li>\n";
echo "\t<li>"._ANSWERS.": $countanswers</li>\n";
echo "\t<li>"._CONDITIONS.": $countconditions</li>\n";
echo "\t<li>"._LABELSET.": $countlabelsets ("._LABELANS.": $countlabels)</li>\n</ul>\n";

echo "<b>"._IS_SUCCESS."</b><br />\n";
echo "<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname?sid=$newsid', '_top')\">\n";

echo "</td></tr></table>\n";
echo "</body>\n</html>";
unlink($the_full_file_path);
?>