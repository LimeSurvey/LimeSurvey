<?php
/*
 * LimeSurvey
 * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
  */
class limereplacementfields extends Survey_Common_Action
{

    public function index()
    {
        $surveyid = intval(App()->request->getQuery('surveyid'));
        $gid = intval(App()->request->getQuery('gid'));
        $qid = intval(App()->request->getQuery('qid'));
        $fieldtype = sanitize_xss_string(App()->request->getQuery('fieldtype'));
        $action = sanitize_xss_string(App()->request->getQuery('action'));
        if (!Yii::app()->session['loginID'])
        {
            throw new CHttpException(401);
        }
        list($replacementFields, $isInsertAnswerEnabled) = $this->_getReplacementFields($fieldtype, $surveyid);

        if ($isInsertAnswerEnabled === true)
        {
            //2: Get all other questions that occur before this question that are pre-determined answer types
            $fieldmap = createFieldMap($surveyid,'full',false,false,getBaseLanguageFromSurveyID($surveyid));

            $surveyInfo = getSurveyInfo($surveyid);
            $surveyformat = $surveyInfo['format']; // S, G, A

            //Go through each question until we reach the current one
            //error_log(print_r($qrows,true));
            $questionlist = $this->_getQuestionList($action, $gid, $qid, $fieldmap, $fieldtype, $surveyformat);
            $childQuestions = $this->_getChildQuestions($questionlist);
        }

        $data['countfields'] = count($replacementFields);
        $data['replFields'] = $replacementFields;
        if (isset($childQuestions)) {
            $data['cquestions'] = $childQuestions;
        }
        if (isset($surveyformat)) {
            $data['surveyformat'] = $surveyformat;
        }

        $this->getController()->render('/admin/limeReplacementFields_view', $data);
    }

    /**
     * @param integer $gid
     * @param integer $qid
     */
    private function _getQuestionList($action, $gid, $qid, array $fieldmap, $questionType, $surveyformat)
    {
        $previousQuestion = null;
        $isPreviousPageQuestion = true;
        $questionList = array();

        foreach ($fieldmap as $question)
        {
            if (empty($question['qid']))
            {
                continue;
            }

            if (is_null($qid) || $this->_shouldAddQuestion($action, $gid, $qid, $question, $previousQuestion))
            {
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

    /**
     * @param integer $gid
     * @param integer $qid
     */
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
                    safeDie("No GID provided.");
                }

                if ($question['gid'] == $gid) {
                    return false;
                }
                return true;

            case 'addquestion':
                if (empty($gid)) {
                    safeDie("No GID provided. Please save the question and try again.");
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
                    safeDie("No GID provided.");
                }
                if (empty($qid)) {
                    safeDie("No QID provided.");
                }

                if ($question['gid'] == $gid && $question['qid'] == $qid) {
                   return false;
                }
                return true;
            case 'editemailtemplates':
                // this is the case for email-conf
                return true;
            default:
                safeDie("No Action provided.");
        }
    }

    /**
     * @param integer $gid
     */
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

