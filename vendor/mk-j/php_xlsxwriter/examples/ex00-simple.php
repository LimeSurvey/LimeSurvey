<?php
set_include_path( get_include_path().PATH_SEPARATOR."..");
include_once("xlsxwriter.class.php");

$header = array(
  'c1-text'=>'string',//text
  'c2-text'=>'@',//text
  'c3-integer'=>'integer',
  'c4-integer'=>'0',
  'c5-price'=>'price',
  'c6-price'=>'#,##0.00',//custom
  'c7-date'=>'date',
  'c8-date'=>'YYYY-MM-DD',
);
$rows = array(
  array('x101',102,103,104,105,106,'2018-01-07','2018-01-08'),
  array('x201',202,203,204,205,206,'2018-02-07','2018-02-08'),
  array('x301',302,303,304,305,306,'2018-03-07','2018-03-08'),
  array('x401',402,403,404,405,406,'2018-04-07','2018-04-08'),
  array('x501',502,503,504,505,506,'2018-05-07','2018-05-08'),
  array('x601',602,603,604,605,606,'2018-06-07','2018-06-08'),
  array('x701',702,703,704,705,706,'2018-07-07','2018-07-08'),
);
$writer = new XLSXWriter();

$writer->writeSheetHeader('Sheet1', $header);
foreach($rows as $row)
	$writer->writeSheetRow('Sheet1', $row);

//$writer->writeSheet($rows,'Sheet1', $header);//or write the whole sheet in 1 call

$writer->writeToFile('xlsx-simple.xlsx');
//$writer->writeToStdOut();
//echo $writer->writeToString();

