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

# TOKENS FILE

$THISOS=""; //SET TO "solaris" if you are using solaris and experiencing the random number bug
require_once(dirname(__FILE__).'/../config.php');
if (!isset($action)) {$action=returnglobal('action');}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($order)) {$order=returnglobal('order');}
if (!isset($limit)) {$limit=returnglobal('limit');}
if (!isset($start)) {$start=returnglobal('start');}
if (!isset($searchstring)) {$searchstring=returnglobal('searchstring');}

sendcacheheaders();

if ($action == "delete") {echo str_replace("<head>\n", "<head>\n<meta http-equiv=\"refresh\" content=\"2;URL={$_SERVER['PHP_SELF']}?action=browse&amp;sid={$_GET['sid']}&amp;start=$start&amp;limit=$limit&amp;order=$order\"", $htmlheader);}
else {echo $htmlheader;}

//Show Help
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

echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n"
	."\t<tr>\n"
	."\t\t<td valign='top' align='center' bgcolor='#BBBBBB'>\n"
	."\t\t<table><tr><td></td></tr></table>\n";

echo "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";

// MAKE SURE THAT THERE IS A SID
if (!isset($surveyid) || !$surveyid)
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._TOKENCONTROL.":</strong></font></td></tr>\n"
		."\t<tr><td align='center'>$setfont<br /><font color='red'><strong>"
		._ERROR."</strong></font><br />"._TC_NOSID."<br /><br />"
		."<input $btstyle type='submit' value='"
		._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\"><br /><br /></td></tr>\n"
		."</table>\n"
		."</body>\n</html>";
	exit;
	}

// MAKE SURE THAT THE SURVEY EXISTS
$chquery = "SELECT * FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
$chresult=db_execute_assoc($chquery);
$chcount=$chresult->RecordCount();
if (!$chcount)
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._TOKENCONTROL.":</strong></font></td></tr>\n"
		."\t<tr><td align='center'>$setfont<br /><font color='red'><strong>"
		._ERROR."</strong></font><br />"._DE_NOEXIST
		."<br /><br />\n\t<input $btstyle type='submit' value='"
		._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\"><br /><br /></td></tr>\n"
		."</table>\n"
		."</body>\n</html>";
	exit;
	}
// A survey DOES exist
while ($chrow = $chresult->FetchRow())
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._TOKENCONTROL.":</strong> "
		."<font color='silver'>{$chrow['short_title']}</font></font></td></tr>\n";
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
						  . "sent varchar(15) NULL DEFAULT 'N',\n "
						  . "completed varchar(15) NULL DEFAULT 'N',\n "
						  
						  . "attribute_1 varchar(100) NULL,\n" 
						  . "attribute_2 varchar(100) NULL,\n"
						  . "mpid int NULL,\n"
						  . "PRIMARY KEY (tid),\n"
						  . "INDEX (token)) TYPE=MyISAM;";
		$ctresult = $connect->Execute($createtokentable) or die ("Completely mucked up<br />$createtokentable<br /><br />".htmlspecialchars($connect->ErrorMsg()));
		echo "\t<tr>\n"
			."\t\t<td align='center'>\n"
			."\t\t\t$setfont<br /><br />\n"
			."\t\t\t"._TC_CREATED." (\"tokens_$surveyid\")<br /><br />\n"
			."\t\t\t<input type='submit' $btstyle value='"
			._CONTINUE."' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid', '_top')\">\n"
			."\t\t</font></td>\n"
			."\t</tr>\n"
			."</table>\n"
			."<table><tr><td></td></tr></table>\n"
			."</td></tr></table>\n"
			.getAdminFooter("$langdir/instructions.html", "Information about PHPSurveyor Tokens Functions");
		exit;
		}
	elseif (isset($_GET['restoretable']) && $_GET['restoretable'] == "Y" && isset($_GET['oldtable']) && $_GET['oldtable'])
		{
		$query = "RENAME TABLE ".db_quote_id($_GET['oldtable'])." TO ".db_table_name{"tokens_$surveyid"});
		$result=$connect->Execute($query) or die("Failed Rename!<br />".$query."<br />".htmlspecialchars($connect->ErrorMsg()));
		echo "\t<tr>\n"
			."\t\t<td align='center'>\n"
			."\t\t\t$setfont<br /><br />\n"
			."\t\t\t"._TC_CREATED." (\"tokens_$surveyid\")<br /><br />\n"
			."\t\t\t<input type='submit' $btstyle value='"
			._CONTINUE."' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid', '_top')\">\n"
			."\t\t</font></td>\n"
			."\t</tr>\n"
			."</table>\n"
			."<table><tr><td></td></tr></table>\n"
			."</td></tr></table>\n"
			.getAdminFooter("$langdir/instructions.html", "Information about PHPSurveyor Tokens Functions");
		exit;
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
		echo "\t<tr>\n"
			."\t\t<td align='center'>\n"
			."\t\t\t$setfont<br /><font color='red'><strong>"._WARNING."</strong></font><br />\n"
			."\t\t\t<strong>"._TC_NOTINITIALISED."</strong><br /><br />\n"
			."\t\t\t"._TC_INITINFO
			."\t\t\t<br /><br />\n"
			."\t\t\t"._TC_INITQ;
		echo "<br /><br />\n";
		echo "\t\t\t<input type='submit' $btstyle value='"
			._TC_INITTOKENS."' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid&amp;createtable=Y', '_top')\"><br />\n"
			."\t\t\t<input type='submit' $btstyle value='"
			._GO_ADMIN."' onClick=\"window.open('$homeurl/admin.php?sid=$surveyid', '_top')\"><br /><br /></font>\n";
		if ($tcount>0)
			{
			echo "<table width='350' border='0' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'><tr>\n"
				 ."<td bgcolor='#666666'>$setfont<font color='white' size='1'>Restore Options:\n"
				 ."</font></font></td></tr>\n"
				 ."<tr>\n"
				 ."<td bgcolor='#DDDDDD' align='center'><form action='$homeurl/tokens.php'>$setfont\n"
				 ."The following old token tables could be restored:<br />\n"
				 ."<select $slstyle2 size='4' name='oldtable'>\n";
			foreach($oldlist as $ol)
				{
				echo "<option>".$ol."</option>\n";
				}
			echo "</select><br />\n"
				 ."<input type='submit' $btstyle value='Restore'>\n"
				 ."<input type='hidden' name='restoretable' value='Y'>\n"
				 ."<input type='hidden' name='sid' value='$surveyid'>\n"
				 ."</font></form></td>\n"
				 ."</tr></table>\n";
			}

		echo "\t\t</td>\n"
			."\t</tr>\n"
			."</table>\n"
			."<table><tr><td></td></tr></table>\n"
			."</td></tr></table>\n"
			.getAdminFooter("$langdir/instructions.html", "Information about PHPSurveyor Tokens Functions");
		exit;
		}
	}

#Lookup the names of the attributes
$query = "SELECT attribute1, attribute2 FROM {$dbprefix}surveys WHERE sid=$surveyid";
$result = mysql_query($query) or die("Couldn't execute query: <br />$query<br />".mysql_error());
$row = mysql_fetch_array($result);
if ($row["attribute1"]) {$attr1_name = $row["attribute1"];} else {$attr1_name=_TL_ATTR1;}
if ($row["attribute2"]) {$attr2_name = $row["attribute2"];} else {$attr2_name=_TL_ATTR2;}

