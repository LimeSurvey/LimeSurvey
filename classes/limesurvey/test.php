<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
include_once(dirname(__FILE__).'/libattributes.php');
include_once(dirname(__FILE__).'/attribute.php');
require_once(dirname(__FILE__).'/../adodb/adodb.inc.php');
require_once(dirname(__FILE__).'/../../config.php');

$connect=&ADONewConnection($databasetype);
$database_exists = FALSE;
if ($connect->Connect("$databaselocation:$databaseport", $databaseuser, $databasepass, $databasename))
   { $database_exists = TRUE;}
    else {
         $connect->database = '';
         $connect->Connect("$databaselocation:$databaseport", $databaseuser, $databasepass);
         }
         
$test = new libattributes($connect,$dbprefix,'','44');
$array = $test->get_attributes('A');
print_r($array);
print "<br /><br />";
$value = $test->get_attribute_value(206,'array_filter');
print $value;
print "<br /><br />";
if ($test->new_attribute('999','test_attribute','abc'))
{
	print "Added Attribute";
} else {
	print "Failed to Add Attribute";
}
?>
