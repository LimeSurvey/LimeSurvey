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
        if ($question->survey->active == 'N') {
            // Clean subQuestions before save.
            $question->deleteAllSubquestions();
            // If question type has subQuestions, save them.
            if ($question->questionType->subquestions > 0) {
                $this->storeSubquestions(
                    $question,
                    $subquestions ?? []
                );
            }
        } else {
            if ($question->questionType->subquestions > 0) {
                $this->updateSubquestions(
                    $question,
                    $subquestions ?? []
                );
            }
        }
    }


    /**
     * Save subquestion.
     * Used when survey is *not* activated.
     *
     * @param Question $question
     * @param array $subquestionsArray
     * @return void
     * @throws PersistErrorException
     * @throws BadRequestException
     */
    private function storeSubquestions(Question $question, $subquestionsArray)
    {
        $questionOrder = 0;
        foreach ($subquestionsArray as $subquestionArray) {
            foreach ($subquestionArray as $scaleId => $data) {
                $subquestion = DI::getContainer()
                    ->make(Question::class);
                $subquestion->sid = $question->sid;
                $subquestion->gid = $question->gid;
                $subquestion->parent_qid = $question->qid;
                $subquestion->question_order = $questionOrder;
                $questionOrder++;
                if (!isset($data['code'])) {
                    throw new BadRequestException(
                        'Internal error: ' .
                        'Missing mandatory field "code" for question'
                    );
                }
                $subquestion->title = $data['code'];
                if ($scaleId === 0) {
                    $subquestion->relevance = $data['relevance'];
                }
                $subquestion->scale_id = $scaleId;
                if (!$subquestion->save()) {
                    throw new PersistErrorException(
                        'Could not save subquestion'
                    );
                }
                $subquestion->refresh();
                $this->updateSubquestionL10nService(
                    $subquestion,
                    $data['subquestionl10n']
                );
            }
        }
    }

    /**
     * Save subquestion.
     * Used when survey *is* activated.
     *
     * @param Question $question
     * @param array $subquestionsArray
     * @return void
     * @throws PersistErrorException
     * @throws BadRequestException
     */
    private function updateSubquestions(Question $question, $subquestionsArray)
    {
        $questionOrder = 0;
        foreach ($subquestionsArray as $subquestionArray) {
            foreach ($subquestionArray as $scaleId => $data) {
                if (!isset($data['code'])) {
                    throw new BadRequestException(
                        'Missing mandatory field "code" for question'
                    );
                }
                $subquestion = $this->modelQuestion->findByAttributes(
                    [
                        'parent_qid' => $question->qid,
                        'title'      => $data['code'],
                        'scale_id'   => $scaleId
                    ]
                );
                if (empty($subquestion)) {
                    throw new NotFoundException(
                        'Subquestion with code "'
                        . $data['code'] . '" not found'
                    );
                }
                $subquestion->sid = $question->sid;
                $subquestion->gid = $question->gid;
                $subquestion->parent_qid = $question->qid;
                $subquestion->question_order = $questionOrder;
                $questionOrder++;
                $subquestion->title = $data['code'];
                if ($scaleId === 0) {
                    $subquestion->relevance = $data['relevance'];
                }
                $subquestion->scale_id = $scaleId;
                if (!$subquestion->update()) {
                    throw new PersistErrorException(
                        'Could not save subquestion'
                    );
                }
                $subquestion->refresh();
                $this->updateSubquestionL10nService(
                    $subquestion,
                    $data['subquestionl10n']
                );
            }
        }
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
