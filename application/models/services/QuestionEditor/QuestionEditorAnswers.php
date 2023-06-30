<?php

namespace LimeSurvey\Models\Services\QuestionEditor;

use Question;
use Answer;
use AnswerL10n;

use LimeSurvey\Models\Services\Exception\{
    PersistErrorException,
    NotFoundException,
    PermissionDeniedException
};

/**
 * Question Editor Service
 *
 * Service class for editing question data.
 *
 * Dependencies are injected to enable mocking.
 */
class QuestionEditorAnswers
{
    /**
     * Based on QuestionAdministrationController::actionSaveQuestionData()
     *
     * @param Question $question
     * @param array{
     *  ?logic: array{
     *      ?min_answers: int,
     *      ?max_answers: int,
     *      ?array_filter_style: int,
     *      ?array_filter: string,
     *      ?array_filter_exclude: string,
     *      ?exclude_all_others: int,
     *      ?random_group: string,
     *      ?em_validation_q: string,
     *      ?em_validation_q_tip: array{
     *          ?en: string,
     *          ?de: string,
     *          ...<array-key, mixed>
     *      },
     *          ...<array-key, mixed>
     * },
     * ?display: array{
     *      ...<array-key, mixed>
     *  },
     *  ?statistics: array{
     *      ...<array-key, mixed>
     *  },
     *  ...<array-key, mixed>
     * } $input
     * @return void
     * @throws PersistErrorException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     */
    public function save(Question $question, $answerOptions)
    {
        // Clean answer options before save.
        // NB: Still inside a database transaction.
        $question->deleteAllAnswers();
        // If question type has answeroptions, save them.
        if ($question->questionType->answerscales > 0) {
            $this->storeAnswerOptions(
                $question,
                $answerOptions ?? []
            );
        }
    }


    /**
     * Store new answer options.
     * Different from update during active survey?
     *
     * @param Question $question
     * @param array $optionsArray
     * @return void
     * @throws PersistErrorException
     */
    private function storeAnswerOptions(Question $question, $optionsArray)
    {
        $count = 0;
        foreach ($optionsArray as $optionArray) {
            $this->storeAnswerOption(
                $question,
                $optionArray,
                $count
            );
        }
        return true;
    }

    /**
     * Store new answer options.
     * Different from update during active survey?
     *
     * @param Question $question
     * @param array $optionsArray
     * @param int &$count
     * @return void
     * @throws PersistErrorException
     */
    private function storeAnswerOption(Question $question, $optionArray, &$count)
    {
        foreach ($optionArray as $scaleId => $data) {
            if (!isset($data['code'])) {
                throw new Exception(
                    'code is not set in data: ' . json_encode($data)
                );
            }
            $answer = new Answer();
            $answer->qid = $question->qid;
            $answer->code = $data['code'];
            $answer->sortorder = $count;
            $count++;
            if (isset($data['assessment'])) {
                $answer->assessment_value = $data['assessment'];
            } else {
                $answer->assessment_value = 0;
            }
            $answer->scale_id = $scaleId;
            if (!$answer->save()) {
                throw new PersistErrorException(
                    gT('Could not save answer')
                );
            }
            $answer->refresh();
            foreach (
                $data['answeroptionl10n']
                as $language => $answerOptionText
            ) {
                $this->storeAnswerL10n(
                    $answer,
                    $language,
                    $answerOptionText
                );
            }
        }
    }

    /**
     * Store new answer L10n
     *
     * @param Answer $answer
     * @param string $language
     * @param string $text
     * @return void
     * @throws PersistErrorException
     */
    private function storeAnswerL10n(Answer $answer, $language, $text)
    {
        $l10n = new AnswerL10n();
        $l10n->aid = $answer->aid;
        $l10n->language = $language;
        $l10n->answer = $text;
        if (!$l10n->save()) {
            throw new PersistErrorException(
                gT('Could not save answer option')
            );
        }
    }
}
