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
echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._IMPORTGROUP."</b></td></tr>\n";
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

if (substr($bigarray[1], 0, 21) != "# SURVEYOR GROUP DUMP")
	{
	echo "<b><font color='red'>"._ERROR."</font></b><br />\n";
	echo _IG_WRONGFILE."<br /><br />\n";
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
if ($noconditions != "Y")
	{
	$stoppoint = count($bigarray)-1;
	for ($i=0; $i<=$stoppoint+1; $i++)
		{
		if ($i<$stoppoint-1) {$labelsarray[] = $bigarray[$i];}
		unset($bigarray[$i]);
		}
	}




$countgroups = count($grouparray);
$countquestions = count($questionarray);
$countanswers = count($answerarray);
$countconditions = count($conditionsarray);
$countlabelsets = count($labelsetsarray);
$countlabels = count($labelsarray);

$newsid = $_POST["sid"];

//DO ANY LABELSETS FIRST, SO WE CAN KNOW WHAT THEY'RE NEW LID IS FOR THE QUESTIONS
if ($labelsetsarray) {
	//echo "DOING LABELSETS<br />";
	foreach ($labelsetsarray as $lsa) {
		$fieldorders=convertToArray($lsa, "`, `", "(`", "`)");
		$fieldcontents=convertToArray($lsa, "', '", "('", "')");
		$newfieldcontents=$fieldcontents;
		$oldlidpos=array_search("lid", $fieldorders);
		$oldlid=$fieldcontents[$oldlidpos];
		
		$newfieldcontents[array_search("lid", $fieldorders)]="";
		$newvalues="('".implode("', '", $newfieldcontents)."')";
		$lsainsert = str_replace("('".implode("', '", $fieldcontents)."')", $newvalues, $lsa);
		//$lsainsert = str_replace("'$oldlid'", "''", $lsa);
		$lsainsert = str_replace("INTO labelsets", "INTO {$dbprefix}labelsets", $lsainsert); //db prefix handler
		$lsiresult=mysql_query($lsainsert);
		
		//GET NEW LID
		$nlidquery="SELECT lid FROM {$dbprefix}labelsets ORDER BY lid DESC LIMIT 1";
		$nlidresult=mysql_query($nlidquery);
		while ($nlidrow=mysql_fetch_array($nlidresult)) {$newlid=$nlidrow['lid'];}
		$labelreplacements[]=array($oldlid, $newlid);
		if ($labelsarray) {
			foreach ($labelsarray as $la) {
				//GET ORDER OF FIELDS
				$lfieldorders=convertToArray($la, "`, `", "(`", "`)");
				$lfieldcontents=convertToArray($la, "', '", "('", "')");
				$newlfieldcontents=$lfieldcontents;
				$labellidpos=array_search("lid", $lfieldorders);
				$labellid=$lfieldcontents[$labellidpos];
				if ($labellid == $oldlid) {
					$newlfieldcontents[array_search("lid", $lfieldorders)]=$newlid;
					$newlvalues="('".implode("', '", $newlfieldcontents)."')";
					$lainsert = str_replace("('".implode("', '", $lfieldcontents)."')", $newlvalues, $la);
					//$lainsert = str_replace("'$labellid'", "'$newlid'", $la);
					$lainsert = str_replace ("INTO labels", "INTO {$dbprefix}labels", $lainsert);
					$liresult=mysql_query($lainsert);
				}
			}
		}
	}
}

// DO GROUPS, QUESTIONS FOR GROUPS, THEN ANSWERS FOR QUESTIONS IN A NESTED FORMAT!
if ($grouparray) {
	foreach ($grouparray as $ga) {
		//GET ORDER OF FIELDS
		$gafieldorders=convertToArray($ga, "`, `", "(`", "`)");
		$gacfieldcontents=convertToArray($ga, "', '", "('", "')");
		$sid=$gacfieldcontents[array_search("sid", $gafieldorders)];
		$oldsid=$sid;
		$gidpos=array_search("gid", $gafieldorders);
		$gid=$gacfieldcontents[$gidpos];
		//$gid = substr($ga, strpos($ga, "('")+2, (strpos($ga, "',")-(strpos($ga, "('")+2)));
		$ginsert = str_replace("('$gid', '$sid',", "('', '$newsid',", $ga);
		$ginsert = str_replace("INTO groups", "INTO {$dbprefix}groups", $ginsert);
		$oldgid=$gid;
		$gres = mysql_query($ginsert);
		//GET NEW GID
		$gidquery = "SELECT gid FROM {$dbprefix}groups ORDER BY gid DESC LIMIT 1";
		$gidres = mysql_query($gidquery);
		while ($grow = mysql_fetch_row($gidres)) {$newgid = $grow[0];}
		//NOW DO NESTED QUESTIONS FOR THIS GID
		if ($questionarray) {
			foreach ($questionarray as $qa) {
				$qafieldorders=convertToArray($qa, "`, `", "(`", "`)");
				$qacfieldcontents=convertToArray($qa, "', '", "('", "')");
				$newfieldcontents=$qacfieldcontents;
				$thisgid=$qacfieldcontents[array_search("gid", $qafieldorders)];
				if ($thisgid == $gid) {
					$qid = $qacfieldcontents[array_search("qid", $qafieldorders)];
					$newfieldcontents[array_search("qid", $qafieldorders)] = "";
					$newfieldcontents[array_search("sid", $qafieldorders)] = $newsid;
					$newfieldcontents[array_search("gid", $qafieldorders)] = $newgid;
					$oldqid=$qid;
					$newvalues="('".implode("', '", $newfieldcontents)."')";
					$qinsert = str_replace ("('".implode("', '", $qacfieldcontents)."')", $newvalues, $qa);
					$qinsert = str_replace("INTO questions", "INTO {$dbprefix}questions", $qinsert);
					$type = $qacfieldcontents[array_search("type", $qafieldorders)]; //Get the type
					$other = $qacfieldcontents[array_search("other", $qafieldorders)]; //Get 'other';
					$qres = mysql_query($qinsert) or die ("<b>"._ERROR."</b> Failed to insert question<br />\n$qinsert<br />\n".mysql_error()."</body>\n</html>");
					$qidquery = "SELECT qid, lid FROM {$dbprefix}questions ORDER BY qid DESC LIMIT 1"; //Get last question added (finds new qid)
					$qidres = mysql_query($qidquery);
					while ($qrow = mysql_fetch_array($qidres)) {$newqid = $qrow['qid']; $oldlid=$qrow['lid'];}
					if ($type == "F") {//IF this is a flexible label array, update the lid entry
						foreach ($labelreplacements as $lrp) {
							if ($lrp[0] == $oldlid) {
								$lrupdate="UPDATE {$dbprefix}questions SET lid='{$lrp[1]}' WHERE qid=$newqid";
								$lrresult=mysql_query($lrupdate);
							}
						}
					}
					$newrank=0;
					//NOW DO NESTED ANSWERS FOR THIS QID
					if ($answerarray) {
						foreach ($answerarray as $aa) {
							$aafieldorders=convertToArray($aa, "`, `", "(`", "`)");
							$aacfieldcontents=convertToArray($aa, "', '", "('", "')");
							$newfieldcontents=$aacfieldcontents;
							$code=$aacfieldcontents[array_search("code", $aafieldorders)];
							$thisqid=$aacfieldcontents[array_search("qid", $aafieldorders)];
							if ($thisqid == $qid) {
								$newfieldcontents[array_search("qid", $aafieldorders)]=$newqid;
								$newvalues="('".implode("', '", $newfieldcontents)."')";
								$ainsert = str_replace("('".implode("', '", $aacfieldcontents)."')", $newvalues, $aa);
								//$ainsert = str_replace("'$qid'", "'$newqid'", $aa);
								$ainsert = str_replace("INTO answers", "INTO {$dbprefix}answers", $ainsert);
								$ares = mysql_query($ainsert) or die ("<b>"._ERROR."</b> Failed to insert answer<br />\n$ainsert<br />\n".mysql_error()."</body>\n</html>");
								if ($type == "A" || $type == "B" || $type == "C" || $type == "M" || $type == "P" || $type == "F") {
									$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid.$code, $newsid."X".$newgid."X".$newqid.$code);
									if ($type == "P") {
										$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid.$code."comment", $newsid."X".$newgid."X".$newqid.$code."comment");
									}
								}
								elseif ($type == "R") {
									$newrank++;
								}
							}			
						}
						if (($type == "A" || $type == "B" || $type == "C" || $type == "M" || $type == "P") && ($other == "Y")) {
							$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid."other", $newsid."X".$newgid."X".$newqid."other");
							if ($type == "P") {
								$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid."othercomment", $newsid."X".$newgid."X".$newqid."othercomment");
							}
						}
						if ($type == "R" && $newrank >0) {
							for ($i=1; $i<=$newrank; $i++) {
								$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid.$i, $newsid."X".$newgid."X".$newqid.$i);
							}
						}
						if ($type != "A" && $type != "B" && $type != "C" && $type != "R" && $type != "M" && $type != "P") {
							$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid, $newsid."X".$newgid."X".$newqid);
							if ($type == "O") {
								$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid."comment", $newsid."X".$newgid."X".$newqid."comment");
							}
						}
						$substitutions[]=array($oldsid, $oldgid, $oldqid, $newsid, $newgid, $newqid);
					} else {
						$fieldnames[]=array($oldsid."X".$oldgid."X".$oldqid, $newsid."X".$newgid."X".$newqid);
						$substitutions[]=array($oldsid, $oldgid, $oldqid, $newsid, $newgid, $newqid);
					}
				//echo $oldsid."X".$oldgid."X".$oldqid ."--". $newsid."X".$newgid."X".$newqid."<br />";
				}
			}
		}
	}
}
//We've built two arrays along the way - one containing the old SID, GID and QIDs - and their NEW equivalents
//and one containing the old 'extended fieldname' and its new equivalent.  These are needed to import conditions.

