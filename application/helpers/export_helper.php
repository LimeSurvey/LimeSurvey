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
 * $Id: export_data_functions.php 10193 2011-06-05 12:20:37Z c_schmitz $
 */

/**
 * Strips html tags and replaces new lines
 *
 * @param $string
 * @return $string
 */
function strip_tags_full($string) {
    $string=html_entity_decode($string, ENT_QUOTES, "UTF-8");
    //combining these into one mb_ereg_replace call ought to speed things up
    //$string = str_replace(array("\r\n","\r","\n",'-oth-'), '', $string);
    //The backslashes must be escaped twice, once for php, and again for the regexp
    //$string = str_replace("'|\\\\'", "&apos;", $string);
    return FlattenText($string);
}

/**
 * Returns true if passed $value is numeric
 *
 * @param $value
 * @return bool
 */
function my_is_numeric($value)  {
    if (empty($value)) return true;
    $eng_or_world = preg_match
    ('/^[+-]?'. // start marker and sign prefix
  '(((([0-9]+)|([0-9]{1,4}(,[0-9]{3,4})+)))?(\\.[0-9])?([0-9]*)|'. // american
  '((([0-9]+)|([0-9]{1,4}(\\.[0-9]{3,4})+)))?(,[0-9])?([0-9]*))'. // world
  '(e[0-9]+)?'. // exponent
  '$/', // end marker
    $value) == 1;
    return ($eng_or_world);
}

function spss_export_data ($na = null) {
    global $length_data;

    // Build array that has to be returned
    $fields = spss_fieldmap();

    //Now get the query string with all fields to export
    $query = spss_getquery();

    $result=db_execute_assoc($query) or safe_die("Couldn't get results<br />$query<br />".$connect->ErrorMsg()); //Checked
    $num_fields =  count($result->row_array());

    //This shouldn't occur, but just to be safe:
    if (count($fields)<>$num_fields) safe_die("Database inconsistency error");

    foreach ($result->result_array() as $row) {
    	$row = array_change_key_case($row,CASE_UPPER);
        //$row = $result->GetRowAssoc(true);	//Get assoc array, use uppercase
        reset($fields);	//Jump to the first element in the field array
        $i = 1;
        foreach ($fields as $field)
        {
            $fieldno = strtoupper($field['sql_name']);
            if ($field['SPSStype']=='DATETIME23.2'){
                #convert mysql  datestamp (yyyy-mm-dd hh:mm:ss) to SPSS datetime (dd-mmm-yyyy hh:mm:ss) format
                if (isset($row[$fieldno]))
                {
                    list( $year, $month, $day, $hour, $minute, $second ) = preg_split( '([^0-9])', $row[$fieldno] );
                    if ($year != '' && (int)$year >= 1970)
                    {
                        echo "'".date('d-m-Y H:i:s', mktime( $hour, $minute, $second, $month, $day, $year ) )."'";
                    } else
                    {
                        echo ($na);
                    }
                }  else
                {
                    echo ($na);
                }
            } else if ($field['LStype'] == 'Y')
            {
                if ($row[$fieldno] == 'Y')    // Yes/No Question Type
                {
                    echo( "'1'");
                } else if ($row[$fieldno] == 'N'){
                    echo( "'2'");
                } else {
                    echo($na);
                }
            } else if ($field['LStype'] == 'G')    //Gender
            {
                if ($row[$fieldno] == 'F')
                {
                    echo( "'1'");
                } else if ($row[$fieldno] == 'M'){
                    echo( "'2'");
                } else {
                    echo($na);
                }
            } else if ($field['LStype'] == 'C')    //Yes/No/Uncertain
            {
                if ($row[$fieldno] == 'Y')
                {
                    echo( "'1'");
                } else if ($row[$fieldno] == 'N'){
                    echo( "'2'");
                } else if ($row[$fieldno] == 'U'){
                    echo( "'3'");
                } else {
                    echo($na);
                }
            } else if ($field['LStype'] == 'E')     //Increase / Same / Decrease
            {
                if ($row[$fieldno] == 'I')
                {
                    echo( "'1'");
                } else if ($row[$fieldno] == 'S'){
                    echo( "'2'");
                } else if ($row[$fieldno] == 'D'){
                    echo( "'3'");
                } else {
                    echo($na);
                }
            } elseif (($field['LStype'] == 'P' || $field['LStype'] == 'M') && (substr($field['code'],-7) != 'comment' && substr($field['code'],-5) != 'other'))
            {
                if ($row[$fieldno] == 'Y')
                {
                    echo("'1'");
                } else
                {
                    echo("'0'");
                }
            } elseif (!$field['hide']) {
            	$strTmp=mb_substr(strip_tags_full($row[$fieldno]), 0, $length_data);
                if (trim($strTmp) != ''){
                    $strTemp=str_replace(array("'","\n","\r"),array("''",' ',' '),trim($strTmp));
                    /*
                     * Temp quick fix for replacing decimal dots with comma's
                     if (my_is_numeric($strTemp)) {
                     $strTemp = str_replace('.',',',$strTemp);
                     }
                     */
                    echo "'$strTemp'";
                }
                else
                {
                    echo $na;
                }
            }
            if ($i<$num_fields && !$field['hide']) echo ',';
            $i++;
        }
        echo "\n";
    }
}

/**
 * Check it the gives field has a labelset and return it as an array if true
 *
 * @param $field array field from spss_fieldmap
 * @return array or false
 */
function spss_getvalues ($field = array(), $qidattributes = null ) {
    global $surveyid, $dbprefix, $connect, $language, $length_vallabel;
	$clang =& get_instance()->limesurvey_lang;

    if (!isset($field['LStype']) || empty($field['LStype'])) return false;
    $answers=array();
    if (strpos("!LORFWZWH1",$field['LStype']) !== false) {
        if (substr($field['code'],-5) == 'other' || substr($field['code'],-7) == 'comment') {
            //We have a comment field, so free text
        } else {
            $query = "SELECT {$dbprefix}answers.code, {$dbprefix}answers.answer,
			{$dbprefix}questions.type FROM {$dbprefix}answers, {$dbprefix}questions WHERE";

            if (isset($field['scale_id'])) $query .= " {$dbprefix}answers.scale_id = " . (int) $field['scale_id'] . " AND";

            $query .= " {$dbprefix}answers.qid = '".$field["qid"]."' and {$dbprefix}questions.language='".$language."' and  {$dbprefix}answers.language='".$language."'
			    and {$dbprefix}questions.qid='".$field['qid']."' ORDER BY sortorder ASC";
            $result=db_execute_assoc($query) or safe_die("Couldn't lookup value labels<br />$query<br />".$connect->ErrorMsg()); //Checked
            $num_results = $result->num_rows();
            if ($num_results > 0)
            {
                $displayvaluelabel = 0;
                # Build array that has to be returned
                for ($i=0; $i < $num_results; $i++)
                {
                    $row = $result->row_array();
                    $answers[] = array('code'=>$row['code'], 'value'=>mb_substr(strip_tags_full($row["answer"]),0,$length_vallabel));
                }
            }
        }
    } elseif ($field['LStype'] == ':') {
        $displayvaluelabel = 0;
        //Get the labels that could apply!
        if (is_null($qidattributes)) $qidattributes=getQuestionAttributeValues($field["qid"], $field['LStype']);
        if (trim($qidattributes['multiflexible_max'])!='') {
            $maxvalue=$qidattributes['multiflexible_max'];
        } else {
            $maxvalue=10;
        }
        if (trim($qidattributes['multiflexible_min'])!='')
        {
            $minvalue=$qidattributes['multiflexible_min'];
        } else {
            $minvalue=1;
        }
        if (trim($qidattributes['multiflexible_step'])!='')
        {
            $stepvalue=$qidattributes['multiflexible_step'];
        } else {
            $stepvalue=1;
        }
        if ($qidattributes['multiflexible_checkbox']!=0) {
            $minvalue=0;
            $maxvalue=1;
            $stepvalue=1;
        }
        for ($i=$minvalue; $i<=$maxvalue; $i+=$stepvalue)
        {
            $answers[] = array('code'=>$i, 'value'=>$i);
        }
    } elseif ($field['LStype'] == 'M' && substr($field['code'],-5) != 'other' && $field['size'] > 0)
    {
        $answers[] = array('code'=>1, 'value'=>$clang->gT('Yes'));
        $answers[] = array('code'=>0, 'value'=>$clang->gT('Not Selected'));
    } elseif ($field['LStype'] == "P" && substr($field['code'],-5) != 'other' && substr($field['code'],-7) != 'comment')
    {
        $answers[] = array('code'=>1, 'value'=>$clang->gT('Yes'));
        $answers[] = array('code'=>0, 'value'=>$clang->gT('Not Selected'));
    } elseif ($field['LStype'] == "G" && $field['size'] > 0)
    {
        $answers[] = array('code'=>1, 'value'=>$clang->gT('Female'));
        $answers[] = array('code'=>2, 'value'=>$clang->gT('Male'));
    } elseif ($field['LStype'] == "Y" && $field['size'] > 0)
    {
        $answers[] = array('code'=>1, 'value'=>$clang->gT('Yes'));
        $answers[] = array('code'=>2, 'value'=>$clang->gT('No'));
    } elseif ($field['LStype'] == "C" && $field['size'] > 0)
    {
        $answers[] = array('code'=>1, 'value'=>$clang->gT('Yes'));
        $answers[] = array('code'=>2, 'value'=>$clang->gT('No'));
        $answers[] = array('code'=>3, 'value'=>$clang->gT('Uncertain'));
    } elseif ($field['LStype'] == "E" && $field['size'] > 0)
    {
        $answers[] = array('code'=>1, 'value'=>$clang->gT('Increase'));
        $answers[] = array('code'=>2, 'value'=>$clang->gT('Same'));
        $answers[] = array('code'=>3, 'value'=>$clang->gT('Decrease'));
    }
    if (count($answers)>0) {
        //check the max width of the answers
        $size = 0;
        $spsstype = $field['SPSStype'];
        foreach ($answers as $answer) {
            $len = mb_strlen($answer['code']);
            if ($len>$size) $size = $len;
            if ($spsstype=='F' && (my_is_numeric($answer['code'])===false || $size>16)) $spsstype='A';
        }
        $answers['SPSStype'] = $spsstype;
        $answers['size'] = $size;
        return $answers;
    } else {
        return false;
    }
}

