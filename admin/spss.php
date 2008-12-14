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
$length_vallabel = '120'; // Set the max text length of Value Labels
$headerComment = '';
$tempFile = '';

include_once('login_check.php');

$typeMap = array(
'5'=>Array('name'=>'5 Point Choice','size'=>1,'SPSStype'=>'F'),
'B'=>Array('name'=>'Array (10 Point Choice)','size'=>1,'SPSStype'=>'F'),
'A'=>Array('name'=>'Array (5 Point Choice)','size'=>1,'SPSStype'=>'F'),
'F'=>Array('name'=>'Array (Flexible Labels)','size'=>1,'SPSStype'=>'F'),
'1'=>Array('name'=>'Array (Flexible Labels) Dual Scale','size'=>1,'SPSStype'=>'F'),
'H'=>Array('name'=>'Array (Flexible Labels) by Column','size'=>1,'SPSStype'=>'F'),
'E'=>Array('name'=>'Array (Increase, Same, Decrease)','size'=>1,'SPSStype'=>'F'),
'C'=>Array('name'=>'Array (Yes/No/Uncertain)','size'=>1,'SPSStype'=>'F'),
'X'=>Array('name'=>'Boilerplate Question','size'=>1,'SPSStype'=>'A'),
'D'=>Array('name'=>'Date','size'=>null,'SPSStype'=>'SDATE'),
'G'=>Array('name'=>'Gender','size'=>1,'SPSStype'=>'F'),
'U'=>Array('name'=>'Huge Free Text','size'=>1,'SPSStype'=>'A'),
'I'=>Array('name'=>'Language Switch','size'=>1,'SPSStype'=>'A'),
'!'=>Array('name'=>'List (Dropdown)','size'=>1,'SPSStype'=>'F'),
'W'=>Array('name'=>'List (Flexible Labels) (Dropdown)','size'=>1,'SPSStype'=>'F'),
'Z'=>Array('name'=>'List (Flexible Labels) (Radio)','size'=>1,'SPSStype'=>'F'),
'L'=>Array('name'=>'List (Radio)','size'=>1,'SPSStype'=>'F'),
'O'=>Array('name'=>'List With Comment','size'=>1,'SPSStype'=>'F'),
'T'=>Array('name'=>'Long free text','size'=>1,'SPSStype'=>'A'),
'K'=>Array('name'=>'Multiple Numerical Input','size'=>1,'SPSStype'=>'F'),
'M'=>Array('name'=>'Multiple Options','size'=>1,'SPSStype'=>'F'),
'P'=>Array('name'=>'Multiple Options With Comments','size'=>1,'SPSStype'=>'F'),
'Q'=>Array('name'=>'Multiple Short Text','size'=>1,'SPSStype'=>'F'),
'N'=>Array('name'=>'Numerical Input','size'=>3,'SPSStype'=>'F'),
'R'=>Array('name'=>'Ranking','size'=>1,'SPSStype'=>'F'),
'S'=>Array('name'=>'Short free text','size'=>1,'SPSStype'=>'F'),
'Y'=>Array('name'=>'Yes/No','size'=>1,'SPSStype'=>'F'),
);

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

function renderDataList($fieldArr){
	global $headerComment, $surveyid;
	echo $headerComment;

   echo "SET UNICODE=ON.\n"
       ."GET DATA\n"
       ."/TYPE=TXT\n"
       ."/FILE='survey_".$surveyid."_SPSS_data_file.dat'\n"
       ."/DELCASE=LINE\n"
       ."/DELIMITERS=\",\"\n"
       ."/QUALIFIER=\"'\"\n"
       ."/ARRANGEMENT=DELIMITED\n"
       ."/FIRSTCASE=1\n"
       ."/IMPORTCASE=ALL\n"
       ."/VARIABLES=";    
	$i=0;
	foreach ($fieldArr as $field){
        echo "\n";
		if($field['SPSStype'] == 'DATETIME23.2') $field['size']='';
        if($field['LStype'] == 'N' || $field['LStype']=='K') {
            $field['size'].='.'.($field['size']-1);
        }
		echo " {$field['id']} {$field['SPSStype']}{$field['size']}";
		$i++;
	}
	echo ".\n"
        ."CACHE.\n"
        ."EXECUTE.\n";
}