if ($conditionsarray) {//ONLY DO THIS IF THERE ARE CONDITIONS!
	foreach ($conditionsarray as $car) {
		$fieldorders=convertToArray($car, "`, `", "(`", "`)");
		$fieldcontents=convertToArray($car, "', '", "('", "')");
		$newfieldcontents=$fieldcontents;
		$oldcid=$fieldcontents[array_search("cid", $fieldorders)];
		$oldqid=$fieldcontents[array_search("qid", $fieldorders)];
		$oldcfieldname=$fieldcontents[array_search("cfieldname", $fieldorders)];
		$oldcqid=$fieldcontents[array_search("cqid", $fieldorders)];
		foreach ($substitutions as $subs) {
			if ($oldqid==$subs[2])	{$newqid=$subs[5];}
			if ($oldcqid==$subs[2])	{$newcqid=$subs[5];}
		}
		foreach($fieldnames as $fns) {
			if ($oldcfieldname==$fns[0]) {$newcfieldname=$fns[1];}
		}
		$newfieldcontents[array_search("cid", $fieldorders)]="";
		$newfieldcontents[array_search("qid", $fieldorders)]=$newqid;
		$newfieldcontents[array_search("cfieldname", $fieldorders)]=$newcfieldname;
		$newfieldcontents[array_search("cqid", $fieldorders)]=$newcqid;
		$newvalues="('".implode("', '", $newfieldcontents)."')";
		$insert=str_replace("('".implode("', '", $fieldcontents)."')", $newvalues, $car);
		$insert=str_replace("INTO conditions", "INTO {$dbprefix}conditions", $insert);
		//echo "-- CONDITIONS --<br />$insert<br />\n";
		$result=mysql_query($insert) or die ("Couldn't insert condition<br />$insert<br />".mysql_error());
	}
}

echo "<br />\n<b><font color='green'>"._SUCCESS."</font></b><br />\n";
echo "<b><u>"._IG_IMPORTSUMMARY."</u></b><br />\n";
echo "<ul>\n\t<li>"._GROUPS.": $countgroups</li>\n";
echo "\t<li>"._QUESTIONS.": $countquestions</li>\n";
echo "\t<li>"._ANSWERS.": $countanswers</li>\n";
echo "\t<li>"._CONDITIONS.": $countconditions</li>\n";
echo "\t<li>"._LABELSET.": $countlabelsets ("._LABELANS.": $countlabels)</li>\n</ul>\n";

echo "<b>"._IG_SUCCESS."</b><br />\n";
echo "<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname?sid=$newsid', '_top')\">\n";

echo "</td></tr></table>\n";
echo "</body>\n</html>";
unlink($the_full_file_path);

function convertToArray($string, $seperator, $start, $end) {
	$begin=strpos($string, $start)+strlen($start);
	$len=strpos($string, $end)-$begin;
	$order=substr($string, $begin, $len);
	$orders=explode($seperator, $order);
	return $orders;
}
?>