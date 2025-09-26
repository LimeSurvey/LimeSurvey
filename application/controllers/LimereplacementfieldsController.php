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
class LimeReplacementFieldsController extends LSBaseController
{
    /**
     *
     * action used to provide the html editor with data for the
     * placeholder fields modal
     * @return false|string|string[]|null
     * @throws CException
     * @throws CHttpException
     */
    public function actionIndex()
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

        if ($surveyid && !Permission::model()->hasSurveyPermission($surveyid, 'survey', 'read')) {
            throw new CHttpException(403);
        }

        if ($newType) {
            $newTypeResponse = $this->getNewTypeResponse($fieldtype, $surveyid, $gid, $qid);
            return $this->renderPartial('/admin/super/_renderJson', ['data' => $newTypeResponse]);
        }

        list($replacementFields, $isInsertAnswerEnabled) = $this->getReplacementFields($fieldtype, $surveyid);

        if ($isInsertAnswerEnabled === true) {
            //2: Get all other questions that occur before this question that are pre-determined answer types
            $fieldmap = createFieldMap($survey, 'full', false, false, $survey->language);

            $surveyformat = $survey->format; // S, G, A

            //Go through each question until we reach the current one
            //error_log(print_r($qrows,true));
            $questionlist = $this->getQuestionList($action, $gid, $qid, $fieldmap, $fieldtype, $surveyformat);
            $childQuestions = $this->getChildQuestions($questionlist);
        }

        $data = [];
        $data['countfields'] = count($replacementFields);
        asort($replacementFields);
        $data['replFields'] = $replacementFields;
        if (isset($childQuestions)) {
            $data['cquestions'] = $childQuestions;
        }
        if (isset($surveyformat)) {
            $data['surveyformat'] = $surveyformat;
        }

