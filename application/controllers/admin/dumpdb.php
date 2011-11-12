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
class Dumpdb extends Admin_Controller {

    function __construct()
	{
		parent::__construct();
	}

    /**
     * Dumpdb::index()
     * Dump database. Only LimeSurvey tables are dumped.
     * @return void
     */
    function index()
    {
        $this->load->dbutil();
        $this->load->helper("string");

        if ($this->dbutil->database_exists($this->db->database) && ($this->db->dbdriver=='mysql' || $this->db->dbdriver=='mysqli') && $this->config->item('demoMode') != true) {

            $tables = $this->db->list_tables();

            foreach ($tables as $table)
            {
               if ($this->db->dbprefix==substr($table, 0, strlen($this->db->dbprefix)))
               {
                    $lstables[] = $table;
               }
            }


            $sfilename = "backup_db_".random_string('unique')."_".date_shift(date("Y-m-d H:i:s"), "Y-m-d", $this->config->item('timeadjust')).".sql";
            $dfilename = "LimeSurvey_".$this->db->database."_dump_".date_shift(date("Y-m-d H:i:s"), "Y-m-d", $this->config->item('timeadjust')).".sql.gz";
            $prefs = array(
                'format'      => 'zip',             // gzip, zip, txt
                   // File name - NEEDED ONLY WITH ZIP FILES
                'filename'    => $sfilename,
                'tables'      => $lstables,
                'add_drop'    => TRUE,              // Whether to add DROP TABLE statements to backup file
                'add_insert'  => TRUE,              // Whether to add INSERT data to backup file
                'newline'     => "\n"               // Newline character used in backup file
              );

            $this->dbutil->backup($prefs);
            $backup =& $this->dbutil->backup();

            $this->load->helper('file');
            write_file('tmp/'.$sfilename.".gz", $backup);

            $this->load->helper('download');
            force_download($dfilename, $backup);




        }
        else
        {
            show_error("This feature is only available for MySQL databases.");
        }

    }

    /**
    function index2()
    {

        $this->load->dbutil();

        if ($this->dbutil->database_exists($this->db->database) && ($this->db->dbdriver=='mysql' || $this->db->dbdriver=='mysqli') && $this->config->item('demoMode') != true && $action=='dumpdb') {

            $export=self::_completedump();

            $file_name = "LimeSurvey_".$this->db->database."_dump_".date_shift(date("Y-m-d H:i:s"), "Y-m-d", $this->config->item('timeadjust')).".sql";
            Header("Content-type: application/octet-stream");
            Header("Content-Disposition: attachment; filename=$file_name");
            Header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            echo $export;
            exit; // needs to be inside the condition so the updater still can include this file
        }

    }

    function _defdump($tablename)
    {
        //global $connect;
        $this->load->helper("database");
        $def = "";
        $def .="#\n";
        $def .="# Table definition for {$tablename}"."\n";
        $def .="#\n";
        $def .= "DROP TABLE IF EXISTS {$tablename};"."\n"."\n";
        $def .= "CREATE TABLE {$tablename} ("."\n";
        $result = db_execute_assoc("SHOW COLUMNS FROM {$tablename}") or die("Table $tablename not existing in database");
        foreach($result->result_array() as $row)
        {
            $def .= "    `$row[Field]` $row[Type]";
            if (!is_null($row["Default"])) $def .= " DEFAULT '$row[Default]'";
            if ($row["Null"] != "YES") $def .= " NOT NULL";
            if ($row["Extra"] != "") $def .= " $row[Extra]";
            $def .= ",\n";
        }
        $def = preg_replace("#,\n$#","", $def);

        $result = db_execute_assoc("SHOW KEYS FROM $tablename");
        foreach($result->result_array() as $row)
        {
            $kname=$row["Key_name"];
            if(($kname != "PRIMARY") && ($row["Non_unique"] == 0)) $kname="UNIQUE|$kname";
            if(!isset($index[$kname])) $index[$kname] = array();
            if ($row["Sub_part"]!='')  $row["Column_name"].=" ({$row["Sub_part"]})";
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

    function _datadump ($table) {

        //global $connect;
        $this->load->helper("database");

        $result = "#\n";
        $result .="# Table data for $table"."\n";
        $result .="#\n";

        $query = db_execute_num("select * from $table");
        $num_fields = $query->FieldCount();
        $aFieldNames= $connect->MetaColumnNames($table, true);
        $sFieldNames= implode('`,`',$aFieldNames);
        $numrow = $query->RecordCount();

        if ($numrow>0)
        {
            $result .= "INSERT INTO `{$table}` (`{$sFieldNames}`) VALUES";
        while($row=$query->FetchRow()){
            @set_time_limit(5);
                $result .= "(";
            for($j=0; $j<$num_fields; $j++) {
                if (isset($row[$j]) && !is_null($row[$j]))
                {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = preg_replace("#\n#","\\n",$row[$j]);
                    $result .= "\"$row[$j]\"";
                }
                else
                {
                    $result .= "NULL";
                }

                if ($j<($num_fields-1)) $result .= ",";
            }
                $result .= "),\n";
        } // while
            $result=substr($result,0,-2);
    }
        return $result . ";\n\n";
    }
    */
    /**
     * Creates a full dump of the current LimeSurvey database
     *
     * @returns string Contains the dumped data
     */
     /**
    function _completedump()
    {
        global $connect, $databasename, $dbprefix, $allowexportalldb;
        $tables = $connect->MetaTables();
        $export ="#------------------------------------------"."\n";
        $export .="# LimeSurvey Database Dump of `$databasename`"."\n";
        if ($allowexportalldb==0) {
            $export .="# Only prefixed tables with: ". $dbprefix ."\n";
        }
        $export .="# Date of Dump: ". date("d-M-Y") ."\n";
        $export .="#------------------------------------------"."\n\n\n";

        foreach($tables as $table) {
            if ($allowexportalldb==0) {
                if ($dbprefix==substr($table, 0, strlen($dbprefix))) {
                    $export .= self::_defdump($table);
                    $export .= self::_datadump($table);
                }
            }
            else {
                $export .= self::_defdump($table);
                $export .= self::_datadump($table);
            }
        }
        return $export;
    }
    */



}