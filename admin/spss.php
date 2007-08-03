<?php
/*
#############################################################
# >>> LimeSurvey       									#
#############################################################
# This set of scripts allows you to develop, publish and	#
# perform data-entry on surveys.							#
#############################################################
#															#
#	Copyright (C) 2007 by the developers of LimeSurvey     #
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
error_reporting(E_ALL ^ E_NOTICE); // No Notices!

require_once(dirname(__FILE__).'/../config.php');
include_once("login_check.php");

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (empty($surveyid)) {die("Cannot run this script directly");}

$sumquery5 = "SELECT b.* FROM {$dbprefix}surveys AS a INNER JOIN {$dbprefix}surveys_rights AS b ON a.sid = b.sid WHERE a.sid=$surveyid AND b.uid = ".$_SESSION['loginID']; //Getting rights for this survey and user
$sumresult5 = db_execute_assoc($sumquery5);
$sumrows5 = $sumresult5->FetchRow();

if ($sumrows5['export'] != "1")
{
	exit;
}
   /*
header("Content-Type: application/octetstream");
header("Content-Disposition: ".
(strpos($_SERVER["HTTP_USER_AGENT"],"MSIE 5.5")?""
:"attachment; ").
"filename=survey_".$surveyid.".sps");
											 */
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
	} else {
		if (isset($fieldname) && $fieldname != "")
		{
			# Determine the SPSS Variable Type
			$val_query="SELECT $fieldname FROM {$dbprefix}survey_$surveyid";
			$val_result=db_execute_assoc($val_query) or die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());
			while ($val_row = $val_result->FetchRow())
			{
				$val_size = 0;
				$teststring="";
				if ($val_row[$fieldname] == "Y")
				{
					$teststring = "1";
				}
				elseif ($val_row[$fieldname] == "F")
				{
					$teststring = "1";
				}
				elseif ($val_row[$fieldname] == "M")
				{
					$teststring = "2";
				}
				elseif ($val_row[$fieldname] == "N")
				{
					$teststring = "2";
				}
				elseif ($val_row[$fieldname] != "-oth-")
				{
					$teststring .= $val_row[$fieldname];
				}
				if ($val_size < strlen($val_row[$fieldname])) $val_size = strlen($val_row[$fieldname]);
			}
			if (is_numeric($teststring))
			{
				$fieldtype = "N".$val_size;
			} elseif ($val_size < 9 && !is_numeric($teststring))
			{
				//die(":".$teststring);
				$fieldtype = "A8";
			} elseif ($val_size >= 255)
			{
				$fieldtype = "A255";
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
	$tempArray=array($fieldno++ =>array("id"=>"d".$fieldno,"name"=>substr($fieldname, 0, 8),"qid"=>$qid, "code"=>$code, "type"=>"$fieldtype", "ftype"=>"$ftype","sql_name"=>$row["Field"],"size"=>$val_size));
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
			echo "'".date("d-m-Y H:i:s", mktime( $hour, $minute, $second, $month, $day, $year ) )."' ";
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
		} else if ($fields[$fieldno]["ftype"] == "M") 
		{
			if ($fields[$fieldno]["code"] == "other")
			{
				$strTmp=substr(strip_tags_full($row[$fieldno]), 0, 59);
				echo "'$strTmp' ";
			} else if ($row[$fieldno] == "Y")
			{
				echo "'1' ";
			} else {
				echo "'2' ";
			}
		} else if ($fields[$fieldno]["ftype"] == "P") 
		{
			if ($fields[$fieldno]["code"] == "other" || $fields[$fieldno]["code"] == "comment" || $fields[$fieldno]["code"] == "othercomment")
			{
				$strTmp=substr(strip_tags_full($row[$fieldno]), 0, 59);
				echo "'$strTmp' ";
			} else if ($row[$fieldno] == "Y")
			{
				echo "'1' ";
			} else {
				echo "'2' ";
			}
		} else {
			$strTmp=substr(strip_tags_full($row[$fieldno]), 0, 59);
			//if ($strTmp=='') $strTmp='.';
			echo "'$strTmp' ";
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
		echo "VARIABLE LABELS ".$field["id"]." '".substr(strip_tags_full($field["name"]), 0, 59)."'.\n";//minni"<br />";
	} elseif ($field["name"]=="id") {
		echo "VARIABLE LABELS ".$field["id"]." '".$clang->gT("Record ID")."'.\n";//minni"<br />";
	} elseif ($field["name"]=="submitda") {
		echo "VARIABLE LABELS ".$field["id"]." '".$clang->gT("Completion Date")."'.\n";//minni"<br />";
	} elseif ($field["name"]=="startlan") {
		echo "VARIABLE LABELS ".$field["id"]." '".$clang->gT("Start Language")."'.\n";//minni"<br />";
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
					echo "VARIABLE LABELS ".$field["id"]." '".substr(strip_tags_full($question_title), 0, 59)." - ".substr(strip_tags_full($row["answer"]), 0, 59)."'.\n";//minni"<br />";
				}
			}
			if (substr($field['sql_name'], -5)=='other') {
				echo "VARIABLE LABELS ".$field["id"]." '".
				substr(strip_tags_full($question_text), 0, 59)." - OTHER'.\n";
			}
			if (substr($field['sql_name'], -7)=='comment') {
				echo "VARIABLE LABELS ".$field["id"]." '".
				substr(strip_tags_full($question_text), 0, 59)." - COMMENT'.\n";
			}
		}else{
			$test=explode ("X", $field["name"]);
			$query = "SELECT question FROM {$dbprefix}questions 
			WHERE sid ='".$surveyid."' AND language='".$language."' 
			AND qid='".$field["qid"]."'";
			$result=db_execute_assoc($query) or die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());
			$row = $result->FetchRow();
			echo "VARIABLE LABELS ".$field["id"]." '".
			substr(strip_tags_full($row["question"]), 0, 59)."'.\n";
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
		if ($field['ftype'] != "T" && $field['ftype'] != "S" && $field['ftype'] != "U" && $field['ftype'] != "A" && $field['ftype'] != "B" && $field['ftype'] != "F" && $field['ftype'] != "M" && $field['ftype'] != "P")
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
					{ //substr($fieldname, 0, 8)
						echo $row["code"]." \"".strip_tags_full(substr($row["answer"],0,59))."\".\n"; // put .
					} else {
						echo $row["code"]." \"".strip_tags_full(substr($row["answer"],0,59))."\"\n";
					}
				}
			}
		}
		if ($field['ftype'] == "F")
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
						echo $row["code"]." \"".strip_tags_full(substr($row["title"],0,59))."\".\n"; // put . at end
					} else {
						echo $row["code"]." \"".strip_tags_full(substr($row["title"],0,59))."\"\n";

					}
				}
			}
		}
		if ($field['ftype'] == "M" && $field['code'] != "other" && $field['size'] > 0)
		{
			echo "VALUE LABELS ".$field["id"]."\n";
			echo "1 \"".$clang->gT("Yes")."\"\n";
			echo "2 \"".$clang->gT("No")."\".\n";
		}
		if ($field['ftype'] == "P" && $field['code'] != "other" && $field['code'] != "comment" && $field['code'] != "othercomment")
		{
			echo "VALUE LABELS ".$field["id"]."\n";
			echo "1 \"".$clang->gT("Yes")."\"\n";
			echo "2 \"".$clang->gT("No")."\".\n";
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
	}
}

