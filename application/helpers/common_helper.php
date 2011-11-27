<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
*	$Id: common_helper.php 11335 2011-11-08 12:06:48Z c_schmitz $
*	Files Purpose: lots of common functions
*/

Yii::import('application.helpers.sanitize_helper', true);

/**
* Simple function to sort the permissions by title
*
* @param mixed $aPermissionA  Permission A to compare
* @param mixed $aPermissionB  Permission B to compare
*/
function aComparePermission($aPermissionA,$aPermissionB)
{
    if($aPermissionA['title'] >$aPermissionB['title']) {
        return 1;
    }
    else {
        return -1;
    }
}

/**
* getqtypelist() Returns list of question types available in LimeSurvey. Edit this if you are adding a new
*    question type
*
* @param string $SelectedCode Value of the Question Type (defaults to "T")
* @param string $ReturnType Type of output from this function (defaults to selector)
*
* @return depending on $ReturnType param, returns a straight "array" of question types, or an <option></option> list
*
* Explanation of questiontype array:
*
* description : Question description
* subquestions : 0= Does not support subquestions x=Number of subquestion scales
* answerscales : 0= Does not need answers x=Number of answer scales (usually 1, but e.g. for dual scale question set to 2)
* assessable : 0=Does not support assessment values when editing answerd 1=Support assessment values
*/
function getqtypelist($SelectedCode = "T", $ReturnType = "selector")
{
    $publicurl = Yii::app()->getConfig('publicurl');
    $clang = Yii::app()->lang;

    $group['Arrays'] = $clang->gT('Arrays');
    $group['MaskQuestions'] = $clang->gT("Mask questions");
    $group['SinChoiceQues'] = $clang->gT("Single choice questions");
    $group['MulChoiceQues'] = $clang->gT("Multiple choice questions");
    $group['TextQuestions'] = $clang->gT("Text questions");


    $qtypes = array(
    "1"=>array('description'=>$clang->gT("Array dual scale"),
    'group'=>$group['Arrays'],
    'subquestions'=>1,
    'assessable'=>1,
    'hasdefaultvalues'=>0,
    'answerscales'=>2),
    "5"=>array('description'=>$clang->gT("5 Point Choice"),
    'group'=>$group['SinChoiceQues'],
    'subquestions'=>0,
    'hasdefaultvalues'=>0,
    'assessable'=>0,
    'answerscales'=>0),
    "A"=>array('description'=>$clang->gT("Array (5 Point Choice)"),
    'group'=>$group['Arrays'],
    'subquestions'=>1,
    'hasdefaultvalues'=>0,
    'assessable'=>1,
    'answerscales'=>0),
    "B"=>array('description'=>$clang->gT("Array (10 Point Choice)"),
    'group'=>$group['Arrays'],
    'subquestions'=>1,
    'hasdefaultvalues'=>0,
    'assessable'=>1,
    'answerscales'=>0),
    "C"=>array('description'=>$clang->gT("Array (Yes/No/Uncertain)"),
    'group'=>$group['Arrays'],
    'subquestions'=>1,
    'hasdefaultvalues'=>0,
    'assessable'=>1,
    'answerscales'=>0),
    "D"=>array('description'=>$clang->gT("Date/Time"),
    'group'=>$group['MaskQuestions'],
    'subquestions'=>0,
    'hasdefaultvalues'=>0,
    'assessable'=>0,
    'answerscales'=>0),
    "E"=>array('description'=>$clang->gT("Array (Increase/Same/Decrease)"),
    'group'=>$group['Arrays'],
    'subquestions'=>1,
    'hasdefaultvalues'=>0,
    'assessable'=>1,
    'answerscales'=>0),
    "F"=>array('description'=>$clang->gT("Array"),
    'group'=>$group['Arrays'],
    'subquestions'=>1,
    'hasdefaultvalues'=>0,
    'assessable'=>1,
    'answerscales'=>1),
    "G"=>array('description'=>$clang->gT("Gender"),
    'group'=>$group['MaskQuestions'],
    'subquestions'=>0,
    'hasdefaultvalues'=>0,
    'assessable'=>0,
    'answerscales'=>0),
    "H"=>array('description'=>$clang->gT("Array by column"),
    'group'=>$group['Arrays'],
    'hasdefaultvalues'=>0,
    'subquestions'=>1,
    'assessable'=>1,
    'answerscales'=>1),
    "I"=>array('description'=>$clang->gT("Language Switch"),
    'group'=>$group['MaskQuestions'],
    'hasdefaultvalues'=>0,
    'subquestions'=>0,
    'assessable'=>0,
    'answerscales'=>0),
    "K"=>array('description'=>$clang->gT("Multiple Numerical Input"),
    'group'=>$group['MaskQuestions'],
    'hasdefaultvalues'=>0,
    'subquestions'=>1,
    'assessable'=>1,
    'answerscales'=>0),
    "L"=>array('description'=>$clang->gT("List (Radio)"),
    'group'=>$group['SinChoiceQues'],
    'subquestions'=>0,
    'hasdefaultvalues'=>1,
    'assessable'=>1,
    'answerscales'=>1),
    "M"=>array('description'=>$clang->gT("Multiple choice"),
    'group'=>$group['MulChoiceQues'],
    'subquestions'=>1,
    'hasdefaultvalues'=>1,
    'assessable'=>1,
    'answerscales'=>0),
    "N"=>array('description'=>$clang->gT("Numerical Input"),
    'group'=>$group['MaskQuestions'],
    'subquestions'=>0,
    'hasdefaultvalues'=>0,
    'assessable'=>0,
    'answerscales'=>0),
    "O"=>array('description'=>$clang->gT("List with comment"),
    'group'=>$group['SinChoiceQues'],
    'subquestions'=>0,
    'hasdefaultvalues'=>1,
    'assessable'=>1,
    'answerscales'=>1),
    "P"=>array('description'=>$clang->gT("Multiple choice with comments"),
    'group'=>$group['MulChoiceQues'],
    'subquestions'=>1,
    'hasdefaultvalues'=>1,
    'assessable'=>1,
    'answerscales'=>0),
    "Q"=>array('description'=>$clang->gT("Multiple Short Text"),
    'group'=>$group['TextQuestions'],
    'subquestions'=>1,
    'hasdefaultvalues'=>0,
    'assessable'=>0,
    'answerscales'=>0),
    "R"=>array('description'=>$clang->gT("Ranking"),
    'group'=>$group['MaskQuestions'],
    'subquestions'=>0,
    'hasdefaultvalues'=>0,
    'assessable'=>1,
    'answerscales'=>1),
    "S"=>array('description'=>$clang->gT("Short Free Text"),
    'group'=>$group['TextQuestions'],
    'subquestions'=>0,
    'hasdefaultvalues'=>0,
    'assessable'=>0,
    'answerscales'=>0),
    "T"=>array('description'=>$clang->gT("Long Free Text"),
    'group'=>$group['TextQuestions'],
    'subquestions'=>0,
    'hasdefaultvalues'=>0,
    'assessable'=>0,
    'answerscales'=>0),
    "U"=>array('description'=>$clang->gT("Huge Free Text"),
    'group'=>$group['TextQuestions'],
    'subquestions'=>0,
    'hasdefaultvalues'=>0,
    'assessable'=>0,
    'answerscales'=>0),
    "X"=>array('description'=>$clang->gT("Text display"),
    'group'=>$group['MaskQuestions'],
    'subquestions'=>0,
    'hasdefaultvalues'=>0,
    'assessable'=>0,
    'answerscales'=>0),
    "Y"=>array('description'=>$clang->gT("Yes/No"),
    'group'=>$group['MaskQuestions'],
    'subquestions'=>0,
    'hasdefaultvalues'=>0,
    'assessable'=>0,
    'answerscales'=>0),
    "!"=>array('description'=>$clang->gT("List (Dropdown)"),
    'group'=>$group['SinChoiceQues'],
    'subquestions'=>0,
    'hasdefaultvalues'=>1,
    'assessable'=>1,
    'answerscales'=>1),
    ":"=>array('description'=>$clang->gT("Array (Numbers)"),
    'group'=>$group['Arrays'],
    'subquestions'=>2,
    'hasdefaultvalues'=>0,
    'assessable'=>1,
    'answerscales'=>0),
    ";"=>array('description'=>$clang->gT("Array (Texts)"),
    'group'=>$group['Arrays'],
    'subquestions'=>2,
    'hasdefaultvalues'=>0,
    'assessable'=>0,
    'answerscales'=>0),
    "|"=>array('description'=>$clang->gT("File upload"),
    'group'=>$group['MaskQuestions'],
    'subquestions'=>0,
    'hasdefaultvalues'=>0,
    'assessable'=>0,
    'answerscales'=>0),
    "*"=>array('description'=>$clang->gT("Equation"),
    'group'=>$group['MaskQuestions'],
    'subquestions'=>0,
    'hasdefaultvalues'=>0,
    'assessable'=>0,
    'answerscales'=>0),
    );
    asort($qtypes);
    if ($ReturnType == "array") {return $qtypes;}
    if ($ReturnType == "group"){
        foreach($qtypes as $qkey=>$qtype){
            $newqType[$qtype['group']][$qkey] = $qtype;
        }


        $qtypeselecter = "";
        foreach($newqType as $group=>$members)
        {
            $qtypeselecter .= '<optgroup label="'.$group.'">';
            foreach($members as $TypeCode=>$TypeProperties)
            {
                $qtypeselecter .= "<option value='$TypeCode'";
                if ($SelectedCode == $TypeCode) {$qtypeselecter .= " selected='selected'";}
                $qtypeselecter .= ">{$TypeProperties['description']}</option>\n";
            }
            $qtypeselecter .= '</optgroup>';
        }

        return $qtypeselecter;

    };
    $qtypeselecter = "";
    foreach($qtypes as $TypeCode=>$TypeProperties)
    {
        $qtypeselecter .= "<option value='$TypeCode'";
        if ($SelectedCode == $TypeCode) {$qtypeselecter .= " selected='selected'";}
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
    return in_array($sTemplateName,array('basic',
    'bluengrey',
    'business_grey',
    'citronade',
    'clear_logo',
    'default',
    'eirenicon',
    'limespired',
    'mint_idea',
    'sherpa',
    'vallendar'));
}

/**
 * getsurveylist() Queries the database (survey table) for a list of existing surveys
 *
 * @param boolean $returnarray if set to true an array instead of an HTML option list is given back
 * @return string This string is returned containing <option></option> formatted list of existing surveys
 *
 */
function getsurveylist($returnarray=false, $returnwithouturl=false, $surveyid=false)
{
    static $cached = null;

	$timeadjust = getGlobalSetting('timeadjust');
	$clang = new Limesurvey_lang(array('langcode' => Yii::app()->session['adminlang']));

    if(is_null($cached)) {
    	if (!bHasGlobalPermission('USER_RIGHT_SUPERADMIN'))
    		$surveyidresult = Survey::model()->permission(Yii::app()->user->getId())->with('languagesettings')->findAll();
    	else
    		$surveyidresult = Survey::model()->with('languagesettings')->findAll();

        if (!$surveyidresult) {return "Database Error";}

        $surveynames = array();
    	foreach ($surveyidresult as $result)
    		$surveynames[] = array_merge($result->attributes, $result->languagesettings->attributes);

        $cached = $surveynames;
    } else {
        $surveynames = $cached;
    }
    $surveyselecter = "";
    if ($returnarray===true) return $surveynames;
    $activesurveys='';
    $inactivesurveys='';
    $expiredsurveys='';
    if ($surveynames)
    {
        foreach($surveynames as $sv)
        {

            $surveylstitle=FlattenText($sv['surveyls_title']);
            if (strlen($surveylstitle)>45)
            {
                $surveylstitle = htmlspecialchars(mb_strcut(html_entity_decode($surveylstitle,ENT_QUOTES,'UTF-8'), 0, 45, 'UTF-8'))."...";
            }

            if($sv['active']!='Y')
            {
                $inactivesurveys .= "<option ";
                if(Yii::app()->user->getId() == $sv['owner_id'])
                {
                    $inactivesurveys .= " style=\"font-weight: bold;\"";
                }
                if ($sv['sid'] == $surveyid)
                {
                    $inactivesurveys .= " selected='selected'"; $svexist = 1;
                }
                if ($returnwithouturl===false)
                {
                    $inactivesurveys .=" value='".Yii::app()->createUrl("admin/survey/view/".$sv['sid'])."'>{$surveylstitle}</option>\n";
                } else
                {
                    $inactivesurveys .=" value='{$sv['sid']}'>{$surveylstitle}</option>\n";
                }
            } elseif($sv['expires']!='' && $sv['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust))
            {
                $expiredsurveys .="<option ";
                if (Yii::app()->user->getId() == $sv['owner_id'])
                {
                    $expiredsurveys .= " style=\"font-weight: bold;\"";
                }
                if ($sv['sid'] == $surveyid)
                {
                    $expiredsurveys .= " selected='selected'"; $svexist = 1;
                }
                if ($returnwithouturl===false)
                {
                    $expiredsurveys .=" value='".Yii::app()->createUrl("admin/survey/view/".$sv['sid'])."'>{$surveylstitle}</option>\n";
                } else
                {
                    $expiredsurveys .=" value='{$sv['sid']}'>{$surveylstitle}</option>\n";
                }
            } else
            {
                $activesurveys .= "<option ";
                if(Yii::app()->user->getId() == $sv['owner_id'])
                {
                    $activesurveys .= " style=\"font-weight: bold;\"";
                }
                if ($sv['sid'] == $surveyid)
                {
                    $activesurveys .= " selected='selected'"; $svexist = 1;
                }
                if ($returnwithouturl===false)
                {
                    $activesurveys .=" value='".Yii::app()->createUrl("admin/survey/view/".$sv['sid'])."'>{$surveylstitle}</option>\n";
                } else
                {
                    $activesurveys .=" value='{$sv['sid']}'>{$surveylstitle}</option>\n";
                }
            }
        } // End Foreach
    }
    //Only show each activesurvey group if there are some
    if ($activesurveys!='')
    {
        $surveyselecter .= "<optgroup label='".$clang->gT("Active")."' class='activesurveyselect'>\n";
        $surveyselecter .= $activesurveys . "</optgroup>";
    }
    if ($expiredsurveys!='')
    {
        $surveyselecter .= "<optgroup label='".$clang->gT("Expired")."' class='expiredsurveyselect'>\n";
        $surveyselecter .= $expiredsurveys . "</optgroup>";
    }
    if ($inactivesurveys!='')
    {
        $surveyselecter .= "<optgroup label='".$clang->gT("Inactive")."' class='inactivesurveyselect'>\n";
        $surveyselecter .= $inactivesurveys . "</optgroup>";
    }
    if (!isset($svexist))
    {
        $surveyselecter = "<option selected='selected' value=''>".$clang->gT("Please choose...")."</option>\n".$surveyselecter;
    } else
    {
        if ($returnwithouturl===false)
        {
            $surveyselecter = "<option value='".Yii::app()->createUrl("/admin")."'>".$clang->gT("None")."</option>\n".$surveyselecter;
        } else
        {
            $surveyselecter = "<option value=''>".$clang->gT("None")."</option>\n".$surveyselecter;
        }
    }
    return $surveyselecter;
}

/**
* Returns true if a user has permissions in the particular survey
*
* @param $iSID The survey ID
* @param $sPermission
* @param $sCRUD
* @param $iUID User ID - if not given the one of the current user is used
* @return bool
*/
function bHasSurveyPermission($iSID, $sPermission, $sCRUD, $iUID=null)
{
    if (!in_array($sCRUD,array('create','read','update','delete','import','export'))) return false;
    $sCRUD=$sCRUD.'_p';
    $iSID = (int)$iSID;
    if ($iSID==0) return false;
    $aSurveyPermissionCache = Yii::app()->getConfig("aSurveyPermissionCache");

    if (is_null($iUID))
    {
        if (!Yii::app()->user->getIsGuest()) $iUID = Yii::app()->session['loginID'];
        else return false;
        if (Yii::app()->session['USER_RIGHT_SUPERADMIN']==1) return true; //Superadmin has access to all
    }

    if (!isset($aSurveyPermissionCache[$iSID][$iUID][$sPermission][$sCRUD]))
    {
    	//!!! Convert this model
        $query = Survey_permissions::model()->findByAttributes(array("sid"=> $iSID,"uid"=> $iUID,"permission"=>$sPermission));
        //$sSQL = "SELECT {$sCRUD} FROM " . db_table_name('survey_permissions') . "
        //        WHERE sid={$iSID} AND uid = {$iUID}
        //        and permission=".db_quoteall($sPermission); //Getting rights for this survey
        $bPermission = is_null($query) ? array() : $query->attributes; //$connect->GetOne($sSQL);
        if (!isset($bPermission[$sCRUD]) || $bPermission[$sCRUD]==0)
        {
            $bPermission=false;
        }
        else
        {
            $bPermission=true;
        }
        $aSurveyPermissionCache[$iSID][$iUID][$sPermission][$sCRUD]=$bPermission;
    }
    Yii::app()->setConfig("aSurveyPermissionCache", $aSurveyPermissionCache);
    return $aSurveyPermissionCache[$iSID][$iUID][$sPermission][$sCRUD];
}

/**
* Returns true if a user has global permission for a certain action. Available permissions are
*
* USER_RIGHT_CREATE_SURVEY
* USER_RIGHT_CONFIGURATOR
* USER_RIGHT_CREATE_USER
* USER_RIGHT_DELETE_USER
* USER_RIGHT_SUPERADMIN
* USER_RIGHT_MANAGE_TEMPLATE
* USER_RIGHT_MANAGE_LABEL
*
* @param $sPermission
* @return bool
*/
function bHasGlobalPermission($sPermission)
{
    if (!Yii::app()->user->getIsGuest()) $iUID = !Yii::app()->user->getId();
    else return false;
    if (Yii::app()->session['USER_RIGHT_SUPERADMIN']==1) return true; //Superadmin has access to all
    if (Yii::app()->session[$sPermission]==1)
    {
        return true;
    }
    else
    {
        return false;
    }

}

function gettemplatelist()
{
    $usertemplaterootdir=Yii::app()->getConfig("usertemplaterootdir");
    $standardtemplaterootdir=Yii::app()->getConfig("standardtemplaterootdir");

    if (!$usertemplaterootdir) {die("gettemplatelist() no template directory");}
    if ($handle = opendir($standardtemplaterootdir))
    {
        while (false !== ($file = readdir($handle)))
        {
            if (!is_file("$standardtemplaterootdir/$file") && $file != "." && $file != ".." && $file!=".svn" && isStandardTemplate($file))
            {
                $list_of_files[$file] = $standardtemplaterootdir.DIRECTORY_SEPARATOR.$file;
            }
        }
        closedir($handle);
    }

    if ($handle = opendir($usertemplaterootdir))
    {
        while (false !== ($file = readdir($handle)))
        {
            if (!is_file("$usertemplaterootdir/$file") && $file != "." && $file != ".." && $file!=".svn")
            {
                $list_of_files[$file] = $usertemplaterootdir.DIRECTORY_SEPARATOR.$file;
            }
        }
        closedir($handle);
    }
    ksort($list_of_files);

    return $list_of_files;
}


/**
* getQuestions() queries the database for an list of all questions matching the current survey and group id
*
* @return This string is returned containing <option></option> formatted list of questions in the current survey and group
*/
function getQuestions($surveyid,$gid,$selectedqid)
{
	$clang = Yii::app()->lang;
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
	$qrows = Questions::model()->findAllByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'language' => $s_lang));

    if (!isset($questionselecter)) {$questionselecter="";}
    foreach ($qrows as $qrow)
    {
    	$qrow = $qrow->attributes;
        $qrow['title'] = strip_tags($qrow['title']);
        $link = Yii::app()->createUrl("/admin/survey/sa/view/surveyid".$surveyid."/gid".$gid."/qid".$qrow['qid']);
        $questionselecter .= "<option value='{$link}'";
        if ($selectedqid == $qrow['qid']) {$questionselecter .= " selected='selected'"; $qexists="Y";}
        $questionselecter .=">{$qrow['title']}:";
        $questionselecter .= " ";
        $question=FlattenText($qrow['question'],true);
        if (strlen($question)<35)
        {
            $questionselecter .= $question;
        }
        else
        {
            $questionselecter .= htmlspecialchars(mb_strcut(html_entity_decode($question,ENT_QUOTES,'UTF-8'), 0, 35, 'UTF-8'))."...";
        }
        $questionselecter .= "</option>\n";
    }

    if (!isset($qexists))
    {
        $questionselecter = "<option selected='selected'>".$clang->gT("Please choose...")."</option>\n".$questionselecter;
    }
    return $questionselecter;
}

/**
* getGidPrevious() returns the Gid of the group prior to the current active group
*
* @param string $surveyid
* @param string $gid
*
* @return The Gid of the previous group
*/
function getGidPrevious($surveyid, $gid)
{
    $clang = Yii::app()->lang;

    if (!$surveyid) {$surveyid=returnglobal('sid');}
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $qresult = Groups::model()->findAllByAttributes(array('sid' => $surveyid, 'language' => $s_lang)); //checked

    $i = 0;
    $iPrev = -1;
        foreach ($qresult as $qrow)
        {
        	$qrow = $qrow->attributes;
            if ($gid == $qrow['gid']) {$iPrev = $i - 1;}
            $i += 1;
        }

    if ($iPrev >= 0) {$GidPrev = $qresult[$iPrev]->gid;}
    else {$GidPrev = "";}
    return $GidPrev;
}

/**
* getQidPrevious() returns the Qid of the question prior to the current active question
*
* @param string $surveyid
* @param string $gid
* @param string $qid
*
* @return This Qid of the previous question
*/
function getQidPrevious($surveyid, $gid, $qid)
{
    /* $CI =& get_instance();
    $CI->load->helper("database");
    //$clang =  $CI->limesurvey_lang;
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->model('questions_model');
    //$qquery = "SELECT * FROM ".$CI->db->dbprefix."questions WHERE sid=$surveyid AND gid=$gid AND language='{$s_lang}' and parent_qid=0 order by question_order";
    $qquery = "SELECT qid FROM ".$CI->db->dbprefix."questions WHERE sid=$surveyid AND gid=$gid AND language='{$s_lang}' and parent_qid=0 order by question_order";
    //$qresult = db_execute_assoc($qquery);
    $qresult = $CI->questions_model->getQuestionID($surveyid,$gid,$s_lang); //checked)
    $qrows = $qresult->result_array();
    var_dump($qrows);*/
    $clang = Yii::app()->lang;
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    //$CI->load->model('questions_model');
    //$qquery = "SELECT qid FROM ".$CI->db->dbprefix."questions WHERE sid=$surveyid AND gid=$gid AND language='{$s_lang}' and parent_qid=0 order by question_order";
    //$qresult = db_execute_assoc($qquery) ;
	$qrows = Questions::model()->findAllByAttributes(array('gid' => $gid, 'sid' => $surveyid, 'language' => $s_lang));

    $i = 0;
    $iPrev = -1;
    if (count($qrows) > 0)
    {

        foreach ($qrows as $qrow)
        {
			$qrow = $qrow->attributes;
            if ($qid == $qrow['qid']) {$iPrev = $i - 1;}
            $i += 1;
        }
    }
    if ($iPrev >= 0) {$QidPrev = $qrows[$iPrev]->qid;}
    else {$QidPrev = "";}


    return $QidPrev;
}

/**
* getGidNext() returns the Gid of the group next to the current active group
*
* @param string $surveyid
* @param string $gid
*
* @return The Gid of the next group
*/
function getGidNext($surveyid, $gid)
{
    $clang = Yii::app()->lang;
    if (!$surveyid) {$surveyid=returnglobal('sid');}
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);

    //$gquery = "SELECT gid FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='{$s_lang}' ORDER BY group_order";

	$qresult = Groups::model()->findAllByAttributes(array('sid' => $surveyid, 'language' => $s_lang)); //checked

    $GidNext="";
    $i = 0;
    $iNext = 1;

        foreach ($qresult as $qrow)
        {
        	$qrow = $qrow->attributes;

            if ($gid == $qrow['gid']) {$iNext = $i + 1;}
            $i += 1;
        }

    if ($iNext < count($qresult)) {$GidNext = $qresult[$iNext]->gid;}
    else {$GidNext = "";}
    return $GidNext;
}

/**
* getQidNext() returns the Qid of the question prior to the current active question
*
* @param string $surveyid
* @param string $gid
* @param string $qid
*
* @return This Qid of the previous question
*/
function getQidNext($surveyid, $gid, $qid)
{
    $clang = Yii::app()->lang;
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    //$CI->load->model('questions_model');
    //$qquery = "SELECT qid FROM ".$CI->db->dbprefix."questions WHERE sid=$surveyid AND gid=$gid AND language='{$s_lang}' and parent_qid=0 order by question_order";
    //$qresult = db_execute_assoc($qquery) ;
	$qrows = Questions::model()->findAllByAttributes(array('gid' => $gid, 'sid' => $surveyid, 'language' => $s_lang));


    $i = 0;
    $iNext = 1;
    if (count($qrows) > 0)
    {
        foreach ($qrows as $qrow)
        {
            if ($qid == $qrow->qid) {$iNext = $i + 1;}
            $i += 1;
        }
    }
    if ($iNext < count($qrows)) {$QidNext = $qrows[$iNext]->qid;}
    else {$QidNext = "";}
    return $QidNext;
}

function get2post($url)
{
    $url = preg_replace('/&amp;/i','&',$url);
    $stack = explode('?',$url);
    $calledscript = array_shift($stack);
    $query = array_shift($stack);
    $aqueryitems = explode('&',$query);
    $arrayParam = Array();
    $arrayVal = Array();

    foreach ($aqueryitems as $queryitem)
    {
        $stack =  explode ('=', $queryitem);
        $paramname = array_shift($stack);
        $value = array_shift($stack);
        $arrayParam[] = "'".$paramname."'";
        $arrayVal[] = substr($value, 0, 9) != "document." ? "'".$value."'" : $value;
    }
    //	$Paramlist = "[" . implode(",",$arrayParam) . "]";
    //	$Valuelist = "[" . implode(",",$arrayVal) . "]";
    $Paramlist = "new Array(" . implode(",",$arrayParam) . ")";
    $Valuelist = "new Array(" . implode(",",$arrayVal) . ")";
    $callscript = "sendPost('$calledscript','".Yii::app()->session['checksessionpost']."',$Paramlist,$Valuelist);";
    return $callscript;
}


/**
* This function calculates how much space is actually used by all files uploaded
* using the File Upload question type
*
* @returns integer Actual space used in MB
*/
function fCalculateTotalFileUploadUsage(){
    global $uploaddir;
    $sQuery="select sid from ".db_table_name('surveys');
    $oResult = db_execute_assoc($sQuery); //checked
    $aRows = $oResult->GetRows();
    $iTotalSize=0.0;
    foreach ($aRows as $aRow)
    {
        $sFilesPath=$uploaddir.'/surveys/'.$aRow['sid'].'/files';
        if (file_exists($sFilesPath))
        {
            $iTotalSize+=(float)iGetDirectorySize($sFilesPath);
        }
    }
    return (float)$iTotalSize/1024/1024;
}

function iGetDirectorySize($directory) {
    $size = 0;
    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
        $size+=$file->getSize();
    }
    return $size;
}


/**
* Gets number of groups inside a particular survey
*
* @param string $surveyid
* @param mixed $lang
*/
function getGroupSum($surveyid, $lang)
{
    //$condn = "WHERE sid=".$surveyid." AND language='".$lang."'"; //Getting a count of questions for this survey
    $condn = array('sid'=>$surveyid,'language'=>$lang);
    $sumresult3 = count(Groups::model()->findAllByAttributes($condn)); //Checked)

    return $sumresult3 ;
}


/**
* Gets number of questions inside a particular group
*
* @param string $surveyid
* @param mixed $groupid
*/
function getQuestionSum($surveyid, $groupid)
{
    $CI= &get_instance();
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->model('questions_model');
    //$condn = "WHERE gid=$groupid and sid=$surveyid AND language='{$s_lang}'"; //Getting a count of questions for this survey
    $condn = array(
    'gid' => $groupid,
    'sid' => $surveyid,
    'language' => $s_lang

    );
    $sumresult3 = $CI->questions_model->getAllRecords($condn); //Checked
    $questionscount = $sumresult3->num_rows();
    return $questionscount ;
}


/**
* getMaxgrouporder($surveyid) queries the database for the maximum sortorder of a group and returns the next higher one.
*
* @param mixed $surveyid
*/
function getMaxgrouporder($surveyid)
{
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);

    //$max_sql = "SELECT max( group_order ) AS max FROM ".db_table_name('groups')." WHERE sid =$surveyid AND language='{$s_lang}'" ;
    $query = Groups::model()->find(array('order' => 'group_order desc'));
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

    $s_lang = GetBaseLanguageFromSurveyID($surveyid);

    //$grporder_sql = "SELECT group_order FROM ".db_table_name('groups')." WHERE sid =$surveyid AND language='{$s_lang}' AND gid=$gid" ;
    $grporder_result = Groups::model()->findByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'language' => $s_lang)); //Checked
    $grporder_row = $grporder_result->attributes ;
    $group_order = $grporder_row['group_order'];
    if($group_order=="")
    {
        return "0" ;
    }
    else return $group_order ;
}

/**
* getMaxquestionorder($gid) queries the database for the maximum sortorder of a question.
*
*/
function getMaxquestionorder($gid,$surveyid)
{
    $gid=sanitize_int($gid);
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $max_sql = "SELECT max( question_order ) AS max FROM {{questions}} WHERE gid='$gid' AND language='$s_lang'";

    $max_result = Yii::app()->db->createCommand($max_sql)->query(); //Checked
    $maxrow = $max_result->read() ;
    $current_max = $maxrow['max'];
    if($current_max=="")
    {
        return "0" ;
    }
    else return $current_max ;
}

/**
* question_class() returns a class name for a given question type to allow custom styling for each question type.
*
* @param string $input containing unique character representing each question type.
* @return string containing the class name for a given question type.
*/
function question_class($input)
{

    switch($input)
    {   // I think this is a bad solution to adding classes to question
        // DIVs but I can't think of a better solution. (eric_t_cruiser)

        case 'X': return 'boilerplate';     //  BOILERPLATE QUESTION
        case '5': return 'choice-5-pt-radio';   //  5 POINT CHOICE radio-buttons
        case 'D': return 'date';        //  DATE
        case 'Z': return 'list-radio-flexible'; //  LIST Flexible radio-button
        case 'L': return 'list-radio';      //  LIST radio-button
        case 'W': return 'list-dropdown-flexible'; //   LIST drop-down (flexible label)
        case '!': return 'list-dropdown';   //  List - dropdown
        case 'O': return 'list-with-comment';   //  LIST radio-button + textarea
        case 'R': return 'ranking';     //  RANKING STYLE
        case 'M': return 'multiple-opt';    //  Multiple choice checkbox
        case 'I': return 'language';        //  Language Question
        case 'P': return 'multiple-opt-comments'; //    Multiple choice with comments checkbox + text
        case 'Q': return 'multiple-short-txt';  //  TEXT
        case 'K': return 'numeric-multi';   //  MULTIPLE NUMERICAL QUESTION
        case 'N': return 'numeric';     //  NUMERICAL QUESTION TYPE
        case 'S': return 'text-short';      //  SHORT FREE TEXT
        case 'T': return 'text-long';       //  LONG FREE TEXT
        case 'U': return 'text-huge';       //  HUGE FREE TEXT
        case 'Y': return 'yes-no';      //  YES/NO radio-buttons
        case 'G': return 'gender';      //  GENDER drop-down list
        case 'A': return 'array-5-pt';      //  ARRAY (5 POINT CHOICE) radio-buttons
        case 'B': return 'array-10-pt';     //  ARRAY (10 POINT CHOICE) radio-buttons
        case 'C': return 'array-yes-uncertain-no'; //   ARRAY (YES/UNCERTAIN/NO) radio-buttons
        case 'E': return 'array-increase-same-decrease'; // ARRAY (Increase/Same/Decrease) radio-buttons
        case 'F': return 'array-flexible-row';  //  ARRAY (Flexible) - Row Format
        case 'H': return 'array-flexible-column'; //    ARRAY (Flexible) - Column Format
            //      case '^': return 'slider';          //  SLIDER CONTROL
        case ':': return 'array-multi-flexi';   //  ARRAY (Multi Flexi) 1 to 10
        case ";": return 'array-multi-flexi-text';
        case "1": return 'array-flexible-duel-scale'; //    Array dual scale
        case "*": return 'equation';    // Equation
        default:  return 'generic_question';    //  Should have a default fallback
    };
};

if(!defined('COLSTYLE'))
{
    /**
    * The following prepares and defines the 'COLSTYLE' constant which
    * dictates how columns are to be marked up for list type questions.
    *
    * $column_style is initialised at the end of config-defaults.php or from within config.php
    */
    if( !isset($column_style)   ||
    $column_style  != 'css' ||
    $column_style  != 'ul'  ||
    $column_style  != 'table' ||
    $column_style  != null )
    {
        $column_style = 'ul';
    };
    define('COLSTYLE' ,strtolower($column_style), true);
};


