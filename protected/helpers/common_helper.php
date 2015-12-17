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
*/
use ls\components\SurveySession;
use ls\models\Answer;
use ls\models\LabelSet;
use ls\models\Question;
use ls\models\QuestionGroup;
use ls\models\Quota;
use ls\models\QuotaMember;
use ls\models\Survey;
use ls\models\SurveyDynamic;
use ls\models\Token;

/**
 * ls\models\Translation helper function
 * @param string $sToTranslate
 * @param string $sEscapeMode
 * @param string $sLanguage
 */
function gT($sToTranslate, $sEscapeMode = 'unescaped', $sLanguage = NULL)
{
    return quoteText(Yii::t('',$sToTranslate,[],null,$sLanguage),$sEscapeMode);
}

/**
 * ls\models\Translation helper function which outputs right away.
 * @param string $sToTranslate
 * @param string $sEscapeMode
 * @param string $sLanguage
 */
function eT($sToTranslate, $sEscapeMode = 'html', $sLanguage = NULL)
{
    echo gT($sToTranslate,$sEscapeMode);
}

/**
 * ls\models\Translation helper function for plural forms
 * @param string $sToTranslate
 * @param integer $iCount
 * @param string $sEscapeMode
 */
function ngT($sToTranslate, $iCount, $sEscapeMode = 'html')
{
    return quoteText(Yii::t('',$sToTranslate,$iCount),$sEscapeMode);
}

/**
 * ls\models\Translation helper function for plural forms which outputs right away
 * @param string $sToTranslate
 * @param integer $iCount
 * @param string $sEscapeMode
 */
function egT($sToTranslate, $iCount, $sEscapeMode = 'html')
{
    echo ngT($sToTranslate,$iCount,$sEscapeMode);
}

/**
* Quotes a translation according to purpose
* if sEscapeMode is null, we use HTML method because probably we had to specify null as sEscapeMode upstream
*
* @param mixed $sText Text to quote
* @param string $sEscapeMode Optional - One of the values 'html','js' or 'unescaped' - defaults to 'html'
*/
function quoteText($sText, $sEscapeMode = 'html')
{
    if ($sEscapeMode === null)
        $sEscapeMode = 'html';

    switch ($sEscapeMode)
    {
        case 'html':
            return HTMLEscape($sText);
            break;
        case 'js':
            return javascriptEscape($sText);
            break;
        case 'unescaped':
            return $sText;
            break;
        default:
            return "Unsupported EscapeMode in gT method";
            break;
    }
}









/**
* This function calculates how much space is actually used by all files uploaded
* using the File Upload question type
*
* @returns integer Actual space used in MB
*/
function calculateTotalFileUploadUsage(){
    global $uploaddir;
    $sQuery='select sid from {{surveys}}';
    $oResult = dbExecuteAssoc($sQuery); //checked
    $aRows = $oResult->readAll();
    $iTotalSize=0.0;
    foreach ($aRows as $aRow)
    {
        $sFilesPath=$uploaddir.'/surveys/'.$aRow['sid'].'/files';
        if (file_exists($sFilesPath))
        {
            $iTotalSize+=(float)getDirectorySize($sFilesPath);
        }
    }
    return (float)$iTotalSize/1024/1024;
}

function getDirectorySize($directory) {
    $size = 0;
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
        $size+=$file->getSize();
    }
    return $size;
}


/**
* getMaxGroupOrder($surveyid) queries the database for the maximum sortorder of a group and returns the next higher one.
*
* @param mixed $surveyid
*/
function getMaxGroupOrder($surveyid)
{
    $s_lang = Survey::model()->findByPk($surveyid)->language;

    //$max_sql = "SELECT max( group_order ) AS max FROM ".db_table_name('groups')." WHERE sid =$surveyid AND language='{$s_lang}'" ;
    $query = QuestionGroup::model()->find(array('order' => 'group_order desc'));
    $current_max = !is_null($query) ? $query->group_order : '';

    if($current_max!="")
    {
        return ++$current_max ;
    }
    else return "0" ;
}


/**
* getGroupOrder($surveyid,$gid) queries the database for the sortorder of a group.
*
* @param mixed $surveyid
* @param mixed $gid
* @return mixed
*/
function getGroupOrder($surveyid,$gid)
{

    $s_lang = Survey::model()->findByPk($surveyid)->language;

    //$grporder_sql = "SELECT group_order FROM ".db_table_name('groups')." WHERE sid =$surveyid AND language='{$s_lang}' AND gid=$gid" ;
    $grporder_result = QuestionGroup::model()->findByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'language' => $s_lang)); //Checked
    $grporder_row = $grporder_result->attributes ;
    $group_order = $grporder_row['group_order'];
    if($group_order=="")
    {
        return "0" ;
    }
    else return $group_order ;
}

/**
* setupColumns() defines all the html tags to be wrapped around
* various list type answers.
*
* @param integer $columns - the number of columns, usually supplied by $dcols
* @param integer $answer_count - the number of answers to a question, usually supplied by $anscount
* @param string $wrapperclass - a global class for the wrapper
* @param string $itemclass - a class for the item
* @return array with all the various opening and closing tags to generate a set of columns.
*
* It returns an array with the following items:
*    $wrapper['whole-start']   = Opening wrapper for the whole list
*    $wrapper['whole-end']     = closing wrapper for the whole list
*    $wrapper['col-devide']    = normal column devider
*    $wrapper['col-devide-last'] = the last column devider (to allow
*                                for different styling of the last
*                                column
*    $wrapper['item-start']    = opening wrapper tag for individual
*                                option
*    $wrapper['item-start-other'] = opening wrapper tag for other
*                                option
*    $wrapper['item-start-noanswer'] = opening wrapper tag for no answer
*                                option
*    $wrapper['item-end']      = closing wrapper tag for individual
*                                option
*    $wrapper['maxrows']       = maximum number of rows in each
*                                column
*    $wrapper['cols']          = Number of columns to be inserted
*                                (and checked against)
*
*
* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
* Columns are a problem.
* Really there is no perfect solution to columns at the moment.
*
* -  Using Tables is problematic semanticly.
* -  Using inline or float to create columns, causes the answers
*    flows horizontally, not vertically which is not ideal visually.
* -  Using CSS3 columns is also a problem because of browser support
*    and also because if you have answeres split across two or more
*    lines, and those answeres happen to fall at the bottom of a
*    column, the answer might be split across columns as well as
*    lines.
* -  Using nested unordered list with the first level of <LI>s
*    floated is the same as using tables and so is bad semantically
*    for the same reason tables are bad.
* -  Breaking the unordered lists into consecutive floated unordered
*    lists is not great semantically but probably not as bad as
*    using tables.
*
* Because I haven't been able to decide which option is the least
* bad, I have handed over that responsibility to the admin who sets
* LimeSurvey up on their server.
*
* There are four options:
*    'css'   using one of the various CSS only methods for
*            rendering columns.
*            (Check the CSS file for your chosen template to see
*             how columns are defined.)
*    'ul'    using multiple floated unordered lists. (DEFAULT)
*    'table' using conventional tables based layout.
*     NULL   blocks the use of columns
*
* 'ul' is the default because it's the best possible compromise
* between semantic markup and visual layout.
*/
function setupColumns($columns, $answer_count,$wrapperclass="",$itemclass="")
{

    $column_style = Yii::app()->getConfig('column_style');
    if ( !in_array($column_style,array('css','ul','table')) && !is_null($column_style) )
    {
        $column_style = 'ul';
    };
    if(!is_null($column_style) && $columns!=1) // Add a global class for all column.
    {
        $wrapperclass.= " colstyle-{$column_style}";
    }
    if($columns < 2)
    {
        $column_style = null;
        $columns = 1;
    }

    if(($columns > $answer_count) && $answer_count>0)
    {
        $columns = $answer_count;
    };


    $class_first = ' class="'.$wrapperclass.'"';
    if($columns > 1 && !is_null($column_style))
    {
        if($column_style == 'ul')
        {
            $ul = '-ul';
        }
        else
        {
            $ul = '';
        }
        $class_first = ' class="'.$wrapperclass.' cols-'.$columns . $ul.' first"';
        $class = ' class="'.$wrapperclass.' cols-'.$columns . $ul.'"';
        $class_last_ul = ' class="'.$wrapperclass.' cols-'.$columns . $ul.' last"';
        $class_last_table = ' class="'.$wrapperclass.' cols-'.$columns.' last"';
    }
    else
    {
        $class = ' class="'.$wrapperclass.'"';
        $class_last_ul = ' class="'.$wrapperclass.'"';
        $class_last_table = ' class="'.$wrapperclass.'"';
    };

    $wrapper = array(
    'whole-start'  => "\n<ul$class_first>\n"
    ,'whole-end'    => "</ul>\n"
    ,'col-devide'   => ''
    ,'col-devide-last' => ''
    ,'item-start'   => "\t<li class=\"{$itemclass}\">\n"
    ,'item-start-other' => "\t<li class=\"{$itemclass} other other-item\">\n"
    ,'item-start-noanswer' => "\t<li class=\"{$itemclass} noanswer-item\">\n"
    ,'item-end' => "\t</li>\n"
    ,'maxrows'  => ceil($answer_count/$columns) //Always rounds up to nearest whole number
    ,'cols'     => $columns
    );

    switch($column_style)
    {
        case 'ul':  if($columns > 1)
            {
                $wrapper['col-devide']  = "\n</ul>\n\n<ul$class>\n";
                $wrapper['col-devide-last'] = "\n</ul>\n\n<ul$class_last_ul>\n";
            }
            break;

        case 'table':   $table_cols = '';
            for($cols = $columns ; $cols > 0 ; --$cols)
            {
                switch($cols)
                {
                    case $columns:  $table_cols .= "\t<col$class_first />\n";
                        break;
                    case 1:     $table_cols .= "\t<col$class_last_table />\n";
                        break;
                    default:    $table_cols .= "\t<col$class />\n";
                };
            };

            if($columns > 1)
            {
                $wrapper['col-devide']  = "\t</ul>\n</td>\n\n<td>\n\t<ul>\n";
                $wrapper['col-devide-last'] = "\t</ul>\n</td>\n\n<td class=\"last\">\n\t<ul>\n";
            };
            $wrapper['whole-start'] = "\n<table$class>\n$table_cols\n\t<tbody>\n<tr>\n<td>\n\t<ul>\n";
            $wrapper['whole-end']   = "\t</ul>\n</td>\n</tr>\n\t</tbody>\n</table>\n";
            $wrapper['item-start']  = "<li class=\"{$itemclass}\">\n";
            $wrapper['item-end']    = "</li class=\"{$itemclass}\">\n";
    };

    return $wrapper;
};

function alternation($alternate = '' , $type = 'col')
{
    /**
    * alternation() Returns a class identifyer for alternating between
    * two options. Used to style alternate elements differently. creates
    * or alternates between the odd string and the even string used in
    * as column and row classes for array type questions.
    *
    * @param string $alternate = '' (empty) (default) , 'array2' ,  'array1' , 'odd' , 'even'
    * @param string  $type = 'col' (default) or 'row'
    *
    * @return string representing either the first alternation or the opposite alternation to the one supplied..
    */
    /*
    // The following allows type to be left blank for row in subsequent
    // function calls.
    // It has been left out because 'row' must be defined the first time
    // alternation() is called. Since it is only ever written once for each
    // while statement within a function, 'row' is always defined.
    if(!empty($alternate) && $type != 'row')
    {   if($alternate == ('array2' || 'array1'))
    {
    $type = 'row';
    };
    };
    // It has been left in case it becomes useful but probably should be
    // removed.
    */
    if($type == 'row')
    {
        $odd  = 'array2'; // should be row_odd
        $even = 'array1'; // should be row_even
    }
    else
    {
        $odd  = 'odd';  // should be col_odd
        $even = 'even'; // should be col_even
    };
    if($alternate == $odd)
    {
        $alternate = $even;
    }
    else
    {
        $alternate = $odd;
    };
    return $alternate;
}


/**
* longestString() returns the length of the longest string past to it.
* @peram string $new_string
* @peram integer $longest_length length of the (previously) longest string passed to it.
* @return integer representing the length of the longest string passed (updated if $new_string was longer than $longest_length)
*
* usage should look like this: $longest_length = longestString( $new_string , $longest_length );
*
*/
function longestString( $new_string , $longest_length )
{
    if($longest_length < strlen(trim(strip_tags($new_string))))
    {
        $longest_length = strlen(trim(strip_tags($new_string)));
    };
    return $longest_length;
};




/**
* Returns the default email template texts as array
*
* @param mixed $oLanguage Required language translationb object
* @param string $mode Escape mode for the translation function
* @return array
*/
function templateDefaultTexts($sLanguage, $mode='html', $sNewlines='text')
{
    $sOldLanguage=App()->language;
    App()->setLanguage($sLanguage);
    $aDefaultTexts=array(
    'admin_detailed_notification_subject'=>gT("Response submission for survey {SURVEYNAME} with results",$mode),
    'admin_detailed_notification'=>gT("Hello,\n\nA new response was submitted for your survey '{SURVEYNAME}'.\n\nClick the following link to reload the survey:\n{RELOADURL}\n\nClick the following link to see the individual response:\n{VIEWRESPONSEURL}\n\nClick the following link to edit the individual response:\n{EDITRESPONSEURL}\n\nView statistics by clicking here:\n{STATISTICSURL}\n\n\nThe following answers were given by the participant:\n{ANSWERTABLE}",$mode),
    'admin_detailed_notification_css'=>'<style type="text/css">
    .printouttable {
    margin:1em auto;
    }
    .printouttable th {
    text-align: center;
    }
    .printouttable td {
    border-color: #ddf #ddf #ddf #ddf;
    border-style: solid;
    border-width: 1px;
    padding:0.1em 1em 0.1em 0.5em;
    }

    .printouttable td:first-child {
    font-weight: 700;
    text-align: right;
    padding-right: 5px;
    padding-left: 5px;

    }
    .printouttable .printanswersquestion td{
    background-color:#F7F8FF;
    }

    .printouttable .printanswersquestionhead td{
    text-align: left;
    background-color:#ddf;
    }

    .printouttable .printanswersgroup td{
    text-align: center;
    font-weight:bold;
    padding-top:1em;
    }
    </style>',
    'admin_notification_subject'=>gT("Response submission for survey {SURVEYNAME}",$mode),
    'admin_notification'=>gT("Hello,\n\nA new response was submitted for your survey '{SURVEYNAME}'.\n\nClick the following link to reload the survey:\n{RELOADURL}\n\nClick the following link to see the individual response:\n{VIEWRESPONSEURL}\n\nClick the following link to edit the individual response:\n{EDITRESPONSEURL}\n\nView statistics by clicking here:\n{STATISTICSURL}",$mode),
    'confirmation_subject'=>gT("Confirmation of your participation in our survey"),
    'confirmation'=>gT("Dear {FIRSTNAME},\n\nthis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}",$mode),
    'invitation_subject'=>gT("Invitation to participate in a survey",$mode),
    'invitation'=>gT("Dear {FIRSTNAME},\n\nyou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",$mode)."\n\n".gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}",$mode)."\n\n".gT("If you are blacklisted but want to participate in this survey and want to receive invitations please click the following link:\n{OPTINURL}",$mode),
    'reminder_subject'=>gT("Reminder to participate in a survey",$mode),
    'reminder'=>gT("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",$mode)."\n\n".gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}",$mode),
    'registration_subject'=>gT("Survey registration confirmation",$mode),
    'registration'=>gT("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.",$mode)
    );
    if ($sNewlines=='html')
    {
        $aDefaultTexts=array_map('nl2br',$aDefaultTexts);
    }
    App()->setLanguage($sOldLanguage);
    return $aDefaultTexts;
}

/**
* Compares two elements from an array (passed by the usort function)
* and returns -1, 0 or 1 depending on the result of the comparison of
* the sort order of the group_order and question_order field
*
* @param mixed $a
* @param mixed $b
* @return int
*/
function groupOrderThenQuestionOrder($a, $b)
{
    if (isset($a['group_order']) && isset($b['group_order']))
    {
        $GroupResult = strnatcasecmp($a['group_order'], $b['group_order']);
    }
    else
    {
        $GroupResult = "";
    }
    if ($GroupResult == 0)
    {
        $TitleResult = strnatcasecmp($a["question_order"], $b["question_order"]);
        return $TitleResult;
    }
    return $GroupResult;
}

/**
* This function returns POST/REQUEST vars, for some vars like SID and others they are also sanitized
*
* @param string $stringname
* @param boolean $bRestrictToString
*/
function returnGlobal($stringname,$bRestrictToString=false)
{
    $urlParam=Yii::app()->request->getParam($stringname);
    if(is_null($urlParam) && $aCookies=Yii::app()->request->getCookies() && $stringname!='sid')
    {
        if(isset($aCookies[$stringname]))
        {
            $urlParam = $aCookies[$stringname];
        }
    }
    $bUrlParamIsArray=is_array($urlParam);// Needed to array map or if $bRestrictToString
    if (!is_null($urlParam) && $stringname!='' && (!$bUrlParamIsArray || !$bRestrictToString))
    {
        if ($stringname == 'sid' || $stringname == "gid" || $stringname == "oldqid" ||
        $stringname == "qid" || $stringname == "tid" ||
        $stringname == "lid" || $stringname == "ugid"||
        $stringname == "thisstep" || $stringname == "scenario" ||
        $stringname == "cqid" || $stringname == "cid" ||
        $stringname == "qaid" || $stringname == "scid" ||
        $stringname == "loadsecurity")
        {
            if($bUrlParamIsArray){
                return filter_var_array($urlParam, FILTER_SANITIZE_NUMBER_INT);
            }else{
                return filter_var($urlParam, FILTER_SANITIZE_NUMBER_INT);
            }
        }
        elseif ($stringname =="lang" || $stringname =="adminlang")
        {
            if($bUrlParamIsArray){
                return array_map("sanitize_languagecode",$urlParam);
            }else{
                return sanitize_languagecode($urlParam);
            }
        }
        elseif ($stringname =="htmleditormode" ||
        $stringname =="subaction" ||
        $stringname =="questionselectormode" ||
        $stringname =="templateeditormode"
        )
        {
            if($bUrlParamIsArray){
                return array_map("\ls\helpers\Sanitize::paranoid_string",$urlParam);
            }else{
                return \ls\helpers\Sanitize::paranoid_string($urlParam);
            }
        }
        elseif ( $stringname =="cquestions")
        {
            if($bUrlParamIsArray){
                return array_map("sanitize_cquestions",$urlParam);
            }else{
                return sanitize_cquestions($urlParam);
            }
        }
        return $urlParam;
    }
    else
    {
        return NULL;
    }
}


function sendCacheHeaders()
{
    if (!headers_sent())
    {
        header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');  // this line lets IE7 run LimeSurvey in an iframe
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // always modified
        header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }
}