function strip_tags_full($string) {
    $string=strip_tags($string);

    $string=str_replace("'", '?', $string);
    $string=str_replace('&nbsp;', ' ', $string);
    $string=str_replace('&agrave;', '?', $string);
    $string=str_replace('&egrave;', '?', $string);
    $string=str_replace('&igrave;', '?', $string);
    $string=str_replace('&ograve;', '?', $string);
    $string=str_replace('&ugrave;', '?', $string);
    $string=str_replace('&eacute;', '?', $string);
    $string=str_replace('&Agrave;', '?', $string);
    $string=str_replace('&Egrave;', '?', $string);
    $string=str_replace('&Igrave;', '?', $string);
    $string=str_replace('&Ograve;', '?', $string);
    $string=str_replace('&Ugrave;', '?', $string);
    $string=str_replace('&Eacute;', '?', $string);

    $string=str_replace('??', '?', $string);
    $string=str_replace('??', '?', $string);
    $string=str_replace('?| ', '?', $string);
    $string=str_replace('??', '?', $string);
    $string=str_replace('??', '?', $string);
    $string=str_replace('??', '?', $string);
    $string=str_replace('??', '?', $string);
    $string=str_replace('?~H', '?', $string);

    $string=str_replace(chr(13), "", $string);
    $string=str_replace(chr(10), " ", $string);

    $string=trim($string);
    if ($string == '-oth-') $string='';

    return $string;
}


?>
