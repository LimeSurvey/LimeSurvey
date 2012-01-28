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
* $Id$
*/

$length_data = "255"; // Set the max text length of Text Data
$length_varlabel = "255"; // Set the max text length of Variable Labels
$length_vallabel = "255"; // Set the max text length of Value Labels

include_once("login_check.php");
error_reporting(E_ALL ^ E_NOTICE); // No Notices!

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

mb_http_output("UTF-8");

header("Content-Type: application/download; charset=utf-8");
header("Content-Disposition: attachment; filename=survey_".$surveyid.".sps");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
Header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache");

// Get Base Language:

$language = GetBaseLanguageFromSurveyID($surveyid);
$clang = new limesurvey_lang($language);

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
$query="SELECT sid, attribute1, attribute2, private, language FROM {$dbprefix}surveys WHERE sid=$surveyid";
$result=db_execute_assoc($query) or die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());
$num_results = $result->RecordCount();
$num_fields = $num_results;
# Build array that has to be returned
for ($i=0; $i < $num_results; $i++) {
	$row = $result->FetchRow();
	if ($row["attribute1"]) {$attr1_name = $row["attribute1"];} else {$attr1_name = $clang->gT("Attribute 1");}
	if ($row["attribute2"]) {$attr2_name = $row["attribute2"];} else {$attr2_name = $clang->gT("Attribute 2");}
	$surveyprivate=$row['private'];
	$language=$row['language'];
}

$fieldno=0;

