<?php
/*
    #############################################################
    # >>> PHPSurveyor                                           #
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
    # Public License as published by the Free Software          #
    # Foundation; either version 2 of the License, or (at your  #
    # option) any later version.                                #
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

if ($argc != 4 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>
This is a command line LimeSurvey Survey importer.

  Usage:
  php <?php echo $argv[0]; ?> <File to import> <user> <password>

  <File to import> has to be a LimeSurvey survey dump.
  With the --help, -help, -h, or -? options, you can get this help.

<?php
	exit;
} else {
    $the_full_file_path = $argv[1];
}

if (!file_exists($the_full_file_path)) {
    echo "The file $the_full_file_path does not exist";
    exit;
}

require_once(dirname(__FILE__).'/../config.php');  // config.php itself includes common.php

//TODO: validate user, password and permissions
$_SESSION['loginID'] = 1;

$importsurvey = "";

$importingfrom = "cmdline";	// "http" for the web version and "cmdline" for the command line version
include("importsurvey.php");


?>
