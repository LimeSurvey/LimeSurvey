<?php

namespace LimeSurvey\Models\Services\QuestionEditor;

use Question;

use LimeSurvey\Models\Services\QuestionEditor\{
    QuestionEditorL10n
};

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException,
    PermissionDeniedException,
    BadRequestException
};

/**
 * Question Editor Service
 *
 * Service class for editing question data.
 *
 * Dependencies are injected to enable mocking.
 */
class QuestionEditorSubQuestions
{
    private QuestionEditorL10n $questionEditorL10n;

    public function __construct(
        QuestionEditorL10n $questionEditorL10n
    ) {
        $this->questionEditorL10n = $questionEditorL10n;
    }

    /**
     * Based on QuestionAdministrationController::actionSaveQuestionData()
     *
     * @param array{
     *  ...<array-key, mixed>
     * } $subquestions
     * @throws PersistErrorException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @return void
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
                $subquestion             = new Question();
                $subquestion->sid        = $question->sid;
                $subquestion->gid        = $question->gid;
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
                        gT('Could not save subquestion')
                    );
                }
                $subquestion->refresh();
                $this->updateSubquestionL10n(
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
                $subquestion = Question::model()->findByAttributes(
                    [
                        'parent_qid' => $question->qid,
                        'title'      => $data['code'],
                        'scale_id'   => $scaleId
                    ]
                );
                if (empty($subquestion)) {
                    throw new NotFoundException(
                        'Found no subquestion with code ' . $data['code']
                    );
                }
                $subquestion->sid        = $question->sid;
                $subquestion->gid        = $question->gid;
                $subquestion->parent_qid = $question->qid;
                $subquestion->question_order = $questionOrder;
                $questionOrder++;
                if (!isset($data['code'])) {
                    throw new BadRequestException(
                        'Internal error: '
                        . 'Missing mandatory field "code" for question'
                    );
                }
                $subquestion->title      = $data['code'];
                if ($scaleId === 0) {
                    $subquestion->relevance  = $data['relevance'];
                }
                $subquestion->scale_id   = $scaleId;
                if (!$subquestion->update()) {
                    throw new PersistErrorException(
                        gT('Could not save subquestion')
                    );
                }
                $subquestion->refresh();
                $this->updateSubquestionL10n(
                    $subquestion,
                    $data['subquestionl10n']
                );
            }
        }
    }

    /**
     * Save subquestion L10n
     *
     * @param Question $question
     * @param string $language
     * @return void
     * @throws PersistErrorException
     * @throws BadRequestException
     */
    private function updateSubquestionL10n(Question $subquestion, $data)
    {
        foreach ($data as $language => $questionText) {
            $this->questionEditorL10n->save(
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
