<?php
/*
#############################################################
# >>> PHPSurveyor                                           #
#############################################################
# > Author:  Jason Cleeland                                 #
# > E-mail:  jason@cleeland.org                             #
# > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
# >          CARLTON SOUTH 3053, AUSTRALIA                  #
# > Date:    19 April 2003                                  #
#                                                           #
# This set of scripts allows you to develop, publish and    #
# perform data-entry on surveys.                            #
#############################################################
#                                                           #
#    Copyright (C) 2003  Jason Cleeland                     #
#                                                           #
# This program is free software; you can redistribute       #
# it and/or modify it under the terms of the GNU General    #
# Public License as published by the Free Software          #
# Foundation; either version 2 of the License, or (at your  #
# option) any later version.                                #
#                                                           #
# This program is distributed in the hope that it will be   #
# useful, but WITHOUT ANY WARRANTY; without even the        #
# implied warranty of MERCHANTABILITY or FITNESS FOR A      #
# PARTICULAR PURPOSE.  See the GNU General Public License   #
# for more details.                                         #
#                                                           #
# You should have received a copy of the GNU General        #
# Public License along with this program; if not, write to  #
# the Free Software Foundation, Inc., 59 Temple Place -     #
# Suite 330, Boston, MA  02111-1307, USA.                   #
#############################################################
*/

require_once(dirname(__FILE__).'/../config.php');

if (!isset($action)) {$action=returnglobal('action');}
if (!isset($lid)) {$lid=returnglobal('lid');}

sendcacheheaders();

//DO DATABASE UPDATESTUFF
if ($action == "updateset") {updateset($lid);}
if ($action == "insertset") {$lid=insertset();}
if ($action == "modanswers") {modanswers($lid);}
if ($action == "delset") {if (delset($lid)) {$lid=0;}}

echo $htmlheader;

if ($action == "importlabels")
{
	include("importlabel.php");
	exit;
}

echo "<script type='text/javascript'>\n"
."<!--\n"
."\tfunction showhelp(action)\n"
."\t\t{\n"
."\t\tvar name='help';\n"
."\t\tif (action == \"hide\")\n"
."\t\t\t{\n"
."\t\t\tdocument.getElementById(name).style.display='none';\n"
."\t\t\t}\n"
."\t\telse if (action == \"show\")\n"
."\t\t\t{\n"
."\t\t\tdocument.getElementById(name).style.display='';\n"
."\t\t\t}\n"
."\t\t}\n"
."-->\n"
."</script>\n";
echo "<table width='100%' border='0' cellpadding='0' cellspacing='0' >\n"
."\t<tr>\n"
."\t\t<td valign='top' align='center' bgcolor='#BBBBBB'>\n"
."\t\t\t<table cellspacing='1'><tr><td></td></tr></table>\n"
."\t\t\t<table width='99%' align='center' style='border: 1px solid #555555' "
."cellpadding='1' cellspacing='0'>\n"
."\t\t\t\t<tr bgcolor='#555555'><td height='4' colspan='2'>"
."<font size='1' face='verdana' color='white'><strong>"
._("Label Sets Administration")."</strong></font></td></tr>\n"
."\t\t\t\t<tr bgcolor='#999999'>\n"
."\t\t\t\t\t<td>\n"
."\t\t\t\t\t<a href='$scriptname' onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'". _("Return to Survey Administration")."');return false\">" .
		"<img name='Administration' src='$imagefiles/home.png' title='' alt='' align='left' ></a>"
