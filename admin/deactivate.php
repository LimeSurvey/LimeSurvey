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
$date = date(Ymd);

if (!$ok)
	{
	
	echo "<TABLE WIDTH='350' ALIGN='CENTER'><TR><TD ALIGN='CENTER' BGCOLOR='PINK'><FONT COLOR='RED'>$setfont<B>";
	echo ":WARNING:<BR>READ THIS CAREFULLY BEFORE PROCEEDING</TD></TR>\n";
	echo "<TR><TD>$setfont";
	echo "In an active survey, a table is created to store all the data-entry records.";
	echo "<P>When you de-activate a survey all the data entered in the original table will ";
	echo "be moved elsewhere, and when you activate the survey again, the table will be empty.<P>";
	echo "If you click on OK below, your survey will be de-activated, and all the data in the ";
	echo "existing table will be moved to a new table name called <B><I>old_{$sid}_{$date}</I></B> ";
	echo "and the existing table <B><I>survey_{$sid}</I></B> will no longer exist.<P>";
	echo "De-activated survey data can only be accessed by system administrators using a MySQL ";
	echo "data access tool like phpmyadmin.<P>";
	echo "The point we are trying to make here is... DON'T DO THIS IF YOU ARE UNSURE.";
	echo "</TD></TR>";
	echo "<TR><TD ALIGN='CENTER'>";
	echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='I`m Unsure' onclick=\"window.open('$scriptname?sid=$sid', '_top')\"><BR>\n";
	echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='De-activate' onClick=\"window.open('$scriptname?action=deactivate&ok=Y&sid=$sid', '_top')\">";
	echo "</TD></TR></TABLE>\n";
	}

else
	{
	$oldtable="survey_{$sid}";
	$newtable="old_{$sid}_{$date}";
	$deactivatequery = "RENAME TABLE $oldtable TO $newtable";
	$deactivateresult = mysql_query($deactivatequery) or die ("Couldn't deactivate because:<BR>".mysql_error()."<BR><BR><a href='$scriptname?sid=$sid'>Admin</a>");
	echo "<TABLE WIDTH='350' ALIGN='CENTER'><TR><TD ALIGN='CENTER'>$setfont<B>";
	echo "Survey Has Been De-Activated</TD></TR>\n";
	echo "<TR><TD>The survey named $oldtable has been renamed to ";
	echo "$newtable and is now no longer accessible using the Surveyor scripts.<P>";
	echo "You should write down the name of this table and keep it somewhere safe ";
	echo "in case you ever need to access this information again. Or, in case you ";
	echo "want your system administrator to completely delete the old table.";
	echo "</TD></TR>\n";
	echo "<TR><TD ALIGN='CENTER'><INPUT TYPE='SUBMIT' VALUE='Admin Page' onClick=\"window.open('$scriptname?sid=$sid', '_top')\">\n";
	echo "</TD></TR></TABLE>\n";
	}

?>