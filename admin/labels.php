<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA
 	# > Date: 	 19 April 2003								#
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

include("config.php");

if (!isset($action)) {$action=returnglobal('action');}
if (!isset($lid)) {$lid=returnglobal('lid');}

sendcacheheaders();

//DO DATABASE UPDATESTUFF
if ($action == "updateset") {updateset($lid);}
if ($action == "insertset") {insertset();}
if ($action == "modanswers") {modanswers($lid);}
if ($action == "delset") {if (delset($lid)) {unset($lid);}}

echo $htmlheader;

if ($action == "importlabels")
	{
	include("importlabel.php");
	exit;
	}


echo "<script type='text/javascript'>\n";
echo "<!--\n";
echo "\tfunction showhelp(action)\n";
echo "\t\t{\n";
echo "\t\tvar name='help';\n";
echo "\t\tif (action == \"hide\")\n";
echo "\t\t\t{\n";
echo "\t\t\tdocument.getElementById(name).style.display='none';\n";
echo "\t\t\t}\n";
echo "\t\telse if (action == \"show\")\n";
echo "\t\t\t{\n";
echo "\t\t\tdocument.getElementById(name).style.display='';\n";
echo "\t\t\t}\n";
echo "\t\t}\n";
echo "-->\n";
echo "</script>\n";
echo "<table width='100%' border='0' cellpadding='0' cellspacing='0' >\n";
echo "\t<tr>\n";
echo "\t\t<td valign='top' align='center' bgcolor='#BBBBBB'>\n";
echo "\t\t\t<table height='1' cellspacing='1'><tr><td></td></tr></table>\n";
echo "\t\t\t<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
echo "\t\t\t\t<tr bgcolor='#555555'><td height='4' colspan='2'><font size='1' face='verdana' color='white'><b>"._LABELCONTROL."</b></td></tr>\n";
echo "\t\t\t\t<tr bgcolor='#999999'>\n";
echo "\t\t\t\t\t<td>\n";
echo "\t\t\t\t\t<input type='image' src='./images/home.gif' title='"._B_ADMIN_BT."' border='0' align='left' hspace='0' onClick=\"window.open('$scriptname', '_top')\">\n";
echo "\t\t\t\t\t<img src='./images/blank.gif' width='11' height='20' border='0' hspace='0' align='left'>\n";
echo "\t\t\t\t\t<img src='./images/seperator.gif' border='0' hspace='0' align='left'>\n";
echo "\t\t\t\t\t<img src='./images/blank.gif' width='60' height='20' border='0' hspace='0' align='left'>\n";
echo "\t\t\t\t\t<img src='./images/seperator.gif' border='0' hspace='0' align='left'>\n";
echo "\t\t\t\t\t</td>\n";
echo "\t\t\t\t\t<td align='right' width='320'>\n";
echo "\t\t\t\t\t<input type='image' src='./images/showhelp.gif' title='"._A_HELP_BT."' align='right' hspace='0' border='0' onClick=\"showhelp('show')\">\n";
echo "\t\t\t\t\t<img src='./images/blank.gif' width='42' height='20' align='right' hspace='0' border='0'>\n";
echo "\t\t\t\t\t<img src='./images/seperator.gif' align='right' hspace='0' border='0'>\n";
echo "\t\t\t\t\t<input type='image' src='./images/add.gif' align='right' hspace='0' border='0' title='"._L_ADDSET_BT."' onClick=\"window.open('labels.php?action=newset', '_top')\">\n";
echo "\t\t\t\t\t$setfont<font size='1'><b>"._LABELSETS.":</b> ";
echo "\t\t\t\t\t<select style='font-size: 9; font-family: verdana; font-color: #333333; background: SILVER; width: 160' onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
$labelsets=getlabelsets();
if (count($labelsets)>0)
	{
	foreach ($labelsets as $lb)
		{
		echo "\t\t\t\t\t\t<option value='?lid={$lb[0]}'";
		if ($lb[0] == $lid) {echo " selected";}
		echo ">{$lb[1]}</option>\n";
		}
	}
echo "\t\t\t\t\t\t<option value=''";
if (!isset($lid)) {echo " selected";}
echo ">"._AD_CHOOSE."</option>\n";

