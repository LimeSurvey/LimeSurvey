<?php
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id: dumpdb.php 11155 2011-10-13 12:59:49Z c_schmitz $
 */
/**
 * Dumpdb
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
 * @version $Id: dumpdb.php 11155 2011-10-13 12:59:49Z c_schmitz $
 * @access public
 */
class Dumpdb extends AdminController {

	var $iMaxRecords;

	/**
	 * Base function
	 *
	 * This functions receives the request to generate a dump file for the
	 * database and does so! Only LimeSurvey tables are dumped.
	 * Only superadmins are allowed to do this!
	 *
	 * @access public
	*/
	public function runWithParams()
	{
		if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1) {
			die();
		}

		$connection = Yii::app()->db;
		$this->iMaxRecords = Yii::app()->getConfig('maxdumpdbrecords');

		if (!in_array($connection->getDriverName(), array('mysql', 'mysqli')) || Yii::app()->getConfig('demoMode') == true) {
			die("This feature is only available for MySQL databases.");
		}

		// Yii doesn't give us a good way to get the database name
		$dbname = preg_match("/dbname=([^;]*)/", $connection->getSchema()->getDbConnection()->connectionString, $matches);
		$dbname = $matches[1];

		$file_name = "LimeSurvey_".$dbname."_dump_".date_shift(date("Y-m-d H:i:s"), "Y-m-d", Yii::app()->getConfig('timeadjust')).".sql";

		Header("Content-type: application/octet-stream");
		Header("Content-Disposition: attachment; filename=$file_name");
		Header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

		echo "-- "."\n";
		echo "-- LimeSurvey Database Dump of `$dbname`"."\n";
		self::_completedump();

		exit; // needs to be inside the condition so the updater still can include this file
	}

	/**
	 * Outputs a full dump of the current LimeSurvey database
	 */
	function _completedump()
	{
		$allowexportalldb = (bool) Yii::app()->getConfig('allowexportalldb');

		$connection = Yii::app()->db;
		$tables = $connection->getSchema()->getTables();
		if ($allowexportalldb==0) {
			echo "-- Only prefixed tables with: ".$connection->tablePrefix."\n";
		}
		echo "-- Date of Dump: ".date_shift(date("d-M-Y"), "d-M-Y", Yii::app()->getConfig('timeadjust'))."\n";
		echo "-- "."\n";

		foreach($tables as $tablename => $tabledata) {
			if ($allowexportalldb == 0 && $connection->tablePrefix != substr($tablename, 0, strlen($connection->tablePrefix)))
				continue;
			self::_defdump($tablename);
			self::_datadump($tablename, $tabledata);
		}
	}

	/**
	 * Outputs the table structure in sql format
	 */
	function _defdump($tablename)
	{

		$connection  = Yii::app()->db;

		$def  ="\n"."-- --------------------------------------------------------"."\n\n";
		$def .="--\n";
		$def .="-- Table structure for table `{$tablename}`"."\n";
		$def .="--\n\n";
		$def .= "DROP TABLE IF EXISTS `{$tablename}`;"."\n";

		$sSql = "SHOW CREATE TABLE `{$tablename}`";
		$aCreateTable = $connection->createCommand($sSql)->queryRow();
		$def .= $aCreateTable['Create Table'].";\n\n";
		echo $def;
	}

	/**
	 * Outputs the table data in sql format
	 */
	function _datadump($tablename, $tabledata)
	{
		$connection  = Yii::app()->db;
		$result  = "--\n";
		$result .="-- Dumping data for table `$tablename`"."\n";
		$result .="--\n\n";
		echo $result;

		$sSql = "SELECT COUNT(*) FROM `$tablename`";
		$aNumRows = $connection->createCommand($sSql)->queryRow();
		$iNumRows = $aNumRows['COUNT(*)'];
		if ($iNumRows < 1)
			return;

		for($i=0; $i < ceil($iNumRows/$this->iMaxRecords); $i++) {
			$aResults = $connection->createCommand()
				->select()
				->from($tablename)
				->limit($this->iMaxRecords, ($i != 0 ? ($i*$this->iMaxRecords) + 1 : null))
				->query()->readAll();

			$aFieldNames = array_keys($tabledata->columns);
			$iNumFields  = count($aFieldNames);
			$result = "";

			foreach($aResults as $row){
				$result .= "INSERT INTO `{$tablename}` VALUES(";

				foreach($aFieldNames as $sFieldName) {
					if (isset($row[$sFieldName]) && !is_null($row[$sFieldName]))
					{
						$row[$sFieldName] = addslashes($row[$sFieldName]);
						$row[$sFieldName] = preg_replace("#\n#","\\n",$row[$sFieldName]);
						$result .= "\"{$row[$sFieldName]}\"";
					}
					else
					{
						$result .= "NULL";
					}

					if (end($aFieldNames) != $sFieldName)
						$result .= ", ";
				}

				$result .= ");\n";
			}

			echo $result . "\n";
		}
	}

}