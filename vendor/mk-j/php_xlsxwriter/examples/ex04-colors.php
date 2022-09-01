<?php
set_include_path( get_include_path().PATH_SEPARATOR."..");
include_once("xlsxwriter.class.php");

$writer = new XLSXWriter();
$colors = array('ff','cc','99','66','33','00');
foreach($colors as $b) {
	foreach($colors as $g) {
		$rowdata = array();
		$rowstyle = array();
		foreach($colors as $r) {
			$rowdata[] = "#$r$g$b";
			$rowstyle[] = array('fill'=>"#$r$g$b");
		}
		$writer->writeSheetRow('Sheet1', $rowdata, $rowstyle );
	}
}
$writer->writeToFile('xlsx-colors.xlsx');