."\t\t\t\t\t<img src='$imagefiles/blank.gif' width='11' height='20' border='0' hspace='0' align='left' alt=''>\n"
."\t\t\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt=''>\n"
."\t\t\t\t\t<img src='$imagefiles/blank.gif' width='60' height='20' border='0' hspace='0' align='left' alt=''>\n"
."\t\t\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt=''>\n"
."\t\t\t\t\t</td>\n"
."\t\t\t\t\t<td align='right' width='620'>\n"
."\t\t\t\t\t<input type='image' src='$imagefiles/showhelp.png' title='"
._("Show Help")."' alt='"._("Show Help")."' align='right'  "
."onClick=\"showhelp('show')\">\n"
."\t\t\t\t\t<img src='$imagefiles/blank.gif' width='42' height='20' align='right' hspace='0' border='0'  alt=''>\n"
."\t\t\t\t\t<img src='$imagefiles/seperator.gif' align='right' hspace='0' border='0' alt=''>\n"
."\t\t\t\t\t<input type='image' src='$imagefiles/add.png' align='right' title='"
._("Add new label set")."' alt='"._("Add new label set")."' onClick=\"window.open('labels.php?action=newset', '_top')\">\n"
."\t\t\t\t\t$setfont<font size='1'><strong>"
._("Labelsets").":</strong> "
."\t\t\t\t\t<select style='font-size: 9; font-family: verdana; color: #333333; background: SILVER; width: 160' "
."onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
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
if (!isset($lid) || $lid<1) {echo " selected";}
echo ">"._("Please Choose...")."</option>\n";

echo "\t\t\t\t\t</select></font></font>\n"
."\t\t\t\t\t</td>\n"
."\t\t\t\t</tr>\n"
."\t\t\t</table>\n"
."\t\t<table ><tr><td></td></tr></table>\n";

