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
require_once(dirname(__FILE__).'/../config.php');

$dbname = $databasename;

sendcacheheaders();

echo $htmlheader;
echo "<br />\n";
echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"._("Create Database")."</strong></td></tr>\n";
echo "\t<tr bgcolor='#CCCCCC'><td align='center'>$setfont\n";

// In Step2 fill the database with data
if (returnglobal('createdbstep2')==_("Populate Database"))
{
   if (modify_database(dirname(__FILE__).'/install/create-'.$databasetype.'.sql'))
   {
   echo _("Database has been successfully populated.")."</font></strong></font><br /><br />\n";
   echo "<input type='submit' value='"._("Main Admin Screen")."' onClick='location.href=\"$scriptname\"'>";
   }
    else
    {
      echo"Error";
    }

}

if (!$dbname)
	{
	echo "<br /><strong>$setfont<font color='red'>"._("Error")."</font></strong><br />\n";
	echo _("Database Information not provided. This script must be run from admin.php only.");
	
	echo "<br /><br />\n";
	echo "<input type='submit' value='"._("Main Admin Screen")."' onClick='location.href=\"$scriptname\"'>";
	exit;
	}
if (!$database_exists) //Database named in config.php does not exist
	{
	// TODO SQL: Portable to other databases??
	switch ($databasetype)
	{
      case 'mysql': $createDb=$connect->Execute("CREATE DATABASE `$dbname` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");
                    break;
      case 'odbc_mssql':
      case 'mssql': $createDb=$connect->Execute("CREATE DATABASE [$dbname];");
                    break;
           default: $createDb=$connect->Execute("CREATE DATABASE $dbname");
    }
    if ($createDb) //Database has been succesfully created
		{
        $connect->database = $dbname;
	    $connect->Execute("USE DATABASE `$dbname`");
		echo "<br />$setfont<strong><font color='green'>\n";
		echo _("Database has been created.")."</font></strong></font><br /><br />\n";
		echo _("Please click below to populate the database")."<br /><br />\n";
		echo "<form method='post'>";
		echo "<input type='submit' name='createdbstep2' value='"._("Populate Database")."' onClick='location.href=\"createdb.php\"'></form>";
		}
	else
		{
		echo "<strong>$setfont<font color='red'>"._("Error")."</font></strong></font><br />\n";
		echo _("Could not create database")." ($dbname)<br /><font size='1'>\n";
		echo $connect->ErrorMsg();
		echo "</font><br /><br />\n";
		echo "<input type='submit' value='"._("Main Admin Screen")."' onClick='location.href=\"$scriptname\"'>";
		}
	}
echo "</td></tr></table>\n";

?>
