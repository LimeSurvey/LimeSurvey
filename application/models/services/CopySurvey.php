<?php

namespace LimeSurvey\Models\Services;

use App;
use Assessment;
use Condition;
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
     * @var Survey */
    private $sourceSurvey;

    /**
     * @param Survey $sourceSurvey
     * @param CopySurveyOptions $options
     * @param int|null $newSurveyId
     */
    public function __construct($sourceSurvey, $options, $newSurveyId=null)
    {
        $this->sourceSurvey = $sourceSurvey;
        $this->options = $options;
        $this->newSurveyId = $newSurveyId;
    }

    /**
     * Copy the survey and return the results.
     *
     * It first extracts the original survey data to xml and afterward imports the xml data
     * as a survey.
     * All the functions used here (surveyGetXMLData, XMLImportSurvey) are very old functions.
     *
     * @return CopySurveyResult  Returns results, success and error messages
     * @throws \Exception
     */
    public function copy()
    {
        $newSurveyTitle = $this->sourceSurvey->currentLanguageSettings->surveyls_title . '- Copy';
        $destinationSurvey = new Survey();
        $destinationSurvey->attributes = $this->sourceSurvey->attributes;
        if ($this->newSurveyId !== null) {
            $destinationSurvey->sid = $this->newSurveyId;
        }
        $destinationSurvey = $this->getValidSurveyId($destinationSurvey);
        if(!$destinationSurvey->save()) {
            throw new \Exception(gt("Failed to copy survey"));
        }

        $copySurveyResult = new CopySurveyResult();
        $copySurveyResult->setCopiedSurvey($destinationSurvey);

        $this->copySurveyLanguages($copySurveyResult, $destinationSurvey);
        $destinationSurvey->currentLanguageSettings->surveyls_title = $newSurveyTitle;
        $destinationSurvey->currentLanguageSettings->save();
        $mappingGroupIdsAndQuestionIds = $this->copyGroupsAndQuestions($copySurveyResult, $destinationSurvey);
        $this->copySurveyAssessments(
            $copySurveyResult,
            $destinationSurvey,
            $mappingGroupIdsAndQuestionIds['questionGroupIds']
        );

        if ($this->options->isQuotas()){
           $copySurveyQuotas = new CopySurveyQuotas($this->sourceSurvey, $destinationSurvey);
            $cntQuotas = $copySurveyQuotas->copyQuotas($mappingGroupIdsAndQuestionIds['questionIds']);
            $copySurveyResult->setCntQuotas($cntQuotas);
        }

        //if a question has conditions they are copied or not...
        if ($this->options->isConditions()) {
            //copy the conditions from table
            $cntConditions = $this->copyConditions(
                $mappingGroupIdsAndQuestionIds['questionIds'],
                $mappingGroupIdsAndQuestionIds['questionGroupIds'],
                $destinationSurvey->sid
            );
        } else {
            Question::model()->updateAll(array('relevance' => '1'), 'sid=' . $destinationSurvey->sid);
            QuestionGroup::model()->updateAll(array('grelevance' => '1'), 'sid=' . $destinationSurvey->sid);
        }

        if ($this->options->isResetResponseStartId()) {
            $oSurvey = Survey::model()->findByPk($destinationSurvey->sid);
            $oSurvey->autonumber_start = 0;
            $oSurvey->save();
        }

        if ($this->options->isPermissions()) {
            Permission::model()->copySurveyPermissions($this->sourceSurvey->sid, $destinationSurvey->sid);
        }

        if ($this->options->isResourcesAndLinks()) {
            $resourceCopier = new CopySurveyResources();
            [, $errorFilesInfo] = $resourceCopier->copyResources($this->sourceSurvey->sid, $destinationSurvey->sid);
            if (!empty($errorFilesInfo)) {
                $copySurveyResult->setWarnings(['message' => gT("Some resources could not be copied from the source survey")]);
            }
        }

        return $copySurveyResult;
    }

    /**
     * Copies survey languages
     *
     * @param CopySurveyResult $copySurveyResult
     * @param Survey $destinationSurvey
     * @return void
     */
    private function copySurveyLanguages($copySurveyResult, $destinationSurvey)
    {
        //copy survey languages
        $sourceLanguageSettings = SurveyLanguageSetting::model()->findAllByAttributes(
            ['surveyls_survey_id' => $this->sourceSurvey->sid]
        );
        $cntCopiedLanguageSettings =0;
        foreach ($sourceLanguageSettings as $sourceLanguageSetting) {
            $destinationLanguageSetting = new SurveyLanguageSetting();
            $destinationLanguageSetting->attributes = $sourceLanguageSetting->attributes;
            $destinationLanguageSetting->surveyls_survey_id = $destinationSurvey->sid;
            $destinationLanguageSetting->surveyls_language = $sourceLanguageSetting->surveyls_language;
            if ($destinationLanguageSetting->save()) {
                $cntCopiedLanguageSettings++;
            }
        }
        $copySurveyResult->setCntSurveyLanguages($cntCopiedLanguageSettings);
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
        //copy questionGroups
        $questionGroups = QuestionGroup::model()->findAllByAttributes(['sid' => $this->sourceSurvey->sid]);
        $mappingQuestionGroupIds = [];
        $cntCopiedQuestionGroups = 0;
        foreach ($questionGroups as $questionGroup) {
            $copyQuestionGroup = new CopyQuestionGroup($questionGroup, $destinationSurvey->sid);
            $destinationQuestionGroup = $copyQuestionGroup->copyQuestionGroup();
            $mappingQuestionGroupIds[$questionGroup->gid] = $destinationQuestionGroup->gid;
            $cntCopiedQuestionGroups++;
        }
        $copyResults->setCntQuestionGroups($cntCopiedQuestionGroups);
        $mapping['questionGroupIds'] = $mappingQuestionGroupIds;

        $questions = Question::model()->findAllByAttributes([
            'sid' => $this->sourceSurvey->sid,
            'parent_qid' => 0
        ]);
        $mappingQuestionIds = [];
        $cntCopiedQuestions = 0;
        foreach ($questions as $question) {
            $copyQuestionValues = new CopyQuestionValues();
            $copyQuestionValues->setQuestiontoCopy($question);
            $copyQuestionValues->setQuestionGroupId($mappingQuestionGroupIds[$question->gid]);
            $copyQuestionValues->setOSurvey($destinationSurvey);
            $copyQuestionValues->setQuestionCode($question->title);
            $copyQuestionValues->setQuestionPositionInGroup($question->question_order);
            //get all languages for the question (text and help)
            $copyQuestionTextValues = [];
            $questionLanguages = QuestionL10n::model()->findAllByAttributes(['qid' => $question->qid]);
            foreach ($questionLanguages as $questionL10n) {
                $questionText = $questionL10n->question ?? '';
                $questionHelp = $questionL10n->help ?? '';
                $copyQuestionTextValues[$questionL10n->language] = new \LimeSurvey\Datavalueobjects\CopyQuestionTextValues($questionText, $questionHelp);
            }
            $copyQuestionValues->setQuestionL10nData($copyQuestionTextValues);
            $copyQuestion = new CopyQuestion($copyQuestionValues);
            $optionsCopyQuestion['copySubquestions'] = true;
            $optionsCopyQuestion['copyAnswerOptions'] = $this->options->isAnswerOptions();
            $optionsCopyQuestion['copyDefaultAnswers'] = true;
            $optionsCopyQuestion['copySettings'] = true;
            if ($copyQuestion->copyQuestion($optionsCopyQuestion, $destinationSurvey->sid)) {
                $destinationQuestion = $copyQuestion->getNewCopiedQuestion();
                //change sid and gip for the new question
                $destinationQuestion->sid = $destinationSurvey->sid;
                $destinationQuestion->gid = $mappingQuestionGroupIds[$question->gid];
                $destinationQuestion->save();
                $mappingQuestionIds[$question->qid] = $destinationQuestion->qid;
                $cntCopiedQuestions++;
            }
        }
        $copyResults->setCntQuestions($cntCopiedQuestions);
        $mapping['questionIds'] = $mappingQuestionIds;

        return $mapping;
    }

    private function getValidSurveyId($destinationSurvey){
        $attempts = 0;
        /* Validate sid : > 1 and unique */
        while (!$destinationSurvey->validate(array('sid'))) {
            $attempts++;
            $destinationSurvey->sid = intval(randomChars(6, '123456789'));
            if ($attempts > CreateSurvey::ATTEMPTS_CREATE_SURVEY_ID) {
                throw new Exception("Unable to get a valid survey ID after 50 attempts");
            }
        }

        return $destinationSurvey;
    }

    /**
     * Copies the assessments of a survey
     *
     * @param CopySurveyResult $copySurveyResult
     * @param Survey $destinationSurvey
     * @param array $mappingGroupIds    the mapped ids of question groups
     * @return void
     */
    private function copySurveyAssessments($copySurveyResult, $destinationSurvey, $mappingGroupIds)
    {
        $cntCopiedAssessments = 0;
        //only get assessment for the base language, as id+language is primary key...
        $assessments = Assessment::model()->findAllByAttributes(['sid' => $this->sourceSurvey->sid,'language' => $this->sourceSurvey->language]);
        foreach ($assessments as $assessment) {
            $destinationAssessment = new Assessment();
            $destinationAssessment->attributes = $assessment->attributes;
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
                if ($assessmentLangEntry->language!= $this->sourceSurvey->language){
                    $langAssessment = new Assessment();
                    $langAssessment->attributes = $assessmentLangEntry->attributes;
                    $langAssessment->language = $assessmentLangEntry->language;
                    $langAssessment->minimum = $assessmentLangEntry->minimum;
                    $langAssessment->maximum = $assessmentLangEntry->maximum;
                    $langAssessment->sid = $destinationSurvey->sid;
                    $langAssessment->gid = $mappingGroupIds[$assessment->gid];
                    $langAssessment->id = $destinationAssessment->id;
                    //var_dump($langAssessment->language . ' id: '. $langAssessment->id);
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
    private function copyConditions($mappingQuestionIds, $mappingGroupIds, $destinationSurveyId){
        //find all conditions for the source survey
        //the surveyId is in the attribute "cfieldname"...ahhhhhhhhh a nightmare...
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
            $sidGidQid = explode('X', $conditionRow['cfieldname']); //[0]sid, [1]gid, [2]qid
            $condition->cfieldname = $destinationSurveyId . "X". $mappingGroupIds[$sidGidQid[1]]. "X". $mappingQuestionIds[$conditionRow['cqid']];
            $condition->value = $conditionRow['value'];
            $condition->method = $conditionRow['method'];
            if ($condition->save()) {
                $cntConditions++;
            }
        }

        return $cntConditions;
    }

}
