<?php

namespace LimeSurvey\Models\Services;

use App;
use Assessment;
use Condition;
use DefaultValue;
use LimeSurvey\Datavalueobjects\CopyQuestionValues;
use LSHttpRequest;
use Question;
use QuestionAttribute;
use QuestionGroup;
use QuestionL10n;
use Survey;
use Permission;
use SurveyLanguageSetting;
use Yii;

/**
 * This class is responsible for copying a survey.
 *
 * Class CopySurvey
 * @package LimeSurvey\Models\Services
 */
class CopySurvey
{
    /** @var ?int */
    private $newSurveyId;

    /** @var CopySurveyOptions */
    private $options;

    /**
     * @var Survey
     */
    private $sourceSurvey;

    /**
     * @param Survey $sourceSurvey
     * @param CopySurveyOptions $options
     * @param int|null $newSurveyId
     */
    public function __construct($sourceSurvey, $options, $newSurveyId = null)
    {
        $this->sourceSurvey = $sourceSurvey;
        $this->options = $options;
        $this->newSurveyId = $newSurveyId;
    }

    /**
     * Copy the survey and return the results.
     *
     * @return CopySurveyResult  Returns results, success and error messages
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function copy()
    {
        $destinationSurvey = new Survey();
        $destinationSurvey->attributes = $this->sourceSurvey->attributes;
        if ($this->newSurveyId !== null) {
            $destinationSurvey->sid = $this->newSurveyId;
        }
        $copySurveyResult = new CopySurveyResult();
        if (Survey::model()->findByPk($destinationSurvey->sid) !== null) {
            $copySurveyResult->setWarnings(gt("The desired survey ID was already in use, therefore a random one was assigned."));
        }
        $destinationSurvey = $this->getValidSurveyId($destinationSurvey);
        $destinationSurvey->active = 'N'; //don't activate the survey !!!
        $destinationSurvey->owner_id = Yii::app()->session['loginID'];
        $destinationSurvey->datecreated = date("Y-m-d H:i:s");
        if (!$destinationSurvey->save()) {
            throw new \Exception(gt("Failed to copy survey"));
        }

        $copySurveyResult->setCopiedSurvey($destinationSurvey);

        $this->copySurveyLanguages($copySurveyResult, $destinationSurvey);
        $destinationSurvey->currentLanguageSettings->surveyls_title = $this->sourceSurvey->currentLanguageSettings->surveyls_title . '- Copy';
        $destinationSurvey->currentLanguageSettings->save();
        $mappingGroupIdsAndQuestionIds = $this->copyGroupsAndQuestions($copySurveyResult, $destinationSurvey);
        $this->copySurveyAssessments($copySurveyResult, $destinationSurvey, $mappingGroupIdsAndQuestionIds['questionGroupIds']);

        if ($this->options->isQuotas()) {
            $copySurveyQuotas = new CopySurveyQuotas($this->sourceSurvey, $destinationSurvey);
            $copySurveyResult->setCntQuotas($copySurveyQuotas->copyQuotas(
                $mappingGroupIdsAndQuestionIds['questionIds'],
                $this->options->isResourcesAndLinks()
            ));
        }

        if ($this->options->isConditions()) {
            $hasQuestionMap = !empty($mappingGroupIdsAndQuestionIds['questionIds']) && is_array($mappingGroupIdsAndQuestionIds['questionIds']);
            $hasGroupMap = !empty($mappingGroupIdsAndQuestionIds['questionGroupIds']) && is_array($mappingGroupIdsAndQuestionIds['questionGroupIds']);
            if ($hasQuestionMap && $hasGroupMap) {
                $this->copyConditions(
                    $mappingGroupIdsAndQuestionIds['questionIds'],
                    $mappingGroupIdsAndQuestionIds['questionGroupIds'],
                    $destinationSurvey->sid
                );
            } else {
                $copySurveyResult->setWarnings(gT("Conditions were not copied because question/group mappings are missing."));
                Question::model()->updateAll(['relevance' => '1'], 'sid=' . (int)$destinationSurvey->sid);
                QuestionGroup::model()->updateAll(['grelevance' => '1'], 'sid=' . (int)$destinationSurvey->sid);
            }
        } else {
            Question::model()->updateAll(['relevance' => '1'], 'sid=' . (int)$destinationSurvey->sid);
            QuestionGroup::model()->updateAll(['grelevance' => '1'], 'sid=' . (int)$destinationSurvey->sid);
        }

        if ($this->options->isResetStartAndEndDate()) {
            //reset start and end dates
            $destinationSurvey->startdate = null;
            $destinationSurvey->expires = null;
            $destinationSurvey->save();
        }

        if ($this->options->isResetResponseStartId()) {
            $destinationSurvey->autonumber_start = 0;
            $destinationSurvey->save();
        }

        if ($this->options->isPermissions()) {
            Permission::model()->copySurveyPermissions($this->sourceSurvey->sid, $destinationSurvey->sid);
        }

        if ($this->options->isResourcesAndLinks()) {
            $resourceCopier = new CopySurveyResources();
            [, $errorFilesInfo] = $resourceCopier->copyResources($this->sourceSurvey->sid, $destinationSurvey->sid);
            if (!empty($errorFilesInfo)) {
                $copySurveyResult->setWarnings(gT("Some resources could not be copied from the source survey"));
            }
        }
        return $copySurveyResult;
    }

    /**
     * Copies survey languages
     *
     * @param CopySurveyResult $copySurveyResult
     * @param Survey $destinationSurvey
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    private function copySurveyLanguages($copySurveyResult, $destinationSurvey)
    {
        //copy survey languages
        $sourceLanguageSettings = SurveyLanguageSetting::model()->findAllByAttributes(
            ['surveyls_survey_id' => $this->sourceSurvey->sid]
        );
        $cntCopiedLanguageSettings = 0;
        foreach ($sourceLanguageSettings as $sourceLanguageSetting) {
            $destLangSet = new SurveyLanguageSetting();
            $destLangSet->attributes = $sourceLanguageSetting->attributes;
            if ($this->options->isResourcesAndLinks()) {
                $destLangSet->surveyls_description = translateLinks(
                    'survey',
                    $this->sourceSurvey->sid,
                    $destinationSurvey->sid,
                    $destLangSet->surveyls_description
                );
                $destLangSet->surveyls_welcometext = translateLinks(
                    'survey',
                    $this->sourceSurvey->sid,
                    $destinationSurvey->sid,
                    $destLangSet->surveyls_welcometext
                );
                $destLangSet->surveyls_endtext = translateLinks(
                    'survey',
                    $this->sourceSurvey->sid,
                    $destinationSurvey->sid,
                    $destLangSet->surveyls_endtext
                );
                $destLangSet->surveyls_policy_notice = translateLinks(
                    'survey',
                    $this->sourceSurvey->sid,
                    $destinationSurvey->sid,
                    $destLangSet->surveyls_policy_notice
                );
                $destLangSet->surveyls_policy_error = translateLinks(
                    'survey',
                    $this->sourceSurvey->sid,
                    $destinationSurvey->sid,
                    $destLangSet->surveyls_policy_error
                );
                $destLangSet->surveyls_email_invite = translateLinks(
                    'survey',
                    $this->sourceSurvey->sid,
                    $destinationSurvey->sid,
                    $destLangSet->surveyls_email_invite
                );
                $destLangSet->surveyls_email_remind = translateLinks(
                    'survey',
                    $this->sourceSurvey->sid,
                    $destinationSurvey->sid,
                    $destLangSet->surveyls_email_remind
                );
                $destLangSet->surveyls_email_register = translateLinks(
                    'survey',
                    $this->sourceSurvey->sid,
                    $destinationSurvey->sid,
                    $destLangSet->surveyls_email_register
                );
                $destLangSet->surveyls_email_confirm = translateLinks(
                    'survey',
                    $this->sourceSurvey->sid,
                    $destinationSurvey->sid,
                    $destLangSet->surveyls_email_confirm
                );
                $destLangSet->email_admin_notification = translateLinks(
                    'survey',
                    $this->sourceSurvey->sid,
                    $destinationSurvey->sid,
                    $destLangSet->email_admin_notification
                );
                $destLangSet->email_admin_responses = translateLinks(
                    'survey',
                    $this->sourceSurvey->sid,
                    $destinationSurvey->sid,
                    $destLangSet->email_admin_responses
                );
                $destLangSet->attachments = translateLinks(
                    'survey',
                    $this->sourceSurvey->sid,
                    $destinationSurvey->sid,
                    $destLangSet->attachments,
                    true
                );
            }
            $destLangSet->surveyls_survey_id = $destinationSurvey->sid;
            $destLangSet->surveyls_language = $sourceLanguageSetting->surveyls_language;
            if ($destLangSet->save()) {
                $cntCopiedLanguageSettings++;
            }
        }
        $copySurveyResult->setCntSurveyLanguages($cntCopiedLanguageSettings);
    }

    /**
     * Copy all question groups of the survey to the destination survey.
     *
     * @param CopySurveyResult $copyResults
     * @param Survey $destinationSurvey
     * @return array
     * @throws \Exception
     */
    private function copyQuestionGroup($copyResults, $destinationSurvey)
    {
        $questionGroups = QuestionGroup::model()->findAllByAttributes(['sid' => $this->sourceSurvey->sid]);
        $mappingQuestionGroupIds = [];
        $cntCopiedQuestionGroups = 0;
        foreach ($questionGroups as $questionGroup) {
            $copyQuestionGroup = new CopyQuestionGroup($questionGroup, $destinationSurvey->sid);
            $destinationQuestionGroup = $copyQuestionGroup->copyQuestionGroup($this->options->isResourcesAndLinks());
            $mappingQuestionGroupIds[$questionGroup->gid] = $destinationQuestionGroup->gid;
            $cntCopiedQuestionGroups++;
        }
        $copyResults->setCntQuestionGroups($cntCopiedQuestionGroups);

        return $mappingQuestionGroupIds;
    }