echo "\t\t\t\t\t</select>\n";
echo "\t\t\t\t\t</td>\n";
echo "\t\t\t\t</tr>\n";
echo "\t\t\t</table>\n";
echo "\t\t<table height='1'><tr><td></td></tr></table>\n";

//NEW SET
if ($action == "newset" || $action == "editset")
	{
	if ($action == "editset")
		{
		$query = "SELECT * FROM labelsets WHERE lid=$lid";
		$result=mysql_query($query);
		while ($row=mysql_fetch_array($result)) {$lbname=$row['label_name']; $lblid=$row['lid'];}
		}
	echo "\t\t<table width='100%' bgcolor='#DDDDDD'>\n";
	echo "\t\t<form method='post' action='labels.php'>\n";
	echo "\t\t\t<tr bgcolor='black'>\n";
	echo "\t\t\t\t<td colspan='2' align='center'>$setfont<font color='white'><b>\n";
	if ($action == "newset") {echo _LB_NEWSET;}
	else {echo _LB_EDITSET;}
	echo "\t\t\t\t</font></font></b></td>\n";
	echo "\t\t\t</tr>\n";
	echo "\t\t\t<tr>\n";
	echo "\t\t\t\t<td align='right' width='15%'>\n";
	echo "\t\t\t\t\t$setfont<b>"._LL_NAME.":</b></font>";
	echo "\t\t\t\t</td>\n";
	echo "\t\t\t\t<td>\n";
	echo "\t\t\t\t\t<input type='text' $slstyle name='label_name' value='$lbname'>\n";
	echo "\t\t\t\t</td>\n";
	echo "\t\t\t</tr>\n";
	echo "\t\t\t<tr>\n";
	echo "\t\t\t\t<td></td>\n";
	echo "\t\t\t\t<td>\n";
	echo "\t\t\t\t<input $btstyle type='submit' value='";
	if ($action == "newset") {echo _ADD;}
	else {echo _UPDATE;}
	echo "'>\n";
	echo "\t\t\t\t</td>\n";
	echo "\t\t\t</tr>\n";
	echo "\t\t<input type='hidden' name='action' value='";
	if ($action == "newset") {echo "insertset";}
	else {echo "updateset";}
	echo "'>\n";
	if ($action == "editset") {echo "\t\t<input type='hidden' name='lid' value='$lblid'>\n";}
	echo "\t\t</form>\n";
	if ($action == "newset")
		{
		echo "\t\t\t<tr><td colspan='2' align='center'>\n";
		echo "\t\t\t\t$setfont<b>OR</b></font>\n";
		echo "\t\t\t</td></tr>\n";
		echo "\t\t\t<tr bgcolor='black'>\n";
		echo "\t\t\t\t<td colspan='2' align='center'>$setfont<font color='white'><b>\n";
		echo "\t\t\t\t"._IMPORTLABEL."\n";
		echo "\t\t\t\t</font></font></b></td>\n";
		echo "\t\t\t</tr>\n";
		echo "\t\t\t<tr>\n";
		echo "\t\t\t<form enctype='multipart/form-data' name='importlabels' action='labels.php' method='post'>\n";
		echo "\t\t\t\t<td align='right'>$setfont<b>"._SL_SELSQL."</b></font></td>\n";
		echo "\t\t<td><input $btstyle name=\"the_file\" type=\"file\" size=\"35\"></td></tr>\n";
		echo "\t<tr><td></td><td><input type='submit' $btstyle value='"._IMPORTLABEL."'></TD>\n";
		echo "\t<input type='hidden' name='action' value='importlabels'>\n";
		echo "\t</tr></form>\n</table>\n";
		
		}
	echo "\t\t</table>\n";
	}
