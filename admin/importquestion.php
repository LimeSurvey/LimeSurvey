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

$the_full_file_path = $homedir . "/" . $_FILES['the_file']['name'];

if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
	{
	echo "<b><center>A major error occurred and your file cannot be uploaded. See system administrator.</center></b>\n";
	echo "</body>\n</html>\n";
	exit;
	}

// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY

echo "<br />\n<b>IMPORTING QUESTION FILE</b><br />Question file succesfully uploaded<br /><br />\n";
echo "Reading File...<br />\n";
$handle = fopen($the_full_file_path, "r");
while (!feof($handle))
	{
	//$buffer = fgets($handle, 1024); //Length parameter is required for PHP versions < 4.2.0
	$buffer = fgets($handle, 10240); //To allow for very long survey welcomes (up to 10k)
	$bigarray[] = $buffer;
	}
fclose($handle);

if (!$_POST['sid'])
	{
	echo "No Survey ID (\$sid) has been provided. Import aborted!\n";
	echo "</body>\n</html>\n";
	exit;
	}
if (!$_POST['gid'])
	{
	echo "No Group ID (\$gid) has been provided. Import aborted!\n";
	echo "</body>\n</html>\n";
	exit;
	}
if (!$bigarray[0] == "# SURVEYOR QUESTION DUMP")
	{
	echo "This is NOT a Surveyor Question Dump File. Import aborted!\n";
	echo "</body>\n</html>\n";
	exit;
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
if (array_search("#</pre>\n", $bigarray))
	{
	$stoppoint = array_search("#</pre>\n", $bigarray);
	}
else
	{
	$stoppoint = count($bigarray)-1;
	}
for ($i=0; $i<$stoppoint; $i++)
	{
	$answerarray[] = $bigarray[$i];
	//echo "($i)[$stoppoint]An Answer! - {$bigarray[$i]}<br />";
	unset($bigarray[$i]);
	}
$bigarray = array_values($bigarray);


$countquestions = count($questionarray);
$countanswers = count($answerarray);
$countconditions = count($conditionsarray);

// CREATE SURVEY
$sid=$_POST['sid'];
$gid=$_POST['gid'];


// DO GROUPS, QUESTIONS FOR GROUPS, THEN ANSWERS FOR QUESTIONS IN A NESTED FORMAT!
if ($questionarray)
	{
	foreach ($questionarray as $qa)
		{
		$oldqidpos=strpos($qa, "VALUES ('") + strlen("VALUES ('");
		$oldqid=substr($qa, $oldqidpos, (strpos($qa, "', '", $oldqidpos))-$oldqidpos);
		$oldsidpos=strpos($qa, "', '", $oldqidpos+1)+strlen("', '");
		$oldsid=substr($qa, $oldsidpos, (strpos($qa, "', '", $oldsidpos))-$oldsidpos);
		$oldgidpos=strpos($qa, "', '", $oldsidpos+1)+strlen("', '");
		$oldgid=substr($qa, $oldgidpos, (strpos($qa, "', '", $oldgidpos))-$oldgidpos);
		$qinsert = str_replace("('$oldqid', '$oldsid', '$oldgid',", "('$sid', '$gid',", $qa);
		$qinsert = str_replace("(`qid`, ", "(", $qinsert);
		$qres = mysql_query($qinsert) or die ("<b>ERROR:</b> Failed to insert question<br />\n$qinsert<br />\n".mysql_error());
		//GET NEW QID
		$qidquery = "SELECT qid FROM questions ORDER BY qid DESC LIMIT 1";
		$qidres = mysql_query($qidquery);
		while ($qrow = mysql_fetch_row($qidres)) {$newqid = $qrow[0];}
		$newrank=0;
		//NOW DO NESTED ANSWERS FOR THIS QID
		echo "<br />COUNT: ".count($answerarray);
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
				echo "DOING $aa<br />\n";
				echo "SUBTR:".substr($aa, $astart, $aend). " VS $oldqid<br />\n";
				if (substr($aa, $astart, $aend) == ($oldqid))
					{
					$ainsert = str_replace("('$oldqid", "('$newqid", $aa);
					//$ainsert = substr(trim($ainsert), 0, -1);
					echo "$ainsert<br />\n";
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


//We've built two arrays along the way - one containing the old SID, GID and QIDs - and their NEW equivalents
//and one containing the old 'extended fieldname' and its new equivalent.  These are needed to import conditions.


echo "QUESTION SUMMARY:<br />\n";
echo "<ul>\n\t<li>Questions: $countquestions</li>\n";
echo "\t<li>Answers: $countanswers</li>\n</ul>\n";

echo "<b>Question Import has been completed. <a href='admin.php?sid=$sid&gid=$gid&qid=$newqid'>Administration</a></b>";

echo "</body>\n</html>";
unlink($the_full_file_path);
?>