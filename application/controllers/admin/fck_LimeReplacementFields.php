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
 * $Id: fck_LimeReplacementFields.php 11184 2011-10-17 08:02:36Z aniesshsethh $
 */
class fck_LimeReplacementFields extends Survey_Common_Action
{

    /**
     * routes to the correct subdir
     *
     * @access public
     * @param string $sa
     * @return void
     */
    public function run($sa)
    {
        $this->route($sa, array('fieldtype', 'action', 'surveyid', 'gid', 'qid'));
    }

    public function index($fieldtype, $action, $surveyid = false, $gid = false, $qid = false)
    {
        if ($surveyid != false) {
            $surveyid = sanitize_int($surveyid);
        }
        if ($gid != false) {
            $gid = sanitize_int($gid);
        }
        if ($qid != false) {
            $qid = sanitize_int($qid);
        }

        $clang = Yii::app()->lang;

        if (!Yii::app()->session['loginID']) {
            die ("Unauthenticated Access Forbiden");
        }

        list($replacementFields, $isInstertAnswerEnabled) = $this->_getReplacementFields($fieldtype, $surveyid);

        if ($isInstertAnswerEnabled === true) {
            if (empty($surveyid)) {
                safe_die("No SID provided.");
            }

            //2: Get all other questions that occur before this question that are pre-determined answer types
            $fieldmap = createFieldMap($surveyid, 'full');

            $surveyInfo = getSurveyInfo($surveyid);
            $surveyformat = $surveyInfo['format']; // S, G, A

            //Go through each question until we reach the current one
            //error_log(print_r($qrows,true));
            $questionlist = $this->_getQuestionList($action, $gid, $qid, $fieldmap, $fieldtype, $surveyformat);
            $childQuestions = $this->_getChildQuestions($questionlist);
        }

        $data['countfields'] = count($replacementFields);
        $data['replFields'] = $replacementFields;
        $data['clang'] = $clang;
        if (isset($childQuestions)) {
            $data['cquestions'] = $childQuestions;
        }
        if (isset($surveyformat)) {
            $data['surveyformat'] = $surveyformat;
        }

        $this->getController()->render('/admin/limeReplacementFields_view', $data);
    }

    public function _getQuestionList($action, $gid, $qid, array $fieldmap, $questionType, $surveyformat)
    {
        $previousQuestion = null;
        $isPreviousPageQuestion = true;
        $questionList = array();

        foreach ($fieldmap as $question)
        {
            if (empty($question['qid'])) {
                continue;
            }

            if ($this->_shouldAddQuestion($action, $gid, $qid, $question, $previousQuestion)) {
                $isPreviousPageQuestion = $this->_addQuestionToList($action, $gid, $question, $questionType, $surveyformat, $isPreviousPageQuestion, $questionList);
                $previousQuestion = $question;
            }
            else
            {
                break;
            }
        }
        return $questionList;
    }

    private function _shouldAddQuestion($action, $gid, $qid, array $question, $previousQuestion)
    {
        switch ($action)
        {
            case 'addgroup':
                return true;

            case 'editgroup':
            case 'editgroup_desc':
            case 'translategroup':
                if (empty($gid)) {
                    safe_die("No GID provided.");
                }

                if ($question['gid'] == $gid) {
                    return false;
                }
                return true;

            case 'addquestion':
                if (empty($gid)) {
                    safe_die("No GID provided.");
                }

                if (!is_null($previousQuestion) && $previousQuestion['gid'] == $gid && $question['gid'] != $gid ) {
                    return false;
                }
                return true;

            case 'editanswer':
            case 'copyquestion':
            case 'editquestion':
            case 'translatequestion':
            case 'translateanswer':
                if (empty($gid)) {
                    safe_die("No GID provided.");
                }
                if (empty($qid)) {
                    safe_die("No QID provided.");
                }

                if ($question['gid'] == $gid && $question['qid'] == $qid) {
                   return false;
                }
                return true;
            case 'emailtemplates':
                // this is the case for email-conf
                return true;
            default:
                safe_die("No Action provided.");
        }
    }

    private function _addQuestionToList($action, $gid, array $field, $questionType, $surveyformat, $isPreviousPageQuestion, &$questionList)
    {
        if ($action == 'tokens' && $questionType == 'email-conf' || $surveyformat == "S") {
            $isPreviousPageQuestion = true;
        }
        elseif ($surveyformat == "G")
        {
            if ($isPreviousPageQuestion === true) { // Last question was on a previous page
                if ($field["gid"] == $gid) { // This question is on same page
                    $isPreviousPageQuestion = false;
                }
            }
        }
        elseif ($surveyformat == "A")
        {
            $isPreviousPageQuestion = false;
        }

        $questionList[] = array_merge($field, Array("previouspage" => $isPreviousPageQuestion));

        return $isPreviousPageQuestion;
    }

