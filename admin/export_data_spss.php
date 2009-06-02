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
'X'=>Array('name'=>'Boilerplate Question','size'=>1,'SPSStype'=>'A','hide'=>1),
'D'=>Array('name'=>'Date','size'=>10,'SPSStype'=>'SDATE'),
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
':'=>Array('name'=>'Multi flexi numbers','size'=>1,'SPSStype'=>'F'),
';'=>Array('name'=>'Multi flexi text','size'=>1,'SPSStype'=>'A'),
);

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

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
                        ."<li>".$clang->gT("Edit the 4th line and complete the filename with a full path to the downloaded data file.")."</li>"
                        ."<li>".$clang->gT("Choose 'Run/All' from the menu to run the import.")."</li>"
                        ."</ol><br />"
                        .$clang->gT("Your data should be imported now.")
                        ."<table><tr><td>";
} else {
	// Get Base Language:

    $language = GetBaseLanguageFromSurveyID($surveyid);
    $clang = new limesurvey_lang($language);
    require_once ("export_data_functions.php"); 
}



if  ($subaction=='dldata') {
    header("Content-Type: application/download; charset=utf-8");
    header("Content-Disposition: attachment; filename=survey_".$surveyid."_SPSS_data_file.dat");
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: no-cache');

    sendcacheheaders();
    
    // Build array that has to be returned    
    $fields = spss_fieldmap();

	//Now get the query string with all fields to export 
    $query = spss_getquery();
    
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
            } elseif (($fields[$fieldno]['LStype'] == 'P' || $fields[$fieldno]['LStype'] == 'M') && (substr($fields[$fieldno]['code'],-7) != 'comment' && substr($fields[$fieldno]['code'],-5) != 'other')) 
            {
                if ($row[$fieldno] == 'Y')
                {
                    echo("'1'");
                } else
                {
                   echo("'0'");
                }
            } elseif (!$fields[$fieldno]['hide']) {
                $strTmp=mb_substr(strip_tags_full($row[$fieldno]), 0, $length_data);
                if (trim($strTmp) != ''){
                    $strTemp=str_replace(array("'","\n","\r"),array("''", ' ', ' '),trim($strTmp));                    
                    /*
                     * Temp quick fix for replacing decimal dots with comma's                    
                    if (my_is_numeric($strTemp)) {
                    	//$strTemp = (string) $strTemp;
                    	$strTemp = str_replace('.',',',$strTemp);
                    }
                    */
                    echo "'$strTemp'";
                }
                else
                {  
                    echo "'0'";
                }
            }
            $fieldno++;
            if ($fieldno<$num_fields && !$fields[$fieldno]['hide']) echo ',';
        }
        echo "\n";
    }
    exit;
    
}