//NEW SET
if ($action == "newset" || $action == "editset")
{
	if ($action == "editset")
	{
		$query = "SELECT * FROM {$dbprefix}labelsets WHERE lid=$lid";
		$result=db_execute_assoc($query);
		while ($row=$result->FetchRow()) {$lbname=$row['label_name']; $lblid=$row['lid'];}
	}
	echo "\t\t<form style='margin-bottom:0;' method='post' action='labels.php'>\n"
	."\t\t<table width='100%' bgcolor='#DDDDDD'>\n"
	."\t\t\t<tr bgcolor='black'>\n"
	."\t\t\t\t<td colspan='2' align='center'>$setfont<font color='white'><strong>\n"
	."\t\t\t\t<input type='image' src='$imagefiles/close.gif' align='right' "
	."onClick=\"window.open('labels.php?lid=$lid', '_top')\">\n";
	if ($action == "newset") {echo _("Create New Label Set");}
	else {echo _("Edit Label Set");}
	echo "\t\t\t\t</strong></font></font></td>\n"
	."\t\t\t</tr>\n"
	."\t\t\t<tr>\n"
	."\t\t\t\t<td align='right' width='15%'>\n"
	."\t\t\t\t\t$setfont<strong>"._("Set Name").":</strong></font>"
	."\t\t\t\t</td>\n"
	."\t\t\t\t<td>\n"
	."\t\t\t\t\t<input type='text' name='label_name' value='";
	if (isset($lbname)) {echo $lbname;}
	echo "'>\n"
	."\t\t\t\t</td>\n"
	."\t\t\t</tr>\n"
	."\t\t\t<tr>\n"
	."\t\t\t\t<td></td>\n"
	."\t\t\t\t<td>\n"
	."\t\t\t\t<input type='submit' value='";
	if ($action == "newset") {echo _("Add");}
	else {echo _("Update");}
	echo "'>\n"
	."\t\t<input type='hidden' name='action' value='";
	if ($action == "newset") {echo "insertset";}
	else {echo "updateset";}
	echo "'>\n";
	if ($action == "editset") {echo "\t\t<input type='hidden' name='lid' value='$lblid'>\n";}
	echo "\t\t</td>\n"
	."\t</tr>\n";
	echo "\t\t</table></form>\n";
	if ($action == "newset")
	{
		echo "\t\t<form enctype='multipart/form-data' name='importlabels' action='labels.php' method='post'>\n"
		."\t\t<table width='100%' bgcolor='#DDDDDD'>\n"
		."\t\t\t<tr><td colspan='2' align='center'>\n"
		."\t\t\t\t$setfont<strong>OR</strong></font>\n"
		."\t\t\t</td></tr>\n"
		."\t\t\t<tr bgcolor='black'>\n"
		."\t\t\t\t<td colspan='2' align='center'>$setfont<font color='white'><strong>\n"
		."\t\t\t\t"._("Import Label Set")."\n"
		."\t\t\t\t</strong></font></font></td>\n"
		."\t\t\t</tr>\n"
		."\t\t\t<tr>\n"
		."\t\t\t\t<td align='right'>$setfont<strong>"
		._("Select SQL File:")."</strong></font></td>\n"
		."\t\t<td><input name=\"the_file\" type=\"file\" size=\"35\">"
		."</td></tr>\n"
		."\t<tr><td></td><td><input type='submit' value='"._("Import Label Set")."'>\n"
		."\t<input type='hidden' name='action' value='importlabels'></TD>\n"
		."\t</tr></table></form>\n";
	}
}
//SET SELECTED
if (isset($lid) && ($action != "editset") && $lid)
{
	//CHECK TO SEE IF ANY ACTIVE SURVEYS ARE USING THIS LABELSET (Don't let it be changed if this is the case)
	$query = "SELECT {$dbprefix}surveys.short_title FROM {$dbprefix}questions, {$dbprefix}surveys WHERE {$dbprefix}questions.sid={$dbprefix}surveys.sid AND {$dbprefix}questions.lid=$lid AND {$dbprefix}surveys.active='Y'";
	$result = db_execute_assoc($query);
	$activeuse=$result->RecordCount();
	while ($row=$result->FetchRow()) {$activesurveys[]=$row['short_title'];}
	//NOW ALSO COUNT UP HOW MANY QUESTIONS ARE USING THIS LABELSET, TO GIVE WARNING ABOUT CHANGES
	$query = "SELECT * FROM {$dbprefix}questions WHERE type IN ('F','H') AND lid=$lid";
	$result = db_execute_assoc($query);
	$totaluse=$result->RecordCount();
	while($row=$result->FetchRow())
	{
		$qidarray[]=array("url"=>"$scriptname?sid=".$row['sid']."&amp;gid=".$row['gid']."&amp;qid=".$row['qid'], "title"=>"QID: ".$row['qid']);
	} // while
	//NOW GET THE ANSWERS AND DISPLAY THEM
	$query = "SELECT * FROM {$dbprefix}labelsets WHERE lid=$lid";
	$result = db_execute_assoc($query);
	while ($row=$result->FetchRow())
	{
		echo "\t\t\t<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t\t\t\t<tr bgcolor='#555555'><td height='4' colspan='2'>"
		."<font size='1' face='verdana' color='white'><strong>"
		._("Label Set").":</strong> {$row['label_name']}</font></td></tr>\n"
		."\t\t\t\t<tr bgcolor='#999999'>\n"
		."\t\t\t\t\t<td>\n"
		."\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' title='"
		._("Close Window")."' align='right' "
		."onClick=\"window.open('labels.php', '_top')\">\n"
		."\t\t\t\t\t<img src='$imagefiles/blank.gif' width='31' height='20' border='0' hspace='0' align='left' alt=''>\n"
		."\t\t\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt=''>\n"
		."\t\t\t\t\t<img src='$imagefiles/blank.gif' width='60' height='20' border='0' hspace='0' align='left' alt=''>\n"
		."\t\t\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt=''>\n"
		."\t\t\t\t\t<a href='labels.php?action=editset&amp;lid=$lid' onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'"._("Edit label set")."');return false\">" .
		"<img name='DeleteTokensButton' src='$imagefiles/edit.png' title='' align='left' ></a>" 
		."\t\t\t\t\t<a href='labels.php?action=delset&amp;lid=$lid' onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'"._("Delete label set")."');return false\">"
		."<img src='$imagefiles/delete.png' border='0' alt='' title='' align='left' onClick=\"return confirm('"._("Are you sure?")."')\"></a>\n"
		."\t\t\t\t\t<a href='dumplabel.php?lid=$lid' onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'"._("Export Label Set")."');return false\">" .
				"<img src='$imagefiles/exportsql.png' alt='"._("Export Label Set")."' title='' align='left'></a>" 
		."\t\t\t\t\t</td>\n"
		."\t\t\t\t</tr>\n"
		."\t\t\t</table>\n";
	}
	//LABEL ANSWERS
	$query = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid ORDER BY sortorder, code";
	$result = db_execute_assoc($query) or die($connect->ErrorMsg());
	$labelcount = $result->RecordCount();
	echo "\t\t\t<table width='99%' align='center' style='border: solid; border-width: 1px; border-color: #555555' cellspacing='0'><thead align='center'>\n"
	."\t\t\t\t<tr bgcolor='#555555' >\n"
	."\t\t\t\t\t<td colspan='4'><strong><font size='1' face='verdana' color='white'>\n"
	._("Labels")
	."\t\t\t\t\t</font></strong></td>\n"
	."\t\t\t\t</tr>\n"
	."\t\t\t\t<tr bgcolor='#BBBBBB'>\n"
	."\t\t\t\t\t<td width='10%'><strong><font size='1' face='verdana'>\n"
	._("Code")
	."\t\t\t\t\t</font></strong></td>\n"
	."\t\t\t\t\t<td width='50%'><strong><font size='1' face='verdana'>\n"
	._("Title")
	."\t\t\t\t\t</font></strong></td>\n"
	."\t\t\t\t\t<td width='20%'><strong><font size='1' face='verdana'>\n"
	._("Action")
	."\t\t\t\t\t</font></strong></td>\n"
	."\t\t\t\t\t<td width='20%'><strong><font size='1' face='verdana'>\n"
	._("Order")
	."\t\t\t\t\t</font></strong></td>\n"
	."\t\t\t\t</tr></thead>\n"
	."\t\t\t\t\n";
	$position=0;
	while ($row=$result->FetchRow())
	{
		echo "\t\t\t<tr><td colspan='4'><form method='post' action='labels.php'>\n"
		."\t\t\t<table width='100%' style='border: solid; border-width: 0px; border-color: #555555' cellspacing='0'><tbody align='center'>\n"
		."\t\t\t\t<tr><td width='10%'>\n";
		if ($activeuse > 0)
		{
			echo "\t\t\t\t\t$setfont{$row['code']}</font>"
			."<input type='hidden' name='code' value=\"{$row['code']}\">\n";
		}
		else
		{
			echo "\t\t\t\t\t<input type='text' name='code' size='7' value=\"{$row['code']}\">\n";
		}
		echo "\t\t\t\t\t</td>\n"
		."\t\t\t\t\t<td width='50%'>\n"
		."\t\t\t\t\t<input type='text' name='title' size='60' value=\"{$row['title']}\">\n"
		."\t\t\t\t\t</td>\n"
		."\t\t\t\t\t<td width='20%'>\n"
		."\t\t\t\t\t<input type='submit' name='method' value='"._("Save")."' />\n";
		if ($activeuse == 0)
		{
			echo "\t\t\t\t\t<input type='submit' name='method' value='"._("Del")."' />\n";
		}
		echo "\t\t\t\t\t</td>\n"
		."\t\t\t\t\t<td>\n";
		if ($position > 0)
		{
			echo "\t\t\t\t\t<input type='submit' name='method' value='"._("Up")."' />\n";
		}
		else {echo "<img src='$imagefiles/blank.gif' width='21' height='5' align='left' alt=''>";}
		if ($position < $labelcount-1)
		{
			echo "\t\t\t\t\t<input type='submit' name='method' value='"._("Dn")."' />\n";
		}
		echo "\t\t\t\t\t</td></tbody></table>\n"
		."\t\t\t\t<input type='hidden' name='sortorder' value='{$row['sortorder']}'>\n"
		."\t\t\t\t<input type='hidden' name='old_title' value='{$row['title']}'>\n"
		."\t\t\t\t<input type='hidden' name='old_code' value='{$row['code']}'>\n"
		."\t\t\t\t<input type='hidden' name='lid' value='$lid'>\n"
		."\t\t\t\t<input type='hidden' name='action' value='modanswers'>\n"
		."\t\t\t\t</form>\n</td></tr>";
		$position++;
	}
	$position=sprintf("%05d", $position);
	if ($activeuse == 0)
	{
		echo "\t\t\t\t<tr><td colspan='4'>\n"
		."\t\t\t\t<form style='margin-bottom:0;' method='post' action='labels.php'>\n"
		."\t\t\t\t<table width='100%' style='border: solid; border-width: 0px; border-color: #555555' cellspacing='0'><tbody align='center'>\n"
		."\t\t\t\t\t<tr><td width='10%'>\n"
		."\t\t\t\t\t<input type='text' name='code' size='7' id='addnewlabelcode'>\n"
		."\t\t\t\t\t</td>\n"
		."\t\t\t\t\t<td width='50%'>\n"
		."\t\t\t\t\t<input type='text' name='title' size='60'>\n"
		."\t\t\t\t\t</td>\n"
		."\t\t\t\t\t<td width='20%'>\n"
		."\t\t\t\t\t<input type='submit' name='method' value='"._("Add")."'>\n"
		."\t\t\t\t\t</td>\n"
		."\t\t\t\t\t<td>\n";
		echo "<script type='text/javascript' language='javascript'>\n"
		."<!--\n"
		."document.getElementById('addnewlabelcode').focus();\n"
		."//-->\n"
		."</script>\n"
		."\t\t\t\t\t<input type='hidden' name='sortorder' value='$position'>\n"
		."\t\t\t\t\t<input type='hidden' name='lid' value='$lid'>\n"
		."\t\t\t\t\t<input type='hidden' name='action' value='modanswers'>\n"
		."\t\t\t\t\t</td>\n"
		."\t\t\t\t</tr>\n"
		."\t\t\t\t</tbody></table></form>\n";

	}
	else
	{
		echo "\t\t\t\t<tr>\n"
		."\t\t\t\t\t<td colspan='4' align='center'>\n"
		."\t\t\t\t\t\t$setfont<font color='red' size='1'><i><strong>"
		._("Warning")."</strong>: "._("You cannot change codes, add or delete entries in this label set because it is being used by an active survey.")."</i></font></font>\n"
		."\t\t\t\t\t</td>\n"
		."\t\t\t\t</tr>\n";
	}
	echo "\t\t\t\t<tr><td colspan='4'><form style='margin-bottom:0;' action='labels.php' method='post'>"
	."\t\t\t\t<table width='100%' style='border: solid; border-width: 0px; border-color: #555555' cellspacing='0'><tbody align='center'>\n"
	."\t\t\t\t\t<tr><td width='80%'></td>"
	."\t\t\t\t<td></td><td><input type='submit' name='method' value='"
	._("Fix Sort")."'></td>\n"
	."\t\t\t\t</tr></tbody></table>"
	."\t\t\t\t\t<input type='hidden' name='lid' value='$lid'>\n"
	."\t\t\t\t\t<input type='hidden' name='action' value='modanswers'>\n"
	."\t\t\t\t\t</form>\n";
	if ($totaluse > 0 && $activeuse == 0) //If there are surveys using this labelset, but none are active warn about modifying
	{
		echo "\t\t\t\t<tr>\n"
		."\t\t\t\t\t<td colspan='4' align='center'>\n"
		."\t\t\t\t\t\t$setfont<font color='red' size='1'><i><strong>"
		._("Warning")."</strong>: "._("Some surveys currently use this label set. Modifying the codes, adding or deleting entries to this label set may produce undesired results in other surveys.")."</i><br />";
		foreach ($qidarray as $qd) {echo "[<a href='".$qd['url']."'>".$qd['title']."</a>] ";}
		echo "</font></font>\n"
		."\t\t\t\t\t</td>\n"
		."\t\t\t\t</tr>\n";
	}
	echo "\t\t\t</table>\n";
}