        $this->renderPartial('limeReplacementFields_view', $data);
    }

    /**
     * Returns array of relevant questions based on the given fieldmap
     *
     * @param mixed $action
     * @param integer $gid
     * @param integer $qid
     * @param array $fieldmap
     * @param mixed $questionType
     * @param string $surveyformat
     * @return array
     */
    private function getQuestionList($action, $gid, $qid, array $fieldmap, $questionType, $surveyformat)
    {
        $previousQuestion = null;
        $isPreviousPageQuestion = true;
        $questionList = array();

        foreach ($fieldmap as $question) {
            if (empty($question['qid'])) {
                continue;
            }

            if (is_null($qid) || $this->shouldAddQuestion($action, $gid, $qid, $question, $previousQuestion)) {
                $isPreviousPageQuestion = $this->addQuestionToList($action, $gid, $question, $questionType, $surveyformat, $isPreviousPageQuestion, $questionList);
                $previousQuestion = $question;
            } else {
                break;
            }
        }
        return $questionList;
    }

    /**
     * Returns true if the question should be added to the list
     * or false if it should not
     * depending on the passed parameters
     *
     * @param mixed $action
     * @param integer $gid
     * @param integer $qid
     * @param array $question
     * @param mixed $previousQuestion
     * @return bool|void
     */
    private function shouldAddQuestion($action, $gid, $qid, array $question, $previousQuestion)
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
     * Updates the question list with info on
     * previouspage (isPreviousPageQuestion).
     * Returns the value of $isPreviousPageQuestion
     *
     * @param mixed $action
     * @param integer $gid
     * @param array $field
     * @param mixed $questionType
     * @param string $surveyformat
     * @param bool $isPreviousPageQuestion
     * @param array $questionList
     * @return bool
     */
    private function addQuestionToList($action, $gid, array $field, $questionType, $surveyformat, $isPreviousPageQuestion, &$questionList)
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

    /**
     * Returns array with relevant question data especially
     * for limeReplacementFields view to populate the replacement field list
     * in the html editor
     *
     * @param array $questions
     * @return array
     */
    private function getChildQuestions(array $questions)
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
            $replacementCode = $this->getReplacementCodeByArray($row);

            $shortquestion = $row['title'] . ": " . flattenText($question);
            $cquestions[] = array(
                $shortquestion,
                $row['qid'],
                $row['type'],
                $row['fieldname'],
                $row['previouspage'],
                $row['title'],
                $replacementCode
            );
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
    private function getReplacementFields($fieldtype, $surveyid)
    {
        $oSurvey = Survey::model()->findByPk($surveyid);
        $replFields = array();

        if ($fieldtype === 'globalSurveySettings') {
            $replFields['TOKEN:FIRSTNAME'] = gT("First name of the participant");
            $replFields['TOKEN:LASTNAME'] = gT("Last name of the participant");
            $replFields['TOKEN:EMAIL'] = gT("Email address of the participant");
            $replFields['EXPIRY'] = gT("Survey expiration date");
            $replFields['ADMINNAME'] = gT("Name of the survey administrator");
            $replFields['ADMINEMAIL'] = gT("Email address of the survey administrator");
            return array($replFields, false);
        }
        if ($fieldtype === 'admincreationemailtemplate') {
            $replFields['SITENAME'] = gT("Name of the website");
            $replFields['ADMINNAME'] = gT("Name of the administrator");
            $replFields['ADMINEMAIL'] = gT("Email address of the administrator");
            $replFields['USERNAME'] = gT("Username of the new user");
            $replFields['FULLNAME'] = gT("Full name of the new user");
            $replFields['LOGINURL'] = gT("Link to create password");
            return array($replFields, false);
        }
        /* For other $fieldtype : we need $surveyId */
        if (!$surveyid) {
            return array($replFields, false);
        }

        if (
            strpos($fieldtype, 'survey-desc') !== false
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
                $replFields['TOKEN:' . strtoupper((string) $attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
            }

            $replFields['EXPIRY'] = gT("Survey expiration date");
            $replFields['ADMINNAME'] = gT("Name of the survey administrator");
            $replFields['ADMINEMAIL'] = gT("Email address of the survey administrator");
            return array($replFields, false);
        } elseif (
            strpos($fieldtype, 'email_admin_notification') !== false
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
                $replFields[strtoupper((string) $attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
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
                $replFields[strtoupper((string) $attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
            }

            $replFields['ADMINNAME'] = gT("Name of the survey administrator");
            $replFields['ADMINEMAIL'] = gT("Email address of the survey administrator");
            return array($replFields, false);
        } elseif (
            strpos($fieldtype, 'email-invitation') !== false
            || strpos($fieldtype, 'email-reminder') !== false
        ) {
            // these 2 fields are supported by email-inv and email-rem
            // but not email-reg for the moment
            $replFields['EMAIL'] = gT("Participant - Email address");
            $replFields['TOKEN'] = gT("Participant - Access code");
            $replFields['OPTOUTURL'] = gT("Participant - Opt-out URL");
            $replFields['GLOBALOPTOUTURL'] = gT("Participant - Central participant DB opt-out URL");
            $replFields['OPTINURL'] = gT("Participant - Opt-in URL");
            $replFields['GLOBALOPTINURL'] = gT("Participant - Central participant DB opt-in URL");
            $replFields['FIRSTNAME'] = gT("Participant - First name");
            $replFields['LASTNAME'] = gT("Participant - Last name");
            $replFields['VALIDFROM'] = gT("Participant - The date from which the token is valid");
            $replFields['VALIDUNTIL'] = gT("Participant - The date until which the token is valid");
            $replFields['SURVEYNAME'] = gT("Survey title");
            $replFields['SID'] = gT("Survey ID");
            $replFields['SURVEYDESCRIPTION'] = gT("Survey description");
            $attributes = getTokenFieldsAndNames($surveyid, true);

            foreach ($attributes as $attributefield => $attributedescription) {
                $replFields[strtoupper((string) $attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
            }

            $replFields['ADMINNAME'] = gT("Survey administrator - Name");
            $replFields['ADMINEMAIL'] = gT("Survey administrator - Email address");
            $replFields['SURVEYURL'] = gT("Survey URL");
            $replFields['SURVEYIDURL'] = gT("Survey URL based on survey ID");
            $replFields['EXPIRY'] = gT("Survey expiration date");
            return array($replFields, false);

            // $replFields['SID']= gT("Survey ID");
        } elseif (strpos($fieldtype, 'email_registration') !== false) {
            $replFields['FIRSTNAME'] = gT("Participant - First name");
            $replFields['LASTNAME'] = gT("Participant - Last name");
            $replFields['SURVEYNAME'] = gT("Survey title");
            $replFields['SID'] = gT("Survey ID");
            $replFields['SURVEYDESCRIPTION'] = gT("Survey description");
            $attributes = getTokenFieldsAndNames($surveyid, true);

            foreach ($attributes as $attributefield => $attributedescription) {
                $replFields[strtoupper((string) $attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
            }

            $replFields['ADMINNAME'] = gT("Survey administrator - Name");
            $replFields['ADMINEMAIL'] = gT("Survey administrator - Email address");
            $replFields['SURVEYURL'] = gT("Survey URL");
            $replFields['SURVEYIDURL'] = gT("Survey URL based on survey ID");
            $replFields['EXPIRY'] = gT("Survey expiration date");
            return array($replFields, false);
        } elseif (strpos($fieldtype, 'email_confirmation') !== false) {
            $replFields['TOKEN'] = gT("Participant - Access code");
            $replFields['FIRSTNAME'] = gT("Participant - First name");
            $replFields['LASTNAME'] = gT("Participant - Last name");
            $replFields['EMAIL'] = gT("Participant - Email address");
            $replFields['SURVEYNAME'] = gT("Survey title");
            $replFields['SID'] = gT("Survey ID");
            $replFields['SURVEYDESCRIPTION'] = gT("Survey description");
            $attributes = getTokenFieldsAndNames($surveyid, true);

            foreach ($attributes as $attributefield => $attributedescription) {
                $replFields[strtoupper((string) $attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
            }

            $replFields['ADMINNAME'] = gT("Survey administrator - Name");
            $replFields['ADMINEMAIL'] = gT("Survey administrator - Email address");
            $replFields['SURVEYURL'] = gT("Survey URL");
            $replFields['SURVEYIDURL'] = gT("Survey URL based on survey ID");
            $replFields['EXPIRY'] = gT("Survey expiration date");

            // email-conf can accept insertans fields for non anonymous surveys
            if (!empty($oSurvey)) {
                if (!$oSurvey->isAnonymized) {
                    return array($replFields, true);
                }
            }
            return array($replFields, false);
        } elseif (
            strpos($fieldtype, 'group-desc') !== false
            || strpos($fieldtype, 'question-text') !== false
            || strpos($fieldtype, 'question-help') !== false
            || strpos($fieldtype, 'editgroup') !== false                // for translation
            || strpos($fieldtype, 'editgroup_desc') !== false           // for translation
            || strpos($fieldtype, 'editquestion') !== false             // for translation
            || strpos($fieldtype, 'editquestion_help') !== false        // for translation
        ) {
            $replFields['TOKEN:FIRSTNAME'] = gT("Participant - First name");
            $replFields['TOKEN:LASTNAME'] = gT("Participant - Last name");
            $replFields['TOKEN:EMAIL'] = gT("Participant - Email address");
            $replFields['SID'] = gT("Survey ID");
            $replFields['GID'] = gT("Question group ID");
            $replFields['QID'] = gT("Question ID");
            $attributes = getTokenFieldsAndNames($surveyid, true);

            foreach ($attributes as $attributefield => $attributedescription) {
                $replFields['TOKEN:' . strtoupper((string) $attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
            }

            $replFields['EXPIRY'] = gT("Survey expiration date");
            return array($replFields, true);
        } elseif (strpos($fieldtype, 'editanswer') !== false) {
            $replFields['TOKEN:FIRSTNAME'] = gT("Participant - First name");
            $replFields['TOKEN:LASTNAME'] = gT("Participant - Last name");
            $replFields['TOKEN:EMAIL'] = gT("Participant - Email address");
            $replFields['SID'] = gT("Survey ID");
            $replFields['GID'] = gT("Question group ID");
            $replFields['QID'] = gT("Question ID");
            $attributes = getTokenFieldsAndNames($surveyid, true);

            foreach ($attributes as $attributefield => $attributedescription) {
                $replFields['TOKEN:' . strtoupper((string) $attributefield)] = sprintf(gT("Participant attribute: %s"), $attributedescription['description']);
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

    /**
     * Returns an multidimensional array
     * containing the replacement fields for the given fieldtype.
     * Probably never used.
     *
     * @param mixed $fieldtype
     * @param integer $surveyid
     * @param integer $gid
     * @param integer $qid
     * @return array
     */
    public function getNewTypeResponse($fieldtype, $surveyid = null, $gid = null, $qid = null)
    {
        $returnArray = [];
        $generalArray = $this->getReplacementFields($fieldtype, $surveyid);

        foreach ($generalArray[0] as $key => $value) {
            $returnArray[gT('General')][$key] = [
                "type" => 'general',
                "value" => $value
            ];
        }

        if ($qid != null || $gid != null || $generalArray[1]) {
            $returnArray[gT('Questions')] = $this->collectQuestionReplacements($surveyid, $gid, $qid);
        }

        return $returnArray;
    }

    /**
     * Should return previous questions as a multidimensional array.
     * [
     *   QUESTIONCODE_SUBQUESTIONCODE = [
     *     "type" => 'question',
     *     "value" => 'Question text'
     *   ],
     *   QUESTIONCODE = [
     *     "type" => 'question',
     *     "value" => 'Question text'
     *   ],
     * ]
     *
     * Most likely not used anymore.
     * The building of the criteria has a logical error when qid is passed.
     * if group id is passed but no question id:
     *   -> we get all (parent) questions of the group and of the groups before.
     * if question id is passed
     *   -> we get all questions of the group and of the groups before
     *      but only those with a sortorder below the ordernumber of the
     *      current question. (This is the error)
     *
     * @param $surveyid
     * @param integer $gid
     * @param integer $qid
     * @return array
     */
    private function collectQuestionReplacements(
        $surveyid,
        $gid = null,
        $qid = null
    ) {
        $oSurvey = Survey::model()->findByPk($surveyid);
        $oCurrentQuestion = Question::model()->findByPk($qid);
        $aResult = [];

        $oCriteria = new CDbCriteria();
        $oCriteria->compare('t.sid', $surveyid);
        $oCriteria->compare('parent_qid', 0);

        if ($gid != null && $qid == null) {
            $oGroup = QuestionGroup::model()->findByPk($gid);
            $oCriteria->with = ['group'];
            $oCriteria->compare('group_order', '<=' . $oGroup->group_order);
        }

        if ($qid != null) {
            $oCriteria->with = ['group'];
            $oCriteria->compare(
                'group_order',
                '<=' . $oCurrentQuestion->group->group_order
            );
            if ($oCurrentQuestion->parent_qid != 0) {
                $oCriteria->compare(
                    'question_order',
                    '<' . $oCurrentQuestion->parent->question_order
                );
            } else {
                $oCriteria->compare(
                    'question_order',
                    '<' . $oCurrentQuestion->question_order
                );
            }
        }

        $aQuestions = Question::model()->findAll($oCriteria);

        uasort(
            $aQuestions,
            function ($a, $b) {
                if ($a->gid != $b->gid) {
                    return $a->group->group_order < $b->group->group_order ? -1 : 1;
                }
                return $a->question_order < $b->question_order ? -1 : 1;
            }
        );

        foreach ($aQuestions as $oQuestion) {
            if ($oCurrentQuestion != null && $oCurrentQuestion->qid == $oQuestion->qid) {
                continue;
            }

            if (safecount($oQuestion->subquestions) != 0) {
                $aSubquestions = $oQuestion->subquestions;

                uasort($aSubquestions, function ($a, $b) {
                    return $a->question_order < $b->question_order ? -1 : 1;
                });

                foreach ($aSubquestions as $oSubQuestion) {
                    $aResult[$oQuestion->title . '_' . $oSubQuestion->title] = [
                        'type' => 'question',
                        'value' => ' -(' . $oQuestion->title . ')| ' . $oSubQuestion->questionl10ns[$oSurvey->language]->question
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

    /**
     * Analyzes the question parameters and returns the replacement code
     * for html editor "Placeholder fields"
     * simple questions: QUESTIONCODE.shown
     * subquestions:  QUESTIONCODE_SUBQCODE.shown
     * other option: QUESTIONCODE_other (.shown is not working in that case)
     * question types using scale_id: QUESTIONCODE_SUBQCODE_SCALEID.shown
     *
     * @param array $question
     * @return string
     */
    private function getReplacementCodeByArray(array $question)
    {
        $replacementCode = $question['title'];
        if (array_key_exists('aid', $question) && $question['aid'] !== '') {
            $replacementCode = $question['title'] . '_' . $question['aid'];
            if (array_key_exists('scale_id', $question)) {
                $replacementCode = $replacementCode . '_' . $question['scale_id'];
            }
        }
        if (strpos($replacementCode, '_other') === false) {
            $replacementCode = $replacementCode . '.shown';
        }
        return $replacementCode;
    }
}
