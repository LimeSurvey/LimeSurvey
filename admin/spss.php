<?php
require_once(dirname(__FILE__).'/../config.php');
if (!isset($imagefiles)) {$imagefiles="./images";}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($style)) {$style=returnglobal('style');}
if (!isset($answers)) {$answers=returnglobal('answers');}
if (!isset($type)) {$type=returnglobal('type');}

if (empty($surveyid)) {die("Cannot run this script directly");}
#Get all legitimate question ids

header("Content-Type: application/octetstream");
header("Content-Disposition: ".(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 5.5")?"":"attachment; ")."filename=survey".$surveyid.".sps");

sendcacheheaders();

$query = "SELECT DISTINCT qid FROM ".db_table_name("questions")." WHERE sid=$surveyid"; //GET LIST OF LEGIT QIDs FOR TESTING LATER
$result=$connect->Execute($query) or die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());
$num_results = $result->RecordCount();
# Build array that has to be returned
$fieldmap=createFieldMap($surveyid);
#See if tokens are being used
$tablelist = $connect->MetaTables() or die ("Error getting table list<br />".$connect->ErrorMsg());
foreach ($tablelist as $tbl)
{
	if ($tbl == "{$dbprefix}tokens_$surveyid") {$tokensexist =  1;}
}

#Lookup the names of the attributes
$query="SELECT sid, attribute1, attribute2, private FROM {$dbprefix}surveys WHERE sid=$surveyid";
$result=db_execute_assoc($query) or die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());
$num_results = $result->RecordCount();
$num_fields = $num_results;
# Build array that has to be returned
for ($i=0; $i < $num_results; $i++) {
	$row = $result->FetchRow();
	if ($row["attribute1"]) {$attr1_name = $row["attribute1"];} else {$attr1_name=_TL_ATTR1;}
	if ($row["attribute2"]) {$attr2_name = $row["attribute2"];} else {$attr2_name=_TL_ATTR2;}
	$surveyprivate=$row['private'];
}

if (isset($tokensexist) && $tokensexist == 1 && $surveyprivate == "N") {
	$query="SHOW COLUMNS FROM ".db_table_name("tokens_$surveyid");
	$result=db_execute_num($query) or die("Couldn't count fields in tokens<br />$query<br />".$connect->ErrorMsg());
	while ($row=$result->FetchRow()) {
		$token_fields[]=$row[0];
	}
	if (in_array("firstname", $token_fields)) {
		$fields[$fieldno++]=array("id"=>"fname","name"=>_TL_FIRST,"code"=>"", "qid"=>0,"type"=>"A40" );
	}
	if (in_array("lastname", $token_fields)) {
		$fields[$fieldno++]=array("id"=>"lname","name"=>_TL_LAST,"code"=>"", "qid"=>0,"type"=>"A40" );
	}
	if (in_array("email", $token_fields)) {
		$fields[$fieldno++]=array("id"=>"email","name"=>_TL_EMAIL,"code"=>"", "qid"=>0,"type"=>"A100");
	}
	if (in_array("attribute_1", $token_fields)) {
		$fields[$fieldno++]=array("id"=>"attr1","name"=>$attr1_name,"code"=>"", "qid"=>0,"type"=>"A100");
	}
	if (in_array("attribute_2", $token_fields)) {
		$fields[$fieldno++]=array("id"=>"attr2","name"=>$attr2_name,"code"=>"", "qid"=>0,"type"=>"A100");
	}
} else {
	$fields=array();
}

// Lets Define Make an arrays of all the fields and add them onto $fields array.
$fieldno=1;
$tempArray = array();
$query="SHOW COLUMNS FROM ".db_table_name("survey_$surveyid");
$result=db_execute_assoc($query) or die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());
$num_results = $result->RecordCount();
$num_fields = $num_results;
for ($i=0; $i < $num_results; $i++) {
	$row = $result->FetchRow();
	#Conditions for SPSS fields:
	# - Length may not be longer than 8 characters
	# - Name may not begin with a digit
	$fieldname = $row["Field"];

	#Rename 'datestamp' to stamp
	if ($fieldname =="submitdate") {
		$fieldname = "stamp";
	}

	#Determine field type
	if ($fieldname=="stamp"){
		$fieldtype = "DATETIME20.0";
	} elseif (isset($fieldname) && $fieldname != "")
	{
		# Determine the SPSS Variable Type
		$val_query="SELECT $fieldname FROM {$dbprefix}survey_$surveyid";
		$val_result=db_execute_assoc($query) or die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());
		$teststring = "";
		while ($val_row = $val_result->FetchRow())
		{
			if ($val_row[$fieldname] != "-oth-") $teststring .= $val_row[$fieldname];
		}
		if (is_numeric($teststring))
		{
			$fieldtype = "N8";
		} elseif (strlen($teststring) < 9)
		{
			$fieldtype = "A8";
		} else
		{
			$fieldtype = "A".strlen($teststring);
		}
	}
