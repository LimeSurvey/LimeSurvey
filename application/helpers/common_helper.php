<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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
Yii::import('application.helpers.sanitize_helper', true);


/**
 * Translation helper function
 * @param string $sToTranslate
 * @param string $sEscapeMode Valid values are html (this is the default, js and unescaped)
 * @param string $sLanguage
 * @return mixed|string
 */
function gT($sToTranslate, $sEscapeMode = 'html', $sLanguage = null)
{
    return quoteText(Yii::t('', $sToTranslate, array(), null, $sLanguage), $sEscapeMode);
}

/**
 * Translation helper function which outputs right away.
 * @param string $sToTranslate
 * @param string $sEscapeMode
 */
function eT($sToTranslate, $sEscapeMode = 'html')
{
    echo gT($sToTranslate, $sEscapeMode);
}

/**
 * Translation helper function for plural forms
 * @param string $sTextToTranslate
 * @param integer $iCount
 * @param string $sEscapeMode
 * @return string
 */
function ngT($sTextToTranslate, $iCount, $sEscapeMode = 'html')
{
    return quoteText(Yii::t('', $sTextToTranslate, $iCount), $sEscapeMode);
}

/**
 * Translation helper function for plural forms which outputs right away
 * @param string $sToTranslate
 * @param integer $iCount
 * @param string $sEscapeMode
 */
function neT($sToTranslate, $iCount, $sEscapeMode = 'html')
{
    echo ngT($sToTranslate, $iCount, $sEscapeMode);
}


/**
 * Quotes a translation according to purpose
 * if sEscapeMode is null, we use HTML method because probably we had to specify null as sEscapeMode upstream
 *
 * @param mixed $sText Text to quote
 * @param string $sEscapeMode Optional - One of the values 'html','js' or 'unescaped' - defaults to 'html'
 * @return mixed|string
 */
function quoteText($sText, $sEscapeMode = 'html')
{
    if ($sEscapeMode === null) {
            $sEscapeMode = 'html';
    }

    switch ($sEscapeMode) {
        case 'html':
            return HTMLEscape($sText);
        case 'js':
            return javascriptEscape($sText);
        case 'json':
            return jsonEscape($sText);
        case 'unescaped':
            return $sText;
        default:
            return "Unsupported EscapeMode in gT method";
    }
}

/**
* getQuestionTypeList() Returns list of question types available in LimeSurvey. Edit this if you are adding a new
*    question type
*
* @param string $SelectedCode Value of the Question Type (defaults to "T")
* @param string $ReturnType Type of output from this function (defaults to selector)
* @param string $language Language for translation
*
* @return array|string depending on $ReturnType param, returns a straight "array" of question types, or an <option></option> list
*
* Explanation of questiontype array:
*
* description : Question description
* subquestions : 0= Does not support subquestions x=Number of subquestion scales
* answerscales : 0= Does not need answers x=Number of answer scales (usually 1, but e.g. for dual scale question set to 2)
* assessable : 0=Does not support assessment values when editing answerd 1=Support assessment values
*/
function getQuestionTypeList($SelectedCode = "T", $ReturnType = "selector", $sLanguage=null)
{

    $qtypes = Question::typeList($sLanguage);

    if ($ReturnType == "array") {
        return $qtypes;
    }


    if ($ReturnType == "group") {
        $newqType = [];
        foreach ($qtypes as $qkey => $qtype) {
            $newqType[$qtype['group']][$qkey] = $qtype;
        }


        $qtypeselecter = "";
        foreach ($newqType as $group => $members) {
            $qtypeselecter .= '<optgroup label="'.$group.'">';
            foreach ($members as $TypeCode => $TypeProperties) {
                $qtypeselecter .= "<option value='$TypeCode'";
                if ($SelectedCode == $TypeCode) {
                    $qtypeselecter .= " selected='selected'";
                }
                $qtypeselecter .= ">{$TypeProperties['description']}</option>\n";
            }
            $qtypeselecter .= '</optgroup>';
        }

        return $qtypeselecter;
    };
    $qtypeselecter = "";
    foreach ($qtypes as $TypeCode => $TypeProperties) {
        $qtypeselecter .= "<option value='$TypeCode'";
        if ($SelectedCode == $TypeCode) {
            $qtypeselecter .= " selected='selected'";
        }
        $qtypeselecter .= ">{$TypeProperties['description']}</option>\n";
    }


    return $qtypeselecter;
}

/**
* isStandardTemplate returns true if a template is a standard template
* This function does not check if a template actually exists
*
* @param mixed $sTemplateName template name to look for
* @return bool True if standard template, otherwise false
*/
function isStandardTemplate($sTemplateName)
{
    return Template::isStandardTemplate($sTemplateName);
}

/**
* getSurveyList() Queries the database (survey table) for a list of existing surveys
*
* @param boolean $bReturnArray If set to true an array instead of an HTML option list is given back
* @return string|array This string is returned containing <option></option> formatted list of existing surveys
*
*/
function getSurveyList($bReturnArray = false)
{
    static $cached = null;
    $bCheckIntegrity = false;
    $timeadjust = getGlobalSetting('timeadjust');
    App()->setLanguage((isset(Yii::app()->session['adminlang']) ? Yii::app()->session['adminlang'] : 'en'));
    $surveynames = array();

    if (is_null($cached)) {
        $surveyidresult = Survey::model()
            ->permission(Yii::app()->user->getId())
            ->with('languagesettings')
            ->findAll();
        foreach ($surveyidresult as $result) {
            $surveynames[] = array_merge($result->attributes, $result->languagesettings[$result->language]->attributes);
        }

        usort($surveynames, function($a, $b)
        {
                return strcmp($a['surveyls_title'], $b['surveyls_title']);
        });
        $cached = $surveynames;
    } else {
        $surveynames = $cached;
    }
    $surveyselecter = "";
    if ($bReturnArray === true) {
        return $surveynames;
    }
    $activesurveys = '';
    $inactivesurveys = '';
    $expiredsurveys = '';
    foreach ($surveynames as $sv) {

        $surveylstitle = flattenText($sv['surveyls_title']);
        if (strlen($surveylstitle) > 70) {
            $surveylstitle = htmlspecialchars(mb_strcut(html_entity_decode($surveylstitle, ENT_QUOTES, 'UTF-8'), 0, 70, 'UTF-8'))."...";
        }

        if ($sv['active'] != 'Y') {
            $inactivesurveys .= "<option ";
            if (Yii::app()->user->getId() == $sv['owner_id']) {
                $inactivesurveys .= " class='mysurvey emphasis'";
            }
            $inactivesurveys .= " value='{$sv['sid']}'>{$surveylstitle}</option>\n";
        } elseif ($sv['expires'] != '' && $sv['expires'] < dateShift((string) date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)) {
            $expiredsurveys .= "<option ";
            if (Yii::app()->user->getId() == $sv['owner_id']) {
                $expiredsurveys .= " class='mysurvey emphasis'";
            }
            $expiredsurveys .= " value='{$sv['sid']}'>{$surveylstitle}</option>\n";
        } else {
            $activesurveys .= "<option ";
            if (Yii::app()->user->getId() == $sv['owner_id']) {
                $activesurveys .= " class='mysurvey emphasis'";
            }
            $activesurveys .= " value='{$sv['sid']}'>{$surveylstitle}</option>\n";
        }
    } // End Foreach

    //Only show each activesurvey group if there are some
    if ($activesurveys != '') {
        $surveyselecter .= "<optgroup label='".gT("Active")."' class='activesurveyselect'>\n";
        $surveyselecter .= $activesurveys."</optgroup>";
    }
    if ($expiredsurveys != '') {
        $surveyselecter .= "<optgroup label='".gT("Expired")."' class='expiredsurveyselect'>\n";
        $surveyselecter .= $expiredsurveys."</optgroup>";
    }
    if ($inactivesurveys != '') {
        $surveyselecter .= "<optgroup label='".gT("Inactive")."' class='inactivesurveyselect'>\n";
        $surveyselecter .= $inactivesurveys."</optgroup>";
    }
    $surveyselecter = "<option selected='selected' value=''>".gT("Please choose...")."</option>\n".$surveyselecter;
    return $surveyselecter;
}

function getTemplateList()
{
    return Template::getTemplateList();
}


/**
* getGidPrevious() returns the Gid of the group prior to the current active group
*
* @param integer $surveyid
* @param integer $gid
*
* @return integer|string The GID of the previous group or blank string if no group
*/
function getGidPrevious($surveyid, $gid)
{
    $surveyid = (int) $surveyid;
    $s_lang = Survey::model()->findByPk($surveyid)->language;
    $qresult = QuestionGroup::model()->findAllByAttributes(array('sid' => $surveyid, 'language' => $s_lang), array('order'=>'group_order'));

    $i = 0;
    $iPrev = -1;
    foreach ($qresult as $qrow) {
        $qrow = $qrow->attributes;
        if ($gid == $qrow['gid']) {$iPrev = $i - 1; }
        $i += 1;
    }

    if ($iPrev >= 0) {$GidPrev = $qresult[$iPrev]->gid; } else {$GidPrev = ""; }
    return $GidPrev;
}


/**
* getGidNext() returns the Gid of the group next to the current active group
*
* @param integer $surveyid
* @param integer $gid
*
* @return integer|string The Gid of the next group or blank string if no group
*/
function getGidNext($surveyid, $gid)
{
    $surveyid = (int) $surveyid;
    $s_lang = Survey::model()->findByPk($surveyid)->language;

    $qresult = QuestionGroup::model()->findAllByAttributes(array('sid' => $surveyid, 'language' => $s_lang), array('order'=>'group_order'));

    $i = 0;
    $iNext = 0;

    foreach ($qresult as $qrow) {
        $qrow = $qrow->attributes;
        if ($gid == $qrow['gid']) {
            $iNext = $i + 1;
        }
        $i += 1;
    }

    if ($iNext < count($qresult)) {
        $GidNext = $qresult[$iNext]->gid;
    } else {
        $GidNext = "";
    }
    return $GidNext;
}


/**
 * convertGETtoPOST a function to create a post Request from get parameters
 * !!! This functions result has to be wrappen in singlequotes!
 *
 * @param String $url | The complete url with all parameters
 * @return String | The onclick action for the element
 */
function convertGETtoPOST($url)
{
    // This function must be deprecated and replaced by $.post
    $url = preg_replace('/&amp;/i', '&', $url);
    $stack = explode('?', $url);
    $calledscript = array_shift($stack);
    $query = array_shift($stack);
    $aqueryitems = explode('&', $query);
    $postArray = [];
    $getArray = [];
    foreach ($aqueryitems as $queryitem) {
        $stack = explode('=', $queryitem);
        $paramname = array_shift($stack);
        $value = array_shift($stack);
        if(in_array($paramname,array(Yii::app()->getComponent('urlManager')->routeVar))) {
            $getArray[$paramname] = $value;
        } else {
            $postArray[$paramname] = $value;
        }
    }
    if(!empty($getArray)) {
        $calledscript = $calledscript."?".implode('&', array_map(
            function ($v, $k) {
                return $k.'='.$v;
            },
            $getArray, 
            array_keys($getArray)
        ));
    }
    $callscript = "window.LS.sendPost(\"".$calledscript."\",\"\",".json_encode($postArray).");";
    return $callscript;
}


/**
* This function calculates how much space is actually used by all files uploaded
* using the File Upload question type
*
* @returns integer Actual space used in MB
*/
function calculateTotalFileUploadUsage()
{
    global $uploaddir;
    $sQuery = 'select sid from {{surveys}}';
    $oResult = dbExecuteAssoc($sQuery); //checked
    $aRows = $oResult->readAll();
    $iTotalSize = 0.0;
    foreach ($aRows as $aRow) {
        $sFilesPath = $uploaddir.'/surveys/'.$aRow['sid'].'/files';
        if (file_exists($sFilesPath)) {
            $iTotalSize += (float) getDirectorySize($sFilesPath);
        }
    }
    return (float) $iTotalSize / 1024 / 1024;
}

/**
 * @param string $directory
 * @return int
 */
function getDirectorySize($directory)
{
    $size = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}



/**
 * Queries the database for the maximum sortorder of a group and returns the next higher one.
 *
 * @param integer $surveyid
 * @return int
 */
function getMaxGroupOrder($surveyid)
{
    $queryResult = QuestionGroup::model()->find(array(
        'condition' => 'sid = :sid',
        'params' => array(':sid' => $surveyid),
        'order' => 'group_order desc',
        'limit' => '1'
    ));

    $current_max = !is_null($queryResult) ? $queryResult->group_order : "";

    if ($current_max !== "") {
        $current_max += 1;
        return $current_max;
    } else {
        return 0;
    }
}


/**
* getGroupOrder($surveyid,$gid) queries the database for the sortorder of a group.
*
* @param mixed $surveyid
* @param mixed $gid
* @return mixed
*/
function getGroupOrder($surveyid, $gid)
{
    $s_lang = Survey::model()->findByPk($surveyid)->language;
    $grporder_result = QuestionGroup::model()->findByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'language' => $s_lang)); //Checked
    $grporder_row = $grporder_result->attributes;
    $group_order = $grporder_row['group_order'];
    if ($group_order == "") {
        return "0";
    } else {
        return $group_order;
    }
}

/**
* Queries the database for the maximum sort order of a question.
*
* @param integer $gid
* @param integer|null $surveyid
* @return integer
*/
function getMaxQuestionOrder($gid, $surveyid)
{
    $gid = (int) $gid;
    $s_lang = Survey::model()->findByPk($surveyid)->language;
    $max_sql = "SELECT max( question_order ) AS max FROM {{questions}} WHERE gid='{$gid}' AND language='{$s_lang}'";
    $max_result = Yii::app()->db->createCommand($max_sql)->query(); //Checked
    $maxrow = $max_result->read();
    $current_max = $maxrow['max'];
    if ($current_max == "") {
        return 0;
    } else {
        return (int) $current_max;
    }
}

/**
* getQuestionClass() returns a class name for a given question type to allow custom styling for each question type.
*
* @param string $input containing unique character representing each question type.
* @return string containing the class name for a given question type.
*/
function getQuestionClass($input)
{
    Question::getQuestionClass($input);
};

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
function setupColumns($columns, $answer_count, $wrapperclass = "", $itemclass = "")
{

    $column_style = Yii::app()->getConfig('column_style');
    if (!in_array($column_style, array('css', 'ul', 'table')) && !is_null($column_style)) {
        $column_style = 'ul';
    };
    if (!is_null($column_style) && $columns != 1) {
// Add a global class for all column
        $wrapperclass .= " colstyle-{$column_style}";
    }
    if ($columns < 2) {
        $column_style = null;
        $columns = 1;
    }

    if (($columns > $answer_count) && $answer_count > 0) {
        $columns = $answer_count;
    };


    $class_first = ' class="'.$wrapperclass.'"';
    if ($columns > 1 && !is_null($column_style)) {
        if ($column_style == 'ul') {
            $ul = '-ul';
        } else {
            $ul = '';
        }
        $class_first = ' class="'.$wrapperclass.' cols-'.$columns.$ul.' first"';
        $class = ' class="'.$wrapperclass.' cols-'.$columns.$ul.'"';
        $class_last_ul = ' class="'.$wrapperclass.' cols-'.$columns.$ul.' last"';
        $class_last_table = ' class="'.$wrapperclass.' cols-'.$columns.' last"';
    } else {
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
    ,'maxrows'  => ceil($answer_count / $columns) //Always rounds up to nearest whole number
    ,'cols'     => $columns
    );

    switch ($column_style) {
        case 'ul':  if ($columns > 1) {
                $wrapper['col-devide'] = "\n</ul>\n\n<ul$class>\n";
                $wrapper['col-devide-last'] = "\n</ul>\n\n<ul$class_last_ul>\n";
            }
            break;

        case 'table':   $table_cols = '';
            for ($cols = $columns; $cols > 0; --$cols) {
                switch ($cols) {
                    case $columns:  $table_cols .= "\t<col$class_first />\n";
                        break;
                    case 1:     $table_cols .= "\t<col$class_last_table />\n";
                        break;
                    default:    $table_cols .= "\t<col$class />\n";
                };
            };

            if ($columns > 1) {
                $wrapper['col-devide'] = "\t</ul>\n</td>\n\n<td>\n\t<ul>\n";
                $wrapper['col-devide-last'] = "\t</ul>\n</td>\n\n<td class=\"last\">\n\t<ul>\n";
            };
            $wrapper['whole-start'] = "\n<table$class>\n$table_cols\n\t<tbody>\n<tr>\n<td>\n\t<ul>\n";
            $wrapper['whole-end']   = "\t</ul>\n</td>\n</tr>\n\t</tbody>\n</table>\n";
            $wrapper['item-start']  = "<li class=\"{$itemclass}\">\n";
            $wrapper['item-end']    = "</li class=\"{$itemclass}\">\n";
    };

    return $wrapper;
};

function alternation($alternate = '', $type = 'col')
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
    if ($type == 'row') {
// Row is sub question OR Y Axis subquestion : it must be column for array by column
        $odd  = 'ls-odd';
        $even = 'ls-even';
    } else {
// cols is answers part OR X axis subquestion : it must the row in array by column
        $odd  = 'ls-col-odd';
        $even = 'ls-col-even';
    };
    if ($alternate == $odd) {
        $alternate = $even;
    } else {
        $alternate = $odd;
    };
    return $alternate;
}


/**
* longestString() returns the length of the longest string past to it.
* @peram string $new_string
* @peram integer $longest_length length of the (previously) longest string passed to it.
* @param integer $longest_length
* @return integer representing the length of the longest string passed (updated if $new_string was longer than $longest_length)
*
* usage should look like this: $longest_length = longestString( $new_string , $longest_length );
*
*/
function longestString($new_string, $longest_length)
{
    if ($longest_length < strlen(trim(strip_tags($new_string)))) {
        $longest_length = strlen(trim(strip_tags($new_string)));
    };
    return $longest_length;
};




/**
* getGroupList() queries the database for a list of all groups matching the current survey sid
*
*
* @param string $gid - the currently selected gid/group
* @param integer $surveyid
*
* @return string string is returned containing <option></option> formatted list of groups to current survey
*/
function getGroupList($gid, $surveyid)
{

    $groupselecter = "";
    $gid = sanitize_int($gid);
    $surveyid = sanitize_int($surveyid);
    if (!$surveyid) {$surveyid = returnGlobal('sid', true); }
    $s_lang = Survey::model()->findByPk($surveyid)->language;

    $gidquery = "SELECT gid, group_name FROM {{groups}} WHERE sid='{$surveyid}' AND  language='{$s_lang}' ORDER BY group_order";
    $gidresult = Yii::app()->db->createCommand($gidquery)->query(); //Checked
    foreach ($gidresult->readAll() as $gv) {
        $groupselecter .= "<option";
        if ($gv['gid'] == $gid) {$groupselecter .= " selected='selected'"; $gvexist = 1; }
        $groupselecter .= " value='".Yii::app()->getConfig('scriptname')."?sid=$surveyid&amp;gid=".$gv['gid']."'>".htmlspecialchars($gv['group_name'])."</option>\n";
    }
    if ($groupselecter) {
        if (!isset($gvexist)) {$groupselecter = "<option selected='selected'>".gT("Please choose...")."</option>\n".$groupselecter; } else {$groupselecter .= "<option value='".Yii::app()->getConfig('scriptname')."?sid=$surveyid&amp;gid='>".gT("None")."</option>\n"; }
    }
    return $groupselecter;
}


//FIXME rename and/or document this
function getGroupList3($gid, $surveyid)
{
    //
    $gid = sanitize_int($gid);
    $surveyid = sanitize_int($surveyid);

    if (!$surveyid) {$surveyid = returnGlobal('sid', true); }
    $groupselecter = "";
    $s_lang = Survey::model()->findByPk($surveyid)->language;


    //$gidquery = "SELECT gid, group_name FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='{$s_lang}' ORDER BY group_order";

    $gidresult = QuestionGroup::model()->findAllByAttributes(array('sid' => $surveyid, 'language' => $s_lang), array('order'=>'group_order'));

    foreach ($gidresult as $gv) {
        $gv = $gv->attributes;
        $groupselecter .= "<option";
        if ($gv['gid'] == $gid) {$groupselecter .= " selected='selected'"; }
        $groupselecter .= " value='".$gv['gid']."'>".htmlspecialchars($gv['group_name'])." (ID:".$gv['gid'].")</option>\n";
    }


    return $groupselecter;
}

/**
 * put your comment there...
 *
 * @param mixed $gid
 * @param mixed $language
 * @return string
 */
