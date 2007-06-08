<?php
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