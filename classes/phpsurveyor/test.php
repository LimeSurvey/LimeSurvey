<?php
include_once(dirname(__FILE__).'/attributes.php');
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
         
$test = new attributes($connect,$dbprefix);
$array = $test->get_attributes('A');
print_r($array);
print "<br><br>";
$value = $test->get_attribute_value(206,'array_filter');
print $value;
?>