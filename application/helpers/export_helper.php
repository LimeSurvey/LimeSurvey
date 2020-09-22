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
* @return string
*/
function stripTagsFull($string)
{
    $string = flattenText($string,false,true); // stripo whole + html_entities
    $string = str_replace('-oth', '', $string);// Why ?
    //The backslashes must be escaped twice, once for php, and again for the regexp
    $string = str_replace("'|\\\\'", "&apos;", $string);
    return $string;
}

/**
* Returns true if passed $value is numeric
*
* @param $value
* @return bool
*/
function isNumericExtended($value)
{
    if (empty($value)) {
        return true;
    }
    $eng_or_world = preg_match('/^[+-]?'.// start marker and sign prefix
    '(((([0-9]+)|([0-9]{1,4}(,[0-9]{3,4})+)))?(\\.[0-9])?([0-9]*)|'.// american
    '((([0-9]+)|([0-9]{1,4}(\\.[0-9]{3,4})+)))?(,[0-9])?([0-9]*))'.// world
    '(e[0-9]+)?'.// exponent
    '$/', // end marker
    $value) == 1;
    return ($eng_or_world);
}

/**
* Returns splitted unicode string correctly
* source: http://www.php.net/manual/en/function.str-split.php#107658
*
* @param string $str
* @param $l
* @return string
*/
function strSplitUnicode($str, $l = 0)
{
    if ($l > 0) {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}

/**
* Quotes a string with surrounding quotes and masking inside quotes by doubling them
* 
* @param string $sText Text to quote
* @param string $sQuoteChar The quote character (Use ' for SPSS and " for R)
* @param string $aField General field information from SPSSFieldmap
*/
function quoteSPSS($sText,$sQuoteChar,$aField)
{            
   $sText=trim($sText);
   if ($sText=='') {
       return '';  
   }
   if (is_numeric($sText) && $aField['SPSStype']=='F') {
       $iDecimals=0;
       if (strpos($aField['size'],'.')>0) {
         $iDecimals=substr($aField['size'],strpos($aField['size'],'.')+1);
       }
       return number_format($sText,$iDecimals,'.','');
   }                                          
   return $sQuoteChar.str_replace($sQuoteChar, $sQuoteChar.$sQuoteChar, $sText).$sQuoteChar; 
}


/**
 * Exports CSV response data for SPSS and R
 *
 * @param mixed $iSurveyID The survey ID
 * @param string $iLength Maximum text lenght data, usually 255 for SPSS <v16 and 16384 for SPSS 16 and later
 * @param string $na Value for N/A data
 * @param string $q sep Quote separator. Use ' for SPSS, " for R
 * @param bool $header logical $header If TRUE, adds SQGA code as column headings (used by export to R)
 * @param string $sLanguage
 */
function SPSSExportData($iSurveyID, $iLength, $na = '', $q = '\'', $header = false, $sLanguage = '')
{
    // Build array that has to be returned
    $fields = SPSSFieldMap($iSurveyID, 'V', $sLanguage);
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
            $num_fields = safecount($row);
            // Add column headers (used by R export)
            if ($header == true) {
                $i = 1;
                foreach ($fields as $field) {
                    if (!$field['hide']) {
                        echo quoteSPSS(strtoupper($field['sql_name']),$q,$field);
                    }
                    if ($i < $num_fields && !$field['hide']) {
                        echo ',';
                    }
                    $i++;
                }
                echo("\n");
            }
        }
        $row = array_change_key_case($row, CASE_UPPER);
        reset($fields); //Jump to the first element in the field array
        $i = 1;
        foreach ($fields as $field) {
            if ($field['hide'] == 1){
                $i++; 
                continue;
            }
            $fieldno = strtoupper($field['sql_name']);
            if ($field['SPSStype'] == 'DATETIME23.2') {
                // convert mysql datestamp (yyyy-mm-dd hh:mm:ss) to SPSS datetime (dd-mmm-yyyy hh:mm:ss) format
                if (isset($row[$fieldno])) {
                    list($year, $month, $day, $hour, $minute, $second) = preg_split('([^0-9])', $row[$fieldno]);
                    if ($year != '' && (int) $year >= 1900) {
                        echo quoteSPSS(date('d-m-Y H:i:s', mktime($hour, $minute, $second, $month, $day, $year)),$q,$field);
                    } else {
                        echo quoteSPSS($na,$q,$field);
                    }
                } else {
                    echo quoteSPSS($na,$q,$field);
                }
            } else {
                switch ($field['LStype']) {
                    case 'Y': // Yes/No Question Type
                        switch ($row[$fieldno]) {
                            case 'Y':
                                echo quoteSPSS('1',$q,$field);
                                break;
                            case 'N':
                                echo quoteSPSS('2',$q,$field);
                                break;
                            default:
                                echo quoteSPSS($na,$q,$field);
                        }
                        break;
                    case 'G': //Gender
                        switch ($row[$fieldno]) {
                            case 'F':
                                echo quoteSPSS('1',$q,$field);
                                break;
                            case 'M':
                                echo quoteSPSS('2',$q,$field);
                                break;
                            default:
                                echo quoteSPSS($na,$q,$field);
                        }
                        break;
                    case 'C': //Yes/No/Uncertain
                        switch ($row[$fieldno]) {
                            case 'Y':
                                echo quoteSPSS('1',$q,$field);
                                break;
                            case 'N':
                                echo quoteSPSS('2',$q,$field);
                                break;
                            case 'U':
                                echo quoteSPSS('3',$q,$field);
                                break;
                            default:
                                echo quoteSPSS($na,$q,$field);
                        }
                        break;
                        case 'E': //Increase / Same / Decrease
                        switch ($row[$fieldno]) {
                            case 'I':
                                echo quoteSPSS('1',$q,$field);
                                break;
                            case 'S':
                                echo quoteSPSS('2',$q,$field);
                                break;
                            case 'D':
                                echo quoteSPSS('3',$q,$field);
                                break;
                            default:
                                echo quoteSPSS($na,$q,$field);
                        }
                        break;
                        case ':':
                            $aSize = explode(".", $field['size']);
                            if (isset($aSize[1]) && $aSize[1]) {
                                // We need to add decimal
                                echo quoteSPSS(number_format($row[$fieldno], $aSize[1], ".", ""),$q,$field);
                            } else {
                                echo quoteSPSS($row[$fieldno],$q,$field);
                            }
                            break;
                        case 'P':
                        case 'M':
                            if (substr($field['code'], -7) != 'comment' && substr($field['code'], -5) != 'other') {
                                if ($row[$fieldno] == 'Y') {
                                    echo quoteSPSS('1',$q,$field);
                                } elseif (isset($row[$fieldno])) {
                                    echo quoteSPSS('0',$q,$field);
                            } else {
                                echo quoteSPSS($na,$q,$field);
                            }
                            break; // Break inside if : comment and other are string to be filtered
                        } // else do default action
                    default:
                        $strTmp = mb_substr(stripTagsFull($row[$fieldno]), 0, $iLength);
                        if (trim($strTmp) != '') {
                            echo quoteSPSS($strTmp,$q,$field);
                        } else {
                            echo quoteSPSS($na,$q,$field);
                        }
                }
            }
            if ($i < $num_fields) {
                echo ',';
            }
            $i++;
        }
        echo "\n";
    }
}



/**
* Check it the gives field has a labelset and return it as an array if true
*
* @param $field array field from SPSSFieldMap
* @param string $language
* @return array|bool
*/
function SPSSGetValues($field = array(), $qidattributes = null, $language)
{
    $language = sanitize_languagecode($language);

    $length_vallabel = 120; // Constant ?
    if (!isset($field['LStype']) || empty($field['LStype'])) {
        return false;
    }
    if (isset($field['hide']) && $field['hide']) {
        return false;
    }
    $answers = array();
    if (strpos("!LORFWZWH1", $field['LStype']) !== false) {
        if (substr($field['code'], -5) == 'other' || substr($field['code'], -7) == 'comment') {
            //We have a comment field, so free text
            return array(
                'SPSStype' => "A",
                'size' => stringSize($field['sql_name']),
            );
        } else {
            $query = "SELECT {{answers}}.code, {{answers}}.answer,
            {{questions}}.type FROM {{answers}}, {{questions}} WHERE";

            if (isset($field['scale_id'])) {
                $query .= " {{answers}}.scale_id = ".(int) $field['scale_id']." AND";
            }

            $query .= " {{answers}}.qid = '".$field["qid"]."' and {{questions}}.language='".$language."' and  {{answers}}.language='".$language."'
            and {{questions}}.qid='".$field['qid']."' ORDER BY sortorder ASC";
            $result = Yii::app()->db->createCommand($query)->query()->readAll(); //Checked
            $num_results = safecount($result);
            if ($num_results > 0) {
                # Build array that has to be returned
                foreach ($result as $row) {
                    $answers[] = array(
                        'code'=>$row['code'],
                        'value'=>mb_substr(stripTagsFull($row["answer"]), 0, $length_vallabel),
                    );
                }
            }
        }
    }
    if ($field['LStype'] == ':') {
        //Get the labels that could apply!
        if (is_null($qidattributes)) {
            $qidattributes = QuestionAttribute::model()->getQuestionAttributes($field["qid"],$language);
        }

        if ($qidattributes['multiflexible_checkbox']) {
            $answers[] = array('code'=>1, 'value'=>1);
            $answers[] = array('code'=>0, 'value'=>0); // 0 happen only when checked + unchecked. Not when just leave unchecked
        } elseif ($qidattributes['input_boxes']) {
            return array(
                'SPSStype' => "F",
                'size' => numericSize($field['sql_name']),
            );
        } else {
            $minvalue = trim($qidattributes['multiflexible_min']) ? $qidattributes['multiflexible_min'] : 1;
            $maxvalue = trim($qidattributes['multiflexible_max']) ? $qidattributes['multiflexible_max'] : 10;
            $stepvalue = trim($qidattributes['multiflexible_step']) ? $qidattributes['multiflexible_step'] : 1;
            for ($i = $minvalue; $i <= $maxvalue; $i += $stepvalue) {
                $answers[] = array('code'=>$i, 'value'=>$i);
            }
        }
    }
    if ($field['LStype'] == 'M') {
        if (substr($field['code'], -5) == 'other') {
            return array(
                'SPSStype' => "A",
                'size' => stringSize($field['sql_name']),
            );
        } else {
            $answers[] = array('code'=>1, 'value'=>gT('Yes'));
            $answers[] = array('code'=>0, 'value'=>gT('Not Selected'));
        }
    }
    if ($field['LStype'] == "P") {
        if (substr($field['code'], -5) == 'other' || substr($field['code'], -7) == 'comment') {
            return array(
                'SPSStype' => "A",
                'size' => stringSize($field['sql_name']),
            );
        } else {
            $answers[] = array('code'=>1, 'value'=>gT('Yes'));
            $answers[] = array('code'=>0, 'value'=>gT('Not Selected'));
        }
    }
    if ($field['LStype'] == "G") {
        $answers[] = array('code'=>1, 'value'=>gT('Female'));
        $answers[] = array('code'=>2, 'value'=>gT('Male'));
    }
    if ($field['LStype'] == "Y") {
        $answers[] = array('code'=>1, 'value'=>gT('Yes'));
        $answers[] = array('code'=>2, 'value'=>gT('No'));
    }
    if ($field['LStype'] == "C") {
        $answers[] = array('code'=>1, 'value'=>gT('Yes'));
        $answers[] = array('code'=>2, 'value'=>gT('No'));
        $answers[] = array('code'=>3, 'value'=>gT('Uncertain'));
    }
    if ($field['LStype'] == "E") {
        $answers[] = array('code'=>1, 'value'=>gT('Increase'));
        $answers[] = array('code'=>2, 'value'=>gT('Same'));
        $answers[] = array('code'=>3, 'value'=>gT('Decrease'));
    }

    if (in_array($field['LStype'], array('N', 'K'))) {
        return array(
            'size' => numericSize($field['sql_name'],true),
        );
    }
    if (in_array($field['LStype'], array('Q', 'S', 'T', 'U', ';', '*'))) {
        return array(
            'SPSStype' => "A",
            'size' => stringSize($field['sql_name']),
        );
    }
    if (count($answers) > 0) {
        //check the max width of the answers
        $size = 0;
        $spsstype = $field['SPSStype'];
        foreach ($answers as $answer) {
            $len = mb_strlen($answer['code']);
            if ($len > $size) {
                $size = $len;
            }
            if ($spsstype == 'F' && (isNumericExtended($answer['code']) === false || $size > 16)) {
                $spsstype = 'A';
            }
        }
        $answers['SPSStype'] = $spsstype;
        $answers['size'] = $size;
        return $answers;
    } else {
        /* Not managed (currently): url, IP, Ã¢â‚¬Â¦ */
        return;
    }
}