//SET SELECTED
if (isset($lid) && ($action != "editset"))
	{
	$query = "SELECT * FROM labelsets WHERE lid=$lid";
	$result = mysql_query($query);
	while ($row=mysql_fetch_array($result)) 
		{
		echo "\t\t\t<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
		echo "\t\t\t\t<tr bgcolor='#555555'><td height='4' colspan='2'><font size='1' face='verdana' color='white'><b>"._LABELSET.":</b> {$row['label_name']}</td></tr>\n";
		echo "\t\t\t\t<tr bgcolor='#999999'>\n";
		echo "\t\t\t\t\t<td>\n";
		echo "\t\t\t\t\t<img src='./images/blank.gif' width='31' height='20' border='0' hspace='0' align='left'>\n";
		echo "\t\t\t\t\t<img src='./images/seperator.gif' border='0' hspace='0' align='left'>\n";
		echo "\t\t\t\t\t<img src='./images/blank.gif' width='60' height='20' border='0' hspace='0' align='left'>\n";
		echo "\t\t\t\t\t<img src='./images/seperator.gif' border='0' hspace='0' align='left'>\n";
		echo "\t\t\t\t\t<input type='image' src='./images/edit.gif' title='"._L_EDIT_BT."' align='left' border='0' hspace='0' onclick=\"window.open('labels.php?action=editset&lid=$lid', '_top')\">\n";
		echo "\t\t\t\t\t<input type='image' src='./images/delete.gif' title='"._L_DEL_BT."' align='left' border='0' hspace='0' onClick=\"window.open('labels.php?action=delset&lid=$lid', '_top')\">\n";
		echo "\t\t\t\t\t<input type='image' src='./images/export.gif' title='"._EXPORTLABEL."' align='left' border='0' hspace='0' onClick=\"window.open('dumplabel.php?lid=$lid', '_top')\">\n";
		echo "\t\t\t\t\t</td>\n";
		echo "\t\t\t\t</tr>\n";
		echo "\t\t\t</table>\n";
		}
	//LABEL ANSWERS
	$query = "SELECT * FROM labels WHERE lid=$lid ORDER BY sortorder, code";
	$result = mysql_query($query);
	$labelcount = mysql_num_rows($result);
	echo "\t\t<table height='1'><tr><td></td></tr></table>\n";
	echo "\t\t\t<table width='99%' align='center' style='border: solid; border-width: 1px; border-color: #555555' cellspacing='0'>\n";
	echo "\t\t\t\t<tr bgcolor='#555555' height='4'>\n";
	echo "\t\t\t\t\t<td colspan='4'><b><font size='1' face='verdana' color='white'>\n";
	echo _LABELANS;
	echo "\t\t\t\t\t</b></font></td>\n";
	echo "\t\t\t\t</tr>\n";
	echo "\t\t\t\t<tr bgcolor='#BBBBBB'>\n";
	echo "\t\t\t\t\t<td><b><font size='1' face='verdana'>\n";
	echo _LL_CODE;
	echo "\t\t\t\t\t</b></font></td>\n";
	echo "\t\t\t\t\t<td><b><font size='1' face='verdana'>\n";
	echo _LL_ANSWER;
	echo "\t\t\t\t\t</b></font></td>\n";
	echo "\t\t\t\t\t<td><b><font size='1' face='verdana'>\n";
	echo _LL_SORTORDER;
	echo "\t\t\t\t\t</b></font></td>\n";
	echo "\t\t\t\t\t<td><b><font size='1' face='verdana'>\n";
	echo _LL_ACTION;
	echo "\t\t\t\t\t</b></font></td>\n";
	echo "\t\t\t\t</tr>\n";
	$position=0;
	while ($row=mysql_fetch_array($result))
		{
		echo "\t\t\t\t<tr>\n";
		echo "\t\t\t\t<form method='post' action='labels.php'>\n";
		echo "\t\t\t\t\t<td>\n";
		echo "\t\t\t\t\t<input type='text' $slstyle name='code' size='5' value=\"{$row['code']}\">\n";
		echo "\t\t\t\t\t</td>\n";
		echo "\t\t\t\t\t<td>\n";
		echo "\t\t\t\t\t<input type='text' $slstyle name='title' size='35' value=\"{$row['title']}\">\n";
		echo "\t\t\t\t\t</td>\n";
		echo "\t\t\t\t\t<td>\n";
		if ($position > 0)
			{
			echo "\t\t\t\t\t<input $btstyle type='submit' name='method' value='"._AL_UP."'>\n";
			}
		else {echo "<img src='./images/spacer.gif' width='21' height='5' align='left'></font>";}
		if ($position < $labelcount-1)
			{
			echo "\t\t\t\t\t<input $btstyle type='submit' name='method' value='"._AL_DN."'>\n";
			}
		echo "\t\t\t\t\t</td>\n";
		echo "\t\t\t\t\t<td>\n";
		echo "\t\t\t\t\t<input $btstyle type='submit' name='method' value='"._AL_SAVE."'>\n";
		echo "\t\t\t\t\t<input $btstyle type='submit' name='method' value='"._AL_DEL."'>\n";
		echo "\t\t\t\t\t</td>\n";
		echo "\t\t\t\t</tr>\n";
		echo "\t\t\t\t<input type='hidden' name='sortorder' value='{$row['sortorder']}'>\n";
		echo "\t\t\t\t<input type='hidden' name='old_title' value='{$row['title']}'>\n";
		echo "\t\t\t\t<input type='hidden' name='old_code' value='{$row['code']}'>\n";
		echo "\t\t\t\t<input type='hidden' name='lid' value='$lid'>\n";
		echo "\t\t\t\t<input type='hidden' name='action' value='modanswers'>\n";
		echo "\t\t\t\t</form>\n";
		$position++;
		}
	$position=sprintf("%05d", $position);
	echo "\t\t\t\t<tr>\n";
	echo "\t\t\t\t<form method='post' action='labels.php'>\n";
	echo "\t\t\t\t\t<td>\n";
	echo "\t\t\t\t\t<input type='text' $slstyle name='code' size='5'>\n";
	echo "\t\t\t\t\t</td>\n";
	echo "\t\t\t\t\t<td>\n";
	echo "\t\t\t\t\t<input type='text' $slstyle name='title' size='35'>\n";
	echo "\t\t\t\t\t</td>\n";
	echo "\t\t\t\t\t<td>\n";
	echo "\t\t\t\t\t</td>\n";
	echo "\t\t\t\t\t<td>\n";
	echo "\t\t\t\t\t<input $btstyle type='submit' name='method' value='"._ADD."'>\n";
	echo "\t\t\t\t\t</td>\n";
	echo "\t\t\t\t</tr>\n";
	echo "\t\t\t\t<input type='hidden' name='sortorder' value='$position'>\n";
	echo "\t\t\t\t<input type='hidden' name='lid' value='$lid'>\n";
	echo "\t\t\t\t<input type='hidden' name='action' value='modanswers'>\n";
	echo "\t\t\t\t</form>\n";
	echo "\t\t\t\t<tr><form action='labels.php' method='post'><td colspan='2'></td>";
	echo "\t\t\t\t<td align='left'><input $btstyle type='submit' name='method' value='"._AL_FIXSORT."'></td><td></td>\n";
	echo "\t\t\t\t\t<input type='hidden' name='lid' value='$lid'>\n";
	echo "\t\t\t\t\t<input type='hidden' name='action' value='modanswers'>\n";
	echo "\t\t\t\t</form></tr>\n";
	echo "\t\t\t</table>\n";
	echo "\t\t\t<table height='1'><tr><td></td></tr></table>\n";
	}