function setup_columns($columns, $answer_count)
{
    /**
    * setup_columns() defines all the html tags to be wrapped around
    * various list type answers.
    *
    * @param integer $columns - the number of columns, usually supplied by $dcols
    * @param integer $answer_count - the number of answers to a question, usually supplied by $anscount
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
    *    $wrapper['item-end']      = closing wrapper tag for individual
    *                                option
    *    $wrapper['maxrows']       = maximum number of rows in each
    *                                column
    *    $wrapper['cols']          = Number of columns to be inserted
    *                                (and checked against)
    *
    * It also expect the constant COLSTYLE which sets how columns should
    * be rendered.
    *
    * COLSTYLE is defined 30 lines above.
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


    $colstyle = COLSTYLE;

    /*
    if(defined('PRINT_TEMPLATE')) // This forces tables based columns for printablesurvey
    {
    $colstyle = 'table';
    };
    */
    if($columns < 2)
    {
        $colstyle = null;
        $columns = 1;
    }

    if(($columns > $answer_count) && $answer_count>0)
    {
        $columns = $answer_count;
    };

    if ($answer_count>0 && $columns>0)
    {
        $columns = ceil($answer_count/ceil($answer_count/$columns)); // # of columns is # of answers divided by # of rows (all rounded up)
    }

    $class_first = '';
    if($columns > 1 && $colstyle != null)
    {
        if($colstyle == 'ul')
        {
            $ul = '-ul';
        }
        else
        {
            $ul = '';
        }
        $class_first = ' class="cols-'.$columns . $ul.' first"';
        $class = ' class="cols-'.$columns . $ul.'"';
        $class_last_ul = ' class="cols-'.$columns . $ul.' last"';
        $class_last_table = ' class="cols-'.$columns.' last"';
    }
    else
    {
        $class = '';
        $class_last_ul = '';
        $class_last_table = '';
    };

    $wrapper = array(
    'whole-start'  => "\n<ul$class_first>\n"
    ,'whole-end'    => "</ul>\n"
    ,'col-devide'   => ''
    ,'col-devide-last' => ''
    ,'item-start'   => "\t<li>\n"
    ,'item-start-other' => "\t<li class=\"other\">\n"
    ,'item-end' => "\t</li>\n"
    ,'maxrows'  => ceil($answer_count/$columns) //Always rounds up to nearest whole number
    ,'cols'     => $columns
    );

    switch($colstyle)
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
            $wrapper['item-start']  = "<li>\n";
            $wrapper['item-end']    = "</li>\n";
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
* longest_string() returns the length of the longest string past to it.
* @peram string $new_string
* @peram integer $longest_length length of the (previously) longest string passed to it.
* @return integer representing the length of the longest string passed (updated if $new_string was longer than $longest_length)
*
* usage should look like this: $longest_length = longest_string( $new_string , $longest_length );
*
*/
function longest_string( $new_string , $longest_length )
{
    if($longest_length < strlen(trim(strip_tags($new_string))))
    {
        $longest_length = strlen(trim(strip_tags($new_string)));
    };
    return $longest_length;
};



/**
* getNotificationlist() returns different options for notifications
*
* @param string $notificationcode - the currently selected one
*
* @return This string is returned containing <option></option> formatted list of notification methods for current survey
*/
function getNotificationlist($notificationcode)
{
    $CI =& get_instance();
    $clang = $CI->limesurvey_lang;
    $ntypes = array(
    "0"=>$clang->gT("No email notification"),
    "1"=>$clang->gT("Basic email notification"),
    "2"=>$clang->gT("Detailed email notification with result codes")
    );
    if (!isset($ntypeselector)) {$ntypeselector="";}
    foreach($ntypes as $ntcode=>$ntdescription)
    {
        $ntypeselector .= "<option value='$ntcode'";
        if ($notificationcode == $ntcode) {$ntypeselector .= " selected='selected'";}
        $ntypeselector .= ">$ntdescription</option>\n";
    }
    return $ntypeselector;
}


/**
* getgrouplist() queries the database for a list of all groups matching the current survey sid
*
*
* @param string $gid - the currently selected gid/group
*
* @return This string is returned containing <option></option> formatted list of groups to current survey
*/
function getgrouplist($gid,$surveyid)
{
    $clang = Yii::app()->lang;
    $groupselecter="";
    $gid=sanitize_int($gid);
    $surveyid=sanitize_int($surveyid);
    if (!$surveyid) {$surveyid=returnglobal('sid');}
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);

    $gidquery = "SELECT gid, group_name FROM {{groups}} WHERE sid='{$surveyid}' AND  language='{$s_lang}' ORDER BY group_order";
    $gidresult = Yii::app()->db->createCommand($gidquery)->query(); //Checked
    foreach ($gidresult->readAll() as $gv)
    {
        $groupselecter .= "<option";
        if ($gv['gid'] == $gid) {$groupselecter .= " selected='selected'"; $gvexist = 1;}
        $groupselecter .= " value='".Yii::app()->getConfig('scriptname')."?sid=$surveyid&amp;gid=".$gv['gid']."'>".htmlspecialchars($gv['group_name'])."</option>\n";
    }
    if ($groupselecter)
    {
        if (!isset($gvexist)) {$groupselecter = "<option selected='selected'>".$clang->gT("Please choose...")."</option>\n".$groupselecter;}
        else {$groupselecter .= "<option value='".Yii::app()->getConfig('scriptname')."?sid=$surveyid&amp;gid='>".$clang->gT("None")."</option>\n";}
    }
    return $groupselecter;
}


function getgrouplist2($gid,$surveyid)
{
    $CI =& get_instance();
    //$clang = $CI->limesurvey_lang;
    $groupselecter = "";
    if (!$surveyid) {$surveyid=returnglobal('sid');}
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->model('groups_model');
    //$gidquery = "SELECT gid, group_name FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='{$s_lang}' ORDER BY group_order";
    $gidresult = $CI->groups_model->getGroupAndID($surveyid,$s_lang) or safe_die("Plain old did not work!");   //Checked

    foreach ($gidresult->result_array() as $gv)
    {
        $groupselecter .= "<option";
        if ($gv['gid'] == $gid) {$groupselecter .= " selected='selected'"; $gvexist = 1;}
        $groupselecter .= " value='".$gv['gid']."'>".htmlspecialchars($gv['group_name'])."</option>\n";
    }
    if ($groupselecter)
    {
        if (!$gvexist) {$groupselecter = "<option selected='selected'>".$clang->gT("Please choose...")."</option>\n".$groupselecter;}
        else {$groupselecter .= "<option value=''>".$clang->gT("None")."</option>\n";}
    }
    return $groupselecter;
}


function getgrouplist3($gid,$surveyid)
{
    //$clang = $CI->limesurvey_lang;
    $gid=sanitize_int($gid);
    $surveyid=sanitize_int($surveyid);

    if (!$surveyid) {$surveyid=returnglobal('sid');}
    $groupselecter = "";
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);


    //$gidquery = "SELECT gid, group_name FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='{$s_lang}' ORDER BY group_order";

	$gidresult = Groups::model()->findAllByAttributes(array('sid' => $surveyid, 'language' => $s_lang));

    foreach ($gidresult as $gv)
    {
    	$gv = $gv->attributes;
        $groupselecter .= "<option";
        if ($gv['gid'] == $gid) {$groupselecter .= " selected='selected'"; }
        $groupselecter .= " value='".$gv['gid']."'>".htmlspecialchars($gv['group_name'])."</option>\n";
    }


    return $groupselecter;
}

/**
* put your comment there...
*
* @param mixed $gid
* @param mixed $language
*/
function getgrouplistlang($gid, $language,$surveyid)
{

    $clang = Yii::app()->lang;

    $groupselecter="";
    if (!$surveyid) {$surveyid=returnglobal('sid');}

    //$gidquery = "SELECT gid, group_name FROM ".$CI->db->prefix('groups')." WHERE sid=$surveyid AND language='".$language."' ORDER BY group_order";
    $gidresult = Groups::model()->findAllByAttributes(array('sid' => $surveyid, 'language' => $language));   //Checked)
    foreach ($gidresult as $gv)
    {
    	$gv = $gv->attributes;
        $groupselecter .= "<option";
        if ($gv['gid'] == $gid) {$groupselecter .= " selected='selected'"; $gvexist = 1;}
        $link = Yii::app()->createUrl("admin/survey/view/".$surveyid."/".$gv['gid']);
        $groupselecter .= " value='{$link}'>";
        if (strip_tags($gv['group_name']))
        {
            $groupselecter .= htmlspecialchars(strip_tags($gv['group_name']));
        } else {
            $groupselecter .= htmlspecialchars($gv['group_name']);
        }
        $groupselecter .= "</option>\n";
    }
    if ($groupselecter)
    {
        $link = Yii::app()->createUrl("admin/survey/view/".$surveyid);
        if (!isset($gvexist)) {$groupselecter = "<option selected='selected'>".$clang->gT("Please choose...")."</option>\n".$groupselecter;}
        else {$groupselecter .= "<option value='{$link}'>".$clang->gT("None")."</option>\n";}
    }
    return $groupselecter;
}


function getuserlist($outputformat='fullinfoarray')
{
	$clang = Yii::app()->lang;

    if (!empty(Yii::app()->session['loginID']))
    {
        $myuid=sanitize_int(Yii::app()->session['loginID']);
    }
    $usercontrolSameGroupPolicy = Yii::app()->getConfig('usercontrolSameGroupPolicy');
    if (Yii::app()->session['USER_RIGHT_SUPERADMIN'] != 1 && isset($usercontrolSameGroupPolicy) &&
    $usercontrolSameGroupPolicy == true)
    {
        if (isset($myuid))
        {
            // List users from same group as me + all my childs
            // a subselect is used here because MSSQL does not like to group by text
            // also Postgres does like this one better
            $uquery = " SELECT * from {{users}} where uid in (
            SELECT uid from {{user_in_groups}} where ugid in (
            SELECT ugid from {{user_in_groups}} where uid=$myuid
            )
            )
            UNION
            SELECT * from {{users}} where users.parent_id=$myuid";
        }
        else
        {
            return array(); // Or die maybe
        }

    }
    else
    {
        $uquery = "SELECT * FROM {{users}} ORDER BY uid";
    }

    $uresult = Yii::app()->db->createCommand($uquery)->query(); //Checked

    if ($uresult->getRowCount()==0)
    //user is not in a group and usercontrolSameGroupPolicy is activated - at least show his own userinfo
    {
        $uquery = "SELECT u.* FROM {{users}} AS u WHERE u.uid=".$myuid;
        $uresult = Yii::app()->db->createCommand($uquery)->query();//Checked
    }

    $userlist = array();
    $userlist[0] = "Reserved for logged in user";
    //while ($srow = $uresult->result_array())
    foreach ($uresult->readAll() as $srow)
    {
        if ($outputformat != 'onlyuidarray')
        {
            if ($srow['uid'] != Yii::app()->session['loginID'])
            {
                $userlist[] = array("user"=>$srow['users_name'], "uid"=>$srow['uid'], "email"=>$srow['email'], "password"=>$srow['password'], "full_name"=>$srow['full_name'], "parent_id"=>$srow['parent_id'], "create_survey"=>$srow['create_survey'], "participant_panel"=>$srow['participant_panel'], "configurator"=>$srow['configurator'], "create_user"=>$srow['create_user'], "delete_user"=>$srow['delete_user'], "superadmin"=>$srow['superadmin'], "manage_template"=>$srow['manage_template'], "manage_label"=>$srow['manage_label']);           //added by Dennis modified by Moses
            }
            else
            {
                $userlist[0] = array("user"=>$srow['users_name'], "uid"=>$srow['uid'], "email"=>$srow['email'], "password"=>$srow['password'], "full_name"=>$srow['full_name'], "parent_id"=>$srow['parent_id'], "create_survey"=>$srow['create_survey'],"participant_panel"=>$srow['participant_panel'], "configurator"=>$srow['configurator'], "create_user"=>$srow['create_user'], "delete_user"=>$srow['delete_user'], "superadmin"=>$srow['superadmin'], "manage_template"=>$srow['manage_template'], "manage_label"=>$srow['manage_label']);
            }
        }
        else
        {
            if ($srow['uid'] != Yii::app()->session['loginID'])
            {
                $userlist[] = $srow['uid'];
            }
            else
            {
                $userlist[0] = $srow['uid'];
            }
        }

    }
    return $userlist;
}


/**
* Gets all survey infos in one big array including the language specific settings
*
* @param string $surveyid  The survey ID
* @param string $languagecode The language code - if not given the base language of the particular survey is used
* @return array Returns array with survey info or false, if survey does not exist
*/
function getSurveyInfo($surveyid, $languagecode='')
{
    global $siteadminname, $siteadminemail, $languagechanger;
    $surveyid=sanitize_int($surveyid);
    $languagecode=sanitize_languagecode($languagecode);
    $thissurvey=false;

    // if no language code is set then get the base language one
    if (!isset($languagecode) || $languagecode=='')
    {
        $languagecode=GetBaseLanguageFromSurveyID($surveyid);;
    }

    //$query="SELECT * FROM ".db_table_name('surveys').",".db_table_name('surveys_languagesettings')." WHERE sid=$surveyid and surveyls_survey_id=$surveyid and surveyls_language='$languagecode'";

	$result = Surveys_languagesettings::model()->with('survey')->findAllByAttributes(array('surveyls_survey_id' => $surveyid, 'surveyls_language' => $languagecode));
    foreach ($result as $row)
    {
        $thissurvey=array();
    	foreach ($row as $k => $v)
    		$thissurvey[$k] = $v;
    	foreach ($row->survey as $k => $v)
    		$thissurvey[$k] = $v;


        // now create some stupid array translations - needed for backward compatibility
        // Newly added surveysettings don't have to be added specifically - these will be available by field name automatically
        $thissurvey['name']=$thissurvey['surveyls_title'];
        $thissurvey['description']=$thissurvey['surveyls_description'];
        $thissurvey['welcome']=$thissurvey['surveyls_welcometext'];
        $thissurvey['templatedir']=$thissurvey['template'];
        $thissurvey['adminname']=$thissurvey['admin'];
        $thissurvey['tablename']='{{survey_'.$thissurvey['sid'] . '}}';
        $thissurvey['urldescrip']=$thissurvey['surveyls_urldescription'];
        $thissurvey['url']=$thissurvey['surveyls_url'];
        $thissurvey['expiry']=$thissurvey['expires'];
        $thissurvey['email_invite_subj']=$thissurvey['surveyls_email_invite_subj'];
        $thissurvey['email_invite']=$thissurvey['surveyls_email_invite'];
        $thissurvey['email_remind_subj']=$thissurvey['surveyls_email_remind_subj'];
        $thissurvey['email_remind']=$thissurvey['surveyls_email_remind'];
        $thissurvey['email_confirm_subj']=$thissurvey['surveyls_email_confirm_subj'];
        $thissurvey['email_confirm']=$thissurvey['surveyls_email_confirm'];
        $thissurvey['email_register_subj']=$thissurvey['surveyls_email_register_subj'];
        $thissurvey['email_register']=$thissurvey['surveyls_email_register'];
        if (!isset($thissurvey['adminname'])) {$thissurvey['adminname']=$siteadminname;}
        if (!isset($thissurvey['adminemail'])) {$thissurvey['adminemail']=$siteadminemail;}
        if (!isset($thissurvey['urldescrip']) ||
        $thissurvey['urldescrip'] == '' ) {$thissurvey['urldescrip']=$thissurvey['surveyls_url'];}
    }

    //not sure this should be here... ToDo: Find a better place
    if (function_exists('makelanguagechanger')) $languagechanger = makelanguagechanger();
    return $thissurvey;
}

/**
* Returns the default email template texts as array
*
* @param mixed $oLanguage Required language translationb object
* @param string $mode Escape mode for the translation function
* @return array
*/
function aTemplateDefaultTexts($oLanguage, $mode='html'){
    return array(
    'admin_detailed_notification_subject'=>$oLanguage->gT("Response submission for survey {SURVEYNAME} with results",$mode),
    'admin_detailed_notification'=>$oLanguage->gT("Hello,\n\nA new response was submitted for your survey '{SURVEYNAME}'.\n\nClick the following link to reload the survey:\n{RELOADURL}\n\nClick the following link to see the individual response:\n{VIEWRESPONSEURL}\n\nClick the following link to edit the individual response:\n{EDITRESPONSEURL}\n\nView statistics by clicking here:\n{STATISTICSURL}\n\n\nThe following answers were given by the participant:\n{ANSWERTABLE}",$mode),
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
    'admin_notification_subject'=>$oLanguage->gT("Response submission for survey {SURVEYNAME}",$mode),
    'admin_notification'=>$oLanguage->gT("Hello,\n\nA new response was submitted for your survey '{SURVEYNAME}'.\n\nClick the following link to reload the survey:\n{RELOADURL}\n\nClick the following link to see the individual response:\n{VIEWRESPONSEURL}\n\nClick the following link to edit the individual response:\n{EDITRESPONSEURL}\n\nView statistics by clicking here:\n{STATISTICSURL}",$mode),
    'confirmation_subject'=>$oLanguage->gT("Confirmation of your participation in our survey"),
    'confirmation'=>$oLanguage->gT("Dear {FIRSTNAME},\n\nthis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}",$mode),
    'invitation_subject'=>$oLanguage->gT("Invitation to participate in a survey",$mode),
    'invitation'=>$oLanguage->gT("Dear {FIRSTNAME},\n\nyou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",$mode)."\n\n".$oLanguage->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}",$mode)."\n\n".$oLanguage->gT("If you have blacklisted before but want to participate in this survey and want to receive invitations please click the following link:\n{OPTINURL}",$mode),
    'reminder_subject'=>$oLanguage->gT("Reminder to participate in a survey",$mode),
    'reminder'=>$oLanguage->gT("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}",$mode)."\n\n".$oLanguage->gT("If you do not want to participate in this survey and don't want to receive any more invitations please click the following link:\n{OPTOUTURL}",$mode),
    'registration_subject'=>$oLanguage->gT("Survey registration confirmation",$mode),
    'registration'=>$oLanguage->gT("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.",$mode)
    );
}

/**
function getlabelsets($languages=null)
// Returns a list with label sets
// if the $languages paramter is provided then only labelset containing all of the languages in the paramter are provided
{
global $dbprefix, $connect, $surveyid;
if ($languages){
$languages=sanitize_languagecodeS($languages);
$languagesarray=explode(' ',trim($languages));
}
$query = "SELECT ".db_table_name('labelsets').".lid as lid, label_name FROM ".db_table_name('labelsets');
if ($languages){
$query .=" where ";
foreach  ($languagesarray as $item)
{
$query .=" ((languages like '% $item %') or (languages='$item') or (languages like '% $item') or (languages like '$item %')) and ";
}
$query .=" 1=1 ";
}
$query .=" order by label_name";
$result = db_execute_assoc($query) or safe_die ("Couldn't get list of label sets<br />$query<br />".$connect->ErrorMsg()); //Checked
$labelsets=array();
while ($row=$result->FetchRow())
{
$labelsets[] = array($row['lid'], $row['label_name']);
}
return $labelsets;
}
*/
/**
* Compares two elements from an array (passed by the usort function)
* and returns -1, 0 or 1 depending on the result of the comparison of
* the sort order of the group_order and question_order field
*
* @param mixed $a
* @param mixed $b
* @return int
*/
function GroupOrderThenQuestionOrder($a, $b)
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


function StandardSort($a, $b)
{
    return strnatcasecmp($a, $b);
}


function fixsortorderAnswers($qid,$surveyid=null) //Function rewrites the sortorder for a group of answers
{
    $CI =& get_instance();
    $qid=sanitize_int($qid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);

    $CI->load->model('answers_model');
    $CI->answers_model->updateSortOrder($qid,$baselang);
    //$cdresult = db_execute_num("SELECT qid, code, sortorder FROM ".db_table_name('answers')." WHERE qid={$qid} and language='{$baselang}' ORDER BY sortorder"); //Checked
    //$position=0;
    //while ($cdrow=$cdresult->FetchRow())
    //{
    //$cd2query="UPDATE ".db_table_name('answers')." SET sortorder={$position} WHERE qid={$cdrow[0]} AND code='{$cdrow[1]}' AND sortorder={$cdrow[2]} ";
    //$cd2result=$connect->Execute($cd2query) or safe_die ("Couldn't update sortorder<br />$cd2query<br />".$connect->ErrorMsg()); //Checked
    //$position++;
    //}
}

/**
* This function rewrites the sortorder for questions inside the named group
*
* @param integer $groupid the group id
* @param integer $surveyid the survey id
*/
function fixsortorderQuestions($groupid, $surveyid) //Function rewrites the sortorder for questions
{
    $gid = sanitize_int($groupid);
    $surveyid = sanitize_int($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);

    $questions = Questions::model()->findAllByAttributes(array('gid' => $gid, 'sid' => $surveyid, 'language' => $baselang));
	$p = 0;
	foreach ($questions as $question)
	{
		$question->question_order = $p;
		$question->save();
		$p++;
	}

    //$cdresult = db_execute_assoc("SELECT qid FROM ".db_table_name('questions')." WHERE gid='{$gid}' and language='{$baselang}' ORDER BY question_order, title ASC");      //Checked
    //$position=0;
    //while ($cdrow=$cdresult->FetchRow())
    //{
    //$cd2query="UPDATE ".db_table_name('questions')." SET question_order='{$position}' WHERE qid='{$cdrow['qid']}' ";
    //$cd2result = $connect->Execute($cd2query) or safe_die ("Couldn't update question_order<br />$cd2query<br />".$connect->ErrorMsg());    //Checked
    //$position++;
    //}
}


function shiftorderQuestions($sid,$gid,$shiftvalue) //Function shifts the sortorder for questions
{
    $CI =& get_instance();
    $sid=sanitize_int($sid);
    $gid=sanitize_int($gid);
    $shiftvalue=sanitize_int($shiftvalue);

    $baselang = GetBaseLanguageFromSurveyID($sid);

    $CI->load->model('questions_model');
    $CI->questions_model->updateQuestionOrder($gid,$baselang,$shiftvalue);

    //$cdresult = db_execute_assoc("SELECT qid FROM ".db_table_name('questions')." WHERE gid='{$gid}' and language='{$baselang}' ORDER BY question_order, title ASC"); //Checked
    //$position=$shiftvalue;
    //while ($cdrow=$cdresult->FetchRow())
    //{
    //$cd2query="UPDATE ".db_table_name('questions')." SET question_order='{$position}' WHERE qid='{$cdrow['qid']}' ";
    //$cd2result = $connect->Execute($cd2query) or safe_die ("Couldn't update question_order<br />$cd2query<br />".$connect->ErrorMsg()); //Checked
    //$position++;
    //}
}

function fixSortOrderGroups($surveyid) //Function rewrites the sortorder for groups
{
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    Groups::model()->updateGroupOrder($surveyid,$baselang);
    //$cdresult = db_execute_assoc("SELECT gid FROM ".db_table_name('groups')." WHERE sid='{$surveyid}' AND language='{$baselang}' ORDER BY group_order, group_name");
    //$position=0;
    //while ($cdrow=$cdresult->FetchRow())
    //{
    //$cd2query="UPDATE ".db_table_name('groups')." SET group_order='{$position}' WHERE gid='{$cdrow['gid']}' ";
    //$cd2result = $connect->Execute($cd2query) or safe_die ("Couldn't update group_order<br />$cd2query<br />".$connect->ErrorMsg());  //Checked
    //$position++;
    //}
}

function fixmovedquestionConditions($qid,$oldgid,$newgid) //Function rewrites the cfieldname for a question after group change
{
    $CI = &get_instance();
    $surveyid = Yii::app()->getConfig('sid');
    $qid=sanitize_int($qid);
    $oldgid=sanitize_int($oldgid);
    $newgid=sanitize_int($newgid);
    $CI->load->model('conditions_model');
    $CI->conditions_model->updateCFieldName($surveyid,$qid,$oldgid,$newgid);
    // TMSW Conditions->Relevance:  Call LEM->ConvertConditionsToRelevance() when done
}


/**
* This function returns POST/REQUEST vars, for some vars like SID and others they are also sanitized
* CI don't support GET parameters'
*
* @param mixed $stringname
* @param mixed $urlParam
*/
function returnglobal($stringname, $urlParam = null)
{
    if(!isset($urlParam))
    {
        if (!empty($_POST[$stringname]))
			$urlParam = $_POST[$stringname];
            //if ($this->input->cookie('stringname')) $urlParam = $this->input->cookie('stringname');
        elseif (!empty($_GET[$stringname] ))
        {
            $urlParam = $_GET[$stringname];
        }
        elseif (!empty($_COOKIE[$stringname]))
        {
            $urlParam = $_COOKIE[$stringname];
        }
    }

    if (isset($urlParam))
    {
        if ($stringname == 'sid' || $stringname == "gid" || $stringname == "oldqid" ||
        $stringname == "qid" || $stringname == "tid" ||
        $stringname == "lid" || $stringname == "ugid"||
        $stringname == "thisstep" || $stringname == "scenario" ||
        $stringname == "cqid" || $stringname == "cid" ||
        $stringname == "qaid" || $stringname == "scid" ||
        $stringname == "loadsecurity")
        {
            return sanitize_int($urlParam);
        }
        elseif ($stringname =="lang" || $stringname =="adminlang")
        {
            return sanitize_languagecode($urlParam);
        }
        elseif ($stringname =="htmleditormode" ||
        $stringname =="subaction")
        {
            return sanitize_paranoid_string($urlParam);
        }
        elseif ( $stringname =="cquestions")
        {
            return sanitize_cquestions($urlParam);
        }
        return $urlParam;
    }
    else
    {
        return NULL;
    }
}


function sendcacheheaders()
{
    global $embedded;
    if ( $embedded ) return;
    if (!headers_sent())
    {
        header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');  // this line lets IE7 run LimeSurvey in an iframe
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // always modified
        header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: text/html; charset=utf-8');
    }
}

function getsidgidqidaidtype($fieldcode)
{
    // use simple parsing to get {sid}, {gid}
    // and what may be {qid} or {qid}{aid} combination
    $CI = &get_instance();
    list($fsid, $fgid, $fqid) = explode('X', $fieldcode);
    $fsid=sanitize_int($fsid);
    $fgid=sanitize_int($fgid);
    if (!$fqid) {$fqid=0;}
    $fqid=sanitize_int($fqid);
    // try a true parsing of fieldcode (can separate qid from aid)
    // but fails for type M and type P multiple choice
    // questions because the SESSION fieldcode is combined
    // and we want here to pass only the sidXgidXqid for type M and P
    $fields=arraySearchByKey($fieldcode, createFieldMap($fsid,'full'), "fieldname", 1);

    if (count($fields) != 0)
    {
        $aRef['sid']=$fields['sid'];
        $aRef['gid']=$fields['gid'];
        $aRef['qid']=$fields['qid'];
        $aRef['aid']=$fields['aid'];
        $aRef['type']=$fields['type'];
    }
    else
    {
        // either the fielcode doesn't match a question
        // or it is a type M or P question
        $aRef['sid']=$fsid;
        $aRef['gid']=$fgid;
        $aRef['qid']=sanitize_int($fqid);

        $s_lang = GetBaseLanguageFromSurveyID($fsid);
        $fieldtoselect = array('type');
        $condition = "qid = ".$fqid." AND language='".$s_lang."'";
        $CI->load->model('questions_model');
        $result = $CI->questions_model->getSomeRecords($fieldtoselect,$condition);
        //$result = db_execute_assoc($query) or safe_die ("Couldn't get question type - getsidgidqidaidtype() in common.php<br />".$connect->ErrorMsg()); //Checked
        if ( $result->num_rows() == 0 )
        { // question doesn't exist
            return Array();
        }
        else
        {   // certainly is type M or P
            foreach ($result->result_array() as $row)
            {
                $aRef['type']=$row['type'];
            }
        }

    }

    //return array('sid'=>$fsid, "gid"=>$fgid, "qid"=>$fqid);
    return $aRef;
}

/**
* put your comment there...
*
* @param mixed $fieldcode
* @param mixed $value
* @param mixed $format
* @param mixed $dateformatid
* @return string
*/
function getextendedanswer($surveyid, $action, $fieldcode, $value, $format='')
{

    $CI = &get_instance();
    $clang = $CI->limesurvey_lang;

    // use Survey base language if s_lang isn't set in _SESSION (when browsing answers)
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    if  (!isset($action) || (isset($action) && $action!='browse') || $action == NULL )
    {
        if ($CI->session->userdata('s_lang')) $s_lang = $CI->session->userdata('s_lang');  //This one does not work in admin mode when you browse a particular answer
    }

    //Fieldcode used to determine question, $value used to match against answer code
    //Returns NULL if question type does not suit
    if (substr_count($fieldcode, "X") > 1) //Only check if it looks like a real fieldcode
    {
        $fieldmap = createFieldMap($surveyid);
        if (isset($fieldmap[$fieldcode]))
            $fields = $fieldmap[$fieldcode];
        else
            return false;
        //Find out the question type
        $this_type = $fields['type'];
        switch($this_type)
        {
            case 'D': if (trim($value)!='')
                {
                    $qidattributes = getQuestionAttributeValues($fields['qid']);
                    $dateformatdetails = aGetDateFormatDataForQid($qidattributes, $surveyid);
                    $value=convertDateTimeFormat($value,"Y-m-d H:i:s",$dateformatdetails['phpdate']);
                }
                break;
            case "L":
            case "!":
            case "O":
            case "^":
            case "I":
            case "R":
                $CI->load->model('answers_model');

                //$query = "SELECT code, answer FROM ".db_table_name('answers')." WHERE qid={$fields['qid']} AND code='".$connect->escape($value)."' AND scale_id=0 AND language='".$s_lang."'";
                $result = $CI->answers_model->getAnswerCode($fields['qid'],$value,$s_lang) or safe_die ("Couldn't get answer type L - getextendedanswer() in common_helper.php<br />$query<br />"); //Checked

                foreach($result->result_array() as $row)
                {
                    $this_answer=$row['answer'];
                } // while
                if ($value == "-oth-")
                {
                    $this_answer=$clang->gT("Other");
                }
                break;
            case "M":
            case "J":
            case "P":
            switch($value)
            {
                case "Y": $this_answer=$clang->gT("Yes"); break;
            }
            break;
            case "Y":
            switch($value)
            {
                case "Y": $this_answer=$clang->gT("Yes"); break;
                case "N": $this_answer=$clang->gT("No"); break;
                default: $this_answer=$clang->gT("No answer");
            }
            break;
            case "G":
            switch($value)
            {
                case "M": $this_answer=$clang->gT("Male"); break;
                case "F": $this_answer=$clang->gT("Female"); break;
                default: $this_answer=$clang->gT("No answer");
            }
            break;
            case "C":
            switch($value)
            {
                case "Y": $this_answer=$clang->gT("Yes"); break;
                case "N": $this_answer=$clang->gT("No"); break;
                case "U": $this_answer=$clang->gT("Uncertain"); break;
            }
            break;
            case "E":
            switch($value)
            {
                case "I": $this_answer=$clang->gT("Increase"); break;
                case "D": $this_answer=$clang->gT("Decrease"); break;
                case "S": $this_answer=$clang->gT("Same"); break;
            }
            break;
            case "F":
            case "H":
            case "1":
                $fieldtoselect = array('answer');
                $condition = "qid = {$fields['qid']} AND code=".$CI->db->escape($value)." AND language='".$s_lang."'";
                $CI->load->model('answers_model');

                $result = $CI->answers_model->getSomeRecords($fieldtoselect,$condition) or safe_die ("Couldn't get answer type F/H - getextendedanswer() in common_helper.php");   //Checked
                foreach($result->result_array() as $row)
                {
                    $this_answer=$row['answer'];
                } // while
                if ($value == "-oth-")
                {
                    $this_answer=$clang->gT("Other");
                }
                break;
            default:
                ;
        } // switch
    }
    if (isset($this_answer))
    {
        if ($format != 'INSERTANS')
        {
            return $this_answer." [$value]";
        }
        else
        {
            if (strip_tags($this_answer) == "")
            {
                switch ($this_type)
                {// for questions with answers beeing
                    // answer code, it is safe to return the
                    // code instead of the blank stripped answer
                    case "A":
                    case "B":
                    case "C":
                    case "E":
                    case "F":
                    case "H":
                    case "1":
                    case "M":
                    case "P":
                    case "!":
                    case "5":
                    case "L":
                    case "O":
                        return $value;
                        break;
                    default:
                        return strip_tags($this_answer);
                        break;
                }
            }
            else
            {
                return strip_tags($this_answer);
            }
        }
    }
    else
    {
        return $value;
    }
}

/*function validate_email($email)
{
// Create the syntactical validation regular expression
// Validate the syntax

// see http://data.iana.org/TLD/tlds-alpha-by-domain.txt
$maxrootdomainlength = 6;
return ( ! preg_match("/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,".$maxrootdomainlength."}))$/ix", $email)) ? FALSE : TRUE;
}*/