/**
* @param integer $iSurveyID The ls\models\Survey ID
* @param string $sFieldCode Field code of the particular field
* @param string $sValue The stored response value
* @param string $sLanguage Initialized limesurvey_lang object for the resulting response data
* @return string
*/
function getExtendedAnswer($iSurveyID, $sFieldCode, $sValue, $sLanguage)
{
    if ($sValue==null || $sValue=='') return '';
    //Fieldcode used to determine question, $sValue used to match against answer code
    //Returns NULL if question type does not suit
    if (strpos($sFieldCode, "{$iSurveyID}X")===0) //Only check if it looks like a real fieldcode
    {
        $fieldmap = createFieldMap($iSurveyID,'short',false,false,$sLanguage);
        if (isset($fieldmap[$sFieldCode]))
            $fields = $fieldmap[$sFieldCode];
        else
            return false;

        // If it is a comment field there is nothing to convert here
        if ($fields['aid']=='comment') return $sValue;

        //Find out the question type
        $this_type = $fields['type'];
        switch($this_type)
        {
            case 'D':
                if (trim($sValue)!='')
                {
                    $qidattributes = \ls\models\QuestionAttribute::model()->getQuestionAttributes($fields['qid']);
                    $dateformatdetails = \ls\helpers\SurveyTranslator::getDateFormatDataForQID($qidattributes, $iSurveyID);
                    $sValue=convertDateTimeFormat($sValue,"Y-m-d H:i:s",$dateformatdetails['phpdate']);
                }
                break;
            case 'N':
                if (trim($sValue)!='')
                {
                    if(strpos($sValue,".")!==false)
                    {
                        $sValue=rtrim(rtrim($sValue,"0"),".");
                    }
                    $qidattributes = \ls\models\QuestionAttribute::model()->getQuestionAttributes($fields['qid']);
                    if($qidattributes['num_value_int_only'])
                    {
                        $sValue=number_format($sValue, 0, '', '');
                    }
                }
                break;
            case "L":
            case "!":
            case "O":
            case "^":
            case "I":
            case "R":
                $result = Answer::model()->getAnswerFromCode($fields['qid'],$sValue,$sLanguage);
                foreach($result as $row)
                {
                    $this_answer=$row['answer'];
                } // while
                if ($sValue == "-oth-")
                {
                    $this_answer=gT("Other",null,$sLanguage);
                }
                break;
            case "M":
            case "J":
            case "P":
            switch($sValue)
            {
                case "Y": $this_answer=gT("Yes",null,$sLanguage); break;
            }
            break;
            case "Y":
            switch($sValue)
            {
                case "Y": $this_answer=gT("Yes",null,$sLanguage); break;
                case "N": $this_answer=gT("No",null,$sLanguage); break;
                default: $this_answer=gT("No answer",null,$sLanguage);
            }
            break;
            case "G":
            switch($sValue)
            {
                case "M": $this_answer=gT("Male",null,$sLanguage); break;
                case "F": $this_answer=gT("Female",null,$sLanguage); break;
                default: $this_answer=gT("No answer",null,$sLanguage);
            }
            break;
            case "C":
            switch($sValue)
            {
                case "Y": $this_answer=gT("Yes",null,$sLanguage); break;
                case "N": $this_answer=gT("No",null,$sLanguage); break;
                case "U": $this_answer=gT("Uncertain",null,$sLanguage); break;
            }
            break;
            case "E":
            switch($sValue)
            {
                case "I": $this_answer=gT("Increase",null,$sLanguage); break;
                case "D": $this_answer=gT("Decrease",null,$sLanguage); break;
                case "S": $this_answer=gT("Same",null,$sLanguage); break;
            }
            break;
            case "F":
            case "H":
            case "1":
                $aConditions=array('qid' => $fields['qid'], 'code' => $sValue, 'language' => $sLanguage);
                if (isset($fields['scale_id']))
                {
                    $iScaleID=$fields['scale_id'];
                }
                else
                {
                    $iScaleID=0;
                }
                $result = Answer::model()->getAnswerFromCode($fields['qid'],$sValue,$sLanguage,$iScaleID);
                foreach($result as $row)
                {
                    $this_answer=$row['answer'];
                } // while
                if ($sValue == "-oth-")
                {
                    $this_answer=gT("Other",null,$sLanguage);
                }
                break;
            case "|": //File upload
                if (substr($sFieldCode, -9) != 'filecount') {
                    //Show the filename, size, title and comment -- no link!
                    $files = json_decode($sValue);
                    $sValue = '';
                    if (is_array($files)) {
                        foreach ($files as $file) {
                            $sValue .= rawurldecode($file->name) .
                            ' (' . round($file->size) . 'KB) ' .
                            strip_tags($file->title);
                            if (trim(strip_tags($file->comment))!="")
                            {
                                $sValue .=' - ' . strip_tags($file->comment);
                            }

                        }
                    }
                }
                break;
            default:
                ;
        } // switch
    }
    switch($sFieldCode)
    {
        case 'submitdate':
        case 'startdate':
        case 'datestamp':
            if (trim($sValue)!='')
            {
                $dateformatdetails = \ls\helpers\SurveyTranslator::getDateFormatDataForQID(null, $iSurveyID);
                $sValue=convertDateTimeFormat($sValue,"Y-m-d H:i:s",$dateformatdetails['phpdate'].' H:i:s');
            }
            break;
    }
    if (isset($this_answer))
    {
        return $this_answer." [$sValue]";
    }
    else
    {
        return $sValue;
    }
}

/**
* Validate an email address - also supports IDN email addresses
* @returns boolean True/false for valid/invalid
*
* @param string $email  Email address to check
*/
function validateEmailAddress($email){
    $validator = new CEmailValidator();
    return $validator->validateValue($email);
}

/**
* Validate an list of email addresses - either as array or as semicolon-limited text
* @returns List with valid email addresses - invalid email addresses are filtered - false if none of the email addresses are valid
*
* @param mixed $sEmailAddresses  Email address to check
*/
function validateEmailAddresses($aEmailAddressList){
  $aOutList=false;
  if (!is_array($aEmailAddressList))
  {
     $aEmailAddressList=explode(';',$aEmailAddressList);
  }
  foreach ($aEmailAddressList as $sEmailAddress)
  {
      $sEmailAddress= trim($sEmailAddress);
      if (validateEmailAddress($sEmailAddress)){
         $aOutList=$sEmailAddress;
      }
  }
  return $aOutList;
}

/**
 *This functions generates a a summary containing the SGQA for questions of a survey, enriched with options per question
 * It can be used for the generation of statistics. Derived from Statistics_userController
 * @param int $iSurveyID Id of the ls\models\Survey in question
 * @param array $aFilters an array which is the result of a query in Questions model
 * @param string $sLanguage
 * @return array The summary
 */
  function createCompleteSGQA($iSurveyID,$aFilters,$sLanguage) {

 foreach ($aFilters as $flt)
 {
    $myfield = "{$iSurveyID}X{$flt['gid']}X{$flt['qid']}";
    $oSurvey = Survey::model()->findByPk($iSurveyID);
    $aAdditionalLanguages = array_filter(explode(" ", $oSurvey->additional_languages));
    if (is_null($sLanguage)|| !in_array($sLanguage,$aAdditionalLanguages))
        $sLanguage = $oSurvey->language;

    switch ($flt['type'])
            {
                case "K": // Multiple Numerical
                case "Q": // Multiple Short Text
                    //get answers
                    $result = Question::model()->getQuestionsForStatistics('title as code, question as answer', "parent_qid=$flt[qid] AND language = '{$sLanguage}'", 'question_order');

                    //go through all the (multiple) answers
                    foreach($result as $row)
                    {
                        $myfield2=$flt['type'].$myfield.reset($row);
                        $allfields[] = $myfield2;
                    }
                    break;
                case "A": // ARRAY OF 5 POINT CHOICE QUESTIONS
                case "B": // ARRAY OF 10 POINT CHOICE QUESTIONS
                case "C": // ARRAY OF YES\No\gT("Uncertain") QUESTIONS
                case "E": // ARRAY OF Increase/Same/Decrease QUESTIONS
                case "F": // FlEXIBLE ARRAY
                case "H": // ARRAY (By Column)
                    //get answers
                    $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[qid] AND language = '{$sLanguage}'", 'question_order');

                    //go through all the (multiple) answers
                    foreach($result as $row)
                    {
                        $myfield2 = $myfield.reset($row);
                        $allfields[]=$myfield2;
                    }
                    break;
                // all "free text" types (T, U, S)  get the same prefix ("T")
                case "T": // Long free text
                case "U": // Huge free text
                case "S": // Short free text
                    $myfield="T$myfield";
                    $allfields[] = $myfield;
                    break;
                case ";":  //ARRAY (Multi Flex) (Text)
                case ":":  //ARRAY (Multi Flex) (Numbers)
                    $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[qid] AND language = '{$sLanguage}' AND scale_id = 0", 'question_order');

                    foreach($result as $row)
                    {
                        $fresult = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[qid] AND language = '{$sLanguage}' AND scale_id = 1", 'question_order');
                        foreach($fresult as $frow)
                        {
                            $myfield2 = $myfield . reset($row) . "_" . $frow['title'];
                        $allfields[]=$myfield2;
                    }
                    }
                    break;
                case "R": //RANKING
                    //get some answers
                    $result = Answer::model()->getQuestionsForStatistics('code, answer', "qid=$flt[qid] AND language = '{$sLanguage}'", 'sortorder, answer');
                    //get number of answers
                    //loop through all answers. if there are 3 items to rate there will be 3 statistics
                    $i=0;
                    foreach($result as $row)
                    {
                        $i++;
                        $myfield2 = "R" . $myfield . $i . "-" . strlen($i);
                        $allfields[]=$myfield2;
                    }

                    break;
                //Boilerplate questions are only used to put some text between other questions -> no analysis needed
                case "X":  //This is a boilerplate question and it has no business in this script
                    break;
                case "1": // MULTI SCALE
                    //get answers
                    $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[qid] AND language = '{$sLanguage}'", 'question_order');
                    //loop through answers
                    foreach($result as $row)
                    {
                        //----------------- LABEL 1 ---------------------
                        $myfield2 = $myfield . reset($row)."#0";
                        $allfields[]=$myfield2;
                        //----------------- LABEL 2 ---------------------
                        $myfield2 = $myfield . reset($row)."#1";
                        $allfields[]=$myfield2;
                    }   //end WHILE -> loop through all answers
                    break;

                case "P":  //P - Multiple choice with comments
                case "M":  //M - Multiple choice
                case "N":  //N - Numerical input
                case "D":  //D - Date
                    $myfield2 = $flt['type'].$myfield;
                            $allfields[]=$myfield2;
                    break;
                default:   //Default settings
                    $allfields[] = $myfield;
                    break;

        } //end switch
 }

return $allfields;

}





/**
* This function generates an array containing the fieldcode, and matching data in the same order as the activate script
*
* @param string $surveyid The ls\models\Survey ID
* @param mixed $style 'short' (default) or 'full' - full creates extra information like default values
* @param mixed $force_refresh - Forces to really refresh the array, not just take the session copy
* @param int $questionid Limit to a certain qid only (for question preview) - default is false
* @param string $sQuestionLanguage The language to use
* @return array
*/
function createFieldMap($surveyid, $style='short', $force_refresh=false, $questionid=false, $sLanguage = null) {
    throw new \Exception();
    global $aDuplicateQIDs;
    static $requestCache = [];
    $cacheKey = 'fieldmap' . md5(json_encode(func_get_args()));
    if (!isset($requestCache[$cacheKey])) {

        bP($cacheKey);
        $sLanguage = sanitize_languagecode($sLanguage);
        $surveyid = \ls\helpers\Sanitize::int($surveyid);


        App()->setLanguage($sLanguage);
        $fieldmap["id"]=array("fieldname"=>"id", 'sid'=>$surveyid, 'type'=>"id", "gid"=>"", "qid"=>"", "aid"=>"");
        if ($style == "full")
        {
            $fieldmap["id"]['title']="";
            $fieldmap["id"]['question']=gT("Response ID");
            $fieldmap["id"]['group_name']="";
        }

        $fieldmap["submitdate"]=array("fieldname"=>"submitdate", 'type'=>"submitdate", 'sid'=>$surveyid, "gid"=>"", "qid"=>"", "aid"=>"");
        if ($style == "full")
        {
            $fieldmap["submitdate"]['title']="";
            $fieldmap["submitdate"]['question']=gT("Date submitted");
            $fieldmap["submitdate"]['group_name']="";
        }

        $fieldmap["lastpage"]=array("fieldname"=>"lastpage", 'sid'=>$surveyid, 'type'=>"lastpage", "gid"=>"", "qid"=>"", "aid"=>"");
        if ($style == "full")
        {
            $fieldmap["lastpage"]['title']="";
            $fieldmap["lastpage"]['question']=gT("Last page");
            $fieldmap["lastpage"]['group_name']="";
        }

        $fieldmap["startlanguage"]=array("fieldname"=>"startlanguage", 'sid'=>$surveyid, 'type'=>"startlanguage", "gid"=>"", "qid"=>"", "aid"=>"");
        if ($style == "full")
        {
            $fieldmap["startlanguage"]['title']="";
            $fieldmap["startlanguage"]['question']=gT("Start language");
            $fieldmap["startlanguage"]['group_name']="";
        }

        //Check for any additional fields for this survey and create necessary fields (token and datestamp and ipaddr)
        $prow = Survey::model()->findByPk($surveyid)->getAttributes(); //Checked

        if ($prow['anonymized'] == "N" && Survey::model()->hasTokens($surveyid)) {
            $fieldmap["token"]=array("fieldname"=>"token", 'sid'=>$surveyid, 'type'=>"token", "gid"=>"", "qid"=>"", "aid"=>"");
            if ($style == "full")
            {
                $fieldmap["token"]['title']="";
                $fieldmap["token"]['question']=gT("Token");
                $fieldmap["token"]['group_name']="";
            }
        }
        if ($prow['datestamp'] == "Y")
        {
            $fieldmap["startdate"]=array("fieldname"=>"startdate",
            'type'=>"startdate",
            'sid'=>$surveyid,
            "gid"=>"",
            "qid"=>"",
            "aid"=>"");
            if ($style == "full")
            {
                $fieldmap["startdate"]['title']="";
                $fieldmap["startdate"]['question']=gT("Date started");
                $fieldmap["startdate"]['group_name']="";
            }

            $fieldmap["datestamp"]=array("fieldname"=>"datestamp",
            'type'=>"datestamp",
            'sid'=>$surveyid,
            "gid"=>"",
            "qid"=>"",
            "aid"=>"");
            if ($style == "full")
            {
                $fieldmap["datestamp"]['title']="";
                $fieldmap["datestamp"]['question']=gT("Date last action");
                $fieldmap["datestamp"]['group_name']="";
            }

        }
        if ($prow['ipaddr'] == "Y")
        {
            $fieldmap["ipaddr"]=array("fieldname"=>"ipaddr",
            'type'=>"ipaddress",
            'sid'=>$surveyid,
            "gid"=>"",
            "qid"=>"",
            "aid"=>"");
            if ($style == "full")
            {
                $fieldmap["ipaddr"]['title']="";
                $fieldmap["ipaddr"]['question']=gT("IP address");
                $fieldmap["ipaddr"]['group_name']="";
            }
        }
        // Add 'refurl' to fieldmap.
        if ($prow['refurl'] == "Y")
        {
            $fieldmap["refurl"]=array("fieldname"=>"refurl", 'type'=>"url", 'sid'=>$surveyid, "gid"=>"", "qid"=>"", "aid"=>"");
            if ($style == "full")
            {
                $fieldmap["refurl"]['title']="";
                $fieldmap["refurl"]['question']=gT("Referrer URL");
                $fieldmap["refurl"]['group_name']="";
            }
        }

        // Collect all default values once so don't need separate query for each question with defaults
        // First collect language specific defaults
        $defaultsQuery = "SELECT a.qid, a.sqid, a.scale_id, a.specialtype, a.defaultvalue"
        . " FROM {{defaultvalues}} as a, {{questions}} as b"
        . " WHERE a.qid = b.qid"
        . " AND a.language = '{$sLanguage}'"
        . " AND b.same_default=0"
        . " AND b.sid = ".$surveyid;
        $defaultResults = Yii::app()->db->createCommand($defaultsQuery)->queryAll();

        $defaultValues = [];   // indexed by question then subquestion
        foreach($defaultResults as $dv)
        {
            if ($dv['specialtype'] != '') {
                $sq = $dv['specialtype'];
            }
            else {
                $sq = $dv['sqid'];
            }
            $defaultValues[$dv['qid'].'~'.$sq] = $dv['defaultvalue'];
        }

        // Now overwrite language-specific defaults (if any) base language values for each question that uses same_defaults=1
        $baseLanguage = getBaseLanguageFromSurveyID($surveyid);
        $defaultsQuery = "SELECT a.qid, a.sqid, a.scale_id, a.specialtype, a.defaultvalue"
        . " FROM {{defaultvalues}} as a, {{questions}} as b"
        . " WHERE a.qid = b.qid"
        . " AND a.language = '{$baseLanguage}'"
        . " AND b.same_default=1"
        . " AND b.sid = ".$surveyid;
        $defaultResults = Yii::app()->db->createCommand($defaultsQuery)->queryAll();

        foreach($defaultResults as $dv)
        {
            if ($dv['specialtype'] != '') {
                $sq = $dv['specialtype'];
            }
            else {
                $sq = $dv['sqid'];
            }
            $defaultValues[$dv['qid'].'~'.$sq] = $dv['defaultvalue'];
        }

        $qtypes = Question::typeList();
        $session = App()->surveySessionManager->current;
        $groups = $session->getGroups();
        /** @var QuestionGroup $group */
        $questionSeq = -1;
        foreach($groups as $groupSeq => $group) {

            /** @var Question $question */
            foreach ($session->getQuestions($group) as $question) {
                ++$questionSeq;
                $fieldname = $question->getSgqa();
                // Condition indicators are obsolete with EM.  However, they are so tightly coupled into LS code that easider to just set values to 'N' for now and refactor later.
                $conditions = 'N';
                $usedinconditions = 'N';


                // Field identifier
                // GXQXSXA
                // G=Group  Q=ls\models\Question S=Subquestion A=ls\models\Answer Option
                // If S or A don't exist then set it to 0
                // Implicit (subqestion intermal to a question type ) or explicit qubquestions/answer count starts at 1

                // Types "L", "!", "O", "D", "G", "N", "X", "Y", "5", "S", "T", "U"

                if (!$question->hasSubQuestions && $question->type != "R" && $question->type != "|") {
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$question->primaryKey] = array(
                            'fieldname' => $fieldname,
                            'question' => $question->question,
                            'gid' => $question->gid
                        );
                    }

                    $fieldmap = array_merge($fieldmap, $question->getFields());



                } // For Multi flexi question types
                elseif ($question->subQuestionScales == 2 && !$question->hasAnswers) {
                    //MULTI FLEXI
                    $abrows = getSubQuestions($surveyid,$question->qid, $sLanguage);
                    //Now first process scale=1
                    $answerset = [];
                    $answerList = [];
                    foreach ($abrows as $key => $abrow) {
                        if ($abrow['scale_id'] == 1) {
                            $answerset[] = $abrow;
                            $answerList[] = array(
                                'code' => $abrow['title'],
                                'answer' => $abrow['question'],
                            );
                            unset($abrows[$key]);
                        }
                    }
                    reset($abrows);
                    foreach ($abrows as $abrow) {
                        foreach ($answerset as $answer) {
                            $fieldname = "{$question->sid}X{$question['gid']}X{$question['qid']}{$abrow['title']}_{$answer['title']}";
                            if (isset($fieldmap[$fieldname])) {
                                $aDuplicateQIDs[$question['qid']] = array(
                                    'fieldname' => $fieldname,
                                    'question' => $question['question'],
                                    'gid' => $question['gid']
                                );
                            }
                            $fieldmap[$fieldname] = array(
                                "fieldname" => $fieldname,
                                'type' => $question->type,
                                'sid' => $surveyid,
                                "gid" => $question['gid'],
                                "qid" =>$question->qid,
                                "aid" => $abrow['title'] . "_" . $answer['title'],
                                "sqid" => $abrow['qid']
                            );
                            if ($abrow['other'] == "Y") {
                                $alsoother = "Y";
                            }
                            if ($style == "full") {
                                $fieldmap[$fieldname]['title'] = $question['title'];
                                $fieldmap[$fieldname]['question'] = $question['question'];
                                $fieldmap[$fieldname]['subquestion1'] = $abrow['question'];
                                $fieldmap[$fieldname]['subquestion2'] = $answer['question'];
                                $fieldmap[$fieldname]['group_name'] = $question->group->group_name;
                                $fieldmap[$fieldname]['mandatory'] = $question->bool_mandatory;
                                $fieldmap[$fieldname]['hasconditions'] = $conditions;
                                $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                                $fieldmap[$fieldname]['preg'] = $question['preg'];
                                $fieldmap[$fieldname]['answerList'] = $answerList;
                                $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                            }
                        }
                    }
                    unset($answerset);
                } elseif ($question->type == "1") {
                    $abrows = getSubQuestions($surveyid,$question->qid, $sLanguage);
                    foreach ($abrows as $abrow) {
                        $fieldname = "{$question->sgqa}{$abrow['title']}#0";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$question['qid']] = array(
                                'fieldname' => $fieldname,
                                'question' => $question['question'],
                                'gid' => $question['gid']
                            );
                        }
                        $fieldmap[$fieldname] = array(
                            "fieldname" => $fieldname,
                            'type' => $question->type,
                            'sid' => $surveyid,
                            "gid" => $question['gid'],
                            "qid" =>$question->qid,
                            "aid" => $abrow['title'],
                            "scale_id" => 0
                        );
                        if ($style == "full") {
                            $fieldmap[$fieldname]['title'] = $question['title'];
                            $fieldmap[$fieldname]['question'] = $question['question'];
                            $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                            $fieldmap[$fieldname]['group_name'] = $question->group->group_name;
                            $fieldmap[$fieldname]['scale'] = gT('Scale 1');
                            $fieldmap[$fieldname]['mandatory'] = $question['mandatory'];
                            $fieldmap[$fieldname]['hasconditions'] = $conditions;
                            $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                            $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                        }

                        $fieldname = "{$question['sid']}X{$question['gid']}X{$question['qid']}{$abrow['title']}#1";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$question['qid']] = array(
                                'fieldname' => $fieldname,
                                'question' => $question['question'],
                                'gid' => $question['gid']
                            );
                        }
                        $fieldmap[$fieldname] = array(
                            "fieldname" => $fieldname,
                            'type' => $question->type,
                            'sid' => $surveyid,
                            "gid" => $question->gid,
                            "qid" =>$question->qid,
                            "aid" => $abrow['title'],
                            "scale_id" => 1
                        );
                        if ($style == "full") {
                            $fieldmap[$fieldname]['title'] = $question->title;
                            $fieldmap[$fieldname]['question'] = $question->question;
                            $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                            $fieldmap[$fieldname]['group_name'] = $question->group->group_name;
                            $fieldmap[$fieldname]['scale'] = gT('Scale 2');
                            $fieldmap[$fieldname]['mandatory'] = $question->mandatory;
                            $fieldmap[$fieldname]['hasconditions'] = $conditions;
                            $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                            // TODO SQrelevance for different scales? $fieldmap[$fieldname]['SQrelevance']=$abrow['relevance'];
                        }
                    }
                } elseif ($question->type == "R") {
                    //MULTI ENTRY
                    $i = 1;
                    foreach($question->answers as $answer) {
                        $fieldname = "{$question->sgqa}$i";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$question->qid] = array(
                                'fieldname' => $fieldname,
                                'question' => $question->question,
                                'gid' => $question->qid
                            );
                        }
                        $fieldmap[$fieldname] = array(
                            "fieldname" => $fieldname,
                            'type' => $question->type,
                            'sid' => $surveyid,
                            "gid" => $question->gid,
                            "qid" => $question->qid,
                            "aid" => $i
                        );
                        if ($style == "full") {
                            $fieldmap[$fieldname]['title'] = $question['title'];
                            $fieldmap[$fieldname]['question'] = $question['question'];
                            $fieldmap[$fieldname]['subquestion'] = sprintf(gT('Rank %s'), $i);
                            $fieldmap[$fieldname]['group_name'] = $question->group->group_name;
                            $fieldmap[$fieldname]['mandatory'] = $question['mandatory'];
                            $fieldmap[$fieldname]['hasconditions'] = $conditions;
                            $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        }

                        $i++;
                    }
                } elseif ($question->type == "|") {
                    $qidattributes = $question->questionAttributes;
                    $fieldname = "{$question['sid']}X{$question['gid']}X{$question['qid']}";
                    $fieldmap[$fieldname] = array(
                        "fieldname" => $fieldname,
                        'type' => $question->type,
                        'sid' => $surveyid,
                        "gid" => $question->gid,
                        "qid" =>$question->qid,
                        "aid" => ''
                    );
                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $question['title'];
                        $fieldmap[$fieldname]['question'] = $question['question'];
                        $fieldmap[$fieldname]['max_files'] = $qidattributes['max_num_of_files'];
                        $fieldmap[$fieldname]['group_name'] = $question->group->group_name;
                        $fieldmap[$fieldname]['mandatory'] = $question->mandatory;
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    }
                    $fieldname = "{$question['sid']}X{$question->gid}X{$question->qid}" . "_filecount";
                    $fieldmap[$fieldname] = array(
                        "fieldname" => $fieldname,
                        'type' => $question->type,
                        'sid' => $surveyid,
                        "gid" => $question->gid,
                        "qid" =>$question->qid,
                        "aid" => "filecount"
                    );
                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $question->title;
                        $fieldmap[$fieldname]['question'] = "filecount - " . $question->question;
                        $fieldmap[$fieldname]['group_name'] = $question->group->group_name;
                        $fieldmap[$fieldname]['mandatory'] = $question->mandatory;
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    }
                } else  // ls\models\Question types with subquestions and one answer per subquestion  (M/A/B/C/E/F/H/P)
                {
                    //MULTI ENTRY
                    $abrows = getSubQuestions($surveyid,$question->qid, $sLanguage);
                    foreach ($abrows as $abrow) {
                        $fieldname = "{$question->sgqa}{$abrow['title']}";

                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$question->qid] = array(
                                'fieldname' => $fieldname,
                                'question' => $question->question,
                                'gid' => $question->gid
                            );
                        }
                        $fieldmap[$fieldname] = array(
                            "fieldname" => $fieldname,
                            'type' => $question->type,
                            'sid' => $question->sid,
                            'gid' => $question->gid,
                            'qid' => $question->qid,
                            'aid' => $abrow['title'],
                            'sqid' => $abrow['qid']
                        );
                        if ($style == "full") {
                            $fieldmap[$fieldname]['title'] = $question->title;
                            $fieldmap[$fieldname]['question'] = $question->question;
                            $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                            $fieldmap[$fieldname]['group_name'] = $question->group->title;
                            $fieldmap[$fieldname]['mandatory'] = $question->mandatory;
                            $fieldmap[$fieldname]['hasconditions'] = $conditions;
                            $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                            $fieldmap[$fieldname]['preg'] = $question->preg;
                            // get SQrelevance from DB
                            $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                            if (isset($defaultValues[$question->qid . '~' . $abrow['qid']])) {
                                $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$question->qid . '~' . $abrow['qid']];
                            }
                        }
                        if ($question->type == "P") {
                            $fieldname = "{$question['sid']}X{$question['gid']}X{$question['qid']}{$abrow['title']}comment";
                            if (isset($fieldmap[$fieldname])) {
                                $aDuplicateQIDs[$question['qid']] = array(
                                    'fieldname' => $fieldname,
                                    'question' => $question['question'],
                                    'gid' => $question['gid']
                                );
                            }
                            $fieldmap[$fieldname] = array(
                                "fieldname" => $fieldname,
                                'type' => $question->type,
                                'sid' => $surveyid,
                                "gid" => $question['gid'],
                                "qid" =>$question->qid,
                                "aid" => $abrow['title'] . "comment"
                            );
                            if ($style == "full") {
                                $fieldmap[$fieldname]['title'] = $question->title;
                                $fieldmap[$fieldname]['question'] = $question->question;
                                $fieldmap[$fieldname]['subquestion'] = gT('Comment');
                                $fieldmap[$fieldname]['group_name'] = $question->group->group_name;
                                $fieldmap[$fieldname]['mandatory'] = $question->mandatory;
                                $fieldmap[$fieldname]['hasconditions'] = $conditions;
                                $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                            }
                        }
                    }
                    if ($question->bool_other && ($question->type == "M" || $question->type == "P")) {
                        $fieldname = "{$question['sid']}X{$question['gid']}X{$question['qid']}other";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$question->qid] = array(
                                'fieldname' => $fieldname,
                                'question' => $question['question'],
                                'gid' => $question->gid
                            );
                        }
                        $fieldmap[$fieldname] = array(
                            "fieldname" => $fieldname,
                            'type' => $question->type,
                            'sid' => $surveyid,
                            "gid" => $question['gid'],
                            "qid" =>$question->qid,
                            "aid" => "other"
                        );
                        if ($style == "full") {
                            $fieldmap[$fieldname]['title'] = $question->title;
                            $fieldmap[$fieldname]['question'] = $question->question;
                            $fieldmap[$fieldname]['subquestion'] = gT('Other');
                            $fieldmap[$fieldname]['group_name'] = $question->group->group_name;
                            $fieldmap[$fieldname]['mandatory'] = $question->mandatory;
                            $fieldmap[$fieldname]['hasconditions'] = $conditions;
                            $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                            $fieldmap[$fieldname]['other'] = $question->bool_other;
                        }
                        if ($question->type == "P") {
                            $fieldname = "{$question['sid']}X{$question->gid}X{$question->qid}othercomment";
                            if (isset($fieldmap[$fieldname])) {
                                $aDuplicateQIDs[$question->qid] = array(
                                    'fieldname' => $fieldname,
                                    'question' => $question->question,
                                    'gid' => $question->gid
                                );
                            }
                            $fieldmap[$fieldname] = array(
                                "fieldname" => $fieldname,
                                'type' => $question->type,
                                'sid' => $surveyid,
                                "gid" => $question->gid,
                                "qid" =>$question->qid,
                                "aid" => "othercomment"
                            );
                            if ($style == "full") {
                                $fieldmap[$fieldname]['title'] = $question->title;
                                $fieldmap[$fieldname]['question'] = $question->question;
                                $fieldmap[$fieldname]['subquestion'] = gT('Other comment');
                                $fieldmap[$fieldname]['group_name'] = $question->group->group_name;
                                $fieldmap[$fieldname]['mandatory'] = $question->mandatory;
                                $fieldmap[$fieldname]['hasconditions'] = $conditions;
                                $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                                $fieldmap[$fieldname]['other'] = $question->other;
                            }
                        }
                    }
                }
            }
        }
        if (isset($fieldmap[$fieldname]))
        {
            //set question relevance (uses last SQ's relevance field for question relevance)
            $fieldmap[$fieldname]['relevance'] = $question->relevance;
            $fieldmap[$fieldname]['grelevance'] = $group->grelevance;
            $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
            $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
            $fieldmap[$fieldname]['preg'] = $question->preg;
            $fieldmap[$fieldname]['other'] = $question->other;
            $fieldmap[$fieldname]['help']=$question->help;
        }
        else
        {
            --$questionSeq; // didn't generate a valid $fieldmap entry, so decrement the question counter to ensure they are sequential
        }