if  (!isset($subaction))
{
    $exportspssoutput = browsemenubar($clang->gT('Export results'));
    $exportspssoutput .= "<br />\n";
    $exportspssoutput .= "<div class='header'>".$clang->gT("Export result data to SPSS")."</div>\n";
    $exportspssoutput .= "<p style='width:100%;'><ul style='width:300px;margin:0 auto;'><li><a href='$scriptname?action=exportspss&amp;sid=$surveyid&amp;subaction=dlstructure'>".$clang->gT("Export SPSS syntax file")."</a></li><li>"
                        ."<a href='$scriptname?action=exportspss&amp;sid=$surveyid&amp;subaction=dldata'>".$clang->gT("Export SPSS data file")."</a></li></ul></p><br />\n"
                        ."<h3>".$clang->gT("Instructions for the impatient")."</h3>"
                        ."<ol style='width:500px;margin:0 auto; font-size:8pt;'>"
                        ."<li>".$clang->gT("Download the data and the syntax file.")."</li>"
                        ."<li>".$clang->gT("Open the syntax file in SPSS in Unicode mode").".</li>"
                        ."<li>".$clang->gT("Edit the 4th line and complete the filename with a full path to he downloaded data file.")."</li>"
                        ."<li>".$clang->gT("Choose 'Run/All' from the menu to run the import.")."</li>"
                        ."</ol><br />"
                        .$clang->gT("Your data should be imported now.")
                        ."<table><tr><td>";
                        
                        
                                                                                        
  
} 