/**
 * Creates a fieldmap with all information necessary to output the fields
 *
 * @param $prefix string prefix for the variable ID
 * @return array
 */
function spss_fieldmap($prefix = 'V') {
    global $surveyid, $dbprefix, $typeMap, $connect, $clang;
    global $surveyprivate, $tokensexist, $language;

	$CI =& get_instance();
    $fieldmap = createFieldMap($surveyid, 'full');		//Create a FULL fieldmap

    #See if tokens are being used
    $tokensexist = tableExists('tokens_'.$surveyid);

    #Lookup the names of the attributes
    $query="SELECT sid, anonymized, language FROM {$dbprefix}surveys WHERE sid=$surveyid";
    $result=db_execute_assoc($query) or safe_die("Couldn't count fields<br />$query<br />".$connect->ErrorMsg());  //Checked
    $num_results = $result->num_rows();
    $num_fields = $num_results;
    # Build array that has to be returned
    for ($i=0; $i < $num_results; $i++) {
        $row = $result->row_array();
        $surveyprivate=$row['anonymized'];
        $language=$row['language'];
    }

    $fieldno=0;

    $fields=array();
    if (isset($tokensexist) && $tokensexist == true && $surveyprivate == 'N') {
        $tokenattributes=GetTokenFieldsAndNames($surveyid,false);
        foreach ($tokenattributes as $attributefield=>$attributedescription)
        {
            //Drop the token field, since it is in the survey too
            if($attributefield!='token') {
                $fieldno++;
                $fields[] = array('id'=>"$prefix$fieldno",'name'=>mb_substr($attributefield, 0, 8),
			    'qid'=>0,'code'=>'','SPSStype'=>'A','LStype'=>'Undef',
			    'VariableLabel'=>$attributedescription,'sql_name'=>$attributefield,'size'=>'100',
			    'title'=>$attributefield,'hide'=>0, 'scale'=>'');
            }
        }
    }

    $tempArray = array();
    $fieldnames = array_values($CI->db->list_fields("survey_$surveyid"));
    $num_results = count($fieldnames);
    $num_fields = $num_results;
    $diff = 0;
    $noQID = Array('id', 'token', 'datestamp', 'submitdate', 'startdate', 'startlanguage', 'ipaddr', 'refurl', 'lastpage');
    # Build array that has to be returned
    for ($i=0; $i < $num_results; $i++) {
        #Conditions for SPSS fields:
        # - Length may not be longer than 8 characters
        # - Name may not begin with a digit
        $fieldname = $fieldnames[$i];
        $fieldtype = '';
        $ftype='';
        $val_size = 1;
        $hide = 0;
        $export_scale = '';
        $code='';
        $scale_id = null;
        $aQuestionAttribs=array();

        #Determine field type
        if ($fieldname=='submitdate' || $fieldname=='startdate' || $fieldname == 'datestamp') {
            $fieldtype = 'DATETIME23.2';
        } elseif ($fieldname=='startlanguage') {
            $fieldtype = 'A';
            $val_size = 19;
        } elseif ($fieldname=='token') {
            $fieldtype = 'A';
            $val_size = 16;
        } elseif ($fieldname=='id') {
            $fieldtype = 'F';
            $val_size = 7; //Arbitrarilty restrict to 9,999,999 (7 digits) responses/survey
        } elseif ($fieldname == 'ipaddr') {
            $fieldtype = 'A';
            $val_size = 15;
        } elseif ($fieldname == 'refurl') {
            $fieldtype = 'A';
            $val_size = 255;
        } elseif ($fieldname == 'lastpage') {
        	$hide = 1;
        }

        #Get qid (question id)
        if (in_array($fieldname, $noQID) || substr($fieldname,0,10)=='attribute_'){
            $qid = 0;
            $varlabel = $fieldname;
            $ftitle = $fieldname;
        } else{
            //GET FIELD DATA
            if (!isset($fieldmap[$fieldname])) {
                //Field in database but no longer in survey... how is this possible?
                //@TODO: think of a fix.
                $fielddata = array();
                $qid=0;
                $varlabel = $fieldname;
                $ftitle = $fieldname;
                $fieldtype = "F";
                $val_size = 1;
            } else {
                $fielddata=$fieldmap[$fieldname];
                $qid=$fielddata['qid'];
                $ftype=$fielddata['type'];
                $fsid=$fielddata['sid'];
                $fgid=$fielddata['gid'];
                $code=mb_substr($fielddata['fieldname'],strlen($fsid."X".$fgid."X".$qid));
                $varlabel=$fielddata['question'];
                if (isset($fielddata['scale'])) $varlabel = "[{$fielddata['scale']}] ". $varlabel;
                if (isset($fielddata['subquestion'])) $varlabel = "[{$fielddata['subquestion']}] ". $varlabel;
                if (isset($fielddata['subquestion2'])) $varlabel = "[{$fielddata['subquestion2']}] ". $varlabel;
                if (isset($fielddata['subquestion1'])) $varlabel = "[{$fielddata['subquestion1']}] ". $varlabel;
                $ftitle=$fielddata['title'];
                if (!is_null($code) && $code<>"" ) $ftitle .= "_$code";
                if (isset($typeMap[$ftype]['size'])) $val_size = $typeMap[$ftype]['size'];
                if (isset($fielddata['scale_id'])) $scale_id = $fielddata['scale_id'];
                if($fieldtype == '') $fieldtype = $typeMap[$ftype]['SPSStype'];
                if (isset($typeMap[$ftype]['hide'])) {
                    $hide = $typeMap[$ftype]['hide'];
                    $diff++;
                }
                //Get default scale for this type
                if (isset($typeMap[$ftype]['Scale'])) $export_scale = $typeMap[$ftype]['Scale'];
                //But allow override
                $aQuestionAttribs = getQuestionAttributeValues($qid,$ftype);
                if (isset($aQuestionAttribs['scale_export'])) $export_scale = $aQuestionAttribs['scale_export'];
            }

        }
        $fieldno++;
        $fid = $fieldno - $diff;
        $lsLong = isset($typeMap[$ftype]["name"])?$typeMap[$ftype]["name"]:$ftype;
        $tempArray = array('id'=>"$prefix$fid",'name'=>mb_substr($fieldname, 0, 8),
		    'qid'=>$qid,'code'=>$code,'SPSStype'=>$fieldtype,'LStype'=>$ftype,"LSlong"=>$lsLong,
		    'ValueLabels'=>'','VariableLabel'=>$varlabel,"sql_name"=>$fieldname,"size"=>$val_size,
		    'title'=>$ftitle,'hide'=>$hide,'scale'=>$export_scale, 'scale_id'=>$scale_id);
        //Now check if we have to retrieve value labels
        $answers = spss_getvalues($tempArray, $aQuestionAttribs);
        if (is_array($answers)) {
            //Ok we have answers
            if (isset($answers['size'])) {
                $tempArray['size'] = $answers['size'];
                unset($answers['size']);
            }
            if (isset($answers['SPSStype'])) {
                $tempArray['SPSStype'] = $answers['SPSStype'];
                unset($answers['SPSStype']);
            }
            $tempArray['answers'] = $answers;
        }
        $fields[] = $tempArray;
    }
    return $fields;
}

/**
 * Creates a query string with all fields for the export
 *
 * @return string
 */
function spss_getquery() {
    global $surveyprivate, $dbprefix, $surveyid, $tokensexist;

    #See if tokens are being used
    if (isset($tokensexist) && $tokensexist == true && $surveyprivate == 'N') {
        $query="SELECT ";
        $tokenattributes=GetTokenFieldsAndNames($surveyid,false);
        foreach ($tokenattributes as $attributefield=>$attributedescription) {
            //Drop the token field, since it is in the survey too
            if($attributefield!='token') {
                $query .= "{$dbprefix}tokens_$surveyid.$attributefield, ";
            }
        }
        $query .= "{$dbprefix}survey_$surveyid.*
	    FROM {$dbprefix}survey_$surveyid
	    LEFT JOIN {$dbprefix}tokens_$surveyid ON {$dbprefix}survey_$surveyid.token = {$dbprefix}tokens_$surveyid.token";
    } else {
        $query = "SELECT *
	    FROM {$dbprefix}survey_$surveyid";
    }
    switch (incompleteAnsFilterstate()) {
        case 'inc':
            //Inclomplete answers only
            $query .= ' WHERE submitdate is null ';
            break;
        case 'filter':
            //Inclomplete answers only
            $query .= ' WHERE submitdate is not null ';
            break;
    }
    return $query;
}