//        if (isset($fieldmap) && $questionid == false) {
//            // If the fieldmap was randomized, the master will contain the proper order.  Copy that fieldmap with the new language settings.
//            if (isset(Yii::app()->session['survey_' . $surveyid]['fieldmap-' . $surveyid . '-randMaster'])) {
//                $masterFieldmap = Yii::app()->session['survey_' . $surveyid]['fieldmap-' . $surveyid . '-randMaster'];
//                $mfieldmap = Yii::app()->session['survey_' . $surveyid][$masterFieldmap];
//
//                foreach ($mfieldmap as $fieldname => $mf) {
//                    if (isset($fieldmap[$fieldname])) {
//                        // This array holds the keys of translatable attributes
//                        $translatable = array_flip(array(
//                            'question',
//                            'subquestion',
//                            'subquestion1',
//                            'subquestion2',
//                            'group_name',
//                            'answerList',
//                            'defaultValue',
//                            'help'
//                        ));
//                        // We take all translatable attributes from the new fieldmap
//                        $newText = array_intersect_key($fieldmap[$fieldname], $translatable);
//                        // And merge them with the other values from the random fieldmap like questionSeq, groupSeq etc.
//                        $mf = $newText + $mf;
//                    }
//                    $mfieldmap[$fieldname] = $mf;
//                }
//                $fieldmap = $mfieldmap;
//            }
//            Yii::app()->session['fieldmap-' . $surveyid . $sLanguage] = $fieldmap;


//        }
        eP($cacheKey);
        $requestCache[$cacheKey] = $fieldmap;
    }
    return $requestCache[$cacheKey];
}

/**
* Returns true if the given survey has a File Upload ls\models\Question Type
* @param $surveyid The survey ID
* @return bool
*/
function hasFileUploadQuestion($iSurveyID) {
    $iCount = Question::model()->count( "sid=:surveyid AND parent_qid=0 AND type='|'", array(':surveyid' => $iSurveyID));
    return $iCount>0 ;
}

/**
* This function generates an array containing the fieldcode, and matching data in the same order as the activate script
*
* @param string $surveyid The ls\models\Survey ID
* @param mixed $style 'short' (default) or 'full' - full creates extra information like default values
* @param mixed $force_refresh - Forces to really refresh the array, not just take the session copy
* @param int $questionid Limit to a certain qid only (for question preview) - default is false
* @param string $sQuestionLanguage The language to use
* @return array
*/
function createTimingsFieldMap($surveyid, $style='full', $force_refresh=false, $questionid=false, $sQuestionLanguage=null) {

    global $aDuplicateQIDs;
    static $timingsFieldMap;

    $sLanguage = sanitize_languagecode($sQuestionLanguage);
    $surveyid = \ls\helpers\Sanitize::int($surveyid);
    $sOldLanguage=App()->language;
    App()->setLanguage($sLanguage);

    //checks to see if fieldmap has already been built for this page.
    if (isset($timingsFieldMap[$surveyid][$style][$sLanguage]) && $force_refresh==false) {
        return $timingsFieldMap[$surveyid][$style][$sLanguage];
    }

    //do something
    $fields = createFieldMap($surveyid, $style, $force_refresh, $questionid, $sQuestionLanguage);
    $fieldmap['interviewtime']=array('fieldname'=>'interviewtime','type'=>'interview_time','sid'=>$surveyid, 'gid'=>'', 'qid'=>'', 'aid'=>'', 'question'=>gT('Total time'), 'title'=>'interviewtime');
    foreach ($fields as $field) {
        if (!empty($field['gid'])) {
            // field for time spent on page
            $fieldname="{$field['sid']}X{$field['gid']}time";
            if (!isset($fieldmap[$fieldname]))
            {
                $fieldmap[$fieldname]=array("fieldname"=>$fieldname, 'type'=>"page_time", 'sid'=>$surveyid, "gid"=>$field['gid'], "group_name"=>$field['group_name'], "qid"=>'', 'aid'=>'', 'title'=>'groupTime'.$field['gid'], 'question'=>gT('Group time').": ".$field['group_name']);
            }

            // field for time spent on answering a question
            $fieldname="{$field['sid']}X{$field['gid']}X{$field['qid']}time";
            if (!isset($fieldmap[$fieldname]))
            {
                $fieldmap[$fieldname]=array("fieldname"=>$fieldname, 'type'=>"answer_time", 'sid'=>$surveyid, "gid"=>$field['gid'], "group_name"=>$field['group_name'], "qid"=>$field['qid'], 'aid'=>'', "title"=>$field['title'].'Time', "question"=>gT('ls\models\Question time').": ".$field['title']);
            }
        }
    }

    $timingsFieldMap[$surveyid][$style][$sLanguage] = $fieldmap;
    App()->setLanguage($sOldLanguage);
    return $timingsFieldMap[$surveyid][$style][$sLanguage];
}

/**
* put your comment there...
*
* @param mixed $needle
* @param mixed $haystack
* @param mixed $keyname
* @param mixed $maxanswers
*/
function arraySearchByKey($needle, $haystack, $keyname, $maxanswers="") {
    $output=[];
    foreach($haystack as $hay) {
        if (array_key_exists($keyname, $hay)) {
            if ($hay[$keyname] == $needle) {
                if ($maxanswers == 1) {
                    return $hay;
                } else {
                    $output[]=$hay;
                }
            }
        }
    }
    return $output;
}


function buildLabelSetCheckSumArray()
{
    // BUILD CHECKSUMS FOR ALL EXISTING LABEL SETS

    /**$query = "SELECT lid
    FROM ".db_table_name('labelsets')."
    ORDER BY lid"; */
    $result = LabelSet::model()->getLID();//($query) or throw new \CHttpException(500, "safe_died collecting labelset ids<br />$query<br />");  //Checked)
    $csarray=[];
    foreach($result as $row)
    {
        $thisset="";
        $query2 = "SELECT code, title, sortorder, language, assessment_value
        FROM {{labels}}
        WHERE lid={$row['lid']}
        ORDER BY language, sortorder, code";
        $result2 = Yii::app()->db->createCommand($query2)->query();
        foreach ($result2->readAll() as $row2)
        {
            $thisset .= implode('.', $row2);
        } // while
        $csarray[$row['lid']]=dechex(crc32($thisset)*1);
    }

    return $csarray;
}


/**
*
* Returns the questionAttribtue value set or '' if not set
* @author: lemeur
* @param $questionAttributeArray
* @param $attributeName
* @param $language string Optional: The language if the particualr attributes is localizable
* @return string
*/
function getQuestionAttributeValue($questionAttributeArray, $attributeName, $language='')
{
    if ($language=='' && isset($questionAttributeArray[$attributeName]))
    {
        return $questionAttributeArray[$attributeName];
    }
    elseif ($language!='' && isset($questionAttributeArray[$attributeName][$language]))
    {
        return $questionAttributeArray[$attributeName][$language];
    }
    else
    {
        return '';
    }
}

