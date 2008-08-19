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

// Security Checked: POST, GET, SESSION, REQUEST, returnglobal, DB

/*
* The SPSS DATA LIST / BEGIN DATA parser is rather simple minded, the number after the type
* specifier identifies the field width (maximum number of characters to scan)
* It will stop short of that number of characters, honouring quote delimited
* space separated strings, however if the width is too small the remaining data in the current
* line becomes part of the next column.  Since we want to restrict this script to ONE scan of 
* the data (scan & output at same time), the information needed to construct the
* DATA LIST is held in the $fields array, while the actual data is written to a
* to a temporary location, updating length (size) values in the $fields array as
* the tmp file is generated (uses @fwrite's return value rather than strlen).
* Final output renders $fields to a DATA LIST, and then stitches in the tmp file data.
*
* Optimization opportunities remain in the VALUE LABELS section, which runs a query / column
*/

$length_data = '255'; // Set the max text length of Text Data
$length_varlabel = '255'; // Set the max text length of Variable Labels
$length_vallabel = '255'; // Set the max text length of Value Labels
$headerComment = '';
$tempFile = '';

include_once('login_check.php');

$typeMap = array(
'5'=>Array('name'=>'5 Point Choice','size'=>1,'SPSStype'=>'N'),
'B'=>Array('name'=>'Array (10 Point Choice)','size'=>1,'SPSStype'=>'N'),
'A'=>Array('name'=>'Array (5 Point Choice)','size'=>1,'SPSStype'=>'N'),
'F'=>Array('name'=>'Array (Flexible Labels)','size'=>1,'SPSStype'=>'N'),
'1'=>Array('name'=>'Array (Flexible Labels) Dual Scale','size'=>1,'SPSStype'=>'N'),
'H'=>Array('name'=>'Array (Flexible Labels) by Column','size'=>1,'SPSStype'=>'N'),
'E'=>Array('name'=>'Array (Increase, Same, Decrease)','size'=>1,'SPSStype'=>'N'),
'C'=>Array('name'=>'Array (Yes/No/Uncertain)','size'=>1,'SPSStype'=>'N'),
'X'=>Array('name'=>'Boilerplate Question','size'=>1,'SPSStype'=>'A'),
'D'=>Array('name'=>'Date','size'=>null,'SPSStype'=>'DATETIME20.0'),
'G'=>Array('name'=>'Gender','size'=>1,'SPSStype'=>'N'),
'U'=>Array('name'=>'Huge Free Text','size'=>1,'SPSStype'=>'A'),
'I'=>Array('name'=>'Language Switch','size'=>1,'SPSStype'=>'A'),
'!'=>Array('name'=>'List (Dropdown)','size'=>1,'SPSStype'=>'N'),
'W'=>Array('name'=>'List (Flexible Labels) (Dropdown)','size'=>1,'SPSStype'=>'N'),
'Z'=>Array('name'=>'List (Flexible Labels) (Radio)','size'=>1,'SPSStype'=>'N'),
'L'=>Array('name'=>'List (Radio)','size'=>1,'SPSStype'=>'N'),
'O'=>Array('name'=>'List With Comment','size'=>1,'SPSStype'=>'N'),
'T'=>Array('name'=>'Long free text','size'=>1,'SPSStype'=>'A'),
'K'=>Array('name'=>'Multiple Numerical Input','size'=>1,'SPSStype'=>'N'),
'M'=>Array('name'=>'Multiple Options','size'=>1,'SPSStype'=>'N'),
'P'=>Array('name'=>'Multiple Options With Comments','size'=>1,'SPSStype'=>'N'),
'Q'=>Array('name'=>'Multiple Short Text','size'=>1,'SPSStype'=>'N'),
'N'=>Array('name'=>'Numerical Input','size'=>3,'SPSStype'=>'N'),
'R'=>Array('name'=>'Ranking','size'=>1,'SPSStype'=>'N'),
'S'=>Array('name'=>'Short free text','size'=>1,'SPSStype'=>'N'),
'Y'=>Array('name'=>'Yes/No','size'=>1,'SPSStype'=>'N'),
);

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

