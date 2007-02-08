<?php
/*
#############################################################
# >>> PHPSurveyor  										#
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

// A FILE TO IMPORT A DUMPED SURVEY FILE, AND CREATE A NEW SURVEY

echo "<br />\n";
echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>".$clang->gT("Import Label Set")."</strong></td></tr>\n";
echo "\t<tr bgcolor='#CCCCCC'><td align='center'>$setfont\n";

$the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];

if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
{
	echo "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
	echo $clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your admin folder.")."<br /><br />\n";
	echo "<input type='submit' value='".html_escape($clang->gT("Main Admin Screen"))."' onClick=\"window.open('$scriptname', '_top')\">\n";
	echo "</td></tr></table>\n";
	echo "</body>\n</html>\n";
	exit;
}

// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY

$csarray=buildLabelsetCSArray();
//$csarray is now a keyed array with the Checksum of each of the label sets, and the lid as the key

echo "<strong><font color='green'>".$clang->gT("Success")."</font></strong><br />\n";
echo $clang->gT("File upload succeeded.")."<br /><br />\n";
echo $clang->gT("Reading file..")."<br />\n";
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
	echo "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
	echo $clang->gT("This file is not a PHPSurveyor question file. Import failed.")."<br /><br />\n";
	echo "<input type='submit' value='".html_escape($clang->gT("Return to Labels Admin"))."' onClick=\"window.open('labels.php', '_top')\">\n";
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
		$lsiresult=$connect->Execute($lsainsert) or die ("ERROR Inserting<br />$lsainsert<br />".$connect->ErrorMsg());
		$newlid=$connect->Insert_ID();

		//GET NEW LID
		$nlidquery="SELECT lid FROM {$dbprefix}labelsets ORDER BY lid DESC LIMIT 1";
		$nlidresult=db_execute_assoc($nlidquery);
		while ($nlidrow=$nlidresult->FetchRow()) {$newlid=$nlidrow['lid'];}
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
					$liresult=$connect->Execute($lainsert);
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
$result2 = db_execute_num($query2) or die("Died querying labelset $lid<br />$query2<br />".$connect->ErrorMsg());
while($row2=$result2->FetchRow())
{
	$thisset .= implode('',$row2);
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
	echo "<p><i><font color='red'>".$clang->gT("There was a duplicate labelset, so this set was not imported. The duplicate will be used instead.")."</font></i></p>\n";
}

echo "<strong>LID:</strong> $newlid<br />\n";
echo "<br />\n<strong><font color='green'>".$clang->gT("Success")."</font></strong><br />\n";
echo "<strong><u>".$clang->gT("Question Import Summary")."</u></strong><br />\n";
echo "\t<li>".$clang->gT("Labelsets").": $countlabelsets</li>\n";
echo "\t<li>".$clang->gT("Labels").": $countlabels</li></ul><br />\n";

echo "<strong>".$clang->gT("Import of Survey is completed.")."</strong><br />\n";
echo "<input type='submit' value='".html_escape($clang->gT("Return to Labels Admin"))."' onClick=\"window.open('labels.php?lid=$newlid', '_top')\">\n";

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