function validate_email($email){


    $no_ws_ctl    = "[\\x01-\\x08\\x0b\\x0c\\x0e-\\x1f\\x7f]";
    $alpha        = "[\\x41-\\x5a\\x61-\\x7a]";
    $digit        = "[\\x30-\\x39]";
    $cr        = "\\x0d";
    $lf        = "\\x0a";
    $crlf        = "(?:$cr$lf)";


    $obs_char    = "[\\x00-\\x09\\x0b\\x0c\\x0e-\\x7f]";
    $obs_text    = "(?:$lf*$cr*(?:$obs_char$lf*$cr*)*)";
    $text        = "(?:[\\x01-\\x09\\x0b\\x0c\\x0e-\\x7f]|$obs_text)";


    $text        = "(?:$lf*$cr*$obs_char$lf*$cr*)";
    $obs_qp        = "(?:\\x5c[\\x00-\\x7f])";
    $quoted_pair    = "(?:\\x5c$text|$obs_qp)";


    $wsp        = "[\\x20\\x09]";
    $obs_fws    = "(?:$wsp+(?:$crlf$wsp+)*)";
    $fws        = "(?:(?:(?:$wsp*$crlf)?$wsp+)|$obs_fws)";
    $ctext        = "(?:$no_ws_ctl|[\\x21-\\x27\\x2A-\\x5b\\x5d-\\x7e])";
    $ccontent    = "(?:$ctext|$quoted_pair)";
    $comment    = "(?:\\x28(?:$fws?$ccontent)*$fws?\\x29)";
    $cfws        = "(?:(?:$fws?$comment)*(?:$fws?$comment|$fws))";


    $outer_ccontent_dull    = "(?:$fws?$ctext|$quoted_pair)";
    $outer_ccontent_nest    = "(?:$fws?$comment)";
    $outer_comment        = "(?:\\x28$outer_ccontent_dull*(?:$outer_ccontent_nest$outer_ccontent_dull*)+$fws?\\x29)";



    $atext        = "(?:$alpha|$digit|[\\x21\\x23-\\x27\\x2a\\x2b\\x2d\\x2f\\x3d\\x3f\\x5e\\x5f\\x60\\x7b-\\x7e])";
    $atext_domain     = "(?:$alpha|$digit|[\\x2b\\x2d\\x5f])";

    $atom        = "(?:$cfws?(?:$atext)+$cfws?)";
    $atom_domain       = "(?:$cfws?(?:$atext_domain)+$cfws?)";


    $qtext        = "(?:$no_ws_ctl|[\\x21\\x23-\\x5b\\x5d-\\x7e])";
    $qcontent    = "(?:$qtext|$quoted_pair)";
    $quoted_string    = "(?:$cfws?\\x22(?:$fws?$qcontent)*$fws?\\x22$cfws?)";


    $quoted_string    = "(?:$cfws?\\x22(?:$fws?$qcontent)+$fws?\\x22$cfws?)";
    $word        = "(?:$atom|$quoted_string)";


    $obs_local_part    = "(?:$word(?:\\x2e$word)*)";


    $obs_domain    = "(?:$atom_domain(?:\\x2e$atom_domain)*)";

    $dot_atom_text     = "(?:$atext+(?:\\x2e$atext+)*)";
    $dot_atom_text_domain    = "(?:$atext_domain+(?:\\x2e$atext_domain+)*)";


    $dot_atom    	   = "(?:$cfws?$dot_atom_text$cfws?)";
    $dot_atom_domain   = "(?:$cfws?$dot_atom_text_domain$cfws?)";


    $dtext        = "(?:$no_ws_ctl|[\\x21-\\x5a\\x5e-\\x7e])";
    $dcontent    = "(?:$dtext|$quoted_pair)";
    $domain_literal    = "(?:$cfws?\\x5b(?:$fws?$dcontent)*$fws?\\x5d$cfws?)";


    $local_part    = "(($dot_atom)|($quoted_string)|($obs_local_part))";
    $domain        = "(($dot_atom_domain)|($domain_literal)|($obs_domain))";
    $addr_spec    = "$local_part\\x40$domain";


    if (strlen($email) > 256) return FALSE;


    $email = strip_comments($outer_comment, $email, "(x)");



    if (!preg_match("!^$addr_spec$!", $email, $m)){

        return FALSE;
    }

    $bits = array(
    'local'            => isset($m[1]) ? $m[1] : '',
    'local-atom'        => isset($m[2]) ? $m[2] : '',
    'local-quoted'        => isset($m[3]) ? $m[3] : '',
    'local-obs'        => isset($m[4]) ? $m[4] : '',
    'domain'        => isset($m[5]) ? $m[5] : '',
    'domain-atom'        => isset($m[6]) ? $m[6] : '',
    'domain-literal'    => isset($m[7]) ? $m[7] : '',
    'domain-obs'        => isset($m[8]) ? $m[8] : '',
    );



    $bits['local']    = strip_comments($comment, $bits['local']);
    $bits['domain']    = strip_comments($comment, $bits['domain']);




    if (strlen($bits['local']) > 64) return FALSE;
    if (strlen($bits['domain']) > 255) return FALSE;



    if (strlen($bits['domain-literal'])){

        $Snum            = "(\d{1,3})";
        $IPv4_address_literal    = "$Snum\.$Snum\.$Snum\.$Snum";

        $IPv6_hex        = "(?:[0-9a-fA-F]{1,4})";

        $IPv6_full        = "IPv6\:$IPv6_hex(:?\:$IPv6_hex){7}";

        $IPv6_comp_part        = "(?:$IPv6_hex(?:\:$IPv6_hex){0,5})?";
        $IPv6_comp        = "IPv6\:($IPv6_comp_part\:\:$IPv6_comp_part)";

        $IPv6v4_full        = "IPv6\:$IPv6_hex(?:\:$IPv6_hex){5}\:$IPv4_address_literal";

        $IPv6v4_comp_part    = "$IPv6_hex(?:\:$IPv6_hex){0,3}";
        $IPv6v4_comp        = "IPv6\:((?:$IPv6v4_comp_part)?\:\:(?:$IPv6v4_comp_part\:)?)$IPv4_address_literal";



        if (preg_match("!^\[$IPv4_address_literal\]$!", $bits['domain'], $m)){

            if (intval($m[1]) > 255) return FALSE;
            if (intval($m[2]) > 255) return FALSE;
            if (intval($m[3]) > 255) return FALSE;
            if (intval($m[4]) > 255) return FALSE;

        }else{


            while (1){

                if (preg_match("!^\[$IPv6_full\]$!", $bits['domain'])){
                    break;
                }

                if (preg_match("!^\[$IPv6_comp\]$!", $bits['domain'], $m)){
                    list($a, $b) = explode('::', $m[1]);
                    $folded = (strlen($a) && strlen($b)) ? "$a:$b" : "$a$b";
                    $groups = explode(':', $folded);
                    if (count($groups) > 6) return FALSE;
                    break;
                }

                if (preg_match("!^\[$IPv6v4_full\]$!", $bits['domain'], $m)){

                    if (intval($m[1]) > 255) return FALSE;
                    if (intval($m[2]) > 255) return FALSE;
                    if (intval($m[3]) > 255) return FALSE;
                    if (intval($m[4]) > 255) return FALSE;
                    break;
                }

                if (preg_match("!^\[$IPv6v4_comp\]$!", $bits['domain'], $m)){
                    list($a, $b) = explode('::', $m[1]);
                    $b = substr($b, 0, -1); # remove the trailing colon before the IPv4 address
                    $folded = (strlen($a) && strlen($b)) ? "$a:$b" : "$a$b";
                    $groups = explode(':', $folded);
                    if (count($groups) > 4) return FALSE;
                    break;
                }

                return FALSE;
            }
        }
    }else{


        $labels = explode('.', $bits['domain']);


        if (count($labels) == 1) return FALSE;


        foreach ($labels as $label){

            if (strlen($label) > 63) return FALSE;
            if (substr($label, 0, 1) == '-') return FALSE;
            if (substr($label, -1) == '-') return FALSE;
        }

        if (preg_match('!^[0-9]+$!', array_pop($labels))) return FALSE;
    }


    return TRUE;
}

##################################################################################

function strip_comments($comment, $email, $replace=''){

    while (1){
        $new = preg_replace("!$comment!", $replace, $email);
        if (strlen($new) == strlen($email)){
            return $email;
        }
        $email = $new;
    }
}


function validate_templatedir($templatename)
{
    $usertemplaterootdir = Yii::app()->getConfig('usertemplaterootdir');
    $standardtemplaterootdir = Yii::app()->getConfig('standardtemplaterootdir');
    $defaulttemplate = Yii::app()->getConfig('defaulttemplate');
    if (is_dir("$usertemplaterootdir/{$templatename}/"))
    {
        return $templatename;
    }
    elseif (is_dir("$standardtemplaterootdir/{$templatename}/"))
    {
        return $templatename;
    }
    elseif (is_dir("$usertemplaterootdir/{$defaulttemplate}/"))
    {
        return $defaulttemplate;
    }
    else
    {
        return 'default';
    }
}


/**
* This function generates an array containing the fieldcode, and matching data in the same order as the activate script
*
* @param string $surveyid The Survey ID
* @param mixed $style 'short' (default) or 'full' - full creates extra information like default values
* @param mixed $force_refresh - Forces to really refresh the array, not just take the session copy
* @param int $questionid Limit to a certain qid only (for question preview) - default is false
* @return array
*/
function createFieldMap($surveyid, $style='short', $force_refresh=false, $questionid=false, $sQuestionLanguage=null) {
    // TMSW Conditions->Relevance:  Refactor this function so that doesn't query conditions table, and so that only 3 db calls total to build array (questions, answers, attributes)
    // TMSW Conditions->Relevance:  'hasconditions' and 'usedinconditions' are no longer needed.

    global $globalfieldmap, $aDuplicateQIDs;

    $clang = Yii::app()->lang;
    $surveyid=sanitize_int($surveyid);
    //checks to see if fieldmap has already been built for this page.
    if (isset($globalfieldmap[$surveyid][$style][$clang->langcode]) && $force_refresh==false) {
        return $globalfieldmap[$surveyid][$style][$clang->langcode];
    }

    $fieldmap["id"]=array("fieldname"=>"id", 'sid'=>$surveyid, 'type'=>"id", "gid"=>"", "qid"=>"", "aid"=>"");
    if ($style == "full")
    {
        $fieldmap["id"]['title']="";
        $fieldmap["id"]['question']=$clang->gT("Response ID");
        $fieldmap["id"]['group_name']="";
    }

    $fieldmap["submitdate"]=array("fieldname"=>"submitdate", 'type'=>"submitdate", 'sid'=>$surveyid, "gid"=>"", "qid"=>"", "aid"=>"");
    if ($style == "full")
    {
        $fieldmap["submitdate"]['title']="";
        $fieldmap["submitdate"]['question']=$clang->gT("Date submitted");
        $fieldmap["submitdate"]['group_name']="";
    }

    $fieldmap["lastpage"]=array("fieldname"=>"lastpage", 'sid'=>$surveyid, 'type'=>"lastpage", "gid"=>"", "qid"=>"", "aid"=>"");
    if ($style == "full")
    {
        $fieldmap["lastpage"]['title']="";
        $fieldmap["lastpage"]['question']=$clang->gT("Last page");
        $fieldmap["lastpage"]['group_name']="";
    }

    $fieldmap["startlanguage"]=array("fieldname"=>"startlanguage", 'sid'=>$surveyid, 'type'=>"startlanguage", "gid"=>"", "qid"=>"", "aid"=>"");
    if ($style == "full")
    {
        $fieldmap["startlanguage"]['title']="";
        $fieldmap["startlanguage"]['question']=$clang->gT("Start language");
        $fieldmap["startlanguage"]['group_name']="";
    }

    // Select which question IDs have default values
	$_aDefaultValues = Defaultvalues::model()->with(array('question' => array('condition' => 'question.sid=' . $surveyid)))->findAll();
	$aDefaultValues = array();
	foreach ($_aDefaultValues as $k => $v)
		$aDefaultValues[] = $v->qid;

    //Check for any additional fields for this survey and create necessary fields (token and datestamp and ipaddr)
    //$pquery = "SELECT anonymized, datestamp, ipaddr, refurl FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
    $fieldtoselect = array('anonymized', 'datestamp', 'ipaddr', 'refurl','language');
    $conditiontoselect = array('sid' => $surveyid); //"WHERE sid=$surveyid";
    $prow = Survey::model()->findByPk($surveyid); //Checked)
    $prow=$prow->attributes;
    if ($prow['anonymized'] == "N")
    {
        $fieldmap["token"]=array("fieldname"=>"token", 'sid'=>$surveyid, 'type'=>"token", "gid"=>"", "qid"=>"", "aid"=>"");
        if ($style == "full")
        {
            $fieldmap["token"]['title']="";
            $fieldmap["token"]['question']=$clang->gT("Token");
            $fieldmap["token"]['group_name']="";
        }
    }
    if ($prow['datestamp'] == "Y")
    {
        $fieldmap["datestamp"]=array("fieldname"=>"datestamp",
        'type'=>"datestamp",
        'sid'=>$surveyid,
        "gid"=>"",
        "qid"=>"",
        "aid"=>"");
        if ($style == "full")
        {
            $fieldmap["datestamp"]['title']="";
            $fieldmap["datestamp"]['question']=$clang->gT("Date last action");
            $fieldmap["datestamp"]['group_name']="";
        }
        $fieldmap["startdate"]=array("fieldname"=>"startdate",
        'type'=>"startdate",
        'sid'=>$surveyid,
        "gid"=>"",
        "qid"=>"",
        "aid"=>"");
        if ($style == "full")
        {
            $fieldmap["startdate"]['title']="";
            $fieldmap["startdate"]['question']=$clang->gT("Date started");
            $fieldmap["startdate"]['group_name']="";
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
            $fieldmap["ipaddr"]['question']=$clang->gT("IP address");
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
            $fieldmap["refurl"]['question']=$clang->gT("Referrer URL");
            $fieldmap["refurl"]['group_name']="";
        }
    }

    //Get list of questions
    if (is_null($sQuestionLanguage))
    {
        $s_lang = $prow['language'];
    }
    else
    {
        $s_lang = $sQuestionLanguage;
    }
    $qtypes=getqtypelist('','array');

	$language = $s_lang;
	$aquery = "SELECT questions.*, groups.group_order, groups.group_name,"
	." (SELECT count(1) FROM {{conditions}} c\n"
	." WHERE questions.qid = c.qid) AS hasconditions,\n"
	." (SELECT count(1) FROM {{conditions}} c\n"
	." WHERE questions.qid = c.cqid) AS usedinconditions\n"
	." FROM {{questions}} as questions, {{groups}} as groups"
	." WHERE questions.gid=groups.gid AND "
	." questions.sid=$surveyid AND "
	." questions.language='{$language}' AND "
	." questions.parent_qid=0 AND "
	." groups.language='{$language}' ";
	if ($questionid!==false)
	{
		$aquery.=" and questions.qid={$questionid} ";
	}
	$aquery.=" ORDER BY group_order, question_order";
    $aresult = Yii::app()->db->createCommand($aquery)->query();
    $questionSeq=-1; // this is incremental question sequence across all groups

    foreach ($aresult->readAll() as $arow) //With each question, create the appropriate field(s))
    {
        ++$questionSeq;
        if ($arow['hasconditions']>0)
        {
            $conditions = "Y";
        }
        else
        {
            $conditions = "N";
        }
        if ($arow['usedinconditions']>0)
        {
            $usedinconditions = "Y";
        }
        else
        {
            $usedinconditions = "N";
        }

        // Field identifier
        // GXQXSXA
        // G=Group  Q=Question S=Subquestion A=Answer Option
        // If S or A don't exist then set it to 0
        // Implicit (subqestion intermal to a question type ) or explicit qubquestions/answer count starts at 1

        // Types "L", "!" , "O", "D", "G", "N", "X", "Y", "5","S","T","U"

        if ($qtypes[$arow['type']]['subquestions']==0  && $arow['type'] != "R" && $arow['type'] != "|")
        {
            $fieldname="{$arow['sid']}X{$arow['gid']}X{$arow['qid']}";
            if (isset($fieldmap[$fieldname])) $aDuplicateQIDs[$arow['qid']]=array('fieldname'=>$fieldname,'question'=>$arow['question'],'gid'=>$arow['gid']);
            $fieldmap[$fieldname]=array("fieldname"=>$fieldname, 'type'=>"{$arow['type']}", 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>"");
            if ($style == "full")
            {
                $fieldmap[$fieldname]['title']=$arow['title'];
                $fieldmap[$fieldname]['question']=$arow['question'];
                $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                $fieldmap[$fieldname]['hasconditions']=$conditions;
                $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                $fieldmap[$fieldname]['questionSeq']=$questionSeq;
                $fieldmap[$fieldname]['groupSeq']=$arow['group_order'];
                $fieldtoselect = array('defaultvalue');

                if ($qtypes[$arow['type']]['hasdefaultvalues'] && in_array($arow['qid'],$aDefaultValues))
                {
                    if ($arow['same_default'])
                    {
                        $conditiontoselect = array(
                        'qid' => $arow['qid'],
                        'scale_id' => 0,
                        'language' => GetBaseLanguageFromSurveyID($surveyid)
                        ); //"WHERE qid={$arow['qid']} AND scale_id=0 AND language='".GetBaseLanguageFromSurveyID($surveyid)."'";
                        $data = Defaultvalues::model()->findByAttributes($conditiontoselect);
                        $data  = $data->attributes;
                        $fieldmap[$fieldname]['defaultvalue']=$data['defaultvalue'];//$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE qid={$arow['qid']} AND scale_id=0 AND language='".GetBaseLanguageFromSurveyID($surveyid)."'");
                    }
                    else
                    {
                        //$conditiontoselect = "WHERE qid={$arow['qid']} AND scale_id=0 AND language='{$clang->langcode}'";
                        $conditiontoselect = array(
                        'qid' => $arow['qid'],
                        'scale_id' => 0,
                        'language' => $clang->langcode
                        );
                        $data = Defaultvalues::model()->findAllByAttributes($conditiontoselect);

                        $row  = $data[0]->attributes;
                        if (count($data) >0)
                            $fieldmap[$fieldname]['defaultvalue']=$row['defaultvalue'];//$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE qid={$arow['qid']} AND scale_id=0 AND language='{$clang->langcode}'");
                        else
                            $fieldmap[$fieldname]['defaultvalue']='';

                    }
                }
            }
            switch($arow['type'])
            {
                case "L":  //RADIO LIST
                case "!":  //DROPDOWN LIST
                    if ($arow['other'] == "Y")
                    {
                        $fieldname="{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other";
                        if (isset($fieldmap[$fieldname])) $aDuplicateQIDs[$arow['qid']]=array('fieldname'=>$fieldname,'question'=>$arow['question'],'gid'=>$arow['gid']);

                        $fieldmap[$fieldname]=array("fieldname"=>$fieldname,
                        'type'=>$arow['type'],
                        'sid'=>$surveyid,
                        "gid"=>$arow['gid'],
                        "qid"=>$arow['qid'],
                        "aid"=>"other");
                        // dgk bug fix line above. aid should be set to "other" for export to append to the field name in the header line.
                        if ($style == "full")
                        {
                            $fieldmap[$fieldname]['title']=$arow['title'];
                            $fieldmap[$fieldname]['question']=$arow['question'];
                            $fieldmap[$fieldname]['subquestion']=$clang->gT("Other");
                            $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                            $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                            $fieldmap[$fieldname]['hasconditions']=$conditions;
                            $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                            $fieldmap[$fieldname]['questionSeq']=$questionSeq;
                            $fieldmap[$fieldname]['groupSeq']=$arow['group_order'];
                            if (in_array($arow['qid'],$aDefaultValues))
                            {
                                if ($arow['same_default'])
                                {
                                    //$conditiontoselect = "WHERE qid={$arow['qid']} AND scale_id=0 AND language='".GetBaseLanguageFromSurveyID($surveyid)."'";
                                    $conditiontoselect = array(
                                    'qid' => $arow['qid'],
                                    'scale_id' => 0,
                                    'language' => GetBaseLanguageFromSurveyID($surveyid)
                                    );
                                    $data = Defaultvalues::model()->findByAttributes($conditiontoselect);
                                    $data  = $data->attributes;
                                    if (!isset($data['defaultvalue'])) $data['defaultvalue']=null;
                                    $fieldmap[$fieldname]['defaultvalue']=$data['defaultvalue'];//$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE qid={$arow['qid']} AND scale_id=0 AND language='".GetBaseLanguageFromSurveyID($surveyid)."'");
                                }
                                else
                                {
                                    //$conditiontoselect = "WHERE qid={$arow['qid']} AND scale_id=0 AND language='{$clang->langcode}'";
                                    $conditiontoselect = array(
                                    'qid' => $arow['qid'],
                                    'scale_id' => 0,
                                    'language' => $clang->langcode
                                    );
                                    $data = Defaultvalues::model()->findByAttributes($conditiontoselect);
                                    $data  = $data->attributes;
                                    if (!isset($data['defaultvalue'])) $data['defaultvalue']=null;
                                    $fieldmap[$fieldname]['defaultvalue']=$data['defaultvalue'];//$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE qid={$arow['qid']} AND scale_id=0 AND language='{$clang->langcode}'");
                                }

                            }
                        }
                    }
                    break;
                case "O": //DROPDOWN LIST WITH COMMENT
                    $fieldname="{$arow['sid']}X{$arow['gid']}X{$arow['qid']}comment";
                    if (isset($fieldmap[$fieldname])) $aDuplicateQIDs[$arow['qid']]=array('fieldname'=>$fieldname,'question'=>$arow['question'],'gid'=>$arow['gid']);

                    $fieldmap[$fieldname]=array("fieldname"=>$fieldname,
                    'type'=>$arow['type'],
                    'sid'=>$surveyid,
                    "gid"=>$arow['gid'],
                    "qid"=>$arow['qid'],
                    "aid"=>"comment");
                    // dgk bug fix line below. aid should be set to "comment" for export to append to the field name in the header line. Also needed set the type element correctly.
                    if ($style == "full")
                    {
                        $fieldmap[$fieldname]['title']=$arow['title'];
                        $fieldmap[$fieldname]['question']=$arow['question'];
                        $fieldmap[$fieldname]['subquestion']=$clang->gT("Comment");
                        $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                        $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                        $fieldmap[$fieldname]['hasconditions']=$conditions;
                        $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                        $fieldmap[$fieldname]['questionSeq']=$questionSeq;
                        $fieldmap[$fieldname]['groupSeq']=$arow['group_order'];
                    }
                    break;
            }
        }
        // For Multi flexi question types
        elseif ($qtypes[$arow['type']]['subquestions']==2 && $qtypes[$arow['type']]['answerscales']==0)
        {
            //MULTI FLEXI
            $abrows = getSubQuestions($surveyid,$arow['qid'],$s_lang);
            //Now first process scale=1
            $answerset=array();
            foreach ($abrows as $key=>$abrow)
            {
                if($abrow['scale_id']==1) {
                    $answerset[]=$abrow;
                    unset($abrows[$key]);
                }
            }
            reset($abrows);
            foreach ($abrows as $abrow)
            {
                foreach($answerset as $answer)
                {
                    $fieldname="{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}_{$answer['title']}";
                    if (isset($fieldmap[$fieldname])) $aDuplicateQIDs[$arow['qid']]=array('fieldname'=>$fieldname,'question'=>$arow['question'],'gid'=>$arow['gid']);
                    $fieldmap[$fieldname]=array("fieldname"=>$fieldname,
                    'type'=>$arow['type'],
                    'sid'=>$surveyid,
                    "gid"=>$arow['gid'],
                    "qid"=>$arow['qid'],
                    "aid"=>$abrow['title']."_".$answer['title'],
                    "sqid"=>$abrow['qid']);
                    if ($abrow['other']=="Y") {$alsoother="Y";}
                    if ($style == "full")
                    {
                        $fieldmap[$fieldname]['title']=$arow['title'];
                        $fieldmap[$fieldname]['question']=$arow['question'];
                        $fieldmap[$fieldname]['subquestion1']=$abrow['question'];
                        $fieldmap[$fieldname]['subquestion2']=$answer['question'];
                        $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                        $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                        $fieldmap[$fieldname]['hasconditions']=$conditions;
                        $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                        $fieldmap[$fieldname]['questionSeq']=$questionSeq;
                        $fieldmap[$fieldname]['groupSeq']=$arow['group_order'];
                    }
                }
            }
            unset($answerset);
        }
        elseif ($arow['type'] == "1")
        {
            $abrows = getSubQuestions($surveyid,$arow['qid'],$s_lang);
            foreach ($abrows as $abrow)
            {
                $fieldname="{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}#0";
                if (isset($fieldmap[$fieldname])) $aDuplicateQIDs[$arow['qid']]=array('fieldname'=>$fieldname,'question'=>$arow['question'],'gid'=>$arow['gid']);
                $fieldmap[$fieldname]=array("fieldname"=>$fieldname, 'type'=>$arow['type'], 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$abrow['title'], "scale_id"=>0);
                if ($style == "full")
                {
                    $fieldmap[$fieldname]['title']=$arow['title'];
                    $fieldmap[$fieldname]['question']=$arow['question'];
                    $fieldmap[$fieldname]['subquestion']=$abrow['question'];
                    $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                    $fieldmap[$fieldname]['scale']=$clang->gT('Scale 1');
                    $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions']=$conditions;
                    $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                    $fieldmap[$fieldname]['questionSeq']=$questionSeq;
                    $fieldmap[$fieldname]['groupSeq']=$arow['group_order'];
                }

                $fieldname="{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}#1";
                if (isset($fieldmap[$fieldname])) $aDuplicateQIDs[$arow['qid']]=array('fieldname'=>$fieldname,'question'=>$arow['question'],'gid'=>$arow['gid']);
                $fieldmap[$fieldname]=array("fieldname"=>$fieldname, 'type'=>$arow['type'], 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$abrow['title'], "scale_id"=>1);
                if ($style == "full")
                {
                    $fieldmap[$fieldname]['title']=$arow['title'];
                    $fieldmap[$fieldname]['question']=$arow['question'];
                    $fieldmap[$fieldname]['subquestion']=$abrow['question'];
                    $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                    $fieldmap[$fieldname]['scale']=$clang->gT('Scale 2');
                    $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions']=$conditions;
                    $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                    $fieldmap[$fieldname]['questionSeq']=$questionSeq;
                    $fieldmap[$fieldname]['groupSeq']=$arow['group_order'];
                }
            }
        }

        elseif ($arow['type'] == "R")
        {
            //MULTI ENTRY
            $data = Answers::model()->findAllByAttributes(array('qid' => $arow['qid'], 'language' => $s_lang));
            $data = count($data);
            $slots=$data;//$connect->GetOne("select count(code) from ".db_table_name('answers')." where qid={$arow['qid']} and language='{$s_lang}'");
            for ($i=1; $i<=$slots; $i++)
            {
                $fieldname="{$arow['sid']}X{$arow['gid']}X{$arow['qid']}$i";
                if (isset($fieldmap[$fieldname])) $aDuplicateQIDs[$arow['qid']]=array('fieldname'=>$fieldname,'question'=>$arow['question'],'gid'=>$arow['gid']);
                $fieldmap[$fieldname]=array("fieldname"=>$fieldname, 'type'=>$arow['type'], 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$i);
                if ($style == "full")
                {
                    $fieldmap[$fieldname]['title']=$arow['title'];
                    $fieldmap[$fieldname]['question']=$arow['question'];
                    $fieldmap[$fieldname]['subquestion']=sprintf($clang->gT('Rank %s'),$i);
                    $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                    $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions']=$conditions;
                    $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                    $fieldmap[$fieldname]['questionSeq']=$questionSeq;
                    $fieldmap[$fieldname]['groupSeq']=$arow['group_order'];
                }
            }
        }
        elseif ($arow['type'] == "|")
        {
            $fieldname="{$arow['sid']}X{$arow['gid']}X{$arow['qid']}";
            $fieldmap[$fieldname]=array("fieldname"=>$fieldname,
            'type'=>$arow['type'],
            'sid'=>$surveyid,
            "gid"=>$arow['gid'],
            "qid"=>$arow['qid'],
            "aid"=>''
            );
            if ($style == "full")
            {
                $fieldmap[$fieldname]['title']=$arow['title'];
                $fieldmap[$fieldname]['question']=$arow['question'];
                $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                $fieldmap[$fieldname]['hasconditions']=$conditions;
                $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                $fieldmap[$fieldname]['questionSeq']=$questionSeq;
                $fieldmap[$fieldname]['groupSeq']=$arow['group_order'];
            }
            $fieldname="{$arow['sid']}X{$arow['gid']}X{$arow['qid']}"."_filecount";
            $fieldmap[$fieldname]=array("fieldname"=>$fieldname,
            'type'=>$arow['type'],
            'sid'=>$surveyid,
            "gid"=>$arow['gid'],
            "qid"=>$arow['qid'],
            "aid"=>"filecount"
            );
            if ($style == "full")
            {
                $fieldmap[$fieldname]['title']=$arow['title'];
                $fieldmap[$fieldname]['question']="filecount - ".$arow['question'];
                $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                $fieldmap[$fieldname]['hasconditions']=$conditions;
                $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                $fieldmap[$fieldname]['questionSeq']=$questionSeq;
                $fieldmap[$fieldname]['groupSeq']=$arow['group_order'];
            }
        }
        else  // Question types with subquestions and one answer per subquestion  (M/A/B/C/E/F/H/P)
        {
            //MULTI ENTRY
            $abrows = getSubQuestions($surveyid,$arow['qid'],$s_lang);
            foreach ($abrows as $abrow)
            {
                $fieldname="{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}";
                if (isset($fieldmap[$fieldname])) $aDuplicateQIDs[$arow['qid']]=array('fieldname'=>$fieldname,'question'=>$arow['question'],'gid'=>$arow['gid']);
                $fieldmap[$fieldname]=array("fieldname"=>$fieldname,
                'type'=>$arow['type'],
                'sid'=>$surveyid,
                'gid'=>$arow['gid'],
                'qid'=>$arow['qid'],
                'aid'=>$abrow['title'],
                'sqid'=>$abrow['qid']);
                if ($style == "full")
                {
                    $fieldmap[$fieldname]['title']=$arow['title'];
                    $fieldmap[$fieldname]['question']=$arow['question'];
                    $fieldmap[$fieldname]['subquestion']=$abrow['question'];
                    $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                    $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions']=$conditions;
                    $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                    $fieldmap[$fieldname]['questionSeq']=$questionSeq;
                    $fieldmap[$fieldname]['groupSeq']=$arow['group_order'];
                    if (in_array($arow['qid'],$aDefaultValues))
                    {

                        if ($arow['same_default'])
                        {
                            $conditiontoselect = "sqid = '{$abrow['qid']}' AND qid={$arow['qid']} AND scale_id=0 AND language='".GetBaseLanguageFromSurveyID($surveyid)."'";

                            $data = Defaultvalues::model()->find($conditiontoselect);
                            $data  = $data->attributes;
                            if(isset($data['defaultvalue']))
                                $fieldmap[$fieldname]['defaultvalue']=$data['defaultvalue'];
                            //$fieldmap[$fieldname]['defaultvalue']=$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE sqid={$abrow['qid']} and qid={$arow['qid']} AND scale_id=0 AND language='".GetBaseLanguageFromSurveyID($surveyid)."'");
                        }
                        else
                        {
                            $conditiontoselect = "sqid = '{$abrow['qid']}' AND qid={$arow['qid']} AND scale_id=0 AND language='{$clang->langcode}'";
                            $data = Defaultvalues::model()->find($conditiontoselect);
                            if(isset($data))
                                $data  = $data->attributes;
                            if(isset($data['defaultvalue']))
                                $fieldmap[$fieldname]['defaultvalue']=$data['defaultvalue'];

                            //$fieldmap[$fieldname]['defaultvalue']=$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE sqid={$abrow['qid']} and qid={$arow['qid']} AND scale_id=0 AND language='{$clang->langcode}'");
                        }
                    }
                }
                if ($arow['type'] == "P")
                {
                    $fieldname="{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}comment";
                    if (isset($fieldmap[$fieldname])) $aDuplicateQIDs[$arow['qid']]=array('fieldname'=>$fieldname,'question'=>$arow['question'],'gid'=>$arow['gid']);
                    $fieldmap[$fieldname]=array("fieldname"=>$fieldname, 'type'=>$arow['type'], 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$abrow['title']."comment");
                    if ($style == "full")
                    {
                        $fieldmap[$fieldname]['title']=$arow['title'];
                        $fieldmap[$fieldname]['question']=$arow['question'];
                        $fieldmap[$fieldname]['subquestion']=$clang->gT('Comment');
                        $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                        $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                        $fieldmap[$fieldname]['hasconditions']=$conditions;
                        $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                        $fieldmap[$fieldname]['questionSeq']=$questionSeq;
                        $fieldmap[$fieldname]['groupSeq']=$arow['group_order'];
                    }
                }
            }
            if ($arow['other']=="Y" && ($arow['type']=="M" || $arow['type']=="P"))
            {
                $fieldname="{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other";
                if (isset($fieldmap[$fieldname])) $aDuplicateQIDs[$arow['qid']]=array('fieldname'=>$fieldname,'question'=>$arow['question'],'gid'=>$arow['gid']);
                $fieldmap[$fieldname]=array("fieldname"=>$fieldname, 'type'=>$arow['type'], 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>"other");
                if ($style == "full")
                {
                    $fieldmap[$fieldname]['title']=$arow['title'];
                    $fieldmap[$fieldname]['question']=$arow['question'];
                    $fieldmap[$fieldname]['subquestion']=$clang->gT('Other');
                    $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                    $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions']=$conditions;
                    $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                    $fieldmap[$fieldname]['questionSeq']=$questionSeq;
                    $fieldmap[$fieldname]['groupSeq']=$arow['group_order'];
                }
                if ($arow['type']=="P")
                {
                    $fieldname="{$arow['sid']}X{$arow['gid']}X{$arow['qid']}othercomment";
                    if (isset($fieldmap[$fieldname])) $aDuplicateQIDs[$arow['qid']]=array('fieldname'=>$fieldname,'question'=>$arow['question'],'gid'=>$arow['gid']);
                    $fieldmap[$fieldname]=array("fieldname"=>$fieldname, 'type'=>$arow['type'], 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>"othercomment");
                    if ($style == "full")
                    {
                        $fieldmap[$fieldname]['title']=$arow['title'];
                        $fieldmap[$fieldname]['question']=$arow['question'];
                        $fieldmap[$fieldname]['subquestion']=$clang->gT('Other comment');
                        $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                        $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                        $fieldmap[$fieldname]['hasconditions']=$conditions;
                        $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                        $fieldmap[$fieldname]['questionSeq']=$questionSeq;
                        $fieldmap[$fieldname]['groupSeq']=$arow['group_order'];
                    }
                }
            }
        }
        $fieldmap[$fieldname]['relevance'] = isset($arow['relevance']) ? $arow['relevance'] : null;
        $fieldmap[$fieldname]['questionSeq']=$questionSeq;
        $fieldmap[$fieldname]['groupSeq']=$arow['group_order'];
    }
    if (isset($fieldmap)) {
        $globalfieldmap[$surveyid][$style][$clang->langcode] = $fieldmap;
        return $fieldmap;
    }
}

/**
* Returns true if the given survey has a File Upload Question Type
* @param $surveyid The survey ID
* @return bool
*/
function bHasFileUploadQuestion($surveyid) {
    $fieldmap = createFieldMap($surveyid);

    foreach ($fieldmap as $field) {
        if (isset($field['type']) &&  $field['type'] === '|') return true;
    }
}