/**
* Creates a fieldmap with all information necessary to output the fields
*
* @param $prefix string prefix for the variable ID
* @return array
*/
function SPSSFieldMap($iSurveyID, $prefix = 'V', $sLanguage = '')
{
    $survey = Survey::model()->findByPk($iSurveyID);
    $typeMap = array(
        '5'=>Array('name'=>'5 Point Choice', 'size'=>1, 'SPSStype'=>'F', 'Scale'=>3),
        'B'=>Array('name'=>'Array (10 Point Choice)', 'size'=>1, 'SPSStype'=>'F', 'Scale'=>3),
        'A'=>Array('name'=>'Array (5 Point Choice)', 'size'=>1, 'SPSStype'=>'F', 'Scale'=>3),
        'F'=>Array('name'=>'Array (Flexible Labels)', 'size'=>1, 'SPSStype'=>'F'),
        '1'=>Array('name'=>'Array (Flexible Labels) Dual Scale', 'size'=>1, 'SPSStype'=>'F'),
        'H'=>Array('name'=>'Array (Flexible Labels) by Column', 'size'=>1, 'SPSStype'=>'F'),
        'E'=>Array('name'=>'Array (Increase, Same, Decrease)', 'size'=>1, 'SPSStype'=>'F', 'Scale'=>2),
        'C'=>Array('name'=>'Array (Yes/No/Uncertain)', 'size'=>1, 'SPSStype'=>'F'),
        'X'=>Array('name'=>'Boilerplate Question', 'size'=>1, 'SPSStype'=>'A', 'hide'=>1),
        'D'=>Array('name'=>'Date', 'size'=>20, 'SPSStype'=>'DATETIME23.2'),
        'G'=>Array('name'=>'Gender', 'size'=>1, 'SPSStype'=>'F'),
        'U'=>Array('name'=>'Huge Free Text', 'size'=>1, 'SPSStype'=>'A'),
        'I'=>Array('name'=>'Language Switch', 'size'=>1, 'SPSStype'=>'A'),
        '!'=>Array('name'=>'List (Dropdown)', 'size'=>1, 'SPSStype'=>'F'),
        'W'=>Array('name'=>'List (Flexible Labels) (Dropdown)', 'size'=>1, 'SPSStype'=>'F'),
        'Z'=>Array('name'=>'List (Flexible Labels) (Radio)', 'size'=>1, 'SPSStype'=>'F'),
        'L'=>Array('name'=>'List (Radio)', 'size'=>1, 'SPSStype'=>'F'),
        'O'=>Array('name'=>'List With Comment', 'size'=>1, 'SPSStype'=>'F'),
        'T'=>Array('name'=>'Long free text', 'size'=>1, 'SPSStype'=>'A'),
        'K'=>Array('name'=>'Multiple Numerical Input', 'size'=>1, 'SPSStype'=>'F'), // Why not F32.10 because it's DECIMAL(30,10) in DB ? (32 because - and .)
        'M'=>Array('name'=>'Multiple choice', 'size'=>1, 'SPSStype'=>'F'),
        'P'=>Array('name'=>'Multiple choice with comments', 'size'=>1, 'SPSStype'=>'F'),
        'Q'=>Array('name'=>'Multiple Short Text', 'size'=>1, 'SPSStype'=>'A'),
        'N'=>Array('name'=>'Numerical Input', 'size'=>3, 'SPSStype'=>'F', 'Scale'=>3), // Why not F32.10 because it's DECIMAL(30,10) in DB ? (32 because - and .)
        'R'=>Array('name'=>'Ranking', 'size'=>1, 'SPSStype'=>'F'),
        'S'=>Array('name'=>'Short free text', 'size'=>1, 'SPSStype'=>'A'),
        'Y'=>Array('name'=>'Yes/No', 'size'=>1, 'SPSStype'=>'F'),
        ':'=>Array('name'=>'Multi flexi numbers', 'size'=>1, 'SPSStype'=>'F', 'Scale'=>3),
        ';'=>Array('name'=>'Multi flexi text', 'size'=>1, 'SPSStype'=>'A'),
        '|'=>Array('name'=>'File upload', 'size'=>1, 'SPSStype'=>'A'),
        '*'=>Array('name'=>'Equation', 'size'=>1, 'SPSStype'=>'A'),
    );

    if (empty($sLanguage)) {
        $sLanguage = $survey->language;
    }
    $fieldmap = createFieldMap($survey, 'full', true, false, $sLanguage);

    #See if tokens are being used
    $bTokenTableExists = tableExists('tokens_'.$iSurveyID);
    // ... and if the survey uses anonymized responses
    $sSurveyAnonymized = $survey->anonymized;

    $iFieldNumber = 0;
    $fields = array();
    if ($bTokenTableExists && $sSurveyAnonymized == 'N' && Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'read')) {
        $tokenattributes = getTokenFieldsAndNames($iSurveyID, false);
        foreach ($tokenattributes as $attributefield=>$attributedescription) {
            //Drop the token field, since it is in the survey too
            if ($attributefield != 'token') {
                $iFieldNumber++;
                $fields[] = array(
                    'id'=>"{$prefix}{$iFieldNumber}",
                    'name'=>mb_substr($attributefield, 0, 8),
                    'qid'=>0,
                    'code'=>'',
                    'SPSStype'=>'A',
                    'LStype'=>'Undef',
                    'VariableLabel'=>$attributedescription['description'],
                    'sql_name'=>$attributefield,
                    'size'=>'100',
                    'title'=>$attributefield,
                    'hide'=>0,
                    'scale'=>''
                );
            }
        }
    }

    $fieldnames = array_keys($fieldmap);
    $num_results = safecount($fieldnames);
    $diff = 0;
    $noQID = Array('id', 'token', 'datestamp', 'submitdate', 'startdate', 'startlanguage', 'ipaddr', 'refurl', 'lastpage','seed');
    # Build array that has to be returned
    for ($i = 0; $i < $num_results; $i++) {
        #Condition for SPSS fields:
        # - Length may not be longer than 8 characters
        # - Name may not begin with a digit
        $fieldname = $fieldnames[$i];
        $fieldtype = '';
        $ftype = '';
        $val_size = 1;
        $hide = 0;
        $export_scale = '';
        $code = '';
        $scale_id = null;
        $aQuestionAttribs = array();

        #Determine field type for specific column
        switch ($fieldname) {
            case 'submitdate':
            case 'startdate':
            case 'datestamp':
                $fieldtype = 'DATETIME23.2';
                break;
            case 'startlanguage';
                $fieldtype = 'A';
                $val_size = 20;
                break;
            case 'token';
                $fieldtype = 'A';
                $val_size = $survey->tokenlength;
                break;
            case 'id';
                $fieldtype = 'F';
                $val_size = 7; //Arbitrarilty restrict to 9,999,999 (7 digits) responses/survey
                break;
            case 'ipaddr';
                $fieldtype = 'A';
                $val_size = 45; // IPv6 + IPv4-mapped feature : 39+1+15
                break;
            case 'refurl';
                $fieldtype = 'A';
                $val_size = 255;
                break;
            case 'lastpage';
                $fieldtype = 'F';
                $val_size = 7;
                break;
            case 'seed';
                $fieldtype = 'A';
                $val_size = 31;
                break;
            default:
                // Not set for default
        }

        #Get qid (question id)
        if (in_array($fieldname, $noQID) || substr($fieldname, 0, 10) == 'attribute_') {
            $qid = 0;
            $varlabel = $fieldname;
            $ftitle = $fieldname;
        } else {
            //GET FIELD DATA
            if (!isset($fieldmap[$fieldname])) {
                //Field in database but no longer in survey... how is this possible?
                //@TODO: think of a fix.
                $fielddata = array();
                $qid = 0;
                $varlabel = $fieldname;
                $ftitle = $fieldname;
                $fieldtype = "F";
                $val_size = 1;
            } else {
                $fielddata = $fieldmap[$fieldname];
                $qid = $fielddata['qid'];
                $ftype = $fielddata['type'];
                $fsid = $fielddata['sid'];
                $fgid = $fielddata['gid'];
                $code = mb_substr($fielddata['fieldname'], strlen($fsid."X".$fgid."X".$qid));
                $varlabel = $fielddata['question'];
                if (isset($fielddata['scale'])) {
                    $varlabel = "[{$fielddata['scale']}] ".$varlabel;
                }
                if (isset($fielddata['subquestion'])) {
                    $varlabel = "[{$fielddata['subquestion']}] ".$varlabel;
                }
                if (isset($fielddata['subquestion2'])) {
                    $varlabel = "[{$fielddata['subquestion2']}] ".$varlabel;
                }
                if (isset($fielddata['subquestion1'])) {
                    $varlabel = "[{$fielddata['subquestion1']}] ".$varlabel;
                }
                $ftitle = $fielddata['title'];
                if (!is_null($code) && $code <> "") {
                    $ftitle .= "_$code";
                }
                if (isset($typeMap[$ftype]['size'])) {
                    $val_size = $typeMap[$ftype]['size'];
                }
                if (isset($fielddata['scale_id'])) {
                    $scale_id = $fielddata['scale_id'];
                }
                if ($fieldtype == '') {
                    $fieldtype = $typeMap[$ftype]['SPSStype'];
                }
                if (isset($typeMap[$ftype]['hide'])) {
                    $hide = $typeMap[$ftype]['hide'];
                    $diff++;
                }
                //Get default scale for this type
                if (isset($typeMap[$ftype]['Scale'])) {
                    $export_scale = $typeMap[$ftype]['Scale'];
                }
                //But allow override
                $aQuestionAttribs = QuestionAttribute::model()->getQuestionAttributes($qid,$sLanguage);
                if (isset($aQuestionAttribs['scale_export'])) {
                    $export_scale = $aQuestionAttribs['scale_export'];
                }
            }

        }
        $iFieldNumber++;
        $fid = $iFieldNumber - $diff;
        $lsLong = isset($typeMap[$ftype]["name"]) ? $typeMap[$ftype]["name"] : $ftype;
        $tempArray = array(
            'id'=>$prefix.$fid,
            'name'=>mb_substr($fieldname, 0, 8),
            'qid'=>$qid,
            'code'=>$code,
            'SPSStype'=>$fieldtype,
            'LStype'=>$ftype,
            'LSlong'=>$lsLong,
            'ValueLabels'=>'',
            'VariableLabel'=>$varlabel,
            'sql_name'=>$fieldname,
            'size'=>$val_size,
            'title'=>$ftitle,
            'hide'=>$hide,
            'scale'=>$export_scale,
            'scale_id'=>$scale_id
        );
        //Now check if we have to retrieve value labels
        $answers = SPSSGetValues($tempArray, $aQuestionAttribs, $sLanguage);
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
            if (!empty($answers)) {
                $tempArray['answers'] = $answers;
            }
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
function SPSSGetQuery($iSurveyID, $limit = null, $offset = null)
{

    $survey = Survey::model()->findByPk($iSurveyID);

    #See if tokens are being used
    $query = App()->db->createCommand();
    $query->from($survey->responsesTableName.' s');
    $columns = array('s.*');
    if ($survey->hasTokensTable && !$survey->isAnonymized && Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'read')) {
        $tokenattributes = array_keys(getTokenFieldsAndNames($iSurveyID, false));
        foreach ($tokenattributes as $attributefield) {
            //Drop the token field, since it is in the survey too
            if ($attributefield != 'token') {
                $columns[] = 't.'.$attributefield;
            }
        }

        $query->leftJoin($survey->tokensTableName.' t', App()->db->quoteColumnName('s.token').' = '.App()->db->quoteColumnName('t.token'));
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

    if (!empty($limit) & !is_null($offset)) {
        $query->limit((int) $limit, (int) $offset);
    }
    $query->order('id ASC');

    return $query;
}

/**
* buildXMLFromQuery() creates a datadump of a table in XML using XMLWriter
*
* @param mixed $xmlwriter  The existing XMLWriter object
* @param mixed $Query  The table query to build from
* @param string $tagname  If the XML tag of the resulting question should be named differently than the table name set it here
* @param string[] $excludes array of columnames not to include in export
*/
function buildXMLFromQuery($xmlwriter, $Query, $tagname = '', $excludes = array())
{
    $iChunkSize = 3000; // This works even for very large result sets and leaves a minimal memory footprint

    preg_match('/\bfrom\b\s*{{(\w+)}}/i', $Query, $MatchResults);
    if ($tagname != '') {
        $TableName = $tagname;
    } else {
        $TableName = $MatchResults[1];
    }



    // Read table in smaller chunks
    $iStart = 0;
    do {
        $QueryResult = Yii::app()->db->createCommand($Query)->limit($iChunkSize, $iStart)->query();
        $result = $QueryResult->readAll();
        if ($iStart == 0 && safecount($result) > 0) {
            $exclude = array_flip($excludes); //Flip key/value in array for faster checks
            $xmlwriter->startElement($TableName);
            $xmlwriter->startElement('fields');
            $aColumninfo = array_keys($result[0]);
            foreach ($aColumninfo as $fieldname) {
                if (!isset($exclude[$fieldname])) {
                    $xmlwriter->writeElement('fieldname', $fieldname);
                }
            }
            $xmlwriter->endElement(); // close columns
            $xmlwriter->startElement('rows');
        }
        foreach ($result as $Row) {
            $xmlwriter->startElement('row');
            foreach ($Row as $Key=>$Value) {
                if (!isset($exclude[$Key])) {
                    if (!(is_null($Value))) {
                        // If the $value is null don't output an element at all
                        if (is_numeric($Key[0])) {
                            // mask invalid element names with an underscore
                            $Key = '_'.$Key;
                        }
                        $Key = str_replace('#', '-', $Key);
                        if (!$xmlwriter->startElement($Key)) {
                            safeDie('Invalid element key: '.$Key);
                        }

                        if ($Value !== '') {
                            // Remove invalid XML characters
                            $Value = preg_replace('/[^\x0\x9\xA\xD\x20-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $Value);
                            $Value = str_replace(']]>', ']] >', $Value);
                            $xmlwriter->writeCData($Value);
                        }
                        $xmlwriter->endElement();
                    }
                }
            }
            $xmlwriter->endElement(); // close row
        }
        $iStart = $iStart + $iChunkSize;
    } while (count($result) == $iChunkSize);
    if (count($result) > 0) {
        $xmlwriter->endElement(); // close rows
        $xmlwriter->endElement(); // close tablename
    }
}

/**
* from export_structure_xml.php
*/
function surveyGetXMLStructure($iSurveyID, $xmlwriter, $exclude = array())
{
    if (!isset($exclude['answers'])) {
        //Answer table
        $aquery = "SELECT {{answers}}.*
        FROM {{answers}}, {{questions}}
        WHERE {{answers}}.language={{questions}}.language
        AND {{answers}}.qid={{questions}}.qid
        AND {{questions}}.sid=$iSurveyID";
        buildXMLFromQuery($xmlwriter, $aquery);
    }

    // Assessments
    $query = "SELECT {{assessments}}.*
    FROM {{assessments}}
    WHERE {{assessments}}.sid=$iSurveyID";
    buildXMLFromQuery($xmlwriter, $query);

    if (!isset($exclude['conditions'])) {
        //Condition table
        $cquery = "SELECT DISTINCT {{conditions}}.*
        FROM {{conditions}}, {{questions}}
        WHERE {{conditions}}.qid={{questions}}.qid
        AND {{questions}}.sid=$iSurveyID";
        buildXMLFromQuery($xmlwriter, $cquery);
    }

    //Default values
    $query = "SELECT {{defaultvalues}}.*
    FROM {{defaultvalues}} JOIN {{questions}} ON {{questions}}.qid = {{defaultvalues}}.qid AND {{questions}}.sid=$iSurveyID AND {{questions}}.language={{defaultvalues}}.language ";

    buildXMLFromQuery($xmlwriter, $query);

    // QuestionGroup
    $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
    $gquery = "SELECT *
    FROM $quotedGroups
    WHERE sid=$iSurveyID
    ORDER BY gid";
    buildXMLFromQuery($xmlwriter, $gquery,'groups');

    //Questions
    $qquery = "SELECT *
    FROM {{questions}}
    WHERE sid=$iSurveyID and parent_qid=0
    ORDER BY qid";
    buildXMLFromQuery($xmlwriter, $qquery);

    //Subquestions
    $qquery = "SELECT *
    FROM {{questions}}
    WHERE sid=$iSurveyID and parent_qid>0
    ORDER BY qid";
    buildXMLFromQuery($xmlwriter, $qquery, 'subquestions');

    //Question attributes
    $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
    $platform = Yii::app()->db->getDriverName();
    if ($platform == 'mssql' || $platform == 'sqlsrv' || $platform == 'dblib') {
        $query = "SELECT qa.qid, qa.attribute, cast(qa.value as varchar(4000)) as value, qa.language
        FROM {{question_attributes}} qa JOIN {{questions}}  q ON q.qid = qa.qid AND q.sid={$iSurveyID}
        where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute,  cast(qa.value as varchar(4000)), qa.language";
    } else {
        $query = "SELECT qa.qid, qa.attribute, qa.value, qa.language
        FROM {{question_attributes}} qa JOIN {{questions}}  q ON q.qid = qa.qid AND q.sid={$iSurveyID}
        where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute, qa.value, qa.language";
    }

    buildXMLFromQuery($xmlwriter, $query, 'question_attributes');

    if (!isset($exclude['quotas'])) {
        //Quota
        $query = "SELECT {{quota}}.*
        FROM {{quota}}
        WHERE {{quota}}.sid=$iSurveyID";
        buildXMLFromQuery($xmlwriter, $query);

        //1Quota members
        $query = "SELECT {{quota_members}}.*
        FROM {{quota_members}}
        WHERE {{quota_members}}.sid=$iSurveyID";
        buildXMLFromQuery($xmlwriter, $query);

        //Quota languagesettings
        $query = "SELECT {{quota_languagesettings}}.*
        FROM {{quota_languagesettings}}, {{quota}}
        WHERE {{quota}}.id = {{quota_languagesettings}}.quotals_quota_id
        AND {{quota}}.sid=$iSurveyID";
        buildXMLFromQuery($xmlwriter, $query);
    }

    // Surveys
    $squery = "SELECT *
    FROM {{surveys}}
    WHERE sid=$iSurveyID";

    //Exclude some fields from the export
    $excludeFromSurvey = array('owner_id', 'active', 'datecreated');
    if (isset($exclude['dates']) && $exclude['dates']) {
        $excludeFromSurvey[] = 'startdate';
        $excludeFromSurvey[] = 'expires';
    }

    buildXMLFromQuery($xmlwriter, $squery, '', $excludeFromSurvey);

    // Survey language settings
    $slsquery = "SELECT *
    FROM {{surveys_languagesettings}}
    WHERE surveyls_survey_id=$iSurveyID";
    buildXMLFromQuery($xmlwriter, $slsquery);

    // Survey url parameters
    $slsquery = "SELECT *
    FROM {{survey_url_parameters}}
    WHERE sid={$iSurveyID}";
    buildXMLFromQuery($xmlwriter, $slsquery);

    // Survey plugin(s)
    $slsquery = " SELECT settings.id,name,".Yii::app()->db->quoteColumnName("key").",".Yii::app()->db->quoteColumnName("value")
                . " FROM {{plugin_settings}} as settings JOIN {{plugins}} as plugins ON plugins.id = settings.plugin_id"
                . " WHERE model='Survey' and model_id=$iSurveyID";
    buildXMLFromQuery($xmlwriter, $slsquery);

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
    $xml->writeElement('LimeSurveyDocType', 'Survey');
    $xml->writeElement('DBVersion', getGlobalSetting("DBVersion"));
    $xml->startElement('languages');
    $surveylanguages = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
    $surveylanguages[] = Survey::model()->findByPk($iSurveyID)->language;
    foreach ($surveylanguages as $surveylanguage) {
        $xml->writeElement('language', $surveylanguage);
    }
    $xml->endElement();
    surveyGetXMLStructure($iSurveyID, $xml, $exclude);
    // survey theme configuration - db values
    surveyGetThemeConfiguration($iSurveyID, $xml, false, 'themes');
    // survey theme configuration - inherited values
    surveyGetThemeConfiguration($iSurveyID, $xml, true, 'themes_inherited');
    $xml->endElement(); // close columns
    $xml->endDocument();
    return $xml->outputMemory(true);
}

/**
* Exports a single table to XML
*
* @param integer $iSurveyID The survey ID
* @param string $sTableName The database table name of the table to be export
* @param string $sDocType What doctype should be written
* @param string $sXMLTableTagName Name of the tag table name in the XML file
* @return string|boolean XMLWriter object
*/
function getXMLDataSingleTable($iSurveyID, $sTableName, $sDocType, $sXMLTableTagName = '', $sFileName = '', $bSetIndent = true)
{
    $xml = getXMLWriter();
    if ($sFileName == '') {
        $xml->openMemory();
    } else {
        $bOK = $xml->openURI($sFileName);
    }
    $xml->setIndent($bSetIndent);
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('document');
    $xml->writeElement('LimeSurveyDocType', $sDocType);
    $xml->writeElement('DBVersion', getGlobalSetting("DBVersion"));
    $xml->startElement('languages');
    $aSurveyLanguages = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
    $aSurveyLanguages[] = Survey::model()->findByPk($iSurveyID)->language;
    foreach ($aSurveyLanguages as $sSurveyLanguage) {
        $xml->writeElement('language', $sSurveyLanguage);
    }
    $xml->endElement();
    $aquery = "SELECT * FROM {{{$sTableName}}}";

    buildXMLFromQuery($xml, $aquery, $sXMLTableTagName);
    $xml->endElement(); // close columns
    $xml->endDocument();
    if ($sFileName == '') {
        return $xml->outputMemory(true);
    } else {
        return $bOK;
    }
}


/**
* from export_structure_quexml.php
*/
function QueXMLCleanup($string, $allow = '<p><b><u><i><em>')
{
    return str_replace("&", "&amp;", html_entity_decode(trim(strip_tags(str_ireplace("<br />", "\n", $string), $allow)), ENT_QUOTES, 'UTF-8'));
}

/**
* from export_structure_quexml.php
*/
function QueXMLCreateFree($f, $len, $lab = "")
{
    global $dom;
    $free = $dom->createElement("free");

    $format = $dom->createElement("format", QueXMLCleanup($f));

    $length = $dom->createElement("length", QueXMLCleanup($len));

    $label = $dom->createElement("label", QueXMLCleanup($lab));

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

    foreach ($array as $key => $v) {
        $category = $dom->createElement("category");

        $label = $dom->createElement("label", QueXMLCleanup("$key", ''));

        $value = $dom->createElement("value", QueXMLCleanup("$v", ''));

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
function QueXMLSkipTo($qid, $value, $cfieldname = "")
{
    return false;
}

/**
* from export_structure_quexml.php
*/
function QueXMLCreateFixed($qid, $iResponseID, $fieldmap, $rotate = false, $labels = true, $scale = 0, $other = false, $varname = "")
{
    global $dom;
    global $quexmllang;
    global $iSurveyID;

    App()->setLanguage($quexmllang);

    if ($labels) {
              $Rows = Yii::app()->db->createCommand()
                ->select('*')
                ->from("{{labels}}")
                ->where(" lid=:labels AND language=:language", array(':labels'=>$labels,  ':language'=>$quexmllang))
                ->order('sortorder asc')
                ->queryAll();
    } else {
          $Rows = Yii::app()->db->createCommand()
              ->select('code,answer as title,sortorder ')
              ->from("{{answers}}")
              ->where(" qid=:qid AND scale_id=:scale AND language=:language", array(':qid'=>$qid, ':scale'=>$scale, ':language'=>$quexmllang))
              ->order('sortorder asc')
              ->queryAll();

    }

    $fixed = $dom->createElement("fixed");

    $nextcode = "";

    foreach ($Rows as $Row) {
        $category = $dom->createElement("category");

        $label = $dom->createElement("label", QueXMLCleanup($Row['title'], ''));

        $value = $dom->createElement("value", QueXMLCleanup($Row['code']));

        $category->appendChild($label);
        $category->appendChild($value);

        $st = QueXMLSkipTo($qid, $Row['code']);
        if ($st !== false) {
            $quexml_skipto = $dom->createElement("quexml_skipto", $st);
            $category->appendChild($quexml_skipto);
        }


        $fixed->appendChild($category);
        $nextcode = $Row['code'];
    }

    if ($other) {
        $category = $dom->createElement("category");

        $label = $dom->createElement("label", quexml_get_lengthth($qid, "other_replace_text", gT("Other"), $quexmllang));

        $value = $dom->createElement("value", '-oth-');

        $category->appendChild($label);
        $category->appendChild($value);

        $contingentQuestion = $dom->createElement("contingentQuestion");
        $length = $dom->createElement("length", 24);
        $format = $dom->createElement("format", "longtext");
        $text = $dom->createElement("text", quexml_get_lengthth($qid, "other_replace_text", gT("Other"), $quexmllang));

        $contingentQuestion->appendChild($text);
        $contingentQuestion->appendChild($length);
        $contingentQuestion->appendChild($format);
        $contingentQuestion->setAttribute("varName", $varname.'other');

        quexml_set_default_value($contingentQuestion, $iResponseID, $qid, $iSurveyID, $fieldmap, "other");

        $category->appendChild($contingentQuestion);

        $fixed->appendChild($category);
    }

    if ($rotate) {
        $fixed->setAttribute("rotate", "true");
    }

    return $fixed;
}

/**
* from export_structure_quexml.php
*/
function quexml_get_lengthth($qid, $attribute, $default, $quexmllang = false)
{
    global $dom;
    if ($quexmllang != false) {
            $Row = Yii::app()->db->createCommand()
                ->select('value')
                ->from("{{question_attributes}}")
                ->where(" qid=:qid   AND language=:language AND attribute = :attribute ", array(':qid'=>$qid, ':language'=>$quexmllang, ':attribute' => $attribute))
                ->queryRow();
      } else {
        $Row = Yii::app()->db->createCommand()
            ->select('value')
            ->from("{{question_attributes}}")
            ->where(" qid=:qid     AND attribute = :attribute ", array(':qid'=>$qid,  ':attribute' => $attribute))
            ->queryRow();
    }


    if ($Row && !empty($Row['value'])) {
            return $Row['value'];
    } else {
            return $default;
    }

}

/**
* from export_structure_quexml.php
*/
function quexml_create_multi(&$question, $qid, $varname, $iResponseID, $fieldmap, $scale_id = false, $free = false, $other = false, $yesvalue = "1",$comment=false)
{
    global $dom;
    global $quexmllang;
    global $iSurveyID;
    App()->setLanguage($quexmllang);

    $quexmllang = sanitize_languagecode($quexmllang);
    $scale_id   = sanitize_paranoid_string($scale_id);

    $Query = "SELECT * FROM {{questions}} WHERE parent_qid = $qid  AND language='$quexmllang' ";
    if ($scale_id != false) {
        $Query .= " AND scale_id = $scale_id ";
    }
    $Query .= " ORDER BY question_order ASC";
    //$QueryResult = mysql_query($Query) or die ("ERROR: $QueryResult<br />".mysql_error());
    $QueryResult = Yii::app()->db->createCommand($Query)->query();

    foreach ($QueryResult->readAll() as $Row) {
        $response = $dom->createElement("response");
        if ($free == false) {
            $fixed = $dom->createElement("fixed");
            $category = $dom->createElement("category");

            $label = $dom->createElement("label", QueXMLCleanup($Row['question'], ''));

            $value = $dom->createElement("value", $yesvalue);
            $nextcode = $Row['title'];

            $category->appendChild($label);
            $category->appendChild($value);

            $st = QueXMLSkipTo($qid, 'Y', " AND c.cfieldname LIKE '+$iSurveyID"."X".$Row['gid']."X".$qid.$Row['title']."' ");
            if ($st !== false) {
                $quexml_skipto = $dom->createElement("skipTo", $st);
                $category->appendChild($quexml_skipto);
            }

            if ($comment) {
                $contingentQuestion = $dom->createElement("contingentQuestion");
                $length = $dom->createElement("length", 10);
                $format = $dom->createElement("format", "longtext");
                $text = $dom->createElement("text", gT("Comment"));

                $contingentQuestion->appendChild($text);
                $contingentQuestion->appendChild($length);
                $contingentQuestion->appendChild($format);
                $contingentQuestion->setAttribute("varName", $varname."_".QueXMLCleanUp($Row['title']).'comment');

                quexml_set_default_value($contingentQuestion, $iResponseID, $qid, $iSurveyID, $fieldmap, $Row['title'] . "comment");

                $category->appendChild($contingentQuestion);
            }

            $fixed->appendChild($category);
            $response->appendChild($fixed);

        } else {
            $response->appendChild(QueXMLCreateFree($free['f'], $free['len'], $Row['question']));
        }

        $response->setAttribute("varName", $varname."_".QueXMLCleanup($Row['title']));

        if ($scale_id == false) {
            //if regular multiple choice question
            quexml_set_default_value($response, $iResponseID, $Row['qid'], $iSurveyID, $fieldmap, false, true);
        } else {
            //if array multi style question
            $dvname = substr($varname, stripos($varname, "_") + 1)."_".$Row['title'];
            quexml_set_default_value($response, $iResponseID, $qid, $iSurveyID, $fieldmap, $dvname);
        }

        $question->appendChild($response);
    }

    if ($other && $free == false) {
        $response = $dom->createElement("response");
        $fixed = $dom->createElement("fixed");
        $category = $dom->createElement("category");

        $label = $dom->createElement("label", quexml_get_lengthth($qid, "other_replace_text", gT("Other"), $quexmllang));

        $value = $dom->createElement("value", $yesvalue);

        //Get next code
        if (is_numeric($nextcode)) {
                    $nextcode++;
        } else if (is_string($nextcode)) {
                        $nextcode = chr(ord($nextcode) + 1);
        }

        $category->appendChild($label);
        $category->appendChild($value);

        $contingentQuestion = $dom->createElement("contingentQuestion");
        $length = $dom->createElement("length", 24);
        $format = $dom->createElement("format", "longtext");
        $text = $dom->createElement("text", quexml_get_lengthth($qid, "other_replace_text", gT("Other"), $quexmllang));

        $contingentQuestion->appendChild($text);
        $contingentQuestion->appendChild($length);
        $contingentQuestion->appendChild($format);
        $contingentQuestion->setAttribute("varName", $varname.'other');

        quexml_set_default_value($contingentQuestion, $iResponseID, $qid, $iSurveyID, $fieldmap, "other");

        $category->appendChild($contingentQuestion);

        $fixed->appendChild($category);
        $response->appendChild($fixed);

        $response->setAttribute("varName", $varname.QueXMLCleanup($nextcode));

        $question->appendChild($response);
    }




    return;

}

/**
* from export_structure_quexml.php
*/
function quexml_create_subQuestions(&$question, $qid, $varname, $iResponseID, $fieldmap, $use_answers = false, $aid = false, $scale = false)
{
    global $dom;
    global $quexmllang;
    global $iSurveyID;

    $quexmllang = sanitize_languagecode($quexmllang);
    $qid        = sanitize_paranoid_string($qid);
    if ($use_answers) {
        $Query = "SELECT qid, answer as question, code as title, sortorder as aid FROM {{answers}} WHERE qid = $qid  AND language='$quexmllang' ORDER BY sortorder ASC";
    } else {
        $Query = "SELECT * FROM {{questions}} WHERE parent_qid = $qid and scale_id = 0  AND language='$quexmllang' ORDER BY question_order ASC";
    }
    $QueryResult = Yii::app()->db->createCommand($Query)->query();
    foreach ($QueryResult->readAll() as $Row) {
        if ($use_answers) {
            $aid = $Row["aid"];
        }
        $subQuestion = $dom->createElement("subQuestion");
        $text = $dom->createElement("text", QueXMLCleanup($Row['question'], ''));
        $subQuestion->appendChild($text);
        $subQuestion->setAttribute("varName", $varname.'_'.QueXMLCleanup($Row['title']));
        if ($use_answers == false && $aid != false) {
//dual scale array questions
            quexml_set_default_value($subQuestion, $iResponseID, $qid, $iSurveyID, $fieldmap, false, false, $Row['title'], $scale);
        } else {
            quexml_set_default_value($subQuestion, $iResponseID, $Row['qid'], $iSurveyID, $fieldmap, false, !$use_answers, $aid);
        }
        $question->appendChild($subQuestion);
    }

    return;
}

/**
 * Set defaultValue attribute of provided element from response table
 *
 * @param mixed $element DOM element to add attribute to
 * @param int $iResponseID The response id
 * @param int $qid The qid of the question
 * @param int $iSurveyID The survey id
 * @param array $fieldmap A mapping of fields to qid
 * @param bool|string $fieldadd Anything additional to search for in the field name
 * @param bool|string $usesqid Search using sqid instead of qid
 * @param bool|string $usesaid Search using aid
 */
function quexml_set_default_value(&$element, $iResponseID, $qid, $iSurveyID, $fieldmap, $fieldadd = false, $usesqid = false, $usesaid = false, $usesscale = false)
{
    //insert response into form if provided
    if ($iResponseID) {
        $colname = "";
        $search = "qid";
        if ($usesqid) {
            $search = "sqid";
        }
        foreach ($fieldmap as $key => $detail) {
            if (array_key_exists($search, $detail) && $detail[$search] == $qid) {
                if (($fieldadd == false || substr($key, (strlen($fieldadd) * -1)) == $fieldadd) &&
                    ($usesaid == false || ($detail["aid"] == $usesaid)) &&
                    ($usesscale == false || ($detail["scale_id"] == $usesscale))) {
                    $colname = $key;
                    break;
                }
            }
        }
        if ($colname != "") {
            $QRE = Yii::app()->db->createCommand()
                ->select($colname.' AS value')
                ->from("{{survey_$iSurveyID}}")
                ->where('id = :id', ['id' => $iResponseID])
                ->query();
            $QROW = $QRE->read();
            $value = $QROW['value'];
            $element->setAttribute("defaultValue", $value);
        }
    }
}

/**
 * Format defaultValue of Date/Time questions according to question date format
 *
 * @param DOMElement $element DOM element with the date to change
 * @param int $qid The qid of the question
 * @param int $iSurveyID The survey id
 * @return void
 */
function quexml_reformat_date(&$element, $qid, $iSurveyID)
{
    // Retrieve date format from the question
    $questionAttributes = QuestionAttribute::model()->getQuestionAttributes($qid);
    $dateformatArr = getDateFormatDataForQID($questionAttributes, $iSurveyID);
    $dateformat = $dateformatArr['phpdate'];

    // Get the value from the DOM element
    $currentValue = $element->getAttribute("defaultValue");

    // Convert the value using the survey's date format
    $value = date($dateformat,strtotime($currentValue));

    // Change the value in the DOM element
    $element->setAttribute("defaultValue", $value);
}

/**
 * Create a queXML question element
 *
 * @param array $RowQ Question details in array
 * @param bool|string $additional Any additional question text to append
 */
function quexml_create_question($RowQ, $additional = false)
{
    global $dom;

    $question = $dom->createElement("question");

    //create a new text element for each new line
    $questiontext = explode('<br />', $RowQ['question']);
    foreach ($questiontext as $qt) {
        $txt = QueXMLCleanup($qt);
        if (!empty($txt)) {
            $text = $dom->createElement("text", $txt);
            $question->appendChild($text);
        }
    }

    if ($additional !== false) {
        $txt = QueXMLCleanup($additional);
        $text = $dom->createElement("text", $txt);
        $question->appendChild($text);
    }

    //directive
    if (!empty($RowQ['help'])) {
        $directive = $dom->createElement("directive");
        $position = $dom->createElement("position", "during");
        $text = $dom->createElement("text", QueXMLCleanup($RowQ['help']));
        $administration = $dom->createElement("administration", "self");

        $directive->appendChild($position);
        $directive->appendChild($text);
        $directive->appendChild($administration);

        $question->appendChild($directive);
    }

    if (Yii::app()->getConfig('quexmlshowprintablehelp') == true) {

        $RowQ['printable_help'] = quexml_get_lengthth($qid, "printable_help", "", $quexmllang);

        if (!empty($RowQ['printable_help'])) {
            $directive = $dom->createElement("directive");
            $position = $dom->createElement("position", "before");
            $text = $dom->createElement("text", '['.gT('Only answer the following question if:')." ".QueXMLCleanup($RowQ['printable_help'])."]");
            $administration = $dom->createElement("administration", "self");
            $directive->appendChild($position);
            $directive->appendChild($text);
            $directive->appendChild($administration);
            $question->appendChild($directive);
        }
    }

    return $question;
}


/**
* Export quexml survey.
*/
function quexml_export($surveyi, $quexmllan, $iResponseID = false)
{
    global $dom, $quexmllang, $iSurveyID;
    $quexmllang = $quexmllan;
    $iSurveyID = $surveyi;

    App()->setLanguage($quexmllang);

    $oSurvey = Survey::model()->findByPk($iSurveyID);
    $fieldmap = createFieldMap($oSurvey, 'short', false, false, $quexmllang);

    $Row = Yii::app()->db->createCommand()
        ->select('*')
        ->from("{{surveys}} ")
        ->join('{{surveys_languagesettings}}', '{{surveys_languagesettings}}.surveyls_survey_id = {{surveys}}.sid')
        ->where('{{surveys}}.sid=:sid', array(':sid'=>$iSurveyID))
        ->andWhere('{{surveys_languagesettings}}.surveyls_language=:lang', array(':lang'=>$quexmllang))
        ->queryRow();

    $dom = new DOMDocument('1.0', 'UTF-8');

    //Title and survey id
    $questionnaire = $dom->createElement("questionnaire");
    $questionnaire->setAttribute("id", $Row['sid']);
    $title = $dom->createElement("title", QueXMLCleanup($Row['surveyls_title']));
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
    if (!empty($Row['surveyls_welcometext'])) {
        $questionnaireInfo = $dom->createElement("questionnaireInfo");
        $position = $dom->createElement("position", "before");
        $text = $dom->createElement("text", QueXMLCleanup($Row['surveyls_welcometext']));
        $administration = $dom->createElement("administration", "self");
        $questionnaireInfo->appendChild($position);
        $questionnaireInfo->appendChild($text);
        $questionnaireInfo->appendChild($administration);
        $questionnaire->appendChild($questionnaireInfo);
    }

    if (!empty($Row['surveyls_endtext'])) {
        $questionnaireInfo = $dom->createElement("questionnaireInfo");
        $position = $dom->createElement("position", "after");
        $text = $dom->createElement("text", QueXMLCleanup($Row['surveyls_endtext']));
        $administration = $dom->createElement("administration", "self");
        $questionnaireInfo->appendChild($position);
        $questionnaireInfo->appendChild($text);
        $questionnaireInfo->appendChild($administration);
        $questionnaire->appendChild($questionnaireInfo);
    }

    // substitute token placeholders for real token values
    $RowQReplacements = array();
    if ($oSurvey->anonymized == 'N' && $oSurvey->hasTokensTable && (int) $iResponseID > 0){
        $response = Response::model($iSurveyID)->findByPk($iResponseID);
        if (!empty($response)){
            $token = TokenDynamic::model($iSurveyID)->find(array('condition' => 'token = \'' . $response->token . '\''));
            if (!empty($token)){
                $RowQReplacements['TOKEN'] = $token->token;
                $RowQReplacements['TOKEN:EMAIL'] = $token->email;
                $RowQReplacements['TOKEN:FIRSTNAME'] = $token->firstname;
                $RowQReplacements['TOKEN:LASTNAME'] = $token->lastname;
                
                $customAttributes = $token->getCustom_attributes();
                foreach($customAttributes as $key => $val){
                    $RowQReplacements['TOKEN:' . strtoupper($key)] = $token->$key;
                }
            }
        }
    }

    //section == group

    $Rows = Yii::app()->db->createCommand()
        ->select('*')
        ->from("{{groups}}")
        ->where('sid=:sid', array(':sid'=>$iSurveyID))
        ->andWhere(' language=:lang', array(':lang'=>$quexmllang))
        ->order('group_order asc')
        ->queryAll();




    //for each section
    foreach ($Rows as $Row) {
        $gid = $Row['gid'];

        $section = $dom->createElement("section");

        if (!empty($Row['group_name'])) {
            $sectionInfo = $dom->createElement("sectionInfo");
            $position = $dom->createElement("position", "title");
            $text = $dom->createElement("text", QueXMLCleanup($Row['group_name']));
            $administration = $dom->createElement("administration", "self");
            $sectionInfo->appendChild($position);
            $sectionInfo->appendChild($text);
            $sectionInfo->appendChild($administration);
            $section->appendChild($sectionInfo);
        }


        if (!empty($Row['description'])) {
            $sectionInfo = $dom->createElement("sectionInfo");
            $position = $dom->createElement("position", "before");
            $text = $dom->createElement("text", QueXMLCleanup($Row['description']));
            $administration = $dom->createElement("administration", "self");
            $sectionInfo->appendChild($position);
            $sectionInfo->appendChild($text);
            $sectionInfo->appendChild($administration);
            $section->appendChild($sectionInfo);
        }

        $section->setAttribute("id", $gid);

        if ($oSurvey->showgroupinfo == 'N' || $oSurvey->showgroupinfo == 'X') {
            $section->setAttribute('hideinfo', 'true');
        }
        if ($oSurvey->showgroupinfo == 'D' || $oSurvey->showgroupinfo == 'X') {
            $section->setAttribute('hidetitle', 'true');
        }

        //boilerplate questions convert to sectionInfo elements
        $Rows = Yii::app()->db->createCommand()
            ->select('*')
            ->from("{{questions}}")
            ->where("sid=:sid AND gid=:gid AND type LIKE 'X' AND language=:language", array(':sid'=>$iSurveyID, ':gid'=>$gid, ':language'=>$quexmllang))
            ->order('question_order asc')
            ->queryAll();

        foreach ($Rows as $RowQ) {
            // placeholder substitution
            $RowQ['question'] = templatereplace($RowQ['question'], $RowQReplacements);
            $sectionInfo = $dom->createElement("sectionInfo");
            $position = $dom->createElement("position", "before");
            $text = $dom->createElement("text", QueXMLCleanup($RowQ['question']));
            $administration = $dom->createElement("administration", "self");

            $sectionInfo->appendChild($position);
            $sectionInfo->appendChild($text);
            $sectionInfo->appendChild($administration);

            $section->appendChild($sectionInfo);
        }



        //foreach question
        $Rows = Yii::app()->db->createCommand()
            ->select('*')
            ->from("{{questions}}")
            ->where("sid=:sid AND gid=:gid AND  parent_qid=0  AND language=:language AND type NOT LIKE 'X'", array(':sid'=>$iSurveyID, ':gid'=>$gid, ':language'=>$quexmllang))
            ->order('question_order asc')
            ->queryAll();

        foreach ($Rows as $RowQ) {
            $type = $RowQ['type'];
            $qid = $RowQ['qid'];

            // placeholder substitution
            $RowQ['question'] = templatereplace($RowQ['question'], $RowQReplacements);
            $other = false;
            if ($RowQ['other'] == 'Y') {
                $other = true;
            }

            $sgq = $RowQ['title'];

            //if this is a multi-flexi style question, create multiple questions
            if ($type == ':' || $type == ';') {

                $Rows = Yii::app()->db->createCommand()
                    ->select('*')
                    ->from("{{questions}}")
                    ->where("parent_qid=:qid AND scale_id=0 AND language=:language", array(':qid'=>$qid, ':language'=>$quexmllang))
                    ->order('question_order asc')
                    ->queryAll();

                foreach ($Rows as $SRow) {
                    $question = quexml_create_question($RowQ, $SRow['question']);

                    if ($type == ":") {
                        //get multiflexible_checkbox - if set then each box is a checkbox (single fixed response)
                        $mcb = quexml_get_lengthth($qid, 'multiflexible_checkbox', -1);
                        if ($mcb != -1) {
                                                    quexml_create_multi($question, $qid, $sgq."_".$SRow['title'], $iResponseID, $fieldmap, 1);
                        } else {
                            //get multiflexible_max and maximum_chars - if set then make boxes of max of these widths
                            $mcm = max(quexml_get_lengthth($qid, 'maximum_chars', 1), strlen(quexml_get_lengthth($qid, 'multiflexible_max', 1)));
                            quexml_create_multi($question, $qid, $sgq."_".$SRow['title'], $iResponseID, $fieldmap, 1, array('f' => 'integer', 'len' => $mcm, 'lab' => ''));
                        }
                    } else if ($type == ";") {
                        //multi-flexi array text

                        //foreach question where scale_id = 1 this is a textbox
                        quexml_create_multi($question, $qid, $sgq."_".$SRow['title'], $iResponseID, $fieldmap, 1, array('f' => 'text', 'len' => quexml_get_lengthth($qid, 'maximum_chars', 10), 'lab' => ''));
                    }
                    $section->appendChild($question);
                }

            } else if ($type == '1') {
              //dual scale array need to split into two questions
                $QROW = Yii::app()->db->createCommand()
                    ->select('value')
                    ->from("{{question_attributes}}")
                    ->where(" qid=:qid AND   language=:language AND attribute='dualscale_headerA' ", array(':qid'=>$qid, ':language'=>$quexmllang))
                    ->queryRow();

                $question = quexml_create_question($RowQ, $QROW['value']);

                //select subQuestions from answers table where QID
                quexml_create_subQuestions($question, $qid, $sgq, $iResponseID, $fieldmap, false, true, 0);
                //get the header of the first scale of the dual scale question
                $response = $dom->createElement("response");
                $response->appendChild(QueXMLCreateFixed($qid, $iResponseID, $fieldmap, false, false, 0, $other, $sgq));
                $question->appendChild($response);

                $section->appendChild($question);

                $QROW = Yii::app()->db->createCommand()
                    ->select('value')
                    ->from("{{question_attributes}}")
                    ->where(" qid=:qid AND   language=:language AND attribute='dualscale_headerB' ", array(':qid'=>$qid, ':language'=>$quexmllang))
                    ->queryRow();

                $question = quexml_create_question($RowQ, $QROW['value']);

                //get the header of the second scale of the dual scale question
                quexml_create_subQuestions($question, $qid, $sgq, $iResponseID, $fieldmap, false, true, 1);
                $response2 = $dom->createElement("response");
                $response2->appendChild(QueXMLCreateFixed($qid, $iResponseID, $fieldmap, false, false, 1, $other, $sgq));
                $question->appendChild($response2);

                $section->appendChild($question);

            } else {
                $question = quexml_create_question($RowQ);

                $response = $dom->createElement("response");
                $response->setAttribute("varName", $sgq);


                switch ($type) {
                    case "X": //BOILERPLATE QUESTION - none should appear

                        break;
                    case "5": //5 POINT CHOICE radio-buttons
                        $response->appendChild(QueXMLFixedArray(array("1" => 1, "2" => 2, "3" => 3, "4" => 4, "5" => 5)));
                        quexml_set_default_value($response, $iResponseID, $qid, $iSurveyID, $fieldmap);
                        $question->appendChild($response);
                        break;
                    case "D": //DATE
                        $response->appendChild(QueXMLCreateFree("date", "19", ""));
                        quexml_set_default_value($response, $iResponseID, $qid, $iSurveyID, $fieldmap);
                        if (Yii::app()->getConfig('quexmlkeepsurveydateformat')) {
                            quexml_reformat_date($response, $qid, $iSurveyID);
                        }
                        $question->appendChild($response);
                        break;
                    case "L": //LIST drop-down/radio-button list
                        $response->appendChild(QueXMLCreateFixed($qid, $iResponseID, $fieldmap, false, false, 0, $other, $sgq));
                        quexml_set_default_value($response, $iResponseID, $qid, $iSurveyID, $fieldmap);
                        $question->appendChild($response);
                        break;
                    case "!": //List - dropdown
                        $response->appendChild(QueXMLCreateFixed($qid, $iResponseID, $fieldmap, false, false, 0, $other, $sgq));
                        quexml_set_default_value($response, $iResponseID, $qid, $iSurveyID, $fieldmap);
                        $question->appendChild($response);
                        break;
                    case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
                        quexml_create_subQuestions($question, $qid, $sgq, $iResponseID, $fieldmap);
                        $response = $dom->createElement("response");
                        quexml_set_default_value($response, $iResponseID, $qid, $iSurveyID, $fieldmap);
                        $response->setAttribute("varName", QueXMLCleanup($sgq));
                        $response->appendChild(QueXMLCreateFixed($qid, $iResponseID, $fieldmap, false, false, 0, $other, $sgq));

                        $response2 = $dom->createElement("response");
                        quexml_set_default_value($response2, $iResponseID, $qid, $iSurveyID, $fieldmap, "comment");
                        $response2->setAttribute("varName", QueXMLCleanup($sgq)."_comment");
                        $response2->appendChild(QueXMLCreateFree("longtext", "40", ""));

                        $question->appendChild($response);
                        $question->appendChild($response2);
                        break;
                    case "R": //RANKING STYLE
                        quexml_create_subQuestions($question, $qid, $sgq, $iResponseID, $fieldmap, true);

                        $QROW = Yii::app()->db->createCommand()
                            ->select('MAX(CHAR_LENGTH(code)) as sc')
                            ->from("{{answers}}")
                            ->where(" qid=:qid AND  language=:language", array(':qid'=>$qid, ':language'=>$quexmllang))
                            ->queryRow();

                        $response->appendChild(QueXMLCreateFree("integer", $QROW['sc'], ""));
                        $question->appendChild($response);
                        break;
                    case "M": //Multiple choice checkbox
                        quexml_create_multi($question, $qid, $sgq, $iResponseID, $fieldmap, false, false, $other, "Y");
                        break;
                    case "P": //Multiple choice with comments checkbox + text
                        quexml_create_multi($question, $qid, $sgq, $iResponseID, $fieldmap, false, false, $other, "Y",true);
                        break;
                    case "Q": //MULTIPLE SHORT TEXT
                        quexml_create_subQuestions($question, $qid, $sgq, $iResponseID, $fieldmap);
                        $response->appendChild(QueXMLCreateFree("text", quexml_get_lengthth($qid, "maximum_chars", "10"), ""));
                        $question->appendChild($response);
                        break;
                    case "K": //MULTIPLE NUMERICAL
                        quexml_create_subQuestions($question, $qid, $sgq, $iResponseID, $fieldmap);
                        $response->appendChild(QueXMLCreateFree("integer", quexml_get_lengthth($qid, "maximum_chars", "10"), ""));
                        $question->appendChild($response);
                        break;
                    case "N": //NUMERICAL QUESTION TYPE
                        $response->appendChild(QueXMLCreateFree("integer", quexml_get_lengthth($qid, "maximum_chars", "10"), ""));
                        quexml_set_default_value($response, $iResponseID, $qid, $iSurveyID, $fieldmap);
                        $question->appendChild($response);
                        break;
                    case "S": //SHORT FREE TEXT
                        // default is fieldlength of 24 characters.
                        $response->appendChild(QueXMLCreateFree("longtext", quexml_get_lengthth($qid, "maximum_chars", "24"), ""));
                        quexml_set_default_value($response, $iResponseID, $qid, $iSurveyID, $fieldmap);
                        $question->appendChild($response);
                        break;
                    case "T": //LONG FREE TEXT
                        $response->appendChild(QueXMLCreateFree("longtext", quexml_get_lengthth($qid, "display_rows", "40"), ""));
                        quexml_set_default_value($response, $iResponseID, $qid, $iSurveyID, $fieldmap);
                        $question->appendChild($response);
                        break;
                    case "U": //HUGE FREE TEXT
                        $response->appendChild(QueXMLCreateFree("longtext", quexml_get_lengthth($qid, "display_rows", "80"), ""));
                        quexml_set_default_value($response, $iResponseID, $qid, $iSurveyID, $fieldmap);
                        $question->appendChild($response);
                        break;
                    case "Y": //YES/NO radio-buttons
                        $response->appendChild(QueXMLFixedArray(array(gT("Yes") => 'Y', gT("No") => 'N')));
                        quexml_set_default_value($response, $iResponseID, $qid, $iSurveyID, $fieldmap);
                        $question->appendChild($response);
                        break;
                    case "G": //GENDER drop-down list
                        $response->appendChild(QueXMLFixedArray(array(gT("Female") => 'F', gT("Male") => 'M')));
                        quexml_set_default_value($response, $iResponseID, $qid, $iSurveyID, $fieldmap);
                        $question->appendChild($response);
                        break;
                    case "A": //ARRAY (5 POINT CHOICE) radio-buttons
                        quexml_create_subQuestions($question, $qid, $sgq, $iResponseID, $fieldmap);
                        $response->appendChild(QueXMLFixedArray(array("1" => 1, "2" => 2, "3" => 3, "4" => 4, "5" => 5)));
                        $question->appendChild($response);
                        break;
                    case "B": //ARRAY (10 POINT CHOICE) radio-buttons
                        quexml_create_subQuestions($question, $qid, $sgq, $iResponseID, $fieldmap);
                        $response->appendChild(QueXMLFixedArray(array("1" => 1, "2" => 2, "3" => 3, "4" => 4, "5" => 5, "6" => 6, "7" => 7, "8" => 8, "9" => 9, "10" => 10)));
                        $question->appendChild($response);
                        break;
                    case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
                        quexml_create_subQuestions($question, $qid, $sgq, $iResponseID, $fieldmap);
                        $response->appendChild(QueXMLFixedArray(array(gT("Yes") => 'Y', gT("Uncertain") => 'U', gT("No") => 'N')));
                        $question->appendChild($response);
                        break;
                    case "E": //ARRAY (Increase/Same/Decrease) radio-buttons
                        quexml_create_subQuestions($question, $qid, $sgq, $iResponseID, $fieldmap);
                        $response->appendChild(QueXMLFixedArray(array(gT("Increase") => 'I', gT("Same") => 'S', gT("Decrease") => 'D')));
                        $question->appendChild($response);
                        break;
                    case "F": //ARRAY (Flexible) - Row Format
                        //select subQuestions from answers table where QID
                        quexml_create_subQuestions($question, $qid, $sgq, $iResponseID, $fieldmap);
                        $response->appendChild(QueXMLCreateFixed($qid, $iResponseID, $fieldmap, false, false, 0, $other, $sgq));
                        $question->appendChild($response);
                        //select fixed responses from
                        break;
                    case "H": //ARRAY (Flexible) - Column Format
                        quexml_create_subQuestions($question, $RowQ['qid'], $sgq, $iResponseID, $fieldmap);
                        $response->appendChild(QueXMLCreateFixed($qid, $iResponseID, $fieldmap, true, false, 0, $other, $sgq));
                        $question->appendChild($response);
                        break;
                    case "^": //SLIDER CONTROL - not supported
                        $response->appendChild(QueXMLFixedArray(array("NOT SUPPORTED:$type" => 1)));
                        $question->appendChild($response);
                        break;
                } //End Switch

                $section->appendChild($question);
            }
        }


        $questionnaire->appendChild($section);
    }


    $dom->appendChild($questionnaire);

    $dom->formatOutput = true;
    return $dom->saveXML();
}



// DUMP THE RELATED DATA FOR A SINGLE QUESTION INTO A SQL FILE FOR IMPORTING LATER ON OR
// ON ANOTHER SURVEY SETUP DUMP ALL DATA WITH RELATED QID FROM THE FOLLOWING TABLES
// 1. questions
// 2. answers

/**
 * @param string $action
 */
function group_export($action, $iSurveyID, $gid)
{
    $fn = "limesurvey_group_$gid.lsg";
    $xml = getXMLWriter();

    viewHelper::disableHtmlLogging();
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=$fn");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
    header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
    header("Cache-Control: must-revalidate, no-store, no-cache");

    $xml->openUri('php://output');
    $xml->setIndent(true);
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('document');
    $xml->writeElement('LimeSurveyDocType', 'Group');
    $xml->writeElement('DBVersion', getGlobalSetting("DBVersion"));
    $xml->startElement('languages');

    $lresult = QuestionGroup::model()->findAllByAttributes(array('gid' => $gid), array('select'=>'language', 'group' => 'language'));
    foreach ($lresult as $row) {
        $xml->writeElement('language', $row->language);
    }
    $xml->endElement();
    groupGetXMLStructure($xml, $gid);
    $xml->endElement(); // close columns
    $xml->endDocument();
}

/**
 * @param XMLWriter $xml
 */
function groupGetXMLStructure($xml, $gid)
{

    $gid = sanitize_paranoid_string($gid);

    // QuestionGroup
    $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
    $gquery = "SELECT *
    FROM $quotedGroups
    WHERE gid=$gid";
    buildXMLFromQuery($xml, $gquery, 'groups');

    // Questions table
    $qquery = "SELECT *
    FROM {{questions}}
    WHERE gid=$gid and parent_qid=0 order by question_order, language, scale_id";
    buildXMLFromQuery($xml, $qquery);

    // Questions table - Subquestions
    $qquery = "SELECT *
    FROM {{questions}}
    WHERE gid=$gid and parent_qid>0 order by question_order, language, scale_id";
    buildXMLFromQuery($xml, $qquery, 'subquestions');

    //Answer
    $aquery = "SELECT DISTINCT {{answers}}.*
    FROM {{answers}}, {{questions}}
    WHERE ({{answers}}.qid={{questions}}.qid)
    AND ({{questions}}.gid=$gid)";
    buildXMLFromQuery($xml, $aquery);

    //Condition - THIS CAN ONLY EXPORT CONDITIONS THAT RELATE TO THE SAME GROUP
    $cquery = "SELECT DISTINCT c.*
    FROM {{conditions}} c, {{questions}} q, {{questions}} b
    WHERE (c.cqid=q.qid)
    AND (c.qid=b.qid)
    AND (q.gid={$gid})
    AND (b.gid={$gid})";
    buildXMLFromQuery($xml, $cquery, 'conditions');

    //Question attributes
    $iSurveyID = Yii::app()->db->createCommand("select sid from ".Yii::app()->db->quoteTableName('{{groups}}')." where gid={$gid}")->query()->read();
    $iSurveyID = $iSurveyID['sid'];
    $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
    $platform = Yii::app()->db->getDriverName();
    if ($platform == 'mssql' || $platform == 'sqlsrv' || $platform == 'dblib') {
        $query = "SELECT qa.qid, qa.attribute, cast(qa.value as varchar(4000)) as value, qa.language
        FROM {{question_attributes}} qa JOIN {{questions}}  q ON q.qid = qa.qid AND q.sid={$iSurveyID} and q.gid={$gid}
        where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute,  cast(qa.value as varchar(4000)), qa.language";
    } else {
        $query = "SELECT qa.qid, qa.attribute, qa.value, qa.language
        FROM {{question_attributes}} qa JOIN {{questions}}  q ON q.qid = qa.qid AND q.sid={$iSurveyID} and q.gid={$gid}
        where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute, qa.value, qa.language";
    }
    buildXMLFromQuery($xml, $query, 'question_attributes');

    // Default values
    $query = "SELECT dv.*
    FROM {{defaultvalues}} dv
    JOIN {{questions}} ON {{questions}}.qid = dv.qid
    AND {{questions}}.language=dv.language
    AND {{questions}}.gid=$gid
    order by dv.language, dv.scale_id";
    buildXMLFromQuery($xml, $query, 'defaultvalues');
}


// DUMP THE RELATED DATA FOR A SINGLE QUESTION INTO A SQL FILE FOR IMPORTING LATER ON OR
// ON ANOTHER SURVEY SETUP DUMP ALL DATA WITH RELATED QID FROM THE FOLLOWING TABLES
//  - Questions
//  - Answer
//  - Question attributes
//  - Default values
/**
 * @param string $action
 */
function questionExport($action, $iSurveyID, $gid, $qid)
{
    $fn = "limesurvey_question_$qid.lsq";
    $xml = getXMLWriter();

    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=$fn");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
    header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
    header("Cache-Control: must-revalidate, no-store, no-cache");
    // HTTP/1.0
    $xml->openURI('php://output');

    $xml->setIndent(true);
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('document');
    $xml->writeElement('LimeSurveyDocType', 'Question');
    $xml->writeElement('DBVersion', getGlobalSetting('DBVersion'));
    $xml->startElement('languages');
    $aLanguages = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
    $aLanguages[] = Survey::model()->findByPk($iSurveyID)->language;
    foreach ($aLanguages as $sLanguage) {
        $xml->writeElement('language', $sLanguage);
    }
    $xml->endElement();
    questionGetXMLStructure($xml, $gid, $qid);
    $xml->endElement(); // close columns
    $xml->endDocument();
    exit;
}

/**
 * @param XMLWriter $xml
 */
function questionGetXMLStructure($xml, $gid, $qid)
{
    $gid = sanitize_paranoid_string($gid);
    $qid = sanitize_paranoid_string($qid);
    // Questions table
    $qquery = "SELECT *
    FROM {{questions}}
    WHERE qid=$qid and parent_qid=0 order by language, scale_id, question_order";
    buildXMLFromQuery($xml, $qquery);

    // Questions table - Subquestions
    $qquery = "SELECT *
    FROM {{questions}}
    WHERE parent_qid=$qid order by language, scale_id, question_order";
    buildXMLFromQuery($xml, $qquery, 'subquestions');


    // Answer table
    $aquery = "SELECT *
    FROM {{answers}}
    WHERE qid = $qid order by language, scale_id, sortorder";
    buildXMLFromQuery($xml, $aquery);



    // Question attributes
    $iSurveyID = Yii::app()->db->createCommand("select sid from ".Yii::app()->db->quoteTableName('{{groups}}')." where gid={$gid}")->query();
    $iSurveyID = $iSurveyID->read();
    $iSurveyID = $iSurveyID['sid'];
    $sBaseLanguage = Survey::model()->findByPk($iSurveyID)->language;
    $platform = Yii::app()->db->getDriverName();
    if ($platform == 'mssql' || $platform == 'sqlsrv' || $platform == 'dblib') {
        $query = "SELECT qa.qid, qa.attribute, cast(qa.value as varchar(4000)) as value, qa.language
        FROM {{question_attributes}} qa JOIN {{questions}}  q ON q.qid = qa.qid AND q.sid={$iSurveyID} and q.qid={$qid}
        where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute,  cast(qa.value as varchar(4000)), qa.language";
    } else {
        $query = "SELECT qa.qid, qa.attribute, qa.value, qa.language
        FROM {{question_attributes}} qa JOIN {{questions}}  q ON q.qid = qa.qid AND q.sid={$iSurveyID} and q.qid={$qid}
        where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute, qa.value, qa.language";
    }
    buildXMLFromQuery($xml, $query);

    // Default values
    $query = "SELECT *
    FROM {{defaultvalues}}
    WHERE qid=$qid  order by language, scale_id";
    buildXMLFromQuery($xml, $query);

}


/**
 * @param integer $iSurveyID
 */
function tokensExport($iSurveyID)
{
    $sEmailFiter = trim(App()->request->getPost('filteremail'));
    $iTokenStatus = App()->request->getPost('tokenstatus');
    $iInvitationStatus = App()->request->getPost('invitationstatus');
    $iReminderStatus = App()->request->getPost('reminderstatus');
    $sTokenLanguage = App()->request->getPost('tokenlanguage');

    $oSurvey = Survey::model()->findByPk($iSurveyID);
    $bIsNotAnonymous = ($oSurvey->anonymized == 'N' && $oSurvey->active == 'Y'); // db table exist (survey_$iSurveyID) ?
    $bIsDateStamped = ($oSurvey->datestamp == 'Y' && $oSurvey->active == 'Y'); // db table exist (survey_$iSurveyID) ?
    $attrfieldnames = getAttributeFieldNames($iSurveyID);
    
    $oRecordSet = Yii::app()->db->createCommand()->from("{{tokens_$iSurveyID}} lt");
    $databasetype = Yii::app()->db->getDriverName();
    $oRecordSet->where("1=1");
    if ($sEmailFiter != '') {
        if (in_array($databasetype, array('mssql', 'sqlsrv', 'dblib'))) {
            $oRecordSet->andWhere("CAST(lt.email as varchar) like ".App()->db->quoteValue('%'.$sEmailFiter.'%'));
        } else {
            $oRecordSet->andWhere("lt.email like ".App()->db->quoteValue('%'.$sEmailFiter.'%'));
        }
    }
    if ($iTokenStatus == 1) {
        $oRecordSet->andWhere("lt.completed<>'N'");
    } elseif ($iTokenStatus == 2) {
        $oRecordSet->andWhere("lt.completed='N'");
        if ($bIsNotAnonymous) {
            $oRecordSet->leftJoin("{{survey_$iSurveyID}} ls", 'lt.token=ls.token');
            $oRecordSet->select("lt.*, ls.id");
        }
    }
    if ($iTokenStatus == 3 && $bIsNotAnonymous) {
        $oRecordSet->leftJoin("{{survey_$iSurveyID}} ls", 'lt.token=ls.token');
        $oRecordSet->andWhere("lt.completed='N'");
        $oRecordSet->andWhere("ls.id IS NULL");
        $oRecordSet->select("lt.*, ls.id");
    }
    if ($iTokenStatus == 4 && $bIsNotAnonymous) {
        // create comma-separated string from attribute names to be used in this sql query
        if (!empty($attrfieldnames)){
            $sAttributes = ', ' . implode(', ', $attrfieldnames);
        } else {
            $sAttributes = '';
        }
        $oRecordSet->selectDistinct('lt.tid, lt.firstname, lt.lastname, lt.email, lt.emailstatus, lt.token, lt.language, lt.sent, lt.remindersent, lt.remindercount, lt.completed, lt.usesleft, lt.validfrom, lt.validuntil' . $sAttributes . ($bIsDateStamped ? ', MAX(ls.startdate) as started' : ''));
        $oRecordSet->join("{{survey_$iSurveyID}} ls", 'lt.token=ls.token');
        $oRecordSet->andWhere("ls.submitdate IS NULL");
        $oRecordSet->andWhere("lt.completed='N'");
        if ($bIsDateStamped){
            $oRecordSet->andWhere("ls.startdate IS NOT NULL");
            $oRecordSet->group('lt.tid, lt.firstname, lt.lastname, lt.email, lt.emailstatus, lt.token, lt.language, lt.sent, lt.remindersent, lt.remindercount, lt.completed, lt.usesleft, lt.validfrom, lt.validuntil, ' . $sAttributes);
        }
    }

    if ($iInvitationStatus == 1) {
        $oRecordSet->andWhere("lt.sent<>'N'");
    }
    if ($iInvitationStatus == 2) {
        $oRecordSet->andWhere("lt.sent='N'");
    }

    if ($iReminderStatus == 1) {
        $oRecordSet->andWhere("lt.remindersent<>'N'");
    }
    if ($iReminderStatus == 2) {
        $oRecordSet->andWhere("lt.remindersent='N'");
    }

    if ($sTokenLanguage != '') {
        $oRecordSet->andWhere("lt.language=".App()->db->quoteValue($sTokenLanguage));
    }
    $oRecordSet->order("lt.tid");
    $bresult = $oRecordSet->query();
    //HEADERS should be after the above query else timeout errors in case there are lots of tokens!
    header("Content-Disposition: attachment; filename=tokens_".$iSurveyID.".csv");
    header("Content-type: text/comma-separated-values; charset=UTF-8");
    header("Cache-Control: must-revalidate, no-store, no-cache");

    // Export UTF8 WITH BOM
    $tokenoutput = chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF'));
    $tokenoutput .= "tid,firstname,lastname,email,emailstatus,token,language,validfrom,validuntil,invited,reminded,remindercount,completed,usesleft";
    if ($iTokenStatus == 4 && $bIsNotAnonymous && $bIsDateStamped) {
        $tokenoutput .= ',started';
    }
    $attrfielddescr = getTokenFieldsAndNames($iSurveyID, true);
    foreach ($attrfieldnames as $attr_name) {
        $tokenoutput .= ", $attr_name";
        if (isset($attrfielddescr[$attr_name])) {
                    $tokenoutput .= " <".str_replace(",", " ", $attrfielddescr[$attr_name]['description']).">";
        }
    }
    $tokenoutput .= "\n";
    echo $tokenoutput;
    $tokenoutput = "";

    // Export token line by line and fill $aExportedTokens with token exported
    Yii::import('application.libraries.Date_Time_Converter', true);
    $aExportedTokens = array();
    while ($brow = $bresult->read()) {
        if (Yii::app()->request->getPost('maskequations')){
            $brow=array_map('MaskFormula',(array) $brow);
        }
        if (trim($brow['validfrom'] != '')) {
            $datetimeobj = new Date_Time_Converter($brow['validfrom'], "Y-m-d H:i:s");
            $brow['validfrom'] = $datetimeobj->convert('Y-m-d H:i');
        }
        if (trim($brow['validuntil'] != '')) {
            $datetimeobj = new Date_Time_Converter($brow['validuntil'], "Y-m-d H:i:s");
            $brow['validuntil'] = $datetimeobj->convert('Y-m-d H:i');
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
        if ($iTokenStatus == 4 && $bIsNotAnonymous && $bIsDateStamped) {
            $tokenoutput .= '"'.trim($brow['started']).'",';
        }
        foreach ($attrfieldnames as $attr_name) {
            $tokenoutput .= '"'.trim($brow[$attr_name]).'",';
        }
        $tokenoutput = substr($tokenoutput, 0, -1); // remove last comma
        $tokenoutput .= "\n";
        echo $tokenoutput;
        $tokenoutput = '';

        $aExportedTokens[] = $brow['tid'];
    }

    if (Yii::app()->request->getPost('tokendeleteexported') && Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'delete') && !empty($aExportedTokens)) {
        Token::model($iSurveyID)->deleteByPk($aExportedTokens);
    }
}

/**
 * @param string $filename
 */
function CPDBExport($data, $filename)
{

    header("Content-Disposition: attachment; filename=".$filename.".csv");
    header("Content-type: text/comma-separated-values; charset=UTF-8");
    header("Cache-Control: must-revalidate, no-store, no-cache");
    echo chr(hexdec('EF')).chr(hexdec('BB')).chr(hexdec('BF')); // UTF-8 BOM

    $handler = fopen('php://output', 'w');
    foreach ($data as $key=>$value) {
        fputcsv($handler, $value);
    }
    fclose($handler);
    exit;
}

/**
 * Find the string size according DB size for existing question
 * Column name must be SGQA currently
 * @param string sColumn column
 * @return integer
 **/
function stringSize($sColumn)
{
    // Find the sid
    $iSurveyId = substr($sColumn, 0, strpos($sColumn, 'X'));
    switch (Yii::app()->db->driverName) {
        case 'sqlsrv':
        case 'dblib':
        case 'mssql':
            $lengthWord = 'LEN';
            break;
        default:
            $lengthWord = 'LENGTH';
    }
    $lengthReal = Yii::app()->db->createCommand()
    ->select("{$lengthWord}(".Yii::app()->db->quoteColumnName($sColumn).")")
    ->from("{{survey_".$iSurveyId."}}")
    ->order("{$lengthWord}(".Yii::app()->db->quoteColumnName($sColumn).")  DESC")
    ->limit(1)
    ->queryScalar();
    // PSPP didn't accept A0 then min value to 1, see bug #13008
    return max(1, (int) $lengthReal);
}
/**
 * Find the numeric size according DB size for existing question for SPSS export
 * Column name must be SGQA currently
 * @param string sColumn column
 * @param boolean $decimal db type as decimal(30,10)
 * @return string integersize.decimalsize
 **/
function numericSize($sColumn,$decimal=false)
{
    $sColumn = sanitize_paranoid_string($sColumn);
    // Find the sid
    $iSurveyId = substr($sColumn, 0, strpos($sColumn, 'X'));
    $sColumn = Yii::app()->db->quoteColumnName($sColumn);
    /* Find the max len of integer part for positive value*/
    $maxInteger = Yii::app()->db
    ->createCommand("SELECT MAX($sColumn) FROM {{survey_".$iSurveyId."}}")
    ->queryScalar();
    $integerMaxLen = strlen(intval($maxInteger));
    /* Find the max len of integer part for negative value including minus when export (adding 1 to lenght) */
    $minInteger = Yii::app()->db
    ->createCommand("SELECT MIN($sColumn) FROM {{survey_".$iSurveyId."}}")
    ->queryScalar();
    $integerMinLen = strlen(intval($minInteger));
    /* Get size of integer part */
    $maxIntegerLen = max([$integerMaxLen, $integerMinLen]);
    /* Find the max len of decimal part */
    if($decimal) {
        /* We have a DECIMAL(30,10) then can always take the last 10 digit and inverse */
        /* According to doc : mysql and mssql didn't need cast, only pgsql > 8.4 */
        $castedColumnString = $sColumn;
        if(Yii::app()->db->driverName == 'pgsql') {
            $castedColumnString = "CAST($sColumn as text)";
        }
        $maxDecimal = Yii::app()->db
        ->createCommand("SELECT MAX(REVERSE(RIGHT($castedColumnString, 10))) FROM {{survey_".$iSurveyId."}}")
        ->queryScalar();
    } else {
        /* Didn't work with text, when datatype are updated to text, but in such case : there are no good solution, except return string …*/
        $castedColumnString = $sColumn;
        if(Yii::app()->db->driverName == 'pgsql') {
            $castedColumnString = "CAST($sColumn as FLOAT)";
        }
	/* pgsql */
        if(Yii::app()->db->driverName == 'pgsql') {
            $maxDecimal = Yii::app()->db
            ->createCommand("SELECT MAX(CAST(nullif(split_part($castedColumnString, '.', 2),'') as integer))
			    FROM {{survey_".$iSurveyId."}}")	
            ->queryScalar();
	/* mssql */
	} elseif (Yii::app()->db->driverName == 'mssql') {
           $maxDecimal = Yii::app()->db
            ->createCommand("SELECT MAX(CASE 
			     WHEN charindex('.',$castedColumnString) > 0 THEN 
                             CAST(SUBSTRING($castedColumnString ,charindex('.',$castedColumnString)+1 , Datalength($castedColumnString)-charindex('.',$castedColumnString) ) AS INT)
                             ELSE null END)
			    FROM {{survey_".$iSurveyId."}}")	
            ->queryScalar();			
	/* mysql */
        } else {
            $maxDecimal = Yii::app()->db
            ->createCommand("SELECT MAX(CASE
                             WHEN INSTR($castedColumnString, '.') THEN CAST(SUBSTRING_INDEX($castedColumnString, '.', -1) as UNSIGNED)
			     ELSE NULL END)
			     FROM {{survey_".$iSurveyId."}}")	
            ->queryScalar();
    	}	
	
    }
    // With integer : Decimal return 00000000000 and float return 0
    // With decimal : Decimal return 00000000012 and float return 12
    if (intval($maxDecimal)) {
        $decimalMaxLen = strlen(intval($maxDecimal));
        // Width is integer width + the dot + decimal width
        $maxLen = $maxIntegerLen + 1 + $decimalMaxLen;
    } else {
        $decimalMaxLen = 0; // Or just return $maxIntegerLen ?
        $maxLen = $maxIntegerLen;
    }
    return $maxLen.".".$decimalMaxLen;
}

/**
 * Export survey to TSV format
 * It is using existing XML function to get the same source data as lss format
 * @param int surveyid
 * @return string
 **/
function tsvSurveyExport($surveyid){
    // TODO: refactor and simplify this code
    // data loops located on first part should be replaced with one loop which writes all data in one big array
    // $tsv_output arrays should be created automatically, just need to create helper array with mapping column names between xml and tsv formats
    $fn = "limesurvey_survey_{$surveyid}.txt";

    $aBaseFields = array(
        'id',         // primary key
        'related_id', // foreign key
        'class',
        'type/scale',
        'name',
        'relevance',
        'text',
        'help',
        'language',
        'validation',
        'mandatory',
        'other',
        'default',
        'same_default',
    );

    $survey = Survey::model()->findByPk($surveyid);
    $aSurveyLanguages = $survey->getAllLanguages();

    // Advanced question attributes : @todo get used question attribute by question in survey ?
    $aQuestionAttributes=array_keys(\LimeSurvey\Helpers\questionHelper::getAttributesDefinitions());
    sort($aQuestionAttributes);
    $fields=array_merge($aBaseFields,$aQuestionAttributes);
    // Reusing existing XML function to get data for exporting into TSV format
    // That way the same data source is used for both XML and TSV formats
    $xml = simplexml_load_string(surveyGetXMLData($surveyid), null, LIBXML_NOCDATA);
    $xmlData = json_decode(json_encode($xml), TRUE);

    // creating an array where attributes are keys, to be reused for each row
    // flip keys and values, fields becoming keys, values are cleared with array_map function
    $fields = array_map(function () { return ''; }, array_flip($fields));
    $out = fopen('php://output', 'w');
    fputcsv($out, array_map('MaskFormula',array_keys($fields)), chr(9));

    // DATA PREPARATION
    // survey settings
    if (array_key_exists('surveys', $xmlData)){
        $surveys_data = $xmlData['surveys']['rows']['row'];
    } else {
        $surveys_data = array();
    }

    foreach ($surveys_data as $key => $value) {

        if (is_array($value)){

            if (count($value) === 0){
                $value = '';
            } else {
                $value = $value;
            }

        }
        $tsv_output = $fields;
        $tsv_output['class'] = 'S';
        $tsv_output['name'] = $key;
        $tsv_output['text'] = str_replace(array("\n", "\r"), '', $value);
        fputcsv($out, array_map('MaskFormula',$tsv_output), chr(9));
    }

    // language settings
    if (array_key_exists('surveys_languagesettings', $xmlData)){
        $language_data = $xmlData['surveys_languagesettings']['rows']['row'];
        if (!array_key_exists('0', $language_data)){
            $language_data = array($language_data);
        }
    } else {
        $language_data = array();
    }

    if (!array_key_exists('surveyls_language', $language_data[0])){
        $language_data[0]['surveyls_language'] = $aSurveyLanguages[0];
    }

    foreach ($language_data as $key => $language) {  //echo $key.'---'; print_r($language); die;
        $current_language = !empty($language['surveyls_language'])?$language['surveyls_language']:'';
        foreach ((array)$language as $key => $value) {
            if (is_array($value)){
                if (count($value) === 0){
                    $value = '';
                } else {
                    $value = $value[0];
                }
            }
            $tsv_output = $fields;
            $tsv_output['class'] = 'SL';
            $tsv_output['name'] = $key;
            $tsv_output['text'] = str_replace(array("\n", "\r"), '', $value);
            $tsv_output['language'] = $current_language;
            fputcsv($out, array_map('MaskFormula',$tsv_output), chr(9));
        }
    }

    // attributes data
    if (array_key_exists('question_attributes', $xmlData)){
        $attributes_data = $xmlData['question_attributes']['rows']['row'];
        if (!array_key_exists('0', $attributes_data)){
            $attributes_data = array($attributes_data);
        }
    } else {
        $attributes_data = array();
    }
    $attributes = array();
    foreach ($attributes_data as $key => $attribute) {
        $attributes[$attribute['qid']][] = $attribute;
    }

    // default values data
    if (array_key_exists('defaultvalues', $xmlData)){
        $defaultvalues_data = $xmlData['defaultvalues']['rows']['row'];
        if (!array_key_exists('0', $defaultvalues_data)){
            $defaultvalues_data = array($defaultvalues_data);
        }
    } else {
        $defaultvalues_data = array();
    }
    $defaultvalues = array();
    foreach ($defaultvalues_data as $key => $defaultvalue) {
        if ($defaultvalue['sqid'] > 0){
            $defaultvalues[$defaultvalue['language']][$defaultvalue['sqid']] = $defaultvalue['defaultvalue'];
        } else {
            $defaultvalues[$defaultvalue['language']][$defaultvalue['qid']] = $defaultvalue['defaultvalue'];
        }
    }

    $groups = array();
    $index_languages = 0;
    foreach ($aSurveyLanguages as $key => $language) {
        // groups
        if (array_key_exists('groups', $xmlData)){
            $groups_data = $xmlData['groups']['rows']['row'];
            if (!array_key_exists('0', $groups_data)){
                $groups_data = array($groups_data);
            }
        } else {
            $groups_data = array();
        }
        $groups = array();
        foreach ($groups_data as $key => $group) {
            if ($group['language'] === $language){
                $groups[$language][$group['gid']] = $group;
            }
        }

        // questions data
        if (array_key_exists('questions', $xmlData)){
            $questions_data = $xmlData['questions']['rows']['row'];
            if (!array_key_exists('0', $questions_data)){
                $questions_data = array($questions_data);
            }
        } else {
            $questions_data = array();
        }
        $questions = array();
        foreach ($questions_data as $key => $question) {
            if ($question['language'] === $language){
                $questions[$language][$question['gid']][$question['qid']] = $question;
            }
        }

        // subquestions data
        if (array_key_exists('subquestions', $xmlData)){
            $subquestions_data = $xmlData['subquestions']['rows']['row'];
            if (!array_key_exists('0', $subquestions_data)){
                $subquestions_data = array($subquestions_data);
            }
        } else {
            $subquestions_data = array();
        }
        $subquestions = array();
        foreach ($subquestions_data as $key => $subquestion) {
            if ($subquestion['language'] === $language){
                $subquestions[$language][$subquestion['parent_qid']][] = $subquestion;
            }
        }

        // answers data
        if (array_key_exists('answers', $xmlData)){
            $answers_data = $xmlData['answers']['rows']['row'];
            if (!array_key_exists('0', $answers_data)){
                $answers_data = array($answers_data);
            }
        } else {
            $answers_data = array();
        }
        $answers = array();
        foreach ($answers_data as $key => $answer) {
            if ($answer['language'] === $language){
                $answers[$language][$answer['qid']][] = $answer;
            }
        }

        // assessments data
        if (array_key_exists('assessments', $xmlData)){
            $assessments_data = $xmlData['assessments']['rows']['row'];
            if (!array_key_exists('0', $assessments_data)){
                $assessments_data = array($assessments_data);
            }
        } else {
            $assessments_data = array();
        }
        $assessments = array();
        foreach ($assessments_data as $key => $assessment) {
                $assessments[] = $assessment;
        }

        // quotas data
        if (array_key_exists('quota', $xmlData)){
            $quotas_data = $xmlData['quota']['rows']['row'];
            if (!array_key_exists('0', $quotas_data)){
                $quotas_data = array($quotas_data);
            }
        } else {
            $quotas_data = array();
        }
        $quotas = array();
        foreach ($quotas_data as $key => $quota) {
                $quotas[$quota['id']] = $quota;
        }

        // quota members data
        if (array_key_exists('quota_members', $xmlData)){
            $quota_members_data = $xmlData['quota_members']['rows']['row'];
            if (!array_key_exists('0', $quota_members_data)){
                $quota_members_data = array($quota_members_data);
            }
        } else {
            $quota_members_data = array();
        }
        $quota_members = array();
        foreach ($quota_members_data as $key => $quota_member) {
            $quota_members[$quota_member['qid']][] = $quota_member;
        }

        // quota language settings data
        if (array_key_exists('quota_languagesettings', $xmlData)){
            $quota_ls_data = $xmlData['quota_languagesettings']['rows']['row'];
            if (!array_key_exists('0', $quota_ls_data)){
                $quota_ls_data = array($quota_ls_data);
            }
        } else {
            $quota_ls_data = array();
        }
        $quota_ls = array();
        foreach ($quota_ls_data as $key => $quota) {
            $quota_ls[$quota['quotals_quota_id']][$quota['quotals_language']][] = $quota;
        }

        // conditions
        if (array_key_exists('conditions', $xmlData)){
            $condition_data = $xmlData['conditions']['rows']['row'];
            if (!array_key_exists('0', $condition_data)){
                $condition_data = array($condition_data);
            }
        } else {
            $condition_data = array();
        }
        $conditions = array();
        foreach ($condition_data as $key => $condition) {
            $conditions[$condition['qid']][] = $condition;
        }

        if (!empty($groups)){
            $groups[$language] = sortArrayByColumn($groups[$language], 'group_order');
            foreach ($groups[$language] as $gid => $group) {
                $tsv_output = $fields;
                $tsv_output['id'] = $gid;
                $tsv_output['class'] = 'G';
                $tsv_output['type/scale'] = $group['group_order'];
                $tsv_output['name'] = !empty($group['group_name']) ? $group['group_name'] : '';
                $tsv_output['text'] = !empty($group['description']) ? str_replace(array("\n", "\r"), '', $group['description']) : '';
                $tsv_output['relevance'] = !empty($group['grelevance']) ? $group['grelevance'] : '';
                $tsv_output['random_group'] = !empty($group['randomization_group']) ? $group['randomization_group'] : '';
                $tsv_output['language'] = $language;
                fputcsv($out, array_map('MaskFormula',$tsv_output), chr(9));

                // questions
                if (array_key_exists($gid, $questions[$language])){
                    $questions[$language][$gid] = sortArrayByColumn($questions[$language][$gid], 'question_order');
                    foreach ($questions[$language][$gid] as $qid => $question) {
                        $tsv_output = $fields;
                        $tsv_output['id'] = $question['qid'];
                        $tsv_output['class'] = 'Q';
                        $tsv_output['type/scale'] = $question['type'];
                        $tsv_output['name'] = !empty($question['title']) ? $question['title'] : '';
                        $tsv_output['relevance'] = !empty($question['relevance']) ? $question['relevance'] : '';
                        $tsv_output['text'] = !empty($question['question']) ? str_replace(array("\n", "\r"), '', $question['question']) : '';
                        $tsv_output['help'] = !empty($question['help']) ? str_replace(array("\n", "\r"), '', $question['help']) : '';
                        $tsv_output['language'] = $question['language'];
                        $tsv_output['mandatory'] = !empty($question['mandatory']) ? $question['mandatory'] : '';
                        $tsv_output['other'] = $question['other'];
                        $tsv_output['same_default'] = $question['same_default'];

                        if (array_key_exists($language, $defaultvalues) && array_key_exists($qid, $defaultvalues[$language])){
                            $tsv_output['default'] = $defaultvalues[$language][$qid];
                        }

                        // question attributes
                        if ($index_languages == 0 && array_key_exists($question['qid'], $attributes)){
                            foreach ($attributes[$question['qid']] as $key => $attribute) {
                                if (in_array($attribute['attribute'], array_keys($fields))){
                                    if (is_array($attribute['value'])){
                                        if (safecount($attribute['attribute']) > 0){
                                            $tsv_output[$attribute['attribute']] = implode(' ', $attribute['value']);
                                        }
                                    } else {
                                        $tsv_output[$attribute['attribute']] = $attribute['value'];
                                    }
                                }
                            }
                        }
                        fputcsv($out, array_map('MaskFormula',$tsv_output), chr(9));
                                                
                        // quota members
                        if ($index_languages == 0 && !empty($quota_members[$qid])){
                            foreach ($quota_members[$qid] as $key => $member) {
                                $tsv_output = $fields;
                                $tsv_output['id'] = $member['id'];
                                $tsv_output['related_id'] = $member['quota_id'];
                                $tsv_output['class'] = 'QTAM';
                                $tsv_output['name'] = $member['code'];
                                fputcsv($out, array_map('MaskFormula',$tsv_output), chr(9));
                            }
                        }

                        // conditions
                        if ($index_languages == 0 && !empty($conditions[$qid])){
                            foreach ($conditions[$qid] as $key => $condition) {
                                $tsv_output = $fields;
                                $tsv_output['id'] = $condition['cid'];
                                $tsv_output['class'] = 'C';
                                $tsv_output['type/scale'] = $condition['scenario'];
                                $tsv_output['related_id'] = $condition['cqid'];
                                $tsv_output['name'] = $condition['cfieldname'];
                                $tsv_output['relevance'] = $condition['method'];
                                $tsv_output['text'] = !empty($assessment['value']) ? $condition['value'] : '';
                                fputcsv($out, array_map('MaskFormula',$tsv_output), chr(9));
                            }
                        }

                        //subquestions
                        if (!empty($subquestions[$language][$qid])){
                            $subquestions[$language][$qid] = sortArrayByColumn($subquestions[$language][$qid], 'question_order');
                            foreach ($subquestions[$language][$qid] as $key => $subquestion) {
                                $tsv_output = $fields;
                                $tsv_output['id'] = $subquestion['qid'];
                                $tsv_output['class'] = 'SQ';
                                $tsv_output['type/scale'] = !empty($subquestion['scale_id']) ? $subquestion['scale_id'] : '';
                                $tsv_output['name'] = $subquestion['title'];
                                $tsv_output['relevance'] = !empty($subquestion['relevance']) ? $subquestion['relevance'] : '';
                                $tsv_output['text'] = $subquestion['question'];
                                $tsv_output['language'] = $subquestion['language'];
                                $tsv_output['mandatory'] = !empty($subquestion['mandatory']) ? $subquestion['mandatory'] : '';
                                $tsv_output['other'] = $subquestion['other'];
                                $tsv_output['same_default'] = $subquestion['same_default'];

                                if (array_key_exists($language, $defaultvalues) && array_key_exists($subquestion['qid'], $defaultvalues[$language])){
                                    $tsv_output['default'] = $defaultvalues[$language][$subquestion['qid']];
                                }
                                fputcsv($out, array_map('MaskFormula',$tsv_output), chr(9));
                            }
                        }

                        // answers
                        if (!empty($answers[$language][$qid])){
                            $answers[$language][$qid] = sortArrayByColumn($answers[$language][$qid], 'sortorder');
                            foreach ($answers[$language][$qid] as $key => $answer) {
                                $tsv_output = $fields;
                                $tsv_output['id'] = $answer['qid'];
                                $tsv_output['class'] = 'A';
                                $tsv_output['type/scale'] = $answer['scale_id'];
                                $tsv_output['name'] = $answer['code'];
                                $tsv_output['text'] = $answer['answer'];
                                $tsv_output['assessment_value'] = $answer['assessment_value'];
                                $tsv_output['language'] = $answer['language'];
                                fputcsv($out, array_map('MaskFormula',$tsv_output), chr(9));
                            }
                        }
                    }
                }
            }
            $index_languages += 1;
        }
    }

    // assessments
    if (!empty($assessments)){
        //$assessments[$gid] = sortArrayByColumn($assessments[$gid], 'other');
        foreach ($assessments as $key => $assessment) {
            $tsv_output = $fields;
            $tsv_output['id'] = $assessment['id'];
            $tsv_output['related_id'] = $assessment['gid'];
            $tsv_output['class'] = 'AS';
            $tsv_output['type/scale'] = $assessment['scope'];
            $tsv_output['name'] = !empty($assessment['name']) ? $assessment['name']:'';
            $tsv_output['text'] = !empty($assessment['message']) ? $assessment['message']:'';
            $tsv_output['min_num_value'] = $assessment['minimum'];
            $tsv_output['max_num_value'] = $assessment['maximum'];
            $tsv_output['language'] = $assessment['language'];
            fputcsv($out, array_map('MaskFormula',$tsv_output), chr(9));
        }
    }

    // quotas
    if (!empty($quotas)){
        $quotas = sortArrayByColumn($quotas, 'id');
        foreach ($quotas as $key => $quota) {
            $tsv_output = $fields;
            $tsv_output['id'] = $quota['id'];
            $tsv_output['class'] = 'QTA';
            $tsv_output['mandatory'] = $quota['qlimit'];
            $tsv_output['name'] = $quota['name'];
            $tsv_output['other'] = $quota['action'];
            $tsv_output['default'] = $quota['active'];
            $tsv_output['same_default'] = $quota['autoload_url'];
            fputcsv($out, array_map('MaskFormula',$tsv_output), chr(9));

                if (!empty($quota_ls[$quota['id']])){
                foreach ($quota_ls[$quota['id']] as $key => $language) {
                    foreach ($language as $key => $ls) {
                        $tsv_output = $fields;
                        $tsv_output['id'] = $ls['quotals_id'];
                        $tsv_output['related_id'] = $quota['id'];
                        $tsv_output['class'] = 'QTALS';
                        //$tsv_output['name'] = $ls['quotals_name'];
                        $tsv_output['relevance'] = $ls['quotals_message'];
                        $tsv_output['text'] = !empty($ls['quotals_url']) ? $ls['quotals_url'] : '';
                        $tsv_output['help'] = !empty($ls['quotals_urldescrip']) ? $ls['quotals_urldescrip'] : '';
                        $tsv_output['language'] = $ls['quotals_language'];
                        fputcsv($out, array_map('MaskFormula',$tsv_output), chr(9));
                    }
                }
            }
        }
    }

    $output = $out;
    fclose($out);
    return $output;
}

/**
 * Sort array by column name
 * @param array $array
 * @param string $column_name
 **/
function sortArrayByColumn($array, $column_name){
    $keys = array_keys($array);
    array_multisort(
        array_column($array, $column_name), SORT_ASC, SORT_NUMERIC, $array, $keys
    );
    $array = array_combine($keys, $array);
    return $array;
}

/**
* Write XML from Associative Array, recursive function
* @param object $xml XMLWriter Object
* @param array $aData Associative Data Array
* @param int $sParentKey parent key
*/
function writeXmlFromArray(XMLWriter $xml, $aData, $sParentKey='') {
    $bCloseElement = false;
    foreach($aData as $key => $value) {
        if (!empty($value)){
            if(is_array($value)) {

                if (is_numeric($key)){
                    $xml->startElement($sParentKey);
                    $bCloseElement = true;
                } elseif (isAssociativeArray($value)){
                    $xml->startElement($key);
                    $bCloseElement = true;
                }

                if (is_numeric($key)){
                    writeXmlFromArray($xml, $value, $sParentKey);
                } else {
                    writeXmlFromArray($xml, $value, $key);
                }

                if ($bCloseElement === true){
                    $xml->endElement();
                    $bCloseElement = false;
                }
                continue;
            } elseif (is_numeric($key)){
                $xml->writeElement($sParentKey, $value);
            } else {
                $xml->writeElement($key, $value);
            }
        }
    }
    return $xml;
}

/**
* Write XML structure for themes
* @param int $iSurveyId Survey ID
* @param object $oXml XMLWriter Object
* @param bool $bInherit should theme configuration be inherited?
* @param string $sElementName name for XML element
*/
function surveyGetThemeConfiguration($iSurveyId = null, $oXml = null, $bInherit = false, $sElementName = 'themes'){

    $aThemeData = array();

    if ($iSurveyId != null) {
        $aSurveyConfiguration = TemplateConfiguration::getThemeOptionsFromSurveyId($iSurveyId, $bInherit);

        foreach ($aSurveyConfiguration as $iThemeKey => $oConfig) {

            foreach ($oConfig as $key => $attribute) {

                if (is_array($attribute)){
                    $attribute = (array)$attribute;
                } elseif (isJson($attribute)){
                    $attribute = (array)json_decode($attribute);
                }
                $aThemeData[$sElementName]['theme'][$iThemeKey][$key] = $attribute;
            }
        }

    }

    if ($oXml !== null && !empty($aThemeData)){

        writeXmlFromArray($oXml, $aThemeData);

    }

}


 function MaskFormula ( $sValue  ) {
     if (isset($sValue[0]) && $sValue[0]=='=') {
        $sValue="'".$sValue;    
     }
     return $sValue;
 }