    /**
     * Copies the question groups and the questions from the source survey to the destination survey.
     * A mapping of groupIds and questionIds is returned.
     *
     * @param Survey $destinationSurvey
     * @param CopySurveyResult $copyResults
     * @return array mapping of groupIds and questionIds
     * @throws \Exception
     */
    private function copyGroupsAndQuestions($copyResults, $destinationSurvey)
    {
        $mapping = [];
        $mapping['questionGroupIds'] = $this->copyQuestionGroup($copyResults, $destinationSurvey);

        $questions = Question::model()->findAllByAttributes([
            'sid' => $this->sourceSurvey->sid,
            'parent_qid' => 0
        ]);
        $mappingQuestionIds = [];
        $mappedSubquestionIds = [];
        $cntCopiedQuestions = 0;
        foreach ($questions as $question) {
            $copyQuestionValues = new CopyQuestionValues();
            $copyQuestionValues->setSourceSurveyId($this->sourceSurvey->sid);
            $copyQuestionValues->setQuestiontoCopy($question);
            $copyQuestionValues->setQuestionGroupId($mapping['questionGroupIds'][$question->gid]);
            $copyQuestionValues->setOSurvey($destinationSurvey);
            $copyQuestionValues->setQuestionCode($question->title);
            $copyQuestionValues->setQuestionPositionInGroup($question->question_order);
            $copyQuestionTextValues = [];
            $questionLanguages = QuestionL10n::model()->findAllByAttributes(['qid' => $question->qid]);
            foreach ($questionLanguages as $questionL10n) {
                $questionText = $questionL10n->question ?? '';
                $questionHelp = $questionL10n->help ?? '';
                $copyQuestionTextValues[$questionL10n->language] = new \LimeSurvey\Datavalueobjects\CopyQuestionTextValues($questionText, $questionHelp);
            }
            $copyQuestionValues->setQuestionL10nData($copyQuestionTextValues);
            $optionsCopyQuestion['copySubquestions'] = true;
            $optionsCopyQuestion['copyAnswerOptions'] = $this->options->isAnswerOptions();
            $optionsCopyQuestion['copyDefaultAnswers'] = false; //we have to do it separately here (id-mapping)
            $optionsCopyQuestion['copySettings'] = true;
            $optionsCopyQuestion['adjustLinks'] = $this->options->isResourcesAndLinks();
            $copyQuestion = new CopyQuestion($copyQuestionValues, $optionsCopyQuestion);
            if ($copyQuestion->copyQuestion($destinationSurvey->sid)) {
                $destinationQuestion = $copyQuestion->getNewCopiedQuestion();
                //change sid and gip for the new question
                $destinationQuestion->sid = $destinationSurvey->sid;
                $destinationQuestion->gid = $mapping['questionGroupIds'][$question->gid];
                $destinationQuestion->save();
                $mappingQuestionIds[$question->qid] = $destinationQuestion->qid;
                $cntCopiedQuestions++;
                if (!empty($copyQuestion->getMappedSubquestionIds()) && is_array($copyQuestion->getMappedSubquestionIds())) {
                    $mappedSubquestionIds += $copyQuestion->getMappedSubquestionIds();
                }
            }
        }
        $copyResults->setCntQuestions($cntCopiedQuestions);
        $mapping['questionIds'] = $mappingQuestionIds;
        $this->copyDefaultAnswers($mappingQuestionIds, $mappedSubquestionIds);

        return $mapping;
    }