// IF WE MADE IT THIS FAR, THEN THERE IS A TOKENS TABLE, SO LETS DEVELOP THE MENU ITEMS
echo "\t<tr bgcolor='#999999'>\n"
	."\t\t<td>\n"
	."\t\t\t<input type='image' name='HelpButton' src='$imagefiles/showhelp.png' title='"
	._A_HELP_BT."' align='right'  onClick=\"showhelp('show')\">\n"
	."\t\t\t<input type='image' name='HomeButton' src='$imagefiles/home.png' title='"
	._B_ADMIN_BT."' align='left' onClick=\"window.open('$scriptname?sid=$surveyid', '_top')\">\n"
	."\t\t\t<img src='$imagefiles/blank.gif' alt='' width='11' border='0' hspace='0' align='left'>\n"
	."\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
	."\t\t\t<input type='image' name='SummaryButton' src='$imagefiles/summary.png' title='"
	._B_SUMMARY_BT."' align='left' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid', '_top')\">\n"
	."\t\t\t<input type='image' name='ViewAllButton' src='$imagefiles/document.png' title='"
	._T_ALL_BT."' align='left' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid&amp;action=browse', '_top')\">\n"
	."\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' border='0' hspace='0' align='left'>\n"
	."\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
	."\t\t\t<input type='image' name='AddNewButton' src='$imagefiles/add.png' title='"
	._T_ADD_BT."' align='left' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid&amp;action=addnew', '_top')\">\n"
	."\t\t\t<input type='image' name='ImportButton' src='$imagefiles/importcsv.png' title='"
	._T_IMPORT_BT."' align='left' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid&amp;action=import', '_top')\">\n"
	."\t\t\t<input type='image' name='ExportButton' src='$imagefiles/exportcsv.png' title='"
	._T_EXPORT_BT."' align='left' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid&amp;action=export', '_top')\">\n"
	."\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
	."\t\t\t<input type='image' name='InviteButton' src='$imagefiles/invite.png' title='"
	._T_INVITE_BT."' align='left' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid&amp;action=email', '_top')\">\n"
	."\t\t\t<input type='image' name='RemindButton' src='$imagefiles/remind.png' title='"
	._T_REMIND_BT."' align='left' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid&amp;action=remind', '_top')\">\n"
	."\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
	."\t\t\t<input type='image' name='TokenifyButton' src='$imagefiles/tokenify.png' title='"
	._T_TOKENIFY_BT."' align='left' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid&amp;action=tokenify', '_top')\">\n"
	."\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
	."\t\t\t<input type='image' name='DeleteTokensButton' src='$imagefiles/delete.png' title='"
	._T_KILL_BT."' align='left' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid&amp;action=kill', '_top')\">\n"
	."\t\t</td>\n"
	."\t</tr>\n";

// SEE HOW MANY RECORDS ARE IN THE TOKEN TABLE
$tkcount = $tkresult->RecordCount();

echo "\t<tr><td align='center'><br /></td></tr>\n";
// GIVE SOME INFORMATION ABOUT THE TOKENS
echo "\t<tr>\n"
	."\t\t<td align='center'>\n"
	."\t\t\t<table align='center' bgcolor='#DDDDDD' cellpadding='2' style='border: 1px solid #555555'>\n"
	."\t\t\t\t<tr>\n"
	."\t\t\t\t\t<td align='center'>\n"
	."\t\t\t\t\t$setfont<strong>"._TC_TOTALCOUNT." $tkcount</strong><br />\n";
$tksq = "SELECT count(*) FROM ".db_table_name("tokens_$surveyid")." WHERE token IS NULL OR token=''";
$tksr = db_execute_num($tksq);
while ($tkr = $tksr->FetchRow())
	{echo "\t\t\t\t\t\t"._TC_NOTOKENCOUNT." $tkr[0] / $tkcount<br />\n";}
	
$tksq = "SELECT count(*) FROM ".db_table_name("tokens_$surveyid")." WHERE (sent!='N' and sent<>'')";

$tksr = db_execute_num($tksq);
while ($tkr = $tksr->FetchRow())
	{echo "\t\t\t\t\t\t"._TC_INVITECOUNT." $tkr[0] / $tkcount<br />\n";}
$tksq = "SELECT count(*) FROM ".db_table_name("tokens_$surveyid")." WHERE (completed!='N' and completed<>'')";

$tksr = db_execute_num($tksq);
while ($tkr = $tksr->FetchRow())
	{echo "\t\t\t\t\t\t"._TC_COMPLETEDCOUNT." $tkr[0] / $tkcount\n";}
echo "\t\t\t\t\t</font></td>\n"
	."\t\t\t\t</tr>\n"
	."\t\t\t</table>\n"
	."\t\t\t<br />\n"
	."\t\t</td>\n"
	."\t</tr>\n"
	."</table>\n"
	."<table ><tr><td></td></tr></table>\n";

echo "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";

#############################################################################################
// NOW FOR VARIOUS ACTIONS:

if ($action == "deleteall")
	{
	$query="DELETE FROM ".db_table_name("tokens_$surveyid");
	$result=$connect->Execute($query) or die ("Couldn't update sent field<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
	echo "<tr><td bgcolor='silver' align='center'><strong>$setfont<font color='green'>"._TC_ALLDELETED."</font></font></strong></td></tr>\n";
	$action="";
	}

if ($action == "clearinvites")
	{
	$query="UPDATE ".db_table_name("tokens_$surveyid")." SET sent='N'";
	$result=$connect->Execute($query) or die ("Couldn't update sent field<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
	echo "<tr><td bgcolor='silver' align='center'><strong>$setfont<font color='green'>"._TC_INVITESCLEARED."</font></font></strong></td></tr>\n";
	$action="";
	}

if ($action == "cleartokens")
	{
	$query="UPDATE ".db_table_name("tokens_$surveyid")." SET token=''";
	$result=$connect->Execute($query) or die("Couldn't reset the tokens field<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
	echo "<tr><td align='center' bgcolor='silver'><strong>$setfont<font color='green'>"._TC_TOKENSCLEARED."</font></font></strong></td></tr>\n";
	$action="";
	}

if ($action == "updatedb" && $surveyid)
	{
	$query = "ALTER TABLE `tokens_$surveyid`\n"
		   . "ADD `attribute_1` varchar(100) NULL,\n"
		   . "ADD `attribute_2` varchar(100) NULL,\n"
		   . "ADD `mpid` int NULL";
	if ($result = $connect->Execute($query))
		{
		echo "<tr><td align='center'>"._SUCCESS."</td></tr>\n";
		$action="";
		}
	else
		{
		echo "<tr><td align='center'>"._ERROR."</td></tr>\n";
		$action="";
		}
	}
	
if (!$action)
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._TOKENDBADMIN.":</strong></font></td></tr>\n"
		."\t<tr>\n"
		."\t\t<td align='center'>\n"
		."\t\t\t<table align='center'><tr><td>\n"
		."\t\t\t<br />\n"
		."\t\t\t<ul><li><a href='$homeurl/tokens.php?sid=$surveyid&amp;action=clearinvites' onClick='return confirm(\""
		._TC_CLEARINV_RUSURE."\")'>"._TC_CLEARINVITES."</a></li>\n"
		."\t\t\t<li><a href='$homeurl/tokens.php?sid=$surveyid&amp;action=cleartokens' onClick='return confirm(\""
		._TC_CLEARTOKENS_RUSURE."\")'>"._TC_CLEARTOKENS."</a></li>\n"
		."\t\t\t<li><a href='$homeurl/tokens.php?sid=$surveyid&amp;action=deleteall' onClick='return confirm(\""
		._TC_DELETEALL_RUSURE."\")'>"._TC_DELETEALL."</a></li>\n";
	$bquery = "SELECT * FROM ".db_table_name("tokens_$surveyid")." LIMIT 1";
	$bresult = $connect->Execute($bquery) or die(_ERROR." counting fields<br />".htmlspecialchars($connect->ErrorMsg()));
	$bfieldcount=$bresult->FieldCount();
	if ($bfieldcount==7)
		{
		echo "\t\t\t<li><a href='$homeurl/tokens.php?sid=$surveyid&amp;action=updatedb'>"._TC_UPDATEDB."</a></li>\n";
		}
	echo "\t\t\t<li><a href='$homeurl/tokens.php?sid=$surveyid&amp;action=kill'>"._T_KILL_BT."</a></li></ul>\n"
		."\t\t\t</td></tr></table>\n"
		."\t\t</td>\n"
		."\t</tr>\n"
		."</table>\n";
	}

if ($action == "browse" || $action == "search")
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
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._VIEWCONTROL.":</strong></font></td></tr>\n"
		."\t<tr bgcolor='#999999'><td align='left'>\n"
		."\t\t\t<img src='$imagefiles/blank.gif' alt='' width='31' height='20' border='0' hspace='0' align='left'>\n"
		."\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
		."\t\t\t<input type='image' name='DBeginButton' align='left' src='$imagefiles/databegin.png' title='"
		._D_BEGIN."' onClick=\"window.open('$homeurl/tokens.php?action=browse&amp;sid=$surveyid&amp;start=0&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring','_top')\" />\n"
		."\t\t\t<input type='image' name='DBackButton' align='left' src='$imagefiles/databack.png' title='"
		._D_BACK."' onClick=\"window.open('$homeurl/tokens.php?action=browse&amp;sid=$surveyid&amp;start=$last&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring','_top')\" />\n"
		."\t\t\t<img src='$imagefiles/blank.gif' alt='' width='13' height='20' border='0' hspace='0' align='left'>\n"
		."\t\t\t<input type='image' name='DForwardButton' align='left' src='$imagefiles/dataforward.png' title='"
		._D_FORWARD."' onClick=\"window.open('$homeurl/tokens.php?action=browse&amp;sid=$surveyid&amp;start=$next&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring','_top')\" />\n"
		."\t\t\t<input type='image' name='DEndButton' align='left'  src='$imagefiles/dataend.png' title='"
		._D_END."' onClick=\"window.open('$homeurl/tokens.php?action=browse&amp;sid=$surveyid&amp;start=$end&amp;limit=$limit&amp;order=$order&amp;searchstring=$searchstring','_top')\" />\n"
		."\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
		."\t\t\t\n"
		."\t\t\t<table align='left' cellpadding='0' cellspacing='0' border='0'>\n"
		."\t\t\t\t<tr><td><form method='post' action='$homeurl/tokens.php'>\n"
		."\t\t\t\t\t<input $slstyle type='text' name='searchstring' value='$searchstring'>\n"
		."\t\t\t\t\t<input $btstyle type='submit' value='"._SEARCH."'>\n"
		."\t\t\t\t<input type='hidden' name='order' value='$order'>\n"
		."\t\t\t\t<input type='hidden' name='action' value='search'>\n"
		."\t\t\t\t<input type='hidden' name='sid' value='$surveyid'>\n"
		."\t\t\t\t</form></td>\n"
		."\t\t\t</tr></table>\n"
		."\t\t</td>\n"
		."\t\t<td align='right'><form action='$homeurl/tokens.php'>\n"
		."\t\t<font size='1' face='verdana'>"
		."&nbsp;"._BR_DISPLAYING."<input type='text' $slstyle size='4' value='$limit' name='limit'>"
		."&nbsp;"._BR_STARTING."<input type='text' $slstyle size='4' value='$start' name='start'>"
		."&nbsp;<input type='submit' value='"._BR_SHOW."' $btstyle>\n"
		."\t\t</font>\n"
		."\t\t<input type='hidden' name='sid' value='$surveyid'>\n"
		."\t\t<input type='hidden' name='action' value='browse'>\n"
		."\t\t<input type='hidden' name='order' value='$order'>\n"
		."\t\t<input type='hidden' name='searchstring' value='$searchstring'>\n"
		."\t\t</form></td>\n"
		."\t</tr>\n";
	$bquery = "SELECT * FROM ".db_table_name("tokens_$surveyid")." LIMIT 1";
	$bresult = $connect->Execute($bquery) or die(_ERROR." counting fields<br />".htmlspecialchars($connect->ErrorMsg()));
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
	$bresult = db_execute_assoc($bquery) or die (_ERROR.": $bquery<br />".htmlspecialchars($connect->ErrorMsg()));
	$bgc="";
	
	echo "<tr><td colspan='2'>\n"
		."<table width='100%' cellpadding='1' cellspacing='1' align='center' bgcolor='#CCCCCC'>\n";
	//COLUMN HEADINGS
	echo "\t<tr>\n"
		."\t\t<th align='left' valign='top'>"
		."<a href='$homeurl/tokens.php?sid=$surveyid&amp;action=browse&amp;order=tid&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
		."<img src='$imagefiles/downarrow.png' alt='"
		._TC_SORTBY."ID' border='0' align='left' hspace='0'></a>$setfont"."ID</font></th>\n"
		."\t\t<th align='left' valign='top'>"
		."<a href='$homeurl/tokens.php?sid=$surveyid&amp;action=browse&amp;order=firstname&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
		."<img src='$imagefiles/downarrow.png' alt='"
		._TC_SORTBY._TL_FIRST."' border='0' align='left'></a>$setfont"._TL_FIRST."</font></th>\n"
		."\t\t<th align='left' valign='top'>"
		."<a href='$homeurl/tokens.php?sid=$surveyid&amp;action=browse&amp;order=lastname&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
		."<img src='$imagefiles/downarrow.png' alt='"
		._TC_SORTBY._TL_LAST."' border='0' align='left'></a>$setfont"._TL_LAST."</font></th>\n"
		."\t\t<th align='left' valign='top'>"
		."<a href='$homeurl/tokens.php?sid=$surveyid&amp;action=browse&amp;order=email&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
		."<img src='$imagefiles/downarrow.png' alt='"
		._TC_SORTBY._TL_EMAIL."' border='0' align='left'></a>$setfont"._TL_EMAIL."</font></th>\n"
		."\t\t<th align='left' valign='top'>"
		."<a href='$homeurl/tokens.php?sid=$surveyid&amp;action=browse&amp;order=token&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
		."<img src='$imagefiles/downarrow.png' alt='"
		._TC_SORTBY._TL_TOKEN."' border='0' align='left'></a>$setfont"._TL_TOKEN."</font></th>\n"
		."\t\t<th align='left' valign='top'>"
		."<a href='$homeurl/tokens.php?sid=$surveyid&amp;action=browse&amp;order=sent%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
		."<img src='$imagefiles/downarrow.png' alt='"
		._TC_SORTBY._TL_INVITE."' border='0' align='left'></a>$setfont"._TL_INVITE."</font></th>\n"
		."\t\t<th align='left' valign='top'>"
		."<a href='$homeurl/tokens.php?sid=$surveyid&amp;action=browse&amp;order=completed%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
		."<img src='$imagefiles/downarrow.png' alt='"
		._TC_SORTBY._TL_DONE."' border='0' align='left'></a>$setfont"._TL_DONE."</font></th>\n";
	if ($bfieldcount == 9) 
		{
		echo "\t\t<th align='left' valign='top'>"
			."<a href='$homeurl/tokens.php?sid=$surveyid&amp;action=browse&amp;order=attribute_1&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
			."<img src='$imagefiles/downarrow.png' alt='"
			._TC_SORTBY._TL_ATTR1."' border='0' align='left'></a>$setfont".$attr1_name."</font></th>\n"
			."\t\t<th align='left' valign='top'>"
			."<a href='$homeurl/tokens.php?sid=$surveyid&amp;action=browse&amp;order=attribute_2&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
			."<img src='$imagefiles/downarrow.png' alt='"
			._TC_SORTBY._TL_ATTR2."' border='0' align='left'></a>$setfont".$attr2_name."</font></th>\n"
			."\t\t<th align='left' valign='top'>"
			."<a href='$homeurl/tokens.php?sid=$surveyid&amp;action=browse&amp;order=mpid&amp;start=$start&amp;limit=$limit&amp;searchstring=$searchstring'>"
			."<img src='$imagefiles/downarrow.png' alt='"
			._TC_SORTBY._TL_MPID."' border='0' align='left'></a>$setfont"._TL_MPID."</font></th>\n";
		}
	echo "\t\t<th align='left' valign='top' colspan='2'>$setfont"._TL_ACTION."</font></th>\n"
		."\t</tr>\n";
	
	while ($brow = $bresult->FetchRow())
		{
		$brow['token'] = trim($brow['token']);
		if ($bgc == "#EEEEEE") {$bgc = "#DDDDDD";} else {$bgc = "#EEEEEE";}
		echo "\t<tr bgcolor='$bgc'>\n";
		for ($i=0; $i<=$bfieldcount; $i++)
			{
			echo "\t\t<td>$setfont$brow[$i]</font></td>\n";
			}
		echo "\t\t<td align='left'>\n"
			."\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='submit' value='E' title='"
			._TC_EDIT."' onClick=\"window.open('{$_SERVER['PHP_SELF']}?sid=$surveyid&amp;action=edit&amp;tid=$brow[0]', '_top')\" />"
			."<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='submit' value='D' title='"
			._TC_DEL."' onClick=\"window.open('{$_SERVER['PHP_SELF']}?sid=$surveyid&amp;action=delete&amp;tid=$brow[0]&amp;limit=$limit&amp;start=$start&amp;order=$order', '_top')\" />";

		if (($brow['completed'] == "N" || $brow['completed'] == "") &&$brow['token']) {echo "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='submit' value='S' title='"._TC_DO."' onClick=\"window.open('$publicurl/index.php?sid=$surveyid&amp;token=".trim($brow['token'])."', '_blank')\" />\n";}
		echo "\n\t\t</td>\n";
		if ($brow['completed'] != "N" && $brow['completed']!="" && $surveyprivate == "N")
			{
			echo "\t\t<form action='$homeurl/browse.php' method='post' target='_blank'>\n"
				."\t\t<td align='center' valign='top'>\n"
				."\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='submit' value='V' title='"
				._TC_VIEW."' />\n"
				."\t\t</td>\n"
				."\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
				."\t\t<input type='hidden' name='action' value='id' />\n"
				."\t\t<input type='hidden' name='sql' value=\"token='{$brow['token']}'\" />\n"
				."\t\t</form>\n";

				// TLR Add an UPDATE button to the tokens display in the MPID Actions column
				$query="SELECT id FROM ".db_table_name("survey_$surveyid")." WHERE token='$brow[4]'";
				$result=db_execute_num($query) or die ("<br />Could not find token!<br />\n" . htmlspecialchars($connect->ErrorMsg()));
				list($id) = $result->FetchRow();
				if  ($id)
                {
				    echo "\t\t<form action='$homeurl/dataentry.php' method='post' target='_blank'>\n"
				    ."\t\t<td align='center' valign='top'>\n"
				    ."\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='submit' value='U' title='"
				    ._TC_UPDATE."' />\n"
				    ."\t\t</td>\n"
				    ."\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
				    ."\t\t<input type='hidden' name='action' value='edit' />\n"
				    ."\t\t<input type='hidden' name='surveytable' value='survey_$surveyid' />\n"
				    ."\t\t<input type='hidden' name='id' value='$id' />\n"
				    ."\t\t</form>\n";
                }
			}
			
								// TLR change to put date into sent and completed
	//	elseif ($brow['completed'] != "Y" && $brow['token'] && $brow['sent'] != "Y")
	//	elseif ($brow['completed'] != "Y" && $brow['token'] && $brow['sent'] == "N")
		elseif ($brow['completed'] == "N" && $brow['token'] && $brow['sent'] == "N")
		
			{
			echo "\t\t<td align='center' valign='top'>\n"
				."\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='submit' value='I' title='"
				._TC_INVITET."' onClick=\"window.open('{$_SERVER['PHP_SELF']}?sid=$surveyid&amp;action=email&amp;tid=$brow[0]', '_top')\" />"
				."\t\t</td>\n";
			}
			
								// TLR change to put date into sent and completed
	//	elseif ($brow['completed'] != "Y" && $brow['token'] && $brow['sent'] == "Y")
	//	elseif ($brow['completed'] != "Y" && $brow['token'] && $brow['sent'] != "N")
		elseif ($brow['completed'] == "N" && $brow['token'] && $brow['sent'] != "N")
		
			{
			echo "\t\t<td align='center' valign='top'>\n"
				."\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='submit' value='R' title='"
				._TC_REMINDT."' onClick=\"window.open('{$_SERVER['PHP_SELF']}?sid=$surveyid&amp;action=remind&amp;tid=$brow[0]', '_top')\" />"
				."\t\t</td>\n";
			}
		else
			{
			echo "\t\t<td>\n"
				."\t\t</td>\n";
			}
		echo "\t</tr>\n";
		}
	echo "</table>\n"
		."</td></tr></table>\n";
	}

if ($action == "kill")
	{
	$date = date('YmdHi');
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4' align='center'>"
		."<font size='1' face='verdana' color='white'><strong>"
		._DROPTOKENS.":</strong></font></td></tr>\n"
		."\t<tr><td colspan='2' align='center'>\n"
		."$setfont<br />\n";
	if (!isset($_GET['ok']) || !$_GET['ok'])
		{
		echo "<font color='red'><strong>"._WARNING."</strong></font><br />\n"
			._TC_DELTOKENSINFO."<br />\n"
			."( \"old_tokens_{$_GET['sid']}_$date\" )<br /><br />\n"
			."<input type='submit' $btstyle value='"
			._TC_DELETETOKENS."' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid&amp;action=kill&amp;ok=surething', '_top')\" /><br />\n"
			."<input type='submit' $btstyle value='"
			._AD_CANCEL."' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid', '_top')\" />\n";
		}
	elseif (isset($_GET['ok']) && $_GET['ok'] == "surething")
		{
		$oldtable = "tokens_$surveyid";
		$newtable = "old_tokens_{$surveyid}_$date";
		$deactivatequery = "RENAME TABLE ".db_table_name($oldtable)." TO ".db_table_name($newtable);
		$deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't deactivate because:<br />\n".htmlspecialchars($connect->ErrorMsg())."<br /><br />\n<a href='$scriptname?sid=$surveyid'>Admin</a>\n");
		echo "<span style='display: block; text-align: center; width: 70%'>\n"
			._TC_TOKENSGONE."<br />\n"
			."(\"{$dbprefix}old_tokens_{$_GET['sid']}_$date\")"."<br /><br />\n"
			."<input type='submit' $btstyle value='"
			._GO_ADMIN."' onClick=\"window.open('$scriptname?sid={$_GET['sid']}', '_top')\" />\n"
			."</span>\n";
		}
	echo "</font></td></tr></table>\n"
		."<table><tr><td></td></tr></table>\n";

	}	


if (returnglobal('action') == "email")
	{
	echo "\t<tr bgcolor='#555555'>\n\t\t<td colspan='2' height='4'>"
		."<font size='1' face='verdana' color='white'><strong>"
		._EMAILINVITE.":</strong></font></td>\n\t</tr>\n"
		."\t<tr>\n\t\t<td colspan='2' align='center'>\n";
	if (!isset($_POST['ok']) || !$_POST['ok'])
		{
		//GET SURVEY DETAILS
		$thissurvey=getSurveyInfo($surveyid);
		if (!$thissurvey['email_invite']) {$thissurvey['email_invite']=str_replace("\n", "\r\n", _TC_EMAILINVITE);}

        $fieldsarray["{ADMINNAME}"]= $thissurvey['adminname'];
        $fieldsarray["{ADMINEMAIL}"]=$thissurvey['adminemail'];
        $fieldsarray["{SURVEYNAME}"]=$thissurvey['name'];
        $fieldsarray["{SURVEYDESCRIPTION}"]=$thissurvey['description'];

		$subject=Replacefields($thissurvey['email_invite_subj'], $fieldsarray);
		$textarea=Replacefields($thissurvey['email_invite'], $fieldsarray);

		echo "<form method='post' action='tokens.php?sid=$surveyid'><table width='100%' align='center' bgcolor='#DDDDDD'>\n"
			."\n";
		if (isset($_GET['tid']) && $_GET['tid']) 
			{
			echo "<tr><td bgcolor='silver' colspan='2'>$setfont<font size='1'>"
				."to TokenID No {$_GET['tid']}"
				."</font></font></td></tr>";
			}
		echo "\t<tr>\n"
			."\t\t<td align='right'>$setfont<strong>"._FROM.":</strong></font></td>\n"
			."\t\t<td><input type='text' $slstyle size='50' name='from' value=\"{$thissurvey['adminname']} <{$thissurvey['adminemail']}>\" /></td>\n"
			."\t</tr>\n"
			."\t<tr>\n"
			."\t\t<td align='right'>$setfont<strong>"._SUBJECT.":</strong></font></td>\n";
		echo "\t\t<td><input type='text' $slstyle size='50' name='subject' value=\"$subject\" /></td>\n"
			."\t</tr>\n"
			."\t<tr>\n"
			."\t\t<td align='right' valign='top'>$setfont<strong>"._MESSAGE.":</strong></font></td>\n"
			."\t\t<td>\n"
			."\t\t\t<textarea name='message' rows='10' cols='80' style='background-color: #EEEFFF; font-family: verdana; font-size: 10; color: #000080'>\n";
		echo $textarea;
		echo "\t\t\t</textarea>\n"
			."\t\t</td>\n"
			."\t</tr>\n"
			."\t<tr><td></td><td align='left'><input type='submit' $btstyle value='"
			._TC_SENDEMAIL."'>\n"
			."\t<input type='hidden' name='ok' value='absolutely' />\n"
			."\t<input type='hidden' name='sid' value='{$_GET['sid']}' />\n"
			."\t<input type='hidden' name='action' value='email' /></td></tr>\n";
		if (isset($_GET['tid']) && $_GET['tid']) {echo "\t<input type='hidden' name='tid' value='{$_GET['tid']}' />";}
		echo "\n"
			."</table></form>\n";
		}
	else
		{
		echo _TC_SENDINGEMAILS;
		$_POST['message']=auto_unescape($_POST['message']);
		$_POST['subject']=auto_unescape($_POST['subject']);
		if (isset($_POST['tid']) && $_POST['tid']) {echo " ("._TC_REMINDTID." {$_POST['tid']})";}
		echo "<br />\n";
		
		$ctquery = "SELECT * FROM ".db_table_name("tokens_{$_POST['sid']}")." WHERE ((completed ='N') or (completed='')) AND ((sent ='N') or (sent='')) AND token !='' AND email != ''";
		
		if (isset($_POST['tid']) && $_POST['tid']) {$ctquery .= " and tid='{$_POST['tid']}'";}
		echo "<!-- ctquery: $ctquery -->\n";
		$ctresult = $connect->Execute($ctquery) or die("Database error!<br />\n" . htmlspecialchars($connect->ErrorMsg()));
		$ctcount = $ctresult->RecordCount();
		$ctfieldcount = $ctresult->FieldCount();
		$emquery = "SELECT firstname, lastname, email, token, tid";
		if ($ctfieldcount > 7) {$emquery .= ", attribute_1, attribute_2";}
		
		$emquery .= " FROM ".db_table_name("tokens_{$_POST['sid']}")." WHERE ((completed ='N') or (completed='')) AND ((sent ='N') or (sent='')) AND token !='' AND email != ''";
		
		if (isset($_POST['tid']) && $_POST['tid']) {$emquery .= " and tid='{$_POST['tid']}'";}
		$emquery .= " LIMIT $maxemails";
		echo "\n\n<!-- emquery: $emquery -->\n\n";
		$emresult = db_execute_assoc($emquery) or die ("Couldn't do query.<br />\n$emquery<br />\n".htmlspecialchars($connect->ErrorMsg()));
		$emcount = $emresult->RecordCount();
		$from = $_POST['from'];
       
		echo "<table width='500px' align='center' bgcolor='#EEEEEE'>\n"
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
			// TLR change to put date into sent and completed
			//		$udequery = "UPDATE {$dbprefix}tokens_{$_POST['sid']} SET sent='Y' WHERE tid={$emrow['tid']}";	
					$today = date("Y-m-d Hi");	
					$udequery = "UPDATE ".db_table_name("tokens_{$_POST['sid']}")."\n"
					."SET sent='$today' WHERE tid={$emrow['tid']}";
			//		
					$uderesult = $connect->Execute($udequery) or die ("Couldn't update tokens<br />$udequery<br />".htmlspecialchars($connect->ErrorMsg()));
					echo "["._TC_INVITESENTTO."{$emrow['firstname']} {$emrow['lastname']} ($to)]<br />\n";
					}
				else
					{
					echo ReplaceFields(_TC_MAILTOFAILED, $fieldsarray);
					echo "<br /><pre>$headers<br />$message</pre>";
					}
				}
			if ($ctcount > $emcount)
				{
				$lefttosend = $ctcount-$maxemails;
				echo "\t\t</td>\n"
					."\t</tr>\n"
					."\t<tr>\n"
					."\t\t<td align='center'>$setfont<strong>"._WARNING."</strong><br />\n"
					."\t\t\t<form method='post'>\n"
					._TC_EMAILSTOGO."<br /><br />\n";
				echo str_replace("{EMAILCOUNT}", "$lefttosend", _TC_EMAILSREMAINING);
				echo "<br /><br />\n";
				$message = html_escape($_POST['message']);
				echo "\t\t\t<input type='submit' value='"._CONTINUE."' />\n"
					."\t\t\t<input type='hidden' name='ok' value=\"absolutely\" />\n"
					."\t\t\t<input type='hidden' name='action' value=\"email\" />\n"
					."\t\t\t<input type='hidden' name='sid' value=\"{$_POST['sid']}\" />\n"
					."\t\t\t<input type='hidden' name='from' value=\"{$_POST['from']}\" />\n"
					."\t\t\t<input type='hidden' name='subject' value=\"{$_POST['subject']}\" />\n"
					."\t\t\t<input type='hidden' name='message' value=\"$message\" />\n"
					."\t\t\t</form>\n";
				}
			}
		else
			{
			echo "<center><strong>"._WARNING."</strong><br />\n"._TC_NONETOSEND."</center>\n";
			}
		echo "\t\t</td>\n";
		
		}
	echo "</td></tr></table>\n";
	}
	