function renderDataList($fieldArr){
	global $headerComment;
	echo $headerComment;
	echo "NEW FILE.\n";
	echo "FILE TYPE NESTED RECORD=1(A).\n";

	$i=0;
	foreach ($fieldArr as $field){
		if ($i % 20 == 0) echo "- RECORD TYPE '".chr(65+intval($i/20))."'.\n- DATA LIST LIST / i".intval($i/20)."(A1)";
		if($field['SPSStype'] == 'DATETIME20.0') $field['size']=null;
		echo " {$field['id']}({$field['SPSStype']}{$field['size']})";
		$i++;
		//if ($i % 25 == 0) echo "\n   /";
		if ($i % 20 == 0) echo ".\n\n";
	}

	if ($i % 20 != 0) echo ".\n";
	echo "END FILE TYPE.\n\n";
}

/**
 * Try to create a temp file using PHPs builtin (since v4)
 * this can cause a problem in safe_mode, which requires the owner of the temp directory
 * be the same as the owner of the script.  So, we've got the $tempdir defined in config-defaults.php
 * try using that instead.
 *
 * This method generates output for the user, it should be adjusted to use the il8n framework.
 */
function mkTmpFile(){
	global $headerComment, $tempFile, $surveyid, $tempdir;
	$fp = @tmpfile();
	if(!$fp){
		$headerComment .= "* Failed to use builtin tmpfile command (trying $tempdir)?\n";
		$tempFile = @tempnam($tempdir, "spss_");
		$fp = @fopen($tempFile, "w+");
		if(!$fp){
			$headerComment .= "* Failed to create temp file in \$tempdir=$tempdir (defined in config.php / config-defaults.php)\n";
			$headerComment .= "* Please ensure that $tempdir is owned by the same user who owns this script\n";
			$fp = null;
		}
	} else $tempFile = "";
	return $fp;
}

function closeTmpFile($fp){
	global $tempFile;
	fclose($fp);
	//If it's blank we used the builtin tmpfile() method
	if($tempFile !== "")
		unlink($tempFile);
}



header("Content-Type: application/download; charset=utf-8");
header("Content-Disposition: attachment; filename=survey_".$surveyid."_SPSS_syntax_file.sps");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache");

// Get Base Language:

$language = GetBaseLanguageFromSurveyID($surveyid);
$clang = new limesurvey_lang($language);

sendcacheheaders();

//These results are over written just a few lines down, they can't be doing anything
//$query = "SELECT DISTINCT qid FROM ".db_table_name("questions")." WHERE sid=$surveyid"; //GET LIST OF LEGIT QIDs FOR TESTING LATER
//$result=$connect->Execute($query) or safe_die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());
//$num_results = $result->RecordCount();

# Build array that has to be returned
$fieldmap=createFieldMap($surveyid);

//echo "FieldMap:";
//print_r($fieldmap);

#See if tokens are being used
$tablelist = $connect->MetaTables() or safe_die ("Error getting table list<br />".$connect->ErrorMsg());
foreach ($tablelist as $tbl)
{
	if ($tbl == "{$dbprefix}tokens_$surveyid") {$tokensexist =  1;}
}

#Lookup the names of the attributes
$query="SELECT sid, attribute1, attribute2, private, language FROM {$dbprefix}surveys WHERE sid=$surveyid";
$result=db_execute_assoc($query) or safe_die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());  //Checked
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

    $tablefieldnames = array_values($connect->MetaColumnNames("{$dbprefix}tokens_$surveyid", true));
	foreach ($tablefieldnames as $tokenfieldname) {
		$token_fields[]=$tokenfieldname;
	}
	if (in_array("firstname", $token_fields)) {
		$fields[$fieldno++]=array("id"=>"fname","name"=>$clang->gT("First Name"),"code"=>"","qid"=>0,"LStype"=>"Undef","SPSStype"=>"A","size"=>40);
	}
	if (in_array("lastname", $token_fields)) {
		$fields[$fieldno++]=array("id"=>"lname","name"=> $clang->gT("Last Name"),"code"=>"","qid"=>0,"LStype"=>"Undef","SPSStype"=>"A","size"=>40);
	}
	if (in_array("email", $token_fields)) {
		$fields[$fieldno++]=array("id"=>"email","name"=> $clang->gT("Email"),"code"=>"","qid"=>0,"LStype"=>"Undef","SPSStype"=>"A","size"=>100);
	}
	if (in_array("attribute_1", $token_fields)) {
		$fields[$fieldno++]=array("id"=>"attr1","name"=>$attr1_name,"code"=>"","qid"=>0,"LStype"=>"Undef","SPSStype"=>"A","size"=>100);
	}
	if (in_array("attribute_2", $token_fields)) {
		$fields[$fieldno++]=array("id"=>"attr2","name"=>$attr2_name,"code"=>"","qid"=>0,"LStype"=>"Undef","SPSStype"=>"A","size"=>100);
	}
} else {
	$fields=array();
}