if (isset($tokensexist) && $tokensexist == 1 && $surveyprivate == "N") {
	$query="SHOW COLUMNS FROM ".db_table_name("tokens_$surveyid");
	$result=db_execute_num($query) or die("Couldn't count fields in tokens<br />$query<br />".$connect->ErrorMsg());
	while ($row=$result->FetchRow()) {
		$token_fields[]=$row[0];
	}
	if (in_array("firstname", $token_fields)) {
		$fields[$fieldno++]=array("id"=>"fname","name"=>$clang->gT("First Name"),"code"=>"", "qid"=>0,"type"=>"A40" );
	}
	if (in_array("lastname", $token_fields)) {
		$fields[$fieldno++]=array("id"=>"lname","name"=> $clang->gT("Last Name"),"code"=>"", "qid"=>0,"type"=>"A40" );
	}
	if (in_array("email", $token_fields)) {
		$fields[$fieldno++]=array("id"=>"email","name"=> $clang->gT("Email"),"code"=>"", "qid"=>0,"type"=>"A100");
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

//$fieldno=1;
$fieldno=0;
$tempArray = array();
$query="SHOW COLUMNS FROM ".db_table_name("survey_$surveyid");
$result=db_execute_assoc($query) or die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());
$num_results = $result->RecordCount();
$num_fields = $num_results;
# Build array that has to be returned
for ($i=0; $i < $num_results; $i++) {
	$row = $result->FetchRow();
	#Conditions for SPSS fields:
	# - Length may not be longer than 8 characters
	# - Name may not begin with a digit
	$fieldname = $row["Field"];
	//echo $fieldname." - ";
	#Rename 'datestamp' to stamp
	if ($fieldname =="datestamp") {
		$fieldname = "stamp";
	}

	
	#Determine field type
	if ($fieldname=="stamp" || $fieldname=="submitdate" || $fieldname=="datestamp"){
		$fieldtype = "DATETIME20.0";
	} elseif ($fieldname=="startlanguage") {
		$fieldtype = "A15";
	} elseif ($fieldname=="token") {
		$fieldtype = "N16";
	} else {
		if (isset($fieldname) && $fieldname != "")
		{
			# Determine the SPSS Variable Type
			$val_query="SELECT $fieldname FROM {$dbprefix}survey_$surveyid";
			$val_result=db_execute_assoc($val_query) or die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());
			$val_size = 0;
			$teststring="";
			while ($val_row = $val_result->FetchRow())
			{
				if ($val_row[$fieldname] == "Y")
				{
					$teststring .= "1";
				}
				elseif ($val_row[$fieldname] == "F")
				{
					$teststring .= "1";
				}
				elseif ($val_row[$fieldname] == "M")
				{
					$teststring .= "2";
				}
				elseif ($val_row[$fieldname] == "N")
				{
					$teststring .= "2";
				}
				elseif ($val_row[$fieldname] == "I")
				{
					$teststring .= "1";
				}
				elseif ($val_row[$fieldname] == "S")
				{
					$teststring .= "2";
				}
				elseif ($val_row[$fieldname] == "D")
				{
					$teststring .= "3";
				}
				elseif ($val_row[$fieldname] == "U")
				{
					$teststring .= "3";
				}
				else
				{
					$teststring .= $val_row[$fieldname];
				}
				if ($val_size < strlen($val_row[$fieldname])) $val_size = strlen($val_row[$fieldname]);
			}
			
			$teststring = strip_tags_full($teststring);
			$teststring = mb_ereg_replace(" ", '', $teststring);

			if (is_numeric($teststring))
			{
				$fieldtype = "N".$val_size;
			} elseif ($teststring == "")
			{
				$fieldtype = "N1";
			} elseif ($val_size < 9 && !is_numeric($teststring))
			{
				$fieldtype = "A8";
			} elseif ($val_size >= $length_data)
			{
				$fieldtype = "A".$length_data;
			}else
			{
				$fieldtype = "A".$val_size;
			}
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
	}
	$tempArray=array($fieldno++ =>array("id"=>"d".$fieldno,"name"=>mb_substr($fieldname, 0, 8),"qid"=>$qid, "code"=>$code, "type"=>"$fieldtype", "ftype"=>"$ftype","sql_name"=>$row["Field"],"size"=>$val_size));
	$fields = $fields + $tempArray;
}

reset($fields);


/*
FILE TYPE NESTED RECORD=1(A).
- RECORD TYPE 'Y'.
- DATA LIST / Year 3-6.

- RECORD TYPE 'R'.
- DATA LIST / Region 3-13 (A).

- RECORD TYPE 'P'.
- DATA LIST / SalesRep 3-13 (A) Sales 20-23.
END FILE TYPE.

BEGIN DATA
Y 2002
R Chicago
P Jones            900
P Gregory          400
R Baton Rouge
P Rodriguez        300
P Smith            333
P Grau             100
END DATA.
EXECUTE.

*/
echo "NEW FILE.\n";
echo "FILE TYPE NESTED RECORD=1(A).\n";

$i=0;
foreach ($fields as $field){
	if ($i % 20 == 0) echo "- RECORD TYPE '".chr(65+intval($i/20))."'.\n- DATA LIST LIST / i".intval($i/20)."(A1) ";
	echo $field["id"];
	echo "(".$field["type"].") ";
	$i++;
	//if ($i % 25 == 0) echo "\n   /";
	if ($i % 20 == 0) echo ".\n\n";
}

if ($i % 20 != 0) echo ".\n";
echo "END FILE TYPE.\n\n";
//echo ".";
//minni echo "<br />";
//echo "\n\n\n";

//echo "*Begin data\n";
echo "BEGIN DATA\n";//minni"<br />";
if (isset($tokensexist) && $tokensexist == 1 && $surveyprivate == "N") {
	$query="SELECT {$dbprefix}tokens_$surveyid.firstname   ,
	       {$dbprefix}tokens_$surveyid.lastname    ,
	       {$dbprefix}tokens_$surveyid.email";
	if (in_array("attribute_1", $token_fields)) {
		$query .= ",\n		{$dbprefix}tokens_$surveyid.attribute_1";
	}
	if (in_array("attribute_2", $token_fields)) {
		$query .= ",\n		{$dbprefix}tokens_$surveyid.attribute_2";
	}
	$query .= ",\n	       {$dbprefix}survey_$surveyid.*
	FROM {$dbprefix}survey_$surveyid
	LEFT JOIN {$dbprefix}tokens_$surveyid ON {$dbprefix}survey_$surveyid.token = {$dbprefix}tokens_$surveyid.token";
	if (incompleteAnsFilterstate() === true)
	{
		$query .= " WHERE {$dbprefix}survey_$surveyid.submitdate is not null ";
	}
} else {
	$query = "SELECT *
	FROM {$dbprefix}survey_$surveyid";
	if (incompleteAnsFilterstate() === true)
	{
		$query .= " WHERE submitdate is not null ";
	}
}


$result=db_execute_num($query) or die("Couldn't get results<br />$query<br />".$connect->ErrorMsg());
$num_results = $result->RecordCount();
$num_fields = $result->FieldCount();
for ($i=0; $i < $num_results; $i++) {

	$row = $result->FetchRow();
	$fieldno = 0;
	while ($fieldno < $num_fields)
	{
			//echo " field: ".$fields[$fieldno]["id"]." id : ".$fields[$fieldno]["qid"]." val:".$row[$fieldno]."-type: ".$fields[$fieldno]["type"]." |<br> ";
		if ($fieldno % 20 == 0) echo chr(65+intval($fieldno/20))." ";
		//if ($i==0) { echo "Field: $fieldno - Dati: ";var_dump($fields[$fieldno]);echo "\n"; }
		if ($fields[$fieldno]["type"]=="DATETIME20.0"){
			#convert mysql  datestamp (yyyy-mm-dd hh:mm:ss) to SPSS datetime (dd-mmm-yyyy hh:mm:ss) format
			list( $year, $month, $day, $hour, $minute, $second ) = split( '([^0-9])', $row[$fieldno] );
			if ($year != "" && (int)$year >= 1970) 
			{
				echo "'".date("d-m-Y H:i:s", mktime( $hour, $minute, $second, $month, $day, $year ) )."' ";
			} else 
			{
				echo "''";
			}
		} else if ($fields[$fieldno]["ftype"] == "Y") 
		{
			if ($row[$fieldno] == "Y")
			{
				echo "'1' ";
			} else if ($row[$fieldno] == "N"){
				echo "'2' ";
			} else {
				echo "'0' ";
			}
		} else if ($fields[$fieldno]["ftype"] == "G") 
		{
			if ($row[$fieldno] == "F")
			{
				echo "'1' ";
			} else if ($row[$fieldno] == "M"){
				echo "'2' ";
			} else {
				echo "'0' ";
			}
		} else if ($fields[$fieldno]["ftype"] == "C") 
		{
			if ($row[$fieldno] == "Y")
			{
				echo "'1' ";
			} else if ($row[$fieldno] == "N"){
				echo "'2' ";
			} else if ($row[$fieldno] == "U"){
				echo "'3' ";
			} else {
				echo "'0' ";
			}
		} else if ($fields[$fieldno]["ftype"] == "E") 
		{
			if ($row[$fieldno] == "I")
			{
				echo "'1' ";
			} else if ($row[$fieldno] == "S"){
				echo "'2' ";
			} else if ($row[$fieldno] == "D"){
				echo "'3' ";
			} else {
				echo "'0' ";
			}
		} else if ($fields[$fieldno]["ftype"] == "M") 
		{
			if ($fields[$fieldno]["code"] == "other")
			{
				$strTmp=mb_substr(strip_tags_full($row[$fieldno]), 0, $length_data);
				echo "'$strTmp' ";
			} else if ($row[$fieldno] == "Y")
			{
				echo "'1' ";
			} else
			{
			   echo "'0' ";
			}
		} else if ($fields[$fieldno]["ftype"] == "P") 
		{
			if ($fields[$fieldno]["code"] == "other" || $fields[$fieldno]["code"] == "comment" || $fields[$fieldno]["code"] == "othercomment")
			{
				$strTmp=mb_substr(strip_tags_full($row[$fieldno]), 0, $length_data);
				echo "'$strTmp' ";
			} else if ($row[$fieldno] == "Y")
			{
				echo "'1' ";
			} else
			{
			   echo "'0' ";
			}
		} else {
			$strTmp=mb_substr(strip_tags_full($row[$fieldno]), 0, $length_data);
			//if ($strTmp=='') $strTmp='.';
			if (mb_ereg_replace(" ", '', $strTmp) == ""){
				echo "'0'";
			}
			else {
				echo "'$strTmp' ";
			}
		}
		$fieldno++;
		if ($fieldno % 20 == 0) echo "\n";
	}
	if ($fieldno % 20 != 0) echo "\n";
	//echo "\n";//minni"<br />";
	#Conditions for SPSS fields:
	# - Length may not be longer than 8 charac
}
echo "END DATA.\nEXECUTE.\n\n";//minni<br />";

echo "*Define Variable Properties.\n";//minni"<br />";
foreach ($fields as $field){
	if (	$field["id"] == "fname" OR
	$field["id"]=="lname" OR
	$field["id"]=="email" OR
	$field["id"]=="attr1" OR
	$field["id"]=="attr2"){
		echo "VARIABLE LABELS ".$field["id"]." '".mb_substr(strip_tags_full($field["name"]), 0, $length_varlabel)."'.\n";//minni"<br />";
	} elseif ($field["name"]=="id") {
		echo "VARIABLE LABELS ".$field["id"]." '".$clang->gT("Record ID")."'.\n";//minni"<br />";
	} elseif ($field["name"]=="submitda") {
		echo "VARIABLE LABELS ".$field["id"]." '".$clang->gT("Completion Date")."'.\n";//minni"<br />";
	} elseif ($field["name"]=="startlan") {
		echo "VARIABLE LABELS ".$field["id"]." '".$clang->gT("Start Language")."'.\n";//minni"<br />";
	} elseif ($field["name"]=="token") {
		echo "VARIABLE LABELS ".$field["id"]." '".$clang->gT("Token")."'.\n";
	}else{
		#If a split question
		if ($field["code"] != ""){
			#Lookup the question

			$query = "SELECT question, title 
			FROM {$dbprefix}questions WHERE sid='".$surveyid."' AND language='".$language."' 
			AND qid='".$field["qid"]."'";
			
			$result=db_execute_assoc($query) or die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());
			$num_results = $result->RecordCount();
			$num_fields = $num_results;
			if ($num_results >0){
				# Build array that has to be returned
				for ($i=0; $i < $num_results; $i++) {
					$row = $result->FetchRow();
					$question_text = $row["question"];
					$question_title = $row["title"];
				}
			}
			#Lookup the answer
			$query = "SELECT answer FROM {$dbprefix}answers WHERE 
			qid='".$field["qid"]."' and language='".$language."' AND code ='".$field["code"]."'";
			$result=db_execute_assoc($query) or die("Couldn't lookup answer<br />$query<br />".$connect->ErrorMsg());
			$num_results = $result->RecordCount();
			$num_fields = $num_results;
			if ($num_results >0){
				# Build array that has to be returned
				for ($i=0; $i < $num_results; $i++) {
					$row = $result->FetchRow();
					echo "VARIABLE LABELS ".$field["id"]." '".mb_substr(strip_tags_full($question_title), 0, $length_varlabel)." - ".mb_substr(strip_tags_full($row["answer"]), 0, $length_varlabel)."'.\n";//minni"<br />";
				}
			}
			if (mb_substr($field['sql_name'], -5)=='other') {
				echo "VARIABLE LABELS ".$field["id"]." '".
				mb_substr(strip_tags_full($question_text), 0, $length_varlabel-8)." - OTHER'.\n";
			}
			if (mb_substr($field['sql_name'], -7)=='comment') {
				echo "VARIABLE LABELS ".$field["id"]." '".
				mb_substr(strip_tags_full($question_text), 0, $length_varlabel-10)." - COMMENT'.\n";
			}
		}else{
			$test=explode ("X", $field["name"]);
			$query = "SELECT question FROM {$dbprefix}questions 
			WHERE sid ='".$surveyid."' AND language='".$language."' 
			AND qid='".$field["qid"]."'";
			$result=db_execute_assoc($query) or die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());
			$row = $result->FetchRow();
			echo "VARIABLE LABELS ".$field["id"]." '".
			mb_substr(strip_tags_full($row["question"]), 0, $length_varlabel)."'.\n";
		}
	}
}