/**
* Returns array of question type chars with attributes
*
* @param mixed $returnByName If set to true the array will be by attribute name
*/
function questionAttributes($returnByName=false)
{
    // Use some static
    static $qattributes=false;
    static $qat=false;


    if (!$qattributes)
    {
        //For each question attribute include a key:
        // name - the display name
        // types - a string with one character representing each question typy to which the attribute applies
        // help - a short explanation

        // If you insert a new attribute please do it in correct alphabetical order!
        // Please also list the new attribute in the function &TSVSurveyExport($sid) in em_manager_helper.php,
        // so your new attribute will not be "forgotten" when the survey is exported to Excel/CSV-format!
        $qattributes["alphasort"]=array(
        "types"=>"!LOWZ",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT("Sort the answer options alphabetically"),
        "caption"=>gT('Sort answers alphabetically'));

        $qattributes["answer_width"]=array(
        "types"=>"ABCEF1:;",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'integer',
        'min'=>'1',
        'max'=>'100',
        "help"=>gT('Set the percentage width of the (sub-)question column (1-100)'),
        "caption"=>gT('(Sub-)question width'));

        $qattributes["repeat_headings"]=array(
        "types"=>"F:1;",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'integer',
         'default'=>'',
        "help"=>gT('Repeat headings every X subquestions (Set to 0 to deactivate heading repeat, deactivate minimum repeat headings from config).'),
        "caption"=>gT('Repeat headers'));

        $qattributes["array_filter"]=array(
        "types"=>"1ABCEF:;MPLKQR",
        'category'=>gT('Logic'),
        'sortorder'=>100,
        'inputtype'=>'text',
        "help"=>gT("Enter the code(s) of Multiple choice question(s) (separated by semicolons) to only show the matching answer options in this question."),
        "caption"=>gT('Array filter'));

        $qattributes["array_filter_exclude"]=array(
        "types"=>"1ABCEF:;MPLKQR",
        'category'=>gT('Logic'),
        'sortorder'=>100,
        'inputtype'=>'text',
        "help"=>gT("Enter the code(s) of Multiple choice question(s) (separated by semicolons) to exclude the matching answer options in this question."),
        "caption"=>gT('Array filter exclusion'));

        $qattributes["array_filter_style"]=array(
        "types"=>"1ABCEF:;MPLKQR",
        'category'=>gT('Logic'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('Hidden'),
        1=>gT('Disabled')),
        'default'=>0,
        "help"=>gT("Specify how array-filtered sub-questions should be displayed"),
        "caption"=>gT('Array filter style'));

        $qattributes["assessment_value"]=array(
        "types"=>"MP",
        'category'=>gT('Logic'),
        'sortorder'=>100,
        'default'=>'1',
        'inputtype'=>'integer',
        "help"=>gT("If one of the subquestions is marked then for each marked subquestion this value is added as assessment."),
        "caption"=>gT('ls\models\Assessment value'));

        $qattributes["category_separator"]=array(
        "types"=>"!",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'text',
        "help"=>gT('Category separator'),
        "caption"=>gT('Category separator'));

        $qattributes["code_filter"]=array(
        "types"=>"WZ",
        'category'=>gT('Logic'),
        'sortorder'=>100,
        'inputtype'=>'text',
        "help"=>gT('Filter the available answers by this value'),
        "caption"=>gT('Code filter'));

        $qattributes["commented_checkbox"]=array(
        "types"=>"P",
        'category'=>gT('Logic'),
        'sortorder'=>110,
        'inputtype'=>'singleselect',
        'options'=>array(
            "allways"=>gT('No control on checkbox'),
            "checked"=>gT('Checkbox is checked'),
            "unchecked"=>gT('Checkbox is unchecked'),
            ),
        'default' => "checked",
        'help'=>gT('Choose when user can add a comment'),
        'caption'=>gT('Comment only when'));

        $qattributes["commented_checkbox_auto"]=array(
        "types"=>"P",
        'category'=>gT('Logic'),
        'sortorder'=>111,
        'inputtype'=>'singleselect',
        'options'=>array(
            "0"=>gT('No'),
            "1"=>gT('Yes'),
            ),
        'default' => "1",
        'help'=>gT('Use javascript function to remove text and uncheck checkbox (or use Expression Manager only).'),
        'caption'=>gT('Remove text or uncheck checkbox automatically'));

        $qattributes["display_columns"]=array(
        "types"=>"LM",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'integer',
        'default'=>'1',
        'min'=>'1',
        'max'=>'100',
        "help"=>gT('The answer options will be distributed across the number of columns set here'),
        "caption"=>gT('Display columns'));

        $qattributes["display_rows"]=array(
        "types"=>"QSTU",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'text',
        "help"=>gT('How many rows to display'),
        "caption"=>gT('Display rows'));

        $qattributes["dropdown_dates"]=array(
        "types"=>"D",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT('Use accessible dropdown boxes instead of calendar popup'),
        "caption"=>gT('Display dropdown boxes'));

        $qattributes["date_min"]=array(
        "types"=>"D",
        'category'=>gT('Display'),
        'sortorder'=>110,
        'inputtype'=>'text',
        "help"=>gT('Minimum date selectable in calendar (YYYY-MM-DD). Only the year is used if dropdown boxes are selected.'),
        "caption"=>gT('Minimum date'));

        $qattributes["date_max"]=array(
        "types"=>"D",
        'category'=>gT('Display'),
        'sortorder'=>111,
        'inputtype'=>'text',
        "help"=>gT('Maximum date selectable in calendar (YYYY-MM-DD). Only the year is used if dropdown boxes are selected.'),
        "caption"=>gT('Maximum date'));

        $qattributes["dropdown_prepostfix"]=array(
        "types"=>"1",
        'category'=>gT('Display'),
        'sortorder'=>112,
        'inputtype'=>'text',
        'i18n'=>true,
        "help"=>gT('Prefix|Suffix for dropdown lists'),
        "caption"=>gT('Dropdown prefix/suffix'));

        $qattributes["dropdown_separators"]=array(
        "types"=>"1",
        'category'=>gT('Display'),
        'sortorder'=>120,
        'inputtype'=>'text',
        "help"=>gT('Text shown on each subquestion row between both scales in dropdown mode'),
        "caption"=>gT('Dropdown separator'));

        $qattributes["dualscale_headerA"]=array(
        "types"=>"1",
        'category'=>gT('Display'),
        'sortorder'=>110,
        'inputtype'=>'text',
        'i18n'=>true,
        "help"=>gT('Enter a header text for the first scale'),
        "caption"=>gT('Header for first scale'));

        $qattributes["dualscale_headerB"]=array(
        "types"=>"1",
        'category'=>gT('Display'),
        'sortorder'=>111,
        'inputtype'=>'text',
        'i18n'=>true,
        "help"=>gT('Enter a header text for the second scale'),
        "caption"=>gT('Header for second scale'));

        $qattributes["equation"]=array(
        "types"=>"*",
        'category'=>gT('Logic'),
        'sortorder'=>100,
        'inputtype'=>'textarea',
        "help"=>gT('Final equation to set in database, defaults to question text.'),
        "caption"=>gT('Equation'),
        "default"=>"");

        $qattributes["equals_num_value"]=array(
        "types"=>"K",
        'category'=>gT('Input'),
        'sortorder'=>100,
        'inputtype'=>'text',
        "help"=>gT('Multiple numeric inputs sum must equal this value'),
        "caption"=>gT('Equals sum value'));

        $qattributes["em_validation_q"]=array(
        "types"=>":;ABCDEFKMNOPQRSTU",
        'category'=>gT('Logic'),
        'sortorder'=>200,
        'inputtype'=>'textarea',
        "help"=>gT('Enter a boolean equation to validate the whole question.'),
        "caption"=>gT('ls\models\Question validation equation'));

        $qattributes["em_validation_q_tip"]=array(
        "types"=>":;ABCDEFKMNOPQRSTU",
        'category'=>gT('Logic'),
        'sortorder'=>210,
        'inputtype'=>'textarea',
        "help"=>gT('This is a hint text that will be shown to the participant describing the question validation equation.'),
        "caption"=>gT('ls\models\Question validation tip'));

        $qattributes["em_validation_sq"]=array(
        "types"=>";:KQSTUN",
        'category'=>gT('Logic'),
        'sortorder'=>220,
        'inputtype'=>'textarea',
        "help"=>gT('Enter a boolean equation to validate each sub-question.'),
        "caption"=>gT('Sub-question validation equation'));

        $qattributes["em_validation_sq_tip"]=array(
        "types"=>";:KQSTUN",
        'category'=>gT('Logic'),
        'sortorder'=>230,
        'inputtype'=>'textarea',
        "help"=>gT('This is a tip shown to the participant describing the sub-question validation equation.'),
        "caption"=>gT('Sub-question validation tip'));

        $qattributes["exclude_all_others"]=array(
        "types"=>":ABCEFMPKQ",
        'category'=>gT('Logic'),
        'sortorder'=>130,
        'inputtype'=>'text',
        "help"=>gT('Excludes all other options if a certain answer is selected - just enter the answer code(s) separated with a semikolon.'),
        "caption"=>gT('Exclusive option'));

        $qattributes["exclude_all_others_auto"]=array(
        "types"=>"MP",
        'category'=>gT('Logic'),
        'sortorder'=>131,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT('If the participant marks all options, uncheck all and check the option set in the "Exclusive option" setting'),
        "caption"=>gT('Auto-check exclusive option if all others are checked'));

        // Map Options

        $qattributes["location_city"]=array(
        "types"=>"S",
        'readonly_when_active'=>true,
        'category'=>gT('Location'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'default'=>0,
        'options'=>array(0=>gT('Yes'),
        1=>gT('No')),
        "help"=>gT("Store the city?"),
        "caption"=>gT("Save city"));

        $qattributes["location_state"]=array(
        "types"=>"S",
        'readonly_when_active'=>true,
        'category'=>gT('Location'),
        'sortorder'=>100,
        'default'=>0,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('Yes'),
        1=>gT('No')),
        "help"=>gT("Store the state?"),
        "caption"=>gT("Save state"));

        $qattributes["location_postal"]=array(
        "types"=>"S",
        'readonly_when_active'=>true,
        'category'=>gT('Location'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'default'=>0,
        'options'=>array(0=>gT('Yes'),
        1=>gT('No')),
        "help"=>gT("Store the postal code?"),
        "caption"=>gT("Save postal code"));

        $qattributes["location_country"]=array(
        "types"=>"S",
        'readonly_when_active'=>true,
        'category'=>gT('Location'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'default'=>0,
        'options'=>array(0=>gT('Yes'),
        1=>gT('No')),
        "help"=>gT("Store the country?"),
        "caption"=>gT("Save country"));

        $qattributes["statistics_showmap"]=array(
        "types"=>"S",
        'category'=>gT('Statistics'),
        'inputtype'=>'singleselect',
        'sortorder'=>100,
        'options'=>array(1=>gT('Yes'), 0=>gT('No')),
        'help'=>gT("Show a map in the statistics?"),
        'caption'=>gT("Display map"),
        'default'=>1
        );

        $qattributes["statistics_showgraph"]=array(
        'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*',
        'category'=>gT('Statistics'),
        'inputtype'=>'singleselect',
        'sortorder'=>101,
        'options'=>array(1=>gT('Yes'), 0=>gT('No')),
        'help'=>gT("Display a chart in the statistics?"),
        'caption'=>gT("Display chart"),
        'default'=>1
        );

        $qattributes["statistics_graphtype"]=array(
        "types"=>'15ABCDEFGHIKLNOQRSTUWXYZ!:;|*',
        'category'=>gT('Statistics'),
        'inputtype'=>'singleselect',
        'sortorder'=>102,
        'options'=>array(0=>gT('Bar chart'), 1=>gT('Pie chart')),
        'help'=>gT("Select the type of chart to be displayed"),
        'caption'=>gT("Chart type"),
        'default'=>0
        );

        $qattributes["location_mapservice"]=array(
        "types"=>"S",
        'category'=>gT('Location'),
        'sortorder'=>90,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('Off'),
            100=>gT('Open Layer (OpenStreetMap via mapquest)'),
            1=>gT('Google Maps')
        ),
        'default' => 0,
        "help"=>gT("Activate this to show a map above the input field where the user can select a location"),
        "caption"=>gT("Use mapping service"));

        $qattributes["location_mapwidth"]=array(
        "types"=>"S",
        'category'=>gT('Location'),
        'sortorder'=>102,
        'inputtype'=>'text',
        'default'=>'500',
        "help"=>gT("Width of the map in pixel"),
        "caption"=>gT("Map width"));

        $qattributes["location_mapheight"]=array(
        "types"=>"S",
        'category'=>gT('Location'),
        'sortorder'=>103,
        'inputtype'=>'text',
        'default'=>'300',
        "help"=>gT("Height of the map in pixel"),
        "caption"=>gT("Map height"));

        $qattributes["location_nodefaultfromip"]=array(
        "types"=>"S",
        'category'=>gT('Location'),
        'sortorder'=>91,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('Yes'),
        1=>gT('No')),
        'default' => 0,
        "help"=>gT("Get the default location using the user's IP address?"),
        "caption"=>gT("IP as default location"));

        $qattributes["location_defaultcoordinates"]=array(
        "types"=>"S",
        'category'=>gT('Location'),
        'sortorder'=>101,
        'inputtype'=>'text',
        "help"=>gT('Default coordinates of the map when the page first loads. Format: latitude [space] longtitude'),
        "caption"=>gT('Default position'));

        $qattributes["location_mapzoom"]=array(
        "types"=>"S",
        'category'=>gT('Location'),
        'sortorder'=>101,
        'inputtype'=>'text',
        'default'=>'11',
        "help"=>gT("Map zoom level"),
        "caption"=>gT("Zoom level"));

        // End Map Options

        $qattributes["hide_tip"]=array(
        "types"=>"15ABCDEFGHIKLMNOPQRSTUXY!:;|",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT('Hide the tip that is normally shown with a question'),
        "caption"=>gT('Hide tip'));

        $qattributes['hidden']=array(
        'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*',
        'category'=>gT('Display'),
        'sortorder'=>101,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        'help'=>gT('Hide this question at any time. This is useful for including data using answer prefilling.'),
        'caption'=>gT('Always hide this question'));

        $qattributes["max_answers"]=array(
        "types"=>"MPR1:;ABCEFKQ",
        'category'=>gT('Logic'),
        'sortorder'=>11,
        'inputtype'=>'integer',
        "help"=>gT('Limit the number of possible answers'),
        "caption"=>gT('Maximum answers'));

        $qattributes["max_num_value"]=array(
        "types"=>"K",
        'category'=>gT('Input'),
        'sortorder'=>100,
        'inputtype'=>'text',
        "help"=>gT('Maximum sum value of multiple numeric input'),
        "caption"=>gT('Maximum sum value'));

        $qattributes["max_num_value_n"]=array(
        "types"=>"NK",
        'category'=>gT('Input'),
        'sortorder'=>110,
        'inputtype'=>'integer',
        "help"=>gT('Maximum value of the numeric input'),
        "caption"=>gT('Maximum value'));

        //    $qattributes["max_num_value_sgqa"]=array(
        //    "types"=>"K",
        //    'category'=>gT('Logic'),
        //    'sortorder'=>100,
        //    'inputtype'=>'text',
        //    "help"=>gT('Enter the SGQA identifier to use the total of a previous question as the maximum for this question'),
        //    "caption"=>gT('Max value from SGQA'));

        $qattributes["maximum_chars"]=array(
        "types"=>"STUNQK:;",
        'category'=>gT('Input'),
        'sortorder'=>100,
        'inputtype'=>'text',
        "help"=>gT('Maximum characters allowed'),
        "caption"=>gT('Maximum characters'));

        $qattributes["min_answers"]=array(
        "types"=>"MPR1:;ABCEFKQ",
        'category'=>gT('Logic'),
        'sortorder'=>10,
        'inputtype'=>'integer',
        "help"=>gT('Ensure a minimum number of possible answers (0=No limit)'),
        "caption"=>gT('Minimum answers'));

        $qattributes["min_num_value"]=array(
        "types"=>"K",
        'category'=>gT('Input'),
        'sortorder'=>100,
        'inputtype'=>'text',
        "help"=>gT('The sum of the multiple numeric inputs must be greater than this value'),
        "caption"=>gT('Minimum sum value'));

        $qattributes["min_num_value_n"]=array(
        "types"=>"NK",
        'category'=>gT('Input'),
        'sortorder'=>100,
        'inputtype'=>'integer',
        "help"=>gT('Minimum value of the numeric input'),
        "caption"=>gT('Minimum value'));

        //    $qattributes["min_num_value_sgqa"]=array(
        //    "types"=>"K",
        //    'category'=>gT('Logic'),
        //    'sortorder'=>100,
        //    'inputtype'=>'text',
        //    "help"=>gT('Enter the SGQA identifier to use the total of a previous question as the minimum for this question'),
        //    "caption"=>gT('Minimum value from SGQA'));

        $qattributes["multiflexible_max"]=array(
        "types"=>":",
        'category'=>gT('Display'),
        'sortorder'=>112,
        'inputtype'=>'text',
        "help"=>gT('Maximum value for array(mult-flexible) question type'),
        "caption"=>gT('Maximum value'));

        $qattributes["multiflexible_min"]=array(
        "types"=>":",
        'category'=>gT('Display'),
        'sortorder'=>110,
        'inputtype'=>'text',
        "help"=>gT('Minimum value for array(multi-flexible) question type'),
        "caption"=>gT('Minimum value'));

        $qattributes["multiflexible_step"]=array(
        "types"=>":",
        'category'=>gT('Display'),
        'sortorder'=>111,
        'inputtype'=>'text',
        "help"=>gT('Step value'),
        "caption"=>gT('Step value'));

        $qattributes["multiflexible_checkbox"]=array(
        "types"=>":",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT('Use checkbox layout'),
        "caption"=>gT('Checkbox layout'));

        $qattributes["reverse"]=array(
        "types"=>"D:",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT('Present answer options in reverse order'),
        "caption"=>gT('Reverse answer order'));

        //    $qattributes["num_value_equals_sgqa"]=array(
        //    "types"=>"K",
        //    'category'=>gT('Logic'),
        //    'sortorder'=>100,
        //    'inputtype'=>'text',
        //    "help"=>gT('SGQA identifier to use total of previous question as total for this question'),
        //    "caption"=>gT('Value equals SGQA'));

        $qattributes["num_value_int_only"]=array(
        "types"=>"N",
        'category'=>gT('Input'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'options'=>array(
        0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT('Restrict input to integer values'),
        "caption"=>gT('Integer only'));

        $qattributes["numbers_only"]=array(
        "types"=>"Q;S*",
        'category'=>gT('Other'),
        'sortorder'=>150,
        'inputtype'=>'singleselect',
        'options'=>array(
        0=>gT('No'),
        1=>gT('Yes')
        ),
        'default'=>0,
        "help"=>gT('Allow only numerical input'),
        "caption"=>gT('Numbers only')
        );

        $qattributes['show_totals'] =    array(
        'types' =>    ';',
        'category' =>    gT('Other'),
        'sortorder' =>    151,
        'inputtype'    => 'singleselect',
        'options' =>    array(
        'X' =>    gT('Off'),
        'R' =>    gT('Rows'),
        'C' =>    gT('Columns'),
        'B' =>    gT('Both rows and columns')
        ),
        'default' =>    'X',
        'help' =>    gT('Show totals for either rows, columns or both rows and columns'),
        'caption' =>    gT('Show totals for')
        );

        $qattributes['show_grand_total'] =    array(
        'types' =>    ';',
        'category' =>    gT('Other'),
        'sortorder' =>    152,
        'inputtype' =>    'singleselect',
        'options' =>    array(
        0 =>    gT('No'),
        1 =>    gT('Yes')
        ),
        'default' =>    0,
        'help' =>    gT('Show grand total for either columns or rows'),
        'caption' =>    gT('Show grand total')
        );

        $qattributes["input_boxes"]=array(
        "types"=>":",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT("Present as text input boxes instead of dropdown lists"),
        "caption"=>gT("Text inputs"));

        $qattributes["other_comment_mandatory"]=array(
        "types"=>"PLW!ZO" . Question::TYPE_MULTIPLE_CHOICE,
        'category'=>gT('Logic'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT("Make the 'Other:' comment field mandatory when the 'Other:' option is active"),
        "caption"=>gT("'Other:' comment mandatory"));

        $qattributes["other_numbers_only"]=array(
        "types"=>"LMP",
        'category'=>gT('Logic'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT("Allow only numerical input for 'Other' text"),
        "caption"=>gT("Numbers only for 'Other'"));

        $qattributes["other_replace_text"]=array(
        "types"=>"LMPWZ!",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'text',
        'i18n'=>true,
        "help"=>gT("Replaces the label of the 'Other:' answer option with a custom text"),
        "caption"=>gT("Label for 'Other:' option"));

        $qattributes["page_break"]=array(
        "types"=>"15ABCDEFGHKLMNOPQRSTUWXYZ!:;|*",
        'category'=>gT('Other'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT('Insert a page break before this question in printable view by setting this to Yes.'),
        "caption"=>gT('Insert page break in printable view'));

        $qattributes["prefix"]=array(
        "types"=>"KNQS",
        'category'=>gT('Display'),
        'sortorder'=>10,
        'inputtype'=>'text',
        'i18n'=>true,
        "help"=>gT('Add a prefix to the answer field'),
        "caption"=>gT('ls\models\Answer prefix'));

        $qattributes["printable_help"]=array(
        "types"=>"15ABCDEFGHKLMNOPRWYZ!:*",
        'category'=>gT('Display'),
        'sortorder'=>201,
        "inputtype"=>"text",
        'i18n'=>true,
        'default'=>"",
        "help"=>gT('In the printable version replace the relevance equation with this explanation text.'),
        "caption"=>gT("Relevance help for printable survey"));

        $qattributes["public_statistics"]=array(
        "types"=>"15ABCEFGHKLMNOPRWYZ!:*",
        'category'=>gT('Statistics'),
        'sortorder'=>80,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT('Show statistics of this question in the public statistics page'),
        "caption"=>gT('Show in public statistics'));

        $qattributes["random_order"]=array(
        "types"=>"!ABCEFHKLMOPQRWZ1:;",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('Off'),
        1=>gT('Randomize on each page load')
        //,2=>gT('Randomize once on survey start')  //Mdekker: commented out as code to handle this was removed in refactoring
        ),
        'default'=>0,
        "help"=>gT('Present subquestions/answer options in random order'),
        "caption"=>gT('Random order'));

        /*
        $qattributes['relevance']=array(
        'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*',
        'category'=>gT('Display'),
        'sortorder'=>1,
        'inputtype'=>'text',
        'default'=>'1',
        'help'=>gT('The relevance equation determines whether a question should be shown (if true) or hiddden and marked as Not Applicable (if false).'
        . '  The relevance equation can be as complex as you like, using any combination of mathematical operators, nested parentheses,'
        . ' any variable or token that has already been set, and any of more than 50 functions.  It is parsed by the ExpressionManager.'),
        'caption'=>gT('Relevance equation'));
        */

        $qattributes["showpopups"]=array(
        "types"=>"R",
        'category'=>gT('Display'),
        'sortorder'=>110,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>1,
        "caption"=>gT('Show javascript alert'),
        "help"=>gT('Show an alert if answers exceeds the number of max answers'));
        $qattributes["samechoiceheight"]=array(
        "types"=>"R",
        'category'=>gT('Display'),
        'sortorder'=>120,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>1,
        "caption"=>gT('Same height for all answer options'),
        "help"=>gT('Force each answer option to have the same height'));
        $qattributes["samelistheight"]=array(
        "types"=>"R",
        'category'=>gT('Display'),
        'sortorder'=>121,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>1,
        "caption"=>gT('Same height for lists'),
        "help"=>gT('Force the choice list and the rank list to have the same height'));

        $qattributes["parent_order"]=array(
        "types"=>":",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'text',
        "caption"=>gT('Get order from previous question'),
        "help"=>gT('Enter question ID to get subquestion order from a previous question'));

        $qattributes["slider_layout"]=array(
        "types"=>"K",
        'category'=>gT('Slider'),
        'sortorder'=>1,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT('Use slider layout'),
        "caption"=>gT('Use slider layout'));

        $qattributes["slider_min"]=array(
        "types"=>"K",
        'category'=>gT('Slider'),
        'sortorder'=>10,
        'inputtype'=>'text',
        "help"=>gT('You can use Expression manager, but this must be a number before showing the page else set to 0. If minimum value is not set, this value is used.'),
        "caption"=>gT('Slider minimum value'));

        $qattributes["slider_max"]=array(
        "types"=>"K",
        'category'=>gT('Slider'),
        'sortorder'=>11,
        'inputtype'=>'text',
        "help"=>gT('You can use Expression manager, but this must be a number before showing the page else set to 100. If maximum value is not set, this value is used.'),
        "caption"=>gT('Slider maximum value'));

        $qattributes["slider_accuracy"]=array(
        "types"=>"K",
        'category'=>gT('Slider'),
        'sortorder'=>30,
        'inputtype'=>'text',
        "help"=>gT('You can use Expression manager, but this must be a number before showing the page else set to 1.'),
        "caption"=>gT('Slider accuracy'));

        $qattributes["slider_default"]=array(
        "types"=>"K",
        'category'=>gT('Slider'),
        'sortorder'=>50,
        'inputtype'=>'text',
        "help"=>gT('Slider start as this value (this will set the initial value). You can use Expression manager, but this must be a number before showing the page.'),
        "caption"=>gT('Slider initial value'));

        $qattributes["slider_middlestart"]=array(
        "types"=>"K",
        'category'=>gT('Slider'),
        'sortorder'=>40,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT('The handle is displayed at the middle of the slider except if Slider initial value is set (this will not set the initial value).'),
        "caption"=>gT('Slider starts at the middle position'));

        $qattributes["slider_rating"]=array(
        "types"=>"5",
        'category'=>gT('Display'),
        'sortorder'=>90,
        'inputtype'=>'singleselect',
        'options'=>array(
        0=>gT('No'),
        1=>gT('Yes - stars'),
        2=>gT('Yes - slider with emoticon'),
        ),
        'default'=>0,
        "help"=>gT('Use slider layout'),
        "caption"=>gT('Use slider layout'));

        $qattributes["slider_reset"]=array(
        "types"=>"K",
        'category'=>gT('Slider'),
        'sortorder'=>50,
        'inputtype'=>'singleselect',
        'options'=>array(
        0=>gT('No'),
        1=>gT('Yes'),
        ),
        'default'=>0,
        "help"=>gT('Add a button to reset the slider. If you choose an start value, it reset at start value, else empty the answer.'),
        "caption"=>gT('Allow reset the slider'));

        $qattributes["slider_showminmax"]=array(
        "types"=>"K",
        'category'=>gT('Slider'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT('Display min and max value under the slider'),
        "caption"=>gT('Display slider min and max value'));

        $qattributes["slider_separator"]=array(
        "types"=>"K",
        'category'=>gT('Slider'),
        'sortorder'=>110,
        'inputtype'=>'text',
        "help"=>gT('ls\models\Answer|Left-slider-text|Right-slider-text separator character'),
        "caption"=>gT('Slider left/right text separator'));

        $qattributes["suffix"]=array(
        "types"=>"KNQS",
        'category'=>gT('Display'),
        'sortorder'=>11,
        'inputtype'=>'text',
        'i18n'=>true,
        "help"=>gT('Add a suffix to the answer field'),
        "caption"=>gT('ls\models\Answer suffix'));

        $qattributes["text_input_width"]=array(
        "types"=>"KNSTUQ;",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'text',
        "help"=>gT('Width of text input box'),
        "caption"=>gT('Input box width'));

        $qattributes["use_dropdown"]=array(
        "types"=>"1FO",
        'category'=>gT('Display'),
        'sortorder'=>112,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT('Present dropdown control(s) instead of list of radio buttons'),
        "caption"=>gT('Use dropdown presentation'));


        $qattributes["dropdown_size"]=array(
        "types"=>"!",   // TODO add these later?  "1F",
        'category'=>gT('Display'),
        'sortorder'=>200,
        'inputtype'=>'text',
        'default'=>0,
        "help"=>gT('For list dropdown boxes, show up to this many rows'),
        "caption"=>gT('Height of dropdown'));

        $qattributes["dropdown_prefix"]=array(
        "types"=>"!",   // TODO add these later?  "1F",
        'category'=>gT('Display'),
        'sortorder'=>201,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('None'),
        1=>gT('Order - like 3)'),
        ),
        'default'=>0,
        "help"=>gT('Accelerator keys for list items'),
        "caption"=>gT('Prefix for list items'));

        $qattributes["scale_export"]=array(
        "types"=>"CEFGHLMOPWYZ1!:*",
        'category'=>gT('Other'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'options'=>array(0=>gT('Default'),
        1=>gT('Nominal'),
        2=>gT('Ordinal'),
        3=>gT('Scale')),
        'default'=>0,
        "help"=>gT("Set a specific SPSS export scale type for this question"),
        "caption"=>gT('SPSS export scale type'));

        $qattributes["choice_title"]=array(
        "types"=>"R",
        'category'=>gT('Other'),
        'sortorder'=>200,
        "inputtype"=>"text",
        'i18n'=>true,
        'default'=>"",
        "help"=>sprintf(gT("Replace choice header (default: \"%s\")",'js'),gT("Your Choices")),
        "caption"=>gT("Choice header"));

        $qattributes["rank_title"]=array(
        "types"=>"R",
        'category'=>gT('Other'),
        'sortorder'=>201,
        "inputtype"=>"text",
        'i18n'=>true,
        'default'=>"",
        "help"=>sprintf(gT("Replace rank header (default: \"%s\")",'js'),gT("Your Ranking")),
        "caption"=>gT("Rank header"));

        //Timer attributes
        $qattributes["time_limit"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>90,
        "inputtype"=>"integer",
        "help"=>gT("Limit time to answer question (in seconds)"),
        "caption"=>gT("Time limit"));

        $qattributes["time_limit_action"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>92,
        'inputtype'=>'singleselect',
        'options'=>array(1=>gT('Warn and move on'),
        2=>gT('Move on without warning'),
        3=>gT('Disable only')),
        "default" => 1,
        "help"=>gT("Action to perform when time limit is up"),
        "caption"=>gT("Time limit action"));

        $qattributes["time_limit_disable_next"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>94,
        "inputtype"=>"singleselect",
        'default'=>0,
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        "help"=>gT("Disable the next button until time limit expires"),
        "caption"=>gT("Time limit disable next"));

        $qattributes["time_limit_disable_prev"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>96,
        "inputtype"=>"singleselect",
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>0,
        "help"=>gT("Disable the prev button until the time limit expires"),
        "caption"=>gT("Time limit disable prev"));

        $qattributes["time_limit_countdown_message"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>98,
        "inputtype"=>"textarea",
        'i18n'=>true,
        "help"=>gT("The text message that displays in the countdown timer during the countdown"),
        "caption"=>gT("Time limit countdown message"));

        $qattributes["time_limit_timer_style"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>100,
        "inputtype"=>"textarea",
        "help"=>gT("CSS Style for the message that displays in the countdown timer during the countdown"),
        "caption"=>gT("Time limit timer CSS style"));

        $qattributes["time_limit_message_delay"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>102,
        "inputtype"=>"integer",
        "help"=>gT("Display the 'time limit expiry message' for this many seconds before performing the 'time limit action' (defaults to 1 second if left blank)"),
        "caption"=>gT("Time limit expiry message display time"));

        $qattributes["time_limit_message"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>104,
        "inputtype"=>"textarea",
        'i18n'=>true,
        "help"=>gT("The message to display when the time limit has expired (a default message will display if this setting is left blank)"),
        "caption"=>gT("Time limit expiry message"));

        $qattributes["time_limit_message_style"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>106,
        "inputtype"=>"textarea",
        "help"=>gT("CSS style for the 'time limit expiry message'"),
        "caption"=>gT("Time limit message CSS style"));

        $qattributes["time_limit_warning"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>108,
        "inputtype"=>"integer",
        "help"=>gT("Display a 'time limit warning' when there are this many seconds remaining in the countdown (warning will not display if left blank)"),
        "caption"=>gT("1st time limit warning message timer"));

        $qattributes["time_limit_warning_display_time"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>110,
        "inputtype"=>"integer",
        "help"=>gT("The 'time limit warning' will stay visible for this many seconds (will not turn off if this setting is left blank)"),
        "caption"=>gT("1st time limit warning message display time"));

        $qattributes["time_limit_warning_message"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>112,
        "inputtype"=>"textarea",
        'i18n'=>true,
        "help"=>gT("The message to display as a 'time limit warning' (a default warning will display if this is left blank)"),
        "caption"=>gT("1st time limit warning message"));

        $qattributes["time_limit_warning_style"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>114,
        "inputtype"=>"textarea",
        "help"=>gT("CSS style used when the 'time limit warning' message is displayed"),
        "caption"=>gT("1st time limit warning CSS style"));

        $qattributes["time_limit_warning_2"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>116,
        "inputtype"=>"integer",
        "help"=>gT("Display the 2nd 'time limit warning' when there are this many seconds remaining in the countdown (warning will not display if left blank)"),
        "caption"=>gT("2nd time limit warning message timer"));

        $qattributes["time_limit_warning_2_display_time"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>118,
        "inputtype"=>"integer",
        "help"=>gT("The 2nd 'time limit warning' will stay visible for this many seconds (will not turn off if this setting is left blank)"),
        "caption"=>gT("2nd time limit warning message display time"));

        $qattributes["time_limit_warning_2_message"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>120,
        "inputtype"=>"textarea",
        'i18n'=>true,
        "help"=>gT("The 2nd message to display as a 'time limit warning' (a default warning will display if this is left blank)"),
        "caption"=>gT("2nd time limit warning message"));

        $qattributes["time_limit_warning_2_style"]=array(
        "types"=>"STUXL!",
        'category'=>gT('Timer'),
        'sortorder'=>122,
        "inputtype"=>"textarea",
        "help"=>gT("CSS style used when the 2nd 'time limit warning' message is displayed"),
        "caption"=>gT("2nd time limit warning CSS style"));

        $qattributes["date_format"]=array(
        "types"=>"D",
        'category'=>gT('Input'),
        'sortorder'=>100,
        "inputtype"=>"text",
        "help"=>gT("Specify a custom date/time format (the <i>d/dd m/mm yy/yyyy H/HH M/MM</i> formats and \"-./: \" characters are allowed for day/month/year/hour/minutes without or with leading zero respectively. Defaults to survey's date format"),
        "caption"=>gT("Date/Time format"));

        $qattributes["dropdown_dates_minute_step"]=array(
        "types"=>"D",
        'category'=>gT('Input'),
        'sortorder'=>100,
        "inputtype"=>"integer",
        'default'=>1,
        "help"=>gT("Minute step interval when using select boxes"),
        "caption"=>gT("Minute step interval"));

        $qattributes["dropdown_dates_month_style"]=array(
        "types"=>"D",
        'category'=>gT('Display'),
        'sortorder'=>100,
        "inputtype"=>"singleselect",
        'options'=>array(0=>gT('Short names'),
        1=>gT('Full names'),
        2=>gT('Numbers')),
        'default'=>0,
        "help"=>gT("Change the display style of the month when using select boxes"),
        "caption"=>gT("Month display style"));

        $qattributes["show_title"]=array(
        "types"=>"|",
        'category'=>gT('File metadata'),
        'sortorder'=>124,
        "inputtype"=>"singleselect",
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>1,
        "help"=>gT("Is the participant required to give a title to the uploaded file?"),
        "caption"=>gT("Show title"));

        $qattributes["show_comment"]=array(
        "types"=>"|",
        'category'=>gT('File metadata'),
        'sortorder'=>126,
        "inputtype"=>"singleselect",
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>1,
        "help"=>gT("Is the participant required to give a comment to the uploaded file?"),
        "caption"=>gT("Show comment"));


        $qattributes["max_filesize"]=array(
        "types"=>"|",
        'category'=>gT('Other'),
        'sortorder'=>128,
        "inputtype"=>"integer",
        'default'=>10240,
        "help"=>gT("The participant cannot upload a single file larger than this size"),
        "caption"=>gT("Maximum file size allowed (in KB)"));

        $qattributes["max_num_of_files"]=array(
        "types"=>"|",
        'category'=>gT('Other'),
        'sortorder'=>130,
        "inputtype"=>"text",
        'default'=>'1',
        "help"=>gT("Maximum number of files that the participant can upload for this question"),
        "caption"=>gT("Max number of files"));

        $qattributes["min_num_of_files"]=array(
        "types"=>"|",
        'category'=>gT('Other'),
        'sortorder'=>132,
        "inputtype"=>"text",
        'default'=>'0',
        "help"=>gT("Minimum number of files that the participant must upload for this question"),
        "caption"=>gT("Min number of files"));

        $qattributes["allowed_filetypes"]=array(
        "types"=>"|",
        'category'=>gT('Other'),
        'sortorder'=>134,
        "inputtype"=>"text",
        'default'=>"png, gif, doc, odt",
        "help"=>gT("Allowed file types in comma separated format. e.g. pdf,doc,odt"),
        "caption"=>gT("Allowed file types"));

        $qattributes["random_group"]=array(
        "types"=>"15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|",
        'category'=>gT('Logic'),
        'sortorder'=>180,
        'inputtype'=>'text',
        "help"=>gT("Place questions into a specified randomization group, all questions included in the specified group will appear in a random order"),
        "caption"=>gT("Randomization group name"));

        // This is added to support historical behavior.  Early versions of 1.92 used a value of "No", so if there was a min_sum_value or equals_sum_value, the question was not valid
        // unless those criteria were met.  In later releases of 1.92, the default was changed so that missing values were allowed even if those attributes were set
        // This attribute lets authors control whether missing values should be allowed in those cases without needing to set min_answers
        // Existing surveys will use the old behavior, but if the author edits the question, the default will be the new behavior.
        $qattributes["value_range_allows_missing"]=array(
        "types"=>"K",
        'category'=>gT('Input'),
        'sortorder'=>100,
        "inputtype"=>"singleselect",
        'options'=>array(0=>gT('No'),
        1=>gT('Yes')),
        'default'=>1,
        "help"=>gT("Is no answer (missing) allowed when either 'Equals sum value' or 'Minimum sum value' are set?"),
        "caption"=>gT("Value range allows missing"));
        $qattributes["thousands_separator"] = array(
            'types' => 'NK',
            "help" => gT("Show a thousands separator when the user enters a value"),
            "caption" => gT("Thousands separator"),
            'category' => gT('Display'),
            'inputtype' => 'singleselect',
            'sortorder' => 100,
            'options' => array(
                0 => gT('No'),
                1 => gT('Yes')
            ),
            'default'=>0,
        );

    }
    //This builds a more useful array (don't modify)
    if ($returnByName==false)
    {
        if(!$qat)
        {
            foreach($qattributes as $qname=>$qvalue)
            {
                for ($i=0; $i<=strlen($qvalue['types'])-1; $i++)
                {
                    $qat[substr($qvalue['types'], $i, 1)][$qname]=array("name"=>$qname,
                    "inputtype"=>$qvalue['inputtype'],
                    "category"=>$qvalue['category'],
                    "sortorder"=>$qvalue['sortorder'],
                    "i18n"=>isset($qvalue['i18n'])?$qvalue['i18n']:false,
                    "readonly"=>isset($qvalue['readonly_when_active'])?$qvalue['readonly_when_active']:false,
                    "options"=>isset($qvalue['options'])?$qvalue['options']:'',
                    "default"=>isset($qvalue['default'])?$qvalue['default']:'',
                    "help"=>$qvalue['help'],
                    "caption"=>$qvalue['caption']);
                }
            }
        }
        return $qat;
    }
    else {
        return $qattributes;
    }
}

function categorySort($a, $b)
{
    $result=strnatcasecmp($a['category'], $b['category']);
    if ($result==0)
    {
        $result=$a['sortorder']-$b['sortorder'];
    }
    return $result;
}


// make a string safe to include in an HTML 'value' attribute.
function HTMLEscape($str) {
    // escape newline characters, too, in case we put a value from
    // a TEXTAREA  into an <input type="hidden"> value attribute.
    return str_replace(array("\x0A","\x0D"),array("&#10;","&#13;"),
    htmlspecialchars( $str, ENT_QUOTES ));
}


// make a string safe to include in a JavaScript String parameter.
function javascriptEscape($str, $strip_tags=false, $htmldecode=false) {
    $new_str ='';

    if ($htmldecode==true) {
        $str=html_entity_decode($str,ENT_QUOTES,'UTF-8');
    }
    if ($strip_tags==true)
    {
        $str=strip_tags($str);
    }
    return str_replace(array('\'','"', "\n", "\r"),
    array("\\'",'\u0022', "\\n",'\r'),
    $str);
}

/**
* This function mails a text $body to the recipient $to.
* You can use more than one recipient when using a semikolon separated string with recipients.
*
* @param string $body Body text of the email in plain text or HTML
* @param mixed $subject Email subject
* @param mixed $to Array with several email addresses or single string with one email address
* @param mixed $from
* @param mixed $sitename
* @param mixed $ishtml
* @param mixed $bouncemail
* @param mixed $attachment
* @return bool If successful returns true
*/
function SendEmailMessage($body, $subject, $to, $from, $sitename, $ishtml=false, $bouncemail=null, $attachments=null, $customheaders="")
{

    global $maildebug, $maildebugbody;


    $emailmethod = Yii::app()->getConfig('emailmethod');
    $emailsmtphost = Yii::app()->getConfig("emailsmtphost");
    $emailsmtpuser = Yii::app()->getConfig("emailsmtpuser");
    $emailsmtppassword = Yii::app()->getConfig("emailsmtppassword");
    $emailsmtpdebug = Yii::app()->getConfig("emailsmtpdebug");
    $emailsmtpssl = Yii::app()->getConfig("emailsmtpssl");
    $defaultlang = Yii::app()->getConfig("defaultlang");
    $emailcharset = Yii::app()->getConfig("emailcharset");

    if ($emailcharset!='utf-8')
    {
        $body=mb_convert_encoding($body,$emailcharset,'utf-8');
        $subject=mb_convert_encoding($subject,$emailcharset,'utf-8');
        $sitename=mb_convert_encoding($sitename,$emailcharset,'utf-8');
    }

    if (!is_array($to)){
        $to=array($to);
    }



    if (!is_array($customheaders) && $customheaders == '')
    {
        $customheaders=[];
    }
    if (Yii::app()->getConfig('demoMode'))
    {
        $maildebug=gT('Email was not sent because demo-mode is activated.');
        $maildebugbody='';
        return false;
    }

    if (is_null($bouncemail) )
    {
        $sender=$from;
    }
    else
    {
        $sender=$bouncemail;
    }


    $mail = new PHPMailer;
    if (!$mail->SetLanguage($defaultlang, Yii::getPathOfAlias('vendor.phpmailer.phpmailer.language')))
    {
        $mail->SetLanguage('en', Yii::getPathOfAlias('vendor.phpmailer.phpmailer.language'));
    }
    $mail->CharSet = $emailcharset;
    if (isset($emailsmtpssl) && trim($emailsmtpssl)!=='' && $emailsmtpssl!==0) {
        if ($emailsmtpssl===1) {$mail->SMTPSecure = "ssl";}
        else {$mail->SMTPSecure = $emailsmtpssl;}
    }

    $fromname='';
    $fromemail=$from;
    if (strpos($from,'<'))
    {
        $fromemail=substr($from,strpos($from,'<')+1,strpos($from,'>')-1-strpos($from,'<'));
        $fromname=trim(substr($from,0, strpos($from,'<')-1));
    }

    $sendername='';
    $senderemail=$sender;
    if (strpos($sender,'<'))
    {
        $senderemail=substr($sender,strpos($sender,'<')+1,strpos($sender,'>')-1-strpos($sender,'<'));
        $sendername=trim(substr($sender,0, strpos($sender,'<')-1));
    }

    switch ($emailmethod) {
        case "qmail":
            $mail->IsQmail();
            break;
        case "smtp":
            $mail->IsSMTP();
            if ($emailsmtpdebug>0)
            {
                $mail->SMTPDebug = $emailsmtpdebug;
            }
            if (strpos($emailsmtphost,':')>0)
            {
                $mail->Host = substr($emailsmtphost,0,strpos($emailsmtphost,':'));
                $mail->Port = substr($emailsmtphost,strpos($emailsmtphost,':')+1);
            }
            else {
                $mail->Host = $emailsmtphost;
            }
            $mail->Username =$emailsmtpuser;
            $mail->Password =$emailsmtppassword;
            if (trim($emailsmtpuser)!="")
            {
                $mail->SMTPAuth = true;
            }
            break;
        case "sendmail":
            $mail->IsSendmail();
            break;
        default:
            //Set to the default value to rule out incorrect settings.
            $emailmethod="mail";
            $mail->IsMail();
    }

    $mail->SetFrom($fromemail, $fromname);
    $mail->Sender = $senderemail; // Sets Return-Path for error notifications
    foreach ($to as $singletoemail)
    {
        if (strpos($singletoemail, '<') )
        {
            $toemail=substr($singletoemail,strpos($singletoemail,'<')+1,strpos($singletoemail,'>')-1-strpos($singletoemail,'<'));
            $toname=trim(substr($singletoemail,0, strpos($singletoemail,'<')-1));
            $mail->AddAddress($toemail,$toname);
        }
        else
        {
            $mail->AddAddress($singletoemail);
        }
    }
    if (is_array($customheaders))
    {
        foreach ($customheaders as $key=>$val) {
            $mail->AddCustomHeader($val);
        }
    }
    $mail->AddCustomHeader("X-Surveymailer: $sitename Emailer (LimeSurvey.sourceforge.net)");
    if (get_magic_quotes_gpc() != "0")    {$body = stripcslashes($body);}
    if ($ishtml)
    {
        $mail->IsHTML(true);
        //$mail->AltBody = strip_tags(breakToNewline(html_entity_decode($body,ENT_QUOTES,$emailcharset))); // Use included PHPmailer system see bug #8234
    }
    else
    {
        $mail->IsHTML(false);
    }
    $mail->Body = $body;
    // Add attachments if they are there.
    if (is_array($attachments))
    {
        foreach ($attachments as $attachment)
        {
            // Attachment is either an array with filename and attachment name.
            if (is_array($attachment))
            {
                $mail->AddAttachment($attachment[0], $attachment[1]);
            }
            else
            { // Or a string with the filename.
                $mail->AddAttachment($attachment);
            }
        }
    }

    if (trim($subject)!='') {$mail->Subject = "=?$emailcharset?B?" . base64_encode($subject) . "?=";}
    if ($emailsmtpdebug>0) {
        ob_start();
    }
    $sent=$mail->Send();
    $maildebug=$mail->ErrorInfo;
    if ($emailsmtpdebug>0) {
        $maildebug .= '<li>'. gT('SMTP debug output:').'</li><pre>'.strip_tags(ob_get_contents()).'</pre>';
        ob_end_clean();
    }
    $maildebugbody=$mail->Body;
    return $sent;
}


/**
*  This functions removes all HTML tags, Javascript, CRs, linefeeds and other strange chars from a given text
*
* @param string $sTextToFlatten  Text you want to clean
* @param boolan $keepSpan set to true for keep span, used for expression manager. Default: false
* @param boolan $bDecodeHTMLEntities If set to true then all HTML entities will be decoded to the specified charset. Default: false
* @param string $sCharset Charset to decode to if $decodeHTMLEntities is set to true. Default: UTF-8
* @param string $bStripNewLines strip new lines if true, if false replace all new line by \r\n. Default: true
*
* @return string  Cleaned text
*/
function flattenText($sTextToFlatten, $keepSpan=false, $bDecodeHTMLEntities=false, $sCharset='UTF-8', $bStripNewLines=true)
{
    $sNicetext = stripJavaScript($sTextToFlatten);
    // When stripping tags, add a space before closing tags so that strings with embedded HTML tables don't get concatenated
    $sNicetext = str_replace(array('</td','</th'),array(' </td',' </th'), $sNicetext);
    if ($keepSpan) {
        // Keep <span> so can show EM syntax-highlighting; add space before tags so that word-wrapping not destroyed when remove tags.
        $sNicetext = strip_tags($sNicetext,'<span><table><tr><td><th>');
    }
    else {
        $sNicetext = strip_tags($sNicetext);
    }
    // ~\R~u : see "What \R matches" and "Newline sequences" in http://www.pcre.org/pcre.txt - only available since PCRE 7.0
    if ($bStripNewLines ){  // strip new lines
        if (version_compare(substr(PCRE_VERSION,0,strpos(PCRE_VERSION,' ')),'7.0')>-1)
        {
            $sNicetext = preg_replace(array('~\R~u'),array(' '), $sNicetext);
        }
        else
        {
            // Poor man's replacement for line feeds
            $sNicetext = str_replace(array("\r\n","\n", "\r"), array(' ',' ',' '), $sNicetext);
        }
    }
    elseif (version_compare(substr(PCRE_VERSION,0,strpos(PCRE_VERSION,' ')),'7.0')>-1)// unify newlines to \r\n
    {
        $sNicetext = preg_replace(array('~\R~u'), array("\r\n"), $sNicetext);
    }
    if ($bDecodeHTMLEntities==true)
    {
        $sNicetext = str_replace('&nbsp;',' ', $sNicetext); // html_entity_decode does not convert &nbsp; to spaces
        $sNicetext = html_entity_decode($sNicetext, ENT_QUOTES, $sCharset);
    }
    $sNicetext = trim($sNicetext);
    return  $sNicetext;
}


/**
* getArrayFilterExcludesCascadesForGroup() queries the database and produces a list of array_filter_exclude questions and targets with in the same group
* @return returns a keyed nested array, keyed by the qid of the question, containing cascade information
*/
function getArrayFilterExcludesCascadesForGroup($surveyid, $gid="", $output="qid")
{
    $surveyid=\ls\helpers\Sanitize::int($surveyid);
    $gid=\ls\helpers\Sanitize::int($gid);

    $cascaded=[];
    $sources=[];
    $qidtotitle=[];
    $fieldmap = createFieldMap($surveyid,'full',false,false,getBaseLanguageFromSurveyID($surveyid));

    if($gid != "") {
        $qrows = arraySearchByKey($gid, $fieldmap, 'gid');
    } else {
        $qrows = $fieldmap;
    }
    $grows = []; //Create an empty array in case query not return any rows
    // Store each result as an array with in the $grows array
    foreach ($qrows as $qrow) {
        if (isset($qrow['gid']) && !empty($qrow['gid'])) {
            $grows[$qrow['qid']] = array('qid' => $qrow['qid'],'type' => $qrow['type'], 'mandatory' => $qrow['mandatory'], 'title' => $qrow['title'], 'gid' => $qrow['gid']);
        }
    }
    $attrmach = []; // Stores Matches of filters that have their values as questions within current group
    foreach ($grows as $qrow) // Cycle through questions to see if any have list_filter attributes
    {
        $qidtotitle[$qrow['qid']]=$qrow['title'];
        $qresult = \ls\models\QuestionAttribute::model()->getQuestionAttributes($qrow['qid'],$qrow['type']);
        if (isset($qresult['array_filter_exclude'])) // We Found a array_filter attribute
        {
            $val = $qresult['array_filter_exclude']; // Get the Value of the Attribute ( should be a previous question's title in same group )
            foreach ($grows as $avalue) // Cycle through all the other questions in this group until we find the source question for this array_filter
            {
                if ($avalue['title'] == $val)
                {
                    /* This question ($avalue) is the question that provides the source information we use
                    * to determine which answers show up in the question we're looking at, which is $qrow['qid']
                    * So, in other words, we're currently working on question $qrow['qid'], trying to find out more
                    * information about question $avalue['qid'], because that's the source */
                    $sources[$qrow['qid']]=$avalue['qid']; /* This question ($qrow['qid']) relies on answers in $avalue['qid'] */
                    if(isset($cascades)) {unset($cascades);}
                    $cascades=[];                     /* Create an empty array */

                    /* At this stage, we know for sure that this question relies on one other question for the filter */
                    /* But this function wants to send back information about questions that rely on multiple other questions for the filter */
                    /* So we don't want to do anything yet */

                    /* What we need to do now, is check whether the question this one relies on, also relies on another */

                    /* The question we are now checking is $avalue['qid'] */
                    $keepgoing=1;
                    $questiontocheck=$avalue['qid'];
                    /* If there is a key in the $sources array that is equal to $avalue['qid'] then we want to add that
                    * to the $cascades array */
                    while($keepgoing > 0)
                    {
                        if(!empty($sources[$questiontocheck]))
                        {
                            $cascades[] = $sources[$questiontocheck];
                            /* Now we need to move down the chain */
                            /* We want to check the $sources[$questiontocheck] question */
                            $questiontocheck=$sources[$questiontocheck];
                        } else {
                            /* Since it was empty, there must not be any more questions down the cascade */
                            $keepgoing=0;
                        }
                    }
                    /* Now add all that info */
                    if(count($cascades) > 0) {
                        $cascaded[$qrow['qid']]=$cascades;
                    }
                }
            }
        }
    }
    $cascade2=[];
    if($output == "title")
    {
        foreach($cascaded as $key=>$cascade) {
            foreach($cascade as $item)
            {
                $cascade2[$key][]=$qidtotitle[$item];
            }
        }
        $cascaded=$cascade2;
    }
    return $cascaded;
}




function CSVEscape($sString)
{
    $sString = preg_replace(array('~\R~u'), array(PHP_EOL), $sString);
    return '"' . str_replace('"','""', $sString) . '"';
}

function convertCSVRowToArray($string, $separator, $quotechar)
{
    $fields=preg_split('/' . $separator . '(?=([^"]*"[^"]*")*(?![^"]*"))/',trim($string));
    $fields=array_map('CSVUnquote',$fields);
    return $fields;
}

function createPassword()
{
    $aCharacters = "ABCDEGHJIKLMNOPQURSTUVWXYZabcdefhjmnpqrstuvwxyz23456789";
    $iPasswordLength = 12;
    $sPassword = '';
    for ($i=0; $i<$iPasswordLength; $i++)
    {
        $sPassword .= $aCharacters[(int)floor(rand(0,strlen($aCharacters)-1))];
    }
    return $sPassword;
}

function languageDropdown($surveyid,$selected)
{

    $homeurl = Yii::app()->getConfig('homeurl');
    $slangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
    $baselang = Survey::model()->findByPk($surveyid)->language;
    array_unshift($slangs,$baselang);
    $html = "<select class='listboxquestions' name='langselect' onchange=\"window.open(this.options[this.selectedIndex].value, '_top')\">\n";

    foreach ($slangs as $lang)
    {
        $link = Yii::app()->homeUrl.("/admin/dataentry/sa/view/surveyid/".$surveyid."/lang/".$lang);
        if ($lang == $selected) $html .= "\t<option value='{$link}' selected='selected'>".\ls\helpers\SurveyTranslator::getLanguageNameFromCode($lang,false)."</option>\n";
        if ($lang != $selected) $html .= "\t<option value='{$link}'>".\ls\helpers\SurveyTranslator::getLanguageNameFromCode($lang,false)."</option>\n";
    }
    $html .= "</select>";
    return $html;
}

function languageDropdownClean($surveyid,$selected)
{
    $slangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
    $baselang = Survey::model()->findByPk($surveyid)->language;
    array_unshift($slangs,$baselang);
    $html = "<select class='listboxquestions' id='language' name='language'>\n";
    foreach ($slangs as $lang)
    {
        if ($lang == $selected) $html .= "\t<option value='$lang' selected='selected'>".\ls\helpers\SurveyTranslator::getLanguageNameFromCode($lang,false)."</option>\n";
        if ($lang != $selected) $html .= "\t<option value='$lang'>".\ls\helpers\SurveyTranslator::getLanguageNameFromCode($lang,false)."</option>\n";
    }
    $html .= "</select>";
    return $html;
}

/**
* This function removes a directory recursively
*
* @param mixed $dirname
* @return bool
*/
function rmdirr($dirname)
{
    // Sanity check
    if (!file_exists($dirname)) {
        return false;
    }

    // Simple delete for a file
    if (is_file($dirname) || is_link($dirname)) {
        return @unlink($dirname);
    }

    // Loop through the folder
    $dir = dir($dirname);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Recurse
        rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
    }

    // Clean up
    $dir->close();
    return @rmdir($dirname);
}

/**
* This function removes surrounding and masking quotes from the CSV field
*
* @param mixed $field
* @return mixed
*/
function CSVUnquote($field)
{
    //print $field.":";
    $field = preg_replace ("/^\040*\"/", "", $field);
    $field = preg_replace ("/\"\040*$/", "", $field);
    $field= str_replace('""','"',$field);
    //print $field."\n";
    return $field;
}

/**
* This function return actual completion state
*
* @return string (complete|incomplete|all) or false
*/
function incompleteAnsFilterState()
{
    $letsfilter='';
    $letsfilter = returnGlobal('completionstate'); //read get/post completionstate

    // first let's initialize the incompleteanswers session variable
    if ($letsfilter != '')
    { // use the read value if not empty
        Yii::app()->session['incompleteanswers'] = $letsfilter;
    }
    elseif (empty(Yii::app()->session['incompleteanswers']))
    { // sets default variable value from config file
        Yii::app()->session['incompleteanswers'] = Yii::app()->getConfig('filterout_incomplete_answers');
    }

    if  (Yii::app()->session['incompleteanswers']=='complete' || Yii::app()->session['incompleteanswers']=='all' || Yii::app()->session['incompleteanswers']=='incomplete') {
        return Yii::app()->session['incompleteanswers'];
    }
    else
    { // last resort is to prevent filtering
        return false;
    }
}


/**
* isCaptchaEnabled($screen, $usecaptchamode)
* @param string $screen - the screen name for which to test captcha activation
*
* @return boolean - returns true if captcha must be enabled
**/
function isCaptchaEnabled($screen, $captchamode='')
{
    switch($screen)
    {
        case 'registrationscreen':
            if ($captchamode == 'A' ||
            $captchamode == 'B' ||
            $captchamode == 'D' ||
            $captchamode == 'R')
            {
                return true;
            }
            else
            {
                return false;
            }
            break;
        case 'surveyaccessscreen':
            if ($captchamode == 'A' ||
            $captchamode == 'B' ||
            $captchamode == 'C' ||
            $captchamode == 'X')
            {
                return true;
            }
            else
            {
                return false;
            }
            break;
        case 'saveandloadscreen':
            if ($captchamode == 'A' ||
            $captchamode == 'C' ||
            $captchamode == 'D' ||
            $captchamode == 'S')
            {
                return true;
            }
            else
            {
                return false;
            }
            return true;
            break;
        default:
            return true;
            break;
    }
}

/**
* used for import[survey|questions|groups]
*
* @param mixed $string
* @return mixed
*/
function convertCSVReturnToReturn($string)
{
    $string= str_replace('\n', "\n", $string);
    return str_replace('\%n', '\n', $string);
}

/**
* Check if a table does exist in the database
*
* @param string $sTableName Table name to check for (without dbprefix!))
* @return boolean True or false if table exists or not
*/
function tableExists($sTableName)
{
    $sTableName=Yii::app()->db->tablePrefix.str_replace(array('{','}'),array('',''),$sTableName);
    return in_array($sTableName,Yii::app()->db->schema->getTableNames());
}

// Returns false if the survey is anonymous,
// and a token table exists: in this case the completed field of a token
// will contain 'Y' instead of the submitted date to ensure privacy
// Returns true otherwise
function isTokenCompletedDatestamped($thesurvey)
{
    if ($thesurvey['anonymized'] == 'Y' &&  tableExists('tokens_'.$thesurvey['sid']))
    {
        return false;
    }
    else
    {
        return true;
    }
}

/**
* example usage
* $date = "2006-12-31 21:00";
* $shift "+6 hours"; // could be days, weeks... see function strtotime() for usage
*
* echo sql_date_shift($date, "Y-m-d H:i:s", $shift);
*
* will output: 2007-01-01 03:00:00
*
* @param mixed $date
* @param mixed $dformat
* @param mixed $shift
* @return string
*/
function dateShift($date, $dformat, $shift)
{
    return date($dformat, strtotime($shift, strtotime($date)));
}


// getBounceEmail: returns email used to receive error notifications
function getBounceEmail($surveyid)
{
    $surveyInfo=getSurveyInfo($surveyid);

    if ($surveyInfo['bounce_email'] == '')
    {
        return null; // will be converted to from in MailText
    }
    else
    {
        return $surveyInfo['bounce_email'];
    }
}

// getEmailFormat: returns email format for the survey
// returns 'text' or 'html'
function getEmailFormat($surveyid)
{
    $surveyInfo=getSurveyInfo($surveyid);
    if ($surveyInfo['htmlemail'] == 'Y')
    {
        return 'html';
    }
    else
    {
        return 'text';
    }

}

/**
* Translate links which are in any answer/question/survey/email template/label set to their new counterpart
*
* @param mixed $sType 'survey' or 'label'
* @param mixed $iOldSurveyID
* @param mixed $iNewSurveyID
* @param mixed $sString
* @return string
*/
function translateLinks($sType, $iOldSurveyID, $iNewSurveyID, $sString)
{
    if ($sType == 'survey')
    {
        $sPattern = "([^'\"]*)/upload/surveys/{$iOldSurveyID}/";
        $sReplace = Yii::app()->getConfig("publicurl")."upload/surveys/{$iNewSurveyID}/";
        return preg_replace('#'.$sPattern.'#', $sReplace, $sString);
    }
    elseif ($sType == 'label')
    {
        $pattern = "([^'\"]*)/upload/labels/{$iOldSurveyID}/";
        $replace = Yii::app()->getConfig("publicurl")."upload/labels/{$iNewSurveyID}/";
        return preg_replace('#'.$pattern.'#', $replace, $sString);
    }
    else // unknown type
    {
        return $sString;
    }
}

/**
* This function creates the old fieldnames for survey import
*
* @param mixed $iOldSID  The old survey id
* @param mixed $iNewSID  The new survey id
* @param array $aGIDReplacements An array with group ids (oldgid=>newgid)
* @param array $aQIDReplacements An array with question ids (oldqid=>newqid)
*/
function reverseTranslateFieldNames($iOldSID,$iNewSID,$aGIDReplacements,$aQIDReplacements)
{
    $aGIDReplacements=array_flip($aGIDReplacements);
    $aQIDReplacements=array_flip($aQIDReplacements);
    if ($iOldSID==$iNewSID) {
        $forceRefresh=true; // otherwise grabs the cached copy and throws undefined index exceptions
    }
    else {
        $forceRefresh=false;
    }
    $aFieldMap = createFieldMap($iNewSID,'short',$forceRefresh,false,getBaseLanguageFromSurveyID($iNewSID));

    $aFieldMappings=[];
    foreach ($aFieldMap as $sFieldname=>$aFieldinfo)
    {
        if ($aFieldinfo['qid']!=null)
        {
            $aFieldMappings[$sFieldname]=$iOldSID.'X'.$aGIDReplacements[$aFieldinfo['gid']].'X'.$aQIDReplacements[$aFieldinfo['qid']].$aFieldinfo['aid'];
            if ($aFieldinfo['type']=='1')
            {
                $aFieldMappings[$sFieldname]=$aFieldMappings[$sFieldname].'#'.$aFieldinfo['scale_id'];
            }
            // now also add a shortened field mapping which is needed for certain kind of condition mappings
            $aFieldMappings[$iNewSID.'X'.$aFieldinfo['gid'].'X'.$aFieldinfo['qid']]=$iOldSID.'X'.$aGIDReplacements[$aFieldinfo['gid']].'X'.$aQIDReplacements[$aFieldinfo['qid']];
            // Shortened field mapping for timings table
            $aFieldMappings[$iNewSID.'X'.$aFieldinfo['gid']]=$iOldSID.'X'.$aGIDReplacements[$aFieldinfo['gid']];
        }
    }
    return array_flip($aFieldMappings);
}

/**
* @todo Move this to the ls\models\Survey model.
* @param mixed $id
* @param mixed $type
*/
function hasResources($id,$type='survey')
{
    $dirname = Yii::app()->getConfig("uploaddir");

    if ($type == 'survey')
    {
        $dirname .= "/surveys/$id";
    }
    elseif ($type == 'label')
    {
        $dirname .= "/labels/$id";
    }
    else
    {
        return false;
    }

    if (is_dir($dirname) && $dh=opendir($dirname))
    {
        while(($entry = readdir($dh)) !== false)
        {
            if($entry !== '.' && $entry !== '..')
            {
                return true;
                break;
            }
        }
        closedir($dh);
    }
    else
    {
        return false;
    }

    return false;
}

/**
* Creates a random sequence of characters
*
* @param mixed $length Length of resulting string
* @param string $pattern To define which characters should be in the resulting string
*/
function randomChars($length,$pattern="23456789abcdefghijkmnpqrstuvwxyz")
{
    $patternlength = strlen($pattern)-1;
    $key = '';
    for($i=0;$i<$length;$i++)
    {
        $key .= $pattern{mt_rand(0,$patternlength)};
    }
    return $key;
}

/**
* used to translate simple text to html (replacing \n with <br />
*
* @param mixed $mytext
* @param mixed $ishtml
* @return mixed
*/
function conditionalNewlineToBreak($mytext,$ishtml,$encoded='')
{
    if ($ishtml === true)
    {
        // $mytext has been processed by gT with html mode
        // and thus \n has already been translated to &#10;
        if ($encoded == '')
        {
            $mytext=str_replace('&#10;', '<br />',$mytext);
        }
        return str_replace("\n", '<br />',$mytext);
    }
    else
    {
        return $mytext;
    }
}


function breakToNewline( $data ) {
    return preg_replace( '!<br.*>!iU', "\n", $data );
}


function fixCKeditorText($str)
{
    $str = str_replace('<br type="_moz" />','',$str);
    if ($str == "<br />" || $str == " " || $str == "&nbsp;")
    {
        $str = "";
    }
    if (preg_match("/^[\s]+$/",$str))
    {
        $str='';
    }
    if ($str == "\n")
    {
        $str = "";
    }
    if (trim($str) == "&nbsp;" || trim($str)=='')
    { // chrome adds a single &nbsp; element to empty fckeditor fields
        $str = "";
    }

    return $str;
}



/**
* Returns the full list of attribute token fields including the properties for each field
* Use this instead of plain ls\models\Survey::model()->findByPk($iSurveyID)->tokenAttributes calls because ls\models\Survey::model()->findByPk($iSurveyID)->tokenAttributes may contain old descriptions where the fields does not physically exist
*
* @param integer $iSurveyID The ls\models\Survey ID
*/
function GetParticipantAttributes($iSurveyID)
{
    if (!Token::valid($iSurveyID))
        return [];
    return getTokenFieldsAndNames($iSurveyID,true);
}


/**
* Retrieves the attribute names from the related token table
*
* @param mixed $surveyid  The survey ID
* @param boolean $bOnlyAttributes Set this to true if you only want the fieldnames of the additional attribue fields - defaults to false
* @return array The fieldnames as key and names as value in an Array
*/
function getTokenFieldsAndNames($surveyid, $bOnlyAttributes = false)
{


    $aBasicTokenFields=array('firstname'=>array(
        'description'=>gT('First name'),
        'mandatory'=>'N',
        'showregister'=>'Y'
        ),
        'lastname'=>array(
            'description'=>gT('Last name'),
            'mandatory'=>'N',
            'showregister'=>'Y'
        ),
        'email'=>array(
            'description'=>gT('Email address'),
            'mandatory'=>'N',
            'showregister'=>'Y'
        ),
        'emailstatus'=>array(
            'description'=>gT("Email status"),
            'mandatory'=>'N',
            'showregister'=>'N'
        ),
        'token'=>array(
            'description'=>gT('ls\models\Token'),
            'mandatory'=>'N',
            'showregister'=>'Y'
        ),
        'language'=>array(
            'description'=>gT('Language code'),
            'mandatory'=>'N',
            'showregister'=>'Y'
        ),
        'sent'=>array(
            'description'=>gT('Invitation sent date'),
            'mandatory'=>'N',
            'showregister'=>'Y'
        ),
        'remindersent'=>array(
            'description'=>gT('Last reminder sent date'),
            'mandatory'=>'N',
            'showregister'=>'Y'
        ),
        'remindercount'=>array(
            'description'=>gT('Total numbers of sent reminders'),
            'mandatory'=>'N',
            'showregister'=>'Y'
        ),
        'usesleft'=>array(
            'description'=>gT('Uses left'),
            'mandatory'=>'N',
            'showregister'=>'Y'
        ),
    );

    $aExtraTokenFields = Token::valid($surveyid) ? Token::model($surveyid)->attributeNames() : [];

    $aSavedExtraTokenFields = Survey::model()->findByPk($surveyid)->tokenAttributes;

    // Drop all fields that are in the saved field description but not in the table definition
    $aSavedExtraTokenFields=array_intersect_key($aSavedExtraTokenFields,array_flip($aExtraTokenFields));

    // Now add all fields that are in the table but not in the field description
    foreach ($aExtraTokenFields as $sField)
    {
        if (!isset($aSavedExtraTokenFields[$sField]))
        {
            $aSavedExtraTokenFields[$sField]=array(
            'description'=>$sField,
            'mandatory'=>'N',
            'showregister'=>'N',
            'cpdbmap'=>''
            );
        }
        elseif(empty($aSavedExtraTokenFields[$sField]['description']))
        {
            $aSavedExtraTokenFields[$sField]['description']=$sField;
        }
    }
    if ($bOnlyAttributes)
    {
        return $aSavedExtraTokenFields;
    }
    else
    {
        return array_merge($aBasicTokenFields,$aSavedExtraTokenFields);
    }
}


/**
* This function strips any content between and including <javascript> tags
*
* @param string $sContent String to clean
* @return string  Cleaned string
*/
function stripJavaScript($sContent){
    $text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $sContent);
    // TODO : Adding the onload/onhover etc ... or remove this false security function
    return $text;
}

/**
* This function converts emebedded Javascript to Text
*
* @param string $sContent String to clean
* @return string  Cleaned string
*/
function showJavaScript($sContent){
    $text = preg_replace_callback ('@<script[^>]*?>.*?</script>@si',         create_function(
            // single quotes are essential here,
            // or alternative escape all $ as \$
            '$matches',
            'return htmlspecialchars($matches[0]);'
        ), $sContent);
    return $text;
}


/**
* This is a convenience function for the coversion of datetime values
*
* @param mixed $value
* @param mixed $fromdateformat
* @param mixed $todateformat
* @return string
*/
function convertDateTimeFormat($value, $fromdateformat, $todateformat)
{
    Yii::import('application.libraries.Date_Time_Converter', true);
    $date = new Date_Time_Converter($value, $fromdateformat);
    return $date->convert($todateformat);
}



/**
* Return an array of subquestions for a given sid/qid
*
* @param int $sid
* @param int $qid
* @param $sLanguage Language of the subquestion text
*/
function getSubQuestions($sid, $qid, $sLanguage) {

    static $subquestions;

    if (!isset($subquestions[$sid]))
    {
        $subquestions[$sid]=[];
    }
    if (!isset($subquestions[$sid][$sLanguage])) {

        $query = "SELECT sq.*, q.other FROM {{questions}} as sq, {{questions}} as q"
        ." WHERE sq.parent_qid=q.qid AND q.sid=".$sid
        ." ORDER BY sq.parent_qid, q.question_order,sq.scale_id , sq.question_order";

        $query = Yii::app()->db->createCommand($query)->query();

        $resultset=[];
        //while ($row=$result->FetchRow())
        foreach ($query->readAll() as $row)
        {
            $resultset[$row['parent_qid']][] = $row;
        }
        $subquestions[$sid][$sLanguage] = $resultset;
    }
    if (isset($subquestions[$sid][$sLanguage][$qid])) return $subquestions[$sid][$sLanguage][$qid];
    return [];
}

/**
* SSLRedirect() generates a redirect URL for the appropriate SSL mode then applies it.
* (Was redirect() before CodeIgniter port.)
*
* @param $enforceSSLMode string 's' or '' (empty).
*/
function SSLRedirect($enforceSSLMode)
{
    $url = 'http'.$enforceSSLMode.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    if (!headers_sent())
    {    // If headers not sent yet... then do php redirect
        //ob_clean();
        header('Location: '.$url);
        //ob_flush();
        exit;
    };
};

/**
* enforceSSLMode() $force_ssl is on or off, it checks if the current
* request is to HTTPS (or not). If $force_ssl is on, and the
* request is not to HTTPS, it redirects the request to the HTTPS
* version of the URL, if the request is to HTTPS, it rewrites all
* the URL variables so they also point to HTTPS.
*/
function enforceSSLMode()
{
    $bSSLActive = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off")||
    (isset($_SERVER['HTTP_FORWARDED_PROTO']) && $_SERVER['HTTP_FORWARDED_PROTO']=="https")||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']=="https"));
    if (Yii::app()->getConfig('ssl_emergency_override') !== true )
    {
        $force_ssl = strtolower(\ls\models\SettingGlobal::get('force_ssl'));
    }
    else
    {
        $force_ssl = 'off';
    };
    if( $force_ssl == 'on' && !$bSSLActive )
    {
        SSLRedirect('s');
    }
    if( $force_ssl == 'off' && $bSSLActive)
    {
        SSLRedirect('');
    };
};

/**
* Returns the number of answers matching the quota
*
* @param int $iSurveyId - ls\models\Survey identification number
* @param int $quotaid - quota id for which you want to compute the completed field
* @return mixed - value of matching entries in the result DB or null
*/
function getQuotaCompletedCount($iSurveyId, $quotaid)
{
    if(!tableExists("survey_{$iSurveyId}")) // Yii::app()->db->schema->getTable('{{survey_' . $iSurveyId . '}}' are not updated even after Yii::app()->db->schema->refresh();
        return;
    $aColumnName=SurveyDynamic::model($iSurveyId)->getTableSchema()->getColumnNames();
    $aQuotas = getQuotaInformation($iSurveyId, Survey::model()->findByPk($iSurveyId)->language, $quotaid);
    $aQuota = $aQuotas[0];
    if (Yii::app()->db->schema->getTable('{{survey_' . $iSurveyId . '}}') &&
    count($aQuota['members']) > 0)
    {
        // Keep a list of fields for easy reference
        $aQuotaColumns = [];

        foreach ($aQuota['members'] as $member)
        {
            if(in_array($member['fieldname'],$aColumnName))
                $aQuotaColumns[$member['fieldname']][] = $member['value'];
            else
                return;
        }

        $oCriteria = new CDbCriteria;
        $oCriteria->condition="submitdate IS NOT NULL";
        foreach ($aQuotaColumns as $sColumn=>$aValue)
        {
            if(count($aValue)==1)
            {
                $oCriteria->compare(Yii::app()->db->quoteColumnName($sColumn),$aValue); // NO need params : compare bind
            }
            else
            {
                $oCriteria->addInCondition(Yii::app()->db->quoteColumnName($sColumn),$aValue); // NO need params : addInCondition bind
            }
        }
        return SurveyDynamic::model($iSurveyId)->count($oCriteria);
    }
}

/**
* Creates an array with details on a particular response for display purposes
* Used in Print answers, Detailed response view and Detailed admin notification email
*
* @param mixed $iSurveyID
* @param mixed $iResponseID
* @param mixed $sLanguageCode
* @param boolean $bHonorConditions Apply conditions
*/
function getFullResponseTable($iSurveyID, $iResponseID, $sLanguageCode, $bHonorConditions=true)
{
    $aFieldMap = createFieldMap($iSurveyID,'full',false,false,$sLanguageCode);

    //Get response data
    $idrow = SurveyDynamic::model($iSurveyID)->findByAttributes(array('id'=>$iResponseID));

    // Create array of non-null values - those are the relevant ones
    $aRelevantFields = [];

    foreach ($aFieldMap as $sKey=>$fname)
    {
        if (LimeExpressionManager::QuestionIsRelevant($fname['qid']) || $bHonorConditions==false)
        {
            $aRelevantFields[$sKey]=$fname;
        }
    }

    $aResultTable=[];
    $oldgid = 0;
    $oldqid = 0;
    foreach ($aRelevantFields as $sKey=>$fname)
    {
        if (!empty($fname['qid']))
        {
            $attributes = \ls\models\QuestionAttribute::model()->getQuestionAttributes($fname['qid']);
            if (getQuestionAttributeValue($attributes, 'hidden') == 1)
            {
                continue;
            }
        }
        $question = $fname['question'];
        $subquestion='';
        if (isset($fname['gid']) && !empty($fname['gid'])) {
            //Check to see if gid is the same as before. if not show group name
            if ($oldgid !== $fname['gid'])
            {
                $oldgid = $fname['gid'];
                if (LimeExpressionManager::GroupIsRelevant($fname['gid']) || $bHonorConditions==false) {
                    $aResultTable['gid_'.$fname['gid']]=array($fname['group_name'], QuestionGroup::model()->getGroupDescription($fname['gid'], $sLanguageCode));
                }
            }
        }
        if (!empty($fname['qid']))
        {
            if ($oldqid !== $fname['qid'])
            {
                $oldqid = $fname['qid'];
                if (isset($fname['subquestion']) || isset($fname['subquestion1']) || isset($fname['subquestion2']))
                {
                    $aResultTable['qid_'.$fname['sid'].'X'.$fname['gid'].'X'.$fname['qid']]=array($fname['question'],'','');
                }
                else
                {
                    $answer = getExtendedAnswer($iSurveyID,$fname['fieldname'], $idrow[$fname['fieldname']],$sLanguageCode);
                    $aResultTable[$fname['fieldname']]=array($question,'',$answer);
                    continue;
                }
            }
        }
        else
        {
            $answer=getExtendedAnswer($iSurveyID,$fname['fieldname'], $idrow[$fname['fieldname']],$sLanguageCode);
            $aResultTable[$fname['fieldname']]=array($question,'',$answer);
            continue;
        }
        if (isset($fname['subquestion']))
            $subquestion = "[{$fname['subquestion']}]";

        if (isset($fname['subquestion1']))
            $subquestion = "[{$fname['subquestion1']}]";

        if (isset($fname['subquestion2']))
            $subquestion .= "[{$fname['subquestion2']}]";

        $answer = getExtendedAnswer($iSurveyID,$fname['fieldname'], $idrow[$fname['fieldname']],$sLanguageCode);
        $aResultTable[$fname['fieldname']]=array($question,$subquestion,$answer);
    }
    return $aResultTable;
}

/**
* getQuotaInformation() returns quota information for the current survey
* @param string $surveyid - ls\models\Survey identification number
* @param string $language - Language of the quota
* @param string $quotaid - Optional quotaid that restricts the result to a given quota
* @return array - nested array, Quotas->Members
*/
function getQuotaInformation($surveyid,$language,$iQuotaID=null)
{
    Yii::log('getQuotaInformation');
    $baselang = Survey::model()->findByPk($surveyid)->language;
    $aAttributes=array('sid' => $surveyid);
    if ((int)$iQuotaID)
    {
        $aAttributes['id'] = $iQuotaID;
    }

    $aQuotas = Quota::model()->with(array('languagesettings' => array('condition' => "quotals_language='$language'")))->findAllByAttributes($aAttributes);

    $aSurveyQuotasInfo = [];
    $x=0;


    // Check all quotas for the current survey
    if (count($aQuotas) > 0)
    {
        foreach ($aQuotas as $oQuota)
        {
            // Array for each quota
            $aQuotaInfo = array_merge($oQuota->attributes,$oQuota->languagesettings[0]->attributes);// We have only one language, then we can use first only
            $aQuotaMembers = QuotaMember::model()->findAllByAttributes(array('quota_id'=>$oQuota->id));
            $aQuotaInfo['members'] = [];
            if (count($aQuotaMembers) > 0)
            {
                foreach ($aQuotaMembers as $oQuotaMember)
                {
                    $oMemberQuestion=Question::model()->findByAttributes(array('qid'=>$oQuotaMember->qid, 'language'=>$baselang));
                    if($oMemberQuestion)
                    {
                        $sFieldName = "0";

                        if ($oMemberQuestion->type == "I" || $oMemberQuestion->type == "G" || $oMemberQuestion->type == "Y")
                        {
                            $sFieldName=$surveyid.'X'.$oMemberQuestion->gid.'X'.$oQuotaMember->qid;
                            $sValue = $oQuotaMember->code;
                        }

                        if($oMemberQuestion->type == "L" || $oMemberQuestion->type == "O" || $oMemberQuestion->type =="!")
                        {
                            $sFieldName=$surveyid.'X'.$oMemberQuestion->gid.'X'.$oQuotaMember->qid;
                            $sValue = $oQuotaMember->code;
                        }

                        if($oMemberQuestion->type == "M")
                        {
                            $sFieldName=$surveyid.'X'.$oMemberQuestion->gid.'X'.$oQuotaMember->qid.$oQuotaMember->code;
                            $sValue = "Y";
                        }

                        if($oMemberQuestion->type == "A" || $oMemberQuestion->type == "B")
                        {
                            $temp = explode('-',$oQuotaMember->code);
                            $sFieldName=$surveyid.'X'.$oMemberQuestion->gid.'X'.$oQuotaMember->qid.$temp[0];
                            $sValue = $temp[1];
                        }

                        $aQuotaInfo['members'][]=array(
                            'title' => $oMemberQuestion->title,
                            'type' => $oMemberQuestion->type,
                            'code' => $oQuotaMember->code,
                            'value' => $sValue,
                            'qid' => $oQuotaMember->qid,
                            'fieldname' => $sFieldName,
                        );
                    }
                }
            }
            // Push this quota Information to all survey quota
            array_push($aSurveyQuotasInfo,$aQuotaInfo);
        }
    }
    return $aSurveyQuotasInfo;
}

/**
* Replaces EM variable codes in a current survey with a new one
*
* @param mixed $iSurveyID The survey ID
* @param mixed $aCodeMap The codemap array (old_code=>new_code)
*/
function replaceExpressionCodes ($iSurveyID, $aCodeMap)
{
   $arQuestions=Question::model()->findAll("sid=:sid",array(':sid'=>$iSurveyID));
   foreach ($arQuestions as $arQuestion)
   {
        $bModified=false;
        foreach ($aCodeMap as $sOldCode=>$sNewCode)
        {
            // Don't search/replace old codes that are too short or were numeric (because they would not have been usable in EM expressions anyway)
            if (strlen($sOldCode)>1 && !is_numeric($sOldCode[0]))
            {
                $sOldCode=preg_quote($sOldCode,'/');
                $arQuestion->relevance=preg_replace("~{[^}]*\K{$sOldCode}(?=[^}]*?})~",$sNewCode,$arQuestion->relevance,-1,$iCount);
                $bModified = $bModified || $iCount;
                $arQuestion->question=preg_replace("~{[^}]*\K{$sOldCode}(?=[^}]*?})~",$sNewCode,$arQuestion->question,-1,$iCount);
                $bModified = $bModified || $iCount;
            }
        }
        if ($bModified)
        {
            $arQuestion->save();
        }
   }
   $arGroups=QuestionGroup::model()->findAll("sid=:sid",array(':sid'=>$iSurveyID));
   foreach ($arGroups as $arGroup)
   {
        $bModified=false;
        foreach ($aCodeMap as $sOldCode=>$sNewCode)
        {
            $sOldCode=preg_quote($sOldCode,'/');
            $arGroup->grelevance=preg_replace("~{[^}]*\K{$sOldCode}(?=[^}]*?})~",$sNewCode,$arGroup->grelevance,-1,$iCount);
            $bModified = $bModified || $iCount;
            $arGroup->description=preg_replace("~{[^}]*\K{$sOldCode}(?=[^}]*?})~",$sNewCode,$arGroup->description,-1,$iCount);
            $bModified = $bModified || $iCount;
        }
        if ($bModified)
        {
            $arGroup->save();
        }
   }
}


/**
* This function switches identity insert on/off for the MSSQL database
*
* @param string $table table name (without prefix)
* @param mixed $state  Set to true to activate ID insert, or false to deactivate
*/
function switchMSSQLIdentityInsert($table,$state)
{
    if (in_array(Yii::app()->db->getDriverName(), array('mssql', 'sqlsrv', 'dblib')))
    {
        if ($state == true)
        {
            // This needs to be done directly on the PDO object because when using CdbCommand or similar it won't have any effect
            Yii::app()->db->pdoInstance->exec('SET IDENTITY_INSERT '.Yii::app()->db->tablePrefix.$table.' ON');
        }
        else
        {
            // This needs to be done directly on the PDO object because when using CdbCommand or similar it won't have any effect
            Yii::app()->db->pdoInstance->exec('SET IDENTITY_INSERT '.Yii::app()->db->tablePrefix.$table.' OFF');
        }
    }
}

/**
* Retrieves the last Insert ID realiable for cross-DB applications
*
* @param string $sTableName Needed for Postgres and MSSQL
*/
function getLastInsertID($sTableName)
{
    $sDBDriver=Yii::app()->db->getDriverName();
    if ($sDBDriver=='mysql' || $sDBDriver=='mysqli')
    {
        return Yii::app()->db->getLastInsertID();
    }
    else
    {
        return Yii::app()->db->getCommandBuilder()->getLastInsertID($sTableName);
    }
}


function getGroupDepsForConditions($sid,$depgid="all",$targgid="all",$indexby="by-depgid")
{
    $sid=\ls\helpers\Sanitize::int($sid);
    $condarray = Array();
    $sqldepgid="";
    $sqltarggid="";
    if ($depgid != "all") { $depgid = \ls\helpers\Sanitize::int($depgid); $sqldepgid="AND tq.gid=$depgid";}
    if ($targgid != "all") {$targgid = \ls\helpers\Sanitize::int($targgid); $sqltarggid="AND tq2.gid=$targgid";}

    $baselang = Survey::model()->findByPk($sid)->language;
    $condquery = "SELECT tg.gid as depgid, tg.group_name as depgpname, "
    . "tg2.gid as targgid, tg2.group_name as targgpname, tq.qid as depqid, tc.cid FROM "
    . "{{conditions}} AS tc, "
    . "{{questions}} AS tq, "
    . "{{questions}} AS tq2, "
    . "{{groups}} AS tg ,"
    . "{{groups}} AS tg2 "
    . "WHERE tq.language='{$baselang}' AND tq2.language='{$baselang}' AND tg.language='{$baselang}' AND tg2.language='{$baselang}' AND tc.qid = tq.qid AND tq.sid=$sid "
    . "AND tq.gid = tg.gid AND tg2.gid = tq2.gid "
    . "AND tq2.qid=tc.cqid AND tq.gid != tg2.gid $sqldepgid $sqltarggid";
    $condresult = Yii::app()->db->createCommand($condquery)->query()->readAll();

    if (count($condresult) > 0) {
        foreach ($condresult as $condrow)
        {

            switch ($indexby)
            {
                case "by-depgid":
                    $depgid=$condrow['depgid'];
                    $targetgid=$condrow['targgid'];
                    $depqid=$condrow['depqid'];
                    $cid=$condrow['cid'];
                    $condarray[$depgid][$targetgid]['depgpname'] = $condrow['depgpname'];
                    $condarray[$depgid][$targetgid]['targetgpname'] = $condrow['targgpname'];
                    $condarray[$depgid][$targetgid]['conditions'][$depqid][]=$cid;
                    break;

                case "by-targgid":
                    $depgid=$condrow['depgid'];
                    $targetgid=$condrow['targgid'];
                    $depqid=$condrow['depqid'];
                    $cid=$condrow['cid'];
                    $condarray[$targetgid][$depgid]['depgpname'] = $condrow['depgpname'];
                    $condarray[$targetgid][$depgid]['targetgpname'] = $condrow['targgpname'];
                    $condarray[$targetgid][$depgid]['conditions'][$depqid][] = $cid;
                    break;
            }
        }
        return $condarray;
    }
    return null;
}

// TMSW Condition->Relevance:  This function is not needed?  Optionally replace this with call to EM to get similar info
/**
* getQuestDepsForConditions() get Dependencies between groups caused by conditions
* @param string $sid - the currently selected survey
* @param string $gid - (optionnal) only search dependecies inside the Group Id $gid
* @param string $depqid - (optionnal) get only the dependencies applying to the question with qid depqid
* @param string $targqid - (optionnal) get only the dependencies for questions dependents on question Id targqid
* @param string $index-by - (optionnal) "by-depqid" for result indexed with $res[$depqid][$targqid]
*                   "by-targqid" for result indexed with $res[$targqid][$depqid]
* @return array - returns an array describing the conditions or NULL if no dependecy is found
*
* Example outupt assumin $index-by="by-depqid":
*Array
*(
*    [184] => Array     // ls\models\Question Id 184
*        (
*            [183] => Array // Depends on ls\models\Question Id 183
*                (
*                    [0] => 5   // Because of condition Id 5
*                )
*
*        )
*
*)
*
* Usage example:
*   * Get all questions dependencies for ls\models\Survey $sid and group $gid indexed by depqid:
*       $result=getQuestDepsForConditions($sid,$gid);
*   * Get all questions dependencies for question $qid in survey/group $sid/$gid indexed by depqid:
*       $result=getGroupDepsForConditions($sid,$gid,$qid);
*   * Get all questions dependents on question $qid in survey/group $sid/$gid indexed by targqid:
*       $result=getGroupDepsForConditions($sid,$gid,"all",$qid,"by-targgid");
*/
function getQuestDepsForConditions($sid,$gid="all",$depqid="all",$targqid="all",$indexby="by-depqid", $searchscope="samegroup")
{

    $condarray = Array();

    $baselang = Survey::model()->findByPk($sid)->language;
    $sqlgid="";
    $sqldepqid="";
    $sqltargqid="";
    $sqlsearchscope="";
    if ($gid != "all") {$gid = \ls\helpers\Sanitize::int($gid); $sqlgid="AND tq.gid=$gid";}
    if ($depqid != "all") {$depqid = \ls\helpers\Sanitize::int($depqid); $sqldepqid="AND tq.qid=$depqid";}
    if ($targqid != "all") {$targqid = \ls\helpers\Sanitize::int($targqid); $sqltargqid="AND tq2.qid=$targqid";}
    if ($searchscope == "samegroup") {$sqlsearchscope="AND tq2.gid=tq.gid";}

    $condquery = "SELECT tq.qid as depqid, tq2.qid as targqid, tc.cid
    FROM {{conditions}} AS tc, {{questions}} AS tq, {{questions}} AS tq2
    WHERE tq.language='{$baselang}' AND tq2.language='{$baselang}' AND tc.qid = tq.qid AND tq.sid='$sid'
    AND  tq2.qid=tc.cqid $sqlsearchscope $sqlgid $sqldepqid $sqltargqid";
    $condresult=Yii::app()->db->createCommand($condquery)->query()->readAll();
    if (count($condresult) > 0) {
        foreach ($condresult as $condrow)
        {
            $depqid=$condrow['depqid'];
            $targetqid=$condrow['targqid'];
            $condid=$condrow['cid'];
            switch ($indexby)
            {
                case "by-depqid":
                    $condarray[$depqid][$targetqid][] = $condid;
                    break;

                case "by-targqid":
                    $condarray[$targetqid][$depqid][] = $condid;
                    break;
            }
        }
        return $condarray;
    }
    return null;
}

/**
* Returns labelsets for given language(s), or for all if null
*
* @param string $languages
* @return array
*/
function getLabelSets($languages = null)
{


    $languagesarray = [];
    if ($languages)
    {
        $languages=sanitize_languagecodeS($languages);
        $languagesarray=explode(' ',trim($languages));
    }

    $criteria = new CDbCriteria;
    $criteria->order = "label_name";
    foreach ($languagesarray as $k => $item)
    {
        $criteria->params[':lang_like1_' . $k] = "% $item %";
        $criteria->params[':lang_' . $k] = $item;
        $criteria->params[':lang_like2_' . $k] = "% $item";
        $criteria->params[':lang_like3_' . $k] = "$item %";
        $criteria->addCondition("
        ((languages like :lang_like1_$k) or
        (languages = :lang_$k) or
        (languages like :lang_like2_$k) or
        (languages like :lang_like3_$k))");
    }

    $result = LabelSet::model()->findAll($criteria);
    $labelsets=[];
    foreach ($result as $row)
        $labelsets[] = array($row->lid, $row->label_name);
    return $labelsets;
}

function getHeader($meta = false)
{
    global $embedded,$surveyid ;

    // Set Langage // TODO remove one of the Yii::app()->session see bug #5901

    $languagecode = isset(App()->surveySessionManager->current) ?App()->surveySessionManager->current->language : App()->language;

    $header=  "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n"
    . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$languagecode}\" lang=\"{$languagecode}\"";
    if (\ls\helpers\SurveyTranslator::getLanguageRTL($languagecode))
    {
        $header.=" dir=\"rtl\" ";
    }
    $header.= ">\n\t<head>\n";

    if ($meta)
        $header .= $meta;


    if ( !$embedded )
    {
        return $header;
    }

    global $embedded_headerfunc;

    if ( function_exists( $embedded_headerfunc ) )
        return $embedded_headerfunc($header);
}


function doHeader()
{
    echo getHeader();
}

/**
* This function returns the header for the printable survey
* @return String
*
*/
function getPrintableHeader()
{
    global $rooturl,$homeurl;
    $headelements = '
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <script type="text/javascript" src="'.App()->publicUrl . '/scripts/admin/'.'printablesurvey.js"></script>
    ';
    return $headelements;
}

// This function returns the Footer as result string
// If you want to echo the Footer use doFooter() !
function getFooter()
{
    global $embedded;
    if ( !$embedded )
    {
        return "\n\n\t</body>\n</html>\n";
    }

    global $embedded_footerfunc;

    if ( function_exists( $embedded_footerfunc ) )
        return $embedded_footerfunc();
}

function doFooter()
{
    echo getFooter();
}


/**
* This function fixes the group ID and type on all subquestions
* Optimized for minimum memory usage even on huge databases
*/
function fixSubquestions()
{
    $surveyidresult=Yii::app()->db->createCommand()
    ->select('sq.qid, q.gid , q.type ')
    ->from('{{questions}} sq')
    ->join('{{questions}} q','sq.parent_qid=q.qid')
    ->where('sq.parent_qid>0 AND (sq.gid!=q.gid or sq.type!=q.type)')
    ->limit(10000)
    ->query();
    $aRecords=$surveyidresult->readAll();
    while (count($aRecords)>0)
    {
        foreach($aRecords as $sv)
        {
            Yii::app()->db->createCommand("update {{questions}} set type='{$sv['type']}', gid={$sv['gid']} where qid={$sv['qid']}")->execute();
        }
        $surveyidresult=Yii::app()->db->createCommand()
        ->select('sq.qid, q.gid , q.type ')
        ->from('{{questions}} sq')
        ->join('{{questions}} q','sq.parent_qid=q.qid')
        ->where('sq.parent_qid>0 AND (sq.gid!=q.gid or sq.type!=q.type)')
        ->limit(10000)
        ->query();
        $aRecords=$surveyidresult->readAll();
    }

}

/**
* Must use ls_json_encode to json_encode content, otherwise LimeExpressionManager will think that the associative arrays are expressions and try to parse them.
*/
function ls_json_encode($content)
{
    if (is_string($content) && get_magic_quotes_gpc())
    {
        $content=stripslashes($content);
    }
    $ans = json_encode($content);
    $ans = str_replace(array('{','}'),array('{ ',' }'), $ans);
    return $ans;
}

/**
 * Decode a json string, sometimes needs stripslashes
 *
 * @param type $jsonString
 * @return type
 */
function json_decode_ls($jsonString)
{
    $decoded = json_decode($jsonString, true);

    if (is_null($decoded) && !empty($jsonString))
    {
        // probably we need stipslahes
        $decoded = json_decode(stripslashes($jsonString), true);
    }

    return $decoded;
}

/**
 * Return accepted codingsArray for importing files
 *
 * Used in vvimport
 * TODO : use in token and
 * @return array
 */
function aEncodingsArray()
    {

        return array(
        "armscii8" => gT("ARMSCII-8 Armenian"),
        "ascii" => gT("US ASCII"),
        "auto" => gT("Automatic"),
        "big5" => gT("Big5 Traditional Chinese"),
        "binary" => gT("Binary pseudo charset"),
        "cp1250" => gT("Windows Central European (Windows-1250)"),
        "cp1251" => gT("Windows Cyrillic (Windows-1251)"),
        "cp1256" => gT("Windows Arabic (Windows-1256)"),
        "cp1257" => gT("Windows Baltic (Windows-1257)"),
        "cp850" => gT("DOS West European (cp850)"),
        "cp852" => gT("DOS Central European (cp852)"),
        "cp866" => gT("DOS Cyrillic (cp866)"),
        "cp932" => gT("Windows-31J - SJIS for Windows Japanese (cp932)"),
        "dec8" => gT("DEC West European"),
        "eucjpms" => gT("UJIS for Windows Japanese"),
        "euckr" => gT("EUC-KR Korean"),
        "gb2312" => gT("GB2312 Simplified Chinese"),
        "gbk" => gT("GBK Simplified Chinese"),
        "geostd8" => gT("GEOSTD8 Georgian"),
        "greek" => gT("ISO 8859-7 Greek"),
        "hebrew" => gT("ISO 8859-8 Hebrew"),
        "hp8" => gT("HP West European"),
        "keybcs2" => gT("DOS Kamenicky Czech-Slovak (cp895)"),
        "koi8r" => gT("KOI8-R Relcom Russian"),
        "koi8u" => gT("KOI8-U Ukrainian"),
        "latin1" => gT("ISO 8859-1 West European (latin1)"),
        "latin2" => gT("ISO 8859-2 Central European (latin2)"),
        "latin5" => gT("ISO 8859-9 Turkish (latin5)"),
        "latin7" => gT("ISO 8859-13 Baltic (latin7)"),
        "macce" => gT("Mac Central European"),
        "macroman" => gT("Mac West European"),
        "sjis" => gT("Shift-JIS Japanese"),
        "swe7" => gT("7bit Swedish"),
        "tis620" => gT("TIS620 Thai"),
        "ucs2" => gT("UCS-2 Unicode"),
        "ujis" => gT("EUC-JP Japanese"),
        "utf8" => gT("UTF-8 Unicode"),
        );
    }



/**
* Ellipsize String
*
* This public static function will strip tags from a string, split it at its max_length and ellipsize
*
* @param    string        string to ellipsize
* @param    integer        max length of string
* @param    mixed        int (1|0) or float, .5, .2, etc for position to split
* @param    string        ellipsis ; Default '...'
* @return    string        ellipsized string
*/
function ellipsize($sString, $iMaxLength, $fPosition = 1, $sEllipsis = '&hellip;')
{
    // Strip tags
    $sString = trim(strip_tags($sString));
    // Is the string long enough to ellipsize?
    if (mb_strlen($sString,'UTF-8') <= $iMaxLength+3)
    {
        return $sString;
    }

    $iStrLen=mb_strlen($sString,'UTF-8');
    $sBegin = mb_substr($sString, 0, floor($iMaxLength * $fPosition),'UTF-8');
    $sEnd = mb_substr($sString,$iStrLen-($iMaxLength-mb_strlen($sBegin,'UTF-8')),$iStrLen,'UTF-8');
    return $sBegin.$sEllipsis.$sEnd;
}

/**
* This function returns the real IP address under all configurations
*
*/
function getIPAddress()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
        $sIPAddress=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
        $sIPAddress= $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    elseif (!empty($_SERVER['REMOTE_ADDR']))
    {
        $sIPAddress= $_SERVER['REMOTE_ADDR'];
    }
    else
    {
        $sIPAddress= '127.0.0.1';
    }
    if (!filter_var($sIPAddress, FILTER_VALIDATE_IP))
    {
        return 'Invalid';
    }
    else
    {
       return $sIPAddress;
    }
}


/**
* This function tries to find out a valid language code for the language of the browser used
* If it cannot find it it will return the default language from global settings
*
*/
function getBrowserLanguage()
{
    $sLanguage=Yii::app()->getRequest()->getPreferredLanguage();
    $aLanguages=\ls\helpers\SurveyTranslator::getLanguageData();
    if (!isset($aLanguages[$sLanguage]))
    {
        $sLanguage=str_replace('_','-',$sLanguage);
        if (!isset($aLanguages[$sLanguage]))
        {
            $sLanguage=substr($sLanguage,0,strpos($sLanguage,'-'));
            if (!isset($aLanguages[$sLanguage]))
            {
                $sLanguage=Yii::app()->getConfig('defaultlang');
            }
        }
    }
    return $sLanguage;
}

function array_diff_assoc_recursive($array1, $array2) {
    $difference=[];
    foreach($array1 as $key => $value) {
        if( is_array($value) ) {
            if( !isset($array2[$key]) || !is_array($array2[$key]) ) {
                $difference[$key] = $value;
            } else {
                $new_diff = array_diff_assoc_recursive($value, $array2[$key]);
                if( !empty($new_diff) )
                    $difference[$key] = $new_diff;
            }
        } else if( !array_key_exists($key,$array2) || $array2[$key] !== $value ) {
            $difference[$key] = $value;
        }
    }
    return $difference;
}


    function convertPHPSizeToBytes($sSize)
    {
        //This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
        $sSuffix = substr($sSize, -1);
        $iValue = substr($sSize, 0, -1);
        switch(strtoupper($sSuffix)){
        case 'P':
            $iValue *= 1024;
        case 'T':
            $iValue *= 1024;
        case 'G':
            $iValue *= 1024;
        case 'M':
            $iValue *= 1024;
        case 'K':
            $iValue *= 1024;
            break;
        }
        return $iValue;
    }


	function getMaximumFileUploadSize()
    {
        return min(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')));
    }
    function renderOldTemplate($fileName, $data = [], $replacements = [], SurveySession $session = null) {
        bP();
        try {
            echo \ls\helpers\Replacements::templatereplace(file_get_contents($fileName), $replacements, $data, null, $session ?: App()->surveySessionManager->current);
        } finally {
            eP();
        }

    }