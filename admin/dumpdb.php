<?php


require_once("config.php");

if ($result=mysql_list_tables($databasename)) {
	while($row=mysql_fetch_row($result)){
		$tables[]=$row[0];
	} // while
}

$export="";
$export .="#------------------------------------------"."\n";
$export .="# PHPSurveyor Database Dump of `$databasename`"."\n";
$export .="# Date of Dump: ". date("d-M-Y") ."\n";
$export .="#------------------------------------------"."\n\n\n";

foreach($tables as $table) {
	$export .= defdump($table);
	$export .= datadump($table);
}

$file_name = "PHPSurveyor_{$databasename}_dump_".date("Y-m-d").".sql";
Header("Content-type: application/octet-stream"); 
Header("Content-Disposition: attachment; filename=$file_name");
echo $export; 
exit;


function defdump($tablename)
    {
        $def = "";
        $def .="#------------------------------------------"."\n";
        $def .="# Table definition for $tablename"."\n";
        $def .="#------------------------------------------"."\n";
        $def .= "DROP TABLE IF EXISTS $tablename;"."\n"."\n";
        $def .= "CREATE TABLE $tablename ("."\n";
        $result = @mysql_query("SHOW FIELDS FROM $tablename") or die("Table $tablename not existing in database");
        while($row = @mysql_fetch_array($result))
        {
          $def .= "    $row[Field] $row[Type]";
          if ($row["Default"] != "") $def .= " DEFAULT '$row[Default]'";
          if ($row["Null"] != "YES") $def .= " NOT NULL";
          if ($row["Extra"] != "") $def .= " $row[Extra]";
          $def .= ",\n";
        }
        $def = ereg_replace(",\n$","", $def);

        $result = @mysql_query("SHOW KEYS FROM $tablename");
        while($row = @mysql_fetch_array($result))
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
		//echo "<pre>$def</pre><br />";
        return (stripslashes($def));
    }


function datadump ($table) {
	
	$result = "#------------------------------------------"."\n";
	$result .="# Table data for $table"."\n";
	$result .="#------------------------------------------"."\n";
	
    $query = mysql_query("select * from $table");
    $num_fields = @mysql_num_fields($query);
    $numrow = mysql_num_rows($query);
	
	while($row=mysql_fetch_row($query)){
		set_time_limit(5);
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