$tempArray = array();
$fieldnames = array_values($connect->MetaColumnNames("{$dbprefix}survey_$surveyid", true));
$num_results = count($fieldnames);
$num_fields = $num_results;
# Build array that has to be returned
for ($i=0; $i < $num_results; $i++) {
	#Conditions for SPSS fields:
	# - Length may not be longer than 8 characters
	# - Name may not begin with a digit
	$fieldname = $fieldnames[$i];
	$fieldtype = "";
	$val_size = 1;
	//echo $fieldname." - ";
	
	#Determine field type
	if ($fieldname=='submitdate' || $fieldname=='startdate' || $fieldname == 'datestamp') {
		$fieldtype = 'DATETIME20.0';
		$ftype = 'DATETIME';
	} elseif ($fieldname=='startlanguage') {
		$fieldtype = 'A';
		$val_size = 19;
	} elseif ($fieldname=='token') {
		$fieldtype = 'A';
		$ftype = 'VARCHAR';
		$val_size = 16;
	} elseif ($fieldname=='id') {
		$fieldtype = 'N'; 
        $ftype = 'ID'; 
		$val_size = 7; //Arbitrarilty restrict to 9,999,999 (7 digits) responses/survey
	} elseif ($fieldname == 'ipaddr') {
		$fieldtype = 'A';
        $ftype = 'IP'; 
		$val_size = '15';
	} elseif ($fieldname == 'refurl') {
		$fieldtype = 'A';
        $ftype = 'REFURL'; 
		$val_size = 255;
	}
	
	#Get qid (question id)
	$code='';
	$noQID = Array('id', 'token', 'stamp', 'submitdate', 'startdate', 'attribute_1', 'attribute_2', 'startlanguage', 'ipaddr', 'refurl');
	if (in_array($fieldname, $noQID)){
		$qid = 0;
	} else{
		//GET FIELD DATA
		$fielddata=arraySearchByKey($fieldname, $fieldmap, 'fieldname', 1);
		$qid=$fielddata['qid'];
		$ftype=$fielddata['type'];
		$fsid=$fielddata['sid'];
		$fgid=$fielddata['gid'];
		$code=$fielddata['aid'];
		if($fieldtype == '') $fieldtype = $typeMap[$ftype]['SPSStype'];
	}
    
	$tempArray = array($fieldno++ =>array("id"=>"d".$fieldno,
			"name"=>mb_substr($fieldname, 0, 8),
			"qid"=>$qid, "code"=>$code,"SPSStype"=>$fieldtype,
			"LStype"=>"$ftype","LSlong"=>isset($typeMap[$ftype]["name"])?$typeMap[$ftype]["name"]:$ftype,"ValueLabels"=>"",
			"VariableLabel"=>"","sql_name"=>$fieldname,"size"=>$val_size));
	    $fields = $fields + $tempArray;
    }
//print_r($fields);
//reset($fields);


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


/**
 * Code that prints out the actual data
 * Refactoring this into a function is impractical at this point, as it relies heavily on global variables.
 */
if (isset($tokensexist) && $tokensexist == 1 && $surveyprivate == 'N') {
	$query="SELECT {$dbprefix}tokens_$surveyid.firstname   ,
	       {$dbprefix}tokens_$surveyid.lastname    ,
	       {$dbprefix}tokens_$surveyid.email";
	if (in_array('attribute_1', $token_fields)) {
		$query .= ",\n		{$dbprefix}tokens_$surveyid.attribute_1";
	}
	if (in_array('attribute_2', $token_fields)) {
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
		$query .= ' WHERE submitdate is not null ';
	}
}


