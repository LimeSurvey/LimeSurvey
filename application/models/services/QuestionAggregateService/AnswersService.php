<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

use Question;
use Answer;
use AnswerL10n;
use LimeSurvey\DI;
use LimeSurvey\Models\Services\Exception\{
    BadRequestException,
    PersistErrorException,
    NotFoundException,
    PermissionDeniedException
};

/**
 * Question Aggregate Service
 *
 * Service class for editing question data.
 *
 * Dependencies are injected to enable mocking.
 */
class AnswersService
{
    /**
     * Based on QuestionAdministrationController::actionSaveQuestionData()
     *
     * @param Question $question
     * @param $answerOptions
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
                throw new BadRequestException(
                    '"code" is not set in attribute data: '
                    . json_encode($data)
                );
            }

            // We use the container to create a model instance
            // allowing us to mock the model instance via
            // container configuration in unit tests
            $answer = DI::getContainer()
                ->make(Answer::class);
            $answer->setAttributes([
                'qid' => $question->qid,
                'code' => $data['code'],
                'sortorder' => $count,
                'assessment_value' =>
                    isset($data['assessment'])
                    ? $data['assessment']
                    : 0,
                'scale_id' => $scaleId
            ]);
            $count++;

            if (!$answer->save()) {
                throw new PersistErrorException(
                    gT('Could not save answer')
                );
            }

            $answer->refresh();

            foreach (
                $data['answeroptionl10n'] as $language => $answerOptionText
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
        $l10n = DI::getContainer()
            ->make(AnswerL10n::class);
        $l10n->setAttributes([
            'aid' => $answer->aid,
            'language' => $language,
            'answer' => $text
        ]);

        if (!$l10n->save()) {
            throw new PersistErrorException(
                gT('Could not save answer option')
            );
        }
    }
}
