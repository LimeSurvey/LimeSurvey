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
	echo "<table width='350' align='center'>";
	echo "\t<tr>\n";
	echo "\t\t<td align='center' bgcolor='pink'>\n";
	echo "\t\t\t<font color='red'>$setfont<b>";
	echo ":WARNING:<br />READ THIS CAREFULLY BEFORE PROCEEDING";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>";
	echo "\t\t<td>\n";
	echo "\t\t\t{$setfont}In an active survey, a table is created to store all the data-entry records.\n";
	echo "\t\t\t<p>When you de-activate a survey all the data entered in the original table will \n";
	echo "\t\t\tbe moved elsewhere, and when you activate the survey again, the table will be empty.</p>\n";
	echo "\t\t\t<p>If you click on OK below, your survey will be de-activated, and all the data in the \n";
	echo "\t\t\texisting table will be moved to a new table name called <b><i>old_{$_GET['sid']}_{$date}</i></b> \n";
	echo "\t\t\tand the existing table <b><i>survey_{$_GET['sid']}</i></b> will no longer exist.</p>\n";
	echo "\t\t\t<p>De-activated survey data can only be accessed by system administrators using a MySQL \n";
	echo "\t\t\tdata access tool like phpmyadmin.</p>\n";
	echo "\t\t\tThe point we are trying to make here is... DON'T DO THIS IF YOU ARE UNSURE.\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>\n";
	echo "\t\t\t<input type='submit' $btstyle value='I`m Unsure' onClick=\"window.open('$scriptname?sid={$_GET['sid']}', '_top')\"><br />\n";
	echo "\t\t\t<input type='submit' $btstyle value='De-activate' onClick=\"window.open('$scriptname?action=deactivate&ok=Y&sid={$_GET['sid']}', '_top')\">\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	}

else
	{
	$oldtable="survey_{$_GET['sid']}";
	$newtable="old_{$_GET['sid']}_{$date}";
	$deactivatequery = "RENAME TABLE $oldtable TO $newtable";
	$deactivateresult = mysql_query($deactivatequery) or die ("Couldn't deactivate because:<BR>".mysql_error()."<BR><BR><a href='$scriptname?sid={$_GET['sid']}'>Admin</a>");
	echo "<table width='350' align='center'>";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>\n";
	echo "\t\t\t$setfont<b>Survey Has Been De-Activated\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td>\n";
	echo "\t\t\tThe survey named $oldtable has been renamed to \n";
	echo "\t\t\t$newtable and is now no longer accessible using the PHPSurveyor scripts.\n";
	echo "\t\t\t<p>You should write down the name of this table and keep it somewhere safe \n";
	echo "\t\t\tin case you ever need to access this information again. Or, in case you \n";
	echo "\t\t\twant your system administrator to completely delete the old table.</p>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>\n";
	echo "\t\t\t<input type='submit' $btstyle value='Admin Page' onClick=\"window.open('$scriptname?sid={$_GET['sid']}', '_top')\">\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	}

?>