$result=db_execute_num($query) or safe_die("Couldn't get results<br />$query<br />".$connect->ErrorMsg()); //Checked
$num_results = $result->RecordCount();
$num_fields = $result->FieldCount();

$fp = mkTmpFile();
@fwrite($fp, "BEGIN DATA\n");
for ($i=0; $i < $num_results; $i++) {
	$row = $result->FetchRow();
	$fieldno = 0;
	while ($fieldno < $num_fields)
	{
		//echo " field: ".$fields[$fieldno]["id"]." id : ".$fields[$fieldno]["qid"]." val:".$row[$fieldno]."-type: ".$fields[$fieldno]['type']." |<br> ";
		if ($fieldno % 20 == 0) @fwrite($fp, chr(65+intval($fieldno/20)).' ');
		//if ($i==0) { echo "Field: $fieldno - Dati: ";var_dump($fields[$fieldno]);echo "\n"; }
		if ($fields[$fieldno]['SPSStype']=='DATETIME20.0'){
			#convert mysql  datestamp (yyyy-mm-dd hh:mm:ss) to SPSS datetime (dd-mmm-yyyy hh:mm:ss) format
            if (isset($row[$fieldno]))
            {
			    list( $year, $month, $day, $hour, $minute, $second ) = split( '([^0-9])', $row[$fieldno] );
			    if ($year != '' && (int)$year >= 1970) 
			    {
				    @fwrite($fp, "'".date('d-m-Y H:i:s', mktime( $hour, $minute, $second, $month, $day, $year ) )."' ");
			    } else 
			    {
				    @fwrite($fp,  "''");
			    }
            }  else 
                {
                    @fwrite($fp,  "''");
                }
		} else if ($fields[$fieldno]['LStype'] == 'Y') 
		{
			if ($row[$fieldno] == 'Y')    // Yes/No Question Type
			{
				@fwrite($fp, "'1' ");
			} else if ($row[$fieldno] == 'N'){
				@fwrite($fp, "'2' ");
			} else {
				@fwrite($fp, "'0' ");
			}
		} else if ($fields[$fieldno]['LStype'] == 'G')    //Gender
		{
			if ($row[$fieldno] == 'F')
			{
				@fwrite($fp, "'1' ");
			} else if ($row[$fieldno] == 'M'){
				@fwrite($fp, "'2' ");
			} else {
				@fwrite($fp, "'0' ");
			}
		} else if ($fields[$fieldno]['LStype'] == 'C')    //Yes/No/Uncertain
		{
			if ($row[$fieldno] == 'Y')
			{
				@fwrite($fp, "'1' ");
			} else if ($row[$fieldno] == 'N'){
				@fwrite($fp, "'2' ");
			} else if ($row[$fieldno] == 'U'){
				@fwrite($fp, "'3' ");
			} else {
				@fwrite($fp, "'0' ");
			}
		} else if ($fields[$fieldno]['LStype'] == 'E')     //Increase / Same / Decrease
		{
			if ($row[$fieldno] == 'I')
			{
				@fwrite($fp, "'1' ");
			} else if ($row[$fieldno] == 'S'){
				@fwrite($fp, "'2' ");
			} else if ($row[$fieldno] == 'D'){
				@fwrite($fp, "'3' ");
			} else {
				@fwrite($fp, "'0' ");
			}
		} else if ($fields[$fieldno]['LStype'] == 'M') 
		{
			if ($fields[$fieldno]['code'] == 'other')
			{
				$strTmp = strip_tags_full($row[$fieldno]);
				$len = @fwrite($fp, "'$strTmp' ") - 3; //Don't count the quotes
				//echo "On fields[$fieldno] wrote $len bytes current:{$fields[$fieldno]["size"]} ($strTmp)\n";
				if($len > $fields[$fieldno]['size']){
					$fields[$fieldno]['size'] = $len;
					//echo "max length changed\n";
				}
			} else if ($row[$fieldno] == 'Y')
			{
				@fwrite($fp, "'1' ");
			} else
			{
			   @fwrite($fp, "'0' ");
			}
		} else if ($fields[$fieldno]['LStype'] == 'P') 
		{
			if ($fields[$fieldno]['code'] == 'other' || $fields[$fieldno]['code'] == 'comment' || $fields[$fieldno]['code'] == 'othercomment')
			{
				$strTmp = strip_tags_full($row[$fieldno]);
				$len = @fwrite($fp, "'$strTmp' ") - 3; //Don't count the quotes
				//echo "On fields[$fieldno] wrote $len bytes current:{$fields[$fieldno]["size"]} ($strTmp)\n";
				if($len > $fields[$fieldno]['size']){
					$fields[$fieldno]['size'] = $len;
					//echo "max length changed\n";
				}
			} else if ($row[$fieldno] == 'Y')
			{
				@fwrite($fp, "'1' ");
			} else
			{
			   @fwrite($fp, "'0' ");
			}
		} else {
			$strTmp=mb_substr(strip_tags_full($row[$fieldno]), 0, $length_data);
			$len = strlen($strTmp);
	
			if($len > $fields[$fieldno]['size']){
				$fields[$fieldno]['size'] = $len;
				//echo "max length changed\n";
			}

			if($len > $fields[$fieldno]['size']) $fields[$fieldno]['size'] = $len;

			if (trim($strTmp) == ''){
				fwrite($fp, "'0'");
			}
			else {
                if (($fields[$fieldno]['SPSStype']=='N' && my_is_numeric($strTmp)===false) || $fields[$fieldno]['size'>16)
                {
                    $fields[$fieldno]['SPSStype']='A';
                }
				$len = fwrite($fp, '\''.$strTmp.'\'') - 3; //Don't count the quotes
				if($len > $fields[$fieldno]['size']){
					$fields[$fieldno]['size'] = $len;
					//echo "max length changed\n";
				}
			}
		}
		$fieldno++;
		if ($fieldno % 20 == 0) @fwrite($fp, "\n");
	}
	if ($fieldno % 20 != 0) @fwrite($fp, "\n");
	#Conditions for SPSS fields:
	# - Length may not be longer than 8 charac
}
@fwrite($fp, "END DATA.\nEXECUTE.\n\n");
@fseek($fp, 0);
/**
 * End of DATA print out
 *
 * Now $fields contains accurate length data, and the DATA LIST can be rendered -- then the contents of the temp file can
 * be sent to the client.
 */
renderDataList($fields);
if($fp){
	while($data = fread($fp, 4096)){
		echo $data;
	}
	closeTmpFile($fp);
} else {
	echo "* This is where your data would be, however LimeSurvey was unable to create a temporary file\n";
	echo "* to store the data as it was processed / outputted to generate the DATA LIST and BEGIN DATA / END DATA\n";
	echo "* statements.  Please verify that you have a you have a value stored in \$tempvalue, it's assigned\n";
	echo "* in config-defaults.php, but you may have to over ride it to a path that is owned by the same\n";
	echo "* user that owns this script (owns refers to file permissions)\n";
}

echo "*Define Variable Properties.\n";//minni"<br />";
foreach ($fields as $field){
	if (	$field['id'] == 'fname' OR
	$field['id']=='lname' OR
	$field['id']=='email' OR
	$field['id']=='attr1' OR
	$field['id']=='attr2'){
		echo "VARIABLE LABELS ".$field['id']." '".mb_substr(strip_tags_full($field['name']), 0, $length_varlabel)."'.\n";//minni"<br />";
	} elseif ($field['name']=='id') {
		echo "VARIABLE LABELS ".$field["id"]." '".$clang->gT('Record ID')."'.\n";//minni"<br />";
	} elseif ($field['name']=='submitda') {
		echo 'VARIABLE LABELS '.$field['id']." '".$clang->gT('Completion Date')."'.\n";//minni"<br />";
	} elseif ($field['name']=='startlan') {
		echo 'VARIABLE LABELS '.$field['id']." '".$clang->gT('Start Language')."'.\n";//minni"<br />";
	} elseif ($field['name']=='token') {
		echo 'VARIABLE LABELS '.$field['id']." '".$clang->gT('Token')."'.\n";
	}else{
		#If a split question
		if ($field['code'] != ''){
			#Lookup the question

			$query = "SELECT question, title 
			FROM {$dbprefix}questions WHERE sid='".$surveyid."' AND language='".$language."' 
			AND qid='".$field["qid"]."'";
			
			$result=db_execute_assoc($query) or safe_die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg()); //Checked
			$num_results = $result->RecordCount();
			$num_fields = $num_results;
			if ($num_results >0){
				# Build array that has to be returned
				for ($i=0; $i < $num_results; $i++) {
					$row = $result->FetchRow();
					$question_text = $row['question'];
					$question_title = $row['title'];
				}
			}
			#Lookup the answer
			$query = "SELECT answer FROM {$dbprefix}answers WHERE 
			qid='".$field["qid"]."' and language='".$language."' AND code ='".$field["code"]."'";
			$result=db_execute_assoc($query) or safe_die("Couldn't lookup answer<br />$query<br />".$connect->ErrorMsg());  //Checked
			$num_results = $result->RecordCount();
			$num_fields = $num_results;
			if ($num_results >0){
				# Build array that has to be returned
				for ($i=0; $i < $num_results; $i++) {
					$row = $result->FetchRow();
					echo "VARIABLE LABELS ".$field['id']." '".mb_substr(strip_tags_full($question_title), 0, $length_varlabel)." - ".mb_substr(strip_tags_full($row['answer']), 0, $length_varlabel)."'.\n";//minni"<br />";
				}
			}
			if (mb_substr($field['sql_name'], -5)=='other') {
				echo "VARIABLE LABELS ".$field["id"]." '".
				mb_substr(strip_tags_full($question_text), 0, $length_varlabel-8)." - OTHER'.\n";
			}
			if (mb_substr($field['sql_name'], -7)=='comment') {
				echo "VARIABLE LABELS ".$field['id']." '".
				mb_substr(strip_tags_full($question_text), 0, $length_varlabel-10)." - COMMENT'.\n";
			}
		}else{
			$test=explode ("X", $field['name']);
			$query = "SELECT question FROM {$dbprefix}questions 
			WHERE sid ='".$surveyid."' AND language='".$language."' 
			AND qid='".$field['qid']."'";
			$result=db_execute_assoc($query) or safe_die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg()); //Checked
			$row = $result->FetchRow();
			echo "VARIABLE LABELS ".$field['id']." '".
			mb_substr(strip_tags_full($row["question"]), 0, $length_varlabel)."'.\n";
		}
	}
}