            $shortquestion = $row['title'] . ": " . flattenText($question);
            $cquestions[] = array($shortquestion, $row['qid'], $row['type'], $row['fieldname'], $row['previouspage']);
        }
        return $cquestions;
    }

    /**
     * @param integer $surveyid
     */
    private function _getReplacementFields($fieldtype, $surveyid)
    {

        $replFields = array();
        if(!$surveyid)
            return array($replFields, false);
        switch ($fieldtype)
        {
            case 'survey-desc':
            case 'survey-welc':
            case 'survey-endtext':
            case 'edittitle': // for translation
            case 'editdescription': // for translation
            case 'editwelcome': // for translation
            case 'editend': // for translation
                $replFields[] = array('TOKEN:FIRSTNAME', gT("First name from token"));
                $replFields[] = array('TOKEN:LASTNAME', gT("Last name from token"));
                $replFields[] = array('TOKEN:EMAIL', gT("Email from the token"));
                $attributes = getTokenFieldsAndNames($surveyid, true);
                foreach ($attributes as $attributefield => $attributedescription)
                {
                    $replFields[] = array('TOKEN:' . strtoupper($attributefield), sprintf(gT("Token attribute: %s"), $attributedescription['description']));
                }
                $replFields[] = array('EXPIRY', gT("Survey expiration date"));
                $replFields[] = array('ADMINNAME', gT("Name of the survey administrator"));
                $replFields[] = array('ADMINEMAIL', gT("Email address of the survey administrator"));
                return array($replFields, false);

            case 'email-admin_notification':
            case 'email-admin_detailed_notification':
                $replFields[] = array('RELOADURL', gT("Reload URL"));
                $replFields[] = array('VIEWRESPONSEURL', gT("View response URL"));
                $replFields[] = array('EDITRESPONSEURL', gT("Edit response URL"));
                $replFields[] = array('STATISTICSURL', gT("Statistics URL"));
                $replFields[] = array('TOKEN', gT("Token code for this participant"));
                $replFields[] = array('TOKEN:FIRSTNAME', gT("First name from token"));
                $replFields[] = array('TOKEN:LASTNAME', gT("Last name from token"));
                $replFields[] = array('SURVEYNAME', gT("Name of the survey"));
                $replFields[] = array('SURVEYDESCRIPTION', gT("Description of the survey"));
                $attributes = getTokenFieldsAndNames($surveyid, true);
                foreach ($attributes as $attributefield => $attributedescription)
                {
                    $replFields[] = array(strtoupper($attributefield), sprintf(gT("Token attribute: %s"), $attributedescription['description']));
                }
                $replFields[] = array('ADMINNAME', gT("Name of the survey administrator"));
                $replFields[] = array('ADMINEMAIL', gT("Email address of the survey administrator"));
                return array($replFields, false);

            case 'email-admin-resp':
                $replFields[] = array('RELOADURL', gT("Reload URL"));
                $replFields[] = array('VIEWRESPONSEURL', gT("View response URL"));
                $replFields[] = array('EDITRESPONSEURL', gT("Edit response URL"));
                $replFields[] = array('STATISTICSURL', gT("Statistics URL"));
                $replFields[] = array('ANSWERTABLE', gT("Answers from this response"));
                $replFields[] = array('TOKEN', gT("Token code for this participant"));
                $replFields[] = array('TOKEN:FIRSTNAME', gT("First name from token"));
                $replFields[] = array('TOKEN:LASTNAME', gT("Last name from token"));
                $replFields[] = array('SURVEYNAME', gT("Name of the survey"));
                $replFields[] = array('SURVEYDESCRIPTION', gT("Description of the survey"));
                $attributes = getTokenFieldsAndNames($surveyid, true);
                foreach ($attributes as $attributefield => $attributedescription)
                {
                    $replFields[] = array(strtoupper($attributefield), sprintf(gT("Token attribute: %s"), $attributedescription['description']));
                }
                $replFields[] = array('ADMINNAME', gT("Name of the survey administrator"));
                $replFields[] = array('ADMINEMAIL', gT("Email address of the survey administrator"));
                return array($replFields, false);

            case 'email-invitation':
            case 'email-reminder':
                // these 2 fields are supported by email-inv and email-rem
                // but not email-reg for the moment
                $replFields[] = array('EMAIL', gT("Email from the token"));
                $replFields[] = array('TOKEN', gT("Token code for this participant"));
                $replFields[] = array('OPTOUTURL', gT("URL for a respondent to opt-out of this survey"));
                $replFields[] = array('OPTINURL', gT("URL for a respondent to opt-in to this survey"));
            case 'email-registration':
                $replFields[] = array('FIRSTNAME', gT("First name from token"));
                $replFields[] = array('LASTNAME', gT("Last name from token"));
                $replFields[] = array('SURVEYNAME', gT("Name of the survey"));
                $replFields[] = array('SURVEYDESCRIPTION', gT("Description of the survey"));
                $attributes = getTokenFieldsAndNames($surveyid, true);
                foreach ($attributes as $attributefield => $attributedescription)
                {
                    $replFields[] = array(strtoupper($attributefield), sprintf(gT("Token attribute: %s"), $attributedescription['description']));
                }
                $replFields[] = array('ADMINNAME', gT("Name of the survey administrator"));
                $replFields[] = array('ADMINEMAIL', gT("Email address of the survey administrator"));
                $replFields[] = array('SURVEYURL', gT("URL of the survey"));
                $replFields[] = array('EXPIRY', gT("Survey expiration date"));
                return array($replFields, false);

            case 'email-confirmation':
                $replFields[] = array('TOKEN', gT("Token code for this participant"));
                $replFields[] = array('FIRSTNAME', gT("First name from token"));
                $replFields[] = array('LASTNAME', gT("Last name from token"));
                $replFields[] = array('EMAIL', gT("Email from token"));
                $replFields[] = array('SURVEYNAME', gT("Name of the survey"));
                $replFields[] = array('SURVEYDESCRIPTION', gT("Description of the survey"));
                $attributes = getTokenFieldsAndNames($surveyid, true);
                foreach ($attributes as $attributefield => $attributedescription)
                {
                    $replFields[] = array(strtoupper($attributefield), sprintf(gT("Token attribute: %s"), $attributedescription['description']));
                }
                $replFields[] = array('ADMINNAME', gT("Name of the survey administrator"));
                $replFields[] = array('ADMINEMAIL', gT("Email address of the survey administrator"));
                $replFields[] = array('SURVEYURL', gT("URL of the survey"));
                $replFields[] = array('EXPIRY', gT("Survey expiration date"));

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
                $replFields[] = array('TOKEN:FIRSTNAME', gT("First name from token"));
                $replFields[] = array('TOKEN:LASTNAME', gT("Last name from token"));
                $replFields[] = array('TOKEN:EMAIL', gT("Email from the token"));
                $replFields[] = array('SID', gT("This question's survey ID number"));
                $replFields[] = array('GID', gT("This question's group ID number"));
                $replFields[] = array('QID', gT("This question's question ID number"));
                $replFields[] = array('SGQ', gT("This question's SGQA code"));
                $attributes = getTokenFieldsAndNames($surveyid, true);
                foreach ($attributes as $attributefield => $attributedescription)
                {
                    $replFields[] = array('TOKEN:' . strtoupper($attributefield), sprintf(gT("Token attribute: %s"), $attributedescription['description']));
                }
                $replFields[] = array('EXPIRY', gT("Survey expiration date"));
            case 'editanswer':
                return array($replFields, true);

            case 'assessment-text':
                $replFields[] = array('TOTAL', gT("Overall assessment score"));
                $replFields[] = array('PERC', gT("Assessment group score"));
                return array($replFields, false);
        }
    }

}
