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
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0

echo $htmlheader;
if (!$sid)
	{
	echo "<CENTER><BR>ERROR: You have not chosen a survey to delete!";
	exit;
	}

if (!$ok)
	{
	echo "<TABLE WIDTH='100%' ALIGN='CENTER'>\n";
	echo "<TR><TD ALIGN='CENTER'>$setfont";
	echo "<BR><FONT COLOR='RED'><B>--:WARNING:--</B></FONT><BR>";
	echo "<B>You are about to delete survey $sid</B>";
	echo "<BR>If you select \"OK\" below to delete this survey<BR>";
	echo "you will lose all your work on this survey. You'd want<BR>";
	echo "to be pretty sure about that!<BR><BR>";
	echo "You could consider 'exporting' the survey before deleting<BR>";
	echo "it, and then if you change your mind later you could<BR>";
	echo "re-install it. If you want to do this, click on 'cancel'<BR>";
	echo "and then choose \"export\" from the survey summary on the<BR>";
	echo "main administration screen.";
	echo "</TD></TR>";
	echo "<TR><TD ALIGN='CENTER'><BR>";
	echo "<INPUT TYPE='SUBMIT' $btstyle STYLE='width:100' VALUE='Delete It' onClick=\"window.open('$PHP_SELF?sid=$sid&ok=Y','_top')\"><BR><BR>";
	echo "<INPUT TYPE='SUBMIT' $btstyle STYLE='width:100' VALUE='Cancel' onClick=\"window.open('admin.php?sid=$sid', '_top')\">";
	echo "</TD></TR></TABLE>\n";
	}

else
	{
	$dsquery="SELECT qid FROM questions WHERE sid=$sid";
	$dsresult=mysql_query($dsquery);
	while ($dsrow=mysql_fetch_row($dsresult))
		{
		$asdel="DELETE FROM answers WHERE qid=$dsrow[0]";
		$asres=mysql_query($asdel);
		}
	
	$qdel="DELETE FROM questions WHERE sid=$sid";
	$qres=mysql_query($qdel);
	
	$gdel="DELETE FROM groups WHERE sid=$sid";
	$gres=mysql_query($gdel);
	
	$sdel="DELETE FROM surveys WHERE sid=$sid";
	$sres=mysql_query($sdel);
	
	echo "<TABLE WIDTH='100%' ALIGN='CENTER'>\n";
	echo "<TR><TD ALIGN='CENTER'>$setfont";
	echo "<BR><B>All bits of survey $sid have been deleted.<BR><BR>";
	echo "<a href='admin.php'>Return to Admin Page</a></TD></TR></TABLE>\n";
	}

?>