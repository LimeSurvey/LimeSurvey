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
        $surveyid = (int) App()->request->getQuery('surveyid');
        $gid = (int) App()->request->getQuery('gid');
        $qid = (int) App()->request->getQuery('qid');
        $newType = (bool) App()->request->getQuery('newtype', 0);

        $survey = Survey::model()->findByPk($surveyid);
        $fieldtype = sanitize_xss_string(App()->request->getQuery('fieldtype'));
        $action = sanitize_xss_string(App()->request->getQuery('action'));

        if (!Yii::app()->session['loginID']) {
            throw new CHttpException(401);
        }

        if ($newType) {
            $this->getNewTypeResponse($fieldtype, $surveyid, $gid, $qid );
            return;
        }

        list($replacementFields, $isInsertAnswerEnabled) = $this->_getReplacementFields($fieldtype, $surveyid);

        if ($isInsertAnswerEnabled === true) {
            //2: Get all other questions that occur before this question that are pre-determined answer types
            $fieldmap = createFieldMap($survey, 'full', false, false, $survey->language);

            $surveyformat = $survey->format; // S, G, A

            //Go through each question until we reach the current one
            //error_log(print_r($qrows,true));
            $questionlist = $this->_getQuestionList($action, $gid, $qid, $fieldmap, $fieldtype, $surveyformat);
            $childQuestions = $this->_getChildQuestions($questionlist);
        }

        $data = [];
        $data['countfields'] = count($replacementFields);
        $data['replacements'] = [];

        asort($replacementFields);
        $data['replacements'][gt('General replacements')] = $replacementFields;

        if (isset($childQuestions)) {
            $data['replacements'][gT('Questions')] = $childQuestions;
        }
        if (isset($surveyformat)) {
            $data['replacements'][gT('Survey format')] = $surveyformat;
        }

        $this->getController()->renderPartial('/admin/super/_renderJson', ['data' => $data]);
        return;
    }

    /**
     * @param integer $gid
     * @param integer $qid
     * @param string $surveyformat
     * @return array
     */
    private function _getQuestionList($action, $gid, $qid, array $fieldmap, $questionType, $surveyformat)
    {
        $previousQuestion = null;
        $isPreviousPageQuestion = true;
        $questionList = array();

        foreach ($fieldmap as $question) {
            if (empty($question['qid'])) {
                continue;
            }

            if (is_null($qid) || $this->_shouldAddQuestion($action, $gid, $qid, $question, $previousQuestion)) {
                $isPreviousPageQuestion = $this->_addQuestionToList($action, $gid, $question, $questionType, $surveyformat, $isPreviousPageQuestion, $questionList);
                $previousQuestion = $question;
            } else {
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
        switch ($action) {
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

                if (!is_null($previousQuestion) && $previousQuestion['gid'] == $gid && $question['gid'] != $gid) {
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
     * @param string $surveyformat
     */
    private function _addQuestionToList($action, $gid, array $field, $questionType, $surveyformat, $isPreviousPageQuestion, &$questionList)
    {
        if ($action == 'tokens' && $questionType == 'email-conf' || $surveyformat == "S") {
            $isPreviousPageQuestion = true;
        } elseif ($surveyformat == "G") {
            if ($isPreviousPageQuestion === true) {
// Last question was on a previous page
                if ($field["gid"] == $gid) {
// This question is on same page
                    $isPreviousPageQuestion = false;
                }
            }
        } elseif ($surveyformat == "A") {
            $isPreviousPageQuestion = false;
        }

        $questionList[] = array_merge($field, array("previouspage" => $isPreviousPageQuestion));

        return $isPreviousPageQuestion;
    }

    private function _getChildQuestions(array $questions)
    {
        $cquestions = array();

        foreach ($questions as $row) {
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
     * Collect the general replacements
     * 
     * @param string  $fieldtype The field to collect replacements for
     * @param integer $surveyid  The transferred surveyid
     * 
     * @return array
     */
    private function _getReplacementFields($fieldtype, $surveyid)
    {
        $oSurvey = Survey::model()->findByPk($surveyid);
        $replFields = array();

        // The only time no survey id is necessary is in the global settings
        if ($fieldtype === 'globalSurveySettings') {
            $replFields['TOKEN:FIRSTNAME'] = gT("First name of the participant");
            $replFields['TOKEN:LASTNAME'] = gT("Last name of the participant");
            $replFields['TOKEN:EMAIL'] = gT("Email address of the participant");
            $replFields['EXPIRY'] = gT("Survey expiration date");
            $replFields['ADMINNAME'] = gT("Name of the survey administrator");
            $replFields['ADMINEMAIL'] = gT("Email address of the survey administrator");
            return array($replFields, false);
        }

        if (!$surveyid) {
            return array($replFields, false);
        }

        if (strpos($fieldtype, 'survey-desc') !== false
            || strpos($fieldtype, 'survey-welc') !== false
            || strpos($fieldtype, 'survey-endtext') !== false
            || strpos($fieldtype, 'edittitle') !== false// for translation
            || strpos($fieldtype, 'editdescription') !== false// for translation
            || strpos($fieldtype, 'editwelcome') !== false// for translation
            || strpos($fieldtype, 'editend') !== false
        ) { // for translation

            $replFields['TOKEN:FIRSTNAME'] = gT("First name of the participant");
            $replFields['TOKEN:LASTNAME'] = gT("Last name of the participant");
            $replFields['TOKEN:EMAIL'] = gT("Email address of the participant");
            $attributes = getTokenFieldsAndNames($surveyid, true);

            foreach ($attributes as $attributefield => $attributedescription) {
                $replFields['TOKEN:' . strtoupper($attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
            }

            $replFields['EXPIRY'] = gT("Survey expiration date");
            $replFields['ADMINNAME'] = gT("Name of the survey administrator");
            $replFields['ADMINEMAIL'] = gT("Email address of the survey administrator");
            return array($replFields, false);

        } elseif (strpos($fieldtype, 'email_admin_notification') !== false
            || strpos($fieldtype, 'email_admin_detailed_notification') !== false
        ) {
            $replFields['VIEWRESPONSEURL'] = gT("View response URL");
            $replFields['EDITRESPONSEURL'] = gT("Edit response URL");
            $replFields['STATISTICSURL'] = gT("Statistics URL");
            $replFields['TOKEN'] = gT("Access code for this participant");
            $replFields['TOKEN:FIRSTNAME'] = gT("First name of the participant");
            $replFields['TOKEN:LASTNAME'] = gT("Last name of the participant");
            $replFields['SURVEYNAME'] = gT("Survey title");
            $replFields['SID'] = gT("Survey ID");
            $replFields['SURVEYDESCRIPTION'] = gT("Survey description");
            $attributes = getTokenFieldsAndNames($surveyid, true);

            foreach ($attributes as $attributefield => $attributedescription) {
                $replFields[strtoupper($attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
            }

            $replFields['ADMINNAME'] = gT("Name of the survey administrator");
            $replFields['ADMINEMAIL'] = gT("Email address of the survey administrator");
            return array($replFields, false);

        } elseif (strpos($fieldtype, 'email-admin-resp') !== false) {
            $replFields['VIEWRESPONSEURL'] = gT("View response URL");
            $replFields['EDITRESPONSEURL'] = gT("Edit response URL");
            $replFields['STATISTICSURL'] = gT("Statistics URL");
            $replFields['ANSWERTABLE'] = gT("Answers in this response");
            $replFields['TOKEN'] = gT("Access code for this participant");
            $replFields['TOKEN:FIRSTNAME'] = gT("First name of the participant");
            $replFields['TOKEN:LASTNAME'] = gT("Last name of the participant");
            $replFields['SURVEYNAME'] = gT("Survey title");
            $replFields['SID'] = gT("Survey ID");
            $replFields['SURVEYDESCRIPTION'] = gT("Survey description");
            $attributes = getTokenFieldsAndNames($surveyid, true);

            foreach ($attributes as $attributefield => $attributedescription) {
                $replFields[strtoupper($attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
            }

            $replFields['ADMINNAME'] = gT("Name of the survey administrator");
            $replFields['ADMINEMAIL'] = gT("Email address of the survey administrator");
            return array($replFields, false);

        } elseif (strpos($fieldtype, 'email_invitation') !== false
            || strpos($fieldtype, 'email_reminder') !== false
        ) {
            // these 2 fields are supported by email-inv and email-rem
            // but not email-reg for the moment
            $replFields['EMAIL'] = gT("Email address of the participant");
            $replFields['TOKEN'] = gT("Access code for this participant");
            $replFields['OPTOUTURL'] = gT("URL for a respondent to opt-out of this survey");
            $replFields['OPTINURL'] = gT("URL for a respondent to opt-in to this survey");
            $replFields['FIRSTNAME'] = gT("First name of the participant");
            $replFields['LASTNAME'] = gT("Last name of the participant");
            $replFields['SURVEYNAME'] = gT("Survey title");
            $replFields['SID'] = gT("Survey ID");
            $replFields['SURVEYDESCRIPTION'] = gT("Survey description");
            $attributes = getTokenFieldsAndNames($surveyid, true);

            foreach ($attributes as $attributefield => $attributedescription) {
                $replFields[strtoupper($attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
            }

            $replFields['ADMINNAME'] = gT("Name of the survey administrator");
            $replFields['ADMINEMAIL'] = gT("Email address of the survey administrator");
            $replFields['SURVEYURL'] = gT("URL of the survey");
            $replFields['EXPIRY'] = gT("Survey expiration date");
            return array($replFields, false);

            // $replFields['SID']= gT("Survey ID");
        } elseif (strpos($fieldtype, 'email_registration') !== false) {
            $replFields['FIRSTNAME'] = gT("First name of the participant");
            $replFields['LASTNAME'] = gT("Last name of the participant");
            $replFields['SURVEYNAME'] = gT("Survey title");
            $replFields['SID'] = gT("Survey ID");
            $replFields['SURVEYDESCRIPTION'] = gT("Survey description");
            $attributes = getTokenFieldsAndNames($surveyid, true);

            foreach ($attributes as $attributefield => $attributedescription) {
                $replFields[strtoupper($attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
            }

            $replFields['ADMINNAME'] = gT("Name of the survey administrator");
            $replFields['ADMINEMAIL'] = gT("Email address of the survey administrator");
            $replFields['SURVEYURL'] = gT("URL of the survey");
            $replFields['EXPIRY'] = gT("Survey expiration date");
            return array($replFields, false);

        } elseif (strpos($fieldtype, 'email_confirmation') !== false) {
            $replFields['TOKEN'] = gT("Access code for this participant");
            $replFields['FIRSTNAME'] = gT("First name of the participant");
            $replFields['LASTNAME'] = gT("Last name of the participant");
            $replFields['EMAIL'] = gT("Email address of the participant");
            $replFields['SURVEYNAME'] = gT("Survey title");
            $replFields['SID'] = gT("Survey ID");
            $replFields['SURVEYDESCRIPTION'] = gT("Survey description");
            $attributes = getTokenFieldsAndNames($surveyid, true);

            foreach ($attributes as $attributefield => $attributedescription) {
                $replFields[strtoupper($attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
            }

            $replFields['ADMINNAME'] = gT("Name of the survey administrator");
            $replFields['ADMINEMAIL'] = gT("Email address of the survey administrator");
            $replFields['SURVEYURL'] = gT("URL of the survey");
            $replFields['EXPIRY'] = gT("Survey expiration date");

            // email-conf can accept insertans fields for non anonymous surveys
            if (!empty($oSurvey)) {
                if (!$oSurvey->isAnonymized) {
                    return array($replFields, true);
                }
            }
            return array($replFields, false);

        } elseif (strpos($fieldtype, 'group-desc') !== false
            || strpos($fieldtype, 'question-text') !== false
            || strpos($fieldtype, 'question-help') !== false
            || strpos($fieldtype, 'editgroup') !== false                // for translation
            || strpos($fieldtype, 'editgroup_desc') !== false           // for translation
            || strpos($fieldtype, 'editquestion') !== false             // for translation
            || strpos($fieldtype, 'editquestion_help') !== false        // for translation
        ) { 
            $replFields['TOKEN:FIRSTNAME'] = gT("First name of the participant");
            $replFields['TOKEN:LASTNAME'] = gT("Last name of the participant");
            $replFields['TOKEN:EMAIL'] = gT("Email address of the participant");
            $replFields['SID'] = gT("This question's survey ID number");
            $replFields['GID'] = gT("This question's group ID number");
            $replFields['QID'] = gT("This question's question ID number");
            $replFields['SGQ'] = gT("This question's SGQA code");
            $attributes = getTokenFieldsAndNames($surveyid, true);

            foreach ($attributes as $attributefield => $attributedescription) {
                $replFields['TOKEN:' . strtoupper($attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
            }

            $replFields['EXPIRY'] = gT("Survey expiration date");
            return array($replFields, true);

        } elseif (strpos($fieldtype, 'editanswer') !== false) {
            $replFields['TOKEN:FIRSTNAME'] = gT("First name of the participant");
            $replFields['TOKEN:LASTNAME'] = gT("Last name of the participant");
            $replFields['TOKEN:EMAIL'] = gT("Email address of the participant");
            $replFields['SID'] = gT("This question's survey ID number");
            $replFields['GID'] = gT("This question's group ID number");
            $replFields['QID'] = gT("This question's question ID number");
            $replFields['SGQ'] = gT("This question's SGQA code");
            $attributes = getTokenFieldsAndNames($surveyid, true);

            foreach ($attributes as $attributefield => $attributedescription) {
                $replFields['TOKEN:' . strtoupper($attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
            }

            $replFields['EXPIRY'] = gT("Survey expiration date");
            return array($replFields, true);

        } elseif (strpos($fieldtype, 'assessment-text') !== false) {
            $replFields['TOTAL'] = gT("Overall assessment score");
            $replFields['PERC'] = gT("Assessment group score");
            return array($replFields, false);
        } else {
            return [[], false];
        }
    }

    public function getNewTypeResponse($fieldtype, $surveyid = null, $gid = null, $qid = null)
    {
        $returnArray = [];
        $generalArray = $this->_getReplacementFields($fieldtype, $surveyid);

        foreach ($generalArray[0] as $key => $value) {
            $returnArray[gT('General')][$key] = [
                "type" => 'general',
                "value" => $value
            ];
        } 

        if ($qid != null || $gid != null || $generalArray[1]) {
            $returnArray[gT('Questions')] = $this->collectQuestionReplacements($surveyid, $gid, $qid);
        }


        $this->getController()->renderPartial('/admin/super/_renderJson', ['data' => $returnArray]);
        return;
    }

    private function collectQuestionReplacements($surveyid, $gid = null, $qid = null) {
        $oSurvey = Survey::model()->findByPk($surveyid);
        $oCurrentQuestion = Question::model()->findByPk($qid);
        $aResult = [];
        
        $oCriteria = new CDbCriteria();
        $oCriteria->compare('t.sid', $surveyid);
        $oCriteria->compare('parent_qid', 0);

        if ($gid != null && $qid == null) {
            $oGroup = QuestionGroup::model()->findByPk($gid);
            $oCriteria->with = ['group'];
            $oCriteria->compare('group_order', '<='.$oGroup->group_order);
        }

        if ($qid != null) {
            $oCriteria->with = ['group'];
            $oCriteria->compare('group_order', '<='.$oCurrentQuestion->group->group_order);
            if ($oCurrentQuestion->parent_qid != 0) {
                $oCriteria->compare('question_order', '<'.$oCurrentQuestion->parent->question_order);
            } else {
                $oCriteria->compare('question_order', '<'.$oCurrentQuestion->question_order );
            }
        }

        $aQuestions = Question::model()->findAll($oCriteria);
        
        uasort( 
            $aQuestions, 
            function ($a,$b) {
            if ($a->gid != $b->gid) {
                return $a->group->group_order < $b->group->group_order ? -1 : 1;
            }
            return $a->question_order < $b->question_order ? -1 : 1;
        });

        foreach ($aQuestions as $oQuestion) {
            if ($oCurrentQuestion != null && $oCurrentQuestion->qid == $oQuestion->qid) { 
                continue; 
            }

            if (safecount($oQuestion->subquestions) != 0) {
                $aSubquestions = $oQuestion->subquestions;
                
                uasort($aSubquestions, function ($a,$b) { return $a->question_order < $b->question_order ? -1 : 1; });

                foreach ($aSubquestions as $oSubQuestion) {
                    $aResult[$oQuestion->title.'_'.$oSubQuestion->title] = [
                        'type' => 'question',
                        'value' => ' -('.$oQuestion->title.')| '.$oSubQuestion->questionl10ns[$oSurvey->language]->question
                    ];
                }
            } else {
                $aResult[$oQuestion->title] = [
                    'type' => 'question',
                    'value' => $oQuestion->questionl10ns[$oSurvey->language]->question,
                ];
            }
        }
        return $aResult;
    }
}

