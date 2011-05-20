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
 *	$Id: common_functions.php 10063 2011-05-13 15:31:20Z c_schmitz $
 *	Files Purpose: lots of common functions
 */
$CI =& get_instance(); 
$CI->load->helper('sanitize');
 

/**
* This function gives back an array that defines which survey permissions and what part of the CRUD+Import+Export subpermissions is available.
* - for example it would not make sense to have  a 'create' permissions for survey locale settings as they exist with every survey
*  so the editor for survey permission should not show a checkbox here, therfore the create element of that permission is set to 'false'
*  If you want to generally add a new permission just add it here.
* 
*/

function aGetBaseSurveyPermissions()
{
    global $clang;
    $aPermissions=array(                                                
                    'assessments'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Assessments"),'description'=>$clang->gT("Permission to create/view/update/delete assessments rules for a survey"),'img'=>'assessments'),  // Checked
                    'quotas'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Quotas"),'description'=>$clang->gT("Permission to create/view/update/delete quota rules for a survey"),'img'=>'quota'), // Checked
                    'responses'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Responses"),'description'=>$clang->gT("Permission to create(data entry)/view/update/delete/import/export responses"),'img'=>'browse'),        
                    'statistics'=>array('create'=>false,'read'=>true,'update'=>false,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Statistics"),'description'=>$clang->gT("Permission to view statistics"),'img'=>'statistics'),    //Checked  
                    'survey'=>array('create'=>false,'read'=>true,'update'=>false,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey deletion"),'description'=>$clang->gT("Permission to delete a survey"),'img'=>'delete'),   //Checked           
                    'surveyactivation'=>array('create'=>false,'read'=>false,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey activation"),'description'=>$clang->gT("Permission to activate/deactivate a survey"),'img'=>'activate_deactivate'),  //Checked  
                    'surveycontent'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Survey content"),'description'=>$clang->gT("Permission to create/view/update/delete/import/export the questions, groups, answers & conditions of a survey"),'img'=>'add'),
                    'surveylocale'=>array('create'=>false,'read'=>true,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey locale settings"),'description'=>$clang->gT("Permission to view/update the survey locale settings"),'img'=>'edit'),    
                    'surveysecurity'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey security"),'description'=>$clang->gT("Permission to modify survey security settings"),'img'=>'survey_security'), 
                    'surveysettings'=>array('create'=>false,'read'=>true,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Survey settings"),'description'=>$clang->gT("Permission to view/update the survey settings including token table creation"),'img'=>'survey_settings'),       
                    'tokens'=>array('create'=>true,'read'=>true,'update'=>true,'delete'=>true,'import'=>true,'export'=>true,'title'=>$clang->gT("Tokens"),'description'=>$clang->gT("Permission to create/update/delete/import/export token entries"),'img'=>'tokens'), 
                    'translations'=>array('create'=>false,'read'=>true,'update'=>true,'delete'=>false,'import'=>false,'export'=>false,'title'=>$clang->gT("Quick translation"),'description'=>$clang->gT("Permission to view & update the translations using the quick-translation feature"),'img'=>'translate')
                    );
   uasort($aPermissions,"aComparePermission");    
   return $aPermissions;                 
}

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
 * @global string $publicurl
 * @global string $sourcefrom
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
    global $publicurl;
    global $sourcefrom, $clang;

    if (!isset($clang))
    {
        $lang = array('en');
        $CI->load->library('Limesurvey_lang',$lang);
        
        $clang = $CI->limesurvey_lang;
    }
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
            foreach($members as $TypeCode=>$TypeProperties){
                $qtypeselecter .= "<option value='$TypeCode'";
                if ($SelectedCode == $TypeCode) {$qtypeselecter .= " selected='selected'";}
                $qtypeselecter .= ">{$TypeProperties['description']}</option>\n";
            }
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
 * @param mixed $returnarray   boolean - if set to true an array instead of an HTML option list is given back
 *
 * @global string $surveyid
 * @global string $dbprefix
 * @global string $scriptname
 * @global string $connect
 * @global string $clang
 *
 * @return string This string is returned containing <option></option> formatted list of existing surveys
 *
 */
function getsurveylist($returnarray=false,$returnwithouturl=false)
{
    global $surveyid, $clang, $timeadjust,$CI;
    static $cached = null;
    
    $CI->load->config('lsconfig');
    if(is_null($cached)) {
        $CI->load->model('surveys_languagesettings');
        $surveyidresult = $CI->surveys_languagesettings->getAllSurveys(!bHasGlobalPermission('USER_RIGHT_SUPERADMIN'));
        
        if (!$surveyidresult) {return "Database Error";}
        $surveynames = $surveyidresult->result_array();
        $cached=$surveynames;
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
                if($this->session->userdata("loginID") == $sv['owner_id'])
                {
                    $inactivesurveys .= " style=\"font-weight: bold;\"";
                }
                if ($sv['sid'] == $surveyid)
                {
                    $inactivesurveys .= " selected='selected'"; $svexist = 1;
                }
                if ($returnwithouturl===false)
                {
                    $inactivesurveys .=" value='".$this->config->item('scriptname')."?sid={$sv['sid']}'>{$surveylstitle}</option>\n";
                } else
                {
                    $inactivesurveys .=" value='{$sv['sid']}'>{$surveylstitle}</option>\n";
                }
            } elseif($sv['expires']!='' && $sv['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust))
            {
                $expiredsurveys .="<option ";
                if ($this->session->userdata("loginID") == $sv['owner_id'])
                {
                    $expiredsurveys .= " style=\"font-weight: bold;\"";
                }
                if ($sv['sid'] == $surveyid)
                {
                    $expiredsurveys .= " selected='selected'"; $svexist = 1;
                }
                if ($returnwithouturl===false)
                {
                    $expiredsurveys .=" value='".$this->config->item('scriptname')."?sid={$sv['sid']}'>{$surveylstitle}</option>\n";
                } else
                {
                    $expiredsurveys .=" value='{$sv['sid']}'>{$surveylstitle}</option>\n";
                }
            } else
            {
                $activesurveys .= "<option ";
                if($this->session->userdata("loginID") == $sv['owner_id'])
                {
                    $activesurveys .= " style=\"font-weight: bold;\"";
                }
                if ($sv['sid'] == $surveyid)
                {
                    $activesurveys .= " selected='selected'"; $svexist = 1;
                }
                if ($returnwithouturl===false)
                {
                    $activesurveys .=" value='".$this->config->item('scriptname')."?sid={$sv['sid']}'>{$surveylstitle}</option>\n";
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
            $surveyselecter = "<option value='".$this->config->item('scriptname')."?sid='>".$clang->gT("None")."</option>\n".$surveyselecter;
        } else
        {
            $surveyselecter = "<option value=''>".$clang->gT("None")."</option>\n".$surveyselecter;
        }
    }
    return $surveyselecter;
}

/**
 * getQuestions() queries the database for an list of all questions matching the current survey and group id
 *
 * @global string $surveyid
 * @global string $gid
 * @global string $selectedqid
 *
 * @return This string is returned containing <option></option> formatted list of questions in the current survey and group
 */
function getQuestions($surveyid,$gid,$selectedqid)
{
    global $clang,$CI;
    $CI->load->config('lsconfig');
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->model('questions');
    $qresult = $CI->questions->getQuestions($surveyid,$gid,$s_lang);
    $qrows = $qresult->result_array();

    if (!isset($questionselecter)) {$questionselecter="";}
    foreach ($qrows as $qrow)
    {
        $qrow['title'] = strip_tags($qrow['title']);
        $questionselecter .= "<option value='".$this->config->item('scriptname')."?sid=$surveyid&amp;gid=$gid&amp;qid={$qrow['qid']}'";
        if ($selectedqid == $qrow['qid']) {$questionselecter .= " selected='selected'"; $qexists="Y";}
        $questionselecter .=">{$qrow['title']}:";
        $questionselecter .= " ";
        $question=FlattenText($qrow['question']);
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
    global $CI, $clang;

    if (!$surveyid) {$surveyid=returnglobal('sid');}
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->model('groups');
    //$gquery = "SELECT gid FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='{$s_lang}' ORDER BY group_order";
    $qresult = $CI->groups->getGroupID($surveyid,$s_lang); //checked
    $qrows = $qresult->result_array();

    $i = 0;
    $iPrev = -1;
    if ($qrows->num_rows() > 0)
    {
        foreach ($qrows as $qrow)
        {
            if ($gid == $qrow['gid']) {$iPrev = $i - 1;}
            $i += 1;
        }
    }
    if ($iPrev >= 0) {$GidPrev = $qrows[$iPrev]['gid'];}
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
    global $CI, $clang;
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->model('questions');
    //$qquery = 'SELECT * FROM '.db_table_name('questions')." WHERE sid=$surveyid AND gid=$gid AND language='{$s_lang}' and parent_qid=0 order by question_order";
    $qresult = $CI->questions->getQuestions($surveyid,$gid,$s_lang); //checked
    $qrows = $qresult->result_array();

    $i = 0;
    $iPrev = -1;
    if ($qrows->num_rows() > 0)
    {
        foreach ($qrows as $qrow)
        {
            if ($qid == $qrow['qid']) {$iPrev = $i - 1;}
            $i += 1;
        }
    }
    if ($iPrev >= 0) {$QidPrev = $qrows[$iPrev]['qid'];}
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
    global $CI, $clang;

    if (!$surveyid) {$surveyid=returnglobal('sid');}
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->model('groups');
    //$gquery = "SELECT gid FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='{$s_lang}' ORDER BY group_order";
    $qresult = $CI->groups->getGroupID($surveyid,$s_lang); //checked
    $qrows = $qresult->result_array();

    $GidNext="";
    $i = 0;
    $iNext = 1;
    if ($qrows->num_rows() > 0)
    {
        foreach ($qrows as $qrow)
        {
            if ($gid == $qrow['gid']) {$iNext = $i + 1;}
            $i += 1;
        }
    }
    if ($iNext < count($qrows)) {$GidNext = $qrows[$iNext]['gid'];}
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
    global $CI, $clang;
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->model('questions');
    //$qquery = 'SELECT qid FROM '.db_table_name('questions')." WHERE sid=$surveyid AND gid=$gid AND language='{$s_lang}' and parent_qid=0 order by question_order";
    $qresult = $CI->questions->getQuestionID($surveyid,$gid,$s_lang); //checked)
    $qrows = $qresult->result_array();

    $i = 0;
    $iNext = 1;
    if ($qrows->num_rows() > 0)
    {
        foreach ($qrows as $qrow)
        {
            if ($qid == $qrow['qid']) {$iNext = $i + 1;}
            $i += 1;
        }
    }
    if ($iNext < count($qrows)) {$QidNext = $qrows[$iNext]['qid'];}
    else {$QidNext = "";}
    return $QidNext;
}



/**
 * Gets number of groups inside a particular survey
 *
 * @param string $surveyid
 * @param mixed $lang
 */
function getGroupSum($surveyid, $lang)
{
    global $surveyid,$CI ;
    $CI->load->model('groups');
    $condn = "WHERE sid=$surveyid AND language='".$lang."'"; //Getting a count of questions for this survey

    $sumresult3 = $CI->groups->getAllRecords($condn); //Checked)
    $groupscount = $sumresult3->num_rows();

    return $groupscount ;
}


/**
 * Gets number of questions inside a particular group
 *
 * @param string $surveyid
 * @param mixed $groupid
 */
function getQuestionSum($surveyid, $groupid)
{
    global $surveyid,$CI ;
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->model('questions');
    $condn = "WHERE gid=$groupid and sid=$surveyid AND language='{$s_lang}'"; //Getting a count of questions for this survey
    
    $sumresult3 = $CI->questions->getAllRecords($condn); //Checked
    $questionscount = $sumresult3->num_rows();
    return $questionscount ;
}


/**
 * getMaxgrouporder($surveyid) queries the database for the maximum sortorder of a group and returns the next higher one.
 *
 * @param mixed $surveyid
 * @global string $surveyid
 */
function getMaxgrouporder($surveyid)
{
    global $surveyid, $CI ;
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->model('groups');
    //$max_sql = "SELECT max( group_order ) AS max FROM ".db_table_name('groups')." WHERE sid =$surveyid AND language='{$s_lang}'" ;
    $query = $CI->groups->getMaximumGroupOrder($surveyid,$s_lang);
    $query = $query->row_array();
    $current_max = $query['max'];
    
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
    global $CI;
    $CI->load->model('groups');
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    
    //$grporder_sql = "SELECT group_order FROM ".db_table_name('groups')." WHERE sid =$surveyid AND language='{$s_lang}' AND gid=$gid" ;
    $grporder_result =$CI->groups->getOrderOfGroup($surveyid,$gid,$s_lang); //Checked
    $grporder_row = $grporder_result->row_array() ;
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
 * @global string $surveyid
 */
function getMaxquestionorder($gid)
{
    global $surveyid ,$CI;
    $gid=sanitize_int($gid);
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->model('questions');
    //$max_sql = "SELECT max( question_order ) AS max FROM ".db_table_name('questions')." WHERE gid='$gid' AND language='$s_lang'";

    $max_result =$CI->questions->getMaximumQuestionOrder($gid,$s_lang); ; //Checked
    $maxrow = $max_result->row_array() ;
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
    global $clang;
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
 * @global string $surveyid
 * @global string $dbprefix
 * @global string $scriptname
 *
 * @param string $gid - the currently selected gid/group
 *
 * @return This string is returned containing <option></option> formatted list of groups to current survey
 */
function getgrouplist($gid)
{
    global $surveyid, $CI, $clang;
    $groupselecter="";
    $gid=sanitize_int($gid);
    $surveyid=sanitize_int($surveyid);
    if (!$surveyid) {$surveyid=returnglobal('sid');}
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->config('lsconfig');
    $CI->load->model('groups');
    //$gidquery = "SELECT gid, group_name FROM ".db_table_name('groups')." WHERE sid='{$surveyid}' AND  language='{$s_lang}'  ORDER BY group_order";
    $gidresult = $CI->groups->getGroupAndID($surveyid,$s_lang) or safe_die("Couldn't get group list in common_helper.php<br />"); //Checked
    foreach ($gidresult->result_array() as $gv)
    {
        $groupselecter .= "<option";
        if ($gv['gid'] == $gid) {$groupselecter .= " selected='selected'"; $gvexist = 1;}
        $groupselecter .= " value='".$CI->config->item('scriptname')."?sid=$surveyid&amp;gid=".$gv['gid']."'>".htmlspecialchars($gv['group_name'])."</option>\n";
    } 
    if ($groupselecter)
    {
        if (!isset($gvexist)) {$groupselecter = "<option selected='selected'>".$clang->gT("Please choose...")."</option>\n".$groupselecter;}
        else {$groupselecter .= "<option value='".$CI->config->item('scriptname')."?sid=$surveyid&amp;gid='>".$clang->gT("None")."</option>\n";}
    }
    return $groupselecter;
}


function getgrouplist2($gid)
{
    global $surveyid, $CI, $clang;
    $groupselecter = "";
    if (!$surveyid) {$surveyid=returnglobal('sid');}
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->model('groups');
    //$gidquery = "SELECT gid, group_name FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='{$s_lang}' ORDER BY group_order";
    $gidresult = $CI->groups->getGroupAndID($surveyid,$s_lang) or safe_die("Plain old did not work!");   //Checked

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


function getgrouplist3($gid)
{
    global $surveyid, $CI;
    if (!$surveyid) {$surveyid=returnglobal('sid');}
    $groupselecter = "";
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->model('groups');
    
    //$gidquery = "SELECT gid, group_name FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='{$s_lang}' ORDER BY group_order";


    $gidresult = $CI->groups->getGroupAndID($surveyid,$s_lang) or safe_die("Plain old did not work!");      //Checked
    foreach ($gidresult->result_array() as $gv)
    {
        $groupselecter .= "<option";
        if ($gv['gid'] == $gid) {$groupselecter .= " selected='selected'"; $gvexist = 1;}
        $groupselecter .= " value='".$gv['gid']."'>".htmlspecialchars($gv['group_name'])."</option>\n";
    }
    return $groupselecter;
}

/**
 * Gives back the name of a group for a certaing group id
 *
 * @param integer $gid Group ID
 */
function getgroupname($gid)
{
    global $surveyid,$CI;
    if (!$surveyid) {$surveyid=returnglobal('sid');}
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    //$gidquery = "SELECT group_name FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='{$s_lang}' and gid=$gid";
    $CI->load->model('groups');
    
    $gidresult = $CI->groups->getGroupName($surveyid,$gid,$s_lang) or safe_die("Group name could not be fetched (getgroupname).");      //Checked
    $gv = $gidresult->row_array();
    
    $groupname = htmlspecialchars($gv['group_name']);
    
    return $groupname;
}

/**
 * put your comment there...
 *
 * @param mixed $gid
 * @param mixed $language
 */
function getgrouplistlang($gid, $language)
{
    global $surveyid, $CI, $clang;
    $CI->load->config('lsconfig');
    $CI->load->model('groups');
    $groupselecter="";
    if (!$surveyid) {$surveyid=returnglobal('sid');}
    //$gidquery = "SELECT gid, group_name FROM ".$CI->db->prefix('groups')." WHERE sid=$surveyid AND language='".$language."' ORDER BY group_order";
    $gidresult = $CI->groups->getGroupAndID($surveyid,$language) or safe_die("Couldn't get group list in common_helper.php<br />");   //Checked)
    foreach ($gidresult->result_array() as $gv)
    {
        $groupselecter .= "<option";
        if ($gv['gid'] == $gid) {$groupselecter .= " selected='selected'"; $gvexist = 1;}
        $groupselecter .= " value='".$CI->config->item('scriptname')."?sid=$surveyid&amp;gid=".$gv['gid']."'>";
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
        if (!isset($gvexist)) {$groupselecter = "<option selected='selected'>".$clang->gT("Please choose...")."</option>\n".$groupselecter;}
        else {$groupselecter .= "<option value='".$CI->config->item('scriptname')."?sid=$surveyid&amp;gid='>".$clang->gT("None")."</option>\n";}
    }
    return $groupselecter;
}

/**
function getuserlist($outputformat='fullinfoarray')
{
    global $CI;
    $CI->load->config('lsconfig');
    if ($CI->session->userdata('loginID'))
    {
        $myuid=sanitize_int($CI->session->userdata('loginID'));
    }
    $usercontrolSameGroupPolicy = $CI->config->item('usercontrolSameGroupPolicy');
    if ($CI->session->userdata('USER_RIGHT_SUPERADMIN') != 1 && isset($usercontrolSameGroupPolicy) &&
    $usercontrolSameGroupPolicy == true)
    {
        if (isset($myuid))
        {
            // List users from same group as me + all my childs
            // a subselect is used here because MSSQL does not like to group by text
            // also Postgres does like this one better
            $uquery = " SELECT * FROM ".db_table_name('users')." where uid in
                        (SELECT u.uid FROM ".db_table_name('users')." AS u,
                        ".db_table_name('user_in_groups')." AS ga ,".db_table_name('user_in_groups')." AS gb
                        WHERE u.uid=$myuid
                        OR (ga.ugid=gb.ugid AND ( (gb.uid=$myuid AND u.uid=ga.uid) OR (u.parent_id=$myuid) ) )
                        GROUP BY u.uid)";
        }
        else
        {
            return Array(); // Or die maybe
        }

    }
    else
    {
        $uquery = "SELECT * FROM ".db_table_name('users')." ORDER BY uid";
    }

    $uresult = db_execute_assoc($uquery); //Checked

    if ($uresult->RecordCount()==0)
    //user is not in a group and usercontrolSameGroupPolicy is activated - at least show his own userinfo
    {
        $uquery = "SELECT u.* FROM ".db_table_name('users')." AS u WHERE u.uid=".$myuid;
        $uresult = db_execute_assoc($uquery);//Checked
    }

    $userlist = array();
    $userlist[0] = "Reserved for logged in user";
    while ($srow = $uresult->FetchRow())
    {
        if ($outputformat != 'onlyuidarray')
        {
            if ($srow['uid'] != $_SESSION['loginID'])
            {
                $userlist[] = array("user"=>$srow['users_name'], "uid"=>$srow['uid'], "email"=>$srow['email'], "password"=>$srow['password'], "full_name"=>$srow['full_name'], "parent_id"=>$srow['parent_id'], "create_survey"=>$srow['create_survey'], "configurator"=>$srow['configurator'], "create_user"=>$srow['create_user'], "delete_user"=>$srow['delete_user'], "superadmin"=>$srow['superadmin'], "manage_template"=>$srow['manage_template'], "manage_label"=>$srow['manage_label']);           //added by Dennis modified by Moses
            }
            else
            {
                $userlist[0] = array("user"=>$srow['users_name'], "uid"=>$srow['uid'], "email"=>$srow['email'], "password"=>$srow['password'], "full_name"=>$srow['full_name'], "parent_id"=>$srow['parent_id'], "create_survey"=>$srow['create_survey'], "configurator"=>$srow['configurator'], "create_user"=>$srow['create_user'], "delete_user"=>$srow['delete_user'], "superadmin"=>$srow['superadmin'], "manage_template"=>$srow['manage_template'], "manage_label"=>$srow['manage_label']);
            }
        }
        else
        {
            if ($srow['uid'] != $_SESSION['loginID'])
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
*/

/**
 * Gets all survey infos in one big array including the language specific settings
 *
 * @param string $surveyid  The survey ID
 * @param string $languagecode The language code - if not given the base language of the particular survey is used
 * @return array Returns array with survey info or false, if survey does not exist
 */
/**function getSurveyInfo($surveyid, $languagecode='')
{
    global $dbprefix, $siteadminname, $siteadminemail, $connect, $languagechanger;
    $surveyid=sanitize_int($surveyid);
    $languagecode=sanitize_languagecode($languagecode);
    $thissurvey=false;
    // if no language code is set then get the base language one
    if (!isset($languagecode) || $languagecode=='')
    {
        $languagecode=GetBaseLanguageFromSurveyID($surveyid);;
    }
    $query="SELECT * FROM ".db_table_name('surveys').",".db_table_name('surveys_languagesettings')." WHERE sid=$surveyid and surveyls_survey_id=$surveyid and surveyls_language='$languagecode'";
    $result=db_execute_assoc($query) or safe_die ("Couldn't access survey settings<br />$query<br />".$connect->ErrorMsg());   //Checked
    while ($row=$result->FetchRow())
    {
        $thissurvey=$row;
        // now create some stupid array translations - needed for backward compatibility
        // Newly added surveysettings don't have to be added specifically - these will be available by field name automatically
        $thissurvey['name']=$thissurvey['surveyls_title'];
        $thissurvey['description']=$thissurvey['surveyls_description'];
        $thissurvey['welcome']=$thissurvey['surveyls_welcometext'];
        $thissurvey['templatedir']=$thissurvey['template'];
        $thissurvey['adminname']=$thissurvey['admin'];
        $thissurvey['tablename']=$dbprefix.'survey_'.$thissurvey['sid'];
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
        $thissurvey['passthrulabel']=isset($_SESSION['passthrulabel']) ? $_SESSION['passthrulabel'] : "";
        $thissurvey['passthruvalue']=isset($_SESSION['passthruvalue']) ? $_SESSION['passthruvalue'] : "";
    }

    //not sure this should be here... ToDo: Find a better place
    if (function_exists('makelanguagechanger')) $languagechanger = makelanguagechanger();
    return $thissurvey;
}


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


function fixsortorderAnswers($qid) //Function rewrites the sortorder for a group of answers
{
    global $CI, $surveyid;
    $qid=sanitize_int($qid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    
    $CI->load->model('answers');
    $CI->answers->updateSortOrder($qid,$baselang);
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
    global $CI;
    $gid = sanitize_int($groupid);
    $surveyid = sanitize_int($surveyid);
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    
    $CI->load->model('questions');
    $CI->questions->updateQuestionOrder($gid,$baselang);
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
    global $CI, $surveyid;
    $sid=sanitize_int($sid);
    $gid=sanitize_int($gid);
    $shiftvalue=sanitize_int($shiftvalue);

    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    
    $CI->load->model('questions');
    $CI->questions->updateQuestionOrder($gid,$baselang,$shiftvalue);
    
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
    global $CI;
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    $CI->load->model('groups');
    $CI->groups->updateGroupOrder($surveyid,$baselang);
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
    global $CI, $surveyid;
    $qid=sanitize_int($qid);
    $oldgid=sanitize_int($oldgid);
    $newgid=sanitize_int($newgid);
    $CI->load->model('conditions');
    $CI->conditions->updateCFieldName($surveyid,$qid,$oldgid,$newgid);
   
}


/**
* This function returns POST/REQUEST vars, for some vars like SID and others they are also sanitized
* CI don't support GET parameters'
* @param mixed $stringname
*/
function returnglobal($stringname)
{
    global $CI;
    $CI->load->config('lsconfig');
    $useWebserverAuth = $CI->config->item('useWebserverAuth');
    if ((isset($useWebserverAuth) && $useWebserverAuth === true) || $stringname=='sid') // don't read SID from a Cookie
    {
        if ($CI->input->get_post('stringname')) $urlParam = $CI->input->get_post('stringname');
        //if ($this->input->cookie('stringname')) $urlParam = $this->input->cookie('stringname');
    }
    elseif ($CI->input->get_post('stringname') )
    {
        $urlParam = $CI->input->get_post('stringname'); 
    }
    elseif ($CI->input->cookie('stringname'))
    {
        $urlParam = $CI->input->cookie('stringname');
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
    global $CI;
    list($fsid, $fgid, $fqid) = explode('X', $fieldcode);
    $fsid=sanitize_int($fsid);
    $fgid=sanitize_int($fgid);
    if (!$fqid) {$fqid=0;}
    $fqid=sanitize_int($fqid);
    // try a true parsing of fieldcode (can separate qid from aid)
    // but fails for type M and type P multiple choice
    // questions because the SESSION fieldcode is combined
    // and we want here to pass only the sidXgidXqid for type M and P
    $fields=arraySearchByKey($fieldcode, createFieldMap($fsid), "fieldname", 1);

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
        $condition = "WHERE qid=".$fqid." AND language='".$s_lang."'";
        $CI->load->model('questions');
        $result = $CI->questions->getSomeRecords($fieldtoselect,$condition);
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
function getextendedanswer($fieldcode, $value, $format='')
{

    global $surveyid, $CI, $clang, $action;

    // use Survey base language if s_lang isn't set in _SESSION (when browsing answers)
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    if  (!isset($action) || (isset($action) && $action!='browse') )
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
                $qidattributes = getQuestionAttributes($fields['qid']);
                $dateformatdetails = aGetDateFormatDataForQid($qidattributes, $surveyid);
                $value = DateTime::createFromFormat("Y-m-d H:i:s", $value)->format($dateformatdetails['phpdate']);
            }
            break;
            case "L":
            case "!":
            case "O":
            case "^":
            case "I":
            case "R":
                $CI->load->model('answers');
                
                //$query = "SELECT code, answer FROM ".db_table_name('answers')." WHERE qid={$fields['qid']} AND code='".$connect->escape($value)."' AND scale_id=0 AND language='".$s_lang."'";
                $result = $CI->answers->getAnswerCode($fields['qid'],$value,$s_lang) or safe_die ("Couldn't get answer type L - getextendedanswer() in common_helper.php<br />$query<br />"); //Checked
                
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
                $condition = "WHERE qid={$fields['qid']} AND code='".$CI->db->escape($value)."' AND language='".$s_lang."'";
                $CI->load->model('answers');
                
                $result = $CI->answers->getSomeRecords($fieldtoselect,$condition) or safe_die ("Couldn't get answer type F/H - getextendedanswer() in common_helper.php");   //Checked
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
    global $CI;
    $CI->load->config('lsconfig');
    $usertemplaterootdir = $CI->config->item('usertemplaterootdir');
    $standardtemplaterootdir = $CI->config->item('standardtemplaterootdir');
    $defaulttemplate = $CI->config->item('defaulttemplate');
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

    global $CI, $globalfieldmap, $clang, $aDuplicateQIDs;
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


    //Check for any additional fields for this survey and create necessary fields (token and datestamp and ipaddr)
    //$pquery = "SELECT anonymized, datestamp, ipaddr, refurl FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
    $CI->load->model('surveys');
    $fieldtoselect = array('anonymized', 'datestamp', 'ipaddr', 'refurl');
    $conditiontoselect = "WHERE sid=$surveyid";
    $presult=$CI->surveys->getSomeRecords($fieldtoselect,$conditiontoselect); //Checked)
    foreach ($presult->result_array() as $prow)
    {
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
    }

    //Get list of questions
    if (is_null($sQuestionLanguage))
    {
    $s_lang = GetBaseLanguageFromSurveyID($surveyid);
    }
    else
    {
        $s_lang = $sQuestionLanguage;
    }
    $qtypes=getqtypelist('','array');
    /**
    $aquery = "SELECT *, "
        ." (SELECT count(1) FROM ".db_table_name('conditions')." c\n"
        ." WHERE questions.qid = c.qid) AS hasconditions,\n"
        ." (SELECT count(1) FROM ".db_table_name('conditions')." c\n"
        ." WHERE questions.qid = c.cqid) AS usedinconditions\n"
        ." FROM ".db_table_name('questions')." as questions, ".db_table_name('groups')." as groups"
        ." WHERE questions.gid=groups.gid AND "
        ." questions.sid=$surveyid AND "
        ." questions.language='{$s_lang}' AND "
        ." questions.parent_qid=0 AND "
        ." groups.language='{$s_lang}' ";
    if ($questionid!==false)
    {
        $aquery.=" and questions.qid={$questionid} ";
    }
    $aquery.=" ORDER BY group_order, question_order"; */
    $CI->load->model('conditions');
    $CI->load->model('defaultvalues');
    $aresult = $CI->conditions->getConditions($surveyid,$questionid,$s_lang) or safe_die ("Couldn't get list of questions in createFieldMap function.<br />$query<br />"); //Checked

    foreach ($aresult->result_array() as $arow) //With each question, create the appropriate field(s))
    {

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
                $fieldtoselect = array('defaultvalue');
                
                if ($arow['same_default'])
                {
                    $conditiontoselect = "WHERE qid={$arow['qid']} AND scale_id=0 AND language='".GetBaseLanguageFromSurveyID($surveyid)."'";
                    $data = $CI->defaultvalues->getSomeRecords($fieldtoselect,$conditiontoselect);
                    $data  = $data->row_array();
                    $fieldmap[$fieldname]['defaultvalue']=$data['defaultvalue'];//$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE qid={$arow['qid']} AND scale_id=0 AND language='".GetBaseLanguageFromSurveyID($surveyid)."'");
                }
                else
                {
                    $conditiontoselect = "WHERE qid={$arow['qid']} AND scale_id=0 AND language='{$clang->langcode}'";
                    $data = $CI->defaultvalues->getSomeRecords($fieldtoselect,$conditiontoselect);
                    $data  = $data->row_array();
                    $fieldmap[$fieldname]['defaultvalue']=$data['defaultvalue'];//$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE qid={$arow['qid']} AND scale_id=0 AND language='{$clang->langcode}'");
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
                            if ($arow['same_default'])
                            {
                                $conditiontoselect = "WHERE qid={$arow['qid']} AND scale_id=0 AND language='".GetBaseLanguageFromSurveyID($surveyid)."'";
                                $data = $CI->defaultvalues->getSomeRecords($fieldtoselect,$conditiontoselect);
                                $data  = $data->row_array();
                                $fieldmap[$fieldname]['defaultvalue']=$data['defaultvalue'];//$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE qid={$arow['qid']} AND scale_id=0 AND language='".GetBaseLanguageFromSurveyID($surveyid)."'");
                            }
                            else
                            {
                                $conditiontoselect = "WHERE qid={$arow['qid']} AND scale_id=0 AND language='{$clang->langcode}'";
                                $data = $CI->defaultvalues->getSomeRecords($fieldtoselect,$conditiontoselect);
                                $data  = $data->row_array();
                                $fieldmap[$fieldname]['defaultvalue']=$data['defaultvalue'];//$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE qid={$arow['qid']} AND scale_id=0 AND language='{$clang->langcode}'");
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
                    }
                }
            }

        elseif ($arow['type'] == "R")
        {
            //MULTI ENTRY
            $CI->load->model('answers');
            $data = $CI->answers->getCountOfCode($arow['qid'],$s_lang);
            $data = $data->row_array();
            $slots=$data['codecount'];//$connect->GetOne("select count(code) from ".db_table_name('answers')." where qid={$arow['qid']} and language='{$s_lang}'");
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
                }
            }
        }
        elseif ($arow['type'] == "|")
        {
            //$abquery = "SELECT value FROM ".db_table_name('question_attributes');
            $fieldtoselect = array('value');
            $conditiontoselect = " WHERE attribute='max_num_of_files' AND qid=".$arow['qid'];
            $CI->load->model('question_attributes');
            $abresult = $CI->question_attributes->getSomeRecords($fieldtoselect,$conditiontoselect) or safe_die ("Couldn't get maximum)
                number of files that can be uploaded <br />");
            $abrow = $abresult->row_array();

            for ($i = 1; $i <= $abrow['value']; $i++)
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
                    $fieldmap[$fieldname]['max_files']=$abrow['value'];
                    $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                    $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions']=$conditions;
                    $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
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
                    //$fieldmap[$fieldname]['subquestion']=$clang->gT("Comment");
                    $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                    $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions']=$conditions;
                    $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                }
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
                $fieldmap[$fieldname]=array("fieldname"=>$fieldname, 'type'=>$arow['type'], 'sid'=>$surveyid, "gid"=>$arow['gid'], "qid"=>$arow['qid'], "aid"=>$abrow['title']);
                if ($style == "full")
                {
                    $fieldmap[$fieldname]['title']=$arow['title'];
                    $fieldmap[$fieldname]['question']=$arow['question'];
                    $fieldmap[$fieldname]['subquestion']=$abrow['question'];
                    $fieldmap[$fieldname]['group_name']=$arow['group_name'];
                    $fieldmap[$fieldname]['mandatory']=$arow['mandatory'];
                    $fieldmap[$fieldname]['hasconditions']=$conditions;
                    $fieldmap[$fieldname]['usedinconditions']=$usedinconditions;
                    if ($arow['same_default'])
                    {
                        $conditiontoselect = "WHERE sqid={$abrow['qid']} AND qid={$arow['qid']} AND scale_id=0 AND language='".GetBaseLanguageFromSurveyID($surveyid)."'";
                        $data = $CI->defaultvalues->getSomeRecords($fieldtoselect,$conditiontoselect);
                        $data  = $data->row_array();
                        $fieldmap[$fieldname]['defaultvalue']=$data['defaultvalue'];
                        //$fieldmap[$fieldname]['defaultvalue']=$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE sqid={$abrow['qid']} and qid={$arow['qid']} AND scale_id=0 AND language='".GetBaseLanguageFromSurveyID($surveyid)."'");
                    }
                    else
                    {
                        $conditiontoselect = "WHERE sqid={$abrow['qid']} AND qid={$arow['qid']} AND scale_id=0 AND language='{$clang->langcode}'";
                        $data = $CI->defaultvalues->getSomeRecords($fieldtoselect,$conditiontoselect);
                        $data  = $data->row_array();
                        $fieldmap[$fieldname]['defaultvalue']=$data['defaultvalue'];
                        
                        //$fieldmap[$fieldname]['defaultvalue']=$connect->GetOne("SELECT defaultvalue FROM ".db_table_name('defaultvalues')." WHERE sqid={$abrow['qid']} and qid={$arow['qid']} AND scale_id=0 AND language='{$clang->langcode}'");
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
                    }
                }
            }
        }
    }
    if (isset($fieldmap)) {
        $globalfieldmap[$surveyid][$style][$clang->langcode] = $fieldmap;
        return $fieldmap;
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

    global $globalfieldmap, $clang, $aDuplicateQIDs;
    static $timingsFieldMap;

    $surveyid=sanitize_int($surveyid);
    //checks to see if fieldmap has already been built for this page.
    if (isset($timingsFieldMap[$surveyid][$style][$clang->langcode]) && $force_refresh==false) {
        return $timingsFieldMap[$surveyid][$style][$clang->langcode];
    }

    //do something
    $fields = createFieldMap($surveyid, $style, $force_refresh, $questionid, $sQuestionLanguage);
    $fieldmap['interviewTime']=array('fieldname'=>'interviewTime','type'=>'interview_time','sid'=>$surveyid, 'gid'=>'', 'qid'=>'', 'aid'=>'', 'question'=>$clang->gT('Total time'), 'title'=>'interviewTime');
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
 * This function replaces keywords in a text and is mainly intended for templates
 * If you use this functions put your replacement strings into the $replacements variable
 * instead of using global variables
 *
 * @param mixed $line Text to search in
 * @param mixed $replacements Array of replacements:  Array( <stringtosearch>=><stringtoreplacewith>
 * @return string  Text with replaced strings
 */
/**function templatereplace($line, $replacements=array())
{
    global $surveylist, $sitename, $clienttoken, $rooturl;
    global $thissurvey, $imageurl, $defaulttemplate;
    global $percentcomplete, $move;
    global $groupname, $groupdescription;
    global $question;
    global $showXquestions, $showgroupinfo, $showqnumcode;
    global $questioncode, $answer, $navigator;
    global $help, $totalquestions, $surveyformat;
    global $completed, $register_errormsg;
    global $notanswered, $privacy, $surveyid;
    global $publicurl, $templatedir, $token;
    global $assessments, $s_lang;
    global $errormsg, $clang;
    global $saved_id, $usertemplaterootdir;
    global $totalBoilerplatequestions, $relativeurl;
    global $languagechanger;
    global $printoutput, $captchapath, $loadname;

	if($question['sgq']) $questiondetails=getsidgidqidaidtype($question['sgq']); //Gets an array containing SID, GID, QID, AID and Question Type
	
	// lets sanitize the survey template
    if(isset($thissurvey['templatedir']))
    {
        $templatename=$thissurvey['templatedir'];
    }
    else
    {
        $templatename=$defaulttemplate;
    }
    $templatename=validate_templatedir($templatename);

    // create absolute template URL and template dir vars
    $templateurl=sGetTemplateURL($templatename).'/';
    $templatedir=sgetTemplatePath($templatename);

    if (stripos ($line,"</head>"))
    {
        $line=str_ireplace("</head>",
            "<script type=\"text/javascript\" src=\"$rooturl/scripts/survey_runtime.js\"></script>\n"
        .use_firebug()
        ."\t</head>", $line);
    }
    // Get some vars : move elsewhere ?
    // surveyformat
    if (isset($thissurvey['format']))
    {
        $surveyformat = str_replace(array("A","S","G"),array("allinone","questionbyquestion","groupbygroup"),$thissurvey['format']);
    }
    else
    {
        $surveyformat = "";
    }
    // real survey contact
    if (isset($surveylist['contact']))
    {
        $surveycontact = $surveylist['contact'];
    }
    elseif (isset($thissurvey['admin']) && $thissurvey['admin']!="")
    {
        $surveycontact=sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['admin'],$thissurvey['adminemail']);
    }
    else
    {
        $surveycontact="";
    }
    
    // If there are non-bracketed replacements to be made do so above this line.
    // Only continue in this routine if there are bracketed items to replace {}
    if (strpos($line, "{") === false) {
        return $line;
    }

    foreach ($replacements as $replacementkey=>$replacementvalue)
    {
        if (strpos($line, '{'.$replacementkey.'}') !== false) $line=str_replace('{'.$replacementkey.'}', $replacementvalue, $line);
    }

    if (strpos($line, "{SURVEYLISTHEADING}") !== false) $line=str_replace("{SURVEYLISTHEADING}", $surveylist['listheading'], $line);
    if (strpos($line, "{SURVEYLIST}") !== false) $line=str_replace("{SURVEYLIST}", $surveylist['list'], $line);
    if (strpos($line, "{NOSURVEYID}") !== false) $line=str_replace("{NOSURVEYID}", $surveylist['nosid'], $line);
    if (strpos($line, "{SURVEYCONTACT}") !== false) $line=str_replace("{SURVEYCONTACT}", $surveylist['contact'], $line);

    if (strpos($line, "{SITENAME}") !== false) $line=str_replace("{SITENAME}", $sitename, $line);

    if (strpos($line, "{SURVEYLIST}") !== false) $line=str_replace("{SURVEYLIST}", $surveylist, $line);
    if (strpos($line, "{CHECKJAVASCRIPT}") !== false) $line=str_replace("{CHECKJAVASCRIPT}", "<noscript><span class='warningjs'>".$clang->gT("Caution: JavaScript execution is disabled in your browser. You may not be able to answer all questions in this survey. Please, verify your browser parameters.")."</span></noscript>", $line);

    if (strpos($line, "{SURVEYLANGAGE}") !== false) $line=str_replace("{SURVEYLANGAGE}", $clang->langcode, $line);
    if (strpos($line, "{SURVEYCONTACT}") !== false) $line=str_replace("{SURVEYCONTACT}", $surveycontact, $line);
    if (strpos($line, "{SURVEYNAME}") !== false) $line=str_replace("{SURVEYNAME}", $thissurvey['name'], $line);
    if (strpos($line, "{SURVEYDESCRIPTION}") !== false) $line=str_replace("{SURVEYDESCRIPTION}", $thissurvey['description'], $line);
    if (strpos($line, "{SURVEYFORMAT}") !== false) $line=str_replace("{SURVEYFORMAT}", $surveyformat, $line);
        if (strpos($line, "{WELCOME}") !== false) $line=str_replace("{WELCOME}", $thissurvey['welcome'], $line);
    if (strpos($line, "{LANGUAGECHANGER}") !== false) $line=str_replace("{LANGUAGECHANGER}", $languagechanger, $line);
    if (strpos($line, "{PERCENTCOMPLETE}") !== false) $line=str_replace("{PERCENTCOMPLETE}", $percentcomplete, $line);

    if(
        $showgroupinfo == 'both' ||
	    $showgroupinfo == 'name' ||
	    ($showgroupinfo == 'choose' && !isset($thissurvey['showgroupinfo'])) ||
	    ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'B') ||
	    ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'N')
    )
    {
        if (strpos($line, "{GROUPNAME}") !== false) $line=str_replace("{GROUPNAME}", $groupname, $line);
    }
    else
    {
        if (strpos($line, "{GROUPNAME}") !== false) $line=str_replace("{GROUPNAME}", '' , $line);
    };
    if(
        $showgroupinfo == 'both' ||
	    $showgroupinfo == 'description' ||
	    ($showgroupinfo == 'choose' && !isset($thissurvey['showgroupinfo'])) ||
	    ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'B') ||
	    ($showgroupinfo == 'choose' && $thissurvey['showgroupinfo'] == 'D')
    )
    {
        if (strpos($line, "{GROUPDESCRIPTION}") !== false) $line=str_replace("{GROUPDESCRIPTION}", $groupdescription, $line);
    }
    else
    {
        if (strpos($line, "{GROUPDESCRIPTION}") !== false) $line=str_replace("{GROUPDESCRIPTION}", '' , $line);
    };

    if (is_array($question))
    {
        if (strpos($line, "{QUESTION}") !== false)
        {
            $line=str_replace("{QUESTION}", $question['all'], $line);
        }
        else
        {
            if (strpos($line, "{QUESTION_TEXT}") !== false) $line=str_replace("{QUESTION_TEXT}", $question['text'], $line);
            if (strpos($line, "{QUESTION_HELP}") !== false) $line=str_replace("{QUESTION_HELP}", $question['help'], $line);
            if (strpos($line, "{QUESTION_MANDATORY}") !== false) $line=str_replace("{QUESTION_MANDATORY}", $question['mandatory'], $line);
            if (strpos($line, "{QUESTION_MAN_MESSAGE}") !== false) $line=str_replace("{QUESTION_MAN_MESSAGE}", $question['man_message'], $line);
            if (strpos($line, "{QUESTION_VALID_MESSAGE}") !== false) $line=str_replace("{QUESTION_VALID_MESSAGE}", $question['valid_message'], $line);
            if (strpos($line, "{QUESTION_FILE_VALID_MESSAGE}") !== false) $line=str_replace("{QUESTION_FILE_VALID_MESSAGE}", $question['file_valid_message'], $line);
        }
    }
    else
    {
        if (strpos($line, "{QUESTION}") !== false) $line=str_replace("{QUESTION}", $question, $line);
    };
    if (strpos($line, '{QUESTION_ESSENTIALS}') !== false) $line=str_replace('{QUESTION_ESSENTIALS}', $question['essentials'], $line);
    if (strpos($line, '{QUESTION_CLASS}') !== false) $line=str_replace('{QUESTION_CLASS}', $question['class'], $line);
    if (strpos($line, '{QUESTION_MAN_CLASS}') !== false) $line=str_replace('{QUESTION_MAN_CLASS}', $question['man_class'], $line);
    if (strpos($line, "{QUESTION_INPUT_ERROR_CLASS}") !== false) $line=str_replace("{QUESTION_INPUT_ERROR_CLASS}", $question['input_error_class'], $line);

    if(
        $showqnumcode == 'both' ||
	    $showqnumcode == 'number' ||
	    ($showqnumcode == 'choose' && !isset($thissurvey['showqnumcode'])) ||
	    ($showqnumcode == 'choose' && $thissurvey['showqnumcode'] == 'B') ||
	    ($showqnumcode == 'choose' && $thissurvey['showqnumcode'] == 'N')
    )
    {
        if (strpos($line, "{QUESTION_NUMBER}") !== false) $line=str_replace("{QUESTION_NUMBER}", $question['number'], $line);
    }
    else
    {
        if (strpos($line, "{QUESTION_NUMBER}") !== false) $line=str_replace("{QUESTION_NUMBER}", '' , $line);
    };
    if(
        $showqnumcode == 'both' ||
	    $showqnumcode == 'code' ||
	    ($showqnumcode == 'choose' && !isset($thissurvey['showqnumcode'])) ||
	    ($showqnumcode == 'choose' && $thissurvey['showqnumcode'] == 'B') ||
	    ($showqnumcode == 'choose' && $thissurvey['showqnumcode'] == 'C')
    )
    {
    if (strpos($line, "{QUESTION_CODE}") !== false) $line=str_replace("{QUESTION_CODE}", $question['code'], $line);
    }
    else
    {
        if (strpos($line, "{QUESTION_CODE}") !== false) $line=preg_replace("/{QUESTION_CODE}:?/", '', $line);

    };

    if (strpos($line, "{ANSWER}") !== false) $line=str_replace("{ANSWER}", $answer, $line);
    $totalquestionsAsked = $totalquestions - $totalBoilerplatequestions;
    if(
      $showXquestions == 'show' ||
      ($showXquestions == 'choose' && !isset($thissurvey['showXquestions'])) ||
      ($showXquestions == 'choose' && $thissurvey['showXquestions'] == 'Y')
    )
    {
        if ($totalquestionsAsked < 1)
        {
            if (strpos($line, "{THEREAREXQUESTIONS}") !== false) $line=str_replace("{THEREAREXQUESTIONS}", $clang->gT("There are no questions in this survey"), $line); //Singular
        }
        elseif ($totalquestionsAsked == 1)
        {
            if (strpos($line, "{THEREAREXQUESTIONS}") !== false) $line=str_replace("{THEREAREXQUESTIONS}", $clang->gT("There is 1 question in this survey"), $line); //Singular
        }
        else
        {
             if (strpos($line, "{THEREAREXQUESTIONS}") !== false) $line=str_replace("{THEREAREXQUESTIONS}", $clang->gT("There are {NUMBEROFQUESTIONS} questions in this survey."), $line); //Note this line MUST be before {NUMBEROFQUESTIONS}
	};
    }
    else
    {
    	if (strpos($line, '{THEREAREXQUESTIONS}') !== false) $line=str_replace('{THEREAREXQUESTIONS}' , '' , $line);
    };
    if (strpos($line, "{NUMBEROFQUESTIONS}") !== false) $line=str_replace("{NUMBEROFQUESTIONS}", $totalquestionsAsked, $line);

    if (strpos($line, "{TOKEN}") !== false) {
        if (isset($token)) {
            $line=str_replace("{TOKEN}", $token, $line);
        }
        elseif (isset($clienttoken)) {
            $line=str_replace("{TOKEN}", htmlentities($clienttoken,ENT_QUOTES,'UTF-8'), $line);
        }
        else {
            $line=str_replace("{TOKEN}",'', $line);
        }
    }

    if (strpos($line, "{SID}") !== false) $line=str_replace("{SID}", $surveyid, $line);

    if (strpos($line, "{EXPIRY}") !== false)
    {
       	$dateformatdetails=getDateFormatData($thissurvey['surveyls_dateformat']);
    	$datetimeobj = new Date_Time_Converter($thissurvey['expiry'] , "Y-m-d");
    	$dateoutput=$datetimeobj->convert($dateformatdetails['phpdate']);
       	$line=str_replace("{EXPIRY}", $dateoutput, $line);
    }
    if (strpos($line, "{NAVIGATOR}") !== false) $line=str_replace("{NAVIGATOR}", $navigator, $line);
    if (strpos($line, "{SUBMITBUTTON}") !== false) {
        $submitbutton="<input class='submit' type='submit' value=' ".$clang->gT("Submit")." ' name='move2' onclick=\"javascript:document.limesurvey.move.value = 'movesubmit';\" />";
        $line=str_replace("{SUBMITBUTTON}", $submitbutton, $line);
    }
    if (strpos($line, "{COMPLETED}") !== false) $line=str_replace("{COMPLETED}", $completed, $line);
    if (strpos($line, "{URL}") !== false) {
        if ($thissurvey['surveyls_url']!=""){
            if (trim($thissurvey['surveyls_urldescription'])!=''){
                $linkreplace="<a target='_top' href='{$thissurvey['surveyls_url']}'>{$thissurvey['surveyls_urldescription']}</a>";
            }
            else {
                $linkreplace="<a target='_top' href='{$thissurvey['surveyls_url']}'>{$thissurvey['surveyls_url']}</a>";
            }
        }
        else $linkreplace='';
        $line=str_replace("{URL}", $linkreplace, $line);
        $line=str_replace("{SAVEDID}",$saved_id, $line);     // to activate the SAVEDID in the END URL
        if (isset($clienttoken)) {$token=$clienttoken;} else {$token='';}
        $line=str_replace("{TOKEN}",urlencode($token), $line);          // to activate the TOKEN in the END URL
        $line=str_replace("{SID}", $surveyid, $line);       // to activate the SID in the RND URL
    }
    if (strpos($line, "{PRIVACY}") !== false)
    {
        $line=str_replace("{PRIVACY}", $privacy, $line);
    }
    if (strpos($line, "{PRIVACYMESSAGE}") !== false)
    {
        $line=str_replace("{PRIVACYMESSAGE}", "<span style='font-weight:bold; font-style: italic;'>".$clang->gT("A Note On Privacy")."</span><br />".$clang->gT("This survey is anonymous.")."<br />".$clang->gT("The record kept of your survey responses does not contain any identifying information about you unless a specific question in the survey has asked for this. If you have responded to a survey that used an identifying token to allow you to access the survey, you can rest assured that the identifying token is not kept with your responses. It is managed in a separate database, and will only be updated to indicate that you have (or haven't) completed this survey. There is no way of matching identification tokens with survey responses in this survey."), $line);
    }
    if (strpos($line, "{CLEARALL}") !== false)  {

        $clearall = "<button class='nav-button ui-corner-all'  type='button' name='clearallbtn'  class='clearall' "
        ."onclick=\"if (confirm('".$clang->gT("Are you sure you want to clear all your responses?",'js')."')) {window.open('{$publicurl}/index.php?sid=$surveyid&amp;move=clearall&amp;lang=".$_SESSION['s_lang'];
        if (returnglobal('token'))
        {
            $clearall .= "&amp;token=".urlencode(trim(sanitize_xss_string(strip_tags(returnglobal('token')))));
        }
        $clearall .= "', '_self')}\" >".$clang->gT("Exit and Clear Survey")."</button>";

        $line=str_replace("{CLEARALL}", $clearall, $line);

    }
    // --> START NEW FEATURE - SAVE
    if (strpos($line, "{DATESTAMP}") !== false) {
        if (isset($_SESSION['datestamp'])) {
            $line=str_replace("{DATESTAMP}", $_SESSION['datestamp'], $line);
        }
        else {
            $line=str_replace("{DATESTAMP}", "-", $line);
        }
    }
    // <-- END NEW FEATURE - SAVE
    if (strpos($line, "{SAVE}") !== false)  {
        //Set up save/load feature
        if ($thissurvey['allowsave'] == "Y")
        {
            // Find out if the user has any saved data

            if ($thissurvey['format']=='A')
            {
                if($thissurvey['tokenanswerspersistence'] != 'Y')
                {
                    $saveall = "\t\t\t<button class='nav-button ui-corner-all' type='submit' name='loadall'  class='saveall' ". (($thissurvey['active'] != "Y")? "disabled='disabled'":"") .">".$clang->gT("Load Unfinished Survey")."</button>"
                    ."\n\t\t\t<button class='nav-button ui-corner-all' name='saveallbtn' class='saveall' onclick=\"javascript:document.limesurvey.move.value = this.value;addHiddenField(document.getElementById('limesurvey'),'saveall',this.value);document.getElementById('limesurvey').submit();\" ". (($thissurvey['active'] != "Y")? "disabled='disabled'":"") .">".$clang->gT("Resume Later")."</button>";  // Show Save So Far button
                }
                else
                {
                    $saveall= "\t\t\t<button class='nav-button ui-corner-all' name='saveallbtn'  class='saveall' onclick=\"javascript:document.limesurvey.move.value = this.value;addHiddenField(document.getElementById('limesurvey'),'saveall',this.value);document.getElementById('limesurvey').submit();\" ". (($thissurvey['active'] != "Y")? "disabled='disabled'":"") .">".$clang->gT("Resume Later")."</button>";  // Show Save So Far button
        	};
            }
            elseif (!isset($_SESSION['step']) || !$_SESSION['step'])  //First page, show LOAD
            {
                if($thissurvey['tokenanswerspersistence'] != 'Y')
                {
                    $saveall = "\t\t\t<button class='nav-button ui-corner-all' type='submit' name='loadall'  class='saveall' ". (($thissurvey['active'] != "Y")? "disabled='disabled'":"") .">".$clang->gT("Load Unfinished Survey")."</button>";
                }
		else
		{
                    $saveall = '';
		};
            }
            elseif (isset($_SESSION['scid']) && (isset($move) && $move == "movelast"))  //Already saved and on Submit Page, dont show Save So Far button
            {
                $saveall='';
            }
            else
            {
                $saveall= "<button class='nav-button ui-corner-all' type='button' name='saveallbtn' class='saveall' onclick=\"javascript:document.limesurvey.move.value = this.value;addHiddenField(document.getElementById('limesurvey'),'saveall',this.value);document.getElementById('limesurvey').submit();\" ". (($thissurvey['active'] != "Y")? "disabled='disabled'":"") .">".$clang->gT("Resume Later")."</button>";  // Show Save So Far button
            }
        }
        else
        {
            $saveall="";
        }
        $line=str_replace("{SAVE}", $saveall, $line);

    }
    if (strpos($line, "{TEMPLATEURL}") !== false) {
        $line=str_replace("{TEMPLATEURL}", $templateurl, $line);
    }

    if (strpos($line, "{TEMPLATECSS}") !== false) {
        $templatecss="<link rel='stylesheet' type='text/css' href='{$templateurl}template.css' />\n";
        if (getLanguageRTL($clang->langcode))
        {
            $templatecss.="<link rel='stylesheet' type='text/css' href='{$templateurl}template-rtl.css' />\n";
        }
        $line=str_replace("{TEMPLATECSS}", $templatecss, $line);
    }

    if (FlattenText($help,true)!='') {
        if (strpos($line, "{QUESTIONHELP}") !== false)
        {
            If (!isset($helpicon))
            {
                if (file_exists($templatedir.'/help.gif'))
                {

                    $helpicon=$templateurl.'help.gif';
                }
                elseif (file_exists($templatedir.'/help.png'))
                {

                    $helpicon=$templateurl.'help.png';
                }
                else
                {
                    $helpicon=$imageurl."/help.gif";
                }
            }
            $line=str_replace("{QUESTIONHELP}", "<img src='$helpicon' alt='Help' align='left' />".$help, $line);

        }
        if (strpos($line, "{QUESTIONHELPPLAINTEXT}") !== false) $line=str_replace("{QUESTIONHELPPLAINTEXT}", strip_tags(addslashes($help)), $line);
    }
    else
    {
        if (strpos($line, "{QUESTIONHELP}") !== false) $line=str_replace("{QUESTIONHELP}", $help, $line);
        if (strpos($line, "{QUESTIONHELPPLAINTEXT}") !== false) $line=str_replace("{QUESTIONHELPPLAINTEXT}", strip_tags(addslashes($help)), $line);
    }

    $line=insertansReplace($line);

	if (strpos($line, "{SID}") !== false) $line=str_replace("{SID}", $questiondetails['sid'], $line);
	if (strpos($line, "{GID}") !== false) $line=str_replace("{GID}", $questiondetails['gid'], $line);
	if (strpos($line, "{QID}") !== false) $line=str_replace("{QID}", $questiondetails['qid'], $line);
	if (strpos($line, "{AID}") !== false) $line=str_replace("{AID}", $questiondetails['aid'], $line);
    if (strpos($line, "{SGQ}") !== false) $line=str_replace("{SGQ}", $question['sgq'], $line);

    if (strpos($line, "{SUBMITCOMPLETE}") !== false) $line=str_replace("{SUBMITCOMPLETE}", "<strong>".$clang->gT("Thank you!")."<br /><br />".$clang->gT("You have completed answering the questions in this survey.")."</strong><br /><br />".$clang->gT("Click on 'Submit' now to complete the process and save your answers."), $line);
    if (strpos($line, "{SUBMITREVIEW}") !== false) {
        if (isset($thissurvey['allowprev']) && $thissurvey['allowprev'] == "N") {
            $strreview = "";
        }
        else {
            $strreview=$clang->gT("If you want to check any of the answers you have made, and/or change them, you can do that now by clicking on the [<< prev] button and browsing through your responses.");
        }
        $line=str_replace("{SUBMITREVIEW}", $strreview, $line);
    }

    $line=tokenReplace($line);

    if (strpos($line, "{ANSWERSCLEARED}") !== false) $line=str_replace("{ANSWERSCLEARED}", $clang->gT("Answers Cleared"), $line);
    if (strpos($line, "{RESTART}") !== false)
    {
        if ($thissurvey['active'] == "N")
        {
            $replacetext= "<a href='{$publicurl}/index.php?sid=$surveyid&amp;newtest=Y";
            if (isset($s_lang) && $s_lang!='') $replacetext.="&amp;lang=".$s_lang;
            $replacetext.="'>".$clang->gT("Restart this Survey")."</a>";
            $line=str_replace("{RESTART}", $replacetext, $line);
        } else {
            $restart_extra = "";
            $restart_token = returnglobal('token');
            if (!empty($restart_token)) $restart_extra .= "&amp;token=".urlencode($restart_token);
            else $restart_extra = "&amp;newtest=Y";
            if (!empty($_GET['lang'])) $restart_extra .= "&amp;lang=".returnglobal('lang');
            $line=str_replace("{RESTART}",  "<a href='{$publicurl}/index.php?sid=$surveyid".$restart_extra."'>".$clang->gT("Restart this Survey")."</a>", $line);
        }
    }
    if (strpos($line, "{CLOSEWINDOW}") !== false) $line=str_replace("{CLOSEWINDOW}", "<a href='javascript:%20self.close()'>".$clang->gT("Close this window")."</a>", $line);
    if (strpos($line, "{SAVEERROR}") !== false) $line=str_replace("{SAVEERROR}", $errormsg, $line);
    if (strpos($line, "{SAVEHEADING}") !== false) $line=str_replace("{SAVEHEADING}", $clang->gT("Save Your Unfinished Survey"), $line);
    if (strpos($line, "{SAVEMESSAGE}") !== false) $line=str_replace("{SAVEMESSAGE}", $clang->gT("Enter a name and password for this survey and click save below.")."<br />\n".$clang->gT("Your survey will be saved using that name and password, and can be completed later by logging in with the same name and password.")."<br /><br />\n".$clang->gT("If you give an email address, an email containing the details will be sent to you.")."<br /><br />\n".$clang->gT("After having clicked the save button you can either close this browser window or continue filling out the survey."), $line);
    if (strpos($line, "{SAVEALERT}") !== false) 
    {
        if (isset($thissurvey['anonymized']) && $thissurvey['anonymized'] =='Y')
        {
            $savealert=$clang->gT("To remain anonymous please use a pseudonym as your username, also an email address is not required.");
        }
        else
        {
            $savealert="";
        }
        $line=str_replace("{SAVEALERT}", $savealert, $line);
    }
    
    if (strpos($line, "{RETURNTOSURVEY}") !== false)
    {
        $savereturn = "<a href='$relativeurl/index.php?sid=$surveyid";
        if (returnglobal('token'))
        {
            $savereturn.= "&amp;token=".urlencode(trim(sanitize_xss_string(strip_tags(returnglobal('token')))));
        }
        $savereturn .= "'>".$clang->gT("Return To Survey")."</a>";
        $line=str_replace("{RETURNTOSURVEY}", $savereturn, $line);
    }
    if (strpos($line, "{SAVEFORM}") !== false) {
        //SAVE SURVEY DETAILS
        $saveform = "<table><tr><td align='right'>".$clang->gT("Name").":</td><td><input type='text' name='savename' value='";
        if (isset($_POST['savename'])) {$saveform .= html_escape(auto_unescape($_POST['savename']));}
        $saveform .= "' /></td></tr>\n"
        . "<tr><td align='right'>".$clang->gT("Password").":</td><td><input type='password' name='savepass' value='";
        if (isset($_POST['savepass'])) {$saveform .= html_escape(auto_unescape($_POST['savepass']));}
        $saveform .= "' /></td></tr>\n"
        . "<tr><td align='right'>".$clang->gT("Repeat Password").":</td><td><input type='password' name='savepass2' value='";
        if (isset($_POST['savepass2'])) {$saveform .= html_escape(auto_unescape($_POST['savepass2']));}
        $saveform .= "' /></td></tr>\n"
        . "<tr><td align='right'>".$clang->gT("Your Email").":</td><td><input type='text' name='saveemail' value='";
        if (isset($_POST['saveemail'])) {$saveform .= html_escape(auto_unescape($_POST['saveemail']));}
        $saveform .= "' /></td></tr>\n";
        if (function_exists("ImageCreate") && captcha_enabled('saveandloadscreen',$thissurvey['usecaptcha']))
        {
            $saveform .="<tr><td align='right'>".$clang->gT("Security Question").":</td><td><table><tr><td valign='middle'><img src='{$captchapath}verification.php?sid=$surveyid' alt='' /></td><td valign='middle' style='text-align:left'><input type='text' size='5' maxlength='3' name='loadsecurity' value='' /></td></tr></table></td></tr>\n";
        }
        $saveform .= "<tr><td align='right'></td><td></td></tr>\n"
        . "<tr><td></td><td><button class='nav-button ui-corner-all'  type='submit'  id='savebutton' name='savesubmit'>".$clang->gT("Save Now")."</button></td></tr>\n"
        . "</table>";
        $line=str_replace("{SAVEFORM}", $saveform, $line);
    }
    if (strpos($line, "{LOADERROR}") !== false) $line=str_replace("{LOADERROR}", $errormsg, $line);
    if (strpos($line, "{LOADHEADING}") !== false) $line=str_replace("{LOADHEADING}", $clang->gT("Load A Previously Saved Survey"), $line);
    if (strpos($line, "{LOADMESSAGE}") !== false) $line=str_replace("{LOADMESSAGE}", $clang->gT("You can load a survey that you have previously saved from this screen.")."<br />".$clang->gT("Type in the 'name' you used to save the survey, and the password.")."<br />", $line);
    if (strpos($line, "{LOADFORM}") !== false) {
        //LOAD SURVEY DETAILS
        $loadform = "<table><tr><td align='right'>".$clang->gT("Saved name").":</td><td><input type='text' name='loadname' value='";
        if ($loadname) {$loadform .= html_escape(auto_unescape($loadname));}
        $loadform .= "' /></td></tr>\n"
        . "<tr><td align='right'>".$clang->gT("Password").":</td><td><input type='password' name='loadpass' value='";
        if (isset($loadpass)) { $loadform .= html_escape(auto_unescape($loadpass)); }
        $loadform .= "' /></td></tr>\n";
        if (function_exists("ImageCreate") && captcha_enabled('saveandloadscreen',$thissurvey['usecaptcha']))
        {
            $loadform .="<tr><td align='right'>".$clang->gT("Security Question").":</td><td><table><tr><td valign='middle'><img src='{$captchapath}verification.php?sid=$surveyid' alt='' /></td><td valign='middle'><input type='text' size='5' maxlength='3' name='loadsecurity' value='' alt=''/></td></tr></table></td></tr>\n";
        }


        $loadform .="<tr><td align='right'></td><td></td></tr>\n"
        . "<tr><td></td><td><button class='nav-button ui-corner-all'  type='submit' id='loadbutton'>".$clang->gT("Load Now")."</button></td></tr></table>\n";
        $line=str_replace("{LOADFORM}", $loadform, $line);
    }
    //REGISTER SURVEY DETAILS
    if (strpos($line, "{REGISTERERROR}") !== false) $line=str_replace("{REGISTERERROR}", $register_errormsg, $line);
    if (strpos($line, "{REGISTERMESSAGE1}") !== false) $line=str_replace("{REGISTERMESSAGE1}", $clang->gT("You must be registered to complete this survey"), $line);
    if (strpos($line, "{REGISTERMESSAGE2}") !== false) $line=str_replace("{REGISTERMESSAGE2}", $clang->gT("You may register for this survey if you wish to take part.")."<br />\n".$clang->gT("Enter your details below, and an email containing the link to participate in this survey will be sent immediately."), $line);
    if (strpos($line, "{REGISTERFORM}") !== false)
    {
        $registerform="<form method='post' action='{$publicurl}/register.php'>\n";
        if (!isset($_REQUEST['lang']))
        {
            $reglang = GetBaseLanguageFromSurveyID($surveyid);
        }
        else
        {
            $reglang = returnglobal('lang');
        }
        $registerform .= "<input type='hidden' name='lang' value='".$reglang."' />\n";
        $registerform .= "<input type='hidden' name='sid' value='$surveyid' id='sid' />\n";

        $registerform.="<table class='register' summary='Registrationform'>\n"
        ."<tr><td align='right'>"
        .$clang->gT("First name").":</td>"
        ."<td align='left'><input class='text' type='text' name='register_firstname'";
        if (isset($_POST['register_firstname']))
        {
            $registerform .= " value='".htmlentities(returnglobal('register_firstname'),ENT_QUOTES,'UTF-8')."'";
        }
        $registerform .= " /></td></tr>"
        ."<tr><td align='right'>".$clang->gT("Last name").":</td>\n"
        ."<td align='left'><input class='text' type='text' name='register_lastname'";
        if (isset($_POST['register_lastname']))
        {
            $registerform .= " value='".htmlentities(returnglobal('register_lastname'),ENT_QUOTES,'UTF-8')."'";
        }
        $registerform .= " /></td></tr>\n"
        ."<tr><td align='right'>".$clang->gT("Email address").":</td>\n"
        ."<td align='left'><input class='text' type='text' name='register_email'";
        if (isset($_POST['register_email']))
        {
            $registerform .= " value='".htmlentities(returnglobal('register_email'),ENT_QUOTES,'UTF-8')."'";
        }
        $registerform .= " /></td></tr>\n";


        if (function_exists("ImageCreate") && captcha_enabled('registrationscreen',$thissurvey['usecaptcha']))
        {
            $registerform .="<tr><td align='right'>".$clang->gT("Security Question").":</td><td><table><tr><td valign='middle'><img src='{$captchapath}verification.php?sid=$surveyid' alt='' /></td><td valign='middle'><input type='text' size='5' maxlength='3' name='loadsecurity' value='' /></td></tr></table></td></tr>\n";
        }
*/

        /*      if(isset($thissurvey['attribute1']) && $thissurvey['attribute1'])
         {
         $registerform .= "<tr><td align='right'>".$thissurvey['attribute1'].":</td>\n"
         ."<td align='left'><input class='text' type='text' name='register_attribute1'";
         if (isset($_POST['register_attribute1']))
         {
         $registerform .= " value='".htmlentities(returnglobal('register_attribute1'),ENT_QUOTES,'UTF-8')."'";
         }
         $registerform .= " /></td></tr>\n";
         }
         if(isset($thissurvey['attribute2']) && $thissurvey['attribute2'])
         {
         $registerform .= "<tr><td align='right'>".$thissurvey['attribute2'].":</td>\n"
         ."<td align='left'><input class='text' type='text' name='register_attribute2'";
         if (isset($_POST['register_attribute2']))
         {
         $registerform .= " value='".htmlentities(returnglobal('register_attribute2'),ENT_QUOTES,'UTF-8')."'";
         }
         $registerform .= " /></td></tr>\n";
         }        */
/**        $registerform .= "<tr><td></td><td><button class='nav-button ui-corner-all' id='registercontinue' class='submit' type='submit' >".$clang->gT("Continue")."</button>"
        ."</td></tr>\n"
        ."</table>\n"
        ."</form>\n";
        $line=str_replace("{REGISTERFORM}", $registerform, $line);
    }
    if (strpos($line, "{ASSESSMENT_CURRENT_TOTAL}") !== false && function_exists('doAssessment'))
    {
        $assessmentdata=doAssessment($surveyid,true);
        $line=str_replace("{ASSESSMENT_CURRENT_TOTAL}", $assessmentdata['total'], $line);
    }
    if (strpos($line, "{ASSESSMENTS}") !== false) $line=str_replace("{ASSESSMENTS}", $assessments, $line);
    if (strpos($line, "{ASSESSMENT_HEADING}") !== false) $line=str_replace("{ASSESSMENT_HEADING}", $clang->gT("Your Assessment"), $line);
    return $line;
}
*/
/**
 * insertAnsReplace() takes a string and looks for any {INSERTANS:xxxx} variables
 *  which it then, one by one, substitutes the SGQA code with the relevant answer
 *  from the session array containing responses
 *
 *  The operations of this function were previously in the templatereplace function
 *  but have been moved to a function of their own to make it available
 *  to other areas of the script.
 *
 * @param mixed $line   string - the string to iterate, and then return
 *
 * @return string This string is returned containing the substituted responses
 *
 */
function insertansReplace($line)
{
    global $CI;
    $dateformats = $CI->session->userdata('dateformats');
    if (!isset($dateformats['phpdate'])) $dateformats['phpdate']='';
    while (strpos($line, "{INSERTANS:") !== false)
    {
        $answreplace=substr($line, strpos($line, "{INSERTANS:"), strpos($line, "}", strpos($line, "{INSERTANS:"))-strpos($line, "{INSERTANS:")+1);
        $answreplace2=substr($answreplace, 11, strpos($answreplace, "}", strpos($answreplace, "{INSERTANS:"))-11);
        $answreplace3=strip_tags(retrieve_Answer($answreplace2));
        $line=str_replace($answreplace, $answreplace3, $line);
    }
    return $line;
}

/**
 * tokenReplace() takes a string and looks for any {TOKEN:xxxx} variables
 *  which it then, one by one, substitutes the TOKEN code with the relevant token
 *  from the session array containing token information
 *
 *  The operations of this function were previously in the templatereplace function
 *  but have been moved to a function of their own to make it available
 *  to other areas of the script.
 *
 * @param mixed $line   string - the string to iterate, and then return
 *
 * @return string This string is returned containing the substituted responses
 *
 */
function tokenReplace($line)
{
    global $surveyid,$CI;

    if ($CI->session->userdata('token'))
    {
        //Gather survey data for tokenised surveys, for use in presenting questions
        $CI->session->set_userdata('thistoken',getTokenData($surveyid, $CI->session->userdata('token')));
    }

    if ($CI->session->userdata('thistoken'))
    {
        $thistoken = $CI->session->userdata('thistoken');
        if (strpos($line, "{TOKEN:FIRSTNAME}") !== false) $line=str_replace("{TOKEN:FIRSTNAME}", $thistoken['firstname'], $line);
        if (strpos($line, "{TOKEN:LASTNAME}") !== false) $line=str_replace("{TOKEN:LASTNAME}", $thistoken['lastname'], $line);
        if (strpos($line, "{TOKEN:EMAIL}") !== false) $line=str_replace("{TOKEN:EMAIL}", $thistoken['email'], $line);
    }
    else
    {
        if (strpos($line, "{TOKEN:FIRSTNAME}") !== false) $line=str_replace("{TOKEN:FIRSTNAME}", "", $line);
        if (strpos($line, "{TOKEN:LASTNAME}") !== false) $line=str_replace("{TOKEN:LASTNAME}", "", $line);
        if (strpos($line, "{TOKEN:EMAIL}") !== false) $line=str_replace("{TOKEN:EMAIL}", "", $line);
    }
    $thistoken = $CI->session->userdata('thistoken');
    while (strpos($line, "{TOKEN:ATTRIBUTE_")!== false)
    {
        $templine=substr($line,strpos($line, "{TOKEN:ATTRIBUTE_"));
        $templine=substr($templine,0,strpos($templine, "}")+1);
        $attr_no=(int)substr($templine,17,strpos($templine, "}")-17);
        $replacestr='';
        if (isset($thistoken['attribute_'.$attr_no])) $replacestr=$thistoken['attribute_'.$attr_no];
        $line=str_replace($templine, $replacestr, $line);
    }
    return $line;
}

/**
 * passthruReplace() takes a string and looks for {PASSTHRULABEL}, {PASSTHRUVALUE} and {PASSTHRU:myarg} variables
 *  which it then substitutes for passthru data sent in the initial URL and stored
 *  in the session array containing responses
 *
 * @param mixed $line   string - the string to iterate, and then return
 * @param mixed $thissurvey     string - the string containing the surveyinformation
 * @return string This string is returned containing the substituted responses
 *
 */
function PassthruReplace($line, $thissurvey)
{
    $line=str_replace("{PASSTHRULABEL}", $thissurvey['passthrulabel'], $line);
    $line=str_replace("{PASSTHRUVALUE}", $thissurvey['passthruvalue'], $line);
    
    //  Replacement for variable passthru argument like {PASSTHRU:myarg}
    while (strpos($line,"{PASSTHRU:") !== false)
    {
        $p1 = strpos($line,"{PASSTHRU:"); // startposition
        $p2 = $p1 + 10; // position of the first arg char
        $p3 = strpos($line,"}",10); // position of the last arg char
        
        $cmd=substr($line,$p1,$p3-$p1+1); // extract the complete passthru like "{PASSTHRU:myarg}"
        $arg=substr($line,$p2,$p3-$p2); // extract the arg to passthru (like "myarg")
        
        // lookup for the fitting arg
        $qstring = $_SESSION['ls_initialquerystr']; // get initial query_string

        parse_str($qstring, $keyvalue); // split into key and value
        $match = 0; // prevent an endless loop if there is no arg in url
        foreach ($keyvalue as $key=>$value) // lookup loop
        {
            if ($key == $arg) // if match
            {
                $line=str_replace($cmd, $arg . "=" . $value, $line); // replace
                $match = 1;
                break;
            }
            
        }
        
        if ($match == 0)
        {
            $line=str_replace($cmd, $arg . "=", $line); // clears "{PASSTHRU:myarg} to "myarg=" if there was no myarg in calling url
        }
    }
    
    return $line;
} 

/**
 * This function returns a count of the number of saved responses to a survey
 *
 * @param mixed $surveyid Survey ID
 */
function getSavedCount($surveyid)
{
    global $CI;
    $surveyid=(int)$surveyid;
    $CI->load->model('saved_control');
    
    //$query = "SELECT COUNT(*) FROM ".db_table_name('saved_control')." WHERE sid=$surveyid";
    $count=$CI->saved_control->getCountOfAll($surveyid);
    return $count;
}

function GetBaseLanguageFromSurveyID($surveyid)
{
    static $cache = array();
    global $CI;
    $surveyid=(int)($surveyid);
    if (!isset($cache[$surveyid])) {
        $fields = array('language');
        $condition = " WHERE sid=$surveyid";
        $CI->load->model('surveys');
	    $query = $CI->surveys->getSomeRecords($fields,$condition);//("SELECT language FROM ".db_table_name('surveys')." WHERE sid=$surveyid";)
	    $surveylanguage = $query->row_array(); //Checked)
	    if (is_null($surveylanguage))
	    {
	        $surveylanguage='en';
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
    global $CI;
    $surveyid=sanitize_int($surveyid);
    if (!isset($cache[$surveyid])) {
        $fields = array('additional_languages');
        $condition = " WHERE sid=$surveyid";
        $CI->load->model('surveys');
	    $result = $CI->surveys->getSomeRecords($fields,$condition);
        //$query = "SELECT additional_languages FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
	    $additional_languages = $result->row_array();
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
    global $CI, $clang;
    $surveyid=sanitize_int($surveyid);
    $CI->load->config('lsconfig');
    $defaultlang = $CI->config->item('defaultlang');
    //require_once($rootdir.'/classes/core/language.php');
    if (isset($surveyid) && $surveyid>0)
    {
        // see if language actually is present in survey
        $fields = array('language', 'additional_languages');
        $condition = " WHERE sid=$surveyid";
        $CI->load->model('surveys');
        
        //$query = "SELECT  language, additional_languages FROM ".db_table_name('surveys')." WHERE sid=$surveyid";
        $result = $CI->surveys->getSomeRecords($fields,$condition); //Checked
        foreach ($result->result_array() as $row) {//while ($result && ($row=$result->FetchRow())) {)
            $additional_languages = $row['additional_languages'];
            $default_language = $row['language'];
        }

        if (!isset($language) || ($language=='') || (isset($additional_languages) && strpos($additional_languages, $language) === false)
        or (isset($default_language) && $default_language == $language)
        ) {
            // Language not supported, or default language for survey, fall back to survey's default language
            $this->session->set_userdata('s_lang',$default_language);
            //echo "Language not supported, resorting to ".$_SESSION['s_lang']."<br />";
        } else {
            $this->session->set_userdata('s_lang',$language);
            //echo "Language will be set to ".$_SESSION['s_lang']."<br />";
        }
        $lang = array($this->session->userdata('s_lang'));
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

    $thissurvey=getSurveyInfo($surveyid, $this->session->userdata('s_lang'));
    $this->session->userdata('dateformats',getDateFormatData($thissurvey['surveyls_dateformat']));
    return $clang;
}


function buildLabelSetCheckSumArray()
{
    global $CI;
    // BUILD CHECKSUMS FOR ALL EXISTING LABEL SETS
    $CI->load->model('labelsets');
    
    /**$query = "SELECT lid
              FROM ".db_table_name('labelsets')."
              ORDER BY lid"; */
    $result = $CI->labelsets->getLID();//($query) or safe_die("safe_died collecting labelset ids<br />$query<br />");  //Checked)
    $csarray=array();
    foreach($result->result_array() as $row)
    {
        $thisset="";
        $CI->load->models('labels');
        
        /**$query2 = "SELECT code, title, sortorder, language, assessment_value
                   FROM ".db_table_name('labels')."
                   WHERE lid={$row['lid']}
                   ORDER BY language, sortorder, code"; */
        $result2 = $CI->labels->getLabelCodeInfo($row['lid']) or safe_die("safe_died querying labelset $lid<br />$query2<br />"); //Checked
        foreach ($result2->result_array() as $row2)
        {
            $thisset .= implode('.', $row2);
        } // while
        $csarray[$row['lid']]=dechex(crc32($thisset)*1);
    }
    return $csarray;
}


/**
 *
 * Returns a flat array with all question attributes for the question only (and the qid we gave it)!
 * @author: c_schmitz
 * @param $qid The question ID
 * @param $type optional The question type - saves a DB query if you provide it
 * @return array{attribute=>value , attribute=>value} or false if the question ID does not exist (anymore)
 */
function getQuestionAttributes($qid, $type='')
{
    global $CI;
    static $cache = array();
    static $availableattributesarr = null;

    if (isset($cache[$qid])) {
        return $cache[$qid];
    }
    if ($type=='')  // If type is not given find it out
    {
        $CI->load->model('questions');
        //$query = "SELECT type FROM ".db_table_name('questions')." WHERE qid=$qid and parent_qid=0 group by type";
        $result = $CI->questions->getQuestionType($qid) or safe_die("Error finding question attributes");  //Checked
        $row=$result->row_array();
        if ($row===false) // Question was deleted while running the survey
        {
            $cache[$qid]=false;
            return false;
        }
        $type=$row['type'];
    }

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
        $defaultattributes[$attribute['name']]=$attribute['default'];
    }
    $setattributes=array();
    $qid=sanitize_int($qid);
    $fields = array('attribute', 'value');
    $condition = "WHERE qid=$qid";
    $CI->load->model('question_attributes');
    //$query = "SELECT attribute, value FROM ".db_table_name('question_attributes')." WHERE qid=$qid";
    $result = $CI->question_attributes->getSomeRecords($fields,$condition) or safe_die("Error finding question attributes");  //Checked)
    $setattributes=array();
    foreach ($result->result_array() as $row)
    {
        $setattributes[$row['attribute']]=$row['value'];
    }
    //echo "<pre>";print_r($qid_attributes);echo "</pre>";
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
 * @return string
 */
function getQuestionAttributeValue($questionAttributeArray, $attributeName)
{
    if (isset($questionAttributeArray[$attributeName]))
    {
        return $questionAttributeArray[$attributeName];
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
    global $clang,$CI;
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
    "help"=>$clang->gT('Enter a header text for the first scale'),
    "caption"=>$clang->gT('Header for first scale'));

    $qattributes["dualscale_headerB"]=array(
    "types"=>"1",
    'category'=>$clang->gT('Display'),
    'sortorder'=>111,
    'inputtype'=>'text',
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
    "types"=>"!KLMNOPRWZ",
    'category'=>$clang->gT('Display'),
    'sortorder'=>100,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('Hide the tip that is normally shown with a question'),
    "caption"=>$clang->gT('Hide tip'));

    $qattributes['hidden']=array(
    'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|',
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
    "types"=>"MPLW!Z",
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
    "help"=>$clang->gT("Replaces the label of the 'Other:' answer option with a custom text"),
    "caption"=>$clang->gT("Label for 'Other:' option"));

    $qattributes["page_break"]=array(
    "types"=>"15ABCDEFGHKLMNOPQRSTUWXYZ!:;|",
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
    "help"=>$clang->gT('Add a prefix to the answer field'),
    "caption"=>$clang->gT('Answer prefix'));

    $qattributes["public_statistics"]=array(
    "types"=>"15ABCEFGHKLMNOPRWYZ!:",
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
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('Present answers in random order'),
    "caption"=>$clang->gT('Random answer order'));

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
    "types"=>"1F",
    'category'=>$clang->gT('Display'),
    'sortorder'=>112,
    'inputtype'=>'singleselect',
    'options'=>array(0=>$clang->gT('No'),
    1=>$clang->gT('Yes')),
    'default'=>0,
    "help"=>$clang->gT('Use dropdown boxes instead of list of radio buttons'),
    "caption"=>$clang->gT('Use dropdown boxes'));

    $qattributes["scale_export"]=array(
    "types"=>"CEFGHLMOPWYZ1!:",
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
    "inputtype"=>"textarea",
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
                $qat[substr($qvalue['types'], $i, 1)][]=array("name"=>$qname,
                                                            "inputtype"=>$qvalue['inputtype'],
                                                            "category"=>$qvalue['category'],
                                                            "sortorder"=>$qvalue['sortorder'],
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