    /**
     * Searches for a valid survey id for the destination survey and retries if necessary.
     *
     * @param Survey $destinationSurvey
     *
     * @return Survey
     * @throws \Exception
     */
    private function getValidSurveyId(Survey $destinationSurvey)
    {
        $attempts = 0;
        /* Validate sid : > 1 and unique */
        while (!$destinationSurvey->validate(['sid'])) {
            $attempts++;
            $destinationSurvey->sid = intval(randomChars(6, '123456789'));
            if ($attempts > CreateSurvey::ATTEMPTS_CREATE_SURVEY_ID) {
                throw new \Exception("Unable to get a valid survey ID after 50 attempts");
            }
        }

        return $destinationSurvey;
    }

    /**
     * Copies the assessments of a survey
     *
     * @param CopySurveyResult $copySurveyResult
     * @param Survey $destinationSurvey
     * @param array $mappingGroupIds the mapped ids of question groups
     * @return void
     */
    private function copySurveyAssessments($copySurveyResult, $destinationSurvey, $mappingGroupIds)
    {
        $cntCopiedAssessments = 0;
        //first get all assessments for the survey  for the base language, as id+language is primary key...
        $assessments = Assessment::model()->findAllByAttributes(['sid' => $this->sourceSurvey->sid, 'language' => $this->sourceSurvey->language]);
        foreach ($assessments as $assessment) {
            $destinationAssessment = new Assessment();
            $destinationAssessment->attributes = $assessment->attributes;
            if ($this->options->isResourcesAndLinks()) {
                $destinationAssessment->message = translateLinks(
                    'survey',
                    $this->sourceSurvey->sid,
                    $destinationSurvey->sid,
                    $destinationAssessment->message
                );
            }
            $destinationAssessment->minimum = $assessment->minimum;
            $destinationAssessment->maximum = $assessment->maximum;
            $destinationAssessment->sid = $destinationSurvey->sid;
            $destinationAssessment->gid = $mappingGroupIds[$assessment->gid];
            if ($destinationAssessment->save()) {
                $cntCopiedAssessments++;
            }
            //now copy for other languages
            $assessmentLangEntries = Assessment::model()->findAllByAttributes(['id' => $assessment->id]);
            foreach ($assessmentLangEntries as $assessmentLangEntry) {
                if ($assessmentLangEntry->language != $this->sourceSurvey->language) {
                    $langAssessment = new Assessment();
                    $langAssessment->attributes = $assessmentLangEntry->attributes;
                    $langAssessment->language = $assessmentLangEntry->language;
                    if ($this->options->isResourcesAndLinks()) {
                        $langAssessment->message = translateLinks(
                            'survey',
                            $this->sourceSurvey->sid,
                            $destinationSurvey->sid,
                            $langAssessment->message
                        );
                    }
                    $langAssessment->minimum = $assessmentLangEntry->minimum;
                    $langAssessment->maximum = $assessmentLangEntry->maximum;
                    $langAssessment->sid = $destinationSurvey->sid;
                    $langAssessment->gid = $mappingGroupIds[$assessment->gid];
                    $langAssessment->id = $destinationAssessment->id;
                    $langAssessment->save();
                }
            }
        }
        $copySurveyResult->setCntAssessments($cntCopiedAssessments);
    }