//CLOSE OFF
echo "\t</td>\n"; //END OF MAIN CELL
helpscreen();
echo "</table>\n";

echo getAdminFooter("$langdir/instructions.html#labels", "Using PHPSurveyor`s Labels Editor");

//************************FUNCTIONS********************************
function updateset($lid)
{
	global $dbprefix, $connect;
	if (get_magic_quotes_gpc() == "0")
	{
		$_POST['label_name'] = addcslashes($_POST['label_name'], "'");
	}
	$query = "UPDATE {$dbprefix}labelsets SET label_name='{$_POST['label_name']}' WHERE lid=$lid";
	if (!$result = $connect->Execute($query))
	{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Update of Label Set failed")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
	}
}
function delset($lid)
{
	global $dbprefix, $connect;
	//CHECK THAT THERE ARE NO QUESTIONS THAT RELY ON THIS LID
	$query = "SELECT qid FROM {$dbprefix}questions WHERE type IN ('F','H') AND lid=$lid";
	$result = $connect->Execute($query) or die("Error");
	$count = $result->RecordCount();
	if ($count > 0)
	{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Couldn't Delete Label Set - There are questions that rely on this. You must delete these questions first.")."\")\n //-->\n</script>\n";
		return false;
	}
	else //There are no dependencies. We can delete this safely
	{
		$query = "DELETE FROM {$dbprefix}labels WHERE lid=$lid";
		$result = $connect->Execute($query);
		$query = "DELETE FROM {$dbprefix}labelsets WHERE lid=$lid";
		$result = $connect->Execute($query);
		return true;
	}
}
function insertset()
{
	global $dbprefix, $connect;
	if (get_magic_quotes_gpc() == "0")
	{
		$_POST['label_name'] = addcslashes($_POST['label_name'], "'");
	}
	$query = "INSERT INTO {$dbprefix}labelsets (label_name) VALUES ('{$_POST['label_name']}')";
	if (!$result = $connect->Execute($query))
	{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Update of Label Set failed")." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
	}
	else
	{
		return $connect->Insert_ID();
	}
}
function modanswers($lid)
{
	global $dbprefix, $connect;
	if (get_magic_quotes_gpc() == "0")
	{
		if (isset($_POST['title']))
		{
			$_POST['title'] = addcslashes($_POST['title'], "'");
		}
	}
	if (!isset($_POST['method'])) {
		$_POST['method'] = _("Save");
	}
	switch($_POST['method'])
	{
		case _("Add"):
		if (isset($_POST['code']) && $_POST['code']!='')
		{
			$query = "INSERT INTO {$dbprefix}labels (lid, code, title, sortorder) VALUES ($lid, '{$_POST['code']}', '{$_POST['title']}', '{$_POST['sortorder']}')";
			if (!$result = $connect->Execute($query))
			{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_INSERTANS." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
			}
		}
		break;
		case _("Save"):
		$query = "UPDATE {$dbprefix}labels SET code='{$_POST['code']}', title='{$_POST['title']}', sortorder='{$_POST['sortorder']}' WHERE lid=$lid AND code='{$_POST['old_code']}'";
		if (!$result = $connect->Execute($query))
		{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_EDITANS." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
		}
		break;
		case _("Up"):
		$newsortorder=sprintf("%05d", $_POST['sortorder']-1);
		$replacesortorder=$newsortorder;
		$newreplacesortorder=sprintf("%05d", $_POST['sortorder']);
		$cdquery = "UPDATE {$dbprefix}labels SET sortorder='PEND' WHERE lid=$lid AND sortorder='$newsortorder'";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE {$dbprefix}labels SET sortorder='$newsortorder' WHERE lid=$lid AND sortorder='$newreplacesortorder'";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE {$dbprefix}labels SET sortorder='$newreplacesortorder' WHERE lid=$lid AND sortorder='PEND'";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		break;
		case _("Dn"):
		$newsortorder=sprintf("%05d", $_POST['sortorder']+1);
		$replacesortorder=$newsortorder;
		$newreplacesortorder=sprintf("%05d", $_POST['sortorder']);
		$newreplace2=sprintf("%05d", $_POST['sortorder']);
		$cdquery = "UPDATE {$dbprefix}labels SET sortorder='PEND' WHERE lid=$lid AND sortorder='$newsortorder'";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE {$dbprefix}labels SET sortorder='$newsortorder' WHERE lid=$lid AND sortorder='{$_POST['sortorder']}'";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE {$dbprefix}labels SET sortorder='$newreplacesortorder' WHERE lid=$lid AND sortorder='PEND'";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		break;
		case _("Del"):
		$query = "DELETE FROM {$dbprefix}labels WHERE lid=$lid AND code='{$_POST['old_code']}'";
		if (!$result = $connect->Execute($query))
		{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._LB_FAIL_DELANS." - ".$query." - ".$connect->ErrorMsg()."\")\n //-->\n</script>\n";
		}
		break;
		case _("Fix Sort"):
		fixorder($lid);
		break;
	}
}
function fixorder($lid) //Function rewrites the sortorder for a group of answers
{
	global $dbprefix, $connect;
	$query = "SELECT lid, code, title FROM {$dbprefix}labels WHERE lid=? ORDER BY sortorder, code";
	$result = db_execute_num($query, $lid);
	$position=0;
	while ($row=$result->FetchRow())
	{
		$position=sprintf("%05d", $position);
		$query2="UPDATE {$dbprefix}labels SET sortorder='$position' WHERE lid=? AND code=? AND title=?";
		$result2=$connect->Execute($query2, $row[0], $row[1], $row[2]) or die ("Couldn't update sortorder<br />$query2<br />".$connect->ErrorMsg());
		$position++;
	}
}