if (returnglobal('action') == "remind")
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._EMAILREMIND.":</strong></font></td></tr>\n"
		."\t<tr><td colspan='2' align='center'>\n";
	if (!isset($_POST['ok']) || !$_POST['ok'])
		{
		//GET SURVEY DETAILS
		$thissurvey=getSurveyInfo($surveyid);
		if (!$thissurvey['email_remind']) {$thissurvey['email_remind']=str_replace("\n", "\r\n", _TC_EMAILREMIND);}
		echo "<form method='post' action='$homeurl/tokens.php'><table width='100%' align='center' bgcolor='#DDDDDD'>\n"
			."\t\n"
			."\t<tr>\n"
			."\t\t<td align='right' width='150'>$setfont<strong>"._FROM.":</strong></font></td>\n"
			."\t\t<td><input type='text' $slstyle size='50' name='from' value=\"{$thissurvey['adminname']} <{$thissurvey['adminemail']}>\" /></td>\n"
			."\t</tr>\n"
			."\t<tr>\n"
			."\t\t<td align='right' width='150'>$setfont<strong>"._SUBJECT.":</strong></font></td>\n";
		$subject=str_replace("{SURVEYNAME}", $thissurvey['name'], $thissurvey['email_remind_subj']);
		echo "\t\t<td><input type='text' $slstyle size='50' name='subject' value='$subject' /></td>\n"
			."\t</tr>\n";
		if (!isset($_GET['tid']) || !$_GET['tid'])
			{
			echo "\t<tr>\n"
				."\t\t<td align='right' width='150' valign='top'>$setfont<strong>"
				._TC_REMINDSTARTAT."</strong></font></td>\n"
				."\t\t<td><input type='text' $slstyle size='5' name='last_tid' /></td>\n"
				."\t</tr>\n";
			}
		else
			{
			echo "\t<tr>\n"
				."\t\t<td align='right' width='150' valign='top'>$setfont<strong>"
				._TC_REMINDTID."</strong></font></td>\n"
				."\t\t<td>$setfont{$_GET['tid']}</font></td>\n"
				."\t</tr>\n";
			}
		echo "\t<tr>\n"
			."\t\t<td align='right' width='150' valign='top'>$setfont<strong>"
			._MESSAGE.":</strong></font></td>\n"
			."\t\t<td>\n"
			."\t\t\t<textarea name='message' rows='10' cols='80' style='background-color: #EEEFFF; font-family: verdana; font-size: 10; color: #000080'>\n";

		$textarea = $thissurvey['email_remind'];
		$textarea = str_replace("{ADMINNAME}", $thissurvey['adminname'], $textarea);
		$textarea = str_replace("{ADMINEMAIL}", $thissurvey['adminemail'], $textarea);
		$textarea = str_replace("{SURVEYNAME}", $thissurvey['name'], $textarea);
		$textarea = str_replace("{SURVEYDESCRIPTION}", $thissurvey['description'], $textarea);
		echo $textarea;

		echo "\t\t\t</textarea>\n"
			."\t\t</td>\n"
			."\t</tr>\n"
			."\t<tr>\n"
			."\t\t<td></td>\n"
			."\t\t<td align='left'>\n"
			."\t\t\t<input type='submit' $btstyle value='"._TC_SENDREMIND."' />\n"
			."\t<input type='hidden' name='ok' value='absolutely'>\n"
			."\t<input type='hidden' name='sid' value='{$_GET['sid']}'>\n"
			."\t<input type='hidden' name='action' value='remind'>\n"
			."\t\t</td>\n"
			."\t</tr>\n";
		if (isset($_GET['tid']) && $_GET['tid']) {echo "\t<input type='hidden' name='tid' value='{$_GET['tid']}'>\n";}
		echo "\t</table>\n"
			."</form>\n";
		}
	else
		{
		echo _TC_SENDINGREMINDERS."<br />\n";
		$_POST['message']=auto_unescape($_POST['message']);
		$_POST['subject']=auto_unescape($_POST['subject']);
		
		if (isset($_POST['last_tid']) && $_POST['last_tid']) {echo " ("._FROM." TID: {$_POST['last_tid']})";}
		if (isset($_POST['tid']) && $_POST['tid']) {echo " ("._TC_REMINDTID." TID: {$_POST['tid']})";}
		
		$ctquery = "SELECT * FROM ".db_table_name("tokens_{$_POST['sid']}")." WHERE (completed ='N' or completed ='') AND sent<>'' AND sent<>'N' AND token <>'' AND email <> ''";
		
		if (isset($_POST['last_tid']) && $_POST['last_tid']) {$ctquery .= " AND tid > '{$_POST['last_tid']}'";}
		if (isset($_POST['tid']) && $_POST['tid']) {$ctquery .= " AND tid = '{$_POST['tid']}'";}
		echo "<!-- ctquery: $ctquery -->\n";
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
		echo "<table width='500' align='center' bgcolor='#EEEEEE'>\n"
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
		        $fieldsarray["{ATTRIBUTE_1}"]=$emrow['attribute_1'];
		        $fieldsarray["{ATTRIBUTE_2}"]=$emrow['attribute_2'];

				$msgsubject=Replacefields($_POST['subject'], $fieldsarray);
				$sendmessage=Replacefields($_POST['message'], $fieldsarray);

				if (MailtextMessage($sendmessage, $msgsubject, $to, $from, $sitename))
					{
					echo "\t\t\t({$emrow['tid']})["._TC_REMINDSENTTO." {$emrow['firstname']} {$emrow['lastname']}]<br />\n";
					}
				else
					{
					echo "\t\t\t({$emrow['tid']})[Email to {$emrow['firstname']} {$emrow['lastname']} failed]<br />\n";
					}
				$lasttid = $emrow['tid'];
				}
			if ($ctcount > $emcount)
				{
				$lefttosend = $ctcount-$maxemails;
				echo "\t\t</td>\n"
					."\t</tr>\n"
					."\t<tr><form method='post' action='$homeurl/tokens.php'>\n"
					."\t\t<td align='center'>\n"
					."\t\t\t$setfont<strong>"._WARNING."</strong><br /><br />\n"
					._TC_EMAILSTOGO."<br /><br />\n"
					.str_replace("{EMAILCOUNT}", $lefttosend, _TC_EMAILSREMAINING)
					."<br />\n"
					."\t\t\t<input type='submit' value='"._CONTINUE."' />\n"
					."\t\t</td>\n"
					."\t<input type='hidden' name='ok' value=\"absolutely\" />\n"
					."\t<input type='hidden' name='action' value=\"remind\" />\n"
					."\t<input type='hidden' name='sid' value=\"{$_POST['sid']}\" />\n"
					."\t<input type='hidden' name='from' value=\"{$_POST['from']}\" />\n"
					."\t<input type='hidden' name='subject' value=\"{$_POST['subject']}\" />\n";
				$message = html_escape($_POST['message']);
				echo "\t<input type='hidden' name='message' value=\"$message\" />\n"
					."\t<input type='hidden' name='last_tid' value=\"$lasttid\" />\n"
					."\t</form>\n";
				}
			}
		else
			{
			echo "<center><strong>"._WARNING."</strong><br />\n"
				._TC_NOREMINDERSTOSEND."\n"
				."<br /><br />\n"
				."\t\t</td>\n";
			}
		echo "\t</tr>\n"
			."</table>\n";
		}
	echo "</td></tr></table>\n";
	}

