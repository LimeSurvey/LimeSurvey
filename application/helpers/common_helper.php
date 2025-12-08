<?php

if (!defined('BASEPATH')) {
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
 * Returns $sToTranslate translated to $sLanguage (defaults to lang set in session) escaped with $sEscapeMode
 *
 * @param string $sToTranslate
 * @param string $sEscapeMode Valid values are html (this is the default, js and unescaped)
 * @param string $sLanguage
 * @return string
 */
function gT($sToTranslate, $sEscapeMode = 'html', $sLanguage = null)
{
    if (($sToTranslate == '')) {
        return '';
    }
    return quoteText(Yii::t('', $sToTranslate, array(), null, $sLanguage), $sEscapeMode);
}

/**
 * As gT(), but echoes directly
 *
 * @param string $sToTranslate
 * @param string $sEscapeMode
 * @return void
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
* getSurveyList() Queries the database (survey table) for a list of existing surveys
*
* @param boolean $bReturnArray If set to true an array instead of an HTML option list is given back (unused by core (2023-04-12))
* @return string|array This string is returned containing <option></option> formatted list of existing surveys
*
*/
function getSurveyList($bReturnArray = false)
{
    static $cached = null;
    $timeadjust = getGlobalSetting('timeadjust');
    App()->setLanguage((Yii::app()->session['adminlang'] ?? 'en'));
    $surveynames = array();

    if (is_null($cached)) {
        $criteria = new CDBCriteria();
        $criteria->select = ['sid','language', 'active', 'expires','startdate'];
        $criteria->with = ['languagesettings' => [
                'select' => 'surveyls_title',
                'where' => 't.language = languagesettings.language'
            ]
        ];
        $surveyidresult = Survey::model()
            ->permission(Yii::app()->user->getId())
            ->findAll($criteria);
        foreach ($surveyidresult as $result) {
            if (isset($result->languagesettings[$result->language])) {
                $surveynames[] = array_merge(
                    $result->attributes,
                    $result->languagesettings[$result->language]->attributes
                );
            }
        }

        usort($surveynames, function ($a, $b) {
            return strcmp((string) $a['surveyls_title'], (string) $b['surveyls_title']);
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
        $surveylstitle = CHtml::encode($sv['surveyls_title']) . " [" . $sv['sid'] . "]";
        if ($sv['active'] != 'Y') {
            $inactivesurveys .= "<option ";
            if (Yii::app()->user->getId() == $sv['owner_id']) {
                $inactivesurveys .= " class='mysurvey emphasis inactivesurvey'";
            }
            $inactivesurveys .= " value='{$sv['sid']}'>{$surveylstitle}</option>\n";
        } elseif ($sv['expires'] != '' && $sv['expires'] < dateShift((string) date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)) {
            $expiredsurveys .= "<option ";
            if (Yii::app()->user->getId() == $sv['owner_id']) {
                $expiredsurveys .= " class='mysurvey emphasis expiredsurvey'";
            }
            $expiredsurveys .= " value='{$sv['sid']}'>{$surveylstitle}</option>\n";
        } else {
            $activesurveys .= "<option ";
            if (Yii::app()->user->getId() == $sv['owner_id']) {
                $activesurveys .= " class='mysurvey emphasis activesurvey'";
            }
            $activesurveys .= " value='{$sv['sid']}'>{$surveylstitle}</option>\n";
        }
    } // End Foreach

    //Only show each activesurvey group if there are some
    if ($activesurveys != '') {
        $surveyselecter .= "<optgroup label='" . gT("Active") . "' class='activesurveyselect'>\n";
        $surveyselecter .= $activesurveys . "</optgroup>";
    }
    if ($expiredsurveys != '') {
        $surveyselecter .= "<optgroup label='" . gT("Expired") . "' class='expiredsurveyselect'>\n";
        $surveyselecter .= $expiredsurveys . "</optgroup>";
    }
    if ($inactivesurveys != '') {
        $surveyselecter .= "<optgroup label='" . gT("Inactive") . "' class='inactivesurveyselect'>\n";
        $surveyselecter .= $inactivesurveys . "</optgroup>";
    }
    $surveyselecter = "<option selected='selected' value=''>" . gT("Please choose...") . "</option>\n" . $surveyselecter;
    return $surveyselecter;
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
    $qresult = QuestionGroup::model()->findAllByAttributes(array('sid' => $surveyid), array('order' => 'group_order'));

    $i = 0;
    $iPrev = -1;
    foreach ($qresult as $qrow) {
        $qrow = $qrow->attributes;
        if ($gid == $qrow['gid']) {
            $iPrev = $i - 1;
        }
        $i += 1;
    }

    if ($iPrev >= 0) {
        $GidPrev = $qresult[$iPrev]->gid;
    } else {
        $GidPrev = "";
    }
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
    $qresult = QuestionGroup::model()->findAllByAttributes(array('sid' => $surveyid), array('order' => 'group_order'));

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
    $url = preg_replace('/&amp;/i', '&', (string) $url);
    $stack = explode('?', $url);
    $calledscript = array_shift($stack);
    $query = array_shift($stack);
    $aqueryitems = explode('&', (string) $query);
    $postArray = [];
    $getArray = [];
    foreach ($aqueryitems as $queryitem) {
        $stack = explode('=', $queryitem);
        $paramname = array_shift($stack);
        $value = array_shift($stack);
        if (in_array($paramname, array(Yii::app()->getComponent('urlManager')->routeVar))) {
            $getArray[$paramname] = $value;
        } else {
            $postArray[$paramname] = $value;
        }
    }
    if (!empty($getArray)) {
        $calledscript = $calledscript . "?" . implode('&', array_map(
            function ($v, $k) {
                return $k . '=' . $v;
            },
            $getArray,
            array_keys($getArray)
        ));
    }
    $callscript = "window.LS.sendPost(\"" . $calledscript . "\",\"\"," . json_encode($postArray) . ");";
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
    $aRows = Survey::model()->findAll();
    $iTotalSize = 0.0;
    foreach ($aRows as $aRow) {
        $sFilesPath = Yii::app()->getConfig("uploaddir") . '/surveys/' . $aRow->sid . '/files';
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
 * @param integer $surveyid The survey ID
 * @return int Next free sortorder digit
 */
function getMaxGroupOrder($surveyid)
{
    $queryResult = QuestionGroup::model()->find(array(
        'condition' => 'sid = :sid',
        'params' => array(':sid' => $surveyid),
        'order' => 'group_order desc',
        'limit' => '1'
    ));

    $current_max = !is_null($queryResult) ? $queryResult->group_order : -1;
    $current_max += 1;
    return $current_max;
}


/**
* Queries the database for the sortorder of a group.
*
* @param mixed $gid  The groups ID
* @return int The sortorder digit
*/
function getGroupOrder($gid)
{
    $arGroup = QuestionGroup::model()->findByAttributes(array('gid' => $gid)); //Checked
    if (empty($arGroup)) {
        return 0;
    }
    return (int) $arGroup->group_order;
}

/**
* Queries the database for the maximum sort order of questions inside question group.
*
* @param integer $gid
* @return integer
*/
function getMaxQuestionOrder($gid)
{
    $gid = (int) $gid;
    $max_sql = "SELECT max( question_order ) AS max FROM {{questions}} WHERE gid={$gid}";
    $current_max = Yii::app()->db->createCommand($max_sql)->queryScalar(); //Checked
    if ($current_max == false) {
        return 0;
    }
    return (int) $current_max;
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
* @deprecated Don't use anymore. Only usage left in printabel survey where it needs to be replaced
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


    $class_first = ' class="' . $wrapperclass . '"';
    if ($columns > 1 && !is_null($column_style)) {
        if ($column_style == 'ul') {
            $ul = '-ul';
        } else {
            $ul = '';
        }
        $class_first = ' class="' . $wrapperclass . ' cols-' . $columns . $ul . ' first"';
        $class = ' class="' . $wrapperclass . ' cols-' . $columns . $ul . '"';
        $class_last_ul = ' class="' . $wrapperclass . ' cols-' . $columns . $ul . ' last"';
        $class_last_table = ' class="' . $wrapperclass . ' cols-' . $columns . ' last"';
    } else {
        $class = ' class="' . $wrapperclass . '"';
        $class_last_ul = ' class="' . $wrapperclass . '"';
        $class_last_table = ' class="' . $wrapperclass . '"';
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
        case 'ul':
            if ($columns > 1) {
                $wrapper['col-devide'] = "\n</ul>\n\n<ul$class>\n";
                $wrapper['col-devide-last'] = "\n</ul>\n\n<ul$class_last_ul>\n";
            }
            break;

        case 'table':
            $table_cols = '';
            for ($cols = $columns; $cols > 0; --$cols) {
                switch ($cols) {
                    case $columns:
                        $table_cols .= "\t<col$class_first />\n";
                        break;
                    case 1:
                        $table_cols .= "\t<col$class_last_table />\n";
                        break;
                    default:
                        $table_cols .= "\t<col$class />\n";
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
}

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
     * @deprecated Should be done by CSS/JS on display. Needs to be fixed.
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
    if ($longest_length < strlen(trim(strip_tags((string) $new_string)))) {
        $longest_length = strlen(trim(strip_tags((string) $new_string)));
    };
    return $longest_length;
}

//FIXME rename and/or document this
function getGroupList3($gid, $surveyid)
{
    $gid = sanitize_int($gid);
    $surveyid = sanitize_int($surveyid);

    if (!$surveyid) {
        $surveyid = returnGlobal('sid', true);
    }
    $groupselecter = "";
    $sBaseLanguage = Survey::model()->findByPk($surveyid)->language;

    $gidresult = QuestionGroup::model()->findAllByAttributes(array('sid' => $surveyid), array('order' => 'group_order'));
    foreach ($gidresult as $gv) {
        $groupselecter .= "<option";
        if ($gv->gid == $gid) {
            $groupselecter .= " selected='selected'";
        }
        $groupselecter .= " value='" . $gv->gid . "'>" . htmlspecialchars((string) $gv->questiongroupl10ns[$sBaseLanguage]->group_name) . " (ID:" . $gv->gid . ")</option>\n";
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
    if (!$surveyid) {
        $surveyid = returnGlobal('sid', true);
    }

    $gidresult = QuestionGroup::model()->findAll(array('condition' => 'sid=:surveyid',
    'order' => 'group_order',
    'params' => array(':surveyid' => $surveyid))); //Checked)
    foreach ($gidresult as $oGroup) {
        $aAttributes = $oGroup->attributes;
        $groupselecter .= "<option";
        if ($aAttributes['gid'] == $gid) {
            $groupselecter .= " selected='selected'";
            $gvexist = 1;
        }
        $link = Yii::app()->getController()->createUrl("/questionGroupsAdministration/view/surveyid/" . $surveyid . "/gid/" . $aAttributes['gid']);
        $groupselecter .= " value='{$link}'>";
        $groupselecter .= htmlspecialchars(strip_tags((string) $oGroup->questiongroupl10ns[$language]->group_name));
        $groupselecter .= "</option>\n";
    }
    if ($groupselecter) {
        $link = Yii::app()->getController()->createUrl("/surveyAdministration/view/surveyid/" . $surveyid);
        if (!isset($gvexist)) {
            $groupselecter = "<option selected='selected'>" . gT("Please choose...") . "</option>\n" . $groupselecter;
        } else {
            $groupselecter .= "<option value='{$link}'>" . gT("None") . "</option>\n";
        }
    }
    return $groupselecter;
}

/**
 * Returns a user list. If 'usercontrolSameGroupPolicy' is set and set to true, only users which are in the same
 * group as me (--> logged in user) will be returned. Superadmin always gets the full list of users.
 *
 * @param $outputformat string could be 'onlyuidarray' which only returns array with userids, default is 'fullinfoarray'
 * @return array returns a list of user ids (param='onlyuidarray') or a list with full user details (e.g. uid, name, full_name etc.)
 */
function getUserList($outputformat = 'fullinfoarray')
{
    if (!empty(Yii::app()->session['loginID'])) {
        $myuid = sanitize_int(Yii::app()->session['loginID']);
    }
    $usercontrolSameGroupPolicy = App()->getConfig('usercontrolSameGroupPolicy');
    if (
        !Permission::model()->hasGlobalPermission('superadmin', 'read') && isset($usercontrolSameGroupPolicy) &&
        $usercontrolSameGroupPolicy == true
    ) {
        if (isset($myuid)) {
            $userGroupList = getUserGroupList();
            $criteria = new CDBCriteria();
            $criteria->order = 'full_name, users_name, t.uid';
            $criteria->with = 'groups';
            /* users in usergroup */
            $criteria->addInCondition('groups.ugid', $userGroupList);
            /* childs of this user */
            $criteria->compare('parent_id', $myuid, false, 'OR');
            /* himself */
            $criteria->compare('t.uid', $myuid, false, 'OR');
            $oUsers = User::model()->findAll($criteria);
        } else {
            return array(); // Or die maybe
        }
    } else {
        $oUsers = User::model()->findAll([
            'order' => 'full_name, users_name, t.uid'
        ]);
    }

    $userlist = array();
    $userlist[0] = "Reserved for logged in user";
    foreach ($oUsers as $oUser) {
        $srow = $oUser->getAttributes();
        if ($outputformat != 'onlyuidarray') {
            if ($srow['uid'] != Yii::app()->session['loginID']) {
                $userlist[] = array(
                    "user" => $srow['users_name'],
                    "uid" => $srow['uid'],
                    "email" => $srow['email'],
                    "password" => $srow['password'],
                    "full_name" => $srow['full_name'],
                    "parent_id" => $srow['parent_id']
                );
            } else {
                $userlist[0] = array(
                    "user" => $srow['users_name'],
                    "uid" => $srow['uid'],
                    "email" => $srow['email'],
                    "password" => $srow['password'],
                    "full_name" => $srow['full_name'],
                    "parent_id" => $srow['parent_id']
                );
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
* @param boolean $force If true, don't use memoization
* @return array|bool Returns array with survey info or false, if survey does not exist
*/
function getSurveyInfo($surveyid, $languagecode = '', $force = false)
{
    static $staticSurveyInfo = array(); // Use some static

    if ($force) {
        $staticSurveyInfo[$surveyid] = null;
    }

    $surveyid = sanitize_int($surveyid);
    $languagecode = sanitize_languagecode($languagecode);
    $thissurvey = false;
    $oSurvey = Survey::model()->findByPk($surveyid);
    // Do job only if this survey exist
    if (!$oSurvey) {
        return false;
    }
    //todo: here ipanonymize is wrong in $oSurvey->aOptions where is that initialized ???
    $aSurveyOptions = $oSurvey->aOptions;
    // if no language code is set then get the base language one
    if ((!isset($languagecode) || $languagecode == '')) {
        $languagecode = $oSurvey->language;
    }

    if (isset($staticSurveyInfo[$surveyid][$languagecode])) {
        $thissurvey = $staticSurveyInfo[$surveyid][$languagecode];
    } else {
        $result = SurveyLanguageSetting::model()->with('survey')->findByPk(array('surveyls_survey_id' => $surveyid, 'surveyls_language' => $languagecode));
        $resultBaseLanguage = SurveyLanguageSetting::model()->with('survey')->findByPk(array('surveyls_survey_id' => $surveyid, 'surveyls_language' => $oSurvey->language));
        if (is_null($result)) {
            // When additional language was added, but not saved it does not exists
            // We should revert to the base language then
            $languagecode = $oSurvey->language;
            $result = $resultBaseLanguage;
        }
        if ($result) {
            $aSurveyAtrributes = array_replace($result->survey->attributes, $aSurveyOptions);
            $thissurvey = array_merge($aSurveyAtrributes, $result->attributes);
            $thissurvey['name'] = $thissurvey['surveyls_title'];
            if (($languagecode != $oSurvey->language) && empty($thissurvey['name']) || $thissurvey['name'] == '') {
                $thissurvey['name'] = $resultBaseLanguage->surveyls_title;
            }
            $thissurvey['description'] = $thissurvey['surveyls_description'];
            if (($languagecode != $oSurvey->language) && empty($thissurvey['description']) || $thissurvey['description'] == '') {
                $thissurvey['description'] = $resultBaseLanguage->surveyls_description;
            }
            $thissurvey['welcome'] = $thissurvey['surveyls_welcometext'];
            // if there is no welcome message for an additional language, we try to get it from the base language
            if (($languagecode != $oSurvey->language) && empty($thissurvey['welcome']) || $thissurvey['welcome'] == '') {
                $thissurvey['welcome'] = $resultBaseLanguage->surveyls_welcometext;
            }
            // if there is no end message for an additional language, we try to get it from the base language
            if (($languagecode != $oSurvey->language) && empty($thissurvey['surveyls_endtext']) || $thissurvey['surveyls_endtext'] == '') {
                $thissurvey['surveyls_endtext'] = $resultBaseLanguage->surveyls_endtext;
            }
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
            $thissurvey['googleanalyticsapikey'] = $oSurvey->getGoogleanalyticsapikey();
            $thissurvey['hastokenstable'] = $oSurvey->hasTokensTable;
            $thissurvey['filltoken'] = (Yii::app()->request->getParam('filltoken') === 'true');
            if (!isset($thissurvey['adminname'])) {
                $thissurvey['adminname'] = Yii::app()->getConfig('siteadminemail');
            }
            if (!isset($thissurvey['adminemail'])) {
                $thissurvey['adminemail'] = Yii::app()->getConfig('siteadminname');
            }
            if (!isset($thissurvey['urldescrip']) || $thissurvey['urldescrip'] == '') {
                $thissurvey['urldescrip'] = $thissurvey['surveyls_url'];
            }

            if ($result->survey->owner_id == -1 && !empty($oSurvey->oOptions->owner_id)) {
                $thissurvey['owner_username'] = User::model()->find("uid=:uid", array(':uid' => $oSurvey->oOptions->owner_id))['users_name'];
            } elseif (!empty($result->survey->owner->users_name)) {
                $thissurvey['owner_username'] = $result->survey->owner->users_name;
            } else {
                $thissurvey['owner_username'] = '';
            }


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
* @todo Move to defaulttexts helper
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
* Used by :
* - remotecontrol_handle->export_statistics with merging group and question attributes (all in same array)
* - checkQuestions() in activate_helper function with ?
* @param mixed $a
* @param mixed $b
* @return int
*/
function groupOrderThenQuestionOrder($a, $b)
{
    if (isset($a->group['group_order']) && isset($b->group['group_order'])) {
        $GroupResult = strnatcasecmp((string) $a->group['group_order'], (string) $b->group['group_order']);
    } else {
        $GroupResult = "";
    }
    if ($GroupResult == 0) {
        $TitleResult = strnatcasecmp((string) $a["question_order"], (string) $b["question_order"]);
        return $TitleResult;
    }
    return $GroupResult;
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

    if (is_null($urlParam) || $stringname == '' || ($bUrlParamIsArray && $bRestrictToString)) {
        return null;
    }

    if (in_array($stringname, ['sid', 'gid', 'oldqid', 'qid', 'tid', 'lid', 'ugid','thisstep', 'scenario', 'cqid', 'cid', 'qaid', 'scid'])) {
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
    } elseif (
        $stringname == "htmleditormode" ||
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
}


function sendSurveyHttpHeaders()
{
    if (!headers_sent()) {
        // Default headers für surveys
        $headers = [
                     'Cache-Control: no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0',
                     'Pragma: no-cache',
                     'Content-Type: text/html; charset=utf-8'
                    ];
        if (Yii::app()->getConfig('x_frame_options', 'allow') == 'sameorigin') {
            $headers[] = 'X-Frame-Options: SAMEORIGIN';
        }
        // plugins can modify the
        $event = new PluginEvent('beforeSurveyHttpHeaders');
        $event->set('headers', $headers);
        App()->getPluginManager()->dispatchEvent($event);
        $headers = $event->get('headers', []);
        if (is_array($headers)) {
            foreach ($headers as $header) {
                header($header);
            }
        }
    }
}

/**
* @param integer $iSurveyID The Survey ID
* @param string $sFieldCode Field code of the particular field
* @param string $sValue The stored response value
* @param string $sLanguage Initialized limesurvey_lang object for the resulting response data
* @param Question|null $question
* @return string
*/
function getExtendedAnswer($iSurveyID, $sFieldCode, $sValue, $sLanguage, $question = null)
{

    if ($sValue == null || $sValue == '') {
        return '';
    }
    $survey = Survey::model()->findByPk($iSurveyID);
    $rawQuestions = Question::model()->findAll("sid = :sid", [":sid" => $iSurveyID]);
    $found = false;
    foreach ($rawQuestions as $rawQuestion) {
        $found = $found || (strpos($sFieldCode, "Q{$rawQuestion->qid}") === 0);
    }
    //Fieldcode used to determine question, $sValue used to match against answer code
    //Returns NULL if question type does not suit
    if ($found) {
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
            case Question::QT_D_DATE:
                if (trim($sValue) != '') {
                    $qidattributes = QuestionAttribute::model()->getQuestionAttributes($question ?? $fields['qid']);
                    $dateformatdetails = getDateFormatDataForQID($qidattributes, $iSurveyID);
                    $sValue = convertDateTimeFormat($sValue, "Y-m-d H:i:s", $dateformatdetails['phpdate']);
                }
                break;
            case Question::QT_K_MULTIPLE_NUMERICAL:
            case Question::QT_N_NUMERICAL:
                // Fix the value : Value is stored as decimal in SQL
                if ($sValue[0] === ".") {
                    // issue #15685 mssql SAVE 0.01 AS .0100000000, set it at 0.0100000000
                    $sValue = "0" . $sValue;
                }
                if (trim($sValue) != '') {
                    if (strpos($sValue, ".") !== false) {
                        $sValue = rtrim(rtrim($sValue, "0"), ".");
                    }
                }
                break;
            case Question::QT_L_LIST:
            case Question::QT_EXCLAMATION_LIST_DROPDOWN:
            case Question::QT_O_LIST_WITH_COMMENT:
            case Question::QT_I_LANGUAGE:
            case Question::QT_R_RANKING:
                $this_answer = Answer::model()->getAnswerFromCode($fields['qid'], $sValue, $sLanguage);
                if ($sValue == "-oth-") {
                    $this_answer = gT("Other", null, $sLanguage);
                }
                break;
            case Question::QT_M_MULTIPLE_CHOICE:
            case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:
                switch ($sValue) {
                    case "Y":
                        $this_answer = gT("Yes", null, $sLanguage);
                        break;
                }
                break;
            case Question::QT_Y_YES_NO_RADIO:
                switch ($sValue) {
                    case "Y":
                        $this_answer = gT("Yes", null, $sLanguage);
                        break;
                    case "N":
                        $this_answer = gT("No", null, $sLanguage);
                        break;
                    default:
                        $this_answer = gT("No answer", null, $sLanguage);
                }
                break;
            case Question::QT_G_GENDER:
                switch ($sValue) {
                    case "M":
                        $this_answer = gT("Male", null, $sLanguage);
                        break;
                    case "F":
                        $this_answer = gT("Female", null, $sLanguage);
                        break;
                    default:
                        $this_answer = gT("No answer", null, $sLanguage);
                }
                break;
            case Question::QT_C_ARRAY_YES_UNCERTAIN_NO:
                switch ($sValue) {
                    case "Y":
                        $this_answer = gT("Yes", null, $sLanguage);
                        break;
                    case "N":
                        $this_answer = gT("No", null, $sLanguage);
                        break;
                    case "U":
                        $this_answer = gT("Uncertain", null, $sLanguage);
                        break;
                }
                break;
            case Question::QT_E_ARRAY_INC_SAME_DEC:
                switch ($sValue) {
                    case "I":
                        $this_answer = gT("Increase", null, $sLanguage);
                        break;
                    case "D":
                        $this_answer = gT("Decrease", null, $sLanguage);
                        break;
                    case "S":
                        $this_answer = gT("Same", null, $sLanguage);
                        break;
                }
                break;
            case Question::QT_F_ARRAY:
            case Question::QT_H_ARRAY_COLUMN:
            case Question::QT_1_ARRAY_DUAL:
                if (isset($fields['scale_id'])) {
                    $iScaleID = $fields['scale_id'];
                } else {
                    $iScaleID = 0;
                }
                $this_answer = Answer::model()->getAnswerFromCode($fields['qid'], $sValue, $sLanguage, $iScaleID);
                if ($sValue == "-oth-") {
                    $this_answer = gT("Other", null, $sLanguage);
                }
                break;
            case Question::QT_VERTICAL_FILE_UPLOAD: //File upload
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
                            $size = "";
                            if ($file['size'] && strval(floatval($file['size'])) == strval($file['size'])) {
                                // avoid to throw PHP error if size is invalid
                                $size = sprintf('%s KB', round($file['size']));
                            }
                            $sValue .= rawurldecode((string) $file['name']) .
                            ' (' . $size . ' ) ' .
                            strip_tags((string) $file['title']);
                            if (trim(strip_tags((string) $file['comment'])) != "") {
                                $sValue .= ' - ' . strip_tags((string) $file['comment']);
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
                $sValue = convertDateTimeFormat($sValue, "Y-m-d H:i:s", $dateformatdetails['phpdate'] . ' H:i:s');
            }
            break;
    }
    if (isset($this_answer)) {
        return $this_answer . " [$sValue]";
    } else {
        return $sValue;
    }
}

/**
* Validate an email address - also supports IDN email addresses
* @deprecated : use LimeMailer::validateAddress($sEmailAddress);
* @returns True/false for valid/invalid
*
* @param mixed $sEmailAddress  Email address to check
*/
function validateEmailAddress($sEmailAddress)
{
    return LimeMailer::validateAddress($sEmailAddress);
}

/**
* Validate an list of email addresses - either as array or as semicolon-limited text
* @deprecated : use LimeMailer::validateAddresses($aEmailAddressList);
* @return string List with valid email addresses - invalid email addresses are filtered - false if none of the email addresses are valid
*
* @param string $aEmailAddressList  Email address to check
* @returns array
*/
function validateEmailAddresses($aEmailAddressList)
{
    return LimeMailer::validateAddresses($aEmailAddressList);
}

/**
 * This functions generates a a summary containing the SGQA for questions of a survey, enriched with options per question
 * It can be used for the generation of statistics. Derived from StatisticsUserController
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
        $myfield = "Q{$flt['qid']}";
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $aAdditionalLanguages = array_filter(explode(" ", (string) $oSurvey->additional_languages));
        if (is_null($sLanguage) || !in_array($sLanguage, $aAdditionalLanguages)) {
            $sLanguage = $oSurvey->language;
        }
        switch ($flt['type']) {
            case Question::QT_K_MULTIPLE_NUMERICAL: // Multiple Numerical
            case Question::QT_Q_MULTIPLE_SHORT_TEXT: // Multiple short text
                //get answers
                $result = Question::model()->getQuestionsForStatistics('title as code, question as answer', "parent_qid=$flt[qid] AND language = '{$sLanguage}'", 'question_order');

                //go through all the (multiple) answers
                foreach ($result as $row) {
                    $myfield2 = $flt['type'] . $myfield . reset($row);
                    $allfields[] = $myfield2;
                }
                break;
            case Question::QT_A_ARRAY_5_POINT: // Array of 5 point choice questions
            case Question::QT_B_ARRAY_10_CHOICE_QUESTIONS: // Array of 10 point choice questions
            case Question::QT_C_ARRAY_YES_UNCERTAIN_NO: // ARRAY OF YES\No\gT("Uncertain") QUESTIONS
            case Question::QT_E_ARRAY_INC_SAME_DEC: // Array of Increase/Same/Decrease questions
            case Question::QT_F_ARRAY: // Array
            case Question::QT_H_ARRAY_COLUMN: // Array (By Column)
                //get answers
                $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[qid] AND language = '{$sLanguage}'", 'question_order');

                //go through all the (multiple) answers
                foreach ($result as $row) {
                    $myfield2 = $myfield . "_S" . $row['qid'];
                    $allfields[] = $myfield2;
                }
                break;
                // all "free text" types (T, U, S)  get the same prefix ("T")
            case Question::QT_T_LONG_FREE_TEXT: // Long free text
            case Question::QT_U_HUGE_FREE_TEXT: // Huge free text
            case Question::QT_S_SHORT_FREE_TEXT: // Short free text
                $myfield = "T$myfield";
                $allfields[] = $myfield;
                break;
            case Question::QT_SEMICOLON_ARRAY_TEXT:  // Array (Text)
            case Question::QT_COLON_ARRAY_NUMBERS:  // Array (Numbers)
                $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[qid] AND language = '{$sLanguage}' AND scale_id = 0", 'question_order');

                foreach ($result as $row) {
                    $fresult = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[qid] AND language = '{$sLanguage}' AND scale_id = 1", 'question_order');
                    foreach ($fresult as $frow) {
                        $myfield2 = $myfield . reset($row) . "_" . $frow['title'];
                        $allfields[] = $myfield2;
                    }
                }
                break;
            case Question::QT_R_RANKING: // Ranking
                //get some answers
                $result = Answer::model()->getQuestionsForStatistics('code, answer', "qid=$flt[qid] AND language = '{$sLanguage}'", 'sortorder, answer');
                //get number of answers
                //loop through all answers. if there are 3 items to rate there will be 3 statistics
                $i = 0;
                foreach ($result as $row) {
                    $i++;
                    $myfield2 = "R" . $myfield . $i . "-" . strlen($i);
                    $allfields[] = $myfield2;
                }

                break;
                //Boilerplate questions are only used to put some text between other questions -> no analysis needed
            case Question::QT_X_TEXT_DISPLAY:  //This is a boilerplate question and it has no business in this script
                break;
            case Question::QT_1_ARRAY_DUAL: // Dual scale
                //get answers
                $result = Question::model()->getQuestionsForStatistics('title, question', "parent_qid=$flt[qid] AND language = '{$sLanguage}'", 'question_order');
                //loop through answers
                foreach ($result as $row) {
                    //----------------- LABEL 1 ---------------------
                    $myfield2 = $myfield . reset($row) . "#0";
                    $allfields[] = $myfield2;
                    //----------------- LABEL 2 ---------------------
                    $myfield2 = $myfield . reset($row) . "#1";
                    $allfields[] = $myfield2;
                }   //end WHILE -> loop through all answers
                break;

            case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:  //P - Multiple choice with comments
            case Question::QT_M_MULTIPLE_CHOICE:  //M - Multiple choice
            case Question::QT_N_NUMERICAL:  //N - Numerical input
            case Question::QT_D_DATE:  //D - Date
                $myfield2 = $flt['type'] . $myfield;
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
 * Returns the field name of a table's question or subquestion
 * 
 * @param string $tableName
 * @param string $fieldName
 * @param array $questions a collection of questions containing a question and its subquestions
 * @param int $sid
 * @param int $gid
 * @param bool $cd is it the condition designer
 * 
 * @return string the field's name
 */
function getFieldName(string $tableName, string $fieldName, array $questions, int $sid, int $gid, bool $cd = false)
{
    $newFieldName = "";
    if (strpos($tableName, "timings") !== false) {
        $X = explode("X", $fieldName);
        $newFieldName = ((count($X) > 2) ? "Q" : "G") . $X[count($X) - 1];
    } else {
        $rootQuestion = $questions[0];
        $questionIndex = 0;
        while ($questionIndex < count($questions)) {
            if (!$questions[$questionIndex]->parent_qid) {
                if ($rootQuestion->parent_qid || ($rootQuestion->qid < $questions[$questionIndex]->qid)) {
                    $rootQuestion = $questions[$questionIndex];
                }
            }
            $questionIndex++;
        }
        $qid = $rootQuestion->qid;
        switch ($rootQuestion->type) {
            case \Question::QT_1_ARRAY_DUAL:
            case \Question::QT_5_POINT_CHOICE:
            case \Question::QT_L_LIST:
            case \Question::QT_M_MULTIPLE_CHOICE:
            case \Question::QT_N_NUMERICAL:
            case \Question::QT_O_LIST_WITH_COMMENT:
            case \Question::QT_EXCLAMATION_LIST_DROPDOWN:
                $currentQuestion = null;
                $length = strlen("{$sid}X{$gid}X{$qid}");
                $hashPos = strpos($fieldName, '#');
                foreach ($questions as $question) {
                    if ($hashPos && ($question->title === substr($fieldName, $length, ($hashPos !== false) ? ($hashPos - $length) : null))) {
                        $currentQuestion = $question;
                    } else if ($question->title === substr($fieldName, strlen("{$sid}X{$gid}X{$qid}"))) {
                        $currentQuestion = $question;
                    }
                }
                $hashTags = explode("#", $fieldName);
                if ($currentQuestion === null) {
                    $newFieldName = "Q{$qid}";
                    if (strlen($fieldName) > strlen("{$sid}X{$gid}X{$qid}")) {
                        $newFieldName .= "_C" . substr($fieldName, strlen("{$sid}X{$gid}X{$qid}"));
                    }
                } else {
                    $newFieldName = "Q{$qid}_S{$currentQuestion->qid}";
                    if (count($hashTags)) {
                        for ($index = 1; $index < count($hashTags); $index++) {
                            $newFieldName .= "#{$hashTags[$index]}";
                        }
                    }
                }
                break;
            case \Question::QT_A_ARRAY_5_POINT:
            case \Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:
            case \Question::QT_C_ARRAY_YES_UNCERTAIN_NO:
            case \Question::QT_E_ARRAY_INC_SAME_DEC:
            case \Question::QT_F_ARRAY:
            case \Question::QT_H_ARRAY_COLUMN:
            case \Question::QT_K_MULTIPLE_NUMERICAL:
            case \Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:
            case \Question::QT_Q_MULTIPLE_SHORT_TEXT:
                $code = substr($fieldName, strlen("{$sid}X{$gid}X{$qid}"));
                $commentText = false;
                $currentQuestion = null;
                $excludeSubquestion = false;
                foreach ($questions as $question) {
                    if (($question->title === $code) || ($code === "")) {
                        $currentQuestion = $question;
                    } elseif (in_array($code, ["other", "comment", "othercomment", $question->title . "other", $question->title . "comment", $question->title . "othercomment"])) {
                        $currentQuestion = $question;
                        $commentText = $code;
                        if (strpos($code, $question->title) === 0) {
                            $commentText = substr($code, strlen($question->title));
                        } else {
                            $excludeSubquestion = true;
                        }
                    }
                }
                if ($currentQuestion) {
                    $newFieldName = "Q{$qid}" . ($excludeSubquestion ? "" : "_S{$currentQuestion->qid}");
                    if ($commentText) {
                        $newFieldName = $newFieldName . "_C" . $commentText;
                    }
                }
                break;
            case \Question::QT_SEMICOLON_ARRAY_TEXT:
            case \Question::QT_COLON_ARRAY_NUMBERS:
                if (strpos($tableName, "timings") !== false) {
                    $newFieldName = "Q{$qid}_Ctime";
                } else {
                    $suffix = explode("_", substr($fieldName, strlen("{$sid}X{$gid}X{$qid}")));
                    $scales = [];
                    foreach ($questions as $question) {
                        if (($suffix[$question->scale_id] ?? null) === $question->title) {
                            $scales[$question->scale_id] = $question->qid;
                        }
                    }
                    $suffixText = "";
                    for ($index = 0; $index < count($scales); $index++) {
                        $suffixText .= "_S" . $scales[$index];
                    }
                    $newFieldName = "Q{$qid}" . $suffixText;
                }
                break;
            case \Question::QT_D_DATE:
            case \Question::QT_G_GENDER:
            case \Question::QT_I_LANGUAGE:
            case \Question::QT_S_SHORT_FREE_TEXT:
            case \Question::QT_T_LONG_FREE_TEXT:
            case \Question::QT_U_HUGE_FREE_TEXT:
            case \Question::QT_X_TEXT_DISPLAY:
            case \Question::QT_Y_YES_NO_RADIO:
            case \Question::QT_VERTICAL_FILE_UPLOAD:
            case \Question::QT_ASTERISK_EQUATION:
                $isRoot = ((strpos($tableName, "timings") !== false) || (($rootQuestion->parent_qid ?? 0) == "0"));
                $newFieldName = ($isRoot ? "Q{$qid}" : "Q{$rootQuestion->parent_qid}");
                $suffix = "";
                $isComment = false;
                if (!$isRoot) {
                    $length = strlen("{$sid}X{$gid}X{$qid}");
                    $hashPos = strpos($fieldName, '#');
                    $code = substr($fieldName, $length, ($hashPos !== false) ? ($hashPos - $length) : 2000);
                    $suffix = "_C{$code}";
                    foreach ($questions as $question) {
                        if ($question->title === $code) {
                            $suffix = "_S{$question->qid}";
                        } elseif ($question->title . "comment" === $code) {
                            $suffix = "_S{$question->qid}";
                            $isComment = true;
                        }
                    }
                }
                $newFieldName .= $suffix;
                if (strpos($fieldName, "time") !== false) {
                    $newFieldName .= "_Ctime";
                } elseif (strpos($fieldName, "filecount") !== false) {
                    $newFieldName .= "_Cfilecount";
                }
                if ($isComment) {
                    $newFieldName .= "_Ccomment";
                }
                break;
            case \Question::QT_R_RANKING:
                $prefix = ((strpos($tableName, "timing") !== false) ? "C" : "R");
                $index = substr($fieldName, strlen("{$sid}X{$gid}X{$qid}"));
                $aid = $cd ? $index : $questions[0]->answers[(substr($fieldName, strlen("{$sid}X{$gid}X{$qid}")) - 1)]->aid;
                $newFieldName = "Q{$qid}_{$prefix}" . $aid;
                break;
        }
    }
    return $newFieldName;
}



/**
* This function generates an array containing the fieldcode, and matching data in the same order as the activate script
*
* @param Survey $survey Survey ActiveRecord model
* @param string $style 'short' (default) or 'full' - full creates extra information like default values
* @param ?boolean $force_refresh - Forces to really refresh the array, not just take the session copy
* @param bool|int $questionid Limit to a certain qid only (for question preview) - default is false
* @param string $sLanguage The language to use
* @param array $aDuplicateQIDs
* @param array $surveyReplacements needed to replace qids with the correct values, for example during import
* @param bool $includeAllAnswerOptions Include all answer options in the fieldmap (e.g. ignore min-max answers values) - default is false
* @return array
*/
function createFieldMap($survey, $style = 'short', $force_refresh = false, $questionid = false, $sLanguage = '', &$aDuplicateQIDs = array(), $surveyReplacements = [], $includeAllAnswerOptions = false)
{

    static $aQIDReplacementMappings = [];
    $sLanguage = sanitize_languagecode($sLanguage);
    $surveyid = $survey->sid;
    if (!isset($aQIDReplacementMappings[$surveyid])) {
        $aQIDReplacementMappings[$surveyid] = $surveyReplacements;
    }
    $aQIDReplacements = $aQIDReplacementMappings[$surveyid];
    //checks to see if fieldmap has already been built for this page.
    if (isset(Yii::app()->session['fieldmap-' . $surveyid . $sLanguage]) && !$force_refresh && $questionid === false) {
        return Yii::app()->session['fieldmap-' . $surveyid . $sLanguage];
    }
    /* Check if $sLanguage is a survey valid language (else $fieldmap is empty) */
    if ($sLanguage == '' || !in_array($sLanguage, $survey->allLanguages)) {
        $sLanguage = $survey->language;
    }
    $fieldmap = [];
    $fieldmap["id"] = array("fieldname" => "id", 'sid' => $surveyid, 'type' => "id", "gid" => "", "qid" => "", "aid" => "");
    if ($style == "full") {
        $fieldmap["id"]['title'] = "";
        $fieldmap["id"]['question'] = gT("Response ID");
        $fieldmap["id"]['group_name'] = "";
    }

    $fieldmap["submitdate"] = array("fieldname" => "submitdate", 'type' => "submitdate", 'sid' => $surveyid, "gid" => "", "qid" => "", "aid" => "");
    if ($style == "full") {
        $fieldmap["submitdate"]['title'] = "";
        $fieldmap["submitdate"]['question'] = gT("Date submitted");
        $fieldmap["submitdate"]['group_name'] = "";
    }

    $fieldmap["lastpage"] = array("fieldname" => "lastpage", 'sid' => $surveyid, 'type' => "lastpage", "gid" => "", "qid" => "", "aid" => "");
    if ($style == "full") {
        $fieldmap["lastpage"]['title'] = "";
        $fieldmap["lastpage"]['question'] = gT("Last page");
        $fieldmap["lastpage"]['group_name'] = "";
    }

    $fieldmap["startlanguage"] = array("fieldname" => "startlanguage", 'sid' => $surveyid, 'type' => "startlanguage", "gid" => "", "qid" => "", "aid" => "");
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
        $fieldmap["token"] = array("fieldname" => "token", 'sid' => $surveyid, 'type' => "token", "gid" => "", "qid" => "", "aid" => "");
        if ($style == "full") {
            $fieldmap["token"]['title'] = "";
            $fieldmap["token"]['question'] = gT("Access code");
            $fieldmap["token"]['group_name'] = "";
        }
    }
    if ($prow['datestamp'] == "Y") {
        $fieldmap["startdate"] = array("fieldname" => "startdate",
        'type' => "startdate",
        'sid' => $surveyid,
        "gid" => "",
        "qid" => "",
        "aid" => "");
        if ($style == "full") {
            $fieldmap["startdate"]['title'] = "";
            $fieldmap["startdate"]['question'] = gT("Date started");
            $fieldmap["startdate"]['group_name'] = "";
        }

        $fieldmap["datestamp"] = array("fieldname" => "datestamp",
        'type' => "datestamp",
        'sid' => $surveyid,
        "gid" => "",
        "qid" => "",
        "aid" => "");
        if ($style == "full") {
            $fieldmap["datestamp"]['title'] = "";
            $fieldmap["datestamp"]['question'] = gT("Date last action");
            $fieldmap["datestamp"]['group_name'] = "";
        }
    }
    if ($prow['ipaddr'] == "Y") {
        $fieldmap["ipaddr"] = array("fieldname" => "ipaddr",
        'type' => "ipaddress",
        'sid' => $surveyid,
        "gid" => "",
        "qid" => "",
        "aid" => "");
        if ($style == "full") {
            $fieldmap["ipaddr"]['title'] = "";
            $fieldmap["ipaddr"]['question'] = gT("IP address");
            $fieldmap["ipaddr"]['group_name'] = "";
        }
    }
    // Add 'refurl' to fieldmap.
    if ($prow['refurl'] == "Y") {
        $fieldmap["refurl"] = array("fieldname" => "refurl", 'type' => "url", 'sid' => $surveyid, "gid" => "", "qid" => "", "aid" => "");
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

    $defaultsQuery = "SELECT a.qid, a.sqid, a.scale_id, a.specialtype, al10.defaultvalue"
    . " FROM {{defaultvalues}} as a "
    . " JOIN {{defaultvalue_l10ns}} as al10 ON a.dvid = al10.dvid " // We NEED a default value set
    . " JOIN {{questions}} as b ON a.qid = b.qid " // We NEED only question in this survey
    . " AND al10.language = '{$sLanguage}'"
    . " AND b.same_default=0"
    . " AND b.sid = " . $surveyid;
    $defaultResults = Yii::app()->db->createCommand($defaultsQuery)->queryAll();
    $defaultValues = array(); // indexed by question then subquestion
    foreach ($defaultResults as $dv) {
        if ($dv['specialtype'] != '') {
            $sq = $dv['specialtype'];
        } else {
            $sq = $dv['sqid'];
        }
        $defaultValues[$dv['qid'] . '~' . $sq] = $dv['defaultvalue'];
    }

    // Now overwrite language-specific defaults (if any) base language values for each question that uses same_defaults=1
    $baseLanguage = $survey->language;
    $defaultsQuery = "SELECT a.qid, a.sqid, a.scale_id, a.specialtype, al10.defaultvalue"
    . " FROM {{defaultvalues}} as a "
    . " JOIN {{defaultvalue_l10ns}} as al10 ON a.dvid = al10.dvid " // We NEED a default value set
    . " JOIN {{questions}} as b ON a.qid = b.qid " // We NEED only question in this survey
    . " AND al10.language = '{$baseLanguage}'"
    . " AND b.same_default=1"
    . " AND b.sid = " . $surveyid;
    $defaultResults = Yii::app()->db->createCommand($defaultsQuery)->queryAll();

    foreach ($defaultResults as $dv) {
        if ($dv['specialtype'] != '') {
            $sq = $dv['specialtype'];
        } else {
            $sq = $dv['sqid'];
        }
        $defaultValues[$dv['qid'] . '~' . $sq] = $dv['defaultvalue'];
    }

    // Main query
    $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
    $aquery = "SELECT g.*, q.*, gls.*, qls.*"
    . " FROM $quotedGroups g"
    . ' JOIN {{questions}} q on q.gid=g.gid '
    . ' JOIN {{group_l10ns}} gls on gls.gid=g.gid '
    . ' JOIN {{question_l10ns}} qls on qls.qid=q.qid '
    . " WHERE qls.language='{$sLanguage}' and gls.language='{$sLanguage}' AND"
    . " g.sid={$surveyid} AND"
    . " q.parent_qid=0";
    if ($questionid !== false) {
        $aquery .= " and questions.qid={$questionid} ";
    }
    $aquery .= " ORDER BY group_order, question_order";
    /** @var Question[] $questions */
    $questions = Yii::app()->db->createCommand($aquery)->queryAll();
    $qids = [0];
    foreach ($questions as $q) {
        $qids[] = $q['qid'];
    }
    $rawQuestions = Question::model()->findAllByPk($qids);
    $qs = [];
    foreach ($rawQuestions as $rawQuestion) {
        $qs[$rawQuestion->qid] = $rawQuestion;
    }
    $questionSeq = -1; // this is incremental question sequence across all groups
    $groupSeq = -1;
    $_groupOrder = -1;

    $questionTypeMetaData = QuestionTheme::findQuestionMetaDataForAllTypes();
    foreach ($questions as $arow) {
        //For each question, create the appropriate field(s))

        ++$questionSeq;

        // fix fact that the group_order may have gaps
        if ($_groupOrder != $arow['group_order']) {
            $_groupOrder = $arow['group_order'];
            ++$groupSeq;
        }
        // Condition indicators are obsolete with EM.  However, they are so tightly coupled into LS code that easider to just set values to 'N' for now and refactor later.
        $conditions = 'N';
        $usedinconditions = 'N';

        // Check if answertable has custom setting for current question
        if (isset($arow['attribute']) && isset($arow['type']) && isset($arow['question_theme_name'])) {
            $answerColumnDefinition = QuestionTheme::getAnswerColumnDefinition($arow['question_theme_name'], $arow['type']);
        }

        // Field identifier
        // Q{qid}(_(S{qid}|Ccomment))?
        // G=Group  Q=Question S=Subquestion A=Answer Option
        // If S or A don't exist then set it to 0
        // Implicit (subqestion intermal to a question type ) or explicit qubquestions/answer count starts at 1

        // Types "L", "!", "O", "D", "G", "N", "X", "Y", "5", "S", "T", "U"
        $fieldname = "Q{$arow['qid']}";

        if ($questionTypeMetaData[$arow['type']]['settings']->subquestions == 0 && $arow['type'] != Question::QT_R_RANKING && $arow['type'] != Question::QT_VERTICAL_FILE_UPLOAD) {
            if (isset($fieldmap[$fieldname])) {
                $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
            }

            $fieldmap[$fieldname] = array("fieldname" => $fieldname, 'type' => "{$arow['type']}", 'sid' => $surveyid, "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => "");
            if (isset($answerColumnDefinition)) {
                $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
            }

            if ($style == "full") {
                $fieldmap[$fieldname]['title'] = $arow['title'];
                $fieldmap[$fieldname]['question'] = $arow['question'];
                $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                $fieldmap[$fieldname]['hasconditions'] = $conditions;
                $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                if (isset($defaultValues[$arow['qid'] . '~0'])) {
                    $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$arow['qid'] . '~0'];
                }
            }
            switch ($arow['type']) {
                case Question::QT_L_LIST:  //RADIO LIST
                case Question::QT_EXCLAMATION_LIST_DROPDOWN:  //DROPDOWN LIST
                    if ($arow['other'] == "Y") {
                        $fieldname = "Q{$arow['qid']}_Cother";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                        }

                        $fieldmap[$fieldname] = array("fieldname" => $fieldname,
                        'type' => $arow['type'],
                        'sid' => $surveyid,
                        "gid" => $arow['gid'],
                        "qid" => $arow['qid'],
                        "aid" => "_Cother");
                        if (isset($answerColumnDefinition)) {
                            $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                        }

                        // dgk bug fix line above. aid should be set to "other" for export to append to the field name in the header line.
                        if ($style == "full") {
                            $fieldmap[$fieldname]['title'] = $arow['title'];
                            $fieldmap[$fieldname]['question'] = $arow['question'];
                            $fieldmap[$fieldname]['subquestion'] = gT("Other");
                            $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                            $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                            $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                            $fieldmap[$fieldname]['hasconditions'] = $conditions;
                            $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                            $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                            $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                            if (isset($defaultValues[$arow['qid'] . '~other'])) {
                                $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$arow['qid'] . '~other'];
                            }
                        }
                    }
                    break;
                case Question::QT_O_LIST_WITH_COMMENT: //DROPDOWN LIST WITH COMMENT
                    $fieldname = "Q{$arow['qid']}_Ccomment";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                    }

                    $fieldmap[$fieldname] = array("fieldname" => $fieldname,
                    'type' => $arow['type'],
                    'sid' => $surveyid,
                    "gid" => $arow['gid'],
                    "qid" => $arow['qid'],
                    "aid" => "_Ccomment");
                    if (isset($answerColumnDefinition)) {
                        $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                    }

                    // dgk bug fix line below. aid should be set to "comment" for export to append to the field name in the header line. Also needed set the type element correctly.
                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion'] = gT("Comment");
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    }
                    break;
            }
        } elseif ($questionTypeMetaData[$arow['type']]['settings']->subquestions == 2 && $questionTypeMetaData[$arow['type']]['settings']->answerscales == 0) {
            // For Multi flexi question types
            $abrows = getSubQuestions($surveyid, $arow['qid'], $sLanguage);
            //Now first process scale=1
            $answerset = array();
            $answerList = array();
            foreach ($abrows as $key => $abrow) {
                if ($abrow['scale_id'] == 1) {
                    $answerset[] = $abrow;
                    $answerList[] = array(
                    'code' => $abrow['title'],
                    'answer' => $abrow['question'],
                    'qid' => $abrow['qid']
                    );
                    unset($abrows[$key]);
                }
            }
            reset($abrows);
            foreach ($abrows as $abrow) {
                foreach ($answerset as $answer) {
                    $fieldname = "Q{$arow['qid']}_S{$abrow['qid']}_S{$answer['qid']}";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                    }
                    $fieldmap[$fieldname] = array("fieldname" => $fieldname,
                    'type' => $arow['type'],
                    'sid' => $surveyid,
                    "gid" => $arow['gid'],
                    "qid" => $arow['qid'],
                    "aid" => $abrow['title'] . "_" . $answer['title'],
                    "suffix" => '_S' . ($aQIDReplacements[$abrow['qid']] ?? $abrow['qid']) . "_S" . $answer['qid'],
                    "sqid" => $abrow['qid']);
                    if (isset($answerColumnDefinition)) {
                        $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                    }

                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion1'] = $abrow['question'];
                        $fieldmap[$fieldname]['subquestion2'] = $answer['question'];
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
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
        } elseif ($arow['type'] == Question::QT_1_ARRAY_DUAL) {
            $abrows = getSubQuestions($surveyid, $arow['qid'], $sLanguage);
            foreach ($abrows as $abrow) {
                $fieldname = "Q{$arow['qid']}_S{$abrow['qid']}#0";
                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                }

                $fieldmap[$fieldname] = array(
                    "fieldname" => $fieldname,
                    'type' => $arow['type'],
                    'sid' => $surveyid,
                    "gid" => $arow['gid'],
                    "qid" => $arow['qid'],
                    "sqid" => $abrow['qid'],
                    "aid" => $abrow['title'],
                    "suffix" => '_S' . ($aQIDReplacements[$abrow['qid']] ?? $abrow['qid']),
                    "scale_id" => 0,
                );
                if (isset($answerColumnDefinition)) {
                    $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                }

                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['scale'] = gT('Scale 1');
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                    $fieldmap[$fieldname]['sqid'] = $abrow['qid'];
                }

                $fieldname = "Q{$arow['qid']}_S{$abrow['qid']}#1";
                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                }
                $fieldmap[$fieldname] = array(
                    "fieldname" => $fieldname,
                    'type' => $arow['type'],
                    'sid' => $surveyid,
                    "gid" => $arow['gid'],
                    "qid" => $arow['qid'],
                    "sqid" => $abrow['qid'],
                    "aid" => $abrow['title'],
                    "suffix" => '_S' . ($aQIDReplacements[$abrow['qid']] ?? $abrow['qid']),
                    "scale_id" => 1,
                );
                if (isset($answerColumnDefinition)) {
                    $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                }

                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['scale'] = gT('Scale 2');
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    $fieldmap[$fieldname]['sqid'] = $abrow['qid'];
                    // TODO SQrelevance for different scales? $fieldmap[$fieldname]['SQrelevance']=$abrow['relevance'];
                }
            }
        } elseif ($arow['type'] == Question::QT_R_RANKING) {
            // Sub question by answer number OR attribute
            $answers = Answer::model()->findAll('qid = :qid', [':qid' => $arow['qid']]);
            $answersCount = count($answers);
            $maxDbAnswer = QuestionAttribute::model()->find("qid = :qid AND attribute = 'max_subquestions'", array(':qid' => $arow['qid']));
            $columnsCount = (!$maxDbAnswer || intval($maxDbAnswer->value) < 1) ? $answersCount : intval($maxDbAnswer->value);
            $columnsCount = min($columnsCount, $answersCount); // Can not be upper than current answers #14899

            if($includeAllAnswerOptions)
                $columnsCount = $answersCount;

            for ($i = 1; $i <= $columnsCount; $i++) {
                $fieldname = "Q{$arow['qid']}_R" . $answers[$i - 1]->aid;
                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                }
                $fieldmap[$fieldname] = array("fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $surveyid, "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => $answers[$i - 1]->aid, "suffix" => "_R" . $answers[$i - 1]->aid, 'csuffix' => $i);
                if (isset($answerColumnDefinition)) {
                    $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                }

                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = sprintf(gT('Rank %s'), $i);
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                }
            }
        } elseif ($arow['type'] == Question::QT_VERTICAL_FILE_UPLOAD) {
            $qidattributes = QuestionAttribute::model()->getQuestionAttributes($qs[$arow['qid']] ?? $arow['qid']);
            $fieldname = "Q{$arow['qid']}";
            $fieldmap[$fieldname] = array(
                "fieldname" => $fieldname,
                'type' => $arow['type'],
                'sid' => $surveyid,
                "gid" => $arow['gid'],
                "qid" => $arow['qid'],
                "aid" => '',
                "suffix" => ''
            );
            if (isset($answerColumnDefinition)) {
                $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
            }

            if ($style == "full") {
                $fieldmap[$fieldname]['title'] = $arow['title'];
                $fieldmap[$fieldname]['question'] = $arow['question'];
                $fieldmap[$fieldname]['max_files'] = $qidattributes['max_num_of_files'];
                $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                $fieldmap[$fieldname]['hasconditions'] = $conditions;
                $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
            }
            $fieldname = "Q{$arow['qid']}" . "_Cfilecount";
            $fieldmap[$fieldname] = array(
                "fieldname" => $fieldname,
                'type' => $arow['type'],
                'sid' => $surveyid,
                "gid" => $arow['gid'],
                "qid" => $arow['qid'],
                "aid" => "filecount",
                "suffix" => "_Cfilecount"
            );
            if (isset($answerColumnDefinition)) {
                $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
            }

            if ($style == "full") {
                $fieldmap[$fieldname]['title'] = $arow['title'];
                $fieldmap[$fieldname]['question'] = "filecount - " . $arow['question'];
                $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
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
                $fieldname = "Q{$arow['qid']}_S{$abrow['qid']}";

                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                }
                $fieldmap[$fieldname] = array("fieldname" => $fieldname,
                'type' => $arow['type'],
                'sid' => $surveyid,
                'gid' => $arow['gid'],
                'qid' => $arow['qid'],
                'aid' => $abrow['title'],
                'suffix' => '_S' . ($aQIDReplacements[$abrow['qid']] ?? $abrow['qid']),
                'sqid' => $abrow['qid']);
                if (isset($answerColumnDefinition)) {
                    $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                }

                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    $fieldmap[$fieldname]['preg'] = $arow['preg'];
                    // get SQrelevance from DB
                    $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                    if (isset($defaultValues[$arow['qid'] . '~' . $abrow['qid']])) {
                        $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$arow['qid'] . '~' . $abrow['qid']];
                    }
                }
                if ($arow['type'] == Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS) {
                    $fieldname = "Q{$arow['qid']}_S{$abrow['qid']}_Ccomment";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                    }
                    $fieldmap[$fieldname] = array("fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $surveyid, "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => $abrow['title'] . "comment", "suffix" => '_S' . ($aQIDReplacements[$abrow['qid']] ?? $abrow['qid']) . "_Ccomment");
                    if (isset($answerColumnDefinition)) {
                        $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                    }
                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion1'] = gT('Comment');
                        $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    }
                }
            }
            if ($arow['other'] == "Y" && ($arow['type'] == Question::QT_M_MULTIPLE_CHOICE || $arow['type'] == Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS)) {
                $fieldname = "Q{$arow['qid']}_Cother";
                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                }
                $fieldmap[$fieldname] = array("fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $surveyid, "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => "other", "suffix" => "_Cother");
                if (isset($answerColumnDefinition)) {
                    $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                }

                if ($style == "full") {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = gT('Other');
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    $fieldmap[$fieldname]['other'] = $arow['other'];
                }
                if ($arow['type'] == Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS) {
                    $fieldname = "Q{$arow['qid']}_Cothercomment";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = array('fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']);
                    }
                    $fieldmap[$fieldname] = array("fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $surveyid, "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => "othercomment", "suffix" => "_Cothercomment");
                    if (isset($answerColumnDefinition)) {
                        $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                    }

                    if ($style == "full") {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion'] = gT('Other comment');
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
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
        if (isset(Yii::app()->session['responses_' . $surveyid]['fieldmap-' . $surveyid . '-randMaster'])) {
            $masterFieldmap = Yii::app()->session['responses_' . $surveyid]['fieldmap-' . $surveyid . '-randMaster'];
            $mfieldmap = Yii::app()->session['responses_' . $surveyid][$masterFieldmap];

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

        Yii::app()->session['fieldmap-' . $surveyid . $sLanguage] = $fieldmap;
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
    $iCount = Question::model()->count("sid=:surveyid AND parent_qid=0 AND type=:type", array(':surveyid' => $iSurveyID, ':type' => Question::QT_VERTICAL_FILE_UPLOAD));
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
    $fieldmap['interviewtime'] = array('fieldname' => 'interviewtime', 'type' => 'interview_time', 'sid' => $surveyid, 'gid' => '', 'qid' => '', 'aid' => '', 'suffix' => '', 'question' => gT('Total time'), 'title' => 'interviewtime');
    foreach ($fields as $field) {
        if (!empty($field['gid'])) {
            // field for time spent on page
            $fieldname = "G{$field['gid']}time";
            if (!isset($fieldmap[$fieldname])) {
                $fieldmap[$fieldname] = array("fieldname" => $fieldname, 'type' => "page_time", 'sid' => $surveyid, "gid" => $field['gid'], "group_name" => $field['group_name'], "qid" => '', 'aid' => '', 'suffix' => '', 'title' => 'groupTime' . $field['gid'], 'question' => gT('Group time') . ": " . $field['group_name']);
            }

            // field for time spent on answering a question
            $fieldname = "Q{$field['qid']}time";
            if (!isset($fieldmap[$fieldname])) {
                $fieldmap[$fieldname] = array("fieldname" => $fieldname, 'type' => "answer_time", 'sid' => $surveyid, "gid" => $field['gid'], "group_name" => $field['group_name'], "qid" => $field['qid'], 'aid' => '', 'suffix' => '', "title" => $field['title'] . 'Time', "question" => gT('Question time') . ": " . $field['title']);
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

function buildLabelSetCheckSumArray()
{
    // BUILD CHECKSUMS FOR ALL EXISTING LABEL SETS

    $result = LabelSet::model()->findAll();
    $csarray = array();
    foreach ($result as $row) {
        $thisset = "";
        $query2 = "SELECT code, title, sortorder, language, assessment_value
        FROM {{labels}} l
        join {{label_l10ns}} ls on label_id=l.id
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

function questionTitleSort($a, $b)
{
    $result = strnatcasecmp((string) $a['title'], (string) $b['title']);
    return $result;
}

/**
* make a string safe to include in an HTML 'value' attribute.
* @deprecated If you need this you are doing something wrong. Use CHTML functions instead.
*/
function HTMLEscape($str)
{
    // escape newline characters, too, in case we put a value from
    // a TEXTAREA  into an <input type="hidden"> value attribute.
    return str_replace(
        array("\x0A", "\x0D"),
        array("&#10;", "&#13;"),
        htmlspecialchars((string) $str, ENT_QUOTES)
    );
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
function javascriptEscape(string $str, $strip_tags = false, $htmldecode = false)
{
    if ($htmldecode == true) {
        $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
    }
    if ($strip_tags == true) {
        $str = strip_tags($str);
    }
    return str_replace(
        array('\'', '"', "\n", "\r"),
        array("\\'", '\u0022', "\\n", '\r'),
        $str
    );
}
// make a string safe to include in a json String parameter.
function jsonEscape(string $str, $strip_tags = false, $htmldecode = false)
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
* @deprecated : leave it in 4.0 for plugins ? Must remove in 5.0 at minima.
*
* @param string $body Body text of the email in plain text or HTML
* @param mixed $subject Email subject
* @param mixed $to Array with several email addresses or single string with one email address
* @param string $from
* @param mixed $sitename
* @param boolean $ishtml
* @param mixed $bouncemail
* @param mixed $attachments
* @return bool If successful returns true
*/
function SendEmailMessage($body, $subject, $to, string $from, $sitename, $ishtml = false, $bouncemail = null, $attachments = null, $customheaders = "")
{
    global $maildebug;

    if (!is_array($to)) {
        $to = array($to);
    }

    if (!is_array($customheaders) && $customheaders == '') {
        $customheaders = array();
    }

    $mail =  new LimeMailer();
    $mail->emailType = 'deprecated';

    $fromname = '';
    $fromemail = $from;
    if (strpos($from, '<')) {
        $fromemail = substr($from, strpos($from, '<') + 1, strpos($from, '>') - 1 - strpos($from, '<'));
        $fromname = trim(substr($from, 0, strpos($from, '<') - 1));
    }
    if (is_null($bouncemail)) {
        $senderemail = $fromemail;
    } else {
        $senderemail = $bouncemail;
    }

    $mail->SetFrom($fromemail, $fromname);
    $mail->Sender = $senderemail; // Sets Return-Path for error notifications
    foreach ($to as $singletoemail) {
        $mail->addAddress($singletoemail);
    }
    if (is_array($customheaders)) {
        foreach ($customheaders as $key => $val) {
            $mail->AddCustomHeader($val);
        }
    }
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->IsHTML($ishtml);
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
    return $mail->Send();
}


/**
*  This functions removes all HTML tags, Javascript, CRs, linefeeds and other strange chars from a given text
*
* @param string $sTextToFlatten  Text you want to clean
* @param boolean $bKeepSpan set to true for keep span, used for expression manager. Default: false
* @param boolean $bDecodeHTMLEntities If set to true then all HTML entities will be decoded to the specified charset. Default: false
* @param string $sCharset Charset to decode to if $decodeHTMLEntities is set to true. Default: UTF-8
* @param boolean $bStripNewLines strip new lines if true, if false replace all new line by \r\n. Default: true
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
    $qids = [0];
    // Store each result as an array with in the $grows array
    foreach ($qrows as $qrow) {
        if (isset($qrow['gid']) && !empty($qrow['gid'])) {
            $qids[] = $qrow['qid'];
            $grows[$qrow['qid']] = array('qid' => $qrow['qid'], 'type' => $qrow['type'], 'mandatory' => $qrow['mandatory'], 'title' => $qrow['title'], 'gid' => $qrow['gid']);
        }
    }
    $rawQuestions = Question::model()->findAllByPk($qids);
    $questions = [];
    foreach ($rawQuestions as $rawQuestion) {
        $questions[$rawQuestion->qid] = $rawQuestion;
    }
    foreach ($grows as $qrow) {
    // Cycle through questions to see if any have list_filter attributes
        $qidtotitle[$qrow['qid']] = $qrow['title'];
        $qresult = QuestionAttribute::model()->getQuestionAttributes($questions[$qrow['qid']] ?? $qrow['qid']);
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
                    if (isset($cascades)) {
                        unset($cascades);
                    }
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
        foreach ($cascaded as $key => $cascade) {
            foreach ($cascade as $item) {
                $cascade2[$key][] = $qidtotitle[$item];
            }
        }
        $cascaded = $cascade2;
    }
    return $cascaded;
}

function createPassword($iPasswordLength = 12)
{
    $aCharacters = "ABCDEGHJIKLMNOPQURSTUVWXYZabcdefhjmnpqrstuvwxyz23456789";
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
    $html = "<select class='listboxquestions form-select' name='langselect' onchange=\"window.open(this.options[this.selectedIndex].value, '_top')\">\n";
    foreach ($slangs as $lang) {
        $link = Yii::app()->createUrl("admin/dataentry/sa/view/surveyid/" . $surveyid . "/lang/" . $lang);
        if ($lang == $selected) {
            $html .= "\t<option value='{$link}' selected='selected'>" . getLanguageNameFromCode($lang, false) . "</option>\n";
        }
        if ($lang != $selected) {
            $html .= "\t<option value='{$link}'>" . getLanguageNameFromCode($lang, false) . "</option>\n";
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
    $html = "<select class='form-select listboxquestions' id='language' name='language'>\n";
    foreach ($slangs as $lang) {
        if ($lang == $selected) {
            $html .= "\t<option value='$lang' selected='selected'>" . getLanguageNameFromCode($lang, false) . "</option>\n";
        }
        if ($lang != $selected) {
            $html .= "\t<option value='$lang'>" . getLanguageNameFromCode($lang, false) . "</option>\n";
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
function CSVUnquote(string $field)
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
        return 'all';
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
            if (
                $captchamode == 'A' ||
                $captchamode == 'B' ||
                $captchamode == 'D' ||
                $captchamode == 'F' ||
                $captchamode == 'G' ||
                $captchamode == 'I' ||
                $captchamode == 'M' ||
                $captchamode == 'U' ||
                $captchamode == 'R'
            ) {
                return true;
            }
            return false;
        case 'surveyaccessscreen':
            if (
                $captchamode == 'A' ||
                $captchamode == 'B' ||
                $captchamode == 'C' ||
                $captchamode == 'F' ||
                $captchamode == 'H' ||
                $captchamode == 'K' ||
                $captchamode == 'O' ||
                $captchamode == 'T' ||
                $captchamode == 'X'
            ) {
                return true;
            }
            return false;
        case 'saveandloadscreen':
            if (
                $captchamode == 'A' ||
                $captchamode == 'C' ||
                $captchamode == 'D' ||
                $captchamode == 'G' ||
                $captchamode == 'H' ||
                $captchamode == 'J' ||
                $captchamode == 'L' ||
                $captchamode == 'P' ||
                $captchamode == 'S'
            ) {
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
    $sTableName = Yii::app()->db->tablePrefix . str_replace(array('{', '}'), array('', ''), $sTableName);
    return in_array($sTableName, Yii::app()->db->schema->getTableNames());
}

// Returns false if the survey is anonymous,
// and a survey participant list exists: in this case the completed field of a token
// will contain 'Y' instead of the submitted date to ensure privacy
// Returns true otherwise
function isTokenCompletedDatestamped($thesurvey)
{
    if ($thesurvey['anonymized'] == 'Y' && tableExists('tokens_' . $thesurvey['sid'])) {
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
* @param string $shift
* @return string
*/
function dateShift($date, $dformat, string $shift)
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
* @param mixed $iOldSurveyID Source SurveyId to be replaced
* @param mixed $iNewSurveyID New SurveyId to be used
* @param string $sString Link (url or local path) to be translated
* @param bool $isLocalPath Indicates if the link ($sString) is a local path or a url.
* @return string
*/
function translateLinks($sType, $iOldSurveyID, $iNewSurveyID, $sString, $isLocalPath = false)
{
    if ($sString == '') {
        return $sString;
    }
    $iOldSurveyID = (int) $iOldSurveyID;
    $iNewSurveyID = (int) $iNewSurveyID; // To avoid injection of a /e regex modifier without having to check all execution paths
    if ($sType == 'survey') {
        $sPattern = '(http(s)?:\/\/)?(([a-z0-9\/\.\-\_:])*(?=(\/upload))\/upload\/surveys\/' . $iOldSurveyID . '\/)';
        if ($isLocalPath) {
            $sReplace = rtrim(App()->getConfig("uploaddir"), "/") . "/surveys/{$iNewSurveyID}/";
            return preg_replace('/' . $sPattern . '/u', $sReplace, $sString);
        } else {
            // Make the replacement conditionaly.
            // If the URL is absolute, make sure we keep it absolute.
            // If it is relative, use the publicurl config (if the publicurl is absolute we assume it
            // makes sense to make the urls absolute)
            return preg_replace_callback('/' . $sPattern . '/u', function ($matches) use ($iNewSurveyID) {
                $url = $matches[0];
                $parsedUrl = parse_url($url);
                $replacementUrl = "/upload/surveys/{$iNewSurveyID}/";
                if (isset($parsedUrl['scheme']) && isset($parsedUrl['host'])) {
                    return rtrim(App()->getPublicBaseUrl(true), "/") . $replacementUrl;
                } else {
                    return rtrim(App()->getConfig("publicurl"), '/') . $replacementUrl;
                }
            }, $sString);
        }
    } elseif ($sType == 'label') {
        $sPattern = '(http(s)?:\/\/)?(([a-z0-9\/\.\-\_])*(?=(\/upload))\/upload\/labels\/' . $iOldSurveyID . '\/)';
        return preg_replace_callback('/' . $sPattern . '/u', function ($matches) use ($iNewSurveyID) {
            $url = $matches[0];
            $parsedUrl = parse_url($url);
            $replacementUrl = "/upload/labels/{$iNewSurveyID}/";
            if (isset($parsedUrl['scheme']) && isset($parsedUrl['host'])) {
                return rtrim(App()->getPublicBaseUrl(true), "/") . $replacementUrl;
            } else {
                return rtrim(App()->getConfig("publicurl"), '/') . $replacementUrl;
            }
        }, $sString);
    } else // unknown type
    {
        return $sString;
    }
}

/**
 * Returns true if there are old links in answer/question/survey/email template/label set texts.
 *
 * @param string $type 'survey' or 'label'
 * @param mixed $oldSurveyId
 * @param mixed $string
 * @return boolean True if the provided string includes links to the old survey. If the type is not 'survey' or 'label', it returns false.
 */
function checkOldLinks($type, $oldSurveyId, $string)
{
    if (empty($string)) {
        return false;
    }
    $oldSurveyId = (int) $oldSurveyId;
    if ($type == 'survey') {
        $pattern = '(http(s)?:\/\/)?(([a-z0-9\/\.])*(?=(\/upload))\/upload\/surveys\/' . $oldSurveyId . '\/)';
        return preg_match('/' . $pattern . '/u', $string, $m);
    } elseif ($type == 'label') {
        $pattern = '(http(s)?:\/\/)?(([a-z0-9\/\.])*(?=(\/upload))\/upload\/labels\/' . $oldSurveyId . '\/)';
        return preg_match('/' . $pattern . '/u', $string, $m);
    } else // unknown type
    {
        return false;
    }
}

/**
 * This function creates the old fieldnames for survey import
 *
 * @param mixed $iOldSID The old survey ID
 * @param integer $iNewSID The new survey ID
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
    $dupes = [];
    $aFieldMap = createFieldMap($oNewSurvey, 'short', $forceRefresh, false, $oNewSurvey->language, $dupes ,$aQIDReplacements);

    $aFieldMappings = array();
    foreach ($aFieldMap as $sFieldname => $aFieldinfo) {
        if ($aFieldinfo['qid'] != null) {
            $aFieldMappings[$sFieldname] = 'Q' . $aQIDReplacements[$aFieldinfo['qid']] . ($aFieldinfo['suffix'] ?? '');
            if ($aFieldinfo['type'] == '1') {
                $aFieldMappings[$sFieldname] = $aFieldMappings[$sFieldname] . '#' . $aFieldinfo['scale_id'];
            }
            // now also add a shortened field mapping which is needed for certain kind of condition mappings
            $aFieldMappings['Q' . $aFieldinfo['qid']] = 'Q' . $aQIDReplacements[$aFieldinfo['qid']];
            // Shortened field mapping for timings table
            $aFieldMappings['G' . $aFieldinfo['gid']] = 'G' . $aGIDReplacements[$aFieldinfo['gid']];
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
        $key .= $pattern[mt_rand(0, $patternlength)];
    }
    return $key;
}

/**
* used to translate simple text to html (replacing \n with <br />
*
* @param string $mytext
* @param mixed $ishtml
* @return mixed
*/
function conditionalNewlineToBreak(string $mytext, $ishtml, $encoded = '')
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

function breakToNewline(string $data)
{
    return preg_replace('!<br.*>!iU', "\n", $data);
}

/**
* Provides a safe way to end the application
*
* @param string $sText
* @return void
* @todo This should probably never be used, since it returns 0 from CLI and makes PHPUnit think all is fine :(
*/
function safeDie(string $sText)
{
    //Only allowed tag: <br />
    $textarray = explode('<br />', $sText);
    $textarray = array_map('htmlspecialchars', $textarray);
    die(implode('<br />', $textarray));
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
 * @param string $fieldname
 * @return bool
 */
function filterForAttributes(string $fieldname)
{
    if (strpos($fieldname, 'attribute_') === false) {
        return false;
    } else {
        return true;
    }
}

/**
* Retrieves the attribute field names from the related survey participant list
*
* @param mixed $iSurveyID  The survey ID
* @return array The fieldnames
*/
function getAttributeFieldNames($iSurveyID)
{
    $survey = Survey::model()->findByPk($iSurveyID);
    if (!$survey->hasTokensTable || !$table = Yii::app()->db->schema->getTable($survey->tokensTableName)) {
            return array();
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
            return array();
    }
    return getTokenFieldsAndNames($iSurveyID, true);
}





/**
* Retrieves the attribute names from the related survey participant list
*
* @param mixed $surveyid  The survey ID
* @param boolean $bOnlyAttributes Set this to true if you only want the fieldnames of the additional attribue fields - defaults to false
* @return array The fieldnames as key and names as value in an Array
*/
function getTokenFieldsAndNames($surveyid, $bOnlyAttributes = false)
{
    $aBasicTokenFields = array(
        'firstname' => array(
            'description' => gT('First name'),
            'mandatory' => 'N',
            'showregister' => 'Y'
        ),
        'lastname' => array(
            'description' => gT('Last name'),
            'mandatory' => 'N',
            'showregister' => 'Y'
        ),
        'email' => array(
            'description' => gT('Email address'),
            'mandatory' => 'N',
            'showregister' => 'Y'
        ),
        'emailstatus' => array(
            'description' => gT("Email status"),
            'mandatory' => 'N',
            'showregister' => 'N'
        ),
        'token' => array(
            'description' => gT('Access code'),
            'mandatory' => 'N',
            'showregister' => 'N'
        ),
        'language' => array(
            'description' => gT('Language code'),
            'mandatory' => 'N',
            'showregister' => 'N'
        ),
        'sent' => array(
            'description' => gT('Invitation sent date'),
            'mandatory' => 'N',
            'showregister' => 'N'
        ),
        'remindersent' => array(
            'description' => gT('Last reminder sent date'),
            'mandatory' => 'N',
            'showregister' => 'N'
        ),
        'remindercount' => array(
            'description' => gT('Total numbers of sent reminders'),
            'mandatory' => 'N',
            'showregister' => 'N'
        ),
        'usesleft' => array(
            'description' => gT('Uses left'),
            'mandatory' => 'N',
            'showregister' => 'N'
        ),
        'completed' => array(
            'description' => gT('Completed'),
            'mandatory' => 'N',
            'showregister' => 'N'
        ),
    );

    $aExtraTokenFields = getAttributeFieldNames($surveyid);
    $aSavedExtraTokenFields = Survey::model()->findByPk($surveyid)->tokenAttributes ?? [];

    // Drop all fields that are in the saved field description but not in the table definition
    $aSavedExtraTokenFields = array_intersect_key($aSavedExtraTokenFields, array_flip($aExtraTokenFields));

    // Now add all fields that are in the table but not in the field description
    foreach ($aExtraTokenFields as $sField) {
        if (!isset($aSavedExtraTokenFields[$sField])) {
            $aSavedExtraTokenFields[$sField] = array(
            'description' => $sField,
            'mandatory' => 'N',
            'showregister' => 'N',
            'cpdbmap' => ''
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
* @param ?string $sContent String to clean
* @return string  Cleaned string
*/
function stripJavaScript($sContent)
{
    $text = preg_replace('@<script[^>]*?>.*?</script>@si', '', (string) $sContent);
    // TODO : Adding the onload/onhover etc ... or remove this false security function
    return (string) $text;
}

/**
* This function converts emebedded Javascript to Text
*
* @param string $sContent String to clean
* @return string  Cleaned string
*/
function showJavaScript($sContent)
{
    $text = preg_replace_callback(
        '@<script[^>]*?>.*?</script>@si',
        function ($matches) {
            return htmlspecialchars($matches[0]);
        },
        $sContent
    );
    return $text;
}

/**
 * Only clean temp directory if modification date of any non-symlinked directory found is older then 25 hours
 * Even if the setting is activated to only symlink assets, there are still some asset dirs that are not symlinked.
 * @return void
 */
function cleanCacheTempDirectoryDaily()
{
    $assetsPath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
    $selectedDirectory = null;

    foreach (glob($assetsPath . '/*') as $dir) {
        if (is_dir($dir) && !is_link($dir) && (filemtime($dir) < (strtotime('-24 hours')))) {
            cleanCacheTempDirectory();
            break;
        }
    }
}

/**
 * Cleans the temporary directory by removing files older than 1 day.
 * It also cleans the 'upload' subdirectory within the temporary directory.
 * Additionally, it calls the 'cleanAssetCacheDirectory' function to clean the asset cache directory.
 *
 * @return void
 */
function cleanCacheTempDirectory()
{
    $dir = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR;
    $dp = opendir($dir) or safeDie('Could not open temporary directory');

    while ($file = readdir($dp)) {
        if (is_file($dir . $file) && (filemtime($dir . $file)) < (strtotime('-1 days')) && $file != 'index.html' && $file != '.gitignore' && $file != 'readme.txt') {
            /** @scrutinizer ignore-unhandled */ @unlink($dir . $file);
        }
    }

    $dir = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR;
    $dp = opendir($dir) or safeDie('Could not open temporary upload directory');

    while ($file = readdir($dp)) {
        if (is_file($dir . $file) && (filemtime($dir . $file)) < (strtotime('-1 days')) && $file != 'index.html' && $file != '.gitignore' && $file != 'readme.txt') {
            /** @scrutinizer ignore-unhandled */ @unlink($dir . $file);
        }
    }

    closedir($dp);
    cleanAssetCacheDirectory(60);
}
/**
 * This function cleans the asset directory by removing directories that are older than a certain threshold.
 *
 * @return void
 */
function cleanAssetCacheDirectory($minutes = 1)
{
    // Define the path to the assets directory
    $assetsPath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;

    // Define the threshold for removing directories (in this case, 60 seconds ago)
    $threshold = time() - (60 * $minutes);

    // Loop through all directories in the assets directory
    foreach (glob($assetsPath . '*') as $path) {
        // check if the directory is older than the threshold and the path is a symlink then delete it
        if (is_link($path)
            && filemtime($path) < $threshold) {
            unlink($path);
            continue;
        }
        // check if the directory is older than the threshold and the path is a directory then remove it
        if (is_dir($path)
            && filemtime($path) < $threshold) {
            // Remove the directory and all its contents recursively
            CFileHelper::removeDirectory($path);
        }
    }
}

/**
 * This function removes the Twig cache directory by looping through all directories
 * within the Twig cache directory and removing each directory.
 *
 * @return void
 */
function cleanTwigCacheDirectory()
{
    $runtimePath = rtrim(Yii::app()->getRuntimePath(), DIRECTORY_SEPARATOR);
    $twigDir = $runtimePath . DIRECTORY_SEPARATOR . 'twig_cache';

    if (!is_dir($twigDir) || !is_writable($runtimePath)) {
        return;
    }

    // read and store the permissions of the Twig cache directory to apply it later
    $oldPermissions = fileperms($twigDir);
    try {
        CFileHelper::removeDirectory($twigDir);
    } catch (Exception $e) {
        Yii::log("Failed to remove Twig cache directory '{$twigDir}': " . $e->getMessage(), \CLogger::LEVEL_WARNING, 'application.cleanup');
        return;
    }

    // Recreate directory to avoid downstream failures expecting it present
    if (!@mkdir($twigDir, $oldPermissions, true) && !is_dir($twigDir)) {
        Yii::log("Failed to recreate Twig cache directory '{$twigDir}' after cleanup.", \CLogger::LEVEL_WARNING, 'application.cleanup');
    }
}

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
    $usedDatetime = ($withTime === true ? $sDateformatdata['phpdate'] . " H:i" : $sDateformatdata['phpdate']); //return also hours and minutes if asked for
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
        $query = "SELECT sq.*, ls.question, q.other FROM {{questions}} as sq
        JOIN {{questions}} as q on sq.parent_qid=q.qid
        JOIN {{question_l10ns}} as ls on ls.qid=sq.qid"
        . " WHERE sq.parent_qid=q.qid AND ls.language='{$sLanguage}' AND q.sid=" . $sid
        . " ORDER BY sq.parent_qid, q.question_order,sq.scale_id, sq.question_order";

        $query = Yii::app()->db->createCommand($query)->query();

        $resultset = array();
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
    $url = 'http' . $enforceSSLMode . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    if (!headers_sent()) {
// If headers not sent yet... then do php redirect
        //ob_clean();
        header('Location: ' . $url);
        //ob_flush();
        Yii::app()->end();
    };
}

/**
* enforceSSLMode() $force_ssl is on or off, it checks if the current
* request is to HTTPS (or not). If $force_ssl is on, and the
* request is not to HTTPS, it redirects the request to the HTTPS
* version of the URL, if the request is to HTTPS, it rewrites all
* the URL variables so they also point to HTTPS.
*/
function enforceSSLMode()
{
    $bForceSSL = ''; // off
    $bSSLActive = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") ||
    (isset($_SERVER['HTTP_FORWARDED_PROTO']) && $_SERVER['HTTP_FORWARDED_PROTO'] == "https") ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == "https"));
    if (Yii::app()->getConfig('ssl_emergency_override') !== true) {
        $bForceSSL = strtolower((string) getGlobalSetting('force_ssl'));
    }
    if ($bForceSSL == 'on' && !$bSSLActive) {
        SSLRedirect('s');
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
 * @return array
 */
function getFullResponseTable($iSurveyID, $iResponseID, $sLanguageCode, $bHonorConditions = true)
{
    $survey = Survey::model()->findByPk($iSurveyID);
    $aFieldMap = createFieldMap($survey, 'full', false, false, $sLanguageCode);

    // Get response data
    $idrow = SurveyDynamic::model($iSurveyID)->findByAttributes(array('id' => $iResponseID));
    // If response data not found, throw an exception
    if (!$idrow) {
        throw new CHttpException(401, gT("Response data not found."));
    }
    $idrow->decryptBeforeOutput();

    // Create array of non-null values - those are the relevant ones
    $aRelevantFields = array();

    foreach ($aFieldMap as $sKey => $fname) {
        if (LimeExpressionManager::QuestionIsRelevant($fname['qid']) || $bHonorConditions === false) {
            $aRelevantFields[$sKey] = $fname;
        }
    }

    $aResultTable = array();
    $oldgid = 0;
    $oldqid = 0;
    $qids = [0];
    foreach ($aRelevantFields as $sKey => $fname) {
        $qids[] = $fname['qid'];
    }
    $rawQuestions = Question::model()->findAllByPk($qids);
    $questions = [];
    foreach ($rawQuestions as $rawQuestion) {
        $questions[$rawQuestion->qid] = $rawQuestion;
    }
    foreach ($aRelevantFields as $sKey => $fname) {
        if (!empty($fname['qid'])) {
            $attributes = QuestionAttribute::model()->getQuestionAttributes($questions[$fname['qid']] ?? $fname['qid']);
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
                    $aResultTable['gid_' . $fname['gid']] = array($fname['group_name'], QuestionGroup::model()->getGroupDescription($fname['gid'], $sLanguageCode));
                }
            }
        }
        if (!empty($fname['qid'])) {
            if ($oldqid !== $fname['qid']) {
                $oldqid = $fname['qid'];
                if (isset($fname['subquestion']) || isset($fname['subquestion1']) || isset($fname['subquestion2'])) {
                    $aResultTable['qid_' . 'Q' . $fname['qid']] = array($fname['question'], '', '');
                } else {
                    $answer = getExtendedAnswer($iSurveyID, $fname['fieldname'], $idrow[$fname['fieldname']], $sLanguageCode, $questions[$fname['qid']] ?? null);
                    $aResultTable[$fname['fieldname']] = array($question, '', $answer);
                    continue;
                }
            }
        } else {
            $answer = getExtendedAnswer($iSurveyID, $fname['fieldname'], $idrow[$fname['fieldname']], $sLanguageCode, $questions[$fname['qid']] ?? null);
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

        $answer = getExtendedAnswer($iSurveyID, $fname['fieldname'], $idrow[$fname['fieldname']], $sLanguageCode, $questions[$fname['qid']] ?? null);
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
                if (strlen((string) $sResult) > Yii::app()->getConfig('maxstringlengthshortimplode') - strlen($sDelimeter) - 3) {
                    return $sResult . $sDelimeter . '...';
                } else {
                    $sResult = $sResult . $sDelimeter . $aArray[$iIndexA];
                }
            }
            $iIndexB = $iIndexA + 1;
            if ($iIndexB < sizeof($aArray)) {
                while ($iIndexB < sizeof($aArray) && $aArray[$iIndexB] - 1 == $aArray[$iIndexB - 1]) {
                    $iIndexB++;
                }
                if ($iIndexA < $iIndexB - 1) {
                    $sResult = $sResult . $sHyphen . $aArray[$iIndexB - 1];
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
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('vendor') . 'jquery-keypad/jquery.plugin.min.js');
    App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('vendor') . 'jquery-keypad/jquery.keypad.min.js');
    $localefile = Yii::app()->getConfig('rootdir') . '/vendor/jquery-keypad/jquery.keypad-' . App()->language . '.js';
    if (App()->language != 'en' && file_exists($localefile)) {
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->getConfig('vendor') . 'jquery-keypad/jquery.keypad-' . App()->language . '.js');
    }
    Yii::app()->getClientScript()->registerCssFile(Yii::app()->getConfig('vendor') . "jquery-keypad/jquery.keypad.alt.css");
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
    uksort($fieldnames, function ($a, $b) {
        return strlen($b) - strlen($a);
    });

    Yii::app()->loadHelper('database');
    $newsid = (int) $newsid;
    $oldsid = (int) $oldsid;

    # translate 'surveyls_urldescription' and 'surveyls_url' INSERTANS tags in surveyls
    $result = SurveyLanguageSetting::model()->findAll("surveyls_survey_id=" . $newsid . " AND (surveyls_urldescription LIKE '%Q%' OR surveyls_url LIKE '%Q%')");
    foreach ($result as $qentry) {
        $urldescription = $qentry['surveyls_urldescription'];
        $endurl = $qentry['surveyls_url'];
        $language = $qentry['surveyls_language'];

        foreach ($fieldnames as $sOldFieldname => $sNewFieldname) {
            $pattern = $sOldFieldname;
            $replacement = $sNewFieldname;
            $urldescription = preg_replace('/' . $pattern . '/', (string) $replacement, (string) $urldescription);
            $endurl = preg_replace('/' . $pattern . '/', (string) $replacement, (string) $endurl);
        }

        if (
            strcmp((string) $urldescription, (string) $qentry['surveyls_urldescription']) != 0 ||
            (strcmp((string) $endurl, (string) $qentry['surveyls_url']) != 0)
        ) {
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
    $result = QuotaLanguageSetting::model()->with('quota', array('condition' => 'sid=' . $newsid))->together()->findAll("(quotals_urldescrip LIKE '%Q%' OR quotals_url LIKE '%Q%')");
    foreach ($result as $qentry) {
        $urldescription = $qentry['quotals_urldescrip'];
        $endurl = $qentry['quotals_url'];

        foreach ($fieldnames as $sOldFieldname => $sNewFieldname) {
            $pattern = $sOldFieldname;
            $replacement = $sNewFieldname;
            $urldescription = preg_replace('/' . $pattern . '/', (string) $replacement, (string) $urldescription);
            $endurl = preg_replace('/' . $pattern . '/', (string) $replacement, (string) $endurl);
        }

        if (strcmp((string) $urldescription, (string) $qentry['quotals_urldescrip']) != 0 || (strcmp((string) $endurl, (string) $qentry['quotals_url']) != 0)) {
            // Update Field
            $qentry->quotals_urldescrip = $urldescription;
            $qentry->quotals_url = $endurl;
            $qentry->save();
        } // Enf if modified
    } // end while qentry

    # translate 'description' INSERTANS tags in groups
    $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
    $sql = "SELECT g.gid, language, group_name, description from $quotedGroups g
    join {{group_l10ns}} l on g.gid=l.gid
    WHERE sid=" . $newsid . " AND description REGEXP 'Q[0-9]+' OR group_name REGEXP 'Q[0-9]+'";
    $res = Yii::app()->db->createCommand($sql)->query();

    //while ($qentry = $res->FetchRow())
    foreach ($res->readAll() as $qentry) {
        $gpname = $qentry['group_name'];
        $description = $qentry['description'];
        $gid = $qentry['gid'];
        $language = $qentry['language'];

        foreach ($fieldnames as $sOldFieldname => $sNewFieldname) {
            $pattern = $sOldFieldname;
            $replacement = $sNewFieldname;
            $gpname = preg_replace('/' . $pattern . '/', (string) $replacement, (string) $gpname);
            $description = preg_replace('/' . $pattern . '/', (string) $replacement, (string) $description);
        }

        if (strcmp((string) $description, (string) $qentry['description']) != 0 || strcmp((string) $gpname, (string) $qentry['group_name']) != 0) {
            // Update Fields
            $where = array(
            'gid' => $gid,
            'language' => $language
            );
            $oGroup = QuestionGroupL10n::model()->findByAttributes($where);
            $oGroup->description = $description;
            $oGroup->group_name = $gpname;
            $oGroup->save();
        } // Enf if modified
    } // end while qentry

    # translate 'question' and 'help' INSERTANS tags in questions
    $sql = "SELECT l.id, question, help from {{questions}} q
    join {{question_l10ns}} l on q.qid=l.qid
    WHERE sid=" . $newsid . " AND (question REGEXP 'Q[0-9]+' OR help REGEXP 'Q[0-9]+')";
    $result = Yii::app()->db->createCommand($sql)->query();
    $aResultData = $result->readAll();
    foreach ($aResultData as $qentry) {
        $question = $qentry['question'];
        $help = $qentry['help'];

        foreach ($fieldnames as $sOldFieldname => $sNewFieldname) {
            $pattern = $sOldFieldname;
            $replacement = $sNewFieldname;
            $question = preg_replace('/' . $pattern . '/', (string) $replacement, (string) $question);
            $help = preg_replace('/' . $pattern . '/', (string) $replacement, (string) $help);
        }

        if (
            strcmp((string) $question, (string) $qentry['question']) != 0 ||
            strcmp((string) $help, (string) $qentry['help']) != 0
        ) {
            // Update Field

            $data = array(
            'question' => $question,
            'help' => $help
            );

            QuestionL10n::model()->updateByPk($qentry['id'], $data);
        } // Enf if modified
    } // end while qentry

    # translate 'answer' INSERTANS tags in answers
    $result = Answer::model()->oldNewInsertansTags($newsid, $oldsid);

    //while ($qentry = $res->FetchRow())
    foreach ($result as $qentry) {
        $translatedAnswers = $qentry->answerl10ns;
        foreach ($translatedAnswers as $translatedAnswer) {
            $answer = $translatedAnswer->answer;
            foreach ($fieldnames as $pattern => $replacement) {
                $translatedAnswer->answer = preg_replace('/' . $pattern . '/', (string) $replacement, (string) $translatedAnswer->answer);
            }
            if ($answer !== $translatedAnswer->answer) {
                $translatedAnswer->save();
            }
        }
    }
}

/**
* Replaces EM variable codes in a current survey with a new one
*
* @param integer $iSurveyID The survey ID
* @param mixed $aCodeMap The codemap array (old_code=>new_code)
*/
function replaceExpressionCodes($iSurveyID, $aCodeMap)
{
    $arQuestions = Question::model()->findAll("sid=:sid", array(':sid' => $iSurveyID));
    foreach ($arQuestions as $arQuestion) {
        $bModified = false;
        foreach ($aCodeMap as $sOldCode => $sNewCode) {
            // Don't search/replace old codes that are too short or were numeric (because they would not have been usable in EM expressions anyway)
            if (strlen((string) $sOldCode) > 1 && !is_numeric($sOldCode)) {
                $sOldCode = preg_quote((string) $sOldCode, '~');
                $arQuestion->relevance = preg_replace("~\b{$sOldCode}~", (string) $sNewCode, (string) $arQuestion->relevance, -1, $iCount);
                $bModified = $bModified || $iCount;
            }
        }
        if ($bModified) {
            $arQuestion->save();
        }
        foreach ($arQuestion->questionl10ns as $arQuestionLS) {
            $bModified = false;
            foreach ($aCodeMap as $sOldCode => $sNewCode) {
                // Don't search/replace old codes that are too short or were numeric (because they would not have been usable in EM expressions anyway)
                if (strlen((string) $sOldCode) > 1 && !is_numeric($sOldCode[0])) {
                    $sOldCode = preg_quote((string) $sOldCode, '~');
                    // The following regex only matches the last occurrence of the old code within each pair of brackets, so we apply the replace recursively
                    // to catch all occurrences.
                    $arQuestionLS->question = recursive_preg_replace("~{[^}]*\K{$sOldCode}(?=[^}]*?})~", $sNewCode, $arQuestionLS->question, -1, $iCount);
                    $bModified = $bModified || $iCount;
                    // Apply the replacement on question help text
                    $arQuestionLS->help = recursive_preg_replace("~{[^}]*\K{$sOldCode}(?=[^}]*?})~", $sNewCode, $arQuestionLS->help, -1, $iCount);
                    $bModified = $bModified || $iCount;
                }
            }
            if ($bModified) {
                $arQuestionLS->save();
            }
        }
        // Also apply on question's default values
        $defaultValues = DefaultValue::model()->with('defaultvaluel10ns')->findAllByAttributes(['qid' => $arQuestion->qid]);
        foreach ($defaultValues as $defaultValue) {
            if (empty($defaultValue->defaultvaluel10ns)) {
                continue;
            }
            foreach ($defaultValue->defaultvaluel10ns as $defaultValueL10n) {
                $bModified = false;
                foreach ($aCodeMap as $sOldCode => $sNewCode) {
                    if (strlen((string) $sOldCode) <= 1 || is_numeric($sOldCode)) {
                        continue;
                    }
                    $sOldCode = preg_quote((string) $sOldCode, '~');
                    $defaultValueL10n->defaultvalue = recursive_preg_replace("~{[^}]*\K{$sOldCode}(?=[^}]*?})~", $sNewCode, $defaultValueL10n->defaultvalue, -1, $iCount);
                    $bModified = $bModified || $iCount;
                }
                if ($bModified > 0) {
                    $defaultValueL10n->save();
                }
            }
        }
    }
    $arGroups = QuestionGroup::model()->findAll("sid=:sid", array(':sid' => $iSurveyID));
    foreach ($arGroups as $arGroup) {
        $bModified = false;
        foreach ($aCodeMap as $sOldCode => $sNewCode) {
            $sOldCode = preg_quote((string) $sOldCode, '~');
            $arGroup->grelevance = preg_replace("~\b{$sOldCode}~", (string) $sNewCode, (string) $arGroup->grelevance, -1, $iCount);
            $bModified = $bModified || $iCount;
        }
        if ($bModified) {
            $arGroup->save();
        }
        foreach ($arGroup->questiongroupl10ns as $arQuestionGroupLS) {
            foreach ($aCodeMap as $sOldCode => $sNewCode) {
                $sOldCode = preg_quote((string) $sOldCode, '~');
                $arQuestionGroupLS->description = recursive_preg_replace("~{[^}]*\K{$sOldCode}(?=[^}]*?})~", $sNewCode, $arQuestionGroupLS->description, -1, $iCount);
                $bModified = $bModified || $iCount;
            }
            if ($bModified) {
                $arQuestionGroupLS->save();
            }
        }
    }
    // Apply the replacement on survey's end message
    $surveyLanguageSettings = SurveyLanguageSetting::model()->findAllByAttributes(array('surveyls_survey_id' => $iSurveyID));
    foreach ($surveyLanguageSettings as $surveyLanguageSetting) {
        $bModified = false;
        foreach ($aCodeMap as $sOldCode => $sNewCode) {
            if (strlen((string) $sOldCode) <= 1 || is_numeric($sOldCode)) {
                continue;
            }
            $sOldCode = preg_quote((string) $sOldCode, '~');
            $surveyLanguageSetting->surveyls_endtext = recursive_preg_replace("~{[^}]*\K{$sOldCode}(?=[^}]*?})~", $sNewCode, $surveyLanguageSetting->surveyls_endtext, -1, $iCount);
            $bModified = $bModified || $iCount;
        }
        if ($bModified) {
            $surveyLanguageSetting->save();
        }
    }
}


/**
* cleanLanguagesFromSurvey() removes any languages from survey tables that are not in the passed list
* @param string $sid - the currently selected survey
* @param string $availlangs - space separated list of additional languages in survey
* @param string|null $baselang - the base language to be used
* @return void
*/
function cleanLanguagesFromSurvey($iSurveyID, $availlangs, $baselang = '')
{
    Yii::app()->loadHelper('database');
    $iSurveyID = (int) $iSurveyID;
    $baselang = sanitize_languagecode($baselang);
    if (empty($baselang)) {
        $baselang = Survey::model()->findByPk($iSurveyID)->language;
    }
    $aLanguages = [];
    if (!empty($availlangs) && $availlangs != " ") {
        $availlangs = sanitize_languagecodeS($availlangs);
        $aLanguages = explode(" ", (string) $availlangs);
        if ($aLanguages[count($aLanguages) - 1] == "") {
            array_pop($aLanguages);
        }
    }

    $sqllang = "language <> '" . $baselang . "' ";

    if (!empty($availlangs) && $availlangs != " ") {
        foreach ($aLanguages as $lang) {
            $sqllang .= "AND language <> '" . $lang . "' ";
        }
    }

    // Remove From Answer Table
    $sQuery = "SELECT ls.id from {{answer_l10ns}} ls
            JOIN {{answers}} a on ls.aid=a.aid
            JOIN {{questions}} q on a.qid=q.qid
            WHERE sid={$iSurveyID} AND {$sqllang}";
    $result = Yii::app()->db->createCommand($sQuery)->queryAll();
    foreach ($result as $row) {
        Yii::app()->db->createCommand('delete from {{answer_l10ns}} where id =' . $row['id'])->execute();
    }
    // Remove From Questions Table
    $sQuery = "SELECT ls.id from {{question_l10ns}} ls
            JOIN {{questions}} q on ls.qid=q.qid
            WHERE sid={$iSurveyID} AND {$sqllang}";
    $result = Yii::app()->db->createCommand($sQuery)->queryAll();
    foreach ($result as $row) {
        Yii::app()->db->createCommand('delete from {{question_l10ns}} where id =' . $row['id'])->execute();
    }

    // Remove From Questions Table
    $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
    $sQuery = "SELECT ls.id from {{group_l10ns}} ls
            JOIN $quotedGroups g on ls.gid=g.gid
            WHERE sid={$iSurveyID} AND {$sqllang}";
    $result = Yii::app()->db->createCommand($sQuery)->queryAll();
    foreach ($result as $row) {
        Yii::app()->db->createCommand('delete from {{group_l10ns}} where id =' . $row['id'])->execute();
    }
}

/**
* fixLanguageConsistency() fixes missing groups, questions, answers, quotas & assessments for languages on a survey
* @param int $sid - the currently selected survey
* @param string $availlangs - space separated list of additional languages in survey - if empty all additional languages of a survey are checked against the base language
* @param string $baselang - language to use as base (useful when changing the base language) - if empty, it will be picked from the survey
* @return bool - always returns true
*/
function fixLanguageConsistency($sid, $availlangs = '', $baselang = '')
{
    $sid = (int) $sid;
    $baselang = sanitize_languagecode($baselang);
    if (empty($baselang)) {
        $baselang = Survey::model()->findByPk($sid)->language;
    }
    if (trim($availlangs) != '') {
        $availlangs = sanitize_languagecodeS($availlangs);
        $languagesToCheck = explode(" ", (string) $availlangs);
        if ($languagesToCheck[count($languagesToCheck) - 1] == "") {
            array_pop($languagesToCheck);
        }
        // If base language is in the list, remove it
        if (($key = array_search($baselang, $languagesToCheck)) !== false) {
            unset($languagesToCheck[$key]);
        }
    } else {
        $languagesToCheck = Survey::model()->findByPk($sid)->additionalLanguages;
    }
    if (count($languagesToCheck) == 0) {
        return true; // Survey only has one language
    }
    $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
    $query = "SELECT g.gid, ls.group_name, ls.description FROM $quotedGroups g JOIN {{group_l10ns}} ls ON ls.gid=g.gid WHERE sid={$sid} AND language='{$baselang}'  ";
    $result = Yii::app()->db->createCommand($query)->query();
    $origGroups = $result->readAll();
    foreach ($languagesToCheck as $lang) {
        $query = "SELECT g.gid FROM $quotedGroups g JOIN {{group_l10ns}} ls ON ls.gid=g.gid WHERE sid={$sid} AND language='{$lang}'  ";
        $gresult = Yii::app()->db->createCommand($query)->queryColumn();
        foreach ($origGroups as $group) {
            if (!in_array($group['gid'], $gresult)) {
                $data = array(
                'gid' => $group['gid'],
                'group_name' => $group['group_name'],
                'description' => $group['description'],
                'language' => $lang
                );
                Yii::app()->db->createCommand()->insert('{{group_l10ns}}', $data);
            }
        }
    }

    $query = "SELECT q.qid, ls.question, ls.help FROM {{questions}} q JOIN {{question_l10ns}} ls ON ls.qid=q.qid WHERE sid={$sid} AND language='{$baselang}'";
    $result = Yii::app()->db->createCommand($query)->query();
    $origQuestions = $result->readAll();
    if (count($origQuestions) > 0) {
        foreach ($languagesToCheck as $lang) {
            $query = "SELECT q.qid FROM {{questions}} q JOIN {{question_l10ns}} ls ON ls.qid=q.qid WHERE sid={$sid} AND language='{$lang}'";
            $gresult = Yii::app()->db->createCommand($query)->queryColumn();
            foreach ($origQuestions as $question) {
                if (!in_array($question['qid'], $gresult)) {
                    $data = array(
                    'qid' => $question['qid'],
                    'question' => $question['question'],
                    'help' => $question['help'],
                    'language' => $lang,
                    );
                    Yii::app()->db->createCommand()->insert('{{question_l10ns}}', $data);
                }
            }
        }
    }

    $query = "SELECT a.aid, ls.answer FROM {{answers}} a
    JOIN {{answer_l10ns}} ls ON ls.aid=a.aid
    JOIN  {{questions}} q on a.qid=q.qid
    WHERE language='{$baselang}' and q.sid={$sid}";
    $baseAnswerResult = Yii::app()->db->createCommand($query)->query();
    $origAnswers = $baseAnswerResult->readAll();
    foreach ($languagesToCheck as $lang) {
        $query = "SELECT a.aid FROM {{answers}} a
        JOIN {{answer_l10ns}} ls ON ls.aid=a.aid
        JOIN  {{questions}} q on a.qid=q.qid
        WHERE language='{$lang}' and q.sid={$sid}";
        $gresult = Yii::app()->db->createCommand($query)->queryColumn();
        foreach ($origAnswers as $answer) {
            if (!in_array($answer['aid'], $gresult)) {
                $data = array(
                'aid' => $answer['aid'],
                'answer' => $answer['answer'],
                'language' => $lang
                );
                Yii::app()->db->createCommand()->insert('{{answer_l10ns}}', $data);
            }
        }
    }

    switchMSSQLIdentityInsert('assessments', true);
    $query = "SELECT id, sid, scope, gid, name, minimum, maximum, message FROM {{assessments}} WHERE sid='{$sid}' AND language='{$baselang}'";
    $result = Yii::app()->db->createCommand($query)->query();
    $origAssessments = $result->readAll();
    foreach ($languagesToCheck as $lang) {
        $query = "SELECT id FROM {{assessments}} WHERE sid='{$sid}' AND language='{$lang}'";
        $gresult = Yii::app()->db->createCommand($query)->queryColumn();
        foreach ($origAssessments as $assessment) {
            if (!in_array($assessment['id'], $gresult)) {
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
    }
    switchMSSQLIdentityInsert('assessments', false);


    $query = "SELECT quotals_quota_id, quotals_name, quotals_message, quotals_url, quotals_urldescrip, quotals_language 
              FROM {{quota_languagesettings}} join {{quota}} q on quotals_quota_id=q.id 
              WHERE q.sid='{$sid}' AND quotals_language='{$baselang}'";
    $result = Yii::app()->db->createCommand($query)->query();
    $origQuotas = $result->readAll();
    foreach ($languagesToCheck as $lang) {
        $query = "SELECT quotals_quota_id FROM {{quota_languagesettings}} join {{quota}} q on quotals_quota_id=q.id WHERE q.sid='{$sid}' AND quotals_language='{$lang}'";
        $qresult = Yii::app()->db->createCommand($query)->queryColumn();
        foreach ($origQuotas as $qls) {
            if (!in_array($qls['quotals_quota_id'], $qresult)) {
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
    }
    return true;
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
* @param string $depgid - (optional) get only the dependencies applying to the group with gid depgid
* @param string $targgid - (optional) get only the dependencies for groups dependents on group targgid
* @param string $indexby - (optional) "by-depgid" for result indexed with $res[$depgid][$targgid]
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
    $condarray = array();
    $sqldepgid = "";
    $sqltarggid = "";
    if ($depgid != "all") {
        $depgid = sanitize_int($depgid);
        $sqldepgid = "AND tq.gid=$depgid";
    }
    if ($targgid != "all") {
        $targgid = sanitize_int($targgid);
        $sqltarggid = "AND tq2.gid=$targgid";
    }

    $baselang = Survey::model()->findByPk($sid)->language;
    $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
    $condquery = "SELECT tg.gid as depgid, ls.group_name as depgpname, "
    . "tg2.gid as targgid, ls2.group_name as targgpname, tq.qid as depqid, tc.cid FROM "
    . "{{conditions}} AS tc, "
    . "{{questions}} AS tq, "
    . "{{questions}} AS tq2, "
    . "$quotedGroups AS tg, "
    . "$quotedGroups AS tg2, "
    . "{{group_l10ns}} as ls,{{group_l10ns}} as ls2 "
    . "WHERE ls.language='{$baselang}' AND ls2.language='{$baselang}' AND tc.qid = tq.qid AND tq.sid=$sid "
    . "AND tq.gid = tg.gid AND tg2.gid = tq2.gid "
    . "AND ls.gid=tg.gid AND ls2.gid=tg2.gid "
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
* @param string $gid - (optional) only search dependecies inside the Group Id $gid
* @param string $depqid - (optional) get only the dependencies applying to the question with qid depqid
* @param string $targqid - (optional) get only the dependencies for questions dependents on question Id targqid
* @param string $indexby - (optional) "by-depqid" for result indexed with $res[$depqid][$targqid]
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

    $condarray = array();

    $baselang = Survey::model()->findByPk($sid)->language;
    $sqlgid = "";
    $sqldepqid = "";
    $sqltargqid = "";
    $sqlsearchscope = "";
    if ($gid != "all") {
        $gid = sanitize_int($gid);
        $sqlgid = "AND tq.gid=$gid";
    }
    if ($depqid != "all") {
        $depqid = sanitize_int($depqid);
        $sqldepqid = "AND tq.qid=$depqid";
    }
    if ($targqid != "all") {
        $targqid = sanitize_int($targqid);
        $sqltargqid = "AND tq2.qid=$targqid";
    }
    if ($searchscope == "samegroup") {
        $sqlsearchscope = "AND tq2.gid=tq.gid";
    }

    $condquery = "SELECT tq.qid as depqid, tq2.qid as targqid, tc.cid
    FROM {{conditions}} AS tc, {{questions}} AS tq, {{questions}} AS tq2
    WHERE tc.qid = tq.qid AND tq.sid='$sid'
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

/**
* Escapes a text value for db
*
* @param string $value
* @return string
*/
function dbQuoteAll($value)
{
    return Yii::app()->db->quoteValue($value);
}

// TMSW Condition->Relevance:  This function is not needed - could replace with a message from EM output.
/**
* checkMoveQuestionConstraintsForConditions()
* @param string $sid - the currently selected survey
* @param string $qid - qid of the question you want to check possible moves
* @param string $newgid - (optional) get only constraints when trying to move to this particular GroupId
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

    $resarray = array();
    $resarray['notAbove'] = null; // defaults to no constraint
    $resarray['notBelow'] = null; // defaults to no constraint
    $sid = sanitize_int($sid);
    $qid = sanitize_int($qid);

    if ($newgid != "all") {
        $newgid = sanitize_int($newgid);
        $newgorder = getGroupOrder($newgid);
    } else {
        $newgorder = ''; // Not used in this case
    }

    $baselang = Survey::model()->findByPk($sid)->language;

    // First look for 'my dependencies': questions on which I have set conditions
    $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
    $condquery = "SELECT tq.qid as depqid, tq.gid as depgid, tg.group_order as depgorder, "
    . "tq2.qid as targqid, tq2.gid as targgid, tg2.group_order as targgorder, "
    . "tc.cid FROM "
    . "{{conditions}} AS tc, "
    . "{{questions}} AS tq, "
    . "{{questions}} AS tq2, "
    . "$quotedGroups AS tg, "
    . "$quotedGroups AS tg2 "
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
                $resarray['notAbove'][] = array($targetgid, $targetgorder, $depqid, $condid);
            }
        } else {
        // get all moves constraints
            $resarray['notAbove'][] = array($targetgid, $targetgorder, $depqid, $condid);
        }
    }

    // Secondly look for 'questions dependent on me': questions that have conditions on my answers
    $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
    $condquery = "SELECT tq.qid as depqid, tq.gid as depgid, tg.group_order as depgorder, "
    . "tq2.qid as targqid, tq2.gid as targgid, tg2.group_order as targgorder, "
    . "tc.cid FROM {{conditions}} AS tc, "
    . "{{questions}} AS tq, "
    . "{{questions}} AS tq2, "
    . "$quotedGroups AS tg, "
    . "$quotedGroups AS tg2 "
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
                $resarray['notBelow'][] = array($depgid, $depgorder, $depqid, $condid);
            }
        } else {
        // get all moves constraints
            $resarray['notBelow'][] = array($depgid, $depgorder, $depqid, $condid);
        }
    }
    return $resarray;
}

/**
* Determines whether the list of user groups will need filtering before viewing.
* @returns bool
*/
function shouldFilterUserGroupList()
{
    $bUserControlSameGroupPolicy = App()->getConfig('usercontrolSameGroupPolicy', true);
    $bUserHasSuperAdminReadPermissions = Permission::model()->hasGlobalPermission('superadmin', 'read');
    return $bUserControlSameGroupPolicy && !$bUserHasSuperAdminReadPermissions;
}

/**
* Get a list of all user groups
* All user group or filtered according to usercontrolSameGroupPolicy
* @returns array
*/
function getUserGroupList()
{
    $sQuery = "SELECT distinct a.ugid, a.name, a.owner_id FROM {{user_groups}} AS a LEFT JOIN {{user_in_groups}} AS b ON a.ugid = b.ugid WHERE 1=1 ";
    if (shouldFilterUserGroupList()) {
        $userid = intval(App()->session['loginID']);
        $sQuery .= " AND (b.uid = {$userid})";
        $sQuery .= " OR (a.owner_id = {$userid})";
    }
    $sQuery .= " ORDER BY name";

    $sresult = App()->db->createCommand($sQuery)->query(); //Checked
    if (!$sresult) {
        return "Database Error";
    }
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

    global $codeString;
    global $modifyoutput;

    $siteadminname = Yii::app()->getConfig('siteadminname');
    $siteadminemail = Yii::app()->getConfig('siteadminemail');
    $success = true; // Let's be optimistic
    $modifyoutput = '';
    $lines = [];
    if (!empty($sqlfile)) {
        if (!is_readable($sqlfile)) {
            $success = false;
            echo '<p>Tried to modify database, but "' . $sqlfile . '" doesn\'t exist!</p>';
            return $success;
        } else {
            $lines = file($sqlfile);
        }
    } else {
        $sqlstring = trim($sqlstring);
        if ($sqlstring[strlen($sqlstring) - 1] != ";") {
            $sqlstring .= ";"; // add it in if it's not there.
        }
        $lines[] = $sqlstring;
    }

    $command = '';

    foreach ($lines as $line) {
        $line = rtrim((string) $line);
        $length = strlen($line);

        if ($length and $line[0] <> '#' and substr($line, 0, 2) <> '--') {
            if (substr($line, $length - 1, 1) == ';') {
                $line = substr($line, 0, $length - 1); // strip ;
                $command .= $line;
                $command = str_replace('prefix_', Yii::app()->db->tablePrefix, $command); // Table prefixes
                $command = str_replace('$defaultuser', Yii::app()->getConfig('defaultuser'), $command);
                $command = str_replace('$defaultpass', hash('sha256', (string) Yii::app()->getConfig('defaultpass')), $command);
                $command = str_replace('$siteadminname', $siteadminname, $command);
                $command = str_replace('$siteadminemail', $siteadminemail, $command);
                $command = str_replace('$defaultlang', Yii::app()->getConfig('defaultlang'), $command);
                $command = str_replace('$databasetabletype', Yii::app()->db->getDriverName(), $command);

                try {
                    Yii::app()->db->createCommand($command)->query(); //Checked
                    $command = htmlspecialchars($command);
                    $modifyoutput .= ". ";
                } catch (CDbException $e) {
                    $command = htmlspecialchars($command);
                    $modifyoutput .= "<br />" . sprintf(gT("SQL command failed: %s"), "<span style='font-size:10px;'>" . $command . "</span>", "<span style='color:#ee0000;font-size:10px;'></span><br/>");
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
        $aLanguages = explode(' ', trim((string) $languages));
    }

    $criteria = new CDbCriteria();
    $criteria->order = "label_name";
    foreach ($aLanguages as $k => $item) {
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
    $surveyid = Yii::app()->session['LEMsid'];
    $oSurvey = Survey::model()->findByPk($surveyid);
    Yii::app()->loadHelper('surveytranslator');

    // Set Langage // TODO remove one of the Yii::app()->session see bug #5901
    if (Yii::app()->session['responses_' . $surveyid]['s_lang']) {
        $languagecode = Yii::app()->session['responses_' . $surveyid]['s_lang'];
    } elseif (isset($surveyid) && $surveyid && $oSurvey) {
        $languagecode = $oSurvey->language;
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
    Yii::app()->clientScript->registerScriptFile(Yii::app()->getConfig("generalscripts") . 'nojs.js', CClientScript::POS_HEAD);
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
* This function fixes the group ID and type on all subquestions,
* or removes the subquestions if the parent question's type doesn't
* allow them.
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
    $aQuestionTypes = QuestionTheme::findQuestionMetaDataForAllTypes(); //be careful!!! only use this if QuestionTheme already exists (see updateDB ...)
    while (count($aRecords) > 0) {
        foreach ($aRecords as $sv) {
            $hasSubquestions = (int)$aQuestionTypes[$sv['type']]['settings']->subquestions;
            if ($hasSubquestions) {
                // If the question type allows subquestions, set the type in each subquestion
                Yii::app()->db->createCommand("update {{questions}} set type='{$sv['type']}', gid={$sv['gid']} where qid={$sv['qid']}")->execute();
            } else {
                // If the question type doesn't allow subquestions, delete each subquestion
                // Model is used because more tables are involved.
                $oSubquestion = Question::model()->find("qid=:qid", array("qid" => $sv['qid']));
                if (!empty($oSubquestion)) {
                    $oSubquestion->delete();
                }
            }
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
    $ans = json_encode($content, JSON_UNESCAPED_UNICODE);
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
    // create a list of key -> value array for accepted encodings
    $encodings = array_combine(array_values(mb_list_encodings()), array_values(mb_list_encodings()));
    // Sort list of encodings
    asort($encodings);
    $encodings = array("auto" => gT("(Automatic)")) + $encodings;
    return $encodings;
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
    return $sBegin . $sEllipsis . $sEnd;
}

/**
 * This function tries to returns the 'real' IP address under all configurations
 * Do not rely security-wise on the detected IP address as except for REMOTE_ADDR all fields could be manipulated by the web client
 *
 * @return  string  Client's IP Address
 */
function getIPAddress()
{
    $sIPAddress = '127.0.0.1';
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP) !== false) {
        //check IP address from share internet
        $sIPAddress = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //Check IP Address passed from proxy
        $vComma = strpos((string) $_SERVER['HTTP_X_FORWARDED_FOR'], ',');
        if (false === $vComma && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP) !== false) {
            // Single forward
            $sIPAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
        // Multiple forward
        // see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
        // TODO: RFC7239 full implementation (https://datatracker.ietf.org/doc/html/rfc7239#section-5.2)
            $aForwarded = explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']);
            if (false !== filter_var($aForwarded[0], FILTER_VALIDATE_IP)) {
                $sIPAddress = $aForwarded[0];
            }
        }
    } elseif (!empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) !== false) {
        // Check IP Address from remote host
        $sIPAddress = $_SERVER['REMOTE_ADDR'];
    }

    return $sIPAddress;
}


/**
 * This function returns the real IP address and should mainly be used for security sensitive purposes
 * If you want to use the IP address for language detection or similar, use getIPAddress() instead
 *
 * @return  string  Client IP Address
 */
function getRealIPAddress()
{
    $sIPAddress = '127.0.0.1';
    if (!empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) !== false) {
        $sIPAddress = $_SERVER['REMOTE_ADDR'];
    }
    // If there is a list of reverse proxy IP addresses, and the current IP address is in that list, we will
    // look for the header that contains the client IP address.
    if (!empty(Yii::app()->getConfig('reverseProxyIpAddresses'))) {
        $reverseProxyIpAddresses = Yii::app()->getConfig('reverseProxyIpAddresses');
        if (in_array($sIPAddress, $reverseProxyIpAddresses)) {
            $reverseProxyIpHeader = Yii::app()->getConfig('reverseProxyIpHeader');
            if (empty($reverseProxyIpHeader)) {
                $reverseProxyIpHeader = 'HTTP_X_FORWARDED_FOR';
            }
            if (isset($_SERVER[$reverseProxyIpHeader]) && filter_var($_SERVER[$reverseProxyIpHeader], FILTER_VALIDATE_IP) !== false) {
                $sIPAddress = $_SERVER[$reverseProxyIpHeader];
            }
        }
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
        $sLanguage = str_replace('_', '-', (string) $sLanguage);
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
        } elseif (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
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
    foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
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
    $string = sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
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
 * @param string $tokenAttributeData The original token attributes as stored in the database
 * @return array|mixed
 */
function decodeTokenAttributes(string $tokenAttributeData)
{
    if (trim($tokenAttributeData) == '') {
        return array();
    }
    if (substr($tokenAttributeData, 0, 1) != '{' && substr($tokenAttributeData, 0, 1) != '[') {
        if (!App()->getConfig('allow_unserialize_attributedescriptions')) {
            return array();
        }
        // minimal broken securisation, mantis issue #20144
        $sSerialType = getSerialClass($tokenAttributeData);
        if ($sSerialType == 'array') {
            $aReturnData = unserialize($tokenAttributeData, ["allowed_classes" => false]) ?? [];
        } else {
            // Something else, sure it's unsafe
            return array();
        }
    } else {
        $aReturnData = json_decode($tokenAttributeData, true) ?? [];
    }
    if ($aReturnData === false || $aReturnData === null) {
        return array();
    }

    // unset core attributes: firstname, lastname, email
    unset($aReturnData['firstname']);
    unset($aReturnData['lastname']);
    unset($aReturnData['email']);
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
    return $aTypes[$aParts[0]] ?? (isset($aParts[2]) ? trim($aParts[2], '"') : null);
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
* @param string $path
* @return string
*/
function get_absolute_path($path)
{
    $startsWithSeparator = $path[0] === '/';
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
    return ($startsWithSeparator ? '/' : '') . implode(DIRECTORY_SEPARATOR, $absolutes);
}

/**
* Check if string is JSON array
*
* @param string $str
* @return bool
*/
function isJson($str)
{
    $json = json_decode((string) $str);
    return $json && $str != $json;
}

/**
* Check if array is associative
*
* @param array $array
* @return bool
*/
function isAssociativeArray($array)
{
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
function createRandomTempDir($dir = null, $prefix = '', $mode = 0700)
{
    $sDir = (empty($dir)) ? Yii::app()->getConfig('tempdir') : get_absolute_path($dir);

    if (substr((string) $sDir, -1) != DIRECTORY_SEPARATOR) {
        $sDir .= DIRECTORY_SEPARATOR;
    }

    do {
        $sRandomString = getRandomString();
        $path = $sDir . $prefix . $sRandomString;
    } while (!mkdir($path, $mode));

    return $path;
}

/**
 * Generate a random string, using openssl if available, else using md5
 * @param  int    $length wanted lenght of the random string (only for openssl mode)
 * @return string
 */
function getRandomString($length = 32)
{

    if (function_exists('openssl_random_pseudo_bytes')) {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[crypto_rand_secure(0, strlen($codeAlphabet))];
        }
    } else {
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
function crypto_rand_secure($min, $max)
{
        $range = $max - $min;
    if ($range < 0) {
        return $min; // not so random...
    }
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
    $isZipBomb = false;
    $zip = new LimeSurvey\Zip();
    if ($zip->open($zip_filename, 0, false) === true) {
        $isZipBomb = $zip->isZipBomb();
        $zip->close();
    }
    return $isZipBomb;
}

/**
 * Get the original size of a zip archive to prevent Zip Bombing
 * see comment here : http://php.net/manual/en/function.zip-entry-filesize.php
 * @param string $filename
 * @return int
 */
function get_zip_originalsize($filename)
{

    if (class_exists('ZipArchive')) {
        $size = 0;
        $zip = new ZipArchive();
        $zip->open($filename);

        for ($i = 0; $i < $zip->numFiles; $i++) {
                $aEntry = $zip->statIndex($i);
                $size += $aEntry['size'];
        }
        $zip->close();
        return $size;
    } else {
        if (YII_DEBUG) {
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
    if ($isCountable) {
        return count($element);
    }
    return 0;
}

/**
* This function switches identity insert on/off for the MSSQL database
*
* @param string $table table name (without prefix)
* @param boolean $state  Set to true to activate ID insert, or false to deactivate
* @return void
*/
function switchMSSQLIdentityInsert($table, $state)
{
    if (in_array(Yii::app()->db->getDriverName(), array('mssql', 'sqlsrv', 'dblib'))) {
        if ($state === true) {
            // This needs to be done directly on the PDO object because when using CdbCommand or similar
            // it won't have any effect
            Yii::app()->db->pdoInstance->exec('SET IDENTITY_INSERT ' . Yii::app()->db->tablePrefix . $table . ' ON');
        } else {
            // This needs to be done directly on the PDO object because when using CdbCommand or similar
            // it won't have any effect
            Yii::app()->db->pdoInstance->exec('SET IDENTITY_INSERT ' . Yii::app()->db->tablePrefix . $table . ' OFF');
        }
    }
}

/**
 * Helper to filter the contents of a .zip file uploaded into the file manager
 */
function resourceExtractFilter($p_event, &$p_header)
{
    $aAllowExtensions = Yii::app()->getConfig('allowedfileuploads');
    $info = pathinfo((string) $p_header['filename']);
    if ($p_header['folder'] || !isset($info['extension']) || in_array($info['extension'], $aAllowExtensions)) {
        return 1;
    } else {
        return 0;
    }
}

/**
 * Applies preg_replace recursively until $recursion_limit is exceeded or no more replacements are done.
 * @param array|string $pattern
 * @param array|string $replacement
 * @param array|string $subject
 * @param int $limit
 * @param int $count    If specified, this variable will be filled with the total number of replacements done (including all iterations)
 * @param int $recursion_limit  Max number of iterations allowed
 * @return string|array
 */
function recursive_preg_replace($pattern, $replacement, $subject, $limit = -1, &$count = 0, $recursion_limit = 50)
{
    if ($recursion_limit < 0) {
        return $subject;
    }
    if (empty($subject)) {
        return $subject;
    }
    $result = preg_replace($pattern, $replacement, $subject, $limit, $count);
    if ($count > 0) {
        $result = recursive_preg_replace($pattern, $replacement, $result, $limit, $auxCount, --$recursion_limit);
        $count += $auxCount;
    }
    return $result;
}

/**
 * Returns the standard deviation of supplied $numbers
 * @param array $numbers The numbers to calculate the standard deviation for
 * @return float
 */
function standardDeviation(array $numbers): float
{
    // Filter empty "" records
    $numbers = array_filter($numbers);
    $numberOfElements = count($numbers);

    $variance = 0.0;
    $average = array_sum($numbers) / $numberOfElements;

    foreach ($numbers as $i) {
        // sum of squares of differences between all numbers
        $variance += ($i - $average) ** 2;
    }

    return sqrt($variance / $numberOfElements);
}

/**
 * Checks if the specified path is absolute.
 * It handles both Unix and Windows paths.
 * @param string $path the path to be checked
 * @return bool whether the path is absolute
 */
function isAbsolutePath($path)
{
    if (strlen($path) == 0) {
        // Empty path is relative by definition
        return false;
    } elseif ($path[0] == '/') {
        // Absolute path on Unix-based systems
        return true;
    } elseif (preg_match('/^[a-zA-Z]:\\\\/', $path)) {
        // Absolute path on Windows systems, e.g. C:\path\to\file
        return true;
    } else {
        // Relative path
        return false;
    }
}

/**
 * Escapes a string for use in a CSV file
 * @param string|null $string
 * @return string
 */
function csvEscape($string)
{
    if (empty($string)) {
        return $string;
    }

    // Escape formulas to avoid CSV injection.
    // If the string starts with =, +, -, @, tab or carriage return, prepend a single quote.
    if (in_array(substr($string, 0, 1), ['=', '-', '+', '@', "\t", "\r"], true)) {
        $string = "'" . $string;
    }

    // Normalize line endings
    $string = preg_replace('~\R~u', "\n", $string);

    // Escape double quotes and wrap the string in double quotes
    $string = '"' . str_replace('"', '""', $string) . '"';

    return $string;
}