/**
* BuildXMLFromQuery() creates a datadump of a table in XML using XMLWriter
*
* @param mixed $xmlwriter  The existing XMLWriter object
* @param mixed $Query  The table query to build from
* @param mixed $tagname  If the XML tag of the resulting question should be named differently than the table name set it here
* @param array $excludes array of columnames not to include in export
*/
function BuildXMLFromQuery($xmlwriter, $Query, $tagname='', $excludes = array())
{
    $iChunkSize=3000; // This works even for very large result sets and leaves a minimal memory footprint
	$CI =& get_instance();
    $dbprefix = $CI->db->dbprefix;
    $CI->load->helper('database');
    preg_match('/\bfrom\b\s*(\w+)/i', $Query, $MatchResults);
    if ($tagname!='')
    {
        $TableName=$tagname;
    }
    else
    {
        $TableName = $MatchResults[1];
        $TableName = substr($TableName, strlen($dbprefix), strlen($TableName));
    }



    // Read table in smaller chunks
    $iStart=0;
    do
    {
   //     debugbreak();
        $QueryResult = db_select_limit_assoc($Query,$iChunkSize,$iStart) or safe_die ("ERROR: $QueryResult<br />".$connect->ErrorMsg()); //safe
        if ($iStart==0 && $QueryResult->num_rows()>0)
        {
            $exclude = array_flip($excludes);    //Flip key/value in array for faster checks
            $xmlwriter->startElement($TableName);
            $xmlwriter->startElement('fields');
            $aColumninfo = $QueryResult->field_data();
            foreach ($aColumninfo as $fieldname)
            {
                if (!isset($exclude[$fieldname->name])) $xmlwriter->writeElement('fieldname',$fieldname->name);
            }
            $xmlwriter->endElement(); // close columns
            $xmlwriter->startElement('rows');
        }
        foreach($QueryResult->result_array() as $Row)
        {
            $xmlwriter->startElement('row');
            foreach ($Row as $Key=>$Value)
            {
                if (!isset($exclude[$Key])) {
                    if(!(is_null($Value))) // If the $value is null don't output an element at all
                    {
                        if (is_numeric($Key[0])) $Key='_'.$Key; // mask invalid element names with an underscore
                        $Key=str_replace('#','-',$Key);
                        if (!$xmlwriter->startElement($Key)) safe_die('Invalid elemnt key: '.$Key);
                            // Remove invalid XML characters
                        if ($Value!='') $xmlwriter->writeCData(preg_replace('/[^\x9\xA\xD\x20-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u','',$Value));
                        $xmlwriter->endElement();
                    }
                }
            }
            $xmlwriter->endElement(); // close row
        }
        $iStart=$iStart+$iChunkSize;
    } while ($QueryResult->num_rows()==$iChunkSize);
    if ($QueryResult->num_rows()>0)
    {
        $QueryResult->free_result();
        $xmlwriter->endElement(); // close rows
        $xmlwriter->endElement(); // close tablename
    }
}

/**
 * from export_structure_xml.php
 */
function survey_getXMLStructure($surveyid, $xmlwriter, $exclude=array())
{

	$CI =& get_instance();

    $sdump = "";

	$dbprefix = $CI->db->dbprefix;

    if ((!isset($exclude) && $exclude['answers'] !== true) || empty($exclude))
    {
        //Answers table
        $aquery = "SELECT {$dbprefix}answers.*
           FROM {$dbprefix}answers, {$dbprefix}questions
		   WHERE {$dbprefix}answers.language={$dbprefix}questions.language
		   AND {$dbprefix}answers.qid={$dbprefix}questions.qid
		   AND {$dbprefix}questions.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$aquery);
    }

    // Assessments
    $query = "SELECT {$dbprefix}assessments.*
          FROM {$dbprefix}assessments
          WHERE {$dbprefix}assessments.sid=$surveyid";
    BuildXMLFromQuery($xmlwriter,$query);

    if ((!isset($exclude) && $exclude['conditions'] !== true) || empty($exclude))
    {
        //Conditions table
        $cquery = "SELECT DISTINCT {$dbprefix}conditions.*
           FROM {$dbprefix}conditions, {$dbprefix}questions
		   WHERE {$dbprefix}conditions.qid={$dbprefix}questions.qid
		   AND {$dbprefix}questions.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$cquery);
    }

    //Default values
    $query = "SELECT {$dbprefix}defaultvalues.*
          FROM {$dbprefix}defaultvalues JOIN {$dbprefix}questions ON {$dbprefix}questions.qid = {$dbprefix}defaultvalues.qid AND {$dbprefix}questions.sid=$surveyid AND {$dbprefix}questions.language={$dbprefix}defaultvalues.language ";

    BuildXMLFromQuery($xmlwriter,$query);

    // Groups
    $gquery = "SELECT *
           FROM {$dbprefix}groups
           WHERE sid=$surveyid
           ORDER BY gid";
    BuildXMLFromQuery($xmlwriter,$gquery);

    //Questions
    $qquery = "SELECT *
           FROM {$dbprefix}questions
           WHERE sid=$surveyid and parent_qid=0
           ORDER BY qid";
    BuildXMLFromQuery($xmlwriter,$qquery);

    //Subquestions
    $qquery = "SELECT *
           FROM {$dbprefix}questions
           WHERE sid=$surveyid and parent_qid>0
           ORDER BY qid";
    BuildXMLFromQuery($xmlwriter,$qquery,'subquestions');

    //Question attributes
    $sBaseLanguage=GetBaseLanguageFromSurveyID($surveyid);
    if ($CI->db->platform() == 'odbc_mssql' || $CI->db->platform() == 'odbtp' || $CI->db->platform() == 'mssql_n' || $CI->db->platform() =='mssqlnative')
    {
        $query="SELECT qa.qid, qa.attribute, cast(qa.value as varchar(4000)) as value
          FROM {$dbprefix}question_attributes qa JOIN {$dbprefix}questions  q ON q.qid = qa.qid AND q.sid={$surveyid}
          where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute,  cast(qa.value as varchar(4000))";
    }
    else {
        $query="SELECT qa.qid, qa.attribute, qa.value
          FROM {$dbprefix}question_attributes qa JOIN {$dbprefix}questions  q ON q.qid = qa.qid AND q.sid={$surveyid}
          where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute, qa.value";
    }

    BuildXMLFromQuery($xmlwriter,$query,'question_attributes');

    if ((!isset($exclude) && $exclude['quotas'] !== true) || empty($exclude))
    {
        //Quota
        $query = "SELECT {$dbprefix}quota.*
          FROM {$dbprefix}quota
		  WHERE {$dbprefix}quota.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$query);

        //1Quota members
        $query = "SELECT {$dbprefix}quota_members.*
          FROM {$dbprefix}quota_members
		  WHERE {$dbprefix}quota_members.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$query);

        //Quota languagesettings
        $query = "SELECT {$dbprefix}quota_languagesettings.*
          FROM {$dbprefix}quota_languagesettings, {$dbprefix}quota
		  WHERE {$dbprefix}quota.id = {$dbprefix}quota_languagesettings.quotals_quota_id
		  AND {$dbprefix}quota.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$query);
    }

    // Surveys
    $squery = "SELECT *
           FROM {$dbprefix}surveys
           WHERE sid=$surveyid";
    //Exclude some fields from the export
    BuildXMLFromQuery($xmlwriter,$squery,'',array('owner_id','active','datecreated'));

    // Survey language settings
    $slsquery = "SELECT *
             FROM {$dbprefix}surveys_languagesettings
             WHERE surveyls_survey_id=$surveyid";
    BuildXMLFromQuery($xmlwriter,$slsquery);

    // Survey url parameters
    $slsquery = "SELECT *
             FROM {$dbprefix}survey_url_parameters
             WHERE sid={$surveyid}";
    BuildXMLFromQuery($xmlwriter,$slsquery);

}

/**
 * from export_structure_xml.php
 */
function survey_getXMLData($surveyid, $exclude = array())
{
    $CI =& get_instance();
    $xml = getXMLWriter();
    $xml->openMemory();
    $xml->setIndent(true);
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('document');
    $xml->writeElement('LimeSurveyDocType','Survey');
    $xml->writeElement('DBVersion',$CI->config->item("dbversionnumber"));
    $xml->startElement('languages');
    $surveylanguages=GetAdditionalLanguagesFromSurveyID($surveyid);
    $surveylanguages[]=GetBaseLanguageFromSurveyID($surveyid);
    foreach ($surveylanguages as $surveylanguage)
    {
        $xml->writeElement('language',$surveylanguage);
    }
    $xml->endElement();
    survey_getXMLStructure($surveyid, $xml,$exclude);
    $xml->endElement(); // close columns
    $xml->endDocument();
    return $xml->outputMemory(true);
}

/**
* Exports a single table to XML
*
* @param inetger $iSurveyID The survey ID
* @param string $sTableName The database table name of the table to be export
* @param string $sDocType What doctype should be written
* @param string $sXMLTableName Name of the tag table name in the XML file
* @return object XMLWriter object
*/
function getXMLDataSingleTable($iSurveyID, $sTableName, $sDocType, $sXMLTableTagName='', $sFileName='', $bSetIndent=true)
{
    $CI =& get_instance();
    $xml = getXMLWriter();
    if ($sFileName=='')
    {
        $xml->openMemory();
    }
    else
    {
        $bOK=$xml->openURI($sFileName);
    }
    $xml->setIndent($bSetIndent);
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('document');
    $xml->writeElement('LimeSurveyDocType',$sDocType);
    $xml->writeElement('DBVersion',$CI->config->item("dbversionnumber"));
    $xml->startElement('languages');
    $aSurveyLanguages=GetAdditionalLanguagesFromSurveyID($iSurveyID);
    $aSurveyLanguages[]=GetBaseLanguageFromSurveyID($iSurveyID);
    foreach ($aSurveyLanguages as $sSurveyLanguage)
    {
        $xml->writeElement('language',$sSurveyLanguage);
    }
    $xml->endElement();
    $aquery = "SELECT * FROM {$CI->db->dbprefix}$sTableName";
    BuildXMLFromQuery($xml,$aquery, $sXMLTableTagName);
    $xml->endElement(); // close columns
    $xml->endDocument();
    if ($sFileName='')
    {
        return $xml->outputMemory(true);
    }
    else
    {
        return $bOK;
    }
}