#Get qid (question id)
$code="";
if ($fieldname == "id" OR $fieldname=="token" OR $fieldname=="stamp" OR $fieldname=="attribute_1" OR $fieldname=="attribute_2"){
	$qid = 0;
}else{
	//GET FIELD DATA
	$fielddata=arraySearchByKey($fieldname, $fieldmap, "fieldname", 1);
	$qid=$fielddata['qid'];
	$ftype=$fielddata['type'];
	$fsid=$fielddata['sid'];
	$fgid=$fielddata['gid'];
	$code=$fielddata['aid'];
	$qtitle=str_replace(" ", '',$fielddata['title']);
}
$tempArray=array($fieldno++ =>array("id"=>"d".$fieldno,"name"=>substr($fieldname, 0, 8),"qid"=>$qid, "code"=>$code, "type"=>"$fieldtype"));
$fields = $fields + $tempArray;
}

// Lets Define SPSS Variables

echo "DATA LIST FREE\n /";
foreach ($fields as $field){
	echo $field["id"];
	echo "(".$field["type"].") ";
}
echo ".";
echo "\n";

// Lets Put in the Data

echo "BEGIN DATA\n";

if (isset($tokensexist) && $tokensexist == 1 && $surveyprivate == "N") {
	$query="SELECT ".db_table_name("tokens_$surveyid").".".db_quote_id("firstname")." ,
	       ".db_table_name("tokens_$surveyid").".".db_quote_id("lastname")."  ,
	       ".db_table_name("tokens_$surveyid").".".db_quote_id("email");
	if (in_array("attribute_1", $token_fields)) {
		$query .= ",\n		`{$dbprefix}tokens_$surveyid`.`attribute_1`";
	}
	if (in_array("attribute_2", $token_fields)) {
		$query .= ",\n		`{$dbprefix}tokens_$surveyid`.`attribute_2`";
	}
	$query .= ",\n	       `{$dbprefix}survey_$surveyid`.*
	FROM `{$dbprefix}survey_$surveyid`
	LEFT JOIN `{$dbprefix}tokens_$surveyid` ON `{$dbprefix}survey_$surveyid`.`token` = `{$dbprefix}tokens_$surveyid`.`token`";
} else {
	$query = "SELECT `{$dbprefix}survey_$surveyid`.*
	FROM `{$dbprefix}survey_$surveyid`";
}

$result=db_execute_num($query) or die("Couldn't get results<br />$query<br />".$connect->ErrorMsg());
$num_results = $result->RecordCount();
$num_fields = $result->FieldCount();
for ($i=0; $i < $num_results; $i++) {
	$row = $result->FetchRow();
	$fieldno = 0;
	while ($fieldno < $num_fields){
		// if ($fields[$fieldno]["id"]=="stamp"){
		// Must be changed if d2 is no longer the date field
		if ($fields[$fieldno]["id"]=="d2"){
			#convert mysql  datestamp (yyyy-mm-dd hh:mm:ss) to SPSS datetime (dd-mmm-yyyy hh:mm:ss) format
			list( $year, $month, $day, $hour, $minute, $second ) = split( '([^0-9])', $row[$fieldno] );
			echo "'".date("d-m-Y H:i:s", mktime( $hour, $minute, $second, $month, $day, $year ) )."' ";
		}else {
			// Remove apostrophes (delimiters)
			$temp = str_replace("\"","",$row[$fieldno]);
			// Remove extra spaces (delimiters)
			$temp = str_replace(" ","",$temp);
			// Only return first 5 characters
			echo "\"".substr($temp,0,20)."\" ";
		}
		$fieldno++;
	}
	echo "\n";
}
echo "END DATA.\n";

// Lets put our labels in!
echo "*Define Variable Properties.\n";
reset($fields);