if ($action == "tokenify")
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"._TOKENIFY.":</strong></font></td></tr>\n";
	echo "\t<tr><td align='center'>$setfont<br />\n";
	if (!isset($_GET['ok']) || !$_GET['ok'])
		{
		echo "<br />"._TC_CREATETOKENSINFO."<br /><br />\n"
			."<input type='submit' $btstyle value='"
			._AD_YES."' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid&amp;action=tokenify&amp;ok=Y', '_top')\" />\n"
			."<input type='submit' $btstyle value='"
			._AD_NO."' onClick=\"window.open('$homeurl/tokens.php?sid=$surveyid', '_top')\" />\n"
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
				if ($THISOS == "solaris")
					{
					$nt1=db_execute_num("SELECT RAND()");
					while ($row=$nt1->FetchRow()) {$newtoken=(int)(sprintf("%010s", $row[0]*1000000000));}
					}
				else
					{
					$newtoken = sprintf("%010s", rand(1, getrandmax()));
					}
				$ntquery = "SELECT * FROM ".db_table_name("tokens_$surveyid")." WHERE token='$newtoken'";
				$ntresult = $connect->Execute($ntquery);
				if (!$ntresult->RecordCount()) {$insert = "OK";}
				}
			$itquery = "UPDATE ".db_table_name("tokens_$surveyid")." SET token='$newtoken' WHERE tid={$tkrow['tid']}";
			$itresult = $connect->Execute($itquery);
			$newtokencount++;
			}
		$message=str_replace("{TOKENCOUNT}", $newtokencount, _TC_TOKENSCREATED);
		echo "<br /><strong>$message</strong><br /><br />\n";
		}
	echo "\t</font></td></tr></table>\n";
	}