function getGroupListLang($gid, $language, $surveyid)
{
    $groupselecter = "";
    if (!$surveyid) {$surveyid = returnGlobal('sid', true); }

    $gidresult = QuestionGroup::model()->findAll(array('condition'=>'sid=:surveyid AND language=:language',
    'order'=>'group_order',
    'params'=>array(':surveyid'=>$surveyid, ':language'=>$language))); //Checked)
    foreach ($gidresult as $gv) {
        $gv = $gv->attributes;
        $groupselecter .= "<option";
        if ($gv['gid'] == $gid) {$groupselecter .= " selected='selected'"; $gvexist = 1; }
        $link = Yii::app()->getController()->createUrl("/admin/questiongroups/sa/view/surveyid/".$surveyid."/gid/".$gv['gid']);
        $groupselecter .= " value='{$link}'>";
        if (strip_tags($gv['group_name'])) {
            $groupselecter .= htmlspecialchars(strip_tags($gv['group_name']));
        } else {
            $groupselecter .= htmlspecialchars($gv['group_name']);
        }
        $groupselecter .= "</option>\n";
    }
    if ($groupselecter) {
        $link = Yii::app()->getController()->createUrl("/admin/survey/sa/view/surveyid/".$surveyid);
        if (!isset($gvexist)) {$groupselecter = "<option selected='selected'>".gT("Please choose...")."</option>\n".$groupselecter; } else {$groupselecter .= "<option value='{$link}'>".gT("None")."</option>\n"; }
    }
    return $groupselecter;
}


function getUserList($outputformat = 'fullinfoarray')
{
    if (!empty(Yii::app()->session['loginID'])) {
        $myuid = sanitize_int(Yii::app()->session['loginID']);
    }
    $usercontrolSameGroupPolicy = Yii::app()->getConfig('usercontrolSameGroupPolicy');
    if (!Permission::model()->hasGlobalPermission('superadmin', 'read') && isset($usercontrolSameGroupPolicy) &&
    $usercontrolSameGroupPolicy == true) {
        if (isset($myuid)) {
            $sDatabaseType = Yii::app()->db->getDriverName();
            if ($sDatabaseType == 'mssql' || $sDatabaseType == "sqlsrv" || $sDatabaseType == "dblib") {
                $sSelectFields = 'users_name,uid,email,full_name,parent_id,CAST(password as varchar) as password';
            } else {
                $sSelectFields = 'users_name,uid,email,full_name,parent_id,password';
            }

            // List users from same group as me + all my childs
            // a subselect is used here because MSSQL does not like to group by text
            // also Postgres does like this one better
            $uquery = " SELECT {$sSelectFields} from {{users}} where uid in (
                SELECT uid from {{user_in_groups}} where ugid in (
                    SELECT ugid from {{user_in_groups}} where uid={$myuid}
                    )
                )
            UNION
            SELECT {$sSelectFields} from {{users}} v where v.parent_id={$myuid}
            UNION
            SELECT {$sSelectFields} from {{users}} v where uid={$myuid}";

        } else {
            return array(); // Or die maybe
        }

    } else {
        $uquery = "SELECT * FROM {{users}} ORDER BY uid";
    }

    $uresult = Yii::app()->db->createCommand($uquery)->query()->readAll(); //Checked

    if (count($uresult) == 0 && !empty($myuid)) {
//user is not in a group and usercontrolSameGroupPolicy is activated - at least show his own userinfo
        $uquery = "SELECT u.* FROM {{users}} AS u WHERE u.uid=".$myuid;
        $uresult = Yii::app()->db->createCommand($uquery)->query()->readAll(); //Checked
    }

    $userlist = array();
    $userlist[0] = "Reserved for logged in user";
    foreach ($uresult as $srow) {
        if ($outputformat != 'onlyuidarray') {
            if ($srow['uid'] != Yii::app()->session['loginID']) {
                $userlist[] = array("user"=>$srow['users_name'], "uid"=>$srow['uid'], "email"=>$srow['email'], "password"=>$srow['password'], "full_name"=>$srow['full_name'], "parent_id"=>$srow['parent_id']);
            } else {
                $userlist[0] = array("user"=>$srow['users_name'], "uid"=>$srow['uid'], "email"=>$srow['email'], "password"=>$srow['password'], "full_name"=>$srow['full_name'], "parent_id"=>$srow['parent_id']);
            }
        } else {
            if ($srow['uid'] != Yii::app()->session['loginID']) {
                $userlist[] = $srow['uid'];
            } else {
                $userlist[0] = $srow['uid'];
            }
        }

    }
    return $userlist;
}


/**
* Gets all survey infos in one big array including the language specific settings
*
* @param integer $surveyid  The survey ID
* @param string $languagecode The language code - if not given the base language of the particular survey is used
* @return array|bool Returns array with survey info or false, if survey does not exist
*/
function getSurveyInfo($surveyid, $languagecode = '')
{
    static $staticSurveyInfo = array(); // Use some static
    $surveyid = sanitize_int($surveyid);
    $languagecode = sanitize_languagecode($languagecode);
    $thissurvey = false;
    $oSurvey = Survey::model()->findByPk($surveyid);
    // Do job only if this survey exist
    if (!$oSurvey) {
        return false;
    }
    // if no language code is set then get the base language one
    if ((!isset($languagecode) || $languagecode == '')) {
        $languagecode = Survey::model()->findByPk($surveyid)->language;
    }

    if (isset($staticSurveyInfo[$surveyid][$languagecode])) {
        $thissurvey = $staticSurveyInfo[$surveyid][$languagecode];
    } else {
        $result = SurveyLanguageSetting::model()->with('survey')->findByPk(array('surveyls_survey_id' => $surveyid, 'surveyls_language' => $languagecode));
        if (is_null($result)) {
            // When additional language was added, but not saved it does not exists
            // We should revert to the base language then
            $languagecode = Survey::model()->findByPk($surveyid)->language;
            $result = SurveyLanguageSetting::model()->with('survey')->findByPk(array('surveyls_survey_id' => $surveyid, 'surveyls_language' => $languagecode));
        }
        if ($result) {
            $thissurvey = array_merge($result->survey->attributes, $result->attributes);
            $thissurvey['name'] = $thissurvey['surveyls_title'];
            $thissurvey['description'] = $thissurvey['surveyls_description'];
            $thissurvey['welcome'] = $thissurvey['surveyls_welcometext'];
            $thissurvey['datasecurity_notice_label'] = $thissurvey['surveyls_policy_notice_label'];
            $thissurvey['datasecurity_error'] = $thissurvey['surveyls_policy_error'];
            $thissurvey['datasecurity_notice'] = $thissurvey['surveyls_policy_notice'];
            $thissurvey['templatedir'] = $thissurvey['template'];
            $thissurvey['adminname'] = $thissurvey['admin'];
            $thissurvey['tablename'] = $oSurvey->responsesTableName;
            $thissurvey['urldescrip'] = $thissurvey['surveyls_urldescription'];
            $thissurvey['url'] = $thissurvey['surveyls_url'];
            $thissurvey['expiry'] = $thissurvey['expires'];
            $thissurvey['email_invite_subj'] = $thissurvey['surveyls_email_invite_subj'];
            $thissurvey['email_invite'] = $thissurvey['surveyls_email_invite'];
            $thissurvey['email_remind_subj'] = $thissurvey['surveyls_email_remind_subj'];
            $thissurvey['email_remind'] = $thissurvey['surveyls_email_remind'];
            $thissurvey['email_confirm_subj'] = $thissurvey['surveyls_email_confirm_subj'];
            $thissurvey['email_confirm'] = $thissurvey['surveyls_email_confirm'];
            $thissurvey['email_register_subj'] = $thissurvey['surveyls_email_register_subj'];
            $thissurvey['email_register'] = $thissurvey['surveyls_email_register'];
            $thissurvey['attributedescriptions'] = $result->survey->tokenAttributes;
            $thissurvey['attributecaptions'] = $result->attributeCaptions;
            if (!isset($thissurvey['adminname'])) {$thissurvey['adminname'] = Yii::app()->getConfig('siteadminemail'); }
            if (!isset($thissurvey['adminemail'])) {$thissurvey['adminemail'] = Yii::app()->getConfig('siteadminname'); }
            if (!isset($thissurvey['urldescrip']) || $thissurvey['urldescrip'] == '') {$thissurvey['urldescrip'] = $thissurvey['surveyls_url']; }

            $thissurvey['owner_username'] = $result->survey->owner->users_name;

            $staticSurveyInfo[$surveyid][$languagecode] = $thissurvey;
        }

    }
    $thissurvey['oSurvey'] = $oSurvey;
    return $thissurvey;
}

/**
* Returns the default email template texts as array
*
* @param mixed $sLanguage Required language translationb object
* @param string $mode Escape mode for the translation function
* @return array
 * // TODO move to template model
*/
function templateDefaultTexts($sLanguage, $mode = 'html', $sNewlines = 'text')
{

    $aDefaultTexts = LsDefaultDataSets::getTemplateDefaultTexts($mode, $sLanguage);

    if ($sNewlines == 'html') {
        $aDefaultTexts = array_map('nl2br', $aDefaultTexts);
    }

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
    if (isset($a['group_order']) && isset($b['group_order'])) {
        $GroupResult = strnatcasecmp($a['group_order'], $b['group_order']);
    } else {
        $GroupResult = "";
    }
    if ($GroupResult == 0) {
        $TitleResult = strnatcasecmp($a["question_order"], $b["question_order"]);
        return $TitleResult;
    }
    return $GroupResult;
}


//FIXME insert UestionGroup model to here
/**
 * @param integer $sid
 * @param integer $gid
 * @param integer $shiftvalue
 */
function shiftOrderQuestions($sid, $gid, $shiftvalue) //Function shifts the sortorder for questions
{
    $sid = (int) $sid;
    $gid = (int) $gid;
    $shiftvalue = (int) $shiftvalue;

    $baselang = Survey::model()->findByPk($sid)->language;

    Question::model()->updateQuestionOrder($gid, $baselang, $shiftvalue);
}

function fixSortOrderGroups($surveyid) //Function rewrites the sortorder for groups
{
    $baselang = Survey::model()->findByPk($surveyid)->language;
    QuestionGroup::model()->updateGroupOrder($surveyid, $baselang);
}

/**
 * @param integer $iSurveyID
 * @param integer $qid
 * @param integer $newgid
 */
function fixMovedQuestionConditions($qid, $oldgid, $newgid, $iSurveyID = null) //Function rewrites the cfieldname for a question after group change
{
    if (!isset($iSurveyID)) {
            $iSurveyID = Yii::app()->getConfig('sid');
    }
    $qid = (int) $qid;
    $oldgid = (int) $oldgid;
    $newgid = (int) $newgid;
    Condition::model()->updateCFieldName($iSurveyID, $qid, $oldgid, $newgid);
    // TMSW Condition->Relevance:  Call LEM->ConvertConditionsToRelevance() when done
}


/**
 * This function returns POST/REQUEST vars, for some vars like SID and others they are also sanitized
 * TODO: extends Yii:getParam
 *
 * @param string $stringname
 * @param boolean $bRestrictToString
 * @return array|bool|mixed|int|null
 */
function returnGlobal($stringname, $bRestrictToString = false)
{
    $urlParam = Yii::app()->request->getParam($stringname);
    $aCookies = Yii::app()->request->getCookies();
    if (is_null($urlParam) && $stringname != 'sid') {
        if (isset($aCookies[$stringname])) {
            $urlParam = $aCookies[$stringname];
        }
    }
    $bUrlParamIsArray = is_array($urlParam); // Needed to array map or if $bRestrictToString
    if (!is_null($urlParam) && $stringname != '' && (!$bUrlParamIsArray || !$bRestrictToString)) {
        if ($stringname == 'sid' || $stringname == "gid" || $stringname == "oldqid" ||
        $stringname == "qid" || $stringname == "tid" ||
        $stringname == "lid" || $stringname == "ugid" ||
        $stringname == "thisstep" || $stringname == "scenario" ||
        $stringname == "cqid" || $stringname == "cid" ||
        $stringname == "qaid" || $stringname == "scid") {
            if ($bUrlParamIsArray) {
                return array_map("sanitize_int", $urlParam);
            } else {
                return sanitize_int($urlParam);
            }
        } elseif ($stringname == "lang" || $stringname == "adminlang") {
            if ($bUrlParamIsArray) {
                return array_map("sanitize_languagecode", $urlParam);
            } else {
                return sanitize_languagecode($urlParam);
            }
        } elseif ($stringname == "htmleditormode" ||
        $stringname == "subaction" ||
        $stringname == "questionselectormode" ||
        $stringname == "templateeditormode"
        ) {
            if ($bUrlParamIsArray) {
                return array_map("sanitize_paranoid_string", $urlParam);
            } else {
                return sanitize_paranoid_string($urlParam);
            }
        } elseif ($stringname == "cquestions") {
            if ($bUrlParamIsArray) {
                return array_map("sanitize_cquestions", $urlParam);
            } else {
                return sanitize_cquestions($urlParam);
            }
        }
        return $urlParam;
    } else {
        return null;
    }
}


function sendCacheHeaders()
{
    if (!headers_sent()) {
        if (Yii::app()->getConfig('x_frame_options', 'allow') == 'sameorigin') {
            header('X-Frame-Options: SAMEORIGIN');
        }
        header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"'); // this line lets IE7 run LimeSurvey in an iframe
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT"); // always modified
        header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: text/html; charset=utf-8');
    }
}

/**
* @param integer $iSurveyID The Survey ID
* @param string $sFieldCode Field code of the particular field
* @param string $sValue The stored response value
* @param string $sLanguage Initialized limesurvey_lang object for the resulting response data
* @return string
*/
function getExtendedAnswer($iSurveyID, $sFieldCode, $sValue, $sLanguage)
{

    if ($sValue == null || $sValue == '') {
        return '';
    }
    $survey = Survey::model()->findByPk($iSurveyID);
    //Fieldcode used to determine question, $sValue used to match against answer code
    //Returns NULL if question type does not suit
    if (strpos($sFieldCode, "{$iSurveyID}X") === 0) {
//Only check if it looks like a real fieldcode
        $fieldmap = createFieldMap($survey, 'short', false, false, $sLanguage);
        if (isset($fieldmap[$sFieldCode])) {
            $fields = $fieldmap[$sFieldCode];
        } else {
            return '';
        }

        // If it is a comment field there is nothing to convert here
        if ($fields['aid'] == 'comment') {
            return $sValue;
        }

        //Find out the question type
        $this_type = $fields['type'];
        switch ($this_type) {
            case 'D':
                if (trim($sValue) != '') {
                    $qidattributes = QuestionAttribute::model()->getQuestionAttributes($fields['qid']);
                    $dateformatdetails = getDateFormatDataForQID($qidattributes, $iSurveyID);
                    $sValue = convertDateTimeFormat($sValue, "Y-m-d H:i:s", $dateformatdetails['phpdate']);
                }
                break;
            case 'K':
            case 'N':
                if (trim($sValue) != '') {
                    if (strpos($sValue, ".") !== false) {
                        $sValue = rtrim(rtrim($sValue, "0"), ".");
                    }
                }
                break;
            case "L":
            case "!":
            case "O":
            case "^":
            case "I":
            case "R":
                $result = Answer::model()->getAnswerFromCode($fields['qid'], $sValue, $sLanguage);
                foreach ($result as $row) {
                    $this_answer = $row['answer'];
                } // while
                if ($sValue == "-oth-") {
                    $this_answer = gT("Other", null, $sLanguage);
                }
                break;
            case "M":
            case "J":
            case "P":
            switch ($sValue) {
                case "Y": $this_answer = gT("Yes", null, $sLanguage); break;
            }
            break;
            case "Y":
            switch ($sValue) {
                case "Y": $this_answer = gT("Yes", null, $sLanguage); break;
                case "N": $this_answer = gT("No", null, $sLanguage); break;
                default: $this_answer = gT("No answer", null, $sLanguage);
            }
            break;
            case "G":
            switch ($sValue) {
                case "M": $this_answer = gT("Male", null, $sLanguage); break;
                case "F": $this_answer = gT("Female", null, $sLanguage); break;
                default: $this_answer = gT("No answer", null, $sLanguage);
            }
            break;
            case "C":
            switch ($sValue) {
                case "Y": $this_answer = gT("Yes", null, $sLanguage); break;
                case "N": $this_answer = gT("No", null, $sLanguage); break;
                case "U": $this_answer = gT("Uncertain", null, $sLanguage); break;
            }
            break;
            case "E":
            switch ($sValue) {
                case "I": $this_answer = gT("Increase", null, $sLanguage); break;
                case "D": $this_answer = gT("Decrease", null, $sLanguage); break;
                case "S": $this_answer = gT("Same", null, $sLanguage); break;
            }
            break;
            case "F":
            case "H":
            case "1":
                if (isset($fields['scale_id'])) {
                    $iScaleID = $fields['scale_id'];
                } else {
                    $iScaleID = 0;
                }
                $result = Answer::model()->getAnswerFromCode($fields['qid'], $sValue, $sLanguage, $iScaleID);
                foreach ($result as $row) {
                    $this_answer = $row['answer'];
                } // while
                if ($sValue == "-oth-") {
                    $this_answer = gT("Other", null, $sLanguage);
                }
                break;
            case "|": //File upload
                if (substr($sFieldCode, -9) != 'filecount') {
                    //Show the filename, size, title and comment -- no link!
                    $files = json_decode($sValue, true);
                    $sValue = '';
                    if (is_array($files)) {
                        foreach ($files as $file) {
                            if (!isset($file['title'])) {
                                $file['title'] = '';
                            }
                            if (!isset($file['comment'])) {
                                $file['comment'] = '';
                            }
                            $sValue .= rawurldecode($file['name']).
                            ' ('.round($file['size']).'KB) '.
                            strip_tags($file['title']);
                            if (trim(strip_tags($file['comment'])) != "") {
                                $sValue .= ' - '.strip_tags($file['comment']);
                            }

                        }
                    }
                }
                break;
            default:
                ;
        } // switch
    }
    switch ($sFieldCode) {
        case 'submitdate':
        case 'startdate':
        case 'datestamp':
            if (trim($sValue) != '') {
                $dateformatdetails = getDateFormatDataForQID(null, $iSurveyID);
                $sValue = convertDateTimeFormat($sValue, "Y-m-d H:i:s", $dateformatdetails['phpdate'].' H:i:s');
            }
            break;
    }
    if (isset($this_answer)) {
        return $this_answer." [$sValue]";
    } else {
        return $sValue;
    }
}

/**
* Validate an email address - also supports IDN email addresses
* @returns True/false for valid/invalid
*
* @param mixed $sEmailAddress  Email address to check
*/
function validateEmailAddress($sEmailAddress)
{
    require_once(APPPATH.'third_party/idna-convert/idna_convert.class.php');
    $oIdnConverter = new idna_convert();
    $sEmailAddress = $oIdnConverter->encode($sEmailAddress);
    $bResult = filter_var($sEmailAddress, FILTER_VALIDATE_EMAIL);
    if ($bResult !== false) {
        return true;
    }
    return false;
}

/**
* Validate an list of email addresses - either as array or as semicolon-limited text
* @return string List with valid email addresses - invalid email addresses are filtered - false if none of the email addresses are valid
*
* @param string $aEmailAddressList  Email address to check
* @returns array
*/
function validateEmailAddresses($aEmailAddressList)
{
    $aOutList = [];
    if (!is_array($aEmailAddressList)) {
        $aEmailAddressList = explode(';', $aEmailAddressList);
    }

    foreach ($aEmailAddressList as $sEmailAddress) {
        $sEmailAddress = trim($sEmailAddress);
        if (validateEmailAddress($sEmailAddress)) {
            $aOutList[] = $sEmailAddress;
        }
    }
    return $aOutList;
}

/**
 * This functions generates a a summary containing the SGQA for questions of a survey, enriched with options per question
 * It can be used for the generation of statistics. Derived from Statistics_userController
 * @param int $iSurveyID Id of the Survey in question
 * @param array $aFilters an array which is the result of a query in Questions model
 * @param string $sLanguage
 * @return array The summary
 */
