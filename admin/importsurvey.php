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
echo "<center><b>Importing Survey</b></center><br /><br />\n";

$the_full_file_path = $homedir . "/" . $_FILES['the_file']['name'];

if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
	{
	echo "<b><center>A major error occurred and your file cannot be uploaded. See system administrator.</center></b>\n";
	echo "</body>\n</html>\n";
	exit;
	}

// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY

echo "<br />\n<b>IMPORTING FILE</b><br />File succesfully uploaded<br /><br />\n";
echo "Reading File...<br />\n";
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
	echo "This is NOT a Surveyor Dump File. Import aborted!\n";
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
if ($noconditions != "Y")
	{
	$stoppoint = count($bigarray)-1;
	for ($i=0; $i<=$stoppoint+1; $i++)
		{
		if ($i<$stoppoint-2) {$conditionsarray[] = $bigarray[$i];}
		unset($bigarray[$i]);
		}
	}

$countsurveys = count($tablearray);
$countgroups = count($grouparray);
$countquestions = count($questionarray);
$countanswers = count($answerarray);
$countconditions = count($conditionsarray);

// CREATE SURVEY
$sid = substr($tablearray[0], strpos($tablearray[0], "('")+2, (strpos($tablearray[0], "',")-(strpos($tablearray[0], "('")+2)));
$insert = str_replace("('$sid'", "(''", $tablearray[0]);
//$insert = substr($insert, 0, -1);
$iresult = mysql_query($insert) or die("Insert of imported survey completely failed<br />\n<font size='1'>$insert</font><hr>$tablearray[0]<br /><br />\n" . mysql_error() . "</body>\n</html>");

$oldsid=$sid;

//GET NEW SID
$sidquery = "SELECT sid FROM surveys ORDER BY sid DESC LIMIT 1";
$sidres = mysql_query($sidquery);
while ($srow = mysql_fetch_row($sidres)) {$newsid = $srow[0];}

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
					$qres = mysql_query($qinsert) or die ("<b>ERROR:</b> Failed to insert question<br />\n$qinsert<br />\n".mysql_error()."</body>\n</html>");
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
								$ares = mysql_query($ainsert) or die ("<b>ERROR:</b> Failed to insert answer<br />\n$ainsert<br />\n".mysql_error()."</body>\n</html>");
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
		//echo "$replacewith<br />\n";
		$insert=str_replace($toreplace, $replacewith, $car);
		//echo "$insert<br /><br />\n";
		$result=mysql_query($insert) or die ("Couldn't insert condition<br />$insert<br />".mysql_error());
		}
	}

echo "SURVEY SUMMARY:<br />\n";
echo "<ul>\n\t<li>Surveys: $countsurveys</li>\n";
echo "\t<li>Groups: $countgroups</li>\n";
echo "\t<li>Questions: $countquestions</li>\n";
echo "\t<li>Answers: $countanswers</li>\n";
echo "\t<li>Conditions: $countconditions</li>\n</ul>\n";

echo "<b>Survey Import has been completed. <a href='admin.php?sid=$newsid'>Administration</a></b>";

echo "</body>\n</html>";
unlink($the_full_file_path);
?>