// Create our Value Labels!
echo "*Define Value labels.\n";
reset($fields);
foreach ($fields as $field)
{
	if ($field["qid"]!=0)
	{
		if ($field['ftype'] != "T" && $field['ftype'] != "S" && $field['ftype'] != "Q" && $field['ftype'] != "U" && $field['ftype'] != "A" && $field['ftype'] != "B" && $field['ftype'] != "F" && $field['ftype'] != "M" && $field['ftype'] != "P" && $field['ftype'] != "C" && $field['ftype'] != "E")
		{
			$query = "SELECT {$dbprefix}answers.code, {$dbprefix}answers.answer, 
			{$dbprefix}questions.type FROM {$dbprefix}answers, {$dbprefix}questions WHERE 
			{$dbprefix}answers.qid = '".$field["qid"]."' and {$dbprefix}questions.language='".$language."' and  {$dbprefix}answers.language='".$language."'
			and {$dbprefix}questions.qid='".$field["qid"]."'";
			$result=db_execute_assoc($query) or die("Couldn't lookup value labels<br />$query<br />".$connect->ErrorMsg());
			$num_results = $result->RecordCount();
			if ($num_results > 0)
			{
				$displayvaluelabel = 0;
				# Build array that has to be returned
				for ($i=0; $i < $num_results; $i++)
				{
					$row = $result->FetchRow();

					if ($displayvaluelabel == 0) echo "VALUE LABELS ".$field["id"]."\n";
					if ($displayvaluelabel == 0) $displayvaluelabel = 1;
					if ($i == ($num_results-1))
					{
						echo $row["code"]." \"".strip_tags_full(mb_substr($row["answer"],0,$length_vallabel))."\".\n"; // put .
					} else {
						echo $row["code"]." \"".strip_tags_full(mb_substr($row["answer"],0,$length_vallabel))."\"\n";
					}
				}
			}
		}
		if ($field['ftype'] == "F" || $field['ftype'] == "W" || $field['ftype'] == "Z")
		{
			$displayvaluelabel = 0;
			$query = "SELECT {$dbprefix}questions.lid, {$dbprefix}labels.code, {$dbprefix}labels.title from 
			{$dbprefix}questions, {$dbprefix}labels WHERE {$dbprefix}labels.language='".$language."' and
			{$dbprefix}questions.language='".$language."' and 
			{$dbprefix}questions.qid ='".$field["qid"]."' and {$dbprefix}questions.lid={$dbprefix}labels.lid";
			$result=db_execute_assoc($query) or die("Couldn't get labels<br />$query<br />".$connect->ErrorMsg());
			$num_results = $result->RecordCount();
			if ($num_results > 0)
			{
				for ($i=0; $i < $num_results; $i++)
				{
					$row = $result->FetchRow();
					if ($displayvaluelabel == 0) echo "VALUE LABELS ".$field["id"]."\n";
					if ($displayvaluelabel == 0) $displayvaluelabel = 1;
					if ($i == ($num_results-1))
					{
						echo $row["code"]." \"".strip_tags_full(mb_substr($row["title"],0,$length_vallabel))."\".\n"; // put . at end
					} else {
						echo $row["code"]." \"".strip_tags_full(mb_substr($row["title"],0,$length_vallabel))."\"\n";

					}
				}
			}
		}
		if ($field['ftype'] == "M" && $field['code'] != "other" && $field['size'] > 0)
		{
			echo "VALUE LABELS ".$field["id"]."\n";
			echo "1 \"".$clang->gT("Yes")."\"\n";
			echo "0 \"".$clang->gT("Not Selected")."\".\n";
		}
		if ($field['ftype'] == "P" && $field['code'] != "other" && $field['code'] != "comment" && $field['code'] != "othercomment")
		{
			echo "VALUE LABELS ".$field["id"]."\n";
			echo "1 \"".$clang->gT("Yes")."\"\n";
			echo "0 \"".$clang->gT("Not Selected")."\".\n";
		}
		if ($field['ftype'] == "G" && $field['size'] > 0)
		{
			echo "VALUE LABELS ".$field["id"]."\n";
			echo "1 \"".$clang->gT("Female")."\"\n";
			echo "2 \"".$clang->gT("Male")."\".\n";
		}
		if ($field['ftype'] == "Y" && $field['size'] > 0)
		{
			echo "VALUE LABELS ".$field["id"]."\n";
			echo "1 \"".$clang->gT("Yes")."\"\n";
			echo "2 \"".$clang->gT("No")."\".\n";
		}
		if ($field['ftype'] == "C" && $field['size'] > 0)
		{
			echo "VALUE LABELS ".$field["id"]."\n";
			echo "1 \"".$clang->gT("Yes")."\"\n";
			echo "2 \"".$clang->gT("No")."\".\n";
			echo "3 \"".$clang->gT("Uncertain")."\".\n";
		}
		if ($field['ftype'] == "E" && $field['size'] > 0)
		{
			echo "VALUE LABELS ".$field["id"]."\n";
			echo "1 \"".$clang->gT("Increase")."\"\n";
			echo "2 \"".$clang->gT("Same")."\".\n";
			echo "3 \"".$clang->gT("Decrease")."\".\n";
		}
	}
}

function strip_tags_full($string) {
    mb_regex_encoding('utf-8');
    $pattern = array('&nbsp;', '&agrave;', '&nbsp;', '&agrave;', '&egrave;', '&igrave;', '&ograve;', '&ugrave;', '&eacute;',
    				 '&Agrave;', '&Egrave;', '&Igrave;', '&Ograve;', '&Ugrave;', '&Eacute;', '\r', '\n', '-oth-');
    for ($i=0; $i<sizeof($pattern); $i++) {
        $string = mb_ereg_replace($pattern[$i], '', $string);
    }
    
    $string = mb_ereg_replace("'", "\'", $string);

    return $string;
}

exit;
?>