foreach ($fields as $field)
{
	if ($field["id"] == "fname" || $field["id"]=="lname" || $field["id"]=="email" || $field["id"]=="attr1" || $field["id"]=="attr2"){
		echo "VARIABLE LABELS ".$field["id"]." \"".str_replace("\"", '', $field["name"])."\".\n";
	} elseif ($field["id"] == "d1" || $field["id"] == "d2")
	{
		if ($field["id"] == "d1") echo "VARIABLE LABELS d1 \"RecordID\".\n";
		if ($field["id"] == "d2") echo "VARIABLE LABELS d2 \"RecordDate\".\n";
	} else
	{
		#If a split question
		if ($field["code"] != "") // Its a multianswer
		{
			#Lookup the question
			$query = "SELECT `{$dbprefix}questions`.`question` FROM {$dbprefix}questions WHERE ((`{$dbprefix}questions`.`sid` =".$surveyid.") AND (`{$dbprefix}questions`.`qid` =".$field["qid"]."))";
			$result=db_execute_assoc($query) or die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());
			$num_results = $result->RecordCount();
			if ($num_results > 0)
			{
				while ($val_row = $result->FetchRow())
				{
					#Find the question's answer
					$aquery = "SELECT `{$dbprefix}answers`.`answer` FROM {$dbprefix}answers WHERE ((`{$dbprefix}answers`.`qid` =".$field["qid"].") AND (`{$dbprefix}answers`.`code` ='".$field["code"]."'))";
					$aresult=db_execute_assoc($aquery) or die("Couldn't Find Answer<br />$aquery<br />".$connect->ErrorMsg());
					$anum_results = $result->RecordCount();
					$ltitle="";
					$lanswer="";
					if ($anum_results > 0)
					{
						$row = $aresult->FetchRow();
						$ltitle = str_replace("\"", '', $val_row['title']);
						$ltitle = str_replace(".", '', $ltitle);
						$lanswer = str_replace("\"", '', $row["answer"]);
						$lanswer = str_replace(".", '', $lanswer);
						echo "VARIABLE LABELS ".$field["id"]." \"$ltitle - $lanswer\".\n";
					}
				}
			}
			// Its a normal Question
		} else
		{
			$aquery = "SELECT `{$dbprefix}questions`.`question` FROM {$dbprefix}questions WHERE ((`{$dbprefix}questions`.`sid` =".$surveyid.") AND (`{$dbprefix}questions`.`qid` ='".$field["qid"]."'))";
			$aresult=db_execute_assoc($aquery) or die("Couldn't Find Question<br />$aquery<br />".$connect->ErrorMsg());
			if ($aresult->RecordCount() > 0)
			{
				$row = $aresult->FetchRow();
				$lquestion="";
				$lquestion = str_replace("\"", '', $row["question"]);
				$lquestion = str_replace(".", '', $lquestion);
				echo "VARIABLE LABELS ".$field["id"]." \"$lquestion\".\n";
			}
		}
	}
}

// Create our Value Labels!
echo "*Define Value labels.\n";
reset($fields);
foreach ($fields as $field){
	if ($field["qid"]!=0)
	{
		$query = "SELECT `{$dbprefix}answers`.`code`, `{$dbprefix}answers`.`answer` FROM {$dbprefix}answers WHERE (`{$dbprefix}answers`.`qid` = ".$field["qid"].")";
		$result=db_execute_assoc($query) or die("Couldn't Find Answers and Codes<br />$query<br />".$connect->ErrorMsg());
		$num_results = $result->RecordCount();
		if ($num_results > 0)
		{
			echo "VALUE LABELS ".$field["id"]."\n";
			# Build array that has to be returned
			for ($i=0; $i < $num_results; $i++) {
				$row = $result->FetchRow();
				if ($row['type'] != "T" && $row['type'] != "S" && $row['type'] != "U" && $row['type'] != "A" && $row['type'] != "B")
				{
					if ($i == ($num_results-1))
					{
						$lanswer = "";
						$lanswer = str_replace("\"", '', $row["answer"]);
						$lanswer = str_replace(".", '', $lanswer);
						echo $row["code"]." \"$lanswer\".\n"; // put .
					} else {
						$lanswer = "";
						$lanswer = str_replace("\"", '', $row["answer"]);
						$lanswer = str_replace(".", '', $lanswer);
						echo $row["code"]." \"$lanswer\"\n";
					}
				}
			}
		}
	}
}

?>
