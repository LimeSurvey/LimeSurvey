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
$sid = $_GET['sid'];
$ok = $_GET['ok'];

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
	echo "<center><br />ERROR: You have not chosen a survey to delete!</center>\n";
	echo "</body>\n</html>";
	exit;
	}

if (!$ok)
	{
	echo "<table width='100%' align='center'>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>$setfont<br />\n";
	echo "\t\t\t<font color='red'><b>--:WARNING:--</b></font><br />\n";
	echo "\t\t\t<b>You are about to delete survey $sid</b><br />\n";
	echo "\t\t\tIf you select \"OK\" below to delete this survey<br />\n";
	echo "\t\t\tyou will lose all your work on this survey. You'd want<br />\n";
	echo "\t\t\tto be pretty sure about that!<br /><br />\n";
	echo "\t\t\tYou could consider 'exporting' the survey before deleting<br />\n";
	echo "\t\t\tit, and then if you change your mind later you could<br />\n";
	echo "\t\t\tre-install it. If you want to do this, click on 'cancel'<br />\n";
	echo "\t\t\tand then choose \"export\" from the survey summary on the<br />\n";
	echo "\t\t\tmain administration screen.\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'><br />\n";
	echo "\t\t\t<input type='submit' $btstyle style='width:100' value='Delete It' onClick=\"window.open('{$_SERVER['PHP_SELF']}?sid=$sid&ok=Y','_top')\" /><br /><br />\n";
	echo "\t\t\t<input type='submit' $btstyle style='width:100' value='Cancel' onClick=\"window.open('admin.php?sid=$sid', '_top')\" />\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	echo "</body>\n</html>";
	}

else
	{
	$dsquery = "SELECT qid FROM questions WHERE sid=$sid";
	$dsresult = mysql_query($dsquery) or die ("Couldn't find matching survey to delete<br />$dsquery<br />".mysql_error());
	while ($dsrow = mysql_fetch_array($dsresult))
		{
		$asdel = "DELETE FROM answers WHERE qid={$dsrow['qid']}";
		$asres = mysql_query($asdel);
		$cddel = "DELETE FROM conditions WHERE qid={$dsrow['qid']}";
		$cdres = mysql_query($cddel) or die ("Delete conditions failed<br />$cddel<br />".mysql_error());
		}
	
	$qdel = "DELETE FROM questions WHERE sid=$sid";
	$qres = mysql_query($qdel);
	
	$gdel = "DELETE FROM groups WHERE sid=$sid";
	$gres = mysql_query($gdel);
	
	$sdel = "DELETE FROM surveys WHERE sid=$sid";
	$sres = mysql_query($sdel);
	
	echo "<table width='100%' align='center'>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>$setfont<br />\n";
	echo "\t\t\t<b>All bits of survey $sid have been deleted.<br /><br />\n";
	echo "\t\t\t<a href='admin.php'>Return to Admin Page</a>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	echo "</body>\n</html>";
	}

?>