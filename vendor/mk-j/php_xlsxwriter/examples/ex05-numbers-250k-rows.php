<?php
set_include_path( get_include_path().PATH_SEPARATOR."..");
include_once("xlsxwriter.class.php");

$header = array('c1'=>'integer','c2'=>'integer','c3'=>'integer','c4'=>'integer');

$writer = new XLSXWriter();
$writer->writeSheetHeader('Sheet1', $header);
for($i=0; $i<250000; $i++)
{
    $writer->writeSheetRow('Sheet1', array(rand()%10000,rand()%10000,rand()%10000,rand()%10000) );
}
$writer->writeToFile('xlsx-numbers-250k.xlsx');
echo '#'.floor((memory_get_peak_usage())/1024/1024)."MB"."\n";