if ($action == "export") //EXPORT FEATURE SUBMITTED BY PIETERJAN HEYSE
        {
        $bquery = "SELECT * FROM ".db_table_name("tokens_$surveyid");
        $bquery .= " ORDER BY tid";

        $bresult = db_execute_assoc($bquery) or die ("$bquery<br />".htmlspecialchars($connect->ErrorMsg()));
		$bfieldcount=$bresult->FieldCount();
		
        echo "\t<textarea rows=20 cols=120>\n";
		echo "Tid, Firstname, Lastname, Email, Token [, attribute1, attribute2, mpid]\n";
        while ($brow = $bresult->FetchRow())
                {
                echo trim($brow['tid']).",";
                echo trim($brow['firstname']).",";
                echo trim($brow['lastname']).",";
                echo trim($brow['email']).",";
                echo trim($brow['token']);
				if($bfieldcount > 7) 
					{
					echo ",";
					echo trim($brow['attribute_1']).",";
					echo trim($brow['attribute_2']).",";
					echo trim($brow['mpid']);
					}
                echo "\n";
				}
        echo "\n\t</textarea>\n";
	}

if ($action == "delete")
	{
	$dlquery = "DELETE FROM ".db_table_name("tokens_$surveyid")." WHERE tid={$_GET['tid']}";
	$dlresult = $connect->Execute($dlquery) or die ("Couldn't delete record {$_GET['tid']}<br />".htmlspecialchars($connect->ErrorMsg()));
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._DELETE."</strong></td></tr>\n"
		."\t<tr><td align='center'>$setfont<br />\n"
		."<br /><strong>"._TC_TOKENDELETED."</strong><br />\n"
		."<font size='1'><i>"._RELOADING."</i><br /><br /></font>\n"
		."\t</td></tr></table>\n";
	}