/**
 * from export_structure_quexml.php
 */
function quexml_cleanup($string)
{
	return trim(strip_tags(str_ireplace("<br />","\n",$string)));
}

/**
 * from export_structure_quexml.php
 */
function quexml_create_free($f,$len,$lab="")
{
	global $dom;
	$free = $dom->create_element("free");

	$format = $dom->create_element("format");
	$format->set_content(quexml_cleanup($f));

	$length = $dom->create_element("length");
	$length->set_content(quexml_cleanup($len));

	$label = $dom->create_element("label");
	$label->set_content(quexml_cleanup($lab));

	$free->append_child($format);
	$free->append_child($length);
	$free->append_child($label);


	return $free;
}

/**
 * from export_structure_quexml.php
 */
function quexml_fixed_array($array)
{
	global $dom;
	$fixed = $dom->create_element("fixed");

	foreach ($array as $key => $v)
	{
		$category = $dom->create_element("category");

		$label = $dom->create_element("label");
		$label->set_content(quexml_cleanup("$key"));

		$value= $dom->create_element("value");
		$value->set_content(quexml_cleanup("$v"));

		$category->append_child($label);
		$category->append_child($value);

		$fixed->append_child($category);
	}


	return $fixed;
}

/**
 * Calculate if this item should have a quexml_skipto element attached to it
 *
 * from export_structure_quexml.php
 *
 * @param mixed $qid
 * @param mixed $value
 *
 * @return bool|string Text of item to skip to otherwise false if nothing to skip to
 * @author Adam Zammit <adam.zammit@acspri.org.au>
 * @since  2010-10-28
 */
function quexml_skipto($qid,$value,$cfieldname = "")
{
	global $connect ;
	$CI =& get_instance();
	$dbprefix = $CI->db->dbprefix;
	global $surveyid ;
        global $quexmllang;
	$qlang = new limesurvey_lang(array($quexmllang));

	$zeros = $CI->db->escape("0000000000");

	$Query = "SELECT q.*," . concat("RIGHT(" . concat($zeros,'g.gid') . ",10)","RIGHT(". concat($zeros,'q.question_order') .",10)") ." as globalorder
                  FROM {$dbprefix}questions as q, {$dbprefix}questions as q2, {$dbprefix}groups as g, {$dbprefix}groups as g2
                  WHERE q.parent_qid = 0
                  AND q2.parent_qid = 0
                  AND q.sid=$surveyid
                  AND q2.sid=$surveyid
                  AND q2.qid = $qid
                  AND g2.gid =q2.gid
                  AND g.gid = q.gid
                  AND " . concat("RIGHT(" . concat($zeros,'g.gid') . ",10)","RIGHT(". concat($zeros,'q.question_order') .",10)") ." > " . concat("RIGHT(" . concat($zeros,'g2.gid') . ",10)","RIGHT(". concat($zeros,'q2.question_order') .",10)") ."
                  ORDER BY globalorder";

	$QueryResult = db_execute_assoc($Query);

	$nextqid="";
	$nextorder="";

	$Row = $QueryResult->row_array();
	if ($Row)
	{
		$nextqid = $Row['qid'];
		$nextorder = $Row['globalorder'];
	}
	else
		return false;


	$Query = "SELECT q.*
		FROM {$dbprefix}questions as q
		JOIN {$dbprefix}groups as g ON (g.gid = q.gid)
		LEFT JOIN {$dbprefix}conditions as c ON (c.cqid = '$qid' AND c.qid = q.qid AND c.method LIKE '==' AND c.value NOT LIKE '$value' $cfieldname)
		WHERE q.sid = $surveyid
		AND q.parent_qid = 0
		AND " . concat("RIGHT(" . concat($zeros,'g.gid') . ",10)","RIGHT(". concat($zeros,'q.question_order') .",10)") ." >= $nextorder
		AND c.cqid IS NULL
		ORDER BY  " . concat("RIGHT(" . concat($zeros,'g.gid') . ",10)","RIGHT(". concat($zeros,'q.question_order') .",10)");


	$QueryResult = db_execute_assoc($Query);

	$Row = $QueryResult->row_array();
	if ($Row)
	{
		if ($nextqid == $Row['qid'])
			return false;
		else
			return $Row['title'];
	}
	else
		return $qlang->gT("End");

}

/**
 * from export_structure_quexml.php
 */
function quexml_create_fixed($qid,$rotate=false,$labels=true,$scale=0,$other=false)
{
	global $dom;
	$CI =& get_instance();
	$dbprefix = $CI->db->dbprefix;
	global $quexmllang;
	$qlang = new limesurvey_lang(array($quexmllang));

	if ($labels)
		$Query = "SELECT * FROM {$dbprefix}labels WHERE lid = $labels  AND language='$quexmllang' ORDER BY sortorder ASC";
	else
		$Query = "SELECT code,answer as title,sortorder FROM {$dbprefix}answers WHERE qid = $qid AND scale_id = $scale  AND language='$quexmllang' ORDER BY sortorder ASC";

	$QueryResult = db_execute_assoc($Query);

	$fixed = $dom->create_element("fixed");

	$nextcode = "";

	foreach($QueryResult->result_array() as $Row)
	{
		$category = $dom->create_element("category");

		$label = $dom->create_element("label");
		$label->set_content(quexml_cleanup($Row['title']));

		$value= $dom->create_element("value");
		$value->set_content(quexml_cleanup($Row['code']));

		$category->append_child($label);
		$category->append_child($value);

		$st = quexml_skipto($qid,$Row['code']);
		if ($st !== false)
		{
			$quexml_skipto = $dom->create_element("quexml_skipto");
			$quexml_skipto->set_content($st);
			$category->append_child($quexml_skipto);
		}


		$fixed->append_child($category);
		$nextcode = $Row['code'];
	}

	if ($other)
	{
		$category = $dom->create_element("category");

		$label = $dom->create_element("label");
		$label->set_content(quexml_get_lengthth($qid,"other_replace_text",$qlang->gT("Other")));

		$value= $dom->create_element("value");

		//Get next code
		if (is_numeric($nextcode))
			$nextcode++;
		else if (is_string($nextcode))
			$nextcode = chr(ord($nextcode) + 1);

		$value->set_content($nextcode);

		$category->append_child($label);
		$category->append_child($value);

		$contingentQuestion = $dom->create_element("contingentQuestion");
		$length = $dom->create_element("length");
		$text = $dom->create_element("text");

		$text->set_content(quexml_get_lengthth($qid,"other_replace_text",$qlang->gT("Other")));
		$length->set_content(24);
		$contingentQuestion->append_child($text);
		$contingentQuestion->append_child($length);

		$category->append_child($contingentQuestion);

		$fixed->append_child($category);
	}

	if ($rotate) $fixed->set_attribute("rotate","true");

	return $fixed;
}

/**
 * from export_structure_quexml.php
 */
function quexml_get_lengthth($qid,$attribute,$default)
{
	global $dom;
	$CI =& get_instance();
	$dbprefix = $CI->db->dbprefix;

	$Query = "SELECT value FROM {$dbprefix}question_attributes WHERE qid = $qid AND attribute = '$attribute'";
	//$QueryResult = mysql_query($Query) or die ("ERROR: $QueryResult<br />".mysql_error());
	$QueryResult = db_execute_assoc($Query);

	$Row = $QueryResult->row_array();
	if ($Row && !empty($Row['value']))
		return $Row['value'];
	else
		return $default;

}

/**
 * from export_structure_quexml.php
 */
