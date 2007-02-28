<?php
/*
#############################################################
# >>> PHPSurveyor  										#
#############################################################
# > Author:  Jason Cleeland									#
# > E-mail:  jason@cleeland.org								#
# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
# >          CARLTON SOUTH 3053, AUSTRALIA
# > Date: 	 20 February 2003								#
#															#
# This set of scripts allows you to develop, publish and	#
# perform data-entry on surveys.							#
#############################################################
#															#
#	Copyright (C) 2003  Jason Cleeland						#
#															#
# This program is free software; you can redistribute 		#
# it and/or modify it under the terms of the GNU General 	#
# Public License Version 2 as published by the Free         #
# Software Foundation.										#
#															#
#															#
# This program is distributed in the hope that it will be 	#
# useful, but WITHOUT ANY WARRANTY; without even the 		#
# implied warranty of MERCHANTABILITY or FITNESS FOR A 		#
# PARTICULAR PURPOSE.  See the GNU General Public License 	#
# for more details.											#
#															#
# You should have received a copy of the GNU General 		#
# Public License along with this program; if not, write to 	#
# the Free Software Foundation, Inc., 59 Temple Place - 	#
# Suite 330, Boston, MA  02111-1307, USA.					#
#############################################################
*/

require_once(dirname(__FILE__).'/../config.php');

include_once("login_check.php");

if($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1)
	{
	if ($database_exists) $tables = $connect->MetaTables();
	
	$export="";
	$export .="#------------------------------------------"."\n";
	$export .="# PHPSurveyor Database Dump of `$databasename`"."\n";
	if ($allowexportalldb==0) {
		$export .="# Only prefixed tables with: ". $dbprefix ."\n";
	}
	$export .="# Date of Dump: ". date("d-M-Y") ."\n";
	$export .="#------------------------------------------"."\n\n\n";
	
	foreach($tables as $table) {
		if ($allowexportalldb==0) {
			if ($dbprefix==substr($table, 0, strlen($dbprefix))) {
				$export .= defdump($table);
				$export .= datadump($table);
			}
		}
		else {
			$export .= defdump($table);
			$export .= datadump($table);
		}
	}
	
	$file_name = "PHPSurveyor_{$databasename}_dump_".date("Y-m-d").".sql";
	Header("Content-type: application/octet-stream");
	Header("Content-Disposition: attachment; filename=$file_name");
	Header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	echo $export;
	exit;

	}
else
	{
	$action = "dumpdb";
	include("access_denied.php");
	include("admin.php");	
	}

	function defdump($tablename)
	{
		global $connect;
		$def = "";
		$def .="#------------------------------------------"."\n";
		$def .="# Table definition for $tablename"."\n";
		$def .="#------------------------------------------"."\n";
		$def .= "DROP TABLE IF EXISTS $tablename;"."\n"."\n";
		$def .= "CREATE TABLE $tablename ("."\n";
		$result = db_execute_assoc("SHOW COLUMNS FROM $tablename") or die("Table $tablename not existing in database");
		while($row = $result->FetchRow())
		{
			$def .= "    $row[Field] $row[Type]";
			if (!is_null($row["Default"])) $def .= " DEFAULT '$row[Default]'";
			if ($row["Null"] != "YES") $def .= " NOT NULL";
			if ($row["Extra"] != "") $def .= " $row[Extra]";
			$def .= ",\n";
		}
		$def = ereg_replace(",\n$","", $def);
	
		$result = db_execute_assoc("SHOW KEYS FROM $tablename");
		while($row = $result->FetchRow())
		{
			$kname=$row["Key_name"];
			if(($kname != "PRIMARY") && ($row["Non_unique"] == 0)) $kname="UNIQUE|$kname";
			if(!isset($index[$kname])) $index[$kname] = array();
			$index[$kname][] = $row["Column_name"];
		}
	
		while(list($x, $columns) = @each($index))
		{
			$def .= ",\n";
			if($x == "PRIMARY") $def .= "   PRIMARY KEY (" . implode($columns, ", ") . ")";
			else if (substr($x,0,6) == "UNIQUE") $def .= "   UNIQUE ".substr($x,7)." (" . implode($columns, ", ") . ")";
			else $def .= "   KEY $x (" . implode($columns, ", ") . ")";
		}
		$def .= "\n);\n\n\n";
		return (stripslashes($def));
	}
	
	
	function datadump ($table) {
	
		global $connect;
	
		$result = "#------------------------------------------"."\n";
		$result .="# Table data for $table"."\n";
		$result .="#------------------------------------------"."\n";
	
		$query = db_execute_num("select * from $table");
		$num_fields = $query->FieldCount();
		$numrow = $query->RecordCount();
	
		while($row=$query->FetchRow()){
			@set_time_limit(5);
			$result .= "INSERT INTO ".$table." VALUES(";
			for($j=0; $j<$num_fields; $j++) {
				$row[$j] = addslashes($row[$j]);
				$row[$j] = ereg_replace("\n","\\n",$row[$j]);
				if (isset($row[$j])) $result .= "\"$row[$j]\"" ; else $result .= "\"\"";
				if ($j<($num_fields-1)) $result .= ",";
			}
			$result .= ");\n";
		} // while
		return $result . "\n\n\n";
	}	

?>
