<?php
include_once("../../xlsxwriter.class.php");

$writer = new XLSXWriter();
$keywords = array('some','interesting','keywords');

$writer->setTitle('Some Title');
$writer->setSubject('Some Subject');
$writer->setAuthor('Some Author');
$writer->setCompany('Some Company');
$writer->setKeywords($keywords);
$writer->setDescription('Some interesting description');

$header = array(
    'General'=>'string',
    'Simple Integer'=>'0',
    '2 Decimal Places Integer'=>'0.00',
    'Integer 1000s Group'=>'#,##0',
    '1000s,Decimal,Leading Zero'=>'#,##0.00',
    '1000s,Decimal,No Leading Zero'=>'#,###.00',
    'Negative In Parentheses'=>'#,##0_);(#,##0)',
    'Negative In Parentheses With Decimal'=>'#,##0.00_);(#,##0.00)',
);
$row = array('1000','2000','3000','4000','0.50','0.50','-50','-50');
$writer->writeSheet(array( $row ),'Number',$header);

$header = array(
    'Whole Percent'=>'0%',
    'Decimal Percent'=>'0.00%',
);
$row = array('1','1');
$writer->writeSheet(array( $row ),'Percent',$header);

$header = array(
    'USD'=>'[$$-409]#,##0.00;[RED]-[$$-409]#,##0.00',
    'CAD'=>'[$$-1009]#,##0.00;[RED]-[$$-1009]#,##0.00',
    'Euro'=>'#,##0.00 [$€-407];[RED]-#,##0.00 [$€-407]',
    'JPY'=>'[$￥-411]#,##0;[RED]-[$￥-411]#,##0',
    'CNY'=>'[$￥-804]#,##0.00;[RED]-[$￥-804]#,##0.00',
);
$row = array('1000','2000','3000','4000','5000');
$writer->writeSheet(array( $row ) ,'Currency',$header);

$header = array(
    'M/D/YY'=>'M/D/YY',
    'MM/DD/YYYY'=>'MM/DD/YYYY',
    'YYYY-MM-DD'=>'YYYY-MM-DD',
    'YYYY-MM-DD HH:MM:SS'=>'YYYY-MM-DD HH:MM:SS',
    'NN'=>'NN',
    'NNN'=>'NNN',
    'NNNN'=>'NNNN',
    'D'=>'D',
    'DD'=>'DD',
    'M'=>'M',
    'MM'=>'MM',
    'MMM'=>'MMM',
    'MMMM'=>'MMMM',
    'YY'=>'YY',
    'YYYY'=>'YYYY',
    'Q YY'=>'Q YY',
    'Q YYYY'=>'Q YYYY',   
);
$row = array('1999-01-01','1999-01-01','1999-12-31','1999-12-31 00:00:00',
	'1999-12-31','1999-12-31','1999-12-31',
	'1999-12-31','1999-12-31','1999-12-31',
	'1999-12-31','1999-12-31','1999-12-31',
	'1999-12-31','1999-12-31','1999-12-31',
	'1999-12-31');
$writer->writeSheet(array( $row ) ,'Date',$header);

$header = array(
    'HH:MM'=>'HH:MM',
    'HH:MM:SS'=>'HH:MM:SS',
    'HH:MM AM/PM'=>'HH:MM AM/PM',
    'HH:MM:SS AM/PM'=>'HH:MM:SS AM/PM',
);
$row = array('12-31-1999 01:23:00','12-31-1999 01:23:00','12-31-1999 01:23:00','12-31-1999 01:23:00');
$writer->writeSheet(array( $row ) ,'Time',$header);

$writer->writeToFile('formats_.xlsx');




