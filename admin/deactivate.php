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
$date = date(YmdHi); //'Hi' adds 24hours+minutes to name to allow multiple deactiviations in a day

if (!$_GET['ok'])
	{
	echo "<br />\n<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t\t\t\t<tr bgcolor='#555555'><td height='4'><font size='1' face='verdana' color='white'><b>"._DEACTIVATE." ($sid)</b></td></tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center' bgcolor='pink'>\n";
	echo "\t\t\t<font color='red'>$setfont<b>";
	echo _WARNING."<br />"._AC_READCAREFULLY;
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>";
	echo "\t\t<td>\n";
	echo "\t\t\t$setfont"._AC_DEACTIVATE_MESSAGE1."\n";
	echo "\t\t\t<p>"._AC_DEACTIVATE_MESSAGE2."</p>\n";
	echo "\t\t\t<p>"._AC_DEACTIVATE_MESSAGE3."</p>\n";
	echo "\t\t\t<p>"._AC_DEACTIVATE_MESSAGE4." old_{$_GET['sid']}_{$date}</p>\n";
	echo "\t\t\t<p>"._AC_DEACTIVATE_MESSAGE5."</p>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>\n";
	echo "\t\t\t<input type='submit' $btstyle value='"._CANCEL."' onClick=\"window.open('$scriptname?sid={$_GET['sid']}', '_top')\"><br />\n";
	echo "\t\t\t<input type='submit' $btstyle value='"._AC_DEACTIVATE."' onClick=\"window.open('$scriptname?action=deactivate&ok=Y&sid={$_GET['sid']}', '_top')\">\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	}

else
	{
	//See if there is a tokens table for this survey
	$result = mysql_list_tables($databasename);
	while ($row = mysql_fetch_row($result))
		{
		$tablelist[]=$row[0];
	    }
	if (in_array("tokens_{$_GET['sid']}", $tablelist))
		{
		$toldtable="tokens_{$_GET['sid']}";
		$tnewtable="old_tokens_{$_GET['sid']}_{$date}";
		$tdeactivatequery = "RENAME TABLE $toldtable TO $tnewtable";
		$tdeactivateresult = mysql_query($tdeactivatequery) or die ("Couldn't deactivate tokens table because:<br />".mysql_error()."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>"._GO_ADMIN."</a>");
		}
	$oldtable="survey_{$_GET['sid']}";
	$newtable="old_{$_GET['sid']}_{$date}";
	$deactivatequery = "RENAME TABLE $oldtable TO $newtable";
	$deactivateresult = mysql_query($deactivatequery) or die ("Couldn't deactivate because:<BR>".mysql_error()."<BR><BR><a href='$scriptname?sid={$_GET['sid']}'>Admin</a>");
	echo "<br />\n<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t\t\t\t<tr bgcolor='#555555'><td height='4'><font size='1' face='verdana' color='white'><b>"._DEACTIVATE." ($sid)</b></td></tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>\n";
	echo "\t\t\t$setfont<b>Survey Has Been De-Activated\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td>\n";
	echo "\t\t\t"._AC_DEACTIVATED_MESSAGE1." $newtable.\n";
	echo "\t\t\t"._AC_DEACTIVATED_MESSAGE2."\n";
	echo "\t\t\t<p>"._AC_DEACTIVATED_MESSAGE3."</p>\n";
	if ($toldtable)
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