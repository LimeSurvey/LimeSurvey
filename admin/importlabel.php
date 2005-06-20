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
echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"._IMPORTLABEL."</strong></td></tr>\n";
echo "\t<tr height='22' bgcolor='#CCCCCC'><td align='center'>$setfont\n";

$the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];

if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
	{
	echo "<strong><font color='red'>"._ERROR."</font></strong><br />\n";
	echo _IS_FAILUPLOAD."<br /><br />\n";
	echo "<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\">\n";
	echo "</td></tr></table>\n";
	echo "</body>\n</html>\n";
	exit;
	}

// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY

$csarray=buildLabelsetCSArray();
//$csarray is now a keyed array with the Checksum of each of the label sets, and the lid as the key

echo "<strong><font color='green'>"._SUCCESS."</font></strong><br />\n";
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

if (substr($bigarray[1], 0, 25) != "# SURVEYOR LABEL SET DUMP")
	{
	echo "<strong><font color='red'>"._ERROR."</font></strong><br />\n";
	echo _IQ_WRONGFILE."<br /><br />\n";
	echo "<input $btstyle type='submit' value='"._IL_GOLABELADMIN."' onClick=\"window.open('labels.php', '_top')\">\n";
	echo "</td></tr></table>\n";
	echo "</body>\n</html>\n";
	exit;
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
	if ($i<$stoppoint-2) {$labelsetarray[] = $bigarray[$i];}
	unset($bigarray[$i]);
	}
$bigarray = array_values($bigarray);

//LABELS
if (array_search("#</pre>\n", $bigarray))
	{
	$stoppoint = array_search("#</pre>\n", $bigarray);
	}
elseif (array_search("#</pre>\r\n", $bigarray))
	{
	$stoppoint = array_search("#</pre>\r\n", $bigarray);
	}
else
	{
	$stoppoint = count($bigarray)-1;
	}
for ($i=0; $i<$stoppoint; $i++)
	{
	$labelarray[] = $bigarray[$i];
	//echo "($i)[$stoppoint]An Answer! - {$bigarray[$i]}<br />";
	unset($bigarray[$i]);
	}
$bigarray = array_values($bigarray);


$countlabelsets = count($labelsetarray);
$countlabels = count($labelarray);


if ($labelsetarray)
	{
	foreach ($labelsetarray as $lsa)
		{
		$fieldorders=convertToArray($lsa, "`, `", "(`", "`)");
		$fieldcontents=convertToArray($lsa, "', '", "('", "')");

		$oldlidpos=array_search("lid", $fieldorders);
		$oldlid=$fieldcontents[$oldlidpos];
		
		$lsainsert = str_replace("'$oldlid'", "''", $lsa);
		$lsainsert = str_replace("INTO labelsets", "INTO {$dbprefix}labelsets", $lsainsert); //db prefix handler
		$lsiresult=mysql_query($lsainsert) or die ("ERROR Inserting<br />$lsainsert<br />".mysql_error());
		$newlid=mysql_insert_id();
		
		//GET NEW LID
		$nlidquery="SELECT lid FROM {$dbprefix}labelsets ORDER BY lid DESC LIMIT 1";
		$nlidresult=mysql_query($nlidquery);
		while ($nlidrow=mysql_fetch_array($nlidresult)) {$newlid=$nlidrow['lid'];}
		$labelreplacements[]=array($oldlid, $newlid);
		if ($labelarray)
			{
			foreach ($labelarray as $la)
				{
				$lfieldorders=convertToArray($la, "`, `", "(`", "`)");
				$lfieldcontents=convertToArray($la, "', '", "('", "')");
	
				$labellidpos=array_search("lid", $lfieldorders);
				$labellid=$lfieldcontents[$labellidpos];
				if ($labellid == $oldlid)
					{
					$lainsert = str_replace("'$labellid'", "'$newlid'", $la);
					$lainsert = str_replace ("INTO labels", "INTO {$dbprefix}labels", $lainsert);
					$liresult=mysql_query($lainsert);
					}
				}
			}
		}
	}
else
	{
	echo "<strong>No Labelsets Found!</strong><br /><br />\n";
	}

//NOW CHECK THAT THE NEW LABELSET ISN'T A REPLICA
$thisset="";
$query2 = "SELECT code, title, sortorder
		   FROM {$dbprefix}labels
		   WHERE lid=".$newlid."
		   ORDER BY sortorder, code";
$result2 = mysql_query($query2) or die("Died querying labelset $lid<br />$query2<br />".mysql_error());
$numfields=mysql_num_fields($result2);
while($row2=mysql_fetch_row($result2))
	{
	for ($i=0; $i<=$numfields-1; $i++)
		{
		$thisset .= $row2[$i];
		}
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
	$result=mysql_query($query) or die("Couldn't delete labels<br />$query<br />".mysql_error());
	$query = "DELETE FROM {$dbprefix}labelsets WHERE lid=$newlid";
	$result=mysql_query($query) or die("Couldn't delete labelset<br />$query<br />".mysql_error());
	$newlid=$lsmatch;
	echo "<p><i><font color='red'>"._IL_DUPLICATE."</font></i></p>\n";
	}

echo "<strong>LID:</strong> $newlid<br />\n";
echo "<br />\n<strong><font color='green'>"._SUCCESS."</font></strong><br />\n";
echo "<strong><u>"._IQ_IMPORTSUMMARY."</u></strong><br />\n";
echo "\t<li>"._LABELSETS.": $countlabelsets</li>\n";
echo "\t<li>"._LABELANS.": $countlabels</li></ul><br />\n";

echo "<strong>"._IS_SUCCESS."</strong><br />\n";
echo "<input $btstyle type='submit' value='"._IL_GOLABELADMIN."' onClick=\"window.open('labels.php?lid=$newlid', '_top')\">\n";

echo "</td></tr></table>\n";
echo "</body>\n</html>";
unlink($the_full_file_path);

function convertToArray($string, $seperator, $start, $end)
	{
	$begin=strpos($string, $start)+strlen($start);
	$len=strpos($string, $end)-$begin;
	$order=substr($string, $begin, $len);
	$orders=explode($seperator, $order);
	
	return $orders;
	}
?>