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

$the_path="$homedir";
$the_full_file_path=$homedir."/".$the_file_name;

if (!@copy($the_file, $the_path . "/" . $the_file_name))
	{
	echo "<B><CENTER>Something went horribly wrong. See system administrator.";
	exit;
	}

// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY

echo "\n<BR><B>IMPORTING FILE</B><BR>File succesfully uploaded<BR><BR>";
echo "\nReading File...<BR>";
$handle=fopen($the_full_file_path, "r");
while (!feof($handle))
	{
	$buffer=fgets($handle);
	$bigarray[]=$buffer;
	}
fclose($handle);

if (!$bigarray[0] == "# SURVEYOR SURVEY DUMP")
	{
	echo "This is NOT a Surveyor Dump File. Import aborted!";
	exit;
	}

for ($i=0; $i<10; $i++)
	{
	unset($bigarray[$i]);
	}
$bigarray=array_values($bigarray);

//TABLES
$stoppoint=array_search("# NEW TABLE\n", $bigarray);
for ($i=0; $i<=$stoppoint+2; $i++)
	{
	if ($i<$stoppoint-1) {$tablearray[]=$bigarray[$i];}
	unset($bigarray[$i]);
	}
$bigarray=array_values($bigarray);

//GROUPS
$stoppoint=array_search("# NEW TABLE\n", $bigarray);
for ($i=0; $i<=$stoppoint+2; $i++)
	{
	if ($i<$stoppoint-1) {$grouparray[]=$bigarray[$i];}
	unset($bigarray[$i]);
	}
$bigarray=array_values($bigarray);

//QUESTIONS
$stoppoint=array_search("# NEW TABLE\n", $bigarray);
for ($i=0; $i<=$stoppoint+2; $i++)
	{
	if ($i<$stoppoint-1) {$questionarray[]=$bigarray[$i];}
	unset($bigarray[$i]);
	}
$bigarray=array_values($bigarray);

//ANSWERS
$stoppoint=count($bigarray);
for ($i=0; $i<=$stoppoint+2; $i++)
	{
	if ($i<$stoppoint-1) {$answerarray[]=$bigarray[$i];}
	unset($bigarray[$i]);
	}
$bigarray=array_values($bigarray);

$countsurveys=count($tablearray);
$countgroups=count($grouparray);
$countquestions=count($questionarray);
$countanswers=count($answerarray);

echo "SURVEY SUMMARY:<BR>";
echo "<UL><LI>Surveys: $countsurveys</LI>";
echo "<LI>Groups: $countgroups</LI>";
echo "<LI>Questions: $countquestions</LI>";
echo "<LI>Answers: $countanswers</LI></UL>";

// CREATE SURVEY
$sid=substr($tablearray[0], strpos($tablearray[0], "('")+2, (strpos($tablearray[0], "',")-(strpos($tablearray[0], "('")+2)));
$insert=str_replace("('$sid'", "(''", $tablearray[0]);
//$insert=substr($insert, 0, -1);
$iresult=mysql_query($insert) or die("Insert of imported survey completely failed<BR>$insert<BR><BR>".mysql_error());

//GET NEW SID
$sidquery="SELECT sid FROM surveys ORDER BY sid DESC LIMIT 1";
$sidres=mysql_query($sidquery);
while ($srow=mysql_fetch_row($sidres)){$newsid=$srow[0];}

// DO GROUPS, QUESTIONS FOR GROUPS, THEN ANSWERS FOR QUESTIONS IN A NESTED FORMAT!
foreach ($grouparray as $ga)
	{
	$gid=substr($ga, strpos($ga, "('")+2, (strpos($ga, "',")-(strpos($ga, "('")+2)));
	$ginsert=str_replace("('$gid', '$sid',", "('', '$newsid',", $ga);
	//$ginsert=substr($ginsert, 0, -1);
	$gres=mysql_query($ginsert);
	//GET NEW GID
	$gidquery="SELECT gid FROM groups ORDER BY gid DESC LIMIT 1";
	$gidres=mysql_query($gidquery);
	while ($grow=mysql_fetch_row($gidres)){$newgid=$grow[0];}
	//NOW DO NESTED QUESTIONS FOR THIS GID
	foreach ($questionarray as $qa)
		{
		$sidpos=", '$sid'";
		$start=strpos($qa, "$sidpos")+2+strlen($sid)+5;
		$end=strpos($qa, "'", $start)-$start;
		if (substr($qa, $start, $end)==$gid)
			{
			$qid=substr($qa, strpos($qa, "('")+2, (strpos($qa, "',")-(strpos($qa, "('")+2)));
			$qinsert=str_replace("('$qid', '$sid', '$gid',", "('$newsid', '$newgid',", $qa);
			$qinsert=str_replace("(`qid`, ", "(", $qinsert);
			//$qinsert=substr(trim($qinsert), 0, -1);
			//echo "$qinsert<BR>";
			$qres=mysql_query($qinsert) or die ("<B>ERROR:</B> Failed to insert question<BR>$qinsert<BR>".mysql_error());
			//GET NEW GID
			$qidquery="SELECT qid FROM questions ORDER BY qid DESC LIMIT 1";
			$qidres=mysql_query($qidquery);
			while ($qrow=mysql_fetch_row($qidres)) {$newqid=$qrow[0];}	
			//NOW DO NESTED ANSWERS FOR THIS QID
			foreach ($answerarray as $aa)
				{
				$qidpos="('";
				$astart=strpos($aa, "$qidpos")+2;
				$aend=strpos($aa, "'", $astart)-$astart;
				if (substr($aa, $astart, $aend) == ($qid))
					{
					$ainsert=str_replace("('$qid", "('$newqid", $aa);
					//$ainsert=substr(trim($ainsert), 0, -1);
					$ares=mysql_query($ainsert) or die ("<B>ERROR:</B> Failed to insert answer<BR>$ainsert<BR>".mysql_error());
					}
				}
			}
		}
	}
echo "<B>Survey Import has been completed. <a href='admin.php?sid=$newsid'>Administration</a>";
unlink($the_full_file_path);
?>