    /**
     * Copies the conditions of a survey.
     *
     * @param array $mappingQuestionIds
     * @param array $mappingGroupIds
     * @param int $destinationSurveyId
     * @return int number of conditions copied
     */
    private function copyConditions($mappingQuestionIds, $mappingGroupIds, $destinationSurveyId)
    {
        // find all conditions for the source survey
        // the surveyId is in the attribute "cfieldname"
        $conditionRows = Yii::app()->db->createCommand()
            ->select('conditions.*')
            ->from('{{conditions}} conditions')
            ->join('{{questions}} questions', 'questions.qid=conditions.qid')
            ->where('questions.sid=:sid and questions.parent_qid=:parent_qid', [
                ':sid' => $this->sourceSurvey->sid,
                ':parent_qid' => 0
            ])
            ->queryAll();

        $cntConditions = 0;
        foreach ($conditionRows as $conditionRow) {
            $condition = new Condition();
            $condition->attributes = $conditionRow;
            $condition->qid = $mappingQuestionIds[$conditionRow['qid']];
            $condition->cqid = $mappingQuestionIds[$conditionRow['cqid']];
            //rebuild the cfieldname --> "$iSurveyID . "X" . $iGroupID . "X" . $iQuestionID"
            list(, $oldGroupId, $oldQuestionId) = explode("X", (string) $conditionRow['cfieldname'], 3);
            //the $oldQuestionId contains the question id from the old question id
            //and could in addition contain a subquestion code or answer option code
            //cut out the question id, which is at the beginning of $oldQuestionId
            $appendSubQuestionOrAnswerOption = substr($oldQuestionId, strlen((string) $conditionRow['qid']));
            $addPlusSign = "";
            if (preg_match("/^\+/", $conditionRow['cfieldname'])) {
                $addPlusSign = "+";
            }
            $condition->cfieldname = $addPlusSign . $destinationSurveyId . "X" . $mappingGroupIds[$oldGroupId] .
                "X" . $mappingQuestionIds[$conditionRow['cqid']] . $appendSubQuestionOrAnswerOption;
            $condition->value = $conditionRow['value'];
            $condition->method = $conditionRow['method'];
            if ($condition->save()) {
                $cntConditions++;
            }
        }

        return $cntConditions;
    }