if  ($subaction=='dldata') 
{
    header("Content-Type: application/download; charset=utf-8");
    header("Content-Disposition: attachment; filename=survey_".$surveyid."_SPSS_data_file.dat");
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: no-cache');
    
    
  // Get Base Language:

    $language = GetBaseLanguageFromSurveyID($surveyid);
    $clang = new limesurvey_lang($language);

    sendcacheheaders();

    # Build array that has to be returned
    $fieldmap=createFieldMap($surveyid);

    //echo 'FieldMap:';
    //print_r($fieldmap);

    #See if tokens are being used
    $tablelist = $connect->MetaTables() or safe_die ('Error getting table list<br />'.$connect->ErrorMsg());
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
        if ($row['attribute1']) {$attr1_name = $row['attribute1'];} else {$attr1_name = $clang->gT('Attribute 1');}
        if ($row['attribute2']) {$attr2_name = $row['attribute2'];} else {$attr2_name = $clang->gT('Attribute 2');}
        $surveyprivate=$row['private'];
        $language=$row['language'];
    }

    $fieldno=0;

    if (isset($tokensexist) && $tokensexist == 1 && $surveyprivate == 'N') {

        $tablefieldnames = array_values($connect->MetaColumnNames("{$dbprefix}tokens_$surveyid", true));
        foreach ($tablefieldnames as $tokenfieldname) {
            $token_fields[]=$tokenfieldname;
        }
        if (in_array('firstname', $token_fields)) {
            $fields[$fieldno++]=array('id'=>'fname','name'=>$clang->gT('First Name'),'code'=>'','qid'=>0,'LStype'=>'Undef','SPSStype'=>'A','size'=>40);
        }
        if (in_array('lastname', $token_fields)) {
            $fields[$fieldno++]=array('id'=>'lname','name'=> $clang->gT('Last Name'),'code'=>'','qid'=>0,'LStype'=>'Undef','SPSStype'=>'A','size'=>40);
        }
        if (in_array('email', $token_fields)) {
            $fields[$fieldno++]=array('id'=>'email','name'=> $clang->gT('Email'),'code'=>'','qid'=>0,'LStype'=>'Undef','SPSStype'=>'A','size'=>100);
        }
        if (in_array('attribute_1', $token_fields)) {
            $fields[$fieldno++]=array('id'=>'attr1','name'=>$attr1_name,'code'=>'','qid'=>0,'LStype'=>'Undef','SPSStype'=>'A','size'=>100);
        }
        if (in_array('attribute_2', $token_fields)) {
            $fields[$fieldno++]=array('id'=>'attr2','name'=>$attr2_name,'code'=>'','qid'=>0,'LStype'=>'Undef','SPSStype'=>'A','size'=>100);
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
        $fieldtype = '';
        $val_size = 1;
        //echo $fieldname.' - ';
        
        #Determine field type
        if ($fieldname=='submitdate' || $fieldname=='startdate' || $fieldname == 'datestamp') {
            $fieldtype = 'DATETIME23.2';
            $ftype = 'DATETIME';
        } elseif ($fieldname=='startlanguage') {
            $fieldtype = 'A';
            $val_size = 19;
        } elseif ($fieldname=='token') {
            $fieldtype = 'A';
            $ftype = 'VARCHAR';
            $val_size = 16;
        } elseif ($fieldname=='id') {
            $fieldtype = 'F'; 
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
        
        $tempArray = array($fieldno++ =>array('id'=>'d'.$fieldno,
                'name'=>mb_substr($fieldname, 0, 8),
                'qid'=>$qid, 'code'=>$code,'SPSStype'=>$fieldtype,
                'LStype'=>$ftype,'LSlong'=>isset($typeMap[$ftype]['name'])?$typeMap[$ftype]['name']:$ftype,'ValueLabels'=>'',
                'VariableLabel'=>'','sql_name'=>$fieldname,'size'=>$val_size));
            $fields = $fields + $tempArray;
        }

    /**
     * Code that prints out the actual data
     * Refactoring this into a function is impractical at this point, as it relies heavily on global variables.
     */
    if (isset($tokensexist) && $tokensexist == 1 && $surveyprivate == 'N') {
        $query="SELECT {$dbprefix}tokens_$surveyid.firstname   ,
               {$dbprefix}tokens_$surveyid.lastname    ,
               {$dbprefix}tokens_$surveyid.email";
        if (in_array('attribute_1', $token_fields)) {
            $query .= ",\n        {$dbprefix}tokens_$surveyid.attribute_1";
        }
        if (in_array('attribute_2', $token_fields)) {
            $query .= ",\n        {$dbprefix}tokens_$surveyid.attribute_2";
        }
        $query .= ",\n           {$dbprefix}survey_$surveyid.*
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

    for ($i=0; $i < $num_results; $i++) {
        $row = $result->FetchRow();
        $fieldno = 0;
        while ($fieldno < $num_fields)
        {
            if ($fields[$fieldno]['SPSStype']=='DATETIME23.2'){
                #convert mysql  datestamp (yyyy-mm-dd hh:mm:ss) to SPSS datetime (dd-mmm-yyyy hh:mm:ss) format
                if (isset($row[$fieldno]))
                {
                    list( $year, $month, $day, $hour, $minute, $second ) = split( '([^0-9])', $row[$fieldno] );
                    if ($year != '' && (int)$year >= 1970) 
                    {
                        echo "'".date('d-m-Y H:i:s', mktime( $hour, $minute, $second, $month, $day, $year ) )."'";
                    } else 
                    {
                        echo ("''");
                    }
                }  else 
                    {
                        echo (  "''");
                    }  
            } else if ($fields[$fieldno]['LStype'] == 'Y') 
            {
                if ($row[$fieldno] == 'Y')    // Yes/No Question Type
                {
                    echo( "'1'");
                } else if ($row[$fieldno] == 'N'){
                    echo( "'2'");
                } else {
                    echo( "'0'");
                }     
            } else if ($fields[$fieldno]['LStype'] == 'G')    //Gender
            {
                if ($row[$fieldno] == 'F')
                {
                    echo( "'1'");
                } else if ($row[$fieldno] == 'M'){
                    echo( "'2'");
                } else {
                    echo( "'0'");
                }                        
            } else if ($fields[$fieldno]['LStype'] == 'C')    //Yes/No/Uncertain
            {     
                if ($row[$fieldno] == 'Y')
                {
                    echo( "'1'");
                } else if ($row[$fieldno] == 'N'){
                    echo( "'2'");
                } else if ($row[$fieldno] == 'U'){
                    echo( "'3'");
                } else {
                    echo( "'0'");
                }                        
            } else if ($fields[$fieldno]['LStype'] == 'E')     //Increase / Same / Decrease
            {             
                if ($row[$fieldno] == 'I')
                {
                    echo( "'1'");
                } else if ($row[$fieldno] == 'S'){
                    echo( "'2'");
                } else if ($row[$fieldno] == 'D'){
                    echo( "'3'");
                } else {
                    echo( "'0'");
                }           
            }elseif ($fields[$fieldno]['LStype'] == 'M') 
            {
                if ($fields[$fieldno]['code'] == 'other')
                {
                    $strTmp = strip_tags_full($row[$fieldno]);
                    $len = mb_strlen($strTmp);
                    echo "'$strTmp'";
                    if($len > $fields[$fieldno]['size']){
                        $fields[$fieldno]['size'] = $len;
                    }
                    if ($fields[$fieldno]['SPSStype']=='F' && (my_is_numeric($strTmp)===false || $fields[$fieldno]['size']>16))
                    {
                        $fields[$fieldno]['SPSStype']='A';
                    }
                }  else if ($row[$fieldno] == 'Y')
                {
                    echo("'1'");
                } else
                {
                   echo("'0'");
                }
            } else if ($fields[$fieldno]['LStype'] == 'P') 
            {
                if ($fields[$fieldno]['code'] == 'other' || $fields[$fieldno]['code'] == 'comment' || $fields[$fieldno]['code'] == 'othercomment')
                {
                    $strTmp = strip_tags_full($row[$fieldno]);
                    $len = mb_strlen($strTmp);
                    echo "'$strTmp'";                    
                    if($len > $fields[$fieldno]['size']){
                        $fields[$fieldno]['size'] = $len;
                    }
                } else if ($row[$fieldno] == 'Y')
                {
                    echo("'1'");
                } else
                {
                   echo("'0'");
                }
            } else {
                $strTmp=mb_substr(strip_tags_full($row[$fieldno]), 0, $length_data);
                $len = mb_strlen($strTmp);
        
                if($len > $fields[$fieldno]['size']){
                    $fields[$fieldno]['size'] = $len;
                    //echo "max length changed\n";
                }

                if($len > $fields[$fieldno]['size']) $fields[$fieldno]['size'] = $len;

                if (trim($strTmp) != ''){
                    if (($fields[$fieldno]['SPSStype']=='F' && my_is_numeric($strTmp)===false) || $fields[$fieldno]['size']>16)
                    {
                        $fields[$fieldno]['SPSStype']='A';
                    }
                    $len = mb_strlen($strTmp); //Don't count the quotes
                    if($len > $fields[$fieldno]['size']){
                        $fields[$fieldno]['size'] = $len;
                    }
                    $strTemp=str_replace(array("'","\n","\r"),array(' '),trim($strTmp));
                    echo "'$strTemp'";
                }
                else
                {  
                    echo "'0'";
                }
            }
            $fieldno++;
            if ($fieldno<$num_fields) echo ',';
        }
        echo "\n";
    }
    exit;
    
}


if  ($subaction=='dlstructure') 
{
    header("Content-Type: application/download; charset=utf-8");
    header("Content-Disposition: attachment; filename=survey_".$surveyid."_SPSS_syntax_file.sps");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: no-cache');

    // Get Base Language:

    $language = GetBaseLanguageFromSurveyID($surveyid);
    $clang = new limesurvey_lang($language);

    sendcacheheaders();

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
	    if ($row['attribute1']) {$attr1_name = $row['attribute1'];} else {$attr1_name = $clang->gT('Attribute 1');}
	    if ($row['attribute2']) {$attr2_name = $row['attribute2'];} else {$attr2_name = $clang->gT('Attribute 2');}
	    $surveyprivate=$row['private'];
	    $language=$row['language'];
    }

    $fieldno=0;

    if (isset($tokensexist) && $tokensexist == 1 && $surveyprivate == 'N') {

        $tablefieldnames = array_values($connect->MetaColumnNames("{$dbprefix}tokens_$surveyid", true));
	    foreach ($tablefieldnames as $tokenfieldname) {
		    $token_fields[]=$tokenfieldname;
	    }
	    if (in_array('firstname', $token_fields)) {
		    $fields[$fieldno++]=array('id'=>'fname','name'=>$clang->gT('First Name'),'code'=>'','qid'=>0,'LStype'=>'Undef','SPSStype'=>'A','size'=>40);
	    }
	    if (in_array('lastname', $token_fields)) {
		    $fields[$fieldno++]=array('id'=>'lname','name'=> $clang->gT('Last Name'),'code'=>'','qid'=>0,'LStype'=>'Undef','SPSStype'=>'A','size'=>40);
	    }
	    if (in_array('email', $token_fields)) {
		    $fields[$fieldno++]=array('id'=>'email','name'=> $clang->gT('Email'),'code'=>'','qid'=>0,'LStype'=>'Undef','SPSStype'=>'A','size'=>100);
	    }
	    if (in_array('attribute_1', $token_fields)) {
		    $fields[$fieldno++]=array('id'=>'attr1','name'=>$attr1_name,'code'=>'','qid'=>0,'LStype'=>'Undef','SPSStype'=>'A','size'=>100);
	    }
	    if (in_array('attribute_2', $token_fields)) {
		    $fields[$fieldno++]=array('id'=>'attr2','name'=>$attr2_name,'code'=>'','qid'=>0,'LStype'=>'Undef','SPSStype'=>'A','size'=>100);
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
	    $fieldtype = '';
	    $val_size = 1;
	    //echo $fieldname." - ";
	    
	    #Determine field type
	    if ($fieldname=='submitdate' || $fieldname=='startdate' || $fieldname == 'datestamp') {
		    $fieldtype = 'DATETIME23.2';
		    $ftype = 'DATETIME';
	    } elseif ($fieldname=='startlanguage') {
		    $fieldtype = 'A';
		    $val_size = 19;
	    } elseif ($fieldname=='token') {
		    $fieldtype = 'A';
		    $ftype = 'VARCHAR';
		    $val_size = 16;
	    } elseif ($fieldname=='id') {
		    $fieldtype = 'F'; 
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
        
	    $tempArray = array($fieldno++ =>array('id'=>'d'.$fieldno,
			    'name'=>mb_substr($fieldname, 0, 8),
			    'qid'=>$qid, 'code'=>$code,'SPSStype'=>$fieldtype,
			    'LStype'=>$ftype,"LSlong"=>isset($typeMap[$ftype]["name"])?$typeMap[$ftype]["name"]:$ftype,"ValueLabels"=>"",
			    "VariableLabel"=>"","sql_name"=>$fieldname,"size"=>$val_size));
	        $fields = $fields + $tempArray;
        }



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

    for ($i=0; $i < $num_results; $i++) {
	    $row = $result->FetchRow();
	    $fieldno = 0;
	    while ($fieldno < $num_fields)
	    {
		    if ($fields[$fieldno]['LStype'] == 'M') 
		    {
			    if ($fields[$fieldno]['code'] == 'other')
			    {
				    $strTmp = strip_tags_full($row[$fieldno]);
				    if($len > $fields[$fieldno]['size']){
					    $fields[$fieldno]['size'] = mb_strlen($strTmp);
				    }
                    if ($fields[$fieldno]['SPSStype']=='F' && (my_is_numeric($strTmp)===false || $fields[$fieldno]['size']>16))
                    {
                        $fields[$fieldno]['SPSStype']='A';
                    }
			    } 
		    } else if ($fields[$fieldno]['LStype'] == 'P') 
		    {
			    if ($fields[$fieldno]['code'] == 'other' || $fields[$fieldno]['code'] == 'comment' || $fields[$fieldno]['code'] == 'othercomment')
			    {
				    $strTmp = strip_tags_full($row[$fieldno]);
				    $len = mb_strlen($strTmp);
				    if($len > $fields[$fieldno]['size']){
					    $fields[$fieldno]['size'] = $len;
				    }
			    } 
		    } else {
			    $strTmp=mb_substr(strip_tags_full($row[$fieldno]), 0, $length_data);
			    $len = mb_strlen($strTmp);
	    
			    if($len > $fields[$fieldno]['size']){
				    $fields[$fieldno]['size'] = $len;
			    }

			    if($len > $fields[$fieldno]['size']) $fields[$fieldno]['size'] = $len;

			    if (trim($strTmp) != ''){
                    if ($fields[$fieldno]['SPSStype']=='F' && (my_is_numeric($strTmp)===false || $fields[$fieldno]['size']>16))
                    {
                        $fields[$fieldno]['SPSStype']='A';
                    }
				    $len = mb_strlen($strTmp); //Don't count the quotes
				    if($len > $fields[$fieldno]['size']){
					    $fields[$fieldno]['size'] = $len;
				    }
			    }
		    }
		    $fieldno++;
	    }
	    #Conditions for SPSS fields:
	    # - Length may not be longer than 8 charac
    }
    /**
     * End of DATA print out
     *
     * Now $fields contains accurate length data, and the DATA LIST can be rendered -- then the contents of the temp file can
     * be sent to the client.
     */
    renderDataList($fields);

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
			if($field['ftype'] == ":")
			{
			    //get the lid
			    $query = "SELECT lid FROM {$dbprefix}questions WHERE qid='".$field["qid"]."'";
			    $result = db_execute_assoc($query) or die("Couldnt!<br />$query<br />".$connect->ErrorMsg());
			    $row=$result->FetchRow();
			    $lid=$row['lid'];
			    list($ac, $lc)=explode("_", $field["code"]);
    			$query = "SELECT title FROM {$dbprefix}labels WHERE
    			lid='$lid' AND code='$lc' and language='".$language."'";
    			$result=db_execute_assoc($query) or die("Couldnt!<br />$query<br />".$connect->ErrorMsg());
    			$num_results = $result->RecordCount();
    			if ($num_results > 0)
    			{
				    for($i=0; $i<$num_results; $i++)
				    {
					  $row=$result->FetchRow();
					  $labels[]=$row['title'];
					}
				}
    			
				$query = "SELECT answer FROM {$dbprefix}answers WHERE 
    			qid='".$field["qid"]."' and language='".$language."' AND code ='$ac'";
    			$result=db_execute_assoc($query) or die("Couldn't lookup answer<br />$query<br />".$connect->ErrorMsg());
    			$num_results = $result->RecordCount();
    			$num_fields = $num_results;
    			if ($num_results >0){
    				# Build array that has to be returned
    				for ($i=0; $i < $num_results; $i++) {
    					$row = $result->FetchRow();
    					foreach($labels as $label) {
    					    echo "VARIABLE LABELS ".$field["id"]." '".mb_substr(strip_tags_full($question_title), 0, $length_varlabel)." - ".mb_substr(strip_tags_full($row["answer"]." [".$label."]"), 0, $length_varlabel)."'.\n";//minni"<br />";
    				    }
					}
    			}
    			unset($labels);
			} else {
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
		if ($field['LStype'] != 'K' && $field['LStype'] != 'S' && $field['LStype'] != 'T' && $field['LStype'] != 'Q' && $field['LStype'] != 'U' && $field['LStype'] != 'A' && $field['LStype'] != 'B' && $field['LStype'] != 'F' && $field['LStype'] != 'M' && $field['LStype'] != 'P' && $field['LStype'] != 'C' && $field['LStype'] != 'E' && $field['ftype'] != ':')
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
						    echo " \"" . $row['code']."\" \"".strip_tags_full(mb_substr($row["answer"],0,$length_vallabel))."\".\n"; // put .
					    } else {
						    echo " \"" . $row['code']."\" \"".strip_tags_full(mb_substr($row['answer'],0,$length_vallabel))."\"\n";
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
						    echo " \"" . $row['code']."\" \"".strip_tags_full(mb_substr($row["title"],0,$length_vallabel))."\".\n"; // put . at end
					    } else {
						    echo " \"" . $row['code']."\" \"".strip_tags_full(mb_substr($row["title"],0,$length_vallabel))."\"\n";

					    }
				    }
			    }
		    }
		if ($field['LStype'] == ':')
		{
			$displayvaluelabel = 0;
			//Get the labels that could apply!
			$qidattributes=getQuestionAttributes($field["qid"]);
        	if ($maxvalue=arraySearchByKey("multiflexible_max", $qidattributes, "attribute", 1)) {
        		$maxvalue=$maxvalue['value'];
        	} else {
        		$maxvalue=10;
        	}
        	if ($minvalue=arraySearchByKey("multiflexible_min", $qidattributes, "attribute", 1)) {
        		$minvalue=$minvalue['value'];
        	} else {
        		$minvalue=1;
        	}
        	if ($stepvalue=arraySearchByKey("multiflexible_step", $qidattributes, "attribute", 1)) {
        		$stepvalue=$stepvalue['value'];
        	} else {
        		$stepvalue=1;
        	}
		if (arraySearchByKey("multiflexible_checkbox", $qidattributes, "attribute", 1)) {
			$minvalue=0;
			$maxvalue=1;
			$stepvalue=1;
		}
            for ($i=$minvalue; $i<=$maxvalue; $i+=$stepvalue)
            {
			    if ($displayvaluelabel == 0) echo "VALUE LABELS ".$field["id"]."\n";  //Beginning of the line
			    if ($displayvaluelabel == 0) $displayvaluelabel = 1; //Now do the rest of the line
			    echo " $i \"$i\".\n";
			}
		}
		    if ($field['LStype'] == 'M' && $field['code'] != 'other' && $field['size'] > 0)
		    {
			    echo "VALUE LABELS ".$field['id']."\n";
			    echo " 1 \"".$clang->gT('Yes')."\"\n";
			    echo " 0 \"".$clang->gT('Not Selected')."\".\n";
		    }
		    if ($field['LStype'] == "P" && $field['code'] != 'other' && $field['code'] != 'comment' && $field['code'] != 'othercomment')
		    {
			    echo "VALUE LABELS ".$field['id']."\n";
			    echo " 1 \"".$clang->gT("Yes")."\"\n";
			    echo " 0 \"".$clang->gT('Not Selected')."\".\n";
		    }
		    if ($field['LStype'] == "G" && $field['size'] > 0)
		    {
			    echo "VALUE LABELS ".$field['id']."\n";
			    echo " 1 \"".$clang->gT('Female')."\"\n";
			    echo " 2 \"".$clang->gT("Male")."\".\n";
		    }
		    if ($field['LStype'] == "Y" && $field['size'] > 0)
		    {
			    echo "VALUE LABELS ".$field['id']."\n";
			    echo " 1 \"".$clang->gT('Yes')."\"\n";
			    echo " 2 \"".$clang->gT("No")."\".\n";
		    }
		    if ($field['LStype'] == "C" && $field['size'] > 0)
		    {
			    echo "VALUE LABELS ".$field['id']."\n";
			    echo " 1 \"".$clang->gT('Yes')."\"\n";
			    echo " 2 \"".$clang->gT('No')."\"\n";
			    echo " 3 \"".$clang->gT('Uncertain')."\".\n";
		    }
		    if ($field['LStype'] == "E" && $field['size'] > 0)
		    {
			    echo "VALUE LABELS ".$field['id']."\n";
			    echo " 1 \"".$clang->gT('Increase')."\"\n";
			    echo " 2 \"".$clang->gT('Same')."\"\n";
			    echo " 3 \"".$clang->gT('Decrease')."\".\n";
		    }
	    }
    } 
    exit;          
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
       
?>