function quexml_create_multi(&$question,$qid,$varname,$scale_id = false,$free = false,$other = false)
{
	global $dom;
	$CI =& get_instance();
	$dbprefix = $CI->db->dbprefix;
	global $quexmllang ;
	global $surveyid;
	$qlang = new limesurvey_lang(array($quexmllang));


	$Query = "SELECT * FROM {$dbprefix}questions WHERE parent_qid = $qid  AND language='$quexmllang' ";
	if ($scale_id != false) $Query .= " AND scale_id = $scale_id ";
	$Query .= " ORDER BY question_order ASC";
	//$QueryResult = mysql_query($Query) or die ("ERROR: $QueryResult<br />".mysql_error());
	$QueryResult = db_execute_assoc($Query);

	$nextcode = "";

	foreach($QueryResult->result_array() as $Row)
	{
		$response = $dom->create_element("response");
		if ($free == false)
		{
			$fixed = $dom->create_element("fixed");
			$category = $dom->create_element("category");

			$label = $dom->create_element("label");
			$label->set_content(quexml_cleanup($Row['question']));

			$value= $dom->create_element("value");
			//$value->set_content(quexml_cleanup($Row['title']));
			$value->set_content("1");
			$nextcode = $Row['title'];

			$category->append_child($label);
			$category->append_child($value);

			$st = quexml_skipto($qid,'Y'," AND c.cfieldname LIKE '+$surveyid" . "X" . $Row['gid'] . "X" . $qid . $Row['title'] . "' ");
			if ($st !== false)
			{
				$quexml_skipto = $dom->create_element("quexml_skipto");
				$quexml_skipto->set_content($st);
				$category->append_child($quexml_skipto);
			}


			$fixed->append_child($category);

			$response->append_child($fixed);
		}
		else
			$response->append_child(quexml_create_free($free['f'],$free['len'],$Row['question']));

		$response->set_attribute("varName",$varname . quexml_cleanup($Row['title']));

		$question->append_child($response);
	}

	if ($other && $free==false)
	{
		$response = $dom->create_element("response");
		$fixed = $dom->create_element("fixed");
		$category = $dom->create_element("category");

		$label = $dom->create_element("label");
		$label->set_content(quexml_get_lengthth($qid,"other_replace_text",$qlang->gT("Other")));

		$value= $dom->create_element("value");

		//Get next code
		if (is_numeric($nextcode))
			$nextcode++;
		else if (is_string($nextcode))
			$nextcode = chr(ord($nextcode) + 1);

		$value->set_content(1);

		$category->append_child($label);
		$category->append_child($value);

		$contingentQuestion = $dom->create_element("contingentQuestion");
		$length = $dom->create_element("length");
		$text = $dom->create_element("text");

		$text->set_content(quexml_get_lengthth($qid,"other_replace_text",$qlang->gT("Other")));
		$length->set_content(24);
		$contingentQuestion->append_child($text);
		$contingentQuestion->append_child($length);

		$category->append_child($contingentQuestion);

		$fixed->append_child($category);
		$response->append_child($fixed);
		$response->set_attribute("varName",$varname . quexml_cleanup($nextcode));

		$question->append_child($response);
	}




	return;

}

/**
 * from export_structure_quexml.php
 */
function quexml_create_subQuestions(&$question,$qid,$varname,$use_answers = false)
{
	global $dom;
	$CI =& get_instance();
	$dbprefix = $CI->db->dbprefix;
	global $quexmllang ;

	if ($use_answers)
		$Query = "SELECT answer as question, code as title FROM {$dbprefix}answers WHERE qid = $qid  AND language='$quexmllang' ORDER BY sortorder ASC";
	else
		$Query = "SELECT * FROM {$dbprefix}questions WHERE parent_qid = $qid and scale_id = 0  AND language='$quexmllang' ORDER BY question_order ASC";
	$QueryResult = db_execute_assoc($Query);
	foreach($QueryResult->result_array() as $Row)
	{
		$subQuestion = $dom->create_element("subQuestion");
		$text = $dom->create_element("text");
		$text->set_content(quexml_cleanup($Row['question']));
		$subQuestion->append_child($text);
		$subQuestion->set_attribute("varName",$varname . quexml_cleanup($Row['title']));
		$question->append_child($subQuestion);
	}

	return;
}

/**
 * Export quexml survey.
 */
function quexml_export($surveyi, $quexmllan)
{
	global $dom, $quexmllang, $surveyid;
	$quexmllang = $quexmllan;
	$surveyid = $surveyi;
	$CI =& get_instance();
	$dbprefix = $CI->db->dbprefix;
	$qlang = new limesurvey_lang(array($quexmllang));

	$CI->load->helper("admin/domxml_wrapper");
	$dom = domxml_new_doc("1.0");

	//Title and survey id
	$questionnaire = $dom->create_element("questionnaire");
	$title = $dom->create_element("title");

	$Query = "SELECT * FROM {$dbprefix}surveys,{$dbprefix}surveys_languagesettings WHERE sid=$surveyid and surveyls_survey_id=sid and surveyls_language='".$quexmllang."'";
	$QueryResult = db_execute_assoc($Query);
	$Row = $QueryResult->row_array();
	$questionnaire->set_attribute("id", $Row['sid']);
	$title->set_content(quexml_cleanup($Row['surveyls_title']));
	$questionnaire->append_child($title);

	//investigator and datacollector
	$investigator = $dom->create_element("investigator");
	$name = $dom->create_element("name");
	$name = $dom->create_element("firstName");
	$name = $dom->create_element("lastName");
	$dataCollector = $dom->create_element("dataCollector");

	$questionnaire->append_child($investigator);
	$questionnaire->append_child($dataCollector);

	//questionnaireInfo == welcome
	if (!empty($Row['surveyls_welcometext']))
	{
		$questionnaireInfo = $dom->create_element("questionnaireInfo");
		$position = $dom->create_element("position");
		$text = $dom->create_element("text");
		$administration = $dom->create_element("administration");
		$position->set_content("before");
		$text->set_content(quexml_cleanup($Row['surveyls_welcometext']));
		$administration->set_content("self");
		$questionnaireInfo->append_child($position);
		$questionnaireInfo->append_child($text);
		$questionnaireInfo->append_child($administration);
		$questionnaire->append_child($questionnaireInfo);
	}

	//section == group


	$Query = "SELECT * FROM {$dbprefix}groups WHERE sid=$surveyid AND language='$quexmllang' order by group_order ASC";
	$QueryResult = db_execute_assoc($Query);

	//for each section
	foreach($QueryResult->result_array() as $Row)
	{
		$gid = $Row['gid'];

		$section = $dom->create_element("section");

		if (!empty($Row['group_name']))
		{
			$sectionInfo = $dom->create_element("sectionInfo");
			$position = $dom->create_element("position");
			$text = $dom->create_element("text");
			$administration = $dom->create_element("administration");
			$position->set_content("title");
			$text->set_content(quexml_cleanup($Row['group_name']));
			$administration->set_content("self");
			$sectionInfo->append_child($position);
			$sectionInfo->append_child($text);
			$sectionInfo->append_child($administration);
			$section->append_child($sectionInfo);
		}


		if (!empty($Row['description']))
		{
			$sectionInfo = $dom->create_element("sectionInfo");
			$position = $dom->create_element("position");
			$text = $dom->create_element("text");
			$administration = $dom->create_element("administration");
			$position->set_content("before");
			$text->set_content(quexml_cleanup($Row['description']));
			$administration->set_content("self");
			$sectionInfo->append_child($position);
			$sectionInfo->append_child($text);
			$sectionInfo->append_child($administration);
			$section->append_child($sectionInfo);
		}



		$section->set_attribute("id", $gid);

		//boilerplate questions convert to sectionInfo elements
		$Query = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid = $gid AND type LIKE 'X'  AND language='$quexmllang' ORDER BY question_order ASC";
		$QR = db_execute_assoc($Query);
		foreach($QR->result_array() as $RowQ)
		{
			$sectionInfo = $dom->create_element("sectionInfo");
			$position = $dom->create_element("position");
			$text = $dom->create_element("text");
			$administration = $dom->create_element("administration");

			$position->set_content("before");
			$text->set_content(quexml_cleanup($RowQ['question']));
			$administration->set_content("self");
			$sectionInfo->append_child($position);
			$sectionInfo->append_child($text);
			$sectionInfo->append_child($administration);

			$section->append_child($sectionInfo);
		}



		//foreach question
		$Query = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid = $gid AND parent_qid=0 AND language='$quexmllang' AND type NOT LIKE 'X' ORDER BY question_order ASC";
		$QR = db_execute_assoc($Query);
		foreach($QR->result_array() as $RowQ)
		{
			$question = $dom->create_element("question");
			$type = $RowQ['type'];
			$qid = $RowQ['qid'];

			$other = false;
			if ($RowQ['other'] == 'Y') $other = true;

			//create a new text element for each new line
			$questiontext = explode('<br />',$RowQ['question']);
			foreach ($questiontext as $qt)
			{
				$txt = quexml_cleanup($qt);
				if (!empty($txt))
				{
					$text = $dom->create_element("text");
					$text->set_content($txt);
					$question->append_child($text);
				}
			}


			//directive
			if (!empty($RowQ['help']))
			{
				$directive = $dom->create_element("directive");
				$position = $dom->create_element("position");
				$position->set_content("during");
				$text = $dom->create_element("text");
				$text->set_content(quexml_cleanup($RowQ['help']));
				$administration = $dom->create_element("administration");
				$administration->set_content("self");

				$directive->append_child($position);
				$directive->append_child($text);
				$directive->append_child($administration);

				$question->append_child($directive);
			}

			$response = $dom->create_element("response");
			$response->set_attribute("varName",quexml_cleanup($RowQ['title']));

			switch ($type)
			{
				case "X": //BOILERPLATE QUESTION - none should appear

					break;
				case "5": //5 POINT CHOICE radio-buttons
					$response->append_child(quexml_fixed_array(array("1" => 1,"2" => 2,"3" => 3,"4" => 4,"5" => 5)));
				$question->append_child($response);
				break;
				case "D": //DATE
					$response->append_child(quexml_create_free("date","8",""));
				$question->append_child($response);
				break;
				case "L": //LIST drop-down/radio-button list
					$response->append_child(quexml_create_fixed($qid,false,false,0,$other));
				$question->append_child($response);
				break;
				case "!": //List - dropdown
					$response->append_child(quexml_create_fixed($qid,false,false,0,$other));
				$question->append_child($response);
				break;
				case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
					$response->append_child(quexml_create_fixed($qid,false,false,0,$other));
				$question->append_child($response);
				//no comment - this should be a separate question
				break;
				case "R": //RANKING STYLE
					quexml_create_subQuestions($question,$qid,$RowQ['title'],true);
				$Query = "SELECT COUNT(*) as sc FROM {$dbprefix}answers WHERE qid = $qid AND language='$quexmllang' ";
				$QRE = db_execute_assoc($Query);
				//$QRE = mysql_query($Query) or die ("ERROR: $QRE<br />".mysql_error());
				//$QROW = mysql_fetch_assoc($QRE);
				$QROW = $QRE->row_array();
				$response->append_child(quexml_create_free("integer",strlen($QROW['sc']),""));
				$question->append_child($response);
				break;
				case "M": //Multiple choice checkbox
					quexml_create_multi($question,$qid,$RowQ['title'],false,false,$other);
				break;
				case "P": //Multiple choice with comments checkbox + text
					//Not yet implemented
					quexml_create_multi($question,$qid,$RowQ['title'],false,false,$other);
				//no comments added
				break;
				case "Q": //MULTIPLE SHORT TEXT
					quexml_create_subQuestions($question,$qid,$RowQ['title']);
				$response->append_child(quexml_create_free("text",quexml_get_lengthth($qid,"maximum_chars","10"),""));
				$question->append_child($response);
				break;
				case "K": //MULTIPLE NUMERICAL
					quexml_create_subQuestions($question,$qid,$RowQ['title']);
				$response->append_child(quexml_create_free("integer",quexml_get_lengthth($qid,"maximum_chars","10"),""));
				$question->append_child($response);
				break;
				case "N": //NUMERICAL QUESTION TYPE
					$response->append_child(quexml_create_free("integer",quexml_get_lengthth($qid,"maximum_chars","10"),""));
				$question->append_child($response);
				break;
				case "S": //SHORT FREE TEXT
					$response->append_child(quexml_create_free("text",quexml_get_lengthth($qid,"maximum_chars","240"),""));
				$question->append_child($response);
				break;
				case "T": //LONG FREE TEXT
					$response->append_child(quexml_create_free("longtext",quexml_get_lengthth($qid,"display_rows","40"),""));
				$question->append_child($response);
				break;
				case "U": //HUGE FREE TEXT
					$response->append_child(quexml_create_free("longtext",quexml_get_lengthth($qid,"display_rows","80"),""));
				$question->append_child($response);
				break;
				case "Y": //YES/NO radio-buttons
					$response->append_child(quexml_fixed_array(array($qlang->gT("Yes") => 1,$qlang->gT("No") => 2)));
				$question->append_child($response);
				break;
				case "G": //GENDER drop-down list
					$response->append_child(quexml_fixed_array(array($qlang->gT("Female") => 1,$qlang->gT("Male") => 2)));
				$question->append_child($response);
				break;
				case "A": //ARRAY (5 POINT CHOICE) radio-buttons
					quexml_create_subQuestions($question,$qid,$RowQ['title']);
				$response->append_child(quexml_fixed_array(array("1" => 1,"2" => 2,"3" => 3,"4" => 4,"5" => 5)));
				$question->append_child($response);
				break;
				case "B": //ARRAY (10 POINT CHOICE) radio-buttons
					quexml_create_subQuestions($question,$qid,$RowQ['title']);
				$response->append_child(quexml_fixed_array(array("1" => 1,"2" => 2,"3" => 3,"4" => 4,"5" => 5,"6" => 6,"7" => 7,"8" => 8,"9" => 9,"10" => 10)));
				$question->append_child($response);
				break;
				case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
					quexml_create_subQuestions($question,$qid,$RowQ['title']);
				$response->append_child(quexml_fixed_array(array($qlang->gT("Yes") => 1,$qlang->gT("Uncertain") => 2,$qlang->gT("No") => 3)));
				$question->append_child($response);
				break;
				case "E": //ARRAY (Increase/Same/Decrease) radio-buttons
					quexml_create_subQuestions($question,$qid,$RowQ['title']);
				$response->append_child(quexml_fixed_array(array($qlang->gT("Increase") => 1,$qlang->gT("Same") => 2,$qlang->gT("Decrease") => 3)));
				$question->append_child($response);
				break;
				case "F": //ARRAY (Flexible) - Row Format
					//select subQuestions from answers table where QID
					quexml_create_subQuestions($question,$qid,$RowQ['title']);
				$response->append_child(quexml_create_fixed($qid,false,false,0,$other));
				$question->append_child($response);
				//select fixed responses from
				break;
				case "H": //ARRAY (Flexible) - Column Format
					quexml_create_subQuestions($question,$RowQ['qid'],$RowQ['title']);
				$response->append_child(quexml_create_fixed($qid,true,false,0,$other));
				$question->append_child($response);
				break;
				case "1": //Dualscale multi-flexi array
					//select subQuestions from answers table where QID
					quexml_create_subQuestions($question,$qid,$RowQ['title']);
				$response = $dom->create_element("response");
				$response->append_child(quexml_create_fixed($qid,false,false,0,$other));
				$response2 = $dom->create_element("response");
				$response2->set_attribute("varName",quexml_cleanup($RowQ['title']) . "_2");
				$response2->append_child(quexml_create_fixed($qid,false,false,1,$other));
				$question->append_child($response);
				$question->append_child($response2);
				break;
				case ":": //multi-flexi array numbers
					quexml_create_subQuestions($question,$qid,$RowQ['title']);
				//get multiflexible_checkbox - if set then each box is a checkbox (single fixed response)
				$mcb  = quexml_get_lengthth($qid,'multiflexible_checkbox',-1);
				if ($mcb != -1)
					quexml_create_multi($question,$qid,$RowQ['title'],1);
				else
				{
					//get multiflexible_max - if set then make boxes of max this width
					$mcm = strlen(quexml_get_lengthth($qid,'multiflexible_max',1));
					quexml_create_multi($question,$qid,$RowQ['title'],1,array('f' => 'integer', 'len' => $mcm, 'lab' => ''));
				}
				break;
				case ";": //multi-flexi array text
					quexml_create_subQuestions($question,$qid,$RowQ['title']);
				//foreach question where scale_id = 1 this is a textbox
				quexml_create_multi($question,$qid,$RowQ['title'],1,array('f' => 'text', 'len' => 10, 'lab' => ''));
				break;
				case "^": //SLIDER CONTROL - not supported
					$response->append_child(quexml_fixed_array(array("NOT SUPPORTED:$type" => 1)));
				$question->append_child($response);
				break;
			} //End Switch




			$section->append_child($question);
		}


		$questionnaire->append_child($section);
	}


	$dom->append_child($questionnaire);

	return $dom->dump_mem(true,'UTF-8');
}