if ($action == "edit" || $action == "addnew")
	{
	if ($action == "edit")
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
	if ($action != "edit") 
		{
		$edquery = "SELECT * FROM ".db_table_name("tokens_$surveyid")." LIMIT 1";
		$edresult = $connect->Execute($edquery);
		$edfieldcount = $edresult->FieldCount();
		}
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._TC_ADDEDIT."</strong></font></td></tr>\n"
		."\t<tr><td align='center'>\n"
		."<form method='post' action='$homeurl/tokens.php'>\n"
		."<table width='100%' bgcolor='#CCCCCC' align='center'>\n"
		."<tr>\n"
		."\t<td align='right' width='20%'>$setfont<strong>ID:</strong></font></td>\n"
		."\t<td bgcolor='#EEEEEE'>{$setfont}Auto</font></td>\n"
		."</tr>\n"
		."<tr>\n"
		."\t<td align='right' width='20%'>$setfont<strong>"._TL_FIRST.":</strong></font></td>\n"
		."\t<td bgcolor='#EEEEEE'>$setfont<input type='text' $slstyle size='30' name='firstname' value=\"";
	if (isset($firstname)) {echo $firstname;}
	echo "\"></font></td>\n"
		."</tr>\n"
		."<tr>\n"
		."\t<td align='right' width='20%'>$setfont<strong>"._TL_LAST.":</strong></font></td>\n"
		."\t<td bgcolor='#EEEEEE'>$setfont<input type='text' $slstyle size='30' name='lastname' value=\"";
	if (isset($lastname)) {echo $lastname;}
	echo "\"></font></td>\n"
		."</tr>\n"
		."<tr>\n"
		."\t<td align='right' width='20%'>$setfont<strong>"._TL_EMAIL.":</strong></font></td>\n"
		."\t<td bgcolor='#EEEEEE'>$setfont<input type='text' $slstyle size='50' name='email' value=\"";
	if (isset($email)) {echo $email;}
	echo "\"></font></td>\n"
		."</tr>\n"
		."<tr>\n"
		."\t<td align='right' width='20%'>$setfont<strong>"._TL_TOKEN.":</strong></font></td>\n"
		."\t<td bgcolor='#EEEEEE'>$setfont<input type='text' size='15' $slstyle name='token' value=\"";
	if (isset($token)) {echo $token;}
	echo "\">\n";
	if ($action == "addnew")
		{
		echo "\t\t$setfont<font size='1' color='red'>"._TC_TOKENCREATEINFO."</font></font>\n";
		}
	echo "\t</font></td>\n"
		."</tr>\n"
		."<tr>\n"
		."\t<td align='right' width='20%'>$setfont<strong>"._TL_INVITE.":</strong></font></td>\n"
		
	// TLR change to put date into sent and completed
    //	."\t<td bgcolor='#EEEEEE'>$setfont<input type='text' size='1' $slstyle name='sent' value=\"";
		."\t<td bgcolor='#EEEEEE'>$setfont<input type='text' size='15' $slstyle name='sent' value=\"";
		
	if (isset($sent)) {echo $sent;}	else {echo "N";}
	echo "\"></font></td>\n"
		."</tr>\n"
		."<tr>\n"
		."\t<td align='right' width='20%'>$setfont<strong>"._TL_DONE.":</strong></font></td>\n"
		
	// TLR change to put date into sent and completed
	//	."\t<td bgcolor='#EEEEEE'>$setfont<input type='text' size='1' $slstyle name='completed' value=\"";
		."\t<td bgcolor='#EEEEEE'>$setfont<input type='text' size='15' $slstyle name='completed' value=\"";
		
	if (isset($completed)) {echo $completed;} else {echo "N";}
	if ($edfieldcount > 7) 
		{
		echo "\"></font></td>\n"
			."</tr>\n"
			."<tr>\n"
			."\t<td align='right' width='20%'>$setfont<strong>".$attr1_name.":</strong></font></td>\n"
			."\t<td bgcolor='#EEEEEE'>$setfont<input type='text' size='50' $slstyle name='attribute1' value=\"";
		if (isset($attribute_1)) {echo $attribute_1;}
		echo "\"></font></td>\n"
			."</tr>\n"
			."<tr>\n"
			."\t<td align='right' width='20%'>$setfont<strong>".$attr2_name.":</strong></font></td>\n"
			."\t<td bgcolor='#EEEEEE'>$setfont<input type='text' size='50' $slstyle name='attribute2' value=\"";
		if (isset($attribute_2)) {echo $attribute_2;}
		}
	echo "\"></font></td>\n"
		."</tr>\n"
		."<tr>\n"
		."\t<td colspan='2' align='center'>";
	switch($action)
		{
		case "edit":
			echo "\t\t<input type='submit' $btstyle name='action' value='"._UPDATE."'>\n"
				."\t\t<input type='hidden' name='tid' value='{$_GET['tid']}'>\n";
			break;
		case "addnew":
			echo "\t\t<input type='submit' $btstyle name='action' value='"._ADD."'>\n";
			break;
		}
	echo "\t\t<input type='hidden' name='sid' value='$surveyid'>\n"
		."\t</td>\n"
		."</tr>\n\n"
		."</table></form>\n"
		."</td></tr></table>\n";
	}


if ($action == _UPDATE)
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._TC_ADDEDIT."</strong></td></tr>\n"
		."\t<tr><td align='center'>\n";
	$data = array();
	$data[] = $_POST['firstname'];
	$data[] = $_POST['lastname'];
	$data[] = $_POST['email'];
	$data[] = $_POST['token'];
	$data[] = $_POST['sent'];
	$data[] = $_POST['completed'];
	$udquery = "UPDATE ".db_table_name("tokens_$surveyid")." SET firstname=?, "
			 . "lastname=?, email=?, "
			 . "token=?, sent=?, completed=?";
	if (isset($_POST['attribute1'])) 
		{
		$data[] = $_POST['attribute1'];
		$data[] = $_POST['attribute2'];
		$udquery .= ", attribute_1=?, attribute_2=?";
		}
	
	$udquery .= " WHERE tid={$_POST['tid']}";
	$udresult = $connect->Execute($udquery, $data) or die ("Update record {$_POST['tid']} failed:<br />\n$udquery<br />\n".htmlspecialchars($connect->ErrorMsg()));
	echo "<br />$setfont<font color='green'><strong>"._SUCCESS."</strong></font><br />\n"
		."<br />"._TC_TOKENUPDATED."<br /><br />\n"
		."<a href='$homeurl/tokens.php?sid=$surveyid&amp;action=browse'>"._T_ALL_BT."</a><br /><br />\n"
		."\t</td></tr></table>\n";
	}

if ($action == _ADD)
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._TC_ADDEDIT."</strong></td></tr>\n"
		."\t<tr><td align='center'>\n";
	$data = array('firstname' => $_POST['firstname'],
		'lastname' => $_POST['lastname'],
		'email' => $_POST['email'],
		'token' => $_POST['token'],
		'sent' => $_POST['sent'],
		'completed' => $_POST['completed']);
	if (isset($_POST['attribute1'])) 
		{
		$data['attribute_1'] = $_POST['attribute1'];
		$data['attribute_2'] = $_POST['attribute2'];
		}
	$inquery = $connect->GetInsertSQL("{$dbprefix}tokens_$surveyid", $data);
	$inresult = $connect->Execute($inquery) or die ("Add new record failed:<br />\n$inquery<br />\n".htmlspecialchars($connect->ErrorMsg()));
	echo "<br />$setfont<font color='green'><strong>"._SUCCESS."</strong></font><br />\n"
		."<br />"._TC_TOKENADDED."<br /><br />\n"
		."<a href='$homeurl/tokens.php?sid=$surveyid&amp;action=browse'>"._T_ALL_BT."</a><br />\n"
		."<a href='$homeurl/tokens.php?sid=$surveyid&amp;action=addnew'>"._T_ADD_BT."</a><br /><br />\n"
		."\t</td></tr></table>\n";
	}

