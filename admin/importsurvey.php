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

if (!$bigarray[0] == "# SURVEYOR SURVEY DUMP")
	{
	echo "<b><font color='red'>"._ERROR."</font></b><br />\n";
	echo _IS_WRONGFILE."<br /><br />\n";
	echo "<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\">\n";
	echo "</td></tr></table>\n";
	echo "</body>\n</html>\n";
	exit;
	}

for ($i=0; $i<9; $i++)
	{
	unset($bigarray[$i]);
	}
$bigarray = array_values($bigarray);

//SURVEYS
$stoppoint = array_search("# GROUPS TABLE\n", $bigarray);
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
else
	{
	$stoppoint = count($bigarray-1);
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
else
	{
	$stoppoint = count($bigarray-1);
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
		if ($i<$stoppoint-2) {$labelsarray[] = $bigarray[$i];}
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
$sid = substr($tablearray[0], strpos($tablearray[0], "('")+2, (strpos($tablearray[0], "',")-(strpos($tablearray[0], "('")+2)));
if (!$sid) 
	{
	echo "<br /><b><font color='red'>"._ERROR."</b></font><br />\n";
	echo _IS_IMPFAILED."<br />\n";
	echo _IS_FILEFAILS."<br />\n";
	echo "<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\">\n";
	echo "</td></tr></table>\n";
	echo "</body>\n</html>\n";
	exit;
	}
$insert = str_replace("('$sid'", "(''", $tablearray[0]);
//$insert = substr($insert, 0, -1);
$iresult = mysql_query($insert) or die("<br />"._IS_IMPFAILED."<br />\n<font size='1'>[$insert]</font><hr>$tablearray[0]<br /><br />\n" . mysql_error() . "</body>\n</html>");

$oldsid=$sid;

//GET NEW SID
$sidquery = "SELECT sid FROM surveys ORDER BY sid DESC LIMIT 1";
$sidres = mysql_query($sidquery);
while ($srow = mysql_fetch_row($sidres)) {$newsid = $srow[0];}

//DO ANY LABELSETS FIRST, SO WE CAN KNOW WHAT THEY'RE NEW LID IS FOR THE QUESTIONS
if ($labelsetsarray)
	{
	foreach ($labelsetsarray as $lsa)
		{
		$start = strpos($lsa, "VALUES ('")+strlen("VALUES ('");
		$end = strpos($lsa, "'", $start)-$start;
		$oldlid=substr($lsa, $start, $end);
		$lsainsert = str_replace("VALUES ('$oldlid", "VALUES ('", $lsa);
		$lsiresult=mysql_query($lsainsert);
		//GET NEW LID
		$nlidquery="SELECT lid FROM labelsets ORDER BY lid DESC LIMIT 1";
		$nlidresult=mysql_query($nlidquery);
		while ($nlidrow=mysql_fetch_array($nlidresult)) {$newlid=$nlidrow['lid'];}
		$labelreplacements[]=array($oldlid, $newlid);
		if ($labelsarray)
			{
			foreach ($labelsarray as $la)
				{
				$lstart=strpos($la, "VALUES ('")+strlen("VALUES ('");
				$lend = strpos($la, "'", $lstart)-$lstart;
				$labellid=substr($la, $lstart, $lend);
				if ($labellid == $oldlid)
					{
					$lainsert = str_replace("VALUES ('$labellid", "VALUES ('$newlid", $la);
					$liresult=mysql_query($lainsert);
					}
				}
			}
		}
	}

foreach ($labelreplacements as $lrt) {echo "OLD: ".$lrt[0]." - NEW: ".$lrt[1];}
// DO GROUPS, QUESTIONS FOR GROUPS, THEN ANSWERS FOR QUESTIONS IN A NESTED FORMAT!
if ($grouparray)
	{
	foreach ($grouparray as $ga)
		{
		$gid = substr($ga, strpos($ga, "('")+2, (strpos($ga, "',")-(strpos($ga, "('")+2)));
		$ginsert = str_replace("('$gid', '$sid',", "('', '$newsid',", $ga);
		$oldgid=$gid;
		//$ginsert = substr($ginsert, 0, -1);
		$gres = mysql_query($ginsert);
		//GET NEW GID
		$gidquery = "SELECT gid FROM groups ORDER BY gid DESC LIMIT 1";
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
					//$qinsert = substr(trim($qinsert), 0, -1);
					//FIELDNAME ARRAY GENERATION
					$typepos = "('$qid', '$sid', '$gid', '";
					$type = substr($qa, strpos($qa, $typepos)+strlen($typepos), 1);
					$otherpos = "')";
					$other = substr($qa, strpos($qa, $otherpos)-6, 1);
					
					//echo "$qinsert<br />\n";
					$qres = mysql_query($qinsert) or die ("<b>"._ERROR."</b> Failed to insert question<br />\n$qinsert<br />\n".mysql_error()."</body>\n</html>");
					//GET NEW GID
					$qidquery = "SELECT qid FROM questions ORDER BY qid DESC LIMIT 1";
					$qidres = mysql_query($qidquery);
					while ($qrow = mysql_fetch_row($qidres)) {$newqid = $qrow[0];}
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
								//$ainsert = substr(trim($ainsert), 0, -1);
								$ares = mysql_query($ainsert) or die ("<b>"._ERROR."</b> Failed to insert answer<br />\n$ainsert<br />\n".mysql_error()."</body>\n</html>");
								if ($type == "A" || $type == "B" || $type == "C" || $type == "M" || $type == "P")
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
		$startpos=strpos($car, "('")+2;
		$nextpos=strpos($car, "', '", $startpos);
		$oldcid=substr($car, $startpos, $nextpos-$startpos);
		$startpos=$nextpos+4;
		$nextpos=strpos($car, "', '", $startpos);
		$oldqid=substr($car, $startpos, $nextpos-$startpos);
		$startpos=$nextpos+4;
		$nextpos=strpos($car, "', '", $startpos);
		$oldcqid=substr($car, $startpos, $nextpos-$startpos);
		$startpos=$nextpos+4;
		$nextpos=strpos($car, "', '", $startpos);
		$oldcfieldname=substr($car, $startpos, $nextpos-$startpos);
		$toreplace="('$oldcid', '$oldqid', '$oldcqid', '$oldcfieldname'";
		//echo "$toreplace<br />\n";
		foreach ($substitutions as $subs)
			{
			if ($oldqid==$subs[2])	{$newqid=$subs[5];}
			if ($oldcqid==$subs[2]) 	{$newcqid=$subs[5];}
			}
		foreach($fieldnames as $fns)
			{
			if ($oldcfieldname==$fns[0]) {$newcfieldname=$fns[1];}
			}
		$replacewith="('', '$newqid', '$newcqid', '$newcfieldname'";
		$insert=str_replace($toreplace, $replacewith, $car);
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