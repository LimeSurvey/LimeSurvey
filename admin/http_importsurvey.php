<?php
/*
#############################################################
# >>> LimeSurvey                                           #
#############################################################
# > Author:  Jason Cleeland                                 #
# > E-mail:  jason@cleeland.org                             #
# > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
# >          CARLTON SOUTH 3053, AUSTRALIA                  #
# > Date:    20 February 2003                               #
#                                                           #
# This set of scripts allows you to develop, publish and    #
# perform data-entry on surveys.                            #
#############################################################
#                                                           #
#   Copyright (C) 2003  Jason Cleeland                      #
#                                                           #
# This program is free software; you can redistribute       #
# it and/or modify it under the terms of the GNU General    #
# Public License Version 2 as published by the Free         #
# Software Foundation.                                      #
#                                                           #
#                                                           #
# This program is distributed in the hope that it will be   #
# useful, but WITHOUT ANY WARRANTY; without even the        #
# implied warranty of MERCHANTABILITY or FITNESS FOR A      #
# PARTICULAR PURPOSE.  See the GNU General Public License   #
# for more details.                                         #
#                                                           #
# You should have received a copy of the GNU General        #
# Public License along with this program; if not, write to  #
# the Free Software Foundation, Inc., 59 Temple Place -     #
# Suite 330, Boston, MA  02111-1307, USA.                   #
#############################################################
*/
//Ensure script is not run directly, avoid path disclosure
if (empty($homedir)) {die ("Cannot run this script directly");}


// A FILE TO IMPORT A DUMPED SURVEY FILE, AND CREATE A NEW SURVEY

$importsurvey = "<br /><table width='100%' align='center'><tr><td>\n";
$importsurvey .= "<table class='alertbox'>\n";
$importsurvey .= "\t<tr ><td colspan='2' height='4'><font size='1' ><strong>"
.$clang->gT("Import Survey")."</strong></font></td></tr>\n";
$importsurvey .= "\t<tr ><td align='center'>\n";

$the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];

if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
{
	$importsurvey .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
	$importsurvey .= $clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your /admin/tmp folder folder.")."<br /><br />\n";
	$importsurvey .= "</font></td></tr></table>\n";
	return;
}

// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY

$importsurvey .= "<strong><font color='green'>".$clang->gT("Success")."!</font></strong><br />\n";
$importsurvey .= $clang->gT("File upload succeeded.")."<br /><br />\n";
$importsurvey .= $clang->gT("Reading file..")."<br />\n";

$importingfrom = "http";	// "http" for the web version and "cmdline" for the command line version
include("importsurvey.php");

?>
