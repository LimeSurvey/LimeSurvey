<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

use LimeSurvey\Models\Services\SurveyAggregateService\LanguageConsistency;
use Permission;
use Question;
use Survey;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException,
    PermissionDeniedException,
    BadRequestException
};

/**
 * Question Aggregate Service
 *
 * Service class for editing question data.
 *
 * Dependencies are injected to enable mocking.
 */
class SubQuestionsService
{
    use ValidateTrait;

    private L10nService $l10nService;
    private Question $modelQuestion;
    private Survey $modelSurvey;
    private Permission $modelPermission;
    private LanguageConsistency $languageConsistency;

    public function __construct(
        L10nService $l10nService,
        Question $modelQuestion,
        Survey $modelSurvey,
        Permission $modelPermission,
        LanguageConsistency $languageConsistency
    ) {
        $this->l10nService = $l10nService;
        $this->modelQuestion = $modelQuestion;
        $this->modelSurvey = $modelSurvey;
        $this->modelPermission = $modelPermission;
        $this->languageConsistency = $languageConsistency;
    }

    /**
     * Based on QuestionAdministrationController::actionSaveQuestionData()
     *
     * @param array {
     *  ...<array-key, mixed>
     * } $subquestions
     * @param bool $fromNewEditor
     * @return void
     * @throws PersistErrorException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     */
    public function save(Question $question, $subquestions, $fromNewEditor = false)
    {
        if ($question->questionType->subquestions > 0) {
            $this->storeSubquestions(
                $question,
                $subquestions ?? [],
                $question->survey->active != 'N'
            );
            if ($fromNewEditor) {
                $survey = $this->modelSurvey->findByPk($question->sid);
                $this->languageConsistency->updateQuestionsOnly(
                    $survey
                );
            }
        }
    }

    /**
     * Deletes a subquestion.
     *
     * @param int $surveyId
     * @param int $subquestionId
     * @throws PermissionDeniedException
     * @throws NotFoundException
     * @throws \CDbException
     */
    public function delete($surveyId, $subquestionId)
    {
        $survey = $this->modelSurvey->findByPk($surveyId);
        if (
            $survey->isActive ||
            !$this->modelPermission->hasSurveyPermission(
                $surveyId,
                'surveycontent',
                'delete'
            )
        ) {
            throw new PermissionDeniedException(
                'Access denied'
            );
        }
        $this->deleteSubquestion($surveyId, $subquestionId);
    }

    /**
     * Save subquestions.
     * Used when survey is *not* activated.
     *
     * @param Question $question
     * @param array $subquestionsArray
     * @param boolean $surveyActive
     * @return void
     * @throws PersistErrorException
     * @throws BadRequestException
     */
    private function storeSubquestions(Question $question, $subquestionsArray, $surveyActive = false)
    {
        $this->validateCodes($subquestionsArray);
        $questionOrder = 0;
        $subquestions = [];
        foreach ($subquestionsArray as $subquestionId => $subquestionArray) {
            foreach ($subquestionArray as $scaleId => $data) {
                $subquestion = $this->storeSubquestion(
                    $question,
                    $subquestionId,
                    $scaleId,
                    $data,
                    $questionOrder,
                    $surveyActive
                );
                $subquestions[] = $subquestion;
            }
        }
        if (false == $surveyActive) {
            $subquestionIds = array_map(function ($subquestion) {
                return $subquestion->qid;
            }, $subquestions);
            $question->deleteAllSubquestions($subquestionIds);
        }
    }

    /**
     * Save subquestion.
     * Used when survey is *not* activated.
     *
     * @param Question $question
     * @param int $subquestionId
     * @param int $scaleId
     * @param array $data
     * @param int &$questionOrder
     * @param boolean $surveyActive
     * @return Question
     * @throws PersistErrorException
     * @throws BadRequestException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function storeSubquestion(
        Question $question,
        $subquestionId,
        $scaleId,
        $data,
        &$questionOrder,
        $surveyActive = false
    ) {
        if (!isset($data['code'])) {
            throw new BadRequestException('Internal error: Missing mandatory field "code" for question');
        }
        $subquestionExists = false;
        // New subquestions have a temporary non-numeric id assigned by the frontend (example: new12345).
        // So, if the subquestion id has letter, is not numeric as the id given by DB, we don't check if it exists.
        if (is_numeric($subquestionId)) {
            // If there is no subquestion with given code, but subquestion with old code do exists, update it.
            $subquestion = $this->modelQuestion->findByAttributes([
                'qid' => $subquestionId,
                'scale_id' => $scaleId,
                'sid' => $question->sid,
                'parent_qid' => $question->qid
            ]);
            $subquestionExists = isset($subquestion);
        }
        if (!$subquestionExists) {
            if ($surveyActive) {
                throw new NotFoundException('Subquestion with id "' . $subquestionId . '" not found');
            } else {
                $subquestion = DI::getContainer()->make(Question::class);
            }
        }
        if (empty($subquestion)) {
            // This should only happen if it's a new subquestion and the DI container fails to create an instance.
            throw new NotFoundException('Subquestion with id "' . $subquestionId . '" failed to process');
        }
        $subquestion->title = $data['code'];
        $subquestion->sid = $question->sid;
        $subquestion->gid = $question->gid;
        $subquestion->parent_qid = $question->qid;
        $subquestion->question_order = $questionOrder;
        $questionOrder++;
        if ($scaleId === 0) {
            $subquestion->relevance = array_key_exists(
                'relevance',
                $data
            ) ? $data['relevance'] : null;
        }
        $subquestion->scale_id = $scaleId;
        $subquestion->setScenario('saveall');
        if (!$subquestion->save()) {
            throw new PersistErrorException('Could not save subquestion');
        }
        $subquestion->refresh();
        $this->updateSubquestionL10nService(
            $subquestion,
            $data['subquestionl10n'] ?? []
        );

        return $subquestion;
    }

    /**
     * Save subquestion L10nService
     *
     * @param Question $question
     * @param array $data
     * @return void
     * @throws PersistErrorException
     * @throws BadRequestException
     */
    private function updateSubquestionL10nService(Question $subquestion, $data)
    {
        foreach ($data as $language => $questionText) {
            $this->l10nService->save(
                $subquestion->qid,
                array(
                    [
                        'qid' => $subquestion->qid,
                        'language' => $language,
                        'question' => $questionText
                    ]
                )
            );
        }
    }

    /**
     * Deletes a subquestion.
     *
     * @param int $surveyId
     * @param int $subQuestionId
     * @throws NotFoundException|\CDbException
     */
    private function deleteSubquestion($surveyId, $subQuestionId)
    {
        $criteria = new \CDbCriteria();
        $criteria->compare('qid', $subQuestionId);
        $criteria->compare('sid', $surveyId);
        $criteria->addNotInCondition('parent_qid', [0]);

        $subQuestion = $this->modelQuestion->find($criteria);
        if (empty($subQuestion)) {
            throw new NotFoundException();
        }
        $subquestionL10ns = \QuestionL10n::model()->findAllByAttributes(
            ['qid' => $subQuestionId]
        );
        foreach ($subquestionL10ns as $subquestionL10n) {
            $subquestionL10n->delete();
        }
        $subQuestion->delete();
    }
}