/**
 * From adodb
 *
 * Different SQL databases used different methods to combine strings together.
 * This function provides a wrapper.
 *
 * param s	variable number of string parameters
 *
 * Usage: $db->Concat($str1,$str2);
 *
 * @return concatenated string
 */
function concat()
{
    $arr = func_get_args();
    return implode('+', $arr);
}

/**
 * LSRC csv survey export
 */
function lsrccsv_export($surveyid)
{
	// DUMP THE RELATED DATA FOR A SINGLE SURVEY INTO A SQL FILE FOR IMPORTING LATER ON OR ON ANOTHER SURVEY SETUP
	// DUMP ALL DATA WITH RELATED SID FROM THE FOLLOWING TABLES
	// 1. Surveys
	// 2. Surveys Language Table
	// 3. Groups
	// 4. Questions
	// 5. Answers
	// 6. Conditions
	// 7. Label Sets
	// 8. Labels
	// 9. Question Attributes
	// 10. Assessments
	// 11. Quota
	// 12. Quota Members

	include_once("login_check.php");

	if (!isset($surveyid)) {$surveyid=returnglobal('sid');}


	if (!$surveyid)
	{
	    echo $htmlheader
	    ."<br />\n"
	    ."<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
	    ."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	    .$clang->gT("Export Survey")."</strong></td></tr>\n"
	    ."\t<tr><td align='center'>\n"
	    ."<br /><strong><font color='red'>"
	    .$clang->gT("Error")."</font></strong><br />\n"
	    .$clang->gT("No SID has been provided. Cannot dump survey")."<br />\n"
	    ."<br /><input type='submit' value='"
	    .$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n"
	    ."\t</td></tr>\n"
	    ."</table>\n"
	    ."</body></html>\n";
	    exit;
	}

	$dumphead = "# LimeSurvey Survey Dump\n"
	. "# DBVersion $dbversionnumber\n"
	. "# This is a dumped survey from the LimeSurvey Script\n"
	. "# http://www.limesurvey.org/\n"
	. "# Do not change this header!\n";

	//1: Surveys table
	$squery = "SELECT *
	           FROM {$dbprefix}surveys
			   WHERE sid=$surveyid";
	$sdump = BuildCSVFromQuery($squery);

	//2: Surveys Languagsettings table
	$slsquery = "SELECT *
	             FROM {$dbprefix}surveys_languagesettings
				 WHERE surveyls_survey_id=$surveyid";
	$slsdump = BuildCSVFromQuery($slsquery);

	//3: Groups Table
	$gquery = "SELECT *
	           FROM {$dbprefix}groups
			   WHERE sid=$surveyid
			   ORDER BY gid";
	$gdump = BuildCSVFromQuery($gquery);

	//4: Questions Table
	$qquery = "SELECT *
	           FROM {$dbprefix}questions
			   WHERE sid=$surveyid
			   ORDER BY qid";
	$qdump = BuildCSVFromQuery($qquery);

	//5: Answers table
	$aquery = "SELECT {$dbprefix}answers.*
	           FROM {$dbprefix}answers, {$dbprefix}questions
			   WHERE {$dbprefix}answers.language={$dbprefix}questions.language
			   AND {$dbprefix}answers.qid={$dbprefix}questions.qid
			   AND {$dbprefix}questions.sid=$surveyid";
	$adump = BuildCSVFromQuery($aquery);

	//6: Conditions table
	$cquery = "SELECT DISTINCT {$dbprefix}conditions.*
	           FROM {$dbprefix}conditions, {$dbprefix}questions
			   WHERE {$dbprefix}conditions.qid={$dbprefix}questions.qid
			   AND {$dbprefix}questions.sid=$surveyid";
	$cdump = BuildCSVFromQuery($cquery);

	//7: Label Sets
	$lsquery = "SELECT DISTINCT {$dbprefix}labelsets.lid, label_name, {$dbprefix}labelsets.languages
	            FROM {$dbprefix}labelsets, {$dbprefix}questions
				WHERE ({$dbprefix}labelsets.lid={$dbprefix}questions.lid or {$dbprefix}labelsets.lid={$dbprefix}questions.lid1)
				AND type IN ('F', 'H', 'W', 'Z', '1', ':', ';')
				AND sid=$surveyid";
	$lsdump = BuildCSVFromQuery($lsquery);

	//8: Labels
	$lquery = "SELECT {$dbprefix}labels.lid, {$dbprefix}labels.code, {$dbprefix}labels.title, {$dbprefix}labels.sortorder,{$dbprefix}labels.language
	           FROM {$dbprefix}labels, {$dbprefix}questions
			   WHERE ({$dbprefix}labels.lid={$dbprefix}questions.lid or {$dbprefix}labels.lid={$dbprefix}questions.lid1)
			   AND type in ('F', 'W', 'H', 'Z', '1', ':', ';')
			   AND sid=$surveyid
			   GROUP BY {$dbprefix}labels.lid, {$dbprefix}labels.code, {$dbprefix}labels.title, {$dbprefix}labels.sortorder,{$dbprefix}labels.language";
	$ldump = BuildCSVFromQuery($lquery);

	//9: Question Attributes
	$query = "SELECT DISTINCT {$dbprefix}question_attributes.*
	          FROM {$dbprefix}question_attributes, {$dbprefix}questions
			  WHERE {$dbprefix}question_attributes.qid={$dbprefix}questions.qid
			  AND {$dbprefix}questions.sid=$surveyid";
	$qadump = BuildCSVFromQuery($query);

	//10: Assessments;
	$query = "SELECT {$dbprefix}assessments.*
	          FROM {$dbprefix}assessments
			  WHERE {$dbprefix}assessments.sid=$surveyid";
	$asdump = BuildCSVFromQuery($query);

	//11: Quota;
	$query = "SELECT {$dbprefix}quota.*
	          FROM {$dbprefix}quota
			  WHERE {$dbprefix}quota.sid=$surveyid";
	$quotadump = BuildCSVFromQuery($query);

	//12: Quota Members;
	$query = "SELECT {$dbprefix}quota_members.*
	          FROM {$dbprefix}quota_members
			  WHERE {$dbprefix}quota_members.sid=$surveyid";
	$quotamemdump = BuildCSVFromQuery($query);

	$fn = "limesurvey_survey_$surveyid.csv";

	//header("Content-Type: application/download");
	//header("Content-Disposition: attachment; filename=$fn");
	//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
	//header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	//header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	//header("Pragma: cache");                          // HTTP/1.0

	//include("../config.php");
	include_once("../config-defaults.php");
	include_once("../common.php");
	include("remotecontrol/lsrc.config.php");

	$lsrcString = $dumphead. $sdump. $gdump. $qdump. $adump. $cdump. $lsdump. $ldump. $qadump. $asdump. $slsdump. $quotadump. $quotamemdump."\n";

	//Select title as Filename and save
	$surveyTitleSql = "SELECT surveyls_title
		             FROM {$dbprefix}surveys_languagesettings
					 WHERE surveyls_survey_id=$surveyid";
	$surveyTitleRs = db_execute_assoc($surveyTitleSql);
	$surveyTitle = $surveyTitleRs->FetchRow();
	file_put_contents("remotecontrol/".$coreDir.$surveyTitle['surveyls_title'].".csv",$lsrcString);

	header("Location: $scriptname?sid=$surveyid");
	exit;
}