function helpscreen()
{
	global $homeurl, $langdir, $imagefiles;
	global $lid, $action;
	echo "\t\t<td id='help' width='150' valign='top' style='display: none' bgcolor='#CCCCCC'>\n";
	echo "\t\t\t<table width='100%'><tr><td><table width='100%' align='center' cellspacing='0'>\n";
	echo "\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t<td bgcolor='#555555' height='8'>\n";
	echo "\t\t\t\t\t\t<font color='white' size='1'><strong>"._("Help")."</strong></font>\n";
	echo "\t\t\t\t\t</td>\n";
	echo "\t\t\t\t</tr>\n";
	echo "\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t<td align='center' bgcolor='#AAAAAA' style='border-style: solid; border-width: 1; border-color: #555555'>\n";
	echo "\t\t\t\t\t\t<img src='$imagefiles/blank.gif' width='20' hspace='0' border='0' align='left' alt=''>\n";
	echo "\t\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' align='right' onClick=\"showhelp('hide')\">\n";
	echo "\t\t\t\t\t</td>\n";
	echo "\t\t\t\t</tr>\n";
	echo "\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t<td bgcolor='silver' height='100%' style='border-style: solid; border-width: 1; border-color: #333333'>\n";
	//determine which help document to show
	if (!$lid)
	{
		$helpdoc = "$langdir/labelsets.html";
	}
	elseif ($lid)
	{
		$helpdoc = "$langdir/labels.html";
	}
	echo "\t\t\t\t\t\t<iframe width='150' height='400' src='$helpdoc' marginwidth='2' marginheight='2'>\n";
	echo "\t\t\t\t\t\t</iframe>\n";
	echo "\t\t\t\t\t</td>";
	echo "\t\t\t\t</tr>\n";
	echo "\t\t\t</table></td></tr></table>\n";
	echo "\t\t</td>\n";
}
?>