if ($action == "import")
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'>"
		."<font size='1' face='verdana' color='white'><strong>"
		._UPLOADCSV."</strong></font></td></tr>\n"
		."\t<tr><td align='center'>\n";
	form();
	echo "<table width='400' bgcolor='#eeeeee'>\n"
		."\t<tr>\n"
		."\t\t<td align='center'>\n"
		."\t\t\t<font size='1'><strong>Note:</strong><br />\n"
		."\t\t\t"._TC_UPLOADINFO."\n"
		."\t\t</font></td>\n"
		."\t</tr>\n"
		."</table><br />\n"
		."</td></tr></table>\n";
	}


if ($action == "upload") 
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._UPLOADCSV."</strong></td></tr>\n"
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
		$errormessage="<strong><font color='red'>"._ERROR.":</font> "._TC_UPLOADFAIL."</strong>\n";
		form($errormessage);
		}
		else
		{
		echo "<br /><strong>"._TC_IMPORT."</strong><br />\n<font color='green'>"._SUCCESS."</font><br /><br />\n"
			._TC_CREATE."<br />\n";
		$xz = 0; $xx = 0;
		$handle = fopen($the_full_file_path, "r");
		if ($handle == false) {echo "Failed to open the uploaded file!\n";}
		while (!feof($handle))
			{
			$buffer=getLine($handle); //Function determines line endings including \r (mac)
			//$buffer = fgets($handle, 4096); //4096 could be increased if very long lines are being used

			if (substr($buffer, -2) == "\r\n") {$buffer = substr($buffer, 0, -2);}
			elseif (substr($buffer, -1) == "\n") {$buffer = substr($buffer, 0, -1);}
			elseif (substr($buffer, -1) == "\r") {$buffer = substr($buffer, 0, -1);}
			if(function_exists('mb_convert_encoding')) {$buffer=mb_convert_encoding($buffer,"UTF-8","auto");} //Sometimes mb_convert_encoding doesn't exist
			$firstname = ""; $lastname = ""; $email = ""; $token = ""; //Clear out values from the last path, in case the next line is missing a value
			if (!$xx)
				{
				//THIS IS THE FIRST LINE. IT IS THE HEADINGS. IGNORE IT
				}
			else
				{
				$line = explode(",", $connect->escape($buffer));
				$elements = count($line);
				if ($elements > 1)
					{
					$xy = 0;
					foreach($line as $el)
						{
						//echo "[$el]($xy)<br />\n"; //Debugging info
						if ($xy < $elements)
							{
							if ($xy == 0) {$firstname = $el;}
							if ($xy == 1) {$lastname = $el;}
							if ($xy == 2) {$email = trim($el);}
							if ($xy == 3) {$token = trim($el);}
							if ($xy == 4) {$attribute1 = trim($el);}
							if ($xy == 5) {$attribute2 = trim($el);}
							}
						$xy++;
						}
					//CHECK FOR DUPLICATES?
					$iq = "INSERT INTO ".db_table_name("tokens_$surveyid")." \n"
						. "(firstname, lastname, email, token";
					if (isset($attribute1)) {$iq .= ", attribute_1";}
					if (isset($attribute2)) {$iq .= ", attribute_2";}
					$iq .=") \n"
						. "VALUES ('$firstname', '$lastname', '$email', '$token'";
					if (isset($attribute1)) {$iq .= ", '$attribute1'";}
					if (isset($attribute2)) {$iq .= ", '$attribute2'";}
					$iq .= ")";
					//echo "<pre style='text-align: left'>$iq</pre>\n"; //Debugging info
					$ir = $connect->Execute($iq) or die ("Couldn't insert line<br />\n$buffer<br />\n".htmlspecialchars($connect->ErrorMsg())."<pre style='text-align: left'>$iq</pre>\n");
					$xz++;
					}
				}
			$xx++;
			}
		echo "<font color='green'>"._SUCCESS."</font><br /><br>\n";
		$message=str_replace("{TOKENCOUNT}", $xz, _TC_TOKENS_CREATED);
		echo "<i>$message</i><br />\n";
		fclose($handle);
		unlink($the_full_file_path);
		}
	echo "\t\t\t</td></tr></table>\n";
	}

//echo "</center>\n";
//echo "&nbsp;"
echo "\t\t<table><tr><td></td></tr></table>\n"
	."\t\t</td>\n";

//echo "</td>\n";
echo helpscreen()
	."</tr></table>\n";

echo getAdminFooter("$langdir/instructions.html#tokens", "Using PHPSurveyors Tokens Function");
//	."</body>\n</html>";


function form($error=false) {
global $surveyid, $btstyle, $slstyle, $setfont;

	if ($error) {print $error . "<br /><br />\n";}
	
	print "\n$setfont<form enctype='multipart/form-data' action='" . $_SERVER['PHP_SELF'] . "' method='post'>\n"
		. "<input type='hidden' name='action' value='upload' />\n"
		. "<input type='hidden' name='sid' value='$surveyid' />\n"
		. "Upload a file<br />\n"
		. "<input type='file' $slstyle name='the_file' size='35' /><br />\n"
		. "<input type='submit' $btstyle value='Upload' />\n"
		. "</form></font>\n\n";

} # END form

function helpscreen()
	{
	global $homeurl, $langdir, $imagefiles;
	global $action, $setfont;
	echo "<!-- HELP SCREEN / RIGHT HAND CELL -->\n"
		."\t\t<td id='help' width='150' valign='top' style='display: none' bgcolor='#CCCCCC'>\n"
		."\t\t\t<table width='100%'><tr><td><table width='100%' align='center' cellspacing='0'>\n"
		."\t\t\t\t<tr>\n"
		."\t\t\t\t\t<td bgcolor='#555555' height='8'>\n"
		."\t\t\t\t\t\t$setfont<font color='white' size='1'><strong>"._HELP."</strong>\n"
		."\t\t\t\t\t</font></font></td>\n"
		."\t\t\t\t</tr>\n"
		."\t\t\t\t<tr>\n"
		."\t\t\t\t\t<td align='center' bgcolor='#AAAAAA' style='border-style: solid; border-width: 1; border-color: #555555'>\n"
		."\t\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' hspace='0' border='0' align='left'>\n"
		."\t\t\t\t\t\t<input type='image' name='CloseHelpButton' src='$imagefiles/close.gif' align='right' onClick=\"showhelp('hide')\">\n"
		."\t\t\t\t\t</td>\n"
		."\t\t\t\t</tr>\n"
		."\t\t\t\t<tr>\n"
		."\t\t\t\t\t<td bgcolor='silver' height='100%' style='border-style: solid; border-width: 1; border-color: #333333'>\n";
	//determine which help document to show
	$helpdoc = "$langdir/tokens.html";
	switch ($action)
		{
		case "browse":
			$helpdoc .= "#Display%20Tokens";
			break;
		case "email":
			$helpdoc .= "#E%20Invitiation";
			break;
		case "addnew":
			$helpdoc .= "#Add%20new%20token%20entry";
			break;
		case "import":
			$helpdoc .= "#Import/Upload%20CSV%20File";
			break;
		case "tokenify":
			$helpdoc .= "#Generate%20Tokens";
			break;
		}
	echo "\t\t\t\t\t\t<iframe width='150' height='400' src='$helpdoc' marginwidth='2' marginheight='2'>\n"
		."\t\t\t\t\t\t</iframe>\n"
		."\t\t\t\t\t</td>"
		."\t\t\t\t</tr>\n"
		."\t\t\t</table></td></tr></table>\n"
		."\t\t</td>\n";
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

?>
