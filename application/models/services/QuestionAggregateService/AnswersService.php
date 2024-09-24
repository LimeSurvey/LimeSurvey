<?php

namespace LimeSurvey\Models\Services\QuestionAggregateService;

use DI\DependencyException;
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
    use ValidateTrait;

    private Answer $modelAnswer;
    private AnswerL10n $modelAnswerL10n;

    public function __construct(
        Answer $modelAnswer,
        AnswerL10n $modelAnswerL10n
    ) {
        $this->modelAnswer = $modelAnswer;
        $this->modelAnswerL10n = $modelAnswerL10n;
    }

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
        $this->validateCodes($optionsArray);
        $answerIds = [];
        $count = 0;
        foreach ($optionsArray as $answerId => $optionArray) {
            $answerIds[] = $this->storeAnswerOption(
                $question,
                $answerId,
                $optionArray,
                $count
            );
        }
        $question->deleteAllAnswers($answerIds);
    }

    /**
     * Stores a single answer.
     * Different from update during active survey?
     *
     * @param Question $question
     * @param $answerId
     * @param array $optionArray
     * @param int &$count
     * @return int
     * @throws BadRequestException
     * @throws PersistErrorException
     * @throws DependencyException
     * @throws \DI\NotFoundException
     */
    private function storeAnswerOption(
        Question $question,
        $answerId,
        array $optionArray,
        int &$count
    ): int {
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
            $answer = $this->modelAnswer->findByPk($answerId);
            if (!$answer) {
                $answer = DI::getContainer()
                    ->make(Answer::class);
            }
            $answer->setAttributes([
                'qid'              => $question->qid,
                'code'             => $data['code'],
                'sortorder'        => $count,
                'assessment_value' =>
                    isset($data['assessment'])
                        ? $data['assessment']
                        : 0,
                'scale_id'         => $scaleId
            ]);
            $answer->setScenario('saveall');
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
            return $answer->aid;
        }
        return 0;
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
        $l10n = $this->modelAnswerL10n->findByAttributes(
            [
                'aid' => $answer->aid,
                'language' => $language
            ]
        );
        if (!$l10n) {
            $l10n = DI::getContainer()->make(AnswerL10n::class);
        }
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
