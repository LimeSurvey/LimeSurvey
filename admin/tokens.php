<?php
/*
#############################################################
# >>> PHPSurveyor  									     	#
#############################################################
# > Author:  Jason Cleeland									#
# > E-mail:  jason@cleeland.org								#
# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
# >          CARLTON SOUTH 3053, AUSTRALIA					#
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

# TOKENS FILE

require_once(dirname(__FILE__).'/../config.php');
if ($enableLdap) 
    {
	require_once(dirname(__FILE__).'/../config-ldap.php');
    }
//if (!isset($action)) {$action=returnglobal('action');}
//if (!isset($subaction)) {$subaction=returnglobal('subaction');}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($order)) {$order=returnglobal('order');}
if (!isset($limit)) {$limit=returnglobal('limit');}
if (!isset($start)) {$start=returnglobal('start');}
if (!isset($searchstring)) {$searchstring=returnglobal('searchstring');}

include_once("login_check.php");
$tokenoutput='';

if ($subaction == "export") //EXPORT FEATURE SUBMITTED BY PIETERJAN HEYSE
{

   	header("Content-Disposition: attachment; filename=tokens_".$surveyid.".csv");
   	header("Content-type: text/comma-separated-values; charset=UTF-8");
    Header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

    $bquery = "SELECT * FROM ".db_table_name("tokens_$surveyid");
	$bquery .= " ORDER BY tid";

	$bresult = db_execute_assoc($bquery) or die ("$bquery<br />".htmlspecialchars($connect->ErrorMsg()));
	$bfieldcount=$bresult->FieldCount();

	$tokenoutput .= "Tid, Firstname, Lastname, Email, Token , Language, Attribute1, Attribute2, mpid\n";
	while ($brow = $bresult->FetchRow())
	{
		$tokenoutput .= trim($brow['tid']).",";
		$tokenoutput .= trim($brow['firstname']).",";
		$tokenoutput .= trim($brow['lastname']).",";
		$tokenoutput .= trim($brow['email']).",";
		$tokenoutput .= trim($brow['token']).",";
		$tokenoutput .= trim($brow['language']);
		if($bfieldcount > 8)
		{
			$tokenoutput .= ",";
			$tokenoutput .= trim($brow['attribute_1']).",";
			$tokenoutput .= trim($brow['attribute_2']).",";
			$tokenoutput .= trim($brow['mpid']);
		}
		$tokenoutput .= "\n";
	}
	echo $tokenoutput;
	exit;
}

if ($subaction == "delete" ) {$_SESSION['metaHeader']="<meta http-equiv=\"refresh\" content=\"1;URL={$scriptname}?action=tokens&amp;subaction=browse&amp;sid={$_GET['sid']}&amp;start=$start&amp;limit=$limit&amp;order=$order\" />";}
//Show Help
$tokenoutput .= "<script type='text/javascript'>\n"
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

$tokenoutput .= "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n"
."\t<tr>\n"
."\t\t<td valign='top' align='center' bgcolor='#BBBBBB'>\n"
."\t\t<table><tr><td></td></tr></table>\n";

$tokenoutput .= "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";

// MAKE SURE THAT THERE IS A SID
if (!isset($surveyid) || !$surveyid)
{
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Token Control").":</strong></font></td></tr>\n"
	."\t<tr><td align='center'><br /><font color='red'><strong>"
	.$clang->gT("Error")."</strong></font><br />".$clang->gT("You have not selected a survey")."<br /><br />"
	."<input type='submit' value='"
	.$clang->gT("Main Admin Screen")."' onClick=\"window.open('$scriptname', '_top')\"><br /><br /></td></tr>\n"
	."</table>\n"
	."</body>\n</html>";
	return;
}

// MAKE SURE THAT THE SURVEY EXISTS
$chquery = "SELECT * FROM ".db_table_name('surveys')." as a inner join ".db_table_name('surveys_languagesettings')." as b on (b.surveyls_survey_id=a.sid and b.surveyls_language=a.language) WHERE a.sid=$surveyid";

$chresult=db_execute_assoc($chquery);
$chcount=$chresult->RecordCount();
if (!$chcount)
{
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Token Control").":</strong></font></td></tr>\n"
	."\t<tr><td align='center'><br /><font color='red'><strong>"
	.$clang->gT("Error")."</strong></font><br />".$clang->gT("The survey you selected does not exist")
	."<br /><br />\n\t<input type='submit' value='"
	.$clang->gT("Main Admin Screen")."' onClick=\"window.open('$scriptname', '_top')\"><br /><br /></td></tr>\n"
	."</table>\n"
	."</body>\n</html>";
	return;
}
// A survey DOES exist
while ($chrow = $chresult->FetchRow())
{
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Token Control").":</strong> "
	."<font color='silver'>{$chrow['surveyls_title']}</font></font></td></tr>\n";
	$surveyprivate = $chrow['private'];
}

// CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
$tkquery = "SELECT * FROM ".db_table_name("tokens_$surveyid");
if (!$tkresult = $connect->Execute($tkquery)) //If the query fails, assume no tokens table exists
{
	if (isset($_GET['createtable']) && $_GET['createtable']=="Y")
	{
		$createtokentable = "CREATE TABLE ".db_table_name("tokens_$surveyid")." (\n"
		. "tid int NOT NULL auto_increment,\n "
		. "firstname varchar(40) NULL,\n "
		. "lastname varchar(40) NULL,\n "
		. "email varchar(100) NULL,\n "
		. "token varchar(10) NULL,\n "
		. "language varchar(2) NULL,\n "
		. "sent varchar(17) NULL DEFAULT 'N',\n "
		. "completed varchar(15) NULL DEFAULT 'N',\n "

		. "attribute_1 varchar(100) NULL,\n"
		. "attribute_2 varchar(100) NULL,\n"
		. "mpid int NULL,\n"
		. "PRIMARY KEY (tid),\n"
		. "INDEX (token)) TYPE=MyISAM;";
		$ctresult = $connect->Execute($createtokentable) or die ("Completely mucked up<br />$createtokentable<br /><br />".htmlspecialchars($connect->ErrorMsg()));
		$tokenoutput .= "\t<tr>\n"
		."\t\t<td align='center'>\n"
		."\t\t\t<br /><br />\n"
		."\t\t\t".$clang->gT("A token table has been created for this survey.")." (\"tokens_$surveyid\")<br /><br />\n"
		."\t\t\t<input type='submit' value='"
		.$clang->gT("Continue")."' onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\">\n"
		."\t\t</td>\n"
		."\t</tr>\n"
		."</table>\n"
		."<table><tr><td></td></tr></table>\n"
		."</td></tr></table>\n";
		return;
	}
	elseif (returnglobal('restoretable') == "Y" && returnglobal('oldtable'))
	{
		$query = "RENAME TABLE ".db_quote_id(returnglobal('oldtable'))." TO ".db_table_name("tokens_$surveyid");
		$result=$connect->Execute($query) or die("Failed Rename!<br />".$query."<br />".htmlspecialchars($connect->ErrorMsg()));
		$tokenoutput .= "\t<tr>\n"
		."\t\t<td align='center'>\n"
		."\t\t\t<br /><br />\n"
		."\t\t\t".$clang->gT("A token table has been created for this survey.")." (\"tokens_$surveyid\")<br /><br />\n"
		."\t\t\t<input type='submit' value='"
		.$clang->gT("Continue")."' onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\">\n"
		."\t\t</td>\n"
		."\t</tr>\n"
		."</table>\n"
		."<table><tr><td></td></tr></table>\n"
		."</td></tr></table>\n";
		return;
	}
	else
	{
		$query="SHOW TABLES LIKE '{$dbprefix}old_tokens_".$surveyid."_%'";
		$result=db_execute_num($query) or die("COuldn't get old table list<br />".$query."<br />".htmlspecialchars($connect->ErrorMsg()));
		$tcount=$result->RecordCount();
		if ($tcount > 0)
		{
			while($rows=$result->FetchRow())
			{
				$oldlist[]=$rows[0];
			}
		}
		$tokenoutput .= "\t<tr>\n"
		."\t\t<td align='center'>\n"
		."\t\t\t<br /><font color='red'><strong>".$clang->gT("Warning")."</strong></font><br />\n"
		."\t\t\t<strong>".$clang->gT("Tokens have not been initialised for this survey.")."</strong><br /><br />\n"
		."\t\t\t".$clang->gT("If you initialise tokens for this survey, the survey will only be accessible to users who have been assigned a token.")
		."\t\t\t<br /><br />\n"
		."\t\t\t".$clang->gT("Do you want to create a tokens table for this survey?");
		$tokenoutput .= "<br /><br />\n";
		$tokenoutput .= "\t\t\t<input type='submit' value='"
		.$clang->gT("Initialise Tokens")."' onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;createtable=Y', '_top')\"><br />\n"
		."\t\t\t<input type='submit' value='"
		.$clang->gT("Main Admin Screen")."' onClick=\"window.open('$homeurl/admin.php?sid=$surveyid', '_top')\"><br /><br />\n";
		if ($tcount>0)
		{
			$tokenoutput .= "<table width='350' border='0' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'><tr>\n"
			."<td bgcolor='#666666'><font color='white' size='1'>Restore Options:\n"
			."</font></td></tr>\n"
			."<tr>\n"
			."<td bgcolor='#DDDDDD' align='center'><form method='post' >\n"
			.$clang->gT("The following old token tables could be restored:")."<br />\n"
			."<select size='4' name='oldtable'>\n";
			foreach($oldlist as $ol)
			{
				$tokenoutput .= "<option>".$ol."</option>\n";
			}
			$tokenoutput .= "</select><br />\n"
			."<input type='submit' value='".html_escape($clang->gT("Restore"))."'>\n"
			."<input type='hidden' name='restoretable' value='Y'>\n"
			."<input type='hidden' name='sid' value='$surveyid'>\n"
			."</form></td>\n"
			."</tr></table>\n";
		}

		$tokenoutput .= "\t\t</td>\n"
		."\t</tr>\n"
		."</table>\n"
		."<table><tr><td></td></tr></table>\n"
		."</td></tr></table>\n";
		return;
	}
}

#Lookup the names of the attributes
$query = "SELECT attribute1, attribute2 FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
$result = db_execute_assoc($query) or die("Couldn't execute query: <br />$query<br />".$connect->ErrorMsg());
$row = $result->FetchRow();
if ($row["attribute1"]) {$attr1_name = $row["attribute1"];} else {$attr1_name=$clang->gT("Attribute 1");}
if ($row["attribute2"]) {$attr2_name = $row["attribute2"];} else {$attr2_name=$clang->gT("Attribute 2");}

// IF WE MADE IT THIS FAR, THEN THERE IS A TOKENS TABLE, SO LETS DEVELOP THE MENU ITEMS
$tokenoutput .= "\t<tr bgcolor='#999999'>\n"
."\t\t<td>\n"
."\t\t\t<a href=\"#\" onClick=\"showhelp('show')\" onmouseout=\"hideTooltip()\""
			."onmouseover=\"showTooltip(event,'".$clang->gT("Show Help")."');return false\">" .
					"<img src='$imagefiles/showhelp.png' title='' align='right'></a>\n"
."\t\t\t<a href=\"#\" onClick=\"window.open('$scriptname?sid=$surveyid', '_top')\" onmouseout=\"hideTooltip()\""
			."onmouseover=\"showTooltip(event,'".$clang->gT("Return to Survey Administration")."');return false\">" .
		"<img name='HomeButton' src='$imagefiles/home.png' align='left' ></a>\n"
."\t\t\t<img src='$imagefiles/blank.gif' alt='' width='11' border='0' hspace='0' align='left'>\n"
."\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
."\t\t\t<a href=\"#\" onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Show summary information")."');return false\" >" .
		"<img name='SummaryButton' src='$imagefiles/summary.png' title='' align='left' ></a>\n"
."\t\t\t<a href=\"#\" onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse', '_top')\" onmouseout=\"hideTooltip()\""
			."onmouseover=\"showTooltip(event,'".$clang->gT("Display Tokens")."');return false\">" .
					"<img name='ViewAllButton' src='$imagefiles/document.png' title='' align='left' ></a>\n"
."\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' border='0' hspace='0' align='left'>\n"
."\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
."\t\t\t<a href=\"#\" onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=addnew', '_top')\" onmouseout=\"hideTooltip()\"" .
		"onmouseover=\"showTooltip(event,'".$clang->gT("Add new token entry")."');return false\">" .
				"<img name='AddNewButton' src='$imagefiles/add.png' title='' align='left' ></a>\n"
."\t\t\t<a href=\"#\" onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=import', '_top')\" onmouseout=\"hideTooltip()\" ".
		"onmouseover=\"showTooltip(event,'".$clang->gT("Import Tokens from CSV File")."');return false\"> <img name='ImportButton' src='$imagefiles/importcsv.png' title='' align='left'></a>"
."\t\t\t<a href=\"#\" onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=importldap', '_top')\" onmouseout=\"hideTooltip()\" ".
                "onmouseover=\"showTooltip(event,'".$clang->gT("Import Tokens from LDAP Query")."');return false\"> <img name='ImportLdapButton' src='$imagefiles/importldap.png' title='' align='left'></a>"
."\t\t\t<a href=\"#\" onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=export', '_top')\" onmouseout=\"hideTooltip()\"" .
	"onmouseover=\"showTooltip(event,'".$clang->gT("Export Tokens to CSV file")."');return false\">".
		"<img name='ExportButton' src='$imagefiles/exportcsv.png' align='left' ></a>\n"
."\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
."\t\t\t<a href=\"#\" onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=email', '_top')\" onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Send email invitation")."');return false\">" .
		"<img name='InviteButton' src='$imagefiles/invite.png' title='' align='left'></a>\n"
."\t\t\t<a href=\"#\" onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=remind', '_top')\" onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Send email reminder")."');return false\">" .
		"<img name='RemindButton' src='$imagefiles/remind.png' title='' align='left' ></a>\n"
."\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
."\t\t\t<a href=\"#\" onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=tokenify', '_top')\" onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Generate Tokens")."');return false\">" .
		"<img name='TokenifyButton' src='$imagefiles/tokenify.png' title='' align='left'></a>\n"
."\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
."\t\t\t<a href=\"#\" onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=kill', '_top')\" onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Drop tokens table")."');return false\">" .
		"<img name='DeleteTokensButton' src='$imagefiles/delete.png' title='' align='left' ></a>\n"
."\t\t</td>\n"
."\t</tr>\n";

// SEE HOW MANY RECORDS ARE IN THE TOKEN TABLE
$tkcount = $tkresult->RecordCount();

$tokenoutput .= "\t<tr><td align='center'><br /></td></tr>\n";
// GIVE SOME INFORMATION ABOUT THE TOKENS
$tokenoutput .= "\t<tr>\n"
."\t\t<td align='center'>\n"
."\t\t\t<table align='center' bgcolor='#DDDDDD' cellpadding='2' style='border: 1px solid #555555'>\n"
."\t\t\t\t<tr>\n"
."\t\t\t\t\t<td align='center'>\n"
."\t\t\t\t\t<strong>".$clang->gT("Total Records in this Token Table").": $tkcount</strong><br />\n";
$tksq = "SELECT count(*) FROM ".db_table_name("tokens_$surveyid")." WHERE token IS NULL OR token=''";
$tksr = db_execute_num($tksq);
while ($tkr = $tksr->FetchRow())
{$tokenoutput .= "\t\t\t\t\t\t".$clang->gT("Total With No Unique Token").": $tkr[0] / $tkcount<br />\n";}

$tksq = "SELECT count(*) FROM ".db_table_name("tokens_$surveyid")." WHERE (sent!='N' and sent<>'')";

$tksr = db_execute_num($tksq);
while ($tkr = $tksr->FetchRow())
{$tokenoutput .= "\t\t\t\t\t\t".$clang->gT("Total Invitations Sent").": $tkr[0] / $tkcount<br />\n";}
$tksq = "SELECT count(*) FROM ".db_table_name("tokens_$surveyid")." WHERE (completed!='N' and completed<>'')";

$tksr = db_execute_num($tksq);
while ($tkr = $tksr->FetchRow())
{$tokenoutput .= "\t\t\t\t\t\t".$clang->gT("Total Surveys Completed").": $tkr[0] / $tkcount\n";}
$tokenoutput .= "\t\t\t\t\t</font></td>\n"
."\t\t\t\t</tr>\n"
."\t\t\t</table>\n"
."\t\t\t<br />\n"
."\t\t</td>\n"
."\t</tr>\n"
."</table>\n"
."<table ><tr><td></td></tr></table>\n";

$tokenoutput .= "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";

#############################################################################################
// NOW FOR VARIOUS ACTIONS:

if ($subaction == "deleteall")
{
	$query="DELETE FROM ".db_table_name("tokens_$surveyid");
	$result=$connect->Execute($query) or die ("Couldn't update sent field<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
	$tokenoutput .= "<tr><td bgcolor='silver' align='center'><strong><font color='green'>".$clang->gT("All token entries have been deleted.")."</font></strong></td></tr>\n";
	$subaction="";
}

if ($subaction == "clearinvites")
{
	$query="UPDATE ".db_table_name("tokens_$surveyid")." SET sent='N'";
	$result=$connect->Execute($query) or die ("Couldn't update sent field<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
	$tokenoutput .= "<tr><td bgcolor='silver' align='center'><strong><font color='green'>".$clang->gT("All invite entries have been set to 'Not Invited'.")."</font></strong></td></tr>\n";
	$subaction="";
}

if ($subaction == "cleartokens")
{
	$query="UPDATE ".db_table_name("tokens_$surveyid")." SET token=''";
	$result=$connect->Execute($query) or die("Couldn't reset the tokens field<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
	$tokenoutput .= "<tr><td align='center' bgcolor='silver'><strong><font color='green'>".$clang->gT("All unique token numbers have been removed.")."</font></strong></td></tr>\n";
	$subaction="";
}

if ($subaction == "updatedb" && $surveyid)
{
	$query = "ALTER TABLE `tokens_$surveyid`\n"
	. "ADD `attribute_1` varchar(100) NULL,\n"
	. "ADD `attribute_2` varchar(100) NULL,\n"
	. "ADD `mpid` int NULL";
	if ($result = $connect->Execute($query))
	{
		$tokenoutput .= "<tr><td align='center'>".$clang->gT("Success")."</td></tr>\n";
		$subaction="";
	}
	else
	{
		$tokenoutput .= "<tr><td align='center'>".$clang->gT("Error")."</td></tr>\n";
		$subaction="";
	}
}

if (!$subaction)
{
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Token Database Administration Options").":</strong></font></td></tr>\n"
	."\t<tr>\n"
	."\t\t<td align='center'>\n"
	."\t\t\t<table align='center'><tr><td>\n"
	."\t\t\t<br />\n"
	."\t\t\t<ul><li><a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=clearinvites' onClick='return confirm(\""
	.$clang->gT("Are you really sure you want to reset all invitation records to NO?")."\")'>".$clang->gT("Set all entries to 'No invitation sent'.")."</a></li>\n"
	."\t\t\t<li><a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=cleartokens' onClick='return confirm(\""
	.$clang->gT("Are you sure you want to delete all unique token numbers?")."\")'>".$clang->gT("Delete all unique token numbers")."</a></li>\n"
	."\t\t\t<li><a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=deleteall' onClick='return confirm(\""
	.$clang->gT("Are you really sure you want to delete ALL token entries?")."\")'>".$clang->gT("Delete all token entries")."</a></li>\n";
	$bquery = "SELECT * FROM ".db_table_name("tokens_$surveyid")." LIMIT 1";
	$bresult = $connect->Execute($bquery) or die($clang->gT("Error")." counting fields<br />".htmlspecialchars($connect->ErrorMsg()));
	$bfieldcount=$bresult->FieldCount();
	if ($bfieldcount==7)
	{
		$tokenoutput .= "\t\t\t<li><a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=updatedb'>".$clang->gT("Update tokens table with new fields")."</a></li>\n";
	}
	$tokenoutput .= "\t\t\t<li><a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=kill'>".$clang->gT("Drop tokens table")."</a></li></ul>\n"
	."\t\t\t</td></tr></table>\n"
	."\t\t</td>\n"
	."\t</tr>\n"
	."</table>\n";
}

if ($subaction == "browse" || $subaction == "search")
{
	if (!isset($limit)) {$limit = 50;}
	if (!isset($start)) {$start = 0;}

	if ($limit > $tkcount) {$limit=$tkcount;}
	$next=$start+$limit;
	$last=$start-$limit;
	$end=$tkcount-$limit;
	if ($end < 0) {$end=0;}
	if ($last <0) {$last=0;}
	if ($next >= $tkcount) {$next=$tkcount-$limit;}
	if ($end < 0) {$end=0;}

	//ALLOW SELECTION OF NUMBER OF RECORDS SHOWN
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Data View Control").":</strong></font></td></tr>\n"
	."\t<tr bgcolor='#999999'><td align='left'>\n"
	."\t\t\t<img src='$imagefiles/blank.gif' alt='' width='31' height='20' border='0' hspace='0' align='left'>\n"
	."\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
	."\t\t\t<a href='$scriptname?action=tokens&amp;subaction=browse&amp;sid=$surveyid&amp;start=0&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'" .
			"onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Show start..")."');return false\">".
			"<img name='DBeginButton' align='left' src='$imagefiles/databegin.png' title=''/></a>\n"
	."\t\t\t<a href='$scriptname?action=tokens&amp;subaction=browse&amp;sid=$surveyid&amp;start=$last&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'" .
			"onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Show previous...")."');return false\">" .
			"<img name='DBackButton' align='left' src='$imagefiles/databack.png' title='' /></a>\n"
	."\t\t\t<img src='$imagefiles/blank.gif' alt='' width='13' height='20' border='0' hspace='0' align='left'>\n"
	."\t\t\t<a href='$scriptname?action=tokens&amp;subaction=browse&amp;sid=$surveyid&amp;start=$next&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'" .
			"onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Show next...")."');return false\">" .
			"<img name='DForwardButton' align='left' src='$imagefiles/dataforward.png' title=''/></a>\n"
	."\t\t\t<a href='$scriptname?action=tokens&amp;subaction=browse&amp;sid=$surveyid&amp;start=$end&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring'" .
			" onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Show last...")."');return false\">".
			"<img name='DEndButton' align='left'  src='$imagefiles/dataend.png' title=''/></a>\n"
	."\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
	."\t\t\t\n"
	."\t\t\t<table align='left' cellpadding='0' cellspacing='0' border='0'>\n"
	."\t\t\t\t<tr><td><form method='post' action='$scriptname?action=tokens'>\n"
	."\t\t\t\t\t<input type='text' name='searchstring' value='$searchstring'>\n"
	."\t\t\t\t\t<input type='submit' value='".html_escape($clang->gT("Search"))."'>\n"
	."\t\t\t\t<input type='hidden' name='order' value='$order'>\n"
	."\t\t\t\t<input type='hidden' name='subaction' value='search'>\n"
	."\t\t\t\t<input type='hidden' name='sid' value='$surveyid'>\n"
	."\t\t\t\t</form></td>\n"
	."\t\t\t</tr></table>\n"
	."\t\t</td>\n"
	."\t\t<td align='right'><form action='$homeurl/tokens.php'>\n"
	."\t\t<font size='1' face='verdana'>"
	."&nbsp;".$clang->gT("Records Displayed:")."<input type='text' size='4' value='$limit' name='limit'>"
	."&nbsp;".$clang->gT("Starting From:")."<input type='text' size='4' value='$start' name='start'>"
	."&nbsp;<input type='submit' value='".html_escape($clang->gT("Show"))."'>\n"
	."\t\t</font>\n"
	."\t\t<input type='hidden' name='sid' value='$surveyid'>\n"
	."\t\t<input type='hidden' name='subaction' value='browse'>\n"
	."\t\t<input type='hidden' name='order' value='$order'>\n"
	."\t\t<input type='hidden' name='searchstring' value='$searchstring'>\n"
	."\t\t</form></td>\n"
	."\t</tr>\n";
	$bquery = "SELECT * FROM ".db_table_name("tokens_$surveyid")." LIMIT 1";
	$bresult = $connect->Execute($bquery) or die($clang->gT("Error")." counting fields<br />".htmlspecialchars($connect->ErrorMsg()));
	$bfieldcount=$bresult->FieldCount()-1;
	$bquery = "SELECT * FROM ".db_table_name("tokens_$surveyid");
	if ($searchstring)
	{
		$bquery .= " WHERE firstname LIKE '%$searchstring%' "
		. "OR lastname LIKE '%$searchstring%' "
		. "OR email LIKE '%$searchstring%' "
		. "OR token LIKE '%$searchstring%'";
		if ($bfieldcount == 9)
		{
			$bquery .= " OR attribute_1 like '%$searchstring%' "
			. "OR attribute_2 like '%$searchstring%'";
		}
	}
	if (!isset($order) || !$order) {$bquery .= " ORDER BY tid";}
	else {$bquery .= " ORDER BY $order"; }
	$bquery .= " LIMIT $start, $limit";
	$bresult = db_execute_assoc($bquery) or die ($clang->gT("Error").": $bquery<br />".htmlspecialchars($connect->ErrorMsg()));
	$bgc="";

	$tokenoutput .= "<tr><td colspan='2'>\n"
	."<table width='100%' cellpadding='1' cellspacing='1' align='center' bgcolor='#CCCCCC'>\n";
	//COLUMN HEADINGS
	$tokenoutput .= "\t<tr>\n"
	."\t\t<th align='left' valign='top'>"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=tid&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imagefiles/downarrow.png' alt='"
	.$clang->gT("Sort by: ")."ID' border='0' align='left' hspace='0'></a>"."ID</th>\n"
	."\t\t<th align='left' valign='top'>"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=firstname&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imagefiles/downarrow.png' alt='"
	.$clang->gT("Sort by: ").$clang->gT("First Name")."' border='0' align='left'></a>".$clang->gT("First Name")."</th>\n"
	."\t\t<th align='left' valign='top'>"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=lastname&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imagefiles/downarrow.png' alt='"
	.$clang->gT("Sort by: ").$clang->gT("Last Name")."' border='0' align='left'></a>".$clang->gT("Last Name")."</th>\n"
	."\t\t<th align='left' valign='top'>"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=email&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imagefiles/downarrow.png' alt='"
	.$clang->gT("Sort by: ").$clang->gT("Email")."' border='0' align='left'></a>".$clang->gT("Email")."</th>\n"
	."\t\t<th align='left' valign='top'>"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=token&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imagefiles/downarrow.png' alt='"
	.$clang->gT("Sort by: ").$clang->gT("Token")."' border='0' align='left'></a>".$clang->gT("Token")."</th>\n"

	."\t\t<th align='left' valign='top'>"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=language&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imagefiles/downarrow.png' alt='"
	.$clang->gT("Sort by: ").$clang->gT("Language")."' border='0' align='left'></a>".$clang->gT("Language")."</th>\n"
	
	."\t\t<th align='left' valign='top'>"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=sent%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imagefiles/downarrow.png' alt='"
	.$clang->gT("Sort by: ").$clang->gT("Invite sent?")."' border='0' align='left'></a>".$clang->gT("Invite sent?")."</th>\n"
	."\t\t<th align='left' valign='top'>"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=completed%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
	."<img src='$imagefiles/downarrow.png' alt='"
	.$clang->gT("Sort by: ").$clang->gT("Completed?")."' border='0' align='left'></a>".$clang->gT("Completed?")."</th>\n";
	if ($bfieldcount == 10)
	{
		$tokenoutput .= "\t\t<th align='left' valign='top'>"
		."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=attribute_1&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
		."<img src='$imagefiles/downarrow.png' alt='"
		.$clang->gT("Sort by: ").$clang->gT("Attribute 1")."' border='0' align='left'></a>".$attr1_name."</th>\n"
		."\t\t<th align='left' valign='top'>"
		."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=attribute_2&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
		."<img src='$imagefiles/downarrow.png' alt='"
		.$clang->gT("Sort by: ").$clang->gT("Attribute 2")."' border='0' align='left'></a>".$attr2_name."</th>\n"
		."\t\t<th align='left' valign='top'>"
		."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=mpid&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
		."<img src='$imagefiles/downarrow.png' alt='"
		.$clang->gT("Sort by: ").$clang->gT("MPID")."' border='0' align='left'></a>".$clang->gT("MPID")."</th>\n";
	}
	$tokenoutput .= "\t\t<th align='left' valign='top' colspan='2'>".$clang->gT("Actions")."</th>\n"
	."\t</tr>\n";

	while ($brow = $bresult->FetchRow())
	{
    	$brow['token'] = trim($brow['token']);
		if ($bgc == "#EEEEEE") {$bgc = "#DDDDDD";} else {$bgc = "#EEEEEE";}
		$tokenoutput .= "\t<tr bgcolor='$bgc'>\n";
		foreach ($brow as $a=>$b)
		{
			$tokenoutput .= "\t\t<td>$brow[$a]</td>\n";
		}
		$tokenoutput .= "\t\t<td align='left'>\n"
		."\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='submit' value='E' title='"
		.$clang->gT("Edit Token Entry")."' onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=edit&amp;tid=".$brow['tid']."', '_top')\" />"
		."<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='submit' value='D' title='"
		.$clang->gT("Delete Token Entry")."' onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=delete&amp;tid=".$brow['tid']."&amp;limit=$limit&amp;start=$start&amp;order=$order', '_top')\" />";

		if (($brow['completed'] == "N" || $brow['completed'] == "") &&$brow['token']) {$tokenoutput .= "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='submit' value='S' title='".$clang->gT("Do Survey")."' onClick=\"window.open('$publicurl/index.php?sid=$surveyid&amp;token=".trim($brow['token'])."', '_blank')\" />\n";}
		$tokenoutput .= "\n\t\t</td>\n";
		if ($brow['completed'] != "N" && $brow['completed']!="" && $surveyprivate == "N")
		{
			$tokenoutput .= "\t\t<form action='$homeurl/browse.php' method='post' target='_blank'>\n"
			."\t\t<td align='center' valign='top'>\n"
			."\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='submit' value='V' title='"
			.$clang->gT("View Response")."' />\n"
			."\t\t</td>\n"
			."\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
			."\t\t<input type='hidden' $subaction value='id' />\n"
			."\t\t<input type='hidden' name='sql' value=\"token='{$brow['token']}'\" />\n"
			."\t\t</form>\n";

			// UPDATE button to the tokens display in the MPID Actions column
			$query="SELECT id FROM ".db_table_name("survey_$surveyid")." WHERE token='".$brow['token']."'";
			$result=db_execute_num($query) or die ("<br />Could not find token!<br />\n" . htmlspecialchars($connect->ErrorMsg()));
			list($id) = $result->FetchRow();
			if  ($id)
			{
				$tokenoutput .= "\t\t<form action='$homeurl/dataentry.php' method='post' target='_blank'>\n"
				."\t\t<td align='center' valign='top'>\n"
				."\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='submit' value='U' title='"
				.$clang->gT("Update Response")."' />\n"
				."\t\t</td>\n"
				."\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
				."\t\t<input type='hidden' $subaction value='edit' />\n"
				."\t\t<input type='hidden' name='surveytable' value='survey_$surveyid' />\n"
				."\t\t<input type='hidden' name='id' value='$id' />\n"
				."\t\t</form>\n";
			}
		}

		elseif ($brow['completed'] == "N" && $brow['token'] && $brow['sent'] == "N")

		{
			$tokenoutput .= "\t\t<td align='center' valign='top'>\n"
			."\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='submit' value='I' title='"
			.$clang->gT("Send invitation email to this entry")."' onClick=\"window.open('{$_SERVER['PHP_SELF']}?sid=$surveyid&amp;subaction=email&amp;tid=".$brow['tid']."', '_top')\" />"
			."\t\t</td>\n";
		}

		elseif ($brow['completed'] == "N" && $brow['token'] && $brow['sent'] != "N")

		{
			$tokenoutput .= "\t\t<td align='center' valign='top'>\n"
			."\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='submit' value='R' title='"
			.$clang->gT("Send reminder email to this entry")."' onClick=\"window.open('{$_SERVER['PHP_SELF']}?sid=$surveyid&amp;subaction=remind&amp;tid=$brow[0]', '_top')\" />"
			."\t\t</td>\n";
		}
		else
		{
			$tokenoutput .= "\t\t<td>\n"
			."\t\t</td>\n";
		}
		$tokenoutput .= "\t</tr>\n";
	}
	$tokenoutput .= "</table>\n"
	."</td></tr></table>\n";
}

if ($subaction == "kill")
{
	$date = date('YmdHi');
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4' align='center'>"
	."<font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Delete Tokens Table").":</strong></font></td></tr>\n"
	."\t<tr><td colspan='2' align='center'>\n"
	."<br />\n";
	// ToDo: Just delete it if there is no token in the table
	if (!isset($_GET['ok']) || !$_GET['ok'])
	{
		$tokenoutput .= "<font color='red'><strong>".$clang->gT("Warning")."</strong></font><br />\n"
		.$clang->gT("If you delete this table tokens will no longer be required to access this survey.<br />A backup of this table will be made if you proceed. Your system administrator will be able to access this table.")."<br />\n"
		."( \"old_tokens_{$_GET['sid']}_$date\" )<br /><br />\n"
		."<input type='submit' value='"
		.$clang->gT("Delete Tokens")."' onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=kill&amp;ok=surething', '_top')\" /><br />\n"
		."<input type='submit' value='"
		.$clang->gT("Cancel")."' onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" />\n";
	}
	elseif (isset($_GET['ok']) && $_GET['ok'] == "surething")
	{
		$oldtable = "tokens_$surveyid";
		$newtable = "old_tokens_{$surveyid}_$date";
		$deactivatequery = "RENAME TABLE ".db_table_name($oldtable)." TO ".db_table_name($newtable);
		$deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't deactivate because:<br />\n".htmlspecialchars($connect->ErrorMsg())."<br /><br />\n<a href='$scriptname?sid=$surveyid'>Admin</a>\n");
		$tokenoutput .= "<span style='display: block; text-align: center; width: 70%'>\n"
		.$clang->gT("The tokens table has now been removed and tokens are no longer required to access this survey.<br /> A backup of this table has been made and can be accessed by your system administrator.")."<br />\n"
		."(\"{$dbprefix}old_tokens_{$_GET['sid']}_$date\")"."<br /><br />\n"
		."<input type='submit' value='"
		.$clang->gT("Main Admin Screen")."' onClick=\"window.open('$scriptname?sid={$_GET['sid']}', '_top')\" />\n"
		."</span>\n";
	}
	$tokenoutput .= "</font></td></tr></table>\n"
	."<table><tr><td></td></tr></table>\n";

}


if ($subaction == "email")
{
	$tokenoutput .= "\t<tr bgcolor='#555555'>\n\t\t<td colspan='2' height='4'>"
	."<font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Email Invitation").":</strong></font></td>\n\t</tr>\n"
	."\t<tr>\n\t\t<td colspan='2' align='center'>\n";
	if (!isset($_POST['ok']) || !$_POST['ok'])
	{
		//GET SURVEY DETAILS
		$thissurvey=getSurveyInfo($surveyid);
		if (!$thissurvey['email_invite']) {$thissurvey['email_invite']=str_replace("\n", "\r\n", $clang->gT("Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}"));}

		$fieldsarray["{ADMINNAME}"]= $thissurvey['adminname'];
		$fieldsarray["{ADMINEMAIL}"]=$thissurvey['adminemail'];
		$fieldsarray["{SURVEYNAME}"]=$thissurvey['name'];
		$fieldsarray["{SURVEYDESCRIPTION}"]=$thissurvey['description'];

		$subject=Replacefields($thissurvey['email_invite_subj'], $fieldsarray);
		$textarea=Replacefields($thissurvey['email_invite'], $fieldsarray);

		$tokenoutput .= "<form method='post' action='$scriptname?action=tokens&amp;sid=$surveyid'><table width='100%' align='center' bgcolor='#DDDDDD'>\n"
		."\n";
		if (isset($_GET['tid']) && $_GET['tid'])
		{
			$tokenoutput .= "<tr><td bgcolor='silver' colspan='2'><font size='1'>"
			."to TokenID No {$_GET['tid']}"
			."</font></font></td></tr>";
		}
		$tokenoutput .= "\t<tr>\n"
		."\t\t<td align='right'><strong>".$clang->gT("From").":</strong></font></td>\n"
		."\t\t<td><input type='text' size='50' name='from' value=\"{$thissurvey['adminname']} <{$thissurvey['adminemail']}>\" /></td>\n"
		."\t</tr>\n"
		."\t<tr>\n"
		."\t\t<td align='right'><strong>".$clang->gT("Subject").":</strong></font></td>\n";
		$tokenoutput .= "\t\t<td><input type='text' size='50' name='subject' value=\"$subject\" /></td>\n"
		."\t</tr>\n"
		."\t<tr>\n"
		."\t\t<td align='right' valign='top'><strong>".$clang->gT("Message").":</strong></font></td>\n"
		."\t\t<td>\n"
		."\t\t\t<textarea name='message' rows='10' cols='80' style='background-color: #EEEFFF; font-family: verdana; font-size: 10; color: #000080'>\n";
		$tokenoutput .= $textarea;
		$tokenoutput .= "\t\t\t</textarea>\n"
		."\t\t</td>\n"
		."\t</tr>\n"
		."\t<tr><td></td><td align='left'><input type='submit' value='"
		.$clang->gT("Send Invitations")."'>\n"
		."\t<input type='hidden' name='ok' value='absolutely' />\n"
		."\t<input type='hidden' name='sid' value='{$_GET['sid']}' />\n"
		."\t<input type='hidden' name='subaction' value='email' /></td></tr>\n";
		if (isset($_GET['tid']) && $_GET['tid']) {$tokenoutput .= "\t<input type='hidden' name='tid' value='{$_GET['tid']}' />";}
		$tokenoutput .= "\n"
		."</table></form>\n";
	}
	else
	{
		$tokenoutput .= $clang->gT("Sending Invitations");
		$_POST['message']=auto_unescape($_POST['message']);
		$_POST['subject']=auto_unescape($_POST['subject']);
		if (isset($_POST['tid']) && $_POST['tid']) {$tokenoutput .= " (".$clang->gT("Sending to TID No:")." {$_POST['tid']})";}
		$tokenoutput .= "<br />\n";

		$ctquery = "SELECT * FROM ".db_table_name("tokens_{$_POST['sid']}")." WHERE ((completed ='N') or (completed='')) AND ((sent ='N') or (sent='')) AND token !='' AND email != ''";

		if (isset($_POST['tid']) && $_POST['tid']) {$ctquery .= " and tid='{$_POST['tid']}'";}
		$tokenoutput .= "<!-- ctquery: $ctquery -->\n";
		$ctresult = $connect->Execute($ctquery) or die("Database error!<br />\n" . htmlspecialchars($connect->ErrorMsg()));
		$ctcount = $ctresult->RecordCount();
		$ctfieldcount = $ctresult->FieldCount();
		$emquery = "SELECT firstname, lastname, email, token, tid";
		if ($ctfieldcount > 7) {$emquery .= ", attribute_1, attribute_2";}

		$emquery .= " FROM ".db_table_name("tokens_{$_POST['sid']}")." WHERE ((completed ='N') or (completed='')) AND ((sent ='N') or (sent='')) AND token !='' AND email != ''";

		if (isset($_POST['tid']) && $_POST['tid']) {$emquery .= " and tid='{$_POST['tid']}'";}
		$emquery .= " LIMIT $maxemails";
		$tokenoutput .= "\n\n<!-- emquery: $emquery -->\n\n";
		$emresult = db_execute_assoc($emquery) or die ("Couldn't do query.<br />\n$emquery<br />\n".htmlspecialchars($connect->ErrorMsg()));
		$emcount = $emresult->RecordCount();
		$from = $_POST['from'];

		$tokenoutput .= "<table width='500px' align='center' bgcolor='#EEEEEE'>\n"
		."\t<tr>\n"
		."\t\t<td><font size='1'>\n";
		if ($emcount > 0)
		{
			while ($emrow = $emresult->FetchRow())
			{
				$to = $emrow['email'];
				unset($fieldsarray);
				$fieldsarray["{EMAIL}"]=$emrow['email'];
				$fieldsarray["{FIRSTNAME}"]=$emrow['firstname'];
				$fieldsarray["{LASTNAME}"]=$emrow['lastname'];
				$fieldsarray["{SURVEYURL}"]="$publicurl/index.php?sid=$surveyid&token={$emrow['token']}";
				$fieldsarray["{TOKEN}"]=$emrow['token'];
				$fieldsarray["{ATTRIBUTE_1}"]=$emrow['attribute_1'];
				$fieldsarray["{ATTRIBUTE_2}"]=$emrow['attribute_2'];
				$modsubject=Replacefields($_POST['subject'], $fieldsarray);
				$modmessage=Replacefields($_POST['message'], $fieldsarray);

				if (MailTextMessage($modmessage, $modsubject, $to , $from, $sitename))
				{
					// Put date into sent and completed
					$today = date("Y-m-d H:i");
					$udequery = "UPDATE ".db_table_name("tokens_{$_POST['sid']}")."\n"
					."SET sent='$today' WHERE tid={$emrow['tid']}";
					//
					$uderesult = $connect->Execute($udequery) or die ("Couldn't update tokens<br />$udequery<br />".htmlspecialchars($connect->ErrorMsg()));
					$tokenoutput .= "[".$clang->gT("Invitation Sent To:")."{$emrow['firstname']} {$emrow['lastname']} ($to)]<br />\n";
				}
				else
				{
					$tokenoutput .= ReplaceFields($clang->gT("Mail to {FIRSTNAME} {LASTNAME} ({EMAIL}) Failed"), $fieldsarray);
					$tokenoutput .= "<br /><pre>$headers<br />$message</pre>";
				}
			}
			if ($ctcount > $emcount)
			{
				$lefttosend = $ctcount-$maxemails;
				$tokenoutput .= "\t\t</td>\n"
				."\t</tr>\n"
				."\t<tr>\n"
				."\t\t<td align='center'><strong>".$clang->gT("Warning")."</strong><br />\n"
				."\t\t\t<form method='post'>\n"
				.$clang->gT("There are more emails pending than can be sent in one batch. Continue sending emails by clicking below.")."<br /><br />\n";
				$tokenoutput .= str_replace("{EMAILCOUNT}", "$lefttosend", $clang->gT("There are {EMAILCOUNT} emails still to be sent."));
				$tokenoutput .= "<br /><br />\n";
				$message = html_escape($_POST['message']);
				$tokenoutput .= "\t\t\t<input type='submit' value='".html_escape($clang->gT("Continue"))."' />\n"
				."\t\t\t<input type='hidden' name='ok' value=\"absolutely\" />\n"
				."\t\t\t<input type='hidden' $subaction value=\"email\" />\n"
				."\t\t\t<input type='hidden' name='sid' value=\"{$_POST['sid']}\" />\n"
				."\t\t\t<input type='hidden' name='from' value=\"{$_POST['from']}\" />\n"
				."\t\t\t<input type='hidden' name='subject' value=\"{$_POST['subject']}\" />\n"
				."\t\t\t<input type='hidden' name='message' value=\"$message\" />\n"
				."\t\t\t</form>\n";
			}
		}
		else
		{
			$tokenoutput .= "<center><strong>".$clang->gT("Warning")."</strong><br />\n".$clang->gT("There were no eligible emails to send. This will be because none satisfied the criteria of - having an email address, not having been sent an invitation already, having already completed the survey and having a token.")."</center>\n";
		}
		$tokenoutput .= "\t\t</td>\n";

	}
	$tokenoutput .= "</td></tr></table>\n";
}

if ($subaction == "remind")
{
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Email Reminder").":</strong></font></td></tr>\n"
	."\t<tr><td colspan='2' align='center'>\n";
	if (!isset($_POST['ok']) || !$_POST['ok'])
	{
		//GET SURVEY DETAILS
		$thissurvey=getSurveyInfo($surveyid);
		if (!$thissurvey['email_remind']) {$thissurvey['email_remind']=str_replace("\n", "\r\n", $clang->gT("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}"));}
		$tokenoutput .= "<form method='post' action='$scriptname?action=tokens'><table width='100%' align='center' bgcolor='#DDDDDD'>\n"
		."\t\n"
		."\t<tr>\n"
		."\t\t<td align='right' width='150'><strong>".$clang->gT("From").":</strong></font></td>\n"
		."\t\t<td><input type='text' size='50' name='from' value=\"{$thissurvey['adminname']} <{$thissurvey['adminemail']}>\" /></td>\n"
		."\t</tr>\n"
		."\t<tr>\n"
		."\t\t<td align='right' width='150'><strong>".$clang->gT("Subject").":</strong></font></td>\n";
		$subject=str_replace("{SURVEYNAME}", $thissurvey['name'], $thissurvey['email_remind_subj']);
		$tokenoutput .= "\t\t<td><input type='text' size='50' name='subject' value='$subject' /></td>\n"
		."\t</tr>\n";
		if (!isset($_GET['tid']) || !$_GET['tid'])
		{
			$tokenoutput .= "\t<tr>\n"
			."\t\t<td align='right' width='150' valign='top'><strong>"
			.$clang->gT("Start at TID No:")."</strong></font></td>\n"
			."\t\t<td><input type='text' size='5' name='last_tid' /></td>\n"
			."\t</tr>\n";
		}
		else
		{
			$tokenoutput .= "\t<tr>\n"
			."\t\t<td align='right' width='150' valign='top'><strong>"
			.$clang->gT("Sending to TID No:")."</strong></font></td>\n"
			."\t\t<td>{$_GET['tid']}</font></td>\n"
			."\t</tr>\n";
		}
		$tokenoutput .= "\t<tr>\n"
		."\t\t<td align='right' width='150' valign='top'><strong>"
		.$clang->gT("Message").":</strong></font></td>\n"
		."\t\t<td>\n"
		."\t\t\t<textarea name='message' rows='10' cols='80' style='background-color: #EEEFFF; font-family: verdana; font-size: 10; color: #000080'>\n";

		$textarea = $thissurvey['email_remind'];
		$textarea = str_replace("{ADMINNAME}", $thissurvey['adminname'], $textarea);
		$textarea = str_replace("{ADMINEMAIL}", $thissurvey['adminemail'], $textarea);
		$textarea = str_replace("{SURVEYNAME}", $thissurvey['name'], $textarea);
		$textarea = str_replace("{SURVEYDESCRIPTION}", $thissurvey['description'], $textarea);
		$tokenoutput .= $textarea;

		$tokenoutput .= "\t\t\t</textarea>\n"
		."\t\t</td>\n"
		."\t</tr>\n"
		."\t<tr>\n"
		."\t\t<td></td>\n"
		."\t\t<td align='left'>\n"
		."\t\t\t<input type='submit' value='".html_escape($clang->gT("Send Reminders"))."' />\n"
		."\t<input type='hidden' name='ok' value='absolutely'>\n"
		."\t<input type='hidden' name='sid' value='{$_GET['sid']}'>\n"
		."\t<input type='hidden' name='subaction' value='remind'>\n"
		."\t\t</td>\n"
		."\t</tr>\n";
		if (isset($_GET['tid']) && $_GET['tid']) {$tokenoutput .= "\t<input type='hidden' name='tid' value='{$_GET['tid']}'>\n";}
		$tokenoutput .= "\t</table>\n"
		."</form>\n";
	}
	else
	{
		$tokenoutput .= $clang->gT("Sending Reminders")."<br />\n";
		$_POST['message']=auto_unescape($_POST['message']);
		$_POST['subject']=auto_unescape($_POST['subject']);

		if (isset($_POST['last_tid']) && $_POST['last_tid']) {$tokenoutput .= " (".$clang->gT("From")." TID: {$_POST['last_tid']})";}
		if (isset($_POST['tid']) && $_POST['tid']) {$tokenoutput .= " (".$clang->gT("Sending to TID No:")." TID: {$_POST['tid']})";}

		$ctquery = "SELECT * FROM ".db_table_name("tokens_{$_POST['sid']}")." WHERE (completed ='N' or completed ='') AND sent<>'' AND sent<>'N' AND token <>'' AND email <> ''";

		if (isset($_POST['last_tid']) && $_POST['last_tid']) {$ctquery .= " AND tid > '{$_POST['last_tid']}'";}
		if (isset($_POST['tid']) && $_POST['tid']) {$ctquery .= " AND tid = '{$_POST['tid']}'";}
		$tokenoutput .= "<!-- ctquery: $ctquery -->\n";
		$ctresult = $connect->Execute($ctquery) or die ("Database error!<br />\n" . htmlspecialchars($connect->ErrorMsg()));
		$ctcount = $ctresult->RecordCount();
		$ctfieldcount = $ctresult->FieldCount();
		$emquery = "SELECT firstname, lastname, email, token, tid";
		if ($ctfieldcount > 7) {$emquery .= ", attribute_1, attribute_2";}

		// TLR change to put date into sent and completed
		$emquery .= " FROM ".db_table_name("tokens_{$_POST['sid']}")." WHERE (completed = 'N' or completed = '') AND sent <> 'N' and sent<>'' AND token <>'' AND EMAIL <>''";

		if (isset($_POST['last_tid']) && $_POST['last_tid']) {$emquery .= " AND tid > '{$_POST['last_tid']}'";}
		if (isset($_POST['tid']) && $_POST['tid']) {$emquery .= " AND tid = '{$_POST['tid']}'";}
		$emquery .= " ORDER BY tid LIMIT $maxemails";
		$emresult = db_execute_assoc($emquery) or die ("Couldn't do query.<br />$emquery<br />".htmlspecialchars($connect->ErrorMsg()));
		$emcount = $emresult->RecordCount();
		$from = $_POST['from'];
		$tokenoutput .= "<table width='500' align='center' bgcolor='#EEEEEE'>\n"
		."\t<tr>\n"
		."\t\t<td><font size='1'>\n";

		if ($emcount > 0)
		{
			while ($emrow = $emresult->FetchRow())
			{
				$to = $emrow['email'];

				$fieldsarray["{EMAIL}"]=$emrow['email'];
				$fieldsarray["{FIRSTNAME}"]=$emrow['firstname'];
				$fieldsarray["{LASTNAME}"]=$emrow['lastname'];
				$fieldsarray["{SURVEYURL}"]="$publicurl/index.php?sid=$surveyid&token={$emrow['token']}";
				$fieldsarray["{TOKEN}"]=$emrow['token'];
				$fieldsarray["{LANGUAGE}"]=$emrow['language'];
				$fieldsarray["{ATTRIBUTE_1}"]=$emrow['attribute_1'];
				$fieldsarray["{ATTRIBUTE_2}"]=$emrow['attribute_2'];

				$msgsubject=Replacefields($_POST['subject'], $fieldsarray);
				$sendmessage=Replacefields($_POST['message'], $fieldsarray);

				if (MailtextMessage($sendmessage, $msgsubject, $to, $from, $sitename))
				{
					$tokenoutput .= "\t\t\t({$emrow['tid']})[".$clang->gT("Reminder Sent To:")." {$emrow['firstname']} {$emrow['lastname']}]<br />\n";
				}
				else
				{
					$tokenoutput .= "\t\t\t({$emrow['tid']})[Email to {$emrow['firstname']} {$emrow['lastname']} failed]<br />\n";
				}
				$lasttid = $emrow['tid'];
			}
			if ($ctcount > $emcount)
			{
				$lefttosend = $ctcount-$maxemails;
				$tokenoutput .= "\t\t</td>\n"
				."\t</tr>\n"
				."\t<tr><form method='post' action='$homeurl/tokens.php'>\n"
				."\t\t<td align='center'>\n"
				."\t\t\t<strong>".$clang->gT("Warning")."</strong><br /><br />\n"
				.$clang->gT("There are more emails pending than can be sent in one batch. Continue sending emails by clicking below.")."<br /><br />\n"
				.str_replace("{EMAILCOUNT}", $lefttosend, $clang->gT("There are {EMAILCOUNT} emails still to be sent."))
				."<br />\n"
				."\t\t\t<input type='submit' value='".html_escape($clang->gT("Continue"))."' />\n"
				."\t\t</td>\n"
				."\t<input type='hidden' name='ok' value=\"absolutely\" />\n"
				."\t<input type='hidden' $subaction value=\"remind\" />\n"
				."\t<input type='hidden' name='sid' value=\"{$_POST['sid']}\" />\n"
				."\t<input type='hidden' name='from' value=\"{$_POST['from']}\" />\n"
				."\t<input type='hidden' name='subject' value=\"{$_POST['subject']}\" />\n";
				$message = html_escape($_POST['message']);
				$tokenoutput .= "\t<input type='hidden' name='message' value=\"$message\" />\n"
				."\t<input type='hidden' name='last_tid' value=\"$lasttid\" />\n"
				."\t</form>\n";
			}
		}
		else
		{
			$tokenoutput .= "<center><strong>".$clang->gT("Warning")."</strong><br />\n"
			.$clang->gT("There were no eligible emails to send. This will be because none satisfied the criteria of - having an email address, having been sent an invitation, but not having yet completed the survey.")."\n"
			."<br /><br />\n"
			."\t\t</td>\n";
		}
		$tokenoutput .= "\t</tr>\n"
		."</table>\n";
	}
	$tokenoutput .= "</td></tr></table>\n";
}

if ($subaction == "tokenify")
{
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>".$clang->gT("Create Tokens").":</strong></font></td></tr>\n";
	$tokenoutput .= "\t<tr><td align='center'><br />\n";
	if (!isset($_GET['ok']) || !$_GET['ok'])
	{
		$tokenoutput .= "<br />".$clang->gT("Clicking yes will generate tokens for all those in this token list that have not been issued one. Is this OK?")."<br /><br />\n"
		."<input type='submit' value='"
		.$clang->gT("Yes")."' onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=tokenify&amp;ok=Y', '_top')\" />\n"
		."<input type='submit' value='"
		.$clang->gT("No")."' onClick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" />\n"
		."<br /><br />\n";
	}
	else
	{
		if (_PHPVERSION < "4.2.0")
		{
			srand((double)microtime()*1000000);
		}
		$newtokencount = 0;
		$tkquery = "SELECT * FROM ".db_table_name("tokens_$surveyid")." WHERE token IS NULL OR token=''";
		$tkresult = db_execute_assoc($tkquery) or die ("Mucked up!<br />$tkquery<br />".htmlspecialchars($connect->ErrorMsg()));
		while ($tkrow = $tkresult->FetchRow())
		{
			$insert = "NO";
			while ($insert != "OK")
			{
				$newtoken = randomkey(10);
				$ntquery = "SELECT * FROM ".db_table_name("tokens_$surveyid")." WHERE token='$newtoken'";
				$ntresult = $connect->Execute($ntquery);
				if (!$ntresult->RecordCount()) {$insert = "OK";}
			}
			$itquery = "UPDATE ".db_table_name("tokens_$surveyid")." SET token='$newtoken' WHERE tid={$tkrow['tid']}";
			$itresult = $connect->Execute($itquery);
			$newtokencount++;
		}
		$message=str_replace("{TOKENCOUNT}", $newtokencount, $clang->gT("{TOKENCOUNT} tokens have been created"));
		$tokenoutput .= "<br /><strong>$message</strong><br /><br />\n";
	}
	$tokenoutput .= "\t</font></td></tr></table>\n";
}


if ($subaction == "delete")
{
	$dlquery = "DELETE FROM ".db_table_name("tokens_$surveyid")." WHERE tid={$_GET['tid']}";
	$dlresult = $connect->Execute($dlquery) or die ("Couldn't delete record {$_GET['tid']}<br />".htmlspecialchars($connect->ErrorMsg()));
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Delete")."</strong></td></tr>\n"
	."\t<tr><td align='center'><br />\n"
	."<br /><strong>".$clang->gT("Token has been deleted.")."</strong><br />\n"
	."<font size='1'><i>".$clang->gT("Reloading Screen. Please wait.")."</i><br /><br /></font>\n"
	."\t</td></tr></table>\n";
}

if ($subaction == "edit" || $subaction == "addnew")
{
	if ($subaction == "edit")
	{
		$edquery = "SELECT * FROM ".db_table_name("tokens_$surveyid")." WHERE tid={$_GET['tid']}";
		$edresult = db_execute_assoc($edquery);
		$edfieldcount = $edresult->FieldCount();
		while($edrow = $edresult->FetchRow())
		{
			//Create variables with the same names as the database column names and fill in the value
			foreach ($edrow as $Key=>$Value) {$$Key = $Value;}
		}
	}
	if ($subaction != "edit")
	{
		$edquery = "SELECT * FROM ".db_table_name("tokens_$surveyid")." LIMIT 1";
		$edresult = $connect->Execute($edquery);
		$edfieldcount = $edresult->FieldCount();
	}
	
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Add or Edit Token")."</strong></font></td></tr>\n"
	."\t<tr><td align='center'>\n"
	."<form method='post' action='$scriptname?action=tokens'>\n"
	."<table width='100%' bgcolor='#CCCCCC' align='center'>\n"
	."<tr>\n"
	."\t<td align='right' width='20%'><strong>ID:</strong></font></td>\n"
	."\t<td bgcolor='#EEEEEE'>";
	if ($subaction == "edit")
	{$tokenoutput .=$_GET['tid'];} else {$tokenoutput .="Auto";} 
    $tokenoutput .= "</font></td>\n"
	."</tr>\n"
	."<tr>\n"
	."\t<td align='right' width='20%'><strong>".$clang->gT("First Name").":</strong></font></td>\n"
	."\t<td bgcolor='#EEEEEE'><input type='text' size='30' name='firstname' value=\"";
	if (isset($firstname)) {$tokenoutput .= $firstname;}
	$tokenoutput .= "\"></font></td>\n"
	."</tr>\n"
	."<tr>\n"
	."\t<td align='right' width='20%'><strong>".$clang->gT("Last Name").":</strong></font></td>\n"
	."\t<td bgcolor='#EEEEEE'><input type='text' size='30' name='lastname' value=\"";
	if (isset($lastname)) {$tokenoutput .= $lastname;}
	$tokenoutput .= "\"></font></td>\n"
	."</tr>\n"
	."<tr>\n"
	."\t<td align='right' width='20%'><strong>".$clang->gT("Email").":</strong></font></td>\n"
	."\t<td bgcolor='#EEEEEE'><input type='text' size='50' name='email' value=\"";
	if (isset($email)) {$tokenoutput .= $email;}
	$tokenoutput .= "\"></font></td>\n"
	."</tr>\n"
	."<tr>\n"
	."\t<td align='right' width='20%'><strong>".$clang->gT("Token").":</strong></font></td>\n"
	."\t<td bgcolor='#EEEEEE'><input type='text' size='15' name='token' value=\"";
	if (isset($token)) {$tokenoutput .= $token;}
	$tokenoutput .= "\">\n";
	if ($subaction == "addnew")
	{
		$tokenoutput .= "\t\t<font size='1' color='red'>".$clang->gT("You can leave this blank, and automatically generate tokens using 'Create Tokens'")."</font></font>\n";
	}
	$tokenoutput .= "\t</font></td>\n"
	."</tr>\n"

."<tr>\n"
	."\t<td align='right' width='20%'><strong>".$clang->gT("Language").":</strong></font></td>\n"
	."\t<td bgcolor='#EEEEEE'>";
	if (isset($language)) {$tokenoutput .= languageDropdownClean($surveyid,$language);}
    else {
	       $tokenoutput .= languageDropdownClean($surveyid,GetBaseLanguageFromSurveyID($surveyid));
	     }
	$tokenoutput .= "</font></td>\n"
	."</tr>\n"

	
	."<tr>\n"
	."\t<td align='right' width='20%'><strong>".$clang->gT("Invite sent?").":</strong></font></td>\n"

	// TLR change to put date into sent and completed
	//	."\t<td bgcolor='#EEEEEE'><input type='text' size='1' name='sent' value=\"";
	."\t<td bgcolor='#EEEEEE'><input type='text' size='15' name='sent' value=\"";

	if (isset($sent)) {$tokenoutput .= $sent;}	else {$tokenoutput .= "N";}
	$tokenoutput .= "\"></font></td>\n"
	."</tr>\n"
	."<tr>\n"
	."\t<td align='right' width='20%'><strong>".$clang->gT("Completed?").":</strong></font></td>\n"

	// TLR change to put date into sent and completed
	//	."\t<td bgcolor='#EEEEEE'><input type='text' size='1' name='completed' value=\"";
	."\t<td bgcolor='#EEEEEE'><input type='text' size='15' name='completed' value=\"";

	if (isset($completed)) {$tokenoutput .= $completed;} else {$tokenoutput .= "N";}
	if ($edfieldcount > 7)
	{
		$tokenoutput .= "\"></font></td>\n"
		."</tr>\n"
		."<tr>\n"
		."\t<td align='right' width='20%'><strong>".$attr1_name.":</strong></font></td>\n"
		."\t<td bgcolor='#EEEEEE'><input type='text' size='50' name='attribute1' value=\"";
		if (isset($attribute_1)) {$tokenoutput .= $attribute_1;}
		$tokenoutput .= "\"></font></td>\n"
		."</tr>\n"
		."<tr>\n"
		."\t<td align='right' width='20%'><strong>".$attr2_name.":</strong></font></td>\n"
		."\t<td bgcolor='#EEEEEE'><input type='text' size='50' name='attribute2' value=\"";
		if (isset($attribute_2)) {$tokenoutput .= $attribute_2;}
	}
	$tokenoutput .= "\"></font></td>\n"
	."</tr>\n"
	."<tr>\n"
	."\t<td colspan='2' align='center'>";
	switch($subaction)
	{
		case "edit":
		$tokenoutput .= "\t\t<input type='submit' value='".html_escape($clang->gT("Update Token"))."'>\n"
		               ."\t\t<input type='hidden' name='subaction' value='updatetoken'>\n"
		               ."\t\t<input type='hidden' name='tid' value='{$_GET['tid']}'>\n";
		break;
		case "addnew":
		$tokenoutput .= "\t\t<input type='submit' value='".html_escape($clang->gT("Add Token"))."'>\n"
		               ."\t\t<input type='hidden' name='subaction' value='inserttoken'>\n";
		break;
	}
	$tokenoutput .= "\t\t<input type='hidden' name='sid' value='$surveyid'>\n"
	."\t</td>\n"
	."</tr>\n\n"
	."</table></form>\n"
	."</td></tr></table>\n";
}


if ($subaction == "updatetoken")
{
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Add or Edit Token")."</strong></td></tr>\n"
	."\t<tr><td align='center'>\n";
	$data = array();
	$data[] = $_POST['firstname'];
	$data[] = $_POST['lastname'];
	$data[] = $_POST['email'];
	$data[] = $_POST['token'];
	$data[] = $_POST['language'];
	$data[] = $_POST['sent'];
	$data[] = $_POST['completed'];
	$udquery = "UPDATE ".db_table_name("tokens_$surveyid")." SET firstname=?, "
	. "lastname=?, email=?, "
	. "token=?, language=?, sent=?, completed=?";
	if (isset($_POST['attribute1']))
	{
		$data[] = $_POST['attribute1'];
		$data[] = $_POST['attribute2'];
		$udquery .= ", attribute_1=?, attribute_2=?";
	}

	$udquery .= " WHERE tid={$_POST['tid']}";

	$udresult = $connect->Execute($udquery, $data) or die ("Update record {$_POST['tid']} failed:<br />\n$udquery<br />\n".htmlspecialchars($connect->ErrorMsg()));
	$tokenoutput .= "<br /><font color='green'><strong>".$clang->gT("Success")."</strong></font><br />\n"
	."<br />".$clang->gT("Updated Token")."<br /><br />\n"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse'>".$clang->gT("Display Tokens")."</a><br /><br />\n"
	."\t</td></tr></table>\n";
}

if ($subaction == "inserttoken")
{
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Add or Edit Token")."</strong></td></tr>\n"
	."\t<tr><td align='center'>\n";
	$data = array('firstname' => $_POST['firstname'],
	'lastname' => $_POST['lastname'],
	'email' => $_POST['email'],
	'token' => $_POST['token'],
	'language' => $_POST['language'],
	'sent' => $_POST['sent'],
	'completed' => $_POST['completed']);
	if (isset($_POST['attribute1']))
	{
		$data['attribute_1'] = $_POST['attribute1'];
		$data['attribute_2'] = $_POST['attribute2'];
	}
    $tblInsert=db_table_name('tokens_'.$surveyid);
	$inquery = $connect->GetInsertSQL($tblInsert, $data);
	$inresult = $connect->Execute($inquery) or die ("Add new record failed:<br />\n$inquery<br />\n".htmlspecialchars($connect->ErrorMsg()));
	$tokenoutput .= "<br /><font color='green'><strong>".$clang->gT("Success")."</strong></font><br />\n"
	."<br />".$clang->gT("Added New Token")."<br /><br />\n"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse'>".$clang->gT("Display Tokens")."</a><br />\n"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=addnew'>".$clang->gT("Add new token entry")."</a><br /><br />\n"
	."\t</td></tr></table>\n";
}

if ($subaction == "import")
{
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'>"
	."<font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Upload CSV File")."</strong></font></td></tr>\n"
	."\t<tr><td align='center'>\n";
	form();
	$tokenoutput .= "<table width='500' bgcolor='#eeeeee'>\n"
	."\t<tr>\n"
	."\t\t<td align='center'>\n"
	."\t\t\t<font size='1'><strong>".$clang->gT("Note:")."</strong><br />\n"
	."\t\t\t".$clang->gT("File should be a standard CSV (comma delimited) file with no quotes. The first line should contain header information (will be removed). Data should be ordered as \"firstname, lastname, email, [token], [attribute1], [attribute2]\".")."\n"
	."\t\t</font></td>\n"
	."\t</tr>\n"
	."</table><br />\n"
	."</td></tr></table>\n";
}

if ($subaction == "importldap")
{
        $tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'>"
        ."<font size='1' face='verdana' color='white'><strong>"
        .$clang->gT("Upload LDAP entries")."</strong></font></td></tr>\n"
        ."\t<tr><td align='center'>\n";
        formldap();
        $tokenoutput .= "<table width='500' bgcolor='#eeeeee'>\n"
        ."\t<tr>\n"
        ."\t\t<td align='center'>\n"
        ."\t\t\t<font size='1'><strong>".$clang->gT("Note:")."</strong><br />\n"
        ."\t\t\t".$clang->gT("LDAP queries are defined by the administrator in the config-ldap.php file")."\n"
        ."\t\t</font></td>\n"
        ."\t</tr>\n"
        ."</table><br />\n"
        ."</td></tr></table>\n";
}

if ($subaction == "upload")
{
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Upload CSV File")."</strong></td></tr>\n"
	."\t<tr><td align='center'>\n";
	if (!isset($tempdir))
	{
		$the_path = $homedir;
	}
	else
	{
		$the_path = $tempdir;
	}
	$the_file_name = $_FILES['the_file']['name'];
	$the_file = $_FILES['the_file']['tmp_name'];
	$the_full_file_path = $the_path."/".$the_file_name;
	if (!@move_uploaded_file($the_file, $the_full_file_path))
	{
		$errormessage="<strong><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("Upload file not found. Check your permissions and path for the upload directory")."</strong>\n";
		form($errormessage);
	}
	else
	{
		$tokenoutput .= "<br /><strong>".$clang->gT("Importing CSV File")."</strong><br />\n<font color='green'>".$clang->gT("Success")."</font><br /><br />\n"
		.$clang->gT("Creating Token Entries")."<br />\n";
		$xz = 0; $xx = 0;
		// This allows to read file with MAC line endings too
		@ini_set('auto_detect_line_endings', true);  
		// open it and trim the ednings
		$tokenlistarray = array_map('rtrim',file($the_full_file_path));
		if (!isset($tokenlistarray)) {$tokenoutput .= "Failed to open the uploaded file!\n";}
		foreach ($tokenlistarray as $buffer)
		{
			if(function_exists('mb_convert_encoding')) {$buffer=mb_convert_encoding($buffer,"UTF-8","auto");} //Sometimes mb_convert_encoding doesn't exist
			$firstname = ""; $lastname = ""; $email = ""; $token = ""; $language=""; $attribute1=""; $attribute2=""; //Clear out values from the last path, in case the next line is missing a value
			if ($xx==0)
			{
				//THIS IS THE FIRST LINE. IT IS THE HEADINGS. IGNORE IT
			}
			else
			{
		
        		$line = convertCSVRowToArray($buffer,',','"');
        		// sanitize it befire writing into table
        		$line = array_map('sanitize_sql_string',$line); 
				$elements = count($line);
				if ($elements > 1)
				{
					$firstname = '';
					$lastname = '';
					$email = '';
					$token = '';
					$language = '';
					$attribute1 = '';
					$attribute2 = '';
					$xy = 0;
					foreach($line as $el)
					{
						//$tokenoutput .= "[$el]($xy)<br />\n"; //Debugging info
						if ($xy < $elements)
						{ if ($xy == 0) {$tid_temp = $el;}
							if ($xy == 1) {$firstname = $el;}
							if ($xy == 2) {$lastname = $el;}
							if ($xy == 3) {$email = trim($el);}
							if ($xy == 4) {$token = trim($el);}
							if ($xy == 5) {$language = trim($el);}
							if ($xy == 6) {$attribute1 = trim($el);}
							if ($xy == 7) {$attribute2 = trim($el);}
						}
						$xy++;
					}
					//CHECK FOR DUPLICATES?
					$iq = "INSERT INTO ".db_table_name("tokens_$surveyid")." \n"
					. "(firstname, lastname, email, token, language, attribute_1, attribute_2";
					$iq .=") \n"
					. "VALUES ('$firstname', '$lastname', '$email', '$token', '$language' , '$attribute1', '$attribute2'";
					$iq .= ")";
					//$tokenoutput .= "<pre style='text-align: left'>$iq</pre>\n"; //Debugging info
					$ir = $connect->Execute($iq) or die ("Couldn't insert line<br />\n$buffer<br />\n".htmlspecialchars($connect->ErrorMsg())."<pre style='text-align: left'>$iq</pre>\n");
					$xz++;
				}
			}
			$xx++;
		}
		$tokenoutput .= "<font color='green'>".$clang->gT("Success")."</font><br /><br />\n";
		$message=str_replace("{TOKENCOUNT}", $xz, $clang->gT("{TOKENCOUNT} Records Created"));
		$tokenoutput .= "<i>$message</i><br />\n";
		unlink($the_full_file_path);
	}
	$tokenoutput .= "\t\t\t</td></tr></table>\n";
}

if ($subaction == "uploadldap") {
	$tokenoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Uploading LDAP Query")."</strong></td></tr>\n"
	."\t<tr><td align='center'>\n";
	$ldapq=$_POST['ldapQueries']; // the ldap query id

	$ldap_server_id=$ldap_queries[$ldapq]['ldapServerId'];
	$ldapserver=$ldap_server[$ldap_server_id]['server'];
	$ldapport=$ldap_server[$ldap_server_id]['port'];

	// define $attrlist: list of attributes to read from users' entries
	$attrparams = array('firstname_attr','lastname_attr',
			'email_attr','token_attr', 'language',
			'attr1', 'attr2');
	foreach ($attrparams as $id => $attr) {
		if (array_key_exists($attr,$ldap_queries[$ldapq]) &&
		    $ldap_queries[$ldapq][$attr] != '') {
			$attrlist[]=$ldap_queries[$ldapq][$attr];
		}
	}

	// Open connection to server
	$ds = ldap_getCnx($ldap_server_id);

	if ($ds) {
		// bind to server
		$resbind=ldap_bindCnx($ds, $ldap_server_id);

		if ($resbind) {
			$ResArray=array();
			$resultnum=ldap_doTokenSearch($ds, $ldapq, $ResArray);

			if ($resultnum >= 1) {
				foreach ($ResArray as $responseGroupId => $responseGroup) {
					for($j = 0;$j < $responseGroup['count']; $j++) {
						$mytoken='';
						$mylanguage='';
						$myfirstname = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['firstname_attr']]);
						$mylastame = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['lastname_attr']]);
						$myemail = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['email_attr']]);
						if ( ! empty($responseGroup[$j][$ldap_queries[$ldapq]['token_attr']]) ) $mytoken = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['token_attr']]);
						if ( ! empty($responseGroup[$j][$ldap_queries[$ldapq]['attr1']]) ) $myattr1 = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['attr1']]);
						if ( ! empty($responseGroup[$j][$ldap_queries[$ldapq]['attr2']]) ) $myattr2 = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['attr2']]);
						if ( ! empty($responseGroup[$j][$ldap_queries[$ldapq]['language']]) ) $mylanguage = ldap_readattr($response[$ldap_queries[$ldapq]['language']]);

						$iq = "INSERT INTO ".db_table_name("tokens_$surveyid")." \n"
						. "(firstname, lastname, email, token, language";
						if (isset($myattr1)) {$iq .= ", attribute_1";}
						if (isset($myattr2)) {$iq .= ", attribute_2";}
						$iq .=") \n"
						. "VALUES ('$myfirstname', '$mylastame', '$myemail', '$mytoken', '$mylanguage'";
						if (isset($myattr1)) {$iq .= ", '$myattr1'";}
						if (isset($myattr2)) {$iq .= ", '$myattr2'";}
						$iq .= ")";
						$ir = $connect->Execute($iq) or die ("Couldn't insert line<br />\n$buffer<br />\n".htmlspecialchars($connect->ErrorMsg())."<pre style='text-align: left'>$iq</pre>\n");
					} // End for each entry
				} // End foreach responseGroup
			} // End of if resnum >= 1

			$tokenoutput .= "<font color='green'>".$clang->gT("Success")."</font><br /><br>\n";
			$message=str_replace("{TOKENCOUNT}", $resultnum, $clang->gT("{TOKENCOUNT} Records Created"));
			$tokenoutput .= "<i>$message</i><br />\n";
		}
		else {
			$errormessage="<strong><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("Can't bind to the LDAP directory")."</strong>\n";
			formldap($errormessage);
		}
		@ldap_close($ds);
	}
	else {
		$errormessage="<strong><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("Can't connect to the LDAP directory")."</strong>\n";
		formldap($errormessage);
	}
}


//$tokenoutput .= "</center>\n";
//$tokenoutput .= "&nbsp;"
$tokenoutput .= "\t\t<table><tr><td></td></tr></table>\n"
."\t\t</td>\n";

//$tokenoutput .= "</td>\n";
$tokenoutput .= helpscreen()
."</tr></table>\n";



function form($error=false)
{
	global $surveyid, $tokenoutput,$scriptname;

	if ($error) {$tokenoutput .= $error . "<br /><br />\n";}

	$tokenoutput .= "<form enctype='multipart/form-data' action='$scriptname?action=tokens' method='post'>\n"
	. "<input type='hidden' name='subaction' value='upload' />\n"
	. "<input type='hidden' name='sid' value='$surveyid' />\n"
	. "Upload a file<br />\n"
	. "<input type='file' name='the_file' size='35' /><br />\n"
	. "<input type='submit' value='Upload' />\n"
	. "</form>\n\n";

} # END form

function formldap($error=false)
{
	global $surveyid, $tokenoutput, $ldap_queries;

	if ($error) {$tokenoutput .= $error . "<br /><br />\n";}

	if (! isset($ldap_queries) || ! is_array($ldap_queries) || count($ldap_queries) == 0) {
		$tokenoutput .= '<br />';
		$tokenoutput .= $clang->gT('LDAP is disabled or no LDAP query defined.');
		$tokenoutput .= '<br /><br /><br />';
		$tokenoutput .= '</center>';
	}
	else {
		$tokenoutput .= '<br />\n';
		$tokenoutput .= _('Select the LDAP query you want:');
		$tokenoutput .= '<br />';
		$tokenoutput .= "<form method='post' action='" . $_SERVER['PHP_SELF'] . "?action=tokens' method='post'>";
		$tokenoutput .= "<select name='ldapQueries' style='length=35'><br />";
		foreach ($ldap_queries as $q_number => $q) {
			$tokenoutput .= " <option value=".$q_number.">".$q['name']."</option>";
		}
		$tokenoutput .= "</select><br />";
		$tokenoutput .= "<input type='hidden' name='sid' value='$surveyid' />";
		$tokenoutput .= "<input type='hidden' name='subaction' value='uploadldap' />";
		$tokenoutput .= "<input type='submit' name='submit'>";
		$tokenoutput .= '</form></font>';
	}
}

function getLine($file)
{
	$buffer="";
	// iterate over each character in line.
	while (!feof($file))
	{
		// append the character to the buffer.
		$character = fgetc($file);
		$buffer .= $character;
		// check for end of line.
		if (($character == "\n") or ($character == "\r"))
		{
			// checks if the next character is part of the line ending, as in
			// the case of windows '\r\n' files, or not as in the case of
			// mac classic '\r', and unix/os x '\n' files.
			$character = fgetc($file);
			if ($character == "\n")
			{
				// part of line ending, append to buffer.
				$buffer .= $character;
			}
			else
			{
				// not part of line ending, roll back file pointer.
				fseek($file, -1, SEEK_CUR);
			}
			// end of line, so stop reading.
			break;
		}
	}
	// return the line buffer.
	return $buffer;
}

function randomkey($length)
{
  $pattern = "1234567890";
  for($i=0;$i<$length;$i++)
  {
   if(isset($key))
     $key .= $pattern{rand(0,9)};
   else
     $key = $pattern{rand(0,9)};
  }
  return $key;
}

?>