if  ($subaction=='dlstructure') {
    header("Content-Type: application/download; charset=utf-8");
    header("Content-Disposition: attachment; filename=survey_".$surveyid."_SPSS_syntax_file.sps");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: no-cache');

    sendcacheheaders();

    // Build array that has to be returned    
    $fields = spss_fieldmap();

	//Now get the query string with all fields to export 
    $query = spss_getquery();

    $result=db_execute_num($query) or safe_die("Couldn't get results<br />$query<br />".$connect->ErrorMsg()); //Checked
    $num_results = $result->RecordCount();
    $num_fields = $result->FieldCount();

    //Now we check if we need to adjust the size of the field or the type of the field
    for ($i=0; $i < $num_results; $i++) {
	    $row = $result->FetchRow();
	    $fieldno = 0;
	    while ($fieldno < $num_fields)
	    {
	    	$strTmp=mb_substr(strip_tags_full($row[$fieldno]), 0, $length_data);
		    $len = mb_strlen($strTmp);    
		    if($len > $fields[$fieldno]['size']) $fields[$fieldno]['size'] = $len;

		    if (trim($strTmp) != ''){			    	
                    if ($fields[$fieldno]['SPSStype']=='F' && (my_is_numeric($strTmp)===false || $fields[$fieldno]['size']>16))
                    {
                        $fields[$fieldno]['SPSStype']='A';
                    }
		    }
		    $fieldno++;
	    }	    
    }

    /**
     * End of DATA print out
     *
     * Now $fields contains accurate length data, and the DATA LIST can be rendered -- then the contents of the temp file can
     * be sent to the client.
     */
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
	foreach ($fields as $field){
		if($field['SPSStype'] == 'DATETIME23.2') $field['size']='';
        if($field['SPSStype']=='F' && ($field['LStype'] == 'N' || $field['LStype']=='K')) {
            $field['size'].='.'.($field['size']-1);
        }
		if (!$field['hide']) echo "\n {$field['id']} {$field['SPSStype']}{$field['size']}";
	}
	echo ".\nCACHE.\n"
        ."EXECUTE.\n";
    
    //Create the variable labels:
    echo "*Define Variable Properties.\n";
    foreach ($fields as $field) {
    	if (!$field['hide']) echo "VARIABLE LABELS " . $field['id'] . " \"" . addslashes(strip_tags_full(mb_substr($field['VariableLabel'],0,$length_varlabel))) . "\".\n";
    }

    // Create our Value Labels!
    echo "*Define Value labels.\n";
	foreach ($fields as $field)
    {
    	$answers=array();
    	if (strpos("!LO",$field['LStype']) !== false) {
    		if (substr($field['code'],-5) == 'other' || substr($field['code'],-7) == 'comment') {
    			//We have a comment field, so free text
    		} else {
			    $query = "SELECT {$dbprefix}answers.code, {$dbprefix}answers.answer, 
			    {$dbprefix}questions.type FROM {$dbprefix}answers, {$dbprefix}questions WHERE 
			    {$dbprefix}answers.qid = '".$field["qid"]."' and {$dbprefix}questions.language='".$language."' and  {$dbprefix}answers.language='".$language."'
			    and {$dbprefix}questions.qid='".$field['qid']."' ORDER BY sortorder ASC";
			    $result=db_execute_assoc($query) or safe_die("Couldn't lookup value labels<br />$query<br />".$connect->ErrorMsg()); //Checked
			    $num_results = $result->RecordCount();
			    if ($num_results > 0)
			    {
				    $displayvaluelabel = 0;
				    # Build array that has to be returned
				    for ($i=0; $i < $num_results; $i++)
				    {
					    $row = $result->FetchRow();
						$answers[] = array('code'=>$row['code'], 'value'=>strip_tags_full(mb_substr($row["answer"],0,$length_vallabel)));
				    }
			    }
    		}
	    }
	    if (strpos("FWZWH1",$field['LStype']) !== false) {
		    $query = "SELECT {$dbprefix}questions.lid, {$dbprefix}labels.code, {$dbprefix}labels.title from 
		    {$dbprefix}questions, {$dbprefix}labels WHERE {$dbprefix}labels.language='".$language."' and
		    {$dbprefix}questions.language='".$language."' and 
		    {$dbprefix}questions.qid ='".$field["qid"]."' and {$dbprefix}questions.lid={$dbprefix}labels.lid ORDER BY sortorder ASC";
		    $result=db_execute_assoc($query) or safe_die("Couldn't get labels<br />$query<br />".$connect->ErrorMsg());   //Checked
		    $num_results = $result->RecordCount();
		    if ($num_results > 0)
		    {
			    for ($i=0; $i < $num_results; $i++)
			    {			    	
				    $row = $result->FetchRow();
					$answers[] = array('code'=>$row['code'], 'value'=>strip_tags_full(mb_substr($row["title"],0,$length_vallabel)));
			    }
		    }
	    }
		if ($field['LStype'] == ':') {
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
				$answers[] = array('code'=>$i, 'value'=>$i);
			}				
		}
	    if ($field['LStype'] == 'M' && substr($field['code'],-5) != 'other' && $field['size'] > 0)
	    {
			$answers[] = array('code'=>1, 'value'=>$clang->gT('Yes'));
		    $answers[] = array('code'=>0, 'value'=>$clang->gT('Not Selected'));
	    }
	    if ($field['LStype'] == "P" && substr($field['code'],-5) != 'other' && substr($field['code'],-7) != 'comment')
	    {
			$answers[] = array('code'=>1, 'value'=>$clang->gT('Yes'));
		    $answers[] = array('code'=>0, 'value'=>$clang->gT('Not Selected'));
	    }
	    if ($field['LStype'] == "G" && $field['size'] > 0)
	    {
	    	$answers[] = array('code'=>1, 'value'=>$clang->gT('Female'));
		    $answers[] = array('code'=>2, 'value'=>$clang->gT('Male'));
	    }
	    if ($field['LStype'] == "Y" && $field['size'] > 0)
	    {
			$answers[] = array('code'=>1, 'value'=>$clang->gT('Yes'));
		    $answers[] = array('code'=>2, 'value'=>$clang->gT('No'));
	    }
	    if ($field['LStype'] == "C" && $field['size'] > 0)
	    {
	    	$answers[] = array('code'=>1, 'value'=>$clang->gT('Yes'));
		    $answers[] = array('code'=>2, 'value'=>$clang->gT('No'));
		    $answers[] = array('code'=>3, 'value'=>$clang->gT('Uncertain'));
	    }
	    if ($field['LStype'] == "E" && $field['size'] > 0)
	    {
	    	$answers[] = array('code'=>1, 'value'=>$clang->gT('Increase'));
	    	$answers[] = array('code'=>2, 'value'=>$clang->gT('Same'));
	    	$answers[] = array('code'=>3, 'value'=>$clang->gT('Decrease'));
	    }
	    if (count($answers)>0) {
	    	//print out the value labels!
	    	echo "VALUE LABELS  {$field['id']}\n";
	    	$i=0;
	    	foreach ($answers as $answer) {
	    		$i++;
	    		if ($field['SPSStype']=="F" && my_is_numeric($answer['code'])) {
	    			$str = "{$answer['code']}";
	    		} else {
	    			$str = "\"{$answer['code']}\"";
	    		}
	    		if ($i < count($answers)) {
	    			echo " $str \"{$answer['value']}\"\n";
	    		} else {
	    			echo " $str \"{$answer['value']}\".\n";
	    		}
	    	}
	    }
    } 
    
    //Rename the Variables (in case somethings goes wrong, we still have the OLD values
	foreach ($fields as $field){
		if (isset($field['sql_name']) && !$field['hide']) {
			$ftitle = $field['title'];
			if (!preg_match ("/^([a-z]|[A-Z])+.*$/", $ftitle)) {
				$ftitle = "q_" . $ftitle;
			}
			$ftitle = str_replace(array(" ","-",":",";","!","/","\\"), array("_","_hyph_","_dd_","_dc_","_excl_","_fs_","_bs_"), $ftitle);
			if (!$field['hide']) {
				if ($ftitle != $field['title']) echo "* Variable name was incorrect and was changed from {$field['title']} to $ftitle .\n";
				echo "RENAME VARIABLE ( " . $field['id'] . " = " . $ftitle . " ).\n";
			}
		}
	}
    exit;          
}
?>