function createCompleteSGQA($iSurveyID, $aFilters, $sLanguage)
{
    $allfields = [];
    foreach ($aFilters as $flt) {
        Yii::app()->loadHelper("surveytranslator");
        $myfield = "{$iSurveyID}X{$flt['gid']}X{$flt['qid']}";
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $aAdditionalLanguages = array_filter(explode(" ", $oSurvey->additional_languages));
        if (is_null($sLanguage) || !in_array($sLanguage, $aAdditionalLanguages)) {
            $sLanguage = $oSurvey->language;
        }
        switch ($flt['type']) {
            case "K": // Multiple Numerical
            case "Q": // Multiple Short Text
                //get answers
                $result = Question::model()->getQuestionsForStatistics('title as code, question as answer', "parent_qid=$flt[qid] AND language = '{$sLanguage}'", 'question_order');

                //go through all the (multiple) answers
                foreach ($result as $row) {
                    $myfield2 = $flt['type'].$myfield.reset($row);
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
                foreach ($result as $row) {
                    $myfield2 = $myfield.reset($row);
                    $allfields[] = $myfield2;
                }
                break;
                // all "free text" types (T, U, S)  get the same prefix ("T")
            case "T": // Long free text
            case "U": // Huge free text
            case "S": // Short free text
                $myfield = "T$myfield";
                $allfields[] = $myfield;
                break;
            case ";":  //ARRAY (Multi Flex) (Text)
            case ":":  //ARRAY (Multi Flex) (Numbers)
                $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[qid] AND language = '{$sLanguage}' AND scale_id = 0", 'question_order');

                foreach ($result as $row) {
                    $fresult = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[qid] AND language = '{$sLanguage}' AND scale_id = 1", 'question_order');
                    foreach ($fresult as $frow) {
                        $myfield2 = $myfield.reset($row)."_".$frow['title'];
                        $allfields[] = $myfield2;
                    }
                }
                break;
            case "R": //RANKING
                //get some answers
                $result = Answer::model()->getQuestionsForStatistics('code, answer', "qid=$flt[qid] AND language = '{$sLanguage}'", 'sortorder, answer');
                //get number of answers
                //loop through all answers. if there are 3 items to rate there will be 3 statistics
                $i = 0;
                foreach ($result as $row) {
                    $i++;
                    $myfield2 = "R".$myfield.$i."-".strlen($i);
                    $allfields[] = $myfield2;
                }

                break;
                //Boilerplate questions are only used to put some text between other questions -> no analysis needed
            case "X":  //This is a boilerplate question and it has no business in this script
                break;
            case "1": // MULTI SCALE
                //get answers
                $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[qid] AND language = '{$sLanguage}'", 'question_order');
                //loop through answers
                foreach ($result as $row) {
                    //----------------- LABEL 1 ---------------------
                    $myfield2 = $myfield.reset($row)."#0";
                    $allfields[] = $myfield2;
                    //----------------- LABEL 2 ---------------------
                    $myfield2 = $myfield.reset($row)."#1";
                    $allfields[] = $myfield2;
                }   //end WHILE -> loop through all answers
                break;

            case "P":  //P - Multiple choice with comments
            case "M":  //M - Multiple choice
            case "N":  //N - Numerical input
            case "D":  //D - Date
                $myfield2 = $flt['type'].$myfield;
                $allfields[] = $myfield2;
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
* @param Survey $survey
* @param string $style 'short' (default) or 'full' - full creates extra information like default values
* @param boolean $force_refresh - Forces to really refresh the array, not just take the session copy
* @param bool|int $questionid Limit to a certain qid only (for question preview) - default is false
* @param string $sLanguage The language to use
* @param array $aDuplicateQIDs
* @return array
*/
function createFieldMap($survey, $style = 'short', $force_refresh = false, $questionid = false, $sLanguage = '', &$aDuplicateQIDs = array())
{

    $sLanguage = sanitize_languagecode($sLanguage);
    $surveyid = $survey->sid;
    //checks to see if fieldmap has already been built for this page.
    if (isset(Yii::app()->session['fieldmap-'.$surveyid.$sLanguage]) && !$force_refresh && $questionid === false) {
        return Yii::app()->session['fieldmap-'.$surveyid.$sLanguage];
    }
    /* Check if $sLanguage is a survey valid language (else $fieldmap is empty) */
    if ($sLanguage == '' || !in_array($sLanguage, $survey->allLanguages)) {
        $sLanguage = $survey->language;
    }
    $fieldmap = [];
    $fieldmap["id"] = array("fieldname"=>"id", 'sid'=>$surveyid, 'type'=>"id", "gid"=>"", "qid"=>"", "aid"=>"");
    if ($style == "full") {
        $fieldmap["id"]['title'] = "";
        $fieldmap["id"]['question'] = gT("Response ID");
        $fieldmap["id"]['group_name'] = "";
    }

    $fieldmap["submitdate"] = array("fieldname"=>"submitdate", 'type'=>"submitdate", 'sid'=>$surveyid, "gid"=>"", "qid"=>"", "aid"=>"");
    if ($style == "full") {
        $fieldmap["submitdate"]['title'] = "";
        $fieldmap["submitdate"]['question'] = gT("Date submitted");
        $fieldmap["submitdate"]['group_name'] = "";
    }

    $fieldmap["lastpage"] = array("fieldname"=>"lastpage", 'sid'=>$surveyid, 'type'=>"lastpage", "gid"=>"", "qid"=>"", "aid"=>"");
    if ($style == "full") {
        $fieldmap["lastpage"]['title'] = "";
        $fieldmap["lastpage"]['question'] = gT("Last page");
        $fieldmap["lastpage"]['group_name'] = "";
    }

    $fieldmap["startlanguage"] = array("fieldname"=>"startlanguage", 'sid'=>$surveyid, 'type'=>"startlanguage", "gid"=>"", "qid"=>"", "aid"=>"");
    if ($style == "full") {
        $fieldmap["startlanguage"]['title'] = "";
        $fieldmap["startlanguage"]['question'] = gT("Start language");
        $fieldmap["startlanguage"]['group_name'] = "";
    }

    $fieldmap['seed'] = array('fieldname' => 'seed', 'sid' => $surveyid, 'type' => 'seed', 'gid' => '', 'qid' => '', 'aid' => '');
    if ($style == 'full') {
        $fieldmap["seed"]['title'] = "";
        $fieldmap["seed"]['question'] = gT("Seed");
        $fieldmap["seed"]['group_name'] = "";
    }

    //Check for any additional fields for this survey and create necessary fields (token and datestamp and ipaddr)
    $prow = $survey->getAttributes(); //Checked

    if ($prow['anonymized'] == "N" && $survey->hasTokensTable) {
        $fieldmap["token"] = array("fieldname"=>"token", 'sid'=>$surveyid, 'type'=>"token", "gid"=>"", "qid"=>"", "aid"=>"");
        if ($style == "full") {
            $fieldmap["token"]['title'] = "";
            $fieldmap["token"]['question'] = gT("Token");
            $fieldmap["token"]['group_name'] = "";
        }
    }
    if ($prow['datestamp'] == "Y") {
        $fieldmap["startdate"] = array("fieldname"=>"startdate",
        'type'=>"startdate",
        'sid'=>$surveyid,
        "gid"=>"",
        "qid"=>"",
        "aid"=>"");
        if ($style == "full") {
            $fieldmap["startdate"]['title'] = "";
            $fieldmap["startdate"]['question'] = gT("Date started");
            $fieldmap["startdate"]['group_name'] = "";
        }

        $fieldmap["datestamp"] = array("fieldname"=>"datestamp",
        'type'=>"datestamp",
        'sid'=>$surveyid,
        "gid"=>"",
        "qid"=>"",
        "aid"=>"");
        if ($style == "full") {
            $fieldmap["datestamp"]['title'] = "";
            $fieldmap["datestamp"]['question'] = gT("Date last action");
            $fieldmap["datestamp"]['group_name'] = "";
        }

    }
    if ($prow['ipaddr'] == "Y") {
        $fieldmap["ipaddr"] = array("fieldname"=>"ipaddr",
        'type'=>"ipaddress",
        'sid'=>$surveyid,
        "gid"=>"",
        "qid"=>"",
        "aid"=>"");
        if ($style == "full") {
            $fieldmap["ipaddr"]['title'] = "";
            $fieldmap["ipaddr"]['question'] = gT("IP address");
            $fieldmap["ipaddr"]['group_name'] = "";
        }
    }
    // Add 'refurl' to fieldmap.
    if ($prow['refurl'] == "Y") {
        $fieldmap["refurl"] = array("fieldname"=>"refurl", 'type'=>"url", 'sid'=>$surveyid, "gid"=>"", "qid"=>"", "aid"=>"");
        if ($style == "full") {
            $fieldmap["refurl"]['title'] = "";
            $fieldmap["refurl"]['question'] = gT("Referrer URL");
            $fieldmap["refurl"]['group_name'] = "";
        }
    }

    $sOldLanguage = App()->language;
    App()->setLanguage($sLanguage);
    // Collect all default values once so don't need separate query for each question with defaults
    // First collect language specific defaults
    $defaultsQuery = "SELECT a.qid, a.sqid, a.scale_id, a.specialtype, a.defaultvalue"
    . " FROM {{defaultvalues}} as a, {{questions}} as b"
    . " WHERE a.qid = b.qid"
    . " AND a.language = b.language"
    . " AND a.language = '{$sLanguage}'"
    . " AND b.same_default=0"
    . " AND b.sid = ".$surveyid;
    $defaultResults = Yii::app()->db->createCommand($defaultsQuery)->queryAll();

    $defaultValues = array(); // indexed by question then subquestion
    foreach ($defaultResults as $dv) {
        if ($dv['specialtype'] != '') {
            $sq = $dv['specialtype'];
        } else {
            $sq = $dv['sqid'];
        }
        $defaultValues[$dv['qid'].'~'.$sq] = $dv['defaultvalue'];
    }

    // Now overwrite language-specific defaults (if any) base language values for each question that uses same_defaults=1
    $baseLanguage = $survey->language;
    $defaultsQuery = "SELECT a.qid, a.sqid, a.scale_id, a.specialtype, a.defaultvalue"
    . " FROM {{defaultvalues}} as a, {{questions}} as b"
    . " WHERE a.qid = b.qid"
    . " AND a.language = b.language"
    . " AND a.language = '{$baseLanguage}'"
    . " AND b.same_default=1"
    . " AND b.sid = ".$surveyid;
    $defaultResults = Yii::app()->db->createCommand($defaultsQuery)->queryAll();

    foreach ($defaultResults as $dv) {
        if ($dv['specialtype'] != '') {
            $sq = $dv['specialtype'];
        } else {
            $sq = $dv['sqid'];
        }
        $defaultValues[$dv['qid'].'~'.$sq] = $dv['defaultvalue'];
    }
    $qtypes = getQuestionTypeList('', 'array');

    // Main query
    $aquery = "SELECT * "
    ." FROM {{questions}} as questions, {{groups}} as question_groups"
    ." WHERE questions.gid=question_groups.gid AND "
    ." questions.sid=$surveyid AND "
    ." questions.language='{$sLanguage}' AND "
    ." questions.parent_qid=0 AND "
    ." question_groups.language='{$sLanguage}' ";
    if ($questionid !== false) {
        $aquery .= " and questions.qid={$questionid} ";
    }
    $aquery .= " ORDER BY group_order, question_order";
    /** @var Question[] $questions */
    $questions = Yii::app()->db->createCommand($aquery)->queryAll();
    $questionSeq = -1; // this is incremental question sequence across all groups
    $groupSeq = -1;
    $_groupOrder = -1;

    foreach ($questions as $arow) {
//With each question, create the appropriate field(s))
        ++$questionSeq;

        // fix fact taht group_order may have gaps
        if ($_groupOrder != $arow['group_order']) {
            $_groupOrder = $arow['group_order'];
            ++$groupSeq;
        }
        // Condition indicators are obsolete with EM.  However, they are so tightly coupled into LS code that easider to just set values to 'N' for now and refactor later.
        $conditions = 'N';
        $usedinconditions = 'N';

        // Field identifier
        // GXQXSXA
        // G=Group  Q=Question S=Subquestion A=Answer Option
        // If S or A don't exist then set it to 0
        // Implicit (subqestion intermal to a question type ) or explicit qubquestions/answer count starts at 1

        // Types "L", "!", "O", "D", "G", "N", "X", "Y", "5", "S", "T", "U"
        $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}";

        if ($qtypes[$arow['type']]['subquestions'] == 0 && $arow['type'] != "R" && $arow['type'] != "|") {
            if (isset($fieldmap[$fieldname])) {
                $aDuplicateQIDs[$arow['qid']] = array('fieldname'=>$fieldname, 'question'=>$arow['question'], 'gid'=>$arow['gid']);
            }

            $fieldmap[$fieldname] = array("fieldname"=>$fieldname, 'type'=>"{$arow['type']}", 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>"");

            if ($style == "full") {
                $fieldmap[$fieldname]['title'] = $arow['title'];
                $fieldmap[$fieldname]['question'] = $arow['question'];
                $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                $fieldmap[$fieldname]['hasconditions'] = $conditions;
                $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                if (isset($defaultValues[$arow['qid'].'~0'])) {
                    $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$arow['qid'].'~0'];
                }
            }
            switch ($arow['type']) {
                case "L":  //RADIO LIST
                case "!":  //DROPDOWN LIST
                    if ($arow['other'] == "Y") {
                        $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$arow['qid']] = array('fieldname'=>$fieldname, 'question'=>$arow['question'], 'gid'=>$arow['gid']);
                        }

                        $fieldmap[$fieldname] = array("fieldname"=>$fieldname,
                        'type'=>$arow['type'],
                        'sid'=>$surveyid,
                        "gid"=>$arow['gid'],
                        "qid"=>$arow['qid'],
                        "aid"=>"other");
                        // dgk bug fix line above. aid should be set to "other" for export to append to the field name in the header line.
                        if ($style == "full") {
                            $fieldmap[$fieldname]['title'] = $arow['title'];
                            $fieldmap[$fieldname]['question'] = $arow['question'];
                            $fieldmap[$fieldname]['subquestion'] = gT("Other");
                            $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                            $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                            $fieldmap[$fieldname]['hasconditions'] = $conditions;
                            $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                            $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                            $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                            if (isset($defaultValues[$arow['qid'].'~other'])) {
                                $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$arow['qid'].'~other'];
                            }
                        }
                    }
                    break;
                case "O": //DROPDOWN LIST WITH COMMENT
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}comment";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = array('fieldname'=>$fieldname, 'question'=>$arow['question'], 'gid'=>$arow['gid']);
                    }

                    $fieldmap[$fieldname] = array("fieldname"=>$fieldname,
                    'type'=>$arow['type'],
                    'sid'=>$surveyid,
                    "gid"=>$arow['gid'],
                    "qid"=>$arow['qid'],
                    "aid"=>"comment");
                    // dgk bug fix line below. aid should be set to "comment" for export to append to the field name in the header line. Also needed set the type element correctly.
                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion'] = gT("Comment");
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    }
                    break;
            }
        }
        // For Multi flexi question types
        elseif ($qtypes[$arow['type']]['subquestions'] == 2 && $qtypes[$arow['type']]['answerscales'] == 0) {
            //MULTI FLEXI
            $abrows = getSubQuestions($surveyid, $arow['qid'], $sLanguage);
            //Now first process scale=1
            $answerset = array();
            $answerList = array();
            foreach ($abrows as $key=>$abrow) {
                if ($abrow['scale_id'] == 1) {
                    $answerset[] = $abrow;
                    $answerList[] = array(
                    'code'=>$abrow['title'],
                    'answer'=>$abrow['question'],
                    );
                    unset($abrows[$key]);
                }
            }
            reset($abrows);
            foreach ($abrows as $abrow) {
                foreach ($answerset as $answer) {
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}_{$answer['title']}";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = array('fieldname'=>$fieldname, 'question'=>$arow['question'], 'gid'=>$arow['gid']);
                    }
                    $fieldmap[$fieldname] = array("fieldname"=>$fieldname,
                    'type'=>$arow['type'],
                    'sid'=>$surveyid,
                    "gid"=>$arow['gid'],
                    "qid"=>$arow['qid'],
                    "aid"=>$abrow['title']."_".$answer['title'],
                    "sqid"=>$abrow['qid']);
                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion1'] = $abrow['question'];
                        $fieldmap[$fieldname]['subquestion2'] = $answer['question'];
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                        $fieldmap[$fieldname]['preg'] = $arow['preg'];
                        $fieldmap[$fieldname]['answerList'] = $answerList;
                        $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                    }
                }
            }
            unset($answerset);
        } elseif ($arow['type'] == "1") {
            $abrows = getSubQuestions($surveyid, $arow['qid'], $sLanguage);
            foreach ($abrows as $abrow) {
                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}#0";
                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = array('fieldname'=>$fieldname, 'question'=>$arow['question'], 'gid'=>$arow['gid']);
                }
                $fieldmap[$fieldname] = array("fieldname"=>$fieldname, 'type'=>$arow['type'], 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$abrow['title'], "scale_id"=>0);
                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['scale'] = gT('Scale 1');
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                }

                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}#1";
                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = array('fieldname'=>$fieldname, 'question'=>$arow['question'], 'gid'=>$arow['gid']);
                }
                $fieldmap[$fieldname] = array("fieldname"=>$fieldname, 'type'=>$arow['type'], 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$abrow['title'], "scale_id"=>1);
                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['scale'] = gT('Scale 2');
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    // TODO SQrelevance for different scales? $fieldmap[$fieldname]['SQrelevance']=$abrow['relevance'];
                }
            }
        } elseif ($arow['type'] == "R") {
            // Sub question by answer number OR attribute
            $answersCount = intval(Answer::model()->countByAttributes(array('qid' => $arow['qid'], 'language' => $sLanguage)));
            $maxDbAnswer = QuestionAttribute::model()->find("qid = :qid AND attribute = 'max_subquestions'", array(':qid' => $arow['qid']));
            $columnsCount = (!$maxDbAnswer || intval($maxDbAnswer->value) < 1) ? $answersCount : intval($maxDbAnswer->value);
            $columnsCount = min($columnsCount,$answersCount); // Can not be upper than current answers #14899
            for ($i = 1; $i <= $columnsCount; $i++) {
                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}$i";
                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = array('fieldname'=>$fieldname, 'question'=>$arow['question'], 'gid'=>$arow['gid']);
                }
                $fieldmap[$fieldname] = array("fieldname"=>$fieldname, 'type'=>$arow['type'], 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$i);
                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = sprintf(gT('Rank %s'), $i);
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                }
            }
        } elseif ($arow['type'] == "|") {
            $qidattributes = QuestionAttribute::model()->getQuestionAttributes($arow['qid']);
                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}";
                $fieldmap[$fieldname] = array("fieldname"=>$fieldname,
                'type'=>$arow['type'],
                'sid'=>$surveyid,
                "gid"=>$arow['gid'],
                "qid"=>$arow['qid'],
                "aid"=>''
                );
                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['max_files'] = $qidattributes['max_num_of_files'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                }
                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}"."_filecount";
                $fieldmap[$fieldname] = array("fieldname"=>$fieldname,
                'type'=>$arow['type'],
                'sid'=>$surveyid,
                "gid"=>$arow['gid'],
                "qid"=>$arow['qid'],
                "aid"=>"filecount"
                );
                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = "filecount - ".$arow['question'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                }
        } else {
// Question types with subquestions and one answer per subquestion  (M/A/B/C/E/F/H/P)
            //MULTI ENTRY
            $abrows = getSubQuestions($surveyid, $arow['qid'], $sLanguage);
            foreach ($abrows as $abrow) {
                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}";

                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = array('fieldname'=>$fieldname, 'question'=>$arow['question'], 'gid'=>$arow['gid']);
                }
                $fieldmap[$fieldname] = array("fieldname"=>$fieldname,
                'type'=>$arow['type'],
                'sid'=>$surveyid,
                'gid'=>$arow['gid'],
                'qid'=>$arow['qid'],
                'aid'=>$abrow['title'],
                'sqid'=>$abrow['qid']);
                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    $fieldmap[$fieldname]['preg'] = $arow['preg'];
                    // get SQrelevance from DB
                    $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                    if (isset($defaultValues[$arow['qid'].'~'.$abrow['qid']])) {
                        $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$arow['qid'].'~'.$abrow['qid']];
                    }
                }
                if ($arow['type'] == "P") {
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}comment";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = array('fieldname'=>$fieldname, 'question'=>$arow['question'], 'gid'=>$arow['gid']);
                    }
                    $fieldmap[$fieldname] = array("fieldname"=>$fieldname, 'type'=>$arow['type'], 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$abrow['title']."comment");
                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion'] = gT('Comment');
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    }
                }
            }
            if ($arow['other'] == "Y" && ($arow['type'] == "M" || $arow['type'] == "P")) {
                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other";
                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = array('fieldname'=>$fieldname, 'question'=>$arow['question'], 'gid'=>$arow['gid']);
                }
                $fieldmap[$fieldname] = array("fieldname"=>$fieldname, 'type'=>$arow['type'], 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>"other");
                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = gT('Other');
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    $fieldmap[$fieldname]['other'] = $arow['other'];
                }
                if ($arow['type'] == "P") {
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}othercomment";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = array('fieldname'=>$fieldname, 'question'=>$arow['question'], 'gid'=>$arow['gid']);
                    }
                    $fieldmap[$fieldname] = array("fieldname"=>$fieldname, 'type'=>$arow['type'], 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>"othercomment");
                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion'] = gT('Other comment');
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                        $fieldmap[$fieldname]['other'] = $arow['other'];
                    }
                }
            }
        }
        if (isset($fieldmap[$fieldname])) {
            //set question relevance (uses last SQ's relevance field for question relevance)
            $fieldmap[$fieldname]['relevance'] = $arow['relevance'];
            $fieldmap[$fieldname]['grelevance'] = $arow['grelevance'];
            $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
            $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
            $fieldmap[$fieldname]['preg'] = $arow['preg'];
            $fieldmap[$fieldname]['other'] = $arow['other'];
            $fieldmap[$fieldname]['help'] = $arow['help'];

            // Set typeName
        } else {
            --$questionSeq; // didn't generate a valid $fieldmap entry, so decrement the question counter to ensure they are sequential
        }

        if (isset($fieldmap[$fieldname]['typename'])) {
                    $fieldmap[$fieldname]['typename'] = $typename[$fieldname] = $arow['typename'];
        }
    }
    App()->setLanguage($sOldLanguage);

    if ($questionid === false) {
        // If the fieldmap was randomized, the master will contain the proper order.  Copy that fieldmap with the new language settings.
        if (isset(Yii::app()->session['survey_'.$surveyid]['fieldmap-'.$surveyid.'-randMaster'])) {
            $masterFieldmap = Yii::app()->session['survey_'.$surveyid]['fieldmap-'.$surveyid.'-randMaster'];
            $mfieldmap = Yii::app()->session['survey_'.$surveyid][$masterFieldmap];

            foreach ($mfieldmap as $fieldname => $mf) {
                if (isset($fieldmap[$fieldname])) {
                    // This array holds the keys of translatable attributes
                    $translatable = array_flip(array('question', 'subquestion', 'subquestion1', 'subquestion2', 'group_name', 'answerList', 'defaultValue', 'help'));
                    // We take all translatable attributes from the new fieldmap
                    $newText = array_intersect_key($fieldmap[$fieldname], $translatable);
                    // And merge them with the other values from the random fieldmap like questionSeq, groupSeq etc.
                    $mf = $newText + $mf;
                }
                $mfieldmap[$fieldname] = $mf;
            }
            $fieldmap = $mfieldmap;
        }

        Yii::app()->session['fieldmap-'.$surveyid.$sLanguage] = $fieldmap;
    }
    return $fieldmap;
}

