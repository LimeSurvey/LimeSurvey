<?php
set_include_path( get_include_path().PATH_SEPARATOR."..");
include_once("xlsxwriter.class.php");

$chars = 'abcdefgh';

$header = array('col-string'=>'string','col-numbers'=>'integer','col-timestamps'=>'datetime');

$writer = new XLSXWriter();
$writer->writeSheetHeader('Sheet1', $header, ['auto_filter'=>true, 'widths'=>[15,15,30]] );
for($i=0; $i<1000; $i++)
{
    $writer->writeSheetRow('Sheet1', array(
        str_shuffle($chars),
        rand()%10000,
        date('Y-m-d H:i:s',time()-(rand()%31536000))
    ));
}
$writer->writeToFile('xlsx-autofilter.xlsx');
echo '#'.floor((memory_get_peak_usage())/1024/1024)."MB"."\n";