//CLOSE OFF
echo "\t</td>\n"; //END OF MAIN CELL
echo "\t<td>"; //START OF HELP CELL
//help
echo "\t</td></tr>\n";
echo "</table>\n";

echo htmlfooter("instructions.html#labels", "Using PHPSurveyor's Labels Editor");

//************************FUNCTIONS********************************
function updateset($lid)
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$_POST['label_name'] = addcslashes($_POST['label_name'], "'");
		}
	$query = "UPDATE labelsets SET label_name='{$_POST['label_name']}' WHERE lid=$lid";
	if (!$result = mysql_query($query))
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_UPDATESET." - ".$query." - ".mysql_error()."\")\n //-->\n</script>\n";
		}
	}
function delset($lid)
	{
	//CHECK THAT THERE ARE NO QUESTIONS THAT RELY ON THIS LID
	$query = "SELECT qid FROM questions WHERE lid=$lid";
	$result = mysql_query($query);
	$count=mysql_num_rows($result);
	if ($count > 0)
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_DELSET." \")\n //-->\n</script>\n";
		return false;
		}
	else //There are no dependencies. We can delete this safely
		{
		$query = "DELETE FROM labels WHERE lid=$lid";
		$result = mysql_query($query);
		$query = "DELETE FROM labelsets WHERE lid=$lid";
		$result = mysql_query($query);
		return true;
		}
	}
