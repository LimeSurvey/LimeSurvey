<?php
require_once("config.php");
if (!isset($imagefiles)) {$imagefiles="./images";}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($style)) {$style=returnglobal('style');}
if (!isset($answers)) {$answers=returnglobal('answers');}
if (!isset($type)) {$type=returnglobal('type');}

if (empty($surveyid)) {die("Cannot run this script directly");}
#Get all legitimate question ids

header("Content-Type: application/octetstream");
header("Content-Disposition: ".
       (strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 5.5")?""
                                           :"attachment; ").
       "filename=survey.sav");


sendcacheheaders();
$query = "SELECT DISTINCT qid FROM {$dbprefix}questions WHERE sid=$surveyid"; //GET LIST OF LEGIT QIDs FOR TESTING LATER
$result=mysql_query($query) or die("Couldn't count fields<br />$query<br />".mysql_error());
$num_results = mysql_num_rows($result);
# Build array that has to be returned
$fieldmap=createFieldMap($surveyid);
#See if tokens are being used
$tresult = @mysql_list_tables($databasename) or die ("Error getting table list<br />".mysql_error());
while($tbl = @mysql_tablename($tresult, $i++))
	{
	if ($tbl == "{$dbprefix}tokens_$surveyid") {$tokensexist = 1;}
	}

#Lookup the names of the attributes
$query="SELECT sid, attribute1, attribute2, private FROM {$dbprefix}surveys WHERE sid=$surveyid";
$result=mysql_query($query) or die("Couldn't count fields<br />$query<br />".mysql_error());
$num_results = mysql_num_rows($result);
$num_fields = $num_results;
# Build array that has to be returned
for ($i=0; $i < $num_results; $i++) {
        $row = mysql_fetch_array($result);
	if ($row["attribute1"]) {$attr1_name = $row["attribute1"];} else {$attr1_name=_TL_ATTR1;}
	if ($row["attribute2"]) {$attr2_name = $row["attribute2"];} else {$attr2_name=_TL_ATTR2;}
	$surveyprivate=$row['private'];
}

$fieldno=0;