// DUMP THE RELATED DATA FOR A SINGLE QUESTION INTO A SQL FILE FOR IMPORTING LATER ON OR
// ON ANOTHER SURVEY SETUP DUMP ALL DATA WITH RELATED QID FROM THE FOLLOWING TABLES
// 1. questions
// 2. answers

function group_export($action, $surveyid, $gid)
{
	$fn = "limesurvey_group_$gid.lsg";
	$xml = getXMLWriter();
	$CI =& get_instance();
	$dbprefix = $CI->db->dbprefix;


	if($action=='exportstructureLsrcCsvGroup')
	{
	    include_once($homedir.'/remotecontrol/lsrc.config.php');
	    //Select group_name as Filename and save
	    $groupTitleSql = "SELECT group_name
	                     FROM {$dbprefix}groups
	                     WHERE sid=$surveyid AND gid=$gid ";
	    $groupTitle = $connect->GetOne($groupTitleSql);
	    $xml->openURI('remotecontrol/'.$queDir.substr($groupTitle,0,20).".lsq");
	}
	else
	{
	    header("Content-Type: text/html/force-download");
	    header("Content-Disposition: attachment; filename=$fn");
	    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
	    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Pragma: cache");                          // HTTP/1.0

	    $xml->openURI('php://output');
	}

	$xml->setIndent(true);
	$xml->startDocument('1.0', 'UTF-8');
	$xml->startElement('document');
	$xml->writeElement('LimeSurveyDocType','Group');
	$xml->writeElement('DBVersion',$CI->config->item("dbversionnumber"));
	$xml->startElement('languages');
	$lquery = "SELECT language
	           FROM {$dbprefix}groups
	           WHERE gid=$gid group by language";
	$lresult=db_execute_assoc($lquery);
	foreach($lresult->result_array() as $row)
	{
	    $xml->writeElement('language',$row['language']);
	}
	$xml->endElement();
	group_getXMLStructure($xml,$gid);
	$xml->endElement(); // close columns
	$xml->endDocument();
	exit;
}

function group_getXMLStructure($xml,$gid)
{
	$CI =& get_instance();
	$dbprefix = $CI->db->dbprefix;
    // Groups
    $gquery = "SELECT *
               FROM {$dbprefix}groups
               WHERE gid=$gid";
    BuildXMLFromQuery($xml,$gquery);

    // Questions table
    $qquery = "SELECT *
               FROM {$dbprefix}questions
               WHERE gid=$gid and parent_qid=0 order by question_order, language, scale_id";
    BuildXMLFromQuery($xml,$qquery);

    // Questions table - Subquestions
    $qquery = "SELECT *
               FROM {$dbprefix}questions
               WHERE gid=$gid and parent_qid>0 order by question_order, language, scale_id";
    BuildXMLFromQuery($xml,$qquery,'subquestions');

    //Answers
    $aquery = "SELECT DISTINCT {$dbprefix}answers.*
               FROM {$dbprefix}answers, {$dbprefix}questions
               WHERE ({$dbprefix}answers.qid={$dbprefix}questions.qid)
               AND ({$dbprefix}questions.gid=$gid)";
    BuildXMLFromQuery($xml,$aquery);

    //Conditions - THIS CAN ONLY EXPORT CONDITIONS THAT RELATE TO THE SAME GROUP
    $cquery = "SELECT DISTINCT c.*
               FROM {$dbprefix}conditions c, {$dbprefix}questions q, {$dbprefix}questions b
               WHERE (c.cqid=q.qid)
               AND (c.qid=b.qid)
               AND (q.gid={$gid})
               AND (b.gid={$gid})";
    BuildXMLFromQuery($xml,$cquery,'conditions');

    //Question attributes
    $surveyid=db_execute_assoc("select sid from {$dbprefix}groups where gid={$gid}");
    $surveyid=reset($surveyid->row_array());
    $sBaseLanguage=GetBaseLanguageFromSurveyID($surveyid);
    if ($CI->db->platform() == 'odbc_mssql' || $CI->db->platform() == 'odbtp' || $CI->db->platform() == 'mssql_n' || $CI->db->platform() =='mssqlnative')
    {
        $query="SELECT qa.qid, qa.attribute, cast(qa.value as varchar(4000)) as value
          FROM {$dbprefix}question_attributes qa JOIN {$dbprefix}questions  q ON q.qid = qa.qid AND q.sid={$surveyid} and q.gid={$gid}
          where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute,  cast(qa.value as varchar(4000))";
    }
    else {
        $query="SELECT qa.qid, qa.attribute, qa.value
          FROM {$dbprefix}question_attributes qa JOIN {$dbprefix}questions  q ON q.qid = qa.qid AND q.sid={$surveyid} and q.gid={$gid}
          where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute, qa.value";
    }
    BuildXMLFromQuery($xml,$query,'question_attributes');

    // Default values
    $query = "SELECT dv.*
                FROM {$dbprefix}defaultvalues dv
                JOIN {$dbprefix}questions ON {$dbprefix}questions.qid = dv.qid
                AND {$dbprefix}questions.language=dv.language
                AND {$dbprefix}questions.gid=$gid
                order by dv.language, dv.scale_id";
    BuildXMLFromQuery($xml,$query,'defaultvalues');
}


// DUMP THE RELATED DATA FOR A SINGLE QUESTION INTO A SQL FILE FOR IMPORTING LATER ON OR
// ON ANOTHER SURVEY SETUP DUMP ALL DATA WITH RELATED QID FROM THE FOLLOWING TABLES
//  - Questions
//  - Answers
//  - Question attributes
//  - Default values