/**
* Returns true if the given survey has a File Upload Question Type
* @param integer $iSurveyID
* @return bool
*/
function hasFileUploadQuestion($iSurveyID)
{
    $iCount = Question::model()->count("sid=:surveyid AND parent_qid=0 AND type='|'", array(':surveyid' => $iSurveyID));
    return $iCount > 0;
}

/**
* This function generates an array containing the fieldcode, and matching data in the same order as the activate script
*
* @param string $surveyid The Survey ID
* @param string $style 'short' (default) or 'full' - full creates extra information like default values
* @param boolean $force_refresh - Forces to really refresh the array, not just take the session copy
* @param int $questionid Limit to a certain qid only (for question preview) - default is false
* @param string $sQuestionLanguage The language to use
* @return array
*/
function createTimingsFieldMap($surveyid, $style = 'full', $force_refresh = false, $questionid = false, $sQuestionLanguage = null)
{

    static $timingsFieldMap;

    $sLanguage = sanitize_languagecode($sQuestionLanguage);
    $surveyid = sanitize_int($surveyid);
    $survey = Survey::model()->findByPk($surveyid);

    $sOldLanguage = App()->language;
    App()->setLanguage($sLanguage);

    //checks to see if fieldmap has already been built for this page.
    if (isset($timingsFieldMap[$surveyid][$style][$sLanguage]) && $force_refresh === false) {
        return $timingsFieldMap[$surveyid][$style][$sLanguage];
    }

    //do something
    $fields = createFieldMap($survey, $style, $force_refresh, $questionid, $sQuestionLanguage);
    $fieldmap = [];
    $fieldmap['interviewtime'] = array('fieldname'=>'interviewtime', 'type'=>'interview_time', 'sid'=>$surveyid, 'gid'=>'', 'qid'=>'', 'aid'=>'', 'question'=>gT('Total time'), 'title'=>'interviewtime');
    foreach ($fields as $field) {
        if (!empty($field['gid'])) {
            // field for time spent on page
            $fieldname = "{$field['sid']}X{$field['gid']}time";
            if (!isset($fieldmap[$fieldname])) {
                $fieldmap[$fieldname] = array("fieldname"=>$fieldname, 'type'=>"page_time", 'sid'=>$surveyid, "gid"=>$field['gid'], "group_name"=>$field['group_name'], "qid"=>'', 'aid'=>'', 'title'=>'groupTime'.$field['gid'], 'question'=>gT('Group time').": ".$field['group_name']);
            }

            // field for time spent on answering a question
            $fieldname = "{$field['sid']}X{$field['gid']}X{$field['qid']}time";
            if (!isset($fieldmap[$fieldname])) {
                $fieldmap[$fieldname] = array("fieldname"=>$fieldname, 'type'=>"answer_time", 'sid'=>$surveyid, "gid"=>$field['gid'], "group_name"=>$field['group_name'], "qid"=>$field['qid'], 'aid'=>'', "title"=>$field['title'].'Time', "question"=>gT('Question time').": ".$field['title']);
            }
        }
    }

    $timingsFieldMap[$surveyid][$style][$sLanguage] = $fieldmap;
    App()->setLanguage($sOldLanguage);
    return $timingsFieldMap[$surveyid][$style][$sLanguage];
}

/**
 *
 * @param mixed $needle
 * @param mixed $haystack
 * @param string $keyname
 * @param integer $maxanswers
 * @return array
 */
function arraySearchByKey($needle, $haystack, $keyname, $maxanswers = "")
{
    $output = array();
    foreach ($haystack as $hay) {
        if (array_key_exists($keyname, $hay)) {
            if ($hay[$keyname] == $needle) {
                if ($maxanswers == 1) {
                    return $hay;
                } else {
                    $output[] = $hay;
                }
            }
        }
    }
    return $output;
}

/**
* This function returns a count of the number of saved responses to a survey
*
* @param mixed $surveyid Survey ID
*/
function getSavedCount($surveyid)
{
    $surveyid = (int) $surveyid;

    return SavedControl::model()->getCountOfAll($surveyid);
}


function buildLabelSetCheckSumArray()
{
    // BUILD CHECKSUMS FOR ALL EXISTING LABEL SETS

    /**$query = "SELECT lid
    FROM ".db_table_name('labelsets')."
    ORDER BY lid"; */
    $result = LabelSet::model()->getLID(); //($query) or safeDie("safe_died collecting labelset ids<br />$query<br />");  //Checked)
    $csarray = array();
    foreach ($result as $row) {
        $thisset = "";
        $query2 = "SELECT code, title, sortorder, language, assessment_value
        FROM {{labels}}
        WHERE lid={$row['lid']}
        ORDER BY language, sortorder, code";
        $result2 = Yii::app()->db->createCommand($query2)->query();
        foreach ($result2->readAll() as $row2) {
            $thisset .= implode('.', $row2);
        } // while
        $csarray[$row['lid']] = hash('sha256', $thisset);
    }

    return $csarray;
}



/**
*
* Returns the questionAttribtue value set or '' if not set
* @author: lemeur
* @param $questionAttributeArray
* @param string $attributeName
* @param $language string Optional: The language if the particualr attributes is localizable
* @return string
*/
function getQuestionAttributeValue($questionAttributeArray, $attributeName, $language = '')
{
    if ($language == '' && isset($questionAttributeArray[$attributeName])) {
        return $questionAttributeArray[$attributeName];
    } elseif ($language != '' && isset($questionAttributeArray[$attributeName][$language])) {
        return $questionAttributeArray[$attributeName][$language];
    } else {
        return '';
    }
}


function categorySort($a, $b)
{
    $result = strnatcasecmp($a['category'], $b['category']);
    if ($result == 0) {
        $result = $a['sortorder'] - $b['sortorder'];
    }
    return $result;
}




// make a string safe to include in an HTML 'value' attribute.
function HTMLEscape($str)
{
    // escape newline characters, too, in case we put a value from
    // a TEXTAREA  into an <input type="hidden"> value attribute.
    return str_replace(array("\x0A", "\x0D"), array("&#10;", "&#13;"),
    htmlspecialchars($str, ENT_QUOTES));
}

/**
* This function strips UTF-8 control characters from strings, except tabs, CR and LF
* - it is intended to be used before any response data is saved to the response table
*
* @param mixed $sValue A string to be sanitized
* @return string A sanitized string, otherwise the unmodified original variable
*/
function stripCtrlChars($sValue)
{
    if (is_string($sValue)) {
        $sValue = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $sValue);
    }
    return $sValue;
}

// make a string safe to include in a JavaScript String parameter.
function javascriptEscape($str, $strip_tags = false, $htmldecode = false)
{

    if ($htmldecode == true) {
        $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
    }
    if ($strip_tags == true) {
        $str = strip_tags($str);
    }
    return str_replace(array('\'', '"', "\n", "\r"),
    array("\\'", '\u0022', "\\n", '\r'),
    $str);
}
// make a string safe to include in a json String parameter.
function jsonEscape($str, $strip_tags = false, $htmldecode = false)
{

    if ($htmldecode == true) {
        $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
    }
    if ($strip_tags == true) {
        $str = strip_tags($str);
    }
    return str_replace(array('"','\''), array("&apos;","&apos;"), $str);
}

/**
* This function mails a text $body to the recipient $to.
* You can use more than one recipient when using a semicolon separated string with recipients.
*
* @param string $body Body text of the email in plain text or HTML
* @param mixed $subject Email subject
* @param mixed $to Array with several email addresses or single string with one email address
* @param mixed $from
* @param mixed $sitename
* @param boolean $ishtml
* @param mixed $bouncemail
* @param mixed $attachments
* @return bool If successful returns true
*/
function SendEmailMessage($body, $subject, $to, $from, $sitename, $ishtml = false, $bouncemail = null, $attachments = null, $customheaders = "")
{
    global $maildebug, $maildebugbody;
    require_once(APPPATH.'/third_party/html2text/src/Html2Text.php');

    $emailmethod = Yii::app()->getConfig('emailmethod');
    $emailsmtphost = Yii::app()->getConfig("emailsmtphost");
    $emailsmtpuser = Yii::app()->getConfig("emailsmtpuser");
    $emailsmtppassword = Yii::app()->getConfig("emailsmtppassword");
    $emailsmtpdebug = Yii::app()->getConfig("emailsmtpdebug");
    $emailsmtpssl = Yii::app()->getConfig("emailsmtpssl");
    $defaultlang = Yii::app()->getConfig("defaultlang");
    $emailcharset = Yii::app()->getConfig("emailcharset");

    if ($emailcharset != 'utf-8') {
        $body = mb_convert_encoding($body, $emailcharset, 'utf-8');
        $subject = mb_convert_encoding($subject, $emailcharset, 'utf-8');
        $sitename = mb_convert_encoding($sitename, $emailcharset, 'utf-8');
    }

    if (!is_array($to)) {
        $to = array($to);
    }



    if (!is_array($customheaders) && $customheaders == '') {
        $customheaders = array();
    }
    if (Yii::app()->getConfig('demoMode')) {
        $maildebug = gT('Email was not sent because demo-mode is activated.');
        $maildebugbody = '';
        return false;
    }

    if (is_null($bouncemail)) {
        $sender = $from;
    } else {
        $sender = $bouncemail;
    }


    require_once(APPPATH.'/third_party/phpmailer/load_phpmailer.php');
    $mail = new PHPMailer\PHPMailer\PHPMailer;
    $mail->SMTPAutoTLS = false;
    if (!$mail->SetLanguage($defaultlang, APPPATH.'/third_party/phpmailer/language/')) {
        $mail->SetLanguage('en', APPPATH.'/third_party/phpmailer/language/');
    }
    $mail->CharSet = $emailcharset;
    if (isset($emailsmtpssl) && trim($emailsmtpssl) !== '' && $emailsmtpssl !== 0) {
        if ($emailsmtpssl === 1) {$mail->SMTPSecure = "ssl"; } else {$mail->SMTPSecure = $emailsmtpssl; }
    }

    $fromname = '';
    $fromemail = $from;
    if (strpos($from, '<')) {
        $fromemail = substr($from, strpos($from, '<') + 1, strpos($from, '>') - 1 - strpos($from, '<'));
        $fromname = trim(substr($from, 0, strpos($from, '<') - 1));
    }

    $senderemail = $sender;
    if (strpos($sender, '<')) {
        $senderemail = substr($sender, strpos($sender, '<') + 1, strpos($sender, '>') - 1 - strpos($sender, '<'));
    }

    switch ($emailmethod) {
        case "qmail":
            $mail->IsQmail();
            break;
        case "smtp":
            $mail->IsSMTP();
            if ($emailsmtpdebug > 0) {
                $mail->SMTPDebug = $emailsmtpdebug;
            }
            if (strpos($emailsmtphost, ':') > 0) {
                $mail->Host = substr($emailsmtphost, 0, strpos($emailsmtphost, ':'));
                $mail->Port = (int) substr($emailsmtphost, strpos($emailsmtphost, ':') + 1);
            } else {
                $mail->Host = $emailsmtphost;
            }
            $mail->Username = $emailsmtpuser;
            $mail->Password = $emailsmtppassword;
            if (trim($emailsmtpuser) != "") {
                $mail->SMTPAuth = true;
            }
            break;
        case "sendmail":
            $mail->IsSendmail();
            break;
        default:
            $mail->IsMail();
    }

    $mail->SetFrom($fromemail, $fromname);
    $mail->Sender = $senderemail; // Sets Return-Path for error notifications
    foreach ($to as $singletoemail) {
        if (strpos($singletoemail, '<')) {
            $toemail = substr($singletoemail, strpos($singletoemail, '<') + 1, strpos($singletoemail, '>') - 1 - strpos($singletoemail, '<'));
            $toname = trim(substr($singletoemail, 0, strpos($singletoemail, '<') - 1));
            $mail->AddAddress($toemail, $toname);
        } else {
            $mail->AddAddress($singletoemail);
        }
    }
    if (is_array($customheaders)) {
        foreach ($customheaders as $key=>$val) {
            $mail->AddCustomHeader($val);
        }
    }
    $mail->AddCustomHeader("X-Surveymailer: $sitename Emailer (LimeSurvey.org)");
    if (get_magic_quotes_gpc() != "0") {$body = stripcslashes($body); }
    if ($ishtml) {
        $mail->IsHTML(true);
        if (strpos($body, "<html>") === false) {
            $body = "<html>".$body."</html>";
        }
        $mail->msgHTML($body, App()->getConfig("publicdir")); // This allow embedded image if we remove the servername from image
        $html = new \Html2Text\Html2Text($body);
        $mail->AltBody = $html->getText();
    } else {
        $mail->IsHTML(false);
        $mail->Body = $body;
    }
    // Add attachments if they are there.
    if (is_array($attachments)) {
        foreach ($attachments as $attachment) {
            // Attachment is either an array with filename and attachment name.
            if (is_array($attachment)) {
                $mail->AddAttachment($attachment[0], $attachment[1]);
            } else {
// Or a string with the filename.
                $mail->AddAttachment($attachment);
            }
        }
    }
    $mail->Subject = $subject;

    if ($emailsmtpdebug > 0) {
        ob_start();
    }
    $sent = $mail->Send();
    $maildebug = $mail->ErrorInfo;
    if ($emailsmtpdebug > 0) {
        $maildebug .= '<br><strong>'.gT('SMTP debug output:').'</strong><pre>'.\CHtml::encode(ob_get_contents()).'</pre>';
        ob_end_clean();
    }
    $maildebugbody = $mail->Body;
    //if(!$sent) var_dump($maildebug);
    return $sent;
}


/**
*  This functions removes all HTML tags, Javascript, CRs, linefeeds and other strange chars from a given text
*
* @param string $sTextToFlatten  Text you want to clean
* @param boolean $bKeepSpan set to true for keep span, used for expression manager. Default: false
* @param boolean $bDecodeHTMLEntities If set to true then all HTML entities will be decoded to the specified charset. Default: false
* @param string $sCharset Charset to decode to if $decodeHTMLEntities is set to true. Default: UTF-8
* @param string $bStripNewLines strip new lines if true, if false replace all new line by \r\n. Default: true
*
* @return string  Cleaned text
*/
function flattenText($sTextToFlatten, $bKeepSpan = false, $bDecodeHTMLEntities = false, $sCharset = 'UTF-8', $bStripNewLines = true)
{
    $sNicetext = stripJavaScript($sTextToFlatten);
    // When stripping tags, add a space before closing tags so that strings with embedded HTML tables don't get concatenated
    $sNicetext = str_replace(array('</td', '</th'), array(' </td', ' </th'), $sNicetext);
    if ($bKeepSpan) {
        // Keep <span> so can show EM syntax-highlighting; add space before tags so that word-wrapping not destroyed when remove tags.
        $sNicetext = strip_tags($sNicetext, '<span><table><tr><td><th>');
    } else {
        $sNicetext = strip_tags($sNicetext);
    }
    // ~\R~u : see "What \R matches" and "Newline sequences" in http://www.pcre.org/pcre.txt - only available since PCRE 7.0
    if ($bStripNewLines) {
// strip new lines
        if (version_compare(substr(PCRE_VERSION, 0, strpos(PCRE_VERSION, ' ')), '7.0') > -1) {
            $sNicetext = preg_replace(array('~\R~u'), array(' '), $sNicetext);
        } else {
            // Poor man's replacement for line feeds
            $sNicetext = str_replace(array("\r\n", "\n", "\r"), array(' ', ' ', ' '), $sNicetext);
        }
    } elseif (version_compare(substr(PCRE_VERSION, 0, strpos(PCRE_VERSION, ' ')), '7.0') > -1) {
        // unify newlines to \r\n
        $sNicetext = preg_replace(array('~\R~u'), array("\r\n"), $sNicetext);
    }
    if ($bDecodeHTMLEntities === true) {
        $sNicetext = str_replace('&nbsp;', ' ', $sNicetext); // html_entity_decode does not convert &nbsp; to spaces
        $sNicetext = html_entity_decode($sNicetext, ENT_QUOTES, $sCharset);
    }
    $sNicetext = trim($sNicetext);
    return  $sNicetext;
}


/**
* getArrayFilterExcludesCascadesForGroup() queries the database and produces a list of array_filter_exclude questions and targets with in the same group
* @return array a keyed nested array, keyed by the qid of the question, containing cascade information
*/
function getArrayFilterExcludesCascadesForGroup($surveyid, $gid = "", $output = "qid")
{
    $surveyid = sanitize_int($surveyid);
    $survey = Survey::model()->findByPk($surveyid);

    $gid = sanitize_int($gid);


    $cascaded = array();
    $sources = array();
    $qidtotitle = array();
    $fieldmap = createFieldMap($survey, 'full', false, false, $survey->language);

    if ($gid != "") {
        $qrows = arraySearchByKey($gid, $fieldmap, 'gid');
    } else {
        $qrows = $fieldmap;
    }
    $grows = array(); //Create an empty array in case query not return any rows
    // Store each result as an array with in the $grows array
    foreach ($qrows as $qrow) {
        if (isset($qrow['gid']) && !empty($qrow['gid'])) {
            $grows[$qrow['qid']] = array('qid' => $qrow['qid'], 'type' => $qrow['type'], 'mandatory' => $qrow['mandatory'], 'title' => $qrow['title'], 'gid' => $qrow['gid']);
        }
    }
    foreach ($grows as $qrow) {
    // Cycle through questions to see if any have list_filter attributes
        $qidtotitle[$qrow['qid']] = $qrow['title'];
        $qresult = QuestionAttribute::model()->getQuestionAttributes($qrow['qid']);
        if (isset($qresult['array_filter_exclude'])) {
        // We Found a array_filter attribute
            $val = $qresult['array_filter_exclude']; // Get the Value of the Attribute ( should be a previous question's title in same group )
            foreach ($grows as $avalue) {
            // Cycle through all the other questions in this group until we find the source question for this array_filter
                if ($avalue['title'] == $val) {
                    /* This question ($avalue) is the question that provides the source information we use
                    * to determine which answers show up in the question we're looking at, which is $qrow['qid']
                    * So, in other words, we're currently working on question $qrow['qid'], trying to find out more
                    * information about question $avalue['qid'], because that's the source */
                    $sources[$qrow['qid']] = $avalue['qid']; /* This question ($qrow['qid']) relies on answers in $avalue['qid'] */
                    if (isset($cascades)) {unset($cascades); }
                    $cascades = array(); /* Create an empty array */

                    /* At this stage, we know for sure that this question relies on one other question for the filter */
                    /* But this function wants to send back information about questions that rely on multiple other questions for the filter */
                    /* So we don't want to do anything yet */

                    /* What we need to do now, is check whether the question this one relies on, also relies on another */

                    /* The question we are now checking is $avalue['qid'] */
                    $keepgoing = 1;
                    $questiontocheck = $avalue['qid'];
                    /* If there is a key in the $sources array that is equal to $avalue['qid'] then we want to add that
                    * to the $cascades array */
                    while ($keepgoing > 0) {
                        if (!empty($sources[$questiontocheck])) {
                            $cascades[] = $sources[$questiontocheck];
                            /* Now we need to move down the chain */
                            /* We want to check the $sources[$questiontocheck] question */
                            $questiontocheck = $sources[$questiontocheck];
                        } else {
                            /* Since it was empty, there must not be any more questions down the cascade */
                            $keepgoing = 0;
                        }
                    }
                    /* Now add all that info */
                    if (count($cascades) > 0) {
                        $cascaded[$qrow['qid']] = $cascades;
                    }
                }
            }
        }
    }
    $cascade2 = array();
    if ($output == "title") {
        foreach ($cascaded as $key=>$cascade) {
            foreach ($cascade as $item) {
                $cascade2[$key][] = $qidtotitle[$item];
            }
        }
        $cascaded = $cascade2;
    }
    return $cascaded;
}