if (isset($tokensexist) && $tokensexist == 1 && $surveyprivate == "N") {
	$query="SHOW COLUMNS FROM {$dbprefix}tokens_$surveyid";
	$result=mysql_query($query) or die("Couldn't count fields in tokens<br />$query<br />".mysql_error());
	while ($row=mysql_fetch_row($result)) {
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
//	$fields=array(
//		$fieldno++ =>array("id"=>"fname","name"=>_TL_FIRST,"code"=>"", "qid"=>0,"type"=>"A40" ) , 
//		$fieldno++ =>array("id"=>"lname","name"=>_TL_LAST,"code"=>"", "qid"=>0,"type"=>"A40" ) , 
//		$fieldno++ =>array("id"=>"email","name"=>_TL_EMAIL,"code"=>"", "qid"=>0,"type"=>"A100") , 
//		$fieldno++ =>array("id"=>"attr1","name"=>$attr1_name,"code"=>"", "qid"=>0,"type"=>"A100") , 
//		$fieldno++ =>array("id"=>"attr2","name"=>$attr2_name,"code"=>"", "qid"=>0,"type"=>"A100"));
} else {
	$fields=array();
}

$query="SHOW COLUMNS FROM {$dbprefix}survey_$surveyid";
$result=mysql_query($query) or die("Couldn't count fields<br />$query<br />".mysql_error());
$num_results = mysql_num_rows($result);
$num_fields = $num_results;
# Build array that has to be returned
for ($i=0; $i < $num_results; $i++) {
        $row = mysql_fetch_array($result);
        #Conditions for SPSS fields:
        # - Length may not be longer than 8 characters
        # - Name may not begin with a digit
        $fieldname = $row["Field"];

        #Rename 'datestamp' to stamp
        if ($fieldname =="datestamp") {
                $fieldname = "stamp";
        }

	#Determine field type
	if ($fieldname=="stamp"){
		$fieldtype = "DATETIME20.0";
	} else {
		$fieldtype = "A10";
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
	}
	$tempArray=array($fieldno++ =>array("id"=>"d".$fieldno,"name"=>substr($fieldname, 0, 8),"qid"=>$qid, "code"=>$code, "type"=>"$fieldtype"));
	$fields = $fields + $tempArray;
}

echo "DATA LIST LIST /";

foreach ($fields as $field){
        echo $field["id"];
        echo "(".$field["type"].") ";
}

echo ".";
echo "<br>";
echo "\n";

#echo "*Begin data\n";
echo "BEGIN DATA<br>";

if (isset($tokensexist) && $tokensexist == 1 && $surveyprivate == "N") {
$query="SELECT `{$dbprefix}tokens_$surveyid`.`firstname`   ,
	       `{$dbprefix}tokens_$surveyid`.`lastname`    ,
	       `{$dbprefix}tokens_$surveyid`.`email`";
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


$result=mysql_query($query) or die("Couldn't get results<br />$query<br />".mysql_error());
$num_results = mysql_num_rows($result);
for ($i=0; $i < $num_results; $i++) {
        $row = mysql_fetch_array($result);
	$fieldno = 0;
	while ($fieldno < $num_fields){
		if ($fields[$fieldno]["id"]=="stamp"){
			#convert mysql  datestamp (yyyy-mm-dd hh:mm:ss) to SPSS datetime (dd-mmm-yyyy hh:mm:ss) format
			list( $year, $month, $day, $hour, $minute, $second ) = split( '([^0-9])', $row[$fieldno] );
			echo "'".date("d-m-Y H:i:s", mktime( $hour, $minute, $second, $month, $day, $year ) )."' ";
		}else {
			echo "'".$row[$fieldno]."' ";
		}
		$fieldno++;
	}
	echo "<br>";
        #Conditions for SPSS fields:
        # - Length may not be longer than 8 charac
}
echo "END DATA.<br>";

echo "*Define Variable Properties.<br>";
foreach ($fields as $field){
	if ($field["id"] == "fname" OR $field["id"]=="lname" OR $field["id"]=="email" OR $field["id"]=="attr1" OR $field["id"]=="attr2"){
	        echo "VARIABLE LABELS ".$field["id"]." '".$field["name"]."'.<br>";
	}else{
		#If a split question
		if ($field["code"] != ""){
			#Lookup the question

			$query = "SELECT `{$dbprefix}questions`.`question` FROM {$dbprefix}questions WHERE ((`{$dbprefix}questions`.`sid` =".$surveyid.") AND (`{$dbprefix}questions`.`qid` =".$field["qid"]."))";
			#echo $query;
			$result=mysql_query($query) or die("Couldn't count fields<br />$query<br />".mysql_error());
			$num_results = mysql_num_rows($result);
			$num_fields = $num_results;
			if ($num_results >0){
				# Build array that has to be returned
				for ($i=0; $i < $num_results; $i++) {
					$row = mysql_fetch_array($result);
					$question_text = $row["question"];
				}
			}
			#Lookup the answer
			$query = "SELECT `{$dbprefix}answers`.`answer` FROM {$dbprefix}answers WHERE ((`{$dbprefix}answers`.`qid` =".$field["qid"].") AND (`{$dbprefix}answers`.`code` ='".$field["code"]."'))";
			$result=mysql_query($query) or die("Couldn't count fields<br />$query<br />".mysql_error());
			$num_results = mysql_num_rows($result);
			$num_fields = $num_results;
			if ($num_results >0){
				# Build array that has to be returned
				for ($i=0; $i < $num_results; $i++) {
					$row = mysql_fetch_array($result);
					echo "VARIABLE LABELS ".$field["id"]." '".$question_text." - ".$row["answer"]."'.<br>";
				}
			}


		#If a 'normal'
		}else{
				####
			#Split fieldname by "X"
			#$test[0] --> contains 'survey id' --> surveys-sid
			#$test[1] --> contains 'group is'  --> groups-gid
			#$test[2] --> contains a combination 'qid' and 'code' in 'answers' table
			$test=explode ("X", $field["name"]);
		}
	}
}

echo "*Define Value labels.<br>";

foreach ($fields as $field){
	if ($field["qid"]!=0){
		$query = "SELECT `{$dbprefix}answers`.`code`, `{$dbprefix}answers`.`answer` FROM {$dbprefix}answers WHERE (`{$dbprefix}answers`.`qid` = ".$field["qid"].")";
		$result=mysql_query($query) or die("Couldn't count fields<br />$query<br />".mysql_error());
		$num_results = mysql_num_rows($result);
		$num_fields = $num_results;
		if ($num_results >0){
			echo "VALUE LABELS ".$field["id"]."<br>";
			# Build array that has to be returned
			for ($i=0; $i < $num_results; $i++) {
			        $row = mysql_fetch_array($result);
				echo $row["code"]." '".$row["answer"]."'<br>";
			}
		}
	}
}
?>
