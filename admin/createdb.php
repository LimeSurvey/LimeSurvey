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
include("config.php");

echo $htmlheader;

if (!$dbname)
	{
	echo "<CENTER><B><FONT COLOR='RED'>Error. References not provided. Please do not run this script directly</FONT></B>";
	echo "<BR><BR><B>Now you can go back to the Admin page!<BR>";
	echo "<INPUT TYPE='SUBMIT' VALUE='Admin' onClick='location.href=\"$scriptname\"'>";
	exit;
	}
$connect=mysql_connect("$databaselocation:$databaseport", "$databaseuser", "$databasepass");
if (!mysql_selectdb ($dbname, $connect))
	{
	if (mysql_create_db("$dbname"))
		{
		echo "<CENTER><B><FONT COLOR='GREEN'>$dbname has now been created.</FONT></B><BR><BR>";
		
		$db=mysql_selectdb($databasename, $connect);
		
		if(mysql_query($createsurveys))
			{echo "Surveys Table Created Succesfully..<BR>";}
		else
			{echo "Surveys table could not be created!<BR>";}
		if (mysql_query($createquestions))
			{echo "Questions Table Created Succesfully..<BR>";}
		else
			{echo "Questions table could not be created!<BR>";}
		if (mysql_query($createanswers))
			{echo "Answers Table Created Succesfully..<BR>";}
		else
			{echo "Answers table could not be created!<BR>";}
		if (mysql_query($creategroups))
			{echo "Groups Table Created Succesfully..<BR>";}
		else
			{echo "Groups table could not be created!<BR>";}
		if (mysql_query($createusers))
			{echo "Users Table Created Succesfully..<BR>";}
		else
			{echo "Users table could not be created!<BR>";}
		
		echo "<BR><BR><B>Now you can go back to the Admin page!<BR>";
		echo "<INPUT TYPE='SUBMIT' VALUE='Admin' onClick='location.href=\"$scriptname\"'>";
		}
	else
		{
		echo "<CENTER><B><FONT COLOR='RED'>ERROR: $dbname could not be created. Contact your system administrator</FONT></B>";
		echo "<BR><BR><B>Now you can go back to the Admin page!<BR>";
		echo "<INPUT TYPE='SUBMIT' VALUE='Admin' onClick='location.href=\"$scriptname\"'>";
		}
	}


?>