    /**
     * Copy default answers from table "defaultvalues" for the questions.
     *
     * @param array $mappingQuestionIds mapping of question ids
     * @param array $mappedSuquestionIds mapping of subquestion ids (if any)
     * @return int number of default answers copied
     */
    private function copyDefaultAnswers($mappingQuestionIds, $mappedSuquestionIds)
    {
        //get all entries from defaultvalues table where the qid belongs to the source survey
        $defaultAnswerRows = Yii::app()->db->createCommand()
            ->select('defaultvalues.*')
            ->from('{{defaultvalues}} defaultvalues')
            ->join('{{questions}} questions', 'questions.qid=defaultvalues.qid')
            ->where('questions.sid=:sid and questions.parent_qid=:parent_qid', [
                ':sid' => $this->sourceSurvey->sid,
                ':parent_qid' => 0
            ])
            ->queryAll();
        $cntDefaultAnswers = 0;
        //now copy the default answers and map them to the corresponding question ids
        foreach ($defaultAnswerRows as $defaultAnswerRow) {
            $defaultAnswer = new Defaultvalue();
            $defaultAnswer->dvid = null;
            $defaultAnswer->qid = $mappingQuestionIds[$defaultAnswerRow['qid']];
            //find the correct subquestion id
            if ($defaultAnswerRow['sqid'] === 0) {
                $defaultAnswer->sqid = 0; //this is the case, when an answer option is the default...
            } else {
                $defaultAnswer->sqid = $mappedSuquestionIds[$defaultAnswerRow['sqid']];
            }
            $defaultAnswer->scale_id = $defaultAnswerRow['scale_id'];
            $defaultAnswer->specialtype = $defaultAnswerRow['specialtype'];
            if ($defaultAnswer->save()) {
                $cntDefaultAnswers++;
            }
        }

        return $cntDefaultAnswers;
    }
}