function insertset()
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$_POST['label_name'] = addcslashes($_POST['label_name'], "'");
		}
	$query = "INSERT INTO labelsets (lid, label_name) VALUES ('', '{$_POST['label_name']}')";
	if (!$result = mysql_query($query))
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_UPDATESET." - ".$query." - ".mysql_error()."\")\n //-->\n</script>\n";
		}
	}
function modanswers($lid)
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$_POST['title'] = addcslashes($_POST['title'], "'");
		}
	
	switch($_POST['method'])
		{
		case _ADD:
			$query = "INSERT INTO labels (lid, code, title, sortorder) VALUES ($lid, '{$_POST['code']}', '{$_POST['title']}', '{$_POST['sortorder']}')";
			if (!$result = mysql_query($query))
				{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_INSERTANS." - ".$query." - ".mysql_error()."\")\n //-->\n</script>\n";
				}
			break;
		case _AL_SAVE:
			$query = "UPDATE labels SET code='{$_POST['code']}', title='{$_POST['title']}', sortorder='{$_POST['sortorder']}' WHERE lid=$lid AND code='{$_POST['old_code']}'";
			if (!$result = mysql_query($query))
				{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_EDITANS." - ".$query." - ".mysql_error()."\")\n //-->\n</script>\n";
				}
			break;
		case _AL_UP:
			$newsortorder=sprintf("%05d", $_POST['sortorder']-1);
			$replacesortorder=$newsortorder;
			$newreplacesortorder=sprintf("%05d", $_POST['sortorder']);
			$cdquery = "UPDATE labels SET sortorder='PEND' WHERE lid=$lid AND sortorder='$newsortorder'";
			$cdresult=mysql_query($cdquery) or die(mysql_error());
			$cdquery = "UPDATE labels SET sortorder='$newsortorder' WHERE lid=$lid AND sortorder='$newreplacesortorder'";
			$cdresult=mysql_query($cdquery) or die(mysql_error());
			$cdquery = "UPDATE labels SET sortorder='$newreplacesortorder' WHERE lid=$lid AND sortorder='PEND'";
			$cdresult=mysql_query($cdquery) or die(mysql_error());
			break;
		case _AL_DN:
			$newsortorder=sprintf("%05d", $_POST['sortorder']+1);
			$replacesortorder=$newsortorder;
			$newreplacesortorder=sprintf("%05d", $_POST['sortorder']);
			$newreplace2=sprintf("%05d", $_POST['sortorder']);
			$cdquery = "UPDATE labels SET sortorder='PEND' WHERE lid=$lid AND sortorder='$newsortorder'";
			$cdresult=mysql_query($cdquery) or die(mysql_error());
			$cdquery = "UPDATE labels SET sortorder='$newsortorder' WHERE lid=$lid AND sortorder='{$_POST['sortorder']}'";
			$cdresult=mysql_query($cdquery) or die(mysql_error());
			$cdquery = "UPDATE labels SET sortorder='$newreplacesortorder' WHERE lid=$lid AND sortorder='PEND'";
			$cdresult=mysql_query($cdquery) or die(mysql_error());
			break;
		case _AL_DEL:
			$query = "DELETE FROM labels WHERE lid=$lid AND code='{$_POST['old_code']}'";
			if (!$result = mysql_query($query))
				{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_DELANS." - ".$query." - ".mysql_error()."\")\n //-->\n</script>\n";
				}
			break;
		case _AL_FIXSORT:
			fixorder($lid);
			break;
		}
	}
function fixorder($lid) //Function rewrites the sortorder for a group of answers
	{
	$query = "SELECT * FROM labels WHERE lid=$lid ORDER BY sortorder, code";
	$result = mysql_query($query);
	$position=0;
	while ($row=mysql_fetch_array($result))
		{
		$position=sprintf("%05d", $position);
		if (phpversion() >= "4.3.0")
			{
			$title = mysql_real_escape_string($row['title']);
			}
		else
			{
			$title = mysql_escape_string($row['title']);
			}
		$query2="UPDATE labels SET sortorder='$position' WHERE lid={$row['lid']} AND code='{$row['code']}' AND title='$title'";
		$result2=mysql_query($query2) or die ("Couldn't update sortorder<br />$query2<br />".mysql_error());
		$position++;
		}
	}

?>