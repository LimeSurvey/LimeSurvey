<?php
/*
#############################################################
# >>> PHPSurveyor  										    #
#############################################################
# > Author:  Jason Cleeland									#
# > E-mail:  jason@cleeland.org								#
# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
# >          CARLTON SOUTH 3053, AUSTRALIA                  #
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

require_once(dirname(__FILE__).'/../../config.php');  // config.php itself includes common.php
$adminoutput='';  // Alle future output is written into this and then outputted at the end of file
// SET THE LANGUAGE???? -> DEFAULT SET TO EN FOR NOW
require_once($rootdir.'/classes/core/language.php');
$clang = new phpsurveyor_lang("en");

if (!$database_exists)
{
	$adminoutput.= "<br />\n"
	."<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
	."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("PHPSurveyor Setup")."</strong></td></tr>\n"
	."\t<tr bgcolor='#CCCCCC'><td align='center'>\n"
	."<strong>".$clang->gT("Welcome to PHPSurveyor Setup!")."</strong><br /><br />\n"
	.$clang->gT("The database defined in config.php does not exist.")."<br />\n"
	.$clang->gT("PHPSurveyor can attempt to create this database for you.")."<br /><br />\n"
	.$clang->gT("Your selected database name is:")."<strong> $databasename</strong><br />\n"
	."<br /><input type='submit' value='"
	.$clang->gT("Create Database")."' onclick='location.href=\"createdb.php\"' /></center>\n"
	."</td></tr></table>\n"
	."</body>\n</html>\n";
}
    elseif ($dbexistsbutempty && !(returnglobal('createdbstep2')==$clang->gT("Populate Database")))
{
        $connect->database = $databasename;
	    $connect->Execute("USE DATABASE `$databasename`");
		$adminoutput.= "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		.$clang->gT("PHPSurveyor Setup")."</strong></td></tr>\n"
		."\t<tr bgcolor='#CCCCCC'><td align='center'>\n";
		$adminoutput.= "<br /><strong><font color='green'>\n";
		$adminoutput.= sprintf($clang->gT('A database named "%s" does already exist.'),$databasename)."</font></strong></font><br /><br />\n";
		$adminoutput.= $clang->gT("Do you want to populate that database now by creating the necessary tables?")."<br /><br />\n";
		$adminoutput.= "<form method='post' action='createdb.php'>";
		$adminoutput.= "<input type='submit' name='createdbstep2' value='".$clang->gT("Populate Database")."'></form>";
		}
else
	{
	//DB EXISTS, CHECK FOR APPROPRIATE UPGRADES
	checkforupgrades();
    }
sendcacheheaders();
echo $adminoutput;        
    
?>
