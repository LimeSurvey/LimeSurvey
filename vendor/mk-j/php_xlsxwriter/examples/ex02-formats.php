<?php
set_include_path( get_include_path().PATH_SEPARATOR."..");
include_once("xlsxwriter.class.php");

$sheet1header = array(
    'c1-string'=>'string',
    'c2-integer'=>'integer',
    'c3-custom-integer'=>'0',
    'c4-custom-1decimal'=>'0.0',
    'c5-custom-2decimal'=>'0.00',
    'c6-custom-percent'=>'0%',
    'c7-custom-percent1'=>'0.0%',
    'c8-custom-percent2'=>'0.00%',
    'c9-custom-text'=>'@',//text
);
$sheet2header = array(
    'col1-date'=>'date',
    'col2-datetime'=>'datetime',
    'col3-time'=>'time',
    'custom-date1'=>'YYYY-MM-DD',
    'custom-date2'=>'MM/DD/YYYY',
    'custom-date3'=>'DD-MMM-YYYY HH:MM AM/PM',
    'custom-date4'=>'MM/DD/YYYY HH:MM:SS',
    'custom-date5'=>'YYYY-MM-DD HH:MM:SS',
    'custom-date6'=>'YY MMMM',
    'custom-date7'=>'QQ YYYY',
    'custom-time1'=>'HH:MM',
    'custom-time2'=>'HH:MM:SS',
);
$sheet3header = array(
    'col1-dollar'=>'dollar',
    'col2-euro'=>'euro',
    'custom-amount1'=>'0',
    'custom-amount2'=>'0.0',//1 decimal place
    'custom-amount3'=>'0.00',//2 decimal places
    'custom-currency1'=>'#,##0.00',//currency 2 decimal places, no currency/dollar sign
    'custom-currency2'=>'[$$-1009]#,##0.00;[RED]-[$$-1009]#,##0.00',//w/dollar sign
    'custom-currency3'=>'#,##0.00 [$€-407];[RED]-#,##0.00 [$€-407]',//w/euro sign
    'custom-currency4'=>'[$￥-411]#,##0;[RED]-[$￥-411]#,##0', //japanese yen
    'custom-scientific'=>'0.00E+000',//-1.23E+003 scientific notation
);
$pi = 3.14159;
$date = '2018-12-31 23:59:59';
$time = '23:59:59';
$amount = '5120.5';

$writer = new XLSXWriter();
$writer->setAuthor('Some Author');
$writer->writeSheetHeader('BasicFormats',$sheet1header);
$writer->writeSheetRow('BasicFormats',array($pi,$pi,$pi,$pi,$pi,$pi,$pi,$pi,$pi) );
$writer->writeSheetHeader('Dates',$sheet2header);
$writer->writeSheetRow('Dates',array($date,$date,$date,$date,$date,$date,$date,$date,$date,$date,$time,$time) );
$writer->writeSheetHeader('Currencies',$sheet3header);
$writer->writeSheetRow('Currencies',array($amount,$amount,$amount,$amount,$amount,$amount,$amount,$amount,$amount) );
$writer->writeToFile('xlsx-formats.xlsx');
//$writer->writeToStdOut();
//echo $writer->writeToString();

echo '#'.floor((memory_get_peak_usage())/1024/1024)."MB"."\n";
exit(0);





