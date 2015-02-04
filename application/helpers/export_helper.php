<?php
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/**
* Strips html tags and replaces new lines
*
* @param $string
* @return $string
*/
function stripTagsFull($string) {
    $string=html_entity_decode($string, ENT_QUOTES, "UTF-8");
    //combining these into one mb_ereg_replace call ought to speed things up
    $string = str_replace(array("\r\n","\r","\n",'-oth-'), '', $string);
    //The backslashes must be escaped twice, once for php, and again for the regexp
    $string = str_replace("'|\\\\'", "&apos;", $string);
    return flattenText($string);
}

/**
* Returns true if passed $value is numeric
*
* @param $value
* @return bool
*/
function isNumericExtended($value)  {
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

/**
* Returns splitted unicode string correctly
* source: http://www.php.net/manual/en/function.str-split.php#107658
*
* @param $str
* @param $l
* @return string
*/
function strSplitUnicode($str, $l = 0) {
    if ($l > 0)
    {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l)
        {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}

/**
* Exports CSV response data for SPSS and R
*
* @param mixed $iSurveyID The survey ID
* @param mixed $iLength Maximum text lenght data, usually 255 for SPSS <v16 and 16384 for SPSS 16 and later
* @param mixed $na Value for N/A data
* @param sep Quote separator. Use '\'' for SPSS, '"' for R
* @param logical $header If TRUE, adds SQGA code as column headings (used by export to R)
*/
function SPSSExportData ($iSurveyID, $iLength, $na = '', $q='\'', $header=FALSE) {

    // Build array that has to be returned
    $fields = SPSSFieldMap($iSurveyID);

    // Now see if we have parameters for from (offset) & num (limit)
    $limit = App()->getRequest()->getParam('limit');
    $offset = App()->getRequest()->getParam('offset');

    //Now get the query string with all fields to export
    $query = SPSSGetQuery($iSurveyID, $limit, $offset);

    $result = $query->query();

    $rownr = 0;

    foreach ($result as $row) {
        $rownr++;
        if ($rownr == 1) {
            $num_fields = count($row);

            //This shouldn't occur, but just to be safe:
            if (count($fields)<>$num_fields) safeDie("Database inconsistency error");

            // Add column headers (used by R export)
            if($header==TRUE)
            {
                $i = 1;
                foreach ($fields as $field) {
                    if (!$field['hide'] ) echo $q.strtoupper($field['sql_name']).$q;
                    if ($i<$num_fields && !$field['hide']) echo ',';
                    $i++;
                }
                echo("\n");
            }
        }
        $row = array_change_key_case($row,CASE_UPPER);
        //$row = $result->GetRowAssoc(true);    //Get assoc array, use uppercase
        reset($fields);    //Jump to the first element in the field array
        $i = 1;
        foreach ($fields as $field)
        {
            $fieldno = strtoupper($field['sql_name']);
            if ($field['SPSStype']=='DATETIME23.2'){
                #convert mysql  datestamp (yyyy-mm-dd hh:mm:ss) to SPSS datetime (dd-mmm-yyyy hh:mm:ss) format
                if (isset($row[$fieldno]))
                {
                    list( $year, $month, $day, $hour, $minute, $second ) = preg_split( '([^0-9])', $row[$fieldno] );
                    if ($year != '' && (int)$year >= 1900)
                    {
                        echo $q.date('d-m-Y H:i:s', mktime( $hour, $minute, $second, $month, $day, $year ) ).$q;
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
                        echo( $q. 1 .$q);
                    } else if ($row[$fieldno] == 'N'){
                            echo( $q. 2 .$q);
                        } else {
                            echo($na);
                    }
                } else if ($field['LStype'] == 'G')    //Gender
                    {
                        if ($row[$fieldno] == 'F')
                        {
                            echo( $q. 1 .$q);
                        } else if ($row[$fieldno] == 'M'){
                                echo( $q. 2 .$q);
                            } else {
                                echo($na);
                        }
                    } else if ($field['LStype'] == 'C')    //Yes/No/Uncertain
                        {
                            if ($row[$fieldno] == 'Y')
                            {
                                echo( $q. 1 .$q);
                            } else if ($row[$fieldno] == 'N'){
                                    echo( $q. 2 .$q);
                                } else if ($row[$fieldno] == 'U'){
                                        echo( $q. 3 .$q);
                                    } else {
                                        echo($na);
                            }
                        } else if ($field['LStype'] == 'E')     //Increase / Same / Decrease
                            {
                                if ($row[$fieldno] == 'I')
                                {
                                    echo( $q. 1 .$q);
                                } else if ($row[$fieldno] == 'S'){
                                        echo( $q. 2 .$q);
                                    } else if ($row[$fieldno] == 'D'){
                                            echo( $q. 3 .$q);
                                        } else {
                                            echo($na);
                                }
                            } elseif (($field['LStype'] == 'P' || $field['LStype'] == 'M') && (substr($field['code'],-7) != 'comment' && substr($field['code'],-5) != 'other'))
                            {
                                if ($row[$fieldno] == 'Y')
                                {
                                    echo($q. 1 .$q);
                                } else
                                {
                                    echo($q. 0 .$q);
                                }
                            } elseif (!$field['hide']) {
                                $strTmp=mb_substr(stripTagsFull($row[$fieldno]), 0, $iLength);
                                if (trim($strTmp) != ''){
                                    if($q=='\'') $strTemp=str_replace(array("'","\n","\r"),array("''",' ',' '),trim($strTmp));
                                    if($q=='"') $strTemp=str_replace(array('"',"\n","\r"),array('""',' ',' '),trim($strTmp));
                                    /*
                                    * Temp quick fix for replacing decimal dots with comma's
                                    if (isNumericExtended($strTemp)) {
                                    $strTemp = str_replace('.',',',$strTemp);
                                    }
                                    */
                                    echo $q. $strTemp .$q ;
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
* @param $field array field from SPSSFieldMap
* @return array or false
*/
function SPSSGetValues ($field = array(), $qidattributes = null, $language ) {
    $length_vallabel = 120;
    $clang = Yii::app()->lang;

    if (!isset($field['LStype']) || empty($field['LStype'])) return false;
    $answers=array();
    if (strpos("!LORFWZWH1",$field['LStype']) !== false) {
        if (substr($field['code'],-5) == 'other' || substr($field['code'],-7) == 'comment') {
            //We have a comment field, so free text
        } else {
            $query = "SELECT {{answers}}.code, {{answers}}.answer,
            {{questions}}.type FROM {{answers}}, {{questions}} WHERE";

            if (isset($field['scale_id'])) $query .= " {{answers}}.scale_id = " . (int) $field['scale_id'] . " AND";

            $query .= " {{answers}}.qid = '".$field["qid"]."' and {{questions}}.language='".$language."' and  {{answers}}.language='".$language."'
            and {{questions}}.qid='".$field['qid']."' ORDER BY sortorder ASC";
            $result= Yii::app()->db->createCommand($query)->query()->readAll(); //Checked
            $num_results = count($result);
            if ($num_results > 0)
            {
                $displayvaluelabel = 0;
                # Build array that has to be returned
                foreach ($result as $row)
                {
                    $answers[] = array('code'=>$row['code'], 'value'=>mb_substr(stripTagsFull($row["answer"]),0,$length_vallabel));
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
            if ($spsstype=='F' && (isNumericExtended($answer['code'])===false || $size>16)) $spsstype='A';
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
function SPSSFieldMap($iSurveyID, $prefix = 'V') {
    global $clang, $surveyprivate;

    $typeMap = array(
'5'=>Array('name'=>'5 Point Choice','size'=>1,'SPSStype'=>'F','Scale'=>3),
'B'=>Array('name'=>'Array (10 Point Choice)','size'=>1,'SPSStype'=>'F','Scale'=>3),
'A'=>Array('name'=>'Array (5 Point Choice)','size'=>1,'SPSStype'=>'F','Scale'=>3),
'F'=>Array('name'=>'Array (Flexible Labels)','size'=>1,'SPSStype'=>'F'),
'1'=>Array('name'=>'Array (Flexible Labels) Dual Scale','size'=>1,'SPSStype'=>'F'),
'H'=>Array('name'=>'Array (Flexible Labels) by Column','size'=>1,'SPSStype'=>'F'),
'E'=>Array('name'=>'Array (Increase, Same, Decrease)','size'=>1,'SPSStype'=>'F','Scale'=>2),
'C'=>Array('name'=>'Array (Yes/No/Uncertain)','size'=>1,'SPSStype'=>'F'),
'X'=>Array('name'=>'Boilerplate Question','size'=>1,'SPSStype'=>'A','hide'=>1),
'D'=>Array('name'=>'Date','size'=>20,'SPSStype'=>'DATETIME23.2'),
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
'M'=>Array('name'=>'Multiple choice','size'=>1,'SPSStype'=>'F'),
'P'=>Array('name'=>'Multiple choice with comments','size'=>1,'SPSStype'=>'F'),
'Q'=>Array('name'=>'Multiple Short Text','size'=>1,'SPSStype'=>'F'),
'N'=>Array('name'=>'Numerical Input','size'=>3,'SPSStype'=>'F','Scale'=>3),
'R'=>Array('name'=>'Ranking','size'=>1,'SPSStype'=>'F'),
'S'=>Array('name'=>'Short free text','size'=>1,'SPSStype'=>'F'),
'Y'=>Array('name'=>'Yes/No','size'=>1,'SPSStype'=>'F'),
':'=>Array('name'=>'Multi flexi numbers','size'=>1,'SPSStype'=>'F','Scale'=>3),
';'=>Array('name'=>'Multi flexi text','size'=>1,'SPSStype'=>'A'),
'|'=>Array('name'=>'File upload','size'=>1,'SPSStype'=>'A'),
'*'=>Array('name'=>'Equation','size'=>1,'SPSStype'=>'A'),
);

    $fieldmap = createFieldMap($iSurveyID,'full',false,false,getBaseLanguageFromSurveyID($iSurveyID));

    #See if tokens are being used
    $bTokenTableExists = tableExists('tokens_'.$iSurveyID);

    #Lookup the names of the attributes
    $query="SELECT sid, anonymized, language FROM {{surveys}} WHERE sid=$iSurveyID";
    $aRow=Yii::app()->db->createCommand($query)->queryRow();  //Checked
    $surveyprivate=$aRow['anonymized'];
    $language=$aRow['language'];

    $fieldno=0;

    $fields=array();
    if ($bTokenTableExists && $surveyprivate == 'N' && Permission::model()->hasSurveyPermission($iSurveyID,'tokens','read')) {
        $tokenattributes=getTokenFieldsAndNames($iSurveyID,false);
        foreach ($tokenattributes as $attributefield=>$attributedescription)
        {
            //Drop the token field, since it is in the survey too
            if($attributefield!='token') {
                $fieldno++;
                $fields[] = array('id'=>"$prefix$fieldno",'name'=>mb_substr($attributefield, 0, 8),
                'qid'=>0,'code'=>'','SPSStype'=>'A','LStype'=>'Undef',
                'VariableLabel'=>$attributedescription['description'],'sql_name'=>$attributefield,'size'=>'100',
                'title'=>$attributefield,'hide'=>0, 'scale'=>'');
            }
        }
    }

    $tempArray = array();
    $fieldnames = Yii::app()->db->schema->getTable("{{survey_$iSurveyID}}")->getColumnNames();
    $num_results = count($fieldnames);
    $num_fields = $num_results;
    $diff = 0;
    $noQID = Array('id', 'token', 'datestamp', 'submitdate', 'startdate', 'startlanguage', 'ipaddr', 'refurl', 'lastpage');
    # Build array that has to be returned
    for ($i=0; $i < $num_results; $i++) {
        #Condition for SPSS fields:
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
            $fieldtype = 'F';
            $val_size = 7; //Arbitrarilty restrict to 9,999,999 (7 digits) pages
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
        $answers = SPSSGetValues($tempArray, $aQuestionAttribs, $language);
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
* @param
* @return CDbCommand
*/
function SPSSGetQuery($iSurveyID, $limit = null, $offset = null) {

    $bDataAnonymized=(Survey::model()->findByPk($iSurveyID)->anonymized=='Y');
    $tokensexist=tableExists('tokens_'.$iSurveyID);

    #See if tokens are being used
    $query = App()->db->createCommand();
    $query->from('{{survey_' . $iSurveyID . '}} s');
    $columns = array('s.*');
    if (isset($tokensexist) && $tokensexist == true && !$bDataAnonymized && Permission::model()->hasSurveyPermission($iSurveyID,'tokens','read')) {
        $tokenattributes=array_keys(getTokenFieldsAndNames($iSurveyID,false));
        foreach ($tokenattributes as $attributefield) {
            //Drop the token field, since it is in the survey too
            if($attributefield!='token') {
                $columns[] = 't.' . $attributefield;
            }
        }

        $query->leftJoin('{{tokens_' . $iSurveyID . '}} t',  App()->db->quoteColumnName('s.token') . ' = ' .  App()->db->quoteColumnName('t.token'));
        //LEFT JOIN {{tokens_$iSurveyID}} t ON ";
    }
    $query->select($columns);
    switch (incompleteAnsFilterState()) {
        case 'incomplete':
            //Inclomplete answers only
            $query->where('s.submitdate IS NULL');
            break;
        case 'complete':
            //Inclomplete answers only
            $query->where('s.submitdate IS NOT NULL');
            break;
    }

    if (!empty($limit) & !is_null($offset))
    {
        $query->limit((int) $limit,  (int) $offset);
    }

    return $query;
}

/**
* buildXMLFromQuery() creates a datadump of a table in XML using XMLWriter
*
* @param mixed $xmlwriter  The existing XMLWriter object
* @param mixed $Query  The table query to build from
* @param mixed $tagname  If the XML tag of the resulting question should be named differently than the table name set it here
* @param array $excludes array of columnames not to include in export
*/
function buildXMLFromQuery($xmlwriter, $Query, $tagname='', $excludes = array())
{
    $iChunkSize=3000; // This works even for very large result sets and leaves a minimal memory footprint

    preg_match('/\bfrom\b\s*{{(\w+)}}/i', $Query, $MatchResults);
    if ($tagname!='')
    {
        $TableName=$tagname;
    }
    else
    {
        $TableName = $MatchResults[1];
    }



    // Read table in smaller chunks
    $iStart=0;
    do
    {
        $QueryResult = Yii::app()->db->createCommand($Query)->limit($iChunkSize, $iStart)->query();
        $result = $QueryResult->readAll();
        if ($iStart==0 && count($result)>0)
        {
            $exclude = array_flip($excludes);    //Flip key/value in array for faster checks
            $xmlwriter->startElement($TableName);
            $xmlwriter->startElement('fields');
            $aColumninfo = array_keys($result[0]);
            foreach ($aColumninfo as $fieldname)
            {
                if (!isset($exclude[$fieldname])) $xmlwriter->writeElement('fieldname',$fieldname);
            }
            $xmlwriter->endElement(); // close columns
            $xmlwriter->startElement('rows');
        }
        foreach($result as $Row)
        {
            $xmlwriter->startElement('row');
            foreach ($Row as $Key=>$Value)
            {
                if (!isset($exclude[$Key])) {
                    if(!(is_null($Value))) // If the $value is null don't output an element at all
                    {
                        if (is_numeric($Key[0])) $Key='_'.$Key; // mask invalid element names with an underscore
                        $Key=str_replace('#','-',$Key);
                        if (!$xmlwriter->startElement($Key)) safeDie('Invalid element key: '.$Key);
                        // Remove invalid XML characters
                        if ($Value!=='') {
                            $Value=str_replace(']]>','',$Value);
                            $xmlwriter->writeCData(preg_replace('/[^\x9\xA\xD\x20-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u','',$Value));
                        }
                        $xmlwriter->endElement();
                    }
                }
            }
            $xmlwriter->endElement(); // close row
        }
        $iStart=$iStart+$iChunkSize;
    } while (count($result)==$iChunkSize);
    if (count($result)>0)
    {
        $xmlwriter->endElement(); // close rows
        $xmlwriter->endElement(); // close tablename
    }
}

/**
* from export_structure_xml.php
*/
function surveyGetXMLStructure($iSurveyID, $xmlwriter, $exclude=array())
{
    $sdump = "";
    if (!isset($exclude['answers']))
    {
        //Answer table
        $aquery = "SELECT {{answers}}.*
        FROM {{answers}}, {{questions}}
        WHERE {{answers}}.language={{questions}}.language
        AND {{answers}}.qid={{questions}}.qid
        AND {{questions}}.sid=$iSurveyID";
        buildXMLFromQuery($xmlwriter,$aquery);
    }

    // Assessments
    $query = "SELECT {{assessments}}.*
    FROM {{assessments}}
    WHERE {{assessments}}.sid=$iSurveyID";
    buildXMLFromQuery($xmlwriter,$query);

    if (!isset($exclude['conditions']))
    {
        //Condition table
        $cquery = "SELECT DISTINCT {{conditions}}.*
        FROM {{conditions}}, {{questions}}
        WHERE {{conditions}}.qid={{questions}}.qid
        AND {{questions}}.sid=$iSurveyID";
        buildXMLFromQuery($xmlwriter,$cquery);
    }

    //Default values
    $query = "SELECT {{defaultvalues}}.*
    FROM {{defaultvalues}} JOIN {{questions}} ON {{questions}}.qid = {{defaultvalues}}.qid AND {{questions}}.sid=$iSurveyID AND {{questions}}.language={{defaultvalues}}.language ";

    buildXMLFromQuery($xmlwriter,$query);

    // QuestionGroup
    $gquery = "SELECT *
    FROM {{groups}}
    WHERE sid=$iSurveyID
    ORDER BY gid";
    buildXMLFromQuery($xmlwriter,$gquery);

    //Questions
    $qquery = "SELECT *
    FROM {{questions}}
    WHERE sid=$iSurveyID and parent_qid=0
    ORDER BY qid";
    buildXMLFromQuery($xmlwriter,$qquery);

    //Subquestions
    $qquery = "SELECT *
    FROM {{questions}}
    WHERE sid=$iSurveyID and parent_qid>0
    ORDER BY qid";
    buildXMLFromQuery($xmlwriter,$qquery,'subquestions');

    //Question attributes
    $sBaseLanguage=Survey::model()->findByPk($iSurveyID)->language;
    $platform = Yii::app()->db->getDriverName();
    if ($platform == 'mssql' || $platform =='sqlsrv' || $platform =='dblib')
    {
        $query="SELECT qa.qid, qa.attribute, cast(qa.value as varchar(4000)) as value, qa.language
        FROM {{question_attributes}} qa JOIN {{questions}}  q ON q.qid = qa.qid AND q.sid={$iSurveyID}
        where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute,  cast(qa.value as varchar(4000)), qa.language";
    }
    else {
        $query="SELECT qa.qid, qa.attribute, qa.value, qa.language
        FROM {{question_attributes}} qa JOIN {{questions}}  q ON q.qid = qa.qid AND q.sid={$iSurveyID}
        where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute, qa.value, qa.language";
    }

    buildXMLFromQuery($xmlwriter,$query,'question_attributes');

    if (!isset($exclude['quotas']))
    {
        //Quota
        $query = "SELECT {{quota}}.*
        FROM {{quota}}
        WHERE {{quota}}.sid=$iSurveyID";
        buildXMLFromQuery($xmlwriter,$query);

        //1Quota members
        $query = "SELECT {{quota_members}}.*
        FROM {{quota_members}}
        WHERE {{quota_members}}.sid=$iSurveyID";
        buildXMLFromQuery($xmlwriter,$query);

        //Quota languagesettings
        $query = "SELECT {{quota_languagesettings}}.*
        FROM {{quota_languagesettings}}, {{quota}}
        WHERE {{quota}}.id = {{quota_languagesettings}}.quotals_quota_id
        AND {{quota}}.sid=$iSurveyID";
        buildXMLFromQuery($xmlwriter,$query);
    }

    // Surveys
    $squery = "SELECT *
    FROM {{surveys}}
    WHERE sid=$iSurveyID";
    //Exclude some fields from the export
    buildXMLFromQuery($xmlwriter,$squery,'',array('owner_id','active','datecreated'));

    // Survey language settings
    $slsquery = "SELECT *
    FROM {{surveys_languagesettings}}
    WHERE surveyls_survey_id=$iSurveyID";
    buildXMLFromQuery($xmlwriter,$slsquery);

    // Survey url parameters
    $slsquery = "SELECT *
    FROM {{survey_url_parameters}}
    WHERE sid={$iSurveyID}";
    buildXMLFromQuery($xmlwriter,$slsquery);

}

/**
* from export_structure_xml.php
*/
function surveyGetXMLData($iSurveyID, $exclude = array())
{
    $xml = getXMLWriter();
    $xml->openMemory();
    $xml->setIndent(true);
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('document');
    $xml->writeElement('LimeSurveyDocType','Survey');
    $xml->writeElement('DBVersion',getGlobalSetting("DBVersion"));
    $xml->startElement('languages');
    $surveylanguages=Survey::model()->findByPk($iSurveyID)->additionalLanguages;
    $surveylanguages[]=Survey::model()->findByPk($iSurveyID)->language;
    foreach ($surveylanguages as $surveylanguage)
    {
        $xml->writeElement('language',$surveylanguage);
    }
    $xml->endElement();
    surveyGetXMLStructure($iSurveyID, $xml,$exclude);
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
    $xml->writeElement('DBVersion',getGlobalSetting("DBVersion"));
    $xml->startElement('languages');
    $aSurveyLanguages=Survey::model()->findByPk($iSurveyID)->additionalLanguages;
    $aSurveyLanguages[]=Survey::model()->findByPk($iSurveyID)->language;
    foreach ($aSurveyLanguages as $sSurveyLanguage)
    {
        $xml->writeElement('language',$sSurveyLanguage);
    }
    $xml->endElement();
    $aquery = "SELECT * FROM {{{$sTableName}}}";

    buildXMLFromQuery($xml,$aquery, $sXMLTableTagName);
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
function QueXMLCleanup($string,$allow = '<p><b><u><i><em>')
{
    return str_replace("&","&amp;",html_entity_decode(trim(strip_tags(str_ireplace("<br />","\n",$string),$allow)),ENT_QUOTES,'UTF-8'));
}

/**
* from export_structure_quexml.php
*/
function QueXMLCreateFree($f,$len,$lab="")
{
    global $dom;
    $free = $dom->createElement("free");

    $format = $dom->createElement("format",QueXMLCleanup($f));

    $length = $dom->createElement("length",QueXMLCleanup($len));

    $label = $dom->createElement("label",QueXMLCleanup($lab));

    $free->appendChild($format);
    $free->appendChild($length);
    $free->appendChild($label);


    return $free;
}

/**
* from export_structure_quexml.php
*/
function QueXMLFixedArray($array)
{
    global $dom;
    $fixed = $dom->createElement("fixed");

    foreach ($array as $key => $v)
    {
        $category = $dom->createElement("category");

        $label = $dom->createElement("label",QueXMLCleanup("$key",''));

        $value= $dom->createElement("value",QueXMLCleanup("$v",''));

        $category->appendChild($label);
        $category->appendChild($value);

        $fixed->appendChild($category);
    }


    return $fixed;
}

/**
* Calculate if this item should have a QueXMLSkipTo element attached to it
*
* from export_structure_quexml.php
*
* @param mixed $qid
* @param mixed $value
*
* @return bool|string Text of item to skip to otherwise false if nothing to skip to
* @author Adam Zammit <adam.zammit@acspri.org.au>
* @since  2010-10-28
* @TODO Correctly handle conditions in a database agnostic way
*/
function QueXMLSkipTo($qid,$value,$cfieldname = "")
{
    return false;
}

/**
* from export_structure_quexml.php
*/
function QueXMLCreateFixed($qid,$rotate=false,$labels=true,$scale=0,$other=false,$varname="")
{
    global $dom;

    global $quexmllang;
    $qlang = new limesurvey_lang($quexmllang);

    if ($labels)
        $Query = "SELECT * FROM {{labels}} WHERE lid = $labels  AND language='$quexmllang' ORDER BY sortorder ASC";
    else
        $Query = "SELECT code,answer as title,sortorder FROM {{answers}} WHERE qid = $qid AND scale_id = $scale  AND language='$quexmllang' ORDER BY sortorder ASC";

    $QueryResult = Yii::app()->db->createCommand($Query)->query();

    $fixed = $dom->createElement("fixed");

    $nextcode = "";

    foreach($QueryResult->readAll() as $Row)
    {
        $category = $dom->createElement("category");

        $label = $dom->createElement("label",QueXMLCleanup($Row['title'],''));

        $value= $dom->createElement("value",QueXMLCleanup($Row['code']));

        $category->appendChild($label);
        $category->appendChild($value);

        $st = QueXMLSkipTo($qid,$Row['code']);
        if ($st !== false)
        {
            $quexml_skipto = $dom->createElement("quexml_skipto",$st);
            $category->appendChild($quexml_skipto);
        }


        $fixed->appendChild($category);
        $nextcode = $Row['code'];
    }

    if ($other)
    {
        $category = $dom->createElement("category");

        $label = $dom->createElement("label",quexml_get_lengthth($qid,"other_replace_text",$qlang->gT("Other")));

        $value= $dom->createElement("value",'-oth-');

        $category->appendChild($label);
        $category->appendChild($value);

        $contingentQuestion = $dom->createElement("contingentQuestion");
        $length = $dom->createElement("length",24);
        $text = $dom->createElement("text",quexml_get_lengthth($qid,"other_replace_text",$qlang->gT("Other")));

        $contingentQuestion->appendChild($text);
        $contingentQuestion->appendChild($length);
        $contingentQuestion->setAttribute("varName",$varname . 'other');

        $category->appendChild($contingentQuestion);

        $fixed->appendChild($category);
    }

    if ($rotate) $fixed->setAttribute("rotate","true");

    return $fixed;
}

/**
* from export_structure_quexml.php
*/
function quexml_get_lengthth($qid,$attribute,$default, $quexmllang=false)
{
    global $dom;
    if ($quexmllang!=false)
        $Query = "SELECT value FROM {{question_attributes}} WHERE qid = $qid AND language='$quexmllang' AND attribute='$attribute'";
    else
        $Query = "SELECT value FROM {{question_attributes}} WHERE qid = $qid AND attribute='$attribute'";

    //$QueryResult = mysql_query($Query) or die ("ERROR: $QueryResult<br />".mysql_error());
    $QueryResult = Yii::app()->db->createCommand($Query)->query();

    $Row = $QueryResult->read();
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
    global $quexmllang ;
    global $iSurveyID;
    $qlang = new limesurvey_lang($quexmllang);


    $Query = "SELECT * FROM {{questions}} WHERE parent_qid = $qid  AND language='$quexmllang' ";
    if ($scale_id != false) $Query .= " AND scale_id = $scale_id ";
    $Query .= " ORDER BY question_order ASC";
    //$QueryResult = mysql_query($Query) or die ("ERROR: $QueryResult<br />".mysql_error());
    $QueryResult = Yii::app()->db->createCommand($Query)->query();

    $nextcode = "";

    foreach($QueryResult->readAll() as $Row)
    {
        $response = $dom->createElement("response");
        if ($free == false)
        {
            $fixed = $dom->createElement("fixed");
            $category = $dom->createElement("category");

            $label = $dom->createElement("label",QueXMLCleanup($Row['question'],''));

            $value= $dom->createElement("value",1);
            $nextcode = $Row['title'];

            $category->appendChild($label);
            $category->appendChild($value);

            $st = QueXMLSkipTo($qid,'Y'," AND c.cfieldname LIKE '+$iSurveyID" . "X" . $Row['gid'] . "X" . $qid . $Row['title'] . "' ");
            if ($st !== false)
            {
                $quexml_skipto = $dom->createElement("skipTo",$st);
                $category->appendChild($quexml_skipto);
            }


            $fixed->appendChild($category);

            $response->appendChild($fixed);
        }
        else
            $response->appendChild(QueXMLCreateFree($free['f'],$free['len'],$Row['question']));

        $response->setAttribute("varName",QueXMLCleanup($Row['title']));

        $question->appendChild($response);
    }

    if ($other && $free==false)
    {
        $response = $dom->createElement("response");
        $fixed = $dom->createElement("fixed");
        $category = $dom->createElement("category");

        $label = $dom->createElement("label",quexml_get_lengthth($qid,"other_replace_text",$qlang->gT("Other")));

        $value= $dom->createElement("value",1);

        //Get next code
        if (is_numeric($nextcode))
            $nextcode++;
        else if (is_string($nextcode))
                $nextcode = chr(ord($nextcode) + 1);

        $category->appendChild($label);
        $category->appendChild($value);

        $contingentQuestion = $dom->createElement("contingentQuestion");
        $length = $dom->createElement("length",24);
        $text = $dom->createElement("text",quexml_get_lengthth($qid,"other_replace_text",$qlang->gT("Other")));

        $contingentQuestion->appendChild($text);
        $contingentQuestion->appendChild($length);
        $contingentQuestion->setAttribute("varName",$varname . 'other');

        $category->appendChild($contingentQuestion);

        $fixed->appendChild($category);
        $response->appendChild($fixed);
        $response->setAttribute("varName",$varname . QueXMLCleanup($nextcode));

        $question->appendChild($response);
    }




    return;

}

/**
* from export_structure_quexml.php
*/
function quexml_create_subQuestions(&$question,$qid,$varname,$use_answers = false)
{
    global $dom;
    global $quexmllang ;

    if ($use_answers)
        $Query = "SELECT answer as question, code as title FROM {{answers}} WHERE qid = $qid  AND language='$quexmllang' ORDER BY sortorder ASC";
    else
        $Query = "SELECT * FROM {{questions}} WHERE parent_qid = $qid and scale_id = 0  AND language='$quexmllang' ORDER BY question_order ASC";
    $QueryResult = Yii::app()->db->createCommand($Query)->query();
    foreach($QueryResult->readAll() as $Row)
    {
        $subQuestion = $dom->createElement("subQuestion");
        $text = $dom->createElement("text",QueXMLCleanup($Row['question'],''));
        $subQuestion->appendChild($text);
        $subQuestion->setAttribute("varName",$varname .'_'. QueXMLCleanup($Row['title']));
        $question->appendChild($subQuestion);
    }

    return;
}

/**
* Export quexml survey.
*/
function quexml_export($surveyi, $quexmllan)
{
    global $dom, $quexmllang, $iSurveyID;
    $quexmllang = $quexmllan;
    $iSurveyID = $surveyi;

    $qlang = new limesurvey_lang($quexmllang);

    $dom = new DOMDocument('1.0','UTF-8');

    //Title and survey id
    $questionnaire = $dom->createElement("questionnaire");

    $Query = "SELECT * FROM {{surveys}},{{surveys_languagesettings}} WHERE sid=$iSurveyID and surveyls_survey_id=sid and surveyls_language='".$quexmllang."'";
    $QueryResult = Yii::app()->db->createCommand($Query)->query();
    $Row = $QueryResult->read();
    $questionnaire->setAttribute("id", $Row['sid']);
    $title = $dom->createElement("title",QueXMLCleanup($Row['surveyls_title']));
    $questionnaire->appendChild($title);

    //investigator and datacollector
    $investigator = $dom->createElement("investigator");
    $name = $dom->createElement("name");
    $name = $dom->createElement("firstName");
    $name = $dom->createElement("lastName");
    $dataCollector = $dom->createElement("dataCollector");

    $questionnaire->appendChild($investigator);
    $questionnaire->appendChild($dataCollector);

    //questionnaireInfo == welcome
    if (!empty($Row['surveyls_welcometext']))
    {
        $questionnaireInfo = $dom->createElement("questionnaireInfo");
        $position = $dom->createElement("position","before");
        $text = $dom->createElement("text",QueXMLCleanup($Row['surveyls_welcometext']));
        $administration = $dom->createElement("administration","self");
        $questionnaireInfo->appendChild($position);
        $questionnaireInfo->appendChild($text);
        $questionnaireInfo->appendChild($administration);
        $questionnaire->appendChild($questionnaireInfo);
    }

    if (!empty($Row['surveyls_endtext']))
    {
        $questionnaireInfo = $dom->createElement("questionnaireInfo");
        $position = $dom->createElement("position","after");
        $text = $dom->createElement("text",QueXMLCleanup($Row['surveyls_endtext']));
        $administration = $dom->createElement("administration","self");
        $questionnaireInfo->appendChild($position);
        $questionnaireInfo->appendChild($text);
        $questionnaireInfo->appendChild($administration);
        $questionnaire->appendChild($questionnaireInfo);
    }



    //section == group


    $Query = "SELECT * FROM {{groups}} WHERE sid=$iSurveyID AND language='$quexmllang' order by group_order ASC";
    $QueryResult = Yii::app()->db->createCommand($Query)->query();

    //for each section
    foreach($QueryResult->readAll() as $Row)
    {
        $gid = $Row['gid'];

        $section = $dom->createElement("section");

        if (!empty($Row['group_name']))
        {
            $sectionInfo = $dom->createElement("sectionInfo");
            $position = $dom->createElement("position","title");
            $text = $dom->createElement("text",QueXMLCleanup($Row['group_name']));
            $administration = $dom->createElement("administration","self");
            $sectionInfo->appendChild($position);
            $sectionInfo->appendChild($text);
            $sectionInfo->appendChild($administration);
            $section->appendChild($sectionInfo);
        }


        if (!empty($Row['description']))
        {
            $sectionInfo = $dom->createElement("sectionInfo");
            $position = $dom->createElement("position","before");
            $text = $dom->createElement("text",QueXMLCleanup($Row['description']));
            $administration = $dom->createElement("administration","self");
            $sectionInfo->appendChild($position);
            $sectionInfo->appendChild($text);
            $sectionInfo->appendChild($administration);
            $section->appendChild($sectionInfo);
        }



        $section->setAttribute("id", $gid);

        //boilerplate questions convert to sectionInfo elements
        $Query = "SELECT * FROM {{questions}} WHERE sid=$iSurveyID AND gid = $gid AND type LIKE 'X'  AND language='$quexmllang' ORDER BY question_order ASC";
        $QR = Yii::app()->db->createCommand($Query)->query();
        foreach($QR->readAll() as $RowQ)
        {
            $sectionInfo = $dom->createElement("sectionInfo");
            $position = $dom->createElement("position","before");
            $text = $dom->createElement("text",QueXMLCleanup($RowQ['question']));
            $administration = $dom->createElement("administration","self");

            $sectionInfo->appendChild($position);
            $sectionInfo->appendChild($text);
            $sectionInfo->appendChild($administration);

            $section->appendChild($sectionInfo);
        }



        //foreach question
        $Query = "SELECT * FROM {{questions}} WHERE sid=$iSurveyID AND gid = $gid AND parent_qid=0 AND language='$quexmllang' AND type NOT LIKE 'X' ORDER BY question_order ASC";
        $QR = Yii::app()->db->createCommand($Query)->query();
        foreach($QR->readAll() as $RowQ)
        {
            $question = $dom->createElement("question");
            $type = $RowQ['type'];
            $qid = $RowQ['qid'];

            $other = false;
            if ($RowQ['other'] == 'Y') $other = true;

            //create a new text element for each new line
            $questiontext = explode('<br />',$RowQ['question']);
            foreach ($questiontext as $qt)
            {
                $txt = QueXMLCleanup($qt);
                if (!empty($txt))
                {
                    $text = $dom->createElement("text",$txt);
                    $question->appendChild($text);
                }
            }


            //directive
            if (!empty($RowQ['help']))
            {
                $directive = $dom->createElement("directive");
                $position = $dom->createElement("position","during");
                $text = $dom->createElement("text",QueXMLCleanup($RowQ['help']));
                $administration = $dom->createElement("administration","self");

                $directive->appendChild($position);
                $directive->appendChild($text);
                $directive->appendChild($administration);

                $question->appendChild($directive);
            }

			if (Yii::app()->getConfig('quexmlshowprintablehelp')==true)
			{

				$RowQ['printable_help']=quexml_get_lengthth($qid,"printable_help","", $quexmllang);

				if (!empty($RowQ['printable_help']))
				{
					$directive = $dom->createElement("directive");
					$position = $dom->createElement("position","before");
					$text = $dom->createElement("text", '['.$qlang->gT('Only answer the following question if:')." ".QueXMLCleanup($RowQ['printable_help'])."]");
					$administration = $dom->createElement("administration","self");
					$directive->appendChild($position);
					$directive->appendChild($text);
					$directive->appendChild($administration);
					$question->appendChild($directive);
				}
			}

            $response = $dom->createElement("response");
            $sgq = $RowQ['title'];
            $response->setAttribute("varName",$sgq);

            switch ($type)
            {
                case "X": //BOILERPLATE QUESTION - none should appear

                    break;
                case "5": //5 POINT CHOICE radio-buttons
                    $response->appendChild(QueXMLFixedArray(array("1" => 1,"2" => 2,"3" => 3,"4" => 4,"5" => 5)));
                    $question->appendChild($response);
                    break;
                case "D": //DATE
                    $response->appendChild(QueXMLCreateFree("date","8",""));
                    $question->appendChild($response);
                    break;
                case "L": //LIST drop-down/radio-button list
                    $response->appendChild(QueXMLCreateFixed($qid,false,false,0,$other,$sgq));
                    $question->appendChild($response);
                    break;
                case "!": //List - dropdown
                    $response->appendChild(QueXMLCreateFixed($qid,false,false,0,$other,$sgq));
                    $question->appendChild($response);
                    break;
                case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
                    quexml_create_subQuestions($question,$qid,$sgq);
                    $response = $dom->createElement("response");
                    $response->setAttribute("varName",QueXMLCleanup($sgq));
                    $response->appendChild(QueXMLCreateFixed($qid,false,false,0,$other,$sgq));

                    $response2 = $dom->createElement("response");
                    $response2->setAttribute("varName",QueXMLCleanup($sgq) . "_comment");
                    $response2->appendChild(QueXMLCreateFree("longtext","40",""));

                    $question->appendChild($response);
                    $question->appendChild($response2);
                    break;
                case "R": //RANKING STYLE
                    quexml_create_subQuestions($question,$qid,$sgq,true);
                    $Query = "SELECT COUNT(*) as sc FROM {{answers}} WHERE qid = $qid AND language='$quexmllang' ";
                    $QRE = Yii::app()->db->createCommand($Query)->query();
                    //$QRE = mysql_query($Query) or die ("ERROR: $QRE<br />".mysql_error());
                    //$QROW = mysql_fetch_assoc($QRE);
                    $QROW = $QRE->read();
                    $response->appendChild(QueXMLCreateFree("integer",strlen($QROW['sc']),""));
                    $question->appendChild($response);
                    break;
                case "M": //Multiple choice checkbox
                    quexml_create_multi($question,$qid,$sgq,false,false,$other);
                    break;
                case "P": //Multiple choice with comments checkbox + text
                    //Not yet implemented
                    quexml_create_multi($question,$qid,$sgq,false,false,$other);
                    //no comments added
                    break;
                case "Q": //MULTIPLE SHORT TEXT
                    quexml_create_subQuestions($question,$qid,$sgq);
                    $response->appendChild(QueXMLCreateFree("text",quexml_get_lengthth($qid,"maximum_chars","10"),""));
                    $question->appendChild($response);
                    break;
                case "K": //MULTIPLE NUMERICAL
                    quexml_create_subQuestions($question,$qid,$sgq);
                    $response->appendChild(QueXMLCreateFree("integer",quexml_get_lengthth($qid,"maximum_chars","10"),""));
                    $question->appendChild($response);
                    break;
                case "N": //NUMERICAL QUESTION TYPE
                    $response->appendChild(QueXMLCreateFree("integer",quexml_get_lengthth($qid,"maximum_chars","10"),""));
                    $question->appendChild($response);
                    break;
                case "S": //SHORT FREE TEXT
                    // default is fieldlength of 24 characters.
                    $response->appendChild(QueXMLCreateFree("text",quexml_get_lengthth($qid,"maximum_chars","24"),""));
                    $question->appendChild($response);
                    break;
                case "T": //LONG FREE TEXT
                    $response->appendChild(QueXMLCreateFree("longtext",quexml_get_lengthth($qid,"display_rows","40"),""));
                    $question->appendChild($response);
                    break;
                case "U": //HUGE FREE TEXT
                    $response->appendChild(QueXMLCreateFree("longtext",quexml_get_lengthth($qid,"display_rows","80"),""));
                    $question->appendChild($response);
                    break;
                case "Y": //YES/NO radio-buttons
                    $response->appendChild(QueXMLFixedArray(array($qlang->gT("Yes") => 'Y',$qlang->gT("No") => 'N')));
                    $question->appendChild($response);
                    break;
                case "G": //GENDER drop-down list
                    $response->appendChild(QueXMLFixedArray(array($qlang->gT("Female") => 'F',$qlang->gT("Male") => 'M')));
                    $question->appendChild($response);
                    break;
                case "A": //ARRAY (5 POINT CHOICE) radio-buttons
                    quexml_create_subQuestions($question,$qid,$sgq);
                    $response->appendChild(QueXMLFixedArray(array("1" => 1,"2" => 2,"3" => 3,"4" => 4,"5" => 5)));
                    $question->appendChild($response);
                    break;
                case "B": //ARRAY (10 POINT CHOICE) radio-buttons
                    quexml_create_subQuestions($question,$qid,$sgq);
                    $response->appendChild(QueXMLFixedArray(array("1" => 1,"2" => 2,"3" => 3,"4" => 4,"5" => 5,"6" => 6,"7" => 7,"8" => 8,"9" => 9,"10" => 10)));
                    $question->appendChild($response);
                    break;
                case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                    quexml_create_subQuestions($question,$qid,$sgq);
                    $response->appendChild(QueXMLFixedArray(array($qlang->gT("Yes") => 'Y',$qlang->gT("Uncertain") => 'U',$qlang->gT("No") => 'N')));
                    $question->appendChild($response);
                    break;
                case "E": //ARRAY (Increase/Same/Decrease) radio-buttons
                    quexml_create_subQuestions($question,$qid,$sgq);
                    $response->appendChild(QueXMLFixedArray(array($qlang->gT("Increase") => 'I',$qlang->gT("Same") => 'S',$qlang->gT("Decrease") => 'D')));
                    $question->appendChild($response);
                    break;
                case "F": //ARRAY (Flexible) - Row Format
                    //select subQuestions from answers table where QID
                    quexml_create_subQuestions($question,$qid,$sgq);
                    $response->appendChild(QueXMLCreateFixed($qid,false,false,0,$other,$sgq));
                    $question->appendChild($response);
                    //select fixed responses from
                    break;
                case "H": //ARRAY (Flexible) - Column Format
                    quexml_create_subQuestions($question,$RowQ['qid'],$sgq);
                    $response->appendChild(QueXMLCreateFixed($qid,true,false,0,$other,$sgq));
                    $question->appendChild($response);
                    break;
                case "1": //Dualscale multi-flexi array
                    //select subQuestions from answers table where QID
                    quexml_create_subQuestions($question,$qid,$sgq);
                    //get the header of the first scale of the dual scale question
                    $Query = "SELECT value FROM {{question_attributes}} WHERE qid = $qid AND language='$quexmllang' AND attribute='dualscale_headerA'";
                    $QRE = Yii::app()->db->createCommand($Query)->query();
                    $QROW = $QRE->read();
                    $response = $dom->createElement("response");
                    if ($QROW['value'])
                        $response->setAttribute("varName",QueXMLCleanup($QROW['value']));
                    $response->appendChild(QueXMLCreateFixed($qid,false,false,0,$other,$sgq));

                    //get the header of the second scale of the dual scale question
                    $Query = "SELECT value FROM {{question_attributes}} WHERE qid = $qid AND language='$quexmllang' AND attribute='dualscale_headerB'";
                    $QRE = Yii::app()->db->createCommand($Query)->query();
                    $QROW = $QRE->read();
                    $response2 = $dom->createElement("response");
                    if ($QROW['value'])
                        $response2->setAttribute("varName",QueXMLCleanup($QROW['value']));
                    $response2->appendChild(QueXMLCreateFixed($qid,false,false,1,$other,$sgq));
                    $question->appendChild($response);
                    $question->appendChild($response2);
                    break;
                case ":": //multi-flexi array numbers
                    quexml_create_subQuestions($question,$qid,$sgq);
                    //get multiflexible_checkbox - if set then each box is a checkbox (single fixed response)
                    $mcb  = quexml_get_lengthth($qid,'multiflexible_checkbox',-1);
                    if ($mcb != -1)
                        quexml_create_multi($question,$qid,$sgq,1);
                    else
                    {
                        //get multiflexible_max and maximum_chars - if set then make boxes of max of these widths
                        $mcm = max(quexml_get_lengthth($qid,'maximum_chars',1), strlen(quexml_get_lengthth($qid,'multiflexible_max',1)));
                        quexml_create_multi($question,$qid,$sgq,1,array('f' => 'integer', 'len' => $mcm, 'lab' => ''));
                    }
                    break;
                case ";": //multi-flexi array text
                    quexml_create_subQuestions($question,$qid,$sgq);
                    //foreach question where scale_id = 1 this is a textbox
                    quexml_create_multi($question,$qid,$sgq,1,array('f' => 'text', 'len' => quexml_get_lengthth($qid,'maximum_chars',10), 'lab' => ''));
                    break;
                case "^": //SLIDER CONTROL - not supported
                    $response->appendChild(QueXMLFixedArray(array("NOT SUPPORTED:$type" => 1)));
                    $question->appendChild($response);
                    break;
            } //End Switch




            $section->appendChild($question);
        }


        $questionnaire->appendChild($section);
    }


    $dom->appendChild($questionnaire);

    $dom->formatOutput = true;
    return $dom->saveXML();
}

/**
* From adodb
*
* Different SQL databases used different methods to combine strings together.
* This function provides a wrapper.
*
* param s    variable number of string parameters
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

// DUMP THE RELATED DATA FOR A SINGLE QUESTION INTO A SQL FILE FOR IMPORTING LATER ON OR
// ON ANOTHER SURVEY SETUP DUMP ALL DATA WITH RELATED QID FROM THE FOLLOWING TABLES
// 1. questions
// 2. answers

function group_export($action, $iSurveyID, $gid)
{
    viewHelper::disableHtmlLogging();

    $fn = "limesurvey_group_$gid.lsg";
    $xml = getXMLWriter();

    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=$fn");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: cache");                // HTTP/1.0

    $xml->openUri('php://output');
    $xml->setIndent(true);
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('document');
    $xml->writeElement('LimeSurveyDocType','Group');
    $xml->writeElement('DBVersion', getGlobalSetting("DBVersion"));
    $xml->startElement('languages');

    $lresult = QuestionGroup::model()->findAllByAttributes(array('gid' => $gid), array('select'=>'language','group' => 'language'));
    foreach($lresult as $row)
    {
        $xml->writeElement('language',$row->language);
    }
    $xml->endElement();
    groupGetXMLStructure($xml,$gid);
    $xml->endElement(); // close columns
    $xml->endDocument();
}

function groupGetXMLStructure($xml,$gid)
{
    // QuestionGroup
    $gquery = "SELECT *
    FROM {{groups}}
    WHERE gid=$gid";
    buildXMLFromQuery($xml,$gquery);

    // Questions table
    $qquery = "SELECT *
    FROM {{questions}}
    WHERE gid=$gid and parent_qid=0 order by question_order, language, scale_id";
    buildXMLFromQuery($xml,$qquery);

    // Questions table - Subquestions
    $qquery = "SELECT *
    FROM {{questions}}
    WHERE gid=$gid and parent_qid>0 order by question_order, language, scale_id";
    buildXMLFromQuery($xml,$qquery,'subquestions');

    //Answer
    $aquery = "SELECT DISTINCT {{answers}}.*
    FROM {{answers}}, {{questions}}
    WHERE ({{answers}}.qid={{questions}}.qid)
    AND ({{questions}}.gid=$gid)";
    buildXMLFromQuery($xml,$aquery);

    //Condition - THIS CAN ONLY EXPORT CONDITIONS THAT RELATE TO THE SAME GROUP
    $cquery = "SELECT DISTINCT c.*
    FROM {{conditions}} c, {{questions}} q, {{questions}} b
    WHERE (c.cqid=q.qid)
    AND (c.qid=b.qid)
    AND (q.gid={$gid})
    AND (b.gid={$gid})";
    buildXMLFromQuery($xml,$cquery,'conditions');

    //Question attributes
    $iSurveyID=Yii::app()->db->createCommand("select sid from {{groups}} where gid={$gid}")->query()->read();
    $iSurveyID=$iSurveyID['sid'];
    $sBaseLanguage=Survey::model()->findByPk($iSurveyID)->language;
    $platform = Yii::app()->db->getDriverName();
    if ($platform == 'mssql' || $platform =='sqlsrv' || $platform =='dblib')
    {
        $query="SELECT qa.qid, qa.attribute, cast(qa.value as varchar(4000)) as value, qa.language
        FROM {{question_attributes}} qa JOIN {{questions}}  q ON q.qid = qa.qid AND q.sid={$iSurveyID} and q.gid={$gid}
        where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute,  cast(qa.value as varchar(4000)), qa.language";
    }
    else {
        $query="SELECT qa.qid, qa.attribute, qa.value, qa.language
        FROM {{question_attributes}} qa JOIN {{questions}}  q ON q.qid = qa.qid AND q.sid={$iSurveyID} and q.gid={$gid}
        where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute, qa.value, qa.language";
    }
    buildXMLFromQuery($xml,$query,'question_attributes');

    // Default values
    $query = "SELECT dv.*
    FROM {{defaultvalues}} dv
    JOIN {{questions}} ON {{questions}}.qid = dv.qid
    AND {{questions}}.language=dv.language
    AND {{questions}}.gid=$gid
    order by dv.language, dv.scale_id";
    buildXMLFromQuery($xml,$query,'defaultvalues');
}


// DUMP THE RELATED DATA FOR A SINGLE QUESTION INTO A SQL FILE FOR IMPORTING LATER ON OR
// ON ANOTHER SURVEY SETUP DUMP ALL DATA WITH RELATED QID FROM THE FOLLOWING TABLES
//  - Questions
//  - Answer
//  - Question attributes
//  - Default values
function questionExport($action, $iSurveyID, $gid, $qid)
{
    $fn = "limesurvey_question_$qid.lsq";
    $xml = getXMLWriter();

    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=$fn");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: cache");
    // HTTP/1.0
    $xml->openURI('php://output');

    $xml->setIndent(true);
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('document');
    $xml->writeElement('LimeSurveyDocType','Question');
    $xml->writeElement('DBVersion', getGlobalSetting('DBVersion'));
    $xml->startElement('languages');
    $aLanguages=Survey::model()->findByPk($iSurveyID)->additionalLanguages;
    $aLanguages[]=Survey::model()->findByPk($iSurveyID)->language;
    foreach ($aLanguages as $sLanguage)
    {
        $xml->writeElement('language',$sLanguage);
    }
    $xml->endElement();
    questionGetXMLStructure($xml,$gid,$qid);
    $xml->endElement(); // close columns
    $xml->endDocument();
    exit;
}

function questionGetXMLStructure($xml,$gid,$qid)
{
    // Questions table
    $qquery = "SELECT *
    FROM {{questions}}
    WHERE qid=$qid and parent_qid=0 order by language, scale_id, question_order";
    buildXMLFromQuery($xml,$qquery);

    // Questions table - Subquestions
    $qquery = "SELECT *
    FROM {{questions}}
    WHERE parent_qid=$qid order by language, scale_id, question_order";
    buildXMLFromQuery($xml,$qquery,'subquestions');


    // Answer table
    $aquery = "SELECT *
    FROM {{answers}}
    WHERE qid = $qid order by language, scale_id, sortorder";
    buildXMLFromQuery($xml,$aquery);



    // Question attributes
    $iSurveyID=Yii::app()->db->createCommand("select sid from {{groups}} where gid={$gid}")->query();
    $iSurveyID=$iSurveyID->read();
    $iSurveyID=$iSurveyID['sid'];
    $sBaseLanguage=Survey::model()->findByPk($iSurveyID)->language;
    $platform = Yii::app()->db->getDriverName();
    if ($platform == 'mssql' || $platform =='sqlsrv'|| $platform =='dblib')
    {
        $query="SELECT qa.qid, qa.attribute, cast(qa.value as varchar(4000)) as value, qa.language
        FROM {{question_attributes}} qa JOIN {{questions}}  q ON q.qid = qa.qid AND q.sid={$iSurveyID} and q.qid={$qid}
        where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute,  cast(qa.value as varchar(4000)), qa.language";
    }
    else {
        $query="SELECT qa.qid, qa.attribute, qa.value, qa.language
        FROM {{question_attributes}} qa JOIN {{questions}}  q ON q.qid = qa.qid AND q.sid={$iSurveyID} and q.qid={$qid}
        where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute, qa.value, qa.language";
    }
    buildXMLFromQuery($xml,$query);

    // Default values
    $query = "SELECT *
    FROM {{defaultvalues}}
    WHERE qid=$qid  order by language, scale_id";
    buildXMLFromQuery($xml,$query);

}


function tokensExport($iSurveyID)
{
    $sEmailFiter=trim(App()->request->getPost('filteremail'));
    $iTokenStatus=App()->request->getPost('tokenstatus');
    $iInvitationStatus=App()->request->getPost('invitationstatus');
    $iReminderStatus=App()->request->getPost('reminderstatus');
    $sTokenLanguage=App()->request->getPost('tokenlanguage');

    $oSurvey=Survey::model()->findByPk($iSurveyID);
    $bIsNotAnonymous= ($oSurvey->anonymized=='N' && $oSurvey->active=='Y');// db table exist (survey_$iSurveyID) ?

    $bquery = "SELECT * FROM {{tokens_$iSurveyID}} where 1=1";
    $databasetype = Yii::app()->db->getDriverName();
    if ($sEmailFiter!='')
    {
        if (in_array($databasetype, array('mssql', 'sqlsrv', 'dblib')))
        {
            $bquery .= ' and CAST(email as varchar) like '.dbQuoteAll('%'.$sEmailFiter.'%', true);
        }
        else
        {
            $bquery .= ' and email like '.dbQuoteAll('%'.$sEmailFiter.'%', true);
        }
    }
    if ($iTokenStatus==1)
    {
        $bquery .= " and completed<>'N'";
    }
    elseif ($iTokenStatus==2)
    {
        $bquery .= " and completed='N'";
        if ($bIsNotAnonymous)
        {
            $bquery .=" and token not in (select token from {{survey_$iSurveyID}} group by token)";
        }
    }
    if ($iTokenStatus==3 && $bIsNotAnonymous)
    {
        $bquery .= " and completed='N' and token in (select token from {{survey_$iSurveyID}} group by token)";
    }

    if ($iInvitationStatus==1)
    {
        $bquery .= " and sent<>'N'";
    }
    if ($iInvitationStatus==2)
    {
        $bquery .= " and sent='N'";
    }

    if ($iReminderStatus==1)
    {
        $bquery .= " and remindersent<>'N'";
    }
    if ($iReminderStatus==2)
    {
        $bquery .= " and remindersent='N'";
    }

    if ($sTokenLanguage!='')
    {
        $bquery .= " and language=".dbQuoteAll($sTokenLanguage);
    }
    $bquery .= " ORDER BY tid";
    Yii::app()->loadHelper('database');

    $bresult = Yii::app()->db->createCommand($bquery)->query(); //dbExecuteAssoc($bquery) is faster but deprecated!
    //HEADERS should be after the above query else timeout errors in case there are lots of tokens!
    header("Content-Disposition: attachment; filename=tokens_".$iSurveyID.".csv");
    header("Content-type: text/comma-separated-values; charset=UTF-8");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: cache");

    // Export UTF8 WITH BOM
    $tokenoutput = chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'));
    $tokenoutput .= "tid,firstname,lastname,email,emailstatus,token,language,validfrom,validuntil,invited,reminded,remindercount,completed,usesleft";
    $attrfieldnames = getAttributeFieldNames($iSurveyID);
    $attrfielddescr = getTokenFieldsAndNames($iSurveyID, true);
    foreach ($attrfieldnames as $attr_name)
    {
        $tokenoutput .=", $attr_name";
        if (isset($attrfielddescr[$attr_name]))
            $tokenoutput .=" <".str_replace(","," ",$attrfielddescr[$attr_name]['description']).">";
    }
    $tokenoutput .="\n";
    echo $tokenoutput;
    $tokenoutput="";

    // Export token line by line and fill $aExportedTokens with token exported
    Yii::import('application.libraries.Date_Time_Converter', true);
    $aExportedTokens = array();
    while ($brow = $bresult->read())
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
        echo $tokenoutput;
        $tokenoutput='';

        $aExportedTokens[] = $brow['tid'];
    }

    if (Yii::app()->request->getPost('tokendeleteexported') && !empty($aExportedTokens))
    {
		Token::model($iSurveyID)->deleteByPk($aExportedTokens);
    }
}

function CPDBExport($data,$filename)
{

    header("Content-Disposition: attachment; filename=".$filename.".csv");
    header("Content-type: text/comma-separated-values; charset=UTF-8");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: cache");
    $tokenoutput = chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'));

    foreach($data as $key=>$value)
    {
        foreach($value as $values)
        {
            $tokenoutput .= trim($values).',';
        }
        $tokenoutput = substr($tokenoutput,0,-1); // remove last comma
        $tokenoutput .= "\n";

    }
    echo $tokenoutput;
    exit;
}