/**
* This function generates an array containing the fieldcode, and matching data in the same order as the activate script
*
* @param string $surveyid The Survey ID
* @param mixed $style 'short' (default) or 'full' - full creates extra information like default values
* @param mixed $force_refresh - Forces to really refresh the array, not just take the session copy
* @param int $questionid Limit to a certain qid only (for question preview) - default is false
* @return array
*/
function createTimingsFieldMap($surveyid, $style='full', $force_refresh=false, $questionid=false, $sQuestionLanguage=null) {

    global $globalfieldmap, $aDuplicateQIDs;
    static $timingsFieldMap;
    
    $clang = Yii::app()->lang;

    $surveyid=sanitize_int($surveyid);
    //checks to see if fieldmap has already been built for this page.
    if (isset($timingsFieldMap[$surveyid][$style][$clang->langcode]) && $force_refresh==false) {
        return $timingsFieldMap[$surveyid][$style][$clang->langcode];
    }

    //do something
    $fields = createFieldMap($surveyid, $style, $force_refresh, $questionid, $sQuestionLanguage);
    $fieldmap['interviewtime']=array('fieldname'=>'interviewtime','type'=>'interview_time','sid'=>$surveyid, 'gid'=>'', 'qid'=>'', 'aid'=>'', 'question'=>$clang->gT('Total time'), 'title'=>'interviewtime');
    foreach ($fields as $field) {
        if (!empty($field['gid'])) {
            // field for time spent on page
            $fieldname="{$field['sid']}X{$field['gid']}time";
            if (!isset($fieldmap[$fieldname]))
            {
                $fieldmap[$fieldname]=array("fieldname"=>$fieldname, 'type'=>"page_time", 'sid'=>$surveyid, "gid"=>$field['gid'], "group_name"=>$field['group_name'], "qid"=>'', 'aid'=>'', 'title'=>'groupTime'.$field['gid'], 'question'=>$clang->gT('Group time').": ".$field['group_name']);
            }

            // field for time spent on answering a question
            $fieldname="{$field['sid']}X{$field['gid']}X{$field['qid']}time";
            if (!isset($fieldmap[$fieldname]))
            {
                $fieldmap[$fieldname]=array("fieldname"=>$fieldname, 'type'=>"answer_time", 'sid'=>$surveyid, "gid"=>$field['gid'], "group_name"=>$field['group_name'], "qid"=>$field['qid'], 'aid'=>'', "title"=>$field['title'].'Time', "question"=>$clang->gT('Question time').": ".$field['title']);
            }
        }
    }

    $timingsFieldMap[$surveyid][$style][$clang->langcode] = $fieldmap;
    return $timingsFieldMap[$surveyid][$style][$clang->langcode];
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
    $output=array();
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



/**
* set the rights of a user and his children
*
* @param int $uid the user id
* @param mixed $rights rights array
*/
function setuserrights($uid, $rights)
{
    $CI =& get_instance();
    $uid=sanitize_int($uid);
    $CI->load->helper("database");
    $updates = "create_survey=".$rights['create_survey']
    . ", create_user=".$rights['create_user']
    . ", participant_panel=".$rights['participant_panel']
    . ", delete_user=".$rights['delete_user']
    . ", superadmin=".$rights['superadmin']
    . ", configurator=".$rights['configurator']
    . ", manage_template=".$rights['manage_template']
    . ", manage_label=".$rights['manage_label'];
    $uquery = "UPDATE ".$CI->db->dbprefix('users')." SET ".$updates." WHERE uid = ".$uid;
    return db_select_limit_assoc($uquery);     //Checked
}

/**
* This function returns a count of the number of saved responses to a survey
*
* @param mixed $surveyid Survey ID
*/
function getSavedCount($surveyid)
{

    $CI = &get_instance();
    $surveyid=(int)$surveyid;

    $CI->load->model('saved_control_model');


    //$query = "SELECT COUNT(*) FROM ".db_table_name('saved_control')." WHERE sid=$surveyid";
    $count=$CI->saved_control_model->getCountOfAll($surveyid);
    return $count;
}

/**
* Returns the base language from a survey id
*
* @param int survey id
*/
function GetBaseLanguageFromSurveyID($surveyid)
{
    //if(empty($surveyid)) var_dump(debug_backtrace());
    static $cache = array();
    $surveyid=(int)($surveyid);
    if (!isset($cache[$surveyid])) {

        $condition = array('sid' => $surveyid);//"sid=$surveyid";

        $surveylanguage = Survey::model()->findByPk($surveyid);//("SELECT language FROM ".db_table_name('surveys')." WHERE sid=$surveyid";)
		if (is_null($surveylanguage))
			die(var_dump(debug_backtrace()));
        $surveylanguage = $surveylanguage->attributes; //Checked)

        if (!isset($surveylanguage['language']) || is_null($surveylanguage))
        {
            $surveylanguage='en';
        }
        else
        {
            $surveylanguage = $surveylanguage['language'];
        }
        $cache[$surveyid] = $surveylanguage;
    } else {
        $surveylanguage = $cache[$surveyid];
    }
    return $surveylanguage;
}


function GetAdditionalLanguagesFromSurveyID($surveyid)
{
    static $cache = array();
    if (!isset($cache[$surveyid])) {
        $result = Survey::model()->findByAttributes(array('sid' => (int) $surveyid));

    	$additional_languages = $result->attributes;
        //$query = "SELECT additional_languages FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
        $additional_languages = $additional_languages['additional_languages'];
        if (trim($additional_languages)=='')
        {
            $additional_languages = array();
        }
        else
        {
            $additional_languages = explode(" ", trim($additional_languages));
        }
        $cache[$surveyid] = $additional_languages;
    } else {
        $additional_languages = $cache[$surveyid];
    }
    return $additional_languages;
}



//For multilanguage surveys
// If null or 0 is given for $surveyid then the default language from config-defaults.php is returned
function SetSurveyLanguage($surveyid, $language)
{
    $CI = &get_instance();
    $surveyid=sanitize_int($surveyid);
    $defaultlang = Yii::app()->getConfig('defaultlang');

    if (isset($surveyid) && $surveyid>0)
    {
        // see if language actually is present in survey
        $fields = array('language', 'additional_languages');
        $condition = array('sid' => $surveyid); //"sid = $surveyid";
        $CI->load->model('surveys_model');

        $result = $CI->surveys_model->getSomeRecords($fields,$condition); //Checked
        foreach ($result->result_array() as $row) {//while ($result && ($row=$result->FetchRow())) {)
            $additional_languages = $row['additional_languages'];
            $default_language = $row['language'];
        }

        if (!isset($language) || ($language=='') || (isset($additional_languages) && strpos($additional_languages, $language) === false)
        or (isset($default_language) && $default_language == $language)
        ) {
            // Language not supported, or default language for survey, fall back to survey's default language
            $_SESSION['s_lang'] = $default_language;
            //echo "Language not supported, resorting to ".$_SESSION['s_lang']."<br />";
        } else {
            $_SESSION['s_lang'] =  $language;
            //echo "Language will be set to ".$_SESSION['s_lang']."<br />";
        }
        $lang = array($_SESSION['s_lang']);
        $CI->load->library('Limesurvey_lang',$lang);
        $clang = $CI->limesurvey_lang;
        //$clang = new limesurvey_lang($_SESSION['s_lang']);
    }
    else {
        $lang = array($defaultlang);
        $CI->load->library('Limesurvey_lang',$lang);
        $clang = $CI->limesurvey_lang;
        //$clang = new limesurvey_lang($defaultlang);
    }

    $thissurvey=getSurveyInfo($surveyid, $_SESSION['s_lang']);
    $CI->load->helper('surveytranslator');
    $_SESSION['dateformats'] = getDateFormatData($thissurvey['surveyls_dateformat']);
    return $clang;
}


function buildLabelSetCheckSumArray()
{
    // BUILD CHECKSUMS FOR ALL EXISTING LABEL SETS

    /**$query = "SELECT lid
    FROM ".db_table_name('labelsets')."
    ORDER BY lid"; */
    $result = Labelsets::getLID();//($query) or safe_die("safe_died collecting labelset ids<br />$query<br />");  //Checked)
    $csarray=array();
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
* Returns a flat array with all saved question attributes for the question only (and the qid we gave it)!
* @author: c_schmitz
* @param $qid The question ID
* @param $type optional The question type - saves a DB query if you provide it
* @return array{attribute=>value , attribute=>value} or false if the question ID does not exist (anymore)
*/
function getQuestionAttributeValues($qid, $type='')
{
    static $cache = array();
    static $availableattributesarr = null;

    if (isset($cache[$qid])) {
        return $cache[$qid];
    }
    $result = Questions::model()->findByAttributes(array('qid' => $qid));  //Checked
    $row=$result->attributes;
    if ($row===false) // Question was deleted while running the survey
    {
        $cache[$qid]=false;
        return false;
    }
    $type=$row['type'];
    $surveyid=$row['sid'];

    $aLanguages=array_merge(array(GetBaseLanguageFromSurveyID($surveyid)),GetAdditionalLanguagesFromSurveyID($surveyid));


    //Now read available attributes, make sure we do this only once per request to save
    //processing cycles and memory
    if (is_null($availableattributesarr)) $availableattributesarr=questionAttributes();
    if (isset($availableattributesarr[$type]))
    {
        $availableattributes=$availableattributesarr[$type];
    }
    else
    {
        $cache[$qid]=array();
        return array();
    }

    foreach($availableattributes as $attribute){
        if ($attribute['i18n']){
            foreach ($aLanguages as $sLanguage)
            {
                $defaultattributes[$attribute['name']][$sLanguage]=$attribute['default'];
            }
        }
        else
        {
            $defaultattributes[$attribute['name']]=$attribute['default'];
        }
    }
    $setattributes=array();
    $qid=sanitize_int($qid);
    $fields = array('attribute', 'value', 'language');
    $condition = "qid = $qid";

    $result = Question_attributes::model()->findAll($condition);  //Checked)
    $setattributes=array();

    foreach ($result as $row)
    {
    	$row = $row->attributes;
        if (!isset($availableattributes[$row['attribute']])) continue; // Sort out attribuets not belonging to this question type
        if (!($availableattributes[$row['attribute']]['i18n']))
        {
            $setattributes[$row['attribute']]=$row['value'];
        }
        elseif(!empty($row['language'])){
            $setattributes[$row['attribute']][$row['language']]=$row['value'];
        }
    }
    $qid_attributes=array_merge($defaultattributes,$setattributes);
    $cache[$qid]=$qid_attributes;
    return $qid_attributes;
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
    $clang = Yii::app()->lang;
    //For each question attribute include a key:
    // name - the display name
    // types - a string with one character representing each question typy to which the attribute applies
    // help - a short explanation

    // If you insert a new attribute please do it in correct alphabetical order!

    $qattributes["alphasort"]=array(
    "types"=>"!LOWZ",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT("Sort the answer options alphabetically"),
    "caption"=>$clang->gT('Sort answers alphabetically'));

    $qattributes["answer_width"]=array(
    "types"=>"ABCEF1:;",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'integer',
    'min'=>'1',
    'max'=>'100',
    "help"=>$clang->gT('Set the percentage width of the answer column (1-100)'),
    "caption"=>$clang->gT('Answer width'));

    $qattributes["array_filter"]=array(
    "types"=>"1ABCEF:;MPL",
    'category'=>$clang->gT('Logic'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT("Enter the code of a Multiple choice question to only show the matching answer options in this question."),
    "caption"=>$clang->gT('Array filter'));

    $qattributes["array_filter_exclude"]=array(
    "types"=>"1ABCEF:;MPL",
    'category'=>$clang->gT('Logic'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT("Enter the code of a Multiple choice question to exclude the matching answer options in this question."),
    "caption"=>$clang->gT('Array filter exclusion'));

    $qattributes["assessment_value"]=array(
    "types"=>"MP",
    'category'=>$clang->gT('Logic'),
    'sortorder'=>100,
    'default'=>'1',
    'inputtype'=>'integer',
    "help"=>$clang->gT("If one of the subquestions is marked then for each marked subquestion this value is added as assessment."),
    "caption"=>$clang->gT('Assessment value'));

    $qattributes["category_separator"]=array(
    "types"=>"!",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('Category separator'),
    "caption"=>$clang->gT('Category separator'));

    $qattributes["code_filter"]=array(
    "types"=>"WZ",
    'category'=>$clang->gT('Logic'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('Filter the available answers by this value'),
    "caption"=>$clang->gT('Code filter'));

    $qattributes["display_columns"]=array(
    "types"=>"GLMZ",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'integer',
    'default'=>'1',
    'min'=>'1',
    'max'=>'100',
    "help"=>$clang->gT('The answer options will be distributed across the number of columns set here'),
    "caption"=>$clang->gT('Display columns'));

    $qattributes["display_rows"]=array(
    "types"=>"QSTU",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('How many rows to display'),
    "caption"=>$clang->gT('Display rows'));

    $qattributes["dropdown_dates"]=array(
    "types"=>"D",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('Use accessible dropdown boxes instead of calendar popup'),
    "caption"=>$clang->gT('Display dropdown boxes'));

    $qattributes["dropdown_dates_year_min"]=array(
    "types"=>"D",
    'category'=>$clang->gT('Display'),
    'sortorder'=>110,
    'inputtype'=>'text',
    "help"=>$clang->gT('Minimum year value in calendar'),
    "caption"=>$clang->gT('Minimum year'));

    $qattributes["dropdown_dates_year_max"]=array(
    "types"=>"D",
    'category'=>$clang->gT('Display'),
    'sortorder'=>111,
    'inputtype'=>'text',
    "help"=>$clang->gT('Maximum year value for calendar'),
    "caption"=>$clang->gT('Maximum year'));

    $qattributes["dropdown_prepostfix"]=array(
    "types"=>"1",
    'category'=>$clang->gT('Display'),
    'sortorder'=>112,
    'inputtype'=>'text',
    'i18n'=>true,
    "help"=>$clang->gT('Prefix|Suffix for dropdown lists'),
    "caption"=>$clang->gT('Dropdown prefix/suffix'));

    $qattributes["dropdown_separators"]=array(
    "types"=>"1",
    'category'=>$clang->gT('Display'),
    'sortorder'=>120,
    'inputtype'=>'text',
    "help"=>$clang->gT('Post-Answer-Separator|Inter-Dropdownlist-Separator for dropdown lists'),
    "caption"=>$clang->gT('Dropdown separator'));

    $qattributes["dualscale_headerA"]=array(
    "types"=>"1",
    'category'=>$clang->gT('Display'),
    'sortorder'=>110,
    'inputtype'=>'text',
    'i18n'=>true,
    "help"=>$clang->gT('Enter a header text for the first scale'),
    "caption"=>$clang->gT('Header for first scale'));

    $qattributes["dualscale_headerB"]=array(
    "types"=>"1",
    'category'=>$clang->gT('Display'),
    'sortorder'=>111,
    'inputtype'=>'text',
    'i18n'=>true,
    "help"=>$clang->gT('Enter a header text for the second scale'),
    "caption"=>$clang->gT('Header for second scale'));

    $qattributes["equals_num_value"]=array(
    "types"=>"K",
    'category'=>$clang->gT('Input'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('Multiple numeric inputs sum must equal this value'),
    "caption"=>$clang->gT('Equals sum value'));

    $qattributes["exclude_all_others"]=array(
    "types"=>"M",
    'category'=>$clang->gT('Logic'),
    'sortorder'=>130,
    'inputtype'=>'text',
    "help"=>$clang->gT('Excludes all other options if a certain answer is selected - just enter the answer code(s) seperated with a semikolon.'),
    "caption"=>$clang->gT('Exclusive option'));

    $qattributes["exclude_all_others_auto"]=array(
    "types"=>"M",
    'category'=>$clang->gT('Logic'),
    'sortorder'=>131,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('If the participant marks all options, uncheck all and check the option set in the "Exclusive option" setting'),
    "caption"=>$clang->gT('Auto-check exclusive option if all others are checked'));

    // Map Options

    $qattributes["location_city"]=array(
    "types"=>"S",
    'readonly_when_active'=>true,
    'category'=>$clang->gT('Location'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('Yes'),
    1=>$clang->gT('No')),
    "help"=>$clang->gT("Store the city?"),
    "caption"=>$clang->gT("Save city"));

    $qattributes["location_state"]=array(
    "types"=>"S",
    'readonly_when_active'=>true,
    'category'=>$clang->gT('Location'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('Yes'),
    1=>$clang->gT('No')),
    "help"=>$clang->gT("Store the state?"),
    "caption"=>$clang->gT("Save state"));

    $qattributes["location_postal"]=array(
    "types"=>"S",
    'readonly_when_active'=>true,
    'category'=>$clang->gT('Location'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('Yes'),
    1=>$clang->gT('No')),
    "help"=>$clang->gT("Store the postal code?"),
    "caption"=>$clang->gT("Save postal code"));

    $qattributes["location_country"]=array(
    "types"=>"S",
    'readonly_when_active'=>true,
    'category'=>$clang->gT('Location'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('Yes'),
    1=>$clang->gT('No')),
    "help"=>$clang->gT("Store the country?"),
    "caption"=>$clang->gT("Save country"));

    $qattributes["statistics_showmap"]=array(
    "types"=>"S",
    'category'=>$clang->gT('Statistics'),
    'inputtype'=>'singleselect',
    'sortorder'=>100,
    'options'=>array(1=>$clang->gT('Yes'), 0=>$clang->gT('No')),
    'help'=>$clang->gT("Show a map in the statistics?"),
    'caption'=>$clang->gT("Display map"),
    'default'=>1
    );
    
    $qattributes["statistics_showgraph"]=array(
    'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*',
    'category'=>$clang->gT('Statistics'),
    'inputtype'=>'singleselect',
    'sortorder'=>101,
    'options'=>array(1=>$clang->gT('Yes'), 0=>$clang->gT('No')),
    'help'=>$clang->gT("Display a chart in the statistics?"),
    'caption'=>$clang->gT("Display chart"),
    'default'=>1
    );
    
    $qattributes["statistics_graphtype"]=array(
    "types"=>'15ABCDEFGHIKLNOQRSTUWXYZ!:;|*',
    'category'=>$clang->gT('Statistics'),
    'inputtype'=>'singleselect',
    'sortorder'=>102,
    'options'=>array(0=>$clang->gT('Bar chart'), 1=>$clang->gT('Pie chart')),
    'help'=>$clang->gT("Select the type of chart to be displayed"),
    'caption'=>$clang->gT("Chart type"),
    'default'=>0
    );

    $qattributes["location_mapservice"]=array(
    "types"=>"S",
    'category'=>$clang->gT('Location'),
    'sortorder'=>90,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('Off'),
    1=>$clang->gT('Google Maps')),
    "help"=>$clang->gT("Activate this to show a map above the input field where the user can select a location"),
    "caption"=>$clang->gT("Use mapping service"));

    $qattributes["location_mapwidth"]=array(
    "types"=>"S",
    'category'=>$clang->gT('Location'),
    'sortorder'=>102,
    'inputtype'=>'text',
    'default'=>'500',
    "help"=>$clang->gT("Width of the map in pixel"),
    "caption"=>$clang->gT("Map width"));

    $qattributes["location_mapheight"]=array(
    "types"=>"S",
    'category'=>$clang->gT('Location'),
    'sortorder'=>103,
    'inputtype'=>'text',
    'default'=>'300',
    "help"=>$clang->gT("Height of the map in pixel"),
    "caption"=>$clang->gT("Map height"));

    $qattributes["location_nodefaultfromip"]=array(
    "types"=>"S",
    'category'=>$clang->gT('Location'),
    'sortorder'=>91,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('Yes'),
    1=>$clang->gT('No')),
    "help"=>$clang->gT("Get the default location using the user's IP address?"),
    "caption"=>$clang->gT("IP as default location"));

    $qattributes["location_defaultcoordinates"]=array(
    "types"=>"S",
    'category'=>$clang->gT('Location'),
    'sortorder'=>101,
    'inputtype'=>'text',
    "help"=>$clang->gT('Default coordinates of the map when the page first loads. Format: latitude [space] longtitude'),
    "caption"=>$clang->gT('Default position'));

    $qattributes["location_mapzoom"]=array(
    "types"=>"S",
    'category'=>$clang->gT('Location'),
    'sortorder'=>101,
    'inputtype'=>'text',
    'default'=>'11',
    "help"=>$clang->gT("Map zoom level"),
    "caption"=>$clang->gT("Zoom level"));

    // End Map Options

    $qattributes["hide_tip"]=array(
    "types"=>"!KLMNOPRSWZ",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('Hide the tip that is normally shown with a question'),
    "caption"=>$clang->gT('Hide tip'));

    $qattributes['hidden']=array(
    'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*',
    'category'=>$clang->gT('Display'),
    'sortorder'=>101,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    'help'=>$clang->gT('Hide this question at any time. This is useful for including data using answer prefilling.'),
    'caption'=>$clang->gT('Always hide this question'));

    $qattributes["max_answers"]=array(
    "types"=>"MPR",
    'category'=>$clang->gT('Logic'),
    'sortorder'=>11,
    'inputtype'=>'integer',
    "help"=>$clang->gT('Limit the number of possible answers'),
    "caption"=>$clang->gT('Maximum answers'));

    $qattributes["max_num_value"]=array(
    "types"=>"K",
    'category'=>$clang->gT('Input'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('Maximum sum value of multiple numeric input'),
    "caption"=>$clang->gT('Maximum sum value'));

    $qattributes["max_num_value_n"]=array(
    "types"=>"N",
    'category'=>$clang->gT('Input'),
    'sortorder'=>110,
    'inputtype'=>'integer',
    "help"=>$clang->gT('Maximum value of the numeric input'),
    "caption"=>$clang->gT('Maximum value'));

    $qattributes["max_num_value_sgqa"]=array(
    "types"=>"K",
    'category'=>$clang->gT('Logic'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('Enter the SGQA identifier to use the total of a previous question as the maximum for this question'),
    "caption"=>$clang->gT('Max value from SGQA'));

    $qattributes["maximum_chars"]=array(
    "types"=>"STUNQK:",
    'category'=>$clang->gT('Input'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('Maximum characters allowed'),
    "caption"=>$clang->gT('Maximum characters'));

    $qattributes["min_answers"]=array(
    "types"=>"MPR",
    'category'=>$clang->gT('Logic'),
    'sortorder'=>10,
    'inputtype'=>'integer',
    "help"=>$clang->gT('Ensure a minimum number of possible answers (0=No limit)'),
    "caption"=>$clang->gT('Minimum answers'));

    $qattributes["min_num_value"]=array(
    "types"=>"K",
    'category'=>$clang->gT('Input'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('The sum of the multiple numeric inputs must be greater than this value'),
    "caption"=>$clang->gT('Minimum sum value'));

    $qattributes["min_num_value_n"]=array(
    "types"=>"N",
    'category'=>$clang->gT('Input'),
    'sortorder'=>100,
    'inputtype'=>'integer',
    "help"=>$clang->gT('Minimum value of the numeric input'),
    "caption"=>$clang->gT('Minimum value'));

    $qattributes["min_num_value_sgqa"]=array(
    "types"=>"K",
    'category'=>$clang->gT('Logic'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('Enter the SGQA identifier to use the total of a previous question as the minimum for this question'),
    "caption"=>$clang->gT('Minimum value from SGQA'));

    $qattributes["multiflexible_max"]=array(
    "types"=>":",
    'category'=>$clang->gT('Display'),
    'sortorder'=>112,
    'inputtype'=>'text',
    "help"=>$clang->gT('Maximum value for array(mult-flexible) question type'),
    "caption"=>$clang->gT('Maximum value'));

    $qattributes["multiflexible_min"]=array(
    "types"=>":",
    'category'=>$clang->gT('Display'),
    'sortorder'=>110,
    'inputtype'=>'text',
    "help"=>$clang->gT('Minimum value for array(multi-flexible) question type'),
    "caption"=>$clang->gT('Minimum value'));

    $qattributes["multiflexible_step"]=array(
    "types"=>":",
    'category'=>$clang->gT('Display'),
    'sortorder'=>111,
    'inputtype'=>'text',
    "help"=>$clang->gT('Step value'),
    "caption"=>$clang->gT('Step value'));

    $qattributes["multiflexible_checkbox"]=array(
    "types"=>":",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('Use checkbox layout'),
    "caption"=>$clang->gT('Checkbox layout'));

    $qattributes["reverse"]=array(
    "types"=>"D:",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('Present answer options in reverse order'),
    "caption"=>$clang->gT('Reverse answer order'));

    $qattributes["num_value_equals_sgqa"]=array(
    "types"=>"K",
    'category'=>$clang->gT('Logic'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('SGQA identifier to use total of previous question as total for this question'),
    "caption"=>$clang->gT('Value equals SGQA'));

    $qattributes["num_value_int_only"]=array(
    "types"=>"N",
    'category'=>$clang->gT('Input'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(
    0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('Restrict input to integer values'),
    "caption"=>$clang->gT('Integer only'));

    $qattributes["numbers_only"]=array(
    "types"=>"Q;S",
    'category'=>$clang->gT('Other'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(
    0=>$clang->gT('No'),
    1=>$clang->gT('Yes')
    ),
    'default'=>0,
    "help"=>$clang->gT('Allow only numerical input'),
    "caption"=>$clang->gT('Numbers only')
    );

    $qattributes['show_totals'] =	array(
    'types' =>	';',
    'category' =>	$clang->gT('Other'),
    'sortorder' =>	100,
    'inputtype'	=> 'singleselect',
    'options' =>	array(
    'X' =>	$clang->gT('Off'),
    'R' =>	$clang->gT('Rows'),
    'C' =>	$clang->gT('Columns'),
    'B' =>	$clang->gT('Both rows and columns')
    ),
    'default' =>	'X',
    'help' =>	$clang->gT('Show totals for either rows, columns or both rows and columns'),
    'caption' =>	$clang->gT('Show totals for')
    );

    $qattributes['show_grand_total'] =	array(
    'types' =>	';',
    'category' =>	$clang->gT('Other'),
    'sortorder' =>	100,
    'inputtype' =>	'singleselect',
    'options' =>	array(
    0 =>	$clang->gT('No'),
    1 =>	$clang->gT('Yes')
    ),
    'default' =>	0,
    'help' =>	$clang->gT('Show grand total for either columns or rows'),
    'caption' =>	$clang->gT('Show grand total')
    );

    $qattributes["input_boxes"]=array(
    "types"=>":",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT("Present as text input boxes instead of dropdown lists"),
    "caption"=>$clang->gT("Text inputs"));

    $qattributes["other_comment_mandatory"]=array(
    "types"=>"PLW!Z",
    'category'=>$clang->gT('Logic'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT("Make the 'Other:' comment field mandatory when the 'Other:' option is active"),
    "caption"=>$clang->gT("'Other:' comment mandatory"));

    $qattributes["other_numbers_only"]=array(
    "types"=>"LMP",
    'category'=>$clang->gT('Logic'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT("Allow only numerical input for 'Other' text"),
    "caption"=>$clang->gT("Numbers only for 'Other'"));

    $qattributes["other_replace_text"]=array(
    "types"=>"LMPWZ!",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'text',
    'i18n'=>true,
    "help"=>$clang->gT("Replaces the label of the 'Other:' answer option with a custom text"),
    "caption"=>$clang->gT("Label for 'Other:' option"));

    $qattributes["page_break"]=array(
    "types"=>"15ABCDEFGHKLMNOPQRSTUWXYZ!:;|*",
    'category'=>$clang->gT('Other'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('Insert a page break before this question in printable view by setting this to Yes.'),
    "caption"=>$clang->gT('Insert page break in printable view'));

    $qattributes["prefix"]=array(
    "types"=>"KNQS",
    'category'=>$clang->gT('Display'),
    'sortorder'=>10,
    'inputtype'=>'text',
    'i18n'=>true,
    "help"=>$clang->gT('Add a prefix to the answer field'),
    "caption"=>$clang->gT('Answer prefix'));

    $qattributes["public_statistics"]=array(
    "types"=>"15ABCEFGHKLMNOPRWYZ!:*",
    'category'=>$clang->gT('Other'),
    'sortorder'=>80,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('Show statistics of this question in the public statistics page'),
    "caption"=>$clang->gT('Show in public statistics'));

    $qattributes["random_order"]=array(
    "types"=>"!ABCEFHKLMOPQRWZ1:;",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('Off'),
    1=>$clang->gT('Randomize on each page load'),
    2=>$clang->gT('Randomize once on survey start')
    ),
    'default'=>0,
    "help"=>$clang->gT('Present answers in random order'),
    "caption"=>$clang->gT('Random answer order'));

    /*
    $qattributes['relevance']=array(
    'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*',
    'category'=>$clang->gT('Display'),
    'sortorder'=>1,
    'inputtype'=>'text',
    'default'=>'1',
    'help'=>$clang->gT('The relevance equation determines whether a question should be shown (if true) or hiddden and marked as Not Applicable (if false).'
    . '  The relevance equation can be as complex as you like, using any combination of mathematical operators, nested parentheses,'
    . ' any variable or token that has already been set, and any of more than 50 functions.  It is parsed by the ExpressionManager.'),
    'caption'=>$clang->gT('Relevance equation'));
    */

    $qattributes["parent_order"]=array(
    "types"=>"!ABCEFHKLMOPQRWZ1:;",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "caption"=>$clang->gT('Get order from previous question'),
    "help"=>$clang->gT('Enter question ID to get subquestion order from a previous question'));

    $qattributes["slider_layout"]=array(
    "types"=>"K",
    'category'=>$clang->gT('Slider'),
    'sortorder'=>1,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('Use slider layout'),
    "caption"=>$clang->gT('Use slider layout'));

    $qattributes["slider_min"]=array(
    "types"=>"K",
    'category'=>$clang->gT('Slider'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('Slider minimum value'),
    "caption"=>$clang->gT('Slider minimum value'));

    $qattributes["slider_max"]=array(
    "types"=>"K",
    'category'=>$clang->gT('Slider'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('Slider maximum value'),
    "caption"=>$clang->gT('Slider maximum value'));

    $qattributes["slider_accuracy"]=array(
    "types"=>"K",
    'category'=>$clang->gT('Slider'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('Slider accuracy'),
    "caption"=>$clang->gT('Slider accuracy'));

    $qattributes["slider_default"]=array(
    "types"=>"K",
    'category'=>$clang->gT('Slider'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('Slider initial value'),
    "caption"=>$clang->gT('Slider initial value'));

    $qattributes["slider_middlestart"]=array(
    "types"=>"K",
    'category'=>$clang->gT('Slider'),
    'sortorder'=>10,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('The handle is displayed at the middle of the slider (this will not set the initial value)'),
    "caption"=>$clang->gT('Slider starts at the middle position'));

    $qattributes["slider_rating"]=array(
    "types"=>"5",
    'category'=>$clang->gT('Display'),
    'sortorder'=>90,
    'inputtype'=>'singleselect',
    'options'=>array(
    0=>$clang->gT('No'),
    1=>$clang->gT('Yes - stars'),
    2=>$clang->gT('Yes - slider with emoticon'),
    ),
    'default'=>0,
    "help"=>$clang->gT('Use slider layout'),
    "caption"=>$clang->gT('Use slider layout'));


    $qattributes["slider_showminmax"]=array(
    "types"=>"K",
    'category'=>$clang->gT('Slider'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('Display min and max value under the slider'),
    "caption"=>$clang->gT('Display slider min and max value'));

    $qattributes["slider_separator"]=array(
    "types"=>"K",
    'category'=>$clang->gT('Slider'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('Answer|Left-slider-text|Right-slider-text separator character'),
    "caption"=>$clang->gT('Slider left/right text separator'));

    $qattributes["suffix"]=array(
    "types"=>"KNQS",
    'category'=>$clang->gT('Display'),
    'sortorder'=>11,
    'inputtype'=>'text',
    'i18n'=>true,
    "help"=>$clang->gT('Add a suffix to the answer field'),
    "caption"=>$clang->gT('Answer suffix'));

    $qattributes["text_input_width"]=array(
    "types"=>"KNSTUQ;",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT('Width of text input box'),
    "caption"=>$clang->gT('Input box width'));

    $qattributes["use_dropdown"]=array(
    "types"=>"1FO",
    'category'=>$clang->gT('Display'),
    'sortorder'=>112,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('Present dropdown control(s) instead of list of radio buttons'),
    "caption"=>$clang->gT('Use dropdown presentation'));

    $qattributes["scale_export"]=array(
    "types"=>"CEFGHLMOPWYZ1!:*",
    'category'=>$clang->gT('Other'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('Default'),
    1=>$clang->gT('Nominal'),
    2=>$clang->gT('Ordinal'),
    3=>$clang->gT('Scale')),
    'default'=>0,
    "help"=>$clang->gT("Set a specific SPSS export scale type for this question"),
    "caption"=>$clang->gT('SPSS export scale type'));

    //Timer attributes
    $qattributes["time_limit"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>90,
    "inputtype"=>"integer",
    "help"=>$clang->gT("Limit time to answer question (in seconds)"),
    "caption"=>$clang->gT("Time limit"));

    $qattributes["time_limit_action"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>92,
    'inputtype'=>'singleselect',
    'options'=>array(1=>$clang->gT('Warn and move on'),
    2=>$clang->gT('Move on without warning'),
    3=>$clang->gT('Disable only')),
    "help"=>$clang->gT("Action to perform when time limit is up"),
    "caption"=>$clang->gT("Time limit action"));

    $qattributes["time_limit_disable_next"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>94,
    "inputtype"=>"singleselect",
    'default'=>0,
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    "help"=>$clang->gT("Disable the next button until time limit expires"),
    "caption"=>$clang->gT("Time limit disable next"));

    $qattributes["time_limit_disable_prev"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>96,
    "inputtype"=>"singleselect",
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    "help"=>$clang->gT("Disable the prev button until the time limit expires"),
    "caption"=>$clang->gT("Time limit disable prev"));

    $qattributes["time_limit_countdown_message"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>98,
    "inputtype"=>"textarea",
    'i18n'=>true,
    "help"=>$clang->gT("The text message that displays in the countdown timer during the countdown"),
    "caption"=>$clang->gT("Time limit countdown message"));

    $qattributes["time_limit_timer_style"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>100,
    "inputtype"=>"textarea",
    "help"=>$clang->gT("CSS Style for the message that displays in the countdown timer during the countdown"),
    "caption"=>$clang->gT("Time limit timer CSS style"));

    $qattributes["time_limit_message_delay"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>102,
    "inputtype"=>"integer",
    "help"=>$clang->gT("Display the 'time limit expiry message' for this many seconds before performing the 'time limit action' (defaults to 1 second if left blank)"),
    "caption"=>$clang->gT("Time limit expiry message display time"));

    $qattributes["time_limit_message"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>104,
    "inputtype"=>"textarea",
    'i18n'=>true,
    "help"=>$clang->gT("The message to display when the time limit has expired (a default message will display if this setting is left blank)"),
    "caption"=>$clang->gT("Time limit expiry message"));

    $qattributes["time_limit_message_style"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>106,
    "inputtype"=>"textarea",
    "help"=>$clang->gT("CSS style for the 'time limit expiry message'"),
    "caption"=>$clang->gT("Time limit message CSS style"));

    $qattributes["time_limit_warning"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>108,
    "inputtype"=>"integer",
    "help"=>$clang->gT("Display a 'time limit warning' when there are this many seconds remaining in the countdown (warning will not display if left blank)"),
    "caption"=>$clang->gT("1st time limit warning message timer"));

    $qattributes["time_limit_warning_display_time"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>110,
    "inputtype"=>"integer",
    "help"=>$clang->gT("The 'time limit warning' will stay visible for this many seconds (will not turn off if this setting is left blank)"),
    "caption"=>$clang->gT("1st time limit warning message display time"));

    $qattributes["time_limit_warning_message"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>112,
    "inputtype"=>"textarea",
    'i18n'=>true,
    "help"=>$clang->gT("The message to display as a 'time limit warning' (a default warning will display if this is left blank)"),
    "caption"=>$clang->gT("1st time limit warning message"));

    $qattributes["time_limit_warning_style"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>114,
    "inputtype"=>"textarea",
    "help"=>$clang->gT("CSS style used when the 'time limit warning' message is displayed"),
    "caption"=>$clang->gT("1st time limit warning CSS style"));

    $qattributes["time_limit_warning_2"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>116,
    "inputtype"=>"integer",
    "help"=>$clang->gT("Display the 2nd 'time limit warning' when there are this many seconds remaining in the countdown (warning will not display if left blank)"),
    "caption"=>$clang->gT("2nd time limit warning message timer"));

    $qattributes["time_limit_warning_2_display_time"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>118,
    "inputtype"=>"integer",
    "help"=>$clang->gT("The 2nd 'time limit warning' will stay visible for this many seconds (will not turn off if this setting is left blank)"),
    "caption"=>$clang->gT("2nd time limit warning message display time"));

    $qattributes["time_limit_warning_2_message"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>120,
    "inputtype"=>"textarea",
    'i18n'=>true,
    "help"=>$clang->gT("The 2nd message to display as a 'time limit warning' (a default warning will display if this is left blank)"),
    "caption"=>$clang->gT("2nd time limit warning message"));

    $qattributes["time_limit_warning_2_style"]=array(
    "types"=>"STUX",
    'category'=>$clang->gT('Timer'),
    'sortorder'=>122,
    "inputtype"=>"textarea",
    "help"=>$clang->gT("CSS style used when the 2nd 'time limit warning' message is displayed"),
    "caption"=>$clang->gT("2nd time limit warning CSS style"));

    $qattributes["date_format"]=array(
    "types"=>"D",
    'category'=>$clang->gT('Input'),
    'sortorder'=>100,
    "inputtype"=>"text",
    "help"=>$clang->gT("Specify a custom date/time format (the <i>d/dd m/mm yy/yyyy H/HH M/MM</i> formats and \"-./: \" characters are allowed for day/month/year/hour/minutes without or with leading zero respectively. Defaults to survey's date format"),
    "caption"=>$clang->gT("Date/Time format"));

    $qattributes["dropdown_dates_minute_step"]=array(
    "types"=>"D",
    'category'=>$clang->gT('Input'),
    'sortorder'=>100,
    "inputtype"=>"integer",
    "help"=>$clang->gT("Minute step interval when using select boxes"),
    "caption"=>$clang->gT("Minute step interval"));

    $qattributes["dropdown_dates_month_style"]=array(
    "types"=>"D",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    "inputtype"=>"singleselect",
    'options'=>array(0=>$clang->gT('Short names'),
    1=>$clang->gT('Full names'),
    2=>$clang->gT('Numbers')),
    'default'=>0,
    "help"=>$clang->gT("Change the display style of the month when using select boxes"),
    "caption"=>$clang->gT("Month display style"));

    $qattributes["show_title"]=array(
    "types"=>"|",
    'category'=>$clang->gT('File metadata'),
    'sortorder'=>124,
    "inputtype"=>"singleselect",
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>1,
    "help"=>$clang->gT("Is the participant required to give a title to the uploaded file?"),
    "caption"=>$clang->gT("Show title"));

    $qattributes["show_comment"]=array(
    "types"=>"|",
    'category'=>$clang->gT('File metadata'),
    'sortorder'=>126,
    "inputtype"=>"singleselect",
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>1,
    "help"=>$clang->gT("Is the participant required to give a comment to the uploaded file?"),
    "caption"=>$clang->gT("Show comment"));


    $qattributes["max_filesize"]=array(
    "types"=>"|",
    'category'=>$clang->gT('Other'),
    'sortorder'=>128,
    "inputtype"=>"integer",
    'default'=>10240,
    "help"=>$clang->gT("The participant cannot upload a single file larger than this size"),
    "caption"=>$clang->gT("Maximum file size allowed (in KB)"));

    $qattributes["max_num_of_files"]=array(
    "types"=>"|",
    'category'=>$clang->gT('Other'),
    'sortorder'=>130,
    "inputtype"=>"integer",
    'default'=>1,
    "help"=>$clang->gT("Maximum number of files that the participant can upload for this question"),
    "caption"=>$clang->gT("Max number of files"));

    $qattributes["min_num_of_files"]=array(
    "types"=>"|",
    'category'=>$clang->gT('Other'),
    'sortorder'=>132,
    "inputtype"=>"integer",
    'default'=>0,
    "help"=>$clang->gT("Minimum number of files that the participant must upload for this question"),
    "caption"=>$clang->gT("Min number of files"));

    $qattributes["allowed_filetypes"]=array(
    "types"=>"|",
    'category'=>$clang->gT('Other'),
    'sortorder'=>134,
    "inputtype"=>"text",
    'default'=>"png, gif, doc, odt",
    "help"=>$clang->gT("Allowed file types in comma separated format. e.g. pdf,doc,odt"),
    "caption"=>$clang->gT("Allowed file types"));

    $qattributes["random_group"]=array(
    "types"=>"15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|",
    'category'=>$clang->gT('Logic'),
    'sortorder'=>100,
    'inputtype'=>'text',
    "help"=>$clang->gT("Place questions into a specified randomization group, all questions included in the specified group will appear in a random order"),
    "caption"=>$clang->gT("Randomization group name"));

    //This builds a more useful array (don't modify)
    if ($returnByName==false)
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
        return $qat;
    }
    else {
        return $qattributes;
    }
}

function CategorySort($a, $b)
{
    $result=strnatcasecmp($a['category'], $b['category']);
    if ($result==0)
    {
        $result=$a['sortorder']-$b['sortorder'];
    }
    return $result;
}

// make sure the given string (which comes from a POST or GET variable)
// is safe to use in MySQL.  This does nothing if gpc_magic_quotes is on.
function auto_escape($str) {
    $CI = &get_instance();
    if (!get_magic_quotes_gpc()) {
        return $CI->db->escape($str);
    }
    return $str;
}

// the opposite of the above: takes a POST or GET variable which may or
// may not have been 'auto-quoted', and return the *unquoted* version.
// this is useful when the value is destined for a web page (eg) not
// a SQL query.
function auto_unescape($str) {
    if (!isset($str)) {return null;};
    if (!get_magic_quotes_gpc())
        return $str;
    return stripslashes($str);
}

// make a string safe to include in an HTML 'value' attribute.
function html_escape($str) {
    // escape newline characters, too, in case we put a value from
    // a TEXTAREA  into an <input type="hidden"> value attribute.
    return str_replace(array("\x0A","\x0D"),array("&#10;","&#13;"),
    htmlspecialchars( $str, ENT_QUOTES ));
}
function db_quote_id($id)
{
	// WE DONT HAVE nor USE other thing that alfanumeric characters in the field names
	//  $quote = $connect->nameQuote;
	//  return $quote.str_replace($quote,$quote.$quote,$id).$quote;
	$id = addslashes($id);

	switch (Yii::app()->db->getDriverName())
	{
		case "mysqli" :
		case "mysql" :
			return "`".$id."`";
			break;
		case "mssql_n" :
		case "mssql" :
		case "mssqlnative" :
		case "odbc_mssql" :
			return "[".$id."]";
			break;
		case "postgre":
			return "\"".$id."\"";
			break;
		default:
			return "`".$id."`";
	}
}

/**
 * Escapes a text value for db
 *
 * @param string $value
 * @return string
 */
function db_quoteall($value)
{
	return '\'' . addslashes($value) . '\'';
}

// make a string safe to include in a JavaScript String parameter.
function javascript_escape($str, $strip_tags=false, $htmldecode=false) {
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
function SendEmailMessage($body, $subject, $to, $from, $sitename, $ishtml=false, $bouncemail=null, $attachment=null, $customheaders="")
{

    global $maildebug, $maildebugbody;

    Yii::app()->loadConfig('email');
    $clang = Yii::app()->lang;
    $emailmethod = Yii::app()->getConfig('emailmethod');
    $emailsmtphost = Yii::app()->getConfig("emailsmtphost");
    $emailsmtpuser = Yii::app()->getConfig("emailsmtpuser");
    $emailsmtppassword = Yii::app()->getConfig("emailsmtppassword");
    $emailsmtpdebug = Yii::app()->getConfig("emailsmtpdebug");
    $emailsmtpssl = Yii::app()->getConfig("emailsmtpssl");
    $defaultlang = Yii::app()->getConfig("defaultlang");
    $emailcharset = Yii::app()->getConfig("charset");

    if (!is_array($to)){
        $to=array($to);
    }



    if (!is_array($customheaders) && $customheaders == '')
    {
        $customheaders=array();
    }
    if (Yii::app()->getConfig('demoMode'))
    {
        $maildebug=$clang->gT('Email was not sent because demo-mode is activated.');
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


    require_once(APPPATH.'/third_party/phpmailer/class.phpmailer.php');
    $mail = new PHPMailer;
    if (!$mail->SetLanguage($defaultlang,APPPATH.'/third_party/phpmailer/language/'))
    {
        $mail->SetLanguage('en',APPPATH.'/third_party/phpmailer/language/');
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
    if (get_magic_quotes_gpc() != "0")	{$body = stripcslashes($body);}
    if ($ishtml) {
        $mail->IsHTML(true);
        $mail->Body = $body;
        $mail->AltBody = strip_tags(br2nl(html_entity_decode($body,ENT_QUOTES,'UTF-8')));
    } else
    {
        $mail->IsHTML(false);
        $mail->Body = $body;
    }

    // add the attachment if there is one
    if(!is_null($attachment))
        $mail->AddAttachment($attachment);

    if (trim($subject)!='') {$mail->Subject = "=?$emailcharset?B?" . base64_encode($subject) . "?=";}
    if ($emailsmtpdebug>0) {
        ob_start();
    }
    $sent=$mail->Send();
    $maildebug=$mail->ErrorInfo;
    if ($emailsmtpdebug>0) {
        $maildebug .= '<li>'.$clang->gT('SMTP debug output:').'</li><pre>'.strip_tags(ob_get_contents()).'</pre>';
        ob_end_clean();
    }
    $maildebugbody=$mail->Body;
    //if(!$sent) var_dump($maildebug);
    return $sent;
}


/**
*  This functions removes all HTML tags, Javascript, CRs, linefeeds and other strange chars from a given text
*
* @param string $sTextToFlatten  Text you want to clean
* @param boolan $bDecodeHTMLEntities If set to true then all HTML entities will be decoded to the specified charset. Default: false
* @param string $sCharset Charset to decode to if $decodeHTMLEntities is set to true
*
* @return string  Cleaned text
*/
function FlattenText($sTextToFlatten, $keepSpan=false, $bDecodeHTMLEntities=false, $sCharset='UTF-8', $bStripNewLines=true)
{
    $sNicetext = strip_javascript($sTextToFlatten);
    // When stripping tags, add a space before closing tags so that strings with embedded HTML tables don't get concatenated
    $sNicetext = str_replace('</',' </', $sNicetext);
    if ($keepSpan) {
        // Keep <span> so can show EM syntax-highlighting; add space before tags so that word-wrapping not destroyed when remove tags.
        $sNicetext = strip_tags($sNicetext,'<span>');
    }
    else {
        $sNicetext = strip_tags($sNicetext);
    }
    if ($bStripNewLines ){  // strip new lines
        $sNicetext = preg_replace(array('~\Ru~','/\s{2,}/'),array(' ',' '), $sNicetext);
    }
    else // unify newlines to \r\n
    {
        $sNicetext = preg_replace(array('~\Ru~'), array("\r\n"), $sNicetext);
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
* getArrayFiltersForGroup() queries the database and produces a list of array_filter questions and targets with in the same group
* @return returns an nested array which contains arrays with the keys: question id (qid), question manditory, target type (type), and list_filter id (fid)
*/
function getArrayFiltersForGroup($surveyid,$gid)
{
    // TODO: Check list_filter values to make sure questions are previous?
    $surveyid=sanitize_int($surveyid);
    $gid=sanitize_int($gid);
    // Get All Questions in Current Group
    $fieldmap = createFieldMap($surveyid,'full');
    if($gid != "") {
        $qrows = arraySearchByKey($gid, $fieldmap, 'gid');
    } else {
        $qrows = $fieldmap;
    }
    $grows = array(); //Create an empty array in case query not return any rows
    // Store each result as an array with in the $grows array
    foreach ($qrows as $qrow) {
        if (isset($qrow['gid']) && !empty($qrow['gid'])) {
            $grows[$qrow['qid']] = array('qid' => $qrow['qid'],'type' => $qrow['type'], 'mandatory' => $qrow['mandatory'], 'title' => $qrow['title'], 'gid' => $qrow['gid']);
        }
    }
    $attrmach = array(); // Stores Matches of filters that have their values as questions with in current group
    $grows2 = $grows;
    foreach ($grows as $qrow) // Cycle through questions to see if any have list_filter attributes
    {
        $qresult = getQuestionAttributeValues($qrow['qid']);
        if (isset($qresult['array_filter'])) // We Found a array_filter attribute
        {
            $val = $qresult['array_filter']; // Get the Value of the Attribute ( should be a previous question's title in same group )
            foreach ($grows2 as $avalue)
            {
                if ($avalue['title'] == $val)
                {
                    $filter = array('qid' => $qrow['qid'], 'mandatory' => $qrow['mandatory'], 'type' => $avalue['type'], 'fid' => $avalue['qid'], 'gid' => $qrow['gid'], 'gid2'=>$avalue['gid']);
                    array_push($attrmach,$filter);
                }
            }
            reset($grows2);
        }
    }
    return $attrmach;
}


/**
* getArrayFilterExcludesCascadesForGroup() queries the database and produces a list of array_filter_exclude questions and targets with in the same group
* @return returns a keyed nested array, keyed by the qid of the question, containing cascade information
*/
function getArrayFilterExcludesCascadesForGroup($surveyid, $gid="", $output="qid")
{
    $surveyid=sanitize_int($surveyid);
    $gid=sanitize_int($gid);

    $cascaded=array();
    $sources=array();
    $qidtotitle=array();
    $fieldmap = createFieldMap($surveyid,'full');
    if($gid != "") {
        $qrows = arraySearchByKey($gid, $fieldmap, 'gid');
    } else {
        $qrows = $fieldmap;
    }
    $grows = array(); //Create an empty array in case query not return any rows
    // Store each result as an array with in the $grows array
    foreach ($qrows as $qrow) {
        if (isset($qrow['gid']) && !empty($qrow['gid'])) {
            $grows[$qrow['qid']] = array('qid' => $qrow['qid'],'type' => $qrow['type'], 'mandatory' => $qrow['mandatory'], 'title' => $qrow['title'], 'gid' => $qrow['gid']);
        }
    }
    $attrmach = array(); // Stores Matches of filters that have their values as questions within current group
    foreach ($grows as $qrow) // Cycle through questions to see if any have list_filter attributes
    {
        $qidtotitle[$qrow['qid']]=$qrow['title'];
        $qresult = getQuestionAttributeValues($qrow['qid'],$qrow['type']);
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
                    $cascades=array();                     /* Create an empty array */

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
    $cascade2=array();
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

/**
* getArrayFilterExcludesForGroup() queries the database and produces a list of array_filter_exclude questions and targets with in the same group
* @return returns an nested array which contains arrays with the keys: question id (qid), question manditory, target type (type), and list_filter id (fid)
*/
function getArrayFilterExcludesForGroup($surveyid,$gid)
{
    // TODO: Check list_filter values to make sure questions are previous?
    $CI = &get_instance();
    $surveyid=sanitize_int($surveyid);
    $gid=sanitize_int($gid);
    // Get All Questions in Current Group
    $fieldmap = createFieldMap($surveyid,'full');
    if($gid != "") {
        $qrows = arraySearchByKey($gid, $fieldmap, 'gid');
    } else {
        $qrows = $fieldmap;
    }
    $grows = array(); //Create an empty array in case query not return any rows
    // Store each result as an array with in the $grows array
    foreach ($qrows as $qrow) {
        if (isset($qrow['gid']) && !empty($qrow['gid'])) {
            $grows[$qrow['qid']] = array('qid' => $qrow['qid'],'type' => $qrow['type'], 'mandatory' => $qrow['mandatory'], 'title' => $qrow['title'], 'gid' => $qrow['gid']);
        }
    }
    $attrmach = array(); // Stores Matches of filters that have their values as questions within current group
    $grows2 = $grows;
    foreach ($grows as $qrow) // Cycle through questions to see if any have list_filter attributes
    {
        $qresult = getQuestionAttributeValues($qrow['qid'],$qrow['type']);
        if (isset($qresult['array_filter_exclude'])) // We Found a array_filter attribute
        {
            $val = $qresult['array_filter_exclude']; // Get the Value of the Attribute ( should be a previous question's title in same group )
            foreach ($grows2 as $avalue)
            {
                if ($avalue['title'] == $val)
                {
                    //Get the code for this question, so we can see if any later questions in this group us it for an array_filter_exclude
                    //$cqquery = "SELECT {$dbprefix}questions.title FROM {$dbprefix}questions WHERE {$dbprefix}questions.qid='".$qrow['qid']."'";
                    $CI->load->model('Questions_model');
                    $cqresult=$CI->Questions_model->getSomeRecords("title",array("qid"=>$qrow['qid']));
                    //$cqresult=db_execute_assoc($cqquery);
                    $xqid="";
                    //while($ftitle=$cqresult->FetchRow())
                    foreach ($cqresult->result_array() as $ftitle)
                    {
                        $xqid=$ftitle['title'];
                    }

                    $filter = array('qid'           => $qrow['qid'],
                    'mandatory'     => $qrow['mandatory'],
                    'type'          => $avalue['type'],
                    'fid'           => $avalue['qid'],
                    'gid'           => $qrow['gid'],
                    'gid2'          => $avalue['gid'],
                    'source_title'  => $avalue['title'],
                    'source_qid'    => $avalue['qid'],
                    'this_title'    => $xqid);
                    array_push($attrmach,$filter);
                }
            }
            reset($grows2);
        }
    }
    return $attrmach;
}

/**
* getArrayFiltersForQuestion($qid) finds out if a question has an array_filter attribute and what codes where selected on target question
* @return returns an array of codes that were selected else returns false
*/
function getArrayFiltersForQuestion($qid)
{
    static $cache = array();

    // TODO: Check list_filter values to make sure questions are previous?
    $CI = &get_instance();
    $qid=sanitize_int($qid);
    if (isset($cache[$qid])) return $cache[$qid];

    $attributes = getQuestionAttributeValues($qid);
    if (isset($attributes['array_filter']) && $CI->session->userdata('fieldarray')) {
        $val = $attributes['array_filter']; // Get the Value of the Attribute ( should be a previous question's title in same group )
        foreach ($CI->session->userdata('fieldarray') as $fields)
        {
            if ($fields[2] == $val)
            {
                // we found the target question, now we need to know what the answers where, we know its a multi!
                $fields[0]=sanitize_int($fields[0]);
                //$query = "SELECT title FROM ".db_table_name('questions')." where parent_qid='{$fields[0]}' AND language='".$_SESSION['s_lang']."' order by question_order";
                $CI->load->model('Questions_model');
                $qresult=$CI->Questions_model->getSomeRecords("title",array("parent_qid"=>$fields[0],"language"=>$CI->session->userdata('s_lang')),"question_order");
                //$qresult = db_execute_assoc($query);  //Checked
                $selected = array();
                //while ($code = $qresult->fetchRow())
                foreach ($qresult->result_array() as $code)
                {
                    if ($CI->session->userdata($fields[1].$code['title']) == "Y"
                    || $CI->session->userdata($fields[1]) == $code['title'])			 array_push($selected,$code['title']);
                }

                //Now we also need to find out if (a) the question had "other" enabled, and (b) if that was selected
                //$query = "SELECT other FROM ".db_table_name('questions')." where qid='{$fields[0]}'";
                $qresult=$CI->Questions_model->getSomeRecords("other",array("qid"=>$fields[0]));
                //$qresult = db_execute_assoc($query);
                //while ($row=$qresult->fetchRow()) {$other=$row['other'];}
                foreach ($qresult->result_array() as $row) {$other=$row['other'];}
                if($other == "Y")
                {
                    if($CI->session->userdata($fields[1].'other') && $CI->session->userdata($fields[1].'other') !="") {array_push($selected, "other");}
                }
                $cache[$qid] = $selected;
                return $cache[$qid];
            }
        }
        $cache[$qid] = false;
        return $cache[$qid];
    }
    $cache[$qid] = false;
    return $cache[$qid];
}

/**
* getGroupsByQuestion($surveyid)
* @return returns a keyed array of groups to questions ie: array([1]=>[2]) question qid 1, is in group gid 2.
*/
function getGroupsByQuestion($surveyid) {
    $CI = &get_instance();
    $output=array();

    $surveyid=sanitize_int($surveyid);
    //$query="SELECT qid, gid FROM ".db_table_name('questions')." WHERE sid='$surveyid'";
    //$result = db_execute_assoc($query);

    $CI->load->model('Questions_model');
    $result=$CI->Questions_model->getSomeRecords("qid, gid",array("sid"=>$surveyid));

    foreach ($qresult->result_array() as $val)
    //while ($val = $result->FetchRow())
    {
        $output[$val['qid']]=$val['gid'];
    }
    return $output;
}

/**
* getArrayFiltersForGroup($qid) finds out if a question is in the current group or not for array filter
* @return returns true if its not in currect group and false if it is..
*/
function getArrayFiltersOutGroup($qid)
{
    // TODO: Check list_filter values to make sure questions are previous?
    global $gid;
    $CI = &get_instance();
    $surveyid = Yii::app()->getConfig('sid');

    $qid=sanitize_int($qid);
    $attributes = getQuestionAttributeValues($qid);
    if (isset($attributes['array_filter'])) // We Found a array_filter attribute
    {
        $val = $attributes['array_filter']; // Get the Value of the Attribute ( should be a previous question's title in same group )

        // we found the target question, now we need to know what the answers where, we know its a multi!
        $surveyid=returnglobal('sid');
        $fieldmap = createFieldMap($surveyid, 'full');
        $val2 = arraySearchByKey($val, $fieldmap, 'title', 1);
        if ($val2['gid'] != $gid) return true;
        if ($val2['gid'] == $gid) return false;
        return false;
    }
    return false;
}

/**
* getArrayFiltersExcludesOutGroup($qid) finds out if a question is in the current group or not for array filter exclude
* @return returns true if its not in currect group and false if it is..
*/
function getArrayFiltersExcludesOutGroup($qid)
{
    // TODO: Check list_filter values to make sure questions are previous?
    global $gid;
    $CI = &get_instance();
    $surveyid = Yii::app()->getConfig('sid');

    $qid=sanitize_int($qid);
    $attributes = getQuestionAttributeValues($qid);
    if (isset($attributes['array_filter_exclude'])) // We Found a array_filter attribute
    {
        $val = $attributes['array_filter_exclude']; // Get the Value of the Attribute ( should be a previous question's title in same group )

        // we found the target question, now we need to know what the answers where, we know its a multi!
        $surveyid=returnglobal('sid');
        $fieldmap = createFieldMap($surveyid, 'full');
        $val2 = arraySearchByKey($val, $fieldmap, 'title', 1);
        if ($val2['gid'] != $gid) return true;
        if ($val2['gid'] == $gid) return false;
    }
    return false;
}

/**
* getArrayFilterExcludesForQuestion($qid) finds out if a question has an array_filter_exclude attribute and what codes where selected on target question
* @return returns an array of codes that were selected else returns false
*/
function getArrayFilterExcludesForQuestion($qid)
{
    static $cascadesCache = array();
    static $cache = array();

    $CI = & get_instance();
    $dbprefix = $CI->db->dbprefix;

    // TODO: Check list_filter values to make sure questions are previous?
    //	$surveyid = Yii::app()->getConfig('sid');
    $surveyid=returnglobal('sid');
    $qid=sanitize_int($qid);

    if (isset($cache[$qid])) return $cache[$qid];

    $attributes = getQuestionAttributeValues($qid);
    $excludevals=array();
    if (isset($attributes['array_filter_exclude'])) // We Found a array_filter_exclude attribute
    {
        $selected=array();
        $excludevals[] = $attributes['array_filter_exclude']; // Get the Value of the Attribute ( should be a previous question's title in same group )
        /* Find any cascades and place them in the $excludevals array*/
        if (!isset($cascadesCache[$surveyid])) {
            $cascadesCache[$surveyid] = getArrayFilterExcludesCascadesForGroup($surveyid, "", "title");
        }
        $array_filterXqs_cascades = $cascadesCache[$surveyid];

        if(isset($array_filterXqs_cascades[$qid]))
        {
            foreach($array_filterXqs_cascades[$qid] as $afc)
            {
                $excludevals[]=array("value"=>$afc);

            }
        }
        /* For each $val (question title) that applies to this, check what values exist and add them to the $selected array */
        foreach ($excludevals as $val)
        {
            foreach ($_SESSION['fieldarray'] as $fields) //iterate through every question in the survey
            {
                if ($fields[2] == $val)
                {
                    // we found the target question, now we need to know what the answers were!
                    $fields[0]=sanitize_int($fields[0]);
                    $query = "SELECT title FROM ".$CI->db->dbprefix('questions')." where parent_qid='{$fields[0]}' AND language='".$_SESSION['s_lang']."' order by question_order";
                    $qresult = db_execute_assoc($query);  //Checked
                    foreach ($qresult->result_array() as $code)
                    {
                        if (isset($_SESSION[$fields[1]]))
                            if ((isset($_SESSION[$fields[1].$code['title']]) && $_SESSION[$fields[1].$code['title']] == "Y")
                            || $_SESSION[$fields[1]] == $code['title'])
                                array_push($selected,$code['title']);
                    }
                    //Now we also need to find out if (a) the question had "other" enabled, and (b) if that was selected
                    $query = "SELECT other FROM ".$CI->db->dbprefix('questions')." where qid='{$fields[0]}'";
                    $qresult = db_execute_assoc($query);
                    foreach ($qresult->result_array() as $row) {$other=$row['other'];}
                    if($other == "Y")
                    {
                        if($_SESSION[$fields[1].'other'] != "") {array_push($selected, "other");}
                    }
                }
            }
        }
        if(count($selected) > 0)
        {
            $cache[$qid] = $selected;
            return $cache[$qid];
        } else {
            $cache[$qid] = false;
            return $cache[$qid];
        }
    }
    $cache[$qid] = false;
    return $cache[$qid];
}

/**
* Unsets all Session variables to kill session
*/
function killSession()  //added by Dennis
{
    $CI = &get_instance();
    $CI->session->sess_destroy();
}

function CSVEscape($str)
{
    $str= str_replace('\n','\%n',$str);
    return '"' . str_replace('"','""', $str) . '"';
}

function convertCSVRowToArray($string, $seperator, $quotechar)
{
    $fields=preg_split('/' . $seperator . '(?=([^"]*"[^"]*")*(?![^"]*"))/',trim($string));
    $fields=array_map('CSVUnquote',$fields);
    return $fields;
}

function createPassword()
{
    $pwchars = "abcdefhjmnpqrstuvwxyz23456789";
    $password_length = 8;
    $passwd = '';

    for ($i=0; $i<$password_length; $i++)
    {
        $passwd .= $pwchars[floor(rand(0,strlen($pwchars)-1))];
    }
    return $passwd;
}

function languageDropdown($surveyid,$selected)
{
    $CI = &get_instance();
    $homeurl = Yii::app()->getConfig('homeurl');
    $slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    array_unshift($slangs,$baselang);
    $html = "<select class='listboxquestions' name='langselect' onchange=\"window.open(this.options[this.selectedIndex].value, '_top')\">\n";

    foreach ($slangs as $lang)
    {
        $link = site_url("admin/dataentry/view/".$surveyid."/".$lang);
        if ($lang == $selected) $html .= "\t<option value='{$link}' selected='selected'>".getLanguageNameFromCode($lang,false)."</option>\n";
        if ($lang != $selected) $html .= "\t<option value='{$link}'>".getLanguageNameFromCode($lang,false)."</option>\n";
    }
    $html .= "</select>";
    return $html;
}

function languageDropdownClean($surveyid,$selected)
{
    $slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    array_unshift($slangs,$baselang);
    $html = "<select class='listboxquestions' id='language' name='language'>\n";
    foreach ($slangs as $lang)
    {
        if ($lang == $selected) $html .= "\t<option value='$lang' selected='selected'>".getLanguageNameFromCode($lang,false)."</option>\n";
        if ($lang != $selected) $html .= "\t<option value='$lang'>".getLanguageNameFromCode($lang,false)."</option>\n";
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

function incompleteAnsFilterstate()
{
    global $filterout_incomplete_answers;
    $letsfilter='';
    $letsfilter = returnglobal('filterinc'); //read get/post filterinc


    // first let's initialize the incompleteanswers session variable
    if ($letsfilter != '')
    { // use the read value if not empty
        Yii::app()->session['incompleteanswers'] = $letsfilter;
    }
    elseif (empty(Yii::app()->session['incompleteanswers']))
    { // sets default variable value from config file
        Yii::app()->session['incompleteanswers'] = $filterout_incomplete_answers;
    }

    if  (Yii::app()->session['incompleteanswers']=='filter') {
        return "filter"; //COMPLETE ANSWERS ONLY
    }
    elseif (Yii::app()->session['incompleteanswers']=='show') {
        return false; //ALL ANSWERS
    }
    elseif (Yii::app()->session['incompleteanswers']=='incomplete') {
        return "inc"; //INCOMPLETE ANSWERS ONLY
    }
    else
    { // last resort is to prevent filtering
        return false;
    }
}


/**
* captcha_enabled($screen, $usecaptchamode)
* @param string $screen - the screen name for which to test captcha activation
*
* @return boolean - returns true if captcha must be enabled
**/
function captcha_enabled($screen, $captchamode='')
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
function convertCsvreturn2return($string)
{
    $string= str_replace('\n', "\n", $string);
    return str_replace('\%n', '\n', $string);
}

/**
* Check if a table does exist in the database
*
* @param mixed $sid  Table name to check for (without dbprefix!))
* @return boolean True or false if table exists or not
*/
function tableExists($tablename)
{
    
    return Yii::app()->db->schema->getTable($tablename);
}

// Returns false if the survey is anonymous,
// and a token table exists: in this case the completed field of a token
// will contain 'Y' instead of the submitted date to ensure privacy
// Returns true otherwise
function bIsTokenCompletedDatestamped($thesurvey)
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
function date_shift($date, $dformat, $shift)
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

// Check if user has manage rights for a template
function hasTemplateManageRights($userid, $templatefolder) {
    $CI = &get_instance();
    $userid=sanitize_int($userid);
    $templatefolder=sanitize_paranoid_string($templatefolder);
    //$query = "SELECT ".db_quote_id('use')." FROM {$dbprefix}templates_rights WHERE uid=".$userid." AND folder LIKE '".$templatefolder."'";

    //$result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());  //Safe

    $CI->load->model('Templates_rights_model');
    $query=$CI->Templates_rights_model->getSomeRecords("use","uid=".$userid." AND folder LIKE '".$templatefolder."'");

    //if ($result->RecordCount() == 0)  return false;
    if ($query->num_rows() == 0)  return false;

    $row = $query->row_array();
    //$row = $result->FetchRow();

    return $row["use"];
}

/**
* This function creates an incrementing answer code based on the previous source-code
*
* @param mixed $sourcecode The previous answer code
*/
function getNextCode($sourcecode)
{
    $i=1;
    $found=true;
    $foundnumber=-1;
    while ($i<=strlen($sourcecode) && $found)
    {
        $found=is_numeric(substr($sourcecode,-$i));
        if ($found)
        {
            $foundnumber=substr($sourcecode,-$i);
            $i++;
        }
    }
    if ($foundnumber==-1)
    {
        return($sourcecode);
    }
    else
    {
        $foundnumber++;
        $result=substr($sourcecode,0,strlen($sourcecode)-strlen($foundnumber)).$foundnumber;
        return($result);
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
function translink($sType, $iOldSurveyID, $iNewSurveyID, $sString)
{
    if ($sType == 'survey')
    {
        $sPattern = "([^'\"]*)/upload/surveys/{$iOldSurveyID}/";
        $sReplace = Yii::app()->getConfig("relativeurl")."/upload/surveys/{$iNewSurveyID}/";
        return preg_replace('#'.$sPattern.'#', $sReplace, $sString);
    }
    elseif ($sType == 'label')
    {
        $pattern = "([^'\"]*)/upload/labels/{$iOldSurveyID}/";
        $replace = Yii::app()->getConfig("relativeurl")."/upload/labels/{$iNewSurveyID}/";
        return preg_replace('#'.$pattern.'#', $replace, $sString);
    }
    else // unkown type
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
function aReverseTranslateFieldnames($iOldSID,$iNewSID,$aGIDReplacements,$aQIDReplacements)
{
    $aGIDReplacements=array_flip($aGIDReplacements);
    $aQIDReplacements=array_flip($aQIDReplacements);
    $aFieldMap=createFieldMap($iNewSID);

    $aFieldMappings=array();
    foreach ($aFieldMap as $sFieldname=>$aFieldinfo)
    {
        if ($aFieldinfo['qid']!=null)
        {
            $aFieldMappings[$sFieldname]=$iOldSID.'X'.$aGIDReplacements[$aFieldinfo['gid']].'X'.$aQIDReplacements[$aFieldinfo['qid']].$aFieldinfo['aid'];
            // now also add a shortened field mapping which is needed for certain kind of condition mappings
            $aFieldMappings[$iNewSID.'X'.$aFieldinfo['gid'].'X'.$aFieldinfo['qid']]=$iOldSID.'X'.$aGIDReplacements[$aFieldinfo['gid']].'X'.$aQIDReplacements[$aFieldinfo['qid']];
            // Shortened field mapping for timings table
            $aFieldMappings[$iNewSID.'X'.$aFieldinfo['gid']]=$iOldSID.'X'.$aGIDReplacements[$aFieldinfo['gid']];
        }
    }
    return array_flip($aFieldMappings);
}

/**
* put your comment there...
*
* @param mixed $id
* @param mixed $type
*/
function hasResources($id,$type='survey')
{
    $CI = &get_instance();
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
function sRandomChars($length,$pattern="23456789abcdefghijkmnpqrstuvwxyz")
{
    $patternlength = strlen($pattern)-1;
    for($i=0;$i<$length;$i++)
    {
        if(isset($key))
            $key .= $pattern{rand(0,$patternlength)};
        else
            $key = $pattern{rand(0,$patternlength)};
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
function conditional_nl2br($mytext,$ishtml,$encoded='')
{
    if ($ishtml === true)
    {
        // $mytext has been processed by clang->gT with html mode
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

function conditional2_nl2br($mytext,$ishtml)
{
    if ($ishtml === true)
    {
        return str_replace("\n", '<br />',$mytext);
    }
    else
    {
        return $mytext;
    }
}

function br2nl( $data ) {
    return preg_replace( '!<br.*>!iU', "\n", $data );
}

function safe_die($text)
{
    //Only allowed tag: <br />
    $textarray=explode('<br />',$text);
    $textarray=array_map('htmlspecialchars',$textarray);
    die(implode( '<br />',$textarray));
}

function fix_FCKeditor_text($str)
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

function recursive_stripslashes($array_or_string)
{
    if (is_array($array_or_string))
    {
        return array_map('recursive_stripslashes', $array_or_string);
    }
    else
    {
        return stripslashes($array_or_string);
    }
}

/**
* This is a helper function for GetAttributeFieldNames
*
* @param mixed $fieldname
*/
function filterforattributes ($fieldname)
{
    if (strpos($fieldname,'attribute_')===false) return false; else return true;
}

/**
* Retrieves the attribute field names from the related token table
*
* @param mixed $surveyid  The survey ID
* @return array The fieldnames
*/
function GetAttributeFieldNames($surveyid)
{
    if (($table = Yii::app()->db->schema->getTable('{{tokens_'.$surveyid . '}}')) === false)
        return Array();

    return array_filter(array_keys($table->columns), 'filterforattributes');
    }

/**
* Retrieves the token field names usable for conditions from the related token table
*
* @param mixed $surveyid  The survey ID
* @return array The fieldnames
*/
function GetTokenConditionsFieldNames($surveyid)
{
    $extra_attrs=GetAttributeFieldNames($surveyid);
    $basic_attrs=Array('firstname','lastname','email','token','language','sent','remindersent','remindercount');
    return array_merge($basic_attrs,$extra_attrs);
}

/**
* Retrieves the attribute names from the related token table
*
* @param mixed $surveyid  The survey ID
* @param boolean $onlyAttributes Set this to true if you only want the fieldnames of the additional attribue fields - defaults to false
* @return array The fieldnames as key and names as value in an Array
*/
function GetTokenFieldsAndNames($surveyid, $onlyAttributes=false)
{
    $clang = Yii::app()->lang;
    if (!Yii::app()->db->schema->getTable("{{token_$surveyid}}"))
    {
        return Array();
    }
    $extra_attrs=GetAttributeFieldNames($surveyid);
    $basic_attrs=Array('firstname','lastname','email','token','language','sent','remindersent','remindercount','usesleft');
    $basic_attrs_names=Array(
    $clang->gT('First name'),
    $clang->gT('Last name'),
    $clang->gT('Email address'),
    $clang->gT('Token code'),
    $clang->gT('Language code'),
    $clang->gT('Invitation sent date'),
    $clang->gT('Last Reminder sent date'),
    $clang->gT('Total numbers of sent reminders'),
    $clang->gT('Uses left')
    );

    $thissurvey=getSurveyInfo($surveyid);
    $attdescriptiondata=array();
    if (!empty($thissurvey['attributedescriptions']))
    {
        $attdescriptiondata=explode("\n",$thissurvey['attributedescriptions']);
    }
    $attributedescriptions=array();
    $basic_attrs_and_names=array();
    $extra_attrs_and_names=array();
    foreach ($attdescriptiondata as $attdescription)
    {
        $attributedescriptions['attribute_'.substr($attdescription,10,strpos($attdescription,'=')-10)] = substr($attdescription,strpos($attdescription,'=')+1);
    }
    foreach ($extra_attrs as $fieldname)
    {
        if (isset($attributedescriptions[$fieldname]))
        {
            $extra_attrs_and_names[$fieldname]=$attributedescriptions[$fieldname];
        }
        else
        {
            $extra_attrs_and_names[$fieldname]=sprintf($clang->gT('Attribute %s'),substr($fieldname,10));
        }
    }
    if ($onlyAttributes===false)
    {
        $basic_attrs_and_names=array_combine($basic_attrs,$basic_attrs_names);
        return array_merge($basic_attrs_and_names,$extra_attrs_and_names);
    }
    else
    {
        return $extra_attrs_and_names;
    }
}

/**
* Retrieves the token attribute value from the related token table
*
* @param mixed $surveyid  The survey ID
* @param mixed $attrName  The token-attribute field name
* @param mixed $token  The token code
* @return string The token attribute value (or null on error)
*/
function GetAttributeValue($surveyid,$attrName,$token)
{
    $CI = &get_instance();
    $attrName=strtolower($attrName);
    if (!tableExists('tokens_'.$surveyid) || !in_array($attrName,GetTokenConditionsFieldNames($surveyid)))
    {
        return null;
    }
    //$sanitized_token=$connect->qstr($token,get_magic_quotes_gpc());
    $surveyid=sanitize_int($surveyid);

    //$query="SELECT $attrName FROM {$dbprefix}tokens_$surveyid WHERE token=$sanitized_token";
    $CI->load->model('Tokens_dynamic_model');
    $query=$CI->Tokens_dynamic_model->getAllRecords($attrName, $surveyid, array("token"=>$token));

    //$result=db_execute_num($query);
    $count=$query->num_rows(); //$result->RecordCount();
    if ($count != 1)
    {
        return null;
    }
    else
    {
        $row=$query->row_array();//$result->FetchRow();
        return $row[$attrName];//[0]
    }
}

/**
* This function strips any content between and including <style>  & <javascript> tags
*
* @param string $content String to clean
* @return string  Cleaned string
*/
function strip_javascript($content){
    $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
    '@<style[^>]*?>.*?</style>@siU'    // Strip style tags properly
    /*               ,'@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
    '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA
    */
    );
    $text = preg_replace($search, '', $content);
    return $text;
}

/**
* This function cleans files from the temporary directory being older than 1 day
* @todo Make the days configurable
*/
function cleanTempDirectory()
{
    $dir =  Yii::app()->getConfig('tempdir').'/';
    $dp = opendir($dir) or show_error('Could not open temporary directory');
    while ($file = readdir($dp)) {
        if (is_file($dir.$file) && (filemtime($dir.$file)) < (strtotime('-1 days')) && $file!='index.html' && $file!='readme.txt' && $file!='..' && $file!='.' && $file!='.svn') {
            @unlink($dir.$file);
        }
    }
    $dir=  Yii::app()->getConfig('tempdir').'/uploads/';
    $dp = opendir($dir) or die ('Could not open temporary directory');
    while ($file = readdir($dp)) {
        if (is_file($dir.$file) && (filemtime($dir.$file)) < (strtotime('-1 days')) && $file!='index.html' && $file!='readme.txt' && $file!='..' && $file!='.' && $file!='.svn') {
            @unlink($dir.$file);
        }
    }
    closedir($dp);
}

function use_firebug()
{
    if(FIREBUG == true)
    {
        return '<script type="text/javascript" src="http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js"></script>';
    };
};

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
    $date = new Date_Time_Converter(array($value, $fromdateformat));
    return $date->convert($todateformat);
}

/**
* This function removes the UTF-8 Byte Order Mark from a string
*
* @param string $str
* @return string
*/
function removeBOM($str=""){
    if(substr($str, 0,3) == pack("CCC",0xef,0xbb,0xbf)) {
        $str=substr($str, 3);
    }
    return $str;
}

/**
* This function requests the latest update information from the LimeSurvey.org website
*
* @returns array Contains update information or false if the request failed for some reason
*/
function GetUpdateInfo()
{
    //require_once($homedir."/classes/http/http.php");
    $CI =& get_instance();
    $CI->load->library('admin/http/http','http');

    /* Connection timeout */
    $CI->http->timeout=0;
    /* Data transfer timeout */
    $CI->http->data_timeout=0;
    $CI->http->user_agent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
    $CI->http->GetRequestArguments("http://update.limesurvey.org?build=".Yii::app()->getConfig("buildnumber"),$arguments);

    $updateinfo=false;
    $error=$CI->http->Open($arguments);
    $error=$CI->http->SendRequest($arguments);

    $CI->http->ReadReplyHeaders($headers);


    if($error=="") {
        $body=''; $full_body='';
        for(;;){
            $error = $CI->http->ReadReplyBody($body,10000);
            if($error != "" || strlen($body)==0) break;
            $full_body .= $body;
        }
        $updateinfo=json_decode($full_body,true);
        if ($CI->http->response_status!='200')
        {
            $updateinfo['errorcode']=$CI->http->response_status;
            $updateinfo['errorhtml']=$full_body;
        }
    }
    else
    {
        $updateinfo['errorcode']=$error;
        $updateinfo['errorhtml']=$error;
    }
    unset( $CI->http );
    return $updateinfo;
}

/**
* This function updates the actual global variables if an update is available after using GetUpdateInfo
* @return Array with update or error information
*/
function updatecheck()
{
    $CI =& get_instance();
    $updateinfo=GetUpdateInfo();
    if (isset($updateinfo['Targetversion']['build']) && (int)$updateinfo['Targetversion']['build']>(int)Yii::app()->getConfig('buildnumber') && trim(Yii::app()->getConfig('buildnumber'))!='')
    {
        setGlobalSetting('updateavailable',1);
        setGlobalSetting('updatebuild',$updateinfo['Targetversion']['build']);
        setGlobalSetting('updateversion',$updateinfo['Targetversion']['versionnumber']);
    }
    else
    {
        setGlobalSetting('updateavailable',0);
    }
    setGlobalSetting('updatelastcheck',date('Y-m-d H:i:s'));
    return $updateinfo;
}

/**
* Return the goodchars to be used when filtering input for numbers.
*
* @param $lang 	string	language used, for localisation
* @param $integer	bool	use only integer
* @param $negative	bool	allow negative values
*/
function getNumericalFormat($lang = 'en', $integer = false, $negative = true) {
    $goodchars = "0123456789";
    if ($integer === false) $goodchars .= ".";    //Todo, add localisation
    if ($negative === true) $goodchars .= "-";    //Todo, check databases
    return $goodchars;
}

function getTokenData($surveyid, $token)
{
    $CI = &get_instance();
    //$query = "SELECT * FROM ".db_table_name('tokens_'.$surveyid)." WHERE token='".db_quote($token)."'";
    //$result = db_execute_assoc($query) or safe_die("Couldn't get token info in getTokenData()<br />".$query."<br />".$connect->ErrorMsg());    //Checked
    $CI->load->model('Tokens_dynamic_model');
    $query=$CI->Tokens_dynamic_model->getAllRecords($surveyid, array("token"=>$token));

    // while($row=$result->FetchRow())
    foreach ($query->result_array() as $row)
    {
        $thistoken=array("firstname"=>$row['firstname'],
        "lastname"=>$row['lastname'],
        "email"=>$row['email'],
        "language" =>$row['language']);
        $attrfieldnames=GetAttributeFieldnames($surveyid);
        foreach ($attrfieldnames as $attr_name)
        {
            $thistoken[$attr_name]=$row[$attr_name];
        }
    } // while
    return $thistoken;
}

/**
* This function returns the complete directory path to a given template name
*
* @param mixed $sTemplateName
*/
function sGetTemplatePath($sTemplateName)
{
    if (isStandardTemplate($sTemplateName))
    {
        return Yii::app()->getConfig("standardtemplaterootdir").'/'.$sTemplateName;
    }
    else
    {
        if (file_exists(Yii::app()->getConfig("usertemplaterootdir").'/'.$sTemplateName))
        {
            return Yii::app()->getConfig("usertemplaterootdir").'/'.$sTemplateName;
        }
        elseif (file_exists(Yii::app()->getConfig("usertemplaterootdir").'/'.Yii::app()->getConfig('defaulttemplate')))
        {
            return Yii::app()->getConfig("usertemplaterootdir").'/'.Yii::app()->getConfig('defaulttemplate');
        }
        elseif (file_exists(Yii::app()->getConfig("standardtemplaterootdir").'/'.Yii::app()->getConfig('defaulttemplate')))
        {
            return Yii::app()->getConfig("standardtemplaterootdir").'/'.Yii::app()->getConfig('defaulttemplate');
        }
        else
        {

            return $Yii::app()->getConfig("standardtemplaterootdir").'/default';
        }
    }
}

/**
* This function returns the complete URL path to a given template name
*
* @param mixed $sTemplateName
*/
function sGetTemplateURL($sTemplateName)
{
    if (isStandardTemplate($sTemplateName))
    {
        return Yii::app()->getConfig("standardtemplaterooturl").'/'.$sTemplateName;
    }
    else
    {
        if (file_exists(Yii::app()->getConfig("usertemplaterootdir").'/'.$sTemplateName))
        {
            return Yii::app()->getConfig("usertemplaterooturl").'/'.$sTemplateName;
        }
        elseif (file_exists(Yii::app()->getConfig("usertemplaterootdir").'/'.Yii::app()->getConfig('defaulttemplate')))
        {
            return Yii::app()->getConfig("usertemplaterooturl").'/'.Yii::app()->getConfig('defaulttemplate');
        }
        elseif (file_exists(Yii::app()->getConfig("standardtemplaterootdir").'/'.Yii::app()->getConfig('defaulttemplate')))
        {
            return Yii::app()->getConfig("standardtemplaterooturl").'/'.Yii::app()->getConfig('defaulttemplate');
        }
        else
        {
            return Yii::app()->getConfig("standardtemplaterooturl").'/default';
        }
    }
}

/**
* Return an array of subquestions for a given sid/qid
*
* @param int $sid
* @param int $qid
* @param $sLanguage Language of the subquestion text
*/
function getSubQuestions($sid, $qid, $sLanguage) {

    $clang = Yii::app()->lang;
    static $subquestions;

    if (!isset($subquestions[$sid])) {

    	$query = "SELECT sq.*, q.other FROM {{questions}} as sq, {{questions}} as q"
    		." WHERE sq.parent_qid=q.qid AND q.sid=".$sid
	    	." AND sq.language='".$sLanguage. "' "
	    	." AND q.language='".$sLanguage. "' "
    		." ORDER BY sq.parent_qid, q.question_order,sq.scale_id , sq.question_order";

        $query = Yii::app()->db->createCommand($query)->query();

        $resultset=array();
        //while ($row=$result->FetchRow())
        foreach ($query->readAll() as $row)
        {
            $resultset[$row['parent_qid']][] = $row;
        }
        $subquestions[$sid] = $resultset;
    }
    if (isset($subquestions[$sid][$qid])) return $subquestions[$sid][$qid];
    return array();
}

/**
* Wrapper function to retrieve an xmlwriter object and do error handling if it is not compiled
* into PHP
*/
function getXMLWriter() {
    if (!extension_loaded('xmlwriter')) {
        safe_die('XMLWriter class not compiled into PHP, please contact your system administrator');
    } else {
        $xmlwriter = new XMLWriter();
    }
    return $xmlwriter;
}

/*
* Return a sql statement for renaming a table
*/
function db_rename_table($oldtable, $newtable)
{
    $CI = &get_instance();

    //$dict = NewDataDictionary($connect);
    //$result=$dict->RenameTableSQL($oldtable, $newtable);
    //return $result[0];

    $CI->load->dbforge();

    return $CI->dbforge->rename_table($oldtable, $newtable);
}

/**
* Returns true when a token can not be used (either doesn't exist or has less then one usage left
*
* @param mixed $tid Token
*/
function usedTokens($token, $surveyid)
{
    $CI = &get_instance();

    $utresult = true;
    $CI->load->model('Tokens_dynamic_model');
    $query=$CI->Tokens_dynamic_model->getSomeRecords(array("tid, usesleft"), $surveyid, array("token"=>$token));

    //$query = "SELECT tid, usesleft from {$dbprefix}tokens_$surveyid WHERE token=".db_quoteall($token);
    //$result=db_execute_assoc($query,null,true);

    if ($query->num_rows() > 0) {
        $row = $query->row_array();
        if ($row['usesleft']>0) $utresult = false;
    }
    return $utresult;
}

/**
* SSL_redirect() generates a redirect URL for the appropriate SSL mode then applies it.
* (Was redirect() before CodeIgniter port.)
*
* @param $ssl_mode string 's' or '' (empty).
*/
function SSL_redirect($ssl_mode)
{
    $url = 'http'.$ssl_mode.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    if (!headers_sent())
    {	// If headers not sent yet... then do php redirect
        //ob_clean();
        header('Location: '.$url);
        //ob_flush();
        exit;
    };
};

/**
* SSL_mode() $force_ssl is on or off, it checks if the current
* request is to HTTPS (or not). If $force_ssl is on, and the
* request is not to HTTPS, it redirects the request to the HTTPS
* version of the URL, if the request is to HTTPS, it rewrites all
* the URL variables so they also point to HTTPS.
*/
function SSL_mode()
{
    $https = isset($_SERVER['HTTPS'])?$_SERVER['HTTPS']:'';
    if (Yii::app()->getConfig('ssl_emergency_override') !== true )
    {
        $force_ssl = strtolower(getGlobalSetting('force_ssl'));
    }
    else
    {
        $force_ssl = 'off';
    };
    if( $force_ssl == 'on' && $https == '' )
    {
        SSL_redirect('s');
    }
    if( $force_ssl == 'off' && $https != '')
    {
        SSL_redirect('');
    };
};

/**
* get_quotaCompletedCount() returns the number of answers matching the quota
* @param string $surveyid - Survey identification number
* @param string $quotaid - quota id for which you want to compute the completed field
* @return string - number of mathing entries in the result DB or 'N/A'
*/
function get_quotaCompletedCount($surveyid, $quotaid)
{

    $result ="N/A";
    $quota_info = getQuotaInformation($surveyid,GetBaseLanguageFromSurveyID($surveyid),$quotaid);
    $quota = $quota_info[0];

	if (Yii::app()->db->schema->getTable('{{survey_' . $surveyid . '}}') &&
    count($quota['members']) > 0)
    {
        $fields_list = array(); // Keep a list of fields for easy reference
        // construct an array of value for each $quota['members']['fieldnames']
        unset($querycond);
        $fields_query = array();
        foreach($quota['members'] as $member)
        {
            foreach($member['fieldnames'] as $fieldname)
            {
                if (!in_array($fieldname,$fields_list)){
                    $fields_list[] = $fieldname;
                    $fields_query[$fieldname] = array();
                }
                $fields_query[$fieldname][]= $fieldname." = '{$member['value']}'";
            }
        }

        foreach($fields_list as $fieldname)
        {
            $select_query = " ( ".implode(' OR ',$fields_query[$fieldname]).' )';
            $querycond[] = $select_query;
        }

        //$querysel = "SELECT count(id) as count FROM ".db_table_name('survey_'.$surveyid)." WHERE ".implode(' AND ',$querycond)." "." AND submitdate IS NOT NULL";
        //$result = db_execute_assoc($querysel) or safe_die($connect->ErrorMsg()); //Checked
        //$quota_check = $result->FetchRow();

        $query = Survey_dynamic::model($sid)->findAll(implode('', $querycond));
        $result = count($query);
        //$result = $quota_check['count'];
    }

    return $result;
}

/**
* Creates an array with details on a particular response for display purposes
* Used in Print answers (done), Detailed response view (Todo:)and Detailed admin notification email (done)
*
* @param mixed $iSurveyID
* @param mixed $iResponseID
* @param mixed $sLanguageCode
* @param boolean $bHonorConditions Apply conditions
*/
function aGetFullResponseTable($iSurveyID, $iResponseID, $sLanguageCode, $bHonorConditions=false)
{
    $CI = &get_instance();
    $aFieldMap = createFieldMap($iSurveyID,'full',false,false,$sLanguageCode);
    //Get response data
    $CI->load->model('surveys_dynamic_model');
    $idquery = $CI->surveys_dynamic_model->getAllRecords($iSurveyID, array('id'=>$iResponseID));
    $idrow = $idquery->row_array();
    //$idquery = "SELECT * FROM ".db_table_name('survey_'.$iSurveyID)." WHERE id=".$iResponseID;
    //$idrow=$connect->GetRow($idquery) or safe_die ("Couldn't get entry<br />\n$idquery<br />\n".$connect->ErrorMsg()); //Checked

    $aResultTable=array();

    // Filter out irrelevant questions
    $relevanceStatus = $_SESSION['relevanceStatus'];

    $oldgid = 0;
    $oldqid = 0;
    foreach ($aFieldMap as $sKey=>$fname)
    {
        $question = $fname['question'];
        $subquestion='';
        if (isset($fname['gid']) && !empty($fname['gid'])) {
            //Check to see if gid is the same as before. if not show group name
            if ($oldgid !== $fname['gid'])
            {
                $oldgid = $fname['gid'];
                if (checkgroupfordisplay($fname['gid']) || !$bHonorConditions)
                {
                    $aResultTable['gid_'.$fname['gid']]=array($fname['group_name']);
                }
            }
        }
        if (isset($fname['qid']))
        {
            $qid = $fname['qid'];
            if (isset($relevanceStatus[$qid]) && $relevanceStatus[$qid] != 1) {
                continue;   // skip irrelevant questions
            }
            if (isset($fname['qid']) && !empty($fname['qid']))
            {
                if ($bHonorConditions)
                {
                     $bQuestionVisible=checkquestionfordisplay($fname['qid'],null);
                }
                else
                {
                     $bQuestionVisible=true;
                }
                if ($oldqid !== $fname['qid'])
                {
                    $oldqid = $fname['qid'];
                    if ($bQuestionVisible)
                    {

                        if (isset($fname['subquestion']) || isset($fname['subquestion1']) || isset($fname['subquestion2']))
                        {
                            $aResultTable['qid_'.$fname['sid'].'X'.$fname['gid'].'X'.$fname['qid']]=array($fname['question'],'','');
                        }
                        else
                        {
                            $answer=getextendedanswer($iSurveyID, NULL,$fname['fieldname'], $idrow[$fname['fieldname']]);
                            $aResultTable[$fname['fieldname']]=array($question,'',$answer);
                            continue;
                        }

                    }
                    else
                    {
                        continue;
                    }
                }
            }
            else
            {
                $answer=getextendedanswer($iSurveyID, NULL,$fname['fieldname'], $idrow[$fname['fieldname']]);
                $aResultTable[$fname['fieldname']]=array($question,'',$answer);
                continue;
            }
        }
        if (isset($fname['subquestion']))
            $subquestion = "{$fname['subquestion']}";

        if (isset($fname['subquestion1']))
            $subquestion = "{$fname['subquestion1']}";

        if (isset($fname['subquestion2']))
            $subquestion .= "[{$fname['subquestion2']}]";
        if ($bQuestionVisible)
        {
            $answer=getextendedanswer($iSurveyID, NULL,$fname['fieldname'], $idrow[$fname['fieldname']]);
            $aResultTable[$fname['fieldname']]=array('',$subquestion,$answer);
        }
    }
    return $aResultTable;
}

/**
* Check if $str is an integer, or string representation of an integer
*
* @param mixed $mStr
*/
function bIsNumericInt($mStr)
{
    if(is_int($mStr))
        return true;
    elseif(is_string($mStr))
        return preg_match("/^[0-9]+$/", $mStr);
    return false;
}

/**
* Invert key/values of an associative array, preserving multiple values in
* the source array as a single key with multiple values in the resulting
* array.
*
* This is not the same as array_flip(), which flattens the structure of the
* source array.
*
* @param array $aArr
*/
function aArrayInvert($aArr)
{
    $aRet = array();
    foreach($aArr as $k => $v)
        $aRet[$v][] = $k;
    return $aRet;
}

/**
* Check if a question was (at least partially) answered in the current session.
*
* @param integer $q - Question id
* @param array $aFieldnamesInfoInv - Inverted fieldnamesInfo
*/
function bCheckQuestionForAnswer($q, $aFieldnamesInfoInv)
{
    $CI =& get_instance();

    $qtype = $_SESSION['fieldmap'][$aFieldnamesInfoInv[$q][0]]['type'];

    switch ($qtype) {
        case 'X':
            return true;
        case 'M':
        case 'P':
        case 'O':
            // multiple choice and list with comments question types - just one answer is required and comments are not required
            foreach($aFieldnamesInfoInv[$q] as $sField)
                if(!strstr($sField, 'comment') && isset($_SESSION[$sField]) && trim($_SESSION[$sField])!='')
                    return true;
                return false;
        case 'L': // List questions only need one answer (including the 'other' option)
            foreach($aFieldnamesInfoInv[$q] as $sField)
            {
                if(isset($_SESSION[$sField]) && trim($_SESSION[$sField])!='')
                    return true;
            }
            return false;
        case 'F':
        case ':':
        case ';':
        case '1':
        case 'C':
        case 'B':
        case 'A':
        case 'E':
            // array question types - if filtered only displayed answer are required
            $qattr = getQuestionAttributeValues(@$_SESSION['fieldmap'][$aFieldnamesInfoInv[$q][0]]['qid'], $qtype);

            $qcodefilter = @$qattr['array_filter'];

            $sgqfilter = '';

            foreach($_SESSION['fieldarray'] as $field)
                //look for the multiple choice filter
                if ($field[2] == $qcodefilter && $field[4] == 'M')
                {
                    //filter SQG
                    $sgqfilter = $field[1];
                    break;
                }

                //if filter not found checkall answers
                if ($sgqfilter == '')
            {
                // all answers required
                foreach($aFieldnamesInfoInv[$q] as $sField)
                    if(!isset($_SESSION[$sField]) || trim($_SESSION[$sField])=='')
                        return false;
                    return true;
            }

            foreach($aFieldnamesInfoInv[$q] as $sField)
            {
                //keep only first subquestion code for multiple scale answer
                $aid = explode('_',$_SESSION['fieldmap'][$sField]['aid']);
                $aid = explode('#',$aid[0]);
                //if a checked answer in the multiple choice is not present
                if (!isset($_SESSION[$sgqfilter.$aid[0]])) {
                    return false;
                }
                if (!isset($_SESSION[$sField]))
                {
                    return false;
                }
                if ($_SESSION[$sgqfilter.$aid[0]] == 'Y' && $_SESSION[$sField] == '')
                    return false;
            }
            return true;
        default:
            // all answers required for all other question types
            foreach($aFieldnamesInfoInv[$q] as $sField)
                if(!isset($_SESSION[$sField]) || trim($_SESSION[$sField])=='')
                    return false;
                return true;
    }
}
/**
* Include Keypad headers
*/
function vIncludeKeypad()
{
    global $js_header_includes, $css_header_includes, $clang;

    $js_header_includes[] = '/scripts/jquery/jquery.keypad.min.js';
    if ($clang->langcode !== 'en')
    {
        $js_header_includes[] = '/scripts/jquery/locale/jquery.ui.keypad-'.$clang->langcode.'.js';
    }
    $css_header_includes[] = '/scripts/jquery/css/jquery.keypad.alt.css';
}

/**
* getQuotaInformation() returns quota information for the current survey
* @param string $surveyid - Survey identification number
* @param string $quotaid - Optional quotaid that restricts the result to a given quota
* @return array - nested array, Quotas->Members->Fields
*/
function getQuotaInformation($surveyid,$language,$quotaid='all')
{
    global $clienttoken;
    $baselang = GetBaseLanguageFromSurveyID($surveyid);

    /*$query = "SELECT * FROM ".db_table_name('quota').", ".db_table_name('quota_languagesettings')."
    WHERE ".db_table_name('quota').".id = ".db_table_name('quota_languagesettings').".quotals_quota_id
    AND sid='{$surveyid}'
    AND quotals_language='".$language."'";
    if ($quotaid != 'all')
    {
    $query .= " AND id=$quotaid";
    }

    $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg());    //Checked
    */

    $result = Quota::model()->with(array('languagesettings' => array('condition' => "quotals_language='$language'")))->findAllByAttributes(array('sid' => $surveyid, 'id' =>$quotaid));
    $quota_info = array();
    $x=0;

    $surveyinfo=getSurveyInfo($surveyid);

    // Check all quotas for the current survey
    //if ($result->RecordCount() > 0)
    if (count($result) > 0)
    {
        //while ($survey_quotas = $result->FetchRow())
        foreach ($result as $_survey_quotas)
        {
        	$survey_quotas = $_survey_quotas->attributes;
        	// !!! Doubting this
        	foreach ($_survey_quotas->languagesettings[0] as $k => $v)
        		$survey_quotas[$k] = $v;

            //Modify the URL - thanks janokary
            $survey_quotas['quotals_url']=str_replace("{SAVEDID}",!empty(Yii::app()->session['srid']) ? Yii::app()->session['srid'] : '', $survey_quotas['quotals_url']);
            $survey_quotas['quotals_url']=str_replace("{SID}", $surveyid, $survey_quotas['quotals_url']);
            $survey_quotas['quotals_url']=str_replace("{LANG}", Yii::app()->lang->getlangcode(), $survey_quotas['quotals_url']);
            $survey_quotas['quotals_url']=str_replace("{TOKEN}",$clienttoken, $survey_quotas['quotals_url']);

            array_push($quota_info,array('Name' => $survey_quotas['name'],
            'Limit' => $survey_quotas['qlimit'],
            'Action' => $survey_quotas['action'],
            'Message' => $survey_quotas['quotals_message'],
            'Url' => $survey_quotas['quotals_url'],
            'UrlDescrip' => $survey_quotas['quotals_urldescrip'],
            'AutoloadUrl' => $survey_quotas['autoload_url']));
            //$query = "SELECT * FROM ".db_table_name('quota_members')." WHERE quota_id='{$survey_quotas['id']}'";
            //$result_qe = db_execute_assoc($query) or safe_die($connect->ErrorMsg());      //Checked
            $result_qe = Quota_members::model()->findAllByAttributes(array('quota_id'=>$survey_quotas['id']));
            $quota_info[$x]['members'] = array();
            if (count($result_qe) > 0)
            //if ($result_qe->RecordCount() > 0)
            {
                //while ($quota_entry = $result_qe->FetchRow())
                foreach ($result_qe as $quota_entry)
                {
                	$quota_entry = $quota_entry->attributes;
                    //$query = "SELECT type, title,gid FROM ".db_table_name('questions')." WHERE qid='{$quota_entry['qid']}' AND language='{$baselang}'";
                    //$result_quest = db_execute_assoc($query) or safe_die($connect->ErrorMsg());     //Checked
                    //$qtype = $result_quest->FetchRow();
                    $result_quest=Questions::model()->findByAttributes(array('qid'=>$quota_entry['qid'], 'language'=>$baselang));
                    $qtype=$result_quest->attributes;

                    $fieldnames = "0";

                    if ($qtype['type'] == "I" || $qtype['type'] == "G" || $qtype['type'] == "Y")
                    {
                        $fieldnames=array(0 => $surveyid.'X'.$qtype['gid'].'X'.$quota_entry['qid']);
                        $value = $quota_entry['code'];
                    }

                    if($qtype['type'] == "L" || $qtype['type'] == "O" || $qtype['type'] =="!")
                    {
                        $fieldnames=array(0 => $surveyid.'X'.$qtype['gid'].'X'.$quota_entry['qid']);
                        $value = $quota_entry['code'];
                    }

                    if($qtype['type'] == "M")
                    {
                        $fieldnames=array(0 => $surveyid.'X'.$qtype['gid'].'X'.$quota_entry['qid'].$quota_entry['code']);
                        $value = "Y";
                    }

                    if($qtype['type'] == "A" || $qtype['type'] == "B")
                    {
                        $temp = explode('-',$quota_entry['code']);
                        $fieldnames=array(0 => $surveyid.'X'.$qtype['gid'].'X'.$quota_entry['qid'].$temp[0]);
                        $value = $temp[1];
                    }

                    array_push($quota_info[$x]['members'],array('Title' => $qtype['title'],
                    'type' => $qtype['type'],
                    'code' => $quota_entry['code'],
                    'value' => $value,
                    'qid' => $quota_entry['qid'],
                    'fieldnames' => $fieldnames));
                }
            }
            $x++;
        }
    }
    return $quota_info;
}

/**
* This function checks if a given question should be displayed or not
* If the optionnal gid parameter is set, then we are in a group/group survey
* and thus we can't evaluate conditions using answers on the same page
* (this will be done by javascript): in this case we disregard conditions on
* answers from same page
*
* @param mixed $qid
* @param mixed $gid
*/
function checkquestionfordisplay($qid, $gid=null)
{
    // TMSW Conditions->Relevance:  not needed (only check relevance)
    global $thissurvey;
    $CI = &get_instance();
    $surveyid = Yii::app()->getConfig('sid');

    if (!is_array($thissurvey))
    {
        $local_thissurvey=getSurveyInfo($surveyid);
    }
    else
    {
        $local_thissurvey=$thissurvey;
    }

    /*$scenarioquery = "SELECT DISTINCT scenario FROM ".db_table_name("conditions")
    ." WHERE ".db_table_name("conditions").".qid=$qid ORDER BY scenario";
    $scenarioresult=db_execute_assoc($scenarioquery);*/
    $CI->load->model('conditions_model');
    $CI->load->model('questions_model');
    $query = $CI->conditions_model->getScenarios($qid);

    //if ($scenarioresult->RecordCount() == 0)
    if($query->num_rows() == 0)
    {
        return true;
    }

    //while ($scenariorow=$scenarioresult->FetchRow())
    foreach ($query->result_array() as $scenariorow)
    {
        $scenario = $scenariorow['scenario'];
        $totalands=0;
        /*$query = "SELECT * FROM ".db_table_name('conditions')."\n"
        ."WHERE qid=$qid AND scenario=$scenario ORDER BY cqid,cfieldname";
        $result = db_execute_assoc($query) or safe_die("Couldn't check conditions<br />$query<br />".$connect->ErrorMsg());   //Checked
        */
        $subquery = $CI->conditions_model->getAllRecords(array('qid'=>$qid,'scenario'=>$scenario),"cqid,cfieldname");

        $conditionsfoundforthisscenario=0;
        foreach ($subquery->result_array() as $row)
        //while($row=$result->FetchRow())
        {
            // Conditions on different cfieldnames from the same question are ANDed together
            // (for instance conditions on several multiple-numerical lines)
            //
            // However, if they are related to the same cfieldname
            // they are ORed. Conditions on the same cfieldname can be either:
            // * conditions on the same 'simple question':
            //   - for instance several possible answers on the same radio-button question
            // * conditions on the same 'multiple choice question':
            //   - this case is very specific. In fact each checkbox corresponds to a different
            //     cfieldname (1X1X1a, 1X1X1b, ...), but the condition uses only the base
            //     'SGQ' cfieldname and the expected answers codes as values
            //   - then, in the following lines for questions M or P, we transform the
            //     condition SGQ='a' to SGQa='Y'. We need also to keep the artificial distinctcfieldname
            //     value to SGQ so that we can implement ORed conditions between the cbx
            //  ==> This explains why conditions on multiple choice answers are ORed even if
            //      in reality they use a different cfieldname for each checkbox
            //
            // In order to implement this we build an array storing the result
            // of condition evaluations for this group and scenario
            // This array is processed as follow:
            // * it is indexed by cfieldname,
            // * each 'cfieldname' row is added at the first condition eval on this fieldname
            // * each row is updated only if the condition evaluation is successful
            //   ==> this way if only 1 condition for a cfieldname is successful, the set of
            //       conditions for this cfieldname is assumed to be met (Ored conditions)

            $conditionsfoundforthisscenario++;
            $conditionCanBeEvaluated=true;
            //Iterate through each condition for this question and check if it is met.

            if (preg_match("/^\+(.*)$/",$row['cfieldname'],$cfieldnamematch))
            { // this condition uses a single checkbox as source
                $conditionSourceType='question';
                /*$query2= "SELECT type, gid FROM ".db_table_name('questions')."\n"
                ." WHERE qid={$row['cqid']} AND language='".$_SESSION['s_lang']."'";
                $result2=db_execute_assoc($query2) or safe_die ("Coudn't get type from questions<br />$ccquery<br />".$connect->ErrorMsg());   //Checked
                */
                $query2=$CI->questions_model->getSomeRecords(array("type, gid"),array('qid'=>$row['cqid'],'language'=>$CI->session->userdata('s_lang')));
                //while($row2=$result2->FetchRow())
                foreach ($query2->result_array() as $row2)
                {
                    $cq_gid=$row2['gid'];
                    // set type to +M or +P in order to skip
                    $thistype='+'.$row2['type'];
                }

                $row['cfieldname']=$cfieldnamematch[1]; // remover the leading '+'
            }
            elseif (preg_match("/^{/",$row['cfieldname']))
            { // this condition uses a token attr as source
                $conditionSourceType='tokenattr';
                $thistype="";
                $cq_gid=0;
            }
            else
            { // this is a simple condition using a question as source
                $conditionSourceType='question';
                /*$query2= "SELECT type, gid FROM ".db_table_name('questions')."\n"
                ." WHERE qid={$row['cqid']} AND language='".$_SESSION['s_lang']."'";
                $result2=db_execute_assoc($query2) or safe_die ("Coudn't get type from questions<br />$ccquery<br />".$connect->ErrorMsg());   //Checked
                */
                $query2=$CI->questions_model->getSomeRecords(array("type, gid"),array('qid'=>$row['cqid'],'language'=>$CI->session->userdata('s_lang')));
                foreach ($query2->result_array() as $row2)
                //while($row2=$result2->FetchRow())
                {
                    $cq_gid=$row2['gid'];
                    //Find out the 'type' of the question this condition uses
                    $thistype=$row2['type'];
                }
            }



            // Fix the cfieldname and cvalue in case of type M or P questions
            if ($thistype == "M" || $thistype == "P")
            {
                // The distinctcfieldname simply is the virtual cfieldname
                $row['distinctcfieldname']=$row['cfieldname'];

                // For multiple choice type questions, the "answer" value will be "Y"
                // if selected, the fieldname will have the answer code appended.
                $row['cfieldname']=$row['cfieldname'].$row['value'];
                $row['value']="Y";
            }
            else
            {
                // the distinctcfieldname simply is the real cfieldname
                $row['distinctcfieldname']=$row['cfieldname'];
            }

            if ( !is_null($gid) && $gid == $cq_gid && $conditionSourceType == 'question')
            {
                //Don't do anything - this cq is in the current group
            }
            elseif (preg_match('/^@([0-9]+X[0-9]+X[^@]+)@'.'/',$row['value'],$targetconditionfieldname))
            {
                if ($CI->session->userdata($targetconditionfieldname[1]))
                {
                    // If value uses @SIDXGIDXQID@ codes i
                    // then try to replace them with a
                    // value recorded in SESSION if any
                    $cvalue=$CI->session->userdata($targetconditionfieldname[1]);
                    if ($conditionSourceType == 'question')
                    {
                        if ($CI->session->userdata($row['cfieldname']))
                        {
                            $cfieldname=$CI->session->userdata($row['cfieldname']);
                        }
                        else
                        {
                            $conditionCanBeEvaluated=false;
                            //$cfieldname=' ';
                        }
                    }
                    elseif ($local_thissurvey['anonymized'] == "N" && preg_match('/^{TOKEN:([^}]*)}$/',$row['cfieldname'],$sourceconditiontokenattr))
                    {
                        if ($CI->session->userdata('token') &&
                        in_array(strtolower($sourceconditiontokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
                        {
                            $cfieldname=GetAttributeValue($surveyid,strtolower($sourceconditiontokenattr[1]),$CI->session->userdata('token'));
                        }
                        else
                        {
                            $conditionCanBeEvaluated=false;
                        }

                    }
                    else
                    {
                        $conditionCanBeEvaluated=false;
                    }
                }
                else
                { // if _SESSION[$targetconditionfieldname[1]] is not set then evaluate condition as FALSE
                    $conditionCanBeEvaluated=false;
                    //$cfieldname=' ';
                }
            }
            elseif ($local_thissurvey['anonymized'] == "N" && preg_match('/^{TOKEN:([^}]*)}$/',$row['value'],$targetconditiontokenattr))
            {
                if ($CI->session->userdata('token') &&
                in_array(strtolower($targetconditiontokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
                {
                    // If value uses {TOKEN:XXX} placeholders
                    // then try to replace them with a
                    // the value recorded in DB
                    $cvalue=GetAttributeValue($surveyid,strtolower($targetconditiontokenattr[1]),$CI->session->userdata('token'));
                    if ($conditionSourceType == 'question')
                    {
                        if ($CI->session->userdata($row['cfieldname']))
                        {
                            $cfieldname=$CI->session->userdata($row['cfieldname']);
                        }
                        else
                        {
                            $conditionCanBeEvaluated=false;
                        }
                    }
                    elseif ($local_thissurvey['anonymized'] == "N" && preg_match('/^{TOKEN:([^}]*)}$/',$row['cfieldname'],$sourceconditiontokenattr))
                    {
                        if ( $CI->session->userdata('token') &&
                        in_array(strtolower($sourceconditiontokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
                        {
                            $cfieldname=GetAttributeValue($surveyid,strtolower($sourceconditiontokenattr[1]),$CI->session->userdata('token'));
                        }
                        else
                        {
                            $conditionCanBeEvaluated=false;
                        }

                    }
                    else
                    {
                        $conditionCanBeEvaluated=false;
                    }
                }
                else
                { // if _SESSION[$targetconditionfieldname[1]] is not set then evaluate condition as FALSE
                    $conditionCanBeEvaluated=false;
                }
            }
            else
            {
                $cvalue=$row['value'];
                if ($conditionSourceType == 'question')
                {
                    if ($CI->session->userdata($row['cfieldname']))
                    {
                        $cfieldname=$CI->session->userdata($row['cfieldname']);
                    }
                    elseif ($thistype == "M" || $thistype == "P" || $thistype == "+M" || $thistype == "+P")
                    {
                        $cfieldname="";
                    }
                    else
                    {
                        $conditionCanBeEvaluated=false;
                    }
                }
                elseif ($local_thissurvey['anonymized'] == "N" && preg_match('/^{TOKEN:([^}]*)}$/',$row['cfieldname'],$sourceconditiontokenattr))
                {
                    if ( $CI->session->userdata('token') &&
                    in_array(strtolower($sourceconditiontokenattr[1]),GetTokenConditionsFieldNames($surveyid)))
                    {
                        $cfieldname=GetAttributeValue($surveyid,strtolower($sourceconditiontokenattr[1]),$CI->session->userdata('token'));
                    }
                    else
                    {
                        $conditionCanBeEvaluated=false;
                    }

                }
                else
                {
                    $conditionCanBeEvaluated=false;
                }
            }

            if ( !is_null($gid) && $gid == $cq_gid && $conditionSourceType == 'question')
            {
                //Don't do anything - this cq is in the current group
                $conditionMatches=true;
            }
            elseif ($conditionCanBeEvaluated === false)
            {
                // condition can't be evaluated, so let's assume FALSE
                $conditionMatches=false;
            }
            else
            {
                if (trim($row['method'])=='')
                {
                    $row['method']='==';
                }
                if ($row['method'] != 'RX')
                {
                    if (preg_match("/^a(.*)b$/",$row['method'],$matchmethods))
                    {
                        // strings comparizon operator in PHP are the same as numerical operators
                        $matchOperator = $matchmethods[1];
                    }
                    else
                    {
                        $matchOperator = $row['method'];
                    }
                    if (eval('if (trim($cfieldname)'. $matchOperator.' trim($cvalue)) return true; else return false;'))
                    {
                        //  error_log("TIBO1 oper=$matchOperator");
                        $conditionMatches=true;
                        //This condition is met
                    }
                    else
                    {
                        //   error_log("TIBO2 oper=$matchOperator");
                        $conditionMatches=false;
                    }
                }
                else
                {
                    if (preg_match('/'.trim($cvalue).'/',trim($cfieldname)))
                    {
                        $conditionMatches=true;

                    }
                    else
                    {
                        $conditionMatches=false;
                    }
                }
            }

            if ($conditionMatches === true)
            {
                // Let's store this positive result in the distinctcqids array
                // indexed by cfieldname so that conditions on theb same cfieldname ar Ored
                // while conditions on different cfieldnames (either different conditions
                // or conditions on different cfieldnames inside the same question)
                if (!isset($distinctcqids[$row['distinctcfieldname']]) || $distinctcqids[$row['distinctcfieldname']] == 0)
                {
                    $distinctcqids[$row['distinctcfieldname']] = 1;
                }
            }
            else
            {
                // Let's store this negative result in the distinctcqids array
                // indexed by cfieldname so that conditions on theb same cfieldname ar Ored
                // while conditions on different cfieldnames (either different conditions
                // or conditions on different cfieldnames inside the same question)
                if (!isset($distinctcqids[$row['distinctcfieldname']]))
                {
                    $distinctcqids[$row['distinctcfieldname']] = 0;
                }
            }
        } // while
        if ($conditionsfoundforthisscenario > 0) {
            foreach($distinctcqids as $key=>$val)
            {
                // Let's count the number of conditions that are met, and then compare
                // it to the total number of stored results
                $totalands=$totalands+$val;
            }
            if ($totalands >= count($distinctcqids))
            {
                // if all stored results are positive then we MUST show the group
                // because at least this question is displayed
                return true;
            }
        }
        else
        {
            //Curious there is no condition for this question in this scenario
            // this is not a normal behaviour, but I propose to defaults to a
            // condition-matched state in this case
            return true;
        }
        unset($distinctcqids);
    } // end while scenario
    return false;
}

/**
* Strips the DB prefix from a string - does not verify just strips the according number of characters
*
* @param mixed $sTableName
* @return string
*/
function sStripDBPrefix($sTableName)
{
    $CI = &get_instance();
    $dbprefix = $CI->db->dbprefix;
    return substr($sTableName,strlen($dbprefix));
}

/**
* This function replaces the old insertans tags with new ones across a survey
*
* @param string $newsid  Old SID
* @param string $oldsid  New SID
* @param mixed $fieldnames Array  array('oldfieldname'=>'newfieldname')
*/
function TranslateInsertansTags($newsid,$oldsid,$fieldnames)
{
    Yii::app()->loadHelper('database');
    $newsid=sanitize_int($newsid);
    $oldsid=sanitize_int($oldsid);

    # translate 'surveyls_urldescription' and 'surveyls_url' INSERTANS tags in surveyls
    $sql = "SELECT surveyls_survey_id, surveyls_language, surveyls_urldescription, surveyls_url from {{surveys_languagesettings}} WHERE surveyls_survey_id=".$newsid." AND (surveyls_urldescription LIKE '%{INSERTANS:".$oldsid."X%' OR surveyls_url LIKE '%{INSERTANS:".$oldsid."X%')";
    $result = db_execute_assoc($sql) or show_error("Can't read groups table in transInsertAns ");     // Checked
    //$result=$CI->surveys_languagesettings_model->getSomeRecords(array("surveyls_survey_id", "surveyls_language", "surveyls_urldescription", "surveyls_url"),"surveyls_survey_id=".$newsid." AND (surveyls_urldescription LIKE '%{INSERTANS:".$oldsid."X%' OR surveyls_url LIKE '%{INSERTANS:".$oldsid."X%')");

    //while ($qentry = $res->FetchRow())
    foreach ($result->readAll() as $qentry)
    {
        $urldescription = $qentry['surveyls_urldescription'];
        $endurl  = $qentry['surveyls_url'];
        $language = $qentry['surveyls_language'];

        foreach ($fieldnames as $sOldFieldname=>$sNewFieldname)
        {
            $pattern = "{INSERTANS:".$sOldFieldname."}";
            $replacement = "{INSERTANS:".$sNewFieldname."}";
            $urldescription=preg_replace('/'.$pattern.'/', $replacement, $urldescription);
            $endurl=preg_replace('/'.$pattern.'/', $replacement, $endurl);
        }

        if (strcmp($urldescription,$qentry['surveyls_urldescription']) !=0  ||
        (strcmp($endurl,$qentry['surveyls_url']) !=0))
        {

            // Update Field
            //$sqlupdate = "UPDATE {$dbprefix}surveys_languagesettings SET surveyls_urldescription='".db_quote($urldescription)."', surveyls_url='".db_quote($endurl)."' WHERE surveyls_survey_id=$newsid AND surveyls_language='$language'";
            //$updateres=$connect->Execute($sqlupdate) or safe_die ("Couldn't update INSERTANS in surveys_languagesettings<br />$sqlupdate<br />".$connect->ErrorMsg());    //Checked

            $data = array(
            'surveyls_urldescription' => $urldescription,
            'surveyls_url' => $endurl
            );

            $where = array(
            'surveyls_survey_id' => $newsid,
            'surveyls_language' => $language
            );

            Surveys_languagesettings::update($data,$where);

        } // Enf if modified
    } // end while qentry

    # translate 'quotals_urldescrip' and 'quotals_url' INSERTANS tags in quota_languagesettings
    $sql = "SELECT quotals_id, quotals_urldescrip, quotals_url from {{quota_languagesettings}} qls, {{quota}} q WHERE sid=".$newsid." AND q.id=qls.quotals_quota_id AND (quotals_urldescrip LIKE '%{INSERTANS:".$oldsid."X%' OR quotals_url LIKE '%{INSERTANS:".$oldsid."X%')";
    $res = db_execute_assoc($sql) or safe_die("Can't read quota table in transInsertAns");     // Checked

    foreach ($result->readAll() as $qentry)
    {
        $urldescription = $qentry['quotals_urldescrip'];
        $endurl  = $qentry['quotals_url'];

        foreach ($fieldnames as $sOldFieldname=>$sNewFieldname)
        {
            $pattern = "{INSERTANS:".$sOldFieldname."}";
            $replacement = "{INSERTANS:".$sNewFieldname."}";
            $urldescription=preg_replace('/'.$pattern.'/', $replacement, $urldescription);
            $endurl=preg_replace('/'.$pattern.'/', $replacement, $endurl);
        }

        if (strcmp($urldescription,$qentry['quotals_urldescrip']) !=0  || (strcmp($endurl,$qentry['quotals_url']) !=0))
        {
            // Update Field
            $sqlupdate = "UPDATE {{quota_languagesettings}} SET quotals_urldescrip='".db_quote($urldescription)."', quotals_url='".db_quote($endurl)."' WHERE quotals_id={$qentry['quotals_id']}";
            $updateres=db_execute_assoc($sqlupdate) or safe_die ("Couldn't update INSERTANS in quota_languagesettings<br />$sqlupdate<br />");    //Checked
        } // Enf if modified
    } // end while qentry

    # translate 'description' INSERTANS tags in groups
    $sql = "SELECT gid, language, group_name, description from {{groups}} WHERE sid=".$newsid." AND description LIKE '%{INSERTANS:".$oldsid."X%' OR group_name LIKE '%{INSERTANS:".$oldsid."X%'";
    $res = db_execute_assoc($sql) or show_error("Can't read groups table in transInsertAns");     // Checked
    //$result=$CI->groups_model->getSomeRecords("gid, language, group_name, description","sid=".$newsid." AND description LIKE '%{INSERTANS:".$oldsid."X%' OR group_name LIKE '%{INSERTANS:".$oldsid."X%'");

    //while ($qentry = $res->FetchRow())
    foreach ($res->readAll() as $qentry)
    {
        $gpname = $qentry['group_name'];
        $description = $qentry['description'];
        $gid = $qentry['gid'];
        $language = $qentry['language'];

        foreach ($fieldnames as $sOldFieldname=>$sNewFieldname)
        {
            $pattern = "{INSERTANS:".$sOldFieldname."}";
            $replacement = "{INSERTANS:".$sNewFieldname."}";
            $gpname = preg_replace('/'.$pattern.'/', $replacement, $gpname);
            $description=preg_replace('/'.$pattern.'/', $replacement, $description);
        }

        if (strcmp($description,$qentry['description']) !=0  ||
        strcmp($gpname,$qentry['group_name']) !=0)
        {
            // Update Fields
            //$sqlupdate = "UPDATE {$dbprefix}groups SET description='".db_quote($description)."', group_name='".db_quote($gpname)."' WHERE gid=$gid AND language='$language'";
            //$updateres=$connect->Execute($sqlupdate) or safe_die ("Couldn't update INSERTANS in groups<br />$sqlupdate<br />".$connect->ErrorMsg());    //Checked

            $data = array(
            'description' => $description,
            'group_name' => $gpname
            );

            $where = array(
            'gid' => $gid,
            'language' => $language
            );

            Groups::model()->update($data,$where);

        } // Enf if modified
    } // end while qentry

    # translate 'question' and 'help' INSERTANS tags in questions
    $sql = "SELECT qid, language, question, help from {{questions}} WHERE sid=".$newsid." AND (question LIKE '%{INSERTANS:".$oldsid."X%' OR help LIKE '%{INSERTANS:".$oldsid."X%')";
    $res = db_execute_assoc($sql) or show_error("Can't read question table in transInsertAns ");     // Checked
    //$result=$CI->questions_model->getSomeRecords("qid, language, question, help","sid=".$newsid." AND (question LIKE '%{INSERTANS:".$oldsid."X%' OR help LIKE '%{INSERTANS:".$oldsid."X%')");

    //while ($qentry = $res->FetchRow())
    foreach ($result->readAll() as $qentry)
    {
        $question = $qentry['question'];
        $help = $qentry['help'];
        $qid = $qentry['qid'];
        $language = $qentry['language'];

        foreach ($fieldnames as $sOldFieldname=>$sNewFieldname)
        {
            $pattern = "{INSERTANS:".$sOldFieldname."}";
            $replacement = "{INSERTANS:".$sNewFieldname."}";
            $question=preg_replace('/'.$pattern.'/', $replacement, $question);
            $help=preg_replace('/'.$pattern.'/', $replacement, $help);
        }

        if (strcmp($question,$qentry['question']) !=0 ||
        strcmp($help,$qentry['help']) !=0)
        {
            // Update Field
            //$sqlupdate = "UPDATE {$dbprefix}questions SET question='".db_quote($question)."', help='".db_quote($help)."' WHERE qid=$qid AND language='$language'";
            //$updateres=$connect->Execute($sqlupdate) or safe_die ("Couldn't update INSERTANS in question<br />$sqlupdate<br />".$connect->ErrorMsg());    //Checked

            $data = array(
            'question' => $question,
            'help' => $help
            );

            $where = array(
            'qid' => $qid,
            'language' => $language
            );

            Questions::model()->update($data,$where);

        } // Enf if modified
    } // end while qentry

    # translate 'answer' INSERTANS tags in answers
    //$sql = "SELECT a.qid, a.language, a.code, a.answer from {$dbprefix}answers as a INNER JOIN {$dbprefix}questions as b ON a.qid=b.qid WHERE b.sid=".$newsid." AND a.answer LIKE '%{INSERTANS:".$oldsid."X%'";
    //$res = db_execute_assoc($sql) or safe_die("Can't read answers table in transInsertAns ".$connect->ErrorMsg());     // Checked
    $result=Answers::model()->oldNewInsertansTags($newsid,$oldsid);

    //while ($qentry = $res->FetchRow())
    foreach ($result->readAll() as $qentry)
    {
        $answer = $qentry['answer'];
        $code = $qentry['code'];
        $qid = $qentry['qid'];
        $language = $qentry['language'];

        foreach ($fieldnames as $sOldFieldname=>$sNewFieldname)
        {
            $pattern = "{INSERTANS:".$sOldFieldname."}";
            $replacement = "{INSERTANS:".$sNewFieldname."}";
            $answer=preg_replace('/'.$pattern.'/', $replacement, $answer);
        }

        if (strcmp($answer,$qentry['answer']) !=0)
        {
            // Update Field
            //$sqlupdate = "UPDATE {$dbprefix}answers SET answer='".db_quote($answer)."' WHERE qid=$qid AND code='$code' AND language='$language'";
            //$updateres=$connect->Execute($sqlupdate) or safe_die ("Couldn't update INSERTANS in answers<br />$sqlupdate<br />".$connect->ErrorMsg());    //Checked

            $data = array(
            'answer' => $answer,
            'qid' => $qid
            );

            $where = array(
            'code' => $code,
            'language' => $language
            );

            Answers::model()->update($data,$where);

        } // Enf if modified
    } // end while qentry
}

/**
* This function is a replacement of access_denied.php which return appropriate error message which is then displayed.
*
* @params string $action - action for which acces denied error message is to be returned
* @params string sid - survey id
* @return $accesssummary - proper access denied error message
*/
function access_denied($action,$sid='')
{
    $clang = Yii::app()->lang;
    if (Yii::app()->session['loginID'])
    {
        $ugid = Yii::app()->getConfig('ugid');
        $accesssummary = "<p><strong>".$clang->gT("Access denied!")."</strong><br />\n";
        $scriptname = Yii::app()->getConfig('scriptname');
        //$action=returnglobal('action');
        if  (  $action == "dumpdb"  )
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed dump the database!")."<br />";
            $accesssummary .= "<a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "dumplabel")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed export a label set!")."<br />";
            $accesssummary .= "<a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "edituser")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed to change user data!");
            $accesssummary .= "<br /><br /><a href='$scriptname?action=editusers'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "newsurvey")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed to create new surveys!")."<br />";
            $accesssummary .= "<a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "deletesurvey")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed to delete this survey!")."<br />";
            $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "addquestion")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed to add new questions for this survey!")."<br />";
            $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "activate")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed to activate this survey!")."<br />";
            $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "deactivate")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed to stop this survey!")."<br />";
            $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "addgroup")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed to add a group to this survey!")."<br />";
            $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "ordergroups")
        {
            $link = site_url("admin/survey/view/$sid");
            $accesssummary .= "<p>".$clang->gT("You are not allowed to order groups in this survey!")."<br />";
            $accesssummary .= "<a href='$link'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "editsurvey")
        {
            $link = site_url("admin/survey/view/$sid");
            $accesssummary .= "<p>".$clang->gT("You are not allowed to edit this survey!")."</p>";
            $accesssummary .= "<a href='$link'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "editgroup")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed to edit groups in this survey!")."</p>";
            $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "browse_response" || $action == "listcolumn" || $action == "vvexport" || $action == "vvimport")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed to browse responses!")."</p>";
            $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "assessment")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed to set assessment rules!")."</p>";
            $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "delusergroup")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed to delete this group!")."</p>";
            $accesssummary .= "<a href='$scriptname?action=editusergroups'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "importsurvey")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed to import a survey!")."</p>";
            $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }

        elseif($action == "importgroup")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed to import a group!")."</p>";
            $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "importquestion")
        {
            $accesssummary .= "<p>".$clang->gT("You are not allowed to to import a question!")."</p>";
            $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "CSRFwarn") //won't be used.
        {
            $sURLID='';
            if (isset($sid)) {
                $sURLID="?sid={$sid}";
            }
            $accesssummary .= "<p><span color='errortitle'>".$clang->gT("Security alert")."</span>: ".$clang->gT("Someone may be trying to use your LimeSurvey session (CSRF attack suspected). If you just clicked on a malicious link, please report this to your system administrator.").'<br>'.$clang->gT('Also this problem can occur when you are working/editing in LimeSurvey in several browser windows/tabs at the same time.')."</p>";
            $accesssummary .= "<a href='{$scriptname}{$sURLID}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif($action == "FakeGET")
        {
            $accesssummary .= "<p><span class='errortitle'>".$clang->gT("Security alert")."</span>: ".$clang->gT("Someone may be trying to use your LimeSurvey session (CSRF attack suspected). If you just clicked on a malicious link, please report this to your system administrator.").'<br>'.$clang->gT('Also this problem can occur when you are working/editing in LimeSurvey in several browser windows/tabs at the same time.')."</p>";
            $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        else
        {
            $accesssummary .= "<br />".$clang->gT("You are not allowed to perform this operation!")."<br />\n";
            if(!empty($sid))
            {
                $accesssummary .= "<br /><br /><a href='$scriptname?sid=$sid>".$clang->gT("Continue")."</a><br />&nbsp;\n";
            }
            elseif(!empty($ugid))
            {
                $accesssummary .= "<br /><br /><a href='$scriptname?action=editusergroups&ugid={$ugid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
            }
            else
            {
                $accesssummary .= "<br /><br /><a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
            }
        }
        return $accesssummary;
    }

}

/**
* CleanLanguagesFromSurvey() removes any languages from survey tables that are not in the passed list
* @param string $sid - the currently selected survey
* @param string $availlangs - space seperated list of additional languages in survey
* @return bool - always returns true
*/
function CleanLanguagesFromSurvey($sid, $availlangs)
{
    $CI =& get_instance();
    $CI->load->helper('database');
    //$clang = $CI->limesurvey_lang;
    $sid=sanitize_int($sid);
    $baselang = GetBaseLanguageFromSurveyID($sid);

    if (!empty($availlangs) && $availlangs != " ")
    {
        $availlangs=sanitize_languagecodeS($availlangs);
        $langs = explode(" ",$availlangs);
        if($langs[count($langs)-1] == "") array_pop($langs);
    }

    $sqllang = "language <> '".$baselang."' ";

    if (!empty($availlangs) && $availlangs != " ")
    {
        foreach ($langs as $lang)
        {
            $sqllang .= "AND language <> '".$lang."' ";
        }
    }

    // Remove From Answers Table
    $query = "SELECT qid FROM ".$CI->db->dbprefix."questions WHERE sid='{$sid}' AND $sqllang";

    $qidresult = db_execute_assoc($query);    //Checked

    foreach ($qidresult->result_array() as $qrow)
    {

        $myqid = $qrow['qid'];
        $query = "DELETE FROM ".$CI->db->dbprefix."answers WHERE qid='$myqid' AND $sqllang";
        db_execute_assoc($query) ; //$connect->Execute($query) or safe_die($connect->ErrorMsg());    //Checked
    }

    // Remove From Questions Table
    $query = "DELETE FROM ".$CI->db->dbprefix."questions WHERE sid='{$sid}' AND $sqllang";
    db_execute_assoc($query) ;
    //$connect->Execute($query) or safe_die($connect->ErrorMsg());   //Checked

    // Remove From Groups Table
    $query = "DELETE FROM ".$CI->db->dbprefix."groups WHERE sid='{$sid}' AND $sqllang";
    //$connect->Execute($query) or safe_die($connect->ErrorMsg());   //Checked
    db_execute_assoc($query) ;

    return true;
}

/**
* FixLanguageConsistency() fixes missing groups,questions,answers & assessments for languages on a survey
* @param string $sid - the currently selected survey
* @param string $availlangs - space seperated list of additional languages in survey - if empty all additional languages of a survey are checked against the base language
* @return bool - always returns true
*/
function FixLanguageConsistency($sid, $availlangs='')
{
    $clang = Yii::app()->lang;
    if (trim($availlangs)!='')
    {
        $availlangs=sanitize_languagecodeS($availlangs);
        $langs = explode(" ",$availlangs);
        if($langs[count($langs)-1] == "") array_pop($langs);
    } else {
        $langs=GetAdditionalLanguagesFromSurveyID($sid);
    }

    $baselang = GetBaseLanguageFromSurveyID($sid);
    $sid=sanitize_int($sid);
    $query = "SELECT * FROM {{groups}} WHERE sid='{$sid}' AND language='{$baselang}'  ORDER BY group_order";
    $result = Yii::app()->db->createCommand($query)->query(); //or safe_die($connect->ErrorMsg());  //Checked
    if ($result->getRowCount() > 0)
    {
        foreach($result->readAll() as $group)
        {
            foreach ($langs as $lang)
            {

                $query = "SELECT gid FROM {{groups}} WHERE sid='{$sid}' AND gid='{$group['gid']}' AND language='{$lang}'";
                $gresult = Yii::app()->db->createCommand($query)->query(); // or safe_die($connect->ErrorMsg()); //Checked
                if ($gresult->getRowCount() < 1)
                {
                    $data = array(
                    'gid' => $group['gid'],
                    'sid' => $group['sid'],
                    'group_name' => $group['group_name'],
                    'group_order' => $group['group_order'],
                    'description' => $group['description'],
                    'language' => $lang

                    );
                    Yii::app()->db->createCommand()->insert('{{groups}}', $data);
                    //$query = "INSERT INTO ".$CI->db->dbprefix."groups (gid,sid,group_name,group_order,description,language) VALUES('{$group['gid']}','{$group['sid']}',".db_quoteall($group['group_name']).",'{$group['group_order']}',".db_quoteall($group['description']).",'{$lang}')";
                    //db_execute_assoc($query); //$connect->Execute($query) or safe_die($connect->ErrorMsg());  //Checked
                }
            }
            reset($langs);
        }
    }

    $quests = array();
    $query = "SELECT * FROM {{questions}} WHERE sid='{$sid}' AND language='{$baselang}' ORDER BY question_order";
    $result = Yii::app()->db->createCommand($query)->query(); // or safe_die($connect->ErrorMsg());  //Checked
    if ($result->getRowCount() > 0)
    {
        foreach($result->readAll() as $question)
        {
            array_push($quests,$question['qid']);
            foreach ($langs as $lang)
            {
                $query = "SELECT qid FROM {{questions}} WHERE sid='{$sid}' AND qid='{$question['qid']}' AND language='{$lang}'";
                $gresult = Yii::app()->db->createCommand($query)->query(); // or safe_die($connect->ErrorMsg());   //Checked
                if ($gresult->getRowCount() < 1)
                {
                    db_switchIDInsert('questions',true);
                    $data = array(
                    'qid' => $question['qid'],
                    'sid' => $question['sid'],
                    'gid' => $question['gid'],
                    'type' => $question['type'],
                    'question' => $question['question'],
                    'preg' => $question['preg'],
                    'help' => $question['help'],
                    'other' => $question['other'],
                    'mandatory' => $question['mandatory'],
                    'question_order' => $question['question_order'],
                    'language' => $lang,
                    'scale_id' => $question['scale_id'],
                    'parent_qid' => $question['parent_qid']
                    );
                	Yii::app()->db->createCommand()->insert('{{questions}}', $data);
                    //$query = "INSERT INTO ".db_table_name('questions')." (qid,sid,gid,type,title,question,preg,help,other,mandatory,question_order,language, scale_id,parent_qid) VALUES('{$question['qid']}','{$question['sid']}','{$question['gid']}','{$question['type']}',".db_quoteall($question['title']).",".db_quoteall($question['question']).",".db_quoteall($question['preg']).",".db_quoteall($question['help']).",'{$question['other']}','{$question['mandatory']}','{$question['question_order']}','{$lang}',{$question['scale_id']},{$question['parent_qid']})";
                }
            }
            reset($langs);
        }

        $sqlans = "";
        foreach ($quests as $quest)
        {
            $sqlans .= " OR qid = '".$quest."' ";
        }

        $query = "SELECT * FROM {{answers}} WHERE language='{$baselang}' and (".trim($sqlans,' OR').") ORDER BY qid, code";
        $result = Yii::app()->db->createCommand($query)->query() ;//or safe_die($connect->ErrorMsg()); //Checked
        if ($result->getRowCount() > 0)
        {
            foreach($result->readAll() as $answer)
            {
                foreach ($langs as $lang)
                {
                    $query = "SELECT qid FROM {{answers}} WHERE code='{$answer['code']}' AND qid='{$answer['qid']}' AND language='{$lang}'";
                    $gresult = Yii::app()->db->createCommand($query)->query(); // or safe_die($connect->ErrorMsg());  //Checked
                    if ($gresult->getRowCount() < 1)
                    {
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
    }


    $query = "SELECT * FROM {{assessments}} WHERE sid='{$sid}' AND language='{$baselang}'";
    $result = Yii::app()->db->createCommand($query)->query(); // or safe_die($connect->ErrorMsg());  //Checked
    if ($result->getRowCount() > 0)
    {
        foreach($result->readAll() as $assessment)
        {
            foreach ($langs as $lang)
            {
                $query = "SELECT id FROM {{assessments}} WHERE sid='{$sid}' AND id='{$assessment['id']}' AND language='{$lang}'";
                $gresult = Yii::app()->db->createCommand($query)->query(); // or safe_die($connect->ErrorMsg()); //Checked
                if ($gresult->getRowCount() < 1)
                {
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
    }



    return true;
}

/**
* This function switches identity insert on/off for the MSSQL database
*
* @param string $table table name (without prefix)
* @param mixed $state  Set to true to activate ID insert, or false to deactivate
*/
function db_switchIDInsert($table,$state)
{
    Yii::app()->loadHelper('database');
    if (Yii::app()->db->getDriverName() =='odbc_mssql' || Yii::app()->db->getDriverName() =='odbtp' || Yii::app()->db->getDriverName() =='mssql_n' || Yii::app()->db->getDriverName() =='mssqlnative')
    {
        if ($state==true)
        {
            //$connect->Execute('SET IDENTITY_INSERT '.db_table_name($table).' ON');
            db_execute_assoc('SET IDENTITY_INSERT {{'.$table.'}} ON');
        }
        else
        {
            //$connect->Execute('SET IDENTITY_INSERT '.db_table_name($table).' OFF');
            db_execute_assoc('SET IDENTITY_INSERT {{'.$table.'}} OFF');
        }
    }
}

// TMSW Conditions->Relevance:  This function is not needed?  Optionally replace this with call to EM to get similar info
/**
* GetGroupDepsForConditions() get Dependencies between groups caused by conditions
* @param string $sid - the currently selected survey
* @param string $depgid - (optionnal) get only the dependencies applying to the group with gid depgid
* @param string $targgid - (optionnal) get only the dependencies for groups dependents on group targgid
* @param string $index-by - (optionnal) "by-depgid" for result indexed with $res[$depgid][$targgid]
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
*       $result=GetGroupDepsForConditions($sid);
*   * Get all group dependencies for GID $gid in survey $sid indexed by depgid:
*       $result=GetGroupDepsForConditions($sid,$gid);
*   * Get all group dependents on group $gid in survey $sid indexed by targgid:
*       $result=GetGroupDepsForConditions($sid,"all",$gid,"by-targgid");
*/
function GetGroupDepsForConditions($sid,$depgid="all",$targgid="all",$indexby="by-depgid")
{
    $sid=sanitize_int($sid);
    $condarray = Array();
    $sqldepgid="";
    $sqltarggid="";
    if ($depgid != "all") { $depgid = sanitize_int($depgid); $sqldepgid="AND tq.gid=$depgid";}
    if ($targgid != "all") {$targgid = sanitize_int($targgid); $sqltarggid="AND tq2.gid=$targgid";}

    $baselang = GetBaseLanguageFromSurveyID($sid);
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
	$condresult = Yii::app()->db->createCommand($condquery)->query();

    if ($condresult->getRowCount() > 0) {
        foreach ($condresult->readAll() as $condrow)
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

// TMSW Conditions->Relevance:  This function is not needed?  Optionally replace this with call to EM to get similar info
/**
* GetQuestDepsForConditions() get Dependencies between groups caused by conditions
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
*       $result=GetQuestDepsForConditions($sid,$gid);
*   * Get all questions dependencies for question $qid in survey/group $sid/$gid indexed by depqid:
*       $result=GetGroupDepsForConditions($sid,$gid,$qid);
*   * Get all questions dependents on question $qid in survey/group $sid/$gid indexed by targqid:
*       $result=GetGroupDepsForConditions($sid,$gid,"all",$qid,"by-targgid");
*/
function GetQuestDepsForConditions($sid,$gid="all",$depqid="all",$targqid="all",$indexby="by-depqid", $searchscope="samegroup")
{
    $clang = Yii::app()->lang;
    $condarray = Array();

    $baselang = GetBaseLanguageFromSurveyID($sid);
    $sqlgid="";
    $sqldepqid="";
    $sqltargqid="";
    $sqlsearchscope="";
    if ($gid != "all") {$gid = sanitize_int($gid); $sqlgid="AND tq.gid=$gid";}
    if ($depqid != "all") {$depqid = sanitize_int($depqid); $sqldepqid="AND tq.qid=$depqid";}
    if ($targqid != "all") {$targqid = sanitize_int($targqid); $sqltargqid="AND tq2.qid=$targqid";}
    if ($searchscope == "samegroup") {$sqlsearchscope="AND tq2.gid=tq.gid";}

    $condquery = "SELECT tq.qid as depqid, tq2.qid as targqid, tc.cid
    	FROM {{conditions}} AS tc, {{questions}} AS tq, {{questions}} AS tq2
    	WHERE tq.language='{$baselang}' AND tq2.language='{$baselang}' AND tc.qid = tq.qid AND tq.sid='$sid'
    	AND  tq2.qid=tc.cqid $sqlsearchscope $sqlgid $sqldepqid $sqltargqid";
	$condresult=Yii::app()->db->createCommand($condquery)->query();
    if ($condresult->getRowCount() > 0) {
        foreach ($condresult->readAll() as $condrow)
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

// TMSW Conditions->Relevance:  This function is not needed - could replace with a message from EM output.
/**
* checkMovequestionConstraintsForConditions()
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
function checkMovequestionConstraintsForConditions($sid,$qid,$newgid="all")
{
    $clang = Yii::app()->lang;
    $resarray=Array();
    $resarray['notAbove']=null; // defaults to no constraint
    $resarray['notBelow']=null; // defaults to no constraint
    $sid=sanitize_int($sid);
    $qid=sanitize_int($qid);

    if ($newgid != "all")
    {
        $newgid=sanitize_int($newgid);
        $newgorder=getGroupOrder($sid,$newgid);
    }
    else
    {
        $neworder=""; // Not used in this case
    }

    $baselang = GetBaseLanguageFromSurveyID($sid);

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

    $condresult=Yii::app()->db->createCommand($condquery)->query(); // or safe_die($connect->ErrorMsg());    //Checked

    if ($condresult->getRowCount() > 0) {

        foreach ($condresult->readAll() as $condrow )
        {
            // This Question can go up to the minimum GID on the 1st row
            $depqid=$condrow['depqid'];
            $depgid=$condrow['depgid'];
            $depgorder=$condrow['depgorder'];
            $targetqid=$condrow['targqid'];
            $targetgid=$condrow['targgid'];
            $targetgorder=$condrow['targgorder'];
            $condid=$condrow['cid'];
            //echo "This question can't go above to GID=$targetgid/order=$targetgorder because of CID=$condid";
            if ($newgid != "all")
            { // Get only constraints when trying to move to this group
                if ($newgorder < $targetgorder)
                {
                    $resarray['notAbove'][]=Array($targetgid,$targetgorder,$depqid,$condid);
                }
            }
            else
            { // get all moves constraints
                $resarray['notAbove'][]=Array($targetgid,$targetgorder,$depqid,$condid);
            }
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

    $condresult=Yii::app()->db->createCommand($condquery)->query(); // or safe_die($connect->ErrorMsg());        //Checked

    if ($condresult->getRowCount() > 0) {

        foreach ($condresult->readAll() as $condrow)
        {
            // This Question can go down to the maximum GID on the 1st row
            $depqid=$condrow['depqid'];
            $depgid=$condrow['depgid'];
            $depgorder=$condrow['depgorder'];
            $targetqid=$condrow['targqid'];
            $targetgid=$condrow['targgid'];
            $targetgorder=$condrow['targgorder'];
            $condid=$condrow['cid'];
            //echo "This question can't go below to GID=$depgid/order=$depgorder because of CID=$condid";
            if ($newgid != "all")
            { // Get only constraints when trying to move to this group
                if ($newgorder > $depgorder)
                {
                    $resarray['notBelow'][]=Array($depgid,$depgorder,$depqid,$condid);
                }
            }
            else
            { // get all moves constraints
                $resarray['notBelow'][]=Array($depgid,$depgorder,$depqid,$condid);
            }
        }
    }
    return $resarray;
}

function getusergrouplist($ugid=NULL,$outputformat='optionlist')
{
    $clang = Yii::app()->lang;
    //$squery = "SELECT ugid, name FROM ".db_table_name('user_groups') ." WHERE owner_id = {$_SESSION['loginID']} ORDER BY name";
    $squery = "SELECT a.ugid, a.name, a.owner_id, b.uid FROM {{user_groups}} AS a LEFT JOIN {{user_in_groups}} AS b ON a.ugid = b.ugid WHERE uid = ".Yii::app()->session['loginID']." ORDER BY name";

    $sresult = Yii::app()->db->createCommand($squery)->query(); //Checked
    if (!$sresult) {return "Database Error";}
    $selecter = "";
    foreach ($sresult->readAll() as $row)
    {
        $groupnames[] = $row;
    }


    //$groupnames = $sresult->GetRows();
    $simplegidarray=array();
    if (isset($groupnames))
    {
        foreach($groupnames as $gn)
        {
            $selecter .= "<option ";
            if(Yii::app()->session['loginID'] == $gn['owner_id']) {$selecter .= " style=\"font-weight: bold;\"";}
            //if (isset($_GET['ugid']) && $gn['ugid'] == $_GET['ugid']) {$selecter .= " selected='selected'"; $svexist = 1;}

            if ($gn['ugid'] == $ugid) {$selecter .= " selected='selected'"; $svexist = 1;}
            $link = Yii::app()->createUrl("admin/usergroups/view/".$gn['ugid']);
            $selecter .=" value='{$link}'>{$gn['name']}</option>\n";
            $simplegidarray[] = $gn['ugid'];
        }
    }

    if (!isset($svexist)) {$selecter = "<option value='-1' selected='selected'>".$clang->gT("Please choose...")."</option>\n".$selecter;}
    //else {$selecter = "<option value='-1'>".$clang->gT("None")."</option>\n".$selecter;}

    if ($outputformat == 'simplegidarray')
    {
        return $simplegidarray;
    }
    else
    {
        return $selecter;
    }
}

function getgroupuserlist($ugid)
{
    $CI =& get_instance();
    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;

    $ugid=sanitize_int($ugid);
    $surveyidquery = "SELECT a.uid, a.users_name FROM ".$CI->db->dbprefix."users AS a LEFT JOIN (SELECT uid AS id FROM ".$CI->db->dbprefix."user_in_groups WHERE ugid = {$ugid}) AS b ON a.uid = b.id WHERE id IS NULL ORDER BY a.users_name";

    $surveyidresult = db_execute_assoc($surveyidquery);  //Checked
    if (!$surveyidresult) {return "Database Error";}
    $surveyselecter = "";
    foreach ($surveyidresult->result_array() as $row)
    {
        $surveynames[] = $row;
    }
    //$surveynames = $surveyidresult->GetRows();
    if (isset($surveynames))
    {
        foreach($surveynames as $sv)
        {
            $surveyselecter .= "<option";
            $surveyselecter .=" value='{$sv['uid']}'>{$sv['users_name']}</option>\n";
        }
    }
    $surveyselecter = "<option value='-1' selected='selected'>".$clang->gT("Please choose...")."</option>\n".$surveyselecter;
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
* @uses $dbprefix
* @param string $sqlfile The path where a file with sql commands can be found on the server.
* @param string $sqlstring If no path is supplied then a string with semicolon delimited sql
* commands can be supplied in this argument.
* @return bool Returns true if database was modified successfully.
*/
function modify_database($sqlfile='', $sqlstring='')
{
    $CI =& get_instance();
    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;

    global $siteadminemail;
    global $siteadminname;
    global $codeString;
    global $modifyoutput;

    //require_once($homedir."/classes/core/sha256.php");
    $CI->load->library('admin/sha256');
    $success = true;  // Let's be optimistic
    $modifyoutput='';

    if (!empty($sqlfile)) {
        if (!is_readable($sqlfile)) {
            $success = false;
            echo '<p>Tried to modify database, but "'. $sqlfile .'" doesn\'t exist!</p>';
            return $success;
        } else {
            $lines = file($sqlfile);
        }
    } else {
        $sqlstring = trim($sqlstring);
        if ($sqlstring{strlen($sqlstring)-1} != ";") {
            $sqlstring .= ";"; // add it in if it's not there.
        }
        $lines[] = $sqlstring;
    }

    $command = '';

    foreach ($lines as $line) {
        $line = rtrim($line);
        $length = strlen($line);

        if ($length and $line[0] <> '#' and substr($line,0,2) <> '--') {
            if (substr($line, $length-1, 1) == ';') {
                $line = substr($line, 0, $length-1);   // strip ;
                $command .= $line;
                $command = str_replace('prefix_', $CI->db->dbprefix, $command); // Table prefixes
                $command = str_replace('$defaultuser', Yii::app()->getConfig('defaultuser'), $command);
                $command = str_replace('$defaultpass', $CI->sha256->hashing(Yii::app()->getConfig('defaultpass')), $command);
                $command = str_replace('$siteadminname', $siteadminname, $command);
                $command = str_replace('$siteadminemail', $siteadminemail, $command);
                $command = str_replace('$defaultlang', Yii::app()->getConfig('defaultlang'), $command);
                $command = str_replace('$sessionname', 'ls'.sRandomChars(20,'123456789'), $command);
                $command = str_replace('$databasetabletype', $CI->db->dbdriver, $command);

                if (!$CI->db->query($command)) {  //Checked
                    $command=htmlspecialchars($command);
                    $modifyoutput .="<br />".sprintf($clang->gT("SQL command failed: %s"),"<span style='font-size:10px;'>".$command."</span>","<span style='color:#ee0000;font-size:10px;'></span><br/>");
                    $success = false;
                }
                else
                {
                    $command=htmlspecialchars($command);
                    $modifyoutput .=". ";
                }

                $command = '';
            } else {
                $command .= $line;
            }
        }
    }

    return $success;

}

function getlabelsets($languages=null)
// Returns a list with label sets
// if the $languages paramter is provided then only labelset containing all of the languages in the paramter are provided
{
    $clang = Yii::app()->lang;
    if ($languages){
        $languages=sanitize_languagecodeS($languages);
        $languagesarray=explode(' ',trim($languages));
    }
    $query = "SELECT {{labelsets}}.lid as lid, label_name FROM {{labelsets}}";
    if ($languages){
        $query .=" where ";
        foreach  ($languagesarray as $item)
        {
            $query .=" ((languages like '% $item %') or (languages='$item') or (languages like '% $item') or (languages like '$item %')) and ";
        }
        $query .=" 1=1 ";
    }
    $query .=" order by label_name";
    $result = Yii::app()->db->createCommand($query)->query(); //Checked
    $labelsets=array();
    foreach ($result->readAll() as $row)
    {
        $labelsets[] = array($row['lid'], $row['label_name']);
    }
    return $labelsets;
}

function getHeader($meta = false)
{
    global $embedded;

    $surveyid = Yii::app()->getConfig('sid');
    Yii::app()->loadHelper('surveytranslator');
    $clang = $CI->limesurvey_lang;

    if (!empty(Yii::app()->session['s_lang']))
    {
        $surveylanguage= Yii::app()->session['s_lang'];
    }
    elseif (isset($surveyid) && $surveyid)
    {
        $surveylanguage=GetBaseLanguageFromSurveyID($surveyid);
    }
    else
    {
        $surveylanguage=Yii::app()->getConfig('defaultlang');
    }

    $js_header = ''; $css_header='';
    if(Yii::app()->getConfig("js_admin_includes"))
    {
        foreach (Yii::app()->getConfig("js_admin_includes") as $jsinclude)
        {
            if (substr($jsinclude,0,4) == 'http')
                $js_header .= "<script type=\"text/javascript\" src=\"$jsinclude\"></script>\n";
            else
                $js_header .= "<script type=\"text/javascript\" src=\"".Yii::app()->baseUrl."$jsinclude\"></script>\n";
        }
    }
    if(Yii::app()->getConfig("css_admin_includes"))
    {
        foreach (Yii::app()->getConfig("css_admin_includes") as $cssinclude)
        {
            $css_header .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"".Yii::app()->baseUrl.$cssinclude."\" />\n";
        }
    }

    if ( !$embedded )
    {
        $header=  "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n"
        . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"".$surveylanguage."\" lang=\"".$surveylanguage."\"";
        if (getLanguageRTL($surveylanguage))
        {
            $header.=" dir=\"rtl\" ";
        }
        $header.= ">\n\t<head>\n"
        . $css_header
        . "<script type=\"text/javascript\" src=\"".Yii::app()->getConfig('generalscripts')."jquery/jquery.js\"></script>\n"
        . "<script type=\"text/javascript\" src=\"".Yii::app()->getConfig('generalscripts')."jquery/jquery-ui.js\"></script>\n"
        . "<link href=\"".Yii::app()->getConfig('generalscripts')."jquery/css/start/jquery-ui.css\" media=\"all\" type=\"text/css\" rel=\"stylesheet\" />"
        . "<link href=\"".Yii::app()->getConfig('generalscripts')."jquery/css/start/lime-progress.css\" media=\"all\" type=\"text/css\" rel=\"stylesheet\" />"
        . $js_header;

        if ($meta)
            $header .= $meta;

        return $header;
    }

    global $embedded_headerfunc;

    if ( function_exists( $embedded_headerfunc ) )
        return $embedded_headerfunc();
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
    <script type="text/javascript" src="'.$rooturl.'/scripts/jquery/jquery.js"></script>
    <script type="text/javascript" src="'.$homeurl.'/scripts/printablesurvey.js"></script>

    <!--[if lt IE 7]>
    <script type="text/javascript" src="'.$homeurl.'/scripts/DD_belatedPNG_0.0.8a-min.js"></script>
    <script>
    DD_belatedPNG.fix("img");
    </script>
    <![endif]-->
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

function get_dbtableusage($surveyid){
    $CI =& get_instance();
    $CI->load->helper('admin/activate');
    $arrCols = activateSurvey($surveyid,$surveyid,'admin.php',true);

    $length = 1;
    foreach ($arrCols['fields'] as $col){
        switch ($col[0]){
            case 'C':
                $length = $length + ($col[1]*3) + 1;
                break;
            case 'X':
            case 'B':
                $length = $length + 12;
                break;
            case 'D':
                $length = $length + 3;
                break;
            case 'T':
            case 'TS':
            case 'N':
                $length = $length + 8;
                break;
            case 'L':
                $legth++;
                break;
            case 'I':
            case 'I4':
            case 'F':
                $length = $length + 4;
                break;
            case 'I1':
                $length = $length + 1;
                break;
            case 'I2':
                $length = $length + 2;
                break;
            case 'I8':
                $length = $length + 8;
                break;
        }
    }
    if ($arrCols['dbtype'] == 'mysql' || $arrCols['dbtype'] == 'mysqli'){
        if ($arrCols['dbengine']=='myISAM'){
            $hard_limit = 4096;
        }
        elseif ($arrCols['dbengine'] == "InnoDB"){
            $hard_limit = 1000;
        }
        else{
            return false;
        }

        $size_limit = 65535;
    }
    elseif ($arrCols['dbtype'] == 'postgre'){
        $hard_limit = 1600;
        $size_limit = 0;
    }
    elseif ($arrCols['dbtype'] == 'mssql'){
        $hard_limit = 1024;
        $size_limit = 0;
    }
    else{
        return false;
    }

    $columns_used = count($arrCols['fields']);



    return (array( 'dbtype'=>$arrCols['dbtype'], 'column'=>array($columns_used,$hard_limit) , 'size' => array($length, $size_limit) ));
}

/**
*  Checks that each object from an array of CSV data [question-rows,answer-rows,labelsets-row] supports at least a given language
*
* @param mixed $csvarray array with a line of csv data per row
* @param mixed $idkeysarray  array of integers giving the csv-row numbers of the object keys
* @param mixed $langfieldnum  integer giving the csv-row number of the language(s) filed
*        ==> the language field  can be a single language code or a
*            space separated language code list
* @param mixed $langcode  the language code to be tested
* @param mixed $hasheader  if we should strip off the first line (if it contains headers)
*/
function  bDoesImportarraySupportsLanguage($csvarray,$idkeysarray,$langfieldnum,$langcode, $hasheader = false)
{
    // An array with one row per object id and langsupport status as value
    $objlangsupportarray=Array();
    if ($hasheader === true)
    { // stripping first row to skip headers if any
        array_shift($csvarray);
    }

    foreach ($csvarray as $csvrow)
    {
        $rowcontents = convertCSVRowToArray($csvrow,',','"');
        $rowid = "";
        foreach ($idkeysarray as $idfieldnum)
        {
            $rowid .= $rowcontents[$idfieldnum]."-";
        }
        $rowlangarray = explode (" ", @$rowcontents[$langfieldnum]);
        if (!isset($objlangsupportarray[$rowid]))
        {
            if (array_search($langcode,$rowlangarray)!== false)
            {
                $objlangsupportarray[$rowid] = "true";
            }
            else
            {
                $objlangsupportarray[$rowid] = "false";
            }
        }
        else
        {
            if ($objlangsupportarray[$rowid] == "false" &&
            array_search($langcode,$rowlangarray) !== false)
            {
                $objlangsupportarray[$rowid] = "true";
            }
        }
    } // end foreach rown

    // If any of the object doesn't support the given language, return false
    if (array_search("false",$objlangsupportarray) === false)
    {
        return true;
    }
    else
    {
        return false;
    }
}

/** This function checks to see if there is an answer saved in the survey session
* data that matches the $code. If it does, it returns that data.
* It is used when building a questions text to allow incorporating the answer
* to an earlier question into the text of a later question.
* IE: Q1: What is your name? [Jason]
*     Q2: Hi [Jason] how are you ?
* This function is called from the retriveAnswers function.
*
* @param mixed $code
* @param mixed $phpdateformat  The date format in which any dates are shown
* @return mixed returns the answerText from session variable corresponding to a question code
*/
function retrieve_Answer($surveyid, $code, $phpdateformat=null)
{
    //This function checks to see if there is an answer saved in the survey session
    //data that matches the $code. If it does, it returns that data.
    //It is used when building a questions text to allow incorporating the answer
    //to an earlier question into the text of a later question.
    //IE: Q1: What is your name? [Jason]
    //    Q2: Hi [Jason] how are you ?
    //This function is called from the retriveAnswers function.
    $CI =& get_instance();
    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;

    //Find question details
    if (isset($_SESSION[$code]))
    {
        $questiondetails=getsidgidqidaidtype($code);
        //the getsidgidqidaidtype function is in common.php and returns
        //a SurveyID, GroupID, QuestionID and an Answer code
        //extracted from a "fieldname" - ie: 1X2X3a
        // also returns question type

        if ($questiondetails['type'] == "M" ||
        $questiondetails['type'] == "P")
        {
            $query="SELECT * FROM ".$CI->db->dbprefix."questions WHERE parent_qid='".$questiondetails['qid']."' AND language='".$_SESSION['s_lang']."'";
            $result=db_execute_assoc($query) or safe_die("Error getting answer<br />$query<br />");
            foreach ($result->result_array() as  $row)
            {
                if (isset($_SESSION[$code.$row['title']]) && $_SESSION[$code.$row['title']] == "Y")
                {
                    $returns[] = $row['question'];
                }
                elseif (isset($_SESSION[$code]) && $_SESSION[$code] == "Y" && $questiondetails['aid']==$row['title'])
                {
                    return $row['question'];
                }
            }
            if (isset($_SESSION[$code."other"]) && $_SESSION[$code."other"])
            {
                $returns[]=$_SESSION[$code."other"];
            }
            if (isset($returns))
            {
                $return=implode(", ", $returns);
                if (strpos($return, ","))
                {
                    $return=substr_replace($return, " &", strrpos($return, ","), 1);
                }
            }
            else
            {
                $return=$clang->gT("No answer");
            }
        }
        elseif (!$_SESSION[$code] && $_SESSION[$code] !=0)
        {
            $return=$clang->gT("No answer");
        }
        else
        {
            $return=getextendedanswer($surveyid, NULL, $code, $_SESSION[$code], 'INSERTANS');
        }
    }
    else
    {
        $return=$clang->gT("Error") . "($code)";
    }
    return html_escape($return);
}

/**
* Retrieve a HTML <OPTION> list of survey admin users
*
* @param mixed $bIncludeOwner If the survey owner should be included
* @param mixed $bIncludeSuperAdmins If Super admins should be included
* @param int surveyid
* @return string
*/
function sGetSurveyUserlist($bIncludeOwner=true, $bIncludeSuperAdmins=true,$surveyid)
{
    $clang = Yii::app()->lang;
    $surveyid=sanitize_int($surveyid);

    $sSurveyIDQuery = "SELECT a.uid, a.users_name, a.full_name FROM {{users}} AS a
    LEFT OUTER JOIN (SELECT uid AS id FROM {{survey_permissions}} WHERE sid = {$surveyid}) AS b ON a.uid = b.id
    WHERE id IS NULL ";
    if (!$bIncludeSuperAdmins)
    {
        $sSurveyIDQuery.='and superadmin=0 ';
    }
    $sSurveyIDQuery.= 'ORDER BY a.users_name';
    $surveyidresult = Yii::app()->db->createCommand($sSurveyIDQuery)->query();  //Checked

    //if ($surveyidresult->num_rows() == 0) {return "Database Error";}
    $surveyselecter = "";
    //$surveynames = $surveyidresult->GetRows();

    if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == true)
    {

        $authorizedUsersList = getuserlist('onlyuidarray');
    }

    if ($surveyidresult->getRowCount() > 0)
    {
        foreach($surveyidresult->readAll() as $sv)
        {
            if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == false ||
            in_array($sv['uid'],$authorizedUsersList))
            {
                $surveyselecter .= "<option";
                $surveyselecter .=" value='{$sv['uid']}'>{$sv['users_name']} {$sv['full_name']}</option>\n";
            }
        }
    }
    if (!isset($svexist)) {$surveyselecter = "<option value='-1' selected='selected'>".$clang->gT("Please choose...")."</option>\n".$surveyselecter;}
    else {$surveyselecter = "<option value='-1'>".$clang->gT("None")."</option>\n".$surveyselecter;}

    return $surveyselecter;
}

function getsurveyusergrouplist($outputformat='htmloptions',$surveyid)
{
    $clang = Yii::app()->lang;
    $surveyid=sanitize_int($surveyid);

	$surveyidquery = "SELECT a.ugid, a.name, MAX(d.ugid) AS da
						FROM {{user_groups}} AS a
						LEFT JOIN (
							SELECT b.ugid
							FROM {{user_in_groups}} AS b
								LEFT JOIN (SELECT * FROM {{survey_permissions}}
							WHERE sid = {$surveyid}) AS c ON b.uid = c.uid WHERE c.uid IS NULL
						) AS d ON a.ugid = d.ugid GROUP BY a.ugid, a.name HAVING MAX(d.ugid) IS NOT NULL";
    $surveyidresult = Yii::app()->db->createCommand($surveyidquery)->query();  //Checked

    //if ($surveyidresult->num_rows() == 0) {return "Database Error";}
    $surveyselecter = "";
    //$surveynames = $surveyidresult->GetRows();

    if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == true)
    {
        $authorizedGroupsList=getusergrouplist('simplegidarray');
    }

    if ($surveyidresult->getRowCount() > 0)
    {
        foreach($surveyidresult->readAll() as $sv)
        {
            if (Yii::app()->getConfig('usercontrolSameGroupPolicy') == false ||
            in_array($sv['ugid'],$authorizedGroupsList))
            {
                $surveyselecter .= "<option";
                $surveyselecter .=" value='{$sv['ugid']}'>{$sv['name']}</option>\n";
                $simpleugidarray[] = $sv['ugid'];
            }
        }
    }
    if (!isset($svexist)) {$surveyselecter = "<option value='-1' selected='selected'>".$clang->gT("Please choose...")."</option>\n".$surveyselecter;}
    else {$surveyselecter = "<option value='-1'>".$clang->gT("None")."</option>\n".$surveyselecter;}

    if ($outputformat == 'simpleugidarray')
    {
        return $simpleugidarray;
    }
    else
    {
        return $surveyselecter;
    }
}

/*
* Emit the standard (last) onsubmit handler for the survey.
*
* This code in injected in the three questionnaire modes right after the <form> element,
* before the individual questions emit their own onsubmit replacement code.
*/
function sDefaultSubmitHandler()
{
    return <<<EOS
    <script type='text/javascript'>
    <!--
        // register the standard (last) onsubmit handler *first*
        document.limesurvey.onsubmit = std_onsubmit_handler;
    -->
    </script>
EOS;
}

/**
* This function fixes the group ID and type on all subquestions
*
*/
function fixSubquestions()
{
    $surveyidresult=Yii::app()->db->createCommand("select sq.qid, sq.parent_qid, sq.gid as sqgid, q.gid, sq.type as sqtype, q.type
    from {{questions}} sq JOIN {{questions}} q on sq.parent_qid=q.qid
    where sq.parent_qid>0 and  (sq.gid!=q.gid or sq.type!=q.type)")->query();
    foreach($surveyidresult->readAll() as $sv)
    {
        Yii::app()->db->createCommand("update {{questions}} set type='{$sv['type']}', gid={$sv['gid']} where qid={$sv['qid']}")->query();
    }

}

/**
* Must use ls_json_encode to json_encode content, otherwise LimeExpressionManager will think that the associative arrays are expressions and try to parse them.
*/
function ls_json_encode($content)
{
    return preg_replace('/\{\"/','{ "',json_encode($content));
}

/**
* Swaps two positions in an array
*
* @param mixed $key1
* @param mixed $key2
* @param mixed $array
*/
function array_swap_assoc($key1, $key2, $array) {
    $newArray = array ();
    foreach ($array as $key => $value) {
        if ($key == $key1) {
            $newArray[$key2] = $array[$key2];
        } elseif ($key == $key2) {
            $newArray[$key1] = $array[$key1];
        } else {
            $newArray[$key] = $value;
        }
    }
    return $newArray;
}

function checkgroupfordisplay($gid,$anonymized,$surveyid)
{
    return LimeExpressionManager::GroupIsRelevant($gid);
    // TMSW Conditions->Relevance:  The rest of this function is not needed

    //This function checks all the questions in a group to see if they have
    //conditions, and if the do - to see if the conditions are met.
    //If none of the questions in the group are set to display, then
    //the function will return false, to indicate that the whole group
    //should not display at all.
    $CI =& get_instance();
    //$_SESSION = $CI->session->userdata;

    $countQuestionsInThisGroup=0;
    $countConditionalQuestionsInThisGroup=0;
    $countQuestionsWithRelevanceIntThisGroup=0;

    // Initialize LimeExpressionManager for this group - this ensures that values from prior pages are available for assessing relevance on this page
    LimeExpressionManager::StartProcessingPage(false);
    LimeExpressionManager::StartProcessingGroup($gid,$anonymized,$surveyid);

    foreach ($_SESSION['fieldarray'] as $ia) //Run through all the questions

    {
        if ($ia[5] == $gid) //If the question is in the group we are checking:

        {
            // Check if this question is hidden
            $qidattributes=getQuestionAttributeValues($ia[0]);
            if ($qidattributes!==false && ($qidattributes['hidden']==0 || $ia[4]=='*'))
            {
                $countQuestionsInThisGroup++;
                if ($ia[7] == "Y") //This question is conditional

                {
                    $countConditionalQuestionsInThisGroup++;
                    $QuestionsWithConditions[]=$ia; //Create an array containing all the conditional questions
                }
                if (isset($qidattributes['relevance']) && ($qidattributes['relevance'] != 1))
                {
                    $countQuestionsWithRelevanceIntThisGroup++;
                    $QuestionsWithRelevance[]=$qidattributes['relevance'];  // Create an array containing all of the questions whose Relevance Equaation must be processed.
                }
            }
        }
    }
    if ($countQuestionsInThisGroup===0)
    {
        return false;
    }
    elseif ($countQuestionsInThisGroup != $countConditionalQuestionsInThisGroup || !isset($QuestionsWithConditions)
    && ($countQuestionsInThisGroup != $countQuestionsWithRelevanceIntThisGroup || !isset($QuestionsWithRelevance)))
    {
        //One of the questions in this group is NOT conditional, therefore
        //the group MUST be displayed
        return true;
    }
    else
    {
        //All of the questions in this group are conditional. Now we must
        //check every question, to see if the condition for each has been met.
        //If 1 or more have their conditions met, then the group should
        //be displayed.
        foreach ($QuestionsWithConditions as $cc)
        {
            if (checkquestionfordisplay($cc[0], $gid) === true)
            {
                return true;
            }
        }
        if (isset($QuestionsWithRelevance)) {
            foreach ($QuestionsWithRelevance as $relevance)
            {
                if (LimeExpressionManager::ProcessRelevance($relevance))
                {
                    return true;
                }
            }
        }
        //Since we made it this far, there mustn't have been any conditions met.
        //Therefore the group should not be displayed.
        return false;
    }
}

function db_quote_id($id)
{
	// WE DONT HAVE nor USE other thing that alfanumeric characters in the field names
	//  $quote = $connect->nameQuote;
	//  return $quote.str_replace($quote,$quote.$quote,$id).$quote;

	switch (Yii::app()->db->createCommand($id))
	{
		case "mysqli" :
		case "mysql" :
			return "`".$id."`";
			break;
		case "mssql_n" :
		case "mssql" :
		case "mssqlnative" :
		case "odbc_mssql" :
			return "[".$id."]";
			break;
		case "postgre":
			return "\"".$id."\"";
			break;
		default:
			return "`".$id."`";
	}
}
// Closing PHP tag intentionally omitted - yes, it is okay