    private function _getChildQuestions(array $questions)
    {
        $cquestions = array();

        foreach ($questions as $row)
        {
            $question = $row['question'];

            if (isset($row['subquestion'])) {
                $question = "[{$row['subquestion']}] " . $question;
            }
            if (isset($row['subquestion1'])) {
                $question = "[{$row['subquestion1']}] " . $question;
            }
            if (isset($row['subquestion2'])) {
                $question = "[{$row['subquestion2']}] " . $question;
            }

            $shortquestion = $row['title'] . ": " . FlattenText($question);
            $cquestions[] = array($shortquestion, $row['qid'], $row['type'], $row['fieldname'], $row['previouspage']);
        }
        return $cquestions;
    }

    private function _getReplacementFields($fieldtype, $surveyid)
    {
        $clang = Yii::app()->lang;
        $replFields = array();

        switch ($fieldtype)
        {
            case 'survey-desc':
            case 'survey-welc':
            case 'survey-endtext':
            case 'edittitle': // for translation
            case 'editdescription': // for translation
            case 'editwelcome': // for translation
            case 'editend': // for translation
                $replFields[] = array('TOKEN:FIRSTNAME', $clang->gT("Firstname from token"));
                $replFields[] = array('TOKEN:LASTNAME', $clang->gT("Lastname from token"));
                $replFields[] = array('TOKEN:EMAIL', $clang->gT("Email from the token"));
                $attributes = GetTokenFieldsAndNames($surveyid, true);
                foreach ($attributes as $attributefield => $attributedescription)
                {
                    $replFields[] = array('TOKEN:' . strtoupper($attributefield), sprintf($clang->gT("Token attribute: %s"), $attributedescription));
                }
                $replFields[] = array('EXPIRY', $clang->gT("Survey expiration date"));
                return array($replFields, false);

            case 'email-admin-notification':
                $replFields[] = array('RELOADURL', $clang->gT("Reload URL"));
                $replFields[] = array('VIEWRESPONSEURL', $clang->gT("View response URL"));
                $replFields[] = array('EDITRESPONSEURL', $clang->gT("Edit response URL"));
                $replFields[] = array('STATISTICSURL', $clang->gT("Statistics URL"));
                $replFields[] = array('TOKEN', $clang->gT("Token code for this participant"));
                $replFields[] = array('TOKEN:FIRSTNAME', $clang->gT("First name from token"));
                $replFields[] = array('TOKEN:LASTNAME', $clang->gT("Last name from token"));
                $replFields[] = array('SURVEYNAME', $clang->gT("Name of the survey"));
                $replFields[] = array('SURVEYDESCRIPTION', $clang->gT("Description of the survey"));
                $attributes = GetTokenFieldsAndNames($surveyid, true);
                foreach ($attributes as $attributefield => $attributedescription)
                {
                    $replFields[] = array(strtoupper($attributefield), sprintf($clang->gT("Token attribute: %s"), $attributedescription));
                }
                $replFields[] = array('ADMINNAME', $clang->gT("Name of the survey administrator"));
                $replFields[] = array('ADMINEMAIL', $clang->gT("Email address of the survey administrator"));
                return array($replFields, false);

            case 'email-admin-resp':
                $replFields[] = array('RELOADURL', $clang->gT("Reload URL"));
                $replFields[] = array('VIEWRESPONSEURL', $clang->gT("View response URL"));
                $replFields[] = array('EDITRESPONSEURL', $clang->gT("Edit response URL"));
                $replFields[] = array('STATISTICSURL', $clang->gT("Statistics URL"));
                $replFields[] = array('ANSWERTABLE', $clang->gT("Answers from this response"));
                $replFields[] = array('TOKEN', $clang->gT("Token code for this participant"));
                $replFields[] = array('TOKEN:FIRSTNAME', $clang->gT("First name from token"));
                $replFields[] = array('TOKEN:LASTNAME', $clang->gT("Last name from token"));
                $replFields[] = array('SURVEYNAME', $clang->gT("Name of the survey"));
                $replFields[] = array('SURVEYDESCRIPTION', $clang->gT("Description of the survey"));
                $attributes = GetTokenFieldsAndNames($surveyid, true);
                foreach ($attributes as $attributefield => $attributedescription)
                {
                    $replFields[] = array(strtoupper($attributefield), sprintf($clang->gT("Token attribute: %s"), $attributedescription));
                }
                $replFields[] = array('ADMINNAME', $clang->gT("Name of the survey administrator"));
                $replFields[] = array('ADMINEMAIL', $clang->gT("Email address of the survey administrator"));
                return array($replFields, false);

            case 'email-inv':
            case 'email-rem':
                // these 2 fields are supported by email-inv and email-rem
                // but not email-reg for the moment
                $replFields[] = array('EMAIL', $clang->gT("Email from the token"));
                $replFields[] = array('TOKEN', $clang->gT("Token code for this participant"));
                $replFields[] = array('OPTOUTURL', $clang->gT("URL for a respondent to opt-out this survey"));
                $replFields[] = array('OPTINURL', $clang->gT("URL for a respondent to opt-in this survey"));
            case 'email-reg':
                $replFields[] = array('FIRSTNAME', $clang->gT("Firstname from token"));
                $replFields[] = array('LASTNAME', $clang->gT("Lastname from token"));
                $replFields[] = array('SURVEYNAME', $clang->gT("Name of the survey"));
                $replFields[] = array('SURVEYDESCRIPTION', $clang->gT("Description of the survey"));
                $attributes = GetTokenFieldsAndNames($surveyid, true);
                foreach ($attributes as $attributefield => $attributedescription)
                {
                    $replFields[] = array(strtoupper($attributefield), sprintf($clang->gT("Token attribute: %s"), $attributedescription));
                }
                $replFields[] = array('ADMINNAME', $clang->gT("Name of the survey administrator"));
                $replFields[] = array('ADMINEMAIL', $clang->gT("Email address of the survey administrator"));
                $replFields[] = array('SURVEYURL', $clang->gT("URL of the survey"));
                $replFields[] = array('EXPIRY', $clang->gT("Survey expiration date"));
                return array($replFields, false);

            case 'email-conf':
                $replFields[] = array('TOKEN', $clang->gT("Token code for this participant"));
                $replFields[] = array('FIRSTNAME', $clang->gT("Firstname from token"));
                $replFields[] = array('LASTNAME', $clang->gT("Lastname from token"));
                $replFields[] = array('SURVEYNAME', $clang->gT("Name of the survey"));
                $replFields[] = array('SURVEYDESCRIPTION', $clang->gT("Description of the survey"));
                $attributes = GetTokenFieldsAndNames($surveyid, true);
                foreach ($attributes as $attributefield => $attributedescription)
                {
                    $replFields[] = array(strtoupper($attributefield), sprintf($clang->gT("Token attribute: %s"), $attributedescription));
                }
                $replFields[] = array('ADMINNAME', $clang->gT("Name of the survey administrator"));
                $replFields[] = array('ADMINEMAIL', $clang->gT("Email address of the survey administrator"));
                $replFields[] = array('SURVEYURL', $clang->gT("URL of the survey"));
                $replFields[] = array('EXPIRY', $clang->gT("Survey expiration date"));

                // email-conf can accept insertans fields for non anonymous surveys
                if (isset($surveyid)) {
                    $surveyInfo = getSurveyInfo($surveyid);
                    if ($surveyInfo['anonymized'] == "N") {
                        return array($replFields, true);
                    }
                }
                return array($replFields, false);

            case 'group-desc':
            case 'question-text':
            case 'question-help':
            case 'editgroup': // for translation
            case 'editgroup_desc': // for translation
            case 'editquestion': // for translation
            case 'editquestion_help': // for translation
                $replFields[] = array('TOKEN:FIRSTNAME', $clang->gT("Firstname from token"));
                $replFields[] = array('TOKEN:LASTNAME', $clang->gT("Lastname from token"));
                $replFields[] = array('TOKEN:EMAIL', $clang->gT("Email from the token"));
                $replFields[] = array('SID', $clang->gT("This question's survey ID number"));
                $replFields[] = array('GID', $clang->gT("This question's group ID number"));
                $replFields[] = array('QID', $clang->gT("This question's question ID number"));
                $replFields[] = array('SGQ', $clang->gT("This question's SGQA code"));
                $attributes = GetTokenFieldsAndNames($surveyid, true);
                foreach ($attributes as $attributefield => $attributedescription)
                {
                    $replFields[] = array('TOKEN:' . strtoupper($attributefield), sprintf($clang->gT("Token attribute: %s"), $attributedescription));
                }
                $replFields[] = array('EXPIRY', $clang->gT("Survey expiration date"));
            case 'editanswer':
                return array($replFields, true);

            case 'assessment-text':
                $replFields[] = array('TOTAL', $clang->gT("Overall assessment score"));
                $replFields[] = array('PERC', $clang->gT("Assessment group score"));
                return array($replFields, false);
        }
    }

}