function question_export($action, $surveyid, $gid, $qid)
{
	$fn = "limesurvey_question_$qid.lsq";
	$xml = getXMLWriter();
	$CI =& get_instance();
	$dbprefix = $CI->db->dbprefix;

	if($action=='exportstructureLsrcCsvQuestion')
	{
	    include_once($homedir.'/remotecontrol/lsrc.config.php');
	    //Select title as Filename and save
	    $questionTitleSql = "SELECT title
	                     FROM {$dbprefix}questions
	                     WHERE qid=$qid AND sid=$surveyid AND gid=$gid ";
	    $questionTitle = $connect->GetOne($questionTitleSql);
	    $xml->openURI('remotecontrol/'.$queDir.substr($questionTitle,0,20).".lsq");
	}
	else
	{
	    header("Content-Type: text/html/force-download");
	    header("Content-Disposition: attachment; filename=$fn");
	    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
	    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Pragma: cache");                          // HTTP/1.0

	    $xml->openURI('php://output');
	}

	$xml->setIndent(true);
	$xml->startDocument('1.0', 'UTF-8');
	$xml->startElement('document');
	$xml->writeElement('LimeSurveyDocType','Question');
	$xml->writeElement('DBVersion',$CI->config->item("dbversionnumber"));
	$xml->startElement('languages');
	$lquery = "SELECT language
	           FROM {$dbprefix}questions
	           WHERE qid=$qid or parent_qid=$qid group by language";
	$lresult=db_execute_assoc($lquery);
	foreach($lresult->result_array() as $row)
	{
	    $xml->writeElement('language',$row['language']);
	}
	$xml->endElement();
	question_getXMLStructure($xml,$gid,$qid);
	$xml->endElement(); // close columns
	$xml->endDocument();
	exit;
}

function question_getXMLStructure($xml,$gid,$qid)
{
	$CI =& get_instance();
	$dbprefix = $CI->db->dbprefix;

    // Questions table
    $qquery = "SELECT *
               FROM {$dbprefix}questions
               WHERE qid=$qid and parent_qid=0 order by language, scale_id, question_order";
    BuildXMLFromQuery($xml,$qquery);

    // Questions table - Subquestions
    $qquery = "SELECT *
               FROM {$dbprefix}questions
               WHERE parent_qid=$qid order by language, scale_id, question_order";
    BuildXMLFromQuery($xml,$qquery,'subquestions');


    // Answers table
    $aquery = "SELECT *
               FROM {$dbprefix}answers
               WHERE qid = $qid order by language, scale_id, sortorder";
    BuildXMLFromQuery($xml,$aquery);



    // Question attributes
    $surveyid=db_execute_assoc("select sid from {$dbprefix}groups where gid={$gid}");
    $surveyid=reset($surveyid->row_array());
    $sBaseLanguage=GetBaseLanguageFromSurveyID($surveyid);
    if ($CI->db->platform() == 'odbc_mssql' || $CI->db->platform() == 'odbtp' || $CI->db->platform() == 'mssql_n' || $CI->db->platform() =='mssqlnative')
    {
        $query="SELECT qa.qid, qa.attribute, cast(qa.value as varchar(4000)) as value
          FROM {$dbprefix}question_attributes qa JOIN {$dbprefix}questions  q ON q.qid = qa.qid AND q.sid={$surveyid} and q.qid={$qid}
          where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute,  cast(qa.value as varchar(4000))";
    }
    else {
        $query="SELECT qa.qid, qa.attribute, qa.value
          FROM {$dbprefix}question_attributes qa JOIN {$dbprefix}questions  q ON q.qid = qa.qid AND q.sid={$surveyid} and q.qid={$qid}
          where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute, qa.value";
    }
    BuildXMLFromQuery($xml,$query);

    // Default values
    $query = "SELECT *
              FROM {$dbprefix}defaultvalues
              WHERE qid=$qid  order by language, scale_id";
    BuildXMLFromQuery($xml,$query);

}


function tokens_export($surveyid)
{
    header("Content-Disposition: attachment; filename=tokens_".$surveyid.".csv");
    header("Content-type: text/comma-separated-values; charset=UTF-8");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: cache");

    $CI =& get_instance();
	$dbprefix = $CI->db->dbprefix;
	$_POST = $CI->input->post();

    $bquery = "SELECT * FROM ".$CI->db->dbprefix("tokens_$surveyid").' where 1=1';
    if (trim($_POST['filteremail'])!='')
    {
        if ($databasetype=='odbc_mssql' || $databasetype=='odbtp' || $databasetype=='mssql_n' || $connect->databaseType == 'mssqlnative')
        {
            $bquery .= ' and CAST(email as varchar) like '.db_quoteall('%'.$_POST['filteremail'].'%', true);
        }
        else
        {
            $bquery .= ' and email like '.db_quoteall('%'.$_POST['filteremail'].'%', true);
        }
    }
    if ($_POST['tokenstatus']==1)
    {
        $bquery .= " and completed<>'N'";
    }
    if ($_POST['tokenstatus']==2)
    {
        $bquery .= " and completed='N'";
        if ($thissurvey['anonymized']=='N')
        {
            $bquery .=" and token not in (select token from ".db_table_name("survey_$surveyid")." group by token)";
        }
    }
    if ($_POST['tokenstatus']==3 && $thissurvey['anonymized']=='N')
    {
        $bquery .= " and completed='N' and token in (select token from ".db_table_name("survey_$surveyid")." group by token)";
    }

    if ($_POST['invitationstatus']==1)
    {
        $bquery .= " and sent<>'N'";
    }
    if ($_POST['invitationstatus']==2)
    {
        $bquery .= " and sent='N'";
    }

    if ($_POST['reminderstatus']==1)
    {
        $bquery .= " and remindersent<>'N'";
    }
    if ($_POST['reminderstatus']==2)
    {
        $bquery .= " and remindersent='N'";
    }

    if ($_POST['tokenlanguage']!='')
    {
        $bquery .= " and language=".db_quoteall($_POST['tokenlanguage']);
    }
    $bquery .= " ORDER BY tid";

    $bresult = db_execute_assoc($bquery) or die ("$bquery<br />".htmlspecialchars($connect->ErrorMsg()));
    $bfieldcount=$bresult->num_rows();
    // Export UTF8 WITH BOM
    $tokenoutput = chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'));
    $tokenoutput .= "tid,firstname,lastname,email,emailstatus,token,language,validfrom,validuntil,invited,reminded,remindercount,completed,usesleft";
    $attrfieldnames = GetAttributeFieldnames($surveyid);
    $attrfielddescr = GetTokenFieldsAndNames($surveyid, true);
    foreach ($attrfieldnames as $attr_name)
    {
        $tokenoutput .=", $attr_name";
        if (isset($attrfielddescr[$attr_name]))
        $tokenoutput .=" <".str_replace(","," ",$attrfielddescr[$attr_name]).">";
    }
    $tokenoutput .="\n";
    foreach($bresult->result_array() as $brow)
    {

        if (trim($brow['validfrom']!=''))
        {
            $datetimeobj = new Date_Time_Converter($brow['validfrom'] , "Y-m-d H:i:s");
            $brow['validfrom']=$datetimeobj->convert('Y-m-d H:i');
        }
        if (trim($brow['validuntil']!=''))
        {
            $datetimeobj = new Date_Time_Converter($brow['validuntil'] , "Y-m-d H:i:s");
            $brow['validuntil']=$datetimeobj->convert('Y-m-d H:i');
        }

        $tokenoutput .= '"'.trim($brow['tid']).'",';
        $tokenoutput .= '"'.trim($brow['firstname']).'",';
        $tokenoutput .= '"'.trim($brow['lastname']).'",';
        $tokenoutput .= '"'.trim($brow['email']).'",';
        $tokenoutput .= '"'.trim($brow['emailstatus']).'",';
        $tokenoutput .= '"'.trim($brow['token']).'",';
        $tokenoutput .= '"'.trim($brow['language']).'",';
        $tokenoutput .= '"'.trim($brow['validfrom']).'",';
        $tokenoutput .= '"'.trim($brow['validuntil']).'",';
        $tokenoutput .= '"'.trim($brow['sent']).'",';
        $tokenoutput .= '"'.trim($brow['remindersent']).'",';
        $tokenoutput .= '"'.trim($brow['remindercount']).'",';
        $tokenoutput .= '"'.trim($brow['completed']).'",';
        $tokenoutput .= '"'.trim($brow['usesleft']).'",';
        foreach ($attrfieldnames as $attr_name)
        {
            $tokenoutput .='"'.trim($brow[$attr_name]).'",';
        }
        $tokenoutput = substr($tokenoutput,0,-1); // remove last comma
        $tokenoutput .= "\n";
    }
    echo $tokenoutput;
    exit;
}

function cpdb_export($data,$filename)
{

    header("Content-Disposition: attachment; filename=".$filename.".csv");
    header("Content-type: text/comma-separated-values; charset=UTF-8");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: cache");
    $tokenoutput = chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'));
    $CI =& get_instance();

    foreach($data as $key=>$value)
    {
        foreach($value as $values)
        {
            $tokenoutput .= trim($values).',';
            $tokenoutput .= ',';
            $tokenoutput = substr($tokenoutput,0,-1); // remove last comma

        }
        $tokenoutput .= "\n";

    }
    echo $tokenoutput;
    exit;
}
