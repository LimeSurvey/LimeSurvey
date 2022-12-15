<?php
include_once("../xlsxwriter.class.php");

$header = array(
    'year'=>'string',
    'month'=>'string',
    'amount'=>'money',
    'first_event'=>'datetime',
    'second_event'=>'date',
);
$data1 = array(
    array('2003','1','-50.5','2010-01-01 23:00:00','2012-12-31 23:00:00'),
    array('2003','=B2', '23.5','2010-01-01 00:00:00','2012-12-31 00:00:00'),
);
$data2 = array(
    array('2003','01','343.12'),
    array('2003','02','345.12'),
);
$writer = new XLSXWriter();
$keywords = array('some','interesting','keywords');

$writer->setTitle('Some Title');
$writer->setSubject('Some Subject');
$writer->setAuthor('Some Author');
$writer->setCompany('Some Company');
$writer->setKeywords($keywords);
$writer->setDescription('Some interesting description');
$writer->writeSheet($data1,'Sheet1',$header);
$writer->writeSheet($data2,'Sheet2');
$writer->writeToFile('test.xlsx');