// Create our Value Labels!
echo "*Define Value labels.\n";
reset($fields);
foreach ($fields as $field)
{
	if ($field['qid']!=0)
	{
		if ($field['LStype'] != 'K' && $field['LStype'] != 'S' && $field['LStype'] != 'T' && $field['LStype'] != 'Q' && $field['LStype'] != 'U' && $field['LStype'] != 'A' && $field['LStype'] != 'B' && $field['LStype'] != 'F' && $field['LStype'] != 'M' && $field['LStype'] != 'P' && $field['LStype'] != 'C' && $field['LStype'] != 'E')
		{
			$query = "SELECT {$dbprefix}answers.code, {$dbprefix}answers.answer, 
			{$dbprefix}questions.type FROM {$dbprefix}answers, {$dbprefix}questions WHERE 
			{$dbprefix}answers.qid = '".$field["qid"]."' and {$dbprefix}questions.language='".$language."' and  {$dbprefix}answers.language='".$language."'
			and {$dbprefix}questions.qid='".$field['qid']."'";
			$result=db_execute_assoc($query) or safe_die("Couldn't lookup value labels<br />$query<br />".$connect->ErrorMsg()); //Checked
			$num_results = $result->RecordCount();
			if ($num_results > 0)
			{
				$displayvaluelabel = 0;
				# Build array that has to be returned
				for ($i=0; $i < $num_results; $i++)
				{
					$row = $result->FetchRow();

					if ($displayvaluelabel == 0) echo 'VALUE LABELS '.$field['id']."\n";
					if ($displayvaluelabel == 0) $displayvaluelabel = 1;
					if ($i == ($num_results-1))
					{
						echo "\"" . $row['code']."\" \"".strip_tags_full(mb_substr($row["answer"],0,$length_vallabel))."\".\n"; // put .
					} else {
						echo "\"" . $row['code']."\" \"".strip_tags_full(mb_substr($row['answer'],0,$length_vallabel))."\"\n";
					}
				}
			}
		}
		if ($field['LStype'] == 'F' || $field['LStype'] == 'W' || $field['LStype'] == 'Z')
		{
			$displayvaluelabel = 0;
			$query = "SELECT {$dbprefix}questions.lid, {$dbprefix}labels.code, {$dbprefix}labels.title from 
			{$dbprefix}questions, {$dbprefix}labels WHERE {$dbprefix}labels.language='".$language."' and
			{$dbprefix}questions.language='".$language."' and 
			{$dbprefix}questions.qid ='".$field["qid"]."' and {$dbprefix}questions.lid={$dbprefix}labels.lid";
			$result=db_execute_assoc($query) or safe_die("Couldn't get labels<br />$query<br />".$connect->ErrorMsg());   //Checked
			$num_results = $result->RecordCount();
			if ($num_results > 0)
			{
				for ($i=0; $i < $num_results; $i++)
				{
					$row = $result->FetchRow();
					if ($displayvaluelabel == 0) echo "VALUE LABELS ".$field['id']."\n";
					if ($displayvaluelabel == 0) $displayvaluelabel = 1;
					if ($i == ($num_results-1))
					{
						echo "\"" . $row['code']."\" \"".strip_tags_full(mb_substr($row["title"],0,$length_vallabel))."\".\n"; // put . at end
					} else {
						echo "\"" . $row['code']."\" \"".strip_tags_full(mb_substr($row["title"],0,$length_vallabel))."\"\n";

					}
				}
			}
		}
		if ($field['LStype'] == 'M' && $field['code'] != 'other' && $field['size'] > 0)
		{
			echo "VALUE LABELS ".$field['id']."\n";
			echo "1 \"".$clang->gT('Yes')."\"\n";
			echo "0 \"".$clang->gT('Not Selected')."\".\n";
		}
		if ($field['LStype'] == "P" && $field['code'] != 'other' && $field['code'] != 'comment' && $field['code'] != 'othercomment')
		{
			echo "VALUE LABELS ".$field['id']."\n";
			echo "1 \"".$clang->gT("Yes")."\"\n";
			echo "0 \"".$clang->gT('Not Selected')."\".\n";
		}
		if ($field['LStype'] == "G" && $field['size'] > 0)
		{
			echo "VALUE LABELS ".$field['id']."\n";
			echo "1 \"".$clang->gT('Female')."\"\n";
			echo "2 \"".$clang->gT("Male")."\".\n";
		}
		if ($field['LStype'] == "Y" && $field['size'] > 0)
		{
			echo "VALUE LABELS ".$field['id']."\n";
			echo "1 \"".$clang->gT('Yes')."\"\n";
			echo "2 \"".$clang->gT("No")."\".\n";
		}
		if ($field['LStype'] == "C" && $field['size'] > 0)
		{
			echo "VALUE LABELS ".$field['id']."\n";
			echo "1 \"".$clang->gT('Yes')."\"\n";
			echo "2 \"".$clang->gT('No')."\".\n";
			echo "3 \"".$clang->gT('Uncertain')."\".\n";
		}
		if ($field['LStype'] == "E" && $field['size'] > 0)
		{
			echo "VALUE LABELS ".$field['id']."\n";
			echo "1 \"".$clang->gT('Increase')."\"\n";
			echo "2 \"".$clang->gT('Same')."\".\n";
			echo "3 \"".$clang->gT('Decrease')."\".\n";
		}
	}
}

function strip_tags_full($string) {
	$string=html_entity_decode_php4($string, ENT_QUOTES, "UTF-8");
	//combining these into one mb_ereg_replace call ought to speed things up
	$string = str_replace(array("\r\n",'-oth-'), '', $string);
	//The backslashes must be escaped twice, once for php, and again for the regexp 
    $string = str_replace("'|\\\\'", "&apos;", $string);
    return strip_tags($string);
}

function my_is_numeric($value)  {
    $american = preg_match ("/^(-){0,1}([0-9]+)(,[0-9][0-9][0-9])*([.][0-9]){0,1}([0-9]*)$/" ,$value) == 1;
    $world = preg_match ("/^(-){0,1}([0-9]+)(.[0-9][0-9][0-9])*([,][0-9]){0,1}([0-9]*)$/" ,$value) == 1;
   return ($american or $world);
}


exit;
?>