function createPassword()
{
    $aCharacters = "ABCDEGHJIKLMNOPQURSTUVWXYZabcdefhjmnpqrstuvwxyz23456789";
    $iPasswordLength = 12;
    $sPassword = '';
    for ($i = 0; $i < $iPasswordLength; $i++) {
        $sPassword .= $aCharacters[(int) floor(rand(0, strlen($aCharacters) - 1))];
    }
    return $sPassword;
}

// TODO input Survey Object
function languageDropdown($surveyid, $selected)
{
    $slangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
    $baselang = Survey::model()->findByPk($surveyid)->language;
    array_unshift($slangs, $baselang);
    $html = "<select class='listboxquestions' name='langselect' onchange=\"window.open(this.options[this.selectedIndex].value, '_top')\">\n";

    foreach ($slangs as $lang) {
        $link = Yii::app()->homeUrl.("/admin/dataentry/sa/view/surveyid/".$surveyid."/lang/".$lang);
        if ($lang == $selected) {
            $html .= "\t<option value='{$link}' selected='selected'>".getLanguageNameFromCode($lang, false)."</option>\n";
        }
        if ($lang != $selected) {
            $html .= "\t<option value='{$link}'>".getLanguageNameFromCode($lang, false)."</option>\n";
        }
    }
    $html .= "</select>";
    return $html;
}

// TODO input Survey Object
/**
 * Creates a <select> HTML element for language selection for this survey
 *
 * @param int $surveyid
 * @param string $selected The selected language
 * @return string
 */
function languageDropdownClean($surveyid, $selected)
{
    $slangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
    $baselang = Survey::model()->findByPk($surveyid)->language;
    array_unshift($slangs, $baselang);
    $html = "<select class='form-control listboxquestions' id='language' name='language'>\n";
    foreach ($slangs as $lang) {
        if ($lang == $selected) {
            $html .= "\t<option value='$lang' selected='selected'>".getLanguageNameFromCode($lang, false)."</option>\n";
        }
        if ($lang != $selected) {
            $html .= "\t<option value='$lang'>".getLanguageNameFromCode($lang, false)."</option>\n";
        }
    }
    $html .= "</select>";
    return $html;
}

/**
* This function removes a directory recursively
*
* @param string $dirname
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
        rmdirr($dirname.DIRECTORY_SEPARATOR.$entry);
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
    $field = preg_replace("/^\040*\"/", "", $field);
    $field = preg_replace("/\"\040*$/", "", $field);
    $field = str_replace('""', '"', $field);
    //print $field."\n";
    return $field;
}

/**
* This function return actual completion state
*
* @return string|boolean (complete|incomplete|all) or false
*/
function incompleteAnsFilterState()
{
    $letsfilter = returnGlobal('completionstate'); //read get/post completionstate

    // first let's initialize the incompleteanswers session variable
    if ($letsfilter != '') {
// use the read value if not empty
        Yii::app()->session['incompleteanswers'] = $letsfilter;
    } elseif (empty(Yii::app()->session['incompleteanswers'])) {
// sets default variable value from config file
        Yii::app()->session['incompleteanswers'] = Yii::app()->getConfig('filterout_incomplete_answers');
    }

    if (Yii::app()->session['incompleteanswers'] == 'complete' || Yii::app()->session['incompleteanswers'] == 'all' || Yii::app()->session['incompleteanswers'] == 'incomplete') {
        return Yii::app()->session['incompleteanswers'];
    } else {
// last resort is to prevent filtering
        return false;
    }
}


/**
* isCaptchaEnabled($screen, $usecaptchamode)
* @param string $screen - the screen name for which to test captcha activation
*
* @return boolean|null - returns true if captcha must be enabled
**/
function isCaptchaEnabled($screen, $captchamode = '')
{
    if (!extension_loaded('gd')) {
        return false;
    }
    switch ($screen) {
        case 'registrationscreen':
            if ($captchamode == 'A' ||
            $captchamode == 'B' ||
            $captchamode == 'D' ||
            $captchamode == 'R') {
                return true;
            }
            return false;
        case 'surveyaccessscreen':
            if ($captchamode == 'A' ||
            $captchamode == 'B' ||
            $captchamode == 'C' ||
            $captchamode == 'X') {
                return true;
            }
            return false;
        case 'saveandloadscreen':
            if ($captchamode == 'A' ||
                $captchamode == 'C' ||
                $captchamode == 'D' ||
                $captchamode == 'S') {
                return true;
            }
            return false;
        default:
            return true;
    }
}


/**
* Check if a table does exist in the database
*
* @param string $sTableName Table name to check for (without dbprefix!))
* @return boolean True or false if table exists or not
*/
function tableExists($sTableName)
{
    $sTableName = Yii::app()->db->tablePrefix.str_replace(array('{', '}'), array('', ''), $sTableName);
    return in_array($sTableName, Yii::app()->db->schema->getTableNames());
}

