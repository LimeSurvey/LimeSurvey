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
//Ensure script is not run directly, avoid path disclosure
if (empty($homedir)) {die ("Cannot run this script directly");}

$date = date('YmdHi'); //'Hi' adds 24hours+minutes to name to allow multiple deactiviations in a day

if (!isset($_GET['ok']) || !$_GET['ok'])
	{
	echo "<br />\n<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t\t\t\t<tr bgcolor='#555555'><td height='4'><font size='1' face='verdana' color='white'><strong>"._DEACTIVATE." ($surveyid)</strong></font></td></tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center' bgcolor='#FFEEEE'>\n";
	echo "\t\t\t<font color='red'>$setfont<strong>";
	echo _WARNING."<br />"._AC_READCAREFULLY;
	echo "\t\t</strong></font></font></td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>";
	echo "\t\t<td>\n";
	echo "\t\t\t"._AC_DEACTIVATE_MESSAGE1."\n";
	echo "\t\t\t<p>"._AC_DEACTIVATE_MESSAGE2."</p>\n";
	echo "\t\t\t<p>"._AC_DEACTIVATE_MESSAGE3."</p>\n";
	echo "\t\t\t<p>"._AC_DEACTIVATE_MESSAGE4." {$dbprefix}old_{$_GET['sid']}_{$date}</p>\n";
	echo "\t\t\t<p>"._AC_DEACTIVATE_MESSAGE5."</p>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>\n";
	echo "\t\t\t<input type='submit' $btstyle value='"._AD_CANCEL."' onClick=\"window.open('$scriptname?sid={$_GET['sid']}', '_top')\"><br />\n";
	echo "\t\t\t<input type='submit' $btstyle value='"._AC_DEACTIVATE."' onClick=\"window.open('$scriptname?action=deactivate&amp;ok=Y&amp;sid={$_GET['sid']}', '_top')\">\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	}

else
	{
	//See if there is a tokens table for this survey
	$tablelist = $connect->MetaTables();
	if (in_array("{$dbprefix}tokens_{$_GET['sid']}", $tablelist))
		{
		$toldtable="{$dbprefix}tokens_{$_GET['sid']}";
		$tnewtable="{$dbprefix}old_tokens_{$_GET['sid']}_{$date}";
		$tdeactivatequery = "RENAME TABLE $toldtable TO $tnewtable";
		$tdeactivateresult = $connect->Execute($tdeactivatequery) or die ("Couldn't deactivate tokens table because:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>"._GO_ADMIN."</a>");
		}
	
	$oldtable="{$dbprefix}survey_{$_GET['sid']}";
	$newtable="{$dbprefix}old_{$_GET['sid']}_{$date}";

	//Update the auto_increment value from the table before renaming
	$new_autonumber_start=0;
	$query = "SELECT id FROM $oldtable ORDER BY id desc LIMIT 1";
	$result = db_execute_assoc($query) or die("Error getting latest id number<br />$query<br />".htmlspecialchars($connect->ErrorMsg())); 
	while ($row=$result->FetchRow())
		{
		if (strlen($row['id']) > 12) //Handle very large autonumbers (like those using IP prefixes)
			{
		    $part1=substr($row['id'], 0, 12);
			$part2len=strlen($row['id'])-12;
			$part2=sprintf("%0{$part2len}d", substr($row['id'], 12, strlen($row['id'])-12)+1);
			$new_autonumber_start="{$part1}{$part2}";
			}
		else
			{
			$new_autonumber_start=$row['id']+1;
			}
		}
	$query = "UPDATE {$dbprefix}surveys SET autonumber_start=$new_autonumber_start WHERE sid=$surveyid";
	@$result = $connect->Execute($query); //Note this won't die if it fails - that's deliberate.
	
	$deactivatequery = "RENAME TABLE $oldtable TO $newtable";
	$deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't deactivate because:<BR>".htmlspecialchars($connect->ErrorMsg())."<BR><BR><a href='$scriptname?sid={$_GET['sid']}'>Admin</a>");
	echo "<br />\n<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t\t\t\t<tr bgcolor='#555555'><td height='4'><font size='1' face='verdana' color='white'><strong>"._DEACTIVATE." ($surveyid)</strong></font></td></tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>\n";
	echo "\t\t\t$setfont<strong>Survey Has Been De-Activated\n";
	echo "\t\t</strong></font></td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td>\n";
	echo "\t\t\t"._AC_DEACTIVATED_MESSAGE1." $newtable.\n";
	echo "\t\t\t"._AC_DEACTIVATED_MESSAGE2."\n";
	echo "\t\t\t<p>"._AC_DEACTIVATED_MESSAGE3."</p>\n";
	if (isset($toldtable) && $toldtable)
		{
		echo "\t\t\t"._AC_DEACTIVATED_MESSAGE4." $tnewtable.\n";
		}
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>\n";
	echo "\t\t\t<input type='submit' $btstyle value='"._GO_ADMIN."' onClick=\"window.open('$scriptname?sid={$_GET['sid']}', '_top')\">\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	}

?>
