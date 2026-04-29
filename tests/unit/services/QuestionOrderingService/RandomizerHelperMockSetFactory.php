<?php

namespace ls\tests\unit\services\QuestionOrderingService;
use Mockery;
use Question;
use Survey;

class RandomizerHelperMockSetFactory
{
    /**
     * @param ?RandomizerHelperMockSet $init
     */
    public function make(RandomizerHelperMockSet $init = null
    ): RandomizerHelperMockSet {
        $mockSet = new RandomizerHelperMockSet;

        $mockSet->question = ($init && isset($init->question))
            ? $init->question
            : $this->getMockQuestion();

        $mockSet->survey = ($init && isset($init->survey))
            ? $init->survey
            : $this->getMockSurvey();

        return $mockSet;
    }

    /**
     * Get a mock Question with default behavior
     */
    private function getMockQuestion(): Question
    {
        $question = Mockery::mock(Question::class)
            ->makePartial();
        $question->shouldReceive('getQuestionAttribute')
            ->andReturn(null);
        $question->sid = 12345;

        return $question;
    }

    /**
     * Get a mock Question configured for excluded subquestion testing
     */
    public function getMockQuestionWithExcludedSubquestion(): Question
    {
        $question = Mockery::mock(Question::class)
            ->makePartial();
        $question->shouldReceive('getQuestionAttribute')
            ->with('exclude_all_others')
            ->andReturn('excluded');
        $question->shouldReceive('getQuestionAttribute')
            ->with('random_order')
            ->andReturn(1);
        $question->sid = 12345;

        return $question;
    }

    /**
     * Get a mock Survey
     */
    private function getMockSurvey(): Survey
    {
        $survey = Mockery::mock(Survey::class)
            ->makePartial();
        $survey->shouldReceive('getIsActive')->andReturn(true);
        return $survey;
    }
}

/**
 * Container for mocks used in RandomizerHelper tests
 */
class RandomizerHelperMockSet
{
    /** @var Question */
    public $question;

    /** @var Survey */
    public $survey;
}
