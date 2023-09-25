<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

use Question;
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
    private L10nService $l10nService;
    private Question $modelQuestion;

    public function __construct(
        L10nService $l10nService,
        Question $modelQuestion
    ) {
        $this->l10nService = $l10nService;
        $this->modelQuestion = $modelQuestion;
    }

    /**
     * Based on QuestionAdministrationController::actionSaveQuestionData()
     *
     * @param array{
     *  ...<array-key, mixed>
     * } $subquestions
     * @return void
     * @throws PersistErrorException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     */
    public function save(Question $question, $subquestions)
    {
        if ($question->questionType->subquestions > 0) {
            $this->storeSubquestions(
                $question,
                $subquestions ?? [],
                $question->survey->active != 'N'
            );
        }
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
        $questionOrder = 0;
        $subquestionIds = [];
        foreach ($subquestionsArray as $subquestionArray) {
            foreach ($subquestionArray as $scaleId => $data) {
                $subquestionId = $this->storeSubquestion(
                    $question,
                    $scaleId,
                    $data,
                    $questionOrder,
                    $surveyActive
                );
                $subquestionIds[] = $subquestionId;
            }
        }
        if (false == $surveyActive) {
            $question->deleteAllSubquestions($subquestionIds);
        }
    }

    /**
     * Save subquestion.
     * Used when survey is *not* activated.
     *
     * @param Question $question
     * @param int $scaleId
     * @param array $data
     * @param int &$questionOrder
     * @param boolean $surveyActive
     * @return int
     * @throws PersistErrorException
     * @throws BadRequestException
     */
    private function storeSubquestion(
        Question $question,
        $scaleId,
        $data,
        &$questionOrder,
        $surveyActive = false
    ) {
        $subquestion = null;
        if (!isset($data['code'])) {
            throw new BadRequestException('Internal error: Missing mandatory field "code" for question');
        }
        // If the subquestion with given code already exists, update it.
        $subquestion = $this->modelQuestion->findByAttributes([
            'sid' => $question->sid,
            'parent_qid' => $question->qid,
            'title' => $data['code'],
            'scale_id' => $scaleId
        ]);
        if (!$subquestion && isset($data['oldcode'])) {
            // If the subquestion with given code does not exist
            // - but subquestion with old code exists, update it.
            $subquestion = $this->modelQuestion->findByAttributes([
                'sid' => $question->sid,
                'parent_qid' => $question->qid,
                'title' => $data['oldcode'],
                'scale_id' => $scaleId
            ]);
        }
        if (!$subquestion) {
            if ($surveyActive) {
                throw new NotFoundException('Subquestion with code "' . $data['code'] . '" not found');
            } else {
                $subquestion = DI::getContainer()->make(Question::class);
            }
        }
        $subquestion->title = $data['code'];
        $subquestion->sid = $question->sid;
        $subquestion->gid = $question->gid;
        $subquestion->parent_qid = $question->qid;
        $subquestion->question_order = $questionOrder;
        $questionOrder++;
        if ($scaleId === 0) {
            $subquestion->relevance = $data['relevance'];
        }
        $subquestion->scale_id = $scaleId;
        if (!$subquestion->save()) {
            throw new PersistErrorException('Could not save subquestion');
        }
        $subquestion->refresh();
        $this->updateSubquestionL10nService(
            $subquestion,
            $data['subquestionl10n']
        );

        return $subquestion->qid;
    }

    /**
     * Save subquestion L10nService
     *
     * @param Question $question
     * @param string $language
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
}