// Returns false if the survey is anonymous,
// and a survey participants table exists: in this case the completed field of a token
// will contain 'Y' instead of the submitted date to ensure privacy
// Returns true otherwise
function isTokenCompletedDatestamped($thesurvey)
{
    if ($thesurvey['anonymized'] == 'Y' && tableExists('tokens_'.$thesurvey['sid'])) {
        return false;
    } else {
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
* @param string $date
* @param string $dformat
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
    $surveyInfo = getSurveyInfo($surveyid);

    if ($surveyInfo['bounce_email'] == '') {
        return null; // will be converted to from in MailText
    } else {
        return $surveyInfo['bounce_email'];
    }
}

// getEmailFormat: returns email format for the survey
// returns 'text' or 'html'
function getEmailFormat($surveyid)
{
    $surveyInfo = getSurveyInfo($surveyid);
    if ($surveyInfo['htmlemail'] == 'Y') {
        return 'html';
    } else {
        return 'text';
    }

}

// Check if user has manage rights for a template
function hasTemplateManageRights($userid, $sThemeFolder)
{
    $userid = (int) $userid;
    $sThemeFolder = sanitize_paranoid_string($sThemeFolder);
    if ($sThemeFolder === false) {
        return false;
    }
    return Permission::model()->hasTemplatePermission($sThemeFolder, 'read', $userid);
}


/**
* Translate links which are in any answer/question/survey/email template/label set to their new counterpart
*
* @param string $sType 'survey' or 'label'
* @param mixed $iOldSurveyID
* @param mixed $iNewSurveyID
* @param mixed $sString
* @return string
*/
function translateLinks($sType, $iOldSurveyID, $iNewSurveyID, $sString)
{
    $iOldSurveyID = (int) $iOldSurveyID;
    $iNewSurveyID = (int) $iNewSurveyID; // To avoid injection of a /e regex modifier without having to check all execution paths
    if ($sType == 'survey') {
        $sPattern = '(http(s)?:\/\/)?(([a-z0-9\/\.])*(?=(\/upload))\/upload\/surveys\/'.$iOldSurveyID.'\/)';
        $sReplace = Yii::app()->getConfig("publicurl")."upload/surveys/{$iNewSurveyID}/";
        return preg_replace('/'.$sPattern.'/u', $sReplace, $sString);
    } elseif ($sType == 'label') {
        $sPattern = '(http(s)?:\/\/)?(([a-z0-9\/\.])*(?=(\/upload))\/upload\/labels\/'.$iOldSurveyID.'\/)';
        $sReplace = Yii::app()->getConfig("publicurl")."upload/labels/{$iNewSurveyID}/";
        return preg_replace("/".$sPattern."/u", $sReplace, $sString);
    } else // unknown type
    {
        return $sString;
    }
}

/**
 * This function creates the old fieldnames for survey import
 *
 * @param mixed $iOldSID The old survey id
 * @param integer $iNewSID The new survey id
 * @param array $aGIDReplacements An array with group ids (oldgid=>newgid)
 * @param array $aQIDReplacements An array with question ids (oldqid=>newqid)
 * @return array|bool
 */
function reverseTranslateFieldNames($iOldSID, $iNewSID, $aGIDReplacements, $aQIDReplacements)
{
    $aGIDReplacements = array_flip($aGIDReplacements);
    $aQIDReplacements = array_flip($aQIDReplacements);

    /** @var Survey $oNewSurvey */
    $oNewSurvey = Survey::model()->findByPk($iNewSID);

    if ($iOldSID == $iNewSID) {
        $forceRefresh = true; // otherwise grabs the cached copy and throws undefined index exceptions
    } else {
        $forceRefresh = false;
    }
    $aFieldMap = createFieldMap($oNewSurvey, 'short', $forceRefresh, false, $oNewSurvey->language);

    $aFieldMappings = array();
    foreach ($aFieldMap as $sFieldname=>$aFieldinfo) {
        if ($aFieldinfo['qid'] != null) {
            $aFieldMappings[$sFieldname] = $iOldSID.'X'.$aGIDReplacements[$aFieldinfo['gid']].'X'.$aQIDReplacements[$aFieldinfo['qid']].$aFieldinfo['aid'];
            if ($aFieldinfo['type'] == '1') {
                $aFieldMappings[$sFieldname] = $aFieldMappings[$sFieldname].'#'.$aFieldinfo['scale_id'];
            }
            // now also add a shortened field mapping which is needed for certain kind of condition mappings
            $aFieldMappings[$iNewSID.'X'.$aFieldinfo['gid'].'X'.$aFieldinfo['qid']] = $iOldSID.'X'.$aGIDReplacements[$aFieldinfo['gid']].'X'.$aQIDReplacements[$aFieldinfo['qid']];
            // Shortened field mapping for timings table
            $aFieldMappings[$iNewSID.'X'.$aFieldinfo['gid']] = $iOldSID.'X'.$aGIDReplacements[$aFieldinfo['gid']];
        }
    }
    return array_flip($aFieldMappings);
}

/**
 * put your comment there...
 *
 * @param integer $id
 * @param string $type
 * @return bool
 */
function hasResources($id, $type = 'survey')
{
    $dirname = Yii::app()->getConfig("uploaddir");

    if ($type == 'survey') {
        $dirname .= "/surveys/$id";
    } elseif ($type == 'label') {
        $dirname .= "/labels/$id";
    } else {
        return false;
    }

    if (is_dir($dirname) && $dh = opendir($dirname)) {
        while (($entry = readdir($dh)) !== false) {
            if ($entry !== '.' && $entry !== '..') {
                return true;
            }
        }
        closedir($dh);
    } else {
        return false;
    }

    return false;
}

/**
 * Creates a random sequence of characters
 *
 * @param integer $length Length of resulting string
 * @param string $pattern To define which characters should be in the resulting string
 * @return string
 */
function randomChars($length, $pattern = "23456789abcdefghijkmnpqrstuvwxyz")
{
    $patternlength = strlen($pattern) - 1;
    $key = '';
    for ($i = 0; $i < $length; $i++) {
        $key .= $pattern{mt_rand(0, $patternlength)};
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
function conditionalNewlineToBreak($mytext, $ishtml, $encoded = '')
{
    if ($ishtml === true) {
        // $mytext has been processed by gT with html mode
        // and thus \n has already been translated to &#10;
        if ($encoded == '') {
            $mytext = str_replace('&#10;', '<br />', $mytext);
        }
        return str_replace("\n", '<br />', $mytext);
    } else {
        return $mytext;
    }
}


function breakToNewline($data)
{
    return preg_replace('!<br.*>!iU', "\n", $data);
}

/**
* Provides a safe way to end the application
*
* @param mixed $sText
* @returns boolean Fake return so Scrutinizes shuts up
*/
function safeDie($sText)
{
    //Only allowed tag: <br />
    $textarray = explode('<br />', $sText);
    $textarray = array_map('htmlspecialchars', $textarray);
    die(implode('<br />', $textarray));
    return false; // do not remove
}

/**
 * @param string $str
 */
function fixCKeditorText($str)
{
    $str = str_replace('<br type="_moz" />', '', $str);
    if ($str == "<br />" || $str == " " || $str == "&nbsp;") {
        $str = "";
    }
    if (preg_match("/^[\s]+$/", $str)) {
        $str = '';
    }
    if ($str == "\n") {
        $str = "";
    }
    if (trim($str) == "&nbsp;" || trim($str) == '') {
// chrome adds a single &nbsp; element to empty fckeditor fields
        $str = "";
    }

    return $str;
}


/**
 * This is a helper function for getAttributeFieldNames
 *
 * @param mixed $fieldname
 * @return bool
 */
function filterForAttributes($fieldname)
{
    if (strpos($fieldname, 'attribute_') === false) {
        return false;
    } else {
        return true;
    }
    }

/**
* Retrieves the attribute field names from the related survey participants table
*
* @param mixed $iSurveyID  The survey ID
* @return array The fieldnames
*/
function getAttributeFieldNames($iSurveyID)
{
    $survey = Survey::model()->findByPk($iSurveyID);
    if (!$survey->hasTokensTable || !$table = Yii::app()->db->schema->getTable($survey->tokensTableName)) {
            return Array();
    }

    return array_filter(array_keys($table->columns), 'filterForAttributes');

}

/**
 * Returns the full list of attribute token fields including the properties for each field
 * Use this instead of plain Survey::model()->findByPk($iSurveyID)->tokenAttributes calls because Survey::model()->findByPk($iSurveyID)->tokenAttributes may contain old descriptions where the fields does not physically exist
 *
 * @param integer $iSurveyID The Survey ID
 * @return array
 */
function getParticipantAttributes($iSurveyID)
{
    $survey = Survey::model()->findByPk($iSurveyID);
    if (!$survey->hasTokensTable || !Yii::app()->db->schema->getTable($survey->tokensTableName)) {
            return Array();
    }
    return getTokenFieldsAndNames($iSurveyID, true);
}





/**
* Retrieves the attribute names from the related survey participants table
*
* @param mixed $surveyid  The survey ID
* @param boolean $bOnlyAttributes Set this to true if you only want the fieldnames of the additional attribue fields - defaults to false
* @return array The fieldnames as key and names as value in an Array
*/
function getTokenFieldsAndNames($surveyid, $bOnlyAttributes = false)
{


    $aBasicTokenFields = array('firstname'=>array(
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
            'description'=>gT('Token'),
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

    $aExtraTokenFields = getAttributeFieldNames($surveyid);
    $aSavedExtraTokenFields = Survey::model()->findByPk($surveyid)->tokenAttributes;

    // Drop all fields that are in the saved field description but not in the table definition
    $aSavedExtraTokenFields = array_intersect_key($aSavedExtraTokenFields, array_flip($aExtraTokenFields));

    // Now add all fields that are in the table but not in the field description
    foreach ($aExtraTokenFields as $sField) {
        if (!isset($aSavedExtraTokenFields[$sField])) {
            $aSavedExtraTokenFields[$sField] = array(
            'description'=>$sField,
            'mandatory'=>'N',
            'showregister'=>'N',
            'cpdbmap'=>''
            );
        } elseif (empty($aSavedExtraTokenFields[$sField]['description'])) {
            $aSavedExtraTokenFields[$sField]['description'] = $sField;
        }
    }
    if ($bOnlyAttributes) {
        return $aSavedExtraTokenFields;
    } else {
        return array_merge($aBasicTokenFields, $aSavedExtraTokenFields);
    }
}


/**
* This function strips any content between and including <javascript> tags
*
* @param string $sContent String to clean
* @return string  Cleaned string
*/
function stripJavaScript($sContent)
{
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
function showJavaScript($sContent)
{
    $text = preg_replace_callback('@<script[^>]*?>.*?</script>@si',
        function($matches) {
            return htmlspecialchars($matches[0]);
        }, $sContent);
    return $text;
}

/**
* This function cleans files from the temporary directory being older than 1 day
* @todo Make the days configurable
*/
function cleanTempDirectory()
{
    $dir = Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR;
    $dp = opendir($dir) or safeDie('Could not open temporary directory');
    while ($file = readdir($dp)) {
        if (is_file($dir.$file) && (filemtime($dir.$file)) < (strtotime('-1 days')) && $file != 'index.html' && $file != '.gitignore' && $file != 'readme.txt') {
            /** @scrutinizer ignore-unhandled */ @unlink($dir.$file);
        }
    }
    $dir = Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR;
    $dp = opendir($dir) or safeDie('Could not open temporary upload directory');
    while ($file = readdir($dp)) {
        if (is_file($dir.$file) && (filemtime($dir.$file)) < (strtotime('-1 days')) && $file != 'index.html' && $file != '.gitignore' && $file != 'readme.txt') {
            /** @scrutinizer ignore-unhandled */ @unlink($dir.$file);
        }
    }
    closedir($dp);
}

function useFirebug()
{
    if (FIREBUG == true) {
        App()->getClientScript()->registerScriptFile('http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js');
    };
};

/**
* This is a convenience function for the coversion of datetime values
*
* @param mixed $value
* @param string $fromdateformat
* @param mixed $todateformat
* @return string
*/
function convertDateTimeFormat($value, $fromdateformat, $todateformat)
{
    $date = DateTime::createFromFormat($fromdateformat, $value);
    if ($date) {
        return $date->format($todateformat);
    } else {
        $date = new DateTime($value);
        return $date->format($todateformat);
    }
}

/**
* This is a convenience function to convert any date, in any date format, to the global setting date format
* Check if the time shoul be rendered also
*
* @param string $sDate
* @param boolean $withTime
* @return string
*/
function convertToGlobalSettingFormat($sDate, $withTime = false)
{

    $sDateformatdata = getDateFormatData(Yii::app()->session['dateformat']); // We get the Global Setting date format
    $usedDatetime = ($withTime === true ? $sDateformatdata['phpdate']." H:i" : $sDateformatdata['phpdate']); //return also hours and minutes if asked for
    try {
        // Workaround for bug in older PHP version (confirmed for 5.5.9)
        // The bug is causing invalid dates to create an internal server error which cannot not be caught by try.. catch
        if (@strtotime($sDate) === false) {
            throw new Exception("Failed to parse date string ({$sDate})");
        }
        $oDate           = new DateTime($sDate); // We generate the Date object (PHP will deal with the format of the string)
        $sDate           = $oDate->format($usedDatetime); // We apply it to the Date object to generate a string date
        return $sDate; // We return the string date
    } catch (Exception $e) {
        $oDate           = new DateTime('1/1/1980 00:00'); // We generate the Date object (PHP will deal with the format of the string)
        $sDate           = $oDate->format($usedDatetime); // We apply it to the Date object to generate a string date
        return $sDate; // We return the string date

    }
}

/**
* This function removes the UTF-8 Byte Order Mark from a string
*
* @param string $str
* @return string
*/
function removeBOM($str = "")
{
    if (substr($str, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
        $str = substr($str, 3);
    }
    return $str;
}


/**
 * This function returns the complete directory path to a given template name
 *
 * @param mixed $sTemplateName
 * @return string
 */
function getTemplatePath($sTemplateName = '')
{
    return Template::getTemplatePath($sTemplateName);
}

/**
 * This function returns the complete URL path to a given template name
 *
 * @param mixed $sTemplateName
 * @return string
 */
function getTemplateURL($sTemplateName)
{
    return Template::getTemplateURL($sTemplateName);
}

/**
 * Return an array of subquestions for a given sid/qid
 *
 * @param int $sid
 * @param int $qid
 * @param string $sLanguage Language of the subquestion text
 * @return array
 */
function getSubQuestions($sid, $qid, $sLanguage)
{

    static $subquestions;

    if (!isset($subquestions[$sid])) {
        $subquestions[$sid] = array();
    }
    if (!isset($subquestions[$sid][$sLanguage])) {

        $query = "SELECT sq.*, q.other FROM {{questions}} as sq, {{questions}} as q"
        ." WHERE sq.parent_qid=q.qid AND q.sid=".$sid
        ." AND sq.language='".$sLanguage."' "
        ." AND q.language='".$sLanguage."' "
        ." ORDER BY sq.parent_qid, q.question_order,sq.scale_id , sq.question_order";

        $query = Yii::app()->db->createCommand($query)->query();

        $resultset = array();
        //while ($row=$result->FetchRow())
        foreach ($query->readAll() as $row) {
            $resultset[$row['parent_qid']][] = $row;
        }
        $subquestions[$sid][$sLanguage] = $resultset;
    }
    if (isset($subquestions[$sid][$sLanguage][$qid])) {
        return $subquestions[$sid][$sLanguage][$qid];
    }
    return array();
}

/**
* Wrapper function to retrieve an xmlwriter object and do error handling if it is not compiled
* into PHP
*/
function getXMLWriter()
{
    if (!extension_loaded('xmlwriter')) {
        safeDie('XMLWriter class not compiled into PHP, please contact your system administrator');
    }
    return new XMLWriter();
}

/**
* SSLRedirect() generates a redirect URL for the appropriate SSL mode then applies it.
* (Was redirect() before CodeIgniter port.)
*
* @param string $enforceSSLMode string 's' or '' (empty).
*/
function SSLRedirect($enforceSSLMode)
{
    $url = 'http'.$enforceSSLMode.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    if (!headers_sent()) {
// If headers not sent yet... then do php redirect
        //ob_clean();
        header('Location: '.$url);
        //ob_flush();
        Yii::app()->end();
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
    $bSSLActive = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") ||
    (isset($_SERVER['HTTP_FORWARDED_PROTO']) && $_SERVER['HTTP_FORWARDED_PROTO'] == "https") ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https"));
    if (Yii::app()->getConfig('ssl_emergency_override') !== true) {
        $force_ssl = strtolower(getGlobalSetting('force_ssl'));
    } else {
        $force_ssl = 'off';
    };
    if ($force_ssl == 'on' && !$bSSLActive) {
        SSLRedirect('s');
    }
    if ($force_ssl == 'off' && $bSSLActive) {
        SSLRedirect('');
    };
};


/**
 * Creates an array with details on a particular response for display purposes
 * Used in Print answers, Detailed response view and Detailed admin notification email
 *
 * @param mixed $iSurveyID
 * @param mixed $iResponseID
 * @param mixed $sLanguageCode
 * @param boolean $bHonorConditions Apply conditions
 * @return array
 */
function getFullResponseTable($iSurveyID, $iResponseID, $sLanguageCode, $bHonorConditions = true)
{
    $survey = Survey::model()->findByPk($iSurveyID);
    $aFieldMap = createFieldMap($survey, 'full', false, false, $sLanguageCode);

    //Get response data
    $idrow = SurveyDynamic::model($iSurveyID)->findByAttributes(array('id'=>$iResponseID));

    // Create array of non-null values - those are the relevant ones
    $aRelevantFields = array();

    foreach ($aFieldMap as $sKey=>$fname) {
        if (LimeExpressionManager::QuestionIsRelevant($fname['qid']) || $bHonorConditions === false) {
            $aRelevantFields[$sKey] = $fname;
        }
    }

    $aResultTable = array();
    $oldgid = 0;
    $oldqid = 0;
    foreach ($aRelevantFields as $sKey=>$fname) {
        if (!empty($fname['qid'])) {
            $attributes = QuestionAttribute::model()->getQuestionAttributes($fname['qid']);
            if (getQuestionAttributeValue($attributes, 'hidden') == 1) {
                continue;
            }
        }
        $question = $fname['question'];
        $subquestion = '';
        if (isset($fname['gid']) && !empty($fname['gid'])) {
            //Check to see if gid is the same as before. if not show group name
            if ($oldgid !== $fname['gid']) {
                $oldgid = $fname['gid'];
                if (LimeExpressionManager::GroupIsRelevant($fname['gid']) || $bHonorConditions === false) {
                    $aResultTable['gid_'.$fname['gid']] = array($fname['group_name'], QuestionGroup::model()->getGroupDescription($fname['gid'], $sLanguageCode));
                }
            }
        }
        if (!empty($fname['qid'])) {
            if ($oldqid !== $fname['qid']) {
                $oldqid = $fname['qid'];
                if (isset($fname['subquestion']) || isset($fname['subquestion1']) || isset($fname['subquestion2'])) {
                    $aResultTable['qid_'.$fname['sid'].'X'.$fname['gid'].'X'.$fname['qid']] = array($fname['question'], '', '');
                } else {
                    $answer = getExtendedAnswer($iSurveyID, $fname['fieldname'], $idrow[$fname['fieldname']], $sLanguageCode);
                    $aResultTable[$fname['fieldname']] = array($question, '', $answer);
                    continue;
                }
            }
        } else {
            $answer = getExtendedAnswer($iSurveyID, $fname['fieldname'], $idrow[$fname['fieldname']], $sLanguageCode);
            $aResultTable[$fname['fieldname']] = array($question, '', $answer);
            continue;
        }
        if (isset($fname['subquestion'])) {
                    $subquestion = "[{$fname['subquestion']}]";
        }

        if (isset($fname['subquestion1'])) {
                    $subquestion = "[{$fname['subquestion1']}]";
        }

        if (isset($fname['subquestion2'])) {
                    $subquestion .= "[{$fname['subquestion2']}]";
        }

        $answer = getExtendedAnswer($iSurveyID, $fname['fieldname'], $idrow[$fname['fieldname']], $sLanguageCode);
        $aResultTable[$fname['fieldname']] = array($question, $subquestion, $answer);
    }
    return $aResultTable;
}

/**
 * Check if $str is an integer, or string representation of an integer
 *
 * @param string $mStr
 * @return bool|int
 */
function isNumericInt($mStr)
{
    if (is_int($mStr)) {
            return true;
    } elseif (is_string($mStr)) {
            return preg_match("/^[0-9]+$/", $mStr);
    }
    return false;
}

/**
* Implode and sort content array for very long arrays
*
* @param string $sDelimeter
* @param array $aArray
* @return string String showing array content
*/
function short_implode($sDelimeter, $sHyphen, $aArray)
{
    if (sizeof($aArray) < Yii::app()->getConfig('minlengthshortimplode')) {
        sort($aArray);
        return implode($sDelimeter, $aArray);
    } else {
        sort($aArray);
        $iIndexA = 0;
        $sResult = null;
        while ($iIndexA < sizeof($aArray)) {
            if ($iIndexA == 0) {
                $sResult = $aArray[$iIndexA];
            } else {
                if (strlen($sResult) > Yii::app()->getConfig('maxstringlengthshortimplode') - strlen($sDelimeter) - 3) {
                    return $sResult.$sDelimeter.'...';
                } else {
                    $sResult = $sResult.$sDelimeter.$aArray[$iIndexA];
                }
            }
            $iIndexB = $iIndexA + 1;
            if ($iIndexB < sizeof($aArray)) {
                while ($iIndexB < sizeof($aArray) && $aArray[$iIndexB] - 1 == $aArray[$iIndexB - 1]) {
                    $iIndexB++;
                }
                if ($iIndexA < $iIndexB - 1) {
                    $sResult = $sResult.$sHyphen.$aArray[$iIndexB - 1];
                }
            }
            $iIndexA = $iIndexB;
        }
        return $sResult;
    }
}

/**
* Include Keypad headers
*/
function includeKeypad()
{
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('third_party').'jquery-keypad/jquery.plugin.min.js');
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('third_party').'jquery-keypad/jquery.keypad.min.js');
    $localefile = Yii::app()->getConfig('rootdir').'/third_party/jquery-keypad/jquery.keypad-'.App()->language.'.js';
    if (App()->language != 'en' && file_exists($localefile)) {
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('third_party').'jquery-keypad/jquery.keypad-'.App()->language.'.js');
    }
    Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('third_party')."jquery-keypad/jquery.keypad.alt.css");
}


/**
* This function replaces the old insertans tags with new ones across a survey
*
* @param string $newsid  Old SID
* @param string $oldsid  New SID
* @param mixed $fieldnames Array  array('oldfieldname'=>'newfieldname')
*/
function translateInsertansTags($newsid, $oldsid, $fieldnames)
{
    uksort($fieldnames, function($a, $b) {return strlen($a) < strlen($b); });

    Yii::app()->loadHelper('database');
    $newsid = (int) $newsid;
    $oldsid = (int) $oldsid;

    # translate 'surveyls_urldescription' and 'surveyls_url' INSERTANS tags in surveyls
    $sql = "SELECT surveyls_survey_id, surveyls_language, surveyls_urldescription, surveyls_url from {{surveys_languagesettings}}
    WHERE surveyls_survey_id=".$newsid." AND (surveyls_urldescription LIKE '%{$oldsid}X%' OR surveyls_url LIKE '%{$oldsid}X%')";
    $result = dbExecuteAssoc($sql) or safeDie("Can't read groups table in translateInsertansTags"); // Checked

    //while ($qentry = $res->FetchRow())
    foreach ($result->readAll() as $qentry) {
        $urldescription = $qentry['surveyls_urldescription'];
        $endurl = $qentry['surveyls_url'];
        $language = $qentry['surveyls_language'];

        foreach ($fieldnames as $sOldFieldname=>$sNewFieldname) {
            $pattern = $sOldFieldname;
            $replacement = $sNewFieldname;
            $urldescription = preg_replace('/'.$pattern.'/', $replacement, $urldescription);
            $endurl = preg_replace('/'.$pattern.'/', $replacement, $endurl);
        }

        if (strcmp($urldescription, $qentry['surveyls_urldescription']) != 0 ||
        (strcmp($endurl, $qentry['surveyls_url']) != 0)) {

            // Update Field

            $data = array(
            'surveyls_urldescription' => $urldescription,
            'surveyls_url' => $endurl
            );

            $where = array(
            'surveyls_survey_id' => $newsid,
            'surveyls_language' => $language
            );

            SurveyLanguageSetting::model()->updateRecord($data, $where);

        } // Enf if modified
    } // end while qentry

    # translate 'quotals_urldescrip' and 'quotals_url' INSERTANS tags in quota_languagesettings
    $sql = "SELECT quotals_id, quotals_urldescrip, quotals_url from {{quota_languagesettings}} qls, {{quota}} q
    WHERE sid=".$newsid." AND q.id=qls.quotals_quota_id AND (quotals_urldescrip LIKE '%{$oldsid}X%' OR quotals_url LIKE '%{$oldsid}X%')";
    $result = dbExecuteAssoc($sql) or safeDie("Can't read quota table in transInsertAns"); // Checked

    foreach ($result->readAll() as $qentry) {
        $urldescription = $qentry['quotals_urldescrip'];
        $endurl = $qentry['quotals_url'];

        foreach ($fieldnames as $sOldFieldname=>$sNewFieldname) {
            $pattern = $sOldFieldname;
            $replacement = $sNewFieldname;
            $urldescription = preg_replace('/'.$pattern.'/', $replacement, $urldescription);
            $endurl = preg_replace('/'.$pattern.'/', $replacement, $endurl);
        }

        if (strcmp($urldescription, $qentry['quotals_urldescrip']) != 0 || (strcmp($endurl, $qentry['quotals_url']) != 0)) {
            // Update Field
            $sqlupdate = "UPDATE {{quota_languagesettings}} SET quotals_urldescrip='".$urldescription."', quotals_url='".$endurl."' WHERE quotals_id={$qentry['quotals_id']}";
            dbExecuteAssoc($sqlupdate) or safeDie("Couldn't update INSERTANS in quota_languagesettings<br />$sqlupdate<br />"); //Checked
        } // Enf if modified
    } // end while qentry

    # translate 'description' INSERTANS tags in groups
    $sql = "SELECT gid, language, group_name, description from {{groups}}
    WHERE sid=".$newsid." AND description LIKE '%{$oldsid}X%' OR group_name LIKE '%{$oldsid}X%'";
    $res = dbExecuteAssoc($sql) or safeDie("Can't read groups table in transInsertAns"); // Checked

    //while ($qentry = $res->FetchRow())
    foreach ($res->readAll() as $qentry) {
        $gpname = $qentry['group_name'];
        $description = $qentry['description'];
        $gid = $qentry['gid'];
        $language = $qentry['language'];

        foreach ($fieldnames as $sOldFieldname=>$sNewFieldname) {
            $pattern = $sOldFieldname;
            $replacement = $sNewFieldname;
            $gpname = preg_replace('/'.$pattern.'/', $replacement, $gpname);
            $description = preg_replace('/'.$pattern.'/', $replacement, $description);
        }

        if (strcmp($description, $qentry['description']) != 0 || strcmp($gpname, $qentry['group_name']) != 0) {
            // Update Fields
            $where = array(
            'gid' => $gid,
            'language' => $language
            );
            $oGroup = QuestionGroup::model()->findByAttributes($where);
            $oGroup->description = $description;
            $oGroup->group_name = $gpname;
            $oGroup->save();

        } // Enf if modified
    } // end while qentry

    # translate 'question' and 'help' INSERTANS tags in questions
    $sql = "SELECT qid, language, question, help from {{questions}}
    WHERE sid=".$newsid." AND (question LIKE '%{$oldsid}X%' OR help LIKE '%{$oldsid}X%')";
    $result = dbExecuteAssoc($sql) or safeDie("Can't read question table in transInsertAns "); // Checked

    //while ($qentry = $res->FetchRow())
    $aResultData = $result->readAll();
    foreach ($aResultData as $qentry) {
        $question = $qentry['question'];
        $help = $qentry['help'];
        $qid = $qentry['qid'];
        $language = $qentry['language'];

        foreach ($fieldnames as $sOldFieldname=>$sNewFieldname) {
            $pattern = $sOldFieldname;
            $replacement = $sNewFieldname;
            $question = preg_replace('/'.$pattern.'/', $replacement, $question);
            $help = preg_replace('/'.$pattern.'/', $replacement, $help);
        }

        if (strcmp($question, $qentry['question']) != 0 ||
        strcmp($help, $qentry['help']) != 0) {
            // Update Field

            $data = array(
            'question' => $question,
            'help' => $help
            );

            $where = array(
            'qid' => $qid,
            'language' => $language
            );

            Question::model()->updateByPk($where, $data);

        } // Enf if modified
    } // end while qentry

    # translate 'answer' INSERTANS tags in answers
    $result = Answer::model()->oldNewInsertansTags($newsid, $oldsid);

    //while ($qentry = $res->FetchRow())
    foreach ($result as $qentry) {
        $answer = $qentry['answer'];
        $code = $qentry['code'];
        $qid = $qentry['qid'];
        $language = $qentry['language'];

        foreach ($fieldnames as $sOldFieldname=>$sNewFieldname) {
            $pattern = $sOldFieldname;
            $replacement = $sNewFieldname;
            $answer = preg_replace('/'.$pattern.'/', $replacement, $answer);
        }

        if (strcmp($answer, $qentry['answer']) != 0) {
            // Update Field

            $data = array(
            'answer' => $answer,
            'qid' => $qid
            );

            $where = array(
            'code' => $code,
            'language' => $language
            );

            Answer::model()->updateRecord($data, $where);

        } // Enf if modified
    } // end while qentry
}

/**
* Replaces EM variable codes in a current survey with a new one
*
* @param integer $iSurveyID The survey ID
* @param mixed $aCodeMap The codemap array (old_code=>new_code)
*/
function replaceExpressionCodes($iSurveyID, $aCodeMap)
{
    $arQuestions = Question::model()->findAll("sid=:sid", array(':sid'=>$iSurveyID));
    foreach ($arQuestions as $arQuestion) {
        $bModified = false;
        foreach ($aCodeMap as $sOldCode=>$sNewCode) {
            // Don't search/replace old codes that are too short or were numeric (because they would not have been usable in EM expressions anyway)
            if (strlen($sOldCode) > 1 && !is_numeric($sOldCode)) {
                $sOldCode = preg_quote($sOldCode, '~');
                $arQuestion->relevance=preg_replace("/\b{$sOldCode}/",$sNewCode,$arQuestion->relevance,-1,$iCount);
                $bModified = $bModified || $iCount;
                $arQuestion->question = preg_replace("~{[^}]*\K{$sOldCode}(?=[^}]*?})~", $sNewCode, $arQuestion->question, -1, $iCount);
                $bModified = $bModified || $iCount;
            }
        }
        if ($bModified) {
            $arQuestion->save();
        }
    }
    $arGroups = QuestionGroup::model()->findAll("sid=:sid", array(':sid'=>$iSurveyID));
    foreach ($arGroups as $arGroup) {
        $bModified = false;
        foreach ($aCodeMap as $sOldCode=>$sNewCode) {
            $sOldCode = preg_quote($sOldCode, '~');
            $arGroup->grelevance=preg_replace("~{[^}]*\K{$sOldCode}(?=[^}]*?})~",$sNewCode,$arGroup->grelevance,-1,$iCount);
            $bModified = $bModified || $iCount;
            $arGroup->description = preg_replace("~{[^}]*\K{$sOldCode}(?=[^}]*?})~", $sNewCode, $arGroup->description, -1, $iCount);
            $bModified = $bModified || $iCount;
        }
        if ($bModified) {
            $arGroup->save();
        }
    }
}


/**
* cleanLanguagesFromSurvey() removes any languages from survey tables that are not in the passed list
* @param string $sid - the currently selected survey
* @param string $availlangs - space separated list of additional languages in survey
* @return bool - always returns true
*/
function cleanLanguagesFromSurvey($sid, $availlangs)
{

    Yii::app()->loadHelper('database');
    //
    $sid = sanitize_int($sid);
    $baselang = Survey::model()->findByPk($sid)->language;
    $aLanguages = [];
    if (!empty($availlangs) && $availlangs != " ") {
        $availlangs = sanitize_languagecodeS($availlangs);
        $aLanguages = explode(" ", $availlangs);
        if ($aLanguages[count($aLanguages) - 1] == "") {
            array_pop($aLanguages);
        }
    }

    $sqllang = "language <> '".$baselang."' ";

    if (!empty($availlangs) && $availlangs != " ") {
        foreach ($aLanguages as $lang) {
            $sqllang .= "AND language <> '".$lang."' ";
        }
    }

    // Remove From Answer Table
    $query = "SELECT qid FROM {{questions}} WHERE sid='{$sid}' AND $sqllang";
    $qidresult = dbExecuteAssoc($query);

    foreach ($qidresult->readAll() as $qrow) {

        $myqid = $qrow['qid'];
        $query = "DELETE FROM {{answers}} WHERE qid='$myqid' AND $sqllang";
        dbExecuteAssoc($query);
    }

    // Remove From Questions Table
    $query = "DELETE FROM {{questions}} WHERE sid='{$sid}' AND $sqllang";
    dbExecuteAssoc($query);

    // Remove From QuestionGroup Table
    $query = "DELETE FROM {{groups}} WHERE sid='{$sid}' AND $sqllang";
    dbExecuteAssoc($query);

    return true;
}

/**
* fixLanguageConsistency() fixes missing groups, questions, answers, quotas & assessments for languages on a survey
* @param string $sid - the currently selected survey
* @param string $availlangs - space separated list of additional languages in survey - if empty all additional languages of a survey are checked against the base language
* @return bool - always returns true
*/
function fixLanguageConsistency($sid, $availlangs = '')
{
    $sid = sanitize_int($sid);


    if (trim($availlangs) != '') {
        $availlangs = sanitize_languagecodeS($availlangs);
        $langs = explode(" ", $availlangs);
        if ($langs[count($langs) - 1] == "") {
            array_pop($langs);
        }
    } else {
        $langs = Survey::model()->findByPk($sid)->additionalLanguages;
    }
    if (count($langs) == 0) {
        return true; // Survey only has one language
    }
    $baselang = Survey::model()->findByPk($sid)->language;
    $query = "SELECT * FROM {{groups}} WHERE sid='{$sid}' AND language='{$baselang}'  ORDER BY group_order";
    $result = Yii::app()->db->createCommand($query)->query();
    foreach ($result->readAll() as $group) {
        foreach ($langs as $lang) {

            $query = "SELECT count(gid) FROM {{groups}} WHERE sid='{$sid}' AND gid='{$group['gid']}' AND language='{$lang}'";
            $gresult = Yii::app()->db->createCommand($query)->queryScalar();
            if ($gresult < 1) {
                $data = array(
                'gid' => $group['gid'],
                'sid' => $group['sid'],
                'group_name' => $group['group_name'],
                'group_order' => $group['group_order'],
                'description' => $group['description'],
                'randomization_group' => $group['randomization_group'],
                'grelevance' => $group['grelevance'],
                'language' => $lang

                );
                switchMSSQLIdentityInsert('groups', true);
                Yii::app()->db->createCommand()->insert('{{groups}}', $data);
                switchMSSQLIdentityInsert('groups', false);
            }
        }
        reset($langs);
    }

    $quests = array();
    $query = "SELECT * FROM {{questions}} WHERE sid='{$sid}' AND language='{$baselang}' ORDER BY question_order";
    $result = Yii::app()->db->createCommand($query)->query()->readAll();
    if (count($result) > 0) {
        foreach ($result as $question) {
            array_push($quests, $question['qid']);
            foreach ($langs as $lang) {
                $query = "SELECT count(qid) FROM {{questions}} WHERE sid='{$sid}' AND qid='{$question['qid']}' AND language='{$lang}' AND scale_id={$question['scale_id']}";
                $gresult = Yii::app()->db->createCommand($query)->queryScalar();
                if ($gresult < 1) {
                    switchMSSQLIdentityInsert('questions', true);
                    $data = array(
                    'qid' => $question['qid'],
                    'sid' => $question['sid'],
                    'gid' => $question['gid'],
                    'type' => $question['type'],
                    'title' => $question['title'],
                    'question' => $question['question'],
                    'preg' => $question['preg'],
                    'help' => $question['help'],
                    'other' => $question['other'],
                    'mandatory' => $question['mandatory'],
                    'question_order' => $question['question_order'],
                    'language' => $lang,
                    'scale_id' => $question['scale_id'],
                    'parent_qid' => $question['parent_qid'],
                    'relevance' => $question['relevance']
                    );
                    Yii::app()->db->createCommand()->insert('{{questions}}', $data);
                }
            }
            reset($langs);
        }

        $sqlans = "";
        foreach ($quests as $quest) {
            $sqlans .= " OR qid = '".$quest."' ";
        }
        $query = "SELECT * FROM {{answers}} WHERE language='{$baselang}' and (".trim($sqlans, ' OR').") ORDER BY qid, code";
        $result = Yii::app()->db->createCommand($query)->query();
        foreach ($result->readAll() as $answer) {
            foreach ($langs as $lang) {
                $query = "SELECT count(qid) FROM {{answers}} WHERE code='{$answer['code']}' AND qid='{$answer['qid']}' AND language='{$lang}' AND scale_id={$answer['scale_id']}";
                $gresult = Yii::app()->db->createCommand($query)->queryScalar();
                if ($gresult < 1) {
                    $data = array(
                    'qid' => $answer['qid'],
                    'code' => $answer['code'],
                    'answer' => $answer['answer'],
                    'scale_id' => $answer['scale_id'],
                    'sortorder' => $answer['sortorder'],
                    'language' => $lang,
                    'assessment_value' =>  $answer['assessment_value']
                    );
                    Yii::app()->db->createCommand()->insert('{{answers}}', $data);
                }
            }
            reset($langs);
        }
    }
    /* Remove invalid question : can break survey */
    Survey::model()->findByPk($sid)->fixInvalidQuestions();

    $query = "SELECT * FROM {{assessments}} WHERE sid='{$sid}' AND language='{$baselang}'";
    $result = Yii::app()->db->createCommand($query)->query();
    foreach ($result->readAll() as $assessment) {
        foreach ($langs as $lang) {
            $query = "SELECT count(id) FROM {{assessments}} WHERE sid='{$sid}' AND id='{$assessment['id']}' AND language='{$lang}'";
            $gresult = Yii::app()->db->createCommand($query)->queryScalar();
            if ($gresult < 1) {
                $data = array(
                'id' => $assessment['id'],
                'sid' => $assessment['sid'],
                'scope' => $assessment['scope'],
                'gid' => $assessment['gid'],
                'name' => $assessment['name'],
                'minimum' => $assessment['minimum'],
                'maximum' => $assessment['maximum'],
                'message' => $assessment['message'],
                'language' => $lang
                );
                Yii::app()->db->createCommand()->insert('{{assessments}}', $data);
            }
        }
        reset($langs);
    }


    $query = "SELECT * FROM {{quota_languagesettings}} join {{quota}} q on quotals_quota_id=q.id WHERE q.sid='{$sid}' AND quotals_language='{$baselang}'";
    $result = Yii::app()->db->createCommand($query)->query();
    foreach ($result->readAll() as $qls) {
        foreach ($langs as $lang) {
            $query = "SELECT count(quotals_id) FROM {{quota_languagesettings}} WHERE quotals_quota_id='{$qls['quotals_quota_id']}' AND quotals_language='{$lang}'";
            $gresult = Yii::app()->db->createCommand($query)->queryScalar();
            if ($gresult < 1) {
                $data = array(
                'quotals_quota_id' => $qls['quotals_quota_id'],
                'quotals_name' => $qls['quotals_name'],
                'quotals_message' => $qls['quotals_message'],
                'quotals_url' => $qls['quotals_url'],
                'quotals_urldescrip' => $qls['quotals_urldescrip'],
                'quotals_language' => $lang
                );
                Yii::app()->db->createCommand()->insert('{{quota_languagesettings}}', $data);
            }
        }
        reset($langs);
    }

    return true;
}

/**
* This function switches identity insert on/off for the MSSQL database
*
* @param string $table table name (without prefix)
* @param boolean $state  Set to true to activate ID insert, or false to deactivate
*/
function switchMSSQLIdentityInsert($table, $state)
{
    if (in_array(Yii::app()->db->getDriverName(), array('mssql', 'sqlsrv', 'dblib'))) {
        if ($state === true) {
            // This needs to be done directly on the PDO object because when using CdbCommand or similar it won't have any effect
            Yii::app()->db->pdoInstance->exec('SET IDENTITY_INSERT '.Yii::app()->db->tablePrefix.$table.' ON');
        } else {
            // This needs to be done directly on the PDO object because when using CdbCommand or similar it won't have any effect
            Yii::app()->db->pdoInstance->exec('SET IDENTITY_INSERT '.Yii::app()->db->tablePrefix.$table.' OFF');
        }
    }
}

/**
 * Retrieves the last Insert ID realiable for cross-DB applications
 *
 * @param string $sTableName Needed for Postgres and MSSQL
 * @return string
 */
function getLastInsertID($sTableName)
{
    $sDBDriver = Yii::app()->db->getDriverName();
    if ($sDBDriver == 'mysql' || $sDBDriver == 'mysqli') {
        return Yii::app()->db->getLastInsertID();
    } else {
        return Yii::app()->db->getCommandBuilder()->getLastInsertID($sTableName);
    }
}

// TMSW Condition->Relevance:  This function is not needed?  Optionally replace this with call to EM to get similar info
/**
* getGroupDepsForConditions() get Dependencies between groups caused by conditions
* @param string $sid - the currently selected survey
* @param string $depgid - (optionnal) get only the dependencies applying to the group with gid depgid
* @param string $targgid - (optionnal) get only the dependencies for groups dependents on group targgid
* @param string $indexby - (optionnal) "by-depgid" for result indexed with $res[$depgid][$targgid]
*                   "by-targgid" for result indexed with $res[$targgid][$depgid]
* @return array - returns an array describing the conditions or NULL if no dependecy is found
*
* Example outupt assumin $index-by="by-depgid":
*Array
*(
*    [125] => Array             // Group Id 125 is dependent on
*        (
*            [123] => Array         // Group Id 123
*                (
*                    [depgpname] => G3      // GID-125 has name G3
*                    [targetgpname] => G1   // GID-123 has name G1
*                    [conditions] => Array
*                        (
*                            [189] => Array // Because Question Id 189
*                                (
*                                    [0] => 9   // Have condition 9 set
*                                    [1] => 10  // and condition 10 set
*                                    [2] => 14  // and condition 14 set
*                                )
*
*                        )
*
*                )
*
*            [124] => Array         // GID 125 is also dependent on GID 124
*                (
*                    [depgpname] => G3
*                    [targetgpname] => G2
*                    [conditions] => Array
*                        (
*                            [189] => Array // Because Question Id 189 have conditions set
*                                (
*                                    [0] => 11
*                                )
*
*                            [215] => Array // And because Question Id 215 have conditions set
*                                (
*                                    [0] => 12
*                                )
*
*                        )
*
*                )
*
*        )
*
*)
*
* Usage example:
*   * Get all group dependencies for SID $sid indexed by depgid:
*       $result=getGroupDepsForConditions($sid);
*   * Get all group dependencies for GID $gid in survey $sid indexed by depgid:
*       $result=getGroupDepsForConditions($sid,$gid);
*   * Get all group dependents on group $gid in survey $sid indexed by targgid:
*       $result=getGroupDepsForConditions($sid,"all",$gid,"by-targgid");
*/
function getGroupDepsForConditions($sid, $depgid = "all", $targgid = "all", $indexby = "by-depgid")
{
    $sid = sanitize_int($sid);
    $condarray = Array();
    $sqldepgid = "";
    $sqltarggid = "";
    if ($depgid != "all") { $depgid = sanitize_int($depgid); $sqldepgid = "AND tq.gid=$depgid"; }
    if ($targgid != "all") {$targgid = sanitize_int($targgid); $sqltarggid = "AND tq2.gid=$targgid"; }

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
        foreach ($condresult as $condrow) {

            switch ($indexby) {
                case "by-depgid":
                    $depgid = $condrow['depgid'];
                    $targetgid = $condrow['targgid'];
                    $depqid = $condrow['depqid'];
                    $cid = $condrow['cid'];
                    $condarray[$depgid][$targetgid]['depgpname'] = $condrow['depgpname'];
                    $condarray[$depgid][$targetgid]['targetgpname'] = $condrow['targgpname'];
                    $condarray[$depgid][$targetgid]['conditions'][$depqid][] = $cid;
                    break;

                case "by-targgid":
                    $depgid = $condrow['depgid'];
                    $targetgid = $condrow['targgid'];
                    $depqid = $condrow['depqid'];
                    $cid = $condrow['cid'];
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
* @param string $indexby - (optionnal) "by-depqid" for result indexed with $res[$depqid][$targqid]
*                   "by-targqid" for result indexed with $res[$targqid][$depqid]
* @return array - returns an array describing the conditions or NULL if no dependecy is found
*
* Example outupt assumin $index-by="by-depqid":
*Array
*(
*    [184] => Array     // Question Id 184
*        (
*            [183] => Array // Depends on Question Id 183
*                (
*                    [0] => 5   // Because of condition Id 5
*                )
*
*        )
*
*)
*
* Usage example:
*   * Get all questions dependencies for Survey $sid and group $gid indexed by depqid:
*       $result=getQuestDepsForConditions($sid,$gid);
*   * Get all questions dependencies for question $qid in survey/group $sid/$gid indexed by depqid:
*       $result=getGroupDepsForConditions($sid,$gid,$qid);
*   * Get all questions dependents on question $qid in survey/group $sid/$gid indexed by targqid:
*       $result=getGroupDepsForConditions($sid,$gid,"all",$qid,"by-targgid");
*/
function getQuestDepsForConditions($sid, $gid = "all", $depqid = "all", $targqid = "all", $indexby = "by-depqid", $searchscope = "samegroup")
{

    $condarray = Array();

    $baselang = Survey::model()->findByPk($sid)->language;
    $sqlgid = "";
    $sqldepqid = "";
    $sqltargqid = "";
    $sqlsearchscope = "";
    if ($gid != "all") {$gid = sanitize_int($gid); $sqlgid = "AND tq.gid=$gid"; }
    if ($depqid != "all") {$depqid = sanitize_int($depqid); $sqldepqid = "AND tq.qid=$depqid"; }
    if ($targqid != "all") {$targqid = sanitize_int($targqid); $sqltargqid = "AND tq2.qid=$targqid"; }
    if ($searchscope == "samegroup") {$sqlsearchscope = "AND tq2.gid=tq.gid"; }

    $condquery = "SELECT tq.qid as depqid, tq2.qid as targqid, tc.cid
    FROM {{conditions}} AS tc, {{questions}} AS tq, {{questions}} AS tq2
    WHERE tq.language='{$baselang}' AND tq2.language='{$baselang}' AND tc.qid = tq.qid AND tq.sid='$sid'
    AND  tq2.qid=tc.cqid $sqlsearchscope $sqlgid $sqldepqid $sqltargqid";
    $condresult = Yii::app()->db->createCommand($condquery)->query()->readAll();
    if (count($condresult) > 0) {
        foreach ($condresult as $condrow) {
            $depqid = $condrow['depqid'];
            $targetqid = $condrow['targqid'];
            $condid = $condrow['cid'];
            switch ($indexby) {
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

// TMSW Condition->Relevance:  This function is not needed - could replace with a message from EM output.
/**
* checkMoveQuestionConstraintsForConditions()
* @param string $sid - the currently selected survey
* @param string $qid - qid of the question you want to check possible moves
* @param string $newgid - (optionnal) get only constraints when trying to move to this particular GroupId
*                                     otherwise, get all moves constraints for this question
*
* @return array - returns an array describing the conditions
*                 Array
*                 (
*                   ['notAbove'] = null | Array
*                       (
*                         Array ( gid1, group_order1, qid1, cid1 )
*                       )
*                   ['notBelow'] = null | Array
*                       (
*                         Array ( gid2, group_order2, qid2, cid2 )
*                       )
*                 )
*
* This should be read as:
*    - this question can't be move above group gid1 in position group_order1 because of the condition cid1 on question qid1
*    - this question can't be move below group gid2 in position group_order2 because of the condition cid2 on question qid2
*
*/
function checkMoveQuestionConstraintsForConditions($sid, $qid, $newgid = "all")
{

    $resarray = Array();
    $resarray['notAbove'] = null; // defaults to no constraint
    $resarray['notBelow'] = null; // defaults to no constraint
    $sid = sanitize_int($sid);
    $qid = sanitize_int($qid);

    if ($newgid != "all") {
        $newgid = sanitize_int($newgid);
        $newgorder = getGroupOrder($sid, $newgid);
    } else {
        $newgorder = ''; // Not used in this case
    }

    $baselang = Survey::model()->findByPk($sid)->language;

    // First look for 'my dependencies': questions on which I have set conditions
    $condquery = "SELECT tq.qid as depqid, tq.gid as depgid, tg.group_order as depgorder, "
    . "tq2.qid as targqid, tq2.gid as targgid, tg2.group_order as targgorder, "
    . "tc.cid FROM "
    . "{{conditions}} AS tc, "
    . "{{questions}} AS tq, "
    . "{{questions}} AS tq2, "
    . "{{groups}} AS tg, "
    . "{{groups}} AS tg2 "
    . "WHERE tq.language='{$baselang}' AND tq2.language='{$baselang}' AND tc.qid = tq.qid AND tq.sid=$sid "
    . "AND  tq2.qid=tc.cqid AND tg.gid=tq.gid AND tg2.gid=tq2.gid AND tq.qid=$qid ORDER BY tg2.group_order DESC";

    $condresult = Yii::app()->db->createCommand($condquery)->query();

    foreach ($condresult->readAll() as $condrow) {
        // This Question can go up to the minimum GID on the 1st row
        $depqid = $condrow['depqid'];
        $targetgid = $condrow['targgid'];
        $targetgorder = $condrow['targgorder'];
        $condid = $condrow['cid'];
        if ($newgid != "all") {
        // Get only constraints when trying to move to this group
            if ($newgorder < $targetgorder) {
                $resarray['notAbove'][] = Array($targetgid, $targetgorder, $depqid, $condid);
            }
        } else {
        // get all moves constraints
            $resarray['notAbove'][] = Array($targetgid, $targetgorder, $depqid, $condid);
        }
    }

    // Secondly look for 'questions dependent on me': questions that have conditions on my answers
    $condquery = "SELECT tq.qid as depqid, tq.gid as depgid, tg.group_order as depgorder, "
    . "tq2.qid as targqid, tq2.gid as targgid, tg2.group_order as targgorder, "
    . "tc.cid FROM {{conditions}} AS tc, "
    . "{{questions}} AS tq, "
    . "{{questions}} AS tq2, "
    . "{{groups}} AS tg, "
    . "{{groups}} AS tg2 "
    . "WHERE tq.language='{$baselang}' AND tq2.language='{$baselang}' AND tc.qid = tq.qid AND tq.sid=$sid "
    . "AND  tq2.qid=tc.cqid AND tg.gid=tq.gid AND tg2.gid=tq2.gid AND tq2.qid=$qid ORDER BY tg.group_order";

    $condresult = Yii::app()->db->createCommand($condquery)->query();

    foreach ($condresult->readAll() as $condrow) {
        // This Question can go down to the maximum GID on the 1st row
        $depqid = $condrow['depqid'];
        $depgid = $condrow['depgid'];
        $depgorder = $condrow['depgorder'];
        $condid = $condrow['cid'];
        if ($newgid != "all") {
        // Get only constraints when trying to move to this group
            if ($newgorder > $depgorder) {
                $resarray['notBelow'][] = Array($depgid, $depgorder, $depqid, $condid);
            }
        } else {
        // get all moves constraints
            $resarray['notBelow'][] = Array($depgid, $depgorder, $depqid, $condid);
        }
    }
    return $resarray;
}

/**
* Get a list of all user groups
* @returns array
*/
function getUserGroupList()
{
    $sQuery = "SELECT distinct a.ugid, a.name, a.owner_id FROM {{user_groups}} AS a LEFT JOIN {{user_in_groups}} AS b ON a.ugid = b.ugid WHERE 1=1 ";
    if (!Permission::model()->hasGlobalPermission('superadmin', 'read')) {
        $sQuery .= "AND uid = ".Yii::app()->session['loginID'];
    }
    $sQuery .= " ORDER BY name";

    $sresult = Yii::app()->db->createCommand($sQuery)->query(); //Checked
    if (!$sresult) {return "Database Error"; }
    $aGroupNames = [];
    foreach ($sresult->readAll() as $row) {
        $aGroupNames[] = $row;
    }
    $simplegidarray = array();
    if (isset($aGroupNames)) {
        foreach ($aGroupNames as $gn) {
            $simplegidarray[] = $gn['ugid'];
        }
    }
    return $simplegidarray;
}

// TODO use Yii model forms
function getGroupUserList($ugid)
{
    Yii::app()->loadHelper('database');


    $ugid = sanitize_int($ugid);
    $surveyidquery = "SELECT a.uid, a.users_name, a.full_name FROM {{users}} AS a LEFT JOIN (SELECT uid AS id FROM {{user_in_groups}} WHERE ugid = {$ugid}) AS b ON a.uid = b.id WHERE id IS NULL ORDER BY a.users_name";

    $surveyidresult = dbExecuteAssoc($surveyidquery); //Checked
    if (!$surveyidresult) {return "Database Error"; }
    $surveyselecter = "";
    $aSurveyNames = [];
    foreach ($surveyidresult->readAll() as $row) {
        $aSurveyNames[] = $row;
    }
    //$surveynames = $surveyidresult->GetRows();
    if (isset($aSurveyNames)) {
        foreach ($aSurveyNames as $sv) {
            $surveyselecter .= "<option";
            $surveyselecter .= " value='{$sv['uid']}'>".\CHtml::encode($sv['users_name'])." (".\CHtml::encode($sv['full_name']).")</option>\n";
        }
    }
    $surveyselecter = "<option value='-1' selected='selected'>".gT("Please choose...")."</option>\n".$surveyselecter;
    return $surveyselecter;
}

/**
* Run an arbitrary sequence of semicolon-delimited SQL commands
*
* Assumes that the input text (file or string) consists of
* a number of SQL statements ENDING WITH SEMICOLONS.  The
* semicolons MUST be the last character in a line.
* Lines that are blank or that start with "#" or "--" (postgres) are ignored.
* Only tested with mysql dump files (mysqldump -p -d limesurvey)
* Function kindly borrowed by Moodle
* @param string $sqlfile The path where a file with sql commands can be found on the server.
* @param string $sqlstring If no path is supplied then a string with semicolon delimited sql
* commands can be supplied in this argument.
* @return bool Returns true if database was modified successfully.
*/
function modifyDatabase($sqlfile = '', $sqlstring = '')
{
    Yii::app()->loadHelper('database');


    global $siteadminemail;
    global $siteadminname;
    global $codeString;
    global $modifyoutput;

    $success = true; // Let's be optimistic
    $modifyoutput = '';
    $lines = [];
    if (!empty($sqlfile)) {
        if (!is_readable($sqlfile)) {
            $success = false;
            echo '<p>Tried to modify database, but "'.$sqlfile.'" doesn\'t exist!</p>';
            return $success;
        } else {
            $lines = file($sqlfile);
        }
    } else {
        $sqlstring = trim($sqlstring);
        if ($sqlstring{strlen($sqlstring) - 1} != ";") {
            $sqlstring .= ";"; // add it in if it's not there.
        }
        $lines[] = $sqlstring;
    }

    $command = '';

    foreach ($lines as $line) {
        $line = rtrim($line);
        $length = strlen($line);

        if ($length and $line[0] <> '#' and substr($line, 0, 2) <> '--') {
            if (substr($line, $length - 1, 1) == ';') {
                $line = substr($line, 0, $length - 1); // strip ;
                $command .= $line;
                $command = str_replace('prefix_', Yii::app()->db->tablePrefix, $command); // Table prefixes
                $command = str_replace('$defaultuser', Yii::app()->getConfig('defaultuser'), $command);
                $command = str_replace('$defaultpass', hash('sha256', Yii::app()->getConfig('defaultpass')), $command);
                $command = str_replace('$siteadminname', $siteadminname, $command);
                $command = str_replace('$siteadminemail', $siteadminemail, $command);
                $command = str_replace('$defaultlang', Yii::app()->getConfig('defaultlang'), $command);
                $command = str_replace('$databasetabletype', Yii::app()->db->getDriverName(), $command);

                try
                {   Yii::app()->db->createCommand($command)->query(); //Checked
                    $command = htmlspecialchars($command);
                    $modifyoutput .= ". ";
                } catch (CDbException $e) {
                    $command = htmlspecialchars($command);
                    $modifyoutput .= "<br />".sprintf(gT("SQL command failed: %s"), "<span style='font-size:10px;'>".$command."</span>", "<span style='color:#ee0000;font-size:10px;'></span><br/>");
                    $success = false;
                }

                $command = '';
            } else {
                $command .= $line;
            }
        }
    }

    return $success;

}

/**
* Returns labelsets for given language(s), or for all if null
*
* @param string $languages
* @return array
*/
function getLabelSets($languages = null)
{
    $aLanguages = array();
    if (!empty($languages)) {
        $languages = sanitize_languagecodeS($languages);
        $aLanguages = explode(' ', trim($languages));
    }

    $criteria = new CDbCriteria;
    $criteria->order = "label_name";
    foreach ($aLanguages as $k => $item) {
        $criteria->params[':lang_like1_'.$k] = "% $item %";
        $criteria->params[':lang_'.$k] = $item;
        $criteria->params[':lang_like2_'.$k] = "% $item";
        $criteria->params[':lang_like3_'.$k] = "$item %";
        $criteria->addCondition("
        ((languages like :lang_like1_$k) or
        (languages = :lang_$k) or
        (languages like :lang_like2_$k) or
        (languages like :lang_like3_$k))");
    }

    $result = LabelSet::model()->findAll($criteria);
    $labelsets = array();
    foreach ($result as $row) {
            $labelsets[] = array($row->lid, $row->label_name);
    }
    return $labelsets;
}

/**
 * get the header
 * @param bool $meta : not used in any call (2016-10-18)
 * @return string
 */
function getHeader($meta = false)
{
    /* Todo : move this to layout/public.html */
    global $surveyid;
    Yii::app()->loadHelper('surveytranslator');

    // Set Langage // TODO remove one of the Yii::app()->session see bug #5901
    if (Yii::app()->session['survey_'.$surveyid]['s_lang']) {
        $languagecode = Yii::app()->session['survey_'.$surveyid]['s_lang'];
    } elseif (isset($surveyid) && $surveyid && Survey::model()->findByPk($surveyid)) {
        $languagecode = Survey::model()->findByPk($surveyid)->language;
    } else {
        $languagecode = Yii::app()->getConfig('defaultlang');
    }
    $header = "<!DOCTYPE html>\n";
    $class = "no-js $languagecode";
    $header .= "<html lang=\"{$languagecode}\"";

    if (getLanguageRTL($languagecode)) {
        $header .= " dir=\"rtl\" ";
        $class .= " dir-rtl";
    } else {
        $header .= " dir=\"ltr\" ";
        $class .= " dir-ltr";
    }
    $header .= " class=\"{$class}\">\n";
    $header .= "\t<head>\n";
    Yii::app()->clientScript->registerScriptFile(Yii::app()->getConfig("generalscripts").'nojs.js', CClientScript::POS_HEAD);
    if ($meta) {
            $header .= $meta;
    }
    return $header;
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
    global $rooturl, $homeurl;
    $headelements = App()->getController()->renderPartial('/survey/system/print_survey/header', array(), true, true);
    return $headelements;
}

/**
 * This function returns the Footer as result string
 * If you want to echo the Footer use doFooter()!
 * @return string
 */
function getFooter()
{
    return "\n\n\t</body>\n</html>\n";
}

function doFooter()
{
    echo getFooter();
}



/**
* Retrieve a HTML <OPTION> list of survey admin users
*
* @param boolean $bIncludeOwner If the survey owner should be included
* @param boolean $bIncludeSuperAdmins If Super admins should be included
* @param int $surveyid
* @return string
*/
function getSurveyUserList($bIncludeSuperAdmins = true, $surveyid)
{

    $surveyid = (int) $surveyid;

    $sSurveyIDQuery = "SELECT a.uid, a.users_name, a.full_name FROM {{users}} AS a
    LEFT OUTER JOIN (SELECT uid AS id FROM {{permissions}} WHERE entity_id = {$surveyid} and entity='survey') AS b ON a.uid = b.id
    WHERE id IS NULL ";
    if (!$bIncludeSuperAdmins) {
        // @todo: Adjust for new permission system - not urgent since it it just display
        //   $sSurveyIDQuery.='and superadmin=0 ';
    }
    $sSurveyIDQuery .= 'ORDER BY a.users_name';
    $oSurveyIDResult = Yii::app()->db->createCommand($sSurveyIDQuery)->query(); //Checked
    $aSurveyIDResult = $oSurveyIDResult->readAll();

    $surveyselecter = "";
    $authorizedUsersList = [];

    if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == true) {
        $authorizedUsersList = getUserList('onlyuidarray');
    }

    $svexist = false;
    foreach ($aSurveyIDResult as $sv) {
        if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == false ||
            in_array($sv['uid'], $authorizedUsersList)) {
            $surveyselecter .= "<option";
            $surveyselecter .= " value='{$sv['uid']}'>".\CHtml::encode($sv['users_name'])." ".\CHtml::encode($sv['full_name'])."</option>\n";
            $svexist = true;
        }
    }

    if ($svexist) {
        $surveyselecter = "<option value='-1' selected='selected'>".gT("Please choose...")."</option>\n".$surveyselecter;
    } else {
        $surveyselecter = "<option value='-1'>".gT("None")."</option>\n".$surveyselecter;
    }

    return $surveyselecter;
}

/**
 * Return HTML <option> list of user groups
 * @param string $outputformat
 * @param int $surveyid
 * @return string|array
 */
function getSurveyUserGroupList($outputformat = 'htmloptions', $surveyid)
{

    $surveyid = sanitize_int($surveyid);

    $surveyidquery = "SELECT a.ugid, a.name, MAX(d.ugid) AS da
    FROM {{user_groups}} AS a
    LEFT JOIN (
    SELECT b.ugid
    FROM {{user_in_groups}} AS b
    LEFT JOIN (SELECT * FROM {{permissions}}
    WHERE entity_id = {$surveyid} and entity='survey') AS c ON b.uid = c.uid WHERE c.uid IS NULL
    ) AS d ON a.ugid = d.ugid GROUP BY a.ugid, a.name HAVING MAX(d.ugid) IS NOT NULL";
    $surveyidresult = Yii::app()->db->createCommand($surveyidquery)->query(); //Checked
    $aResult = $surveyidresult->readAll();

    $authorizedGroupsList = [];
    if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == true) {
        $authorizedGroupsList = getUserGroupList();
    }

    $svexist = false;
    $surveyselecter = "";
    $simpleugidarray = [];
    foreach ($aResult as $sv) {
        if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == false ||
        in_array($sv['ugid'], $authorizedGroupsList)) {
            $surveyselecter .= "<option";
            $surveyselecter .= " value='{$sv['ugid']}'>{$sv['name']}</option>\n";
            $simpleugidarray[] = $sv['ugid'];
            $svexist = true;
        }
    }

    if ($svexist) {
        $surveyselecter = "<option value='-1' selected='selected'>".gT("Please choose...")."</option>\n".$surveyselecter;
    } else {
        $surveyselecter = "<option value='-1'>".gT("None")."</option>\n".$surveyselecter;
    }

    if ($outputformat == 'simpleugidarray') {
        return $simpleugidarray;
    } else {
        return $surveyselecter;
    }
}



/**
* This function fixes the group ID and type on all subquestions
* Optimized for minimum memory usage even on huge databases
*/
function fixSubquestions()
{
    $surveyidresult = Yii::app()->db->createCommand()
    ->select('sq.qid, q.gid , q.type ')
    ->from('{{questions}} sq')
    ->join('{{questions}} q', 'sq.parent_qid=q.qid')
    ->where('sq.parent_qid>0 AND (sq.gid!=q.gid or sq.type!=q.type)')
    ->limit(10000)
    ->query();
    $aRecords = $surveyidresult->readAll();
    while (count($aRecords) > 0) {
        foreach ($aRecords as $sv) {
            Yii::app()->db->createCommand("update {{questions}} set type='{$sv['type']}', gid={$sv['gid']} where qid={$sv['qid']}")->execute();
        }
        $surveyidresult = Yii::app()->db->createCommand()
        ->select('sq.qid, q.gid , q.type ')
        ->from('{{questions}} sq')
        ->join('{{questions}} q', 'sq.parent_qid=q.qid')
        ->where('sq.parent_qid>0 AND (sq.gid!=q.gid or sq.type!=q.type)')
        ->limit(10000)
        ->query();
        $aRecords = $surveyidresult->readAll();
    }

}

/**
* Must use ls_json_encode to json_encode content, otherwise LimeExpressionManager will think that the associative arrays are expressions and try to parse them.
*/
function ls_json_encode($content)
{
    if (is_string($content) && get_magic_quotes_gpc()) {
        $content = stripslashes($content);
    }
    $ans = json_encode($content);
    $ans = str_replace(array('{', '}'), array('{ ', ' }'), $ans);
    return $ans;
}

/**
 * Decode a json string, sometimes needs stripslashes
 *
 * @param string $jsonString
 * @return mixed
 */
function json_decode_ls($jsonString)
{
    $decoded = json_decode($jsonString, true);

    if (is_null($decoded) && !empty($jsonString)) {
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
        $aEncodings = array(
        "armscii8" => gT("ARMSCII-8 Armenian"),
        "ascii" => gT("US ASCII"),
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
        // Sort list of encodings
        asort($aEncodings);
        $aEncodings = array("auto" => gT("(Automatic)")) + $aEncodings;
        return $aEncodings;
    }


/**
* Ellipsize String
*
* This public static function will strip tags from a string, split it at its max_length and ellipsize
*
* @param    string  $sString        string to ellipsize
* @param    integer $iMaxLength       max length of string
* @param    integer   $fPosition       int (1|0) or float, .5, .2, etc for position to split
* @param    string  $sEllipsis      ellipsis ; Default '...'
* @return    string        ellipsized string
*/
function ellipsize($sString, $iMaxLength, $fPosition = 1, $sEllipsis = '&hellip;')
{
    // Strip tags
    $sString = trim(strip_tags($sString));
    // Is the string long enough to ellipsize?
    if (mb_strlen($sString, 'UTF-8') <= $iMaxLength + 3) {
        return $sString;
    }

    $iStrLen = mb_strlen($sString, 'UTF-8');
    $sBegin = mb_substr($sString, 0, (int) floor($iMaxLength * $fPosition), 'UTF-8');
    $sEnd = mb_substr($sString, $iStrLen - ($iMaxLength - mb_strlen($sBegin, 'UTF-8')), $iStrLen, 'UTF-8');
    return $sBegin.$sEllipsis.$sEnd;
}

/**
* This function tries to returns the 'real' IP address under all configurations
* Do not rely security-wise on the detected IP address as except for REMOTE_ADDR all fields could be manipulated by the web client
*/
function getIPAddress()
{
    $sIPAddress = '127.0.0.1';
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)!==false) {
        //check IP address from share internet
        $sIPAddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)!==false) {
        //Check IP address passed from proxy
        $sIPAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)!==false) {
        $sIPAddress = $_SERVER['REMOTE_ADDR'];
    }
    return $sIPAddress;
}


/**
* This function tries to find out a valid language code for the language of the browser used
* If it cannot find it it will return the default language from global settings
*
*/
function getBrowserLanguage()
{
    $sLanguage = Yii::app()->getRequest()->getPreferredLanguage();
    Yii::app()->loadHelper("surveytranslator");
    $aLanguages = getLanguageData();
    if (!isset($aLanguages[$sLanguage])) {
        $sLanguage = str_replace('_', '-', $sLanguage);
        if (strpos($sLanguage, '-') !== false) {
            $aLanguage = explode('-', $sLanguage);
            $aLanguage[1] = strtoupper($aLanguage[1]);
            $sLanguage = implode('-', $aLanguage);
        }
        if (!isset($aLanguages[$sLanguage])) {
            $sLanguage = substr($sLanguage, 0, strpos($sLanguage, '-'));
            if (!isset($aLanguages[$sLanguage])) {
                $sLanguage = Yii::app()->getConfig('defaultlang');
            }
        }
    }
    return $sLanguage;
}

function array_diff_assoc_recursive($array1, $array2)
{
    $difference = array();
    foreach ($array1 as $key => $value) {
        if (is_array($value)) {
            if (!isset($array2[$key]) || !is_array($array2[$key])) {
                $difference[$key] = $value;
            } else {
                $new_diff = array_diff_assoc_recursive($value, $array2[$key]);
                if (!empty($new_diff)) {
                                    $difference[$key] = $new_diff;
                }
            }
        } else if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
            $difference[$key] = $value;
        }
    }
    return $difference;
}

/**
 * Calculate folder size
 * NB: If this function is changed, please notify LimeSurvey GmbH.
 *     An exact copy of this function is used to calculate storage
 *     limit on LimeSurvey Pro hosting.
 * @param string $dir Folder
 * @return integer Size in bytes.
 */
function folderSize($dir)
{
    $size = 0;
    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
        if (is_file($each)) {
            // NB: stat() can be used to calculate disk usage (instead
            // of file size - it's not the same thing).
            //$stat = stat($each);
            //$tmpsize = $stat[11] * $stat[12] / 8;
            //$size += $tmpsize;
            $size += filesize($each);
        } else {
            $size += folderSize($each);
        }
    }
    return $size;
}

/**
 * Format size in human readable format.
 * @param int $bytes
 * @param int $decimals
 * @return string
 */
function humanFilesize($bytes, $decimals = 2)
{
    $sz = 'BKMGTP';
    //$factor = floor((strlen($bytes) - 1) / 3);
    $factor = 2;
    $string = sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).@$sz[$factor];
    $aLangData = getLanguageData();
    $radix = getRadixPointData($aLangData[Yii::app()->session['adminlang']]['radixpoint']);
    return str_replace('.', $radix['separator'], $string);
}

/**
* This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
*
* @param string $sSize
* @return integer The value in bytes
*/
function convertPHPSizeToBytes($sSize)
{
    //
    $sSuffix = strtoupper(substr($sSize, -1));
    if (!in_array($sSuffix, array('P', 'T', 'G', 'M', 'K'))) {
        return (int) $sSize;
    }
    $iValue = substr($sSize, 0, -1);
    switch ($sSuffix) {
        case 'P':
            $iValue *= 1024;
            // Fallthrough intended
        case 'T':
            $iValue *= 1024;
            // Fallthrough intended
        case 'G':
            $iValue *= 1024;
            // Fallthrough intended
        case 'M':
            $iValue *= 1024;
            // Fallthrough intended
        case 'K':
            $iValue *= 1024;
            break;
    }
    return (int) $iValue;
}

function getMaximumFileUploadSize()
{
    return min(convertPHPSizeToBytes(ini_get('post_max_size')), convertPHPSizeToBytes(ini_get('upload_max_filesize')));
}

/**
 * Decodes token attribute data because due to bugs in the past it can be written in JSON or be serialized - future format should be JSON as serialized data can be exploited
 *
 * @param string $oTokenAttributeData The original token attributes as stored in the database
 * @return array|mixed
 */
function decodeTokenAttributes($oTokenAttributeData)
{
    if (trim($oTokenAttributeData) == '') {
        return array();
    }
    if (substr($oTokenAttributeData, 0, 1) != '{' && substr($oTokenAttributeData, 0, 1) != '[') {
        $sSerialType = getSerialClass($oTokenAttributeData);
        if ($sSerialType == 'array') {
// Safe to decode
            $aReturnData = @unserialize($oTokenAttributeData);
        } else {
// Something else, might be unsafe
            return array();
        }
    } else {
            $aReturnData = @json_decode($oTokenAttributeData, true);
    }
    if ($aReturnData === false || $aReturnData === null) {
        return array();
    }
    return $aReturnData;
}

/**
 * @param string $sSerial
 * @return string|null
 */
function getSerialClass($sSerial)
{
    $aTypes = array('s' => 'string', 'a' => 'array', 'b' => 'bool', 'i' => 'int', 'd' => 'float', 'N;' => 'NULL');

    $aParts = explode(':', $sSerial, 4);
    return isset($aTypes[$aParts[0]]) ? $aTypes[$aParts[0]] : (isset($aParts[2]) ? trim($aParts[2], '"') : null);
}

/**
* Force Yii to create a new CSRF token by removing the old one
*
*/
function regenerateCSRFToken()
{
    // Expire the CSRF cookie
    $cookie = new CHttpCookie('YII_CSRF_TOKEN', '');
    $cookie->expire = time() - 3600;
    Yii::app()->request->cookies['YII_CSRF_TOKEN'] = $cookie;
}

/**
* A function to remove ../ or ./ from paths to prevent directory traversal
*
* @param mixed $path
*/
function get_absolute_path($path)
{
    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
    $absolutes = array();
    foreach ($parts as $part) {
        if ('.' == $part) {
            continue;
        }
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    return implode(DIRECTORY_SEPARATOR, $absolutes);
}

/**
* Check if string is JSON array
*
* @param string $str
* @return bool
*/
function isJson($str) {
    $json = json_decode($str);
    return $json && $str != $json;
}

/**
* Check if array is associative
*
* @param array $array
* @return bool
*/
function isAssociativeArray($array){
    foreach ($array as $key => $value) {
        if (is_string($key)) {
            return true;
        }
    }
    return false;
}


/**
* Create a directory in tmp dir using a random string
*
* @param  string $dir      the temp directory (if empty will use the one from configuration)
* @param  string $prefix   wanted prefix for the directory
* @param  int    $mode     wanted  file mode for this directory
* @return string           the path of the created directory
*/
function createRandomTempDir($dir=null, $prefix = '', $mode = 0700)
{

    $sDir = (empty($dir)) ? Yii::app()->getConfig('tempdir') : get_absolute_path ($dir);

    if (substr($sDir, -1) != DIRECTORY_SEPARATOR) {
        $sDir .= DIRECTORY_SEPARATOR;
    }

    do {
        $sRandomString = getRandomString();
        $path = $sDir.$prefix.$sRandomString;
    }
    while (!mkdir($path, $mode));

    return $path;
}

/**
 * Generate a random string, using openssl if available, else using md5
 * @param  int    $length wanted lenght of the random string (only for openssl mode)
 * @return string
 */
function getRandomString($length=32){

    if ( function_exists('openssl_random_pseudo_bytes') ) {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        for($i=0;$i<$length;$i++){
            $token .= $codeAlphabet[crypto_rand_secure(0,strlen($codeAlphabet))];
        }
    }else{
        $token = md5(uniqid(rand(), true));
    }
    return $token;
}

/**
 * Get a random number between two values using openssl_random_pseudo_bytes
 * @param  int    $min
 * @param  int    $max
 * @return string
 */
function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 0) return $min; // not so random...
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
}

/**
 * Test if a given zip file is Zip Bomb
 * see comment here : http://php.net/manual/en/function.zip-entry-filesize.php
 * @param string $zip_filename
 * @return int
 */
function isZipBomb($zip_filename)
{
    return ( get_zip_originalsize($zip_filename) >  Yii::app()->getConfig('maximum_unzipped_size') );
}

/**
 * Get the original size of a zip archive to prevent Zip Bombing
 * see comment here : http://php.net/manual/en/function.zip-entry-filesize.php
 * @param string $filename
 * @return int
 */
function get_zip_originalsize($filename) {

    if ( function_exists ('zip_entry_filesize') ){
        $size = 0;
        $resource = zip_open($filename);

        if ( ! is_int($resource) ) {
            while ($dir_resource = zip_read($resource)) {
                $size += zip_entry_filesize($dir_resource);
            }
            zip_close($resource);
        }

        return $size;
    }else{
        if ( YII_DEBUG ){
            Yii::app()->setFlashMessage("Warning! The PHP Zip extension is not installed on this server. You're not protected from ZIP bomb attacks.", 'error');
        }
    }

    return -1;
}

/**
 * PHP7 has created a little nasty bomb with count throwing erroros on uncountables
 * This is to "fix" this problem
 * 
 * @param mixed $element
 * @return integer counted element
 * @author
 */
function safecount($element)
{
    $isCountable = is_array($element) || $element instanceof Countable;
    if($isCountable) {
        return count($element);
    }
    return 0;
}
